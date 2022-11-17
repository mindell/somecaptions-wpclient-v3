<?php

/**
 * SomeCaptions Activate Deactivate
 *
 * @package   ActDeact
 * @author    Mindell <mindell.zamora@gmail.com>
 * @license   GPL-2.0+
 * @link      https://github.com/mindell/
 * @copyright 2022 GPL
 */

namespace SomeCaptions_WPClient\Includes;

class ActDeact{
    /**
	 * Initialize the class.
	 *
	 * @return void|bool
	 */
	public function initialize() {

		// Activate plugin when new blog is added
		\add_action( 'wp_initialize_site', array( $this, 'activate_new_site' ) );

		\add_action( 'admin_init', array( $this, 'upgrade_procedure' ) );
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @param int $blog_id ID of the new blog.
	 * @since 0.0.1
	 * @return void
	 */
	public function activate_new_site( int $blog_id ) {
		if ( 1 !== \did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		\switch_to_blog( $blog_id );
		self::single_activate();
		\restore_current_blog();
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param bool $network_wide True if active in a multiste, false if classic site.
	 * @since 0.0.1
	 * @return void
	 */
	public static function activate( bool $network_wide ) {
		if ( \function_exists( 'is_multisite' ) && \is_multisite() ) {
			if ( $network_wide ) {
				// Get all blog ids
				/** @var array<\WP_Site> $blogs */
				$blogs = \get_sites();

				foreach ( $blogs as $blog ) {
					\switch_to_blog( (int) $blog->blog_id );
					self::single_activate();
					\restore_current_blog();
				}

				return;
			}
		}

		self::single_activate();
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param bool $network_wide True if WPMU superadmin uses
	 * "Network Deactivate" action, false if
	 * WPMU is disabled or plugin is
	 * deactivated on an individual blog.
	 * @since 0.0.1
	 * @return void
	 */
	public static function deactivate( bool $network_wide ) {
		if ( \function_exists( 'is_multisite' ) && \is_multisite() ) {
			if ( $network_wide ) {
				// Get all blog ids
				/** @var array<\WP_Site> $blogs */
				$blogs = \get_sites();

				foreach ( $blogs as $blog ) {
					\switch_to_blog( (int) $blog->blog_id );
					self::single_deactivate();
					\restore_current_blog();
				}

				return;
			}
		}

		self::single_deactivate();
	}

	/**
	 * Upgrade procedure
	 *
	 * @return void
	 */
	public static function upgrade_procedure() {
		if ( !\is_admin() ) {
			return;
		}

		$version = \strval( \get_option( 'somecaptionswpclient-version' ) );

		if ( !\version_compare( SW_VERSION, $version, '>' ) ) {
			return;
		}

		\update_option( 'somecaptionswpclient-version', SW_VERSION );
		\delete_option( SW_TEXTDOMAIN . '_fake-meta' );
	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	private static function single_activate() {
		self::upgrade_procedure();
		// Clear the permalinks
		\flush_rewrite_rules();
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	private static function single_deactivate() {

		// Deactivate domain
		$form_params = array();
		$ep          = '/api/wpclient/offline';
		ApiClient::request( $ep, $form_params );
		\delete_option( SW_TEXTDOMAIN . '-init' );
        \delete_option( SW_TEXTDOMAIN . '-settings' );
		$gsc_connected = \get_option( SW_TEXTDOMAIN . '-gsc-connected' );
		if( $gsc_connected ) {
			\delete_option( SW_TEXTDOMAIN . '-gsc-connected' );
		}
		
		// Clear the permalinks
		\flush_rewrite_rules();
	}
}