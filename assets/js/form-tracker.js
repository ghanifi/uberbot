/**
 * Airlinel Form Tracker
 * Task 11: Frontend form interaction tracking system
 *
 * This module tracks all booking form interactions and communicates with
 * the WordPress backend analytics system via AJAX. It monitors:
 * - Form initialization and customer information
 * - Individual field changes (customer details, flight info, etc.)
 * - Vehicle selection and updates
 * - Form completion
 *
 * Usage:
 *   1. Call startTracking(formData) when vehicle is selected
 *   2. Call attachFieldListeners() to enable field change tracking
 *   3. Call updateCustomerData() when customer info is filled
 *   4. Call updateVehicleData() when vehicle is selected
 *   5. Call markFormCompleted() when booking is finalized
 */

(function() {
    "use strict";

    /**
     * Main FormTracker object - exposed as window.AirinelFormTracker
     */
    var FormTracker = {
        // Internal state
        formId: null,
        sessionId: null,
        isTracking: false,
        ajaxUrl: null,
        nonce: null,

        /**
         * Initialize the tracker (called on page load)
         */
        init: function() {
            // Get WordPress AJAX settings from global data object
            if (typeof chauffeur_data !== 'undefined' && chauffeur_data.ajax_url) {
                this.ajaxUrl = chauffeur_data.ajax_url;
                this.nonce = chauffeur_data.nonce || '';
            }

            if (!this.ajaxUrl) {
                console.warn('FormTracker: chauffeur_data.ajax_url not available');
                return;
            }

            // Generate session ID
            this.sessionId = this.generateSessionId();

            // Store session_id globally for other scripts
            window.airinelSessionId = this.sessionId;

            console.log('FormTracker initialized with session ID ' + this.sessionId);
        },

        /**
         * Generate UUID v4 format session ID
         */
        generateSessionId: function() {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                var r = Math.random() * 16 | 0,
                    v = c == 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        },

        /**
         * Start tracking a new booking form
         * Called when user selects a vehicle (after search results)
         *
         * @param {Object} formData - Initial form data
         *   - pickup: {string} Pickup location
         *   - dropoff: {string} Dropoff location
         *   - distance: {number} Distance in kilometers
         *   - country: {string} Country code (e.g., 'GB')
         *   - [language]: {string} Language code (auto-detected if omitted)
         *   - [site_source]: {string} 'main' or 'regional' (auto-detected if omitted)
         */
        startTracking: function(formData) {
            var self = this;

            if (!this.ajaxUrl) {
                console.warn('FormTracker: Cannot start tracking - AJAX URL not available');
                return;
            }

            // Validate required fields
            if (!formData.pickup || !formData.dropoff || !formData.country) {
                console.warn('FormTracker: Missing required form data (pickup, dropoff, country)');
                return;
            }

            // Prepare AJAX data
            var ajaxData = {
                action: 'airlinel_ajax_log_form_start',
                nonce: this.nonce,
                pickup: formData.pickup || '',
                dropoff: formData.dropoff || '',
                distance: parseFloat(formData.distance) || 0,
                country: formData.country || '',
                language: formData.language || this.detectLanguage(),
                site_source: formData.site_source || this.detectSiteSource(),
                session_id: this.sessionId,
                website_id: this.detectSiteSource(),
                website_language: this.detectLanguage()
            };

            // Make AJAX call
            jQuery.ajax({
                url: this.ajaxUrl,
                type: 'POST',
                data: ajaxData,
                success: function(response) {
                    if (response.success && response.data && response.data.form_id) {
                        self.formId = response.data.form_id;
                        self.isTracking = true;
                        console.log('FormTracker: Form tracking started with ID ' + self.formId);

                        // Update session_id from response if provided
                        if (response.data && response.data.session_id) {
                            self.sessionId = response.data.session_id;
                            window.airinelSessionId = self.sessionId;
                        }

                        // Attach field listeners once form tracking is active
                        self.attachFieldListeners();
                    } else {
                        console.warn('FormTracker: Failed to start form tracking', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.warn('FormTracker: AJAX error starting form tracking', error);
                }
            });
        },

        /**
         * Attach event listeners to all form fields for change tracking
         * Listens for blur (text fields) and change (select/time) events
         */
        attachFieldListeners: function() {
            var self = this;

            // List of all fields to track
            var fieldSelectors = [
                'input[name="customer_name"]',
                'input[name="customer_email"]',
                'input[name="customer_phone"]',
                'input[name="pickup_date"]',
                'input[name="pickup_time"]',
                'input[name="flight_number"]',
                'input[name="agency_code"]',
                'textarea[name="notes"]'
            ];

            // Attach listeners to each field
            fieldSelectors.forEach(function(selector) {
                var fields = document.querySelectorAll(selector);
                fields.forEach(function(field) {
                    // Determine event type based on field type
                    var eventType = (field.tagName === 'TEXTAREA' || field.type === 'text' || field.type === 'email' || field.type === 'tel')
                        ? 'blur'
                        : 'change';

                    // Attach event listener
                    field.addEventListener(eventType, function(e) {
                        self.logFieldChange(e);
                    });
                });
            });

            console.log('FormTracker: Field listeners attached');
        },

        /**
         * Log a field change to the backend
         * Called automatically by field event listeners
         *
         * @param {Event} event - DOM event from field change
         */
        logFieldChange: function(event) {
            var self = this;

            if (!this.formId) {
                console.warn('FormTracker: Cannot log field change - no active form');
                return;
            }

            var field = event.target;
            var fieldName = field.name;
            var fieldValue = field.value;

            // Skip logging if field is empty
            if (!fieldName) {
                return;
            }

            // Prepare AJAX data
            var ajaxData = {
                action: 'airlinel_ajax_log_field_change',
                nonce: this.nonce,
                form_id: this.formId,
                field_name: fieldName,
                field_value: fieldValue,
                session_id: this.sessionId
            };

            // Make AJAX call (fire and forget - don't wait for response)
            jQuery.ajax({
                url: this.ajaxUrl,
                type: 'POST',
                data: ajaxData,
                success: function(response) {
                    console.log('FormTracker: Logged field change - ' + fieldName);
                },
                error: function(xhr, status, error) {
                    console.warn('FormTracker: Error logging field change for ' + fieldName, error);
                }
            });
        },

        /**
         * Update form with customer information
         * Called when customer name, email, and phone are filled
         *
         * @param {Object} customerData
         *   - customer_name: {string} Customer's full name
         *   - customer_email: {string} Customer's email address
         *   - customer_phone: {string} Customer's phone number
         */
        updateCustomerData: function(customerData) {
            var self = this;

            if (!this.formId) {
                console.warn('FormTracker: Cannot update customer data - no active form');
                return;
            }

            // Prepare AJAX data
            var ajaxData = {
                action: 'airlinel_ajax_update_form_customer',
                nonce: this.nonce,
                form_id: this.formId,
                customer_name: customerData.customer_name || '',
                customer_email: customerData.customer_email || '',
                customer_phone: customerData.customer_phone || '',
                session_id: this.sessionId
            };

            // Make AJAX call
            jQuery.ajax({
                url: this.ajaxUrl,
                type: 'POST',
                data: ajaxData,
                success: function(response) {
                    if (response.success) {
                        console.log('FormTracker: Customer data updated for form ' + self.formId);
                    } else {
                        console.warn('FormTracker: Failed to update customer data', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.warn('FormTracker: Error updating customer data', error);
                }
            });
        },

        /**
         * Update form with vehicle selection
         * Called when user selects/changes the vehicle
         *
         * @param {Object} vehicleData
         *   - vehicle_id: {number} Vehicle ID from system
         *   - vehicle_name: {string} Vehicle name/type (e.g., 'Mercedes S-Class')
         *   - price: {number} Vehicle price in base currency
         */
        updateVehicleData: function(vehicleData) {
            var self = this;

            if (!this.formId) {
                console.warn('FormTracker: Cannot update vehicle data - no active form');
                return;
            }

            // Prepare AJAX data
            var ajaxData = {
                action: 'airlinel_ajax_update_form_vehicle',
                nonce: this.nonce,
                form_id: this.formId,
                vehicle_id: vehicleData.vehicle_id || 0,
                vehicle_name: vehicleData.vehicle_name || '',
                vehicle_price: vehicleData.price || 0,
                session_id: this.sessionId
            };

            // Make AJAX call
            jQuery.ajax({
                url: this.ajaxUrl,
                type: 'POST',
                data: ajaxData,
                success: function(response) {
                    if (response.success) {
                        console.log('FormTracker: Vehicle data updated for form ' + self.formId);
                    } else {
                        console.warn('FormTracker: Failed to update vehicle data', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.warn('FormTracker: Error updating vehicle data', error);
                }
            });
        },

        /**
         * Mark the booking form as completed
         * Called when the booking is finalized/submitted
         */
        markFormCompleted: function() {
            var self = this;

            if (!this.formId) {
                console.warn('FormTracker: Cannot mark form completed - no active form');
                return;
            }

            // Prepare AJAX data
            var ajaxData = {
                action: 'airlinel_ajax_mark_form_completed',
                nonce: this.nonce,
                form_id: this.formId,
                session_id: this.sessionId
            };

            // Make AJAX call
            jQuery.ajax({
                url: this.ajaxUrl,
                type: 'POST',
                data: ajaxData,
                success: function(response) {
                    if (response.success) {
                        console.log('FormTracker: Form ' + self.formId + ' marked as completed');
                        self.isTracking = false;
                    } else {
                        console.warn('FormTracker: Failed to mark form completed', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.warn('FormTracker: Error marking form completed', error);
                }
            });
        },

        /**
         * Detect the current language from page HTML or locale constant
         * Looks for: data-language attribute, html lang attribute, or locale constant
         *
         * @returns {string} Language code (e.g., 'en', 'fr', 'de')
         */
        detectLanguage: function() {
            // Try data-language attribute on html
            var htmlElement = document.documentElement;
            var language = htmlElement.getAttribute('data-language');

            if (language) {
                return language.substring(0, 2);
            }

            // Try lang attribute on html
            language = htmlElement.lang;
            if (language) {
                return language.substring(0, 2);
            }

            // Try window.locale constant (WordPress locale)
            if (typeof window.locale !== 'undefined') {
                language = window.locale.substring(0, 2);
                return language;
            }

            // Try navigator language as fallback
            if (navigator.language) {
                return navigator.language.substring(0, 2);
            }

            // Default to English
            return 'en';
        },

        /**
         * Detect whether this is main site or regional site
         * Compares current hostname with known main domain
         *
         * @returns {string} 'main' or 'regional'
         */
        detectSiteSource: function() {
            var hostname = window.location.hostname;

            // Main site patterns (exact domain)
            var mainSites = [
                'airlinel.com',
                'www.airlinel.com'
            ];

            // Check if hostname matches main site
            for (var i = 0; i < mainSites.length; i++) {
                if (hostname === mainSites[i] || hostname.endsWith('.' + mainSites[i])) {
                    return 'main';
                }
            }

            // If not main, it's regional (subdomain like london.airlinel.com)
            return 'regional';
        },

        /**
         * Get the current form ID
         * Useful for integrations and testing
         *
         * @returns {number|null} Current form ID or null if not tracking
         */
        getFormId: function() {
            return this.formId;
        },

        /**
         * Get current session ID
         * Useful for integrations and testing
         *
         * @returns {string} Current session ID
         */
        getSessionId: function() {
            return this.sessionId;
        },

        /**
         * Check if form tracking is currently active
         *
         * @returns {boolean} True if actively tracking a form
         */
        isActive: function() {
            return this.isTracking && this.formId !== null;
        }
    };

    /**
     * Initialize tracker when page loads
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            FormTracker.init();
        });
    } else {
        FormTracker.init();
    }

    /**
     * Expose FormTracker as global API
     */
    window.AirinelFormTracker = FormTracker;

    console.log('Airlinel FormTracker loaded');
})();
