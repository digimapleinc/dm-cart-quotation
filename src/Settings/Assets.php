<?php

namespace WooQuoteRequest\Settings;

/**
 * Class Assets
 *
 * Handles the enqueuing of stylesheets and scripts for the WooCommerce Cart Quotation plugin.
 *
 * This class is responsible for loading necessary CSS and JavaScript files for both
 * the front-end settings page and the admin area. It ensures that all required assets
 * are properly loaded when needed.
 *
 * @package WooQuoteRequest\Settings
 */
class Assets
{


	/**
	 * Initializes the asset loading for the plugin.
	 *
	 * This method hooks into WordPress actions to enqueue the necessary stylesheets and
	 * scripts for both the front-end settings page and the admin area.
	 *
	 * @return void
	 */
	public static function initialize()
	{
		add_action('wp_enqueue_scripts', [self::class, 'add_settings_assets']);
		add_action('admin_enqueue_scripts', [self::class, 'wcq_enqueue_admin_style']);
	}

	/**
	 * Enqueues the settings stylesheet and scripts on the front-end settings page.
	 *
	 * This method is called on the `wp_enqueue_scripts` action and is responsible
	 * for adding the plugin's front-end CSS and JavaScript files. It also localizes
	 * the script with the AJAX URL for handling AJAX requests.
	 *
	 * @return void
	 */
	public static function add_settings_assets()
	{


		if (is_page( wc_get_page_id( 'cart' ) )) {

			// Enqueue the stylesheet
			wp_enqueue_style(
				'wcq-style', // Handle
				WCQ_URL . 'assets/css/common.css', // Source
				[], // Dependencies
				'0.0.1' // Version
			);

			// Enqueue the JavaScript file
			wp_enqueue_script(
				'wcq-script-common', // Handle
				WCQ_URL . 'assets/js/wcq-common.js', // Source
				['jquery'], // Dependencies
				'0.0.1', // Version
				true // Load in footer
			);
			wp_enqueue_script(
				'wcq-script', // Handle
				WCQ_URL . 'assets/js/wcq-js.js', // Source
				['jquery', 'wcq-script-common'], // Dependencies
				'0.0.1', // Version
				false // Load in header
			);
			wp_localize_script(
				'wcq-script',
				'ajax_object',
				[
					'ajax_url' => admin_url('admin-ajax.php'),
					'create_quotation_nonce' => wp_create_nonce('wcq_create_quotation'),
					'empty_cart_nonce' => wp_create_nonce('wcq_empty_cart'),
				]
			);
		}
	}

	/**
	 * Enqueues admin styles and scripts for the plugin.
	 *
	 * This method is called on the `admin_enqueue_scripts` action and is responsible
	 * for adding the plugin's admin CSS and JavaScript files. It also includes the WordPress
	 * color picker and localizes the admin script with AJAX URL and nonce for security.
	 *
	 * @return void
	 */
	public static function wcq_enqueue_admin_style()
	{
	 
		// Enqueue admin stylesheet
		wp_register_style('wcq-admin-common-css', WCQ_URL . 'assets/css/common.css', false, '1.0.0');
		wp_register_style('wcq-admin-css', WCQ_URL . 'assets/css/admin-style.css', false, '1.0.0');
		wp_enqueue_style('wcq-admin-common-css');
		wp_enqueue_style('wcq-admin-css');

		// Enqueue WordPress color picker styles and scripts
		wp_enqueue_script('wp-color-picker');
		wp_enqueue_style('wp-color-picker');

		// Enqueue custom admin script
		wp_enqueue_script(
			'wcq-admin-script-common', // Handle
			WCQ_URL . 'assets/js/wcq-common.js', // Source
			['jquery'], // Dependencies
			'0.0.1', // Version
			true // Load in footer
		);
		wp_enqueue_script(
			'wcq-admin-script', // Handle
			WCQ_URL . 'assets/js/wcq-admin-script.js', // Source
			['jquery', 'wcq-admin-script-common'], // Dependencies
			'0.0.1', // Version
			true // Load in footer
		);

		// Localize the admin script with AJAX URL and nonce
		wp_localize_script(
			'wcq-admin-script',
			'wcqForm',
			[
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce'    => wp_create_nonce('custom_form_nonce'),
			]
		);
	}
}
