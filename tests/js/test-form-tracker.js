/**
 * Airlinel Form Tracker - Unit Tests
 * Task 11: Test suite for form interaction tracking
 *
 * Tests verify that the FormTracker object:
 * - Initializes and exposes a global API
 * - Tracks form initialization with proper AJAX calls
 * - Monitors field changes and logs them
 * - Handles customer data updates
 * - Tracks vehicle selection changes
 * - Properly detects language and site source
 */

(function() {
    "use strict";

    // Mock jQuery AJAX for testing
    window.jQuery = window.jQuery || {
        ajax: function() {}
    };

    // Mock WordPress data object
    window.chauffeur_data = {
        ajax_url: 'http://localhost/wp-admin/admin-ajax.php',
        nonce: 'test_nonce_12345'
    };

    // Mock window.location for site source detection
    Object.defineProperty(window, 'location', {
        value: {
            hostname: 'london.airlinel.com',
            search: '?pickup=LHR&dropoff=London&distance=15&country=GB'
        },
        writable: true
    });

    // Test Suite
    var testResults = {
        passed: 0,
        failed: 0,
        tests: []
    };

    /**
     * Test assertion function
     */
    function assert(condition, message) {
        if (condition) {
            testResults.passed++;
            testResults.tests.push({ status: 'PASS', message: message });
            console.log('[PASS] ' + message);
        } else {
            testResults.failed++;
            testResults.tests.push({ status: 'FAIL', message: message });
            console.error('[FAIL] ' + message);
        }
    }

    /**
     * Test: FormTracker initializes and exposes global API
     */
    function testFormTrackerInitialization() {
        assert(
            typeof window.AirinelFormTracker !== 'undefined',
            'FormTracker should initialize and be accessible via window.AirinelFormTracker'
        );

        assert(
            typeof window.AirinelFormTracker.startTracking === 'function',
            'FormTracker should expose startTracking method'
        );

        assert(
            typeof window.AirinelFormTracker.attachFieldListeners === 'function',
            'FormTracker should expose attachFieldListeners method'
        );

        assert(
            typeof window.AirinelFormTracker.updateCustomerData === 'function',
            'FormTracker should expose updateCustomerData method'
        );

        assert(
            typeof window.AirinelFormTracker.updateVehicleData === 'function',
            'FormTracker should expose updateVehicleData method'
        );

        assert(
            typeof window.AirinelFormTracker.markFormCompleted === 'function',
            'FormTracker should expose markFormCompleted method'
        );

        assert(
            typeof window.AirinelFormTracker.detectLanguage === 'function',
            'FormTracker should expose detectLanguage method'
        );

        assert(
            typeof window.AirinelFormTracker.detectSiteSource === 'function',
            'FormTracker should expose detectSiteSource method'
        );
    }

    /**
     * Test: startTracking makes AJAX call with correct parameters
     */
    function testStartTracking() {
        var ajaxCalled = false;
        var ajaxData = null;

        // Mock jQuery.ajax
        jQuery.ajax = function(config) {
            ajaxCalled = true;
            ajaxData = config.data;
        };

        var formData = {
            pickup: 'London Heathrow',
            dropoff: 'London City Center',
            distance: 25.5,
            country: 'GB'
        };

        window.AirinelFormTracker.startTracking(formData);

        assert(ajaxCalled, 'startTracking should call jQuery.ajax');
        assert(ajaxData !== null, 'AJAX call should include data');

        if (ajaxData) {
            assert(
                ajaxData.action === 'airlinel_ajax_log_form_start',
                'AJAX action should be airlinel_ajax_log_form_start'
            );

            assert(
                ajaxData.pickup === 'London Heathrow',
                'AJAX data should include pickup location'
            );

            assert(
                ajaxData.dropoff === 'London City Center',
                'AJAX data should include dropoff location'
            );

            assert(
                ajaxData.distance === 25.5,
                'AJAX data should include distance'
            );

            assert(
                ajaxData.country === 'GB',
                'AJAX data should include country'
            );

            assert(
                typeof ajaxData.language === 'string',
                'AJAX data should include language'
            );

            assert(
                typeof ajaxData.site_source === 'string',
                'AJAX data should include site_source'
            );
        }
    }

    /**
     * Test: attachFieldListeners binds events to form fields
     */
    function testAttachFieldListeners() {
        // Create mock form fields
        var mockFields = {
            customer_name: document.createElement('input'),
            customer_email: document.createElement('input'),
            customer_phone: document.createElement('input'),
            pickup_date: document.createElement('input'),
            pickup_time: document.createElement('input'),
            flight_number: document.createElement('input'),
            agency_code: document.createElement('input'),
            notes: document.createElement('textarea')
        };

        // Set up field names
        Object.keys(mockFields).forEach(function(fieldName) {
            mockFields[fieldName].name = fieldName;
            mockFields[fieldName].value = 'test_value';
        });

        // Mock document.querySelectorAll to return our mock fields
        var originalQuerySelectorAll = document.querySelectorAll;
        document.querySelectorAll = function(selector) {
            if (selector === 'input[name="customer_name"]') {
                return [mockFields.customer_name];
            } else if (selector === 'input[name="customer_email"]') {
                return [mockFields.customer_email];
            }
            return originalQuerySelectorAll.call(document, selector);
        };

        var listenerAttached = false;
        var originalAddEventListener = Element.prototype.addEventListener;
        Element.prototype.addEventListener = function(event, handler) {
            if ((event === 'blur' || event === 'change') &&
                (this.name === 'customer_name' || this.name === 'customer_email')) {
                listenerAttached = true;
            }
            originalAddEventListener.call(this, event, handler);
        };

        window.AirinelFormTracker.attachFieldListeners();

        assert(
            listenerAttached,
            'attachFieldListeners should attach event listeners to form fields'
        );

        // Restore
        document.querySelectorAll = originalQuerySelectorAll;
        Element.prototype.addEventListener = originalAddEventListener;
    }

    /**
     * Test: Field changes trigger AJAX calls
     */
    function testFieldChangeTriggers() {
        var ajaxCallCount = 0;
        jQuery.ajax = function(config) {
            if (config.data && config.data.action === 'airlinel_ajax_log_field_change') {
                ajaxCallCount++;
            }
        };

        // Simulate a field change by directly calling the handler
        var mockEvent = {
            target: {
                name: 'customer_name',
                value: 'John Doe'
            }
        };

        // Manually trigger field change if the tracker has the handler stored
        if (window.AirinelFormTracker.logFieldChange) {
            window.AirinelFormTracker.logFieldChange.call(mockEvent);
        }

        assert(
            ajaxCallCount > 0,
            'Field changes should trigger AJAX calls to log_field_change'
        );
    }

    /**
     * Test: updateCustomerData calls correct AJAX endpoint
     */
    function testUpdateCustomerData() {
        var ajaxCalled = false;
        var ajaxAction = null;

        jQuery.ajax = function(config) {
            ajaxCalled = true;
            ajaxAction = config.data ? config.data.action : null;
        };

        var customerData = {
            customer_name: 'Jane Smith',
            customer_email: 'jane@example.com',
            customer_phone: '+44 20 1234 5678'
        };

        window.AirinelFormTracker.updateCustomerData(customerData);

        assert(
            ajaxCalled,
            'updateCustomerData should call jQuery.ajax'
        );

        assert(
            ajaxAction === 'airlinel_ajax_update_form_customer',
            'updateCustomerData should use airlinel_ajax_update_form_customer action'
        );
    }

    /**
     * Test: updateVehicleData calls correct AJAX endpoint
     */
    function testUpdateVehicleData() {
        var ajaxCalled = false;
        var ajaxAction = null;

        jQuery.ajax = function(config) {
            ajaxCalled = true;
            ajaxAction = config.data ? config.data.action : null;
        };

        var vehicleData = {
            vehicle_id: 42,
            vehicle_name: 'Mercedes S-Class',
            price: 89.50
        };

        window.AirinelFormTracker.updateVehicleData(vehicleData);

        assert(
            ajaxCalled,
            'updateVehicleData should call jQuery.ajax'
        );

        assert(
            ajaxAction === 'airlinel_ajax_update_form_vehicle',
            'updateVehicleData should use airlinel_ajax_update_form_vehicle action'
        );
    }

    /**
     * Test: markFormCompleted calls correct AJAX endpoint
     */
    function testMarkFormCompleted() {
        var ajaxCalled = false;
        var ajaxAction = null;

        jQuery.ajax = function(config) {
            ajaxCalled = true;
            ajaxAction = config.data ? config.data.action : null;
        };

        window.AirinelFormTracker.markFormCompleted();

        assert(
            ajaxCalled,
            'markFormCompleted should call jQuery.ajax'
        );

        assert(
            ajaxAction === 'airlinel_ajax_mark_form_completed',
            'markFormCompleted should use airlinel_ajax_mark_form_completed action'
        );
    }

    /**
     * Test: detectLanguage returns language code
     */
    function testDetectLanguage() {
        var language = window.AirinelFormTracker.detectLanguage();

        assert(
            typeof language === 'string',
            'detectLanguage should return a string'
        );

        assert(
            language.length >= 2,
            'detectLanguage should return a valid language code'
        );
    }

    /**
     * Test: detectSiteSource distinguishes main vs regional sites
     */
    function testDetectSiteSource() {
        var siteSource = window.AirinelFormTracker.detectSiteSource();

        assert(
            typeof siteSource === 'string',
            'detectSiteSource should return a string'
        );

        assert(
            (siteSource === 'main' || siteSource === 'regional'),
            'detectSiteSource should return either "main" or "regional"'
        );
    }

    /**
     * Test: Form ID is stored and used across calls
     */
    function testFormIdStorage() {
        var storedFormId = null;

        jQuery.ajax = function(config) {
            if (config.success) {
                // Simulate successful response with form_id
                config.success({
                    success: true,
                    data: {
                        form_id: 12345
                    }
                });
            }
        };

        var formData = {
            pickup: 'LHR',
            dropoff: 'London',
            distance: 20,
            country: 'GB'
        };

        window.AirinelFormTracker.startTracking(formData);

        // Check if form_id was stored
        if (window.AirinelFormTracker.getFormId) {
            storedFormId = window.AirinelFormTracker.getFormId();
        }

        assert(
            storedFormId !== null && storedFormId > 0,
            'FormTracker should store and retrieve form_id'
        );
    }

    /**
     * Run all tests
     */
    function runAllTests() {
        console.log('\n====== Airlinel Form Tracker - Test Suite ======\n');

        testFormTrackerInitialization();
        testStartTracking();
        testAttachFieldListeners();
        testFieldChangeTriggers();
        testUpdateCustomerData();
        testUpdateVehicleData();
        testMarkFormCompleted();
        testDetectLanguage();
        testDetectSiteSource();
        testFormIdStorage();

        // Print summary
        console.log('\n====== Test Summary ======');
        console.log('Passed: ' + testResults.passed);
        console.log('Failed: ' + testResults.failed);
        console.log('Total:  ' + (testResults.passed + testResults.failed));
        console.log('======\n');

        if (testResults.failed > 0) {
            console.error('TESTS FAILED - See details above');
            return false;
        } else {
            console.log('ALL TESTS PASSED');
            return true;
        }
    }

    // Run tests when document is ready or immediately if already loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runAllTests);
    } else {
        runAllTests();
    }

    // Export for testing frameworks
    window.AirinelFormTrackerTests = {
        results: testResults,
        runTests: runAllTests
    };
})();
