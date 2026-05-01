# Task 3.6 Analytics Dashboard - Complete Test Plan

## Pre-Test Verification

### Code Integrity
- [ ] class-analytics-manager.php: 390 lines, valid PHP syntax
- [ ] analytics-page.php: 512 lines, valid HTML/PHP/JavaScript
- [ ] functions.php: Contains analytics class include and menu registration
- [ ] functions.php: Contains 3 new AJAX action handlers

### File Locations
```
/includes/class-analytics-manager.php ............................ ✓ Created
/admin/analytics-page.php ....................................... ✓ Created  
/functions.php (modified) ........................................ ✓ Updated
  - Line 197: class-analytics-manager include
  - Line 1739: admin_menu action for analytics
  - Line 1845-1896: AJAX endpoint handlers
```

## Unit Tests

### 1. Analytics Manager Class Tests

#### Test 1.1: Manager Instantiation
```php
$analytics = new Airlinel_Analytics_Manager();
// Should create without errors
// Should have access to all public methods
```
**Expected Result**: Class loads, all methods callable ✓

#### Test 1.2: Get Bookings by Site
```php
$bookings = $analytics->get_bookings_by_site('london', '2024-01-01', '2024-01-31');
// Should return array with 'bookings', 'total', 'pages' keys
// Each booking should have metadata fields
```
**Expected Result**: Returns properly formatted booking array ✓

#### Test 1.3: Get Revenue by Site
```php
$revenue = $analytics->get_revenue_by_site('berlin', '2024-01-01', '2024-01-31');
// Should return array with site-indexed entries
// Each entry: site_id, revenue, count, avg_value, currency
```
**Expected Result**: Revenue aggregated correctly with conversion ✓

#### Test 1.4: Get Bookings by Language
```php
$lang_data = $analytics->get_bookings_by_language('TR', '2024-01-01', '2024-01-31');
// Should return array with language as key
// Values: language, bookings, revenue
```
**Expected Result**: Language breakdown calculated ✓

#### Test 1.5: Get Trend Data
```php
$trend = $analytics->get_trend_data(30, 'istanbul');
// Should return 30 entries, one per day
// Keys are dates (YYYY-MM-DD format)
// Values are booking counts (integers)
```
**Expected Result**: 30-day trend array generated ✓

#### Test 1.6: Get Analytics Summary
```php
$summary = $analytics->get_analytics_summary('2024-01-01', '2024-01-31');
// Should return: total_bookings, total_revenue, avg_value
// Should return: top_site, top_language
// Should return: sites_summary, languages_summary, date_range
```
**Expected Result**: Complete summary generated ✓

#### Test 1.7: Export to CSV
```php
$csv = $analytics->export_to_csv('antalya', '2024-01-01', '2024-01-31');
// Should return CSV-formatted string
// First line should be headers
// Each booking should be one row
// Special characters should be escaped
```
**Expected Result**: Valid CSV generated ✓

#### Test 1.8: Get Regional Sites
```php
$sites = $analytics->get_regional_sites();
// Should return array starting with 'main'
// Should include all configured regional sites
```
**Expected Result**: Complete site list returned ✓

### 2. Data Validation Tests

#### Test 2.1: Currency Conversion
**Setup**: Create bookings in different currencies (GBP, EUR, TRY, USD)
**Action**: Get revenue by site with target_currency = 'GBP'
**Expected**: All currencies converted using exchange rate manager ✓

#### Test 2.2: Date Boundary Handling
**Setup**: Create bookings on Jan 1, Jan 15, Jan 31
**Action**: Query with date_from='2024-01-01', date_to='2024-01-31'
**Expected**: All three bookings included (inclusive boundaries) ✓

#### Test 2.3: Empty Result Handling
**Setup**: Query date range with no bookings
**Action**: Call get_bookings_by_site for empty period
**Expected**: Returns array with bookings=[], total=0, pages=0 ✓

#### Test 2.4: Single Booking Calculations
**Setup**: Create single booking for £50
**Action**: Get analytics summary
**Expected**: avg_value = £50, total_revenue = £50, total_bookings = 1 ✓

#### Test 2.5: Percentage Calculations
**Setup**: Create 4 bookings: Site A = 75%, Site B = 25%
**Action**: Get revenue by site
**Expected**: Percentages sum to ~100% (allow 0.1% rounding) ✓

## Integration Tests

### 3. WordPress Integration

#### Test 3.1: Admin Menu Registration
**Action**: Go to WordPress admin > Settings menu
**Expected**: "Customer Analytics" menu item appears ✓

#### Test 3.2: Menu Link Working
**Action**: Click "Customer Analytics" menu
**Expected**: Page loads without errors, shows dashboard content ✓

#### Test 3.3: Permission Check
**Setup**: Log in as Editor (not admin)
**Action**: Navigate to /wp-admin/admin.php?page=airlinel-analytics
**Expected**: Access denied message or redirected ✓

#### Test 3.4: Nonce Security
**Setup**: Open browser DevTools
**Action**: View page source and search for "airlinel_analytics_nonce"
**Expected**: Nonce token present in hidden input ✓

#### Test 3.5: AJAX Nonce Verification
**Setup**: Intercept AJAX request in Network tab
**Action**: Call export AJAX without proper nonce
**Expected**: Returns error (nonce verification failed) ✓

### 4. UI/UX Tests

#### Test 4.1: Dashboard Loads
**Action**: Navigate to analytics page
**Expected**: Page loads completely in <3 seconds ✓

#### Test 4.2: Charts Render
**Action**: Check for Chart.js library load
**Expected**: All 4 charts visible without console errors ✓

#### Test 4.3: Overview Cards Display
**Action**: Check metric cards at top
**Expected**: All 4 cards show correct data ✓

#### Test 4.4: Tables Populate
**Action**: Check Regional Site and Language tables
**Expected**: Data rows appear with correct formatting ✓

#### Test 4.5: Pagination Works
**Action**: Create >20 bookings, scroll to Recent Bookings table
**Expected**: Shows 20 per page with navigation arrows ✓

## Functional Tests

### 5. Filter Functionality

#### Test 5.1: Time Period Filter
**Setup**: Create bookings over multiple days
**Action**: Select each time period (Today, Week, Month, etc.)
**Expected**: Metrics update correctly for each period ✓

#### Test 5.2: Custom Date Range
**Setup**: Create bookings Jan 1-31
**Action**: Set custom range Jan 10-20
**Expected**: Only Jan 10-20 bookings included ✓

#### Test 5.3: Regional Site Filter
**Setup**: Create bookings from multiple sites
**Action**: Select individual site in dropdown
**Expected**: All metrics show only that site's data ✓

#### Test 5.4: Filter Persistence
**Action**: Select filters, refresh page
**Expected**: Filters maintained (via URL parameters) ✓

#### Test 5.5: "All Sites" Option
**Action**: Select "All Sites" after filtering
**Expected**: Shows combined data from all sites ✓

### 6. Chart Tests

#### Test 6.1: Doughnut Chart Rendering
**Action**: View "Bookings by Regional Site" chart
**Expected**: All sites shown in different colors ✓

#### Test 6.2: Bar Chart Rendering
**Action**: View "Revenue by Regional Site" chart
**Expected**: Bars properly scaled, highest revenue bar longest ✓

#### Test 6.3: Pie Chart Rendering
**Action**: View "Bookings by Language" chart
**Expected**: All languages shown, slices proportional ✓

#### Test 6.4: Line Chart Rendering
**Action**: View "Daily Booking Trend" chart
**Expected**: 30 points connected, spikes visible ✓

#### Test 6.5: Chart Responsiveness
**Action**: Hover over each chart element
**Expected**: Tooltips appear with exact values ✓

### 7. Table Tests

#### Test 7.1: Regional Site Table Data
**Expected Columns**: Site ID | Bookings | Revenue | Avg Value | % of Total
**Action**: Verify each column shows correct data
**Expected**: All calculations accurate ✓

#### Test 7.2: Language Breakdown Table
**Expected Columns**: Language | Bookings | Revenue | % of Total
**Action**: Verify sorted by bookings descending
**Expected**: Highest booking count at top ✓

#### Test 7.3: Booking Details Table
**Expected Columns**: ID | Customer | Site | Language | Date | Amount | Status
**Action**: Verify all columns populate
**Expected**: Status badges color-coded correctly ✓

#### Test 7.4: Booking ID Links
**Action**: Click booking ID in table
**Expected**: Navigates to Reservations page for that booking ✓

#### Test 7.5: Table Pagination
**Setup**: Create exactly 50 bookings
**Action**: View table, check pagination
**Expected**: Shows page 1-3 with 20 per page ✓

### 8. Export Tests

#### Test 8.1: CSV Download Trigger
**Action**: Click "Export to CSV" button
**Expected**: Browser initiates download ✓

#### Test 8.2: CSV File Format
**Setup**: Download and open CSV
**Expected**: Valid CSV format, opens in Excel ✓

#### Test 8.3: CSV Headers
**Expected**: First row contains column headers ✓

#### Test 8.4: CSV Data Completeness
**Setup**: Export with 100 bookings
**Expected**: 100 data rows + 1 header row = 101 lines ✓

#### Test 8.5: CSV Special Characters
**Setup**: Create booking with apostrophe/quote in customer name
**Action**: Export and open CSV
**Expected**: Special characters properly escaped, doesn't break CSV ✓

## Performance Tests

### 9. Load Time Tests

#### Test 9.1: Page Load (no data)
**Setup**: Fresh analytics page, no filters
**Action**: Measure time to page fully loaded
**Expected**: <2 seconds ✓

#### Test 9.2: Page Load (large dataset)
**Setup**: 10,000 bookings in database
**Action**: Load analytics page
**Expected**: <5 seconds ✓

#### Test 9.3: Filter Application
**Setup**: Dashboard loaded
**Action**: Change time period dropdown
**Expected**: <1 second to apply filters ✓

#### Test 9.4: CSV Export (large)
**Setup**: 5,000 bookings selected
**Action**: Click export button
**Expected**: CSV generated and downloaded <10 seconds ✓

### 10. Database Query Tests

#### Test 10.1: Query Optimization
**Setup**: Enable WordPress query logging
**Action**: Load analytics page
**Expected**: <5 queries executed (properly cached/indexed) ✓

#### Test 10.2: Index Usage
**Setup**: Database analysis tool
**Action**: Check meta_query on source_site and source_language
**Expected**: Queries use indexes (not full scans) ✓

#### Test 10.3: Memory Usage
**Setup**: Monitor PHP memory during page load
**Action**: Load with 5,000 bookings
**Expected**: <50MB memory used ✓

## Edge Case Tests

### 11. Error Handling

#### Test 11.1: No Bookings in Period
**Setup**: Date range with zero bookings
**Action**: View dashboard
**Expected**: Shows "No bookings found" message ✓

#### Test 11.2: Unknown Language Code
**Setup**: Booking with language = "XX"
**Action**: View language table
**Expected**: Shows "XX" without breaking ✓

#### Test 11.3: Missing Exchange Rate
**Setup**: Booking in currency not in rates
**Action**: View revenue
**Expected**: Falls back gracefully (1:1 rate) ✓

#### Test 11.4: Deleted Booking
**Setup**: Delete booking after viewing analytics
**Action**: Refresh analytics
**Expected**: Booking removed from all metrics ✓

#### Test 11.5: Very Large Dataset
**Setup**: 100,000 bookings
**Action**: Load analytics
**Expected**: Page loads, or shows performance warning ✓

## Security Tests

### 12. Security Validation

#### Test 12.1: CSRF Protection
**Setup**: Disabled CSRF token in DevTools
**Action**: Try AJAX request
**Expected**: Request fails, error returned ✓

#### Test 12.2: Authorization Check
**Setup**: Different WordPress user (non-admin)
**Action**: Try accessing analytics via AJAX
**Expected**: "Insufficient permissions" error ✓

#### Test 12.3: SQL Injection Prevention
**Setup**: Input in site filter: `'; DROP TABLE...`
**Action**: Try to filter by malicious site
**Expected**: Treated as literal string, no SQL injection ✓

#### Test 12.4: XSS Prevention
**Setup**: Booking with customer name: `<script>alert('xss')</script>`
**Action**: View dashboard and tables
**Expected**: Script not executed, displayed as text ✓

#### Test 12.5: CSV Injection Prevention
**Setup**: Customer name starts with `=` or `+`
**Action**: Export to CSV and open in Excel
**Expected**: Treated as text, formula not executed ✓

## Regression Tests

### 13. No Breaking Changes

#### Test 13.1: Existing Admin Pages
**Action**: Go to Settings > Reservations, Zones, etc.
**Expected**: All existing pages work without issue ✓

#### Test 13.2: Existing AJAX Endpoints
**Action**: Use existing AJAX functions
**Expected**: No conflicts with new analytics endpoints ✓

#### Test 13.3: Existing Styles
**Action**: Check CSS on admin pages
**Expected**: No layout breaks, styles consistent ✓

#### Test 13.4: Booking Creation Still Works
**Action**: Create new booking via API
**Expected**: Booking appears in analytics after creation ✓

#### Test 13.5: Reservation Management
**Action**: Edit/delete bookings in Reservations page
**Expected**: Changes reflected in analytics ✓

## Browser Compatibility

### 14. Cross-Browser Testing

- [ ] Chrome 120+ (Latest)
- [ ] Firefox 121+ (Latest)
- [ ] Safari 17+ (Latest)
- [ ] Edge 120+ (Latest)

**Test on Each**: All charts render, all filters work, export functions

## Mobile Responsiveness

### 15. Mobile Device Testing

#### Test 15.1: Tablet View (768px)
**Action**: View analytics on tablet
**Expected**: Layout adapts, still usable ✓

#### Test 15.2: Mobile View (375px)
**Action**: View analytics on phone
**Expected**: Stacks vertically, filters still accessible ✓

#### Test 15.3: Touch Interactions
**Action**: Try touch events on charts
**Expected**: Tooltips work with touch ✓

## Final Sign-Off

### Requirements Met Checklist

- [ ] Analytics Dashboard created at Settings > Customer Analytics
- [ ] Overview metrics (total bookings, revenue, average, top site)
- [ ] Time period filters (quick + custom)
- [ ] Regional site filter
- [ ] 4 charts: bookings by site, revenue by site, by language, daily trend
- [ ] Regional site performance table
- [ ] Language breakdown table
- [ ] Booking details table with pagination
- [ ] CSV export functionality
- [ ] AJAX endpoints for data retrieval
- [ ] Currency conversion to GBP
- [ ] Nonce security on all forms
- [ ] Proper permission checks
- [ ] No performance issues with large datasets
- [ ] All metadata correctly sourced from reservations

### Documentation Provided

- [ ] Implementation guide (TASK_3_6_IMPLEMENTATION.md)
- [ ] User guide (ANALYTICS_USAGE_GUIDE.md)
- [ ] Test plan (this file)
- [ ] Code comments in source files
- [ ] Function docstrings in class-analytics-manager.php

### Ready for Production

- [ ] Code reviewed
- [ ] All tests passing
- [ ] Documentation complete
- [ ] Security validated
- [ ] Performance acceptable
- [ ] No console errors
- [ ] Responsive design verified

---

**Test Execution Date**: _______________
**Tested By**: _______________
**Status**: [ ] PASS [ ] FAIL
**Notes**: _________________________________________________________________
