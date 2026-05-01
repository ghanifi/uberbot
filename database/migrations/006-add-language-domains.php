<?php
/**
 * Migration: Add Language Domains Table
 *
 * Creates table for storing language-to-domain mappings
 * allowing redirection to different domains based on selected language
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$charset_collate = $wpdb->get_charset_collate();
$table_name = $wpdb->prefix . 'language_domains';

// Check if table already exists
if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        language_code VARCHAR(10) NOT NULL UNIQUE,
        language_name VARCHAR(50),
        domain_url VARCHAR(255),
        flag CHAR(2),
        is_active TINYINT DEFAULT 1,
        display_order INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_language_code (language_code),
        KEY idx_is_active (is_active),
        KEY idx_display_order (display_order)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Add initial data
    $wpdb->insert($table_name, array(
        'language_code' => 'en_US',
        'language_name' => 'English',
        'domain_url' => 'airlinel.com',
        'flag' => 'EN',
        'is_active' => 1,
        'display_order' => 1
    ));

    error_log('Language Domains table created successfully');
}

// Add session_currency column to booking_analytics if it doesn't exist
$analytics_table = $wpdb->prefix . 'booking_analytics';
$column_exists = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$analytics_table' AND COLUMN_NAME = 'session_currency'");

if (empty($column_exists)) {
    $wpdb->query("ALTER TABLE $analytics_table ADD COLUMN session_currency VARCHAR(3) DEFAULT 'GBP' AFTER currency");
    error_log('session_currency column added to booking_analytics');
}
