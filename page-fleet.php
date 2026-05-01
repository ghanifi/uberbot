<?php /* Template Name: Our Fleet */ get_header(); ?>
<!-- Fleet Grid Section -->
<section class="py-20 bg-[#1a1a1a]">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-white mb-10 text-center"><?php _e('Our Premium Vehicles', 'airlinel-theme'); ?></h2>
        <div id="fleet-results" class="container mx-auto px-4 max-w-[1600px]">
            <?php
                // Query fleet vehicles
                $args = array(
                    'post_type'      => 'fleet',
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                );
                
                $query = new WP_Query($args);

                if ($query->have_posts()) {
                    // Collect vehicles with their details
                    $vehicles = array();
                    
                    while ($query->have_posts()) {
                        $query->the_post();
                        
                        // Get vehicle details
                        $multiplier = get_post_meta(get_the_ID(), '_vehicle_multiplier', true) ?: 1.0;
                        $passengers = intval(get_post_meta(get_the_ID(), 'fleet_passengers', true)) ?: 4;
                        $luggage = intval(get_post_meta(get_the_ID(), 'fleet_luggage', true)) ?: 3;
                        
                        // Resmi birden fazla boyutta dene
                        $thumbnail = '';
                        if (has_post_thumbnail(get_the_ID())) {
                            $thumbnail = get_the_post_thumbnail(get_the_ID(), 'large', array(
                                'class' => 'max-w-[90%] max-h-[90%] object-contain rounded-2xl group-hover:scale-110 transition-transform duration-700'
                            ));
                            
                            if (empty($thumbnail)) {
                                $thumbnail = get_the_post_thumbnail(get_the_ID(), 'medium_large', array(
                                    'class' => 'max-w-[90%] max-h-[90%] object-contain rounded-2xl group-hover:scale-110 transition-transform duration-700'
                                ));
                            }
                            if (empty($thumbnail)) {
                                $thumbnail = get_the_post_thumbnail(get_the_ID(), 'full', array(
                                    'class' => 'max-w-[90%] max-h-[90%] object-contain rounded-2xl group-hover:scale-110 transition-transform duration-700'
                                ));
                            }
                        }
                        
                        error_log("Fleet page vehicle " . get_the_ID() . " thumbnail: " . strlen($thumbnail) . " bytes");
                        
                        $vehicles[] = array(
                            'post_id'    => get_the_ID(),
                            'title'      => get_the_title(),
                            'thumbnail'  => $thumbnail,
                            'excerpt'    => get_the_excerpt(),
                            'permalink'  => get_the_permalink(),
                            'multiplier' => $multiplier,
                            'passengers' => $passengers,
                            'luggage'    => $luggage,
                        );
                    }
                    
                    echo '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 w-full">';
                    
                    foreach ($vehicles as $vehicle) {
                        ?>
                        <div class="vehicle-card group bg-white border border-gray-100 rounded-[2rem] overflow-hidden hover:shadow-2xl transition-all duration-500 flex flex-col h-full shadow-sm">
                            
                            <div class="relative p-6 h-56 flex items-center justify-center overflow-hidden bg-white">
                                <?php if (!empty($vehicle['thumbnail'])) : ?>
                                    <?php echo $vehicle['thumbnail']; ?>
                                <?php endif; ?>
                                
                                <div class="absolute top-4 right-4">
                                    <span class="bg-[var(--primary-color)] text-black text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-tighter"><?php _e('Premium', 'airlinel-theme'); ?></span>
                                </div>
                            </div>
                            
                            <div class="p-6 flex flex-col flex-grow bg-white">
                                <div class="mb-4">
                                    <h3 class="text-xl font-bold text-gray-900 leading-tight mb-1"><?php echo esc_html($vehicle['title']); ?></h3>
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                        <p class="text-gray-400 text-xs uppercase tracking-widest font-medium"><?php _e('Available Now', 'airlinel-theme'); ?></p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3 mb-6">
                                    <div class="bg-gray-50 border border-gray-100 p-3 rounded-2xl flex items-center justify-center gap-3">
                                        <i class="fa fa-user text-[var(--primary-color)] text-sm"></i>
                                        <span class="text-gray-700 text-xs font-bold"><?php echo $vehicle['passengers']; ?> <?php _e('Passengers', 'airlinel-theme'); ?></span>
                                    </div>
                                    <div class="bg-gray-50 border border-gray-100 p-3 rounded-2xl flex items-center justify-center gap-3">
                                        <i class="fa fa-briefcase text-[var(--primary-color)] text-sm"></i>
                                        <span class="text-gray-700 text-xs font-bold"><?php echo $vehicle['luggage']; ?> <?php _e('Luggage', 'airlinel-theme'); ?></span>
                                    </div>
                                </div>

                                <?php if (!empty($vehicle['excerpt'])) : ?>
                                    <div class="mb-6 pb-6 border-t border-gray-100">
                                        <p class="text-gray-600 text-sm leading-relaxed">
                                            <?php echo wp_trim_words($vehicle['excerpt'], 20); ?>
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <div class="mt-auto pt-5 border-t border-gray-100 flex flex-col gap-3">
                                    <a href="<?php echo $vehicle['permalink']; ?>"
                                       class="w-full inline-flex items-center justify-center bg-black hover:bg-[var(--primary-color)] text-white hover:text-black font-black py-3 px-6 rounded-2xl transition-all duration-300 text-xs uppercase tracking-widest shadow-lg active:scale-95">
                                        <?php _e('View Full Details', 'airlinel-theme'); ?>
                                        <i class="fa-solid fa-arrow-right ml-2"></i>
                                    </a>
                                    <a href="<?php echo home_url('/book-your-ride'); ?>"
                                       class="w-full inline-flex items-center justify-center bg-[var(--primary-color)] text-white font-black py-3 px-6 rounded-2xl transition-all duration-300 text-xs uppercase tracking-widest shadow-lg active:scale-95 hover:shadow-lg">
                                        <?php _e('Book This Vehicle', 'airlinel-theme'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    
                    echo '</div>';
                } else {
                    echo '<div class="text-center py-20"><p class="text-white text-lg">' . __('No vehicles in fleet at this time.', 'airlinel-theme') . '</p></div>';
                }
                wp_reset_postdata();
            ?>
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



<!-- Fleet Features Section -->
<section class="py-20 sm:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="font-[var(--font-family-heading)] text-4xl sm:text-5xl lg:text-6xl font-bold text-[var(--dark-text-color)] mb-6">
                <?php _e('Why Choose Our Fleet?', 'airlinel-theme'); ?>
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="group bg-gradient-to-br from-[var(--light-background-color)] to-white rounded-2xl p-8 hover:shadow-xl transition-all duration-300 border border-[var(--light-border-color)]">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-gradient-to-br from-[var(--primary-color)] to-[var(--accent-color)] flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <i class="fa-solid fa-crown text-xl text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-[var(--dark-text-color)] mb-2"><?php _e('Premium Selection', 'airlinel-theme'); ?></h3>
                        <p class="text-[var(--gray-text-color)] leading-relaxed"><?php _e('Hand-curated vehicles maintained to the highest standards.', 'airlinel-theme'); ?></p>
                    </div>
                </div>
            </div>

            <div class="group bg-gradient-to-br from-[var(--light-background-color)] to-white rounded-2xl p-8 hover:shadow-xl transition-all duration-300 border border-[var(--light-border-color)]">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-gradient-to-br from-[var(--primary-color)] to-[var(--accent-color)] flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <i class="fa-solid fa-shield-halved text-xl text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-[var(--dark-text-color)] mb-2"><?php _e('Fully Insured', 'airlinel-theme'); ?></h3>
                        <p class="text-[var(--gray-text-color)] leading-relaxed"><?php _e('Complete coverage on all vehicles with comprehensive insurance.', 'airlinel-theme'); ?></p>
                    </div>
                </div>
            </div>

            <div class="group bg-gradient-to-br from-[var(--light-background-color)] to-white rounded-2xl p-8 hover:shadow-xl transition-all duration-300 border border-[var(--light-border-color)]">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-gradient-to-br from-[var(--primary-color)] to-[var(--accent-color)] flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <i class="fa-solid fa-headset text-xl text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-[var(--dark-text-color)] mb-2"><?php _e('Expert Chauffeurs', 'airlinel-theme'); ?></h3>
                        <p class="text-[var(--gray-text-color)] leading-relaxed"><?php _e('Professionally trained drivers with extensive experience.', 'airlinel-theme'); ?></p>
                    </div>
                </div>
            </div>

            <div class="group bg-gradient-to-br from-[var(--light-background-color)] to-white rounded-2xl p-8 hover:shadow-xl transition-all duration-300 border border-[var(--light-border-color)]">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-gradient-to-br from-[var(--primary-color)] to-[var(--accent-color)] flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <i class="fa-solid fa-clock text-xl text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-[var(--dark-text-color)] mb-2"><?php _e('Always On Time', 'airlinel-theme'); ?></h3>
                        <p class="text-[var(--gray-text-color)] leading-relaxed"><?php _e('Punctuality guaranteed with real-time tracking.', 'airlinel-theme'); ?></p>
                    </div>
                </div>
            </div>

            <div class="group bg-gradient-to-br from-[var(--light-background-color)] to-white rounded-2xl p-8 hover:shadow-xl transition-all duration-300 border border-[var(--light-border-color)]">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-gradient-to-br from-[var(--primary-color)] to-[var(--accent-color)] flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <i class="fa-solid fa-leaf text-xl text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-[var(--dark-text-color)] mb-2"><?php _e('Eco-Conscious', 'airlinel-theme'); ?></h3>
                        <p class="text-[var(--gray-text-color)] leading-relaxed"><?php _e('Modern vehicles with reduced emissions.', 'airlinel-theme'); ?></p>
                    </div>
                </div>
            </div>

            <div class="group bg-gradient-to-br from-[var(--light-background-color)] to-white rounded-2xl p-8 hover:shadow-xl transition-all duration-300 border border-[var(--light-border-color)]">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-gradient-to-br from-[var(--primary-color)] to-[var(--accent-color)] flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <i class="fa-solid fa-wifi text-xl text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-[var(--dark-text-color)] mb-2"><?php _e('Connected Travel', 'airlinel-theme'); ?></h3>
                        <p class="text-[var(--gray-text-color)] leading-relaxed"><?php _e('WiFi and premium amenities in every vehicle.', 'airlinel-theme'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 sm:py-24 bg-gradient-to-r from-[var(--dark-background-color)] to-black">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="font-[var(--font-family-heading)] text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-6">
            <?php _e('Experience Premium Transportation', 'airlinel-theme'); ?>
        </h2>
        <p class="text-lg sm:text-xl text-gray-300 max-w-2xl mx-auto mb-8">
            <?php _e('Book one of our premium vehicles today and discover the Airlinel difference.', 'airlinel-theme'); ?>
        </p>
        <a href="<?php echo home_url('/book-your-ride'); ?>" class="inline-flex items-center justify-center px-10 py-5 text-lg font-bold text-white bg-[var(--primary-color)] rounded-full hover:bg-[var(--primary-button-hover-bg-color)] transition-all duration-300 hover:shadow-2xl hover:shadow-[var(--primary-color)]/50 hover:-translate-y-1">
            <?php _e('Book Your Ride Now', 'airlinel-theme'); ?>
            <i class="fa-solid fa-arrow-right ml-3"></i>
        </a>
    </div>
</section>

<?php get_footer(); ?>
