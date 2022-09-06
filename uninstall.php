<?php

/**
 * SomeCaptions_WPClient
 *
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * @package   SomeCaptions_WPClient
 * @author    Mindell <mindell.zamora@gmail.com>
 * @copyright N/A
 * @license   GPL 2.0+
 * @link      https://github.com/mindell/
 */

// If uninstall not called from WordPress, then exit.
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Loop for uninstall
 *
 * @return void
 */
function sw_uninstall_multisite() {
	if ( is_multisite() ) {
		/** @var array<\WP_Site> $blogs */
		$blogs = get_sites();

		if ( !empty( $blogs ) ) {
			foreach ( $blogs as $blog ) {
				switch_to_blog( (int) $blog->blog_id );
				sw_uninstall();
				restore_current_blog();
			}

			return;
		}
	}

	sw_uninstall();
}

/**
 * What happen on uninstall?
 *
 * @global WP_Roles $wp_roles
 * @return void
 */
function sw_uninstall() { // phpcs:ignore
    
}

sw_uninstall_multisite();
