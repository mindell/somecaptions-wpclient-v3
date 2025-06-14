<?php

/**
 * @package   SoMeCaptions_WPClient
 * @author    Mindell <mindell.zamora@gmail.com>
 * @copyright 2022 GPL
 * @license   GPL 2.0+
 * @link      https://github.com/mindell/
 *
 * Plugin Name:     SoMe Captions Client
 * Plugin URI:      https://github.com/mindell/somecaptions-client
 * Description:     Plugin for WordPress to integrate with SoMe Captions platform
 * Version:         3.0.1
 * Author:          Mindell
 * Author URI:      https://github.com/mindell/
 * Text Domain:     somecaptions-client
 * License:         GPL 2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path:     /languages
 * Requires PHP:    7.4
 * WordPress-Plugin-Boilerplate-Powered: v3.3.0
 */

// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

define( 'SW_VERSION', '3.0.1' );
define( 'SW_TEXTDOMAIN', 'somecaptions-client' );
define( 'SW_NAME', 'SoMe Captions Client' );
define( 'SW_PLUGIN_ROOT', plugin_dir_path( __FILE__ ) );
define( 'SW_PLUGIN_ABSOLUTE', __FILE__ );
define( 'SW_MIN_PHP_VERSION', '7.4' );
define( 'SW_WP_VERSION', '5.2' );
define( 'SW_CRON_NAME', 'somecaptions_cronjob_publisher');
define( 'SW_SIGNIN_HOST', 'https://app1.somecaptions.dk/gsc_signin' );

add_action(
	'init',
	static function () {
		load_plugin_textdomain( 'somecaptions-client', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
	);

if ( version_compare( PHP_VERSION, SW_MIN_PHP_VERSION, '<' ) ) {
	add_action(
		'admin_init',
		static function() {
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}
	);
	add_action(
		'admin_notices',
		static function() {
			echo wp_kses_post(
			sprintf(
				'<div class="notice notice-error"><p>%s</p></div>',
				/* translators: %1$s: Plugin name, %2$s: Required PHP version */
				sprintf( __( '%1$s requires PHP %2$s or newer.', 'somecaptions-client' ), 'SoMe Captions Client', SW_MIN_PHP_VERSION )
			)
			);
		}
	);

	// Return early to prevent loading the plugin.
	return;
}

// Check WordPress version
global $wp_version;
if ( version_compare( $wp_version, SW_WP_VERSION, '<' ) ) {
	add_action(
		'admin_init',
		static function() {
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}
	);
	add_action(
		'admin_notices',
		static function() {
			echo wp_kses_post(
				sprintf(
					'<div class="notice notice-error"><p>%s</p></div>',
					/* translators: %1$s: Plugin name, %2$s: Required WordPress version */
					sprintf( __( '%1$s requires WordPress %2$s or newer.', 'somecaptions-client' ), 'SoMe Captions Client', SW_WP_VERSION )
				)
			);
		}
	);

	// Return early to prevent loading the plugin.
	return;
}

$somecaptionswpclient_libraries = require SW_PLUGIN_ROOT . 'vendor/autoload.php'; //phpcs:ignore

// Include cache clearing functionality
require_once plugin_dir_path( __FILE__ ) . 'clear-cache.php';

// Include the connection checker
require_once plugin_dir_path( __FILE__ ) . 'includes/ConnectionChecker.php';

function sw_get_settings() {
	$opts =  apply_filters( 'sw_get_settings', get_option( 'somecaptions-client' . '-settings' ) );
	//  set a default value
	if( !$opts ) {
		$opts = array(
			'api_key'  => '',
			'endpoint' => 'https://api.somecaptions.dk',
		);
	}

	return $opts;
}


// Add your new plugin on the wiki: https://github.com/WPBP/WordPress-Plugin-Boilerplate-Powered/wiki/Plugin-made-with-this-Boilerplate

$requirements = new \Micropackage\Requirements\Requirements(
	'SoMe Captions Client',
	array(
		'php'            => SW_MIN_PHP_VERSION,
		'php_extensions' => array( 'mbstring', 'xml' ),
		'wp'             => SW_WP_VERSION,
	)
);

if ( ! $requirements->satisfied() ) {
	$requirements->print_notice();

	return;
}

// Documentation to integrate GitHub, GitLab or BitBucket https://github.com/YahnisElsts/plugin-update-checker/blob/master/README.md
$updateChecker = Puc_v4_Factory::buildUpdateChecker( 'https://github.com/mindell/somecaptions-client', __FILE__, 'somecaptions-client' );
$updateChecker->getVcsApi()->enableReleaseAssets();

if ( ! wp_installing() ) {
	register_activation_hook( dirname( plugin_basename( __FILE__ ) ) . '/' . 'somecaptions-client' . '.php', array( new \SoMeCaptions_WPClient\Includes\ActDeact, 'activate' ) );
	register_deactivation_hook( dirname( plugin_basename( __FILE__ ) ) . '/' . 'somecaptions-client' . '.php', array( new \SoMeCaptions_WPClient\Includes\ActDeact, 'deactivate' ) );
	add_action(
		'plugins_loaded',
		static function () use ( $somecaptionswpclient_libraries ) {
			new \SoMeCaptions_WPClient\Engine\Initialize( $somecaptionswpclient_libraries );
			if( is_admin() ) {
				require_once SW_PLUGIN_ROOT . 'vendor/cmb2/cmb2/init.php';
				new \SoMeCaptions_WPClient\Includes\Settings();
				new \SoMeCaptions_WPClient\Includes\ApiInitialize();
				new \SoMeCaptions_WPClient\Includes\Actions();
				// Initialize domain verification functionality
				new \SoMeCaptions_WPClient\Includes\DomainVerification();
				// Initialize admin UI enhancements
				new \SoMeCaptions_WPClient\Includes\Admin();
			}
			//if(!defined('DISABLE_WP_CRON')){
			//	define('DISABLE_WP_CRON',true);
			//}
			new \SoMeCaptions_WPClient\Includes\Cron();
		}
	);
}
