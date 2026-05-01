<?php
/**
 * Airlinel Migration 005: Add Session Tracking to Analytics
 *
 * Adds session_id, website_id, and website_language columns to all analytics tables
 * to enable complete user journey tracking from search through booking completion.
 *
 * Session tracking allows:
 * - Connecting searches with form fills
 * - Identifying which regional site generated the booking
 * - Tracking language preferences per site
 * - Complete funnel analysis and user journey mapping
 */

if (!defined('ABSPATH')) {
    exit;
}

class Airlinel_Migration_005_Add_Session_Tracking {
    const VERSION = '005';
    const DESCRIPTION = 'Add session tracking columns to analytics tables (session_id, website_id, website_language)';

    /**
     * Run migration - Add session tracking columns
     */
    public static function up() {
        global $wpdb;

        // Table 1: wp_booking_search_analytics
        // Add columns for session tracking searches
        $search_analytics_table = $wpdb->prefix . 'booking_search_analytics';

        // Check if columns already exist before adding
        $existing_columns = $wpdb->get_results("DESCRIBE {$search_analytics_table}");
        $column_names = wp_list_pluck($existing_columns, 'Field');

        if (!in_array('session_id', $column_names)) {
            $wpdb->query("ALTER TABLE {$search_analytics_table} ADD COLUMN session_id VARCHAR(36) UNIQUE KEY COMMENT 'Unique session identifier (UUID v4)'");
            error_log('[Airlinel Migration 005] Added session_id column to wp_booking_search_analytics');
        }

        if (!in_array('website_id', $column_names)) {
            $wpdb->query("ALTER TABLE {$search_analytics_table} ADD COLUMN website_id VARCHAR(50) COMMENT 'Website ID (main, regional-tr, regional-uk, etc.)'");
            error_log('[Airlinel Migration 005] Added website_id column to wp_booking_search_analytics');
        }

        if (!in_array('website_language', $column_names)) {
            $wpdb->query("ALTER TABLE {$search_analytics_table} ADD COLUMN website_language VARCHAR(10) COMMENT 'Website configured language (en, tr, de, etc.)'");
            error_log('[Airlinel Migration 005] Added website_language column to wp_booking_search_analytics');
        }

        // Add indexes for performance
        $indexes = $wpdb->get_results("SHOW INDEX FROM {$search_analytics_table}");
        $index_names = wp_list_pluck($indexes, 'Key_name');

        if (!in_array('idx_session_id', $index_names)) {
            $wpdb->query("ALTER TABLE {$search_analytics_table} ADD INDEX idx_session_id (session_id)");
            error_log('[Airlinel Migration 005] Added idx_session_id index to wp_booking_search_analytics');
        }

        if (!in_array('idx_website_id', $index_names)) {
            $wpdb->query("ALTER TABLE {$search_analytics_table} ADD INDEX idx_website_id (website_id)");
            error_log('[Airlinel Migration 005] Added idx_website_id index to wp_booking_search_analytics');
        }

        // Table 2: wp_booking_form_analytics
        // Add session tracking to form lifecycle tracking
        $form_analytics_table = $wpdb->prefix . 'booking_form_analytics';

        $existing_columns = $wpdb->get_results("DESCRIBE {$form_analytics_table}");
        $column_names = wp_list_pluck($existing_columns, 'Field');

        if (!in_array('session_id', $column_names)) {
            $wpdb->query("ALTER TABLE {$form_analytics_table} ADD COLUMN session_id VARCHAR(36) COMMENT 'Session ID linking to search'");
            error_log('[Airlinel Migration 005] Added session_id column to wp_booking_form_analytics');
        }

        if (!in_array('website_id', $column_names)) {
            $wpdb->query("ALTER TABLE {$form_analytics_table} ADD COLUMN website_id VARCHAR(50) COMMENT 'Website ID that initiated this booking'");
            error_log('[Airlinel Migration 005] Added website_id column to wp_booking_form_analytics');
        }

        if (!in_array('website_language', $column_names)) {
            $wpdb->query("ALTER TABLE {$form_analytics_table} ADD COLUMN website_language VARCHAR(10) COMMENT 'Website language for this booking'");
            error_log('[Airlinel Migration 005] Added website_language column to wp_booking_form_analytics');
        }

        // Add indexes for performance
        $indexes = $wpdb->get_results("SHOW INDEX FROM {$form_analytics_table}");
        $index_names = wp_list_pluck($indexes, 'Key_name');

        if (!in_array('idx_session_id', $index_names)) {
            $wpdb->query("ALTER TABLE {$form_analytics_table} ADD INDEX idx_session_id (session_id)");
            error_log('[Airlinel Migration 005] Added idx_session_id index to wp_booking_form_analytics');
        }

        // Table 3: wp_booking_form_field_changes
        // Add session tracking to field-level changes
        $field_changes_table = $wpdb->prefix . 'booking_form_field_changes';

        $existing_columns = $wpdb->get_results("DESCRIBE {$field_changes_table}");
        $column_names = wp_list_pluck($existing_columns, 'Field');

        if (!in_array('session_id', $column_names)) {
            $wpdb->query("ALTER TABLE {$field_changes_table} ADD COLUMN session_id VARCHAR(36) COMMENT 'Session ID for grouping field changes'");
            error_log('[Airlinel Migration 005] Added session_id column to wp_booking_form_field_changes');
        }

        // Add index for performance
        $indexes = $wpdb->get_results("SHOW INDEX FROM {$field_changes_table}");
        $index_names = wp_list_pluck($indexes, 'Key_name');

        if (!in_array('idx_session_id', $index_names)) {
            $wpdb->query("ALTER TABLE {$field_changes_table} ADD INDEX idx_session_id (session_id)");
            error_log('[Airlinel Migration 005] Added idx_session_id index to wp_booking_form_field_changes');
        }

        error_log('[Airlinel Migration 005] Migration completed successfully');
        return true;
    }

    /**
     * Rollback migration - Remove session tracking columns
     * WARNING: This will remove session tracking data
     */
    public static function down() {
        global $wpdb;

        error_log('[Airlinel Migration 005] Rolling back migration (removing session tracking columns)');

        // Table 1: wp_booking_search_analytics
        $search_analytics_table = $wpdb->prefix . 'booking_search_analytics';

        $wpdb->query("ALTER TABLE {$search_analytics_table} DROP COLUMN IF EXISTS session_id");
        $wpdb->query("ALTER TABLE {$search_analytics_table} DROP COLUMN IF EXISTS website_id");
        $wpdb->query("ALTER TABLE {$search_analytics_table} DROP COLUMN IF EXISTS website_language");
        $wpdb->query("ALTER TABLE {$search_analytics_table} DROP INDEX IF EXISTS idx_session_id");
        $wpdb->query("ALTER TABLE {$search_analytics_table} DROP INDEX IF EXISTS idx_website_id");

        error_log('[Airlinel Migration 005] Rolled back wp_booking_search_analytics');

        // Table 2: wp_booking_form_analytics
        $form_analytics_table = $wpdb->prefix . 'booking_form_analytics';

        $wpdb->query("ALTER TABLE {$form_analytics_table} DROP COLUMN IF EXISTS session_id");
        $wpdb->query("ALTER TABLE {$form_analytics_table} DROP COLUMN IF EXISTS website_id");
        $wpdb->query("ALTER TABLE {$form_analytics_table} DROP COLUMN IF EXISTS website_language");
        $wpdb->query("ALTER TABLE {$form_analytics_table} DROP INDEX IF EXISTS idx_session_id");

        error_log('[Airlinel Migration 005] Rolled back wp_booking_form_analytics');

        // Table 3: wp_booking_form_field_changes
        $field_changes_table = $wpdb->prefix . 'booking_form_field_changes';

        $wpdb->query("ALTER TABLE {$field_changes_table} DROP COLUMN IF EXISTS session_id");
        $wpdb->query("ALTER TABLE {$field_changes_table} DROP INDEX IF EXISTS idx_session_id");

        error_log('[Airlinel Migration 005] Rolled back wp_booking_form_field_changes');
        error_log('[Airlinel Migration 005] Rollback completed');

        return true;
    }

    /**
     * Get migration info
     */
    public static function get_info() {
        return array(
            'version' => self::VERSION,
            'description' => self::DESCRIPTION,
            'tables_affected' => array(
                'wp_booking_search_analytics',
                'wp_booking_form_analytics',
                'wp_booking_form_field_changes',
            ),
            'columns_added' => array(
                'session_id' => 'VARCHAR(36) - Unique session identifier',
                'website_id' => 'VARCHAR(50) - Website identifier (main, regional-tr, etc.)',
                'website_language' => 'VARCHAR(10) - Website configured language',
            ),
            'indexes_added' => array(
                'idx_session_id' => 'Index on session_id for performance',
                'idx_website_id' => 'Index on website_id for filtering',
            ),
        );
    }
}

// Auto-execute on include if not in migration framework context
if (!class_exists('Airlinel_Database_Migrations')) {
    // Migration will be run through the Database Migrations Manager
    error_log('[Airlinel Migration 005] Migration file loaded - ready for execution');
}
?>
