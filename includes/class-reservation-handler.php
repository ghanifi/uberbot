<?php
/**
 * Airlinel Reservation Handler
 * Handles creation and management of reservations
 */
class Airlinel_Reservation_Handler {

    public function create($data) {
        $required = array('customer_name', 'email', 'phone', 'pickup_location', 'dropoff_location', 'transfer_date', 'total_price');
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return new WP_Error('validation', "Missing: $field");
            }
        }

        // Validate transfer_date format (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['transfer_date'])) {
            return new WP_Error('validation', 'Invalid transfer_date format (YYYY-MM-DD required)');
        }
        $transfer_time = strtotime($data['transfer_date']);
        if (!$transfer_time || $transfer_time < strtotime('today midnight')) {
            return new WP_Error('validation', 'transfer_date cannot be in the past');
        }

        // Validate email format
        if (!is_email($data['email'])) {
            return new WP_Error('validation', 'Invalid email address provided');
        }

        $post_id = wp_insert_post(array(
            'post_type' => 'reservations',
            'post_title' => sprintf('%s - %s → %s', $data['customer_name'], $data['pickup_location'], $data['dropoff_location']),
            'post_status' => 'pending',
        ));

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        // Validate and sanitize passengers count
        $passengers = intval($data['passengers'] ?? 1);
        if ($passengers < 1 || $passengers > 8) {
            return new WP_Error('validation', 'Passengers must be between 1 and 8');
        }

        // Validate and sanitize country code
        $country = sanitize_text_field($data['country'] ?? 'UK');
        if (!in_array($country, array('UK', 'TR'), true)) {
            return new WP_Error('validation', 'Invalid country code. Allowed: UK, TR');
        }

        // Validate total_price is a valid positive number
        $total_price = floatval($data['total_price']);
        if ($total_price <= 0) {
            return new WP_Error('validation', 'total_price must be greater than 0');
        }

        update_post_meta($post_id, 'customer_name', sanitize_text_field($data['customer_name']));
        update_post_meta($post_id, 'email', sanitize_email($data['email']));
        update_post_meta($post_id, 'phone', sanitize_text_field($data['phone']));
        update_post_meta($post_id, 'pickup_location', sanitize_text_field($data['pickup_location']));
        update_post_meta($post_id, 'dropoff_location', sanitize_text_field($data['dropoff_location']));
        update_post_meta($post_id, 'transfer_date', sanitize_text_field($data['transfer_date']));
        update_post_meta($post_id, 'passengers', $passengers);
        update_post_meta($post_id, 'currency', sanitize_text_field($data['currency'] ?? 'GBP'));
        update_post_meta($post_id, 'country', $country);
        update_post_meta($post_id, 'total_price', $total_price);

        // Task 3.0: Source site tracking for multi-site platform
        if (!empty($data['source_site'])) {
            update_post_meta($post_id, 'source_site', sanitize_text_field($data['source_site']));
        }
        if (!empty($data['source_language'])) {
            update_post_meta($post_id, 'source_language', sanitize_text_field($data['source_language']));
        }
        if (!empty($data['source_url'])) {
            update_post_meta($post_id, 'source_url', esc_url_raw($data['source_url']));
        }

        if (!empty($data['agency_code'])) {
            update_post_meta($post_id, 'agency_code', sanitize_text_field($data['agency_code']));
            update_post_meta($post_id, 'commission_type', sanitize_text_field($data['commission_type'] ?? 'included'));
        }

        return $post_id;
    }
}
?>
