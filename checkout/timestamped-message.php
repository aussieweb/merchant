<?php

	/**
	 * Timestamped message shortcode
	 * @return string Shortcode markup
	 */
	function merchant_timestamped_message( $atts, $content = '' ) {

		// Prevent this content from caching
		if ( !defined( 'DONOTCACHEPAGE' ) ) {
			define('DONOTCACHEPAGE', TRUE);
		}

		// If no content, bail
		if ( empty( $content ) ) return;

		// Check that timestamp exists
		if ( !isset( $_GET['merchant_success'] ) ) return;

		// Check that timestamp is valid
		$timestamp = merchant_get_session( 'merchant_timestamp_' . $_GET['merchant_success'] );
		if ( empty( $timestamp ) ) return;

		return wpautop( $content );

	}
	add_shortcode( 'merchant_timestamp', 'merchant_timestamped_message' );