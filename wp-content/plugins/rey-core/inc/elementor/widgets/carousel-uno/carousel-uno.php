<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if(!class_exists('ReyCore_Widget_Carousel_Uno')):
	/**
	 *
	 * Elementor widget.
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Widget_Carousel_Uno extends \Elementor\Widget_Base {

		private $_settings = [];
		private $_items = [];

		public function get_name() {
			return 'reycore-carousel-uno';
		}

		public function get_title() {
			return __( 'Carousel - Uno', 'rey-core' );
		}

		public function get_icon() {
			return 'eicon-post-slider';
		}

		public function get_categories() {
			return [ 'rey-theme' ];
		}

		public function get_script_depends() {
			return [ 'jquery-slick', 'reycore-widget-carousel-uno-scripts' ];
		}

		public function get_custom_help_url() {
			return 'https://support.reytheme.com/kb/rey-elements/#carousel-uno';
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
				'section_content',
				[
					'label' => __( 'Content', 'rey-core' ),
				]
			);

			$items = new \Elementor\Repeater();

			$items->add_control(
				'bg_type',
				[
					'label' => __( 'Content Type', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'default' => 'image',
					'options' => [
						'image' => [
							'title' => _x( 'Image', 'Background Control', 'rey-core' ),
							'icon' => 'eicon-image',
						],
						'video' => [
							'title' => _x( 'Self Hosted Video', 'Background Control', 'rey-core' ),
							'icon' => 'eicon-video-camera',
						],
						'youtube' => [
							'title' => _x( 'YouTube Video', 'Background Control', 'rey-core' ),
							'icon' => 'eicon-youtube',
						],
					],
				]
			);

			$items->add_control(
				'html_video',
				[
					'label' => __( 'Self Hosted Video URL', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'label_block' => true,
					'description' => __( 'Link to video file (mp4 is recommended).', 'rey-core' ),
					'conditions' => [
						'terms' => [
							[
								'name' => 'bg_type',
								'operator' => '==',
								'value' => 'video',
							],
						],
					],
				]
			);

			$items->add_control(
				'yt_video',
				[
					'label' => __( 'YouTube URL', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'label_block' => true,
					// 'description' => __( 'Link to Youtube.', 'rey-core' ),
					'conditions' => [
						'terms' => [
							[
								'name' => 'bg_type',
								'operator' => '==',
								'value' => 'youtube',
							],
						],
					],
				]
			);

			$items->add_control(
				'image',
				[
				'label' => __( 'Image', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'default' => [
						'url' => \Elementor\Utils::get_placeholder_image_src(),
					],
					'conditions' => [
						'terms' => [
							[
								'name' => 'bg_type',
								'operator' => '==',
								'value' => 'image',
							],
						],
					],
				]
			);

			$items->add_control(
				'image_as_video_fallback',
				[
					// 'label' => __( 'Important Note', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'raw' => __( 'Use the image as fallback for videos on mobile.', 'rey-core' ),
					'content_classes' => 'elementor-descriptor',
					'conditions' => [
						'terms' => [
							[
								'name' => 'bg_type',
								'operator' => '!=',
								'value' => 'image',
							],
						],
					],
				]
			);


			$items->add_control(
				'video_image',
				[
				'label' => __( 'Background Fallback', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'default' => [
						'url' => \Elementor\Utils::get_placeholder_image_src(),
					],
					'description' => __( 'This cover image will replace the background video on mobile and tablet devices. ', 'rey-core' ),
					'conditions' => [
						'terms' => [
							[
								'name' => 'bg_type',
								'operator' => '!=',
								'value' => 'image',
							],
						],
					],
				]
			);

			$items->add_control(
				'overlay_color',
				[
					'label' => __( 'Overlay Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} {{CURRENT_ITEM}} .cUno-slideOverlay' => 'background-color: {{VALUE}}',
					],
				]
			);

			$items->add_control(
				'captions',
				[
					'label' => __( 'Enable Captions', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$items->add_control(
				'title',
				[
					'label'       => __( 'Title', 'rey-core' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'label_block' => true,
					'conditions' => [
						'terms' => [
							[
								'name' => 'captions',
								'operator' => '!=',
								'value' => '',
							],
						],
					],
				]
			);

			$items->add_control(
				'subtitle',
				[
					'label'       => __( 'Subtitle Text', 'rey-core' ),
					'type'        => \Elementor\Controls_Manager::TEXTAREA,
					'label_block' => true,
					'conditions' => [
						'terms' => [
							[
								'name' => 'captions',
								'operator' => '!=',
								'value' => '',
							],
						],
					],
				]
			);

			$items->add_control(
				'text_color',
				[
					'label' => __( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} {{CURRENT_ITEM}} .cUno-caption' => 'color: {{VALUE}}',
					],
					'conditions' => [
						'terms' => [
							[
								'name' => 'captions',
								'operator' => '!=',
								'value' => '',
							],
						],
					],
					'separator' => 'after'
				]
			);

			$items->add_control(
				'button_text',
				[
					'label' => __( 'Button Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'dynamic' => [
						'active' => true,
					],
					'default' => __( 'Click here', 'rey-core' ),
					'placeholder' => __( 'eg: SHOP NOW', 'rey-core' ),
					'conditions' => [
						'terms' => [
							[
								'name' => 'captions',
								'operator' => '!=',
								'value' => '',
							],
						],
					],
				]
			);

			$items->add_control(
				'button_url',
				[
					'label' => __( 'Link', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::URL,
					'dynamic' => [
						'active' => true,
					],
					'placeholder' => __( 'https://your-link.com', 'rey-core' ),
					'default' => [
						'url' => '#',
					],
					'conditions' => [
						'terms' => [
							[
								'name' => 'captions',
								'operator' => '!=',
								'value' => '',
							],
						],
					],
					'separator' => 'after'
				]
			);

			// No 2nd button because they would need too many options, style, color, hover color, per button

			$this->add_control(
				'items',
				[
					'label' => __( 'Items', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $items->get_controls(),
					'default' => [
						[
							'image' => [
								'url' => \Elementor\Utils::get_placeholder_image_src(),
							],
							'captions' => 'yes',
							'title' => esc_html_x('Some title', 'Placeholder title', 'rey-core'),
							'subtitle' => esc_html_x('Phosfluorescently predominate pandemic applications for real-time customer service', 'Placeholder text', 'rey-core'),
							'button_text' => esc_html_x('Click here', 'Placeholder button text', 'rey-core'),
							'button_url' => [
								'url' => '#',
							],
						],
						[
							'image' => [
								'url' => \Elementor\Utils::get_placeholder_image_src(),
							],
							'captions' => 'yes',
							'title' => esc_html_x('Some title', 'Placeholder title', 'rey-core'),
							'subtitle' => esc_html_x('Phosfluorescently predominate pandemic applications for real-time customer service', 'Placeholder text', 'rey-core'),
							'button_text' => esc_html_x('Click here', 'Placeholder button text', 'rey-core'),
							'button_url' => [
								'url' => '#',
							],
						],
					]
				]
			);

			$this->end_controls_section();


			$this->start_controls_section(
				'section_content_settings',
				[
					'label' => __( 'Content Settings', 'rey-core' ),
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Image_Size::get_type(),
				[
					'name' => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
					'default' => 'large',
					// 'separator' => 'before',
					'exclude' => ['custom'],
					// TODO: add support for custom size thumbnails #40
				]
			);

			$this->add_control(
				'stretch_content',
				[
					'label' => __( 'Content Inside', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'return_value' => 'stretch-content',
					'prefix_class' => 'cUno--',
				]
			);

			$this->add_responsive_control(
				'stretch_image_height',
				[
				'label' => __( 'Image Height', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 10,
							'max' => 1000,
							'step' => 1,
						],
					],
					'default' => [
						'unit' => 'px',
					],
					'tablet_default' => [
						'unit' => 'px',
					],
					'mobile_default' => [
						'unit' => 'px',
					],
					'selectors' => [
						'{{WRAPPER}}.cUno--stretch-content .cUno-slide' => 'height: {{SIZE}}{{UNIT}};',
					],
					'condition' => [
						'stretch_content' => 'stretch-content',
					],
				]
			);

			$this->add_responsive_control(
				'content_inside_valign',
				[
					'label' => __( 'Caption Vertical Align', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'options' => [
						'flex-start' => [
							'title' => __( 'Start', 'rey-core' ),
							'icon' => 'eicon-v-align-top',
						],
						'center' => [
							'title' => __( 'Middle', 'rey-core' ),
							'icon' => 'eicon-v-align-middle',
						],
						'flex-end' => [
							'title' => __( 'End', 'rey-core' ),
							'icon' => 'eicon-v-align-bottom',
						],
					],
					'default' => 'flex-start',
					'condition' => [
						'stretch_content' => 'stretch-content',
					],
					'selectors' => [
						'{{WRAPPER}}.cUno--stretch-content .cUno-slide' => 'justify-content: {{VALUE}};',
					],
				]
			);

			$this->add_responsive_control(
				'content_inside_halign',
				[
					'label' => __( 'Caption Horizontal Align', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'options' => [
						'left'           => [
							'title'         => __( 'Left', 'rey-core' ),
							'icon'          => 'eicon-h-align-left',
						],
						'center'        => [
							'title'         => __( 'Center', 'rey-core' ),
							'icon'          => 'eicon-h-align-stretch',
						],
						'right'          => [
							'title'         => __( 'Right', 'rey-core' ),
							'icon'          => 'eicon-h-align-right',
						],
					],
					'default' => 'left',
					'condition' => [
						'stretch_content' => 'stretch-content',
					],
					'selectors' => [
						'{{WRAPPER}}.cUno--stretch-content .cUno-slide' => 'text-align: {{VALUE}};',
					],
				]
			);

			$this->add_responsive_control(
				'content_inside_spacing',
				[
				   'label' => esc_html__( 'Inner Spacing', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 200,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .rey-carouselUno' => '--side-spacing: {{SIZE}}px;',
					],
					'condition' => [
						'stretch_content' => 'stretch-content',
					],
				]
			);


			$this->end_controls_section();


			$this->start_controls_section(
				'section_slider',
				[
					'label' => __( 'Carousel Settings', 'rey-core' ),
				]
			);

			$this->add_control(
				'fade',
				[
					'label' => __( 'Enable Fade Transition', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'autoplay',
				[
					'label' => __( 'Autoplay', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default' => '',
					'condition' => [
						'sync' => '',
					],
				]
			);

			$this->add_control(
				'autoplay_duration',
				[
					'label' => __( 'Autoplay Duration (ms)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 9000,
					'min' => 3500,
					'max' => 20000,
					'step' => 50,
					'condition' => [
						'autoplay' => 'yes',
						'sync' => '',
					],
				]
			);

			$this->add_control(
				'arrows',
				[
					'label' => __( 'Arrows Navigation', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'sync' => '',
					],
				]
			);

			$this->add_control(
				'arrows_on_hover',
				[
					'label' => __( 'Show Arrows On Hover', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'sync' => '',
						'arrows' => 'yes',
					],
				]
			);

			$this->add_control(
				'arrows_position',
				[
				   'label' => esc_html__( 'Arrows Vertical Position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'%' => [
							'min' => 0,
							'max' => 100,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .rey-arrowSvg' => 'top: calc({{SIZE}}% - (var(--arrow-size)/2));',
					],
					'condition' => [
						'sync' => '',
						'arrows' => 'yes',
					],
				]
			);

			$this->add_control(
				'dots',
				[
					'label' => __( 'Dots Navigation', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'sync' => '',
					],
				]
			);

			$this->add_control(
				'dots_position',
				[
					'label' => esc_html__( 'Dots Position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'top-left',
					'options' => [
						'top-left'  => esc_html__( 'Top Left', 'rey-core' ),
						'top-center'  => esc_html__( 'Top Center', 'rey-core' ),
						'top-right'  => esc_html__( 'Top Right', 'rey-core' ),
						'bottom-left'  => esc_html__( 'Bottom Left', 'rey-core' ),
						'bottom-center'  => esc_html__( 'Bottom Center', 'rey-core' ),
						'bottom-right'  => esc_html__( 'Bottom Right', 'rey-core' ),
					],
					'condition' => [
						'stretch_content' => 'stretch-content',
					],
				]
			);

			$this->add_control(
				'sync_id',
				[
					'label' => __( 'Sync another carousel', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => __( 'eg: carousel-unique-id', 'rey-core' ),
					'description' => esc_html__('Sync this slider with another one. Access the other carousel Slider settings and enable Sync option, to generate a unique id which you\'ll have to paste here.', 'rey-core'),
					'condition' => [
						'sync' => '',
					],
				]
			);

			$this->add_control(
				'sync',
				[
					'label' => __( 'Enable Sync', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'target_sync_id',
				[
					'label' => __( 'Sync ID', 'rey-core' ),
					'label_block' => true,
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => uniqid('carousel-'),
					'placeholder' => __( 'eg: carousel-unique-id', 'rey-core' ),
					'description' => __( 'Copy this ID above and paste it into the "Carousel Uno" widget "Sync another carousel".', 'rey-core' ),
					'condition' => [
						'sync!' => '',
					],
					'render_type' => 'none',
				]
			);

			$this->end_controls_section();


			$this->start_controls_section(
				'section_style',
				[
					'label' => __( 'Slider Styles', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'title_typo',
					'label' => esc_html__('Title Typography', 'rey-core'),
					'scheme' => \Elementor\Scheme_Typography::TYPOGRAPHY_1,
					'selector' => '{{WRAPPER}} .cUno-captionTitle',
				]
			);

			$this->add_control(
				'title_color',
				[
					'label' => esc_html__( 'Title Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .cUno-captionTitle' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'subtitle_typo',
					'label' => esc_html__('Sub-Title Typography', 'rey-core'),
					'selector' => '{{WRAPPER}} .cUno-captionSubtitle',
				]
			);

			$this->add_control(
				'subtitle_color',
				[
					'label' => esc_html__( 'Sub-Title Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .cUno-captionSubtitle' => 'color: {{VALUE}}',
					],
					'separator' => 'after'
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'button_typo',
					'label' => esc_html__('Button Typography', 'rey-core'),
					'selector' => '{{WRAPPER}} .cUno-captionBtn a',
				]
			);

			$this->add_control(
				'button_style',
				[
					'label' => __( 'Button Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'btn-line-active',
					'options' => [
						'btn-simple'  => __( 'Link', 'rey-core' ),
						'btn-primary'  => __( 'Primary', 'rey-core' ),
						'btn-secondary'  => __( 'Secondary', 'rey-core' ),
						'btn-primary-outline'  => __( 'Primary Outlined', 'rey-core' ),
						'btn-secondary-outline'  => __( 'Secondary Outlined', 'rey-core' ),
						'btn-line-active'  => __( 'Underlined', 'rey-core' ),
						'btn-line'  => __( 'Hover Underlined', 'rey-core' ),
						'btn-primary-outline btn-dash'  => __( 'Primary Outlined & Dash', 'rey-core' ),
						'btn-primary-outline btn-dash btn-rounded'  => __( 'Primary Outlined & Dash & Rounded', 'rey-core' ),
					],
				]
			);

			$this->start_controls_tabs( 'tabs_styles');

				$this->start_controls_tab(
					'tab_default',
					[
						'label' => __( 'Default', 'rey-core' ),
					]
				);

					$this->add_control(
						'button_color',
						[
							'label' => esc_html__( 'Primary Color (text)', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .cUno-captionBtn .btn' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'button_color_bg',
						[
							'label' => esc_html__( 'Primary Color (background)', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .cUno-captionBtn .btn' => 'background-color: {{VALUE}}',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'tab_hover',
					[
						'label' => __( 'Hover', 'rey-core' ),
					]
				);

					$this->add_control(
						'button_color_hover',
						[
							'label' => esc_html__( 'Primary Color (text)', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .cUno-captionBtn .btn:hover' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'button_color_bg_hover',
						[
							'label' => esc_html__( 'Primary Color (background)', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .cUno-captionBtn .btn:hover' => 'background-color: {{VALUE}}',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_control(
				'arrows_color',
				[
					'label' => esc_html__( 'Arrows Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .cUno-arrows' => 'color: {{VALUE}}',
					],
					'separator' => 'before',
				]
			);

			$this->add_control(
				'arrows_color_diff',
				[
					'label' => esc_html__( 'Enable difference color?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'difference',
					'return_value' => 'difference',
					'selectors' => [
						'{{WRAPPER}} .cUno-arrows' => 'mix-blend-mode: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'dots_color',
				[
					'label' => esc_html__( 'Dots Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .cUno-nav' => 'color: {{VALUE}}',
					],
					'separator' => 'before',
				]
			);

			$this->add_control(
				'dots_color_diff',
				[
					'label' => esc_html__( 'Enable difference color?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'difference',
					'return_value' => 'difference',
					'selectors' => [
						'{{WRAPPER}} .cUno-nav' => 'mix-blend-mode: {{VALUE}}',
					],
				]
			);

			$this->end_controls_section();
		}

		public function render_start(){

			$this->add_render_attribute( 'wrapper', 'class', 'rey-carouselUno' );

			if( count($this->_items) > 1 ) {
				$this->add_render_attribute( 'wrapper', 'data-slider-settings', wp_json_encode([
					'autoplay' => $this->_settings['autoplay'] === 'yes',
					'autoplaySpeed' => absint($this->_settings['autoplay_duration']),
					'fade' => $this->_settings['fade'] !== '',
					'sync' => $this->_settings['sync'] !== '',
					'syncId' => esc_attr($this->_settings['sync_id']),
					'targetSyncId' => esc_attr($this->_settings['target_sync_id']),
					'contentPosition' => 'inside',
				]) );
			}
			?><div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>><?php
		}

		public function render_end(){
			?></div><?php
		}

		public function render_slides(){

			?>
			<div class="cUno-slides <?php echo $this->_settings['sync'] !== '' ? esc_attr($this->_settings['target_sync_id']) : '' ?>">
			<?php
				if( !empty($this->_items) ):
					foreach($this->_items as $key => $item): ?>

					<div class="cUno-slide --<?php echo esc_attr($item['bg_type']) ?> elementor-repeater-item-<?php echo esc_attr($item['_id']) ?>">

						<?php
						$slide_content = '';

						$slide_image = reycore__get_attachment_image( [
							'image' => $item['image'],
							'size' => $this->_settings['image_size'],
							'attributes' => ['class'=>'cUno-slideContent']
						] );

						if( $item['bg_type'] === 'video' )
						{
							$slide_content = reycore__get_video_html([
								'video_url' => $item['html_video'],
								'class' => 'cUno-slideContent',
							]);
						}

						elseif( $item['bg_type'] === 'youtube' )
						{
							$slide_content = reycore__get_youtube_iframe_html([
								'video_url' => $item['yt_video'],
								'class' => 'cUno-slideContent',
								'html_id' => 'yt' . $item['_id'],
								'add_preview_image' => empty($slide_image),
							]);
						}

						echo $slide_content;

						$url_key = 'url' . $key;
						$link_start = $link_end = '';

						if( $item['bg_type'] === 'image' && isset($item['button_url']['url']) && $url = $item['button_url']['url'] )
						{
							$this->add_render_attribute( $url_key , 'href', $url );

							if( $item['button_url']['is_external'] ){
								$this->add_render_attribute( $url_key , 'target', '_blank' );
							}

							if( $item['button_url']['nofollow'] ){
								$this->add_render_attribute( $url_key , 'rel', 'nofollow' );
							}

							$link_start = sprintf('<a %s>', $this->get_render_attribute_string($url_key) );
							$link_end = '</a>';
						}

						echo $link_start . $slide_image . $link_end;
						?>

						<div class="cUno-slideOverlay "></div>

						<?php $this->render_nav(); ?>
						<?php $this->render_caption($item, $key . '_slide'); ?>
					</div>
					<?php
					endforeach;
				endif; ?>
			</div>
			<?php
		}

		public function render_captions() {
			?>
			<div class="cUno-captions">
			<?php
				if( !empty($this->_items) ):
					foreach($this->_items as $key => $item):
						$this->render_caption($item, $key);
					endforeach;
				endif; ?>
			</div>
			<?php
		}

		public function render_caption( $slide, $key )
		{
			if( $slide['captions'] !== '' ): ?>

				<div class="cUno-caption">

					<?php if( $title = $slide['title'] ): ?>
						<h2 class="cUno-captionEl cUno-captionTitle"><?php echo $title ?></h2>
					<?php endif; ?>

					<?php if( $subtitle = $slide['subtitle'] ): ?>
						<div class="cUno-captionEl cUno-captionSubtitle"><?php echo $subtitle ?></div>
					<?php endif; ?>

					<?php if( $button_text = $slide['button_text'] ): ?>
						<div class="cUno-captionEl cUno-captionBtn">

							<?php
							$url_key = 'url'.$key;

							$this->add_render_attribute( $url_key , 'class', 'btn ' . $this->_settings['button_style'] );

							if( isset($slide['button_url']['url']) && $url = $slide['button_url']['url'] ){
								$this->add_render_attribute( $url_key , 'href', $url );

								if( $slide['button_url']['is_external'] ){
									$this->add_render_attribute( $url_key , 'target', '_blank' );
								}

								if( $slide['button_url']['nofollow'] ){
									$this->add_render_attribute( $url_key , 'rel', 'nofollow' );
								}
							} ?>
							<a <?php echo  $this->get_render_attribute_string($url_key); ?>>
								<?php echo $button_text; ?>
							</a>
						</div>
						<!-- .cUno-btn -->
					<?php endif; ?>

				</div><?php
			endif;
		}

		public function render_arrows()
		{
			if( $this->_settings['arrows'] === 'yes' ): ?>
				<div class="cUno-arrows <?php echo $this->_settings['arrows_on_hover'] === 'yes' ? '--hide-on-idle' : '' ?>">
					<?php
						echo reycore__arrowSvg(false);
						echo reycore__arrowSvg();
					?>
				</div>
			<?php endif;
		}

		public function render_nav(){
			if( $this->_settings['dots'] === 'yes' && !empty($this->_items) ){ ?>
				<div class="cUno-nav <?php  echo $this->_settings['stretch_content'] !== '' ? '--dots-pos--' . $this->_settings['dots_position'] : ''; ?>">
					<?php
					foreach($this->_items as $key => $item):
						printf('<button data-index="%s"></button>', esc_attr($key));
					endforeach; ?>
				</div>
			<?php }
		}


		protected function render() {

			$this->_settings = $this->get_settings_for_display();
			$this->_items = $this->_settings['items'];

			$this->render_start();
			$this->render_slides();
			// $this->render_nav();
			// $this->render_captions();
			$this->render_arrows();
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
