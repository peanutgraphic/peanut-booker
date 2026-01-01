<?php
/**
 * Tests for Peanut Booker REST API Security
 *
 * Tests rate limiting, authentication, and authorization on REST endpoints.
 *
 * @package Peanut_Booker\Tests
 */

namespace Peanut_Booker\Tests\Unit;

use Peanut_Booker\Tests\TestCase;

/**
 * REST API Security test case
 */
class RestApiSecurityTest extends TestCase {

    /**
     * Test rate limiter blocks after threshold
     */
    public function test_rate_limiter_blocks_after_threshold(): void {
        $limiter = new \Peanut_Booker_Rate_Limiter('test_action', 3, 60);

        // First 3 attempts should pass
        $this->assertTrue($limiter->check(), 'First attempt should pass');
        $limiter->hit();

        $this->assertTrue($limiter->check(), 'Second attempt should pass');
        $limiter->hit();

        $this->assertTrue($limiter->check(), 'Third attempt should pass');
        $limiter->hit();

        // Fourth attempt should fail
        $this->assertFalse($limiter->check(), 'Fourth attempt should be blocked');
    }

    /**
     * Test rate limiter get_remaining returns correct count
     */
    public function test_rate_limiter_remaining_count(): void {
        $limiter = new \Peanut_Booker_Rate_Limiter('remaining_test', 5, 60);

        $this->assertEquals(5, $limiter->get_remaining(), 'Should have 5 remaining initially');

        $limiter->hit();
        $this->assertEquals(4, $limiter->get_remaining(), 'Should have 4 remaining after 1 hit');

        $limiter->hit();
        $limiter->hit();
        $this->assertEquals(2, $limiter->get_remaining(), 'Should have 2 remaining after 3 hits');
    }

    /**
     * Test rate limiter reset clears count
     */
    public function test_rate_limiter_reset(): void {
        $limiter = new \Peanut_Booker_Rate_Limiter('reset_test', 3, 60);

        $limiter->hit();
        $limiter->hit();
        $limiter->hit();

        $this->assertFalse($limiter->check(), 'Should be blocked after 3 hits');

        $limiter->reset();

        $this->assertTrue($limiter->check(), 'Should pass after reset');
        $this->assertEquals(3, $limiter->get_remaining(), 'Should have full count after reset');
    }

    /**
     * Test rate limit headers are formatted correctly
     */
    public function test_rate_limit_headers(): void {
        $limiter = new \Peanut_Booker_Rate_Limiter('header_test', 10, 60);
        $limiter->hit();
        $limiter->hit();

        $headers = $limiter->get_headers();

        $this->assertArrayHasKey('X-RateLimit-Limit', $headers);
        $this->assertArrayHasKey('X-RateLimit-Remaining', $headers);
        $this->assertArrayHasKey('X-RateLimit-Reset', $headers);

        $this->assertEquals(10, $headers['X-RateLimit-Limit']);
        $this->assertEquals(8, $headers['X-RateLimit-Remaining']);
    }

    /**
     * Test unauthenticated request returns 401 for protected endpoints
     */
    public function test_unauthenticated_request_returns_401(): void {
        $this->set_current_user(0);

        // Simulate permission check for protected endpoint
        $result = \Peanut_Booker_Rest_Api::permission_manage_bookings();

        $this->assertInstanceOf(\WP_Error::class, $result);
        $this->assertEquals('rest_forbidden', $result->get_error_code());
    }

    /**
     * Test authenticated user without capability returns 403
     */
    public function test_user_without_capability_returns_403(): void {
        $customer = $this->create_mock_customer(2);
        $this->set_current_user(2);

        // Customer should not have pb_manage_bookings capability
        $result = \Peanut_Booker_Rest_Api::permission_manage_bookings();

        $this->assertInstanceOf(\WP_Error::class, $result);
    }

    /**
     * Test admin can access manage endpoints
     */
    public function test_admin_can_access_manage_endpoints(): void {
        $admin = $this->create_mock_admin(1);
        $this->set_current_user(1);

        $result = \Peanut_Booker_Rest_Api::permission_manage_bookings();

        $this->assertTrue($result, 'Admin should be able to access manage endpoints');
    }

    /**
     * Test booking access checks authorization
     */
    public function test_booking_access_requires_authorization(): void {
        $customer = $this->create_mock_customer(2);
        $this->set_current_user(2);

        $other_booking = $this->create_mock_booking(1, [
            'customer_id' => 999,
            'performer_id' => 888,
        ]);

        $this->set_db_mock_row($other_booking);

        // Attempting to access another user's booking should fail
        $can_access = \Peanut_Booker_Roles::can_view_booking($other_booking);

        $this->assertFalse($can_access, 'Customer should not access another user\'s booking');
    }

    /**
     * Test review submission validates booking ownership
     */
    public function test_review_submission_validates_ownership(): void {
        $customer = $this->create_mock_customer(2);
        $this->set_current_user(2);

        // Booking belongs to different customer
        $booking = $this->create_mock_booking(1, [
            'customer_id' => 999,
            'performer_id' => 5,
            'booking_status' => 'completed',
        ]);

        $can_review = \Peanut_Booker_Roles::can_review_booking($booking);

        $this->assertFalse($can_review, 'User should not be able to review booking they don\'t own');
    }

    /**
     * Test performer endpoint validates ownership
     */
    public function test_performer_profile_edit_validates_ownership(): void {
        $performer = $this->create_mock_performer(3);
        $this->set_current_user(3);

        // Different performer's profile
        $other_profile = $this->create_mock_performer_record(10, [
            'user_id' => 999,
        ]);

        $can_edit = \Peanut_Booker_Roles::can_edit_performer($other_profile);

        $this->assertFalse($can_edit, 'Performer should not edit another\'s profile');
    }

    /**
     * Test nonce verification for form submissions
     */
    public function test_nonce_verification_required(): void {
        global $mock_nonce_verified;
        $mock_nonce_verified = false;

        $customer = $this->create_mock_customer(2);
        $this->set_current_user(2);

        // Simulate AJAX handler checking nonce
        $nonce_valid = wp_verify_nonce('invalid_nonce', 'pb_booking_action');

        $this->assertFalse($nonce_valid, 'Invalid nonce should fail verification');
    }

    /**
     * Test valid nonce passes verification
     */
    public function test_valid_nonce_passes(): void {
        global $mock_nonces;
        $mock_nonces['pb_booking_action'] = 'valid_nonce_123';

        $nonce_valid = wp_verify_nonce('valid_nonce_123', 'pb_booking_action');

        $this->assertTrue($nonce_valid !== false, 'Valid nonce should pass verification');
    }

    /**
     * Test sanitization of user input
     */
    public function test_input_sanitization(): void {
        $dirty_input = '<script>alert("xss")</script>Test Input';
        $clean_input = sanitize_text_field($dirty_input);

        $this->assertStringNotContainsString('<script>', $clean_input);
        $this->assertStringContainsString('Test Input', $clean_input);
    }

    /**
     * Test email sanitization
     */
    public function test_email_sanitization(): void {
        $valid_email = 'test@example.com';
        $invalid_email = 'not-an-email';

        $this->assertEquals($valid_email, sanitize_email($valid_email));
        $this->assertEquals('', sanitize_email($invalid_email));
    }

    /**
     * Test URL sanitization
     */
    public function test_url_sanitization(): void {
        $valid_url = 'https://example.com/page';
        $javascript_url = 'javascript:alert("xss")';

        $this->assertEquals($valid_url, esc_url_raw($valid_url));
        $this->assertEmpty(esc_url_raw($javascript_url));
    }

    /**
     * Test concurrent rate limit tracking per action
     */
    public function test_rate_limits_are_per_action(): void {
        $limiter1 = new \Peanut_Booker_Rate_Limiter('action_one', 2, 60);
        $limiter2 = new \Peanut_Booker_Rate_Limiter('action_two', 2, 60);

        // Exhaust action_one
        $limiter1->hit();
        $limiter1->hit();

        $this->assertFalse($limiter1->check(), 'action_one should be blocked');
        $this->assertTrue($limiter2->check(), 'action_two should still be available');
    }
}
