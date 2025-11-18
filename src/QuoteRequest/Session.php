<?php

namespace WooQuoteRequest\QuoteRequest;

/**
 * Class Main
 *
 * Handles the core functionality for managing quotations in WooCommerce. This includes
 * initializing shortcodes, processing quotations, managing cart actions, and handling
 * AJAX requests related to quotations.
 *
 * @package WooQuoteRequest\QuoteRequest
 */
class Session {


	/**
	 * Generates a unique customer ID for the quotation session.
	 *
	 * @return string The generated customer ID.
	 */
	public static function wcq_generate_customer_id() {
		$user_id = get_current_user_id() ? get_current_user_id() : 'q';
		$random_token = generate_quote_hash( 16 ); // Generates 32-character hex string
		return $user_id . '_' . $random_token;
	}

	/**
	 * Saves the quotation data as a custom session in the database.
	 *
	 * @param array  $cart_data An array of cart data items.
	 * @param string $_customer_id The customer ID associated with the quotation.
	 * @return string|false The quotation token if successful, false otherwise.
	 */
	public static function wcq_save_session_as_quotation( $cart_data, $_customer_id ) {
		global $wpdb;

		$table = $wpdb->prefix . 'woocommerce_sessions';
		$_session_expiration = time() + intval( apply_filters( 'wc_session_expiration', 60 * 60 * 360 ) ); // 15 days

		$data = array(
			'session_key' => $_customer_id,
			'session_value' => maybe_serialize( $cart_data ),
			'session_expiry' => $_session_expiration
		);
		$format = array( '%s', '%s', '%s' );

		// @codingStandardsIgnoreStart
		$wpdb->insert( $table, $data, $format );
		// @codingStandardsIgnoreEnd
		$insert_id = $wpdb->insert_id;

		if ( $insert_id ) {
			return $_customer_id;
		} else {
			// Log error if insert failed
			if ( ! empty( $wpdb->last_error ) ) {
				error_log( sprintf( 'WCQ: Failed to save quotation session. Error: %s', $wpdb->last_error ) );
			}
			return false;
		}
	}

	/**
	 * Retrieves a session from the database based on the customer ID.
	 *
	 * @param string $customer_id The customer ID associated with the session.
	 *
	 * @return mixed The session data if found, false otherwise.
	 */
	public static function wcq_get_session( $customer_id ) {
		global $wpdb;
		$value = false;

		if ( $customer_id ) {
			$table = $wpdb->prefix . 'woocommerce_sessions';
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$value = $wpdb->get_var( $wpdb->prepare( "SELECT session_value FROM {$table} WHERE session_key = %s", $customer_id ) );
		}

		return maybe_unserialize( $value );
	}

	/**
	 * Deletes a session from the database.
	 *
	 * @param string $customer_id The customer ID associated with the session to delete.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function wcq_delete_session( $customer_id ) {
		global $wpdb;

		$table = $wpdb->prefix . 'woocommerce_sessions';

		// @codingStandardsIgnoreStart
		$result = $wpdb->delete(
			$table,
			[
				'session_key' => $customer_id,
			]
		);
		// @codingStandardsIgnoreEnd

		if ( $result === false ) {
			// Log error if delete failed
			if ( ! empty( $wpdb->last_error ) ) {
				error_log( sprintf( 'WCQ: Failed to delete quotation session %s. Error: %s', $customer_id, $wpdb->last_error ) );
			}
			return false;
		}

		return true;
	}

	/**
	 * Cleans up expired quotation sessions from the database.
	 *
	 * This method is called by the WP-Cron job to remove old sessions
	 * and prevent database bloat. It removes all quotation sessions (identified
	 * by session_key pattern) that have expired.
	 *
	 * @return int Number of sessions deleted.
	 */
	public static function wcq_cleanup_expired_sessions() {
		global $wpdb;

		$table = $wpdb->prefix . 'woocommerce_sessions';
		$current_time = time();

		// Delete all expired quotation sessions (session keys contain underscore separator)
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$deleted = $wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"DELETE FROM {$table} WHERE session_key LIKE %s AND session_expiry < %d",
				'%' . $wpdb->esc_like( '_' ) . '%',
				$current_time
			)
		);

		if ( $deleted === false ) {
			// Log error if deletion failed
			error_log( 'WCQ: Failed to cleanup expired quotation sessions' );
			return 0;
		}

		if ( $deleted > 0 ) {
			error_log( sprintf( 'WCQ: Cleaned up %d expired quotation session(s)', $deleted ) );
		}

		return $deleted;
	}
}
