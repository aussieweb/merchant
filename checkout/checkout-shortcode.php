<?php

	/**
	 * Checkout shortcode
	 * @return string Checkout markup
	 */
	function merchant_checkout_page(  ) {

		// Prevent this content from caching
		if ( !defined( 'DONOTCACHEPAGE' ) ) {
			define('DONOTCACHEPAGE', TRUE);
		}

		// Settings
		$options = merchant_get_theme_options();

		// Session data
		$error = merchant_get_session( 'merchant_checkout_error', true );
		$discount_error = merchant_get_session( 'merchant_discount_error', true );
		$discount_success = merchant_get_session( 'merchant_discount_success', true );
		$purchase = merchant_get_session( 'merchant_purchase_item' );

		// Sanity check
		if ( empty( $purchase ) ) return '<p>' . $options['no_item'] . '</p>';

		// Plan data
		$plan = get_post( $purchase['id'] );
		$plan_details = get_post_meta( $purchase['id'], 'merchant_pricing_details', true );
		$summary = get_post_meta( $purchase['id'], 'merchant_pricing_report_summary', true );

		// Check that plan exists
		if ( empty( $plan ) || empty( $plan_details ) ) return '<p>' . $options['item_discontinued'] . '</p>';

		// Check that plan has a price and isn't sold out
		if ( $plan->post_status !== 'publish' || is_null( $plan_details['amount'] ) || $plan_details['amount'] === '' || ( intval( $plan_details['max'] ) !== -1 && is_array( $summary ) && array_key_exists( 'count', $summary ) &&  $plan_details['max'] <= $summary['count'] ) ) return '<p>' . $options['item_discontinued'] . '</p>';

		// Get the price
		$price = number_format( ( isset( $purchase['discount_price'] ) ? $purchase['discount_price'] : $plan_details['amount'] ), 2 );

		// If the plan is free, verify purchase. Otherwise, get payment info.
		if ( intval( $price ) === 0 ) {
			$email = merchant_get_session( 'merchant_checkout_email', true );
			$checkout =
				'<h2>' . __( 'Almost there!', 'merchant' ) . '</h2>' .
				( empty( $error ) ? '' : '<div class=" ' . $options['alert_error_class'] . ' ">' . $error . '</div>' ) .
				'<p>' . ( !array_key_exists( 'submit_free_message', $plan_details ) || empty( $plan_details['submit_free_message'] ) ? sprintf( __( 'Get free, instant access to %s.', 'merchant' ), '<em>' . $plan->post_title . '</em>' ) : stripslashes( $plan_details['submit_free_message'] ) ) . '</p>' .
				'<p>' .
					'<strong>' . $plan->post_title . '</strong><br>' .
					( empty( $plan_details['description'] ) ? '' : '<span class="text-muted">' . $plan_details['description'] . '</span><br>' ) .
					'$' . $price .
				'</p>' .
				'<form class="merchant-checkout-submit-free-form" id="merchant-checkout-submit-free-form" name="merchant_checkout_submit_free" action="" method="post">' .
					'<div class="merchant-free-email">' .
						'<label>' . __( 'Your email address', 'merchant' ) . '</label>' .
						'<input type="email" name="merchant_free_email" value="' . $email . '" required>' .
					'</div>' .
					wp_nonce_field( 'merchant_submit_free_nonce', 'merchant_submit_free_process' ) .
					'<button class="btn btn-secondary btn-large">' . __( 'Get Free Access', 'merchant' ) .  '</button>' .
				'</form>';

		} else {

			$checkout =
				'<h2>' . __( 'Almost there!', 'merchant' ) . '</h2>' .
				( empty( $error ) ? '' : '<div class=" ' . $options['alert_error_class'] . ' ">' . $error . '</div>' ) .
				'<p>' .
					'<strong>' . $plan->post_title . '</strong><br>' .
					( empty( $plan_details['description'] ) ? '' : '<span class="text-muted">' . $plan_details['description'] . '</span><br>' ) .
					'$' . $price .
				'</p>' .
				( empty( $discount_error ) ? '' : '<div class=" ' . $options['alert_error_class'] . ' ">' . $discount_error . '</div>' ) .
				( empty( $discount_success ) ? '' : '<div class=" ' . $options['alert_success_class'] . ' ">' . $discount_success . '</div>' ) .
				( $options['promo_codes'] === 'on' ? '' : '<form class="merchant-checkout-discount-form margin-bottom-small" id="merchant-checkout-discount-form" name="merchant_checkout_discount" action="" method="post">' .
					'<label class="input-inline margin-right" for="merchant_checkout_discount_code">Discount Code:</label>' .
					'<input type="text" class="input-inline input-condensed margin-right" id="merchant_checkout_discount_code" name="merchant_checkout_discount_code" value="' . esc_attr( $purchase['discount_code'] ) . '">' .
					wp_nonce_field( 'merchant_checkout_discount_nonce', 'merchant_checkout_discount_process' ) .
					'<button class="btn btn-small">' . __( 'Apply', 'merchant' ) . '</button>' .
				'</form>' ) .
				'<form class="merchant-checkout-form" id="merchant-checkout-form" name="merchant_checkout" action="" method="post">' .
					wp_nonce_field( 'merchant_pay_with_paypal_nonce', 'merchant_pay_with_paypal_process' ) .
					'<button class="btn btn-secondary btn-large">' . stripslashes( $options['paypal_icon'] ) . __( 'Pay with PayPal', 'merchant' ) . '</button>' .
				'</form>';

		}

		return $checkout;
	}
	add_shortcode( 'merchant_checkout', 'merchant_checkout_page' );


	/**
	 * Process discount codes
	 */
	function merchant_process_promo_code() {

		// Check that form was submitted
		if ( !isset( $_POST['merchant_checkout_discount_process'] ) ) return;

		// Verify data came from proper screen
		if ( !wp_verify_nonce( $_POST['merchant_checkout_discount_process'], 'merchant_checkout_discount_nonce' ) ) {
			die( 'Security check' );
		}

		// Get referrer URL
		$referrer = esc_url_raw( merchant_get_url() );

		// Verify that a code was supplied
		if ( empty( $_POST['merchant_checkout_discount_code'] ) ) {
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Variables
		$options = merchant_get_theme_options();
		$purchase = merchant_get_session( 'merchant_purchase_item' );
		$plan_details = get_post_meta( $purchase['id'], 'merchant_pricing_details', true );

		// Sanity check
		if ( empty( $purchase ) ) {
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Get the promo code
		$promo = get_page_by_title( $_POST['merchant_checkout_discount_code'], 'OBJECT', 'merchant-promos' );

		// If promo is no longer available, display an error
		if ( empty( $promo ) || $promo->post_status !== 'publish' ) {
			merchant_set_session( 'merchant_discount_error', $options['discount_failure'] );
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Get promo code details
		$promo_details = get_post_meta( $promo->ID, 'merchant_promo_details', true );
		$promo_summary = get_post_meta( $promo->ID, 'merchant_promo_report_summary', true );

		// If promo code is expired, unpublish and display an error
		if ( array_key_exists( 'expiration', $promo_details ) && !empty( $promo_details['expiration'] ) && $promo_details['expiration'] < strtotime( 'today', current_time( 'timestamp' ) ) ) {

			// Unpublish the promo code
			wp_update_post(array(
				'ID' => $promo->ID,
				'post_status' => 'draft',
			));

			// Display an error message
			merchant_set_session( 'merchant_discount_error', $options['discount_failure'] );
			wp_safe_redirect( $referrer, 302 );
			exit;

		}

		// If promo code has exceded max usage, unpublish and display an error
		if ( array_key_exists( 'max', $promo_details ) && intval( $promo_details['max'] ) !== -1 && is_array( $promo_summary ) && array_key_exists( 'count', $promo_summary ) && intval( $promo_details['max'] ) <= intval( $promo_summary['count'] ) ) {

			// Unpublish the promo code
			wp_update_post(array(
				'ID' => $promo->ID,
				'post_status' => 'draft',
			));

			// Display an error message
			merchant_set_session( 'merchant_discount_error', $options['discount_failure'] );
			wp_safe_redirect( $referrer, 302 );
			exit;

		}

		// If promo code is not valid on this product, display an error
		if ( array_key_exists( 'valid_on', $promo_details ) && !empty( $promo_details['valid_on'] ) && !array_key_exists( 'all', $promo_details['valid_on'] ) ) {
			if ( !array_key_exists( $purchase['id'], $promo_details['valid_on'] ) || $promo_details['valid_on'][$purchase['id']] !== 'on' ) {
				merchant_set_session( 'merchant_discount_error', $options['discount_invalid'] );
				wp_safe_redirect( $referrer, 302 );
				exit;
			}
		}

		// Get promo price
		$new_price;
		if ( !empty( $promo_details ) && !empty( $promo_details['amount'] ) ) {
			if ( $promo_details['type'] === 'percentage' ) {
				$multiplier = 100 - $promo_details['amount'] <= 0 ? 0 : ( 100 - $promo_details['amount'] ) / 100;
				$new_price = $plan_details['amount'] * $multiplier;
			} else {
				$new_price = $plan_details['amount'] - $promo_details['amount'];
			}
		}

		// Apply promo code
		if ( isset( $new_price ) ) {

			// Convert code to uppercase
			$user_code = strtoupper( $_POST['merchant_checkout_discount_code'] );

			// Update purchase values
			$purchase['discount'] = $promo->ID;
			$purchase['discount_code'] = strtoupper( $promo->post_title );
			$purchase['discount_price'] = $new_price;

			// Set session varaibles
			merchant_set_session( 'merchant_discount_success', $options['discount_success'] );
			merchant_set_session( 'merchant_purchase_item', $purchase );

			// Run custom WordPress action
			do_action( 'merchant_after_discount_code_applied', $user_code, $plan_details['amount'], $new_price );

			// Redirect
			wp_safe_redirect( $referrer, 302 );
			exit;

		}

		// If no code matches, display an error message
		merchant_set_session( 'merchant_discount_error', $options['discount_failure'] );
		wp_safe_redirect( $referrer, 302 );
		exit;

	}
	add_action( 'init', 'merchant_process_promo_code' );


	/**
	 * Process "Pay with PayPal" button
	 */
	function merchant_process_pay_with_paypal() {

		// Check that form was submitted
		if ( !isset( $_POST['merchant_pay_with_paypal_process'] ) ) return;

		// Verify data came from proper screen
		if ( !wp_verify_nonce( $_POST['merchant_pay_with_paypal_process'], 'merchant_pay_with_paypal_nonce' ) ) {
			die( 'Security check' );
		}

		// Variables
		$wpPayPalFramework = wpPayPalFramework::getInstance();
		$referrer = esc_url_raw( merchant_get_url() );
		$options = merchant_get_theme_options();
		$purchase = merchant_get_session( 'merchant_purchase_item' );
		$plan = get_post( $purchase['id'] );
		$plan_details = get_post_meta( $purchase['id'], 'merchant_pricing_details', true );

		// Sanity check
		if ( empty( $purchase ) || empty( $plan ) || empty( $plan_details ) ) {
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Set timestamp to provide access to success page for 24 minutes
		$timestamp = wp_generate_password( 48, false );
		merchant_set_session( 'merchant_timestamp_' . $timestamp, $purchase );

		// Request token
		$setCheckoutArgs = array(
			'METHOD' => 'SetExpressCheckout',
			'RETURNURL' => esc_url_raw( $options['success_url'] ),
			'CANCELURL' => esc_url_raw( add_query_arg( 'merchant_success', $timestamp,  $options['checkout_url'] ) ),
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Authorization',
			'PAYMENTREQUEST_0_AMT' => number_format( ( isset( $purchase['discount_price'] ) ? $purchase['discount_price'] : $plan_details['amount'] ), 2 ),
			'PAYMENTREQUEST_0_DESC' => $plan->post_title . ( empty( $plan_details['description'] ) ? '' : ': ' . $plan_details['description'] ),
			'PAYMENTREQUEST_0_CURRENCYCODE' => 'USD',
			'SOLUTIONTYPE' => 'Sole',
			'NOSHIPPING' => 1,
			'ALLOWNOTE' => 0,
		);
		$setCheckoutResponse = hashCall( $setCheckoutArgs );

		// if response is not successful, display an error message
		if ( is_wp_error( $setCheckoutResponse ) || $setCheckoutResponse['ACK'] !== 'Success' ) {
			merchant_set_session( 'merchant_checkout_error', $options['paypal_error'] );
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Store token for 24 minutes
		merchant_set_session( 'merchant_checkout_token', $setCheckoutResponse['TOKEN'] );

		// Run custom WordPress action
		do_action( 'merchant_after_paypal_authorization', $purchase['id'], $plan_details['amount'] );

		// If response successful, send to PayPal for authorization.
		$getAuthorizationArgs = array(
			'token' => $setCheckoutResponse['TOKEN'],
			'useraction' => 'commit',
		);
		$wpPayPalFramework->sendToExpressCheckout( $getAuthorizationArgs );

	}
	add_action( 'init', 'merchant_process_pay_with_paypal' );


	/**
	 *
	 */
	function merchant_process_free_submit() {

		// Check that form was submitted
		if ( !isset( $_POST['merchant_submit_free_process'] ) ) return;

		// Verify data came from proper screen
		if ( !wp_verify_nonce( $_POST['merchant_submit_free_process'], 'merchant_submit_free_nonce' ) ) {
			die( 'Security check' );
		}

		// Variables
		$options = merchant_get_theme_options();
		$purchase = merchant_get_session( 'merchant_purchase_item' );
		$referrer = esc_url_raw( merchant_get_url() );

		// Check that valid email is provided
		if ( !isset( $_POST['merchant_free_email'] ) || !is_email( $_POST['merchant_free_email'] ) ) {
			if ( isset( $_POST['merchant_free_email'] ) ) {
				merchant_set_session( 'merchant_checkout_email', $_POST['merchant_free_email'] );
			}
			merchant_set_session( 'merchant_checkout_error', $options['no_email_error'] );
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Update purchase data with email
		$purchase['email'] = $_POST['merchant_free_email'];
		merchant_set_session( 'merchant_purchase_item', $purchase );

		// Set timestamp to provide access to success page for 24 minutes
		$timestamp = wp_generate_password( 48, false );
		merchant_set_session( 'merchant_timestamp_' . $timestamp, $purchase );

		// Redirect to success page
		wp_safe_redirect( add_query_arg( 'merchant_success', $timestamp, $options['success_url'] ), 302 );
		exit;

	}
	add_action( 'init', 'merchant_process_free_submit' );