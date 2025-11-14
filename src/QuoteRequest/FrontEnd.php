<?php

namespace WooQuoteRequest\QuoteRequest;

use WooQuoteRequest\QuoteRequest\Session;
use WooQuoteRequest\Settings\Utilities;

/**
 * Class FrontEnd
 *
 * Handles the core functionality for managing quotations in WooCommerce. This includes
 * initializing shortcodes, processing quotations, managing cart actions, and handling
 * AJAX requests related to quotations.
 *
 * @package WooQuoteRequest\QuoteRequest
 */
class FrontEnd {



	private static $_url_var = 'quotation';
	/**
	 * Initializes the shortcode and hooks for quotation functionality.
	 *
	 * Registers actions and filters for processing quotations, adding buttons, and
	 * handling AJAX requests for creating and managing quotations.
	 *
	 * @return void
	 */
	public static function initialize() {

		add_filter( 'query_vars', [ self::class, 'add_query_vars_filter' ] );

		// Buffer the output
		ob_start();
		add_action( 'wp_head', [ self::class, 'process_quotation' ] );

		add_action( 'woocommerce_before_cart', [ self::class, 'dm_quotation_button' ] );

		add_filter( 'render_block', [ self::class, 'dm_woocommerce_cart_block_do_actions' ], 9999, 2 );

		// Admin-only AJAX actions - no nopriv needed
		add_action( 'wp_ajax_create_quotation_link', [ self::class, 'create_quotation_link' ] );
		add_action( 'wp_ajax_empty_cart_quotation', [ self::class, 'empty_cart_quotation' ] );
	
		add_action('woocommerce_checkout_create_order', [ self::class, 'dm_add_quotation_token_checkout'], 10, 2);

		add_action('woocommerce_store_api_checkout_update_order_meta', [ self::class,'dm_add_quotation_token_checkout_rest'], 10, 1);
	
	}

	/**
	 * Adds a custom query variable to the list of recognized query variables.
	 *
	 * This method appends a custom query variable, defined by the class property
	 * `$_url_var`, to the array of query variables. This allows WordPress to
	 * recognize and handle the custom query variable in URLs.
	 *
	 * @param array $vars An array of current query variables.
	 * @return array The modified array of query variables including the custom query variable.
	 */
	public static function add_query_vars_filter( $vars ) {
		$vars[] = self::$_url_var;
		return $vars;
	}

	/**
	 * Adds the quotation token to the order during checkout.
	 *
	 * @param \WooQuoteRequest\QuoteRequest\WC_Order $order The order object.
	 * @param array                                  $data The data associated with the order.
	 * @return void
	 */
	public static function dm_add_quotation_token_checkout($order, $data)
	{
		$quotation_token = WC()->session->get('quotation_token');
		
		if ($quotation_token) {
			$order->update_meta_data('quotation_token', $quotation_token);
		}
	}

	/**
	 * Adds the quotation token to the order during checkout via the REST API.
	 *
	 * @param \WooQuoteRequest\QuoteRequest\WC_Order $order The order object.
	 * @param array                                  $data The data associated with the order.
	 * @return void
	 */
	public static function dm_add_quotation_token_checkout_rest($order)
	{
		$quotation_token = WC()->session->get('quotation_token');
		if ($quotation_token) {
			$order->update_meta_data('quotation_token', $quotation_token);
		}
	}



	public static function dm_woocommerce_cart_block_do_actions( $block_content, $block ) {
		$blocks = [
			'woocommerce/cart',
		];
		if ( in_array( $block['blockName'], $blocks ) ) {
			ob_start();

			self::dm_quotation_button();

			do_action( 'dm_before_' . $block['blockName'] );
			echo wp_kses_post( $block_content );
			do_action( 'dm_after_' . $block['blockName'] );
			$block_content = ob_get_contents();
			ob_end_clean();
		}

		return $block_content;
	}
	/**
	 * Displays the quotation buttons on the front-end for administrators.
	 *
	 * Adds "Send Quote" and "Empty Cart" buttons to a fixed position on the page.
	 *
	 * @return void
	 */
	public static function dm_quotation_button() {

		if ( is_user_logged_in() && current_user_can( 'administrator' ) ) {
			$settings = Utilities::wcq_get_settings();
			if ( ! $settings ) {
				return;
			}
			// Extract settings for buttons
			$quote_settings      = $settings['button_quote'] ?? [];
			$empty_cart_settings = $settings['button_empty_cart'] ?? [];

			// Define default values in case settings are missing
			$quote_padding          = $quote_settings['padding'] ?? [
				'top'    => 10,
				'right'  => 10,
				'bottom' => 10,
				'left'   => 10,
			];
			$quote_background_color = $quote_settings['background_color'] ?? '#000';
			$quote_text_color       = $quote_settings['text_color'] ?? '#fff';
			$quote_hover_background = $quote_settings['hover_background'] ?? '#000';
			$quote_hover_text_color = $quote_settings['hover_text_color'] ?? '#fff';
			$quote_position         = $quote_settings['position'] ?? 'top_right';
			$quote_position_one     = $quote_settings['position_one_per'] ?? '10';
			$quote_position_two     = $quote_settings['position_two_per'] ?? '5';

			$empty_cart_padding          = $empty_cart_settings['padding'] ?? [
				'top'    => 10,
				'right'  => 10,
				'bottom' => 10,
				'left'   => 10,
			];
			$empty_cart_background_color = $empty_cart_settings['background_color'] ?? '#000';
			$empty_cart_text_color       = $empty_cart_settings['text_color'] ?? '#fff';
			$empty_cart_hover_background = $empty_cart_settings['hover_background'] ?? '#000';
			$empty_cart_hover_text_color = $empty_cart_settings['hover_text_color'] ?? '#fff';
			$empty_cart_position         = $empty_cart_settings['position'] ?? 'top_right';
			$empty_cart_position_one     = $empty_cart_settings['position_one_per'] ?? '20';
			$empty_cart_position_two     = $empty_cart_settings['position_two_per'] ?? '10';

			// Apply position and styles for Send Quote button
			$quote_position_styles = '';
			if ( $quote_position === 'top_right' ) {
				$quote_position_styles = 'top: ' . $quote_position_one . '%; right: ' . $quote_position_two . '%;';
			} elseif ( $quote_position === 'top_center' ) {
				$quote_position_styles = 'top: ' . $quote_position_one . '%; left: 50%; transform: translateX(-50%);';
			} elseif ( $quote_position === 'top_left' ) {
				$quote_position_styles = 'top: ' . $quote_position_one . '%; left: ' . $quote_position_two . '%;';
			} elseif ( $quote_position === 'bottom_right' ) {
				$quote_position_styles = 'bottom: ' . $quote_position_one . '%; right: ' . $quote_position_two . '%;';
			} elseif ( $quote_position === 'bottom_center' ) {
				$quote_position_styles = 'bottom: ' . $quote_position_one . '%; left: 50%; transform: translateX(-50%);';
			} elseif ( $quote_position === 'bottom_left' ) {
				$quote_position_styles = 'bottom: ' . $quote_position_one . '%; left: ' . $quote_position_two . '%;';
			}

			// Apply position and styles for Empty Cart button
			$empty_cart_position_styles = '';
			if ( $empty_cart_position === 'top_right' ) {
				$empty_cart_position_styles = 'top: ' . $empty_cart_position_one . '%; right: ' . $empty_cart_position_two . '%;';
			} elseif ( $empty_cart_position === 'top_center' ) {
				$empty_cart_position_styles = 'top: ' . $empty_cart_position_one . '%; left: 50%; transform: translateX(-50%);';
			} elseif ( $empty_cart_position === 'top_left' ) {
				$empty_cart_position_styles = 'top: ' . $empty_cart_position_one . '%; left: ' . $empty_cart_position_two . '%;';
			} elseif ( $empty_cart_position === 'bottom_right' ) {
				$empty_cart_position_styles = 'bottom: ' . $empty_cart_position_one . '%; right: ' . $empty_cart_position_two . '%;';
			} elseif ( $empty_cart_position === 'bottom_center' ) {
				$empty_cart_position_styles = 'bottom: ' . $empty_cart_position_one . '%; left: 50%; transform: translateX(-50%);';
			} elseif ( $empty_cart_position === 'bottom_left' ) {
				$empty_cart_position_styles = 'bottom: ' . $empty_cart_position_one . '%; left: ' . $empty_cart_position_two . '%;';
			}

			?>
			<div id="send-quote" style="position: fixed; z-index: 99; <?php echo esc_attr( $quote_position_styles ); ?> width: 120px;">
				<button id="wcq-create-quotation" class="button wp-element-button" style="
					padding: <?php echo esc_attr( $quote_padding['top'] ); ?>px <?php echo esc_attr( $quote_padding['right'] ); ?>px <?php echo esc_attr( $quote_padding['bottom'] ); ?>px <?php echo esc_attr( $quote_padding['left'] ); ?>px;
					background-color: <?php echo esc_attr( $quote_background_color ); ?>;
					color: <?php echo esc_attr( $quote_text_color ); ?>;"
					onmouseover="this.style.backgroundColor='<?php echo esc_attr( $quote_hover_background ); ?>'; this.style.color='<?php echo esc_attr( $quote_hover_text_color ); ?>';"
					onmouseout="this.style.backgroundColor='<?php echo esc_attr( $quote_background_color ); ?>'; this.style.color='<?php echo esc_attr( $quote_text_color ); ?>';">
					<?php echo esc_html( $quote_settings['text'] ); ?>
				</button>
			</div>
			<div id="empty-cart" style="position: fixed; z-index: 99; <?php echo esc_attr( $empty_cart_position_styles ); ?> width: 120px;">
				<button id="wcq-empty-cart" class="button wp-element-button" style="
					padding: <?php echo esc_attr( $empty_cart_padding['top'] ); ?>px <?php echo esc_attr( $empty_cart_padding['right'] ); ?>px <?php echo esc_attr( $empty_cart_padding['bottom'] ); ?>px <?php echo esc_attr( $empty_cart_padding['left'] ); ?>px;
					background-color: <?php echo esc_attr( $empty_cart_background_color ); ?>;
					color: <?php echo esc_attr( $empty_cart_text_color ); ?>;"
					onmouseover="this.style.backgroundColor='<?php echo esc_attr( $empty_cart_hover_background ); ?>'; this.style.color='<?php echo esc_attr( $empty_cart_hover_text_color ); ?>';"
					onmouseout="this.style.backgroundColor='<?php echo esc_attr( $empty_cart_background_color ); ?>'; this.style.color='<?php echo esc_attr( $empty_cart_text_color ); ?>';">
					<?php echo esc_html( $empty_cart_settings['text'] ); ?>
				</button>
			</div>
			<?php

			self::handle_frontend_notices();

			Utilities::wcq_reploader();
		}
	}

	public static function handle_frontend_notices() {
		$settings = Utilities::wcq_get_settings();

		$ns     = $settings['notices'];
		$style  = '';
		$style .= 'border-radius: ' . $ns['border_radius'] . 'px;';

		?>
		<style>
			.wcq-notice-success {
				padding: 5px;
				border-radius: <?php echo esc_attr( $ns['border_radius'] ); ?>px;
				background-color: <?php echo esc_attr( $ns['success']['background_color'] ); ?>;
				color: <?php echo esc_attr( $ns['success']['text_color'] ); ?>;
			}

			.wcq-notice-error {
				padding: 5px;
				border-radius: <?php echo esc_attr( $ns['border_radius'] ); ?>px;
				background-color: <?php echo esc_attr( $ns['error']['background_color'] ); ?>;
				color: <?php echo esc_attr( $ns['error']['text_color'] ); ?>;
			}
		</style>

		<p id="copy-status" style="position: fixed; bottom: 10px; left: 50%; transform: translateX(-50%); z-index: 99;"></p>
		<?php
	}


	/**
	 * Empties the cart and redirects the user to the cart page.
	 *
	 * Deletes all items from the cart and provides feedback to the user.
	 *
	 * @return void
	 */
	public static function empty_cart_quotation() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wcq_empty_cart' ) ) {
			wp_send_json_error(
				[
					'message' => 'Security verification failed',
					'status'  => 'error',
				]
			);
			wp_die();
		}

		// Check if user is logged in and has administrator capability
		if ( ! is_user_logged_in() || ! current_user_can( 'administrator' ) ) {
			wp_send_json_error(
				[
					'message' => 'You are not allowed to perform this action',
					'status'  => 'error',
				]
			);
			wp_die();
		}

		if ( WC()->cart->is_empty() ) {
			wp_send_json_error(
				[
					'message' => 'Cart is already empty',
					'status'  => 'error',
				]
			);
			wp_die();
		}

		WC()->cart->empty_cart();

		$messages = Utilities::wcq_get_setting( 'messages' );

		try {
			$result = [
				'message'  => sprintf( '%s', $messages['success']['cart_empty'] ),
				'status'   => 'success',
				'redirect' => wc_get_cart_url(),
			];
		} catch ( \Throwable $e ) {
			$result = [
				'message' => sprintf( '%s', $messages['error']['cart_empty'] ),
				'status'  => 'error',
			];
		}

		wp_send_json_success( $result ); // Send a JSON success response
		wp_die();
	}

	/**
	 * Processes the quotation request and adds items to the cart.
	 *
	 * Redirects to the cart page after adding items from the quotation.
	 *
	 * @return void
	 */
	public static function process_quotation() {
		// Test if the query exists at the URL
		if ( get_query_var( self::$_url_var ) ) {

			// If so echo the value
			$token = get_query_var( self::$_url_var );
			if ( isset( $token ) && sanitize_text_field( wp_unslash( $token ) ) ) {
				if ( isset( WC()->session ) && ! WC()->session->has_session() ) {
					WC()->session->set_customer_session_cookie( true );
				}

				self::add_items_to_cart();

				wp_safe_redirect( site_url( '/cart' ) );
			}
		}
	}

	/**
	 * Adds items to the cart based on the quotation token.
	 *
	 * @return void
	 */
	public static function add_items_to_cart() {
		if ( get_query_var( self::$_url_var ) ) {
			$token = get_query_var( self::$_url_var );
			if ( isset( $token ) ) {
				$token = sanitize_text_field( wp_unslash( $token ) );

				$cart = Session::wcq_get_session( $token );

				if ( $cart ) {
					// Set the quotation token in the session and save in the database
					wc()->session->set('quotation_token', $token );
					foreach ( $cart as $item ) {
						$product_id = $item['product_id'];
						$quantity   = $item['quantity'];
						unset( $item['data'] );
						WC()->cart->add_to_cart( $product_id, $quantity, 0, [], $item );
					}
				}
			}
		}
	}

	/**
	 * Creates a quotation link and sends a JSON response.
	 *
	 * @return void
	 */
	public static function create_quotation_link() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wcq_create_quotation' ) ) {
			wp_send_json_error(
				[
					'message' => 'Security verification failed',
					'status'  => 'error',
				]
			);
			wp_die();
		}

		// Check if user is logged in and has administrator capability
		if ( ! is_user_logged_in() || ! current_user_can( 'administrator' ) ) {
			wp_send_json_error(
				[
					'message' => 'You are not allowed to perform this action',
					'status'  => 'error',
				]
			);
			wp_die();
		}

		if ( WC()->cart->is_empty() ) {
			wp_send_json_error(
				[
					'message' => 'Your cart is empty. Cannot Create Quotation',
					'status'  => 'error',
				]
			);
			wp_die();
		}

		$quotation_token = self::save_quotation();
		$messages        = Utilities::wcq_get_setting( 'messages' );

		$quotation_url = add_query_arg(
			[
				'quotation' => $quotation_token,

			],
			site_url( '/cart/' )
		);

		try {
			$result = [
				'link'    => $quotation_url,
				'message' => sprintf( '%s', $messages['success']['cart_quote'] ),
				'status'  => 'success',
			];
		} catch ( \Throwable $e ) {
			$result = [
				'message' => sprintf( '%s', $messages['error']['cart_quote'] ),
				'status'  => 'error',
			];
		}

		wp_send_json_success( $result ); // Send a JSON success response
		wp_die();
	}





	/**
	 * Saves the quotation data as a custom session in the database.
	 *
	 * @return string|false The quotation token if successful, false otherwise.
	 */
	public static function save_quotation() {

		$_customer_id = Session::wcq_generate_customer_id();
		$_data        = WC()->cart->get_cart();
		$data         = Session::wcq_save_session_as_quotation( $_data, $_customer_id );

		if ( $data ) {
			return $_customer_id;
		} else {
			return false;
		}
	}
}
