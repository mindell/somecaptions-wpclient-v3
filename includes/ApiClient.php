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

namespace SomeCaptions_WPClient\Includes;

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
	 * Generic private function for making a POST request
	 *
	 * @param int $category_id ID of the new category.
     * 
	 * @since 0.0.1
     * 
	 * @return any
	 */
	public static function request( $ep, $form_params) {
		$client = self::client();
		$opts   = \sw_get_settings();
		$auth   = 'Bearer '.$opts['api_key'];
		$res    = null;
		try{
			$res = $client->request( 'POST', $ep, array(
				'form_params' => $form_params,
				'headers'     => array(
					'Accept'         => 'application/json',
					'Authorization'  => $auth,
					'Origin'         => site_url(),
				),
			) );
		}catch(BadResponseException $e){
			self::_error( $e->getMessage() );
		}catch(ClientException $e){
			self::_error( $e->getMessage() );
		}catch(ConnectException $e){
			self::_error( $e->getMessage() );
		}catch(GuzzleException $e){
			self::_error( $e->getMessage() );
		}catch(InvalidArgumentException $e){
			self::_error( $e->getMessage() );
		}catch(RequestException $e){
			self::_error( $e->getMessage() );
		}catch(ServerException $e){
			self::_error( $e->getMessage() );
		}catch(TooManyRedirectsException $e){
			self::_error( $e->getMessage() );
		}catch(TransferException $e){
			self::_error( $e->getMessage() );
		}catch(Exception $e){
			self::_error( $e->getMessage() );
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
	protected static function _error($message) {
		global $pagenow;
		if ( isset( $_GET['page'] ) ) {
			if ( $_GET['page'] == SW_TEXTDOMAIN && $pagenow == 'admin.php' ) {
				\wpdesk_wp_notice( SW_TEXTDOMAIN . ': ' .$message, 'error', true );
			}

		}

	}
}