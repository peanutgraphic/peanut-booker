<?php
/**
 * Customer functionality.
 *
 * @package Peanut_Booker
 * @since   1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Customer functionality class.
 */
class Peanut_Booker_Customer {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'wp_ajax_pb_update_customer_profile', array( $this, 'ajax_update_profile' ) );
    }

    /**
     * Get customer data by user ID.
     *
     * @param int $user_id User ID.
     * @return array|null Customer data or null.
     */
    public static function get( $user_id ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return null;
        }

        // Get customer stats.
        $bookings_completed = Peanut_Booker_Database::count(
            'bookings',
            array(
                'customer_id'    => $user_id,
                'booking_status' => 'completed',
            )
        );

        $total_bookings = Peanut_Booker_Database::count(
            'bookings',
            array( 'customer_id' => $user_id )
        );

        // Calculate average rating given to this customer by performers.
        global $wpdb;
        $reviews_table = $wpdb->prefix . 'pb_reviews';
        $avg_rating    = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT AVG(rating) FROM $reviews_table
                WHERE reviewee_id = %d AND reviewer_type = 'performer' AND is_visible = 1",
                $user_id
            )
        );

        $total_reviews = Peanut_Booker_Database::count(
            'reviews',
            array(
                'reviewee_id'   => $user_id,
                'reviewer_type' => 'performer',
                'is_visible'    => 1,
            )
        );

        return array(
            'user_id'            => $user_id,
            'display_name'       => $user->display_name,
            'email'              => $user->user_email,
            'first_name'         => get_user_meta( $user_id, 'first_name', true ),
            'last_name'          => get_user_meta( $user_id, 'last_name', true ),
            'phone'              => get_user_meta( $user_id, 'pb_phone', true ),
            'company'            => get_user_meta( $user_id, 'pb_company', true ),
            'address'            => get_user_meta( $user_id, 'pb_address', true ),
            'city'               => get_user_meta( $user_id, 'pb_city', true ),
            'state'              => get_user_meta( $user_id, 'pb_state', true ),
            'zip'                => get_user_meta( $user_id, 'pb_zip', true ),
            'avatar_url'         => get_avatar_url( $user_id, array( 'size' => 150 ) ),
            'member_since'       => $user->user_registered,
            'total_bookings'     => $total_bookings,
            'completed_bookings' => $bookings_completed,
            'average_rating'     => $avg_rating ? round( floatval( $avg_rating ), 2 ) : null,
            'total_reviews'      => $total_reviews,
        );
    }

    /**
     * Get customer's upcoming bookings.
     *
     * @param int $user_id User ID.
     * @param int $limit   Number of bookings to return.
     * @return array Array of bookings.
     */
    public static function get_upcoming_bookings( $user_id, $limit = 5 ) {
        global $wpdb;

        $table = $wpdb->prefix . 'pb_bookings';
        $today = current_time( 'Y-m-d' );

        $bookings = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table
                WHERE customer_id = %d
                AND event_date >= %s
                AND booking_status NOT IN ('cancelled', 'completed')
                ORDER BY event_date ASC
                LIMIT %d",
                $user_id,
                $today,
                $limit
            )
        );

        return array_map( array( 'Peanut_Booker_Booking', 'format_booking_data' ), $bookings );
    }

    /**
     * Get customer's past bookings.
     *
     * @param int $user_id User ID.
     * @param int $limit   Number of bookings to return.
     * @param int $offset  Offset for pagination.
     * @return array Array of bookings.
     */
    public static function get_past_bookings( $user_id, $limit = 10, $offset = 0 ) {
        global $wpdb;

        $table = $wpdb->prefix . 'pb_bookings';
        $today = current_time( 'Y-m-d' );

        $bookings = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table
                WHERE customer_id = %d
                AND (event_date < %s OR booking_status IN ('cancelled', 'completed'))
                ORDER BY event_date DESC
                LIMIT %d OFFSET %d",
                $user_id,
                $today,
                $limit,
                $offset
            )
        );

        return array_map( array( 'Peanut_Booker_Booking', 'format_booking_data' ), $bookings );
    }

    /**
     * Get customer's market events.
     *
     * @param int    $user_id User ID.
     * @param string $status  Event status filter.
     * @param int    $limit   Number of events to return.
     * @return array Array of events.
     */
    public static function get_market_events( $user_id, $status = '', $limit = 10 ) {
        $args = array(
            'post_type'      => 'pb_market_event',
            'posts_per_page' => $limit,
            'author'         => $user_id,
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        if ( ! empty( $status ) ) {
            $args['meta_query'] = array(
                array(
                    'key'   => 'pb_event_status',
                    'value' => $status,
                ),
            );
        }

        $query  = new WP_Query( $args );
        $events = array();

        while ( $query->have_posts() ) {
            $query->the_post();
            $events[] = Peanut_Booker_Market::get_event_data( get_the_ID() );
        }
        wp_reset_postdata();

        return $events;
    }

    /**
     * Get reviews left by customer.
     *
     * @param int $user_id User ID.
     * @param int $limit   Number of reviews to return.
     * @return array Array of reviews.
     */
    public static function get_reviews_given( $user_id, $limit = 10 ) {
        return Peanut_Booker_Database::get_results(
            'reviews',
            array( 'reviewer_id' => $user_id ),
            'created_at',
            'DESC',
            $limit
        );
    }

    /**
     * Get reviews about customer (from performers).
     *
     * @param int $user_id User ID.
     * @param int $limit   Number of reviews to return.
     * @return array Array of reviews.
     */
    public static function get_reviews_received( $user_id, $limit = 10 ) {
        return Peanut_Booker_Database::get_results(
            'reviews',
            array(
                'reviewee_id'   => $user_id,
                'reviewer_type' => 'performer',
                'is_visible'    => 1,
            ),
            'created_at',
            'DESC',
            $limit
        );
    }

    /**
     * Check if customer can leave a review for a booking.
     *
     * @param int $user_id    User ID.
     * @param int $booking_id Booking ID.
     * @return bool
     */
    public static function can_review_booking( $user_id, $booking_id ) {
        $booking = Peanut_Booker_Booking::get( $booking_id );

        if ( ! $booking ) {
            return false;
        }

        // Must be the customer of this booking.
        if ( (int) $booking->customer_id !== $user_id ) {
            return false;
        }

        // Booking must be completed.
        if ( 'completed' !== $booking->booking_status ) {
            return false;
        }

        // Check if already reviewed.
        $existing = Peanut_Booker_Database::get_row(
            'reviews',
            array(
                'booking_id'  => $booking_id,
                'reviewer_id' => $user_id,
            )
        );

        return ! $existing;
    }

    /**
     * Get customer's star rating display.
     *
     * @param int $user_id User ID.
     * @return string HTML rating display.
     */
    public static function get_rating_display( $user_id ) {
        $customer = self::get( $user_id );

        if ( ! $customer || ! $customer['average_rating'] ) {
            return '<span class="pb-no-rating">' . esc_html__( 'No ratings yet', 'peanut-booker' ) . '</span>';
        }

        return Peanut_Booker_Reviews::render_stars( $customer['average_rating'], $customer['total_reviews'] );
    }

    /**
     * AJAX handler for updating customer profile.
     */
    public function ajax_update_profile() {
        check_ajax_referer( 'pb_customer_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'peanut-booker' ) ) );
        }

        $user_id = get_current_user_id();

        if ( ! Peanut_Booker_Roles::is_customer( $user_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Not authorized.', 'peanut-booker' ) ) );
        }

        // Update user data.
        $user_data = array( 'ID' => $user_id );

        if ( isset( $_POST['display_name'] ) ) {
            $user_data['display_name'] = sanitize_text_field( $_POST['display_name'] );
        }

        if ( isset( $_POST['first_name'] ) ) {
            update_user_meta( $user_id, 'first_name', sanitize_text_field( $_POST['first_name'] ) );
        }

        if ( isset( $_POST['last_name'] ) ) {
            update_user_meta( $user_id, 'last_name', sanitize_text_field( $_POST['last_name'] ) );
        }

        if ( count( $user_data ) > 1 ) {
            wp_update_user( $user_data );
        }

        // Update custom meta.
        $meta_fields = array( 'phone', 'company', 'address', 'city', 'state', 'zip' );

        foreach ( $meta_fields as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                update_user_meta( $user_id, 'pb_' . $field, sanitize_text_field( $_POST[ $field ] ) );
            }
        }

        do_action( 'peanut_booker_customer_updated', $user_id );

        wp_send_json_success( array( 'message' => __( 'Profile updated successfully.', 'peanut-booker' ) ) );
    }

    /**
     * Get dashboard stats for customer.
     *
     * @param int $user_id User ID.
     * @return array Dashboard statistics.
     */
    public static function get_dashboard_stats( $user_id ) {
        $customer = self::get( $user_id );

        // Get pending bookings count.
        $pending_bookings = Peanut_Booker_Database::count(
            'bookings',
            array(
                'customer_id'    => $user_id,
                'booking_status' => 'pending',
            )
        );

        // Get confirmed upcoming bookings.
        global $wpdb;
        $table = $wpdb->prefix . 'pb_bookings';
        $today = current_time( 'Y-m-d' );

        $upcoming_confirmed = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table
                WHERE customer_id = %d
                AND event_date >= %s
                AND booking_status = 'confirmed'",
                $user_id,
                $today
            )
        );

        // Get open market events.
        $open_events = Peanut_Booker_Database::count(
            'events',
            array(
                'customer_id' => $user_id,
                'status'      => 'open',
            )
        );

        // Get total bids on customer's events.
        $events_table = $wpdb->prefix . 'pb_events';
        $total_bids   = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(total_bids) FROM $events_table WHERE customer_id = %d AND status = 'open'",
                $user_id
            )
        );

        // Get unread bid notifications.
        $bids_table = $wpdb->prefix . 'pb_bids';
        $unread_bids = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $bids_table b
                INNER JOIN $events_table e ON b.event_id = e.id
                WHERE e.customer_id = %d AND b.is_read = 0",
                $user_id
            )
        );

        return array(
            'total_bookings'     => $customer['total_bookings'],
            'completed_bookings' => $customer['completed_bookings'],
            'pending_bookings'   => $pending_bookings,
            'upcoming_confirmed' => (int) $upcoming_confirmed,
            'average_rating'     => $customer['average_rating'],
            'total_reviews'      => $customer['total_reviews'],
            'open_events'        => $open_events,
            'total_bids'         => (int) $total_bids,
            'unread_bids'        => (int) $unread_bids,
        );
    }
}
