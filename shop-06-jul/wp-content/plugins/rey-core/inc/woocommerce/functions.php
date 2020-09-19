<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


if(!function_exists('reycore_wc__is_catalog')):
/**
 * Check if store is catalog
 *
 * @since 1.2.0
 **/
function reycore_wc__is_catalog()
{
	return get_theme_mod('shop_catalog', false) === true;
}
endif;

if(!function_exists('reycore_wc__get_discount')):
	/**
	 * Get product discount percentage
	 *
	 * @since 1.0.0
	 */
	function reycore_wc__get_discount( $product = false ){

		if( ! $product ){
			$product = wc_get_product();
		}

		if ( $product && $product->is_on_sale() ) {
			 return get_post_meta($product->get_id(), '_rey__discount_percentage', true);
		}

		return false;
	}
endif;


if(!function_exists('reycore_wc__reset_filters_link')):
	/**
	 * Get link for resetting filters
	 *
	 * @since 1.0.0
	 **/
	function reycore_wc__reset_filters_link()
	{
		$link = '';

		if ( defined( 'SHOP_IS_ON_FRONT' ) ) {
			$link = home_url();
		} elseif ( is_shop() ) {
			$link = get_permalink( wc_get_page_id( 'shop' ) );
		} elseif ( is_product_category() && $cat = get_query_var( 'product_cat' ) ) {
			$link = get_term_link( $cat, 'product_cat' );
		} elseif ( is_product_tag() && $tag = get_query_var( 'product_tag' ) ) {
			$link = get_term_link( $tag, 'product_tag' );
		} else {
			$queried_object = get_queried_object();
			if( is_object($queried_object) && isset($queried_object->slug) && !empty($queried_object->slug) ){
				$link = get_term_link( $queried_object->slug, $queried_object->taxonomy );
			}
		}

		/**
		 * Search Arg.
		 * To support quote characters, first they are decoded from &quot; entities, then URL encoded.
		 */
		if ( get_search_query() ) {
			$link = add_query_arg( 's', rawurlencode( wp_specialchars_decode( get_search_query() ) ), $link );
		}

		// Post Type Arg
		if ( isset( $_GET['post_type'] ) ) {
			$link = add_query_arg( 'post_type', wc_clean( wp_unslash( $_GET['post_type'] ) ), $link );
		}

		return esc_url( apply_filters('reycore/woocommerce/reset_filters_link', $link) );
	}
endif;


if(!function_exists('reycore_wc__check_filter_panel')):
	/**
	 * Check if panel filter is enabled
	 *
	 * @since 1.0.0
	 **/
	function reycore_wc__check_filter_panel()
	{
		return is_active_sidebar( 'filters-sidebar' );
	}
endif;


if(!function_exists('reycore_wc__check_filter_sidebar_top')):
	/**
	 * Check if top sidebar filter is enabled
	 *
	 * @since 1.0.0
	 **/
	function reycore_wc__check_filter_sidebar_top()
	{
		return is_active_sidebar( 'filters-top-sidebar' );
	}
endif;


if(!function_exists('reycore_wc__check_shop_sidebar')):
	/**
	 * Check if shop sidebar filter is enabled
	 *
	 * @since 1.5.0
	 **/
	function reycore_wc__check_shop_sidebar()
	{
		return apply_filters('reycore/woocommerce/check_shop_sidebar', false );
	}
endif;


if(!function_exists('reycore_wc__get_active_filters')):
	/**
	 * Check if panel filter is enabled
	 *
	 * @since 1.0.0
	 **/
	function reycore_wc__get_active_filters()
	{
		return apply_filters('reycore/woocommerce/get_active_filters', 0);
	}
endif;


/**
 * Load custom WooCommerce templates
 *
 * @since 1.0.0
 */
add_filter('wc_get_template', function( $template, $template_name, $args ){

	$templates = [];

	// Loop - Pagination
	if( $template_name === 'loop/pagination.php' ){
		$templates[] = [
			'template_name' => $template_name,
			'template' => sprintf( 'template-parts/woocommerce/loop-pagination-%s.php', get_theme_mod('loop_pagination', 'paged') )
		];
	}
	// Product page - Blocks
	elseif( $template_name === 'single-product/tabs/tabs.php' && get_theme_mod('product_content_layout', 'blocks') === 'blocks' ){
		$templates[] = [
			'template_name' => $template_name,
			'template' => 'template-parts/woocommerce/single-blocks.php'
		];
	}
	// Order by select list
	elseif( $template_name === 'loop/orderby.php' ){
		$templates[] = [
			'template_name' => $template_name,
			'template' => 'template-parts/woocommerce/loop-orderby.php'
		];
	}


	foreach ($templates as $tpl) {
		if( $template_name === $tpl['template_name'] ){
			if ( file_exists( STYLESHEETPATH . '/' . $tpl['template'] ) ) {
				$template = STYLESHEETPATH . '/' . $tpl['template'];
			}
			elseif ( file_exists( TEMPLATEPATH . '/' . $tpl['template'] ) ) {
				$template = TEMPLATEPATH . '/' . $tpl['template'];
			}
			elseif ( file_exists( REY_CORE_DIR . $tpl['template'] ) ) {
				$template = REY_CORE_DIR . $tpl['template'];
			}
		}
	}

	return $template;

}, 10, 3);


if(!function_exists('reycore_wc_get_columns')):
	/**
	 * Get Enabled WooCommerce Components
	 */
	function reycore_wc_get_columns( $device = '' ){

		$devices = apply_filters('reycore/woocommerce/columns', [
			'desktop' => wc_get_default_products_per_row(),
			'tablet' => get_theme_mod('woocommerce_catalog_columns_tablet', 2),
			'mobile' => get_theme_mod('woocommerce_catalog_columns_mobile', 2)
		]);

		if( isset($devices[$device]) ){
			return absint($devices[$device]);
		}

		return $devices;
	}
endif;


if(!function_exists('reycore_wc_get_loop_components')):
	/**
	 * Get Enabled WooCommerce Components
	 */
	function reycore_wc_get_loop_components( $component = '' ){

		$components = apply_filters('reycore/loop_components', [
			// items components
			'result_count' => true,
			'catalog_ordering' => true,
			'title' => true,
			'ratings' => get_theme_mod('loop_ratings', '2') == '1',
			'quickview' => [
				'bottom' => get_theme_mod('loop_quickview', '1') != '2' && get_theme_mod('loop_quickview_position', 'bottom') === 'bottom',
				'topright' => get_theme_mod('loop_quickview', '1') != '2' && get_theme_mod('loop_quickview_position', 'bottom') === 'topright',
				'bottomright' => get_theme_mod('loop_quickview', '1') != '2' && get_theme_mod('loop_quickview_position', 'bottom') === 'bottomright',
			],
			'brands' => get_theme_mod('loop_show_brads', '1') == '1',
			'category' => get_theme_mod('loop_show_categories', '2') == '1',
			'excerpt' => get_theme_mod('loop_short_desc', '2') == '1',
			'wishlist' => class_exists('TInvWL_Public_AddToWishlist'),
			'prices' => get_theme_mod('loop_show_prices', '1') == '1',
			'thumbnails' => true,
			'add_to_cart' => get_theme_mod('loop_add_to_cart', true) === true,
			'variations' => get_theme_mod('woocommerce_loop_variation', 'disabled') !== 'disabled',
			'new_badge' => get_theme_mod('loop_new_badge', '1') === '1',
			'sold_out_badge' => get_theme_mod('loop_sold_out_badge', '1') !== '2',
			'featured_badge' => get_theme_mod('loop_featured_badge', 'hide') !== 'hide',
			'discount' => [
				'top' => get_theme_mod('loop_show_sale_label', 'percentage') !== '',
				'price' => get_theme_mod('loop_show_sale_label', 'percentage') !== '' && get_theme_mod('loop_discount_label', 'price') === 'price',
			]
		]);

		if( isset($components[$component]) ){
			return $components[$component];
		}

		return $components;
	}
endif;

if(!function_exists('reycore_wc__append_loop_components_status')):
/**
 * Append loop components status in INIT.
 * If added dirrectly in `reycore_wc_get_loop_components`, will throw an error
 * because the sidebar needs to check after init.
 *
 * @since 1.3.0
 **/
function reycore_wc__append_loop_components_status()
{
	add_filter('reycore/loop_components', function($components){

		// loop components
		$components['view_selector'] = get_theme_mod('loop_view_switcher', '1') == '1';
		$components['filter_button'] = reycore_wc__check_filter_panel();
		$components['filter_top_sidebar'] = reycore_wc__check_filter_sidebar_top();
		$components['mobile_filter_button'] = reycore_wc__check_filter_sidebar_top() || reycore_wc__check_shop_sidebar();

		if( class_exists('TInvWL_Public_AddToWishlist') && class_exists('ReyCore_WooCommerce_Wishlist') && get_theme_mod('loop_wishlist_enable', true) ):
			$wishlist_pos = ReyCore_WooCommerce_Wishlist::tinvl_get_wishlist_default_position();
			$components['wishlist'] = [
				'bottom' => $wishlist_pos === 'bottom',
				'topright' => $wishlist_pos === 'topright',
				'bottomright' => $wishlist_pos === 'bottomright'
			];
		endif;

		return $components;
	});
}
add_action('init', 'reycore_wc__append_loop_components_status');
endif;


if(!function_exists('reycore_wc__check_downloads_endpoint')):
	/**
	 * Check if downloads endpoint is disabled
	 *
	 * @since 1.0.0
	 */
	function reycore_wc__check_downloads_endpoint() {
		return get_option('woocommerce_myaccount_downloads_endpoint') != '';
	}
endif;


if(!function_exists('reycore_wc__count_downloads')):
	/**
	 * Get downloads count and store in transient
	 *
	 * @since 1.0.0
	 */
	function reycore_wc__count_downloads( $user_id ) {

		if( function_exists('rey__maybe_disable_obj_cache') ){
			rey__maybe_disable_obj_cache();
		}

		$transient_name = "rey-wc-user-dld-{$user_id}";

		if ( false === ( $downloads_count = get_transient( $transient_name ) ) ) {

			if( isset(WC()->customer) ){
				$customer = WC()->customer;
			}
			else {
				$customer = new WC_Customer( $user_id );
			}

			$downloads_count = $customer->get_downloadable_products();
			set_transient( $transient_name, $downloads_count, HOUR_IN_SECONDS );
		}

		return $downloads_count;
	}
endif;


if(!function_exists('reycore_wc__count_orders')):
	/**
	 * Get orders count and store in transient
	 *
	 * @since 1.0.0
	 */
	function reycore_wc__count_orders( $user_id ) {

		if( function_exists('rey__maybe_disable_obj_cache') ){
			rey__maybe_disable_obj_cache();
		}

		$transient_name = "rey-wc-user-order-{$user_id}";

		if ( false === ( $orders_count = get_transient( $transient_name ) ) ) {
			$orders_count = wc_get_customer_order_count($user_id);
			set_transient( $transient_name, $orders_count, HOUR_IN_SECONDS );
		}

		return $orders_count;
	}
endif;

if(!function_exists('reycore_wc__account_counters_reset')):
	/**
	 * Reset orders count transients
	 *
	 * @since 1.6.3
	 */
	function reycore_wc__account_counters_reset( $order_id ) {
		if( ($order = wc_get_order( $order_id )) && ($user_id = $order->get_user_id()) ){
			delete_transient("rey-wc-user-order-{$user_id}");
			delete_transient("rey-wc-user-dld-{$user_id}");
		}
	}
	add_action( 'woocommerce_delete_shop_order_transients', 'reycore_wc__account_counters_reset' );
endif;




if(!function_exists('reycore_wc__cache_discounts')):
	/**
	 * Save discount percentage
	 *
	 * @since 1.0.0
	 */
	function reycore_wc__cache_discounts( $product_id )
	{
		$product = wc_get_product( $product_id );

		if ( $product && $product->is_on_sale() )
		{

			$discount = 0;

			if ( $product->is_type( 'simple' ) ) {
				$discount = ( ( $product->get_regular_price() - $product->get_sale_price() ) / $product->get_regular_price() ) * 100;
			}

			elseif ( $product->is_type( 'variable' ) ) {

				foreach ( $product->get_children() as $child_id ) {
					$variation = wc_get_product( $child_id );
					$price = $variation->get_regular_price();
					$sale = $variation->get_sale_price();
					if ( $price != 0 && ! empty( $sale ) ) {
						$percentage = ( $price - $sale ) / $price * 100;
						if ( $percentage > $discount ) {
							$discount = $percentage;
						}
					}
				}
			}

			if ( ! add_post_meta( $product_id, '_rey__discount_percentage', round($discount), true ) ) {
				update_post_meta( $product_id, '_rey__discount_percentage', round($discount) );
			}
		}
	}
endif;
add_action( 'woocommerce_update_product', 'reycore_wc__cache_discounts', 10, 1 );

if(!function_exists('reycore_wc__cache_discounts_refresh')):
/**
 * Refresh discounted products meta
 *
 * @since 1.5.0
 **/
function reycore_wc__cache_discounts_refresh( $post_id )
{
	if( $post_id > 0 ){
		return;
	}

	$products_on_sale = wc_get_product_ids_on_sale();

	foreach($products_on_sale as $product_id){
		$product_object = wc_get_product( $product_id );
		if( $product_object ){
			$product_object->save();
		}
	}
}
add_action('woocommerce_delete_product_transients', 'reycore_wc__cache_discounts_refresh');
endif;


if(!function_exists('reycore_wc__product_categories')):
	/**
	 * WooCommerce Product Query
	 * @return array
	 */
	function reycore_wc__product_categories( $args = [] ){

		$args = wp_parse_args($args, [
			'hide_empty' => true,
			'parent' => false,
			'labels' => false,
			'orderby' => 'name',
			'extra_item' => [],
		]);

		$terms_args = [
			'taxonomy'   => 'product_cat',
			'hide_empty' => $args['hide_empty'],
			'orderby' => $args['orderby'], // 'name', 'term_id', 'term_group', 'parent', 'menu_order', 'count'
			'update_term_meta_cache'	=> false,
		];

		// if parent only
		if( $args['parent'] === 0 ){
			$terms_args['parent'] = 0;
		}
		// if subcategories
		elseif( $args['parent'] !== 0 && $args['parent'] !== false ){

			// if automatic
			if( $args['parent'] === '' ) {
				$parent_term = get_queried_object();
			}
			// if pre-defined parent category
			else {
				$parent_term = get_term_by('slug', $args['parent'], 'product_cat');
			}

			if(is_object($parent_term) && isset($parent_term->term_id) ) {
				$terms_args['parent'] = $parent_term->term_id;
			}
		}

		$terms = get_terms( $terms_args );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
			$options = $args['extra_item'];
			foreach ( $terms as $term ) {

				$term_name = wp_specialchars_decode($term->name);

				if( $args['labels'] === true && isset($term->parent) && $term->parent === 0 ){
					$term_name = sprintf('%s (%s)', $term_name, esc_html__('Parent Category', 'rey-core'));
				}

				$options[ $term->slug ] = $term_name;
			}
			return $options;
		}
	}
endif;


if(!function_exists('reycore_wc__product_tags')):
	/**
	 * WooCommerce Product Query
	 * @return array
	 */
	function reycore_wc__product_tags( $args = [] ){

		$args = wp_parse_args($args, [
			'taxonomy'   => 'product_tag',
			'hide_empty' => true,
		]);

		$terms = get_terms( $args );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
			foreach ( $terms as $term ) {
				$options[ $term->slug ] = $term->name;
			}
			return $options;
		}
	}
endif;


if(!function_exists('reycore_wc__products')):
	/**
	 * WooCommerce Product Query
	 * @return array
	 */
	function reycore_wc__products(){

		$product_ids = get_posts( [
				'post_type' => 'product',
				'numberposts' => -1,
				'post_status' => 'publish',
				'fields' => 'ids',
		 ]);

		if ( ! empty( $product_ids ) ){
			foreach ( $product_ids as $product_id ) {
				$options[ $product_id ] = get_the_title($product_id);
			}
			return $options;
		}
		return [];
	}
endif;
