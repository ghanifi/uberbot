<?php
/**
 * Database Migrations Admin Page
 *
 * Display and manage database migrations
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}

require_once get_template_directory() . '/includes/class-database-migrations.php';

$mgr = new Airlinel_Database_Migrations();
$pending = $mgr->get_pending_migrations();
$completed = $mgr->get_completed_migrations();

// Handle form submission
$message = '';
$message_type = '';

if (isset($_POST['airlinel_run_migrations']) && check_admin_referer('airlinel_migrations_nonce')) {
    $results = $mgr->run_all_pending();

    if (!empty($results['success'])) {
        $message = count($results['success']) . ' migration(s) completed successfully.';
        $message_type = 'success';
    }

    if (!empty($results['errors'])) {
        if (!empty($message)) {
            $message .= ' ';
        }
        $message .= count($results['errors']) . ' error(s) occurred.';
        $message_type = 'error';
    }

    // Refresh data
    $pending = $mgr->get_pending_migrations();
    $completed = $mgr->get_completed_migrations();
}
?>

<div class="wrap airlinel-migrations">
    <h1><?php _e('Database Migrations', 'airlinel-theme'); ?></h1>

    <?php if (!empty($message)) : ?>
    <div class="notice notice-<?php echo esc_attr($message_type); ?> is-dismissible">
        <p><?php echo esc_html($message); ?></p>
    </div>
    <?php endif; ?>

    <?php if (!empty($pending)) : ?>
    <div class="migration-section pending-migrations">
        <h2><?php printf(__('Pending Migrations (%d)', 'airlinel-theme'), count($pending)); ?></h2>

        <form method="post">
            <?php wp_nonce_field('airlinel_migrations_nonce'); ?>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php _e('Migration File', 'airlinel-theme'); ?></th>
                        <th><?php _e('Description', 'airlinel-theme'); ?></th>
                        <th><?php _e('Status', 'airlinel-theme'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending as $file => $data) : ?>
                    <tr>
                        <td><code><?php echo esc_html($file); ?></code></td>
                        <td><?php echo esc_html($data['name']); ?></td>
                        <td><span class="status-badge pending"><?php _e('Pending', 'airlinel-theme'); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button type="submit" name="airlinel_run_migrations" class="button button-primary button-large" style="margin-top: 15px;">
                <?php _e('Run All Pending Migrations', 'airlinel-theme'); ?>
            </button>
        </form>
    </div>
    <?php endif; ?>

    <?php if (!empty($completed)) : ?>
    <div class="migration-section completed-migrations">
        <h2><?php printf(__('Completed Migrations (%d)', 'airlinel-theme'), count($completed)); ?></h2>

        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php _e('Migration File', 'airlinel-theme'); ?></th>
                    <th><?php _e('Description', 'airlinel-theme'); ?></th>
                    <th><?php _e('Completed At', 'airlinel-theme'); ?></th>
                    <th><?php _e('Status', 'airlinel-theme'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($completed as $file => $data) : ?>
                <tr>
                    <td><code><?php echo esc_html($file); ?></code></td>
                    <td><?php echo esc_html($data['name']); ?></td>
                    <td><?php echo esc_html(wp_date(__('Y-m-d H:i:s', 'airlinel-theme'), strtotime($data['completed_at']))); ?></td>
                    <td><span class="status-badge completed">✓ <?php _e('Completed', 'airlinel-theme'); ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if (empty($pending) && empty($completed)) : ?>
    <div class="notice notice-info">
        <p><?php printf(__('No migrations found. Create migration files in %s', 'airlinel-theme'), '<code>database/migrations/</code>'); ?></p>
    </div>
    <?php endif; ?>
</div>

<style>
.airlinel-migrations {
    padding: 20px 0;
}

.migration-section {
    background: #fff;
    padding: 20px;
    margin-bottom: 30px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.migration-section h2 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 18px;
    color: #333;
}

.status-badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: bold;
    color: white;
    text-transform: uppercase;
}

.status-badge.pending {
    background: #ffa500;
}

.status-badge.completed {
    background: #28a745;
}

.widefat {
    margin-bottom: 15px;
}

.widefat code {
    background: #f5f5f5;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
}

code {
    background: #f5f5f5;
    padding: 2px 6px;
    border-radius: 3px;
}

.button-primary {
    background-color: #0073aa;
    border-color: #006799;
    color: #fff;
}

.button-primary:hover {
    background-color: #005a87;
    border-color: #004a6f;
}
</style>
