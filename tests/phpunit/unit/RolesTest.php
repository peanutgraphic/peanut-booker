<?php
/**
 * Tests for Peanut_Booker_Roles capability-based permission system
 *
 * @package Peanut_Booker\Tests
 */

namespace Peanut_Booker\Tests\Unit;

use Peanut_Booker\Tests\TestCase;

/**
 * Roles and permissions test case
 */
class RolesTest extends TestCase {

    /**
     * Test that admin can view any booking
     */
    public function test_admin_can_view_any_booking(): void {
        $admin = $this->create_mock_admin(1);
        $this->set_current_user(1);

        $booking = $this->create_mock_booking(1, [
            'customer_id' => 999,
            'performer_id' => 888,
        ]);

        $result = \Peanut_Booker_Roles::can_view_booking($booking);

        $this->assertTrue($result, 'Admin should be able to view any booking');
    }

    /**
     * Test that customer can view their own booking
     */
    public function test_customer_can_view_own_booking(): void {
        $customer = $this->create_mock_customer(2);
        $this->set_current_user(2);

        $booking = $this->create_mock_booking(1, [
            'customer_id' => 2,
            'performer_id' => 5,
        ]);

        $result = \Peanut_Booker_Roles::can_view_booking($booking);

        $this->assertTrue($result, 'Customer should be able to view their own booking');
    }

    /**
     * Test that customer cannot view other's booking
     */
    public function test_customer_cannot_view_others_booking(): void {
        $customer = $this->create_mock_customer(2);
        $this->set_current_user(2);

        $booking = $this->create_mock_booking(1, [
            'customer_id' => 999,
            'performer_id' => 888,
        ]);

        $result = \Peanut_Booker_Roles::can_view_booking($booking);

        $this->assertFalse($result, 'Customer should not be able to view another user\'s booking');
    }

    /**
     * Test that performer can view booking they're assigned to
     */
    public function test_performer_can_view_assigned_booking(): void {
        $performer = $this->create_mock_performer(3);
        $this->set_current_user(3);

        // Mock performer record lookup
        $performer_record = $this->create_mock_performer_record(10, [
            'user_id' => 3,
        ]);
        $this->set_db_mock_row($performer_record);

        $booking = $this->create_mock_booking(1, [
            'customer_id' => 999,
            'performer_id' => 10,
        ]);

        $result = \Peanut_Booker_Roles::can_view_booking($booking);

        $this->assertTrue($result, 'Performer should be able to view booking they are assigned to');
    }

    /**
     * Test customer can review booking where they are the customer
     */
    public function test_customer_can_review_own_booking(): void {
        $customer = $this->create_mock_customer(2);
        $this->set_current_user(2);

        $booking = $this->create_mock_booking(1, [
            'customer_id' => 2,
            'performer_id' => 5,
            'booking_status' => 'completed',
        ]);

        $result = \Peanut_Booker_Roles::can_review_booking($booking);

        $this->assertTrue($result, 'Customer should be able to review their own completed booking');
    }

    /**
     * Test customer cannot review other's booking
     */
    public function test_customer_cannot_review_others_booking(): void {
        $customer = $this->create_mock_customer(2);
        $this->set_current_user(2);

        $booking = $this->create_mock_booking(1, [
            'customer_id' => 999,
            'performer_id' => 888,
            'booking_status' => 'completed',
        ]);

        $result = \Peanut_Booker_Roles::can_review_booking($booking);

        $this->assertFalse($result, 'Customer should not be able to review another\'s booking');
    }

    /**
     * Test is_booking_customer correctly identifies customer
     */
    public function test_is_booking_customer_returns_true_for_customer(): void {
        $customer = $this->create_mock_customer(2);
        $this->set_current_user(2);

        $booking = $this->create_mock_booking(1, [
            'customer_id' => 2,
        ]);

        $result = \Peanut_Booker_Roles::is_booking_customer($booking);

        $this->assertTrue($result);
    }

    /**
     * Test is_booking_customer returns false for non-customer
     */
    public function test_is_booking_customer_returns_false_for_non_customer(): void {
        $customer = $this->create_mock_customer(2);
        $this->set_current_user(2);

        $booking = $this->create_mock_booking(1, [
            'customer_id' => 999,
        ]);

        $result = \Peanut_Booker_Roles::is_booking_customer($booking);

        $this->assertFalse($result);
    }

    /**
     * Test performer can edit their own profile
     */
    public function test_performer_can_edit_own_profile(): void {
        $performer = $this->create_mock_performer(3);
        $this->set_current_user(3);

        $performer_record = $this->create_mock_performer_record(10, [
            'user_id' => 3,
        ]);

        $result = \Peanut_Booker_Roles::can_edit_performer($performer_record);

        $this->assertTrue($result, 'Performer should be able to edit their own profile');
    }

    /**
     * Test performer cannot edit another's profile
     */
    public function test_performer_cannot_edit_others_profile(): void {
        $performer = $this->create_mock_performer(3);
        $this->set_current_user(3);

        $other_performer = $this->create_mock_performer_record(10, [
            'user_id' => 999,
        ]);

        $result = \Peanut_Booker_Roles::can_edit_performer($other_performer);

        $this->assertFalse($result, 'Performer should not be able to edit another\'s profile');
    }

    /**
     * Test admin can edit any performer profile
     */
    public function test_admin_can_edit_any_performer_profile(): void {
        $admin = $this->create_mock_admin(1);
        $this->set_current_user(1);

        $performer_record = $this->create_mock_performer_record(10, [
            'user_id' => 999,
        ]);

        $result = \Peanut_Booker_Roles::can_edit_performer($performer_record);

        $this->assertTrue($result, 'Admin should be able to edit any performer profile');
    }

    /**
     * Test customer can manage their own market event
     */
    public function test_customer_can_manage_own_market_event(): void {
        $customer = $this->create_mock_customer(2);
        $this->set_current_user(2);

        $event = (object) [
            'id' => 1,
            'customer_id' => 2,
            'title' => 'Test Event',
        ];

        $result = \Peanut_Booker_Roles::can_manage_market_event($event);

        $this->assertTrue($result, 'Customer should be able to manage their own market event');
    }

    /**
     * Test customer cannot manage another's market event
     */
    public function test_customer_cannot_manage_others_market_event(): void {
        $customer = $this->create_mock_customer(2);
        $this->set_current_user(2);

        $event = (object) [
            'id' => 1,
            'customer_id' => 999,
            'title' => 'Other Event',
        ];

        $result = \Peanut_Booker_Roles::can_manage_market_event($event);

        $this->assertFalse($result, 'Customer should not be able to manage another\'s market event');
    }

    /**
     * Test unauthenticated user cannot view bookings
     */
    public function test_unauthenticated_user_cannot_view_booking(): void {
        // Set current user to 0 (not logged in)
        $this->set_current_user(0);

        $booking = $this->create_mock_booking(1);

        $result = \Peanut_Booker_Roles::can_view_booking($booking);

        $this->assertFalse($result, 'Unauthenticated user should not be able to view bookings');
    }

    /**
     * Test explicit user_id parameter works
     */
    public function test_explicit_user_id_parameter(): void {
        // Set current user to someone else
        $this->set_current_user(999);

        // Create customer 2
        $customer = $this->create_mock_customer(2);

        $booking = $this->create_mock_booking(1, [
            'customer_id' => 2,
        ]);

        // Pass explicit user_id
        $result = \Peanut_Booker_Roles::can_view_booking($booking, 2);

        $this->assertTrue($result, 'Explicit user_id parameter should be respected');
    }
}
