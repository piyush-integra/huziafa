<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_ProductBadges') ):

class ReyCore_WooCommerce_ProductBadges
{

	public $type = false;
	public $product_id = false;

	public function __construct() {
		add_action('wp', [$this, 'init_badges']);
	}

	function init_badges(){

		$available_positions = apply_filters('reycore/woocommerce/product_badges_positions', [
			'top_left' => 'reycore/loop_inside_thumbnail/top-left',
			'top_right' => 'reycore/loop_inside_thumbnail/top-right',
			'bottom_left' => 'reycore/loop_inside_thumbnail/bottom-left',
			'bottom_right' => 'reycore/loop_inside_thumbnail/bottom-right',
			'before_title' => ['woocommerce_before_shop_loop_item_title', 13],
			'after_content' => ['woocommerce_after_shop_loop_item', 999],
		]);

		foreach( $available_positions as $key => $pos ){

			if( is_array($pos) ){
				$hook_position = $pos[0];
				$hook_priority = $pos[1];
			}
			else {
				$hook_position = $pos;
				$hook_priority = 10;
			}

			add_action( $hook_position, [ $this, 'render_badge_position_' . $key ], $hook_priority, 1 );
		}
	}

	function get_position(){
		return reycore__acf_get_field('badge_position', get_the_ID() );
	}

	function render_badge_position_top_left(){
		if( $this->get_position() === 'top_left' ){
			$this->render_badge();
		}
	}

	function render_badge_position_top_right(){
		if( $this->get_position() === 'top_right' ){
			$this->render_badge();
		}
	}

	function render_badge_position_bottom_left(){
		if( $this->get_position() === 'bottom_left' ){
			$this->render_badge();
		}
	}

	function render_badge_position_bottom_right(){
		if( $this->get_position() === 'bottom_right' ){
			$this->render_badge();
		}
	}

	function render_badge_position_before_title(){
		if( $this->get_position() === 'before_title' ){
			$this->render_badge();
		}
	}

	function render_badge_position_after_content(){
		if( $this->get_position() === 'after_content' ){
			$this->render_badge();
		}
	}

	public function render_badge(){

		$product = wc_get_product();

		if( ! $product ){
			return;
		}

		$this->product_id = $product->get_id();

		$this->type = reycore__acf_get_field('badge_type', $this->product_id);

		if( !$this->type ){
			return;
		}

		if( $this->type === 'text'){
			$this->render_text();
		}
		else if( $this->type === 'image'){
			$this->render_image();
		}
	}

	function render_text(){

		$product_id = $this->product_id;
		$text = reycore__acf_get_field('badge_text', $product_id);

		if( $text ){

			$classes[] = reycore__acf_get_field('badge_show_on_mobile', $product_id) ? '--show-mobile' : '';
			$classes[] = '--' . $this->get_position();

			$styles = [];

			if( $text_color = reycore__acf_get_field('badge_text_color', $product_id) ){
				$styles['text_color'] = '--badge-text-color:' . $text_color;
			}

			if( $bg_color = reycore__acf_get_field('badge_text_bg_color', $product_id) ){
				$styles['bg_color'] = '--badge-bg-color:' . $bg_color;
			}

			foreach(['', '_tablet', '_mobile'] as $breakpoint){
				if( $text_size = reycore__acf_get_field('badge_text_size' . $breakpoint , $product_id) ){
					$styles['text_size' . $breakpoint] = sprintf( '--badge-text-size%s:%spx', $breakpoint, $text_size );
				}
			}

			printf('<div class="rey-pBadge --text %2$s" style="%3$s"><span>%1$s</span></div>', $text, esc_attr( implode(' ', $classes) ), esc_attr( implode(';', $styles) ) );
		}
	}

	function render_image(){

		$product_id = $this->product_id;
		$images = reycore__acf_get_field('badge_images', $product_id);

		if( $images ){
			$images_html = '';

			foreach( $images as $image_id ){
				$images_html .= wp_get_attachment_image( $image_id['select_image'] );
			}

			$classes[] = reycore__acf_get_field('badge_show_on_mobile', $product_id) ? '--show-mobile' : '';
			$classes[] = '--' . $this->get_position();

			$styles = [];

			foreach(['', '_tablet', '_mobile'] as $breakpoint){
				if( $image_size = reycore__acf_get_field('badge_image_size' . $breakpoint , $product_id) ){
					$styles['image_size' . $breakpoint] = sprintf( '--badge-image-size%s:%spx', $breakpoint, $image_size );
				}
			}

			printf('<div class="rey-pBadge --image %2$s" style="%3$s">%1$s</div>',
				$images_html,
				esc_attr( implode(' ', $classes) ),
				esc_attr( implode(';', $styles) )
			);
		}
	}

}

new ReyCore_WooCommerce_ProductBadges;

endif;
