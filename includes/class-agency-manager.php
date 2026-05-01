<?php
/**
 * Airlinel Agency Manager
 * Manages agency partners, commission tracking, and verification
 */

class Airlinel_Agency_Manager {

    /**
     * Create a new agency
     *
     * @param string $code Unique agency code
     * @param string $name Agency name
     * @param string $email Agency contact email
     * @param float $commission_percent Commission percentage (default: 15)
     * @return int Post ID of created agency
     */
    public function create($code, $name, $email, $commission_percent = 15) {
        $post_id = wp_insert_post(array(
            'post_type' => 'agencies',
            'post_title' => $name,
            'post_status' => 'publish',
        ));

        if (is_wp_error($post_id)) {
            return false;
        }

        update_post_meta($post_id, 'agency_code', $code);
        update_post_meta($post_id, 'email', $email);
        update_post_meta($post_id, 'commission_percent', $commission_percent);

        return $post_id;
    }

    /**
     * Verify agency exists and return details
     *
     * @param string $code Agency code to verify
     * @return array|false Agency details or false if not found
     */
    public function verify($code) {
        // Sanitize code input
        $code = sanitize_text_field($code);
        if (empty($code)) {
            return false;
        }

        $posts = get_posts(array(
            'post_type' => 'agencies',
            'meta_query' => array(
                array(
                    'key' => 'agency_code',
                    'value' => $code,
                )
            ),
            'posts_per_page' => 1,
            'no_found_rows' => true,
        ));

        if (empty($posts)) {
            return false;
        }

        $post = $posts[0];
        return array(
            'id' => $post->ID,
            'name' => $post->post_title,
            'commission_percent' => get_post_meta($post->ID, 'commission_percent', true),
        );
    }

    /**
     * Get agency earnings for different time periods
     *
     * @param int $agency_id Agency post ID
     * @return array Earnings with keys: week, month, all
     */
    public function get_earnings($agency_id) {
        // Validate agency_id
        $agency_id = intval($agency_id);
        if ($agency_id <= 0) {
            error_log('Airlinel: Invalid agency_id in get_earnings() - ' . var_export($agency_id, true));
            return array('week' => 0, 'month' => 0, 'all' => 0);
        }

        $posts = get_posts(array(
            'post_type' => 'reservations',
            'meta_query' => array(
                array(
                    'key' => 'agency_id',
                    'value' => $agency_id,
                )
            ),
            'posts_per_page' => -1,
        ));

        $week = 0;
        $month = 0;
        $all = 0;

        $week_ago = strtotime('-7 days');
        $month_ago = strtotime('-30 days');

        foreach ($posts as $res) {
            $comm = floatval(get_post_meta($res->ID, 'agency_commission', true));
            $res_date = strtotime($res->post_date);

            $all += $comm;
            if ($res_date > $week_ago) {
                $week += $comm;
            }
            if ($res_date > $month_ago) {
                $month += $comm;
            }
        }

        return compact('week', 'month', 'all');
    }
}
?>
