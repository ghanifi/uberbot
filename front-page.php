<?php
get_header();

// Initialize the Homepage Manager for controlling section visibility
$homepage_mgr = new Airlinel_Homepage_Manager();
?>

<?php if ($homepage_mgr->get_section_visibility('booking_cta')) : ?>
<?php $custom_content = $homepage_mgr->get_section_content('booking_cta'); ?>
<?php if (!empty($custom_content)) : echo $custom_content; else: ?>
<section class="code-section relative min-h-screen flex items-center overflow-hidden bg-gradient-to-br from-white via-gray-50 to-gray-100">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-[var(--primary-color)] to-transparent animate-gradient"></div>
    </div>
    <div class="relative z-10 w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-32 lg:py-20">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center min-h-[85vh]">
            <div class="order-1">
                <div class="mb-6">
                    <p class="text-sm font-semibold text-[var(--primary-color)] mb-2 tracking-wide uppercase"><?php _e('Instant Booking', 'airlinel-theme'); ?></p>
                    <h1 class="font-[var(--font-family-heading)] text-4xl sm:text-5xl lg:text-6xl font-bold text-[var(--dark-text-color)] mb-4 leading-tight">
                        <?php _e('London Airport Transfer', 'airlinel-theme'); ?><br><span class="text-[var(--primary-color)]"><?php _e('& Chauffeur Service', 'airlinel-theme'); ?></span>
                    </h1>
                </div>
                <div class="bg-white/90 backdrop-blur-2xl rounded-3xl shadow-2xl border border-white/60 p-6 sm:p-8">
                    <form class="space-y-5">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-[var(--dark-text-color)] mb-2 uppercase tracking-wide"><?php _e('Pickup', 'airlinel-theme'); ?></label>
                                <input type="text" id="pickup-location" placeholder="<?php esc_attr_e('Airport, hotel, or address', 'airlinel-theme'); ?>" class="w-full px-5 py-4 rounded-xl border-2 border-gray-200 outline-none focus:border-[var(--primary-color)]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[var(--dark-text-color)] mb-2 uppercase tracking-wide"><?php _e('Destination', 'airlinel-theme'); ?></label>
                                <input type="text" id="dropoff-location" placeholder="<?php esc_attr_e('Where are you going?', 'airlinel-theme'); ?>" class="w-full px-5 py-4 rounded-xl border-2 border-gray-200 outline-none focus:border-[var(--primary-color)]">
                            </div>
                        </div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="flex flex-col">
        <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 ml-2"><?php _e('Pickup Date', 'airlinel-theme'); ?></label>
        <input type="date" id="pickup-date-ap" placeholder="<?php esc_attr_e('Select Date', 'airlinel-theme'); ?>" class="w-full px-5 py-4 rounded-xl border-2 border-gray-200 outline-none focus:border-[var(--primary-color)]">

      </div>
    <div class="flex flex-col">
        <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 ml-2"><?php _e('Pickup Time', 'airlinel-theme'); ?></label>
<input type="time" id="pickup-time-ap" placeholder="<?php esc_attr_e('Select Time', 'airlinel-theme'); ?>" class="w-full px-5 py-4 rounded-xl border-2 border-gray-200 outline-none focus:border-[var(--primary-color)]">
    </div>
</div>
                        <button id="search-button" class="w-full bg-[var(--primary-color)] text-white font-bold py-5 rounded-full hover:bg-[var(--primary-button-hover-bg-color)] transition-all transform hover:-translate-y-1 shadow-xl">
                            <?php _e('Check Availability & Price', 'airlinel-theme'); ?>
                        </button>
<div id="trip-summary" style="display: none; margin-top: 20px; padding: 20px; background: rgba(255,255,255,0.1); border-radius: 15px;">
    <div style="display: flex; justify-content: space-around; ">
        <div class="text-center">
            <small style="opacity: 0.6; display: block;"><?php _e('Distance', 'airlinel-theme'); ?></small>
            <span id="display-distance" style="font-size: 24px; font-weight: bold; ">0</span>
            <span style="font-size: 14px;">KM</span>
        </div>
        <div class="text-center">
            <small style="opacity: 0.6; display: block;"><?php _e('Est. Time', 'airlinel-theme'); ?></small>
            <span id="display-duration" style="font-size: 24px; font-weight: bold; ">0</span>
        </div>
    </div>
    <input type="hidden" id="distance-value">
    <input type="hidden" id="duration-value">
</div>                   </form>
                </div>
				 <!-- Sub-text -->
                    <div class="mt-6 text-center lg:text-left">
                      <p class="text-sm text-[var(--gray-text-color)] font-medium leading-relaxed">
                        <?php _e('No hidden fees. No surge pricing. Just precision travel.', 'airlinel-theme'); ?>
                      </p>
                    </div>
            </div>
        <!-- RIGHT SIDE - VISUAL STORYTELLING STACK -->
                <div class="order-2 lg:order-2 relative h-[500px] lg:h-[700px]">
                  <!-- Main Large Image with Overlay Text -->
                  <div class="absolute top-0 right-0 w-[85%] h-[55%] rounded-3xl overflow-hidden shadow-2xl group">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-009.webp" alt="Professional chauffeur opening car door for London airport transfer" title="Airport transfer chauffeur opening car door" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" fetchpriority="high" width="1438" height="1080" data-media="{&quot;id&quot;:&quot;2246480516&quot;,&quot;src&quot;:&quot;iStock&quot;,&quot;type&quot;:&quot;image&quot;}">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                    <div class="absolute bottom-8 left-8 right-8">
                      <h2 class="font-[var(--font-family-heading)] text-3xl sm:text-4xl lg:text-5xl font-bold text-white leading-tight">
                        <?php _e('Book Your Airport', 'airlinel-theme'); ?><br><?php _e('Transfer in Advance.', 'airlinel-theme'); ?>
                      </h2>
                    </div>
                  </div>

                  <!-- Bottom Left Card -->
                  <div class="absolute bottom-0 left-0 w-[55%] h-[40%] rounded-3xl overflow-hidden shadow-2xl group">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-013.webp" alt="Executive passenger in luxury car during London airport transfer" title="Luxury airport transfer interior" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" data-media="{&quot;id&quot;:&quot;1695499902&quot;,&quot;src&quot;:&quot;iStock&quot;,&quot;type&quot;:&quot;image&quot;}">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                    <div class="absolute bottom-6 left-6 right-6">
                      <p class="font-[var(--font-family-heading)] text-xl sm:text-2xl font-bold text-white leading-tight">
                        <?php _e('Travel Without', 'airlinel-theme'); ?><br><?php _e('Friction.', 'airlinel-theme'); ?>
                      </p>
                    </div>
                  </div>

                  <!-- Top Left Floating Badge -->
                  <div class="absolute top-12 left-0 bg-white/95 backdrop-blur-xl rounded-2xl shadow-xl px-6 py-4 border border-white/60">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[var(--primary-color)] to-[var(--accent-color)] flex items-center justify-center">
                        <i class="fa-solid fa-plane-arrival text-white text-xl"></i>
                      </div>
                      <div>
                        <p class="text-xs text-[var(--gray-text-color)] font-semibold uppercase tracking-wide">
                          <?php _e('Flight Tracking', 'airlinel-theme'); ?>
                        </p>
                        <p class="text-sm font-bold text-[var(--dark-text-color)]">
                          <?php _e('Always On Time', 'airlinel-theme'); ?>
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- Bottom Right Stat Badge -->
                  <div class="absolute bottom-[45%] right-[15%] bg-white/95 backdrop-blur-xl rounded-2xl shadow-xl px-6 py-4 border border-white/60">
                    <div class="text-center">
                      <p class="font-[var(--font-family-heading)] text-3xl font-bold text-[var(--primary-color)]">
                        24/7
                      </p>
                      <p class="text-xs text-[var(--gray-text-color)] font-semibold uppercase tracking-wide mt-1">
                        <?php _e('Available', 'airlinel-theme'); ?>
                      </p>
                    </div>
                  </div>
                </div>
        </div>
    </div>
</section>
<?php endif; ?>
<?php endif; // booking_cta section ?>

          <!-- What Sets Us Apart Section -->
          <?php if ($homepage_mgr->get_section_visibility('service_highlights')) : ?>
          <?php $custom_content = $homepage_mgr->get_section_content('service_highlights'); ?>
          <?php if (!empty($custom_content)) : echo $custom_content; else: ?>
          <section class="code-section bg-white py-20 sm:py-24 border-b border-gray-100" id="sh2t7nm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
              <!-- Section Header -->
              <div class="text-center mb-12">
                <p class="text-sm font-bold text-[var(--gray-text-color)] uppercase tracking-wider mb-3">
                  <?php _e('Trusted by travelers who don\'t compromise', 'airlinel-theme'); ?>
                </p>
                <h2 class="font-[var(--font-family-heading)] text-3xl sm:text-4xl lg:text-5xl font-bold text-[var(--dark-text-color)] mb-6">
                  <?php _e('Why Choose Airlinel for London Airport Transfers', 'airlinel-theme'); ?>
                </h2>
              </div>

              <!-- Trust Grid -->
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Trust Item 1 -->
                <div class="group bg-gradient-to-br from-[var(--light-background-color)] to-white rounded-2xl p-8 hover:shadow-xl transition-all duration-300 border border-[var(--light-border-color)]">
                  <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-gradient-to-br from-[var(--primary-color)] to-[var(--accent-color)] flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                      <i class="fa-solid fa-plane-arrival text-white text-2xl"></i>
                    </div>
                    <div>
                      <h3 class="font-[var(--font-family-heading)] text-lg font-bold text-[var(--dark-text-color)] mb-2">
                        <?php _e('Real-Time Flight Tracking', 'airlinel-theme'); ?>
                      </h3>
                      <p class="text-sm text-[var(--gray-text-color)] leading-relaxed">
                        <?php _e('We monitor your flight automatically and adjust pickup times accordingly.', 'airlinel-theme'); ?>
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Trust Item 2 -->
                <div class="group bg-gradient-to-br from-[var(--light-background-color)] to-white rounded-2xl p-8 hover:shadow-xl transition-all duration-300 border border-[var(--light-border-color)]">
                  <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-gradient-to-br from-[var(--primary-color)] to-[var(--accent-color)] flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                      <i class="fa-solid fa-user-tie text-white text-2xl"></i>
                    </div>
                    <div>
                      <h3 class="font-[var(--font-family-heading)] text-lg font-bold text-[var(--dark-text-color)] mb-2">
                        <?php _e('Executive-Level Chauffeurs', 'airlinel-theme'); ?>
                      </h3>
                      <p class="text-sm text-[var(--gray-text-color)] leading-relaxed">
                        <?php _e('Professionally trained, impeccably presented, and multilingual.', 'airlinel-theme'); ?>
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Trust Item 3 -->
                <div class="group bg-gradient-to-br from-[var(--light-background-color)] to-white rounded-2xl p-8 hover:shadow-xl transition-all duration-300 border border-[var(--light-border-color)]">
                  <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-gradient-to-br from-[var(--primary-color)] to-[var(--accent-color)] flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                      <i class="fa-solid fa-tag text-white text-2xl"></i>
                    </div>
                    <div>
                      <h3 class="font-[var(--font-family-heading)] text-lg font-bold text-[var(--dark-text-color)] mb-2">
                        <?php _e('Fixed Transparent Pricing', 'airlinel-theme'); ?>
                      </h3>
                      <p class="text-sm text-[var(--gray-text-color)] leading-relaxed">
                        <?php _e('No surge pricing. No hidden fees. The price you see is final.', 'airlinel-theme'); ?>
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Trust Item 4 -->
                <div class="group bg-gradient-to-br from-[var(--light-background-color)] to-white rounded-2xl p-8 hover:shadow-xl transition-all duration-300 border border-[var(--light-border-color)]">
                  <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-gradient-to-br from-[var(--primary-color)] to-[var(--accent-color)] flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                      <i class="fa-solid fa-handshake text-white text-2xl"></i>
                    </div>
                    <div>
                      <h3 class="font-[var(--font-family-heading)] text-lg font-bold text-[var(--dark-text-color)] mb-2">
                        <?php _e('Meet & Greet Included', 'airlinel-theme'); ?>
                      </h3>
                      <p class="text-sm text-[var(--gray-text-color)] leading-relaxed">
                        <?php _e('Your chauffeur will meet you at arrivals with a name board.', 'airlinel-theme'); ?>
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Trust Item 5 -->
                <div class="group bg-gradient-to-br from-[var(--light-background-color)] to-white rounded-2xl p-8 hover:shadow-xl transition-all duration-300 border border-[var(--light-border-color)]">
                  <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-gradient-to-br from-[var(--primary-color)] to-[var(--accent-color)] flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                      <i class="fa-solid fa-headset text-white text-2xl"></i>
                    </div>
                    <div>
                      <h3 class="font-[var(--font-family-heading)] text-lg font-bold text-[var(--dark-text-color)] mb-2">
                        <?php _e('24/7 Global Support', 'airlinel-theme'); ?>
                      </h3>
                      <p class="text-sm text-[var(--gray-text-color)] leading-relaxed">
                        <?php _e('Round-the-clock assistance in multiple languages, wherever you are.', 'airlinel-theme'); ?>
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Trust Item 6 -->
                <div class="group bg-gradient-to-br from-[var(--light-background-color)] to-white rounded-2xl p-8 hover:shadow-xl transition-all duration-300 border border-[var(--light-border-color)]">
                  <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-gradient-to-br from-[var(--primary-color)] to-[var(--accent-color)] flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                      <i class="fa-solid fa-car-side text-white text-2xl"></i>
                    </div>
                    <div>
                      <h3 class="font-[var(--font-family-heading)] text-lg font-bold text-[var(--dark-text-color)] mb-2">
                        <?php _e('Luxury Fleet Guarantee', 'airlinel-theme'); ?>
                      </h3>
                      <p class="text-sm text-[var(--gray-text-color)] leading-relaxed">
                        <?php _e('Immaculate vehicles maintained to showroom standards daily.', 'airlinel-theme'); ?>
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Trust Item 7 -->
                <div class="group bg-gradient-to-br from-[var(--light-background-color)] to-white rounded-2xl p-8 hover:shadow-xl transition-all duration-300 border border-[var(--light-border-color)]">
                  <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-gradient-to-br from-[var(--primary-color)] to-[var(--accent-color)] flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                      <i class="fa-solid fa-bolt text-white text-2xl"></i>
                    </div>
                    <div>
                      <h3 class="font-[var(--font-family-heading)] text-lg font-bold text-[var(--dark-text-color)] mb-2">
                        <?php _e('Instant Booking Confirmation', 'airlinel-theme'); ?>
                      </h3>
                      <p class="text-sm text-[var(--gray-text-color)] leading-relaxed">
                        <?php _e('Receive immediate confirmation with full journey details.', 'airlinel-theme'); ?>
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Trust Item 8 -->
                <div class="group bg-gradient-to-br from-[var(--light-background-color)] to-white rounded-2xl p-8 hover:shadow-xl transition-all duration-300 border border-[var(--light-border-color)]">
                  <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-gradient-to-br from-[var(--primary-color)] to-[var(--accent-color)] flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                      <i class="fa-solid fa-language text-white text-2xl"></i>
                    </div>
                    <div>
                      <h3 class="font-[var(--font-family-heading)] text-lg font-bold text-[var(--dark-text-color)] mb-2">
                        <?php _e('Multilingual Drivers', 'airlinel-theme'); ?>
                      </h3>
                      <p class="text-sm text-[var(--gray-text-color)] leading-relaxed">
                        <?php _e('English, Turkish, and other languages spoken fluently.', 'airlinel-theme'); ?>
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Trust Item 9 -->
                <div class="group bg-gradient-to-br from-[var(--light-background-color)] to-white rounded-2xl p-8 hover:shadow-xl transition-all duration-300 border border-[var(--light-border-color)]">
                  <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-gradient-to-br from-[var(--primary-color)] to-[var(--accent-color)] flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                      <i class="fa-solid fa-building text-white text-2xl"></i>
                    </div>
                    <div>
                      <h3 class="font-[var(--font-family-heading)] text-lg font-bold text-[var(--dark-text-color)] mb-2">
                        <?php _e('Corporate Billing Available', 'airlinel-theme'); ?>
                      </h3>
                      <p class="text-sm text-[var(--gray-text-color)] leading-relaxed">
                        <?php _e('Streamlined invoicing and account management for businesses.', 'airlinel-theme'); ?>
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>
          <?php endif; ?>
          <?php endif; // service_highlights section ?>

          <!-- Services Section -->
          <?php if ($homepage_mgr->get_section_visibility('featured_routes')) : ?>
          <?php $custom_content = $homepage_mgr->get_section_content('featured_routes'); ?>
          <?php if (!empty($custom_content)) : echo $custom_content; else: ?>
          <section class="code-section relative py-32 sm:py-40 bg-gradient-to-b from-white via-gray-50 to-white overflow-hidden" id="sp27k1n">
            <div class="w-full px-4 sm:px-6 lg:px-12 max-w-[1800px] mx-auto">
              <!-- Section Header -->
              <div class="text-center mb-20">
                <h2 class="font-[var(--font-family-heading)] text-5xl sm:text-6xl lg:text-7xl xl:text-8xl font-bold text-[var(--dark-text-color)] mb-8 leading-tight">
                  <?php _e('Airport Transfer Services Tailored for Every Journey', 'airlinel-theme'); ?>
                </h2>
                <p class="text-xl sm:text-2xl text-[var(--gray-text-color)] max-w-3xl mx-auto leading-relaxed">
                  <?php _e('Precision transport designed for how the world moves today.', 'airlinel-theme'); ?>
                </p>
              </div>

              <!-- Services Grid - 3x3 -->
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                <!-- Service 1: Airport Transfers -->
                <div class="group relative overflow-hidden rounded-3xl bg-white shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                  <div class="aspect-[4/3] overflow-hidden">
                    <img loading="lazy" src="<?php echo get_template_directory_uri(); ?>/images/theme-image-009.webp" alt="Professional chauffeur opening car door for London airport transfer" title="Airport transfer chauffeur opening car door" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" width="1438" height="1080" data-media="{&quot;id&quot;:&quot;2246480516&quot;,&quot;src&quot;:&quot;iStock&quot;,&quot;type&quot;:&quot;image&quot;}">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                  </div>
                  <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                    <h3 class="font-[var(--font-family-heading)] text-2xl sm:text-3xl font-bold mb-3">
                      <?php _e('Airport Transfers', 'airlinel-theme'); ?>
                    </h3>
                    <p class="text-sm text-white/90 mb-4 leading-relaxed">
                      <?php _e('Seamless arrivals and departures with precision timing.', 'airlinel-theme'); ?>
                    </p>
                    <a href="/services" class="inline-flex items-center text-sm font-bold text-white hover:text-[var(--primary-color)] transition-colors">
                      <?php _e('Airport Transfer Services in London', 'airlinel-theme'); ?> <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                  </div>
                </div>

                <!-- Service 2: Corporate Travel -->
                <div class="group relative overflow-hidden rounded-3xl bg-white shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                  <div class="aspect-[4/3] overflow-hidden">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-013.webp" alt="Executive passenger in luxury car during London airport transfer" title="Luxury airport transfer interior" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" data-media="{&quot;id&quot;:&quot;1695499902&quot;,&quot;src&quot;:&quot;iStock&quot;,&quot;type&quot;:&quot;image&quot;}">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                  </div>
                  <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                    <h3 class="font-[var(--font-family-heading)] text-2xl sm:text-3xl font-bold mb-3">
                      <?php _e('Corporate Travel', 'airlinel-theme'); ?>
                    </h3>
                    <p class="text-sm text-white/90 mb-4 leading-relaxed">
                      <?php _e('Executive transport for professionals who value punctuality.', 'airlinel-theme'); ?>
                    </p>
                    <a href="/services" class="inline-flex items-center text-sm font-bold text-white hover:text-[var(--primary-color)] transition-colors">
                      <?php _e('Corporate Airport Travel Services', 'airlinel-theme'); ?> <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                  </div>
                </div>

                <!-- Service 3: Wedding Transport -->
                <div class="group relative overflow-hidden rounded-3xl bg-white shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                  <div class="aspect-[4/3] overflow-hidden">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-008.webp" alt="Chauffeur assisting passenger for airport transfer" title="Airport transfer chauffeur assistance" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" data-media="{&quot;id&quot;:&quot;1695506351&quot;,&quot;src&quot;:&quot;iStock&quot;,&quot;type&quot;:&quot;image&quot;}">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                  </div>
                  <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                    <h3 class="font-[var(--font-family-heading)] text-2xl sm:text-3xl font-bold mb-3">
                      <?php _e('Wedding Transport', 'airlinel-theme'); ?>
                    </h3>
                    <p class="text-sm text-white/90 mb-4 leading-relaxed">
                      <?php _e('Your special day deserves seamless, elegant transportation.', 'airlinel-theme'); ?>
                    </p>
                    <a href="/services" class="inline-flex items-center text-sm font-bold text-white hover:text-[var(--primary-color)] transition-colors">
                      <?php _e('Wedding Transport & Chauffeur Service', 'airlinel-theme'); ?> <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                  </div>
                </div>

                <!-- Service 4: VIP Chauffeur -->
                <div class="group relative overflow-hidden rounded-3xl bg-white shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                  <div class="aspect-[4/3] overflow-hidden">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-007.webp" alt="Uniformed chauffeur ready for London airport transfer service" title="Professional airport chauffeur in uniform" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" data-media="{&quot;id&quot;:&quot;2246478916&quot;,&quot;src&quot;:&quot;iStock&quot;,&quot;type&quot;:&quot;image&quot;}">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                  </div>
                  <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                    <h3 class="font-[var(--font-family-heading)] text-2xl sm:text-3xl font-bold mb-3">
                      <?php _e('VIP Chauffeur', 'airlinel-theme'); ?>
                    </h3>
                    <p class="text-sm text-white/90 mb-4 leading-relaxed">
                      <?php _e('Full-day dedicated chauffeur for discerning clients.', 'airlinel-theme'); ?>
                    </p>
                    <a href="/services" class="inline-flex items-center text-sm font-bold text-white hover:text-[var(--primary-color)] transition-colors">
                      <?php _e('VIP Chauffeur Service Details', 'airlinel-theme'); ?> <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                  </div>
                </div>

                <!-- Service 5: Event Transport -->
                <div class="group relative overflow-hidden rounded-3xl bg-white shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                  <div class="aspect-[4/3] overflow-hidden">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-002.webp" alt="Executive vehicle escort for VIP London airport transfer" title="VIP airport transfer escort" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" data-media="{&quot;id&quot;:&quot;2159608056&quot;,&quot;src&quot;:&quot;iStock&quot;,&quot;type&quot;:&quot;image&quot;}">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                  </div>
                  <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                    <h3 class="font-[var(--font-family-heading)] text-2xl sm:text-3xl font-bold mb-3">
                      <?php _e('Event Transport', 'airlinel-theme'); ?>
                    </h3>
                    <p class="text-sm text-white/90 mb-4 leading-relaxed">
                      <?php _e('Coordinated group transport for conferences and galas.', 'airlinel-theme'); ?>
                    </p>
                    <a href="/services" class="inline-flex items-center text-sm font-bold text-white hover:text-[var(--primary-color)] transition-colors">
                      <?php _e('Event & Conference Transport', 'airlinel-theme'); ?> <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                  </div>
                </div>

                <!-- Service 6: Hotel Transfers -->
                <div class="group relative overflow-hidden rounded-3xl bg-white shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                  <div class="aspect-[4/3] overflow-hidden">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-003.webp" alt="Key handover for London airport transfer service" title="Airport transfer vehicle unlocking" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" data-media="{&quot;id&quot;:&quot;2161442798&quot;,&quot;src&quot;:&quot;iStock&quot;,&quot;type&quot;:&quot;image&quot;}">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                  </div>
                  <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                    <h3 class="font-[var(--font-family-heading)] text-2xl sm:text-3xl font-bold mb-3">
                      <?php _e('Hotel Transfers', 'airlinel-theme'); ?>
                    </h3>
                    <p class="text-sm text-white/90 mb-4 leading-relaxed">
                      <?php _e('Direct transfer to your accommodation with luggage assistance.', 'airlinel-theme'); ?>
                    </p>
                    <a href="/services" class="inline-flex items-center text-sm font-bold text-white hover:text-[var(--primary-color)] transition-colors">
                      <?php _e('Hotel Airport Transfer Service', 'airlinel-theme'); ?> <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                  </div>
                </div>

                <!-- Service 7: Cruise Port Transfers -->
                <div class="group relative overflow-hidden rounded-3xl bg-white shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                  <div class="aspect-[4/3] overflow-hidden">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-004.webp" alt="Luxury car interior for London airport transfer" title="Airport transfer vehicle interior" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" data-media="{&quot;id&quot;:&quot;2223015736&quot;,&quot;src&quot;:&quot;iStock&quot;,&quot;type&quot;:&quot;image&quot;}">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                  </div>
                  <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                    <h3 class="font-[var(--font-family-heading)] text-2xl sm:text-3xl font-bold mb-3">
                      <?php _e('Cruise Port Transfers', 'airlinel-theme'); ?>
                    </h3>
                    <p class="text-sm text-white/90 mb-4 leading-relaxed">
                      <?php _e('Seamless transfer from port to airport or accommodation.', 'airlinel-theme'); ?>
                    </p>
                    <a href="/services" class="inline-flex items-center text-sm font-bold text-white hover:text-[var(--primary-color)] transition-colors">
                      <?php _e('Cruise Port Transfer Service', 'airlinel-theme'); ?> <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                  </div>
                </div>

                <!-- Service 8: Private City Tours -->
                <div class="group relative overflow-hidden rounded-3xl bg-white shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                  <div class="aspect-[4/3] overflow-hidden">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-011.webp" alt="Futuristic airport transfer vehicle in London" title="Modern airport transfer car" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" data-media="{&quot;id&quot;:&quot;2213450516&quot;,&quot;src&quot;:&quot;iStock&quot;,&quot;type&quot;:&quot;image&quot;}">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                  </div>
                  <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                    <h3 class="font-[var(--font-family-heading)] text-2xl sm:text-3xl font-bold mb-3">
                      <?php _e('Private City Tours', 'airlinel-theme'); ?>
                    </h3>
                    <p class="text-sm text-white/90 mb-4 leading-relaxed">
                      <?php _e('Bespoke city tours with local insider knowledge.', 'airlinel-theme'); ?>
                    </p>
                    <a href="/services" class="inline-flex items-center text-sm font-bold text-white hover:text-[var(--primary-color)] transition-colors">
                      <?php _e('Private City Tour Packages', 'airlinel-theme'); ?> <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                  </div>
                </div>

                <!-- Service 9: Business Class Mobility (Premium) -->
                <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-[var(--dark-background-color)] to-black shadow-2xl hover:shadow-[var(--primary-color)]/30 transition-all duration-500 hover:-translate-y-2 border-2 border-[var(--primary-color)]">
                  <div class="aspect-[4/3] overflow-hidden">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-012.webp" alt="High-performance black car used for London airport transfer" title="Sports car airport transfer" class="w-full h-full object-cover opacity-40 group-hover:scale-110 group-hover:opacity-60 transition-all duration-700" data-media="{&quot;id&quot;:&quot;2206757786&quot;,&quot;src&quot;:&quot;iStock&quot;,&quot;type&quot;:&quot;image&quot;}">
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/70 to-transparent"></div>
                  </div>
                  <div class="absolute top-6 right-6">
                    <span class="inline-block px-4 py-2 bg-[var(--primary-color)] text-white text-xs font-bold uppercase tracking-wider rounded-full">
                      <?php _e('Most Requested by Executives', 'airlinel-theme'); ?>
                    </span>
                  </div>
                  <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                    <h3 class="font-[var(--font-family-heading)] text-2xl sm:text-3xl font-bold mb-3">
                      <?php _e('Business Class Mobility', 'airlinel-theme'); ?>
                    </h3>
                    <p class="text-sm text-white/90 mb-4 leading-relaxed">
                      <?php _e('Elite-tier service combining chauffeur, executive assistant, and mobile office.', 'airlinel-theme'); ?>
                    </p>
                    <a href="/services" class="inline-flex items-center text-sm font-bold text-[var(--primary-color)] hover:text-white transition-colors">
                      <?php _e('Business Class Mobility Service', 'airlinel-theme'); ?> <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </section>
          <?php endif; ?>
          <?php endif; // featured_routes section ?>

          <!-- Cities Section -->
          <?php if ($homepage_mgr->get_section_visibility('faq_section')) : ?>
          <?php $custom_content = $homepage_mgr->get_section_content('faq_section'); ?>
          <?php if (!empty($custom_content)) : echo $custom_content; else: ?>
          <?php include( get_template_directory() . '/homepage-cities-section.php' ); ?>
          <?php endif; ?>
          <?php endif; // faq_section section ?>
<?php if ($homepage_mgr->get_section_visibility('customer_testimonials')) : ?>
<?php $custom_content = $homepage_mgr->get_section_content('customer_testimonials'); ?>
<?php if (!empty($custom_content)) : echo $custom_content; else: ?>
<section class="code-section py-32 sm:py-40 bg-gradient-to-b from-[var(--dark-background-color)] via-gray-900 to-white" id="travel-intelligence">
    <div class="w-full px-4 sm:px-6 lg:px-12 max-w-[1800px] mx-auto">
        
        <div class="mb-20">
            <h2 class="font-[var(--font-family-heading)] text-5xl sm:text-6xl lg:text-7xl xl:text-8xl font-bold text-white mb-8 leading-tight">
                <?php _e('Travel Intelligence', 'airlinel-theme'); ?>
            </h2>
            <p class="text-xl sm:text-2xl text-gray-300 max-w-3xl leading-relaxed">
                <?php _e('Strategies, insights, and destination expertise for travelers who move differently.', 'airlinel-theme'); ?>
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-6 lg:grid-cols-12 gap-6 auto-rows-[280px]">
            
            <?php
            $args = array(
                'post_type' => 'post',
                'posts_per_page' => 8,
                'orderby' => 'date',
                'order' => 'DESC'
            );
            $query = new WP_Query($args);
            $count = 0;

            if ($query->have_posts()) :
                while ($query->have_posts()) : $query->the_post();
                    $count++;
                    
                    // Yazı kategorisini alalım
                    $categories = get_the_category();
                    $cat_name = !empty($categories) ? $categories[0]->name : 'Travel';
                    
                    // Yazı okuma süresini tahmin edelim (Opsiyonel)
                    $content = get_post_field('post_content', get_the_ID());
                    $word_count = str_word_count(strip_tags($content));
                    $reading_time = ceil($word_count / 200); // Dakikada 200 kelime

                    // Tasarım Belirleme (Bento Grid Mantığı)
                    if ($count == 1) : // EN BÜYÜK YAZI (XL)
                        ?>
                        <article onclick="location.href='<?php the_permalink(); ?>'" class="group relative md:col-span-6 lg:col-span-8 md:row-span-2 rounded-3xl overflow-hidden shadow-2xl hover:shadow-[var(--primary-color)]/20 transition-all duration-500 cursor-pointer">
                            <?php if (has_post_thumbnail()) : the_post_thumbnail('full', ['class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-700']); endif; ?>
                            <div class="absolute inset-0 bg-gradient-to-t from-black via-black/70 to-black/20"></div>
                            <div class="absolute inset-0 p-10 sm:p-12 lg:p-16 flex flex-col justify-end">
                                <div class="mb-6">
                                    <span class="inline-block px-5 py-2 bg-[var(--primary-color)] text-white text-sm font-bold uppercase tracking-wider rounded-full shadow-lg">
                                        <?php echo $cat_name; ?>
                                    </span>
                                </div>
                                <h3 class="font-[var(--font-family-heading)] text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight group-hover:translate-x-2 transition-transform duration-300">
                                    <?php the_title(); ?>
                                </h3>
                                <div class="text-white/90 text-lg sm:text-xl leading-relaxed mb-6 max-w-2xl line-clamp-2">
                                    <?php echo wp_trim_words(get_the_excerpt(), 25); ?>
                                </div>
                                <div class="flex items-center text-white/70 text-base">
                                    <i class="fa-regular fa-clock mr-3"></i>
                                    <span><?php echo $reading_time; ?> min read</span>
                                </div>
                            </div>
                        </article>

                    <?php elseif ($count == 2) : // ORTA BOY YAZI (Medium) ?>
                        <article onclick="location.href='<?php the_permalink(); ?>'" class="group relative md:col-span-3 lg:col-span-4 md:row-span-2 rounded-3xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-500 cursor-pointer">
                            <?php if (has_post_thumbnail()) : the_post_thumbnail('large', ['class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-700']); endif; ?>
                            <div class="absolute inset-0 bg-gradient-to-t from-black via-black/60 to-transparent"></div>
                            <div class="absolute inset-0 p-8 sm:p-10 flex flex-col justify-end">
                                <div class="mb-4">
                                    <span class="inline-block px-4 py-1.5 bg-white/20 backdrop-blur-sm text-white text-xs font-bold uppercase tracking-wider rounded-full">
                                        <?php echo $cat_name; ?>
                                    </span>
                                </div>
                                <h3 class="font-[var(--font-family-heading)] text-2xl sm:text-3xl lg:text-4xl font-bold text-white mb-4 leading-tight">
                                    <?php the_title(); ?>
                                </h3>
                                <div class="text-white/80 text-base leading-relaxed mb-4 line-clamp-2">
                                    <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                                </div>
                                <div class="flex items-center text-white/70 text-sm">
                                    <i class="fa-regular fa-clock mr-2"></i>
                                    <span><?php echo $reading_time; ?> min read</span>
                                </div>
                            </div>
                        </article>

                    <?php else : // KÜÇÜK YAZILAR (Geri kalan 6 tane) ?>
                        <article onclick="location.href='<?php the_permalink(); ?>'" class="group relative md:col-span-3 lg:col-span-4 rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 cursor-pointer">
                            <?php if (has_post_thumbnail()) : the_post_thumbnail('medium_large', ['class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-700']); endif; ?>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
                            <div class="absolute inset-0 p-6 sm:p-8 flex flex-col justify-end">
                                <div class="mb-3">
                                    <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-sm text-white text-xs font-bold uppercase tracking-wide rounded-full">
                                        <?php echo $cat_name; ?>
                                    </span>
                                </div>
                                <h3 class="font-[var(--font-family-heading)] text-xl sm:text-2xl font-bold text-white mb-2 leading-tight">
                                    <?php the_title(); ?>
                                </h3>
                                <div class="flex items-center text-white/70 text-xs">
                                    <i class="fa-regular fa-clock mr-2"></i>
                                    <span><?php echo $reading_time; ?> min read</span>
                                </div>
                            </div>
                        </article>
                        <?php 
                    endif;
                endwhile;
                wp_reset_postdata();
            endif; 
            ?>
        </div>

        <div class="mt-16 text-center">
            <a href="<?php echo get_permalink( get_option('page_for_posts') ); ?>" class="inline-flex items-center text-xl font-bold text-white hover:text-[var(--primary-color)] transition-colors group">
                <?php _e('View All Articles', 'airlinel-theme'); ?>
                <i class="fa-solid fa-arrow-right ml-3 group-hover:translate-x-2 transition-transform"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>
<?php endif; // customer_testimonials section ?>

          <!-- CTA Section -->
          <?php if ($homepage_mgr->get_section_visibility('special_offers')) : ?>
          <?php $custom_content = $homepage_mgr->get_section_content('special_offers'); ?>
          <?php if (!empty($custom_content)) : echo $custom_content; else: ?>
          <section class="code-section relative w-full overflow-hidden" id="su976aj" style="min-height: 80vh;">
            <!-- Animated Background with Motion Effect -->
            <div class="absolute inset-0">
              <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-010.webp" alt="Dynamic airport transfer imagery representing speed and travel" title="Airport transfer speed graphic" class="w-full h-full object-cover scale-110 animate-[slowZoom_20s_ease-in-out_infinite_alternate]" data-media="{&quot;id&quot;:&quot;2207576129&quot;,&quot;src&quot;:&quot;iStock&quot;,&quot;type&quot;:&quot;image&quot;}">
              <!-- Dark Overlay for Readability -->
              <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/60 to-black/70"></div>
            </div>

            <!-- Floating Decorative Elements -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
              <div class="absolute top-1/4 left-10 w-32 h-32 bg-[var(--primary-color)] rounded-full opacity-20 blur-3xl animate-[float_8s_ease-in-out_infinite]"></div>
              <div class="absolute bottom-1/3 right-10 w-40 h-40 bg-[var(--accent-color)] rounded-full opacity-20 blur-3xl animate-[float_10s_ease-in-out_infinite_1s]"></div>
            </div>

            <!-- Content Container -->
            <div class="relative z-10 flex flex-col items-center justify-center px-4 sm:px-6 lg:px-8 text-center" style="min-height: 80vh;">
              <!-- Editorial Headline -->
              <h2 class="font-[var(--font-family-heading)] text-5xl sm:text-6xl lg:text-7xl xl:text-8xl font-bold text-white mb-8 leading-[1.1] max-w-5xl opacity-0 translate-y-8 animate-[fadeInUp_1s_ease-out_0.2s_forwards]">
                <?php _e('Stop booking taxis.', 'airlinel-theme'); ?><br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--primary-color)] via-[var(--accent-color)] to-[var(--primary-color)] bg-[length:200%_auto] animate-[shimmer_3s_linear_infinite]"><?php _e('Start traveling properly.', 'airlinel-theme'); ?></span>
              </h2>

              <!-- Refined Subtext -->
              <p class="text-lg sm:text-xl lg:text-2xl text-gray-300 max-w-3xl mx-auto mb-12 leading-relaxed opacity-0 translate-y-8 animate-[fadeInUp_1s_ease-out_0.4s_forwards]">
                <?php _e('Experience the difference when every detail is engineered, every arrival is anticipated, and every journey feels effortless.', 'airlinel-theme'); ?>
              </p>

              <!-- Dual CTA Buttons -->
              <div class="flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6 mb-12 opacity-0 translate-y-8 animate-[fadeInUp_1s_ease-out_0.6s_forwards]">
                <a href="/book-your-ride" class="group relative inline-flex items-center justify-center px-12 py-6 text-lg font-bold text-white bg-[var(--primary-color)] rounded-full overflow-hidden transition-all duration-300 hover:bg-[var(--primary-button-hover-bg-color)] hover:shadow-[0_0_40px_rgba(204,68,82,0.6)] hover:-translate-y-1 w-full sm:w-auto">
                  <span class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></span>
                  <span class="relative z-10 flex items-center">
                    <?php _e('Book Your Ride', 'airlinel-theme'); ?>
                    <i class="fa-solid fa-arrow-right ml-3 group-hover:translate-x-2 transition-transform"></i>
                  </span>
                </a>

                <a href="/contact" class="inline-flex items-center justify-center px-12 py-6 text-lg font-bold text-white bg-transparent border-2 border-white/40 rounded-full hover:border-white hover:bg-white/10 backdrop-blur-sm transition-all duration-300 hover:-translate-y-1 w-full sm:w-auto">
                  <?php _e('Contact Us', 'airlinel-theme'); ?>
                </a>
              </div>

              <!-- Trust Micro-Bar -->
              <div class="flex flex-wrap items-center justify-center gap-6 sm:gap-8 lg:gap-10 opacity-0 translate-y-8 animate-[fadeInUp_1s_ease-out_0.8s_forwards]">
                <div class="flex items-center gap-2 text-sm sm:text-base text-gray-300">
                  <i class="fa-solid fa-check-circle text-[var(--primary-color)] text-lg"></i>
                  <span><?php _e('Instant Confirmation', 'airlinel-theme'); ?></span>
                </div>
                <div class="hidden sm:block w-px h-4 bg-gray-600"></div>
                <div class="flex items-center gap-2 text-sm sm:text-base text-gray-300">
                  <i class="fa-solid fa-check-circle text-[var(--primary-color)] text-lg"></i>
                  <span><?php _e('24/7 Support', 'airlinel-theme'); ?></span>
                </div>
                <div class="hidden sm:block w-px h-4 bg-gray-600"></div>
                <div class="flex items-center gap-2 text-sm sm:text-base text-gray-300">
                  <i class="fa-solid fa-check-circle text-[var(--primary-color)] text-lg"></i>
                  <span><?php _e('Free Cancellation', 'airlinel-theme'); ?></span>
                </div>
              </div>
            </div>

            <!-- Custom Animations -->
            <style>
              @keyframes slowZoom {
                0% { transform: scale(1.1); }
                100% { transform: scale(1.2); }
              }
              @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
              }
              @keyframes fadeInUp {
                to {
                  opacity: 1;
                  transform: translateY(0);
                }
              }
              @keyframes shimmer {
                0% { background-position: 0% center; }
                100% { background-position: 200% center; }
              }
            </style>
          </section>
          <?php endif; ?>
          <?php endif; // special_offers section ?>

 <!-- Partners & Drivers Section -->
          <?php if ($homepage_mgr->get_section_visibility('trust_signals')) : ?>
          <?php $custom_content = $homepage_mgr->get_section_content('trust_signals'); ?>
          <?php if (!empty($custom_content)) : echo $custom_content; else: ?>
          <section class="code-section relative w-full overflow-hidden" style="padding-bottom: 40px;" id="s47b4wh">
            <div class="grid lg:grid-cols-2">
              <!-- LEFT SIDE — PARTNER PROGRAM -->
              <div class="relative group overflow-hidden transition-all duration-500 hover:lg:scale-[1.02] z-10 min-h-[700px] lg:min-h-screen">
                <!-- Background Image -->
                <div class="absolute inset-0">
                  <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-005.webp" alt="Luxury hotel reception lobby near London airport transfer pickup" title="Hotel lobby for airport transfers" class="w-full h-full object-cover brightness-[0.35] contrast-[0.8]" data-media="{&quot;id&quot;:&quot;2176749084&quot;,&quot;src&quot;:&quot;iStock&quot;,&quot;type&quot;:&quot;image&quot;}">
                  <!-- Dark Overlay -->
                  <div class="absolute inset-0 bg-gradient-to-br from-black/85 via-[var(--dark-background-color)]/80 to-black/85"></div>
                </div>

                <!-- Decorative Elements -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-[var(--primary-color)] rounded-full opacity-10 blur-3xl"></div>

                <!-- Content -->
                <div class="relative z-10 flex flex-col justify-center px-6 sm:px-10 lg:px-12 xl:px-14 py-16 sm:py-20 lg:py-24 h-full">
                  <!-- Floating Glass Panel -->
                  <div class="backdrop-blur-xl bg-white/5 border border-white/10 rounded-3xl p-6 sm:p-8 lg:p-10 xl:p-12 shadow-2xl group-hover:bg-white/8 group-hover:border-white/15 transition-all duration-500 max-w-3xl mx-auto lg:mx-0">
                    <!-- Icon -->
                    <div class="mb-5 sm:mb-6 lg:mb-7">
                      <div class="inline-flex items-center justify-center w-14 sm:w-15 lg:w-16 h-14 sm:h-15 lg:h-16 rounded-2xl bg-[var(--primary-color)]/20 backdrop-blur-sm border border-[var(--primary-color)]/40">
                        <i class="fa-solid fa-handshake text-xl sm:text-2xl text-[var(--primary-color)]"></i>
                      </div>
                    </div>

                    <!-- Headline -->
                    <h2 class="font-[var(--font-family-heading)] text-3xl sm:text-4xl lg:text-[2.75rem] xl:text-5xl font-bold text-white mb-3 sm:mb-4 leading-[1.15]">
                      <?php _e('Earn With', 'airlinel-theme'); ?> <span class="text-[var(--primary-color)]">Airlinel.</span>
                    </h2>

                    <!-- Subheadline -->
                    <p class="text-base sm:text-lg text-gray-200 mb-7 sm:mb-8 leading-relaxed">
                      <?php _e('Join our global partner network and unlock preferred pricing, priority booking access, and immediate commission payouts.', 'airlinel-theme'); ?>
                    </p>

                    <!-- Bullet Benefits -->
                    <ul class="space-y-3 sm:space-y-4 mb-8 sm:mb-10">
                      <li class="flex items-start gap-3 text-white">
                        <i class="fa-solid fa-circle-check text-[var(--primary-color)] text-lg sm:text-xl mt-0.5 flex-shrink-0"></i>
                        <span class="text-[15px] sm:text-base lg:text-lg leading-relaxed"><strong class="font-semibold"><?php _e('Average partners earn £100/week', 'airlinel-theme'); ?></strong></span>
                      </li>
                      <li class="flex items-start gap-3 text-white">
                        <i class="fa-solid fa-circle-check text-[var(--primary-color)] text-lg sm:text-xl mt-0.5 flex-shrink-0"></i>
                        <span class="text-[15px] sm:text-base lg:text-lg leading-relaxed"><?php _e('Discounted transfer rates for your guests', 'airlinel-theme'); ?></span>
                      </li>
                      <li class="flex items-start gap-3 text-white">
                        <i class="fa-solid fa-circle-check text-[var(--primary-color)] text-lg sm:text-xl mt-0.5 flex-shrink-0"></i>
                        <span class="text-[15px] sm:text-base lg:text-lg leading-relaxed"><?php _e('Earn commission on every booking', 'airlinel-theme'); ?></span>
                      </li>
                      <li class="flex items-start gap-3 text-white">
                        <i class="fa-solid fa-circle-check text-[var(--primary-color)] text-lg sm:text-xl mt-0.5 flex-shrink-0"></i>
                        <span class="text-[15px] sm:text-base lg:text-lg leading-relaxed"><?php _e('Instant payouts with zero delays', 'airlinel-theme'); ?></span>
                      </li>
                      <li class="flex items-start gap-3 text-white">
                        <i class="fa-solid fa-circle-check text-[var(--primary-color)] text-lg sm:text-xl mt-0.5 flex-shrink-0"></i>
                        <span class="text-[15px] sm:text-base lg:text-lg leading-relaxed"><?php _e('API access available', 'airlinel-theme'); ?></span>
                      </li>
                      <li class="flex items-start gap-3 text-white">
                        <i class="fa-solid fa-circle-check text-[var(--primary-color)] text-lg sm:text-xl mt-0.5 flex-shrink-0"></i>
                        <span class="text-[15px] sm:text-base lg:text-lg leading-relaxed"><?php _e('Dedicated partner support team', 'airlinel-theme'); ?></span>
                      </li>
                      <li class="flex items-start gap-3 text-white">
                        <i class="fa-solid fa-circle-check text-[var(--primary-color)] text-lg sm:text-xl mt-0.5 flex-shrink-0"></i>
                        <span class="text-[15px] sm:text-base lg:text-lg leading-relaxed"><?php _e('Zero operational hassle', 'airlinel-theme'); ?></span>
                      </li>
                    </ul>

                    <!-- CTA Button -->
                    <div>
                      <a href="/partners" class="group/btn inline-flex items-center justify-center w-full sm:w-auto px-8 sm:px-10 py-4 sm:py-5 text-base sm:text-lg font-bold text-white bg-[var(--primary-color)] rounded-full hover:bg-[var(--primary-button-hover-bg-color)] transition-all duration-300 hover:shadow-[0_0_50px_rgba(204,68,82,0.6)] hover:-translate-y-1 shadow-lg">
                        <?php _e('Become a Partner', 'airlinel-theme'); ?>
                        <i class="fa-solid fa-arrow-right ml-3 group-hover/btn:translate-x-2 transition-transform"></i>
                      </a>
                    </div>

                    <!-- Trust Line -->
                    <p class="mt-6 sm:mt-7 text-xs sm:text-sm text-gray-400 italic">
                      <?php _e('Trusted by hospitality professionals worldwide', 'airlinel-theme'); ?>
                    </p>
                  </div>
                </div>
              </div>

              <!-- VERTICAL DIVIDER -->
              <div class="hidden lg:block absolute left-1/2 top-0 bottom-0 w-px bg-gradient-to-b from-transparent via-gray-600 to-transparent transform -translate-x-1/2 z-20"></div>

              <!-- RIGHT SIDE — DRIVER RECRUITMENT -->
              <div class="relative group overflow-hidden transition-all duration-500 hover:lg:scale-[1.02] z-10 min-h-[700px] lg:min-h-screen">
                <!-- Background Image -->
                <div class="absolute inset-0">
                  <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-006.webp" alt="Driver en route during London airport transfer service" title="Airport transfer driving scene" class="w-full h-full object-cover brightness-[0.35] contrast-[0.8]" data-media="{&quot;id&quot;:&quot;2181215769&quot;,&quot;src&quot;:&quot;iStock&quot;,&quot;type&quot;:&quot;image&quot;}">
                  <!-- Dark Overlay -->
                  <div class="absolute inset-0 bg-gradient-to-bl from-black/85 via-[var(--dark-background-color)]/80 to-black/85"></div>
                </div>

                <!-- Decorative Elements -->
                <div class="absolute top-0 left-0 w-64 h-64 bg-[var(--accent-color)] rounded-full opacity-10 blur-3xl"></div>

                <!-- Content -->
                <div class="relative z-10 flex flex-col justify-center px-6 sm:px-10 lg:px-12 xl:px-14 py-16 sm:py-20 lg:py-24 h-full">
                  <!-- Floating Glass Panel -->
                  <div class="backdrop-blur-xl bg-white/5 border border-white/10 rounded-3xl p-6 sm:p-8 lg:p-10 xl:p-12 shadow-2xl group-hover:bg-white/8 group-hover:border-white/15 transition-all duration-500 max-w-3xl mx-auto lg:mx-0">
                    <!-- Icon -->
                    <div class="mb-5 sm:mb-6 lg:mb-7">
                      <div class="inline-flex items-center justify-center w-14 sm:w-15 lg:w-16 h-14 sm:h-15 lg:h-16 rounded-2xl bg-[var(--accent-color)]/20 backdrop-blur-sm border border-[var(--accent-color)]/40">
                        <i class="fa-solid fa-car text-xl sm:text-2xl text-[var(--accent-color)]"></i>
                      </div>
                    </div>

                    <!-- Headline -->
                    <h2 class="font-[var(--font-family-heading)] text-3xl sm:text-4xl lg:text-[2.75rem] xl:text-5xl font-bold text-white mb-3 sm:mb-4 leading-[1.15]">
                      <?php _e('Drive With', 'airlinel-theme'); ?> <span class="text-[var(--accent-color)]">Airlinel.</span>
                    </h2>

                    <!-- Subheadline -->
                    <p class="text-base sm:text-lg text-gray-200 mb-7 sm:mb-8 leading-relaxed">
                      <?php _e('Turn your vehicle into a premium earning asset. Work on your terms while accessing high-value clients.', 'airlinel-theme'); ?>
                    </p>

                    <!-- Bullet Benefits -->
                    <ul class="space-y-3 sm:space-y-4 mb-8 sm:mb-10">
                      <li class="flex items-start gap-3 text-white">
                        <i class="fa-solid fa-circle-check text-[var(--accent-color)] text-lg sm:text-xl mt-0.5 flex-shrink-0"></i>
                        <span class="text-[15px] sm:text-base lg:text-lg leading-relaxed"><?php _e('High earning potential with', 'airlinel-theme'); ?>
                          <span class="text-[var(--accent-color)] font-semibold"><?php _e('premium rates', 'airlinel-theme'); ?></span></span>
                      </li>
                      <li class="flex items-start gap-3 text-white">
                        <i class="fa-solid fa-circle-check text-[var(--accent-color)] text-lg sm:text-xl mt-0.5 flex-shrink-0"></i>
                        <span class="text-[15px] sm:text-base lg:text-lg leading-relaxed"><?php _e('Access to premium passenger base', 'airlinel-theme'); ?></span>
                      </li>
                      <li class="flex items-start gap-3 text-white">
                        <i class="fa-solid fa-circle-check text-[var(--accent-color)] text-lg sm:text-xl mt-0.5 flex-shrink-0"></i>
                        <span class="text-[15px] sm:text-base lg:text-lg leading-relaxed"><?php _e('Flexible schedule—you decide when', 'airlinel-theme'); ?></span>
                      </li>
                      <li class="flex items-start gap-3 text-white">
                        <i class="fa-solid fa-circle-check text-[var(--accent-color)] text-lg sm:text-xl mt-0.5 flex-shrink-0"></i>
                        <span class="text-[15px] sm:text-base lg:text-lg leading-relaxed"><?php _e('Fast onboarding and training', 'airlinel-theme'); ?></span>
                      </li>
                      <li class="flex items-start gap-3 text-white">
                        <i class="fa-solid fa-circle-check text-[var(--accent-color)] text-lg sm:text-xl mt-0.5 flex-shrink-0"></i>
                        <span class="text-[15px] sm:text-base lg:text-lg leading-relaxed"><?php _e('Consistent ride flow and bookings', 'airlinel-theme'); ?></span>
                      </li>
					   <li class="flex items-start gap-3 text-white">
                        <i class="fa-solid fa-circle-check text-[var(--accent-color)] text-lg sm:text-xl mt-0.5 flex-shrink-0"></i>
                        <span class="text-[15px] sm:text-base lg:text-lg leading-relaxed"><?php _e('Average drivers earn £1000/week', 'airlinel-theme'); ?></span>
                      </li>
                    </ul>

                    <!-- CTA Button -->
                    <div>
                      <a href="/career/" class="group/btn inline-flex items-center justify-center w-full sm:w-auto px-8 sm:px-10 py-4 sm:py-5 text-base sm:text-lg font-bold text-white bg-[var(--primary-color)] rounded-full hover:bg-[var(--primary-button-hover-bg-color)] transition-all duration-300 hover:shadow-[0_0_50px_rgba(204,68,82,0.6)] hover:-translate-y-1 shadow-lg">
                        <?php _e('Apply to Drive', 'airlinel-theme'); ?>
                        <i class="fa-solid fa-arrow-right ml-3 group-hover/btn:translate-x-2 transition-transform"></i>
                      </a>
                    </div>

                    <!-- Trust Line -->
                    <p class="mt-6 sm:mt-7 text-xs sm:text-sm text-gray-400 italic">
                      <?php _e('Serious drivers only', 'airlinel-theme'); ?>
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Mobile Horizontal Divider -->
            <div class="lg:hidden absolute left-0 right-0 top-1/2 h-px bg-gradient-to-r from-transparent via-gray-600 to-transparent transform -translate-y-1/2 z-20"></div>



          </section>
          <?php endif; ?>
          <?php endif; // trust_signals section ?>

<?php
// ── COLLAPSIBLE SEO CONTENT BLOCK ────────────────────────────────
$airlinel_seo_content = get_option( 'airlinel_seo_block_content', '' );
if ( ! empty( trim( $airlinel_seo_content ) ) ) :
    $airlinel_seo_title = get_option( 'airlinel_seo_block_title', '' );
?>
<section class="bg-[var(--light-background-color)] border-y border-[var(--light-border-color)]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Toggle row — always visible -->
        <button type="button"
                id="airlinel-seo-toggle"
                class="w-full py-5 flex items-center justify-between text-left group"
                aria-expanded="false"
                aria-controls="airlinel-seo-body">

            <!-- Left: accent bar + title -->
            <span class="flex items-center gap-3 min-w-0">
                <span class="flex-shrink-0 w-1 h-7 rounded-full bg-[var(--primary-color)]"></span>
                <span class="[font-family:var(--font-family-heading)] font-bold text-sm sm:text-base text-[var(--dark-text-color)] leading-snug">
                    <?php echo ! empty( $airlinel_seo_title ) ? esc_html( $airlinel_seo_title ) : esc_html__( 'About Our Services', 'airlinel-theme' ); ?>
                </span>
            </span>

            <!-- Right: label + chevron -->
            <span class="flex-shrink-0 ml-6 flex items-center gap-2 text-[var(--primary-color)] group-hover:text-[var(--primary-button-hover-bg-color)] transition-colors text-sm font-semibold">
                <span id="airlinel-seo-lbl"><?php _e( 'Read More', 'airlinel-theme' ); ?></span>
                <i id="airlinel-seo-icon" class="fa-solid fa-chevron-down text-xs" style="transition: transform 0.35s ease;"></i>
            </span>
        </button>

        <!-- Collapsible content -->
        <div id="airlinel-seo-body"
             role="region"
             style="max-height:0; overflow:hidden; transition: max-height 0.55s ease;">
            <div class="border-t border-[var(--light-border-color)] pt-8 pb-10">
                <div id="airlinel-seo-inner" class="text-[var(--gray-text-color)] text-sm leading-relaxed">
                    <?php echo wp_kses_post( $airlinel_seo_content ); ?>
                </div>
            </div>
        </div>

    </div>
</section>

<style>
/* ── SEO Block typography ── */
#airlinel-seo-inner h2 {
    font-family: var(--font-family-heading);
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--dark-text-color);
    margin: 2rem 0 0.6rem;
    line-height: 1.3;
}
#airlinel-seo-inner h3 {
    font-family: var(--font-family-heading);
    font-size: 1rem;
    font-weight: 700;
    color: var(--dark-text-color);
    margin: 1.5rem 0 0.5rem;
    line-height: 1.35;
}
#airlinel-seo-inner p {
    margin: 0 0 0.9rem;
    line-height: 1.8;
}
#airlinel-seo-inner ul {
    list-style: none;
    padding: 0;
    margin: 0.75rem 0 1rem;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 0.4rem 1.5rem;
}
#airlinel-seo-inner ul li {
    padding-left: 1.25rem;
    position: relative;
    line-height: 1.7;
}
#airlinel-seo-inner ul li::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0.55rem;
    width: 6px;
    height: 6px;
    background: var(--primary-color);
    border-radius: 50%;
}
#airlinel-seo-inner strong {
    color: var(--dark-text-color);
    font-weight: 600;
}
#airlinel-seo-inner a {
    color: var(--primary-color);
    text-decoration: none;
}
#airlinel-seo-inner a:hover {
    text-decoration: underline;
}
</style>
<script>
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var btn  = document.getElementById('airlinel-seo-toggle');
        var body = document.getElementById('airlinel-seo-body');
        var icon = document.getElementById('airlinel-seo-icon');
        var lbl  = document.getElementById('airlinel-seo-lbl');
        if (!btn || !body) return;
        btn.addEventListener('click', function () {
            var open = btn.getAttribute('aria-expanded') === 'true';
            if (open) {
                body.style.maxHeight = '0';
                icon.style.transform = '';
                lbl.textContent = '<?php echo esc_js( __( 'Read More', 'airlinel-theme' ) ); ?>';
                btn.setAttribute('aria-expanded', 'false');
            } else {
                body.style.maxHeight = body.scrollHeight + 'px';
                icon.style.transform = 'rotate(180deg)';
                lbl.textContent = '<?php echo esc_js( __( 'Read Less', 'airlinel-theme' ) ); ?>';
                btn.setAttribute('aria-expanded', 'true');
            }
        });
    });
}());
</script>
<?php endif; ?>

<?php get_footer(); ?>