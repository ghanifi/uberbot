<?php
/**
 * Migration: Create booking form field changes tracking table
 *
 * This table tracks all changes made to booking form fields, allowing analysis of:
 * - User interaction patterns
 * - Field modification frequency
 * - Session-based user behavior
 * - When and how customers modify their selections
 */

return array(
    'name' => 'Create booking form field changes tracking table',
    'sql' => array(
        "CREATE TABLE IF NOT EXISTS wp_booking_form_field_changes (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            form_id BIGINT UNSIGNED NOT NULL,
            field_name VARCHAR(100) NOT NULL,
            field_value VARCHAR(500),
            change_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            user_session VARCHAR(100),
            ip_address VARCHAR(45),
            INDEX idx_form_id (form_id),
            INDEX idx_field_name (field_name),
            INDEX idx_timestamp (change_timestamp)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "ALTER TABLE wp_booking_form_field_changes ADD CONSTRAINT fk_booking_form_field_changes_form_id
         FOREIGN KEY (form_id) REFERENCES wp_booking_form_analytics(id) ON DELETE CASCADE"
    )
);
?>
