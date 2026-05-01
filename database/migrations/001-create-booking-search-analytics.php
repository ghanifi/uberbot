<?php
/**
 * Migration: Create booking search analytics table
 *
 * This table tracks all search queries made on the booking form,
 * including vehicle selection and payment completion.
 */

return array(
    'name' => 'Create booking search analytics table',
    'sql' => "CREATE TABLE IF NOT EXISTS wp_booking_search_analytics (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        stage VARCHAR(20) NOT NULL DEFAULT 'search',
        pickup VARCHAR(255) NOT NULL,
        dropoff VARCHAR(255) NOT NULL,
        distance_km FLOAT NOT NULL,
        country VARCHAR(5) NOT NULL,
        currency VARCHAR(3) NOT NULL,
        vehicle_count INT NOT NULL,
        source VARCHAR(50) NOT NULL,
        language VARCHAR(10) DEFAULT 'en',
        exchange_rate FLOAT DEFAULT 1,
        site_url VARCHAR(255) DEFAULT 'main',
        ip_address VARCHAR(45),
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_stage (stage),
        INDEX idx_country (country),
        INDEX idx_timestamp (timestamp)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);
?>
