<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists( 'WC_Bundles' ) && !class_exists('ReyCore_Compatibility__WooCommerceProductBundles') ):

	class ReyCore_Compatibility__WooCommerceProductBundles
	{
		private $settings = [];

		public function __construct()
		{
			add_action( 'init', [ $this, 'init' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'load_styles' ] );
		}

		public function init(){
			$this->settings = apply_filters('reycore/compat/wc_bundles/params', []);
		}

		public function load_styles(){
            wp_enqueue_style( 'reycore-wc-bundles-styles', REY_CORE_COMPATIBILITY_URI . basename(__DIR__) . '/style.css', [], REY_CORE_VERSION );
		}

	}

	new ReyCore_Compatibility__WooCommerceProductBundles();
endif;
