<?php
/**
 * REST API endpoints.
 *
 * @package Peanut_Booker
 * @since   1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * REST API class.
 */
class Peanut_Booker_REST_API {

    /**
     * API namespace.
     *
     * @var string
     */
    private $namespace = 'peanut-booker/v1';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Register REST routes.
     */
    public function register_routes() {
        // Performers.
        register_rest_route(
            $this->namespace,
            '/performers',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_performers' ),
                'permission_callback' => '__return_true',
                'args'                => $this->get_collection_params(),
            )
        );

        register_rest_route(
            $this->namespace,
            '/performers/(?P<id>\d+)',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_performer' ),
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
            $this->namespace,
            '/performers/(?P<id>\d+)/availability',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_performer_availability' ),
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
            $this->namespace,
            '/performers/(?P<id>\d+)/reviews',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_performer_reviews' ),
                'permission_callback' => '__return_true',
            )
        );

        // Market events.
        register_rest_route(
            $this->namespace,
            '/market',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_market_events' ),
                'permission_callback' => '__return_true',
                'args'                => $this->get_collection_params(),
            )
        );

        register_rest_route(
            $this->namespace,
            '/market/(?P<id>\d+)',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_market_event' ),
                'permission_callback' => '__return_true',
            )
        );

        // Bookings (authenticated).
        register_rest_route(
            $this->namespace,
            '/bookings',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_bookings' ),
                'permission_callback' => array( $this, 'check_auth' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/bookings',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'create_booking' ),
                'permission_callback' => array( $this, 'check_auth' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/bookings/(?P<id>\d+)',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_booking' ),
                'permission_callback' => array( $this, 'check_booking_access' ),
            )
        );

        // Categories.
        register_rest_route(
            $this->namespace,
            '/categories',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_categories' ),
                'permission_callback' => '__return_true',
            )
        );

        // Service areas.
        register_rest_route(
            $this->namespace,
            '/service-areas',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_service_areas' ),
                'permission_callback' => '__return_true',
            )
        );

        // Featured performers.
        register_rest_route(
            $this->namespace,
            '/performers/featured',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_featured_performers' ),
                'permission_callback' => '__return_true',
            )
        );
    }

    /**
     * Check if user is authenticated.
     *
     * @return bool|WP_Error
     */
    public function check_auth() {
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'rest_not_logged_in', __( 'You must be logged in.', 'peanut-booker' ), array( 'status' => 401 ) );
        }
        return true;
    }

    /**
     * Check if user has access to booking.
     *
     * @param WP_REST_Request $request Request object.
     * @return bool|WP_Error
     */
    public function check_booking_access( $request ) {
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'rest_not_logged_in', __( 'You must be logged in.', 'peanut-booker' ), array( 'status' => 401 ) );
        }

        $booking = Peanut_Booker_Booking::get( $request['id'] );
        if ( ! $booking ) {
            return new WP_Error( 'rest_not_found', __( 'Booking not found.', 'peanut-booker' ), array( 'status' => 404 ) );
        }

        $user_id   = get_current_user_id();
        $performer = Peanut_Booker_Performer::get_by_user_id( $user_id );

        $is_customer  = (int) $booking->customer_id === $user_id;
        $is_performer = $performer && (int) $performer->id === (int) $booking->performer_id;
        $is_admin     = current_user_can( 'pb_manage_bookings' );

        if ( ! $is_customer && ! $is_performer && ! $is_admin ) {
            return new WP_Error( 'rest_forbidden', __( 'Not authorized.', 'peanut-booker' ), array( 'status' => 403 ) );
        }

        return true;
    }

    /**
     * Get collection parameters.
     *
     * @return array
     */
    private function get_collection_params() {
        return array(
            'page'     => array(
                'default'           => 1,
                'sanitize_callback' => 'absint',
            ),
            'per_page' => array(
                'default'           => 12,
                'sanitize_callback' => 'absint',
            ),
            'category' => array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'service_area' => array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'search'   => array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ),
        );
    }

    /**
     * Get performers.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_performers( $request ) {
        $result = Peanut_Booker_Performer::query(
            array(
                'paged'          => $request['page'],
                'posts_per_page' => $request['per_page'],
                'category'       => $request['category'],
                'service_area'   => $request['service_area'],
                'search'         => $request['search'],
            )
        );

        return rest_ensure_response( $result );
    }

    /**
     * Get single performer.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function get_performer( $request ) {
        $performer = Peanut_Booker_Performer::get( $request['id'] );

        if ( ! $performer || ! $performer->profile_id ) {
            return new WP_Error( 'not_found', __( 'Performer not found.', 'peanut-booker' ), array( 'status' => 404 ) );
        }

        $data = Peanut_Booker_Performer::get_display_data( $performer->profile_id );

        return rest_ensure_response( $data );
    }

    /**
     * Get performer availability.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_performer_availability( $request ) {
        $month = $request->get_param( 'month' ) ?: gmdate( 'Y-m' );

        $calendar = Peanut_Booker_Availability::get_calendar_data( $request['id'], $month );

        return rest_ensure_response(
            array(
                'month'    => $month,
                'calendar' => $calendar,
            )
        );
    }

    /**
     * Get performer reviews.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_performer_reviews( $request ) {
        $page     = $request->get_param( 'page' ) ?: 1;
        $per_page = $request->get_param( 'per_page' ) ?: 10;
        $offset   = ( $page - 1 ) * $per_page;

        $reviews = Peanut_Booker_Reviews::get_performer_reviews( $request['id'], $per_page, $offset );

        return rest_ensure_response(
            array(
                'reviews' => $reviews,
                'page'    => $page,
            )
        );
    }

    /**
     * Get market events.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_market_events( $request ) {
        $result = Peanut_Booker_Market::query(
            array(
                'paged'          => $request['page'],
                'posts_per_page' => $request['per_page'],
                'category'       => $request['category'],
                'service_area'   => $request['service_area'],
                'search'         => $request['search'],
            )
        );

        return rest_ensure_response( $result );
    }

    /**
     * Get single market event.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function get_market_event( $request ) {
        $event = Peanut_Booker_Market::get_event_data( $request['id'] );

        if ( empty( $event ) ) {
            return new WP_Error( 'not_found', __( 'Event not found.', 'peanut-booker' ), array( 'status' => 404 ) );
        }

        return rest_ensure_response( $event );
    }

    /**
     * Get user's bookings.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_bookings( $request ) {
        $user_id   = get_current_user_id();
        $performer = Peanut_Booker_Performer::get_by_user_id( $user_id );

        $bookings = array();

        if ( $performer ) {
            // Get performer's bookings.
            $results = Peanut_Booker_Database::get_results(
                'bookings',
                array( 'performer_id' => $performer->id ),
                'event_date',
                'DESC',
                20
            );
            $bookings = array_map( array( 'Peanut_Booker_Booking', 'format_booking_data' ), $results );
        } elseif ( Peanut_Booker_Roles::is_customer( $user_id ) ) {
            // Get customer's bookings.
            $results = Peanut_Booker_Database::get_results(
                'bookings',
                array( 'customer_id' => $user_id ),
                'event_date',
                'DESC',
                20
            );
            $bookings = array_map( array( 'Peanut_Booker_Booking', 'format_booking_data' ), $results );
        }

        return rest_ensure_response( array( 'bookings' => $bookings ) );
    }

    /**
     * Create booking.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function create_booking( $request ) {
        $data = array(
            'performer_id'      => $request['performer_id'],
            'customer_id'       => get_current_user_id(),
            'event_title'       => $request['event_title'],
            'event_description' => $request['event_description'],
            'event_date'        => $request['event_date'],
            'event_start_time'  => $request['event_start_time'],
            'event_end_time'    => $request['event_end_time'],
            'event_location'    => $request['event_location'],
            'event_address'     => $request['event_address'],
            'event_city'        => $request['event_city'],
            'event_state'       => $request['event_state'],
            'event_zip'         => $request['event_zip'],
            'total_amount'      => $request['total_amount'],
            'notes'             => $request['notes'],
        );

        $result = Peanut_Booker_Booking::create( $data );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        $booking = Peanut_Booker_Booking::get( $result );

        return rest_ensure_response(
            array(
                'booking_id'     => $result,
                'booking_number' => $booking->booking_number,
                'checkout_url'   => Peanut_Booker_Booking::get_checkout_url( $result ),
            )
        );
    }

    /**
     * Get single booking.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function get_booking( $request ) {
        $booking = Peanut_Booker_Booking::get( $request['id'] );

        if ( ! $booking ) {
            return new WP_Error( 'not_found', __( 'Booking not found.', 'peanut-booker' ), array( 'status' => 404 ) );
        }

        return rest_ensure_response( Peanut_Booker_Booking::format_booking_data( $booking ) );
    }

    /**
     * Get categories.
     *
     * @return WP_REST_Response
     */
    public function get_categories() {
        $terms = get_terms(
            array(
                'taxonomy'   => 'pb_performer_category',
                'hide_empty' => false,
            )
        );

        $categories = array();
        foreach ( $terms as $term ) {
            $categories[] = array(
                'id'    => $term->term_id,
                'name'  => $term->name,
                'slug'  => $term->slug,
                'count' => $term->count,
            );
        }

        return rest_ensure_response( $categories );
    }

    /**
     * Get service areas.
     *
     * @return WP_REST_Response
     */
    public function get_service_areas() {
        $terms = get_terms(
            array(
                'taxonomy'   => 'pb_service_area',
                'hide_empty' => false,
            )
        );

        $areas = array();
        foreach ( $terms as $term ) {
            $areas[] = array(
                'id'    => $term->term_id,
                'name'  => $term->name,
                'slug'  => $term->slug,
                'count' => $term->count,
            );
        }

        return rest_ensure_response( $areas );
    }

    /**
     * Get featured performers.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_featured_performers( $request ) {
        $limit      = $request->get_param( 'limit' ) ?: 4;
        $performers = Peanut_Booker_Performer::get_featured( $limit );

        return rest_ensure_response( $performers );
    }
}
