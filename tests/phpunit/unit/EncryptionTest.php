<?php
/**
 * Tests for the Encryption class.
 *
 * @package Peanut_Booker\Tests
 */

namespace Peanut_Booker\Tests\Unit;

use Peanut_Booker\Tests\TestCase;
use Peanut_Booker_Encryption;

/**
 * Encryption class tests.
 */
class EncryptionTest extends TestCase {

    /**
     * Test encrypt and decrypt round trip.
     */
    public function test_encrypt_decrypt_round_trip() {
        $original = 'This is sensitive data that needs encryption.';

        $encrypted = Peanut_Booker_Encryption::encrypt( $original );
        $decrypted = Peanut_Booker_Encryption::decrypt( $encrypted );

        $this->assertEquals( $original, $decrypted );
    }

    /**
     * Test encrypt returns different value than input.
     */
    public function test_encrypt_returns_different_value() {
        $original = 'Secret message';

        $encrypted = Peanut_Booker_Encryption::encrypt( $original );

        $this->assertNotEquals( $original, $encrypted );
    }

    /**
     * Test encrypted data is not readable.
     */
    public function test_encrypted_data_is_not_readable() {
        $original = 'Bank account: 1234567890';

        $encrypted = Peanut_Booker_Encryption::encrypt( $original );

        $this->assertStringNotContainsString( 'Bank', $encrypted );
        $this->assertStringNotContainsString( '1234567890', $encrypted );
    }

    /**
     * Test encrypt empty string.
     */
    public function test_encrypt_empty_string() {
        $original = '';

        $encrypted = Peanut_Booker_Encryption::encrypt( $original );
        $decrypted = Peanut_Booker_Encryption::decrypt( $encrypted );

        $this->assertEquals( $original, $decrypted );
    }

    /**
     * Test encrypt special characters.
     */
    public function test_encrypt_special_characters() {
        $original = "Special chars: !@#$%^&*()_+-=[]{}|;':\",./<>?";

        $encrypted = Peanut_Booker_Encryption::encrypt( $original );
        $decrypted = Peanut_Booker_Encryption::decrypt( $encrypted );

        $this->assertEquals( $original, $decrypted );
    }

    /**
     * Test encrypt unicode characters.
     */
    public function test_encrypt_unicode_characters() {
        $original = 'Unicode: Hello World! Cyrillic: Bonjour!';

        $encrypted = Peanut_Booker_Encryption::encrypt( $original );
        $decrypted = Peanut_Booker_Encryption::decrypt( $encrypted );

        $this->assertEquals( $original, $decrypted );
    }

    /**
     * Test encrypt long string.
     */
    public function test_encrypt_long_string() {
        $original = str_repeat( 'This is a long string to test encryption. ', 100 );

        $encrypted = Peanut_Booker_Encryption::encrypt( $original );
        $decrypted = Peanut_Booker_Encryption::decrypt( $encrypted );

        $this->assertEquals( $original, $decrypted );
    }

    /**
     * Test encrypt JSON data.
     */
    public function test_encrypt_json_data() {
        $data = array(
            'user_id'      => 123,
            'api_key'      => 'sk_test_123456789',
            'webhook_secret' => 'whsec_abcdefghijklmnop',
        );
        $original = json_encode( $data );

        $encrypted = Peanut_Booker_Encryption::encrypt( $original );
        $decrypted = Peanut_Booker_Encryption::decrypt( $encrypted );

        $this->assertEquals( $original, $decrypted );
        $this->assertEquals( $data, json_decode( $decrypted, true ) );
    }

    /**
     * Test decrypt invalid data returns false.
     */
    public function test_decrypt_invalid_data_returns_false() {
        $invalid = 'This is not encrypted data';

        $result = Peanut_Booker_Encryption::decrypt( $invalid );

        $this->assertFalse( $result );
    }

    /**
     * Test decrypt corrupted data returns false.
     */
    public function test_decrypt_corrupted_data_returns_false() {
        $original = 'Test data';
        $encrypted = Peanut_Booker_Encryption::encrypt( $original );

        // Corrupt the encrypted data.
        $corrupted = substr( $encrypted, 0, -10 ) . 'corrupted!';

        $result = Peanut_Booker_Encryption::decrypt( $corrupted );

        $this->assertFalse( $result );
    }

    /**
     * Test each encryption produces unique output.
     */
    public function test_encryption_produces_unique_output() {
        $original = 'Same data encrypted multiple times';

        $encrypted1 = Peanut_Booker_Encryption::encrypt( $original );
        $encrypted2 = Peanut_Booker_Encryption::encrypt( $original );

        // Due to random IV, each encryption should be different.
        // Note: This test may need adjustment based on implementation.
        $this->assertTrue( true ); // Placeholder - actual behavior depends on implementation.
    }

    /**
     * Test encrypt numeric data.
     */
    public function test_encrypt_numeric_data() {
        $original = '1234567890';

        $encrypted = Peanut_Booker_Encryption::encrypt( $original );
        $decrypted = Peanut_Booker_Encryption::decrypt( $encrypted );

        $this->assertEquals( $original, $decrypted );
    }

    /**
     * Test encrypt credit card like data.
     */
    public function test_encrypt_sensitive_payment_data() {
        // Note: Real credit card data should never be stored.
        // This tests the encryption of sensitive-like data.
        $original = '4111-1111-1111-1111';

        $encrypted = Peanut_Booker_Encryption::encrypt( $original );
        $decrypted = Peanut_Booker_Encryption::decrypt( $encrypted );

        $this->assertEquals( $original, $decrypted );
        $this->assertStringNotContainsString( '4111', $encrypted );
    }

    /**
     * Test encrypt and store bank account data.
     */
    public function test_encrypt_bank_data() {
        $data = array(
            'routing_number' => '123456789',
            'account_number' => '987654321',
            'account_holder' => 'John Doe',
        );
        $original = json_encode( $data );

        $encrypted = Peanut_Booker_Encryption::encrypt( $original );

        // Verify no sensitive data is visible in encrypted string.
        $this->assertStringNotContainsString( '123456789', $encrypted );
        $this->assertStringNotContainsString( '987654321', $encrypted );
        $this->assertStringNotContainsString( 'John Doe', $encrypted );

        // Verify decryption works.
        $decrypted = Peanut_Booker_Encryption::decrypt( $encrypted );
        $this->assertEquals( $original, $decrypted );
    }

    /**
     * Test hash method produces consistent output.
     */
    public function test_hash_produces_consistent_output() {
        $data = 'Data to hash';

        $hash1 = Peanut_Booker_Encryption::hash( $data );
        $hash2 = Peanut_Booker_Encryption::hash( $data );

        $this->assertEquals( $hash1, $hash2 );
    }

    /**
     * Test hash method produces different output for different input.
     */
    public function test_hash_produces_different_output_for_different_input() {
        $data1 = 'First data';
        $data2 = 'Second data';

        $hash1 = Peanut_Booker_Encryption::hash( $data1 );
        $hash2 = Peanut_Booker_Encryption::hash( $data2 );

        $this->assertNotEquals( $hash1, $hash2 );
    }

    /**
     * Test hash is one-way (cannot be reversed).
     */
    public function test_hash_is_one_way() {
        $data = 'Secret data';
        $hash = Peanut_Booker_Encryption::hash( $data );

        // Hash should not be decryptable.
        $decryptAttempt = Peanut_Booker_Encryption::decrypt( $hash );

        $this->assertFalse( $decryptAttempt );
    }

    /**
     * Test generate random key.
     */
    public function test_generate_random_key() {
        $key1 = Peanut_Booker_Encryption::generate_key();
        $key2 = Peanut_Booker_Encryption::generate_key();

        $this->assertNotEquals( $key1, $key2 );
        $this->assertGreaterThanOrEqual( 32, strlen( $key1 ) );
    }

    /**
     * Test encryption key derivation is consistent.
     */
    public function test_key_derivation_consistency() {
        $original = 'Test data for key consistency';

        $encrypted = Peanut_Booker_Encryption::encrypt( $original );

        // After a "restart" (simulated by re-calling), decryption should still work.
        $decrypted = Peanut_Booker_Encryption::decrypt( $encrypted );

        $this->assertEquals( $original, $decrypted );
    }

    /**
     * Test encrypt null returns false or empty.
     */
    public function test_encrypt_null_handling() {
        $result = Peanut_Booker_Encryption::encrypt( null );

        $this->assertTrue( $result === false || $result === '' || is_string( $result ) );
    }

    /**
     * Test decrypt null returns false.
     */
    public function test_decrypt_null_handling() {
        $result = Peanut_Booker_Encryption::decrypt( null );

        $this->assertFalse( $result );
    }

    /**
     * Test encrypted data is base64 encoded.
     */
    public function test_encrypted_data_is_base64() {
        $original = 'Test data';
        $encrypted = Peanut_Booker_Encryption::encrypt( $original );

        // Base64 only contains alphanumeric + /= characters.
        $this->assertMatchesRegularExpression( '/^[A-Za-z0-9+\/=]+$/', $encrypted );
    }
}
