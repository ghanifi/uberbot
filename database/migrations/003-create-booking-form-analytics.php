<?php
/**
 * Migration: Create booking form analytics table
 *
 * This table tracks all booking form interactions including:
 * - Customer details (name, email, phone)
 * - Vehicle selection
 * - Form progression stages
 * - Pickup/dropoff locations and timing
 * - Additional booking information (flight number, agency code, notes)
 */

return array(
    'name' => 'Create booking form analytics table',
    'sql' => "CREATE TABLE IF NOT EXISTS wp_booking_form_analytics (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        search_id BIGINT UNSIGNED,
        pickup VARCHAR(255),
        dropoff VARCHAR(255),
        distance_km FLOAT,
        country VARCHAR(5),
        language VARCHAR(10),
        customer_name VARCHAR(255),
        customer_email VARCHAR(255),
        customer_phone VARCHAR(50),
        vehicle_id BIGINT UNSIGNED,
        vehicle_name VARCHAR(255),
        vehicle_price VARCHAR(50),
        pickup_date DATE,
        pickup_time TIME,
        flight_number VARCHAR(50),
        agency_code VARCHAR(50),
        notes TEXT,
        form_stage VARCHAR(50),
        site_source VARCHAR(50),
        site_url VARCHAR(255),
        ip_address VARCHAR(45),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_stage (form_stage),
        INDEX idx_country (country),
        INDEX idx_created (created_at),
        INDEX idx_site_source (site_source)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);
?>
