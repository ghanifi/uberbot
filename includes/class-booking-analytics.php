<?php
/**
 * Airlinel Booking Analytics
 * Integrated into theme - originally as separate plugin
 * Arama, araç seçimi ve müşteri verilerini kaydeder. Admin dashboard ile istatistik gösterir.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Airlinel_Booking_Analytics {
    const VERSION = '1.0.0';
    const TABLE = 'booking_searches';

    public function __construct() {
        // Register hooks - support both naming conventions
        add_action('admin_init', array($this, 'create_table'));

        // Track search - supports both airlinel_track_search and airlinel_log_search
        add_action('wp_ajax_airlinel_track_search', array($this, 'log_search'));
        add_action('wp_ajax_nopriv_airlinel_track_search', array($this, 'log_search'));
        add_action('wp_ajax_airlinel_log_search', array($this, 'log_search'));
        add_action('wp_ajax_nopriv_airlinel_log_search', array($this, 'log_search'));

        // Log vehicle selection
        add_action('wp_ajax_airlinel_track_vehicle', array($this, 'log_vehicle'));
        add_action('wp_ajax_nopriv_airlinel_track_vehicle', array($this, 'log_vehicle'));
        add_action('wp_ajax_airlinel_log_vehicle', array($this, 'log_vehicle'));
        add_action('wp_ajax_nopriv_airlinel_log_vehicle', array($this, 'log_vehicle'));

        // Log customer form
        add_action('wp_ajax_airlinel_track_customer', array($this, 'log_customer'));
        add_action('wp_ajax_nopriv_airlinel_track_customer', array($this, 'log_customer'));
        add_action('wp_ajax_airlinel_log_customer', array($this, 'log_customer'));
        add_action('wp_ajax_nopriv_airlinel_log_customer', array($this, 'log_customer'));

        // Log payment complete
        add_action('wp_ajax_airlinel_track_payment', array($this, 'log_payment_complete'));
        add_action('wp_ajax_nopriv_airlinel_track_payment', array($this, 'log_payment_complete'));
        add_action('wp_ajax_airlinel_log_payment_complete', array($this, 'log_payment_complete'));
        add_action('wp_ajax_nopriv_airlinel_log_payment_complete', array($this, 'log_payment_complete'));

        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_post_airlinel_reset_analytics_table', array($this, 'reset_table'));
    }

    public function create_table() {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            stage         VARCHAR(20)  NOT NULL DEFAULT 'search',
            pickup        VARCHAR(255) NOT NULL DEFAULT '',
            dropoff       VARCHAR(255) NOT NULL DEFAULT '',
            distance      FLOAT        NOT NULL DEFAULT 0,
            duration      VARCHAR(50)  NOT NULL DEFAULT '',
            pickup_date   DATE         NULL,
            pickup_time   TIME         NULL,
            country       VARCHAR(5)   NOT NULL DEFAULT '',
            vehicle_name  VARCHAR(255) NOT NULL DEFAULT '',
            vehicle_price VARCHAR(50)  NOT NULL DEFAULT '',
            customer_name VARCHAR(255) NOT NULL DEFAULT '',
            customer_phone VARCHAR(50) NOT NULL DEFAULT '',
            customer_email VARCHAR(255) NOT NULL DEFAULT '',
            flight_number  VARCHAR(50)  NOT NULL DEFAULT '',
            agency_code    VARCHAR(50)  NOT NULL DEFAULT '',
            notes          TEXT         NOT NULL DEFAULT '',
            stripe_session_id VARCHAR(255) NOT NULL DEFAULT '',
            ip_address    VARCHAR(45)  NOT NULL DEFAULT '',
            created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_stage (stage),
            KEY idx_country (country),
            KEY idx_created (created_at)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        update_option('airlinel_analytics_db_version', self::VERSION);
    }

    public function log_search() {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;

        $pickup = sanitize_text_field($_POST['pickup'] ?? '');
        $dropoff = sanitize_text_field($_POST['dropoff'] ?? '');
        $distance = floatval($_POST['distance'] ?? 0);
        $duration = sanitize_text_field($_POST['duration'] ?? '');
        $pickup_date = sanitize_text_field($_POST['pickup_date'] ?? '');
        $pickup_time = sanitize_text_field($_POST['pickup_time'] ?? '');
        $country = strtoupper(sanitize_text_field($_POST['country'] ?? ''));

        if (empty($pickup) || empty($dropoff)) {
            wp_send_json_error('Missing fields');
        }

        $wpdb->insert($table, [
            'stage' => 'search',
            'pickup' => $pickup,
            'dropoff' => $dropoff,
            'distance' => $distance,
            'duration' => $duration,
            'pickup_date' => $pickup_date ?: null,
            'pickup_time' => $pickup_time ?: null,
            'country' => $country,
            'ip_address' => $this->get_ip(),
        ]);

        wp_send_json_success(['id' => $wpdb->insert_id]);
    }

    public function log_vehicle() {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;

        $row_id = intval($_POST['row_id'] ?? 0);
        $vehicle_name = sanitize_text_field($_POST['vehicle_name'] ?? '');
        $vehicle_price = sanitize_text_field($_POST['vehicle_price'] ?? '');

        if ($row_id) {
            $wpdb->update($table,
                ['stage' => 'vehicle_selected', 'vehicle_name' => $vehicle_name, 'vehicle_price' => $vehicle_price],
                ['id' => $row_id]
            );
        } else {
            $wpdb->insert($table, [
                'stage' => 'vehicle_selected',
                'vehicle_name' => $vehicle_name,
                'vehicle_price' => $vehicle_price,
                'ip_address' => $this->get_ip(),
            ]);
            $row_id = $wpdb->insert_id;
        }

        wp_send_json_success(['id' => $row_id]);
    }

    public function log_customer() {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;

        $row_id = intval($_POST['row_id'] ?? 0);
        $name = sanitize_text_field($_POST['customer_name'] ?? '');
        $phone = sanitize_text_field($_POST['customer_phone'] ?? '');
        $email = sanitize_email($_POST['customer_email'] ?? '');
        $flight = sanitize_text_field($_POST['flight_number'] ?? '');
        $agency = sanitize_text_field($_POST['agency_code'] ?? '');
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');

        $stage_raw = sanitize_text_field($_POST['stage'] ?? 'form_filling');
        $allowed_stages = ['form_filling', 'checkout_started', 'checkout'];
        $stage = in_array($stage_raw, $allowed_stages, true) ? $stage_raw : 'form_filling';

        if ($row_id) {
            $wpdb->update($table,
                ['stage' => $stage, 'customer_name' => $name, 'customer_phone' => $phone, 'customer_email' => $email, 'flight_number' => $flight, 'agency_code' => $agency, 'notes' => $notes],
                ['id' => $row_id]
            );
        } else {
            $wpdb->insert($table, [
                'stage' => $stage,
                'customer_name' => $name,
                'customer_phone' => $phone,
                'customer_email' => $email,
                'flight_number' => $flight,
                'agency_code' => $agency,
                'notes' => $notes,
                'ip_address' => $this->get_ip(),
            ]);
            $row_id = $wpdb->insert_id;
        }

        wp_send_json_success(['id' => $row_id]);
    }

    public function log_payment_complete() {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;
        $row_id = intval($_POST['row_id'] ?? 0);
        $stripe_id = sanitize_text_field($_POST['stripe_session_id'] ?? '');

        if (!$row_id) {
            wp_send_json_error('No row_id');
        }

        $wpdb->update($table,
            ['stage' => 'payment_complete', 'stripe_session_id' => $stripe_id],
            ['id' => $row_id]
        );

        wp_send_json_success();
    }

    private function get_ip() {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '';
    }

    public function enqueue_scripts() {
        $is_booking = is_page('book-your-ride') || is_page('booking');
        $is_success = is_page('success') || (isset($_GET['session_id']) && strpos($_SERVER['REQUEST_URI'], 'success') !== false);
        if (!$is_booking && !$is_success) {
            return;
        }

        wp_enqueue_script(
            'airlinel-booking-tracker',
            get_template_directory_uri() . '/assets/js/booking-tracker.js',
            ['jquery'],
            self::VERSION,
            true
        );

        wp_localize_script('airlinel-booking-tracker', 'airlinel_tracker', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('airlinel_nonce'),
        ]);
    }

    public function register_admin_menu() {
        add_menu_page(
            'Booking Analytics',
            'Booking Analytics',
            'manage_options',
            'airlinel-analytics',
            array($this, 'dashboard_page'),
            'dashicons-chart-bar',
            30
        );
    }

    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_airlinel-analytics') {
            return;
        }
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', [], null, true);
    }

    public function reset_table() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('airlinel_reset_analytics_table');

        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;
        $wpdb->query("DROP TABLE IF EXISTS {$table}");
        $this->create_table();

        wp_redirect(add_query_arg(['page' => 'airlinel-analytics', 'reset' => '1'], admin_url('admin.php')));
        exit;
    }

    public function dashboard_page() {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;

        $filter_country = sanitize_text_field($_GET['country'] ?? '');
        $filter_stage = sanitize_text_field($_GET['stage'] ?? '');
        $filter_from = sanitize_text_field($_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days')));
        $filter_to = sanitize_text_field($_GET['date_to'] ?? date('Y-m-d'));
        $filter_search = sanitize_text_field($_GET['s'] ?? '');
        $per_page = 25;
        $current_page = max(1, intval($_GET['paged'] ?? 1));
        $offset = ($current_page - 1) * $per_page;

        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            $this->export_csv($table, $filter_country, $filter_stage, $filter_from, $filter_to, $filter_search);
            exit;
        }

        $where = "WHERE created_at BETWEEN %s AND %s";
        $params = [$filter_from . ' 00:00:00', $filter_to . ' 23:59:59'];

        if ($filter_country) {
            $where .= " AND country = %s";
            $params[] = $filter_country;
        }
        if ($filter_stage) {
            $where .= " AND stage = %s";
            $params[] = $filter_stage;
        }
        if ($filter_search) {
            $where .= " AND (pickup LIKE %s OR dropoff LIKE %s OR customer_email LIKE %s OR customer_name LIKE %s)";
            $like = '%' . $wpdb->esc_like($filter_search) . '%';
            array_push($params, $like, $like, $like, $like);
        }

        $where_sql = $wpdb->prepare($where, ...$params);

        $total_searches = $wpdb->get_var("SELECT COUNT(*) FROM {$table} {$where_sql}");
        $total_checkout = $wpdb->get_var("SELECT COUNT(*) FROM {$table} {$where_sql} AND stage IN ('checkout_started','checkout')");
        $total_paid = $wpdb->get_var("SELECT COUNT(*) FROM {$table} {$where_sql} AND stage = 'payment_complete'");
        $total_vehicle = $wpdb->get_var("SELECT COUNT(*) FROM {$table} {$where_sql} AND stage IN ('vehicle_selected','checkout')");
        $conversion = $total_searches > 0 ? round(($total_paid / $total_searches) * 100, 1) : 0;

        $top_routes = $wpdb->get_results(
            "SELECT CONCAT(pickup, ' → ', dropoff) AS route, COUNT(*) AS cnt
             FROM {$table} {$where_sql}
             GROUP BY route ORDER BY cnt DESC LIMIT 10"
        );

        $top_vehicles = $wpdb->get_results(
            "SELECT vehicle_name, COUNT(*) AS cnt
             FROM {$table} {$where_sql} AND vehicle_name != ''
             GROUP BY vehicle_name ORDER BY cnt DESC LIMIT 5"
        );

        $daily = $wpdb->get_results(
            "SELECT DATE(created_at) AS day, COUNT(*) AS cnt
             FROM {$table} {$where_sql}
             GROUP BY day ORDER BY day ASC LIMIT 60"
        );

        $by_country = $wpdb->get_results(
            "SELECT country, COUNT(*) AS cnt FROM {$table} {$where_sql} GROUP BY country"
        );

        $by_hour = $wpdb->get_results(
            "SELECT HOUR(pickup_time) AS hr, COUNT(*) AS cnt
             FROM {$table} {$where_sql} AND pickup_time IS NOT NULL
             GROUP BY hr ORDER BY hr ASC"
        );

        $total_rows = $wpdb->get_var("SELECT COUNT(*) FROM {$table} {$where_sql}");
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $per_page, $offset
        ));

        $total_pages = ceil($total_rows / $per_page);

        $daily_labels = wp_json_encode(array_column($daily, 'day'));
        $daily_data = wp_json_encode(array_map('intval', array_column($daily, 'cnt')));

        $vehicle_labels = wp_json_encode(array_column($top_vehicles, 'vehicle_name'));
        $vehicle_data = wp_json_encode(array_map('intval', array_column($top_vehicles, 'cnt')));

        $country_labels = wp_json_encode(array_column($by_country, 'country'));
        $country_data = wp_json_encode(array_map('intval', array_column($by_country, 'cnt')));

        $hour_raw = array_fill(0, 24, 0);
        foreach ($by_hour as $h) {
            if ($h->hr !== null) {
                $hour_raw[intval($h->hr)] = intval($h->cnt);
            }
        }
        $hour_labels = wp_json_encode(range(0, 23));
        $hour_data = wp_json_encode($hour_raw);

        $current_url = admin_url('admin.php?page=airlinel-analytics');
        $export_url = add_query_arg(array_merge($_GET, ['export' => 'csv']), $current_url);

        include get_template_directory() . '/admin/analytics-page.php';
    }

    private function export_csv($table, $country, $stage, $from, $to, $search) {
        global $wpdb;

        $where = "WHERE created_at BETWEEN %s AND %s";
        $params = [$from . ' 00:00:00', $to . ' 23:59:59'];
        if ($country) {
            $where .= " AND country = %s";
            $params[] = $country;
        }
        if ($stage) {
            $where .= " AND stage = %s";
            $params[] = $stage;
        }
        if ($search) {
            $where .= " AND (pickup LIKE %s OR dropoff LIKE %s OR customer_email LIKE %s)";
            $like = '%' . $wpdb->esc_like($search) . '%';
            array_push($params, $like, $like, $like);
        }

        $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} {$where} ORDER BY created_at DESC", ...$params), ARRAY_A);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="booking-analytics-' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($out, ['ID', 'Stage', 'Pickup', 'Dropoff', 'Distance', 'Duration', 'Date', 'Time', 'Country', 'Vehicle', 'Price', 'Name', 'Phone', 'Email', 'Flight', 'Agency', 'Notes', 'IP', 'Created']);

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'], $r['stage'], $r['pickup'], $r['dropoff'],
                $r['distance'], $r['duration'], $r['pickup_date'], $r['pickup_time'],
                $r['country'], $r['vehicle_name'], $r['vehicle_price'],
                $r['customer_name'], $r['customer_phone'], $r['customer_email'],
                $r['flight_number'], $r['agency_code'], $r['notes'],
                $r['ip_address'], $r['created_at']
            ]);
        }
        fclose($out);
    }
}

// Initialize
// Disabled: Using new Airlinel Analytics system instead (class-analytics-tracker.php + admin pages)
// new Airlinel_Booking_Analytics();
