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

namespace SomeCaptions_WPClient\Includes;

class Actions {

    /**
     * Add actions for syncing category
     * 
     * @since 0.0.1
     */
    public function __construct(){
		$initialized = \get_option( SW_TEXTDOMAIN . '-init' );
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
		$site_info_sent = \get_option( SW_TEXTDOMAIN . '-site-info-sent', false );
		
		if (!$site_info_sent) {
			\error_log('SomeCaptions - Sending site info for the first time');
			$this->send_site_info();
			
			// Mark as sent so we don't do it again unless site info changes
			\update_option( SW_TEXTDOMAIN . '-site-info-sent', true );
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
		\error_log('SomeCaptions - Site info changed, updating');
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
			\error_log('SomeCaptions - Site Name is empty, trying alternative methods');
			$site_name = \get_option('blogname');
			\error_log('SomeCaptions - Site Name from option: ' . $site_name);
			
			// If still empty, use the domain name as a fallback
			if (empty($site_name)) {
				$parsed_url = parse_url(\site_url());
				$site_name = isset($parsed_url['host']) ? $parsed_url['host'] : 'WordPress Site';
				\error_log('SomeCaptions - Using domain as site name: ' . $site_name);
			}
		}
		
		$site_url = \site_url();
		
		// Log the values for debugging
		\error_log('SomeCaptions - Sending Site Name: ' . $site_name);
		\error_log('SomeCaptions - Sending Site URL: ' . $site_url);
		
		// Only send if we have values
		if (!empty($site_name) && !empty($site_url)) {
			$form_params = array(
				'site_name' => $site_name,
				'site_url'  => $site_url
			);
			$ep = '/api/wpclient/online';
			$response = ApiClient::request($ep, $form_params);
			
			\error_log('SomeCaptions - Site info sent response: ' . print_r($response, true));
			return $response;
		}
		
		return false;
	}

}