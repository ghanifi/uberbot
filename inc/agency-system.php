<?php
// ========== AGENCY MANAGEMENT SYSTEM ==========
// NOTE: Admin page management moved to CPT (Custom Post Type) in functions.php
// This file now contains AJAX handlers for agency registration and validation
// that are used in the public-facing booking and partners pages

/**
 * AJAX: Register new agency from partners form
 */
function airlinel_register_agency() {
    // Verify nonce
    $nonce = isset($_POST['agency_nonce']) ? $_POST['agency_nonce'] : '';
    if (!wp_verify_nonce($nonce, 'register_agency_nonce')) {
        wp_send_json_error(['message' => 'Security check failed. Please refresh and try again.']);
    }
    
    // Get form data
    $full_name = sanitize_text_field($_POST['full_name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $position = sanitize_text_field($_POST['position'] ?? '');
    $company_name = sanitize_text_field($_POST['company_name'] ?? '');
    
    // Validate required fields
    if (empty($full_name) || empty($email) || empty($phone) || empty($position)) {
        wp_send_json_error(['message' => 'Please fill in all required fields']);
    }
    
    // Validate email format
    if (!is_email($email)) {
        wp_send_json_error(['message' => 'Please enter a valid email address']);
    }
    
    // Get existing agencies
    $agencies = get_option('airlinel_agencies', []);
    
    // Check if email already registered
    foreach ($agencies as $agency) {
        if ($agency['email'] === $email) {
            wp_send_json_error(['message' => 'This email is already registered']);
        }
    }
    
    // Create temporary agency registration (pending verification)
    $pending_registrations = get_option('airlinel_pending_agencies', []);
    $pending_registrations[] = [
        'full_name' => $full_name,
        'email' => $email,
        'phone' => $phone,
        'position' => $position,
        'company_name' => $company_name,
        'date_submitted' => current_time('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'],
    ];
    update_option('airlinel_pending_agencies', $pending_registrations);
    
    // Send email to admin
    $admin_email = get_option('admin_email');
    $subject = 'New Agency Partnership Application: ' . $full_name;
    $message = "New agency partnership application received:\n\n";
    $message .= "Name: " . $full_name . "\n";
    $message .= "Email: " . $email . "\n";
    $message .= "Phone: " . $phone . "\n";
    $message .= "Position: " . $position . "\n";
    $message .= "Company: " . $company_name . "\n";
    $message .= "Date: " . current_time('Y-m-d H:i:s') . "\n\n";
    $message .= "Please review at: " . admin_url('admin.php?page=airlinel_agencies');
    
    wp_mail($admin_email, $subject, $message);
    
    wp_send_json_success(['message' => 'Thank you for applying! Your application has been submitted. We will review it and contact you shortly with your agency code.']);
}
add_action('wp_ajax_register_agency', 'airlinel_register_agency');
add_action('wp_ajax_nopriv_register_agency', 'airlinel_register_agency');

/**
 * Validate Agency Code and Get Discount
 */
function airlinel_get_agency_discount($agency_code) {
    if (empty($agency_code)) {
        return 0;
    }
    
    $agencies = get_option('airlinel_agencies', []);
    $code = strtoupper($agency_code);
    
    if (isset($agencies[$code])) {
        return floatval($agencies[$code]['discount']);
    }
    
    return 0;
}

/**
 * AJAX: Validate Agency Code
 */
function airlinel_validate_agency_code() {
    $code = sanitize_text_field($_POST['code']);
    
    $agencies = get_option('airlinel_agencies', []);
    $code_upper = strtoupper($code);
    
    if (isset($agencies[$code_upper])) {
        wp_send_json_success([
            'discount' => $agencies[$code_upper]['discount'],
            'name' => $agencies[$code_upper]['name']
        ]);
    } else {
        wp_send_json_error(['message' => 'Invalid agency code']);
    }
}
add_action('wp_ajax_validate_agency_code', 'airlinel_validate_agency_code');
add_action('wp_ajax_nopriv_validate_agency_code', 'airlinel_validate_agency_code');

