<?php
/**
 * Airlinel Analytics Tracker
 * Tracks booking form fills and field changes in real-time
 */
class Airlinel_Analytics_Tracker {

    /**
     * Log initial booking form creation (called when vehicle selected)
     */
    public static function log_form_start($pickup, $dropoff, $distance, $country, $language, $site_source, $session_id = '', $website_id = '', $website_language = '') {
        global $wpdb;

        $table = $wpdb->prefix . 'booking_form_analytics';

        $data = array(
            'pickup' => sanitize_text_field($pickup),
            'dropoff' => sanitize_text_field($dropoff),
            'distance_km' => floatval($distance),
            'country' => sanitize_text_field($country),
            'language' => sanitize_text_field($language),
            'form_stage' => 'vehicle_selection',
            'site_source' => sanitize_text_field($site_source),
            'session_id' => sanitize_text_field($session_id),
            'website_id' => sanitize_text_field($website_id),
            'website_language' => sanitize_text_field($website_language),
            'site_url' => home_url(),
            'ip_address' => self::get_client_ip(),
            'created_at' => current_time('mysql'),
        );

        $wpdb->insert($table, $data);
        $form_id = $wpdb->insert_id;

        error_log('[Airlinel Analytics] Form started: ID=' . $form_id . ' for ' . $country . ', session_id=' . $session_id);

        return $form_id;
    }

    /**
     * Log individual form field change
     */
    public static function log_field_change($form_id, $field_name, $field_value, $session_id = '') {
        global $wpdb;

        if (intval($form_id) <= 0) {
            error_log('[Airlinel Analytics] Invalid form_id for field_change: ' . $form_id);
            return false;
        }

        $table = $wpdb->prefix . 'booking_form_field_changes';

        $data = array(
            'form_id' => intval($form_id),
            'field_name' => sanitize_text_field($field_name),
            'field_value' => sanitize_text_field($field_value),
            'session_id' => sanitize_text_field($session_id),
            'ip_address' => self::get_client_ip(),
        );

        $result = $wpdb->insert($table, $data);

        if ($result === false) {
            error_log('[Airlinel Analytics] Error logging field change: ' . $wpdb->last_error);
            return false;
        }

        error_log('[Airlinel Analytics] Field changed - FormID=' . $form_id . ', Field=' . $field_name . ', session_id=' . $session_id);

        return true;
    }

    /**
     * Update form with customer data (called when customer fills name/email/phone)
     */
    public static function update_form_with_customer_data($form_id, $customer_name, $customer_email, $customer_phone, $session_id = '') {
        global $wpdb;

        if (intval($form_id) <= 0) {
            error_log('[Airlinel Analytics] Invalid form_id for customer update: ' . $form_id);
            return false;
        }

        $table = $wpdb->prefix . 'booking_form_analytics';

        $data = array(
            'customer_name' => sanitize_text_field($customer_name),
            'customer_email' => sanitize_email($customer_email),
            'customer_phone' => sanitize_text_field($customer_phone),
            'form_stage' => 'customer_info',
            'session_id' => sanitize_text_field($session_id),
            'updated_at' => current_time('mysql'),
        );

        $where = array('id' => intval($form_id));

        $result = $wpdb->update($table, $data, $where);

        if ($result === false) {
            error_log('[Airlinel Analytics] Error updating customer data: ' . $wpdb->last_error);
            return false;
        }

        error_log('[Airlinel Analytics] Customer data updated - FormID=' . $form_id . ', Email=' . $customer_email . ', session_id=' . $session_id);

        return true;
    }

    /**
     * Update form with vehicle selection data
     */
    public static function update_form_with_vehicle($form_id, $vehicle_id, $vehicle_name, $vehicle_price, $session_id = '') {
        global $wpdb;

        if (intval($form_id) <= 0) {
            error_log('[Airlinel Analytics] Invalid form_id for vehicle update: ' . $form_id);
            return false;
        }

        $table = $wpdb->prefix . 'booking_form_analytics';

        $data = array(
            'vehicle_id' => intval($vehicle_id),
            'vehicle_name' => sanitize_text_field($vehicle_name),
            'vehicle_price' => sanitize_text_field($vehicle_price),
            'form_stage' => 'booking_details',
            'session_id' => sanitize_text_field($session_id),
            'updated_at' => current_time('mysql'),
        );

        $where = array('id' => intval($form_id));

        $result = $wpdb->update($table, $data, $where);

        if ($result === false) {
            error_log('[Airlinel Analytics] Error updating vehicle: ' . $wpdb->last_error);
            return false;
        }

        error_log('[Airlinel Analytics] Vehicle updated - FormID=' . $form_id . ', Vehicle=' . $vehicle_name . ', Price=' . $vehicle_price . ', session_id=' . $session_id);

        return true;
    }

    /**
     * Mark form as completed (called when payment form submitted)
     */
    public static function mark_form_completed($form_id, $session_id = '') {
        global $wpdb;

        if (intval($form_id) <= 0) {
            error_log('[Airlinel Analytics] Invalid form_id for completion: ' . $form_id);
            return false;
        }

        $table = $wpdb->prefix . 'booking_form_analytics';

        $data = array(
            'form_stage' => 'completed',
            'session_id' => sanitize_text_field($session_id),
            'updated_at' => current_time('mysql'),
        );

        $where = array('id' => intval($form_id));

        $result = $wpdb->update($table, $data, $where);

        if ($result === false) {
            error_log('[Airlinel Analytics] Error marking form completed: ' . $wpdb->last_error);
            return false;
        }

        error_log('[Airlinel Analytics] Form completed - FormID=' . $form_id . ', session_id=' . $session_id);

        return true;
    }

    /**
     * Get client IP address - handles CloudFlare, proxies, and direct connection
     */
    private static function get_client_ip() {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return sanitize_text_field($_SERVER['HTTP_CF_CONNECTING_IP']);
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Handle multiple IPs in proxy headers
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return sanitize_text_field(trim($ips[0]));
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED'])) {
            return sanitize_text_field($_SERVER['HTTP_X_FORWARDED']);
        } elseif (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            return sanitize_text_field($_SERVER['HTTP_FORWARDED_FOR']);
        } elseif (!empty($_SERVER['HTTP_FORWARDED'])) {
            return sanitize_text_field($_SERVER['HTTP_FORWARDED']);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            return sanitize_text_field($_SERVER['REMOTE_ADDR']);
        }
        return '0.0.0.0';
    }
}
?>
