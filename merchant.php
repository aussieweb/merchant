<?php

/**
 * Plugin Name: GMT Merchant
 * Plugin URI: https://github.com/cferdinandi/merchant/
 * GitHub Plugin URI: https://github.com/cferdinandi/merchant/
 * Description: A simple plugin for selling things with PayPal.
 * Version: 1.0.0
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

// Promo Codes
require_once( plugin_dir_path( __FILE__ ) . 'promos/promos-cpt.php' );
require_once( plugin_dir_path( __FILE__ ) . 'promos/promos-metabox.php' );

// Checkout
require_once( plugin_dir_path( __FILE__ ) . 'checkout/buy-now-shortcode.php' );
require_once( plugin_dir_path( __FILE__ ) . 'checkout/limited-supply-shortcode.php' );
require_once( plugin_dir_path( __FILE__ ) . 'checkout/checkout-shortcode.php' );
require_once( plugin_dir_path( __FILE__ ) . 'checkout/profile-purchases.php' );

// MailChimp integration
require_once( plugin_dir_path( __FILE__ ) . 'mailchimp/mailchimp.php' );


// Check that PayPal Framework is installed
function beacon_required_plugins_admin_notice() {

	// PayPal Framework
	if ( !class_exists( 'wpPayPalFramework' ) ) :
	?>
	<div class="notice notice-error"><p><strong>Warning!</strong> Merchant requires the <a href="https://wordpress.org/support/plugin/paypal-framework">PayPal Framework plugin</a>. Please install it immediately.</p></div>
	<?php
	endif;

}
add_action( 'admin_notices', 'beacon_required_plugins_admin_notice' );


// Flush rewrite rules on activation and deactivation
function beacon_flush_rewrites() {
	projects_add_custom_post_type();
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
register_activation_hook( __FILE__, 'beacon_flush_rewrites' );