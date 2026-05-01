<?php
/**
 * Regional Settings Manager
 * Handles database-driven configuration for regional sites (main site URL, API key, site ID)
 * Replaces hardcoded constants in wp-config.php with flexible wp_options storage
 */

class Airlinel_Regional_Settings_Manager {

    private $option_prefix = 'airlinel_regional_';

    /**
     * Get main site URL
     *
     * @return string The main site URL, empty if not configured
     */
    public function get_main_site_url() {
        return get_option($this->option_prefix . 'main_site_url', '');
    }

    /**
     * Set main site URL with validation
     *
     * @param string $url The main site URL to save
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function set_main_site_url($url) {
        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return new WP_Error('invalid_url', 'Invalid URL format. Please enter a valid URL (e.g., https://example.com)');
        }

        // Ensure URL is properly sanitized
        $url = esc_url_raw($url);

        return update_option($this->option_prefix . 'main_site_url', $url);
    }

    /**
     * Get regional site API key (used for connecting to main site)
     *
     * @return string The API key, empty if not configured
     */
    public function get_api_key() {
        return get_option($this->option_prefix . 'api_key', '');
    }

    /**
     * Set regional site API key with validation
     *
     * @param string $key The API key to save
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function set_api_key($key) {
        if (empty($key)) {
            return new WP_Error('empty_key', 'API key cannot be empty');
        }

        // Sanitize the key (should be alphanumeric with hyphens/underscores)
        $key = sanitize_text_field($key);

        if (strlen($key) < 10) {
            return new WP_Error('short_key', 'API key must be at least 10 characters long');
        }

        return update_option($this->option_prefix . 'api_key', $key);
    }

    /**
     * Get regional site ID (e.g., 'antalya', 'istanbul')
     *
     * @return string The site ID, empty if not configured
     */
    public function get_site_id() {
        return get_option($this->option_prefix . 'site_id', '');
    }

    /**
     * Set regional site ID with validation
     *
     * @param string $site_id The site ID to save (lowercase alphanumeric and hyphens)
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function set_site_id($site_id) {
        if (empty($site_id)) {
            return new WP_Error('empty_site_id', 'Site ID cannot be empty');
        }

        // Sanitize: lowercase, alphanumeric and hyphens only
        $site_id = sanitize_text_field(strtolower($site_id));

        if (!preg_match('/^[a-z0-9\-]+$/', $site_id)) {
            return new WP_Error('invalid_site_id', 'Site ID must contain only lowercase letters, numbers, and hyphens');
        }

        return update_option($this->option_prefix . 'site_id', $site_id);
    }

    /**
     * Get all regional settings at once
     *
     * @return array Array with keys: main_site_url, api_key, site_id
     */
    public function get_all_settings() {
        return array(
            'main_site_url' => $this->get_main_site_url(),
            'api_key'       => $this->get_api_key(),
            'site_id'       => $this->get_site_id(),
        );
    }

    /**
     * Check if regional settings are configured
     *
     * @return bool True if all required settings are configured
     */
    public function is_configured() {
        $settings = $this->get_all_settings();
        return !empty($settings['main_site_url']) &&
               !empty($settings['api_key']) &&
               !empty($settings['site_id']);
    }

    /**
     * Save all settings at once with validation
     *
     * @param array $data Array with keys: main_site_url, api_key, site_id
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function save_settings($data) {
        if (!is_array($data)) {
            return new WP_Error('invalid_data', 'Settings must be an array');
        }

        // Validate and save main site URL
        if (isset($data['main_site_url'])) {
            $url_result = $this->set_main_site_url($data['main_site_url']);
            if (is_wp_error($url_result)) {
                return $url_result;
            }
        }

        // Validate and save API key
        if (isset($data['api_key'])) {
            $key_result = $this->set_api_key($data['api_key']);
            if (is_wp_error($key_result)) {
                return $key_result;
            }
        }

        // Validate and save site ID
        if (isset($data['site_id'])) {
            $id_result = $this->set_site_id($data['site_id']);
            if (is_wp_error($id_result)) {
                return $id_result;
            }
        }

        return true;
    }

    /**
     * Test connection to main site API
     * Verifies that the configured main site URL and API key are valid
     *
     * @return array Array with 'success' (bool) and 'message' (string) keys
     */
    public function test_connection() {
        $main_url = $this->get_main_site_url();
        $api_key = $this->get_api_key();

        // Check if both URL and API key are configured
        if (empty($main_url) || empty($api_key)) {
            return array(
                'success' => false,
                'message' => 'Main site URL and API key must be configured before testing the connection.',
            );
        }

        // Build API endpoint URL
        $endpoint = rtrim($main_url, '/') . '/wp-json/airlinel/v1/health';

        // Make test request to main site
        $response = wp_remote_get($endpoint, array(
            'headers' => array(
                'X-API-Key'     => $api_key,
                'Content-Type'  => 'application/json',
            ),
            'timeout'   => 10,
            'sslverify' => true,
        ));

        // Check for network errors
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'Connection failed: ' . $response->get_error_message(),
            );
        }

        // Check HTTP status code
        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code === 200) {
            return array(
                'success' => true,
                'message' => 'Connected to main site successfully! Main site is responding correctly.',
            );
        } elseif ($status_code === 401 || $status_code === 403) {
            return array(
                'success' => false,
                'message' => 'Authentication failed. The API key is invalid or has been revoked.',
            );
        } elseif ($status_code >= 500) {
            return array(
                'success' => false,
                'message' => 'Main site is experiencing issues (HTTP ' . $status_code . '). Please try again later.',
            );
        } else {
            return array(
                'success' => false,
                'message' => 'Unexpected response from main site (HTTP ' . $status_code . '). Please verify the main site URL.',
            );
        }
    }

    /**
     * Clear all regional settings
     * This should only be called by administrators
     *
     * @return bool True on success
     */
    public function clear_all_settings() {
        delete_option($this->option_prefix . 'main_site_url');
        delete_option($this->option_prefix . 'api_key');
        delete_option($this->option_prefix . 'site_id');
        return true;
    }
}
?>
