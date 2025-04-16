<?php
/**
 * Template part for displaying the about section on the front page
 *
 * @package Car_Leasing
 */
?>

<section class="about-section py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4" alt="<?php esc_attr_e('About Our Company', 'car-leasing'); ?>" class="img-fluid rounded">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-content">
                    <h2 class="section-title"><?php esc_html_e('About Our Company', 'car-leasing'); ?></h2>
                    <p class="section-subtitle"><?php esc_html_e('Leading the way in car leasing solutions', 'car-leasing'); ?></p>
                    
                    <p><?php esc_html_e('Car Leasing is a premier provider of automotive leasing solutions in Russia. With years of experience in the industry, we\'ve established ourselves as a trusted partner for both individuals and businesses looking for flexible vehicle leasing options.', 'car-leasing'); ?></p>
                    
                    <p><?php esc_html_e('Our team of experienced professionals is dedicated to providing exceptional service and finding the perfect leasing solution for each client\'s unique needs. We work with a wide network of manufacturers and dealers to offer competitive rates and terms.', 'car-leasing'); ?></p>
                    
                    <div class="about-stats mt-4 mb-4">
                        <div class="row">
                            <div class="col-6 col-md-3 mb-3">
                                <div class="stat-item text-center">
                                    <div class="stat-value h2 mb-0 text-primary">10+</div>
                                    <div class="stat-label"><?php esc_html_e('Years', 'car-leasing'); ?></div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <div class="stat-item text-center">
                                    <div class="stat-value h2 mb-0 text-primary">5000+</div>
                                    <div class="stat-label"><?php esc_html_e('Clients', 'car-leasing'); ?></div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <div class="stat-item text-center">
                                    <div class="stat-value h2 mb-0 text-primary">20+</div>
                                    <div class="stat-label"><?php esc_html_e('Partners', 'car-leasing'); ?></div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <div class="stat-item text-center">
                                    <div class="stat-value h2 mb-0 text-primary">98%</div>
                                    <div class="stat-label"><?php esc_html_e('Satisfaction', 'car-leasing'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <a href="<?php echo esc_url(home_url('/about-us/')); ?>" class="btn btn-primary"><?php esc_html_e('Learn More About Us', 'car-leasing'); ?></a>
                </div>
            </div>
        </div>
    </div>
</section>
