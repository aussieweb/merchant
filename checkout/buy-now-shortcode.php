<?php

	/**
	 * "Buy Now" button shortcode
	 * @return string Shortcode markup
	 */
	function merchant_buy_now_button( $atts ) {

		// Prevent this content from caching
		if ( !defined( 'DONOTCACHEPAGE' ) ) {
			define('DONOTCACHEPAGE', TRUE);
		}

		// Get shortcode atts
		$buynow = shortcode_atts( array(
			'id' => null,
			'class' => 'btn',
			'label' => 'Buy Now',
			'soldout' => 'Sold Out',
		), $atts );

		// Verify that access plan ID has been provided
		if ( is_null( $buynow['id'] ) || $buynow['id'] === '' ) return;

		// Get plan
		$plan = get_post( $buynow['id'] );
		if ( empty( $plan ) ) return;

		// Setup button
		$disabled = $plan->post_status === 'publish' ? '' : 'disabled';
		$text = empty( $disabled ) ? $buynow['label'] : $buynow['soldout'];
		$btn =
			'<form class="merchant-buy-now-form" id="merchant-buy-now-form-' . $buynow['id'] . '" name="merchant_buy_now" action="" method="post">' .
				'<input type="hidden" name="merchant_buy_now_id" value="' . $buynow['id'] . '">' .
				wp_nonce_field( 'merchant_buy_now_nonce', 'merchant_buy_now_process' ) .
				'<button class="' . $buynow['class'] . '" ' . $disabled . '>' . $text . '</button>' .
			'</form>';

		return $btn;

	}
	add_shortcode( 'merchant_buy_now', 'merchant_buy_now_button' );


	/**
	 * Process "Buy Now" button
	 */
	function merchant_process_buy_now_button() {

		// Check that form was submitted
		if ( !isset( $_POST['merchant_buy_now_process'] ) ) return;

		// Verify data came from proper screen
		if ( !wp_verify_nonce( $_POST['merchant_buy_now_process'], 'merchant_buy_now_nonce' ) ) {
			die( 'Security check' );
		}

		// Referring URL
		$referrer = merchant_get_url();

		// Sanity check
		if ( !isset( $_POST['merchant_buy_now_id'] ) ) {
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Get plan data
		$plan = get_post( $_POST['merchant_buy_now_id'] );
		$plan_details = get_post_meta( $_POST['merchant_buy_now_id'], 'merchant_pricing_details', true );

		// Check that plan exists
		if ( empty( $plan ) || empty( $plan_details ) ) {
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Check that plan has a price and isn't sold out
		if ( $plan->post_status !== 'publish' ||is_null( $plan_details['amount'] ) || $plan_details['amount'] === '' ) {
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Create array
		$purchase = array(
			'id' => $_POST['merchant_buy_now_id'],
			'discount' => null,
			'discount_code' => null,
			'discount_price' => null,
			'email' => null,
		);

		// Wipe out previous sessions
		merchant_unset_session( 'merchant_checkout_token' );
		merchant_unset_session( 'merchant_checkout_response' );

		// Set session
		merchant_set_session( 'merchant_purchase_item', $purchase );

		// Redirect to checkout
		$options = merchant_get_theme_options();
		wp_safe_redirect( $options['checkout_url'], 302 );
		exit;

	}
	add_action( 'init', 'merchant_process_buy_now_button' );