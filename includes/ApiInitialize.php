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

namespace SoMeCaptions_WPClient\Includes;

class ApiInitialize {

    /**
     * Initialize API 
     * 
     * @since 0.0.1
     * 
     */
    public function __construct(){
        $initialized = \get_option( 'somecaptions-wpclient' . '-init' );
        // This is always fired until initialized
        if ( !$initialized ) {
			$opts  = \sw_get_settings();
            if ( $opts ) {
                $epoint      = '/api/wpclient/online';
                $form_params = array();
                $res         = ApiClient::request( $epoint, $form_params );
                // Below will fire only if not initiliazed and API key was already correctly setup
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

                        $authors     = \get_users( array(
                            'role__in' => array( 'author' )
                        ) );
                        $param_authors = array();
                        foreach($authors as $author) {
                            $param_authors[$author->ID] = $author->display_name;
                        }
                        $epoint      = '/api/wpclient/categories';
                        $form_params = array(
                            'categories' => $param_categories,
                            'authors'    => $param_authors
                        );

                        $res         = ApiClient::request( $epoint, $form_params );
                        if( $res ) {
                            $body = \json_decode( (string)  $res->getBody() );
                            if( $body->success ) {
                                $user_created = $this->_create_user( $opts['api_key'] );
                                if( $user_created ) {
                                    \add_option( 'somecaptions-wpclient' . '-init', true );
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
        if( \get_option( 'somecaptions-wpclient' . '-user_id' ) ) {
            return true;
        }
        $sc_user = \get_user_by('login', 'somecaptions');
        if(!$sc_user) {
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
        }
        else {
            $user_id = $sc_user->ID;
        }

        if( is_int($user_id) ){
            \add_option( 'somecaptions-wpclient' . '-user_id', $user_id );
            return true;
        }
        else {
            \wpdesk_wp_notice( 'somecaptions-wpclient' . ': ' . $user_id->get_error_message(), 'error', true );
        }

        return false;
    }
    
}