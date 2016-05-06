<?php

/**
 * Helper Methods
 */


	/**
	 * WP Session Helpers
	 */

	// Set session data
	function merchant_set_session( $name, $value, $sanitize = null ) {

		// Start session
		$wp_session = WP_Session::get_instance();

		// Sanitize data
		if ( $sanitize === 'post' ) {
			$value = wp_filter_post_kses( $value );
		} elseif ( $sanitize === 'nohtml' ) {
			$value = wp_filter_nohtml_kses( $value );
		}

		// Store session value
		$wp_session[$name] = $value;

	}

	// Get session data
	function merchant_get_session( $name, $unset = false ) {

		// Start session
		$wp_session = WP_Session::get_instance();

		// Store session value
		$value = $wp_session[$name];

		// If value is array, transform it
		if ( is_object( $value ) ) {
			$value->toArray();
		}

		// Unset session value
		if ( $unset ) {
			unset( $wp_session[$name] );
		}

		return $value;

	}

	// Unset session data
	function merchant_unset_session( $name ) {
		$wp_session = WP_Session::get_instance();
		unset( $wp_session[$name] );
	}



	/**
	 * URL Helpers
	 * Get, sanitize, and process URLs.
	 */

	// Get and sanitize the current URL
	function merchant_get_url() {
		$url  = @( $_SERVER['HTTPS'] != 'on' ) ? 'http://' . $_SERVER['SERVER_NAME'] :  'https://' . $_SERVER['SERVER_NAME'];
		$url .= ( $_SERVER['SERVER_PORT'] !== 80 ) ? ":" . $_SERVER['SERVER_PORT'] : '';
		$url .= $_SERVER['REQUEST_URI'];
		return $url;
	}

	// Get the site domain and remove the www.
	function merchant_get_site_domain( $url = null ) {
		$sitename = strtolower( $_SERVER['SERVER_NAME'] );
		if ( substr( $sitename, 0, 4 ) == 'www.' ) {
			$sitename = substr( $sitename, 4 );
		}
		return $sitename;
	}

	// Prepare URL for status string
	function merchant_prepare_url( $url ) {

		// If URL has a '?', add an '&'.
		// Otherwise, add a '?'.
		$url_status = strpos($url, '?');
		if ( $url_status === false ) {
			$concate = '?';
		}
		else {
			$concate = '&';
		}

		return $url . $concate;

	}


	// Remove a $_GET variable from the URL
	function merchant_clean_url( $variable, $url ) {
		$new_url = preg_replace('/(?:&|(\?))' . $variable . '=[^&]*(?(1)&|)?/i', '$1', $url);
		$last_char = substr( $new_url, -1 );
		if ( $last_char == '?' ) {
			$new_url = substr($new_url, 0, -1);
		}
		return $new_url;
	}



	/**
	 * JetPack Markdown
	 */

	/**
	 * Convert markdown to HTML using Jetpack
	 * @param  string $content Markdown content
	 * @return string          Converted content
	 */
	function merchant_process_jetpack_markdown( $content ) {

		// If markdown class is defined, convert content
		if ( class_exists( 'WPCom_Markdown' ) ) {

			// Get markdown library
			jetpack_require_lib( 'markdown' );

			// Return converted content
			return WPCom_Markdown::get_instance()->transform( $content );

		}

		// Else, return content
		return $content;

	}



	/**
	 * Get saved markdown content if it exists and Jetpack is active. Otherwise, get HTML.
	 * @param  array  $options  Array with HTML and markdown content
	 * @param  string $name     The name of the content
	 * @param  string $suffix   The suffix to denote the markdown version of the content
	 * @return string           The content
	 */
	function merchant_get_jetpack_markdown( $options, $name, $suffix = '_markdown' ) {

		// If markdown class is defined, get markdown content
		if ( class_exists( 'WPCom_Markdown' ) && array_key_exists( $name . $suffix, $options ) && !empty( $options[$name . $suffix] ) ) {
			return $options[$name . $suffix];
		}

		// Else, return HTML
		return $options[$name];

	}