<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Wishlist plugin integration
 * https://wordpress.org/plugins/ti-woocommerce-wishlist/
 *
 * @since 1.0.0
 */
if( !class_exists('ReyCore_WooCommerce_Wishlist') ):

/**
 * ReyCore_WooCommerce_Wishlist class.
 *
 * @since 1.0.0
 */
class ReyCore_WooCommerce_Wishlist {

	/**
	 * Holds the reference to the instance of this class
	 * @var ReyCore_WooCommerce_Wishlist
	 */
	private static $_instance = null;

	private function __construct() {

		if( ! class_exists('TInvWL_Public_AddToWishlist') ) {
			return;
		}

		// Disable wizard
		add_filter( 'tinvwl_enable_wizard', '__return_false', 10);
		add_filter( 'tinvwl_prevent_automatic_wizard_redirect', '__return_true', 10);
		add_action( 'wp_ajax_get_wishlist_data', [ $this, 'get_wishlist_data'] );
		add_action( 'wp_ajax_nopriv_get_wishlist_data', [ $this, 'get_wishlist_data'] );
		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 10 );
		add_filter( 'tinvwl_wishlist_item_thumbnail', [ $this, 'prevent_product_slideshows'], 10, 3 );
		add_action( 'wp_footer', [ $this, 'item_template']);
		add_action( 'woocommerce_single_product_summary', [ $this, 'show_add_to_wishlist_in_product_page_catalog_mode'], 20);

		add_action('init', function(){
			remove_action( 'woocommerce_before_shop_loop_item', 'tinvwl_view_addto_htmlloop', 9 );
			remove_action( 'woocommerce_after_shop_loop_item', 'tinvwl_view_addto_htmlloop', 9 );
			remove_action( 'woocommerce_after_shop_loop_item', 'tinvwl_view_addto_htmlloop', 10 );
		});
	}

	public static function tinvl_get_wishlist_default_position(){

		if( get_theme_mod('loop_wishlist_position', '') !== '' ){
			return get_theme_mod('loop_wishlist_position', 'bottom');
		}

		return tinv_get_option( 'add_to_wishlist_catalog', 'position' ) === 'above_thumb' ? 'topright' :  'bottom';
	}

	/**
	 * Filter main script's params
	 *
	 * @since 1.0.0
	 **/
	public function script_params($params)
	{
		$params['empty_text'] = esc_html__('Your Wishlist is currently empty.', 'rey-core');

		return $params;
	}


	/**
	 * Get wihslist
	 *
	 * @since   1.1.1
	 */
	public function get_wishlist_data()
	{
		echo do_shortcode('[ti_wishlistsview lists_per_page="6"]');
	}


	/**
	 * template used for wishlist items.
	 * @since 1.0.0
	 */
	public function item_template(){ ?>

		<script type="text/html" id="tmpl-reyWishlistItem">
			<# for (var i = 0; i < data.num; i++) { #>
				<div class="rey-wishlistItem clearfix" style="transition-delay: {{i * 0.07}}s ">
					<div class="rey-wishlistItem-thumbnail">{{{data.ob[i].img}}}</div>
					<div class="rey-wishlistItem-name">{{{data.ob[i].name}}}</div>
					<div class="rey-wishlistItem-price">{{{data.ob[i].price}}}</div>
				</div>
			<# } #>
		</script>

		<?php
	}

	/**
	* Add the icon, wrapped in custom div
	*
	* @since 1.0.0
	*/
	public static function get_button_html(){
		echo do_shortcode("[ti_wishlists_addtowishlist loop=yes]");
	}

	function show_add_to_wishlist_in_product_page_catalog_mode(){
		if( is_product() || get_query_var('rey__is_quickview', false) === true ) {
			$product = wc_get_product();
			if(
				! $product->is_purchasable() &&
				( $product->get_regular_price() || $product->get_sale_price() ||
					( $product->is_type( 'variable' ) && $product->get_price() !== '' )
				) ) {

				remove_action( 'woocommerce_single_product_summary', 'tinvwl_view_addto_htmlout', 29 );
				remove_action( 'woocommerce_single_product_summary', 'tinvwl_view_addto_htmlout', 31 );
				remove_action( 'woocommerce_before_add_to_cart_button', 'tinvwl_view_addto_html', 20 );
				remove_action( 'woocommerce_after_add_to_cart_button', 'tinvwl_view_addto_html', 0 );

				echo do_shortcode("[ti_wishlists_addtowishlist]");
			}
		}
	}

	function prevent_product_slideshows($html, $wl_product, $product){

		$product->set_catalog_visibility('hidden');
		$html = str_replace('rey-productSlideshow', 'rey-productSlideshow --prevent-thumbnail-sliders --show-first-only', $html);

		return $html;
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_WooCommerce_Wishlist
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

ReyCore_WooCommerce_Wishlist::getInstance();
