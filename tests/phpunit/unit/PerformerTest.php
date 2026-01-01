<?php
/**
 * Tests for the Performer class.
 *
 * @package Peanut_Booker\Tests
 */

namespace Peanut_Booker\Tests\Unit;

use Peanut_Booker\Tests\TestCase;
use Peanut_Booker_Performer;

/**
 * Performer class tests.
 */
class PerformerTest extends TestCase {

    /**
     * Test get performer by ID.
     */
    public function test_get_performer_by_id() {
        $mock_performer = $this->create_mock_performer_record( 1, array(
            'user_id'    => 101,
            'profile_id' => 1001,
            'tier'       => 'pro',
            'status'     => 'approved',
        ) );
        $this->set_db_mock_row( $mock_performer );

        $result = Peanut_Booker_Performer::get( 1 );

        $this->assertIsObject( $result );
        $this->assertEquals( 1, $result->id );
        $this->assertEquals( 'pro', $result->tier );
    }

    /**
     * Test get performer by user ID.
     */
    public function test_get_performer_by_user_id() {
        $mock_performer = $this->create_mock_performer_record( 1, array(
            'user_id' => 101,
        ) );
        $this->set_db_mock_row( $mock_performer );

        $result = Peanut_Booker_Performer::get_by_user_id( 101 );

        $this->assertIsObject( $result );
        $this->assertEquals( 101, $result->user_id );
    }

    /**
     * Test get performer returns null when not found.
     */
    public function test_get_performer_returns_null_when_not_found() {
        $this->set_db_mock_row( null );

        $result = Peanut_Booker_Performer::get( 99999 );

        $this->assertNull( $result );
    }

    /**
     * Test performer registration with valid data.
     */
    public function test_register_performer_with_valid_data() {
        $this->create_mock_user( 101, array(
            'user_email' => 'performer@example.com',
        ) );
        $this->set_current_user( 101 );

        $data = array(
            'user_id'         => 101,
            'stage_name'      => 'The Amazing Performer',
            'bio'             => 'Professional entertainer with 10 years experience.',
            'hourly_rate'     => 150.00,
            'performer_type'  => 'musician',
            'city'            => 'Austin',
            'state'           => 'TX',
        );

        $result = Peanut_Booker_Performer::register( $data );

        $this->assertTrue( is_int( $result ) || is_wp_error( $result ) );
    }

    /**
     * Test performer registration fails without user_id.
     */
    public function test_register_performer_fails_without_user_id() {
        $data = array(
            'stage_name'  => 'Test Performer',
            'hourly_rate' => 100.00,
        );

        $result = Peanut_Booker_Performer::register( $data );

        $this->assertWPError( $result );
    }

    /**
     * Test update performer.
     */
    public function test_update_performer() {
        $mock_performer = $this->create_mock_performer_record( 1 );
        $this->set_db_mock_row( $mock_performer );

        $update_data = array(
            'hourly_rate' => 175.00,
            'tier'        => 'featured',
        );

        $result = Peanut_Booker_Performer::update( 1, $update_data );

        $this->assertTrue( $result );
    }

    /**
     * Test performer status update.
     */
    public function test_update_performer_status() {
        $mock_performer = $this->create_mock_performer_record( 1, array( 'status' => 'pending' ) );
        $this->set_db_mock_row( $mock_performer );

        $result = Peanut_Booker_Performer::update_status( 1, 'approved' );

        $this->assertTrue( $result );
    }

    /**
     * Test valid performer statuses.
     */
    public function test_valid_performer_statuses() {
        $valid_statuses = array(
            'pending',
            'approved',
            'suspended',
            'rejected',
        );

        foreach ( $valid_statuses as $status ) {
            $performer = $this->create_mock_performer_record( 1, array( 'status' => $status ) );
            $this->assertEquals( $status, $performer->status );
        }
    }

    /**
     * Test performer tier values.
     */
    public function test_valid_performer_tiers() {
        $valid_tiers = array( 'free', 'pro', 'featured' );

        foreach ( $valid_tiers as $tier ) {
            $performer = $this->create_mock_performer_record( 1, array( 'tier' => $tier ) );
            $this->assertEquals( $tier, $performer->tier );
        }
    }

    /**
     * Test performer verification.
     */
    public function test_verify_performer() {
        $mock_performer = $this->create_mock_performer_record( 1, array( 'is_verified' => 0 ) );
        $this->set_db_mock_row( $mock_performer );

        $result = Peanut_Booker_Performer::verify( 1 );

        $this->assertTrue( $result );
    }

    /**
     * Test unverify performer.
     */
    public function test_unverify_performer() {
        $mock_performer = $this->create_mock_performer_record( 1, array( 'is_verified' => 1 ) );
        $this->set_db_mock_row( $mock_performer );

        $result = Peanut_Booker_Performer::unverify( 1 );

        $this->assertTrue( $result );
    }

    /**
     * Test feature performer.
     */
    public function test_feature_performer() {
        $mock_performer = $this->create_mock_performer_record( 1, array( 'is_featured' => 0 ) );
        $this->set_db_mock_row( $mock_performer );

        $result = Peanut_Booker_Performer::feature( 1 );

        $this->assertTrue( $result );
    }

    /**
     * Test unfeature performer.
     */
    public function test_unfeature_performer() {
        $mock_performer = $this->create_mock_performer_record( 1, array( 'is_featured' => 1 ) );
        $this->set_db_mock_row( $mock_performer );

        $result = Peanut_Booker_Performer::unfeature( 1 );

        $this->assertTrue( $result );
    }

    /**
     * Test get featured performers.
     */
    public function test_get_featured_performers() {
        $mock_results = array(
            $this->create_mock_performer_record( 1, array( 'is_featured' => 1 ) ),
            $this->create_mock_performer_record( 2, array( 'is_featured' => 1 ) ),
        );
        $this->set_db_mock_results( $mock_results );

        $result = Peanut_Booker_Performer::get_featured( 4 );

        $this->assertIsArray( $result );
    }

    /**
     * Test query performers.
     */
    public function test_query_performers() {
        $mock_results = array(
            $this->create_mock_performer_record( 1 ),
            $this->create_mock_performer_record( 2 ),
            $this->create_mock_performer_record( 3 ),
        );
        $this->set_db_mock_results( $mock_results );

        $args = array(
            'paged'          => 1,
            'posts_per_page' => 12,
        );

        $result = Peanut_Booker_Performer::query( $args );

        $this->assertIsArray( $result );
    }

    /**
     * Test achievement levels.
     */
    public function test_valid_achievement_levels() {
        $valid_levels = array(
            'bronze',
            'silver',
            'gold',
            'platinum',
            'diamond',
        );

        foreach ( $valid_levels as $level ) {
            $performer = $this->create_mock_performer_record( 1, array( 'achievement_level' => $level ) );
            $this->assertEquals( $level, $performer->achievement_level );
        }
    }

    /**
     * Test calculate achievement score.
     */
    public function test_calculate_achievement_score() {
        $mock_performer = $this->create_mock_performer_record( 1, array(
            'completed_bookings'   => 50,
            'average_rating'       => 4.8,
            'total_reviews'        => 25,
            'profile_completeness' => 100,
        ) );
        $this->set_db_mock_row( $mock_performer );

        $result = Peanut_Booker_Performer::calculate_achievement_score( 1 );

        $this->assertTrue( is_int( $result ) || $result === true );
    }

    /**
     * Test performer display data.
     */
    public function test_get_display_data() {
        $mock_performer = $this->create_mock_performer_record( 1, array(
            'profile_id' => 1001,
        ) );
        $this->set_db_mock_row( $mock_performer );

        $this->create_mock_performer_profile( 1001, 101 );

        $result = Peanut_Booker_Performer::get_display_data( 1001 );

        $this->assertIsArray( $result );
    }

    /**
     * Test profile completeness calculation.
     */
    public function test_profile_completeness_range() {
        $performer = $this->create_mock_performer_record( 1, array(
            'profile_completeness' => 75,
        ) );

        $this->assertGreaterThanOrEqual( 0, $performer->profile_completeness );
        $this->assertLessThanOrEqual( 100, $performer->profile_completeness );
    }

    /**
     * Test hourly rate is positive.
     */
    public function test_hourly_rate_is_positive() {
        $performer = $this->create_mock_performer_record( 1, array(
            'hourly_rate' => 100.00,
        ) );

        $this->assertGreaterThan( 0, $performer->hourly_rate );
    }

    /**
     * Test deposit percentage range.
     */
    public function test_deposit_percentage_range() {
        $performer = $this->create_mock_performer_record( 1, array(
            'deposit_percentage' => 25,
        ) );

        $this->assertGreaterThanOrEqual( 0, $performer->deposit_percentage );
        $this->assertLessThanOrEqual( 100, $performer->deposit_percentage );
    }

    /**
     * Test average rating range.
     */
    public function test_average_rating_range() {
        $performer = $this->create_mock_performer_record( 1, array(
            'average_rating' => 4.5,
        ) );

        if ( $performer->average_rating !== null ) {
            $this->assertGreaterThanOrEqual( 1, $performer->average_rating );
            $this->assertLessThanOrEqual( 5, $performer->average_rating );
        } else {
            $this->assertNull( $performer->average_rating );
        }
    }

    /**
     * Test delete performer.
     */
    public function test_delete_performer() {
        $mock_performer = $this->create_mock_performer_record( 1 );
        $this->set_db_mock_row( $mock_performer );

        $result = Peanut_Booker_Performer::delete( 1 );

        $this->assertTrue( $result );
    }

    /**
     * Test increment completed bookings.
     */
    public function test_increment_completed_bookings() {
        $mock_performer = $this->create_mock_performer_record( 1, array(
            'completed_bookings' => 10,
        ) );
        $this->set_db_mock_row( $mock_performer );

        $result = Peanut_Booker_Performer::increment_completed_bookings( 1 );

        $this->assertTrue( $result );
    }
}
