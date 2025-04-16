<?php
/**
 * Car Leasing functions and definitions
 *
 * @package Car_Leasing
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Theme setup
function car_leasing_setup() {
    // Add default posts and comments RSS feed links to head
    add_theme_support('automatic-feed-links');

    // Let WordPress manage the document title
    add_theme_support('title-tag');

    // Enable support for Post Thumbnails on posts and pages
    add_theme_support('post-thumbnails');

    // This theme uses wp_nav_menu() in two locations
    register_nav_menus(array(
        'primary' => esc_html__('Primary Menu', 'car-leasing'),
        'footer'  => esc_html__('Footer Menu', 'car-leasing'),
    ));

    // Switch default core markup to output valid HTML5
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));

    // Set up the WordPress core custom background feature
    add_theme_support('custom-background', apply_filters('car_leasing_custom_background_args', array(
        'default-color' => 'ffffff',
        'default-image' => '',
    )));

    // Add theme support for selective refresh for widgets
    add_theme_support('customize-selective-refresh-widgets');

    // Add support for custom logo
    add_theme_support('custom-logo', array(
        'height'      => 250,
        'width'       => 250,
        'flex-width'  => true,
        'flex-height' => true,
    ));
}
add_action('after_setup_theme', 'car_leasing_setup');

// Set the content width in pixels
function car_leasing_content_width() {
    $GLOBALS['content_width'] = apply_filters('car_leasing_content_width', 1140);
}
add_action('after_setup_theme', 'car_leasing_content_width', 0);

// Register widget area
function car_leasing_widgets_init() {
    register_sidebar(array(
        'name'          => esc_html__('Sidebar', 'car-leasing'),
        'id'            => 'sidebar-1',
        'description'   => esc_html__('Add widgets here.', 'car-leasing'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));

    register_sidebar(array(
        'name'          => esc_html__('Footer Widgets', 'car-leasing'),
        'id'            => 'footer-widgets',
        'description'   => esc_html__('Add footer widgets here.', 'car-leasing'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'car_leasing_widgets_init');

// Enqueue scripts and styles
function car_leasing_scripts() {
    // Enqueue Bootstrap CSS from CDN
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css', array(), '5.2.3');
    
    // Enqueue Font Awesome CSS from CDN
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0');
    
    // Theme stylesheet
    wp_enqueue_style('car-leasing-style', get_stylesheet_uri(), array(), '1.0.0');
    
    // Custom CSS
    wp_enqueue_style('car-leasing-main', get_template_directory_uri() . '/css/main.css', array(), '1.0.0');
    
    // Bootstrap JS Bundle with Popper
    wp_enqueue_script('bootstrap-bundle', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.2.3', true);
    
    // Theme custom JS
    wp_enqueue_script('car-leasing-script', get_template_directory_uri() . '/js/script.js', array('jquery'), '1.0.0', true);
    
    // Comment reply script
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'car_leasing_scripts');

// Include custom post types
require get_template_directory() . '/inc/custom-post-types.php';

// Include user roles
require get_template_directory() . '/inc/user-roles.php';

// Include messaging system
require get_template_directory() . '/inc/messaging-system.php';

// Include application system
require get_template_directory() . '/inc/application-system.php';

// Include template functions
require get_template_directory() . '/inc/template-functions.php';

// Include customizer options
require get_template_directory() . '/inc/customizer.php';

// Redirect users to their dashboards after login based on role
function car_leasing_login_redirect($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        // Check if user is a client
        if (in_array('car_leasing_client', $user->roles)) {
            return home_url('/client-dashboard/');
        }
        // Check if user is a manager
        elseif (in_array('car_leasing_manager', $user->roles)) {
            return home_url('/manager-dashboard/');
        }
        // Check if user is an administrator
        elseif (in_array('administrator', $user->roles)) {
            return home_url('/admin-dashboard/');
        }
    }
    return $redirect_to;
}
add_filter('login_redirect', 'car_leasing_login_redirect', 10, 3);

// Custom login/registration page shortcode
function car_leasing_login_register_shortcode() {
    ob_start();
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        
        echo '<div class="logged-in-message">';
        echo '<p>' . sprintf(esc_html__('You are currently logged in as %s.', 'car-leasing'), $current_user->display_name) . '</p>';
        
        // Display appropriate dashboard link based on user role
        if (in_array('car_leasing_client', $current_user->roles)) {
            echo '<p><a href="' . esc_url(home_url('/client-dashboard/')) . '" class="btn btn-primary">' . esc_html__('Go to Dashboard', 'car-leasing') . '</a></p>';
        } elseif (in_array('car_leasing_manager', $current_user->roles)) {
            echo '<p><a href="' . esc_url(home_url('/manager-dashboard/')) . '" class="btn btn-primary">' . esc_html__('Go to Dashboard', 'car-leasing') . '</a></p>';
        } elseif (in_array('administrator', $current_user->roles)) {
            echo '<p><a href="' . esc_url(home_url('/admin-dashboard/')) . '" class="btn btn-primary">' . esc_html__('Go to Dashboard', 'car-leasing') . '</a></p>';
        }
        
        echo '<p><a href="' . esc_url(wp_logout_url(home_url())) . '" class="btn btn-outline-secondary">' . esc_html__('Logout', 'car-leasing') . '</a></p>';
        echo '</div>';
    } else {
        include_once(get_template_directory() . '/page-templates/login-register.php');
    }
    return ob_get_clean();
}
add_shortcode('car_leasing_login_register', 'car_leasing_login_register_shortcode');

// Function to check if user has access to a specific dashboard
function car_leasing_can_access_dashboard($dashboard_type) {
    if (!is_user_logged_in()) {
        return false;
    }
    
    $user = wp_get_current_user();
    
    switch ($dashboard_type) {
        case 'client':
            return in_array('car_leasing_client', $user->roles);
        case 'manager':
            return in_array('car_leasing_manager', $user->roles);
        case 'admin':
            return in_array('administrator', $user->roles);
        default:
            return false;
    }
}
