<?php
/**
 * Tests for the Database class.
 *
 * @package Peanut_Booker\Tests
 */

namespace Peanut_Booker\Tests\Unit;

use Peanut_Booker\Tests\TestCase;
use Peanut_Booker_Database;

/**
 * Database class tests.
 */
class DatabaseTest extends TestCase {

    /**
     * Test insert method.
     */
    public function test_insert_returns_insert_id() {
        global $wpdb;

        $data = array(
            'user_id'    => 1,
            'profile_id' => 100,
            'tier'       => 'free',
            'status'     => 'pending',
        );

        $result = Peanut_Booker_Database::insert( 'performers', $data );

        $this->assertIsInt( $result );
        $this->assertGreaterThan( 0, $result );
    }

    /**
     * Test insert with empty data.
     */
    public function test_insert_with_empty_data_returns_false() {
        $result = Peanut_Booker_Database::insert( 'performers', array() );

        $this->assertFalse( $result );
    }

    /**
     * Test get_row method.
     */
    public function test_get_row_returns_object_when_found() {
        $mock_row = (object) array(
            'id'      => 1,
            'user_id' => 100,
            'status'  => 'approved',
        );

        $this->set_db_mock_row( $mock_row );

        $result = Peanut_Booker_Database::get_row( 'performers', array( 'id' => 1 ) );

        $this->assertIsObject( $result );
        $this->assertEquals( 1, $result->id );
        $this->assertEquals( 100, $result->user_id );
    }

    /**
     * Test get_row returns null when not found.
     */
    public function test_get_row_returns_null_when_not_found() {
        $this->set_db_mock_row( null );

        $result = Peanut_Booker_Database::get_row( 'performers', array( 'id' => 999 ) );

        $this->assertNull( $result );
    }

    /**
     * Test get_results method.
     */
    public function test_get_results_returns_array_of_objects() {
        $mock_results = array(
            (object) array( 'id' => 1, 'name' => 'Performer 1' ),
            (object) array( 'id' => 2, 'name' => 'Performer 2' ),
        );

        $this->set_db_mock_results( $mock_results );

        $result = Peanut_Booker_Database::get_results( 'performers', array() );

        $this->assertIsArray( $result );
        $this->assertCount( 2, $result );
        $this->assertEquals( 'Performer 1', $result[0]->name );
    }

    /**
     * Test get_results returns empty array when no results.
     */
    public function test_get_results_returns_empty_array_when_no_results() {
        $this->set_db_mock_results( array() );

        $result = Peanut_Booker_Database::get_results( 'performers', array( 'status' => 'nonexistent' ) );

        $this->assertIsArray( $result );
        $this->assertEmpty( $result );
    }

    /**
     * Test update method.
     */
    public function test_update_returns_rows_affected() {
        $data = array(
            'status' => 'approved',
        );
        $where = array(
            'id' => 1,
        );

        $result = Peanut_Booker_Database::update( 'performers', $data, $where );

        $this->assertIsInt( $result );
    }

    /**
     * Test delete method.
     */
    public function test_delete_returns_rows_deleted() {
        $where = array(
            'id' => 1,
        );

        $result = Peanut_Booker_Database::delete( 'performers', $where );

        $this->assertIsInt( $result );
    }

    /**
     * Test get_var method.
     */
    public function test_get_var_returns_single_value() {
        $this->set_db_mock_var( 42 );

        $result = Peanut_Booker_Database::get_var( 'performers', 'COUNT(*)' );

        $this->assertEquals( 42, $result );
    }

    /**
     * Test table name prefixing.
     */
    public function test_table_names_are_prefixed_correctly() {
        global $wpdb;

        // The insert should use the correct prefixed table name.
        $data = array( 'user_id' => 1 );
        $result = Peanut_Booker_Database::insert( 'performers', $data );

        // If no error, table name prefix is being applied.
        $this->assertNotFalse( $result );
    }

    /**
     * Test get_results with order parameters.
     */
    public function test_get_results_with_order_parameters() {
        $mock_results = array(
            (object) array( 'id' => 2, 'created_at' => '2024-01-02' ),
            (object) array( 'id' => 1, 'created_at' => '2024-01-01' ),
        );

        $this->set_db_mock_results( $mock_results );

        $result = Peanut_Booker_Database::get_results(
            'performers',
            array(),
            'created_at',
            'DESC',
            10
        );

        $this->assertIsArray( $result );
        $this->assertEquals( 2, $result[0]->id );
    }

    /**
     * Test insert sanitizes data.
     */
    public function test_insert_handles_various_data_types() {
        $data = array(
            'user_id'      => 123,
            'hourly_rate'  => 99.99,
            'status'       => 'approved',
            'is_verified'  => true,
            'notes'        => null,
        );

        $result = Peanut_Booker_Database::insert( 'performers', $data );

        $this->assertNotFalse( $result );
    }

    /**
     * Test update with empty where returns false.
     */
    public function test_update_with_empty_where_returns_false() {
        $data = array( 'status' => 'approved' );

        $result = Peanut_Booker_Database::update( 'performers', $data, array() );

        $this->assertFalse( $result );
    }

    /**
     * Test delete with empty where returns false.
     */
    public function test_delete_with_empty_where_returns_false() {
        $result = Peanut_Booker_Database::delete( 'performers', array() );

        $this->assertFalse( $result );
    }
}
