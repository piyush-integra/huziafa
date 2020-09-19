<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists('ReyCore_WooCommerce_Base') ):

class ReyCore_WooCommerce_Base {

	private static $_instance = null;

	/**
	 * Rest Endpoint
	 */
	const REY_ENDPOINT = 'rey/v1';

	/**
	 * Shop sidebar ID
	 */
	const SHOP_SIDEBAR_ID = 'shop-sidebar';

	private function __construct(){
		$this->init_hooks();
		$this->includes();
	}

	function includes(){
		require_once REY_CORE_DIR . 'inc/woocommerce/functions.php';
		// Load tags
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/general.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/mini-cart.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/wishlist.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/quickview.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/checkout.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/cart.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/brands.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/variations.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-tabs.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-product-navigation.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-store-notice.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-badges.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-video.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-related-products.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-estimated-delivery.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/loop.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/single.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/search.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/request_quote.php';
		//require_once REY_CORE_DIR . 'inc/woocommerce/tags/widget-product-categories.php';

	}

	function init_hooks()
	{
		// Remove default wrappers.
		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper' );
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end' );
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

		add_action( 'after_setup_theme', [ $this, 'add_support'], 10 );
		add_action( 'wp', [ $this, 'wp_actions'], 6);
		add_filter( 'woocommerce_enqueue_styles', [ $this, 'enqueue_styles'] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts'] );
		add_action( 'widgets_init', [ $this, 'register_sidebars'] );
		add_filter( 'rey/sidebar_name', [ $this, 'shop_sidebar'] );
		add_action( 'rey/get_sidebar', [ $this, 'get_shop_sidebar'] );
		add_filter( 'is_active_sidebar', [ $this, 'disable_product_sidebar'] );
		add_filter( 'rey/content/site_main_class', [ $this, 'main_classes'], 10 );
		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 10 );
		add_action( 'admin_bar_menu', [$this, 'shop_page_toolbar_edit_link'], 100);
		add_filter( 'body_class', [ $this, 'body_classes'], 20 );
		add_filter( 'rey/css_styles', [ $this, 'css_styles'] );
	}

	function add_support(){

		// Register theme features.
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );

		add_theme_support( 'woocommerce', [
			'product_grid::max_columns' => 6,
			'product_grid' => [
				'max_columns'=> 6
			],
		 ] );
	}

	/**
	 * General actions
	 * @since 1.0.0
	 **/
	public function wp_actions()
	{
		if( function_exists('rey_action__before_site_container') && function_exists('rey_action__after_site_container') ){
			// add rey wrappers
			add_action( 'woocommerce_before_main_content', 'rey_action__before_site_container', 0 );
			add_action( 'woocommerce_after_main_content', 'rey_action__after_site_container', 10 );
		}

		// disable shop functionality
		if( reycore_wc__is_catalog() ){
			add_filter( 'woocommerce_is_purchasable', '__return_false');
			if( get_theme_mod('shop_catalog_variable', true) === true ){
				remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
			}
		}

		// disable post thumbnail (featured-image) in woocommerce posts
		if ( is_woocommerce() ) {
			add_filter( 'rey__can_show_post_thumbnail', '__return_false' );
		}

		// force flexslider to be disabled
		add_filter( 'woocommerce_single_product_flexslider_enabled', '__return_false', 100 );
	}


	/**
	 * Register sidebars
	 *
	 * @since 1.0.0
	 **/
	public function register_sidebars()
	{
		register_sidebar( array(
			'name'          => esc_html__( 'Shop Sidebar', 'rey-core' ),
			'id'            => self::SHOP_SIDEBAR_ID,
			'description'   => esc_html__('This sidebar will be visible on the shop pages.' , 'rey-core'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		) );

		register_sidebar( array(
			'name'          => esc_html__( 'Filter Panel', 'rey-core' ),
			'id'            => 'filters-sidebar',
			'description'   => esc_html__('This sidebar should contain WooCommerce filter widgets.' , 'rey-core'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		) );

		register_sidebar( array(
			'name'          => esc_html__( 'Filter Top Bar', 'rey-core' ),
			'id'            => 'filters-top-sidebar',
			'description'   => esc_html__('This sidebar should contain WooCommerce filter widgets horizontally before the products.' , 'rey-core'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		) );
	}

	/**
	 * Show Shop sidebar
	 *
	 * @since 1.0.0
	 */
	public function shop_sidebar($sidebar)
	{
		if( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ){
			return self::SHOP_SIDEBAR_ID;
		}
		return $sidebar;
	}

	/**
	 * Get Shop Sidebar
	 * @hooks to rey/get_sidebar
	 */
	public function get_shop_sidebar( $position )
	{
		if(
			( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) &&
			is_active_sidebar(self::SHOP_SIDEBAR_ID) &&
			get_theme_mod('catalog_sidebar_position', 'right') == $position
		) {
			get_sidebar();
		}
	}


	/**
	 * Disable sidebar on product pages
	 *
	 * @since 1.0.0
	 */
	public function disable_product_sidebar( $status ) {

		global $wp_query;

		if ( $wp_query->is_singular && $wp_query->get('post_type') === 'product' ) {
			return false;
		}

		return $status;
	}

	/**
	 * Filter main wrapper's css classes
	 *
	 * @since 1.0.0
	 **/
	public function main_classes($classes)
	{
		if( ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) && is_active_sidebar(self::SHOP_SIDEBAR_ID) && get_theme_mod('catalog_sidebar_position', 'right') !== 'disabled' ) {
			$classes[] = '--has-sidebar';
		}

		return $classes;
	}

	/**
	 * Enqueue CSS for this theme.
	 *
	 * @param  array $styles Array of registered styles.
	 * @return array
	 */
	public function enqueue_styles( $styles )
	{
		// Override WooCommerce layout styles
		$styles['woocommerce-layout'] = [
			'src'     => REY_CORE_URI . 'assets/css/woocommerce-layout.css',
			'deps'    => '',
			'version' => REY_CORE_VERSION,
			'media'   => 'all',
			'has_rtl' => true,
		];

		// Override WooCommerce general styles
		$styles['woocommerce-general'] = [
			'src'     => REY_CORE_URI . 'assets/css/woocommerce.css',
			'deps'    => '',
			'version' => REY_CORE_VERSION,
			'media'   => 'all',
			'has_rtl' => true,
		];

		if( isset($styles['woocommerce-smallscreen']) ){
			// disable smallscreen stylesheet
			unset( $styles['woocommerce-smallscreen'] );
		}

		// WooCommerce header components
		$styles['rey-woocommerce-header'] = [
			'src'     => REY_CORE_URI . 'assets/css/woocommerce-header.css',
			'deps'    => ['rey-wp-style'],
			'version' => REY_CORE_VERSION,
			'media'   => 'all',
			'has_rtl' => false,
		];

		return $styles;
	}

	/**
	 * Enqueue scripts for WooCommerce
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts()
	{
		// WooCommerce scripts
		wp_enqueue_script( 'animejs' );
		wp_enqueue_script( 'simple-scrollbar');
		wp_enqueue_script( 'rey-woocommerce-script', REY_CORE_URI . 'assets/js/woocommerce.js', ['rey-script', 'reycore-scripts'], REY_CORE_VERSION, true );
		wp_enqueue_script( 'rey-product-gallery-script', REY_CORE_URI . 'assets/js/product-gallery.js', ['rey-script', 'reycore-scripts', 'wc-single-product'], REY_CORE_VERSION, true );
	}

	/**
	 * Filter main script's params
	 *
	 * @since 1.0.0
	 **/
	public function script_params($params)
	{
		$params['woocommerce'] = true;
		$params['rest_url'] = esc_url_raw( rest_url( self::REY_ENDPOINT ) );
		$params['rest_nonce'] = wp_create_nonce( 'wp_rest' );
		$params['ajaxurl'] = admin_url( 'admin-ajax.php' );
		$params['ajax_nonce'] = wp_create_nonce( 'rey_nonce' );
		$params['catalog_cols'] = reycore_wc_get_columns('desktop');
		$params['added_to_cart_text'] = reycore__texts('added_to_cart_text');
		$params['cannot_update_cart'] = reycore__texts('cannot_update_cart');
		$params['site_id'] = is_multisite() ? get_current_blog_id() : 0;
		$params['checkout_url'] = get_permalink( wc_get_page_id( 'checkout' ) );
		$params['after_add_to_cart'] = get_theme_mod('product_page_after_add_to_cart_behviour', 'cart');
		$params['loop_ajax_variable_products'] = get_theme_mod('loop_ajax_variable_products', false);
		$params['js_params'] = [
			'scattered_grid_max_items' => 7,
			'scattered_grid_custom_items' => [],
			'product_item_slideshow_nav' => get_theme_mod('loop_slideshow_nav', 'dots'),
			'product_item_slideshow_dots_hover' => get_theme_mod('loop_slideshow_nav', 'dots') !== 'arrows' && get_theme_mod('loop_slideshow_nav_hover_dots', true),
			'product_item_slideshow_disable_mobile' => false,
			'scroll_top_after_variation_change' => get_theme_mod('product_page_scroll_top_after_variation_change', true),
			'product_page_mobile_gallery_nav' => get_theme_mod('product_gallery_mobile_nav_style', 'bars'),
			'product_page_mobile_gallery_nav_thumbs' => 4,
			'product_page_mobile_gallery_arrows' => get_theme_mod('product_gallery_mobile_arrows', false),
			'equalize_product_items' => [],
			'gallery_icon' => '',
			'ajax_search_letter_count' => 3,
			// 'gallery_icon' => 'reycore-icon-zoom',
		];

		if( get_theme_mod('product_items_eq', false) ){
			$params['js_params']['equalize_product_items'] = [
				'.woocommerce-loop-product__title',
				// '.rey-productVariations',
			];
		}

		return $params;
	}

	/**
	 * Add Edit Page toolbar link for Shop Page
	 *
	 * @since 1.0.0
	 */
	function shop_page_toolbar_edit_link( $admin_bar ){
		if( is_shop() ){
			$admin_bar->add_menu( array(
				'id'    => 'edit',
				'title' => __('Edit Shop Page', 'rey-core'),
				'href'  => get_edit_post_link( wc_get_page_id('shop') ),
				'meta'  => array(
					'title' => __('Edit Shop Page', 'rey-core'),
				),
			));
		}
	}

	/**
	 * Filter body css classes
	 * @since 1.0.0
	 */
	function body_classes($classes)
	{
		if( get_theme_mod('shop_catalog', false) == true ) {
			$classes[] = '--catalog-mode';
		}

		return $classes;
	}

	/**
	 * Filter css styles
	 * @since 1.1.2
	 */
	function css_styles($styles)
	{
		$styles[] = sprintf( ':root{ --woocommerce-grid-columns:%d; }', reycore_wc_get_columns('desktop') );
		return $styles;
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_WooCommerce_Base
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

ReyCore_WooCommerce_Base::getInstance();
