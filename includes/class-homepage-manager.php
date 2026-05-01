<?php
/**
 * Airlinel Homepage Manager
 * Manages homepage content sections with visibility toggles and custom content
 */

class Airlinel_Homepage_Manager {

    /**
     * Available sections in the homepage
     *
     * @var array
     */
    private $sections = array(
        'featured_routes' => array(
            'label' => 'Featured Routes',
            'description' => 'Highlight popular travel routes with descriptions'
        ),
        'customer_testimonials' => array(
            'label' => 'Customer Testimonials',
            'description' => 'Display customer reviews and testimonials with ratings'
        ),
        'service_highlights' => array(
            'label' => 'Service Highlights',
            'description' => 'Showcase key service features and benefits'
        ),
        'trust_signals' => array(
            'label' => 'Trust Signals',
            'description' => 'Display trust badges, certifications, and credentials'
        ),
        'special_offers' => array(
            'label' => 'Special Offers',
            'description' => 'Promote current discounts and special offers'
        ),
        'fleet_showcase' => array(
            'label' => 'Fleet Showcase',
            'description' => 'Display available vehicle types and fleet options'
        ),
        'booking_cta' => array(
            'label' => 'Booking Call-to-Action',
            'description' => 'Primary booking call-to-action button and messaging'
        ),
        'faq_section' => array(
            'label' => 'FAQ Section',
            'description' => 'Frequently asked questions about transfers'
        ),
    );

    /**
     * Initialize the manager
     */
    public function __construct() {
        // Initialize on construction if needed
    }

    /**
     * Get visibility status of a section
     *
     * @param string $section_id Section identifier
     * @return bool True if section is visible, false otherwise
     */
    public function get_section_visibility($section_id) {
        $section_id = sanitize_text_field($section_id);

        if (!$this->is_valid_section($section_id)) {
            return false;
        }

        $option_key = 'airlinel_homepage_section_' . $section_id . '_visible';
        // Default to true if option doesn't exist (sections visible by default)
        return get_option($option_key, true);
    }

    /**
     * Set visibility status of a section
     *
     * @param string $section_id Section identifier
     * @param bool $visible True to show, false to hide
     * @return bool Success status
     */
    public function set_section_visibility($section_id, $visible) {
        $section_id = sanitize_text_field($section_id);

        if (!$this->is_valid_section($section_id)) {
            return false;
        }

        $option_key = 'airlinel_homepage_section_' . $section_id . '_visible';
        $visible = (bool) $visible;

        return update_option($option_key, $visible);
    }

    /**
     * Get custom content for a section
     *
     * @param string $section_id Section identifier
     * @return string Custom content or empty string if not set
     */
    public function get_section_content($section_id) {
        $section_id = sanitize_text_field($section_id);

        if (!$this->is_valid_section($section_id)) {
            return '';
        }

        $option_key = 'airlinel_homepage_section_' . $section_id . '_content';
        $content = get_option($option_key, '');

        return $content ? wp_kses_post($content) : '';
    }

    /**
     * Set custom content for a section
     *
     * @param string $section_id Section identifier
     * @param string $content Custom content HTML
     * @return bool Success status
     */
    public function set_section_content($section_id, $content) {
        $section_id = sanitize_text_field($section_id);

        if (!$this->is_valid_section($section_id)) {
            return false;
        }

        $option_key = 'airlinel_homepage_section_' . $section_id . '_content';
        // Sanitize content but allow HTML tags
        $sanitized_content = wp_kses_post($content);

        return update_option($option_key, $sanitized_content);
    }

    /**
     * Get all available sections
     *
     * @return array Array of section data with IDs, labels, and descriptions
     */
    public function get_all_sections() {
        $sections_data = array();

        foreach ($this->sections as $id => $section_info) {
            $sections_data[] = array(
                'id' => $id,
                'label' => $section_info['label'],
                'description' => $section_info['description'],
                'visible' => $this->get_section_visibility($id),
                'content' => $this->get_section_content($id),
            );
        }

        return $sections_data;
    }

    /**
     * Reset all sections to default visibility (all visible)
     *
     * @return bool Success status
     */
    public function reset_to_defaults() {
        $success = true;

        foreach (array_keys($this->sections) as $section_id) {
            // Set all sections to visible (true)
            if (!$this->set_section_visibility($section_id, true)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Check if a section ID is valid
     *
     * @param string $section_id Section identifier to validate
     * @return bool True if valid, false otherwise
     */
    private function is_valid_section($section_id) {
        return isset($this->sections[$section_id]);
    }

    /**
     * Get section label
     *
     * @param string $section_id Section identifier
     * @return string Section label or empty string
     */
    public function get_section_label($section_id) {
        $section_id = sanitize_text_field($section_id);

        if (!$this->is_valid_section($section_id)) {
            return '';
        }

        return $this->sections[$section_id]['label'];
    }

    /**
     * Get section description
     *
     * @param string $section_id Section identifier
     * @return string Section description or empty string
     */
    public function get_section_description($section_id) {
        $section_id = sanitize_text_field($section_id);

        if (!$this->is_valid_section($section_id)) {
            return '';
        }

        return $this->sections[$section_id]['description'];
    }
}
?>
