<?php
/**
 * Template Name: Login / Register
 *
 * Template for displaying the login and registration forms
 *
 * @package Car_Leasing
 */

// Redirect logged in users to their dashboards
if (is_user_logged_in()) {
    $current_user = wp_get_current_user();
    
    if (in_array('car_leasing_client', $current_user->roles)) {
        wp_redirect(home_url('/client-dashboard/'));
        exit;
    } elseif (in_array('car_leasing_manager', $current_user->roles)) {
        wp_redirect(home_url('/manager-dashboard/'));
        exit;
    } elseif (in_array('administrator', $current_user->roles)) {
        wp_redirect(home_url('/admin-dashboard/'));
        exit;
    }
}

// Process login form
$login_error = '';
if (isset($_POST['car_leasing_login'])) {
    $creds = array(
        'user_login'    => sanitize_text_field($_POST['user_login']),
        'user_password' => $_POST['user_pass'],
        'remember'      => isset($_POST['rememberme']),
    );

    $user = wp_signon($creds, false);

    if (is_wp_error($user)) {
        $login_error = $user->get_error_message();
    } else {
        // Redirect to the requested page or dashboard
        $redirect_to = isset($_GET['redirect_to']) ? $_GET['redirect_to'] : '';
        
        if (empty($redirect_to)) {
            // Redirect based on user role
            if (in_array('car_leasing_client', $user->roles)) {
                $redirect_to = home_url('/client-dashboard/');
            } elseif (in_array('car_leasing_manager', $user->roles)) {
                $redirect_to = home_url('/manager-dashboard/');
            } elseif (in_array('administrator', $user->roles)) {
                $redirect_to = home_url('/admin-dashboard/');
            } else {
                $redirect_to = home_url();
            }
        }
        
        wp_redirect($redirect_to);
        exit;
    }
}

// Process registration form
$reg_error = '';
$reg_success = '';
if (isset($_POST['car_leasing_register'])) {
    $user_login = sanitize_user($_POST['user_login_reg']);
    $user_email = sanitize_email($_POST['user_email']);
    $user_pass = $_POST['user_pass_reg'];
    $user_pass_confirm = $_POST['user_pass_confirm'];
    $user_type = sanitize_text_field($_POST['user_type']);
    $phone = sanitize_text_field($_POST['phone']);
    $company = sanitize_text_field($_POST['company']);
    
    // Validation
    if (empty($user_login) || empty($user_email) || empty($user_pass) || empty($user_pass_confirm)) {
        $reg_error = __('All fields are required', 'car-leasing');
    } elseif (!is_email($user_email)) {
        $reg_error = __('Please enter a valid email address', 'car-leasing');
    } elseif (username_exists($user_login)) {
        $reg_error = __('Username already exists', 'car-leasing');
    } elseif (email_exists($user_email)) {
        $reg_error = __('Email address already exists', 'car-leasing');
    } elseif ($user_pass !== $user_pass_confirm) {
        $reg_error = __('Passwords do not match', 'car-leasing');
    } elseif (strlen($user_pass) < 6) {
        $reg_error = __('Password must be at least 6 characters long', 'car-leasing');
    } else {
        // Create the user
        $user_id = wp_create_user($user_login, $user_pass, $user_email);
        
        if (is_wp_error($user_id)) {
            $reg_error = $user_id->get_error_message();
        } else {
            // Set user role based on selection
            $user = new WP_User($user_id);
            $user->set_role('car_leasing_client'); // Default role
            
            // Add user meta
            if (!empty($phone)) {
                update_user_meta($user_id, 'phone', $phone);
            }
            
            if (!empty($company)) {
                update_user_meta($user_id, 'company', $company);
            }
            
            // Set display name
            wp_update_user(array(
                'ID' => $user_id,
                'display_name' => $user_login,
            ));
            
            // Set registration success message
            $reg_success = __('Registration successful! You can now log in.', 'car-leasing');
            
            // Send notification to admin
            $admin_email = get_option('admin_email');
            $subject = sprintf(__('[%s] New User Registration', 'car-leasing'), get_bloginfo('name'));
            $message = sprintf(__('New user registration on your website %s:', 'car-leasing'), get_bloginfo('name')) . "\r\n\r\n";
            $message .= sprintf(__('Username: %s', 'car-leasing'), $user_login) . "\r\n";
            $message .= sprintf(__('Email: %s', 'car-leasing'), $user_email) . "\r\n";
            $message .= sprintf(__('Phone: %s', 'car-leasing'), $phone) . "\r\n";
            $message .= sprintf(__('Company: %s', 'car-leasing'), $company) . "\r\n\r\n";
            $message .= sprintf(__('View user: %s', 'car-leasing'), admin_url('user-edit.php?user_id=' . $user_id)) . "\r\n";
            
            wp_mail($admin_email, $subject, $message);
        }
    }
}

get_header();
?>

<div class="page-banner bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto text-center">
                <h1 class="page-title"><?php echo get_the_title(); ?></h1>
                <p class="lead"><?php esc_html_e('Access your account or register as a new user', 'car-leasing'); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-lg-6 d-none d-lg-block">
                            <div class="h-100 bg-primary position-relative">
                                <div class="position-absolute top-0 start-0 end-0 bottom-0 d-flex flex-column justify-content-center px-5 text-white">
                                    <h2 class="mb-4"><?php esc_html_e('Welcome to Car Leasing', 'car-leasing'); ?></h2>
                                    <p><?php esc_html_e('Log in to your account to access your dashboard, manage applications, and communicate with our team.', 'car-leasing'); ?></p>
                                    <p><?php esc_html_e('New to our service? Register now to apply for vehicle leasing and enjoy our personalized service.', 'car-leasing'); ?></p>
                                    <div class="mt-4">
                                        <img src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4" alt="<?php esc_attr_e('Car Leasing', 'car-leasing'); ?>" class="img-fluid rounded opacity-75" style="max-height: 150px; object-fit: cover;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="p-4 p-md-5">
                                <!-- Login/Register Tabs -->
                                <ul class="nav nav-pills nav-fill mb-4" id="auth-tab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="login-tab" data-bs-toggle="pill" data-bs-target="#login-pane" type="button" role="tab" aria-controls="login-pane" aria-selected="true"><?php esc_html_e('Login', 'car-leasing'); ?></button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="register-tab" data-bs-toggle="pill" data-bs-target="#register-pane" type="button" role="tab" aria-controls="register-pane" aria-selected="false"><?php esc_html_e('Register', 'car-leasing'); ?></button>
                                    </li>
                                </ul>
                                
                                <div class="tab-content" id="auth-tab-content">
                                    <!-- Login Tab -->
                                    <div class="tab-pane fade show active" id="login-pane" role="tabpanel" aria-labelledby="login-tab" tabindex="0">
                                        <?php if (!empty($login_error)) : ?>
                                            <div class="alert alert-danger">
                                                <?php echo esc_html($login_error); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <form id="login-form" method="post" action="<?php echo esc_url(remove_query_arg('action', add_query_arg(array()))); ?>">
                                            <div class="mb-3">
                                                <label for="user_login" class="form-label"><?php esc_html_e('Username or Email Address', 'car-leasing'); ?></label>
                                                <input type="text" class="form-control" id="user_login" name="user_login" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="user_pass" class="form-label"><?php esc_html_e('Password', 'car-leasing'); ?></label>
                                                <div class="password-toggle-wrapper">
                                                    <input type="password" class="form-control" id="user_pass" name="user_pass" required>
                                                    <span class="toggle-password" data-toggle="#user_pass">
                                                        <i class="fas fa-eye"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3 form-check">
                                                <input type="checkbox" class="form-check-input" id="rememberme" name="rememberme" value="forever">
                                                <label class="form-check-label" for="rememberme"><?php esc_html_e('Remember Me', 'car-leasing'); ?></label>
                                            </div>
                                            
                                            <div class="d-grid mb-3">
                                                <button type="submit" name="car_leasing_login" class="btn btn-primary"><?php esc_html_e('Log In', 'car-leasing'); ?></button>
                                            </div>
                                            
                                            <div class="text-center">
                                                <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="text-decoration-none"><?php esc_html_e('Forgot your password?', 'car-leasing'); ?></a>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <!-- Register Tab -->
                                    <div class="tab-pane fade" id="register-pane" role="tabpanel" aria-labelledby="register-tab" tabindex="0">
                                        <?php if (!empty($reg_error)) : ?>
                                            <div class="alert alert-danger">
                                                <?php echo esc_html($reg_error); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($reg_success)) : ?>
                                            <div class="alert alert-success">
                                                <?php echo esc_html($reg_success); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <form id="register-form" method="post" action="<?php echo esc_url(remove_query_arg('action', add_query_arg(array()))); ?>">
                                            <div class="mb-3">
                                                <label for="user_login_reg" class="form-label"><?php esc_html_e('Username', 'car-leasing'); ?></label>
                                                <input type="text" class="form-control" id="user_login_reg" name="user_login_reg" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="user_email" class="form-label"><?php esc_html_e('Email Address', 'car-leasing'); ?></label>
                                                <input type="email" class="form-control" id="user_email" name="user_email" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="user_pass_reg" class="form-label"><?php esc_html_e('Password', 'car-leasing'); ?></label>
                                                <div class="password-toggle-wrapper">
                                                    <input type="password" class="form-control" id="user_pass_reg" name="user_pass_reg" required>
                                                    <span class="toggle-password" data-toggle="#user_pass_reg">
                                                        <i class="fas fa-eye"></i>
                                                    </span>
                                                </div>
                                                <div class="form-text"><?php esc_html_e('Password must be at least 6 characters long', 'car-leasing'); ?></div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="user_pass_confirm" class="form-label"><?php esc_html_e('Confirm Password', 'car-leasing'); ?></label>
                                                <div class="password-toggle-wrapper">
                                                    <input type="password" class="form-control" id="user_pass_confirm" name="user_pass_confirm" required>
                                                    <span class="toggle-password" data-toggle="#user_pass_confirm">
                                                        <i class="fas fa-eye"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="phone" class="form-label"><?php esc_html_e('Phone Number', 'car-leasing'); ?></label>
                                                <input type="tel" class="form-control" id="phone" name="phone">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="company" class="form-label"><?php esc_html_e('Company (Optional)', 'car-leasing'); ?></label>
                                                <input type="text" class="form-control" id="company" name="company">
                                            </div>
                                            
                                            <input type="hidden" name="user_type" value="client">
                                            
                                            <div class="mb-3 form-check">
                                                <input type="checkbox" class="form-check-input" id="terms_agree" name="terms_agree" required>
                                                <label class="form-check-label" for="terms_agree">
                                                    <?php esc_html_e('I agree to the', 'car-leasing'); ?> <a href="<?php echo esc_url(home_url('/terms-and-conditions/')); ?>" target="_blank"><?php esc_html_e('Terms and Conditions', 'car-leasing'); ?></a>
                                                </label>
                                            </div>
                                            
                                            <div class="d-grid">
                                                <button type="submit" name="car_leasing_register" class="btn btn-primary"><?php esc_html_e('Register', 'car-leasing'); ?></button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p><?php esc_html_e('By using our service, you agree to our', 'car-leasing'); ?> <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>"><?php esc_html_e('Privacy Policy', 'car-leasing'); ?></a> <?php esc_html_e('and', 'car-leasing'); ?> <a href="<?php echo esc_url(home_url('/terms-and-conditions/')); ?>"><?php esc_html_e('Terms of Service', 'car-leasing'); ?></a>.</p>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Toggle password visibility
    $('.toggle-password').on('click', function() {
        var passwordField = $($(this).data('toggle'));
        var type = passwordField.attr('type') === 'password' ? 'text' : 'password';
        passwordField.attr('type', type);
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });
    
    // Activate register tab if there's a registration error or success message
    <?php if (!empty($reg_error) || !empty($reg_success)) : ?>
    $('#register-tab').tab('show');
    <?php endif; ?>
});
</script>

<?php
get_footer();
