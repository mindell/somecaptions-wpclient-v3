<?php
/**
 * General Settings Tab
 *
 * @package   SomeCaptions_WPClient
 * @author    Mindell <mindell.zamora@gmail.com>
 * @copyright N/A
 * @license   GPL 2.0+
 * @link      https://github.com/mindell/
 */

// Create the general settings CMB2 box
$cmb = new_cmb2_box(
    array(
        'id'           => SW_TEXTDOMAIN . '_options',
        'hookup'       => false,
        'show_on'      => array('key' => 'options-page', 'value' => array(SW_TEXTDOMAIN)),
        'show_names'   => true,
        'object_types' => array('options-page'),
        'option_key'   => SW_TEXTDOMAIN . '_options',
    )
);

// API Endpoint field
$cmb->add_field(
    array(
        'name'    => __('API endpoint', SW_TEXTDOMAIN),
        'id'      => 'endpoint',
        'type'    => 'text',
        'default' => 'https://api.somecaptions.dk/',
    )
);

// API Key field
$cmb->add_field(
    array(
        'name'    => __('API key', SW_TEXTDOMAIN),
        'id'      => 'api_key',
        'type'    => 'text',
        'default' => '',
    )
);

// Render the form
cmb2_metabox_form(SW_TEXTDOMAIN . '_options', SW_TEXTDOMAIN . '-settings');
?>
