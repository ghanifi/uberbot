<?php
/**
 * Airlinel Regional Site Proxy
 * Proxies API requests from regional sites to the main site
 * Implements caching and fallback strategies
 */
class Airlinel_Regional_Site_Proxy {
    private $main_site_url;
    private $main_site_api_key;
    private $source_site_id;
    private $transient_ttl = 300; // 5 minutes
    private $request_timeout = 30; // seconds

    public function __construct() {
        // Read main site URL from wp-config constant
        if (!defined('AIRLINEL_MAIN_SITE_URL')) {
            error_log('AIRLINEL_MAIN_SITE_URL not defined in wp-config');
            return;
        }
        $this->main_site_url = AIRLINEL_MAIN_SITE_URL;

        // Read API key from wp-config constant
        if (!defined('AIRLINEL_MAIN_SITE_API_KEY')) {
            error_log('AIRLINEL_MAIN_SITE_API_KEY not defined in wp-config');
            return;
        }
        $this->main_site_api_key = AIRLINEL_MAIN_SITE_API_KEY;

        // Read source site ID from wp_option
        $this->source_site_id = get_option('airlinel_source_site_id', '');
    }

    /**
     * Call /search endpoint on main site
     * FIX 5: HIGH - Add input validation
     */
    public function call_search($pickup, $dropoff, $country, $passengers = 1, $currency = 'GBP') {
        // Validate inputs
        if (!is_string($pickup) || empty(trim($pickup))) {
            return new WP_Error('invalid_pickup', 'Invalid pickup location');
        }
        if (!is_string($dropoff) || empty(trim($dropoff))) {
            return new WP_Error('invalid_dropoff', 'Invalid dropoff location');
        }
        if (!in_array($country, array('UK', 'TR'), true)) {
            return new WP_Error('invalid_country', 'Country must be UK or TR');
        }
        if (!is_numeric($passengers) || $passengers < 1 || $passengers > 20) {
            return new WP_Error('invalid_passengers', 'Passengers must be 1-20');
        }
        if (!in_array($currency, array('GBP', 'EUR', 'TRY', 'USD'), true)) {
            return new WP_Error('invalid_currency', 'Invalid currency');
        }

        $cache_key = $this->get_cache_key('search', $pickup, $dropoff, $country);

        // Try to get from cache first
        $cached = $this->get_cached_vehicles($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        // Build request
        $request_body = array(
            'pickup' => $pickup,
            'dropoff' => $dropoff,
            'country' => $country,
            'passengers' => intval($passengers),
            'currency' => $currency,
            'source_site' => $this->source_site_id,
        );

        $response = $this->send_request('/search', 'POST', $request_body);

        if (is_wp_error($response)) {
            // FIX 7: MEDIUM - Actually use cached data on failure instead of misleading error
            error_log('Error calling main site: ' . $response->get_error_message());
            $cached = $this->get_cached_vehicles($cache_key);
            if ($cached) {
                error_log('Using cached vehicle data for ' . $this->source_site_id);
                return rest_ensure_response($cached);
            }
            return new WP_Error('api_unavailable', 'Main site is temporarily unavailable and no cached data available', array('status' => 503));
        }

        // REGRESSION FIX 1: Validate response structure for search endpoint only
        if (!isset($response['vehicles']) || !is_array($response['vehicles'])) {
            error_log('Invalid search response - missing vehicles array');
            return new WP_Error('invalid_response', 'Invalid response format from main site');
        }

        // Cache the successful response
        $this->cache_vehicles($cache_key, $response);

        return $response;
    }

    /**
     * Call /reservation/create endpoint on main site
     * FIX 5: HIGH - Add input validation
     */
    public function call_create_reservation($data) {
        // Validate required fields
        if (!is_array($data)) {
            return new WP_Error('invalid_data', 'Data must be an array');
        }

        // Validate customer name
        $customer_name = isset($data['customer_name']) ? $data['customer_name'] : '';
        if (!is_string($customer_name) || empty(trim($customer_name))) {
            return new WP_Error('invalid_customer_name', 'Customer name is required');
        }

        // Validate email
        $email = isset($data['email']) ? $data['email'] : '';
        if (!is_string($email) || !is_email($email)) {
            return new WP_Error('invalid_email', 'Valid email is required');
        }

        // Validate phone (if provided)
        if (!empty($data['phone']) && !is_string($data['phone'])) {
            return new WP_Error('invalid_phone', 'Phone must be a string');
        }

        // Add source tracking fields
        $data['source_site'] = $this->source_site_id;
        $data['source_language'] = get_locale();
        $data['source_url'] = get_site_url();

        $response = $this->send_request('/reservation/create', 'POST', $data);

        if (is_wp_error($response)) {
            error_log('Regional proxy create_reservation failed: ' . $response->get_error_message());
            return $response;
        }

        return $response;
    }

    /**
     * Call /reservation/{id} endpoint on main site
     */
    public function call_get_reservation($id) {
        $response = $this->send_request('/reservation/' . intval($id), 'GET', array());

        if (is_wp_error($response)) {
            error_log('Regional proxy get_reservation failed: ' . $response->get_error_message());
            return $response;
        }

        return $response;
    }

    /**
     * Get cached vehicles by key
     */
    public function get_cached_vehicles($cache_key) {
        return get_transient($cache_key);
    }

    /**
     * Cache vehicles locally with TTL
     */
    public function cache_vehicles($cache_key, $data) {
        set_transient($cache_key, $data, $this->transient_ttl);
    }

    /**
     * Generate cache key from parameters
     * FIX 4: HIGH - Include source site ID to prevent cache key collision across regional sites
     */
    private function get_cache_key($endpoint, $pickup, $dropoff, $country) {
        $cache_id = $this->source_site_id . '_' . $endpoint . '_' . $pickup . '_' . $dropoff . '_' . $country;
        return 'airlinel_proxy_' . md5($cache_id);
    }

    /**
     * Send request to main site via wp_remote_request
     * Supports both GET and POST methods
     */
    private function send_request($endpoint, $method, $body) {
        if (empty($this->main_site_url) || empty($this->main_site_api_key)) {
            return new WP_Error('config_error', 'Proxy configuration incomplete');
        }

        $url = rtrim($this->main_site_url, '/') . '/wp-json/airlinel/v1' . $endpoint;

        // Prepare request arguments
        $args = array(
            'method' => $method,
            'timeout' => $this->request_timeout,
            'headers' => array(
                'Content-Type' => 'application/json',
                'x-api-key' => $this->main_site_api_key,
            ),
            'sslverify' => true, // Verify SSL in production
        );

        // Add body for POST requests
        if ($method === 'POST') {
            $args['body'] = wp_json_encode($body);
        }

        // Send request using wp_remote_request (supports both GET and POST)
        $response = wp_remote_request($url, $args);

        // Check for network errors
        if (is_wp_error($response)) {
            return $response;
        }

        // Check HTTP status code
        $http_code = wp_remote_retrieve_response_code($response);
        if ($http_code >= 400) {
            $error_body = wp_remote_retrieve_body($response);
            error_log('Regional proxy request failed (HTTP ' . $http_code . '): ' . $error_body);
            return new WP_Error(
                'http_error',
                'Main site returned error: HTTP ' . $http_code,
                array('status' => $http_code)
            );
        }

        // Parse response body
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Regional proxy JSON decode failed: ' . json_last_error_msg());
            return new WP_Error('json_error', 'Invalid response from main site');
        }

        // REGRESSION FIX 1: Only validate JSON array structure (not endpoint-specific keys)
        if (!is_array($data)) {
            error_log('Invalid JSON response from main site: ' . $body);
            return new WP_Error('invalid_response', 'Invalid response format from main site');
        }

        return $data;
    }
}
?>
