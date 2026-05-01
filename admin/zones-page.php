<?php
/**
 * Airlinel Zones Admin Page
 * Manage pricing zones for UK and Turkey
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check permissions (also verified in callback)
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to manage zones.');
}

$zone_mgr = new Airlinel_Zone_Manager();

    if (isset($_POST['add_uk_zone'])) {
        check_admin_referer('zones_nonce');

        // Validate all fields present
        if (empty($_POST['uk_id']) || empty($_POST['uk_name']) || empty($_POST['uk_price']) || empty($_POST['uk_postcodes'])) {
            echo '<div class="notice notice-error"><p>All fields are required.</p></div>';
        } else {
            // Validate zone ID format (alphanumeric + underscore only)
            $zone_id = sanitize_key($_POST['uk_id']);
            if ($zone_id !== $_POST['uk_id']) {
                echo '<div class="notice notice-error"><p>Zone ID can only contain letters, numbers, and underscores.</p></div>';
            } else {
                // Check for duplicate
                $existing = $zone_mgr->get_uk_zones();
                if (isset($existing[$zone_id])) {
                    echo '<div class="notice notice-error"><p>This Zone ID already exists.</p></div>';
                } else {
                    $zone_mgr->add_uk_zone($zone_id, array(
                        'name' => sanitize_text_field($_POST['uk_name']),
                        'base_gbp' => floatval($_POST['uk_price']),
                        'postcodes' => array_filter(array_map('strtoupper', array_map('trim', explode(',', $_POST['uk_postcodes'])))),
                    ));
                    echo '<div class="notice notice-success"><p>UK zone added!</p></div>';
                }
            }
        }
    }

    if (isset($_POST['add_tr_zone'])) {
        check_admin_referer('zones_nonce');

        if (empty($_POST['tr_id']) || empty($_POST['tr_name']) || empty($_POST['tr_price']) || empty($_POST['tr_areas'])) {
            echo '<div class="notice notice-error"><p>All fields are required.</p></div>';
        } else {
            $zone_id = sanitize_key($_POST['tr_id']);
            if ($zone_id !== $_POST['tr_id']) {
                echo '<div class="notice notice-error"><p>Zone ID can only contain letters, numbers, and underscores.</p></div>';
            } else {
                $existing = $zone_mgr->get_tr_zones();
                if (isset($existing[$zone_id])) {
                    echo '<div class="notice notice-error"><p>This Zone ID already exists.</p></div>';
                } else {
                    $zone_mgr->add_tr_zone($zone_id, array(
                        'name' => sanitize_text_field($_POST['tr_name']),
                        'base_gbp' => floatval($_POST['tr_price']),
                        'areas' => array_filter(array_map('trim', explode(',', $_POST['tr_areas']))),
                    ));
                    echo '<div class="notice notice-success"><p>TR zone added!</p></div>';
                }
            }
        }
    }

    ?>
    <div class="wrap">
        <h1>Pricing Zones</h1>

        <h2>UK Zones</h2>
        <table class="widefat">
            <thead><tr><th>Name</th><th>Base (£)</th><th>Postcodes</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($zone_mgr->get_uk_zones() as $id => $zone) { ?>
                    <tr>
                        <td><?php echo esc_html($zone['name']); ?></td>
                        <td>£<?php echo esc_html($zone['base_gbp']); ?></td>
                        <td><?php echo esc_html(implode(', ', $zone['postcodes'])); ?></td>
                        <td><a href="#" class="button button-small">Edit</a> <a href="#" class="button button-small">Delete</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <h3>Add UK Zone</h3>
        <form method="post">
            <?php wp_nonce_field('zones_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label>Zone ID</label></th>
                    <td><input type="text" name="uk_id" placeholder="e.g., zone_4"></td>
                </tr>
                <tr>
                    <th><label>Name</label></th>
                    <td><input type="text" name="uk_name" placeholder="e.g., Zone 4 - Outer London"></td>
                </tr>
                <tr>
                    <th><label>Base Price (£)</label></th>
                    <td><input type="number" name="uk_price" step="0.01" placeholder="8.00"></td>
                </tr>
                <tr>
                    <th><label>Postcodes (comma-separated)</label></th>
                    <td><textarea name="uk_postcodes" rows="3">E4, E10, N9, NW7, SE9</textarea></td>
                </tr>
            </table>
            <?php submit_button('Add UK Zone', 'primary', 'add_uk_zone'); ?>
        </form>

        <h2 style="margin-top:50px;">Turkey Zones</h2>
        <table class="widefat">
            <thead><tr><th>Name</th><th>Base (£)</th><th>Areas</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($zone_mgr->get_tr_zones() as $id => $zone) { ?>
                    <tr>
                        <td><?php echo esc_html($zone['name']); ?></td>
                        <td>£<?php echo esc_html($zone['base_gbp']); ?></td>
                        <td><?php echo esc_html(implode(', ', $zone['areas'] ?? array())); ?></td>
                        <td><a href="#" class="button button-small">Edit</a> <a href="#" class="button button-small">Delete</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <h3>Add Turkey Zone</h3>
        <form method="post">
            <?php wp_nonce_field('zones_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label>Zone ID</label></th>
                    <td><input type="text" name="tr_id" placeholder="e.g., istanbul_outer"></td>
                </tr>
                <tr>
                    <th><label>Name</label></th>
                    <td><input type="text" name="tr_name" placeholder="e.g., Istanbul - Outer"></td>
                </tr>
                <tr>
                    <th><label>Base Price (£)</label></th>
                    <td><input type="number" name="tr_price" step="0.01" placeholder="8.00"></td>
                </tr>
                <tr>
                    <th><label>Areas (comma-separated)</label></th>
                    <td><textarea name="tr_areas" rows="3">Fatih, Beyoğlu, Çankırı</textarea></td>
                </tr>
            </table>
            <?php submit_button('Add TR Zone', 'primary', 'add_tr_zone'); ?>
        </form>
    </div>
    <?php
