<?php
/**
 * Airlinel Admin Dashboard
 * Main dashboard page for unified admin menu
 */

if (!current_user_can('manage_options')) {
    wp_die(__('You do not have permission to access this page.', 'airlinel-theme'));
}

// Get sync stats if available
$sync_stats = array();
if (class_exists('Airlinel_Data_Sync_Manager')) {
    $sync_mgr = new Airlinel_Data_Sync_Manager();
    $sync_stats = $sync_mgr->get_sync_stats();
}

// Get exchange rate info
$exchange_rates = array();
if (class_exists('Airlinel_Exchange_Rate_Manager')) {
    $rate_mgr = new Airlinel_Exchange_Rate_Manager();
    $exchange_rates = $rate_mgr->get_rates();
}

// Get agency count
$agencies = get_posts(array(
    'post_type' => 'agencies',
    'posts_per_page' => -1,
));
$agency_count = count($agencies);

// Get bookings count (if post type exists)
$booking_count = 0;
if (post_type_exists('bookings')) {
    $bookings = get_posts(array(
        'post_type' => 'bookings',
        'posts_per_page' => -1,
    ));
    $booking_count = count($bookings);
}

?>
<div class="wrap airlinel-admin-dashboard">
    <h1><?php echo esc_html(__('Airlinel Dashboard', 'airlinel-theme')); ?></h1>

    <div class="airlinel-dashboard-header">
        <p><?php echo esc_html(__('Welcome to the Airlinel Transfer Services Admin Panel', 'airlinel-theme')); ?></p>
    </div>

    <!-- Quick Stats -->
    <div class="airlinel-stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo intval($agency_count); ?></div>
            <div class="stat-label"><?php echo esc_html(__('Agencies', 'airlinel-theme')); ?></div>
            <div class="stat-action">
                <a href="<?php echo esc_url(admin_url('admin.php?page=airlinel-dashboard&tab=agencies')); ?>">
                    <?php echo esc_html(__('Manage', 'airlinel-theme')); ?>
                </a>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-value"><?php echo intval($booking_count); ?></div>
            <div class="stat-label"><?php echo esc_html(__('Bookings', 'airlinel-theme')); ?></div>
            <div class="stat-action">
                <a href="<?php echo esc_url(admin_url('admin.php?page=airlinel-reservations')); ?>">
                    <?php echo esc_html(__('View', 'airlinel-theme')); ?>
                </a>
            </div>
        </div>

        <?php if (!empty($exchange_rates)) : ?>
        <div class="stat-card">
            <div class="stat-value"><?php echo count($exchange_rates); ?></div>
            <div class="stat-label"><?php echo esc_html(__('Exchange Rates', 'airlinel-theme')); ?></div>
            <div class="stat-action">
                <a href="<?php echo esc_url(admin_url('admin.php?page=airlinel-exchange-rates')); ?>">
                    <?php echo esc_html(__('Update', 'airlinel-theme')); ?>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <div class="stat-card">
            <div class="stat-label"><?php echo esc_html(__('System Status', 'airlinel-theme')); ?></div>
            <div class="stat-value system-status">
                <span class="status-indicator status-online"></span>
                <?php echo esc_html(__('Online', 'airlinel-theme')); ?>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="airlinel-dashboard-grid">
        <!-- Settings Card -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2><?php echo esc_html(__('Settings', 'airlinel-theme')); ?></h2>
            </div>
            <div class="card-content">
                <p><?php echo esc_html(__('Manage API keys, rates, and system configuration', 'airlinel-theme')); ?></p>
                <ul class="card-links">
                    <li><a href="<?php echo esc_url(admin_url('admin.php?page=airlinel-settings')); ?>">
                        <?php echo esc_html(__('API & Payment Settings', 'airlinel-theme')); ?>
                    </a></li>
                    <li><a href="<?php echo esc_url(admin_url('admin.php?page=airlinel-zones')); ?>">
                        <?php echo esc_html(__('Pricing Zones', 'airlinel-theme')); ?>
                    </a></li>
                    <li><a href="<?php echo esc_url(admin_url('admin.php?page=airlinel-exchange-rates')); ?>">
                        <?php echo esc_html(__('Exchange Rates', 'airlinel-theme')); ?>
                    </a></li>
                </ul>
            </div>
        </div>

        <!-- Content Management Card -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2><?php echo esc_html(__('Content Management', 'airlinel-theme')); ?></h2>
            </div>
            <div class="card-content">
                <p><?php echo esc_html(__('Manage homepage and page content', 'airlinel-theme')); ?></p>
                <ul class="card-links">
                    <li><a href="<?php echo esc_url(admin_url('admin.php?page=airlinel-homepage-content')); ?>">
                        <?php echo esc_html(__('Homepage Content', 'airlinel-theme')); ?>
                    </a></li>
                    <li><a href="<?php echo esc_url(admin_url('admin.php?page=airlinel-page-content')); ?>">
                        <?php echo esc_html(__('Pages & Content', 'airlinel-theme')); ?>
                    </a></li>
                    <li><a href="<?php echo esc_url(admin_url('admin.php?page=airlinel-ads-txt')); ?>">
                        <?php echo esc_html(__('Ads.txt Manager', 'airlinel-theme')); ?>
                    </a></li>
                </ul>
            </div>
        </div>

        <!-- Data Management Card -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2><?php echo esc_html(__('Data Management', 'airlinel-theme')); ?></h2>
            </div>
            <div class="card-content">
                <p><?php echo esc_html(__('Manage agencies, reservations, and data sync', 'airlinel-theme')); ?></p>
                <ul class="card-links">
                    <li><a href="<?php echo esc_url(admin_url('edit.php?post_type=agencies')); ?>">
                        <?php echo esc_html(__('Agencies', 'airlinel-theme')); ?>
                    </a></li>
                    <li><a href="<?php echo esc_url(admin_url('admin.php?page=airlinel-reservations')); ?>">
                        <?php echo esc_html(__('Reservations', 'airlinel-theme')); ?>
                    </a></li>
                    <?php if (defined('AIRLINEL_IS_REGIONAL_SITE') && AIRLINEL_IS_REGIONAL_SITE) : ?>
                    <li><a href="<?php echo esc_url(admin_url('admin.php?page=airlinel-sync-dashboard')); ?>">
                        <?php echo esc_html(__('Sync Dashboard', 'airlinel-theme')); ?>
                    </a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Analytics Card -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2><?php echo esc_html(__('Analytics & Reporting', 'airlinel-theme')); ?></h2>
            </div>
            <div class="card-content">
                <p><?php echo esc_html(__('View analytics and system reports', 'airlinel-theme')); ?></p>
                <ul class="card-links">
                    <li><a href="<?php echo esc_url(admin_url('admin.php?page=airlinel-analytics')); ?>">
                        <?php echo esc_html(__('Analytics Dashboard', 'airlinel-theme')); ?>
                    </a></li>
                    <?php if (defined('AIRLINEL_IS_REGIONAL_SITE') && AIRLINEL_IS_REGIONAL_SITE) : ?>
                    <li><a href="<?php echo esc_url(admin_url('admin.php?page=airlinel-regional-settings')); ?>">
                        <?php echo esc_html(__('Regional Settings', 'airlinel-theme')); ?>
                    </a></li>
                    <?php else : ?>
                    <li><a href="<?php echo esc_url(admin_url('admin.php?page=airlinel-regional-keys')); ?>">
                        <?php echo esc_html(__('Regional Keys', 'airlinel-theme')); ?>
                    </a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Sync Status (if available) -->
    <?php if (!empty($sync_stats)) : ?>
    <div class="dashboard-card dashboard-card-wide">
        <div class="card-header">
            <h2><?php echo esc_html(__('Recent Sync Activity', 'airlinel-theme')); ?></h2>
        </div>
        <div class="card-content">
            <div class="sync-stats-grid">
                <div class="sync-stat">
                    <span class="sync-label"><?php echo esc_html(__('Last Vehicle Sync:', 'airlinel-theme')); ?></span>
                    <span class="sync-value"><?php echo isset($sync_stats['last_vehicle_sync']) ? esc_html($sync_stats['last_vehicle_sync']) : esc_html(__('Never', 'airlinel-theme')); ?></span>
                </div>
                <div class="sync-stat">
                    <span class="sync-label"><?php echo esc_html(__('Last Rate Sync:', 'airlinel-theme')); ?></span>
                    <span class="sync-value"><?php echo isset($sync_stats['last_rate_sync']) ? esc_html($sync_stats['last_rate_sync']) : esc_html(__('Never', 'airlinel-theme')); ?></span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>
