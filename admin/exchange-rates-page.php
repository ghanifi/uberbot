<?php
/**
 * Airlinel Exchange Rates Management Page
 * Manage and update currency exchange rates
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check permissions (also verified in callback)
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to manage exchange rates.');
}

$sync_mgr = new Airlinel_Data_Sync_Manager();
    $exchange_rates = $sync_mgr->get_exchange_rates();
    $current_rates = $exchange_rates['rates'];

    // Handle form submission
    $action_result = null;
    if (isset($_POST['update_exchange_rates'])) {
        check_admin_referer('exchange_rates_nonce');

        $rates = array(
            'GBP' => 1.00,
            'EUR' => floatval($_POST['rate_eur'] ?? 1.18),
            'TRY' => floatval($_POST['rate_try'] ?? 42.50),
            'USD' => floatval($_POST['rate_usd'] ?? 1.27),
        );

        // Validate all rates are positive numbers
        $valid = true;
        foreach ($rates as $currency => $rate) {
            if ($rate <= 0) {
                $action_result = array(
                    'success' => false,
                    'message' => sprintf('Invalid rate for %s. Must be greater than 0.', $currency),
                );
                $valid = false;
                break;
            }
        }

        if ($valid) {
            if ($sync_mgr->sync_exchange_rates($rates)) {
                $action_result = array(
                    'success' => true,
                    'message' => 'Exchange rates updated successfully',
                );
                // Refresh rates
                $exchange_rates = $sync_mgr->get_exchange_rates();
                $current_rates = $exchange_rates['rates'];
            } else {
                $action_result = array(
                    'success' => false,
                    'message' => 'Failed to update exchange rates',
                );
            }
        }
    }

    // Get sync log for this data type
    $sync_log = $sync_mgr->get_sync_log(100);
    $rate_events = array_filter($sync_log, function($event) {
        return $event['data_type'] === 'exchange_rates';
    });

    ?>
    <div class="wrap">
        <h1>Exchange Rates Management</h1>

        <?php if ($action_result): ?>
            <div class="notice notice-<?php echo $action_result['success'] ? 'success' : 'error'; ?>">
                <p><?php echo esc_html($action_result['message']); ?></p>
            </div>
        <?php endif; ?>

        <div class="card" style="padding: 20px; margin-bottom: 20px;">
            <h2 style="margin-top: 0;">Current Exchange Rates</h2>
            <p style="color: #666; margin-bottom: 20px;">
                Base Currency: <strong>GBP (£1.00)</strong>
                <br>
                Last Updated: <strong>
                    <?php
                    if ($exchange_rates['last_updated']) {
                        echo wp_date('Y-m-d H:i:s', $exchange_rates['last_updated']) . ' (' . human_time_diff($exchange_rates['last_updated']) . ' ago)';
                    } else {
                        echo 'Never updated';
                    }
                    ?>
                </strong>
            </p>

            <form method="post">
                <?php wp_nonce_field('exchange_rates_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th style="width: 200px;"><label>GBP (Base Currency)</label></th>
                        <td>
                            <input type="number" step="0.01" value="1.00" disabled style="background: #f0f0f0; width: 200px;">
                            <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">
                                Base reference currency - cannot be changed
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="rate_eur">EUR (Euro)</label></th>
                        <td>
                            <input type="number" id="rate_eur" name="rate_eur" step="0.01" min="0.01"
                                   value="<?php echo esc_attr($current_rates['EUR'] ?? 1.18); ?>" style="width: 200px;">
                            <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">
                                How many GBP equals 1 EUR
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="rate_try">TRY (Turkish Lira)</label></th>
                        <td>
                            <input type="number" id="rate_try" name="rate_try" step="0.01" min="0.01"
                                   value="<?php echo esc_attr($current_rates['TRY'] ?? 42.50); ?>" style="width: 200px;">
                            <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">
                                How many GBP equals 1 TRY
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="rate_usd">USD (US Dollar)</label></th>
                        <td>
                            <input type="number" id="rate_usd" name="rate_usd" step="0.01" min="0.01"
                                   value="<?php echo esc_attr($current_rates['USD'] ?? 1.27); ?>" style="width: 200px;">
                            <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">
                                How many GBP equals 1 USD
                            </p>
                        </td>
                    </tr>
                </table>

                <input type="hidden" name="update_exchange_rates" value="1">
                <?php submit_button('Update Exchange Rates', 'primary', 'submit_rates', true); ?>
            </form>
        </div>

        <div class="card" style="padding: 20px; margin-bottom: 20px;">
            <h2 style="margin-top: 0;">Exchange Rate History</h2>
            <p style="color: #666; margin-bottom: 20px;">
                Showing the last <?php echo count($rate_events); ?> updates to exchange rates
            </p>

            <?php if (empty($rate_events)): ?>
                <p style="text-align: center; padding: 20px; color: #666;">
                    No exchange rate history available yet
                </p>
            <?php else: ?>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th style="width: 200px;">Update Time</th>
                            <th style="width: 100px;">Status</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rate_events as $event): ?>
                            <tr>
                                <td><?php echo wp_date('Y-m-d H:i:s', $event['timestamp']); ?></td>
                                <td>
                                    <span style="<?php
                                        echo $event['status'] === 'success' ? 'color: #28a745;' : ($event['status'] === 'error' ? 'color: #dc3545;' : 'color: #ffc107;');
                                    ?> font-weight: bold;">
                                        <?php echo strtoupper($event['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($event['message']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="card" style="padding: 20px;">
            <h2 style="margin-top: 0;">Information</h2>
            <p>
                Exchange rates are used to convert prices between currencies (GBP, EUR, TRY, USD) across all regional sites.
                Update the rates whenever they need to be adjusted to reflect current market conditions.
            </p>
            <p>
                <strong>Note:</strong> These rates are cached on regional sites with a 5-minute TTL (time-to-live).
                Updated rates will be synchronized to regional sites on their next cache refresh.
            </p>
            <p style="color: #666;">
                For future integration with live exchange rate APIs, see the Airlinel_Data_Sync_Manager class
                documentation or contact your administrator.
            </p>
        </div>
    </div>
    <?php
