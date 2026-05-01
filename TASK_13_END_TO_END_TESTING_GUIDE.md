# Task 13: End-to-End Analytics Flow Testing Guide

## Overview

This comprehensive testing guide validates that the complete booking analytics system works from initial search through booking completion, with all data properly captured in the analytics tables. This guide covers:

1. **Search Analytics Tracking** - Verify search data is captured correctly
2. **Form Initialization** - Verify booking forms are created and tracked
3. **Field Change Tracking** - Verify each field change is logged
4. **Form Stage Progression** - Verify form stages update correctly
5. **Admin Dashboard Display** - Verify admin pages display captured data with working filters

---

## Part 1: Prerequisites Checklist

### Environment Setup Verification

- [ ] **WordPress Environment Running**
  - [ ] WordPress installed and accessible at your local/staging domain
  - [ ] Database is working properly
  - [ ] All plugins are activated

- [ ] **Database Integrity**
  - [ ] Access WordPress admin at `/wp-admin`
  - [ ] Verify Airlinel menu appears in left sidebar
  - [ ] Click `Airlinel → Database Migrations`
  - [ ] **CRITICAL:** Verify all migrations show "Completed" status
  - [ ] No pending or failed migrations

- [ ] **Theme Configuration**
  - [ ] Airlinel theme is active
  - [ ] Theme's `functions.php` has enqueued:
    - `form-tracker.js` for front-end tracking
    - `booking.js` for booking flow
  - [ ] All CSS and JavaScript loaded without errors

- [ ] **External Dependencies**
  - [ ] Google Maps API key is configured (in wp_options or theme settings)
  - [ ] jQuery is enqueued (required by form-tracker.js)
  - [ ] No JavaScript errors in browser console on `/booking` page

- [ ] **Database Tables Exist**
  - [ ] Using phpMyAdmin or WordPress Database Tool:
    - [ ] `wp_booking_search_analytics` table exists with all columns
    - [ ] `wp_booking_form_analytics` table exists with all columns
    - [ ] `wp_booking_form_field_changes` table exists with all columns
  - [ ] Run SQL: `SHOW TABLES LIKE 'wp_booking%';`
  - [ ] Should return 3 tables

### Quick Database Check

Run this in phpMyAdmin to verify table structure:

```sql
-- Check search analytics table
DESCRIBE wp_booking_search_analytics;
-- Should show: id, stage, pickup, dropoff, distance_km, country, currency, 
-- vehicle_count, source, language, exchange_rate, site_url, ip_address, timestamp

-- Check form analytics table
DESCRIBE wp_booking_form_analytics;
-- Should show: id, search_id, pickup, dropoff, distance_km, country, language,
-- customer_name, customer_email, customer_phone, vehicle_id, vehicle_name, 
-- vehicle_price, pickup_date, pickup_time, flight_number, agency_code, notes,
-- form_stage, site_source, site_url, ip_address, created_at, updated_at

-- Check field changes table
DESCRIBE wp_booking_form_field_changes;
-- Should show: id, form_id, field_name, field_value, change_timestamp, user_session, ip_address
```

---

## Part 2: Test Case 1 - Search Analytics Tracking

### Objective
Verify that search data is captured correctly in the `wp_booking_search_analytics` table.

### Prerequisites
- [ ] WordPress home page accessible
- [ ] Booking search form available on homepage or regional site
- [ ] Google Maps API responding properly

### Step 1: Clear Previous Test Data (Optional)
```sql
-- CAUTION: This deletes all test data. Only run if needed.
-- DELETE FROM wp_booking_search_analytics WHERE DATE(timestamp) = CURDATE();
```

### Step 2: Execute a Search from Main Site

1. [ ] Open main site homepage (e.g., `https://airlinel.com` or `https://localhost/airlinel`)
2. [ ] Verify search form displays with fields:
   - [ ] Pickup Location (autocomplete text input)
   - [ ] Dropoff Location (autocomplete text input)
   - [ ] Search button
3. [ ] Fill in search form:
   - **Pickup:** "London Heathrow" (or "LHR")
   - **Dropoff:** "Central London" (or any London location)
4. [ ] Click "Search" button
5. [ ] **Expected Result:** Page redirects to `/booking/?pickup=London%20Heathrow&dropoff=Central%20London&distance=25.5&country=UK&...`
6. [ ] Note the `distance` value from URL (e.g., 25.5)
7. [ ] Observe trip summary showing:
   - [ ] Pickup and dropoff locations
   - [ ] Distance (in kilometers)
   - [ ] Estimated duration

### Step 3: Verify Search Data in Database

**Run this query in phpMyAdmin:**

```sql
SELECT * FROM wp_booking_search_analytics ORDER BY id DESC LIMIT 1;
```

**Verify the following fields:**

| Field | Expected Value | Notes |
|-------|-----------------|-------|
| `id` | Auto-incrementing ID | Should be a new record |
| `stage` | `'search'` | Initial stage |
| `pickup` | `'London Heathrow'` | Matches your input |
| `dropoff` | `'Central London'` | Matches your input |
| `distance_km` | Numeric value (e.g., 25.5) | Matches distance from URL |
| `country` | `'UK'` or country code | Based on location |
| `currency` | `'GBP'` or active currency | Should be set |
| `vehicle_count` | Numeric (count of matching vehicles) | Should be > 0 |
| `source` | `'main'` or `'regional_api'` | Indicates site source |
| `language` | Language code (e.g., `'en'`, `'fr'`) | Detected from page |
| `exchange_rate` | Numeric (e.g., 1.0, 1.15) | Exchange rate at time of search |
| `site_url` | Base URL of site | e.g., `'airlinel.com'` |
| `ip_address` | Your IP address | Your current IP |
| `timestamp` | Current time | Should be recent (within last few minutes) |

### Step 4: Test Search from Regional Site (Optional)

Repeat Steps 2-3 but from a regional site (if configured):

1. [ ] Open regional site (e.g., `https://london.airlinel.com`)
2. [ ] Perform same search with same parameters
3. [ ] Verify database record shows:
   - [ ] `source = 'regional_api'`
   - [ ] `site_url = 'london.airlinel.com'` (or regional domain)
   - [ ] Same distance, country, and locations

### Step 5: Test Multiple Searches

Perform 3-5 different searches with different locations:

```sql
-- Verify multiple records exist
SELECT COUNT(*) as total_searches FROM wp_booking_search_analytics WHERE DATE(timestamp) = CURDATE();

-- View all searches by country
SELECT country, COUNT(*) as count FROM wp_booking_search_analytics WHERE DATE(timestamp) = CURDATE() GROUP BY country;
```

---

## Part 3: Test Case 2 - Booking Form Initialization

### Objective
Verify that booking forms are created and tracked when user selects a vehicle.

### Prerequisites
- [ ] Search completed (from Test Case 1)
- [ ] Results page displays vehicle options
- [ ] Browser console is open (F12)

### Step 1: Observe Vehicle List

1. [ ] After search from Test Case 1, you should see `/booking` page with vehicle list
2. [ ] Verify vehicle list displays:
   - [ ] Multiple vehicle options
   - [ ] Vehicle name, image, capacity, price
   - [ ] "Select" or similar button for each vehicle
3. [ ] [ ] Check browser console - should be clean (no critical errors)

### Step 2: Select a Vehicle

1. [ ] Click "Select" button on any vehicle (e.g., first vehicle)
2. [ ] **Expected Result:**
   - [ ] Page remains on `/booking`
   - [ ] Vehicle list disappears
   - [ ] Booking form appears (Step 2 form)
   - [ ] Form shows selected vehicle details (name, price)
3. [ ] Browser console should show (if logging enabled):
   - Success messages from form-tracker.js initialization
   - No AJAX errors

### Step 3: Verify Form in Database

**Run this query:**

```sql
SELECT * FROM wp_booking_form_analytics ORDER BY id DESC LIMIT 1;
```

**Verify the following fields:**

| Field | Expected Value | Notes |
|-------|-----------------|-------|
| `id` | Auto-incrementing ID | New form record |
| `search_id` | NULL or ID | May reference search (if linked) |
| `pickup` | `'London Heathrow'` | From search |
| `dropoff` | `'Central London'` | From search |
| `distance_km` | 25.5 (example) | From search |
| `country` | `'UK'` | From search |
| `language` | Language code | Detected from page |
| `customer_name` | NULL initially | Not filled yet |
| `customer_email` | NULL initially | Not filled yet |
| `customer_phone` | NULL initially | Not filled yet |
| `vehicle_id` | Numeric ID | Selected vehicle ID |
| `vehicle_name` | Vehicle name (e.g., 'Premium Car') | Selected vehicle |
| `vehicle_price` | Price (e.g., '45.00') | Selected vehicle price |
| `pickup_date` | NULL initially | Not filled yet |
| `pickup_time` | NULL initially | Not filled yet |
| `flight_number` | NULL initially | Not filled yet |
| `agency_code` | NULL initially | Not filled yet |
| `notes` | NULL initially | Not filled yet |
| `form_stage` | `'vehicle_selection'` | Initial stage after selection |
| `site_source` | `'main'` or `'regional'` | Site source |
| `site_url` | Base URL | Current site URL |
| `ip_address` | Your IP | Your current IP |
| `created_at` | Current timestamp | Recent timestamp |
| `updated_at` | Current timestamp | Same as created_at initially |

### Step 4: Verify FormTracker Initialization

In browser console, run:

```javascript
// Check if FormTracker is active
console.log('FormTracker active:', window.AirinelFormTracker.isActive());

// Get current form ID
var formId = window.AirinelFormTracker.getFormId();
console.log('Form ID:', formId);

// Get detected language and site
console.log('Language:', window.AirinelFormTracker.detectLanguage());
console.log('Site Source:', window.AirinelFormTracker.detectSiteSource());
```

**Expected Output:**
- FormTracker active: `true`
- Form ID: Numeric value (should match database `id`)
- Language: Language code
- Site Source: `'main'` or `'regional'`

---

## Part 4: Test Case 3 - Field Change Tracking

### Objective
Verify that each field change is logged in the `wp_booking_form_field_changes` table.

### Prerequisites
- [ ] Form is displayed (from Test Case 2)
- [ ] Form ID is known from database
- [ ] Browser console is open

### Step 1: Fill in Customer Information

On the booking form, fill in the following fields:

1. **Full Name Field:**
   - [ ] Find input with `name="passenger_name"` (or similar)
   - [ ] Enter: `"John Smith"`
   - [ ] Move to next field (trigger blur event)

2. **Email Address Field:**
   - [ ] Find input with `name="passenger_email"`
   - [ ] Enter: `"john.smith@example.com"`
   - [ ] Move to next field

3. **Phone Number Field:**
   - [ ] Find input with `name="passenger_phone"`
   - [ ] Enter: `"+44 7911 123456"`
   - [ ] Move to next field

4. **Pickup Date Field:**
   - [ ] Find input with `name="pickup_date"`
   - [ ] Select a date 2-3 days from now
   - [ ] Move to next field

5. **Pickup Time Field:**
   - [ ] Find input with `name="pickup_time"`
   - [ ] Select a time (e.g., "14:30")
   - [ ] Move to next field

6. **Flight Number Field:** (if available)
   - [ ] Find input with `name="flight_number"`
   - [ ] Enter: `"BA123"`
   - [ ] Move to next field

7. **Agency Code Field:** (if available)
   - [ ] Find input with `name="agency_code"`
   - [ ] Enter: `"AGENCY001"` (or valid agency code)
   - [ ] Move to next field

8. **Notes Field:**
   - [ ] Find textarea with `name="notes"`
   - [ ] Enter: `"This is a test booking for analytics verification."`
   - [ ] Move to next field

### Step 2: Monitor Network Activity

1. [ ] Open browser Developer Tools → Network tab
2. [ ] Filter for XHR requests
3. [ ] As you fill each field, you should see AJAX calls to:
   - `wp-admin/admin-ajax.php` 
   - Action: `airlinel_ajax_log_field_change`
4. [ ] Verify each request:
   - [ ] Method: POST
   - [ ] Status: 200 OK
   - [ ] Response: JSON with success status

### Step 3: Verify Field Changes in Database

**Get the form ID from Test Case 2, then run this query:**

```sql
SELECT * FROM wp_booking_form_field_changes 
WHERE form_id = [INSERT_FORM_ID_HERE]
ORDER BY change_timestamp ASC;
```

**Example with form_id = 5:**
```sql
SELECT * FROM wp_booking_form_field_changes 
WHERE form_id = 5
ORDER BY change_timestamp ASC;
```

**Verify the following records exist:**

| Field Name | Expected Value | Notes |
|------------|-----------------|-------|
| `form_id` | Your form ID | References booking form |
| `field_name` | `'passenger_name'` | Matches form field |
| `field_value` | `'John Smith'` | Value you entered |
| `ip_address` | Your IP | Your current IP |
| `change_timestamp` | Recent timestamp | When you filled field |

**Expected Records (in order):**
1. `field_name='passenger_name'`, `field_value='John Smith'`
2. `field_name='passenger_email'`, `field_value='john.smith@example.com'`
3. `field_name='passenger_phone'`, `field_value='+44 7911 123456'`
4. `field_name='pickup_date'`, `field_value='2024-04-29'` (or your date)
5. `field_name='pickup_time'`, `field_value='14:30'` (or your time)
6. `field_name='flight_number'`, `field_value='BA123'` (if present)
7. `field_name='agency_code'`, `field_value='AGENCY001'` (if present)
8. `field_name='notes'`, `field_value='This is a test booking...'`

### Step 4: Count Field Changes

**Count total changes for your form:**

```sql
SELECT COUNT(*) as total_changes FROM wp_booking_form_field_changes 
WHERE form_id = 5;
```

**Expected:** At least 8 records (one for each field you filled)

### Step 5: Verify Chronological Order

```sql
SELECT id, field_name, field_value, change_timestamp 
FROM wp_booking_form_field_changes 
WHERE form_id = 5
ORDER BY change_timestamp ASC;
```

**Verify:**
- [ ] Timestamps are in chronological order
- [ ] Timestamps match approximately when you filled each field
- [ ] No timestamps are NULL

---

## Part 5: Test Case 4 - Form Stage Progression

### Objective
Verify that form stages update as the user progresses through the booking process.

### Prerequisites
- [ ] Booking form in progress (from previous test cases)
- [ ] Customer information partially filled
- [ ] Form ID known from database

### Step 1: Check Initial Stage

**Run this query to see the form's current stage:**

```sql
SELECT id, vehicle_name, form_stage, created_at, updated_at 
FROM wp_booking_form_analytics 
WHERE id = [YOUR_FORM_ID];
```

**Expected:** `form_stage = 'vehicle_selection'` (initial stage)

### Step 2: Trigger Customer Info Stage Update

The form stage should progress as you continue filling the form:

1. [ ] Continue filling customer information (if not already done)
2. [ ] After filling at least name, email, and phone, the form should progress
3. [ ] Check if form UI shows progress indication (if implemented)

### Step 3: Check Stage After Customer Info

**Run this query:**

```sql
SELECT id, customer_name, customer_email, form_stage, updated_at 
FROM wp_booking_form_analytics 
WHERE id = [YOUR_FORM_ID];
```

**Expected:** `form_stage` may be `'customer_info'` or `'booking_details'` depending on implementation

### Step 4: View Stage Update Timeline

```sql
SELECT id, form_stage, created_at, updated_at 
FROM wp_booking_form_analytics 
WHERE id = [YOUR_FORM_ID];
```

**Verify:**
- [ ] `created_at` = when form was initially created
- [ ] `updated_at` = when form was last modified (should be more recent)
- [ ] Time difference shows form progression

### Step 5: Monitor Stage Changes

If implementation supports this, observe multiple stages:

**Expected Stage Progression:**
1. `vehicle_selection` - When vehicle first selected
2. `customer_info` - When customer details filled
3. `booking_details` - When booking details complete
4. `completed` - When booking is finalized

---

## Part 6: Test Case 5 - Admin Dashboard Functionality

### Objective
Verify that admin pages display all captured analytics data with working filters.

### Prerequisites
- [ ] WordPress admin accessible at `/wp-admin`
- [ ] Have admin privileges
- [ ] At least one search and one booking form created (from Test Cases 1-4)

### Step 1: Navigate to Analytics Dashboard

1. [ ] Log into WordPress admin
2. [ ] In left sidebar, locate "Airlinel" menu item
3. [ ] Click "Airlinel" to expand submenu
4. [ ] Click "Analytics Dashboard"
5. **Expected:** Page at `/wp-admin/admin.php?page=airlinel-analytics` loads

### Step 2: Verify Summary Stats

**Verify the following stats cards display:**

- [ ] **Total Searches Card**
  - [ ] Shows "Total Searches" as heading
  - [ ] Displays numeric value (should be ≥ 1 from your test)
  - [ ] Large, bold number formatting

- [ ] **Completed Bookings Card**
  - [ ] Shows "Completed Bookings" as heading
  - [ ] Displays count of completed forms
  - [ ] Large, bold number formatting

- [ ] **Conversion Rate Card**
  - [ ] Shows "Conversion Rate" as heading
  - [ ] Displays percentage (e.g., "12.5%")
  - [ ] Calculated as: (completed / searches) × 100

**Verification Query:**
```sql
SELECT COUNT(*) as total_searches FROM wp_booking_search_analytics;
SELECT COUNT(*) as total_forms FROM wp_booking_form_analytics;
SELECT COUNT(*) as completed FROM wp_booking_form_analytics 
WHERE form_stage = 'completed';
```

### Step 3: Verify Booking Form Funnel Table

**On the dashboard, verify the "Booking Form Funnel" table:**

- [ ] Table has columns: Stage, Count, Percentage
- [ ] Shows rows for:
  - [ ] Vehicle Selection (count of forms at this stage)
  - [ ] Customer Info (count)
  - [ ] Booking Details (count)
  - [ ] Completed (count)
- [ ] Percentages sum to approximately 100%
- [ ] All counts are non-negative integers

**Verification Query:**
```sql
SELECT form_stage, COUNT(*) as count 
FROM wp_booking_form_analytics 
GROUP BY form_stage
ORDER BY form_stage;
```

### Step 4: Verify Searches by Country Table

**On the dashboard, verify the "Searches by Country" table:**

- [ ] Table displays with columns: Country, Count
- [ ] Shows your search's country (e.g., "UK")
- [ ] Count reflects number of searches from that country
- [ ] At least one row should show count ≥ 1

**Verification Query:**
```sql
SELECT country, COUNT(*) as count 
FROM wp_booking_search_analytics 
GROUP BY country;
```

### Step 5: Verify Searches by Source Table

**On the dashboard, verify the "Searches by Source" table:**

- [ ] Table displays with columns: Source, Count
- [ ] Shows either "Main Site" or "Regional Sites"
- [ ] Count reflects searches from that source
- [ ] Should show your search source

**Verification Query:**
```sql
SELECT source, COUNT(*) as count 
FROM wp_booking_search_analytics 
GROUP BY source;
```

### Step 6: Test Date Range Filter

1. [ ] On the dashboard, find the date filter section at the top
2. [ ] Find "From" and "To" date input fields
3. [ ] Default range should show: Last 30 days
4. [ ] [ ] Click "From" field and select a different start date
5. [ ] [ ] Click "To" field and select a different end date
6. [ ] [ ] Click "Filter" button
7. **Expected:**
   - [ ] Page reloads with new data
   - [ ] Summary stats update
   - [ ] Tables update to show only data in new date range
   - [ ] URL updates to include `date_from` and `date_to` parameters

### Step 7: Test Country Filter (if implemented)

If the dashboard has a country filter:

1. [ ] Locate country filter dropdown
2. [ ] Select a specific country
3. [ ] [ ] Click "Filter" button
4. **Expected:**
   - [ ] Data updates to show only that country
   - [ ] Searches by Country table shows only selected country
   - [ ] Counts decrease if filtered

### Step 8: Test Language Filter (if implemented)

If the dashboard has a language filter:

1. [ ] Locate language filter dropdown
2. [ ] Select a specific language (e.g., "English")
3. [ ] [ ] Click "Filter" button
4. **Expected:**
   - [ ] Data updates to show only that language
   - [ ] Counts may decrease if filtered

### Step 9: Test Source Filter (if implemented)

If the dashboard has a source filter:

1. [ ] Locate source filter dropdown
2. [ ] Select "Main Site" or "Regional Sites"
3. [ ] [ ] Click "Filter" button
4. **Expected:**
   - [ ] Data updates to show only selected source
   - [ ] Searches by Source table reflects filter

---

## Part 7: Test Case 6 - Search Analytics Page

### Objective
Verify the detailed search analytics page displays search records with working filters.

### Prerequisites
- [ ] Admin dashboard accessible
- [ ] At least one search exists in database

### Step 1: Navigate to Search Analytics Page

1. [ ] From Analytics Dashboard, click button: "View Detailed Search Analytics →"
2. Or navigate directly to: `/wp-admin/admin.php?page=airlinel-analytics-search`
3. **Expected:** Page loads with search records list

### Step 2: Verify Search List Display

**Verify the following columns display:**

- [ ] **Pickup** - Your search's pickup location (e.g., "London Heathrow")
- [ ] **Dropoff** - Your search's dropoff location (e.g., "Central London")
- [ ] **Distance** - Distance in kilometers (e.g., "25.5 km")
- [ ] **Country** - Country code (e.g., "UK")
- [ ] **Language** - Language code (e.g., "en")
- [ ] **Source** - "Main Site" or "Regional Sites"
- [ ] **Exchange Rate** - Numeric exchange rate value
- [ ] **Date** - Date of search (today's date for your test)

### Step 3: Verify Your Search Record

**Verify your test search displays in the list:**

- [ ] Find the row with your pickup and dropoff locations
- [ ] Click the row or "View" button if available
- [ ] **Expected:** Detailed view shows all search parameters
- [ ] All values match what you searched for

### Step 4: Test Date Range Filter

1. [ ] Find date filter inputs (From / To)
2. [ ] Select "Last 7 Days"
3. [ ] Click "Filter"
4. **Expected:**
   - [ ] Table updates showing searches from last 7 days
   - [ ] Your test search should still appear (if within range)

### Step 5: Test Country Filter

1. [ ] Find country filter dropdown
2. [ ] Select the country from your search (e.g., "UK")
3. [ ] Click "Filter"
4. **Expected:**
   - [ ] Table shows only searches from selected country
   - [ ] Your search is visible

### Step 6: Test Language Filter

1. [ ] Find language filter dropdown
2. [ ] Select the detected language (e.g., "English")
3. [ ] Click "Filter"
4. **Expected:**
   - [ ] Table shows only searches in selected language
   - [ ] Your search is visible

### Step 7: Test Source Filter

1. [ ] Find source filter dropdown
2. [ ] Select "Main Site" (if your search was from main site)
3. [ ] Click "Filter"
4. **Expected:**
   - [ ] Table shows only searches from selected source
   - [ ] Your search is visible

### Step 8: Test Multiple Filters Together

1. [ ] Apply date filter (Last 7 Days)
2. [ ] Apply country filter (UK)
3. [ ] Apply source filter (Main Site)
4. [ ] Click "Filter"
5. **Expected:**
   - [ ] All three filters are applied
   - [ ] Table shows only records matching ALL filters
   - [ ] Counts decrease appropriately

---

## Part 8: Test Case 7 - Form Analytics Page

### Objective
Verify the booking form analytics page displays form records with working filters and drill-down capability.

### Prerequisites
- [ ] Admin dashboard accessible
- [ ] At least one booking form exists in database
- [ ] Form ID is known

### Step 1: Navigate to Form Analytics Page

1. [ ] From Analytics Dashboard, look for "Form Analytics" link
2. Or navigate directly to: `/wp-admin/admin.php?page=airlinel-analytics-form`
3. **Expected:** Page loads with booking form records list

### Step 2: Verify Form List Display

**Verify the following columns display:**

- [ ] **Customer Name** - Your entered name (e.g., "John Smith")
- [ ] **Email** - Your entered email (e.g., "john.smith@example.com")
- [ ] **Vehicle** - Selected vehicle name
- [ ] **Price** - Vehicle price (e.g., "£45.00")
- [ ] **Stage** - Current form stage (e.g., "vehicle_selection", "customer_info")
- [ ] **Date** - Date form was created (today's date)
- [ ] **IP Address** - Your IP address

### Step 3: Verify Your Form Record

**Verify your test form displays in the list:**

- [ ] Find the row with your customer name
- [ ] Verify email matches what you entered
- [ ] Verify vehicle matches what you selected
- [ ] Verify current stage shows correct progression

### Step 4: Test Form Drill-Down

1. [ ] Click on your form record (click the row or "View" button)
2. **Expected:** Page navigates to field changes detail view
3. **Expected:** URL like: `/wp-admin/admin.php?page=airlinel-analytics-form&form_id=5`

### Step 5: Verify Field Changes Timeline

**On the form detail page, verify field changes display:**

1. [ ] Find the "Field Changes" or "Timeline" section
2. [ ] Verify records exist for each field you changed:
   - [ ] `passenger_name` - "John Smith"
   - [ ] `passenger_email` - "john.smith@example.com"
   - [ ] `passenger_phone` - "+44 7911 123456"
   - [ ] `pickup_date` - "2024-04-29"
   - [ ] `pickup_time` - "14:30"
   - [ ] Other fields (flight_number, agency_code, notes)

3. [ ] Verify each record shows:
   - [ ] Field name
   - [ ] Field value
   - [ ] Timestamp (when changed)
   - [ ] IP address

### Step 6: Verify Chronological Order

On the field changes timeline:

- [ ] Records should be ordered by timestamp (newest first or oldest first)
- [ ] Timestamps should be in correct sequence
- [ ] All timestamps should be within a few minutes of each other

### Step 7: Test Form Stage Filter (if applicable)

If form list has a stage filter:

1. [ ] Select a specific stage (e.g., "Vehicle Selection")
2. [ ] Click "Filter"
3. **Expected:**
   - [ ] Only forms at that stage display
   - [ ] Your form may or may not appear depending on its current stage

### Step 8: Test Date Range Filter on Form List

1. [ ] Select date range (e.g., "Last 7 Days")
2. [ ] Click "Filter"
3. **Expected:**
   - [ ] Forms from selected date range display
   - [ ] Your form should appear (if within range)

---

## Part 9: Test Case 8 - Analytics Data Integrity

### Objective
Verify all required fields are populated and data integrity checks pass.

### Prerequisites
- [ ] All previous test cases completed
- [ ] At least one search and one booking form exist
- [ ] Access to database queries

### Step 1: Verify Required Fields in Search Analytics

**Run this query:**

```sql
SELECT 
    COUNT(*) as total,
    COUNT(pickup) as pickup_count,
    COUNT(dropoff) as dropoff_count,
    COUNT(distance_km) as distance_count,
    COUNT(country) as country_count,
    COUNT(language) as language_count,
    COUNT(source) as source_count,
    COUNT(ip_address) as ip_count
FROM wp_booking_search_analytics
WHERE DATE(timestamp) = CURDATE();
```

**Verify:**
- [ ] All counts equal `total` (no NULL values in critical fields)
- [ ] All fields are populated

### Step 2: Verify Required Fields in Form Analytics

**Run this query:**

```sql
SELECT 
    COUNT(*) as total,
    COUNT(pickup) as pickup_count,
    COUNT(dropoff) as dropoff_count,
    COUNT(country) as country_count,
    COUNT(language) as language_count,
    COUNT(vehicle_id) as vehicle_id_count,
    COUNT(vehicle_name) as vehicle_name_count,
    COUNT(form_stage) as form_stage_count,
    COUNT(site_source) as site_source_count,
    COUNT(ip_address) as ip_address_count
FROM wp_booking_form_analytics
WHERE DATE(created_at) = CURDATE();
```

**Verify:**
- [ ] All counts are equal (no NULL values in critical fields)
- [ ] Customer fields (name, email, phone) may be NULL until filled

### Step 3: Verify Field Change Records Have Form References

**Run this query:**

```sql
SELECT 
    COUNT(*) as total_changes,
    COUNT(form_id) as with_form_id,
    COUNT(field_name) as with_field_name,
    COUNT(field_value) as with_field_value,
    COUNT(ip_address) as with_ip
FROM wp_booking_form_field_changes
WHERE DATE(change_timestamp) = CURDATE();
```

**Verify:**
- [ ] All counts equal total_changes
- [ ] Every field change has a form_id reference
- [ ] No NULL values in critical fields

### Step 4: Verify Foreign Key Relationships

**Run this query to verify field changes reference valid forms:**

```sql
SELECT 
    fc.form_id,
    COUNT(fc.id) as changes_count
FROM wp_booking_form_field_changes fc
LEFT JOIN wp_booking_form_analytics fa ON fc.form_id = fa.id
WHERE fa.id IS NULL
GROUP BY fc.form_id;
```

**Verify:**
- [ ] Query returns no results (all field changes reference valid forms)
- [ ] If results exist, there's a data integrity issue

### Step 5: Verify Timestamps Are Accurate

**Check timestamp accuracy:**

```sql
SELECT 
    MIN(timestamp) as earliest_search,
    MAX(timestamp) as latest_search,
    TIMESTAMPDIFF(MINUTE, MIN(timestamp), MAX(timestamp)) as minutes_span
FROM wp_booking_search_analytics
WHERE DATE(timestamp) = CURDATE();

SELECT 
    MIN(created_at) as earliest_form,
    MAX(updated_at) as latest_form,
    TIMESTAMPDIFF(MINUTE, MIN(created_at), MAX(updated_at)) as minutes_span
FROM wp_booking_form_analytics
WHERE DATE(created_at) = CURDATE();
```

**Verify:**
- [ ] Timestamps are recent (within last hour or day)
- [ ] Minutes span seems reasonable for test duration
- [ ] No future timestamps

### Step 6: Verify No Duplicate Records

**Check for duplicate searches:**

```sql
SELECT 
    pickup, dropoff, country, DATE(timestamp),
    COUNT(*) as count
FROM wp_booking_search_analytics
WHERE DATE(timestamp) = CURDATE()
GROUP BY pickup, dropoff, country, DATE(timestamp)
HAVING COUNT(*) > 1;
```

**Verify:**
- [ ] Query returns no results (no exact duplicates)
- [ ] If duplicates exist, investigate why multiple searches were logged for same parameters

### Step 7: Verify Exchange Rate Values Are Reasonable

**Check exchange rate values:**

```sql
SELECT 
    MIN(exchange_rate) as min_rate,
    MAX(exchange_rate) as max_rate,
    AVG(exchange_rate) as avg_rate,
    COUNT(*) as count
FROM wp_booking_search_analytics
WHERE exchange_rate > 0;
```

**Verify:**
- [ ] Exchange rates are between 0.5 and 3.0 (reasonable range)
- [ ] Rates match current market rates (e.g., GBP to EUR around 1.15)
- [ ] No extreme outliers

### Step 8: Verify IP Addresses Are Valid

**Check IP address formats:**

```sql
SELECT 
    ip_address,
    COUNT(*) as count
FROM wp_booking_search_analytics
WHERE ip_address IS NOT NULL AND ip_address != ''
GROUP BY ip_address;
```

**Verify:**
- [ ] IP addresses appear in valid format (e.g., 192.168.1.1 or ::1 for IPv6)
- [ ] No clearly invalid values

### Step 9: Generate Summary Statistics

**Get overall data summary:**

```sql
SELECT 
    'Search Analytics' as table_name,
    COUNT(*) as records,
    MIN(timestamp) as first_record,
    MAX(timestamp) as last_record
FROM wp_booking_search_analytics
UNION ALL
SELECT 
    'Form Analytics' as table_name,
    COUNT(*) as records,
    MIN(created_at) as first_record,
    MAX(updated_at) as last_record
FROM wp_booking_form_analytics
UNION ALL
SELECT 
    'Field Changes' as table_name,
    COUNT(*) as records,
    MIN(change_timestamp) as first_record,
    MAX(change_timestamp) as last_record
FROM wp_booking_form_field_changes;
```

**Document the results:**
- [ ] Total search records: ___
- [ ] Total form records: ___
- [ ] Total field change records: ___
- [ ] Date range of data: ___ to ___

---

## Part 10: Performance and Scalability Testing

### Objective
Verify system performance with test data and no SQL errors.

### Prerequisites
- [ ] All previous test cases completed
- [ ] Database has test data
- [ ] WordPress error logging enabled

### Step 1: Check WordPress Error Log

1. [ ] Navigate to WordPress `/wp-content/debug.log`
2. [ ] Review for any PHP errors or warnings
3. [ ] Verify no database connection errors
4. [ ] **Expected:** No new errors related to analytics

### Step 2: Check Database Performance

**Run performance test query:**

```sql
-- Test query response time
SELECT * FROM wp_booking_search_analytics 
ORDER BY id DESC 
LIMIT 100;
```

**Verify:**
- [ ] Query completes in < 100ms
- [ ] No slow query warnings
- [ ] Results display correctly

### Step 3: Verify Indexes Are Used

**Check index efficiency:**

```sql
EXPLAIN SELECT * FROM wp_booking_search_analytics 
WHERE country = 'UK' 
ORDER BY timestamp DESC;

EXPLAIN SELECT * FROM wp_booking_form_analytics 
WHERE form_stage = 'vehicle_selection' 
AND DATE(created_at) = CURDATE();
```

**Verify:**
- [ ] Queries use indexes (look for "Using index" in EXPLAIN output)
- [ ] Key column shows index being used
- [ ] Rows examined is reasonable

### Step 4: Check Admin Page Load Time

1. [ ] Open Analytics Dashboard in admin
2. [ ] Open browser Developer Tools → Performance tab
3. [ ] Reload page
4. [ ] **Verify:**
   - [ ] Page Load Time (DOMContentLoaded): < 2 seconds
   - [ ] Full Page Load: < 3 seconds
   - [ ] No JavaScript errors in console

### Step 5: Test Filter Performance

1. [ ] On Analytics Dashboard, apply multiple filters
2. [ ] Observe response time
3. [ ] **Verify:**
   - [ ] Filters apply within 1 second
   - [ ] Table updates without page reload (if AJAX)
   - [ ] No JavaScript errors

### Step 6: Verify No Memory Leaks in JavaScript

In browser console:

```javascript
// Check FormTracker memory usage
console.log('FormTracker object size:', Object.keys(window.AirinelFormTracker).length);

// Perform multiple operations
for (let i = 0; i < 100; i++) {
    window.AirinelFormTracker.detectLanguage();
    window.AirinelFormTracker.detectSiteSource();
}

console.log('FormTracker still responsive:', window.AirinelFormTracker.isActive());
```

**Verify:**
- [ ] No memory warnings
- [ ] FormTracker remains responsive

---

## Part 11: Troubleshooting Guide

### Issue: Analytics data not appearing

**Symptoms:** Tables appear empty, no records created

**Diagnosis Steps:**
1. [ ] Check if migrations were applied: Go to Airlinel → Database Migrations
2. [ ] Verify tables exist: Run `SHOW TABLES LIKE 'wp_booking%';`
3. [ ] Check if JavaScript errors exist: Open browser console
4. [ ] Verify AJAX endpoint: Check Network tab for `/wp-admin/admin-ajax.php` calls

**Solutions:**
1. [ ] Run migrations again if showing "Pending"
2. [ ] Check WordPress error log at `/wp-content/debug.log`
3. [ ] Enable debug mode: Add to `wp-config.php`:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```
4. [ ] Verify `chauffeur_data.ajax_url` exists in page source (right-click → View Page Source, search for "chauffeur_data")

### Issue: AJAX calls failing

**Symptoms:** Network tab shows 404 or 403 errors

**Diagnosis Steps:**
1. [ ] Check Network tab for AJAX request URL
2. [ ] Verify WordPress nonce is being sent
3. [ ] Check browser console for security warnings
4. [ ] Verify user is logged in (for admin pages)

**Solutions:**
1. [ ] Verify `wp-admin/admin-ajax.php` exists
2. [ ] Check nonce verification: `wp_verify_nonce()` in PHP handler
3. [ ] Clear browser cache and reload
4. [ ] Check WordPress security plugins for blocking AJAX

### Issue: Filters not working

**Symptoms:** Filters appear to apply but data doesn't change

**Diagnosis Steps:**
1. [ ] Check if date picker JavaScript is loaded
2. [ ] Verify database has filtered data
3. [ ] Check for JavaScript errors in console
4. [ ] Verify form submission is being processed

**Solutions:**
1. [ ] Manually test query in phpMyAdmin:
   ```sql
   SELECT * FROM wp_booking_search_analytics 
   WHERE DATE(timestamp) BETWEEN '2024-04-20' AND '2024-04-27';
   ```
2. [ ] Check filter form's action attribute
3. [ ] Look for form validation JavaScript

### Issue: Form ID not being set

**Symptoms:** Field changes not being logged, form appears incomplete

**Diagnosis Steps:**
1. [ ] Check browser console: `console.log(window.AirinelFormTracker.getFormId());`
2. [ ] Check AJAX response in Network tab for `startTracking` call
3. [ ] Verify vehicle was actually selected

**Solutions:**
1. [ ] Refresh page and retry vehicle selection
2. [ ] Check Network tab: Does `airlinel_ajax_log_form_start` return form_id?
3. [ ] Verify response JSON: `{"success": true, "form_id": 5}`

### Issue: Language not detected correctly

**Symptoms:** Language field shows wrong value or 'en' default always

**Diagnosis Steps:**
1. [ ] Check HTML source: Look for `<html lang="...">` or `data-language="..."`
2. [ ] Check window.locale value: `console.log(window.locale);`
3. [ ] Check browser language: Check browser settings

**Solutions:**
1. [ ] Set language explicitly in HTML: `<html lang="fr">`
2. [ ] Or set data attribute: `<html data-language="fr">`
3. [ ] Check WordPress locale setting: Go to WordPress Settings → General → Site Language

### Issue: IP address appears as localhost or 127.0.0.1

**Symptoms:** Testing locally shows localhost IP instead of real IP

**This is expected behavior for local testing.** In production, real IPs will appear.

**For testing purposes:**
- Local IP (127.0.0.1) is acceptable
- If using remote development server, you should see actual IP

### Issue: Exchange rate showing 0 or NULL

**Symptoms:** Exchange rate field is empty or zero

**Diagnosis Steps:**
1. [ ] Check if exchange rate manager is installed: `class Airlinel_Exchange_Rate_Manager`
2. [ ] Verify exchange rates are set in WordPress options
3. [ ] Check for currency settings in theme

**Solutions:**
1. [ ] Verify exchange rates option: 
   ```sql
   SELECT * FROM wp_options WHERE option_name LIKE '%exchange%';
   ```
2. [ ] Check theme settings for currency/exchange rate configuration

---

## Part 12: Success Criteria Checklist

Review this checklist after completing all test cases:

- [ ] **Database Migrations**
  - [ ] All migrations show "Completed" status
  - [ ] All 3 analytics tables exist with correct structure
  - [ ] No pending or failed migrations

- [ ] **Search Analytics**
  - [ ] Search data captured correctly with all fields populated
  - [ ] Multiple searches create multiple records
  - [ ] Data visible in database and admin page

- [ ] **Form Initialization**
  - [ ] Vehicle selection triggers form creation
  - [ ] Form record has correct initial stage
  - [ ] Form ID is generated and stored
  - [ ] FormTracker reports active status

- [ ] **Field Change Tracking**
  - [ ] Each field change creates a record in database
  - [ ] Field values match what was entered
  - [ ] Field change records reference correct form
  - [ ] Timestamps are accurate and in order

- [ ] **Form Stage Progression**
  - [ ] Form starts at `vehicle_selection` stage
  - [ ] Stages update as form progresses
  - [ ] `updated_at` timestamp changes with each update
  - [ ] Final stage shows as `completed` (or similar)

- [ ] **Admin Dashboard**
  - [ ] Dashboard loads without errors
  - [ ] Summary stats display correct values
  - [ ] Booking funnel table shows all stages
  - [ ] Country and source tables display correctly
  - [ ] All filters work and update data

- [ ] **Search Analytics Page**
  - [ ] Search records display with all columns
  - [ ] Filters (date, country, language, source) work correctly
  - [ ] Data matches database values
  - [ ] Pagination works if many records exist

- [ ] **Form Analytics Page**
  - [ ] Form records display with all columns
  - [ ] Drill-down shows field changes
  - [ ] Field changes timeline is chronological
  - [ ] Filters work correctly

- [ ] **Data Integrity**
  - [ ] No NULL values in critical fields
  - [ ] Foreign key relationships intact
  - [ ] No duplicate records
  - [ ] Exchange rates are reasonable
  - [ ] IP addresses are valid format

- [ ] **Performance**
  - [ ] Admin pages load within 3 seconds
  - [ ] Filters apply within 1 second
  - [ ] No database errors in log
  - [ ] No memory leaks in JavaScript
  - [ ] Indexes are being used efficiently

---

## Testing Conclusion

Once you've completed all test cases and verified all success criteria, the end-to-end analytics flow is working correctly. Document any issues found and create tickets for fixes if needed.

### Summary Reporting Template

**Test Summary:**
- Total Test Cases: 8
- Passed: ___
- Failed: ___
- Issues Found: ___

**Date Tested:** ___
**Tester Name:** ___
**WordPress Version:** ___
**PHP Version:** ___

**Issues/Notes:**
[Document any issues found, queries that returned unexpected results, or performance concerns]

**Recommendations for Production:**
[List any optimizations or improvements needed before going live]

---

## Appendix: Quick SQL Reference

### View all analytics tables
```sql
SELECT TABLE_NAME, TABLE_ROWS, DATA_LENGTH 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'wordpress' 
AND TABLE_NAME LIKE 'wp_booking%';
```

### Clear test data (USE WITH CAUTION)
```sql
-- Delete today's test data
DELETE FROM wp_booking_form_field_changes 
WHERE form_id IN (
    SELECT id FROM wp_booking_form_analytics 
    WHERE DATE(created_at) = CURDATE()
);

DELETE FROM wp_booking_form_analytics 
WHERE DATE(created_at) = CURDATE();

DELETE FROM wp_booking_search_analytics 
WHERE DATE(timestamp) = CURDATE();
```

### Generate test report
```sql
SELECT 
    'All Data Summary' as Report,
    (SELECT COUNT(*) FROM wp_booking_search_analytics) as Total_Searches,
    (SELECT COUNT(*) FROM wp_booking_form_analytics) as Total_Forms,
    (SELECT COUNT(*) FROM wp_booking_form_field_changes) as Total_Field_Changes,
    (SELECT COUNT(*) FROM wp_booking_form_analytics WHERE form_stage = 'completed') as Completed_Forms;
```

---

## Document Version

**Version:** 1.0  
**Created:** 2024-04-27  
**Task:** Task 13 - End-to-End Testing Guide  
**Status:** Complete

For questions or issues, refer to the Troubleshooting Guide (Part 11) or check the implementation documentation for Tasks 4, 5, and 11.
