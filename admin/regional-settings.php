<?php
/**
 * Airlinel Regional Site Settings Admin Page
 *
 * This page displays regional site configuration and allows:
 * - Viewing current site ID and language
 * - Viewing main site connectivity status
 * - Testing connection to main site API
 * - Selecting language for the regional site
 */

// Security: Check if user has admin access
if (!function_exists('airlinel_regional_settings_page')) {
    function airlinel_regional_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.', 'airlinel-theme'));
        }

        // Handle test connection AJAX (form submission)
        if (isset($_POST['test_connection']) && wp_verify_nonce($_POST['_wpnonce'], 'airlinel_regional_nonce')) {
            // Attempt to test connection
            $test_result = airlinel_regional_test_connection();
            $test_message = $test_result['message'];
            $test_status = $test_result['status'];
        } else {
            $test_message = null;
            $test_status = null;
        }

        // Handle language change
        if (isset($_POST['save_language']) && wp_verify_nonce($_POST['_wpnonce'], 'airlinel_regional_nonce')) {
            $new_language = sanitize_text_field($_POST['airlinel_language'] ?? '');

            if (!empty($new_language) && in_array($new_language, array(
                'en_US', 'tr_TR', 'de_DE', 'ru_RU', 'fr_FR', 'it_IT', 'ar',
                'da_DK', 'nl_NL', 'sv_SE', 'zh_CN', 'ja'
            ), true)) {
                update_option('WPLANG', $new_language);
                $test_message = __('Language updated successfully. Please refresh the page.', 'airlinel-theme');
                $test_status = 'success';
            } else {
                $test_message = __('Invalid language selected.', 'airlinel-theme');
                $test_status = 'error';
            }
        }

        // Get configuration - now support both constants and database-driven settings
        // Try database-driven settings first (new Regional_Settings_Manager), then fall back to constants
        $regional_settings_mgr = new Airlinel_Regional_Settings_Manager();
        $db_settings = $regional_settings_mgr->get_all_settings();

        // Use database settings if available, otherwise fall back to constants
        $main_site_url = !empty($db_settings['main_site_url']) ?
            $db_settings['main_site_url'] :
            (defined('AIRLINEL_MAIN_SITE_URL') ? AIRLINEL_MAIN_SITE_URL : 'Not configured');

        $site_id = !empty($db_settings['site_id']) ?
            $db_settings['site_id'] :
            (defined('AIRLINEL_SITE_ID') ? AIRLINEL_SITE_ID : get_option('airlinel_source_site_id', 'unknown'));

        $api_key_set = !empty($db_settings['api_key']) ?
            true :
            (defined('AIRLINEL_MAIN_SITE_API_KEY') && !empty(AIRLINEL_MAIN_SITE_API_KEY));
        $wplang = defined('WPLANG') ? WPLANG : get_option('WPLANG', 'en_US');

        // Get language name
        $language_names = airlinel_get_language_names();
        $current_language_name = isset($language_names[$wplang]) ? $language_names[$wplang] : $wplang;

        // Check if Regional_Site_Proxy can be initialized
        $proxy_status = airlinel_check_regional_proxy_status();

        // Get last API sync time
        $last_sync_time = get_option('airlinel_regional_last_sync', 'Never');
        if ($last_sync_time !== 'Never') {
            $last_sync_time = date_i18n('Y-m-d H:i:s', intval($last_sync_time));
        }

        // Determine overall status
        $is_connected = $proxy_status['initialized'] && $api_key_set;

        ?>
        <div class="wrap airlinel-regional-settings">
            <h1><?php _e('Regional Site Settings', 'airlinel-theme'); ?></h1>

            <?php if ($test_message): ?>
                <div class="notice notice-<?php echo esc_attr($test_status); ?> is-dismissible">
                    <p><?php echo wp_kses_post($test_message); ?></p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text"><?php _e('Dismiss this notice', 'airlinel-theme'); ?></span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="postbox">
                <h2 class="hndle">
                    <span>
                        <?php _e('Configuration Status', 'airlinel-theme'); ?>
                        <span class="status-indicator <?php echo $is_connected ? 'connected' : 'disconnected'; ?>"
                              style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-left: 10px;
                                     background-color: <?php echo $is_connected ? '#28a745' : '#dc3545'; ?>;">
                        </span>
                    </span>
                </h2>
                <div class="inside">
                    <table class="widefat striped">
                        <tbody>
                            <tr>
                                <th><?php _e('Site ID', 'airlinel-theme'); ?></th>
                                <td><code><?php echo esc_html($site_id); ?></code></td>
                            </tr>
                            <tr>
                                <th><?php _e('Current Language', 'airlinel-theme'); ?></th>
                                <td>
                                    <strong><?php echo esc_html($current_language_name); ?></strong>
                                    <small style="display: block; color: #666;">Code: <code><?php echo esc_html($wplang); ?></code></small>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('Main Site URL', 'airlinel-theme'); ?></th>
                                <td>
                                    <code><?php echo esc_html($main_site_url); ?></code>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('API Key Status', 'airlinel-theme'); ?></th>
                                <td>
                                    <?php if ($api_key_set): ?>
                                        <span style="color: #28a745; font-weight: bold;">✓ <?php _e('Configured', 'airlinel-theme'); ?></span>
                                    <?php else: ?>
                                        <span style="color: #dc3545; font-weight: bold;">✗ <?php _e('Not Configured', 'airlinel-theme'); ?></span>
                                        <p style="margin-top: 10px; color: #666;">
                                            <?php printf(
                                                __('Please add %s to wp-config.php', 'airlinel-theme'),
                                                '<code>AIRLINEL_MAIN_SITE_API_KEY</code>'
                                            ); ?>
                                        </p>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('Proxy Status', 'airlinel-theme'); ?></th>
                                <td>
                                    <?php if ($proxy_status['initialized']): ?>
                                        <span style="color: #28a745; font-weight: bold;">✓ <?php _e('Initialized', 'airlinel-theme'); ?></span>
                                    <?php else: ?>
                                        <span style="color: #dc3545; font-weight: bold;">✗ <?php _e('Not Initialized', 'airlinel-theme'); ?></span>
                                        <?php if ($proxy_status['error']): ?>
                                            <p style="margin-top: 10px; color: #666;">
                                                <?php _e('Error:', 'airlinel-theme'); ?>
                                                <code><?php echo esc_html($proxy_status['error']); ?></code>
                                            </p>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('Last API Sync', 'airlinel-theme'); ?></th>
                                <td><?php echo esc_html($last_sync_time); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="postbox">
                <h2 class="hndle"><?php _e('Test Connection', 'airlinel-theme'); ?></h2>
                <div class="inside">
                    <p><?php _e('Click the button below to test the connection to the main site API.', 'airlinel-theme'); ?></p>
                    <form method="post" action="">
                        <?php wp_nonce_field('airlinel_regional_nonce'); ?>
                        <button type="submit" name="test_connection" class="button button-primary">
                            <?php _e('Test Connection to Main Site', 'airlinel-theme'); ?>
                        </button>
                    </form>
                </div>
            </div>

            <div class="postbox">
                <h2 class="hndle"><?php _e('Language Settings', 'airlinel-theme'); ?></h2>
                <div class="inside">
                    <p><?php _e('Select the language for this regional site. The admin interface will update when you save.', 'airlinel-theme'); ?></p>
                    <form method="post" action="">
                        <?php wp_nonce_field('airlinel_regional_nonce'); ?>
                        <table class="form-table">
                            <tr>
                                <th><label for="airlinel_language"><?php _e('Site Language', 'airlinel-theme'); ?></label></th>
                                <td>
                                    <select id="airlinel_language" name="airlinel_language" class="regular-text">
                                        <?php
                                        $languages = array(
                                            'en_US' => 'English (United States)',
                                            'tr_TR' => 'Türkçe (Türkiye)',
                                            'de_DE' => 'Deutsch (Deutschland)',
                                            'ru_RU' => 'Русский (Россия)',
                                            'fr_FR' => 'Français (France)',
                                            'it_IT' => 'Italiano (Italia)',
                                            'ar'    => 'العربية',
                                            'da_DK' => 'Dansk (Danmark)',
                                            'nl_NL' => 'Nederlands (Nederland)',
                                            'sv_SE' => 'Svenska (Sverige)',
                                            'zh_CN' => '中文 (简体)',
                                            'ja'    => '日本語',
                                        );
                                        foreach ($languages as $code => $name):
                                            ?>
                                            <option value="<?php echo esc_attr($code); ?>"
                                                    <?php selected($code, $wplang); ?>>
                                                <?php echo esc_html($name); ?>
                                            </option>
                                            <?php
                                        endforeach;
                                        ?>
                                    </select>
                                    <small><?php _e('This will change the WordPress admin language.', 'airlinel-theme'); ?></small>
                                </td>
                            </tr>
                        </table>
                        <button type="submit" name="save_language" class="button button-primary">
                            <?php _e('Update Language', 'airlinel-theme'); ?>
                        </button>
                    </form>
                </div>
            </div>

            <div class="postbox">
                <h2 class="hndle"><?php _e('Configuration Help', 'airlinel-theme'); ?></h2>
                <div class="inside">
                    <h3><?php _e('Required wp-config.php Settings', 'airlinel-theme'); ?></h3>
                    <p><?php _e('For this regional site to work correctly, the following must be configured in wp-config.php:', 'airlinel-theme'); ?></p>
                    <pre><code>// Main site URL (required)
define('AIRLINEL_MAIN_SITE_URL', 'https://airlinel.com');

// Regional site API key (from main site)
define('AIRLINEL_MAIN_SITE_API_KEY', 'your-api-key-here');

// Site language (required)
define('WPLANG', 'tr_TR');

// Site ID (optional, defaults to subdomain)
define('AIRLINEL_SITE_ID', 'antalya');</code></pre>

                    <h3><?php _e('Getting Your API Key', 'airlinel-theme'); ?></h3>
                    <ol>
                        <li><?php printf(__('Log in to the main site: %s', 'airlinel-theme'), '<code>https://airlinel.com/wp-admin</code>'); ?></li>
                        <li><?php _e('Go to: Settings → Airlinel Settings', 'airlinel-theme'); ?></li>
                        <li><?php _e('Scroll to "Regional Site API Keys" section', 'airlinel-theme'); ?></li>
                        <li><?php _e('Copy the API key for this regional site', 'airlinel-theme'); ?></li>
                        <li><?php _e('Paste it into wp-config.php as AIRLINEL_MAIN_SITE_API_KEY', 'airlinel-theme'); ?></li>
                    </ol>

                    <h3><?php _e('Troubleshooting', 'airlinel-theme'); ?></h3>
                    <ul>
                        <li>
                            <strong><?php _e('Connection fails:', 'airlinel-theme'); ?></strong>
                            <?php _e('Check that AIRLINEL_MAIN_SITE_URL and AIRLINEL_MAIN_SITE_API_KEY are correct in wp-config.php', 'airlinel-theme'); ?>
                        </li>
                        <li>
                            <strong><?php _e('Language not changing:', 'airlinel-theme'); ?></strong>
                            <?php _e('Try refreshing the page or clearing your browser cache', 'airlinel-theme'); ?>
                        </li>
                        <li>
                            <strong><?php _e('Booking form not showing vehicles:', 'airlinel-theme'); ?></strong>
                            <?php _e('Check the browser console (F12) for errors and verify the connection test passes', 'airlinel-theme'); ?>
                        </li>
                    </ul>
                </div>
            </div>

            <div style="margin-top: 20px; color: #666; font-size: 13px;">
                <p>
                    <?php printf(
                        __('For detailed setup instructions, see %s', 'airlinel-theme'),
                        '<code>/docs/REGIONAL_SETUP.md</code>'
                    ); ?>
                </p>
            </div>
        </div>

        <style>
            .airlinel-regional-settings .postbox {
                margin-top: 20px;
                margin-bottom: 20px;
            }
            .airlinel-regional-settings .postbox h2 {
                margin: 0;
                padding: 8px 12px;
                background: #f5f5f5;
                border-bottom: 1px solid #ddd;
                font-size: 13px;
                font-weight: 600;
            }
            .airlinel-regional-settings .postbox .inside {
                padding: 12px;
            }
            .airlinel-regional-settings table.widefat {
                margin-top: 10px;
            }
            .airlinel-regional-settings table.widefat th {
                width: 200px;
                background-color: #f9f9f9;
                font-weight: 600;
            }
            .airlinel-regional-settings table.widefat code {
                padding: 2px 6px;
                background-color: #f5f5f5;
                border-radius: 3px;
                font-family: 'Courier New', monospace;
            }
            .airlinel-regional-settings pre {
                background-color: #f5f5f5;
                padding: 12px;
                border-radius: 4px;
                overflow-x: auto;
                border: 1px solid #ddd;
            }
            .airlinel-regional-settings pre code {
                background-color: transparent;
                padding: 0;
                font-size: 12px;
            }
        </style>
        <?php
    }
}

/**
 * Test connection to main site API
 *
 * @return array {
 *     @type string 'message' Human-readable message
 *     @type string 'status' 'success' or 'error'
 * }
 */
function airlinel_regional_test_connection() {
    // Check if required constants are defined
    if (!defined('AIRLINEL_MAIN_SITE_URL')) {
        return array(
            'message' => __('Error: AIRLINEL_MAIN_SITE_URL not defined in wp-config.php', 'airlinel-theme'),
            'status' => 'error',
        );
    }

    if (!defined('AIRLINEL_MAIN_SITE_API_KEY')) {
        return array(
            'message' => __('Error: AIRLINEL_MAIN_SITE_API_KEY not defined in wp-config.php', 'airlinel-theme'),
            'status' => 'error',
        );
    }

    // Attempt to initialize and use proxy
    if (!class_exists('Airlinel_Regional_Site_Proxy')) {
        return array(
            'message' => __('Error: Regional Site Proxy class not found', 'airlinel-theme'),
            'status' => 'error',
        );
    }

    try {
        $proxy = new Airlinel_Regional_Site_Proxy();

        // Try a simple search call to test connectivity
        $result = $proxy->call_search('Test', 'Test', 'UK', 1, 'GBP');

        if (is_wp_error($result)) {
            // Even if search fails, it means the proxy initialized and contacted the server
            update_option('airlinel_regional_last_sync', time());
            return array(
                'message' => sprintf(
                    __('Connected to main site, but search returned: %s', 'airlinel-theme'),
                    $result->get_error_message()
                ),
                'status' => 'warning',
            );
        }

        // Success
        update_option('airlinel_regional_last_sync', time());
        return array(
            'message' => __('Successfully connected to main site API!', 'airlinel-theme'),
            'status' => 'success',
        );
    } catch (Exception $e) {
        return array(
            'message' => sprintf(__('Connection failed: %s', 'airlinel-theme'), $e->getMessage()),
            'status' => 'error',
        );
    }
}

/**
 * Check if Regional Site Proxy can be initialized
 *
 * @return array {
 *     @type bool 'initialized' Whether the proxy was initialized successfully
 *     @type string|null 'error' Error message if initialization failed
 * }
 */
function airlinel_check_regional_proxy_status() {
    if (!defined('AIRLINEL_MAIN_SITE_URL') || !defined('AIRLINEL_MAIN_SITE_API_KEY')) {
        return array(
            'initialized' => false,
            'error' => __('AIRLINEL_MAIN_SITE_URL or AIRLINEL_MAIN_SITE_API_KEY not defined', 'airlinel-theme'),
        );
    }

    if (!class_exists('Airlinel_Regional_Site_Proxy')) {
        return array(
            'initialized' => false,
            'error' => __('Regional Site Proxy class not found', 'airlinel-theme'),
        );
    }

    try {
        new Airlinel_Regional_Site_Proxy();
        return array(
            'initialized' => true,
            'error' => null,
        );
    } catch (Exception $e) {
        return array(
            'initialized' => false,
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Get language names mapping
 *
 * @return array Language code => Language name
 */
function airlinel_get_language_names() {
    return array(
        'en_US' => 'English (United States)',
        'tr_TR' => 'Türkçe (Türkiye)',
        'de_DE' => 'Deutsch (Deutschland)',
        'ru_RU' => 'Русский (Россия)',
        'fr_FR' => 'Français (France)',
        'it_IT' => 'Italiano (Italia)',
        'ar'    => 'العربية',
        'da_DK' => 'Dansk (Danmark)',
        'nl_NL' => 'Nederlands (Nederland)',
        'sv_SE' => 'Svenska (Sverige)',
        'zh_CN' => '中文 (简体)',
        'ja'    => '日本語',
    );
}
?>
