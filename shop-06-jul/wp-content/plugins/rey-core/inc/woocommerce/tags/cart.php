<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


if(!function_exists('reycore_wc__cart_hide_flatrate_if_freeshipping')):
	function reycore_wc__cart_hide_flatrate_if_freeshipping( $rates ) {

		if( ! get_theme_mod('cart_checkout_hide_flat_rate_if_free_shipping', false) ){
			return $rates;
		}

		$free = [];

		foreach ( $rates as $rate_id => $rate ) {
			if ( 'free_shipping' === $rate->method_id ) {
				$free[ $rate_id ] = $rate;
				break;
			}
		}

		return ! empty( $free ) ? $free : $rates;
	}
	add_filter( 'woocommerce_package_rates', 'reycore_wc__cart_hide_flatrate_if_freeshipping', 100 );
endif;


/**
 * Enable Qty controls on Cart & disable select (if enabled)
 * @since 1.6.6
 */
add_action('woocommerce_before_cart_table', function(){
	add_filter('theme_mod_single_atc_qty_controls', '__return_true');
	add_filter('reycore/woocommerce/quantity_field/can_add_select', '__return_false');
});
add_action('woocommerce_after_cart_table', function(){
	remove_filter('theme_mod_single_atc_qty_controls', '__return_true');
	remove_filter('reycore/woocommerce/quantity_field/can_add_select', '__return_false');
});
