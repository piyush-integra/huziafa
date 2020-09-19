<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


if( !class_exists('ReyCore_WooCommerce_ProductGallery_Base') ):
/**
 * This will initialize Rey's product page galleries
 */
class ReyCore_WooCommerce_ProductGallery_Base
{
	private static $_instance = null;

	public static $params = [];

	private function __construct()
	{
		$this->init_hooks();
	}

	function init_hooks(){
		add_action( 'wp', [$this, 'load_current_gallery_type']);
		add_filter( 'woocommerce_single_product_image_gallery_classes', [$this, 'gallery_classes'], 10);
		add_filter( 'rey/main_script_params', [$this, 'add_script_params'], 10 );
		add_action( 'woocommerce_before_single_product_summary', [$this, 'mobile_gallery'], 5);
		add_filter( 'woocommerce_single_product_image_thumbnail_html', [$this, 'add_zoom_custom_target'], 20);
		add_filter( 'woocommerce_single_product_zoom_enabled', [$this, 'enable_gallery_zoom']);
		add_filter( 'woocommerce_single_product_photoswipe_enabled', [$this, 'enable_photoswipe']);
		add_filter( 'woocommerce_single_product_zoom_options', [$this,'add_zoom_custom_target_option']);
		add_filter( 'body_class', [ $this, 'body_classes'], 30 );
		add_filter('woocommerce_get_image_size_gallery_thumbnail', [$this, 'disable_thumbs_cropping']);
	}

	function load_current_gallery_type()
	{
		$gallery_type = $this->get_active_gallery_type();
		$gallery_skin_path = REY_CORE_DIR . "inc/woocommerce/tags/product-gallery/gallery-{$gallery_type}.php";

		if( is_readable($gallery_skin_path) ){
			require_once $gallery_skin_path;
		}
	}

	function get_active_gallery_type(){
		return get_theme_mod('product_gallery_layout', 'vertical');
	}


	/**
	 * Adds the main image's thumb
	 * Used for Vertical, Horizontal.
	 *
	 * @since 1.0.0
	 */
	function add_main_thumb($html, $post_thumbnail_id){
		$product = wc_get_product();
		$thumb_html = '';

		if( $product && $post_thumbnail_id === $product->get_image_id() && ($gallery_image_ids = $product->get_gallery_image_ids()) && count($gallery_image_ids) !== 0 ){
			$thumb_html = wc_get_gallery_image_html( $post_thumbnail_id );
		}

		return $html . $thumb_html;
	}

	/**
	 * Add dataset attributes for image gallery's thumbs
	 * They'll be used for transferring their full-src to active image & zoom functionality
	 * Used for Vertical, Horizontal.
	 *
	 * @since 1.0.0
	 */
	function add_image_datasets($html, $post_thumbnail_id)
	{
		// Add Preview Source URL
		$preview_src = wp_get_attachment_image_src($post_thumbnail_id, 'woocommerce_single');
		$preview_srcset = wp_get_attachment_image_srcset($post_thumbnail_id, 'woocommerce_single');
		$preview_sizes = wp_get_attachment_image_sizes($post_thumbnail_id, 'woocommerce_single');

		if( isset($preview_src[0]) && !empty($preview_srcset) ){
			$attributes = 'data-preview-src="' . $preview_src[0] . '"';
			$attributes .= ' data-preview-srcset="' . $preview_srcset . '"';
			$attributes .= ' data-preview-sizes="' . $preview_sizes . '"';
			$attributes .= ' data-src';

			$html = str_replace('data-src', $attributes, $html);
		}

		return $html;
	}

	/**
	 * Replace all thumbs with `woocommerce_single` sized images.
	 * Used for Grid,
	 *
	 * @since 1.0.0
	 */
	function thumbs_to_single_size($html, $post_thumbnail_id)
	{
		if( $post_thumbnail_id ){
			$html = wc_get_gallery_image_html( $post_thumbnail_id, true );
		}

		return $html;
	}

	/**
	 * Filter Gallery wrapper classes
	 *
	 * @since 1.0.0
	 **/
	function gallery_classes($classes)
	{
		$classes['loading'] = '--is-loading';

		$classes['gallery_type'] = 'woocommerce-product-gallery--' . esc_attr( $this->get_active_gallery_type() );

		if( $this->is_gallery_with_thumbs() ){

			if( get_theme_mod('product_gallery_thumbs_flip', false) ){
				$classes['gallery_flip_thumbs'] = '--flip-thumbs';
			}

			if( ($max_thumbs = get_theme_mod('product_gallery_thumbs_max', '')) && absint($max_thumbs) > 1 ){
				$classes['gallery_max_thumbs'] = '--max-thumbs';
			}

			$classes['gallery_thumbs_style'] = '--thumbs-nav-' . get_theme_mod('product_gallery_thumbs_nav_style', 'boxed');

			if( get_theme_mod('product_gallery_thumbs_disable_cropping', false) ){
				$classes['gallery_thumbs_nocrop'] = '--thumbs-no-crop';
			}

		}

		if(
			$this->get_active_gallery_type() === 'horizontal' &&
			get_theme_mod('custom_main_image_height', false)
		){
				$classes['main-image-container-height'] = '--main-img-height';

		}

		return $classes;
	}

	function is_gallery_with_thumbs(){
		return $this->get_active_gallery_type() === 'vertical' || $this->get_active_gallery_type() === 'horizontal';
	}

	/**
	 * Filter product page's css classes
	 * @since 1.0.0
	 */
	function body_classes($classes)
	{
		if( in_array('single-product', $classes) )
		{
			if( $this->is_gallery_with_thumbs() ){
				unset($classes['fixed_summary']);
			}

			$classes['gallery_type'] = '--gallery-' . $this->get_active_gallery_type();
		}

		return $classes;
	}

	/**
	 * Script params
	 *
	 * @since 1.0.0
	 **/
	function add_script_params($params)
	{
		$params['active_gallery_type'] = $this->get_active_gallery_type();
		$params['product_page_gallery_max_thumbs'] = get_theme_mod('product_gallery_thumbs_max', '');
		$params['product_page_gallery_thumbs_nav_style'] = get_theme_mod('product_gallery_thumbs_nav_style', 'boxed');
		$params['cascade_bullets'] = get_theme_mod('single_skin_cascade_bullets', true);
		$params['product_page_gallery_arrows'] = get_theme_mod('product_page_gallery_arrow_nav', false);

		if( !empty(self::$params) ){
			foreach (self::$params as $k => $p) {
				$params[$k] = $p;
			}
		}

		return $params;
	}

	/**
	 * Prepare mobile gallery slider
	 *
	 * @since 1.0.0
	 */
	public function mobile_gallery( $image_ids = [], $product_id = 0 )
	{
		if( ! apply_filters('reycore/woocommerce/allow_mobile_gallery', true) ){
			return;
		}

		$gallery_html = '';
		$product = $product_id ? wc_get_product($product_id) : wc_get_product();
		$gallery_images = [];
		$size = 'woocommerce_single';

		if( !empty($image_ids) ){
			$gallery_image_ids = $image_ids;
		}
		else {
			$gallery_image_ids = reycore_wc__get_product_images_ids();
		}

		// get gallery
		if( $product && !empty($gallery_image_ids) ){

			foreach ($gallery_image_ids as $key => $gallery_img_id) {
				$gallery_img = wp_get_attachment_image_src($gallery_img_id, $size);
				if( $gallery_img ){
					$gallery_images[] = sprintf('<div><img class="woocommerce-product-gallery__mobile--%5$s" src="%1$s" data-index="%2$s" data-no-lazy="1" data-skip-lazy="1" data-full=\'%3$s\' title="%4$s"/></div>',
						$gallery_img[0],
						$key + 1,
						wp_json_encode( wp_get_attachment_image_src($gallery_img_id, 'full') ),
						get_the_title($gallery_img_id),
						$key
					);
				}
			}
		}

		if( empty($gallery_images) ){
			return;
		}

		$nav = get_theme_mod('product_gallery_mobile_nav_style', 'bars');

		$gallery_html .= sprintf('<div class="woocommerce-product-gallery__mobile --loading %2$s">%1$s</div>', implode('', $gallery_images), '--nav-' . $nav );

		echo wp_kses_post( $gallery_html );

	}

	/**
	 * Enable Gallery Zoom on hover
	 * @since 1.0.0
	 */
	function enable_gallery_zoom(){
		return get_theme_mod('product_page_gallery_zoom', true);
	}

	/**
	 * Enable Gallery Zoom on hover
	 * @since 1.6.1
	 */
	function enable_photoswipe(){
		return get_theme_mod('product_page_gallery_lightbox', true);
	}

	/**
	 * Adds custom target container for zoom image
	 * @since 1.0.0
	 */
	function add_zoom_custom_target($html)
	{
		if( apply_filters( 'woocommerce_single_product_zoom_enabled', get_theme_support( 'wc-product-gallery-zoom' ) ) ){
			return str_replace('</a>', '</a><div class="rey-zoomContainer"></div>', $html);
		}
		return $html;
	}

	/**
	 * Specifies the custom zoom target
	 * @since 1.0.0
	 */
	function add_zoom_custom_target_option($options){
		$options['target'] = '.rey-zoomContainer';
		return $options;
	}

	/**
	 * Adds animation class to gallery item
	 *
	 * @since 1.0.0
	 */

	function add_animation_classes($html, $post_thumbnail_id)
	{
		$product = wc_get_product();

		if( $product && ($main_image_id = $product->get_image_id()) && $main_image_id === $post_thumbnail_id){
			return $html;
		}

		return str_replace('woocommerce-product-gallery__image', 'woocommerce-product-gallery__image --animated-entry', $html);
	}

	function disable_thumbs_cropping($size){

		if( get_theme_mod('product_gallery_thumbs_disable_cropping', false) ){
			$size['height']  = 9999;
			$size['crop']   = false;
		}

		return $size;
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_WooCommerce_ProductGallery_Base
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

ReyCore_WooCommerce_ProductGallery_Base::getInstance();
