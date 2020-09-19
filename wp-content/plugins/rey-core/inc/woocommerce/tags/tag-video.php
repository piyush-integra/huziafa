<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_ProductVideos') ):

	class ReyCore_WooCommerce_ProductVideos
	{
		public $video_url;
		public $product_id;

		public function __construct() {
			add_action('wp', [$this, 'init']);
		}

		function init(){

			if( ! is_product() ){
				return;
			}

			$product = wc_get_product();

			if( ! $product ){
				return;
			}

			$this->product_id = $product->get_id();
			$this->video_url = reycore__acf_get_field('product_video_url', $this->product_id );

			if( ! $this->video_url ){
				return;
			}

			$this->add_to_summary();

			add_action('woocommerce_before_single_product_summary', [$this, 'add_video_into_gallery']);
		}

		function add_video_into_gallery(){
			$this->print_button([
				'text' => '',
				'class' => 'rey-singlePlayVideo d-none'
			]);

		}

		function add_video_attribute($attr, $attachment_id, $image_size, $main_image){
			if( $main_image && reycore__acf_get_field('product_video_main_image', $this->product_id ) !== false ){
				$attr['data-product-video'] = esc_attr($this->video_url);
			}
			return $attr;
		}

		function summary_button(){
			$this->print_button();
		}

		function print_button( $args = [] ){

			$args = wp_parse_args($args, [
				'text' => esc_html__('PLAY PRODUCT VIDEO', 'rey-core'),
				'title_attr' => esc_html__('PLAY PRODUCT VIDEO', 'rey-core'),
				'class' => 'btn btn-line u-btn-icon-sm',
				'icon' => reycore__get_svg_icon__core(['id' => 'reycore-icon-play'])
			]);

			$options = [
				'iframe' => esc_url($this->video_url),
				'width' => 750,
			];

			if( $width = reycore__acf_get_field('product_video_modal_size', $this->product_id ) ){
				$options['width'] = absint($width);
			}

			$button = sprintf('<span class="%4$s" title="%5$s" data-elementor-open-lightbox="no" data-reymodal=\'%1$s\'>%3$s %2$s</span>',
				wp_json_encode($options),
				$args['text'],
				$args['icon'],
				$args['class'],
				$args['title_attr']
			);

			echo apply_filters( 'reycore/woocommerce/video_button', $button, $args );
		}

		function add_to_summary(){

			if( ! ($summary_position = reycore__acf_get_field('product_video_summary', $this->product_id )) ) {
				return;
			}

			if( $summary_position === 'disabled' ){
				return;
			}

			$available_positions = [
				'after_title'         => 6,
				'before_add_to_cart'  => 29,
				'before_product_meta' => 39,
				'after_product_meta'  => 41,
				'after_share'         => 51,
			];

			add_action( 'woocommerce_single_product_summary', [ $this, 'summary_button' ], $available_positions[$summary_position] );
		}

	}

	new ReyCore_WooCommerce_ProductVideos;

endif;
