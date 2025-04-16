<?php
/**
 * Template part for displaying the hero section on the front page
 *
 * @package Car_Leasing
 */
?>

<section class="hero-section bg-overlay text-white text-center" style="background-image: url('https://images.unsplash.com/photo-1498887960847-2a5e46312788');">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="hero-content">
                    <h1 class="hero-title mb-4"><?php esc_html_e('Premium Car Leasing Solutions', 'car-leasing'); ?></h1>
                    <p class="hero-subtitle mb-5"><?php esc_html_e('Get the car you want with flexible leasing terms and competitive rates', 'car-leasing'); ?></p>
                    <div class="hero-buttons">
                        <a href="<?php echo esc_url(home_url('/application-form/')); ?>" class="btn btn-primary btn-lg me-3 mb-3"><?php esc_html_e('Apply for Leasing', 'car-leasing'); ?></a>
                        <a href="<?php echo esc_url(home_url('/vehicle-marketplace/')); ?>" class="btn btn-outline-light btn-lg mb-3"><?php esc_html_e('Browse Vehicles', 'car-leasing'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
