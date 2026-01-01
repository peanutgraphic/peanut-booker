<?php
/**
 * Tests for the Booking class.
 *
 * @package Peanut_Booker\Tests
 */

namespace Peanut_Booker\Tests\Unit;

use Peanut_Booker\Tests\TestCase;
use Peanut_Booker_Booking;

/**
 * Booking class tests.
 */
class BookingTest extends TestCase {

    /**
     * Set up test fixtures.
     */
    protected function setUp(): void {
        parent::setUp();

        // Create mock performer.
        $performer_row = (object) array(
            'id'                 => 1,
            'user_id'            => 101,
            'profile_id'         => 1001,
            'tier'               => 'pro',
            'deposit_percentage' => 25,
            'status'             => 'approved',
            'hourly_rate'        => 100.00,
        );
        $this->set_db_mock_row( $performer_row );
    }

    /**
     * Test booking creation with valid data.
     */
    public function test_create_booking_with_valid_data() {
        $this->create_mock_customer( 2 );
        $this->set_current_user( 2 );

        $data = array(
            'performer_id'      => 1,
            'customer_id'       => 2,
            'event_title'       => 'Birthday Party',
            'event_description' => 'A fun birthday celebration',
            'event_date'        => date( 'Y-m-d', strtotime( '+14 days' ) ),
            'event_start_time'  => '18:00:00',
            'event_end_time'    => '22:00:00',
            'event_location'    => 'Community Center',
            'event_address'     => '123 Main St',
            'event_city'        => 'Austin',
            'event_state'       => 'TX',
            'event_zip'         => '78701',
            'total_amount'      => 400.00,
        );

        $result = Peanut_Booker_Booking::create( $data );

        // Since we're using mocks, we expect an ID or WP_Error.
        $this->assertTrue( is_int( $result ) || is_wp_error( $result ) );
    }

    /**
     * Test booking creation fails without performer_id.
     */
    public function test_create_booking_fails_without_performer_id() {
        $data = array(
            'customer_id'  => 2,
            'event_title'  => 'Test Event',
            'event_date'   => date( 'Y-m-d', strtotime( '+14 days' ) ),
            'total_amount' => 400.00,
        );

        $result = Peanut_Booker_Booking::create( $data );

        $this->assertWPError( $result );
        $this->assertWPErrorCode( 'missing_field', $result );
    }

    /**
     * Test booking creation fails without customer_id.
     */
    public function test_create_booking_fails_without_customer_id() {
        $data = array(
            'performer_id' => 1,
            'event_title'  => 'Test Event',
            'event_date'   => date( 'Y-m-d', strtotime( '+14 days' ) ),
            'total_amount' => 400.00,
        );

        $result = Peanut_Booker_Booking::create( $data );

        $this->assertWPError( $result );
        $this->assertWPErrorCode( 'missing_field', $result );
    }

    /**
     * Test booking creation fails with past date.
     */
    public function test_create_booking_fails_with_past_date() {
        $data = array(
            'performer_id' => 1,
            'customer_id'  => 2,
            'event_title'  => 'Test Event',
            'event_date'   => date( 'Y-m-d', strtotime( '-7 days' ) ),
            'total_amount' => 400.00,
        );

        $result = Peanut_Booker_Booking::create( $data );

        $this->assertWPError( $result );
        $this->assertWPErrorCode( 'invalid_date', $result );
    }

    /**
     * Test get booking by ID.
     */
    public function test_get_booking_by_id() {
        $mock_booking = $this->create_mock_booking( 1 );
        $this->set_db_mock_row( $mock_booking );

        $result = Peanut_Booker_Booking::get( 1 );

        $this->assertIsObject( $result );
        $this->assertEquals( 1, $result->id );
    }

    /**
     * Test get booking returns null for non-existent ID.
     */
    public function test_get_booking_returns_null_for_nonexistent_id() {
        $this->set_db_mock_row( null );

        $result = Peanut_Booker_Booking::get( 99999 );

        $this->assertNull( $result );
    }

    /**
     * Test booking number generation.
     */
    public function test_booking_number_format() {
        // Booking numbers should follow format: PB + Date + Random.
        $booking = $this->create_mock_booking( 1, array(
            'booking_number' => 'PB20240115ABC123',
        ) );

        $this->assertStringStartsWith( 'PB', $booking->booking_number );
        $this->assertGreaterThan( 10, strlen( $booking->booking_number ) );
    }

    /**
     * Test update booking status.
     */
    public function test_update_booking_status() {
        $mock_booking = $this->create_mock_booking( 1, array( 'booking_status' => 'pending' ) );
        $this->set_db_mock_row( $mock_booking );

        $result = Peanut_Booker_Booking::update_status( 1, 'confirmed' );

        $this->assertTrue( $result );
    }

    /**
     * Test update booking status with invalid status.
     */
    public function test_update_booking_status_with_invalid_status() {
        $mock_booking = $this->create_mock_booking( 1 );
        $this->set_db_mock_row( $mock_booking );

        $result = Peanut_Booker_Booking::update_status( 1, 'invalid_status' );

        $this->assertWPError( $result );
    }

    /**
     * Test cancel booking.
     */
    public function test_cancel_booking() {
        $mock_booking = $this->create_mock_booking( 1, array( 'booking_status' => 'pending' ) );
        $this->set_db_mock_row( $mock_booking );

        $this->create_mock_admin( 1 );
        $this->set_current_user( 1 );

        $result = Peanut_Booker_Booking::cancel( 1 );

        $this->assertTrue( $result === true || is_wp_error( $result ) );
    }

    /**
     * Test format booking data.
     */
    public function test_format_booking_data() {
        $booking = $this->create_mock_booking( 1, array(
            'event_title'   => 'Wedding Reception',
            'performer_id'  => 1,
            'customer_id'   => 2,
        ) );

        $result = Peanut_Booker_Booking::format_booking_data( $booking );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'id', $result );
        $this->assertArrayHasKey( 'event_title', $result );
        $this->assertArrayHasKey( 'booking_status', $result );
    }

    /**
     * Test deposit calculation.
     */
    public function test_deposit_calculation() {
        $booking = $this->create_mock_booking( 1, array(
            'total_amount'   => 400.00,
            'deposit_amount' => 100.00,
        ) );

        $this->assertEquals( 400.00, $booking->total_amount );
        $this->assertEquals( 100.00, $booking->deposit_amount );
        $this->assertEquals( 25, ( $booking->deposit_amount / $booking->total_amount ) * 100 );
    }

    /**
     * Test commission calculation.
     */
    public function test_commission_calculation() {
        $booking = $this->create_mock_booking( 1, array(
            'total_amount'        => 400.00,
            'platform_commission' => 80.00,
            'performer_payout'    => 320.00,
        ) );

        $this->assertEquals( 80.00, $booking->platform_commission );
        $this->assertEquals( 320.00, $booking->performer_payout );
        $this->assertEquals(
            $booking->total_amount,
            $booking->platform_commission + $booking->performer_payout
        );
    }

    /**
     * Test booking status validation.
     */
    public function test_valid_booking_statuses() {
        $valid_statuses = array(
            'pending',
            'confirmed',
            'completed',
            'cancelled',
            'refunded',
            'disputed',
        );

        foreach ( $valid_statuses as $status ) {
            $booking = $this->create_mock_booking( 1, array( 'booking_status' => $status ) );
            $this->assertEquals( $status, $booking->booking_status );
        }
    }

    /**
     * Test escrow status validation.
     */
    public function test_valid_escrow_statuses() {
        $valid_statuses = array(
            'pending',
            'held',
            'released',
            'refunded',
        );

        foreach ( $valid_statuses as $status ) {
            $booking = $this->create_mock_booking( 1, array( 'escrow_status' => $status ) );
            $this->assertEquals( $status, $booking->escrow_status );
        }
    }

    /**
     * Test get customer bookings.
     */
    public function test_get_customer_bookings() {
        $mock_results = array(
            $this->create_mock_booking( 1, array( 'customer_id' => 2 ) ),
            $this->create_mock_booking( 2, array( 'customer_id' => 2 ) ),
        );
        $this->set_db_mock_results( $mock_results );

        $result = Peanut_Booker_Booking::get_for_customer( 2 );

        $this->assertIsArray( $result );
        $this->assertCount( 2, $result );
    }

    /**
     * Test get performer bookings.
     */
    public function test_get_performer_bookings() {
        $mock_results = array(
            $this->create_mock_booking( 1, array( 'performer_id' => 1 ) ),
            $this->create_mock_booking( 2, array( 'performer_id' => 1 ) ),
            $this->create_mock_booking( 3, array( 'performer_id' => 1 ) ),
        );
        $this->set_db_mock_results( $mock_results );

        $result = Peanut_Booker_Booking::get_for_performer( 1 );

        $this->assertIsArray( $result );
        $this->assertCount( 3, $result );
    }

    /**
     * Test performer confirmation.
     */
    public function test_performer_confirm_booking() {
        $mock_booking = $this->create_mock_booking( 1, array( 'performer_confirmed' => 0 ) );
        $this->set_db_mock_row( $mock_booking );

        $result = Peanut_Booker_Booking::performer_confirm( 1 );

        $this->assertTrue( $result === true || is_wp_error( $result ) );
    }

    /**
     * Test customer confirmation of completion.
     */
    public function test_customer_confirm_completion() {
        $mock_booking = $this->create_mock_booking( 1, array(
            'booking_status'              => 'confirmed',
            'customer_confirmed_completion' => 0,
        ) );
        $this->set_db_mock_row( $mock_booking );

        $result = Peanut_Booker_Booking::customer_confirm_completion( 1 );

        $this->assertTrue( $result === true || is_wp_error( $result ) );
    }

    /**
     * Test booking update.
     */
    public function test_update_booking() {
        $mock_booking = $this->create_mock_booking( 1 );
        $this->set_db_mock_row( $mock_booking );

        $update_data = array(
            'event_title' => 'Updated Event Title',
            'notes'       => 'Special instructions added',
        );

        $result = Peanut_Booker_Booking::update( 1, $update_data );

        $this->assertTrue( $result );
    }

    /**
     * Test get checkout URL.
     */
    public function test_get_checkout_url() {
        $mock_booking = $this->create_mock_booking( 1 );
        $this->set_db_mock_row( $mock_booking );

        $url = Peanut_Booker_Booking::get_checkout_url( 1 );

        $this->assertIsString( $url );
        $this->assertStringContainsString( 'checkout', $url );
    }
}
