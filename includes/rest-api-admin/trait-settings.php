<?php
/**
 * Settings-related REST API admin endpoints.
 *
 * This trait contains all settings management endpoints for the admin REST API.
 * It is designed to be used by Peanut_Booker_REST_API_Admin class.
 *
 * Methods included:
 * - Route registration
 * - Get settings (get_settings)
 * - Update settings (update_settings)
 * - License management (activate_license, deactivate_license)
 * - Demo mode (get_demo_status, enable_demo_mode, disable_demo_mode)
 * - Categories and service areas (get_categories, get_service_areas)
 *
 * @package Peanut_Booker
 * @since   2.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Trait for settings admin endpoints.
 */
trait Peanut_Booker_REST_Admin_Settings {

	/**
	 * Register settings-related routes.
	 *
	 * Called from main register_routes() method.
	 */
	protected function register_settings_routes() {
		// Get settings.
		register_rest_route(
			$this->namespace,
			'/admin/settings',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_settings' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		// Update settings.
		register_rest_route(
			$this->namespace,
			'/admin/settings',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'update_settings' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		// Activate license.
		register_rest_route(
			$this->namespace,
			'/admin/license/activate',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'activate_license' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		// Deactivate license.
		register_rest_route(
			$this->namespace,
			'/admin/license/deactivate',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'deactivate_license' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		// Demo mode status.
		register_rest_route(
			$this->namespace,
			'/admin/demo',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_demo_status' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		// Enable demo mode.
		register_rest_route(
			$this->namespace,
			'/admin/demo/enable',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'enable_demo_mode' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		// Disable demo mode.
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

		// Service areas.
		register_rest_route(
			$this->namespace,
			'/admin/service-areas',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_service_areas' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);
	}

	/**
	 * Get all plugin settings.
	 *
	 * @return WP_REST_Response Response object.
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

	/**
	 * Update plugin settings.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
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

	/**
	 * Activate license.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
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

	/**
	 * Deactivate license.
	 *
	 * @return WP_REST_Response Response object.
	 */
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
	 * Get demo mode status.
	 *
	 * @return WP_REST_Response Response object.
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

	/**
	 * Enable demo mode.
	 *
	 * @return WP_REST_Response Response object.
	 */
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

	/**
	 * Disable demo mode.
	 *
	 * @return WP_REST_Response Response object.
	 */
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
	 * @return WP_REST_Response Response object.
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
	 * @return WP_REST_Response Response object.
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
}
