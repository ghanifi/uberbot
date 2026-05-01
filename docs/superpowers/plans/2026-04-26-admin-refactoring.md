# Admin Panel & Regional Site Refactoring Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor admin panel to use unified AIRLINEL menu, make regional settings database-driven (not hardcoded), integrate booking analytics, and optimize for multi-site deployment.

**Architecture:** 
- Single "AIRLINEL" top-level admin menu consolidating all settings/dashboards
- Regional site configuration via database (wp_options) instead of wp-config.php constants
- Booking analytics fully integrated into theme with search-to-payment funnel tracking
- Agencies managed via CPT (not custom page) for consistency
- Page content settings optimized for regional sites

**Tech Stack:** WordPress 6.x, PHP 8.x, Chart.js, MySQL custom tables, AJAX with nonce security

---

## File Structure & Modifications

### New Files (Create):
- `/includes/class-regional-settings-manager.php` - Manage regional site configuration
- `/admin/regional-api-settings-page.php` - Admin UI for regional settings
- `/includes/class-booking-analytics-tracker.php` - Booking funnel tracking (core logic)
- `/assets/js/booking-tracker.js` - Frontend tracking AJAX calls

### Modified Files:
- `/functions.php` - Register unified AIRLINEL menu, load new classes
- `/admin/agencies-page.php` - Keep but mark as DEPRECATED (CPT is primary)
- `/admin/page-content-settings.php` - Update for regional site context
- `/assets/js/booking.js` - Add tracking calls at each stage

### Files to Deprecate/Rename:
- `/admin/agencies-page.php` → Will be superseded by CPT edit.php?post_type=agencies
- Remove custom "Booking Analytics" menu if plugin version exists

---

## Tasks

### Task 1: Regional Settings Manager - Database Configuration

**Files:**
- Create: `/includes/class-regional-settings-manager.php`
- Create: `/admin/regional-api-settings-page.php`
- Modify: `/functions.php`
- Modify: `/admin/regional-settings.php`

**Context:**
Currently: `define('AIRLINEL_MAIN_SITE_URL', 'https://airlinel.com')` in wp-config.php

Wanted: Admin form to manage these in database (wp_options), no wp-config.php edits needed.

- [ ] **Step 1: Create Regional_Settings_Manager class**

Create `/includes/class-regional-settings-manager.php`:

```php
<?php
/**
 * Regional Settings Manager
 * Handles configuration for regional sites (main site URL, API key, site ID)
 */

class Airlinel_Regional_Settings_Manager {
    
    private $option_prefix = 'airlinel_regional_';
    
    /**
     * Get main site URL
     */
    public function get_main_site_url() {
        return get_option($this->option_prefix . 'main_site_url', '');
    }
    
    /**
     * Set main site URL
     */
    public function set_main_site_url($url) {
        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return new WP_Error('invalid_url', 'Invalid URL format');
        }
        return update_option($this->option_prefix . 'main_site_url', esc_url_raw($url));
    }
    
    /**
     * Get regional site API key (for connecting to main site)
     */
    public function get_api_key() {
        return get_option($this->option_prefix . 'api_key', '');
    }
    
    /**
     * Set regional site API key (encrypted storage)
     */
    public function set_api_key($key) {
        if (empty($key)) {
            return new WP_Error('empty_key', 'API key cannot be empty');
        }
        // Store as-is (WordPress will sanitize on save)
        return update_option($this->option_prefix . 'api_key', sanitize_text_field($key));
    }
    
    /**
     * Get regional site ID (e.g., 'antalya', 'istanbul')
     */
    public function get_site_id() {
        return get_option($this->option_prefix . 'site_id', '');
    }
    
    /**
     * Set regional site ID
     */
    public function set_site_id($site_id) {
        if (empty($site_id)) {
            return new WP_Error('empty_site_id', 'Site ID cannot be empty');
        }
        return update_option($this->option_prefix . 'site_id', sanitize_text_field($site_id));
    }
    
    /**
     * Get all regional settings at once
     */
    public function get_all_settings() {
        return array(
            'main_site_url' => $this->get_main_site_url(),
            'api_key'       => $this->get_api_key(),
            'site_id'       => $this->get_site_id(),
        );
    }
    
    /**
     * Save all settings at once
     */
    public function save_settings($data) {
        if (!is_array($data)) {
            return new WP_Error('invalid_data', 'Settings must be array');
        }
        
        if (isset($data['main_site_url'])) {
            $url_result = $this->set_main_site_url($data['main_site_url']);
            if (is_wp_error($url_result)) return $url_result;
        }
        
        if (isset($data['api_key'])) {
            $key_result = $this->set_api_key($data['api_key']);
            if (is_wp_error($key_result)) return $key_result;
        }
        
        if (isset($data['site_id'])) {
            $id_result = $this->set_site_id($data['site_id']);
            if (is_wp_error($id_result)) return $id_result;
        }
        
        return true;
    }
    
    /**
     * Test connection to main site API
     */
    public function test_connection() {
        $main_url = $this->get_main_site_url();
        $api_key = $this->get_api_key();
        
        if (empty($main_url) || empty($api_key)) {
            return array(
                'success' => false,
                'message' => 'Main site URL and API key must be configured',
            );
        }
        
        $endpoint = rtrim($main_url, '/') . '/wp-json/airlinel/v1/search';
        
        $response = wp_remote_post($endpoint, array(
            'headers' => array(
                'Content-Type'  => 'application/json',
                'X-API-Key'     => $api_key,
            ),
            'body' => json_encode(array(
                'pickup'  => 'Test Location',
                'dropoff' => 'Test Location 2',
                'country' => 'UK',
            )),
            'timeout' => 10,
            'sslverify' => true,
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'Connection failed: ' . $response->get_error_message(),
            );
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200 && $status_code !== 403) { // 403 is expected for invalid key in test
            return array(
                'success' => false,
                'message' => 'Server returned error code: ' . $status_code,
            );
        }
        
        return array(
            'success' => true,
            'message' => 'Connected to main site successfully!',
        );
    }
}
?>
```

- [ ] **Step 2: Create regional settings admin page**

Create `/admin/regional-api-settings-page.php`:

```php
<?php
/**
 * Regional Site API Settings Admin Page
 * Allows managing main site connection without editing wp-config.php
 */

if (!current_user_can('manage_options')) {
    wp_die(__('You do not have permission to access this page.', 'airlinel-theme'));
}

require_once get_template_directory() . '/includes/class-regional-settings-manager.php';
$regional_mgr = new Airlinel_Regional_Settings_Manager();

$message = '';
$message_type = 'info';

// Handle form submission
if (isset($_POST['airlinel_regional_settings_nonce'])) {
    if (!wp_verify_nonce($_POST['airlinel_regional_settings_nonce'], 'airlinel_regional_settings')) {
        wp_die(__('Nonce verification failed', 'airlinel-theme'));
    }
    
    $result = $regional_mgr->save_settings(array(
        'main_site_url' => $_POST['main_site_url'] ?? '',
        'api_key'       => $_POST['api_key'] ?? '',
        'site_id'       => $_POST['site_id'] ?? '',
    ));
    
    if (is_wp_error($result)) {
        $message = $result->get_error_message();
        $message_type = 'error';
    } else {
        $message = __('Settings saved successfully!', 'airlinel-theme');
        $message_type = 'success';
    }
}

// Handle connection test
$test_result = null;
if (isset($_POST['test_connection'])) {
    if (!wp_verify_nonce($_POST['airlinel_regional_settings_nonce'], 'airlinel_regional_settings')) {
        wp_die(__('Nonce verification failed', 'airlinel-theme'));
    }
    $test_result = $regional_mgr->test_connection();
}

// Get current settings
$settings = $regional_mgr->get_all_settings();
?>

<div class="wrap airlinel-regional-settings-wrap">
    <h1><?php _e('Regional Site API Settings', 'airlinel-theme'); ?></h1>
    
    <?php if ($message): ?>
        <div class="notice notice-<?php echo esc_attr($message_type); ?> is-dismissible">
            <p><?php echo wp_kses_post($message); ?></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e('Dismiss', 'airlinel-theme'); ?></span></button>
        </div>
    <?php endif; ?>
    
    <?php if ($test_result): ?>
        <div class="notice notice-<?php echo esc_attr($test_result['success'] ? 'success' : 'error'); ?> is-dismissible">
            <p><?php echo wp_kses_post($test_result['message']); ?></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e('Dismiss', 'airlinel-theme'); ?></span></button>
        </div>
    <?php endif; ?>
    
    <div class="postbox" style="max-width: 600px;">
        <h2 class="hndle"><?php _e('Configuration', 'airlinel-theme'); ?></h2>
        <div class="inside">
            <form method="post">
                <?php wp_nonce_field('airlinel_regional_settings', 'airlinel_regional_settings_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="main_site_url"><?php _e('Main Site URL', 'airlinel-theme'); ?></label>
                        </th>
                        <td>
                            <input type="url" id="main_site_url" name="main_site_url" 
                                   value="<?php echo esc_attr($settings['main_site_url']); ?>"
                                   placeholder="https://airlinel.com"
                                   class="large-text">
                            <p class="description"><?php _e('Full URL to the main Airlinel site (e.g., https://airlinel.com)', 'airlinel-theme'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="site_id"><?php _e('Regional Site ID', 'airlinel-theme'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="site_id" name="site_id" 
                                   value="<?php echo esc_attr($settings['site_id']); ?>"
                                   placeholder="e.g., antalya, istanbul"
                                   class="regular-text">
                            <p class="description"><?php _e('Unique identifier for this regional site (e.g., antalya, istanbul, berlin)', 'airlinel-theme'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="api_key"><?php _e('API Key', 'airlinel-theme'); ?></label>
                        </th>
                        <td>
                            <input type="password" id="api_key" name="api_key" 
                                   value="<?php echo esc_attr($settings['api_key']); ?>"
                                   class="large-text"
                                   placeholder="Paste your regional API key here">
                            <p class="description"><?php _e('Get this from the main site admin panel', 'airlinel-theme'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <?php submit_button(__('Save Settings', 'airlinel-theme'), 'primary', 'submit', false); ?>
                    <?php submit_button(__('Test Connection', 'airlinel-theme'), 'secondary', 'test_connection', false); ?>
                </div>
            </form>
        </div>
    </div>
    
    <div class="postbox" style="max-width: 600px; margin-top: 20px;">
        <h2 class="hndle"><?php _e('Setup Instructions', 'airlinel-theme'); ?></h2>
        <div class="inside">
            <ol>
                <li><?php _e('Fill in the Main Site URL above', 'airlinel-theme'); ?></li>
                <li><?php _e('Generate a Regional Site ID (e.g., "antalya" for Antalya site)', 'airlinel-theme'); ?></li>
                <li><?php _e('Get the API Key from main site admin (Settings → Airlinel Settings → Regional Keys)', 'airlinel-theme'); ?></li>
                <li><?php _e('Paste the API Key above and save', 'airlinel-theme'); ?></li>
                <li><?php _e('Click "Test Connection" to verify', 'airlinel-theme'); ?></li>
            </ol>
        </div>
    </div>
</div>
```

- [ ] **Step 3: Update functions.php to load Regional_Settings_Manager and register admin page**

In `/functions.php`, add after other require_once statements:

```php
// Regional settings manager
require_once get_template_directory() . '/includes/class-regional-settings-manager.php';

// Register regional API settings page
add_action('admin_menu', function() {
    add_submenu_page(
        'airlinel-settings',  // Will change to unified menu in Task 2
        __('Regional API Settings', 'airlinel-theme'),
        __('Regional API Settings', 'airlinel-theme'),
        'manage_options',
        'airlinel-regional-api-settings',
        function() {
            include get_template_directory() . '/admin/regional-api-settings-page.php';
        }
    );
});
```

- [ ] **Step 4: Update existing regional-settings.php to use new manager**

In `/admin/regional-settings.php`, replace the hardcoded constant checks with:

```php
// At top of file, after permission check:
require_once get_template_directory() . '/includes/class-regional-settings-manager.php';
$regional_mgr = new Airlinel_Regional_Settings_Manager();
$settings = $regional_mgr->get_all_settings();

// Replace these lines:
// $site_id = defined('AIRLINEL_SITE_ID') ? AIRLINEL_SITE_ID : ...
// With:
$site_id = $settings['site_id'] ?: 'Not configured';
$main_site_url = $settings['main_site_url'] ?: 'Not configured';
$api_key_set = !empty($settings['api_key']);
```

- [ ] **Step 5: Commit**

```bash
git add includes/class-regional-settings-manager.php
git add admin/regional-api-settings-page.php
git add functions.php
git add admin/regional-settings.php
git commit -m "feat: database-driven regional site configuration (no wp-config.php needed)"
```

**Test:**
- Navigate to: Settings → Airlinel Settings → Regional API Settings
- Fill in: Main Site URL, Site ID, API Key
- Click Save Settings → verify success message
- Click Test Connection → verify connection works

---

### Task 2: Unified AIRLINEL Admin Menu

**Files:**
- Modify: `/functions.php`
- Modify: `/admin/*.php` (all admin pages)

**Context:**
Currently: Settings, Options-general scattered across multiple menus
Goal: Single "Airlinel" top-level menu with all submenus

- [ ] **Step 1: Create unified menu structure in functions.php**

Replace all `add_menu_page` and `add_submenu_page` calls with unified structure:

```php
// Main AIRLINEL menu with unified structure
add_action('admin_menu', function() {
    // Main menu (first item)
    add_menu_page(
        __('Airlinel', 'airlinel-theme'),
        __('Airlinel', 'airlinel-theme'),
        'manage_options',
        'airlinel-dashboard',  // Main page
        'airlinel_dashboard_page',
        'dashicons-car',  // Car icon
        2  // Position after Dashboard
    );
    
    // Submenus
    add_submenu_page('airlinel-dashboard', __('Dashboard', 'airlinel-theme'), __('Dashboard', 'airlinel-theme'), 'manage_options', 'airlinel-dashboard', 'airlinel_dashboard_page');
    
    add_submenu_page('airlinel-dashboard', __('Settings', 'airlinel-theme'), __('Settings', 'airlinel-theme'), 'manage_options', 'airlinel-settings', 'airlinel_settings_page');
    
    add_submenu_page('airlinel-dashboard', __('Agencies', 'airlinel-theme'), __('Agencies', 'airlinel-theme'), 'manage_options', 'edit.php?post_type=agencies', 'airlinel_agencies_page');
    
    add_submenu_page('airlinel-dashboard', __('Reservations', 'airlinel-theme'), __('Reservations', 'airlinel-theme'), 'manage_options', 'airlinel-reservations', 'airlinel_reservations_page');
    
    add_submenu_page('airlinel-dashboard', __('Pricing Zones', 'airlinel-theme'), __('Pricing Zones', 'airlinel-theme'), 'manage_options', 'airlinel-zones', 'airlinel_zones_page');
    
    add_submenu_page('airlinel-dashboard', __('Exchange Rates', 'airlinel-theme'), __('Exchange Rates', 'airlinel-theme'), 'manage_options', 'airlinel-exchange-rates', 'airlinel_exchange_rates_page');
    
    add_submenu_page('airlinel-dashboard', __('Homepage Content', 'airlinel-theme'), __('Homepage Content', 'airlinel-theme'), 'manage_options', 'airlinel-homepage-content', 'airlinel_homepage_content_page');
    
    add_submenu_page('airlinel-dashboard', __('Pages & Content', 'airlinel-theme'), __('Pages & Content', 'airlinel-theme'), 'manage_options', 'airlinel-page-content', 'airlinel_page_content_page');
    
    add_submenu_page('airlinel-dashboard', __('Analytics', 'airlinel-theme'), __('Analytics', 'airlinel-theme'), 'manage_options', 'airlinel-analytics', 'airlinel_analytics_page');
    
    // Phase 3 specific
    if (defined('AIRLINEL_IS_REGIONAL_SITE') && AIRLINEL_IS_REGIONAL_SITE) {
        add_submenu_page('airlinel-dashboard', __('Regional Settings', 'airlinel-theme'), __('Regional Settings', 'airlinel-theme'), 'manage_options', 'airlinel-regional-settings', 'airlinel_regional_settings_page');
        add_submenu_page('airlinel-dashboard', __('Sync Dashboard', 'airlinel-theme'), __('Sync Dashboard', 'airlinel-theme'), 'manage_options', 'airlinel-sync-dashboard', 'airlinel_sync_dashboard_page');
    } else {
        // Main site only
        add_submenu_page('airlinel-dashboard', __('Regional Keys', 'airlinel-theme'), __('Regional Keys', 'airlinel-theme'), 'manage_options', 'airlinel-regional-keys', 'airlinel_regional_keys_page');
    }
    
    add_submenu_page('airlinel-dashboard', __('Ads.txt Manager', 'airlinel-theme'), __('Ads.txt Manager', 'airlinel-theme'), 'manage_options', 'airlinel-ads-txt', 'airlinel_ads_txt_page');
});
```

- [ ] **Step 2: Create dashboard page**

Create `/admin/dashboard-page.php`:

```php
<?php
/**
 * Airlinel Dashboard
 * Main hub for all Airlinel management
 */

if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}

$is_regional = defined('AIRLINEL_IS_REGIONAL_SITE') && AIRLINEL_IS_REGIONAL_SITE;
?>

<div class="wrap airlinel-dashboard-wrap">
    <h1><?php _e('Airlinel Platform Dashboard', 'airlinel-theme'); ?></h1>
    
    <div style="background: #f1f1f1; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h2><?php echo $is_regional ? __('Regional Site', 'airlinel-theme') : __('Main Site', 'airlinel-theme'); ?></h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
            
            <a href="<?php echo admin_url('admin.php?page=airlinel-settings'); ?>" class="airlinel-dashboard-card" style="text-decoration: none; padding: 20px; background: white; border-radius: 6px; border: 1px solid #ddd; transition: all 0.2s;">
                <h3 style="margin: 0 0 10px 0;">⚙️ <?php _e('Settings', 'airlinel-theme'); ?></h3>
                <p><?php _e('API keys, rates, zones', 'airlinel-theme'); ?></p>
            </a>
            
            <a href="<?php echo admin_url('edit.php?post_type=agencies'); ?>" class="airlinel-dashboard-card" style="text-decoration: none; padding: 20px; background: white; border-radius: 6px; border: 1px solid #ddd; transition: all 0.2s;">
                <h3 style="margin: 0 0 10px 0;">🏢 <?php _e('Agencies', 'airlinel-theme'); ?></h3>
                <p><?php _e('Manage partners', 'airlinel-theme'); ?></p>
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=airlinel-analytics'); ?>" class="airlinel-dashboard-card" style="text-decoration: none; padding: 20px; background: white; border-radius: 6px; border: 1px solid #ddd; transition: all 0.2s;">
                <h3 style="margin: 0 0 10px 0;">📊 <?php _e('Analytics', 'airlinel-theme'); ?></h3>
                <p><?php _e('Booking funnels & conversions', 'airlinel-theme'); ?></p>
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=airlinel-reservations'); ?>" class="airlinel-dashboard-card" style="text-decoration: none; padding: 20px; background: white; border-radius: 6px; border: 1px solid #ddd; transition: all 0.2s;">
                <h3 style="margin: 0 0 10px 0;">📅 <?php _e('Reservations', 'airlinel-theme'); ?></h3>
                <p><?php _e('All bookings', 'airlinel-theme'); ?></p>
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=airlinel-homepage-content'); ?>" class="airlinel-dashboard-card" style="text-decoration: none; padding: 20px; background: white; border-radius: 6px; border: 1px solid #ddd; transition: all 0.2s;">
                <h3 style="margin: 0 0 10px 0;">🏠 <?php _e('Homepage', 'airlinel-theme'); ?></h3>
                <p><?php _e('Manage sections', 'airlinel-theme'); ?></p>
            </a>
            
            <?php if ($is_regional): ?>
                <a href="<?php echo admin_url('admin.php?page=airlinel-regional-settings'); ?>" class="airlinel-dashboard-card" style="text-decoration: none; padding: 20px; background: #fffbeb; border-radius: 6px; border: 1px solid #fcd34d; transition: all 0.2s;">
                    <h3 style="margin: 0 0 10px 0;">🔌 <?php _e('API Connection', 'airlinel-theme'); ?></h3>
                    <p><?php _e('Regional site config', 'airlinel-theme'); ?></p>
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
        <h3><?php _e('Quick Help', 'airlinel-theme'); ?></h3>
        <ul style="list-style: disc; margin-left: 20px;">
            <li><?php printf(__('New to Airlinel? Start with %s', 'airlinel-theme'), '<a href="'.admin_url('admin.php?page=airlinel-settings').'">'.__('Settings', 'airlinel-theme').'</a>'); ?></li>
            <li><?php _e('View booking analytics in Analytics tab', 'airlinel-theme'); ?></li>
            <li><?php _e('Add agencies and track commissions', 'airlinel-theme'); ?></li>
        </ul>
    </div>
</div>

<style>
.airlinel-dashboard-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-color: #0073aa;
}
.airlinel-dashboard-card h3 {
    color: #0073aa;
}
</style>
```

- [ ] **Step 3: Update functions.php to register dashboard page function**

```php
function airlinel_dashboard_page() {
    include get_template_directory() . '/admin/dashboard-page.php';
}
```

- [ ] **Step 4: Remove old menu registrations**

Search functions.php for old `add_menu_page` and `add_submenu_page` calls and remove them (keep only the unified menu created in Step 1).

- [ ] **Step 5: Commit**

```bash
git add functions.php
git add admin/dashboard-page.php
git commit -m "feat: unified AIRLINEL admin menu with dashboard"
```

**Test:**
- Go to WordPress admin
- Verify "Airlinel" menu appears at top-left (after Dashboard)
- Verify all submenus load correctly
- Click each submenu item and verify correct page loads

---

### Task 3: Agencies - CPT Primary (Remove Redundancy)

**Files:**
- Modify: `/functions.php`
- Deprecate: `/admin/agencies-page.php` (rename to agencies-page.php.bak)

**Context:**
Currently: Agencies managed both via CPT (`edit.php?post_type=agencies`) AND custom admin page (`admin.php?page=airlinel_agencies`)
Goal: Use CPT only (native WordPress), delete custom page

- [ ] **Step 1: Verify CPT definition in functions.php**

Make sure this exists and is correct:

```php
register_post_type('agencies', array(
    'labels' => array('name' => 'Agencies', 'singular_name' => 'Agency'),
    'public' => false,
    'show_ui' => true,
    'menu_icon' => 'dashicons-building',
    'supports' => array('title', 'custom-fields'),
    'has_archive' => false,
    'rewrite' => false,
    'show_in_menu' => false,  // Will be shown in unified menu instead
));
```

- [ ] **Step 2: Add agency meta boxes to CPT**

Add to functions.php (if not already there):

```php
add_action('add_meta_boxes', function() {
    add_meta_box(
        'agency_meta',
        __('Agency Details', 'airlinel-theme'),
        function($post) {
            $code = get_post_meta($post->ID, 'agency_code', true);
            $email = get_post_meta($post->ID, 'email', true);
            $commission = get_post_meta($post->ID, 'commission_percent', true);
            
            wp_nonce_field('agency_meta_nonce', 'agency_nonce');
            ?>
            <table class="form-table">
                <tr>
                    <th><label><?php _e('Agency Code', 'airlinel-theme'); ?></label></th>
                    <td><input type="text" name="agency_code" value="<?php echo esc_attr($code); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label><?php _e('Email', 'airlinel-theme'); ?></label></th>
                    <td><input type="email" name="agency_email" value="<?php echo esc_attr($email); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label><?php _e('Commission %', 'airlinel-theme'); ?></label></th>
                    <td><input type="number" name="commission_percent" value="<?php echo esc_attr($commission); ?>" step="0.1" min="0" max="100" class="small-text"></td>
                </tr>
            </table>
            <?php
        },
        'agencies',
        'normal'
    );
});

add_action('save_post_agencies', function($post_id) {
    if (!isset($_POST['agency_nonce']) || !wp_verify_nonce($_POST['agency_nonce'], 'agency_meta_nonce')) {
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
});
```

- [ ] **Step 3: Update unified menu to link to CPT**

In Task 2's menu code, agencies submenu should be:

```php
add_submenu_page('airlinel-dashboard', __('Agencies', 'airlinel-theme'), __('Agencies', 'airlinel-theme'), 'manage_options', 'edit.php?post_type=agencies');
```

- [ ] **Step 4: Rename custom agencies page**

```bash
mv /wp-content/themes/airlinel-transfer-services/admin/agencies-page.php \
   /wp-content/themes/airlinel-transfer-services/admin/agencies-page.php.bak
```

- [ ] **Step 5: Remove custom agencies page registration from functions.php**

Delete these lines:

```php
// OLD - REMOVE THIS
add_action('admin_menu', function() {
    add_submenu_page(...'airlinel-agencies'...);
});
function airlinel_verify_agency_ajax() { ... }
```

- [ ] **Step 6: Commit**

```bash
git add functions.php
git rm admin/agencies-page.php
git commit -m "feat: use CPT for agencies (remove redundant custom page)"
```

**Test:**
- Go to admin → Airlinel → Agencies
- Verify you see the agencies list (CPT native interface)
- Click Add New Agency
- Fill in meta boxes (Agency Code, Email, Commission %)
- Save and verify fields are persisted
- Edit existing agency to verify meta appears

---

### Task 4: Booking Analytics Integration

**Files:**
- Create: `/includes/class-booking-analytics-tracker.php` (theme version of plugin)
- Create: `/assets/js/booking-tracker.js` (frontend tracking)
- Create: `/admin/analytics-dashboard-page.php` (admin dashboard, may reuse Phase 3 version)
- Modify: `/assets/js/booking.js` (add tracking calls)
- Modify: `/functions.php` (register DB table, AJAX handlers)

**Context:**
The plugin `/wp-content-plugins/airlinel-booking-analytics/` tracks booking funnel: search → vehicle selection → customer form → payment.
Goal: Integrate this tracking system into theme (not plugin) with custom DB table and analytics dashboard showing search-to-payment conversion.

- [ ] **Step 1: Create Booking_Analytics_Tracker class**

Create `/includes/class-booking-analytics-tracker.php`:

```php
<?php
/**
 * Booking Analytics Tracker
 * Tracks customer journey from search through payment
 */

class Airlinel_Booking_Analytics_Tracker {
    
    private $table_name = '';
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'airlinel_booking_searches';
    }
    
    /**
     * Create tracking table on theme activation
     */
    public function create_table() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            stage         VARCHAR(30)  NOT NULL DEFAULT 'search',
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
            source_site    VARCHAR(50)  NOT NULL DEFAULT 'main',
            source_language VARCHAR(10)  NOT NULL DEFAULT 'en_US',
            ip_address    VARCHAR(45)  NOT NULL DEFAULT '',
            created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_stage (stage),
            KEY idx_country (country),
            KEY idx_created (created_at),
            KEY idx_source (source_site)
        ) {$charset};";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
    
    /**
     * Log search query
     */
    public function log_search($data) {
        global $wpdb;
        
        $result = $wpdb->insert($this->table_name, array(
            'stage'       => 'search',
            'pickup'      => sanitize_text_field($data['pickup'] ?? ''),
            'dropoff'     => sanitize_text_field($data['dropoff'] ?? ''),
            'distance'    => floatval($data['distance'] ?? 0),
            'duration'    => sanitize_text_field($data['duration'] ?? ''),
            'pickup_date' => sanitize_text_field($data['pickup_date'] ?? null),
            'pickup_time' => sanitize_text_field($data['pickup_time'] ?? null),
            'country'     => strtoupper(sanitize_text_field($data['country'] ?? 'UK')),
            'source_site' => sanitize_text_field($data['source_site'] ?? 'main'),
            'source_language' => sanitize_text_field($data['source_language'] ?? 'en_US'),
            'ip_address'  => $this->get_client_ip(),
        ), array('%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s'));
        
        if (!$result) {
            return new WP_Error('insert_failed', 'Failed to insert search record');
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Log vehicle selection
     */
    public function log_vehicle_selected($record_id, $data) {
        global $wpdb;
        
        $result = $wpdb->update($this->table_name, array(
            'stage'         => 'vehicle_selected',
            'vehicle_name'  => sanitize_text_field($data['vehicle_name'] ?? ''),
            'vehicle_price' => sanitize_text_field($data['vehicle_price'] ?? ''),
        ), array('id' => intval($record_id)));
        
        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to update vehicle selection');
        }
        
        return true;
    }
    
    /**
     * Log customer form filling (before payment)
     */
    public function log_customer_info($record_id, $data) {
        global $wpdb;
        
        $result = $wpdb->update($this->table_name, array(
            'stage'         => 'form_filled',
            'customer_name' => sanitize_text_field($data['customer_name'] ?? ''),
            'customer_phone' => sanitize_text_field($data['customer_phone'] ?? ''),
            'customer_email' => sanitize_email($data['customer_email'] ?? ''),
            'flight_number' => sanitize_text_field($data['flight_number'] ?? ''),
            'agency_code'   => sanitize_text_field($data['agency_code'] ?? ''),
            'notes'         => sanitize_textarea_field($data['notes'] ?? ''),
        ), array('id' => intval($record_id)));
        
        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to update customer info');
        }
        
        return true;
    }
    
    /**
     * Log payment completion
     */
    public function log_payment_complete($record_id, $data) {
        global $wpdb;
        
        $result = $wpdb->update($this->table_name, array(
            'stage'             => 'payment_complete',
            'stripe_session_id' => sanitize_text_field($data['stripe_session_id'] ?? ''),
        ), array('id' => intval($record_id)));
        
        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to log payment');
        }
        
        return true;
    }
    
    /**
     * Get conversion funnel stats
     */
    public function get_funnel_stats($start_date = null, $end_date = null) {
        global $wpdb;
        
        if (!$start_date) $start_date = date('Y-m-d', strtotime('-30 days'));
        if (!$end_date) $end_date = date('Y-m-d');
        
        $where = $wpdb->prepare(
            "WHERE created_at BETWEEN %s AND %s",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        );
        
        $searches = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} $where");
        $vehicles = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} $where AND stage IN ('vehicle_selected','form_filled','payment_complete')");
        $filled = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} $where AND stage IN ('form_filled','payment_complete')");
        $paid = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} $where AND stage = 'payment_complete'");
        
        return array(
            'total_searches' => intval($searches),
            'vehicle_selected' => intval($vehicles),
            'form_filled' => intval($filled),
            'payment_complete' => intval($paid),
            'conversion_rate' => $searches > 0 ? round(($paid / $searches) * 100, 1) : 0,
        );
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        $ip = trim($ip);
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
    }
}
?>
```

- [ ] **Step 2: Create frontend booking tracker JavaScript**

Create `/assets/js/booking-tracker.js`:

```javascript
/**
 * Airlinel Booking Tracker
 * Tracks user journey from search through payment
 */

(function() {
    
    // Global tracking state
    window.airlinel_tracker = {
        record_id: null,
        nonce: typeof aba_data !== 'undefined' ? aba_data.nonce : '',
        ajax_url: typeof aba_data !== 'undefined' ? aba_data.ajax_url : '',
    };
    
    /**
     * Log search query
     */
    window.airlinel_track_search = function(pickup, dropoff, distance, duration, pickup_date, pickup_time, country) {
        const data = {
            action: 'airlinel_track_search',
            nonce: window.airlinel_tracker.nonce,
            pickup: pickup,
            dropoff: dropoff,
            distance: distance,
            duration: duration,
            pickup_date: pickup_date,
            pickup_time: pickup_time,
            country: country,
            source_site: typeof airlinel_source_site !== 'undefined' ? airlinel_source_site : 'main',
            source_language: document.documentElement.lang || 'en_US',
        };
        
        jQuery.post(window.airlinel_tracker.ajax_url, data, function(response) {
            if (response.success && response.data.id) {
                window.airlinel_tracker.record_id = response.data.id;
                console.log('[Tracker] Search logged, record ID:', window.airlinel_tracker.record_id);
            }
        });
    };
    
    /**
     * Log vehicle selection
     */
    window.airlinel_track_vehicle = function(vehicle_name, vehicle_price) {
        if (!window.airlinel_tracker.record_id) {
            console.warn('[Tracker] No record_id, creating new from vehicle selection');
            window.airlinel_track_search('', '', 0, '', '', '', 'UK');
            // Try again after a short delay
            setTimeout(function() {
                _do_track_vehicle(vehicle_name, vehicle_price);
            }, 500);
        } else {
            _do_track_vehicle(vehicle_name, vehicle_price);
        }
        
        function _do_track_vehicle(name, price) {
            const data = {
                action: 'airlinel_track_vehicle',
                nonce: window.airlinel_tracker.nonce,
                record_id: window.airlinel_tracker.record_id,
                vehicle_name: name,
                vehicle_price: price,
            };
            
            jQuery.post(window.airlinel_tracker.ajax_url, data, function(response) {
                if (response.success) {
                    console.log('[Tracker] Vehicle selection logged');
                }
            });
        }
    };
    
    /**
     * Log customer form filling
     */
    window.airlinel_track_customer_form = function(name, phone, email, flight_number, agency_code, notes) {
        const data = {
            action: 'airlinel_track_customer',
            nonce: window.airlinel_tracker.nonce,
            record_id: window.airlinel_tracker.record_id,
            customer_name: name,
            customer_phone: phone,
            customer_email: email,
            flight_number: flight_number,
            agency_code: agency_code,
            notes: notes,
        };
        
        jQuery.post(window.airlinel_tracker.ajax_url, data, function(response) {
            if (response.success) {
                console.log('[Tracker] Customer form logged');
            }
        });
    };
    
    /**
     * Log payment completion
     */
    window.airlinel_track_payment = function(stripe_session_id) {
        const data = {
            action: 'airlinel_track_payment',
            nonce: window.airlinel_tracker.nonce,
            record_id: window.airlinel_tracker.record_id,
            stripe_session_id: stripe_session_id,
        };
        
        jQuery.post(window.airlinel_tracker.ajax_url, data, function(response) {
            if (response.success) {
                console.log('[Tracker] Payment logged, journey complete');
            }
        });
    };
    
})();
```

- [ ] **Step 3: Register tracker table and AJAX handlers in functions.php**

```php
// Load tracker class
require_once get_template_directory() . '/includes/class-booking-analytics-tracker.php';

// Create table on theme activation
add_action('after_switch_theme', function() {
    $tracker = new Airlinel_Booking_Analytics_Tracker();
    $tracker->create_table();
});

// Enqueue tracker script on booking pages
add_action('wp_enqueue_scripts', function() {
    $is_booking = is_page('book-your-ride') || is_page('booking') || is_page('reservation');
    if (!$is_booking) return;
    
    wp_enqueue_script('airlinel-tracker', get_template_directory_uri() . '/assets/js/booking-tracker.js', array('jquery'), '1.0.0', true);
    
    wp_localize_script('airlinel-tracker', 'aba_data', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('airlinel_tracker'),
    ));
});

// AJAX: Log search
add_action('wp_ajax_airlinel_track_search', 'airlinel_track_search_handler');
add_action('wp_ajax_nopriv_airlinel_track_search', 'airlinel_track_search_handler');

function airlinel_track_search_handler() {
    check_ajax_referer('airlinel_tracker', 'nonce');
    
    $tracker = new Airlinel_Booking_Analytics_Tracker();
    $result = $tracker->log_search($_POST);
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    } else {
        wp_send_json_success(array('id' => $result));
    }
}

// AJAX: Log vehicle selection
add_action('wp_ajax_airlinel_track_vehicle', 'airlinel_track_vehicle_handler');
add_action('wp_ajax_nopriv_airlinel_track_vehicle', 'airlinel_track_vehicle_handler');

function airlinel_track_vehicle_handler() {
    check_ajax_referer('airlinel_tracker', 'nonce');
    
    $tracker = new Airlinel_Booking_Analytics_Tracker();
    $result = $tracker->log_vehicle_selected($_POST['record_id'], $_POST);
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    } else {
        wp_send_json_success();
    }
}

// AJAX: Log customer form
add_action('wp_ajax_airlinel_track_customer', 'airlinel_track_customer_handler');
add_action('wp_ajax_nopriv_airlinel_track_customer', 'airlinel_track_customer_handler');

function airlinel_track_customer_handler() {
    check_ajax_referer('airlinel_tracker', 'nonce');
    
    $tracker = new Airlinel_Booking_Analytics_Tracker();
    $result = $tracker->log_customer_info($_POST['record_id'], $_POST);
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    } else {
        wp_send_json_success();
    }
}

// AJAX: Log payment
add_action('wp_ajax_airlinel_track_payment', 'airlinel_track_payment_handler');
add_action('wp_ajax_nopriv_airlinel_track_payment', 'airlinel_track_payment_handler');

function airlinel_track_payment_handler() {
    check_ajax_referer('airlinel_tracker', 'nonce');
    
    $tracker = new Airlinel_Booking_Analytics_Tracker();
    $result = $tracker->log_payment_complete($_POST['record_id'], $_POST);
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    } else {
        wp_send_json_success();
    }
}
```

- [ ] **Step 4: Update booking.js to call tracker functions**

In `/assets/js/booking.js`, add tracking calls at key points:

```javascript
// When search form is submitted:
function submitSearch() {
    const pickup = document.getElementById('pickup').value;
    const dropoff = document.getElementById('dropoff').value;
    const distance = calculateDistance(pickup, dropoff);
    
    // Call tracker
    if (typeof window.airlinel_track_search === 'function') {
        airlinel_track_search(pickup, dropoff, distance, '', '', '', 'UK');
    }
    
    // Rest of search logic...
}

// When user selects a vehicle:
function selectVehicle(vehicle_id, vehicle_name, vehicle_price) {
    if (typeof window.airlinel_track_vehicle === 'function') {
        airlinel_track_vehicle(vehicle_name, vehicle_price);
    }
    
    // Rest of vehicle selection logic...
}

// When user fills customer form and clicks Next/Continue:
function submitCustomerForm() {
    const name = document.getElementById('customer_name').value;
    const phone = document.getElementById('customer_phone').value;
    const email = document.getElementById('customer_email').value;
    const flight = document.getElementById('flight_number').value || '';
    const agency = document.getElementById('agency_code').value || '';
    
    if (typeof window.airlinel_track_customer_form === 'function') {
        airlinel_track_customer_form(name, phone, email, flight, agency, '');
    }
    
    // Rest of form logic...
}

// When payment completes (on success page or after Stripe confirms):
function onPaymentComplete(stripe_session_id) {
    if (typeof window.airlinel_track_payment === 'function') {
        airlinel_track_payment(stripe_session_id);
    }
    
    // Rest of completion logic...
}
```

- [ ] **Step 5: Update analytics-page.php to use new tracking data**

Reuse or update existing `/admin/analytics-page.php` to query the new tracking table and display funnel stats.

Add to analytics page:

```php
<?php
require_once get_template_directory() . '/includes/class-booking-analytics-tracker.php';
$tracker = new Airlinel_Booking_Analytics_Tracker();
$funnel = $tracker->get_funnel_stats($filter_start_date, $filter_end_date);
?>

<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0;">
    <div style="background: #f0f9ff; padding: 15px; border-radius: 6px; border-left: 4px solid #0073aa;">
        <strong><?php _e('Total Searches', 'airlinel-theme'); ?></strong>
        <div style="font-size: 24px; color: #0073aa; margin-top: 5px;"><?php echo number_format($funnel['total_searches']); ?></div>
    </div>
    
    <div style="background: #fef3c7; padding: 15px; border-radius: 6px; border-left: 4px solid #f59e0b;">
        <strong><?php _e('Vehicle Selected', 'airlinel-theme'); ?></strong>
        <div style="font-size: 24px; color: #f59e0b; margin-top: 5px;"><?php echo number_format($funnel['vehicle_selected']); ?></div>
    </div>
    
    <div style="background: #dcfce7; padding: 15px; border-radius: 6px; border-left: 4px solid #10b981;">
        <strong><?php _e('Form Filled', 'airlinel-theme'); ?></strong>
        <div style="font-size: 24px; color: #10b981; margin-top: 5px;"><?php echo number_format($funnel['form_filled']); ?></div>
    </div>
    
    <div style="background: #f3e8ff; padding: 15px; border-radius: 6px; border-left: 4px solid #8b5cf6;">
        <strong><?php _e('Paid Bookings', 'airlinel-theme'); ?></strong>
        <div style="font-size: 24px; color: #8b5cf6; margin-top: 5px;"><?php echo number_format($funnel['payment_complete']); ?></div>
        <div style="font-size: 12px; margin-top: 10px;">
            <?php printf(__('Conversion: %s%%', 'airlinel-theme'), $funnel['conversion_rate']); ?>
        </div>
    </div>
</div>
```

- [ ] **Step 6: Commit**

```bash
git add includes/class-booking-analytics-tracker.php
git add assets/js/booking-tracker.js
git add admin/analytics-dashboard-page.php
git add assets/js/booking.js
git add functions.php
git commit -m "feat: integrated booking funnel analytics (search to payment tracking)"
```

**Test:**
- Go to booking page
- Enter search (check DB: new record with stage='search')
- Select vehicle (check DB: record updates to stage='vehicle_selected')
- Fill customer form (check DB: updates to stage='form_filled')
- Complete payment (check DB: updates to stage='payment_complete')
- Go to admin → Airlinel → Analytics
- Verify funnel stats show correct numbers
- Test that conversion rate calculates correctly

---

### Task 5: Page Content Settings - Regional Site Optimization

**Files:**
- Modify: `/admin/page-content-settings.php`
- Modify: `/includes/class-page-manager.php`

**Context:**
Page content settings currently manage: contact info, company description, business hours, trust indicators.
For regional sites: Each regional site can override these or use defaults from main site.

- [ ] **Step 1: Update Page_Manager class to support regional overrides**

In `/includes/class-page-manager.php`, update methods to check for regional site overrides:

```php
<?php
class Airlinel_Page_Manager {
    
    private $is_regional = false;
    private $regional_prefix = '';
    
    public function __construct() {
        $this->is_regional = defined('AIRLINEL_IS_REGIONAL_SITE') && AIRLINEL_IS_REGIONAL_SITE;
        $this->regional_prefix = $this->is_regional ? 'regional_' : '';
    }
    
    /**
     * Get contact info (with regional override support)
     */
    public static function get_contact_info() {
        $self = new self();
        return array(
            'phone'   => get_option($self->regional_prefix . 'airlinel_contact_phone', get_option('airlinel_contact_phone', '+44 20 XXXX XXXX')),
            'email'   => get_option($self->regional_prefix . 'airlinel_contact_email', get_option('airlinel_contact_email', 'contact@airlinel.com')),
            'address' => get_option($self->regional_prefix . 'airlinel_contact_address', get_option('airlinel_contact_address', '123 Main St, London')),
        );
    }
    
    /**
     * Get business hours (with regional override)
     */
    public static function get_business_hours() {
        $self = new self();
        $default = array(
            'monday' => array('open' => '08:00', 'close' => '22:00'),
            'tuesday' => array('open' => '08:00', 'close' => '22:00'),
            'wednesday' => array('open' => '08:00', 'close' => '22:00'),
            'thursday' => array('open' => '08:00', 'close' => '22:00'),
            'friday' => array('open' => '08:00', 'close' => '23:00'),
            'saturday' => array('open' => '09:00', 'close' => '23:00'),
            'sunday' => array('open' => '09:00', 'close' => '22:00'),
        );
        
        return get_option($self->regional_prefix . 'airlinel_business_hours', get_option('airlinel_business_hours', $default));
    }
    
    // Similar for other methods...
}
?>
```

- [ ] **Step 2: Update page-content-settings.php for regional context**

Update the form in `/admin/page-content-settings.php` to show:

```php
<?php
$is_regional = defined('AIRLINEL_IS_REGIONAL_SITE') && AIRLINEL_IS_REGIONAL_SITE;
?>

<div class="wrap">
    <h1>
        <?php _e('Page & Content Settings', 'airlinel-theme'); ?>
        <?php if ($is_regional): ?>
            <span style="font-size: 14px; color: #666; margin-left: 20px;">
                <?php _e('(Regional Site - override main site defaults)', 'airlinel-theme'); ?>
            </span>
        <?php endif; ?>
    </h1>
    
    <!-- Form with note about overrides -->
    <div style="background: #fffbeb; border: 1px solid #fcd34d; padding: 15px; border-radius: 6px; margin: 20px 0;">
        <?php if ($is_regional): ?>
            <p><?php _e('Leave fields blank to use main site defaults. Fill in values to override for this regional site.', 'airlinel-theme'); ?></p>
        <?php else: ?>
            <p><?php _e('These are the default values used by regional sites. Regional sites can override these settings.', 'airlinel-theme'); ?></p>
        <?php endif; ?>
    </div>
    
    <!-- Rest of form with conditional option keys based on $is_regional -->
</div>
```

- [ ] **Step 3: Add info box to unified menu**

In dashboard-page.php, add link:

```php
<a href="<?php echo admin_url('admin.php?page=airlinel-page-content'); ?>" ...>
    <h3><?php _e('Pages & Content', 'airlinel-theme'); ?></h3>
    <p><?php _e('About, Contact, Hours, etc.', 'airlinel-theme'); ?></p>
</a>
```

- [ ] **Step 4: Document regional override behavior**

In page-content-settings.php, add help section:

```php
<div class="postbox">
    <h2 class="hndle"><?php _e('How Regional Overrides Work', 'airlinel-theme'); ?></h2>
    <div class="inside">
        <ul style="list-style: disc; margin-left: 20px;">
            <li><?php _e('Main site: Set default values here', 'airlinel-theme'); ?></li>
            <li><?php _e('Regional sites: Leave blank to use main site defaults', 'airlinel-theme'); ?></li>
            <li><?php _e('Regional sites: Fill in values to customize for your region', 'airlinel-theme'); ?></li>
            <li><?php _e('Example: Main site hours are 8am-10pm, but Istanbul site can have different hours', 'airlinel-theme'); ?></li>
        </ul>
    </div>
</div>
```

- [ ] **Step 5: Commit**

```bash
git add admin/page-content-settings.php
git add includes/class-page-manager.php
git add admin/dashboard-page.php
git commit -m "feat: regional site content overrides (inherit main site defaults)"
```

**Test:**
- Main site: Fill in contact info, hours
- Regional site: Leave blank, verify main site defaults are used
- Regional site: Fill in values, verify they override main site values
- Template pages: Verify correct values display based on site type

---

## Summary

**5 Tasks, 25 Steps Total:**
1. **Regional Settings Manager** (database-driven config)
2. **Unified AIRLINEL Menu** (single admin hub)
3. **Agencies via CPT** (remove redundancy)
4. **Booking Analytics Integration** (search-to-payment tracking)
5. **Regional Content Overrides** (inherit with customization)

**Production Status After Completion:**
- ✅ No hardcoded wp-config.php required for regional sites
- ✅ Unified admin experience
- ✅ Complete booking funnel visibility
- ✅ Regional site content flexibility

---

Plan complete and saved to `docs/superpowers/plans/2026-04-26-admin-refactoring.md`.

**Execution choice:**

**1. Subagent-Driven (recommended)** - Fresh subagent per task, full reviews between tasks
**2. Inline Execution** - Run tasks sequentially in this session

Which approach?