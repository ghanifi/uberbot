<?php
/**
 * Airlinel Regional API Settings Admin Page
 *
 * This page allows administrators to configure regional site connection settings:
 * - Main Site URL (e.g., https://main.airlinel.com)
 * - Regional Site ID (e.g., 'antalya', 'istanbul')
 * - API Key (for authenticating with main site)
 *
 * All settings are stored in wp_options for flexibility without wp-config.php constants
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('airlinel_render_regional_api_settings_page')) {
    /**
     * Render the Regional API Settings page in the admin panel
     */
    function airlinel_render_regional_api_settings_page() {
        // Security: Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.', 'airlinel-theme'));
        }

        // Initialize settings manager
        $settings_manager = new Airlinel_Regional_Settings_Manager();

        // Handle form submission (Save Settings)
        $save_message = null;
        $save_status = null;

        if (isset($_POST['airlinel_save_regional_settings'])) {
            // Verify nonce for security
            if (!isset($_POST['airlinel_regional_nonce']) ||
                !wp_verify_nonce($_POST['airlinel_regional_nonce'], 'airlinel_regional_settings_nonce')) {
                wp_die(__('Security check failed. Please try again.', 'airlinel-theme'));
            }

            // Collect and sanitize form input
            $form_data = array(
                'main_site_url' => isset($_POST['airlinel_main_site_url']) ?
                    esc_url_raw($_POST['airlinel_main_site_url']) : '',
                'site_id' => isset($_POST['airlinel_site_id']) ?
                    sanitize_text_field($_POST['airlinel_site_id']) : '',
                'api_key' => isset($_POST['airlinel_api_key']) ?
                    sanitize_text_field($_POST['airlinel_api_key']) : '',
            );

            // Save settings using the manager
            $save_result = $settings_manager->save_settings($form_data);

            if (is_wp_error($save_result)) {
                $save_status = 'error';
                $save_message = $save_result->get_error_message();
            } else {
                $save_status = 'success';
                $save_message = __('Regional API settings have been saved successfully!', 'airlinel-theme');
            }
        }

        // Handle connection test
        $test_message = null;
        $test_status = null;

        if (isset($_POST['airlinel_test_connection'])) {
            // Verify nonce for security
            if (!isset($_POST['airlinel_regional_nonce']) ||
                !wp_verify_nonce($_POST['airlinel_regional_nonce'], 'airlinel_regional_settings_nonce')) {
                wp_die(__('Security check failed. Please try again.', 'airlinel-theme'));
            }

            // Test the connection using the manager
            $test_result = $settings_manager->test_connection();

            if ($test_result['success']) {
                $test_status = 'success';
                $test_message = $test_result['message'];
            } else {
                $test_status = 'error';
                $test_message = $test_result['message'];
            }
        }

        // Get current settings to populate the form
        $current_settings = $settings_manager->get_all_settings();
        $is_configured = $settings_manager->is_configured();

        // Get environment information for display
        $wp_debug = defined('WP_DEBUG') ? WP_DEBUG : false;

        ?>
        <div class="wrap airlinel-regional-api-settings">
            <h1><?php _e('Regional Site API Settings', 'airlinel-theme'); ?></h1>
            <p><?php _e('Configure the connection between this regional site and the main Airlinel site.', 'airlinel-theme'); ?></p>

            <!-- Status/Messages Display -->
            <?php if ($save_message): ?>
                <div class="notice notice-<?php echo esc_attr($save_status); ?> is-dismissible">
                    <p><?php echo wp_kses_post($save_message); ?></p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text"><?php _e('Dismiss this notice', 'airlinel-theme'); ?></span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($test_message): ?>
                <div class="notice notice-<?php echo esc_attr($test_status); ?> is-dismissible">
                    <p><?php echo wp_kses_post($test_message); ?></p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text"><?php _e('Dismiss this notice', 'airlinel-theme'); ?></span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Configuration Status Overview -->
            <div class="postbox">
                <h2 class="hndle">
                    <span>
                        <?php _e('Configuration Status', 'airlinel-theme'); ?>
                        <span class="status-indicator" style="display: inline-block; width: 12px; height: 12px;
                                                              border-radius: 50%; margin-left: 10px;
                                                              background-color: <?php echo $is_configured ? '#28a745' : '#dc3545'; ?>;">
                        </span>
                    </span>
                </h2>
                <div class="inside">
                    <table class="widefat striped">
                        <tbody>
                            <tr>
                                <th><?php _e('Main Site URL', 'airlinel-theme'); ?></th>
                                <td>
                                    <?php if (!empty($current_settings['main_site_url'])): ?>
                                        <code><?php echo esc_html($current_settings['main_site_url']); ?></code>
                                        <span style="color: #28a745; margin-left: 10px;">✓ <?php _e('Configured', 'airlinel-theme'); ?></span>
                                    <?php else: ?>
                                        <span style="color: #dc3545;">✗ <?php _e('Not configured', 'airlinel-theme'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('Regional Site ID', 'airlinel-theme'); ?></th>
                                <td>
                                    <?php if (!empty($current_settings['site_id'])): ?>
                                        <code><?php echo esc_html($current_settings['site_id']); ?></code>
                                        <span style="color: #28a745; margin-left: 10px;">✓ <?php _e('Configured', 'airlinel-theme'); ?></span>
                                    <?php else: ?>
                                        <span style="color: #dc3545;">✗ <?php _e('Not configured', 'airlinel-theme'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('API Key Status', 'airlinel-theme'); ?></th>
                                <td>
                                    <?php if (!empty($current_settings['api_key'])): ?>
                                        <span style="color: #28a745;">✓ <?php _e('Configured', 'airlinel-theme'); ?></span>
                                        <small style="display: block; color: #666; margin-top: 5px;">
                                            <?php _e('API key is set (not displayed for security)', 'airlinel-theme'); ?>
                                        </small>
                                    <?php else: ?>
                                        <span style="color: #dc3545;">✗ <?php _e('Not configured', 'airlinel-theme'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Settings Form -->
            <div class="postbox">
                <h2 class="hndle"><?php _e('Regional API Configuration', 'airlinel-theme'); ?></h2>
                <div class="inside">
                    <form method="post" action="">
                        <?php wp_nonce_field('airlinel_regional_settings_nonce', 'airlinel_regional_nonce'); ?>

                        <table class="form-table">
                            <!-- Main Site URL Field -->
                            <tr>
                                <th scope="row">
                                    <label for="airlinel_main_site_url">
                                        <?php _e('Main Site URL', 'airlinel-theme'); ?>
                                        <span style="color: red;">*</span>
                                    </label>
                                </th>
                                <td>
                                    <input type="url"
                                           id="airlinel_main_site_url"
                                           name="airlinel_main_site_url"
                                           class="regular-text"
                                           value="<?php echo esc_attr($current_settings['main_site_url']); ?>"
                                           placeholder="https://main.airlinel.com"
                                           required>
                                    <p class="description">
                                        <?php _e('The URL of the main Airlinel website (e.g., https://main.airlinel.com)', 'airlinel-theme'); ?>
                                    </p>
                                </td>
                            </tr>

                            <!-- Regional Site ID Field -->
                            <tr>
                                <th scope="row">
                                    <label for="airlinel_site_id">
                                        <?php _e('Regional Site ID', 'airlinel-theme'); ?>
                                        <span style="color: red;">*</span>
                                    </label>
                                </th>
                                <td>
                                    <input type="text"
                                           id="airlinel_site_id"
                                           name="airlinel_site_id"
                                           class="regular-text"
                                           value="<?php echo esc_attr($current_settings['site_id']); ?>"
                                           placeholder="antalya"
                                           pattern="[a-z0-9\-]+"
                                           required>
                                    <p class="description">
                                        <?php _e('A unique identifier for this regional site (lowercase letters, numbers, and hyphens only). Examples: antalya, istanbul, london', 'airlinel-theme'); ?>
                                    </p>
                                </td>
                            </tr>

                            <!-- API Key Field -->
                            <tr>
                                <th scope="row">
                                    <label for="airlinel_api_key">
                                        <?php _e('API Key', 'airlinel-theme'); ?>
                                        <span style="color: red;">*</span>
                                    </label>
                                </th>
                                <td>
                                    <input type="password"
                                           id="airlinel_api_key"
                                           name="airlinel_api_key"
                                           class="regular-text"
                                           value="<?php echo esc_attr($current_settings['api_key']); ?>"
                                           placeholder="<?php esc_attr_e('Enter your API key', 'airlinel-theme'); ?>"
                                           minlength="10"
                                           required>
                                    <p class="description">
                                        <?php _e('The API key provided by the main site for authenticating regional site requests. Keep this secure.', 'airlinel-theme'); ?>
                                    </p>
                                    <?php if (!empty($current_settings['api_key'])): ?>
                                        <p style="margin-top: 10px;">
                                            <label>
                                                <input type="checkbox" id="show_api_key" onchange="document.getElementById('airlinel_api_key').type = this.checked ? 'text' : 'password'">
                                                <?php _e('Show API key', 'airlinel-theme'); ?>
                                            </label>
                                        </p>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>

                        <!-- Form Actions -->
                        <div style="margin-top: 20px;">
                            <?php submit_button(__('Save Settings', 'airlinel-theme'), 'primary', 'airlinel_save_regional_settings'); ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Connection Test Section -->
            <div class="postbox">
                <h2 class="hndle"><?php _e('Test Connection', 'airlinel-theme'); ?></h2>
                <div class="inside">
                    <p>
                        <?php _e('Click the button below to verify the connection to the main site using the configured settings.', 'airlinel-theme'); ?>
                    </p>

                    <?php if (!$is_configured): ?>
                        <div class="notice notice-warning inline">
                            <p>
                                <?php _e('Please configure all required settings before testing the connection.', 'airlinel-theme'); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="" style="margin-top: 15px;">
                        <?php wp_nonce_field('airlinel_regional_settings_nonce', 'airlinel_regional_nonce'); ?>
                        <?php
                            $button_args = $is_configured ? array() : array('disabled' => 'disabled');
                            submit_button(
                                __('Test Connection to Main Site', 'airlinel-theme'),
                                'secondary',
                                'airlinel_test_connection',
                                true,
                                $button_args
                            );
                        ?>
                    </form>
                </div>
            </div>

            <!-- Information Section -->
            <div class="postbox">
                <h2 class="hndle"><?php _e('Setup Guide & Information', 'airlinel-theme'); ?></h2>
                <div class="inside">
                    <h3><?php _e('📍 Quick Setup Workflow', 'airlinel-theme'); ?></h3>
                    <div style="background: #f0f6ff; border-left: 4px solid #0073aa; padding: 15px; margin-bottom: 20px; border-radius: 3px;">
                        <p><strong><?php _e('Step 1: On the Main Site', 'airlinel-theme'); ?></strong></p>
                        <ol style="margin-left: 20px; margin-top: 8px;">
                            <li><?php _e('Go to: Airlinel → Regional Sites', 'airlinel-theme'); ?></li>
                            <li><?php _e('Generate a new API Key for this regional site (e.g., "testairlinel")', 'airlinel-theme'); ?></li>
                            <li><?php _e('Copy the generated API Key', 'airlinel-theme'); ?></li>
                        </ol>
                        <p style="margin-top: 15px;"><strong><?php _e('Step 2: On the Regional Site (this page)', 'airlinel-theme'); ?></strong></p>
                        <ol style="margin-left: 20px; margin-top: 8px;">
                            <li><?php _e('Main Site URL: Enter the URL from Step 1 (e.g., https://airlinelmaintest.londonos.uk)', 'airlinel-theme'); ?></li>
                            <li><?php _e('Regional Site ID: Enter your regional site ID (e.g., testairlinel - must match Main Site setup)', 'airlinel-theme'); ?></li>
                            <li><?php _e('API Key: Paste the key from Step 1', 'airlinel-theme'); ?></li>
                            <li><?php _e('Click "Save Settings"', 'airlinel-theme'); ?></li>
                            <li><?php _e('Click "Test Connection" to verify the setup', 'airlinel-theme'); ?></li>
                        </ol>
                    </div>

                    <h3><?php _e('❓ What are Regional API Settings?', 'airlinel-theme'); ?></h3>
                    <p>
                        <?php _e('This regional site connects to a main Airlinel site to synchronize data (flights, prices, availability). The API Key is used to authenticate requests, and the Site ID uniquely identifies this regional installation.', 'airlinel-theme'); ?>
                    </p>

                    <h3><?php _e('🔐 Important Security Notes', 'airlinel-theme'); ?></h3>
                    <div style="background: #fff3cd; border-left: 4px solid #ff9800; padding: 15px; margin-bottom: 20px; border-radius: 3px;">
                        <ul style="margin-left: 20px;">
                            <li><?php _e('API keys are sensitive - keep them secure', 'airlinel-theme'); ?></li>
                            <li><?php _e('For production environments, store API keys in wp-config.php, not in database', 'airlinel-theme'); ?></li>
                            <li><?php _e('Never commit API keys to version control (Git)', 'airlinel-theme'); ?></li>
                            <li><?php _e('If you suspect key compromise, regenerate it immediately on the Main Site', 'airlinel-theme'); ?></li>
                        </ul>
                    </div>

                    <h3><?php _e('🔗 Related Pages', 'airlinel-theme'); ?></h3>
                    <p><?php _e('Other regional site configuration pages:', 'airlinel-theme'); ?></p>
                    <ul style="margin-left: 20px;">
                        <li><strong><?php _e('This page (Regional API Settings):', 'airlinel-theme'); ?></strong>
                            <?php _e(' Configure the connection to your main site', 'airlinel-theme'); ?></li>
                        <li><strong><?php _e('Settings (Airlinel Settings):', 'airlinel-theme'); ?></strong>
                            <?php _e(' General application settings - DO NOT confuse this with API settings', 'airlinel-theme'); ?></li>
                    </ul>

                    <h3><?php _e('💾 Database Storage', 'airlinel-theme'); ?></h3>
                    <p>
                        <?php _e('These settings are stored securely in the WordPress options table with keys:', 'airlinel-theme'); ?>
                    </p>
                    <ul style="margin-left: 20px; font-family: monospace; background: #f5f5f5; padding: 10px; border-radius: 3px;">
                        <li>airlinel_regional_main_site_url</li>
                        <li>airlinel_regional_api_key</li>
                        <li>airlinel_regional_site_id</li>
                    </ul>
                </div>
            </div>
        </div>

        <style>
            .airlinel-regional-api-settings .postbox {
                margin-top: 20px;
            }

            .airlinel-regional-api-settings .form-table {
                margin-top: 10px;
            }

            .airlinel-regional-api-settings .form-table th {
                width: 200px;
                font-weight: 600;
            }

            .airlinel-regional-api-settings .description {
                color: #666;
                font-size: 13px;
                margin-top: 5px;
            }

            .airlinel-regional-api-settings .status-indicator {
                vertical-align: middle;
            }
        </style>
        <?php
    }
}

// Register the admin page function for use in functions.php
if (!function_exists('airlinel_register_regional_api_settings_page')) {
    /**
     * Register the Regional API Settings admin page in the menu
     */
    function airlinel_register_regional_api_settings_page() {
        add_submenu_page(
            'airlinel-settings',
            __('Regional API Settings', 'airlinel-theme'),
            __('Regional API Settings', 'airlinel-theme'),
            'manage_options',
            'airlinel-regional-api-settings',
            'airlinel_render_regional_api_settings_page'
        );
    }
}
?>
