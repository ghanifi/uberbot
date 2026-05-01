<?php
/**
 * Airlinel Booking Analytics Tracker
 * Task 4: Tracks booking funnel from search to payment completion
 */

if (!defined('ABSPATH')) {
    exit;
}

class Airlinel_Booking_Analytics_Tracker {
    /**
     * Database table name
     */
    private $table_name;

    /**
     * WordPress database object
     */
    private $wpdb;

    /**
     * Constructor - Initialize table name
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'airlinel_booking_searches';
    }

    /**
     * Create the booking searches table
     */
    public function create_table() {
        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            stage VARCHAR(50) NOT NULL,
            pickup VARCHAR(255),
            dropoff VARCHAR(255),
            distance FLOAT,
            duration VARCHAR(50),
            pickup_date DATE,
            pickup_time TIME,
            country VARCHAR(10),
            vehicle_name VARCHAR(255),
            vehicle_price DECIMAL(10, 2),
            customer_name VARCHAR(255),
            customer_phone VARCHAR(20),
            customer_email VARCHAR(255),
            flight_number VARCHAR(50),
            agency_code VARCHAR(50),
            notes TEXT,
            stripe_session_id VARCHAR(255),
            source_site VARCHAR(255),
            source_language VARCHAR(10),
            ip_address VARCHAR(45),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_stage (stage),
            INDEX idx_country (country),
            INDEX idx_created_at (created_at),
            INDEX idx_source_site (source_site),
            INDEX idx_customer_email (customer_email)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        return true;
    }

    /**
     * Log a search record
     */
    public function log_search($data) {
        // Validate and sanitize input
        $pickup = isset($data['pickup']) ? sanitize_text_field($data['pickup']) : '';
        $dropoff = isset($data['dropoff']) ? sanitize_text_field($data['dropoff']) : '';
        $distance = isset($data['distance']) ? floatval($data['distance']) : 0;
        $duration = isset($data['duration']) ? sanitize_text_field($data['duration']) : '';
        $pickup_date = isset($data['pickup_date']) ? sanitize_text_field($data['pickup_date']) : null;
        $pickup_time = isset($data['pickup_time']) ? sanitize_text_field($data['pickup_time']) : null;
        $country = isset($data['country']) ? sanitize_text_field($data['country']) : '';
        $source_site = isset($data['source_site']) ? sanitize_text_field($data['source_site']) : '';
        $source_language = isset($data['source_language']) ? sanitize_text_field($data['source_language']) : '';

        $ip_address = $this->get_client_ip();

        $result = $this->wpdb->insert(
            $this->table_name,
            array(
                'stage' => 'search',
                'pickup' => $pickup,
                'dropoff' => $dropoff,
                'distance' => $distance,
                'duration' => $duration,
                'pickup_date' => $pickup_date,
                'pickup_time' => $pickup_time,
                'country' => $country,
                'source_site' => $source_site,
                'source_language' => $source_language,
                'ip_address' => $ip_address,
                'created_at' => current_time('mysql'),
            ),
            array(
                '%s', // stage
                '%s', // pickup
                '%s', // dropoff
                '%f', // distance
                '%s', // duration
                '%s', // pickup_date
                '%s', // pickup_time
                '%s', // country
                '%s', // source_site
                '%s', // source_language
                '%s', // ip_address
                '%s', // created_at
            )
        );

        if ($result === false) {
            return new WP_Error('db_insert_error', 'Failed to insert search record: ' . $this->wpdb->last_error);
        }

        return $this->wpdb->insert_id;
    }

    /**
     * Log vehicle selection - update record and change stage
     */
    public function log_vehicle_selected($record_id, $data) {
        if (empty($record_id)) {
            return new WP_Error('invalid_record_id', 'Record ID is required');
        }

        $record_id = intval($record_id);
        $vehicle_name = isset($data['vehicle_name']) ? sanitize_text_field($data['vehicle_name']) : '';
        $vehicle_price = isset($data['vehicle_price']) ? floatval($data['vehicle_price']) : 0;

        $result = $this->wpdb->update(
            $this->table_name,
            array(
                'stage' => 'vehicle_selected',
                'vehicle_name' => $vehicle_name,
                'vehicle_price' => $vehicle_price,
                'updated_at' => current_time('mysql'),
            ),
            array('id' => $record_id),
            array(
                '%s', // stage
                '%s', // vehicle_name
                '%f', // vehicle_price
                '%s', // updated_at
            ),
            array('%d') // id
        );

        if ($result === false) {
            return new WP_Error('db_update_error', 'Failed to update vehicle selection: ' . $this->wpdb->last_error);
        }

        return true;
    }

    /**
     * Log customer form information - update record and change stage
     */
    public function log_customer_info($record_id, $data) {
        if (empty($record_id)) {
            return new WP_Error('invalid_record_id', 'Record ID is required');
        }

        $record_id = intval($record_id);
        $customer_name = isset($data['customer_name']) ? sanitize_text_field($data['customer_name']) : '';
        $customer_phone = isset($data['customer_phone']) ? sanitize_text_field($data['customer_phone']) : '';
        $customer_email = isset($data['customer_email']) ? sanitize_email($data['customer_email']) : '';
        $flight_number = isset($data['flight_number']) ? sanitize_text_field($data['flight_number']) : '';
        $agency_code = isset($data['agency_code']) ? sanitize_text_field($data['agency_code']) : '';
        $notes = isset($data['notes']) ? sanitize_textarea_field($data['notes']) : '';

        $result = $this->wpdb->update(
            $this->table_name,
            array(
                'stage' => 'form_filled',
                'customer_name' => $customer_name,
                'customer_phone' => $customer_phone,
                'customer_email' => $customer_email,
                'flight_number' => $flight_number,
                'agency_code' => $agency_code,
                'notes' => $notes,
                'updated_at' => current_time('mysql'),
            ),
            array('id' => $record_id),
            array(
                '%s', // stage
                '%s', // customer_name
                '%s', // customer_phone
                '%s', // customer_email
                '%s', // flight_number
                '%s', // agency_code
                '%s', // notes
                '%s', // updated_at
            ),
            array('%d') // id
        );

        if ($result === false) {
            return new WP_Error('db_update_error', 'Failed to update customer info: ' . $this->wpdb->last_error);
        }

        return true;
    }

    /**
     * Log payment completion - update record and change stage
     */
    public function log_payment_complete($record_id, $data) {
        if (empty($record_id)) {
            return new WP_Error('invalid_record_id', 'Record ID is required');
        }

        $record_id = intval($record_id);
        $stripe_session_id = isset($data['stripe_session_id']) ? sanitize_text_field($data['stripe_session_id']) : '';

        $result = $this->wpdb->update(
            $this->table_name,
            array(
                'stage' => 'payment_complete',
                'stripe_session_id' => $stripe_session_id,
                'updated_at' => current_time('mysql'),
            ),
            array('id' => $record_id),
            array(
                '%s', // stage
                '%s', // stripe_session_id
                '%s', // updated_at
            ),
            array('%d') // id
        );

        if ($result === false) {
            return new WP_Error('db_update_error', 'Failed to update payment: ' . $this->wpdb->last_error);
        }

        return true;
    }

    /**
     * Get conversion funnel statistics
     */
    public function get_funnel_stats($start_date, $end_date) {
        // Sanitize dates
        $start_date = sanitize_text_field($start_date);
        $end_date = sanitize_text_field($end_date);

        // Query counts for each stage
        $total_searches = intval($this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE stage = 'search' AND created_at >= %s AND created_at < DATE_ADD(%s, INTERVAL 1 DAY)",
            $start_date,
            $end_date
        )));

        $vehicle_selected = intval($this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE stage = 'vehicle_selected' AND created_at >= %s AND created_at < DATE_ADD(%s, INTERVAL 1 DAY)",
            $start_date,
            $end_date
        )));

        $form_filled = intval($this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE stage = 'form_filled' AND created_at >= %s AND created_at < DATE_ADD(%s, INTERVAL 1 DAY)",
            $start_date,
            $end_date
        )));

        $payment_complete = intval($this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE stage = 'payment_complete' AND created_at >= %s AND created_at < DATE_ADD(%s, INTERVAL 1 DAY)",
            $start_date,
            $end_date
        )));

        // Calculate conversion rate
        $conversion_rate = 0;
        if ($total_searches > 0) {
            $conversion_rate = round(($payment_complete / $total_searches) * 100, 2);
        }

        return array(
            'total_searches' => $total_searches,
            'vehicle_selected' => $vehicle_selected,
            'form_filled' => $form_filled,
            'payment_complete' => $payment_complete,
            'conversion_rate' => $conversion_rate,
        );
    }

    /**
     * Extract client IP address, handling proxies
     */
    public function get_client_ip() {
        // Check for Cloudflare
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return sanitize_text_field($_SERVER['HTTP_CF_CONNECTING_IP']);
        }

        // Check for X-Forwarded-For (proxy)
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
            return sanitize_text_field($ip);
        }

        // Check for X-Forwarded-Proto
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            return sanitize_text_field($_SERVER['HTTP_X_FORWARDED_PROTO']);
        }

        // Fall back to REMOTE_ADDR
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            return sanitize_text_field($_SERVER['REMOTE_ADDR']);
        }

        return '';
    }
}
