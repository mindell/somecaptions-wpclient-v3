<?php

/**
 * SoMeCaptions_WPClient
 *
 * @package   SoMeCaptions_WPClient
 * @author    Mindell <mindell.zamora@gmail.com>
 * @copyright N/A
 * @license   GPL 2.0+
 * @link      https://github.com/mindell/
 */

namespace SoMeCaptions_WPClient\Engine;

use Inpsyde\WpContext;

/**
 * SoMe Captions Client Is Methods
 */
class Context {

	/**
	 * WpContext Class
	 *
	 * @var object
	 */
	public $context = null;

	/**
	 * What type of request is this?
	 *
	 * @since 0.0.1
	 * @param  string $type admin, ajax, cron, cli, amp or frontend.
	 * @return bool
	 * @SuppressWarnings("StaticAccess")
	 */
	public function request( string $type ) {
		$this->context = WpContext::determine();

		switch ( $type ) {
			case 'backend':
				return $this->context->isBackoffice();

			case 'ajax':
				return $this->context->isAjax();

			case 'installing_wp':
				return $this->context->isInstalling();

			case 'rest':
				return $this->context->isRest();

			case 'cron':
				return $this->context->isCron();

			case 'frontend':
				return $this->context->isFrontoffice();

			case 'cli':
				return $this->context->isWpCli();

			case 'amp':
				return $this->is_amp();

			default:
				\_doing_it_wrong( __METHOD__, \esc_html( \sprintf( 'Unknown request type: %s', $type ) ), '1.0.0' );

				return false;
		}
	}

	/**
	 * Is AMP
	 *
	 * @return bool
	 */
	public function is_amp() {
		return \function_exists( 'is_amp_endpoint' ) && \is_amp_endpoint();
	}

	/**
	 * Whether given user is an administrator.
	 *
	 * @param \WP_User|null $user The given user.
	 * @return bool
	 */
	public static function is_user_admin( \WP_User $user = null ) { // phpcs:ignore
		if ( \is_null( $user ) ) {
			$user = \wp_get_current_user();
		}

		if ( ! $user instanceof \WP_User ) {
			\_doing_it_wrong( __METHOD__, 'To check if the user is admin is required a WP_User object.', '0.0.1' );
		}

		return \is_multisite() ? \user_can( $user, 'manage_network' ) : \user_can( $user, 'manage_options' ); // phpcs:ignore
	}

}
