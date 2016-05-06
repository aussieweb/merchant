<?php

/**
 * Plugin Name: GMT Merchant
 * Plugin URI: https://github.com/cferdinandi/merchant/
 * GitHub Plugin URI: https://github.com/cferdinandi/merchant/
 * Description: A simple plugin for selling things with PayPal.
 * Version: 2.0.0
 * Author URI: http://gomakethings.com
 * License: MIT
 */


// Includes
require_once( plugin_dir_path( __FILE__ ) . 'includes/wp-session-manager/wp-session-manager.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/helpers.php' );

// Options and reporting
require_once( plugin_dir_path( __FILE__ ) . 'includes/options.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/reporting.php' );

// Pricing
require_once( plugin_dir_path( __FILE__ ) . 'prices/prices-cpt.php' );
require_once( plugin_dir_path( __FILE__ ) . 'prices/prices-metabox.php' );

// Purchases
require_once( plugin_dir_path( __FILE__ ) . 'purchases/purchases-cpt.php' );
// require_once( plugin_dir_path( __FILE__ ) . 'purchases/purchases-metabox.php' );

// Promo Codes
require_once( plugin_dir_path( __FILE__ ) . 'promos/promos-cpt.php' );
require_once( plugin_dir_path( __FILE__ ) . 'promos/promos-metabox.php' );

// Checkout
require_once( plugin_dir_path( __FILE__ ) . 'checkout/buy-now-shortcode.php' );
require_once( plugin_dir_path( __FILE__ ) . 'checkout/limited-supply-shortcode.php' );
require_once( plugin_dir_path( __FILE__ ) . 'checkout/checkout-shortcode.php' );
require_once( plugin_dir_path( __FILE__ ) . 'checkout/success-shortcode.php' );
require_once( plugin_dir_path( __FILE__ ) . 'checkout/timestamped-message.php' );

// MailChimp integration
require_once( plugin_dir_path( __FILE__ ) . 'mailchimp/mailchimp.php' );


// Check that PayPal Framework is installed
function merchant_required_plugins_admin_notice() {

	// PayPal Framework
	if ( !class_exists( 'wpPayPalFramework' ) ) :
	?>
	<div class="notice notice-error"><p><strong>Warning!</strong> Merchant requires the <a href="https://wordpress.org/support/plugin/paypal-framework">PayPal Framework plugin</a>. Please install it immediately.</p></div>
	<?php
	endif;

}
add_action( 'admin_notices', 'merchant_required_plugins_admin_notice' );


// Flush rewrite rules on activation and deactivation
function merchant_flush_rewrites() {
	merchant_add_plans_custom_post_type();
	merchant_add_promos_custom_post_type();
	merchant_add_purchases_custom_post_type();
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
register_activation_hook( __FILE__, 'merchant_flush_rewrites' );