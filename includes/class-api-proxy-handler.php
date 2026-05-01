<?php
/**
 * Airlinel API Proxy Handler
 * Intercepts and proxies API requests from regional sites to the main site
 * Implements transparent proxy with caching and fallback strategies
 */
class Airlinel_API_Proxy_Handler {
    private $client;
    private $cache_ttl = 300; // 5 minutes

    /**
     * Constructor - Initialize proxy handler
     */
    public function __construct() {
        $this->client = new Airlinel_Main_Site_Client();
    }

    /**
     * Register AJAX endpoints for regional sites
     * These handle front-end requests transparently
     */
    public function register_ajax_routes() {
        // Search endpoint - no auth required
        add_action('wp_ajax_nopriv_airlinel_search', array($this, 'handle_search_request'));
        add_action('wp_ajax_airlinel_search', array($this, 'handle_search_request'));

        // Create reservation endpoint - no auth required
        add_action('wp_ajax_nopriv_airlinel_create_reservation', array($this, 'handle_create_reservation'));
        add_action('wp_ajax_airlinel_create_reservation', array($this, 'handle_create_reservation'));

        // Get reservation endpoint - no auth required
        add_action('wp_ajax_nopriv_airlinel_get_reservation', array($this, 'handle_get_reservation'));
        add_action('wp_ajax_airlinel_get_reservation', array($this, 'handle_get_reservation'));
    }

    /**
     * Register REST API routes for regional sites
     * These provide a programmatic interface
     */
    public function register_rest_routes() {
        register_rest_route('airlinel-proxy/v1', '/search', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_rest_search'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('airlinel-proxy/v1', '/reservation/create', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_rest_create_reservation'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('airlinel-proxy/v1', '/reservation/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_rest_get_reservation'),
            'permission_callback' => '__return_true',
        ));
    }

    /**
     * AJAX Handler - Search for transfers
     * Forwards to main site and caches result
     */
    public function handle_search_request() {
        check_ajax_referer('airlinel_nonce', 'nonce', false);

        $pickup = isset($_POST['pickup']) ? sanitize_text_field($_POST['pickup']) : '';
        $dropoff = isset($_POST['dropoff']) ? sanitize_text_field($_POST['dropoff']) : '';
        $country = isset($_POST['country']) ? sanitize_text_field($_POST['country']) : 'UK';
        $passengers = isset($_POST['passengers']) ? intval($_POST['passengers']) : 1;
        $currency = isset($_POST['currency']) ? sanitize_text_field($_POST['currency']) : 'GBP';

        if (empty($pickup) || empty($dropoff)) {
            wp_send_json_error(array(
                'message' => 'Pickup and dropoff locations are required',
            ));
        }

        // Try to get from cache first
        $cache_key = $this->get_cache_key('search', $pickup, $dropoff, $country);
        $cached = $this->get_cached_search($cache_key);

        if ($cached !== false) {
            error_log('[Airlinel] Proxy: Returning cached search results');
            wp_send_json_success($cached);
        }

        // Call main site client
        $response = $this->client->search($pickup, $dropoff, $country, $passengers, $currency);

        if (is_wp_error($response)) {
            error_log('[Airlinel] Proxy search error: ' . $response->get_error_message());

            // Try to fall back to cached data
            $cached = $this->get_cached_search($cache_key);
            if ($cached !== false) {
                error_log('[Airlinel] Proxy: Using cached data due to main site timeout');
                wp_send_json_success($cached);
            }

            wp_send_json_error(array(
                'message' => 'Transfer search service temporarily unavailable. Please try again.',
                'error' => $response->get_error_message(),
            ));
        }

        // Cache the result
        $this->cache_search_results($cache_key, $response);

        wp_send_json_success($response);
    }

    /**
     * AJAX Handler - Create reservation
     * Forwards to main site
     */
    public function handle_create_reservation() {
        check_ajax_referer('airlinel_nonce', 'nonce', false);

        $data = array(
            'customer_name' => isset($_POST['customer_name']) ? sanitize_text_field($_POST['customer_name']) : '',
            'email' => isset($_POST['email']) ? sanitize_email($_POST['email']) : '',
            'phone' => isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '',
            'pickup' => isset($_POST['pickup']) ? sanitize_text_field($_POST['pickup']) : '',
            'dropoff' => isset($_POST['dropoff']) ? sanitize_text_field($_POST['dropoff']) : '',
            'date' => isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '',
            'passengers' => isset($_POST['passengers']) ? intval($_POST['passengers']) : 1,
            'total_price' => isset($_POST['total_price']) ? floatval($_POST['total_price']) : 0,
            'currency' => isset($_POST['currency']) ? sanitize_text_field($_POST['currency']) : 'GBP',
            'country' => isset($_POST['country']) ? sanitize_text_field($_POST['country']) : 'UK',
        );

        $response = $this->client->create_reservation($data);

        if (is_wp_error($response)) {
            error_log('[Airlinel] Proxy create_reservation error: ' . $response->get_error_message());
            wp_send_json_error(array(
                'message' => 'Failed to create reservation. Please try again.',
                'error' => $response->get_error_message(),
            ));
        }

        wp_send_json_success($response);
    }

    /**
     * AJAX Handler - Get reservation
     * Forwards to main site
     */
    public function handle_get_reservation() {
        check_ajax_referer('airlinel_nonce', 'nonce', false);

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if ($id <= 0) {
            wp_send_json_error(array(
                'message' => 'Invalid reservation ID',
            ));
        }

        $response = $this->client->get_reservation($id);

        if (is_wp_error($response)) {
            error_log('[Airlinel] Proxy get_reservation error: ' . $response->get_error_message());
            wp_send_json_error(array(
                'message' => 'Failed to retrieve reservation. Please try again.',
                'error' => $response->get_error_message(),
            ));
        }

        wp_send_json_success($response);
    }

    /**
     * REST Handler - Search for transfers
     * Used by regional sites to fetch vehicles from main site
     */
    public function handle_rest_search($request) {
        // Try to get parameters from JSON body first
        $params = $request->get_json_params();

        // If JSON parsing failed, get from query string parameters
        if (empty($params)) {
            $query_params = $request->get_query_params();
            $params = array(
                'pickup' => isset($query_params['pickup']) ? $query_params['pickup'] : '',
                'dropoff' => isset($query_params['dropoff']) ? $query_params['dropoff'] : '',
                'country' => isset($query_params['country']) ? $query_params['country'] : 'UK',
                'passengers' => isset($query_params['passengers']) ? $query_params['passengers'] : 1,
                'currency' => isset($query_params['currency']) ? $query_params['currency'] : 'GBP',
            );
        }

        // Debug logging
        error_log('[Airlinel API Debug] Request method: ' . $request->get_method());
        error_log('[Airlinel API Debug] Request body: ' . $request->get_body());
        error_log('[Airlinel API Debug] Parsed params: ' . json_encode($params));

        $pickup = isset($params['pickup']) ? sanitize_text_field($params['pickup']) : '';
        $dropoff = isset($params['dropoff']) ? sanitize_text_field($params['dropoff']) : '';
        $distance = isset($params['distance']) ? floatval($params['distance']) : 0;
        $country = isset($params['country']) ? sanitize_text_field($params['country']) : 'UK';
        $passengers = isset($params['passengers']) ? intval($params['passengers']) : 1;
        $currency = isset($params['currency']) ? sanitize_text_field($params['currency']) : 'GBP';

        error_log('[Airlinel API Debug] Extracted - Pickup: ' . $pickup . ', Dropoff: ' . $dropoff . ', Country: ' . $country);

        if (empty($pickup) || empty($dropoff)) {
            error_log('[Airlinel API Debug] Missing required params. Pickup: ' . $pickup . ', Dropoff: ' . $dropoff);
            return new WP_Error('missing_params', 'Pickup and dropoff are required', array('status' => 400));
        }

        // Try cache first (unless nocache parameter set)
        $cache_key = $this->get_cache_key('search', $pickup, $dropoff, $country);
        $cached = $this->get_cached_search($cache_key);

        $nocache = isset($_GET['nocache']) || isset($_POST['nocache']);
        if ($cached !== false && !$nocache) {
            error_log('[Airlinel] Returning cached response');
            return rest_ensure_response($cached);
        }

        if ($nocache) {
            error_log('[Airlinel] Nocache mode: bypassing cache');
            // Also clear the cache for this key
            delete_transient($cache_key);
        }

        // For main site: return fleet vehicles with calculated prices
        // For regional sites: use Main Site Client proxy
        $response = null;

        // Check if this is the main site or a regional site
        if (class_exists('Airlinel_Pricing_Engine')) {
            // Main site: get fleet vehicles and calculate prices
            error_log('[Airlinel] Main site detected - fetching fleet vehicles');

            // Query fleet vehicles
            $args = array(
                'post_type' => 'fleet',
                'posts_per_page' => -1,
                'post_status' => 'publish',
            );

            $query = new WP_Query($args);
            $vehicles = array();

            if ($query->have_posts()) {
                // Get pricing engine to calculate prices
                $engine = new Airlinel_Pricing_Engine();
                // Use provided distance to ensure consistent pricing between regional and main site
                $pricing = $engine->calculate($pickup, $dropoff, $passengers, $currency, $country, $distance);

                if (isset($pricing['total_display'])) {
                    // Use the calculated total price for all vehicles as base
                    $base_total = $pricing['total_display'];

                    while ($query->have_posts()) {
                        $query->the_post();

                        $multiplier = floatval(get_post_meta(get_the_ID(), '_fleet_multiplier', true) ?: 1.0);
                        $passengers_capacity = intval(get_post_meta(get_the_ID(), '_fleet_passengers', true) ?: 4);
                        $luggage = intval(get_post_meta(get_the_ID(), '_fleet_luggage', true) ?: 3);

                        // Get vehicle image - try large first, then fallback to full
                        $image_url = '';
                        if (has_post_thumbnail(get_the_ID())) {
                            $image_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
                            if (empty($image_url)) {
                                $image_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
                            }
                        }

                        // Calculate price with multiplier
                        $final_price = $base_total * $multiplier;

                        $vehicles[] = array(
                            'post_id' => get_the_ID(),
                            'title' => get_the_title(),
                            'price' => round($final_price, 2),
                            'currency' => $currency,
                            'passengers' => $passengers_capacity,
                            'luggage' => $luggage,
                            'multiplier' => $multiplier,
                            'image_url' => $image_url,
                        );
                    }

                    // Extract language from request (default to 'en')
                    $language = isset($params['language']) ? sanitize_text_field($params['language']) : 'en';

                    // Get exchange rate from pricing result if available, else get from Exchange Rate Manager
                    $exchange_rate = 1;
                    if (isset($pricing['rate'])) {
                        $exchange_rate = floatval($pricing['rate']);
                    }

                    // Generate session tracking data
                    $session_id = $this->generate_session_id();
                    $website_id = $this->get_website_id();
                    $website_language = $this->get_website_language();

                    // Log search to analytics with session tracking
                    $this->log_search_to_analytics(
                        $pickup,
                        $dropoff,
                        $distance,
                        $country,
                        $currency,
                        count($vehicles),
                        $language,
                        $exchange_rate,
                        $session_id,
                        $website_id,
                        $website_language
                    );

                    $response = array(
                        'data' => $vehicles,
                        'pricing_info' => $pricing,
                        'session_id' => $session_id,
                    );

                    error_log('[Airlinel] Returning ' . count($vehicles) . ' fleet vehicles');
                } else {
                    error_log('[Airlinel] Pricing calculation failed: ' . json_encode($pricing));
                    return new WP_Error('pricing_error', 'Could not calculate pricing', array('status' => 400));
                }

                wp_reset_postdata();
            } else {
                error_log('[Airlinel] No fleet vehicles found');
                return new WP_Error('no_vehicles', 'No vehicles available', array('status' => 404));
            }
        } elseif ($this->client) {
            // Fallback: use Main Site Client for regional sites
            $response = $this->client->search($pickup, $dropoff, $country, $passengers, $currency);
        }

        if (is_wp_error($response)) {
            // Try to use cached data
            $cached = $this->get_cached_search($cache_key);
            if ($cached !== false) {
                error_log('[Airlinel] Proxy REST: Using cached data due to error');
                return rest_ensure_response($cached);
            }
            return $response;
        }

        // Cache result
        $this->cache_search_results($cache_key, $response);

        return rest_ensure_response($response);
    }

    /**
     * REST Handler - Create reservation
     */
    public function handle_rest_create_reservation($request) {
        $params = $request->get_json_params();

        $data = array(
            'customer_name' => isset($params['customer_name']) ? sanitize_text_field($params['customer_name']) : '',
            'email' => isset($params['email']) ? sanitize_email($params['email']) : '',
            'phone' => isset($params['phone']) ? sanitize_text_field($params['phone']) : '',
            'pickup' => isset($params['pickup']) ? sanitize_text_field($params['pickup']) : '',
            'dropoff' => isset($params['dropoff']) ? sanitize_text_field($params['dropoff']) : '',
            'date' => isset($params['date']) ? sanitize_text_field($params['date']) : '',
            'passengers' => isset($params['passengers']) ? intval($params['passengers']) : 1,
            'total_price' => isset($params['total_price']) ? floatval($params['total_price']) : 0,
            'currency' => isset($params['currency']) ? sanitize_text_field($params['currency']) : 'GBP',
            'country' => isset($params['country']) ? sanitize_text_field($params['country']) : 'UK',
        );

        $response = $this->client->create_reservation($data);

        if (is_wp_error($response)) {
            return $response;
        }

        return rest_ensure_response($response);
    }

    /**
     * REST Handler - Get reservation
     */
    public function handle_rest_get_reservation($request) {
        $id = $request->get_param('id');

        $response = $this->client->get_reservation($id);

        if (is_wp_error($response)) {
            return $response;
        }

        return rest_ensure_response($response);
    }

    /**
     * Get cached search results
     * @param string $cache_key Cache key
     * @return array|false Cached data or false if not found
     */
    public function get_cached_search($cache_key) {
        return get_transient($cache_key);
    }

    /**
     * Cache search results with 5-minute TTL
     * @param string $cache_key Cache key
     * @param array $data Data to cache
     */
    public function cache_search_results($cache_key, $data) {
        set_transient($cache_key, $data, $this->cache_ttl);
    }

    /**
     * Generate cache key from search parameters
     * @param string $endpoint Endpoint name
     * @param string $pickup Pickup location
     * @param string $dropoff Dropoff location
     * @param string $country Country code
     * @return string Cache key
     */
    private function get_cache_key($endpoint, $pickup, $dropoff, $country) {
        $cache_id = $endpoint . '_' . $pickup . '_' . $dropoff . '_' . $country;
        return 'airlinel_proxy_' . md5($cache_id);
    }

    /**
     * Generate UUID v4 format session ID
     * Used to track unique sessions across the booking lifecycle
     */
    private function generate_session_id() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Get website ID (regional site ID or main)
     * Identifies which site made the request
     */
    private function get_website_id() {
        // First check regional site ID
        $site_id = get_option('airlinel_regional_site_id');
        if (!empty($site_id)) {
            return sanitize_text_field($site_id);
        }

        // Fallback to main site
        return 'main';
    }

    /**
     * Get website language configuration
     * Returns the language configured on the current site
     */
    private function get_website_language() {
        // Get from WordPress option first
        $language = get_option('WPLANG');
        if (!empty($language)) {
            return sanitize_text_field($language);
        }

        // Fallback to constant
        if (defined('WPLANG') && !empty(WPLANG)) {
            return sanitize_text_field(WPLANG);
        }

        // Default to English
        return 'en';
    }

    /**
     * Log search to analytics database
     * Records vehicle search requests from regional sites to main site analytics
     * Tracks session, website, and language information for analytics
     */
    private function log_search_to_analytics($pickup, $dropoff, $distance, $country, $currency, $vehicle_count, $language = 'en', $exchange_rate = 1, $session_id = null, $website_id = null, $website_language = null) {
        global $wpdb;

        // Generate or use provided session tracking data
        if (empty($session_id)) {
            $session_id = $this->generate_session_id();
        }
        if (empty($website_id)) {
            $website_id = $this->get_website_id();
        }
        if (empty($website_language)) {
            $website_language = $this->get_website_language();
        }

        $table = $wpdb->prefix . 'booking_search_analytics';

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") != $table) {
            error_log('[Airlinel] Analytics table not found: ' . $table);
            return;
        }

        $data = array(
            'stage' => 'search',
            'pickup' => $pickup,
            'dropoff' => $dropoff,
            'distance_km' => floatval($distance),
            'country' => $country,
            'currency' => $currency,
            'vehicle_count' => intval($vehicle_count),
            'source' => 'regional_api',
            'language' => sanitize_text_field($language),
            'exchange_rate' => floatval($exchange_rate),
            'site_url' => home_url(),
            'timestamp' => current_time('mysql'),
            'ip_address' => $this->get_client_ip(),
            'session_id' => sanitize_text_field($session_id),
            'website_id' => sanitize_text_field($website_id),
            'website_language' => sanitize_text_field($website_language),
        );

        $wpdb->insert($table, $data);

        error_log('[Airlinel Analytics] Search logged: Session=' . $session_id . ', Website=' . $website_id . ', Language=' . $website_language . ', Rate=' . $exchange_rate);
    }

    /**
     * Get client IP address
     */
    private function get_client_ip() {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return sanitize_text_field($_SERVER['HTTP_CF_CONNECTING_IP']);
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
        } else {
            return sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
        }
    }

    /**
     * Check if main site is available
     * @return bool True if main site is healthy
     */
    public function is_main_site_available() {
        return $this->client->get_health();
    }
}
?>
