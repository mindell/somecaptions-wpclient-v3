<?php
/**
 * Modern General Settings Tab
 *
 * @package   SoMeCaptions_WPClient
 * @author    Mindell <mindell.zamora@gmail.com>
 * @copyright 2022 GPL
 * @license   GPL 2.0+
 * @link      https://github.com/mindell/
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$settings = sw_get_settings();
$api_key = isset($settings['api_key']) ? $settings['api_key'] : '';
$endpoint = isset($settings['endpoint']) ? $settings['endpoint'] : 'https://api.somecaptions.dk/';
?>

<div class="somecaptions-card">
    <div class="somecaptions-card-header">
        <h2 class="somecaptions-card-title"><?php esc_html_e('API Configuration', 'somecaptions-client'); ?></h2>
        <p class="somecaptions-card-description">
            <?php esc_html_e('Connect your WordPress site to SoMe Captions by entering your API credentials below.', 'somecaptions-client'); ?>
        </p>
    </div>
    
    <div class="somecaptions-card-content">
        <!-- API Settings Status Messages -->
        <div id="api-settings-status" class="somecaptions-notification-container" style="display: none;"></div>
        
        <!-- Settings Form -->
        <div class="somecaptions-form-container" id="settings-form-container">
            <?php
            // Create the general settings CMB2 box
            $cmb = new_cmb2_box(
                array(
                    'id'           => 'somecaptions-client' . '_options',
                    'hookup'       => false,
                    'show_on'      => array('key' => 'options-page', 'value' => array('somecaptions-client')),
                    'show_names'   => true,
                    'object_types' => array('options-page'),
                    'option_key'   => 'somecaptions-client' . '_options',
                    'classes'      => 'somecaptions-cmb2-form',
                )
            );
            
            // API Endpoint field
            $cmb->add_field(
                array(
                    'name'    => __('API endpoint', 'somecaptions-client'),
                    'id'      => 'endpoint',
                    'type'    => 'text',
                    'default' => 'https://api.somecaptions.dk/',
                    'desc'    => __('The URL of the SoMe Captions API service', 'somecaptions-client'),
                    'attributes' => array(
                        'class' => 'somecaptions-input',
                        'placeholder' => 'https://api.somecaptions.dk/'
                    ),
                )
            );
            
            // API Key field
            $cmb->add_field(
                array(
                    'name'    => __('API key', 'somecaptions-client'),
                    'id'      => 'api_key',
                    'type'    => 'text',
                    'default' => '',
                    'desc'    => __('Your unique API key from the SoMe Captions dashboard', 'somecaptions-client'),
                    'attributes' => array(
                        'class' => 'somecaptions-input',
                        'placeholder' => __('Enter your API key', 'somecaptions-client')
                    ),
                )
            );
            
            // Add CSS to hide the default CMB2 submit button
            echo '<style>
                #somecaptions-client_options .button-primary[name="submit-cmb"] {
                    display: none !important;
                }
                .somecaptions-form-actions {
                    margin-top: 20px;
                    display: flex;
                    justify-content: flex-start;
                }
                .somecaptions-form-wrapper {
                    position: relative;
                }
            </style>';
            
            // Debug output to check form structure
            echo '<!-- DEBUG: Form structure will be modified with ID somecaptions-client-settings-form -->';
            
            // Add a custom ID to the form for JavaScript targeting
            add_filter('cmb2_form_attributes', function($attrs) {
                $attrs['id'] = 'somecaptions-client-settings-form';
                $attrs['class'] .= ' somecaptions-ajax-form';
                // Debug output of attributes
                echo '<!-- DEBUG: Form attributes: ' . print_r($attrs, true) . ' -->';
                return $attrs;
            }, 10, 1);
            
            // Render the form without the default submit button
            echo '<div class="somecaptions-form-wrapper">';
            cmb2_metabox_form('somecaptions-client' . '_options', 'somecaptions-client' . '-settings', array(
                'save_button' => ' ', // Empty space to make the default button invisible
            ));
            
            // Add our custom submit button outside the form
            echo '<div class="somecaptions-form-actions">
                <button type="button" id="somecaptions-save-settings" class="somecaptions-button somecaptions-button-primary">
                    ' . esc_html__('Save Settings', 'somecaptions-client') . '
                </button>
            </div>';
            echo '</div>';
            ?>
            
            <div class="somecaptions-loading-overlay" style="display: none;">
                <div class="somecaptions-spinner">
                    <div class="somecaptions-spinner-icon"></div>
                    <p class="somecaptions-spinner-text"><?php esc_html_e('Validating API connection...', 'somecaptions-client'); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="somecaptions-card-footer">
        <div class="somecaptions-help-text">
            <p>
                <span class="dashicons dashicons-info-outline"></span>
                <?php echo wp_kses_post( __('Need help? Find your API key in your <a href="https://app.somecaptions.dk/account" target="_blank">SoMe Captions dashboard</a>.', 'somecaptions-client') ); ?>
            </p>
        </div>
    </div>
</div>

<?php if (empty($api_key)) : ?>
<div class="somecaptions-card somecaptions-mt-md">
    <div class="somecaptions-card-header">
        <h2 class="somecaptions-card-title"><?php esc_html_e('Getting Started', 'somecaptions-client'); ?></h2>
    </div>
    <div class="somecaptions-card-content">
        <div class="somecaptions-onboarding">
            <div class="somecaptions-onboarding-step">
                <div class="somecaptions-onboarding-step-number">1</div>
                <div class="somecaptions-onboarding-step-content">
                    <h3><?php esc_html_e('Create an Account', 'somecaptions-client'); ?></h3>
                    <p><?php echo wp_kses_post( __('Sign up for a   account at <a href="https://app.somecaptions.dk/signup" target="_blank">app.somecaptions.dk</a>', 'somecaptions-client') ); ?></p>
                </div>
            </div>
            <div class="somecaptions-onboarding-step">
                <div class="somecaptions-onboarding-step-number">2</div>
                <div class="somecaptions-onboarding-step-content">
                    <h3><?php esc_html_e('Get Your API Key', 'somecaptions-client'); ?></h3>
                    <p><?php esc_html_e('Generate an API key from your SoMe Captions dashboard under Account Settings', 'somecaptions-client'); ?></p>
                </div>
            </div>
            <div class="somecaptions-onboarding-step">
                <div class="somecaptions-onboarding-step-number">3</div>
                <div class="somecaptions-onboarding-step-content">
                    <h3><?php esc_html_e('Connect Your Site', 'somecaptions-client'); ?></h3>
                    <p><?php esc_html_e('Enter your API key above and click Save Settings', 'somecaptions-client'); ?></p>
                </div>
            </div>
            <div class="somecaptions-onboarding-step">
                <div class="somecaptions-onboarding-step-number">4</div>
                <div class="somecaptions-onboarding-step-content">
                    <h3><?php esc_html_e('Verify Your Domain', 'somecaptions-client'); ?></h3>
                    <p><?php esc_html_e('After connecting, verify your domain to unlock all features', 'somecaptions-client'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
