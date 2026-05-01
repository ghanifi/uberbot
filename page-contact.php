<?php /* Template Name: Contact Us */
get_header();
require_once get_template_directory() . '/includes/class-page-manager.php';
$page_mgr = new Airlinel_Page_Manager();
$contact_info = $page_mgr->get_contact_info();
$hours = $page_mgr->get_business_hours();
$is_open = $page_mgr->is_open_now();
?>

<!-- Hero Section -->
<section class="pt-32 pb-20 sm:pt-40 sm:pb-32 bg-gradient-to-br from-[var(--dark-background-color)] to-black text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="font-[var(--font-family-heading)] text-5xl sm:text-6xl lg:text-7xl font-bold mb-6">
            <?php _e('Get in Touch', 'airlinel-theme'); ?>
        </h1>
        <p class="text-lg sm:text-xl text-gray-300 max-w-2xl mx-auto">
            <?php _e("Have questions? We're here to help. Contact us today for more information about our services.", 'airlinel-theme'); ?>
        </p>
    </div>
</section>

<!-- Contact Information & Form Section -->
<section class="py-20 sm:py-32 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <!-- Contact Information -->
            <div class="lg:col-span-1">
                <h2 class="font-[var(--font-family-heading)] text-3xl font-bold text-[var(--dark-text-color)] mb-8">
                    <?php _e('Contact Information', 'airlinel-theme'); ?>
                </h2>

                <!-- Phone -->
                <div class="mb-8">
                    <h3 class="font-bold text-lg text-[var(--dark-text-color)] mb-3">
                        <i class="fa-solid fa-phone text-[var(--primary-color)] mr-3"></i>
                        <?php _e('Phone', 'airlinel-theme'); ?>
                    </h3>
                    <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $contact_info['phone'])); ?>" class="text-gray-600 hover:text-[var(--primary-color)] transition-colors">
                        <?php echo esc_html($contact_info['phone']); ?>
                    </a>
                    <p class="text-sm text-gray-500 mt-2"><?php echo $is_open ? __('Available 24/7', 'airlinel-theme') : __('Available during business hours', 'airlinel-theme'); ?></p>
                </div>

                <!-- Email -->
                <div class="mb-8">
                    <h3 class="font-bold text-lg text-[var(--dark-text-color)] mb-3">
                        <i class="fa-solid fa-envelope text-[var(--primary-color)] mr-3"></i>
                        <?php _e('Email', 'airlinel-theme'); ?>
                    </h3>
                    <a href="mailto:<?php echo esc_attr($contact_info['email']); ?>" class="text-gray-600 hover:text-[var(--primary-color)] transition-colors">
                        <?php echo esc_html($contact_info['email']); ?>
                    </a>
                    <p class="text-sm text-gray-500 mt-2"><?php _e("We'll respond within 2 hours", 'airlinel-theme'); ?></p>
                </div>

                <!-- Address -->
                <div class="mb-8">
                    <h3 class="font-bold text-lg text-[var(--dark-text-color)] mb-3">
                        <i class="fa-solid fa-map-pin text-[var(--primary-color)] mr-3"></i>
                        <?php _e('Address', 'airlinel-theme'); ?>
                    </h3>
                    <p class="text-gray-600">
                        <?php echo esc_html($contact_info['address']); ?>
                    </p>
                </div>

                <!-- Business Hours -->
                <div class="bg-gray-50 p-6 rounded-2xl">
                    <h3 class="font-bold text-lg text-[var(--dark-text-color)] mb-4">
                        <i class="fa-solid fa-clock text-[var(--primary-color)] mr-3"></i>
                        <?php _e('Business Hours', 'airlinel-theme'); ?>
                    </h3>
                    <div class="space-y-2 text-sm text-gray-600">
                        <?php
                        $days_order = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
                        foreach ($days_order as $day) {
                            $times = $hours[$day] ?? array('open' => 'Closed', 'close' => '');
                            $open = $times['open'] ?? 'Closed';
                            $close = $times['close'] ?? '';
                            $day_name = ucfirst($day);
                            echo '<div class="flex justify-between">';
                            echo '<span>' . esc_html($day_name) . ':</span>';
                            if ($close) {
                                echo '<span class="font-semibold">' . esc_html($open) . ' - ' . esc_html($close) . '</span>';
                            } else {
                                echo '<span class="font-semibold">' . esc_html($open) . '</span>';
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-sm">
                            <span class="inline-block w-2 h-2 rounded-full <?php echo $is_open ? 'bg-green-500' : 'bg-red-500'; ?> mr-2"></span>
                            <?php echo $is_open ? __('We are currently open', 'airlinel-theme') : __('We are currently closed', 'airlinel-theme'); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="lg:col-span-2">
                <div class="bg-gray-50 p-8 rounded-3xl">
                    <h2 class="font-[var(--font-family-heading)] text-3xl font-bold text-[var(--dark-text-color)] mb-8">
                        <?php _e('Send us a Message', 'airlinel-theme'); ?>
                    </h2>

                    <form id="airlinel-contact-form" method="post" class="space-y-6">
                        <?php wp_nonce_field('airlinel_contact_form_nonce', 'airlinel_contact_nonce'); ?>

                        <!-- Name Field -->
                        <div>
                            <label for="contact_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php _e('Full Name *', 'airlinel-theme'); ?>
                            </label>
                            <input
                                type="text"
                                id="contact_name"
                                name="contact_name"
                                required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] focus:border-transparent transition-all"
                                placeholder="John Doe"
                            />
                        </div>

                        <!-- Email Field -->
                        <div>
                            <label for="contact_email" class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php _e('Email Address *', 'airlinel-theme'); ?>
                            </label>
                            <input
                                type="email"
                                id="contact_email"
                                name="contact_email"
                                required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] focus:border-transparent transition-all"
                                placeholder="john@example.com"
                            />
                        </div>

                        <!-- Phone Field -->
                        <div>
                            <label for="contact_phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php _e('Phone Number', 'airlinel-theme'); ?>
                            </label>
                            <input
                                type="tel"
                                id="contact_phone"
                                name="contact_phone"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] focus:border-transparent transition-all"
                                placeholder="+44 (0)20 XXXX XXXX"
                            />
                        </div>

                        <!-- Subject Field -->
                        <div>
                            <label for="contact_subject" class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php _e('Subject *', 'airlinel-theme'); ?>
                            </label>
                            <select
                                id="contact_subject"
                                name="contact_subject"
                                required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] focus:border-transparent transition-all"
                            >
                                <option value=""><?php _e('-- Select a subject --', 'airlinel-theme'); ?></option>
                                <option value="booking_inquiry"><?php _e('Booking Inquiry', 'airlinel-theme'); ?></option>
                                <option value="general_question"><?php _e('General Question', 'airlinel-theme'); ?></option>
                                <option value="complaint"><?php _e('Complaint or Feedback', 'airlinel-theme'); ?></option>
                                <option value="partnership"><?php _e('Partnership Opportunity', 'airlinel-theme'); ?></option>
                                <option value="other"><?php _e('Other', 'airlinel-theme'); ?></option>
                            </select>
                        </div>

                        <!-- Message Field -->
                        <div>
                            <label for="contact_message" class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php _e('Message *', 'airlinel-theme'); ?>
                            </label>
                            <textarea
                                id="contact_message"
                                name="contact_message"
                                required
                                rows="6"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] focus:border-transparent transition-all resize-none"
                                placeholder="<?php esc_attr_e('Please tell us how we can help...', 'airlinel-theme'); ?>"
                            ></textarea>
                        </div>

                        <!-- Privacy Notice -->
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <p class="text-sm text-gray-600">
                                <i class="fa-solid fa-shield text-[var(--primary-color)] mr-2"></i>
                                <?php _e('Your information is secure and will only be used to respond to your inquiry.', 'airlinel-theme'); ?>
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            class="w-full px-8 py-4 text-lg font-bold text-white bg-[var(--primary-color)] rounded-lg hover:bg-[var(--primary-button-hover-bg-color)] transition-all duration-300 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <i class="fa-solid fa-paper-plane mr-2"></i>
                            <?php _e('Send Message', 'airlinel-theme'); ?>
                        </button>

                        <!-- Form Status Messages -->
                        <div id="form-status-message" class="hidden p-4 rounded-lg text-sm"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section (Optional) -->
<?php if (!empty($contact_info['address'])) : ?>
<section class="py-20 sm:py-32 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="font-[var(--font-family-heading)] text-4xl sm:text-5xl font-bold text-[var(--dark-text-color)] mb-12 text-center">
            <?php _e('Find Us', 'airlinel-theme'); ?>
        </h2>
        <div class="w-full rounded-3xl overflow-hidden shadow-2xl">
            <iframe
                width="100%"
                height="500"
                style="border:0;"
                loading="lazy"
                allowfullscreen=""
                referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps/embed/v1/place?key=AIzaSyBUFYkE9wVqz4Z_h2JV4_u6d92-Q_GVYAM&q=<?php echo urlencode($contact_info['address']); ?>"
            ></iframe>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- FAQ Section -->
<section class="py-20 sm:py-32 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="font-[var(--font-family-heading)] text-4xl sm:text-5xl font-bold text-[var(--dark-text-color)] mb-12 text-center">
            <?php _e('Frequently Asked Questions', 'airlinel-theme'); ?>
        </h2>

        <div class="space-y-4">
            <!-- FAQ Item 1 -->
            <details class="group border border-gray-200 rounded-lg p-6 hover:border-[var(--primary-color)] transition-colors">
                <summary class="flex cursor-pointer items-center justify-between font-semibold text-[var(--dark-text-color)]">
                    <span><?php _e('How far in advance should I book?', 'airlinel-theme'); ?></span>
                    <span class="transition group-open:rotate-180">
                        <i class="fa-solid fa-chevron-down"></i>
                    </span>
                </summary>
                <p class="text-gray-600 mt-4"><?php _e('We recommend booking at least 24 hours in advance for best availability. However, we do accept same-day bookings depending on our current schedule. Contact us directly for urgent bookings.', 'airlinel-theme'); ?></p>
            </details>

            <!-- FAQ Item 2 -->
            <details class="group border border-gray-200 rounded-lg p-6 hover:border-[var(--primary-color)] transition-colors">
                <summary class="flex cursor-pointer items-center justify-between font-semibold text-[var(--dark-text-color)]">
                    <span><?php _e('What payment methods do you accept?', 'airlinel-theme'); ?></span>
                    <span class="transition group-open:rotate-180">
                        <i class="fa-solid fa-chevron-down"></i>
                    </span>
                </summary>
                <p class="text-gray-600 mt-4"><?php _e('We accept all major credit cards, debit cards, and bank transfers. Online payment is processed securely through our booking system. You can also pay directly to the driver if pre-arranged.', 'airlinel-theme'); ?></p>
            </details>

            <!-- FAQ Item 3 -->
            <details class="group border border-gray-200 rounded-lg p-6 hover:border-[var(--primary-color)] transition-colors">
                <summary class="flex cursor-pointer items-center justify-between font-semibold text-[var(--dark-text-color)]">
                    <span><?php _e('What if I need to cancel my booking?', 'airlinel-theme'); ?></span>
                    <span class="transition group-open:rotate-180">
                        <i class="fa-solid fa-chevron-down"></i>
                    </span>
                </summary>
                <p class="text-gray-600 mt-4"><?php _e('Cancellations made 24 hours or more in advance receive a full refund. Cancellations within 24 hours may incur a cancellation fee. Please contact us immediately if you need to modify your booking.', 'airlinel-theme'); ?></p>
            </details>

            <!-- FAQ Item 4 -->
            <details class="group border border-gray-200 rounded-lg p-6 hover:border-[var(--primary-color)] transition-colors">
                <summary class="flex cursor-pointer items-center justify-between font-semibold text-[var(--dark-text-color)]">
                    <span><?php _e('Are your vehicles wheelchair accessible?', 'airlinel-theme'); ?></span>
                    <span class="transition group-open:rotate-180">
                        <i class="fa-solid fa-chevron-down"></i>
                    </span>
                </summary>
                <p class="text-gray-600 mt-4"><?php _e('Yes, we have wheelchair accessible vehicles available. Please mention your accessibility requirements when booking so we can arrange the appropriate vehicle for your needs.', 'airlinel-theme'); ?></p>
            </details>

            <!-- FAQ Item 5 -->
            <details class="group border border-gray-200 rounded-lg p-6 hover:border-[var(--primary-color)] transition-colors">
                <summary class="flex cursor-pointer items-center justify-between font-semibold text-[var(--dark-text-color)]">
                    <span><?php _e('Can I bring luggage and how much is included?', 'airlinel-theme'); ?></span>
                    <span class="transition group-open:rotate-180">
                        <i class="fa-solid fa-chevron-down"></i>
                    </span>
                </summary>
                <p class="text-gray-600 mt-4"><?php _e('Absolutely! All our vehicles have spacious boot space to accommodate standard luggage. Oversized baggage may require a larger vehicle. Our team will assist with luggage handling as part of our service.', 'airlinel-theme'); ?></p>
            </details>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 sm:py-24 bg-gradient-to-r from-[var(--primary-color)] to-[var(--accent2-color)]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="font-[var(--font-family-heading)] text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-6">
            <?php _e('Ready to Book Your Transfer?', 'airlinel-theme'); ?>
        </h2>
        <p class="text-lg sm:text-xl text-white/90 max-w-2xl mx-auto mb-8">
            <?php _e('Experience premium airport transfer service today.', 'airlinel-theme'); ?>
        </p>
        <a href="<?php echo home_url('/book-your-ride'); ?>" class="inline-flex items-center justify-center px-10 py-5 text-lg font-bold text-[var(--primary-color)] bg-white rounded-full hover:bg-gray-100 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
            <?php _e('Book Now', 'airlinel-theme'); ?>
            <i class="fa-solid fa-arrow-right ml-3"></i>
        </a>
    </div>
</section>

<!-- Contact Form Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('airlinel-contact-form');
    const statusMessage = document.getElementById('form-status-message');

    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;

        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Sending...';

        // Send form via AJAX
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: new URLSearchParams({
                action: 'airlinel_submit_contact_form',
                security: '<?php echo wp_create_nonce('airlinel_contact_form_nonce'); ?>',
                contact_name: formData.get('contact_name'),
                contact_email: formData.get('contact_email'),
                contact_phone: formData.get('contact_phone'),
                contact_subject: formData.get('contact_subject'),
                contact_message: formData.get('contact_message'),
            })
        })
        .then(response => response.json())
        .then(data => {
            // Reset button
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;

            // Show message
            statusMessage.classList.remove('hidden');
            if (data.success) {
                statusMessage.className = 'p-4 rounded-lg text-sm bg-green-50 border border-green-200 text-green-800';
                statusMessage.innerHTML = '<i class="fa-solid fa-check mr-2"></i>' + (data.message || 'Thank you! Your message has been sent successfully.');
                form.reset();
            } else {
                statusMessage.className = 'p-4 rounded-lg text-sm bg-red-50 border border-red-200 text-red-800';
                statusMessage.innerHTML = '<i class="fa-solid fa-exclamation-circle mr-2"></i>' + (data.message || 'An error occurred. Please try again.');
            }

            // Auto-hide success message after 5 seconds
            if (data.success) {
                setTimeout(() => {
                    statusMessage.classList.add('hidden');
                }, 5000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            statusMessage.classList.remove('hidden');
            statusMessage.className = 'p-4 rounded-lg text-sm bg-red-50 border border-red-200 text-red-800';
            statusMessage.innerHTML = '<i class="fa-solid fa-exclamation-circle mr-2"></i>An error occurred. Please try again later.';
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        });
    });
});
</script>

<?php get_footer(); ?>
