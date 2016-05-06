<?php

	/**
	 * Create the metabox
	 */
	function merchant_create_promos_metabox() {
		add_meta_box( 'merchant_promos_metabox', 'Promo Code Options', 'merchant_render_promos_metabox', 'merchant-promos', 'normal', 'default');
	}
	add_action( 'add_meta_boxes', 'merchant_create_promos_metabox' );



	/**
	 * Create the metabox default values
	 */
	function merchant_promos_metabox_defaults() {
		return array(
			'amount' => 0,
			'type' => 'fixed',
			'max' => -1,
			'expiration' => '',
			'notify' => '',
			'count' => 0,
			'total' => 0,
			'valid_on' => array(),
		);
	}



	/**
	 * Render the metabox
	 */
	function merchant_render_promos_metabox() {

		// Variables
		global $post;
		$saved = get_post_meta( $post->ID, 'merchant_promo_details', true );
		$defaults = merchant_promos_metabox_defaults();
		$details = wp_parse_args( $saved, $defaults );
		$plans = get_posts(
			array(
				'posts_per_page'   => -1,
				'orderby'          => 'menu_order',
				'order'            => 'DESC',
				'post_type'        => 'merchant-prices',
				'post_status'      => 'any',
			)
		);

		?>

			<fieldset>

				<p><?php _e( '"Limited Supply" shortcode', 'merchant' ); ?>: <code>[merchant_limited_supply id="<?php echo $post->ID; ?>" type="promo" display="count|total|remaining"]</code></p>

				<div>
					<label for="merchant_promo_codes_amount"><?php _e( 'Amount', 'merchant' ) ?></label>
					<input type="number" min="0" steps="any" id="merchant_promo_codes_amount" name="merchant_promo_code[amount]" value="<?php echo esc_attr( $details['amount'] ); ?>">
				</div>
				<br>

				<div>
					<label>
						<input type="radio" name="merchant_promo_code[type]" value="fixed" <?php checked( 'fixed', $details['type'] ); ?>>
						<?php _e( 'Fixed Amount', 'merchant' ) ?>
					</label>
					<br>
					<label>
						<input type="radio" name="merchant_promo_code[type]" value="percentage" <?php checked( 'percentage', $details['type'] ); ?>>
						<?php _e( 'Percentage', 'merchant' ) ?>
					</label>
				</div>
				<br>

				<div>
					<label for="merchant_promo_codes_max"><?php _e( 'Max # of Uses', 'merchant' ) ?> (<?php printf( __( 'use %s for unlimited', 'merchant' ), '<code>-1</code>' ); ?>)</label>
					<input type="number" min="-1" id="merchant_promo_codes_max" name="merchant_promo_code[max]" value="<?php echo esc_attr( $details['max'] ); ?>">
				</div>
				<br>

				<div>
					<label for="merchant_promo_codes_expiration"><?php _e( 'Expiration (leave blank for no expiration)', 'merchant' ) ?></label>
					<input type="date" id="merchant_promo_codes_expiration" name="merchant_promo_code[expiration]" value="<?php echo esc_attr( date( 'Y-m-d', $details['expiration'] ) ); ?>" placeholder="MM/DD/YYYY">
				</div>
				<br>

				<div>
					<label for="merchant_promo_codes_notify"><?php _e( 'Notification Emails (comma separated)', 'merchant' ) ?></label>
					<input type="text" class="large-text" id="merchant_promo_codes_notify" name="merchant_promo_code[notify]" value="<?php echo esc_attr( $details['notify'] ); ?>">
				</div>
				<br>

				<div>
					<strong><?php _e( 'Valid On', 'merchant' ); ?></strong>
					<br>

					<label>
						<input type="checkbox" name="merchant_promo_code[valid_on][all]" value="on" <?php if ( empty( $details['valid_on'] ) ) { echo 'checked="true"'; } ?> <?php if ( array_key_exists( 'all', $details['valid_on'] ) && $details['valid_on']['all'] === 'on' ) { echo 'checked="true"'; } ?>>
						<?php _e( 'All', 'merchant' ); ?>
					</label>
					<br>

					<?php foreach ( $plans as $key => $plan ) : ?>
						<label>
							<input type="checkbox" name="merchant_promo_code[valid_on][<?php echo $plan->ID ?>]" value="on" <?php if ( array_key_exists( $plan->ID, $details['valid_on'] ) && $details['valid_on'][$plan->ID] === 'on' ) { echo 'checked="true"'; } ?>>
							<?php echo $plan->post_title; ?>
						</label>
						<br>
					<?php endforeach; ?>
				</div>
				<br>

			</fieldset>

		<?php

		// Security field
		wp_nonce_field( 'merchant_promos_metabox_nonce', 'merchant_promos_metabox_process' );

	}



	/**
	 * Save the metabox
	 * @param  Number $post_id The post ID
	 * @param  Array  $post    The post data
	 */
	function merchant_save_promos_metabox( $post_id, $post ) {

		if ( !isset( $_POST['merchant_promos_metabox_process'] ) ) return;

		// Verify data came from edit screen
		if ( !wp_verify_nonce( $_POST['merchant_promos_metabox_process'], 'merchant_promos_metabox_nonce' ) ) {
			return $post->ID;
		}

		// Verify user has permission to edit post
		if ( !current_user_can( 'edit_post', $post->ID )) {
			return $post->ID;
		}

		// Check that events details are being passed along
		if ( !isset( $_POST['merchant_promo_code'] ) ) {
			return $post->ID;
		}

		// Sanitize all data
		$sanitized = array();
		foreach ( $_POST['merchant_promo_code'] as $key => $detail ) {

			// Expiration date
			if ( $key === 'expiration' ) {
				$sanitized[$key] = wp_filter_post_kses( strtotime( $detail ) );
				continue;
			}

			// Valid plans
			if ( $key === 'valid_on' ) {
				$valid_on = array();
				foreach ( $detail as $plan_id => $plan ) {
					$valid_on[$plan_id] = 'on';
				}
				$sanitized['valid_on'] = $valid_on;
				continue;
			}

			// Everything else
			$sanitized[$key] = wp_filter_post_kses( $detail );

		}

		// Update data in database
		update_post_meta( $post->ID, 'merchant_promo_details', $sanitized );

	}
	add_action('save_post', 'merchant_save_promos_metabox', 1, 2);