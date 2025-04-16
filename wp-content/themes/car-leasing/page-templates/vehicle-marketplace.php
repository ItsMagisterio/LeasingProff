<?php
/**
 * Template Name: Vehicle Marketplace
 *
 * Template for displaying the vehicle marketplace
 *
 * @package Car_Leasing
 */

get_header();

// Get vehicle makes for filter
$args = array(
    'post_type' => 'vehicle',
    'posts_per_page' => -1,
);
$vehicles_query = new WP_Query($args);

$makes = array();
$years = array();
$price_ranges = array(
    '0-500000' => __('Up to 500,000 ₽', 'car-leasing'),
    '500000-1000000' => __('500,000 - 1,000,000 ₽', 'car-leasing'),
    '1000000-2000000' => __('1,000,000 - 2,000,000 ₽', 'car-leasing'),
    '2000000-3000000' => __('2,000,000 - 3,000,000 ₽', 'car-leasing'),
    '3000000-5000000' => __('3,000,000 - 5,000,000 ₽', 'car-leasing'),
    '5000000-10000000' => __('5,000,000 - 10,000,000 ₽', 'car-leasing'),
    '10000000-999999999' => __('10,000,000 ₽ and up', 'car-leasing'),
);

// Get unique makes and years
if ($vehicles_query->have_posts()) {
    while ($vehicles_query->have_posts()) {
        $vehicles_query->the_post();
        
        $make = get_post_meta(get_the_ID(), '_vehicle_make', true);
        $year = get_post_meta(get_the_ID(), '_vehicle_year', true);
        
        if ($make && !in_array($make, $makes)) {
            $makes[] = $make;
        }
        
        if ($year && !in_array($year, $years)) {
            $years[] = $year;
        }
    }
    wp_reset_postdata();
    
    sort($makes);
    rsort($years);
}

// Get search and filter parameters
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$make_filter = isset($_GET['make']) ? sanitize_text_field($_GET['make']) : '';
$year_filter = isset($_GET['year']) ? sanitize_text_field($_GET['year']) : '';
$price_filter = isset($_GET['price']) ? sanitize_text_field($_GET['price']) : '';
$sort_by = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'newest';

// Build query arguments
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$per_page = 12;

$args = array(
    'post_type' => 'vehicle',
    'posts_per_page' => $per_page,
    'paged' => $paged,
);

// Add search
if (!empty($search_query)) {
    $args['s'] = $search_query;
}

// Add meta queries
$meta_query = array();

// Make filter
if (!empty($make_filter)) {
    $meta_query[] = array(
        'key' => '_vehicle_make',
        'value' => $make_filter,
        'compare' => '=',
    );
}

// Year filter
if (!empty($year_filter)) {
    $meta_query[] = array(
        'key' => '_vehicle_year',
        'value' => $year_filter,
        'compare' => '=',
    );
}

// Price filter
if (!empty($price_filter)) {
    $price_range = explode('-', $price_filter);
    if (count($price_range) === 2) {
        $meta_query[] = array(
            'key' => '_vehicle_price',
            'value' => array($price_range[0], $price_range[1]),
            'type' => 'NUMERIC',
            'compare' => 'BETWEEN',
        );
    }
}

if (!empty($meta_query)) {
    $args['meta_query'] = $meta_query;
}

// Sorting
switch ($sort_by) {
    case 'price_low':
        $args['meta_key'] = '_vehicle_price';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'ASC';
        break;
    case 'price_high':
        $args['meta_key'] = '_vehicle_price';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
        break;
    case 'oldest':
        $args['orderby'] = 'date';
        $args['order'] = 'ASC';
        break;
    case 'newest':
    default:
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
        break;
}

$vehicles = new WP_Query($args);
?>

<div class="page-banner bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto text-center">
                <h1 class="page-title"><?php echo get_the_title(); ?></h1>
                <p class="lead"><?php esc_html_e('Browse our selection of quality vehicles available for leasing', 'car-leasing'); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><?php esc_html_e('Filters', 'car-leasing'); ?></h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo esc_url(get_permalink()); ?>" method="get" id="vehicle-filter-form">
                        <div class="mb-3">
                            <label for="search" class="form-label"><?php esc_html_e('Search', 'car-leasing'); ?></label>
                            <input type="text" class="form-control" id="search" name="search" placeholder="<?php esc_attr_e('Search vehicles...', 'car-leasing'); ?>" value="<?php echo esc_attr($search_query); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="make" class="form-label"><?php esc_html_e('Make', 'car-leasing'); ?></label>
                            <select class="form-select" id="make" name="make">
                                <option value=""><?php esc_html_e('All Makes', 'car-leasing'); ?></option>
                                <?php foreach ($makes as $make) : ?>
                                    <option value="<?php echo esc_attr($make); ?>" <?php selected($make_filter, $make); ?>><?php echo esc_html($make); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="year" class="form-label"><?php esc_html_e('Year', 'car-leasing'); ?></label>
                            <select class="form-select" id="year" name="year">
                                <option value=""><?php esc_html_e('All Years', 'car-leasing'); ?></option>
                                <?php foreach ($years as $year) : ?>
                                    <option value="<?php echo esc_attr($year); ?>" <?php selected($year_filter, $year); ?>><?php echo esc_html($year); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="price" class="form-label"><?php esc_html_e('Price Range', 'car-leasing'); ?></label>
                            <select class="form-select" id="price" name="price">
                                <option value=""><?php esc_html_e('All Prices', 'car-leasing'); ?></option>
                                <?php foreach ($price_ranges as $range => $label) : ?>
                                    <option value="<?php echo esc_attr($range); ?>" <?php selected($price_filter, $range); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="sort" class="form-label"><?php esc_html_e('Sort By', 'car-leasing'); ?></label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="newest" <?php selected($sort_by, 'newest'); ?>><?php esc_html_e('Newest First', 'car-leasing'); ?></option>
                                <option value="oldest" <?php selected($sort_by, 'oldest'); ?>><?php esc_html_e('Oldest First', 'car-leasing'); ?></option>
                                <option value="price_low" <?php selected($sort_by, 'price_low'); ?>><?php esc_html_e('Price: Low to High', 'car-leasing'); ?></option>
                                <option value="price_high" <?php selected($sort_by, 'price_high'); ?>><?php esc_html_e('Price: High to Low', 'car-leasing'); ?></option>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary"><?php esc_html_e('Apply Filters', 'car-leasing'); ?></button>
                            <a href="<?php echo esc_url(get_permalink()); ?>" class="btn btn-outline-secondary"><?php esc_html_e('Reset Filters', 'car-leasing'); ?></a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><?php esc_html_e('Need Help?', 'car-leasing'); ?></h5>
                </div>
                <div class="card-body">
                    <p><?php esc_html_e('Contact our leasing specialists for assistance in finding the perfect vehicle for your needs.', 'car-leasing'); ?></p>
                    <div class="d-grid">
                        <a href="<?php echo esc_url(home_url('/contact-us/')); ?>" class="btn btn-outline-primary"><?php esc_html_e('Contact Us', 'car-leasing'); ?></a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Vehicles Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-0"><?php esc_html_e('Available Vehicles', 'car-leasing'); ?></h2>
                    <p class="text-muted mb-0"><?php echo sprintf(_n('%s vehicle found', '%s vehicles found', $vehicles->found_posts, 'car-leasing'), number_format($vehicles->found_posts)); ?></p>
                </div>
                
                <div>
                    <a href="<?php echo esc_url(home_url('/application-form/')); ?>" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i> <?php esc_html_e('Apply for Leasing', 'car-leasing'); ?>
                    </a>
                </div>
            </div>
            
            <?php if ($vehicles->have_posts()) : ?>
                <div class="row">
                    <?php 
                    while ($vehicles->have_posts()) : 
                        $vehicles->the_post();
                        get_template_part('template-parts/content', 'vehicle');
                    endwhile; 
                    wp_reset_postdata();
                    ?>
                </div>
                
                <div class="pagination-wrapper mt-4">
                    <?php
                    $big = 999999999; // need an unlikely integer
                    echo paginate_links(array(
                        'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                        'format' => '?paged=%#%',
                        'current' => max(1, get_query_var('paged')),
                        'total' => $vehicles->max_num_pages,
                        'prev_text' => '<i class="fas fa-angle-left"></i> ' . __('Previous', 'car-leasing'),
                        'next_text' => __('Next', 'car-leasing') . ' <i class="fas fa-angle-right"></i>',
                        'type' => 'list',
                        'end_size' => 3,
                        'mid_size' => 2
                    ));
                    ?>
                </div>
            <?php else : ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> <?php esc_html_e('No vehicles found matching your criteria. Please try different filters or check back later.', 'car-leasing'); ?>
                </div>
            <?php endif; ?>
            
            <div class="marketplace-info mt-5">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h3 class="h5"><?php esc_html_e('About Our Vehicle Marketplace', 'car-leasing'); ?></h3>
                        <p><?php esc_html_e('Our marketplace features vehicles from various sources, including vehicles from our own fleet and those repossessed from other leasing companies. All vehicles undergo a thorough inspection and are offered at competitive rates.', 'car-leasing'); ?></p>
                        <p><?php esc_html_e('When you find a vehicle you\'re interested in, simply submit a leasing application, and one of our managers will contact you to discuss the terms and answer any questions you may have.', 'car-leasing'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Call to Action Section -->
<section class="cta-section bg-primary text-white py-5 mt-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 text-center">
                <h2 class="mb-3"><?php esc_html_e('Ready to Drive Your Dream Car?', 'car-leasing'); ?></h2>
                <p class="lead mb-4"><?php esc_html_e('Apply for leasing today and get a quick response from our specialists.', 'car-leasing'); ?></p>
                <div>
                    <a href="<?php echo esc_url(home_url('/application-form/')); ?>" class="btn btn-light btn-lg me-3 mb-2"><?php esc_html_e('Apply Now', 'car-leasing'); ?></a>
                    <a href="<?php echo esc_url(home_url('/contact-us/')); ?>" class="btn btn-outline-light btn-lg mb-2"><?php esc_html_e('Contact Us', 'car-leasing'); ?></a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
jQuery(document).ready(function($) {
    // Update filters when sort is changed
    $('#sort').on('change', function() {
        $('#vehicle-filter-form').submit();
    });
});
</script>

<?php
get_footer();
