<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Widget_Product_Grid__Carousel') ):

	class ReyCore_Widget_Product_Grid__Carousel extends \Elementor\Skin_Base
	{

		public function get_id() {
			return 'carousel';
		}

		public function get_title() {
			return __( 'Carousel', 'rey-core' );
		}

		public function get_script_depends() {
			return [ 'jquery-slick' ];
		}

		protected function _register_controls_actions() {
			parent::_register_controls_actions();

			add_action( 'elementor/element/reycore-product-grid/section_layout/after_section_end', [ $this, 'register_carousel_controls' ] );
			add_action( 'elementor/element/reycore-product-grid/section_styles_general/after_section_end', [ $this, 'register_carousel_styles' ] );
		}

		public function register_carousel_controls( $element ){

			$element->start_injection( [
				'of' => 'per_row',
			] );

			$slides_to_show = range( 1, 10 );
			$slides_to_show = array_combine( $slides_to_show, $slides_to_show );

			$element->add_responsive_control(
				'slides_to_show',
				[
					'label' => __( 'Slides to Show', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'options' => [
						'' => __( 'Default', 'rey-core' ),
					] + $slides_to_show,
					'condition' => [
						'_skin' => 'carousel',
					],
					'selectors' => [
						'{{WRAPPER}} ul.products' => '--woocommerce-grid-columns: {{VALUE}}',
					],
					'render_type' => 'template'
				]
			);

			$element->add_responsive_control(
				'slides_to_scroll',
				[
					'label' => __( 'Slides to Scroll', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'description' => __( 'Set how many slides are scrolled per swipe.', 'rey-core' ),
					'options' => [
						'' => __( 'Default', 'rey-core' ),
					] + $slides_to_show,
					'condition' => [
						// 'slides_to_show!' => '1',
						'_skin' => 'carousel',
					],
					'render_type' => 'template'
				]
			);

			$element->end_injection();


			$element->start_controls_section(
				'section_carousel_settings',
				[
					'label' => __( 'Carousel Settings', 'rey-core' ),
					'condition' => [
						'_skin' => 'carousel',
					],
				]
			);

			$element->add_control(
				'pause_on_hover',
				[
					'label' => __( 'Pause on Hover', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'yes',
					'options' => [
						'yes' => __( 'Yes', 'rey-core' ),
						'no' => __( 'No', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'autoplay',
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
				'autoplay_speed',
				[
					'label' => __( 'Autoplay Speed', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 5000,
				]
			);

			$element->add_control(
				'infinite',
				[
					'label' => __( 'Infinite Loop', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'yes',
					'options' => [
						'yes' => __( 'Yes', 'rey-core' ),
						'no' => __( 'No', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'effect',
				[
					'label' => __( 'Effect', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'slide',
					'options' => [
						'slide' => __( 'Slide', 'rey-core' ),
						'fade' => __( 'Fade', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'speed',
				[
					'label' => __( 'Animation Speed', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 500,
				]
			);

			$element->add_control(
				'direction',
				[
					'label' => __( 'Direction', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'ltr',
					'options' => [
						'ltr' => __( 'Left', 'rey-core' ),
						'rtl' => __( 'Right', 'rey-core' ),
					],
				]
			);

			// Navigation
			$element->add_control(
				'carousel_arrows',
				[
					'label' => esc_html__( 'Enable Arrows', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'separator' => 'before'
				]
			);

			$element->add_control(
				'carousel_nav_notice',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'raw' => esc_html__( 'Did you know you can use "Slider Navigation" element to control this carousel, and place it everywhere? Read below on the Carousel Unique ID option.', 'rey-core' ),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
					'condition' => [
						'carousel_arrows!' => '',
					],
				]
			);


			$element->add_control(
				'carousel_id',
				[
					'label' => __( 'Carousel Unique ID', 'rey-core' ),
					'label_block' => true,
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => uniqid('carousel-'),
					'placeholder' => __( 'eg: some-unique-id', 'rey-core' ),
					'description' => __( 'Copy the ID above and paste it into the "Toggle Boxes" Widget or "Slider Navigation" widget where specified. No hashtag needed. Read more on <a href="https://support.reytheme.com/kb/products-grid-element/#adding-custom-navigation" target="_blank">how to connect them</a>.', 'rey-core' ),
					'separator' => 'before'
				]
			);

			$element->add_control(
				'disable_acc_outlines',
				[
					'label' => esc_html__( 'Disable accesibility outlines', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'prefix_class' => '--disable-acc-outlines-',
				]
			);

			$element->end_controls_section();

		}

		function register_carousel_styles( $element ){

			$element->start_controls_section(
				'section_carousel_arrows_styles',
				[
					'label' => __( 'Arrows styles', 'rey-core' ),
					'condition' => [
						'_skin' => 'carousel',
						'carousel_arrows!' => '',
					],
				]
			);

			$element->add_control(
				'carousel_arrows_position',
				[
					'label' => esc_html__( 'Arrows position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'inside',
					'options' => [
						'inside'  => esc_html__( 'Inside (over first/last products)', 'rey-core' ),
						'outside'  => esc_html__( 'Outside', 'rey-core' ),
					],
					'prefix_class' => '--carousel-navPos-'
				]
			);

			$element->add_control(
				'carousel_arrows_show_on_hover',
				[
					'label' => esc_html__( 'Show on hover only', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'prefix_class' => '--show-on-hover-'
				]
			);

			$element->add_control(
				'carousel_arrows_type',
				[
					'label' => esc_html__( 'Arrows Type', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( 'Default', 'rey-core' ),
						'chevron'  => esc_html__( 'Chevron', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'carousel_arrows_size',
				[
					'label' => esc_html__( 'Arrows size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 5,
					'max' => 200,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg' => 'font-size: {{VALUE}}px',
					],
				]
			);

			$element->add_responsive_control(
				'carousel_arrows_padding',
				[
					'label' => __( 'Arrows Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$element->start_controls_tabs( 'tabs_styles');

				$element->start_controls_tab(
					'tab_default',
					[
						'label' => __( 'Default', 'rey-core' ),
					]
				);

					$element->add_control(
						'carousel_arrows_color',
						[
							'label' => esc_html__( 'Arrows Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg' => 'color: {{VALUE}}',
							],
						]
					);

					$element->add_control(
						'carousel_arrows_bg_color',
						[
							'label' => esc_html__( 'Arrows Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg' => 'background-color: {{VALUE}}',
							],
						]
					);

					$element->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'carousel_arrows_border',
							'selector' => '{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg',
							'responsive' => true,
						]
					);

				$element->end_controls_tab();

				$element->start_controls_tab(
					'tab_hover',
					[
						'label' => __( 'Hover', 'rey-core' ),
					]
				);

					$element->add_control(
						'carousel_arrows_color_hover',
						[
							'label' => esc_html__( 'Arrows Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg:hover' => 'color: {{VALUE}}',
							],
						]
					);

					$element->add_control(
						'carousel_arrows_bg_color_hover',
						[
							'label' => esc_html__( 'Arrows Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg:hover' => 'background-color: {{VALUE}}',
							],
						]
					);

					$element->add_control(
						'carousel_arrows_border_color_hover',
						[
							'label' => esc_html__( 'Arrows Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg:hover' => 'border-color: {{VALUE}}',
							],
						]
					);

				$element->end_controls_tab();

			$element->end_controls_tabs();

			$element->add_control(
				'carousel_arrows_border_radius',
				[
					'label' => __( 'Border Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$element->end_controls_section();
		}


		public function loop_start()
		{
			wc_set_loop_prop( 'loop', 0 );

			$parent_classes = $this->parent->get_css_classes();

			if( $parent_classes['grid_layout'] === 'rey-wcGrid-metro' ){
				$parent_classes[] = '--prevent-metro';
			}

			$classes = [
				esc_attr( $this->parent->get_settings_for_display('carousel_id') ),
				'--prevent-thumbnail-sliders', // make sure it does not have thumbnail slideshow
				'--prevent-scattered', // make sure scattered is not applied
				'--prevent-masonry', // make sure masonry is not applied
			];

			$attributes = '';

			if( $carousel_id = $this->parent->get_settings_for_display('carousel_id') ){
				$attributes .= sprintf(' data-slider-carousel-id="%s"', esc_attr($carousel_id));
			}

			printf('<ul class="products %s" %s>', implode(' ', array_merge( $classes, $parent_classes ) ), $attributes );
		}

		public function loop_end(){
			echo '</ul>';
		}

		function render_nav( $settings ){

			if( $settings['carousel_arrows'] === '' ){
				return;
			}

			echo '<div class="reyEl-productGrid-carouselNav">';

				if( $settings['carousel_arrows_type'] === 'chevron' ){
					add_filter('rey/svg_arrow_markup', [$this, 'chevron_arrows']);
				}

					echo reycore__arrowSvg(false);
					echo reycore__arrowSvg();

				if( $settings['carousel_arrows_type'] === 'chevron' ){
					remove_filter('rey/svg_arrow_markup', [$this, 'chevron_arrows']);
				}

			echo '</div>';

		}

		function chevron_arrows(){
			return '
			<svg width="114px" height="184px" viewBox="0 0 114 184" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
				<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" stroke-linecap="square">
					<polyline stroke="currentColor" stroke-width="20" transform="translate(21.710678, 91.921593) rotate(-135.000000) translate(-21.710678, -91.921593) " points="-28.2893219 41.9215929 -28.2893219 141.921593 71.7106781 141.921593"></polyline>
				</g>
			</svg>';
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

				$carousel_config = [
					'slides_to_show' => $settings['slides_to_show'],
					'slides_to_scroll' => $settings['slides_to_scroll'],
					'autoplay_speed' => $settings['autoplay_speed'],
					'pause_on_hover' => $settings['pause_on_hover'],
					'autoplay' => $settings['autoplay'],
					'infinite' => $settings['infinite'],
					'effect' => $settings['effect'],
					'speed' => $settings['speed'],
					'direction' => $settings['direction'],
				];

				if( $settings['carousel_arrows'] !== '' ){
					$carousel_config['arrows'] = true;
					$carousel_config['appendArrows'] = '.reyEl-productGrid-carouselNav';
				}

				if( $settings['slides_to_show_tablet'] ){
					$carousel_config['slides_to_show_tablet'] = $settings['slides_to_show_tablet'];
					$carousel_config['slides_to_scroll_tablet'] = $settings['slides_to_scroll_tablet'] !== '' ? $settings['slides_to_scroll_tablet'] : $settings['slides_to_show_tablet'];
				}

				if( $settings['slides_to_show_mobile'] ){
					$carousel_config['slides_to_show_mobile'] = $settings['slides_to_show_mobile'];
					$carousel_config['slides_to_scroll_mobile'] = $settings['slides_to_scroll_mobile'] !== '' ? $settings['slides_to_scroll_mobile'] : $settings['slides_to_show_mobile'];
				}

				$this->parent->add_render_attribute( 'wrapper', 'data-carousel-settings', wp_json_encode($carousel_config) );

				$this->parent->render_start( $settings, $products );

					$this->loop_start();

					$this->parent->render_products( $products );

					$this->loop_end();

					$this->render_nav( $settings );

				$this->parent->render_end();
			}
			else {
				wc_get_template( 'loop/no-products-found.php' );
			}
		}

	}
endif;
