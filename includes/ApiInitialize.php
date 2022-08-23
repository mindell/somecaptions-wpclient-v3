<?php

/**
 * SomeCaptions API Initialize
 *
 * @package   ApiInitialize
 * @author    Mindell <mindell.zamora@gmail.com>
 * @license   GPL-2.0+
 * @link      https://github.com/mindell/
 * @copyright 2022 GPL
 */

namespace SomeCaptions_WPClient\Includes;

class ApiInitialize {

    /**
     * Initialize API 
     * 
     * @since 0.0.1
     * 
     */
    public function __construct(){
        $initialized = \get_option( SW_TEXTDOMAIN . '-init' );
        if ( !$initialized ) {
			$opts  = \sw_get_settings();
            if ( $opts ) {
                $epoint      = '/api/wpclient/online';
                $form_params = array();
                $res         = ApiClient::request( $epoint, $form_params );
                if( $res ) {
                    $body = \json_decode( (string)  $res->getBody() );
                    if( $body->success ) {
                        $categories      = \get_categories( array(
                            'orderby'    => 'name',
                            'order'      => 'ASC',
                            'hide_empty' => 0,
                        ) );
                        $param_categories = array();
                        foreach($categories as $category) {
                            if($category->taxonomy == 'category') {
                                $param_categories[$category->term_id] = $category->name;
                            }

                        }
                        $epoint      = '/api/wpclient/categories';
                        $form_params = array(
                            'categories' => $param_categories,
                        );
                        $res         = ApiClient::request( $epoint, $form_params );
                        if( $res ) {
                            $body = \json_decode( (string)  $res->getBody() );
                            if($body->success){
                                \add_option( SW_TEXTDOMAIN . '-init', true );
                            }
                        }

                    }   
                }
            }

        }

    }
}