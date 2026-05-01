<?php
/**
 * Airlinel Language Settings
 * Integrated into theme - originally as separate plugin
 * Automatically configures WordPress language based on WPLANG from wp-config.php for regional sites
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Airlinel_Language_Settings {
    const SUPPORTED_LANGUAGES = array(
        'en_US', 'tr_TR', 'de_DE', 'ru_RU', 'fr_FR', 'it_IT', 'ar',
        'da_DK', 'nl_NL', 'sv_SE', 'zh_CN', 'ja', 'es_ES'
    );

    public function __construct() {
        add_action('plugins_loaded', array($this, 'init_language_settings'), 1);
        add_action('admin_notices', array($this, 'admin_notice'));
        add_filter('locale', array($this, 'override_locale'));
    }

    public function init_language_settings() {
        // Check URL parameter first (lang=tr_TR)
        $wplang = '';
        if (isset($_GET['lang']) && !empty($_GET['lang'])) {
            $wplang = sanitize_text_field($_GET['lang']);
            // Validate against supported languages
            if (in_array($wplang, self::SUPPORTED_LANGUAGES, true)) {
                // Update WordPress option if URL param is valid
                update_option('WPLANG', $wplang);
            }
        }

        // Try WordPress option from database (Settings → General)
        if (empty($wplang)) {
            $wplang = get_option('WPLANG', '');
        }

        // Fall back to constant if option not set
        if (empty($wplang) && defined('WPLANG')) {
            $wplang = WPLANG;
        }

        // Default to en_US if nothing is set
        if (empty($wplang)) {
            $wplang = 'en_US';
        }

        // Validate the language
        if (!in_array($wplang, self::SUPPORTED_LANGUAGES, true)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Airlinel Language Settings: Unsupported language code: ' . $wplang);
            }
            // Fall back to English if invalid
            $wplang = 'en_US';
        }

        // Skip loading translations for English (default)
        if ($wplang === 'en_US') {
            return;
        }

        $theme_dir = get_template_directory();
        $languages_dir = $theme_dir . '/languages';

        if (!is_dir($languages_dir)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Airlinel Language Settings: Languages directory not found at: ' . $languages_dir);
            }
            return;
        }

        // Load translation files
        load_theme_textdomain('airlinel-theme', $languages_dir);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Airlinel Language Settings: Loaded language ' . $wplang);
        }
    }

    public function admin_notice() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Check WordPress option first (database setting)
        $wplang = get_option('WPLANG', '');

        // Only show error if neither option nor constant is set
        if (empty($wplang) && !defined('WPLANG')) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p>
                    <strong>Airlinel Language Settings:</strong>
                    No language configured. Please set Site Language in <strong>Settings → General</strong> or define WPLANG constant in wp-config.php.
                </p>
            </div>
            <?php
            return;
        }

        // If still no language, use constant
        if (empty($wplang) && defined('WPLANG')) {
            $wplang = WPLANG;
        }

        // Check languages directory only if non-English language is set
        $languages_dir = get_template_directory() . '/languages';
        if (!is_dir($languages_dir) && !empty($wplang) && $wplang !== 'en_US') {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <strong>Airlinel Language Settings:</strong>
                    Languages directory not found at <code><?php echo esc_html($languages_dir); ?></code>.
                    Language files for <code><?php echo esc_html($wplang); ?></code> may not load correctly.
                    Please ensure the theme's languages/ directory exists with the necessary .po and .mo files.
                </p>
            </div>
            <?php
        }
    }

    public function override_locale($locale) {
        // Check URL parameter first (temporary override)
        if (isset($_GET['lang']) && !empty($_GET['lang'])) {
            $lang = sanitize_text_field($_GET['lang']);
            if (in_array($lang, self::SUPPORTED_LANGUAGES, true)) {
                return $lang;
            }
        }

        // Check WordPress option (primary source - Settings → General)
        $wplang = get_option('WPLANG', '');
        if (!empty($wplang) && in_array($wplang, self::SUPPORTED_LANGUAGES, true)) {
            return $wplang;
        }

        // Check if WPLANG constant is defined
        if (defined('WPLANG') && !empty(WPLANG) && in_array(WPLANG, self::SUPPORTED_LANGUAGES, true)) {
            return WPLANG;
        }

        // Default to en_US
        return 'en_US';
    }

    /**
     * Get supported language information
     */
    public static function get_supported_languages() {
        return array(
            'en_US' => array(
                'name' => 'English (United States)',
                'native_name' => 'English',
                'flag' => '🇺🇸',
            ),
            'tr_TR' => array(
                'name' => 'Türkçe (Türkiye)',
                'native_name' => 'Türkçe',
                'flag' => '🇹🇷',
            ),
            'de_DE' => array(
                'name' => 'Deutsch (Deutschland)',
                'native_name' => 'Deutsch',
                'flag' => '🇩🇪',
            ),
            'ru_RU' => array(
                'name' => 'Русский (Россия)',
                'native_name' => 'Русский',
                'flag' => '🇷🇺',
            ),
            'fr_FR' => array(
                'name' => 'Français (France)',
                'native_name' => 'Français',
                'flag' => '🇫🇷',
            ),
            'it_IT' => array(
                'name' => 'Italiano (Italia)',
                'native_name' => 'Italiano',
                'flag' => '🇮🇹',
            ),
            'es_ES' => array(
                'name' => 'Español (España)',
                'native_name' => 'Español',
                'flag' => '🇪🇸',
            ),
            'ar' => array(
                'name' => 'العربية',
                'native_name' => 'العربية',
                'flag' => '🌍',
            ),
            'da_DK' => array(
                'name' => 'Dansk (Danmark)',
                'native_name' => 'Dansk',
                'flag' => '🇩🇰',
            ),
            'nl_NL' => array(
                'name' => 'Nederlands (Nederland)',
                'native_name' => 'Nederlands',
                'flag' => '🇳🇱',
            ),
            'sv_SE' => array(
                'name' => 'Svenska (Sverige)',
                'native_name' => 'Svenska',
                'flag' => '🇸🇪',
            ),
            'zh_CN' => array(
                'name' => '中文 (简体)',
                'native_name' => '中文',
                'flag' => '🇨🇳',
            ),
            'ja' => array(
                'name' => '日本語',
                'native_name' => '日本語',
                'flag' => '🇯🇵',
            ),
        );
    }

    /**
     * Get language name for a locale
     */
    public static function get_language_name($locale) {
        $languages = self::get_supported_languages();
        if (isset($languages[$locale])) {
            return $languages[$locale]['name'];
        }
        return $locale;
    }
}

// Initialize
new Airlinel_Language_Settings();
