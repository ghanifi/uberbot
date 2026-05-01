jQuery(document).ready(function($) {
    "use strict";

    // --- 1. GOOGLE PLACES & MESAFE AYARLARI ---
    let autocompletePickup, autocompleteDropoff;
    // keep track of which country to price for, default to Turkey
    let bookingCountry = 'TR';

    function initAutocomplete() {
        const pickupInput = document.getElementById('pickup-location');
        const dropoffInput = document.getElementById('dropoff-location');

        // Sadece inputlar varsa çalış (Ana Sayfa kontrolü)
        if (!pickupInput || !dropoffInput) return;

        const options = {
            fields: ["formatted_address", "geometry", "name"],
            types: [] // Tüm yerleri (otel, havalimanı, adres) getirir
        };

        autocompletePickup = new google.maps.places.Autocomplete(pickupInput, options);
        autocompleteDropoff = new google.maps.places.Autocomplete(dropoffInput, options);

        autocompletePickup.addListener('place_changed', calculateDistance);
        autocompleteDropoff.addListener('place_changed', calculateDistance);
    }

    function calculateDistance() {
        const origin = $('#pickup-location').val();
        const destination = $('#dropoff-location').val();

        if (origin.length > 5 && destination.length > 5) {
            const service = new google.maps.DistanceMatrixService();
            service.getDistanceMatrix({
                origins: [origin],
                destinations: [destination],
                travelMode: google.maps.TravelMode.DRIVING,
            }, function(response, status) {
                if (status === 'OK') {
                    const element = response.rows[0].elements[0];
                    if (element.status === 'OK') {
                        const distanceKm = (element.distance.value / 1000).toFixed(1);
                        const durationText = element.duration.text;

                        // Gizli inputlara ve ekrana bas
                        $('#distance-value').val(distanceKm);
                        $('#duration-value').val(durationText);
                        $('#display-distance').text(distanceKm);
                        $('#display-duration').text(durationText);
                        
                        // determine country code from pickup place (fallback to TR)
                        if (autocompletePickup) {
                            const place = autocompletePickup.getPlace();
                            if (place && place.address_components) {
                                for (const comp of place.address_components) {
                                    if (comp.types && comp.types.indexOf('country') !== -1) {
                                        bookingCountry = (comp.short_name || comp.long_name || 'TR').toUpperCase();
                                        // Google returns GB for United Kingdom; map it to our UK code
                                        if (bookingCountry === 'GB') {
                                            bookingCountry = 'UK';
                                        }
                                        if (bookingCountry !== 'TR' && bookingCountry !== 'UK') {
                                            bookingCountry = 'TR';
                                        }
                                        break;
                                    }
                                }
                            }
                        }

                        // Özet panelini göster
                        $('#trip-summary').fadeIn().css("display", "block").removeClass('hidden');
                    }
                }
            });
        }
    }

    // Google kütüphanesi yüklendiyse başlat
    if (typeof google !== 'undefined' && google.maps.places) {
        initAutocomplete();
    }

    // --- 2. ANA SAYFA: ARAMA & YÖNLENDİRME ---
    $('#search-button').on('click', function(e) {
        e.preventDefault();
        
        const distance = $('#distance-value').val();
        const pickup = $('#pickup-location').val();
        const dropoff = $('#dropoff-location').val();
        const duration = $('#duration-value').val();
        const pickupDate = $('#pickup-date-ap').val();
        const pickupTime = $('#pickup-time-ap').val();

        if (!distance || distance == 0) {
            alert("Please select pickup and drop-off locations from the list.");
            return;
        }

        // Verileri URL parametresi olarak gönder
        const selectedCurrency = window.airinelCurrency || 'GBP';
        let bookingUrl = "/booking/?" +
            "pickup=" + encodeURIComponent(pickup) +
            "&dropoff=" + encodeURIComponent(dropoff) +
            "&distance=" + distance +
            "&duration=" + encodeURIComponent(duration) +
            "&country=" + encodeURIComponent(bookingCountry) +
            "&currency=" + encodeURIComponent(selectedCurrency);

        // Eğer tarih ve saat varsa, URL'ye ekle
        if (pickupDate) {
            bookingUrl += "&pickup_date=" + encodeURIComponent(pickupDate);
        }
        if (pickupTime) {
            bookingUrl += "&pickup_time=" + encodeURIComponent(pickupTime);
        }

        window.location.href = bookingUrl;
    });

    // --- 3. REZERVASYON SAYFASI: ARAÇLARI OTOMATİK GETİR ---
    if (window.location.pathname.includes('booking')) {
        const urlParams = new URLSearchParams(window.location.search);
        const pickup = urlParams.get('pickup');
        const dropoff = urlParams.get('dropoff');
        const distance = urlParams.get('distance');
        const duration = urlParams.get('duration');
        const pickupDate = urlParams.get('pickup_date');
        const pickupTime = urlParams.get('pickup_time');

        if (pickup && dropoff && distance) {
            // Üst barı doldur
            $('#summary-pickup').text(pickup);
            $('#summary-dropoff').text(dropoff);
            $('#summary-km').text(distance);
            $('#summary-duration').text(duration || '-- min');

            // Tespit edilen ülkeyi pikap lokasyonundan güçlendir
            function detectCountryFromLocation(location) {
                if (!location) return 'TR';
                const loc = location.toUpperCase();
                
                // UK indicators
                const ukKeywords = ['UK', 'LONDON', 'ENGLAND', 'SCOTLAND', 'WALES', 'LTN', 'STN', 'LHR', 'LGW', 'STN', 'LCY', 'MAN', 'BHX', 'EDI', 'GLA', 'BHD', 'EMA', 'LBA', 'PIK', 'BRS'];
                for (let keyword of ukKeywords) {
                    if (loc.includes(keyword)) return 'UK';
                }
                
                // TR indicators  
                const trKeywords = ['TURKEY', 'TÜRKİYE', 'TÜRKIYE', 'TR', 'ANTALYA', 'ISTANBUL', 'ANKARA', 'IZMIR', 'KEMER', 'BODRUM', 'MARMARIS', 'DALYAN', 'AYT', 'IST', 'BJJ'];
                for (let keyword of trKeywords) {
                    if (loc.includes(keyword)) return 'TR';
                }
                
                return 'TR'; // varsayılan
            }

            const detectedCountry = detectCountryFromLocation(pickup);
            
            // Form alanlarını doldur ve readonly yap (eğer URL'den parametreler geldi ise)
            if (pickupDate) {
                const $pickupDateField = $('input[name="pickup_date"]');
                $pickupDateField.val(pickupDate).prop('readonly', true).css('opacity', '0.7');
            }
            
            if (pickupTime) {
                const $pickupTimeField = $('input[name="pickup_time"]');
                $pickupTimeField.val(pickupTime).prop('readonly', true).css('opacity', '0.7');
            }

            // AJAX ile Araç Listesini Çek
            $('#vehicle-results').html('<div class="col-span-full text-center py-20"><i class="fas fa-spinner fa-spin text-4xl text-yellow-500"></i><p class="text-white mt-4">Finding best vehicles for your route...</p></div>');
            
            // Tespit edilen ülkeyi kullan
            console.log('sending fetch_vehicles', { pickup: pickup, dropoff: dropoff, distance: distance, country: detectedCountry });
            $.ajax({
                url: chauffeur_data.ajax_url,
                type: 'POST',
                data: {
                    action: 'fetch_vehicles',
                    pickup: pickup,
                    dropoff: dropoff,
                    distance: distance,
                    country: detectedCountry
                },
                success: function(response) {
                    console.log('AJAX success:', response.substring(0, 100));
                    if (!response || response.trim().length === 0) {
                        $('#vehicle-results').html('<p class="text-white text-center">Server returned empty response. Please check admin settings.</p>');
                    } else {
                        $('#vehicle-results').html(response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error, xhr.responseText);
                    $('#vehicle-results').html('<p class="text-white text-center">Error loading vehicles (Status: ' + xhr.status + '). Please try again.</p>');
                }
            });
        }
    }

    // --- 4. STRIPE ÖDEME FORMU İŞLEME ---
    // Stripe anahtarını WP Admin'den veya direkt buraya yazarak kontrol et
    const stripe = Stripe('pk_live_51N8Q54Lq9UAdTGTeIP1jY8gCqIps5uDKRtR0m7WsWp1uDZML1ql5okf1My9a9L965RNJ7FwLxHwVZG7IgAEV3phM00IB6sY8qS');

    $('#final-booking-form').on('submit', function(e) {
        e.preventDefault();

        const $btn = $('#pay-button');
        const formData = $(this).serialize();
        const vehicleName = $('#selected-vehicle-name-display').text();
        const agencyCode = $('input[name="agency_code"]').val().toUpperCase();

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Connecting to Secure Payment...');

        $.post(chauffeur_data.ajax_url, formData + '&action=create_stripe_session&vehicle_name=' + encodeURIComponent(vehicleName) + '&agency_code=' + encodeURIComponent(agencyCode), function(response) {
            if (response.success) {
                // Task 12: Track form completion before redirecting to Stripe
                if (window.AirinelFormTracker && typeof window.AirinelFormTracker.isActive === 'function' && window.AirinelFormTracker.isActive()) {
                    window.AirinelFormTracker.markFormCompleted();
                }

                // Stripe Checkout sayfasına yönlendir
                stripe.redirectToCheckout({ sessionId: response.data.id });
            } else {
                alert('Payment Error: ' + response.data.message);
                $btn.prop('disabled', false).text('PAY NOW');
            }
        });
    });
});

// --- 5. GLOBAL FONKSİYONLAR (Döngü Dışında) ---

/**
 * Araç Seçimi: Araçları gizler, formu doldurur ve gösterir.
 */
window.bookingStepTwo = function(vehicleId, price, vehicleName) {
    const $ = jQuery;
    console.log("Vehicle Selected:", vehicleName);

    // Form değerlerini doldur
    $('#selected-vehicle-id').val(vehicleId);
    $('#selected-vehicle-price').val(price);
    $('#selected-vehicle-name-display').text(vehicleName);
    $('#final-amount-display').text('£' + parseFloat(price).toFixed(2));

    // Animasyonlu geçiş
    $('#step-1-vehicles').fadeOut(300, function() {
        // Formu görünür yap ve CSS kilidini aç
        $('#step-2-form').removeClass('hidden').attr('style', 'display: block !important; opacity: 1 !important;');
        
        // Sayfayı forma kaydır
        $('html, body').animate({
            scrollTop: $("#step-2-form").offset().top - 80
        }, 800);
    });
};

/**
 * Agency Code Validation and Discount Calculation
 */
window.validateAgencyCode = function() {
    const $ = jQuery;
    const agencyCode = $('input[name="agency_code"]').val().trim().toUpperCase();
    const basePrice = parseFloat($('#selected-vehicle-price').val());
    
    if (!agencyCode) {
        // No code, use base price
        $('#final-amount-display').text('£' + basePrice.toFixed(2));
        return;
    }
    
    // Check agency code via AJAX
    $.post(chauffeur_data.ajax_url, {
        action: 'validate_agency_code',
        code: agencyCode,
        security: $('input[name="_wpnonce"]').val() || ''
    }, function(response) {
        if (response.success) {
            const discount = response.data.discount;
            const discountedPrice = basePrice * (1 - (discount / 100));
            $('#final-amount-display').text('£' + discountedPrice.toFixed(2));
            $('#selected-vehicle-price').val(discountedPrice);
            
            // Show discount message
            if (!$('#discount-info').length) {
                $('input[name="agency_code"]').after(
                    '<div id="discount-info" class="text-green-400 text-xs mt-2">✓ ' + discount + '% discount applied</div>'
                );
            }
        } else {
            $('#final-amount-display').text('£' + basePrice.toFixed(2));
            $('input[name="agency_code"]').val('');
            alert('Invalid agency code');
        }
    });
};

// Listen for agency code input
jQuery(document).ready(function($) {
    $(document).on('blur', 'input[name="agency_code"]', function() {
        if ($(this).val().trim()) {
            validateAgencyCode();
        }
    });
});






// booking.js dosyasının en başını şu şekilde başlat:
(function($) { 
    "use strict";

    $(document).ready(function() {
        
        // --- AnyPicker Başlatma ---
        if (typeof $.fn.AnyPicker !== 'undefined') {
            
            $("#pickup-date-ap").AnyPicker({
                mode: "datetime",
                dateTimeFormat: "dd MMMM yyyy",
                theme: "iOS",
                rowsNavigation: "scroller",
                layout: "popup",
                components: [{ component: "date", label: "Date" }]
            });

            $("#pickup-time-ap").AnyPicker({
                mode: "datetime",
                dateTimeFormat: "HH:mm",
                theme: "iOS",
                rowsNavigation: "scroller",
                layout: "popup",
                components: [{ component: "time", label: "Time" }]
            });
            
        }

        // ... Diğer tüm kodların ($ işaretini artık güvenle kullanabilirsin) ...
        
    });

})(jQuery); // jQuery nesnesini içeriye $ olarak güvenli bir şekilde aktarır