<?php
/**
 * Author: Rodrigo Sancho | https://github.com/trofrigo
 * URL: wp.trofrigo.me
 */

/*------------------------------------*\
    External Modules/Files
\*------------------------------------*/

include('polymer-theme-API.php');

/*------------------------------------*\
    Theme Support
\*------------------------------------*/

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

// Polymer theme navigation
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

// Load Polymer theme scripts (header.php)
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

// Load Polymer theme styles
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

// Register Polymer theme Navigation
function register_html5_menu()
{
    register_nav_menus(array( // Using array to specify more menus if needed
        'header-menu' => __('Header Menu', 'polymer_theme'), // Main Navigation
        'sidebar-menu' => __('Sidebar Menu', 'polymer_theme'), // Sidebar Navigation
        'extra-menu' => __('Extra Menu', 'polymer_theme') // Extra Navigation if needed (duplicate as many as you need!)
    ));
}

// Remove Admin bar
function remove_admin_bar()
{
    return false;
}

// Custom View Article link to Post
function html5_blank_view_article($more)
{
    global $post;
    return '... <a class="view-article" href="' . get_permalink($post->ID) . '">' . __('View Article', 'polymer_theme') . '</a>';
}

/**
 * Add the field "thumbnail" to REST API responses for posts and pages read
 */

add_action('rest_api_init', 'slug_register');

function slug_register()
{
    register_rest_field(['post', 'page'],
        'thumbnail',
        array(
            'get_callback' => 'slug_get_thumbnail',
            'update_callback' => null,
            'schema' => null,
        )
    );
}

function slug_get_thumbnail($object, $field_name, $request)
{
    return wp_get_attachment_url($object['featured_media']);
}

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

    if(!$options){ //get_option returns null if the option doesn't exist
        $options = new stdClass();
    }

    if(!isset($options->settings)){
        $options->settings = new stdClass();
    }

    if(!isset($options->templates)){
        $options->templates = new stdClass();
    }

    if(!isset($options->state)){
        $options->state = new stdClass();
    }

    if(!isset($options->state->siteurl)){
        $options->state->siteurl = get_option('siteurl');
    }

    if(!isset($options->state->custom)){
        $options->state->custom = array();
    }


    //Enqueues all scripts, styles, settings, and templates necessary to use all media JavaScript APIs.
    wp_enqueue_media();
    ?>
    <polymer-theme-admin-shell url="<?= get_admin_url() . 'admin-ajax.php'; ?>"
                               options-name='polymer-theme-options'
                               options='<?= json_encode($options); ?>'
                               nonce="<?= wp_create_nonce('polymer-theme-nonce'); ?>"
                               templates='<?= json_encode($templates); ?>'
                               posts='<?= json_encode($posts); ?>'>
    </polymer-theme-admin-shell>
    <?php
}

function delete_polymer_theme_options()
{
    //Check secure param
    if (wp_verify_nonce($_REQUEST['_nonce'], 'polymer-theme-nonce')) {
        //Save info
        delete_option('polymer-theme_options');
    }
    die();
}

function update_polymer_theme_options()
{
    $options = file_get_contents('php://input');
    //Check secure param
    if (wp_verify_nonce($_REQUEST['_nonce'], 'polymer-theme-nonce') && isset($options)) {
        //Save info
        update_option('polymer-theme_options', json_decode($options));
    }
    die();
}

//Replace the rel=stylesheet rel=import
function import_html($html, $handle, $href)
{
    if ('import_html' === $handle)
        $html = str_replace('stylesheet', 'import', $html);
    return $html;
}


/*------------------------------------*\
    Actions + Filters + ShortCodes
\*------------------------------------*/

// Add Actions
add_action('init', 'polymer_theme_header_scripts'); // Add Custom Scripts to wp_head
add_action('wp_enqueue_scripts', 'polymer_theme_styles'); // Add Theme Stylesheet
add_action('init', 'register_html5_menu'); // Add Polymer theme Menu
add_action('admin_menu', 'polymer_theme_create_admin'); // create custom plugin settings menu
add_action('wp_ajax_update_polymer-theme_options', 'update_polymer_theme_options'); //updates polymer theme options
add_action('wp_ajax_delete_polymer-theme_options', 'delete_polymer_theme_options'); //delete polymer theme options

// Remove Actions
remove_action('wp_head', 'feed_links_extra', 3); // Display the links to the extra feeds such as category feeds
remove_action('wp_head', 'feed_links', 2); // Display the links to the general feeds: Post and Comment Feed
remove_action('wp_head', 'rsd_link'); // Display the link to the Really Simple Discovery service endpoint, EditURI link
remove_action('wp_head', 'wlwmanifest_link'); // Display the link to the Windows Live Writer manifest file.
remove_action('wp_head', 'wp_generator'); // Display the XHTML generator that is generated on the wp_head hook, WP version
remove_action('wp_head', 'rel_canonical');
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);

// Add Filters
add_filter('excerpt_more', 'html5_blank_view_article'); // Add 'View Article' button instead of [...] for Excerpts
add_filter('show_admin_bar', 'remove_admin_bar'); // Remove Admin bar
add_filter('style_loader_tag', 'import_html', 10, 3); //Replace the rel=stylesheet with rel=import