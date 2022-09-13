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
            $body = json_decode( (string) $res->getBody() );
            if( $body->success ) {
                $articles  = $body->articles;
                $Parsedown = new Parsedown();
                foreach( $articles as $article ) {
                    $post_arg = array(
                        'post_type'     => $article->type,
                        'post_status'   => 'publish',
                        'post_title'    => $article->title,
                        'post_category'	=> array( $article->domain_category->term_id ),
                        'post_content'  => $Parsedown->text( $article->content ),
                        'post_name'     => \sanitize_title( $article->title ),
                        'post_author'   => \get_option( SW_TEXTDOMAIN . '-user_id' ),
                        'post_date'     => $article->publish_at,
                    );
                    $post_id = \wp_insert_post( $post_arg, true );
                    if( !\is_wp_error($post_id) ) {
                        $image_bin        = base64_decode( $article->web_format_image );
                        $upload_dir       = \wp_upload_dir();
                        $upload_path      = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;
                        $image_name       = \sanitize_title( $article->title ) . '.jpg';
                        $unique_file_name = \wp_unique_filename($upload_dir['path'], $image_name);
                        $filename         = basename($unique_file_name);
                        // HANDLE UPLOADED FILE
                        if( !function_exists( 'wp_handle_sideload' ) ) {
                            require_once( ABSPATH . 'wp-admin/includes/file.php' );
                        }

                        // Check folder permission and define file location
                        if( \wp_mkdir_p( $upload_dir['path'] ) ) {
                            $file = $upload_dir['path'] . '/' . $filename;
                        } else {
                            $file = $upload_dir['basedir'] . '/' . $filename;
                        }

                        file_put_contents( $file, $image_bin );
                        $attachment  = array(
                            'post_mime_type' => 'image/jpeg',
                            'post_title'     => $article->title,
                            'post_content'   => '',
                            'post_status'    => 'inherit'
                        );
                        // Create the attachment
                        $attach_id   = \wp_insert_attachment( $attachment, $file, $post_id );
                        // Include image.php
                        require_once ABSPATH . 'wp-admin/includes/image.php';
                        // Define attachment metadata
                        $attach_data = \wp_generate_attachment_metadata( $attach_id, $file );
                        // Assign metadata to attachment
                        \wp_update_attachment_metadata( $attach_id, $attach_data );
                        \update_post_meta( $attach_id, '_wp_attachment_image_alt', $article->alt );
                        // And finally assign featured image to post
                        $thumbnail   = \set_post_thumbnail( $post_id, $attach_id );
                        // Send update to SoMe Captions API
                        $epoint      = '/api/wpclient/published/' . $article->id;
                        $form_params = array( 'url' => \get_permalink( $post_id ) );
                        ApiClient::request( $epoint, $form_params );
                    }

                }
            }
        }
        
    }
}