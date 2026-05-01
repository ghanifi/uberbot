<?php
/**
 * Airlinel Analytics Dashboard
 * Main site analytics showing customer source tracking and regional site metrics
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check permissions (also verified in callback)
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to view analytics.');
}

// Load analytics manager
if (!class_exists('Airlinel_Analytics_Manager')) {
    require_once get_template_directory() . '/includes/class-analytics-manager.php';
}

    $analytics = new Airlinel_Analytics_Manager();
    $nonce = wp_create_nonce('airlinel_analytics_nonce');

    // Get filters from GET
    $filter_period = isset($_GET['filter_period']) ? sanitize_text_field($_GET['filter_period']) : 'month';
    $filter_start_date = isset($_GET['filter_start_date']) ? sanitize_text_field($_GET['filter_start_date']) : '';
    $filter_end_date = isset($_GET['filter_end_date']) ? sanitize_text_field($_GET['filter_end_date']) : '';
    $filter_site = isset($_GET['filter_site']) ? sanitize_text_field($_GET['filter_site']) : '';

    // Calculate date range
    $start_date = '';
    $end_date = '';

    switch ($filter_period) {
        case 'today':
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d');
            break;
        case 'week':
            $start_date = date('Y-m-d', strtotime('-7 days'));
            $end_date = date('Y-m-d');
            break;
        case 'month':
            $start_date = date('Y-m-d', strtotime('-30 days'));
            $end_date = date('Y-m-d');
            break;
        case 'thismonth':
            $start_date = date('Y-m-01');
            $end_date = date('Y-m-d');
            break;
        case 'year':
            $start_date = date('Y-01-01');
            $end_date = date('Y-m-d');
            break;
        case 'custom':
            $start_date = !empty($filter_start_date) ? $filter_start_date : date('Y-m-01');
            $end_date = !empty($filter_end_date) ? $filter_end_date : date('Y-m-d');
            break;
        default:
            $start_date = date('Y-m-d', strtotime('-30 days'));
            $end_date = date('Y-m-d');
    }

    // Get analytics data
    $summary = $analytics->get_analytics_summary($start_date, $end_date);
    $revenue_by_site = $analytics->get_revenue_by_site($filter_site, $start_date, $end_date);
    $bookings_by_language = $analytics->get_bookings_by_language(null, $start_date, $end_date);
    $trend_data = $analytics->get_trend_data(30, $filter_site);
    $regional_sites = $analytics->get_regional_sites();

    // Get detailed bookings for table
    $bookings_result = $analytics->get_bookings_by_site($filter_site, $start_date, $end_date, array('per_page' => 20, 'paged' => isset($_GET['paged']) ? intval($_GET['paged']) : 1));
    $bookings = $bookings_result['bookings'];
    $total_bookings_pages = $bookings_result['pages'];
    $current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;

    ?>

    <div class="wrap airlinel-analytics-wrap">
        <h1>Customer Analytics & Source Tracking</h1>

        <!-- Filters -->
        <div class="airlinel-filters" style="background: #f1f1f1; padding: 20px; margin: 20px 0; border-radius: 4px;">
            <form method="get" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
                <input type="hidden" name="page" value="airlinel-analytics">

                <!-- Time Period Filter -->
                <div style="min-width: 200px;">
                    <label for="filter_period" style="display: block; margin-bottom: 5px; font-weight: bold;">Time Period:</label>
                    <select name="filter_period" id="filter_period" onchange="this.form.submit();" style="width: 100%; padding: 8px;">
                        <option value="today" <?php selected($filter_period, 'today'); ?>>Today</option>
                        <option value="week" <?php selected($filter_period, 'week'); ?>>Last 7 Days</option>
                        <option value="month" <?php selected($filter_period, 'month'); ?>>Last 30 Days</option>
                        <option value="thismonth" <?php selected($filter_period, 'thismonth'); ?>>This Month</option>
                        <option value="year" <?php selected($filter_period, 'year'); ?>>This Year</option>
                        <option value="custom" <?php selected($filter_period, 'custom'); ?>>Custom Range</option>
                    </select>
                </div>

                <!-- Custom Date Range (shown when custom selected) -->
                <?php if ($filter_period === 'custom'): ?>
                    <div style="min-width: 180px;">
                        <label for="filter_start_date" style="display: block; margin-bottom: 5px; font-weight: bold;">From:</label>
                        <input type="date" name="filter_start_date" id="filter_start_date" value="<?php echo esc_attr($filter_start_date); ?>" style="width: 100%; padding: 8px;">
                    </div>

                    <div style="min-width: 180px;">
                        <label for="filter_end_date" style="display: block; margin-bottom: 5px; font-weight: bold;">To:</label>
                        <input type="date" name="filter_end_date" id="filter_end_date" value="<?php echo esc_attr($filter_end_date); ?>" style="width: 100%; padding: 8px;">
                    </div>
                <?php endif; ?>

                <!-- Regional Site Filter -->
                <div style="min-width: 200px;">
                    <label for="filter_site" style="display: block; margin-bottom: 5px; font-weight: bold;">Regional Site:</label>
                    <select name="filter_site" id="filter_site" onchange="this.form.submit();" style="width: 100%; padding: 8px;">
                        <option value="">All Sites</option>
                        <?php foreach ($regional_sites as $site): ?>
                            <option value="<?php echo esc_attr($site); ?>" <?php selected($filter_site, $site); ?>>
                                <?php echo esc_html(ucfirst($site)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="button button-primary" style="padding: 8px 20px; height: auto;">Apply Filters</button>
            </form>
        </div>

        <!-- Overview Cards -->
        <div class="airlinel-overview-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 30px 0;">
            <!-- Total Bookings Card -->
            <div style="background: white; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="font-size: 12px; color: #666; text-transform: uppercase; margin-bottom: 10px;">Total Bookings</div>
                <div style="font-size: 32px; font-weight: bold; color: #2c3e50; margin-bottom: 5px;"><?php echo intval($summary['total_bookings']); ?></div>
                <div style="font-size: 12px; color: #999;">Period: <?php echo esc_html($start_date); ?> to <?php echo esc_html($end_date); ?></div>
            </div>

            <!-- Total Revenue Card -->
            <div style="background: white; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="font-size: 12px; color: #666; text-transform: uppercase; margin-bottom: 10px;">Total Revenue (GBP)</div>
                <div style="font-size: 32px; font-weight: bold; color: #27ae60; margin-bottom: 5px;">£<?php echo number_format($summary['total_revenue'], 2); ?></div>
                <div style="font-size: 12px; color: #999;">All currencies converted</div>
            </div>

            <!-- Average Value Card -->
            <div style="background: white; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="font-size: 12px; color: #666; text-transform: uppercase; margin-bottom: 10px;">Average Booking Value</div>
                <div style="font-size: 32px; font-weight: bold; color: #3498db; margin-bottom: 5px;">£<?php echo number_format($summary['avg_value'], 2); ?></div>
                <div style="font-size: 12px; color: #999;">Per booking</div>
            </div>

            <!-- Top Source Site Card -->
            <div style="background: white; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="font-size: 12px; color: #666; text-transform: uppercase; margin-bottom: 10px;">Top Source Site</div>
                <div style="font-size: 32px; font-weight: bold; color: #e74c3c; margin-bottom: 5px;">
                    <?php echo $summary['top_site'] ? esc_html(ucfirst($summary['top_site'])) : 'N/A'; ?>
                </div>
                <div style="font-size: 12px; color: #999;">
                    <?php
                    if ($summary['top_site'] && isset($summary['sites_summary'][$summary['top_site']])) {
                        echo intval($summary['sites_summary'][$summary['top_site']]['count']) . ' bookings';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Booking Funnel Analytics -->
        <?php
        // Load booking analytics tracker
        require_once get_template_directory() . '/includes/class-booking-analytics-tracker.php';
        $tracker = new Airlinel_Booking_Analytics_Tracker();

        // Ensure table exists before querying
        $tracker->create_table();

        $funnel = $tracker->get_funnel_stats($start_date, $end_date);
        ?>

        <div style="background: white; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin: 30px 0;">
            <h2>Booking Funnel Analytics</h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0;">
                <!-- Total Searches Card -->
                <div style="background: #f0f9ff; padding: 15px; border-radius: 6px; border-left: 4px solid #0073aa;">
                    <strong><?php _e('Total Searches', 'airlinel-theme'); ?></strong>
                    <div style="font-size: 24px; color: #0073aa; margin-top: 5px; font-weight: bold;">
                        <?php echo number_format($funnel['total_searches']); ?>
                    </div>
                </div>

                <!-- Vehicle Selected Card -->
                <div style="background: #fef3c7; padding: 15px; border-radius: 6px; border-left: 4px solid #f59e0b;">
                    <strong><?php _e('Vehicle Selected', 'airlinel-theme'); ?></strong>
                    <div style="font-size: 24px; color: #f59e0b; margin-top: 5px; font-weight: bold;">
                        <?php echo number_format($funnel['vehicle_selected']); ?>
                    </div>
                    <?php if ($funnel['total_searches'] > 0): ?>
                        <div style="font-size: 12px; color: #666; margin-top: 5px;">
                            <?php printf('%.1f%% of searches', ($funnel['vehicle_selected'] / $funnel['total_searches']) * 100); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Form Filled Card -->
                <div style="background: #dcfce7; padding: 15px; border-radius: 6px; border-left: 4px solid #10b981;">
                    <strong><?php _e('Form Filled', 'airlinel-theme'); ?></strong>
                    <div style="font-size: 24px; color: #10b981; margin-top: 5px; font-weight: bold;">
                        <?php echo number_format($funnel['form_filled']); ?>
                    </div>
                    <?php if ($funnel['total_searches'] > 0): ?>
                        <div style="font-size: 12px; color: #666; margin-top: 5px;">
                            <?php printf('%.1f%% of searches', ($funnel['form_filled'] / $funnel['total_searches']) * 100); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Paid Bookings Card -->
                <div style="background: #f3e8ff; padding: 15px; border-radius: 6px; border-left: 4px solid #8b5cf6;">
                    <strong><?php _e('Paid Bookings', 'airlinel-theme'); ?></strong>
                    <div style="font-size: 24px; color: #8b5cf6; margin-top: 5px; font-weight: bold;">
                        <?php echo number_format($funnel['payment_complete']); ?>
                    </div>
                    <div style="font-size: 12px; color: #666; margin-top: 5px;">
                        <?php printf(__('Conversion: %s%%', 'airlinel-theme'), $funnel['conversion_rate']); ?>
                    </div>
                </div>
            </div>

            <!-- Funnel Visualization -->
            <div style="background: #f5f5f5; padding: 20px; border-radius: 6px; margin-top: 20px;">
                <h3 style="margin-top: 0; margin-bottom: 20px;">Funnel Drop-off Analysis</h3>

                <div style="display: flex; align-items: center; margin-bottom: 15px;">
                    <div style="flex: 1;">
                        <div style="height: 40px; background: #0073aa; border-radius: 4px; display: flex; align-items: center; padding: 0 15px; color: white; font-weight: bold;">
                            100% - Searches (<?php echo $funnel['total_searches']; ?>)
                        </div>
                    </div>
                </div>

                <?php if ($funnel['total_searches'] > 0): ?>
                    <div style="display: flex; align-items: center; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <div style="height: 40px; background: #f59e0b; border-radius: 4px; display: flex; align-items: center; padding: 0 15px; color: white; font-weight: bold; width: <?php echo ($funnel['vehicle_selected'] / $funnel['total_searches']) * 100; ?>%;">
                                <?php printf('%.1f%% - Vehicle Selected', ($funnel['vehicle_selected'] / $funnel['total_searches']) * 100); ?>
                            </div>
                        </div>
                        <div style="margin-left: 20px; color: #999; min-width: 50px;">
                            (<?php echo $funnel['vehicle_selected']; ?>)
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <div style="height: 40px; background: #10b981; border-radius: 4px; display: flex; align-items: center; padding: 0 15px; color: white; font-weight: bold; width: <?php echo ($funnel['form_filled'] / $funnel['total_searches']) * 100; ?>%;">
                                <?php printf('%.1f%% - Form Filled', ($funnel['form_filled'] / $funnel['total_searches']) * 100); ?>
                            </div>
                        </div>
                        <div style="margin-left: 20px; color: #999; min-width: 50px;">
                            (<?php echo $funnel['form_filled']; ?>)
                        </div>
                    </div>

                    <div style="display: flex; align-items: center;">
                        <div style="flex: 1;">
                            <div style="height: 40px; background: #8b5cf6; border-radius: 4px; display: flex; align-items: center; padding: 0 15px; color: white; font-weight: bold; width: <?php echo ($funnel['payment_complete'] / $funnel['total_searches']) * 100; ?>%;">
                                <?php printf('%.1f%% - Paid Bookings', ($funnel['payment_complete'] / $funnel['total_searches']) * 100); ?>
                            </div>
                        </div>
                        <div style="margin-left: 20px; color: #999; min-width: 50px;">
                            (<?php echo $funnel['payment_complete']; ?>)
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Charts Section -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px; margin: 30px 0;">
            <!-- Bookings by Regional Site -->
            <div style="background: white; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0;">Bookings by Regional Site</h3>
                <canvas id="chart_bookings_by_site" style="max-height: 300px;"></canvas>
            </div>

            <!-- Revenue by Regional Site -->
            <div style="background: white; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0;">Revenue by Regional Site (GBP)</h3>
                <canvas id="chart_revenue_by_site" style="max-height: 300px;"></canvas>
            </div>

            <!-- Bookings by Language -->
            <div style="background: white; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0;">Bookings by Language</h3>
                <canvas id="chart_bookings_by_language" style="max-height: 300px;"></canvas>
            </div>

            <!-- Daily Trend -->
            <div style="background: white; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0;">Daily Booking Trend (Last 30 Days)</h3>
                <canvas id="chart_trend" style="max-height: 300px;"></canvas>
            </div>
        </div>

        <!-- Regional Site Breakdown Table -->
        <div style="background: white; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin: 30px 0;">
            <h2>Regional Site Performance</h2>
            <table class="widefat striped" style="margin-top: 15px;">
                <thead>
                    <tr>
                        <th style="width: 20%;">Site ID</th>
                        <th style="width: 15%;">Bookings</th>
                        <th style="width: 20%;">Revenue (GBP)</th>
                        <th style="width: 15%;">Avg Value</th>
                        <th style="width: 20%;">% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_revenue_all = array_sum(array_column($revenue_by_site, 'revenue'));

                    foreach ($revenue_by_site as $site_data):
                        $percentage = $total_revenue_all > 0 ? ($site_data['revenue'] / $total_revenue_all) * 100 : 0;
                    ?>
                        <tr>
                            <td style="font-weight: bold;"><?php echo esc_html(ucfirst($site_data['site_id'])); ?></td>
                            <td><?php echo intval($site_data['count']); ?></td>
                            <td>£<?php echo number_format($site_data['revenue'], 2); ?></td>
                            <td>£<?php echo number_format($site_data['avg_value'], 2); ?></td>
                            <td><?php echo number_format($percentage, 1); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Language Breakdown Table -->
        <div style="background: white; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin: 30px 0;">
            <h2>Bookings by Language</h2>
            <table class="widefat striped" style="margin-top: 15px;">
                <thead>
                    <tr>
                        <th style="width: 20%;">Language</th>
                        <th style="width: 20%;">Bookings</th>
                        <th style="width: 30%;">Revenue (GBP)</th>
                        <th style="width: 20%;">% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_bookings_all = array_sum(array_column($bookings_by_language, 'bookings'));
                    $total_revenue_language = array_sum(array_column($bookings_by_language, 'revenue'));

                    // Sort by bookings descending
                    usort($bookings_by_language, function($a, $b) {
                        return $b['bookings'] - $a['bookings'];
                    });

                    foreach ($bookings_by_language as $lang_data):
                        $percentage = $total_bookings_all > 0 ? ($lang_data['bookings'] / $total_bookings_all) * 100 : 0;
                    ?>
                        <tr>
                            <td style="font-weight: bold;"><?php echo esc_html(strtoupper($lang_data['language'])); ?></td>
                            <td><?php echo intval($lang_data['bookings']); ?></td>
                            <td>£<?php echo number_format($lang_data['revenue'], 2); ?></td>
                            <td><?php echo number_format($percentage, 1); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Detailed Bookings Table -->
        <div style="background: white; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin: 30px 0;">
            <h2 style="display: flex; justify-content: space-between; align-items: center;">
                Recent Bookings
                <button type="button" class="button button-secondary" id="btn_export_csv" style="margin: 0;">Export to CSV</button>
            </h2>

            <table class="widefat striped" style="margin-top: 15px;">
                <thead>
                    <tr>
                        <th style="width: 8%;">Booking ID</th>
                        <th style="width: 15%;">Customer</th>
                        <th style="width: 12%;">Site</th>
                        <th style="width: 8%;">Language</th>
                        <th style="width: 15%;">Date</th>
                        <th style="width: 12%;">Amount</th>
                        <th style="width: 12%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bookings)): ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td style="font-weight: bold;">
                                    <a href="<?php echo admin_url('admin.php?page=airlinel-reservations'); ?>#booking-<?php echo intval($booking['id']); ?>">
                                        #<?php echo intval($booking['id']); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html($booking['customer_name']); ?></td>
                                <td><?php echo esc_html(ucfirst($booking['source_site'] ?: 'main')); ?></td>
                                <td><?php echo esc_html(strtoupper($booking['source_language'] ?: 'EN')); ?></td>
                                <td><?php echo esc_html(date('Y-m-d H:i', strtotime($booking['post_date']))); ?></td>
                                <td><?php echo esc_html($booking['currency']); ?> <?php echo number_format($booking['total_price'], 2); ?></td>
                                <td>
                                    <span style="padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold;
                                        <?php
                                        if ($booking['status'] === 'completed') {
                                            echo 'background: #d4edda; color: #155724;';
                                        } elseif ($booking['status'] === 'pending') {
                                            echo 'background: #fff3cd; color: #856404;';
                                        } elseif ($booking['status'] === 'cancelled') {
                                            echo 'background: #f8d7da; color: #721c24;';
                                        }
                                        ?>
                                    ">
                                        <?php echo esc_html(ucfirst($booking['status'])); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px;">No bookings found for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total_bookings_pages > 1): ?>
                <div style="margin-top: 20px; text-align: center;">
                    <?php
                    $pagination_args = array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => __('&laquo;'),
                        'next_text' => __('&raquo;'),
                        'total' => $total_bookings_pages,
                        'current' => $current_page,
                    );
                    echo paginate_links($pagination_args);
                    ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Hidden nonce for AJAX -->
        <input type="hidden" id="airlinel_nonce" value="<?php echo esc_attr($nonce); ?>">
    </div>

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

    <!-- Analytics Charts Script -->
    <script>
    jQuery(document).ready(function($) {
        const chartColors = ['#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c', '#34495e', '#e67e22'];

        // Prepare data from PHP
        const siteData = <?php echo json_encode($revenue_by_site); ?>;
        const languageData = <?php echo json_encode($bookings_by_language); ?>;
        const trendData = <?php echo json_encode($trend_data); ?>;

        // Chart 1: Bookings by Regional Site
        const siteLabels = siteData.map(site => site.site_id.charAt(0).toUpperCase() + site.site_id.slice(1));
        const siteCounts = siteData.map(site => site.count);

        new Chart(document.getElementById('chart_bookings_by_site'), {
            type: 'doughnut',
            data: {
                labels: siteLabels,
                datasets: [{
                    data: siteCounts,
                    backgroundColor: chartColors.slice(0, siteLabels.length),
                    borderColor: '#fff',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Chart 2: Revenue by Regional Site (Bar)
        new Chart(document.getElementById('chart_revenue_by_site'), {
            type: 'bar',
            data: {
                labels: siteLabels,
                datasets: [{
                    label: 'Revenue (GBP)',
                    data: siteData.map(site => site.revenue),
                    backgroundColor: chartColors[0],
                    borderColor: chartColors[0],
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '£' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Chart 3: Bookings by Language (Pie)
        const langLabels = languageData.map(lang => lang.language.toUpperCase());
        const langCounts = languageData.map(lang => lang.bookings);

        new Chart(document.getElementById('chart_bookings_by_language'), {
            type: 'pie',
            data: {
                labels: langLabels,
                datasets: [{
                    data: langCounts,
                    backgroundColor: chartColors,
                    borderColor: '#fff',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Chart 4: Daily Trend (Line)
        const dates = Object.keys(trendData);
        const counts = Object.values(trendData);

        new Chart(document.getElementById('chart_trend'), {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Bookings',
                    data: counts,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#3498db',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // CSV Export
        $('#btn_export_csv').on('click', function() {
            const form_data = new FormData();
            form_data.append('action', 'airlinel_export_analytics_csv');
            form_data.append('nonce', $('#airlinel_nonce').val());
            form_data.append('site_id', '<?php echo esc_js($filter_site); ?>');
            form_data.append('start_date', '<?php echo esc_js($start_date); ?>');
            form_data.append('end_date', '<?php echo esc_js($end_date); ?>');

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: form_data,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        // Create blob and download
                        const blob = new Blob([response.data.csv], { type: 'text/csv' });
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'analytics_export_' + new Date().getTime() + '.csv';
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        a.remove();
                    } else {
                        alert('Export failed: ' + response.data.error);
                    }
                },
                error: function() {
                    alert('Error exporting data');
                }
            });
        });
    });
    </script>

    <?php
