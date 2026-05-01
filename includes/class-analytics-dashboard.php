<?php
/**
 * Airlinel Analytics Dashboard
 * Queries and filters analytics data for admin display
 */
class Airlinel_Analytics_Dashboard {

    /**
     * Get search analytics with optional filters
     *
     * Filters array can include:
     * - date_from: YYYY-MM-DD
     * - date_to: YYYY-MM-DD
     * - country: 'UK' or 'TR'
     * - language: 'en', 'tr', etc.
     * - site_source: 'regional_api', 'main_site'
     * - website_id: filter by website_id
     * - website_language: filter by website_language
     * - session_id: filter by session_id
     */
    public static function get_search_analytics($filters = array()) {
        global $wpdb;

        $table = $wpdb->prefix . 'booking_search_analytics';

        $query = "SELECT * FROM {$table} WHERE 1=1";
        $params = array();

        // Date range filter
        if (!empty($filters['date_from'])) {
            $query .= " AND timestamp >= %s";
            $params[] = sanitize_text_field($filters['date_from']) . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $query .= " AND timestamp <= %s";
            $params[] = sanitize_text_field($filters['date_to']) . ' 23:59:59';
        }

        // Country filter
        if (!empty($filters['country'])) {
            $query .= " AND country = %s";
            $params[] = sanitize_text_field($filters['country']);
        }

        // Site source filter
        if (!empty($filters['site_source'])) {
            $query .= " AND source = %s";
            $params[] = sanitize_text_field($filters['site_source']);
        }

        // Language filter
        if (!empty($filters['language'])) {
            $query .= " AND language = %s";
            $params[] = sanitize_text_field($filters['language']);
        }

        // Website ID filter
        if (!empty($filters['website_id'])) {
            $query .= " AND website_id = %s";
            $params[] = sanitize_text_field($filters['website_id']);
        }

        // Website Language filter
        if (!empty($filters['website_language'])) {
            $query .= " AND website_language = %s";
            $params[] = sanitize_text_field($filters['website_language']);
        }

        // Session ID filter
        if (!empty($filters['session_id'])) {
            $query .= " AND session_id = %s";
            $params[] = sanitize_text_field($filters['session_id']);
        }

        $query .= " ORDER BY timestamp DESC LIMIT 1000";

        if (!empty($params)) {
            return $wpdb->get_results($wpdb->prepare($query, $params));
        }

        return $wpdb->get_results($query);
    }

    /**
     * Get booking form analytics with optional filters
     *
     * Filters array can include:
     * - date_from: YYYY-MM-DD
     * - date_to: YYYY-MM-DD
     * - form_stage: 'vehicle_selection', 'customer_info', 'booking_details', 'completed'
     * - country: 'UK' or 'TR'
     * - language: 'en', 'tr', etc.
     * - site_source: 'regional', 'main'
     * - website_id: filter by website_id
     * - website_language: filter by website_language
     * - session_id: filter by session_id
     */
    public static function get_form_analytics($filters = array()) {
        global $wpdb;

        $table = $wpdb->prefix . 'booking_form_analytics';

        $query = "SELECT * FROM {$table} WHERE 1=1";
        $params = array();

        // Date range filter
        if (!empty($filters['date_from'])) {
            $query .= " AND created_at >= %s";
            $params[] = sanitize_text_field($filters['date_from']) . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $query .= " AND created_at <= %s";
            $params[] = sanitize_text_field($filters['date_to']) . ' 23:59:59';
        }

        // Form stage filter
        if (!empty($filters['form_stage'])) {
            $query .= " AND form_stage = %s";
            $params[] = sanitize_text_field($filters['form_stage']);
        }

        // Country filter
        if (!empty($filters['country'])) {
            $query .= " AND country = %s";
            $params[] = sanitize_text_field($filters['country']);
        }

        // Language filter
        if (!empty($filters['language'])) {
            $query .= " AND language = %s";
            $params[] = sanitize_text_field($filters['language']);
        }

        // Site source filter
        if (!empty($filters['site_source'])) {
            $query .= " AND site_source = %s";
            $params[] = sanitize_text_field($filters['site_source']);
        }

        // Website ID filter
        if (!empty($filters['website_id'])) {
            $query .= " AND website_id = %s";
            $params[] = sanitize_text_field($filters['website_id']);
        }

        // Website Language filter
        if (!empty($filters['website_language'])) {
            $query .= " AND website_language = %s";
            $params[] = sanitize_text_field($filters['website_language']);
        }

        // Session ID filter
        if (!empty($filters['session_id'])) {
            $query .= " AND session_id = %s";
            $params[] = sanitize_text_field($filters['session_id']);
        }

        $query .= " ORDER BY created_at DESC LIMIT 1000";

        if (!empty($params)) {
            return $wpdb->get_results($wpdb->prepare($query, $params));
        }

        return $wpdb->get_results($query);
    }

    /**
     * Get form field changes for a specific form
     * Shows the timeline of all field updates for a single booking form
     */
    public static function get_form_field_changes($form_id) {
        global $wpdb;

        $form_id = intval($form_id);
        if ($form_id <= 0) {
            return array();
        }

        $table = $wpdb->prefix . 'booking_form_field_changes';

        $query = "SELECT * FROM {$table} WHERE form_id = %d ORDER BY change_timestamp ASC";

        return $wpdb->get_results($wpdb->prepare($query, $form_id));
    }

    /**
     * Get summary statistics for a date range
     * Returns: total searches, completions, conversion rate, breakdown by country/language, session tracking
     */
    public static function get_summary_stats($date_from, $date_to, $website_id = '', $website_language = '') {
        global $wpdb;

        $date_from = sanitize_text_field($date_from);
        $date_to = sanitize_text_field($date_to);
        $website_id = sanitize_text_field($website_id);
        $website_language = sanitize_text_field($website_language);

        $search_table = $wpdb->prefix . 'booking_search_analytics';
        $form_table = $wpdb->prefix . 'booking_form_analytics';

        $stats = array();

        // Build WHERE clause for date and optional filters
        $where = "timestamp BETWEEN %s AND %s";
        $params = array($date_from . ' 00:00:00', $date_to . ' 23:59:59');

        if (!empty($website_id)) {
            $where .= " AND website_id = %s";
            $params[] = $website_id;
        }
        if (!empty($website_language)) {
            $where .= " AND website_language = %s";
            $params[] = $website_language;
        }

        // Total searches
        $query = "SELECT COUNT(*) FROM {$search_table} WHERE {$where}";
        $total_searches = $wpdb->get_var($wpdb->prepare($query, $params));
        $stats['total_searches'] = intval($total_searches);

        // Total sessions (distinct session_id)
        $query = "SELECT COUNT(DISTINCT session_id) FROM {$search_table} WHERE {$where}";
        $total_sessions = $wpdb->get_var($wpdb->prepare($query, $params));
        $stats['total_sessions'] = intval($total_sessions);

        // Total form completions
        $form_where = "form_stage = 'completed' AND created_at BETWEEN %s AND %s";
        $form_params = array($date_from . ' 00:00:00', $date_to . ' 23:59:59');

        if (!empty($website_id)) {
            $form_where .= " AND website_id = %s";
            $form_params[] = $website_id;
        }
        if (!empty($website_language)) {
            $form_where .= " AND website_language = %s";
            $form_params[] = $website_language;
        }

        $query = "SELECT COUNT(*) FROM {$form_table} WHERE {$form_where}";
        $total_completions = $wpdb->get_var($wpdb->prepare($query, $form_params));
        $stats['total_completions'] = intval($total_completions);

        // Sessions completed (distinct session_id with completed form)
        $query = "SELECT COUNT(DISTINCT session_id) FROM {$form_table} WHERE {$form_where}";
        $completed_sessions = $wpdb->get_var($wpdb->prepare($query, $form_params));
        $stats['completed_sessions'] = intval($completed_sessions);

        // Conversion rate
        $stats['conversion_rate'] = $stats['total_searches'] > 0
            ? round(($stats['total_completions'] / $stats['total_searches']) * 100, 2)
            : 0;

        // By country
        $by_country_raw = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT country, COUNT(*) as count FROM {$search_table} WHERE {$where} GROUP BY country ORDER BY count DESC",
                $params
            )
        );
        $stats['by_country'] = is_array($by_country_raw) ? $by_country_raw : array();

        // By language
        $by_language_raw = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT language, COUNT(*) as count FROM {$search_table} WHERE {$where} GROUP BY language ORDER BY count DESC",
                $params
            )
        );
        $stats['by_language'] = is_array($by_language_raw) ? $by_language_raw : array();

        // By site source (regional vs main)
        $by_source_raw = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT source, COUNT(*) as count FROM {$search_table} WHERE {$where} GROUP BY source ORDER BY count DESC",
                $params
            )
        );
        $stats['by_source'] = is_array($by_source_raw) ? $by_source_raw : array();

        // Form completion rate by stage
        $by_stage_raw = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT form_stage, COUNT(*) as count FROM {$form_table} WHERE created_at BETWEEN %s AND %s GROUP BY form_stage ORDER BY count DESC",
                array($date_from . ' 00:00:00', $date_to . ' 23:59:59')
            )
        );
        $stats['by_stage'] = is_array($by_stage_raw) ? $by_stage_raw : array();

        return $stats;
    }

    /**
     * Get count of forms at each stage (for funnel analysis)
     */
    public static function get_form_funnel_stats($date_from, $date_to) {
        global $wpdb;

        $date_from = sanitize_text_field($date_from);
        $date_to = sanitize_text_field($date_to);

        $table = $wpdb->prefix . 'booking_form_analytics';

        $stages = array(
            'vehicle_selection',
            'customer_info',
            'booking_details',
            'completed'
        );

        $funnel = array();

        foreach ($stages as $stage) {
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE form_stage = %s AND created_at BETWEEN %s AND %s",
                $stage,
                $date_from . ' 00:00:00',
                $date_to . ' 23:59:59'
            ));

            $funnel[$stage] = intval($count);
        }

        return $funnel;
    }

    /**
     * Get top vehicles by popularity (number of selections)
     */
    public static function get_top_vehicles($limit = 10) {
        global $wpdb;

        $table = $wpdb->prefix . 'booking_form_analytics';

        $query = "SELECT vehicle_name, COUNT(*) as selections FROM {$table}
                 WHERE vehicle_name != '' AND vehicle_name IS NOT NULL
                 GROUP BY vehicle_name
                 ORDER BY selections DESC
                 LIMIT %d";

        return $wpdb->get_results($wpdb->prepare($query, $limit));
    }

    /**
     * Get average price by country
     */
    public static function get_avg_price_by_country() {
        global $wpdb;

        $table = $wpdb->prefix . 'booking_form_analytics';

        $query = "SELECT country, AVG(CAST(vehicle_price AS DECIMAL(10,2))) as avg_price, COUNT(*) as total
                 FROM {$table}
                 WHERE vehicle_price != '' AND vehicle_price IS NOT NULL
                 GROUP BY country
                 ORDER BY country";

        return $wpdb->get_results($query);
    }

    /**
     * Get all distinct website IDs from search analytics
     */
    public static function get_website_ids() {
        global $wpdb;
        $table = $wpdb->prefix . 'booking_search_analytics';
        $results = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT website_id FROM {$table} WHERE website_id IS NOT NULL AND website_id != '' ORDER BY website_id"
        ));
        return is_array($results) ? $results : array();
    }

    /**
     * Get all distinct website languages from search analytics
     */
    public static function get_website_languages() {
        global $wpdb;
        $table = $wpdb->prefix . 'booking_search_analytics';
        $results = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT website_language FROM {$table} WHERE website_language IS NOT NULL AND website_language != '' ORDER BY website_language"
        ));
        return is_array($results) ? $results : array();
    }
}
?>
