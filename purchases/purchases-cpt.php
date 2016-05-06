<?php

	/**
	 * Add custom post type
	 */
	function merchant_add_purchases_custom_post_type() {

		$labels = array(
			'name'               => _x( 'Purchases', 'post type general name', 'merchant' ),
			'singular_name'      => _x( 'Purchase', 'post type singular name', 'merchant' ),
			'add_new'            => _x( 'Add New', 'merchant-prices', 'merchant' ),
			'add_new_item'       => __( 'Add New Purchase', 'merchant' ),
			'edit_item'          => __( 'Edit Purchase', 'merchant' ),
			'new_item'           => __( 'New Purchase', 'merchant' ),
			'all_items'          => __( 'All Purchases', 'merchant' ),
			'view_item'          => __( 'View Purchase', 'merchant' ),
			'search_items'       => __( 'Search Purchases', 'merchant' ),
			'not_found'          => __( 'No purchases found', 'merchant' ),
			'not_found_in_trash' => __( 'No purchases found in the Trash', 'merchant' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Purchases', 'merchant' ),
		);
		$args = array(
			'labels'              => $labels,
			'description'         => 'Holds our purchases and purchase-specific data',
			'public'              => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_in_nav_menus'   => false,
			// 'menu_position' => 5,
			'menu_icon'           => 'dashicons-chart-area',
			'hierarchical'        => false,
			// 'supports'            => array(
			// 	'title',
			// 	'editor',
			// 	'thumbnail',
			// 	'excerpt',
			// 	'revisions',
			// 	'page-attributes',
			// ),
			'supports'            => false,
			'has_archive'         => false,
			// 'rewrite' => array(
			// 	'slug' => 'courses',
			// ),
			'map_meta_cap'        => true,
			'capabilities'        => array(
				'create_posts'           => false,
				// 'edit_published_posts'   => false,
				// 'delete_posts'           => true,
				// 'delete_published_posts' => true,
			)
		);
		register_post_type( 'merchant-purchases', $args );
	}
	add_action( 'init', 'merchant_add_purchases_custom_post_type' );


	/**
	 * Update purchases overview columns
	 * @param  array $existing_columns  Existing columns
	 */
	function merchant_purchases_add_columns( $existing_columns ) {
		$columns = array();
		$columns['cb'] = '<input type="checkbox" />';
		$columns['title'] = _x( 'ID', 'merchant' );
		$columns['purchase_date'] = _x( 'Date', 'merchant' );
		$columns['plan'] = _x( 'Plan', 'merchant' );
		$columns['purchaser'] = _x( 'Purchaser', 'merchant' );
		$columns['price'] = _x( 'Price', 'merchant' );
		$columns['discount'] = _x( 'Promo Code', 'merchant' );
		return $columns;
	}
	add_action( 'manage_edit-merchant-purchases_columns', 'merchant_purchases_add_columns', 10, 2 );


	/**
	 * Add purchase overview data
	 * @param  string  $column   Column label
	 * @param  string  $post_id  The post ID
	 */
	function merchant_purchases_add_column_data( $column, $post_id ) {
		switch ( $column ) {
			case 'purchase_date':
				$date = get_the_time( 'F j, Y', $post_id );
				if ( empty( $date ) ) {
					_e( 'Unable to get date', 'merchant' );
				} else {
					echo $date;
				}
				break;

			case 'plan':
				$plan_id = get_post_meta( $post_id, 'merchant_purchase_plan', true );
				$plan = empty( $plan_id ) ? null : get_post( $plan_id );
				if ( empty( $plan ) ) {
					_e( 'Unable to get plan', 'merchant' );
				} else {
					echo esc_html( $plan->post_title );
				}
				break;

			case 'purchaser':
				$details = get_post_meta( $post_id, 'merchant_purchase_details', true );
				if ( is_array( $details ) && array_key_exists( 'purchaser', $details ) ) {
					echo esc_html( $details['purchaser'] );
				} else {
					_e( 'Unable to get purchaser', 'merchant' );
				}
				break;

			case 'price':
				$details = get_post_meta( $post_id, 'merchant_purchase_details', true );
				if ( is_array( $details ) && array_key_exists( 'price', $details ) ) {
					echo esc_html( $details['price'] );
				} else {
					_e( 'Unable to get purchase price', 'merchant' );
				}
				break;

			case 'discount':
				$promo_id = get_post_meta( $post_id, 'merchant_purchase_discount', true );
				$promo = empty( $plan_id ) ? null : get_post( $promo_id );
				if ( empty( $promo ) ) {
					_e( '', 'merchant' );
				} else {
					echo esc_html( $promo->post_title );
				}
				break;

		}
	}
	add_action( 'manage_posts_custom_column' , 'merchant_purchases_add_column_data', 10, 2 );


	/**
	 * Make purchase columns sortable
	 * @param array $columns The columns
	 * @return $columns
	 */
	function merchant_purchases_make_columns_sortable( $columns ) {

		$custom = array(
			'purchase_date' => 'purchase_date',
			'plan' => 'plan',
			'purchaser' => 'purchaser',
			'price' => 'price',
			'discount' => 'discount',
		);

		return wp_parse_args($custom, $columns);

	}
	add_filter( 'manage_edit-merchant-purchases_sortable_columns', 'merchant_purchases_make_columns_sortable' );


	/**
	 * Disable the auto-save functionality for IPN.
	 */
	function merchant_purchases_disable_autosave() {
	    global $post;

	    if ( $post && get_post_type( $post->ID ) === 'merchant-purchases' ) {
	        wp_dequeue_script( 'autosave' );
	    }

	}
	add_action( 'admin_print_scripts', 'merchant_purchases_disable_autosave' );


	/**
	 * Remove submit metabox
	 */
	function merchant_purchases_remove_meta_boxes() {
	    remove_meta_box( 'submitdiv', 'merchant-purchases', 'side' );
	    remove_meta_box( 'slugdiv', 'merchant-purchases', 'normal' );
	}
	add_action( 'add_meta_boxes', 'merchant_purchases_remove_meta_boxes', 10 );