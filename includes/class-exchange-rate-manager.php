<?php
/**
 * Airlinel Exchange Rate Manager
 * Manages currency exchange rates for GBP conversion
 */
class Airlinel_Exchange_Rate_Manager {

    private $option = 'airlinel_exchange_rates';

    public function get_rates() {
        return get_option($this->option, array(
            'GBP' => 1.00,
            'EUR' => 1.18,
            'TRY' => 42.50,
            'USD' => 1.27,
        ));
    }

    public function get_rate($currency) {
        $rates = $this->get_rates();
        if (!isset($rates[$currency])) {
            error_log('Airlinel: Exchange rate not found for currency ' . sanitize_text_field($currency));
            return false;
        }
        return floatval($rates[$currency]);
    }

    public function set_rates($rates) {
        if (isset($rates['GBP'])) {
            update_option($this->option, $rates);
            // Task 3.4: Update sync timestamp when rates are set
            $timestamps = get_option('airlinel_sync_timestamps', array());
            $timestamps['exchange_rates'] = time();
            update_option('airlinel_sync_timestamps', $timestamps);
            return true;
        }
        return false;
    }
}
?>
