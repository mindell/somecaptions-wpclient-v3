<?php

/**
 * SomeCaptions Settings
 *
 * @package   Settings
 * @author    Mindell <mindell.zamora@gmail.com>
 * @license   GPL-2.0+
 * @link      https://github.com/mindell/
 * @copyright 2022 GPL
 */
 
namespace SomeCaptions_WPClient\Includes;

class Settings{

    /**
     * Initialize settings page
     * 
     * @since 0.0.1
     * 
     */
    public function __construct() {
		
		//add scripts
		\add_action( 'wp_enqueue_scripts', array( $this, 'add_signin_script' ));
		\wp_enqueue_scripts();
        // Add the options page and menu item.
		\add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		$realpath        = (string) \realpath( \dirname( __FILE__ ) );
		$plugin_basename = \plugin_basename( \plugin_dir_path( $realpath ) . SW_TEXTDOMAIN . '.php' );
		\add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
    }

    /**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function add_plugin_admin_menu() {
		/*
		 * Add a settings page for this plugin to the main menu
		 *
		 */
		\add_menu_page( \__( 'SoMe Captions WPClient Settings', SW_TEXTDOMAIN ), SW_NAME, 'manage_options', SW_TEXTDOMAIN, array( $this, 'display_plugin_admin_page' ), 'dashicons-hammer', 90 );
	}

    /**
	 * Render the settings page for this plugin.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function display_plugin_admin_page() {
		include_once SW_PLUGIN_ROOT . 'backend/views/admin.php';
	}

    /**
	 * Add settings action link to the plugins page.
	 *
	 * @since 0.0.1
	 * @param array $links Array of links.
	 * @return array
	 */
	public function add_action_links( array $links ) {
		return \array_merge(
			array(
				'settings' => '<a href="' . \admin_url( 'options-general.php?page=' . SW_TEXTDOMAIN ) . '">' . \__( 'Settings', SW_TEXTDOMAIN ) . '</a>',
			),
			$links
		);
	}

	/**
	 * Add a custom script for GSC login
	 * 
	 * @since 1.7.0
	 * @return void
	 */
	public function add_signin_script() {
		\wp_enqueue_script( 'gsc_signin_script', 
							plugin_dir_url( __DIR__ . '/../../' ) . 'backend/js/gsc_signin.js',
							[],
							'1.6.8',// SW_VERSION,
							true // @footer
						  );
	}
}