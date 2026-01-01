<?php
/**
 * Base Test Case for Peanut Booker PHPUnit Tests
 *
 * Provides common setup, teardown, and helper methods for all tests.
 *
 * @package Peanut_Booker\Tests
 */

namespace Peanut_Booker\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base test case class.
 */
abstract class TestCase extends PHPUnitTestCase {

    /**
     * Set up test fixtures.
     */
    protected function setUp(): void {
        parent::setUp();
        peanut_booker_reset_mocks();
    }

    /**
     * Tear down test fixtures.
     */
    protected function tearDown(): void {
        peanut_booker_reset_mocks();
        parent::tearDown();
    }

    /**
     * Create a mock user.
     *
     * @param int    $user_id User ID.
     * @param array  $data    User data.
     * @return object Mock user object.
     */
    protected function create_mock_user( int $user_id, array $data = array() ): object {
        global $mock_users;

        $defaults = array(
            'ID'           => $user_id,
            'user_login'   => 'testuser' . $user_id,
            'user_email'   => 'testuser' . $user_id . '@example.com',
            'display_name' => 'Test User ' . $user_id,
            'roles'        => array( 'subscriber' ),
            'allcaps'      => array(),
        );

        $user_data = array_merge( $defaults, $data );

        $user = (object) $user_data;
        $mock_users[ $user_id ] = $user;

        return $user;
    }

    /**
     * Set current user.
     *
     * @param int $user_id User ID.
     */
    protected function set_current_user( int $user_id ): void {
        global $mock_current_user_id;
        $mock_current_user_id = $user_id;
    }

    /**
     * Create a mock performer user.
     *
     * @param int   $user_id User ID.
     * @param array $data    Additional user data.
     * @return object Mock user object.
     */
    protected function create_mock_performer( int $user_id, array $data = array() ): object {
        $defaults = array(
            'roles'   => array( 'pb_performer' ),
            'allcaps' => array(
                'pb_performer'          => true,
                'pb_edit_own_profile'   => true,
                'pb_view_bookings'      => true,
                'pb_manage_availability' => true,
                'pb_respond_reviews'    => true,
                'pb_view_market'        => true,
            ),
        );

        return $this->create_mock_user( $user_id, array_merge( $defaults, $data ) );
    }

    /**
     * Create a mock customer user.
     *
     * @param int   $user_id User ID.
     * @param array $data    Additional user data.
     * @return object Mock user object.
     */
    protected function create_mock_customer( int $user_id, array $data = array() ): object {
        $defaults = array(
            'roles'   => array( 'pb_customer' ),
            'allcaps' => array(
                'pb_customer'      => true,
                'pb_book_performers' => true,
                'pb_create_events' => true,
                'pb_leave_reviews' => true,
                'pb_view_market'   => true,
            ),
        );

        return $this->create_mock_user( $user_id, array_merge( $defaults, $data ) );
    }

    /**
     * Create a mock admin user.
     *
     * @param int   $user_id User ID.
     * @param array $data    Additional user data.
     * @return object Mock user object.
     */
    protected function create_mock_admin( int $user_id, array $data = array() ): object {
        $defaults = array(
            'roles'   => array( 'administrator' ),
            'allcaps' => array(
                'pb_manage_performers'  => true,
                'pb_manage_bookings'    => true,
                'pb_manage_reviews'     => true,
                'pb_manage_market'      => true,
                'pb_manage_settings'    => true,
                'pb_arbitrate_reviews'  => true,
                'pb_manage_payouts'     => true,
            ),
        );

        return $this->create_mock_user( $user_id, array_merge( $defaults, $data ) );
    }

    /**
     * Create a mock post.
     *
     * @param int   $post_id Post ID.
     * @param array $data    Post data.
     * @return object Mock post object.
     */
    protected function create_mock_post( int $post_id, array $data = array() ): object {
        global $mock_posts;

        $defaults = array(
            'ID'           => $post_id,
            'post_title'   => 'Test Post ' . $post_id,
            'post_content' => 'Test content for post ' . $post_id,
            'post_status'  => 'publish',
            'post_type'    => 'post',
            'post_author'  => 1,
            'post_excerpt' => '',
            'post_date'    => date( 'Y-m-d H:i:s' ),
        );

        $post = (object) array_merge( $defaults, $data );
        $mock_posts[ $post_id ] = $post;

        return $post;
    }

    /**
     * Create a mock performer profile post.
     *
     * @param int   $post_id     Post ID.
     * @param int   $user_id     User ID of the performer.
     * @param array $data        Additional post data.
     * @param array $meta        Post meta data.
     * @return object Mock post object.
     */
    protected function create_mock_performer_profile( int $post_id, int $user_id, array $data = array(), array $meta = array() ): object {
        global $mock_post_meta;

        $defaults = array(
            'post_type'   => 'pb_performer',
            'post_status' => 'publish',
            'post_author' => $user_id,
            'post_title'  => 'Performer ' . $user_id,
        );

        $post = $this->create_mock_post( $post_id, array_merge( $defaults, $data ) );

        // Set default meta.
        $default_meta = array(
            'pb_user_id'      => array( $user_id ),
            'pb_hourly_rate'  => array( 100 ),
            'pb_stage_name'   => array( 'Test Performer' ),
        );

        $mock_post_meta[ $post_id ] = array_merge( $default_meta, $meta );

        return $post;
    }

    /**
     * Set option value.
     *
     * @param string $option Option name.
     * @param mixed  $value  Option value.
     */
    protected function set_option( string $option, $value ): void {
        global $mock_options;
        $mock_options[ $option ] = $value;
    }

    /**
     * Set transient value.
     *
     * @param string $transient  Transient name.
     * @param mixed  $value      Transient value.
     * @param int    $expiration Expiration time in seconds.
     */
    protected function set_transient( string $transient, $value, int $expiration = 0 ): void {
        global $mock_transients;
        $mock_transients[ $transient ] = array(
            'value'      => $value,
            'expiration' => $expiration > 0 ? time() + $expiration : 0,
        );
    }

    /**
     * Set post meta.
     *
     * @param int    $post_id Post ID.
     * @param string $key     Meta key.
     * @param mixed  $value   Meta value.
     */
    protected function set_post_meta( int $post_id, string $key, $value ): void {
        global $mock_post_meta;
        if ( ! isset( $mock_post_meta[ $post_id ] ) ) {
            $mock_post_meta[ $post_id ] = array();
        }
        $mock_post_meta[ $post_id ][ $key ] = array( $value );
    }

    /**
     * Set database mock row result.
     *
     * @param object|null $row Row object or null.
     */
    protected function set_db_mock_row( ?object $row ): void {
        global $wpdb;
        $wpdb->set_mock_row( $row );
    }

    /**
     * Set database mock results.
     *
     * @param array $results Array of result objects.
     */
    protected function set_db_mock_results( array $results ): void {
        global $wpdb;
        $wpdb->set_mock_results( $results );
    }

    /**
     * Set database mock var.
     *
     * @param mixed $var Value to return from get_var.
     */
    protected function set_db_mock_var( $var ): void {
        global $wpdb;
        $wpdb->set_mock_var( $var );
    }

    /**
     * Create a mock booking object.
     *
     * @param int   $id   Booking ID.
     * @param array $data Booking data.
     * @return object Mock booking object.
     */
    protected function create_mock_booking( int $id, array $data = array() ): object {
        $defaults = array(
            'id'                => $id,
            'booking_number'    => 'PB' . date( 'Ymd' ) . 'TEST' . $id,
            'performer_id'      => 1,
            'customer_id'       => 2,
            'event_id'          => null,
            'bid_id'            => null,
            'event_title'       => 'Test Event',
            'event_description' => 'Test event description',
            'event_date'        => date( 'Y-m-d', strtotime( '+7 days' ) ),
            'event_start_time'  => '18:00:00',
            'event_end_time'    => '22:00:00',
            'event_location'    => 'Test Venue',
            'event_address'     => '123 Test St',
            'event_city'        => 'Test City',
            'event_state'       => 'TS',
            'event_zip'         => '12345',
            'total_amount'      => 400.00,
            'deposit_amount'    => 100.00,
            'remaining_amount'  => 300.00,
            'platform_commission' => 80.00,
            'performer_payout'  => 320.00,
            'escrow_status'     => 'pending',
            'booking_status'    => 'pending',
            'deposit_paid'      => 0,
            'fully_paid'        => 0,
            'performer_confirmed' => 0,
            'customer_confirmed_completion' => 0,
            'order_id'          => null,
            'notes'             => '',
            'created_at'        => date( 'Y-m-d H:i:s' ),
            'updated_at'        => date( 'Y-m-d H:i:s' ),
        );

        return (object) array_merge( $defaults, $data );
    }

    /**
     * Create a mock performer record.
     *
     * @param int   $id   Performer ID.
     * @param array $data Performer data.
     * @return object Mock performer object.
     */
    protected function create_mock_performer_record( int $id, array $data = array() ): object {
        $defaults = array(
            'id'                  => $id,
            'user_id'             => $id + 100,
            'profile_id'          => $id + 1000,
            'tier'                => 'free',
            'achievement_level'   => 'bronze',
            'achievement_score'   => 0,
            'completed_bookings'  => 0,
            'profile_completeness' => 50,
            'deposit_percentage'  => 25,
            'status'              => 'approved',
            'hourly_rate'         => 100.00,
            'average_rating'      => null,
            'total_reviews'       => 0,
            'is_verified'         => 0,
            'is_featured'         => 0,
            'created_at'          => date( 'Y-m-d H:i:s' ),
        );

        return (object) array_merge( $defaults, $data );
    }

    /**
     * Create a mock review object.
     *
     * @param int   $id   Review ID.
     * @param array $data Review data.
     * @return object Mock review object.
     */
    protected function create_mock_review( int $id, array $data = array() ): object {
        $defaults = array(
            'id'                 => $id,
            'booking_id'         => 1,
            'reviewer_id'        => 1,
            'reviewee_id'        => 2,
            'reviewer_type'      => 'customer',
            'rating'             => 5,
            'title'              => 'Great performer!',
            'content'            => 'Had a wonderful experience.',
            'response'           => null,
            'response_date'      => null,
            'is_visible'         => 1,
            'is_flagged'         => 0,
            'flag_reason'        => null,
            'flagged_by'         => null,
            'flagged_date'       => null,
            'arbitration_status' => null,
            'arbitration_notes'  => null,
            'arbitrated_by'      => null,
            'arbitration_date'   => null,
            'created_at'         => date( 'Y-m-d H:i:s' ),
        );

        return (object) array_merge( $defaults, $data );
    }

    /**
     * Create a mock message object.
     *
     * @param int   $id   Message ID.
     * @param array $data Message data.
     * @return object Mock message object.
     */
    protected function create_mock_message( int $id, array $data = array() ): object {
        $defaults = array(
            'id'           => $id,
            'sender_id'    => 1,
            'recipient_id' => 2,
            'message'      => 'Test message content',
            'booking_id'   => null,
            'is_read'      => 0,
            'created_at'   => date( 'Y-m-d H:i:s' ),
        );

        return (object) array_merge( $defaults, $data );
    }

    /**
     * Create a mock market event object.
     *
     * @param int   $id   Event ID.
     * @param array $data Event data.
     * @return object Mock event object.
     */
    protected function create_mock_market_event( int $id, array $data = array() ): object {
        $defaults = array(
            'id'            => $id,
            'customer_id'   => 1,
            'post_id'       => $id + 1000,
            'title'         => 'Test Market Event',
            'description'   => 'Looking for a performer',
            'event_date'    => date( 'Y-m-d', strtotime( '+30 days' ) ),
            'event_start_time' => '18:00:00',
            'city'          => 'Test City',
            'state'         => 'TS',
            'budget_min'    => 200,
            'budget_max'    => 500,
            'bid_deadline'  => date( 'Y-m-d H:i:s', strtotime( '+7 days' ) ),
            'status'        => 'open',
            'total_bids'    => 0,
            'accepted_bid_id' => null,
            'created_at'    => date( 'Y-m-d H:i:s' ),
        );

        return (object) array_merge( $defaults, $data );
    }

    /**
     * Create a mock bid object.
     *
     * @param int   $id   Bid ID.
     * @param array $data Bid data.
     * @return object Mock bid object.
     */
    protected function create_mock_bid( int $id, array $data = array() ): object {
        $defaults = array(
            'id'           => $id,
            'event_id'     => 1,
            'performer_id' => 1,
            'bid_amount'   => 350.00,
            'message'      => 'I would love to perform at your event!',
            'status'       => 'pending',
            'is_read'      => 0,
            'created_at'   => date( 'Y-m-d H:i:s' ),
        );

        return (object) array_merge( $defaults, $data );
    }

    /**
     * Assert that a value is a WP_Error.
     *
     * @param mixed  $actual  The value to check.
     * @param string $message Optional message.
     */
    protected function assertWPError( $actual, string $message = '' ): void {
        $this->assertInstanceOf( \WP_Error::class, $actual, $message );
    }

    /**
     * Assert that a value is not a WP_Error.
     *
     * @param mixed  $actual  The value to check.
     * @param string $message Optional message.
     */
    protected function assertNotWPError( $actual, string $message = '' ): void {
        $this->assertNotInstanceOf( \WP_Error::class, $actual, $message );
    }

    /**
     * Assert that a WP_Error has a specific error code.
     *
     * @param string    $expected_code Expected error code.
     * @param \WP_Error $error         WP_Error instance.
     * @param string    $message       Optional message.
     */
    protected function assertWPErrorCode( string $expected_code, \WP_Error $error, string $message = '' ): void {
        $this->assertSame( $expected_code, $error->get_error_code(), $message );
    }
}
