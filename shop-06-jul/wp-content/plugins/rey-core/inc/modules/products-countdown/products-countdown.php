<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( class_exists('WooCommerce') && !class_exists('ReyCore_Wc_ProductsCountdown') ):

class ReyCore_Wc_ProductsCountdown
{
	private $settings = [];

	public function __construct()
	{
		add_action( 'init', [$this, 'init'] );
	}

	public function init()
	{
		if( ! $this->is_enabled() ){
			return;
		}

		$this->set_settings();

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	private function set_settings(){
		$this->settings = apply_filters('reycore/module/product_countdown_settings', []);
	}

	public function enqueue_scripts(){
		wp_enqueue_style( 'reycore-product-countdown', REY_CORE_MODULE_URI . basename(__DIR__) . '/style.css', [], REY_CORE_VERSION );
		wp_enqueue_script( 'reycore-product-countdown', REY_CORE_MODULE_URI . basename(__DIR__) . '/script.js', ['rey-script', 'reycore-scripts', 'rey-woocommerce-script'], REY_CORE_VERSION , true);
	}

	public function is_enabled() {
		return false;
	}

}

new ReyCore_Wc_ProductsCountdown;

endif;
