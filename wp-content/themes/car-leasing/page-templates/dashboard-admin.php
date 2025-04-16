<?php
/**
 * Template Name: Admin Dashboard
 *
 * Template for displaying the administrator dashboard
 *
 * @package Car_Leasing
 */

// Redirect if user is not logged in or not an administrator
if (!is_user_logged_in() || !car_leasing_can_access_dashboard('admin')) {
    wp_redirect(home_url('/login-register/?redirect_to=' . urlencode(home_url('/admin-dashboard/'))));
    exit;
}

$current_user = wp_get_current_user();
$admin_id = $current_user->ID;

// Get all applications count
$args = array(
    'post_type' => 'application',
    'posts_per_page' => -1,
);
$applications_query = new WP_Query($args);
$applications_count = $applications_query->found_posts;

// Get pending applications count
$args = array(
    'post_type' => 'application',
    'posts_per_page' => -1,
    'meta_query' => array(
        array(
            'key' => '_application_status',
            'value' => 'pending',
        ),
        array(
            'key' => '_manager_id',
            'compare' => 'NOT EXISTS',
        ),
    ),
);
$pending_query = new WP_Query($args);
$pending_count = $pending_query->found_posts;

// Get clients count
$clients = get_users(array('role' => 'car_leasing_client'));
$clients_count = count($clients);

// Get managers count
$managers = get_users(array('role' => 'car_leasing_manager'));
$managers_count = count($managers);

// Get vehicles count
$args = array(
    'post_type' => 'vehicle',
    'posts_per_page' => -1,
);
$vehicles_query = new WP_Query($args);
$vehicles_count = $vehicles_query->found_posts;

// Get unread messages
$unread_messages = car_leasing_get_unread_messages_count($admin_id);

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
                                <?php echo get_avatar($admin_id, 60, '', '', array('class' => 'rounded-circle')); ?>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo esc_html($current_user->display_name); ?></h5>
                                <small class="text-muted"><?php esc_html_e('Administrator', 'car-leasing'); ?></small>
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
                                    <i class="fas fa-users"></i> <?php esc_html_e('Clients', 'car-leasing'); ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#dashboard-managers" data-bs-toggle="tab">
                                    <i class="fas fa-user-tie"></i> <?php esc_html_e('Managers', 'car-leasing'); ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#dashboard-vehicles" data-bs-toggle="tab">
                                    <i class="fas fa-car"></i> <?php esc_html_e('Vehicles', 'car-leasing'); ?>
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
                                <a class="nav-link" href="<?php echo esc_url(admin_url()); ?>">
                                    <i class="fas fa-cog"></i> <?php esc_html_e('WordPress Admin', 'car-leasing'); ?>
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
                            <h2 class="h3 mb-0"><?php esc_html_e('Admin Dashboard', 'car-leasing'); ?></h2>
                            <div class="date"><?php echo date_i18n(get_option('date_format')); ?></div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <div class="dashboard-card">
                                    <div class="dashboard-card-icon">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div class="dashboard-card-title"><?php esc_html_e('Applications', 'car-leasing'); ?></div>
                                    <div class="dashboard-card-value"><?php echo esc_html($applications_count); ?></div>
                                    <a href="#dashboard-applications" data-bs-toggle="tab" class="btn btn-sm btn-outline-primary mt-2"><?php esc_html_e('View All', 'car-leasing'); ?></a>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <div class="dashboard-card">
                                    <div class="dashboard-card-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="dashboard-card-title"><?php esc_html_e('Clients', 'car-leasing'); ?></div>
                                    <div class="dashboard-card-value"><?php echo esc_html($clients_count); ?></div>
                                    <a href="#dashboard-clients" data-bs-toggle="tab" class="btn btn-sm btn-outline-primary mt-2"><?php esc_html_e('View All', 'car-leasing'); ?></a>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <div class="dashboard-card">
                                    <div class="dashboard-card-icon">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div class="dashboard-card-title"><?php esc_html_e('Managers', 'car-leasing'); ?></div>
                                    <div class="dashboard-card-value"><?php echo esc_html($managers_count); ?></div>
                                    <a href="#dashboard-managers" data-bs-toggle="tab" class="btn btn-sm btn-outline-primary mt-2"><?php esc_html_e('View All', 'car-leasing'); ?></a>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <div class="dashboard-card">
                                    <div class="dashboard-card-icon">
                                        <i class="fas fa-car"></i>
                                    </div>
                                    <div class="dashboard-card-title"><?php esc_html_e('Vehicles', 'car-leasing'); ?></div>
                                    <div class="dashboard-card-value"><?php echo esc_html($vehicles_count); ?></div>
                                    <a href="#dashboard-vehicles" data-bs-toggle="tab" class="btn btn-sm btn-outline-primary mt-2"><?php esc_html_e('View All', 'car-leasing'); ?></a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Unassigned Applications -->
                        <div class="pending-applications mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="h5 mb-0"><?php esc_html_e('Unassigned Applications', 'car-leasing'); ?></h3>
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
                                            <th><?php esc_html_e('Status', 'car-leasing'); ?></th>
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
                                                $status = get_post_meta(get_the_ID(), '_application_status', true);
                                                
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
                                                    <td><?php echo esc_html($client_name); ?></td>
                                                    <td><?php echo esc_html($vehicle); ?></td>
                                                    <td><?php echo get_the_date(); ?></td>
                                                    <td><span class="badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html(ucfirst($status)); ?></span></td>
                                                    <td>
                                                        <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-outline-primary"><?php esc_html_e('View', 'car-leasing'); ?></a>
                                                        <a href="#" class="btn btn-sm btn-outline-success assign-manager" data-id="<?php echo esc_attr(get_the_ID()); ?>" data-client="<?php echo esc_attr($client_name); ?>" data-vehicle="<?php echo esc_attr($vehicle); ?>"><?php esc_html_e('Assign', 'car-leasing'); ?></a>
                                                    </td>
                                                </tr>
                                                <?php
                                            endwhile;
                                            wp_reset_postdata();
                                        else : ?>
                                            <tr>
                                                <td colspan="6" class="text-center"><?php esc_html_e('No unassigned applications', 'car-leasing'); ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Recent Activity -->
                        <div class="recent-activity">
                            <h3 class="h5 mb-3"><?php esc_html_e('Recent Activity', 'car-leasing'); ?></h3>
                            
                            <?php
                            $args = array(
                                'post_type' => array('application', 'vehicle'),
                                'posts_per_page' => 10,
                                'orderby' => 'modified',
                                'order' => 'DESC',
                            );
                            $recent_activity = new WP_Query($args);
                            
                            if ($recent_activity->have_posts()) :
                            ?>
                                <div class="list-group">
                                    <?php
                                    while ($recent_activity->have_posts()) :
                                        $recent_activity->the_post();
                                        $icon_class = '';
                                        $activity_type = '';
                                        
                                        if (get_post_type() == 'application') {
                                            $icon_class = 'fa-file-alt';
                                            $activity_type = __('Application', 'car-leasing');
                                            
                                            $status = get_post_meta(get_the_ID(), '_application_status', true);
                                            $client_id = get_post_meta(get_the_ID(), '_client_id', true);
                                            $client_name = get_userdata($client_id)->display_name;
                                            
                                            $activity_description = sprintf(
                                                __('Application #%1$s by %2$s was %3$s', 'car-leasing'),
                                                get_the_ID(),
                                                $client_name,
                                                $status
                                            );
                                        } else {
                                            $icon_class = 'fa-car';
                                            $activity_type = __('Vehicle', 'car-leasing');
                                            
                                            $activity_description = sprintf(
                                                __('Vehicle "%s" was added or updated', 'car-leasing'),
                                                get_the_title()
                                            );
                                        }
                                    ?>
                                        <a href="<?php the_permalink(); ?>" class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between align-items-center">
                                                <div>
                                                    <span class="activity-icon me-2"><i class="fas <?php echo esc_attr($icon_class); ?>"></i></span>
                                                    <span class="activity-type badge bg-secondary me-2"><?php echo esc_html($activity_type); ?></span>
                                                    <?php echo esc_html($activity_description); ?>
                                                </div>
                                                <small class="text-muted"><?php echo human_time_diff(get_the_modified_time('U'), current_time('timestamp')) . ' ' . __('ago', 'car-leasing'); ?></small>
                                            </div>
                                        </a>
                                    <?php endwhile; wp_reset_postdata(); ?>
                                </div>
                            <?php else : ?>
                                <div class="alert alert-info">
                                    <?php esc_html_e('No recent activity', 'car-leasing'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Applications Tab -->
                    <div class="tab-pane fade" id="dashboard-applications">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="h3 mb-0"><?php esc_html_e('All Applications', 'car-leasing'); ?></h2>
                        </div>
                        
                        <!-- Filter Controls -->
                        <div class="filter-controls mb-4">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <select id="application-status-filter" class="form-select">
                                        <option value=""><?php esc_html_e('All Statuses', 'car-leasing'); ?></option>
                                        <option value="pending"><?php esc_html_e('Pending', 'car-leasing'); ?></option>
                                        <option value="approved"><?php esc_html_e('Approved', 'car-leasing'); ?></option>
                                        <option value="rejected"><?php esc_html_e('Rejected', 'car-leasing'); ?></option>
                                        <option value="processing"><?php esc_html_e('Processing', 'car-leasing'); ?></option>
                                    </select>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <select id="application-manager-filter" class="form-select">
                                        <option value=""><?php esc_html_e('All Managers', 'car-leasing'); ?></option>
                                        <option value="unassigned"><?php esc_html_e('Unassigned', 'car-leasing'); ?></option>
                                        <?php foreach ($managers as $manager) : ?>
                                            <option value="<?php echo esc_attr($manager->ID); ?>"><?php echo esc_html($manager->display_name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <select id="application-client-filter" class="form-select">
                                        <option value=""><?php esc_html_e('All Clients', 'car-leasing'); ?></option>
                                        <?php foreach ($clients as $client) : ?>
                                            <option value="<?php echo esc_attr($client->ID); ?>"><?php echo esc_html($client->display_name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <input type="text" id="application-search" class="form-control" placeholder="<?php esc_attr_e('Search applications...', 'car-leasing'); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="all-applications-table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('ID', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Client', 'car-leasing'); ?></th>
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
                                            $client_id = get_post_meta(get_the_ID(), '_client_id', true);
                                            $client_name = get_userdata($client_id)->display_name;
                                            $manager_id = get_post_meta(get_the_ID(), '_manager_id', true);
                                            $manager_name = $manager_id ? get_userdata($manager_id)->display_name : __('Not assigned', 'car-leasing');
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
                                            
                                            $manager_value = $manager_id ? $manager_id : 'unassigned';
                                            ?>
                                            <tr data-status="<?php echo esc_attr($status); ?>" data-manager="<?php echo esc_attr($manager_value); ?>" data-client="<?php echo esc_attr($client_id); ?>">
                                                <td><?php echo esc_html(get_the_ID()); ?></td>
                                                <td><?php echo esc_html($client_name); ?></td>
                                                <td><?php echo esc_html($vehicle); ?></td>
                                                <td><?php echo get_the_date(); ?></td>
                                                <td><span class="badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html(ucfirst($status)); ?></span></td>
                                                <td><?php echo esc_html($manager_name); ?></td>
                                                <td>
                                                    <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-outline-primary"><?php esc_html_e('View', 'car-leasing'); ?></a>
                                                    <?php if (!$manager_id) : ?>
                                                        <a href="#" class="btn btn-sm btn-outline-success assign-manager" data-id="<?php echo esc_attr(get_the_ID()); ?>" data-client="<?php echo esc_attr($client_name); ?>" data-vehicle="<?php echo esc_attr($vehicle); ?>"><?php esc_html_e('Assign', 'car-leasing'); ?></a>
                                                    <?php else : ?>
                                                        <a href="#" class="btn btn-sm btn-outline-warning reassign-manager" data-id="<?php echo esc_attr(get_the_ID()); ?>" data-client="<?php echo esc_attr($client_name); ?>" data-vehicle="<?php echo esc_attr($vehicle); ?>" data-manager="<?php echo esc_attr($manager_id); ?>"><?php esc_html_e('Reassign', 'car-leasing'); ?></a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php
                                        endwhile;
                                        wp_reset_postdata();
                                    else : ?>
                                        <tr>
                                            <td colspan="7" class="text-center"><?php esc_html_e('No applications found', 'car-leasing'); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Clients Tab -->
                    <div class="tab-pane fade" id="dashboard-clients">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="h3 mb-0"><?php esc_html_e('All Clients', 'car-leasing'); ?></h2>
                        </div>
                        
                        <div class="mb-4">
                            <input type="text" id="client-search" class="form-control" placeholder="<?php esc_attr_e('Search clients...', 'car-leasing'); ?>">
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="clients-table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('ID', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Name', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Email', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Phone', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Company', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Applications', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Manager', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Action', 'car-leasing'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($clients)) :
                                        foreach ($clients as $client) :
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
                                                ),
                                            );
                                            $client_applications_query = new WP_Query($args);
                                            $client_applications_count = $client_applications_query->found_posts;
                                            
                                            // Get client's manager
                                            $manager_id = get_user_meta($client->ID, '_assigned_manager', true);
                                            $manager_name = $manager_id ? get_userdata($manager_id)->display_name : __('Not assigned', 'car-leasing');
                                            ?>
                                            <tr>
                                                <td><?php echo esc_html($client->ID); ?></td>
                                                <td><?php echo esc_html($client->display_name); ?></td>
                                                <td><?php echo esc_html($client->user_email); ?></td>
                                                <td><?php echo esc_html($phone ? $phone : '—'); ?></td>
                                                <td><?php echo esc_html($company ? $company : '—'); ?></td>
                                                <td><?php echo esc_html($client_applications_count); ?></td>
                                                <td><?php echo esc_html($manager_name); ?></td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="clientActions<?php echo esc_attr($client->ID); ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <?php esc_html_e('Actions', 'car-leasing'); ?>
                                                        </button>
                                                        <ul class="dropdown-menu" aria-labelledby="clientActions<?php echo esc_attr($client->ID); ?>">
                                                            <li><a class="dropdown-item" href="<?php echo esc_url(admin_url('user-edit.php?user_id=' . $client->ID)); ?>"><?php esc_html_e('Edit', 'car-leasing'); ?></a></li>
                                                            <li><a class="dropdown-item" href="<?php echo esc_url(home_url('/messages/?user=' . $client->ID)); ?>"><?php esc_html_e('Message', 'car-leasing'); ?></a></li>
                                                            <?php if ($client_applications_count > 0) : ?>
                                                                <li><a class="dropdown-item view-client-applications" href="#" data-client="<?php echo esc_attr($client->ID); ?>" data-name="<?php echo esc_attr($client->display_name); ?>"><?php esc_html_e('View Applications', 'car-leasing'); ?></a></li>
                                                            <?php endif; ?>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item assign-client-manager" href="#" data-client="<?php echo esc_attr($client->ID); ?>" data-name="<?php echo esc_attr($client->display_name); ?>" data-manager="<?php echo esc_attr($manager_id); ?>"><?php echo $manager_id ? esc_html__('Reassign Manager', 'car-leasing') : esc_html__('Assign Manager', 'car-leasing'); ?></a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                        endforeach;
                                    else : ?>
                                        <tr>
                                            <td colspan="8" class="text-center"><?php esc_html_e('No clients found', 'car-leasing'); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Managers Tab -->
                    <div class="tab-pane fade" id="dashboard-managers">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="h3 mb-0"><?php esc_html_e('All Managers', 'car-leasing'); ?></h2>
                            <a href="<?php echo esc_url(admin_url('user-new.php')); ?>" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i> <?php esc_html_e('Add New Manager', 'car-leasing'); ?>
                            </a>
                        </div>
                        
                        <div class="mb-4">
                            <input type="text" id="manager-search" class="form-control" placeholder="<?php esc_attr_e('Search managers...', 'car-leasing'); ?>">
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="managers-table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('ID', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Name', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Email', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Phone', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Department', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Clients', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Applications', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Action', 'car-leasing'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($managers)) :
                                        foreach ($managers as $manager) :
                                            $phone = get_user_meta($manager->ID, 'phone', true);
                                            $department = get_user_meta($manager->ID, 'department', true);
                                            
                                            // Get manager's clients
                                            $assigned_clients = car_leasing_get_manager_clients($manager->ID);
                                            $clients_count = count($assigned_clients);
                                            
                                            // Get manager's applications count
                                            $args = array(
                                                'post_type' => 'application',
                                                'posts_per_page' => -1,
                                                'meta_query' => array(
                                                    array(
                                                        'key' => '_manager_id',
                                                        'value' => $manager->ID,
                                                    ),
                                                ),
                                            );
                                            $manager_applications_query = new WP_Query($args);
                                            $manager_applications_count = $manager_applications_query->found_posts;
                                            ?>
                                            <tr>
                                                <td><?php echo esc_html($manager->ID); ?></td>
                                                <td><?php echo esc_html($manager->display_name); ?></td>
                                                <td><?php echo esc_html($manager->user_email); ?></td>
                                                <td><?php echo esc_html($phone ? $phone : '—'); ?></td>
                                                <td><?php echo esc_html($department ? $department : '—'); ?></td>
                                                <td><?php echo esc_html($clients_count); ?></td>
                                                <td><?php echo esc_html($manager_applications_count); ?></td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="managerActions<?php echo esc_attr($manager->ID); ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <?php esc_html_e('Actions', 'car-leasing'); ?>
                                                        </button>
                                                        <ul class="dropdown-menu" aria-labelledby="managerActions<?php echo esc_attr($manager->ID); ?>">
                                                            <li><a class="dropdown-item" href="<?php echo esc_url(admin_url('user-edit.php?user_id=' . $manager->ID)); ?>"><?php esc_html_e('Edit', 'car-leasing'); ?></a></li>
                                                            <li><a class="dropdown-item" href="<?php echo esc_url(home_url('/messages/?user=' . $manager->ID)); ?>"><?php esc_html_e('Message', 'car-leasing'); ?></a></li>
                                                            <?php if ($clients_count > 0) : ?>
                                                                <li><a class="dropdown-item view-manager-clients" href="#" data-manager="<?php echo esc_attr($manager->ID); ?>" data-name="<?php echo esc_attr($manager->display_name); ?>"><?php esc_html_e('View Clients', 'car-leasing'); ?></a></li>
                                                            <?php endif; ?>
                                                            <?php if ($manager_applications_count > 0) : ?>
                                                                <li><a class="dropdown-item view-manager-applications" href="#" data-manager="<?php echo esc_attr($manager->ID); ?>" data-name="<?php echo esc_attr($manager->display_name); ?>"><?php esc_html_e('View Applications', 'car-leasing'); ?></a></li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                        endforeach;
                                    else : ?>
                                        <tr>
                                            <td colspan="8" class="text-center"><?php esc_html_e('No managers found', 'car-leasing'); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Vehicles Tab -->
                    <div class="tab-pane fade" id="dashboard-vehicles">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="h3 mb-0"><?php esc_html_e('All Vehicles', 'car-leasing'); ?></h2>
                            <a href="<?php echo esc_url(admin_url('post-new.php?post_type=vehicle')); ?>" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i> <?php esc_html_e('Add New Vehicle', 'car-leasing'); ?>
                            </a>
                        </div>
                        
                        <div class="mb-4">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="vehicle-source-filter" class="form-label"><?php esc_html_e('Source', 'car-leasing'); ?></label>
                                    <select id="vehicle-source-filter" class="form-select">
                                        <option value=""><?php esc_html_e('All Sources', 'car-leasing'); ?></option>
                                        <option value="internal"><?php esc_html_e('Internal', 'car-leasing'); ?></option>
                                        <option value="external"><?php esc_html_e('External', 'car-leasing'); ?></option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="vehicle-make-filter" class="form-label"><?php esc_html_e('Make', 'car-leasing'); ?></label>
                                    <select id="vehicle-make-filter" class="form-select">
                                        <option value=""><?php esc_html_e('All Makes', 'car-leasing'); ?></option>
                                        <?php
                                        $makes = array();
                                        
                                        if ($vehicles_query->have_posts()) :
                                            while ($vehicles_query->have_posts()) :
                                                $vehicles_query->the_post();
                                                $make = get_post_meta(get_the_ID(), '_vehicle_make', true);
                                                if ($make && !in_array($make, $makes)) {
                                                    $makes[] = $make;
                                                }
                                            endwhile;
                                            wp_reset_postdata();
                                            
                                            sort($makes);
                                            foreach ($makes as $make) :
                                                echo '<option value="' . esc_attr($make) . '">' . esc_html($make) . '</option>';
                                            endforeach;
                                        endif;
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="vehicle-year-filter" class="form-label"><?php esc_html_e('Year', 'car-leasing'); ?></label>
                                    <select id="vehicle-year-filter" class="form-select">
                                        <option value=""><?php esc_html_e('All Years', 'car-leasing'); ?></option>
                                        <?php
                                        $years = array();
                                        
                                        if ($vehicles_query->have_posts()) :
                                            while ($vehicles_query->have_posts()) :
                                                $vehicles_query->the_post();
                                                $year = get_post_meta(get_the_ID(), '_vehicle_year', true);
                                                if ($year && !in_array($year, $years)) {
                                                    $years[] = $year;
                                                }
                                            endwhile;
                                            wp_reset_postdata();
                                            
                                            rsort($years);
                                            foreach ($years as $year) :
                                                echo '<option value="' . esc_attr($year) . '">' . esc_html($year) . '</option>';
                                            endforeach;
                                        endif;
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="vehicle-search" class="form-label"><?php esc_html_e('Search', 'car-leasing'); ?></label>
                                    <input type="text" id="vehicle-search" class="form-control" placeholder="<?php esc_attr_e('Search vehicles...', 'car-leasing'); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="vehicles-table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('ID', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Image', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Make & Model', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Year', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Price', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Source', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Status', 'car-leasing'); ?></th>
                                        <th><?php esc_html_e('Action', 'car-leasing'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($vehicles_query->have_posts()) :
                                        while ($vehicles_query->have_posts()) :
                                            $vehicles_query->the_post();
                                            $make = get_post_meta(get_the_ID(), '_vehicle_make', true);
                                            $model = get_post_meta(get_the_ID(), '_vehicle_model', true);
                                            $year = get_post_meta(get_the_ID(), '_vehicle_year', true);
                                            $price = get_post_meta(get_the_ID(), '_vehicle_price', true);
                                            $external_source = get_post_meta(get_the_ID(), '_vehicle_external_source', true);
                                            $source_type = $external_source ? 'external' : 'internal';
                                            $featured = get_post_meta(get_the_ID(), '_vehicle_featured', true);
                                            ?>
                                            <tr data-make="<?php echo esc_attr($make); ?>" data-year="<?php echo esc_attr($year); ?>" data-source="<?php echo esc_attr($source_type); ?>">
                                                <td><?php echo esc_html(get_the_ID()); ?></td>
                                                <td>
                                                    <?php if (has_post_thumbnail()) : ?>
                                                        <img src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'thumbnail')); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" width="60" height="60" class="img-thumbnail">
                                                    <?php else : ?>
                                                        <div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                            <i class="fas fa-car text-secondary"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo esc_html($make . ' ' . $model); ?></td>
                                                <td><?php echo esc_html($year); ?></td>
                                                <td><?php echo esc_html(number_format($price)); ?> ₽</td>
                                                <td>
                                                    <?php if ($external_source) : ?>
                                                        <span class="badge bg-info"><?php echo esc_html($external_source); ?></span>
                                                    <?php else : ?>
                                                        <span class="badge bg-secondary"><?php esc_html_e('Internal', 'car-leasing'); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($featured) : ?>
                                                        <span class="badge bg-primary"><?php esc_html_e('Featured', 'car-leasing'); ?></span>
                                                    <?php else : ?>
                                                        <span class="badge bg-secondary"><?php esc_html_e('Regular', 'car-leasing'); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="vehicleActions<?php echo esc_attr(get_the_ID()); ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <?php esc_html_e('Actions', 'car-leasing'); ?>
                                                        </button>
                                                        <ul class="dropdown-menu" aria-labelledby="vehicleActions<?php echo esc_attr(get_the_ID()); ?>">
                                                            <li><a class="dropdown-item" href="<?php the_permalink(); ?>"><?php esc_html_e('View', 'car-leasing'); ?></a></li>
                                                            <li><a class="dropdown-item" href="<?php echo esc_url(admin_url('post.php?post=' . get_the_ID() . '&action=edit')); ?>"><?php esc_html_e('Edit', 'car-leasing'); ?></a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <?php if ($featured) : ?>
                                                                <li><a class="dropdown-item toggle-featured" href="#" data-id="<?php echo esc_attr(get_the_ID()); ?>" data-featured="1"><?php esc_html_e('Remove Featured', 'car-leasing'); ?></a></li>
                                                            <?php else : ?>
                                                                <li><a class="dropdown-item toggle-featured" href="#" data-id="<?php echo esc_attr(get_the_ID()); ?>" data-featured="0"><?php esc_html_e('Mark Featured', 'car-leasing'); ?></a></li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                        endwhile;
                                        wp_reset_postdata();
                                    else : ?>
                                        <tr>
                                            <td colspan="8" class="text-center"><?php esc_html_e('No vehicles found', 'car-leasing'); ?></td>
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

<!-- Assign Manager Modal -->
<div class="modal fade" id="assignManagerModal" tabindex="-1" aria-labelledby="assignManagerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignManagerModalLabel"><?php esc_html_e('Assign Manager to Application', 'car-leasing'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assign-manager-form">
                    <input type="hidden" id="application-id" name="application_id">
                    
                    <div class="mb-3">
                        <p><strong><?php esc_html_e('Client:', 'car-leasing'); ?></strong> <span id="modal-client-name"></span></p>
                        <p><strong><?php esc_html_e('Vehicle:', 'car-leasing'); ?></strong> <span id="modal-vehicle-name"></span></p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="manager-id" class="form-label"><?php esc_html_e('Select Manager', 'car-leasing'); ?></label>
                        <select class="form-select" id="manager-id" name="manager_id" required>
                            <option value=""><?php esc_html_e('Select a manager...', 'car-leasing'); ?></option>
                            <?php foreach ($managers as $manager) : ?>
                                <option value="<?php echo esc_attr($manager->ID); ?>"><?php echo esc_html($manager->display_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php esc_html_e('Cancel', 'car-leasing'); ?></button>
                <button type="button" class="btn btn-primary" id="submit-assignment"><?php esc_html_e('Assign Manager', 'car-leasing'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Assign Client Manager Modal -->
<div class="modal fade" id="assignClientManagerModal" tabindex="-1" aria-labelledby="assignClientManagerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignClientManagerModalLabel"><?php esc_html_e('Assign Manager to Client', 'car-leasing'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assign-client-manager-form">
                    <input type="hidden" id="client-id" name="client_id">
                    
                    <div class="mb-3">
                        <p><strong><?php esc_html_e('Client:', 'car-leasing'); ?></strong> <span id="modal-client-name-2"></span></p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="client-manager-id" class="form-label"><?php esc_html_e('Select Manager', 'car-leasing'); ?></label>
                        <select class="form-select" id="client-manager-id" name="manager_id" required>
                            <option value=""><?php esc_html_e('Select a manager...', 'car-leasing'); ?></option>
                            <?php foreach ($managers as $manager) : ?>
                                <option value="<?php echo esc_attr($manager->ID); ?>"><?php echo esc_html($manager->display_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php esc_html_e('Cancel', 'car-leasing'); ?></button>
                <button type="button" class="btn btn-primary" id="submit-client-assignment"><?php esc_html_e('Assign Manager', 'car-leasing'); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Application filters
    $('#application-status-filter, #application-manager-filter, #application-client-filter').on('change', function() {
        var statusFilter = $('#application-status-filter').val();
        var managerFilter = $('#application-manager-filter').val();
        var clientFilter = $('#application-client-filter').val();
        
        $('#all-applications-table tbody tr').each(function() {
            var row = $(this);
            var showRow = true;
            
            if (statusFilter && row.data('status') !== statusFilter) {
                showRow = false;
            }
            
            if (managerFilter && row.data('manager') !== managerFilter) {
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
        
        $('#all-applications-table tbody tr').each(function() {
            var rowText = $(this).text().toLowerCase();
            if (rowText.indexOf(searchText) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Client search
    $('#client-search').on('keyup', function() {
        var searchText = $(this).val().toLowerCase();
        
        $('#clients-table tbody tr').each(function() {
            var rowText = $(this).text().toLowerCase();
            if (rowText.indexOf(searchText) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Manager search
    $('#manager-search').on('keyup', function() {
        var searchText = $(this).val().toLowerCase();
        
        $('#managers-table tbody tr').each(function() {
            var rowText = $(this).text().toLowerCase();
            if (rowText.indexOf(searchText) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Vehicle filters
    $('#vehicle-source-filter, #vehicle-make-filter, #vehicle-year-filter').on('change', function() {
        var sourceFilter = $('#vehicle-source-filter').val();
        var makeFilter = $('#vehicle-make-filter').val();
        var yearFilter = $('#vehicle-year-filter').val();
        
        $('#vehicles-table tbody tr').each(function() {
            var row = $(this);
            var showRow = true;
            
            if (sourceFilter && row.data('source') !== sourceFilter) {
                showRow = false;
            }
            
            if (makeFilter && row.data('make') !== makeFilter) {
                showRow = false;
            }
            
            if (yearFilter && row.data('year') !== yearFilter) {
                showRow = false;
            }
            
            if (showRow) {
                row.show();
            } else {
                row.hide();
            }
        });
    });
    
    // Vehicle search
    $('#vehicle-search').on('keyup', function() {
        var searchText = $(this).val().toLowerCase();
        
        $('#vehicles-table tbody tr').each(function() {
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
        var clientName = $(this).data('name');
        
        // Switch to applications tab
        $('a[href="#dashboard-applications"]').tab('show');
        
        // Apply client filter
        $('#application-client-filter').val(clientId).trigger('change');
    });
    
    // View manager clients button
    $('.view-manager-clients').on('click', function(e) {
        e.preventDefault();
        var managerId = $(this).data('manager');
        var managerName = $(this).data('name');
        
        // Switch to clients tab and filter (would need additional implementation)
        $('a[href="#dashboard-clients"]').tab('show');
        
        // Display alert for now
        alert('Clients assigned to ' + managerName + ' will be highlighted');
    });
    
    // View manager applications button
    $('.view-manager-applications').on('click', function(e) {
        e.preventDefault();
        var managerId = $(this).data('manager');
        var managerName = $(this).data('name');
        
        // Switch to applications tab
        $('a[href="#dashboard-applications"]').tab('show');
        
        // Apply manager filter
        $('#application-manager-filter').val(managerId).trigger('change');
    });
    
    // Assign manager to application
    $('.assign-manager, .reassign-manager').on('click', function(e) {
        e.preventDefault();
        var applicationId = $(this).data('id');
        var clientName = $(this).data('client');
        var vehicleName = $(this).data('vehicle');
        var currentManager = $(this).data('manager');
        
        $('#application-id').val(applicationId);
        $('#modal-client-name').text(clientName);
        $('#modal-vehicle-name').text(vehicleName);
        
        if (currentManager) {
            $('#manager-id').val(currentManager);
            $('#assignManagerModalLabel').text('<?php esc_html_e('Reassign Manager to Application', 'car-leasing'); ?>');
            $('#submit-assignment').text('<?php esc_html_e('Reassign Manager', 'car-leasing'); ?>');
        } else {
            $('#manager-id').val('');
            $('#assignManagerModalLabel').text('<?php esc_html_e('Assign Manager to Application', 'car-leasing'); ?>');
            $('#submit-assignment').text('<?php esc_html_e('Assign Manager', 'car-leasing'); ?>');
        }
        
        var modal = new bootstrap.Modal(document.getElementById('assignManagerModal'));
        modal.show();
    });
    
    // Submit manager assignment
    $('#submit-assignment').on('click', function() {
        var applicationId = $('#application-id').val();
        var managerId = $('#manager-id').val();
        
        if (!managerId) {
            alert('<?php esc_attr_e('Please select a manager', 'car-leasing'); ?>');
            return;
        }
        
        // Send AJAX request
        $.ajax({
            url: car_leasing_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'car_leasing_assign_manager',
                application_id: applicationId,
                manager_id: managerId,
                security: car_leasing_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Close modal
                    var modal = bootstrap.Modal.getInstance(document.getElementById('assignManagerModal'));
                    modal.hide();
                    
                    // Show success message and reload page
                    alert(response.data.message || '<?php esc_attr_e('Manager assigned successfully', 'car-leasing'); ?>');
                    location.reload();
                } else {
                    alert(response.data.message || '<?php esc_attr_e('An error occurred while assigning the manager', 'car-leasing'); ?>');
                }
            },
            error: function() {
                alert('<?php esc_attr_e('An error occurred. Please try again later.', 'car-leasing'); ?>');
            }
        });
    });
    
    // Assign manager to client
    $('.assign-client-manager').on('click', function(e) {
        e.preventDefault();
        var clientId = $(this).data('client');
        var clientName = $(this).data('name');
        var currentManager = $(this).data('manager');
        
        $('#client-id').val(clientId);
        $('#modal-client-name-2').text(clientName);
        
        if (currentManager) {
            $('#client-manager-id').val(currentManager);
            $('#assignClientManagerModalLabel').text('<?php esc_html_e('Reassign Manager to Client', 'car-leasing'); ?>');
            $('#submit-client-assignment').text('<?php esc_html_e('Reassign Manager', 'car-leasing'); ?>');
        } else {
            $('#client-manager-id').val('');
            $('#assignClientManagerModalLabel').text('<?php esc_html_e('Assign Manager to Client', 'car-leasing'); ?>');
            $('#submit-client-assignment').text('<?php esc_html_e('Assign Manager', 'car-leasing'); ?>');
        }
        
        var modal = new bootstrap.Modal(document.getElementById('assignClientManagerModal'));
        modal.show();
    });
    
    // Submit client manager assignment
    $('#submit-client-assignment').on('click', function() {
        var clientId = $('#client-id').val();
        var managerId = $('#client-manager-id').val();
        
        if (!managerId) {
            alert('<?php esc_attr_e('Please select a manager', 'car-leasing'); ?>');
            return;
        }
        
        // Send AJAX request
        $.ajax({
            url: car_leasing_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'car_leasing_assign_client_manager',
                client_id: clientId,
                manager_id: managerId,
                security: car_leasing_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Close modal
                    var modal = bootstrap.Modal.getInstance(document.getElementById('assignClientManagerModal'));
                    modal.hide();
                    
                    // Show success message and reload page
                    alert(response.data.message || '<?php esc_attr_e('Manager assigned successfully', 'car-leasing'); ?>');
                    location.reload();
                } else {
                    alert(response.data.message || '<?php esc_attr_e('An error occurred while assigning the manager', 'car-leasing'); ?>');
                }
            },
            error: function() {
                alert('<?php esc_attr_e('An error occurred. Please try again later.', 'car-leasing'); ?>');
            }
        });
    });
    
    // Toggle vehicle featured status
    $('.toggle-featured').on('click', function(e) {
        e.preventDefault();
        var vehicleId = $(this).data('id');
        var isFeatured = $(this).data('featured');
        
        // Send AJAX request
        $.ajax({
            url: car_leasing_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'car_leasing_toggle_vehicle_featured',
                vehicle_id: vehicleId,
                is_featured: isFeatured,
                security: car_leasing_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Show success message and reload page
                    alert(response.data.message || '<?php esc_attr_e('Vehicle status updated successfully', 'car-leasing'); ?>');
                    location.reload();
                } else {
                    alert(response.data.message || '<?php esc_attr_e('An error occurred while updating the vehicle status', 'car-leasing'); ?>');
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
