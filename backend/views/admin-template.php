<?php
/**
 * Main admin template for SoMe Captions Client
 *
 * This template provides the modern UI structure for the admin interface
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

// Get settings and status
$api_key = cmb2_get_option('somecaptions-client' . '-settings', 'api_key', '');
$domain_verified = get_option('somecaptions-client' . '-domain-verified', false);
$initialized = get_option('somecaptions-client' . '-init');
$is_connected = get_option('somecaptions-client-connected', false);

// Define logo URL
$logo_url = plugin_dir_url(SW_PLUGIN_ABSOLUTE) . 'assets/images/somecaptions-logo.png';
// Fallback to WordPress dashicon if logo doesn't exist
$logo_exists = file_exists(plugin_dir_path(SW_PLUGIN_ABSOLUTE) . 'assets/images/somecaptions-logo.png');
?>

<div class="somecaptions-admin-wrap">
    <!-- Admin Header with Logo -->
    <div class="somecaptions-admin-header">
        <?php if ($logo_exists) : ?>
            <?php
            // Use WordPress image handling functions instead of direct output
            $image_id = attachment_url_to_postid($logo_url);
            if ($image_id) {
                // If the image is in the media library, use wp_get_attachment_image
                echo wp_get_attachment_image($image_id, 'medium', false, array(
                    'class' => 'somecaptions-logo',
                    'alt' => 'SoMe Captions Client'
                ));
            } else {
                // Fallback for images not in the media library using WordPress functions
                $img_attr = array(
                    'class' => 'somecaptions-logo',
                    'alt'   => esc_attr__('SoMe Captions Client', 'somecaptions-client'),
                );
                
                // Use WordPress HTML API to generate the image tag
                echo wp_kses(
                    // Create an HTML img tag with proper attributes
                    '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr__('SoMe Captions Client', 'somecaptions-client') . '" class="somecaptions-logo" />',
                    // Define allowed HTML elements and attributes
                    array(
                        'img' => array(
                            'src' => array(),
                            'alt' => array(),
                            'class' => array(),
                        ),
                    )
                );
            }
            ?>
        <?php else : ?>
            <div class="somecaptions-logo-placeholder">
                <span class="dashicons dashicons-format-gallery"></span>
                <span><?php esc_html_e('SoMe Captions', 'somecaptions-client'); ?></span>
            </div>
        <?php endif; ?>
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    </div>

    <!-- Notifications Container -->
    <div class="somecaptions-notifications"></div>

    <!-- Connection Status Card -->
    <?php if (!empty($api_key)) : ?>
        <div class="somecaptions-card somecaptions-status-card somecaptions-mb-md">
            <div class="somecaptions-card-content somecaptions-flex-between">
                <div>
                    <h3 class="somecaptions-status-title"><?php esc_html_e('Connection Status', 'somecaptions-client'); ?></h3>
                    <div class="somecaptions-status-details">
                        <?php if ($domain_verified) : ?>
                            <span class="somecaptions-status somecaptions-status-success">
                                <span class="somecaptions-status-icon">✓</span>
                                <?php esc_html_e('Connected & Verified', 'somecaptions-client'); ?>
                            </span>
                        <?php else : ?>
                            <span class="somecaptions-status somecaptions-status-warning">
                                <span class="somecaptions-status-icon">!</span>
                                <?php esc_html_e('Connected, Verification Required', 'somecaptions-client'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!$domain_verified && !empty($api_key)) : ?>
                    <div class="somecaptions-status-actions">
                        <a href="#domain-verification" class="somecaptions-button somecaptions-button-primary somecaptions-nav-tab-trigger" data-tab="domain-verification">
                            <?php esc_html_e('Verify Domain', 'somecaptions-client'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Tab Navigation -->
    <div class="somecaptions-nav-tabs">
        <a href="#general-settings" class="somecaptions-nav-tab somecaptions-nav-tab-active" data-tab="general-settings">
            <span class="dashicons dashicons-admin-generic"></span>
            <?php esc_html_e('General Settings', 'somecaptions-client'); ?>
        </a>
        <a href="#domain-verification" class="somecaptions-nav-tab" data-tab="domain-verification">
            <span class="dashicons dashicons-shield"></span>
            <?php esc_html_e('Domain Verification', 'somecaptions-client'); ?>
        </a>
        <a href="#help-support" class="somecaptions-nav-tab" data-tab="help-support">
            <span class="dashicons dashicons-editor-help"></span>
            <?php esc_html_e('Help & Support', 'somecaptions-client'); ?>
        </a>
    </div>

    <!-- Tab Content -->
    <div class="somecaptions-admin-content">
        <!-- General Settings Tab -->
        <div id="general-settings" class="somecaptions-tab-panel">
            <?php require_once plugin_dir_path(__FILE__) . 'settings-general-modern.php'; ?>
        </div>

        <!-- Domain Verification Tab -->
        <div id="domain-verification" class="somecaptions-tab-panel somecaptions-hidden">
            <?php if ($is_connected) : ?>
                <?php require_once plugin_dir_path(__FILE__) . 'settings-domain-modern.php'; ?>
            <?php else : ?>
                <div class="somecaptions-card">
                    <div class="somecaptions-card-header">
                        <h2><?php esc_html_e('Domain Verification', 'somecaptions-client'); ?></h2>
                    </div>
                    <div class="somecaptions-card-body">
                        <div class="somecaptions-notice somecaptions-notice-warning">
                            <p><?php esc_html_e('Please connect your API key in the General Settings tab before verifying your domain.', 'somecaptions-client'); ?></p>
                        </div>
                        
                        <div class="somecaptions-form-section">
                            <h3><?php esc_html_e('Domain Verification Code', 'somecaptions-client'); ?></h3>
                            <p><?php esc_html_e('The verification code will be available after connecting your API key.', 'somecaptions-client'); ?></p>
                            <div class="somecaptions-form-field">
                                <input type="text" disabled placeholder="<?php esc_attr_e('Connect API key first', 'somecaptions-client'); ?>" class="somecaptions-input">
                            </div>
                        </div>
                        
                        <div class="somecaptions-form-actions">
                            <button class="somecaptions-button somecaptions-button-primary" disabled>
                                <?php esc_html_e('Verify Domain', 'somecaptions-client'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Help & Support Tab -->
        <div id="help-support" class="somecaptions-tab-panel somecaptions-hidden">
            <?php require_once plugin_dir_path(__FILE__) . 'settings-help-modern.php'; ?>
        </div>
    </div>

    <!-- Admin Footer -->
    <div class="somecaptions-admin-footer">
        <div class="somecaptions-footer-content">
            <p>
                <?php printf(
                    /* translators: %s: SoMe Captions Client version */
                    esc_html__('SoMe Captions Client v%s', 'somecaptions-client'),
                    esc_html(SW_VERSION)
                ); ?>
            </p>
        </div>
    </div>
</div>
