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

	function beacon_settings_field_checkout_url() {
		$options = beacon_get_theme_options();
		?>
		<input type="url" name="beacon_theme_options[checkout_url]" class="large-text" id="checkout_url" value="<?php echo esc_url( $options['checkout_url'] ); ?>" />
		<label class="description" for="checkout_url"><?php _e( 'URL for the checkout page', 'beacon' ); ?></label>
		<?php
	}

	function beacon_settings_field_checkout_signup_form_text() {
		$options = beacon_get_theme_options();
		?>
		<textarea name="beacon_theme_options[signup_form_text]" class="large-text" id="signup_form_text" cols="50" rows="6"><?php echo stripslashes( esc_textarea( beacon_get_jetpack_markdown( $options, 'signup_form_text' ) ) ); ?></textarea>
		<label class="description" for="signup_form_text"><?php _e( 'Text to display after the sign up form during checkout', 'beacon' ); ?></label>
		<?php
	}

	function beacon_settings_field_alert_classes() {
		$options = beacon_get_theme_options();
		?>
		<div>
			<input type="text" name="beacon_theme_options[alert_success_class]" id="alert_success_class" value="<?php echo esc_attr( $options['alert_success_class'] ); ?>" />
			<label class="description" for="alert_success_class"><?php _e( 'Class(es) for messages when tasks and processes are successfully completed', 'beacon' ); ?></label>
		</div>
		<br>
		<div>
			<input type="text" name="beacon_theme_options[alert_error_class]" id="alert_error_class" value="<?php echo esc_attr( $options['alert_error_class'] ); ?>" />
			<label class="description" for="alert_error_class"><?php _e( 'Class(es) for messages when tasks and processes fail', 'beacon' ); ?></label>
		</div>
		<?php
	}

	function beacon_settings_field_paypal_error() {
		$options = beacon_get_theme_options();
		?>
		<input type="text" name="beacon_theme_options[paypal_error]" class="large-text" id="paypal_error" value="<?php echo stripslashes( esc_attr( $options['paypal_error'] ) ); ?>" />
		<label class="description" for="paypal_error"><?php _e( 'Error when PayPal authorization fails', 'beacon' ); ?></label>
		<?php
	}

	function beacon_settings_field_course_already_purchased_error() {
		$options = beacon_get_theme_options();
		?>
		<input type="text" name="beacon_theme_options[course_already_purchased]" class="large-text" id="course_already_purchased" value="<?php echo stripslashes( esc_attr( $options['course_already_purchased'] ) ); ?>" />
		<label class="description" for="course_already_purchased"><?php _e( 'Error when user already owns the course', 'beacon' ); ?></label>
		<?php
	}

	function beacon_settings_field_no_course_error() {
		$options = beacon_get_theme_options();
		?>
		<input type="text" name="beacon_theme_options[no_course]" class="large-text" id="no_course" value="<?php echo stripslashes( esc_attr( $options['no_course'] ) ); ?>" />
		<label class="description" for="no_course"><?php _e( 'Error when no course is chosen', 'beacon' ); ?></label>
		<?php
	}

	function beacon_settings_field_course_discontinued_error() {
		$options = beacon_get_theme_options();
		?>
		<input type="text" name="beacon_theme_options[course_discontinued]" class="large-text" id="course_discontinued" value="<?php echo stripslashes( esc_attr( $options['course_discontinued'] ) ); ?>" />
		<label class="description" for="course_discontinued"><?php _e( 'Error when course is no longer available at set price', 'beacon' ); ?></label>
		<?php
	}

	function beacon_settings_field_course_discount_failure_error() {
		$options = beacon_get_theme_options();
		?>
		<input type="text" name="beacon_theme_options[discount_failure]" class="large-text" id="discount_failure" value="<?php echo stripslashes( esc_attr( $options['discount_failure'] ) ); ?>" />
		<label class="description" for="discount_failure"><?php _e( 'Error when promo code fails', 'beacon' ); ?></label>
		<?php
	}

	function beacon_settings_field_course_discount_invalid_error() {
		$options = beacon_get_theme_options();
		?>
		<input type="text" name="beacon_theme_options[discount_invalid]" class="large-text" id="discount_invalid" value="<?php echo stripslashes( esc_attr( $options['discount_invalid'] ) ); ?>" />
		<label class="description" for="discount_invalid"><?php _e( 'Error when promo code is used on an invalid product', 'beacon' ); ?></label>
		<?php
	}

	function beacon_settings_field_course_discount_success_error() {
		$options = beacon_get_theme_options();
		?>
		<input type="text" name="beacon_theme_options[discount_success]" class="large-text" id="discount_success" value="<?php echo stripslashes( esc_attr( $options['discount_success'] ) ); ?>" />
		<label class="description" for="discount_success"><?php _e( 'Message when promo code successfully entered', 'beacon' ); ?></label>
		<?php
	}

	function beacon_settings_field_get_mailchimp_data( $group = null ) {

		$options = beacon_get_theme_options();

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

	function beacon_settings_field_mailchimp_api_key() {
		$options = beacon_get_theme_options();
		?>
		<input type="text" name="beacon_theme_options[mailchimp_api_key]" class="regular-text" id="mailchimp_api_key" value="<?php echo esc_attr( $options['mailchimp_api_key'] ); ?>" />
		<label class="description" for="mailchimp_api_key"><?php _e( 'MailChimp API key', 'beacon' ); ?></label>
		<?php
	}

	function beacon_settings_field_mailchimp_list_id() {
		$options = beacon_get_theme_options();
		?>
		<input type="text" name="beacon_theme_options[mailchimp_list_id]" class="regular-text" id="mailchimp_list_id" value="<?php echo esc_attr( $options['mailchimp_list_id'] ); ?>" />
		<label class="description" for="mailchimp_list_id"><?php _e( 'MailChimp list ID', 'beacon' ); ?></label>
		<?php
	}

	function beacon_settings_field_mailchimp_category_id() {
		$options = beacon_get_theme_options();
		$mailchimp = beacon_settings_field_get_mailchimp_data();
		?>
		<select name="beacon_theme_options[mailchimp_category_id]" id="mailchimp_category_id">
			<option value="" <?php selected( '', $options['mailchimp_category_id'] ); ?>>None</option>
			<?php foreach ( $mailchimp['categories'] as $key => $category ) : ?>
				<option value="<?php echo esc_attr( $category['id'] ); ?>" <?php selected( $category['id'], $options['mailchimp_category_id'] ); ?>><?php echo esc_html( $category['title'] ); ?></option>
			<?php endforeach; ?>
		</select>
		<label class="description" for="sample_theme_options[selectinput]"><?php _e( 'MailChimp category ID', 'beacon' ); ?></label>
		<?php
	}

	function beacon_settings_field_mailchimp_group_id() {
		$options = beacon_get_theme_options();
		$mailchimp = beacon_settings_field_get_mailchimp_data( $options['mailchimp_category_id'] );
		?>
		<select name="beacon_theme_options[mailchimp_group_id]" id="mailchimp_group_id">
			<option value="" <?php selected( '', $options['mailchimp_group_id'] ); ?>>None</option>
			<?php foreach ( $mailchimp['interests'] as $key => $interest ) : ?>
				<option value="<?php echo esc_attr( $interest['id'] ); ?>" <?php selected( $interest['id'], $options['mailchimp_group_id'] ); ?>><?php echo esc_html( $interest['name'] ); ?></option>
			<?php endforeach; ?>
		</select>
		<label class="description" for="sample_theme_options[selectinput]"><?php _e( 'MailChimp group ID', 'beacon' ); ?></label>
		<?php
	}



	/**
	 * Theme Option Defaults & Sanitization
	 * Each option field requires a default value under beacon_get_theme_options(), and an if statement under beacon_theme_options_validate();
	 */

	// Get the current options from the database.
	// If none are specified, use these defaults.
	function beacon_get_theme_options() {
		$saved = (array) get_option( 'beacon_theme_options' );
		$defaults = array(

			// URLs
			'checkout_url' => site_url() . '/checkout/',

			// Checkout
			'signup_form_text' => '<p>Already have an account? <a href="' . site_url() . '/login/">Login.</a></p>',
			'signup_form_text_markdown' => '',

			// Errors
			'alert_success_class' => '',
			'alert_error_class' => '',
			'paypal_error' => 'Unable to authorize payment through PayPal. Please try again.',
			'course_already_purchased' => 'You already own this.',
			'no_course' => 'Please select a course to purchase.',
			'course_discontinued' => 'Sorry, this offering is no longer available.',
			'discount_failure' => 'Sorry, this discount code is no longer valid.',
			'discount_invalid' => 'Sorry, this discount code cannot be used on this product.',
			'discount_success' => 'Your discount code was applied to this purchase.',

			// MailChimp
			'mailchimp_api_key' => '',
			'mailchimp_list_id' => '',
			'mailchimp_category_id' => '',
			'mailchimp_group_id' => '',

		);

		$defaults = apply_filters( 'beacon_default_theme_options', $defaults );

		$options = wp_parse_args( $saved, $defaults );
		$options = array_intersect_key( $options, $defaults );

		return $options;
	}

	// Sanitize and validate updated theme options
	function beacon_theme_options_validate( $input ) {
		$output = array();

		// URLs
		if ( isset( $input['checkout_url'] ) && ! empty( $input['checkout_url'] ) )
			$output['checkout_url'] = wp_filter_nohtml_kses( $input['checkout_url'] );

		// Checkout
		if ( isset( $input['signup_form_text'] ) && ! empty( $input['signup_form_text'] ) ) {
			$output['signup_form_text'] = wp_filter_post_kses( beacon_process_jetpack_markdown ( $input['signup_form_text'] ) );
			$output['signup_form_text_markdown'] = wp_filter_post_kses( $input['signup_form_text'] );
		}

		// Errors
		if ( isset( $input['alert_success_class'] ) && ! empty( $input['alert_success_class'] ) )
			$output['alert_success_class'] = wp_filter_nohtml_kses( $input['alert_success_class'] );

		if ( isset( $input['alert_error_class'] ) && ! empty( $input['alert_error_class'] ) )
			$output['alert_error_class'] = wp_filter_nohtml_kses( $input['alert_error_class'] );

		if ( isset( $input['paypal_error'] ) && ! empty( $input['paypal_error'] ) )
			$output['paypal_error'] = wp_filter_nohtml_kses( $input['paypal_error'] );

		if ( isset( $input['course_already_purchased'] ) && ! empty( $input['course_already_purchased'] ) )
			$output['course_already_purchased'] = wp_filter_nohtml_kses( $input['course_already_purchased'] );

		if ( isset( $input['no_course'] ) && ! empty( $input['no_course'] ) )
			$output['no_course'] = wp_filter_nohtml_kses( $input['no_course'] );

		if ( isset( $input['course_discontinued'] ) && ! empty( $input['course_discontinued'] ) )
			$output['course_discontinued'] = wp_filter_nohtml_kses( $input['course_discontinued'] );

		if ( isset( $input['discount_failure'] ) && ! empty( $input['discount_failure'] ) )
			$output['discount_failure'] = wp_filter_nohtml_kses( $input['discount_failure'] );

		if ( isset( $input['discount_invalid'] ) && ! empty( $input['discount_invalid'] ) )
			$output['discount_invalid'] = wp_filter_nohtml_kses( $input['discount_invalid'] );

		if ( isset( $input['discount_success'] ) && ! empty( $input['discount_success'] ) )
			$output['discount_success'] = wp_filter_nohtml_kses( $input['discount_success'] );

		// MailChimp
		if ( isset( $input['mailchimp_api_key'] ) && ! empty( $input['mailchimp_api_key'] ) )
			$output['mailchimp_api_key'] = wp_filter_nohtml_kses( $input['mailchimp_api_key'] );

		if ( isset( $input['mailchimp_list_id'] ) && ! empty( $input['mailchimp_list_id'] ) )
			$output['mailchimp_list_id'] = wp_filter_nohtml_kses( $input['mailchimp_list_id'] );

		if ( isset( $input['mailchimp_category_id'] ) && ! empty( $input['mailchimp_category_id'] ) )
			$output['mailchimp_category_id'] = wp_filter_nohtml_kses( $input['mailchimp_category_id'] );

		if ( isset( $input['mailchimp_group_id'] ) && ! empty( $input['mailchimp_group_id'] ) )
			$output['mailchimp_group_id'] = wp_filter_nohtml_kses( $input['mailchimp_group_id'] );

		return apply_filters( 'beacon_theme_options_validate', $output, $input );
	}



	/**
	 * Theme Options Menu
	 * Each option field requires its own add_settings_field function.
	 */

	// Create theme options menu
	// The content that's rendered on the menu page.
	function beacon_theme_options_render_page() {
		?>
		<div class="wrap">
			<h2><?php _e( 'Beacon Course Options', 'beacon' ); ?></h2>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'beacon_options' );
					do_settings_sections( 'beacon_options' );
					submit_button();
				?>
			</form>
		</div>
		<?php
	}

	// Register the theme options page and its fields
	function beacon_theme_options_init() {

		// Register a setting and its sanitization callback
		// register_setting( $option_group, $option_name, $sanitize_callback );
		// $option_group - A settings group name.
		// $option_name - The name of an option to sanitize and save.
		// $sanitize_callback - A callback function that sanitizes the option's value.
		register_setting( 'beacon_options', 'beacon_theme_options', 'beacon_theme_options_validate' );


		// Register our settings field group
		// add_settings_section( $id, $title, $callback, $page );
		// $id - Unique identifier for the settings section
		// $title - Section title
		// $callback - // Section callback (we don't want anything)
		// $page - // Menu slug, used to uniquely identify the page. See beacon_theme_options_add_page().
		add_settings_section( 'urls', 'URLs', '__return_false', 'beacon_options' );
		add_settings_section( 'checkout', 'Checkout', '__return_false', 'beacon_options' );
		add_settings_section( 'errors', 'Error Messages', '__return_false', 'beacon_options' );
		add_settings_section( 'mailchimp', 'MailChimp', '__return_false', 'beacon_options' );


		// Register our individual settings fields
		// add_settings_field( $id, $title, $callback, $page, $section );
		// $id - Unique identifier for the field.
		// $title - Setting field title.
		// $callback - Function that creates the field (from the Theme Option Fields section).
		// $page - The menu page on which to display this field.
		// $section - The section of the settings page in which to show the field.

		// URLs
		add_settings_field( 'checkout_url', __( 'Checkout URL', 'beacon' ), 'beacon_settings_field_checkout_url', 'beacon_options', 'urls' );

		// Checkout
		add_settings_field( 'signup_form_text', __( 'Sign Up Form Text', 'beacon' ), 'beacon_settings_field_checkout_signup_form_text', 'beacon_options', 'checkout' );

		// Errors
		add_settings_field( 'alerts', __( 'Alert Classes', 'beacon' ), 'beacon_settings_field_alert_classes', 'beacon_options', 'errors' );
		add_settings_field( 'paypal_error', __( 'PayPal Error', 'beacon' ), 'beacon_settings_field_paypal_error', 'beacon_options', 'errors' );
		add_settings_field( 'course_already_purchased', __( 'Already Owned Error', 'beacon' ), 'beacon_settings_field_course_already_purchased_error', 'beacon_options', 'errors' );
		add_settings_field( 'no_course', __( 'No Course Error', 'beacon' ), 'beacon_settings_field_no_course_error', 'beacon_options', 'errors' );
		add_settings_field( 'course_discontinued', __( 'Discontinued Error', 'beacon' ), 'beacon_settings_field_course_discontinued_error', 'beacon_options', 'errors' );
		add_settings_field( 'discount_failure', __( 'Promo Code Error', 'beacon' ), 'beacon_settings_field_course_discount_failure_error', 'beacon_options', 'errors' );
		add_settings_field( 'discount_invalid', __( 'Promo Code Invalid', 'beacon' ), 'beacon_settings_field_course_discount_invalid_error', 'beacon_options', 'errors' );
		add_settings_field( 'discount_success', __( 'Promo Code Success', 'beacon' ), 'beacon_settings_field_course_discount_success_error', 'beacon_options', 'errors' );

		// MailChimp
		add_settings_field( 'mailchimp_api_key', __( 'API Key', 'beacon' ), 'beacon_settings_field_mailchimp_api_key', 'beacon_options', 'mailchimp' );
		add_settings_field( 'mailchimp_list_id', __( 'List ID', 'beacon' ), 'beacon_settings_field_mailchimp_list_id', 'beacon_options', 'mailchimp' );
		add_settings_field( 'mailchimp_category_id', __( 'Category ID', 'beacon' ), 'beacon_settings_field_mailchimp_category_id', 'beacon_options', 'mailchimp' );
		add_settings_field( 'mailchimp_group_id', __( 'Group ID', 'beacon' ), 'beacon_settings_field_mailchimp_group_id', 'beacon_options', 'mailchimp' );

	}
	add_action( 'admin_init', 'beacon_theme_options_init' );

	// Add the theme options page to the admin menu
	// Use add_theme_page() to add under Appearance tab (default).
	// Use add_menu_page() to add as it's own tab.
	// Use add_submenu_page() to add to another tab.
	function beacon_theme_options_add_page() {

		// add_theme_page( $page_title, $menu_title, $capability, $menu_slug, $function );
		// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function );
		// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
		// $page_title - Name of page
		// $menu_title - Label in menu
		// $capability - Capability required
		// $menu_slug - Used to uniquely identify the page
		// $function - Function that renders the options page
		// $theme_page = add_theme_page( __( 'Theme Options', 'beacon' ), __( 'Theme Options', 'beacon' ), 'edit_theme_options', 'theme_options', 'beacon_theme_options_render_page' );

		// $theme_page = add_menu_page( __( 'Theme Options', 'beacon' ), __( 'Theme Options', 'beacon' ), 'edit_theme_options', 'theme_options', 'beacon_theme_options_render_page' );
		$theme_page = add_submenu_page( 'edit.php?post_type=beacon-prices', __( 'Options', 'beacon' ), __( 'Options', 'beacon' ), 'edit_theme_options', 'beacon_options', 'beacon_theme_options_render_page' );
	}
	add_action( 'admin_menu', 'beacon_theme_options_add_page' );



	// Restrict access to the theme options page to admins
	function beacon_option_page_capability( $capability ) {
		return 'edit_theme_options';
	}
	add_filter( 'option_page_capability_beacon_options', 'beacon_option_page_capability' );
