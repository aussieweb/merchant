<?php

	/**
	 * "Buy Now" button shortcode
	 * @return string Shortcode markup
	 */
	function beacon_buy_now_button( $atts ) {

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
			'<form class="beacon-buy-now-form" id="beacon-buy-now-form-' . $buynow['id'] . '" name="beacon_buy_now" action="" method="post">' .
				'<input type="hidden" name="beacon_buy_now_id" value="' . $buynow['id'] . '">' .
				wp_nonce_field( 'beacon_buy_now_nonce', 'beacon_buy_now_process' ) .
				'<button class="' . $buynow['class'] . '" ' . $disabled . '>' . $text . '</button>' .
			'</form>';

		return $btn;

	}
	add_shortcode( 'beacon_buy_now', 'beacon_buy_now_button' );


	/**
	 * Process "Buy Now" button
	 */
	function beacon_process_buy_now_button() {

		// Check that form was submitted
		if ( !isset( $_POST['beacon_buy_now_process'] ) ) return;

		// Verify data came from proper screen
		if ( !wp_verify_nonce( $_POST['beacon_buy_now_process'], 'beacon_buy_now_nonce' ) ) {
			die( 'Security check' );
		}

		// Referring URL
		$referrer = beacon_get_url();

		// Sanity check
		if ( !isset( $_POST['beacon_buy_now_id'] ) ) {
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Get plan data
		$plan = get_post( $_POST['beacon_buy_now_id'] );
		$plan_details = get_post_meta( $_POST['beacon_buy_now_id'], 'beacon_pricing_details', true );

		// Check that plan exists
		if ( empty( $plan ) || empty( $plan_details ) ) {
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Check that plan has a price and isn't sold out
		if ( $plan->post_status !== 'publish' || empty( $plan_details['amount'] ) ) {
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Create array
		$purchase = array(
			'id' => $_POST['beacon_buy_now_id'],
			'discount' => null,
			'discount_code' => null,
			'discount_price' => null,
		);

		// Wipe out previous sessions
		beacon_unset_session( 'beacon_checkout_token' );
		beacon_unset_session( 'beacon_checkout_response' );

		// Set session
		beacon_set_session( 'beacon_purchase_course', $purchase );

		// Redirect to checkout
		$options = beacon_get_theme_options();
		wp_safe_redirect( $options['checkout_url'], 302 );
		exit;

	}
	add_action( 'init', 'beacon_process_buy_now_button' );