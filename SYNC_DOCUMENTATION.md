# Airlinel Data Synchronization & Monitoring System

## Overview

Task 3.4 implements a comprehensive data synchronization and monitoring system for the Airlinel airport transfer platform. This system ensures data consistency across the main site (airlinel.com) and regional sites (antalya.airlinel.com, istanbul.airlinel.com, etc.).

## Architecture

### Data Sync Strategy

```
┌─────────────────────────────────────────────────────────────┐
│                        MAIN SITE                            │
│  (airlinel.com - Single Source of Truth)                    │
│                                                              │
│  ├─ Vehicles Management (Admin interface)                   │
│  ├─ Pricing Zones & Rates (Admin interface)                │
│  ├─ Exchange Rates (Exchange Rates Manager)                │
│  └─ Reservations (Created here, tracked by source site)     │
│                                                              │
└─────────────────────────────────────────────────────────────┘
              ↓        ↓        ↓        ↓
   API Query  │        │        │        │
              ↓        ↓        ↓        ↓
┌──────────┐  ┌──────────────┐  ┌──────────────┐
│ ANTALYA  │  │ ISTANBUL     │  │  OTHER       │
│ SITE     │  │  SITE        │  │  SITES       │
├──────────┤  ├──────────────┤  ├──────────────┤
│ Vehicles │  │ Vehicles     │  │ Vehicles     │
│ (cached) │  │ (cached)     │  │ (cached)     │
│          │  │              │  │              │
│ Pricing  │  │ Pricing      │  │ Pricing      │
│ (via API)│  │ (via API)    │  │ (via API)    │
│          │  │              │  │              │
│ Exchange │  │ Exchange     │  │ Exchange     │
│ Rates    │  │ Rates        │  │ Rates        │
│(5min TTL)│  │ (5min TTL)   │  │ (5min TTL)   │
│          │  │              │  │              │
│ Booking  │  │ Booking      │  │ Booking      │
│ Requests →→→ Forward to     →→→ Forward to    │
│          │  │ Main Site    │  │ Main Site    │
└──────────┘  └──────────────┘  └──────────────┘
```

### Data Types & Sync Strategy

| Data Type | Source | Sync Method | Freshness | Notes |
|-----------|--------|-------------|-----------|-------|
| **Vehicles** | Main site only | Scheduled hourly | Hourly | Managed in admin, regional sites query via API |
| **Pricing** | Main site only | API proxy | Real-time | Zone pricing & km rates retrieved via API |
| **Exchange Rates** | Main site only | Manual/scheduled update | Up to 24h | Updated on main site, regional sites cache with 5min TTL |
| **Users** | Shared WP table | Database shared | Real-time | Same user table across all sites (WordPress MU setup) |
| **Reservations** | Created on main | All routed to main | Real-time | Created centrally, tracked with source_site metadata |

## Components

### 1. Airlinel_Data_Sync_Manager Class
**File:** `/includes/class-data-sync-manager.php`

Core class managing all synchronization operations.

#### Public Methods

```php
// Get vehicles with optional country filter
get_vehicles($country = null): array

// Update exchange rates
sync_exchange_rates($rates = null, $from_api = false): bool

// Get current exchange rates with timestamp
get_exchange_rates(): array

// Verify all data is in sync - returns health status
verify_sync_health(): array

// Get last sync timestamp for a data type
get_last_sync_time($data_type): int

// Log a synchronization event
log_sync_event($data_type, $status, $message): bool

// Get recent sync events
get_sync_log($limit = 50): array

// Clear the sync log
clear_sync_log(): bool

// Get sync statistics for dashboard
get_sync_stats(): array

// Perform full vehicle sync
sync_vehicles(): array

// Schedule periodic sync jobs
static schedule_sync_jobs(): void

// Unschedule sync jobs
static unschedule_sync_jobs(): void
```

#### Usage Examples

```php
// Initialize the sync manager
$sync_mgr = new Airlinel_Data_Sync_Manager();

// Get all vehicles
$vehicles = $sync_mgr->get_vehicles();

// Get vehicles only for Turkey
$tr_vehicles = $sync_mgr->get_vehicles('TR');

// Update exchange rates
$sync_mgr->sync_exchange_rates(array(
    'GBP' => 1.00,
    'EUR' => 1.18,
    'TRY' => 42.50,
    'USD' => 1.27
));

// Check sync health
$health = $sync_mgr->verify_sync_health();
if ($health['overall'] === 'healthy') {
    // All systems go
}

// Get sync stats
$stats = $sync_mgr->get_sync_stats();

// Log an event
$sync_mgr->log_sync_event('vehicles', 'success', 'Vehicle sync completed: 45 vehicles');

// Get recent events
$log = $sync_mgr->get_sync_log(25);
```

### 2. Sync Dashboard Admin Page
**File:** `/admin/sync-dashboard.php`
**Menu Location:** Settings > Synchronization Dashboard

The admin dashboard provides visual monitoring of all sync operations:

#### Features

1. **Sync Status Overview**
   - Overall health indicator (green/yellow/red)
   - Data counts (vehicles, users, reservations)
   - Quick status checks

2. **Vehicles Synchronization**
   - Total vehicle count
   - Breakdown by country (UK/TR)
   - Last sync timestamp
   - Manual sync button
   - Recent vehicle sync events log

3. **Exchange Rates Management**
   - Current rates display (GBP, EUR, TRY, USD)
   - Last update timestamp
   - Form to manually update rates
   - Exchange rate change history

4. **User Synchronization**
   - Total user count
   - Info about shared user table
   - Status indicator

5. **Reservation Tracking**
   - Total reservations (all-time, pending, completed)
   - Breakdown by status
   - Link to detailed reservations view

6. **Synchronization Log**
   - Recent sync events (up to 50)
   - Status indicators (success/warning/error)
   - Detailed messages
   - Clear log button

### 3. Exchange Rates Page
**File:** `/admin/exchange-rates-page.php`
**Menu Location:** Settings > Exchange Rates

Dedicated interface for managing currency exchange rates:

- Update individual rates (EUR, TRY, USD relative to GBP)
- View last update timestamp
- See historical rate changes
- Input validation (rates must be positive numbers)

### 4. Integration with Existing Classes

#### Airlinel_API_Handler
**Modified methods:**
- `create_reservation()` - Now logs reservation creation to sync log with source_site info

#### Airlinel_Reservation_Handler
**Used by:** API handler when creating reservations
**Features:** Stores source_site, source_language, source_url metadata

#### Airlinel_Exchange_Rate_Manager
**Modified methods:**
- `set_rates()` - Now automatically updates sync timestamp

## WordPress Cron Jobs

Two automatic sync jobs are scheduled:

### 1. Hourly Vehicle Sync
- **Hook:** `airlinel_hourly_vehicle_sync`
- **Frequency:** Every hour
- **Action:** Syncs vehicle data and logs status
- **Managed by:** `Airlinel_Data_Sync_Manager::schedule_sync_jobs()`

### 2. Hourly Exchange Rate Sync
- **Hook:** `airlinel_hourly_exchange_rate_sync`
- **Frequency:** Every hour
- **Action:** Checks exchange rate sync status
- **Managed by:** `Airlinel_Data_Sync_Manager::schedule_sync_jobs()`

## AJAX Endpoints

Two AJAX endpoints are available for frontend/admin interaction:

### 1. Manual Vehicle Sync
```javascript
// Trigger manual sync
fetch(ajaxurl, {
    method: 'POST',
    body: new FormData(/* form with action=airlinel_sync_manual_vehicles */)
})
```

### 2. Get Sync Status
```javascript
// Get current sync status
fetch(ajaxurl, {
    method: 'POST',
    body: new FormData(/* form with action=airlinel_get_sync_status */)
})
```

## Data Storage

### Sync Timestamps
- **Option Name:** `airlinel_sync_timestamps`
- **Structure:** `array('vehicles' => timestamp, 'exchange_rates' => timestamp, 'users' => timestamp)`
- **Purpose:** Track when each data type was last synced

### Sync Log
- **Option Name:** `airlinel_sync_log`
- **Structure:** Array of sync events (up to 500 entries)
- **Entry Format:**
```php
array(
    'timestamp' => int (Unix timestamp),
    'data_type' => string (vehicles|exchange_rates|users|reservations),
    'status' => string (success|warning|error|info),
    'message' => string (details about sync)
)
```

## Sync Health Indicators

The `verify_sync_health()` method returns status for each data type:

```php
array(
    'overall' => 'healthy|warning|error',
    'vehicles' => array(
        'status' => 'healthy|error',
        'count' => int,
        'last_sync' => timestamp,
        'freshness' => 'fresh|stale|error'
    ),
    'exchange_rates' => array(
        'status' => 'healthy',
        'last_sync' => timestamp,
        'freshness' => 'fresh|stale'
    ),
    'users' => array(
        'status' => 'healthy',
        'count' => int,
        'last_sync' => timestamp,
        'freshness' => 'fresh|stale'
    ),
    'reservations' => array(
        'status' => 'healthy',
        'count' => int,
        'last_sync' => timestamp
    )
)
```

### Freshness Rules

- **Fresh:** Data synced less than 1 hour ago (vehicles) or 24 hours (exchange rates)
- **Stale:** Data not synced within freshness window
- **Error:** Data not available or invalid

## Regional Site Integration

### For Regional Sites

Regional sites use the Airlinel_Regional_Site_Proxy class (Task 3.1) to:

1. **Query vehicles from main site**
   - Makes API call to main site `/airlinel/v1/search` endpoint
   - Caches results locally
   - Automatically refreshes on TTL expiration

2. **Get pricing from main site**
   - Uses API proxy for real-time pricing
   - Zone pricing and km rates retrieved via API
   - No local storage needed

3. **Cache exchange rates locally**
   - Fetches rates from main site via API
   - Caches with 5-minute TTL
   - Falls back to default rates if API unavailable

4. **Forward booking requests to main site**
   - All reservations created on main site only
   - Regional sites POST requests to main site API
   - Tracks source_site for analytics

## Configuration

### For Main Site Setup

```php
// In wp-config.php - Already configured by Task 3.1
define('AIRLINEL_MAIN_SITE_URL', 'https://airlinel.com');
define('AIRLINEL_API_KEY', 'your-main-site-api-key');

// Sync scheduling happens automatically via after_setup_theme hook
// No additional configuration needed
```

### For Regional Site Setup

```php
// In wp-config.php - Already configured by Task 3.1
define('AIRLINEL_MAIN_SITE_URL', 'https://airlinel.com');
define('AIRLINEL_MAIN_SITE_API_KEY', 'main-site-api-key');
define('AIRLINEL_REGIONAL_SITE_ID', 'antalya'); // or 'istanbul', etc.
define('AIRLINEL_REGIONAL_API_KEY', 'regional-site-api-key');

// Data Sync Manager works the same on regional sites
// but sync operations are for caching only
```

## API Endpoints (Task 3.0 - Existing)

The following endpoints were added in Task 3.0 and are used by the sync system:

### Search Transfers
```
POST /wp-json/airlinel/v1/search
X-API-Key: {api_key}
Content-Type: application/json

{
    "pickup": "Airport",
    "dropoff": "City Center",
    "date": "2026-05-15",
    "passengers": 2,
    "currency": "GBP",
    "country": "UK"
}
```

### Create Reservation
```
POST /wp-json/airlinel/v1/reservation/create
X-API-Key: {api_key}
Content-Type: application/json

{
    "customer_name": "John Doe",
    "email": "john@example.com",
    "phone": "+44...",
    "pickup_location": "London Heathrow",
    "dropoff_location": "Central London",
    "transfer_date": "2026-05-15",
    "total_price": 65.00,
    "passengers": 2,
    "currency": "GBP",
    "country": "UK",
    "source_site": "antalya",
    "source_language": "en",
    "source_url": "https://antalya.airlinel.com/booking"
}
```

### Get Reservation
```
GET /wp-json/airlinel/v1/reservation/{id}
X-API-Key: {api_key}
```

## Troubleshooting

### Sync Status Shows "Error"
1. Check the sync log for detailed error messages
2. Verify vehicles exist in the vehicle database
3. Check WordPress error logs

### Exchange Rates Not Updating
1. Verify rates are valid positive numbers
2. Check user has manage_options capability
3. Review sync log for exchange_rates entries

### Cron Jobs Not Running
1. Verify WordPress cron is enabled: `define('DISABLE_WP_CRON', false);` in wp-config.php
2. Check that `wp_cron` is actually running (check logs)
3. For production, consider real cron: Set `define('DISABLE_WP_CRON', true);` and add server cron job:
   ```bash
   */5 * * * * curl https://airlinel.com/wp-cron.php?doing_wp_cron > /dev/null 2>&1
   ```

## Future Enhancements

1. **Live Exchange Rate API Integration**
   - Integrate with exchangerate-api.com or similar service
   - Auto-sync rates on schedule
   - Add rate alerts if change exceeds threshold

2. **Webhook Notifications**
   - Notify regional sites immediately on data changes
   - Real-time sync instead of polling

3. **Sync Metrics & Analytics**
   - Track sync performance
   - Alert on sync failures
   - Dashboard charts of sync health over time

4. **Database Replication**
   - Direct database sync for vehicles/pricing
   - Reduce API calls
   - Improved consistency checks

## Related Tasks

- **Task 3.0:** API Key Management & Rate Limiting
- **Task 3.1:** Regional Site Configuration & Proxy
- **Task 3.2:** Admin Settings Interface
- **Task 3.3:** Language & Localization System
- **Task 3.4:** Data Synchronization & Monitoring (This document)

## Support

For issues with the sync system:
1. Check the Sync Dashboard for error messages
2. Review sync log entries
3. Check WordPress error logs at `/wp-content/debug.log`
4. Verify network connectivity between sites
5. Confirm API keys are correctly configured
