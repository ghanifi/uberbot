<?php
/**
 * Airlinel Zone Manager
 * Manages UK and Turkey pricing zones with area matching
 */
class Airlinel_Zone_Manager {

    private $uk_option = 'airlinel_uk_zones';
    private $tr_option = 'airlinel_tr_zones';

    public function __construct() {
        $this->init_defaults();
    }

    private function init_defaults() {
        if (!get_option($this->uk_option)) {
            update_option($this->uk_option, array(
                'zone_1' => array('name' => 'Zone 1 - Central London', 'base_gbp' => 15, 'postcodes' => array('EC', 'WC', 'SW1', 'W1')),
                'zone_2' => array('name' => 'Zone 2', 'base_gbp' => 12.50, 'postcodes' => array('E', 'N', 'NW', 'SE5', 'SW2')),
                'zone_3' => array('name' => 'Zone 3', 'base_gbp' => 10, 'postcodes' => array('E3', 'E8', 'N3', 'NW2')),
            ));
        }
        if (!get_option($this->tr_option)) {
            update_option($this->tr_option, array(
                'istanbul_center' => array('name' => 'Istanbul Center', 'areas' => array('Sultanahmet', 'Beyoğlu', 'Taksim'), 'base_gbp' => 12),
                'istanbul_airport' => array('name' => 'Istanbul Airport', 'areas' => array('Istanbul Airport', 'Sabiha Gökçen'), 'base_gbp' => 25),
                'ankara_center' => array('name' => 'Ankara Center', 'areas' => array('Kızılay', 'Tunalı'), 'base_gbp' => 6.5),
                'antalya_airport' => array('name' => 'Antalya Airport', 'areas' => array('Antalya Airport'), 'base_gbp' => 12),
            ));
        }
    }

    // UK Zones
    public function get_uk_zones() {
        return get_option($this->uk_option, array());
    }

    public function add_uk_zone($id, $data) {
        $zones = $this->get_uk_zones();
        $zones[$id] = $data;
        update_option($this->uk_option, $zones);
    }

    public function update_uk_zone($id, $data) {
        $this->add_uk_zone($id, $data);
    }

    public function delete_uk_zone($id) {
        $zones = $this->get_uk_zones();
        unset($zones[$id]);
        update_option($this->uk_option, $zones);
    }

    public function match_uk_zone($postcode) {
        $prefix = strtoupper(substr(trim($postcode), 0, 2));
        foreach ($this->get_uk_zones() as $zone) {
            if (in_array($prefix, $zone['postcodes'])) {
                return $zone;
            }
        }
        return null;
    }

    // TR Zones
    public function get_tr_zones() {
        return get_option($this->tr_option, array());
    }

    public function add_tr_zone($id, $data) {
        $zones = $this->get_tr_zones();
        $zones[$id] = $data;
        update_option($this->tr_option, $zones);
    }

    public function update_tr_zone($id, $data) {
        $this->add_tr_zone($id, $data);
    }

    public function delete_tr_zone($id) {
        $zones = $this->get_tr_zones();
        unset($zones[$id]);
        update_option($this->tr_option, $zones);
    }

    public function match_tr_zone($location) {
        foreach ($this->get_tr_zones() as $zone) {
            if (isset($zone['areas'])) {
                foreach ($zone['areas'] as $area) {
                    if (stripos($location, $area) !== false) {
                        return $zone;
                    }
                }
            }
        }
        return null;
    }
}
?>
