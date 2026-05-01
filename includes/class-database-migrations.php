<?php
/**
 * Airlinel Database Migrations Manager
 *
 * Handles scanning, loading, and executing database migrations.
 * Tracks migration history in wp_options.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Airlinel_Database_Migrations {
    private $migrations_dir;
    private $option_name = 'airlinel_migrations_history';

    /**
     * Constructor - Initialize migrations directory
     */
    public function __construct() {
        $this->migrations_dir = get_template_directory() . '/database/migrations/';
    }

    /**
     * Get all migration files
     *
     * @return array Array of migrations with 'name', 'sql', and 'file' keys
     */
    public function get_all_migrations() {
        $migrations = array();

        if (!is_dir($this->migrations_dir)) {
            return $migrations;
        }

        $files = glob($this->migrations_dir . '*.php');
        sort($files);

        foreach ($files as $file) {
            $data = include $file;
            if (is_array($data) && isset($data['name'])) {
                $basename = basename($file);
                $migrations[$basename] = array(
                    'name' => $data['name'],
                    'sql' => isset($data['sql']) ? $data['sql'] : '',
                    'file' => $basename,
                );
            }
        }

        return $migrations;
    }

    /**
     * Get pending (not yet run) migrations
     *
     * @return array Array of pending migrations
     */
    public function get_pending_migrations() {
        $all = $this->get_all_migrations();
        $completed = $this->get_completed_migrations();

        $pending = array();
        foreach ($all as $file => $data) {
            if (!isset($completed[$file])) {
                $pending[$file] = $data;
            }
        }

        return $pending;
    }

    /**
     * Get completed migrations with timestamps
     *
     * @return array Array of completed migrations with timestamps
     */
    public function get_completed_migrations() {
        $history = get_option($this->option_name, array());
        return is_array($history) ? $history : array();
    }

    /**
     * Run all pending migrations
     *
     * @return array Array with 'success' and 'errors' keys
     */
    public function run_all_pending() {
        global $wpdb;

        $pending = $this->get_pending_migrations();
        $results = array(
            'success' => array(),
            'errors' => array(),
        );

        foreach ($pending as $file => $data) {
            try {
                // Execute SQL
                $sql = $data['sql'];

                if (is_array($sql)) {
                    foreach ($sql as $statement) {
                        if (!empty(trim($statement))) {
                            $wpdb->query($statement);
                            if ($wpdb->last_error) {
                                throw new Exception($wpdb->last_error);
                            }
                        }
                    }
                } else {
                    if (!empty(trim($sql))) {
                        $wpdb->query($sql);
                        if ($wpdb->last_error) {
                            throw new Exception($wpdb->last_error);
                        }
                    }
                }

                // Mark as completed
                $this->mark_migration_completed($file, $data['name']);

                $results['success'][] = array(
                    'file' => $file,
                    'name' => $data['name'],
                );

                error_log('[Airlinel Migrations] Successfully ran: ' . $file);
            } catch (Exception $e) {
                $results['errors'][] = array(
                    'file' => $file,
                    'name' => $data['name'],
                    'error' => $e->getMessage(),
                );

                error_log('[Airlinel Migrations] Error running ' . $file . ': ' . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Run a specific migration by file
     *
     * @param string $file Migration filename
     * @return array Result with status
     */
    public function run_migration($file) {
        global $wpdb;

        $all = $this->get_all_migrations();
        $completed = $this->get_completed_migrations();

        if (!isset($all[$file])) {
            return array(
                'success' => false,
                'error' => 'Migration file not found: ' . $file,
            );
        }

        if (isset($completed[$file])) {
            return array(
                'success' => false,
                'error' => 'Migration already completed: ' . $file,
            );
        }

        $data = $all[$file];

        try {
            $sql = $data['sql'];

            if (is_array($sql)) {
                foreach ($sql as $statement) {
                    if (!empty(trim($statement))) {
                        $wpdb->query($statement);
                        if ($wpdb->last_error) {
                            throw new Exception($wpdb->last_error);
                        }
                    }
                }
            } else {
                if (!empty(trim($sql))) {
                    $wpdb->query($sql);
                    if ($wpdb->last_error) {
                        throw new Exception($wpdb->last_error);
                    }
                }
            }

            $this->mark_migration_completed($file, $data['name']);

            error_log('[Airlinel Migrations] Successfully ran: ' . $file);

            return array(
                'success' => true,
                'file' => $file,
                'name' => $data['name'],
            );
        } catch (Exception $e) {
            error_log('[Airlinel Migrations] Error running ' . $file . ': ' . $e->getMessage());

            return array(
                'success' => false,
                'file' => $file,
                'name' => $data['name'],
                'error' => $e->getMessage(),
            );
        }
    }

    /**
     * Mark migration as completed
     *
     * @param string $file Migration filename
     * @param string $name Migration name
     */
    private function mark_migration_completed($file, $name) {
        $history = get_option($this->option_name, array());

        if (!is_array($history)) {
            $history = array();
        }

        $history[$file] = array(
            'name' => $name,
            'completed_at' => current_time('mysql'),
        );

        update_option($this->option_name, $history);
    }

    /**
     * Reset all migrations (for dev/testing only)
     * This removes all migration history but does NOT rollback database changes
     */
    public function reset_migrations() {
        delete_option($this->option_name);
        error_log('[Airlinel Migrations] Migration history cleared');
    }

    /**
     * Get migration count
     *
     * @return array Array with 'total', 'pending', 'completed' counts
     */
    public function get_counts() {
        $all = $this->get_all_migrations();
        $completed = $this->get_completed_migrations();

        return array(
            'total' => count($all),
            'pending' => count($all) - count($completed),
            'completed' => count($completed),
        );
    }
}
?>
