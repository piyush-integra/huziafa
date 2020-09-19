<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


if( !class_exists('ReyCore_WooCommerce_ProductGallery_Horizontal') ):
/**
 * This will initialize Rey's product page galleries
 */
class ReyCore_WooCommerce_ProductGallery_Horizontal extends ReyCore_WooCommerce_ProductGallery_Base
{
	private static $_instance = null;

	private function __construct()
	{
		parent::$params['gallery__enable_thumbs'] = true;

		$this->init_hooks();
	}

	function init_hooks(){
		add_filter( 'woocommerce_single_product_image_thumbnail_html', [$this, 'add_main_thumb'], 10, 2); // add main to image thumbs
		add_filter( 'woocommerce_single_product_image_thumbnail_html', [$this, 'add_image_datasets'], 10, 2); // add datasets to image thumbs
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_WooCommerce_ProductGallery_Horizontal
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

ReyCore_WooCommerce_ProductGallery_Horizontal::getInstance();
