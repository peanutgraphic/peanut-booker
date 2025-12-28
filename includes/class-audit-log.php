<?php
/**
 * Audit logging for security and compliance.
 *
 * @package Peanut_Booker
 * @since   1.3.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Audit log class for tracking sensitive operations.
 */
class Peanut_Booker_Audit_Log {

	/**
	 * Constructor - register hooks.
	 */
	public function __construct() {
		// Booking events.
		add_action( 'peanut_booker_booking_created', array( $this, 'log_booking_created' ), 10, 2 );
		add_action( 'peanut_booker_booking_status_changed', array( $this, 'log_booking_status_changed' ), 10, 3 );
		add_action( 'peanut_booker_booking_updated', array( $this, 'log_booking_updated' ), 10, 2 );

		// Escrow events.
		add_action( 'peanut_booker_escrow_released', array( $this, 'log_escrow_released' ), 10, 2 );
		add_action( 'peanut_booker_booking_refunded', array( $this, 'log_refund' ), 10, 2 );

		// Payment events.
		add_action( 'peanut_booker_payment_received', array( $this, 'log_payment_received' ), 10, 3 );

		// User role changes.
		add_action( 'set_user_role', array( $this, 'log_role_change' ), 10, 3 );

		// Performer status changes.
		add_action( 'peanut_booker_performer_status_changed', array( $this, 'log_performer_status' ), 10, 3 );
	}

	/**
	 * Log an event to the audit table.
	 *
	 * @param string $entity_type Type of entity (booking, transaction, user, performer).
	 * @param int    $entity_id   ID of the entity.
	 * @param string $action      Action performed.
	 * @param mixed  $old_values  Previous values (will be JSON encoded).
	 * @param mixed  $new_values  New values (will be JSON encoded).
	 * @return int|false Insert ID or false on failure.
	 */
	public static function log( $entity_type, $entity_id, $action, $old_values = null, $new_values = null ) {
		global $wpdb;

		$table = $wpdb->prefix . 'pb_audit_log';

		$data = array(
			'entity_type' => sanitize_text_field( $entity_type ),
			'entity_id'   => absint( $entity_id ),
			'action'      => sanitize_text_field( $action ),
			'old_values'  => $old_values ? wp_json_encode( $old_values ) : null,
			'new_values'  => $new_values ? wp_json_encode( $new_values ) : null,
			'user_id'     => get_current_user_id() ?: null,
			'ip_address'  => self::get_client_ip(),
			'user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] )
				? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 500 )
				: null,
			'created_at'  => current_time( 'mysql' ),
		);

		$result = $wpdb->insert( $table, $data );

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Get the client IP address.
	 *
	 * @return string|null
	 */
	private static function get_client_ip() {
		$ip_keys = array(
			'HTTP_CF_CONNECTING_IP', // Cloudflare.
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_REAL_IP',
			'REMOTE_ADDR',
		);

		foreach ( $ip_keys as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
				// Handle comma-separated IPs (X-Forwarded-For).
				if ( strpos( $ip, ',' ) !== false ) {
					$ip = trim( explode( ',', $ip )[0] );
				}
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return null;
	}

	/**
	 * Log booking creation.
	 *
	 * @param int   $booking_id   Booking ID.
	 * @param array $booking_data Booking data.
	 */
	public function log_booking_created( $booking_id, $booking_data ) {
		self::log(
			'booking',
			$booking_id,
			'created',
			null,
			array(
				'booking_number' => $booking_data['booking_number'] ?? null,
				'performer_id'   => $booking_data['performer_id'] ?? null,
				'customer_id'    => $booking_data['customer_id'] ?? null,
				'total_amount'   => $booking_data['total_amount'] ?? null,
				'event_date'     => $booking_data['event_date'] ?? null,
			)
		);
	}

	/**
	 * Log booking status change.
	 *
	 * @param int    $booking_id Booking ID.
	 * @param string $new_status New status.
	 * @param string $old_status Old status.
	 */
	public function log_booking_status_changed( $booking_id, $new_status, $old_status ) {
		self::log(
			'booking',
			$booking_id,
			'status_changed',
			array( 'booking_status' => $old_status ),
			array( 'booking_status' => $new_status )
		);
	}

	/**
	 * Log booking update.
	 *
	 * @param int   $booking_id Booking ID.
	 * @param array $data       Updated data.
	 */
	public function log_booking_updated( $booking_id, $data ) {
		// Filter sensitive data.
		$filtered_data = array_filter( $data, function( $key ) {
			// Only log safe fields.
			$safe_fields = array(
				'event_title', 'event_date', 'event_location', 'booking_status',
				'escrow_status', 'deposit_paid', 'fully_paid', 'performer_confirmed',
				'customer_confirmed_completion', 'notes',
			);
			return in_array( $key, $safe_fields, true );
		}, ARRAY_FILTER_USE_KEY );

		if ( ! empty( $filtered_data ) ) {
			self::log(
				'booking',
				$booking_id,
				'updated',
				null,
				$filtered_data
			);
		}
	}

	/**
	 * Log escrow release.
	 *
	 * @param int   $booking_id Booking ID.
	 * @param float $amount     Amount released.
	 */
	public function log_escrow_released( $booking_id, $amount ) {
		self::log(
			'booking',
			$booking_id,
			'escrow_released',
			null,
			array(
				'amount'        => $amount,
				'escrow_status' => 'released',
			)
		);
	}

	/**
	 * Log refund.
	 *
	 * @param int   $booking_id    Booking ID.
	 * @param float $refund_amount Refund amount.
	 */
	public function log_refund( $booking_id, $refund_amount ) {
		self::log(
			'booking',
			$booking_id,
			'refunded',
			null,
			array(
				'refund_amount' => $refund_amount,
				'escrow_status' => 'refunded',
			)
		);
	}

	/**
	 * Log payment received.
	 *
	 * @param int  $booking_id Booking ID.
	 * @param int  $order_id   WooCommerce order ID.
	 * @param bool $is_deposit Whether this is a deposit payment.
	 */
	public function log_payment_received( $booking_id, $order_id, $is_deposit ) {
		$order = wc_get_order( $order_id );
		$amount = $order ? $order->get_total() : 0;

		self::log(
			'transaction',
			$order_id,
			$is_deposit ? 'deposit_received' : 'payment_received',
			null,
			array(
				'booking_id'     => $booking_id,
				'amount'         => $amount,
				'payment_method' => $order ? $order->get_payment_method() : null,
			)
		);
	}

	/**
	 * Log user role change.
	 *
	 * @param int    $user_id   User ID.
	 * @param string $new_role  New role.
	 * @param array  $old_roles Old roles.
	 */
	public function log_role_change( $user_id, $new_role, $old_roles ) {
		// Only log peanut-booker related roles.
		$pb_roles = array( 'pb_performer', 'pb_customer', 'administrator' );

		if ( in_array( $new_role, $pb_roles, true ) || array_intersect( $old_roles, $pb_roles ) ) {
			self::log(
				'user',
				$user_id,
				'role_changed',
				array( 'roles' => $old_roles ),
				array( 'role' => $new_role )
			);
		}
	}

	/**
	 * Log performer status change.
	 *
	 * @param int    $performer_id Performer ID.
	 * @param string $new_status   New status.
	 * @param string $old_status   Old status.
	 */
	public function log_performer_status( $performer_id, $new_status, $old_status ) {
		self::log(
			'performer',
			$performer_id,
			'status_changed',
			array( 'status' => $old_status ),
			array( 'status' => $new_status )
		);
	}

	/**
	 * Get audit log entries for an entity.
	 *
	 * @param string $entity_type Entity type.
	 * @param int    $entity_id   Entity ID.
	 * @param int    $limit       Number of entries to return.
	 * @return array
	 */
	public static function get_for_entity( $entity_type, $entity_id, $limit = 50 ) {
		global $wpdb;

		$table = $wpdb->prefix . 'pb_audit_log';

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table WHERE entity_type = %s AND entity_id = %d ORDER BY created_at DESC LIMIT %d",
				$entity_type,
				$entity_id,
				$limit
			)
		);

		// Decode JSON values.
		foreach ( $results as $row ) {
			$row->old_values = $row->old_values ? json_decode( $row->old_values, true ) : null;
			$row->new_values = $row->new_values ? json_decode( $row->new_values, true ) : null;
		}

		return $results;
	}

	/**
	 * Get recent audit log entries.
	 *
	 * @param int    $limit       Number of entries to return.
	 * @param string $entity_type Optional. Filter by entity type.
	 * @param string $action      Optional. Filter by action.
	 * @return array
	 */
	public static function get_recent( $limit = 100, $entity_type = null, $action = null ) {
		global $wpdb;

		$table = $wpdb->prefix . 'pb_audit_log';
		$where = array( '1=1' );
		$values = array();

		if ( $entity_type ) {
			$where[] = 'entity_type = %s';
			$values[] = $entity_type;
		}

		if ( $action ) {
			$where[] = 'action = %s';
			$values[] = $action;
		}

		$values[] = $limit;
		$where_clause = implode( ' AND ', $where );

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table WHERE $where_clause ORDER BY created_at DESC LIMIT %d",
				$values
			)
		);

		// Decode JSON values.
		foreach ( $results as $row ) {
			$row->old_values = $row->old_values ? json_decode( $row->old_values, true ) : null;
			$row->new_values = $row->new_values ? json_decode( $row->new_values, true ) : null;
		}

		return $results;
	}

	/**
	 * Cleanup old audit log entries.
	 * Keep entries for 90 days by default.
	 *
	 * @param int $days Number of days to retain. Default 90.
	 * @return int Number of entries deleted.
	 */
	public static function cleanup( $days = 90 ) {
		global $wpdb;

		$table = $wpdb->prefix . 'pb_audit_log';
		$cutoff = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		return $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $table WHERE created_at < %s",
				$cutoff
			)
		);
	}
}
