<?php
/**
 * Language Domains Manager
 * Handles language-to-domain mappings
 */

if (!defined('ABSPATH')) {
    exit;
}

class Airlinel_Language_Domains {
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'language_domains';
    }

    /**
     * Get all language domains
     */
    public function get_all_domains($active_only = true) {
        global $wpdb;

        $query = "SELECT * FROM {$this->table_name}";
        if ($active_only) {
            $query .= " WHERE is_active = 1";
        }
        $query .= " ORDER BY display_order ASC";

        return $wpdb->get_results($query);
    }

    /**
     * Get domain URL by language code
     */
    public function get_domain_by_language($language_code) {
        global $wpdb;

        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE language_code = %s AND is_active = 1",
            $language_code
        ));

        return $result;
    }

    /**
     * Get language code by domain
     */
    public function get_language_by_domain($domain) {
        global $wpdb;

        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE domain_url = %s AND is_active = 1",
            $domain
        ));

        return $result;
    }

    /**
     * Add or update language domain
     */
    public function save_domain($data) {
        global $wpdb;

        $language_code = sanitize_text_field($data['language_code']);
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE language_code = %s",
            $language_code
        ));

        $prepare_data = array(
            'language_code' => $language_code,
            'language_name' => sanitize_text_field($data['language_name'] ?? ''),
            'domain_url' => sanitize_text_field($data['domain_url'] ?? ''),
            'flag' => sanitize_text_field(substr($data['flag'] ?? '', 0, 2)),
            'is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
            'display_order' => isset($data['display_order']) ? (int) $data['display_order'] : 0
        );

        if ($existing) {
            // Update
            return $wpdb->update($this->table_name, $prepare_data, array('id' => $existing));
        } else {
            // Insert
            return $wpdb->insert($this->table_name, $prepare_data);
        }
    }

    /**
     * Delete language domain
     */
    public function delete_domain($language_code) {
        global $wpdb;

        return $wpdb->delete($this->table_name, array(
            'language_code' => sanitize_text_field($language_code)
        ));
    }

    /**
     * Get all supported languages for dropdown
     */
    public function get_language_options() {
        $domains = $this->get_all_domains(true);
        $options = array();

        foreach ($domains as $domain) {
            $options[$domain->language_code] = array(
                'name' => $domain->language_name,
                'domain' => $domain->domain_url,
                'flag' => $domain->flag
            );
        }

        return $options;
    }
}

// Initialize
new Airlinel_Language_Domains();
