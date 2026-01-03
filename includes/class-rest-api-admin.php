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

// Load REST API Admin traits.
require_once __DIR__ . '/rest-api-admin/trait-performers.php';
require_once __DIR__ . '/rest-api-admin/trait-bookings.php';
require_once __DIR__ . '/rest-api-admin/trait-reviews.php';
require_once __DIR__ . '/rest-api-admin/trait-market.php';
require_once __DIR__ . '/rest-api-admin/trait-payouts.php';
require_once __DIR__ . '/rest-api-admin/trait-settings.php';
require_once __DIR__ . '/rest-api-admin/trait-microsites.php';
require_once __DIR__ . '/rest-api-admin/trait-conversations.php';

/**
 * Admin REST API class.
 */
class Peanut_Booker_REST_API_Admin {

    use Peanut_Booker_REST_Admin_Performers;
    use Peanut_Booker_REST_Admin_Bookings;
    use Peanut_Booker_REST_Admin_Reviews;
    use Peanut_Booker_REST_Admin_Market;
    use Peanut_Booker_REST_Admin_Payouts;
    use Peanut_Booker_REST_Admin_Settings;
    use Peanut_Booker_REST_Admin_Microsites;
    use Peanut_Booker_REST_Admin_Conversations;

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

}
