<?php
if (!defined('ABSPATH')) exit;
if (!current_user_can('manage_options')) wp_die('Unauthorized');

require_once(plugin_dir_path(__FILE__) . '../../includes/class-analytics-dashboard.php');

$date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : date('Y-m-d', strtotime('-30 days'));
$date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : date('Y-m-d');
$country = isset($_GET['country']) ? sanitize_text_field($_GET['country']) : '';
$language = isset($_GET['language']) ? sanitize_text_field($_GET['language']) : '';
$source = isset($_GET['source']) ? sanitize_text_field($_GET['source']) : '';
$website_id = isset($_GET['website']) ? sanitize_text_field($_GET['website']) : '';
$session_id = isset($_GET['session_id']) ? sanitize_text_field($_GET['session_id']) : '';

// Get available website IDs
$website_ids = Airlinel_Analytics_Dashboard::get_website_ids();

$filters = array(
    'date_from' => $date_from,
    'date_to' => $date_to,
    'country' => $country,
    'language' => $language,
    'site_source' => $source,
    'website_id' => $website_id,
    'session_id' => $session_id,
);

$searches = Airlinel_Analytics_Dashboard::get_search_analytics($filters);
?>

<div class="wrap">
    <h1>Search Analytics</h1>

    <div class="airlinel-filters" style="background: #fff; padding: 15px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px;">
        <form method="get" action="">
            <input type="hidden" name="page" value="airlinel-analytics-search">

            <label>Date Range:</label>
            <input type="date" name="date_from" value="<?php echo esc_attr($date_from); ?>" style="padding: 8px; margin-right: 10px;">
            to
            <input type="date" name="date_to" value="<?php echo esc_attr($date_to); ?>" style="padding: 8px; margin-right: 15px;">

            <label>Country:</label>
            <select name="country" style="padding: 8px; margin-right: 15px;">
                <option value="">All</option>
                <option value="UK" <?php selected($country, 'UK'); ?>>UK</option>
                <option value="TR" <?php selected($country, 'TR'); ?>>Turkey</option>
            </select>

            <label>Language:</label>
            <select name="language" style="padding: 8px; margin-right: 15px;">
                <option value="">All</option>
                <option value="en" <?php selected($language, 'en'); ?>>English</option>
                <option value="tr" <?php selected($language, 'tr'); ?>>Turkish</option>
            </select>

            <label>Source:</label>
            <select name="source" style="padding: 8px; margin-right: 15px;">
                <option value="">All</option>
                <option value="regional_api" <?php selected($source, 'regional_api'); ?>>Regional API</option>
                <option value="main_site" <?php selected($source, 'main_site'); ?>>Main Site</option>
            </select>

            <label>Website:</label>
            <select name="website" style="padding: 8px; margin-right: 15px;">
                <option value="">All Websites</option>
                <?php foreach ($website_ids as $site_id): ?>
                    <option value="<?php echo esc_attr($site_id); ?>" <?php selected($website_id, $site_id); ?>>
                        <?php echo esc_html($site_id); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="submit" class="button button-primary" value="Filter">
        </form>
    </div>

    <h2>Search Records (<?php echo count($searches); ?> total)</h2>
    <table class="widefat">
        <thead>
            <tr>
                <th>ID</th>
                <th>Pickup</th>
                <th>Dropoff</th>
                <th>Km</th>
                <th>Country</th>
                <th>Language</th>
                <th>Source</th>
                <th>Session ID</th>
                <th>Website ID</th>
                <th>Website Language</th>
                <th>Rate</th>
                <th>Vehicles</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($searches)) : ?>
            <tr>
                <td colspan="13">No searches found</td>
            </tr>
            <?php else : foreach ($searches as $s) : ?>
            <tr>
                <td><?php echo intval($s->id); ?></td>
                <td><?php echo esc_html(substr($s->pickup, 0, 25)); ?></td>
                <td><?php echo esc_html(substr($s->dropoff, 0, 25)); ?></td>
                <td><?php echo number_format($s->distance_km, 1); ?></td>
                <td><?php echo esc_html($s->country); ?></td>
                <td><?php echo esc_html($s->language); ?></td>
                <td><?php echo esc_html($s->source); ?></td>
                <td>
                    <?php if (!empty($s->session_id)): ?>
                        <a href="<?php echo admin_url('admin.php?page=airlinel-analytics-search&session_id=' . urlencode($s->session_id)); ?>" title="View session">
                            <?php echo esc_html(substr($s->session_id, 0, 8)); ?>...
                        </a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td><?php echo esc_html($s->website_id ?: '-'); ?></td>
                <td><?php echo esc_html($s->website_language ?: '-'); ?></td>
                <td><?php echo number_format($s->exchange_rate, 4); ?></td>
                <td><?php echo intval($s->vehicle_count); ?></td>
                <td><?php echo esc_html(substr($s->timestamp, 0, 10)); ?></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
