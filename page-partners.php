<?php /* Template Name: Partners */ get_header(); ?>

<!-- Hero Section -->
<section class="relative pt-40 pb-20 bg-gradient-to-br from-[var(--dark-background-color)] to-black overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-[var(--primary-color)] to-transparent animate-gradient"></div>
    </div>
    
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <nav class="flex justify-center mb-6 text-sm font-medium text-gray-400 uppercase tracking-widest">
            <a href="<?php echo home_url(); ?>" class="hover:text-[var(--primary-color)] transition"><?php _e('Home', 'airlinel-theme'); ?></a>
            <span class="mx-3">/</span>
            <span class="text-white"><?php _e('Partners', 'airlinel-theme'); ?></span>
        </nav>
        <h1 class="font-[var(--font-family-heading)] text-4xl md:text-6xl font-bold text-white mb-4">
            <?php _e('Become Our Partner', 'airlinel-theme'); ?>
        </h1>
        <p class="text-xl text-gray-400 max-w-2xl mx-auto leading-relaxed">
            <?php _e('Join our network of agencies and unlock exclusive opportunities for your business', 'airlinel-theme'); ?>
        </p>
    </div>
</section>

<!-- Content Section -->
<section class="py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="prose prose-lg max-w-none 
                    prose-headings:font-[var(--font-family-heading)] 
                    prose-headings:text-[var(--dark-text-color)] 
                    prose-p:text-gray-600 prose-p:leading-relaxed
                    prose-a:text-[var(--primary-color)] prose-a:no-underline hover:prose-a:underline
                    prose-img:rounded-3xl prose-img:shadow-lg">
            
            <?php the_content(); ?>

        </div>
    </div>
</section>

<!-- Registration Form Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white border border-gray-200 rounded-3xl shadow-xl p-8 md:p-12">
            <h2 class="text-3xl font-bold font-[var(--font-family-heading)] text-gray-900 mb-2 text-center"><?php _e('Apply for Partnership', 'airlinel-theme'); ?></h2>
            <p class="text-center text-gray-600 mb-8"><?php _e("Fill out the form below and we'll review your application", 'airlinel-theme'); ?></p>
            
            <form id="agency-registration-form" class="space-y-6">
                <div id="agency-response-message" class="hidden mt-4 p-4 rounded-xl text-center"></div>
                
                <input type="hidden" name="action" value="register_agency">
                <?php wp_nonce_field('register_agency_nonce', 'agency_nonce'); ?>
                
                <!-- Name -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2"><?php _e('Full Name *', 'airlinel-theme'); ?></label>
                    <input type="text" name="full_name" required class="w-full px-5 py-4 border border-gray-300 rounded-xl focus:outline-none focus:border-[var(--primary-color)] transition-colors">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2"><?php _e('Email Address *', 'airlinel-theme'); ?></label>
                    <input type="email" name="email" required class="w-full px-5 py-4 border border-gray-300 rounded-xl focus:outline-none focus:border-[var(--primary-color)] transition-colors">
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2"><?php _e('Phone Number *', 'airlinel-theme'); ?></label>
                    <input type="tel" name="phone" required class="w-full px-5 py-4 border border-gray-300 rounded-xl focus:outline-none focus:border-[var(--primary-color)] transition-colors">
                </div>

                <!-- Job Position -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2"><?php _e('Current Job / Position *', 'airlinel-theme'); ?></label>
                    <select name="position" required class="w-full px-5 py-4 border border-gray-300 rounded-xl focus:outline-none focus:border-[var(--primary-color)] transition-colors">
                        <option value=""><?php _e('Select your position...', 'airlinel-theme'); ?></option>
                        <option value="receptionist"><?php _e('Receptionist', 'airlinel-theme'); ?></option>
                        <option value="hotel_owner"><?php _e('Hotel Owner / Manager', 'airlinel-theme'); ?></option>
                        <option value="travel_agency"><?php _e('Travel Agency', 'airlinel-theme'); ?></option>
                        <option value="ota"><?php _e('Online Travel Agency (OTA)', 'airlinel-theme'); ?></option>
                        <option value="dorm"><?php _e('Student Accommodation Facility', 'airlinel-theme'); ?></option>
                        <option value="overseas_edu_agency"><?php _e('Overseas Education Agency', 'airlinel-theme'); ?></option>
                        <option value="consulate"><?php _e('Consulate / Embassy', 'airlinel-theme'); ?></option>
                        <option value="other"><?php _e('Other', 'airlinel-theme'); ?></option>
                    </select>
                </div>

                <!-- Company Name (Optional) -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2"><?php _e('Company / Organization Name', 'airlinel-theme'); ?></label>
                    <input type="text" name="company_name" class="w-full px-5 py-4 border border-gray-300 rounded-xl focus:outline-none focus:border-[var(--primary-color)] transition-colors">
                </div>

                <!-- Terms & Conditions -->
                <div class="flex items-start gap-3">
                    <input type="checkbox" id="agree_terms" name="agree_terms" required class="mt-1">
                    <label for="agree_terms" class="text-sm text-gray-700">
                        <?php _e('I agree to the Terms & Conditions and understand that I will receive an agency code after verification.', 'airlinel-theme'); ?>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-[var(--primary-color)] text-white font-bold py-4 px-6 rounded-full hover:bg-[var(--primary-button-hover-bg-color)] transition-all transform hover:-translate-y-1 shadow-lg">
                    <?php _e('Apply for Agency Partnership', 'airlinel-theme'); ?>
                    <i class="fa-solid fa-arrow-right ml-2"></i>
                </button>
            </form>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-white border-t border-gray-100">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold font-[var(--font-family-heading)] mb-4"><?php _e('Questions About Partnership?', 'airlinel-theme'); ?></h2>
        <p class="text-gray-600 mb-8 max-w-2xl mx-auto"><?php _e("Get in touch with our partnership team and we'll help you get started", 'airlinel-theme'); ?></p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="<?php echo home_url('/book-your-ride'); ?>" class="px-8 py-4 bg-[var(--primary-color)] text-white rounded-full font-bold hover:bg-[var(--primary-button-hover-bg-color)] transition shadow-lg">
                <?php _e('Book a Ride', 'airlinel-theme'); ?>
            </a>
            <a href="<?php echo home_url('/contact'); ?>" class="px-8 py-4 bg-white border-2 border-gray-200 text-[var(--dark-text-color)] rounded-full font-bold hover:border-[var(--primary-color)] transition">
                <?php _e('Contact Support', 'airlinel-theme'); ?>
            </a>
        </div>
    </div>
</section>

<script>
jQuery(document).ready(function($) {
    $('#agency-registration-form').on('submit', function(e) {
        e.preventDefault();
        
        const $btn = $('button[type="submit"]');
        const $message = $('#agency-response-message');
        const $form = $(this);
        
        // Show loading state
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Submitting...');
        $message.hide();
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                $btn.prop('disabled', false).html('Apply for Agency Partnership<i class="fa-solid fa-arrow-right ml-2"></i>');
                
                if (response.success) {
                    // Success
                    $message.removeClass('hidden bg-red-50 text-red-700').addClass('bg-green-50 text-green-700 border border-green-200');
                    $message.html('<strong>✓ Success!</strong> ' + response.data.message);
                    $form[0].reset();
                    // Scroll to message
                    $('html, body').animate({scrollTop: $message.offset().top - 100}, 500);
                } else {
                    // Error
                    $message.removeClass('hidden bg-green-50 text-green-700').addClass('bg-red-50 text-red-700 border border-red-200');
                    $message.html('<strong>✗ Error!</strong> ' + response.data.message);
                    $('html, body').animate({scrollTop: $message.offset().top - 100}, 500);
                }
            },
            error: function(xhr, status, error) {
                $btn.prop('disabled', false).html('Apply for Agency Partnership<i class="fa-solid fa-arrow-right ml-2"></i>');
                console.log('AJAX Error:', error, xhr.responseText);
                $message.removeClass('hidden bg-green-50 text-green-700').addClass('bg-red-50 text-red-700 border border-red-200');
                $message.html('<strong>✗ Error!</strong> Something went wrong. Please try again.');
                $('html, body').animate({scrollTop: $message.offset().top - 100}, 500);
            }
        });
    });
});
</script>

<?php get_footer(); ?>
