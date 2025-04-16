<?php
/**
 * The header for our theme
 *
 * @package Car_Leasing
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'car-leasing'); ?></a>

    <header id="masthead" class="site-header">
        <div class="top-bar bg-dark text-white py-2">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="contact-info">
                            <span class="me-3"><i class="fas fa-phone me-1"></i> +7 (999) 123-45-67</span>
                            <span><i class="fas fa-envelope me-1"></i> info@carleasing.com</span>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <?php if (is_user_logged_in()) : ?>
                            <?php $current_user = wp_get_current_user(); ?>
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-sm btn-dark dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user me-1"></i> <?php echo esc_html($current_user->display_name); ?>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="userDropdown">
                                    <?php if (in_array('car_leasing_client', $current_user->roles)) : ?>
                                        <li><a class="dropdown-item" href="<?php echo esc_url(home_url('/client-dashboard/')); ?>"><?php esc_html_e('Dashboard', 'car-leasing'); ?></a></li>
                                    <?php elseif (in_array('car_leasing_manager', $current_user->roles)) : ?>
                                        <li><a class="dropdown-item" href="<?php echo esc_url(home_url('/manager-dashboard/')); ?>"><?php esc_html_e('Dashboard', 'car-leasing'); ?></a></li>
                                    <?php elseif (in_array('administrator', $current_user->roles)) : ?>
                                        <li><a class="dropdown-item" href="<?php echo esc_url(home_url('/admin-dashboard/')); ?>"><?php esc_html_e('Dashboard', 'car-leasing'); ?></a></li>
                                    <?php endif; ?>
                                    <li><a class="dropdown-item" href="<?php echo esc_url(home_url('/messages/')); ?>"><?php esc_html_e('Messages', 'car-leasing'); ?></a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?php echo esc_url(wp_logout_url(home_url())); ?>"><?php esc_html_e('Logout', 'car-leasing'); ?></a></li>
                                </ul>
                            </div>
                        <?php else : ?>
                            <a href="<?php echo esc_url(home_url('/login-register/')); ?>" class="text-white text-decoration-none"><i class="fas fa-user me-1"></i> <?php esc_html_e('Login / Register', 'car-leasing'); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="<?php echo esc_url(home_url('/')); ?>">
                    <?php if (has_custom_logo()) : ?>
                        <?php the_custom_logo(); ?>
                    <?php else : ?>
                        <span class="site-title h4 mb-0"><?php bloginfo('name'); ?></span>
                    <?php endif; ?>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarMain">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'menu_id'        => 'primary-menu',
                        'depth'          => 2,
                        'container'      => false,
                        'menu_class'     => 'navbar-nav ms-auto mb-2 mb-lg-0',
                        'fallback_cb'    => '__return_false',
                        'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                        'walker'         => new Bootstrap_Walker_Nav_Menu(),
                    ));
                    ?>
                    
                    <a href="<?php echo esc_url(home_url('/application-form/')); ?>" class="btn btn-primary ms-lg-3"><?php esc_html_e('Apply for Leasing', 'car-leasing'); ?></a>
                </div>
            </div>
        </nav>
    </header><!-- #masthead -->

<?php
// Bootstrap NavWalker implementation for WordPress
if (!class_exists('Bootstrap_Walker_Nav_Menu')) {
    class Bootstrap_Walker_Nav_Menu extends Walker_Nav_Menu {
        public function start_lvl(&$output, $depth = 0, $args = null) {
            $indent = str_repeat("\t", $depth);
            $output .= "\n$indent<ul class=\"dropdown-menu\">\n";
        }

        public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
            $indent = ($depth) ? str_repeat("\t", $depth) : '';

            $li_attributes = '';
            $class_names = $value = '';

            $classes = empty($item->classes) ? array() : (array) $item->classes;
            $classes[] = 'nav-item menu-item-' . $item->ID;

            if ($args->walker->has_children) {
                $classes[] = 'dropdown';
            }

            if (in_array('current-menu-item', $classes)) {
                $classes[] = 'active';
            }

            $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
            $class_names = ' class="' . esc_attr($class_names) . '"';

            $id = apply_filters('nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args);
            $id = strlen($id) ? ' id="' . esc_attr($id) . '"' : '';

            $output .= $indent . '<li' . $id . $class_names . $li_attributes . '>';

            $attributes  = !empty($item->attr_title) ? ' title="'  . esc_attr($item->attr_title) .'"' : '';
            $attributes .= !empty($item->target)     ? ' target="' . esc_attr($item->target) .'"' : '';
            $attributes .= !empty($item->xfn)        ? ' rel="'    . esc_attr($item->xfn) .'"' : '';
            $attributes .= !empty($item->url)        ? ' href="'   . esc_attr($item->url) .'"' : '';

            // Check if menu item is in main menu
            if ($depth === 0) {
                $attributes .= ' class="nav-link"';
                // Add dropdown for menu items with children
                if ($args->walker->has_children) {
                    $attributes .= ' class="nav-link dropdown-toggle" data-bs-toggle="dropdown"';
                }
            } else {
                $attributes .= ' class="dropdown-item"';
            }

            $item_output = $args->before;
            $item_output .= '<a'. $attributes .'>';
            $item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
            $item_output .= '</a>';
            $item_output .= $args->after;

            $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
        }
    }
}
?>
