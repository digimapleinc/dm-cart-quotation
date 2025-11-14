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
	 * @return void
	 */
	public static function wcq_delete_session( $customer_id ) {
		global $wpdb;

		$table = $wpdb->prefix . 'woocommerce_sessions';

		// @codingStandardsIgnoreStart
		$wpdb->delete(
			$table,
			[
				'session_key' => $customer_id,
			]
		);
		// @codingStandardsIgnoreEnd
	}
}
