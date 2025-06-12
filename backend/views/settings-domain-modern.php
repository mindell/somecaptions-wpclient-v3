<?php
/**
 * Modern Domain Verification Tab
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

// Get the verification code from the main settings
$verification_code = cmb2_get_option('somecaptions-client' . '-settings', 'verification_code', '');

// Get verification status
$domain_verified = get_option('somecaptions-client' . '-domain-verified', false);
?>

<div class="somecaptions-card">
    <div class="somecaptions-card-header">
        <h2 class="somecaptions-card-title"><?php esc_html_e('Domain Verification', 'somecaptions-client'); ?></h2>
        <p class="somecaptions-card-description">
            <?php esc_html_e('Verify your domain ownership to enable full integration with SoMe Captions', 'somecaptions-client'); ?>
        </p>
    </div>
    
    <div class="somecaptions-card-content">
        <!-- Verification Status -->
        <?php if ($domain_verified) : ?>
            <div class="somecaptions-status-container">
                <div class="somecaptions-status somecaptions-status-success">
                    <span class="somecaptions-status-icon">✓</span>
                    <?php esc_html_e('Domain Successfully Verified', 'somecaptions-client'); ?>
                </div>
                <p class="somecaptions-status-message">
                    <?php esc_html_e('Your domain is verified and fully connected to SoMe Captions.', 'somecaptions-client'); ?>
                </p>
            </div>
        <?php else : ?>
            <!-- Verification Form -->
            <div class="somecaptions-form-container">
                <?php
                // Create the domain verification CMB2 box
                $domain_cmb = new_cmb2_box(
                    array(
                        'id'           => 'somecaptions-client' . '_domain_verification',
                        'hookup'       => false,
                        'show_on'      => array('key' => 'options-page', 'value' => array('somecaptions-client')),
                        'show_names'   => true,
                        'object_types' => array('options-page'),
                        'option_key'   => 'somecaptions-client' . '-settings', // Use the main settings option key
                        'classes'      => 'somecaptions-cmb2-form',
                    )
                );
                
                // Verification code field
                $domain_cmb->add_field(
                    array(
                        'name'    => __('Verification Code', 'somecaptions-client'),
                        'id'      => 'verification_code',
                        'type'    => 'text',
                        'desc'    => __('Enter the verification code from your SoMe Captions dashboard', 'somecaptions-client'),
                        'attributes' => array(
                            'class' => 'somecaptions-input',
                            'placeholder' => __('Enter verification code', 'somecaptions-client')
                        ),
                    )
                );
                
                // Render the form without submit button
                // Using show_form() instead of render_form() which is the correct method
                $domain_cmb->show_form('somecaptions-client' . '_domain_verification', array(
                    'save_button' => false, // Remove the Save button
                ));
                ?>
                
                <div class="somecaptions-form-actions">
                    <button type="button" id="verify-domain-btn" class="somecaptions-button somecaptions-button-primary">
                        <?php esc_html_e('Verify Domain', 'somecaptions-client'); ?>
                    </button>
                    <div id="verification-status" class="somecaptions-verification-status" style="display: none;"></div>
                </div>
                
                <div class="somecaptions-loading-overlay">
                    <div class="somecaptions-spinner">
                        <div class="somecaptions-spinner-icon"></div>
                        <p class="somecaptions-spinner-text"><?php esc_html_e('Verifying domain...', 'somecaptions-client'); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="somecaptions-card somecaptions-mt-md">
    <div class="somecaptions-card-header">
        <h2 class="somecaptions-card-title"><?php esc_html_e('What is Domain Verification?', 'somecaptions-client'); ?></h2>
    </div>
    <div class="somecaptions-card-content">
        <p><?php esc_html_e('Domain verification confirms that you own this website and allows SoMe Captions to securely connect with your WordPress site.', 'somecaptions-client'); ?></p>
        
        <h3 class="somecaptions-mt-md"><?php esc_html_e('Benefits of Domain Verification', 'somecaptions-client'); ?></h3>
        <div class="somecaptions-feature-grid">
            <div class="somecaptions-feature">
                <span class="dashicons dashicons-admin-site"></span>
                <h4><?php esc_html_e('Secure Connection', 'somecaptions-client'); ?></h4>
                <p><?php esc_html_e('Establish a secure connection between your WordPress site and SoMe Captions', 'somecaptions-client'); ?></p>
            </div>
            <div class="somecaptions-feature">
                <span class="dashicons dashicons-update"></span>
                <h4><?php esc_html_e('Content Sync', 'somecaptions-client'); ?></h4>
                <p><?php esc_html_e('Sync content between WordPress and SoMe Captions automatically', 'somecaptions-client'); ?></p>
            </div>
            <div class="somecaptions-feature">
                <span class="dashicons dashicons-admin-appearance"></span>
                <h4><?php esc_html_e('Auto Publishing', 'somecaptions-client'); ?></h4>
                <p><?php esc_html_e('Automatically publish generated content to your WordPress site', 'somecaptions-client'); ?></p>
            </div>
            <div class="somecaptions-feature">
                <span class="dashicons dashicons-category"></span>
                <h4><?php esc_html_e('Category Mapping', 'somecaptions-client'); ?></h4>
                <p><?php esc_html_e('Access advanced features like category mapping and content organization', 'somecaptions-client'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="somecaptions-card-footer">
        <div class="somecaptions-help-text">
            <p>
                <span class="dashicons dashicons-info-outline"></span>
                <?php echo wp_kses_post( __('Need help? Find your verification code in your <a href="https://app.somecaptions.dk/account" target="_blank">SoMe Captions dashboard</a>.', 'somecaptions-client') ); ?>
            </p>
        </div>
    </div>
</div>
