<?php

/**
 * @package   SomeCaptions_WPClient
 * @author    Mindell <mindell.zamora@gmail.com>
 * @copyright 2022 GPL
 * @license   GPL 2.0+
 * @link      https://github.com/mindell/
 *
 * Plugin Name:     SoMeCaptions WPClient
 * Plugin URI:      https://github.com/mindell/somecaptions-wpclient
 * Description:     WP plugin for somecaptions.dk
 * Version:         1.6.0
 * Author:          Mindell
 * Author URI:      https://github.com/mindell/
 * Text Domain:     somecaptionswpclient
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

define( 'SW_VERSION', '1.6.0' );
define( 'SW_TEXTDOMAIN', 'somecaptionswpclient' );
define( 'SW_NAME', 'SoMeCaptions WPClient' );
define( 'SW_PLUGIN_ROOT', plugin_dir_path( __FILE__ ) );
define( 'SW_PLUGIN_ABSOLUTE', __FILE__ );
define( 'SW_MIN_PHP_VERSION', '7.4' );
define( 'SW_WP_VERSION', '5.2' );
define( 'SW_CRON_NAME', 'somecaptions_cronjob_publisher');

add_action(
	'init',
	static function () {
		load_plugin_textdomain( SW_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
	);

if ( version_compare( PHP_VERSION, SW_MIN_PHP_VERSION, '<=' ) ) {
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
				__( '"SomeCaptions WPClient" requires PHP ' . SW_MIN_PHP_VERSION . ' or newer.', SW_TEXTDOMAIN )
			)
			);
		}
	);

	// Return early to prevent loading the plugin.
	return;
}

$somecaptionswpclient_libraries = require SW_PLUGIN_ROOT . 'vendor/autoload.php'; //phpcs:ignore

function sw_get_settings() {
	$opts =  apply_filters( 'sw_get_settings', get_option( SW_TEXTDOMAIN . '-settings' ) );
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
	'SomeCaptions WPClient',
	array(
		'php'            => SW_MIN_PHP_VERSION,
		'php_extensions' => array( 'mbstring' ),
		'wp'             => SW_WP_VERSION,
	)
);

if ( ! $requirements->satisfied() ) {
	$requirements->print_notice();

	return;
}

// Documentation to integrate GitHub, GitLab or BitBucket https://github.com/YahnisElsts/plugin-update-checker/blob/master/README.md
$updateChecker = Puc_v4_Factory::buildUpdateChecker( 'https://github.com/mindell/somecaptions-wpclient', __FILE__, 'somecaptionswpclient' );
$updateChecker->getVcsApi()->enableReleaseAssets();

if ( ! wp_installing() ) {
	register_activation_hook( dirname( plugin_basename( __FILE__ ) ) . '/' . SW_TEXTDOMAIN . '.php', array( new \SomeCaptions_WPClient\Includes\ActDeact, 'activate' ) );
	register_deactivation_hook( dirname( plugin_basename( __FILE__ ) ) . '/' . SW_TEXTDOMAIN . '.php', array( new \SomeCaptions_WPClient\Includes\ActDeact, 'deactivate' ) );
	add_action(
		'plugins_loaded',
		static function () use ( $somecaptionswpclient_libraries ) {
			new \SomeCaptions_WPClient\Engine\Initialize( $somecaptionswpclient_libraries );
			if( is_admin() ) {
				require_once SW_PLUGIN_ROOT . 'vendor/cmb2/cmb2/init.php';
				new \SomeCaptions_WPClient\Includes\Settings();
				new \SomeCaptions_WPClient\Includes\ApiInitialize();
				new \SomeCaptions_WPClient\Includes\Actions();
			}
			if(!defined('DISABLE_WP_CRON')){
				define('DISABLE_WP_CRON',true);
			}
			new \SomeCaptions_WPClient\Includes\Cron();
		}
	);
}
