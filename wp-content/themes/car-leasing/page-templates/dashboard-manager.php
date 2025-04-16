<?php
/**
 * Template Name: Manager Dashboard
 *
 * Template for displaying the manager dashboard
 *
 * @package Car_Leasing
 */

// Redirect if user is not logged in or not a manager
if (!is_user_logged_in() || !car_leasing_can_access_dashboard('manager')) {
    wp_redirect(home_url('/login-register/?redirect_to=' . urlencode(home_url('/manager-dashboard/'))));
    exit;
}

$current_user = wp_get_current_user();
$manager_id = $current_user->ID;

// Get assigned client applications
$args = array(
    'post_type' => 'application',
    'posts_per_page' => -1,
    'meta_query' => array(
        array(
            'key' => '_manager_id',
            'value' => $manager_id,
        ),
    ),
);
$applications_query = new WP_Query($args);
$applications_count = $applications_query->found_posts;

// Get pending applications count
$args = array(
    'post_type' => 'application',
    'posts_per_page' => -1,
    'meta_query' => array(
        array(
            'key' => '_manager_id',
            'value' => $manager_id,
        ),
        array(
            'key' => '_application_status',
            'value' => 'pending',
        ),
    ),
);
$pending_query = new WP_Query($args);
$pending_count = $pending_query->found_posts;

// Get assigned clients
$assigned_clients = car_leasing_get_manager_clients($manager_id);
$clients_count = count($assigned_clients);

// Get unread messages
$unread_messages = car_leasing_get_unread_messages_count($manager_id);

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
                                <?php echo get_avatar($manager_id, 60, '', '', array('class' => 'rounded-circle')); ?>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo esc_html($current_user->display_name); ?></h5>
                                <small class="text-muted"><?php esc_html_e('Manager', 'car-leasing'); ?></small>
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
                                    <i class="fas fa-file-alt"></i> <?php esc_html_e('Applications', 'car-leasing'); ?>
                                    <?php if ($pending_count > 0) : ?>
                                        <span class="badge bg-warning float-end"><?php echo esc_html($pending_count); ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#dashboard-clients" data-bs-toggle="tab">
                                    <i class="fas fa-users"></i> <?php esc_html_e('My Clients', 'car-leasing'); ?>
                                    <?php if ($clients_count > 0) : ?>
                                        <span class="badge bg-primary float-end"><?php echo esc_html($clients_count); ?></span>
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
                            <h2 class="h3 mb-0"><?php esc_html_e('Manager Dashboard', 'car-leasing'); ?></h2>
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
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="dashboard-card-title"><?php esc_html_e('Clients', 'car-leasing'); ?></div>
                                    <div class="dashboard-card-value"><?php echo esc_html($clients_count); ?></div>
                                    <a href="#dashboard-clients" data-bs-toggle="tab" class="btn btn-sm btn-outline-primary mt-2"><?php esc_html_e('View All', 'car-leasing'); ?></a>
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
                        </div>
                        
                        <!-- Pending Applications -->
                        <div class="pending-applications mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="h5 mb-0"><?php esc_html_e('Pending Applications', 'car-leasing'); ?></h3>
                                <a href="#dashboard-applications" data-bs-toggle="tab" class="btn btn-sm btn-link"><?php esc_html_e('View All', 'car-leasing'); ?></a>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e('ID', 'car-leasing'); ?></th>
                                            <th><?php esc_html_e('Client', 'car-leasing'); ?></th>
                                            <th><?php esc_html_e('Vehicle', 'car-leasing'); ?></th>
                                            <th><?php esc_html_e('Date', 'car-leasing'); ?></th>
                                            <th><?php esc_html_e('Action', 'car-leasing'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($pending_query->have_posts()) :
                                            while ($pending_query->have_posts()) :
                                                $pending_query->the_post();
                                                $client_id = get_post_meta(get_the_ID(), '_client_id', true);
                                                $client_name = get_userdata($client_id)->display_name;
                                                $vehicle_make = get_post_meta(get_the_ID(), '_vehicle_make', true);
                                                $vehicle_model = get_post_meta(get_the_ID(), '_vehicle_model', true);
                                                $vehicle = $vehicle_make . ' ' . $vehicle_model;
                                                ?>
                                                <tr>
                                                    <td><?php echo esc_html(get_the_ID()); ?></td>
                                                    <td><?php echo esc_html($client_name); ?></td>
                                                    <td><?php echo esc_html($vehicle); ?></td>
                                                    <td><?php echo get_the_date(); ?></td>
                                                    <td>
                                                        <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-outline-primary"><?php esc_html_e('View', 'car-leasing'); ?></a>
                                                        <a href="#" class="btn btn-sm btn-outline-success respond-application" data-id="<?php echo esc_attr(get_the_ID()); ?>" data-client="<?php echo esc_attr($client_name); ?>" data-vehicle="<?php echo esc_attr($vehicle); ?>"><?php esc_html_e('Respond', 'car-leasing'); ?></a>
                                                    </td>
                                                </tr>
                                                <?php
                                            endwhile;
                                            wp_reset_postdata();
                                        else : ?>
                                            <tr>
                                                <td colspan="5" class="text-center"><?php esc_html_e('No pending applications', 'car-leasing'); ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Recent Messages -->
                        <div class="recent-messages">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="h5 mb-0"><?php esc_html_e('Recent Messages', 'car-leasing'); ?></h3>
                                <a href="<?php echo esc_url(home_url('/messages/')); ?>" class="btn btn-sm btn-link"><?php esc_html_e('View All', 'car-leasing'); ?></a>
                            </div>
                            
                            <?php
                            $recent_conversations = car_leasing_get_recent_conversations($manager_id, 3);
                            if (!empty($recent_conversations)) :
                            ?>
                                <div class="list-group">
                                    <?php foreach ($recent_conversations as $conversation) : 
                                        $other_user = get_userdata($conversation->other_user_id);
                                        $unread = $conversation->unread;
                                    ?>
                                        <a href="<?php echo esc_url(home_url('/messages/?user=' . $conversation->other_user_id)); ?>" class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <?php echo get_avatar($conversation->other_user_id, 40, '', '', array('class' => 'rounded-circle me-3')); ?>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo esc_html($other_user->display_name); ?></h6>
                                                        <small class="text-muted"><?php echo esc_html($conversation->last_message_excerpt); ?></small>
                                                    </div>
                                                </div>
                                                <div>
                                                    <small class="text-muted"><?php echo esc_html(human_time_diff(strtotime($conversation->last_message_time), current_time('timestamp'))); ?></small>
                                                    <?php if ($unread > 0) : ?>
                                                        <span class="badge bg-danger rounded-pill ms-2"><?php echo esc_html($unread); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <div class="alert alert-info">
                                    <?php esc_html_e('No recent messages', 'car-leasing'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Applications Tab -->
                    <div class="tab-pane fade" id="dashboard-applications">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="h3 mb-0"><?php esc_html_e('Client Applications', 'car-leasing'); ?></h2>
                        </div>
                        
                        <!-- Filter Controls -->
                        <div class="filter-controls mb-4">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <select id="application-status-filter" class="form-select">
                                        <option value=""><?php esc_html_e('All Statuses', 'car-leasing'); ?></option>
                                        <option value="pending"><?php esc_html_e('Pending', 'car-leasing'); ?></option>
                                        <option value="approved"><?php esc_html_e('Approved', 'car-leasing'); ?></option>
                                        <option value="rejected"><?php esc_html_e('Rejected', 'car-leasing'); ?></option>
                                        <option value="processing"><?php esc_html_e('Processing', 'car-leasing'); ?></option>
                                    </select>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <select id="application-client-filter" class="form-select">
                                        <option value=""><?php esc_html_e('All Clients', 'car-leasing'); ?></option>
                                        <?php foreach ($assigned_clients as $client) : ?>
                                            <option value="<?php echo esc_attr($client->ID); ?>"><?php echo esc_html($client->display_name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <input type="text" id="application-search" class="form-control" placeholder="<?php esc_attr_e('Search applications...', 'car-leasing'); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="applications-table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('ID', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Client', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Vehicle', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Date', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Status', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Action', 'car-leasing'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($applications_query->have_posts()) :
                                        while ($applications_query->have_posts()) :
                                            $applications_query->the_post();
                                            $status = get_post_meta(get_the_ID(), '_application_status', true);
                                            $client_id = get_post_meta(get_the_ID(), '_client_id', true);
                                            $client_name = get_userdata($client_id)->display_name;
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
                                            <tr data-status="<?php echo esc_attr($status); ?>" data-client="<?php echo esc_attr($client_id); ?>">
                                                <td><?php echo esc_html(get_the_ID()); ?></td>
                                                <td><?php echo esc_html($client_name); ?></td>
                                                <td><?php echo esc_html($vehicle); ?></td>
                                                <td><?php echo get_the_date(); ?></td>
                                                <td><span class="badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html(ucfirst($status)); ?></span></td>
                                                <td>
                                                    <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-outline-primary"><?php esc_html_e('View', 'car-leasing'); ?></a>
                                                    
                                                    <?php if ($status === 'pending') : ?>
                                                    <a href="#" class="btn btn-sm btn-outline-success respond-application" data-id="<?php echo esc_attr(get_the_ID()); ?>" data-client="<?php echo esc_attr($client_name); ?>" data-vehicle="<?php echo esc_attr($vehicle); ?>"><?php esc_html_e('Respond', 'car-leasing'); ?></a>
                                                    <?php endif; ?>
                                                    
                                                    <a href="<?php echo esc_url(home_url('/messages/?user=' . $client_id)); ?>" class="btn btn-sm btn-outline-secondary">
                                                        <i class="fas fa-comment"></i>
                                                    </a>
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
                    
                    <!-- Clients Tab -->
                    <div class="tab-pane fade" id="dashboard-clients">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="h3 mb-0"><?php esc_html_e('My Clients', 'car-leasing'); ?></h2>
                        </div>
                        
                        <?php if (!empty($assigned_clients)) : ?>
                            <div class="row">
                                <?php foreach ($assigned_clients as $client) : 
                                    $phone = get_user_meta($client->ID, 'phone', true);
                                    $company = get_user_meta($client->ID, 'company', true);
                                    
                                    // Get client applications count
                                    $args = array(
                                        'post_type' => 'application',
                                        'posts_per_page' => -1,
                                        'meta_query' => array(
                                            array(
                                                'key' => '_client_id',
                                                'value' => $client->ID,
                                            ),
                                            array(
                                                'key' => '_manager_id',
                                                'value' => $manager_id,
                                            ),
                                        ),
                                    );
                                    $client_applications_query = new WP_Query($args);
                                    $client_applications_count = $client_applications_query->found_posts;
                                ?>
                                    <div class="col-md-6 mb-4">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="client-avatar me-3">
                                                        <?php echo get_avatar($client->ID, 60, '', '', array('class' => 'rounded-circle')); ?>
                                                    </div>
                                                    <div>
                                                        <h5 class="card-title mb-0"><?php echo esc_html($client->display_name); ?></h5>
                                                        <?php if ($company) : ?>
                                                            <p class="card-subtitle text-muted"><?php echo esc_html($company); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                
                                                <div class="client-info mb-3">
                                                    <p class="mb-1"><i class="fas fa-envelope me-2 text-muted"></i> <?php echo esc_html($client->user_email); ?></p>
                                                    <?php if ($phone) : ?>
                                                        <p class="mb-1"><i class="fas fa-phone me-2 text-muted"></i> <?php echo esc_html($phone); ?></p>
                                                    <?php endif; ?>
                                                    <p class="mb-0"><i class="fas fa-file-alt me-2 text-muted"></i> <?php echo esc_html($client_applications_count); ?> <?php esc_html_e('applications', 'car-leasing'); ?></p>
                                                </div>
                                                
                                                <div class="client-actions">
                                                    <a href="<?php echo esc_url(home_url('/messages/?user=' . $client->ID)); ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-comment me-1"></i> <?php esc_html_e('Message', 'car-leasing'); ?>
                                                    </a>
                                                    <a href="#" class="btn btn-sm btn-outline-primary view-client-applications" data-client="<?php echo esc_attr($client->ID); ?>">
                                                        <i class="fas fa-file-alt me-1"></i> <?php esc_html_e('Applications', 'car-leasing'); ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else : ?>
                            <div class="alert alert-info">
                                <?php esc_html_e('No clients assigned to you yet.', 'car-leasing'); ?>
                            </div>
                        <?php endif; ?>
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
                                $phone = get_user_meta($manager_id, 'phone', true);
                                $department = get_user_meta($manager_id, 'department', true);
                                $position = get_user_meta($manager_id, 'position', true);
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
                                            <label for="department" class="form-label"><?php esc_html_e('Department', 'car-leasing'); ?></label>
                                            <input type="text" class="form-control" id="department" name="department" value="<?php echo esc_attr($department); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="position" class="form-label"><?php esc_html_e('Position', 'car-leasing'); ?></label>
                                        <input type="text" class="form-control" id="position" name="position" value="<?php echo esc_attr($position); ?>">
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

<!-- Application Response Modal -->
<div class="modal fade" id="applicationResponseModal" tabindex="-1" aria-labelledby="applicationResponseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="applicationResponseModalLabel"><?php esc_html_e('Respond to Application', 'car-leasing'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="respond-application-form">
                    <input type="hidden" id="application-id" name="application_id">
                    
                    <div class="mb-3">
                        <p><strong><?php esc_html_e('Client:', 'car-leasing'); ?></strong> <span id="modal-client-name"></span></p>
                        <p><strong><?php esc_html_e('Vehicle:', 'car-leasing'); ?></strong> <span id="modal-vehicle-name"></span></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><?php esc_html_e('Response', 'car-leasing'); ?></label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="response-type" id="response-approve" value="approved" checked>
                            <label class="form-check-label" for="response-approve">
                                <?php esc_html_e('Approve', 'car-leasing'); ?>
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="response-type" id="response-process" value="processing">
                            <label class="form-check-label" for="response-process">
                                <?php esc_html_e('Processing (Need more info)', 'car-leasing'); ?>
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="response-type" id="response-reject" value="rejected">
                            <label class="form-check-label" for="response-reject">
                                <?php esc_html_e('Reject', 'car-leasing'); ?>
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="response-note" class="form-label"><?php esc_html_e('Note (Optional)', 'car-leasing'); ?></label>
                        <textarea class="form-control" id="response-note" name="response-note" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php esc_html_e('Cancel', 'car-leasing'); ?></button>
                <button type="button" class="btn btn-primary" id="submit-response"><?php esc_html_e('Submit Response', 'car-leasing'); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Application filters
    $('#application-status-filter, #application-client-filter').on('change', function() {
        var statusFilter = $('#application-status-filter').val();
        var clientFilter = $('#application-client-filter').val();
        
        $('#applications-table tbody tr').each(function() {
            var row = $(this);
            var showRow = true;
            
            if (statusFilter && row.data('status') !== statusFilter) {
                showRow = false;
            }
            
            if (clientFilter && row.data('client') !== clientFilter) {
                showRow = false;
            }
            
            if (showRow) {
                row.show();
            } else {
                row.hide();
            }
        });
    });
    
    // Application search
    $('#application-search').on('keyup', function() {
        var searchText = $(this).val().toLowerCase();
        
        $('#applications-table tbody tr').each(function() {
            var rowText = $(this).text().toLowerCase();
            if (rowText.indexOf(searchText) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // View client applications button
    $('.view-client-applications').on('click', function(e) {
        e.preventDefault();
        var clientId = $(this).data('client');
        
        // Switch to applications tab
        $('a[href="#dashboard-applications"]').tab('show');
        
        // Apply client filter
        $('#application-client-filter').val(clientId).trigger('change');
    });
    
    // Respond to application modal
    $('.respond-application').on('click', function(e) {
        e.preventDefault();
        var applicationId = $(this).data('id');
        var clientName = $(this).data('client');
        var vehicleName = $(this).data('vehicle');
        
        $('#application-id').val(applicationId);
        $('#modal-client-name').text(clientName);
        $('#modal-vehicle-name').text(vehicleName);
        
        var modal = new bootstrap.Modal(document.getElementById('applicationResponseModal'));
        modal.show();
    });
    
    // Submit application response
    $('#submit-response').on('click', function() {
        var applicationId = $('#application-id').val();
        var responseType = $('input[name="response-type"]:checked').val();
        var responseNote = $('#response-note').val();
        
        // Send AJAX request
        $.ajax({
            url: car_leasing_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'car_leasing_respond_application',
                application_id: applicationId,
                response_type: responseType,
                response_note: responseNote,
                security: car_leasing_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Close modal
                    var modal = bootstrap.Modal.getInstance(document.getElementById('applicationResponseModal'));
                    modal.hide();
                    
                    // Show success message and reload page
                    alert(response.data.message || '<?php esc_attr_e('Response submitted successfully', 'car-leasing'); ?>');
                    location.reload();
                } else {
                    alert(response.data.message || '<?php esc_attr_e('An error occurred while submitting the response', 'car-leasing'); ?>');
                }
            },
            error: function() {
                alert('<?php esc_attr_e('An error occurred. Please try again later.', 'car-leasing'); ?>');
            }
        });
    });
});
</script>

<?php
get_footer();
