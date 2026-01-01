<?php
/**
 * Reviews and ratings functionality.
 *
 * @package Peanut_Booker
 * @since   1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Reviews and ratings class.
 */
class Peanut_Booker_Reviews {

    /**
     * Arbitration statuses.
     */
    const ARBITRATION_PENDING  = 'pending';
    const ARBITRATION_REVIEWED = 'reviewed';
    const ARBITRATION_UPHELD   = 'upheld';
    const ARBITRATION_REMOVED  = 'removed';
    const ARBITRATION_EDITED   = 'edited';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'wp_ajax_pb_submit_review', array( $this, 'ajax_submit_review' ) );
        add_action( 'wp_ajax_pb_respond_review', array( $this, 'ajax_respond_review' ) );
        add_action( 'wp_ajax_pb_flag_review', array( $this, 'ajax_flag_review' ) );
        add_action( 'wp_ajax_pb_arbitrate_review', array( $this, 'ajax_arbitrate_review' ) );
    }

    /**
     * Submit a review.
     *
     * @param array $data Review data.
     * @return int|WP_Error Review ID or error.
     */
    public static function submit( $data ) {
        $required = array( 'booking_id', 'reviewer_id', 'reviewee_id', 'rating' );

        foreach ( $required as $field ) {
            if ( empty( $data[ $field ] ) ) {
                return new WP_Error( 'missing_field', sprintf( __( 'Missing required field: %s', 'peanut-booker' ), $field ) );
            }
        }

        // Validate rating.
        $rating = absint( $data['rating'] );
        if ( $rating < 1 || $rating > 5 ) {
            return new WP_Error( 'invalid_rating', __( 'Rating must be between 1 and 5.', 'peanut-booker' ) );
        }

        // Validate booking.
        $booking = Peanut_Booker_Booking::get( $data['booking_id'] );
        if ( ! $booking ) {
            return new WP_Error( 'invalid_booking', __( 'Booking not found.', 'peanut-booker' ) );
        }

        // Determine reviewer type.
        $reviewer_type = 'customer';
        $performer     = Peanut_Booker_Performer::get_by_user_id( $data['reviewer_id'] );
        if ( $performer && (int) $performer->id === (int) $booking->performer_id ) {
            $reviewer_type = 'performer';
        }

        // Check if already reviewed.
        $existing = Peanut_Booker_Database::get_row(
            'reviews',
            array(
                'booking_id'  => $data['booking_id'],
                'reviewer_id' => $data['reviewer_id'],
            )
        );

        if ( $existing ) {
            return new WP_Error( 'already_reviewed', __( 'You have already reviewed this booking.', 'peanut-booker' ) );
        }

        // Insert review.
        $review_data = array(
            'booking_id'    => absint( $data['booking_id'] ),
            'reviewer_id'   => absint( $data['reviewer_id'] ),
            'reviewee_id'   => absint( $data['reviewee_id'] ),
            'reviewer_type' => $reviewer_type,
            'rating'        => $rating,
            'title'         => sanitize_text_field( $data['title'] ?? '' ),
            'content'       => sanitize_textarea_field( $data['content'] ?? '' ),
            'is_visible'    => 1,
        );

        $review_id = Peanut_Booker_Database::insert( 'reviews', $review_data );

        if ( ! $review_id ) {
            return new WP_Error( 'insert_failed', __( 'Failed to submit review.', 'peanut-booker' ) );
        }

        // Update performer stats if reviewing a performer.
        if ( 'customer' === $reviewer_type ) {
            self::update_performer_rating( $booking->performer_id );
        }

        // Send notification.
        Peanut_Booker_Notifications::send( 'new_review', $review_id );

        do_action( 'peanut_booker_review_submitted', $review_id, $data );

        return $review_id;
    }

    /**
     * Update performer's average rating.
     *
     * @param int $performer_id Performer ID.
     */
    public static function update_performer_rating( $performer_id ) {
        global $wpdb;

        $table = $wpdb->prefix . 'pb_reviews';
        $performer = Peanut_Booker_Performer::get( $performer_id );

        if ( ! $performer ) {
            return;
        }

        // Calculate average rating from customer reviews.
        $stats = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT AVG(rating) as avg_rating, COUNT(*) as total
                FROM $table
                WHERE reviewee_id = %d
                AND reviewer_type = 'customer'
                AND is_visible = 1",
                $performer->user_id
            )
        );

        Peanut_Booker_Performer::update(
            $performer_id,
            array(
                'average_rating' => $stats->avg_rating ? round( $stats->avg_rating, 2 ) : null,
                'total_reviews'  => (int) $stats->total,
            )
        );

        // Recalculate achievement score.
        Peanut_Booker_Performer::calculate_achievement_score( $performer_id );
    }

    /**
     * Get review by ID.
     *
     * @param int $review_id Review ID.
     * @return object|null Review object.
     */
    public static function get( $review_id ) {
        return Peanut_Booker_Database::get_row( 'reviews', array( 'id' => $review_id ) );
    }

    /**
     * Get reviews for a performer.
     *
     * @param int $performer_id Performer ID (from performers table).
     * @param int $limit        Number of reviews.
     * @param int $offset       Offset for pagination.
     * @return array Array of reviews.
     */
    public static function get_performer_reviews( $performer_id, $limit = 10, $offset = 0 ) {
        $performer = Peanut_Booker_Performer::get( $performer_id );
        if ( ! $performer ) {
            return array();
        }

        global $wpdb;
        $table = $wpdb->prefix . 'pb_reviews';

        $reviews = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table
                WHERE reviewee_id = %d
                AND reviewer_type = 'customer'
                AND is_visible = 1
                ORDER BY created_at DESC
                LIMIT %d OFFSET %d",
                $performer->user_id,
                $limit,
                $offset
            )
        );

        return array_map( array( __CLASS__, 'format_review_data' ), $reviews );
    }

    /**
     * Get reviews by a user.
     *
     * @param int $user_id User ID.
     * @param int $limit   Number of reviews.
     * @return array Array of reviews.
     */
    public static function get_user_reviews( $user_id, $limit = 10 ) {
        global $wpdb;
        $table = $wpdb->prefix . 'pb_reviews';

        $reviews = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table
                WHERE reviewee_id = %d
                AND is_visible = 1
                ORDER BY created_at DESC
                LIMIT %d",
                $user_id,
                $limit
            )
        );

        return array_map( array( __CLASS__, 'format_review_data' ), $reviews );
    }

    /**
     * Get reviews left by a customer.
     *
     * @param int $customer_id Customer user ID.
     * @param int $limit       Number of reviews.
     * @return array Array of reviews.
     */
    public static function get_customer_reviews( $customer_id, $limit = 10 ) {
        global $wpdb;
        $table = $wpdb->prefix . 'pb_reviews';

        $reviews = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table
                WHERE reviewer_id = %d
                AND reviewer_type = 'customer'
                ORDER BY created_at DESC
                LIMIT %d",
                $customer_id,
                $limit
            )
        );

        return array_map( array( __CLASS__, 'format_review_data' ), $reviews );
    }

    /**
     * Check if customer has reviewed a booking.
     *
     * @param int $customer_id Customer user ID.
     * @param int $booking_id  Booking ID.
     * @return bool True if reviewed, false otherwise.
     */
    public static function customer_has_reviewed( $customer_id, $booking_id ) {
        $existing = Peanut_Booker_Database::get_row(
            'reviews',
            array(
                'booking_id'  => $booking_id,
                'reviewer_id' => $customer_id,
            )
        );

        return ! empty( $existing );
    }

    /**
     * Format review data.
     *
     * @param object $review Review object.
     * @return array Formatted review data.
     */
    public static function format_review_data( $review ) {
        if ( ! $review ) {
            return array();
        }

        $reviewer = get_userdata( $review->reviewer_id );
        $reviewee = get_userdata( $review->reviewee_id );
        $booking  = Peanut_Booker_Booking::get( $review->booking_id );

        return array(
            'id'               => $review->id,
            'booking_id'       => $review->booking_id,
            'reviewer_id'      => $review->reviewer_id,
            'reviewer_name'    => $reviewer ? $reviewer->display_name : __( 'Unknown', 'peanut-booker' ),
            'reviewer_avatar'  => get_avatar_url( $review->reviewer_id, array( 'size' => 50 ) ),
            'reviewer_type'    => $review->reviewer_type,
            'reviewee_id'      => $review->reviewee_id,
            'reviewee_name'    => $reviewee ? $reviewee->display_name : __( 'Unknown', 'peanut-booker' ),
            'rating'           => (int) $review->rating,
            'title'            => $review->title,
            'content'          => $review->content,
            'response'         => $review->response,
            'response_date'    => $review->response_date,
            'is_flagged'       => (bool) $review->is_flagged,
            'arbitration_status' => $review->arbitration_status,
            'created_at'       => $review->created_at,
            'date_formatted'   => date_i18n( get_option( 'date_format' ), strtotime( $review->created_at ) ),
            'event_title'      => $booking ? $booking->event_title : '',
            'event_date'       => $booking ? $booking->event_date : '',
        );
    }

    /**
     * Add response to a review.
     *
     * @param int    $review_id Review ID.
     * @param int    $user_id   User ID responding.
     * @param string $response  Response content.
     * @return bool|WP_Error Success or error.
     */
    public static function respond( $review_id, $user_id, $response ) {
        $review = self::get( $review_id );
        if ( ! $review ) {
            return new WP_Error( 'not_found', __( 'Review not found.', 'peanut-booker' ) );
        }

        // Only the reviewee can respond.
        if ( (int) $review->reviewee_id !== $user_id ) {
            return new WP_Error( 'not_authorized', __( 'Not authorized to respond to this review.', 'peanut-booker' ) );
        }

        // Can't respond if already responded.
        if ( ! empty( $review->response ) ) {
            return new WP_Error( 'already_responded', __( 'You have already responded to this review.', 'peanut-booker' ) );
        }

        Peanut_Booker_Database::update(
            'reviews',
            array(
                'response'      => sanitize_textarea_field( $response ),
                'response_date' => current_time( 'mysql' ),
            ),
            array( 'id' => $review_id )
        );

        do_action( 'peanut_booker_review_responded', $review_id, $response );

        return true;
    }

    /**
     * Flag a review for admin arbitration.
     *
     * @param int    $review_id Review ID.
     * @param int    $user_id   User flagging.
     * @param string $reason    Reason for flagging.
     * @return bool|WP_Error Success or error.
     */
    public static function flag( $review_id, $user_id, $reason ) {
        $review = self::get( $review_id );
        if ( ! $review ) {
            return new WP_Error( 'not_found', __( 'Review not found.', 'peanut-booker' ) );
        }

        // Only reviewer or reviewee can flag.
        if ( (int) $review->reviewer_id !== $user_id && (int) $review->reviewee_id !== $user_id ) {
            return new WP_Error( 'not_authorized', __( 'Not authorized.', 'peanut-booker' ) );
        }

        if ( $review->is_flagged ) {
            return new WP_Error( 'already_flagged', __( 'This review has already been flagged.', 'peanut-booker' ) );
        }

        Peanut_Booker_Database::update(
            'reviews',
            array(
                'is_flagged'         => 1,
                'flag_reason'        => sanitize_textarea_field( $reason ),
                'flagged_by'         => $user_id,
                'flagged_date'       => current_time( 'mysql' ),
                'arbitration_status' => self::ARBITRATION_PENDING,
            ),
            array( 'id' => $review_id )
        );

        // Notify admin.
        Peanut_Booker_Notifications::send( 'review_flagged', $review_id );

        do_action( 'peanut_booker_review_flagged', $review_id, $reason );

        return true;
    }

    /**
     * Admin arbitration of flagged review.
     *
     * @param int    $review_id Review ID.
     * @param int    $admin_id  Admin user ID.
     * @param string $decision  Decision (upheld, removed, edited).
     * @param string $notes     Admin notes.
     * @param string $edited_content Optional edited content.
     * @return bool|WP_Error Success or error.
     */
    public static function arbitrate( $review_id, $admin_id, $decision, $notes = '', $edited_content = '' ) {
        if ( ! current_user_can( 'pb_arbitrate_reviews' ) ) {
            return new WP_Error( 'not_authorized', __( 'Not authorized.', 'peanut-booker' ) );
        }

        $review = self::get( $review_id );
        if ( ! $review ) {
            return new WP_Error( 'not_found', __( 'Review not found.', 'peanut-booker' ) );
        }

        $update_data = array(
            'arbitration_status' => sanitize_text_field( $decision ),
            'arbitration_notes'  => sanitize_textarea_field( $notes ),
            'arbitrated_by'      => $admin_id,
            'arbitration_date'   => current_time( 'mysql' ),
        );

        switch ( $decision ) {
            case self::ARBITRATION_REMOVED:
                $update_data['is_visible'] = 0;
                break;

            case self::ARBITRATION_EDITED:
                if ( ! empty( $edited_content ) ) {
                    $update_data['content'] = sanitize_textarea_field( $edited_content );
                }
                break;

            case self::ARBITRATION_UPHELD:
            default:
                // Keep review as is.
                break;
        }

        Peanut_Booker_Database::update( 'reviews', $update_data, array( 'id' => $review_id ) );

        // If removed and it was a performer review, update their rating.
        if ( self::ARBITRATION_REMOVED === $decision && 'customer' === $review->reviewer_type ) {
            $booking = Peanut_Booker_Booking::get( $review->booking_id );
            if ( $booking ) {
                self::update_performer_rating( $booking->performer_id );
            }
        }

        // Notify parties.
        Peanut_Booker_Notifications::send( 'review_arbitrated', $review_id );

        do_action( 'peanut_booker_review_arbitrated', $review_id, $decision );

        return true;
    }

    /**
     * Get flagged reviews pending arbitration.
     *
     * @return array Array of reviews.
     */
    public static function get_pending_arbitration() {
        $reviews = Peanut_Booker_Database::get_results(
            'reviews',
            array(
                'is_flagged'         => 1,
                'arbitration_status' => self::ARBITRATION_PENDING,
            ),
            'flagged_date',
            'ASC'
        );

        return array_map( array( __CLASS__, 'format_review_data' ), $reviews );
    }

    /**
     * Render star rating HTML.
     *
     * @param float $rating       Rating value.
     * @param int   $total_reviews Optional review count.
     * @param bool  $show_number  Whether to show the number.
     * @return string HTML.
     */
    public static function render_stars( $rating, $total_reviews = 0, $show_number = true ) {
        $rating    = floatval( $rating );
        $full      = floor( $rating );
        $half      = ( $rating - $full ) >= 0.5 ? 1 : 0;
        $empty     = 5 - $full - $half;

        $html = '<span class="pb-star-rating" title="' . esc_attr( sprintf( __( '%s out of 5 stars', 'peanut-booker' ), number_format( $rating, 1 ) ) ) . '">';

        // Full stars.
        for ( $i = 0; $i < $full; $i++ ) {
            $html .= '<span class="pb-star pb-star-full">★</span>';
        }

        // Half star.
        if ( $half ) {
            $html .= '<span class="pb-star pb-star-half">★</span>';
        }

        // Empty stars.
        for ( $i = 0; $i < $empty; $i++ ) {
            $html .= '<span class="pb-star pb-star-empty">☆</span>';
        }

        if ( $show_number ) {
            $html .= '<span class="pb-rating-number">' . number_format( $rating, 1 ) . '</span>';

            if ( $total_reviews > 0 ) {
                $html .= '<span class="pb-review-count">(' . sprintf(
                    _n( '%d review', '%d reviews', $total_reviews, 'peanut-booker' ),
                    $total_reviews
                ) . ')</span>';
            }
        }

        $html .= '</span>';

        return $html;
    }

    /**
     * Render interactive star input.
     *
     * @param string $name  Input name.
     * @param int    $value Current value.
     * @return string HTML.
     */
    public static function render_star_input( $name, $value = 0 ) {
        $html = '<div class="pb-star-input" data-name="' . esc_attr( $name ) . '">';

        for ( $i = 5; $i >= 1; $i-- ) {
            $checked = ( $i === (int) $value ) ? 'checked' : '';
            $html   .= sprintf(
                '<input type="radio" id="%s_%d" name="%s" value="%d" %s>
                <label for="%s_%d" title="%d stars">★</label>',
                esc_attr( $name ),
                $i,
                esc_attr( $name ),
                $i,
                $checked,
                esc_attr( $name ),
                $i,
                $i
            );
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * AJAX: Submit review.
     */
    public function ajax_submit_review() {
        check_ajax_referer( 'pb_review_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'peanut-booker' ) ) );
        }

        $booking_id = absint( $_POST['booking_id'] ?? 0 );
        $booking    = Peanut_Booker_Booking::get( $booking_id );

        if ( ! $booking ) {
            wp_send_json_error( array( 'message' => __( 'Booking not found.', 'peanut-booker' ) ) );
        }

        $user_id = get_current_user_id();

        // Use capability-based check for review authorization.
        if ( ! Peanut_Booker_Roles::can_review_booking( $booking, $user_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Not authorized to review this booking.', 'peanut-booker' ) ) );
        }

        // Determine reviewee based on user's role in booking.
        if ( Peanut_Booker_Roles::is_booking_performer( $booking, $user_id ) ) {
            // Performer reviewing customer.
            $reviewee_id = $booking->customer_id;
        } elseif ( Peanut_Booker_Roles::is_booking_customer( $booking, $user_id ) ) {
            // Customer reviewing performer.
            $performer_record = Peanut_Booker_Performer::get( $booking->performer_id );
            $reviewee_id      = $performer_record ? $performer_record->user_id : 0;
        } else {
            wp_send_json_error( array( 'message' => __( 'Not authorized.', 'peanut-booker' ) ) );
        }

        $data = array(
            'booking_id'  => $booking_id,
            'reviewer_id' => $user_id,
            'reviewee_id' => $reviewee_id,
            'rating'      => absint( $_POST['rating'] ?? 0 ),
            'title'       => sanitize_text_field( $_POST['title'] ?? '' ),
            'content'     => sanitize_textarea_field( $_POST['content'] ?? '' ),
        );

        $result = self::submit( $data );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array( 'message' => __( 'Review submitted successfully.', 'peanut-booker' ) ) );
    }

    /**
     * AJAX: Respond to review.
     */
    public function ajax_respond_review() {
        check_ajax_referer( 'pb_review_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'peanut-booker' ) ) );
        }

        $result = self::respond(
            absint( $_POST['review_id'] ?? 0 ),
            get_current_user_id(),
            sanitize_textarea_field( $_POST['response'] ?? '' )
        );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array( 'message' => __( 'Response added successfully.', 'peanut-booker' ) ) );
    }

    /**
     * AJAX: Flag review.
     */
    public function ajax_flag_review() {
        check_ajax_referer( 'pb_review_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'peanut-booker' ) ) );
        }

        $result = self::flag(
            absint( $_POST['review_id'] ?? 0 ),
            get_current_user_id(),
            sanitize_textarea_field( $_POST['reason'] ?? '' )
        );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array( 'message' => __( 'Review flagged for admin review.', 'peanut-booker' ) ) );
    }

    /**
     * AJAX: Arbitrate review (admin only).
     */
    public function ajax_arbitrate_review() {
        check_ajax_referer( 'pb_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'pb_arbitrate_reviews' ) ) {
            wp_send_json_error( array( 'message' => __( 'Not authorized.', 'peanut-booker' ) ) );
        }

        $result = self::arbitrate(
            absint( $_POST['review_id'] ?? 0 ),
            get_current_user_id(),
            sanitize_text_field( $_POST['decision'] ?? '' ),
            sanitize_textarea_field( $_POST['notes'] ?? '' ),
            sanitize_textarea_field( $_POST['edited_content'] ?? '' )
        );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array( 'message' => __( 'Arbitration decision saved.', 'peanut-booker' ) ) );
    }
}
