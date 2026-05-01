# Session Tracking Testing Guide
## Complete Testing for Session-Based Analytics Flow

**Document Version:** 1.0  
**Last Updated:** April 2026  
**Purpose:** Comprehensive testing guide for Migration 005 - Session tracking implementation

---

## Table of Contents

1. [Prerequisites Testing](#prerequisites-testing)
2. [Session Generation Testing](#session-generation-testing)
3. [Database Verification Test](#database-verification-test)
4. [Session Continuity Testing](#session-continuity-testing)
5. [Field Change Tracking Testing](#field-change-tracking-testing)
6. [Form Stage Progression Testing](#form-stage-progression-testing)
7. [Regional Site Testing](#regional-site-testing)
8. [Admin Dashboard Testing](#admin-dashboard-testing)
9. [Search Analytics Page Testing](#search-analytics-page-testing)
10. [Form Analytics Page Testing](#form-analytics-page-testing)
11. [Field Changes Timeline Testing](#field-changes-timeline-testing)
12. [Multi-Session Testing](#multi-session-testing)
13. [Database Integrity Testing](#database-integrity-testing)
14. [Performance Testing](#performance-testing)
15. [Troubleshooting](#troubleshooting)
16. [Success Criteria Checklist](#success-criteria-checklist)

---

## Prerequisites Testing

**Objective:** Verify that the database migration and frontend tracking are ready

### Step 1: Verify Migration 005 Applied

1. Log into WordPress admin dashboard
2. Navigate to **Airlinel → Database Migrations**
3. Look for Migration 005 in the list
4. Verify status shows: ✓ **Applied** or **Completed**
5. Check the migration description: "Add session tracking columns to analytics tables"

**Expected Result:** Migration 005 should be marked as applied

### Step 2: Verify All New Columns Exist

Open a terminal/command prompt with database access and run:

```sql
DESCRIBE wp_booking_search_analytics;
DESCRIBE wp_booking_form_analytics;
DESCRIBE wp_booking_form_field_changes;
```

**Verify these columns exist in each table:**

**wp_booking_search_analytics:**
- [ ] `session_id` (VARCHAR(36))
- [ ] `website_id` (VARCHAR(50))
- [ ] `website_language` (VARCHAR(10))

**wp_booking_form_analytics:**
- [ ] `session_id` (VARCHAR(36))
- [ ] `website_id` (VARCHAR(50))
- [ ] `website_language` (VARCHAR(10))

**wp_booking_form_field_changes:**
- [ ] `session_id` (VARCHAR(36))

### Step 3: Verify Indexes Were Created

Run in database:

```sql
SHOW INDEX FROM wp_booking_search_analytics;
SHOW INDEX FROM wp_booking_form_analytics;
SHOW INDEX FROM wp_booking_form_field_changes;
```

**Verify these indexes exist:**

**wp_booking_search_analytics:**
- [ ] `idx_session_id` index exists
- [ ] `idx_website_id` index exists

**wp_booking_form_analytics:**
- [ ] `idx_session_id` index exists

**wp_booking_form_field_changes:**
- [ ] `idx_session_id` index exists

### Step 4: Verify form-tracker.js is Loaded

1. Open Firefox/Chrome Developer Tools (F12)
2. Go to the **Sources** or **Network** tab
3. Navigate to your booking page
4. Search for `form-tracker.js` in the files list

**Verify:**
- [ ] File is listed under loaded JavaScript files
- [ ] File size > 0 KB (not empty)
- [ ] No 404 errors for form-tracker.js
- [ ] No console errors loading the file

---

## Session Generation Testing

**Objective:** Verify that sessions are created correctly when users interact with the site

### Step 1: Open Browser Developer Tools

1. Open Firefox or Chrome
2. Press `F12` to open Developer Tools
3. Go to **Console** tab
4. Keep console visible during testing

### Step 2: Navigate to Main Site Search Page

1. Visit your main site homepage (e.g., `https://airlinel.com/`)
2. Locate the booking search form
3. Keep Developer Tools console visible

### Step 3: Perform a Search

1. Enter **Pickup location** (e.g., "Heathrow")
2. Enter **Dropoff location** (e.g., "London City")
3. Select a future **date**
4. Enter **number of passengers** (e.g., 4)
5. Click **Search** button

### Step 4: Check Console for Session ID Logging

**In the browser console, you should see:**

```
Session ID: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
FormTracker initialized with session ID xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
```

**Verify:**
- [ ] Session ID appears in console (UUID format: 8-4-4-4-12 hex digits)
- [ ] Session ID is unique (copy it for later database checks)
- [ ] No JavaScript errors in console
- [ ] FormTracker initialization message appears

**Note:** Save/copy this Session ID for later database verification tests.

### Step 5: Verify Vehicle List Loads

1. Wait for search results to appear
2. Verify a list of vehicles displays with:
   - [ ] Vehicle images
   - [ ] Vehicle names
   - [ ] Passenger capacity
   - [ ] Price information
   - [ ] "Book Now" buttons

### Step 6: Verify Booking Form Initializes

1. From search results, click a vehicle's "Book Now" button
2. Verify a booking form appears with fields for:
   - [ ] Customer Name
   - [ ] Customer Email
   - [ ] Customer Phone
   - [ ] Pickup/Dropoff details (pre-filled from search)
   - [ ] Additional details (pickup time, flight number, etc.)

### Step 7: Verify Form Tracker is Active

**In the console, you should see messages indicating:**
- [ ] Form initialization logged
- [ ] Session ID being used for tracking
- [ ] No errors about missing FormTracker

---

## Database Verification Test

**Objective:** Confirm that search data is stored with session information

### Prerequisites

- You should have a Session ID from the [Session Generation Testing](#session-generation-testing) section
- This test must be run immediately after performing a search in [Session Generation Testing](#session-generation-testing)

### Step 1: Connect to Database

Open a database client (MySQL Workbench, phpMyAdmin, or command line):

```bash
mysql -u [username] -p [database_name]
```

### Step 2: Query Latest Search Record

**Run this query:**

```sql
SELECT * FROM wp_booking_search_analytics ORDER BY id DESC LIMIT 1;
```

### Step 3: Verify All Columns Are Populated

**Check these columns in the result:**

| Column | Expected Value | Check |
|--------|---|---|
| `session_id` | UUID format (xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx) | [ ] |
| `website_id` | 'main' (for main site) | [ ] |
| `website_language` | Site language code (e.g., 'en_US', 'de_DE') | [ ] |
| `pickup_location` | Your search pickup (e.g., "Heathrow") | [ ] |
| `dropoff_location` | Your search dropoff (e.g., "London City") | [ ] |
| `site_url` | Your main site URL | [ ] |
| `created_at` | Recent timestamp (within last 5 minutes) | [ ] |

**Verify:**
- [ ] session_id matches the UUID you saw in console
- [ ] website_id is 'main'
- [ ] website_language matches your site's language setting
- [ ] All search parameters are correctly stored
- [ ] created_at is recent

### Step 4: Test with Different Language

If your site supports multiple languages:

1. Switch to a different language (if available)
2. Perform another search
3. Run the query again
4. Verify `website_language` column shows the new language code

---

## Session Continuity Testing

**Objective:** Verify that the same session_id is used throughout a user's journey

### Prerequisites

- You must have a valid Session ID from previous testing

### Step 1: From Previous Search, Select a Vehicle

1. If you've closed the search results, perform another search (note the new Session ID)
2. From search results, click "Book Now" on any vehicle
3. Verify booking form appears

### Step 2: Query Form Analytics Table

**Run this query with your Session ID:**

```sql
SELECT * FROM wp_booking_form_analytics 
WHERE session_id = '[your_session_id_from_search]'
ORDER BY id DESC LIMIT 1;
```

**Replace `[your_session_id_from_search]` with the actual UUID**

### Step 3: Verify Session Continuity

**Check these columns:**

| Column | Expected Value | Check |
|--------|---|---|
| `session_id` | MUST match search session_id | [ ] |
| `website_id` | 'main' | [ ] |
| `website_language` | Must match search language | [ ] |
| `stage` | 'vehicle_selection' | [ ] |
| `created_at` | Within 1 minute of search | [ ] |

**Verify:**
- [ ] session_id in form record EXACTLY matches session_id from search
- [ ] website_id is consistent ('main')
- [ ] website_language is consistent
- [ ] stage shows correct booking stage
- [ ] Form record created shortly after search (within 1-2 minutes)

### Step 4: Verify No Duplicate Sessions

**Run this query:**

```sql
SELECT COUNT(DISTINCT session_id) as unique_sessions 
FROM wp_booking_form_analytics 
WHERE session_id = '[your_session_id]';
```

**Verify:**
- [ ] Result shows exactly 1 unique session
- [ ] No multiple session IDs for same booking flow

---

## Field Change Tracking Testing

**Objective:** Verify that individual form field changes are tracked with session information

### Prerequisites

- Active booking form from previous test
- Browser console still visible

### Step 1: Fill in Form Fields

Fill in the booking form one field at a time, waiting between fields:

1. **Customer Name:** Enter your full name (e.g., "John Smith")
2. **Customer Email:** Enter valid email (e.g., "john@example.com")
3. **Customer Phone:** Enter phone number (e.g., "+442071838750")
4. **Pickup Date:** Select a date (should be pre-filled, can modify)
5. **Pickup Time:** Select a time (e.g., "14:30")
6. **Flight Number:** Enter if visible (e.g., "BA1234")
7. **Agency Code:** Enter if available for testing (e.g., "AGENCY001")
8. **Notes:** Add any additional notes if field exists

**Note:** Wait 2-3 seconds between filling each field to ensure tracking captures the change.

### Step 2: Check Console for Field Change Messages

**In console, you should see messages like:**
```
Field changed: customer_name = John Smith
Field changed: customer_email = john@example.com
Field changed: customer_phone = +442071838750
```

**Verify:**
- [ ] Console shows field change messages
- [ ] Field names match form fields you filled
- [ ] Values are correct
- [ ] No console errors

### Step 3: Query Database for Field Changes

**Run this query with your Session ID:**

```sql
SELECT * FROM wp_booking_form_field_changes 
WHERE session_id = '[your_session_id]' 
ORDER BY created_at ASC;
```

### Step 4: Verify Field Change Records

**Check each returned record:**

For **each field** you filled in:

| Column | Expected Value | Check |
|--------|---|---|
| `session_id` | Must match your session_id | [ ] |
| `field_name` | Name of field (e.g., 'customer_name') | [ ] |
| `field_value` | Value you entered | [ ] |
| `created_at` | Timestamp when field was changed | [ ] |

**Verify:**
- [ ] Record exists for each field you filled
- [ ] All field values match exactly what you entered
- [ ] session_id in all records matches your session
- [ ] created_at timestamps are in chronological order (oldest first)
- [ ] Time difference between field changes is 2-5 seconds (as you filled them)

### Step 5: Verify Chronological Order

**Check the created_at timestamps:**

```sql
SELECT field_name, field_value, created_at FROM wp_booking_form_field_changes 
WHERE session_id = '[your_session_id]' 
ORDER BY created_at ASC;
```

**Verify:**
- [ ] Rows are in order of when you filled them
- [ ] No timestamps out of order
- [ ] Time gaps between records are realistic (2-30 seconds per field)

---

## Form Stage Progression Testing

**Objective:** Verify that form stage changes are tracked correctly

### Prerequisites

- Booking form from previous test still open
- Several field changes recorded from [Field Change Tracking Testing](#field-change-tracking-testing)

### Step 1: Check Initial Stage (Vehicle Selection)

**Run this query after you've filled customer info:**

```sql
SELECT id, stage, created_at FROM wp_booking_form_analytics 
WHERE session_id = '[your_session_id]' 
ORDER BY id ASC;
```

### Step 2: Observe Stage Values

**As you filled the form, stages should progress like this:**

1. **Initial:** stage = `'vehicle_selection'` (when form loads)
2. **After customer info:** stage = `'customer_info'` (after name/email filled)
3. **After booking details:** stage = `'booking_details'` (after date/time filled)

### Step 3: Fill More Fields and Re-Query

Continue filling the form with additional information:

1. Special requests / Notes
2. Any discount codes
3. Additional preferences

Then re-run the stage query:

```sql
SELECT id, stage, created_at FROM wp_booking_form_analytics 
WHERE session_id = '[your_session_id]' 
ORDER BY id DESC LIMIT 1;
```

### Step 4: Verify Stage Progression

**Expected behavior:**

- [ ] Stage starts as `'vehicle_selection'`
- [ ] Stage changes to `'customer_info'` after customer data filled
- [ ] Stage changes to `'booking_details'` after booking details added
- [ ] Stage never goes backwards
- [ ] Stage values are consistent across queries

### Step 5: Check Stage Timestamps

**Verify that stage changes correlate with field fills:**

```sql
SELECT 
    (SELECT stage FROM wp_booking_form_analytics WHERE session_id = '[your_session_id]' ORDER BY id ASC LIMIT 1) as initial_stage,
    (SELECT MIN(created_at) FROM wp_booking_form_field_changes WHERE session_id = '[your_session_id]' AND field_name IN ('customer_name', 'customer_email')) as customer_info_time,
    (SELECT MIN(created_at) FROM wp_booking_form_field_changes WHERE session_id = '[your_session_id]' AND field_name IN ('pickup_date', 'pickup_time')) as booking_details_time;
```

**Verify:**
- [ ] Initial stage timestamp before any field changes
- [ ] Customer info stage timestamp coincides with customer field changes
- [ ] Booking details stage timestamp coincides with date/time field changes

---

## Regional Site Testing

**Objective:** Verify session tracking works correctly on regional sites

### Prerequisites

- At least one regional site configured and accessible
- Access to regional site URLs (e.g., regional-tr.example.com, regional-uk.example.com)

### Step 1: Access Regional Site

1. Navigate to a regional site (e.g., `https://regional-tr.example.com/`)
2. Verify homepage loads correctly
3. Locate booking search form

### Step 2: Perform Search on Regional Site

1. Enter **Pickup location**
2. Enter **Dropoff location**
3. Select **date** and **passengers**
4. Click **Search**

### Step 3: Check Session ID in Console

**Browser console should show:**
```
Session ID: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
```

**Important:** Note this Session ID - it should be DIFFERENT from main site sessions

- [ ] Session ID logged in console
- [ ] Session ID format is valid UUID
- [ ] Session ID is different from previous main site session

### Step 4: Query Database for Regional Site Search

**Run this query:**

```sql
SELECT * FROM wp_booking_search_analytics 
WHERE website_id = 'regional-tr' 
ORDER BY id DESC LIMIT 1;
```

**Adjust 'regional-tr' to match your actual regional site ID**

### Step 5: Verify Regional Site Data

**Check these columns:**

| Column | Expected Value | Check |
|--------|---|---|
| `session_id` | Should be the UUID you just saw in console | [ ] |
| `website_id` | Should match regional site ID (e.g., 'regional-tr') | [ ] |
| `website_language` | Should match regional site's language | [ ] |
| `site_url` | Should show regional site's URL | [ ] |

**Verify:**
- [ ] website_id correctly identifies regional site
- [ ] website_language shows the regional site's configured language
- [ ] site_url points to regional site domain
- [ ] session_id is unique and different from main site sessions

### Step 6: Verify Regional Site Booking Form

1. From regional site search results, click "Book Now"
2. Verify booking form appears

**Query database:**

```sql
SELECT * FROM wp_booking_form_analytics 
WHERE session_id = '[regional_session_id]' 
ORDER BY id DESC LIMIT 1;
```

### Step 7: Verify Session Continuity on Regional Site

**Check:**

| Column | Expected Value | Check |
|--------|---|---|
| `session_id` | MUST match regional search session_id | [ ] |
| `website_id` | Must match regional site ID | [ ] |
| `website_language` | Must match regional site language | [ ] |

**Verify:**
- [ ] Form record uses same session_id as search
- [ ] website_id is consistent for the regional site
- [ ] website_language is consistent throughout regional site flow

### Step 8: Test Multiple Regional Sites (if available)

Repeat steps 1-7 for each regional site:
- [ ] Regional site A (e.g., regional-de.example.com)
- [ ] Regional site B (e.g., regional-it.example.com)
- [ ] Verify each site has:
  - [ ] Unique session IDs
  - [ ] Correct website_id values
  - [ ] Correct website_language values

---

## Admin Dashboard Testing

**Objective:** Verify analytics dashboard displays session data correctly

### Step 1: Log Into WordPress Admin

1. Navigate to WordPress admin (`/wp-admin/`)
2. Log in with admin account
3. Verify you're logged in

### Step 2: Navigate to Analytics Dashboard

1. In left sidebar, find **Airlinel** menu
2. Click **Analytics Dashboard** (or similar name)
3. Wait for page to load completely

**Verify:**
- [ ] Page loads without errors
- [ ] No 404 errors in browser console
- [ ] All widgets display

### Step 3: Verify New Metrics Displayed

**Look for these new metrics/cards:**

1. **"Total Sessions" Card**
   - [ ] Card is visible
   - [ ] Shows a number (count of all sessions)
   - [ ] Number > 0 (from your previous tests)
   
2. **"Sessions Completed" Card**
   - [ ] Card is visible
   - [ ] Shows a number
   - [ ] Number <= Total Sessions

3. **"Average Session Duration" Card** (if implemented)
   - [ ] Card visible
   - [ ] Shows time value

### Step 4: Test Website Filter

1. Look for **Website** filter/dropdown
2. Click to open filter options
3. Select **"Main"** from the list

**Verify:**
- [ ] Dashboard updates after selection
- [ ] Data shown only includes main site data
- [ ] Metrics decrease (showing fewer sessions)
- [ ] No errors

### Step 5: Test Language Filter

1. Look for **Language** filter/dropdown
2. Click to open filter options
3. Select a language (e.g., "English", "en_US")

**Verify:**
- [ ] Dashboard updates
- [ ] Data filters to selected language only
- [ ] Metrics change appropriately

### Step 6: Test Combined Filters

1. Select **Website:** "Main" AND **Language:** "en_US"
2. Apply filters (if required)

**Verify:**
- [ ] Dashboard uses AND logic (both conditions must match)
- [ ] Data shows only sessions meeting both conditions
- [ ] Metrics reflect filtered data

### Step 7: Verify Filter Accuracy

**Test by querying database:**

```sql
SELECT COUNT(*) as session_count 
FROM wp_booking_search_analytics 
WHERE website_id = 'main' AND website_language = 'en_US';
```

**Compare:**
- [ ] Database count matches dashboard "Total Sessions" card when Main + en_US filtered
- [ ] Numbers align within 1-2 records (allowing for timing variations)

---

## Search Analytics Page Testing

**Objective:** Verify session tracking columns and functionality on Search Analytics page

### Step 1: Navigate to Search Analytics

1. In WordPress admin, go to **Airlinel → Search Analytics**
2. Wait for page to load
3. Verify table displays

### Step 2: Verify New Columns Visible

**Check that these columns are displayed in the table:**

- [ ] **Session ID** column - should show first 8 characters of UUID (clickable link)
- [ ] **Website ID** column - shows 'main' or regional site ID
- [ ] **Website Language** column - shows language code (e.g., 'en_US')
- [ ] Original columns still present:
  - [ ] Pickup Location
  - [ ] Dropoff Location
  - [ ] Created Date
  - [ ] User IP

### Step 3: Test Session ID Link

1. Find a session ID in the table
2. Click on the session ID (should be a hyperlink)

**Verify:**
- [ ] Page filters/navigates to show only that session's data
- [ ] Table now shows only records with that session_id
- [ ] A "Clear Filter" or back button appears

### Step 4: Test Website Filter

1. Click to reset any session ID filters first
2. Look for **Website Filter** dropdown
3. Select "Main" from the filter

**Verify:**
- [ ] Table updates to show only main site searches
- [ ] website_id column shows only "main"
- [ ] Record count decreases

### Step 5: Test Language Filter

1. Look for **Language Filter** dropdown
2. Select a specific language (e.g., "German", "de_DE")

**Verify:**
- [ ] Table updates to show only selected language
- [ ] website_language column shows selected language
- [ ] Record count changes appropriately

### Step 6: Verify Drill-Down Navigation

1. Look for a search record from a regional site (website_id = 'regional-*')
2. Click its session ID link

**Verify:**
- [ ] Page filters to show only that session
- [ ] Only records with that session_id appear
- [ ] Other searches from other sessions disappear
- [ ] A "Back" or "Clear Filter" option is available

### Step 7: Verify Session ID Uniqueness in Filtered View

1. Apply session ID filter to a specific session
2. Count visible rows

**Verify:**
- [ ] All visible rows have the SAME session_id
- [ ] No mixing of different sessions
- [ ] Each session has only 1 search record (typically)

---

## Form Analytics Page Testing

**Objective:** Verify form records show session tracking and allow navigation

### Step 1: Navigate to Form Analytics

1. In WordPress admin, go to **Airlinel → Booking Forms** (or Form Analytics)
2. Wait for table to load
3. Verify data displays

### Step 2: Verify New Columns Visible

**Check these columns in table:**

- [ ] **Session ID** column - shows first 8 chars (clickable)
- [ ] **Website ID** column - shows 'main' or regional ID
- [ ] **Website Language** column - shows language code
- [ ] **Form Stage** column - shows current stage (vehicle_selection, customer_info, etc.)
- [ ] Original columns:
  - [ ] Created Date
  - [ ] Customer Name (if available)
  - [ ] Status

### Step 3: Test Session Navigation from Form Analytics

1. Find a form record with a session_id
2. Click the session ID link

**Verify:**
- [ ] Takes you to Search Analytics page
- [ ] Search Analytics page is filtered to show that session
- [ ] You can see the search record that started this session
- [ ] You can see the form record created after that search

### Step 4: Test Website Filter

1. Make sure no session filters are active
2. Click **Website** filter dropdown
3. Select a regional site (if available)

**Verify:**
- [ ] Table updates to show only selected website
- [ ] website_id column matches selected filter
- [ ] Form records from other sites disappear

### Step 5: Test Language Filter

1. Click **Language** filter dropdown
2. Select a language

**Verify:**
- [ ] Table updates to show only selected language
- [ ] website_language column matches filter
- [ ] Records from other languages disappear

### Step 6: Verify Form Stage Display

**Check the Form Stage column:**

- [ ] Shows values like: 'vehicle_selection', 'customer_info', 'booking_details'
- [ ] Different records show different stages
- [ ] Stages are logical and in expected progression

### Step 7: Test Multi-Filter Scenario

1. Select Website = "Main"
2. Select Language = "en_US"
3. Observe table

**Verify:**
- [ ] Table shows only records matching BOTH conditions (AND logic)
- [ ] Records where (website_id = 'main' AND website_language = 'en_US') are visible
- [ ] All others are hidden

---

## Field Changes Timeline Testing

**Objective:** Verify field changes are displayed in correct order with session tracking

### Step 1: Navigate to a Form Record

1. Go to **Airlinel → Booking Forms**
2. Click on a form record that has field changes

### Step 2: Verify Field Changes Page Loads

**Check for:**
- [ ] Page shows individual field change records
- [ ] Table displays columns:
  - [ ] Field Name
  - [ ] Field Value
  - [ ] Created At (timestamp)
  - [ ] Session ID

### Step 3: Verify Session ID Present

1. Look at the session_id column or a header showing session info
2. Verify it displays the UUID

**Verify:**
- [ ] session_id is visible (e.g., in page header or table)
- [ ] session_id matches the form's session_id
- [ ] Appears in UUID format

### Step 4: Verify Chronological Order

1. Look at the "Created At" timestamps
2. Read from top to bottom

**Verify:**
- [ ] First field change has earliest timestamp
- [ ] Last field change has latest timestamp
- [ ] Time differences are logical (seconds between changes)
- [ ] No timestamps out of order

### Step 5: Verify Field Values

1. Check that field_value column shows actual data you entered

**For example:**
- [ ] customer_name field shows the name you entered
- [ ] customer_email field shows the email you entered
- [ ] Values are not truncated or corrupted

### Step 6: Test Back Navigation

1. Look for a "Back" button or link
2. Click it

**Verify:**
- [ ] Returns to Form Analytics page
- [ ] Form record is still visible in table
- [ ] Can navigate back and forth

---

## Multi-Session Testing

**Objective:** Verify system correctly handles and isolates multiple sessions

### Prerequisites

- Admin access to WordPress
- Ability to perform multiple searches

### Step 1: Perform 5+ Searches with Variations

Perform searches from different scenarios to create multiple sessions:

**Search 1: Main Site, English**
- Site: Main site (airlinel.com)
- Language: English
- From/To: London → Heathrow
- Note Session ID (Session-A)

**Search 2: Main Site, English, Different Route**
- Site: Main site
- Language: English
- From/To: Manchester → Birmingham
- Note Session ID (Session-B)

**Search 3: Regional Site, Turkish**
- Site: Regional-TR (if available)
- Language: Turkish
- From/To: Istanbul → Ankara
- Note Session ID (Session-C)

**Search 4: Regional Site, German**
- Site: Regional-DE (if available)
- Language: German
- From/To: Berlin → Munich
- Note Session ID (Session-D)

**Search 5: Main Site, French**
- Site: Main site
- Language: French
- From/To: Paris → Lyon
- Note Session ID (Session-E)

### Step 2: Query Database for All Sessions

```sql
SELECT DISTINCT 
    session_id, 
    website_id, 
    website_language, 
    pickup_location, 
    dropoff_location 
FROM wp_booking_search_analytics 
WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
ORDER BY created_at DESC
LIMIT 10;
```

### Step 3: Verify Session Uniqueness

**Check:**
- [ ] Each row has a unique session_id
- [ ] All session_ids are in UUID format
- [ ] No duplicate session_ids in results

### Step 4: Verify Website ID Distribution

```sql
SELECT website_id, COUNT(*) as count FROM wp_booking_search_analytics 
WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY website_id;
```

**Verify:**
- [ ] Shows breakdown of searches by website
- [ ] Counts match your expectations
- [ ] Main site has expected number of searches
- [ ] Regional sites show their searches separately

### Step 5: Verify Language Distribution

```sql
SELECT website_language, COUNT(*) as count FROM wp_booking_search_analytics 
WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY website_language;
```

**Verify:**
- [ ] Shows count of searches per language
- [ ] Languages shown match what you used
- [ ] Counts are reasonable

### Step 6: Verify Admin Dashboard Updates

1. Go to Analytics Dashboard
2. Check "Total Sessions" metric

**Verify:**
- [ ] Total Sessions count >= number of searches you performed
- [ ] Number increased after each search
- [ ] No sessions are missing

### Step 7: Verify Filter Accuracy

1. In Analytics Dashboard, filter by a regional site
2. Check metrics

**Verify:**
- [ ] Total Sessions shows only that site's sessions
- [ ] Number equals count from database query by website_id
- [ ] Main site filter shows different count

### Step 8: Complete a Booking Flow in One Session

1. Pick one of your search sessions
2. From its results, click "Book Now"
3. Fill in customer info
4. Fill in additional details

**Query:**
```sql
SELECT COUNT(*) as same_session_forms FROM wp_booking_form_analytics 
WHERE session_id = '[search_session_id]';
```

**Verify:**
- [ ] Returns at least 1 (the form is linked to the search)
- [ ] Only 1 form per search session (typically)
- [ ] session_id in form matches search session_id

---

## Database Integrity Testing

**Objective:** Verify data quality and consistency at the database level

### Step 1: Check for Null Session IDs

**Query:**

```sql
SELECT COUNT(*) as null_sessions 
FROM wp_booking_search_analytics 
WHERE session_id IS NULL 
AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR);
```

**Verify:**
- [ ] Result is 0 (no null session_ids)
- [ ] All recent searches have session_id values
- [ ] No data without session tracking

### Step 2: Check Session ID Format

**Query:**

```sql
SELECT COUNT(*) as invalid_format 
FROM wp_booking_search_analytics 
WHERE session_id IS NOT NULL 
AND session_id NOT REGEXP '^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$'
AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR);
```

**Verify:**
- [ ] Result is 0 (all session_ids are valid UUIDs)
- [ ] No malformed session_id values
- [ ] All follow UUID v4 format

### Step 3: Check Website ID Distribution

**Query:**

```sql
SELECT website_id, COUNT(*) as count 
FROM wp_booking_search_analytics 
WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY website_id 
ORDER BY count DESC;
```

**Sample output:**
```
website_id       count
main             45
regional-tr      23
regional-de      18
regional-uk      12
```

**Verify:**
- [ ] website_id values are reasonable (main, regional-*, etc.)
- [ ] No unexpected/garbage values
- [ ] Distribution makes sense

### Step 4: Check Language Codes

**Query:**

```sql
SELECT DISTINCT website_language FROM wp_booking_search_analytics 
WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR);
```

**Verify:**
- [ ] Shows valid language codes (en_US, de_DE, tr_TR, etc.)
- [ ] No garbage values
- [ ] Matches your site's configured languages

### Step 5: Verify Index Performance

**Query to check index usage:**

```sql
EXPLAIN SELECT * FROM wp_booking_search_analytics 
WHERE session_id = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx' 
AND website_id = 'main';
```

**Look for in the output:**
- [ ] Using index (check "Extra" column)
- [ ] Key used shows an index name
- [ ] Rows examined is small (< 10)

**If "Using index" not shown:**
- Performance issue - indexes may not be working
- Run: `ANALYZE TABLE wp_booking_search_analytics;`

### Step 6: Check for Data Integrity Issues

**Query for orphaned records:**

```sql
SELECT COUNT(*) as orphaned_forms 
FROM wp_booking_form_analytics 
WHERE session_id IS NOT NULL 
AND session_id NOT IN (
    SELECT DISTINCT session_id FROM wp_booking_search_analytics
);
```

**Verify:**
- [ ] Result is 0 or very small number
- [ ] Most forms link to existing searches
- [ ] No significant data orphaning

### Step 7: Verify Timestamp Consistency

**Query:**

```sql
SELECT 
    s.session_id,
    s.created_at as search_time,
    f.created_at as form_time,
    TIMESTAMPDIFF(SECOND, s.created_at, f.created_at) as seconds_later
FROM wp_booking_search_analytics s
LEFT JOIN wp_booking_form_analytics f ON s.session_id = f.session_id
WHERE s.created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
LIMIT 10;
```

**Verify:**
- [ ] search_time is before form_time (or equal)
- [ ] seconds_later is positive (form comes after search)
- [ ] seconds_later is reasonable (5 seconds to a few minutes)
- [ ] No forms created before their searches

---

## Performance Testing

**Objective:** Verify that session tracking doesn't impact performance

### Step 1: Measure Dashboard Load Time

**Using Browser DevTools (F12):**

1. Go to **Airlinel → Analytics Dashboard**
2. Open DevTools > Network tab
3. Refresh page (Ctrl+R or Cmd+R)
4. Wait for page to fully load
5. Check "DOMContentLoaded" and "Load" times

**Expected results:**
- [ ] DOMContentLoaded: < 2 seconds
- [ ] Page Load: < 3 seconds
- [ ] Dashboard fully interactive in < 2 seconds

### Step 2: Measure Search Analytics Load Time

1. Go to **Airlinel → Search Analytics**
2. Open DevTools > Network tab
3. Refresh page
4. Check load times

**Expected:**
- [ ] DOMContentLoaded: < 1 second
- [ ] Page Load: < 2 seconds

### Step 3: Measure Filter Performance

1. Open **Search Analytics** page
2. Open DevTools
3. Click Website filter dropdown

**Expected:**
- [ ] Dropdown appears instantly (< 500ms)
- [ ] No lag

4. Select a website

**Expected:**
- [ ] Table updates in < 1 second
- [ ] No spinning loaders or delays > 1s

### Step 4: Check Index Effectiveness

**Query with index hint:**

```sql
SELECT * FROM wp_booking_search_analytics 
WHERE session_id = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';
```

Run `EXPLAIN` on this query:

```sql
EXPLAIN SELECT * FROM wp_booking_search_analytics 
WHERE session_id = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';
```

**Check output - "Extra" column should show:**
- [ ] "Using index" (if available in index)
- [ ] NOT "Using filescan" (would be slow)

**rows examined should be:**
- [ ] < 10 (good)
- [ ] Not thousands (would be slow)

### Step 5: Query Multiple Sessions Simultaneously

**Simulate concurrent requests:**

```bash
# Run this multiple times in parallel (or use Apache Bench)
ab -n 100 -c 10 'https://yourdomain.com/wp-admin/admin.php?page=airlinel-analytics'
```

**Or use curl:**

```bash
for i in {1..20}; do
  curl -s 'https://yourdomain.com/wp-admin/admin.php?page=airlinel-analytics' &
done
wait
```

**Verify:**
- [ ] No timeouts
- [ ] No 500 errors
- [ ] Response time remains < 2 seconds
- [ ] Database doesn't become unresponsive

### Step 6: Check with Large Dataset

**If you have 1000+ records:**

```sql
SELECT 
    COUNT(*) as total_records,
    MAX(id) as latest_id,
    MIN(created_at) as oldest,
    MAX(created_at) as newest
FROM wp_booking_search_analytics;
```

**Then query with filters:**

```sql
SELECT * FROM wp_booking_search_analytics 
WHERE website_id = 'main' 
AND website_language = 'en_US'
AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY);
```

**Verify:**
- [ ] Query completes in < 1 second
- [ ] Result set is reasonably sized
- [ ] Uses appropriate indexes (check EXPLAIN)

### Step 7: Monitor Server Resources

**During heavy testing, monitor:**

1. **CPU Usage:** Should not spike above 50%
2. **Memory Usage:** Should be stable
3. **Disk I/O:** Should not show sustained high activity
4. **MySQL Connections:** Should not max out

**Command to check MySQL status:**

```sql
SHOW PROCESSLIST;
SHOW STATUS LIKE 'Threads%';
```

**Verify:**
- [ ] No stuck/sleeping queries
- [ ] Queries complete quickly
- [ ] Thread count reasonable

---

## Troubleshooting

### Session ID is NULL in Database

**Symptom:** Running database queries shows NULL for session_id

**Possible Causes:**

1. **form-tracker.js not loaded**
   - Check: Open browser DevTools > Network tab, search for "form-tracker.js"
   - Fix: Verify file exists, check wp_enqueue_script in PHP
   - Debug: Check console for load errors

2. **Session ID not being generated**
   - Check: Console should show "Session ID: [UUID]" message
   - Fix: Verify FormTracker.init() is called
   - Debug: Add console.log to booking.js

3. **Session ID not sent to backend**
   - Check: Network tab > XHR/Fetch requests, look for session_id parameter
   - Fix: Verify AJAX request includes session_id
   - Debug: Edit form-tracker.js to add logging:
     ```javascript
     console.log('Sending session_id:', self.sessionId);
     ```

**Verification query:**

```sql
SELECT COUNT(*) as null_count FROM wp_booking_search_analytics 
WHERE session_id IS NULL;
SELECT COUNT(*) as total_count FROM wp_booking_search_analytics;
```

If null_count > 0, investigate above causes.

### Website ID is Incorrect

**Symptom:** Records show wrong website_id (e.g., 'main' when should be 'regional-tr')

**Possible Causes:**

1. **detectWebsiteInfo() not working in booking.js**
   - Debug: Add to booking.js:
     ```javascript
     var websiteInfo = detectWebsiteInfo();
     console.log('Detected website:', websiteInfo);
     ```
   - Fix: Update detectWebsiteInfo() function

2. **get_website_id() returning wrong value in PHP**
   - Debug: Add to your PHP handler:
     ```php
     error_log('get_website_id returned: ' . $website_id);
     ```
   - Check: Verify airlinel_website_id is set correctly
   - Check: Verify airlinel_regional_site_id option if regional

3. **Site detection logic based on domain**
   - Verify: Domain mapping is correct
   - Check: WordPress network configuration
   - Check: Regional site subdomain setup

**Verification:**

```sql
SELECT DISTINCT website_id FROM wp_booking_search_analytics;
```

Should show: 'main', and/or regional IDs (regional-tr, regional-de, etc.)

### Website Language is Wrong

**Symptom:** website_language shows wrong language code

**Possible Causes:**

1. **WordPress site language not configured correctly**
   - Check: WordPress Admin > Settings > General > Site Language
   - Fix: Set correct language
   - Verify: Value should match language pack installed

2. **get_website_language() not getting correct value**
   - Debug: Add logging in class files:
     ```php
     error_log('Website language: ' . get_website_language());
     ```
   - Check: Verify get_locale() returns correct value

3. **Language not persisted across requests**
   - Check: Language cookie/session
   - Verify: Language parameter passed in URL

**Verification:**

```sql
SELECT DISTINCT website_language FROM wp_booking_search_analytics;
```

Should show language codes (en_US, de_DE, tr_TR, etc.) matching your site's languages.

### Filters Don't Work

**Symptom:** Admin page filters don't update the displayed data

**Possible Causes:**

1. **SQL WHERE clauses missing or incorrect**
   - Check: class-analytics-dashboard.php
   - Verify: $wpdb->prepare() is used (SQL injection protection)
   - Debug: Enable query logging:
     ```php
     define('SAVEQUERIES', true);
     // Then in template:
     foreach ($wpdb->queries as $query) {
         error_log($query[0]);
     }
     ```

2. **JavaScript filter not posting correctly**
   - Check: Browser DevTools > Network tab > XHR requests
   - Verify: Request includes filter parameters
   - Debug: Check AJAX handler receives parameters

3. **Option/setting values not sanitized**
   - Check: Filter values are properly sanitized
   - Verify: No unexpected characters in filter values
   - Fix: Add sanitization:
     ```php
     $website_id = sanitize_text_field($_POST['website_id']);
     ```

**Test filters directly with query:**

```sql
SELECT * FROM wp_booking_search_analytics 
WHERE website_id = 'main' LIMIT 5;

SELECT * FROM wp_booking_search_analytics 
WHERE website_language = 'en_US' LIMIT 5;
```

If queries return results but dashboard doesn't, issue is in filtering code.

### Session ID Link Doesn't Work

**Symptom:** Clicking session ID in analytics table doesn't filter/navigate

**Possible Causes:**

1. **Link href not set correctly**
   - Check: Inspect element (F12) on session ID link
   - Verify: href attribute has correct URL with session parameter
   - Example: `href="?page=airlinel-search-analytics&session_id=xxxx"`

2. **Filter parameter not being read**
   - Check: PHP $_GET or $_POST for session_id parameter
   - Debug: Add logging:
     ```php
     if (isset($_GET['session_id'])) {
         error_log('Filter by session: ' . $_GET['session_id']);
     }
     ```

3. **WHERE clause not applied**
   - Verify: Query includes `WHERE session_id = ...`
   - Check: Parameter is properly escaped

**Test fix:**

1. Manually visit URL with session filter:
   ```
   https://yourdomain.com/wp-admin/admin.php?page=airlinel-search-analytics&session_id=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
   ```

2. Verify table filters to show only that session

### Multiple Session IDs for One Search

**Symptom:** Same search has multiple different session_id values in database

**Causes:**

1. **Session ID regenerated between search and form**
   - The session ID should be persistent across form submission
   - Check: FormTracker.sessionId is stored in window global
   - Verify: booking.js uses same sessionId throughout

2. **Session ID reset on page navigation**
   - Session ID should survive page refresh during booking flow
   - Check: Session ID stored in localStorage or similar
   - Current implementation: Stored in window.airinelSessionId

**Verification:**

```sql
SELECT session_id, COUNT(*) as count FROM wp_booking_search_analytics 
GROUP BY session_id 
HAVING count > 1 LIMIT 10;
```

Should return 0 results (each session_id unique per search)

**Fix:** Ensure session ID persists using:
- localStorage: `localStorage.setItem('airinelSessionId', sessionId)`
- Or ensure same page for search->form flow

### Performance Degradation

**Symptom:** Dashboard becomes slow, queries take > 2 seconds

**Causes:**

1. **Indexes not being used**
   - Verify indexes exist (see Database Integrity Testing, Step 5)
   - Rebuild indexes:
     ```sql
     OPTIMIZE TABLE wp_booking_search_analytics;
     OPTIMIZE TABLE wp_booking_form_analytics;
     OPTIMIZE TABLE wp_booking_form_field_changes;
     ```

2. **Large table without proper WHERE clause**
   - Check: Queries filter by date range (not querying all rows)
   - Fix: Add `WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)`

3. **Inefficient queries in admin**
   - Check: EXPLAIN plans for slow queries
   - Fix: Add indexes or rewrite query
   - Monitor: Enable slow query log

**Enable slow query logging:**

```sql
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;
```

Then check log for slow queries and optimize.

---

## Success Criteria Checklist

### Database Schema

- [ ] Migration 005 applied successfully in WordPress admin
- [ ] session_id column exists in all three analytics tables
- [ ] website_id column exists in all three analytics tables
- [ ] website_language column exists in all three analytics tables
- [ ] Indexes (idx_session_id, idx_website_id) created on search table
- [ ] Indexes (idx_session_id) created on form tables
- [ ] No migration errors in error log

### Session Generation

- [ ] Session IDs are generated in UUID v4 format (xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx)
- [ ] Each search generates a NEW unique session_id
- [ ] Each regional site search generates DIFFERENT session_id from main site
- [ ] Session ID logged to browser console on page load
- [ ] Session ID visible in FormTracker initialization message

### Data Accuracy

- [ ] Booking forms use same session_id as their initiating search
- [ ] All field changes within a session have same session_id
- [ ] website_id correctly identifies main vs regional sites
  - [ ] Main site searches show website_id = 'main'
  - [ ] Regional searches show website_id = 'regional-tr' (etc.)
- [ ] website_language reflects site configuration
  - [ ] Matches WordPress Site Language setting
  - [ ] Consistent throughout user's journey

### Admin Dashboard

- [ ] Analytics Dashboard loads successfully
- [ ] New metrics display:
  - [ ] "Total Sessions" card shows session count
  - [ ] "Sessions Completed" card shows completed sessions
- [ ] Dashboard filters work correctly:
  - [ ] Website filter updates data
  - [ ] Language filter updates data
  - [ ] Combination filters use AND logic
- [ ] Metrics accuracy verified against database queries

### Analytics Pages

- [ ] Search Analytics page displays new columns:
  - [ ] Session ID (clickable link)
  - [ ] Website ID
  - [ ] Website Language
- [ ] Form Analytics page displays new columns:
  - [ ] Session ID (clickable link)
  - [ ] Website ID
  - [ ] Website Language
  - [ ] Form Stage
- [ ] Field Changes page shows:
  - [ ] session_id field
  - [ ] Chronologically ordered field changes
  - [ ] Correct field names and values

### Navigation & Functionality

- [ ] Clicking session ID in Search Analytics works correctly
- [ ] Clicking session ID in Form Analytics navigates to Search Analytics filtered by session
- [ ] Back navigation returns to previous page
- [ ] All filters persist when navigating between pages
- [ ] Drill-down works (click session → see related records)

### Multi-Site & Multi-Language

- [ ] Each regional site has unique session_ids
- [ ] Regional site data is properly segregated
- [ ] Language switching creates appropriate language codes
- [ ] Multi-site data doesn't bleed between sites
- [ ] Dashboard correctly shows data by region and language

### Database Quality

- [ ] No NULL session_ids in recent data (< 1 hour)
  - Query: `SELECT COUNT(*) FROM wp_booking_search_analytics WHERE session_id IS NULL AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)` → Result: 0
- [ ] All session_ids are valid UUID format
- [ ] No duplicate session_ids per user journey
- [ ] website_id values are reasonable (main, regional-*, no garbage)
- [ ] website_language values are valid codes (en_US, de_DE, etc.)

### Performance

- [ ] Analytics Dashboard loads in < 2 seconds
- [ ] Search Analytics with filters loads in < 1 second
- [ ] Database queries use indexes (EXPLAIN shows "Using index")
- [ ] No performance degradation with 100+ records
- [ ] Queries execute in < 1 second with appropriate indexes
- [ ] No SQL errors in error log related to session tracking

### Error Handling

- [ ] No JavaScript errors in browser console related to FormTracker
- [ ] No PHP errors in WordPress error log
- [ ] No MySQL errors when applying migration
- [ ] Graceful handling if FormTracker not available
- [ ] No AJAX errors when sending session data

### Documentation

- [ ] Code comments explain session tracking purpose
- [ ] Migration includes proper inline documentation
- [ ] API endpoints documented with session parameters
- [ ] Admin pages include help text for new columns

---

## Sign-Off

**Testing Completed By:** ________________  
**Date:** ________________  
**Overall Status:**

- [ ] **PASS** - All criteria met, system ready for production
- [ ] **CONDITIONAL PASS** - Minor issues found (see below), acceptable for production with noted concerns
- [ ] **FAIL** - Critical issues found (see below), do NOT deploy

### Issues Found (if any):

```
Issue 1:
- Description:
- Severity: Critical / High / Medium / Low
- Affected Component:
- Steps to Reproduce:
- Resolution:
- Testing Status:

Issue 2:
- Description:
- Severity: Critical / High / Medium / Low
- Affected Component:
- Steps to Reproduce:
- Resolution:
- Testing Status:
```

### Performance Baseline:

- Dashboard Load Time: ______ seconds
- Search Analytics Load Time: ______ seconds
- Average Query Time: ______ ms
- Database Size: ______ MB

### Notes:

```
[Add any additional notes, observations, or recommendations here]
```

---

**Document Version:** 1.0  
**Last Updated:** April 2026  
**Maintenance Schedule:** Review quarterly or after major updates  
**Related Documents:**
- PHASE_3_TESTING_CHECKLIST.md
- Database migration files (005-add-session-tracking.php)
- Analytics module documentation
