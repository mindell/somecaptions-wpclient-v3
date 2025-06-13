<?php

/**
 * SomeCaptions API Client
 *
 * @package   ApiClient
 * @author    Mindell <mindell.zamora@gmail.com>
 * @license   GPL-2.0+
 * @link      https://github.com/mindell/
 * @copyright 2022 GPL
 */

namespace SoMeCaptions_WPClient\Includes;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\InvalidArgumentException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use GuzzleHttp\Exception\TransferException;

class ApiClient{
    /**
     * The client
     * 
     * @since 0.0.1
     * 
     * @var   object
     */
	protected static $client;

    /**
     * Return the initialized client
     * 
     * @since 0.0.1
     * 
     * @return object The client
     */
	protected static function client(){
		if(is_null(self::$client)){
			$opts = \sw_get_settings();
			self::$client = new Client([
				// Base URI is used with relative requests
				'base_uri' => $opts['endpoint'],
				// You can set any number of default request options.
				'timeout'  => 10,
			]);
		}
		return self::$client;
	}
	
	/**
	 * Reset the API client to force it to reload settings
	 * 
	 * @since 3.0.2
	 * 
	 * @return void
	 */
	public static function reset() {
		self::$client = null;
	}

	/**
	 * Generic private function for making a POST request
	 *
	 * @param int $category_id ID of the new category.
     * 
	 * @since 0.0.1
     * 
	 * @return any
	 */
	public static function request( $ep, $form_params) {
		// Sanitize endpoint parameter
		$ep = sanitize_text_field($ep);
		
		// Validate form parameters
		if (!is_array($form_params)) {
			$form_params = array();
		} else {
			// Recursively sanitize all form parameters
			$form_params = self::sanitize_recursive($form_params);
		}
		
		$client = self::client();
		$opts   = \sw_get_settings();
		$api_key = isset($opts['api_key']) ? sanitize_text_field($opts['api_key']) : '';
		
		// Debug log only in development mode
		if (defined('WP_DEBUG') && WP_DEBUG) {
			// \error_log('SomeCaptions API Request - Endpoint: ' . $ep);
			// \error_log('SomeCaptions API Request - API Key: ' . $api_key);
			// \error_log('SomeCaptions API Request - Form Params: ' . print_r($form_params, true));
		}
		
		$res    = null;
		try{
			// Ensure we're sending form data properly
			$request_options = array(
				'headers' => array(
					'Accept'         => 'application/json',
					'Authorization'  => 'Bearer ' . $api_key,
					'Origin'         => site_url(),
					'Content-Type'   => 'application/json',
				),
			);
			if (!is_array($form_params)) {
				$form_params = [];
			}
			// Only add json data if we have data to send
			if (!empty($form_params)) {
				// Debug the params before sending
				if (defined('WP_DEBUG') && WP_DEBUG) {
					// Create a copy of form params for logging to redact sensitive information
					$log_params = $form_params;
					// Redact sensitive information
					if (isset($log_params['api_key'])) {
						$log_params['api_key'] = '***REDACTED***';
					}
					// \error_log('SomeCaptions API Request - Params Before: ' . print_r($log_params, true));
				}
				
				// Ensure all values are properly encoded and non-empty
				foreach ($form_params as $key => $value) {
					// Convert any null or false values to empty strings
					if ($value === null || $value === false) {
						$form_params[$key] = '';
					}
				}
				
				if (defined('WP_DEBUG') && WP_DEBUG) {
					// Create a copy of form params for logging to redact sensitive information
					$log_params = $form_params;
					// Redact sensitive information
					if (isset($log_params['api_key'])) {
						$log_params['api_key'] = '***REDACTED***';
					}
					// \error_log('SomeCaptions API Request - Params After: ' . print_r($log_params, true));
				}
				// Send as JSON body instead of form params
				$request_options['json'] = $form_params;
			}
			
			if (defined('WP_DEBUG') && WP_DEBUG) {
				// Create a copy of request options for logging to redact sensitive information
				$log_options = $request_options;
				// Redact sensitive information in headers
				if (isset($log_options['headers']) && isset($log_options['headers']['Authorization'])) {
					$log_options['headers']['Authorization'] = '***REDACTED***';
				}
				// Redact sensitive information in JSON body
				if (isset($log_options['json']) && isset($log_options['json']['api_key'])) {
					$log_options['json']['api_key'] = '***REDACTED***';
				}
				// \error_log('SomeCaptions API Request - Options: ' . print_r($log_options, true));
			}
			
			$res = $client->request('POST', $ep, $request_options);
		
		// If we get here, the request was successful
		// Update the connection status option
		if ($res->getStatusCode() >= 200 && $res->getStatusCode() < 300) {
			\update_option('somecaptions-client-connected', true);
			
			// Store the last successful connection time
			\update_option('somecaptions-client-last-connected', time());
		}
		
		// Debug response only in development mode
		if (defined('WP_DEBUG') && WP_DEBUG) {
			// \error_log('SomeCaptions API Response - Status: ' . $res->getStatusCode());
			// \error_log('SomeCaptions API Response - Body: ' . $res->getBody());
		}
		}catch(\GuzzleHttp\Exception\BadResponseException $e){
			self::_error( $e->getMessage() );
			// Mark as disconnected on error
			\update_option('somecaptions-client-connected', false);
			if (defined('WP_DEBUG') && WP_DEBUG) { 
				// \error_log('SomeCaptions API Error - BadResponseException: ' . $e->getMessage());
			}
		}catch(\GuzzleHttp\Exception\ClientException $e){
			self::_error( $e->getMessage() );
			if (defined('WP_DEBUG') && WP_DEBUG) { 
				// \error_log('SomeCaptions API Error - ClientException: ' . $e->getMessage());
			}
		}catch(\GuzzleHttp\Exception\ConnectException $e){
			self::_error( $e->getMessage() );
			if (defined('WP_DEBUG') && WP_DEBUG) { 
				// \error_log('SomeCaptions API Error - ConnectException: ' . $e->getMessage());
			}
		}catch(\GuzzleHttp\Exception\GuzzleException $e){
			self::_error( $e->getMessage() );
			if (defined('WP_DEBUG') && WP_DEBUG) { 
				// \error_log('SomeCaptions API Error - GuzzleException: ' . $e->getMessage());
			}
		}catch(InvalidArgumentException $e){
			self::_error( $e->getMessage() );
			if (defined('WP_DEBUG') && WP_DEBUG) { 
				// \error_log('SomeCaptions API Error - InvalidArgumentException: ' . $e->getMessage());
			}
		}catch(RequestException $e){
			self::_error( $e->getMessage() );
			if (defined('WP_DEBUG') && WP_DEBUG) { 
				// \error_log('SomeCaptions API Error - RequestException: ' . $e->getMessage());
			}
		}catch(ServerException $e){
			self::_error( $e->getMessage() );
			if (defined('WP_DEBUG') && WP_DEBUG) { 
				// \error_log('SomeCaptions API Error - ServerException: ' . $e->getMessage());
			}
		}catch(TooManyRedirectsException $e){
			self::_error( $e->getMessage() );
			if (defined('WP_DEBUG') && WP_DEBUG) { 
				// \error_log('SomeCaptions API Error - TooManyRedirectsException: ' . $e->getMessage());
			}
		}catch(\GuzzleHttp\Exception\TransferException $e){
			self::_error( $e->getMessage() );
			if (defined('WP_DEBUG') && WP_DEBUG) { 
				// \error_log('SomeCaptions API Error - TransferException: ' . $e->getMessage());
			}
		}catch(\Exception $e){
			self::_error( $e->getMessage() );
			if (defined('WP_DEBUG') && WP_DEBUG) { 
				// \error_log('SomeCaptions API Error - Exception: ' . $e->getMessage());
			}
		}
		return $res;
	}

	/**
	 * Display error message of API client
	 * 
	 * @var string $message The error message
	 * 
	 * @since 0.0.1
	 * 
	 * @return void
	 */
	/**
	 * Recursively sanitize an array of values
	 *
	 * @since 3.0.2
	 *
	 * @param array $data The array to sanitize
	 * @return array The sanitized array
	 */
	protected static function sanitize_recursive($data) {
		if (!is_array($data)) {
			return sanitize_text_field($data);
		}
		
		$sanitized = array();
		foreach ($data as $key => $value) {
			$sanitized_key = sanitize_text_field($key);
			
			if (is_array($value)) {
				$sanitized[$sanitized_key] = self::sanitize_recursive($value);
			} else {
				$sanitized[$sanitized_key] = sanitize_text_field($value);
			}
		}
		
		return $sanitized;
	}

	protected static function _error($message) {
		global $pagenow;
		
		// Verify nonce when processing GET data
		if ( isset( $_GET['page'] ) ) {
			// Sanitize the page parameter
			$page = sanitize_text_field( wp_unslash( $_GET['page'] ) );
			
			// Check if we're on the plugin admin page and verify nonce if available
			if ( $page === 'somecaptions-client' && $pagenow === 'admin.php' ) {
				// Check for nonce if this is a form submission
				if ( isset( $_REQUEST['_wpnonce'] ) ) {
					$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
					if ( ! wp_verify_nonce( $nonce, 'somecaptions_api_action' ) ) {
						// If nonce verification fails, still show the error but don't proceed further
						// Debug logging removed for production code
						return;
					}
				}
				
				// Display the error message
				// \wpdesk_wp_notice( esc_html( 'SoMe Captions Client: ' . $message ), 'error', true );
			}
		}

	}
}