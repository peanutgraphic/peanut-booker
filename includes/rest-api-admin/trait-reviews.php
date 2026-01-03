<?php
/**
 * Review-related REST API admin endpoints.
 *
 * This trait contains all review management endpoints for the admin REST API.
 * It is designed to be used by Peanut_Booker_REST_API_Admin class.
 *
 * Methods included:
 * - Route registration
 * - Listing reviews (get_admin_reviews)
 * - Getting single review (get_admin_review)
 * - Responding to reviews (respond_to_review)
 * - Arbitrating flagged reviews (arbitrate_review)
 * - Toggle visibility (toggle_review_visibility)
 *
 * @package Peanut_Booker
 * @since   2.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Trait for review admin endpoints.
 */
trait Peanut_Booker_REST_Admin_Reviews {

    /**
     * Register review-related routes.
     *
     * Called from main register_routes() method.
     */
    protected function register_review_routes() {
        // Admin Reviews list.
        register_rest_route(
            $this->namespace,
            '/admin/reviews',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_admin_reviews' ),
                'permission_callback' => array( $this, 'check_manage_reviews' ),
                'args'                => $this->get_review_params(),
            )
        );

        // Single review.
        register_rest_route(
            $this->namespace,
            '/admin/reviews/(?P<id>\d+)',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_admin_review' ),
                'permission_callback' => array( $this, 'check_manage_reviews' ),
            )
        );

        // Respond to review.
        register_rest_route(
            $this->namespace,
            '/admin/reviews/(?P<id>\d+)/respond',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'respond_to_review' ),
                'permission_callback' => array( $this, 'check_manage_reviews' ),
            )
        );

        // Arbitrate review.
        register_rest_route(
            $this->namespace,
            '/admin/reviews/(?P<id>\d+)/arbitrate',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'arbitrate_review' ),
                'permission_callback' => array( $this, 'check_manage_reviews' ),
            )
        );

        // Toggle visibility.
        register_rest_route(
            $this->namespace,
            '/admin/reviews/(?P<id>\d+)/visibility',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'toggle_review_visibility' ),
                'permission_callback' => array( $this, 'check_manage_reviews' ),
            )
        );
    }

    /**
     * Get review query parameters.
     *
     * @return array Query parameters.
     */
    protected function get_review_params() {
        return array(
            'page'     => array(
                'default'           => 1,
                'sanitize_callback' => 'absint',
            ),
            'per_page' => array(
                'default'           => 20,
                'sanitize_callback' => 'absint',
            ),
            'search'   => array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'flagged'  => array(
                'default'           => '',
                'sanitize_callback' => 'rest_sanitize_boolean',
            ),
        );
    }

    /**
     * Get admin reviews list.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function get_admin_reviews( $request ) {
        global $wpdb;

        $page     = $request->get_param( 'page' );
        $per_page = $request->get_param( 'per_page' );
        $search   = $request->get_param( 'search' );
        $flagged  = $request->get_param( 'flagged' );
        $offset   = ( $page - 1 ) * $per_page;

        $reviews_table = Peanut_Booker_Database::get_table( 'reviews' );

        $where_clauses = array( '1=1' );
        $where_values  = array();

        if ( ! empty( $search ) ) {
            $where_clauses[] = '(r.title LIKE %s OR r.content LIKE %s)';
            $search_like     = '%' . $wpdb->esc_like( $search ) . '%';
            $where_values[]  = $search_like;
            $where_values[]  = $search_like;
        }

        if ( $flagged ) {
            $where_clauses[] = 'r.is_flagged = 1';
        }

        $where_sql = implode( ' AND ', $where_clauses );

        // Get total count.
        $count_sql = "SELECT COUNT(*) FROM $reviews_table r WHERE $where_sql";
        if ( ! empty( $where_values ) ) {
            $count_sql = $wpdb->prepare( $count_sql, $where_values );
        }
        $total = (int) $wpdb->get_var( $count_sql );

        // Get results.
        $sql = "SELECT r.*,
                       u_reviewer.display_name as reviewer_name,
                       u_reviewee.display_name as reviewee_name
                FROM $reviews_table r
                LEFT JOIN {$wpdb->users} u_reviewer ON r.reviewer_id = u_reviewer.ID
                LEFT JOIN {$wpdb->users} u_reviewee ON r.reviewee_id = u_reviewee.ID
                WHERE $where_sql
                ORDER BY r.created_at DESC
                LIMIT %d OFFSET %d";

        $query_values = array_merge( $where_values, array( $per_page, $offset ) );
        $sql          = $wpdb->prepare( $sql, $query_values );

        $results = $wpdb->get_results( $sql );

        // Format results.
        $reviews = array();
        foreach ( $results as $review ) {
            $reviews[] = $this->format_review( $review );
        }

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => array(
                    'data'        => $reviews,
                    'total'       => $total,
                    'page'        => $page,
                    'per_page'    => $per_page,
                    'total_pages' => ceil( $total / $per_page ),
                ),
            )
        );
    }

    /**
     * Get single admin review.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object or error.
     */
    public function get_admin_review( $request ) {
        global $wpdb;

        $reviews_table = Peanut_Booker_Database::get_table( 'reviews' );

        $sql = $wpdb->prepare(
            "SELECT r.*,
                    u_reviewer.display_name as reviewer_name,
                    u_reviewee.display_name as reviewee_name
             FROM $reviews_table r
             LEFT JOIN {$wpdb->users} u_reviewer ON r.reviewer_id = u_reviewer.ID
             LEFT JOIN {$wpdb->users} u_reviewee ON r.reviewee_id = u_reviewee.ID
             WHERE r.id = %d",
            $request['id']
        );

        $review = $wpdb->get_row( $sql );

        if ( ! $review ) {
            return new WP_Error( 'not_found', 'Review not found.', array( 'status' => 404 ) );
        }

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => $this->format_review( $review ),
            )
        );
    }

    /**
     * Respond to a review.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function respond_to_review( $request ) {
        $id       = $request['id'];
        $response = $request->get_param( 'response' );

        Peanut_Booker_Database::update(
            'reviews',
            array(
                'response'      => sanitize_textarea_field( $response ),
                'response_date' => current_time( 'mysql' ),
                'updated_at'    => current_time( 'mysql' ),
            ),
            array( 'id' => $id )
        );

        return rest_ensure_response(
            array(
                'success' => true,
                'message' => 'Response added.',
            )
        );
    }

    /**
     * Arbitrate a flagged review.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object or error.
     */
    public function arbitrate_review( $request ) {
        $id       = $request['id'];
        $decision = $request->get_param( 'decision' );
        $notes    = $request->get_param( 'notes' );

        $valid_decisions = array( 'keep', 'remove', 'edit' );
        if ( ! in_array( $decision, $valid_decisions, true ) ) {
            return new WP_Error( 'invalid_decision', 'Invalid arbitration decision.', array( 'status' => 400 ) );
        }

        $update_data = array(
            'arbitration_status' => 'resolved',
            'arbitration_notes'  => sanitize_textarea_field( $notes ),
            'arbitrated_by'      => get_current_user_id(),
            'arbitration_date'   => current_time( 'mysql' ),
            'is_flagged'         => 0,
            'updated_at'         => current_time( 'mysql' ),
        );

        if ( 'remove' === $decision ) {
            $update_data['is_visible'] = 0;
        }

        Peanut_Booker_Database::update( 'reviews', $update_data, array( 'id' => $id ) );

        return rest_ensure_response(
            array(
                'success' => true,
                'message' => 'Review arbitrated.',
            )
        );
    }

    /**
     * Toggle review visibility.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function toggle_review_visibility( $request ) {
        $id      = $request['id'];
        $visible = $request->get_param( 'visible' );

        Peanut_Booker_Database::update(
            'reviews',
            array(
                'is_visible' => $visible ? 1 : 0,
                'updated_at' => current_time( 'mysql' ),
            ),
            array( 'id' => $id )
        );

        return rest_ensure_response(
            array(
                'success' => true,
                'message' => $visible ? 'Review visible.' : 'Review hidden.',
            )
        );
    }

    /**
     * Format review for API response.
     *
     * @param object $review Review database row.
     * @return array|null Formatted review data or null.
     */
    private function format_review( $review ) {
        if ( ! $review ) {
            return null;
        }

        return array(
            'id'                 => (int) $review->id,
            'booking_id'         => (int) $review->booking_id,
            'reviewer_id'        => (int) $review->reviewer_id,
            'reviewer_name'      => isset( $review->reviewer_name ) ? $review->reviewer_name : '',
            'reviewer_type'      => $review->reviewer_type,
            'reviewee_id'        => (int) $review->reviewee_id,
            'reviewee_name'      => isset( $review->reviewee_name ) ? $review->reviewee_name : '',
            'rating'             => (int) $review->rating,
            'title'              => $review->title,
            'content'            => $review->content,
            'response'           => $review->response,
            'response_date'      => $review->response_date,
            'is_flagged'         => (bool) $review->is_flagged,
            'flag_reason'        => $review->flag_reason,
            'flagged_by'         => $review->flagged_by ? (int) $review->flagged_by : null,
            'flagged_date'       => $review->flagged_date,
            'arbitration_status' => $review->arbitration_status,
            'arbitration_notes'  => $review->arbitration_notes,
            'arbitrated_by'      => $review->arbitrated_by ? (int) $review->arbitrated_by : null,
            'arbitration_date'   => $review->arbitration_date,
            'is_visible'         => (bool) $review->is_visible,
            'created_at'         => $review->created_at,
            'updated_at'         => $review->updated_at,
        );
    }
}
