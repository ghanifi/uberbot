# Booking Analytics Funnel Troubleshooting Guide

## Fixed Issues

### 1. Database Table Creation
- **Issue**: `wp_airlinel_booking_searches` table might not exist
- **Fix**: Added automatic table creation check on `init` hook (runs daily via transient)
- **Status**: ✅ FIXED - Table will be created automatically on next page load

### 2. Tracker Script Loading
- **Issue**: Tracker script not loading on booking page if slug doesn't match expected names
- **Fix**: Updated script enqueue to check page template (`page-booking.php`) in addition to slug
- **Status**: ✅ FIXED - Script now loads reliably on any page using booking template

### 3. Exchange Rates API
- **Issue**: Duplicate AJAX handlers causing conflicts
- **Fix**: Consolidated handlers and added fallback defaults
- **Status**: ✅ FIXED - Currency selector now works with fallback rates

## Verification Checklist

### Step 1: Verify Table Exists
1. Access WordPress Admin > Tools > phpMyAdmin (or database client)
2. Look for table: `wp_airlinel_booking_searches`
3. Should have columns: id, stage, pickup, dropoff, vehicle_name, etc.

### Step 2: Test Booking Funnel
1. Go to booking page: `/book-your-ride/` or page with booking template
2. Verify currency selector shows in page header with exchange rates
3. Perform a complete booking flow:
   - **Stage 1**: Enter pickup/dropoff → search triggered → `stage='search'` record created
   - **Stage 2**: Select vehicle → `stage='vehicle_selected'` record updated
   - **Stage 3**: Fill customer form → `stage='form_filled'` record updated
   - **Stage 4**: Complete payment → `stage='payment_complete'` record updated

### Step 3: Verify Analytics Tracking
1. Open browser Console (F12) during booking
2. Should see logs like:
   - "Search tracked with record ID: 123"
   - "Vehicle selection tracked for record: 123"
   - "Customer form tracked for record: 123"
3. Check for any errors starting with "airlinel_tracker"

### Step 4: Check Analytics Dashboard
1. Go to WordPress Admin > Airlinel Dashboard > Customer Analytics & Source Tracking
2. Under "Booking Funnel Analytics" should show:
   - Total Searches: (count from database)
   - Vehicle Selected: (count)
   - Form Filled: (count)
   - Paid Bookings: (count)
   - Visual funnel chart with drop-off percentages

### Step 5: Debug Database
Run SQL query in phpMyAdmin:
```sql
SELECT stage, COUNT(*) as count FROM wp_airlinel_booking_searches 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
GROUP BY stage;
```

Expected output (if tracking works):
```
search          | 45
vehicle_selected| 32
form_filled     | 18
payment_complete| 15
```

## Debugging Currency Selector

### Expected Behavior
- Currency selector visible in page header (right side of booking summary bar)
- Shows: "Currency" label with dropdown (GBP, EUR, TRY, USD)
- Shows exchange rate: "1 GBP = X.XXXX [CURRENCY]"
- Dropdown is functional and updates displayed prices

### If Not Visible
1. Check page actually uses `page-booking.php` template
2. Verify CSS not hiding the selector:
   - Open DevTools (F12)
   - Inspect element with class "border-white/5" or "currency"
   - Check for display:none or visibility:hidden
3. Check JavaScript errors in Console tab
4. Verify the page template is assigned correctly

### Testing Exchange Rates AJAX
Open browser Console and run:
```javascript
fetch(chauffeur_data.ajax_url, {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: new URLSearchParams({action: 'get_exchange_rates'})
})
.then(r => r.json())
.then(r => console.log(r))
```

Should return:
```json
{
    "success": true,
    "data": {
        "GBP": 1.0,
        "EUR": 0.86,
        "TRY": 32.5,
        "USD": 1.27
    }
}
```

## Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| "Exchange rate manager not available" error | Class not properly loaded - check includes/class-exchange-rate-manager.php exists |
| Currency selector visible but exchange rates show "--" | AJAX not returning data - check network tab in DevTools |
| No data in analytics after testing | Check: 1) table exists, 2) tracker script loaded, 3) no console errors |
| Only "search" records appear, no other stages | Vehicle selection/form/payment tracking functions not called - check booking.js |

## Next Steps if Issues Persist

1. Check WordPress error log: `wp-content/debug.log`
2. Enable WP_DEBUG in wp-config.php to see more detailed errors
3. Verify all required classes exist:
   - `Airlinel_Booking_Analytics_Tracker`
   - `Airlinel_Exchange_Rate_Manager`
4. Check AJAX handlers are properly registered (no conflicts)
