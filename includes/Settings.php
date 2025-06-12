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
 
namespace SoMeCaptions_WPClient\Includes;

class Settings{

    /**
     * Initialize settings page
     * 
     * @since 0.0.1
     * 
     */
    public function __construct() {
        // Add the options page and menu item.
		\add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		$realpath        = (string) \realpath( \dirname( __FILE__ ) );
		$plugin_basename = \plugin_basename( \plugin_dir_path( $realpath ) . 'somecaptions-client' . '.php' );
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
		 * Using a more appropriate dashicon for captions/media
		 */
		\add_menu_page( \__( 'SoMe Captions Client Settings', 'somecaptions-client' ), SW_NAME, 'manage_options', 'somecaptions-client', array( $this, 'display_plugin_admin_page' ), 'dashicons-format-gallery', 90 );
	}

    /**
	 * Render the settings page for this plugin.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function display_plugin_admin_page() {
		// Use the new modern admin template
		include_once SW_PLUGIN_ROOT . 'backend/views/admin-template.php';
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
				'settings' => '<a href="' . \admin_url( 'options-general.php?page=' . 'somecaptions-client' ) . '">' . \esc_html__( 'Settings', 'somecaptions-client' ) . '</a>',
			),
			$links
		);
	}


}