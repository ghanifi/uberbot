<?php
if (!defined('ABSPATH')) exit;
if (!current_user_can('manage_options')) wp_die('Unauthorized');

require_once(plugin_dir_path(__FILE__) . '../../includes/class-analytics-dashboard.php');

$date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : date('Y-m-d', strtotime('-30 days'));
$date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : date('Y-m-d');
$website_filter = isset($_GET['website']) ? sanitize_text_field($_GET['website']) : '';
$language_filter = isset($_GET['language']) ? sanitize_text_field($_GET['language']) : '';

// Get available website IDs and languages
$website_ids = Airlinel_Analytics_Dashboard::get_website_ids();
$website_languages = Airlinel_Analytics_Dashboard::get_website_languages();

$stats = Airlinel_Analytics_Dashboard::get_summary_stats($date_from, $date_to, $website_filter, $language_filter);
$funnel = Airlinel_Analytics_Dashboard::get_form_funnel_stats($date_from, $date_to);
?>

<div class="wrap">
    <h1>Airlinel Analytics Dashboard</h1>

    <div class="airlinel-filters" style="background: #fff; padding: 15px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px;">
        <form method="get" action="">
            <input type="hidden" name="page" value="airlinel-analytics">

            <label for="date_from">From:</label>
            <input type="date" id="date_from" name="date_from" value="<?php echo esc_attr($date_from); ?>" style="padding: 8px; margin-right: 15px;">

            <label for="date_to">To:</label>
            <input type="date" id="date_to" name="date_to" value="<?php echo esc_attr($date_to); ?>" style="padding: 8px; margin-right: 15px;">

            <label for="website_filter">Filter by Website:</label>
            <select id="website_filter" name="website" style="padding: 8px; margin-right: 15px;">
                <option value="">All Websites</option>
                <?php foreach ($website_ids as $website_id): ?>
                    <option value="<?php echo esc_attr($website_id); ?>" <?php selected($website_filter, $website_id); ?>>
                        <?php echo esc_html($website_id); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="language_filter">Filter by Language:</label>
            <select id="language_filter" name="language" style="padding: 8px; margin-right: 15px;">
                <option value="">All Languages</option>
                <?php foreach ($website_languages as $lang): ?>
                    <option value="<?php echo esc_attr($lang); ?>" <?php selected($language_filter, $lang); ?>>
                        <?php echo esc_html($lang); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="submit" class="button button-primary" value="Filter">
        </form>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: #fff; border: 1px solid #ccc; border-radius: 4px; padding: 20px; text-align: center;">
            <h3 style="margin: 0 0 10px 0; color: #666;">Total Searches</h3>
            <p style="font-size: 36px; font-weight: bold; color: #0073aa; margin: 0;"><?php echo intval($stats['total_searches']); ?></p>
        </div>

        <div style="background: #fff; border: 1px solid #ccc; border-radius: 4px; padding: 20px; text-align: center;">
            <h3 style="margin: 0 0 10px 0; color: #666;">Total Sessions</h3>
            <p style="font-size: 36px; font-weight: bold; color: #0073aa; margin: 0;"><?php echo intval($stats['total_sessions']); ?></p>
        </div>

        <div style="background: #fff; border: 1px solid #ccc; border-radius: 4px; padding: 20px; text-align: center;">
            <h3 style="margin: 0 0 10px 0; color: #666;">Completed Bookings</h3>
            <p style="font-size: 36px; font-weight: bold; color: #0073aa; margin: 0;"><?php echo intval($stats['total_completions']); ?></p>
        </div>

        <div style="background: #fff; border: 1px solid #ccc; border-radius: 4px; padding: 20px; text-align: center;">
            <h3 style="margin: 0 0 10px 0; color: #666;">Sessions Completed</h3>
            <p style="font-size: 36px; font-weight: bold; color: #0073aa; margin: 0;"><?php echo intval($stats['completed_sessions']); ?></p>
        </div>

        <div style="background: #fff; border: 1px solid #ccc; border-radius: 4px; padding: 20px; text-align: center;">
            <h3 style="margin: 0 0 10px 0; color: #666;">Conversion Rate</h3>
            <p style="font-size: 36px; font-weight: bold; color: #0073aa; margin: 0;"><?php echo $stats['conversion_rate']; ?>%</p>
        </div>
    </div>

    <div style="background: #fff; padding: 20px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px;">
        <h2>Booking Form Funnel</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Stage</th>
                    <th>Count</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_forms = max(1, array_sum($funnel));
                $stages = array(
                    'vehicle_selection' => 'Vehicle Selection',
                    'customer_info' => 'Customer Info',
                    'booking_details' => 'Booking Details',
                    'completed' => 'Completed'
                );
                foreach ($stages as $key => $label):
                    $count = isset($funnel[$key]) ? intval($funnel[$key]) : 0;
                    $percentage = round(($count / $total_forms) * 100, 1);
                ?>
                <tr>
                    <td><?php echo esc_html($label); ?></td>
                    <td><?php echo $count; ?></td>
                    <td><?php echo $percentage; ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="background: #fff; padding: 20px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px;">
        <h2>Searches by Country</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Country</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats['by_country'] as $row) : ?>
                <tr>
                    <td><?php echo esc_html($row->country); ?></td>
                    <td><?php echo intval($row->count); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="background: #fff; padding: 20px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px;">
        <h2>Searches by Source</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Source</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats['by_source'] as $row) : ?>
                <tr>
                    <td><?php echo esc_html($row->source === 'regional_api' ? 'Regional Sites' : 'Main Site'); ?></td>
                    <td><?php echo intval($row->count); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p>
        <a href="<?php echo admin_url('admin.php?page=airlinel-analytics-search'); ?>" class="button button-secondary">View Detailed Search Analytics →</a>
    </p>
</div>
