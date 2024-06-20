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

}