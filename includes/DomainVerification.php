<?php
/**
 * SomeCaptions Domain Verification
 * 
 * This file contains the code for domain verification in the WordPress plugin.
 * It should be integrated into the existing SoMe Captions Client plugin.
 */

namespace SoMeCaptions_WPClient\Includes;

/**
 * Global debug message array to store debug messages in memory
 */
global $sc_debug_messages;
$sc_debug_messages = array();

/**
 * Helper function to log debug messages to memory
 */
function sw_debug_log($message) {
    global $sc_debug_messages;
    
    // Format the message
    $timestamp = gmdate('[Y-m-d H:i:s]');
    $log_message = $timestamp . ' ' . $message;
    
    // Store in memory array
    $sc_debug_messages[] = $log_message;
    
    // Also log to PHP error log as a fallback
    // error_log('SomeCaptions: ' . $log_message);
    
    return $log_message;
}

// Test log entry
sw_debug_log('SomeCaptions Debug Log Initialized - ' . gmdate('Y-m-d H:i:s'));

class DomainVerification {
    /**
     * Initialize the domain verification functionality
     */
    public function __construct() {
        // Debug log for initialization
        sw_debug_log('DomainVerification - Initializing plugin');
        
        // Add settings field for domain verification
        add_action('cmb2_admin_init', array($this, 'add_verification_fields'));
        
        // Add AJAX handler for domain verification
        add_action('wp_ajax_somecaptions_verify_domain', array($this, 'verify_domain'));
        sw_debug_log('DomainVerification - Registered AJAX action: wp_ajax_somecaptions_verify_domain');
        
        // Add AJAX handler to save verification status
        add_action('wp_ajax_somecaptions_save_verification', array($this, 'save_verification'));
        sw_debug_log('DomainVerification - Registered AJAX action: wp_ajax_somecaptions_save_verification');
        
        // Register a test action to verify AJAX is working
        add_action('wp_ajax_somecaptions_test_ajax', function() {
            sw_debug_log('DomainVerification - Test AJAX action called');
            wp_send_json_success(array('message' => 'AJAX is working'));
        });
        sw_debug_log('DomainVerification - Registered AJAX action: wp_ajax_somecaptions_test_ajax');
        
        // Register an action to get the debug log
        add_action('wp_ajax_somecaptions_get_debug_log', array($this, 'get_debug_log'));
        sw_debug_log('DomainVerification - Registered AJAX action: wp_ajax_somecaptions_get_debug_log');
        
        // Debug mode can be enabled by adding a filter
        if (apply_filters('somecaptions_debug_mode', false)) {
            // Add an admin notice to show debug information
            add_action('admin_notices', function() {
                global $sc_debug_messages;
                
                echo '<div class="notice notice-info"><p>';
                echo '<strong>' . esc_html__('SomeCaptions Debug Mode', 'somecaptions-client') . '</strong><br>';
                echo esc_html__('Debug mode is enabled. Check the browser console for debug information.', 'somecaptions-client');
                echo '</p></div>';
            });
        }
        
        // Add action to log all AJAX requests for debugging
        add_action('admin_init', function() {
            sw_debug_log('DomainVerification - Admin initialized');
            if (defined('DOING_AJAX') && DOING_AJAX) {
                // Only process AJAX requests with valid nonces for our plugin actions
                $somecaptions_actions = array(
                    'somecaptions_verify_domain',
                    'somecaptions_save_verification',
                    'somecaptions_test_ajax',
                    'somecaptions_get_debug_log'
                );
                
                // Properly sanitize and unslash the action parameter
                $action = isset($_REQUEST['action']) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'unknown action';
                
                // Check for nonce if this is one of our plugin's AJAX actions
                if (in_array($action, $somecaptions_actions)) {
                    sw_debug_log('DomainVerification - SoMe Captions AJAX request detected: ' . $action);
                    
                    // Verify nonce for all our AJAX actions
                    // Note: Each AJAX handler will also verify its own nonce, this is an additional security layer
                    if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce(
                        sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])),
                        'somecaptions_api_action'
                    )) {
                        sw_debug_log('DomainVerification - Admin init: Invalid nonce in AJAX request');
                    }
                }
            }
        });
    }
    
    /**
     * Add verification fields to the settings page
     */
    public function add_verification_fields() {
        $cmb = \cmb2_get_metabox('somecaptions-client' . '_options');
        
        if (!$cmb) {
            return;
        }
        
        // Add verification code field
        $cmb->add_field(array(
            'name'    => __('Domain Verification Code', 'somecaptions-client'),
            'desc'    => __('Enter the verification code from your SomeCaptions dashboard', 'somecaptions-client'),
            'id'      => 'verification_code',
            'type'    => 'text',
            'default' => '',
        ));
        
        // Add verification button
        $cmb->add_field(array(
            'name' => '',
            'desc' => '<button type="button" id="verify-domain-btn" class="button button-primary">Verify Domain</button>
                      <span id="verification-status"></span>

                      <script>
                        jQuery(document).ready(function($) {
                            // Check if ajaxurl is defined
                            if (typeof ajaxurl === "undefined") {
                                console.log("ajaxurl not defined, adding fallback");
                                window.ajaxurl = "' . admin_url('admin-ajax.php') . '";
                            }
                            
                            // Domain verification via AJAX
                            $("#verify-domain-btn").on("click", function() {
                                const verification_code = $("#verification_code").val();
                                if (!verification_code) {
                                    $("#verification-status").html("<span style=\'color:red\'>Please enter a verification code</span>");
                                    return;
                                }
                                
                                $("#verification-status").html("<span style=\'color:blue\'>Verifying...</span>");
                                
                                $.ajax({
                                    url: ajaxurl,
                                    type: "POST",
                                    data: {
                                        action: "somecaptions_verify_domain",
                                        verification_code: verification_code,
                                        nonce: "' . wp_create_nonce('somecaptions_verify_domain') . '"
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            $("#verification-status").html("<span style=\'color:green\'>Domain verified successfully!</span>");
                                        } else {
                                            $("#verification-status").html("<span style=\'color:red\'>" + (response.data ? response.data.message : "Unknown error") + "</span>");
                                        }
                                    },
                                    error: function() {
                                        $("#verification-status").html("<span style=\'color:red\'>Verification failed. Please try again.</span>");
                                    }
                                });
                            });
                        });
                      </script>',
            'id'   => 'verification_button',
            'type' => 'title',
        ));
    }
    
    /**
     * AJAX handler to retrieve the debug log contents from memory
     */
    public function get_debug_log() {
        global $sc_debug_messages;
        sw_debug_log('DomainVerification - get_debug_log() method called');
        
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'somecaptions_verify_domain')) {
            sw_debug_log('DomainVerification - Invalid nonce in get_debug_log');
            wp_send_json_error(array('message' => 'Invalid security token'));
            return;
        }
        
        // Get system information for debugging
        $debug_info = array(
            'php_version' => PHP_VERSION,
            'wordpress_version' => get_bloginfo('version'),
            'server_software' => isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : 'Unknown',
            'user' => get_current_user(),
            'time' => gmdate('Y-m-d H:i:s'),
            'wp_content_dir' => defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : 'Not defined',
            'plugin_dir' => dirname(__FILE__),
            'temp_dir' => sys_get_temp_dir(),
            'is_ajax' => defined('DOING_AJAX') && DOING_AJAX ? 'Yes' : 'No',
            'admin_url' => admin_url('admin-ajax.php'),
            'site_url' => site_url(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'error_log_path' => ini_get('error_log')
        );
        
        // Get the in-memory debug messages
        $log_content = implode("\n", $sc_debug_messages);
        
        // Also try to get the PHP error log if possible
        $error_log_content = '';
        $error_log_path = ini_get('error_log');
        if (!empty($error_log_path) && file_exists($error_log_path) && is_readable($error_log_path)) {
            $error_log_content = $this->tail_file($error_log_path, 50);
        }
        
        // Send the debug information
        wp_send_json_success(array(
            'log' => $log_content,
            'system_info' => $debug_info,
            'error_log' => $error_log_content,
            'error_log_path' => $error_log_path
        ));
    }
    
    /**
     * Helper function to get the last N lines of a file
     */
    private function tail_file($file_path, $lines = 100) {
        $file = file($file_path);
        if (count($file) < $lines) {
            return implode('', $file);
        } else {
            return implode('', array_slice($file, -$lines));
        }
    }
    
    /**
     * AJAX handler to save verification status after successful direct API call
     */
    public function save_verification() {
        // Debug log for AJAX handler
        sw_debug_log('DomainVerification - save_verification() method called');
        
        // Debug logging removed for production code
        // For development, uncomment the following code block:
        /*
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // Sanitize data before logging
            $sanitized_post = array_map('sanitize_text_field', wp_unslash($_POST));
            $sanitized_request = array_map('sanitize_text_field', wp_unslash($_REQUEST));
            
            // Remove sensitive data
            if (isset($sanitized_post['api_key'])) $sanitized_post['api_key'] = '***REDACTED***';
            if (isset($sanitized_request['api_key'])) $sanitized_request['api_key'] = '***REDACTED***';
            
            // Log sanitized data
            sw_debug_log('DomainVerification - POST data available');
            sw_debug_log('DomainVerification - REQUEST data available');
        }
        */
        
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'somecaptions_verify_domain')) {
            sw_debug_log('DomainVerification - Invalid nonce in save_verification');
            wp_send_json_error(array('message' => 'Invalid security token'));
            return;
        }
        
        // Save verification status
        update_option('somecaptions-client' . '-domain-verified', true);
        sw_debug_log('DomainVerification - Saved verification status');
        wp_send_json_success(array('message' => 'Verification status saved'));
    }
    
    /**
     * AJAX handler for domain verification
     */
    public function verify_domain() {
        // Debug log for AJAX handler
        if (defined('WP_DEBUG') && WP_DEBUG) {
            sw_debug_log('DomainVerification - verify_domain() method called');
            // Debug logging of request data removed for production code
            // For development, uncomment the following code block:
            /*
            // Only log essential data, not the entire arrays
            $post_keys = array_map('sanitize_text_field', array_keys($_POST));
            $request_keys = array_map('sanitize_text_field', array_keys($_REQUEST));
            sw_debug_log('DomainVerification - POST data keys available');
            sw_debug_log('DomainVerification - REQUEST data keys available');
            */
        }
        
        // Check if this is an AJAX request
        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                sw_debug_log('DomainVerification - WARNING: Not an AJAX request!');
            }
            wp_send_json_error(array('message' => 'Invalid request method'));
            return;
        }
        
        // Verify nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'somecaptions_verify_domain')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                sw_debug_log('DomainVerification - Invalid nonce!');
            }
            wp_send_json_error(array('message' => 'Security check failed'));
            return;
        }
        
        // Get verification code from request
        $verification_code = isset($_POST['verification_code']) ? sanitize_text_field(wp_unslash($_POST['verification_code'])) : '';
        
        // Get domain name from site URL
        $site_url = site_url();
        $parsed_url = wp_parse_url($site_url);
        $domain_name = $parsed_url['host'];
        
        // Debug log only in debug mode
        if (defined('WP_DEBUG') && WP_DEBUG) {
            sw_debug_log('DomainVerification - Domain: ' . $domain_name);
            sw_debug_log('DomainVerification - Code: ' . $verification_code);
        }
        
        // Try to verify domain using the API endpoint
        try {
            // The verify-domain endpoint expects JSON, not form data
            // Create JSON params for API request
            $json_params = json_encode(array(
                'domain_name' => $domain_name,
                'verification_code' => $verification_code
            ));
            
            // Get API settings
            $opts = \sw_get_settings();
            
            // Debug log API settings (with redacted API key) only in debug mode
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // Debug logging removed for production code
                // For development, uncomment the following code block:
                /*
                $log_opts = $opts;
                if (isset($log_opts['api_key'])) {
                    $log_opts['api_key'] = '***REDACTED***';
                }
                sw_debug_log('DomainVerification - API Settings available');
                */
            }
            
            $api_endpoint = trailingslashit($opts['endpoint']);
            $api_key = $opts['api_key'];
            
            sw_debug_log('DomainVerification - API Endpoint: ' . $api_endpoint);
            sw_debug_log('DomainVerification - API Key set: ' . (!empty($api_key) ? 'Yes' : 'No'));
            
            if (empty($api_key)) {
                sw_debug_log('DomainVerification - API key not configured');
                wp_send_json_error(array('message' => 'API key is not configured'));
                return;
            }
            
            // Make API request directly since this endpoint expects JSON
            $target_url = $api_endpoint . 'api/wpclient/verify-domain';
            if (defined('WP_DEBUG') && WP_DEBUG) {
                sw_debug_log('DomainVerification - Sending request to: ' . $target_url);
                sw_debug_log('DomainVerification - Request body: ' . $json_params);
            }
            
            $headers = array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
                'Origin' => site_url()
            );
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // Debug logging removed for production code
                // For development, uncomment the following code block:
                /*
                // Redact sensitive information from headers
                $safe_headers = $headers;
                if (isset($safe_headers['Authorization'])) {
                    $safe_headers['Authorization'] = 'Bearer ****';
                }
                sw_debug_log('DomainVerification - Request headers available');
                */
            }
            
            $response = ApiClient::request('/api/wpclient/verify-domain', array(
                'domain_name' => $domain_name,
                'verification_code' => $verification_code
            ));
            
           // Check for WP_Error
            if (!$response) {
                wp_send_json_error(['message' => 'API request failed']);
                return;
            }

            // Use the correct Guzzle methods
            $response_code = $response->getStatusCode();
            $body = (string) $response->getBody();

            // Parse the JSON response
            $data = json_decode($body, true);

            // Check success condition
            if ($response_code === 200 && $data['success']) {
                // Verify nonce again before updating options
                if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'somecaptions_verify_domain')) {
                    sw_debug_log('DomainVerification - Invalid nonce before updating options');
                    wp_send_json_error(['message' => 'Security check failed']);
                    return;
                }
                
                update_option('somecaptions-client-domain-verified', true);
                wp_send_json_success(['message' => 'Domain verified successfully']);
            } else {
                $error_message = $data['message'] ?? 'Domain verification failed';
                wp_send_json_error(['message' => $error_message]);
            }
            
            if (isset($data['success']) && $data['success']) {
                // Verify nonce again before updating options
                if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'somecaptions_verify_domain')) {
                    sw_debug_log('DomainVerification - Invalid nonce before updating options');
                    wp_send_json_error(['message' => 'Security check failed']);
                    return;
                }
                
                // Save verification status
                update_option('somecaptions-client' . '-domain-verified', true);
                sw_debug_log('DomainVerification - Success! Domain verified');
                wp_send_json_success(array('message' => 'Domain verified successfully'));
            } else {
                $error_message = isset($data['message']) ? $data['message'] : 'Domain verification failed';
                sw_debug_log('DomainVerification - Failed: ' . $error_message);
                wp_send_json_error(array('message' => $error_message));
            }
        } catch (\Exception $e) {
            sw_debug_log('DomainVerification - Exception: ' . $e->getMessage());
            sw_debug_log('DomainVerification - Exception trace: ' . $e->getTraceAsString());
            wp_send_json_error(array('message' => 'Verification failed: ' . $e->getMessage()));
        }
    }
}

// Initialize domain verification
new DomainVerification();
