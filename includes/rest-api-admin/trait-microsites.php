<?php
/**
 * Microsite-related REST API admin endpoints.
 *
 * This trait contains all microsite management endpoints for the admin REST API.
 * It is designed to be used by Peanut_Booker_REST_API_Admin class.
 *
 * Methods included:
 * - Route registration
 * - Listing microsites (get_admin_microsites)
 * - Getting single microsite (get_admin_microsite)
 * - Updating microsite (update_microsite)
 * - Update microsite status (update_microsite_status)
 * - Delete microsite (delete_microsite)
 *
 * @package Peanut_Booker
 * @since   2.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Trait for microsite admin endpoints.
 */
trait Peanut_Booker_REST_Admin_Microsites {

	/**
	 * Register microsite-related routes.
	 *
	 * Called from main register_routes() method.
	 */
	protected function register_microsite_routes() {
		// Admin microsites list.
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

		// Single microsite.
		register_rest_route(
			$this->namespace,
			'/admin/microsites/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_admin_microsite' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		// Update microsite.
		register_rest_route(
			$this->namespace,
			'/admin/microsites/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_microsite' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		// Update microsite status.
		register_rest_route(
			$this->namespace,
			'/admin/microsites/(?P<id>\d+)/status',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'update_microsite_status' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		// Delete microsite.
		register_rest_route(
			$this->namespace,
			'/admin/microsites/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_microsite' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);
	}

	/**
	 * Get microsite query parameters.
	 *
	 * @return array Query parameters.
	 */
	protected function get_microsite_params() {
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

	/**
	 * Get admin microsites list.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
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

	/**
	 * Get single admin microsite.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
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

	/**
	 * Update a microsite.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
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

	/**
	 * Update microsite status.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
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

	/**
	 * Delete a microsite.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
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

	/**
	 * Format microsite for API response.
	 *
	 * @param object $microsite Microsite database row.
	 * @return array|null Formatted microsite data or null.
	 */
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
}
