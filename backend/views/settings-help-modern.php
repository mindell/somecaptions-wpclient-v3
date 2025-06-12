<?php
/**
 * SoMe Captions Client - Help & Support Tab
 *
 * This file contains the Help & Support tab content for the SoMe Captions Client admin interface.
 * It provides documentation, support resources, and troubleshooting information.
 *
 * @package SoMeCaptions_WPClient
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="somecaptions-card-container">
    <!-- Getting Started Card -->
    <div class="somecaptions-card">
        <div class="somecaptions-card-header">
            <h3><span class="dashicons dashicons-welcome-learn-more"></span> <?php esc_html_e('Getting Started', 'somecaptions-client'); ?></h3>
        </div>
        <div class="somecaptions-card-content">
            <p><?php esc_html_e('New to SoMe Captions? Follow these steps to get started:', 'somecaptions-client'); ?></p>
            
            <ol class="somecaptions-steps">
                <li>
                    <strong><?php esc_html_e('Create an Account', 'somecaptions-client'); ?></strong>
                    <p><?php echo wp_kses_post( __('Sign up for a SoMe Captions account at <a href="https://somecaptions.dk/signup" target="_blank">somecaptions.dk</a>', 'somecaptions-client') ); ?></p>
                </li>
                <li>
                    <strong><?php esc_html_e('Get Your API Key', 'somecaptions-client'); ?></strong>
                    <p><?php esc_html_e('Generate an API key from your SoMe Captions dashboard', 'somecaptions-client'); ?></p>
                </li>
                <li>
                    <strong><?php esc_html_e('Configure the Plugin', 'somecaptions-client'); ?></strong>
                    <p><?php esc_html_e('Enter your API key in the General Settings tab', 'somecaptions-client'); ?></p>
                </li>
                <li>
                    <strong><?php esc_html_e('Verify Your Domain', 'somecaptions-client'); ?></strong>
                    <p><?php esc_html_e('Complete domain verification for enhanced security and features', 'somecaptions-client'); ?></p>
                </li>
            </ol>
        </div>
    </div>

    <!-- Documentation Card -->
    <div class="somecaptions-card">
        <div class="somecaptions-card-header">
            <h3><span class="dashicons dashicons-book"></span> <?php esc_html_e('Documentation', 'somecaptions-client'); ?></h3>
        </div>
        <div class="somecaptions-card-content">
            <p><?php esc_html_e('Access comprehensive documentation to learn how to use all features of SoMe Captions:', 'somecaptions-client'); ?></p>
            
            <div class="somecaptions-resource-links">
                <a href="https://docs.somecaptions.dk/wordpress-plugin" target="_blank" class="somecaptions-resource-link">
                    <span class="dashicons dashicons-media-document"></span>
                    <span><?php esc_html_e('Plugin Documentation', 'somecaptions-client'); ?></span>
                </a>
                <a href="https://docs.somecaptions.dk/api" target="_blank" class="somecaptions-resource-link">
                    <span class="dashicons dashicons-rest-api"></span>
                    <span><?php esc_html_e('API Reference', 'somecaptions-client'); ?></span>
                </a>
                <a href="https://docs.somecaptions.dk/tutorials" target="_blank" class="somecaptions-resource-link">
                    <span class="dashicons dashicons-video-alt3"></span>
                    <span><?php esc_html_e('Video Tutorials', 'somecaptions-client'); ?></span>
                </a>
                <a href="https://docs.somecaptions.dk/faq" target="_blank" class="somecaptions-resource-link">
                    <span class="dashicons dashicons-editor-help"></span>
                    <span><?php esc_html_e('Frequently Asked Questions', 'somecaptions-client'); ?></span>
                </a>
            </div>
        </div>
    </div>

    <!-- Troubleshooting Card -->
    <div class="somecaptions-card">
        <div class="somecaptions-card-header">
            <h3><span class="dashicons dashicons-admin-tools"></span> <?php esc_html_e('Troubleshooting', 'somecaptions-client'); ?></h3>
        </div>
        <div class="somecaptions-card-content">
            <div class="somecaptions-collapsible">
                <div class="somecaptions-collapsible-header" aria-expanded="false">
                    <h4><?php esc_html_e('API Connection Issues', 'somecaptions-client'); ?></h4>
                    <span class="somecaptions-collapsible-icon dashicons dashicons-arrow-down-alt2"></span>
                </div>
                <div class="somecaptions-collapsible-content" aria-hidden="true">
                    <ul class="somecaptions-checklist">
                        <li><?php esc_html_e('Verify your API key is entered correctly', 'somecaptions-client'); ?></li>
                        <li><?php esc_html_e('Check that your domain is properly verified', 'somecaptions-client'); ?></li>
                        <li><?php esc_html_e('Ensure your server can make outbound HTTP requests', 'somecaptions-client'); ?></li>
                        <li><?php esc_html_e('Confirm your SoMe Captions subscription is active', 'somecaptions-client'); ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="somecaptions-collapsible">
                <div class="somecaptions-collapsible-header" aria-expanded="false">
                    <h4><?php esc_html_e('Plugin Conflicts', 'somecaptions-client'); ?></h4>
                    <span class="somecaptions-collapsible-icon dashicons dashicons-arrow-down-alt2"></span>
                </div>
                <div class="somecaptions-collapsible-content" aria-hidden="true">
                    <p><?php esc_html_e('If you experience conflicts with other plugins:', 'somecaptions-client'); ?></p>
                    <ol>
                        <li><?php esc_html_e('Temporarily deactivate other plugins to identify conflicts', 'somecaptions-client'); ?></li>
                        <li><?php esc_html_e('Check for JavaScript errors in your browser console', 'somecaptions-client'); ?></li>
                        <li><?php esc_html_e('Ensure your WordPress and PHP versions meet requirements', 'somecaptions-client'); ?></li>
                    </ol>
                </div>
            </div>
            
            <div class="somecaptions-collapsible">
                <div class="somecaptions-collapsible-header" aria-expanded="false">
                    <h4><?php esc_html_e('Performance Optimization', 'somecaptions-client'); ?></h4>
                    <span class="somecaptions-collapsible-icon dashicons dashicons-arrow-down-alt2"></span>
                </div>
                <div class="somecaptions-collapsible-content" aria-hidden="true">
                    <p><?php esc_html_e('For optimal performance:', 'somecaptions-client'); ?></p>
                    <ul>
                        <li><?php esc_html_e('Use a caching plugin for your WordPress site', 'somecaptions-client'); ?></li>
                        <li><?php esc_html_e('Optimize your images before uploading them', 'somecaptions-client'); ?></li>
                        <li><?php esc_html_e('Consider upgrading to a higher API tier for better rate limits', 'somecaptions-client'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Support Card -->
    <div class="somecaptions-card">
        <div class="somecaptions-card-header">
            <h3><span class="dashicons dashicons-businesswoman"></span> <?php esc_html_e('Contact Support', 'somecaptions-client'); ?></h3>
        </div>
        <div class="somecaptions-card-content">
            <p><?php esc_html_e('Need help? Our support team is ready to assist you:', 'somecaptions-client'); ?></p>
            
            <div class="somecaptions-support-options">
                <div class="somecaptions-support-option">
                    <span class="dashicons dashicons-email-alt"></span>
                    <h4><?php esc_html_e('Email Support', 'somecaptions-client'); ?></h4>
                    <p><?php echo wp_kses_post( __('Send us an email at <a href="mailto:support@somecaptions.dk">support@somecaptions.dk</a>', 'somecaptions-client') ); ?></p>
                </div>
                
                <div class="somecaptions-support-option">
                    <span class="dashicons dashicons-format-chat"></span>
                    <h4><?php esc_html_e('Live Chat', 'somecaptions-client'); ?></h4>
                    <p><?php esc_html_e('Chat with our support team on our website during business hours', 'somecaptions-client'); ?></p>
                    <a href="https://somecaptions.dk/support" target="_blank" class="somecaptions-button somecaptions-button-secondary">
                        <?php esc_html_e('Start Chat', 'somecaptions-client'); ?>
                    </a>
                </div>
                
                <div class="somecaptions-support-option">
                    <span class="dashicons dashicons-groups"></span>
                    <h4><?php esc_html_e('Community Forum', 'somecaptions-client'); ?></h4>
                    <p><?php esc_html_e('Join our community forum to connect with other users', 'somecaptions-client'); ?></p>
                    <a href="https://community.somecaptions.dk" target="_blank" class="somecaptions-button somecaptions-button-secondary">
                        <?php esc_html_e('Visit Forum', 'somecaptions-client'); ?>
                    </a>
                </div>
            </div>
            
            <div class="somecaptions-system-info">
                <h4><?php esc_html_e('System Information', 'somecaptions-client'); ?></h4>
                <p><?php esc_html_e('When contacting support, please include the following information:', 'somecaptions-client'); ?></p>
                
                <div class="somecaptions-system-info-table">
                    <table>
                        <tr>
                            <th><?php esc_html_e('WordPress Version', 'somecaptions-client'); ?></th>
                            <td><?php echo esc_html(get_bloginfo('version')); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('PHP Version', 'somecaptions-client'); ?></th>
                            <td><?php echo esc_html(phpversion()); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Plugin Version', 'somecaptions-client'); ?></th>
                            <td><?php echo esc_html(defined('SW_PLUGIN_VERSION') ? SW_PLUGIN_VERSION : '3.0.0'); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Active Theme', 'somecaptions-client'); ?></th>
                            <td><?php echo esc_html(wp_get_theme()->get('Name')); ?></td>
                        </tr>
                    </table>
                    
                    <button id="copy-system-info" class="somecaptions-button somecaptions-button-secondary">
                        <span class="dashicons dashicons-clipboard"></span> <?php esc_html_e('Copy System Info', 'somecaptions-client'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Copy system info to clipboard
    document.addEventListener('DOMContentLoaded', function() {
        const copyButton = document.getElementById('copy-system-info');
        if (copyButton) {
            copyButton.addEventListener('click', function() {
                const rows = document.querySelectorAll('.somecaptions-system-info-table table tr');
                let systemInfo = '';
                
                rows.forEach(row => {
                    const label = row.querySelector('th').textContent;
                    const value = row.querySelector('td').textContent;
                    systemInfo += `${label}: ${value}\n`;
                });
                
                // Add browser info
                systemInfo += `Browser: ${navigator.userAgent}\n`;
                
                // Copy to clipboard
                navigator.clipboard.writeText(systemInfo).then(function() {
                    // Show success message
                    if (window.somecaptionsNotify) {
                        window.somecaptionsNotify.success('<?php echo esc_js( __('System information copied to clipboard', 'somecaptions-client') ); ?>');
                    } else {
                        alert('<?php echo esc_js( __('System information copied to clipboard', 'somecaptions-client') ); ?>');
                    }
                }).catch(function() {
                    // Show error message
                    if (window.somecaptionsNotify) {
                        window.somecaptionsNotify.error('<?php echo esc_js( __('Failed to copy system information', 'somecaptions-client') ); ?>');
                    } else {
                        alert('<?php echo esc_js( __('Failed to copy system information', 'somecaptions-client') ); ?>');
                    }
                });
            });
        }
    });
</script>
