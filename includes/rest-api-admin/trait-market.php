<?php
/**
 * Market event-related REST API admin endpoints.
 *
 * This trait contains all market event management endpoints for the admin REST API.
 * It is designed to be used by Peanut_Booker_REST_API_Admin class.
 *
 * Methods included:
 * - Route registration
 * - Listing market events (get_admin_market_events)
 * - Update event status (update_market_status)
 * - Format market event (format_market_event)
 *
 * @package Peanut_Booker
 * @since   2.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Trait for market event admin endpoints.
 */
trait Peanut_Booker_REST_Admin_Market {

	/**
	 * Register market-related routes.
	 *
	 * Called from main register_routes() method.
	 */
	protected function register_market_routes() {
		// Admin market events list.
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

		// Update market event status.
		register_rest_route(
			$this->namespace,
			'/admin/market/(?P<id>\d+)/status',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'update_market_status' ),
				'permission_callback' => array( $this, 'check_manage_market' ),
			)
		);
	}

	/**
	 * Get market event query parameters.
	 *
	 * @return array Query parameters.
	 */
	protected function get_market_params() {
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

	/**
	 * Get admin market events list.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
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

	/**
	 * Update market event status.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
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

	/**
	 * Format market event for API response.
	 *
	 * @param object $event Event database row.
	 * @return array|null Formatted event data or null.
	 */
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
			'id'                 => (int) $event->id,
			'customer_id'        => (int) $event->customer_id,
			'customer_name'      => isset( $event->customer_name ) ? $event->customer_name : '',
			'title'              => $event->title,
			'description'        => $event->description,
			'event_date'         => $event->event_date,
			'event_time_start'   => $event->event_time_start,
			'event_time_end'     => $event->event_time_end,
			'event_location'     => $event->event_location,
			'event_city'         => $event->event_city,
			'event_state'        => $event->event_state,
			'category_id'        => (int) $event->category_id,
			'category_name'      => $category_name,
			'budget_min'         => (float) $event->budget_min,
			'budget_max'         => (float) $event->budget_max,
			'bid_deadline'       => $event->bid_deadline,
			'auto_deadline_days' => (int) $event->auto_deadline_days,
			'total_bids'         => (int) $event->total_bids,
			'accepted_bid_id'    => $event->accepted_bid_id ? (int) $event->accepted_bid_id : null,
			'status'             => $event->status,
			'is_featured'        => (bool) $event->is_featured,
			'created_at'         => $event->created_at,
			'updated_at'         => $event->updated_at,
		);
	}
}
