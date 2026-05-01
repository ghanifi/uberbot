<?php
/**
 * Airlinel Main Site Client
 * Provides a client interface for regional sites to call main site API endpoints
 * Implements sanitization, SSL verification, and comprehensive error handling
 */
class Airlinel_Main_Site_Client {
    private $main_site_url;
    private $api_key;
    private $request_timeout = 30; // seconds

    /**
     * Constructor - Initialize with main site URL and API key
     * Reads from database options first (Regional Settings Manager), then falls back to constants (legacy)
     */
    public function __construct() {
        // Try database options first (Regional Settings Manager)
        if (class_exists('Airlinel_Regional_Settings_Manager')) {
            $settings_mgr = new Airlinel_Regional_Settings_Manager();
            $this->main_site_url = $settings_mgr->get_main_site_url();
            $this->api_key = $settings_mgr->get_api_key();
        }

        // Fall back to constants if options not set (legacy configuration)
        if (empty($this->main_site_url) && defined('AIRLINEL_MAIN_SITE_URL')) {
            $this->main_site_url = AIRLINEL_MAIN_SITE_URL;
        }

        if (empty($this->api_key) && defined('AIRLINEL_MAIN_SITE_API_KEY')) {
            $this->api_key = AIRLINEL_MAIN_SITE_API_KEY;
        }

        // Log if not configured
        if (empty($this->main_site_url)) {
            error_log('[Airlinel] MainSiteClient: Main site URL not configured in options or constants');
        }

        if (empty($this->api_key)) {
            error_log('[Airlinel] MainSiteClient: API key not configured in options or constants');
        }
    }

    /**
     * Call /search endpoint on main site
     * @param string $pickup Pickup location
     * @param string $dropoff Dropoff location
     * @param string $country Country code (UK, TR)
     * @param int $passengers Number of passengers
     * @param string $currency Currency code
     * @return array|WP_Error API response or error
     */
    public function search($pickup, $dropoff, $country, $passengers = 1, $currency = 'GBP') {
        // Validate and sanitize inputs
        $pickup = $this->sanitize_location($pickup);
        if (is_wp_error($pickup)) {
            return $pickup;
        }

        $dropoff = $this->sanitize_location($dropoff);
        if (is_wp_error($dropoff)) {
            return $dropoff;
        }

        $country = $this->sanitize_country($country);
        if (is_wp_error($country)) {
            return $country;
        }

        $passengers = $this->sanitize_passengers($passengers);
        if (is_wp_error($passengers)) {
            return $passengers;
        }

        $currency = $this->sanitize_currency($currency);
        if (is_wp_error($currency)) {
            return $currency;
        }

        $request_body = array(
            'pickup' => $pickup,
            'dropoff' => $dropoff,
            'country' => $country,
            'passengers' => $passengers,
            'currency' => $currency,
        );

        $response = $this->send_request('/search', 'POST', $request_body);

        if (is_wp_error($response)) {
            error_log('[Airlinel] MainSiteClient search error: ' . $response->get_error_message());
            return $response;
        }

        // Validate response structure
        if (!isset($response['vehicles']) || !is_array($response['vehicles'])) {
            error_log('[Airlinel] MainSiteClient: Invalid search response - missing vehicles array');
            return new WP_Error('invalid_response', 'Invalid response format from main site');
        }

        return $response;
    }

    /**
     * Call /reservation/create endpoint on main site
     * @param array $data Reservation data
     * @return array|WP_Error API response or error
     */
    public function create_reservation($data) {
        if (!is_array($data)) {
            return new WP_Error('invalid_data', 'Data must be an array');
        }

        // Validate required fields
        if (empty($data['customer_name'])) {
            return new WP_Error('invalid_customer_name', 'Customer name is required');
        }

        if (empty($data['email']) || !is_email($data['email'])) {
            return new WP_Error('invalid_email', 'Valid email is required');
        }

        // Sanitize all data before sending
        $sanitized = array(
            'customer_name' => sanitize_text_field($data['customer_name']),
            'email' => sanitize_email($data['email']),
            'phone' => isset($data['phone']) ? sanitize_text_field($data['phone']) : '',
            'pickup' => isset($data['pickup']) ? sanitize_text_field($data['pickup']) : '',
            'dropoff' => isset($data['dropoff']) ? sanitize_text_field($data['dropoff']) : '',
            'date' => isset($data['date']) ? sanitize_text_field($data['date']) : '',
            'passengers' => isset($data['passengers']) ? intval($data['passengers']) : 1,
            'total_price' => isset($data['total_price']) ? floatval($data['total_price']) : 0,
            'currency' => isset($data['currency']) ? sanitize_text_field($data['currency']) : 'GBP',
            'country' => isset($data['country']) ? sanitize_text_field($data['country']) : 'UK',
        );

        $response = $this->send_request('/reservation/create', 'POST', $sanitized);

        if (is_wp_error($response)) {
            error_log('[Airlinel] MainSiteClient create_reservation error: ' . $response->get_error_message());
            return $response;
        }

        return $response;
    }

    /**
     * Call /reservation/{id} endpoint on main site
     * @param int $id Reservation ID
     * @return array|WP_Error API response or error
     */
    public function get_reservation($id) {
        $id = intval($id);
        if ($id <= 0) {
            return new WP_Error('invalid_id', 'Reservation ID must be a positive integer');
        }

        $response = $this->send_request('/reservation/' . $id, 'GET', array());

        if (is_wp_error($response)) {
            error_log('[Airlinel] MainSiteClient get_reservation error: ' . $response->get_error_message());
            return $response;
        }

        return $response;
    }

    /**
     * Check if main site is reachable and healthy
     * @return bool True if main site is reachable
     */
    public function get_health() {
        if (empty($this->main_site_url) || empty($this->api_key)) {
            return false;
        }

        $url = rtrim($this->main_site_url, '/') . '/wp-json/airlinel/v1/search';

        $args = array(
            'method' => 'POST',
            'timeout' => $this->request_timeout,
            'headers' => array(
                'Content-Type' => 'application/json',
                'x-api-key' => $this->api_key,
            ),
            'sslverify' => true,
            'body' => wp_json_encode(array(
                'pickup' => 'test',
                'dropoff' => 'test',
                'country' => 'UK',
                'passengers' => 1,
                'currency' => 'GBP',
            )),
        );

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            return false;
        }

        $http_code = wp_remote_retrieve_response_code($response);
        return ($http_code >= 200 && $http_code < 400) || $http_code === 400; // 400 is OK for health check (bad test data is expected)
    }

    /**
     * Send HTTP request to main site
     * @param string $endpoint API endpoint (e.g., '/search')
     * @param string $method HTTP method (GET, POST)
     * @param array $body Request body (for POST)
     * @return array|WP_Error Decoded response or error
     */
    private function send_request($endpoint, $method, $body) {
        if (empty($this->main_site_url) || empty($this->api_key)) {
            return new WP_Error('config_error', 'Main site client not properly configured');
        }

        $url = rtrim($this->main_site_url, '/') . '/wp-json/airlinel/v1' . $endpoint;

        $args = array(
            'method' => $method,
            'timeout' => $this->request_timeout,
            'headers' => array(
                'Content-Type' => 'application/json',
                'x-api-key' => $this->api_key,
            ),
            'sslverify' => true, // Always verify SSL in production
        );

        if ($method === 'POST') {
            $args['body'] = wp_json_encode($body);
        }

        // Send request
        $response = wp_remote_request($url, $args);

        // Check for network errors
        if (is_wp_error($response)) {
            error_log('[Airlinel] MainSiteClient network error: ' . $response->get_error_message());
            return $response;
        }

        // Check HTTP status code
        $http_code = wp_remote_retrieve_response_code($response);
        if ($http_code >= 400) {
            $error_body = wp_remote_retrieve_body($response);
            error_log('[Airlinel] MainSiteClient HTTP error ' . $http_code . ': ' . $error_body);
            return new WP_Error(
                'http_error',
                'Main site error: HTTP ' . $http_code,
                array('status' => $http_code)
            );
        }

        // Parse JSON response
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('[Airlinel] MainSiteClient JSON decode error: ' . json_last_error_msg());
            return new WP_Error('json_error', 'Invalid JSON response from main site');
        }

        if (!is_array($data)) {
            error_log('[Airlinel] MainSiteClient: Response is not an array: ' . $body);
            return new WP_Error('invalid_response', 'Main site returned non-array response');
        }

        return $data;
    }

    /**
     * Sanitize location parameter
     * @param mixed $location Location value
     * @return string|WP_Error Sanitized location or error
     */
    private function sanitize_location($location) {
        if (!is_string($location) || empty(trim($location))) {
            return new WP_Error('invalid_location', 'Location must be a non-empty string');
        }
        return sanitize_text_field($location);
    }

    /**
     * Sanitize country parameter
     * @param mixed $country Country value
     * @return string|WP_Error Sanitized country or error
     */
    private function sanitize_country($country) {
        $country = sanitize_text_field($country);
        if (!in_array($country, array('UK', 'TR'), true)) {
            return new WP_Error('invalid_country', 'Country must be UK or TR');
        }
        return $country;
    }

    /**
     * Sanitize passengers parameter
     * @param mixed $passengers Passengers value
     * @return int|WP_Error Sanitized passengers or error
     */
    private function sanitize_passengers($passengers) {
        $passengers = intval($passengers);
        if ($passengers < 1 || $passengers > 20) {
            return new WP_Error('invalid_passengers', 'Passengers must be between 1 and 20');
        }
        return $passengers;
    }

    /**
     * Sanitize currency parameter
     * @param mixed $currency Currency value
     * @return string|WP_Error Sanitized currency or error
     */
    private function sanitize_currency($currency) {
        $currency = sanitize_text_field($currency);
        if (!in_array($currency, array('GBP', 'EUR', 'TRY', 'USD'), true)) {
            return new WP_Error('invalid_currency', 'Currency must be GBP, EUR, TRY, or USD');
        }
        return $currency;
    }
}
?>
