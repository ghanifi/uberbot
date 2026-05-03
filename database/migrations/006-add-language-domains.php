<?php
/**
 * Migration 006: Create language domains table
 *
 * Stores language-to-domain mappings for multi-language redirects.
 * Also adds session_currency column to booking_analytics if absent.
 *
 * Safe on MySQL 5.7+: runner treats "already exists" as success.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'name' => 'Create language domains table and add session_currency to booking analytics',
    'sql'  => array(
        // Create language domains table (runner uses dbDelta for CREATE TABLE)
        "CREATE TABLE IF NOT EXISTS wp_language_domains (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            language_code VARCHAR(10) NOT NULL,
            language_name VARCHAR(50) DEFAULT NULL,
            domain_url VARCHAR(255) DEFAULT NULL,
            flag CHAR(2) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            display_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_language_code (language_code),
            KEY idx_is_active (is_active),
            KEY idx_display_order (display_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // Seed English as default language domain
        "INSERT IGNORE INTO wp_language_domains
            (language_code, language_name, domain_url, flag, is_active, display_order)
         VALUES
            ('en_US', 'English', 'airlinel.com', 'EN', 1, 1)",

        // Add session_currency column to booking_analytics
        "ALTER TABLE wp_booking_analytics ADD COLUMN session_currency VARCHAR(3) NOT NULL DEFAULT 'GBP' AFTER currency",
    ),
);
?>
