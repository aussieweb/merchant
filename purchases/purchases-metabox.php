<?php

	/**
	 * Create the metabox
	 */
	function merchant_create_prices_metabox() {
		add_meta_box( 'merchant_prices_metabox', 'Pricing Plan Options', 'merchant_render_prices_metabox', 'merchant-prices', 'normal', 'default');
	}
	add_action( 'add_meta_boxes', 'merchant_create_prices_metabox' );



	/**
	 * Create the metabox default values
	 */
	function merchant_prices_metabox_defaults() {
		return array(
			'amount' => null,
			'description' => '',
			'description_markdown' => '',
			'redirect_url' => site_url(),
			'success' => '',
			'success_markdown' => '',
			'email_1_subject' => '',
			'email_1_content' => '',
			'email_2_subject' => '',
			'email_2_content' => '',
			'submit_payment_message' => '',
			'submit_free_message' => '',
			'max' => -1,
			'count' => 0,
			'total' => 0,
		);
	}



	/**
	 * Render the metabox
	 */
	function merchant_render_prices_metabox() {

		// Variables
		global $post;
		$saved = get_post_meta( $post->ID, 'merchant_pricing_details', true );
		$defaults = merchant_prices_metabox_defaults();
		$details = wp_parse_args( $saved, $defaults );
		$summary = get_post_meta( $post->ID, 'merchant_pricing_report_summary', true );

		?>

			<fieldset>

				<p><?php _e( '"Buy Now" button shortcode', 'merchant' ); ?>: <code>[merchant_buy_now id="<?php echo $post->ID; ?>"]</code></p>

				<p><?php _e( '"Limited Supply" shortcode', 'merchant' ); ?>: <code>[merchant_limited_supply id="<?php echo $post->ID; ?>" display="count|total|remaining"]</code></p>

				<div>
					<label for="merchant_price_amount"><?php _e( 'Price', 'merchant' ) ?></label>
					<input type="number" min="0" steps="any" id="merchant_price_amount" name="merchant_price[amount]" value="<?php echo esc_attr( $details['amount'] ); ?>">
				</div>
				<br>

				<div>
					<label for="merchant_price_description"><?php _e( 'Description of the plan (displayed at checkout)', 'merchant' ); ?></label>
					<textarea class="large-text" id="merchant_price_description" name="merchant_price[description]" cols="50" rows="10"><?php echo stripslashes( esc_textarea( merchant_get_jetpack_markdown( $details, 'description' ) ) ); ?></textarea>
				</div>
				<br>

				<div>
					<label for="merchant_price_redirect_url"><?php _e( 'Redirect URL', 'merchant' ) ?></label>
					<input type="url" class="large-text" id="merchant_price_redirect_url" name="merchant_price[redirect_url]" value="<?php echo esc_url( $details['redirect_url'] ); ?>">
				</div>
				<br>

				<div>
					<label for="merchant_price_success"><?php _e( 'Successful Purchase Message', 'merchant' ); ?></label>
					<textarea class="large-text" id="merchant_price_success" name="merchant_price[success]" cols="50" rows="10"><?php echo stripslashes( esc_textarea( merchant_get_jetpack_markdown( $details, 'success' ) ) ); ?></textarea>
				</div>
				<br>

				<div>
					<label for="merchant_price_email_1_subject"><?php _e( 'Email to Buyer: Subject Line [optional]', 'merchant' ); ?></label>
					<input type="text" class="large-text" id="merchant_price_email_1_subject" name="merchant_price[email_1_subject]" value="<?php echo esc_attr( $details['email_1_subject'] ); ?>">
				</div>
				<br>

				<div>
					<label for="merchant_price_email_1_content"><?php _e( 'Email to Buyer: Content [optional]', 'merchant' ); ?></label>
					<textarea class="large-text" id="merchant_price_email_1_content" name="merchant_price[email_1_content]" cols="50" rows="10"><?php echo stripslashes( esc_textarea( $details['email_1_content'] ) ); ?></textarea>
				</div>
				<br>

				<div>
					<label for="merchant_price_email_2_subject"><?php _e( 'Email to Seller: Subject Line [optional]', 'merchant' ); ?></label>
					<input type="text" class="large-text" id="merchant_price_email_2_subject" name="merchant_price[email_2_subject]" value="<?php echo esc_attr( $details['email_2_subject'] ); ?>">
				</div>
				<br>

				<div>
					<label for="merchant_price_email_2_content"><?php _e( 'Email to Seller: Content [optional]', 'merchant' ); ?></label>
					<textarea class="large-text" id="merchant_price_email_2_content" name="merchant_price[email_2_content]" cols="50" rows="10"><?php echo stripslashes( esc_textarea( $details['email_2_content'] ) ); ?></textarea>
				</div>
				<br>

				<div>
					<label for="merchant_price_submit_payment_message"><?php _e( 'Submit Payment Message [optional]', 'merchant' ); ?></label>
					<textarea class="large-text" id="merchant_price_submit_payment_message" name="merchant_price[submit_payment_message]" cols="50" rows="4"><?php echo stripslashes( esc_textarea( $details['submit_payment_message'] ) ); ?></textarea>
				</div>
				<br>

				<div>
					<label for="merchant_price_submit_free_message"><?php _e( 'Confirm Free Access Message [optional]', 'merchant' ); ?></label>
					<textarea class="large-text" id="merchant_price_submit_free_message" name="merchant_price[submit_free_message]" cols="50" rows="4"><?php echo stripslashes( esc_textarea( $details['submit_free_message'] ) ); ?></textarea>
				</div>
				<br>

				<div>
					<label for="merchant_price_max"><?php _e( 'Max # of Purchases', 'merchant' ) ?> (<?php printf( __( 'use %s for unlimited', 'merchant' ), '<code>-1</code>' ); ?>)</label>
					<input type="number" min="-1" id="merchant_price_max" name="merchant_price[max]" value="<?php echo esc_attr( $details['max'] ); ?>">
				</div>
				<br>

				<div>
					<strong><?php _e( 'Times Purchased', 'merchant' ); ?>:</strong> <?php echo esc_html( is_array( $summary ) && array_key_exists( 'count', $summary ) && !empty( $summary['count'] ) ? $summary['count'] : 0 ); ?>
				</div>
				<br>

				<div>
					<strong><?php _e( 'Total Purchased', 'merchant' ); ?>:</strong> $<?php echo number_format( esc_html( is_array( $summary ) && array_key_exists( 'total', $summary ) && !empty( $summary['total'] ) ? $summary['total'] : 0 ), 2 ); ?>
				</div>
				<br>

			</fieldset>

		<?php

		// Security field
		wp_nonce_field( 'merchant_prices_metabox_nonce', 'merchant_prices_metabox_process' );

	}



	/**
	 * Save the metabox
	 * @param  Number $post_id The post ID
	 * @param  Array  $post    The post data
	 */
	function merchant_save_prices_metabox( $post_id, $post ) {

		if ( !isset( $_POST['merchant_prices_metabox_process'] ) ) return;

		// Verify data came from edit screen
		if ( !wp_verify_nonce( $_POST['merchant_prices_metabox_process'], 'merchant_prices_metabox_nonce' ) ) {
			return $post->ID;
		}

		// Verify user has permission to edit post
		if ( !current_user_can( 'edit_post', $post->ID )) {
			return $post->ID;
		}

		// Check that events details are being passed along
		if ( !isset( $_POST['merchant_price'] ) ) {
			return $post->ID;
		}

		// Sanitize all data
		$sanitized = array();
		foreach ( $_POST['merchant_price'] as $key => $detail ) {
			if ( $key === 'description' ) {
				$sanitized['description'] = merchant_process_jetpack_markdown( $detail );
				$sanitized['description_markdown'] = wp_filter_post_kses( $detail );
				continue;
			}
			if ( $key === 'success' ) {
				$sanitized['success'] = merchant_process_jetpack_markdown( $detail );
				$sanitized['success_markdown'] = wp_filter_post_kses( $detail );
				continue;
			}
			$sanitized[$key] = wp_filter_post_kses( $detail );
		}

		// Update data in database
		update_post_meta( $post->ID, 'merchant_pricing_details', $sanitized );

	}
	add_action('save_post', 'merchant_save_prices_metabox', 1, 2);