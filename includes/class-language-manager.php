<?php
/**
 * Airlinel Language Manager
 *
 * Manages language and translation functionality for the Airlinel airport transfer platform.
 * Handles 12 language support with WordPress native i18n functions.
 *
 * @package Airlinel
 * @subpackage Language
 */

class Airlinel_Language_Manager {

    /**
     * Supported languages with metadata
     * Format: code => [locale => string, name_english => string, name_native => string, rtl => bool]
     */
    private $supported_languages = array(
        'EN' => array(
            'locale'        => 'en_US',
            'name_english'  => 'English',
            'name_native'   => 'English',
            'rtl'           => false,
        ),
        'TR' => array(
            'locale'        => 'tr_TR',
            'name_english'  => 'Turkish',
            'name_native'   => 'Türkçe',
            'rtl'           => false,
        ),
        'DE' => array(
            'locale'        => 'de_DE',
            'name_english'  => 'German',
            'name_native'   => 'Deutsch',
            'rtl'           => false,
        ),
        'RU' => array(
            'locale'        => 'ru_RU',
            'name_english'  => 'Russian',
            'name_native'   => 'Русский',
            'rtl'           => false,
        ),
        'FR' => array(
            'locale'        => 'fr_FR',
            'name_english'  => 'French',
            'name_native'   => 'Français',
            'rtl'           => false,
        ),
        'IT' => array(
            'locale'        => 'it_IT',
            'name_english'  => 'Italian',
            'name_native'   => 'Italiano',
            'rtl'           => false,
        ),
        'AR' => array(
            'locale'        => 'ar',
            'name_english'  => 'Arabic',
            'name_native'   => 'العربية',
            'rtl'           => true,
        ),
        'DA' => array(
            'locale'        => 'da_DK',
            'name_english'  => 'Danish',
            'name_native'   => 'Dansk',
            'rtl'           => false,
        ),
        'NL' => array(
            'locale'        => 'nl_NL',
            'name_english'  => 'Dutch',
            'name_native'   => 'Nederlands',
            'rtl'           => false,
        ),
        'SV' => array(
            'locale'        => 'sv_SE',
            'name_english'  => 'Swedish',
            'name_native'   => 'Svenska',
            'rtl'           => false,
        ),
        'ZH' => array(
            'locale'        => 'zh_CN',
            'name_english'  => 'Chinese (Simplified)',
            'name_native'   => '简体中文',
            'rtl'           => false,
        ),
        'JA' => array(
            'locale'        => 'ja',
            'name_english'  => 'Japanese',
            'name_native'   => '日本語',
            'rtl'           => false,
        ),
    );

    /**
     * Text domain for theme
     */
    private $text_domain = 'airlinel-theme';

    /**
     * Current language code
     */
    private $current_language = null;

    /**
     * Constructor
     */
    public function __construct() {
        $this->current_language = $this->get_current_language();
    }

    /**
     * Get all supported languages with metadata
     *
     * @return array Associative array of language codes and their metadata
     */
    public function get_supported_languages() {
        return $this->supported_languages;
    }

    /**
     * Get current language code
     *
     * Reads from WordPress WPLANG constant which can be set in wp-config.php
     * or updated via wp_options table
     *
     * @return string Language code (e.g., 'EN', 'TR', 'DE')
     */
    public function get_current_language() {
        if ( null !== $this->current_language ) {
            return $this->current_language;
        }

        // Get language from WordPress options (can be set via admin)
        $wplang = get_option( 'wplang', '' );

        if ( empty( $wplang ) && defined( 'WPLANG' ) ) {
            $wplang = WPLANG;
        }

        // If still empty, default to English
        if ( empty( $wplang ) ) {
            $wplang = 'en_US';
        }

        // Convert locale to language code
        $language_code = $this->locale_to_code( $wplang );

        $this->current_language = $language_code;
        return $language_code;
    }

    /**
     * Get WordPress locale (e.g., 'en_US', 'tr_TR')
     *
     * @return string WordPress locale
     */
    public function get_current_locale() {
        $language_code = $this->get_current_language();
        return $this->supported_languages[ $language_code ]['locale'] ?? 'en_US';
    }

    /**
     * Switch language
     *
     * Updates wp_options table with new language setting
     *
     * @param string $language_code Language code (e.g., 'EN', 'TR')
     * @return bool True if switch successful, false otherwise
     */
    public function switch_language( $language_code ) {
        $language_code = strtoupper( $language_code );

        // Validate language code
        if ( ! isset( $this->supported_languages[ $language_code ] ) ) {
            return false;
        }

        $locale = $this->supported_languages[ $language_code ]['locale'];

        // Update WordPress language setting
        update_option( 'wplang', $locale );

        // Reset current language cache
        $this->current_language = $language_code;

        // Load translations for new language
        $this->load_translations();

        return true;
    }

    /**
     * Get native language name
     *
     * @param string $language_code Language code (e.g., 'EN', 'TR')
     * @return string Native language name (e.g., 'Türkçe' for 'TR')
     */
    public function get_language_name( $language_code ) {
        $language_code = strtoupper( $language_code );

        if ( ! isset( $this->supported_languages[ $language_code ] ) ) {
            return '';
        }

        return $this->supported_languages[ $language_code ]['name_native'];
    }

    /**
     * Get English language name
     *
     * @param string $language_code Language code (e.g., 'EN', 'TR')
     * @return string English language name (e.g., 'Turkish' for 'TR')
     */
    public function get_language_name_english( $language_code ) {
        $language_code = strtoupper( $language_code );

        if ( ! isset( $this->supported_languages[ $language_code ] ) ) {
            return '';
        }

        return $this->supported_languages[ $language_code ]['name_english'];
    }

    /**
     * Check if language is RTL (Right-to-Left)
     *
     * @param string $language_code Language code (e.g., 'EN', 'TR')
     * @return bool True if language is RTL, false otherwise
     */
    public function is_rtl( $language_code ) {
        $language_code = strtoupper( $language_code );

        if ( ! isset( $this->supported_languages[ $language_code ] ) ) {
            return false;
        }

        return $this->supported_languages[ $language_code ]['rtl'];
    }

    /**
     * Load translations for current language
     *
     * Loads the .mo file for the current language
     *
     * @return bool True if translations loaded, false otherwise
     */
    public function load_translations() {
        $locale = $this->get_current_locale();
        $languages_dir = get_template_directory() . '/languages';

        // Load .mo file for theme
        $result = load_textdomain(
            $this->text_domain,
            "{$languages_dir}/airlinel-theme-{$locale}.mo"
        );

        return $result;
    }

    /**
     * Get translated string
     *
     * Wrapper around WordPress __() function
     *
     * @param string $string_id String ID to translate
     * @return string Translated string
     */
    public function get_translated_string( $string_id ) {
        return __( $string_id, $this->text_domain );
    }

    /**
     * Get all translations as array
     *
     * Returns a mapping of original strings to their translations
     * This is useful for JavaScript integration or export
     *
     * @return array Translation map [original => translated]
     */
    public function get_all_translations() {
        // This method would require loading .mo file data
        // For now, return common strings mapping
        $translations = array();

        // Common UI strings
        $common_strings = array(
            'Book Now',
            'Pickup Location',
            'Destination',
            'Pickup Date',
            'Pickup Time',
            'Distance',
            'Estimated Time',
            'Check Availability & Price',
            'Select Your Vehicle',
            'Full Name',
            'Email Address',
            'Phone Number',
            'Confirm Booking',
            'Payment',
            'Booking Confirmed',
            'No Hidden Fees',
            'Premium Vehicles',
            'Expert Chauffeurs',
            'Always On Time',
            'Cities',
            'Fleet',
            'Services',
            'Partners',
            'Contact',
            'About',
            'Blog',
        );

        foreach ( $common_strings as $string ) {
            $translations[ $string ] = $this->get_translated_string( $string );
        }

        return $translations;
    }

    /**
     * Convert WordPress locale to language code
     *
     * @param string $locale WordPress locale (e.g., 'tr_TR', 'en_US')
     * @return string Language code (e.g., 'TR', 'EN')
     */
    private function locale_to_code( $locale ) {
        // Search for matching locale
        foreach ( $this->supported_languages as $code => $lang_data ) {
            if ( $lang_data['locale'] === $locale ) {
                return $code;
            }
        }

        // Default to English if no match
        return 'EN';
    }

    /**
     * Get locale from language code
     *
     * @param string $language_code Language code (e.g., 'TR', 'EN')
     * @return string WordPress locale
     */
    public function code_to_locale( $language_code ) {
        $language_code = strtoupper( $language_code );

        return $this->supported_languages[ $language_code ]['locale'] ?? 'en_US';
    }

    /**
     * Get text domain
     *
     * @return string Theme text domain
     */
    public function get_text_domain() {
        return $this->text_domain;
    }
}
