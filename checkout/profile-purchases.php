<?php


	/**
	 * Display Beacon course purchases in profile
	 * @param  array $user User data
	 */
	function beacon_add_purchases_to_user_profile( $user ) {

		// Check user capabilities first
		if ( !current_user_can( 'edit_users', $user->ID ) ) return false;

		// Variables
		$purchases = get_the_author_meta( 'beacon_purchases', $user->ID );
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

		<h3>Beacon Purchases</h3>

		<table class="form-table">

			<label>
				<input type="checkbox" name="beacon_plan_purchased_modify" data-beacon-purchases-toggle="[data-beacon-purchases]" disabled>
				<?php _e( 'Modify user\'s purchases', 'beacon' ); ?>
			</label>

		<?php foreach ( $plans as $plan ) : ?>

			<tr>
				<th><?php echo $plan->post_title; ?></th>

				<td>
					<label>
						<input type="checkbox" name="beacon_plan_purchased[<?php echo $plan->ID; ?>]" value="on" <?php if ( is_array( $purchases ) && array_key_exists( $plan->ID, $purchases ) && $purchases[$plan->ID] === 'on' ) { echo 'checked="true"'; } ?> disabled data-beacon-purchases>
						<?php printf( __( 'Purchased %s', 'beacon' ), '<em>' . $plan->post_title . '</em>' ); ?>
					</label>
				</td>
			</tr>

		<?php endforeach; ?>

			<script>
				// Remove disabled from user purchases
				;(function (window, document, undefined) {
					'use strict';
					var toggle = document.querySelector( '[data-beacon-purchases-toggle]' );
					if ( !toggle ) return;
					toggle.addEventListener('click', function (event) {
						var purchases = document.querySelectorAll( toggle.getAttribute( 'data-beacon-purchases-toggle' ) );
						var disable = toggle.checked ? true : false;
						for ( var i = 0, len = purchases.length; i < len; i++ ) {
							if ( disable ) {
								purchases[i].disabled = false;
								continue;
							}
							purchases[i].disabled = true;
						}
					}, false);
				})(window, document);
			</script>

		</table>

		<?php
	}
	add_action( 'show_user_profile', 'beacon_add_purchases_to_user_profile' );
	add_action( 'edit_user_profile', 'beacon_add_purchases_to_user_profile' );


	function beacon_save_purchases_in_user_profile( $user_id ) {

		// Check user capabilities first
		if ( !current_user_can( 'edit_users', $user_id ) ) return false;

		// Sanity check
		if ( !isset( $_POST['beacon_plan_purchased_modify'] ) ) return;

		$purchases = array();
		if ( isset( $_POST['beacon_plan_purchased'] ) ) {
			foreach ( $_POST['beacon_plan_purchased'] as $key => $plan ) {
				$purchases[$key] = 'on';
			}
		}
		update_user_meta( $user_id, 'beacon_purchases', $purchases );

	}
	add_action( 'personal_options_update', 'beacon_save_purchases_in_user_profile' );
	add_action( 'edit_user_profile_update', 'beacon_save_purchases_in_user_profile' );