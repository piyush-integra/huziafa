<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if(!class_exists('ReyCore_Widget_Cover_Blurry')):
	/**
	 *
	 * Elementor widget.
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Widget_Cover_Blurry extends \Elementor\Widget_Base {

		private $_settings = [];
		private $_items = [];

		public function get_name() {
			return 'reycore-cover-blurry';
		}

		public function get_title() {
			return __( 'Cover - Blurry Slider', 'rey-core' );
		}

		public function get_icon() {
			return 'rey-font-icon-general-r';
		}

		public function get_categories() {
			return [ 'rey-theme-covers' ];
		}

		public function get_script_depends() {
			return [ 'jquery-slick', 'reycore-widget-cover-blurry-scripts' ];
		}

		public function get_custom_help_url() {
			return 'https://support.reytheme.com/kb/rey-elements-covers/#blurry-slider';
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
					'label' => __( 'Background Type', 'rey-core' ),
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
				'video_play_on_mobile',
				[
					'label' => esc_html__( 'Play video on mobile', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
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
				'image',
				[
				'label' => __( 'Image', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'default' => [
						'url' => \Elementor\Utils::get_placeholder_image_src(),
					],
					'dynamic' => [
						'active' => true,
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
				'overlay_color',
				[
					'label' => __( 'Overlay Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} {{CURRENT_ITEM}} .cBlurry-slideOverlay' => 'background-color: {{VALUE}}',
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
				'label',
				[
					'label'       => __( 'Label Text', 'rey-core' ),
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
				]
			);

			$items->add_control(
				'align',
				[
					'label' => esc_html__( 'Horizontal Alignment', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Default -', 'rey-core' ),
						'start'  => esc_html__( 'Start', 'rey-core' ),
						'center'  => esc_html__( 'Center', 'rey-core' ),
						'end'  => esc_html__( 'End', 'rey-core' ),
					],
				]
			);

			$items->add_control(
				'text_color',
				[
					'label' => __( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} {{CURRENT_ITEM}} .cBlurry-caption' => 'color: {{VALUE}}',
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
				]
			);

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
							'label' => __( 'Label Text #1', 'rey-core' ),
							'title' => __( 'Title Text #1', 'rey-core' ),
							'subtitle' => __( 'Subtitle Text #1', 'rey-core' ),
							'button_text' => __( 'Button Text #1', 'rey-core' ),
							'button_url' => [
								'url' => '#',
							],
						],
						[
							'image' => [
								'url' => \Elementor\Utils::get_placeholder_image_src(),
							],
							'captions' => 'yes',
							'label' => __( 'Label Text #2', 'rey-core' ),
							'title' => __( 'Title Text #2', 'rey-core' ),
							'subtitle' => __( 'Subtitle Text #2', 'rey-core' ),
							'button_text' => __( 'Button Text #2', 'rey-core' ),
							'button_url' => [
								'url' => '#',
							],
						],
					],
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


			$this->end_controls_section();

			/**
			 * Social Icons
			 */

			$this->start_controls_section(
				'section_social',
				[
					'label' => __( 'Social Icons', 'rey-core' ),
				]
			);

			$social_icons = new \Elementor\Repeater();

			$social_icons->add_control(
				'social',
				[
					'label' => __( 'Show Elements', 'plugin-domain' ),
					'type' => \Elementor\Controls_Manager::SELECT2,
					'label_block' => true,
					'options' => reycore__social_icons_list_select2(),
					'default' => 'wordpress',
				]
			);

			$social_icons->add_control(
				'link',
				[
					'label' => __( 'Link', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::URL,
					'label_block' => true,
					'default' => [
						'is_external' => 'true',
					],
					'placeholder' => __( 'https://your-link.com', 'rey-core' ),
				]
			);

			$this->add_control(
				'social_icon_list',
				[
					'label' => __( 'Social Icons', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $social_icons->get_controls(),
					'default' => [
						[
							'social' => 'facebook',
						],
						[
							'social' => 'twitter',
						],
						[
							'social' => 'google-plus',
						],
					],
					'title_field' => '{{{ social.replace( \'-\', \' \' ).replace( /\b\w/g, function( letter ){ return letter.toUpperCase() } ) }}}',
					'prevent_empty' => false,
				]
			);


			$this->add_control(
				'social_text',
				[
					'label' => __( '"Follow" Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => __( 'FOLLOW US', 'rey-core' ),
					'placeholder' => __( 'eg: FOLLOW US', 'rey-core' ),
				]
			);

			$this->end_controls_section();

			/**
			 * Scroll Button
			 */

			$this->start_controls_section(
				'section_scroll',
				[
					'label' => __( 'Scroll Button', 'rey-core' ),
				]
			);

			$this->add_control(
				'scroll_decoration',
				[
					'label' => __( 'Show Scroll Decoration', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'scroll_text',
				[
					'label' => __( '"Scroll" Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => __( 'SCROLL', 'rey-core' ),
					'placeholder' => __( 'eg: SCROLL', 'rey-core' ),
					'condition' => [
						'scroll_decoration' => 'yes',
					],
				]
			);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_slider',
				[
					'label' => __( 'Slider Settings', 'rey-core' ),
				]
			);

			$this->add_control(
				'autoplay',
				[
					'label' => __( 'Autoplay', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
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
						'autoplay!' => '',
					],
				]
			);

			$this->add_control(
				'arrows',
				[
					'label' => __( 'Arrows Navigation', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'bars_nav',
				[
					'label' => __( 'Bars Navigation', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->end_controls_section();


			$this->start_controls_section(
				'section_general_style',
				[
					'label' => __( 'General Styles', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_control(
				'bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-coverBlurry' => '--bg-color: {{VALUE}}',
					],
				]
			);

			$this->add_responsive_control(
				'custom_height',
				[
					'label' => __( 'Height', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 1440,
						],
						'vh' => [
							'min' => 0,
							'max' => 100,
						],
						'vw' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'size_units' => [ 'px', 'vh', 'vw' ],
					'selectors' => [
						'{{WRAPPER}} .cBlurry-slides, {{WRAPPER}} .slick-list, {{WRAPPER}} .slick-track, {{WRAPPER}} .cBlurry-slideBlur .cBlurry-slideContent' => 'height: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'blur',
				[
					'label' => esc_html__( 'Enable Blur', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'blur_width',
				[
					'label' => __( 'Blur Overlay Width', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'min' => 3,
							'max' => 100,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .cBlurry-slide.--active .cBlurry-slideBlur' => 'width: {{SIZE}}%;'
					],
					'condition' => [
						'blur' => 'yes',
					],
				]
			);

			$this->add_control(
				'blur_align',
				[
					'label' => esc_html__( 'Right-aligned Blur Overlay', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'blur' => 'yes',
					],
				]
			);

			$this->add_control(
				'content_title',
				[
				   'label' => esc_html__( 'Content', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'content_width',
				[
					'label' => __( 'Content Width', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 1440,
						],
						'vw' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'size_units' => [ 'px', 'vh', 'vw' ],
					'selectors' => [
						'{{WRAPPER}} .rey-coverBlurry .cBlurry-caption' => 'max-width: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'x_align',
				[
					'label' => esc_html__( 'Horizontal Alignment', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'start',
					'options' => [
						'start'  => esc_html__( 'Start', 'rey-core' ),
						'center'  => esc_html__( 'Center', 'rey-core' ),
						'end'  => esc_html__( 'End', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'y_align',
				[
					'label' => esc_html__( 'Vertical Alignment', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'center',
					'options' => [
						'start'  => esc_html__( 'Start', 'rey-core' ),
						'center'  => esc_html__( 'Center', 'rey-core' ),
						'end'  => esc_html__( 'End', 'rey-core' ),
					],
				]
			);


			$this->end_controls_section();

			$this->start_controls_section(
				'section_slides_style',
				[
					'label' => __( 'Slides Styles', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'label_typo',
					'label' => esc_html__('Label Typography', 'rey-core'),
					'selector' => '{{WRAPPER}} .cBlurry-captionLabel',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'title_typo',
					'label' => esc_html__('Title Typography', 'rey-core'),
					'scheme' => \Elementor\Scheme_Typography::TYPOGRAPHY_1,
					'selector' => '{{WRAPPER}} .cBlurry-captionTitle',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'subtitle_typo',
					'label' => esc_html__('Sub-Title Typography', 'rey-core'),
					'selector' => '{{WRAPPER}} .cBlurry-captionSubtitle',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'button_typo',
					'label' => esc_html__('Button Typography', 'rey-core'),
					'selector' => '{{WRAPPER}} .cBlurry-captionBtn a',
				]
			);

			$this->add_control(
				'button_style',
				[
					'label' => __( 'Button Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'btn-primary-outline btn-dash btn-rounded',
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

			$this->end_controls_section();
		}

		public function render_start(){

			$classes = [
				'rey-coverBlurry',
			];

			if( $this->_settings['blur'] === 'yes' ){
				$classes[] = '--hasBlur';
				if( $this->_settings['blur_align'] === 'yes' ){
					$classes[] = '--blurAlign-right';
				}
			}

			if( ! reycore__preloader_is_active() ){
				$classes[] = '--loading';
			}

			$this->add_render_attribute( 'wrapper', 'class', $classes );

			if( count($this->_items) > 1 ) {
				$this->add_render_attribute( 'wrapper', 'data-slider-settings', wp_json_encode([
					'autoplay' => $this->_settings['autoplay'] !== '',
					'autoplaySpeed' => $this->_settings['autoplay_duration'],
				]) );
			}
			?>
			<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
				<div class="cBlurry-loadingBg cBlurry--abs"></div>
			<?php
		}

		public function render_end(){
			?>
			</div>
			<?php
			echo ReyCoreElementor::edit_mode_widget_notice(['full_viewport', 'tabs_modal']);
		}

		public function render_slides(){
			?>
			<div class="cBlurry-slides">
			<?php
				if( !empty($this->_items) ):
					foreach($this->_items as $key => $item):

						$classes = [
							'cBlurry-slide',
							'--' . esc_attr($item['bg_type']),
							'elementor-repeater-item-' . esc_attr($item['_id']),
							'align' => '--content-x-' . $this->_settings['x_align'],
							'--content-y-' . $this->_settings['y_align']
						];

						if( isset($item['align']) && $item['align'] !== '' ){
							$classes['align'] = '--content-x-' . $item['align'];
						}

					?>
					<div class="<?php echo implode(' ', $classes); ?>">

						<?php
						$slide_content = '';

						$slide_image = reycore__get_attachment_image( [
							'image' => $item['image'],
							'size' => $this->_settings['image_size'],
							'attributes' => ['class'=>'cBlurry-slideContent cBlurry--abs']
						] );

						if( $item['bg_type'] === 'video' ) {

							$slide_content = reycore__get_video_html([
								'video_url' => $item['html_video'],
								'class' => 'cBlurry-slideContent cBlurry--abs',
								'mobile' => isset($item['video_play_on_mobile']) && $item['video_play_on_mobile'] === 'yes',
							]);
						}

						elseif( $item['bg_type'] === 'youtube' ) {

							$slide_content = reycore__get_youtube_iframe_html([
								'video_url' => $item['yt_video'],
								'class' => 'cBlurry-slideContent cBlurry--abs ',
								'html_id' => 'yt' . $item['_id'],
								// only show video preview if image not provided
								'add_preview_image' => empty($slide_image),
								'mobile' => isset($item['video_play_on_mobile']) && $item['video_play_on_mobile'] === 'yes',
							]);
						}
						?>

						<div class="cBlurry-slideContent-wrapper cBlurry--abs">
							<?php
								echo $slide_content;
								echo $slide_image;
							?>
							<div class="cBlurry-slideOverlay cBlurry--abs"></div>
						</div>

						<?php if( $this->_settings['blur'] === 'yes' ): ?>

							<div class="cBlurry-slideBlur cBlurry--abs">
								<?php echo $item['bg_type'] == 'image' ? $slide_image : ''; ?>
								<div class="cBlurry-slideOverlay cBlurry--abs"></div>
							</div>

						<?php endif; ?>

						<div class="cBlurry-slideBg cBlurry--abs"></div>

						<?php $this->render_caption($item, $key); ?>

					</div>
					<?php
					endforeach;
				endif; ?>

			</div>
			<?php
		}


		public function render_caption( $slide, $key )
		{
			if( $slide['captions'] !== '' ): ?>

				<div class="cBlurry-caption">

					<?php if( $label = $slide['label'] ): ?>
					<div class="cBlurry-captionEl cBlurry-captionLabel"><?php echo $label ?></div>
					<?php endif; ?>

					<?php if( $title = $slide['title'] ): ?>
						<h2 class="cBlurry-captionEl cBlurry-captionTitle"><?php echo $title ?></h2>
					<?php endif; ?>

					<?php if( $subtitle = $slide['subtitle'] ): ?>
						<div class="cBlurry-captionEl cBlurry-captionSubtitle"><?php echo $subtitle ?></div>
					<?php endif; ?>

					<?php if( $button_text = $slide['button_text'] ): ?>
						<div class="cBlurry-captionEl cBlurry-captionBtn">

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
						<!-- .cBlurry-btn -->
					<?php endif; ?>

				</div><?php
			endif;
		}

		public function render_arrows()
		{
			if( $this->_settings['arrows'] === 'yes' ): ?>
				<div class="cBlurry-arrows">
					<?php
						echo reycore__arrowSvg(false);
						echo reycore__arrowSvg();
					?>
				</div>
			<?php endif;
		}

		public function render_footer(){
			?>
			<div class="cBlurry-footer">
				<div class="cBlurry-footerInner">
				<?php
					$this->render_social();
					$this->render_nav();
					$this->render_scroll();
				?>
				</div>
			</div>
			<?php
		}

		public function render_scroll()
		{
			if( $this->_settings['scroll_decoration'] === 'yes' ): ?>
			<div class="cBlurry-scroll">
				<a href="#" class="rey-scrollDeco rey-scrollDeco--default" data-target="next">
					<span class="rey-scrollDeco-text"><?php echo $this->_settings['scroll_text'] ?></span>
					<span class="rey-scrollDeco-line"></span>
				</a>
			</div>
			<?php endif;
		}

		public function render_nav(){
			if( $this->_settings['bars_nav'] === 'yes' ){ ?>
				<div class="cBlurry-nav"></div>
			<?php }
		}

		public function render_social(){

			if( $social_icon_list = $this->_settings['social_icon_list'] ): ?>

				<div class="cBlurry-social">

					<?php if($social_text = $this->_settings['social_text']): ?>
						<div class="cBlurry-socialText"><?php echo $social_text ?></div>
					<?php endif; ?>

					<div class="cBlurry-socialIcons">
						<?php
						foreach ( $social_icon_list as $index => $item ):
							$link_key = 'link_' . $index;

							$this->add_render_attribute( $link_key, 'href', $item['link']['url'] );

							if ( $item['link']['is_external'] ) {
								$this->add_render_attribute( $link_key, 'target', '_blank' );
							}

							if ( $item['link']['nofollow'] ) {
								$this->add_render_attribute( $link_key, 'rel', 'nofollow' );
							}

							?>
							<a class="cBlurry-socialIcons-link" <?php echo $this->get_render_attribute_string( $link_key ); ?>>
								<?php echo reycore__get_svg_social_icon([ 'id'=>$item['social'] ]); ?>
							</a>
						<?php endforeach; ?>
					</div>

				</div>
				<!-- .cBlurry-social -->
			<?php endif;
		}

		protected function render() {

			$this->_settings = $this->get_settings_for_display();
			$this->_items = apply_filters('reycore/elementor/blurry_slider/items', $this->_settings['items'], $this->get_id());

			$this->render_start();
			$this->render_slides();
			$this->render_arrows();
			$this->render_footer();
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
