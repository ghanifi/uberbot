<?php
/**
 * Airlinel Price Table
 * Handles all DB operations for the price comparison table.
 */
class Airlinel_Price_Table {

    const TABLE = 'wp_airlinel_price_table';

    // ── Schema ────────────────────────────────────────────────────

    /**
     * Create (or upgrade) the price table.
     * Called on activation and from the DB migrations admin page.
     */
    public static function create_table() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $table   = $wpdb->prefix . 'airlinel_price_table';

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            source            VARCHAR(80)   NOT NULL DEFAULT 'airlinel',
            pickup            VARCHAR(255)  NOT NULL,
            dropoff           VARCHAR(255)  NOT NULL,
            pickup_resolved   VARCHAR(255)  DEFAULT NULL,
            dropoff_resolved  VARCHAR(255)  DEFAULT NULL,
            pickup_lat        DOUBLE        DEFAULT NULL,
            pickup_lng        DOUBLE        DEFAULT NULL,
            dropoff_lat       DOUBLE        DEFAULT NULL,
            dropoff_lng       DOUBLE        DEFAULT NULL,
            name              VARCHAR(120)  NOT NULL,
            classification    VARCHAR(80)   DEFAULT NULL,
            price_value       DECIMAL(10,2) NOT NULL,
            currency          VARCHAR(3)    NOT NULL DEFAULT 'GBP',
            eta_min           SMALLINT UNSIGNED DEFAULT NULL,
            trip_min          SMALLINT UNSIGNED DEFAULT NULL,
            capacity          TINYINT UNSIGNED  DEFAULT NULL,
            is_available      TINYINT(1)    NOT NULL DEFAULT 1,
            has_promo         TINYINT(1)    NOT NULL DEFAULT 0,
            price_timestamp   DATETIME      DEFAULT NULL,
            recorded_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_source        (source),
            INDEX idx_pickup        (pickup(100)),
            INDEX idx_dropoff       (dropoff(100)),
            INDEX idx_currency      (currency),
            INDEX idx_classification(classification),
            INDEX idx_recorded_at   (recorded_at)
        ) ENGINE=InnoDB {$charset}";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    // ── Write ─────────────────────────────────────────────────────

    /**
     * Insert or update a single price entry.
     *
     * @param array $row  Associative array matching table columns.
     * @return int|false  Inserted/updated ID, or false on error.
     */
    public static function upsert( array $row ) {
        global $wpdb;
        $table = $wpdb->prefix . 'airlinel_price_table';

        $data = self::sanitize_row( $row );
        $wpdb->insert( $table, $data );
        return $wpdb->insert_id ?: false;
    }

    /**
     * Bulk insert entries (ignores duplicates gracefully).
     *
     * @param array $rows  Array of row arrays.
     * @return array       ['inserted' => int, 'errors' => int]
     */
    public static function bulk_insert( array $rows ) {
        $inserted = 0;
        $errors   = 0;
        foreach ( $rows as $row ) {
            $result = self::upsert( $row );
            $result ? $inserted++ : $errors++;
        }
        return compact( 'inserted', 'errors' );
    }

    /**
     * Delete entries by source (e.g. clear a competitor's data).
     */
    public static function delete_by_source( string $source ) {
        global $wpdb;
        $table = $wpdb->prefix . 'airlinel_price_table';
        return $wpdb->delete( $table, array( 'source' => $source ), array( '%s' ) );
    }

    /**
     * Delete a single entry by ID.
     */
    public static function delete( int $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'airlinel_price_table';
        return $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
    }

    // ── Read ──────────────────────────────────────────────────────

    /**
     * Query price entries with optional filters.
     *
     * @param array $args {
     *   @type string   $pickup         Partial match on pickup
     *   @type string   $dropoff        Partial match on dropoff
     *   @type string   $source         Exact match on source
     *   @type string   $currency       Exact match on currency
     *   @type string   $classification Exact match
     *   @type int      $capacity_min   Minimum capacity
     *   @type bool     $available_only Only is_available = 1
     *   @type int      $limit          Default 100
     *   @type int      $offset         Default 0
     *   @type string   $order_by       price_value|recorded_at|trip_min  (default price_value)
     *   @type string   $order          ASC|DESC (default ASC)
     * }
     * @return array
     */
    public static function query( array $args = array() ) {
        global $wpdb;
        $table = $wpdb->prefix . 'airlinel_price_table';

        $where  = array( '1=1' );
        $values = array();

        if ( ! empty( $args['source'] ) ) {
            $where[]  = 'source = %s';
            $values[] = $args['source'];
        }
        if ( ! empty( $args['pickup'] ) ) {
            $where[]  = '(pickup LIKE %s OR pickup_resolved LIKE %s)';
            $like     = '%' . $wpdb->esc_like( $args['pickup'] ) . '%';
            $values[] = $like;
            $values[] = $like;
        }
        if ( ! empty( $args['dropoff'] ) ) {
            $where[]  = '(dropoff LIKE %s OR dropoff_resolved LIKE %s)';
            $like     = '%' . $wpdb->esc_like( $args['dropoff'] ) . '%';
            $values[] = $like;
            $values[] = $like;
        }
        if ( ! empty( $args['currency'] ) ) {
            $where[]  = 'currency = %s';
            $values[] = strtoupper( $args['currency'] );
        }
        if ( ! empty( $args['classification'] ) ) {
            $where[]  = 'classification = %s';
            $values[] = $args['classification'];
        }
        if ( ! empty( $args['capacity_min'] ) ) {
            $where[]  = 'capacity >= %d';
            $values[] = (int) $args['capacity_min'];
        }
        if ( ! empty( $args['available_only'] ) ) {
            $where[] = 'is_available = 1';
        }

        $allowed_order = array( 'price_value', 'recorded_at', 'trip_min', 'eta_min', 'id' );
        $order_by = in_array( $args['order_by'] ?? '', $allowed_order, true )
            ? $args['order_by']
            : 'price_value';
        $order = strtoupper( $args['order'] ?? 'ASC' ) === 'DESC' ? 'DESC' : 'ASC';

        $limit  = min( (int) ( $args['limit']  ?? 100 ), 500 );
        $offset = max( (int) ( $args['offset'] ?? 0   ),   0 );

        $sql = "SELECT * FROM {$table} WHERE " . implode( ' AND ', $where )
             . " ORDER BY {$order_by} {$order}"
             . " LIMIT %d OFFSET %d";

        $values[] = $limit;
        $values[] = $offset;

        return $wpdb->get_results(
            empty( array_filter( $values ) )
                ? $wpdb->prepare( $sql, $limit, $offset )
                : $wpdb->prepare( $sql, ...$values ),
            ARRAY_A
        );
    }

    /**
     * Return distinct sources (for filter dropdowns).
     */
    public static function get_sources() {
        global $wpdb;
        $table = $wpdb->prefix . 'airlinel_price_table';
        return $wpdb->get_col( "SELECT DISTINCT source FROM {$table} ORDER BY source ASC" );
    }

    /**
     * Row count per source (for admin dashboard widget).
     */
    public static function count_by_source() {
        global $wpdb;
        $table = $wpdb->prefix . 'airlinel_price_table';
        return $wpdb->get_results(
            "SELECT source, COUNT(*) AS cnt FROM {$table} GROUP BY source ORDER BY source ASC",
            ARRAY_A
        );
    }

    /**
     * Total row count.
     */
    public static function total_count() {
        global $wpdb;
        $table = $wpdb->prefix . 'airlinel_price_table';
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
    }

    // ── Helpers ───────────────────────────────────────────────────

    private static function sanitize_row( array $row ): array {
        return array(
            'source'           => sanitize_text_field( $row['source']           ?? 'airlinel' ),
            'pickup'           => sanitize_text_field( $row['pickup']           ?? '' ),
            'dropoff'          => sanitize_text_field( $row['dropoff']          ?? '' ),
            'pickup_resolved'  => sanitize_text_field( $row['pickup_resolved']  ?? '' ) ?: null,
            'dropoff_resolved' => sanitize_text_field( $row['dropoff_resolved'] ?? '' ) ?: null,
            'pickup_lat'       => isset( $row['pickup_lat']  ) ? (float) $row['pickup_lat']  : null,
            'pickup_lng'       => isset( $row['pickup_lng']  ) ? (float) $row['pickup_lng']  : null,
            'dropoff_lat'      => isset( $row['dropoff_lat'] ) ? (float) $row['dropoff_lat'] : null,
            'dropoff_lng'      => isset( $row['dropoff_lng'] ) ? (float) $row['dropoff_lng'] : null,
            'name'             => sanitize_text_field( $row['name']             ?? '' ),
            'classification'   => sanitize_text_field( $row['classification']   ?? '' ) ?: null,
            'price_value'      => round( (float) ( $row['price_value'] ?? 0 ), 2 ),
            'currency'         => strtoupper( substr( sanitize_text_field( $row['currency'] ?? 'GBP' ), 0, 3 ) ),
            'eta_min'          => isset( $row['eta_min']  ) ? (int) $row['eta_min']  : null,
            'trip_min'         => isset( $row['trip_min'] ) ? (int) $row['trip_min'] : null,
            'capacity'         => isset( $row['capacity'] ) ? (int) $row['capacity'] : null,
            'is_available'     => (int) ( $row['is_available'] ?? 1 ),
            'has_promo'        => (int) ( $row['has_promo']    ?? 0 ),
            'price_timestamp'  => ! empty( $row['timestamp'] ) ? date( 'Y-m-d H:i:s', strtotime( $row['timestamp'] ) ) : null,
        );
    }
}
?>
