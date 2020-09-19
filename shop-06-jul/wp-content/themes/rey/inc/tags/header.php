<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if(!function_exists('rey__add_header_markup')):
	/**
	 * Add header markup
	 *
	 * @since 1.0.0
	 **/
	function rey__add_header_markup()
	{
		if( rey__get_option('header_layout_type', 'default') == 'none' || is_404() ){
			return;
		}
		get_template_part( 'template-parts/header/base' );
	}
endif;
add_action('rey/header', 'rey__add_header_markup', 10);


if(!function_exists('rey__header__content')):
	/**
	 * Add header content into header
	 *
	 * @since 1.0.0
	 **/
	function rey__header__content(){
		// Load default header
		if( rey__get_option('header_layout_type', 'default') == 'default'  || ! class_exists('ReyCore') ){
			get_template_part('template-parts/header/content');
		}
	}
endif;
add_action('rey/header/content', 'rey__header__content', 10);


if(!function_exists('rey__header__overlay')):
	/**
	 * Add header overlay
	 *
	 * @since 1.0.0
	 **/
	function rey__header__overlay(){
		get_template_part('template-parts/header/overlay');
	}
endif;
add_action('rey/header/content', 'rey__header__overlay', 200);


if(!function_exists('rey__header__logo')):
	/**
	 * Add logo markup
	 *
	 * @since 1.0.0
	 **/
	function rey__header__logo(){
		get_template_part('template-parts/header/logo');
	}
endif;
add_action('rey/header/row', 'rey__header__logo', 10);


if(!function_exists('rey__header__navigation')):
	/**
	 * Add navigation markup
	 *
	 * @since 1.0.0
	 **/
	function rey__header__navigation(){
		get_template_part('template-parts/header/navigation');
	}
endif;
add_action('rey/header/row', 'rey__header__navigation', 20);


if(!function_exists('rey__header__search')):
	/**
	 * Add search button markup
	 *
	 * @since 1.0.0
	 **/
	function rey__header__search(){
		// return if search is disabled
		if( get_theme_mod('header_enable_search', true) ) {
			get_template_part('template-parts/header/search-button');
		}
	}
endif;
add_action('rey/header/row', 'rey__header__search', 30);



/**
 * Add classes to header
 *
 * @since 1.0.0
 */
add_filter('rey/header/header_classes', function($classes){

	$header_layout = rey__get_option('header_layout_type', 'default');

	if( $header_layout != 'default' && !class_exists('ReyCore') ){
		$header_layout = 'default';
	}

	// Header Style Class
	$classes['layout'] = 'rey-siteHeader--' . $header_layout;

	if( $header_layout != 'default' && $header_layout != 'none' ) {
		$classes['layout'] = 'rey-siteHeader--custom';
	}

	if( $header_pos = rey__get_option('header_position', 'rel') ) {
		// Header Position
		$classes['position'] = 'header-pos--' . $header_pos;

		// Fixed header - Disable mobile option
		if( $header_pos === 'fixed' && get_theme_mod('header_fixed_disable_mobile', true) === true ){
			$classes['fixed-mobile'] = '--not-mobile';
		}
	}

	if( $header_layout === 'default' && get_theme_mod('header_separator_bar', true) ){
		// Header Position
		$classes['separator-bar'] = 'header--separator-bar';
		// Only on mobile?
		if( get_theme_mod('header_separator_bar_mobile', false) ){
			$classes['separator-bar-mobile'] = '--separator-bar-mobile';
		}
	}

	return $classes;
});


if(!function_exists('rey__custom_logo')):
	/**
	 * Prints Logo HTML
	 * Based on WP get_custom_logo()
	 *
	 * @since 1.0.0
	 **/
	function rey__custom_logo( $args = [] )
	{
		$html          = '';

		// We have a logo. Logo is go.
		if ( isset($args['logo']) && $custom_logo_id = $args['logo'] ) {

			$custom_logo_attr = array(
				'class'    => 'custom-logo',
			);

			/*
			* If the logo alt attribute is empty, get the site title and explicitly
			* pass it to the attributes used by wp_get_attachment_image().
			*/
			$image_alt = get_post_meta( $custom_logo_id, '_wp_attachment_image_alt', true );
			if ( empty( $image_alt ) ) {
				$custom_logo_attr['alt'] = get_bloginfo( 'name', 'display' );
			}

			/**
			 * Get The mobile logo
			 */
			$mobile_logo   = '';
			if( isset($args['logo_mobile']) && $custom_logo_mobile_id = $args['logo_mobile'] ){
				$mobile_logo = wp_get_attachment_image($custom_logo_mobile_id, 'full', false, ['class' => 'rey-mobileLogo']);
			}

			/*
			* If the alt attribute is not empty, there's no need to explicitly pass
			* it because wp_get_attachment_image() already adds the alt attribute.
			*/
			$html = sprintf(
				'<a href="%1$s" class="custom-logo-link" rel="home" itemprop="url">%2$s %3$s</a>',
				esc_url( home_url( '/' ) ),
				wp_get_attachment_image( $custom_logo_id, 'full', false, apply_filters('rey/logo/attributes', $custom_logo_attr) ),
				$mobile_logo
			);

		} elseif ( is_customize_preview() ) {
			// If no logo is set but we're in the Customizer, leave a placeholder (needed for the live preview).
			$html = sprintf(
				'<a href="%1$s" class="custom-logo-link" style="display:none;"><img class="custom-logo"/></a>',
				esc_url( home_url( '/' ) )
			);
		}

		return apply_filters('rey/header/logo_img_html', $html);
	}
endif;



if(!function_exists('rey__main_menu_css_class')):
	/**
	 * Menu CSS Classes
	 *
	 * @since: 1.0.0
	 */
	function rey__main_menu_css_class( $classes, $item, $args, $depth)
	{
		if( strpos($args->menu_class, 'id--mainMenu--desktop') !== false ) {

			$classes['depth'] = 'depth--' . $depth;

			if( $depth === 0 ) {
				$classes['type'] = '--is-regular';
			}
		}

		return $classes;
	}
endif;
add_filter('nav_menu_css_class', 'rey__main_menu_css_class', 10, 4);


if(!function_exists('rey__get_nav_menu_by_location')):
	/**
	 * Get menu location by name
	 *
	 * @since 1.0.2
	 */
	function rey__get_nav_menu_by_location( $menu_name = '' )
	{
		$menu_locations = get_nav_menu_locations();

		if ( isset($menu_locations[$menu_name]) && $menu = $menu_locations[$menu_name] ) {
			return $menu;
		}

		return $menu_name;
	}
endif;
