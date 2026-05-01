<?php
/**
 * Airlinel Analytics Manager
 * Handles analytics data retrieval and calculations for customer source tracking
 * and multi-site performance metrics
 */
class Airlinel_Analytics_Manager {

    private $exchange_rate_mgr;

    /**
     * Constructor
     */
    public function __construct() {
        if (class_exists('Airlinel_Exchange_Rate_Manager')) {
            $this->exchange_rate_mgr = new Airlinel_Exchange_Rate_Manager();
        }
    }

    /**
     * Get bookings filtered by site, date range, and other criteria
     *
     * @param string|null $site_id Optional site ID filter
     * @param string|null $start_date Optional start date (YYYY-MM-DD)
     * @param string|null $end_date Optional end date (YYYY-MM-DD)
     * @param array $args Additional arguments (status, language, etc.)
     * @return array Array of booking data
     */
    public function get_bookings_by_site($site_id = null, $start_date = null, $end_date = null, $args = array()) {
        $query_args = array(
            'post_type' => 'reservations',
            'posts_per_page' => isset($args['per_page']) ? intval($args['per_page']) : -1,
            'paged' => isset($args['paged']) ? intval($args['paged']) : 1,
            'post_status' => 'any',
            'orderby' => 'post_date',
            'order' => 'DESC',
        );

        $meta_query = array('relation' => 'AND');

        // Filter by site if specified
        if (!empty($site_id)) {
            $meta_query[] = array(
                'key' => 'source_site',
                'value' => sanitize_text_field($site_id),
                'compare' => '=',
            );
        }

        // Filter by status if specified
        if (!empty($args['status'])) {
            $query_args['post_status'] = sanitize_text_field($args['status']);
        }

        // Filter by language if specified
        if (!empty($args['language'])) {
            $meta_query[] = array(
                'key' => 'source_language',
                'value' => sanitize_text_field($args['language']),
                'compare' => '=',
            );
        }

        // Filter by date range
        if (!empty($start_date) || !empty($end_date)) {
            $date_query = array();

            if (!empty($start_date)) {
                $date_query['after'] = array(
                    'year' => intval(substr($start_date, 0, 4)),
                    'month' => intval(substr($start_date, 5, 2)),
                    'day' => intval(substr($start_date, 8, 2)),
                );
            }

            if (!empty($end_date)) {
                $date_query['before'] = array(
                    'year' => intval(substr($end_date, 0, 4)),
                    'month' => intval(substr($end_date, 5, 2)),
                    'day' => intval(substr($end_date, 8, 2)),
                );
            }

            $date_query['inclusive'] = true;
            $query_args['date_query'] = array($date_query);
        }

        if (count($meta_query) > 1) {
            $query_args['meta_query'] = $meta_query;
        }

        $query = new WP_Query($query_args);
        $bookings = array();

        foreach ($query->posts as $post) {
            $bookings[] = $this->_format_booking($post);
        }

        return array(
            'bookings' => $bookings,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
        );
    }

    /**
     * Get revenue aggregated by site with currency conversion to GBP
     *
     * @param string|null $site_id Optional site ID filter
     * @param string|null $start_date Optional start date (YYYY-MM-DD)
     * @param string|null $end_date Optional end date (YYYY-MM-DD)
     * @param string $target_currency Target currency for conversion (default: GBP)
     * @return array Array with site IDs as keys and revenue data as values
     */
    public function get_revenue_by_site($site_id = null, $start_date = null, $end_date = null, $target_currency = 'GBP') {
        $result = $this->get_bookings_by_site($site_id, $start_date, $end_date, array('per_page' => -1));
        $bookings = $result['bookings'];

        $revenue_by_site = array();

        foreach ($bookings as $booking) {
            $site = $booking['source_site'] ?: 'main';
            $amount_gbp = $this->_convert_to_currency($booking['total_price'], $booking['currency'], $target_currency);

            if (!isset($revenue_by_site[$site])) {
                $revenue_by_site[$site] = array(
                    'site_id' => $site,
                    'revenue' => 0,
                    'count' => 0,
                    'currency' => $target_currency,
                );
            }

            $revenue_by_site[$site]['revenue'] += $amount_gbp;
            $revenue_by_site[$site]['count'] += 1;
        }

        // Calculate average
        foreach ($revenue_by_site as $site => $data) {
            if ($data['count'] > 0) {
                $revenue_by_site[$site]['avg_value'] = $data['revenue'] / $data['count'];
            } else {
                $revenue_by_site[$site]['avg_value'] = 0;
            }
        }

        return $revenue_by_site;
    }

    /**
     * Get bookings aggregated by language
     *
     * @param string|null $language Optional language filter
     * @param string|null $start_date Optional start date (YYYY-MM-DD)
     * @param string|null $end_date Optional end date (YYYY-MM-DD)
     * @return array Array with languages as keys and booking data as values
     */
    public function get_bookings_by_language($language = null, $start_date = null, $end_date = null) {
        $result = $this->get_bookings_by_site(null, $start_date, $end_date, array('per_page' => -1));
        $bookings = $result['bookings'];

        $bookings_by_language = array();

        foreach ($bookings as $booking) {
            $lang = $booking['source_language'] ?: 'EN';

            if (!isset($bookings_by_language[$lang])) {
                $bookings_by_language[$lang] = array(
                    'language' => $lang,
                    'bookings' => 0,
                    'revenue' => 0,
                );
            }

            $bookings_by_language[$lang]['bookings'] += 1;
            $amount_gbp = $this->_convert_to_currency($booking['total_price'], $booking['currency'], 'GBP');
            $bookings_by_language[$lang]['revenue'] += $amount_gbp;
        }

        return $bookings_by_language;
    }

    /**
     * Get daily booking trend data for charting
     *
     * @param int $days Number of days to retrieve (default: 30)
     * @param string|null $site_id Optional site ID filter
     * @return array Array with dates as keys and booking counts as values
     */
    public function get_trend_data($days = 30, $site_id = null) {
        $start_date = date('Y-m-d', strtotime("-{$days} days"));
        $end_date = date('Y-m-d');

        $result = $this->get_bookings_by_site($site_id, $start_date, $end_date, array('per_page' => -1));
        $bookings = $result['bookings'];

        // Initialize array with all dates
        $trend_data = array();
        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime("-" . ($days - $i - 1) . " days"));
            $trend_data[$date] = 0;
        }

        // Count bookings by date
        foreach ($bookings as $booking) {
            $date = substr($booking['post_date'], 0, 10);
            if (isset($trend_data[$date])) {
                $trend_data[$date]++;
            }
        }

        return $trend_data;
    }

    /**
     * Get comprehensive analytics summary with all key metrics
     *
     * @param string|null $start_date Optional start date (YYYY-MM-DD)
     * @param string|null $end_date Optional end date (YYYY-MM-DD)
     * @return array Summary metrics including totals, averages, and breakdowns
     */
    public function get_analytics_summary($start_date = null, $end_date = null) {
        $result = $this->get_bookings_by_site(null, $start_date, $end_date, array('per_page' => -1));
        $bookings = $result['bookings'];
        $total_bookings = count($bookings);

        $total_revenue = 0;
        $sites = array();
        $languages = array();

        foreach ($bookings as $booking) {
            $amount_gbp = $this->_convert_to_currency($booking['total_price'], $booking['currency'], 'GBP');
            $total_revenue += $amount_gbp;

            $site = $booking['source_site'] ?: 'main';
            if (!isset($sites[$site])) {
                $sites[$site] = array('count' => 0, 'revenue' => 0);
            }
            $sites[$site]['count']++;
            $sites[$site]['revenue'] += $amount_gbp;

            $lang = $booking['source_language'] ?: 'EN';
            if (!isset($languages[$lang])) {
                $languages[$lang] = array('count' => 0, 'revenue' => 0);
            }
            $languages[$lang]['count']++;
            $languages[$lang]['revenue'] += $amount_gbp;
        }

        $avg_value = $total_bookings > 0 ? $total_revenue / $total_bookings : 0;

        return array(
            'total_bookings' => $total_bookings,
            'total_revenue' => round($total_revenue, 2),
            'avg_value' => round($avg_value, 2),
            'top_site' => $this->_get_top_key($sites),
            'top_language' => $this->_get_top_key($languages),
            'sites_summary' => $sites,
            'languages_summary' => $languages,
            'date_range' => array('from' => $start_date, 'to' => $end_date),
        );
    }

    /**
     * Get all regional site IDs
     *
     * @return array Array of site IDs
     */
    public function get_regional_sites() {
        $regional_keys = get_option('airlinel_regional_api_keys', array());
        $sites = array('main');

        if (is_array($regional_keys)) {
            $sites = array_merge($sites, array_keys($regional_keys));
        }

        return $sites;
    }

    /**
     * Format a booking post for analytics display
     *
     * @param WP_Post $post Booking post object
     * @return array Formatted booking data
     */
    private function _format_booking($post) {
        return array(
            'id' => $post->ID,
            'customer_name' => get_post_meta($post->ID, 'customer_name', true),
            'email' => get_post_meta($post->ID, 'email', true),
            'source_site' => get_post_meta($post->ID, 'source_site', true),
            'source_language' => get_post_meta($post->ID, 'source_language', true),
            'source_url' => get_post_meta($post->ID, 'source_url', true),
            'total_price' => floatval(get_post_meta($post->ID, 'total_price', true)),
            'currency' => get_post_meta($post->ID, 'currency', true) ?: 'GBP',
            'transfer_date' => get_post_meta($post->ID, 'transfer_date', true),
            'status' => $post->post_status,
            'post_date' => $post->post_date,
            'pickup_location' => get_post_meta($post->ID, 'pickup_location', true),
            'dropoff_location' => get_post_meta($post->ID, 'dropoff_location', true),
        );
    }

    /**
     * Convert amount from one currency to target currency
     *
     * @param float $amount Amount to convert
     * @param string $from_currency Source currency code
     * @param string $to_currency Target currency code
     * @return float Converted amount
     */
    private function _convert_to_currency($amount, $from_currency, $to_currency) {
        if ($from_currency === $to_currency) {
            return floatval($amount);
        }

        if (!$this->exchange_rate_mgr) {
            // Fallback if exchange rate manager not available
            return floatval($amount);
        }

        $rates = $this->exchange_rate_mgr->get_rates();

        // Convert to GBP first (base currency)
        $base_rate = $rates[$from_currency] ?? 1.0;
        $amount_gbp = floatval($amount) / floatval($base_rate);

        // Convert from GBP to target currency
        $target_rate = $rates[$to_currency] ?? 1.0;
        return $amount_gbp * floatval($target_rate);
    }

    /**
     * Get the top key from an associative array by count
     *
     * @param array $data Array with count values
     * @return string|null The key with highest count, or null if empty
     */
    private function _get_top_key($data) {
        if (empty($data)) {
            return null;
        }

        $top = null;
        $max_count = 0;

        foreach ($data as $key => $value) {
            $count = isset($value['count']) ? $value['count'] : 0;
            if ($count > $max_count) {
                $max_count = $count;
                $top = $key;
            }
        }

        return $top;
    }

    /**
     * Export bookings to CSV format
     *
     * @param string|null $site_id Optional site ID filter
     * @param string|null $start_date Optional start date (YYYY-MM-DD)
     * @param string|null $end_date Optional end date (YYYY-MM-DD)
     * @return string CSV formatted data
     */
    public function export_to_csv($site_id = null, $start_date = null, $end_date = null) {
        $result = $this->get_bookings_by_site($site_id, $start_date, $end_date, array('per_page' => -1));
        $bookings = $result['bookings'];

        $csv = "Booking ID,Customer Name,Email,Site,Language,Transfer Date,Amount,Currency,Status\n";

        foreach ($bookings as $booking) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s,%.2f,%s,%s\n",
                $booking['id'],
                '"' . str_replace('"', '""', $booking['customer_name']) . '"',
                $booking['email'],
                $booking['source_site'] ?: 'main',
                $booking['source_language'] ?: 'EN',
                $booking['transfer_date'],
                $booking['total_price'],
                $booking['currency'],
                $booking['status']
            );
        }

        return $csv;
    }
}
?>
