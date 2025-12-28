<?php
/**
 * Admin REST API endpoints for React SPA.
 *
 * @package Peanut_Booker
 * @since   2.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Admin REST API class.
 */
class Peanut_Booker_REST_API_Admin {

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
        // Dashboard.
        register_rest_route(
            $this->namespace,
            '/dashboard/stats',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_dashboard_stats' ),
                'permission_callback' => array( $this, 'check_admin_permission' ),
            )
        );

        // Admin Performers.
        register_rest_route(
            $this->namespace,
            '/admin/performers',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_admin_performers' ),
                'permission_callback' => array( $this, 'check_manage_performers' ),
                'args'                => $this->get_collection_params(),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/performers/(?P<id>\d+)',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_admin_performer' ),
                    'permission_callback' => array( $this, 'check_manage_performers' ),
                ),
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'update_performer' ),
                    'permission_callback' => array( $this, 'check_manage_performers' ),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/performers/(?P<id>\d+)/verify',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'verify_performer' ),
                'permission_callback' => array( $this, 'check_manage_performers' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/performers/(?P<id>\d+)/feature',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'feature_performer' ),
                'permission_callback' => array( $this, 'check_manage_performers' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/performers/bulk',
            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array( $this, 'bulk_delete_performers' ),
                'permission_callback' => array( $this, 'check_manage_performers' ),
            )
        );

        // Performer Editor (unified profile + microsite editing).
        register_rest_route(
            $this->namespace,
            '/admin/performers/(?P<id>\d+)/editor',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_performer_editor' ),
                    'permission_callback' => array( $this, 'check_manage_performers' ),
                ),
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'update_performer_editor' ),
                    'permission_callback' => array( $this, 'check_manage_performers' ),
                ),
            )
        );

        // Performer Analytics (by performer ID).
        register_rest_route(
            $this->namespace,
            '/admin/performers/(?P<id>\d+)/analytics',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_performer_analytics' ),
                'permission_callback' => array( $this, 'check_manage_performers' ),
            )
        );

        // Performer External Gigs (by performer ID).
        register_rest_route(
            $this->namespace,
            '/admin/performers/(?P<id>\d+)/external-gigs',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_performer_external_gigs_endpoint' ),
                    'permission_callback' => array( $this, 'check_manage_performers' ),
                ),
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( $this, 'create_performer_external_gig' ),
                    'permission_callback' => array( $this, 'check_manage_performers' ),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/performers/(?P<id>\d+)/external-gigs/(?P<gig_id>\d+)',
            array(
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'update_performer_external_gig' ),
                    'permission_callback' => array( $this, 'check_manage_performers' ),
                ),
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => array( $this, 'delete_performer_external_gig' ),
                    'permission_callback' => array( $this, 'check_manage_performers' ),
                ),
            )
        );

        // Performer's Bookings (for performer dashboard).
        register_rest_route(
            $this->namespace,
            '/admin/performers/(?P<id>\d+)/bookings',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_performer_bookings' ),
                'permission_callback' => array( $this, 'check_manage_performers' ),
                'args'                => array(
                    'id' => array(
                        'required'          => true,
                        'validate_callback' => function( $param ) {
                            return is_numeric( $param );
                        },
                    ),
                    'status' => array(
                        'default' => '',
                    ),
                    'page' => array(
                        'default' => 1,
                    ),
                    'per_page' => array(
                        'default' => 20,
                    ),
                ),
            )
        );

        // Performer's Reviews (for performer dashboard).
        register_rest_route(
            $this->namespace,
            '/admin/performers/(?P<id>\d+)/reviews',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_performer_reviews' ),
                'permission_callback' => array( $this, 'check_manage_performers' ),
                'args'                => array(
                    'id' => array(
                        'required'          => true,
                        'validate_callback' => function( $param ) {
                            return is_numeric( $param );
                        },
                    ),
                    'page' => array(
                        'default' => 1,
                    ),
                    'per_page' => array(
                        'default' => 20,
                    ),
                ),
            )
        );

        // Performer's Availability (for performer dashboard calendar).
        register_rest_route(
            $this->namespace,
            '/admin/performers/(?P<id>\d+)/availability',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_performer_availability' ),
                'permission_callback' => array( $this, 'check_manage_performers' ),
                'args'                => array(
                    'id' => array(
                        'required'          => true,
                        'validate_callback' => function( $param ) {
                            return is_numeric( $param );
                        },
                    ),
                    'start_date' => array(
                        'default' => gmdate( 'Y-m-01' ),
                    ),
                    'end_date' => array(
                        'default' => gmdate( 'Y-m-t', strtotime( '+2 months' ) ),
                    ),
                ),
            )
        );

        // Block dates for performer.
        register_rest_route(
            $this->namespace,
            '/admin/performers/(?P<id>\d+)/availability/block',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'block_performer_dates' ),
                'permission_callback' => array( $this, 'check_manage_performers' ),
                'args'                => array(
                    'id' => array(
                        'required'          => true,
                        'validate_callback' => function( $param ) {
                            return is_numeric( $param );
                        },
                    ),
                ),
            )
        );

        // Update or unblock a date for performer.
        register_rest_route(
            $this->namespace,
            '/admin/performers/(?P<id>\d+)/availability/(?P<slot_id>\d+)',
            array(
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'update_performer_availability_slot' ),
                    'permission_callback' => array( $this, 'check_manage_performers' ),
                    'args'                => array(
                        'id' => array(
                            'required'          => true,
                            'validate_callback' => function( $param ) {
                                return is_numeric( $param );
                            },
                        ),
                        'slot_id' => array(
                            'required'          => true,
                            'validate_callback' => function( $param ) {
                                return is_numeric( $param );
                            },
                        ),
                    ),
                ),
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => array( $this, 'unblock_performer_date' ),
                    'permission_callback' => array( $this, 'check_manage_performers' ),
                    'args'                => array(
                        'id' => array(
                            'required'          => true,
                            'validate_callback' => function( $param ) {
                                return is_numeric( $param );
                            },
                        ),
                        'slot_id' => array(
                            'required'          => true,
                            'validate_callback' => function( $param ) {
                                return is_numeric( $param );
                            },
                        ),
                    ),
                ),
            )
        );

        // Performer's Conversations (for performer dashboard).
        register_rest_route(
            $this->namespace,
            '/admin/performers/(?P<id>\d+)/conversations',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_performer_conversations' ),
                'permission_callback' => array( $this, 'check_manage_performers' ),
                'args'                => array(
                    'id' => array(
                        'required'          => true,
                        'validate_callback' => function( $param ) {
                            return is_numeric( $param );
                        },
                    ),
                    'page' => array(
                        'default' => 1,
                    ),
                    'per_page' => array(
                        'default' => 20,
                    ),
                ),
            )
        );

        // Performer's Messages in a Conversation.
        register_rest_route(
            $this->namespace,
            '/admin/performers/(?P<id>\d+)/conversations/(?P<conversation_id>\d+)',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_performer_conversation_messages' ),
                'permission_callback' => array( $this, 'check_manage_performers' ),
                'args'                => array(
                    'id' => array(
                        'required'          => true,
                        'validate_callback' => function( $param ) {
                            return is_numeric( $param );
                        },
                    ),
                    'conversation_id' => array(
                        'required'          => true,
                        'validate_callback' => function( $param ) {
                            return is_numeric( $param );
                        },
                    ),
                ),
            )
        );

        // Performer's Payouts (for performer dashboard).
        register_rest_route(
            $this->namespace,
            '/admin/performers/(?P<id>\d+)/payouts',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_performer_payouts' ),
                'permission_callback' => array( $this, 'check_manage_performers' ),
                'args'                => array(
                    'id' => array(
                        'required'          => true,
                        'validate_callback' => function( $param ) {
                            return is_numeric( $param );
                        },
                    ),
                    'status' => array(
                        'default' => 'all',
                    ),
                    'page' => array(
                        'default' => 1,
                    ),
                    'per_page' => array(
                        'default' => 20,
                    ),
                ),
            )
        );

        // Performer's Dashboard Overview (for performer dashboard).
        register_rest_route(
            $this->namespace,
            '/admin/performers/(?P<id>\d+)/overview',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_performer_overview' ),
                'permission_callback' => array( $this, 'check_manage_performers' ),
                'args'                => array(
                    'id' => array(
                        'required'          => true,
                        'validate_callback' => function( $param ) {
                            return is_numeric( $param );
                        },
                    ),
                ),
            )
        );

        // Admin Bookings.
        register_rest_route(
            $this->namespace,
            '/admin/bookings',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_admin_bookings' ),
                'permission_callback' => array( $this, 'check_manage_bookings' ),
                'args'                => $this->get_booking_params(),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/bookings/export',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'export_bookings' ),
                'permission_callback' => array( $this, 'check_manage_bookings' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/bookings/(?P<id>\d+)/status',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'update_booking_status' ),
                'permission_callback' => array( $this, 'check_manage_bookings' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/bookings/(?P<id>\d+)/cancel',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'cancel_booking' ),
                'permission_callback' => array( $this, 'check_manage_bookings' ),
            )
        );

        // Admin Market Events.
        register_rest_route(
            $this->namespace,
            '/admin/market',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_admin_market_events' ),
                'permission_callback' => array( $this, 'check_manage_market' ),
                'args'                => $this->get_market_params(),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/market/(?P<id>\d+)/status',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'update_market_status' ),
                'permission_callback' => array( $this, 'check_manage_market' ),
            )
        );

        // Admin Reviews.
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

        register_rest_route(
            $this->namespace,
            '/admin/reviews/(?P<id>\d+)',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_admin_review' ),
                'permission_callback' => array( $this, 'check_manage_reviews' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/reviews/(?P<id>\d+)/respond',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'respond_to_review' ),
                'permission_callback' => array( $this, 'check_manage_reviews' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/reviews/(?P<id>\d+)/arbitrate',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'arbitrate_review' ),
                'permission_callback' => array( $this, 'check_manage_reviews' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/reviews/(?P<id>\d+)/visibility',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'toggle_review_visibility' ),
                'permission_callback' => array( $this, 'check_manage_reviews' ),
            )
        );

        // Payouts.
        register_rest_route(
            $this->namespace,
            '/admin/payouts/stats',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_payout_stats' ),
                'permission_callback' => array( $this, 'check_manage_payouts' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/payouts/pending',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_pending_payouts' ),
                'permission_callback' => array( $this, 'check_manage_payouts' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/payouts/(?P<booking_id>\d+)/release',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'release_payout' ),
                'permission_callback' => array( $this, 'check_manage_payouts' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/payouts/bulk-release',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'bulk_release_payouts' ),
                'permission_callback' => array( $this, 'check_manage_payouts' ),
            )
        );

        // Settings.
        register_rest_route(
            $this->namespace,
            '/admin/settings',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_settings' ),
                    'permission_callback' => array( $this, 'check_manage_settings' ),
                ),
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'update_settings' ),
                    'permission_callback' => array( $this, 'check_manage_settings' ),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/settings/license/activate',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'activate_license' ),
                'permission_callback' => array( $this, 'check_manage_settings' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/settings/license/deactivate',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'deactivate_license' ),
                'permission_callback' => array( $this, 'check_manage_settings' ),
            )
        );

        // Demo Mode.
        register_rest_route(
            $this->namespace,
            '/admin/demo/status',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_demo_status' ),
                'permission_callback' => array( $this, 'check_admin_permission' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/demo/enable',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'enable_demo_mode' ),
                'permission_callback' => array( $this, 'check_admin_permission' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/demo/disable',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'disable_demo_mode' ),
                'permission_callback' => array( $this, 'check_admin_permission' ),
            )
        );

        // Categories.
        register_rest_route(
            $this->namespace,
            '/admin/categories',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_categories' ),
                'permission_callback' => array( $this, 'check_admin_permission' ),
            )
        );

        // Service Areas.
        register_rest_route(
            $this->namespace,
            '/admin/service-areas',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_service_areas' ),
                'permission_callback' => array( $this, 'check_admin_permission' ),
            )
        );

        // Microsites.
        register_rest_route(
            $this->namespace,
            '/admin/microsites',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_admin_microsites' ),
                'permission_callback' => array( $this, 'check_admin_permission' ),
                'args'                => $this->get_microsite_params(),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/microsites/(?P<id>\d+)',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_admin_microsite' ),
                    'permission_callback' => array( $this, 'check_admin_permission' ),
                ),
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'update_microsite' ),
                    'permission_callback' => array( $this, 'check_admin_permission' ),
                ),
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => array( $this, 'delete_microsite' ),
                    'permission_callback' => array( $this, 'check_admin_permission' ),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/microsites/(?P<id>\d+)/status',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'update_microsite_status' ),
                'permission_callback' => array( $this, 'check_admin_permission' ),
            )
        );

        // Messages/Conversations.
        register_rest_route(
            $this->namespace,
            '/admin/messages/conversations',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_admin_conversations' ),
                'permission_callback' => array( $this, 'check_admin_permission' ),
                'args'                => $this->get_pagination_params(),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/messages/conversations/(?P<id>\d+)',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_conversation_messages' ),
                'permission_callback' => array( $this, 'check_admin_permission' ),
            )
        );

        // Customers.
        register_rest_route(
            $this->namespace,
            '/admin/customers',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_admin_customers' ),
                'permission_callback' => array( $this, 'check_admin_permission' ),
                'args'                => $this->get_pagination_params(),
            )
        );

        register_rest_route(
            $this->namespace,
            '/admin/customers/(?P<id>\d+)',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_admin_customer' ),
                'permission_callback' => array( $this, 'check_admin_permission' ),
            )
        );

        // Analytics.
        register_rest_route(
            $this->namespace,
            '/admin/analytics/overview',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_analytics_overview' ),
                'permission_callback' => array( $this, 'check_admin_permission' ),
            )
        );
    }

    /**
     * Permission callbacks.
     */
    public function check_admin_permission() {
        return current_user_can( 'manage_options' );
    }

    public function check_manage_performers() {
        $can_manage = current_user_can( 'pb_manage_performers' ) || current_user_can( 'manage_options' );
        if ( ! $can_manage ) {
            error_log( 'Permission denied for check_manage_performers. User: ' . get_current_user_id() );
        }
        return $can_manage;
    }

    public function check_manage_bookings() {
        return current_user_can( 'pb_manage_bookings' ) || current_user_can( 'manage_options' );
    }

    public function check_manage_market() {
        return current_user_can( 'pb_manage_market' ) || current_user_can( 'manage_options' );
    }

    public function check_manage_reviews() {
        return current_user_can( 'pb_manage_reviews' ) || current_user_can( 'manage_options' );
    }

    public function check_manage_payouts() {
        return current_user_can( 'pb_manage_payouts' ) || current_user_can( 'manage_options' );
    }

    public function check_manage_settings() {
        return current_user_can( 'pb_manage_settings' ) || current_user_can( 'manage_options' );
    }

    /**
     * Get collection parameters.
     */
    private function get_collection_params() {
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
            'tier'     => array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'status'   => array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ),
        );
    }

    private function get_booking_params() {
        return array(
            'page'      => array(
                'default'           => 1,
                'sanitize_callback' => 'absint',
            ),
            'per_page'  => array(
                'default'           => 20,
                'sanitize_callback' => 'absint',
            ),
            'search'    => array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'status'    => array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'date_from' => array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'date_to'   => array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ),
        );
    }

    private function get_market_params() {
        return array(
            'page'      => array(
                'default'           => 1,
                'sanitize_callback' => 'absint',
            ),
            'per_page'  => array(
                'default'           => 20,
                'sanitize_callback' => 'absint',
            ),
            'search'    => array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'status'    => array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'date_from' => array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'date_to'   => array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ),
        );
    }

    private function get_review_params() {
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

    private function get_microsite_params() {
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
            'status'   => array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ),
        );
    }

    private function get_pagination_params() {
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
        );
    }

    /**
     * Dashboard Stats.
     */
    public function get_dashboard_stats() {
        global $wpdb;

        $performers_table = Peanut_Booker_Database::get_table( 'performers' );
        $bookings_table   = Peanut_Booker_Database::get_table( 'bookings' );
        $reviews_table    = Peanut_Booker_Database::get_table( 'reviews' );

        $total_performers    = Peanut_Booker_Database::count( 'performers' );
        $total_bookings      = Peanut_Booker_Database::count( 'bookings' );
        $pending_bookings    = Peanut_Booker_Database::count( 'bookings', array( 'booking_status' => 'pending' ) );
        $flagged_reviews     = Peanut_Booker_Database::count( 'reviews', array( 'is_flagged' => 1 ) );

        // Calculate revenue.
        $total_revenue = $wpdb->get_var(
            "SELECT COALESCE(SUM(total_amount), 0) FROM $bookings_table WHERE booking_status IN ('completed', 'confirmed')"
        );

        $platform_commission = $wpdb->get_var(
            "SELECT COALESCE(SUM(commission_amount), 0) FROM $bookings_table WHERE booking_status = 'completed'"
        );

        $demo_mode = get_option( 'peanut_booker_demo_mode', false );

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => array(
                    'total_performers'            => (int) $total_performers,
                    'total_bookings'              => (int) $total_bookings,
                    'pending_bookings'            => (int) $pending_bookings,
                    'total_revenue'               => (float) $total_revenue,
                    'platform_commission'         => (float) $platform_commission,
                    'reviews_needing_arbitration' => (int) $flagged_reviews,
                    'demo_mode'                   => (bool) $demo_mode,
                ),
            )
        );
    }

    /**
     * Admin Performers.
     */
    public function get_admin_performers( $request ) {
        global $wpdb;

        $page     = $request->get_param( 'page' );
        $per_page = $request->get_param( 'per_page' );
        $search   = $request->get_param( 'search' );
        $tier     = $request->get_param( 'tier' );
        $status   = $request->get_param( 'status' );
        $offset   = ( $page - 1 ) * $per_page;

        $performers_table = Peanut_Booker_Database::get_table( 'performers' );

        $where_clauses = array( '1=1' );
        $where_values  = array();

        if ( ! empty( $search ) ) {
            $where_clauses[] = '(stage_name LIKE %s OR email LIKE %s)';
            $search_like     = '%' . $wpdb->esc_like( $search ) . '%';
            $where_values[]  = $search_like;
            $where_values[]  = $search_like;
        }

        if ( ! empty( $tier ) ) {
            $where_clauses[] = 'tier = %s';
            $where_values[]  = $tier;
        }

        if ( ! empty( $status ) ) {
            $where_clauses[] = 'status = %s';
            $where_values[]  = $status;
        }

        $where_sql = implode( ' AND ', $where_clauses );

        // Get total count.
        $count_sql = "SELECT COUNT(*) FROM $performers_table WHERE $where_sql";
        if ( ! empty( $where_values ) ) {
            $count_sql = $wpdb->prepare( $count_sql, $where_values );
        }
        $total = (int) $wpdb->get_var( $count_sql );

        // Get results.
        $sql = "SELECT p.*, u.user_email as email
                FROM $performers_table p
                LEFT JOIN {$wpdb->users} u ON p.user_id = u.ID
                WHERE $where_sql
                ORDER BY p.id DESC
                LIMIT %d OFFSET %d";

        $query_values = array_merge( $where_values, array( $per_page, $offset ) );
        $sql          = $wpdb->prepare( $sql, $query_values );

        $results = $wpdb->get_results( $sql );

        // Format results.
        $performers = array();
        foreach ( $results as $performer ) {
            $performers[] = $this->format_performer( $performer );
        }

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => array(
                    'data'        => $performers,
                    'total'       => $total,
                    'page'        => $page,
                    'per_page'    => $per_page,
                    'total_pages' => ceil( $total / $per_page ),
                ),
            )
        );
    }

    public function get_admin_performer( $request ) {
        $performer = Peanut_Booker_Database::get_row( 'performers', array( 'id' => $request['id'] ) );

        if ( ! $performer ) {
            return new WP_Error( 'not_found', 'Performer not found.', array( 'status' => 404 ) );
        }

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => $this->format_performer( $performer ),
            )
        );
    }

    public function update_performer( $request ) {
        $id   = $request['id'];
        $data = $request->get_json_params();

        // Remove non-updatable fields.
        unset( $data['id'], $data['user_id'], $data['created_at'] );

        // Update performer table.
        $performer_fields = array(
            'tier', 'achievement_level', 'hourly_rate', 'deposit_percentage',
            'service_radius', 'is_verified', 'is_featured', 'status',
        );

        $performer_data = array_intersect_key( $data, array_flip( $performer_fields ) );
        if ( ! empty( $performer_data ) ) {
            $performer_data['updated_at'] = current_time( 'mysql' );
            Peanut_Booker_Database::update( 'performers', $performer_data, array( 'id' => $id ) );
        }

        // Update post meta if profile_id exists.
        $performer = Peanut_Booker_Database::get_row( 'performers', array( 'id' => $id ) );
        if ( $performer && $performer->profile_id ) {
            $meta_fields = array(
                'stage_name', 'tagline', 'experience_years', 'website', 'phone',
                'email_public', 'minimum_booking', 'sale_price', 'sale_active',
                'location_city', 'location_state', 'travel_willing', 'travel_radius',
            );

            foreach ( $meta_fields as $field ) {
                if ( isset( $data[ $field ] ) ) {
                    update_post_meta( $performer->profile_id, '_pb_' . $field, $data[ $field ] );
                }
            }
        }

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => $this->format_performer( Peanut_Booker_Database::get_row( 'performers', array( 'id' => $id ) ) ),
            )
        );
    }

    public function verify_performer( $request ) {
        $id       = $request['id'];
        $verified = $request->get_param( 'verified' );

        Peanut_Booker_Database::update(
            'performers',
            array(
                'is_verified' => $verified ? 1 : 0,
                'updated_at'  => current_time( 'mysql' ),
            ),
            array( 'id' => $id )
        );

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => $this->format_performer( Peanut_Booker_Database::get_row( 'performers', array( 'id' => $id ) ) ),
            )
        );
    }

    public function feature_performer( $request ) {
        $id       = $request['id'];
        $featured = $request->get_param( 'featured' );

        Peanut_Booker_Database::update(
            'performers',
            array(
                'is_featured' => $featured ? 1 : 0,
                'updated_at'  => current_time( 'mysql' ),
            ),
            array( 'id' => $id )
        );

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => $this->format_performer( Peanut_Booker_Database::get_row( 'performers', array( 'id' => $id ) ) ),
            )
        );
    }

    public function bulk_delete_performers( $request ) {
        $ids = $request->get_param( 'ids' );

        if ( empty( $ids ) || ! is_array( $ids ) ) {
            return new WP_Error( 'invalid_ids', 'Invalid performer IDs.', array( 'status' => 400 ) );
        }

        foreach ( $ids as $id ) {
            $performer = Peanut_Booker_Database::get_row( 'performers', array( 'id' => $id ) );
            if ( $performer ) {
                // Delete profile post.
                if ( $performer->profile_id ) {
                    wp_delete_post( $performer->profile_id, true );
                }
                // Delete performer record.
                Peanut_Booker_Database::delete( 'performers', array( 'id' => $id ) );
            }
        }

        return rest_ensure_response(
            array(
                'success' => true,
                'message' => sprintf( 'Deleted %d performer(s).', count( $ids ) ),
            )
        );
    }

    private function format_performer( $performer ) {
        if ( ! $performer ) {
            return null;
        }

        $user = get_userdata( $performer->user_id );

        // Get meta from profile if exists.
        $stage_name = '';
        if ( $performer->profile_id ) {
            $stage_name = get_post_meta( $performer->profile_id, '_pb_stage_name', true );
            if ( empty( $stage_name ) ) {
                $stage_name = get_the_title( $performer->profile_id );
            }
        }

        // Get microsite info for this performer.
        $microsite = Peanut_Booker_Database::get_row( 'microsites', array( 'performer_id' => $performer->id ) );

        return array(
            'id'                   => (int) $performer->id,
            'user_id'              => (int) $performer->user_id,
            'profile_id'           => (int) $performer->profile_id,
            'stage_name'           => $stage_name ?: ( $user ? $user->display_name : '' ),
            'email'                => $user ? $user->user_email : '',
            'tier'                 => $performer->tier ?: 'free',
            'achievement_level'    => $performer->achievement_level ?: 'bronze',
            'achievement_score'    => (int) $performer->achievement_score,
            'completed_bookings'   => (int) $performer->completed_bookings,
            'average_rating'       => (float) $performer->average_rating,
            'total_reviews'        => (int) $performer->total_reviews,
            'profile_completeness' => (int) $performer->profile_completeness,
            'hourly_rate'          => (float) $performer->hourly_rate,
            'deposit_percentage'   => (float) $performer->deposit_percentage,
            'service_radius'       => (int) $performer->service_radius,
            'is_verified'          => (bool) $performer->is_verified,
            'is_featured'          => (bool) $performer->is_featured,
            'status'               => $performer->status ?: 'active',
            'location_city'        => $performer->profile_id ? get_post_meta( $performer->profile_id, '_pb_location_city', true ) : '',
            'location_state'       => $performer->profile_id ? get_post_meta( $performer->profile_id, '_pb_location_state', true ) : '',
            // Microsite info.
            'microsite_id'         => $microsite ? (int) $microsite->id : null,
            'microsite_status'     => $microsite ? $microsite->status : null,
            'microsite_slug'       => $microsite ? $microsite->slug : null,
            'microsite_created_at' => $microsite ? $microsite->created_at : null,
            'microsite_views'      => $microsite ? (int) $microsite->view_count : null,
            'created_at'           => $performer->created_at,
            'updated_at'           => $performer->updated_at,
        );
    }

    /**
     * Get performer editor data (combined performer + microsite data).
     */
    public function get_performer_editor( $request ) {
        $performer_id = (int) $request['id'];
        $performer = Peanut_Booker_Database::get_row( 'performers', array( 'id' => $performer_id ) );

        if ( ! $performer ) {
            return new WP_Error( 'not_found', 'Performer not found.', array( 'status' => 404 ) );
        }

        // Get or create microsite for this performer.
        $microsite = Peanut_Booker_Database::get_row( 'microsites', array( 'performer_id' => $performer_id ) );

        if ( ! $microsite ) {
            // Create a new microsite for this performer.
            $stage_name = '';
            if ( $performer->profile_id ) {
                $stage_name = get_post_meta( $performer->profile_id, '_pb_stage_name', true );
                if ( empty( $stage_name ) ) {
                    $stage_name = get_the_title( $performer->profile_id );
                }
            }
            $microsite_data = array(
                'performer_id'    => $performer_id,
                'user_id'         => $performer->user_id,
                'status'          => 'pending',
                'slug'            => sanitize_title( $stage_name ?: 'performer' ) . '-' . $performer_id,
                'design_settings' => wp_json_encode( $this->get_default_design_settings() ),
                'created_at'      => current_time( 'mysql' ),
                'updated_at'      => current_time( 'mysql' ),
            );
            Peanut_Booker_Database::insert( 'microsites', $microsite_data );
            $microsite = Peanut_Booker_Database::get_row( 'microsites', array( 'performer_id' => $performer_id ) );
        }

        return rest_ensure_response( $this->build_performer_editor_data( $performer, $microsite ) );
    }

    /**
     * Update performer editor data.
     */
    public function update_performer_editor( $request ) {
        $performer_id = (int) $request['id'];
        $performer = Peanut_Booker_Database::get_row( 'performers', array( 'id' => $performer_id ) );

        if ( ! $performer ) {
            return new WP_Error( 'not_found', 'Performer not found.', array( 'status' => 404 ) );
        }

        $params = $request->get_json_params();

        // Update performer profile data.
        if ( ! empty( $params['performer'] ) && $performer->profile_id ) {
            $profile_data = $params['performer'];
            $profile_id = $performer->profile_id;

            if ( isset( $profile_data['stage_name'] ) ) {
                update_post_meta( $profile_id, '_pb_stage_name', sanitize_text_field( $profile_data['stage_name'] ) );
            }
            if ( isset( $profile_data['tagline'] ) ) {
                update_post_meta( $profile_id, '_pb_tagline', sanitize_text_field( $profile_data['tagline'] ) );
            }
            if ( isset( $profile_data['bio'] ) ) {
                wp_update_post( array( 'ID' => $profile_id, 'post_content' => wp_kses_post( $profile_data['bio'] ) ) );
            }
            if ( isset( $profile_data['location_city'] ) ) {
                update_post_meta( $profile_id, '_pb_location_city', sanitize_text_field( $profile_data['location_city'] ) );
            }
            if ( isset( $profile_data['location_state'] ) ) {
                update_post_meta( $profile_id, '_pb_location_state', sanitize_text_field( $profile_data['location_state'] ) );
            }

            $performer_update = array();
            if ( isset( $profile_data['hourly_rate'] ) ) {
                $performer_update['hourly_rate'] = (float) $profile_data['hourly_rate'];
            }
            if ( ! empty( $performer_update ) ) {
                $performer_update['updated_at'] = current_time( 'mysql' );
                Peanut_Booker_Database::update( 'performers', $performer_update, array( 'id' => $performer_id ) );
            }
        }

        // Update microsite data.
        $microsite = Peanut_Booker_Database::get_row( 'microsites', array( 'performer_id' => $performer_id ) );

        if ( $microsite ) {
            $microsite_update = array();

            if ( ! empty( $params['microsite'] ) ) {
                $ms = $params['microsite'];
                if ( isset( $ms['slug'] ) ) {
                    $microsite_update['slug'] = sanitize_title( $ms['slug'] );
                }
                if ( isset( $ms['status'] ) ) {
                    $microsite_update['status'] = sanitize_text_field( $ms['status'] );
                }
            }

            if ( ! empty( $params['design_settings'] ) ) {
                $microsite_update['design_settings'] = wp_json_encode( $params['design_settings'] );
            }

            if ( ! empty( $microsite_update ) ) {
                $microsite_update['updated_at'] = current_time( 'mysql' );
                Peanut_Booker_Database::update( 'microsites', $microsite_update, array( 'id' => $microsite->id ) );
                $microsite = Peanut_Booker_Database::get_row( 'microsites', array( 'id' => $microsite->id ) );
            }
        }

        $performer = Peanut_Booker_Database::get_row( 'performers', array( 'id' => $performer_id ) );
        return rest_ensure_response( $this->build_performer_editor_data( $performer, $microsite ) );
    }

    /**
     * Get performer analytics.
     */
    public function get_performer_analytics( $request ) {
        global $wpdb;

        $performer_id = (int) $request['id'];
        $days = (int) ( $request->get_param( 'days' ) ?: 30 );
        $microsite = Peanut_Booker_Database::get_row( 'microsites', array( 'performer_id' => $performer_id ) );

        if ( ! $microsite ) {
            return rest_ensure_response( array(
                'total_views'     => 0,
                'unique_visitors' => 0,
                'booking_clicks'  => 0,
                'views_by_day'    => array(),
                'top_referrers'   => array(),
                'popular_hours'   => array(),
            ) );
        }

        $analytics_table = $wpdb->prefix . 'pb_microsite_analytics';
        $start_date = gmdate( 'Y-m-d', strtotime( "-{$days} days" ) );

        // Get totals.
        $totals = $wpdb->get_row( $wpdb->prepare(
            "SELECT
                COALESCE(SUM(page_views), 0) as total_views,
                COALESCE(SUM(unique_visitors), 0) as unique_visitors,
                COALESCE(SUM(booking_clicks), 0) as booking_clicks
             FROM $analytics_table
             WHERE microsite_id = %d AND date >= %s",
            $microsite->id,
            $start_date
        ) );

        // Get views by day.
        $views_by_day = $wpdb->get_results( $wpdb->prepare(
            "SELECT date, SUM(page_views) as views
             FROM $analytics_table
             WHERE microsite_id = %d AND date >= %s
             GROUP BY date
             ORDER BY date ASC",
            $microsite->id,
            $start_date
        ) );

        // Get top referrers.
        $top_referrers = $wpdb->get_results( $wpdb->prepare(
            "SELECT referrer_domain as domain, SUM(page_views) as count
             FROM $analytics_table
             WHERE microsite_id = %d AND date >= %s AND referrer_domain IS NOT NULL AND referrer_domain != ''
             GROUP BY referrer_domain
             ORDER BY count DESC
             LIMIT 10",
            $microsite->id,
            $start_date
        ) );

        // Get popular hours.
        $popular_hours = $wpdb->get_results( $wpdb->prepare(
            "SELECT hour_of_day as hour, SUM(page_views) as count
             FROM $analytics_table
             WHERE microsite_id = %d AND date >= %s AND hour_of_day IS NOT NULL
             GROUP BY hour_of_day
             ORDER BY hour_of_day ASC",
            $microsite->id,
            $start_date
        ) );

        return rest_ensure_response( array(
            'total_views'     => (int) ( $totals->total_views ?? 0 ),
            'unique_visitors' => (int) ( $totals->unique_visitors ?? 0 ),
            'booking_clicks'  => (int) ( $totals->booking_clicks ?? 0 ),
            'views_by_day'    => array_map( function( $row ) {
                return array( 'date' => $row->date, 'views' => (int) $row->views );
            }, $views_by_day ?: array() ),
            'top_referrers'   => array_map( function( $row ) {
                return array( 'domain' => $row->domain, 'count' => (int) $row->count );
            }, $top_referrers ?: array() ),
            'popular_hours'   => array_map( function( $row ) {
                return array( 'hour' => (int) $row->hour, 'count' => (int) $row->count );
            }, $popular_hours ?: array() ),
        ) );
    }

    /**
     * Get external gigs for a performer.
     */
    public function get_performer_external_gigs_endpoint( $request ) {
        $performer_id = (int) $request['id'];
        $gigs = $this->get_performer_external_gigs( $performer_id );
        return rest_ensure_response( array( 'data' => $gigs ) );
    }

    /**
     * Create external gig for a performer.
     */
    public function create_performer_external_gig( $request ) {
        $performer_id = (int) $request['id'];
        $params = $request->get_json_params();

        if ( empty( $params['date'] ) || empty( $params['event_name'] ) ) {
            return new WP_Error( 'invalid_data', 'Date and event name are required.', array( 'status' => 400 ) );
        }

        // Validate ticket_url if provided (must be HTTPS).
        $ticket_url = '';
        if ( ! empty( $params['ticket_url'] ) ) {
            $ticket_url = esc_url_raw( $params['ticket_url'] );
            if ( ! empty( $ticket_url ) && strpos( $ticket_url, 'https://' ) !== 0 ) {
                return new WP_Error( 'invalid_url', 'Ticket URL must use HTTPS.', array( 'status' => 400 ) );
            }
        }

        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'pb_availability',
            array(
                'performer_id'   => $performer_id,
                'date'           => sanitize_text_field( $params['date'] ),
                'event_name'     => sanitize_text_field( $params['event_name'] ),
                'venue_name'     => sanitize_text_field( $params['venue_name'] ?? '' ),
                'event_location' => sanitize_text_field( $params['event_location'] ?? '' ),
                'event_time'     => ! empty( $params['event_time'] ) ? sanitize_text_field( $params['event_time'] ) : null,
                'is_public'      => isset( $params['is_public'] ) ? ( $params['is_public'] ? 1 : 0 ) : 1,
                'ticket_url'     => $ticket_url,
                'block_type'     => 'external_gig',
                'status'         => 'blocked',
                'created_at'     => current_time( 'mysql' ),
                'updated_at'     => current_time( 'mysql' ),
            )
        );

        $gig_id = $wpdb->insert_id;
        $gig = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pb_availability WHERE id = %d",
            $gig_id
        ) );

        return rest_ensure_response( array(
            'success' => true,
            'id'      => $gig_id,
            'data'    => $this->format_external_gig( $gig ),
        ) );
    }

    /**
     * Update external gig.
     */
    public function update_performer_external_gig( $request ) {
        $gig_id = (int) $request['gig_id'];
        $performer_id = (int) $request['id'];
        $params = $request->get_json_params();

        global $wpdb;

        // Verify gig belongs to performer.
        $gig = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pb_availability WHERE id = %d AND performer_id = %d AND block_type = 'external_gig'",
            $gig_id,
            $performer_id
        ) );

        if ( ! $gig ) {
            return new WP_Error( 'not_found', 'External gig not found.', array( 'status' => 404 ) );
        }

        // Validate ticket_url if provided.
        $ticket_url = $gig->ticket_url;
        if ( isset( $params['ticket_url'] ) ) {
            if ( empty( $params['ticket_url'] ) ) {
                $ticket_url = '';
            } else {
                $ticket_url = esc_url_raw( $params['ticket_url'] );
                if ( ! empty( $ticket_url ) && strpos( $ticket_url, 'https://' ) !== 0 ) {
                    return new WP_Error( 'invalid_url', 'Ticket URL must use HTTPS.', array( 'status' => 400 ) );
                }
            }
        }

        $update_data = array(
            'updated_at' => current_time( 'mysql' ),
        );

        if ( isset( $params['date'] ) ) {
            $update_data['date'] = sanitize_text_field( $params['date'] );
        }
        if ( isset( $params['event_name'] ) ) {
            $update_data['event_name'] = sanitize_text_field( $params['event_name'] );
        }
        if ( isset( $params['venue_name'] ) ) {
            $update_data['venue_name'] = sanitize_text_field( $params['venue_name'] );
        }
        if ( isset( $params['event_location'] ) ) {
            $update_data['event_location'] = sanitize_text_field( $params['event_location'] );
        }
        if ( isset( $params['event_time'] ) ) {
            $update_data['event_time'] = ! empty( $params['event_time'] ) ? sanitize_text_field( $params['event_time'] ) : null;
        }
        if ( isset( $params['is_public'] ) ) {
            $update_data['is_public'] = $params['is_public'] ? 1 : 0;
        }
        if ( isset( $params['ticket_url'] ) ) {
            $update_data['ticket_url'] = $ticket_url;
        }

        $wpdb->update(
            $wpdb->prefix . 'pb_availability',
            $update_data,
            array( 'id' => $gig_id )
        );

        $updated_gig = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pb_availability WHERE id = %d",
            $gig_id
        ) );

        return rest_ensure_response( array(
            'success' => true,
            'data'    => $this->format_external_gig( $updated_gig ),
        ) );
    }

    /**
     * Delete external gig.
     */
    public function delete_performer_external_gig( $request ) {
        $gig_id = (int) $request['gig_id'];
        $performer_id = (int) $request['id'];

        global $wpdb;

        // Verify gig belongs to performer.
        $gig = $wpdb->get_row( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}pb_availability WHERE id = %d AND performer_id = %d AND block_type = 'external_gig'",
            $gig_id,
            $performer_id
        ) );

        if ( ! $gig ) {
            return new WP_Error( 'not_found', 'External gig not found.', array( 'status' => 404 ) );
        }

        $wpdb->delete( $wpdb->prefix . 'pb_availability', array( 'id' => $gig_id ) );

        return rest_ensure_response( array( 'success' => true ) );
    }

    /**
     * Get external gigs for performer.
     */
    private function get_performer_external_gigs( $performer_id ) {
        global $wpdb;
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pb_availability WHERE performer_id = %d AND block_type = 'external_gig' ORDER BY date DESC",
            $performer_id
        ) );

        return array_map( array( $this, 'format_external_gig' ), $results ?: array() );
    }

    /**
     * Format external gig for API response.
     *
     * @param object $gig Gig database row.
     * @return array Formatted gig data.
     */
    private function format_external_gig( $gig ) {
        return array(
            'id'             => (int) $gig->id,
            'performer_id'   => (int) $gig->performer_id,
            'date'           => $gig->date,
            'event_name'     => $gig->event_name ?? '',
            'venue_name'     => $gig->venue_name ?? '',
            'event_location' => $gig->event_location ?? '',
            'event_time'     => $gig->event_time ?? null,
            'is_public'      => (bool) ( $gig->is_public ?? true ),
            'ticket_url'     => $gig->ticket_url ?? '',
        );
    }

    /**
     * Build performer editor data.
     */
    private function build_performer_editor_data( $performer, $microsite ) {
        $performer_data = null;

        if ( $performer && $performer->profile_id ) {
            $profile_id = $performer->profile_id;
            $performer_data = array(
                'id'                 => (int) $performer->id,
                'stage_name'         => html_entity_decode( get_post_meta( $profile_id, '_pb_stage_name', true ) ?: get_the_title( $profile_id ), ENT_QUOTES, 'UTF-8' ),
                'tagline'            => get_post_meta( $profile_id, '_pb_tagline', true ),
                'bio'                => get_post_field( 'post_content', $profile_id ),
                'hourly_rate'        => (float) $performer->hourly_rate,
                'minimum_booking'    => (int) ( $performer->minimum_booking ?? 1 ),
                'deposit_percentage' => (float) ( $performer->deposit_percentage ?? 25 ),
                'location_city'      => get_post_meta( $profile_id, '_pb_location_city', true ),
                'location_state'     => get_post_meta( $profile_id, '_pb_location_state', true ),
                'profile_photo'      => get_the_post_thumbnail_url( $profile_id, 'large' ) ?: '',
                'gallery'            => $this->get_performer_gallery( $profile_id ),
            );
        }

        // Get reviews.
        // Reviews table uses reviewee_id (user_id of performer) not performer_id.
        $reviews = array();
        if ( $performer && $performer->user_id ) {
            $review_results = Peanut_Booker_Database::get_results(
                'reviews',
                array( 'reviewee_id' => $performer->user_id, 'is_visible' => 1 ),
                'created_at',
                'DESC',
                5
            );
            foreach ( $review_results as $review ) {
                $reviews[] = array(
                    'id'       => (int) $review->id,
                    'rating'   => (int) $review->rating,
                    'content'  => $review->content,
                    'reviewer' => get_user_meta( $review->reviewer_id, 'first_name', true ) ?: 'Customer',
                );
            }
        }

        // Get external gigs.
        $external_gigs = $this->get_performer_external_gigs( $performer->id );

        // Parse design settings.
        $design_settings = $this->get_default_design_settings();
        if ( $microsite && ! empty( $microsite->design_settings ) ) {
            $saved_settings = json_decode( $microsite->design_settings, true ) ?: array();
            $design_settings = wp_parse_args( $saved_settings, $design_settings );
        }

        return array(
            'microsite'     => $microsite ? $this->format_microsite( $microsite ) : null,
            'performer'     => $performer_data,
            'reviews'       => $reviews,
            'external_gigs' => $external_gigs,
            'preview_url'   => $microsite ? home_url( '/performer/' . $microsite->slug . '/?preview=1' ) : '',
        );
    }

    /**
     * Get default design settings.
     */
    private function get_default_design_settings() {
        return array(
            'template'            => 'classic',
            'primary_color'       => '#3b82f6',
            'secondary_color'     => '#1e40af',
            'background_color'    => '#ffffff',
            'text_color'          => '#1e293b',
            'font_family'         => 'Inter',
            'show_reviews'        => true,
            'show_calendar'       => true,
            'show_booking_button' => true,
            'custom_css'          => '',
            'social_links'        => array(),
            'layout_settings'     => array(
                'hero_style'           => 'full_width',
                'sections_order'       => array( 'hero', 'bio', 'gallery', 'reviews', 'calendar', 'social', 'booking' ),
                'show_external_gigs'   => true,
                'external_gig_privacy' => 'public',
            ),
        );
    }

    /**
     * Get performer gallery.
     */
    private function get_performer_gallery( $profile_id ) {
        $gallery = get_post_meta( $profile_id, '_pb_gallery', true );
        if ( empty( $gallery ) || ! is_array( $gallery ) ) {
            return array();
        }
        return array_map( function( $id ) {
            return array( 'id' => (int) $id, 'url' => wp_get_attachment_url( $id ) ?: '' );
        }, $gallery );
    }

    /**
     * Get bookings for a specific performer (for performer dashboard).
     */
    public function get_performer_bookings( $request ) {
        global $wpdb;

        $performer_id = (int) $request->get_param( 'id' );
        $status       = $request->get_param( 'status' );
        $page         = (int) $request->get_param( 'page' );
        $per_page     = (int) $request->get_param( 'per_page' );
        $offset       = ( $page - 1 ) * $per_page;

        // Verify performer exists.
        $performer = Peanut_Booker_Database::get_row( 'performers', array( 'id' => $performer_id ) );
        if ( ! $performer ) {
            return new WP_Error( 'performer_not_found', 'Performer not found', array( 'status' => 404 ) );
        }

        $bookings_table = Peanut_Booker_Database::get_table( 'bookings' );

        $where_clauses = array( 'b.performer_id = %d' );
        $where_values  = array( $performer_id );

        if ( ! empty( $status ) ) {
            $where_clauses[] = 'b.booking_status = %s';
            $where_values[]  = $status;
        }

        $where_sql = implode( ' AND ', $where_clauses );

        // Get total count.
        $count_sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM $bookings_table b WHERE $where_sql",
            $where_values
        );
        $total = (int) $wpdb->get_var( $count_sql );

        // Get bookings with customer info.
        $sql = $wpdb->prepare(
            "SELECT b.*,
                    u_customer.display_name as customer_name,
                    u_customer.user_email as customer_email
             FROM $bookings_table b
             LEFT JOIN {$wpdb->users} u_customer ON b.customer_id = u_customer.ID
             WHERE $where_sql
             ORDER BY b.event_date DESC
             LIMIT %d OFFSET %d",
            array_merge( $where_values, array( $per_page, $offset ) )
        );

        $results = $wpdb->get_results( $sql );

        // Format results.
        $bookings = array();
        foreach ( $results as $booking ) {
            $bookings[] = $this->format_booking( $booking );
        }

        // Calculate stats.
        $stats_sql = $wpdb->prepare(
            "SELECT
                COUNT(CASE WHEN booking_status = 'confirmed' AND event_date >= CURDATE() THEN 1 END) as upcoming,
                COUNT(CASE WHEN booking_status = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN booking_status = 'completed' THEN 1 END) as completed,
                COUNT(CASE WHEN booking_status = 'cancelled' THEN 1 END) as cancelled,
                SUM(CASE WHEN booking_status = 'completed' THEN total_amount ELSE 0 END) as total_earned
             FROM $bookings_table
             WHERE performer_id = %d",
            $performer_id
        );
        $stats = $wpdb->get_row( $stats_sql );

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => $bookings,
                'stats'   => array(
                    'upcoming'     => (int) $stats->upcoming,
                    'pending'      => (int) $stats->pending,
                    'completed'    => (int) $stats->completed,
                    'cancelled'    => (int) $stats->cancelled,
                    'total_earned' => (float) $stats->total_earned,
                ),
                'total'      => $total,
                'page'       => $page,
                'per_page'   => $per_page,
                'total_pages' => ceil( $total / $per_page ),
            )
        );
    }

    /**
     * Get reviews for a specific performer (for performer dashboard).
     */
    public function get_performer_reviews( $request ) {
        global $wpdb;

        $performer_id = (int) $request->get_param( 'id' );
        $page         = (int) $request->get_param( 'page' );
        $per_page     = (int) $request->get_param( 'per_page' );
        $offset       = ( $page - 1 ) * $per_page;

        // Verify performer exists.
        $performer = Peanut_Booker_Database::get_row( 'performers', array( 'id' => $performer_id ) );
        if ( ! $performer ) {
            return new WP_Error( 'performer_not_found', 'Performer not found', array( 'status' => 404 ) );
        }

        // Get the user_id for this performer to find their reviews
        $user_id = $performer->user_id;

        $reviews_table = Peanut_Booker_Database::get_table( 'reviews' );

        // Get total count of reviews where this performer is the reviewee.
        $count_sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM $reviews_table WHERE reviewee_id = %d AND is_visible = 1",
            $user_id
        );
        $total = (int) $wpdb->get_var( $count_sql );

        // Get reviews.
        $sql = $wpdb->prepare(
            "SELECT r.*,
                    u_reviewer.display_name as reviewer_name
             FROM $reviews_table r
             LEFT JOIN {$wpdb->users} u_reviewer ON r.reviewer_id = u_reviewer.ID
             WHERE r.reviewee_id = %d AND r.is_visible = 1
             ORDER BY r.created_at DESC
             LIMIT %d OFFSET %d",
            $user_id,
            $per_page,
            $offset
        );

        $results = $wpdb->get_results( $sql );

        // Format results.
        $reviews = array();
        foreach ( $results as $review ) {
            $reviews[] = $this->format_review( $review );
        }

        // Calculate rating distribution.
        $rating_sql = $wpdb->prepare(
            "SELECT
                rating,
                COUNT(*) as count
             FROM $reviews_table
             WHERE reviewee_id = %d AND is_visible = 1
             GROUP BY rating
             ORDER BY rating DESC",
            $user_id
        );
        $rating_results = $wpdb->get_results( $rating_sql );

        $rating_distribution = array( 5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0 );
        foreach ( $rating_results as $row ) {
            $rating_distribution[ (int) $row->rating ] = (int) $row->count;
        }

        // Calculate average rating.
        $avg_sql = $wpdb->prepare(
            "SELECT AVG(rating) as avg_rating FROM $reviews_table WHERE reviewee_id = %d AND is_visible = 1",
            $user_id
        );
        $avg_rating = (float) $wpdb->get_var( $avg_sql );

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => $reviews,
                'stats'   => array(
                    'total_reviews'       => $total,
                    'average_rating'      => round( $avg_rating, 1 ),
                    'rating_distribution' => $rating_distribution,
                ),
                'total'      => $total,
                'page'       => $page,
                'per_page'   => $per_page,
                'total_pages' => ceil( $total / $per_page ),
            )
        );
    }

    /**
     * Get performer availability for calendar.
     */
    public function get_performer_availability( $request ) {
        global $wpdb;

        $performer_id = (int) $request->get_param( 'id' );
        $start_date   = sanitize_text_field( $request->get_param( 'start_date' ) );
        $end_date     = sanitize_text_field( $request->get_param( 'end_date' ) );

        // Verify performer exists.
        $performer = Peanut_Booker_Database::get_row( 'performers', array( 'id' => $performer_id ) );
        if ( ! $performer ) {
            return new WP_Error( 'performer_not_found', 'Performer not found', array( 'status' => 404 ) );
        }

        $availability_table = $wpdb->prefix . 'pb_availability';
        $bookings_table     = $wpdb->prefix . 'pb_bookings';

        // Get blocked dates (manual blocks and external gigs).
        $blocks_sql = $wpdb->prepare(
            "SELECT a.*
             FROM $availability_table a
             WHERE a.performer_id = %d
             AND a.date >= %s
             AND a.date <= %s
             AND a.status IN ('blocked', 'booked')
             ORDER BY a.date ASC",
            $performer_id,
            $start_date,
            $end_date
        );

        $blocks = $wpdb->get_results( $blocks_sql );

        // Get confirmed bookings for this performer.
        $bookings_sql = $wpdb->prepare(
            "SELECT b.id, b.booking_number, b.event_title, b.event_date, b.event_time,
                    b.status, b.customer_id, u.display_name as customer_name
             FROM $bookings_table b
             LEFT JOIN {$wpdb->users} u ON b.customer_id = u.ID
             WHERE b.performer_id = %d
             AND b.event_date >= %s
             AND b.event_date <= %s
             AND b.status IN ('confirmed', 'pending', 'completed')
             ORDER BY b.event_date ASC",
            $performer_id,
            $start_date,
            $end_date
        );

        $bookings = $wpdb->get_results( $bookings_sql );

        // Format events for calendar.
        $events = array();

        // Add blocked dates.
        foreach ( $blocks as $block ) {
            $events[] = array(
                'id'         => (int) $block->id,
                'title'      => $block->event_name ?: ( $block->block_type === 'external_gig' ? 'External Gig' : 'Blocked' ),
                'start'      => $block->date,
                'end'        => $block->date,
                'allDay'     => true,
                'type'       => $block->block_type ?: 'manual',
                'status'     => $block->status,
                'venue_name' => $block->venue_name ?: '',
                'event_location' => $block->event_location ?: '',
                'notes'      => $block->notes ?: '',
                'canDelete'  => $block->block_type !== 'booking',
            );
        }

        // Add bookings.
        foreach ( $bookings as $booking ) {
            $events[] = array(
                'id'            => 'booking_' . (int) $booking->id,
                'title'         => $booking->event_title ?: 'Booking #' . $booking->booking_number,
                'start'         => $booking->event_date,
                'end'           => $booking->event_date,
                'allDay'        => true,
                'type'          => 'booking',
                'status'        => $booking->status,
                'booking_id'    => (int) $booking->id,
                'customer_name' => $booking->customer_name ?: '',
                'event_time'    => $booking->event_time ?: '',
                'canDelete'     => false,
            );
        }

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => $events,
            )
        );
    }

    /**
     * Block dates for performer.
     */
    public function block_performer_dates( $request ) {
        global $wpdb;

        $performer_id = (int) $request->get_param( 'id' );
        $params       = $request->get_json_params();

        error_log( 'Block dates called for performer: ' . $performer_id );
        error_log( 'Params: ' . print_r( $params, true ) );

        // Verify performer exists.
        $performer = Peanut_Booker_Database::get_row( 'performers', array( 'id' => $performer_id ) );
        if ( ! $performer ) {
            error_log( 'Performer not found: ' . $performer_id );
            return new WP_Error( 'performer_not_found', 'Performer not found', array( 'status' => 404 ) );
        }

        $dates      = isset( $params['dates'] ) ? array_map( 'sanitize_text_field', (array) $params['dates'] ) : array();
        $title      = sanitize_text_field( $params['title'] ?? '' );
        $notes      = sanitize_text_field( $params['notes'] ?? '' );
        $block_type = sanitize_text_field( $params['block_type'] ?? 'manual' );

        if ( empty( $dates ) ) {
            return new WP_Error( 'no_dates', 'Please provide at least one date to block', array( 'status' => 400 ) );
        }

        $availability_table = $wpdb->prefix . 'pb_availability';
        $blocked_count      = 0;

        foreach ( $dates as $date ) {
            // Check if date already has a block.
            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM $availability_table
                     WHERE performer_id = %d AND date = %s AND status IN ('blocked', 'booked')",
                    $performer_id,
                    $date
                )
            );

            if ( $existing ) {
                continue; // Skip already blocked dates.
            }

            // Insert new block.
            $result = $wpdb->insert(
                $availability_table,
                array(
                    'performer_id' => $performer_id,
                    'date'         => $date,
                    'slot_type'    => 'full_day',
                    'status'       => 'blocked',
                    'block_type'   => $block_type,
                    'event_name'   => $title,
                    'notes'        => $notes,
                    'created_at'   => current_time( 'mysql' ),
                    'updated_at'   => current_time( 'mysql' ),
                )
            );

            if ( $result === false ) {
                error_log( 'Database insert error: ' . $wpdb->last_error );
            }

            if ( $wpdb->insert_id ) {
                $blocked_count++;
            }
        }

        error_log( 'Blocked ' . $blocked_count . ' dates' );

        return rest_ensure_response(
            array(
                'success' => true,
                'message' => sprintf( '%d date(s) blocked successfully', $blocked_count ),
                'blocked' => $blocked_count,
            )
        );
    }

    /**
     * Unblock a date for performer.
     */
    public function unblock_performer_date( $request ) {
        global $wpdb;

        $performer_id = (int) $request->get_param( 'id' );
        $slot_id      = (int) $request->get_param( 'slot_id' );

        // Verify performer exists.
        $performer = Peanut_Booker_Database::get_row( 'performers', array( 'id' => $performer_id ) );
        if ( ! $performer ) {
            return new WP_Error( 'performer_not_found', 'Performer not found', array( 'status' => 404 ) );
        }

        $availability_table = $wpdb->prefix . 'pb_availability';

        // Verify the slot belongs to this performer and can be deleted.
        $slot = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $availability_table WHERE id = %d AND performer_id = %d",
                $slot_id,
                $performer_id
            )
        );

        if ( ! $slot ) {
            return new WP_Error( 'slot_not_found', 'Availability slot not found', array( 'status' => 404 ) );
        }

        if ( $slot->block_type === 'booking' ) {
            return new WP_Error( 'cannot_delete_booking', 'Cannot delete a booking block', array( 'status' => 400 ) );
        }

        // Delete the block.
        $deleted = $wpdb->delete( $availability_table, array( 'id' => $slot_id ) );

        return rest_ensure_response(
            array(
                'success' => (bool) $deleted,
                'message' => $deleted ? 'Date unblocked successfully' : 'Failed to unblock date',
            )
        );
    }

    /**
     * Update performer availability slot.
     */
    public function update_performer_availability_slot( $request ) {
        global $wpdb;

        $performer_id = (int) $request->get_param( 'id' );
        $slot_id      = (int) $request->get_param( 'slot_id' );
        $params       = $request->get_json_params();

        // Verify performer exists.
        $performer = Peanut_Booker_Database::get_row( 'performers', array( 'id' => $performer_id ) );
        if ( ! $performer ) {
            return new WP_Error( 'performer_not_found', 'Performer not found', array( 'status' => 404 ) );
        }

        $availability_table = $wpdb->prefix . 'pb_availability';

        // Verify the slot belongs to this performer.
        $slot = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $availability_table WHERE id = %d AND performer_id = %d",
                $slot_id,
                $performer_id
            )
        );

        if ( ! $slot ) {
            return new WP_Error( 'slot_not_found', 'Availability slot not found', array( 'status' => 404 ) );
        }

        // Cannot edit booking blocks.
        if ( $slot->block_type === 'booking' ) {
            return new WP_Error( 'cannot_edit_booking', 'Cannot edit a booking block', array( 'status' => 400 ) );
        }

        // Prepare update data.
        $update_data = array(
            'updated_at' => current_time( 'mysql' ),
        );

        // Update fields if provided.
        if ( isset( $params['event_name'] ) ) {
            $update_data['event_name'] = sanitize_text_field( $params['event_name'] );
        }
        if ( isset( $params['venue_name'] ) ) {
            $update_data['venue_name'] = sanitize_text_field( $params['venue_name'] );
        }
        if ( isset( $params['event_location'] ) ) {
            $update_data['event_location'] = sanitize_text_field( $params['event_location'] );
        }
        if ( isset( $params['notes'] ) ) {
            $update_data['notes'] = sanitize_textarea_field( $params['notes'] );
        }
        if ( isset( $params['block_type'] ) && in_array( $params['block_type'], array( 'manual', 'external_gig' ), true ) ) {
            $update_data['block_type'] = sanitize_text_field( $params['block_type'] );
        }

        // Update the slot.
        $updated = $wpdb->update(
            $availability_table,
            $update_data,
            array( 'id' => $slot_id )
        );

        // Fetch the updated slot.
        $updated_slot = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $availability_table WHERE id = %d",
                $slot_id
            )
        );

        return rest_ensure_response(
            array(
                'success' => $updated !== false,
                'message' => $updated !== false ? 'Availability updated successfully' : 'Failed to update availability',
                'data'    => $updated_slot,
            )
        );
    }

    /**
     * Get performer conversations.
     */
    public function get_performer_conversations( $request ) {
        global $wpdb;

        $performer_id = (int) $request->get_param( 'id' );
        $page         = (int) $request->get_param( 'page' );
        $per_page     = (int) $request->get_param( 'per_page' );
        $offset       = ( $page - 1 ) * $per_page;

        // Verify performer exists and get user_id.
        $performer = Peanut_Booker_Database::get_row( 'performers', array( 'id' => $performer_id ) );
        if ( ! $performer ) {
            return new WP_Error( 'performer_not_found', 'Performer not found', array( 'status' => 404 ) );
        }

        $user_id        = $performer->user_id;
        $messages_table = Peanut_Booker_Database::get_table( 'messages' );

        // Get unique conversations where this performer is a participant.
        $sql = $wpdb->prepare(
            "SELECT
                MIN(id) as id,
                LEAST(sender_id, recipient_id) as participant_1_id,
                GREATEST(sender_id, recipient_id) as participant_2_id,
                MAX(created_at) as last_message_at,
                booking_id
            FROM $messages_table
            WHERE sender_id = %d OR recipient_id = %d
            GROUP BY LEAST(sender_id, recipient_id), GREATEST(sender_id, recipient_id), booking_id
            ORDER BY last_message_at DESC
            LIMIT %d OFFSET %d",
            $user_id,
            $user_id,
            $per_page,
            $offset
        );

        $results = $wpdb->get_results( $sql );

        // Get total count.
        $count_sql = $wpdb->prepare(
            "SELECT COUNT(DISTINCT CONCAT(LEAST(sender_id, recipient_id), '-', GREATEST(sender_id, recipient_id), '-', COALESCE(booking_id, 0)))
             FROM $messages_table
             WHERE sender_id = %d OR recipient_id = %d",
            $user_id,
            $user_id
        );
        $total = (int) $wpdb->get_var( $count_sql );

        // Format results.
        $conversations = array();
        foreach ( $results as $row ) {
            // Determine the other participant (not the performer).
            $other_user_id = ( (int) $row->participant_1_id === $user_id )
                ? (int) $row->participant_2_id
                : (int) $row->participant_1_id;

            $other_user = get_userdata( $other_user_id );

            // Get last message.
            $last_msg = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT content FROM $messages_table
                     WHERE (sender_id = %d AND recipient_id = %d) OR (sender_id = %d AND recipient_id = %d)
                     ORDER BY created_at DESC LIMIT 1",
                    $row->participant_1_id,
                    $row->participant_2_id,
                    $row->participant_2_id,
                    $row->participant_1_id
                )
            );

            // Get unread count (messages sent TO the performer that are unread).
            $unread = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $messages_table
                     WHERE recipient_id = %d AND is_read = 0
                     AND ((sender_id = %d AND recipient_id = %d) OR (sender_id = %d AND recipient_id = %d))",
                    $user_id,
                    $row->participant_1_id,
                    $row->participant_2_id,
                    $row->participant_2_id,
                    $row->participant_1_id
                )
            );

            $conversations[] = array(
                'id'               => (int) $row->id,
                'other_user_id'    => $other_user_id,
                'other_user_name'  => $other_user ? $other_user->display_name : 'Unknown',
                'last_message'     => $last_msg ? wp_trim_words( $last_msg, 15 ) : null,
                'last_message_at'  => $row->last_message_at,
                'unread_count'     => (int) $unread,
                'booking_id'       => $row->booking_id ? (int) $row->booking_id : null,
            );
        }

        return rest_ensure_response(
            array(
                'success'     => true,
                'data'        => $conversations,
                'total'       => $total,
                'page'        => $page,
                'per_page'    => $per_page,
                'total_pages' => ceil( $total / $per_page ),
            )
        );
    }

    /**
     * Get messages in a conversation for performer.
     */
    public function get_performer_conversation_messages( $request ) {
        global $wpdb;

        $performer_id    = (int) $request->get_param( 'id' );
        $conversation_id = (int) $request->get_param( 'conversation_id' );

        // Verify performer exists and get user_id.
        $performer = Peanut_Booker_Database::get_row( 'performers', array( 'id' => $performer_id ) );
        if ( ! $performer ) {
            return new WP_Error( 'performer_not_found', 'Performer not found', array( 'status' => 404 ) );
        }

        $user_id        = $performer->user_id;
        $messages_table = Peanut_Booker_Database::get_table( 'messages' );

        // Get a reference message to find participants.
        $ref_msg = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT sender_id, recipient_id FROM $messages_table WHERE id = %d",
                $conversation_id
            )
        );

        if ( ! $ref_msg ) {
            return new WP_Error( 'not_found', 'Conversation not found.', array( 'status' => 404 ) );
        }

        // Verify this performer is part of this conversation.
        if ( (int) $ref_msg->sender_id !== $user_id && (int) $ref_msg->recipient_id !== $user_id ) {
            return new WP_Error( 'forbidden', 'You do not have access to this conversation.', array( 'status' => 403 ) );
        }

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $messages_table
                 WHERE (sender_id = %d AND recipient_id = %d) OR (sender_id = %d AND recipient_id = %d)
                 ORDER BY created_at ASC
                 LIMIT 100",
                $ref_msg->sender_id,
                $ref_msg->recipient_id,
                $ref_msg->recipient_id,
                $ref_msg->sender_id
            )
        );

        // Mark messages as read (where performer is recipient).
        $wpdb->update(
            $messages_table,
            array( 'is_read' => 1 ),
            array(
                'recipient_id' => $user_id,
                'is_read'      => 0,
            )
        );

        $messages = array();
        foreach ( $results as $msg ) {
            $sender         = get_userdata( $msg->sender_id );
            $is_from_me     = ( (int) $msg->sender_id === $user_id );

            $messages[] = array(
                'id'          => (int) $msg->id,
                'sender_id'   => (int) $msg->sender_id,
                'sender_name' => $sender ? $sender->display_name : 'Unknown',
                'content'     => $msg->content,
                'is_read'     => (bool) $msg->is_read,
                'is_from_me'  => $is_from_me,
                'booking_id'  => $msg->booking_id ? (int) $msg->booking_id : null,
                'created_at'  => $msg->created_at,
            );
        }

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => $messages,
                'total'   => count( $messages ),
            )
        );
    }

    /**
     * Get performer payouts.
     */
    public function get_performer_payouts( $request ) {
        global $wpdb;

        $performer_id = (int) $request->get_param( 'id' );
        $status       = sanitize_text_field( $request->get_param( 'status' ) );
        $page         = (int) $request->get_param( 'page' );
        $per_page     = (int) $request->get_param( 'per_page' );
        $offset       = ( $page - 1 ) * $per_page;

        // Verify performer exists.
        $performer = Peanut_Booker_Database::get_row( 'performers', array( 'id' => $performer_id ) );
        if ( ! $performer ) {
            return new WP_Error( 'performer_not_found', 'Performer not found', array( 'status' => 404 ) );
        }

        $bookings_table     = Peanut_Booker_Database::get_table( 'bookings' );
        $transactions_table = Peanut_Booker_Database::get_table( 'transactions' );

        // Build where clauses based on status filter.
        $where_clauses = array( 'b.performer_id = %d' );
        $where_values  = array( $performer_id );

        if ( $status === 'pending' ) {
            $where_clauses[] = "b.booking_status = 'completed' AND b.escrow_status = 'full_held'";
        } elseif ( $status === 'released' ) {
            $where_clauses[] = "b.escrow_status = 'released'";
        } else {
            // All payable bookings (completed or released)
            $where_clauses[] = "(b.booking_status = 'completed' OR b.escrow_status = 'released')";
        }

        $where_sql = implode( ' AND ', $where_clauses );

        // Get payouts.
        $sql = $wpdb->prepare(
            "SELECT b.*, u.display_name as customer_name
             FROM $bookings_table b
             LEFT JOIN {$wpdb->users} u ON b.customer_id = u.ID
             WHERE $where_sql
             ORDER BY b.completion_date DESC
             LIMIT %d OFFSET %d",
            array_merge( $where_values, array( $per_page, $offset ) )
        );

        $results = $wpdb->get_results( $sql );

        // Get total count.
        $count_sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM $bookings_table b WHERE $where_sql",
            $where_values
        );
        $total = (int) $wpdb->get_var( $count_sql );

        // Format payouts.
        $payouts              = array();
        $auto_release_days    = (int) get_option( 'peanut_booker_auto_release_days', 7 );

        foreach ( $results as $booking ) {
            $auto_release_date = null;
            if ( $booking->completion_date && $booking->escrow_status === 'full_held' ) {
                $completion_date   = new DateTime( $booking->completion_date );
                $auto_release_date = $completion_date->modify( "+{$auto_release_days} days" )->format( 'Y-m-d' );
            }

            $payouts[] = array(
                'booking_id'        => (int) $booking->id,
                'booking_number'    => $booking->booking_number,
                'event_title'       => $booking->event_title ?: 'Booking #' . $booking->booking_number,
                'customer_name'     => $booking->customer_name ?: 'Unknown',
                'event_date'        => $booking->event_date,
                'completion_date'   => $booking->completion_date,
                'total_amount'      => (float) $booking->total_amount,
                'commission_amount' => (float) $booking->commission_amount,
                'payout_amount'     => (float) $booking->payout_amount,
                'escrow_status'     => $booking->escrow_status,
                'auto_release_date' => $auto_release_date,
            );
        }

        // Calculate stats.
        $stats_sql = $wpdb->prepare(
            "SELECT
                SUM(CASE WHEN escrow_status = 'released' THEN payout_amount ELSE 0 END) as total_released,
                SUM(CASE WHEN booking_status = 'completed' AND escrow_status = 'full_held' THEN payout_amount ELSE 0 END) as total_pending,
                COUNT(CASE WHEN booking_status = 'completed' AND escrow_status = 'full_held' THEN 1 END) as pending_count,
                COUNT(CASE WHEN escrow_status = 'released' THEN 1 END) as released_count
             FROM $bookings_table
             WHERE performer_id = %d AND (booking_status = 'completed' OR escrow_status = 'released')",
            $performer_id
        );
        $stats = $wpdb->get_row( $stats_sql );

        return rest_ensure_response(
            array(
                'success'     => true,
                'data'        => $payouts,
                'stats'       => array(
                    'total_earned'   => (float) ( $stats->total_released ?? 0 ) + (float) ( $stats->total_pending ?? 0 ),
                    'total_released' => (float) ( $stats->total_released ?? 0 ),
                    'total_pending'  => (float) ( $stats->total_pending ?? 0 ),
                    'pending_count'  => (int) ( $stats->pending_count ?? 0 ),
                    'released_count' => (int) ( $stats->released_count ?? 0 ),
                ),
                'total'       => $total,
                'page'        => $page,
                'per_page'    => $per_page,
                'total_pages' => ceil( $total / $per_page ),
            )
        );
    }

    /**
     * Get performer dashboard overview.
     */
    public function get_performer_overview( $request ) {
        global $wpdb;

        $performer_id = (int) $request->get_param( 'id' );

        // Verify performer exists and get user_id.
        $performer = Peanut_Booker_Database::get_row( 'performers', array( 'id' => $performer_id ) );
        if ( ! $performer ) {
            return new WP_Error( 'performer_not_found', 'Performer not found', array( 'status' => 404 ) );
        }

        $user_id            = $performer->user_id;
        $bookings_table     = Peanut_Booker_Database::get_table( 'bookings' );
        $messages_table     = Peanut_Booker_Database::get_table( 'messages' );
        $reviews_table      = Peanut_Booker_Database::get_table( 'reviews' );
        $microsites_table   = Peanut_Booker_Database::get_table( 'microsites' );

        $today = gmdate( 'Y-m-d' );

        // Get booking stats.
        $booking_stats = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT
                    COUNT(CASE WHEN event_date >= %s AND booking_status IN ('confirmed', 'pending') THEN 1 END) as upcoming,
                    COUNT(CASE WHEN booking_status = 'pending' THEN 1 END) as pending_approval,
                    COUNT(CASE WHEN booking_status = 'completed' THEN 1 END) as completed,
                    SUM(CASE WHEN booking_status = 'completed' THEN payout_amount ELSE 0 END) as total_earned
                 FROM $bookings_table
                 WHERE performer_id = %d",
                $today,
                $performer_id
            )
        );

        // Get pending payout amount.
        $pending_payout = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(payout_amount)
                 FROM $bookings_table
                 WHERE performer_id = %d AND booking_status = 'completed' AND escrow_status = 'full_held'",
                $performer_id
            )
        );

        // Get unread messages count.
        $unread_messages = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*)
                 FROM $messages_table
                 WHERE recipient_id = %d AND is_read = 0",
                $user_id
            )
        );

        // Get recent reviews count (last 30 days).
        $recent_reviews = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*)
                 FROM $reviews_table
                 WHERE reviewee_id = %d AND is_visible = 1 AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
                $user_id
            )
        );

        // Get next upcoming booking.
        $next_booking = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, booking_number, event_title, event_date, event_time, customer_id
                 FROM $bookings_table
                 WHERE performer_id = %d AND event_date >= %s AND booking_status IN ('confirmed', 'pending')
                 ORDER BY event_date ASC, event_time ASC
                 LIMIT 1",
                $performer_id,
                $today
            )
        );

        $next_booking_data = null;
        if ( $next_booking ) {
            $customer          = get_userdata( $next_booking->customer_id );
            $next_booking_data = array(
                'id'             => (int) $next_booking->id,
                'booking_number' => $next_booking->booking_number,
                'event_title'    => $next_booking->event_title ?: 'Booking #' . $next_booking->booking_number,
                'event_date'     => $next_booking->event_date,
                'event_time'     => $next_booking->event_time,
                'customer_name'  => $customer ? $customer->display_name : 'Unknown',
            );
        }

        // Get recent activity (last 5 items).
        $recent_activity = array();

        // Recent bookings.
        $recent_bookings = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 'booking' as type, id, booking_number, event_title, booking_status as status, created_at
                 FROM $bookings_table
                 WHERE performer_id = %d
                 ORDER BY created_at DESC
                 LIMIT 3",
                $performer_id
            )
        );

        foreach ( $recent_bookings as $item ) {
            $recent_activity[] = array(
                'type'        => 'booking',
                'id'          => (int) $item->id,
                'title'       => $item->event_title ?: 'Booking #' . $item->booking_number,
                'status'      => $item->status,
                'created_at'  => $item->created_at,
            );
        }

        // Recent reviews.
        $recent_reviews_list = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 'review' as type, id, rating, content, created_at
                 FROM $reviews_table
                 WHERE reviewee_id = %d AND is_visible = 1
                 ORDER BY created_at DESC
                 LIMIT 2",
                $user_id
            )
        );

        foreach ( $recent_reviews_list as $item ) {
            $recent_activity[] = array(
                'type'       => 'review',
                'id'         => (int) $item->id,
                'title'      => $item->rating . '-star review',
                'content'    => wp_trim_words( $item->content, 10 ),
                'rating'     => (int) $item->rating,
                'created_at' => $item->created_at,
            );
        }

        // Sort by created_at descending.
        usort( $recent_activity, function( $a, $b ) {
            return strtotime( $b['created_at'] ) - strtotime( $a['created_at'] );
        });

        // Limit to 5 items.
        $recent_activity = array_slice( $recent_activity, 0, 5 );

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => array(
                    'bookings'        => array(
                        'upcoming'         => (int) ( $booking_stats->upcoming ?? 0 ),
                        'pending_approval' => (int) ( $booking_stats->pending_approval ?? 0 ),
                        'completed'        => (int) ( $booking_stats->completed ?? 0 ),
                    ),
                    'earnings'        => array(
                        'total'   => (float) ( $booking_stats->total_earned ?? 0 ),
                        'pending' => (float) ( $pending_payout ?? 0 ),
                    ),
                    'messages'        => array(
                        'unread' => (int) ( $unread_messages ?? 0 ),
                    ),
                    'reviews'         => array(
                        'average'       => (float) $performer->average_rating,
                        'total'         => (int) $performer->total_reviews,
                        'recent_count'  => (int) ( $recent_reviews ?? 0 ),
                    ),
                    'next_booking'    => $next_booking_data,
                    'recent_activity' => $recent_activity,
                ),
            )
        );
    }

    /**
     * Admin Bookings.
     */
    public function get_admin_bookings( $request ) {
        global $wpdb;

        $page      = $request->get_param( 'page' );
        $per_page  = $request->get_param( 'per_page' );
        $search    = $request->get_param( 'search' );
        $status    = $request->get_param( 'status' );
        $date_from = $request->get_param( 'date_from' );
        $date_to   = $request->get_param( 'date_to' );
        $offset    = ( $page - 1 ) * $per_page;

        $bookings_table   = Peanut_Booker_Database::get_table( 'bookings' );
        $performers_table = Peanut_Booker_Database::get_table( 'performers' );

        $where_clauses = array( '1=1' );
        $where_values  = array();

        if ( ! empty( $search ) ) {
            $where_clauses[] = '(b.booking_number LIKE %s OR b.event_title LIKE %s)';
            $search_like     = '%' . $wpdb->esc_like( $search ) . '%';
            $where_values[]  = $search_like;
            $where_values[]  = $search_like;
        }

        if ( ! empty( $status ) ) {
            $where_clauses[] = 'b.booking_status = %s';
            $where_values[]  = $status;
        }

        if ( ! empty( $date_from ) ) {
            $where_clauses[] = 'b.event_date >= %s';
            $where_values[]  = $date_from;
        }

        if ( ! empty( $date_to ) ) {
            $where_clauses[] = 'b.event_date <= %s';
            $where_values[]  = $date_to;
        }

        $where_sql = implode( ' AND ', $where_clauses );

        // Get total count.
        $count_sql = "SELECT COUNT(*) FROM $bookings_table b WHERE $where_sql";
        if ( ! empty( $where_values ) ) {
            $count_sql = $wpdb->prepare( $count_sql, $where_values );
        }
        $total = (int) $wpdb->get_var( $count_sql );

        // Get results with performer and customer info.
        $sql = "SELECT b.*,
                       u_customer.display_name as customer_name,
                       u_customer.user_email as customer_email
                FROM $bookings_table b
                LEFT JOIN {$wpdb->users} u_customer ON b.customer_id = u_customer.ID
                WHERE $where_sql
                ORDER BY b.event_date DESC
                LIMIT %d OFFSET %d";

        $query_values = array_merge( $where_values, array( $per_page, $offset ) );
        $sql          = $wpdb->prepare( $sql, $query_values );

        $results = $wpdb->get_results( $sql );

        // Format results.
        $bookings = array();
        foreach ( $results as $booking ) {
            $bookings[] = $this->format_booking( $booking );
        }

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => array(
                    'data'        => $bookings,
                    'total'       => $total,
                    'page'        => $page,
                    'per_page'    => $per_page,
                    'total_pages' => ceil( $total / $per_page ),
                ),
            )
        );
    }

    public function export_bookings( $request ) {
        global $wpdb;

        $status    = $request->get_param( 'status' );
        $date_from = $request->get_param( 'date_from' );
        $date_to   = $request->get_param( 'date_to' );

        $bookings_table = Peanut_Booker_Database::get_table( 'bookings' );

        $where_clauses = array( '1=1' );
        $where_values  = array();

        if ( ! empty( $status ) ) {
            $where_clauses[] = 'b.booking_status = %s';
            $where_values[]  = $status;
        }

        if ( ! empty( $date_from ) ) {
            $where_clauses[] = 'b.event_date >= %s';
            $where_values[]  = $date_from;
        }

        if ( ! empty( $date_to ) ) {
            $where_clauses[] = 'b.event_date <= %s';
            $where_values[]  = $date_to;
        }

        $where_sql = implode( ' AND ', $where_clauses );

        $sql = "SELECT b.*,
                       u_customer.display_name as customer_name,
                       u_customer.user_email as customer_email
                FROM $bookings_table b
                LEFT JOIN {$wpdb->users} u_customer ON b.customer_id = u_customer.ID
                WHERE $where_sql
                ORDER BY b.event_date DESC";

        if ( ! empty( $where_values ) ) {
            $sql = $wpdb->prepare( $sql, $where_values );
        }

        $results = $wpdb->get_results( $sql );

        // Generate CSV.
        $csv_lines = array();
        $csv_lines[] = 'Booking ID,Performer,Customer,Event Date,Location,Total,Commission,Status,Escrow Status';

        foreach ( $results as $booking ) {
            $performer_name = $this->get_performer_name( $booking->performer_id );
            $csv_lines[] = sprintf(
                '"%s","%s","%s","%s","%s, %s",%.2f,%.2f,"%s","%s"',
                $booking->booking_number,
                $performer_name,
                $booking->customer_name,
                $booking->event_date,
                $booking->event_city,
                $booking->event_state,
                $booking->total_amount,
                $booking->commission_amount,
                $booking->booking_status,
                $booking->escrow_status
            );
        }

        $csv_content = implode( "\n", $csv_lines );

        return new WP_REST_Response(
            $csv_content,
            200,
            array(
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="bookings-' . gmdate( 'Y-m-d' ) . '.csv"',
            )
        );
    }

    public function update_booking_status( $request ) {
        $id     = $request['id'];
        $status = $request->get_param( 'status' );

        $valid_statuses = array( 'pending', 'confirmed', 'completed', 'cancelled' );
        if ( ! in_array( $status, $valid_statuses, true ) ) {
            return new WP_Error( 'invalid_status', 'Invalid booking status.', array( 'status' => 400 ) );
        }

        $update_data = array(
            'booking_status' => $status,
            'updated_at'     => current_time( 'mysql' ),
        );

        if ( 'completed' === $status ) {
            $update_data['completion_date'] = current_time( 'mysql' );
        }

        Peanut_Booker_Database::update( 'bookings', $update_data, array( 'id' => $id ) );

        $booking = Peanut_Booker_Database::get_row( 'bookings', array( 'id' => $id ) );

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => $this->format_booking( $booking ),
            )
        );
    }

    public function cancel_booking( $request ) {
        $id     = $request['id'];
        $reason = $request->get_param( 'reason' ) ?: 'Cancelled by admin';

        Peanut_Booker_Database::update(
            'bookings',
            array(
                'booking_status'      => 'cancelled',
                'cancellation_date'   => current_time( 'mysql' ),
                'cancellation_reason' => sanitize_text_field( $reason ),
                'updated_at'          => current_time( 'mysql' ),
            ),
            array( 'id' => $id )
        );

        $booking = Peanut_Booker_Database::get_row( 'bookings', array( 'id' => $id ) );

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => $this->format_booking( $booking ),
            )
        );
    }

    private function format_booking( $booking ) {
        if ( ! $booking ) {
            return null;
        }

        return array(
            'id'                          => (int) $booking->id,
            'booking_number'              => $booking->booking_number,
            'performer_id'                => (int) $booking->performer_id,
            'performer_name'              => $this->get_performer_name( $booking->performer_id ),
            'customer_id'                 => (int) $booking->customer_id,
            'customer_name'               => isset( $booking->customer_name ) ? $booking->customer_name : '',
            'customer_email'              => isset( $booking->customer_email ) ? $booking->customer_email : '',
            'event_title'                 => $booking->event_title,
            'event_description'           => $booking->event_description,
            'event_date'                  => $booking->event_date,
            'event_time_start'            => $booking->event_time_start,
            'event_time_end'              => $booking->event_time_end,
            'event_location'              => $booking->event_location,
            'event_city'                  => $booking->event_city,
            'event_state'                 => $booking->event_state,
            'total_amount'                => (float) $booking->total_amount,
            'deposit_amount'              => (float) $booking->deposit_amount,
            'remaining_amount'            => (float) $booking->remaining_amount,
            'commission_amount'           => (float) $booking->commission_amount,
            'payout_amount'               => (float) $booking->payout_amount,
            'booking_status'              => $booking->booking_status,
            'escrow_status'               => $booking->escrow_status,
            'performer_confirmed'         => (bool) $booking->performer_confirmed,
            'customer_confirmed_completion' => (bool) $booking->customer_confirmed_completion,
            'completion_date'             => $booking->completion_date,
            'payout_date'                 => $booking->payout_date,
            'cancellation_date'           => $booking->cancellation_date,
            'cancellation_reason'         => $booking->cancellation_reason,
            'created_at'                  => $booking->created_at,
            'updated_at'                  => $booking->updated_at,
        );
    }

    private function get_performer_name( $performer_id ) {
        $performer = Peanut_Booker_Database::get_row( 'performers', array( 'id' => $performer_id ) );
        if ( ! $performer ) {
            return '';
        }

        if ( $performer->profile_id ) {
            $stage_name = get_post_meta( $performer->profile_id, '_pb_stage_name', true );
            if ( ! empty( $stage_name ) ) {
                return $stage_name;
            }
            return get_the_title( $performer->profile_id );
        }

        $user = get_userdata( $performer->user_id );
        return $user ? $user->display_name : '';
    }

    /**
     * Admin Market Events.
     */
    public function get_admin_market_events( $request ) {
        global $wpdb;

        $page      = $request->get_param( 'page' );
        $per_page  = $request->get_param( 'per_page' );
        $search    = $request->get_param( 'search' );
        $status    = $request->get_param( 'status' );
        $date_from = $request->get_param( 'date_from' );
        $date_to   = $request->get_param( 'date_to' );
        $offset    = ( $page - 1 ) * $per_page;

        $events_table = Peanut_Booker_Database::get_table( 'events' );

        $where_clauses = array( '1=1' );
        $where_values  = array();

        if ( ! empty( $search ) ) {
            $where_clauses[] = 'e.title LIKE %s';
            $search_like     = '%' . $wpdb->esc_like( $search ) . '%';
            $where_values[]  = $search_like;
        }

        if ( ! empty( $status ) ) {
            $where_clauses[] = 'e.status = %s';
            $where_values[]  = $status;
        }

        if ( ! empty( $date_from ) ) {
            $where_clauses[] = 'e.event_date >= %s';
            $where_values[]  = $date_from;
        }

        if ( ! empty( $date_to ) ) {
            $where_clauses[] = 'e.event_date <= %s';
            $where_values[]  = $date_to;
        }

        $where_sql = implode( ' AND ', $where_clauses );

        // Get total count.
        $count_sql = "SELECT COUNT(*) FROM $events_table e WHERE $where_sql";
        if ( ! empty( $where_values ) ) {
            $count_sql = $wpdb->prepare( $count_sql, $where_values );
        }
        $total = (int) $wpdb->get_var( $count_sql );

        // Get results.
        $sql = "SELECT e.*, u.display_name as customer_name
                FROM $events_table e
                LEFT JOIN {$wpdb->users} u ON e.customer_id = u.ID
                WHERE $where_sql
                ORDER BY e.event_date DESC
                LIMIT %d OFFSET %d";

        $query_values = array_merge( $where_values, array( $per_page, $offset ) );
        $sql          = $wpdb->prepare( $sql, $query_values );

        $results = $wpdb->get_results( $sql );

        // Format results.
        $events = array();
        foreach ( $results as $event ) {
            $events[] = $this->format_market_event( $event );
        }

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => array(
                    'data'        => $events,
                    'total'       => $total,
                    'page'        => $page,
                    'per_page'    => $per_page,
                    'total_pages' => ceil( $total / $per_page ),
                ),
            )
        );
    }

    public function update_market_status( $request ) {
        $id     = $request['id'];
        $status = $request->get_param( 'status' );

        $valid_statuses = array( 'open', 'closed', 'booked', 'completed', 'cancelled' );
        if ( ! in_array( $status, $valid_statuses, true ) ) {
            return new WP_Error( 'invalid_status', 'Invalid event status.', array( 'status' => 400 ) );
        }

        Peanut_Booker_Database::update(
            'events',
            array(
                'status'     => $status,
                'updated_at' => current_time( 'mysql' ),
            ),
            array( 'id' => $id )
        );

        $event = Peanut_Booker_Database::get_row( 'events', array( 'id' => $id ) );

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => $this->format_market_event( $event ),
            )
        );
    }

    private function format_market_event( $event ) {
        if ( ! $event ) {
            return null;
        }

        $category_name = '';
        if ( $event->category_id ) {
            $term = get_term( $event->category_id, 'pb_performer_category' );
            if ( $term && ! is_wp_error( $term ) ) {
                $category_name = $term->name;
            }
        }

        return array(
            'id'                => (int) $event->id,
            'customer_id'       => (int) $event->customer_id,
            'customer_name'     => isset( $event->customer_name ) ? $event->customer_name : '',
            'title'             => $event->title,
            'description'       => $event->description,
            'event_date'        => $event->event_date,
            'event_time_start'  => $event->event_time_start,
            'event_time_end'    => $event->event_time_end,
            'event_location'    => $event->event_location,
            'event_city'        => $event->event_city,
            'event_state'       => $event->event_state,
            'category_id'       => (int) $event->category_id,
            'category_name'     => $category_name,
            'budget_min'        => (float) $event->budget_min,
            'budget_max'        => (float) $event->budget_max,
            'bid_deadline'      => $event->bid_deadline,
            'auto_deadline_days' => (int) $event->auto_deadline_days,
            'total_bids'        => (int) $event->total_bids,
            'accepted_bid_id'   => $event->accepted_bid_id ? (int) $event->accepted_bid_id : null,
            'status'            => $event->status,
            'is_featured'       => (bool) $event->is_featured,
            'created_at'        => $event->created_at,
            'updated_at'        => $event->updated_at,
        );
    }

    /**
     * Admin Reviews.
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

    /**
     * Payouts.
     */
    public function get_payout_stats() {
        global $wpdb;

        $bookings_table = Peanut_Booker_Database::get_table( 'bookings' );

        $pending = $wpdb->get_var(
            "SELECT COALESCE(SUM(payout_amount), 0)
             FROM $bookings_table
             WHERE booking_status = 'completed' AND escrow_status = 'full_held'"
        );

        $released = $wpdb->get_var(
            "SELECT COALESCE(SUM(payout_amount), 0)
             FROM $bookings_table
             WHERE escrow_status = 'released'"
        );

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => array(
                    'pending'  => (float) $pending,
                    'released' => (float) $released,
                ),
            )
        );
    }

    public function get_pending_payouts() {
        global $wpdb;

        $bookings_table = Peanut_Booker_Database::get_table( 'bookings' );

        $sql = "SELECT b.*, u.display_name as customer_name
                FROM $bookings_table b
                LEFT JOIN {$wpdb->users} u ON b.customer_id = u.ID
                WHERE b.booking_status = 'completed' AND b.escrow_status = 'full_held'
                ORDER BY b.completion_date ASC";

        $results = $wpdb->get_results( $sql );

        $payouts = array();
        foreach ( $results as $booking ) {
            $auto_release_days = (int) get_option( 'peanut_booker_auto_release_days', 7 );
            $completion_date   = new DateTime( $booking->completion_date );
            $auto_release_date = $completion_date->modify( "+{$auto_release_days} days" )->format( 'Y-m-d' );

            $payouts[] = array(
                'booking_id'        => (int) $booking->id,
                'booking_number'    => $booking->booking_number,
                'performer_id'      => (int) $booking->performer_id,
                'performer_name'    => $this->get_performer_name( $booking->performer_id ),
                'event_date'        => $booking->event_date,
                'completion_date'   => $booking->completion_date,
                'total_amount'      => (float) $booking->total_amount,
                'commission_amount' => (float) $booking->commission_amount,
                'payout_amount'     => (float) $booking->payout_amount,
                'escrow_status'     => $booking->escrow_status,
                'auto_release_date' => $auto_release_date,
            );
        }

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => $payouts,
            )
        );
    }

    public function release_payout( $request ) {
        $booking_id = $request['booking_id'];

        $booking = Peanut_Booker_Database::get_row( 'bookings', array( 'id' => $booking_id ) );

        if ( ! $booking ) {
            return new WP_Error( 'not_found', 'Booking not found.', array( 'status' => 404 ) );
        }

        if ( 'completed' !== $booking->booking_status ) {
            return new WP_Error( 'invalid_status', 'Booking must be completed to release payout.', array( 'status' => 400 ) );
        }

        if ( 'released' === $booking->escrow_status ) {
            return new WP_Error( 'already_released', 'Payout already released.', array( 'status' => 400 ) );
        }

        Peanut_Booker_Database::update(
            'bookings',
            array(
                'escrow_status' => 'released',
                'payout_date'   => current_time( 'mysql' ),
                'updated_at'    => current_time( 'mysql' ),
            ),
            array( 'id' => $booking_id )
        );

        // Send notification to performer.
        do_action( 'peanut_booker_payout_released', $booking_id );

        return rest_ensure_response(
            array(
                'success' => true,
                'message' => 'Payout released successfully.',
            )
        );
    }

    public function bulk_release_payouts( $request ) {
        $booking_ids = $request->get_param( 'booking_ids' );

        if ( empty( $booking_ids ) || ! is_array( $booking_ids ) ) {
            return new WP_Error( 'invalid_ids', 'Invalid booking IDs.', array( 'status' => 400 ) );
        }

        $released = 0;
        foreach ( $booking_ids as $booking_id ) {
            $booking = Peanut_Booker_Database::get_row( 'bookings', array( 'id' => $booking_id ) );

            if ( $booking && 'completed' === $booking->booking_status && 'held' === $booking->escrow_status ) {
                Peanut_Booker_Database::update(
                    'bookings',
                    array(
                        'escrow_status' => 'released',
                        'payout_date'   => current_time( 'mysql' ),
                        'updated_at'    => current_time( 'mysql' ),
                    ),
                    array( 'id' => $booking_id )
                );
                $released++;

                do_action( 'peanut_booker_payout_released', $booking_id );
            }
        }

        return rest_ensure_response(
            array(
                'success' => true,
                'message' => sprintf( 'Released %d payout(s).', $released ),
            )
        );
    }

    /**
     * Settings.
     */
    public function get_settings() {
        $settings = array(
            'currency'                 => get_option( 'peanut_booker_currency', 'USD' ),
            'woocommerce_active'       => class_exists( 'WooCommerce' ),
            'license_key'              => get_option( 'peanut_booker_license_key', '' ),
            'license_status'           => get_option( 'peanut_booker_license_status', 'inactive' ),
            'license_expires'          => get_option( 'peanut_booker_license_expires', '' ),
            'free_tier_commission'     => (float) get_option( 'peanut_booker_free_commission', 15 ),
            'pro_tier_commission'      => (float) get_option( 'peanut_booker_pro_commission', 10 ),
            'flat_fee_per_transaction' => (float) get_option( 'peanut_booker_flat_fee', 0 ),
            'pro_monthly_price'        => (float) get_option( 'peanut_booker_pro_monthly', 29.99 ),
            'pro_annual_price'         => (float) get_option( 'peanut_booker_pro_annual', 299.99 ),
            'min_deposit_percentage'   => (float) get_option( 'peanut_booker_min_deposit', 25 ),
            'max_deposit_percentage'   => (float) get_option( 'peanut_booker_max_deposit', 100 ),
            'auto_release_escrow_days' => (int) get_option( 'peanut_booker_auto_release_days', 7 ),
            'silver_threshold'         => (int) get_option( 'peanut_booker_silver_threshold', 100 ),
            'gold_threshold'           => (int) get_option( 'peanut_booker_gold_threshold', 500 ),
            'platinum_threshold'       => (int) get_option( 'peanut_booker_platinum_threshold', 1000 ),
            'google_client_id'         => get_option( 'peanut_booker_google_client_id', '' ),
            'google_client_secret'     => '',  // Don't expose secret.
        );

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => $settings,
            )
        );
    }

    public function update_settings( $request ) {
        $data = $request->get_json_params();

        $option_map = array(
            'currency'                 => 'peanut_booker_currency',
            'free_tier_commission'     => 'peanut_booker_free_commission',
            'pro_tier_commission'      => 'peanut_booker_pro_commission',
            'flat_fee_per_transaction' => 'peanut_booker_flat_fee',
            'pro_monthly_price'        => 'peanut_booker_pro_monthly',
            'pro_annual_price'         => 'peanut_booker_pro_annual',
            'min_deposit_percentage'   => 'peanut_booker_min_deposit',
            'max_deposit_percentage'   => 'peanut_booker_max_deposit',
            'auto_release_escrow_days' => 'peanut_booker_auto_release_days',
            'silver_threshold'         => 'peanut_booker_silver_threshold',
            'gold_threshold'           => 'peanut_booker_gold_threshold',
            'platinum_threshold'       => 'peanut_booker_platinum_threshold',
            'google_client_id'         => 'peanut_booker_google_client_id',
            'google_client_secret'     => 'peanut_booker_google_client_secret',
        );

        foreach ( $option_map as $key => $option ) {
            if ( isset( $data[ $key ] ) ) {
                update_option( $option, sanitize_text_field( $data[ $key ] ) );
            }
        }

        return $this->get_settings();
    }

    public function activate_license( $request ) {
        $license_key = $request->get_param( 'license_key' );

        if ( empty( $license_key ) ) {
            return new WP_Error( 'empty_key', 'License key is required.', array( 'status' => 400 ) );
        }

        // Try to activate via Peanut License Server.
        if ( class_exists( 'Peanut_License_Client' ) ) {
            $client = new Peanut_License_Client( 'peanut-booker' );
            $result = $client->activate( $license_key );

            if ( is_wp_error( $result ) ) {
                return $result;
            }

            update_option( 'peanut_booker_license_key', $license_key );
            update_option( 'peanut_booker_license_status', 'active' );

            return rest_ensure_response(
                array(
                    'success' => true,
                    'message' => 'License activated successfully.',
                )
            );
        }

        // Fallback: Just store the key.
        update_option( 'peanut_booker_license_key', $license_key );
        update_option( 'peanut_booker_license_status', 'active' );

        return rest_ensure_response(
            array(
                'success' => true,
                'message' => 'License key saved.',
            )
        );
    }

    public function deactivate_license() {
        if ( class_exists( 'Peanut_License_Client' ) ) {
            $client = new Peanut_License_Client( 'peanut-booker' );
            $client->deactivate();
        }

        delete_option( 'peanut_booker_license_key' );
        update_option( 'peanut_booker_license_status', 'inactive' );
        delete_option( 'peanut_booker_license_expires' );

        return rest_ensure_response(
            array(
                'success' => true,
                'message' => 'License deactivated.',
            )
        );
    }

    /**
     * Demo Mode.
     */
    public function get_demo_status() {
        $enabled = (bool) get_option( 'peanut_booker_demo_mode', false );

        $stats = array();
        if ( $enabled ) {
            $stats = array(
                'performers' => Peanut_Booker_Database::count( 'performers', array( 'status' => 'demo' ) ),
                'customers'  => 0, // Would need to track demo customers.
                'bookings'   => Peanut_Booker_Database::count( 'bookings' ),
                'reviews'    => Peanut_Booker_Database::count( 'reviews' ),
                'events'     => Peanut_Booker_Database::count( 'events' ),
                'bids'       => Peanut_Booker_Database::count( 'bids' ),
            );
        }

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => array(
                    'enabled' => $enabled,
                    'stats'   => $stats,
                ),
            )
        );
    }

    public function enable_demo_mode() {
        // Generate demo data if class exists (this also sets the option to true).
        if ( class_exists( 'Peanut_Booker_Demo_Data' ) ) {
            Peanut_Booker_Demo_Data::enable_demo_mode();
        } else {
            update_option( 'peanut_booker_demo_mode', true );
        }

        return rest_ensure_response(
            array(
                'success' => true,
                'message' => 'Demo mode enabled.',
            )
        );
    }

    public function disable_demo_mode() {
        // Clean up demo data if class exists (this also sets the option to false).
        if ( class_exists( 'Peanut_Booker_Demo_Data' ) ) {
            Peanut_Booker_Demo_Data::disable_demo_mode();
        } else {
            update_option( 'peanut_booker_demo_mode', false );
        }

        return rest_ensure_response(
            array(
                'success' => true,
                'message' => 'Demo mode disabled and data cleaned up.',
            )
        );
    }

    /**
     * Get performer categories.
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

        if ( is_wp_error( $terms ) ) {
            return rest_ensure_response( array() );
        }

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

        if ( is_wp_error( $terms ) ) {
            return rest_ensure_response( array() );
        }

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
     * Admin Microsites.
     */
    public function get_admin_microsites( $request ) {
        global $wpdb;

        $page     = $request->get_param( 'page' );
        $per_page = $request->get_param( 'per_page' );
        $search   = $request->get_param( 'search' );
        $status   = $request->get_param( 'status' );
        $offset   = ( $page - 1 ) * $per_page;

        $microsites_table = Peanut_Booker_Database::get_table( 'microsites' );
        $performers_table = Peanut_Booker_Database::get_table( 'performers' );

        $where_clauses = array( '1=1' );
        $where_values  = array();

        if ( ! empty( $search ) ) {
            $where_clauses[] = '(m.slug LIKE %s OR p.stage_name LIKE %s)';
            $search_like     = '%' . $wpdb->esc_like( $search ) . '%';
            $where_values[]  = $search_like;
            $where_values[]  = $search_like;
        }

        if ( ! empty( $status ) ) {
            $where_clauses[] = 'm.status = %s';
            $where_values[]  = $status;
        }

        $where_sql = implode( ' AND ', $where_clauses );

        // Get total count.
        $count_sql = "SELECT COUNT(*) FROM $microsites_table m
                      LEFT JOIN $performers_table p ON m.performer_id = p.id
                      WHERE $where_sql";
        if ( ! empty( $where_values ) ) {
            $count_sql = $wpdb->prepare( $count_sql, $where_values );
        }
        $total = (int) $wpdb->get_var( $count_sql );

        // Get results.
        $sql = "SELECT m.*, p.profile_id
                FROM $microsites_table m
                LEFT JOIN $performers_table p ON m.performer_id = p.id
                WHERE $where_sql
                ORDER BY m.created_at DESC
                LIMIT %d OFFSET %d";

        $query_values = array_merge( $where_values, array( $per_page, $offset ) );
        $sql          = $wpdb->prepare( $sql, $query_values );

        $results = $wpdb->get_results( $sql );

        // Format results.
        $microsites = array();
        foreach ( $results as $microsite ) {
            $microsites[] = $this->format_microsite( $microsite );
        }

        return rest_ensure_response(
            array(
                'data'        => $microsites,
                'total'       => $total,
                'page'        => $page,
                'per_page'    => $per_page,
                'total_pages' => ceil( $total / $per_page ),
            )
        );
    }

    public function get_admin_microsite( $request ) {
        $microsite = Peanut_Booker_Database::get_row( 'microsites', array( 'id' => $request['id'] ) );

        if ( ! $microsite ) {
            return new WP_Error( 'not_found', 'Microsite not found.', array( 'status' => 404 ) );
        }

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => $this->format_microsite( $microsite ),
            )
        );
    }

    public function update_microsite( $request ) {
        $id   = $request['id'];
        $data = $request->get_json_params();

        // Remove non-updatable fields.
        unset( $data['id'], $data['performer_id'], $data['user_id'], $data['created_at'] );

        // Allowed fields for update.
        $allowed_fields = array(
            'status', 'slug', 'custom_domain', 'domain_verified',
            'design_settings', 'meta_title', 'meta_description',
        );

        $update_data = array_intersect_key( $data, array_flip( $allowed_fields ) );

        if ( isset( $update_data['design_settings'] ) && is_array( $update_data['design_settings'] ) ) {
            $update_data['design_settings'] = wp_json_encode( $update_data['design_settings'] );
        }

        $update_data['updated_at'] = current_time( 'mysql' );

        Peanut_Booker_Database::update( 'microsites', $update_data, array( 'id' => $id ) );

        $microsite = Peanut_Booker_Database::get_row( 'microsites', array( 'id' => $id ) );

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => $this->format_microsite( $microsite ),
            )
        );
    }

    public function update_microsite_status( $request ) {
        $id     = $request['id'];
        $status = $request->get_param( 'status' );

        $valid_statuses = array( 'active', 'pending', 'inactive', 'expired' );
        if ( ! in_array( $status, $valid_statuses, true ) ) {
            return new WP_Error( 'invalid_status', 'Invalid microsite status.', array( 'status' => 400 ) );
        }

        Peanut_Booker_Database::update(
            'microsites',
            array(
                'status'     => $status,
                'updated_at' => current_time( 'mysql' ),
            ),
            array( 'id' => $id )
        );

        $microsite = Peanut_Booker_Database::get_row( 'microsites', array( 'id' => $id ) );

        return rest_ensure_response(
            array(
                'success' => true,
                'data'    => $this->format_microsite( $microsite ),
            )
        );
    }

    public function delete_microsite( $request ) {
        $id = $request['id'];

        $microsite = Peanut_Booker_Database::get_row( 'microsites', array( 'id' => $id ) );

        if ( ! $microsite ) {
            return new WP_Error( 'not_found', 'Microsite not found.', array( 'status' => 404 ) );
        }

        Peanut_Booker_Database::delete( 'microsites', array( 'id' => $id ) );

        return rest_ensure_response(
            array(
                'success' => true,
                'message' => 'Microsite deleted.',
            )
        );
    }

    private function format_microsite( $microsite ) {
        if ( ! $microsite ) {
            return null;
        }

        // Get performer name.
        $performer_name = '';
        $performer      = Peanut_Booker_Database::get_row( 'performers', array( 'id' => $microsite->performer_id ) );
        if ( $performer && $performer->profile_id ) {
            $performer_name = get_post_meta( $performer->profile_id, '_pb_stage_name', true );
            if ( empty( $performer_name ) ) {
                $performer_name = get_the_title( $performer->profile_id );
            }
            // Decode HTML entities for proper display.
            $performer_name = html_entity_decode( $performer_name, ENT_QUOTES, 'UTF-8' );
        }

        // Parse design settings.
        $design_settings = array();
        if ( ! empty( $microsite->design_settings ) ) {
            $design_settings = json_decode( $microsite->design_settings, true );
            if ( ! is_array( $design_settings ) ) {
                $design_settings = array();
            }
        }

        // Default design settings.
        $design_settings = wp_parse_args(
            $design_settings,
            array(
                'template'            => 'classic',
                'primary_color'       => '#3b82f6',
                'secondary_color'     => '#1e40af',
                'background_color'    => '#ffffff',
                'text_color'          => '#1e293b',
                'font_family'         => 'Inter',
                'show_reviews'        => true,
                'show_calendar'       => true,
                'show_booking_button' => true,
                'custom_css'          => '',
            )
        );

        return array(
            'id'                      => (int) $microsite->id,
            'performer_id'            => (int) $microsite->performer_id,
            'performer_name'          => $performer_name,
            'user_id'                 => (int) $microsite->user_id,
            'subscription_id'         => $microsite->subscription_id ? (int) $microsite->subscription_id : null,
            'status'                  => $microsite->status ?: 'pending',
            'slug'                    => $microsite->slug,
            'custom_domain'           => $microsite->custom_domain,
            'domain_verified'         => (bool) $microsite->domain_verified,
            'has_custom_domain_addon' => (bool) $microsite->has_custom_domain_addon,
            'design_settings'         => $design_settings,
            'meta_title'              => $microsite->meta_title,
            'meta_description'        => $microsite->meta_description,
            'view_count'              => (int) $microsite->view_count,
            'created_at'              => $microsite->created_at,
            'updated_at'              => $microsite->updated_at,
        );
    }

    /**
     * Messages/Conversations.
     */
    public function get_admin_conversations( $request ) {
        global $wpdb;

        $page     = $request->get_param( 'page' );
        $per_page = $request->get_param( 'per_page' );
        $search   = $request->get_param( 'search' );
        $offset   = ( $page - 1 ) * $per_page;

        $messages_table = Peanut_Booker_Database::get_table( 'messages' );

        // Build a conversation view from messages.
        $where_clauses = array( '1=1' );
        $where_values  = array();

        if ( ! empty( $search ) ) {
            $where_clauses[] = '(sender_name LIKE %s OR recipient_name LIKE %s)';
            $search_like     = '%' . $wpdb->esc_like( $search ) . '%';
            $where_values[]  = $search_like;
            $where_values[]  = $search_like;
        }

        $where_sql = implode( ' AND ', $where_clauses );

        // Get unique conversations (grouped by sender/recipient pair).
        $sql = "SELECT
                    MIN(id) as id,
                    LEAST(sender_id, recipient_id) as participant_1_id,
                    GREATEST(sender_id, recipient_id) as participant_2_id,
                    MAX(created_at) as last_message_at,
                    booking_id
                FROM $messages_table
                WHERE $where_sql
                GROUP BY LEAST(sender_id, recipient_id), GREATEST(sender_id, recipient_id), booking_id
                ORDER BY last_message_at DESC
                LIMIT %d OFFSET %d";

        $query_values = array_merge( $where_values, array( $per_page, $offset ) );
        if ( ! empty( $query_values ) ) {
            $sql = $wpdb->prepare( $sql, $query_values );
        }

        $results = $wpdb->get_results( $sql );

        // Get total count.
        $count_sql = "SELECT COUNT(DISTINCT CONCAT(LEAST(sender_id, recipient_id), '-', GREATEST(sender_id, recipient_id), '-', COALESCE(booking_id, 0)))
                      FROM $messages_table WHERE $where_sql";
        if ( ! empty( $where_values ) ) {
            $count_sql = $wpdb->prepare( $count_sql, $where_values );
        }
        $total = (int) $wpdb->get_var( $count_sql );

        // Format results.
        $conversations = array();
        foreach ( $results as $row ) {
            $user1     = get_userdata( $row->participant_1_id );
            $user2     = get_userdata( $row->participant_2_id );
            $user1_type = $this->get_user_type( $row->participant_1_id );
            $user2_type = $this->get_user_type( $row->participant_2_id );

            // Get last message.
            $last_msg = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT content FROM $messages_table
                     WHERE (sender_id = %d AND recipient_id = %d) OR (sender_id = %d AND recipient_id = %d)
                     ORDER BY created_at DESC LIMIT 1",
                    $row->participant_1_id,
                    $row->participant_2_id,
                    $row->participant_2_id,
                    $row->participant_1_id
                )
            );

            // Get unread count.
            $unread = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $messages_table
                     WHERE recipient_id IN (%d, %d) AND is_read = 0
                     AND ((sender_id = %d AND recipient_id = %d) OR (sender_id = %d AND recipient_id = %d))",
                    $row->participant_1_id,
                    $row->participant_2_id,
                    $row->participant_1_id,
                    $row->participant_2_id,
                    $row->participant_2_id,
                    $row->participant_1_id
                )
            );

            $conversations[] = array(
                'id'                   => (int) $row->id,
                'participant_1_id'     => (int) $row->participant_1_id,
                'participant_1_name'   => $user1 ? $user1->display_name : 'Unknown',
                'participant_1_type'   => $user1_type,
                'participant_2_id'     => (int) $row->participant_2_id,
                'participant_2_name'   => $user2 ? $user2->display_name : 'Unknown',
                'participant_2_type'   => $user2_type,
                'last_message'         => $last_msg ? wp_trim_words( $last_msg, 15 ) : null,
                'last_message_at'      => $row->last_message_at,
                'unread_count'         => (int) $unread,
                'booking_id'           => $row->booking_id ? (int) $row->booking_id : null,
                'created_at'           => $row->last_message_at,
            );
        }

        return rest_ensure_response(
            array(
                'data'        => $conversations,
                'total'       => $total,
                'page'        => $page,
                'per_page'    => $per_page,
                'total_pages' => ceil( $total / $per_page ),
            )
        );
    }

    public function get_conversation_messages( $request ) {
        global $wpdb;

        $conversation_id = $request['id'];
        $messages_table  = Peanut_Booker_Database::get_table( 'messages' );

        // Get a reference message to find participants.
        $ref_msg = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT sender_id, recipient_id FROM $messages_table WHERE id = %d",
                $conversation_id
            )
        );

        if ( ! $ref_msg ) {
            return new WP_Error( 'not_found', 'Conversation not found.', array( 'status' => 404 ) );
        }

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $messages_table
                 WHERE (sender_id = %d AND recipient_id = %d) OR (sender_id = %d AND recipient_id = %d)
                 ORDER BY created_at ASC
                 LIMIT 100",
                $ref_msg->sender_id,
                $ref_msg->recipient_id,
                $ref_msg->recipient_id,
                $ref_msg->sender_id
            )
        );

        $messages = array();
        foreach ( $results as $msg ) {
            $sender = get_userdata( $msg->sender_id );
            $messages[] = array(
                'id'              => (int) $msg->id,
                'conversation_id' => $conversation_id,
                'sender_id'       => (int) $msg->sender_id,
                'sender_name'     => $sender ? $sender->display_name : 'Unknown',
                'sender_type'     => $this->get_user_type( $msg->sender_id ),
                'recipient_id'    => (int) $msg->recipient_id,
                'recipient_name'  => get_userdata( $msg->recipient_id )->display_name ?? 'Unknown',
                'content'         => $msg->content,
                'is_read'         => (bool) $msg->is_read,
                'booking_id'      => $msg->booking_id ? (int) $msg->booking_id : null,
                'created_at'      => $msg->created_at,
            );
        }

        return rest_ensure_response(
            array(
                'data'        => $messages,
                'total'       => count( $messages ),
                'page'        => 1,
                'per_page'    => 100,
                'total_pages' => 1,
            )
        );
    }

    private function get_user_type( $user_id ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return 'unknown';
        }
        if ( in_array( 'pb_performer', (array) $user->roles, true ) ) {
            return 'performer';
        }
        return 'customer';
    }

    /**
     * Customers.
     */
    public function get_admin_customers( $request ) {
        global $wpdb;

        $page     = $request->get_param( 'page' );
        $per_page = $request->get_param( 'per_page' );
        $search   = $request->get_param( 'search' );
        $offset   = ( $page - 1 ) * $per_page;

        $bookings_table = Peanut_Booker_Database::get_table( 'bookings' );

        // Get customers from WordPress users with customer role or who have bookings.
        $args = array(
            'role__in' => array( 'pb_customer', 'subscriber' ),
            'number'   => $per_page,
            'offset'   => $offset,
            'orderby'  => 'registered',
            'order'    => 'DESC',
        );

        if ( ! empty( $search ) ) {
            $args['search']         = '*' . $search . '*';
            $args['search_columns'] = array( 'user_login', 'user_email', 'display_name' );
        }

        $user_query = new WP_User_Query( $args );
        $users      = $user_query->get_results();
        $total      = $user_query->get_total();

        $customers = array();
        foreach ( $users as $user ) {
            // Get booking stats.
            $stats = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT COUNT(*) as total_bookings, SUM(total_amount) as total_spent, MAX(event_date) as last_booking
                     FROM $bookings_table WHERE customer_id = %d",
                    $user->ID
                )
            );

            $customers[] = array(
                'id'                => (int) $user->ID,
                'user_id'           => (int) $user->ID,
                'email'             => $user->user_email,
                'display_name'      => $user->display_name,
                'first_name'        => get_user_meta( $user->ID, 'first_name', true ),
                'last_name'         => get_user_meta( $user->ID, 'last_name', true ),
                'phone'             => Peanut_Booker_Encryption::decrypt( get_user_meta( $user->ID, 'billing_phone', true ) ),
                'total_bookings'    => (int) ( $stats->total_bookings ?? 0 ),
                'total_spent'       => (float) ( $stats->total_spent ?? 0 ),
                'last_booking_date' => $stats->last_booking ?? null,
                'created_at'        => $user->user_registered,
            );
        }

        return rest_ensure_response(
            array(
                'data'        => $customers,
                'total'       => $total,
                'page'        => $page,
                'per_page'    => $per_page,
                'total_pages' => ceil( $total / $per_page ),
            )
        );
    }

    public function get_admin_customer( $request ) {
        $user = get_userdata( $request['id'] );

        if ( ! $user ) {
            return new WP_Error( 'not_found', 'Customer not found.', array( 'status' => 404 ) );
        }

        global $wpdb;
        $bookings_table = Peanut_Booker_Database::get_table( 'bookings' );

        $stats = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT COUNT(*) as total_bookings, SUM(total_amount) as total_spent, MAX(event_date) as last_booking
                 FROM $bookings_table WHERE customer_id = %d",
                $user->ID
            )
        );

        return rest_ensure_response(
            array(
                'id'                => (int) $user->ID,
                'user_id'           => (int) $user->ID,
                'email'             => $user->user_email,
                'display_name'      => $user->display_name,
                'first_name'        => get_user_meta( $user->ID, 'first_name', true ),
                'last_name'         => get_user_meta( $user->ID, 'last_name', true ),
                'phone'             => Peanut_Booker_Encryption::decrypt( get_user_meta( $user->ID, 'billing_phone', true ) ),
                'total_bookings'    => (int) ( $stats->total_bookings ?? 0 ),
                'total_spent'       => (float) ( $stats->total_spent ?? 0 ),
                'last_booking_date' => $stats->last_booking ?? null,
                'created_at'        => $user->user_registered,
            )
        );
    }

    /**
     * Analytics.
     */
    public function get_analytics_overview() {
        global $wpdb;

        $bookings_table   = Peanut_Booker_Database::get_table( 'bookings' );
        $performers_table = Peanut_Booker_Database::get_table( 'performers' );

        // Revenue stats.
        $total_revenue = (float) $wpdb->get_var( "SELECT SUM(total_amount) FROM $bookings_table WHERE booking_status = 'completed'" );

        $this_month_start = date( 'Y-m-01' );
        $this_month_revenue = (float) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(total_amount) FROM $bookings_table WHERE booking_status = 'completed' AND created_at >= %s",
                $this_month_start
            )
        );

        $last_month_start = date( 'Y-m-01', strtotime( '-1 month' ) );
        $last_month_end   = date( 'Y-m-t', strtotime( '-1 month' ) );
        $last_month_revenue = (float) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(total_amount) FROM $bookings_table WHERE booking_status = 'completed' AND created_at >= %s AND created_at <= %s",
                $last_month_start,
                $last_month_end
            )
        );

        $growth = $last_month_revenue > 0
            ? round( ( ( $this_month_revenue - $last_month_revenue ) / $last_month_revenue ) * 100, 1 )
            : 0;

        // Booking stats.
        $total_bookings     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $bookings_table" );
        $completed_bookings = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $bookings_table WHERE booking_status = 'completed'" );
        $pending_bookings   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $bookings_table WHERE booking_status = 'pending'" );
        $cancelled_bookings = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $bookings_table WHERE booking_status = 'cancelled'" );
        $completion_rate    = $total_bookings > 0 ? round( ( $completed_bookings / $total_bookings ) * 100, 1 ) : 0;

        // Performer stats.
        $total_performers    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $performers_table" );
        $active_performers   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $performers_table WHERE status = 'active'" );
        $verified_performers = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $performers_table WHERE is_verified = 1" );
        $pro_performers      = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $performers_table WHERE tier = 'pro'" );

        // Customer stats.
        $customer_query = new WP_User_Query( array( 'role__in' => array( 'pb_customer', 'subscriber' ) ) );
        $total_customers = $customer_query->get_total();

        $active_customer_query = new WP_User_Query(
            array(
                'role__in'   => array( 'pb_customer', 'subscriber' ),
                'date_query' => array( array( 'after' => '3 months ago' ) ),
            )
        );
        $active_customers = $active_customer_query->get_total();

        $new_customer_query = new WP_User_Query(
            array(
                'role__in'   => array( 'pb_customer', 'subscriber' ),
                'date_query' => array( array( 'after' => $this_month_start ) ),
            )
        );
        $new_customers = $new_customer_query->get_total();

        // Top performers.
        $top_performers = $wpdb->get_results(
            "SELECT p.id, p.profile_id, COUNT(b.id) as bookings, SUM(b.total_amount) as revenue, p.average_rating as rating
             FROM $performers_table p
             LEFT JOIN $bookings_table b ON p.id = b.performer_id AND b.booking_status = 'completed'
             GROUP BY p.id
             ORDER BY revenue DESC
             LIMIT 5"
        );

        $formatted_top = array();
        foreach ( $top_performers as $perf ) {
            $name = get_post_meta( $perf->profile_id, '_pb_stage_name', true );
            if ( empty( $name ) && $perf->profile_id ) {
                $name = get_the_title( $perf->profile_id );
            }
            $formatted_top[] = array(
                'id'       => (int) $perf->id,
                'name'     => $name ?: 'Unknown',
                'bookings' => (int) $perf->bookings,
                'revenue'  => (float) ( $perf->revenue ?? 0 ),
                'rating'   => (float) ( $perf->rating ?? 0 ),
            );
        }

        // Recent activity.
        $recent_bookings = $wpdb->get_results(
            "SELECT id, booking_number, performer_name, total_amount, created_at
             FROM $bookings_table
             ORDER BY created_at DESC
             LIMIT 5"
        );

        $recent_activity = array();
        foreach ( $recent_bookings as $booking ) {
            $recent_activity[] = array(
                'id'          => (int) $booking->id,
                'type'        => 'booking',
                'description' => "New booking #{$booking->booking_number} with {$booking->performer_name}",
                'amount'      => (float) $booking->total_amount,
                'created_at'  => $booking->created_at,
            );
        }

        return rest_ensure_response(
            array(
                'revenue'    => array(
                    'total'             => $total_revenue ?: 0,
                    'this_month'        => $this_month_revenue ?: 0,
                    'last_month'        => $last_month_revenue ?: 0,
                    'growth_percentage' => $growth,
                ),
                'bookings'   => array(
                    'total'           => $total_bookings,
                    'completed'       => $completed_bookings,
                    'pending'         => $pending_bookings,
                    'cancelled'       => $cancelled_bookings,
                    'completion_rate' => $completion_rate,
                ),
                'performers' => array(
                    'total'    => $total_performers,
                    'active'   => $active_performers,
                    'verified' => $verified_performers,
                    'pro_tier' => $pro_performers,
                ),
                'customers'  => array(
                    'total'          => $total_customers,
                    'active'         => $active_customers,
                    'new_this_month' => $new_customers,
                ),
                'top_performers'   => $formatted_top,
                'recent_activity'  => $recent_activity,
            )
        );
    }
}
