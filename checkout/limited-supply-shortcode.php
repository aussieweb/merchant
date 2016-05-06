<?php

	/**
	 * "Buy Now" button shortcode
	 * @return string Shortcode markup
	 */
	function merchant_display_supply_count( $atts ) {

		// Prevent this content from caching
		if ( !defined( 'DONOTCACHEPAGE' ) ) {
			define('DONOTCACHEPAGE', TRUE);
		}

		// Get shortcode atts
		$supply = shortcode_atts( array(
			'id' => null,
			'display' => null,
			'type' => 'pricing',
		), $atts );

		// Verify that access plan ID has been provided
		if ( is_null( $supply['id'] ) || $supply['id'] === '' || empty( $supply['display'] ) ) return;

		// Get plan
		$plan = get_post_meta( $supply['id'], 'merchant_' . $supply['type'] . '_details', true );
		if ( empty( $plan ) ) return;

		if ( $supply['display'] === 'count' ) {
			return $plan['count'];
		}

		if ( $supply['display'] === 'total' ) {
			return $plan['max'];
		}

		if ( $supply['display'] === 'remaining' ) {
			return $plan['max'] - $plan['count'];
		}

	}
	add_shortcode( 'merchant_limited_supply', 'merchant_display_supply_count' );