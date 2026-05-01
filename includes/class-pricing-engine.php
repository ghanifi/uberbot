<?php
/**
 * Airlinel Pricing Engine
 * GBP-based pricing calculation with dynamic zone management
 */
class Airlinel_Pricing_Engine {

    private $zone_mgr;
    private $exchange_mgr;

    public function __construct() {
        $this->zone_mgr = new Airlinel_Zone_Manager();
        $this->exchange_mgr = new Airlinel_Exchange_Rate_Manager();
    }

    public function calculate($pickup, $dropoff, $passengers = 1, $currency = 'GBP', $country = 'UK', $distance = null) {
        // Use provided distance or calculate from Google Maps
        if ($distance === null) {
            $distance = $this->get_distance($pickup, $dropoff);

            if (!$distance) {
                return array('error' => 'Could not calculate distance');
            }
        }

        // Calculate in GBP
        if ($country === 'UK') {
            $base_gbp = $this->calculate_uk_price($pickup, $distance);
        } else if ($country === 'TR') {
            $base_gbp = $this->calculate_tr_price($dropoff, $distance);
        } else {
            $base_gbp = $distance * 0.70;
        }

        // Passenger multiplier
        $multiplier = max(1, ($passengers - 1) * 0.5 + 1);
        $total_gbp = $base_gbp * $multiplier;

        // Convert to currency
        $rate = $this->exchange_mgr->get_rate($currency);
        if ($rate === false) {
            return array('error' => 'Exchange rate not available for ' . $currency);
        }
        $final = $total_gbp * $rate;

        return array(
            'success' => true,
            'distance_km' => round($distance, 2),
            'base_price_gbp' => round($base_gbp, 2),
            'passengers' => $passengers,
            'multiplier' => round($multiplier, 2),
            'total_gbp' => round($total_gbp, 2),
            'currency' => $currency,
            'rate' => round($rate, 4),
            'total_display' => round($final, 2),
        );
    }

    private function calculate_uk_price($postcode, $distance) {
        $zone = $this->zone_mgr->match_uk_zone($postcode);
        if ($zone) {
            return $zone['base_gbp'];
        }
        return $distance * floatval(Airlinel_Settings_Manager::get('airlinel_uk_km_rate', '0.75'));
    }

    private function calculate_tr_price($location, $distance) {
        $zone = $this->zone_mgr->match_tr_zone($location);
        if ($zone) {
            return $zone['base_gbp'];
        }
        return $distance * floatval(Airlinel_Settings_Manager::get('airlinel_tr_km_rate', '0.65'));
    }

    private function get_distance($pickup, $dropoff) {
        $key = Airlinel_Settings_Manager::get('airlinel_google_maps_key');
        if (!$key) return false;

        // Cache key based on route
        $cache_key = 'airlinel_distance_' . md5(strtolower($pickup . '|' . $dropoff));
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $url = 'https://maps.googleapis.com/maps/api/distancematrix/json?' . http_build_query(array(
            'origins' => $pickup,
            'destinations' => $dropoff,
            'key' => $key,
        ));

        $response = wp_remote_get($url);
        if (is_wp_error($response)) return false;

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!$body || !isset($body['status'])) {
            error_log('Airlinel: Invalid Google Maps response');
            return false;
        }

        if ($body['status'] === 'ZERO_RESULTS') {
            error_log('Airlinel: No route found between ' . $pickup . ' and ' . $dropoff);
            return false;
        }

        if ($body['status'] === 'OVER_QUERY_LIMIT') {
            error_log('Airlinel: Google Maps API quota exceeded');
            return false;
        }

        if ($body['status'] !== 'OK') {
            error_log('Airlinel: Google Maps error - ' . $body['status']);
            return false;
        }

        if (!isset($body['rows'][0]['elements'][0]['distance']['value'])) {
            error_log('Airlinel: Unexpected Google Maps response structure');
            return false;
        }

        $distance = $body['rows'][0]['elements'][0]['distance']['value'] / 1000;

        // Cache for 7 days (604800 seconds)
        set_transient($cache_key, $distance, 604800);
        return $distance;
    }
}
?>
