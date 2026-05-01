<footer id="global-footer" class="code-section relative bg-gradient-to-b from-[var(--dark-background-color)] to-black border-t border-gray-800">

    <!-- ══ CTA BAND ══ -->
    <div class="border-b border-gray-800 py-20 sm:py-24">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-xs font-bold tracking-widest uppercase mb-4" style="color: var(--primary-color);">
                <?php _e('UK &amp; Turkey &middot; 24 / 7 &middot; Fixed Rates &middot; Meet &amp; Greet', 'airlinel-theme'); ?>
            </p>
            <h2 class="font-bold text-white mb-8" style="font-family: var(--font-family-heading); font-size: clamp(2rem, 5vw, 3.5rem); line-height: 1.1;">
                <?php _e('Upgrade the Way You Travel.', 'airlinel-theme'); ?>
            </h2>
            <a href="<?php echo esc_url( home_url('/book-your-ride') ); ?>"
               class="inline-flex items-center justify-center px-12 py-5 text-lg font-bold text-white rounded-full transition-all duration-300"
               style="background: var(--primary-color);"
               onmouseover="this.style.background='var(--primary-button-hover-bg-color)';this.style.transform='translateY(-2px)';"
               onmouseout="this.style.background='var(--primary-color)';this.style.transform='translateY(0)';">
                <?php _e('Book Your Ride', 'airlinel-theme'); ?>
                <i class="fa-solid fa-arrow-right ml-3" aria-hidden="true"></i>
            </a>
        </div>
    </div>

    <!-- ══ SEO LINK GRID (5 cols × 5 rows — dynamic latest posts per themed column) ══ -->
    <div class="border-b border-gray-800 py-16 sm:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php
            /**
             * 5 themed columns — each tries to match a WordPress category by slug.
             * If no matching category exists, the column falls back to the 5 most
             * recent posts site-wide (de-duplicated across columns).
             */
            $footer_col_defs = array(
                array(
                    'title' => __( 'London Transfers', 'airlinel-theme' ),
                    'slugs' => array( 'london-transfers', 'london', 'london-airport', 'heathrow', 'gatwick' ),
                ),
                array(
                    'title' => __( 'UK Regional', 'airlinel-theme' ),
                    'slugs' => array( 'uk-regional', 'uk', 'united-kingdom', 'manchester', 'birmingham', 'scotland' ),
                ),
                array(
                    'title' => __( 'Turkey Transfers', 'airlinel-theme' ),
                    'slugs' => array( 'turkey-transfers', 'turkey', 'turkiye', 'istanbul', 'antalya', 'turkish' ),
                ),
                array(
                    'title' => __( 'Corporate', 'airlinel-theme' ),
                    'slugs' => array( 'corporate', 'business', 'corporate-chauffeur', 'executive', 'vip' ),
                ),
                array(
                    'title' => __( 'Services', 'airlinel-theme' ),
                    'slugs' => array( 'services', 'service', 'airport-transfer', 'chauffeur', 'transfers' ),
                ),
            );

            // Cache all published posts once to avoid multiple queries
            $all_recent = get_posts( array(
                'numberposts' => 50,
                'post_status' => 'publish',
                'orderby'     => 'date',
                'order'       => 'DESC',
            ) );

            $used_ids = array();

            echo '<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-10 lg:gap-12">';

            foreach ( $footer_col_defs as $col ) {

                // Try each slug until we find a category with posts
                $col_posts = array();
                foreach ( $col['slugs'] as $slug ) {
                    $cat = get_category_by_slug( $slug );
                    if ( $cat && ! is_wp_error( $cat ) ) {
                        $col_posts = get_posts( array(
                            'numberposts' => 5,
                            'category'    => $cat->term_id,
                            'post_status' => 'publish',
                            'orderby'     => 'date',
                            'order'       => 'DESC',
                        ) );
                        if ( ! empty( $col_posts ) ) break;
                    }
                }

                // Fallback: pick next 5 unused recent posts
                if ( empty( $col_posts ) ) {
                    $col_posts = array();
                    foreach ( $all_recent as $rp ) {
                        if ( ! in_array( $rp->ID, $used_ids, true ) ) {
                            $col_posts[] = $rp;
                            if ( count( $col_posts ) >= 5 ) break;
                        }
                    }
                }

                // Track used IDs to avoid duplicates in fallback columns
                foreach ( $col_posts as $cp ) { $used_ids[] = $cp->ID; }

                echo '<div>';
                echo '<h3 class="font-bold text-white text-xs uppercase tracking-widest mb-4 pb-2 border-b border-gray-700" style="font-family: var(--font-family-heading);">'
                     . esc_html( $col['title'] ) . '</h3>';
                echo '<ul class="space-y-2">';
                foreach ( $col_posts as $fp ) {
                    echo '<li><a href="' . esc_url( get_permalink( $fp->ID ) ) . '" class="text-gray-400 hover:text-white transition-colors text-sm">'
                         . esc_html( get_the_title( $fp->ID ) ) . '</a></li>';
                }
                echo '</ul></div>';
            }

            echo '</div>';
            ?>
        </div>
    </div>

    <!-- ══ MAIN FOOTER BODY ══ -->
    <?php
    // Main site detection: links in this section always point to airlinel.com
    // On the main site itself, use relative home_url(); on regional sites, use the absolute main URL.
    $airlinel_main = 'https://airlinel.com';
    $current_host  = parse_url( home_url(), PHP_URL_HOST );
    $is_main_site  = in_array( $current_host, array( 'airlinel.com', 'www.airlinel.com' ), true );
    $fl            = $is_main_site ? '' : $airlinel_main; // prefix for footer links
    ?>
    <div class="border-b border-gray-800 py-16 sm:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-12">

                <!-- Brand + Contact -->
                <div class="lg:col-span-2">
                    <a href="<?php echo esc_url( home_url('/') ); ?>" class="inline-block mb-5">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/theme-image-001.webp"
                             alt="<?php bloginfo('name'); ?> – London Airport Transfer &amp; Chauffeur Service"
                             loading="lazy" class="h-24 py-1 w-auto" data-logo="">
                    </a>
                    <p class="text-gray-400 text-sm leading-relaxed mb-6 max-w-xs">
                        <?php _e('Premium airport transfers &amp; chauffeur services across the UK and Turkey. Professional drivers, fixed rates, 24/7.', 'airlinel-theme'); ?>
                    </p>

                    <!-- Email (shared) -->
                    <a href="mailto:booking@airlinel.com" class="inline-flex items-center gap-2 text-gray-400 hover:text-white transition-colors text-sm mb-6">
                        <i class="fa-solid fa-envelope flex-shrink-0" style="color: var(--primary-color);" aria-hidden="true"></i>
                        booking@airlinel.com
                    </a>

                    <!-- Office list -->
                    <ul class="space-y-5 text-sm">

                        <!-- London -->
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-location-dot mt-1 flex-shrink-0" style="color: var(--primary-color);" aria-hidden="true"></i>
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-0.5">🇬🇧 London</span>
                                <span class="text-gray-400 leading-relaxed">86-90 Paul Street, London, EC2A 4NE</span><br>
                                <a href="tel:+442034112421" class="text-gray-400 hover:text-white transition-colors">+44 20 3411 2421</a>
                            </div>
                        </li>

                        <!-- Kemer -->
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-location-dot mt-1 flex-shrink-0" style="color: var(--primary-color);" aria-hidden="true"></i>
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-0.5">🇹🇷 Kemer, Antalya</span>
                                <span class="text-gray-400 leading-relaxed">Merkez Mah. 128 Sok. No:7, Kemer / Antalya</span><br>
                                <a href="tel:+902422120430" class="text-gray-400 hover:text-white transition-colors">+90 242 212 0430</a>
                            </div>
                        </li>

                        <!-- Muratpaşa -->
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-location-dot mt-1 flex-shrink-0" style="color: var(--primary-color);" aria-hidden="true"></i>
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-0.5">🇹🇷 Muratpaşa, Antalya</span>
                                <span class="text-gray-400 leading-relaxed">Meltem 3834 Özgün Sitesi B1 Blok No:6A, Muratpaşa / Antalya</span><br>
                                <a href="tel:+902422120430" class="text-gray-400 hover:text-white transition-colors">+90 242 212 0430</a>
                            </div>
                        </li>

                    </ul>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="font-bold text-white text-xs uppercase tracking-widest mb-5"
                        style="font-family: var(--font-family-heading);">
                        <?php _e('Quick Links', 'airlinel-theme'); ?>
                    </h3>
                    <ul class="space-y-2">
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/"><?php _e('Home', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/about-us/"><?php _e('About Us', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/service-rates/"><?php _e('Service Rates', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/our-fleet/"><?php _e('Our Fleet', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/cities/"><?php _e('Cities', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo esc_url( home_url('/book-your-ride/') ); ?>"><?php _e('Book Your Ride', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/testimonials/"><?php _e('Testimonials', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/frequently-asked-questions/"><?php _e('Questions &amp; Answers', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/photo-gallery/"><?php _e('Photo Gallery', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/video-gallery/"><?php _e('Video Gallery', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/partners/"><?php _e('Partners', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/news/"><?php _e('News', 'airlinel-theme'); ?></span></li>
                    </ul>
                </div>

                <!-- Company & Contact -->
                <div>
                    <h3 class="font-bold text-white text-xs uppercase tracking-widest mb-5"
                        style="font-family: var(--font-family-heading);">
                        <?php _e('Company', 'airlinel-theme'); ?>
                    </h3>
                    <ul class="space-y-2">
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/about-us/contact-us/"><?php _e('Contact — United Kingdom', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/about-us/contact-turkiye/"><?php _e('Contact — Türkiye', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/career/"><?php _e('Career', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/career/driver-job-application/"><?php _e('Driver Job Application', 'airlinel-theme'); ?></span></li>
                    </ul>
                </div>

                <!-- Legal & Policy -->
                <div>
                    <h3 class="font-bold text-white text-xs uppercase tracking-widest mb-5"
                        style="font-family: var(--font-family-heading);">
                        <?php _e('Legal &amp; Policy', 'airlinel-theme'); ?>
                    </h3>
                    <ul class="space-y-2">
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/about-us/terms-and-conditions/"><?php _e('Terms &amp; Conditions', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/privacy-policy/"><?php _e('Privacy Policy', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/about-us/refund-cancellation-policy/"><?php _e('Refund &amp; Cancellation', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/about-us/publishing-principles/"><?php _e('Publishing Principles', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/about-us/ethics-policy/"><?php _e('Ethics Policy', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/about-us/diversity-policy/"><?php _e('Diversity Policy', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/about-us/diversity-staffing-report/"><?php _e('Diversity Staffing Report', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/about-us/ownership-and-funding-information/"><?php _e('Ownership &amp; Funding', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/about-us/corrections-policy/"><?php _e('Corrections Policy', 'airlinel-theme'); ?></span></li>
                        <li><span class="footer-js-link text-gray-400 hover:text-white transition-colors text-sm cursor-pointer" data-href="<?php echo $fl; ?>/about-us/actionable-feedback-policy/"><?php _e('Actionable Feedback Policy', 'airlinel-theme'); ?></span></li>
                    </ul>
                </div>

            </div>
        </div>
    </div>
    <script>
    // Footer JS navigation — no href, click dispatches location change
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.footer-js-link').forEach(function(el) {
            el.addEventListener('click', function() {
                var href = this.getAttribute('data-href');
                if (href) window.location.href = href;
            });
        });
    });
    </script>

    <!-- ══ BOTTOM BAR ══ -->
    <!-- ── Trust Bar ── -->
    <div class="border-b border-gray-800 py-5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-center gap-x-8 gap-y-3">

                <span class="flex items-center gap-2 text-gray-400 text-xs font-medium">
                    <i class="fa-solid fa-shield-halved text-sm" style="color: var(--primary-color);"></i>
                    <?php _e('TfL Licensed', 'airlinel-theme'); ?>
                </span>

                <span class="hidden sm:block w-px h-3 bg-gray-700"></span>

                <span class="flex items-center gap-2 text-gray-400 text-xs font-medium">
                    <i class="fa-solid fa-certificate text-sm" style="color: var(--primary-color);"></i>
                    TURSAB A-9163
                </span>

                <span class="hidden sm:block w-px h-3 bg-gray-700"></span>

                <span class="flex items-center gap-2 text-gray-400 text-xs font-medium">
                    <i class="fa-solid fa-star text-sm" style="color: var(--primary-color);"></i>
                    <?php _e('5-Star Rated', 'airlinel-theme'); ?>
                </span>

                <span class="hidden sm:block w-px h-3 bg-gray-700"></span>

                <span class="flex items-center gap-2 text-gray-400 text-xs font-medium">
                    <i class="fa-solid fa-lock text-sm" style="color: var(--primary-color);"></i>
                    <?php _e('Secure Payments', 'airlinel-theme'); ?>
                </span>

            </div>
        </div>
    </div>

    <!-- ── Copyright ── -->
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 flex-wrap">

                <!-- Copyright + Company Nos — both lines start at the same column -->
                <div class="text-gray-600 text-xs text-left">
                    <p class="text-gray-500 mb-1.5">&copy; <?php echo date('Y'); ?> <strong>Airlinel</strong></p>
                    <div class="space-y-0.5">
                        <p>
                            <strong class="text-gray-500">LONDONOS LTD</strong>
                            &middot; Registered in England &amp; Wales &middot; Co. No. 12665318
                        </p>
                        <p>
                            <strong class="text-gray-500">GGG Güvençoğlu Turizm İnş. Bil. Ter. Hiz. San. ve Tic. Ltd. Şti.</strong>
                            &middot; Registered in Antalya, Türkiye &middot; Co. No. 3950930242
                        </p>
                    </div>
                </div>

                <!-- Social Icons — only render if URL is set in WP Admin > Airlinel Settings -->
                <?php
                $soc_fb = get_option('airlinel_social_facebook',  '');
                $soc_ig = get_option('airlinel_social_instagram', '');
                $soc_li = get_option('airlinel_social_linkedin',  '');
                $soc_tw = get_option('airlinel_social_twitter',   '');
                $icon_cls = 'w-9 h-9 rounded-full bg-gray-800 hover:bg-[var(--primary-color)] flex items-center justify-center text-gray-400 hover:text-white transition-all duration-300';
                if ( $soc_fb || $soc_ig || $soc_li || $soc_tw ) : ?>
                <div class="flex items-center gap-3">
                    <?php if ( $soc_fb ) : ?>
                    <a href="<?php echo esc_url($soc_fb); ?>" target="_blank" rel="noopener noreferrer" class="<?php echo $icon_cls; ?>" aria-label="Facebook">
                        <i class="fa-brands fa-facebook-f text-xs" aria-hidden="true"></i>
                    </a>
                    <?php endif; ?>
                    <?php if ( $soc_ig ) : ?>
                    <a href="<?php echo esc_url($soc_ig); ?>" target="_blank" rel="noopener noreferrer" class="<?php echo $icon_cls; ?>" aria-label="Instagram">
                        <i class="fa-brands fa-instagram text-xs" aria-hidden="true"></i>
                    </a>
                    <?php endif; ?>
                    <?php if ( $soc_li ) : ?>
                    <a href="<?php echo esc_url($soc_li); ?>" target="_blank" rel="noopener noreferrer" class="<?php echo $icon_cls; ?>" aria-label="LinkedIn">
                        <i class="fa-brands fa-linkedin-in text-xs" aria-hidden="true"></i>
                    </a>
                    <?php endif; ?>
                    <?php if ( $soc_tw ) : ?>
                    <a href="<?php echo esc_url($soc_tw); ?>" target="_blank" rel="noopener noreferrer" class="<?php echo $icon_cls; ?>" aria-label="X / Twitter">
                        <i class="fa-brands fa-x-twitter text-xs" aria-hidden="true"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

</footer>

      </div> </div> </div> <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButton = document.querySelector('[data-landingsite-mobile-menu-toggle]');
        const mobileMenu = document.querySelector('[data-landingsite-mobile-menu]');
        
        if(toggleButton && mobileMenu) {
            toggleButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });
        }
    });
  </script>

<?php wp_footer(); ?>
</body>
</html>
