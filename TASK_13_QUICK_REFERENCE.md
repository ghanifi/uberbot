# Task 13: Quick Reference Card

## Files Created

1. **TASK_13_END_TO_END_TESTING_GUIDE.md** (1,327 lines)
   - Complete testing guide with 8 test cases
   - 12 organized parts covering all testing aspects
   - 42+ verification checkpoints

2. **TASK_13_SUMMARY.md** (402 lines)
   - High-level overview of deliverables
   - Architecture documentation
   - Duration estimates and testing flow

3. **TASK_13_QUICK_REFERENCE.md** (this file)
   - Quick lookup for common testing tasks
   - SQL queries reference
   - Key URLs and endpoints

---

## Quick Test Checklist

### Before Testing
- [ ] WordPress admin accessible at `/wp-admin`
- [ ] Airlinel menu visible in left sidebar
- [ ] Database Migrations show all "Completed"
- [ ] No pending migrations
- [ ] All 3 analytics tables exist (verify in phpMyAdmin)
- [ ] Google Maps API key configured
- [ ] Browser console clear of errors

### The Test Flow (45 minutes)

1. **Search Test** (5-10 min)
   - Fill search form with "London Heathrow" → "Central London"
   - Note the distance from URL
   - Query: `SELECT * FROM wp_booking_search_analytics ORDER BY id DESC LIMIT 1;`

2. **Form Init Test** (10-15 min)
   - Select a vehicle
   - Check FormTracker: `window.AirinelFormTracker.isActive()` → true
   - Query: `SELECT * FROM wp_booking_form_analytics ORDER BY id DESC LIMIT 1;`

3. **Field Changes Test** (15-20 min)
   - Fill: Name, Email, Phone, Date, Time, Flight#, Agency, Notes
   - Watch Network tab for AJAX calls
   - Query: `SELECT * FROM wp_booking_form_field_changes WHERE form_id = [ID] ORDER BY change_timestamp;`

4. **Admin Pages Test** (20-30 min)
   - Go to Airlinel → Analytics Dashboard
   - Verify stats display (searches, bookings, conversion rate)
   - Test filters (date, country, language, source)
   - Check Search Analytics and Form Analytics pages

---

## Essential SQL Queries

### Verify Tables Exist
```sql
SHOW TABLES LIKE 'wp_booking%';
```

### View Latest Search
```sql
SELECT * FROM wp_booking_search_analytics ORDER BY id DESC LIMIT 1;
```

### View Latest Form
```sql
SELECT * FROM wp_booking_form_analytics ORDER BY id DESC LIMIT 1;
```

### View Field Changes for Form
```sql
SELECT * FROM wp_booking_form_field_changes WHERE form_id = [FORM_ID] ORDER BY change_timestamp;
```

### Count Records by Date
```sql
SELECT 
    DATE(timestamp) as date,
    COUNT(*) as searches
FROM wp_booking_search_analytics
GROUP BY DATE(timestamp)
ORDER BY date DESC;
```

### Verify Data Integrity
```sql
SELECT 
    COUNT(*) as total,
    COUNT(pickup) as with_pickup,
    COUNT(dropoff) as with_dropoff,
    COUNT(country) as with_country
FROM wp_booking_search_analytics;
```

### Check Form Stages
```sql
SELECT 
    form_stage,
    COUNT(*) as count
FROM wp_booking_form_analytics
GROUP BY form_stage;
```

### Generate Test Report
```sql
SELECT 
    (SELECT COUNT(*) FROM wp_booking_search_analytics) as Total_Searches,
    (SELECT COUNT(*) FROM wp_booking_form_analytics) as Total_Forms,
    (SELECT COUNT(*) FROM wp_booking_form_field_changes) as Total_Field_Changes;
```

---

## Key Test Data Values

### Search Parameters
- **Pickup:** London Heathrow (or LHR)
- **Dropoff:** Central London
- **Expected Distance:** 25-30 km
- **Expected Country:** UK

### Customer Information
- **Name:** John Smith (or any test name)
- **Email:** john.smith@example.com
- **Phone:** +44 7911 123456
- **Date:** 2-3 days from today
- **Time:** 14:30 (or any time)

### Additional Fields
- **Flight Number:** BA123
- **Agency Code:** AGENCY001
- **Notes:** Test booking for analytics verification

---

## Browser Console Commands

### Check FormTracker Status
```javascript
window.AirinelFormTracker.isActive()  // true = active
```

### Get Current Form ID
```javascript
window.AirinelFormTracker.getFormId()  // numeric form ID
```

### Check Detected Language
```javascript
window.AirinelFormTracker.detectLanguage()  // e.g., 'en', 'fr'
```

### Check Site Source
```javascript
window.AirinelFormTracker.detectSiteSource()  // 'main' or 'regional'
```

### Verify WordPress Data
```javascript
console.log(window.chauffeur_data)  // should show object with ajax_url
```

---

## Admin URLs

### Analytics Dashboard
`/wp-admin/admin.php?page=airlinel-analytics`

### Search Analytics
`/wp-admin/admin.php?page=airlinel-analytics-search`

### Form Analytics
`/wp-admin/admin.php?page=airlinel-analytics-form`

### Database Migrations
`/wp-admin/admin.php?page=airlinel-migrations`

---

## Expected Database Values

### Search Analytics Fields
| Field | Expected Value |
|-------|-----------------|
| stage | 'search' |
| pickup | 'London Heathrow' |
| dropoff | 'Central London' |
| distance_km | 25-30 |
| country | 'UK' |
| currency | 'GBP' |
| vehicle_count | > 0 |
| source | 'main' or 'regional_api' |
| language | 'en', 'fr', etc. |
| exchange_rate | 0.8-1.5 |
| site_url | Domain name |
| ip_address | Your IP |
| timestamp | Recent |

### Form Analytics Fields
| Field | Expected Value |
|-------|-----------------|
| pickup | 'London Heathrow' |
| dropoff | 'Central London' |
| vehicle_name | Selected vehicle |
| vehicle_price | Price number |
| form_stage | 'vehicle_selection' → 'customer_info' → 'booking_details' → 'completed' |
| customer_name | 'John Smith' (after filled) |
| customer_email | 'john.smith@example.com' (after filled) |
| customer_phone | '+44 7911 123456' (after filled) |
| site_source | 'main' or 'regional' |
| ip_address | Your IP |
| created_at | Recent |

### Field Changes Fields
| Field | Expected Value |
|-------|-----------------|
| form_id | Form ID number |
| field_name | 'passenger_name', 'passenger_email', etc. |
| field_value | Value you entered |
| ip_address | Your IP |
| change_timestamp | When you filled field |

---

## Success Indicators

### ✓ Search Analytics Working
- [ ] Query returns 1+ row
- [ ] pickup, dropoff, distance_km populated
- [ ] country, language, site_url populated
- [ ] timestamp is recent

### ✓ Form Analytics Working
- [ ] Query returns 1+ row
- [ ] vehicle_id, vehicle_name populated
- [ ] form_stage shows progression
- [ ] created_at is recent

### ✓ Field Changes Working
- [ ] Query returns 8+ rows
- [ ] Each row has form_id, field_name, field_value
- [ ] change_timestamp in chronological order
- [ ] No NULL values in critical fields

### ✓ Admin Dashboard Working
- [ ] Stats cards show numbers
- [ ] Funnel table shows stages
- [ ] Country table shows data
- [ ] Source table shows data
- [ ] Filters update data

---

## Common Issues & Quick Fixes

### Issue: Tables Don't Exist
```sql
-- Check table status
SHOW TABLES LIKE 'wp_booking%';

-- Solution: Run migrations again in admin
```

### Issue: No Data Appearing
```javascript
// Check FormTracker in console
window.AirinelFormTracker.isActive()  // Should be true

// Check Network tab for AJAX errors
// Filter XHR requests for admin-ajax.php
```

### Issue: Form ID Not Set
```javascript
// Get form ID
var formId = window.AirinelFormTracker.getFormId();
console.log(formId);  // Should be a number, not undefined
```

### Issue: Field Changes Not Logged
```sql
-- Verify records exist
SELECT COUNT(*) FROM wp_booking_form_field_changes WHERE form_id = 5;

-- Verify field_name values match
SELECT DISTINCT field_name FROM wp_booking_form_field_changes;
```

### Issue: Filters Not Working
```javascript
// Check date picker is loaded
console.log(typeof jQuery.datepicker);  // Should be 'function'

// Try manually filtering with SQL
SELECT * FROM wp_booking_search_analytics 
WHERE DATE(timestamp) = '2024-04-27';
```

---

## Performance Benchmarks

| Component | Target | Status |
|-----------|--------|--------|
| Page Load | < 3 sec | _____ |
| Filter Apply | < 1 sec | _____ |
| Query Time | < 100ms | _____ |
| No JS Errors | Pass | _____ |
| No DB Errors | Pass | _____ |

---

## Testing Duration

- **Quick Check:** 15-20 minutes (just verify data appears)
- **Standard Test:** 45 minutes (all test cases)
- **Full Verification:** 2-3 hours (all parts + troubleshooting)

---

## Sign-Off Template

```
Testing Date: _______________
Tester Name: _______________
WordPress Version: __________
PHP Version: ________________

All Test Cases Passed: [ ] Yes [ ] No
All Success Criteria Met: [ ] Yes [ ] No
Issues Found: ______________

Signature: _______________
```

---

## Quick Navigation

- **Full Guide:** TASK_13_END_TO_END_TESTING_GUIDE.md
- **Summary:** TASK_13_SUMMARY.md
- **This Reference:** TASK_13_QUICK_REFERENCE.md

For detailed information on any test case, refer to the full guide by section:
- Part 1: Prerequisites
- Part 2: Search Analytics
- Part 3: Form Initialization
- Part 4: Field Change Tracking
- Part 5: Form Stage Progression
- Part 6: Admin Dashboard
- Part 7: Search Analytics Page
- Part 8: Form Analytics Page
- Part 9: Data Integrity
- Part 10: Performance
- Part 11: Troubleshooting
- Part 12: Success Criteria

---

**Version:** 1.0  
**Task:** Task 13 - End-to-End Analytics Testing  
**Created:** 2024-04-27  
**Status:** Complete
