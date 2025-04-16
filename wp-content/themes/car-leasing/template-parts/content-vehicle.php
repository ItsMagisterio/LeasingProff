<?php
/**
 * Template part for displaying vehicle posts
 *
 * @package Car_Leasing
 */
?>

<div class="col-md-4 mb-4">
    <div class="card vehicle-card h-100">
        <?php if (has_post_thumbnail()) : ?>
            <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail('medium', array('class' => 'card-img-top')); ?>
            </a>
        <?php else : ?>
            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                <i class="fas fa-car fa-3x text-secondary"></i>
            </div>
        <?php endif; ?>
        
        <div class="card-body">
            <h5 class="card-title">
                <a href="<?php the_permalink(); ?>" class="text-dark text-decoration-none"><?php the_title(); ?></a>
            </h5>
            
            <p class="card-text">
                <?php 
                // Display vehicle year if available
                $vehicle_year = get_post_meta(get_the_ID(), '_vehicle_year', true);
                if ($vehicle_year) :
                ?>
                    <span class="badge bg-primary"><?php echo esc_html($vehicle_year); ?></span>
                <?php endif; ?>
                
                <?php 
                // Display vehicle price if available
                $vehicle_price = get_post_meta(get_the_ID(), '_vehicle_price', true);
                if ($vehicle_price) :
                ?>
                    <span class="ms-2"><i class="fas fa-tag"></i> <?php echo esc_html(number_format($vehicle_price)); ?> ₽</span>
                <?php endif; ?>
            </p>
            
            <?php
            // Display additional vehicle details if available
            $vehicle_mileage = get_post_meta(get_the_ID(), '_vehicle_mileage', true);
            $vehicle_fuel = get_post_meta(get_the_ID(), '_vehicle_fuel', true);
            if ($vehicle_mileage || $vehicle_fuel) :
            ?>
                <div class="vehicle-details d-flex flex-wrap mt-3 mb-2">
                    <?php if ($vehicle_mileage) : ?>
                        <div class="me-3">
                            <i class="fas fa-tachometer-alt me-1 text-secondary"></i> <?php echo esc_html(number_format($vehicle_mileage)); ?> km
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($vehicle_fuel) : ?>
                        <div>
                            <i class="fas fa-gas-pump me-1 text-secondary"></i> <?php echo esc_html($vehicle_fuel); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php
            // Get excerpt or generate one from content
            if (has_excerpt()) {
                echo '<p class="card-text">' . get_the_excerpt() . '</p>';
            } else {
                echo '<p class="card-text">' . wp_trim_words(get_the_content(), 10, '...') . '</p>';
            }
            ?>
        </div>
        
        <div class="card-footer bg-white border-top-0">
            <a href="<?php the_permalink(); ?>" class="btn btn-outline-primary btn-sm"><?php esc_html_e('View Details', 'car-leasing'); ?></a>
            <a href="<?php echo esc_url(home_url('/application-form/')); ?>" class="btn btn-primary btn-sm float-end"><?php esc_html_e('Inquire', 'car-leasing'); ?></a>
        </div>
    </div>
</div>
