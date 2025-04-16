<?php
/**
 * Template Name: Client Dashboard
 *
 * Template for displaying the client dashboard
 *
 * @package Car_Leasing
 */

// Redirect if user is not logged in or not a client
if (!is_user_logged_in() || !car_leasing_can_access_dashboard('client')) {
    wp_redirect(home_url('/login-register/?redirect_to=' . urlencode(home_url('/client-dashboard/'))));
    exit;
}

$current_user = wp_get_current_user();
$client_id = $current_user->ID;

// Get client applications
$args = array(
    'post_type' => 'application',
    'posts_per_page' => -1,
    'meta_query' => array(
        array(
            'key' => '_client_id',
            'value' => $client_id,
        ),
    ),
);
$applications_query = new WP_Query($args);
$applications_count = $applications_query->found_posts;

// Get client messages
$unread_messages = car_leasing_get_unread_messages_count($client_id);

get_header();
?>

<div class="dashboard-wrapper py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 mb-4">
                <div class="dashboard-sidebar">
                    <div class="user-info mb-4">
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-3">
                                <?php echo get_avatar($client_id, 60, '', '', array('class' => 'rounded-circle')); ?>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo esc_html($current_user->display_name); ?></h5>
                                <small class="text-muted"><?php esc_html_e('Client', 'car-leasing'); ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <nav class="dashboard-nav">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link active" href="#dashboard-overview" data-bs-toggle="tab">
                                    <i class="fas fa-tachometer-alt"></i> <?php esc_html_e('Dashboard', 'car-leasing'); ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#dashboard-applications" data-bs-toggle="tab">
                                    <i class="fas fa-file-alt"></i> <?php esc_html_e('My Applications', 'car-leasing'); ?>
                                    <?php if ($applications_count > 0) : ?>
                                        <span class="badge bg-primary float-end"><?php echo esc_html($applications_count); ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo esc_url(home_url('/messages/')); ?>">
                                    <i class="fas fa-envelope"></i> <?php esc_html_e('Messages', 'car-leasing'); ?>
                                    <?php if ($unread_messages > 0) : ?>
                                        <span class="badge bg-danger float-end"><?php echo esc_html($unread_messages); ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#dashboard-profile" data-bs-toggle="tab">
                                    <i class="fas fa-user"></i> <?php esc_html_e('Profile', 'car-leasing'); ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo esc_url(home_url('/application-form/')); ?>">
                                    <i class="fas fa-plus-circle"></i> <?php esc_html_e('New Application', 'car-leasing'); ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo esc_url(wp_logout_url(home_url())); ?>">
                                    <i class="fas fa-sign-out-alt"></i> <?php esc_html_e('Logout', 'car-leasing'); ?>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
            
            <div class="col-lg-9">
                <div class="dashboard-content tab-content">
                    <!-- Dashboard Overview Tab -->
                    <div class="tab-pane fade show active" id="dashboard-overview">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="h3 mb-0"><?php esc_html_e('Dashboard Overview', 'car-leasing'); ?></h2>
                            <div class="date"><?php echo date_i18n(get_option('date_format')); ?></div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <div class="dashboard-card">
                                    <div class="dashboard-card-icon">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div class="dashboard-card-title"><?php esc_html_e('Applications', 'car-leasing'); ?></div>
                                    <div class="dashboard-card-value"><?php echo esc_html($applications_count); ?></div>
                                    <a href="#dashboard-applications" data-bs-toggle="tab" class="btn btn-sm btn-outline-primary mt-2"><?php esc_html_e('View All', 'car-leasing'); ?></a>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="dashboard-card">
                                    <div class="dashboard-card-icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div class="dashboard-card-title"><?php esc_html_e('Messages', 'car-leasing'); ?></div>
                                    <div class="dashboard-card-value"><?php echo esc_html($unread_messages); ?></div>
                                    <a href="<?php echo esc_url(home_url('/messages/')); ?>" class="btn btn-sm btn-outline-primary mt-2"><?php esc_html_e('View Messages', 'car-leasing'); ?></a>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="dashboard-card">
                                    <div class="dashboard-card-icon">
                                        <i class="fas fa-car"></i>
                                    </div>
                                    <div class="dashboard-card-title"><?php esc_html_e('Browse Vehicles', 'car-leasing'); ?></div>
                                    <a href="<?php echo esc_url(home_url('/vehicle-marketplace/')); ?>" class="btn btn-sm btn-outline-primary mt-2"><?php esc_html_e('View Marketplace', 'car-leasing'); ?></a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recent Applications -->
                        <div class="recent-applications mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="h5 mb-0"><?php esc_html_e('Recent Applications', 'car-leasing'); ?></h3>
                                <a href="#dashboard-applications" data-bs-toggle="tab" class="btn btn-sm btn-link"><?php esc_html_e('View All', 'car-leasing'); ?></a>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e('ID', 'car-leasing'); ?></th>
                                            <th><?php esc_html_e('Vehicle', 'car-leasing'); ?></th>
                                            <th><?php esc_html_e('Date', 'car-leasing'); ?></th>
                                            <th><?php esc_html_e('Status', 'car-leasing'); ?></th>
                                            <th><?php esc_html_e('Action', 'car-leasing'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($applications_query->have_posts()) :
                                            $count = 0;
                                            while ($applications_query->have_posts() && $count < 5) :
                                                $applications_query->the_post();
                                                $count++;
                                                $status = get_post_meta(get_the_ID(), '_application_status', true);
                                                $vehicle_make = get_post_meta(get_the_ID(), '_vehicle_make', true);
                                                $vehicle_model = get_post_meta(get_the_ID(), '_vehicle_model', true);
                                                $vehicle = $vehicle_make . ' ' . $vehicle_model;
                                                
                                                // Set status class
                                                $status_class = '';
                                                switch ($status) {
                                                    case 'pending':
                                                        $status_class = 'bg-warning';
                                                        break;
                                                    case 'approved':
                                                        $status_class = 'bg-success';
                                                        break;
                                                    case 'rejected':
                                                        $status_class = 'bg-danger';
                                                        break;
                                                    case 'processing':
                                                        $status_class = 'bg-info';
                                                        break;
                                                    default:
                                                        $status_class = 'bg-secondary';
                                                }
                                                ?>
                                                <tr>
                                                    <td><?php echo esc_html(get_the_ID()); ?></td>
                                                    <td><?php echo esc_html($vehicle); ?></td>
                                                    <td><?php echo get_the_date(); ?></td>
                                                    <td><span class="badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html(ucfirst($status)); ?></span></td>
                                                    <td>
                                                        <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-outline-primary"><?php esc_html_e('View', 'car-leasing'); ?></a>
                                                    </td>
                                                </tr>
                                                <?php
                                            endwhile;
                                            wp_reset_postdata();
                                        else : ?>
                                            <tr>
                                                <td colspan="5" class="text-center"><?php esc_html_e('No applications found', 'car-leasing'); ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Quick Links -->
                        <div class="quick-links">
                            <h3 class="h5 mb-3"><?php esc_html_e('Quick Links', 'car-leasing'); ?></h3>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <a href="<?php echo esc_url(home_url('/application-form/')); ?>" class="btn btn-primary w-100">
                                        <i class="fas fa-plus-circle me-2"></i> <?php esc_html_e('New Application', 'car-leasing'); ?>
                                    </a>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <a href="<?php echo esc_url(home_url('/vehicle-marketplace/')); ?>" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-car me-2"></i> <?php esc_html_e('Browse Vehicles', 'car-leasing'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Applications Tab -->
                    <div class="tab-pane fade" id="dashboard-applications">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="h3 mb-0"><?php esc_html_e('My Applications', 'car-leasing'); ?></h2>
                            <a href="<?php echo esc_url(home_url('/application-form/')); ?>" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i> <?php esc_html_e('New Application', 'car-leasing'); ?>
                            </a>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('ID', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Vehicle', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Date', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Status', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Manager', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Action', 'car-leasing'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($applications_query->have_posts()) :
                                        while ($applications_query->have_posts()) :
                                            $applications_query->the_post();
                                            $status = get_post_meta(get_the_ID(), '_application_status', true);
                                            $vehicle_make = get_post_meta(get_the_ID(), '_vehicle_make', true);
                                            $vehicle_model = get_post_meta(get_the_ID(), '_vehicle_model', true);
                                            $vehicle = $vehicle_make . ' ' . $vehicle_model;
                                            $manager_id = get_post_meta(get_the_ID(), '_manager_id', true);
                                            $manager_name = $manager_id ? get_userdata($manager_id)->display_name : __('Not assigned', 'car-leasing');
                                            
                                            // Set status class
                                            $status_class = '';
                                            switch ($status) {
                                                case 'pending':
                                                    $status_class = 'bg-warning';
                                                    break;
                                                case 'approved':
                                                    $status_class = 'bg-success';
                                                    break;
                                                case 'rejected':
                                                    $status_class = 'bg-danger';
                                                    break;
                                                case 'processing':
                                                    $status_class = 'bg-info';
                                                    break;
                                                default:
                                                    $status_class = 'bg-secondary';
                                            }
                                            ?>
                                            <tr>
                                                <td><?php echo esc_html(get_the_ID()); ?></td>
                                                <td><?php echo esc_html($vehicle); ?></td>
                                                <td><?php echo get_the_date(); ?></td>
                                                <td><span class="badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html(ucfirst($status)); ?></span></td>
                                                <td><?php echo esc_html($manager_name); ?></td>
                                                <td>
                                                    <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-outline-primary"><?php esc_html_e('View', 'car-leasing'); ?></a>
                                                    
                                                    <?php if ($manager_id) : ?>
                                                        <a href="<?php echo esc_url(home_url('/messages/?user=' . $manager_id)); ?>" class="btn btn-sm btn-outline-secondary">
                                                            <i class="fas fa-comment"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php
                                        endwhile;
                                        wp_reset_postdata();
                                    else : ?>
                                        <tr>
                                            <td colspan="6" class="text-center"><?php esc_html_e('No applications found', 'car-leasing'); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Profile Tab -->
                    <div class="tab-pane fade" id="dashboard-profile">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="h3 mb-0"><?php esc_html_e('My Profile', 'car-leasing'); ?></h2>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <?php
                                // Get user meta
                                $phone = get_user_meta($client_id, 'phone', true);
                                $company = get_user_meta($client_id, 'company', true);
                                $address = get_user_meta($client_id, 'address', true);
                                ?>
                                
                                <form id="update-profile-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                    <input type="hidden" name="action" value="car_leasing_update_profile">
                                    <?php wp_nonce_field('car_leasing_update_profile_nonce', 'profile_nonce'); ?>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3">
                                            <label for="display_name" class="form-label"><?php esc_html_e('Full Name', 'car-leasing'); ?></label>
                                            <input type="text" class="form-control" id="display_name" name="display_name" value="<?php echo esc_attr($current_user->display_name); ?>" required>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="user_email" class="form-label"><?php esc_html_e('Email', 'car-leasing'); ?></label>
                                            <input type="email" class="form-control" id="user_email" name="user_email" value="<?php echo esc_attr($current_user->user_email); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label"><?php esc_html_e('Phone', 'car-leasing'); ?></label>
                                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo esc_attr($phone); ?>">
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="company" class="form-label"><?php esc_html_e('Company', 'car-leasing'); ?></label>
                                            <input type="text" class="form-control" id="company" name="company" value="<?php echo esc_attr($company); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="address" class="form-label"><?php esc_html_e('Address', 'car-leasing'); ?></label>
                                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo esc_textarea($address); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label"><?php esc_html_e('New Password', 'car-leasing'); ?></label>
                                        <div class="password-toggle-wrapper">
                                            <input type="password" class="form-control" id="password" name="password">
                                            <span class="toggle-password" data-toggle="#password">
                                                <i class="fas fa-eye"></i>
                                            </span>
                                        </div>
                                        <small class="form-text text-muted"><?php esc_html_e('Leave blank to keep current password', 'car-leasing'); ?></small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password_confirm" class="form-label"><?php esc_html_e('Confirm New Password', 'car-leasing'); ?></label>
                                        <div class="password-toggle-wrapper">
                                            <input type="password" class="form-control" id="password_confirm" name="password_confirm">
                                            <span class="toggle-password" data-toggle="#password_confirm">
                                                <i class="fas fa-eye"></i>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary"><?php esc_html_e('Update Profile', 'car-leasing'); ?></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();
