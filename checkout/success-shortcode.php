<?php

	/**
	 * Checkout shortcode
	 * @return string Checkout markup
	 */
	function merchant_success_page(  ) {

		// Prevent this content from caching
		if ( !defined( 'DONOTCACHEPAGE' ) ) {
			define('DONOTCACHEPAGE', TRUE);
		}

		// Variables
		$options = merchant_get_theme_options();
		$purchase = merchant_get_session( 'merchant_purchase_item' );
		if ( empty( $purchase ) && isset( $_GET['merchant_success'] ) ) {
			$purchase = merchant_get_session( 'merchant_timestamp_' . $_GET['merchant_success'] );
		}

		// Sanity check
		if ( empty( $purchase ) ) return '<p>' . $options['no_item'] . '</p>';

		// Plan data
		$plan = get_post( $purchase['id'] );
		$plan_details = get_post_meta( $purchase['id'], 'merchant_pricing_details', true );

		// Check that plan exists
		if ( empty( $plan ) || empty( $plan_details ) ) return '<p>' . $options['item_discontinued'] . '</p>';

		// Get the price
		$price = number_format( ( isset( $purchase['discount_price'] ) ? $purchase['discount_price'] : $plan_details['amount'] ), 2 );

		// If the plan is free, display sucessful purchase info
		if ( intval( $price ) === 0 ) {
			return merchant_process_free( $purchase, $plan_details, $options );
		}

		return merchant_submit_payment( $purchase, $plan_details, $options );

	}
	add_shortcode( 'merchant_success', 'merchant_success_page' );


	/**
	 * Send the buyer an email after purchase
	 * @param  array $purchase The purchase info
	 * @param  array $details  The plan details
	 */
	function merchant_checkout_send_buyer_email( $purchase, $details, $email ) {

		// Make sure email content exists
		if ( !is_array( $details ) || !array_key_exists( 'email_1_subject', $details ) || !array_key_exists( 'email_1_content', $details ) ) return;

		// Variables
		$admin_email = sanitize_email( get_option( 'admin_email' ) );
		$name = get_bloginfo( 'name' );
		$domain = merchant_get_site_domain();

		// Setup email
		$to = sanitize_email( $email );
		$headers = array(
			'From: ' . $name . ' <notifications@' . $domain . '>',
			'Sender: ' . $name . ' <' . $admin_email . '>',
			'Reply-To: ' . $name . ' <' . $admin_email . '>',
		);
		$subject = $details['email_1_subject'];
		$message = nl2br( $details['email_1_content'] );

		// Send email
		wp_mail( $to, $subject, $message, $headers );

	}


	/**
	 * Send the seller an email after purchase
	 * @param  array $purchase The purchase info
	 * @param  array $details  The plan details
	 */
	function merchant_checkout_send_admin_email( $purchase, $details, $email ) {

		// Make sure email content exists
		if ( !is_array( $details ) || !array_key_exists( 'email_2_subject', $details ) || !array_key_exists( 'email_2_content', $details ) ) return;

		// Variables
		$admin_email = sanitize_email( get_option( 'admin_email' ) );
		$name = get_bloginfo( 'name' );
		$domain = merchant_get_site_domain();

		// Setup email
		$headers = array(
			'From: ' . $name . ' <notifications@' . $domain . '>',
			'Sender: ' . $name . ' <' . $admin_email . '>',
			'Reply-To: ' . $name . ' <' . $admin_email . '>',
		);
		$subject = $details['email_2_subject'];
		$message = nl2br( str_replace( '{{username}}', $email, $details['email_2_content'] ) );

		// Send email
		wp_mail( $admin_email, $subject, $message, $headers );

	}


	/**
	 * If max volume available is reached, make the plan unavailable for future purchases
	 * @param  array $purchase      The purchase data
	 * @param  array $plan_details  Details about the plan being purchased
	 */
	function merchant_checkout_send_plan_soldout_email( $purchase, $plan_details ) {

		// Check if plan has a max volume
		if ( !array_key_exists( 'max', $plan_details ) || intval( $plan_details['max'] ) === -1 ) return;

		// Get number of times plan has been purchased
		$purchases = get_posts(array(
			'post_type' => 'merchant-purchases',
			'meta_key' => 'merchant_purchase_plan',
			'meta_value' => $purchase['id'],
		));

		// Check if max volume allowed to be sold has been reached
		if ( intval( $plan_details['max'] ) > intval( count( $purchases ) ) ) return;

		// Unpublish the plan
		wp_update_post(array(
			'ID' => $purchase['id'],
			'post_status' => 'draft',
		));

		// Setup email
		$plan = get_post( $purchase['id'] );
		$admin_email = sanitize_email( get_option( 'admin_email' ) );
		$name = get_bloginfo( 'name' );
		$domain = merchant_get_site_domain();
		$headers = array(
			'From: ' . $name . ' <notifications@' . $domain . '>',
			'Sender: ' . $name . ' <' . $admin_email . '>',
			'Reply-To: ' . $name . ' <' . $admin_email . '>',
		);
		$subject = __( 'Sold Out', 'merchant' ) . ': ' . $plan->post_title;
		$message = sprintf( __( '"%s" has sold out. Visit %s to learn more.', 'merchant' ), $plan->post_title, 'http://localhost:8888/merchant/wordpress/wp-admin/post.php?post=' . $purchase['id'] . '&action=edit' );

		// Send email
		wp_mail( $admin_email, $subject, $message, $headers );

	}


	/**
	 * If max volume available is reached, make the plan unavailable for future purchases
	 * @param  array $purchase  The purchase data
	 */
	function merchant_checkout_send_promo_soldout_email( $purchase) {

		// Only run if a promo code was used
		if ( empty( $purchase['discount'] ) ) return;

		// Get promo code
		$promo_details = get_post_meta( $purchase['discount'], 'merchant_promo_details', true );

		// Check if max volume allowed to be used has been reached
		if ( !array_key_exists( 'max', $promo_details ) || intval( $promo_details['max'] ) === -1 ) return;

		// Get number of times promo has been used
		$promos = get_posts(array(
			'post_type' => 'merchant-purchases',
			'meta_key' => 'merchant_purchase_discount',
			'meta_value' => $purchase['discount'],
		));

		// Check if max volume allowed to be used has been reached
		if ( intval( $promo_details['max'] ) > intval( count( $promos ) ) ) return;

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
		$domain = merchant_get_site_domain();
		$headers = array(
			'From: ' . $name . ' <notifications@' . $domain . '>',
			'Sender: ' . $name . ' <' . $admin_email . '>',
			'Reply-To: ' . $name . ' <' . $admin_email . '>',
		);
		$subject = __( 'Promo Code Sold Out', 'merchant' ) . ': ' . strtoupper( $promo->post_title );
		$message = sprintf( __( 'The promo code %s has sold out. Visit %s to learn more.', 'merchant' ), strtoupper( $promo->post_title ), 'http://localhost:8888/merchant/wordpress/wp-admin/post.php?post=' . $purchase['discount'] . '&action=edit' );

		// Send email
		wp_mail( $to, $subject, $message, $headers );

	}


	/**
	 * Send notification email when promo code is used (if applicable)
	 * @param  array $purchase  The purchase data
	 */
	function merchant_checkout_send_promo_code_used_email( $purchase ) {

		// Only run if a promo code was used
		if ( empty( $purchase['discount'] ) ) return;

		// Get promo code
		$promo_details = get_post_meta( $purchase['discount'], 'merchant_promo_details', true );

		// Check if notifications are set
		if ( !array_key_exists( 'notify', $promo_details ) || empty( $promo_details['notify'] ) ) return;

		// Setup email
		$promo = get_post( $purchase['discount'] );
		$admin_email = sanitize_email( get_option( 'admin_email' ) );
		$to = array_map( 'trim', explode( ',', $notify ) );
		$name = get_bloginfo( 'name' );
		$domain = merchant_get_site_domain();
		$headers = array(
			'From: ' . $name . ' <notifications@' . $domain . '>',
			'Sender: ' . $name . ' <' . $admin_email . '>',
			'Reply-To: ' . $name . ' <' . $admin_email . '>',
		);
		$subject = __( 'Promo Code Purchase Notification', 'merchant' ) . ': ' . strtoupper( $promo->post_title );
		$message = sprintf( __( 'The promo code %s was just used for a %s purchase on %s.', 'merchant' ), strtoupper( $promo->post_title ), number_format( $purchase['discount_price'], 2 ), $name );

		// Send email
		wp_mail( $to, $subject, $message, $headers );

	}


	/**
	 * Create a purchase record
	 * @param  array  $purchase  Purchase data
	 * @param  string $email     Buyer email address
	 */
	function merchant_create_purchase_record( $purchase, $plan_details, $email ) {

		// Create post
		$post = wp_insert_post(array(
			'post_content'   => '', // The full text of the post
			'post_title'     => $purchase['id'] . '_' . wp_generate_password( 48, false ), // The title of the post
			'post_status'    => 'publish', // Default 'draft'
			'post_type'      => 'merchant-purchases', // Default 'post'
		));

		// Save extra info to post meta
		if ( $post === 0 ) return;

		// Add metadata
		$details = array(
			'purchaser' => $email,
			'price' => number_format( ( isset( $purchase['discount_price'] ) ? $purchase['discount_price'] : $plan_details['amount'] ), 2 ),
		);
		update_post_meta( $post, 'merchant_purchase_plan', $purchase['id'] );
		update_post_meta( $post, 'merchant_purchase_discount', $purchase['discount'] );
		update_post_meta( $post, 'merchant_purchase_details', $details );

	}


	/**
	 * Submit payment to PayPal
	 * @param  array $purchase      Purchase data
	 * @param  array $plan_details  Plan details
	 * @param  array $options       Plugin options
	 * @return string
	 */
	function merchant_submit_payment( $purchase, $plan_details, $options ) {

		// Variables
		$wpPayPalFramework = wpPayPalFramework::getInstance();
		$referrer = esc_url_raw( merchant_get_url() );
		$token = merchant_get_session( 'merchant_checkout_token' );

		// Get checkout data from PayPal
		$getCheckoutArgs = array(
			'METHOD' => 'GetExpressCheckoutDetails',
			'TOKEN' => $token,
		);
		$checkoutResponse = hashCall( $getCheckoutArgs );

		// Sanity check
		if ( is_wp_error( $checkoutResponse ) || $checkoutResponse['ACK'] !== 'Success' || !array_key_exists( 'PAYERID', $checkoutResponse ) ) return '<div class=" ' . $options['alert_error_class'] . ' ">' . $options['paypal_error'] . '</div>';

		// Sanity check
		if ( empty( $token ) || empty( $checkoutResponse['PAYERID'] ) || !isset( $_GET['PayerID'] ) || empty( $checkoutResponse['PAYMENTREQUEST_0_AMT'] ) ) return '<div class=" ' . $options['alert_error_class'] . ' ">' . $options['paypal_error'] . '</div>';

		// Complete payment
		$doCheckoutArgs = array(
			'METHOD' => 'DoExpressCheckoutPayment',
			'TOKEN' => $token,
			'PAYERID' => $checkoutResponse['PAYERID'],
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'SALE',
			'PAYMENTREQUEST_0_AMT' => $checkoutResponse['PAYMENTREQUEST_0_AMT'],
			'PAYMENTREQUEST_0_CURRENCYCODE' => 'USD',
		);
		$doCheckoutResponse = hashCall( $doCheckoutArgs );

		// If payment completion fails, display error message
		if ( is_wp_error( $doCheckoutResponse ) || $doCheckoutResponse['ACK'] !== 'Success' ) return '<div class=" ' . $options['alert_error_class'] . ' ">' . $options['paypal_error'] . '</div>';

		// Send notification emails
		merchant_checkout_send_buyer_email( $purchase, $plan_details, $checkoutResponse['EMAIL'] );
		merchant_checkout_send_admin_email( $purchase, $plan_details, $checkoutResponse['EMAIL'] );

		// Create purchase record
		merchant_create_purchase_record( $purchase, $plan_details, $checkoutResponse['EMAIL'] );

		// If max volume for plan or promo code reached, make them unavailable for future purchases
		merchant_checkout_send_plan_soldout_email( $purchase, $plan_details );
		merchant_checkout_send_promo_soldout_email( $purchase );

		// If promo code was used and notifiers provided, notify them
		merchant_checkout_send_promo_code_used_email( $purchase );

		// Unset session variables
		merchant_unset_session( 'merchant_checkout_token' );
		merchant_unset_session( 'merchant_purchase_item' );

		// Run custom WordPress action
		do_action( 'merchant_after_paypal_complete', $checkoutResponse['EMAIL'], $purchase['id'] );

		return do_shortcode( wpautop( stripslashes( $plan_details['success'] ), false ) );

	}


	/**
	 * Process "Get Free Access" button
	 * @param  array $purchase      Purchase data
	 * @param  array $plan_details  Plan details
	 * @param  array $options       Plugin options
	 * @return string
	 */
	function merchant_process_free( $purchase, $plan_details, $options ) {

		// Send notification emails
		merchant_checkout_send_buyer_email( $purchase, $plan_details, $purchase['email'] );
		merchant_checkout_send_admin_email( $purchase, $plan_details, $purchase['email'] );

		// Create purchase record
		merchant_create_purchase_record( $purchase, $plan_details, $purchase['email'] );

		// If max volume for plan or promo code reached, make them unavailable for future purchases
		merchant_checkout_send_plan_soldout_email( $purchase, $plan_details );
		merchant_checkout_send_promo_soldout_email( $purchase );

		// If promo code was used and notifiers provided, notify them
		merchant_checkout_send_promo_code_used_email( $purchase );

		// Unset session variables
		merchant_unset_session( 'merchant_checkout_token' );
		merchant_unset_session( 'merchant_purchase_item' );

		// Run custom WordPress action
		do_action( 'merchant_after_free_access_complete', $purchase['email'], $purchase['id'] );

		return do_shortcode( wpautop( stripslashes( $plan_details['success'] ), false ) );

	}