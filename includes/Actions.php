<?php

/**
 * SomeCaptions Actions
 *
 * @package   Actions
 * @author    Mindell <mindell.zamora@gmail.com>
 * @license   GPL-2.0+
 * @link      https://github.com/mindell/
 * @copyright 2022 GPL
 */

namespace SoMeCaptions_WPClient\Includes;

class Actions {

    /**
     * Add actions for syncing category
     * 
     * @since 0.0.1
     */
    public function __construct(){
		$initialized = \get_option( 'somecaptions-client' . '-init' );
		if($initialized) {
			\add_action( 'create_category',  array( & $this, 'somecaptions_new_category' ),      10, 1 );
			\add_action( 'delete_category',  array( & $this, 'somecaptions_remove_category' ),   10, 1 );
			\add_action( 'add_user_role',    array( & $this, 'somecaptions_new_user_role' ),     10, 1 );
			\add_action( 'remove_user_role', array( & $this, 'somecaptions_removed_user_role' ), 10, 1 );
			\add_action( 'deleted_user',     array( & $this, 'somecaptions_deleted_user' ),      10, 1 );
			
			// Add action to send site info after admin is fully loaded
			\add_action( 'admin_init', array( & $this, 'maybe_send_site_info' ), 20 );
			
			// Add action to update site info when site title changes
			\add_action( 'update_option_blogname', array( & $this, 'update_site_info' ), 10, 0 );
			\add_action( 'update_option_siteurl', array( & $this, 'update_site_info' ), 10, 0 );
		}
		
		// Add AJAX handlers for settings improvements
		\add_action( 'wp_ajax_somecaptions_validate_api', array( $this, 'validate_api_settings' ) );
		\add_action( 'wp_ajax_somecaptions_load_domain_tab', array( $this, 'load_domain_tab' ) );
		\add_action( 'wp_ajax_somecaptions_save_settings', array( $this, 'save_general_settings' ) );
	}
    
	/**
	 * Fired when a new user role is added
	 * 
	 * @param int $user_id
	 * 
	 * @since 2.1.5
	 * 
	 * @return void
	 */
	public function somecaptions_new_user_role($user_id) {
		$author = \get_user_by('id', $user_id);
		if(isset($author->caps['author'])) {
			if($author->caps['author']) {
				$form_params = array(
					'display_name' => $author->display_name,
					'wp_user_id'   => $user_id,
				);
				$epoint = '/api/wpclient/new_author';
				ApiClient::request( $epoint, $form_params );
			}
		}
	}

	/**
	 * Fired when a role is removed from user
	 * 
	 * @param int $user_id
	 * 
	 * 
	 * @since 2.1.5
	 * 
	 * @return void
	 */
	public function somecaptions_removed_user_role($user_id) {
		$author = \get_user_by('id', $user_id);
		if(!isset($author->caps['author'])) {
			$form_params = array(
				'wp_user_id'   => $user_id
			);
			$epoint = '/api/wpclient/remove_author';
			ApiClient::request( $epoint, $form_params );
		}
		else {
			if($author->caps['author']) {
				$form_params = array(
					'display_name' => $author->display_name,
					'wp_user_id'   => $user_id,
				);
				$epoint = '/api/wpclient/new_author';
				ApiClient::request( $epoint, $form_params );
			}
		}
	}

    /**
	 * Fired when a new category is added
	 *
	 * @param int $category_id ID of the new category.
     * 
	 * @since 0.0.1
     * 
	 * @return void
	 */
	public function somecaptions_new_category($category_id) {
		$cat = \get_term($category_id,'category');
		if($cat->taxonomy == 'category') {
			$form_params = array(
				'name'	         => $cat->name,
				'term_id'        => $category_id,
			);
			$epoint      = '/api/wpclient/new_category';
			ApiClient::request( $epoint, $form_params );
		}

	}

	/**
	 * Fired when deleted a user
	 * 
	 * @param int $user_id
	 * 
	 * @since 2.1.5 
	 * 
	 * @return void
	 */
	public function somecaptions_deleted_user($user_id) {
		$form_params = array(
			'wp_user_id'   => $user_id
		);
		$epoint = '/api/wpclient/remove_author';
		ApiClient::request( $epoint, $form_params );
	}

	/**
	 * Fired when a category is deleted
	 *
	 * @param int $category_id ID of the new category.
	 * 
	 * @since 0.0.1
	 * 
	 * @return void
	 */
	public function somecaptions_remove_category( $category_id ) {
		$form_params = array(
						'term_id' => $category_id,
		);
		$ep = '/api/wpclient/remove_category';
		ApiClient::request( $ep, $form_params );
	}

	/**
	 * Check if site info has been sent and send it if not
	 *
	 * @since 2.2.2
	 *
	 * @return void
	 */
	public function maybe_send_site_info() {
		// Only run this once or when forced
		$site_info_sent = \get_option( 'somecaptions-client' . '-site-info-sent', false );
		
		if (!$site_info_sent) {
			// \error_log('SomeCaptions - Sending site info for the first time');
			$this->send_site_info();
			
			// Mark as sent so we don't do it again unless site info changes
			\update_option( 'somecaptions-client' . '-site-info-sent', true );
		}
	}

	/**
	 * Update site info when site title or URL changes
	 *
	 * @since 2.2.2
	 *
	 * @return void
	 */
	public function update_site_info() {
		// \error_log('SomeCaptions - Site info changed, updating');
		$this->send_site_info();
	}

	/**
	 * Send site info to the API
	 *
	 * @since 2.2.2
	 *
	 * @return void
	 */
	private function send_site_info() {
		// Get site info
		$site_name = \get_bloginfo('name');
		
		// If site name is empty, try alternative methods
		if (empty($site_name)) {
			// \error_log('SomeCaptions - Site Name is empty, trying alternative methods');
			$site_name = \get_option('blogname');
			// \error_log('SomeCaptions - Site Name from option: ' . $site_name);
			
			// If still empty, use the domain name as a fallback
			if (empty($site_name)) {
				$parsed_url = \wp_parse_url(\site_url());
				$site_name = isset($parsed_url['host']) ? $parsed_url['host'] : 'WordPress Site';
				// \error_log('SomeCaptions - Using domain as site name: ' . $site_name);
			}
		}
		
		$site_url = \site_url();
		
		// Log the values for debugging
		// \error_log('SomeCaptions - Sending Site Name: ' . $site_name);
		// \error_log('SomeCaptions - Sending Site URL: ' . $site_url);
		
		// Only send if we have values
		if (!empty($site_name) && !empty($site_url)) {
			$form_params = array(
				'site_name' => $site_name,
				'site_url'  => $site_url
			);
			$ep = '/api/wpclient/online';
			$response = ApiClient::request($ep, $form_params);
			
			// \error_log('SomeCaptions - Site info sent response: ' . print_r($response, true));
			return $response;
		}
		
		return false;
	}
	
	/**
	 * Validate API settings via AJAX
	 *
	 * @since 3.0.2
	 *
	 * @return void
	 */
	public function validate_api_settings() {
		// Check if this is an AJAX request
		if (!defined('DOING_AJAX') || !DOING_AJAX) {
			if (defined('WP_DEBUG') && WP_DEBUG) {
				// \error_log('SomeCaptions - validate_api_settings: Not an AJAX request');
			}
			\wp_send_json_error(array('message' => 'Invalid request method'));
			return;
		}

		// Verify nonce
		if (!isset($_POST['nonce']) || !\wp_verify_nonce(\sanitize_text_field(wp_unslash($_POST['nonce'])), 'somecaptions_validate_api')) {
			if (defined('WP_DEBUG') && WP_DEBUG) {
				// \error_log('SomeCaptions - validate_api_settings: Invalid nonce');
			}
			\wp_send_json_error(array('message' => 'Security check failed'));
			return;
		}
		
		// Get the submitted settings
		$endpoint = isset($_POST['endpoint']) ? \sanitize_text_field(wp_unslash($_POST['endpoint'])) : '';
		$api_key = isset($_POST['api_key']) ? \sanitize_text_field(wp_unslash($_POST['api_key'])) : '';
		
		if (empty($endpoint) || empty($api_key)) {
			\wp_send_json_error(array('message' => 'API endpoint and key are required'));
			return;
		}
		
		// Save the settings to the WordPress options table
		$options = \get_option('somecaptions-client' . '-settings', array());
		$options['endpoint'] = $endpoint;
		$options['api_key'] = $api_key;
		\update_option('somecaptions-client' . '-settings', $options);
		
		// Test the API connection with the newly saved settings
		try {
			// Use the ApiClient class to ensure we're using the same code path as the rest of the plugin
			$form_params = array(
				'site_url' => \site_url(),
				'site_name' => \get_bloginfo('name'),
				'wp_version' => \get_bloginfo('version'),
				'plugin_version' => SW_VERSION
			);
			
			if (defined('WP_DEBUG') && WP_DEBUG) {
				// \error_log('SomeCaptions - validate_api_settings: Testing API connection with endpoint: ' . $endpoint);
			}
			// Force ApiClient to reload settings from the database
			ApiClient::reset();
			$ep = '/api/wpclient/online';
			$response = ApiClient::request($ep, $form_params);
			
			// If we get here, the API connection was successful
			// Mark the plugin as initialized
			\update_option('somecaptions-client' . '-init', true);
			
			if (defined('WP_DEBUG') && WP_DEBUG) {
				// \error_log('SoMe Captions - validate_api_settings: API connection successful, plugin initialized');
			}
			
			// Send success response
			\wp_send_json_success(array(
				'message' => 'API connection successful! Your settings have been saved.',
				'show_domain_tab' => true
			));
		} catch (\Exception $e) {
			if (defined('WP_DEBUG') && WP_DEBUG) {
				// \error_log('SoMe Captions - validate_api_settings: API connection failed: ' . $e->getMessage());
			}
			\wp_send_json_error(array(
				'message' => 'API connection failed: ' . $e->getMessage()
			));
			return;
		}
	}
	
	/**
	 * Load domain tab content via AJAX
	 *
	 * @since 3.0.2
	 *
	 * @return void
	 */
	public function load_domain_tab() {
		ob_start();
		include_once SW_PLUGIN_ROOT . 'backend/views/settings-domain.php';
		$content = ob_get_clean();
		echo \wp_kses_post($content);
		\wp_die();
	}

	/**
	 * Save general settings via AJAX
	 *
	 * @since 3.0.2
	 *
	 * @return void
	 */
	public function save_general_settings() {
		// Always log for debugging
		\error_log('SomeCaptions - save_general_settings: AJAX request received');
		\error_log('POST data: ' . print_r($_POST, true));
		
		// Check if this is an AJAX request
		if (!defined('DOING_AJAX') || !DOING_AJAX) {
			\error_log('SomeCaptions - save_general_settings: Not an AJAX request');
			\wp_send_json_error(array('message' => 'Invalid request method'));
			return;
		}

		// Verify nonce
		if (!isset($_POST['_wpnonce']) || !\wp_verify_nonce(\sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'somecaptions_save_settings')) {
			\error_log('SomeCaptions - save_general_settings: Invalid nonce');
			\error_log('Received nonce: ' . (isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : 'not set'));
			\wp_send_json_error(array('message' => 'Security check failed'));
			return;
		}
		
		// Get the submitted settings
		$settings = isset($_POST['somecaptions-client-settings']) ? $_POST['somecaptions-client-settings'] : array();
		\error_log('SomeCaptions - save_general_settings: Settings array: ' . print_r($settings, true));
		
		// Extract and sanitize the values
		$endpoint = isset($settings['endpoint']) ? \sanitize_text_field(wp_unslash($settings['endpoint'])) : '';
		$api_key = isset($settings['api_key']) ? \sanitize_text_field(wp_unslash($settings['api_key'])) : '';
		
		\error_log('SomeCaptions - save_general_settings: Endpoint: ' . $endpoint);
		\error_log('SomeCaptions - save_general_settings: API Key: ' . substr($api_key, 0, 5) . '...');
		
		if (empty($endpoint) || empty($api_key)) {
			\error_log('SomeCaptions - save_general_settings: Empty endpoint or API key');
			\wp_send_json_error(array('message' => 'API endpoint and key are required'));
			return;
		}
		
		// Let's check what option key is used in sw_get_settings()
		\error_log('SomeCaptions - save_general_settings: Checking sw_get_settings() result: ' . print_r(sw_get_settings(), true));
		
		// The option key from the CMB2 form
		$cmb2_option_key = 'somecaptions-client_options';
		
		// The option key used in sw_get_settings()
		$settings_option_key = 'somecaptions-client-settings';
		
		// Get existing options from both possible keys
		$cmb2_options = \get_option($cmb2_option_key, array());
		$settings_options = \get_option($settings_option_key, array());
		
		\error_log('SomeCaptions - save_general_settings: CMB2 options: ' . print_r($cmb2_options, true));
		\error_log('SomeCaptions - save_general_settings: Settings options: ' . print_r($settings_options, true));
		
		// Update both options to ensure consistency
		$cmb2_options['endpoint'] = $endpoint;
		$cmb2_options['api_key'] = $api_key;
		
		$settings_options['endpoint'] = $endpoint;
		$settings_options['api_key'] = $api_key;
		
		// Force update by deleting the option first
		\delete_option($cmb2_option_key);
		\delete_option($settings_option_key);
		
		// Update both options in the database
		$result1 = \update_option($cmb2_option_key, $cmb2_options);
		$result2 = \update_option($settings_option_key, $settings_options);
		
		\error_log('SomeCaptions - save_general_settings: CMB2 update result: ' . ($result1 ? 'true' : 'false'));
		\error_log('SomeCaptions - save_general_settings: Settings update result: ' . ($result2 ? 'true' : 'false'));
		
		// Get the options back to verify they were saved
		$saved_cmb2_options = \get_option($cmb2_option_key);
		$saved_settings_options = \get_option($settings_option_key);
		
		\error_log('SomeCaptions - save_general_settings: Saved CMB2 options: ' . print_r($saved_cmb2_options, true));
		\error_log('SomeCaptions - save_general_settings: Saved settings options: ' . print_r($saved_settings_options, true));
		
		// Send success response
		\wp_send_json_success(array(
			'message' => 'Settings saved successfully!',
			'cmb2_settings' => $saved_cmb2_options,
			'settings' => $saved_settings_options,
			'cmb2_result' => $result1,
			'settings_result' => $result2
		));
	}

}