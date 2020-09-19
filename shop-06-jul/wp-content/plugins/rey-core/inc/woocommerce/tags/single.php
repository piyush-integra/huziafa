<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_Single') ):
/**
 * Loop fucntionality
 *
 */
class ReyCore_WooCommerce_Single
{
	private static $_instance = null;

	private $_skins = [];

	private function __construct()
	{
		add_action( 'init', [$this, 'init']);

		$this->set_single_skins();
		$this->load_includes();
	}

	function init(){

		if ( is_customize_preview() ) {
			add_action( 'customize_preview_init', [$this, 'load_hooks'] );
			return;
		}

		$this->load_hooks();
	}

	public function load_hooks()
	{
		add_action( 'wp', [ $this, 'rearrangements' ]);
		add_action( 'woocommerce_single_product_summary', [ $this, 'wrap_inner_summary' ], 2);
		add_action( 'woocommerce_single_product_summary', 'reycore_wc__generic_wrapper_end', 500);
		add_action( 'woocommerce_before_single_product_summary', [ $this, 'wrap_product_summary' ], 0);
		add_action( 'woocommerce_after_single_product_summary', 'reycore_wc__generic_wrapper_end', 2);
		add_action( 'woocommerce_single_product_summary', [ $this, 'wrap_title' ], 4);
		add_action( 'woocommerce_single_product_summary', 'reycore_wc__generic_wrapper_end', 6);
		add_filter( 'woocommerce_get_stock_html', [ $this, 'adjust_stock_html' ], 9, 2);
		add_filter( 'woocommerce_post_class', [$this, 'product_page_classes'], 20, 2 );
		add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'add_text_before_atc' ], 10);
		add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'add_text_after_atc' ], 10);
		add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'wrap_cart_qty' ], 10);
		add_action( 'woocommerce_after_add_to_cart_button', 'reycore_wc__generic_wrapper_end', 5);
		add_action( 'woocommerce_before_quantity_input_field', [$this, 'wrap_quantity_input']);
		add_action( 'woocommerce_after_quantity_input_field', 'reycore_wc__generic_wrapper_end');
		add_filter( 'woocommerce_product_thumbnails_columns', [$this,'product_thumbnails_columns'], 10);
		add_filter( 'woocommerce_get_price_html', [ $this, 'discount_percentage' ], 10, 2);
		add_action( 'woocommerce_share', [$this,'add_share_buttons']);
		add_filter( 'wc_product_sku_enabled', [$this, 'product_sku']);
		add_action( 'woocommerce_after_single_product_summary', [$this, 'global_section_after_product_summary'], 6 );
		add_action( 'wp', [$this, 'global_section_after_content_choose'], 10 );
		add_filter( 'reycore/woocommerce/product_page/content_after', [$this, 'global_section_after_content_per_category'], 10, 2);
		add_filter( 'reycore/woocommerce/product_page/content_after', [$this, 'global_section_after_content_per_page'], 20, 2);
		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 10 );
		add_filter( 'body_class', [ $this, 'body_classes'], 20 );
		add_filter( 'woocommerce_short_description', [$this, 'add_excerpt_toggle']);
		add_filter( 'woocommerce_single_product_flexslider_enabled', '__return_false');
		add_filter( 'woocommerce_breadcrumb_defaults', [$this, 'remove_home_in_breadcrumbs']);
		add_filter( 'get_previous_post_where', [$this, 'get_adjacent_product_where']);
		add_filter( 'get_next_post_where', [$this, 'get_adjacent_product_where']);
		add_filter( 'get_previous_post_join', [$this, 'get_adjacent_product_join']);
		add_filter( 'get_next_post_join', [$this, 'get_adjacent_product_join']);

		$this->remove_product_meta();
		$this->remove_product_price();
	}

	/**
	 * Filter main script's params
	 *
	 * @since 1.0.0
	 **/
	public function script_params($params)
	{
		$params['wc_open_gallery_text'] = esc_html__('OPEN GALLERY', 'rey-core');
		$params['fixed_summary'] = get_theme_mod('product_page_summary_fixed', false);
		$params['fixed_summary__offset'] = get_theme_mod('product_page_summary_fixed__offset', '');
		$params['single_ajax_add_to_cart'] = ('yes' === get_theme_mod('product_page_ajax_add_to_cart', 'yes') && 'yes' === get_option( 'woocommerce_enable_ajax_add_to_cart' ));

		return $params;
	}

	/**
	 * Move stuff in template
	 * @since 1.0.0
	 */
	function rearrangements()
	{
		// Move breadcrumbs
		if( is_product() ){
			remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
		}

		// Move ratings at the end
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
		add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 45 );

	}

	/**
	 * Wrap inner summary - start
	 *
	 * @since 1.0.0
	 **/
	function wrap_inner_summary()
	{ ?>
		<div class="rey-innerSummary"><?php
	}

	/**
	 * Wrap single summary - start
	 *
	 * @since 1.0.0
	 **/
	function wrap_product_summary()
	{
		do_action('reycore/woocommerce/before_single_product_summary'); ?>

		<div class="rey-productSummary"><?php
	}

	/**
	 * Wrap single summary - start
	 *
	 * @since 1.0.0
	 **/
	function wrap_title()
	{ ?>
		<div class="rey-productTitle-wrapper"><?php
	}

	/**
	 * Wrap Add to cart & Quantity
	 *
	 * @since 1.0.0
	 **/
	function wrap_cart_qty()
	{
		$classes = [ 'rey-cartBtnQty' ];

		$product = wc_get_product();

		if ( $product && ! $product->is_sold_individually() ) {
			if( $style = get_theme_mod('single_atc_qty_controls_styles', 'default') ){
				$classes[] = '--style-' . $style;
			}
		}

		$classes = apply_filters('reycore/woocommerce/cart_wrapper/classes', $classes, $product);

		printf('<div class="%s">', implode(' ', array_map('esc_attr', $classes)));
	}

	/**
	 * Wrap Quantity
	 *
	 * @since 1.0.0
	 **/
	function wrap_quantity_input()
	{

		$classes = [ 'rey-qtyField' ];
		$style = get_theme_mod('single_atc_qty_controls_styles', 'default');
		$controls = get_theme_mod('single_atc_qty_controls', false);
		$can_add_select_box = $style === 'select' && apply_filters('reycore/woocommerce/quantity_field/can_add_select', true);
		$can_add_controls = ($controls && !$can_add_select_box);

		// will also be added in the cart
		if( $can_add_controls ){
			$classes[] = 'cartBtnQty-controls';
		}
		// start
		$content = sprintf('<div class="%s">', implode(' ', $classes));

		// return if product is sold individually
		if ( ($product = wc_get_product()) && $product && $product->is_sold_individually() ) {
			return;
		}

		// show QTY controls
		// - when enabled in product page
		// - in cart
		if( $can_add_controls ){
			$content .= sprintf('<span class="cartBtnQty-control --minus">%s</span>', reycore__get_svg_icon__core(['id'=>'reycore-icon-minus']));
			$content .= sprintf('<span class="cartBtnQty-control --plus">%s</span>', reycore__get_svg_icon__core(['id'=>'reycore-icon-plus']));
		}

		// Select box
		if( $can_add_select_box ) :

			$product = wc_get_product();

			$defaults = array(
				'input_name'  	=> 'quantity',
				'input_value'  	=> '1',
				'max_value'  	=> apply_filters( 'woocommerce_quantity_input_max', '', $product ),
				'min_value'  	=> apply_filters( 'woocommerce_quantity_input_min', '1', $product ),
				'step' 		=> apply_filters( 'woocommerce_quantity_input_step', '1', $product ),
				'style'		=> apply_filters( 'woocommerce_quantity_style', '', $product )
			);

			if ( ! empty( $defaults['min_value'] ) )
				$min = $defaults['min_value'];
			else $min = 1;

			if ( ! empty( $defaults['max_value'] ) )
				$max = $defaults['max_value'];
			else $max = 20;

			if ( ! empty( $defaults['step'] ) )
				$step = $defaults['step'];
			else $step = 1;

			$options = '';
			for ( $count = $min; $count <= $max; $count = $count+$step ) {
				$options .= '<option value="' . $count . '">' . $count . '</option>';
			}

			$content .= '<div class="rey-qtySelect" style="' . $defaults['style'] . '">';
			$content .= '<span class="rey-qtySelect-title">'. reycore__texts('qty') .'</span>';
			$content .= reycore__get_svg_icon__core(['id'=>'reycore-icon-arrow']);
			$content .= sprintf('<select name="%1$s" title="%2$s" class="qty" data-min="%4$s" data-max="%5$s" >%3$s</select>',
				esc_attr( $defaults['input_name'] ),
				reycore__texts('qty'),
				$options,
				$min,
				$max
			);
			$content .= '</div>';

		endif;

		echo $content;
	}

	/**
	 * Add text before add to cart button
	 *
	 * @since 1.4.0
	 **/
	function add_text_before_atc()
	{
		$en = reycore__get_option( 'enable_text_before_add_to_cart', false );

		if( $en === false || $en === 'false' ){
			return;
		}

		$content = reycore__get_option( 'text_before_add_to_cart', false, ($en !== 'custom') );

		printf('<div class="rey-cartBtn-beforeText">%s</div>', reycore__parse_text_editor( $content )  );
	}

	/**
	 * Add text after add to cart button
	 *
	 * @since 1.4.0
	 **/
	function add_text_after_atc()
	{
		$en = reycore__get_option( 'enable_text_after_add_to_cart', false );

		if( $en === false || $en === 'false' ){
			return;
		}

		$content = reycore__get_option( 'text_after_add_to_cart', false, ($en !== 'custom') );

		printf('<div class="rey-cartBtn-afterText">%s</div>', reycore__parse_text_editor( $content ) );
	}

	/**
	 * Filter Adjacent Post JOIN query
	 * to exclude out of stock items
	 *
	 * @since 1.3.7
	 */
	function get_adjacent_product_join($join){
		global $wpdb;

		if ( 'yes' == get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$join = $join . " INNER JOIN $wpdb->postmeta ON ( p.ID = $wpdb->postmeta.post_id )";
		}

		return $join;
	}

	/**
	 * Filter Adjacent Post WHERE query
	 * to exclude out of stock items
	 *
	 * @since 1.3.7
	 */
	function get_adjacent_product_where($where){

		global $wpdb;

		if ( 'yes' == get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$where = $wpdb->prepare("$where AND ($wpdb->postmeta.meta_key = %s AND $wpdb->postmeta.meta_value NOT LIKE %s)", '_stock_status', 'outofstock');
		}

		return $where;
	}

	/**
	 * Show Product Navigation
	 *
	 * @since 1.0.0
	 **/
	function product_navigation() {}

	/**
	 * Override thumbnails columns
	 *
	 * @since 1.0.0
	 **/
	function product_thumbnails_columns()
	{
		return 5;
	}

	/**
	 * Creates a read more / less toggle for short description
	 *
	 * @since 1.0.0
	 */
	function add_excerpt_toggle( $excerpt ){

		if( ! reycore__get_fallback_mod('product_short_desc_toggle_v2', false, [
			'mod' => 'product_short_desc_toggle',
			'value' => 'no',
			'compare' => '==='
		] ) || (!is_single() && !get_query_var('rey__is_quickview', false)) ){
			return $excerpt;
		}

		$intro = wp_strip_all_tags($excerpt);
		$limit = 50;

		if ( strlen($intro) > $limit) {

			$full_content = $excerpt;
			// truncate string
			$excerptCut = substr($intro, 0, $limit);
			$endPoint = strrpos($excerptCut, ' ');

			$excerpt = '<div class="u-toggle-text --collapsed">';
				$excerpt .= '<div class="u-toggle-content">';
				$excerpt .= $intro;
				$excerpt .= '</div>';
				$excerpt .= '<button class="btn u-toggle-btn" data-read-more="'. esc_html_x('Read more', 'Toggling the product excerpt in Compact layout.', 'rey-core') .'" data-read-less="'. esc_html_x('Less', 'Toggling the product excerpt in Compact layout.', 'rey-core') .'"></button>';
			$excerpt .= '</div>';

			return $excerpt;
		}

		return $excerpt;
	}

	function is_single_true_product(){
		return !in_array( wc_get_loop_prop('name'), ['upsells', 'crosssells', 'related'] );
	}

	/**
	 * Add discount percentage to product
	 *
	 * @since 1.0.0
	 */
	function discount_percentage($html, $product){

		if( ! is_product() ){
			return $html;
		}

		$content = '';

		/* ------------------------------------ DISCOUNT ------------------------------------ */

		$has_badge = reycore__get_fallback_mod('single_discount_badge_v2', true, [
			'mod' => 'single_discount_badge',
			'value' => '1',
			'compare' => '==='
		] );

		if ( $has_badge && $this->is_single_true_product() && $product->is_on_sale() && $discount_perc = reycore_wc__get_discount() ) {
			$content .= sprintf(
				__('<span class="rey-discount">-%d%% %s</span>', 'rey-core'),
				$discount_perc,
				esc_html_x('OFF', 'WooCommerce single item discount percentage', 'rey-core')
			);
		}

		/* ------------------------------------ CUSTOM TEXT ------------------------------------ */

		if( $this->is_single_true_product() && ($text_type = get_theme_mod('single_product_price_text_type', 'no')) && $text_type !== 'no' ){

			$text = sprintf( '<span class="rey-priceText %2$s">%1$s</span>',
				get_theme_mod('single_product_price_text_custom', esc_html__('Free Shipping!', 'rey-core')),
				get_theme_mod('single_product_price_text_inline', false) ? '--block' : ''
			);

			// simple custom text
			if( $text_type === 'custom_text' ){
				$content .= $text;
			}

			// based on current product price & cart totals
			elseif( $text_type === 'free_shipping' && null !== WC()->cart && wc_shipping_enabled() ){
				if( ($product->get_price() + absint( WC()->cart->get_displayed_subtotal() )) > absint( get_theme_mod('single_product_price_text_shipping_cost', 0) ) ){
					$content .= $text;
				}
				// keep an eye on https://gist.githubusercontent.com/rashmimalpande/5de0d929b0cc27130096f6da5be8f9e9/raw/9282cc4d36203dd76343afd481f8baffab206f9b/free-shipping-notice-on-checkout.php
			}

		}

		return $html . $content;
	}


	/**
	 * Filter Stock's text markup
	 *
	 * @since 1.0.0
	 **/
	function adjust_stock_html($html, $product)
	{
        if ( ! apply_filters( 'reycore/woocommerce/stock_display', true ) ) {
            return $html;
        }

		$availability = $product->get_availability();
		$stock_status = $product->get_stock_status();

		switch( $stock_status ):
			// onbackorder
			case "instock":
				return sprintf('<p class="stock %s">%s <span>%s</span></p>',
					esc_attr( $availability['class'] ),
					reycore__get_svg_icon(['id' => 'rey-icon-check']),
					$availability['availability'] ? $availability['availability'] : esc_html__( 'In stock', 'rey-core' )
				);
				break;
			case "outofstock":
				return sprintf('<p class="stock %s">%s <span>%s</span></p>',
					esc_attr( $availability['class'] ),
					reycore__get_svg_icon(['id' => 'rey-icon-close']),
					$availability['availability'] ? $availability['availability'] : esc_html__( 'Out of stock', 'rey-core' )
				);
				break;
		endswitch;

		return $html;
	}


	/**
	 * Add share buttons
	 *
	 * @since 1.0.0
	 **/
	function add_share_buttons()
	{
		$class = apply_filters('reycore/woocommerce/product_page/share/classes', false);
		// Sharing
		if( get_theme_mod('product_share', '1') == '1' ){
			printf('<div class="rey-productShare %s">', esc_attr($class));
				echo '<div class="rey-productShare-inner">';
				echo '<h5>'. esc_html__('SHARE', 'rey-core') .'</h5>';
				if( function_exists('reycore__socialShare') ){

					$share_icons = get_theme_mod('product_share_icons', [
						[
							'social_icon' => 'twitter',
						],
						[
							'social_icon' => 'facebook-f',
						],
						[
							'social_icon' => 'linkedin',
						],
						[
							'social_icon' => 'pinterest-p',
						],
						[
							'social_icon' => 'mail',
						],
						[
							'social_icon' => 'copy',
						],
					]);

					reycore__socialShare([
						'share_items' => wp_list_pluck($share_icons, 'social_icon'),
						'colored' => get_theme_mod('product_share_icons_colored', false)
					]);
				}
				echo '</div>';
			echo '</div>';
		}
	}

	/**
	 * Toggle product sku visibility
	 *
	 * @since 1.0.0
	 */
	function product_sku( $status ){

		if( is_product() || get_query_var('rey__is_quickview', false) ){
			return reycore__get_fallback_mod('product_sku_v2', true, [
				'mod' => 'product_sku',
				'value' => '1',
				'compare' => '==='
			] );
		}

		return $status;
	}

	function product_meta_is_enabled(){
		return reycore__get_fallback_mod('single_product_meta_v2', true, [
			'mod' => 'single_product_meta',
			'value' => 'show',
			'compare' => '==='
		] );
	}

	/**
	 * Product meta
	 *
	 * @since 1.3.5
	 */
	function remove_product_meta(){
		if( !$this->product_meta_is_enabled() ){
			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
		}
	}

	/**
	 * Product Price
	 *
	 * @since 1.6.4
	 */
	function remove_product_price(){
		if( ! get_theme_mod('single_product_price', true) ){
			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
		}
	}

	/**
	 * Check if breadcrums are enabled
	 *
	 * @since 1.3.4
	 */
	function breadcrumb_enabled(){
		return get_theme_mod('single_breadcrumbs', 'yes_hide_home') !== 'no';
	}

	/**
	 * Remove Home button in breadcrumbs
	 *
	 * @since 1.3.4
	 */
	function remove_home_in_breadcrumbs( $args ){

		if( is_product() && get_theme_mod('single_breadcrumbs', 'yes_hide_home') === 'yes_hide_home' ){
			$args['home']  = false;
		}

		return $args;
	}



	/**
	 * Adds global section *after product summary*
	 *
	 * @since 1.0.0
	 */
	function global_section_after_product_summary(){

		if( ! class_exists('ReyCore_GlobalSections' ) ){
			return;
		}

		$global_section = '';

		if( ($gs = reycore__get_option('product_content_after_summary', 'none')) && $gs !== 'none' ) {
			$global_section = $gs;
		}

		$global_section = absint( apply_filters( 'reycore/woocommerce/product_page/content_after', $global_section, 'summary' ) );

		if( $global_section ){
			echo ReyCore_GlobalSections::do_section( $global_section );
		}
	}

	function global_section_after_content_choose(){

		if( apply_filters('reycore/woocommerce/global_section_after_content/before_reviews', false) ){
			add_action( 'reycore/woocommerce/before_blocks_review', [$this, 'global_section_after_content'], 10 );
		}
		else {
			add_action( 'woocommerce_after_single_product_summary', [$this, 'global_section_after_content'], 10 );
		}
	}

	/**
	 * Adds global section *after content*
	 *
	 * @since 1.0.0
	 */
	function global_section_after_content(){
		if( ! class_exists('ReyCore_GlobalSections' ) ){
			return;
		}

		$global_section = '';

		if( ($gs = reycore__get_option('product_content_after_content', 'none')) && $gs !== 'none' ) {
			$global_section = $gs;
		}

		$global_section = absint( apply_filters( 'reycore/woocommerce/product_page/content_after', $global_section, 'content' ) );

		if( $global_section ){
			echo ReyCore_GlobalSections::do_section( $global_section );
		}
	}

	/**
	 * Adds global section after content, to products that belong to certain categories.
	 *
	 * @since 1.4.0
	 */
	function global_section_after_content_per_category( $gs, $position ){

		$custom_cats = get_theme_mod('product_content_after_content_per_category', []);

		if( empty($custom_cats) ){
			return $gs;
		}

		foreach($custom_cats as $gs_cat){
			if( !empty($gs_cat['categories']) && has_term( $gs_cat['categories'], 'product_cat', get_the_ID() ) && $gs_cat['position'] === $position ){
				$gs = absint($gs_cat['gs']);
			}
		}

		return $gs;
	}

	/**
	 * Adds global section after content, to products that belong to certain categories.
	 *
	 * @since 1.4.0
	 */
	function global_section_after_content_per_page( $gs, $position ){

		if( $position === 'content' && ($acf_content_gs = reycore__acf_get_field('product_content_after_content')) ){

			if( $acf_content_gs === 'none' ){
				return false;
			}

			return absint($acf_content_gs);
		}

		elseif( $position === 'summary' && ($acf_summary_gs = reycore__acf_get_field('product_content_after_summary')) ){

			if( $acf_summary_gs === 'none' ){
				return false;
			}

			return absint($acf_summary_gs);
		}

		return $gs;
	}

	/**
	 * Filter product page's css classes
	 * @since 1.0.0
	 */
	function product_page_classes($classes, $product)
	{
		if( is_product() && $product->get_id() === get_queried_object_id() ){
			$classes['product_page_class'] = 'rey-product';
		}

		return $classes;
	}

	/**
	 * Filter product page's css classes
	 * @since 1.0.0
	 */
	function body_classes($classes)
	{
		if( in_array('single-product', $classes) )
		{
			// Skin Class
			$classes['single_skin'] = 'single-skin--' . $this->get_single_active_skin();

			if( get_theme_mod('product_page_summary_fixed', false) ){
				// Fixed Summary
				$classes['fixed_summary'] = '--fixed-summary';
			}
		}

		return $classes;
	}

	private function set_single_skins(){
		$this->_skins = [
			'default' => __('Default', 'rey-core'),
			'fullscreen' => __('Full-screen Summary', 'rey-core'),
			'compact' => __('Compact Layout', 'rey-core'),
		];
	}

	public function get_single_skins(){
		return apply_filters('reycore/woocommerce/single_skins', $this->_skins);
	}

	public function get_single_active_skin(){
		return get_theme_mod('single_skin', 'default');
	}

	/**
	 * Load files
	 *
	 * @since 1.0.0
	 **/
	function load_includes(){

		foreach( $this->_skins as $skin => $name ){
			if( ($skin_path = sprintf("%sinc/woocommerce/tags/single-%s.php", REY_CORE_DIR, $skin)) && is_readable($skin_path) ){
				require $skin_path;
			}
		}

		// load product gallery
		require REY_CORE_DIR . "inc/woocommerce/tags/product-gallery/gallery-base.php";
	}


	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_WooCommerce_Single
	 */
	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
}
endif;

ReyCore_WooCommerce_Single::getInstance();
