# Task 5: Architecture & Design Decisions

## System Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                     WordPress Options Layer                      │
├─────────────────────────────────────────────────────────────────┤
│  Main Site Options       │  Regional Site Options (prefixed)    │
│  ─────────────────────   │  ──────────────────────────────────  │
│  airlinel_contact_phone  │  regional_airlinel_contact_phone     │
│  airlinel_contact_email  │  regional_airlinel_contact_email     │
│  airlinel_*_*            │  regional_airlinel_*_*               │
└─────────────────────────────────────────────────────────────────┘
                            ↑
                    Airlinel_Page_Manager
                  (Fallback Chain Handler)
                            ↑
                    ┌───────┴────────┐
                    ↓                ↓
            Frontend Display    Admin Interface
            (theme templates)   (page-content-settings.php)
```

## Core Design Patterns

### 1. Static Property Caching Pattern

```php
class Airlinel_Page_Manager {
    private static $is_regional = null;      // Lazy initialization
    private static $regional_prefix = '';    // Cached value
    
    private static function init_regional_context() {
        if (self::$is_regional === null) {   // Only initialize once
            self::$is_regional = defined('AIRLINEL_IS_REGIONAL_SITE') && AIRLINEL_IS_REGIONAL_SITE;
            self::$regional_prefix = self::$is_regional ? 'regional_' : '';
        }
    }
}
```

**Benefits:**
- Constant defined only once per request
- Prefix computed once, reused in all methods
- Minimal performance overhead
- Thread-safe on single-threaded PHP

### 2. Three-Level Fallback Pattern

```php
get_option(
    self::$regional_prefix . 'airlinel_setting',           // Level 1: Regional
    get_option(
        'airlinel_setting',                                 // Level 2: Main Site
        self::get_defaults('setting')                       // Level 3: Default
    )
)
```

**Advantages:**
- Explicit fallback chain
- No complex logic needed
- WordPress handles all caching
- Readable and maintainable
- Works with nested get_option() calls

### 3. Prefix-Based Storage Isolation

Instead of separate tables or complex structures:

```
Main Site:    airlinel_contact_phone = "..."
Regional:     regional_airlinel_contact_phone = "..."
```

**Rationale:**
- Simple and efficient
- Uses WordPress standard option system
- No database schema changes
- Automatic backup/export compatibility
- Easy to find related options

## Data Flow Diagrams

### Write Flow (Form Submission)

```
Form Submission
    ↓
Nonce Verification
    ↓
Capability Check (manage_options)
    ↓
Input Sanitization
  ├─ sanitize_text_field()
  ├─ sanitize_email()
  ├─ wp_kses_post()
  └─ intval()
    ↓
Determine Option Key
  ├─ If regional: add 'regional_' prefix
  └─ If main: use standard key
    ↓
Update/Delete Option
  ├─ If empty: delete_option()
  └─ If filled: update_option()
    ↓
Display Success Message
```

### Read Flow (Frontend Display)

```
Airlinel_Page_Manager::get_contact_info()
    ↓
init_regional_context()
  └─ Set $is_regional and $regional_prefix (cached)
    ↓
For each field (phone, email, address):
    ├─ Get option(regional_prefix + key)
    ├─ If empty, get option(main key)
    ├─ If empty, return default
    └─ Return value
    ↓
wp_kses_post() for output safety
    ↓
Return to caller
```

## Database Schema

### WordPress Options Table Entries

```sql
-- Main Site
INSERT INTO wp_options (option_name, option_value, autoload) VALUES
('airlinel_contact_phone', '+44 20 XXXX XXXX', 'yes'),
('airlinel_contact_email', 'contact@airlinel.com', 'yes'),
('airlinel_contact_address', '123 Main St, London', 'yes'),
('airlinel_company_description', 'Premium airport transfers...', 'yes'),
('airlinel_company_mission', 'Deliver exceptional service...', 'yes'),
('airlinel_company_history', 'Founded in 2009...', 'yes'),
('airlinel_years_in_business', '15', 'yes'),
('airlinel_customers_served', '50000', 'yes'),
('airlinel_fleet_size', '250', 'yes'),
('airlinel_daily_rides', '1000', 'yes'),
('airlinel_business_hours', 'a:7:{s:6:"monday";a:2:{...}}', 'yes');

-- Regional Site (Istanbul)
INSERT INTO wp_options (option_name, option_value, autoload) VALUES
('regional_airlinel_contact_phone', '+90 212 XXXX XXXX', 'yes'),
-- Other fields not set (uses main site values via fallback)
('regional_airlinel_business_hours', 'a:7:{...}', 'yes');
```

### Option Naming Convention

**Pattern:** `[regional_]airlinel_[module]_[setting]`

Examples:
- `airlinel_contact_phone` - Main site contact
- `regional_airlinel_contact_phone` - Regional override
- `airlinel_business_hours` - Main site hours (array)
- `regional_airlinel_business_hours` - Regional hours (array)

### Business Hours Array Structure

```php
$hours = array(
    'monday' => array('open' => '06:00', 'close' => '23:00'),
    'tuesday' => array('open' => '06:00', 'close' => '23:00'),
    'wednesday' => array('open' => '06:00', 'close' => '23:00'),
    'thursday' => array('open' => '06:00', 'close' => '23:00'),
    'friday' => array('open' => '06:00', 'close' => '23:00'),
    'saturday' => array('open' => '09:00', 'close' => '23:00'),
    'sunday' => array('open' => '09:00', 'close' => '22:00'),
);
```

Stored in database as serialized PHP:
```
a:7:{s:6:"monday";a:2:{s:4:"open";s:5:"06:00";s:5:"close";s:5:"23:00";}...}
```

## Security Architecture

### Input Validation Flow

```
User Input
    ↓
1. Nonce Verification
   ├─ Check: wp_verify_nonce($_POST[token], action)
   └─ Fail: wp_die('Nonce verification failed')
    ↓
2. Capability Check
   ├─ Check: current_user_can('manage_options')
   └─ Fail: wp_die('Unauthorized access')
    ↓
3. Input Sanitization (Type-Based)
   ├─ Text: sanitize_text_field()
   ├─ Email: sanitize_email()
   ├─ Rich Text: wp_kses_post()
   └─ Integer: intval()
    ↓
4. Output Escaping (Context-Based)
   ├─ Attribute: esc_attr()
   ├─ Text: esc_html()
   ├─ Textarea: esc_textarea()
   └─ HTML: wp_kses_post()
    ↓
Stored in Database
```

### Nonce Strategy

```php
// Generation (in form)
wp_nonce_field('airlinel_page_settings_action', 'airlinel_page_settings_nonce');

// Verification (on POST)
if (!wp_verify_nonce($_POST['airlinel_page_settings_nonce'], 'airlinel_page_settings_action')) {
    wp_die('Nonce verification failed');
}
```

One nonce per form, unique action string prevents CSRF attacks.

## Admin Interface Architecture

### Form Structure

```html
<form method="post">
    [Nonce Field]
    
    [Information Box - Context Aware]
        ├─ If main site: "These are defaults"
        └─ If regional: "Override for this region"
    
    [Contact Information Section]
        ├─ Phone (text input)
        ├─ Email (email input)
        └─ Address (textarea)
    
    [Company Information Section]
        ├─ Description (wp_editor)
        ├─ Mission (wp_editor)
        └─ History (wp_editor)
    
    [Trust Indicators Section]
        ├─ Years in Business (number)
        ├─ Customers Served (number)
        ├─ Fleet Size (number)
        └─ Daily Rides (number)
    
    [Business Hours Section]
        └─ Table with 7 rows (days)
            ├─ Day name
            ├─ Opening time (time input)
            └─ Closing time (time input)
    
    [Submit Button]
    
    [Help Section - How Overrides Work]
</form>
```

### Conditional UI Elements

```php
<?php if ($is_regional): ?>
    <!-- Regional-specific UI -->
    <span class="airlinel-regional-badge">Regional Site</span>
    <p>Leave blank to use main site defaults</p>
<?php else: ?>
    <!-- Main site UI -->
    <p>These are defaults for regional sites</p>
<?php endif; ?>
```

## Rich Text Editor Configuration

### Why wp_editor()?

```php
wp_editor(
    $company_description,        // Content to edit
    'company_description',       // Unique editor ID
    array(
        'textarea_rows' => 5,    // Visual size
        'media_buttons' => false,  // Security: prevent file upload
        'teeny' => false,        // Full toolbar, not minimal
    )
);
```

**Configuration Reasoning:**
- `media_buttons: false` - Prevents accidental file uploads
- `teeny: false` - Full formatting options for professional content
- `textarea_rows: 5` - Reasonable default without overwhelming UI

### Sanitization in wp_editor()

WordPress automatically:
1. Outputs content through the editor safely
2. Sanitizes on save via wp_kses_post()
3. Escapes on output
4. Removes malicious scripts/code

## Performance Considerations

### Query Optimization

```
Initial Load:
  1x check: defined('AIRLINEL_IS_REGIONAL_SITE')
  1x cache: Set static $is_regional and $regional_prefix
  
Per get_contact_info() call:
  1x get_option('regional_airlinel_contact_phone')     // Cached
  1x get_option('airlinel_contact_phone')              // Cached
  Total: 2 database queries (both cached by WordPress)

Static caching prevents:
  - Repeated constant checks
  - Repeated prefix construction
  - Unnecessary condition evaluations
```

### WordPress get_option() Caching

WordPress automatically caches all options in memory:
```php
get_option('key')  // First call: DB query, then cached
get_option('key')  // Subsequent: Returns from cache
```

Our implementation leverages this existing optimization.

### Database Indexes

WordPress automatically indexes wp_options:
- Primary key: option_id
- Unique key: option_name (UNIQUE)
- Lookup by option_name is O(1)

No additional indexes needed.

## Scalability Considerations

### Multi-Language Support

Current implementation ready for translation:
```php
_e('Regional Site Mode', 'airlinel-theme')  // Translatable string
__('Save Changes', 'airlinel-theme')         // Translatable string
```

Each regional site can have translations:
```
Main Site (English):      English content
Regional Site (French):   French content + regional overrides
Regional Site (German):   German content + regional overrides
```

### Multi-Regional Network

System supports N regional sites:
- Each gets own `regional_airlinel_*` options
- No conflicts (all prefixed uniquely)
- Independent of other regions
- Scales horizontally

### Caching Strategy for Distributed Systems

If using Redis/Memcached:
```php
// WordPress handles caching
get_option() checks object cache first
Falls back to database on cache miss
```

No changes needed for compatibility.

## Testing Architecture

### Unit Test Scenarios

```php
// Test 1: Regional override precedence
define('AIRLINEL_IS_REGIONAL_SITE', true);
$contact = Airlinel_Page_Manager::get_contact_info();
assert($contact['phone'] === regional_value);

// Test 2: Main site fallback
define('AIRLINEL_IS_REGIONAL_SITE', false);
$contact = Airlinel_Page_Manager::get_contact_info();
assert($contact['phone'] === main_value);

// Test 3: Default fallback
delete_option('regional_airlinel_contact_phone');
delete_option('airlinel_contact_phone');
$contact = Airlinel_Page_Manager::get_contact_info();
assert($contact['phone'] === default_value);
```

### Integration Test Path

```
1. Set main site values
2. Verify main site displays them
3. Verify regional site inherits them
4. Set regional overrides
5. Verify regional site shows overrides
6. Verify main site unchanged
7. Clear regional override
8. Verify regional site falls back to main
```

## Error Handling Strategy

### Admin Form Submission Errors

```php
// Nonce failure
if (!wp_verify_nonce(...)) {
    wp_die('Nonce verification failed');  // Clear error message
}

// Capability failure
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized access');  // Clear error message
}
```

### Frontend Display Fallback

```php
// If all options empty
$value = get_option('regional_' . $key)
         ?: get_option($key)
         ?: get_default($key);

// Always returns something, never null
```

## Future Enhancement Hooks

### Extension Points

1. **Filter Hook for Defaults**
   ```php
   $defaults = apply_filters('airlinel_page_defaults', $defaults);
   ```

2. **Action Hook After Save**
   ```php
   do_action('airlinel_page_settings_saved', $option_key, $value);
   ```

3. **Filter Hook for Option Display**
   ```php
   $value = apply_filters('airlinel_page_option_display', $value, $option_key);
   ```

## Conclusion

This architecture provides:
- **Simplicity**: Straightforward prefix-based isolation
- **Performance**: Minimal queries, leverages WordPress caching
- **Security**: Multiple validation layers, nonce protection
- **Scalability**: Works with unlimited regional sites
- **Maintainability**: Clear patterns, easy to understand
- **Extensibility**: Hooks for future enhancements
