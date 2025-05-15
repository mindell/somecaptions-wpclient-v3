<?php
/**
 * Google Search Console Settings Tab
 *
 * @package   SomeCaptions_WPClient
 * @author    Mindell <mindell.zamora@gmail.com>
 * @copyright N/A
 * @license   GPL 2.0+
 * @link      https://github.com/mindell/
 */

// Get site URL information
$parsed_url = parse_url(site_url());
$gsc_connected = get_option(SW_TEXTDOMAIN . '-gsc-connected');

// Create the GSC settings CMB2 box
$gsc_cmb = new_cmb2_box(
    array(
        'id'           => SW_TEXTDOMAIN . '_gsc_options',
        'hookup'       => false,
        'show_on'      => array('key' => 'options-page', 'value' => array(SW_TEXTDOMAIN)),
        'show_names'   => true,
        'object_types' => array('options-page'),
        'option_key'   => SW_TEXTDOMAIN . '-settings', // Use the main settings option key
    )
);

// GSC section title
$gsc_cmb->add_field(
    array(
        'name'    => __('Google Search Console Integration', SW_TEXTDOMAIN),
        'id'      => 'gsc_section',
        'type'    => 'title',
        'desc'    => __('Connect your site with Google Search Console to improve SEO performance', SW_TEXTDOMAIN),
    )
);

// Connection status
$status_class = $gsc_connected ? 'connected' : 'not-connected';
$status_icon = $gsc_connected ? '✓' : '✗';
$status_text = $gsc_connected ? 'Connected' : 'Not Connected';

$gsc_cmb->add_field(
    array(
        'name' => __('Connection Status', SW_TEXTDOMAIN),
        'desc' => sprintf(
            '<div class="gsc-status %s"><span class="status-icon">%s</span> <span class="status-text">%s</span></div>',
            $status_class,
            $status_icon,
            $status_text
        ),
        'id'   => 'gsc_status',
        'type' => 'title',
    )
);

// Connect button
$gsc_cmb->add_field(
    array(
        'name' => '',
        'desc' => sprintf(
            '<button class="button-primary" onClick="open_app_gw(\'%s/%s\')">%s</button>',
            SW_SIGNIN_HOST,
            $parsed_url['host'],
            $gsc_connected ? 'Reconnect Google Search Console' : 'Connect Google Search Console'
        ),
        'id'   => 'gsc_connect_button',
        'type' => 'title',
    )
);

// Add information about GSC integration
$gsc_cmb->add_field(
    array(
        'name' => __('Why Connect Google Search Console?', SW_TEXTDOMAIN),
        'desc' => __('
            <p>Connecting Google Search Console enables SomeCaptions to:</p>
            <ul style="list-style-type: disc; margin-left: 20px;">
                <li>Analyze your site\'s search performance</li>
                <li>Identify high-value keywords for content generation</li>
                <li>Optimize generated content for better search rankings</li>
                <li>Track improvements in your site\'s visibility</li>
            </ul>
            <p>Your Google Search Console data remains private and is only used to improve your content.</p>
        ', SW_TEXTDOMAIN),
        'id'   => 'gsc_info',
        'type' => 'title',
    )
);

// Add some styling for the GSC status
echo '<style>
    .gsc-status {
        padding: 10px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        font-weight: 500;
    }
    .gsc-status.connected {
        background-color: #d4edda;
        color: #155724;
    }
    .gsc-status.not-connected {
        background-color: #f8d7da;
        color: #721c24;
    }
    .status-icon {
        margin-right: 8px;
        font-size: 16px;
    }
</style>';

// Render the form
cmb2_metabox_form(SW_TEXTDOMAIN . '_gsc_options', SW_TEXTDOMAIN . '-settings');
?>
