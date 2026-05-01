<?php
/**
 * Currency Session Manager
 * Handles session-based currency management
 */

if (!defined('ABSPATH')) {
    exit;
}

class Airlinel_Currency_Session {
    const SESSION_KEY = 'airlinel_currency';
    const VALID_CURRENCIES = array('GBP', 'EUR', 'TRY', 'USD');
    const DEFAULT_CURRENCY = 'GBP';

    public function __construct() {
        // Skip session for REST API requests - prevent session locking
        if ($this->is_rest_request()) {
            return;
        }

        // Initialize session if needed
        if (!session_id()) {
            session_start();
        }

        // Set default currency
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = self::DEFAULT_CURRENCY;
        }

        // Handle currency parameter from GET
        $this->handle_currency_parameter();

        // Add hooks
        add_action('wp_footer', array($this, 'print_currency_script'));
        add_action('wp_footer', array($this, 'close_session'), 999);
    }

    /**
     * Check if this is a REST API request
     * Use multiple methods to ensure accurate detection
     */
    private function is_rest_request() {
        // Method 1: Check REST_REQUEST constant
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return true;
        }

        // Method 2: Check request URI (wp-json endpoint)
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/wp-json/') !== false) {
            return true;
        }

        // Method 3: Check WordPress function (WP 5.0+)
        if (function_exists('wp_is_json_request') && wp_is_json_request()) {
            return true;
        }

        return false;
    }

    /**
     * Close session after page is generated
     */
    public function close_session() {
        if (session_id()) {
            session_write_close();
        }
    }

    /**
     * Handle currency parameter from GET request
     */
    private function handle_currency_parameter() {
        if (isset($_GET['currency'])) {
            $currency = sanitize_text_field($_GET['currency']);
            if (in_array($currency, self::VALID_CURRENCIES, true)) {
                $_SESSION[self::SESSION_KEY] = $currency;
            }
        }
    }

    /**
     * Get current session currency
     */
    public static function get_currency() {
        if (!session_id()) {
            session_start();
        }

        return isset($_SESSION[self::SESSION_KEY]) ? $_SESSION[self::SESSION_KEY] : self::DEFAULT_CURRENCY;
    }

    /**
     * Set session currency
     */
    public static function set_currency($currency) {
        if (!session_id()) {
            session_start();
        }

        if (in_array($currency, self::VALID_CURRENCIES, true)) {
            $_SESSION[self::SESSION_KEY] = $currency;
            return true;
        }

        return false;
    }

    /**
     * Get all valid currencies
     */
    public static function get_valid_currencies() {
        return self::VALID_CURRENCIES;
    }

    /**
     * Get currency URL parameter
     */
    public static function get_currency_param() {
        $currency = self::get_currency();
        return '?currency=' . $currency;
    }

    /**
     * Print currency to JavaScript global
     */
    public function print_currency_script() {
        $currency = self::get_currency();
        ?>
        <script>
        // Airlinel Currency Session
        window.airinelCurrency = '<?php echo esc_js($currency); ?>';
        console.log('Session currency:', window.airinelCurrency);
        </script>
        <?php
    }
}

// Initialize
new Airlinel_Currency_Session();
