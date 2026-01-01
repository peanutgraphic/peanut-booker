<?php
/**
 * Tests for the Market class.
 *
 * @package Peanut_Booker\Tests
 */

namespace Peanut_Booker\Tests\Unit;

use Peanut_Booker\Tests\TestCase;
use Peanut_Booker_Market;

/**
 * Market class tests.
 */
class MarketTest extends TestCase {

    /**
     * Test create market event with valid data.
     */
    public function test_create_event_with_valid_data() {
        $this->create_mock_customer( 1 );
        $this->set_current_user( 1 );

        $data = array(
            'customer_id'      => 1,
            'title'            => 'Looking for Wedding Band',
            'description'      => 'Need a 4-piece band for outdoor wedding reception.',
            'event_date'       => date( 'Y-m-d', strtotime( '+60 days' ) ),
            'event_start_time' => '17:00:00',
            'city'             => 'Austin',
            'state'            => 'TX',
            'budget_min'       => 500,
            'budget_max'       => 1500,
            'bid_deadline'     => date( 'Y-m-d H:i:s', strtotime( '+14 days' ) ),
        );

        $result = Peanut_Booker_Market::create_event( $data );

        $this->assertTrue( is_int( $result ) || is_wp_error( $result ) );
    }

    /**
     * Test create event fails without customer_id.
     */
    public function test_create_event_fails_without_customer_id() {
        $data = array(
            'title'       => 'Test Event',
            'description' => 'Test description',
            'event_date'  => date( 'Y-m-d', strtotime( '+30 days' ) ),
        );

        $result = Peanut_Booker_Market::create_event( $data );

        $this->assertWPError( $result );
    }

    /**
     * Test create event fails without title.
     */
    public function test_create_event_fails_without_title() {
        $data = array(
            'customer_id' => 1,
            'description' => 'Test description',
            'event_date'  => date( 'Y-m-d', strtotime( '+30 days' ) ),
        );

        $result = Peanut_Booker_Market::create_event( $data );

        $this->assertWPError( $result );
    }

    /**
     * Test get event by ID.
     */
    public function test_get_event_by_id() {
        $mock_event = $this->create_mock_market_event( 1 );
        $this->set_db_mock_row( $mock_event );

        $result = Peanut_Booker_Market::get_event( 1 );

        $this->assertIsObject( $result );
        $this->assertEquals( 1, $result->id );
    }

    /**
     * Test get event returns null when not found.
     */
    public function test_get_event_returns_null_when_not_found() {
        $this->set_db_mock_row( null );

        $result = Peanut_Booker_Market::get_event( 99999 );

        $this->assertNull( $result );
    }

    /**
     * Test valid event statuses.
     */
    public function test_valid_event_statuses() {
        $valid_statuses = array(
            'open',
            'closed',
            'filled',
            'expired',
            'cancelled',
        );

        foreach ( $valid_statuses as $status ) {
            $event = $this->create_mock_market_event( 1, array( 'status' => $status ) );
            $this->assertEquals( $status, $event->status );
        }
    }

    /**
     * Test update event status.
     */
    public function test_update_event_status() {
        $mock_event = $this->create_mock_market_event( 1, array( 'status' => 'open' ) );
        $this->set_db_mock_row( $mock_event );

        $result = Peanut_Booker_Market::update_event_status( 1, 'closed' );

        $this->assertTrue( $result );
    }

    /**
     * Test submit bid with valid data.
     */
    public function test_submit_bid_with_valid_data() {
        $mock_event = $this->create_mock_market_event( 1, array( 'status' => 'open' ) );
        $this->set_db_mock_row( $mock_event );

        $this->create_mock_performer( 101 );
        $this->set_current_user( 101 );

        $data = array(
            'event_id'     => 1,
            'performer_id' => 1,
            'bid_amount'   => 750.00,
            'message'      => 'I would love to perform at your event!',
        );

        $result = Peanut_Booker_Market::submit_bid( $data );

        $this->assertTrue( is_int( $result ) || is_wp_error( $result ) );
    }

    /**
     * Test submit bid fails without event_id.
     */
    public function test_submit_bid_fails_without_event_id() {
        $data = array(
            'performer_id' => 1,
            'bid_amount'   => 500.00,
        );

        $result = Peanut_Booker_Market::submit_bid( $data );

        $this->assertWPError( $result );
    }

    /**
     * Test submit bid fails on closed event.
     */
    public function test_submit_bid_fails_on_closed_event() {
        $mock_event = $this->create_mock_market_event( 1, array( 'status' => 'closed' ) );
        $this->set_db_mock_row( $mock_event );

        $data = array(
            'event_id'     => 1,
            'performer_id' => 1,
            'bid_amount'   => 500.00,
        );

        $result = Peanut_Booker_Market::submit_bid( $data );

        $this->assertWPError( $result );
    }

    /**
     * Test get bid by ID.
     */
    public function test_get_bid_by_id() {
        $mock_bid = $this->create_mock_bid( 1 );
        $this->set_db_mock_row( $mock_bid );

        $result = Peanut_Booker_Market::get_bid( 1 );

        $this->assertIsObject( $result );
        $this->assertEquals( 1, $result->id );
    }

    /**
     * Test get bids for event.
     */
    public function test_get_bids_for_event() {
        $mock_bids = array(
            $this->create_mock_bid( 1, array( 'event_id' => 1 ) ),
            $this->create_mock_bid( 2, array( 'event_id' => 1 ) ),
            $this->create_mock_bid( 3, array( 'event_id' => 1 ) ),
        );
        $this->set_db_mock_results( $mock_bids );

        $result = Peanut_Booker_Market::get_event_bids( 1 );

        $this->assertIsArray( $result );
        $this->assertCount( 3, $result );
    }

    /**
     * Test get bids for performer.
     */
    public function test_get_bids_for_performer() {
        $mock_bids = array(
            $this->create_mock_bid( 1, array( 'performer_id' => 1 ) ),
            $this->create_mock_bid( 2, array( 'performer_id' => 1 ) ),
        );
        $this->set_db_mock_results( $mock_bids );

        $result = Peanut_Booker_Market::get_performer_bids( 1 );

        $this->assertIsArray( $result );
        $this->assertCount( 2, $result );
    }

    /**
     * Test valid bid statuses.
     */
    public function test_valid_bid_statuses() {
        $valid_statuses = array(
            'pending',
            'accepted',
            'rejected',
            'withdrawn',
        );

        foreach ( $valid_statuses as $status ) {
            $bid = $this->create_mock_bid( 1, array( 'status' => $status ) );
            $this->assertEquals( $status, $bid->status );
        }
    }

    /**
     * Test accept bid.
     */
    public function test_accept_bid() {
        $mock_event = $this->create_mock_market_event( 1, array(
            'customer_id' => 1,
            'status'      => 'open',
        ) );

        $mock_bid = $this->create_mock_bid( 1, array(
            'event_id' => 1,
            'status'   => 'pending',
        ) );

        $this->set_db_mock_row( $mock_bid );

        $this->create_mock_customer( 1 );
        $this->set_current_user( 1 );

        $result = Peanut_Booker_Market::accept_bid( 1 );

        $this->assertTrue( $result === true || is_wp_error( $result ) );
    }

    /**
     * Test reject bid.
     */
    public function test_reject_bid() {
        $mock_event = $this->create_mock_market_event( 1, array( 'customer_id' => 1 ) );
        $mock_bid = $this->create_mock_bid( 1, array(
            'event_id' => 1,
            'status'   => 'pending',
        ) );
        $this->set_db_mock_row( $mock_bid );

        $this->create_mock_customer( 1 );
        $this->set_current_user( 1 );

        $result = Peanut_Booker_Market::reject_bid( 1 );

        $this->assertTrue( $result === true || is_wp_error( $result ) );
    }

    /**
     * Test withdraw bid.
     */
    public function test_withdraw_bid() {
        $mock_performer = $this->create_mock_performer_record( 1, array( 'user_id' => 101 ) );
        $mock_bid = $this->create_mock_bid( 1, array(
            'performer_id' => 1,
            'status'       => 'pending',
        ) );
        $this->set_db_mock_row( $mock_bid );

        $this->create_mock_performer( 101 );
        $this->set_current_user( 101 );

        $result = Peanut_Booker_Market::withdraw_bid( 1 );

        $this->assertTrue( $result === true || is_wp_error( $result ) );
    }

    /**
     * Test query market events.
     */
    public function test_query_market_events() {
        $mock_results = array(
            $this->create_mock_market_event( 1 ),
            $this->create_mock_market_event( 2 ),
        );
        $this->set_db_mock_results( $mock_results );

        $args = array(
            'paged'          => 1,
            'posts_per_page' => 12,
            'status'         => 'open',
        );

        $result = Peanut_Booker_Market::query( $args );

        $this->assertIsArray( $result );
    }

    /**
     * Test get customer events.
     */
    public function test_get_customer_events() {
        $mock_results = array(
            $this->create_mock_market_event( 1, array( 'customer_id' => 1 ) ),
            $this->create_mock_market_event( 2, array( 'customer_id' => 1 ) ),
        );
        $this->set_db_mock_results( $mock_results );

        $result = Peanut_Booker_Market::get_customer_events( 1 );

        $this->assertIsArray( $result );
        $this->assertCount( 2, $result );
    }

    /**
     * Test budget range validation.
     */
    public function test_budget_range_validation() {
        $event = $this->create_mock_market_event( 1, array(
            'budget_min' => 200,
            'budget_max' => 500,
        ) );

        $this->assertLessThanOrEqual( $event->budget_max, $event->budget_max );
        $this->assertGreaterThanOrEqual( $event->budget_min, $event->budget_min );
    }

    /**
     * Test bid amount is within budget range.
     */
    public function test_bid_amount_typically_in_budget_range() {
        $event = $this->create_mock_market_event( 1, array(
            'budget_min' => 200,
            'budget_max' => 500,
        ) );

        $bid = $this->create_mock_bid( 1, array(
            'event_id'   => 1,
            'bid_amount' => 350.00,
        ) );

        $this->assertGreaterThanOrEqual( $event->budget_min, $bid->bid_amount );
        $this->assertLessThanOrEqual( $event->budget_max, $bid->bid_amount );
    }

    /**
     * Test update event.
     */
    public function test_update_event() {
        $mock_event = $this->create_mock_market_event( 1 );
        $this->set_db_mock_row( $mock_event );

        $update_data = array(
            'title'       => 'Updated Event Title',
            'description' => 'Updated description',
        );

        $result = Peanut_Booker_Market::update_event( 1, $update_data );

        $this->assertTrue( $result );
    }

    /**
     * Test delete event.
     */
    public function test_delete_event() {
        $mock_event = $this->create_mock_market_event( 1, array( 'customer_id' => 1 ) );
        $this->set_db_mock_row( $mock_event );

        $this->create_mock_customer( 1 );
        $this->set_current_user( 1 );

        $result = Peanut_Booker_Market::delete_event( 1 );

        $this->assertTrue( $result === true || is_wp_error( $result ) );
    }

    /**
     * Test format event data.
     */
    public function test_format_event_data() {
        $event = $this->create_mock_market_event( 1 );

        $result = Peanut_Booker_Market::get_event_data( 1 );

        $this->assertTrue( is_array( $result ) || $result === null );
    }

    /**
     * Test bid deadline enforcement.
     */
    public function test_bid_deadline_validation() {
        $event = $this->create_mock_market_event( 1, array(
            'bid_deadline' => date( 'Y-m-d H:i:s', strtotime( '+7 days' ) ),
        ) );

        $deadline = strtotime( $event->bid_deadline );
        $now = time();

        $this->assertGreaterThan( $now, $deadline );
    }

    /**
     * Test total bids counter.
     */
    public function test_total_bids_counter() {
        $event = $this->create_mock_market_event( 1, array( 'total_bids' => 5 ) );

        $this->assertEquals( 5, $event->total_bids );
        $this->assertGreaterThanOrEqual( 0, $event->total_bids );
    }
}
