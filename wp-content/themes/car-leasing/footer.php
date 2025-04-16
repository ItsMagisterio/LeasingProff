<?php
/**
 * The template for displaying the footer
 *
 * @package Car_Leasing
 */
?>

    <footer id="colophon" class="site-footer bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="text-uppercase mb-4"><?php esc_html_e('About Us', 'car-leasing'); ?></h5>
                    <p><?php esc_html_e('Car Leasing provides the best leasing solutions for businesses and individuals. We offer flexible terms, competitive rates, and exceptional service.', 'car-leasing'); ?></p>
                    <div class="social-links mt-4">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="text-uppercase mb-4"><?php esc_html_e('Quick Links', 'car-leasing'); ?></h5>
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer',
                        'menu_id'        => 'footer-menu',
                        'depth'          => 1,
                        'container'      => false,
                        'menu_class'     => 'list-unstyled',
                        'fallback_cb'    => '__return_false',
                        'add_li_class'   => 'mb-2',
                        'link_class'     => 'text-white text-decoration-none',
                    ));
                    ?>
                </div>
                
                <div class="col-md-4">
                    <h5 class="text-uppercase mb-4"><?php esc_html_e('Contact Us', 'car-leasing'); ?></h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> <?php esc_html_e('123 Leasing Street, Moscow, Russia', 'car-leasing'); ?></li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i> +7 (999) 123-45-67</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i> info@carleasing.com</li>
                        <li><i class="fas fa-clock me-2"></i> <?php esc_html_e('Mon-Fri: 9:00 AM - 6:00 PM', 'car-leasing'); ?></li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4 bg-light">
            
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-md-0">&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php esc_html_e('All Rights Reserved.', 'car-leasing'); ?></p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0"><?php esc_html_e('Designed by', 'car-leasing'); ?> <a href="#" class="text-white">Car Leasing Team</a></p>
                </div>
            </div>
        </div>
    </footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>

<?php
// Add custom classes to footer menu items
add_filter('nav_menu_css_class', 'car_leasing_add_li_class', 10, 3);
function car_leasing_add_li_class($classes, $item, $args) {
    if (isset($args->add_li_class)) {
        $classes[] = $args->add_li_class;
    }
    return $classes;
}

// Add custom classes to footer menu links
add_filter('nav_menu_link_attributes', 'car_leasing_add_link_class', 10, 3);
function car_leasing_add_link_class($atts, $item, $args) {
    if (isset($args->link_class)) {
        $atts['class'] = $args->link_class;
    }
    return $atts;
}
?>
