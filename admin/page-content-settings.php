<?php
/**
 * Page Content Settings Admin Page
 * Manages editable content for about, contact, and other pages
 * Supports regional site overrides with fallback to main site defaults
 */

if (!current_user_can('manage_options')) {
    wp_die('Unauthorized access');
}

require_once get_template_directory() . '/includes/class-page-manager.php';

// Determine if this is a regional site
$is_regional = defined('AIRLINEL_IS_REGIONAL_SITE') && AIRLINEL_IS_REGIONAL_SITE;
$regional_prefix = $is_regional ? 'regional_' : '';

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['airlinel_page_settings_nonce'])) {
    // Verify nonce
    if (!wp_verify_nonce($_POST['airlinel_page_settings_nonce'], 'airlinel_page_settings_action')) {
        wp_die('Nonce verification failed');
    }

    // Update contact information
    if (isset($_POST['contact_phone'])) {
        $phone = sanitize_text_field($_POST['contact_phone']);
        if (!$is_regional && $phone === '') {
            // Main site - keep default if empty
            delete_option('airlinel_contact_phone');
        } else {
            update_option($regional_prefix . 'airlinel_contact_phone', $phone);
        }
    }
    if (isset($_POST['contact_email'])) {
        $email = sanitize_email($_POST['contact_email']);
        if (!$is_regional && $email === '') {
            delete_option('airlinel_contact_email');
        } else {
            update_option($regional_prefix . 'airlinel_contact_email', $email);
        }
    }
    if (isset($_POST['contact_address'])) {
        $address = sanitize_text_field($_POST['contact_address']);
        if (!$is_regional && $address === '') {
            delete_option('airlinel_contact_address');
        } else {
            update_option($regional_prefix . 'airlinel_contact_address', $address);
        }
    }

    // Update company information
    if (isset($_POST['company_description'])) {
        $desc = wp_kses_post($_POST['company_description']);
        if (!$is_regional && $desc === '') {
            delete_option('airlinel_company_description');
        } else {
            update_option($regional_prefix . 'airlinel_company_description', $desc);
        }
    }
    if (isset($_POST['company_mission'])) {
        $mission = wp_kses_post($_POST['company_mission']);
        if (!$is_regional && $mission === '') {
            delete_option('airlinel_company_mission');
        } else {
            update_option($regional_prefix . 'airlinel_company_mission', $mission);
        }
    }
    if (isset($_POST['company_history'])) {
        $history = wp_kses_post($_POST['company_history']);
        if (!$is_regional && $history === '') {
            delete_option('airlinel_company_history');
        } else {
            update_option($regional_prefix . 'airlinel_company_history', $history);
        }
    }

    // Update trust indicators
    if (isset($_POST['years_in_business'])) {
        $years = intval($_POST['years_in_business']);
        if (!$is_regional && $years === 0) {
            delete_option('airlinel_years_in_business');
        } else {
            update_option($regional_prefix . 'airlinel_years_in_business', $years);
        }
    }
    if (isset($_POST['customers_served'])) {
        $customers = intval($_POST['customers_served']);
        if (!$is_regional && $customers === 0) {
            delete_option('airlinel_customers_served');
        } else {
            update_option($regional_prefix . 'airlinel_customers_served', $customers);
        }
    }
    if (isset($_POST['fleet_size'])) {
        $fleet = intval($_POST['fleet_size']);
        if (!$is_regional && $fleet === 0) {
            delete_option('airlinel_fleet_size');
        } else {
            update_option($regional_prefix . 'airlinel_fleet_size', $fleet);
        }
    }
    if (isset($_POST['daily_rides'])) {
        $rides = intval($_POST['daily_rides']);
        if (!$is_regional && $rides === 0) {
            delete_option('airlinel_daily_rides');
        } else {
            update_option($regional_prefix . 'airlinel_daily_rides', $rides);
        }
    }

    // Update business hours
    if (isset($_POST['business_hours'])) {
        $hours = array();
        $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');

        foreach ($days as $day) {
            if (isset($_POST['business_hours'][$day])) {
                $hours[$day] = array(
                    'open' => sanitize_text_field($_POST['business_hours'][$day]['open']),
                    'close' => sanitize_text_field($_POST['business_hours'][$day]['close']),
                );
            }
        }

        if (!$is_regional && empty(array_filter($hours))) {
            delete_option('airlinel_business_hours');
        } else {
            update_option($regional_prefix . 'airlinel_business_hours', $hours);
        }
    }

    $message = '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'airlinel-theme') . '</p></div>';
}

// Get current settings for display
$contact_phone = get_option($regional_prefix . 'airlinel_contact_phone', get_option('airlinel_contact_phone', ''));
$contact_email = get_option($regional_prefix . 'airlinel_contact_email', get_option('airlinel_contact_email', ''));
$contact_address = get_option($regional_prefix . 'airlinel_contact_address', get_option('airlinel_contact_address', ''));

$company_description = get_option($regional_prefix . 'airlinel_company_description', get_option('airlinel_company_description', ''));
$company_mission = get_option($regional_prefix . 'airlinel_company_mission', get_option('airlinel_company_mission', ''));
$company_history = get_option($regional_prefix . 'airlinel_company_history', get_option('airlinel_company_history', ''));

$years_in_business = intval(get_option($regional_prefix . 'airlinel_years_in_business', get_option('airlinel_years_in_business', 0)));
$customers_served = intval(get_option($regional_prefix . 'airlinel_customers_served', get_option('airlinel_customers_served', 0)));
$fleet_size = intval(get_option($regional_prefix . 'airlinel_fleet_size', get_option('airlinel_fleet_size', 0)));
$daily_rides = intval(get_option($regional_prefix . 'airlinel_daily_rides', get_option('airlinel_daily_rides', 0)));

$hours = get_option($regional_prefix . 'airlinel_business_hours', get_option('airlinel_business_hours', array()));
?>

<div class="wrap">
    <h1>
        <?php _e('Page Content Settings', 'airlinel-theme'); ?>
        <?php if ($is_regional): ?>
            <span class="airlinel-regional-badge" style="font-size: 14px; color: #666; margin-left: 20px; font-weight: normal;">
                <?php _e('(Regional Site - override main site defaults)', 'airlinel-theme'); ?>
            </span>
        <?php endif; ?>
    </h1>

    <?php echo $message; ?>

    <!-- Information Box -->
    <div style="background: #fffbeb; border: 1px solid #fcd34d; padding: 15px; border-radius: 6px; margin: 20px 0;">
        <?php if ($is_regional): ?>
            <p><strong><?php _e('Regional Site Mode', 'airlinel-theme'); ?></strong></p>
            <p><?php _e('Leave fields blank to use main site defaults. Fill in values to customize for this regional site.', 'airlinel-theme'); ?></p>
        <?php else: ?>
            <p><strong><?php _e('Main Site Settings', 'airlinel-theme'); ?></strong></p>
            <p><?php _e('These are the default values used by regional sites. Regional sites can override these settings locally.', 'airlinel-theme'); ?></p>
        <?php endif; ?>
    </div>

    <form method="post" action="" class="airlinel-page-settings-form">
        <?php wp_nonce_field('airlinel_page_settings_action', 'airlinel_page_settings_nonce'); ?>

        <!-- Contact Information Section -->
        <div class="postbox">
            <h2 class="hndle"><span><?php _e('Contact Information', 'airlinel-theme'); ?></span></h2>
            <div class="inside">
                <table class="form-table">
                    <!-- Phone -->
                    <tr>
                        <th scope="row">
                            <label for="contact_phone"><?php _e('Phone Number', 'airlinel-theme'); ?></label>
                        </th>
                        <td>
                            <input
                                type="text"
                                id="contact_phone"
                                name="contact_phone"
                                value="<?php echo esc_attr($contact_phone); ?>"
                                class="regular-text"
                                placeholder="+44 20 XXXX XXXX"
                            />
                            <p class="description"><?php _e('Primary contact phone number', 'airlinel-theme'); ?></p>
                        </td>
                    </tr>

                    <!-- Email -->
                    <tr>
                        <th scope="row">
                            <label for="contact_email"><?php _e('Email Address', 'airlinel-theme'); ?></label>
                        </th>
                        <td>
                            <input
                                type="email"
                                id="contact_email"
                                name="contact_email"
                                value="<?php echo esc_attr($contact_email); ?>"
                                class="regular-text"
                            />
                            <p class="description"><?php _e('Email where contact form submissions will be sent', 'airlinel-theme'); ?></p>
                        </td>
                    </tr>

                    <!-- Address -->
                    <tr>
                        <th scope="row">
                            <label for="contact_address"><?php _e('Address', 'airlinel-theme'); ?></label>
                        </th>
                        <td>
                            <textarea
                                id="contact_address"
                                name="contact_address"
                                class="large-text"
                                rows="3"
                            ><?php echo esc_textarea($contact_address); ?></textarea>
                            <p class="description"><?php _e('Office address (displayed on contact page)', 'airlinel-theme'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Company Information Section -->
        <div class="postbox">
            <h2 class="hndle"><span><?php _e('Company Information', 'airlinel-theme'); ?></span></h2>
            <div class="inside">
                <table class="form-table">
                    <!-- Company Description -->
                    <tr>
                        <th scope="row">
                            <label for="company_description"><?php _e('Company Description', 'airlinel-theme'); ?></label>
                        </th>
                        <td>
                            <?php
                            wp_editor(
                                $company_description,
                                'company_description',
                                array(
                                    'textarea_rows' => 5,
                                    'media_buttons' => false,
                                    'teeny' => false,
                                )
                            );
                            ?>
                            <p class="description"><?php _e('Displayed in the hero section of the About page', 'airlinel-theme'); ?></p>
                        </td>
                    </tr>

                    <!-- Company Mission -->
                    <tr>
                        <th scope="row">
                            <label for="company_mission"><?php _e('Mission Statement', 'airlinel-theme'); ?></label>
                        </th>
                        <td>
                            <?php
                            wp_editor(
                                $company_mission,
                                'company_mission',
                                array(
                                    'textarea_rows' => 5,
                                    'media_buttons' => false,
                                    'teeny' => false,
                                )
                            );
                            ?>
                            <p class="description"><?php _e('Your company\'s mission and core values', 'airlinel-theme'); ?></p>
                        </td>
                    </tr>

                    <!-- Company History -->
                    <tr>
                        <th scope="row">
                            <label for="company_history"><?php _e('Company History', 'airlinel-theme'); ?></label>
                        </th>
                        <td>
                            <?php
                            wp_editor(
                                $company_history,
                                'company_history',
                                array(
                                    'textarea_rows' => 5,
                                    'media_buttons' => false,
                                    'teeny' => false,
                                )
                            );
                            ?>
                            <p class="description"><?php _e('Background and history section for the About page', 'airlinel-theme'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Trust Indicators Section -->
        <div class="postbox">
            <h2 class="hndle"><span><?php _e('Trust Indicators', 'airlinel-theme'); ?></span></h2>
            <div class="inside">
                <p class="description"><?php _e('Statistics displayed in the About page to build credibility', 'airlinel-theme'); ?></p>
                <table class="form-table">
                    <!-- Years in Business -->
                    <tr>
                        <th scope="row">
                            <label for="years_in_business"><?php _e('Years in Business', 'airlinel-theme'); ?></label>
                        </th>
                        <td>
                            <input
                                type="number"
                                id="years_in_business"
                                name="years_in_business"
                                value="<?php echo $years_in_business; ?>"
                                min="0"
                                class="small-text"
                            />
                        </td>
                    </tr>

                    <!-- Customers Served -->
                    <tr>
                        <th scope="row">
                            <label for="customers_served"><?php _e('Customers Served', 'airlinel-theme'); ?></label>
                        </th>
                        <td>
                            <input
                                type="number"
                                id="customers_served"
                                name="customers_served"
                                value="<?php echo $customers_served; ?>"
                                min="0"
                                class="small-text"
                            />
                            <p class="description"><?php _e('Total number of customers served (will display with + and thousand separator)', 'airlinel-theme'); ?></p>
                        </td>
                    </tr>

                    <!-- Fleet Size -->
                    <tr>
                        <th scope="row">
                            <label for="fleet_size"><?php _e('Fleet Size', 'airlinel-theme'); ?></label>
                        </th>
                        <td>
                            <input
                                type="number"
                                id="fleet_size"
                                name="fleet_size"
                                value="<?php echo $fleet_size; ?>"
                                min="0"
                                class="small-text"
                            />
                            <p class="description"><?php _e('Total number of vehicles in fleet', 'airlinel-theme'); ?></p>
                        </td>
                    </tr>

                    <!-- Daily Rides -->
                    <tr>
                        <th scope="row">
                            <label for="daily_rides"><?php _e('Daily Rides', 'airlinel-theme'); ?></label>
                        </th>
                        <td>
                            <input
                                type="number"
                                id="daily_rides"
                                name="daily_rides"
                                value="<?php echo $daily_rides; ?>"
                                min="0"
                                class="small-text"
                            />
                            <p class="description"><?php _e('Average number of rides per day', 'airlinel-theme'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Business Hours Section -->
        <div class="postbox">
            <h2 class="hndle"><span><?php _e('Business Hours', 'airlinel-theme'); ?></span></h2>
            <div class="inside">
                <p class="description"><?php _e('Set your business hours for each day of the week', 'airlinel-theme'); ?></p>
                <table class="widefat" style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th><?php _e('Day', 'airlinel-theme'); ?></th>
                            <th><?php _e('Opening Time', 'airlinel-theme'); ?></th>
                            <th><?php _e('Closing Time', 'airlinel-theme'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $days = array(
                            'monday' => __('Monday', 'airlinel-theme'),
                            'tuesday' => __('Tuesday', 'airlinel-theme'),
                            'wednesday' => __('Wednesday', 'airlinel-theme'),
                            'thursday' => __('Thursday', 'airlinel-theme'),
                            'friday' => __('Friday', 'airlinel-theme'),
                            'saturday' => __('Saturday', 'airlinel-theme'),
                            'sunday' => __('Sunday', 'airlinel-theme'),
                        );

                        foreach ($days as $day_key => $day_label) {
                            $day_hours = $hours[$day_key] ?? array('open' => '06:00', 'close' => '23:00');
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($day_label); ?></strong>
                                </td>
                                <td>
                                    <input
                                        type="time"
                                        name="business_hours[<?php echo esc_attr($day_key); ?>][open]"
                                        value="<?php echo esc_attr($day_hours['open'] ?? '06:00'); ?>"
                                    />
                                </td>
                                <td>
                                    <input
                                        type="time"
                                        name="business_hours[<?php echo esc_attr($day_key); ?>][close]"
                                        value="<?php echo esc_attr($day_hours['close'] ?? '23:00'); ?>"
                                    />
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Submit Button -->
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'airlinel-theme'); ?>" />
        </p>
    </form>

    <!-- Help Section -->
    <div class="postbox">
        <h2 class="hndle"><span><?php _e('How Regional Overrides Work', 'airlinel-theme'); ?></span></h2>
        <div class="inside">
            <ul style="list-style: disc; margin-left: 20px; line-height: 1.8;">
                <li><?php _e('Main site: Set default values here', 'airlinel-theme'); ?></li>
                <li><?php _e('Regional sites: Leave blank to use main site defaults', 'airlinel-theme'); ?></li>
                <li><?php _e('Regional sites: Fill in values to customize for your region', 'airlinel-theme'); ?></li>
                <li><?php _e('Example: Main site hours are 6am-11pm, but a regional site can have different hours', 'airlinel-theme'); ?></li>
                <li><?php _e('The system checks: regional override → main site value → default value', 'airlinel-theme'); ?></li>
            </ul>
        </div>
    </div>
</div>

<style>
.airlinel-page-settings-form .postbox {
    margin-bottom: 20px;
}

.airlinel-page-settings-form .form-table td {
    vertical-align: top;
}

.airlinel-page-settings-form textarea {
    width: 100%;
}

.airlinel-page-settings-form input[type="time"] {
    padding: 5px;
    border: 1px solid #ddd;
    border-radius: 3px;
}

.airlinel-regional-badge {
    display: inline-block;
    background: #e7f3ff;
    border: 1px solid #b3d9ff;
    padding: 4px 8px;
    border-radius: 3px;
}
</style>
