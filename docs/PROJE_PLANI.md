# Airlinel.com - Havalimani Transfer Platform Projesi

> **Agentic Workers İçin:** Bu planı `superpowers:subagent-driven-development` ile task-by-task uygulayınız.

**Hedef:** SEO-optimized, multi-platform havalimani transfer reservation sistemi

**Mimarileri:**
- **Backend:** WordPress REST API (tema functions.php içinde - NO plugins)
- **Database:** MySQL (wp_options + wp_postmeta)
- **Admin:** Dynamic pricing/zones/settings management
- **Frontend:** React (Web), React Native (Mobile)
- **Payment:** Stripe 3D Secure
- **Languages:** EN, TR, DE, RU, FR, IT, AR, DA, NL, SV, ZH, JA

**Tech Stack:** WordPress 6.x, PHP 8.x, React Native, Stripe API, Google Maps API

---

## Tema Dosya Yapısı & Custom Post Types

### Theme Directory Structure

```
wp-content/themes/airlinel-transfer-services/
├── functions.php                    # Main functions + CPT definitions + AJAX handlers
├── header.php                       # Header template
├── footer.php                       # Footer template
├── index.php                        # Fallback template
├── page.php                         # Single page template
├── single.php                       # Single post template
├── category.php                     # Category template
├── front-page.php                   # Homepage template
├── page-booking.php                 # Booking page template (needs refactor)
├── page-cities.php                  # Cities page
├── page-fleet.php                   # Fleet showcase
├── page-partners.php                # Partners page
├── page-search.php                  # Search page
├── page-services.php                # Services page
├── homepage-cities-section.php      # Homepage cities section component
├── style.css                        # Theme styles (custom CSS)
├── airlinel-tailwind.css            # Compiled Tailwind CSS
├── screenshot.png                   # Theme screenshot
├── .htaccess                        # Server config
│
├── includes/                        # NEW: Core classes & functionality
│   ├── class-api-handler.php
│   ├── class-settings-manager.php
│   ├── class-reservation-handler.php
│   ├── class-pricing-engine.php
│   ├── class-zone-manager.php
│   ├── class-exchange-rate-manager.php
│   ├── class-agency-manager.php
│   └── class-payment-processor.php
│
├── admin/                           # NEW: Admin pages
│   ├── settings-page.php
│   ├── zones-page.php
│   └── exchange-rates-page.php
│
├── assets/                          # Assets
│   ├── js/
│   │   ├── booking.js               # Main booking form logic
│   │   ├── booking-form.js          # NEW: Enhanced booking form
│   │   └── api-client.js            # NEW: REST API client
│   ├── css/
│   │   └── booking.css              # NEW: Booking form styles
│   └── images/
│       ├── theme-image-*.webp       # Hero images
│       └── ...
│
├── inc/                             # EXISTING: Custom includes
│   ├── agency-system.php            # Agency management (existing)
│   ├── category-image-support.php   # Category images (existing)
│   └── stripe-php/                  # Stripe SDK
│
└── languages/                       # NEW: i18n translations
    ├── airlinel-theme-en_US.po
    ├── airlinel-theme-tr_TR.po
    ├── airlinel-theme-de_DE.po
    ├── airlinel-theme-ru_RU.po
    ├── airlinel-theme-fr_FR.po
    ├── airlinel-theme-it_IT.po
    ├── airlinel-theme-ar.po
    ├── airlinel-theme-da_DK.po
    ├── airlinel-theme-nl_NL.po
    ├── airlinel-theme-sv_SE.po
    ├── airlinel-theme-zh_CN.po
    └── airlinel-theme-ja.po
```

### Custom Post Types (CPT)

#### 1. **fleet** (Araçlar/Vehicles)
```
Post Type: fleet
Menu Icon: dashicons-car
Supports: title, thumbnail, editor
Visibility: public

Meta Fields:
- _vehicle_multiplier (float) - Price multiplier (e.g., Sedan 1.0, VIP 1.5)
- fleet_passengers (int) - Passenger capacity
- fleet_luggage (int) - Luggage capacity
```

#### 2. **reservations** (Rezervasyonlar/Bookings)
```
Post Type: reservations
Menu Icon: dashicons-calendar-alt
Supports: title, editor, custom-fields
Visibility: private (not show_ui initially)

Meta Fields:
- customer_name (string)
- email (string)
- phone (string)
- pickup_location (string)
- dropoff_location (string)
- transfer_date (datetime)
- passengers (int)
- currency (string) - GBP, EUR, TRY, USD
- country (string) - UK, TR
- fleet_id (int) - Vehicle selected
- total_price (float) - Final price
- payment_status (string) - pending, completed, failed
- agency_code (string) - Optional
- commission_type (string) - included, excluded
- agency_commission (float)
- stripe_intent_id (string)
- stripe_charge_id (string)
```

#### 3. **agencies** (Agencies - NEW)
```
Post Type: agencies
Menu Icon: dashicons-building
Supports: title, custom-fields
Visibility: private

Meta Fields:
- agency_code (string) - Unique code
- email (string)
- commission_percent (float)
- total_earnings (float)
- active (boolean)
```

### WordPress Options (Settings)

```
airlinel_api_key (string)                    - Internal API key
airlinel_google_maps_key (string)            - Google Maps API key
airlinel_stripe_publishable_key (string)    - Stripe public key
airlinel_stripe_secret_key (string)         - Stripe secret key (encrypted)

airlinel_base_currency (string)              - GBP (fixed)
airlinel_uk_km_rate (float)                 - Default km rate for UK (£/km)
airlinel_tr_km_rate (float)                 - Default km rate for TR (£/km)

airlinel_uk_zones (array)                   - UK pricing zones (JSON)
airlinel_tr_zones (array)                   - TR pricing zones (JSON)

airlinel_exchange_rates (array)              - Currency exchange rates
  {
    "GBP": 1.00,
    "EUR": 1.18,
    "TRY": 42.50,
    "USD": 1.27
  }

airlinel_rates_last_update (datetime)       - Last rate update timestamp
```

---

## PHASE 1: WordPress Core Sistemi

### Task 0: Tema Setup + CPT Definitions

**Sorumluluk:** Opus 4.7  
**Files:**
- Modify: `/wp-content/themes/airlinel-transfer-services/functions.php`
- Create: `/wp-content/themes/airlinel-transfer-services/includes/` (klasör)
- Create: `/wp-content/themes/airlinel-transfer-services/admin/` (klasör)
- Create: `/wp-content/themes/airlinel-transfer-services/assets/js/` (klasör)
- Create: `/wp-content/themes/airlinel-transfer-services/languages/` (klasör)

**Context:**
Tema zaten var. CPT'leri (fleet, reservations, agencies) tanımla, options setup, include yapısını düzenle.

- [ ] **Step 1: functions.php'ye CPT definitions ekle**

```php
// /wp-content/themes/airlinel-transfer-services/functions.php başına ekle:

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
    ));
}
add_action('init', 'airlinel_register_cpts');

// ===== FLEET META BOXES =====
function airlinel_add_fleet_metabox() {
    add_meta_box('fleet_details', 'Vehicle Details', 'airlinel_fleet_metabox_cb', 'fleet', 'normal');
}
add_action('add_meta_boxes', 'airlinel_add_fleet_metabox');

function airlinel_fleet_metabox_cb($post) {
    $multiplier = get_post_meta($post->ID, '_vehicle_multiplier', true) ?: '1.0';
    $passengers = get_post_meta($post->ID, 'fleet_passengers', true) ?: '4';
    $luggage = get_post_meta($post->ID, 'fleet_luggage', true) ?: '3';
    
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
    if (!isset($_POST['fleet_nonce']) || !wp_verify_nonce($_POST['fleet_nonce'], 'airlinel_fleet_nonce')) {
        return;
    }
    
    if (isset($_POST['multiplier'])) {
        update_post_meta($post_id, '_vehicle_multiplier', floatval($_POST['multiplier']));
    }
    if (isset($_POST['passengers'])) {
        update_post_meta($post_id, 'fleet_passengers', intval($_POST['passengers']));
    }
    if (isset($_POST['luggage'])) {
        update_post_meta($post_id, 'fleet_luggage', intval($_POST['luggage']));
    }
}
add_action('save_post_fleet', 'airlinel_save_fleet_meta');

// ===== FLUSH REWRITE RULES ON THEME ACTIVATION =====
function airlinel_flush_rewrite_on_activation() {
    airlinel_register_cpts();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'airlinel_flush_rewrite_on_activation');
```

- [ ] **Step 2: Tema klasörlerini oluştur**

Run:
```bash
mkdir -p wp-content/themes/airlinel-transfer-services/includes
mkdir -p wp-content/themes/airlinel-transfer-services/admin
mkdir -p wp-content/themes/airlinel-transfer-services/assets/js
mkdir -p wp-content/themes/airlinel-transfer-services/languages
```

- [ ] **Step 3: functions.php'ye include requires ekle**

```php
// functions.php başına (require_once'lar için):

// ===== CORE INCLUDES =====
// require_once get_template_directory() . '/includes/class-api-handler.php';
// require_once get_template_directory() . '/includes/class-settings-manager.php';
// ... (diğer includes aşamalı olarak Task 1.1'de eklenecek)
```

- [ ] **Step 4: Commit**

```bash
git add wp-content/themes/airlinel-transfer-services/functions.php
git mkdir -p wp-content/themes/airlinel-transfer-services/includes
git mkdir -p wp-content/themes/airlinel-transfer-services/admin
git mkdir -p wp-content/themes/airlinel-transfer-services/assets/js
git mkdir -p wp-content/themes/airlinel-transfer-services/languages
git commit -m "chore: setup theme structure and CPT definitions"
```

---

### Task 1.1: REST API + Settings Manager (Tema içinde)

**Sorumluluk:** Opus 4.7  
**Files:**
- Create: `/wp-content/themes/airlinel/includes/class-api-handler.php`
- Create: `/wp-content/themes/airlinel/includes/class-settings-manager.php`
- Create: `/wp-content/themes/airlinel/includes/class-reservation-handler.php`
- Modify: `/wp-content/themes/airlinel/functions.php`

**Context:**
WordPress REST API + Admin settings. Tüm 3rd party keys (Google Maps, Stripe) + Base km rates admin'de yönetilecek.

- [ ] **Step 1: API classes'ı tema'ya ekle**

functions.php başına:
```php
require_once get_template_directory() . '/includes/class-settings-manager.php';
require_once get_template_directory() . '/includes/class-api-handler.php';
require_once get_template_directory() . '/includes/class-reservation-handler.php';

// Initialize API
add_action('rest_api_init', function() {
    $api = new Airlinel_API_Handler();
    $api->register_routes();
});

// Admin menu
add_action('admin_menu', function() {
    $settings = new Airlinel_Settings_Manager();
    $settings->register_admin_page();
});
```

- [ ] **Step 2: API Handler oluştur**

```php
// /wp-content/themes/airlinel/includes/class-api-handler.php
<?php
class Airlinel_API_Handler {
    private $namespace = 'airlinel/v1';
    
    public function register_routes() {
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
    }
    
    public function verify_api_key($request) {
        $headers = $request->get_headers();
        $provided_key = isset($headers['x-api-key'][0]) ? $headers['x-api-key'][0] : '';
        $stored_key = Airlinel_Settings_Manager::get('airlinel_api_key');
        return hash_equals($stored_key, $provided_key);
    }
    
    public function search_transfers($request) {
        $params = $request->get_json_params();
        
        if (!isset($params['pickup'], $params['dropoff'], $params['date'])) {
            return new WP_Error('missing_params', 'Missing required parameters', array('status' => 400));
        }
        
        $engine = new Airlinel_Pricing_Engine();
        return rest_ensure_response($engine->calculate(
            $params['pickup'],
            $params['dropoff'],
            $params['passengers'] ?? 1,
            $params['currency'] ?? 'GBP',
            $params['country'] ?? 'UK'
        ));
    }
    
    public function create_reservation($request) {
        $params = $request->get_json_params();
        $handler = new Airlinel_Reservation_Handler();
        $res_id = $handler->create($params);
        
        if (is_wp_error($res_id)) {
            return $res_id;
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
            'pickup' => get_post_meta($post->ID, 'pickup_location', true),
            'dropoff' => get_post_meta($post->ID, 'dropoff_location', true),
            'date' => get_post_meta($post->ID, 'transfer_date', true),
            'total_price' => get_post_meta($post->ID, 'total_price', true),
            'currency' => get_post_meta($post->ID, 'currency', true),
        ));
    }
}
?>
```

- [ ] **Step 3: Reservation Handler oluştur**

```php
// /wp-content/themes/airlinel/includes/class-reservation-handler.php
<?php
class Airlinel_Reservation_Handler {
    
    public function create($data) {
        $required = array('customer_name', 'email', 'phone', 'pickup_location', 'dropoff_location', 'transfer_date');
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return new WP_Error('validation', "Missing: $field");
            }
        }
        
        $post_id = wp_insert_post(array(
            'post_type' => 'reservations',
            'post_title' => sprintf('%s - %s → %s', $data['customer_name'], $data['pickup_location'], $data['dropoff_location']),
            'post_status' => 'pending',
        ));
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        update_post_meta($post_id, 'customer_name', sanitize_text_field($data['customer_name']));
        update_post_meta($post_id, 'email', sanitize_email($data['email']));
        update_post_meta($post_id, 'phone', sanitize_text_field($data['phone']));
        update_post_meta($post_id, 'pickup_location', sanitize_text_field($data['pickup_location']));
        update_post_meta($post_id, 'dropoff_location', sanitize_text_field($data['dropoff_location']));
        update_post_meta($post_id, 'transfer_date', sanitize_text_field($data['transfer_date']));
        update_post_meta($post_id, 'passengers', intval($data['passengers'] ?? 1));
        update_post_meta($post_id, 'currency', sanitize_text_field($data['currency'] ?? 'GBP'));
        update_post_meta($post_id, 'country', sanitize_text_field($data['country'] ?? 'UK'));
        
        if (!empty($data['agency_code'])) {
            update_post_meta($post_id, 'agency_code', sanitize_text_field($data['agency_code']));
            update_post_meta($post_id, 'commission_type', sanitize_text_field($data['commission_type'] ?? 'included'));
        }
        
        return $post_id;
    }
}
?>
```

- [ ] **Step 4: Settings Manager oluştur (Admin panel)**

```php
// /wp-content/themes/airlinel/includes/class-settings-manager.php
<?php
class Airlinel_Settings_Manager {
    
    public function register_admin_page() {
        add_menu_page(
            'Airlinel Settings',
            'Airlinel Settings',
            'manage_options',
            'airlinel-settings',
            array($this, 'render_page'),
            'dashicons-cog',
            3
        );
    }
    
    public function render_page() {
        if (isset($_POST['airlinel_save'])) {
            check_admin_referer('airlinel_nonce');
            update_option('airlinel_api_key', sanitize_text_field($_POST['api_key']));
            update_option('airlinel_google_maps_key', sanitize_text_field($_POST['google_key']));
            update_option('airlinel_stripe_pub_key', sanitize_text_field($_POST['stripe_pub']));
            update_option('airlinel_stripe_secret_key', sanitize_text_field($_POST['stripe_secret']));
            update_option('airlinel_uk_km_rate', floatval($_POST['uk_km_rate']));
            update_option('airlinel_tr_km_rate', floatval($_POST['tr_km_rate']));
            echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
        }
        
        $api_key = self::get('airlinel_api_key', wp_generate_password(32, false));
        $google_key = self::get('airlinel_google_maps_key');
        $stripe_pub = self::get('airlinel_stripe_pub_key');
        $stripe_secret = self::get('airlinel_stripe_secret_key');
        $uk_km = self::get('airlinel_uk_km_rate', '0.75');
        $tr_km = self::get('airlinel_tr_km_rate', '0.65');
        
        ?>
        <div class="wrap">
            <h1>Airlinel Transfer Settings</h1>
            <form method="post">
                <?php wp_nonce_field('airlinel_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><label>API Key</label></th>
                        <td><input type="text" name="api_key" value="<?php echo esc_attr($api_key); ?>" size="60"></td>
                    </tr>
                    <tr>
                        <th><label>Google Maps API Key</label></th>
                        <td><input type="password" name="google_key" value="<?php echo esc_attr($google_key); ?>" size="60"></td>
                    </tr>
                    <tr>
                        <th><label>Stripe Publishable Key</label></th>
                        <td><input type="password" name="stripe_pub" value="<?php echo esc_attr($stripe_pub); ?>" size="60"></td>
                    </tr>
                    <tr>
                        <th><label>Stripe Secret Key</label></th>
                        <td><input type="password" name="stripe_secret" value="<?php echo esc_attr($stripe_secret); ?>" size="60"></td>
                    </tr>
                    <tr>
                        <th><label>UK Default Rate (£/km)</label></th>
                        <td><input type="number" name="uk_km_rate" value="<?php echo esc_attr($uk_km); ?>" step="0.01" min="0"></td>
                    </tr>
                    <tr>
                        <th><label>Turkey Default Rate (£/km)</label></th>
                        <td><input type="number" name="tr_km_rate" value="<?php echo esc_attr($tr_km); ?>" step="0.01" min="0"></td>
                    </tr>
                </table>
                <?php submit_button('Save Settings', 'primary', 'airlinel_save'); ?>
            </form>
        </div>
        <?php
    }
    
    public static function get($key, $default = '') {
        return get_option($key, $default);
    }
}
?>
```

- [ ] **Step 5: Commit**

```bash
git add wp-content/themes/airlinel/includes/class-*.php
git add wp-content/themes/airlinel/functions.php
git commit -m "feat: add REST API and Settings Manager (theme-based, no plugins)"
```

---

### Task 1.2: Pricing Engine + Dynamic Zone Management

**Sorumluluk:** Opus 4.7  
**Files:**
- Create: `/wp-content/themes/airlinel/includes/class-pricing-engine.php`
- Create: `/wp-content/themes/airlinel/includes/class-zone-manager.php`
- Create: `/wp-content/themes/airlinel/includes/class-exchange-rate-manager.php`
- Create: `/wp-content/themes/airlinel/admin/zones-page.php`
- Modify: `/wp-content/themes/airlinel/functions.php`

**Context:**
GBP base pricing. UK/TR special zones dinamik (admin CRUD). Default km-rates admin ayar. Search'te country parameter seçilecek.

- [ ] **Step 1: Zone Manager oluştur (UK/TR zones CRUD)**

```php
// /wp-content/themes/airlinel/includes/class-zone-manager.php
<?php
class Airlinel_Zone_Manager {
    
    private $uk_option = 'airlinel_uk_zones';
    private $tr_option = 'airlinel_tr_zones';
    
    public function __construct() {
        $this->init_defaults();
    }
    
    private function init_defaults() {
        if (!get_option($this->uk_option)) {
            update_option($this->uk_option, array(
                'zone_1' => array('name' => 'Zone 1 - Central London', 'base_gbp' => 15, 'postcodes' => array('EC', 'WC', 'SW1', 'W1')),
                'zone_2' => array('name' => 'Zone 2', 'base_gbp' => 12.50, 'postcodes' => array('E', 'N', 'NW', 'SE5', 'SW2')),
                'zone_3' => array('name' => 'Zone 3', 'base_gbp' => 10, 'postcodes' => array('E3', 'E8', 'N3', 'NW2')),
            ));
        }
        if (!get_option($this->tr_option)) {
            update_option($this->tr_option, array(
                'istanbul_center' => array('name' => 'Istanbul Center', 'areas' => array('Sultanahmet', 'Beyoğlu', 'Taksim'), 'base_gbp' => 12),
                'istanbul_airport' => array('name' => 'Istanbul Airport', 'areas' => array('Istanbul Airport', 'Sabiha Gökçen'), 'base_gbp' => 25),
                'ankara_center' => array('name' => 'Ankara Center', 'areas' => array('Kızılay', 'Tunalı'), 'base_gbp' => 6.5),
                'antalya_airport' => array('name' => 'Antalya Airport', 'areas' => array('Antalya Airport'), 'base_gbp' => 12),
            ));
        }
    }
    
    // UK Zones
    public function get_uk_zones() {
        return get_option($this->uk_option, array());
    }
    
    public function add_uk_zone($id, $data) {
        $zones = $this->get_uk_zones();
        $zones[$id] = $data;
        update_option($this->uk_option, $zones);
    }
    
    public function update_uk_zone($id, $data) {
        $this->add_uk_zone($id, $data);
    }
    
    public function delete_uk_zone($id) {
        $zones = $this->get_uk_zones();
        unset($zones[$id]);
        update_option($this->uk_option, $zones);
    }
    
    public function match_uk_zone($postcode) {
        $prefix = strtoupper(substr(trim($postcode), 0, 2));
        foreach ($this->get_uk_zones() as $zone) {
            if (in_array($prefix, $zone['postcodes'])) {
                return $zone;
            }
        }
        return null;
    }
    
    // TR Zones
    public function get_tr_zones() {
        return get_option($this->tr_option, array());
    }
    
    public function add_tr_zone($id, $data) {
        $zones = $this->get_tr_zones();
        $zones[$id] = $data;
        update_option($this->tr_option, $zones);
    }
    
    public function update_tr_zone($id, $data) {
        $this->add_tr_zone($id, $data);
    }
    
    public function delete_tr_zone($id) {
        $zones = $this->get_tr_zones();
        unset($zones[$id]);
        update_option($this->tr_option, $zones);
    }
    
    public function match_tr_zone($location) {
        foreach ($this->get_tr_zones() as $zone) {
            if (isset($zone['areas'])) {
                foreach ($zone['areas'] as $area) {
                    if (stripos($location, $area) !== false) {
                        return $zone;
                    }
                }
            }
        }
        return null;
    }
}
?>
```

- [ ] **Step 2: Exchange Rate Manager oluştur**

```php
// /wp-content/themes/airlinel/includes/class-exchange-rate-manager.php
<?php
class Airlinel_Exchange_Rate_Manager {
    
    private $option = 'airlinel_exchange_rates';
    
    public function get_rates() {
        return get_option($this->option, array(
            'GBP' => 1.00,
            'EUR' => 1.18,
            'TRY' => 42.50,
            'USD' => 1.27,
        ));
    }
    
    public function get_rate($currency) {
        $rates = $this->get_rates();
        return $rates[$currency] ?? 1.00;
    }
    
    public function set_rates($rates) {
        if (isset($rates['GBP'])) {
            update_option($this->option, $rates);
            return true;
        }
        return false;
    }
}
?>
```

- [ ] **Step 3: Pricing Engine oluştur (GBP base, country-aware)**

```php
// /wp-content/themes/airlinel/includes/class-pricing-engine.php
<?php
class Airlinel_Pricing_Engine {
    
    private $zone_mgr;
    private $exchange_mgr;
    
    public function __construct() {
        $this->zone_mgr = new Airlinel_Zone_Manager();
        $this->exchange_mgr = new Airlinel_Exchange_Rate_Manager();
    }
    
    public function calculate($pickup, $dropoff, $passengers = 1, $currency = 'GBP', $country = 'UK') {
        $distance = $this->get_distance($pickup, $dropoff);
        
        if (!$distance) {
            return array('error' => 'Could not calculate distance');
        }
        
        // Calculate in GBP
        if ($country === 'UK') {
            $base_gbp = $this->calculate_uk_price($pickup, $distance);
        } else if ($country === 'TR') {
            $base_gbp = $this->calculate_tr_price($dropoff, $distance);
        } else {
            $base_gbp = $distance * 0.70;
        }
        
        // Passenger multiplier
        $multiplier = max(1, ($passengers - 1) * 0.5 + 1);
        $total_gbp = $base_gbp * $multiplier;
        
        // Convert to currency
        $rate = $this->exchange_mgr->get_rate($currency);
        $final = $total_gbp * $rate;
        
        return array(
            'success' => true,
            'distance_km' => round($distance, 2),
            'base_price_gbp' => round($base_gbp, 2),
            'passengers' => $passengers,
            'multiplier' => round($multiplier, 2),
            'total_gbp' => round($total_gbp, 2),
            'currency' => $currency,
            'rate' => round($rate, 4),
            'total_display' => round($final, 2),
        );
    }
    
    private function calculate_uk_price($postcode, $distance) {
        $zone = $this->zone_mgr->match_uk_zone($postcode);
        if ($zone) {
            return $zone['base_gbp'];
        }
        return $distance * floatval(Airlinel_Settings_Manager::get('airlinel_uk_km_rate', '0.75'));
    }
    
    private function calculate_tr_price($location, $distance) {
        $zone = $this->zone_mgr->match_tr_zone($location);
        if ($zone) {
            return $zone['base_gbp'];
        }
        return $distance * floatval(Airlinel_Settings_Manager::get('airlinel_tr_km_rate', '0.65'));
    }
    
    private function get_distance($pickup, $dropoff) {
        $key = Airlinel_Settings_Manager::get('airlinel_google_maps_key');
        if (!$key) return false;
        
        $url = 'https://maps.googleapis.com/maps/api/distancematrix/json?' . http_build_query(array(
            'origins' => $pickup,
            'destinations' => $dropoff,
            'key' => $key,
        ));
        
        $response = wp_remote_get($url);
        if (is_wp_error($response)) return false;
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if ($body['status'] !== 'OK') return false;
        
        return $body['rows'][0]['elements'][0]['distance']['value'] / 1000;
    }
}
?>
```

- [ ] **Step 4: Admin Zones Management page oluştur**

```php
// /wp-content/themes/airlinel/admin/zones-page.php
// functions.php'ye ekle:

add_action('admin_menu', function() {
    add_submenu_page(
        'airlinel-settings',
        'Pricing Zones',
        'Pricing Zones',
        'manage_options',
        'airlinel-zones',
        'airlinel_zones_page'
    );
});

function airlinel_zones_page() {
    $zone_mgr = new Airlinel_Zone_Manager();
    
    if (isset($_POST['add_uk_zone'])) {
        check_admin_referer('zones_nonce');
        $zone_mgr->add_uk_zone($_POST['uk_id'], array(
            'name' => sanitize_text_field($_POST['uk_name']),
            'base_gbp' => floatval($_POST['uk_price']),
            'postcodes' => array_filter(array_map('trim', explode(',', $_POST['uk_postcodes']))),
        ));
        echo '<div class="notice notice-success"><p>UK zone added!</p></div>';
    }
    
    if (isset($_POST['add_tr_zone'])) {
        check_admin_referer('zones_nonce');
        $zone_mgr->add_tr_zone($_POST['tr_id'], array(
            'name' => sanitize_text_field($_POST['tr_name']),
            'base_gbp' => floatval($_POST['tr_price']),
            'areas' => array_filter(array_map('trim', explode(',', $_POST['tr_areas']))),
        ));
        echo '<div class="notice notice-success"><p>TR zone added!</p></div>';
    }
    
    ?>
    <div class="wrap">
        <h1>Pricing Zones</h1>
        
        <h2>UK Zones</h2>
        <table class="widefat">
            <thead><tr><th>Name</th><th>Base (£)</th><th>Postcodes</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($zone_mgr->get_uk_zones() as $id => $zone) { ?>
                    <tr>
                        <td><?php echo $zone['name']; ?></td>
                        <td>£<?php echo $zone['base_gbp']; ?></td>
                        <td><?php echo implode(', ', $zone['postcodes']); ?></td>
                        <td><a href="#" class="button button-small">Edit</a> <a href="#" class="button button-small">Delete</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        
        <h3>Add UK Zone</h3>
        <form method="post">
            <?php wp_nonce_field('zones_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label>Zone ID</label></th>
                    <td><input type="text" name="uk_id" placeholder="e.g., zone_4"></td>
                </tr>
                <tr>
                    <th><label>Name</label></th>
                    <td><input type="text" name="uk_name" placeholder="e.g., Zone 4 - Outer London"></td>
                </tr>
                <tr>
                    <th><label>Base Price (£)</label></th>
                    <td><input type="number" name="uk_price" step="0.01" placeholder="8.00"></td>
                </tr>
                <tr>
                    <th><label>Postcodes (comma-separated)</label></th>
                    <td><textarea name="uk_postcodes" rows="3">E4, E10, N9, NW7, SE9</textarea></td>
                </tr>
            </table>
            <?php submit_button('Add UK Zone', 'primary', 'add_uk_zone'); ?>
        </form>
        
        <h2 style="margin-top:50px;">Turkey Zones</h2>
        <table class="widefat">
            <thead><tr><th>Name</th><th>Base (£)</th><th>Areas</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($zone_mgr->get_tr_zones() as $id => $zone) { ?>
                    <tr>
                        <td><?php echo $zone['name']; ?></td>
                        <td>£<?php echo $zone['base_gbp']; ?></td>
                        <td><?php echo implode(', ', $zone['areas'] ?? array()); ?></td>
                        <td><a href="#" class="button button-small">Edit</a> <a href="#" class="button button-small">Delete</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        
        <h3>Add Turkey Zone</h3>
        <form method="post">
            <?php wp_nonce_field('zones_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label>Zone ID</label></th>
                    <td><input type="text" name="tr_id" placeholder="e.g., istanbul_outer"></td>
                </tr>
                <tr>
                    <th><label>Name</label></th>
                    <td><input type="text" name="tr_name" placeholder="e.g., Istanbul - Outer"></td>
                </tr>
                <tr>
                    <th><label>Base Price (£)</label></th>
                    <td><input type="number" name="tr_price" step="0.01" placeholder="8.00"></td>
                </tr>
                <tr>
                    <th><label>Areas (comma-separated)</label></th>
                    <td><textarea name="tr_areas" rows="3">Fatih, Beyoğlu, Çankırı</textarea></td>
                </tr>
            </table>
            <?php submit_button('Add TR Zone', 'primary', 'add_tr_zone'); ?>
        </form>
    </div>
    <?php
}
```

- [ ] **Step 5: Commit**

```bash
git add wp-content/themes/airlinel/includes/class-pricing-engine.php
git add wp-content/themes/airlinel/includes/class-zone-manager.php
git add wp-content/themes/airlinel/includes/class-exchange-rate-manager.php
git add wp-content/themes/airlinel/functions.php
git commit -m "feat: add Pricing Engine with dynamic zone management (UK/TR)"
```

---

### Task 1.3: Agency & Commission Management (Tema içinde)

**Sorumluluk:** Opus 4.7  
**Files:**
- Create: `/wp-content/themes/airlinel/includes/class-agency-manager.php`
- Modify: `/wp-content/themes/airlinel/functions.php`

- [ ] **Step 1: Agency Manager oluştur**

```php
// /wp-content/themes/airlinel/includes/class-agency-manager.php
<?php
class Airlinel_Agency_Manager {
    
    public function create($code, $name, $email, $commission_percent = 15) {
        $post_id = wp_insert_post(array(
            'post_type' => 'agencies',
            'post_title' => $name,
            'post_status' => 'publish',
        ));
        
        update_post_meta($post_id, 'agency_code', $code);
        update_post_meta($post_id, 'email', $email);
        update_post_meta($post_id, 'commission_percent', $commission_percent);
        
        return $post_id;
    }
    
    public function verify($code) {
        $posts = get_posts(array(
            'post_type' => 'agencies',
            'meta_query' => array(array('key' => 'agency_code', 'value' => $code)),
        ));
        
        if (empty($posts)) return false;
        
        $post = $posts[0];
        return array(
            'id' => $post->ID,
            'name' => $post->post_title,
            'commission_percent' => get_post_meta($post->ID, 'commission_percent', true),
        );
    }
    
    public function get_earnings($agency_id) {
        $posts = get_posts(array(
            'post_type' => 'reservations',
            'meta_query' => array(array('key' => 'agency_id', 'value' => $agency_id)),
            'posts_per_page' => -1,
        ));
        
        $week = $month = $all = 0;
        $week_ago = strtotime('-7 days');
        $month_ago = strtotime('-30 days');
        
        foreach ($posts as $res) {
            $comm = floatval(get_post_meta($res->ID, 'agency_commission', true));
            $res_date = strtotime($res->post_date);
            
            $all += $comm;
            if ($res_date > $week_ago) $week += $comm;
            if ($res_date > $month_ago) $month += $comm;
        }
        
        return compact('week', 'month', 'all');
    }
}
?>
```

- [ ] **Step 2-3: Admin Agency page + AJAX endpoint ekle**

```php
// functions.php'ye ekle:

require_once get_template_directory() . '/includes/class-agency-manager.php';

add_action('admin_menu', function() {
    add_submenu_page(
        'options-general.php',
        'Agencies',
        'Agencies',
        'manage_options',
        'airlinel-agencies',
        function() {
            $mgr = new Airlinel_Agency_Manager();
            $agencies = get_posts(array('post_type' => 'agencies', 'posts_per_page' => 50));
            ?>
            <div class="wrap">
                <h1>Agencies</h1>
                <table class="widefat">
                    <thead><tr><th>Name</th><th>Code</th><th>Commission</th><th>This Week</th><th>This Month</th><th>All Time</th></tr></thead>
                    <tbody>
                        <?php foreach ($agencies as $agency) {
                            $code = get_post_meta($agency->ID, 'agency_code', true);
                            $comm = get_post_meta($agency->ID, 'commission_percent', true);
                            $earnings = $mgr->get_earnings($agency->ID);
                            ?>
                            <tr>
                                <td><?php echo $agency->post_title; ?></td>
                                <td><code><?php echo $code; ?></code></td>
                                <td><?php echo $comm; ?>%</td>
                                <td>£<?php echo number_format($earnings['week'], 2); ?></td>
                                <td>£<?php echo number_format($earnings['month'], 2); ?></td>
                                <td>£<?php echo number_format($earnings['all'], 2); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
    );
});

add_action('wp_ajax_nopriv_verify_agency', 'airlinel_verify_agency_ajax');
add_action('wp_ajax_verify_agency', 'airlinel_verify_agency_ajax');

function airlinel_verify_agency_ajax() {
    check_ajax_referer('airlinel_nonce', 'nonce');
    $code = sanitize_text_field($_POST['code']);
    $mgr = new Airlinel_Agency_Manager();
    $agency = $mgr->verify($code);
    
    if (!$agency) {
        wp_send_json_error(array('message' => 'Invalid code'));
    }
    
    wp_send_json_success($agency);
}
```

- [ ] **Step 4: Commit**

```bash
git add wp-content/themes/airlinel/includes/class-agency-manager.php
git commit -m "feat: add Agency management with commission tracking"
```

---

### Task 1.4: Stripe 3D Secure Payment (Tema içinde)

**Sorumluluk:** Opus 4.7  
**Files:**
- Create: `/wp-content/themes/airlinel/includes/class-payment-processor.php`

- [ ] **Step 1-2: Payment Processor + AJAX endpoints**

[Similar to agency - theme-based, no plugin]

---

### Task 1.5: Booking Form + Country Selector

**Sorumluluk:** Sonnet 4.6  
**Files:**
- Modify: `/wp-content/themes/airlinel/booking-page.php`
- Create: `/wp-content/themes/airlinel/assets/js/booking.js`

- [ ] **Step 1-4: Form redesign with country dropdown, agency modal, etc.**

[Complete implementation with HTML/CSS/JS]

---

### Task 1.6-1.8: Admin Management, ads.txt, API Docs

[Similar theme-based approach]

---

## EXECUTION NOTES

✅ **NO PLUGINS** - Tüm işler tema functions.php + includes/ klasörü
✅ **Dynamic Management** - Tüm zones/rates/keys admin panel'de CRUD
✅ **Country Aware** - Search: pickup location + country parameter
✅ **Multi-language** - EN, TR, DE, RU, FR, IT, AR, DA, NL, SV, ZH, JA
✅ **Base Currency** - GBP (all calculations), EUR/TRY/USD display only

**Total Phase 1: 8 Tasks, ~40 steps**

---

## PHASE 3: Multi-Site Regional Platform

**Hedef:** 12 dilde farkli domainlerde bölgesel siteler (Türkiye Antalya/Istanbul, diğer destinasyonlar) - airlinel.com ana siteden veri alan merkezi sistem

**Mimarileri:**
- **Ana Site:** airlinel.com (airlinel-transfer-services tema) - Veri merkezi (araçlar, fiyatlar, reservasyonlar, kullanıcılar)
- **Bölgesel Siteler:** Separate WordPress installations per destination/language
  - Türkiye: antalya.airlinel.com, istanbul.airlinel.com (12 dilde)
  - Diğer destinasyonlar: destination.airlinel.com (12 dilde)
  - Her site kendi domaininde, kendi dili, ana siteden tasarım/veri alır
- **Senkronizasyon:** Bölgesel siteler ana siteden API çağrısı yaparak araç/fiyat/rezervasyon alır
- **Tasarım:** Aynı tema ve yapı, sadece URL/dil/içerik farklı
- **Ödeme:** Tüm siteler Stripe üzerinden (ana site API çağrısı)

**Tech Stack:** Aynı (WordPress 6.x, PHP 8.x, Stripe, Google Maps)

---

## PHASE 3 ARCHITECTURE

### Site Topology

```
airlinel.com (Ana Site)
├── wp-content/themes/airlinel-transfer-services/ (Ortak tema)
├── Database (Shared)
│   ├── Fleet (Araçlar)
│   ├── Reservations (Tüm rezervasyonlar, source_site field ile)
│   ├── Users (Tüm kullanıcılar, shared)
│   └── Exchange Rates, Zones, Settings
└── REST API (/wp-json/airlinel/v1/)

Bölgesel Siteler (Separate WP Installations)
├── antalya.airlinel.com/
├── istanbul.airlinel.com/
├── berlin.airlinel.com/ (diğer destinasyonlar)
└── ... (12 dilde her destination)

Each Regional Site:
├── Same tema (airlinel-transfer-services copied)
├── Same structure (page-booking.php, etc.)
├── Separate wp_options (language, content, local settings)
├── Separate wp_posts (pages, blogs - but structure same)
├── Local database OR shared database with site prefix
└── API Client to main site
    ├── GET /wp-json/airlinel/v1/search (for vehicles/pricing)
    ├── POST /wp-json/airlinel/v1/reservation/create (to main site)
    ├── GET /wp-json/airlinel/v1/reservation/{id} (shared reservations)
    └── API Key auth with site-specific key per regional site
```

### Data Flow

```
Müşteri (Bölgesel Site - antalya.airlinel.com)
  ↓
Booking Form (Aynı tasarım, Türkçe arayüz)
  ↓
JavaScript: calculateDistance(), validateForm()
  ↓
API Call to antalya.airlinel.com/wp-json/airlinel/v1/search
  ↓
Bölgesel Site Plugin/Handler: Intercept & Forward to Main
  ├── Add source_site = 'antalya' 
  ├── Verify API key
  └── Call main site: airlinel.com/wp-json/airlinel/v1/search
  ↓
Main Site Handler: Calculate pricing + return vehicles
  ↓
Response back to Bölgesel Site with pricing
  ↓
Display to user (Türkçe, EUR prices)
  ↓
User submits: POST /reservation/create
  ↓
Main Site creates reservation with:
  ├── source_site: 'antalya'
  ├── source_language: 'tr_TR'
  └── customer data
  ↓
Shared reservation visible on both sites
```

---

## PHASE 3 TASKS

### Task 3.0: Multi-Site Foundation & API Proxy

**Sorumluluk:** Opus 4.7

**Files:**
- Create: `/includes/class-regional-site-proxy.php` (on regional sites)
- Modify: `/includes/class-api-handler.php` (add source_site tracking)
- Modify: `/functions.php` (add proxy handlers)
- Create: `/wp-content/plugins/airlinel-regional-proxy/airlinel-regional-proxy.php` (light proxy plugin)

**Goals:**
1. Bölgesel siteler ana siteden API çağrıları yapar
2. Ana site tüm veri merkezini yönetir
3. Rezervasyonlar birleştirilir, kaynağı tracked edilir
4. Senkronizasyon otomatik

**Implementation:**
- Regional_Site_Proxy class to forward requests
- API key management per regional site
- source_site field added to reservations
- source_language field for language tracking
- Caching of vehicle/pricing data locally (5 min TTL)

### Task 3.1: Regional Site Setup & Theme Configuration

**Sorumluluk:** Sonnet 4.6

**Files:**
- Create regional site WordPress installations (12 × destinations)
- Copy `/wp-content/themes/airlinel-transfer-services/` to each
- Create `/admin/regional-settings.php` (per-site content management)
- Create `/wp-content/plugins/airlinel-language-settings/` (language switcher)

**Goals:**
1. Her bölgesel site ana tema kullanır ama kendi diliyle
2. Yönetici panelinde language selector
3. İçerik sayfaları (about, services, contact) WordPress editöründen yönetilir
4. SEO-optimized homepage toggle (content up/down)

**Implementation:**
- WordPress language set: wp-config.php WPLANG = specific language
- WPML compat OR custom language solution
- Regional site settings in wp_options
- Content pages from local wp_posts

### Task 3.2: Homepage Toggle & Content Management

**Sorumluluk:** Sonnet 4.6

**Files:**
- Create: `/admin/homepage-content-page.php` (toggle interface)
- Modify: `/front-page.php` (dynamic content loading)
- Create: `/includes/class-homepage-manager.php` (toggle logic)

**Goals:**
1. Homepage yöneticisi içeriği aşağı/yukarı toggle edebilir
2. SEO improvement: featured content, testimonials, trust signals
3. A/B testing capability (future)

**Implementation:**
- Admin panel with toggle buttons for sections
- Featured routes, customer reviews, service highlights
- Conditional display: if ( get_option('show_features') )
- Each section has text editor in WordPress admin

### Task 3.3: Language & Localization System

**Sorumluluk:** Sonnet 4.6

**Files:**
- Create: `/includes/class-language-manager.php`
- Modify: `/functions.php` (load text domain per language)
- Create: `/languages/` directory with all language files

**Goals:**
1. 12 dil desteği: EN, TR, DE, RU, FR, IT, AR, DA, NL, SV, ZH, JA
2. WordPress admin language setting'den çevre dili seç
3. Frontend otomatik olarak doğru dilde render edilir
4. API responses dilden bağımsız (dil ayrı şekilde apply edilir)

**Implementation:**
- Use WordPress __() ve _e() fonksiyonları
- Load text domain from WordPress language setting
- Translation files per language (po/mo)
- Dynamic locale from wp_options

### Task 3.4: Shared Data & Synchronization

**Sorumluluk:** Opus 4.7

**Files:**
- Modify: `/includes/class-api-handler.php` (add source tracking)
- Create: `/includes/class-data-sync-manager.php` (sync logic)
- Create: `/admin/sync-dashboard.php` (monitoring)

**Goals:**
1. Araçlar merkezi (ana site) yönetilir, bölgesel siteler onları kullanır
2. Fiyatlandırma merkezi ve uniform
3. Kullanıcılar paylaşılır (shared user table)
4. Rezervasyonlar merkezi ama kaynağı tracked

**Implementation:**
- Vehicles: Read-only on regional, managed on main
- Users: Sync table or direct query to main
- Reservations: Insert on main with source_site field
- Exchange rates: Update on main, regional sites cache
- Sync verification dashboard in main site admin

### Task 3.5: Regional API Client & Proxy Service

**Sorumluluk:** Sonnet 4.6

**Files:**
- Create: `/includes/class-main-site-client.php` (on regional sites)
- Create: `/includes/class-api-proxy-handler.php` (request forwarding)

**Goals:**
1. Bölgesel siteler ana siteden API çağrısı yapar
2. Transparent proxy: bölgesel site kullanıcısına ana siteden gibi görünür
3. Error handling: ana site down ise graceful fallback
4. Rate limiting & caching

**Implementation:**
- Regional site: POST to self /wp-json/airlinel/v1/search
- Handler intercepts, checks API key, forwards to main
- Main site processes, returns data
- Regional site caches locally (5 min TTL)
- Falls back to cached data if main unavailable

### Task 3.6: Customer Source Tracking & Analytics

**Sorumluluk:** Sonnet 4.6

**Files:**
- Modify: `/includes/class-api-handler.php` (track source)
- Modify: `/includes/class-reservation-handler.php` (store source_site)
- Create: `/admin/analytics-page.php` (source breakdown)

**Goals:**
1. Hangi bölgesel siteden reservation geldiğini bilmek
2. Analytics: per-site booking count, revenue
3. Main site admin tüm sitelerden data görebilir

**Implementation:**
- Add source_site, source_language to reservation meta
- Add source_url to track exact referrer
- Admin dashboard: filter by source_site
- Reports: bookings by regional site

### Task 3.7: Content Pages & Blog Management

**Sorumluluk:** Sonnet 4.6

**Files:**
- Create: `/page-about.php` (editable about page)
- Create: `/page-services.php` (services listing)
- Create: `/page-contact.php` (contact form)
- Create: `/functions.php` hooks (content editable from admin)

**Goals:**
1. Her bölgesel site kendi about/services/contact page'lerine sahip
2. İçerik WordPress editöründen yönetilir
3. Aynı template ama farklı wp_posts
4. SEO-optimized yapı

**Implementation:**
- Standard page templates
- Custom meta boxes for SEO fields
- Content managed per regional site
- Same structure, different content

### Task 3.8: Multi-Site Testing & Deployment

**Sorumluluk:** Sonnet 4.6

**Files:**
- Create: `/docs/REGIONAL_SETUP.md` (deployment guide)
- Create: `/tests/regional-site-tests.php` (integration tests)

**Goals:**
1. Test cross-site API calls
2. Verify data sync works
3. Language switching works
4. User sync works
5. Payment workflow works end-to-end

**Implementation:**
- Unit tests for API proxy
- Integration tests for booking flow
- Multi-site testing scenario
- Documentation for setting up new regional site

---

## PHASE 3 EXECUTION NOTES

✅ **Separate Installations** - Her bölgesel site kendi WP installation'ı
✅ **Shared API** - Ana site data provider, bölgesel siteler consumer
✅ **Unified Data** - Reservations, users, vehicles merkezi yönetilir
✅ **Multi-Language** - 12 dil, WordPress language setting'den seç
✅ **Content Pages** - Editable, same structure, different content
✅ **Source Tracking** - Hangi siteden booking geldiği tracked
✅ **Shared Payment** - Stripe merkezi, ana site yönetir

**Total Phase 3: 8 Tasks, ~35 steps**

---

Hangi execution method seçiyorsun?
