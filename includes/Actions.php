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

class Actions{

    /**
     * Add actions for syncing category
     * 
     * @since 0.0.1
     */
    public function __construct(){
		$initialized = \get_option( SW_TEXTDOMAIN . '-init' );
		if($initialized){
			\add_action( 'create_category', array( & $this, 'somecaptions_new_category' ), 10, 1 );
			\add_action( 'delete_category', array( & $this, 'somecaptions_remove_category' ), 10, 1 );
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
		if($cat->taxonomy == 'category'){
			$form_params = array(
				'name'	         => $cat->name,
				'term_id'        => $category_id,
			);
			$epoint      = '/api/wpclient/new_category';
			ApiClient::request( $epoint, $form_params );
		}

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