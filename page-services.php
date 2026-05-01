<?php /* Template Name: Services */ get_header(); ?>



<!-- Services Grid Section -->
<section class="py-32 sm:py-40 bg-gradient-to-b from-white via-gray-50 to-white overflow-hidden">
    <div class="w-full px-4 sm:px-6 lg:px-12 max-w-[1800px] mx-auto">
        <!-- Services Grid - 3x3 -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            <!-- Service 1: Airport Transfers -->
            <div class="group relative overflow-hidden rounded-3xl bg-white shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                <div class="aspect-[4/3] overflow-hidden">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-009.webp" alt="Professional chauffeur opening car door for London airport transfer" title="Airport transfer chauffeur opening car door" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                    <h3 class="font-[var(--font-family-heading)] text-2xl sm:text-3xl font-bold mb-3">
                        <?php _e('Airport Transfers', 'airlinel-theme'); ?>
                    </h3>
                    <p class="text-sm text-white/90 mb-4 leading-relaxed">
                        <?php _e('Seamless arrivals and departures with precision timing.', 'airlinel-theme'); ?>
                    </p>
                    <a href="<?php echo home_url('/airport-transfers'); ?>" class="inline-flex items-center text-sm font-bold text-white hover:text-[var(--primary-color)] transition-colors">
                        <?php _e('Learn More', 'airlinel-theme'); ?> <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>

            <!-- Service 2: Corporate Travel -->
            <div class="group relative overflow-hidden rounded-3xl bg-white shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                <div class="aspect-[4/3] overflow-hidden">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-013.webp" alt="Executive passenger in luxury car during London airport transfer" title="Luxury airport transfer interior" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                    <h3 class="font-[var(--font-family-heading)] text-2xl sm:text-3xl font-bold mb-3">
                        <?php _e('Corporate Travel', 'airlinel-theme'); ?>
                    </h3>
                    <p class="text-sm text-white/90 mb-4 leading-relaxed">
                        <?php _e('Executive transport for professionals who value punctuality.', 'airlinel-theme'); ?>
                    </p>
                    <a href="<?php echo home_url('/executive-travel-uk'); ?>" class="inline-flex items-center text-sm font-bold text-white hover:text-[var(--primary-color)] transition-colors">
                        <?php _e('Learn More', 'airlinel-theme'); ?> <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>

            <!-- Service 3: Wedding Transport -->
            <div class="group relative overflow-hidden rounded-3xl bg-white shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                <div class="aspect-[4/3] overflow-hidden">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-008.webp" alt="Chauffeur assisting passenger for airport transfer" title="Airport transfer chauffeur assistance" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                    <h3 class="font-[var(--font-family-heading)] text-2xl sm:text-3xl font-bold mb-3">
                        <?php _e('Wedding Transport', 'airlinel-theme'); ?>
                    </h3>
                    <p class="text-sm text-white/90 mb-4 leading-relaxed">
                        <?php _e('Your special day deserves seamless, elegant transportation.', 'airlinel-theme'); ?>
                    </p>
                    <a href="<?php echo home_url('/wedding-transport'); ?>" class="inline-flex items-center text-sm font-bold text-white hover:text-[var(--primary-color)] transition-colors">
                        <?php _e('Learn More', 'airlinel-theme'); ?> <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>

            <!-- Service 4: VIP Chauffeur -->
            <div class="group relative overflow-hidden rounded-3xl bg-white shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                <div class="aspect-[4/3] overflow-hidden">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-007.jpg" alt="Uniformed chauffeur ready for London airport transfer service" title="Professional airport chauffeur in uniform" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                    <h3 class="font-[var(--font-family-heading)] text-2xl sm:text-3xl font-bold mb-3">
                        <?php _e('VIP Chauffeur', 'airlinel-theme'); ?>
                    </h3>
                    <p class="text-sm text-white/90 mb-4 leading-relaxed">
                        <?php _e('Full-day dedicated chauffeur for discerning clients.', 'airlinel-theme'); ?>
                    </p>
                    <a href="<?php echo home_url('/vip-chauffeur-london'); ?>" class="inline-flex items-center text-sm font-bold text-white hover:text-[var(--primary-color)] transition-colors">
                        <?php _e('Learn More', 'airlinel-theme'); ?> <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>

            <!-- Service 5: Event Transport -->
            <div class="group relative overflow-hidden rounded-3xl bg-white shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                <div class="aspect-[4/3] overflow-hidden">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-002.jpg" alt="Executive vehicle escort for VIP London airport transfer" title="VIP airport transfer escort" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                    <h3 class="font-[var(--font-family-heading)] text-2xl sm:text-3xl font-bold mb-3">
                        <?php _e('Event Transport', 'airlinel-theme'); ?>
                    </h3>
                    <p class="text-sm text-white/90 mb-4 leading-relaxed">
                        <?php _e('Coordinated group transport for conferences and galas.', 'airlinel-theme'); ?>
                    </p>
                    <a href="<?php echo home_url('/event-transport'); ?>" class="inline-flex items-center text-sm font-bold text-white hover:text-[var(--primary-color)] transition-colors">
                        <?php _e('Learn More', 'airlinel-theme'); ?> <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>

            <!-- Service 6: Cruise Port Transfers -->
            <div class="group relative overflow-hidden rounded-3xl bg-white shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                <div class="aspect-[4/3] overflow-hidden">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-003.jpg" alt="Key handover for London airport transfer service" title="Airport transfer vehicle unlocking" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                    <h3 class="font-[var(--font-family-heading)] text-2xl sm:text-3xl font-bold mb-3">
                        <?php _e('Cruise Port Transfers', 'airlinel-theme'); ?>
                    </h3>
                    <p class="text-sm text-white/90 mb-4 leading-relaxed">
                        <?php _e('Seamless transfer from port to airport or accommodation.', 'airlinel-theme'); ?>
                    </p>
                    <a href="<?php echo home_url('/cruise-port-transfers'); ?>" class="inline-flex items-center text-sm font-bold text-white hover:text-[var(--primary-color)] transition-colors">
                        <?php _e('Learn More', 'airlinel-theme'); ?> <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>

            <!-- Service 7: Private City Tours -->
            <div class="group relative overflow-hidden rounded-3xl bg-white shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                <div class="aspect-[4/3] overflow-hidden">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-011.jpg" alt="Futuristic airport transfer vehicle in London" title="Modern airport transfer car" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                    <h3 class="font-[var(--font-family-heading)] text-2xl sm:text-3xl font-bold mb-3">
                        <?php _e('Private City Tours', 'airlinel-theme'); ?>
                    </h3>
                    <p class="text-sm text-white/90 mb-4 leading-relaxed">
                        <?php _e('Bespoke city tours with local insider knowledge.', 'airlinel-theme'); ?>
                    </p>
                    <a href="<?php echo home_url('/private-city-tours'); ?>" class="inline-flex items-center text-sm font-bold text-white hover:text-[var(--primary-color)] transition-colors">
                        <?php _e('Learn More', 'airlinel-theme'); ?> <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>

            <!-- Service 8: Hotel Transfers -->
            <div class="group relative overflow-hidden rounded-3xl bg-white shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                <div class="aspect-[4/3] overflow-hidden">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-004.jpg" alt="Luxury car interior for London airport transfer" title="Airport transfer vehicle interior" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                    <h3 class="font-[var(--font-family-heading)] text-2xl sm:text-3xl font-bold mb-3">
                        <?php _e('Hotel Transfers', 'airlinel-theme'); ?>
                    </h3>
                    <p class="text-sm text-white/90 mb-4 leading-relaxed">
                        <?php _e('Direct transfer to your accommodation with luggage assistance.', 'airlinel-theme'); ?>
                    </p>
                    <a href="#" class="inline-flex items-center text-sm font-bold text-white hover:text-[var(--primary-color)] transition-colors">
                        <?php _e('Learn More', 'airlinel-theme'); ?> <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>

            <!-- Service 9: Business Class Mobility (Premium) -->
            <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-[var(--dark-background-color)] to-black shadow-2xl hover:shadow-[var(--primary-color)]/30 transition-all duration-500 hover:-translate-y-2 border-2 border-[var(--primary-color)]">
                <div class="aspect-[4/3] overflow-hidden">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-012.jpg" alt="High-performance black car used for London airport transfer" title="Sports car airport transfer" class="w-full h-full object-cover opacity-40 group-hover:scale-110 group-hover:opacity-60 transition-all duration-700">
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
                    <a href="<?php echo home_url('/business-car-service'); ?>" class="inline-flex items-center text-sm font-bold text-[var(--primary-color)] hover:text-white transition-colors">
                        <?php _e('Learn More', 'airlinel-theme'); ?> <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Description Section -->
<section class="py-20 bg-white">
    <article class="max-w-4xl mx-auto px-4">
        <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed mb-20">
            <?php the_content(); ?>
        </div>
    </article>
</section>

<!-- CTA Section -->
<section class="py-20 sm:py-24 bg-gradient-to-r from-[var(--dark-background-color)] to-black">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="font-[var(--font-family-heading)] text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-6">
            <?php _e('Ready to Experience Premium Travel?', 'airlinel-theme'); ?>
        </h2>
        <p class="text-lg sm:text-xl text-gray-300 max-w-2xl mx-auto mb-8">
            <?php _e('Book your service today and discover the Airlinel difference.', 'airlinel-theme'); ?>
        </p>
        <a href="<?php echo home_url('/book-your-ride'); ?>" class="inline-flex items-center justify-center px-10 py-5 text-lg font-bold text-white bg-[var(--primary-color)] rounded-full hover:bg-[var(--primary-button-hover-bg-color)] transition-all duration-300 hover:shadow-2xl hover:shadow-[var(--primary-color)]/50 hover:-translate-y-1">
            <?php _e('Book Now', 'airlinel-theme'); ?>
            <i class="fa-solid fa-arrow-right ml-3"></i>
        </a>
    </div>
</section>

<?php get_footer(); ?>
