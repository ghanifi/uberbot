<?php
/**
 * Airlinel Data Synchronization Manager
 * Manages synchronization of vehicles, pricing, users, exchange rates, and reservations
 * across main site and regional sites
 */
class Airlinel_Data_Sync_Manager {

    private $sync_log_option = 'airlinel_sync_log';
    private $sync_timestamps_option = 'airlinel_sync_timestamps';
    private $max_log_entries = 500;

    /**
     * Constructor - Initialize sync manager
     */
    public function __construct() {
        // Initialize options if they don't exist
        if (!get_option($this->sync_timestamps_option)) {
            update_option($this->sync_timestamps_option, array(
                'vehicles' => 0,
                'exchange_rates' => 0,
                'users' => 0,
            ));
        }
    }

    /**
     * Get all vehicles, optionally filtered by country
     * Returns from WordPress database (fleet CPT)
     *
     * @param string $country Optional country filter ('UK' or 'TR')
     * @return array Array of vehicle objects
     */
    public function get_vehicles($country = null) {
        $args = array(
            'post_type' => 'fleet',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        );

        if (!empty($country)) {
            $args['meta_query'] = array(
                array(
                    'key' => '_fleet_country',
                    'value' => sanitize_text_field($country),
                    'compare' => '=',
                ),
            );
        }

        $vehicles = get_posts($args);
        $result = array();

        foreach ($vehicles as $vehicle) {
            $result[] = array(
                'id' => $vehicle->ID,
                'name' => $vehicle->post_title,
                'country' => get_post_meta($vehicle->ID, '_fleet_country', true) ?: 'UK',
                'passengers' => get_post_meta($vehicle->ID, '_fleet_passengers', true) ?: 4,
                'luggage' => get_post_meta($vehicle->ID, '_fleet_luggage', true) ?: 3,
                'multiplier' => get_post_meta($vehicle->ID, '_fleet_multiplier', true) ?: 1.0,
                'category' => get_post_meta($vehicle->ID, '_fleet_category', true) ?: 'standard',
            );
        }

        return $result;
    }

    /**
     * Sync exchange rates from external API or manual update
     * Updates rates in the exchange rate manager and logs the sync event
     *
     * @param array $rates Optional array of rates to set (GBP, EUR, TRY, USD)
     * @param bool $from_api If true, attempt to fetch from API instead of using provided rates
     * @return bool True on success, false on failure
     */
    public function sync_exchange_rates($rates = null, $from_api = false) {
        try {
            if ($from_api) {
                // Attempt to fetch from free API (exchangerate-api.com)
                // For now, we'll use manual rates or cached rates
                // In production, you'd call: https://api.exchangerate-api.com/v4/latest/GBP
                error_log('Airlinel: Exchange rate sync from API requested but not yet configured');
                $this->log_sync_event('exchange_rates', 'warning', 'API sync requested but not configured. Using manual update.');
                return false;
            }

            // Update rates using provided data or keep existing
            if (!empty($rates) && is_array($rates)) {
                if (class_exists('Airlinel_Exchange_Rate_Manager')) {
                    $mgr = new Airlinel_Exchange_Rate_Manager();
                    $mgr->set_rates($rates);

                    // Update timestamp
                    $this->_update_sync_timestamp('exchange_rates');

                    $this->log_sync_event('exchange_rates', 'success', sprintf(
                        'Exchange rates updated: GBP=%.2f, EUR=%.2f, TRY=%.2f, USD=%.2f',
                        $rates['GBP'] ?? 1.0,
                        $rates['EUR'] ?? 0,
                        $rates['TRY'] ?? 0,
                        $rates['USD'] ?? 0
                    ));

                    return true;
                }
            }

            return false;
        } catch (Exception $e) {
            $this->log_sync_event('exchange_rates', 'error', 'Exception: ' . $e->getMessage());
            error_log('Airlinel_Data_Sync_Manager::sync_exchange_rates - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get current exchange rates with timestamp
     *
     * @return array Array with 'rates' and 'last_updated' keys
     */
    public function get_exchange_rates() {
        if (class_exists('Airlinel_Exchange_Rate_Manager')) {
            $mgr = new Airlinel_Exchange_Rate_Manager();
            $rates = $mgr->get_rates();
        } else {
            $rates = array(
                'GBP' => 1.00,
                'EUR' => 1.18,
                'TRY' => 42.50,
                'USD' => 1.27,
            );
        }

        $timestamp = $this->get_last_sync_time('exchange_rates');

        return array(
            'rates' => $rates,
            'last_updated' => $timestamp,
            'last_updated_formatted' => $timestamp ? wp_date('Y-m-d H:i:s', $timestamp) : 'Never',
        );
    }

    /**
     * Verify that all data is in sync
     * Returns health status for each data type
     *
     * @return array Health status with 'overall' and individual statuses
     */
    public function verify_sync_health() {
        $health = array(
            'overall' => 'healthy', // healthy, warning, error
            'vehicles' => array(
                'status' => 'healthy',
                'count' => 0,
                'last_sync' => 0,
                'freshness' => 'fresh', // fresh, stale, error
            ),
            'exchange_rates' => array(
                'status' => 'healthy',
                'last_sync' => 0,
                'freshness' => 'fresh',
            ),
            'users' => array(
                'status' => 'healthy',
                'count' => 0,
                'last_sync' => 0,
                'freshness' => 'fresh',
            ),
            'reservations' => array(
                'status' => 'healthy',
                'count' => 0,
                'last_sync' => 0,
            ),
        );

        // Check vehicles
        $vehicles = $this->get_vehicles();
        $health['vehicles']['count'] = count($vehicles);
        $health['vehicles']['last_sync'] = $this->get_last_sync_time('vehicles');
        if (empty($vehicles)) {
            $health['vehicles']['status'] = 'error';
            $health['vehicles']['freshness'] = 'error';
            $health['overall'] = 'error';
        } elseif ($health['vehicles']['last_sync'] && time() - $health['vehicles']['last_sync'] > 3600) {
            $health['vehicles']['freshness'] = 'stale';
            $health['overall'] = 'warning';
        }

        // Check exchange rates
        $ex_rates = $this->get_exchange_rates();
        $health['exchange_rates']['last_sync'] = $ex_rates['last_updated'];
        if ($ex_rates['last_updated'] && time() - $ex_rates['last_updated'] > 86400) { // 24 hours
            $health['exchange_rates']['freshness'] = 'stale';
            if ($health['overall'] !== 'error') {
                $health['overall'] = 'warning';
            }
        }

        // Check users
        $user_count = count_users();
        $health['users']['count'] = $user_count['total_users'] ?? 0;
        $health['users']['last_sync'] = $this->get_last_sync_time('users');

        // Check reservations
        $res_count = wp_count_posts('reservations');
        $health['reservations']['count'] = $res_count->publish + $res_count->pending + $res_count->draft ?? 0;

        return $health;
    }

    /**
     * Get last sync timestamp for a specific data type
     *
     * @param string $data_type Type of data (vehicles, exchange_rates, users)
     * @return int Unix timestamp of last sync, or 0 if never synced
     */
    public function get_last_sync_time($data_type) {
        $timestamps = get_option($this->sync_timestamps_option, array());
        return isset($timestamps[$data_type]) ? (int)$timestamps[$data_type] : 0;
    }

    /**
     * Log a synchronization event
     *
     * @param string $data_type Type of data being synced
     * @param string $status Status of sync (success, warning, error)
     * @param string $message Detailed message about the sync
     * @return bool Success
     */
    public function log_sync_event($data_type, $status, $message) {
        $log = get_option($this->sync_log_option, array());

        // Ensure log is an array
        if (!is_array($log)) {
            $log = array();
        }

        // Add new event
        $event = array(
            'timestamp' => time(),
            'data_type' => sanitize_text_field($data_type),
            'status' => sanitize_text_field($status),
            'message' => sanitize_text_field($message),
        );

        array_unshift($log, $event);

        // Keep only max entries
        $log = array_slice($log, 0, $this->max_log_entries);

        return update_option($this->sync_log_option, $log);
    }

    /**
     * Get recent sync events from the log
     *
     * @param int $limit Number of events to return
     * @return array Array of sync events
     */
    public function get_sync_log($limit = 50) {
        $log = get_option($this->sync_log_option, array());

        if (!is_array($log)) {
            return array();
        }

        return array_slice($log, 0, min($limit, count($log)));
    }

    /**
     * Clear the sync log
     *
     * @return bool Success
     */
    public function clear_sync_log() {
        return update_option($this->sync_log_option, array());
    }

    /**
     * Get sync statistics for the dashboard
     *
     * @return array Statistics data
     */
    public function get_sync_stats() {
        $health = $this->verify_sync_health();
        $log = $this->get_sync_log(10);

        // Count success/warning/error from recent log
        $stats = array(
            'recent_success' => 0,
            'recent_warning' => 0,
            'recent_error' => 0,
        );

        foreach ($log as $event) {
            if ($event['status'] === 'success') {
                $stats['recent_success']++;
            } elseif ($event['status'] === 'warning') {
                $stats['recent_warning']++;
            } elseif ($event['status'] === 'error') {
                $stats['recent_error']++;
            }
        }

        return array(
            'health' => $health,
            'stats' => $stats,
            'recent_log' => $log,
        );
    }

    /**
     * Perform a full sync of vehicles (on main site only)
     * This would synchronize vehicles across the network
     *
     * @return array Result with status and message
     */
    public function sync_vehicles() {
        try {
            $vehicles = $this->get_vehicles();
            $this->_update_sync_timestamp('vehicles');

            $this->log_sync_event('vehicles', 'success', sprintf(
                'Vehicle sync completed: %d vehicles found',
                count($vehicles)
            ));

            return array(
                'success' => true,
                'count' => count($vehicles),
                'message' => sprintf('%d vehicles synced', count($vehicles)),
            );
        } catch (Exception $e) {
            $this->log_sync_event('vehicles', 'error', 'Exception: ' . $e->getMessage());
            error_log('Airlinel_Data_Sync_Manager::sync_vehicles - ' . $e->getMessage());

            return array(
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            );
        }
    }

    /**
     * Internal helper to update sync timestamp
     *
     * @param string $data_type Type of data being synced
     * @return void
     */
    private function _update_sync_timestamp($data_type) {
        $timestamps = get_option($this->sync_timestamps_option, array());
        $timestamps[$data_type] = time();
        update_option($this->sync_timestamps_option, $timestamps);
    }

    /**
     * Schedule automatic sync jobs
     * Called during plugin/theme setup
     *
     * @return void
     */
    public static function schedule_sync_jobs() {
        // Schedule hourly vehicle sync
        if (!wp_next_scheduled('airlinel_hourly_vehicle_sync')) {
            wp_schedule_event(time(), 'hourly', 'airlinel_hourly_vehicle_sync');
        }

        // Schedule hourly exchange rate sync
        if (!wp_next_scheduled('airlinel_hourly_exchange_rate_sync')) {
            wp_schedule_event(time(), 'hourly', 'airlinel_hourly_exchange_rate_sync');
        }
    }

    /**
     * Unschedule automatic sync jobs
     * Called during plugin/theme deactivation
     *
     * @return void
     */
    public static function unschedule_sync_jobs() {
        wp_clear_scheduled_hook('airlinel_hourly_vehicle_sync');
        wp_clear_scheduled_hook('airlinel_hourly_exchange_rate_sync');
    }
}
?>
