<?php
/**
 * Domain Verification Tab
 *
 * @package   SoMeCaptions_WPClient
 * @author    Mindell <mindell.zamora@gmail.com>
 * @copyright N/A
 * @license   GPL 2.0+
 * @link      https://github.com/mindell/
 */

// Get the verification code from the main settings
$verification_code = cmb2_get_option('somecaptions-wpclient' . '-settings', 'verification_code', '');

// Create the domain verification CMB2 box
$domain_cmb = new_cmb2_box(
    array(
        'id'           => 'somecaptions-wpclient' . '_domain_verification',
        'hookup'       => false,
        'show_on'      => array('key' => 'options-page', 'value' => array('somecaptions-wpclient')),
        'show_names'   => true,
        'object_types' => array('options-page'),
        'option_key'   => 'somecaptions-wpclient' . '-settings', // Use the main settings option key
    )
);

// Domain verification section title
$domain_cmb->add_field(
    array(
        'name'    => __('Domain Verification', 'somecaptions-wpclient'),
        'id'      => 'verification_section',
        'type'    => 'title',
        'desc'    => __('Verify your domain ownership to enable full integration with SomeCaptions', 'somecaptions-wpclient'),
    )
);

// Verification code field
$domain_cmb->add_field(
    array(
        'name'    => __('Verification Code', 'somecaptions-wpclient'),
        'id'      => 'verification_code',
        'type'    => 'text',
        'desc'    => __('Enter the verification code from your SomeCaptions dashboard', 'somecaptions-wpclient'),
    )
);

// Get verification status
$domain_verified = get_option('somecaptions-wpclient' . '-domain-verified', false);
$verify_button_html = '<button type="button" id="verify-domain-btn" class="button button-primary">Verify Domain</button>';
$verify_status_html = '<span id="verification-status" style="margin-left: 10px;">';

if ($domain_verified) {
    $verify_status_html .= '<span style="color:green;">✓ Domain verified</span>';
}

$verify_status_html .= '</span>';

// Add verification button and status
$domain_cmb->add_field(
    array(
        'name' => '',
        'desc' => $verify_button_html . $verify_status_html . '
            <script>
                jQuery(document).ready(function($) {
                    $("#verify-domain-btn").on("click", function() {
                        const verification_code = $("#verification_code").val();
                        if (!verification_code) {
                            $("#verification-status").html("<span style=\'color:red\'>Please enter a verification code</span>");
                            return;
                        }
                        
                        $("#verification-status").html("<span style=\'color:blue\'>Verifying...</span>");
                        
                        $.ajax({
                            url: ajaxurl,
                            type: "POST",
                            data: {
                                action: "somecaptions_verify_domain",
                                verification_code: verification_code,
                                nonce: "' . wp_create_nonce('somecaptions_verify_domain') . '"
                            },
                            success: function(response) {
                                if (response.success) {
                                    $("#verification-status").html("<span style=\'color:green\'>✓ " + response.data.message + "</span>");
                                    setTimeout(function() {
                                        window.location.reload();
                                    }, 1500);
                                } else {
                                    $("#verification-status").html("<span style=\'color:red\'>" + response.data.message + "</span>");
                                }
                            },
                            error: function() {
                                $("#verification-status").html("<span style=\'color:red\'>Verification failed. Please try again.</span>");
                            }
                        });
                    });
                });
            </script>',
        'id'   => 'verification_button',
        'type' => 'title',
    )
);

// Add information about domain verification
$domain_cmb->add_field(
    array(
        'name' => __('What is Domain Verification?', 'somecaptions-wpclient'),
        'desc' => __('
            <p>Domain verification confirms that you own this website and allows SomeCaptions to securely connect with your WordPress site.</p>
            <p>After verification, you\'ll be able to:</p>
            <ul style="list-style-type: disc; margin-left: 20px;">
                <li>Sync content between WordPress and SomeCaptions</li>
                <li>Automatically publish generated content</li>
                <li>Access advanced features like category mapping</li>
            </ul>
        ', 'somecaptions-wpclient'),
        'id'   => 'verification_info',
        'type' => 'title',
    )
);

// Render the form
cmb2_metabox_form('somecaptions-wpclient' . '_domain_verification', 'somecaptions-wpclient' . '-settings');
?>
