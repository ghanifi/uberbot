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
     * Execute a single SQL statement, treating benign "already exists"
     * conditions as success rather than failure.
     *
     * Handles:
     *  - Duplicate column  (MySQL 1060) — column was added in a previous partial run
     *  - Duplicate key     (MySQL 1061) — index already exists
     *  - Table exists      (MySQL 1050) — CREATE TABLE without IF NOT EXISTS
     *  - dbDelta is used automatically for CREATE TABLE statements
     *
     * @throws Exception on real errors
     */
    private function execute_statement( string $sql ) {
        global $wpdb;

        $sql = trim( $sql );
        if ( empty( $sql ) ) {
            return;
        }

        // Use dbDelta for CREATE TABLE — handles upgrades + existing tables safely
        if ( preg_match( '/^\s*CREATE\s+TABLE/i', $sql ) ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta( $sql );
            // dbDelta does not set $wpdb->last_error on benign already-exists
            if ( $wpdb->last_error ) {
                throw new Exception( $wpdb->last_error );
            }
            return;
        }

        $wpdb->query( $sql );

        if ( ! $wpdb->last_error ) {
            return; // success
        }

        $err = $wpdb->last_error;

        // Benign conditions — schema already in desired state
        $benign_patterns = array(
            'Duplicate column name',          // 1060 – column already added
            'Duplicate key name',             // 1061 – index already exists
            "Table '",                        // 1050 – table already exists (varies)
            'already exists',                 // generic catch-all
            "Can't DROP",                     // 1091 – dropping non-existent column/key
        );

        foreach ( $benign_patterns as $pat ) {
            if ( stripos( $err, $pat ) !== false ) {
                error_log( '[Airlinel Migrations] Skipped (already applied): ' . $err );
                return; // treat as success
            }
        }

        throw new Exception( $err );
    }

    /**
     * Run all pending migrations
     *
     * @return array Array with 'success' and 'errors' keys
     */
    public function run_all_pending() {
        $pending = $this->get_pending_migrations();
        $results = array(
            'success' => array(),
            'errors'  => array(),
        );

        foreach ( $pending as $file => $data ) {
            $result = $this->run_migration( $file );
            if ( $result['success'] ) {
                $results['success'][] = array(
                    'file' => $file,
                    'name' => $data['name'],
                );
            } else {
                $results['errors'][] = array(
                    'file'  => $file,
                    'name'  => $data['name'],
                    'error' => $result['error'] ?? '',
                );
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
    public function run_migration( $file ) {
        $all       = $this->get_all_migrations();
        $completed = $this->get_completed_migrations();

        if ( ! isset( $all[$file] ) ) {
            return array(
                'success' => false,
                'error'   => 'Migration file not found: ' . $file,
            );
        }

        if ( isset( $completed[$file] ) ) {
            return array(
                'success' => false,
                'error'   => 'Migration already completed: ' . $file,
            );
        }

        $data = $all[$file];

        try {
            $sql = $data['sql'];

            if ( is_array( $sql ) ) {
                foreach ( $sql as $statement ) {
                    $this->execute_statement( $statement );
                }
            } else {
                $this->execute_statement( $sql );
            }

            $this->mark_migration_completed( $file, $data['name'] );
            error_log( '[Airlinel Migrations] Successfully ran: ' . $file );

            return array(
                'success' => true,
                'file'    => $file,
                'name'    => $data['name'],
            );
        } catch ( Exception $e ) {
            error_log( '[Airlinel Migrations] Error running ' . $file . ': ' . $e->getMessage() );

            return array(
                'success' => false,
                'file'    => $file,
                'name'    => $data['name'],
                'error'   => $e->getMessage(),
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
