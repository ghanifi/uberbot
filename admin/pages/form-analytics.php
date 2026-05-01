<?php
if (!defined('ABSPATH')) exit;
if (!current_user_can('manage_options')) wp_die('Unauthorized');

require_once(plugin_dir_path(__FILE__) . '../../includes/class-analytics-dashboard.php');

$date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : date('Y-m-d', strtotime('-30 days'));
$date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : date('Y-m-d');
$stage = isset($_GET['stage']) ? sanitize_text_field($_GET['stage']) : '';
$country = isset($_GET['country']) ? sanitize_text_field($_GET['country']) : '';
$website_id = isset($_GET['website']) ? sanitize_text_field($_GET['website']) : '';
$session_id = isset($_GET['session_id']) ? sanitize_text_field($_GET['session_id']) : '';

// Get available website IDs
$website_ids = Airlinel_Analytics_Dashboard::get_website_ids();

$filters = array(
    'date_from' => $date_from,
    'date_to' => $date_to,
    'form_stage' => $stage,
    'country' => $country,
    'website_id' => $website_id,
    'session_id' => $session_id,
);

$forms = Airlinel_Analytics_Dashboard::get_form_analytics($filters);
?>

<div class="wrap">
    <h1>Booking Forms Analytics</h1>

    <div class="airlinel-filters" style="background: #fff; padding: 15px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px;">
        <form method="get" action="">
            <input type="hidden" name="page" value="airlinel-analytics-forms">

            <label>Date Range:</label>
            <input type="date" name="date_from" value="<?php echo esc_attr($date_from); ?>" style="padding: 8px; margin-right: 10px;">
            to
            <input type="date" name="date_to" value="<?php echo esc_attr($date_to); ?>" style="padding: 8px; margin-right: 15px;">

            <label>Stage:</label>
            <select name="stage" style="padding: 8px; margin-right: 15px;">
                <option value="">All</option>
                <option value="vehicle_selection" <?php selected($stage, 'vehicle_selection'); ?>>Vehicle Selection</option>
                <option value="customer_info" <?php selected($stage, 'customer_info'); ?>>Customer Info</option>
                <option value="booking_details" <?php selected($stage, 'booking_details'); ?>>Booking Details</option>
                <option value="completed" <?php selected($stage, 'completed'); ?>>Completed</option>
            </select>

            <label>Country:</label>
            <select name="country" style="padding: 8px; margin-right: 15px;">
                <option value="">All</option>
                <option value="UK" <?php selected($country, 'UK'); ?>>UK</option>
                <option value="TR" <?php selected($country, 'TR'); ?>>Turkey</option>
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

    <h2>Booking Forms (<?php echo count($forms); ?> total)</h2>
    <table class="widefat">
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Pickup</th>
                <th>Dropoff</th>
                <th>Vehicle</th>
                <th>Price</th>
                <th>Stage</th>
                <th>Session ID</th>
                <th>Website ID</th>
                <th>Website Language</th>
                <th>Country</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($forms)) : ?>
            <tr>
                <td colspan="14">No forms found</td>
            </tr>
            <?php else : foreach ($forms as $form) : ?>
            <tr>
                <td><?php echo intval($form->id); ?></td>
                <td><?php echo esc_html($form->customer_name); ?></td>
                <td><?php echo esc_html($form->customer_email); ?></td>
                <td><?php echo esc_html(substr($form->pickup, 0, 20)); ?></td>
                <td><?php echo esc_html(substr($form->dropoff, 0, 20)); ?></td>
                <td><?php echo esc_html($form->vehicle_name); ?></td>
                <td><?php echo esc_html($form->vehicle_price); ?></td>
                <td><span style="display: inline-block; padding: 5px 10px; background: #0073aa; color: white; border-radius: 3px; font-size: 11px; font-weight: bold;"><?php echo esc_html($form->form_stage); ?></span></td>
                <td>
                    <?php if (!empty($form->session_id)): ?>
                        <a href="<?php echo admin_url('admin.php?page=airlinel-analytics-search&session_id=' . urlencode($form->session_id)); ?>" title="View session details">
                            <?php echo esc_html(substr($form->session_id, 0, 8)); ?>...
                        </a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td><?php echo esc_html($form->website_id ?: '-'); ?></td>
                <td><?php echo esc_html($form->website_language ?: '-'); ?></td>
                <td><?php echo esc_html($form->country); ?></td>
                <td><?php echo esc_html(substr($form->created_at, 0, 10)); ?></td>
                <td><a href="<?php echo admin_url('admin.php?page=airlinel-analytics-fields&form_id=' . intval($form->id)); ?>" class="button button-small">Details</a></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
