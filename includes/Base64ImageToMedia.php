<?php

/**
 * SomeCaptions Base64 image to media file
 *
 * @package   Cron
 * @author    Mindell <mindell.zamora@gmail.com>
 * @license   GPL-2.0+
 * @link      https://github.com/mindell/
 * @copyright 2022 GPL
 */
 
namespace SomeCaptions_WPClient\Includes;

class Base64ImageToMedia {
    /**
     * The HTML
     * 
     * @since 2.0.4
     * 
     * @var   string
     */
    public $html;
    
    /**
     * image sources
     * 
     * @since 2.0.4
     * 
     * @var array
     */
    public $sources = [];

    /**
     * The post id
     * 
     * @since 2.0.4
     * 
     * @var int|\WP_Error
     */
    private $_post_id;

    /**
     * Scan contents for base64 images 
     * Replace base64 images src to media file when xml extension was available
     * 
     * @param string $html
     * @param int|\WP_Error $post_id
     * 
     * @since 2.0.4
     */
    public function __construct( $html, $post_id ) {
        $this->_post_id = $post_id;
        if( class_exists("\\DOMDocument") ) {
            $doc = new \DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML( '<?xml encoding="utf-8" ?>' . $html);
            $xpath = new \DOMXPath($doc);
            $nodelist = $xpath->query("//img");
            foreach( $nodelist as $idx => $node ) {
                $src = $node->attributes->getNamedItem('src')->nodeValue;
                $alt = $node->attributes->getNamedItem('alt')->nodeValue;
                if( !$alt ) {
                    $alt = 'somecaptions-'.uniqid();
                }

                $new_src = $this->_convert_src( $src, $alt );
                if( $new_src ) {
                    $node->setAttribute( 'src', $new_src );
                    $this->sources[ md5($src) ] = $new_src;
                }
                
            }
            if( !empty($this->sources) )
                $html = $doc->saveHTML();
        }

        $this->html = $html;
    }

    /**
     * Convert base64 to media file src
     * 
     * @param string $src
     * @param string $alt 
     * @return string|null
     * 
     * @since 2.0.4
     */
    private function _convert_src( $src, $alt ) {
        if( strpos( $src, ';base64,' ) !== false ) {
            $extension = 'jpg';
            $splited   = explode( ',', substr( $src , 5 ) , 2 );
            $mime      = $splited[0];
            $data      = $splited[1];
            $mime_split_w_out_b64 = explode( ';', $mime, 2 );
            $mime_split           = explode( '/', $mime_split_w_out_b64[0] ,2 );
            if( count( $mime_split ) == 2 ) { 
                $extension = $mime_split[1];
                if( $extension == 'jpeg' ) 
                    $extension = 'jpg';
            }

            $image_bin        = base64_decode( $data );
            $upload_dir       = \wp_upload_dir();
            $upload_path      = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;
            $image_name       = \sanitize_title( $alt ) . '.' . $extension;
            $unique_file_name = \wp_unique_filename( $upload_dir['path'], $image_name );
            $filename         = basename( $unique_file_name );
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
                'post_mime_type' => 'image/' . $extension,
                'post_title'     => $alt,
                'post_content'   => '',
                'post_status'    => 'inherit'
            );
            // Create the attachment
            $attach_id   = \wp_insert_attachment( $attachment, $file, $this->_post_id );

            if ( is_wp_error($attach_id) ) { 
                return null; 
            }
             
            // Include image.php
            require_once ABSPATH . 'wp-admin/includes/image.php';
            // Define attachment metadata
            $attach_data = \wp_generate_attachment_metadata( $attach_id, $file );
            // Assign metadata to attachment
            \wp_update_attachment_metadata( $attach_id, $attach_data );
            \update_post_meta( $attach_id, '_wp_attachment_image_alt', $alt );
            return wp_get_attachment_image_url( $attach_id, '' );
        }
        return null;
    }

}
