<?php
/**
 * Airlinel Page Manager
 * Manages editable page content and regional site-specific information
 */

class Airlinel_Page_Manager {

    private static $is_regional = null;
    private static $regional_prefix = '';

    /**
     * Initialize regional context
     */
    private static function init_regional_context() {
        if (self::$is_regional === null) {
            self::$is_regional = defined('AIRLINEL_IS_REGIONAL_SITE') && AIRLINEL_IS_REGIONAL_SITE;
            self::$regional_prefix = self::$is_regional ? 'regional_' : '';
        }
    }

    /**
     * Get regional contact information
     *
     * @return array Contact info with phone, email, address
     */
    public static function get_contact_info() {
        self::init_regional_context();

        return array(
            'phone' => get_option(self::$regional_prefix . 'airlinel_contact_phone', get_option('airlinel_contact_phone', self::get_defaults('phone'))),
            'email' => get_option(self::$regional_prefix . 'airlinel_contact_email', get_option('airlinel_contact_email', self::get_defaults('email'))),
            'address' => get_option(self::$regional_prefix . 'airlinel_contact_address', get_option('airlinel_contact_address', self::get_defaults('address'))),
        );
    }

    /**
     * Get business hours
     *
     * @return array Business hours with days and times
     */
    public static function get_business_hours() {
        self::init_regional_context();

        // Check regional site first, then main site, then defaults
        $hours = get_option(self::$regional_prefix . 'airlinel_business_hours', array());

        if (empty($hours)) {
            $hours = get_option('airlinel_business_hours', array());
        }

        if (empty($hours)) {
            // Return defaults
            return self::get_default_business_hours();
        }

        return $hours;
    }

    /**
     * Get office address
     *
     * @return string Office address
     */
    public static function get_office_address() {
        $contact_info = self::get_contact_info();
        return $contact_info['address'];
    }

    /**
     * Get office phone
     *
     * @return string Phone number
     */
    public static function get_office_phone() {
        $contact_info = self::get_contact_info();
        return $contact_info['phone'];
    }

    /**
     * Get office email
     *
     * @return string Email address
     */
    public static function get_office_email() {
        $contact_info = self::get_contact_info();
        return $contact_info['email'];
    }

    /**
     * Get SEO meta data for a page
     *
     * @param int $post_id Post ID
     * @return array SEO meta data
     */
    public static function get_seo_meta($post_id = null) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }

        return array(
            'seo_title' => get_post_meta($post_id, '_airlinel_seo_title', true),
            'seo_description' => get_post_meta($post_id, '_airlinel_seo_description', true),
            'focus_keyword' => get_post_meta($post_id, '_airlinel_focus_keyword', true),
            'og_image' => get_post_meta($post_id, '_airlinel_og_image', true),
            'canonical_url' => get_post_meta($post_id, '_airlinel_canonical_url', true),
        );
    }

    /**
     * Get company description/about text
     *
     * @return string Company description
     */
    public static function get_company_description() {
        self::init_regional_context();

        $description = get_option(self::$regional_prefix . 'airlinel_company_description', '');

        if (!$description) {
            $description = get_option('airlinel_company_description', '');
        }

        if (!$description) {
            $description = self::get_defaults('company_description');
        }

        return wp_kses_post($description);
    }

    /**
     * Get company mission statement
     *
     * @return string Mission statement
     */
    public static function get_company_mission() {
        self::init_regional_context();

        $mission = get_option(self::$regional_prefix . 'airlinel_company_mission', '');

        if (!$mission) {
            $mission = get_option('airlinel_company_mission', '');
        }

        if (!$mission) {
            $mission = self::get_defaults('mission');
        }

        return wp_kses_post($mission);
    }

    /**
     * Get company history/background
     *
     * @return string Company history
     */
    public static function get_company_history() {
        self::init_regional_context();

        $history = get_option(self::$regional_prefix . 'airlinel_company_history', '');

        if (!$history) {
            $history = get_option('airlinel_company_history', '');
        }

        if (!$history) {
            $history = self::get_defaults('history');
        }

        return wp_kses_post($history);
    }

    /**
     * Get trust indicators (years in business, customers served, etc.)
     *
     * @return array Trust indicators
     */
    public static function get_trust_indicators() {
        self::init_regional_context();

        // Get regional override or main site value or default
        $years = intval(get_option(self::$regional_prefix . 'airlinel_years_in_business', get_option('airlinel_years_in_business', 15)));
        $customers = intval(get_option(self::$regional_prefix . 'airlinel_customers_served', get_option('airlinel_customers_served', 50000)));
        $vehicles = intval(get_option(self::$regional_prefix . 'airlinel_fleet_size', get_option('airlinel_fleet_size', 150)));
        $daily_rides = intval(get_option(self::$regional_prefix . 'airlinel_daily_rides', get_option('airlinel_daily_rides', 500)));

        return array(
            'years_in_business' => $years,
            'customers_served' => number_format($customers),
            'fleet_size' => $vehicles,
            'daily_rides' => number_format($daily_rides),
        );
    }

    /**
     * Get default values for content and contact info
     *
     * @param string $key Key for specific default
     * @return string|array Default value
     */
    private static function get_defaults($key = null) {
        $defaults = array(
            'phone' => '+44 (0)20 XXXX XXXX',
            'email' => 'contact@airlinel.com',
            'address' => 'London, United Kingdom',
            'company_description' => 'Airlinel offers premium airport transfer and chauffeur services across the UK. With over 15 years of experience, we provide reliable, professional transportation for business travelers and leisure passengers.',
            'mission' => 'To deliver exceptional airport transfer services with punctuality, professionalism, and personalized care.',
            'history' => 'Founded in 2009, Airlinel has grown to become a trusted name in UK airport transfers, serving thousands of satisfied customers with our commitment to excellence and reliability.',
        );

        return $key ? ($defaults[$key] ?? '') : $defaults;
    }

    /**
     * Get default business hours
     *
     * @return array Default business hours
     */
    private static function get_default_business_hours() {
        return array(
            'monday' => array('open' => '06:00', 'close' => '23:00'),
            'tuesday' => array('open' => '06:00', 'close' => '23:00'),
            'wednesday' => array('open' => '06:00', 'close' => '23:00'),
            'thursday' => array('open' => '06:00', 'close' => '23:00'),
            'friday' => array('open' => '06:00', 'close' => '23:00'),
            'saturday' => array('open' => '06:00', 'close' => '23:00'),
            'sunday' => array('open' => '06:00', 'close' => '23:00'),
        );
    }

    /**
     * Format business hours for display
     *
     * @return string Formatted business hours
     */
    public static function get_formatted_business_hours() {
        $hours = self::get_business_hours();
        $formatted = '';

        foreach ($hours as $day => $times) {
            $day_name = ucfirst($day);
            $open = $times['open'] ?? '06:00';
            $close = $times['close'] ?? '23:00';
            $formatted .= "$day_name: $open - $close\n";
        }

        return trim($formatted);
    }

    /**
     * Check if business is open now
     *
     * @return bool True if business is currently open
     */
    public static function is_open_now() {
        $hours = self::get_business_hours();
        $today = strtolower(date('l'));

        if (!isset($hours[$today])) {
            return false;
        }

        $current_time = date('H:i');
        $open = $hours[$today]['open'] ?? '06:00';
        $close = $hours[$today]['close'] ?? '23:00';

        return ($current_time >= $open && $current_time <= $close);
    }
}
?>
