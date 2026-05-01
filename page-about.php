<?php /* Template Name: About Us */
get_header();
require_once get_template_directory() . '/includes/class-page-manager.php';
$page_mgr = new Airlinel_Page_Manager();
?>

<!-- Hero Section -->
<section class="pt-32 pb-20 sm:pt-40 sm:pb-32 bg-gradient-to-br from-[var(--dark-background-color)] to-black text-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- Content -->
            <div>
                <h1 class="font-[var(--font-family-heading)] text-5xl sm:text-6xl lg:text-7xl font-bold mb-6 leading-tight">
                    <?php _e('About Airlinel', 'airlinel-theme'); ?>
                </h1>
                <p class="text-lg sm:text-xl text-gray-300 mb-8 leading-relaxed">
                    <?php echo esc_html($page_mgr->get_company_description()); ?>
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="<?php echo home_url('/book-your-ride'); ?>" class="inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-white bg-[var(--primary-color)] rounded-full hover:bg-[var(--primary-button-hover-bg-color)] transition-all duration-300 hover:shadow-2xl hover:shadow-[var(--primary-color)]/50 hover:-translate-y-1">
                        <?php _e('Book Now', 'airlinel-theme'); ?>
                        <i class="fa-solid fa-arrow-right ml-3"></i>
                    </a>
                    <a href="<?php echo home_url('/contact'); ?>" class="inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-white border-2 border-white rounded-full hover:bg-white hover:text-[var(--dark-background-color)] transition-all duration-300">
                        <?php _e('Get in Touch', 'airlinel-theme'); ?>
                        <i class="fa-solid fa-envelope ml-3"></i>
                    </a>
                </div>
            </div>

            <!-- Image -->
            <div class="relative hidden lg:block">
                <div class="aspect-square rounded-3xl overflow-hidden shadow-2xl">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-007.jpg" alt="Airlinel professional chauffeur" class="w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Values Section -->
<section class="py-20 sm:py-32 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <!-- Mission -->
            <div class="lg:col-span-2">
                <h2 class="font-[var(--font-family-heading)] text-4xl sm:text-5xl font-bold text-[var(--dark-text-color)] mb-6">
                    <?php _e('Our Mission', 'airlinel-theme'); ?>
                </h2>
                <p class="text-lg text-gray-700 leading-relaxed mb-8">
                    <?php echo esc_html($page_mgr->get_company_mission()); ?>
                </p>

                <!-- Core Values -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">
                    <div class="p-6 bg-gray-50 rounded-2xl">
                        <div class="text-4xl mb-4">
                            <i class="fa-solid fa-handshake text-[var(--primary-color)]"></i>
                        </div>
                        <h3 class="font-bold text-xl text-[var(--dark-text-color)] mb-3"><?php _e('Reliability', 'airlinel-theme'); ?></h3>
                        <p class="text-gray-600"><?php _e('On-time arrivals and dependable service you can trust.', 'airlinel-theme'); ?></p>
                    </div>
                    <div class="p-6 bg-gray-50 rounded-2xl">
                        <div class="text-4xl mb-4">
                            <i class="fa-solid fa-star text-[var(--primary-color)]"></i>
                        </div>
                        <h3 class="font-bold text-xl text-[var(--dark-text-color)] mb-3"><?php _e('Excellence', 'airlinel-theme'); ?></h3>
                        <p class="text-gray-600"><?php _e('Consistently delivering premium service standards.', 'airlinel-theme'); ?></p>
                    </div>
                    <div class="p-6 bg-gray-50 rounded-2xl">
                        <div class="text-4xl mb-4">
                            <i class="fa-solid fa-heart text-[var(--primary-color)]"></i>
                        </div>
                        <h3 class="font-bold text-xl text-[var(--dark-text-color)] mb-3"><?php _e('Customer Care', 'airlinel-theme'); ?></h3>
                        <p class="text-gray-600"><?php _e('Your satisfaction is our top priority.', 'airlinel-theme'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-gradient-to-br from-[var(--light-background-color)] to-[var(--medium-background-color)] p-8 rounded-3xl h-fit">
                <h3 class="font-[var(--font-family-heading)] text-2xl font-bold text-[var(--dark-text-color)] mb-8"><?php _e('By The Numbers', 'airlinel-theme'); ?></h3>
                <?php
                $indicators = $page_mgr->get_trust_indicators();
                ?>
                <div class="space-y-6">
                    <div>
                        <div class="text-3xl font-bold text-[var(--primary-color)]">
                            <?php echo esc_html($indicators['years_in_business']); ?>+
                        </div>
                        <p class="text-gray-600"><?php _e('Years in Business', 'airlinel-theme'); ?></p>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-[var(--primary-color)]">
                            <?php echo esc_html($indicators['customers_served']); ?>+
                        </div>
                        <p class="text-gray-600"><?php _e('Happy Customers', 'airlinel-theme'); ?></p>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-[var(--primary-color)]">
                            <?php echo esc_html($indicators['fleet_size']); ?>+
                        </div>
                        <p class="text-gray-600"><?php _e('Vehicles in Fleet', 'airlinel-theme'); ?></p>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-[var(--primary-color)]">
                            <?php echo esc_html($indicators['daily_rides']); ?>+
                        </div>
                        <p class="text-gray-600"><?php _e('Daily Rides', 'airlinel-theme'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- History Section -->
<section class="py-20 sm:py-32 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="font-[var(--font-family-heading)] text-4xl sm:text-5xl font-bold text-[var(--dark-text-color)] mb-12 text-center">
            <?php _e('Our Story', 'airlinel-theme'); ?>
        </h2>
        <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
            <p><?php echo esc_html($page_mgr->get_company_history()); ?></p>
        </div>
    </div>
</section>

<!-- Team Section (if content is available) -->
<?php if (get_the_content()) : ?>
<section class="py-20 sm:py-32 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="prose prose-lg max-w-none text-gray-700">
            <?php the_content(); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Trust Indicators Section -->
<section class="py-20 sm:py-32 bg-gradient-to-r from-[var(--dark-background-color)] to-black text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="font-[var(--font-family-heading)] text-4xl sm:text-5xl font-bold mb-12 text-center">
            <?php _e('Why Choose Airlinel?', 'airlinel-theme'); ?>
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="text-center">
                <div class="text-5xl font-bold text-[var(--primary-color)] mb-3">
                    <i class="fa-solid fa-award"></i>
                </div>
                <h3 class="text-xl font-bold mb-2"><?php _e('Industry Certified', 'airlinel-theme'); ?></h3>
                <p class="text-gray-300"><?php _e('Licensed and fully insured for your peace of mind.', 'airlinel-theme'); ?></p>
            </div>
            <div class="text-center">
                <div class="text-5xl font-bold text-[var(--primary-color)] mb-3">
                    <i class="fa-solid fa-shield"></i>
                </div>
                <h3 class="text-xl font-bold mb-2"><?php _e('Safety First', 'airlinel-theme'); ?></h3>
                <p class="text-gray-300"><?php _e('Professional drivers with extensive background checks.', 'airlinel-theme'); ?></p>
            </div>
            <div class="text-center">
                <div class="text-5xl font-bold text-[var(--primary-color)] mb-3">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <h3 class="text-xl font-bold mb-2"><?php _e('24/7 Available', 'airlinel-theme'); ?></h3>
                <p class="text-gray-300"><?php _e('Round-the-clock service for all your transfer needs.', 'airlinel-theme'); ?></p>
            </div>
            <div class="text-center">
                <div class="text-5xl font-bold text-[var(--primary-color)] mb-3">
                    <i class="fa-solid fa-star"></i>
                </div>
                <h3 class="text-xl font-bold mb-2"><?php _e('5-Star Rated', 'airlinel-theme'); ?></h3>
                <p class="text-gray-300"><?php _e('Consistently praised by our satisfied customers.', 'airlinel-theme'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 sm:py-24 bg-gradient-to-r from-[var(--primary-color)] to-[var(--accent2-color)]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="font-[var(--font-family-heading)] text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-6">
            <?php _e('Ready to Experience Premium Service?', 'airlinel-theme'); ?>
        </h2>
        <p class="text-lg sm:text-xl text-white/90 max-w-2xl mx-auto mb-8">
            <?php _e('Join thousands of satisfied customers who trust Airlinel for their airport transfers.', 'airlinel-theme'); ?>
        </p>
        <a href="<?php echo home_url('/book-your-ride'); ?>" class="inline-flex items-center justify-center px-10 py-5 text-lg font-bold text-[var(--primary-color)] bg-white rounded-full hover:bg-gray-100 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
            <?php _e('Book Your Transfer Today', 'airlinel-theme'); ?>
            <i class="fa-solid fa-arrow-right ml-3"></i>
        </a>
    </div>
</section>

<?php get_footer(); ?>
