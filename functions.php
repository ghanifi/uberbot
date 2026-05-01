<?php
/**
 * Airlinel Theme Functions and Definitions
 */

// ===== TASK 3.3: LANGUAGE & LOCALIZATION SYSTEM (i18n) =====
// Load Language Manager class for translation support
require_once get_template_directory() . '/includes/class-language-manager.php';

// Load text domain and set up WordPress i18n
add_action('after_setup_theme', function() {
    // Load theme text domain for translation
    load_theme_textdomain('airlinel-theme', get_template_directory() . '/languages');
});

// Initialize language manager
add_action('after_setup_theme', function() {
    $lang_mgr = new Airlinel_Language_Manager();
    $lang_mgr->load_translations();

    // Make language manager available globally
    global $airlinel_language_manager;
    $airlinel_language_manager = $lang_mgr;
});

// ===== TASK 3.1: REGIONAL SITE INITIALIZATION =====
// Check and validate regional site configuration if this is a regional site
if (defined('AIRLINEL_MAIN_SITE_URL')) {
    add_action('admin_init', function() {
        // Verify required constants are configured for regional sites
        if (!defined('AIRLINEL_MAIN_SITE_API_KEY') && is_admin()) {
            add_action('admin_notices', function() {
                if (current_user_can('manage_options')) {
                    echo '<div class="notice notice-error"><p>';
                    echo __('Airlinel: AIRLINEL_MAIN_SITE_API_KEY is not configured in wp-config.php', 'airlinel-theme');
                    echo '</p></div>';
                }
            });
        }

        // Verify Regional_Site_Proxy can be initialized
        if (class_exists('Airlinel_Regional_Site_Proxy')) {
            try {
                new Airlinel_Regional_Site_Proxy();
            } catch (Exception $e) {
                add_action('admin_notices', function() use ($e) {
                    if (current_user_can('manage_options')) {
                        echo '<div class="notice notice-warning"><p>';
                        echo 'Airlinel: Regional Site Proxy error: ' . esc_html($e->getMessage());
                        echo '</p></div>';
                    }
                });
            }
        }
    });
}

// ===== TASK 3.5: REGIONAL API CLIENT & PROXY SERVICE =====
// Load main site client and API proxy handler
// Always load for regional sites (configured via constants OR database options)
require_once get_template_directory() . '/includes/class-main-site-client.php';
require_once get_template_directory() . '/includes/class-api-proxy-handler.php';

// Register AJAX endpoints for front-end integration
add_action('init', function() {
    $proxy = new Airlinel_API_Proxy_Handler();
    $proxy->register_ajax_routes();
});

// Register REST API routes
add_action('rest_api_init', function() {
    $proxy = new Airlinel_API_Proxy_Handler();
    $proxy->register_rest_routes();
});

// ===== INTEGRATED PLUGINS (formerly separate plugins) =====
// Booking Analytics - Integrated from plugin
require_once get_template_directory() . '/includes/class-booking-analytics.php';

// Language Settings - Integrated from plugin
require_once get_template_directory() . '/includes/class-language-settings.php';

// Language Domains & Currency Session Management - NEW SYSTEM
require_once get_template_directory() . '/includes/class-language-domains.php';
require_once get_template_directory() . '/includes/class-currency-session.php';

// ===== CUSTOM POST TYPES =====
function airlinel_register_cpts() {
    // Fleet (Vehicles)
    register_post_type('fleet', array(
        'labels' => array('name' => 'Vehicles', 'singular_name' => 'Vehicle'),
        'public' => true,
        'menu_icon' => 'dashicons-car',
        'supports' => array('title', 'thumbnail', 'editor'),
        'has_archive' => true,
        'rewrite' => array('slug' => 'vehicle'),
    ));

    // Reservations (Bookings)
    register_post_type('reservations', array(
        'labels' => array('name' => 'Reservations', 'singular_name' => 'Reservation'),
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-calendar-alt',
        'supports' => array('title', 'editor', 'custom-fields'),
    ));

    // Agencies
    register_post_type('agencies', array(
        'labels' => array('name' => 'Agencies', 'singular_name' => 'Agency'),
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-building',
        'supports' => array('title', 'custom-fields'),
        'show_in_menu' => false,  // Will be shown in unified menu instead
    ));
}
add_action('init', 'airlinel_register_cpts');

// ===== FLEET META BOXES =====
function airlinel_add_fleet_metabox() {
    add_meta_box('fleet_details', 'Vehicle Details', 'airlinel_fleet_metabox_cb', 'fleet', 'normal');
}
add_action('add_meta_boxes', 'airlinel_add_fleet_metabox');

function airlinel_fleet_metabox_cb($post) {
    $multiplier = get_post_meta($post->ID, '_fleet_multiplier', true) ?: '1.0';
    $passengers = get_post_meta($post->ID, '_fleet_passengers', true) ?: '4';
    $luggage = get_post_meta($post->ID, '_fleet_luggage', true) ?: '3';

    wp_nonce_field('airlinel_fleet_nonce', 'fleet_nonce');

    ?>
    <table class="form-table">
        <tr>
            <th><label for="multiplier">Price Multiplier</label></th>
            <td><input type="number" step="0.1" id="multiplier" name="multiplier" value="<?php echo esc_attr($multiplier); ?>"></td>
        </tr>
        <tr>
            <th><label for="passengers">Passengers</label></th>
            <td><input type="number" min="1" id="passengers" name="passengers" value="<?php echo esc_attr($passengers); ?>"></td>
        </tr>
        <tr>
            <th><label for="luggage">Luggage</label></th>
            <td><input type="number" min="0" id="luggage" name="luggage" value="<?php echo esc_attr($luggage); ?>"></td>
        </tr>
    </table>
    <?php
}

function airlinel_save_fleet_meta($post_id) {
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (!isset($_POST['fleet_nonce']) || !wp_verify_nonce($_POST['fleet_nonce'], 'airlinel_fleet_nonce')) {
        return;
    }

    if (isset($_POST['multiplier'])) {
        update_post_meta($post_id, '_fleet_multiplier', floatval(sanitize_text_field($_POST['multiplier'])));
    }
    if (isset($_POST['passengers'])) {
        update_post_meta($post_id, '_fleet_passengers', intval(sanitize_text_field($_POST['passengers'])));
    }
    if (isset($_POST['luggage'])) {
        update_post_meta($post_id, '_fleet_luggage', intval(sanitize_text_field($_POST['luggage'])));
    }
}
add_action('save_post_fleet', 'airlinel_save_fleet_meta');

// ===== AGENCIES META BOXES =====
function airlinel_add_agencies_metabox() {
    add_meta_box('agency_details', __('Agency Details', 'airlinel-theme'), 'airlinel_agencies_metabox_cb', 'agencies', 'normal');
}
add_action('add_meta_boxes', 'airlinel_add_agencies_metabox');

function airlinel_agencies_metabox_cb($post) {
    $code = get_post_meta($post->ID, 'agency_code', true);
    $email = get_post_meta($post->ID, 'email', true);
    $commission = get_post_meta($post->ID, 'commission_percent', true);

    wp_nonce_field('airlinel_agencies_nonce', 'agencies_nonce');

    ?>
    <table class="form-table">
        <tr>
            <th><label for="agency_code"><?php _e('Agency Code', 'airlinel-theme'); ?></label></th>
            <td><input type="text" id="agency_code" name="agency_code" value="<?php echo esc_attr($code); ?>" class="regular-text" placeholder="e.g., AGENCY001"></td>
        </tr>
        <tr>
            <th><label for="agency_email"><?php _e('Email', 'airlinel-theme'); ?></label></th>
            <td><input type="email" id="agency_email" name="agency_email" value="<?php echo esc_attr($email); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="commission_percent"><?php _e('Commission %', 'airlinel-theme'); ?></label></th>
            <td><input type="number" id="commission_percent" name="commission_percent" value="<?php echo esc_attr($commission); ?>" step="0.1" min="0" max="100" class="small-text"> %</td>
        </tr>
    </table>
    <?php
}

function airlinel_save_agencies_meta($post_id) {
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (!isset($_POST['agencies_nonce']) || !wp_verify_nonce($_POST['agencies_nonce'], 'airlinel_agencies_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (isset($_POST['agency_code'])) {
        update_post_meta($post_id, 'agency_code', sanitize_text_field($_POST['agency_code']));
    }
    if (isset($_POST['agency_email'])) {
        update_post_meta($post_id, 'email', sanitize_email($_POST['agency_email']));
    }
    if (isset($_POST['commission_percent'])) {
        update_post_meta($post_id, 'commission_percent', floatval($_POST['commission_percent']));
    }
}
add_action('save_post_agencies', 'airlinel_save_agencies_meta');

// ===== FLUSH REWRITE RULES ON THEME ACTIVATION =====
function airlinel_flush_rewrite_on_activation() {
    airlinel_register_cpts();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'airlinel_flush_rewrite_on_activation');

// ===== CLASS INCLUDES =====
require_once get_template_directory() . '/includes/class-settings-manager.php';
require_once get_template_directory() . '/includes/class-api-handler.php';
require_once get_template_directory() . '/includes/class-reservation-handler.php';

// ===== CLASS INCLUDES (TASK 1.2) =====
require_once get_template_directory() . '/includes/class-zone-manager.php';
require_once get_template_directory() . '/includes/class-exchange-rate-manager.php';
require_once get_template_directory() . '/includes/class-pricing-engine.php';

// ===== CLASS INCLUDES (TASK 1.3) =====
require_once get_template_directory() . '/includes/class-agency-manager.php';

// ===== CLASS INCLUDES (TASK 1.4) =====
require_once get_template_directory() . '/includes/class-payment-processor.php';

// ===== CLASS INCLUDES (TASK 1.6) =====
require_once get_template_directory() . '/includes/class-reservation-manager.php';

// ===== CLASS INCLUDES (TASK 1.7) =====
require_once get_template_directory() . '/includes/class-ads-txt-manager.php';

// ===== CLASS INCLUDES (TASK 3.0) =====
require_once get_template_directory() . '/includes/class-regional-site-proxy.php';

// ===== CLASS INCLUDES (ADMIN REFACTOR TASK 1) - REGIONAL SETTINGS MANAGER =====
require_once get_template_directory() . '/includes/class-regional-settings-manager.php';

// ===== CLASS INCLUDES (TASK 3.2) - HOMEPAGE MANAGER =====
require_once get_template_directory() . '/includes/class-homepage-manager.php';
require_once get_template_directory() . '/includes/homepage-defaults.php';

// ===== CLASS INCLUDES (TASK 3.6) - ANALYTICS MANAGER =====
require_once get_template_directory() . '/includes/class-analytics-manager.php';

// ===== CLASS INCLUDES (TASK 4) - BOOKING ANALYTICS TRACKER =====
require_once get_template_directory() . '/includes/class-booking-analytics-tracker.php';

// ===== CLASS INCLUDES (TASK 0) - DATABASE MIGRATIONS MANAGER =====
require_once get_template_directory() . '/includes/class-database-migrations.php';

// Create booking analytics table on theme activation and admin pages
add_action('after_switch_theme', function() {
    $tracker = new Airlinel_Booking_Analytics_Tracker();
    $tracker->create_table();
});

// Ensure booking analytics table exists (checked when table is queried)
// Table creation is handled lazily in the tracker class itself

// Enqueue booking tracker script on booking pages
add_action('wp_enqueue_scripts', function() {
    // Check by page slug, page template, or custom post type
    $is_booking = is_page('book-your-ride')
        || is_page('booking')
        || is_page('reservation')
        || is_singular('reservations')
        || (is_page() && get_page_template_slug() === 'page-booking.php');

    if (!$is_booking) {
        return;
    }

    wp_enqueue_script(
        'airlinel-tracker',
        get_template_directory_uri() . '/assets/js/booking-tracker.js',
        array(),
        '1.0.0',
        true
    );

    wp_localize_script('airlinel-tracker', 'aba_data', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('airlinel_tracker'),
    ));
});

// AJAX handler for tracking search
add_action('wp_ajax_airlinel_track_search', 'airlinel_track_search_handler');
add_action('wp_ajax_nopriv_airlinel_track_search', 'airlinel_track_search_handler');

function airlinel_track_search_handler() {
    check_ajax_referer('airlinel_tracker', 'nonce');

    $tracker = new Airlinel_Booking_Analytics_Tracker();

    // Ensure table exists
    $tracker->create_table();

    $result = $tracker->log_search($_POST);

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    } else {
        wp_send_json_success(array('id' => $result));
    }
}

// AJAX handler for tracking vehicle selection
add_action('wp_ajax_airlinel_track_vehicle', 'airlinel_track_vehicle_handler');
add_action('wp_ajax_nopriv_airlinel_track_vehicle', 'airlinel_track_vehicle_handler');

function airlinel_track_vehicle_handler() {
    check_ajax_referer('airlinel_tracker', 'nonce');

    if (empty($_POST['record_id'])) {
        wp_send_json_error('Record ID is required');
        return;
    }

    $tracker = new Airlinel_Booking_Analytics_Tracker();
    $result = $tracker->log_vehicle_selected(intval($_POST['record_id']), $_POST);

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    } else {
        wp_send_json_success(array('id' => intval($_POST['record_id'])));
    }
}

// AJAX handler for tracking customer form
add_action('wp_ajax_airlinel_track_customer', 'airlinel_track_customer_handler');
add_action('wp_ajax_nopriv_airlinel_track_customer', 'airlinel_track_customer_handler');

function airlinel_track_customer_handler() {
    check_ajax_referer('airlinel_tracker', 'nonce');

    if (empty($_POST['record_id'])) {
        wp_send_json_error('Record ID is required');
        return;
    }

    $tracker = new Airlinel_Booking_Analytics_Tracker();
    $result = $tracker->log_customer_info(intval($_POST['record_id']), $_POST);

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    } else {
        wp_send_json_success(array('id' => intval($_POST['record_id'])));
    }
}

// AJAX handler for tracking payment completion
add_action('wp_ajax_airlinel_track_payment', 'airlinel_track_payment_handler');
add_action('wp_ajax_nopriv_airlinel_track_payment', 'airlinel_track_payment_handler');

function airlinel_track_payment_handler() {
    check_ajax_referer('airlinel_tracker', 'nonce');

    if (empty($_POST['record_id'])) {
        wp_send_json_error('Record ID is required');
        return;
    }

    $tracker = new Airlinel_Booking_Analytics_Tracker();
    $result = $tracker->log_payment_complete(intval($_POST['record_id']), $_POST);

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    } else {
        wp_send_json_success(array('id' => intval($_POST['record_id'])));
    }
}

// ===== STRIPE PAYMENT AJAX HANDLERS =====
add_action('wp_ajax_create_payment_intent', 'airlinel_create_payment_intent_ajax');
add_action('wp_ajax_nopriv_create_payment_intent', 'airlinel_create_payment_intent_ajax');

function airlinel_create_payment_intent_ajax() {
    check_ajax_referer('airlinel_nonce', 'nonce');

    if (empty($_POST['reservation_id']) || empty($_POST['amount'])) {
        wp_send_json_error(array('message' => 'Missing required parameters'));
        return;
    }

    $reservation_id = intval($_POST['reservation_id']);
    $amount_gbp = floatval($_POST['amount']);
    $currency = sanitize_text_field($_POST['currency'] ?? 'GBP');
    $email = sanitize_email($_POST['email'] ?? '');

    // Validate reservation exists
    $reservation = get_post($reservation_id);
    if (!$reservation || $reservation->post_type !== 'reservations') {
        wp_send_json_error(array('message' => 'Invalid reservation'));
        return;
    }

    $processor = new Airlinel_Payment_Processor();
    $result = $processor->create_payment_intent($reservation_id, $amount_gbp, $currency, $email);

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
        return;
    }

    wp_send_json_success($result);
}

add_action('wp_ajax_confirm_payment', 'airlinel_confirm_payment_ajax');
add_action('wp_ajax_nopriv_confirm_payment', 'airlinel_confirm_payment_ajax');

function airlinel_confirm_payment_ajax() {
    check_ajax_referer('airlinel_nonce', 'nonce');

    if (empty($_POST['intent_id'])) {
        wp_send_json_error(array('message' => 'Missing payment intent ID'));
        return;
    }

    $intent_id = sanitize_text_field($_POST['intent_id']);

    $processor = new Airlinel_Payment_Processor();
    $result = $processor->confirm_payment($intent_id);

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
        return;
    }

    wp_send_json_success($result);
}

// ===== REGIONAL SITE PROXY HANDLERS (TASK 3.0) =====
// These handlers are only active if the site is a regional site (has AIRLINEL_MAIN_SITE_URL defined)
if (defined('AIRLINEL_MAIN_SITE_URL')) {
    add_action('wp_ajax_nopriv_airlinel_proxy_search', 'airlinel_proxy_search_ajax');
    add_action('wp_ajax_airlinel_proxy_search', 'airlinel_proxy_search_ajax');

    function airlinel_proxy_search_ajax() {
        // FIX 1: CRITICAL - Add nonce verification for CSRF protection
        check_ajax_referer('airlinel_nonce', 'nonce');

        $params = isset($_POST['data']) ? json_decode(stripslashes($_POST['data']), true) : array();

        if (empty($params['pickup']) || empty($params['dropoff'])) {
            wp_send_json_error(array('message' => 'Missing required parameters'));
            return;
        }

        $proxy = new Airlinel_Regional_Site_Proxy();
        $result = $proxy->call_search(
            $params['pickup'],
            $params['dropoff'],
            $params['country'] ?? 'UK',
            $params['passengers'] ?? 1,
            $params['currency'] ?? 'GBP'
        );

        if (is_wp_error($result)) {
            wp_send_json_error(array(
                'message' => $result->get_error_message(),
                'code' => $result->get_error_code(),
            ));
            return;
        }

        wp_send_json_success($result);
    }

    add_action('wp_ajax_nopriv_airlinel_proxy_create_reservation', 'airlinel_proxy_create_reservation_ajax');
    add_action('wp_ajax_airlinel_proxy_create_reservation', 'airlinel_proxy_create_reservation_ajax');

    function airlinel_proxy_create_reservation_ajax() {
        // FIX 1: CRITICAL - Add nonce verification for CSRF protection
        check_ajax_referer('airlinel_nonce', 'nonce');

        $data = isset($_POST['data']) ? json_decode(stripslashes($_POST['data']), true) : array();

        if (empty($data['customer_name']) || empty($data['email'])) {
            wp_send_json_error(array('message' => 'Missing required parameters'));
            return;
        }

        $proxy = new Airlinel_Regional_Site_Proxy();
        $result = $proxy->call_create_reservation($data);

        if (is_wp_error($result)) {
            wp_send_json_error(array(
                'message' => $result->get_error_message(),
                'code' => $result->get_error_code(),
            ));
            return;
        }

        wp_send_json_success($result);
    }

    add_action('wp_ajax_nopriv_airlinel_proxy_get_reservation', 'airlinel_proxy_get_reservation_ajax');
    add_action('wp_ajax_airlinel_proxy_get_reservation', 'airlinel_proxy_get_reservation_ajax');

    function airlinel_proxy_get_reservation_ajax() {
        // FIX 1: CRITICAL - Add nonce verification for CSRF protection
        check_ajax_referer('airlinel_nonce', 'nonce');

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if (empty($id)) {
            wp_send_json_error(array('message' => 'Missing reservation ID'));
            return;
        }

        $proxy = new Airlinel_Regional_Site_Proxy();
        $result = $proxy->call_get_reservation($id);

        if (is_wp_error($result)) {
            wp_send_json_error(array(
                'message' => $result->get_error_message(),
                'code' => $result->get_error_code(),
            ));
            return;
        }

        wp_send_json_success($result);
    }
}

// ===== TASK 3.0: REGIONAL API KEYS INITIALIZATION =====
function airlinel_initialize_regional_api_keys() {
    // Initialize regional API keys if not already set
    $regional_keys = get_option('airlinel_regional_api_keys');

    if (empty($regional_keys)) {
        // Generate secure random keys for each sample regional site (32 chars each)
        // Store as plain text for hash_equals() constant-time comparison in verify_api_key()
        $sample_keys = array(
            'antalya' => wp_generate_password(32, false),
            'istanbul' => wp_generate_password(32, false),
            'berlin' => wp_generate_password(32, false),
        );

        update_option('airlinel_regional_api_keys', $sample_keys);

        if (WP_DEBUG) {
            error_log('Initialized regional API keys for: ' . implode(', ', array_keys($sample_keys)));
            error_log('IMPORTANT: Regional API keys generated and stored. To use regional sites, copy these keys to their local Regional API Settings:');
            foreach ($sample_keys as $site_id => $key) {
                error_log("  {$site_id}: {$key}");
            }
        }
    }
}
add_action('admin_init', 'airlinel_initialize_regional_api_keys');

// ===== API INITIALIZATION =====
add_action('rest_api_init', function() {
    $api = new Airlinel_API_Handler();
    $api->register_routes();
});

// ===== UNIFIED AIRLINEL ADMIN MENU (TASK 2) =====
add_action('admin_menu', 'airlinel_register_unified_menu');

function airlinel_register_unified_menu() {
    // Note: Admin page files are now loaded lazily in their callback functions
    // instead of here, to avoid menu routing issues

    // Main AIRLINEL menu (positioned after Dashboard and before Posts)
    add_menu_page(
        __('Airlinel', 'airlinel-theme'),
        __('Airlinel', 'airlinel-theme'),
        'manage_options',
        'airlinel-dashboard',
        'airlinel_dashboard_page',
        'dashicons-car',
        5
    );

    // Dashboard submenu (first item)
    add_submenu_page(
        'airlinel-dashboard',
        __('Dashboard', 'airlinel-theme'),
        __('Dashboard', 'airlinel-theme'),
        'manage_options',
        'airlinel-dashboard',
        'airlinel_dashboard_page'
    );

    // Settings submenu
    add_submenu_page(
        'airlinel-dashboard',
        __('Settings', 'airlinel-theme'),
        __('Settings', 'airlinel-theme'),
        'manage_options',
        'airlinel-settings',
        'airlinel_settings_page'
    );

    // Agencies submenu
    add_submenu_page(
        'airlinel-dashboard',
        __('Agencies', 'airlinel-theme'),
        __('Agencies', 'airlinel-theme'),
        'manage_options',
        'edit.php?post_type=agencies'
    );

    // Reservations submenu
    add_submenu_page(
        'airlinel-dashboard',
        __('Reservations', 'airlinel-theme'),
        __('Reservations', 'airlinel-theme'),
        'manage_options',
        'airlinel-reservations',
        'airlinel_reservations_page'
    );

    // Pricing Zones submenu
    add_submenu_page(
        'airlinel-dashboard',
        __('Pricing Zones', 'airlinel-theme'),
        __('Pricing Zones', 'airlinel-theme'),
        'manage_options',
        'airlinel-zones',
        'airlinel_zones_page'
    );

    // Exchange Rates submenu
    add_submenu_page(
        'airlinel-dashboard',
        __('Exchange Rates', 'airlinel-theme'),
        __('Exchange Rates', 'airlinel-theme'),
        'manage_options',
        'airlinel-exchange-rates',
        'airlinel_exchange_rates_callback'
    );

    // Homepage Content submenu
    add_submenu_page(
        'airlinel-dashboard',
        __('Homepage Content', 'airlinel-theme'),
        __('Homepage Content', 'airlinel-theme'),
        'manage_options',
        'airlinel-homepage-content',
        'airlinel_homepage_content_page_callback'
    );

    // Pages & Content submenu
    add_submenu_page(
        'airlinel-dashboard',
        __('Pages & Content', 'airlinel-theme'),
        __('Pages & Content', 'airlinel-theme'),
        'manage_options',
        'airlinel-page-content',
        'airlinel_page_content_settings_callback'
    );

    // Analytics submenu
    add_submenu_page(
        'airlinel-dashboard',
        __('Analytics', 'airlinel-theme'),
        __('Analytics', 'airlinel-theme'),
        'manage_options',
        'airlinel-analytics',
        'airlinel_analytics_callback'
    );

    // Ads.txt Manager submenu
    add_submenu_page(
        'airlinel-dashboard',
        __('Ads.txt Manager', 'airlinel-theme'),
        __('Ads.txt Manager', 'airlinel-theme'),
        'manage_options',
        'airlinel-ads-txt',
        'airlinel_ads_txt_page'
    );

    // Database Migrations submenu
    add_submenu_page(
        'airlinel-dashboard',
        __('Database Migrations', 'airlinel-theme'),
        __('Database Migrations', 'airlinel-theme'),
        'manage_options',
        'airlinel-migrations',
        'airlinel_database_migrations_page'
    );

    // Regional/Sync specific submenus
    if (defined('AIRLINEL_MAIN_SITE_URL') && defined('AIRLINEL_IS_REGIONAL_SITE') && AIRLINEL_IS_REGIONAL_SITE) {
        // Regional site menus
        add_submenu_page(
            'airlinel-dashboard',
            __('Regional Settings', 'airlinel-theme'),
            __('Regional Settings', 'airlinel-theme'),
            'manage_options',
            'airlinel-regional-settings',
            'airlinel_regional_settings_page'
        );

        add_submenu_page(
            'airlinel-dashboard',
            __('Sync Dashboard', 'airlinel-theme'),
            __('Sync Dashboard', 'airlinel-theme'),
            'manage_options',
            'airlinel-sync-dashboard',
            'airlinel_sync_dashboard_callback'
        );
    } else if (!defined('AIRLINEL_MAIN_SITE_URL')) {
        // Main site only menus
        add_submenu_page(
            'airlinel-dashboard',
            __('Regional Sites', 'airlinel-theme'),
            __('Regional Sites', 'airlinel-theme'),
            'manage_options',
            'airlinel-regional-sites',
            'airlinel_regional_sites_page'
        );

        add_submenu_page(
            'airlinel-dashboard',
            __('Regional API Settings', 'airlinel-theme'),
            __('Regional API Settings', 'airlinel-theme'),
            'manage_options',
            'airlinel-regional-api-settings',
            'airlinel_regional_api_settings_page'
        );
    }
}

// Dashboard page callback
function airlinel_dashboard_page() {
    include get_template_directory() . '/admin/dashboard-page.php';
}

// Settings page callback
function airlinel_settings_page() {
    $settings = new Airlinel_Settings_Manager();
    $settings->render_page();
}

// Page content callback
function airlinel_page_content_settings_callback() {
    require_once get_template_directory() . '/admin/page-content-settings.php';
    airlinel_page_content_settings_page();
}

// Regional API Settings page callback
function airlinel_regional_api_settings_page() {
    require_once get_template_directory() . '/admin/regional-api-settings-page.php';
    airlinel_render_regional_api_settings_page();
}

// Reservations page callback
function airlinel_reservations_page() {
    require_once get_template_directory() . '/includes/class-reservation-manager.php';
    require_once get_template_directory() . '/admin/reservations-page.php';
}

// Zones page callback
function airlinel_zones_page() {
    require_once get_template_directory() . '/includes/class-zone-manager.php';
    require_once get_template_directory() . '/admin/zones-page.php';
}

// ADS.txt page callback
function airlinel_ads_txt_page() {
    require_once get_template_directory() . '/admin/ads-txt-page.php';
}

// Database Migrations page callback
function airlinel_database_migrations_page() {
    require_once get_template_directory() . '/admin/database-migrations-page.php';
}

// Regional Settings page callback (for regional sites)
function airlinel_regional_settings_page() {
    require_once get_template_directory() . '/admin/regional-settings.php';
}

// Regional Sites page callback (for main site)
function airlinel_regional_sites_page() {
    $settings = new Airlinel_Settings_Manager();
    $settings->render_regional_sites_page();
}

// ===== ENQUEUE ADMIN STYLES & SCRIPTS =====
add_action('admin_enqueue_scripts', function($hook) {
    // Enqueue dashboard CSS on all Airlinel admin pages
    if (strpos($hook, 'airlinel-') !== false) {
        wp_enqueue_style('airlinel-dashboard', get_template_directory_uri() . '/assets/css/admin-dashboard.css', array(), '1.0');
    }

    // Enqueue ADS.TXT specific scripts
    if ($hook !== 'settings_page_airlinel-ads-txt') {
        return;
    }

    wp_enqueue_script('airlinel-ads-txt', get_template_directory_uri() . '/assets/js/ads-txt-admin.js', array('jquery'), '1.0', true);
    wp_enqueue_style('airlinel-ads-txt', get_template_directory_uri() . '/assets/css/ads-txt-admin.css', array(), '1.0');

    wp_localize_script('airlinel-ads-txt', 'airlinel_ads_txt', array(
        'nonce' => wp_create_nonce('airlinel_nonce'),
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
});


// Exchange rates AJAX handlers - moved to TASK 3.5 section below

// ===== AGENCY VERIFICATION AJAX =====
add_action('wp_ajax_nopriv_verify_agency', 'airlinel_verify_agency_ajax');
add_action('wp_ajax_verify_agency', 'airlinel_verify_agency_ajax');

function airlinel_verify_agency_ajax() {
    // Verify nonce if provided
    if (!empty($_POST['nonce'])) {
        check_ajax_referer('airlinel_nonce', 'nonce');
    }

    if (empty($_POST['code'])) {
        wp_send_json_error(array('message' => 'Agency code is required'));
        return;
    }

    $code = sanitize_text_field($_POST['code']);
    $mgr = new Airlinel_Agency_Manager();
    $agency = $mgr->verify($code);

    if (!$agency) {
        wp_send_json_error(array('message' => 'Invalid agency code'));
        return;
    }

    wp_send_json_success($agency);
}

// ===== ADMIN RESERVATIONS AJAX HANDLERS (TASK 1.6) =====
add_action('wp_ajax_airlinel_update_reservation_status', 'airlinel_update_reservation_status_ajax');

function airlinel_update_reservation_status_ajax() {
    check_ajax_referer('airlinel_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
        return;
    }

    if (empty($_POST['id']) || empty($_POST['status'])) {
        wp_send_json_error(array('message' => 'Missing required parameters'));
        return;
    }

    $id = intval($_POST['id']);
    $status = sanitize_text_field($_POST['status']);

    $mgr = new Airlinel_Reservation_Manager();
    $result = $mgr->update_reservation_status($id, $status);

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
        return;
    }

    wp_send_json_success(array('message' => 'Status updated successfully'));
}

add_action('wp_ajax_airlinel_bulk_update_reservations', 'airlinel_bulk_update_reservations_ajax');

function airlinel_bulk_update_reservations_ajax() {
    check_ajax_referer('airlinel_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
        return;
    }

    if (empty($_POST['ids']) || empty($_POST['status'])) {
        wp_send_json_error(array('message' => 'Missing required parameters'));
        return;
    }

    $ids = array_map('intval', (array) $_POST['ids']);
    $status = sanitize_text_field($_POST['status']);

    $mgr = new Airlinel_Reservation_Manager();
    $result = $mgr->bulk_update_status($ids, $status);

    wp_send_json_success(array(
        'message' => 'Updated ' . $result['updated'] . ' reservations',
        'updated' => $result['updated'],
    ));
}

add_action('wp_ajax_airlinel_get_reservation_details', 'airlinel_get_reservation_details_ajax');

function airlinel_get_reservation_details_ajax() {
    check_ajax_referer('airlinel_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
        return;
    }

    if (empty($_POST['id'])) {
        wp_send_json_error(array('message' => 'Missing reservation ID'));
        return;
    }

    $id = intval($_POST['id']);
    $mgr = new Airlinel_Reservation_Manager();
    $reservation = $mgr->get_reservation($id);

    if (!$reservation) {
        wp_send_json_error(array('message' => 'Reservation not found'));
        return;
    }

    // Generate modal HTML
    ob_start();
    ?>
    <!-- Customer Information -->
    <div class="modal-section">
        <h3>Customer Information</h3>
        <div class="modal-field">
            <label>Name:</label>
            <value><?php echo esc_html($reservation['customer_name']); ?></value>
        </div>
        <div class="modal-field">
            <label>Email:</label>
            <value><?php echo esc_html($reservation['email']); ?></value>
        </div>
        <div class="modal-field">
            <label>Phone:</label>
            <value><?php echo esc_html($reservation['phone']); ?></value>
        </div>
    </div>

    <!-- Route Information -->
    <div class="modal-section">
        <h3>Route Information</h3>
        <div class="modal-field">
            <label>Pickup Location:</label>
            <value><?php echo esc_html($reservation['pickup_location']); ?></value>
        </div>
        <div class="modal-field">
            <label>Dropoff Location:</label>
            <value><?php echo esc_html($reservation['dropoff_location']); ?></value>
        </div>
        <div class="modal-field">
            <label>Transfer Date:</label>
            <value><?php echo !empty($reservation['transfer_date']) ? esc_html(date('Y-m-d', strtotime($reservation['transfer_date']))) : 'N/A'; ?></value>
        </div>
        <div class="modal-field">
            <label>Distance:</label>
            <value><?php echo !empty($reservation['distance']) ? esc_html(number_format($reservation['distance'], 2)) . ' km' : 'N/A'; ?></value>
        </div>
        <div class="modal-field">
            <label>Passengers:</label>
            <value><?php echo esc_html($reservation['passengers']); ?></value>
        </div>
    </div>

    <!-- Pricing Information -->
    <div class="modal-section">
        <h3>Pricing Information</h3>
        <div class="modal-field">
            <label>Base Price:</label>
            <value>£<?php echo esc_html(number_format($reservation['base_price'], 2)); ?></value>
        </div>
        <div class="modal-field">
            <label>Fleet Multiplier:</label>
            <value><?php echo esc_html(number_format($reservation['multiplier'], 2)); ?>x</value>
        </div>
        <div class="modal-field">
            <label>Total Price (GBP):</label>
            <value>£<?php echo esc_html(number_format($reservation['total_price_gbp'], 2)); ?></value>
        </div>
        <div class="modal-field">
            <label>Currency:</label>
            <value><?php echo esc_html($reservation['currency']); ?></value>
        </div>
        <div class="modal-field">
            <label>Exchange Rate:</label>
            <value><?php echo esc_html(number_format($reservation['exchange_rate'], 4)); ?></value>
        </div>
    </div>

    <!-- Payment Information -->
    <div class="modal-section">
        <h3>Payment Information</h3>
        <div class="modal-field">
            <label>Payment Status:</label>
            <value><?php echo esc_html(ucfirst($reservation['payment_status'])); ?></value>
        </div>
        <div class="modal-field">
            <label>Stripe Intent ID:</label>
            <value style="font-family: monospace; font-size: 11px;"><?php echo esc_html($reservation['stripe_intent_id']); ?></value>
        </div>
        <div class="modal-field">
            <label>Stripe Charge ID:</label>
            <value style="font-family: monospace; font-size: 11px;"><?php echo esc_html($reservation['stripe_charge_id']); ?></value>
        </div>
    </div>

    <!-- Agency Information -->
    <div class="modal-section">
        <h3>Agency Information</h3>
        <div class="modal-field">
            <label>Agency Code:</label>
            <value><?php echo esc_html($reservation['agency_code']); ?></value>
        </div>
        <div class="modal-field">
            <label>Commission Type:</label>
            <value><?php echo esc_html(ucfirst($reservation['commission_type'])); ?></value>
        </div>
        <div class="modal-field">
            <label>Agency Commission:</label>
            <value>£<?php echo esc_html(number_format($reservation['agency_commission'], 2)); ?></value>
        </div>
    </div>

    <!-- Special Requests / Notes -->
    <div class="modal-section">
        <h3>Special Requests & Notes</h3>
        <label for="notes-field">Edit Notes:</label>
        <textarea id="notes-field"><?php echo esc_textarea($reservation['special_requests']); ?></textarea>
        <button type="button" class="button button-primary save-notes-btn" data-id="<?php echo intval($reservation['id']); ?>" style="margin-top: 10px;">Save Notes</button>
    </div>
    <?php
    $html = ob_get_clean();

    wp_send_json_success(array('html' => $html));
}

add_action('wp_ajax_airlinel_update_reservation_notes', 'airlinel_update_reservation_notes_ajax');

function airlinel_update_reservation_notes_ajax() {
    check_ajax_referer('airlinel_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
        return;
    }

    if (empty($_POST['id'])) {
        wp_send_json_error(array('message' => 'Missing reservation ID'));
        return;
    }

    $id = intval($_POST['id']);
    $notes = sanitize_textarea_field($_POST['notes'] ?? '');

    $mgr = new Airlinel_Reservation_Manager();
    $result = $mgr->update_notes($id, $notes);

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
        return;
    }

    wp_send_json_success(array('message' => 'Notes updated successfully'));
}

add_action('wp_ajax_airlinel_export_reservations_csv', 'airlinel_export_reservations_csv_ajax');

function airlinel_export_reservations_csv_ajax() {
    check_ajax_referer('airlinel_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }

    // Get filter values
    $status = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : '';
    $payment_status = isset($_GET['filter_payment_status']) ? sanitize_text_field($_GET['filter_payment_status']) : '';
    $country = isset($_GET['filter_country']) ? sanitize_text_field($_GET['filter_country']) : '';
    $date_from = isset($_GET['filter_date_from']) ? sanitize_text_field($_GET['filter_date_from']) : '';
    $date_to = isset($_GET['filter_date_to']) ? sanitize_text_field($_GET['filter_date_to']) : '';
    $search = isset($_GET['filter_search']) ? sanitize_text_field($_GET['filter_search']) : '';

    $mgr = new Airlinel_Reservation_Manager();
    $result = $mgr->get_reservations(array(
        'status' => $status,
        'payment_status' => $payment_status,
        'country' => $country,
        'date_from' => $date_from,
        'date_to' => $date_to,
        'search' => $search,
        'per_page' => 500,
    ));

    $csv = $mgr->export_csv($result['reservations']);

    // Send CSV file
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="reservations_' . date('Y-m-d_H-i-s') . '.csv"');
    echo $csv;
    wp_die();
}

// Include Agency Management System
require_once get_template_directory() . '/inc/agency-system.php';

// Blog Kategorileri için Resim + SEO Description Desteği
require_once get_template_directory() . '/inc/category-image-support.php';

if ( ! function_exists( 'airlinel_setup' ) ) :
    function airlinel_setup() {
        // ===== TEXT DOMAIN SETUP (TASK 3.1) =====
        // Load text domain for theme translations
        // Language is configured via WPLANG in wp-config.php or via language settings plugin
        load_theme_textdomain( 'airlinel-theme', get_template_directory() . '/languages' );

        // Otomatik feed bağlantıları desteği
        add_theme_support( 'automatic-feed-links' );

        // WordPress'in başlık etiketini otomatik yönetmesi
        add_theme_support( 'title-tag' );

        // Yazılar ve sayfalar için öne çıkan görsel (Thumbnail) desteği
        add_theme_support( 'post-thumbnails' );

        // Menü yerlerini tanımlayalım (Daha sonra WP Panelden yönetmek istersen)
        register_nav_menus( array(
            'primary' => esc_html__( 'Primary Menu', 'airlinel-theme' ),
        ) );

        // HTML5 desteği
        add_theme_support( 'html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        ) );
    }
endif;
add_action( 'after_setup_theme', 'airlinel_setup' );

/**
 * CSS ve JS dosyalarını dahil etme
 */
function airlinel_scripts() {
    // Ana style.css dosyamızı çağırıyoruz
    wp_enqueue_style( 'airlinel-style', get_stylesheet_uri(), array(), _S_VERSION );
    // Compiled Tailwind CSS (CDN yerine production build)
    wp_enqueue_style( 'airlinel-tailwind', get_template_directory_uri() . '/airlinel-tailwind.css', array(), _S_VERSION );

    // Booking page styles (Task 1.5)
    if ( is_page_template( 'page-booking.php' ) ) {
        wp_enqueue_style( 'airlinel-booking', get_template_directory_uri() . '/assets/css/booking.css', array(), _S_VERSION );
    }

    // Stripe.js — loaded in <head> for PCI compliance
    wp_enqueue_script( 'stripe-js', 'https://js.stripe.com/v3/', array(), null, false );

    // Header language & currency selectors (Task 2)
    wp_enqueue_script( 'airlinel-header-selectors', get_template_directory_uri() . '/assets/js/header-selectors.js', array(), _S_VERSION, true );

    // Localize script with AJAX URL
    wp_localize_script( 'airlinel-header-selectors', 'ajaxurl', admin_url( 'admin-ajax.php' ) );

    // Eğer harici bir JS dosyan olursa buraya ekleyebilirsin
}
add_action( 'wp_enqueue_scripts', 'airlinel_scripts' );

// LCP image preload - hero image'ı öncelikli yükle
function airlinel_preload_lcp_image() {
    $img_url = get_template_directory_uri() . '/images/theme-image-009.webp';
    echo '<link rel="preload" fetchpriority="high" as="image" href="' . esc_url($img_url) . '">' . "\n";
}
add_action( 'wp_head', 'airlinel_preload_lcp_image', 2 );

// DNS prefetch for external CDN - Booking sayfasında kullanılan paketler
function airlinel_dns_prefetch_cdn() {
    echo '<link rel="dns-prefetch" href="//cdn.jsdelivr.net">' . "\n";
}
add_action( 'wp_head', 'airlinel_dns_prefetch_cdn', 3 );

// Browser cache headers - tüm statik dosyalar için
function airlinel_add_cache_headers() {
    if ( !is_admin() ) {
        header( 'Cache-Control: public, max-age=31536000, immutable', false );
    }
}
add_action( 'send_headers', 'airlinel_add_cache_headers' );

// Not: jQuery defer edilemiyor - booking.js tüm işlevselliği jQuery'e bağımlı

/**
 * Blog yazıları için özet uzunluğunu ayarlayalım (Anasayfadaki 8 adet blog için)
 */
function airlinel_excerpt_length( $length ) {
    return 15; // Özet kısmında kaç kelime görünsün?
}
add_filter( 'excerpt_length', 'airlinel_excerpt_length', 999 );

/**
 * Tema için sabit değişken tanımlama
 */
if ( ! defined( '_S_VERSION' ) ) {
    define( '_S_VERSION', '1.0.0' );
}

// Admin panelinde görsel hataları engellemek için bazen gerekebilir
function airlinel_admin_styles() {
    echo '<style>#wpadminbar { z-index: 99999 !important; }</style>';
}
add_action('wp_head', 'airlinel_admin_styles');

// Menü linklerine tema varsayılan sınıfları ekle
define('AIRLINEL_MENU_LINK_CLASSES', 'text-[var(--dark-text-color)] hover:text-[var(--primary-color)] transition-colors');

function airlinel_nav_menu_link_attributes($atts, $item, $args, $depth) {
    if ( 'primary' === $args->theme_location ) {
        $classes = AIRLINEL_MENU_LINK_CLASSES;
        // depth 0 is top level
        if ( $depth === 0 ) {
            if ( strpos($args->menu_class, 'mobile-menu') !== false ) {
                // mobile menu padding
                $classes .= ' px-3 py-2 text-base font-medium';
            } else {
                $classes .= ' text-sm font-medium';
            }
        }
        $atts['class'] = isset($atts['class']) ? trim($atts['class'].' '.$classes) : $classes;
    }
    return $atts;
}
add_filter('nav_menu_link_attributes', 'airlinel_nav_menu_link_attributes', 10, 4);


// 3. Ülke Bazlı Ayarlar (General Settings altına basit bir alan ekleyelim)
function chauffeur_register_settings() {
    register_setting('chauffeur_options', 'chauffeur_rates');
}
add_action('admin_init', 'chauffeur_register_settings');


function chauffeur_enqueue_google_maps() {
    $api_key = Airlinel_Settings_Manager::get('airlinel_google_maps_key');
    if (empty($api_key)) {
        if (WP_DEBUG) {
            error_log('Google Maps API key not configured in settings');
        }
        return;
    }
    // footer=true: render-blocking'i önlemek için footer'a taşındı
    wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($api_key) . '&libraries=places&v=weekly', array(), null, true);

    // Load booking scripts:
    // - assets/js/booking.js: Her sayfada (vehicles fetch, currency, form tracking)
    // - js/booking.js: Home page'de EK olarak (Google Places autocomplete için)
    wp_enqueue_script('chauffeur-booking', get_template_directory_uri() . '/assets/js/booking.js', array('jquery', 'google-maps', 'stripe-js'), null, true);

    if (is_front_page() || is_home()) {
        wp_enqueue_script('chauffeur-booking-home', get_template_directory_uri() . '/js/booking.js', array('jquery', 'google-maps', 'chauffeur-booking'), null, true);
    }

    // Enqueue form tracker for booking analytics (Task 11)
    wp_enqueue_script('airlinel-form-tracker', get_template_directory_uri() . '/assets/js/form-tracker.js', array('jquery'), null, true);

    // Enqueue intl-tel-input for phone number country code selector
    wp_enqueue_script('intl-tel-input', 'https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.3/build/js/intlTelInput.min.js', array('jquery'), '17.0.3', true);
    wp_enqueue_style('intl-tel-input', 'https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.3/build/css/intlTelInput.css', array(), '17.0.3');

    // Localize script data AFTER enqueueing
    // stripe-js is already enqueued in <head> via airlinel_scripts()
    wp_localize_script('chauffeur-booking', 'chauffeur_data', array(
        'ajax_url'       => admin_url('admin-ajax.php'),
        'nonce'          => wp_create_nonce('airlinel_booking_payment'),
        'stripe_pub_key' => Airlinel_Settings_Manager::get('airlinel_stripe_pub_key'),
    ));
}

// strip any dominant-color attributes/styles from thumbnail HTML
function airlinel_strip_dominant_color($html, $post_id, $post_attachment_id, $size, $attr) {
    $html = preg_replace('/\sdata-dominant-color="[^"]*"/', '', $html);
    $html = preg_replace('/\sstyle="--dominant-color:[^;\"]*;?"/', '', $html);
    return $html;
}
add_filter('post_thumbnail_html','airlinel_strip_dominant_color',10,5);

/**
 * AJAX handler to save language preference to WordPress option
 */
function airlinel_save_language_ajax() {
    // Check if language parameter is provided
    if ( !isset($_POST['lang']) ) {
        wp_send_json_error('No language specified');
    }

    $lang = sanitize_text_field($_POST['lang']);

    // Validate language against supported languages
    $supported = array(
        'en_US', 'tr_TR', 'de_DE', 'ru_RU', 'fr_FR', 'it_IT', 'ar',
        'da_DK', 'nl_NL', 'sv_SE', 'zh_CN', 'ja', 'es_ES'
    );

    if ( !in_array($lang, $supported, true) ) {
        wp_send_json_error('Unsupported language');
    }

    // Update WordPress WPLANG option
    update_option('WPLANG', $lang);

    wp_send_json_success(array('lang' => $lang));
}

// Hook AJAX for both logged in and non-logged in users
add_action('wp_ajax_airlinel_save_language', 'airlinel_save_language_ajax');
add_action('wp_ajax_nopriv_airlinel_save_language', 'airlinel_save_language_ajax');

add_action('wp_enqueue_scripts', 'chauffeur_enqueue_google_maps');

/**
 * Display vehicles from main site in HTML format
 * @param array $vehicles Array of vehicle data from main site API
 * @param float $distance Distance for reference
 * @param string $country Country code
 */
function chauffeur_display_vehicles_html($vehicles, $distance, $country) {
    if (empty($vehicles)) {
        echo '<div class="text-center py-20"><p class="text-white text-lg">No vehicles available for this route.</p><p class="text-gray-400 text-sm mt-2">Please try a different route or contact support.</p></div>';
        return;
    }

    echo '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 w-full">';

    foreach ($vehicles as $vehicle) {
        // Extract vehicle data from main site response - support both old and new formats
        $vehicle_id = isset($vehicle['post_id']) ? $vehicle['post_id'] : (isset($vehicle['id']) ? $vehicle['id'] : 'vehicle-' . rand(1000, 9999));
        $vehicle_name = isset($vehicle['title']) ? $vehicle['title'] : (isset($vehicle['name']) ? $vehicle['name'] : 'Vehicle');
        $vehicle_price = isset($vehicle['price']) ? floatval($vehicle['price']) : 0;
        $vehicle_passengers = isset($vehicle['passengers']) ? intval($vehicle['passengers']) : 4;
        $vehicle_luggage = isset($vehicle['luggage']) ? intval($vehicle['luggage']) : 3;
        $vehicle_image = isset($vehicle['image_url']) ? $vehicle['image_url'] : (isset($vehicle['image']) ? $vehicle['image'] : '');

        $formatted_price = number_format($vehicle_price, 2);
        $km_cost_base = isset($vehicle['km_cost_base']) ? floatval($vehicle['km_cost_base']) : 0;

        ?>
<div class="vehicle-card group bg-white border border-gray-100 rounded-[2rem] overflow-hidden hover:shadow-2xl transition-all duration-500 flex flex-col h-full shadow-sm">
    <div class="relative p-6 h-56 flex items-center justify-center overflow-hidden bg-white">
        <?php if (!empty($vehicle_image)) : ?>
            <img src="<?php echo esc_url($vehicle_image); ?>" alt="<?php echo esc_attr($vehicle_name); ?>" class="max-w-[90%] max-h-[90%] object-contain rounded-2xl group-hover:scale-110 transition-transform duration-700">
        <?php endif; ?>
        <div class="absolute top-4 right-4">
            <span class="bg-[var(--primary-color)] text-black text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-tighter"><?php _e('Premium', 'airlinel-theme'); ?></span>
        </div>
    </div>

    <div class="p-6 flex flex-col flex-grow bg-white">
        <div class="mb-4">
            <h3 class="text-xl font-bold text-gray-900 leading-tight mb-1"><?php echo esc_html($vehicle_name); ?></h3>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                <p class="text-gray-400 text-xs uppercase tracking-widest font-medium"><?php _e('Available Now', 'airlinel-theme'); ?></p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 mb-6">
            <div class="bg-gray-50 border border-gray-100 p-3 rounded-2xl flex items-center justify-center gap-3">
                <i class="fa fa-user text-[var(--primary-color)] text-sm"></i>
                <span class="text-gray-700 text-xs font-bold"><?php echo $vehicle_passengers; ?> <?php _e('Passengers', 'airlinel-theme'); ?></span>
            </div>
            <div class="bg-gray-50 border border-gray-100 p-3 rounded-2xl flex items-center justify-center gap-3">
                <i class="fa fa-briefcase text-[var(--primary-color)] text-sm"></i>
                <span class="text-gray-700 text-xs font-bold"><?php echo $vehicle_luggage; ?> <?php _e('Luggage', 'airlinel-theme'); ?></span>
            </div>
        </div>

        <div class="mt-auto pt-5 border-t border-gray-100 flex items-center justify-between gap-4">
            <div>
                <span class="text-gray-400 text-[10px] block uppercase font-bold mb-1"><?php _e('Total Price', 'airlinel-theme'); ?></span>
                <span class="vehicle-price text-3xl font-black text-gray-900 tracking-tighter" data-base-price="<?php echo $formatted_price; ?>">£<?php echo $formatted_price; ?></span>
            </div>
            <button onclick="bookingStepTwo('<?php echo esc_attr($vehicle_id); ?>', '<?php echo esc_attr($formatted_price); ?>', '<?php echo esc_attr($vehicle_name); ?>', '<?php echo esc_attr(number_format($km_cost_base, 2)); ?>')"
                    class="bg-black hover:bg-[var(--primary-color)] text-white hover:text-black font-black py-4 px-8 rounded-2xl transition-all duration-300 text-xs uppercase tracking-widest shadow-lg active:scale-95">
                <?php _e('Select', 'airlinel-theme'); ?>
            </button>
        </div>
    </div>
</div>
        <?php
    }

    echo '</div>';
}

// AJAX: fetch fleet vehicles with calculated prices
// hooked to both logged-in and guest users below
function chauffeur_fetch_vehicles_with_prices() {
    // Mesafe verisi – AJAX POST'tan ya da URL GET'ten gel
    $distance = 0;
    if (isset($_POST['distance'])) {
        $distance = floatval($_POST['distance']);
    } elseif (isset($_GET['distance'])) {
        $distance = floatval($_GET['distance']);
    }

    // Hangi ülke için fiyatlama yapılacak? AJAX POST'tan ya da URL GET'ten gel
    $country = 'TR';
    if (isset($_POST['country'])) {
        $country = sanitize_text_field($_POST['country']);
    } elseif (isset($_GET['country'])) {
        $country = sanitize_text_field($_GET['country']);
    }

    // Ülke kodunu normalize et ve doğrula
    $country = strtoupper($country);
    if ( $country === 'GB' ) {
        $country = 'UK';
    }
    if ( ! in_array($country, array('TR','UK'), true) ) {
        $country = 'TR';
    }

    // Debug logging only in WP_DEBUG mode
    if (WP_DEBUG) {
        error_log("chauffeur_fetch_vehicles: distance={$distance}, country={$country}");
    }

    // Check if this is a regional site - use proxy to main site if configured
    $is_regional_site = false;
    $main_site_url = '';
    $api_key = '';

    if (class_exists('Airlinel_Regional_Settings_Manager')) {
        $settings_mgr = new Airlinel_Regional_Settings_Manager();
        $main_site_url = $settings_mgr->get_main_site_url();
        $api_key = $settings_mgr->get_api_key();
    }

    if (empty($main_site_url) && defined('AIRLINEL_MAIN_SITE_URL')) {
        $main_site_url = AIRLINEL_MAIN_SITE_URL;
    }

    if (empty($api_key) && defined('AIRLINEL_MAIN_SITE_API_KEY')) {
        $api_key = AIRLINEL_MAIN_SITE_API_KEY;
    }

    $is_regional_site = !empty($main_site_url) && !empty($api_key);

    // Log detailed diagnostic info
    error_log("[Airlinel] fetch_vehicles diagnostic:");
    error_log("[Airlinel]   is_regional_site: " . ($is_regional_site ? 'YES' : 'NO'));
    error_log("[Airlinel]   main_site_url: " . ($main_site_url ? $main_site_url : 'EMPTY'));
    error_log("[Airlinel]   api_key: " . ($api_key ? 'SET (len=' . strlen($api_key) . ')' : 'EMPTY'));
    error_log("[Airlinel]   AIRLINEL_MAIN_SITE_URL constant: " . (defined('AIRLINEL_MAIN_SITE_URL') ? AIRLINEL_MAIN_SITE_URL : 'NOT DEFINED'));
    error_log("[Airlinel]   AIRLINEL_MAIN_SITE_API_KEY constant: " . (defined('AIRLINEL_MAIN_SITE_API_KEY') ? 'DEFINED' : 'NOT DEFINED'));

    // Also check raw database options
    $db_main_url = get_option('airlinel_regional_main_site_url', '');
    $db_api_key = get_option('airlinel_regional_api_key', '');
    error_log("[Airlinel]   Database option airlinel_regional_main_site_url: " . ($db_main_url ? $db_main_url : 'EMPTY'));
    error_log("[Airlinel]   Database option airlinel_regional_api_key: " . ($db_api_key ? 'SET (len=' . strlen($db_api_key) . ')' : 'EMPTY'));

    // If this is a regional site, use proxy to get vehicles from main site
    if ($is_regional_site && class_exists('Airlinel_Main_Site_Client')) {
        // Try to get pickup and dropoff from POST data
        $pickup = isset($_POST['pickup']) ? sanitize_text_field($_POST['pickup']) : '';
        $dropoff = isset($_POST['dropoff']) ? sanitize_text_field($_POST['dropoff']) : '';

        error_log("[Airlinel] Regional site detected - proxying vehicle search to main site (pickup={$pickup}, dropoff={$dropoff}, country={$country})");

        if (!empty($pickup) && !empty($dropoff)) {
            // Create a minimal proxy request to main site
            // Note: The main site /search endpoint requires different parameters than we have
            // So we'll use the API proxy handler's REST endpoint instead
            $main_url = !empty($main_site_url) ? rtrim($main_site_url, '/') : '';

            if (!empty($main_url) && !empty($api_key)) {
                // Call main site REST API directly
                $api_url = $main_url . '/wp-json/airlinel-proxy/v1/search';

                $request_body = array(
                    'pickup' => $pickup,
                    'dropoff' => $dropoff,
                    'distance' => $distance,
                    'country' => $country,
                    'passengers' => 1,
                    'currency' => 'GBP',
                );

                error_log("[Airlinel] Calling main site API at {$api_url}");

                $response = wp_remote_post($api_url, array(
                    'method' => 'POST',
                    'timeout' => 30,
                    'headers' => array(
                        'Content-Type' => 'application/json',
                        'x-api-key' => $api_key,
                    ),
                    'sslverify' => true,
                    'body' => wp_json_encode($request_body),
                ));

                if (is_wp_error($response)) {
                    error_log("[Airlinel] Main site API error: " . $response->get_error_message());
                } else {
                    $http_code = wp_remote_retrieve_response_code($response);
                    $body = wp_remote_retrieve_body($response);
                    $data = json_decode($body, true);

                    error_log("[Airlinel] Main site API response code: {$http_code}");

                    if ($http_code === 200 && isset($data['data'])) {
                        error_log("[Airlinel] Successfully got " . count($data['data']) . " vehicles from main site");
                        chauffeur_display_vehicles_html($data['data'], $distance, $country);
                        wp_die();
                    } else {
                        error_log("[Airlinel] Invalid response from main site: " . substr($body, 0, 200));
                    }
                }
            }
        } else {
            error_log("[Airlinel] Pickup or dropoff missing for regional site search (pickup='{$pickup}', dropoff='{$dropoff}')");
        }

        // Fallback to local vehicles if proxy fails
        error_log("[Airlinel] Falling back to local vehicles");
    } else {
        error_log("[Airlinel] Not a regional site - using local vehicles (is_regional_site={$is_regional_site}, class_exists=" . (class_exists('Airlinel_Main_Site_Client') ? 'yes' : 'no') . ")");
    }

    // Use local vehicles (main site or regional site without proxy)
    // Admin panelinden kaydettiğimiz ücret ayarları
    $rates = get_option('chauffeur_rates') ?: [];

    // Seçilen ülkeye göre base ve km ücretini alıyoruz (default values if missing)
    $base_rate = isset($rates[$country]['base']) ? floatval($rates[$country]['base']) : 10;
    $km_rate   = isset($rates[$country]['km']) ? floatval($rates[$country]['km']) : 1;

    // query published fleet vehicles – note post_status not status
    $args = array(
        'post_type'      => 'fleet',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    );

    $query = new WP_Query($args);

    if (WP_DEBUG) {
        error_log("chauffeur_fetch_vehicles: Found " . $query->found_posts . " fleet posts");
    }

    if ($query->have_posts()) {
        // Collect vehicles with calculated prices for sorting
        $vehicles = array();
        
        while ($query->have_posts()) {
            $query->the_post();
            
            // Araç çarpanını çek (Örn: Sedan 1.0, VIP 1.5)
            $multiplier = get_post_meta(get_the_ID(), '_fleet_multiplier', true) ?: 1.0;

            // Araç özelliklerini çek
            $passengers = intval(get_post_meta(get_the_ID(), '_fleet_passengers', true)) ?: 4;
            $luggage = intval(get_post_meta(get_the_ID(), '_fleet_luggage', true)) ?: 3;
            
            // Resimleri al - birden fazla boyutta dene
            $thumbnail_html = '';
            if (has_post_thumbnail(get_the_ID())) {
                $thumbnail_html = get_the_post_thumbnail(get_the_ID(), 'large', array(
                    'class' => 'max-w-[90%] max-h-[90%] object-contain rounded-2xl group-hover:scale-110 transition-transform duration-700'
                ));
                
                // Eğer large boyut yoksa medium_large veya full dene
                if (empty($thumbnail_html)) {
                    $thumbnail_html = get_the_post_thumbnail(get_the_ID(), 'medium_large', array(
                        'class' => 'max-w-[90%] max-h-[90%] object-contain rounded-2xl group-hover:scale-110 transition-transform duration-700'
                    ));
                }
                if (empty($thumbnail_html)) {
                    $thumbnail_html = get_the_post_thumbnail(get_the_ID(), 'full', array(
                        'class' => 'max-w-[90%] max-h-[90%] object-contain rounded-2xl group-hover:scale-110 transition-transform duration-700'
                    ));
                }
            }

            if (WP_DEBUG) {
                error_log("Fleet post " . get_the_ID() . " thumbnail: " . strlen($thumbnail_html) . " bytes");
            }

            // FORMÜL: (Base Rate + (Mesafe * KM Ücreti)) * Araç Çarpanı
            $km_cost_base = $distance * $km_rate;
            $calculated_price = ($base_rate + $km_cost_base) * $multiplier;

            $vehicles[] = array(
                'post_id'    => get_the_ID(),
                'title'      => get_the_title(),
                'thumbnail'  => $thumbnail_html,
                'price'      => $calculated_price,
                'km_cost_base' => $km_cost_base * $multiplier,
                'multiplier' => $multiplier,
                'passengers' => $passengers,
                'luggage'    => $luggage,
            );
        }
        
        // Sort vehicles by price (cheapest first)
        usort($vehicles, function($a, $b) {
            return $a['price'] <=> $b['price'];
        });
        
        echo '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 w-full">';
        
        foreach ($vehicles as $vehicle) {
            $formatted_price = number_format($vehicle['price'], 2);
            ?>
<div class="vehicle-card group bg-white border border-gray-100 rounded-[2rem] overflow-hidden hover:shadow-2xl transition-all duration-500 flex flex-col h-full shadow-sm">
    
    <div class="relative p-6 h-56 flex items-center justify-center overflow-hidden bg-white">
        <?php if (!empty($vehicle['thumbnail'])) : ?>
            <?php echo $vehicle['thumbnail']; ?>
        <?php endif; ?>
        
        <div class="absolute top-4 right-4">
            <span class="bg-[var(--primary-color)] text-black text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-tighter"><?php _e('Premium', 'airlinel-theme'); ?></span>
        </div>
    </div>
    
    <div class="p-6 flex flex-col flex-grow bg-white">
        <div class="mb-4">
            <h3 class="text-xl font-bold text-gray-900 leading-tight mb-1"><?php echo esc_html($vehicle['title']); ?></h3>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                <p class="text-gray-400 text-xs uppercase tracking-widest font-medium"><?php _e('Available Now', 'airlinel-theme'); ?></p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 mb-6">
            <div class="bg-gray-50 border border-gray-100 p-3 rounded-2xl flex items-center justify-center gap-3">
                <i class="fa fa-user text-[var(--primary-color)] text-sm"></i>
                <span class="text-gray-700 text-xs font-bold"><?php echo $vehicle['passengers']; ?> <?php _e('Passengers', 'airlinel-theme'); ?></span>
            </div>
            <div class="bg-gray-50 border border-gray-100 p-3 rounded-2xl flex items-center justify-center gap-3">
                <i class="fa fa-briefcase text-[var(--primary-color)] text-sm"></i>
                <span class="text-gray-700 text-xs font-bold"><?php echo $vehicle['luggage']; ?> <?php _e('Luggage', 'airlinel-theme'); ?></span>
            </div>
        </div>

        <div class="mt-auto pt-5 border-t border-gray-100 flex items-center justify-between gap-4">
            <div>
                <span class="text-gray-400 text-[10px] block uppercase font-bold mb-1"><?php _e('Total Price', 'airlinel-theme'); ?></span>
                <span class="vehicle-price text-3xl font-black text-gray-900 tracking-tighter" data-base-price="<?php echo $formatted_price; ?>">£<?php echo $formatted_price; ?></span>
            </div>
            <button onclick="bookingStepTwo('<?php echo $vehicle['post_id']; ?>', '<?php echo $formatted_price; ?>', '<?php echo esc_attr($vehicle['title']); ?>', '<?php echo number_format($vehicle['km_cost_base'], 2); ?>')"
                    class="bg-black hover:bg-[var(--primary-color)] text-white hover:text-black font-black py-4 px-8 rounded-2xl transition-all duration-300 text-xs uppercase tracking-widest shadow-lg active:scale-95">
                <?php _e('Select', 'airlinel-theme'); ?>
            </button>
        </div>
    </div>
</div>
            <?php
        }
        
        echo '</div>';
    } else {
        echo '<div class="text-center py-20"><p class="text-white text-lg">No vehicles available for this route.</p><p class="text-gray-400 text-sm mt-2">Please try a different distance or contact support.</p></div>';
        if (WP_DEBUG) {
            error_log("chauffeur_fetch_vehicles: No fleet posts found!");
        }
    }
    wp_reset_postdata();
    wp_die();
}
add_action('wp_ajax_fetch_vehicles', 'chauffeur_fetch_vehicles_with_prices');
add_action('wp_ajax_nopriv_fetch_vehicles', 'chauffeur_fetch_vehicles_with_prices');

function chauffeur_setup_reservations() {
    register_post_type('reservations', [
        'labels' => ['name' => 'Rezervasyonlar', 'singular_name' => 'Rezervasyon'],
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-calendar-alt',
        'supports' => ['title', 'editor', 'custom-fields'],
    ]);
}
add_action('init', 'chauffeur_setup_reservations');


// Stripe API ve Ödeme Fonksiyonu
function chauffeur_create_stripe_session() {
    require_once get_template_directory() . '/inc/stripe-php/init.php';

    // Retrieve Stripe secret key from settings
    $stripe_secret_key = Airlinel_Settings_Manager::get('airlinel_stripe_secret_key');
    if (empty($stripe_secret_key)) {
        wp_send_json_error(['message' => 'Stripe configuration is incomplete']);
        return;
    }
    \Stripe\Stripe::setApiKey($stripe_secret_key);

    // Formdan gelen veriler
    $amount = floatval($_POST['total_price']);
    $vehicle_name = sanitize_text_field($_POST['vehicle_name']);
    $customer_name = sanitize_text_field($_POST['passenger_name']);

    try {
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'gbp',
                    'product_data' => [
                        'name' => 'Transfer: ' . $vehicle_name,
                        'description' => 'Customer: ' . $customer_name,
                    ],
                    'unit_amount' => $amount * 100, // Pence cinsinden
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => home_url('/success?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url' => home_url('/booking'),
        ]);

        wp_send_json_success(['id' => $session->id]);
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}
add_action('wp_ajax_create_stripe_session', 'chauffeur_create_stripe_session');
add_action('wp_ajax_nopriv_create_stripe_session', 'chauffeur_create_stripe_session');


// ===== INLINE STRIPE PAYMENT: Create reservation + PaymentIntent in one call =====
add_action('wp_ajax_nopriv_airlinel_create_booking_payment', 'airlinel_create_booking_payment');
add_action('wp_ajax_airlinel_create_booking_payment', 'airlinel_create_booking_payment');

function airlinel_create_booking_payment() {
    check_ajax_referer('airlinel_booking_payment', 'nonce');

    // Sanitise all inputs
    $passenger_name   = sanitize_text_field($_POST['passenger_name']   ?? '');
    $passenger_email  = sanitize_email($_POST['passenger_email']        ?? '');
    $passenger_phone  = sanitize_text_field($_POST['passenger_phone']  ?? '');
    $pickup_date      = sanitize_text_field($_POST['pickup_date']       ?? '');
    $pickup_time      = sanitize_text_field($_POST['pickup_time']       ?? '');
    $flight_number    = sanitize_text_field($_POST['flight_number']     ?? '');
    $agency_code      = sanitize_text_field($_POST['agency_code']       ?? '');
    $notes            = sanitize_textarea_field($_POST['notes']         ?? '');
    $vehicle_id       = intval($_POST['vehicle_id']                     ?? 0);
    $total_price      = floatval($_POST['total_price']                  ?? 0);
    $currency         = strtoupper(sanitize_text_field($_POST['currency']  ?? 'GBP'));
    $country          = strtoupper(sanitize_text_field($_POST['country']   ?? 'UK'));
    $pickup_location  = sanitize_text_field($_POST['pickup_location']   ?? '');
    $dropoff_location = sanitize_text_field($_POST['dropoff_location']  ?? '');

    // Required fields
    if (empty($passenger_name) || empty($passenger_email) || empty($passenger_phone)) {
        wp_send_json_error(['message' => 'Please fill in your Name, Email and Phone number.']);
        return;
    }
    if (empty($pickup_location) || empty($dropoff_location)) {
        wp_send_json_error(['message' => 'Pickup and drop-off locations are required.']);
        return;
    }
    if ($total_price <= 0) {
        wp_send_json_error(['message' => 'Invalid booking price.']);
        return;
    }

    // Normalise country (reservation handler only accepts UK / TR)
    if (!in_array($country, ['UK', 'TR'], true)) {
        $country = 'UK';
    }

    // Normalise currency
    if (!in_array($currency, ['GBP', 'EUR', 'TRY', 'USD'], true)) {
        $currency = 'GBP';
    }

    // Ensure pickup_date is a valid future (or today) date
    if (empty($pickup_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $pickup_date)) {
        $pickup_date = date('Y-m-d', strtotime('+1 day'));
    }

    // Create the reservation record
    require_once get_template_directory() . '/includes/class-reservation-handler.php';
    $handler        = new Airlinel_Reservation_Handler();
    $reservation_id = $handler->create([
        'customer_name'    => $passenger_name,
        'email'            => $passenger_email,
        'phone'            => $passenger_phone,
        'pickup_location'  => $pickup_location,
        'dropoff_location' => $dropoff_location,
        'transfer_date'    => $pickup_date,
        'total_price'      => $total_price,
        'currency'         => $currency,
        'country'          => $country,
        'passengers'       => 1,
        'agency_code'      => $agency_code,
    ]);

    if (is_wp_error($reservation_id)) {
        wp_send_json_error(['message' => $reservation_id->get_error_message()]);
        return;
    }

    // Save optional fields as post-meta
    if ($flight_number) update_post_meta($reservation_id, 'flight_number',  $flight_number);
    if ($notes)         update_post_meta($reservation_id, 'booking_notes',  $notes);
    if ($pickup_time)   update_post_meta($reservation_id, 'pickup_time',    $pickup_time);
    if ($vehicle_id)    update_post_meta($reservation_id, 'vehicle_id',     $vehicle_id);

    // Create Stripe PaymentIntent
    require_once get_template_directory() . '/includes/class-payment-processor.php';
    $processor = new Airlinel_Payment_Processor();
    $intent    = $processor->create_payment_intent($reservation_id, $total_price, $currency, $passenger_email);

    if (is_wp_error($intent)) {
        // Roll back reservation so the customer can retry cleanly
        wp_delete_post($reservation_id, true);
        wp_send_json_error(['message' => 'Payment setup failed. Please try again.']);
        error_log('Airlinel payment intent error: ' . $intent->get_error_message());
        return;
    }

    wp_send_json_success([
        'client_secret'  => $intent['client_secret'],
        'reservation_id' => $reservation_id,
    ]);
}



function chauffeur_rates_page() {
    ?>
    <div class="wrap">
        <h1>Ülke Bazlı Fiyatlandırma</h1>
        <form method="post" action="options.php">
            <?php settings_fields('chauffeur_options'); ?>
            <?php $rates = get_option('chauffeur_rates') ?: []; ?>
            <table class="form-table">
                <tr>
                    <th>Türkiye (Base + KM Başı)</th>
                    <td>
                        Base: <input type="number" name="chauffeur_rates[TR][base]" value="<?php echo $rates['TR']['base']; ?>"> 
                        KM: <input type="number" name="chauffeur_rates[TR][km]" value="<?php echo $rates['TR']['km']; ?>">
                    </td>
                </tr>
                <tr>
                    <th>İngiltere (Base + KM Başı)</th>
                    <td>
                        Base: <input type="number" name="chauffeur_rates[UK][base]" value="<?php echo $rates['UK']['base']; ?>"> 
                        KM: <input type="number" name="chauffeur_rates[UK][km]" value="<?php echo $rates['UK']['km']; ?>">
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}



// SEO: LocalBusiness schema (front page only)
function airlinel_local_business_schema() {
    if ( ! is_front_page() ) {
        return;
    }
    $schema = array(
        '@context' => 'https://schema.org',
        '@type'    => array( 'LocalBusiness', 'TaxiService' ),
        '@id'      => 'https://airlinel.com/#LocalBusiness',
        'name'     => 'Airlinel Airport Transfer Service',
        'url'      => 'https://airlinel.com',
        'image'    => 'https://airlinel.com/wp-content/uploads/2025/09/airport-shuttle-london-8.webp',
        'description' => 'Premium London airport transfer and VIP chauffeur service.',
        'telephone'   => '+44 20 3411 2421',
        'email'       => 'booking@airlinel.com',
        'address'     => array(
            '@type'           => 'PostalAddress',
            'streetAddress'   => '86-90 Paul Street',
            'addressLocality' => 'London',
            'postalCode'      => 'EC2A 4NE',
            'addressCountry'  => 'GB',
        ),
        'geo' => array(
            '@type'     => 'GeoCoordinates',
            'latitude'  => 51.5237,
            'longitude' => -0.0863,
        ),
        'openingHoursSpecification' => array(
            array(
                '@type'     => 'OpeningHoursSpecification',
                'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ),
                'opens'     => '00:00',
                'closes'    => '23:59',
            ),
        ),
        'areaServed' => array(
            array( '@type' => 'City', 'name' => 'London' ),
            array( '@type' => 'City', 'name' => 'Manchester' ),
        ),
    );
    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
}
add_action( 'wp_head', 'airlinel_local_business_schema' );

// SEO: hreflang + og overrides (priority 1 = before SEO plugins)
function airlinel_seo_meta_overrides() {
    // Mevcut sayfa URL'sini kullan (her sayfa kendi URL'sini gosterir)
    $current_url = esc_url( ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . strtok( $_SERVER['REQUEST_URI'], '?' ) );
    echo '<link rel="alternate" hreflang="en-gb" href="' . $current_url . '" />';
    echo '<link rel="alternate" hreflang="x-default" href="' . $current_url . '" />';
    if ( ! is_front_page() ) {
        return;
    }
    $title = esc_attr( get_bloginfo( 'name' ) . ' - London Airport Transfer & VIP Chauffeur Service' );
    $desc  = esc_attr( 'Book a premium London airport transfer or VIP chauffeur with Airlinel. Fixed fares, professional drivers, all airports covered.' );
    $image = esc_url( 'https://airlinel.com/wp-content/uploads/2025/09/airport-shuttle-london-8.webp' );
    echo '<meta property="og:title"        content="' . $title . '" />';
    echo '<meta property="og:image:type"   content="image/webp" />';
    echo '<meta property="og:image:width"  content="1920" />';
    echo '<meta property="og:image:height" content="1088" />';
    echo '<meta name="twitter:card"        content="summary_large_image" />';
    echo '<meta name="twitter:site"        content="@airlinel" />';
    echo '<meta name="twitter:title"       content="' . $title . '" />';
    echo '<meta name="twitter:description" content="' . $desc . '" />';
    echo '<meta name="twitter:image"       content="' . $image . '" />';
}
add_action( 'wp_head', 'airlinel_seo_meta_overrides', 1 );

/**
 * İçerik kaydedilirken H1 → H2'ye dönüştür
 * Tema zaten her sayfada kendi H1'ini üretiyor.
 * Editörde yanlışlıkla girilen H1'ler kaydedilirken otomatik H2 olur.
 */
function airlinel_convert_h1_to_h2( $data, $postarr ) {

    // Sadece post/page/revizyon için çalış
    if ( empty( $data['post_content'] ) ) {
        return $data;
    }

    // post_content içinde <h1...>...</h1> varsa <h2> yap
    $data['post_content'] = preg_replace(
        '/<h1(\b[^>]*)>(.*?)<\/h1>/is',
        '<h2$1>$2</h2>',
        $data['post_content']
    );

    return $data;
}
add_filter( 'wp_insert_post_data', 'airlinel_convert_h1_to_h2', 10, 2 );

/**
 * Kategori arşiv sayfalarında sayfalama varsa
 * Yoast SEO title ve description'ını dinamik güncelle.
 * Örnek: "Antalya Airport Transfer | Page 4 of 48 | AIRLINEL"
 */
function airlinel_yoast_category_paged_title( $title ) {
    $paged = (int) get_query_var( 'paged' );
    if ( $paged < 2 ) return $title;

    $max_pages = $GLOBALS['wp_query']->max_num_pages;

    // Kategori arşivi (şehirler)
    if ( is_category() ) {
        $category = get_queried_object();
        $cat_name = $category ? esc_html( $category->name ) : '';
        return sprintf(
            '%s Airport Transfer | Page %d of %d | AIRLINEL',
            $cat_name, $paged, $max_pages
        );
    }

    // Blog / News arşivi (index.php — is_home)
    if ( is_home() ) {
        return sprintf(
            'Latest News - Transfer & Chauffeur Updates | Page %d of %d | Airlinel',
            $paged, $max_pages
        );
    }

    return $title;
}
add_filter( 'wpseo_title', 'airlinel_yoast_category_paged_title', 20 );

function airlinel_yoast_category_paged_desc( $desc ) {
    $paged = (int) get_query_var( 'paged' );
    if ( $paged < 2 ) return $desc;

    $max_pages = $GLOBALS['wp_query']->max_num_pages;

    // Kategori arşivi (şehirler)
    if ( is_category() ) {
        $category = get_queried_object();
        $cat_name = $category ? esc_html( $category->name ) : 'city';
        return sprintf(
            'Browse %s airport transfer guides — page %d of %d. Private transfers, fixed rates, professional drivers. Book with AIRLINEL.',
            $cat_name, $paged, $max_pages
        );
    }

    // Blog / News arşivi
    if ( is_home() ) {
        return sprintf(
            'Airlinel news and airport transfer updates — page %d of %d. Routes, service tips, promotions and chauffeur guides for UK and Turkey.',
            $paged, $max_pages
        );
    }

    return $desc;
}
add_filter( 'wpseo_metadesc', 'airlinel_yoast_category_paged_desc', 20 );

/**
 * Sayfalanmış arşivlerde canonical'ı doğru ayarla.
 * Yoast bazen /page/N/ yerine ana URL'yi canonical olarak verir.
 */
function airlinel_yoast_category_paged_canonical( $canonical ) {
    $paged = (int) get_query_var( 'paged' );
    if ( $paged < 2 ) return $canonical;

    if ( is_category() || is_home() ) {
        return get_pagenum_link( $paged );
    }

    return $canonical;
}
add_filter( 'wpseo_canonical', 'airlinel_yoast_category_paged_canonical', 20 );

/**
 * Yoast hreflang düzeltmesi.
 * Yoast tüm sayfalarda hreflang'ı ana sayfa URL'siyle üretiyor.
 * Bu filter mevcut URL'yi kullanarak doğru hreflang çıktısı üretir.
 */
function airlinel_fix_hreflang( $links ) {
    // Sadece tek dil varsa (tek hreflang bloğu) müdahale et
    // Çok dilli site değilse Yoast zaten sadece en-gb + x-default üretiyor
    if ( empty( $links ) ) return $links;

    $current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    // Temizle: query string varsa at
    $current_url = strtok( $current_url, '?' );

    foreach ( $links as &$link ) {
        $link['url'] = $current_url;
    }

    return $links;
}
add_filter( 'wpseo_hreflang_links', 'airlinel_fix_hreflang', 20 );

/**
 * Yoast Organization schema bloğuna address ekle.
 * Google Rich Results validator "Missing field address (optional)" uyarısını kapatır.
 */
function airlinel_add_address_to_yoast_organization( $data ) {
    $data['address'] = array(
        '@type'           => 'PostalAddress',
        'streetAddress'   => '86-90 Paul Street',
        'addressLocality' => 'London',
        'postalCode'      => 'EC2A 4NE',
        'addressRegion'   => 'England',
        'addressCountry'  => 'GB',
    );
    return $data;
}
add_filter( 'wpseo_schema_organization', 'airlinel_add_address_to_yoast_organization', 20 );

/**
 * Yoast schema graph'indaki tüm WebPage türü bloklara image ekle.
 * wpseo_schema_webpage yerine wpseo_schema_graph kullanıyoruz çünkü
 * Yoast sayfa tipine göre farklı hook'lar kullanabiliyor (AboutPage, CollectionPage vb.)
 * Google Rich Results "Missing field image (optional)" uyarısını kapatır.
 */
function airlinel_add_image_to_yoast_graph( $graph_pieces, $context ) {
    $fallback_image = 'https://airlinel.com/wp-content/uploads/2025/09/airport-transfer-12.webp';

    // Featured image veya Yoast OG image'ı bul
    $image_url = '';
    if ( is_singular() && has_post_thumbnail() ) {
        $image_url = get_the_post_thumbnail_url( get_the_ID(), 'large' );
    }
    if ( ! $image_url && is_singular() ) {
        $og = get_post_meta( get_the_ID(), '_yoast_wpseo_opengraph-image', true );
        if ( $og ) $image_url = $og;
    }
    if ( ! $image_url ) {
        $image_url = $fallback_image;
    }

    // image eklenecek schema tipleri: WebPage türevleri + Article türevleri
    $target_types = array(
        'WebPage', 'AboutPage', 'CollectionPage', 'ContactPage',
        'FAQPage', 'ItemPage', 'MedicalWebPage', 'ProfilePage',
        'QAPage', 'RealEstateListing', 'SearchResultsPage',
        'Article', 'NewsArticle', 'BlogPosting', 'TechArticle',
    );

    foreach ( $graph_pieces as &$piece ) {
        if ( ! isset( $piece['@type'] ) ) continue;

        $types = (array) $piece['@type'];
        $is_target = ! empty( array_intersect( $types, $target_types ) );

        if ( $is_target && ! isset( $piece['image'] ) && ! isset( $piece['primaryImageOfPage'] ) ) {
            $piece['image'] = array(
                '@type'      => 'ImageObject',
                'url'        => esc_url( $image_url ),
                'contentUrl' => esc_url( $image_url ),
            );
        }
    }
    unset( $piece );

    return $graph_pieces;
}
add_filter( 'wpseo_schema_graph', 'airlinel_add_image_to_yoast_graph', 20, 2 );

/**
 * Yoast Article schema bloğuna image ekle.
 * Google Rich Results "Missing field image (optional)" - Article tipi için.
 */
function airlinel_add_image_to_yoast_article( $data ) {
    if ( isset( $data['image'] ) ) {
        return $data;
    }

    $image_url = '';

    // 1. Featured image
    if ( has_post_thumbnail() ) {
        $image_url = get_the_post_thumbnail_url( get_the_ID(), 'large' );
    }

    // 2. Yoast OG image meta
    if ( ! $image_url ) {
        $og = get_post_meta( get_the_ID(), '_yoast_wpseo_opengraph-image', true );
        if ( $og ) $image_url = $og;
    }

    // 3. Fallback
    if ( ! $image_url ) {
        $image_url = 'https://airlinel.com/wp-content/uploads/2025/09/airport-transfer-12.webp';
    }

    $data['image'] = array(
        '@type'      => 'ImageObject',
        'url'        => esc_url( $image_url ),
        'contentUrl' => esc_url( $image_url ),
    );

    return $data;
}
add_filter( 'wpseo_schema_article', 'airlinel_add_image_to_yoast_article', 20 );

// ===== ADS.TXT AJAX HANDLERS (TASK 1.7) =====
add_action('wp_ajax_airlinel_add_ads_txt_entry', 'airlinel_add_ads_txt_entry_ajax');

function airlinel_add_ads_txt_entry_ajax() {
    check_ajax_referer('airlinel_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
        return;
    }

    if (empty($_POST['domain']) || empty($_POST['pub_id']) || empty($_POST['relationship'])) {
        wp_send_json_error(array('message' => 'Missing required parameters'));
        return;
    }

    $mgr = new Airlinel_Ads_Txt_Manager();
    $result = $mgr->add_entry(
        sanitize_text_field($_POST['domain']),
        sanitize_text_field($_POST['pub_id']),
        sanitize_text_field($_POST['relationship']),
        sanitize_text_field($_POST['cert_id'] ?? '')
    );

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
        return;
    }

    wp_send_json_success(array('entry' => $result));
}

add_action('wp_ajax_airlinel_update_ads_txt_entry', 'airlinel_update_ads_txt_entry_ajax');

function airlinel_update_ads_txt_entry_ajax() {
    check_ajax_referer('airlinel_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
        return;
    }

    if (empty($_POST['id']) || empty($_POST['domain']) || empty($_POST['pub_id']) || empty($_POST['relationship'])) {
        wp_send_json_error(array('message' => 'Missing required parameters'));
        return;
    }

    $mgr = new Airlinel_Ads_Txt_Manager();

    // Validate entry ID
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    if (empty($id) || strlen($id) > 50) {
        wp_send_json_error(array('message' => 'Invalid entry ID'));
        return;
    }

    $result = $mgr->update_entry(
        $id,
        sanitize_text_field($_POST['domain']),
        sanitize_text_field($_POST['pub_id']),
        sanitize_text_field($_POST['relationship']),
        sanitize_text_field($_POST['cert_id'] ?? '')
    );

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
        return;
    }

    wp_send_json_success(array('message' => 'Entry updated successfully'));
}

add_action('wp_ajax_airlinel_delete_ads_txt_entry', 'airlinel_delete_ads_txt_entry_ajax');

function airlinel_delete_ads_txt_entry_ajax() {
    check_ajax_referer('airlinel_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
        return;
    }

    // Validate entry ID
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    if (empty($id) || strlen($id) > 50) {
        wp_send_json_error(array('message' => 'Invalid entry ID'));
        return;
    }

    $mgr = new Airlinel_Ads_Txt_Manager();
    $result = $mgr->delete_entry($id);

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
        return;
    }

    wp_send_json_success(array('message' => 'Entry deleted successfully'));
}

add_action('wp_ajax_airlinel_regenerate_ads_txt_file', 'airlinel_regenerate_ads_txt_file_ajax');

function airlinel_regenerate_ads_txt_file_ajax() {
    check_ajax_referer('airlinel_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
        return;
    }

    $mgr = new Airlinel_Ads_Txt_Manager();
    $result = $mgr->generate_file();

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
        return;
    }

    wp_send_json_success(array('message' => 'File regenerated successfully'));
}

// Homepage content page callback
function airlinel_homepage_content_page_callback() {
    include get_template_directory() . '/admin/homepage-content-page.php';
}

// ===== TASK 3.4: DATA SYNCHRONIZATION & MONITORING =====
// Load Data Sync Manager class
require_once get_template_directory() . '/includes/class-data-sync-manager.php';

// Sync dashboard page callback
function airlinel_sync_dashboard_callback() {
    include get_template_directory() . '/admin/sync-dashboard.php';
    airlinel_sync_dashboard();
}

// Exchange rates page callback
function airlinel_exchange_rates_callback() {
    include get_template_directory() . '/admin/exchange-rates-page.php';
    airlinel_exchange_rates_page();
}

// ===== TASK 3.6: ANALYTICS DASHBOARD ADMIN MENU =====
// Analytics page callback
function airlinel_analytics_callback() {
    include get_template_directory() . '/admin/analytics-page.php';
    airlinel_analytics_page();
}


// Initialize sync jobs on theme setup
add_action('after_setup_theme', function() {
    if (class_exists('Airlinel_Data_Sync_Manager')) {
        Airlinel_Data_Sync_Manager::schedule_sync_jobs();
    }
});

// Handle hourly vehicle sync via wp_cron
add_action('airlinel_hourly_vehicle_sync', function() {
    if (class_exists('Airlinel_Data_Sync_Manager')) {
        $sync_mgr = new Airlinel_Data_Sync_Manager();
        $sync_mgr->sync_vehicles();
    }
});

// Handle hourly exchange rate sync via wp_cron
add_action('airlinel_hourly_exchange_rate_sync', function() {
    if (class_exists('Airlinel_Data_Sync_Manager')) {
        $sync_mgr = new Airlinel_Data_Sync_Manager();
        // Log that sync is scheduled but no automatic API update
        $sync_mgr->log_sync_event('exchange_rates', 'info', 'Hourly sync check - no changes');
    }
});

// AJAX endpoint for manual sync operations
add_action('wp_ajax_airlinel_sync_manual_vehicles', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }

    if (class_exists('Airlinel_Data_Sync_Manager')) {
        $sync_mgr = new Airlinel_Data_Sync_Manager();
        $result = $sync_mgr->sync_vehicles();
        wp_send_json_success($result);
    } else {
        wp_send_json_error('Sync manager not available');
    }
});

// AJAX endpoint to get sync status
add_action('wp_ajax_airlinel_get_sync_status', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }

    if (class_exists('Airlinel_Data_Sync_Manager')) {
        $sync_mgr = new Airlinel_Data_Sync_Manager();
        $stats = $sync_mgr->get_sync_stats();
        wp_send_json_success($stats);
    } else {
        wp_send_json_error('Sync manager not available');
    }
});

// ===== TASK 3.5: AJAX HANDLERS FOR FRONT-END INTEGRATION =====
// Get exchange rates for currency conversion
add_action('wp_ajax_nopriv_get_exchange_rates', 'airlinel_get_exchange_rates_handler');
add_action('wp_ajax_get_exchange_rates', 'airlinel_get_exchange_rates_handler');

function airlinel_get_exchange_rates_handler() {
    // Default fallback rates if manager not available
    $defaults = array('GBP' => 1.0, 'EUR' => 0.86, 'TRY' => 32.5, 'USD' => 1.27);

    if (!class_exists('Airlinel_Exchange_Rate_Manager')) {
        wp_send_json_success($defaults);
        return;
    }

    $mgr = new Airlinel_Exchange_Rate_Manager();
    $rates = $mgr->get_rates();

    if (is_wp_error($rates)) {
        // Return defaults on error instead of failing
        wp_send_json_success($defaults);
        return;
    }

    wp_send_json_success($rates);
}

// ===== TASK 3.6: ANALYTICS AJAX ENDPOINTS =====
// Export analytics data to CSV
add_action('wp_ajax_airlinel_export_analytics_csv', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('error' => 'Insufficient permissions'));
    }

    check_ajax_referer('airlinel_analytics_nonce', 'nonce');

    if (!class_exists('Airlinel_Analytics_Manager')) {
        wp_send_json_error(array('error' => 'Analytics manager not available'));
    }

    $site_id = isset($_POST['site_id']) ? sanitize_text_field($_POST['site_id']) : null;
    $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : null;
    $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : null;

    $analytics = new Airlinel_Analytics_Manager();
    $csv = $analytics->export_to_csv($site_id, $start_date, $end_date);

    wp_send_json_success(array('csv' => $csv));
});

// Get analytics summary data
add_action('wp_ajax_airlinel_get_analytics_summary', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('error' => 'Insufficient permissions'));
    }

    check_ajax_referer('airlinel_analytics_nonce', 'nonce');

    if (!class_exists('Airlinel_Analytics_Manager')) {
        wp_send_json_error(array('error' => 'Analytics manager not available'));
    }

    $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : null;
    $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : null;

    $analytics = new Airlinel_Analytics_Manager();
    $summary = $analytics->get_analytics_summary($start_date, $end_date);

    wp_send_json_success($summary);
});

// Get bookings by site
add_action('wp_ajax_airlinel_get_bookings_by_site', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('error' => 'Insufficient permissions'));
    }

    check_ajax_referer('airlinel_analytics_nonce', 'nonce');

    if (!class_exists('Airlinel_Analytics_Manager')) {
        wp_send_json_error(array('error' => 'Analytics manager not available'));
    }

    $site_id = isset($_POST['site_id']) ? sanitize_text_field($_POST['site_id']) : null;
    $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : null;
    $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : null;

    $analytics = new Airlinel_Analytics_Manager();
    $result = $analytics->get_bookings_by_site($site_id, $start_date, $end_date, array('per_page' => -1));

    wp_send_json_success($result);
});

// ===== TASK 3.7: CONTENT PAGES & BLOG MANAGEMENT =====
// Load Page Manager class for editable content and SEO
require_once get_template_directory() . '/includes/class-page-manager.php';


// Register SEO meta boxes for pages
add_action('add_meta_boxes', 'airlinel_register_seo_meta_boxes');

function airlinel_register_seo_meta_boxes() {
    $screens = array('page', 'post');

    foreach ($screens as $screen) {
        add_meta_box(
            'airlinel_seo_meta',
            __('SEO Information', 'airlinel-theme'),
            'airlinel_render_seo_meta_box',
            $screen,
            'normal',
            'high'
        );
    }
}

function airlinel_render_seo_meta_box($post) {
    $page_mgr = new Airlinel_Page_Manager();
    $seo_data = $page_mgr::get_seo_meta($post->ID);

    wp_nonce_field('airlinel_seo_meta_nonce', 'airlinel_seo_nonce');
    ?>
    <div class="airlinel-seo-meta-box" style="padding: 10px 0;">
        <!-- SEO Title -->
        <div class="airlinel-meta-field" style="margin-bottom: 20px;">
            <label for="airlinel_seo_title" style="display: block; margin-bottom: 5px; font-weight: bold;">
                <?php _e('SEO Title', 'airlinel-theme'); ?>
            </label>
            <input
                type="text"
                id="airlinel_seo_title"
                name="airlinel_seo_title"
                value="<?php echo esc_attr($seo_data['seo_title']); ?>"
                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                placeholder="Enter SEO title (50-60 characters)"
                maxlength="70"
            />
            <small style="display: block; margin-top: 5px; color: #666;">
                Recommended: 50-60 characters. Current: <span id="seo_title_count">0</span> characters
            </small>
        </div>

        <!-- Meta Description -->
        <div class="airlinel-meta-field" style="margin-bottom: 20px;">
            <label for="airlinel_seo_description" style="display: block; margin-bottom: 5px; font-weight: bold;">
                <?php _e('Meta Description', 'airlinel-theme'); ?>
            </label>
            <textarea
                id="airlinel_seo_description"
                name="airlinel_seo_description"
                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; height: 60px; font-family: inherit;"
                placeholder="Enter meta description (150-160 characters)"
                maxlength="160"
            ><?php echo esc_textarea($seo_data['seo_description']); ?></textarea>
            <small style="display: block; margin-top: 5px; color: #666;">
                Recommended: 150-160 characters. Current: <span id="seo_description_count">0</span> characters
            </small>
        </div>

        <!-- Focus Keyword -->
        <div class="airlinel-meta-field" style="margin-bottom: 20px;">
            <label for="airlinel_focus_keyword" style="display: block; margin-bottom: 5px; font-weight: bold;">
                <?php _e('Focus Keyword', 'airlinel-theme'); ?>
            </label>
            <input
                type="text"
                id="airlinel_focus_keyword"
                name="airlinel_focus_keyword"
                value="<?php echo esc_attr($seo_data['focus_keyword']); ?>"
                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                placeholder="Main keyword for this page"
            />
            <small style="display: block; margin-top: 5px; color: #666;">
                Primary keyword for SEO optimization
            </small>
        </div>

        <!-- Open Graph Image -->
        <div class="airlinel-meta-field" style="margin-bottom: 20px;">
            <label for="airlinel_og_image" style="display: block; margin-bottom: 5px; font-weight: bold;">
                <?php _e('Open Graph Image', 'airlinel-theme'); ?>
            </label>
            <input
                type="url"
                id="airlinel_og_image"
                name="airlinel_og_image"
                value="<?php echo esc_url($seo_data['og_image']); ?>"
                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                placeholder="https://example.com/image.jpg"
            />
            <small style="display: block; margin-top: 5px; color: #666;">
                Image for social media sharing (recommended 1200x630px)
            </small>
        </div>

        <!-- Canonical URL -->
        <div class="airlinel-meta-field">
            <label for="airlinel_canonical_url" style="display: block; margin-bottom: 5px; font-weight: bold;">
                <?php _e('Canonical URL', 'airlinel-theme'); ?>
            </label>
            <input
                type="url"
                id="airlinel_canonical_url"
                name="airlinel_canonical_url"
                value="<?php echo esc_url($seo_data['canonical_url']); ?>"
                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                placeholder="<?php echo esc_url(get_permalink($post->ID)); ?>"
            />
            <small style="display: block; margin-top: 5px; color: #666;">
                Leave empty to use page URL
            </small>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const titleInput = document.getElementById('airlinel_seo_title');
        const descInput = document.getElementById('airlinel_seo_description');
        const titleCount = document.getElementById('seo_title_count');
        const descCount = document.getElementById('seo_description_count');

        if (titleInput) {
            titleInput.addEventListener('input', function() {
                titleCount.textContent = this.value.length;
            });
            titleCount.textContent = titleInput.value.length;
        }

        if (descInput) {
            descInput.addEventListener('input', function() {
                descCount.textContent = this.value.length;
            });
            descCount.textContent = descInput.value.length;
        }
    });
    </script>
    <?php
}

// Save SEO meta boxes
add_action('save_post', 'airlinel_save_seo_meta_box');

function airlinel_save_seo_meta_box($post_id) {
    // Verify nonce
    if (!isset($_POST['airlinel_seo_nonce']) || !wp_verify_nonce($_POST['airlinel_seo_nonce'], 'airlinel_seo_meta_nonce')) {
        return;
    }

    // Check user capabilities
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save SEO title
    if (isset($_POST['airlinel_seo_title'])) {
        update_post_meta($post_id, '_airlinel_seo_title', sanitize_text_field($_POST['airlinel_seo_title']));
    }

    // Save SEO description
    if (isset($_POST['airlinel_seo_description'])) {
        update_post_meta($post_id, '_airlinel_seo_description', sanitize_textarea_field($_POST['airlinel_seo_description']));
    }

    // Save focus keyword
    if (isset($_POST['airlinel_focus_keyword'])) {
        update_post_meta($post_id, '_airlinel_focus_keyword', sanitize_text_field($_POST['airlinel_focus_keyword']));
    }

    // Save OG image
    if (isset($_POST['airlinel_og_image'])) {
        update_post_meta($post_id, '_airlinel_og_image', esc_url_raw($_POST['airlinel_og_image']));
    }

    // Save canonical URL
    if (isset($_POST['airlinel_canonical_url'])) {
        update_post_meta($post_id, '_airlinel_canonical_url', esc_url_raw($_POST['airlinel_canonical_url']));
    }
}

// Output SEO meta tags in head
add_action('wp_head', 'airlinel_output_seo_meta_tags', 5);

function airlinel_output_seo_meta_tags() {
    if (!is_singular(array('page', 'post'))) {
        return;
    }

    $post_id = get_queried_object_id();
    if (!$post_id) {
        return;
    }

    $page_mgr = new Airlinel_Page_Manager();
    $seo_data = $page_mgr::get_seo_meta($post_id);

    // Output SEO title
    if (!empty($seo_data['seo_title'])) {
        echo "\n<!-- Airlinel SEO Meta Tags -->\n";
    }

    // Output meta description
    if (!empty($seo_data['seo_description'])) {
        echo '<meta name="description" content="' . esc_attr($seo_data['seo_description']) . '" />' . "\n";
    }

    // Output Open Graph tags
    if (!empty($seo_data['og_image'])) {
        echo '<meta property="og:image" content="' . esc_url($seo_data['og_image']) . '" />' . "\n";
        echo '<meta property="og:image:width" content="1200" />' . "\n";
        echo '<meta property="og:image:height" content="630" />' . "\n";
    }

    // Output canonical URL
    if (!empty($seo_data['canonical_url'])) {
        echo '<link rel="canonical" href="' . esc_url($seo_data['canonical_url']) . '" />' . "\n";
    }
}

// Contact form handler AJAX endpoint
add_action('wp_ajax_airlinel_submit_contact_form', 'airlinel_submit_contact_form_handler');

function airlinel_submit_contact_form_handler() {
    // Verify nonce
    check_ajax_referer('airlinel_contact_form_nonce', 'security');

    // Get and sanitize form data
    $name = isset($_POST['contact_name']) ? sanitize_text_field($_POST['contact_name']) : '';
    $email = isset($_POST['contact_email']) ? sanitize_email($_POST['contact_email']) : '';
    $phone = isset($_POST['contact_phone']) ? sanitize_text_field($_POST['contact_phone']) : '';
    $subject = isset($_POST['contact_subject']) ? sanitize_text_field($_POST['contact_subject']) : '';
    $message = isset($_POST['contact_message']) ? sanitize_textarea_field($_POST['contact_message']) : '';

    // Validate required fields
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        wp_send_json_error(array('message' => 'Please fill in all required fields.'));
        return;
    }

    // Validate email
    if (!is_email($email)) {
        wp_send_json_error(array('message' => 'Please enter a valid email address.'));
        return;
    }

    // Get site contact email for sending responses
    $page_mgr = new Airlinel_Page_Manager();
    $site_email = $page_mgr::get_office_email();
    if (empty($site_email) || !is_email($site_email)) {
        $site_email = get_option('admin_email');
    }

    // Prepare email content
    $subject_line = '[Contact Form] ' . wp_kses_post($subject);
    $body = "New contact form submission:\n\n";
    $body .= "Name: " . wp_kses_post($name) . "\n";
    $body .= "Email: " . wp_kses_post($email) . "\n";
    if (!empty($phone)) {
        $body .= "Phone: " . wp_kses_post($phone) . "\n";
    }
    $body .= "Subject: " . wp_kses_post($subject) . "\n";
    $body .= "---\n\n";
    $body .= "Message:\n" . wp_kses_post($message) . "\n\n";
    $body .= "---\n";
    $body .= "Sent from: " . get_bloginfo('url') . "\n";
    $body .= "Sent at: " . current_time('mysql') . "\n";

    // Send email to site admin
    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . wp_kses_post($name) . ' <' . sanitize_email($email) . '>',
        'Reply-To: ' . sanitize_email($email),
    );

    $mail_sent = wp_mail($site_email, $subject_line, $body, $headers);

    if (!$mail_sent) {
        wp_send_json_error(array('message' => 'Failed to send message. Please try again later.'));
        return;
    }

    // Send confirmation email to user
    $confirmation_subject = 'We received your message - ' . get_bloginfo('name');
    $confirmation_body = "Hi " . wp_kses_post($name) . ",\n\n";
    $confirmation_body .= "Thank you for contacting us. We have received your message and will get back to you as soon as possible.\n\n";
    $confirmation_body .= "Best regards,\n" . get_bloginfo('name') . " Team\n";

    wp_mail($email, $confirmation_subject, $confirmation_body, array('Content-Type: text/plain; charset=UTF-8'));

    // Log the submission (optional)
    if (function_exists('error_log')) {
        error_log('Contact form submission from ' . $email . ' at ' . current_time('mysql'));
    }

    wp_send_json_success(array('message' => 'Thank you for your message! We will contact you shortly.'));
}

// ========================================
// TASK 5: ANALYTICS FORM TRACKING AJAX HANDLERS
// ========================================

// Register AJAX handlers - no auth required for search/booking process
add_action('wp_ajax_nopriv_airlinel_log_form_start', 'airlinel_ajax_log_form_start');
add_action('wp_ajax_airlinel_log_form_start', 'airlinel_ajax_log_form_start');

add_action('wp_ajax_nopriv_airlinel_log_field_change', 'airlinel_ajax_log_field_change');
add_action('wp_ajax_airlinel_log_field_change', 'airlinel_ajax_log_field_change');

add_action('wp_ajax_nopriv_airlinel_update_form_customer', 'airlinel_ajax_update_form_customer');
add_action('wp_ajax_airlinel_update_form_customer', 'airlinel_ajax_update_form_customer');

add_action('wp_ajax_nopriv_airlinel_update_form_vehicle', 'airlinel_ajax_update_form_vehicle');
add_action('wp_ajax_airlinel_update_form_vehicle', 'airlinel_ajax_update_form_vehicle');

add_action('wp_ajax_nopriv_airlinel_mark_form_completed', 'airlinel_ajax_mark_form_completed');
add_action('wp_ajax_airlinel_mark_form_completed', 'airlinel_ajax_mark_form_completed');

/**
 * AJAX Handler: Log form start
 * Called when user selects a vehicle - creates initial booking form record
 *
 * POST params:
 * - pickup: pickup location
 * - dropoff: dropoff location
 * - distance: distance in km
 * - country: country code (UK, TR)
 * - language: language code (en, tr)
 * - site_source: which site initiated (main, regional)
 */
function airlinel_ajax_log_form_start() {
    $pickup = isset($_POST['pickup']) ? sanitize_text_field($_POST['pickup']) : '';
    $dropoff = isset($_POST['dropoff']) ? sanitize_text_field($_POST['dropoff']) : '';
    $distance = isset($_POST['distance']) ? floatval($_POST['distance']) : 0;
    $country = isset($_POST['country']) ? sanitize_text_field($_POST['country']) : '';
    $language = isset($_POST['language']) ? sanitize_text_field($_POST['language']) : 'en';
    $site_source = isset($_POST['site_source']) ? sanitize_text_field($_POST['site_source']) : 'main';
    $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';
    $website_id = isset($_POST['website_id']) ? sanitize_text_field($_POST['website_id']) : '';
    $website_language = isset($_POST['website_language']) ? sanitize_text_field($_POST['website_language']) : '';

    if (empty($pickup) || empty($dropoff) || empty($country)) {
        wp_send_json_error(array('message' => 'Missing required parameters'));
        return;
    }

    require_once(plugin_dir_path(__FILE__) . 'includes/class-analytics-tracker.php');

    $form_id = Airlinel_Analytics_Tracker::log_form_start($pickup, $dropoff, $distance, $country, $language, $site_source, $session_id, $website_id, $website_language);

    if ($form_id && $form_id > 0) {
        wp_send_json_success(array(
            'form_id' => $form_id,
            'session_id' => $session_id,
        ));
    } else {
        wp_send_json_error(array('message' => 'Failed to create form record'));
    }
}

/**
 * AJAX Handler: Log field change
 * Called on every form field blur/change
 * Records which field changed and what value was entered
 *
 * POST params:
 * - form_id: booking form ID (from log_form_start response)
 * - field_name: name of the field that changed
 * - field_value: value entered in the field
 */
function airlinel_ajax_log_field_change() {
    $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
    $field_name = isset($_POST['field_name']) ? sanitize_text_field($_POST['field_name']) : '';
    $field_value = isset($_POST['field_value']) ? sanitize_text_field($_POST['field_value']) : '';
    $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';

    if ($form_id <= 0) {
        wp_send_json_error(array('message' => 'Invalid form_id'));
        return;
    }

    if (empty($field_name)) {
        wp_send_json_error(array('message' => 'Missing field_name'));
        return;
    }

    require_once(plugin_dir_path(__FILE__) . 'includes/class-analytics-tracker.php');

    $result = Airlinel_Analytics_Tracker::log_field_change($form_id, $field_name, $field_value, $session_id);

    if ($result) {
        wp_send_json_success();
    } else {
        wp_send_json_error(array('message' => 'Failed to log field change'));
    }
}

/**
 * AJAX Handler: Update form with customer data
 * Called after customer fills in name, email, phone
 * Updates form stage to 'customer_info'
 *
 * POST params:
 * - form_id: booking form ID
 * - customer_name: customer's full name
 * - customer_email: customer's email address
 * - customer_phone: customer's phone number
 */
function airlinel_ajax_update_form_customer() {
    $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
    $customer_name = isset($_POST['customer_name']) ? sanitize_text_field($_POST['customer_name']) : '';
    $customer_email = isset($_POST['customer_email']) ? sanitize_email($_POST['customer_email']) : '';
    $customer_phone = isset($_POST['customer_phone']) ? sanitize_text_field($_POST['customer_phone']) : '';
    $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';

    if ($form_id <= 0) {
        wp_send_json_error(array('message' => 'Invalid form_id'));
        return;
    }

    require_once(plugin_dir_path(__FILE__) . 'includes/class-analytics-tracker.php');

    $result = Airlinel_Analytics_Tracker::update_form_with_customer_data($form_id, $customer_name, $customer_email, $customer_phone, $session_id);

    if ($result) {
        wp_send_json_success();
    } else {
        wp_send_json_error(array('message' => 'Failed to update customer data'));
    }
}

/**
 * AJAX Handler: Update form with vehicle selection
 * Called after user selects a vehicle
 * Updates form stage to 'booking_details' and records vehicle info
 *
 * POST params:
 * - form_id: booking form ID
 * - vehicle_id: ID of selected vehicle
 * - vehicle_name: name of selected vehicle
 * - vehicle_price: price for selected vehicle
 */
function airlinel_ajax_update_form_vehicle() {
    $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
    $vehicle_id = isset($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : 0;
    $vehicle_name = isset($_POST['vehicle_name']) ? sanitize_text_field($_POST['vehicle_name']) : '';
    $vehicle_price = isset($_POST['vehicle_price']) ? sanitize_text_field($_POST['vehicle_price']) : '';
    $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';

    if ($form_id <= 0) {
        wp_send_json_error(array('message' => 'Invalid form_id'));
        return;
    }

    require_once(plugin_dir_path(__FILE__) . 'includes/class-analytics-tracker.php');

    $result = Airlinel_Analytics_Tracker::update_form_with_vehicle($form_id, $vehicle_id, $vehicle_name, $vehicle_price, $session_id);

    if ($result) {
        wp_send_json_success();
    } else {
        wp_send_json_error(array('message' => 'Failed to update vehicle selection'));
    }
}

/**
 * AJAX Handler: Mark form completed
 * Called when user submits payment form (final step)
 * Updates form stage to 'completed'
 *
 * POST params:
 * - form_id: booking form ID
 */
function airlinel_ajax_mark_form_completed() {
    $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
    $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';

    if ($form_id <= 0) {
        wp_send_json_error(array('message' => 'Invalid form_id'));
        return;
    }

    require_once(plugin_dir_path(__FILE__) . 'includes/class-analytics-tracker.php');

    $result = Airlinel_Analytics_Tracker::mark_form_completed($form_id, $session_id);

    if ($result) {
        wp_send_json_success();
    } else {
        wp_send_json_error(array('message' => 'Failed to mark form completed'));
    }
}

// ========================================
// TASK 6: ADMIN DASHBOARD PAGES REGISTRATION
// ========================================

/**
 * Register admin menu for Airlinel Analytics Dashboard
 */
add_action('admin_menu', 'airlinel_register_analytics_admin_menu');

function airlinel_register_analytics_admin_menu() {
    // Main menu page
    add_menu_page(
        'Airlinel Analytics',                    // Page title
        'Airlinel Analytics',                    // Menu title
        'manage_options',                        // Capability required
        'airlinel-analytics',                    // Menu slug
        'airlinel_render_analytics_dashboard',   // Callback function
        'dashicons-chart-line',                  // Icon
        30                                       // Position in menu
    );

    // Submenu: Dashboard (also accessible from main menu)
    add_submenu_page(
        'airlinel-analytics',                    // Parent menu slug
        'Analytics Dashboard',                   // Page title
        'Dashboard',                             // Submenu title
        'manage_options',                        // Capability
        'airlinel-analytics',                    // Submenu slug (same as main = becomes main page)
        'airlinel_render_analytics_dashboard'    // Callback
    );

    // Submenu: Search Analytics
    add_submenu_page(
        'airlinel-analytics',
        'Search Analytics',
        'Search Analytics',
        'manage_options',
        'airlinel-analytics-search',
        'airlinel_render_search_analytics'
    );

    // Submenu: Booking Forms
    add_submenu_page(
        'airlinel-analytics',
        'Booking Forms',
        'Booking Forms',
        'manage_options',
        'airlinel-analytics-forms',
        'airlinel_render_form_analytics'
    );

    // Submenu: Form Field Changes
    add_submenu_page(
        'airlinel-analytics',
        'Form Field Changes',
        'Form Field Changes',
        'manage_options',
        'airlinel-analytics-fields',
        'airlinel_render_field_changes'
    );

    // Submenu: Database Migrations (from Task 0)
    add_submenu_page(
        'airlinel-analytics',
        'Database Migrations',
        'Database Migrations',
        'manage_options',
        'airlinel-migrations',
        'airlinel_render_migrations_page'
    );
}

/**
 * Render main analytics dashboard page
 * Callback for: airlinel-analytics menu
 */
function airlinel_render_analytics_dashboard() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }
    include(plugin_dir_path(__FILE__) . 'admin/pages/analytics-dashboard.php');
}

/**
 * Render search analytics page
 * Callback for: airlinel-analytics-search submenu
 */
function airlinel_render_search_analytics() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }
    include(plugin_dir_path(__FILE__) . 'admin/pages/search-analytics.php');
}

/**
 * Render booking forms analytics page
 * Callback for: airlinel-analytics-forms submenu
 */
function airlinel_render_form_analytics() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }
    include(plugin_dir_path(__FILE__) . 'admin/pages/form-analytics.php');
}

/**
 * Render form field changes page
 * Callback for: airlinel-analytics-fields submenu
 */
function airlinel_render_field_changes() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }
    include(plugin_dir_path(__FILE__) . 'admin/pages/field-changes.php');
}

/**
 * Render migrations page
 * Callback for: airlinel-migrations submenu (registered in Task 0)
 */
function airlinel_render_migrations_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }
    include(plugin_dir_path(__FILE__) . 'admin/pages/database-migrations.php');
}

/**
 * Render Generate MO Files page
 */
function airlinel_render_generate_mo_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }
    include(get_template_directory() . '/admin/generate-mo.php');
}

/**
 * Render Language Domains page
 * Callback for: airlinel-language-domains submenu
 */
function airlinel_render_language_domains_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }
    include(get_template_directory() . '/admin/pages/language-domains.php');
}

// ===== LANGUAGE DOMAINS AJAX HANDLERS =====

/**
 * AJAX Handler: Get language domain by language code
 * Allows frontend to lookup domain URL for redirect
 */
function airlinel_ajax_get_language_domain() {
    check_ajax_referer('airlinel_nonce', 'nonce');

    $language_code = sanitize_text_field($_POST['language_code'] ?? '');

    if (empty($language_code)) {
        wp_send_json_error('Language code is required');
    }

    $language_domains = new Airlinel_Language_Domains();
    $domain = $language_domains->get_domain_by_language($language_code);

    if ($domain && $domain->domain_url) {
        wp_send_json_success(array(
            'language_code' => $domain->language_code,
            'domain_url' => $domain->domain_url,
            'language_name' => $domain->language_name
        ));
    } else {
        wp_send_json_error('Domain not found for this language');
    }
}

add_action('wp_ajax_airlinel_get_language_domain', 'airlinel_ajax_get_language_domain');
add_action('wp_ajax_nopriv_airlinel_get_language_domain', 'airlinel_ajax_get_language_domain');

/**
 * Add Language Domains menu item to Airlinel admin
 */
add_action('admin_menu', function() {
    add_submenu_page(
        'airlinel-dashboard',
        'Dil Domainleri',
        'Dil Domainleri',
        'manage_options',
        'airlinel-language-domains',
        'airlinel_render_language_domains_page'
    );

    add_submenu_page(
        'airlinel-dashboard',
        'Çeviri (.mo) Oluştur',
        '🌐 Çeviri Oluştur',
        'manage_options',
        'airlinel-generate-mo',
        'airlinel_render_generate_mo_page'
    );
});

/**
 * Add AJAX nonce to frontend for language domain requests
 */
add_action('wp_footer', function() {
    if (!is_admin()) {
        ?>
        <script>
        // Add nonce for AJAX requests
        window.airinelNonce = '<?php echo wp_create_nonce('airlinel_nonce'); ?>';
        </script>
        <?php
    }
});
