<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if(!class_exists('ReyCore_Widget_Basic_Post_Grid')):
	/**
	 *
	 * Elementor widget.
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Widget_Basic_Post_Grid extends \Elementor\Widget_Base {

		public $_query = null;

		public function get_name() {
			return 'reycore-basic-post-grid';
		}

		public function get_title() {
			return __( 'Posts (Rey)', 'rey-core' );
		}

		public function get_icon() {
			return 'eicon-posts-grid';
		}

		public function get_categories() {
			return [ 'rey-theme' ];
		}

		public function get_keywords() {
			return [ 'post', 'grid', 'blog', 'recent', 'news' ];
		}

		protected function _register_skins() {
			$this->add_skin( new ReyCore_Widget_Basic_Post_Grid__Basic2( $this ) );
			$this->add_skin( new ReyCore_Widget_Basic_Post_Grid__Inner( $this ) );
		}

		public function get_custom_help_url() {
			return 'https://support.reytheme.com/kb/rey-elements/#post-grid';
		}

		/**
		 * Register widget controls.
		 *
		 * Adds different input fields to allow the user to change and customize the widget settings.
		 *
		 * @since 1.0.0
		 * @access protected
		 */
		protected function _register_controls() {

			$this->start_controls_section(
				'section_layout',
				[
					'label' => __( 'Layout', 'rey-core' ),
				]
			);

			$this->add_responsive_control(
				'per_row',
				[
					'label' => __( 'Posts per row', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'prefix_class' => 'reyEl-bPostGrid%s--',
					'min' => 1,
					'max' => 6,
					'default' => 2,
					'condition' => [
						'carousel!' => 'yes',
					],
				]
			);

			$this->add_control(
				'posts_per_page',
				[
					'label' => __( 'Limit', 'rey-core' ),
					'description' => __( 'Select the number of items to load from query.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 2,
					'min' => 1,
					'max' => 100,
				]
			);

			$this->add_control(
				'gaps',
				[
					'label' => __( 'Grid Gaps', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'default',
					'options' => [
						'no'  => __( 'No gaps', 'rey-core' ),
						'narrow'  => __( 'Narrow', 'rey-core' ),
						'default'  => __( 'Default', 'rey-core' ),
						'extended'  => __( 'Extended', 'rey-core' ),
						'wide'  => __( 'Wide', 'rey-core' ),
						'wider'  => __( 'Wider', 'rey-core' ),
					],
				]
			);

			$this->add_responsive_control(
				'align',
				[
					'label' => __( 'Alignment', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'options' => [
						'left' => [
							'title' => __( 'Left', 'rey-core' ),
							'icon' => 'fa fa-align-left',
						],
						'center' => [
							'title' => __( 'Center', 'rey-core' ),
							'icon' => 'fa fa-align-center',
						],
						'right' => [
							'title' => __( 'Right', 'rey-core' ),
							'icon' => 'fa fa-align-right',
						],
					],
					'prefix_class' => 'elementor%s-align-',
				]
			);

			$this->end_controls_section();

			/**
			 * Query Settings
			 */

			$this->start_controls_section(
				'section_query',
				[
					'label' => __( 'Posts Query', 'rey-core' ),
				]
			);

			$this->add_control(
				'query_type',
				[
					'label' => esc_html__('Query Type', 'rey-core'),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'recent',
					'options' => [
						'recent'           => esc_html__('Recent posts', 'rey-core'),
						'manual-selection' => esc_html__('Manual Selection', 'rey-core'),
					],
				]
			);

			$this->add_control(
				'categories',
				[
					'label' => esc_html__('Categories', 'rey-core'),
					'placeholder' => esc_html__('- Select category -', 'rey-core'),
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'terms',
						'taxonomy' => 'category',
					],
					'label_block' => true,
					'multiple' => true,
					'default'     => [],
					'condition' => [
						'query_type' => ['recent'],
					],
				]
			);

			$this->add_control(
				'tags',
				[
					'label' => esc_html__('Tags', 'rey-core'),
					'placeholder' => esc_html__('- Select tags -', 'rey-core'),
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'terms',
						'taxonomy' => 'post_tag',
					],
					'label_block' => true,
					'multiple' => true,
					'default'     => [],
					'condition' => [
						'query_type' => ['recent'],
					],
				]
			);

			// Advanced settings
			$this->add_control(
				'include',
				[
					'label'       => esc_html__( 'Posts', 'rey-core' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'placeholder' => 'eg: 21, 22',
					'label_block' => true,
					'description' => __( 'Add posts IDs separated by comma.', 'rey-core' ),
					'condition' => [
						'query_type' => 'manual-selection',
					],
				]
			);

			$this->add_control(
				'exclude',
				[
					'label'       => esc_html__( 'Exclude Posts', 'rey-core' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'placeholder' => 'eg: 21, 22',
					'label_block' => true,
					'description' => __( 'Add posts IDs separated by comma.', 'rey-core' ),
					'condition' => [
						'query_type!' => 'manual-selection',
					],
				]
			);

			$this->add_control(
				'orderby',
				[
					'label' => __( 'Order By', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'post_date',
					'options' => [
						'post_date' => __( 'Date', 'rey-core' ),
						'post_title' => __( 'Title', 'rey-core' ),
						'menu_order' => __( 'Menu Order', 'rey-core' ),
						'rand' => __( 'Random', 'rey-core' ),
					],
					'condition' => [
						'query_type!' => 'manual-selection',
					],
				]
			);

			$this->add_control(
				'order',
					[
					'label' => __( 'Order', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'desc',
					'options' => [
						'asc' => __( 'ASC', 'rey-core' ),
						'desc' => __( 'DESC', 'rey-core' ),
					],
					'condition' => [
						'query_type!' => 'manual-selection',
					],
				]
			);

			$this->add_control(
				'exclude_duplicates',
				[
					'label' => __( 'Exclude Duplicate Products', 'rey-core' ),
					'description' => __( 'Exclude duplicate products that were already loaded in this page', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->end_controls_section();

			/* ------------------------------------ CAROUSEL ------------------------------------ */

			$this->start_controls_section(
				'section_carousel_settings',
				[
					'label' => __( 'Carousel Settings', 'rey-core' ),
				]
			);

			$this->add_control(
				'carousel',
				[
					'label' => esc_html__( 'Activate Carousel', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);


			$slides_to_show = range( 1, 10 );
			$slides_to_show = array_combine( $slides_to_show, $slides_to_show );

			$this->add_responsive_control(
				'slides_to_show',
				[
					'label' => __( 'Slides to Show', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'options' => [
						'' => __( 'Default', 'rey-core' ),
					] + $slides_to_show,
					'condition' => [
						'carousel' => 'yes',
					],
				]
			);

			$this->add_responsive_control(
				'slides_to_scroll',
				[
					'label' => __( 'Slides to Scroll', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'description' => __( 'Set how many slides are scrolled per swipe.', 'rey-core' ),
					'options' => [
						'' => __( 'Default', 'rey-core' ),
					] + $slides_to_show,
					'condition' => [
						'carousel' => 'yes',
					],
				]
			);

			$this->add_control(
				'pause_on_hover',
				[
					'label' => __( 'Pause on Hover', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'carousel' => 'yes',
					],
				]
			);

			$this->add_control(
				'autoplay',
				[
					'label' => __( 'Autoplay', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'carousel' => 'yes',
					],
				]
			);

			$this->add_control(
				'autoplay_speed',
				[
					'label' => __( 'Autoplay Speed', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 5000,
					'condition' => [
						'carousel' => 'yes',
					],
				]
			);

			$this->add_control(
				'infinite',
				[
					'label' => __( 'Infinite Loop', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'yes',
					'options' => [
						'yes' => __( 'Yes', 'rey-core' ),
						'no' => __( 'No', 'rey-core' ),
					],
					'condition' => [
						'carousel' => 'yes',
					],
				]
			);

			$this->add_control(
				'effect',
				[
					'label' => __( 'Effect', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'slide',
					'options' => [
						'slide' => __( 'Slide', 'rey-core' ),
						'fade' => __( 'Fade', 'rey-core' ),
					],
					'condition' => [
						'carousel' => 'yes',
					],
				]
			);

			$this->add_control(
				'speed',
				[
					'label' => __( 'Animation Speed', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 500,
					'condition' => [
						'carousel' => 'yes',
					],
				]
			);

			$this->add_control(
				'direction',
				[
					'label' => __( 'Direction', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'ltr',
					'options' => [
						'ltr' => __( 'Left', 'rey-core' ),
						'rtl' => __( 'Right', 'rey-core' ),
					],
					'condition' => [
						'carousel' => 'yes',
					],
				]
			);

			$this->add_control(
				'carousel_id',
				[
					'label' => __( 'Carousel Unique ID', 'rey-core' ),
					'label_block' => true,
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => uniqid('carousel-'),
					'placeholder' => __( 'eg: some-unique-id', 'rey-core' ),
					'description' => __( 'Copy the ID above and paste it into the "Toggle Boxes" Widget or "Slider Navigation" widget where specified. No hashtag needed. Read more on <a href="https://support.reytheme.com/kb/products-grid-element/#adding-custom-navigation" target="_blank">how to connect them</a>.', 'rey-core' ),
					'condition' => [
						'carousel' => 'yes',
					],
				]
			);

			$this->end_controls_section();


			/* ------------------------------------ Inner Skin Options ------------------------------------ */


			$this->start_controls_section(
				'section_innerskin_styles',
				[
					'label' => __( 'Inner Skin Settings', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
					'condition' => [
						'_skin' => 'inner',
					],
				]
			);

			$this->add_control(
				'inner_align',
				[
					'label' => esc_html__( 'Vertical Alignment', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'flex-end',
					'options' => [
						'flex-start'  => esc_html__( 'Top', 'rey-core' ),
						'center'  => esc_html__( 'Middle', 'rey-core' ),
						'flex-end'  => esc_html__( 'Bottom', 'rey-core' ),
					],
					'selectors' => [
						'{{WRAPPER}} .reyEl-bPostGrid-inner' => 'justify-content: {{VALUE}}',
					],
					'condition' => [
						'_skin' => 'inner',
					],
				]
			);

			$this->add_responsive_control(
				'inner_spacing',
				[
				   'label' => esc_html__( 'Content Spacing', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'em' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 180,
							'step' => 1,
						],
						'em' => [
							'min' => 0,
							'max' => 10.0,
						],
					],
					'default' => [
						'unit' => 'px',
						'size' => 40,
					],
					'selectors' => [
						'{{WRAPPER}} .reyEl-bPostGrid--inner' => '--posts-spacing: {{SIZE}}{{UNIT}};',
					],
					'condition' => [
						'_skin' => 'inner',
					],
				]
			);

			$this->add_control(
				'inner_image_stretch',
				[
					'label' => esc_html__( 'Image Stretch', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'inner_hover_effect',
				[
					'label' => esc_html__( 'Hover Effect', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'none',
					'options' => [
						'none'  => esc_html__( 'Default', 'rey-core' ),
						'scale'  => esc_html__( 'Scale', 'rey-core' ),
						'clip'  => esc_html__( 'Clip & Scale', 'rey-core' ),
						'shift'  => esc_html__( 'Subtle Shift', 'rey-core' ),
					],
				]
			);

			$this->start_controls_tabs( 'tabs_inner_styles' );

				$this->start_controls_tab(
					'tab_inner_normal',
					[
						'label' => __( 'Normal', 'rey-core' ),
					]
				);

					$this->add_control(
						'inner_text_color',
						[
							'label' => __( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'default' => '',
							'selectors' => [
								'{{WRAPPER}} .reyEl-bPostGrid--inner' => '--posts-text-color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'inner_link_color',
						[
							'label' => __( 'Link Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'default' => '',
							'selectors' => [
								'{{WRAPPER}} .reyEl-bPostGrid--inner' => '--posts-link-color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'inner_bg_color',
						[
							'label' => __( 'Overlay Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'default' => '',
							'selectors' => [
								'{{WRAPPER}} .reyEl-bPostGrid--inner' => '--posts-inner-bg-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'tab_inner_hover',
					[
						'label' => __( 'Hover', 'rey-core' ),
					]
				);

					$this->add_control(
						'inner_link_color_hover',
						[
							'label' => __( 'Link Hover Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'default' => '',
							'selectors' => [
								'{{WRAPPER}} .reyEl-bPostGrid--inner' => '--posts-link-hover-color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'inner_bg_color_hover',
						[
							'label' => __( 'Overlay Hover Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'default' => '',
							'selectors' => [
								'{{WRAPPER}} .reyEl-bPostGrid--inner' => '--posts-inner-bg-hover-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();


			$this->end_controls_section();

			/* ------------------------------------ Start section - Thumbnail ------------------------------------ */

			$this->start_controls_section(
				'section_thumb_styles',
				[
					'label' => __( 'Thumbnail Settings', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_control(
				'thumb',
				[
					'label' => __( 'Display thumbnail', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default' => 'yes',
				]
			);

			$this->add_control(
				'thumb_size',
				[
					'label' => __( 'Thumbnail layout', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'natural',
					'options' => [
						'natural'  => __( 'Natural', 'rey-core' ),
						'custom'  => __( 'Custom Height', 'rey-core' ),
					],
					'prefix_class' => 'reyEl-bpost-thumb--',
					'condition' => [
						'thumb' => 'yes',
					],
				]
			);

			$this->add_responsive_control(
				'custom_height',
				[
					'label' => __( 'Height', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'default' => [
						'size' => 300,
					],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 800,
						],
					],
					'size_units' => [ 'px' ],
					'selectors' => [
						'{{WRAPPER}}.reyEl-bpost-thumb--custom .reyEl-bpost-thumb' => 'height: {{SIZE}}{{UNIT}};',
					],
					'condition' => [
						'thumb' => 'yes',
						'thumb_size' => 'custom',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Image_Size::get_type(),
				[
					'name' => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
					'default' => 'medium_large',
					'separator' => 'before',
					'exclude' => ['custom'],
					// 'condition' => [
					// 	'_skin!' => 'carousel-section',
					// ],
				]
			);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_title_styles',
				[
					'label' => __( 'Title Settings', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
					'title',
					[
						'label' => __( 'Display title', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'return_value' => 'yes',
						'default' => 'yes',
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' => 'title_typo',
						'scheme' => \Elementor\Scheme_Typography::TYPOGRAPHY_1,
						'selector' => '{{WRAPPER}} .reyEl-bpost-title',
						'condition' => [
							'title' => ['yes'],
						],
					]
				);

				$this->add_control(
					'title_underline_effect',
					[
						'label' => esc_html__( 'Title underline effect', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'default' => '',
						'prefix_class' => '--title-hover-',
					]
				);

				$this->add_control(
					'title_color',
					[
						'label' => esc_html__( 'Title Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .reyEl-bpost-title a' => 'color: {{VALUE}}',
						],
						'condition' => [
							'_skin!' => 'inner',
						],
					]
				);

			$this->end_controls_section();



			$this->start_controls_section(
				'section_meta_styles',
				[
					'label' => __( 'Meta Settings', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_control(
				'meta_author',
				[
					'label' => __( 'Display Author', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default' => 'yes',
				]
			);

			$this->add_control(
				'meta_date',
				[
					'label' => __( 'Display Date', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default' => 'yes',
				]
			);

			$this->add_control(
				'meta_comments',
				[
					'label' => __( 'Display Comments', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default' => 'yes',
				]
			);

			$this->add_control(
				'meta_color',
				[
					'label' => esc_html__( 'Meta Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-postInfo, {{WRAPPER}} .rey-postInfo a' => 'color: {{VALUE}}',
					],
					'condition' => [
						'_skin!' => 'inner',
					],
				]
			);

			$this->add_control(
				'date_color',
				[
					'label' => esc_html__( 'Date Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-entryDate' => 'color: {{VALUE}}',
					],
					'condition' => [
						'_skin!' => 'inner',
					],
				]
			);

			$this->end_controls_section();


			$this->start_controls_section(
				'section_content_styles',
				[
					'label' => __( 'Excerpt Settings', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_control(
				'excerpt',
				[
					'label' => __( 'Display excerpt', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default' => 'yes',
				]
			);

			$this->add_control(
				'excerpt_length',
				[
					'label' => __( 'Excerpt Length', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 20,
					'min' => 0,
					'max' => 200,
					'step' => 0,
				]
			);

			$this->add_control(
				'excerpt_color',
				[
					'label' => esc_html__( 'Excerpt Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .reyEl-bpost-content, {{WRAPPER}} .reyEl-bpost-content a' => 'color: {{VALUE}}',
					],
					'condition' => [
						'_skin!' => 'inner',
					],
				]
			);

			// settings pt image size

			$this->end_controls_section();


			$this->start_controls_section(
				'section_footer_styles',
				[
					'label' => __( 'Post footer settings', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_control(
				'read_more',
				[
					'label' => __( 'Display Read more button', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default' => 'yes',
				]
			);

			$this->add_control(
				'read_duration',
				[
					'label' => __( 'Display Read Duration', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default' => 'yes',
					'condition' => [
						'read_more' => ['yes'],
					],
				]
			);

			$this->add_control(
				'footer_color',
				[
					'label' => esc_html__( 'Footer Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .reyEl-bpost-footer, {{WRAPPER}} .reyEl-bpost-footer a' => 'color: {{VALUE}}',
					],
					'condition' => [
						'_skin!' => 'inner',
					],
				]
			);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_posts_misc',
				[
					'label' => __( 'Misc. settings', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_responsive_control(
				'vertical_spacing',
				[
				   'label' => esc_html__( 'Vertical Spacing', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'em' ],
					'range' => [
						'px' => [
							'min' => 9,
							'max' => 180,
							'step' => 1,
						],
						'em' => [
							'min' => 0,
							'max' => 5.0,
						],
					],
					'default' => [
						'unit' => 'px',
						'size' => 30,
					],
					'selectors' => [
						'{{WRAPPER}} .reyEl-bPostGrid' => '--bpostgrid-vspacing: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'entry_animation',
				[
					'label' => __( 'Animate on scroll', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->end_controls_section();
		}

		public function get_query() {
			return $this->_query;
		}

		public function query_posts() {

			$settings = $this->get_settings_for_display();

			$query_args = [
				'posts_per_page' => $settings['posts_per_page'] ? $settings['posts_per_page'] : get_option('posts_per_page'),
				'post_type' => 'post',
				'post_status' => 'publish',
				'ignore_sticky_posts' => true,
				// 'no_found_rows' => true,
				// 'update_post_meta_cache' => false, //useful when post meta will not be utilized
				'update_post_term_cache' => false, //useful when taxonomy terms will not be utilized
				'orderby' => isset($settings['orderby']) ? $settings['orderby'] : 'date',
				'order' => isset($settings['order']) ? $settings['order'] : 'DESC',
			];

			if( $settings['query_type'] == 'manual-selection' && !empty($settings['include']) ) {
				$query_args['post__in'] = array_map( 'trim', explode( ',', $settings['include'] ) );
			}
			else {

				if( isset($settings['categories']) && $categories = $settings['categories'] ){
					unset($query_args['update_post_term_cache']);
					$query_args['category__in'] = array_map( 'absint', $categories );
				}

				if( isset($settings['tags']) && $tags = $settings['tags'] ){
					unset($query_args['update_post_term_cache']);
					$query_args['tag__in'] = array_map( 'absint', $tags );
				}

				if( !empty($settings['exclude']) ) {
					$query_args['post__not_in'] = array_map( 'trim', explode( ',', $settings['exclude'] ) );
				}
			}

			// Exclude duplicates
			if( $settings['exclude_duplicates'] !== '' &&
				isset($GLOBALS["rey_exclude_posts"]) &&
				($to_exclude = $GLOBALS["rey_exclude_posts"]) ){
				$query_args['post__not_in'] = isset($query_args['post__not_in']) ? array_merge( $query_args['post__not_in'], $to_exclude ) : $to_exclude;
			}

			$this->_query = new WP_Query( $query_args );
		}

		/**
		 * Render thumbnail
		 *
		 * @since 1.0.0
		 **/
		public function render_thumbnail()
		{
			$settings = $this->get_settings_for_display();

			if ( 'yes' === $settings['thumb'] && (function_exists('rey__can_show_post_thumbnail') && rey__can_show_post_thumbnail()) ): ?>
			<div class="reyEl-bpost-thumb">
				<a class="reyEl-bpost-thumbLink" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">

					<?php
					$image_size = $settings['image_size'] !== 'custom' ? $settings['image_size'] : 'medium_large';

					the_post_thumbnail(  $image_size); ?>
				</a>
			</div>
			<?php endif;
		}

		/**
		 * Render meta
		 *
		 * @since 1.0.0
		 **/
		public function render_meta()
		{
			$settings = $this->get_settings_for_display();

			if( 'yes' === $settings['meta_author'] || 'yes' === $settings['meta_date'] || 'yes' === $settings['meta_comments'] ): ?>
				<div class="rey-postInfo">
				<?php
					if( 'yes' === $settings['meta_author'] ){
						if( function_exists('rey__posted_by') ){
							rey__posted_by();
						}
					}
					if( 'yes' === $settings['meta_date'] ){
						if( function_exists('rey__posted_date') ){
							rey__posted_date();
						}
					}
					if( 'yes' === $settings['meta_comments'] ){
						if( function_exists('rey__comment_count') ){
							rey__comment_count();
						}
					}
					if( function_exists('rey__edit_link') ){
						rey__edit_link();
					}
				?>
				</div>
			<?php endif;
		}

		/**
		 * Render title
		 *
		 * @since 1.0.0
		 **/
		public function render_title()
		{
			$title = $this->get_settings_for_display('title');

			if( 'yes' === $title ){
				the_title( sprintf( '<h3 class="reyEl-bpost-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h3>' );
			}
		}

		/**
		 * Render excerpt
		 *
		 * @since 1.0.0
		 **/
		public function render_excerpt()
		{
			$settings = $this->get_settings_for_display();

			if( 'yes' !== $settings['excerpt'] ) {
				return;
			}

			add_filter( 'excerpt_length', function() use ($settings) {
				return $settings['excerpt_length'];
			}, 999 );
			?>

			<div class="reyEl-bpost-content">
				<?php the_excerpt(); ?>
			</div>

			<?php
		}

		/**
		 * Render footer
		 *
		 * @since 1.0.0
		 **/
		public function render_footer()
		{
			$settings = $this->get_settings_for_display();

			if( 'yes' !== $settings['read_more'] ) {
				return;
			}

			?>
			<div class="reyEl-bpost-footer">

				<a class="btn btn-line-active" href="<?php echo esc_url( get_permalink() ) ?>">
					<?php esc_html_e('CONTINUE READING', 'rey-core') ?>
					<span class="screen-reader-text"> <?php echo get_the_title(); ?></span>
				</a>
				<?php
				if( 'yes' === $settings['read_duration'] ){
					if( function_exists('rey__postDuration') ){
						echo rey__postDuration();
					}
				} ?>
			</div>

			<?php
		}

		public function render_start(){

			$settings = $this->get_settings_for_display();
			$skin = $settings['_skin'] ? $settings['_skin'] : 'default';

			$classes = [
				'rey-element',
				'reyEl-bPostGrid',
				'rey-gap--' . $settings['gaps'],
				'reyEl-bPostGrid--' . $skin,
			];

			if( 'inner' === $skin) {
				if( 'yes' === $settings['inner_image_stretch'] ) {
					$classes['inner_stretch_img'] = '--stretch-image';
				}
				$classes['inner_effect'] = '--inner-effect-' . $settings['inner_hover_effect'];
			}

			if( $settings['per_row'] < $settings['posts_per_page'] ){
				$classes['masonry'] = '--masonry';
			}

			if( $settings['carousel'] === 'yes' ){
				unset($classes['masonry']);
				$classes['carousel'] = '--carousel';
			}

			$this->add_render_attribute( 'wrapper', 'class', $classes );

			if( 'yes' === $settings['carousel'] ):

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

				if( $settings['slides_to_show_tablet'] ){
					$carousel_config['slides_to_show_tablet'] = $settings['slides_to_show_tablet'];
					$carousel_config['slides_to_scroll_tablet'] = $settings['slides_to_scroll_tablet'] !== '' ? $settings['slides_to_scroll_tablet'] : $settings['slides_to_show_tablet'];
				}

				if( $settings['slides_to_show_mobile'] ){
					$carousel_config['slides_to_show_mobile'] = $settings['slides_to_show_mobile'];
					$carousel_config['slides_to_scroll_mobile'] = $settings['slides_to_scroll_mobile'] !== '' ? $settings['slides_to_scroll_mobile'] : $settings['slides_to_show_mobile'];
				}

				$this->add_render_attribute( 'wrapper', 'data-carousel-settings', wp_json_encode($carousel_config) );
			endif;

			$GLOBALS["rey_exclude_posts"] = wp_list_pluck( $this->_query->posts, 'ID' );

			?>
			<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<?php
		}

		public function render_end(){
			?>
			</div>
			<?php
		}

		function animatedItemClass() {
			$settings = $this->get_settings_for_display();

			if(reycore__config('animate_blog_items') && !\Elementor\Plugin::$instance->editor->is_edit_mode() && $settings['entry_animation'] === 'yes' && $settings['carousel'] !== 'yes') {
				return 'is-animated-entry';
			}

			return '';
		}


		/**
		 * Render widget output on the frontend.
		 *
		 * Written in PHP and used to generate the final HTML.
		 *
		 * @since 1.0.0
		 * @access protected
		 */
		protected function render() {

			$this->query_posts();

			if ( ! $this->_query->found_posts ) {
				return;
			}

			$this->render_start();

			while ( $this->_query->have_posts() ) : $this->_query->the_post(); ?>
			<div class="reyEl-bPostGrid-item rey-gapItem <?php echo $this->animatedItemClass(); ?>">
				<?php
					$this->render_thumbnail();
					$this->render_meta();
					$this->render_title();
					$this->render_excerpt();
					$this->render_footer();
				?>
			</div>
			<?php endwhile;
			wp_reset_postdata();

			$this->render_end();
		}

		/**
		 * Render widget output in the editor.
		 *
		 * Written as a Backbone JavaScript template and used to generate the live preview.
		 *
		 * @since 1.0.0
		 * @access protected
		 */
		protected function _content_template() {}
	}
endif;
