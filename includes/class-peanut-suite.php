<?php
/**
 * Peanut Suite integration.
 *
 * @package Peanut_Booker
 * @since   1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Peanut Suite integration class.
 *
 * Provides hooks and API endpoints for analytics integration with Peanut Suite.
 */
class Peanut_Booker_Peanut_Suite {

    /**
     * Constructor.
     */
    public function __construct() {
        // Register hooks for Peanut Suite to consume.
        $this->register_hooks();

        // Register REST API endpoints for analytics.
        add_action( 'rest_api_init', array( $this, 'register_analytics_routes' ) );
    }

    /**
     * Register action hooks for all major events.
     */
    private function register_hooks() {
        // Performer events.
        add_action( 'peanut_booker_performer_created', array( $this, 'track_performer_created' ), 10, 3 );
        add_action( 'peanut_booker_performer_updated', array( $this, 'track_performer_updated' ), 10, 2 );
        add_action( 'peanut_booker_achievement_updated', array( $this, 'track_achievement_updated' ), 10, 3 );

        // Booking events.
        add_action( 'peanut_booker_booking_created', array( $this, 'track_booking_created' ), 10, 2 );
        add_action( 'peanut_booker_booking_status_changed', array( $this, 'track_booking_status_changed' ), 10, 3 );
        add_action( 'peanut_booker_booking_completed', array( $this, 'track_booking_completed' ), 10, 2 );
        add_action( 'peanut_booker_booking_cancelled', array( $this, 'track_booking_cancelled' ), 10, 2 );
        add_action( 'peanut_booker_payment_received', array( $this, 'track_payment_received' ), 10, 3 );
        add_action( 'peanut_booker_escrow_released', array( $this, 'track_escrow_released' ), 10, 2 );

        // Market events.
        add_action( 'peanut_booker_market_event_created', array( $this, 'track_market_event_created' ), 10, 2 );
        add_action( 'peanut_booker_bid_submitted', array( $this, 'track_bid_submitted' ), 10, 3 );
        add_action( 'peanut_booker_bid_accepted', array( $this, 'track_bid_accepted' ), 10, 2 );

        // Review events.
        add_action( 'peanut_booker_review_submitted', array( $this, 'track_review_submitted' ), 10, 2 );

        // Subscription events.
        add_action( 'peanut_booker_subscription_activated', array( $this, 'track_subscription_activated' ), 10, 2 );
        add_action( 'peanut_booker_subscription_cancelled', array( $this, 'track_subscription_cancelled' ), 10, 2 );
    }

    /**
     * Register analytics REST routes.
     */
    public function register_analytics_routes() {
        $namespace = 'peanut-booker/v1';

        register_rest_route(
            $namespace,
            '/analytics/summary',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_analytics_summary' ),
                'permission_callback' => array( $this, 'check_analytics_permission' ),
            )
        );

        register_rest_route(
            $namespace,
            '/analytics/bookings',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_booking_analytics' ),
                'permission_callback' => array( $this, 'check_analytics_permission' ),
            )
        );

        register_rest_route(
            $namespace,
            '/analytics/revenue',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_revenue_analytics' ),
                'permission_callback' => array( $this, 'check_analytics_permission' ),
            )
        );

        register_rest_route(
            $namespace,
            '/analytics/performers',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_performer_analytics' ),
                'permission_callback' => array( $this, 'check_analytics_permission' ),
            )
        );
    }

    /**
     * Check analytics permission.
     *
     * @return bool
     */
    public function check_analytics_permission() {
        // Allow Peanut Suite or admin access.
        if ( current_user_can( 'manage_options' ) ) {
            return true;
        }

        // Check for Peanut Suite API key.
        $api_key = isset( $_SERVER['HTTP_X_PEANUT_SUITE_KEY'] ) ? $_SERVER['HTTP_X_PEANUT_SUITE_KEY'] : '';
        $stored_key = get_option( 'peanut_booker_suite_api_key' );

        return ! empty( $api_key ) && $api_key === $stored_key;
    }

    /**
     * Fire event to Peanut Suite.
     *
     * @param string $event Event name.
     * @param array  $data  Event data.
     */
    private function fire_event( $event, $data ) {
        /**
         * Fires when a trackable event occurs.
         *
         * @param string $event Event name.
         * @param array  $data  Event data.
         */
        do_action( 'peanut_suite_track_event', $event, $data );
        do_action( 'peanut_suite_' . $event, $data );

        // Also store locally for analytics.
        $this->store_event( $event, $data );
    }

    /**
     * Store event for local analytics.
     *
     * @param string $event Event name.
     * @param array  $data  Event data.
     */
    private function store_event( $event, $data ) {
        $events = get_option( 'peanut_booker_recent_events', array() );

        array_unshift(
            $events,
            array(
                'event'     => $event,
                'data'      => $data,
                'timestamp' => current_time( 'mysql' ),
            )
        );

        // Keep last 100 events.
        $events = array_slice( $events, 0, 100 );

        update_option( 'peanut_booker_recent_events', $events );
    }

    // Event tracking methods.

    /**
     * Track performer creation.
     *
     * @param int $performer_id Performer ID.
     * @param int $user_id      User ID.
     * @param int $profile_id   Profile post ID.
     */
    public function track_performer_created( $performer_id, $user_id, $profile_id ) {
        $this->fire_event(
            'performer_created',
            array(
                'performer_id' => $performer_id,
                'user_id'      => $user_id,
                'profile_id'   => $profile_id,
            )
        );
    }

    /**
     * Track performer update.
     *
     * @param int    $performer_id Performer ID.
     * @param object $performer    Performer object.
     */
    public function track_performer_updated( $performer_id, $performer ) {
        $this->fire_event(
            'performer_updated',
            array(
                'performer_id'        => $performer_id,
                'tier'                => $performer->tier,
                'profile_completeness' => $performer->profile_completeness,
            )
        );
    }

    /**
     * Track achievement update.
     *
     * @param int    $performer_id Performer ID.
     * @param string $level        Achievement level.
     * @param int    $score        Achievement score.
     */
    public function track_achievement_updated( $performer_id, $level, $score ) {
        $this->fire_event(
            'achievement_updated',
            array(
                'performer_id' => $performer_id,
                'level'        => $level,
                'score'        => $score,
            )
        );
    }

    /**
     * Track booking creation.
     *
     * @param int   $booking_id Booking ID.
     * @param array $data       Booking data.
     */
    public function track_booking_created( $booking_id, $data ) {
        $this->fire_event(
            'booking_created',
            array(
                'booking_id'   => $booking_id,
                'performer_id' => $data['performer_id'],
                'customer_id'  => $data['customer_id'],
                'total_amount' => $data['total_amount'],
                'event_date'   => $data['event_date'],
            )
        );
    }

    /**
     * Track booking status change.
     *
     * @param int    $booking_id Booking ID.
     * @param string $new_status New status.
     * @param string $old_status Old status.
     */
    public function track_booking_status_changed( $booking_id, $new_status, $old_status ) {
        $this->fire_event(
            'booking_status_changed',
            array(
                'booking_id' => $booking_id,
                'new_status' => $new_status,
                'old_status' => $old_status,
            )
        );
    }

    /**
     * Track booking completion.
     *
     * @param int    $booking_id Booking ID.
     * @param object $booking    Booking object.
     */
    public function track_booking_completed( $booking_id, $booking ) {
        $this->fire_event(
            'booking_completed',
            array(
                'booking_id'          => $booking_id,
                'performer_id'        => $booking->performer_id,
                'customer_id'         => $booking->customer_id,
                'total_amount'        => $booking->total_amount,
                'platform_commission' => $booking->platform_commission,
                'performer_payout'    => $booking->performer_payout,
            )
        );
    }

    /**
     * Track booking cancellation.
     *
     * @param int    $booking_id Booking ID.
     * @param object $booking    Booking object.
     */
    public function track_booking_cancelled( $booking_id, $booking ) {
        $this->fire_event(
            'booking_cancelled',
            array(
                'booking_id'   => $booking_id,
                'performer_id' => $booking->performer_id,
                'customer_id'  => $booking->customer_id,
                'total_amount' => $booking->total_amount,
            )
        );
    }

    /**
     * Track payment received.
     *
     * @param int  $booking_id Booking ID.
     * @param int  $order_id   WooCommerce order ID.
     * @param bool $is_deposit Whether this is a deposit.
     */
    public function track_payment_received( $booking_id, $order_id, $is_deposit ) {
        $order = wc_get_order( $order_id );

        $this->fire_event(
            'payment_received',
            array(
                'booking_id' => $booking_id,
                'order_id'   => $order_id,
                'amount'     => $order ? $order->get_total() : 0,
                'is_deposit' => $is_deposit,
            )
        );
    }

    /**
     * Track escrow release.
     *
     * @param int   $booking_id Booking ID.
     * @param float $amount     Amount released.
     */
    public function track_escrow_released( $booking_id, $amount ) {
        $this->fire_event(
            'escrow_released',
            array(
                'booking_id' => $booking_id,
                'amount'     => $amount,
            )
        );
    }

    /**
     * Track market event creation.
     *
     * @param int   $event_id Event post ID.
     * @param array $data     Event data.
     */
    public function track_market_event_created( $event_id, $data ) {
        $this->fire_event(
            'market_event_created',
            array(
                'event_id'    => $event_id,
                'customer_id' => $data['customer_id'],
                'budget_min'  => $data['budget_min'] ?? 0,
                'budget_max'  => $data['budget_max'] ?? 0,
            )
        );
    }

    /**
     * Track bid submission.
     *
     * @param int $bid_id       Bid ID.
     * @param int $event_id     Event ID.
     * @param int $performer_id Performer ID.
     */
    public function track_bid_submitted( $bid_id, $event_id, $performer_id ) {
        $bid = Peanut_Booker_Database::get_row( 'bids', array( 'id' => $bid_id ) );

        $this->fire_event(
            'bid_submitted',
            array(
                'bid_id'       => $bid_id,
                'event_id'     => $event_id,
                'performer_id' => $performer_id,
                'bid_amount'   => $bid ? $bid->bid_amount : 0,
            )
        );
    }

    /**
     * Track bid acceptance.
     *
     * @param int $bid_id     Bid ID.
     * @param int $booking_id Resulting booking ID.
     */
    public function track_bid_accepted( $bid_id, $booking_id ) {
        $this->fire_event(
            'bid_accepted',
            array(
                'bid_id'     => $bid_id,
                'booking_id' => $booking_id,
            )
        );
    }

    /**
     * Track review submission.
     *
     * @param int   $review_id Review ID.
     * @param array $data      Review data.
     */
    public function track_review_submitted( $review_id, $data ) {
        $this->fire_event(
            'review_submitted',
            array(
                'review_id'   => $review_id,
                'reviewer_id' => $data['reviewer_id'],
                'reviewee_id' => $data['reviewee_id'],
                'rating'      => $data['rating'],
            )
        );
    }

    /**
     * Track subscription activation.
     *
     * @param int    $user_id      User ID.
     * @param object $subscription WC Subscription.
     */
    public function track_subscription_activated( $user_id, $subscription ) {
        $this->fire_event(
            'subscription_activated',
            array(
                'user_id'         => $user_id,
                'subscription_id' => $subscription->get_id(),
                'amount'          => $subscription->get_total(),
            )
        );
    }

    /**
     * Track subscription cancellation.
     *
     * @param int    $user_id      User ID.
     * @param object $subscription WC Subscription.
     */
    public function track_subscription_cancelled( $user_id, $subscription ) {
        $this->fire_event(
            'subscription_cancelled',
            array(
                'user_id'         => $user_id,
                'subscription_id' => $subscription->get_id(),
            )
        );
    }

    // Analytics endpoints.

    /**
     * Get analytics summary.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_analytics_summary( $request ) {
        global $wpdb;

        $period = $request->get_param( 'period' ) ?: '30days';
        $date_from = $this->get_period_start( $period );

        $bookings_table = $wpdb->prefix . 'pb_bookings';
        $performers_table = $wpdb->prefix . 'pb_performers';

        // Total performers.
        $total_performers = Peanut_Booker_Database::count( 'performers' );
        $pro_performers = Peanut_Booker_Database::count( 'performers', array( 'tier' => 'pro' ) );

        // Bookings stats.
        $total_bookings = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $bookings_table WHERE created_at >= %s",
                $date_from
            )
        );

        $completed_bookings = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $bookings_table WHERE booking_status = 'completed' AND created_at >= %s",
                $date_from
            )
        );

        // Revenue.
        $total_revenue = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(total_amount) FROM $bookings_table WHERE booking_status = 'completed' AND created_at >= %s",
                $date_from
            )
        );

        $platform_commission = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(platform_commission) FROM $bookings_table WHERE booking_status = 'completed' AND created_at >= %s",
                $date_from
            )
        );

        return rest_ensure_response(
            array(
                'period'              => $period,
                'total_performers'    => (int) $total_performers,
                'pro_performers'      => (int) $pro_performers,
                'total_bookings'      => (int) $total_bookings,
                'completed_bookings'  => (int) $completed_bookings,
                'total_revenue'       => floatval( $total_revenue ),
                'platform_commission' => floatval( $platform_commission ),
            )
        );
    }

    /**
     * Get booking analytics.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_booking_analytics( $request ) {
        global $wpdb;

        $period = $request->get_param( 'period' ) ?: '30days';
        $date_from = $this->get_period_start( $period );

        $table = $wpdb->prefix . 'pb_bookings';

        // Bookings by status.
        $by_status = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT booking_status, COUNT(*) as count
                FROM $table
                WHERE created_at >= %s
                GROUP BY booking_status",
                $date_from
            )
        );

        // Bookings by day.
        $by_day = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DATE(created_at) as date, COUNT(*) as count
                FROM $table
                WHERE created_at >= %s
                GROUP BY DATE(created_at)
                ORDER BY date ASC",
                $date_from
            )
        );

        return rest_ensure_response(
            array(
                'period'    => $period,
                'by_status' => $by_status,
                'by_day'    => $by_day,
            )
        );
    }

    /**
     * Get revenue analytics.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_revenue_analytics( $request ) {
        global $wpdb;

        $period = $request->get_param( 'period' ) ?: '30days';
        $date_from = $this->get_period_start( $period );

        $table = $wpdb->prefix . 'pb_bookings';

        // Revenue by day.
        $by_day = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DATE(created_at) as date,
                        SUM(total_amount) as revenue,
                        SUM(platform_commission) as commission
                FROM $table
                WHERE booking_status = 'completed'
                AND created_at >= %s
                GROUP BY DATE(created_at)
                ORDER BY date ASC",
                $date_from
            )
        );

        return rest_ensure_response(
            array(
                'period' => $period,
                'by_day' => $by_day,
            )
        );
    }

    /**
     * Get performer analytics.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_performer_analytics( $request ) {
        global $wpdb;

        $performers_table = $wpdb->prefix . 'pb_performers';

        // By tier.
        $by_tier = $wpdb->get_results(
            "SELECT tier, COUNT(*) as count FROM $performers_table GROUP BY tier"
        );

        // By achievement level.
        $by_level = $wpdb->get_results(
            "SELECT achievement_level, COUNT(*) as count FROM $performers_table GROUP BY achievement_level"
        );

        // Top performers by bookings.
        $top_by_bookings = $wpdb->get_results(
            "SELECT id, user_id, completed_bookings, average_rating
            FROM $performers_table
            ORDER BY completed_bookings DESC
            LIMIT 10"
        );

        return rest_ensure_response(
            array(
                'by_tier'        => $by_tier,
                'by_level'       => $by_level,
                'top_performers' => $top_by_bookings,
            )
        );
    }

    /**
     * Get period start date.
     *
     * @param string $period Period string.
     * @return string Date string.
     */
    private function get_period_start( $period ) {
        switch ( $period ) {
            case '7days':
                return gmdate( 'Y-m-d', strtotime( '-7 days' ) );
            case '90days':
                return gmdate( 'Y-m-d', strtotime( '-90 days' ) );
            case 'year':
                return gmdate( 'Y-m-d', strtotime( '-1 year' ) );
            case '30days':
            default:
                return gmdate( 'Y-m-d', strtotime( '-30 days' ) );
        }
    }
}
