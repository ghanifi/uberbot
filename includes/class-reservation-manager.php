<?php
/**
 * Airlinel Reservation Manager
 * Manages reservations with filtering, searching, status updates, and CSV export
 */

class Airlinel_Reservation_Manager {

    /**
     * Get all reservations with filters and pagination
     *
     * @param array $args Query arguments: status, payment_status, country, date_from, date_to, search, page, per_page
     * @return array Array with 'reservations' and 'total' keys
     */
    public function get_reservations($args = array()) {
        $defaults = array(
            'status' => '',
            'payment_status' => '',
            'country' => '',
            'date_from' => '',
            'date_to' => '',
            'search' => '',
            'page' => 1,
            'per_page' => 20,
            'orderby' => 'meta_value',
            'order' => 'DESC',
        );

        $args = wp_parse_args($args, $defaults);

        // Validate pagination
        $page = intval($args['page']);
        $per_page = intval($args['per_page']);
        if ($page < 1) $page = 1;
        if ($per_page < 1 || $per_page > 100) $per_page = 20;

        $query_args = array(
            'post_type' => 'reservations',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'orderby' => 'meta_value',
            'meta_key' => 'transfer_date',
            'order' => 'DESC',
            'meta_query' => array(),
            's' => '',
        );

        // Handle search
        if (!empty($args['search'])) {
            $search = sanitize_text_field($args['search']);
            $query_args['s'] = $search;
        }

        // Build meta_query for filters
        $meta_query = array();

        // Status filter
        if (!empty($args['status'])) {
            $status = sanitize_text_field($args['status']);
            if (in_array($status, $this->get_statuses())) {
                $meta_query[] = array(
                    'key' => 'status',
                    'value' => $status,
                    'compare' => '=',
                );
            }
        }

        // Payment status filter
        if (!empty($args['payment_status'])) {
            $payment_status = sanitize_text_field($args['payment_status']);
            $valid_statuses = array('pending', 'completed', 'failed');
            if (in_array($payment_status, $valid_statuses)) {
                $meta_query[] = array(
                    'key' => 'payment_status',
                    'value' => $payment_status,
                    'compare' => '=',
                );
            }
        }

        // Country filter
        if (!empty($args['country'])) {
            $country = sanitize_text_field($args['country']);
            if (in_array($country, array('UK', 'TR'))) {
                $meta_query[] = array(
                    'key' => 'country',
                    'value' => $country,
                    'compare' => '=',
                );
            }
        }

        // Date range filter
        if (!empty($args['date_from'])) {
            $date_from = sanitize_text_field($args['date_from']);
            // Validate YYYY-MM-DD format
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from) && strtotime($date_from)) {
                $meta_query[] = array(
                    'key' => 'transfer_date',
                    'value' => $date_from,
                    'compare' => '>=',
                    'type' => 'DATE',
                );
            }
        }

        if (!empty($args['date_to'])) {
            $date_to = sanitize_text_field($args['date_to']);
            // Validate YYYY-MM-DD format
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to) && strtotime($date_to)) {
                $meta_query[] = array(
                    'key' => 'transfer_date',
                    'value' => $date_to,
                    'compare' => '<=',
                    'type' => 'DATE',
                );
            }
        }

        // Add meta_query if conditions exist
        if (!empty($meta_query)) {
            if (count($meta_query) > 1) {
                $meta_query['relation'] = 'AND';
            }
            $query_args['meta_query'] = $meta_query;
        }

        // Execute query
        $query = new WP_Query($query_args);

        // Build reservations array
        $reservations = array();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $reservations[] = $this->get_reservation(get_the_ID());
            }
        }
        wp_reset_postdata();

        return array(
            'reservations' => $reservations,
            'total' => (int) $query->found_posts,
            'pages' => (int) $query->max_num_pages,
            'page' => $page,
            'per_page' => $per_page,
        );
    }

    /**
     * Get single reservation with all details
     *
     * @param int $id Reservation post ID
     * @return array|false Reservation data or false
     */
    public function get_reservation($id) {
        $id = intval($id);
        $post = get_post($id);

        if (!$post || $post->post_type !== 'reservations') {
            return false;
        }

        return array(
            'id' => $post->ID,
            'customer_name' => get_post_meta($post->ID, 'customer_name', true),
            'email' => get_post_meta($post->ID, 'email', true),
            'phone' => get_post_meta($post->ID, 'phone', true),
            'pickup_location' => get_post_meta($post->ID, 'pickup_location', true),
            'dropoff_location' => get_post_meta($post->ID, 'dropoff_location', true),
            'transfer_date' => get_post_meta($post->ID, 'transfer_date', true),
            'passengers' => get_post_meta($post->ID, 'passengers', true),
            'currency' => get_post_meta($post->ID, 'currency', true),
            'country' => get_post_meta($post->ID, 'country', true),
            'fleet_id' => get_post_meta($post->ID, 'fleet_id', true),
            'base_price' => floatval(get_post_meta($post->ID, 'base_price', true)),
            'multiplier' => floatval(get_post_meta($post->ID, 'multiplier', true)),
            'total_price_gbp' => floatval(get_post_meta($post->ID, 'total_price_gbp', true)),
            'currency_display' => get_post_meta($post->ID, 'currency_display', true),
            'exchange_rate' => floatval(get_post_meta($post->ID, 'exchange_rate', true)),
            'payment_status' => get_post_meta($post->ID, 'payment_status', true),
            'stripe_intent_id' => get_post_meta($post->ID, 'stripe_intent_id', true),
            'stripe_charge_id' => get_post_meta($post->ID, 'stripe_charge_id', true),
            'agency_code' => get_post_meta($post->ID, 'agency_code', true),
            'commission_type' => get_post_meta($post->ID, 'commission_type', true),
            'agency_commission' => floatval(get_post_meta($post->ID, 'agency_commission', true)),
            'special_requests' => get_post_meta($post->ID, 'special_requests', true),
            'status' => get_post_meta($post->ID, 'status', true),
            'status_updated_at' => get_post_meta($post->ID, 'status_updated_at', true),
            'distance' => floatval(get_post_meta($post->ID, 'distance', true)),
        );
    }

    /**
     * Update reservation status
     *
     * @param int $id Reservation post ID
     * @param string $status New status
     * @return bool|WP_Error
     */
    public function update_reservation_status($id, $status) {
        $id = intval($id);
        $post = get_post($id);

        if (!$post || $post->post_type !== 'reservations') {
            return new WP_Error('invalid_reservation', 'Reservation not found');
        }

        $status = sanitize_text_field($status);
        if (!in_array($status, $this->get_statuses())) {
            return new WP_Error('invalid_status', 'Invalid status value');
        }

        update_post_meta($id, 'status', $status);
        update_post_meta($id, 'status_updated_at', current_time('mysql'));

        return true;
    }

    /**
     * Bulk update status for multiple reservations
     *
     * @param array $ids Array of reservation IDs
     * @param string $status New status
     * @return array Count of updated reservations
     */
    public function bulk_update_status($ids, $status) {
        $status = sanitize_text_field($status);
        if (!in_array($status, $this->get_statuses())) {
            return array('updated' => 0, 'error' => 'Invalid status');
        }

        $updated = 0;
        foreach ($ids as $id) {
            $id = intval($id);
            $result = $this->update_reservation_status($id, $status);
            if ($result !== false && !is_wp_error($result)) {
                $updated++;
            }
        }

        return array('updated' => $updated);
    }

    /**
     * Update reservation notes
     *
     * @param int $id Reservation post ID
     * @param string $notes Notes content
     * @return bool|WP_Error
     */
    public function update_notes($id, $notes) {
        $id = intval($id);
        $post = get_post($id);

        if (!$post || $post->post_type !== 'reservations') {
            return new WP_Error('invalid_reservation', 'Reservation not found');
        }

        $notes = sanitize_textarea_field($notes);
        update_post_meta($id, 'special_requests', $notes);

        return true;
    }

    /**
     * Get allowed reservation statuses
     *
     * @return array Status values
     */
    public function get_statuses() {
        return array('pending', 'processing', 'completed', 'cancelled');
    }

    /**
     * Export reservations to CSV format
     *
     * @param array $reservations Array of reservation data
     * @return string CSV content
     */
    public function export_csv($reservations) {
        if (empty($reservations)) {
            return '';
        }

        // CSV headers
        $headers = array(
            'ID',
            'Customer Name',
            'Email',
            'Phone',
            'Pickup Location',
            'Dropoff Location',
            'Transfer Date',
            'Total Price (GBP)',
            'Status',
            'Payment Status',
            'Agency Code',
        );

        $csv = implode(',', $headers) . "\n";

        // CSV rows
        foreach ($reservations as $res) {
            $row = array(
                $res['id'],
                $this->_csv_escape($res['customer_name']),
                $this->_csv_escape($res['email']),
                $this->_csv_escape($res['phone']),
                $this->_csv_escape($res['pickup_location']),
                $this->_csv_escape($res['dropoff_location']),
                !empty($res['transfer_date']) ? date('Y-m-d', strtotime($res['transfer_date'])) : '',
                number_format($res['total_price_gbp'], 2),
                $this->_csv_escape($res['status']),
                $this->_csv_escape($res['payment_status']),
                $this->_csv_escape($res['agency_code']),
            );
            $csv .= implode(',', $row) . "\n";
        }

        return $csv;
    }

    /**
     * Escape CSV field values
     *
     * @param string $value Field value
     * @return string Escaped value
     */
    private function _csv_escape($value) {
        $value = sanitize_text_field($value);
        if (strpos($value, ',') !== false || strpos($value, '"') !== false) {
            $value = '"' . str_replace('"', '""', $value) . '"';
        }
        return $value;
    }
}
?>
