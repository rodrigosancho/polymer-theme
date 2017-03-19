<?php
/**
 * Author: Rodrigo Sancho | https://github.com/trofrigo
 * URL: wp.trofrigo.me
 * Custom functions, support, custom post types and more.
 */

/*------------------------------------*\
    External Modules/Files
\*------------------------------------*/

// Load any external files you have here

/*------------------------------------*\
    Theme Support
\*------------------------------------*/

if (!isset($content_width)) {
    $content_width = 900;
}

if (function_exists('add_theme_support')) {

    // Add Thumbnail Theme Support
    add_theme_support('post-thumbnails');
    add_image_size('large', 700, '', true); // Large Thumbnail
    add_image_size('medium', 250, '', true); // Medium Thumbnail
    add_image_size('small', 120, '', true); // Small Thumbnail
    add_image_size('custom-size', 700, 200, true); // Custom Thumbnail Size call using the_post_thumbnail('custom-size');

    // Add Support for Custom Backgrounds - Uncomment below if you're going to use
    /*add_theme_support('custom-background', array(
    'default-color' => 'FFF',
    'default-image' => get_template_directory_uri() . '/img/bg.jpg'
    ));*/

    // Add Support for Custom Header - Uncomment below if you're going to use
    /*add_theme_support('custom-header', array(
    'default-image'          => get_template_directory_uri() . '/img/headers/default.jpg',
    'header-text'            => false,
    'default-text-color'     => '000',
    'width'                  => 1000,
    'height'                 => 198,
    'random-default'         => false,
    'wp-head-callback'       => $wphead_cb,
    'admin-head-callback'    => $adminhead_cb,
    'admin-preview-callback' => $adminpreview_cb
    ));*/

    // Enables post and comment RSS feed links to head
    add_theme_support('automatic-feed-links');

    // Enable HTML5 support
    add_theme_support('html5', array('comment-list', 'comment-form', 'search-form', 'gallery', 'caption'));

    // Localisation Support
    load_theme_textdomain('polymer_theme', get_template_directory() . '/languages');
}

/*------------------------------------*\
    Functions
\*------------------------------------*/

function get_menus()
{

    $menusKeys = ['header-menu', 'sidebar-menu', 'extra-menu'];
    $menus = [];

    $locations = get_nav_menu_locations();

    foreach ($menusKeys as $key) {
        $menus[$key] = wp_get_nav_menu_items($locations[$key]);
    }

    return json_encode($menus);
}

function get_all_pages()
{
    return json_encode(get_pages());
}

function get_front_page()
{
    $frontPageId = get_option('page_on_front');
    if ($frontPageId > 0) {
        return get_post_field('post_name', $frontPageId);
    } else {
        return 'blog';
    }
}

// HTML5 Blank navigation
function polymer_theme_nav()
{
    wp_nav_menu(
        array(
            'theme_location' => 'header-menu',
            'menu' => '',
            'container' => 'div',
            'container_class' => 'menu-{menu slug}-container',
            'container_id' => '',
            'menu_class' => 'menu',
            'menu_id' => '',
            'echo' => true,
            'fallback_cb' => 'wp_page_menu',
            'before' => '',
            'after' => '',
            'link_before' => '',
            'link_after' => '',
            'items_wrap' => '<ul>%3$s</ul>',
            'depth' => 0,
            'walker' => ''
        )
    );
}

// Load HTML5 Blank scripts (header.php)
function polymer_theme_header_scripts()
{
    if ($GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {
        if (HTML5_DEBUG) {
            // jQuery
            wp_deregister_script('jquery');
            wp_register_script('jquery', get_template_directory_uri() . '/bower_components/jquery/dist/jquery.js', array(), '1.11.1');

            // Conditionizr
            wp_register_script('conditionizr', get_template_directory_uri() . '/js/lib/conditionizr-4.3.0.min.js', array(), '4.3.0');

            // Modernizr
            wp_register_script('modernizr', get_template_directory_uri() . '/bower_components/modernizr/modernizr.js', array(), '2.8.3');

            // Custom scripts
            wp_register_script(
                'polymer_themescripts',
                get_template_directory_uri() . '/js/scripts.js',
                array(
                    'conditionizr',
                    'modernizr',
                    'jquery'),
                '1.0.0');

            // Enqueue Scripts
            wp_enqueue_script('polymer_themescripts');

            // If production
        } else {
            // Scripts minify
            wp_register_script('polymer_themescripts-min', get_template_directory_uri() . '/js/scripts.min.js', array(), '1.0.0');
            // Enqueue Scripts
            wp_enqueue_script('polymer_themescripts-min');
        }
    } else {


        //Adding Webcomponents polyfill
        wp_register_script('webcomponents_polyfill', get_template_directory_uri() . '/bower_components/webcomponentsjs/webcomponents-lite.min.js', array());
        wp_enqueue_script('webcomponents_polyfill');
        //Adding polymer-theme-admin-shell element
        wp_register_style('import_html', get_template_directory_uri() . '/src/admin/polymer-theme-admin-shell.html', array());
        wp_enqueue_style('import_html');
    }
}

// Load HTML5 Blank conditional scripts
function polymer_theme_conditional_scripts()
{
    if (is_page('pagenamehere')) {
        // Conditional script(s)
        wp_register_script('scriptname', get_template_directory_uri() . '/js/scriptname.js', array('jquery'), '1.0.0');
        wp_enqueue_script('scriptname');
    }
}

// Load HTML5 Blank styles
function polymer_theme_styles()
{
    if (HTML5_DEBUG) {
        // normalize-css
        wp_register_style('normalize', get_template_directory_uri() . '/bower_components/normalize.css/normalize.css', array(), '3.0.1');

        // Custom CSS
        wp_register_style('polymer_theme', get_template_directory_uri() . '/style.css', array('normalize'), '1.0');

        // Register CSS
        wp_enqueue_style('polymer_theme');
    } else {
        // Custom CSS
        wp_register_style('polymer_themecssmin', get_template_directory_uri() . '/style.css', array(), '1.0');
        // Register CSS
        wp_enqueue_style('polymer_themecssmin');
    }
}

// Register HTML5 Blank Navigation
function register_html5_menu()
{
    register_nav_menus(array( // Using array to specify more menus if needed
        'header-menu' => __('Header Menu', 'polymer_theme'), // Main Navigation
        'sidebar-menu' => __('Sidebar Menu', 'polymer_theme'), // Sidebar Navigation
        'extra-menu' => __('Extra Menu', 'polymer_theme') // Extra Navigation if needed (duplicate as many as you need!)
    ));
}

//Replace the rel=stylesheet rel=import
function import_html($html, $handle, $href)
{
    if ('import_html' === $handle)
        $html = str_replace('stylesheet', 'import', $html);
    return $html;
}

// Remove the <div> surrounding the dynamic navigation to cleanup markup
function my_wp_nav_menu_args($args = '')
{
    $args['container'] = false;
    return $args;
}

// Remove Injected classes, ID's and Page ID's from Navigation <li> items
function my_css_attributes_filter($var)
{
    return is_array($var) ? array() : '';
}

// Remove invalid rel attribute values in the categorylist
function remove_category_rel_from_category_list($thelist)
{
    return str_replace('rel="category tag"', 'rel="tag"', $thelist);
}

// Add page slug to body class, love this - Credit: Starkers Wordpress Theme
function add_slug_to_body_class($classes)
{
    global $post;
    if (is_home()) {
        $key = array_search('blog', $classes);
        if ($key > -1) {
            unset($classes[$key]);
        }
    } elseif (is_page()) {
        $classes[] = sanitize_html_class($post->post_name);
    } elseif (is_singular()) {
        $classes[] = sanitize_html_class($post->post_name);
    }

    return $classes;
}

// Remove the width and height attributes from inserted images
function remove_width_attribute($html)
{
    $html = preg_replace('/(width|height)="\d*"\s/', "", $html);
    return $html;
}


// If Dynamic Sidebar Exists
if (function_exists('register_sidebar')) {
    // Define Sidebar Widget Area 1
    register_sidebar(array(
        'name' => __('Widget Area 1', 'polymer_theme'),
        'description' => __('Description for this widget-area...', 'polymer_theme'),
        'id' => 'widget-area-1',
        'before_widget' => '<div id="%1$s" class="%2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3>',
        'after_title' => '</h3>'
    ));

    // Define Sidebar Widget Area 2
    register_sidebar(array(
        'name' => __('Widget Area 2', 'polymer_theme'),
        'description' => __('Description for this widget-area...', 'polymer_theme'),
        'id' => 'widget-area-2',
        'before_widget' => '<div id="%1$s" class="%2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3>',
        'after_title' => '</h3>'
    ));
}

// Remove wp_head() injected Recent Comment styles
function my_remove_recent_comments_style()
{
    global $wp_widget_factory;
    remove_action('wp_head', array(
        $wp_widget_factory->widgets['WP_Widget_Recent_Comments'],
        'recent_comments_style'
    ));
}

// Pagination for paged posts, Page 1, Page 2, Page 3, with Next and Previous Links, No plugin
function html5wp_pagination()
{
    global $wp_query;
    $big = 999999999;
    echo paginate_links(array(
        'base' => str_replace($big, '%#%', get_pagenum_link($big)),
        'format' => '?paged=%#%',
        'current' => max(1, get_query_var('paged')),
        'total' => $wp_query->max_num_pages
    ));
}

// Custom Excerpts
function html5wp_index($length) // Create 20 Word Callback for Index page Excerpts, call using html5wp_excerpt('html5wp_index');
{
    return 20;
}

// Create 40 Word Callback for Custom Post Excerpts, call using html5wp_excerpt('html5wp_custom_post');
function html5wp_custom_post($length)
{
    return 40;
}

// Create the Custom Excerpts callback
function html5wp_excerpt($length_callback = '', $more_callback = '')
{
    global $post;
    if (function_exists($length_callback)) {
        add_filter('excerpt_length', $length_callback);
    }
    if (function_exists($more_callback)) {
        add_filter('excerpt_more', $more_callback);
    }
    $output = get_the_excerpt();
    $output = apply_filters('wptexturize', $output);
    $output = apply_filters('convert_chars', $output);
    $output = '<p>' . $output . '</p>';
    echo $output;
}

// Custom View Article link to Post
function html5_blank_view_article($more)
{
    global $post;
    return '... <a class="view-article" href="' . get_permalink($post->ID) . '">' . __('View Article', 'polymer_theme') . '</a>';
}

// Remove Admin bar
function remove_admin_bar()
{
    return false;
}

// Remove 'text/css' from our enqueued stylesheet
function html5_style_remove($tag)
{
    return preg_replace('~\s+type=["\'][^"\']++["\']~', '', $tag);
}

// Remove thumbnail width and height dimensions that prevent fluid images in the_thumbnail
function remove_thumbnail_dimensions($html)
{
    $html = preg_replace('/(width|height)=\"\d*\"\s/', "", $html);
    return $html;
}

// Custom Gravatar in Settings > Discussion
function polymer_themegravatar($avatar_defaults)
{
    $myavatar = get_template_directory_uri() . '/img/gravatar.jpg';
    $avatar_defaults[$myavatar] = "Custom Gravatar";
    return $avatar_defaults;
}

// Threaded Comments
function enable_threaded_comments()
{
    if (!is_admin()) {
        if (is_singular() AND comments_open() AND (get_option('thread_comments') == 1)) {
            wp_enqueue_script('comment-reply');
        }
    }
}

// Custom Comments Callback
function polymer_themecomments($comment, $args, $depth)
{
    $GLOBALS['comment'] = $comment;
    extract($args, EXTR_SKIP);

    if ('div' == $args['style']) {
        $tag = 'div';
        $add_below = 'comment';
    } else {
        $tag = 'li';
        $add_below = 'div-comment';
    }
    ?>
    <!-- heads up: starting < for the html tag (li or div) in the next line: -->
    <<?php echo $tag ?><?php comment_class(empty($args['has_children']) ? '' : 'parent') ?> id="comment-<?php comment_ID() ?>">
    <?php if ('div' != $args['style']) : ?>
    <div id="div-comment-<?php comment_ID() ?>" class="comment-body">
<?php endif; ?>
    <div class="comment-author vcard">
        <?php if ($args['avatar_size'] != 0) echo get_avatar($comment, $args['avatar_size']); ?>
        <?php printf(__('<cite class="fn">%s</cite> <span class="says">says:</span>'), get_comment_author_link()) ?>
    </div>
    <?php if ($comment->comment_approved == '0') : ?>
    <em class="comment-awaiting-moderation"><?php _e('Your comment is awaiting moderation.') ?></em>
    <br/>
<?php endif; ?>

    <div class="comment-meta commentmetadata"><a
            href="<?php echo htmlspecialchars(get_comment_link($comment->comment_ID)) ?>">
            <?php
            printf(__('%1$s at %2$s'), get_comment_date(), get_comment_time()) ?></a><?php edit_comment_link(__('(Edit)'), '  ', '');
        ?>
    </div>

    <?php comment_text() ?>

    <div class="reply">
        <?php comment_reply_link(array_merge($args, array('add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
    </div>
    <?php if ('div' != $args['style']) : ?>
    </div>
<?php endif; ?>
<?php }

/*------------------------------------*\
    Actions + Filters + ShortCodes
\*------------------------------------*/

// Add Actions
add_action('init', 'polymer_theme_header_scripts'); // Add Custom Scripts to wp_head
add_action('wp_print_scripts', 'polymer_theme_conditional_scripts'); // Add Conditional Page Scripts
add_action('get_header', 'enable_threaded_comments'); // Enable Threaded Comments
add_action('wp_enqueue_scripts', 'polymer_theme_styles'); // Add Theme Stylesheet
add_action('init', 'register_html5_menu'); // Add HTML5 Blank Menu
add_action('widgets_init', 'my_remove_recent_comments_style'); // Remove inline Recent Comment Styles from wp_head()
add_action('init', 'html5wp_pagination'); // Add our HTML5 Pagination

// Remove Actions
remove_action('wp_head', 'feed_links_extra', 3); // Display the links to the extra feeds such as category feeds
remove_action('wp_head', 'feed_links', 2); // Display the links to the general feeds: Post and Comment Feed
remove_action('wp_head', 'rsd_link'); // Display the link to the Really Simple Discovery service endpoint, EditURI link
remove_action('wp_head', 'wlwmanifest_link'); // Display the link to the Windows Live Writer manifest file.
remove_action('wp_head', 'wp_generator'); // Display the XHTML generator that is generated on the wp_head hook, WP version
remove_action('wp_head', 'rel_canonical');
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);

// Add Filters
add_filter('avatar_defaults', 'polymer_themegravatar'); // Custom Gravatar in Settings > Discussion
add_filter('body_class', 'add_slug_to_body_class'); // Add slug to body class (Starkers build)
add_filter('widget_text', 'do_shortcode'); // Allow shortcodes in Dynamic Sidebar
add_filter('widget_text', 'shortcode_unautop'); // Remove <p> tags in Dynamic Sidebars (better!)
add_filter('wp_nav_menu_args', 'my_wp_nav_menu_args'); // Remove surrounding <div> from WP Navigation
// add_filter('nav_menu_css_class', 'my_css_attributes_filter', 100, 1); // Remove Navigation <li> injected classes (Commented out by default)
// add_filter('nav_menu_item_id', 'my_css_attributes_filter', 100, 1); // Remove Navigation <li> injected ID (Commented out by default)
// add_filter('page_css_class', 'my_css_attributes_filter', 100, 1); // Remove Navigation <li> Page ID's (Commented out by default)
add_filter('the_category', 'remove_category_rel_from_category_list'); // Remove invalid rel attribute
add_filter('the_excerpt', 'shortcode_unautop'); // Remove auto <p> tags in Excerpt (Manual Excerpts only)
add_filter('the_excerpt', 'do_shortcode'); // Allows Shortcodes to be executed in Excerpt (Manual Excerpts only)
add_filter('excerpt_more', 'html5_blank_view_article'); // Add 'View Article' button instead of [...] for Excerpts
add_filter('show_admin_bar', 'remove_admin_bar'); // Remove Admin bar
add_filter('style_loader_tag', 'html5_style_remove'); // Remove 'text/css' from enqueued stylesheet
add_filter('post_thumbnail_html', 'remove_thumbnail_dimensions', 10); // Remove width and height dynamic attributes to thumbnails
add_filter('post_thumbnail_html', 'remove_width_attribute', 10); // Remove width and height dynamic attributes to post images
add_filter('image_send_to_editor', 'remove_width_attribute', 10); // Remove width and height dynamic attributes to post images
add_filter('style_loader_tag', 'import_html', 10, 3); //Replace the rel=stylesheet with rel=import

// Remove Filters
remove_filter('the_excerpt', 'wpautop'); // Remove <p> tags from Excerpt altogether


/**
 * Add the field "seo" and "thumbnail" to REST API responses for posts and pages read
 */

add_action('rest_api_init', 'slug_register');

function slug_register()
{
    register_rest_field(['post', 'page'],
        'seo',
        array(
            'get_callback' => 'slug_get_seo',
            'update_callback' => null,
            'schema' => null,
        )
    );

    register_rest_field(['post', 'page'],
        'thumbnail',
        array(
            'get_callback' => 'slug_get_thumbnail',
            'update_callback' => null,
            'schema' => null,
        )
    );
}

/**
 * Handler for getting custom field data.
 *
 * @since 0.1.0
 *
 * @param array $object The object from the response
 * @param string $field_name Name of field
 * @param WP_REST_Request $request Current request
 *
 * @return mixed
 */
function slug_get_seo($object, $field_name, $request)
{
    //TODO Recover SEO YOAST information if installed
    $title = $object['title']['rendered'];
    $description = $object['excerpt']['rendered'];

    return array(
        "title" => substr($title, 0, 59),
        "description" => substr($description, 0, 159)
    );
}


function slug_get_thumbnail($object, $field_name, $request)
{
    return wp_get_attachment_url($object['featured_media']);
}

// create custom plugin settings menu
add_action('admin_menu', 'polymer_theme_create_admin');

function polymer_theme_create_admin()
{
    //create new top-level menu
    add_menu_page(
        'polymer-theme',
        'polymer-theme',
        'administrator',
        'polymer-theme',
        'create_polymer_theme_view',
        get_template_directory_uri() . '/images/polymer.svg');
}

function create_polymer_theme_view()
{
    //Recovering options
    $options = get_option('polymer-theme_options');
    //Recovering templates
    $dir_content = scandir(dirname(__FILE__) . "/src/templates/");
    $filter_dir_content = array_filter($dir_content, function ($k) {
        return $k != '.' && $k != '..';
    });
    $templates = array_values($filter_dir_content);
    //Recovering pages
    $args = array('post_type' => 'any');
    $query = new WP_Query ($args);
    $posts = array_map(function ($k) {
        return array(
            'id' => $k->ID,
            'slug' => $k->post_name,
            'title' => $k->post_title
        );
    }, $query->posts);

    ?>
    <polymer-theme-admin-shell url="<?= get_admin_url() . 'admin-ajax.php'; ?>"
                               options-name='polymer-theme-options'
       <?php if($options) { ?> options='<?= json_encode($options); ?>' <?php } ?>
                               nonce="<?= wp_create_nonce('polymer-theme-nonce'); ?>"
                               action="update_polymer-theme_options"
                               templates='<?= json_encode($templates); ?>'
                               posts='<?= json_encode($posts); ?>'>
    </polymer-theme-admin-shell>
    <?php
}


add_action('wp_ajax_update_polymer-theme_options', 'post_love_add_love');

function post_love_add_love()
{
    $options = file_get_contents('php://input');
    //Check secure param
    if (wp_verify_nonce($_REQUEST['_nonce'], 'polymer-theme-nonce') && isset($options)) {
        //Save info
        update_option('polymer-theme_options', json_decode($options));
    }
    die();
}

include('polymer-theme-API.php');






