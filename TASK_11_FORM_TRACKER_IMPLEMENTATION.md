# Task 11: Frontend Form Tracker JavaScript Implementation

## Overview

Task 11 implements a comprehensive frontend form tracking system that monitors all booking form interactions and communicates with the WordPress analytics backend via AJAX. The system tracks:

- Form initialization when user selects a vehicle
- Individual field changes (customer name, email, phone, flight info, etc.)
- Vehicle selection and updates
- Form completion

## Files Created

### 1. `/assets/js/form-tracker.js` (Main Implementation)
The core FormTracker module that provides a global API for form interaction tracking.

**Key Features:**
- Window.AirinelFormTracker object exposed as the main API
- Automatic initialization on page load
- AJAX communication with WordPress backend
- Event listener attachment to form fields
- Language and site source detection

**Main Methods:**
- `startTracking(formData)` - Initializes form tracking with pickup, dropoff, distance, country
- `attachFieldListeners()` - Attaches blur/change event listeners to form fields
- `logFieldChange(event)` - Logs individual field changes (called automatically)
- `updateCustomerData(customerData)` - Updates customer name, email, phone
- `updateVehicleData(vehicleData)` - Updates vehicle selection
- `markFormCompleted()` - Marks booking as completed
- `detectLanguage()` - Detects page language from HTML attributes
- `detectSiteSource()` - Detects main vs regional site
- `getFormId()` - Returns current form ID
- `isActive()` - Checks if form tracking is active

### 2. `/tests/js/test-form-tracker.js` (Unit Tests)
Comprehensive test suite following TDD principles that verifies:

- FormTracker initialization and API exposure
- startTracking AJAX calls with correct parameters
- Field listener attachment
- Field change tracking
- Customer data updates
- Vehicle data updates
- Form completion tracking
- Language detection
- Site source detection
- Form ID storage and retrieval

**Test Coverage:**
- 10+ test functions
- Mock AJAX implementation
- Mock WordPress data object
- Test result summary with pass/fail counts

### 3. `/tests/js/manual-test-form-tracker.html` (Manual Test Interface)
Interactive HTML interface for manual testing with:

- 8 separate test sections
- Form inputs for test data
- Real-time test output
- Mock jQuery AJAX for standalone testing
- Visual test results with color coding

## Integration Points

### 1. JavaScript Integration (booking.js)
Modified `window.bookingStepTwo()` function to initialize form tracking when user selects a vehicle:

```javascript
// Automatically initializes FormTracker when vehicle is selected
if (typeof window.AirinelFormTracker !== 'undefined') {
    const formData = {
        pickup: urlParams.get('pickup') || $('#pickup-location').val(),
        dropoff: urlParams.get('dropoff') || $('#dropoff-location').val(),
        distance: parseFloat(urlParams.get('distance')),
        country: urlParams.get('country') || localStorage.getItem('airlinel_country')
    };
    window.AirinelFormTracker.startTracking(formData);
}
```

### 2. WordPress Integration (functions.php)
Added script enqueue for form-tracker.js:

```php
// Enqueue form tracker for booking analytics (Task 11)
wp_enqueue_script('airlinel-form-tracker', get_template_directory_uri() . '/assets/js/form-tracker.js', array('jquery'), null, true);
```

The script automatically uses the `chauffeur_data` object already localized by WordPress.

### 3. Backend AJAX Handlers (functions.php)
Already implemented AJAX handlers for all form tracking actions:

- `airlinel_ajax_log_form_start` - Creates new form record
- `airlinel_ajax_log_field_change` - Logs field changes
- `airlinel_ajax_update_form_customer` - Updates customer info
- `airlinel_ajax_update_form_vehicle` - Updates vehicle selection
- `airlinel_ajax_mark_form_completed` - Marks form as completed

All handlers are registered with both `wp_ajax` and `wp_ajax_nopriv` actions for unauthenticated users.

## Form Fields Tracked

The FormTracker monitors changes to the following form fields:

**Customer Information:**
- customer_name (input[name="customer_name"])
- customer_email (input[name="customer_email"])
- customer_phone (input[name="customer_phone"])

**Booking Details:**
- pickup_date (input[name="pickup_date"])
- pickup_time (input[name="pickup_time"])
- flight_number (input[name="flight_number"])
- agency_code (input[name="agency_code"])
- notes (textarea[name="notes"])

## Data Flow

### 1. Form Initialization
```
User selects vehicle
↓
bookingStepTwo() called
↓
startTracking(formData) called
↓
AJAX: airlinel_ajax_log_form_start
↓
Backend creates wp_booking_form_analytics record
↓
Returns form_id
↓
FormTracker stores form_id internally
↓
attachFieldListeners() automatically called
```

### 2. Field Change Tracking
```
User types in form field
↓
blur/change event triggered
↓
logFieldChange() handler called
↓
AJAX: airlinel_ajax_log_field_change (fire-and-forget)
↓
Backend logs field change to wp_booking_form_field_changes
```

### 3. Customer Data Update
```
Customer fills name/email/phone
↓
updateCustomerData() called manually or by form handler
↓
AJAX: airlinel_ajax_update_form_customer
↓
Backend updates wp_booking_form_analytics with customer info
↓
Updates form_stage to 'customer_info'
```

### 4. Vehicle Selection Update
```
Vehicle selection changes
↓
updateVehicleData() called
↓
AJAX: airlinel_ajax_update_form_vehicle
↓
Backend updates vehicle_id, vehicle_name, vehicle_price
↓
Updates form_stage to 'booking_details'
```

### 5. Form Completion
```
User finalizes booking
↓
markFormCompleted() called
↓
AJAX: airlinel_ajax_mark_form_completed
↓
Backend marks form_stage as 'completed'
↓
FormTracker stops active tracking
```

## Language Detection

The FormTracker automatically detects the page language in this order:

1. `data-language` attribute on HTML element
2. `lang` attribute on HTML element
3. `window.locale` constant (WordPress locale)
4. `navigator.language` (browser language)
5. Default to 'en' (English)

## Site Source Detection

Distinguishes between main and regional sites:

- **Main Sites:** airlinel.com, www.airlinel.com
- **Regional Sites:** Subdomains like london.airlinel.com, paris.airlinel.com

Returns 'main' or 'regional' based on current hostname.

## Error Handling

The FormTracker implements comprehensive error handling:

- Validates required parameters before AJAX calls
- Logs warnings to browser console for debugging
- Handles AJAX errors gracefully (fire-and-forget pattern for non-critical updates)
- Checks for undefined global objects (chauffeur_data, jQuery)
- Validates form_id is set before logging field changes

## Browser Compatibility

Uses standard JavaScript APIs compatible with all modern browsers:

- ES5+ compatible (no arrow functions or const/let in initialization)
- jQuery for AJAX (already used by theme)
- DOM APIs for element selection and event binding
- Local state management (no external dependencies)

## Testing

### Running Unit Tests
Open `/tests/js/test-form-tracker.js` in a browser console after loading the page:
```javascript
window.AirinelFormTrackerTests.runTests();
```

### Running Manual Tests
1. Open `/tests/js/manual-test-form-tracker.html` in a browser
2. Click individual "Run Test" buttons in each section
3. Or click "Run All Tests" at the bottom

### Production Testing Checklist
- [x] FormTracker initializes on page load
- [x] Vehicle selection triggers form tracking
- [x] Form ID is stored and used for subsequent calls
- [x] Field changes are logged via AJAX
- [x] Customer data updates work
- [x] Vehicle data updates work
- [x] Language detection works
- [x] Site source detection works
- [x] Console warnings appear for missing parameters
- [x] AJAX errors are handled gracefully

## Database Integration

Form data is stored in WordPress tables created by migrations:

### wp_booking_form_analytics
- Stores form records with all booking details
- Created by migration 003-create-booking-form-analytics.php
- Indexed by form_stage, country, created_at, site_source

### wp_booking_form_field_changes
- Stores individual field changes
- Created by migration 004-create-booking-form-field-changes.php
- Tracks field_name, field_value, and timestamp

## Performance Considerations

- Field change tracking uses fire-and-forget AJAX pattern (no blocking)
- Blur event listeners only attach to visible form fields
- Language/site detection runs once at initialization
- Form ID caching prevents repeated lookups
- Automatic initialization prevents manual setup overhead

## Future Enhancements

Potential improvements for future tasks:

1. Add form analytics dashboard in WordPress admin
2. Implement form abandonment tracking
3. Add conversion funnel visualization
4. Track time spent in each form stage
5. A/B testing for form variations
6. Integration with email marketing platforms
7. Real-time form monitoring alerts

## Debug Output

Enable detailed logging by adding to browser console:
```javascript
// View current form state
console.log(window.AirinelFormTracker.getFormId());
console.log(window.AirinelFormTracker.isActive());

// Check detected language and site source
console.log('Language:', window.AirinelFormTracker.detectLanguage());
console.log('Site:', window.AirinelFormTracker.detectSiteSource());
```

All AJAX calls log success/error messages to browser console automatically.

## Troubleshooting

### FormTracker not initializing
- Check if chauffeur_data is available: `console.log(window.chauffeur_data)`
- Verify form-tracker.js script is loaded
- Check browser console for error messages

### Field changes not being tracked
- Verify form fields have correct name attributes
- Check if attachFieldListeners() was called
- Look for AJAX errors in Network tab

### Form ID not being set
- Ensure startTracking() is called after vehicle selection
- Check AJAX response in Network tab
- Verify backend handlers are receiving data

### Language not detected correctly
- Set data-language attribute on HTML: `<html data-language="fr">`
- Or set lang attribute: `<html lang="fr">`
- Check window.locale value in console

## Conclusion

Task 11 successfully implements a production-ready form tracking system that captures all booking form interactions. The implementation follows WordPress best practices, uses proper AJAX security, and integrates seamlessly with the existing analytics infrastructure.
