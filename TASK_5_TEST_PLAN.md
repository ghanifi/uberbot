# Task 5 Testing Plan

## Test Environment Setup

### Prerequisites
- Main site properly configured (AIRLINEL_IS_REGIONAL_SITE not defined or false)
- Regional site configured (AIRLINEL_IS_REGIONAL_SITE = true in wp-config.php)
- WordPress admin access with manage_options capability
- Browser developer tools (for database verification)

## Test Cases

### Section 1: Admin Interface Display

#### Test 1.1: Main Site Admin Display
**Steps:**
1. Log into main site WordPress admin
2. Navigate to Settings > Page Content Settings

**Expected Results:**
- Page title shows "Page Content Settings" (no regional badge)
- Information box shows: "Main Site Settings"
- Message: "These are the default values used by regional sites"
- All input fields are empty or contain previously saved values
- No "regional_" prefixed options in database

**Verification:**
```sql
SELECT * FROM wp_options WHERE option_name LIKE 'regional_%' AND option_name LIKE 'airlinel%'
-- Should return 0 results
```

#### Test 1.2: Regional Site Admin Display
**Steps:**
1. Log into regional site WordPress admin
2. Navigate to Settings > Page Content Settings

**Expected Results:**
- Page title shows "Page Content Settings (Regional Site - override main site defaults)"
- Regional site badge appears with blue background
- Information box shows: "Regional Site Mode"
- Message: "Leave fields blank to use main site defaults"
- Help section visible explaining override behavior
- All fields initially empty (no values saved yet)

**Verification:**
```php
defined('AIRLINEL_IS_REGIONAL_SITE') && AIRLINEL_IS_REGIONAL_SITE // Should be true
```

---

### Section 2: Main Site Content Entry

#### Test 2.1: Contact Information Entry
**Steps:**
1. On main site, fill in all contact fields:
   - Phone: "+44 20 7946 0958"
   - Email: "london@airlinel.com"
   - Address: "123 Bond Street, London, W1S 1AP"
2. Click "Save Changes"
3. Reload page

**Expected Results:**
- Success message: "Settings saved successfully!"
- All values persist on page reload
- Database options created (without regional_ prefix):
  - airlinel_contact_phone
  - airlinel_contact_email
  - airlinel_contact_address

**Verification:**
```sql
SELECT option_name, option_value FROM wp_options 
WHERE option_name IN ('airlinel_contact_phone', 'airlinel_contact_email', 'airlinel_contact_address')
-- Should show 3 rows with correct values
```

#### Test 2.2: Company Information Entry
**Steps:**
1. Fill company information fields using rich text editor:
   - Description: "Airlinel provides premium airport transfers across the UK..."
   - Mission: "To deliver exceptional airport transfer services..."
   - History: "Founded in 2009, Airlinel has..."
2. Click "Save Changes"
3. Reload page

**Expected Results:**
- Rich text editor preserves formatting
- Content persists after reload
- HTML formatting preserved in database
- Database options created:
  - airlinel_company_description
  - airlinel_company_mission
  - airlinel_company_history

#### Test 2.3: Trust Indicators Entry
**Steps:**
1. Fill trust indicators:
   - Years in Business: "15"
   - Customers Served: "50000"
   - Fleet Size: "250"
   - Daily Rides: "1000"
2. Click "Save Changes"

**Expected Results:**
- All numeric values accepted
- Values persist after reload
- Options created in database

#### Test 2.4: Business Hours Entry
**Steps:**
1. Set business hours for all days:
   - Monday-Friday: 06:00 - 23:00
   - Saturday-Sunday: 09:00 - 23:00
2. Click "Save Changes"
3. Verify on homepage that hours display correctly

**Expected Results:**
- Time inputs accept valid times
- Values persist
- Single database option created: airlinel_business_hours (serialized array)
- Frontend displays correct hours

**Verification:**
```php
$hours = get_option('airlinel_business_hours');
// Should be array with keys: monday, tuesday, wednesday, thursday, friday, saturday, sunday
// Each containing 'open' and 'close' keys
```

---

### Section 3: Regional Site Override - Part 1

#### Test 3.1: Regional Site Inherits Main Site Defaults
**Steps:**
1. On regional site, go to Page Content Settings
2. Leave all fields empty
3. Check database for regional_ prefixed options

**Expected Results:**
- All fields display empty (no values shown)
- Database has no regional_ prefixed options
- Frontend displays main site values automatically

#### Test 3.2: Regional Phone Number Override
**Steps:**
1. On regional site, enter:
   - Phone: "+1 (555) 555-0199"
2. Leave other contact fields empty
3. Click "Save Changes"

**Expected Results:**
- Success message appears
- Phone field shows entered value
- Email and address fields still empty
- Only regional_airlinel_contact_phone created in database

**Verification:**
```sql
SELECT option_name FROM wp_options WHERE option_name LIKE 'regional_%' AND option_name LIKE 'airlinel%'
-- Should show: regional_airlinel_contact_phone only
```

#### Test 3.3: Regional Company Description Override
**Steps:**
1. On regional site, fill only company description:
   - "Istanbul office: Premium transfers from Istanbul Airport..."
2. Leave mission and history empty
3. Save

**Expected Results:**
- Description saved with regional_ prefix
- Mission and history fall back to main site values
- Frontend shows custom description for this region

---

### Section 4: Fallback Chain Verification

#### Test 4.1: Regional → Main → Default Fallback
**Steps:**
1. Clear regional_airlinel_contact_phone from database
2. Clear airlinel_contact_phone from database
3. Call Airlinel_Page_Manager::get_contact_info()

**Expected Results:**
- Phone returns default: "+44 (0)20 XXXX XXXX"

**Verification:**
```php
$contact = Airlinel_Page_Manager::get_contact_info();
echo $contact['phone']; // Should be default
```

#### Test 4.2: Regional Override Precedence
**Steps:**
1. Main site phone: "+44 20 XXXX XXXX"
2. Regional phone: "+1 555 0000"
3. Call get_contact_info() on regional site

**Expected Results:**
- Returns regional value: "+1 555 0000"
- Never falls back to main site or default

**Verification:**
```php
define('AIRLINEL_IS_REGIONAL_SITE', true);
$contact = Airlinel_Page_Manager::get_contact_info();
echo $contact['phone']; // Should be "+1 555 0000"
```

---

### Section 5: Business Hours Special Handling

#### Test 5.1: Regional Business Hours Override
**Steps:**
1. Main site hours: 06:00-23:00 (all days)
2. Regional site: Set Monday-Friday 08:00-22:00, weekend 10:00-20:00
3. Check database

**Expected Results:**
- regional_airlinel_business_hours contains custom array
- get_business_hours() on regional site returns custom hours
- get_business_hours() on main site returns main hours

#### Test 5.2: Business Hours Array Structure
**Steps:**
1. Inspect database for business hours option
2. Verify structure

**Expected Results:**
```php
$hours = array(
    'monday' => array('open' => '08:00', 'close' => '22:00'),
    'tuesday' => array('open' => '08:00', 'close' => '22:00'),
    // ... etc
);
```

---

### Section 6: Empty Field Handling

#### Test 6.1: Main Site Empty Field Behavior
**Steps:**
1. Main site: Enter phone "123456"
2. Save
3. Clear phone field (leave empty)
4. Save

**Expected Results:**
- Option deleted from database
- No airlinel_contact_phone in database
- Default value returns when needed

#### Test 6.2: Regional Site Empty Field Behavior
**Steps:**
1. Regional site: Enter phone "555-0000"
2. Save
3. Clear phone field
4. Save

**Expected Results:**
- Option deleted from database
- No regional_airlinel_contact_phone in database
- Falls back to main site value (if exists) or default

---

### Section 7: Rich Text Editor

#### Test 7.1: Rich Text Formatting Preservation
**Steps:**
1. Fill company description with:
   - Bold text
   - Italic text
   - Numbered list
   - Paragraph breaks
2. Save
3. Reload page

**Expected Results:**
- Formatting preserved in admin
- HTML properly escaped in database
- Frontend displays with wp_kses_post() for security

#### Test 7.2: Special Characters Handling
**Steps:**
1. Enter text with special characters: &, <, >, "
2. Save
3. Check database

**Expected Results:**
- Characters properly escaped/sanitized
- wp_kses_post() sanitizes on retrieval
- Frontend displays correctly

---

### Section 8: Frontend Display Testing

#### Test 8.1: Main Site Frontend Display
**Steps:**
1. Main site: Set all values
2. Check About page, Contact page, Homepage

**Expected Results:**
- Contact info displays correctly
- Company information shows on About page
- Trust indicators show on About page
- Business hours display correctly

#### Test 8.2: Regional Site Frontend Display
**Steps:**
1. Regional site: Set region-specific phone and description
2. Check same pages

**Expected Results:**
- Shows regional phone (not main site)
- Shows regional description (not main site)
- Shows main site trust indicators (not overridden)
- Shows regional hours (if set) or main site hours (if not)

---

### Section 9: Security Testing

#### Test 9.1: Nonce Verification
**Steps:**
1. Open admin page source code
2. Copy form data without nonce
3. Attempt POST directly

**Expected Results:**
- Request fails with "Nonce verification failed"
- No data saved

#### Test 9.2: Capability Check
**Steps:**
1. Log out
2. Access /wp-admin/admin.php?page=page-content-settings

**Expected Results:**
- Redirected to login or shown permission error
- Page not accessible without manage_options

#### Test 9.3: XSS Prevention
**Steps:**
1. Attempt to inject JavaScript in phone field:
   - `<script>alert('xss')</script>`
2. Save
3. Check admin page display
4. Check frontend display

**Expected Results:**
- Script tags removed/escaped
- No JavaScript execution
- Text displays as literal string

#### Test 9.4: SQL Injection Prevention
**Steps:**
1. Attempt SQL injection in email field:
   - `test@example.com' OR '1'='1`
2. Save

**Expected Results:**
- Safely stored as text
- No database errors
- Retrieved correctly escaped

---

### Section 10: Data Persistence

#### Test 10.1: Persistent Across Page Reloads
**Steps:**
1. Fill all fields
2. Save
3. Reload page 5 times
4. Navigate away and back

**Expected Results:**
- All values persist
- No data loss

#### Test 10.2: Persistent Across WordPress Updates
**Steps:**
1. Set values
2. Run WordPress core update
3. Check page content settings

**Expected Results:**
- All values preserved
- No data corruption

---

### Section 11: Edge Cases

#### Test 11.1: Very Long Text Input
**Steps:**
1. Company description: 10,000+ characters
2. Save

**Expected Results:**
- Text saved completely
- Truncation not needed for normal use case

#### Test 11.2: Special Time Values
**Steps:**
1. Set opening time: 00:00 (midnight)
2. Set closing time: 23:59 (just before midnight)
3. Save and verify is_open_now() works correctly

**Expected Results:**
- Times saved correctly
- is_open_now() calculation correct

#### Test 11.3: All Fields Empty
**Steps:**
1. Main site: Delete all options from database
2. Call all Airlinel_Page_Manager methods

**Expected Results:**
- All methods return default values
- No errors, graceful fallback

---

## Test Result Summary Template

```
Test Case: [Number and Name]
Status: [PASS/FAIL]
Expected: [Expected behavior]
Actual: [What actually happened]
Notes: [Any observations]
```

## Performance Baseline

Before optimizations, measure:
```php
// Query count for page load
define('SAVEQUERIES', true);
// Check $GLOBALS['wpdb']->queries
```

After implementation, verify:
- No increase in database queries
- Static property caching working
- get_option() cache being used

## Accessibility Testing

- [ ] All form labels properly associated with inputs
- [ ] Form submits with Enter key
- [ ] Tab navigation works through all fields
- [ ] Rich text editor accessible via keyboard
- [ ] Error messages clear and helpful
- [ ] Success messages announced

## Browser Compatibility

Test on:
- [ ] Chrome/Chromium
- [ ] Firefox
- [ ] Safari
- [ ] Edge
- [ ] Mobile browsers (iOS Safari, Chrome Android)

## Conclusion Criteria

All tests pass when:
- Regional override fallback chain works perfectly
- Admin UI clearly indicates site type
- Form submission handles all field types correctly
- Security measures prevent injection attacks
- Data persists reliably
- Frontend displays correct content
- All accessibility requirements met
