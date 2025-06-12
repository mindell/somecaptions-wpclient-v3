<?php
/**
 * SomeCaptions Client - Connection Checker
 *
 * @package SoMeCaptions_WPClient
 * @subpackage Includes
 * @since 1.0.0
 */

namespace SoMeCaptions_WPClient\Includes;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Connection Checker Class
 * 
 * Handles API connection status checking and management
 */
class ConnectionChecker {

    /**
     * Initialize the class
     */
    public static function init() {
        // Add hooks
        add_action('admin_init', [self::class, 'maybe_check_connection']);
        add_action('somecaptions_daily_tasks', [self::class, 'check_connection_status']);
        
        // Register the daily cron event if not already scheduled
        if (!wp_next_scheduled('somecaptions_daily_tasks')) {
            wp_schedule_event(time(), 'daily', 'somecaptions_daily_tasks');
        }
    }

    /**
     * Check if we should verify the connection status
     */
    public static function maybe_check_connection() {
        // Only run on admin pages
        if (!is_admin()) {
            return;
        }
        
        // Check if we're on the plugin's admin page
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'somecaptions-client') === false) {
            return;
        }
        
        // Check if we need to verify the connection
        $last_check = get_option('somecaptions-client-last-connected', 0);
        $check_interval = 3600; // Check once per hour
        
        if (time() - $last_check > $check_interval) {
            self::check_connection_status();
        }
    }

    /**
     * Check if the API connection is still valid
     * 
     * @return boolean True if connected, false otherwise
     */
    public static function check_connection_status() {
        $api_key = cmb2_get_option('somecaptions-client' . '-settings', 'api_key', '');
        
        // If no API key, we're definitely not connected
        if (empty($api_key)) {
            update_option('somecaptions-client-connected', false);
            return false;
        }
        
        // Make a request to verify the connection
        $form_params = array(
            'site_name' => get_bloginfo('name'),
            'site_url'  => site_url()
        );
        $ep = '/api/wpclient/online';
        $response = ApiClient::request($ep, $form_params);
        
        // Update connection status based on response
        $is_connected = false;
        if ($response && method_exists($response, 'getStatusCode') && $response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            update_option('somecaptions-client-connected', true);
            update_option('somecaptions-client-last-connected', time());
            $is_connected = true;
        } else {
            update_option('somecaptions-client-connected', false);
        }
        
        return $is_connected;
    }
}

// Initialize the class
ConnectionChecker::init();
