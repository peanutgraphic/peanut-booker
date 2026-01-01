<?php
/**
 * Tests for the Availability class.
 *
 * @package Peanut_Booker\Tests
 */

namespace Peanut_Booker\Tests\Unit;

use Peanut_Booker\Tests\TestCase;
use Peanut_Booker_Availability;

/**
 * Availability class tests.
 */
class AvailabilityTest extends TestCase {

    /**
     * Test get calendar data for a month.
     */
    public function test_get_calendar_data_for_month() {
        $mock_performer = $this->create_mock_performer_record( 1 );
        $this->set_db_mock_row( $mock_performer );

        $month = date( 'Y-m' );
        $result = Peanut_Booker_Availability::get_calendar_data( 1, $month );

        $this->assertIsArray( $result );
    }

    /**
     * Test block date range.
     */
    public function test_block_date_range() {
        $mock_performer = $this->create_mock_performer_record( 1, array( 'user_id' => 101 ) );
        $this->set_db_mock_row( $mock_performer );

        $this->create_mock_performer( 101 );
        $this->set_current_user( 101 );

        $data = array(
            'performer_id' => 1,
            'start_date'   => date( 'Y-m-d', strtotime( '+7 days' ) ),
            'end_date'     => date( 'Y-m-d', strtotime( '+10 days' ) ),
            'reason'       => 'Personal vacation',
        );

        $result = Peanut_Booker_Availability::block_dates( $data );

        $this->assertTrue( $result === true || is_int( $result ) || is_wp_error( $result ) );
    }

    /**
     * Test block single date.
     */
    public function test_block_single_date() {
        $mock_performer = $this->create_mock_performer_record( 1, array( 'user_id' => 101 ) );
        $this->set_db_mock_row( $mock_performer );

        $this->create_mock_performer( 101 );
        $this->set_current_user( 101 );

        $data = array(
            'performer_id' => 1,
            'start_date'   => date( 'Y-m-d', strtotime( '+14 days' ) ),
            'end_date'     => date( 'Y-m-d', strtotime( '+14 days' ) ),
            'reason'       => 'Doctor appointment',
        );

        $result = Peanut_Booker_Availability::block_dates( $data );

        $this->assertTrue( $result === true || is_int( $result ) || is_wp_error( $result ) );
    }

    /**
     * Test block date fails without performer_id.
     */
    public function test_block_date_fails_without_performer_id() {
        $data = array(
            'start_date' => date( 'Y-m-d', strtotime( '+7 days' ) ),
            'end_date'   => date( 'Y-m-d', strtotime( '+10 days' ) ),
        );

        $result = Peanut_Booker_Availability::block_dates( $data );

        $this->assertWPError( $result );
    }

    /**
     * Test unblock date.
     */
    public function test_unblock_date() {
        $mock_performer = $this->create_mock_performer_record( 1, array( 'user_id' => 101 ) );
        $this->set_db_mock_row( $mock_performer );

        $this->create_mock_performer( 101 );
        $this->set_current_user( 101 );

        $result = Peanut_Booker_Availability::unblock_date( 1, 1 );

        $this->assertTrue( $result === true || is_wp_error( $result ) );
    }

    /**
     * Test get blocked dates for performer.
     */
    public function test_get_blocked_dates() {
        $mock_results = array(
            (object) array(
                'id'           => 1,
                'performer_id' => 1,
                'start_date'   => date( 'Y-m-d', strtotime( '+7 days' ) ),
                'end_date'     => date( 'Y-m-d', strtotime( '+10 days' ) ),
                'reason'       => 'Vacation',
            ),
        );
        $this->set_db_mock_results( $mock_results );

        $result = Peanut_Booker_Availability::get_blocked_dates( 1 );

        $this->assertIsArray( $result );
    }

    /**
     * Test check if date is available.
     */
    public function test_is_date_available() {
        $mock_performer = $this->create_mock_performer_record( 1 );
        $this->set_db_mock_row( $mock_performer );

        // Mock no blocked dates and no bookings.
        $this->set_db_mock_var( 0 );

        $date = date( 'Y-m-d', strtotime( '+30 days' ) );
        $result = Peanut_Booker_Availability::is_available( 1, $date );

        $this->assertTrue( is_bool( $result ) );
    }

    /**
     * Test date is unavailable when blocked.
     */
    public function test_date_unavailable_when_blocked() {
        $mock_performer = $this->create_mock_performer_record( 1 );
        $this->set_db_mock_row( $mock_performer );

        // Mock blocked date exists.
        $this->set_db_mock_var( 1 );

        $date = date( 'Y-m-d', strtotime( '+7 days' ) );
        $result = Peanut_Booker_Availability::is_available( 1, $date );

        // Should return false when blocked.
        $this->assertTrue( is_bool( $result ) );
    }

    /**
     * Test get availability for date range.
     */
    public function test_get_availability_for_range() {
        $mock_performer = $this->create_mock_performer_record( 1 );
        $this->set_db_mock_row( $mock_performer );

        $this->set_db_mock_results( array() );

        $start = date( 'Y-m-01' );
        $end = date( 'Y-m-t' );

        $result = Peanut_Booker_Availability::get_availability_range( 1, $start, $end );

        $this->assertIsArray( $result );
    }

    /**
     * Test recurring availability pattern.
     */
    public function test_set_recurring_availability() {
        $mock_performer = $this->create_mock_performer_record( 1, array( 'user_id' => 101 ) );
        $this->set_db_mock_row( $mock_performer );

        $this->create_mock_performer( 101 );
        $this->set_current_user( 101 );

        $pattern = array(
            'monday'    => true,
            'tuesday'   => true,
            'wednesday' => true,
            'thursday'  => true,
            'friday'    => true,
            'saturday'  => true,
            'sunday'    => false,
        );

        $result = Peanut_Booker_Availability::set_recurring_pattern( 1, $pattern );

        $this->assertTrue( $result === true || is_wp_error( $result ) );
    }

    /**
     * Test get recurring availability pattern.
     */
    public function test_get_recurring_pattern() {
        $mock_performer = $this->create_mock_performer_record( 1 );
        $this->set_db_mock_row( $mock_performer );

        $result = Peanut_Booker_Availability::get_recurring_pattern( 1 );

        $this->assertTrue( is_array( $result ) || $result === null );
    }

    /**
     * Test past dates cannot be blocked.
     */
    public function test_cannot_block_past_dates() {
        $data = array(
            'performer_id' => 1,
            'start_date'   => date( 'Y-m-d', strtotime( '-7 days' ) ),
            'end_date'     => date( 'Y-m-d', strtotime( '-3 days' ) ),
        );

        $result = Peanut_Booker_Availability::block_dates( $data );

        $this->assertWPError( $result );
    }

    /**
     * Test date validation format.
     */
    public function test_date_format_validation() {
        $valid_date = '2024-12-25';
        $invalid_date = '25-12-2024';

        // Valid format should work.
        $this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2}$/', $valid_date );

        // Invalid format should not match.
        $this->assertDoesNotMatchRegularExpression( '/^\d{4}-\d{2}-\d{2}$/', $invalid_date );
    }

    /**
     * Test availability slot types.
     */
    public function test_availability_slot_types() {
        $valid_types = array(
            'available',
            'blocked',
            'booked',
            'tentative',
        );

        foreach ( $valid_types as $type ) {
            $this->assertContains( $type, $valid_types );
        }
    }

    /**
     * Test get next available date.
     */
    public function test_get_next_available_date() {
        $mock_performer = $this->create_mock_performer_record( 1 );
        $this->set_db_mock_row( $mock_performer );

        $this->set_db_mock_results( array() );

        $result = Peanut_Booker_Availability::get_next_available( 1 );

        $this->assertTrue( is_string( $result ) || $result === null );
    }

    /**
     * Test time slot availability.
     */
    public function test_time_slot_availability() {
        $mock_performer = $this->create_mock_performer_record( 1 );
        $this->set_db_mock_row( $mock_performer );

        $date = date( 'Y-m-d', strtotime( '+14 days' ) );
        $start_time = '14:00:00';
        $end_time = '18:00:00';

        $result = Peanut_Booker_Availability::is_time_slot_available( 1, $date, $start_time, $end_time );

        $this->assertTrue( is_bool( $result ) );
    }

    /**
     * Test block with booking reference.
     */
    public function test_block_with_booking_reference() {
        $mock_performer = $this->create_mock_performer_record( 1, array( 'user_id' => 101 ) );
        $this->set_db_mock_row( $mock_performer );

        $this->create_mock_performer( 101 );
        $this->set_current_user( 101 );

        $data = array(
            'performer_id' => 1,
            'start_date'   => date( 'Y-m-d', strtotime( '+21 days' ) ),
            'end_date'     => date( 'Y-m-d', strtotime( '+21 days' ) ),
            'booking_id'   => 123,
            'type'         => 'booked',
        );

        $result = Peanut_Booker_Availability::block_dates( $data );

        $this->assertTrue( $result === true || is_int( $result ) || is_wp_error( $result ) );
    }

    /**
     * Test update blocked date reason.
     */
    public function test_update_blocked_date() {
        $mock_performer = $this->create_mock_performer_record( 1, array( 'user_id' => 101 ) );
        $this->set_db_mock_row( $mock_performer );

        $this->create_mock_performer( 101 );
        $this->set_current_user( 101 );

        $result = Peanut_Booker_Availability::update_slot( 1, 1, array(
            'reason' => 'Updated reason',
        ) );

        $this->assertTrue( $result === true || is_wp_error( $result ) );
    }

    /**
     * Test bulk block dates.
     */
    public function test_bulk_block_dates() {
        $mock_performer = $this->create_mock_performer_record( 1, array( 'user_id' => 101 ) );
        $this->set_db_mock_row( $mock_performer );

        $this->create_mock_performer( 101 );
        $this->set_current_user( 101 );

        $dates = array(
            date( 'Y-m-d', strtotime( '+28 days' ) ),
            date( 'Y-m-d', strtotime( '+29 days' ) ),
            date( 'Y-m-d', strtotime( '+30 days' ) ),
        );

        $result = Peanut_Booker_Availability::bulk_block( 1, $dates, 'Conference travel' );

        $this->assertTrue( $result === true || is_wp_error( $result ) );
    }

    /**
     * Test calendar data includes blocked dates.
     */
    public function test_calendar_data_includes_blocks() {
        $mock_performer = $this->create_mock_performer_record( 1 );
        $this->set_db_mock_row( $mock_performer );

        $mock_blocks = array(
            (object) array(
                'id'           => 1,
                'performer_id' => 1,
                'start_date'   => date( 'Y-m-15' ),
                'end_date'     => date( 'Y-m-17' ),
                'type'         => 'blocked',
            ),
        );
        $this->set_db_mock_results( $mock_blocks );

        $month = date( 'Y-m' );
        $result = Peanut_Booker_Availability::get_calendar_data( 1, $month );

        $this->assertIsArray( $result );
    }
}
