<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
Plugin Name: WooCommerce MoneyGram Gateway
Plugin URI: https://wordpress.org/plugins/woo-western-union-gateway/
Description: Adds MoneyGram Gateway to WooCommerce e-commerce plugin
Version: 1.1.1
Author: Afolabi Omotoso
Author URI: https://afolabiomotoso.com/
*/

// Include our Gateway Class and register Payment Gateway with WooCommerce
add_action( 'plugins_loaded', 'woo_mg_init', 0 );
function woo_mg_init() {
	// If the parent WC_Payment_Gateway class doesn't exist
	// it means WooCommerce is not installed on the site
	// so do nothing
	if ( ! class_exists( 'MG_Payment_Gateway' ) ) return;
	
	// If we made it this far, then include our Gateway Class
	include_once( 'MoneyGram.php' );

	// Now that we have successfully included our class,
	// Lets add it too WooCommerce
	add_filter( 'woocommerce_payment_gateways', 'woo_add_mg_gateway' );
	function woo_add_wu_gateway( $methods ) {
		$methods[] = 'MG_Gateway_Money_Gram';
		return $methods;
	}
}

// Add custom action links
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'spyr_authorizenet_aim_action_links' );
function spyr_authorizenet_aim_action_links( $links ) {
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wunion' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>',
	);

	// Merge our new link with the default ones
	return array_merge( $plugin_links, $links );	
}

?>