<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!function_exists('reycore_shortcode__can_ship')):
	/**
	 * Can Ship shortcode. Will check if shipping is supported for visitors.
	 *
	 * @since 1.0.0
	 **/
	function reycore_shortcode__can_ship($atts)
	{
		if( class_exists('WooCommerce') && class_exists('WC_Geolocation') && is_callable('WC') ){

			// Bail if localhost
			if( in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) ){
				return esc_html__('Can\'t geolocate localhost.', 'rey-core');
			}

			$geolocation = WC_Geolocation::geolocate_ip();
			$country_list = WC()->countries->get_shipping_countries();
			if( !is_null($geolocation) && !empty($geolocation) && isset($geolocation['country']) ) {
				if( isset($country_list[$geolocation['country']]) && ($supported_country = $country_list[$geolocation['country']]) ) {
					if( isset($atts['text']) && !empty($atts['text']) ){
						$html = '<span class="rey-canShip">'. str_replace('%s', '<span>%s</span>', sanitize_text_field($atts['text'])) .'</span>';
						return sprintf( $html, $country_list[$geolocation['country']] );
					}
				}
			}
		}
		return false;
	}
endif;
add_shortcode('can_ship', 'reycore_shortcode__can_ship');


if(!function_exists('reycore_shortcode__site_info')):
	/**
	 * Display site info through shortcodes.
	 * show = name / email / url
	 *
	 * @since 1.0.0
	 **/
	function reycore_shortcode__site_info($atts)
	{
		$content = '';
		if( isset($atts['show']) && $show = $atts['show'] ){
			switch ($show):
				case"name":
					$content = get_bloginfo( 'name' );
					break;
				case"email":
					$content = get_bloginfo( 'admin_email' );
					break;
				case"url":
					$content = sprintf('<a href="%1$s">%1%s</a>', get_bloginfo( 'url' ));
					break;
			endswitch;
		}
		return $content;
	}
endif;
add_shortcode('site_info', 'reycore_shortcode__site_info');
