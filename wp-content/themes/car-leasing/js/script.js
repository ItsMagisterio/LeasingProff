/**
 * Car Leasing Theme JavaScript
 */
(function($) {
    'use strict';
    
    // Document Ready
    $(document).ready(function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Message System
        initializeMessaging();
        
        // Application Form Validation
        initializeApplicationForm();
        
        // Login/Register Form Validation
        initializeAuthForms();
        
        // Dashboard functionality
        initializeDashboard();
    });
    
    function initializeMessaging() {
        // Message sending functionality
        $('#send-message-form').on('submit', function(e) {
            e.preventDefault();
            
            var messageContent = $('#message-content').val();
            var recipientId = $('#recipient-id').val();
            
            if (!messageContent.trim()) {
                alert('Please enter a message');
                return;
            }
            
            var data = {
                action: 'car_leasing_send_message',
                recipient_id: recipientId,
                message_content: messageContent,
                security: car_leasing_ajax.nonce
            };
            
            $.post(car_leasing_ajax.ajax_url, data, function(response) {
                if (response.success) {
                    $('#message-content').val('');
                    refreshMessages();
                } else {
                    alert(response.data.message || 'An error occurred while sending the message');
                }
            }).fail(function() {
                alert('An error occurred. Please try again later.');
            });
        });
        
        // Function to refresh messages
        function refreshMessages() {
            if ($('#messages-container').length > 0) {
                var conversationId = $('#current-conversation-id').val();
                
                if (!conversationId) {
                    return;
                }
                
                var data = {
                    action: 'car_leasing_get_messages',
                    conversation_id: conversationId,
                    security: car_leasing_ajax.nonce
                };
                
                $.post(car_leasing_ajax.ajax_url, data, function(response) {
                    if (response.success) {
                        $('#messages-container').html(response.data.messages_html);
                        // Scroll to the bottom of the messages container
                        var messagesContainer = document.getElementById('messages-container');
                        messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    }
                });
            }
        }
        
        // Auto-refresh messages every 30 seconds
        if ($('#messages-container').length > 0) {
            setInterval(refreshMessages, 30000);
            
            // Initial scroll to bottom of messages
            var messagesContainer = document.getElementById('messages-container');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        }
    }
    
    function initializeApplicationForm() {
        // Application form validation
        $('#leasing-application-form').on('submit', function(e) {
            var isValid = true;
            
            // Basic client information validation
            if ($('#client-name').val().trim() === '') {
                isValid = false;
                $('#client-name').addClass('is-invalid');
            } else {
                $('#client-name').removeClass('is-invalid');
            }
            
            if ($('#client-email').val().trim() === '' || !isValidEmail($('#client-email').val())) {
                isValid = false;
                $('#client-email').addClass('is-invalid');
            } else {
                $('#client-email').removeClass('is-invalid');
            }
            
            if ($('#client-phone').val().trim() === '') {
                isValid = false;
                $('#client-phone').addClass('is-invalid');
            } else {
                $('#client-phone').removeClass('is-invalid');
            }
            
            // Vehicle information validation
            if ($('#vehicle-make').val().trim() === '') {
                isValid = false;
                $('#vehicle-make').addClass('is-invalid');
            } else {
                $('#vehicle-make').removeClass('is-invalid');
            }
            
            if ($('#vehicle-model').val().trim() === '') {
                isValid = false;
                $('#vehicle-model').addClass('is-invalid');
            } else {
                $('#vehicle-model').removeClass('is-invalid');
            }
            
            // Leasing terms validation
            if ($('#leasing-term').val() === '') {
                isValid = false;
                $('#leasing-term').addClass('is-invalid');
            } else {
                $('#leasing-term').removeClass('is-invalid');
            }
            
            if ($('#initial-payment').val().trim() === '') {
                isValid = false;
                $('#initial-payment').addClass('is-invalid');
            } else {
                $('#initial-payment').removeClass('is-invalid');
            }
            
            if (!isValid) {
                e.preventDefault();
                $('#form-error-message').removeClass('d-none');
                
                // Scroll to the top of the form to show the error message
                $('html, body').animate({
                    scrollTop: $('#leasing-application-form').offset().top - 100
                }, 200);
            }
        });
        
        // Function to validate email format
        function isValidEmail(email) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        // Clear validation when fields change
        $('#leasing-application-form input, #leasing-application-form select, #leasing-application-form textarea').on('change', function() {
            $(this).removeClass('is-invalid');
            $('#form-error-message').addClass('d-none');
        });
    }
    
    function initializeAuthForms() {
        // Login form validation
        $('#login-form').on('submit', function(e) {
            var isValid = true;
            
            if ($('#user_login').val().trim() === '') {
                isValid = false;
                $('#user_login').addClass('is-invalid');
            } else {
                $('#user_login').removeClass('is-invalid');
            }
            
            if ($('#user_pass').val().trim() === '') {
                isValid = false;
                $('#user_pass').addClass('is-invalid');
            } else {
                $('#user_pass').removeClass('is-invalid');
            }
            
            if (!isValid) {
                e.preventDefault();
                $('#login-error-message').removeClass('d-none');
            }
        });
        
        // Registration form validation
        $('#register-form').on('submit', function(e) {
            var isValid = true;
            
            if ($('#user_login_reg').val().trim() === '') {
                isValid = false;
                $('#user_login_reg').addClass('is-invalid');
            } else {
                $('#user_login_reg').removeClass('is-invalid');
            }
            
            if ($('#user_email').val().trim() === '' || !isValidEmail($('#user_email').val())) {
                isValid = false;
                $('#user_email').addClass('is-invalid');
            } else {
                $('#user_email').removeClass('is-invalid');
            }
            
            if ($('#user_pass_reg').val().trim() === '') {
                isValid = false;
                $('#user_pass_reg').addClass('is-invalid');
            } else {
                $('#user_pass_reg').removeClass('is-invalid');
            }
            
            if ($('#user_pass_confirm').val().trim() === '' || $('#user_pass_reg').val() !== $('#user_pass_confirm').val()) {
                isValid = false;
                $('#user_pass_confirm').addClass('is-invalid');
            } else {
                $('#user_pass_confirm').removeClass('is-invalid');
            }
            
            if (!isValid) {
                e.preventDefault();
                $('#register-error-message').removeClass('d-none');
            }
        });
        
        // Function to validate email format
        function isValidEmail(email) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        // Clear validation when fields change
        $('#login-form input, #register-form input').on('change', function() {
            $(this).removeClass('is-invalid');
            $('#login-error-message, #register-error-message').addClass('d-none');
        });
        
        // Toggle password visibility
        $('.toggle-password').on('click', function() {
            var passwordField = $($(this).data('toggle'));
            var type = passwordField.attr('type') === 'password' ? 'text' : 'password';
            passwordField.attr('type', type);
            $(this).find('i').toggleClass('fa-eye fa-eye-slash');
        });
        
        // Switch between login and register forms
        $('.auth-toggle-link').on('click', function(e) {
            e.preventDefault();
            $('#login-form, #register-form').toggleClass('d-none');
        });
    }
    
    function initializeDashboard() {
        // Admin dashboard - assign manager to client
        $('#assign-manager-form').on('submit', function(e) {
            e.preventDefault();
            
            var clientId = $('#client-id').val();
            var managerId = $('#manager-id').val();
            
            if (!clientId || !managerId) {
                alert('Please select both a client and a manager');
                return;
            }
            
            var data = {
                action: 'car_leasing_assign_manager',
                client_id: clientId,
                manager_id: managerId,
                security: car_leasing_ajax.nonce
            };
            
            $.post(car_leasing_ajax.ajax_url, data, function(response) {
                if (response.success) {
                    alert('Manager assigned successfully');
                    location.reload();
                } else {
                    alert(response.data.message || 'An error occurred while assigning the manager');
                }
            }).fail(function() {
                alert('An error occurred. Please try again later.');
            });
        });
        
        // Manager dashboard - respond to application
        $('#respond-application-form').on('submit', function(e) {
            e.preventDefault();
            
            var applicationId = $('#application-id').val();
            var responseType = $('input[name="response-type"]:checked').val();
            var responseNote = $('#response-note').val();
            
            if (!applicationId || !responseType) {
                alert('Please select an application and a response type');
                return;
            }
            
            var data = {
                action: 'car_leasing_respond_application',
                application_id: applicationId,
                response_type: responseType,
                response_note: responseNote,
                security: car_leasing_ajax.nonce
            };
            
            $.post(car_leasing_ajax.ajax_url, data, function(response) {
                if (response.success) {
                    alert('Response submitted successfully');
                    location.reload();
                } else {
                    alert(response.data.message || 'An error occurred while submitting the response');
                }
            }).fail(function() {
                alert('An error occurred. Please try again later.');
            });
        });
    }
    
})(jQuery);
