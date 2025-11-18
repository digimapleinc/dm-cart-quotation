<?php

namespace WooQuoteRequest\Settings;

use WooQuoteRequest\Settings\Utilities;

/**
 * Class Administration
 *
 * Handles the administration settings for the WooCommerce Cart Quotation plugin.
 *
 * This class is responsible for registering settings menus, displaying settings pages,
 * and handling AJAX requests for saving plugin settings. It also provides methods
 * for rendering settings fields and defining default settings.
 *
 * @package WooQuoteRequest\Settings
 */
class Administration {



	/**
	 * Initializes the administration settings for the plugin.
	 *
	 * This method sets up actions to register settings menus and handle settings
	 * via AJAX. It hooks into WordPress actions to add the settings menu and initialize
	 * the settings on the admin side.
	 *
	 * @return void
	 */
	public static function initialize() {
		add_action( 'admin_menu', [ self::class, 'register_settings_menu' ] );
		add_action( 'admin_init', [ self::class, 'register_settings' ] );

		add_action( 'wp_ajax_nopriv_save_wcq_settings', [ self::class, 'save_wcq_settings' ] );
		add_action( 'wp_ajax_save_wcq_settings', [ self::class, 'save_wcq_settings' ] );
	}

	/**
	 * Registers the settings menu for the plugin in the WordPress admin area.
	 *
	 * This method adds a submenu page under the WooCommerce menu where plugin settings
	 * can be managed. It uses `add_submenu_page` to create the menu item.
	 *
	 * @return void
	 */
	public static function register_settings_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'CQW', 'dm-cart-quotation' ),
			__( 'CQW', 'dm-cart-quotation' ),
			'manage_options',
			'wcq-settings',
			[ self::class, 'display_settings_page' ]
		);
	}

	/**
	 * Registers the settings for the plugin.
	 *
	 * This method registers the settings group and option for the plugin using
	 * the `register_setting` function.
	 *
	 * @return void
	 */
	public static function register_settings() {
		register_setting( 'wcq_settings', 'wcq_settings' );
	}

	/**
	 * Displays the settings page for the plugin.
	 *
	 * This method outputs the HTML for the plugin settings page. It retrieves current
	 * settings, renders the form fields, and includes a nonce field for security.
	 *
	 * @return void
	 */
	public static function display_settings_page() {
		// Retrieve the settings from the database or use the default settings if none are found.
		$settings = Utilities::wcq_get_settings();

		if ( ! $settings ) {
			$settings = self::default_wcq_settings( [] );
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Cart Quotation Generator Settings', 'dm-cart-quotation' ); ?></h1>

			<form id="wcq-settings-form">
				<?php wp_nonce_field( 'wcq_save_settings_nonce_action', 'wcq_save_settings_nonce' ); ?>
				<table class="form-table">
					<?php self::render_button_fields( 'button_quote', $settings['button_quote'] ); ?>
					<?php self::render_button_fields( 'button_empty_cart', $settings['button_empty_cart'] ); ?>
					<?php self::render_notice_fields( 'notices', $settings['notices'] ); ?>
					<?php self::render_messages_fields( 'messages', $settings['messages'] ); ?>
				</table>
				<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'dm-cart-quotation' ); ?>">
			</form>
			<?php Utilities::wcq_reploader(); ?>
		</div>
		<?php
	}

	/**
	 * Renders the fields for button settings on the settings page.
	 *
	 * This method generates the HTML for the settings fields related to button customization.
	 * It includes fields for background color, text color, hover colors, padding, and position.
	 *
	 * @param string $button_name The name of the button to render settings for.
	 * @param array  $button_settings The current settings for the button.
	 *
	 * @return void
	 */
	public static function render_button_fields( $button_name, $button_settings ) {

		?>
		<tr>
			<th colspan="2">
				<h2><?php echo esc_html( ucfirst( str_replace( '_', ' ', $button_name ) ) ); ?></h2>
			</th>
		</tr>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $button_name ); ?>_text"><?php esc_html_e( 'Button Text', 'dm-cart-quotation' ); ?></label></th>
			<td><input type="text" id="<?php echo esc_attr( $button_name ); ?>_text" name="<?php echo esc_attr( $button_name ); ?>[text]" value="<?php echo esc_attr( $button_settings['text'] ); ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $button_name ); ?>_background_color"><?php esc_html_e( 'Background Color', 'dm-cart-quotation' ); ?></label></th>
			<td><input type="text" id="<?php echo esc_attr( $button_name ); ?>_background_color" name="<?php echo esc_attr( $button_name ); ?>[background_color]" value="<?php echo esc_attr( $button_settings['background_color'] ); ?>" class="regular-text wcq-color-picker" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $button_name ); ?>_text_color"><?php esc_html_e( 'Text Color', 'dm-cart-quotation' ); ?></label></th>
			<td><input type="text" id="<?php echo esc_attr( $button_name ); ?>_text_color" name="<?php echo esc_attr( $button_name ); ?>[text_color]" value="<?php echo esc_attr( $button_settings['text_color'] ); ?>" class="regular-text wcq-color-picker" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $button_name ); ?>_hover_background"><?php esc_html_e( 'Hover Background Color', 'dm-cart-quotation' ); ?></label></th>
			<td><input type="text" id="<?php echo esc_attr( $button_name ); ?>_hover_background" name="<?php echo esc_attr( $button_name ); ?>[hover_background]" value="<?php echo esc_attr( $button_settings['hover_background'] ); ?>" class="regular-text wcq-color-picker" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $button_name ); ?>_hover_text_color"><?php esc_html_e( 'Hover Text Color', 'dm-cart-quotation' ); ?></label></th>
			<td><input type="text" id="<?php echo esc_attr( $button_name ); ?>_hover_text_color" name="<?php echo esc_attr( $button_name ); ?>[hover_text_color]" value="<?php echo esc_attr( $button_settings['hover_text_color'] ); ?>" class="regular-text wcq-color-picker" /></td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Padding', 'dm-cart-quotation' ); ?></th>
			<td>
				<input id="<?php echo esc_attr( $button_name ) . '_padding_top'; ?>" type="number" name="<?php echo esc_attr( $button_name ); ?>[padding][top]" value="<?php echo esc_attr( $button_settings['padding']['top'] ); ?>" placeholder="Top" />
				<input id="<?php echo esc_attr( $button_name ) . '_padding_right'; ?>" type="number" name="<?php echo esc_attr( $button_name ); ?>[padding][right]" value="<?php echo esc_attr( $button_settings['padding']['right'] ); ?>" placeholder="Right" />
				<input id="<?php echo esc_attr( $button_name ) . '_padding_bottom'; ?>" type="number" name="<?php echo esc_attr( $button_name ); ?>[padding][bottom]" value="<?php echo esc_attr( $button_settings['padding']['bottom'] ); ?>" placeholder="Bottom" />
				<input id="<?php echo esc_attr( $button_name ) . '_padding_left'; ?>" type="number" name="<?php echo esc_attr( $button_name ); ?>[padding][left]" value="<?php echo esc_attr( $button_settings['padding']['left'] ); ?>" placeholder="Left" />
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Position', 'dm-cart-quotation' ); ?></th>
			<td>
				<select id="<?php echo esc_attr( $button_name ); ?>_position" name="<?php echo esc_attr( $button_name ); ?>_position">
					<?php foreach ( self::default_position_button() as $key => $value ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $button_settings['position'] ); ?>><?php echo esc_html( ucfirst( str_replace( '_', ' ', $key ) ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Position Percentage', 'dm-cart-quotation' ); ?></th>
			<td>
				<input id="<?php echo esc_attr( $button_name . '_position_one_per' ); ?>"
					type="number"
					name="<?php echo esc_attr( $button_name ); ?>[position_one_per]"
					value="<?php echo esc_attr( $button_settings['position_one_per'] ); ?>"
					placeholder="10"
					min="0" max="100" /> %

				<input id="<?php echo esc_attr( $button_name . '_position_two_per' ); ?>"
					type="number"
					name="<?php echo esc_attr( $button_name ); ?>[position_two_per]"
					value="<?php echo esc_attr( $button_settings['position_two_per'] ); ?>"
					placeholder="10"
					min="0" max="100" /> %
			</td>
		</tr>

		<?php
	}


	/**
	 * Renders the fields for notice settings on the settings page.
	 *
	 * This method generates the HTML for the settings fields related to success and error notices customization.
	 * It includes fields for background color, text color, and border radius.
	 *
	 * @param string $notice_name The name of the notice settings group.
	 * @param array  $notice_settings The current settings for the notices.
	 *
	 * @return void
	 */
	public static function render_notice_fields( $notice_name, $notice_settings ) {
		?>
		<tr>
			<th colspan="2">
				<h2><?php esc_html_e( 'Notice Settings', 'dm-cart-quotation' ); ?></h2>
			</th>
		</tr>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $notice_name ); ?>_border_radius"><?php esc_html_e( 'Border Radius', 'dm-cart-quotation' ); ?></label></th>
			<td><input type="number" id="<?php echo esc_attr( $notice_name ); ?>_border_radius" name="<?php echo esc_attr( $notice_name ); ?>[border_radius]" value="<?php echo esc_attr( $notice_settings['border_radius'] ); ?>" class="regular-text" /> px</td>
		</tr>
		<tr>
			<th colspan="2">
				<h3><?php esc_html_e( 'Success Notice', 'dm-cart-quotation' ); ?></h3>
			</th>
		</tr>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $notice_name ); ?>_success_text"><?php esc_html_e( 'Success Text', 'dm-cart-quotation' ); ?></label></th>
			<td><input type="text" id="<?php echo esc_attr( $notice_name ); ?>_success_text" name="<?php echo esc_attr( $notice_name ); ?>[success][text]" value="<?php echo esc_attr( $notice_settings['success']['text'] ); ?>" class="regular-text" /></td>
		</tr>

		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $notice_name ); ?>_success_background"><?php esc_html_e( 'Background Color', 'dm-cart-quotation' ); ?></label></th>
			<td><input type="text" id="<?php echo esc_attr( $notice_name ); ?>_success_background" name="<?php echo esc_attr( $notice_name ); ?>[success][background_color]" value="<?php echo esc_attr( $notice_settings['success']['background_color'] ); ?>" class="regular-text wcq-color-picker" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $notice_name ); ?>_success_text_color"><?php esc_html_e( 'Text Color', 'dm-cart-quotation' ); ?></label></th>
			<td><input type="text" id="<?php echo esc_attr( $notice_name ); ?>_success_text_color" name="<?php echo esc_attr( $notice_name ); ?>[success][text_color]" value="<?php echo esc_attr( $notice_settings['success']['text_color'] ); ?>" class="regular-text wcq-color-picker" /></td>
		</tr>


		<tr>
			<th colspan="2">
				<h3><?php esc_html_e( 'Error Notice', 'dm-cart-quotation' ); ?></h3>
			</th>
		</tr>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $notice_name ); ?>_error_text"><?php esc_html_e( 'Error Text', 'dm-cart-quotation' ); ?></label></th>
			<td><input type="text" id="<?php echo esc_attr( $notice_name ); ?>_error_text" name="<?php echo esc_attr( $notice_name ); ?>[error][text]" value="<?php echo esc_attr( $notice_settings['error']['text'] ); ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $notice_name ); ?>_error_background"><?php esc_html_e( 'Background Color', 'dm-cart-quotation' ); ?></label></th>
			<td><input type="text" id="<?php echo esc_attr( $notice_name ); ?>_error_background" name="<?php echo esc_attr( $notice_name ); ?>[error][background_color]" value="<?php echo esc_attr( $notice_settings['error']['background_color'] ); ?>" class="regular-text wcq-color-picker" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $notice_name ); ?>_error_text_color"><?php esc_html_e( 'Text Color', 'dm-cart-quotation' ); ?></label></th>
			<td><input type="text" id="<?php echo esc_attr( $notice_name ); ?>_error_text_color" name="<?php echo esc_attr( $notice_name ); ?>[error][text_color]" value="<?php echo esc_attr( $notice_settings['error']['text_color'] ); ?>" class="regular-text wcq-color-picker" /></td>
		</tr>

		<?php
	}

	/**
	 * Renders the fields for notice settings on the settings page.
	 *
	 * This method generates the HTML for the settings fields related to success and error notices customization.
	 * It includes fields for background color, text color, and border radius.
	 *
	 * @param string $notice_name The name of the notice settings group.
	 * @param array  $notice_settings The current settings for the notices.
	 *
	 * @return void
	 */
	public static function render_messages_fields( $message_name, $notice_settings ) {
		?>
		<tr>
			<th colspan="2">
				<h2><?php esc_html_e( 'Messages Settings', 'dm-cart-quotation' ); ?></h2>
			</th>
		</tr>

		<tr>
			<th colspan="2">
				<h3><?php esc_html_e( 'Success Messages', 'dm-cart-quotation' ); ?></h3>
			</th>
		</tr>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $message_name ); ?>_success_cart_quote"><?php esc_html_e( 'Cart Quote Message', 'dm-cart-quotation' ); ?></label></th>
			<td><input type="text" id="<?php echo esc_attr( $message_name ); ?>_success_cart_quote" name="<?php echo esc_attr( $message_name ); ?>[success][cart_quote]" value="<?php echo esc_attr( $notice_settings['success']['cart_quote'] ); ?>" class="regular-text" /></td>
		</tr>

		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $message_name ); ?>_success_cart_empty"><?php esc_html_e( 'Cart Empty Message', 'dm-cart-quotation' ); ?></label></th>
			<td><input type="text" id="<?php echo esc_attr( $message_name ); ?>_success_cart_empty" name="<?php echo esc_attr( $message_name ); ?>[success][cart_empty]" value="<?php echo esc_attr( $notice_settings['success']['cart_empty'] ); ?>" class="regular-text" /></td>
		</tr>

		<tr>
			<th colspan="2">
				<h3><?php esc_html_e( 'Error Messages', 'dm-cart-quotation' ); ?></h3>
			</th>
		</tr>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $message_name ); ?>_error_cart_quote"><?php esc_html_e( 'Cart Quote Message', 'dm-cart-quotation' ); ?></label></th>
			<td><input type="text" id="<?php echo esc_attr( $message_name ); ?>_error_cart_quote" name="<?php echo esc_attr( $message_name ); ?>[error][cart_quote]" value="<?php echo esc_attr( $notice_settings['error']['cart_quote'] ); ?>" class="regular-text" /></td>
		</tr>

		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $message_name ); ?>_error_cart_empty"><?php esc_html_e( 'Cart Empty Message', 'dm-cart-quotation' ); ?></label></th>
			<td><input type="text" id="<?php echo esc_attr( $message_name ); ?>_error_cart_empty" name="<?php echo esc_attr( $message_name ); ?>[error][cart_empty]" value="<?php echo esc_attr( $notice_settings['error']['cart_empty'] ); ?>" class="regular-text" /></td>
		</tr>

		<?php
	}

	public static function sanitize_text_field( $value ) {
		return sanitize_text_field( wp_unslash( $value ) );
	}


	/**
	 * Handles AJAX requests to save plugin settings.
	 *
	 * This method verifies the nonce for security, checks user capabilities, and
	 * then updates the plugin settings in the database. It responds with a success
	 * or error message based on the outcome of the operation.
	 *
	 * @return void
	 */
	public static function save_wcq_settings() {
		// Verify nonce
		if ( ! isset( $_POST['wcq_save_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wcq_save_settings_nonce'] ) ), 'wcq_save_settings_nonce_action' ) ) {
			wp_send_json_error( __( 'Nonce verification failed', 'dm-cart-quotation' ) );
			return;
		}

		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You are not authorized to perform this action', 'dm-cart-quotation' ) );
			return;
		}

		// Save settings
		$settings = [
			'button_quote'      => self::save_button_quote( $_REQUEST ),
			'button_empty_cart' => self::save_empty_cart_button( $_REQUEST ),
			'notices'           => self::save_notices( $_REQUEST ),
			'messages'          => self::save_messages( $_REQUEST ),
		];

		update_option( 'wcq_settings', $settings );

		wp_send_json_success( __( 'Settings saved successfully', 'dm-cart-quotation' ) );
	}

	public static function save_button_quote( $form_data ) {
		$default = self::default_settings();

		$settings = [];

		if ( isset( $form_data['button_quote'] ) && is_array( $form_data['button_quote'] ) ) {
			$settings = [
				'padding'          => isset( $form_data['button_quote']['padding'] ) && ! empty( $form_data['button_quote']['padding'] ) ? array_map( 'absint', $form_data['button_quote']['padding'] ) : $default['button_quote']['padding'],
				'background_color' => isset( $form_data['button_quote']['background_color'] ) ? sanitize_hex_color( wp_unslash( $form_data['button_quote']['background_color'] ) ) : $default['button_quote']['background_color'],
				'text_color'       => isset( $form_data['button_quote']['text_color'] ) ? sanitize_hex_color( wp_unslash( $form_data['button_quote']['text_color'] ) ) : $default['button_quote']['text_color'],
				'hover_background' => isset( $form_data['button_quote']['hover_background'] ) ? sanitize_hex_color( wp_unslash( $form_data['button_quote']['hover_background'] ) ) : $default['button_quote']['hover_background'],
				'hover_text_color' => isset( $form_data['button_quote']['hover_text_color'] ) ? sanitize_hex_color( wp_unslash( $form_data['button_quote']['hover_text_color'] ) ) : $default['button_quote']['hover_text_color'],
				'position'         => isset( $form_data['button_quote']['position'] ) ? sanitize_text_field( wp_unslash( $form_data['button_quote']['position'] ) ) : $default['button_quote']['position'],
				'position_one_per' => isset( $form_data['button_quote']['position_one_per'] ) ? max( 0, min( 100, absint( wp_unslash( $form_data['button_quote']['position_one_per'] ) ) ) ) : $default['button_quote']['position_one_per'],
				'position_two_per' => isset( $form_data['button_quote']['position_two_per'] ) ? max( 0, min( 100, absint( wp_unslash( $form_data['button_quote']['position_two_per'] ) ) ) ) : $default['button_quote']['position_two_per'],
				'text'             => isset( $form_data['button_quote']['text'] ) ? sanitize_text_field( wp_unslash( $form_data['button_quote']['text'] ) ) : $default['button_quote']['text'],
			];
		}

		return $settings;
	}


	public static function save_empty_cart_button( $form_data ) {
		$default = self::default_settings();

		$settings = [];

		if ( isset( $form_data['button_empty_cart'] ) && is_array( $form_data['button_empty_cart'] ) ) {
			$settings = [
				'padding'          => isset( $form_data['button_empty_cart']['padding'] ) && ! empty( $form_data['button_empty_cart']['padding'] ) ? array_map( 'absint', $form_data['button_empty_cart']['padding'] ) : $default['button_empty_cart']['padding'],
				'background_color' => isset( $form_data['button_empty_cart']['background_color'] ) ? sanitize_hex_color( wp_unslash( $form_data['button_empty_cart']['background_color'] ) ) : $default['button_empty_cart']['background_color'],
				'text_color'       => isset( $form_data['button_empty_cart']['text_color'] ) ? sanitize_hex_color( wp_unslash( $form_data['button_empty_cart']['text_color'] ) ) : $default['button_empty_cart']['text_color'],
				'hover_background' => isset( $form_data['button_empty_cart']['hover_background'] ) ? sanitize_hex_color( wp_unslash( $form_data['button_empty_cart']['hover_background'] ) ) : $default['button_empty_cart']['hover_background'],
				'hover_text_color' => isset( $form_data['button_empty_cart']['hover_text_color'] ) ? sanitize_hex_color( wp_unslash( $form_data['button_empty_cart']['hover_text_color'] ) ) : $default['button_empty_cart']['hover_text_color'],
				'position'         => isset( $form_data['button_empty_cart']['position'] ) ? sanitize_text_field( wp_unslash( $form_data['button_empty_cart']['position'] ) ) : $default['button_empty_cart']['position'],
				'position_one_per' => isset( $form_data['button_empty_cart']['position_one_per'] ) ? max( 0, min( 100, absint( wp_unslash( $form_data['button_empty_cart']['position_one_per'] ) ) ) ) : $default['button_empty_cart']['position_one_per'],
				'position_two_per' => isset( $form_data['button_empty_cart']['position_two_per'] ) ? max( 0, min( 100, absint( wp_unslash( $form_data['button_empty_cart']['position_two_per'] ) ) ) ) : $default['button_empty_cart']['position_two_per'],
				'text'             => isset( $form_data['button_empty_cart']['text'] ) ? sanitize_text_field( wp_unslash( $form_data['button_empty_cart']['text'] ) ) : $default['button_empty_cart']['text'],
			];
		}

		return $settings;
	}

	public static function save_notices( $form_data ) {
		$default = self::default_settings();

		$settings = [];

		if ( isset( $form_data['notices'] ) && is_array( $form_data['notices'] ) ) {
			$settings = [
				'border_radius' => isset( $form_data['notices']['border_radius'] ) ? sanitize_text_field( wp_unslash( $form_data['notices']['border_radius'] ) ) : $default['notices']['border_radius'],
				'success'       => [
					'text'             => isset( $form_data['notices']['success']['text'] ) ? sanitize_text_field( wp_unslash( $form_data['notices']['success']['text'] ) ) : $default['notices']['success']['text'],
					'background_color' => isset( $form_data['notices']['success']['background_color'] ) ? sanitize_hex_color( wp_unslash( $form_data['notices']['success']['background_color'] ) ) : $default['notices']['success']['background_color'],
					'text_color'       => isset( $form_data['notices']['success']['text_color'] ) ? sanitize_hex_color( wp_unslash( $form_data['notices']['success']['text_color'] ) ) : $default['notices']['success']['text_color'],
				],
				'error'         => [
					'text'             => isset( $form_data['notices']['error']['text'] ) ? sanitize_text_field( wp_unslash( $form_data['notices']['error']['text'] ) ) : $default['notices']['error']['text'],
					'background_color' => isset( $form_data['notices']['error']['background_color'] ) ? sanitize_hex_color( wp_unslash( $form_data['notices']['error']['background_color'] ) ) : $default['notices']['error']['background_color'],
					'text_color'       => isset( $form_data['notices']['error']['text_color'] ) ? sanitize_hex_color( wp_unslash( $form_data['notices']['error']['text_color'] ) ) : $default['notices']['error']['text_color'],
				],
			];
		}

		return $settings;
	}

	public static function save_messages( $form_data ) {
		$default = self::default_settings();

		$settings = [];

		if ( isset( $form_data['messages'] ) && is_array( $form_data['messages'] ) ) {
			$settings = [
				'success' => [
					'cart_quote' => isset( $form_data['messages']['success']['cart_quote'] ) ? sanitize_text_field( wp_unslash( $form_data['messages']['success']['cart_quote'] ) ) : $default['messages']['success']['cart_quote'],
					'cart_empty' => isset( $form_data['messages']['success']['cart_empty'] ) ? sanitize_text_field( wp_unslash( $form_data['messages']['success']['cart_empty'] ) ) : $default['messages']['success']['cart_empty'],
				],
				'error'   => [
					'cart_quote' => isset( $form_data['messages']['error']['cart_quote'] ) ? sanitize_text_field( wp_unslash( $form_data['messages']['error']['cart_quote'] ) ) : $default['messages']['error']['cart_quote'],
					'cart_empty' => isset( $form_data['messages']['error']['cart_empty'] ) ? sanitize_text_field( wp_unslash( $form_data['messages']['error']['cart_empty'] ) ) : $default['messages']['error']['cart_empty'],
				],
			];
		}

		return $settings;
	}

	/**
	 * Provides default settings for the plugin.
	 *
	 * This method returns an array of default settings for the buttons used in the plugin.
	 * It is used to populate the settings form with default values if no settings are found.
	 *
	 * @return array Default settings for the buttons.
	 */
	public static function default_settings() {
		return [
			'button_quote'      => [
				'padding'          => [
					'top'    => 10,
					'right'  => 10,
					'bottom' => 10,
					'left'   => 10,
				],
				'background_color' => '#000',
				'text_color'       => '#fff',
				'hover_background' => '#000',
				'hover_text_color' => '#fff',
				'position'         => 'top_right',
				'position_one_per' => 10,
				'position_two_per' => 5,
				'text'             => __( 'Send Quote', 'dm-cart-quotation' ),
			],
			'button_empty_cart' => [
				'padding'          => [
					'top'    => 10,
					'right'  => 10,
					'bottom' => 10,
					'left'   => 10,
				],
				'background_color' => '#000',
				'text_color'       => '#fff',
				'hover_background' => '#000',
				'hover_text_color' => '#fff',
				'position'         => 'bottom_right',
				'position_one_per' => 10,
				'position_two_per' => 5,
				'text'             => __( 'Empty Cart', 'dm-cart-quotation' ),
			],
			'notices'           => [
				'success'       => [
					'background_color' => '#008000',
					'text_color'       => '#fff',
					'text'             => __( 'Quote generated & link copied to clipboard.', 'dm-cart-quotation' ),
				],
				'error'         => [
					'background_color' => '#d20a2e',
					'text_color'       => '#fff',
					'text'             => __( 'Something went wrong. Please try again.', 'dm-cart-quotation' ),
				],
				'border_radius' => 5,
			],
			'messages'          => [
				'success' => [
					'cart_quote' => __( 'Quote generated & link copied to clipboard.', 'dm-cart-quotation' ),
					'cart_empty' => __( 'Cart items removed. You are being redirected.', 'dm-cart-quotation' ),
				],
				'error'   => [
					'cart_quote' => __( 'Something went wrong. Please try again.', 'dm-cart-quotation' ),
					'cart_empty' => __( 'Something went wrong. Please try again.', 'dm-cart-quotation' ),

				],
			],
		];
	}

	/**
	 * Merges default settings with provided settings.
	 *
	 * This method combines default settings with any existing settings provided,
	 * ensuring that all necessary settings are present and properly formatted.
	 *
	 * @param array $settings An array of settings to merge with default settings.
	 *
	 * @return array Merged settings with default values applied.
	 */
	public static function default_wcq_settings( $settings ) {

		return wp_parse_args( self::default_settings() );
	}

	/**
	 * Defines the default positions for the buttons.
	 *
	 * This method returns an array of possible positions where buttons can be placed
	 * on the screen. It is used to populate the position dropdown in the settings form.
	 *
	 * @return array Available positions for buttons.
	 */
	public static function default_position_button() {
		return [
			'top_right'     => [
				'top',
				'right',
			],
			'top_center'    => [
				'top',
				'center',
			],
			'top_left'      => [
				'top',
				'left',
			],
			'bottom_left'   => [
				'bottom',
				'left',
			],
			'bottom_center' => [
				'bottom',
				'center',
			],
			'bottom_right'  => [
				'bottom',
				'right',
			],
		];
	}
}
