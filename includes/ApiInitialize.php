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
                        foreach( $categories as $category ) {
                            if( $category->taxonomy == 'category' ) {
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
                            if( $body->success ) {
                                $user_created = $this->_create_user( $opts['api_key'] );
                                if( $user_created ) {
                                    \add_option( SW_TEXTDOMAIN . '-init', true );
                                }

                            }

                        }

                    }   

                }
            }

        }

    }
    
    /**
     * Create SEO af SoMe Captions user.
     * 
     * @param string $key
     * 
     * @return bool
     * 
     * @since 1.0.0
     */
    private function _create_user( $key ) {
        if( \get_option( SW_TEXTDOMAIN . '-user_id' ) ) {
            return true;
        }

        $passw = \wp_generate_password( 15 );
        $userdata = array(
            'user_pass'     => $passw,
            'user_login'    => 'somecaptions',
            'user_nicename' => 'seo-af-somecaptions',
            'user_url'      => 'https://seo.somecaptions.dk',
            'user_email'    => 'seo@somecaptions.dk',
            'display_name'  => 'SEO af SoMe Captions',
            'role'          => 'author',
        );

        $user_id = \wp_insert_user( $userdata );
        if( is_int($user_id) ){
            \add_option( SW_TEXTDOMAIN . '-user_id', $user_id );
            return true;
        }
        else {
            \wpdesk_wp_notice( SW_TEXTDOMAIN . ': ' . $user_id->get_error_message(), 'error', true );
        }

        return false;
    }
    
}