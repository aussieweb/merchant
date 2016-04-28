<?php

	/**
	 * Create the metabox
	 */
	function beacon_create_prices_metabox() {
		add_meta_box( 'beacon_prices_metabox', 'Pricing Plan Options', 'beacon_render_prices_metabox', 'beacon-prices', 'normal', 'default');
	}
	add_action( 'add_meta_boxes', 'beacon_create_prices_metabox' );



	/**
	 * Create the metabox default values
	 */
	function beacon_prices_metabox_defaults() {
		return array(
			'amount' => null,
			'description' => '',
			'description_markdown' => '',
			'redirect_url' => site_url(),
			'email_1_subject' => '',
			'email_1_content' => '',
			'email_2_subject' => '',
			'email_2_content' => '',
			'submit_payment_message' => '',
			'submit_free_message' => '',
			'max' => -1,
			'multiple_purchases' => 'off',
			'count' => 0,
			'total' => 0,
		);
	}



	/**
	 * Render the metabox
	 */
	function beacon_render_prices_metabox() {

		// Variables
		global $post;
		$saved = get_post_meta( $post->ID, 'beacon_pricing_details', true );
		$defaults = beacon_prices_metabox_defaults();
		$details = wp_parse_args( $saved, $defaults );
		$summary = get_post_meta( $post->ID, 'beacon_pricing_report_summary', true );

		?>

			<fieldset>

				<p><?php _e( '"Buy Now" button shortcode', 'beacon' ); ?>: <code>[beacon_buy_now id="<?php echo $post->ID; ?>"]</code></p>

				<p><?php _e( '"Limited Supply" shortcode', 'beacon' ); ?>: <code>[beacon_limited_supply id="<?php echo $post->ID; ?>" display="count|total|remaining"]</code></p>

				<div>
					<label for="beacon_price_amount"><?php _e( 'Price', 'beacon' ) ?></label>
					<input type="number" min="0" steps="any" id="beacon_price_amount" name="beacon_price[amount]" value="<?php echo esc_attr( $details['amount'] ); ?>">
				</div>
				<br>

				<div>
					<label for="beacon_price_description"><?php _e( 'Description of the plan (displayed at checkout)', 'beacon' ); ?></label>
					<textarea class="large-text" id="beacon_price_description" name="beacon_price[description]" cols="50" rows="10"><?php echo stripslashes( esc_textarea( beacon_get_jetpack_markdown( $details, 'description' ) ) ); ?></textarea>
				</div>
				<br>

				<div>
					<label for="beacon_price_redirect_url"><?php _e( 'Redirect URL', 'beacon' ) ?></label>
					<input type="url" class="large-text" id="beacon_price_redirect_url" name="beacon_price[redirect_url]" value="<?php echo esc_url( $details['redirect_url'] ); ?>">
				</div>
				<br>

				<div>
					<label for="beacon_price_email_1_subject"><?php _e( 'Email to Buyer: Subject Line [optional]', 'beacon' ); ?></label>
					<input type="text" class="large-text" id="beacon_price_email_1_subject" name="beacon_price[email_1_subject]" value="<?php echo esc_attr( $details['email_1_subject'] ); ?>">
				</div>
				<br>

				<div>
					<label for="beacon_price_email_1_content"><?php _e( 'Email to Buyer: Content [optional]', 'beacon' ); ?></label>
					<textarea class="large-text" id="beacon_price_email_1_content" name="beacon_price[email_1_content]" cols="50" rows="10"><?php echo stripslashes( esc_textarea( $details['email_1_content'] ) ); ?></textarea>
				</div>
				<br>

				<div>
					<label for="beacon_price_email_2_subject"><?php _e( 'Email to Seller: Subject Line [optional]', 'beacon' ); ?></label>
					<input type="text" class="large-text" id="beacon_price_email_2_subject" name="beacon_price[email_2_subject]" value="<?php echo esc_attr( $details['email_2_subject'] ); ?>">
				</div>
				<br>

				<div>
					<label for="beacon_price_email_2_content"><?php _e( 'Email to Seller: Content [optional]', 'beacon' ); ?></label>
					<textarea class="large-text" id="beacon_price_email_2_content" name="beacon_price[email_2_content]" cols="50" rows="10"><?php echo stripslashes( esc_textarea( $details['email_2_content'] ) ); ?></textarea>
				</div>
				<br>

				<div>
					<label for="beacon_price_submit_payment_message"><?php _e( 'Submit Payment Message [optional]', 'beacon' ); ?></label>
					<textarea class="large-text" id="beacon_price_submit_payment_message" name="beacon_price[submit_payment_message]" cols="50" rows="4"><?php echo stripslashes( esc_textarea( $details['submit_payment_message'] ) ); ?></textarea>
				</div>
				<br>

				<div>
					<label for="beacon_price_submit_free_message"><?php _e( 'Confirm Free Access Message [optional]', 'beacon' ); ?></label>
					<textarea class="large-text" id="beacon_price_submit_free_message" name="beacon_price[submit_free_message]" cols="50" rows="4"><?php echo stripslashes( esc_textarea( $details['submit_free_message'] ) ); ?></textarea>
				</div>
				<br>

				<div>
					<label for="beacon_price_max"><?php _e( 'Max # of Purchases', 'beacon' ) ?> (<?php printf( __( 'use %s for unlimited', 'beacon' ), '<code>-1</code>' ); ?>)</label>
					<input type="number" min="-1" id="beacon_price_max" name="beacon_price[max]" value="<?php echo esc_attr( $details['max'] ); ?>">
				</div>
				<br>

				<div>
					<label>
						<input type="checkbox" id="beacon_price_mutiple_purchases" name="beacon_price[multiple_purchases]" value="on" <?php checked( 'on', $details['multiple_purchases'] ); ?>>
						<?php _e( 'Allow multiple purchases', 'beacon' ) ?>
					</label>
				</div>
				<br>

				<div>
					<strong><?php _e( 'Times Purchased', 'beacon' ); ?>:</strong> <?php echo esc_html( is_array( $summary ) && array_key_exists( 'count', $summary ) && !empty( $summary['count'] ) ? $summary['count'] : 0 ); ?>
				</div>
				<br>

				<div>
					<strong><?php _e( 'Total Purchased', 'beacon' ); ?>:</strong> $<?php echo number_format( esc_html( is_array( $summary ) && array_key_exists( 'total', $summary ) && !empty( $summary['total'] ) ? $summary['total'] : 0 ), 2 ); ?>
				</div>
				<br>

			</fieldset>

		<?php

		// Security field
		wp_nonce_field( 'beacon_prices_metabox_nonce', 'beacon_prices_metabox_process' );

	}



	/**
	 * Save the metabox
	 * @param  Number $post_id The post ID
	 * @param  Array  $post    The post data
	 */
	function beacon_save_prices_metabox( $post_id, $post ) {

		if ( !isset( $_POST['beacon_prices_metabox_process'] ) ) return;

		// Verify data came from edit screen
		if ( !wp_verify_nonce( $_POST['beacon_prices_metabox_process'], 'beacon_prices_metabox_nonce' ) ) {
			return $post->ID;
		}

		// Verify user has permission to edit post
		if ( !current_user_can( 'edit_post', $post->ID )) {
			return $post->ID;
		}

		// Check that events details are being passed along
		if ( !isset( $_POST['beacon_price'] ) ) {
			return $post->ID;
		}

		// Sanitize all data
		$sanitized = array();
		foreach ( $_POST['beacon_price'] as $key => $detail ) {
			if ( $key === 'description' ) {
				$sanitized['description'] = beacon_process_jetpack_markdown( $detail );
				$sanitized['description_markdown'] = wp_filter_post_kses( $detail );
				continue;
			}
			$sanitized[$key] = wp_filter_post_kses( $detail );
		}

		// Update data in database
		update_post_meta( $post->ID, 'beacon_pricing_details', $sanitized );

	}
	add_action('save_post', 'beacon_save_prices_metabox', 1, 2);