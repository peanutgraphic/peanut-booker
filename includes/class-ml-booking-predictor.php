<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ML Booking Predictor Integration
 *
 * Integrates with the Peanut ML Service for booking completion prediction
 * and dispute risk assessment.
 *
 * @package Peanut_Booker
 * @since   1.7.2
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Peanut Booker ML Predictor Class
 */
class Peanut_Booker_ML_Predictor {

    /**
     * ML Service base URL.
     *
     * @var string
     */
    private $ml_service_url = 'http://127.0.0.1:8100';

    /**
     * ML Service API key.
     *
     * @var string
     */
    private $ml_api_key = '';

    /**
     * Constructor.
     */
    public function __construct() {
        // Load ML service configuration from options.
        $ml_settings = get_option( 'peanut_ml_settings', array() );

        if ( ! empty( $ml_settings['service_url'] ) ) {
            $this->ml_service_url = esc_url_raw( $ml_settings['service_url'] );
        }

        if ( ! empty( $ml_settings['api_key'] ) ) {
            $this->ml_api_key = sanitize_text_field( $ml_settings['api_key'] );
        }
    }

    /**
     * Check if ML service is available and responding.
     *
     * @return bool
     */
    public function is_available() {
        $transient_key = 'peanut_booker_ml_health';

        // Check cache first (2-minute TTL).
        $cached_health = get_transient( $transient_key );
        if ( false !== $cached_health ) {
            return (bool) $cached_health;
        }

        $response = $this->make_request( 'GET', '/health' );

        if ( is_wp_error( $response ) ) {
            set_transient( $transient_key, 0, 2 * MINUTE_IN_SECONDS );
            return false;
        }

        set_transient( $transient_key, 1, 2 * MINUTE_IN_SECONDS );
        return true;
    }

    /**
     * Predict booking completion probability.
     *
     * @param array $booking_data Booking data array with keys:
     *                             - performer_id (int)
     *                             - customer_id (int)
     *                             - booking_amount (float)
     *                             - category (string, optional)
     *                             - has_escrow (bool)
     *                             - performer_rating (float)
     *                             - performer_completed_count (int)
     *                             - customer_booking_count (int)
     * @return array|WP_Error Prediction result or error.
     */
    public function predict_completion( $booking_data ) {
        if ( ! $this->is_available() ) {
            return new WP_Error(
                'ml_service_unavailable',
                __( 'ML service is currently unavailable.', 'peanut-booker' )
            );
        }

        // Normalize input data.
        $request_data = array(
            'performer_id'              => intval( $booking_data['performer_id'] ?? 0 ),
            'customer_id'               => intval( $booking_data['customer_id'] ?? 0 ),
            'booking_amount'            => floatval( $booking_data['booking_amount'] ?? 0 ),
            'category'                  => sanitize_text_field( $booking_data['category'] ?? 'general' ),
            'has_escrow'                => (bool) ( $booking_data['has_escrow'] ?? true ),
            'performer_rating'          => floatval( $booking_data['performer_rating'] ?? 0 ),
            'performer_completed_count' => intval( $booking_data['performer_completed_count'] ?? 0 ),
            'customer_booking_count'    => intval( $booking_data['customer_booking_count'] ?? 0 ),
        );

        $transient_key = 'peanut_booker_completion_pred_' . md5( wp_json_encode( $request_data ) );

        // Check cache first (15-minute TTL).
        $cached_result = get_transient( $transient_key );
        if ( false !== $cached_result ) {
            return $cached_result;
        }

        $response = $this->make_request( 'POST', '/predict-completion', $request_data );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        // Cache the result.
        set_transient( $transient_key, $response, 15 * MINUTE_IN_SECONDS );

        return $response;
    }

    /**
     * Predict dispute likelihood.
     *
     * @param array $booking_data Booking data array with keys:
     *                             - booking_id (int)
     *                             - amount (float)
     *                             - duration_days (int)
     *                             - performer_response_time_hours (float)
     *                             - messages_exchanged (int)
     * @return array|WP_Error Prediction result or error.
     */
    public function predict_dispute( $booking_data ) {
        if ( ! $this->is_available() ) {
            return new WP_Error(
                'ml_service_unavailable',
                __( 'ML service is currently unavailable.', 'peanut-booker' )
            );
        }

        // Normalize input data.
        $request_data = array(
            'booking_id'                   => intval( $booking_data['booking_id'] ?? 0 ),
            'amount'                       => floatval( $booking_data['amount'] ?? 0 ),
            'duration_days'                => intval( $booking_data['duration_days'] ?? 0 ),
            'performer_response_time_hours' => floatval( $booking_data['performer_response_time_hours'] ?? 0 ),
            'messages_exchanged'           => intval( $booking_data['messages_exchanged'] ?? 0 ),
        );

        $transient_key = 'peanut_booker_dispute_pred_' . md5( wp_json_encode( $request_data ) );

        // Check cache first (15-minute TTL).
        $cached_result = get_transient( $transient_key );
        if ( false !== $cached_result ) {
            return $cached_result;
        }

        $response = $this->make_request( 'POST', '/predict-dispute', $request_data );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        // Cache the result.
        set_transient( $transient_key, $response, 15 * MINUTE_IN_SECONDS );

        return $response;
    }

    /**
     * Train booking prediction models.
     *
     * Makes an async request to the ML service to train models from latest data.
     *
     * @return array|WP_Error Training result or error.
     */
    public function train_model() {
        if ( ! $this->is_available() ) {
            return new WP_Error(
                'ml_service_unavailable',
                __( 'ML service is currently unavailable.', 'peanut-booker' )
            );
        }

        // Clear training cache to force fresh run.
        delete_transient( 'peanut_booker_ml_last_train' );

        $response = $this->make_request( 'POST', '/train' );

        if ( ! is_wp_error( $response ) ) {
            set_transient( 'peanut_booker_ml_last_train', time(), WEEK_IN_SECONDS );
        }

        return $response;
    }

    /**
     * Make HTTP request to ML service.
     *
     * @param string       $method HTTP method (GET, POST).
     * @param string       $endpoint API endpoint path (e.g., /predict-completion).
     * @param array|null   $data Request body data (for POST).
     * @return array|WP_Error Response body as array or WP_Error.
     */
    private function make_request( $method = 'GET', $endpoint = '', $data = null ) {
        $url = trailingslashit( $this->ml_service_url ) . 'booking' . $endpoint;

        $args = array(
            'method'  => $method,
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        );

        if ( ! empty( $this->ml_api_key ) ) {
            $args['headers']['X-ML-API-Key'] = $this->ml_api_key;
        }

        if ( 'POST' === $method && ! empty( $data ) ) {
            $args['body'] = wp_json_encode( $data );
        }

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            error_log(
                sprintf(
                    '[Peanut Booker ML] HTTP error: %s %s',
                    $method,
                    $endpoint
                ) . ' ' . $response->get_error_message()
            );
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body        = wp_remote_retrieve_body( $response );

        if ( empty( $body ) ) {
            return new WP_Error(
                'ml_empty_response',
                __( 'ML service returned empty response.', 'peanut-booker' )
            );
        }

        $data = json_decode( $body, true );

        if ( null === $data ) {
            error_log(
                sprintf(
                    '[Peanut Booker ML] Invalid JSON response: %s',
                    $body
                )
            );
            return new WP_Error(
                'ml_invalid_json',
                __( 'ML service returned invalid JSON.', 'peanut-booker' )
            );
        }

        // Check for API error responses.
        if ( $status_code >= 400 ) {
            $detail = isset( $data['detail'] ) ? $data['detail'] : 'Unknown error';
            error_log(
                sprintf(
                    '[Peanut Booker ML] HTTP %d: %s',
                    $status_code,
                    $detail
                )
            );
            return new WP_Error(
                'ml_service_error',
                sprintf( __( 'ML service error: %s', 'peanut-booker' ), $detail )
            );
        }

        return $data;
    }
}
