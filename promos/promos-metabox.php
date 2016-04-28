<?php

	/**
	 * Create the metabox
	 */
	function beacon_create_promos_metabox() {
		add_meta_box( 'beacon_promos_metabox', 'Promo Code Options', 'beacon_render_promos_metabox', 'beacon-promos', 'normal', 'default');
	}
	add_action( 'add_meta_boxes', 'beacon_create_promos_metabox' );



	/**
	 * Create the metabox default values
	 */
	function beacon_promos_metabox_defaults() {
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
	function beacon_render_promos_metabox() {

		// Variables
		global $post;
		$saved = get_post_meta( $post->ID, 'beacon_promo_details', true );
		$defaults = beacon_promos_metabox_defaults();
		$details = wp_parse_args( $saved, $defaults );
		$summary = get_post_meta( $post->ID, 'beacon_promo_report_summary', true );
		$plans = get_posts(
			array(
				'posts_per_page'   => -1,
				'orderby'          => 'menu_order',
				'order'            => 'DESC',
				'post_type'        => 'beacon-prices',
				'post_status'      => 'any',
			)
		);

		?>

			<fieldset>

				<p><?php _e( '"Limited Supply" shortcode', 'beacon' ); ?>: <code>[beacon_limited_supply id="<?php echo $post->ID; ?>" type="promo" display="count|total|remaining"]</code></p>

				<div>
					<label for="beacon_promo_codes_amount"><?php _e( 'Amount', 'beacon' ) ?></label>
					<input type="number" min="0" steps="any" id="beacon_promo_codes_amount" name="beacon_promo_code[amount]" value="<?php echo esc_attr( $details['amount'] ); ?>">
				</div>
				<br>

				<div>
					<label>
						<input type="radio" name="beacon_promo_code[type]" value="fixed" <?php checked( 'fixed', $details['type'] ); ?>>
						<?php _e( 'Fixed Amount', 'beacon' ) ?>
					</label>
					<br>
					<label>
						<input type="radio" name="beacon_promo_code[type]" value="percentage" <?php checked( 'percentage', $details['type'] ); ?>>
						<?php _e( 'Percentage', 'beacon' ) ?>
					</label>
				</div>
				<br>

				<div>
					<label for="beacon_promo_codes_max"><?php _e( 'Max # of Uses', 'beacon' ) ?> (<?php printf( __( 'use %s for unlimited', 'beacon' ), '<code>-1</code>' ); ?>)</label>
					<input type="number" min="-1" id="beacon_promo_codes_max" name="beacon_promo_code[max]" value="<?php echo esc_attr( $details['max'] ); ?>">
				</div>
				<br>

				<div>
					<label for="beacon_promo_codes_expiration"><?php _e( 'Expiration (leave blank for no expiration)', 'beacon' ) ?></label>
					<input type="date" id="beacon_promo_codes_expiration" name="beacon_promo_code[expiration]" value="<?php echo esc_attr( date( 'Y-m-d', $details['expiration'] ) ); ?>" placeholder="MM/DD/YYYY">
				</div>
				<br>

				<div>
					<label for="beacon_promo_codes_notify"><?php _e( 'Notification Emails (comma separated)', 'beacon' ) ?></label>
					<input type="text" class="large-text" id="beacon_promo_codes_notify" name="beacon_promo_code[notify]" value="<?php echo esc_attr( $details['notify'] ); ?>">
				</div>
				<br>

				<div>
					<strong><?php _e( 'Valid On', 'beacon' ); ?></strong>
					<br>

					<label>
						<input type="checkbox" name="beacon_promo_code[valid_on][all]" value="on" <?php if ( empty( $details['valid_on'] ) ) { echo 'checked="true"'; } ?> <?php if ( array_key_exists( 'all', $details['valid_on'] ) && $details['valid_on']['all'] === 'on' ) { echo 'checked="true"'; } ?>>
						<?php _e( 'All', 'beacon' ); ?>
					</label>
					<br>

					<?php foreach ( $plans as $key => $plan ) : ?>
						<label>
							<input type="checkbox" name="beacon_promo_code[valid_on][<?php echo $plan->ID ?>]" value="on" <?php if ( array_key_exists( $plan->ID, $details['valid_on'] ) && $details['valid_on'][$plan->ID] === 'on' ) { echo 'checked="true"'; } ?>>
							<?php echo $plan->post_title; ?>
						</label>
						<br>
					<?php endforeach; ?>
				</div>
				<br>

				<div>
					<strong><?php _e( 'Times Used', 'beacon' ); ?>:</strong> <?php echo esc_html( is_array( $summary ) && array_key_exists( 'count', $summary ) && !empty( $summary['count'] ) ? $summary['count'] : 0 ); ?>
				</div>
				<br>

				<div>
					<strong><?php _e( 'Total Purchased', 'beacon' ); ?>:</strong> $<?php echo number_format( esc_html( is_array( $summary ) && array_key_exists( 'total', $summary ) && !empty( $summary['total'] ) ? $summary['total'] : 0 ), 2 ); ?>
				</div>
				<br>

			</fieldset>

		<?php

		// Security field
		wp_nonce_field( 'beacon_promos_metabox_nonce', 'beacon_promos_metabox_process' );

	}



	/**
	 * Save the metabox
	 * @param  Number $post_id The post ID
	 * @param  Array  $post    The post data
	 */
	function beacon_save_promos_metabox( $post_id, $post ) {

		if ( !isset( $_POST['beacon_promos_metabox_process'] ) ) return;

		// Verify data came from edit screen
		if ( !wp_verify_nonce( $_POST['beacon_promos_metabox_process'], 'beacon_promos_metabox_nonce' ) ) {
			return $post->ID;
		}

		// Verify user has permission to edit post
		if ( !current_user_can( 'edit_post', $post->ID )) {
			return $post->ID;
		}

		// Check that events details are being passed along
		if ( !isset( $_POST['beacon_promo_code'] ) ) {
			return $post->ID;
		}

		// Sanitize all data
		$sanitized = array();
		foreach ( $_POST['beacon_promo_code'] as $key => $detail ) {

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
		update_post_meta( $post->ID, 'beacon_promo_details', $sanitized );

	}
	add_action('save_post', 'beacon_save_promos_metabox', 1, 2);