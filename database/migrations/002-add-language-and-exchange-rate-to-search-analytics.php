<?php
/**
 * Migration: Add language, exchange_rate, and site_url columns to search analytics table
 *
 * This migration adds columns to the wp_booking_search_analytics table for:
 * - language: Track the language setting for each search
 * - exchange_rate: Store the exchange rate used at time of search
 * - site_url: Track which site the search originated from (main, regional)
 */

return array(
    'name' => 'Add language, exchange_rate, and site_url columns to search analytics table',
    'sql' => array(
        "ALTER TABLE wp_booking_search_analytics ADD COLUMN IF NOT EXISTS language VARCHAR(10) DEFAULT 'en' AFTER source",
        "ALTER TABLE wp_booking_search_analytics ADD COLUMN IF NOT EXISTS exchange_rate FLOAT DEFAULT 1 AFTER language",
        "ALTER TABLE wp_booking_search_analytics ADD COLUMN IF NOT EXISTS site_url VARCHAR(255) DEFAULT 'main' AFTER exchange_rate",
    )
);
?>
