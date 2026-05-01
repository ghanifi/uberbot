<?php
/**
 * Airlinel Ads.txt Manager
 * Manages ad network publisher IDs and generates ads.txt file
 */

class Airlinel_Ads_Txt_Manager {

    const OPTION_KEY = 'airlinel_ads_txt_entries';
    const MAX_ENTRIES = 100;
    const VALID_RELATIONSHIPS = array('DIRECT', 'RESELLER');

    /**
     * Get all ads.txt entries
     */
    public function get_entries() {
        $entries = get_option(self::OPTION_KEY, array());
        if (is_string($entries)) {
            $entries = json_decode($entries, true);
        }
        return is_array($entries) ? $entries : array();
    }

    /**
     * Add new ads.txt entry
     */
    public function add_entry($domain, $pub_id, $relationship, $cert_id = '') {
        $validation = $this->validate_entry($domain, $pub_id, $relationship, $cert_id);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $entries = $this->get_entries();

        // Check for duplicate domain (case-insensitive)
        $domain_lower = strtolower(trim($domain));
        foreach ($entries as $entry) {
            if (strtolower(trim($entry['domain'])) === $domain_lower) {
                return new WP_Error('duplicate_domain', 'This domain already has an entry.');
            }
        }

        // Check entry limit
        if (count($entries) >= self::MAX_ENTRIES) {
            return new WP_Error('max_entries', 'Maximum entries reached (100 limit).');
        }

        $new_entry = array(
            'id' => uniqid(),
            'domain' => sanitize_text_field($domain),
            'pub_id' => sanitize_text_field($pub_id),
            'relationship' => sanitize_text_field($relationship),
            'cert_id' => sanitize_text_field($cert_id),
            'added_at' => current_time('mysql'),
        );

        $entries[] = $new_entry;
        update_option(self::OPTION_KEY, $entries);

        // Generate the ads.txt file
        $this->generate_file();

        return $new_entry;
    }

    /**
     * Update existing ads.txt entry
     */
    public function update_entry($id, $domain, $pub_id, $relationship, $cert_id = '') {
        $validation = $this->validate_entry($domain, $pub_id, $relationship, $cert_id);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $entries = $this->get_entries();
        $found = false;

        foreach ($entries as &$entry) {
            if ($entry['id'] === $id) {
                // Check if new domain conflicts with another entry (case-insensitive)
                $domain_lower = strtolower(trim($domain));
                $current_domain_lower = strtolower(trim($entry['domain']));

                if ($current_domain_lower !== $domain_lower) {
                    foreach ($entries as $other) {
                        if ($other['id'] !== $id && strtolower(trim($other['domain'])) === $domain_lower) {
                            return new WP_Error('duplicate_domain', 'This domain already has an entry.');
                        }
                    }
                }

                $entry['domain'] = sanitize_text_field($domain);
                $entry['pub_id'] = sanitize_text_field($pub_id);
                $entry['relationship'] = sanitize_text_field($relationship);
                $entry['cert_id'] = sanitize_text_field($cert_id);
                $entry['updated_at'] = current_time('mysql');
                $found = true;
                break;
            }
        }

        if (!$found) {
            return new WP_Error('not_found', 'Entry not found.');
        }

        update_option(self::OPTION_KEY, $entries);
        $this->generate_file();

        return true;
    }

    /**
     * Delete ads.txt entry
     */
    public function delete_entry($id) {
        $entries = $this->get_entries();
        $new_entries = array();

        foreach ($entries as $entry) {
            if ($entry['id'] !== $id) {
                $new_entries[] = $entry;
            }
        }

        if (count($new_entries) === count($entries)) {
            return new WP_Error('not_found', 'Entry not found.');
        }

        update_option(self::OPTION_KEY, $new_entries);
        $this->generate_file();

        return true;
    }

    /**
     * Validate domain format
     */
    public function validate_domain($domain) {
        $domain = trim($domain);

        if (empty($domain)) {
            return new WP_Error('empty_domain', 'Domain cannot be empty.');
        }

        // Must contain at least one dot and only contain alphanumeric, dots, and hyphens
        if (!preg_match('/^[a-z0-9][a-z0-9.-]*\.[a-z0-9]{2,}$/i', $domain)) {
            return new WP_Error('invalid_domain', 'Invalid domain format. Must contain a dot and only alphanumeric, dots, and hyphens.');
        }

        return true;
    }

    /**
     * Validate all entry fields
     */
    public function validate_entry($domain, $pub_id, $relationship, $cert_id = '') {
        // Validate domain
        $domain_check = $this->validate_domain($domain);
        if (is_wp_error($domain_check)) {
            return $domain_check;
        }

        // Validate publisher ID
        $pub_id = trim($pub_id);
        if (empty($pub_id)) {
            return new WP_Error('empty_pub_id', 'Publisher ID cannot be empty.');
        }
        if (strlen($pub_id) > 100) {
            return new WP_Error('long_pub_id', 'Publisher ID cannot exceed 100 characters.');
        }
        if (!preg_match('/^[a-z0-9_-]+$/i', $pub_id)) {
            return new WP_Error('invalid_pub_id', 'Publisher ID can only contain alphanumeric characters, underscores, and hyphens.');
        }

        // Validate relationship
        $relationship = strtoupper(trim($relationship));
        if (!in_array($relationship, self::VALID_RELATIONSHIPS)) {
            return new WP_Error('invalid_relationship', 'Relationship must be DIRECT or RESELLER.');
        }

        // Validate certification ID (optional)
        if (!empty($cert_id)) {
            $cert_id = trim($cert_id);
            if (strlen($cert_id) > 50) {
                return new WP_Error('long_cert_id', 'Certification ID cannot exceed 50 characters.');
            }
            if (!preg_match('/^[a-z0-9-]+$/i', $cert_id)) {
                return new WP_Error('invalid_cert_id', 'Certification ID can only contain alphanumeric characters and hyphens.');
            }
        }

        return true;
    }

    /**
     * Get the ads.txt file path
     */
    public function get_file_path() {
        return ABSPATH . 'ads.txt';
    }

    /**
     * Check if ads.txt file is writable
     */
    public function is_file_writable() {
        $path = $this->get_file_path();
        if (file_exists($path)) {
            return is_writable($path);
        }
        // Check if directory is writable
        return is_writable(ABSPATH);
    }

    /**
     * Generate ads.txt file from entries
     */
    public function generate_file() {
        $entries = $this->get_entries();
        $file_path = $this->get_file_path();

        if (!$this->is_file_writable()) {
            return new WP_Error('not_writable', 'ads.txt file is not writable.');
        }

        $content = $this->build_file_content($entries);

        $result = file_put_contents($file_path, $content);

        if ($result === false) {
            return new WP_Error('write_failed', 'Failed to write ads.txt file.');
        }

        return true;
    }

    /**
     * Build ads.txt file content
     */
    private function build_file_content($entries) {
        $lines = array();
        $lines[] = '# Airlinel Transfer Platform - ads.txt';
        $lines[] = '# Last updated: ' . current_time('Y-m-d H:i:s');
        $lines[] = '';

        if (empty($entries)) {
            $lines[] = '# No publisher entries configured yet.';
        } else {
            foreach ($entries as $entry) {
                $line = $entry['domain'] . ', ' . $entry['pub_id'] . ', ' . $entry['relationship'];
                if (!empty($entry['cert_id'])) {
                    $line .= ', ' . $entry['cert_id'];
                }
                $lines[] = $line;
            }
        }

        return implode("\n", $lines) . "\n";
    }

    /**
     * Get current ads.txt file content
     */
    public function get_file_content() {
        $path = $this->get_file_path();
        if (file_exists($path)) {
            return file_get_contents($path);
        }
        return '';
    }

    /**
     * Get file status info
     */
    public function get_file_status() {
        $path = $this->get_file_path();

        return array(
            'exists' => file_exists($path),
            'readable' => file_exists($path) && is_readable($path),
            'writable' => $this->is_file_writable(),
            'last_modified' => file_exists($path) ? filemtime($path) : null,
            'size' => file_exists($path) ? filesize($path) : 0,
            'path' => $path,
        );
    }
}
?>
