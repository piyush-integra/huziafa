<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_Single__Default') ):

class ReyCore_WooCommerce_Single__Default extends ReyCore_WooCommerce_Single
{
	private static $_instance = null;

	const TYPE = 'default';

	private function __construct()
	{
		add_action('init', [$this, 'init']);
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
		if( $this->get_single_active_skin() !== self::TYPE ){
			return;
		}

		add_filter( 'woocommerce_post_class', [$this, 'product_page_classes'], 20, 2 );
		add_action( 'wp', [ $this, 'wp' ]);
	}

	function wp (){

		if( ! is_product() ){
			return;
		}

		if( $this->breadcrumb_enabled() ){
			add_action( 'woocommerce_single_product_summary', 'woocommerce_breadcrumb', 1 );
		}

		add_action( 'woocommerce_single_product_summary', [ ReyCore_WooCommerce_ProductNavigation::getInstance(), 'get_navigation' ], 1); // right after summary begins
	}

	/**
	 * Filter product page's css classes
	 */
	function product_page_classes($classes, $product)
	{
		if( (is_array($classes) && in_array('rey-product', $classes)) && get_theme_mod('single_skin_default_flip', false) == true ){
			$classes[] = '--reversed';
		}
		return $classes;
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

ReyCore_WooCommerce_Single__Default::getInstance();
