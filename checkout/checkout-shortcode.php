<?php

	/**
	 * Checkout shortcode
	 * @return string Checkout markup
	 */
	function beacon_checkout_page(  ) {

		// Prevent this content from caching
		if ( !defined( 'DONOTCACHEPAGE' ) ) {
			define('DONOTCACHEPAGE', TRUE);
		}

		// Settings
		$options = beacon_get_theme_options();

		if ( is_user_logged_in() ) {

			// Session data
			$error = beacon_get_session( 'beacon_checkout_error', true );
			$discount_error = beacon_get_session( 'beacon_discount_error', true );
			$discount_success = beacon_get_session( 'beacon_discount_success', true );
			$token = beacon_get_session( 'beacon_checkout_token' );
			$purchase = beacon_get_session( 'beacon_purchase_course' );

			// Sanity check
			if ( empty( $purchase ) ) return '<p>' . $options['no_course'] . '</p>';

			// Plan data
			$plan = get_post( $purchase['id'] );
			$plan_details = get_post_meta( $purchase['id'], 'beacon_pricing_details', true );
			$summary = get_post_meta( $purchase['id'], 'beacon_pricing_report_summary', true );

			// Check that plan exists
			if ( empty( $plan ) || empty( $plan_details ) ) return '<p>' . $options['course_discontinued'] . '</p>';

			// Check that plan has a price and isn't sold out
			if ( $plan->post_status !== 'publish' || empty( $plan_details['amount'] ) || ( intval( $plan_details['max'] ) !== -1 && is_array( $summary ) && array_key_exists( 'count', $summary ) &&  $plan_details['max'] <= $summary['count'] ) ) return '<p>' . $options['course_discontinued'] . '</p>';

			// Make sure user hasn't already purchased this plan
			if (
				( !array_key_exists( 'multiple_purchases', $plan_details ) || $plan_details['multiple_purchases'] !== 'on' ) &&
				get_user_meta( get_current_user_id(), 'beacon_course_purchased_' . $purchase['id'], true )  === 'on'
			) {
				beacon_unset_session( 'beacon_purchase_course' );
				return '<p>' . $options['course_already_purchased'] . '</p>';
			}

			// If token exists, check for Payer ID
			if ( !empty( $token ) ) {

				// Get checkout data from PayPal
				$getCheckoutArgs = array(
					'METHOD' => 'GetExpressCheckoutDetails',
					'TOKEN' => $token,
				);
				$getCheckoutResponse = hashCall( $getCheckoutArgs );

				// Get payer ID and final amount
				if ( is_wp_error( $getCheckoutResponse ) || $getCheckoutResponse['ACK'] !== 'Success' || !array_key_exists( 'PAYERID', $getCheckoutResponse ) ) {
					$checkoutResponse = array(
						'payerid' => null,
						'amount' => null,
					);
				} else {
					$checkoutResponse = array(
						'payerid' => $getCheckoutResponse['PAYERID'],
						'amount' => $getCheckoutResponse['PAYMENTREQUEST_0_AMT'],
					);
					beacon_set_session( 'beacon_checkout_response', $checkoutResponse );
				}
			}

			// Get the price
			$price = number_format( ( isset( $purchase['discount_price'] ) ? $purchase['discount_price'] : $plan_details['amount'] ), 2 );

			// If the plan is free, verify purchase. Otherwise, get payment info.
			if ( intval( $price ) === 0 ) {

				$checkout =
					'<h2>' . __( 'One last step...', 'beacon' ) . '</h2>' .
					'<p>' . ( !array_key_exists( 'submit_free_message', $plan_details ) || empty( $plan_details['submit_free_message'] ) ? sprintf( __( 'Get free, instant access to %s.', 'beacon' ), '<em>' . $plan->post_title . '</em>' ) : stripslashes( $plan_details['submit_free_message'] ) ) . '</p>' .
					'<p>' .
						'<strong>' . $plan->post_title . '</strong><br>' .
						( empty( $plan_details['description'] ) ? '' : '<span class="text-muted">' . $plan_details['description'] . '</span><br>' ) .
						'$' . $price .
					'</p>' .
					'<form class="beacon-checkout-submit-free-form" id="beacon-checkout-submit-free-form" name="beacon_checkout_submit_free" action="" method="post">' .
						wp_nonce_field( 'beacon_submit_free_nonce', 'beacon_submit_free_process' ) .
						'<button class="btn btn-secondary btn-large">' . __( 'Get Free Access', 'beacon' ) .  '</button>' .
					'</form>';

			} elseif ( empty( $token ) || empty( $checkoutResponse['payerid'] ) ) {

				$checkout =
					'<h2>' . __( 'Almost there!', 'beacon' ) . '</h2>' .
					( empty( $error ) ? '' : '<div class=" ' . $options['alert_error_class'] . ' ">' . $error . '</div>' ) .
					'<p>' .
						'<strong>' . $plan->post_title . '</strong><br>' .
						( empty( $plan_details['description'] ) ? '' : '<span class="text-muted">' . $plan_details['description'] . '</span><br>' ) .
						'$' . $price .
					'</p>' .
					( empty( $discount_error ) ? '' : '<div class=" ' . $options['alert_error_class'] . ' ">' . $discount_error . '</div>' ) .
					( empty( $discount_success ) ? '' : '<div class=" ' . $options['alert_success_class'] . ' ">' . $discount_success . '</div>' ) .
					'<form class="beacon-checkout-discount-form margin-bottom-small" id="beacon-checkout-discount-form" name="beacon_checkout_discount" action="" method="post">' .
						'<label class="input-inline margin-right" for="beacon_checkout_discount_code">Discount Code:</label>' .
						'<input type="text" class="input-inline input-condensed margin-right" id="beacon_checkout_discount_code" name="beacon_checkout_discount_code" value="' . esc_attr( $purchase['discount_code'] ) . '">' .
						wp_nonce_field( 'beacon_checkout_discount_nonce', 'beacon_checkout_discount_process' ) .
						'<button class="btn btn-small">' . __( 'Apply', 'beacon' ) . '</button>' .
					'</form>' .
					'<form class="beacon-checkout-form" id="beacon-checkout-form" name="beacon_checkout" action="" method="post">' .
						wp_nonce_field( 'beacon_pay_with_paypal_nonce', 'beacon_pay_with_paypal_process' ) .
						'<button class="btn btn-secondary btn-large">' . __( 'Pay with PayPal', 'beacon' ) . '</button>' .
					'</form>';

			} else {

				$checkout =
					'<h2>' . __( 'One last step...', 'beacon' ) . '</h2>' .
					'<p>' . ( !array_key_exists( 'submit_payment_message', $plan_details ) || empty( $plan_details['submit_payment_message'] ) ? sprintf( __( 'Submit your payment for instant access to %s.', 'beacon' ), '<em>' . $plan->post_title . '</em>' ) : stripslashes( $plan_details['submit_payment_message'] ) ) . '</p>' .
					( empty( $error ) ? '' : '<div class=" ' . $options['alert_error_class'] . ' ">' . $error . '</div>' ) .
					'<p>' .
						'<strong>' . $plan->post_title . '</strong><br>' .
						( empty( $plan_details['description'] ) ? '' : '<span class="text-muted">' . $plan_details['description'] . '</span><br>' ) .
						'$' . $price .
					'</p>' .
					'<form class="beacon-checkout-submit-payment-form" id="beacon-checkout-submit-payment-form" name="beacon_checkout_submit_payment" action="" method="post">' .
						wp_nonce_field( 'beacon_submit_payment_nonce', 'beacon_submit_payment_process' ) .
						'<button class="btn btn-secondary btn-large">' . __( 'Submit Payment', 'beacon' ) . '</button>' .
					'</form>';

			}

			return $checkout;

		}

		// Variables
		$referrer = beacon_get_url();

		$checkout =
			'<h2>' . __( 'Step 1: Create an Account', 'beacon' ) . '</h2>' .
			wpwebapp_signup_form() .
			stripslashes( do_shortcode( $options['signup_form_text'] ) );

		return $checkout;

	}
	add_shortcode( 'beacon_checkout', 'beacon_checkout_page' );


	/**
	 * Process discount codes
	 */
	function beacon_process_promo_code() {

		// Check that form was submitted
		if ( !isset( $_POST['beacon_checkout_discount_process'] ) ) return;

		// Verify data came from proper screen
		if ( !wp_verify_nonce( $_POST['beacon_checkout_discount_process'], 'beacon_checkout_discount_nonce' ) ) {
			die( 'Security check' );
		}

		// Get referrer URL
		$referrer = esc_url_raw( beacon_get_url() );

		// Verify that a code was supplied
		if ( empty( $_POST['beacon_checkout_discount_code'] ) ) {
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Variables
		$options = beacon_get_theme_options();
		$purchase = beacon_get_session( 'beacon_purchase_course' );
		$plan_details = get_post_meta( $purchase['id'], 'beacon_pricing_details', true );

		// Sanity check
		if ( empty( $purchase ) ) {
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Get the promo code
		$promo = get_page_by_title( $_POST['beacon_checkout_discount_code'], 'OBJECT', 'beacon-promos' );

		// If promo is no longer available, display an error
		if ( empty( $promo ) || $promo->post_status !== 'publish' ) {
			beacon_set_session( 'beacon_discount_error', $options['discount_failure'] );
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Get promo code details
		$promo_details = get_post_meta( $promo->ID, 'beacon_promo_details', true );
		$promo_summary = get_post_meta( $promo->ID, 'beacon_promo_report_summary', true );

		// If promo code is expired, unpublish and display an error
		if ( array_key_exists( 'expiration', $promo_details ) && !empty( $promo_details['expiration'] ) && $promo_details['expiration'] < strtotime( 'today', current_time( 'timestamp' ) ) ) {

			// Unpublish the promo code
			wp_update_post(array(
				'ID' => $promo->ID,
				'post_status' => 'draft',
			));

			// Display an error message
			beacon_set_session( 'beacon_discount_error', $options['discount_failure'] );
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
			beacon_set_session( 'beacon_discount_error', $options['discount_failure'] );
			wp_safe_redirect( $referrer, 302 );
			exit;

		}

		// If promo code is not valid on this product, display an error
		if ( array_key_exists( 'valid_on', $promo_details ) && !empty( $promo_details['valid_on'] ) && !array_key_exists( 'all', $promo_details['valid_on'] ) ) {
			if ( !array_key_exists( $purchase['id'], $promo_details['valid_on'] ) || $promo_details['valid_on'][$purchase['id']] !== 'on' ) {
				beacon_set_session( 'beacon_discount_error', $options['discount_invalid'] );
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
			$user_code = strtoupper( $_POST['beacon_checkout_discount_code'] );

			// Update purchase values
			$purchase['discount'] = $promo->ID;
			$purchase['discount_code'] = strtoupper( $promo->post_title );
			$purchase['discount_price'] = $new_price;

			// Set session varaibles
			beacon_set_session( 'beacon_discount_success', $options['discount_success'] );
			beacon_set_session( 'beacon_purchase_course', $purchase );

			// Run custom WordPress action
			do_action( 'beacon_after_discount_code_applied', $user_code, $plan_details['amount'], $new_price );

			// Redirect
			wp_safe_redirect( $referrer, 302 );
			exit;

		}

		// If no code matches, display an error message
		beacon_set_session( 'beacon_discount_error', $options['discount_failure'] );
		wp_safe_redirect( $referrer, 302 );
		exit;

	}
	add_action( 'init', 'beacon_process_promo_code' );


	/**
	 * Process "Pay with PayPal" button
	 */
	function beacon_process_pay_with_paypal() {

		// Check that form was submitted
		if ( !isset( $_POST['beacon_pay_with_paypal_process'] ) ) return;

		// Verify data came from proper screen
		if ( !wp_verify_nonce( $_POST['beacon_pay_with_paypal_process'], 'beacon_pay_with_paypal_nonce' ) ) {
			die( 'Security check' );
		}

		// Variables
		$wpPayPalFramework = wpPayPalFramework::getInstance();
		$referrer = esc_url_raw( beacon_get_url() );
		$options = beacon_get_theme_options();
		$purchase = beacon_get_session( 'beacon_purchase_course' );
		$plan = get_post( $purchase['id'] );
		$plan_details = get_post_meta( $purchase['id'], 'beacon_pricing_details', true );

		// Sanity check
		if ( empty( $purchase ) || empty( $plan ) || empty( $plan_details ) ) {
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Request token
		$setCheckoutArgs = array(
			'METHOD' => 'SetExpressCheckout',
			'RETURNURL' => esc_url_raw( $referrer ),
			'CANCELURL' => esc_url_raw( $referrer ),
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
			beacon_set_session( 'beacon_checkout_error', $options['paypal_error'] );
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Store token for 24 minutes
		beacon_set_session( 'beacon_checkout_token', $setCheckoutResponse['TOKEN'] );

		// Run custom WordPress action
		do_action( 'beacon_after_paypal_authorization', $purchase['id'], $plan_details['amount'] );

		// If response successful, send to PayPal for authorization.
		$getAuthorizationArgs = array(
			'token' => $setCheckoutResponse['TOKEN']
		);
		$wpPayPalFramework->sendToExpressCheckout( $getAuthorizationArgs );

	}
	add_action( 'init', 'beacon_process_pay_with_paypal' );


	/**
	 * Send the buyer an email after purchase
	 * @param  array $purchase The purchase info
	 */
	function beacon_checkout_send_buyer_email( $purchase ) {

		// Get email details
		$details = get_post_meta( $purchase['id'], 'beacon_pricing_details', true );

		// Make sure email content exists
		if ( !is_array( $details ) || array_key_exists( 'email_1_subject', $details ) || array_key_exists( 'email_1_content', $details ) ) return;

		// Variables
		$current_user = wp_get_current_user();
		$admin_email = sanitize_email( get_option( 'admin_email' ) );
		$name = get_bloginfo( 'name' );
		$domain = beacon_get_site_domain();

		// Setup email
		$to = sanitize_email( $current_user->user_email );
		$headers = array(
			'From: ' . $name . ' <notifications@' . $domain . '>',
			'Sender: ' . $name . ' <' . $admin_email . '>',
			'Reply-To: ' . $name . ' <' . $admin_email . '>',
		);
		$subject = $details['email_1_subject'];
		$message = nl2br( $details['email_1'] );

		// Send email
		wp_mail( $to, $subject, $message, $headers );

	}


	/**
	 * Send the seller an email after purchase
	 * @param  array $purchase The purchase info
	 */
	function beacon_checkout_send_admin_email( $purchase ) {

		// Get email details
		$details = get_post_meta( $purchase['id'], 'beacon_pricing_details', true );

		// Make sure email content exists
		if ( !is_array( $details ) || array_key_exists( 'email_2_subject', $details ) || array_key_exists( 'email_2_content', $details ) ) return;

		// Variables
		$current_user = wp_get_current_user();
		$admin_email = sanitize_email( get_option( 'admin_email' ) );
		$name = get_bloginfo( 'name' );
		$domain = beacon_get_site_domain();

		// Setup email
		$headers = array(
			'From: ' . $name . ' <notifications@' . $domain . '>',
			'Sender: ' . $name . ' <' . $admin_email . '>',
			'Reply-To: ' . $name . ' <' . $admin_email . '>',
		);
		$subject = $details['email_2_subject'];
		$message = nl2br( str_replace( '{{username}}', $current_user->user_login, $details['email_2'] ) );

		// Send email
		wp_mail( $admin_email, $subject, $message, $headers );

	}


	/**
	 * Grant user access to plan
	 * @param  array $purchase  The purchase data
	 */
	function beacon_checkout_grant_access( $purchase ) {

		// Get existing purchases
		$user_id = get_current_user_id();
		$purchases = get_user_meta( $user_id, 'beacon_purchases', true );
		$purchases = empty( $purchases ) ? array() : $purchases;

		// Add new purchase
		$purchases[$purchase['id']] = 'on';

		// Update user access
		update_user_meta( $user_id, 'beacon_purchases', $purchases );

	}


	/**
	 * If max volume available is reached, make the plan unavailable for future purchases
	 * @param  array $purchase      The purchase data
	 * @param  array $plan_details  Details about the plan being purchased
	 */
	function beacon_checkout_send_plan_soldout_email( $purchase, $plan_details, $summary ) {

		// Check if max volume allowed to be sold has been reached
		if ( !array_key_exists( 'max', $plan_details ) || intval( $plan_details['max'] ) === -1 || intval( $plan_details['max'] ) > intval( $summary['count'] ) ) return;

		// Unpublish the plan
		wp_update_post(array(
			'ID' => $purchase['id'],
			'post_status' => 'draft',
		));

		// Setup email
		$plan = get_post( $purchase['id'] );
		$admin_email = sanitize_email( get_option( 'admin_email' ) );
		$name = get_bloginfo( 'name' );
		$domain = beacon_get_site_domain();
		$headers = array(
			'From: ' . $name . ' <notifications@' . $domain . '>',
			'Sender: ' . $name . ' <' . $admin_email . '>',
			'Reply-To: ' . $name . ' <' . $admin_email . '>',
		);
		$subject = __( 'Sold Out', 'beacon' ) . ': ' . $plan->post_title;
		$message = sprintf( __( '"%s" has sold out. Visit %s to learn more.', 'beacon' ), $plan->post_title, 'http://localhost:8888/beacon/wordpress/wp-admin/post.php?post=' . $purchase['id'] . '&action=edit' );

		// Send email
		wp_mail( $admin_email, $subject, $message, $headers );

	}


	/**
	 * Record purchase for reporting
	 * @param  array $purchase  The purchase data
	 */
	function beacon_checkout_update_purchase_report( $purchase ) {

		// Variables
		$plan_details = get_post_meta( $purchase['id'], 'beacon_pricing_details', true );
		$summary = get_post_meta( $purchase['id'], 'beacon_pricing_report_summary', true );
		$report = get_post_meta( $purchase['id'], 'beacon_pricing_report', true );

		// Create arrays if missing
		if ( empty( $summary ) ) $summary = array();
		if ( empty( $report ) ) $report = array();

		// Update sold count
		$summary['count'] = ( array_key_exists( 'count', $summary ) ? $summary['count'] + 1 : 1 );

		// Update total
		$price = ( isset( $purchase['discount_price'] ) ? $purchase['discount_price'] : $plan_details['amount'] );
		$summary['total'] = ( array_key_exists( 'total', $summary ) ? $summary['total'] + $price : $price );

		// Update complete report
		$report[] = array(
			'date' => current_time( 'timestamp' ),
			'purchaser' => get_current_user_id(),
			'price' => ( isset( $purchase['discount_price'] ) ? $purchase['discount_price'] : $plan_details['amount'] ),
			'promo_code' => $purchase['discount'],
		);

		// Update count in database
		update_post_meta( $purchase['id'], 'beacon_pricing_report_summary', $summary );
		update_post_meta( $purchase['id'], 'beacon_pricing_report', $report );

		// If max volume available is reached, make the plan unavailable for future purchases
		beacon_checkout_send_plan_soldout_email( $purchase, $plan_details, $summary );

	}


	/**
	 * Send notification email when promo code is used (if applicable)
	 * @param  array $purchase      The purchase data
	 * @param  array $promo_details Details about the promo code being used
	 */
	function beacon_checkout_send_promo_code_used_email( $purchase, $promo_details ) {

		// Check if notifications are set
		if ( !array_key_exists( 'notify', $promo_details ) || empty( $promo_details['notify'] ) ) return;

		// Setup email
		$promo = get_post( $purchase['discount'] );
		$admin_email = sanitize_email( get_option( 'admin_email' ) );
		$to = array_map( 'trim', explode( ',', $notify ) );
		$name = get_bloginfo( 'name' );
		$domain = beacon_get_site_domain();
		$headers = array(
			'From: ' . $name . ' <notifications@' . $domain . '>',
			'Sender: ' . $name . ' <' . $admin_email . '>',
			'Reply-To: ' . $name . ' <' . $admin_email . '>',
		);
		$subject = __( 'Promo Code Purchase Notification', 'beacon' ) . ': ' . strtoupper( $promo->post_title );
		$message = sprintf( __( 'The promo code %s was just used for a %s purchase on %s.', 'beacon' ), strtoupper( $promo->post_title ), number_format( $purchase['discount_price'], 2 ), $name );

		// Send email
		wp_mail( $to, $subject, $message, $headers );

	}


	/**
	 * If max volume available is reached, make the plan unavailable for future purchases
	 * @param  array $purchase      The purchase data
	 * @param  array $promo_details Details about the promo code being used
	 */
	function beacon_checkout_send_promo_soldout_email( $purchase, $promo_details, $summary ) {

		// Check if max volume allowed to be sold has been reached
		if ( !array_key_exists( 'max', $promo_details ) || intval( $promo_details['max'] ) === -1 || intval( $promo_details['max'] ) > intval( $summary['count'] ) ) return;

		// Unpublish the promo
		wp_update_post(array(
			'ID' => $purchase['discount'],
			'post_status' => 'draft',
		));

		// Setup email
		$promo = get_post( $purchase['discount'] );
		$admin_email = sanitize_email( get_option( 'admin_email' ) );
		$to = ( array_key_exists( 'notify', $promo_details ) && !empty( $promo_details['notify'] ) ? array_map( 'trim', explode( ',', $notify ) ) : $admin_email );
		$name = get_bloginfo( 'name' );
		$domain = beacon_get_site_domain();
		$headers = array(
			'From: ' . $name . ' <notifications@' . $domain . '>',
			'Sender: ' . $name . ' <' . $admin_email . '>',
			'Reply-To: ' . $name . ' <' . $admin_email . '>',
		);
		$subject = __( 'Promo Code Sold Out', 'beacon' ) . ': ' . strtoupper( $promo->post_title );
		$message = sprintf( __( 'The promo code %s has sold out. Visit %s to learn more.', 'beacon' ), strtoupper( $promo->post_title ), 'http://localhost:8888/beacon/wordpress/wp-admin/post.php?post=' . $purchase['discount'] . '&action=edit' );

		// Send email
		wp_mail( $to, $subject, $message, $headers );

	}


	/**
	 * Record purchase against a promo code (if one was used) for reporting
	 * @param  array $purchase  The purchase data
	 */
	function beacon_checkout_update_promo_code_report( $purchase ) {

		// Only run if promo code was used
		if ( !isset( $purchase['discount'] ) || !isset( $purchase['discount_price'] ) ) return;

		// Get promo code details
		$promo_details = get_post_meta( $purchase['discount'], 'beacon_promo_details', true );
		$summary = get_post_meta( $purchase['discount'], 'beacon_promo_report_summary', true );

		// Sanity check
		if ( empty( $promo_details ) ) return;

		// Create arrays if missing
		if ( empty( $summary ) ) $summary = array();

		// Update used count
		$summary['count'] = ( array_key_exists( 'count', $summary ) ? $summary['count'] + 1 : 1 );

		// Update total
		$summary['total'] = ( array_key_exists( 'total', $summary ) ? $summary['total'] + $purchase['discount_price'] : $purchase['discount_price'] );

		// Save the new count to the database
		update_post_meta( $purchase['discount'], 'beacon_promo_report_summary', $summary );

		// If notifiers provided, notify them that promo code was used
		beacon_checkout_send_promo_code_used_email( $purchase, $promo_details );

		// If max volume available is reached, make the promo code unavailable for future purchases
		beacon_checkout_send_promo_soldout_email( $purchase, $promo_details, $summary );

	}


	/**
	 * Process "Submit Payment" button
	 */
	function beacon_process_submit_payment() {

		// Check that form was submitted
		if ( !isset( $_POST['beacon_submit_payment_process'] ) ) return;

		// Verify data came from proper screen
		if ( !wp_verify_nonce( $_POST['beacon_submit_payment_process'], 'beacon_submit_payment_nonce' ) ) {
			die( 'Security check' );
		}

		// Variables
		$wpPayPalFramework = wpPayPalFramework::getInstance();
		$referrer = esc_url_raw( beacon_get_url() );
		$options = beacon_get_theme_options();
		$token = beacon_get_session( 'beacon_checkout_token' );
		$checkoutResponse = beacon_get_session( 'beacon_checkout_response' );
		$purchase = beacon_get_session( 'beacon_purchase_course' );

		// Sanity check
		if ( empty( $token ) || empty( $checkoutResponse['payerid'] ) || empty( $checkoutResponse['amount'] ) || empty( $purchase ) ) {
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Complete payment
		$doCheckoutArgs = array(
			'METHOD' => 'DoExpressCheckoutPayment',
			'TOKEN' => $token,
			'PAYERID' => $checkoutResponse['payerid'],
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'SALE',
			'PAYMENTREQUEST_0_AMT' => $checkoutResponse['amount'],
			'PAYMENTREQUEST_0_CURRENCYCODE' => 'USD',
		);
		$doCheckoutResponse = hashCall( $doCheckoutArgs );

		// If payment completion fail, display error message
		if ( is_wp_error( $doCheckoutResponse ) || $doCheckoutResponse['ACK'] !== 'Success' ) {
			beacon_set_session( 'beacon_checkout_error', $options['paypal_error'] );
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Grant user access
		beacon_checkout_grant_access( $purchase );

		// Update plans sold count
		beacon_checkout_update_purchase_report( $purchase );

		// Update promo code count
		beacon_checkout_update_promo_code_report( $purchase );

		// Send notification emails
		beacon_checkout_send_buyer_email( $purchase );
		beacon_checkout_send_admin_email( $purchase );

		// Unset session variables
		beacon_unset_session( 'beacon_checkout_token' );
		beacon_unset_session( 'beacon_checkout_response' );
		beacon_unset_session( 'beacon_purchase_course' );

		// Run custom WordPress action
		do_action( 'beacon_after_paypal_complete', get_current_user_id(), $purchase['id'] );

		// Redirect
		$plan_details = get_post_meta( $purchase['id'], 'beacon_pricing_details', true );
		wp_safe_redirect( $plan_details['redirect_url'], 302 );
		exit;

	}
	add_action( 'init', 'beacon_process_submit_payment' );


	/**
	 * Process "Get Free Access" button
	 */
	function beacon_process_submit_free() {

		// Check that form was submitted
		if ( !isset( $_POST['beacon_submit_free_process'] ) ) return;

		// Verify data came from proper screen
		if ( !wp_verify_nonce( $_POST['beacon_submit_free_process'], 'beacon_submit_free_nonce' ) ) {
			die( 'Security check' );
		}

		// Variables
		$referrer = esc_url_raw( beacon_get_url() );
		$options = beacon_get_theme_options();
		$purchase = beacon_get_session( 'beacon_purchase_course' );

		// Sanity check
		if ( empty( $purchase ) ) {
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Grant user access
		beacon_checkout_grant_access( $purchase );

		// Update plans sold count
		beacon_checkout_update_purchase_report( $purchase );

		// Update promo code count
		beacon_checkout_update_promo_code_report( $purchase );

		// Sent notification emails
		beacon_checkout_send_buyer_email( $purchase );
		beacon_checkout_send_admin_email( $purchase );

		// Unset session variables
		beacon_unset_session( 'beacon_checkout_token' );
		beacon_unset_session( 'beacon_checkout_response' );
		beacon_unset_session( 'beacon_purchase_course' );

		// Run custom WordPress action
		do_action( 'beacon_after_free_access_complete', get_current_user_id(), $purchase['id'] );

		// Redirect
		$plan_details = get_post_meta( $purchase['id'], 'beacon_pricing_details', true );
		wp_safe_redirect( $plan_details['redirect_url'], 302 );
		exit;

	}
	add_action( 'init', 'beacon_process_submit_free' );