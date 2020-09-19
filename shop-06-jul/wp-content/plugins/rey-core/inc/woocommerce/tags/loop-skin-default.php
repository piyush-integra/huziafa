<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_Loop_Skin_Default') ):
	/**
	 * Default Products loop skin (almost default WooCommerce)
	 */
	class ReyCore_WooCommerce_Loop_Skin_Default extends ReyCore_WooCommerce_Loop
	{
		const TYPE = 'default';

		public function __construct()
		{
			add_action( 'init', [$this, 'init'] );
		}

		public function init()
		{
			if ( is_customize_preview() ) {
				add_action( 'customize_preview_init', [$this, 'load_skin_hooks'] );
				return;
			}
			$this->load_skin_hooks();
		}

		public function load_skin_hooks()
		{
			if( $this->get_loop_active_skin() !== self::TYPE ){
				return;
			}

			add_action( 'woocommerce_before_shop_loop_item', [$this, 'apply_extra_thumbs_filter'], 10);
			add_action( 'woocommerce_after_shop_loop_item', [$this, 'remove_extra_thumbs_filter'], 10);

			add_filter( 'post_class', [$this,'custom_css_classes'], 20 );
			add_filter( 'product_cat_class', [$this,'custom_css_classes'], 20 );

			do_action( 'reycore/woocommerce/loop/after_skin_init', $this, self::TYPE);
		}

		/**
		 * Exclusion template
		 */
		// function exclude_components( $components ){
		// 	unset($components['brands']);
		// 	unset($components['wishlist']);
		// 	return $components;
		// }

		/**
		 * Adds custom CSS Classes
		 *
		 * @since 1.1.2
		 */
		function custom_css_classes( $classes )
		{
			if( is_admin() ){
				return $classes;
			}

			if( $general_css_classes = $this->general_css_classes() ){
				$classes = array_merge($classes, $general_css_classes);
			}

			return $classes;
		}

	}
	new ReyCore_WooCommerce_Loop_Skin_Default;
endif;
