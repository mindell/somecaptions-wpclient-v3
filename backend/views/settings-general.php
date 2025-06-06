<?php
/**
 * General Settings Tab
 *
 * @package   SoMeCaptions_WPClient
 * @author    Mindell <mindell.zamora@gmail.com>
 * @copyright N/A
 * @license   GPL 2.0+
 * @link      https://github.com/mindell/
 */

// Add custom CSS for form feedback
?>
<style>
.settings-saved-notification {
    background-color: #d4edda;
    color: #155724;
    padding: 10px 15px;
    margin: 15px 0;
    border-radius: 4px;
    border-left: 4px solid #28a745;
    animation: fadeOut 5s forwards;
    display: none;
}

@keyframes fadeOut {
    0% { opacity: 1; }
    70% { opacity: 1; }
    100% { opacity: 0; }
}

.settings-form-overlay {
    position: relative;
}

.settings-form-overlay::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 100;
    visibility: hidden;
}

.settings-form-overlay.loading::after {
    visibility: visible;
}

.spinner-container {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 101;
    display: none;
}

.settings-form-overlay.loading .spinner-container {
    display: block;
}
</style>

<div class="settings-form-overlay" id="settings-form-container">
    <div id="api-settings-status"></div>
    <div id="settings-saved-message" style="display:none; background-color:#dff0d8; color:#3c763d; padding:10px; margin-bottom:15px; border-radius:4px; border:1px solid #d6e9c6;">
        <p><strong>Success!</strong> Your settings have been saved and the API connection has been verified.</p>
    </div>
    <div class="spinner-container">
        <span class="spinner is-active" style="float:none;"></span>
        <p>Validating API connection...</p>
    </div>
<?php

// Create the general settings CMB2 box
$cmb = new_cmb2_box(
    array(
        'id'           => 'somecaptions-wpclient' . '_options',
        'hookup'       => false,
        'show_on'      => array('key' => 'options-page', 'value' => array('somecaptions-wpclient')),
        'show_names'   => true,
        'object_types' => array('options-page'),
        'option_key'   => 'somecaptions-wpclient' . '_options',
    )
);

// API Endpoint field
$cmb->add_field(
    array(
        'name'    => __('API endpoint', 'somecaptions-wpclient'),
        'id'      => 'endpoint',
        'type'    => 'text',
        'default' => 'https://api.somecaptions.dk/',
    )
);

// API Key field
$cmb->add_field(
    array(
        'name'    => __('API key', 'somecaptions-wpclient'),
        'id'      => 'api_key',
        'type'    => 'text',
        'default' => '',
    )
);

// Render the form
cmb2_metabox_form('somecaptions-wpclient' . '_options', 'somecaptions-wpclient' . '-settings');
?>
</div>

<script>
jQuery(document).ready(function($) {
    // Intercept the form submission
    $('#<?php echo esc_attr('somecaptions-wpclient'); ?>-settings').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading message
        $('#settings-form-container').addClass('loading');
        $('#api-settings-status').html('<p><span style="color:blue;">Saving settings and validating API connection...</span></p>');
        
        // Get form data and add action parameter
        var formData = $(this).serialize();
        formData += '&action=cmb2_save_options-page_fields&object_id=' + '<?php echo esc_attr('somecaptions-wpclient'); ?>_options';
        
        // First save the form via AJAX
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(saveResponse) {
                console.log('Settings saved successfully', saveResponse);
                
                // After saving, validate the API connection
                var endpoint = $('#endpoint').val();
                var api_key = $('#api_key').val();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'somecaptions_validate_api',
                        endpoint: endpoint,
                        api_key: api_key,
                        nonce: '<?php echo esc_js(wp_create_nonce('somecaptions_validate_api')); ?>'
                    },
                    success: function(response) {
                        $('#settings-form-container').removeClass('loading');
                        
                        if (response.success) {
                            // Show success message
                            $('#api-settings-status').html('<p><span style="color:green;">✓ ' + response.data.message + '</span></p>');
                            
                            // Show the settings saved notification
                            $('#settings-saved-message').show();
                            setTimeout(function() {
                                $('#settings-saved-message').fadeOut(1000);
                            }, 5000);
                            
                            // If domain tab should be shown, update the UI
                            if (response.data.show_domain_tab) {
                                // Add the domain tab if it doesn't exist
                                if ($('.nav-tab-wrapper a[href="#domain-verification"]').length === 0) {
                                    $('.nav-tab-wrapper').append('<a href="#domain-verification" class="nav-tab">Domain Verification</a>');
                                    
                                    // Add the domain verification tab content if it doesn't exist
                                    if ($('#domain-verification').length === 0) {
                                        $('.tab-content').append('<div id="domain-verification" class="tab-pane"></div>');
                                    }
                                    
                                    // Load the domain verification content via AJAX
                                    $('#domain-verification').load(ajaxurl, {
                                        action: 'somecaptions_load_domain_tab'
                                    }, function() {
                                        // Initialize any scripts that might be needed for the domain tab
                                        // This ensures that any JavaScript in the loaded tab works properly
                                        if (typeof(jQuery) !== 'undefined') {
                                            jQuery(document).trigger('somecaptions_domain_tab_loaded');
                                        }
                                    });
                                }
                            }
                        } else {
                            $('#api-settings-status').html('<p><span style="color:red;">✗ ' + response.data.message + '</span></p>');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#settings-form-container').removeClass('loading');
                        console.error('API validation error:', error);
                        $('#api-settings-status').html('<p><span style="color:red;">✗ An error occurred while validating the API connection: ' + error + '</span></p>');
                    }
                });
            },
            error: function(xhr, status, error) {
                $('#settings-form-container').removeClass('loading');
                console.error('Settings save error:', error);
                $('#api-settings-status').html('<p><span style="color:red;">✗ An error occurred while saving settings: ' + error + '</span></p>');
            }
        });
    });
});
</script>
