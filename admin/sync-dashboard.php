<?php
/**
 * Airlinel Synchronization Dashboard
 * Monitor data synchronization status across main and regional sites
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check permissions (also verified in callback)
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to view the sync dashboard.');
}

$sync_mgr = new Airlinel_Data_Sync_Manager();

    // Handle manual sync actions
    $action_result = null;
    if (isset($_POST['airlinel_sync_action'])) {
        check_admin_referer('sync_dashboard_nonce');

        if ($_POST['airlinel_sync_action'] === 'sync_vehicles') {
            $action_result = $sync_mgr->sync_vehicles();
        } elseif ($_POST['airlinel_sync_action'] === 'sync_exchange_rates') {
            // Get rates from form submission
            $rates = array(
                'GBP' => 1.0,
                'EUR' => floatval($_POST['rate_eur'] ?? 1.18),
                'TRY' => floatval($_POST['rate_try'] ?? 42.50),
                'USD' => floatval($_POST['rate_usd'] ?? 1.27),
            );
            $action_result = array(
                'success' => $sync_mgr->sync_exchange_rates($rates),
                'message' => 'Exchange rates updated',
            );
        } elseif ($_POST['airlinel_sync_action'] === 'clear_log') {
            $sync_mgr->clear_sync_log();
            $action_result = array(
                'success' => true,
                'message' => 'Sync log cleared',
            );
        }
    }

    // Get sync data
    $health = $sync_mgr->verify_sync_health();
    $stats = $sync_mgr->get_sync_stats();
    $exchange_rates = $sync_mgr->get_exchange_rates();
    $sync_log = $sync_mgr->get_sync_log(50);

    // Determine health color
    $health_color = match($health['overall']) {
        'healthy' => '#28a745',
        'warning' => '#ffc107',
        'error' => '#dc3545',
        default => '#6c757d',
    };

    $health_text = match($health['overall']) {
        'healthy' => 'HEALTHY',
        'warning' => 'WARNING',
        'error' => 'ERROR',
        default => 'UNKNOWN',
    };

    $vehicles_data = $sync_mgr->get_vehicles();
    $vehicles_by_country = array('UK' => 0, 'TR' => 0);
    foreach ($vehicles_data as $vehicle) {
        $country = $vehicle['country'] ?? 'UK';
        $vehicles_by_country[$country]++;
    }

    $user_count = count_users();
    $reservation_count = wp_count_posts('reservations');
    ?>
    <div class="wrap">
        <h1>Synchronization Dashboard</h1>

        <?php if ($action_result): ?>
            <div class="notice notice-<?php echo $action_result['success'] ? 'success' : 'error'; ?>">
                <p><?php echo esc_html($action_result['message']); ?></p>
            </div>
        <?php endif; ?>

        <!-- SECTION 1: Sync Status Overview -->
        <div class="card" style="margin-bottom: 20px; padding: 20px;">
            <h2 style="margin-top: 0;">Sync Status Overview</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <!-- Health Indicator -->
                <div style="border: 2px solid <?php echo $health_color; ?>; padding: 15px; border-radius: 5px; text-align: center;">
                    <div style="font-size: 24px; font-weight: bold; color: <?php echo $health_color; ?>;">
                        ● <?php echo $health_text; ?>
                    </div>
                    <div style="font-size: 12px; margin-top: 5px;">Overall System Health</div>
                </div>

                <!-- Data Counts -->
                <div style="border: 1px solid #ccc; padding: 15px; border-radius: 5px;">
                    <div style="font-size: 18px; font-weight: bold;"><?php echo count($vehicles_data); ?></div>
                    <div style="font-size: 12px; color: #666;">Total Vehicles</div>
                </div>

                <div style="border: 1px solid #ccc; padding: 15px; border-radius: 5px;">
                    <div style="font-size: 18px; font-weight: bold;"><?php echo $user_count['total_users'] ?? 0; ?></div>
                    <div style="font-size: 12px; color: #666;">Total Users</div>
                </div>

                <div style="border: 1px solid #ccc; padding: 15px; border-radius: 5px;">
                    <div style="font-size: 18px; font-weight: bold;">
                        <?php echo ($reservation_count->publish ?? 0) + ($reservation_count->pending ?? 0) + ($reservation_count->draft ?? 0); ?>
                    </div>
                    <div style="font-size: 12px; color: #666;">Total Reservations</div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: Vehicles Sync -->
        <div class="card" style="margin-bottom: 20px; padding: 20px;">
            <h2 style="margin-top: 0;">Vehicles Synchronization</h2>
            <p>
                <strong>Total:</strong> <?php echo count($vehicles_data); ?> vehicles
                | <strong>UK:</strong> <?php echo $vehicles_by_country['UK']; ?>
                | <strong>TR:</strong> <?php echo $vehicles_by_country['TR']; ?>
            </p>
            <p>
                <strong>Last Sync:</strong>
                <?php
                $vehicle_sync_time = $health['vehicles']['last_sync'];
                if ($vehicle_sync_time) {
                    echo wp_date('Y-m-d H:i:s', $vehicle_sync_time) . ' (' . human_time_diff($vehicle_sync_time) . ' ago)';
                } else {
                    echo 'Never synced';
                }
                ?>
            </p>
            <form method="post" style="margin-top: 15px;">
                <?php wp_nonce_field('sync_dashboard_nonce'); ?>
                <input type="hidden" name="airlinel_sync_action" value="sync_vehicles">
                <?php submit_button('Sync Vehicles Now', 'primary', 'sync_vehicles_btn', false); ?>
            </form>
            <div style="margin-top: 15px; border-top: 1px solid #ddd; padding-top: 15px;">
                <h3 style="margin-top: 0;">Recent Vehicle Sync Events</h3>
                <table class="widefat" style="margin-bottom: 10px;">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Status</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $vehicle_events = array_filter($sync_log, function($event) {
                            return $event['data_type'] === 'vehicles';
                        });
                        foreach (array_slice($vehicle_events, 0, 5) as $event) {
                            $status_color = $event['status'] === 'success' ? '#28a745' : ($event['status'] === 'error' ? '#dc3545' : '#ffc107');
                            ?>
                            <tr>
                                <td><?php echo wp_date('Y-m-d H:i:s', $event['timestamp']); ?></td>
                                <td><span style="color: <?php echo $status_color; ?>; font-weight: bold;">
                                    <?php echo strtoupper($event['status']); ?></span></td>
                                <td><?php echo esc_html($event['message']); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECTION 3: Exchange Rates -->
        <div class="card" style="margin-bottom: 20px; padding: 20px;">
            <h2 style="margin-top: 0;">Exchange Rates Management</h2>
            <p>
                <strong>Base Currency:</strong> GBP (£1.00)
                <br>
                <strong>Last Updated:</strong>
                <?php
                if ($exchange_rates['last_updated']) {
                    echo wp_date('Y-m-d H:i:s', $exchange_rates['last_updated']) . ' (' . human_time_diff($exchange_rates['last_updated']) . ' ago)';
                } else {
                    echo 'Never updated';
                }
                ?>
            </p>

            <form method="post" style="margin-top: 20px;">
                <?php wp_nonce_field('sync_dashboard_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><label>GBP (Base)</label></th>
                        <td>
                            <input type="number" value="1.00" disabled style="background: #f0f0f0;">
                        </td>
                    </tr>
                    <tr>
                        <th><label>EUR to GBP</label></th>
                        <td>
                            <input type="number" step="0.01" name="rate_eur" value="<?php echo esc_attr($exchange_rates['rates']['EUR'] ?? 1.18); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><label>TRY to GBP</label></th>
                        <td>
                            <input type="number" step="0.01" name="rate_try" value="<?php echo esc_attr($exchange_rates['rates']['TRY'] ?? 42.50); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><label>USD to GBP</label></th>
                        <td>
                            <input type="number" step="0.01" name="rate_usd" value="<?php echo esc_attr($exchange_rates['rates']['USD'] ?? 1.27); ?>">
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="airlinel_sync_action" value="sync_exchange_rates">
                <?php submit_button('Update Exchange Rates', 'primary', 'sync_rates_btn', false); ?>
            </form>

            <div style="margin-top: 20px; border-top: 1px solid #ddd; padding-top: 15px;">
                <h3 style="margin-top: 0;">Recent Exchange Rate Changes</h3>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Status</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $rate_events = array_filter($sync_log, function($event) {
                            return $event['data_type'] === 'exchange_rates';
                        });
                        foreach (array_slice($rate_events, 0, 5) as $event) {
                            $status_color = $event['status'] === 'success' ? '#28a745' : ($event['status'] === 'error' ? '#dc3545' : '#ffc107');
                            ?>
                            <tr>
                                <td><?php echo wp_date('Y-m-d H:i:s', $event['timestamp']); ?></td>
                                <td><span style="color: <?php echo $status_color; ?>; font-weight: bold;">
                                    <?php echo strtoupper($event['status']); ?></span></td>
                                <td><?php echo esc_html($event['message']); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECTION 4: User Sync -->
        <div class="card" style="margin-bottom: 20px; padding: 20px;">
            <h2 style="margin-top: 0;">User Synchronization</h2>
            <p>
                <strong>Total Users:</strong> <?php echo $user_count['total_users'] ?? 0; ?>
            </p>
            <p>
                <strong>Last Sync:</strong>
                <?php
                $user_sync_time = $health['users']['last_sync'];
                if ($user_sync_time) {
                    echo wp_date('Y-m-d H:i:s', $user_sync_time) . ' (' . human_time_diff($user_sync_time) . ' ago)';
                } else {
                    echo 'Never synced';
                }
                ?>
            </p>
            <p style="font-size: 12px; color: #666;">
                Users are shared across all sites via the WordPress user table. User sync is managed through user management.
            </p>
        </div>

        <!-- SECTION 5: Reservation Tracking -->
        <div class="card" style="margin-bottom: 20px; padding: 20px;">
            <h2 style="margin-top: 0;">Reservation Tracking</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px;">
                <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
                    <div style="font-size: 16px; font-weight: bold;">
                        <?php echo ($reservation_count->publish ?? 0) + ($reservation_count->pending ?? 0) + ($reservation_count->draft ?? 0); ?>
                    </div>
                    <div style="font-size: 12px;">Total All-Time</div>
                </div>
                <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
                    <div style="font-size: 16px; font-weight: bold;"><?php echo $reservation_count->pending ?? 0; ?></div>
                    <div style="font-size: 12px;">Pending</div>
                </div>
                <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
                    <div style="font-size: 16px; font-weight: bold;"><?php echo $reservation_count->publish ?? 0; ?></div>
                    <div style="font-size: 12px;">Completed</div>
                </div>
                <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
                    <div style="font-size: 16px; font-weight: bold;"><?php echo $reservation_count->draft ?? 0; ?></div>
                    <div style="font-size: 12px;">Draft</div>
                </div>
            </div>
            <p style="font-size: 12px; color: #666;">
                All reservations are created on the main site and tracked with source_site metadata.
                <a href="<?php echo admin_url('edit.php?post_type=reservations'); ?>">View All Reservations</a>
            </p>
        </div>

        <!-- SECTION 6: Sync Log -->
        <div class="card" style="padding: 20px;">
            <h2 style="margin-top: 0;">Synchronization Log</h2>
            <p style="font-size: 12px; color: #666;">
                Showing last <?php echo min(50, count($sync_log)); ?> events
            </p>
            <table class="widefat">
                <thead>
                    <tr>
                        <th style="width: 200px;">Timestamp</th>
                        <th style="width: 150px;">Data Type</th>
                        <th style="width: 100px;">Status</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (empty($sync_log)) {
                        ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 20px;">No sync events logged yet</td>
                        </tr>
                        <?php
                    } else {
                        foreach ($sync_log as $event) {
                            $status_color = match($event['status']) {
                                'success' => '#28a745',
                                'error' => '#dc3545',
                                'warning' => '#ffc107',
                                default => '#6c757d',
                            };
                            ?>
                            <tr>
                                <td><?php echo wp_date('Y-m-d H:i:s', $event['timestamp']); ?></td>
                                <td><?php echo esc_html($event['data_type']); ?></td>
                                <td>
                                    <span style="color: <?php echo $status_color; ?>; font-weight: bold;">
                                        <?php echo strtoupper($event['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($event['message']); ?></td>
                            </tr>
                        <?php
                        }
                    }
                    ?>
                </tbody>
            </table>

            <div style="margin-top: 20px;">
                <form method="post">
                    <?php wp_nonce_field('sync_dashboard_nonce'); ?>
                    <input type="hidden" name="airlinel_sync_action" value="clear_log">
                    <?php submit_button('Clear Log', 'delete', 'clear_log_btn', false); ?>
                </form>
            </div>
        </div>
    </div>
    <?php
