<?php

namespace WooQuoteRequest\Settings;

use WooQuoteRequest\Settings\Administration;

/**
 * Class Utilities
 *
 * Provides utility functions for managing plugin settings, activation, and localization.
 * This class includes methods for handling plugin activation and deactivation, managing
 * settings, and loading text domains for translations.
 *
 * @package WooQuoteRequest\Settings
 */
class Utilities {


	/**
	 * Initializes the Utilities class by setting up necessary hooks.
	 *
	 * @return void
	 */
	public static function initialize() {
		add_action( 'init', [ self::class, 'load_wcq_textdomain' ] );
		add_action( 'wcq_cleanup_expired_sessions', [ 'WooQuoteRequest\QuoteRequest\Session', 'wcq_cleanup_expired_sessions' ] );
	}

	/**
	 * Returns the current version of the plugin.
	 *
	 * @return string The current plugin version.
	 */
	public static function plugin_version() {
		$plugin = get_plugin_data( WCQ_FILE );

		return $plugin['Version'];
	}

	/**
	 * Handles plugin activation.
	 *
	 * This method checks if another plugin (`woo-cart-quotation`) is active and deactivates
	 * it if necessary. It then sets default settings for the plugin and schedules cleanup cron.
	 *
	 * @link https://developer.wordpress.org/reference/functions/register_activation_hook/
	 *
	 * @return void
	 */
	public static function handle_activation() {
		if ( is_plugin_active( 'woo-cart-quotation/woo-cart-quotation.php' ) ) {
			deactivate_plugins( 'woo-cart-quotation/woo-cart-quotation.php', true );
		}

		self::maybe_set_default_settings();
		self::schedule_cleanup_cron();
	}

	/**
	 * Applies the default settings to the site if no settings exist.
	 *
	 * This method adds the default settings to the database if no settings are currently
	 * available.
	 *
	 * @return void
	 */
	public static function maybe_set_default_settings() {
		$default_settings = Administration::default_settings();

		add_option( 'wcq_settings', $default_settings );
	}

	/**
	 * Handles plugin deactivation.
	 *
	 * This method deletes all plugin settings from the database upon deactivation
	 * and clears scheduled cron jobs.
	 *
	 * @link https://developer.wordpress.org/reference/functions/register_deactivation_hook/
	 *
	 * @return void
	 */
	public static function handle_deactivation() {
		// Delete WCQ settings
		delete_option( 'wcq_settings' );

		// Clear scheduled cron jobs
		self::clear_scheduled_cron();
	}

	/**
	 * Loads the plugin text domain for translations.
	 *
	 * This method loads the text domain to enable localization and translation of
	 * plugin text.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/init/
	 *
	 * @return void
	 */
	public static function load_wcq_textdomain() {
		load_plugin_textdomain( 'dm-cart-quotation', false, WCQ_DIR . '/languages' );
	}

	/**
	 * Retrieves all WCQ settings.
	 *
	 * @return array An array of plugin settings.
	 */
	public static function wcq_get_settings() {
		$settings = get_option( 'wcq_settings', [] );

		/**
		 * Filter 'wcq_get_settings'.
		 *
		 * @param array $settings Array of plugin settings.
		 */
		return apply_filters( 'wcq_get_settings', $settings );
	}

	/**
	 * Retrieves a specific WCQ setting by name.
	 *
	 * @param string $name          The setting name.
	 * @param mixed  $default_value Optional setting value. Default false.
	 *
	 * @return mixed The setting value.
	 */
	public static function wcq_get_setting( $name, $default_value = false ) {
		$value    = $default_value;
		$settings = self::wcq_get_settings();

		if ( isset( $settings[ $name ] ) ) {
			$value = $settings[ $name ];
		}

		/**
		 * Filter 'wcq_get_setting'.
		 *
		 * @param mixed  $value         The setting value.
		 * @param string $name          The setting name.
		 * @param mixed  $default_value Optional setting value.
		 */
		return apply_filters( 'wcq_get_setting', $value, $name, $default_value );
	}

	public static function wcq_reploader() {
		?>
		<div id="wcq-preloader">
			<div class="wcq-loader"></div>
		</div>

		<?php
	}

	/**
	 * Schedules the WP-Cron job for cleaning up expired sessions.
	 *
	 * Runs daily to remove expired quotation sessions from the database.
	 *
	 * @return void
	 */
	public static function schedule_cleanup_cron() {
		if ( ! wp_next_scheduled( 'wcq_cleanup_expired_sessions' ) ) {
			wp_schedule_event( time(), 'daily', 'wcq_cleanup_expired_sessions' );
		}
	}

	/**
	 * Clears all scheduled WP-Cron jobs for this plugin.
	 *
	 * Called during plugin deactivation.
	 *
	 * @return void
	 */
	public static function clear_scheduled_cron() {
		$timestamp = wp_next_scheduled( 'wcq_cleanup_expired_sessions' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'wcq_cleanup_expired_sessions' );
		}
	}
}
