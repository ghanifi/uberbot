<?php /* Template Name: Booking Page */ get_header(); ?>

<section class="booking-flow py-20 bg-gray-900 min-h-screen">
    <div class="container mx-auto px-4">
        
<div id="booking-summary-bar" class="relative overflow-hidden bg-[#1a1a1a] border border-white/5 rounded-[2rem] mb-12 shadow-2xl">
    <div class="absolute top-0 right-0 w-64 h-64 bg-[var(--primary-color)] opacity-[0.03] blur-[100px] -mr-32 -mt-32"></div>

    <div class="relative flex flex-col lg:flex-row items-stretch">
        <div class="flex-grow p-8 lg:p-10 flex items-center gap-6 border-b lg:border-b-0 lg:border-r border-white/5">
            <div class="hidden sm:flex flex-col items-center gap-2">
                <div class="w-3 h-3 rounded-full border-2 border-[var(--primary-color)]"></div>
                <div class="w-0.5 h-12 border-l border-dashed border-white/20"></div>
                <div class="w-3 h-3 bg-[var(--primary-color)] rounded-full"></div>
            </div>
            
            <div class="flex-grow space-y-4">
                <div class="group">
                    <span class="text-[10px] uppercase tracking-[0.2em] text-gray-500 font-bold block mb-1"><?php _e('Pickup Location', 'airlinel-theme'); ?></span>
                    <div id="summary-pickup" class="text-white text-sm md:text-base font-medium line-clamp-1 opacity-90 group-hover:opacity-100 transition-opacity">
                        </div>
                </div>
                <div class="group">
                    <span class="text-[10px] uppercase tracking-[0.2em] text-[var(--primary-color)] font-bold block mb-1"><?php _e('Destination', 'airlinel-theme'); ?></span>
                    <div id="summary-dropoff" class="text-white text-sm md:text-base font-medium line-clamp-1 opacity-90 group-hover:opacity-100 transition-opacity">
                        </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 lg:flex lg:w-auto divide-x divide-white/5">
            <div class="p-8 lg:px-12 flex flex-col justify-center items-center text-center bg-white/[0.02]">
                <span class="text-[10px] uppercase tracking-[0.2em] text-gray-500 font-bold mb-2"><?php _e('Distance', 'airlinel-theme'); ?></span>
                <div class="flex items-baseline gap-1">
                    <span id="summary-km" class="text-3xl font-black text-white tracking-tighter">0</span>
                    <span class="text-[var(--primary-color)] font-bold text-xs">KM</span>
                </div>
            </div>
            <div class="p-8 lg:px-12 flex flex-col justify-center items-center text-center">
                <span class="text-[10px] uppercase tracking-[0.2em] text-gray-500 font-bold mb-2"><?php _e('Est. Time', 'airlinel-theme'); ?></span>
                <div id="summary-duration" class="text-xl font-bold text-white whitespace-nowrap">
                    -- min
                </div>
            </div>
        </div>
    </div>
</div>

        <div id="step-1-vehicles">
            <h2 class="text-3xl font-bold text-white mb-8"><?php _e('Select Your Vehicle', 'airlinel-theme'); ?></h2>
            <div id="vehicle-results" class="container mx-auto px-4 max-w-[1600px]">
                <!-- AJAX results will be injected here -->
            </div>
        </div>

        <div id="step-2-form" style="display: none;" class="min-h-[400px]">
    <button onclick="window.location.reload()" class="flex items-center gap-2 text-[var(--primary-color)] mb-8 hover:underline font-bold">
        <i class="fa fa-arrow-left"></i> <?php _e('Change Vehicle', 'airlinel-theme'); ?>
    </button>

    <div class="max-w-4xl mx-auto bg-[#1a1a1a] border border-white/10 p-8 rounded-3xl shadow-2xl">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-12 h-12 bg-[var(--primary-color)] rounded-full flex items-center justify-center text-black font-bold text-xl">2</div>
            <h2 class="text-3xl font-bold text-white"><?php _e('Booking Details', 'airlinel-theme'); ?></h2>
        </div>

        <form id="final-booking-form" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <label class="block">
                        <span class="text-gray-400 text-xs uppercase mb-2 block"><?php _e('Full Name', 'airlinel-theme'); ?></span>
                        <input type="text" name="passenger_name" required class="w-full bg-white/5 border border-white/10 rounded-xl p-4 text-white focus:border-[var(--primary-color)] outline-none transition-all">
                    </label>
                    <label class="block">
                        <span class="text-gray-400 text-xs uppercase mb-2 block"><?php _e('Email Address', 'airlinel-theme'); ?></span>
                        <input type="email" name="passenger_email" required class="w-full bg-white/5 border border-white/10 rounded-xl p-4 text-white focus:border-[var(--primary-color)] outline-none transition-all">
                    </label>
                    <label class="block">
                        <span class="text-gray-400 text-xs uppercase mb-2 block"><?php _e('Phone Number', 'airlinel-theme'); ?></span>
                        <input type="tel" id="passenger-phone" name="passenger_phone" required placeholder="<?php esc_attr_e('+44 7911 123456', 'airlinel-theme'); ?>" class="w-full bg-white/5 border border-white/10 rounded-xl p-4 text-white focus:border-[var(--primary-color)] outline-none transition-all">
                    </label>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <label class="block">
                            <span class="text-gray-400 text-xs uppercase mb-2 block"><?php _e('Pickup Date', 'airlinel-theme'); ?></span>
                            <input type="date" name="pickup_date" required readonly class="w-full bg-white/5 border border-white/10 rounded-xl p-4 text-white focus:border-[var(--primary-color)] outline-none transition-all opacity-60 cursor-not-allowed" style="pointer-events: none;">
                        </label>
                        <label class="block">
                            <span class="text-gray-400 text-xs uppercase mb-2 block"><?php _e('Pickup Time', 'airlinel-theme'); ?></span>
                            <input type="time" name="pickup_time" required readonly class="w-full bg-white/5 border border-white/10 rounded-xl p-4 text-white focus:border-[var(--primary-color)] outline-none transition-all opacity-60 cursor-not-allowed" style="pointer-events: none;">
                        </label>
                    </div>
                    <label class="block">
                        <span class="text-gray-400 text-xs uppercase mb-2 block"><?php _e('Flight Number', 'airlinel-theme'); ?></span>
                        <input type="text" name="flight_number" placeholder="<?php esc_attr_e('e.g. TK1234', 'airlinel-theme'); ?>" class="w-full bg-white/5 border border-white/10 rounded-xl p-4 text-white focus:border-[var(--primary-color)] outline-none transition-all">
                    </label>
                    <label class="block">
                        <span class="text-gray-400 text-xs uppercase mb-2 block"><?php _e('Agency Code (Optional)', 'airlinel-theme'); ?></span>
                        <div class="flex gap-2">
                            <input type="text" name="agency_code" id="agency-code-input" placeholder="<?php esc_attr_e('Enter your agency code', 'airlinel-theme'); ?>" class="flex-grow bg-white/5 border border-white/10 rounded-xl p-4 text-white focus:border-[var(--primary-color)] outline-none transition-all uppercase">
                            <button type="button" id="agency-verify-btn" class="bg-[var(--primary-color)] hover:bg-white/10 text-black hover:text-white px-6 py-4 rounded-xl font-bold transition-all text-xs uppercase"><?php _e('Verify', 'airlinel-theme'); ?></button>
                        </div>
                        <small class="text-gray-500 text-xs mt-2 block"><?php _e('Leave blank if you don\'t have one', 'airlinel-theme'); ?></small>
                        <input type="hidden" id="agency-verified" name="agency_verified" value="0">
                    </label>
                    <label class="block">
                        <span class="text-gray-400 text-xs uppercase mb-2 block"><?php _e('Extra Notes', 'airlinel-theme'); ?></span>
                        <textarea name="notes" class="w-full bg-white/5 border border-white/10 rounded-xl p-4 text-white focus:border-[var(--primary-color)] outline-none transition-all h-[108px]"></textarea>
                    </label>
                </div>
            </div>

            <input type="hidden" id="selected-vehicle-id" name="vehicle_id">
            <input type="hidden" id="selected-vehicle-price" name="total_price">
            <input type="hidden" id="selected-country" name="country" value="TR">
            <!-- NEW SYSTEM: Currency from session -->
            <input type="hidden" id="selected-currency" name="currency" value="<?php
                require_once get_template_directory() . '/includes/class-currency-session.php';
                echo esc_attr(Airlinel_Currency_Session::get_currency());
            ?>">

            <!-- Stripe Payment Details -->
            <div class="mt-8 p-6 bg-white/5 border border-white/10 rounded-2xl" data-stripe-ready="<?php echo Airlinel_Settings_Manager::get('airlinel_stripe_pub_key') ? '1' : '0'; ?>">
                <div class="flex items-center gap-3 mb-5">
                    <i class="fa fa-lock text-[var(--primary-color)]"></i>
                    <h3 class="text-white font-bold text-lg"><?php _e('Payment Details', 'airlinel-theme'); ?></h3>
                    <div class="ml-auto flex items-center gap-2 text-gray-400 text-xs">
                        <i class="fa fa-shield-alt text-green-400"></i>
                        <span><?php _e('Secured by Stripe', 'airlinel-theme'); ?></span>
                    </div>
                </div>
                <div id="stripe-card-element" class="bg-white/5 border border-white/10 rounded-xl p-4 min-h-[52px] transition-all focus-within:border-[var(--primary-color)]"></div>
                <div id="stripe-card-errors" class="mt-3 text-red-400 text-sm" style="display:none;"></div>
            </div>

            <div class="mt-6 p-6 bg-[var(--primary-color)] rounded-2xl flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-black text-center md:text-left">
                    <span class="text-xs uppercase font-bold opacity-70"><?php _e('Selected Vehicle', 'airlinel-theme'); ?></span>
                    <div class="text-xl font-black" id="selected-vehicle-name-display">-</div>
                </div>
                <button type="submit" id="pay-button" class="w-full md:w-auto bg-black text-white px-12 py-5 rounded-xl font-black text-lg hover:scale-105 transition-transform shadow-xl flex items-center justify-center gap-3">
                    <i class="fa fa-lock text-xs opacity-60"></i>
                    <span><?php _e('PAY', 'airlinel-theme'); ?> <span id="final-amount-display">£0</span> <?php _e('NOW', 'airlinel-theme'); ?></span>
                </button>
            </div>
        </form>
    </div>
</div>
        </div>

    </div>
</section>

<!-- Agency Verification Modal -->
<div id="agency-modal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4">
    <div class="bg-[#1a1a1a] border border-white/10 rounded-3xl max-w-md w-full shadow-2xl">
        <div class="p-8 border-b border-white/10">
            <h3 class="text-2xl font-bold text-white flex items-center gap-3">
                <i class="fa fa-check-circle text-[var(--primary-color)]"></i>
                <?php _e('Agency Verified', 'airlinel-theme'); ?>
            </h3>
        </div>
        <div class="p-8 space-y-6">
            <div>
                <span class="text-gray-400 text-xs uppercase block mb-2 font-bold"><?php _e('Agency Name', 'airlinel-theme'); ?></span>
                <p id="modal-agency-name" class="text-white text-lg font-bold">-</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white/5 border border-white/10 rounded-xl p-4">
                    <span class="text-gray-400 text-xs uppercase block mb-2 font-bold"><?php _e('Commission %', 'airlinel-theme'); ?></span>
                    <p id="modal-commission-percent" class="text-[var(--primary-color)] text-2xl font-black">-</p>
                </div>
                <div class="bg-white/5 border border-white/10 rounded-xl p-4">
                    <span class="text-gray-400 text-xs uppercase block mb-2 font-bold"><?php _e('Your Savings', 'airlinel-theme'); ?></span>
                    <p id="modal-commission-amount" class="text-green-400 text-2xl font-black">-</p>
                </div>
            </div>
            <div class="bg-green-400/10 border border-green-400/30 rounded-xl p-4 text-center">
                <p class="text-green-400 text-sm font-bold"><?php _e('Commission has been applied to your booking', 'airlinel-theme'); ?></p>
            </div>
        </div>
        <div class="p-8 border-t border-white/10">
            <button type="button" onclick="closeAgencyModal()" class="w-full bg-[var(--primary-color)] text-black py-4 rounded-xl font-black hover:opacity-90 transition">
                <?php _e('Close', 'airlinel-theme'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Agency Error Modal -->
<div id="agency-error-modal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4">
    <div class="bg-[#1a1a1a] border border-white/10 rounded-3xl max-w-md w-full shadow-2xl">
        <div class="p-8 border-b border-white/10">
            <h3 class="text-2xl font-bold text-white flex items-center gap-3">
                <i class="fa fa-times-circle text-red-400"></i>
                <?php _e('Invalid Agency Code', 'airlinel-theme'); ?>
            </h3>
        </div>
        <div class="p-8">
            <p id="modal-error-message" class="text-gray-300 text-base"><?php _e('The agency code you entered could not be verified.', 'airlinel-theme'); ?></p>
        </div>
        <div class="p-8 border-t border-white/10">
            <button type="button" onclick="closeAgencyErrorModal()" class="w-full bg-[var(--primary-color)] text-black py-4 rounded-xl font-black hover:opacity-90 transition">
                <?php _e('Try Again', 'airlinel-theme'); ?>
            </button>
        </div>
    </div>
</div>

<style>
/* ── intl-tel-input — dark booking form ── */
.iti { position: relative; display: flex; align-items: center; width: 100%; }

/* White pill container for flag + dial code */
.iti__flag-container {
    position: absolute !important;
    left: 0;
    top: 0;
    bottom: 0;
    display: flex !important;
    align-items: center !important;
}
.iti__selected-flag {
    background: #fff !important;
    border-radius: 0.75rem 0 0 0.75rem !important;
    border-right: 1px solid rgba(255,255,255,0.15) !important;
    padding: 0 10px !important;
    height: 100% !important;
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    cursor: pointer !important;
}
.iti__selected-flag:hover { background: #f3f4f6 !important; }

/* Flags: let the CDN sprite show properly */
.iti__flag { display: inline-block; }

/* Dial code in white container — must be dark */
.iti__selected-dial-code {
    color: #111827 !important;
    font-weight: 600;
    font-size: 13px;
}

/* The phone text input itself */
.iti--separate-dial-code .iti__input,
#passenger-phone.iti__input,
.iti__input {
    width: 100% !important;
    background: rgba(255,255,255,0.05) !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    color: #fff !important;
    border-radius: 0.75rem !important;
    padding: 1rem !important;
    padding-left: 90px !important;   /* room for flag+dial code */
    outline: none;
}
.iti__input:focus { border-color: var(--primary-color) !important; }

/* Country dropdown */
.iti__country-list {
    background: #fff !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 0.75rem !important;
    box-shadow: 0 8px 24px rgba(0,0,0,0.18) !important;
    max-height: 260px !important;
    z-index: 9999 !important;
}
.iti__search-input {
    padding: 8px 12px !important;
    border: none !important;
    border-bottom: 1px solid #e5e7eb !important;
    font-size: 13px !important;
    width: 100% !important;
    outline: none !important;
}
.iti__country-list .iti__country {
    color: #374151 !important;
    padding: 8px 12px !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}
.iti__country-list .iti__country:hover,
.iti__country-list .iti__highlight {
    background: #f9fafb !important;
}
.iti__country-list .iti__dial-code { color: #6b7280 !important; font-size: 12px; }
.iti__country-list .iti__country-name { color: #111827 !important; font-size: 13px; }

/* ── Stripe card element — white background ── */
#stripe-card-element {
    background: #fff !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 0.75rem !important;
    padding: 14px 16px !important;
    min-height: 52px;
    transition: border-color 0.2s;
}
#stripe-card-element.StripeElement--focus {
    border-color: var(--primary-color) !important;
    box-shadow: 0 0 0 3px rgba(204,68,82,0.12) !important;
}
</style>

<?php get_footer(); ?>