<?php

/**
 * Theme Options v1.1.0
 * Adjust theme settings from the admin dashboard.
 * Find and replace `YourTheme` with your own namepspacing.
 *
 * Created by Michael Fields.
 * https://gist.github.com/mfields/4678999
 *
 * Forked by Chris Ferdinandi
 * http://gomakethings.com
 *
 * Free to use under the MIT License.
 * http://gomakethings.com/mit/
 */


	/**
	 * Theme Options Fields
	 * Each option field requires its own uniquely named function. Select options and radio buttons also require an additional uniquely named function with an array of option choices.
	 */

	function merchant_settings_field_checkout_url() {
		$options = merchant_get_theme_options();
		?>
		<input type="url" name="merchant_theme_options[checkout_url]" class="large-text" id="checkout_url" value="<?php echo esc_url( $options['checkout_url'] ); ?>" />
		<label class="description" for="checkout_url"><?php _e( 'URL for the checkout page', 'merchant' ); ?></label>
		<?php
	}

	function merchant_settings_field_success_url() {
		$options = merchant_get_theme_options();
		?>
		<input type="url" name="merchant_theme_options[success_url]" class="large-text" id="success_url" value="<?php echo esc_url( $options['success_url'] ); ?>" />
		<label class="description" for="success_url"><?php _e( 'URL for successful purchases', 'merchant' ); ?></label>
		<?php
	}

	function merchant_settings_field_paypal_icon() {
		$options = merchant_get_theme_options();
		?>
		<textarea class="large-text" type="text" name="merchant_theme_options[paypal_icon]" id="paypal_icon" cols="50" rows="10" /><?php echo stripslashes( esc_textarea( $options['paypal_icon'] ) ); ?></textarea>
		<label class="description" for="checkout_url"><?php _e( 'PayPal icon to include in "Pay with PayPal" button', 'merchant' ); ?></label>
		<?php
	}

	function merchant_settings_field_promo_codes() {
		$options = merchant_get_theme_options();
		?>
		<label>
			<input type="checkbox" name="merchant_theme_options[promo_codes]" value="on" <?php checked( 'on', $options['promo_codes'] ) ?>>
			<?php _e( 'Disable promo codes', 'merchant' ); ?>
		</label>
		<?php
	}

	function merchant_settings_field_alert_classes() {
		$options = merchant_get_theme_options();
		?>
		<div>
			<input type="text" name="merchant_theme_options[alert_success_class]" id="alert_success_class" value="<?php echo esc_attr( $options['alert_success_class'] ); ?>" />
			<label class="description" for="alert_success_class"><?php _e( 'Class(es) for messages when tasks and processes are successfully completed', 'merchant' ); ?></label>
		</div>
		<br>
		<div>
			<input type="text" name="merchant_theme_options[alert_error_class]" id="alert_error_class" value="<?php echo esc_attr( $options['alert_error_class'] ); ?>" />
			<label class="description" for="alert_error_class"><?php _e( 'Class(es) for messages when tasks and processes fail', 'merchant' ); ?></label>
		</div>
		<?php
	}

	function merchant_settings_field_paypal_error() {
		$options = merchant_get_theme_options();
		?>
		<input type="text" name="merchant_theme_options[paypal_error]" class="large-text" id="paypal_error" value="<?php echo stripslashes( esc_attr( $options['paypal_error'] ) ); ?>" />
		<label class="description" for="paypal_error"><?php _e( 'Error when PayPal authorization fails', 'merchant' ); ?></label>
		<?php
	}

	function merchant_settings_field_no_item_error() {
		$options = merchant_get_theme_options();
		?>
		<input type="text" name="merchant_theme_options[no_item]" class="large-text" id="no_item" value="<?php echo stripslashes( esc_attr( $options['no_item'] ) ); ?>" />
		<label class="description" for="no_item"><?php _e( 'Error when no item is chosen', 'merchant' ); ?></label>
		<?php
	}

	function merchant_settings_field_item_discontinued_error() {
		$options = merchant_get_theme_options();
		?>
		<input type="text" name="merchant_theme_options[item_discontinued]" class="large-text" id="item_discontinued" value="<?php echo stripslashes( esc_attr( $options['item_discontinued'] ) ); ?>" />
		<label class="description" for="item_discontinued"><?php _e( 'Error when item is no longer available at set price', 'merchant' ); ?></label>
		<?php
	}

	function merchant_settings_field_item_discount_failure_error() {
		$options = merchant_get_theme_options();
		?>
		<input type="text" name="merchant_theme_options[discount_failure]" class="large-text" id="discount_failure" value="<?php echo stripslashes( esc_attr( $options['discount_failure'] ) ); ?>" />
		<label class="description" for="discount_failure"><?php _e( 'Error when promo code fails', 'merchant' ); ?></label>
		<?php
	}

	function merchant_settings_field_item_discount_invalid_error() {
		$options = merchant_get_theme_options();
		?>
		<input type="text" name="merchant_theme_options[discount_invalid]" class="large-text" id="discount_invalid" value="<?php echo stripslashes( esc_attr( $options['discount_invalid'] ) ); ?>" />
		<label class="description" for="discount_invalid"><?php _e( 'Error when promo code is used on an invalid product', 'merchant' ); ?></label>
		<?php
	}

	function merchant_settings_field_item_discount_success_error() {
		$options = merchant_get_theme_options();
		?>
		<input type="text" name="merchant_theme_options[discount_success]" class="large-text" id="discount_success" value="<?php echo stripslashes( esc_attr( $options['discount_success'] ) ); ?>" />
		<label class="description" for="discount_success"><?php _e( 'Message when promo code successfully entered', 'merchant' ); ?></label>
		<?php
	}

	function merchant_settings_field_item_no_email_error() {
		$options = merchant_get_theme_options();
		?>
		<input type="text" name="merchant_theme_options[no_email_error]" class="large-text" id="no_email_error" value="<?php echo stripslashes( esc_attr( $options['no_email_error'] ) ); ?>" />
		<label class="description" for="no_email_error"><?php _e( 'Message when item is free and no email is provided', 'merchant' ); ?></label>
		<?php
	}

	function merchant_settings_field_get_mailchimp_data( $group = null ) {

		$options = merchant_get_theme_options();

		if ( empty( $options['mailchimp_api_key'] ) || empty( $options['mailchimp_list_id'] ) ) return;

		// Create API call
		$shards = explode( '-', $options['mailchimp_api_key'] );
		$url = 'https://' . $shards[1] . '.api.mailchimp.com/3.0/lists/' . $options['mailchimp_list_id'] . '/interest-categories' . ( empty( $group ) ? '' : '/' . $group . '/interests' );
		$params = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'mailchimp' . ':' . $options['mailchimp_api_key'] )
			),
		);

		// Get data from  MailChimp
		$request = wp_remote_get( $url, $params );
		$response = wp_remote_retrieve_body( $request );
		$data = json_decode( $response, true );

		// If request fails, bail
		if ( empty( $group ) ) {
			if ( !array_key_exists( 'categories', $data ) || !is_array( $data['categories'] ) || empty( $data['categories'] ) ) return array();
		} else {
			if ( !array_key_exists( 'interests', $data ) || !is_array( $data['interests'] ) || empty( $data['interests'] ) ) return array();
		}

		return $data;

	}

	function merchant_settings_field_mailchimp_disable() {
		$options = merchant_get_theme_options();
		?>
		<label>
			<input type="checkbox" name="merchant_theme_options[mailchimp_disable]" value="on" <?php checked( 'on', $options['mailchimp_disable'] ) ?>>
			<?php _e( 'Disable MailChimp integration', 'merchant' ); ?>
		</label>
		<?php
	}

	function merchant_settings_field_mailchimp_api_key() {
		$options = merchant_get_theme_options();
		?>
		<input type="text" name="merchant_theme_options[mailchimp_api_key]" class="regular-text" id="mailchimp_api_key" value="<?php echo esc_attr( $options['mailchimp_api_key'] ); ?>" />
		<label class="description" for="mailchimp_api_key"><?php _e( 'MailChimp API key', 'merchant' ); ?></label>
		<?php
	}

	function merchant_settings_field_mailchimp_list_id() {
		$options = merchant_get_theme_options();
		?>
		<input type="text" name="merchant_theme_options[mailchimp_list_id]" class="regular-text" id="mailchimp_list_id" value="<?php echo esc_attr( $options['mailchimp_list_id'] ); ?>" />
		<label class="description" for="mailchimp_list_id"><?php _e( 'MailChimp list ID', 'merchant' ); ?></label>
		<?php
	}

	function merchant_settings_field_mailchimp_category_id() {
		$options = merchant_get_theme_options();
		$mailchimp = merchant_settings_field_get_mailchimp_data();
		?>
		<select name="merchant_theme_options[mailchimp_category_id]" id="mailchimp_category_id">
			<option value="" <?php selected( '', $options['mailchimp_category_id'] ); ?>>None</option>
			<?php foreach ( $mailchimp['categories'] as $key => $category ) : ?>
				<option value="<?php echo esc_attr( $category['id'] ); ?>" <?php selected( $category['id'], $options['mailchimp_category_id'] ); ?>><?php echo esc_html( $category['title'] ); ?></option>
			<?php endforeach; ?>
		</select>
		<label class="description" for="sample_theme_options[selectinput]"><?php _e( 'MailChimp category ID', 'merchant' ); ?></label>
		<?php
	}

	function merchant_settings_field_mailchimp_group_id() {
		$options = merchant_get_theme_options();
		$mailchimp = merchant_settings_field_get_mailchimp_data( $options['mailchimp_category_id'] );
		?>
		<select name="merchant_theme_options[mailchimp_group_id]" id="mailchimp_group_id">
			<option value="" <?php selected( '', $options['mailchimp_group_id'] ); ?>>None</option>
			<?php foreach ( $mailchimp['interests'] as $key => $interest ) : ?>
				<option value="<?php echo esc_attr( $interest['id'] ); ?>" <?php selected( $interest['id'], $options['mailchimp_group_id'] ); ?>><?php echo esc_html( $interest['name'] ); ?></option>
			<?php endforeach; ?>
		</select>
		<label class="description" for="sample_theme_options[selectinput]"><?php _e( 'MailChimp group ID', 'merchant' ); ?></label>
		<?php
	}



	/**
	 * Theme Option Defaults & Sanitization
	 * Each option field requires a default value under merchant_get_theme_options(), and an if statement under merchant_theme_options_validate();
	 */

	// Get the current options from the database.
	// If none are specified, use these defaults.
	function merchant_get_theme_options() {
		$saved = (array) get_option( 'merchant_theme_options' );
		$defaults = array(

			// Basics
			'checkout_url' => site_url() . '/checkout/',
			'success_url' => site_url() . '/success/',
			'paypal_icon' => '',
			'promo_codes' => 'off',

			// Errors
			'alert_success_class' => '',
			'alert_error_class' => '',
			'paypal_error' => 'Unable to authorize payment through PayPal. Please try again.',
			'no_item' => 'Please select an item to purchase.',
			'item_discontinued' => 'Sorry, this item is no longer available.',
			'discount_failure' => 'Sorry, this discount code is no longer valid.',
			'discount_invalid' => 'Sorry, this discount code cannot be used on this product.',
			'discount_success' => 'Your discount code was applied to this purchase.',
			'no_email_error' => 'Please provide a valid email address.',

			// MailChimp
			'mailchimp_disable' => 'off',
			'mailchimp_api_key' => '',
			'mailchimp_list_id' => '',
			'mailchimp_category_id' => '',
			'mailchimp_group_id' => '',

		);

		$defaults = apply_filters( 'merchant_default_theme_options', $defaults );

		$options = wp_parse_args( $saved, $defaults );
		$options = array_intersect_key( $options, $defaults );

		return $options;
	}

	// Sanitize and validate updated theme options
	function merchant_theme_options_validate( $input ) {
		$output = array();

		// Basics
		if ( isset( $input['checkout_url'] ) && ! empty( $input['checkout_url'] ) )
			$output['checkout_url'] = wp_filter_nohtml_kses( $input['checkout_url'] );

		if ( isset( $input['success_url'] ) && ! empty( $input['success_url'] ) )
			$output['success_url'] = wp_filter_nohtml_kses( $input['success_url'] );

		if ( isset( $input['paypal_icon'] ) && ! empty( $input['paypal_icon'] ) )
			$output['paypal_icon'] = wp_filter_post_kses( $input['paypal_icon'] );

		if ( isset( $input['promo_codes'] ) )
			$output['promo_codes'] = 'on';

		// Errors
		if ( isset( $input['alert_success_class'] ) && ! empty( $input['alert_success_class'] ) )
			$output['alert_success_class'] = wp_filter_nohtml_kses( $input['alert_success_class'] );

		if ( isset( $input['alert_error_class'] ) && ! empty( $input['alert_error_class'] ) )
			$output['alert_error_class'] = wp_filter_nohtml_kses( $input['alert_error_class'] );

		if ( isset( $input['paypal_error'] ) && ! empty( $input['paypal_error'] ) )
			$output['paypal_error'] = wp_filter_nohtml_kses( $input['paypal_error'] );

		if ( isset( $input['no_item'] ) && ! empty( $input['no_item'] ) )
			$output['no_item'] = wp_filter_nohtml_kses( $input['no_item'] );

		if ( isset( $input['item_discontinued'] ) && ! empty( $input['item_discontinued'] ) )
			$output['item_discontinued'] = wp_filter_nohtml_kses( $input['item_discontinued'] );

		if ( isset( $input['discount_failure'] ) && ! empty( $input['discount_failure'] ) )
			$output['discount_failure'] = wp_filter_nohtml_kses( $input['discount_failure'] );

		if ( isset( $input['discount_invalid'] ) && ! empty( $input['discount_invalid'] ) )
			$output['discount_invalid'] = wp_filter_nohtml_kses( $input['discount_invalid'] );

		if ( isset( $input['discount_success'] ) && ! empty( $input['discount_success'] ) )
			$output['discount_success'] = wp_filter_nohtml_kses( $input['discount_success'] );

		if ( isset( $input['no_email_error'] ) && ! empty( $input['no_email_error'] ) )
			$output['no_email_error'] = wp_filter_nohtml_kses( $input['no_email_error'] );

		// MailChimp
		if ( isset( $input['mailchimp_disable'] ) )
			$output['mailchimp_disable'] = 'on';

		if ( isset( $input['mailchimp_api_key'] ) && ! empty( $input['mailchimp_api_key'] ) )
			$output['mailchimp_api_key'] = wp_filter_nohtml_kses( $input['mailchimp_api_key'] );

		if ( isset( $input['mailchimp_list_id'] ) && ! empty( $input['mailchimp_list_id'] ) )
			$output['mailchimp_list_id'] = wp_filter_nohtml_kses( $input['mailchimp_list_id'] );

		if ( isset( $input['mailchimp_category_id'] ) && ! empty( $input['mailchimp_category_id'] ) )
			$output['mailchimp_category_id'] = wp_filter_nohtml_kses( $input['mailchimp_category_id'] );

		if ( isset( $input['mailchimp_group_id'] ) && ! empty( $input['mailchimp_group_id'] ) )
			$output['mailchimp_group_id'] = wp_filter_nohtml_kses( $input['mailchimp_group_id'] );

		return apply_filters( 'merchant_theme_options_validate', $output, $input );
	}



	/**
	 * Theme Options Menu
	 * Each option field requires its own add_settings_field function.
	 */

	// Create theme options menu
	// The content that's rendered on the menu page.
	function merchant_theme_options_render_page() {
		?>
		<div class="wrap">
			<h2><?php _e( 'Merchant Options', 'merchant' ); ?></h2>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'merchant_options' );
					do_settings_sections( 'merchant_options' );
					submit_button();
				?>
			</form>
		</div>
		<?php
	}

	// Register the theme options page and its fields
	function merchant_theme_options_init() {

		// Register a setting and its sanitization callback
		// register_setting( $option_group, $option_name, $sanitize_callback );
		// $option_group - A settings group name.
		// $option_name - The name of an option to sanitize and save.
		// $sanitize_callback - A callback function that sanitizes the option's value.
		register_setting( 'merchant_options', 'merchant_theme_options', 'merchant_theme_options_validate' );


		// Register our settings field group
		// add_settings_section( $id, $title, $callback, $page );
		// $id - Unique identifier for the settings section
		// $title - Section title
		// $callback - // Section callback (we don't want anything)
		// $page - // Menu slug, used to uniquely identify the page. See merchant_theme_options_add_page().
		add_settings_section( 'basics', __( 'Basics', 'merchant' ), '__return_false', 'merchant_options' );
		add_settings_section( 'errors', __( 'Error Messages', 'merchant' ), '__return_false', 'merchant_options' );
		add_settings_section( 'mailchimp', __( 'MailChimp', 'merchant' ), '__return_false', 'merchant_options' );


		// Register our individual settings fields
		// add_settings_field( $id, $title, $callback, $page, $section );
		// $id - Unique identifier for the field.
		// $title - Setting field title.
		// $callback - Function that creates the field (from the Theme Option Fields section).
		// $page - The menu page on which to display this field.
		// $section - The section of the settings page in which to show the field.

		// URLs
		add_settings_field( 'checkout_url', __( 'Checkout URL', 'merchant' ), 'merchant_settings_field_checkout_url', 'merchant_options', 'basics' );
		add_settings_field( 'success_url', __( 'Success URL', 'merchant' ), 'merchant_settings_field_success_url', 'merchant_options', 'basics' );
		add_settings_field( 'paypal_icon', __( 'PayPal Icon', 'merchant' ), 'merchant_settings_field_paypal_icon', 'merchant_options', 'basics' );
		add_settings_field( 'promo_codes', __( 'Promo Codes', 'merchant' ), 'merchant_settings_field_promo_codes', 'merchant_options', 'basics' );

		// Errors
		add_settings_field( 'alerts', __( 'Alert Classes', 'merchant' ), 'merchant_settings_field_alert_classes', 'merchant_options', 'errors' );
		add_settings_field( 'paypal_error', __( 'PayPal Error', 'merchant' ), 'merchant_settings_field_paypal_error', 'merchant_options', 'errors' );
		add_settings_field( 'no_item', __( 'No Item Error', 'merchant' ), 'merchant_settings_field_no_item_error', 'merchant_options', 'errors' );
		add_settings_field( 'item_discontinued', __( 'Discontinued Error', 'merchant' ), 'merchant_settings_field_item_discontinued_error', 'merchant_options', 'errors' );
		add_settings_field( 'discount_failure', __( 'Promo Code Error', 'merchant' ), 'merchant_settings_field_item_discount_failure_error', 'merchant_options', 'errors' );
		add_settings_field( 'discount_invalid', __( 'Promo Code Invalid', 'merchant' ), 'merchant_settings_field_item_discount_invalid_error', 'merchant_options', 'errors' );
		add_settings_field( 'discount_success', __( 'Promo Code Success', 'merchant' ), 'merchant_settings_field_item_discount_success_error', 'merchant_options', 'errors' );
		add_settings_field( 'no_email_error', __( 'No Email Error', 'merchant' ), 'merchant_settings_field_item_no_email_error', 'merchant_options', 'errors' );

		// MailChimp
		add_settings_field( 'mailchimp_disable', __( 'Disable', 'merchant' ), 'merchant_settings_field_mailchimp_disable', 'merchant_options', 'mailchimp' );
		add_settings_field( 'mailchimp_api_key', __( 'API Key', 'merchant' ), 'merchant_settings_field_mailchimp_api_key', 'merchant_options', 'mailchimp' );
		add_settings_field( 'mailchimp_list_id', __( 'List ID', 'merchant' ), 'merchant_settings_field_mailchimp_list_id', 'merchant_options', 'mailchimp' );
		add_settings_field( 'mailchimp_category_id', __( 'Category ID', 'merchant' ), 'merchant_settings_field_mailchimp_category_id', 'merchant_options', 'mailchimp' );
		add_settings_field( 'mailchimp_group_id', __( 'Group ID', 'merchant' ), 'merchant_settings_field_mailchimp_group_id', 'merchant_options', 'mailchimp' );

	}
	add_action( 'admin_init', 'merchant_theme_options_init' );

	// Add the theme options page to the admin menu
	// Use add_theme_page() to add under Appearance tab (default).
	// Use add_menu_page() to add as it's own tab.
	// Use add_submenu_page() to add to another tab.
	function merchant_theme_options_add_page() {

		// add_theme_page( $page_title, $menu_title, $capability, $menu_slug, $function );
		// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function );
		// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
		// $page_title - Name of page
		// $menu_title - Label in menu
		// $capability - Capability required
		// $menu_slug - Used to uniquely identify the page
		// $function - Function that renders the options page
		// $theme_page = add_theme_page( __( 'Theme Options', 'merchant' ), __( 'Theme Options', 'merchant' ), 'edit_theme_options', 'theme_options', 'merchant_theme_options_render_page' );

		// $theme_page = add_menu_page( __( 'Theme Options', 'merchant' ), __( 'Theme Options', 'merchant' ), 'edit_theme_options', 'theme_options', 'merchant_theme_options_render_page' );
		$theme_page = add_submenu_page( 'edit.php?post_type=merchant-prices', __( 'Options', 'merchant' ), __( 'Options', 'merchant' ), 'edit_theme_options', 'merchant_options', 'merchant_theme_options_render_page' );
	}
	add_action( 'admin_menu', 'merchant_theme_options_add_page' );



	// Restrict access to the theme options page to admins
	function merchant_option_page_capability( $capability ) {
		return 'edit_theme_options';
	}
	add_filter( 'option_page_capability_merchant_options', 'merchant_option_page_capability' );
