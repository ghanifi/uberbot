/**
 * Airlinel Booking Enhancement - Task 1.5
 * Country selector, currency switching, and agency verification
 * Enhanced for Task 3.5: Regional API Client & Proxy Service
 */

jQuery(document).ready(function($) {
    "use strict";

    // ===== EXCHANGE RATES & CURRENCY SETUP =====
    let exchangeRates = {
        'GBP': 1.0,
        'EUR': 0.86,  // Fallback, will be overridden by server
        'TRY': 32.5,  // Fallback
        'USD': 1.27   // Fallback
    };

    // Currency priority: URL param → session (window.airinelCurrency) → hidden field → default GBP
    const _urlCurrency = new URLSearchParams(window.location.search).get('currency');
    const _validCurrencies = ['GBP', 'EUR', 'TRY', 'USD'];
    let selectedCurrency = (_urlCurrency && _validCurrencies.includes(_urlCurrency))
        ? _urlCurrency
        : (window.airinelCurrency || document.getElementById('selected-currency')?.value || 'GBP');
    console.log('Booking.js currency — URL:', _urlCurrency, '| Session:', window.airinelCurrency, '| Final:', selectedCurrency);
    let bookingCountry = localStorage.getItem('airlinel_country') || 'TR';
    let basePrice = 0;
    let kmCostBase = 0;
    let agencyVerified = false;

    /**
     * Load exchange rates from server settings
     */
    function loadExchangeRates() {
        if (!chauffeur_data || !chauffeur_data.ajax_url) {
            if (window.console && console.warn) {
                console.warn('chauffeur_data not found - exchange rates AJAX may fail');
            }
            return;
        }

        $.ajax({
            url: chauffeur_data.ajax_url,
            type: 'POST',
            data: {
                action: 'get_exchange_rates',
                nonce: $('input[name="_wpnonce"]').val() || ''
            },
            success: function(response) {
                if (response && response.success && response.data) {
                    exchangeRates = response.data;
                    updateCurrencyDisplay();
                }
            },
            error: function() {
                if (window.console && console.warn) {
                    console.warn('Could not load exchange rates, using defaults');
                }
            }
        });
    }

    /**
     * Format price based on selected currency
     * @param {number} priceInGBP - Price in GBP
     * @param {string} currency - Target currency code
     * @returns {string} Formatted price with currency symbol
     */
    window.formatCurrency = function(priceInGBP, currency) {
        const rate = exchangeRates[currency] || 1.0;
        const amount = (priceInGBP * rate).toFixed(2);

        const symbols = {
            'GBP': '£',
            'EUR': '€',
            'TRY': '₺',
            'USD': '$'
        };

        return symbols[currency] + amount;
    };

    /**
     * Initialize country from URL parameter (not user-selectable)
     * Country is determined from pickup/dropoff addresses during search
     */
    function initCountryFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        const urlCountry = urlParams.get('country');
        if (urlCountry) {
            bookingCountry = urlCountry;
        }

        // Store country in hidden field for form submission
        $('#selected-country').val(bookingCountry);
    }

    /**
     * Initialize currency selector (header-based)
     */
    function initCurrencySelector() {
        const $selector = $('#header-currency-selector');
        if (!$selector.length) {
            if (window.console && console.warn) {
                console.warn('Header currency selector not found on page');
            }
            return;
        }

        // NEW SYSTEM: Currency is session-based, set by header-selectors.js
        // Currency selector in header changes via query string (?currency=EUR)
        // Page reloads and backend sets session currency
        // No local change handler needed

        $selector.val(selectedCurrency);
        $('#selected-currency').val(selectedCurrency);
        updateCurrencyDisplay();
    }

    /**
     * Update all currency displays on page
     * NEW SYSTEM: Session-based currency (no localStorage)
     */
    function updateCurrencyDisplay() {
        const rate = exchangeRates[selectedCurrency] || 1.0;

        // Update the selector to match
        const $selector = $('#header-currency-selector');
        if ($selector.length) {
            $selector.val(selectedCurrency);
        }

        // Update exchange rate display
        const rateDisplay = selectedCurrency === 'GBP'
            ? '1 GBP = 1 GBP'
            : `1 GBP = ${rate.toFixed(4)} ${selectedCurrency}`;
        $('#header-exchange-rate').text(rateDisplay);

        // Update all vehicle prices
        $('.vehicle-price').each(function() {
            const basePriceText = $(this).data('base-price');
            if (basePriceText) {
                const price = parseFloat(basePriceText);
                const displayPrice = formatCurrency(price, selectedCurrency);
                $(this).text(displayPrice);
            }
        });

        // Update displayed prices on form
        if (basePrice > 0) {
            const displayPrice = formatCurrency(basePrice, selectedCurrency);
            $('#final-amount-display').text(displayPrice);
        }
    };

    // Make updateCurrencyDisplay globally accessible
    window.updateCurrencyDisplay = updateCurrencyDisplay;

    /**
     * Initialize international phone number input
     * Uses intl-tel-input library for country code selection with flags
     */
    function initPhoneInput() {
        const $phoneInput = $('#passenger-phone');
        if (!$phoneInput.length) {
            return; // Phone input not found (may not be on this page)
        }

        // Initialize intl-tel-input with options
        const iti = window.intlTelInput($phoneInput[0], {
            initialCountry: "gb", // Default to GB
            preferredCountries: ["gb", "tr", "us"],
            separateDialCode: true,
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.3/build/js/utils.min.js",
            formatOnDisplay: true,
            allowDropdown: true,
            autoPlaceholder: "aggressive"
        });

        // Format phone number on blur
        $phoneInput.on('blur', function() {
            if ($(this).val().trim()) {
                $(this).val(iti.getNumber());
            }
        });

        // Store formatted number before form submission
        $('#final-booking-form').on('submit', function(e) {
            const phoneNumber = iti.getNumber(intlTelInputUtils.numberFormat.E164);
            $phoneInput.val(phoneNumber);
        });
    }

    /**
     * Re-fetch vehicles for new country
     */
    function refetchVehicles() {
        const urlParams = new URLSearchParams(window.location.search);
        const distance = urlParams.get('distance');
        const pickup = urlParams.get('pickup');

        if (!distance) return;

        console.log('Re-fetching vehicles for country:', bookingCountry);

        const loadingHTML = '<div class="col-span-full text-center py-20"><i class="fas fa-spinner fa-spin text-4xl text-yellow-500"></i><p class="text-white mt-4">Updating vehicles for <span></span>...</p></div>';
        const $loader = $(loadingHTML);
        $loader.find('span').text(bookingCountry);
        $('#vehicle-results').html($loader);

        $.ajax({
            url: chauffeur_data.ajax_url,
            type: 'POST',
            data: {
                action: 'fetch_vehicles',
                pickup: pickup,
                dropoff: dropoff,
                distance: distance,
                country: bookingCountry
            },
            success: function(response) {
                if (response && response.trim().length > 0) {
                    $('#vehicle-results').html(response);
                } else {
                    const noVehiclesMsg = $('<p class="text-white text-center"></p>').text('No vehicles available for this route in ' + bookingCountry + '.');
                    $('#vehicle-results').html(noVehiclesMsg);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching vehicles:', status, error);
                $('#vehicle-results').html('<p class="text-white text-center">Error loading vehicles. Please try again.</p>');
            }
        });
    }

    /**
     * Initialize agency verification modal
     */
    function initAgencyVerification() {
        const $verifyBtn = $('#agency-verify-btn');
        const $codeInput = $('#agency-code-input');

        if (!$verifyBtn.length) {
            if (window.console && console.warn) {
                console.warn('Agency verification button not found on page');
            }
            return;
        }

        $verifyBtn.on('click', function(e) {
            e.preventDefault();

            const code = $codeInput.val().trim().toUpperCase();

            if (!code) {
                showAgencyError('Please enter an agency code');
                return;
            }

            $verifyBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Verifying...');

            verifyAgency(code);
        });

        // Allow verification on Enter key
        $codeInput.on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $verifyBtn.click();
            }
        });
    }

    /**
     * Verify agency code via AJAX
     * @param {string} code - Agency code to verify
     */
    function verifyAgency(code) {
        $.ajax({
            url: chauffeur_data.ajax_url,
            type: 'POST',
            data: {
                action: 'verify_agency',
                code: code,
                nonce: $('input[name="_wpnonce"]').val() || ''
            },
            success: function(response) {
                if (response && response.success === true && response.data) {
                    const agency = response.data;
                    showAgencySuccess(agency);
                } else if (response && response.data && response.data.message) {
                    showAgencyError(response.data.message);
                } else {
                    showAgencyError('Unknown error verifying agency');
                }
            },
            error: function(xhr, status, error) {
                console.error('Agency verification error:', error);
                showAgencyError('Failed to verify agency: ' + error);
            },
            complete: function() {
                $('#agency-verify-btn').prop('disabled', false).text('Verify');
            }
        });
    }

    /**
     * Show agency success modal
     * @param {object} agency - Agency details
     */
    function showAgencySuccess(agency) {
        const commissionAmount = ((basePrice - kmCostBase) * (agency.commission_percent / 100)).toFixed(2);

        $('#modal-agency-name').text(agency.name);
        $('#modal-commission-percent').text(agency.commission_percent + '%');
        $('#modal-commission-amount').text(formatCurrency(commissionAmount, selectedCurrency));

        // Mark as verified
        agencyVerified = true;
        $('#agency-verified').val('1');

        // Show success modal
        $('#agency-modal').removeClass('hidden');
    }

    /**
     * Show agency error modal
     * @param {string} message - Error message
     */
    function showAgencyError(message) {
        $('#modal-error-message').text(message);
        $('#agency-error-modal').removeClass('hidden');

        agencyVerified = false;
        $('#agency-verified').val('0');
    }

    /**
     * Close agency modals
     */
    window.closeAgencyModal = function() {
        $('#agency-modal').addClass('hidden');
    };

    window.closeAgencyErrorModal = function() {
        $('#agency-error-modal').addClass('hidden');
    };

    /**
     * Display validation error in a styled modal instead of alert()
     */
    function showValidationError(message) {
        const errorModal = `
            <div id="validation-error-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="bg-[#1a1a1a] border border-red-500 rounded-2xl p-8 max-w-md">
                    <h3 class="text-xl font-bold text-red-500 mb-4">Validation Error</h3>
                    <p class="text-gray-300 mb-6">${message}</p>
                    <button onclick="$('#validation-error-modal').remove()" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg">
                        Close
                    </button>
                </div>
            </div>
        `;
        $('body').append(errorModal);
        setTimeout(() => $('#validation-error-modal').fadeOut(() => $('#validation-error-modal').remove()), 5000);
    }

    /**
     * Show booking success modal after payment
     */
    function showBookingSuccess(reservationId, email) {
        const modal = `
            <div id="booking-success-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4">
                <div class="bg-[#1a1a1a] border border-[var(--primary-color)]/40 rounded-3xl max-w-md w-full shadow-2xl text-center p-10">
                    <div class="w-20 h-20 bg-[var(--primary-color)]/20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-check text-[var(--primary-color)] text-3xl"></i>
                    </div>
                    <h2 class="text-2xl font-black text-white mb-3">Booking Confirmed!</h2>
                    <p class="text-gray-400 mb-2">Your reservation <span class="text-[var(--primary-color)] font-bold">#${reservationId}</span> is confirmed.</p>
                    <p class="text-gray-400 text-sm mb-8">A confirmation email will be sent to <span class="text-white">${email}</span>.</p>
                    <a href="/" class="inline-block bg-[var(--primary-color)] text-black font-black px-8 py-4 rounded-xl hover:opacity-90 transition">
                        Return to Home
                    </a>
                </div>
            </div>`;
        $('body').append(modal);
    }

    /**
     * Form submission — Stripe inline card payment
     * Always prevents default GET submission; processes payment via Stripe Elements.
     */
    $('#final-booking-form').on('submit', function(e) {
        e.preventDefault(); // always prevent GET submission

        const agencyCode = $('#agency-code-input').val().trim();

        // Agency code entered but not verified
        if (agencyCode && !agencyVerified) {
            showValidationError('Please verify your agency code before proceeding.');
            return false;
        }

        // Stripe must be initialised
        if (!window._airlinelStripe || !window._airlinelCardElement) {
            showValidationError('Payment system is loading. Please wait a moment and try again.');
            return false;
        }

        const $btn          = $('#pay-button');
        const originalBtnHtml = $btn.html();
        const urlParams     = new URLSearchParams(window.location.search);
        const pickupLocation  = urlParams.get('pickup')  || '';
        const dropoffLocation = urlParams.get('dropoff') || '';

        // Update hidden fields
        $('#selected-country').val(bookingCountry);
        $('#selected-currency').val(selectedCurrency);

        // Track customer form submission
        if (typeof window.airlinel_track_customer_form === 'function') {
            window.airlinel_track_customer_form(
                $('[name="passenger_name"]').val()  || '',
                $('#passenger-phone').val()          || '',
                $('[name="passenger_email"]').val() || '',
                $('[name="flight_number"]').val()   || '',
                agencyCode,
                ''
            );
        }

        // Disable button, show spinner
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Processing...');
        $('#stripe-card-errors').hide().text('');

        // Step 1: Create reservation + get PaymentIntent client_secret
        $.ajax({
            url:  chauffeur_data.ajax_url,
            type: 'POST',
            data: {
                action:           'airlinel_create_booking_payment',
                nonce:            chauffeur_data.nonce,
                passenger_name:   $('[name="passenger_name"]').val(),
                passenger_email:  $('[name="passenger_email"]').val(),
                passenger_phone:  $('#passenger-phone').val(),
                pickup_date:      $('[name="pickup_date"]').val(),
                pickup_time:      $('[name="pickup_time"]').val(),
                flight_number:    $('[name="flight_number"]').val(),
                agency_code:      agencyCode,
                notes:            $('[name="notes"]').val(),
                vehicle_id:       $('#selected-vehicle-id').val(),
                total_price:      $('#selected-vehicle-price').val(),
                currency:         selectedCurrency,
                country:          bookingCountry,
                pickup_location:  pickupLocation,
                dropoff_location: dropoffLocation,
            },
            success: function(response) {
                if (!response.success || !response.data || !response.data.client_secret) {
                    $btn.prop('disabled', false).html(originalBtnHtml);
                    const msg = (response.data && response.data.message)
                        ? response.data.message
                        : 'Booking failed. Please try again.';
                    showValidationError(msg);
                    return;
                }

                const clientSecret  = response.data.client_secret;
                const reservationId = response.data.reservation_id;

                // Step 2: Confirm card payment with Stripe
                window._airlinelStripe.confirmCardPayment(clientSecret, {
                    payment_method: {
                        card: window._airlinelCardElement,
                        billing_details: {
                            name:  $('[name="passenger_name"]').val(),
                            email: $('[name="passenger_email"]').val(),
                        },
                    },
                }).then(function(result) {
                    if (result.error) {
                        $btn.prop('disabled', false).html(originalBtnHtml);
                        $('#stripe-card-errors').show().text(result.error.message);
                    } else if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
                        showBookingSuccess(reservationId, $('[name="passenger_email"]').val());
                    } else {
                        $btn.prop('disabled', false).html(originalBtnHtml);
                        showValidationError('Payment could not be completed. Please try again.');
                    }
                });
            },
            error: function() {
                $btn.prop('disabled', false).html(originalBtnHtml);
                showValidationError('Connection error. Please check your internet and try again.');
            },
        });
    });

    /**
     * Initialize booking page: fetch vehicles and fill summary bar from URL params
     */
    function initBookingPage() {
        if (!window.location.pathname.includes('booking')) return;

        const urlParams = new URLSearchParams(window.location.search);
        const pickup   = urlParams.get('pickup');
        const dropoff  = urlParams.get('dropoff');
        const distance = urlParams.get('distance');
        const duration = urlParams.get('duration');
        const pickupDate = urlParams.get('pickup_date');
        const pickupTime = urlParams.get('pickup_time');

        if (!pickup || !dropoff || !distance) return;

        // Fill summary bar
        $('#summary-pickup').text(pickup);
        $('#summary-dropoff').text(dropoff);
        $('#summary-km').text(parseFloat(distance));
        $('#summary-duration').text(duration || '-- min');

        // Fill date/time fields
        if (pickupDate) {
            $('input[name="pickup_date"]').val(pickupDate);
        }
        if (pickupTime) {
            $('input[name="pickup_time"]').val(pickupTime);
        }

        // Fetch vehicles
        console.log('Fetching vehicles for:', pickup, '→', dropoff, distance, 'km', bookingCountry);
        $('#vehicle-results').html('<div class="col-span-full text-center py-20"><i class="fas fa-spinner fa-spin text-4xl text-yellow-500"></i><p class="text-white mt-4">Finding best vehicles for your route...</p></div>');

        $.ajax({
            url: chauffeur_data.ajax_url,
            type: 'POST',
            data: {
                action: 'fetch_vehicles',
                pickup: pickup,
                dropoff: dropoff,
                distance: distance,
                country: bookingCountry
            },
            success: function(response) {
                if (response && response.trim().length > 0) {
                    $('#vehicle-results').html(response);
                    updateCurrencyDisplay();
                } else {
                    $('#vehicle-results').html('<p class="text-white text-center py-10">No vehicles available for this route.</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching vehicles:', status, error);
                $('#vehicle-results').html('<p class="text-white text-center py-10">Error loading vehicles. Please try again.</p>');
            }
        });
    }

    // ===== INITIALIZATION =====
    loadExchangeRates();
    initCountryFromUrl();
    initBookingPage();
    initCurrencySelector();
    initPhoneInput();
    initAgencyVerification();
});

/**
 * Task 3.5: Search via Regional API Proxy
 * For regional sites: calls local AJAX endpoint which proxies to main site
 * @param {string} pickup - Pickup location
 * @param {string} dropoff - Dropoff location
 * @param {string} country - Country code
 * @param {number} passengers - Number of passengers
 * @param {function} onSuccess - Callback for successful search
 * @param {function} onError - Callback for error
 */
window.searchViaProxy = function(pickup, dropoff, country, passengers, onSuccess, onError) {
    const $ = jQuery;
    // Currency priority: URL param → server session → hidden field → default GBP
    const _validC = ['GBP', 'EUR', 'TRY', 'USD'];
    const _urlC   = new URLSearchParams(window.location.search).get('currency');
    const selectedCurrency = (_urlC && _validC.includes(_urlC))
        ? _urlC
        : (window.airinelCurrency || document.getElementById('selected-currency')?.value || 'GBP');

    if (!chauffeur_data || !chauffeur_data.ajax_url) {
        if (onError) onError('AJAX configuration missing');
        return;
    }

    // Track search - extract parameters from URL
    if (typeof window.airlinel_track_search === 'function') {
        const urlParams = new URLSearchParams(window.location.search);
        const distance = parseFloat(urlParams.get('distance')) || 0;
        const duration = urlParams.get('duration') || '';
        const pickup_date = urlParams.get('pickup_date') || '';
        const pickup_time = urlParams.get('pickup_time') || '';

        window.airlinel_track_search(
            pickup,
            dropoff,
            distance,
            duration,
            pickup_date,
            pickup_time,
            country || 'UK'
        );
    }

    $.ajax({
        url: chauffeur_data.ajax_url,
        type: 'POST',
        data: {
            action: 'airlinel_search',
            pickup: pickup,
            dropoff: dropoff,
            country: country || 'UK',
            passengers: passengers || 1,
            currency: selectedCurrency,
            nonce: $('input[name="_wpnonce"]').val() || ''
        },
        success: function(response) {
            if (response && response.success && response.data) {
                if (onSuccess) onSuccess(response.data);
            } else {
                const errorMsg = (response && response.data && response.data.message)
                    ? response.data.message
                    : 'Unknown error during search';
                if (onError) onError(errorMsg);
            }
        },
        error: function(xhr, status, error) {
            console.error('Proxy search error:', status, error);
            const errorMsg = 'Failed to search transfers. Please try again.';
            if (onError) onError(errorMsg);
        }
    });
};

/**
 * Task 3.5: Create reservation via Regional API Proxy
 * @param {object} reservationData - Reservation details
 * @param {function} onSuccess - Callback for successful creation
 * @param {function} onError - Callback for error
 */
window.createReservationViaProxy = function(reservationData, onSuccess, onError) {
    const $ = jQuery;

    if (!chauffeur_data || !chauffeur_data.ajax_url) {
        if (onError) onError('AJAX configuration missing');
        return;
    }

    $.ajax({
        url: chauffeur_data.ajax_url,
        type: 'POST',
        data: Object.assign({
            action: 'airlinel_create_reservation',
            nonce: $('input[name="_wpnonce"]').val() || ''
        }, reservationData),
        success: function(response) {
            if (response && response.success && response.data) {
                if (onSuccess) onSuccess(response.data);
            } else {
                const errorMsg = (response && response.data && response.data.message)
                    ? response.data.message
                    : 'Failed to create reservation';
                if (onError) onError(errorMsg);
            }
        },
        error: function(xhr, status, error) {
            console.error('Proxy create reservation error:', status, error);
            const errorMsg = 'Failed to create reservation. Please try again.';
            if (onError) onError(errorMsg);
        }
    });
};

/**
 * Task 3.5: Get reservation via Regional API Proxy
 * @param {number} id - Reservation ID
 * @param {function} onSuccess - Callback for successful retrieval
 * @param {function} onError - Callback for error
 */
window.getReservationViaProxy = function(id, onSuccess, onError) {
    const $ = jQuery;

    if (!chauffeur_data || !chauffeur_data.ajax_url) {
        if (onError) onError('AJAX configuration missing');
        return;
    }

    $.ajax({
        url: chauffeur_data.ajax_url,
        type: 'POST',
        data: {
            action: 'airlinel_get_reservation',
            id: id,
            nonce: $('input[name="_wpnonce"]').val() || ''
        },
        success: function(response) {
            if (response && response.success && response.data) {
                if (onSuccess) onSuccess(response.data);
            } else {
                const errorMsg = (response && response.data && response.data.message)
                    ? response.data.message
                    : 'Failed to retrieve reservation';
                if (onError) onError(errorMsg);
            }
        },
        error: function(xhr, status, error) {
            console.error('Proxy get reservation error:', status, error);
            const errorMsg = 'Failed to retrieve reservation. Please try again.';
            if (onError) onError(errorMsg);
        }
    });
};

/**
 * Task 5: Detect website info for form tracker
 * Determines website_id and website_language for analytics tracking
 * Works for main site and all regional sites
 */
function detectWebsiteInfo() {
    const hostname = window.location.hostname;

    // Determine website_id
    let website_id = 'main';
    if (hostname.includes('regional') || !hostname.includes('airlinel')) {
        website_id = hostname.split('.')[0]; // Extract subdomain
        if (website_id === 'www') {
            website_id = hostname.split('.')[1] || 'main';
        }
    }

    // Get language from page (similar to form-tracker logic)
    const language = document.documentElement.getAttribute('data-language') ||
                     document.documentElement.getAttribute('lang') ||
                     (typeof airinelLocale !== 'undefined' ? airinelLocale : 'en');

    return {
        website_id: website_id,
        website_language: language
    };
}

/**
 * Override the original bookingStepTwo to use currency formatting
 * Enhanced for Task 11: Integrated Form Tracker initialization
 * Enhanced for Task 5: Website detection for session tracking
 */
window.bookingStepTwo = function(vehicleId, price, vehicleName, kmCostBaseValue) {
    const $ = jQuery;
    // Currency priority: URL param → server session → hidden field → default GBP
    // (mirrors the same priority chain at the top of this file)
    const _validCurrencies = ['GBP', 'EUR', 'TRY', 'USD'];
    const _urlCur = new URLSearchParams(window.location.search).get('currency');
    const _activeCurrency = (_urlCur && _validCurrencies.includes(_urlCur))
        ? _urlCur
        : (window.airinelCurrency || document.getElementById('selected-currency')?.value || 'GBP');
    const bookingJs = {
        formatCurrency: window.formatCurrency,
        selectedCurrency: _activeCurrency
    };

    const basePrice = parseFloat(price);
    const kmCostBase = parseFloat(kmCostBaseValue) || 0;
    const displayPrice = bookingJs.formatCurrency(basePrice, bookingJs.selectedCurrency);

    // Track vehicle selection
    if (typeof window.airlinel_track_vehicle === 'function') {
        window.airlinel_track_vehicle(vehicleName, basePrice);
    }

    // Form değerlerini doldur
    $('#selected-vehicle-id').val(vehicleId);
    $('#selected-vehicle-price').val(basePrice);
    $('#selected-vehicle-name-display').text(vehicleName);
    $('#final-amount-display').text(displayPrice);

    // Store base price and km cost base for currency conversion and commission calculation
    window.basePrice = basePrice;
    window.kmCostBase = kmCostBase;

    // Task 5: Initialize form tracking when vehicle is selected
    if (typeof window.AirinelFormTracker !== 'undefined' && typeof window.AirinelFormTracker.startTracking === 'function') {
        // Detect website info for analytics
        const websiteInfo = detectWebsiteInfo();

        // Extract form data from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const formData = {
            pickup: urlParams.get('pickup') || $('#pickup-location').val() || '',
            dropoff: urlParams.get('dropoff') || $('#dropoff-location').val() || '',
            distance: parseFloat(urlParams.get('distance') || 0),
            country: urlParams.get('country') || localStorage.getItem('airlinel_country') || 'GB',
            currency: urlParams.get('currency') || window.airinelCurrency || 'GBP',
            website_id: websiteInfo.website_id,
            website_language: websiteInfo.website_language
        };

        // Start tracking the booking form
        if (formData.pickup && formData.dropoff) {
            window.AirinelFormTracker.startTracking(formData);

            // Get session_id from form tracker
            const sessionId = window.AirinelFormTracker.getSessionId();
            if (sessionId) {
                // Store in window for other scripts
                window.airinelSessionId = sessionId;
                console.log('Session ID:', sessionId);
            }
        }
    }

    // Animasyonlu geçiş
    $('#step-1-vehicles').fadeOut(300, function() {
        $('#step-2-form').removeClass('hidden').attr('style', 'display: block !important; opacity: 1 !important;');

        // Sayfayı forma kaydır
        $('html, body').animate({
            scrollTop: $("#step-2-form").offset().top - 80
        }, 800);

        // Initialise Stripe Elements for inline card payment
        const $cardEl      = $('#stripe-card-element');
        const $cardErr     = $('#stripe-card-errors');
        const stripeReady  = $('[data-stripe-ready]').attr('data-stripe-ready');

        // Show loading placeholder while we check
        $cardEl.html('<p style="color:#9ca3af;font-size:13px;padding:4px 0;"><i class="fa fa-spinner fa-spin mr-2"></i>Loading payment form...</p>');

        if (typeof Stripe === 'undefined') {
            // stripe.js failed to load (network/CSP issue)
            console.error('Stripe.js not loaded — check network/CSP');
            $cardEl.html('<p style="color:#f87171;font-size:14px;padding:4px 0;"><i class="fa fa-exclamation-triangle mr-2"></i>Payment system unavailable. Please refresh the page.</p>');
        } else if (stripeReady === '0' || !chauffeur_data || !chauffeur_data.stripe_pub_key) {
            // No publishable key configured in admin settings
            console.warn('Stripe publishable key not configured. Go to: WP Admin → Airlinel Settings → Stripe Publishable Key');
            $cardEl.html('<p style="color:#fbbf24;font-size:13px;padding:4px 0;"><i class="fa fa-cog mr-2"></i>Payment not configured. Please contact support or set Stripe keys in Airlinel Settings.</p>');
        } else if (!window._airlinelStripe) {
            try {
                window._airlinelStripe      = Stripe(chauffeur_data.stripe_pub_key);
                const elements              = window._airlinelStripe.elements();
                window._airlinelCardElement = elements.create('card', {
                    style: {
                        base: {
                            color:           '#111827',
                            fontFamily:      '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                            fontSize:        '16px',
                            fontSmoothing:   'antialiased',
                            '::placeholder': { color: '#9ca3af' },
                        },
                        invalid: {
                            color:     '#dc2626',
                            iconColor: '#dc2626',
                        },
                    },
                });
                $cardEl.empty(); // clear loading placeholder
                window._airlinelCardElement.mount('#stripe-card-element');

                // Real-time card error feedback
                window._airlinelCardElement.on('change', function(event) {
                    if (event.error) {
                        $cardErr.show().text(event.error.message);
                    } else {
                        $cardErr.hide().text('');
                    }
                });

                console.log('Stripe Elements mounted successfully');
            } catch (stripeErr) {
                console.error('Stripe initialisation error:', stripeErr);
                $cardEl.html('<p style="color:#f87171;font-size:14px;padding:4px 0;"><i class="fa fa-exclamation-triangle mr-2"></i>Payment system error. Please refresh the page.</p>');
            }
        }
    });
};
