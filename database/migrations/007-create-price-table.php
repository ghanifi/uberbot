<?php
/**
 * Migration 007: Create price comparison table
 *
 * Stores price snapshots from Airlinel and competitors
 * for route-based comparison analysis.
 */

return array(
    'name' => 'Create price comparison table',
    'sql'  => "CREATE TABLE IF NOT EXISTS wp_airlinel_price_table (
        id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        source            VARCHAR(80)  NOT NULL DEFAULT 'airlinel' COMMENT 'airlinel | competitor name',
        pickup            VARCHAR(255) NOT NULL,
        dropoff           VARCHAR(255) NOT NULL,
        pickup_resolved   VARCHAR(255) DEFAULT NULL,
        dropoff_resolved  VARCHAR(255) DEFAULT NULL,
        pickup_lat        DOUBLE       DEFAULT NULL,
        pickup_lng        DOUBLE       DEFAULT NULL,
        dropoff_lat       DOUBLE       DEFAULT NULL,
        dropoff_lng       DOUBLE       DEFAULT NULL,
        name              VARCHAR(120) NOT NULL COMMENT 'Vehicle / service name',
        classification    VARCHAR(80)  DEFAULT NULL COMMENT 'economy | business | vip | van',
        price_value       DECIMAL(10,2) NOT NULL,
        currency          VARCHAR(3)   NOT NULL DEFAULT 'GBP',
        eta_min           SMALLINT UNSIGNED DEFAULT NULL,
        trip_min          SMALLINT UNSIGNED DEFAULT NULL,
        capacity          TINYINT UNSIGNED  DEFAULT NULL,
        is_available      TINYINT(1)   NOT NULL DEFAULT 1,
        has_promo         TINYINT(1)   NOT NULL DEFAULT 0,
        price_timestamp   DATETIME     DEFAULT NULL COMMENT 'When this price was valid',
        recorded_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_source       (source),
        INDEX idx_pickup       (pickup(100)),
        INDEX idx_dropoff      (dropoff(100)),
        INDEX idx_currency     (currency),
        INDEX idx_classification (classification),
        INDEX idx_recorded_at  (recorded_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
);
?>
