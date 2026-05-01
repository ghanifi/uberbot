# Phase 3 Testing Checklist
## Manual & Automated Testing for Multi-Site Deployment

**Document Version:** 1.0  
**Last Updated:** April 2026  
**For:** Phase 3 Deployment - Regional Sites & Multi-Language Support

---

## Pre-Testing Setup

- [ ] Test environment identical to production
- [ ] Staging database restored from production backup
- [ ] All Phase 3 code deployed to staging
- [ ] Cache cleared on staging
- [ ] Test accounts created
- [ ] Test data loaded (vehicles, zones, exchange rates)
- [ ] Monitoring enabled on staging
- [ ] Browser developer tools open (console, network tab)

---

## Part 1: Automated Test Suite

### Run All Automated Tests

```bash
# Test 1: Security Fixes (8 tests)
wp eval-file tests/test-security-fixes.php

# Test 2: Regional Sites Integration (23+ tests)
wp eval-file tests/regional-site-tests.php

# Expected output:
# - No fatal errors
# - All tests show ✓ PASS
# - Summary shows 100% pass rate
```

**Automated Tests Status:**
- [ ] Security fixes tests passing
- [ ] Integration tests passing
- [ ] No warnings or notices
- [ ] Expected number of tests running

---

## Part 2: Main Site Functionality Tests

### 2.1 Vehicles Management

**Test:** Admin can manage vehicles

```
1. Login to main site admin
   [ ] Navigate to Vehicles page
   [ ] Verify vehicles list displays
   
2. Add new vehicle
   [ ] Click "Add Vehicle"
   [ ] Enter vehicle details (name, capacity, price)
   [ ] Upload vehicle image
   [ ] Click Save
   [ ] Verify vehicle appears in list
   
3. Edit vehicle
   [ ] Click Edit on existing vehicle
   [ ] Change vehicle details
   [ ] Save changes
   [ ] Verify changes appear in list
   
4. Delete vehicle
   [ ] Click Delete on vehicle
   [ ] Confirm deletion
   [ ] Verify vehicle removed from list
   [ ] Check no broken references in bookings
```

### 2.2 Zone Management

**Test:** Zones (pickup/dropoff locations) work correctly

```
1. View zones list
   [ ] Navigate to Zones page
   [ ] Verify all zones display
   [ ] Check zone count matches database
   
2. Create zone
   [ ] Click "Add Zone"
   [ ] Enter zone name (e.g., "Heathrow Terminal 1")
   [ ] Enter airport code
   [ ] Enter pricing tier
   [ ] Save zone
   [ ] Verify zone appears in search
   
3. Edit zone
   [ ] Update zone details
   [ ] Verify changes reflected in search
   
4. Verify zones in API
   [ ] Make API call: GET /wp-json/airlinel/v1/zones
   [ ] Verify all zones returned
   [ ] Verify correct structure
```

### 2.3 Exchange Rates

**Test:** Currency exchange rates configured and updating

```
1. View exchange rates
   [ ] Navigate to Exchange Rates page
   [ ] Verify all supported currencies displayed
   [ ] Check rates are numeric values
   
2. Update exchange rate
   [ ] Update GBP to USD rate
   [ ] Save changes
   [ ] Verify rate change reflects in pricing
   
3. Exchange rate API
   [ ] Test API returns current rates
   [ ] Verify rates cached
   [ ] Verify cache expires and updates
   
4. Pricing calculation
   [ ] Perform search with different currency
   [ ] Verify prices calculated correctly with exchange rate
```

### 2.4 Reservations

**Test:** Reservation creation and management

```
1. Create test reservation (via API)
   [ ] POST to /wp-json/airlinel/v1/reservation/create
   [ ] Include customer details, pickup, dropoff
   [ ] Verify 200 response with reservation ID
   
2. View reservations in admin
   [ ] Navigate to Reservations page
   [ ] Verify new reservation appears
   [ ] Check all fields displayed correctly
   
3. Edit reservation
   [ ] Click Edit on reservation
   [ ] Change status to "confirmed"
   [ ] Verify email sent to customer
   
4. Filter reservations
   [ ] Filter by status
   [ ] Filter by date range
   [ ] Filter by customer
   [ ] Verify correct results displayed
```

### 2.5 Analytics Dashboard

**Test:** Analytics showing all data correctly

```
1. Load analytics dashboard
   [ ] Navigate to Analytics page
   [ ] Verify page loads without errors
   [ ] Check all widgets display
   
2. Check metrics
   [ ] Total bookings displayed
   [ ] Revenue calculated correctly
   [ ] Booking trend chart displays data
   [ ] Peak hours chart shows correct times
   
3. Test filters
   [ ] Filter by date range
   [ ] Filter by region
   [ ] Filter by payment status
   [ ] Verify filtered results
   
4. Test export
   [ ] Click Export CSV
   [ ] Verify CSV downloads
   [ ] Open CSV and verify data
   [ ] Check formatting and completeness
```

---

## Part 3: Regional Site Functionality Tests

### 3.1 Booking Search (via Proxy)

**Test:** Users on regional sites can search for vehicles

```
URL: https://berlin.airlinel.com/ (simulate regional site)

1. Load homepage
   [ ] Page loads without errors
   [ ] Search form visible
   [ ] Auto-complete works for locations
   
2. Perform search
   [ ] Enter pickup location (London)
   [ ] Enter dropoff location (Heathrow)
   [ ] Select date
   [ ] Enter number of passengers
   [ ] Click Search
   
3. Verify search results
   [ ] Results load (should show vehicles from main site via proxy)
   [ ] Each result shows:
      [ ] Vehicle image
      [ ] Vehicle name
      [ ] Passenger capacity
      [ ] Price in correct currency
      [ ] Available vehicles indicator
      
4. Check console (F12 Developer Tools)
   [ ] No 404 errors
   [ ] No CORS errors
   [ ] API calls to correct endpoint
   [ ] Response time < 2 seconds
```

### 3.2 Booking Creation (via Proxy)

**Test:** Users can create bookings on regional sites

```
1. From search results, select vehicle
   [ ] Click "Book Now"
   [ ] Vehicle details page loads
   [ ] All vehicle info displayed correctly
   
2. Fill booking form
   [ ] Full name field
   [ ] Email field
   [ ] Phone number field
   [ ] Special requests textarea
   [ ] All fields validate on input
   
3. Complete booking
   [ ] Click "Proceed to Payment"
   [ ] Payment form loads
   [ ] Card details form visible (or payment provider modal)
   
4. Verify booking created
   [ ] After successful payment, confirmation page shows
   [ ] Email received with booking confirmation
   [ ] Check main site reservations - booking appears with correct source site
```

### 3.3 Language Switching

**Test:** Language switching works on regional sites

```
1. Homepage language switching
   [ ] Visit https://berlin.airlinel.com/
   [ ] Look for language selector (usually in header)
   [ ] Click German (de)
   [ ] Page reloads in German
   [ ] Check URL includes ?lang=de or similar
   
2. Content in correct language
   [ ] Hero section title in German
   [ ] Button text in German
   [ ] Form labels in German
   [ ] Error messages in German
   
3. API responses respect language
   [ ] Make API call with lang=tr parameter
   [ ] Verify response text in Turkish
   [ ] Check translations are not English fallbacks
   
4. Language persistence
   [ ] Switch to Spanish
   [ ] Navigate to different page
   [ ] Language should remain Spanish
   [ ] Cookie/session storing language preference
   
5. RTL language test (Arabic)
   [ ] Switch to Arabic (ar)
   [ ] Page content aligns right-to-left
   [ ] Input fields align correctly
   [ ] Buttons and controls position correctly
   
6. Test all 12 languages
   [ ] English (en) ✓
   [ ] French (fr) ✓
   [ ] German (de) ✓
   [ ] Spanish (es) ✓
   [ ] Italian (it) ✓
   [ ] Portuguese (pt) ✓
   [ ] Turkish (tr) ✓
   [ ] Arabic (ar) ✓
   [ ] Russian (ru) ✓
   [ ] Japanese (ja) ✓
   [ ] Chinese (zh) ✓
   [ ] Korean (ko) ✓
```

### 3.4 Homepage Sections Toggle

**Test:** Admin can toggle homepage sections per regional site

```
1. Navigate to Homepage Settings
   [ ] Go to admin > Regional Settings > Homepage
   [ ] Verify page loads
   
2. Toggle sections on/off
   [ ] Hero section toggle
   [ ] Services section toggle
   [ ] Featured locations toggle
   [ ] Testimonials section toggle
   [ ] Call-to-action section toggle
   
3. Save settings
   [ ] Click Save
   [ ] Verify success message
   
4. Check frontend
   [ ] Refresh regional site homepage
   [ ] Disabled sections should not display
   [ ] Enabled sections should display
   [ ] Layout should adjust for hidden sections
```

### 3.5 Contact Form

**Test:** Contact form works on regional sites

```
1. Locate contact form
   [ ] Usually in footer or contact page
   [ ] Form visible and interactive
   
2. Fill form
   [ ] Name field
   [ ] Email field
   [ ] Subject field
   [ ] Message textarea
   
3. Submit form
   [ ] Click Submit
   [ ] Form validates (empty required fields should show error)
   [ ] Submission shows success message
   
4. Email verification
   [ ] Check email received
   [ ] Email contains form data
   [ ] Email sent to correct address
   [ ] Email formatted correctly
```

### 3.6 Page Content Management

**Test:** Custom page content editable per regional site

```
1. Navigate to Page Content settings
   [ ] Admin > Pages/Content management
   [ ] Verify current page editable
   
2. Edit page content
   [ ] Update page title
   [ ] Edit page description
   [ ] Add/remove sections
   [ ] Upload images
   
3. Save and verify
   [ ] Click Save
   [ ] Check frontend - changes reflected
   [ ] No broken images or formatting
```

---

## Part 4: Cross-Site Functionality Tests

### 4.1 User Accounts Work Across Sites

**Test:** User account created on one site accessible on others

```
1. Create user account on regional site
   [ ] Visit https://berlin.airlinel.com/register
   [ ] Create account with email and password
   [ ] Verify email confirmation (if applicable)
   
2. Login on same regional site
   [ ] Navigate to login page
   [ ] Enter credentials
   [ ] Successfully logged in
   [ ] User dashboard accessible
   
3. Check account on other regional site
   [ ] Navigate to https://istanbul.airlinel.com/
   [ ] Attempt to login with same email
   [ ] Should be logged in or account accessible (depending on configuration)
   
4. Check account on main site
   [ ] Admin checks user list
   [ ] New user appears in list
   [ ] User metadata correct
```

### 4.2 Reservations Appear on Main Site

**Test:** Reservations made on regional sites visible on main site

```
1. Create booking on regional site
   [ ] Complete full booking on regional site
   [ ] Receive confirmation
   
2. Check main site reservations
   [ ] Login to main site admin
   [ ] Navigate to Reservations
   [ ] Search for booking by customer name
   [ ] Booking should appear
   [ ] All booking details correct
   
3. Verify source site tracking
   [ ] Booking should show source site = "berlin" (or relevant regional site)
   [ ] Source site field populated correctly
```

### 4.3 Source Site Tracking Accurate

**Test:** Source site field correctly records where booking originated

```
1. Make 3 test bookings from different regional sites
   Booking A from berlin.airlinel.com
   Booking B from istanbul.airlinel.com
   Booking C from main.airlinel.com
   
2. Check main site reservations
   [ ] Booking A shows source_site = 'berlin'
   [ ] Booking B shows source_site = 'istanbul'
   [ ] Booking C shows source_site = 'main' or blank
   
3. Filter by source site
   [ ] Filter reservations by source site
   [ ] Only bookings from selected source site display
   [ ] Count matches expected
```

### 4.4 Exchange Rates Sync Correctly

**Test:** Exchange rates consistent across all sites

```
1. Check exchange rate on main site
   [ ] View current GBP to USD rate on main site
   [ ] Note the rate: GBP = $X
   
2. Make identical search on regional sites
   Berlin site search: London to Heathrow
   Istanbul site search: London to Heathrow
   
3. Compare pricing
   [ ] All three searches show same price in base currency
   [ ] Price calculations use same exchange rate
   [ ] No discrepancies between sites
   
4. Update exchange rate
   [ ] Update rate on main site
   [ ] Wait for sync (should be automatic)
   [ ] Check all regional sites use new rate
   [ ] Test pricing calculation with new rate
```

### 4.5 Analytics Show All Regional Sites

**Test:** Analytics aggregate data from all regional sites

```
1. Check main site analytics
   [ ] Total bookings includes bookings from all regional sites
   [ ] Regional breakdown shows:
      [ ] Berlin site bookings
      [ ] Istanbul site bookings
      [ ] Antalya site bookings
      [ ] etc.
   
2. Filter by region
   [ ] Analytics show filtering by region works
   [ ] Selecting "Berlin" shows only Berlin bookings
   
3. Revenue by region
   [ ] Total revenue includes all regions
   [ ] Revenue breakdown by currency
   [ ] Revenue conversion using exchange rates
   
4. Export analytics
   [ ] Export all data as CSV
   [ ] Open CSV and verify completeness
   [ ] Data includes source site information
```

---

## Part 5: Performance Tests

### 5.1 API Proxy Response Times

**Test:** API proxy doesn't add excessive latency

```
Baseline: Local API call (no proxy): ~100ms
Target: Regional site API call (with proxy): <500ms

1. Test search endpoint
   [ ] Time search request from regional site
   [ ] Should complete in < 500ms
   [ ] Log slow queries if > 500ms
   
2. Test reservation creation
   [ ] Time reservation creation from regional site
   [ ] Should complete in < 1000ms (includes processing)
   
3. Test with cache
   [ ] First call: measure baseline time
   [ ] Second identical call: should be <100ms (cached)
   [ ] Cache hit working correctly
   
4. Test under load
   [ ] Simulate 10 concurrent requests
   [ ] All should complete within reasonable time
   [ ] No timeouts or failures
```

### 5.2 Cache Hit Rate

**Test:** Caching working effectively

```
1. Measure cache hits
   [ ] Make identical search 10 times
   [ ] First request: uncached
   [ ] Requests 2-10: cached
   [ ] Cache hit rate should be 90%
   
2. Check cache age
   [ ] Cache should be valid for 5 minutes
   [ ] After 5 minutes, should refresh
   
3. Cache invalidation
   [ ] Update vehicle price on main site
   [ ] Regional sites should see new price within 5 minutes
   [ ] Check cache TTL setting
```

### 5.3 Database Query Performance

**Test:** Database queries optimized

```
Run these commands:

# Enable slow query log temporarily
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 0.5;

# Run tests
wp eval-file tests/regional-site-tests.php

# Check slow queries
grep "Query_time" /var/log/mysql/slow.log | head -20

Expected results:
- No query takes > 1 second
- No N+1 query problems
- Indexes used on all JOINs
```

### 5.4 Front-end Load Times

**Test:** Pages load quickly

```
Browser DevTools (F12):

1. Regional site homepage
   [ ] First Contentful Paint: < 2s
   [ ] Largest Contentful Paint: < 3s
   [ ] Layout Shift: < 0.1
   [ ] No render-blocking resources
   
2. Search results page
   [ ] Load time: < 1s
   [ ] Images lazy-loading
   [ ] Pagination working
   
3. Booking page
   [ ] Form loads quickly
   [ ] No lag when typing
   [ ] Payment form loads fast
   
Optimize if needed:
- Minify CSS/JS
- Compress images
- Enable gzip compression
- Use CDN for assets
```

---

## Part 6: Security Tests

### 6.1 API Key Validation

**Test:** Invalid API keys rejected

```
1. Valid key test
   [ ] Use correct API key in request
   [ ] Request succeeds (200 status)
   
2. Missing key test
   [ ] Omit X-API-Key header
   [ ] Should return 401 Unauthorized
   
3. Invalid key test
   [ ] Use wrong API key
   [ ] Should return 401 Unauthorized
   [ ] Error message doesn't expose key details
   
4. Check logs
   [ ] No API key material in error logs
   [ ] Only hash of key logged (if at all)
```

### 6.2 CSRF Protection

**Test:** CSRF attacks prevented

```
1. Nonce verification
   [ ] Form submission requires valid nonce
   [ ] Old nonces rejected
   [ ] Nonce not leaked in URLs
   
2. AJAX requests
   [ ] AJAX calls validated with nonce
   [ ] wp_verify_nonce() called
   [ ] check_ajax_referer() validates requests
   
3. Manual test
   [ ] Inspect form source (F12)
   [ ] Verify nonce field present
   [ ] Try submitting from different domain (should fail)
```

### 6.3 XSS Protection

**Test:** XSS vulnerabilities prevented

```
1. Test input sanitization
   [ ] Enter HTML in name field: <script>alert('xss')</script>
   [ ] Submit form
   [ ] No JavaScript executed
   [ ] Dangerous characters escaped in output
   
2. Test output escaping
   [ ] Verify HTML entities used: &lt; &gt; &quot;
   [ ] Check page source - no unescaped HTML
   [ ] Use browser DevTools to inspect elements
   
3. Test API responses
   [ ] JSON responses properly escaped
   [ ] No inline JavaScript in JSON
```

### 6.4 SQL Injection Protection

**Test:** SQL injection attempts blocked

```
1. Test search parameters
   [ ] Try SQL injection: ' OR '1'='1
   [ ] Submit: London' OR '1'='1'; --
   [ ] Should return valid results (not extra data)
   [ ] No database error messages shown
   
2. Check queries
   [ ] Verify prepared statements used
   [ ] $wpdb->prepare() used for SQL queries
   [ ] No concatenation of user input
```

### 6.5 Rate Limiting

**Test:** Rate limiting prevents abuse

```
1. Make rapid requests
   [ ] Send 100 requests in 10 seconds
   [ ] After X requests, should get 429 (Too Many Requests)
   [ ] Check rate limit headers:
      X-RateLimit-Limit: 1000
      X-RateLimit-Remaining: 500
      X-RateLimit-Reset: [timestamp]
      
2. Test per IP
   [ ] From different IPs, rate limits separate
   [ ] Each IP has own quota
   
3. Test recovery
   [ ] Wait for rate limit window to reset
   [ ] Requests should succeed again
```

---

## Part 7: Browser Compatibility Tests

Test on major browsers:

### 7.1 Chrome (Latest)
- [ ] Homepage loads
- [ ] Search works
- [ ] Booking completes
- [ ] Language switching works
- [ ] Console no errors

### 7.2 Firefox (Latest)
- [ ] [ ] All above tests

### 7.3 Safari (Latest)
- [ ] [ ] All above tests

### 7.4 Edge (Latest)
- [ ] [ ] All above tests

### 7.5 Mobile - iPhone Safari
- [ ] [ ] All above tests
- [ ] [ ] Layout responsive
- [ ] [ ] Touch interactions work

### 7.6 Mobile - Android Chrome
- [ ] [ ] All above tests
- [ ] [ ] Layout responsive
- [ ] [ ] Touch interactions work

---

## Part 8: Email Tests

### 8.1 Registration/Password Reset Emails

```
1. User registration email
   [ ] Email received
   [ ] Contains account confirmation link (if applicable)
   [ ] Contains login details
   [ ] No broken HTML
   
2. Password reset email
   [ ] User requests password reset
   [ ] Email received
   [ ] Contains reset link
   [ ] Link works and resets password
```

### 8.2 Booking Confirmation Emails

```
1. Booking confirmation
   [ ] Email received after booking creation
   [ ] Contains:
      [ ] Booking reference number
      [ ] Customer name
      [ ] Pickup/dropoff locations
      [ ] Pickup time
      [ ] Vehicle details
      [ ] Price
      [ ] Payment status
      
2. Email formatting
   [ ] Email is readable in all clients (Gmail, Outlook, Apple Mail)
   [ ] Images load (if included)
   [ ] Links clickable
   [ ] No missing fields
```

### 8.3 Admin Notification Emails

```
1. New booking notification
   [ ] Admin receives email for new booking
   [ ] Email contains booking details
   [ ] Contains link to admin panel
```

---

## Part 9: Data Validation Tests

### 9.1 Invalid Input Handling

```
1. Search form
   [ ] Empty pickup - shows error
   [ ] Empty dropoff - shows error
   [ ] Invalid date - shows error
   [ ] Zero passengers - shows error
   [ ] Negative passengers - shows error
   [ ] 21+ passengers - shows error
   [ ] Correct error message displayed
   
2. Booking form
   [ ] Empty name - shows error
   [ ] Invalid email - shows error
   [ ] Invalid phone - shows error (if required)
   [ ] Required fields enforced
   [ ] Error messages helpful
   
3. Admin forms
   [ ] Required fields enforced
   [ ] Numeric fields validate numbers
   [ ] Email fields validate email format
   [ ] URL fields validate URLs
```

### 9.2 Data Integrity

```
1. Vehicle data
   [ ] No duplicate vehicle IDs
   [ ] Prices are valid decimals
   [ ] Capacities are positive integers
   [ ] All required fields populated
   
2. Zone data
   [ ] No duplicate zone names
   [ ] Airport codes valid format
   [ ] Coordinates valid (if stored)
   
3. Reservation data
   [ ] Customer email valid
   [ ] Phone number valid format
   [ ] Payment references unique
   [ ] Source site tracked
   [ ] Timestamps correct
```

---

## Part 10: Regional Sites - Advanced Tests

### 10.1 Regional Site Independence

```
1. Configure regional site A with different settings
   [ ] Different homepage sections
   [ ] Different language default
   [ ] Different currency
   
2. Verify settings isolated
   [ ] Regional site B settings unaffected
   [ ] Main site settings unaffected
   [ ] No setting bleed between sites
   
3. Test feature flags
   [ ] Enable feature on one regional site
   [ ] Feature disabled on other regional site
   [ ] Works independently
```

### 10.2 Main Site Maintenance

```
1. While regional sites running
   [ ] Update vehicle on main site
   [ ] Regional sites see updated vehicle
   [ ] Pricing changes propagate
   [ ] Zones changes visible
   
2. Database changes
   [ ] Add new zone
   [ ] Regional sites see new zone
   [ ] New zone searchable
   
3. Exchange rate update
   [ ] Update rate on main site
   [ ] Regional sites calculate new prices
   [ ] Existing cached searches expire and refresh
```

### 10.3 Regional Site Downtime Tolerance

```
1. Temporarily disable regional site
   [ ] User trying to book on that site sees error
   [ ] Error message helpful
   [ ] Main site unaffected
   
2. Temporarily disable main site API
   [ ] Regional sites use cached data
   [ ] User can still search with cached results
   [ ] Error message indicates using cached data
   [ ] When main site back online, fresh data loads
```

---

## Final Sign-off

### Testing Complete Checklist

- [ ] All automated tests passing
- [ ] Main site functionality verified
- [ ] All regional sites tested
- [ ] Cross-site features working
- [ ] Performance acceptable
- [ ] Security tests passed
- [ ] All browsers tested
- [ ] Email functionality working
- [ ] Data integrity confirmed
- [ ] Regional sites independent
- [ ] Deployment checklist reviewed

### Sign-off

**Testing Lead Name:** ________________  
**Date:** ________________  
**Status:** ☐ PASS - Ready for Production  
☐ CONDITIONAL PASS - Minor issues (document below)  
☐ FAIL - Do not deploy (document below)

**Issues Found (if any):**

```
Issue 1:
- Description:
- Severity: Critical / High / Medium / Low
- Resolution:
- Tested:

Issue 2:
- Description:
- Severity: Critical / High / Medium / Low
- Resolution:
- Tested:
```

**Approval:**

- [ ] QA Lead sign-off
- [ ] Product Manager sign-off
- [ ] Operations Lead sign-off

---

**Document Author:** QA Team  
**Last Review:** April 2026  
**Next Review:** July 2026
