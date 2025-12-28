<?php
/**
 * Booking wizard functionality.
 *
 * @package Peanut_Booker
 * @since   1.6.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Booking wizard class.
 */
class Peanut_Booker_Booking_Wizard {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_pb_wizard_validate_step', array( $this, 'ajax_validate_step' ) );
		add_action( 'wp_ajax_nopriv_pb_wizard_validate_step', array( $this, 'ajax_validate_step' ) );
		add_action( 'wp_ajax_pb_wizard_create_booking', array( $this, 'ajax_create_booking' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue wizard scripts and styles.
	 */
	public function enqueue_scripts() {
		if ( is_singular( 'pb_performer' ) || is_page() ) {
			wp_enqueue_style(
				'pb-booking-wizard',
				PEANUT_BOOKER_URL . 'assets/css/booking-wizard.css',
				array(),
				PEANUT_BOOKER_VERSION
			);

			wp_enqueue_script(
				'pb-booking-wizard',
				PEANUT_BOOKER_URL . 'assets/js/booking-wizard.js',
				array( 'jquery' ),
				PEANUT_BOOKER_VERSION,
				true
			);

			wp_localize_script(
				'pb-booking-wizard',
				'pbWizard',
				array(
					'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
					'nonce'      => wp_create_nonce( 'pb_wizard_nonce' ),
					'i18n'       => array(
						'selectService'  => __( 'Please select a service', 'peanut-booker' ),
						'selectDate'     => __( 'Please select a date', 'peanut-booker' ),
						'dateNotAvailable' => __( 'Selected date is not available', 'peanut-booker' ),
						'fillRequired'   => __( 'Please fill in all required fields', 'peanut-booker' ),
						'bookingError'   => __( 'An error occurred. Please try again.', 'peanut-booker' ),
					),
				)
			);
		}
	}

	/**
	 * Validate a wizard step.
	 */
	public function ajax_validate_step() {
		check_ajax_referer( 'pb_wizard_nonce', 'nonce' );

		$step         = absint( $_POST['step'] ?? 1 );
		$performer_id = absint( $_POST['performer_id'] ?? 0 );
		$data         = isset( $_POST['data'] ) ? $_POST['data'] : array();

		$validation = $this->validate_step_data( $step, $performer_id, $data );

		if ( is_wp_error( $validation ) ) {
			wp_send_json_error( array( 'message' => $validation->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Step validated successfully', 'peanut-booker' ),
				'data'    => $validation,
			)
		);
	}

	/**
	 * Validate step data.
	 *
	 * @param int   $step         Step number.
	 * @param int   $performer_id Performer ID.
	 * @param array $data         Step data.
	 * @return array|WP_Error Validated data or error.
	 */
	private function validate_step_data( $step, $performer_id, $data ) {
		switch ( $step ) {
			case 1:
				return $this->validate_step_1( $performer_id, $data );
			case 2:
				return $this->validate_step_2( $performer_id, $data );
			case 3:
				return $this->validate_step_3( $performer_id, $data );
			default:
				return new WP_Error( 'invalid_step', __( 'Invalid step', 'peanut-booker' ) );
		}
	}

	/**
	 * Validate step 1: Service selection.
	 *
	 * @param int   $performer_id Performer ID.
	 * @param array $data         Step data.
	 * @return array|WP_Error Validated data or error.
	 */
	private function validate_step_1( $performer_id, $data ) {
		$performer = Peanut_Booker_Performer::get( $performer_id );
		if ( ! $performer ) {
			return new WP_Error( 'invalid_performer', __( 'Invalid performer', 'peanut-booker' ) );
		}

		$service_type = sanitize_text_field( $data['service_type'] ?? '' );
		$category     = sanitize_text_field( $data['category'] ?? '' );

		if ( empty( $service_type ) ) {
			return new WP_Error( 'missing_service', __( 'Please select a service type', 'peanut-booker' ) );
		}

		// Get performer's hourly rate.
		$profile_id  = $performer->profile_id;
		$hourly_rate = get_post_meta( $profile_id, 'pb_hourly_rate', true );
		$sale_active = get_post_meta( $profile_id, 'pb_sale_active', true );
		$sale_price  = get_post_meta( $profile_id, 'pb_sale_price', true );

		$rate = $sale_active && $sale_price ? floatval( $sale_price ) : floatval( $hourly_rate );

		return array(
			'service_type'     => $service_type,
			'category'         => $category,
			'hourly_rate'      => $rate,
			'deposit_percentage' => $performer->deposit_percentage,
		);
	}

	/**
	 * Validate step 2: Date/time selection.
	 *
	 * @param int   $performer_id Performer ID.
	 * @param array $data         Step data.
	 * @return array|WP_Error Validated data or error.
	 */
	private function validate_step_2( $performer_id, $data ) {
		$event_date       = sanitize_text_field( $data['event_date'] ?? '' );
		$event_start_time = sanitize_text_field( $data['event_start_time'] ?? '' );
		$event_end_time   = sanitize_text_field( $data['event_end_time'] ?? '' );
		$duration_hours   = absint( $data['duration_hours'] ?? 0 );
		$event_location   = sanitize_text_field( $data['event_location'] ?? '' );
		$event_description = sanitize_textarea_field( $data['event_description'] ?? '' );

		if ( empty( $event_date ) ) {
			return new WP_Error( 'missing_date', __( 'Please select an event date', 'peanut-booker' ) );
		}

		// Validate date is in the future.
		if ( strtotime( $event_date ) < strtotime( 'today' ) ) {
			return new WP_Error( 'invalid_date', __( 'Event date must be in the future', 'peanut-booker' ) );
		}

		// Check availability.
		$available = Peanut_Booker_Availability::is_available(
			$performer_id,
			$event_date,
			$event_start_time,
			$event_end_time
		);

		if ( ! $available ) {
			return new WP_Error( 'not_available', __( 'Performer is not available on this date/time', 'peanut-booker' ) );
		}

		if ( empty( $event_start_time ) || $duration_hours < 1 ) {
			return new WP_Error( 'missing_time', __( 'Please select start time and duration', 'peanut-booker' ) );
		}

		// Calculate end time if not provided.
		if ( empty( $event_end_time ) && $duration_hours > 0 ) {
			$start_timestamp = strtotime( $event_date . ' ' . $event_start_time );
			$end_timestamp   = strtotime( "+{$duration_hours} hours", $start_timestamp );
			$event_end_time  = gmdate( 'H:i', $end_timestamp );
		}

		return array(
			'event_date'        => $event_date,
			'event_start_time'  => $event_start_time,
			'event_end_time'    => $event_end_time,
			'duration_hours'    => $duration_hours,
			'event_location'    => $event_location,
			'event_description' => $event_description,
		);
	}

	/**
	 * Validate step 3: Review and confirm.
	 *
	 * @param int   $performer_id Performer ID.
	 * @param array $data         Step data.
	 * @return array|WP_Error Validated data or error.
	 */
	private function validate_step_3( $performer_id, $data ) {
		// Validate all previous data is present.
		$required = array( 'service_type', 'event_date', 'event_start_time', 'duration_hours' );

		foreach ( $required as $field ) {
			if ( empty( $data[ $field ] ) ) {
				return new WP_Error(
					'incomplete_data',
					sprintf( __( 'Missing required field: %s', 'peanut-booker' ), $field )
				);
			}
		}

		// Additional contact info.
		$contact_phone = sanitize_text_field( $data['contact_phone'] ?? '' );
		$special_notes = sanitize_textarea_field( $data['special_notes'] ?? '' );

		return array(
			'contact_phone' => $contact_phone,
			'special_notes' => $special_notes,
		);
	}

	/**
	 * AJAX: Create booking from wizard.
	 */
	public function ajax_create_booking() {
		check_ajax_referer( 'pb_wizard_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in to book', 'peanut-booker' ) ) );
		}

		$performer_id = absint( $_POST['performer_id'] ?? 0 );
		$wizard_data  = isset( $_POST['wizard_data'] ) ? $_POST['wizard_data'] : array();

		// Validate all steps.
		$step1 = $this->validate_step_1( $performer_id, $wizard_data );
		if ( is_wp_error( $step1 ) ) {
			wp_send_json_error( array( 'message' => $step1->get_error_message() ) );
		}

		$step2 = $this->validate_step_2( $performer_id, $wizard_data );
		if ( is_wp_error( $step2 ) ) {
			wp_send_json_error( array( 'message' => $step2->get_error_message() ) );
		}

		$step3 = $this->validate_step_3( $performer_id, $wizard_data );
		if ( is_wp_error( $step3 ) ) {
			wp_send_json_error( array( 'message' => $step3->get_error_message() ) );
		}

		// Calculate total amount.
		$total_amount = $step1['hourly_rate'] * $step2['duration_hours'];

		// Prepare booking data.
		$booking_data = array(
			'performer_id'      => $performer_id,
			'customer_id'       => get_current_user_id(),
			'event_title'       => $step1['service_type'],
			'event_description' => $step2['event_description'] . "\n\n" . $step3['special_notes'],
			'event_date'        => $step2['event_date'],
			'event_start_time'  => $step2['event_start_time'],
			'event_end_time'    => $step2['event_end_time'],
			'event_location'    => $step2['event_location'],
			'total_amount'      => $total_amount,
			'notes'             => $step3['special_notes'],
		);

		// Create booking.
		$booking_id = Peanut_Booker_Booking::create( $booking_data );

		if ( is_wp_error( $booking_id ) ) {
			wp_send_json_error( array( 'message' => $booking_id->get_error_message() ) );
		}

		$booking = Peanut_Booker_Booking::get( $booking_id );

		wp_send_json_success(
			array(
				'message'        => __( 'Booking created successfully!', 'peanut-booker' ),
				'booking_id'     => $booking_id,
				'booking_number' => $booking->booking_number,
				'checkout_url'   => Peanut_Booker_Booking::get_checkout_url( $booking_id ),
			)
		);
	}

	/**
	 * Render wizard HTML.
	 *
	 * @param int $performer_id Performer ID.
	 * @return string HTML.
	 */
	public static function render( $performer_id ) {
		$performer = Peanut_Booker_Performer::get( $performer_id );
		if ( ! $performer ) {
			return '';
		}

		$display_data = Peanut_Booker_Performer::get_display_data( $performer->profile_id );

		ob_start();
		include PEANUT_BOOKER_PATH . 'templates/booking-wizard.php';
		return ob_get_clean();
	}

	/**
	 * Get performer's service categories.
	 *
	 * @param int $profile_id Profile post ID.
	 * @return array Service categories.
	 */
	public static function get_service_categories( $profile_id ) {
		$categories = wp_get_post_terms(
			$profile_id,
			'pb_performer_category',
			array(
				'fields' => 'all',
			)
		);

		if ( is_wp_error( $categories ) ) {
			return array();
		}

		return $categories;
	}
}
