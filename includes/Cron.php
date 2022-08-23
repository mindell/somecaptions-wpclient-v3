<?php

/**
 * SomeCaptions Cron
 *
 * @package   Cron
 * @author    Mindell <mindell.zamora@gmail.com>
 * @license   GPL-2.0+
 * @link      https://github.com/mindell/
 * @copyright 2022 GPL
 */
 
namespace SomeCaptions_WPClient\Includes;

use CronPlus;
use Parsedown;

class Cron{

    /**
     * Add actions for syncing category
     * 
     * @since 0.0.1
     * 
     */
    public function __construct() {
        $args   = array(
            // hourly, daily, twicedaily, weekly, monthly or timestamp for single event
            'recurrence'       => 'hourly',
            // schedule (specific interval) or single (at the time specified)
            'schedule'         => 'schedule',
            // Name of the Cron job used internally
            'name'             => 'somecaptions_cronjob_publisher',
            // Callback to execute when the cron job is launched
            'cb'               => array( & $this, 'publish' ),
            // Multisite support disabled by default
            'multisite'        => true,
            // Used on deactivation for register_deactivation_hook to cleanup
            'plugin_root_file' => 'somecaptionswpclient.php',
            // When the event is scheduled is also executed
            'run_on_creation'  => true,
            // Args passed to the hook executed during the cron
            'args'             => array()
        );
        $cronplus = new CronPlus( $args );
        // Schedule the event
        $cronplus->schedule_event();
    }

    /**
     * Publish articles if there is any
     * 
     * @since 0.0.1
     * 
     * @return void
     */
    public function publish() {
        $ep          = '/api/wpclient/publish';
        $form_params = array();
        $res         = ApiClient::request( $ep, $form_params );
        if( $res ) {
            $body = \json_decode( (string) $res->getBody() );
            if( $body->success ) {
                $articles  = $body->articles;
                $Parsedown = new Parsedown();
                foreach( $articles as $article ) {
                    $post_arg = array(
                        'post_type'     => 'post',
                        'post_status'   => 'publish',
                        'post_title'    => $article->title,
                        'post_category'	=> array( $article->domain_category->term_id ),
                        'post_content'  => $Parsedown->text( $article->content ),
                        'post_name'     => \sanitize_title( $article->title ),
                    );
    
                    $post_id = \wp_insert_post( $post_arg );
                    if( $post_id ) {
                        $epoint          = '/api/wpclient/published/' . $article->id;
                        $form_params = array();
                        ApiClient::request( $epoint, $form_params );
                    }

                }
            }
        }
        
    }
}