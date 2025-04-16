<?php
/**
 * The template for displaying the front page
 *
 * @package Car_Leasing
 */

get_header();
?>

<main id="primary" class="site-main">
    <?php
    // Hero Section
    get_template_part('template-parts/hero-section');
    
    // Features Section
    get_template_part('template-parts/features-section');
    
    // About Section
    get_template_part('template-parts/about-section');
    
    // Call to Action Section
    get_template_part('template-parts/cta-section');
    ?>
    
    <!-- Vehicle Marketplace Section -->
    <section class="vehicle-marketplace py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title"><?php esc_html_e('Explore Our Vehicle Marketplace', 'car-leasing'); ?></h2>
                    <p class="section-subtitle"><?php esc_html_e('Find quality vehicles from various leasing companies', 'car-leasing'); ?></p>
                </div>
            </div>
            
            <div class="row">
                <?php
                // Get 3 featured vehicles
                $args = array(
                    'post_type'      => 'vehicle',
                    'posts_per_page' => 3,
                    'meta_query'     => array(
                        array(
                            'key'   => '_vehicle_featured',
                            'value' => '1',
                        ),
                    ),
                );
                
                $query = new WP_Query($args);
                
                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                        get_template_part('template-parts/content', 'vehicle');
                    endwhile;
                    wp_reset_postdata();
                else :
                    // If no featured vehicles, display mock data for demonstration
                    $mock_vehicles = array(
                        array(
                            'title' => 'Mercedes-Benz E-Class',
                            'price' => '65,000',
                            'year'  => '2022',
                            'image' => 'https://images.unsplash.com/photo-1485463611174-f302f6a5c1c9'
                        ),
                        array(
                            'title' => 'BMW 5 Series',
                            'price' => '58,500',
                            'year'  => '2023',
                            'image' => 'https://images.unsplash.com/photo-1558368718-808f08b6d9a8'
                        ),
                        array(
                            'title' => 'Audi A6',
                            'price' => '61,200',
                            'year'  => '2022',
                            'image' => 'https://images.unsplash.com/photo-1498887960847-2a5e46312788'
                        )
                    );
                    
                    foreach ($mock_vehicles as $vehicle) :
                    ?>
                    <div class="col-md-4 mb-4">
                        <div class="card vehicle-card h-100">
                            <img src="<?php echo esc_url($vehicle['image']); ?>" class="card-img-top" alt="<?php echo esc_attr($vehicle['title']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo esc_html($vehicle['title']); ?></h5>
                                <p class="card-text">
                                    <span class="badge bg-primary"><?php echo esc_html($vehicle['year']); ?></span>
                                    <span class="ms-2"><i class="fas fa-tag"></i> <?php echo esc_html($vehicle['price']); ?> ₽</span>
                                </p>
                            </div>
                            <div class="card-footer bg-white border-top-0">
                                <a href="<?php echo esc_url(home_url('/vehicle-marketplace/')); ?>" class="btn btn-outline-primary btn-sm"><?php esc_html_e('View Details', 'car-leasing'); ?></a>
                                <a href="<?php echo esc_url(home_url('/application-form/')); ?>" class="btn btn-primary btn-sm float-end"><?php esc_html_e('Inquire', 'car-leasing'); ?></a>
                            </div>
                        </div>
                    </div>
                    <?php
                    endforeach;
                endif;
                ?>
            </div>
            
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <a href="<?php echo esc_url(home_url('/vehicle-marketplace/')); ?>" class="btn btn-lg btn-outline-primary"><?php esc_html_e('Browse All Vehicles', 'car-leasing'); ?></a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Testimonials Section -->
    <section class="testimonials py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title"><?php esc_html_e('What Our Clients Say', 'car-leasing'); ?></h2>
                    <p class="section-subtitle"><?php esc_html_e('Hear from our satisfied customers', 'car-leasing'); ?></p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card p-4 bg-white shadow-sm h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="testimonial-avatar me-3">
                                <i class="fas fa-user-circle fa-3x text-primary"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Ivan Petrov</h5>
                                <small class="text-muted">Business Owner</small>
                            </div>
                        </div>
                        <p class="testimonial-text">"The leasing process was smooth and transparent. I got a great deal on my company fleet and the customer service was exceptional."</p>
                        <div class="testimonial-rating text-primary">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card p-4 bg-white shadow-sm h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="testimonial-avatar me-3">
                                <i class="fas fa-user-circle fa-3x text-primary"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Maria Ivanova</h5>
                                <small class="text-muted">Doctor</small>
                            </div>
                        </div>
                        <p class="testimonial-text">"I was looking for a luxury car and couldn't afford to buy one outright. Car Leasing offered me flexible terms that fit my budget perfectly."</p>
                        <div class="testimonial-rating text-primary">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card p-4 bg-white shadow-sm h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="testimonial-avatar me-3">
                                <i class="fas fa-user-circle fa-3x text-primary"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Alexei Smirnov</h5>
                                <small class="text-muted">IT Specialist</small>
                            </div>
                        </div>
                        <p class="testimonial-text">"The manager assigned to me was very knowledgeable and helped me understand all aspects of the leasing contract. Highly recommended!"</p>
                        <div class="testimonial-rating text-primary">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Partners Section -->
    <section class="partners py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title"><?php esc_html_e('Our Partners', 'car-leasing'); ?></h2>
                    <p class="section-subtitle"><?php esc_html_e('We work with the best in the industry', 'car-leasing'); ?></p>
                </div>
            </div>
            
            <div class="row align-items-center justify-content-center">
                <div class="col-4 col-md-2 mb-4 text-center">
                    <div class="partner-logo">
                        <i class="fab fa-cc-visa fa-4x text-secondary"></i>
                    </div>
                </div>
                <div class="col-4 col-md-2 mb-4 text-center">
                    <div class="partner-logo">
                        <i class="fab fa-cc-mastercard fa-4x text-secondary"></i>
                    </div>
                </div>
                <div class="col-4 col-md-2 mb-4 text-center">
                    <div class="partner-logo">
                        <i class="fab fa-apple-pay fa-4x text-secondary"></i>
                    </div>
                </div>
                <div class="col-4 col-md-2 mb-4 text-center">
                    <div class="partner-logo">
                        <i class="fab fa-google-pay fa-4x text-secondary"></i>
                    </div>
                </div>
                <div class="col-4 col-md-2 mb-4 text-center">
                    <div class="partner-logo">
                        <i class="fas fa-university fa-4x text-secondary"></i>
                    </div>
                </div>
                <div class="col-4 col-md-2 mb-4 text-center">
                    <div class="partner-logo">
                        <i class="fas fa-shield-alt fa-4x text-secondary"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();
