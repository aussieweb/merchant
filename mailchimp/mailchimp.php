<?php


	/**
	 * Add members to MailChimp when they purchase
	 * @param  string $email    The purchaser's email address
	 */
	function merchant_add_new_member_to_mailchimp( $email = null ) {

		// Make sure email is provided
		if ( empty( $email ) ) return;

		// Get MailChimp API variables
		$options = merchant_get_theme_options();

		// Sanity check
		if ( empty( $options['mailchimp_api_key'] ) || empty( $options['mailchimp_list_id'] ) || $options['mailchimp_disable'] === 'on' ) return;

		// Create API call
		$shards = explode( '-', $options['mailchimp_api_key'] );
		$url = 'https://' . $shards[1] . '.api.mailchimp.com/3.0/lists/' . $options['mailchimp_list_id'] . '/members';
		$params = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'mailchimp' . ':' . $options['mailchimp_api_key'] )
			),
			'body' => json_encode(array(
				'status' => 'subscribed',
				'email_address' => $email,
				'interests' => array(
					$options['mailchimp_group_id'] => true,
				),
			)),
		);

		// Add subscriber
		$request = wp_remote_post( $url, $params );
		$response = wp_remote_retrieve_body( $request );
		$data = json_decode( $response, true );

		// If subscriber already exists, update profile
		if ( $data['status'] === 400 && $data['title'] === 'Member Exists' ) {

			$url .= '/' . md5( $email );
			$params = array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( 'mailchimp' . ':' . $options['mailchimp_api_key'] )
				),
				'method' => 'PUT',
				'body' => json_encode(array(
					'interests' => array(
						$options['mailchimp_group_id'] => true,
					),
				)),
			);
			$request = wp_remote_post( $url, $params );
			$response = wp_remote_retrieve_body( $request );
			$data = json_decode( $response, true );

		}

	}
	add_action( 'wpwebapp_after_signup', 'merchant_add_new_member_to_mailchimp', 10, 2 );