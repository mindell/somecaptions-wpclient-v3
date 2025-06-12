<?php
/**
 * Cache Clearing Script for SoMe Captions Client
 * 
 * This script helps clear WordPress transients and other caches related to the plugin.
 * To use: Add this to your plugin directory and access via wp-admin by going to:
 * Tools > SoMe Captions Cache Clear
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the cache clearing page
 */
function somecaptions_register_cache_clear_page() {
    add_management_page(
        'SoMe Captions Cache Clear',
        'SoMe Captions Cache',
        'manage_options',
        'somecaptions-cache-clear',
        'somecaptions_cache_clear_page'
    );
}
add_action('admin_menu', 'somecaptions_register_cache_clear_page');

/**
 * Display and handle the cache clearing page
 */
function somecaptions_cache_clear_page() {
    $cache_cleared = false;
    
    // Handle form submission
    if (isset($_POST['somecaptions_clear_cache']) && check_admin_referer('somecaptions_clear_cache_nonce')) {
        // Clear plugin-specific transients using WordPress functions instead of direct DB query
        $all_transients = get_option('_transient_timeout_somecaptions%', array());
        
        // Get all transients with our plugin prefix
        $somecaptions_transients = array();
        foreach (array_keys($all_transients) as $transient) {
            if (strpos($transient, 'somecaptions') !== false) {
                $somecaptions_transients[] = $transient;
            }
        }
        
        // Delete each transient properly
        foreach ($somecaptions_transients as $transient_name) {
            delete_transient($transient_name);
        }
        
        // Delete cached script versions by updating the version timestamp
        update_option('somecaptions_script_version_timestamp', time());
        
        // Clear WordPress object cache if available
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        $cache_cleared = true;
    }
    
    // Output the admin page
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <?php if ($cache_cleared) : ?>
            <div class="notice notice-success">
                <p><?php esc_html_e('Cache cleared successfully!', 'somecaptions-client'); ?></p>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <h2><?php esc_html_e('Clear SoMe Captions Cache', 'somecaptions-client'); ?></h2>
            <p><?php esc_html_e('Use this tool to clear any cached data related to the SoMe Captions plugin. This can help resolve issues with the admin interface, especially after updates.', 'somecaptions-client'); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field('somecaptions_clear_cache_nonce'); ?>
                <p>
                    <button type="submit" name="somecaptions_clear_cache" class="button button-primary">
                        <?php esc_html_e('Clear Cache', 'somecaptions-client'); ?>
                    </button>
                </p>
            </form>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h2><?php esc_html_e('Browser Cache', 'somecaptions-client'); ?></h2>
            <p><?php esc_html_e('If issues persist after clearing the WordPress cache, you may need to clear your browser cache as well.', 'somecaptions-client'); ?></p>
            
            <p><strong><?php esc_html_e('To clear your browser cache:', 'somecaptions-client'); ?></strong></p>
            <ul style="list-style-type: disc; margin-left: 20px;">
                <li><?php esc_html_e('Chrome: Press Ctrl+Shift+Delete (or Cmd+Shift+Delete on Mac)', 'somecaptions-client'); ?></li>
                <li><?php esc_html_e('Firefox: Press Ctrl+Shift+Delete (or Cmd+Shift+Delete on Mac)', 'somecaptions-client'); ?></li>
                <li><?php esc_html_e('Safari: Press Option+Command+E', 'somecaptions-client'); ?></li>
                <li><?php esc_html_e('Edge: Press Ctrl+Shift+Delete', 'somecaptions-client'); ?></li>
            </ul>
            
            <p><?php esc_html_e('Or try accessing the admin page in an incognito/private browsing window.', 'somecaptions-client'); ?></p>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h2><?php esc_html_e('Immediate Fix', 'somecaptions-client'); ?></h2>
            <p><?php
                /* translators: %s: Current timestamp used for cache busting */
                echo esc_html(sprintf( __('For an immediate fix without clearing cache, you can try adding "?nocache=%s" to the end of the URL.', 'somecaptions-client'), esc_attr(time()) ));
            ?></p>
            <p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=somecaptions-client&nocache=' . time())); ?>" class="button">
                    <?php esc_html_e('Open SoMe Captions Admin (No Cache)', 'somecaptions-client'); ?>
                </a>
            </p>
        </div>
    </div>
    <?php
}

/**
 * Add a direct link to the cache clearing page from the plugins page
 */
/*
function somecaptions_add_cache_clear_link($links, $file) {
    if (plugin_basename(SW_PLUGIN_ABSOLUTE) === $file) {
        $cache_link = '<a href="' . esc_url(admin_url('tools.php?page=somecaptions-cache-clear')) . '">' . esc_html__('Clear Cache', 'somecaptions-client') . '</a>';
        array_unshift($links, $cache_link);
    }
    return $links;
}
add_filter('plugin_action_links', 'somecaptions_add_cache_clear_link', 10, 2);
*/
