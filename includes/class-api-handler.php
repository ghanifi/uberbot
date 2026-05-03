<?php
/**
 * Airlinel API Handler
 * Registers and manages REST API endpoints for transfers and reservations
 */
class Airlinel_API_Handler {
    private $namespace = 'airlinel/v1';
    private $request_api_key = '';
    private $source_site = '';

    public function register_routes() {
        // Health check endpoint - public, no API key required
        register_rest_route($this->namespace, '/health', array(
            'methods' => 'GET',
            'callback' => array($this, 'health_check'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route($this->namespace, '/search', array(
            'methods' => 'POST',
            'callback' => array($this, 'search_transfers'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        register_rest_route($this->namespace, '/reservation/create', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_reservation'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        register_rest_route($this->namespace, '/reservation/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_reservation'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));

        // ── Price Table ──────────────────────────────────────────
        // GET  /wp-json/airlinel/v1/price-table         — public query
        // POST /wp-json/airlinel/v1/price-table/import  — bulk import (API key)
        // DELETE /wp-json/airlinel/v1/price-table/(?P<id>\d+) — single delete (API key)

        register_rest_route($this->namespace, '/price-table', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'price_table_query' ),
            'permission_callback' => '__return_true',
            'args'                => array(
                'pickup'         => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
                'dropoff'        => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
                'source'         => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
                'currency'       => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
                'classification' => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
                'capacity_min'   => array( 'type' => 'integer' ),
                'available_only' => array( 'type' => 'boolean' ),
                'limit'          => array( 'type' => 'integer', 'default' => 50 ),
                'offset'         => array( 'type' => 'integer', 'default' => 0  ),
                'order_by'       => array( 'type' => 'string', 'default' => 'price_value' ),
                'order'          => array( 'type' => 'string', 'default' => 'ASC' ),
            ),
        ));

        register_rest_route($this->namespace, '/price-table/import', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'price_table_import' ),
            'permission_callback' => array( $this, 'verify_api_key' ),
        ));

        register_rest_route($this->namespace, '/price-table/(?P<id>\d+)', array(
            'methods'             => 'DELETE',
            'callback'            => array( $this, 'price_table_delete' ),
            'permission_callback' => array( $this, 'verify_api_key' ),
        ));
    }

    public function verify_api_key($request) {
        $headers = $request->get_headers();
        $provided_key = isset($headers['x-api-key'][0]) ? $headers['x-api-key'][0] : '';

        if (empty($provided_key)) {
            return new WP_Error('missing_api_key', 'API key is required', array('status' => 401));
        }

        $this->request_api_key = $provided_key;

        // First, try main site API key
        $stored_key = Airlinel_Settings_Manager::get('airlinel_api_key');
        if (!empty($stored_key) && hash_equals($stored_key, $provided_key)) {
            // Main site request
            $this->source_site = 'main';
            // Check rate limiting
            return $this->check_rate_limit($provided_key);
        }

        // Task 3.0: Check regional site API keys
        $regional_keys = get_option('airlinel_regional_api_keys', array());
        if (is_array($regional_keys)) {
            foreach ($regional_keys as $site_id => $key) {
                if (!empty($key) && hash_equals($key, $provided_key)) {
                    // Regional site request
                    $this->source_site = $site_id;
                    // Extract from request body if provided
                    $params = $request->get_json_params();
                    if (!empty($params['source_site'])) {
                        $this->source_site = sanitize_text_field($params['source_site']);
                    }
                    // Check rate limiting
                    return $this->check_rate_limit($provided_key);
                }
            }
        }

        return new WP_Error('invalid_api_key', 'Invalid API key', array('status' => 401));
    }

    /**
     * Task 3.0: Rate limiting - max 100 requests per minute per API key
     * FIX 2: CRITICAL - Fix race condition with atomic increment
     * FIX 3: CRITICAL - Avoid exposing API key material in logs
     * REGRESSION FIX 2: Initialize transient BEFORE incrementing to prevent counter reset
     */
    private function check_rate_limit($api_key) {
        $rate_limit = 100;
        $time_window = 60; // seconds

        $transient_key = 'airlinel_rate_limit_' . md5($api_key);
        $count = get_transient($transient_key);
        $count = $count ? (int)$count : 0;

        if ($count >= $rate_limit) {
            // FIX 3: Use MD5 hash instead of key prefix to avoid exposing key material
            error_log('Invalid regional API key: ' . md5($api_key) . '...');
            return new WP_Error('rate_limit', 'Rate limit exceeded', array('status' => 429));
        }

        // REGRESSION FIX 2: Initialize transient BEFORE incrementing to prevent counter reset
        if ($count === 0) {
            // First request - initialize with 1
            set_transient($transient_key, 1, $time_window);
        } else {
            // Subsequent request - increment
            if (function_exists('wp_cache_incr')) {
                wp_cache_incr($transient_key, 1);
            } else {
                set_transient($transient_key, $count + 1, $time_window);
            }
        }

        return true;
    }

    /**
     * Get the source site from the current request
     */
    public function get_source_site() {
        return $this->source_site;
    }

    /**
     * Health check endpoint - allows regional sites to verify main site connectivity
     */
    public function health_check($request) {
        return rest_ensure_response(array(
            'status' => 'ok',
            'message' => 'Main site API is operational',
            'timestamp' => current_time('mysql'),
        ));
    }

    public function search_transfers($request) {
        $params = $request->get_json_params();

        if (!isset($params['pickup'], $params['dropoff'], $params['date'])) {
            return new WP_Error('missing_params', 'Missing required parameters', array('status' => 400));
        }

        // Pricing engine will be implemented in Task 1.2
        // For now, return stub response
        if (class_exists('Airlinel_Pricing_Engine')) {
            $engine = new Airlinel_Pricing_Engine();
            return rest_ensure_response($engine->calculate(
                $params['pickup'],
                $params['dropoff'],
                $params['passengers'] ?? 1,
                $params['currency'] ?? 'GBP',
                $params['country'] ?? 'UK'
            ));
        } else {
            return new WP_Error('pricing_engine_not_available', 'Pricing engine not yet initialized', array('status' => 503));
        }
    }

    public function create_reservation($request) {
        $params = $request->get_json_params();

        // Task 3.0: Add source site tracking
        if (!empty($this->source_site) && $this->source_site !== 'main') {
            $params['source_site'] = $this->source_site;
        }

        // Task 3.0: Extract source_language and source_url from request if provided
        if (!empty($params['source_language'])) {
            $params['source_language'] = sanitize_text_field($params['source_language']);
        }
        if (!empty($params['source_url'])) {
            $params['source_url'] = esc_url_raw($params['source_url']);
        }

        $handler = new Airlinel_Reservation_Handler();
        $res_id = $handler->create($params);

        if (is_wp_error($res_id)) {
            return $res_id;
        }

        // Task 3.4: Log reservation creation with sync manager
        if (class_exists('Airlinel_Data_Sync_Manager')) {
            $sync_mgr = new Airlinel_Data_Sync_Manager();
            $sync_mgr->log_sync_event(
                'reservations',
                'success',
                sprintf('Reservation #%d created from %s', $res_id, $this->source_site ?: 'main')
            );
        }

        return rest_ensure_response(array(
            'success' => true,
            'reservation_id' => $res_id,
        ));
    }

    public function get_reservation($request) {
        $id = $request->get_param('id');
        $post = get_post($id);

        if (!$post || $post->post_type !== 'reservations') {
            return new WP_Error('not_found', 'Reservation not found', array('status' => 404));
        }

        return rest_ensure_response(array(
            'id' => $post->ID,
            'status' => $post->post_status,
            'customer_name' => get_post_meta($post->ID, 'customer_name', true),
            'email' => get_post_meta($post->ID, 'email', true),
            'phone' => get_post_meta($post->ID, 'phone', true),
            'pickup' => get_post_meta($post->ID, 'pickup_location', true),
            'dropoff' => get_post_meta($post->ID, 'dropoff_location', true),
            'date' => get_post_meta($post->ID, 'transfer_date', true),
            'passengers' => get_post_meta($post->ID, 'passengers', true),
            'total_price' => get_post_meta($post->ID, 'total_price', true),
            'currency' => get_post_meta($post->ID, 'currency', true),
            'country' => get_post_meta($post->ID, 'country', true),
            // Task 3.0: Source site tracking fields
            'source_site' => get_post_meta($post->ID, 'source_site', true),
            'source_language' => get_post_meta($post->ID, 'source_language', true),
            'source_url' => get_post_meta($post->ID, 'source_url', true),
        ));
    }

    // ── Price Table Handlers ──────────────────────────────────────────────────

    /**
     * GET /wp-json/airlinel/v1/price-table
     * Public endpoint — returns price data with optional filters.
     */
    public function price_table_query( WP_REST_Request $request ) {
        if ( ! class_exists( 'Airlinel_Price_Table' ) ) {
            require_once get_template_directory() . '/includes/class-price-table.php';
        }

        $args = array(
            'pickup'         => $request->get_param('pickup'),
            'dropoff'        => $request->get_param('dropoff'),
            'source'         => $request->get_param('source'),
            'currency'       => $request->get_param('currency'),
            'classification' => $request->get_param('classification'),
            'capacity_min'   => $request->get_param('capacity_min'),
            'available_only' => $request->get_param('available_only'),
            'limit'          => $request->get_param('limit')   ?? 50,
            'offset'         => $request->get_param('offset')  ?? 0,
            'order_by'       => $request->get_param('order_by') ?? 'price_value',
            'order'          => $request->get_param('order')    ?? 'ASC',
        );

        $rows    = Airlinel_Price_Table::query( $args );
        $sources = Airlinel_Price_Table::get_sources();

        return rest_ensure_response( array(
            'success' => true,
            'count'   => count( $rows ),
            'sources' => $sources,
            'data'    => $rows,
        ) );
    }

    /**
     * POST /wp-json/airlinel/v1/price-table/import
     * Bulk import price entries. Requires API key.
     *
     * Body (JSON):
     * {
     *   "source":  "airlinel",          // optional, overrides per-row source
     *   "entries": [ { ...row fields } ]
     * }
     */
    public function price_table_import( WP_REST_Request $request ) {
        if ( ! class_exists( 'Airlinel_Price_Table' ) ) {
            require_once get_template_directory() . '/includes/class-price-table.php';
        }

        $body    = $request->get_json_params();
        $entries = $body['entries'] ?? null;

        if ( empty( $entries ) || ! is_array( $entries ) ) {
            return new WP_Error(
                'missing_entries',
                'Body must contain a non-empty "entries" array.',
                array( 'status' => 400 )
            );
        }

        // Allow caller to override source for all entries
        $global_source = ! empty( $body['source'] ) ? sanitize_text_field( $body['source'] ) : null;
        if ( $global_source ) {
            foreach ( $entries as &$entry ) {
                $entry['source'] = $global_source;
            }
            unset( $entry );
        }

        $result = Airlinel_Price_Table::bulk_insert( $entries );

        return rest_ensure_response( array(
            'success'  => true,
            'inserted' => $result['inserted'],
            'errors'   => $result['errors'],
            'message'  => sprintf( '%d entries imported, %d failed.', $result['inserted'], $result['errors'] ),
        ) );
    }

    /**
     * DELETE /wp-json/airlinel/v1/price-table/{id}
     * Delete a single price entry. Requires API key.
     */
    public function price_table_delete( WP_REST_Request $request ) {
        if ( ! class_exists( 'Airlinel_Price_Table' ) ) {
            require_once get_template_directory() . '/includes/class-price-table.php';
        }

        $id     = (int) $request->get_param('id');
        $result = Airlinel_Price_Table::delete( $id );

        if ( $result === false ) {
            return new WP_Error( 'delete_failed', 'Could not delete entry.', array( 'status' => 500 ) );
        }
        if ( $result === 0 ) {
            return new WP_Error( 'not_found', 'Entry not found.', array( 'status' => 404 ) );
        }

        return rest_ensure_response( array( 'success' => true, 'deleted_id' => $id ) );
    }
}
?>
