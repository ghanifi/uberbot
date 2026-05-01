<?php
/**
 * Default Content for Homepage Sections
 * Provides fallback content when custom content is not configured
 */

/**
 * Get default content for Featured Routes section
 */
function airlinel_get_default_featured_routes() {
    return '<div class="featured-routes-default">
        <p>Explore our most popular airport transfer routes with premium service and competitive pricing.</p>
        <ul style="margin-top: 15px; line-height: 1.8;">
            <li><strong>London → Heathrow Airport:</strong> Fast, reliable transfers with real-time flight tracking</li>
            <li><strong>London → Gatwick Airport:</strong> Premium service to South London and beyond</li>
            <li><strong>London → Stansted Airport:</strong> Express routes to East Anglia and regional airports</li>
            <li><strong>London → Luton Airport:</strong> Convenient transfers to North London and surrounding areas</li>
            <li><strong>London → London City Airport:</strong> Quick urban transfers for business travelers</li>
        </ul>
    </div>';
}

/**
 * Get default content for Customer Testimonials section
 */
function airlinel_get_default_customer_testimonials() {
    return '<div class="testimonials-default">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
            <div style="padding: 20px; background: #f9f9f9; border-left: 4px solid var(--primary-color); border-radius: 4px;">
                <div style="margin-bottom: 10px; color: #ffc107;">★★★★★</div>
                <p style="margin: 10px 0; font-style: italic;">"Excellent service! The driver was professional, the vehicle was immaculate, and I arrived on time. Highly recommended!"</p>
                <p style="margin: 10px 0; font-weight: 600;">- Sarah Johnson, London</p>
            </div>
            <div style="padding: 20px; background: #f9f9f9; border-left: 4px solid var(--primary-color); border-radius: 4px;">
                <div style="margin-bottom: 10px; color: #ffc107;">★★★★★</div>
                <p style="margin: 10px 0; font-style: italic;">"Best airport transfer service I\'ve used. No hidden fees, professional staff, and real-time tracking. Worth every penny!"</p>
                <p style="margin: 10px 0; font-weight: 600;">- Michael Chen, Business Traveler</p>
            </div>
            <div style="padding: 20px; background: #f9f9f9; border-left: 4px solid var(--primary-color); border-radius: 4px;">
                <div style="margin-bottom: 10px; color: #ffc107;">★★★★★</div>
                <p style="margin: 10px 0; font-style: italic;">"Made my airport experience stress-free. The booking was simple, the driver was courteous, and the rate was competitive."</p>
                <p style="margin: 10px 0; font-weight: 600;">- Emma Watson, Frequent Flyer</p>
            </div>
        </div>
    </div>';
}

/**
 * Get default content for Service Highlights section
 */
function airlinel_get_default_service_highlights() {
    return '<div class="service-highlights-default">
        <p>What makes Airlinel the preferred choice for airport transfers:</p>
        <ul style="margin-top: 15px; line-height: 2;">
            <li>✓ <strong>24/7 Global Support:</strong> Always available in multiple languages</li>
            <li>✓ <strong>Professional Chauffeurs:</strong> Executive-level training and impeccable presentation</li>
            <li>✓ <strong>Real-Time Flight Tracking:</strong> Automatic pickup adjustments based on flight status</li>
            <li>✓ <strong>Transparent Pricing:</strong> No hidden fees, no surge pricing</li>
            <li>✓ <strong>Luxury Fleet:</strong> Modern vehicles maintained to showroom standards</li>
            <li>✓ <strong>Instant Confirmation:</strong> Receive booking details immediately</li>
            <li>✓ <strong>Meet & Greet Service:</strong> Drivers greet you with a name board at arrivals</li>
            <li>✓ <strong>On-Time Guarantee:</strong> Professional punctuality for every journey</li>
        </ul>
    </div>';
}

/**
 * Get default content for Trust Signals section
 */
function airlinel_get_default_trust_signals() {
    return '<div class="trust-signals-default">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; text-align: center; margin-top: 20px;">
            <div>
                <div style="font-size: 36px; margin-bottom: 10px;">🏆</div>
                <p style="font-weight: 600; margin-bottom: 5px;">Licensed & Insured</p>
                <p style="font-size: 14px; color: #666;">Full regulatory compliance and comprehensive insurance coverage</p>
            </div>
            <div>
                <div style="font-size: 36px; margin-bottom: 10px;">✓</div>
                <p style="font-weight: 600; margin-bottom: 5px;">Trusted by Thousands</p>
                <p style="font-size: 14px; color: #666;">Serving over 50,000 satisfied customers worldwide</p>
            </div>
            <div>
                <div style="font-size: 36px; margin-bottom: 10px;">🔒</div>
                <p style="font-weight: 600; margin-bottom: 5px;">Secure & Safe</p>
                <p style="font-size: 14px; color: #666;">Advanced safety features and passenger protection</p>
            </div>
            <div>
                <div style="font-size: 36px; margin-bottom: 10px;">⭐</div>
                <p style="font-weight: 600; margin-bottom: 5px;">5-Star Rated</p>
                <p style="font-size: 14px; color: #666;">Consistently high ratings from verified customers</p>
            </div>
        </div>
    </div>';
}

/**
 * Get default content for Special Offers section
 */
function airlinel_get_default_special_offers() {
    return '<div class="special-offers-default">
        <div style="padding: 30px; background: linear-gradient(135deg, var(--primary-color), var(--accent-color)); color: white; border-radius: 8px; text-align: center;">
            <h3 style="margin: 0 0 15px 0; font-size: 28px;">Limited Time Offers</h3>
            <ul style="list-style: none; padding: 0; margin: 20px 0;">
                <li style="margin: 10px 0; font-size: 16px;">✨ <strong>10% Off Return Bookings</strong> - Book your return transfer and save</li>
                <li style="margin: 10px 0; font-size: 16px;">✨ <strong>Free Cancellation</strong> - Cancel for free up to 24 hours before pickup</li>
                <li style="margin: 10px 0; font-size: 16px;">✨ <strong>Corporate Rates Available</strong> - Special pricing for business travel</li>
            </ul>
        </div>
    </div>';
}

/**
 * Get default content for Fleet Showcase section
 */
function airlinel_get_default_fleet_showcase() {
    ob_start();
    ?>
    <div class="fleet-showcase-default">
        <p>Explore our diverse fleet of modern, well-maintained vehicles:</p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
            <?php
            // Query fleet vehicles if they exist
            $fleet_args = array(
                'post_type' => 'fleet',
                'posts_per_page' => 6,
                'orderby' => 'menu_order',
                'order' => 'ASC'
            );
            $fleet_query = new WP_Query($fleet_args);

            if ($fleet_query->have_posts()) {
                while ($fleet_query->have_posts()) {
                    $fleet_query->the_post();
                    $multiplier = get_post_meta(get_the_ID(), '_fleet_multiplier', true) ?: '1.0';
                    $passengers = get_post_meta(get_the_ID(), '_fleet_passengers', true) ?: '4';
                    $luggage = get_post_meta(get_the_ID(), '_fleet_luggage', true) ?: '3';
                    ?>
                    <div style="padding: 20px; background: #f9f9f9; border-radius: 8px; border: 1px solid #ddd;">
                        <?php if (has_post_thumbnail()) : ?>
                            <div style="margin-bottom: 15px; border-radius: 4px; overflow: hidden; max-height: 200px;">
                                <?php the_post_thumbnail('medium', array('style' => 'width: 100%; height: auto; display: block;')); ?>
                            </div>
                        <?php endif; ?>
                        <h3 style="margin: 0 0 10px 0; font-size: 18px;"><?php the_title(); ?></h3>
                        <div style="font-size: 13px; color: #666;">
                            <p style="margin: 5px 0;">👥 <strong><?php echo esc_html($passengers); ?> passengers</strong></p>
                            <p style="margin: 5px 0;">🧳 <strong><?php echo esc_html($luggage); ?> luggage pieces</strong></p>
                        </div>
                    </div>
                    <?php
                }
                wp_reset_postdata();
            } else {
                // Default vehicles if none exist
                $default_vehicles = array(
                    array('name' => 'Executive Sedan', 'passengers' => '4', 'luggage' => '3'),
                    array('name' => 'Premium SUV', 'passengers' => '6', 'luggage' => '4'),
                    array('name' => 'Luxury Limousine', 'passengers' => '8', 'luggage' => '5'),
                    array('name' => 'Business Minibus', 'passengers' => '14', 'luggage' => '6'),
                );

                foreach ($default_vehicles as $vehicle) {
                    ?>
                    <div style="padding: 20px; background: #f9f9f9; border-radius: 8px; border: 1px solid #ddd;">
                        <h3 style="margin: 0 0 10px 0; font-size: 18px;"><?php echo esc_html($vehicle['name']); ?></h3>
                        <div style="font-size: 13px; color: #666;">
                            <p style="margin: 5px 0;">👥 <strong><?php echo esc_html($vehicle['passengers']); ?> passengers</strong></p>
                            <p style="margin: 5px 0;">🧳 <strong><?php echo esc_html($vehicle['luggage']); ?> luggage pieces</strong></p>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Get default content for Booking CTA section
 */
function airlinel_get_default_booking_cta() {
    return '<div class="booking-cta-default">
        <p>Ready to book your airport transfer? Our simple booking process takes less than 2 minutes.</p>
        <p style="margin-top: 15px; font-style: italic; color: #666;">Enter your pickup location, date, and time above to check availability and get an instant price quote.</p>
    </div>';
}

/**
 * Get default content for FAQ section
 */
function airlinel_get_default_faq_section() {
    return '<div class="faq-section-default">
        <div style="max-width: 800px; margin: 20px auto;">
            <div style="margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 20px;">
                <h4 style="margin: 0 0 10px 0; font-size: 16px;">How do I book an airport transfer?</h4>
                <p style="margin: 10px 0;">Simply enter your pickup location, date, and time in the booking form above. You\'ll receive an instant quote and can confirm your booking immediately.</p>
            </div>
            <div style="margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 20px;">
                <h4 style="margin: 0 0 10px 0; font-size: 16px;">Can I cancel my booking?</h4>
                <p style="margin: 10px 0;">Yes! You can cancel for free up to 24 hours before your scheduled pickup time. After that, cancellation fees may apply.</p>
            </div>
            <div style="margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 20px;">
                <h4 style="margin: 0 0 10px 0; font-size: 16px;">Do you offer meet and greet service?</h4>
                <p style="margin: 10px 0;">Yes! Our drivers meet you at arrivals with a name board. Your driver will contact you if your flight is delayed.</p>
            </div>
            <div style="margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 20px;">
                <h4 style="margin: 0 0 10px 0; font-size: 16px;">What payment methods do you accept?</h4>
                <p style="margin: 10px 0;">We accept all major credit cards, debit cards, and digital payment methods. You can pay at booking or settle with the driver.</p>
            </div>
            <div style="margin-bottom: 20px;">
                <h4 style="margin: 0 0 10px 0; font-size: 16px;">Is there 24/7 customer support?</h4>
                <p style="margin: 10px 0;">Absolutely! Our global support team is available 24/7 in multiple languages. Contact us via phone, email, or live chat.</p>
            </div>
        </div>
    </div>';
}
