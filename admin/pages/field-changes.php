<?php
if (!defined('ABSPATH')) exit;
if (!current_user_can('manage_options')) wp_die('Unauthorized');

require_once(plugin_dir_path(__FILE__) . '../../includes/class-analytics-dashboard.php');

$form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;

if ($form_id <= 0) {
    wp_die('Invalid form ID');
}

$field_changes = Airlinel_Analytics_Dashboard::get_form_field_changes($form_id);
?>

<div class="wrap">
    <h1>Form Field Changes - Booking #<?php echo intval($form_id); ?></h1>

    <h2>Field Change Timeline</h2>
    <table class="widefat">
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>Field Name</th>
                <th>Field Value</th>
                <th>IP Address</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($field_changes)) : ?>
            <tr>
                <td colspan="4">No field changes recorded</td>
            </tr>
            <?php else : foreach ($field_changes as $change) : ?>
            <tr>
                <td><?php echo esc_html($change->change_timestamp); ?></td>
                <td><strong><?php echo esc_html($change->field_name); ?></strong></td>
                <td><?php echo esc_html(substr($change->field_value, 0, 50)); ?></td>
                <td><?php echo esc_html($change->ip_address); ?></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <p style="margin-top: 20px;">
        <a href="<?php echo admin_url('admin.php?page=airlinel-analytics-forms'); ?>" class="button button-secondary">← Back to Forms</a>
    </p>
</div>
