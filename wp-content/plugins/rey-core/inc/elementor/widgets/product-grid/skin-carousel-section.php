<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Widget_Product_Grid__Carousel_Section') ):

	class ReyCore_Widget_Product_Grid__Carousel_Section extends \Elementor\Skin_Base
	{
		public $images = [];

		public function get_id() {
			return 'carousel-section';
		}

		public function get_title() {
			return __( 'Carousel Section', 'rey-core' );
		}

		public function get_script_depends() {
			return [ 'jquery-slick' ];
		}

		protected function _register_controls_actions() {
			parent::_register_controls_actions();

			add_action( 'elementor/element/reycore-product-grid/section_layout/after_section_end', [ $this, 'register_carousel_controls' ] );
		}

		public function register_carousel_controls( $element ){

			$element->start_controls_section(
				'section_carousel_section_settings',
				[
					'label' => __( 'Carousel Settings', 'rey-core' ),
					'condition' => [
						'_skin' => 'carousel-section',
					],
				]
			);

			$element->add_control(
				'cs_autoplay',
				[
					'label' => __( 'Autoplay', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'yes',
					'options' => [
						'yes' => __( 'Yes', 'rey-core' ),
						'no' => __( 'No', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'cs_autoplay_speed',
				[
					'label' => __( 'Autoplay Speed', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 5000,
					'condition' => [
						'cs_autoplay' => 'yes',
					],
				]
			);

			$element->add_control(
				'cs_speed',
				[
					'label' => __( 'Animation Speed', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 500,
				]
			);

			$element->add_control(
				'cs_dots',
				[
					'label' => __( 'Dots Navigation', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => __( 'Disabled', 'rey-core' ),
						'before'  => __( 'Before', 'rey-core' ),
						'after'  => __( 'After', 'rey-core' ),
					],
				]
			);

			$element->end_controls_section();
		}

		/**
		 * Prints dots HTML
		 *
		 * @since 1.0.0
		 */
		public function show_dots($position, $total = 0){
			$dots = $this->parent->get_settings_for_display('cs_dots');
			if( $dots == $position ){
				echo '<div class="reyEl-productGrid-cs-dots reyEl-productGrid-cs-dots--'.$position.'">';
				for($i = 0; $i < $total; $i++){
					echo ' <button></button> ';
				}
				echo '</div>';
			}
		}

		function loop_start()
		{
			wc_set_loop_prop( 'loop', 0 );

			printf('<ul class="products %s">', implode(' ', [
				'--prevent-thumbnail-sliders', // make sure it does not have thumbnail slideshow
				'--prevent-scattered', // make sure scattered is not applied
				'--prevent-masonry', // make sure masonry is not applied
			]) );
		}

		function loop_end(){
			echo '</ul>';
		}

		/**
		 * Render widget output on the frontend.
		 *
		 * Written in PHP and used to generate the final HTML.
		 *
		 * @since 1.0.0
		 * @access public
		 */
		public function render() {

			$this->parent->get_query_args();

			$products = $this->parent->get_query_results();

			if ( $products && $products->ids ) {

				$settings = $this->parent->get_settings_for_display();

				foreach ( $products->ids as $product_id ) {
					// assign images
					$this->images[] = get_the_post_thumbnail_url( $product_id, 'full' );
				}

				// Hide thumbnails
				$settings['hide_thumbnails'] = 'yes';

				$this->parent->add_render_attribute( 'wrapper', 'data-carousel-section-settings', wp_json_encode([
					'autoplay' => esc_attr($settings['cs_autoplay']),
					'autoplay_speed' => esc_attr($settings['cs_autoplay_speed']),
					'speed' => esc_attr($settings['cs_speed']),
					'dots' => $settings['cs_dots']
				]) );

				$this->parent->render_start( $settings, $products );

				// Show dots before
				$this->show_dots('before', $products->total);

					$this->loop_start();

					$this->parent->render_products( $products );

					$this->loop_end();

					// Show dots after
					$this->show_dots('after', $products->total);

				$this->parent->render_end();

				// slideshow markup
				if( !empty($this->images) ){
					echo '<div class="rey-section-slideshow--template" data-cs-id="'. $this->parent->get_id() .'" id="tmpl-slideshow-tpl-'. $this->parent->get_id() .'">';
						foreach($this->images as $i => $item){
							printf( '<div class="rey-section-slideshowItem rey-section-slideshowItem--%s" style="background-image: url(%s);"></div>', $i, $item);
						}
					echo '</div>';
				}
			}
			else {
				wc_get_template( 'loop/no-products-found.php' );
			}
		}

	}
endif;
