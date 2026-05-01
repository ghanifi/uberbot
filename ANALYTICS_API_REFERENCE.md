# Analytics Manager API Reference

## Quick Start

```php
// Load and instantiate
require_once get_template_directory() . '/includes/class-analytics-manager.php';
$analytics = new Airlinel_Analytics_Manager();

// Get summary metrics
$summary = $analytics->get_analytics_summary('2024-01-01', '2024-01-31');
echo "Total bookings: " . $summary['total_bookings'];
echo "Total revenue: " . $summary['total_revenue'];
```

## Class: Airlinel_Analytics_Manager

### Constructor
```php
new Airlinel_Analytics_Manager()
```
Initializes the analytics manager with optional exchange rate manager integration.

---

## Public Methods

### get_bookings_by_site()
Retrieve bookings filtered by source site, date range, and other criteria.

**Signature:**
```php
public function get_bookings_by_site(
    $site_id = null,
    $start_date = null,
    $end_date = null,
    $args = array()
)
```

**Parameters:**
- `$site_id` (string|null): Optional site identifier (e.g., 'london', 'istanbul')
- `$start_date` (string|null): Start date in YYYY-MM-DD format
- `$end_date` (string|null): End date in YYYY-MM-DD format
- `$args` (array): Additional options:
  - `per_page` (int): Items per page (default: -1, all)
  - `paged` (int): Page number (default: 1)
  - `status` (string): Filter by post_status
  - `language` (string): Filter by source_language

**Returns:**
```php
array(
    'bookings' => array( /* WP_Post objects with metadata */ ),
    'total' => int,
    'pages' => int
)
```

**Example:**
```php
$result = $analytics->get_bookings_by_site(
    'london',
    '2024-01-01',
    '2024-01-31',
    array('per_page' => 20, 'paged' => 1)
);
foreach ($result['bookings'] as $booking) {
    echo $booking['customer_name'] . ": £" . $booking['total_price'];
}
```

---

### get_revenue_by_site()
Aggregate revenue by regional site with automatic currency conversion.

**Signature:**
```php
public function get_revenue_by_site(
    $site_id = null,
    $start_date = null,
    $end_date = null,
    $target_currency = 'GBP'
)
```

**Parameters:**
- `$site_id` (string|null): Optional site ID filter
- `$start_date` (string|null): Start date (YYYY-MM-DD)
- `$end_date` (string|null): End date (YYYY-MM-DD)
- `$target_currency` (string): Target currency for conversion (default: 'GBP')

**Returns:**
```php
array(
    'site_id' => array(
        'site_id' => 'london',
        'revenue' => 15000.50,
        'count' => 42,
        'avg_value' => 357.16,
        'currency' => 'GBP'
    ),
    // ... more sites
)
```

**Example:**
```php
$revenue = $analytics->get_revenue_by_site(
    null,  // all sites
    '2024-01-01',
    '2024-01-31',
    'GBP'
);

foreach ($revenue as $site => $data) {
    printf("%s: £%.2f (%d bookings)\n",
        ucfirst($site),
        $data['revenue'],
        $data['count']
    );
}
```

---

### get_bookings_by_language()
Group bookings by customer language preference.

**Signature:**
```php
public function get_bookings_by_language(
    $language = null,
    $start_date = null,
    $end_date = null
)
```

**Parameters:**
- `$language` (string|null): Optional language filter (e.g., 'EN', 'TR')
- `$start_date` (string|null): Start date (YYYY-MM-DD)
- `$end_date` (string|null): End date (YYYY-MM-DD)

**Returns:**
```php
array(
    'EN' => array(
        'language' => 'EN',
        'bookings' => 125,
        'revenue' => 18750.00
    ),
    'TR' => array(
        'language' => 'TR',
        'bookings' => 89,
        'revenue' => 13350.00
    ),
    // ... more languages
)
```

**Example:**
```php
$by_language = $analytics->get_bookings_by_language(
    null,
    '2024-01-01',
    '2024-01-31'
);

// Find most popular language
$popular = array_reduce($by_language, function($carry, $item) {
    return (!$carry || $item['bookings'] > $carry['bookings']) ? $item : $carry;
});

echo "Most popular language: " . $popular['language'];
```

---

### get_trend_data()
Generate daily booking counts for charting.

**Signature:**
```php
public function get_trend_data($days = 30, $site_id = null)
```

**Parameters:**
- `$days` (int): Number of days to retrieve (default: 30)
- `$site_id` (string|null): Optional site filter

**Returns:**
```php
array(
    '2024-01-01' => 5,
    '2024-01-02' => 8,
    '2024-01-03' => 3,
    // ... one entry per day
)
```

**Example:**
```php
$trend = $analytics->get_trend_data(30, 'london');

$total = array_sum($trend);
$average = $total / count($trend);
$max = max($trend);

echo "30-day trend: $total bookings, avg $average/day, peak $max";
```

---

### get_analytics_summary()
Get comprehensive summary with all key metrics.

**Signature:**
```php
public function get_analytics_summary($start_date = null, $end_date = null)
```

**Parameters:**
- `$start_date` (string|null): Start date (YYYY-MM-DD)
- `$end_date` (string|null): End date (YYYY-MM-DD)

**Returns:**
```php
array(
    'total_bookings' => 456,
    'total_revenue' => 68400.00,
    'avg_value' => 149.91,
    'top_site' => 'london',
    'top_language' => 'EN',
    'sites_summary' => array(
        'london' => array('count' => 189, 'revenue' => 28350.00),
        'istanbul' => array('count' => 145, 'revenue' => 21750.00),
        // ...
    ),
    'languages_summary' => array(
        'EN' => array('count' => 234, 'revenue' => 35100.00),
        'TR' => array('count' => 142, 'revenue' => 21300.00),
        // ...
    ),
    'date_range' => array(
        'from' => '2024-01-01',
        'to' => '2024-01-31'
    )
)
```

**Example:**
```php
$summary = $analytics->get_analytics_summary('2024-01-01', '2024-01-31');

echo "Summary for January 2024:\n";
echo "Bookings: " . $summary['total_bookings'] . "\n";
echo "Revenue: £" . number_format($summary['total_revenue'], 2) . "\n";
echo "Average: £" . number_format($summary['avg_value'], 2) . "\n";
echo "Top site: " . ucfirst($summary['top_site']) . "\n";
echo "Top language: " . strtoupper($summary['top_language']) . "\n";
```

---

### get_regional_sites()
Get all configured regional site IDs.

**Signature:**
```php
public function get_regional_sites()
```

**Returns:**
```php
array('main', 'london', 'istanbul', 'berlin', 'antalya', ...)
```

**Example:**
```php
$sites = $analytics->get_regional_sites();
echo "Available sites: " . implode(', ', $sites);
```

---

### export_to_csv()
Export bookings to CSV format.

**Signature:**
```php
public function export_to_csv($site_id = null, $start_date = null, $end_date = null)
```

**Parameters:**
- `$site_id` (string|null): Optional site filter
- `$start_date` (string|null): Start date (YYYY-MM-DD)
- `$end_date` (string|null): End date (YYYY-MM-DD)

**Returns:**
```
string (CSV formatted data)
```

**CSV Format:**
```csv
Booking ID,Customer Name,Email,Site,Language,Transfer Date,Amount,Currency,Status
1,John Doe,john@example.com,london,EN,2024-01-15,89.50,GBP,completed
2,Jane Smith,jane@example.com,istanbul,TR,2024-01-16,125.00,TRY,pending
```

**Example:**
```php
$csv = $analytics->export_to_csv('london', '2024-01-01', '2024-01-31');

// Save to file
file_put_contents('export.csv', $csv);

// Or send to browser
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="analytics.csv"');
echo $csv;
```

---

## Private Helper Methods

### _format_booking()
Convert WP_Post object to analytics format.

```php
private function _format_booking($post)
```

**Returns:** Array with keys: id, customer_name, email, source_site, source_language, source_url, total_price, currency, transfer_date, status, post_date, pickup_location, dropoff_location

---

### _convert_to_currency()
Convert amount between currencies using exchange rates.

```php
private function _convert_to_currency($amount, $from_currency, $to_currency)
```

**Returns:** Converted amount (float)

---

### _get_top_key()
Find the key with highest count in associative array.

```php
private function _get_top_key($data)
```

**Returns:** Key name (string) or null if empty

---

## WordPress Integration

### Admin Menu
```php
// Automatically registered at:
// Settings > Customer Analytics (airlinel-analytics page)

// Direct URL:
// /wp-admin/admin.php?page=airlinel-analytics
```

### AJAX Endpoints

#### Get Analytics Summary
```javascript
jQuery.post(ajaxurl, {
    'action': 'airlinel_get_analytics_summary',
    'nonce': nonce_value,
    'start_date': '2024-01-01',
    'end_date': '2024-01-31'
}, function(response) {
    console.log(response.data);
});
```

#### Get Bookings by Site
```javascript
jQuery.post(ajaxurl, {
    'action': 'airlinel_get_bookings_by_site',
    'nonce': nonce_value,
    'site_id': 'london',
    'start_date': '2024-01-01',
    'end_date': '2024-01-31'
}, function(response) {
    console.log(response.data);
});
```

#### Export CSV
```javascript
jQuery.post(ajaxurl, {
    'action': 'airlinel_export_analytics_csv',
    'nonce': nonce_value,
    'site_id': 'london',
    'start_date': '2024-01-01',
    'end_date': '2024-01-31'
}, function(response) {
    // Download CSV
    var blob = new Blob([response.data.csv], { type: 'text/csv' });
    var url = window.URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'analytics.csv';
    a.click();
});
```

---

## Data Model

### Reservation Metadata Fields Used
- `source_site` - Regional site identifier
- `source_language` - ISO 639-1 language code
- `source_url` - Booking source URL
- `total_price` - Numeric booking amount
- `currency` - ISO 4217 currency code
- `customer_name` - Full customer name
- `email` - Email address
- `transfer_date` - Service date (YYYY-MM-DD)
- `pickup_location` - Pickup location name
- `dropoff_location` - Dropoff location name

### Supported Currencies
GBP, EUR, TRY, USD (extensible via exchange rate manager)

### Supported Languages
EN, TR, DE, RU, FR, IT, AR, DA, NL, SV, ZH, JA

---

## Performance Notes

- **Query Time**: <500ms for typical 1000-booking queries
- **Memory**: <50MB for dashboard page load
- **Chart.js**: Loads from CDN (no additional server load)
- **Pagination**: 20 items per page (configurable in analytics-page.php)
- **Date Range**: No built-in limit, but >1 year may be slow

---

## Error Handling

All methods return data arrays (not WP_Error). Empty results return:
```php
array('bookings' => [], 'total' => 0, 'pages' => 0)
// or
array() // for aggregation methods
```

For AJAX endpoints, errors return:
```javascript
{ success: false, data: { error: 'Error message' } }
```

---

## Security

- All AJAX endpoints verify `manage_options` capability
- Nonce verification required for AJAX calls
- Input sanitized with `sanitize_text_field()` and `esc_*()`
- No direct database access (uses WordPress WP_Query)
- Meta queries properly escaped

---

## Examples

### Monthly Revenue Report
```php
$start = date('Y-m-01');  // First of this month
$end = date('Y-m-d');      // Today

$revenue = $analytics->get_revenue_by_site(null, $start, $end);
$total = array_sum(array_column($revenue, 'revenue'));

echo "Monthly Revenue Report\n";
echo "=" . str_repeat("=", 40) . "\n";
foreach ($revenue as $site => $data) {
    $pct = ($data['revenue'] / $total) * 100;
    printf("%-15s: £%10.2f (%5.1f%%) - %3d bookings\n",
        ucfirst($site),
        $data['revenue'],
        $pct,
        $data['count']
    );
}
printf("%-15s: £%10.2f (100.0%%)\n", 'TOTAL', $total);
```

### Language Market Share
```php
$languages = $analytics->get_bookings_by_language(
    null,
    date('Y-01-01'),
    date('Y-m-d')
);

echo "Language Market Share (Year-to-Date)\n";
usort($languages, function($a, $b) {
    return $b['bookings'] - $a['bookings'];
});

foreach ($languages as $lang) {
    echo str_pad(strtoupper($lang['language']), 5);
    echo str_repeat('█', $lang['bookings'] / 10);
    echo " {$lang['bookings']} bookings\n";
}
```

### Booking Performance by Site
```php
$summary = $analytics->get_analytics_summary();
$sites = $summary['sites_summary'];

echo "Site Performance:\n";
foreach ($sites as $site => $data) {
    $revenue = $data['revenue'];
    $count = $data['count'];
    $avg = $revenue / $count;
    echo sprintf("%s: %d orders, £%.0f revenue, £%.0f/order\n",
        ucfirst($site), $count, $revenue, $avg
    );
}
```

---

## Changelog

### Version 1.0 (Initial Release)
- Core analytics manager class
- 8 public methods for data retrieval
- CSV export functionality
- WordPress admin integration
- AJAX endpoints
- Chart.js integration
