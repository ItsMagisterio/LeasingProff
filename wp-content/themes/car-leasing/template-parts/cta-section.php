<?php
/**
 * Template part for displaying the call to action section on the front page
 *
 * @package Car_Leasing
 */
?>

<section class="cta-section bg-overlay" style="background-image: url('https://images.unsplash.com/photo-1552664730-d307ca884978');">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="cta-content text-center">
                    <h2 class="cta-title mb-4"><?php esc_html_e('Ready to Start Your Leasing Journey?', 'car-leasing'); ?></h2>
                    <p class="cta-text mb-5"><?php esc_html_e('Our team of experts is standing by to help you find the perfect leasing solution for your needs.', 'car-leasing'); ?></p>
                    <div class="cta-buttons">
                        <a href="<?php echo esc_url(home_url('/application-form/')); ?>" class="btn btn-primary btn-lg me-3 mb-3"><?php esc_html_e('Apply Now', 'car-leasing'); ?></a>
                        <a href="<?php echo esc_url(home_url('/contact-us/')); ?>" class="btn btn-outline-light btn-lg mb-3"><?php esc_html_e('Contact Us', 'car-leasing'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
