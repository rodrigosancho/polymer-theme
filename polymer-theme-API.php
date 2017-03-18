<?php

/**
 * Created by PhpStorm.
 * User: Rodrigo
 * Date: 18/03/2017
 * Time: 11:20
 */
class Query_REST_Controller
{

    // Here initialize our namespace and resource name.
    public function __construct()
    {
        $this->namespace = '/polymer-theme/v1';
        $this->resource_name = 'search';
    }

    // Register our routes.
    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->resource_name, array(
            // Here we register the readable endpoint for collections.
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_items'),
                'permission_callback' => array($this, 'get_items_permissions_check'),
            ),
            // Register our schema callback.
            'schema' => array($this, 'get_item_schema'),
        ));
    }

    /**
     * Check permissions for the posts.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_items_permissions_check($request)
    {
//        TODO
//        if ( ! current_user_can( 'read' ) ) {
//            return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the post resource.' ), array( 'status' => $this->authorization_status_code() ) );
//        }
        return true;
    }

    /**
     * Grabs the five most recent posts and outputs them as a rest response.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_items($request)
    {

        $args = $request->get_query_params();

        if (!isset($args['post_type'])) {
            $args['post_type'] = 'any';
        }

        if (isset($args['url'])) {
            $args['p'] = url_to_postid($args['url']);
        }

        $query = new WP_Query ($args);
        $posts = $query->posts;

        $data = array();

        if (empty($posts)) {
            return rest_ensure_response($data);
        }

        foreach ($posts as $post) {
            $response = $this->prepare_item_for_response($post, $request);
            $data[] = $this->prepare_response_for_collection($response);
        }

        // Return all of our comment response data.
        return rest_ensure_response($data);
    }

    /**
     * Check permissions for the posts.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_item_permissions_check($request)
    {
        if (!current_user_can('read')) {
            return new WP_Error('rest_forbidden', esc_html__('You cannot view the post resource.'), array('status' => $this->authorization_status_code()));
        }
        return true;
    }

    /**
     * Matches the post data to the schema we want.
     *
     * @param WP_Post $post The comment object whose response is being prepared.
     */
    public function prepare_item_for_response($post, $request)
    {
        $data = array();

        $schema = $this->get_item_schema($request);

        // We are also renaming the fields to more understandable names.
        if (isset($schema['properties']['id'])) {
            $data['id'] = (int)$post->ID;
        }

        if ( ! empty( $schema['properties']['date'] ) ) {
            $data['date'] = $this->prepare_date_response( $post->post_date_gmt, $post->post_date );
        }

        if ( ! empty( $schema['properties']['date_gmt'] ) ) {
            $data['date_gmt'] = $this->prepare_date_response( $post->post_date_gmt );
        }

        if ( ! empty( $schema['properties']['guid'] ) ) {
            $data['guid'] = array(
                /** This filter is documented in wp-includes/post-template.php */
                'rendered' => apply_filters( 'get_the_guid', $post->guid ),
                'raw'      => $post->guid,
            );
        }

        if ( ! empty( $schema['properties']['content'] ) ) {
            $data['content'] = array(
                'raw'       => $post->post_content,
                /** This filter is documented in wp-includes/post-template.php */
                'rendered'  => post_password_required( $post ) ? '' : apply_filters( 'the_content', $post->post_content ),
                'protected' => (bool) $post->post_password,
            );
        }

        if ( ! empty( $schema['properties']['slug'] ) ) {
            $data['slug'] = $post->post_name;
        }

        if ( ! empty( $schema['properties']['status'] ) ) {
            $data['status'] = $post->post_status;
        }

        if ( ! empty( $schema['properties']['type'] ) ) {
            $data['type'] = $post->post_type;
        }

        if ( ! empty( $schema['properties']['link'] ) ) {
            $data['link'] = get_permalink( $post->ID );
        }

        if ( ! empty( $schema['properties']['title'] ) ) {
            add_filter( 'protected_title_format', array( $this, 'protected_title_format' ) );
            $data['title'] = array(
                'raw'      => $post->post_title,
                'rendered' => get_the_title( $post->ID )
            );
            remove_filter( 'protected_title_format', array( $this, 'protected_title_format' ) );
        }

        if ( ! empty( $schema['properties']['excerpt'] ) ) {
            /** This filter is documented in wp-includes/post-template.php */
            $excerpt = apply_filters( 'the_excerpt', apply_filters( 'get_the_excerpt', $post->post_excerpt, $post ) );
            $data['excerpt'] = array(
                'raw'       => $post->post_excerpt,
                'rendered'  => post_password_required( $post ) ? '' : $excerpt,
                'protected' => (bool) $post->post_password,
            );
        }

        if ( ! empty( $schema['properties']['featured_media'] ) ) {
            $data['featured_media'] = (int) get_post_thumbnail_id( $post->ID );
        }

        if(!empty( $schema['properties']['tags'])){

            $title = $post->post_title;
            if( strlen($title) > 60 ){
                $title = substr($title, 0, 59);
            }

            $description = $post->post_excerpt;
            if( strlen($description) > 60 ){
                $description = substr($description, 0, 59);
            }

            $data['tags'] = array(
                'title' => $title,
                'description' => $description
            );
        }

        if(!empty( $schema['properties']['custom_fields'] )){
            $data['custom_fields'] = get_post_custom($post->ID);
        }

        return rest_ensure_response($data);
    }

    /**
     * Prepare a response for inserting into a collection of responses.
     *
     * This is copied from WP_REST_Controller class in the WP REST API v2 plugin.
     *
     * @param WP_REST_Response $response Response object.
     * @return array Response data, ready for insertion into collection data.
     */
    public function prepare_response_for_collection($response)
    {
        if (!($response instanceof WP_REST_Response)) {
            return $response;
        }

        $data = (array)$response->get_data();
        $server = rest_get_server();

        if (method_exists($server, 'get_compact_response_links')) {
            $links = call_user_func(array($server, 'get_compact_response_links'), $response);
        } else {
            $links = call_user_func(array($server, 'get_response_links'), $response);
        }

        if (!empty($links)) {
            $data['_links'] = $links;
        }

        return $data;
    }

    /**
     * Get our sample schema for a post.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_item_schema($request)
    {
        $schema = array(
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'query',
            'type' => 'object',
            /*
             * Base properties for every Post.
             */
            'properties' => array(
                'date' => array(
                    'description' => __("The date the object was published, in the site's timezone."),
                    'type' => 'string',
                    'format' => 'date-time',
                    'context' => array('view', 'edit', 'embed'),
                ),
                'date_gmt' => array(
                    'description' => __('The date the object was published, as GMT.'),
                    'type' => 'string',
                    'format' => 'date-time',
                    'context' => array('view', 'edit'),
                ),
                'guid' => array(
                    'description' => __('The globally unique identifier for the object.'),
                    'type' => 'object',
                    'context' => array('view', 'edit'),
                    'readonly' => true,
                    'properties' => array(
                        'raw' => array(
                            'description' => __('GUID for the object, as it exists in the database.'),
                            'type' => 'string',
                            'context' => array('edit'),
                            'readonly' => true,
                        ),
                        'rendered' => array(
                            'description' => __('GUID for the object, transformed for display.'),
                            'type' => 'string',
                            'context' => array('view', 'edit'),
                            'readonly' => true,
                        ),
                    ),
                ),
                'id' => array(
                    'description' => __('Unique identifier for the object.'),
                    'type' => 'integer',
                    'context' => array('view', 'edit', 'embed'),
                    'readonly' => true,
                ),
                'link' => array(
                    'description' => __('URL to the object.'),
                    'type' => 'string',
                    'format' => 'uri',
                    'context' => array('view', 'edit', 'embed'),
                    'readonly' => true,
                ),
                'modified' => array(
                    'description' => __("The date the object was last modified, in the site's timezone."),
                    'type' => 'string',
                    'format' => 'date-time',
                    'context' => array('view', 'edit'),
                    'readonly' => true,
                ),
                'modified_gmt' => array(
                    'description' => __('The date the object was last modified, as GMT.'),
                    'type' => 'string',
                    'format' => 'date-time',
                    'context' => array('view', 'edit'),
                    'readonly' => true,
                ),
                'slug' => array(
                    'description' => __('An alphanumeric identifier for the object unique to its type.'),
                    'type' => 'string',
                    'context' => array('view', 'edit', 'embed'),
                    'arg_options' => array(
                        'sanitize_callback' => array($this, 'sanitize_slug'),
                    ),
                ),
                'status' => array(
                    'description' => __('A named status for the object.'),
                    'type' => 'string',
                    'enum' => array_keys(get_post_stati(array('internal' => false))),
                    'context' => array('edit'),
                ),
                'type' => array(
                    'description' => __('Type of Post for the object.'),
                    'type' => 'string',
                    'context' => array('view', 'edit', 'embed'),
                    'readonly' => true,
                ),
                'title' => array(
                    'description' => __('The title for the object.'),
                    'type' => 'object',
                    'context' => array('view', 'edit', 'embed'),
                    'properties' => array(
                        'raw' => array(
                            'description' => __('Title for the object, as it exists in the database.'),
                            'type' => 'string',
                            'context' => array('edit'),
                        ),
                        'rendered' => array(
                            'description' => __('HTML title for the object, transformed for display.'),
                            'type' => 'string',
                            'context' => array('view', 'edit', 'embed'),
                            'readonly' => true,
                        ),
                    ),
                ),
                'content' => array(
                    'description' => __('The content for the object.'),
                    'type' => 'object',
                    'context' => array('view', 'edit'),
                    'properties' => array(
                        'raw' => array(
                            'description' => __('Content for the object, as it exists in the database.'),
                            'type' => 'string',
                            'context' => array('edit'),
                        ),
                        'rendered' => array(
                            'description' => __('HTML content for the object, transformed for display.'),
                            'type' => 'string',
                            'context' => array('view', 'edit'),
                            'readonly' => true,
                        ),
                        'protected' => array(
                            'description' => __('Whether the content is protected with a password.'),
                            'type' => 'boolean',
                            'context' => array('view', 'edit', 'embed'),
                            'readonly' => true,
                        ),
                    ),
                ),
                'featured_media' => array(
                        'description' => __( 'The id of the featured media for the object.' ),
                        'type'        => 'integer',
                        'context'     => array( 'view', 'edit' ),
                    ),
                'excerpt' => array(
                        'description' => __( 'The excerpt for the object.' ),
                        'type'        => 'object',
                        'context'     => array( 'view', 'edit', 'embed' ),
                        'properties'  => array(
                            'raw' => array(
                                'description' => __( 'Excerpt for the object, as it exists in the database.' ),
                                'type'        => 'string',
                                'context'     => array( 'edit' ),
                            ),
                            'rendered' => array(
                                'description' => __( 'HTML excerpt for the object, transformed for display.' ),
                                'type'        => 'string',
                                'context'     => array( 'view', 'edit', 'embed' ),
                                'readonly'    => true,
                            ),
                            'protected'       => array(
                                'description' => __( 'Whether the excerpt is protected with a password.' ),
                                'type'        => 'boolean',
                                'context'     => array( 'view', 'edit', 'embed' ),
                                'readonly'    => true,
                            ),
                        ),
                    ),
                'tags' => array(
                    'description' => __('The head tags content.'),
                    'type' => 'object',
                    'context' => array('view', 'edit', 'embed'),
                    'properties' => array(
                        'title' => array(
                            'description' => __('Title attribute with a max of 60 characters.'),
                            'type' => 'string',
                            'context' => array('view', 'edit', 'embed'),
                            'readonly' => true
                        ),
                        'description' => array(
                            'description' => __('Excerpt attribute with a max of 160 characters.'),
                            'type' => 'string',
                            'context' => array('view', 'edit', 'embed'),
                            'readonly' => true,
                        ),
                    ),
                ),
                'custom_fields' => array(
                    'description' => __('Custom fields for the element.'),
                    'type' => 'object',
                    'context' => array('view', 'edit', 'embed'),
                    'readonly' => true
                ),
            ),
        );

        return $schema;
    }

    // Sets up the proper HTTP status code for authorization.
    public function authorization_status_code()
    {

        $status = 401;

        if (is_user_logged_in()) {
            $status = 403;
        }

        return $status;
    }

    /**
     * Check the post_date_gmt or modified_gmt and prepare any post or
     * modified date for single post output.
     *
     * @param string       $date_gmt
     * @param string|null  $date
     * @return string|null ISO8601/RFC3339 formatted datetime.
     */
    protected function prepare_date_response( $date_gmt, $date = null ) {
        // Use the date if passed.
        if ( isset( $date ) ) {
            return mysql_to_rfc3339( $date );
        }

        // Return null if $date_gmt is empty/zeros.
        if ( '0000-00-00 00:00:00' === $date_gmt ) {
            return null;
        }

        // Return the formatted datetime.
        return mysql_to_rfc3339( $date_gmt );
    }
}

// Function to register our new routes from the controller.
function prefix_register_routes()
{
    $controller = new Query_REST_Controller();
    $controller->register_routes();
}

add_action('rest_api_init', 'prefix_register_routes');