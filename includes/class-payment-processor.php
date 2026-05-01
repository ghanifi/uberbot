<?php
/**
 * Airlinel Payment Processor
 * Handles Stripe 3D Secure payment processing with PaymentIntent API
 */
class Airlinel_Payment_Processor {

    private $stripe_secret_key;

    public function __construct() {
        $this->stripe_secret_key = Airlinel_Settings_Manager::get('airlinel_stripe_secret_key');
        if (!$this->stripe_secret_key) {
            error_log('Airlinel: Stripe secret key not configured');
        }
    }

    public function create_payment_intent($reservation_id, $amount_gbp, $currency = 'GBP', $customer_email = '') {
        if (!$this->stripe_secret_key) {
            return new WP_Error('stripe_not_configured', 'Stripe is not configured');
        }

        if ($amount_gbp <= 0) {
            return new WP_Error('invalid_amount', 'Payment amount must be greater than 0');
        }

        // Amount in cents
        $amount_cents = intval($amount_gbp * 100);

        // Prepare metadata for tracking
        $metadata = array(
            'reservation_id' => $reservation_id,
            'site_url' => get_home_url(),
            'timestamp' => current_time('mysql'),
        );

        // Create payment intent via Stripe API
        $response = wp_remote_post('https://api.stripe.com/v1/payment_intents', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->stripe_secret_key,
            ),
            'body' => array(
                'amount' => $amount_cents,
                'currency' => strtolower($currency),
                'payment_method_types' => array('card'),
                'metadata' => $metadata,
                'receipt_email' => sanitize_email($customer_email),
                'confirm' => 'false',
            ),
        ));

        if (is_wp_error($response)) {
            error_log('Airlinel: Stripe API error - ' . $response->get_error_message());
            return new WP_Error('stripe_api_error', 'Failed to create payment intent');
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            error_log('Airlinel: Stripe error - ' . $body['error']['message']);
            return new WP_Error('stripe_error', $body['error']['message']);
        }

        // Store intent ID in reservation
        update_post_meta($reservation_id, 'stripe_intent_id', $body['id']);
        update_post_meta($reservation_id, 'stripe_intent_status', $body['status']);
        update_post_meta($reservation_id, 'payment_amount_gbp', floatval($amount_gbp));

        return array(
            'intent_id' => $body['id'],
            'client_secret' => $body['client_secret'],
            'status' => $body['status'],
            'amount' => $body['amount'],
            'currency' => $body['currency'],
        );
    }

    public function confirm_payment($intent_id) {
        if (!$this->stripe_secret_key) {
            return new WP_Error('stripe_not_configured', 'Stripe is not configured');
        }

        // Retrieve payment intent from Stripe
        $response = wp_remote_get('https://api.stripe.com/v1/payment_intents/' . $intent_id, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->stripe_secret_key,
            ),
        ));

        if (is_wp_error($response)) {
            error_log('Airlinel: Stripe API error - ' . $response->get_error_message());
            return new WP_Error('stripe_api_error', 'Failed to retrieve payment intent');
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            error_log('Airlinel: Stripe error - ' . $body['error']['message']);
            return new WP_Error('stripe_error', $body['error']['message']);
        }

        // Extract metadata and find reservation
        $metadata = $body['metadata'];
        $reservation_id = intval($metadata['reservation_id'] ?? 0);

        if (!$reservation_id) {
            return new WP_Error('no_reservation', 'No reservation associated with this payment');
        }

        // Update reservation with payment status
        if ($body['status'] === 'succeeded') {
            update_post_meta($reservation_id, 'payment_status', 'completed');
            update_post_meta($reservation_id, 'stripe_charge_id', $body['charges']['data'][0]['id'] ?? '');
            wp_update_post(array('ID' => $reservation_id, 'post_status' => 'processing'));
            return array('success' => true, 'status' => 'succeeded');
        } else if ($body['status'] === 'requires_payment_method') {
            update_post_meta($reservation_id, 'payment_status', 'pending');
            return array('success' => false, 'status' => 'requires_payment_method', 'message' => 'Payment method required');
        } else if ($body['status'] === 'requires_action') {
            update_post_meta($reservation_id, 'payment_status', 'requires_action');
            return array('success' => false, 'status' => 'requires_action', 'message' => 'Additional authentication required');
        }

        return array('success' => false, 'status' => $body['status'], 'message' => 'Payment not completed');
    }

    public function get_payment_status($reservation_id) {
        $intent_id = get_post_meta($reservation_id, 'stripe_intent_id', true);
        if (!$intent_id) {
            return array('status' => 'not_started');
        }

        $status = get_post_meta($reservation_id, 'stripe_intent_status', true);
        $payment_status = get_post_meta($reservation_id, 'payment_status', true);

        return array(
            'intent_id' => $intent_id,
            'intent_status' => $status,
            'payment_status' => $payment_status,
        );
    }
}
?>
