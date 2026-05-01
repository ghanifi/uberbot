# Task 3.5: Regional API Client & Proxy Service Documentation

## Overview

Task 3.5 implements a transparent API proxy service that allows regional sites to seamlessly call the main site's API endpoints. This architecture enables:

- **Transparent Proxying**: Regional sites forward requests to the main site transparently
- **Caching Layer**: Search results cached for 5 minutes to reduce main site load
- **Graceful Fallback**: Cached data served if main site becomes unavailable
- **Error Handling**: Comprehensive error handling and logging

## Architecture

### Three-Layer Request Flow

```
Regional Site Frontend
    ↓
Local AJAX Endpoint (regional-site.com/wp-admin/admin-ajax.php)
    ↓
API Proxy Handler (Airlinel_API_Proxy_Handler)
    ↓
Main Site Client (Airlinel_Main_Site_Client)
    ↓
Main Site API (main-site.com/wp-json/airlinel/v1/*)
    ↓ (Response cached for 5 min)
Returns to Frontend
```

### Components

#### 1. Airlinel_Main_Site_Client (class-main-site-client.php)

Direct client for calling main site API endpoints. Runs on regional sites only.

**Configuration**:
```php
// In wp-config.php on regional sites:
define('AIRLINEL_MAIN_SITE_URL', 'https://main-site.com');
define('AIRLINEL_MAIN_SITE_API_KEY', 'your-regional-api-key');
```

**Methods**:
- `search($pickup, $dropoff, $country, $passengers, $currency)` - Search transfers
- `create_reservation($data)` - Create a reservation
- `get_reservation($id)` - Retrieve reservation details
- `get_health()` - Check if main site is reachable

**Features**:
- Automatic input sanitization and validation
- SSL verification enabled
- Detailed error logging
- 30-second timeout for requests

#### 2. Airlinel_API_Proxy_Handler (class-api-proxy-handler.php)

Proxy layer that intercepts and forwards requests. Runs on regional sites only.

**Registers**:
- **AJAX Endpoints**: For front-end integration (no auth required)
  - `wp_ajax_nopriv_airlinel_search` - Search endpoint
  - `wp_ajax_nopriv_airlinel_create_reservation` - Reservation creation
  - `wp_ajax_nopriv_airlinel_get_reservation` - Reservation retrieval

- **REST API Routes**: For programmatic access
  - `POST /wp-json/airlinel-proxy/v1/search`
  - `POST /wp-json/airlinel-proxy/v1/reservation/create`
  - `GET /wp-json/airlinel-proxy/v1/reservation/{id}`

**Features**:
- Transparent caching with 5-minute TTL
- Automatic fallback to cached data on main site timeout
- Request/response logging
- Error handling with user-friendly messages

## Configuration on Regional Sites

### Step 1: Define Constants in wp-config.php

```php
// Main site configuration
define('AIRLINEL_MAIN_SITE_URL', 'https://main-airport-transfers.com');
define('AIRLINEL_MAIN_SITE_API_KEY', 'regional-api-key-from-main-site');
```

### Step 2: Classes Auto-Load in functions.php

The proxy service automatically registers when `AIRLINEL_MAIN_SITE_URL` is defined:

```php
// ===== TASK 3.5: REGIONAL API CLIENT & PROXY SERVICE =====
if (defined('AIRLINEL_MAIN_SITE_URL')) {
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
}
```

## Front-End Integration

### Using AJAX for Search (For Regional Sites)

```javascript
// Call the proxy search endpoint
window.searchViaProxy(
    'London Heathrow',      // pickup
    'Central London',        // dropoff
    'UK',                   // country
    2,                      // passengers
    function(response) {
        // Success - response.vehicles contains available vehicles
        console.log('Available vehicles:', response.vehicles);
    },
    function(error) {
        // Error - fallback to cached data or show error message
        console.error('Search failed:', error);
    }
);
```

### Using AJAX for Reservation Creation

```javascript
// Create reservation via proxy
window.createReservationViaProxy({
    customer_name: 'John Doe',
    email: 'john@example.com',
    phone: '+44 1234 567890',
    pickup: 'London Heathrow',
    dropoff: 'Central London',
    date: '2026-05-15',
    passengers: 2,
    total_price: 75.50,
    currency: 'GBP',
    country: 'UK'
}, function(response) {
    // Reservation created successfully
    console.log('Reservation ID:', response.reservation_id);
}, function(error) {
    // Creation failed
    console.error('Reservation failed:', error);
});
```

### Using AJAX to Retrieve Reservation

```javascript
// Get reservation details
window.getReservationViaProxy(123, function(response) {
    console.log('Reservation details:', response);
}, function(error) {
    console.error('Failed to retrieve:', error);
});
```

### Using REST API Endpoints

For programmatic access, use the REST API endpoints:

```bash
# Search
curl -X POST https://regional-site.com/wp-json/airlinel-proxy/v1/search \
  -H "Content-Type: application/json" \
  -d '{
    "pickup": "London Heathrow",
    "dropoff": "Central London",
    "country": "UK",
    "passengers": 2,
    "currency": "GBP"
  }'

# Create Reservation
curl -X POST https://regional-site.com/wp-json/airlinel-proxy/v1/reservation/create \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "John Doe",
    "email": "john@example.com",
    "phone": "+44 1234 567890",
    "pickup": "London Heathrow",
    "dropoff": "Central London",
    "date": "2026-05-15",
    "passengers": 2,
    "total_price": 75.50,
    "currency": "GBP",
    "country": "UK"
  }'

# Get Reservation
curl https://regional-site.com/wp-json/airlinel-proxy/v1/reservation/123
```

## Caching Behavior

### Cache Key Generation

Cache keys are generated as: `airlinel_proxy_{md5_hash}`

For search: `md5(search_pickup_dropoff_country)`

This ensures:
- Same searches from different regions use different cache keys
- Cache is specific to the regional site

### Cache Lifespan

- **Default TTL**: 5 minutes (300 seconds)
- **Location**: WordPress transients (database-backed)
- **Automatic Cleanup**: WordPress handles transient expiration

### Fallback Behavior

```
1. Request arrives at proxy
2. Check cache for matching key
   ├─ Cache hit → Return cached data immediately
   └─ Cache miss → Continue to step 3
3. Forward request to main site
4. Main site responds (or times out after 30 seconds)
   ├─ Success → Cache result, return to client
   ├─ Error → Check cache
   │  ├─ Cache available → Return cached data with warning
   │  └─ No cache → Return error to client
   └─ Timeout → Check cache
      ├─ Cache available → Return cached data
      └─ No cache → Return timeout error
```

## Error Handling

### Input Validation

All inputs are validated and sanitized before sending to main site:

**Locations (pickup/dropoff)**:
- Must be non-empty strings
- Sanitized with `sanitize_text_field()`

**Country**:
- Must be one of: UK, TR
- Validated against whitelist

**Passengers**:
- Must be integer between 1 and 20
- Converted to int

**Currency**:
- Must be one of: GBP, EUR, TRY, USD
- Validated against whitelist

**Email**:
- Validated with `is_email()`
- Sanitized with `sanitize_email()`

### Error Responses

**AJAX Errors** (wp_send_json_error):
```json
{
  "success": false,
  "data": {
    "message": "User-friendly error message",
    "error": "Technical error details (if available)"
  }
}
```

**REST API Errors** (WP_Error):
```json
{
  "code": "error_code",
  "message": "User-friendly error message",
  "data": {
    "status": 400
  }
}
```

## Logging

All API calls and errors are logged to WordPress debug log with prefix `[Airlinel]`.

### Search Request Log
```
[Airlinel] Proxy: Returning cached search results
[Airlinel] Proxy search error: main site unavailable
[Airlinel] Proxy: Using cached data due to main site timeout
```

### Client Request Log
```
[Airlinel] MainSiteClient network error: connection timeout
[Airlinel] MainSiteClient HTTP error 401: Invalid API key
```

Enable debug logging in wp-config.php:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

## Health Monitoring

### Checking Main Site Health

```php
$client = new Airlinel_Main_Site_Client();
if ($client->get_health()) {
    echo "Main site is reachable";
} else {
    echo "Main site is unavailable";
}
```

### Proxy Health Status

Check the latest error log:
```bash
tail -f wp-content/debug.log | grep "Airlinel"
```

## Security Considerations

### API Key Management

- **Never** commit API keys to version control
- Store in wp-config.php using environment-based configuration
- Use different keys for different regional sites
- Rotate keys periodically

### SSL Verification

- Always enabled (`sslverify => true`)
- Ensures secure communication with main site
- Disable only in development/testing (not recommended)

### Request Validation

- All user inputs are validated before sending
- No direct SQL queries (uses WordPress methods)
- No file operations or exec() calls
- Sanitization at multiple layers

### Nonce Protection

AJAX endpoints use WordPress nonce verification:
```javascript
data: {
    action: 'airlinel_search',
    nonce: $('input[name="_wpnonce"]').val() || ''
}
```

## Troubleshooting

### Problem: Regional Site Can't Reach Main Site

**Symptoms**:
- All searches fail with timeout error
- "Main site is temporarily unavailable" message

**Solutions**:
1. Verify network connectivity: `ping main-site.com`
2. Check firewall rules - ensure HTTPS (port 443) is open
3. Verify API key in wp-config.php is correct
4. Check main site's SSL certificate is valid

### Problem: Searches Return Cached/Stale Data

**Symptoms**:
- New vehicles aren't showing up
- Old prices are displayed

**Solutions**:
1. Wait 5 minutes for cache to expire naturally
2. Clear WordPress transients (in admin panel or via WP-CLI)
3. Force refresh by clearing browser cache

### Problem: Reservation Creation Fails

**Symptoms**:
- "Failed to create reservation" error
- Email not received

**Solutions**:
1. Check all required fields are provided (name, email, date)
2. Verify email format is valid
3. Check main site logs for reservation creation errors
4. Ensure API key has create_reservation permission

### Problem: AJAX Endpoints Return 404

**Symptoms**:
- "Action not found" error from WordPress
- Browser console shows 404

**Solutions**:
1. Verify theme functions.php is loading proxy classes
2. Check AIRLINEL_MAIN_SITE_URL is defined in wp-config.php
3. Flush WordPress cache/rewrite rules
4. Check theme is active and not corrupted

## Performance Optimization

### Reducing Main Site Load

1. **Use Caching**: 5-minute cache dramatically reduces main site requests
2. **Batch Requests**: Avoid multiple simultaneous searches
3. **Implement Client-Side Caching**: Cache results in browser localStorage
4. **Use CDN**: Serve static assets from CDN to reduce bandwidth

### Monitoring Cache Hit Rate

Monitor transient usage in database:
```sql
SELECT * FROM wp_options 
WHERE option_name LIKE '_transient_airlinel_proxy_%'
ORDER BY option_modified DESC;
```

## Maintenance

### Database Cleanup

WordPress automatically expires transients, but old entries can accumulate:

```sql
DELETE FROM wp_options 
WHERE option_name LIKE '_transient_timeout_airlinel_proxy_%'
AND option_value < UNIX_TIMESTAMP();
```

### API Key Rotation

1. Generate new key on main site
2. Update wp-config.php on regional site
3. Test with pilot regional site first
4. Roll out to other regional sites
5. Retire old key on main site

## Testing

### Unit Test: Input Validation

```php
$client = new Airlinel_Main_Site_Client();

// Valid input
$result = $client->search('London', 'Manchester', 'UK', 2, 'GBP');

// Invalid inputs
$error = $client->search('', 'Manchester', 'UK', 2, 'GBP'); // Empty pickup
$error = $client->search('London', 'Manchester', 'INVALID', 2, 'GBP'); // Invalid country
$error = $client->search('London', 'Manchester', 'UK', 0, 'GBP'); // Invalid passengers
```

### Integration Test: End-to-End Flow

1. Regional site search → Main site responds
2. Results cached → Check cache expiry
3. Main site becomes unavailable → Cache fallback works
4. Main site recovered → Fresh results fetched

## Migration from Direct API Calls

If your regional site was calling main site API directly, update the calls:

### Before (Direct API Call)
```javascript
$.ajax({
    url: 'https://main-site.com/wp-json/airlinel/v1/search',
    headers: {'x-api-key': 'some-key'},
    data: {...}
});
```

### After (Via Proxy)
```javascript
window.searchViaProxy('pickup', 'dropoff', 'UK', 2,
    function(response) { /* success */ },
    function(error) { /* error */ }
);
```

Benefits of proxy approach:
- Automatic caching
- Fallback to cached data
- No API key exposure in client-side code
- Better error handling
- Consistent architecture across all regional sites

## Support & Monitoring

### Enable Debug Logging

Add to wp-config.php:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Logs available at: `wp-content/debug.log`

### Monitor Key Metrics

1. **Cache Hit Rate**: % of requests served from cache
2. **Main Site Availability**: % of requests that succeeded
3. **Average Response Time**: Including cache lookup
4. **Fallback Usage**: How often cached data is used due to main site failure

### Alert Conditions

Set up alerts for:
- Main site unavailable for > 5 minutes
- API key returning 401 errors
- Excessive JSON decode errors
- Network timeouts > 30% of requests
