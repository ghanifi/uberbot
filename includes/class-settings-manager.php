<?php
/**
 * Airlinel Settings Manager
 * Manages admin settings panel for API keys and km rates
 */
class Airlinel_Settings_Manager {

    public function register_admin_page() {
        add_menu_page(
            'Airlinel Settings',
            'Airlinel Settings',
            'manage_options',
            'airlinel-settings',
            array($this, 'render_page'),
            'dashicons-cog',
            3
        );

        // Task 3.0: Add regional sites submenu (main site only)
        add_submenu_page(
            'airlinel-settings',
            'Regional Sites',
            'Regional Sites',
            'manage_options',
            'airlinel-regional-sites',
            array($this, 'render_regional_sites_page')
        );
    }

    public function render_page() {
        if (isset($_POST['airlinel_save'])) {
            if (!current_user_can('manage_options')) {
                wp_die('You do not have permission to manage settings.');
            }
            check_admin_referer('airlinel_nonce');

            // Validate numeric ranges
            $uk_km = floatval($_POST['uk_km_rate']);
            $tr_km = floatval($_POST['tr_km_rate']);

            if ($uk_km < 0.01 || $uk_km > 1000) {
                wp_die('UK KM rate must be between 0.01 and 1000');
            }
            if ($tr_km < 0.01 || $tr_km > 1000) {
                wp_die('Turkey KM rate must be between 0.01 and 1000');
            }

            update_option('airlinel_api_key', sanitize_text_field($_POST['api_key']));
            update_option('airlinel_google_maps_key', sanitize_text_field($_POST['google_key']));
            update_option('airlinel_stripe_pub_key', sanitize_text_field($_POST['stripe_pub']));
            update_option('airlinel_stripe_secret_key', sanitize_text_field($_POST['stripe_secret']));
            update_option('airlinel_uk_km_rate', $uk_km);
            update_option('airlinel_tr_km_rate', $tr_km);
            // Social media URLs
            update_option('airlinel_social_facebook',  esc_url_raw($_POST['social_facebook']  ?? ''));
            update_option('airlinel_social_instagram', esc_url_raw($_POST['social_instagram'] ?? ''));
            update_option('airlinel_social_linkedin',  esc_url_raw($_POST['social_linkedin']  ?? ''));
            update_option('airlinel_social_twitter',   esc_url_raw($_POST['social_twitter']   ?? ''));
            echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
        }

        $api_key        = self::get('airlinel_api_key', wp_generate_password(32, false));
        $google_key     = self::get('airlinel_google_maps_key');
        $stripe_pub     = self::get('airlinel_stripe_pub_key');
        $stripe_secret  = self::get('airlinel_stripe_secret_key');
        $uk_km          = self::get('airlinel_uk_km_rate', '0.75');
        $tr_km          = self::get('airlinel_tr_km_rate', '0.65');
        $social_fb      = self::get('airlinel_social_facebook',  '');
        $social_ig      = self::get('airlinel_social_instagram', '');
        $social_li      = self::get('airlinel_social_linkedin',  '');
        $social_tw      = self::get('airlinel_social_twitter',   '');

        ?>
        <div class="wrap">
            <h1>Airlinel Transfer Settings</h1>
            <form method="post">
                <?php wp_nonce_field('airlinel_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><label>API Key</label></th>
                        <td><input type="text" name="api_key" value="<?php echo esc_attr($api_key); ?>" size="60"></td>
                    </tr>
                    <tr>
                        <th><label>Google Maps API Key</label></th>
                        <td><input type="password" name="google_key" value="<?php echo esc_attr($google_key); ?>" size="60"></td>
                    </tr>
                    <tr>
                        <th><label>Stripe Publishable Key</label></th>
                        <td><input type="password" name="stripe_pub" value="<?php echo esc_attr($stripe_pub); ?>" size="60"></td>
                    </tr>
                    <tr>
                        <th><label>Stripe Secret Key</label></th>
                        <td><input type="password" name="stripe_secret" value="<?php echo esc_attr($stripe_secret); ?>" size="60"></td>
                    </tr>
                    <tr>
                        <th><label>UK Default Rate (£/km)</label></th>
                        <td><input type="number" name="uk_km_rate" value="<?php echo esc_attr($uk_km); ?>" step="0.01" min="0"></td>
                    </tr>
                    <tr>
                        <th><label>Turkey Default Rate (£/km)</label></th>
                        <td><input type="number" name="tr_km_rate" value="<?php echo esc_attr($tr_km); ?>" step="0.01" min="0"></td>
                    </tr>
                </table>

                <h2 style="margin-top:30px;">Social Media URLs</h2>
                <p style="color:#666; font-size:13px;">Leave blank to hide the icon in the footer.</p>
                <table class="form-table">
                    <tr>
                        <th><label>Facebook URL</label></th>
                        <td><input type="url" name="social_facebook" value="<?php echo esc_attr($social_fb); ?>" size="60" placeholder="https://facebook.com/yourpage"></td>
                    </tr>
                    <tr>
                        <th><label>Instagram URL</label></th>
                        <td><input type="url" name="social_instagram" value="<?php echo esc_attr($social_ig); ?>" size="60" placeholder="https://instagram.com/yourpage"></td>
                    </tr>
                    <tr>
                        <th><label>LinkedIn URL</label></th>
                        <td><input type="url" name="social_linkedin" value="<?php echo esc_attr($social_li); ?>" size="60" placeholder="https://linkedin.com/company/yourpage"></td>
                    </tr>
                    <tr>
                        <th><label>X / Twitter URL</label></th>
                        <td><input type="url" name="social_twitter" value="<?php echo esc_attr($social_tw); ?>" size="60" placeholder="https://x.com/yourpage"></td>
                    </tr>
                </table>

                <?php submit_button('Save Settings', 'primary', 'airlinel_save'); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Task 3.0: Render regional sites management page
     */
    public function render_regional_sites_page() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to manage regional sites.');
        }

        // Handle form submission
        if (isset($_POST['airlinel_save_regional'])) {
            check_admin_referer('airlinel_nonce');

            // Delete existing site
            if (isset($_POST['delete_site_id'])) {
                $delete_id = sanitize_text_field($_POST['delete_site_id']);
                $regional_keys = get_option('airlinel_regional_api_keys', array());
                if (isset($regional_keys[$delete_id])) {
                    unset($regional_keys[$delete_id]);
                    update_option('airlinel_regional_api_keys', $regional_keys);
                    echo '<div class="notice notice-success"><p>Regional site deleted!</p></div>';
                }
                return;
            }

            // Add new site
            $site_id = sanitize_text_field($_POST['site_id'] ?? '');
            $site_name = sanitize_text_field($_POST['site_name'] ?? '');
            $site_url = esc_url_raw($_POST['site_url'] ?? '');

            if (empty($site_id) || empty($site_name) || empty($site_url)) {
                echo '<div class="notice notice-error"><p>All fields are required!</p></div>';
            } else {
                // Generate unique API key for this regional site
                $api_key = wp_generate_password(32, false);

                $regional_keys = get_option('airlinel_regional_api_keys', array());
                $regional_keys[$site_id] = $api_key;
                update_option('airlinel_regional_api_keys', $regional_keys);

                // Store site metadata
                $regional_sites = get_option('airlinel_regional_sites_metadata', array());
                $regional_sites[$site_id] = array(
                    'name' => $site_name,
                    'url' => $site_url,
                    'created' => current_time('mysql'),
                );
                update_option('airlinel_regional_sites_metadata', $regional_sites);

                echo '<div class="notice notice-success"><p>Regional site added! API Key: <code>' . esc_html($api_key) . '</code></p></div>';
            }
        }

        $regional_keys = get_option('airlinel_regional_api_keys', array());
        $regional_sites = get_option('airlinel_regional_sites_metadata', array());

        ?>
        <div class="wrap">
            <h1>Regional Sites Management</h1>
            <p>Manage regional site API keys and configuration for the multi-site platform.</p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                <!-- Add New Regional Site Form -->
                <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h2>Add New Regional Site</h2>
                    <form method="post">
                        <?php wp_nonce_field('airlinel_nonce'); ?>
                        <table class="form-table">
                            <tr>
                                <th><label for="site_id">Site ID (e.g., "antalya", "istanbul")</label></th>
                                <td><input type="text" id="site_id" name="site_id" required></td>
                            </tr>
                            <tr>
                                <th><label for="site_name">Site Name</label></th>
                                <td><input type="text" id="site_name" name="site_name" placeholder="Antalya Transfer Service" required></td>
                            </tr>
                            <tr>
                                <th><label for="site_url">Site URL</label></th>
                                <td><input type="url" id="site_url" name="site_url" placeholder="https://antalya.airlinel.com" required></td>
                            </tr>
                        </table>
                        <?php submit_button('Add Regional Site', 'primary', 'airlinel_save_regional'); ?>
                    </form>
                </div>

                <!-- Configuration Guide -->
                <div style="background: #f1f1f1; padding: 20px; border-radius: 5px;">
                    <h2>Setup Instructions</h2>
                    <ol style="padding-left: 20px;">
                        <li>Add regional site details above</li>
                        <li>Copy the generated API Key</li>
                        <li>Add to regional site's wp-config.php:
                            <pre style="background: white; padding: 10px; margin-top: 10px; font-size: 12px;"><code>define('AIRLINEL_MAIN_SITE_URL', 'https://airlinel.com');
define('AIRLINEL_MAIN_SITE_API_KEY', '[COPIED_KEY_HERE]');
update_option('airlinel_source_site_id', '[SITE_ID]');</code></pre>
                        </li>
                    </ol>
                </div>
            </div>

            <!-- List Regional Sites -->
            <h2>Registered Regional Sites</h2>
            <?php if (empty($regional_keys)) : ?>
                <p>No regional sites configured yet.</p>
            <?php else : ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>Site ID</th>
                            <th>Site Name</th>
                            <th>Site URL</th>
                            <th>Created</th>
                            <th>API Key</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($regional_keys as $site_id => $api_key) : ?>
                            <?php $site_info = $regional_sites[$site_id] ?? array(); ?>
                            <tr>
                                <td><strong><?php echo esc_html($site_id); ?></strong></td>
                                <td><?php echo esc_html($site_info['name'] ?? ''); ?></td>
                                <td><a href="<?php echo esc_url($site_info['url'] ?? ''); ?>" target="_blank"><?php echo esc_html($site_info['url'] ?? ''); ?></a></td>
                                <td><?php echo esc_html($site_info['created'] ?? ''); ?></td>
                                <td>
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <code style="font-size: 12px; padding: 6px 10px; background: #f1f1f1; word-break: break-all; flex-grow: 1; max-width: 300px;" id="api-key-<?php echo esc_attr($site_id); ?>"><?php echo esc_html($api_key); ?></code>
                                        <button type="button" class="button button-small" onclick="airlinel_copy_to_clipboard('api-key-<?php echo esc_attr($site_id); ?>')">
                                            📋 Copy
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <form method="post" style="display: inline;">
                                        <?php wp_nonce_field('airlinel_nonce'); ?>
                                        <input type="hidden" name="delete_site_id" value="<?php echo esc_attr($site_id); ?>">
                                        <button type="submit" name="airlinel_save_regional" class="button button-small button-link-delete" onclick="return confirm('Delete this regional site?');">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h2 style="margin-top: 30px;">API Key Management</h2>
            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <strong>Important:</strong> API keys are sensitive. Store them securely in wp-config.php on regional sites, not in database.
                Never commit keys to version control.
            </div>

            <table class="form-table">
                <tr>
                    <th><label>Main Site API Key</label></th>
                    <td>
                        <code><?php echo esc_html(self::get('airlinel_api_key')); ?></code>
                        <p class="description">Used by main site API endpoints. Change this if compromised.</p>
                    </td>
                </tr>
            </table>

            <script>
                function airlinel_copy_to_clipboard(elementId) {
                    const element = document.getElementById(elementId);
                    const text = element.textContent;

                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        // Modern clipboard API
                        navigator.clipboard.writeText(text).then(function() {
                            const button = event.target;
                            const originalText = button.textContent;
                            button.textContent = '✓ Copied!';
                            setTimeout(function() {
                                button.textContent = originalText;
                            }, 2000);
                        }).catch(function(err) {
                            alert('Failed to copy: ' + err);
                        });
                    } else {
                        // Fallback for older browsers
                        const textarea = document.createElement('textarea');
                        textarea.value = text;
                        document.body.appendChild(textarea);
                        textarea.select();
                        try {
                            document.execCommand('copy');
                            const button = event.target;
                            const originalText = button.textContent;
                            button.textContent = '✓ Copied!';
                            setTimeout(function() {
                                button.textContent = originalText;
                            }, 2000);
                        } catch (err) {
                            alert('Failed to copy: ' + err);
                        }
                        document.body.removeChild(textarea);
                    }
                }
            </script>
        </div>
        <?php
    }

    public static function get($key, $default = '') {
        return get_option($key, $default);
    }
}
?>
