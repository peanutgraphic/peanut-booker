<?php
/**
 * Data encryption for sensitive fields.
 *
 * Uses AES-256-CBC encryption with WordPress salts for key derivation.
 *
 * @package Peanut_Booker
 * @since   1.3.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Encryption class for protecting sensitive data at rest.
 */
class Peanut_Booker_Encryption {

	/**
	 * Cipher method to use.
	 */
	const CIPHER = 'aes-256-cbc';

	/**
	 * Prefix for encrypted values to identify them.
	 */
	const ENCRYPTED_PREFIX = '$PB_ENC$';

	/**
	 * Get the encryption key derived from WordPress salts.
	 *
	 * @return string 32-byte key for AES-256.
	 */
	private static function get_key() {
		// Use WordPress salts combined for key derivation.
		$salt = defined( 'AUTH_KEY' ) ? AUTH_KEY : 'peanut-booker-default-key';
		$salt .= defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : '';

		// Derive a 32-byte key using PBKDF2.
		return hash_pbkdf2( 'sha256', $salt, 'peanut-booker-encryption', 10000, 32, true );
	}

	/**
	 * Encrypt a value.
	 *
	 * @param string $value The plaintext value to encrypt.
	 * @return string The encrypted value (base64 encoded with IV prepended).
	 */
	public static function encrypt( $value ) {
		if ( empty( $value ) || ! is_string( $value ) ) {
			return $value;
		}

		// Don't double-encrypt.
		if ( self::is_encrypted( $value ) ) {
			return $value;
		}

		$key = self::get_key();
		$iv_length = openssl_cipher_iv_length( self::CIPHER );
		$iv = openssl_random_pseudo_bytes( $iv_length );

		$encrypted = openssl_encrypt( $value, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv );

		if ( false === $encrypted ) {
			// Encryption failed, return original value.
			return $value;
		}

		// Prepend IV to encrypted data and base64 encode, with prefix.
		return self::ENCRYPTED_PREFIX . base64_encode( $iv . $encrypted );
	}

	/**
	 * Decrypt a value.
	 *
	 * @param string $value The encrypted value.
	 * @return string The decrypted plaintext value.
	 */
	public static function decrypt( $value ) {
		if ( empty( $value ) || ! is_string( $value ) ) {
			return $value;
		}

		// Check if value is actually encrypted.
		if ( ! self::is_encrypted( $value ) ) {
			return $value;
		}

		// Remove prefix.
		$encoded = substr( $value, strlen( self::ENCRYPTED_PREFIX ) );

		$data = base64_decode( $encoded );
		if ( false === $data ) {
			return $value;
		}

		$key = self::get_key();
		$iv_length = openssl_cipher_iv_length( self::CIPHER );

		// Extract IV from beginning of data.
		$iv = substr( $data, 0, $iv_length );
		$encrypted = substr( $data, $iv_length );

		$decrypted = openssl_decrypt( $encrypted, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv );

		if ( false === $decrypted ) {
			// Decryption failed, return original value.
			return $value;
		}

		return $decrypted;
	}

	/**
	 * Check if a value is encrypted.
	 *
	 * @param string $value Value to check.
	 * @return bool True if encrypted.
	 */
	public static function is_encrypted( $value ) {
		if ( ! is_string( $value ) ) {
			return false;
		}
		return strpos( $value, self::ENCRYPTED_PREFIX ) === 0;
	}

	/**
	 * Encrypt multiple fields in an array.
	 *
	 * @param array $data   Data array.
	 * @param array $fields Field names to encrypt.
	 * @return array Data with specified fields encrypted.
	 */
	public static function encrypt_fields( $data, $fields ) {
		foreach ( $fields as $field ) {
			if ( isset( $data[ $field ] ) && ! empty( $data[ $field ] ) ) {
				$data[ $field ] = self::encrypt( $data[ $field ] );
			}
		}
		return $data;
	}

	/**
	 * Decrypt multiple fields in an object or array.
	 *
	 * @param object|array $data   Data object or array.
	 * @param array        $fields Field names to decrypt.
	 * @return object|array Data with specified fields decrypted.
	 */
	public static function decrypt_fields( $data, $fields ) {
		$is_object = is_object( $data );

		foreach ( $fields as $field ) {
			if ( $is_object && isset( $data->$field ) && ! empty( $data->$field ) ) {
				$data->$field = self::decrypt( $data->$field );
			} elseif ( ! $is_object && isset( $data[ $field ] ) && ! empty( $data[ $field ] ) ) {
				$data[ $field ] = self::decrypt( $data[ $field ] );
			}
		}

		return $data;
	}

	/**
	 * Get list of sensitive booking fields that should be encrypted.
	 *
	 * @return array Field names.
	 */
	public static function get_booking_encrypted_fields() {
		return array(
			'event_address',
			'event_zip',
		);
	}

	/**
	 * Get list of sensitive customer fields that should be encrypted.
	 *
	 * @return array Field names.
	 */
	public static function get_customer_encrypted_fields() {
		return array(
			'phone',
			'billing_phone',
		);
	}

	/**
	 * Encrypt booking data before storage.
	 *
	 * @param array $data Booking data.
	 * @return array Encrypted booking data.
	 */
	public static function encrypt_booking_data( $data ) {
		return self::encrypt_fields( $data, self::get_booking_encrypted_fields() );
	}

	/**
	 * Decrypt booking data after retrieval.
	 *
	 * @param object|array $booking Booking data.
	 * @return object|array Decrypted booking data.
	 */
	public static function decrypt_booking_data( $booking ) {
		return self::decrypt_fields( $booking, self::get_booking_encrypted_fields() );
	}
}
