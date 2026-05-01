/**
 * Airlinel Booking Tracker
 * Task 4: Frontend AJAX calls for booking funnel tracking
 */

(function() {
    "use strict";

    // Initialize tracker state
    window.airlinel_tracker = window.airlinel_tracker || {
        record_id: null,
        nonce: typeof aba_data !== 'undefined' ? aba_data.nonce : '',
        ajax_url: typeof aba_data !== 'undefined' ? aba_data.ajax_url : '',
    };

    /**
     * Track search submission
     * @param {string} pickup - Pickup location
     * @param {string} dropoff - Dropoff location
     * @param {number} distance - Distance in km
     * @param {string} duration - Duration estimate
     * @param {string} pickup_date - Pickup date (YYYY-MM-DD)
     * @param {string} pickup_time - Pickup time (HH:MM)
     * @param {string} country - Country code
     */
    window.airlinel_track_search = function(pickup, dropoff, distance, duration, pickup_date, pickup_time, country) {
        if (!airlinel_tracker.ajax_url) {
            console.warn('airlinel_tracker: AJAX URL not available');
            return;
        }

        var data = {
            action: 'airlinel_track_search',
            nonce: airlinel_tracker.nonce,
            pickup: pickup || '',
            dropoff: dropoff || '',
            distance: distance || 0,
            duration: duration || '',
            pickup_date: pickup_date || '',
            pickup_time: pickup_time || '',
            country: country || '',
            source_site: window.location.hostname,
            source_language: document.documentElement.lang || '',
        };

        fetch(airlinel_tracker.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(data),
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(result) {
            if (result.success && result.data && result.data.id) {
                airlinel_tracker.record_id = result.data.id;
                console.log('Search tracked with record ID: ' + result.data.id);
            } else {
                console.warn('Failed to track search: ' + (result.data || 'unknown error'));
            }
        })
        .catch(function(error) {
            console.warn('Search tracking request failed: ' + error);
        });
    };

    /**
     * Track vehicle selection
     * @param {string} vehicle_name - Vehicle name/type
     * @param {number} vehicle_price - Vehicle price
     */
    window.airlinel_track_vehicle = function(vehicle_name, vehicle_price) {
        if (!airlinel_tracker.ajax_url || !airlinel_tracker.record_id) {
            if (!airlinel_tracker.record_id) {
                console.warn('airlinel_tracker: No record_id available - search may not have been tracked');
            }
            return;
        }

        var data = {
            action: 'airlinel_track_vehicle',
            nonce: airlinel_tracker.nonce,
            record_id: airlinel_tracker.record_id,
            vehicle_name: vehicle_name || '',
            vehicle_price: vehicle_price || 0,
        };

        fetch(airlinel_tracker.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(data),
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(result) {
            if (result.success) {
                console.log('Vehicle selection tracked for record: ' + airlinel_tracker.record_id);
            } else {
                console.warn('Failed to track vehicle: ' + (result.data || 'unknown error'));
            }
        })
        .catch(function(error) {
            console.warn('Vehicle tracking request failed: ' + error);
        });
    };

    /**
     * Track customer form submission
     * @param {string} name - Customer name
     * @param {string} phone - Customer phone
     * @param {string} email - Customer email
     * @param {string} flight_number - Flight number
     * @param {string} agency_code - Agency code
     * @param {string} notes - Additional notes
     */
    window.airlinel_track_customer_form = function(name, phone, email, flight_number, agency_code, notes) {
        if (!airlinel_tracker.ajax_url || !airlinel_tracker.record_id) {
            if (!airlinel_tracker.record_id) {
                console.warn('airlinel_tracker: No record_id available - search may not have been tracked');
            }
            return;
        }

        var data = {
            action: 'airlinel_track_customer',
            nonce: airlinel_tracker.nonce,
            record_id: airlinel_tracker.record_id,
            customer_name: name || '',
            customer_phone: phone || '',
            customer_email: email || '',
            flight_number: flight_number || '',
            agency_code: agency_code || '',
            notes: notes || '',
        };

        fetch(airlinel_tracker.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(data),
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(result) {
            if (result.success) {
                console.log('Customer form tracked for record: ' + airlinel_tracker.record_id);
            } else {
                console.warn('Failed to track customer form: ' + (result.data || 'unknown error'));
            }
        })
        .catch(function(error) {
            console.warn('Customer form tracking request failed: ' + error);
        });
    };

    /**
     * Track payment completion
     * @param {string} stripe_session_id - Stripe session ID
     */
    window.airlinel_track_payment = function(stripe_session_id) {
        if (!airlinel_tracker.ajax_url || !airlinel_tracker.record_id) {
            if (!airlinel_tracker.record_id) {
                console.warn('airlinel_tracker: No record_id available - search may not have been tracked');
            }
            return;
        }

        var data = {
            action: 'airlinel_track_payment',
            nonce: airlinel_tracker.nonce,
            record_id: airlinel_tracker.record_id,
            stripe_session_id: stripe_session_id || '',
        };

        fetch(airlinel_tracker.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(data),
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(result) {
            if (result.success) {
                console.log('Payment tracked for record: ' + airlinel_tracker.record_id);
            } else {
                console.warn('Failed to track payment: ' + (result.data || 'unknown error'));
            }
        })
        .catch(function(error) {
            console.warn('Payment tracking request failed: ' + error);
        });
    };
})();
