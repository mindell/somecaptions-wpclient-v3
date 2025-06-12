<?php

/**
 * SoMe Captions Admin Assets
 *
 * @package   SoMeCaptions_WPClient\Includes
 * @author    Mindell <mindell.zamora@gmail.com>
 * @license   GPL-2.0+
 * @link      https://github.com/mindell/
 * @copyright 2022 GPL
 */
 
namespace SoMeCaptions_WPClient\Includes;

/**
 * Class Admin
 * 
 * Handles admin-specific functionality including assets loading
 */
class Admin {

    /**
     * Initialize the class
     */
    public function __construct() {
        // Add hooks for admin assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Add hooks for admin notices
        add_action('admin_notices', array($this, 'display_admin_notices'));
        
        // Localize admin scripts
        add_action('admin_enqueue_scripts', array($this, 'localize_admin_scripts'));
    }

    /**
     * Enqueue admin-specific CSS and JavaScript
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_assets($hook) {
        // Only load on plugin settings page
        if (strpos($hook, 'somecaptions-client') === false) {
            return;
        }
        
        // Get version for cache busting
        $version = defined('SW_VERSION') ? SW_VERSION . '.' . time() : '1.0.0.' . time();
        
        // Enqueue admin styles
        wp_enqueue_style(
            'somecaptions-admin-styles',
            plugin_dir_url(SW_PLUGIN_ABSOLUTE) . 'assets/css/admin-styles.css',
            array(),
            $version
        );
        
        // Enqueue help & support styles
        wp_enqueue_style(
            'somecaptions-help-support',
            plugin_dir_url(SW_PLUGIN_ABSOLUTE) . 'assets/css/help-support.css',
            array('somecaptions-admin-styles'),
            $version
        );
        
        // Add cache busting by appending timestamp to script version
        $script_version = SW_VERSION . '-' . time();

        // Enqueue admin scripts
        // We're replacing the old tab navigation with a new, more robust implementation
        /* Commenting out the old tab navigation script
        wp_enqueue_script(
            'somecaptions-admin-scripts',
            plugin_dir_url(SW_PLUGIN_ABSOLUTE) . 'assets/js/admin-scripts-new.js',
            array('jquery'),
            $script_version,
            true
        );
        */
        
        // Enqueue our new tab navigation script
        wp_enqueue_script(
            'somecaptions-tab-navigation',
            plugin_dir_url(SW_PLUGIN_ABSOLUTE) . 'assets/js/tab-navigation.js',
            array('jquery'),
            $script_version,
            true
        );
        
        // Enqueue domain verification script
        wp_enqueue_script(
            'somecaptions-domain-verification',
            plugin_dir_url(SW_PLUGIN_ABSOLUTE) . 'assets/js/domain-verification.js',
            array('somecaptions-tab-navigation'),
            $script_version,
            true
        );
        
        // Enqueue settings handler script
        wp_enqueue_script(
            'somecaptions-settings-handler',
            plugin_dir_url(SW_PLUGIN_ABSOLUTE) . 'assets/js/settings-handler.js',
            array('somecaptions-tab-navigation'),
            $script_version,
            true
        );
        
        // Enqueue system info script
        wp_enqueue_script(
            'somecaptions-system-info',
            plugin_dir_url(SW_PLUGIN_ABSOLUTE) . 'assets/js/system-info.js',
            array('somecaptions-admin-scripts'),
            $version,
            true
        );
        
        // Enqueue tab fix script (loads last to ensure it can fix any tab issues)
        wp_enqueue_script(
            'somecaptions-tab-fix',
            plugin_dir_url(SW_PLUGIN_ABSOLUTE) . 'assets/js/tab-fix.js',
            array('somecaptions-admin-scripts', 'somecaptions-domain-verification', 'somecaptions-settings-handler'),
            $version . '.' . time(), // Force no caching
            true
        );
    }
    
    /**
     * Localize admin scripts with necessary data
     *
     * @param string $hook Current admin page hook
     */
    public function localize_admin_scripts($hook) {
        // Only load on plugin settings page
        if (strpos($hook, 'somecaptions-client') === false) {
            return;
        }
        
        // Localize the script with data
        wp_localize_script(
            'somecaptions-admin-scripts',
            'somecaptions_admin',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('somecaptions_admin_nonce'),
                'i18n' => array(
                    'saving' => esc_js(__('Saving...', 'somecaptions-client')),
                    'saved' => esc_js(__('Settings saved successfully', 'somecaptions-client')),
                    'error' => esc_js(__('An error occurred', 'somecaptions-client')),
                    'verifying' => esc_js(__('Verifying domain...', 'somecaptions-client')),
                    'verified' => esc_js(__('Domain verified successfully', 'somecaptions-client')),
                    'verificationFailed' => esc_js(__('Domain verification failed', 'somecaptions-client')),
                    'confirmReset' => esc_js(__('Are you sure you want to reset all settings? This cannot be undone.', 'somecaptions-client')),
                    'pleaseEnterCode' => esc_js(__('Please enter a verification code', 'somecaptions-client')),
                    'connectionSuccess' => esc_js(__('API connection successful', 'somecaptions-client')),
                    'connectionFailed' => esc_js(__('API connection failed', 'somecaptions-client')),
                    'settingsSaved' => esc_js(__('Settings saved', 'somecaptions-client')),
                    'savingSettings' => esc_js(__('Saving settings...', 'somecaptions-client')),
                    'validatingApi' => esc_js(__('Validating API connection...', 'somecaptions-client')),
                )
            )
        );
        
        // Localize domain verification script
        wp_localize_script(
            'somecaptions-domain-verification',
            'somecaptionsDomainVerification',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('somecaptions_verify_domain'),
                'verifying' => esc_js(__('Verifying domain...', 'somecaptions-client')),
                'pleaseEnterCode' => esc_js(__('Please enter a verification code', 'somecaptions-client')),
                'success' => esc_js(__('Domain verified successfully', 'somecaptions-client')),
                'error' => esc_js(__('Domain verification failed', 'somecaptions-client'))
            )
        );
        
        // Localize system info script
        wp_localize_script(
            'somecaptions-system-info',
            'somecaptionsSystemInfo',
            array(
                'copySuccess' => esc_js(__('System information copied to clipboard', 'somecaptions-client')),
                'copyError' => esc_js(__('Failed to copy system information', 'somecaptions-client')),
            )
        );
    }
    
    /**
     * Display admin notices
     */
    public function display_admin_notices() {
        // Check for transient notices
        $notices = get_transient('somecaptions_admin_notices');
        
        if (!$notices || !is_array($notices)) {
            return;
        }
        
        foreach ($notices as $notice) {
            $type = isset($notice['type']) ? $notice['type'] : 'info';
            $message = isset($notice['message']) ? $notice['message'] : '';
            $dismissible = isset($notice['dismissible']) && $notice['dismissible'] ? 'is-dismissible' : '';
            
            if (!empty($message)) {
                printf(
                    '<div class="notice notice-%s %s somecaptions-notice"><p>%s</p></div>',
                    esc_attr($type),
                    esc_attr($dismissible),
                    wp_kses_post($message)
                );
            }
        }
        
        // Clear the transient after displaying notices
        delete_transient('somecaptions_admin_notices');
    }
    
    /**
     * Add an admin notice
     *
     * @param string $message The notice message
     * @param string $type The notice type (success, error, warning, info)
     * @param bool $dismissible Whether the notice is dismissible
     */
    public static function add_admin_notice($message, $type = 'info', $dismissible = true) {
        $notices = get_transient('somecaptions_admin_notices');
        
        if (!$notices || !is_array($notices)) {
            $notices = array();
        }
        
        $notices[] = array(
            'message' => $message,
            'type' => $type,
            'dismissible' => $dismissible
        );
        
        set_transient('somecaptions_admin_notices', $notices, 60 * 5); // 5 minutes expiration
    }
}
