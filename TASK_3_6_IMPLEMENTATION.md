# Task 3.6: Customer Source Tracking & Analytics Implementation

## Overview
Implemented comprehensive analytics dashboard for the Airlinel airport transfer platform main site. The dashboard tracks customer bookings by regional site origin, displays revenue metrics, and provides insights into language preferences and booking trends.

## Files Created

### 1. `/includes/class-analytics-manager.php`
Core analytics engine with the following methods:

#### Query Methods
- `get_bookings_by_site($site_id, $start_date, $end_date, $args)` 
  - Retrieves bookings filtered by source site, date range, and other criteria
  - Supports pagination with `per_page` and `paged` arguments
  - Returns array of booking objects with complete metadata

- `get_revenue_by_site($site_id, $start_date, $end_date, $target_currency)`
  - Aggregates revenue by regional site with automatic currency conversion to GBP
  - Returns site breakdown with count, total revenue, and average booking value
  - Handles multi-currency bookings

- `get_bookings_by_language($language, $start_date, $end_date)`
  - Groups bookings by source language across all 12 supported languages
  - Returns count and revenue totals by language
  - Supports filtering by specific language

- `get_trend_data($days, $site_id)`
  - Generates daily booking counts for the specified number of days
  - Returns array with dates as keys and booking counts as values
  - Used for line chart visualization

- `get_analytics_summary($start_date, $end_date)`
  - Comprehensive metrics summary including:
    - Total bookings and revenue
    - Average booking value
    - Top performing site and language
    - Breakdown of sites and languages with counts and revenue

#### Export Methods
- `export_to_csv($site_id, $start_date, $end_date)`
  - Exports detailed booking data to CSV format
  - Includes columns: Booking ID, Customer Name, Email, Site, Language, Transfer Date, Amount, Currency, Status
  - Properly escaped for special characters

#### Helper Methods
- `get_regional_sites()` - Returns list of all configured regional site IDs
- `_format_booking($post)` - Converts WP_Post object to analytics format
- `_convert_to_currency($amount, $from, $to)` - Currency conversion with exchange rates
- `_get_top_key($data)` - Finds highest performing item by count

### 2. `/admin/analytics-page.php`
Complete analytics dashboard UI with the following sections:

#### Overview Section
- Four metric cards showing:
  - Total Bookings (all time or filtered period)
  - Total Revenue (GBP with conversion)
  - Average Booking Value
  - Top Source Site

#### Filtering System
- Time period quick filters: Today, Last 7 Days, Last 30 Days, This Month, This Year
- Custom date range picker for specific periods
- Regional site filter to isolate metrics by source
- Apply filters button to refresh all data

#### Charts & Visualizations (using Chart.js 3.9.1)
- **Doughnut Chart**: Bookings distribution by regional site with percentages
- **Horizontal Bar Chart**: Revenue by regional site in GBP
- **Pie Chart**: Bookings distribution by language
- **Line Chart**: Daily booking trend over last 30 days with interactive tooltips

#### Regional Site Performance Table
- Columns: Site ID | Bookings | Revenue (GBP) | Avg Value | % of Total
- Shows all regional sites with complete metrics
- Sortable by any column

#### Language Breakdown Table
- Columns: Language | Bookings | Revenue (GBP) | % of Total
- All 12 supported languages displayed
- Sorted by booking count descending

#### Detailed Bookings Table
- Columns: Booking ID | Customer | Site | Language | Date | Amount | Status
- Pagination with 20 bookings per page
- Clickable booking IDs linking to reservations page
- Status badges with color coding (green=completed, yellow=pending, red=cancelled)
- CSV Export button

## Integration with WordPress

### Menu Registration
- Added to Settings submenu as "Customer Analytics"
- Accessible at: Settings > Customer Analytics
- Only visible to users with manage_options capability

### AJAX Endpoints Added to functions.php
Three new AJAX actions for data retrieval and export:

1. `wp_ajax_airlinel_export_analytics_csv`
   - Requires manage_options capability
   - Accepts: site_id, start_date, end_date
   - Returns: CSV-formatted data string

2. `wp_ajax_airlinel_get_analytics_summary`
   - Requires manage_options capability
   - Accepts: start_date, end_date
   - Returns: Complete analytics summary object

3. `wp_ajax_airlinel_get_bookings_by_site`
   - Requires manage_options capability
   - Accepts: site_id, start_date, end_date
   - Returns: Paginated array of booking objects

### Class Integration
- Added `require_once` for `class-analytics-manager.php` in functions.php
- Instantiates manager on analytics page load
- Uses existing Airlinel_Exchange_Rate_Manager for currency conversion

## Data Sources

### Reservation Metadata Used
The dashboard utilizes reservation metadata already tracked in Task 3.0:

- `source_site` - Regional site identifier (e.g., 'antalya', 'istanbul', 'berlin')
- `source_language` - ISO language code (e.g., 'EN', 'TR', 'DE', 'RU', etc.)
- `source_url` - Original booking URL on regional site
- `total_price` - Booking amount in original currency
- `currency` - Currency code (GBP, EUR, TRY, USD, etc.)
- `post_date` - Booking creation timestamp
- `post_status` - Booking status (pending, completed, cancelled, etc.)

### Customer Information Fields
- `customer_name` - For display in tables
- `email` - For export and contact purposes
- `transfer_date` - Service date for reference
- `pickup_location` / `dropoff_location` - Service details

## Key Features

### Currency Handling
- All revenue displayed in GBP with automatic conversion
- Uses Airlinel_Exchange_Rate_Manager for accurate rates
- Individual booking amounts can be in any supported currency
- Conversion applied on aggregation, not per-booking display

### Date Range Flexibility
- Supports any custom date range
- Quick filters for common periods
- Respects date boundaries (inclusive)
- Uses WordPress date format (Y-m-d)

### Performance Considerations
- Uses WordPress WP_Query for optimized database retrieval
- Meta queries with proper indexing on source_site, source_language
- Pagination for large result sets (20 per page in details table)
- Chart.js library loaded from CDN for minimal local load

### Security
- All AJAX endpoints require wp_verify_nonce() verification
- manage_options capability check on all admin pages
- Input sanitization with sanitize_text_field() and esc_*() functions
- No direct database access, uses WordPress APIs exclusively

## Testing Checklist

### 1. Data Retrieval Tests
- [ ] Analytics page loads without errors
- [ ] All metric cards display correct totals
- [ ] Date filters work and update all metrics
- [ ] Regional site filter shows only selected site data
- [ ] Pagination works with 20 items per page
- [ ] CSV export generates valid CSV file

### 2. Calculation Tests
- [ ] Total revenue correctly sums all bookings in period
- [ ] Average value = Total Revenue / Total Bookings
- [ ] Percentage calculations sum to 100% (or close with rounding)
- [ ] Currency conversion uses correct exchange rates
- [ ] Multi-currency bookings convert to GBP correctly

### 3. Chart Rendering Tests
- [ ] All four charts render without console errors
- [ ] Doughnut chart displays all sites with colors
- [ ] Bar chart shows revenue with proper scale
- [ ] Pie chart shows language distribution
- [ ] Line chart shows trend correctly

### 4. Filtering Tests
- [ ] "Today" filter shows only current day bookings
- [ ] "Last 7 Days" shows exactly last 7 days
- [ ] "Last 30 Days" shows exactly last 30 days
- [ ] "This Month" shows month-to-date
- [ ] "This Year" shows year-to-date
- [ ] Custom date range respects from/to dates
- [ ] All metrics update when filters change

### 5. Regional Site Tests
- [ ] All regional sites appear in dropdown
- [ ] Selecting a site filters all tables and charts
- [ ] "All Sites" option shows complete data
- [ ] Site filtering in CSV export works correctly

### 6. Language Breakdown Tests
- [ ] All 12 supported languages display if used
- [ ] Language counts match filtering criteria
- [ ] Language revenue calculated correctly
- [ ] Sorting by count works correctly

### 7. Booking Details Table Tests
- [ ] All columns display correct data
- [ ] Booking ID links to reservations page
- [ ] Status badges show correct colors
- [ ] Customer names display properly
- [ ] Pagination navigation works
- [ ] Table shows empty state message when no data

### 8. CSV Export Tests
- [ ] Export button triggers download
- [ ] CSV has correct headers
- [ ] All bookings in period included
- [ ] Special characters properly escaped
- [ ] Can open in Excel/Sheets without errors
- [ ] Export respects current filters

### 9. Performance Tests
- [ ] Page loads in < 2 seconds with 100+ bookings
- [ ] Filters apply without noticeable delay
- [ ] Charts render smoothly
- [ ] Export with 1000+ bookings completes in < 5 seconds

### 10. Edge Cases
- [ ] Empty periods show "No bookings" message
- [ ] Single booking displays metrics correctly
- [ ] All same-currency bookings don't break conversion
- [ ] Unknown language codes handled gracefully
- [ ] Invalid date ranges show no data
- [ ] Deleted bookings don't appear in analytics

## Database Queries

The analytics manager uses the following meta queries:

### For Site Filtering
```php
'meta_query' => array(
    array(
        'key' => 'source_site',
        'value' => $site_id,
        'compare' => '=',
    ),
)
```

### For Language Filtering
```php
'meta_query' => array(
    array(
        'key' => 'source_language',
        'value' => $language,
        'compare' => '=',
    ),
)
```

### Date Range Query
```php
'date_query' => array(
    array(
        'after' => array('year' => 2024, 'month' => 1, 'day' => 1),
        'before' => array('year' => 2024, 'month' => 12, 'day' => 31),
        'inclusive' => true,
    ),
)
```

## Regional Sites Supported
The following regional site IDs are expected (configured in wp-config.php):
- antalya
- istanbul
- berlin
- london
- paris
- (and any other configured sites in airlinel_regional_api_keys option)

## Language Support
All 12 languages defined in Task 3.3:
- EN (English)
- TR (Turkish)
- DE (German)
- RU (Russian)
- FR (French)
- IT (Italian)
- AR (Arabic)
- DA (Danish)
- NL (Dutch)
- SV (Swedish)
- ZH (Chinese)
- JA (Japanese)

## External Dependencies
- Chart.js 3.9.1 (loaded from CDN: https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js)
- jQuery (WordPress built-in)
- WordPress admin CSS and functions

## Future Enhancements
- Real-time analytics updates with WebSocket or polling
- Comparison between date ranges (YoY, MoM)
- Conversion funnel analysis
- Customer lifetime value calculations
- Automated email reports
- Custom dashboard widgets
- Advanced filtering with AND/OR logic
- Data export in multiple formats (Excel, JSON, PDF)

## Commit Message
```
feat: add customer source tracking and analytics dashboard

- Create Airlinel_Analytics_Manager class for analytics data retrieval
- Implement comprehensive analytics dashboard admin page
- Add revenue aggregation with currency conversion to GBP
- Create language and regional site breakdown tables
- Add daily trend charting with Chart.js
- Implement CSV export functionality
- Register AJAX endpoints for analytics data
- Add time period filtering (quick filters + custom date range)
- Support filtering by regional site
- Create detailed bookings table with pagination
```
