<?php
/**
 * Migration 005: Add session tracking to analytics tables
 *
 * Adds session_id, website_id, website_language columns to all three
 * analytics tables so every search and form event can be correlated
 * into a complete user journey.
 *
 * Safe on MySQL 5.7+: each statement is run through the migration runner
 * which treats "Duplicate column name" / "Duplicate key name" as success.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'name' => 'Add session tracking columns to analytics tables',
    'sql'  => array(
        // ── wp_booking_search_analytics ─────────────────────────────
        "ALTER TABLE wp_booking_search_analytics ADD COLUMN session_id VARCHAR(36) DEFAULT NULL COMMENT 'Unique session identifier (UUID v4)'",
        "ALTER TABLE wp_booking_search_analytics ADD COLUMN website_id VARCHAR(50) DEFAULT NULL COMMENT 'Website ID (main, regional-tr, regional-uk, etc.)'",
        "ALTER TABLE wp_booking_search_analytics ADD COLUMN website_language VARCHAR(10) DEFAULT NULL COMMENT 'Website configured language (en, tr, de, etc.)'",
        "ALTER TABLE wp_booking_search_analytics ADD INDEX idx_session_id (session_id)",
        "ALTER TABLE wp_booking_search_analytics ADD INDEX idx_website_id (website_id)",

        // ── wp_booking_form_analytics ────────────────────────────────
        "ALTER TABLE wp_booking_form_analytics ADD COLUMN session_id VARCHAR(36) DEFAULT NULL COMMENT 'Session ID linking to search'",
        "ALTER TABLE wp_booking_form_analytics ADD COLUMN website_id VARCHAR(50) DEFAULT NULL COMMENT 'Website ID that initiated this booking'",
        "ALTER TABLE wp_booking_form_analytics ADD COLUMN website_language VARCHAR(10) DEFAULT NULL COMMENT 'Website language for this booking'",
        "ALTER TABLE wp_booking_form_analytics ADD INDEX idx_session_id (session_id)",

        // ── wp_booking_form_field_changes ────────────────────────────
        "ALTER TABLE wp_booking_form_field_changes ADD COLUMN session_id VARCHAR(36) DEFAULT NULL COMMENT 'Session ID for grouping field changes'",
        "ALTER TABLE wp_booking_form_field_changes ADD INDEX idx_session_id (session_id)",
    ),
);
?>
