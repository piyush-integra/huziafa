<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Element_Section') ):
	/**
	 * Section Overrides and customizations
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Element_Section {

		function __construct(){
			add_action( 'elementor/element/section/section_background/before_section_end', [$this, 'bg_settings'], 10);
			add_action( 'elementor/element/section/section_background/after_section_end', [$this, 'slideshow_settings'], 10);
			add_action( 'elementor/element/section/section_background_overlay/before_section_end', [$this,'slideshow_bg_overlay_settings'], 10);
			add_action( 'elementor/element/section/section_layout/before_section_end', [$this, 'layout_settings'], 10);
			add_action( 'elementor/element/section/section_layout/after_section_end', [$this, 'modal_settings'], 10);
			add_action( 'elementor/element/section/section_layout/after_section_end', [$this, 'tabs_settings'], 10);
			add_action( 'elementor/element/section/section_effects/before_section_end', [$this, 'effects_settings'], 10);
			add_action( 'elementor/element/section/section_advanced/before_section_end', [$this, 'section_advanced'], 10);
			add_action( 'elementor/element/section/_section_responsive/after_section_end', [$this, 'custom_css_settings'], 10);
			add_action( 'elementor/frontend/section/before_render', [$this, 'before_render'], 10);
			add_action( 'elementor/frontend/section/after_render', [$this, 'after_render'], 10);
			add_action('elementor/element/after_add_attributes', [$this, 'after_add_attributes'], 10);
			add_filter( 'elementor/section/print_template', [$this, 'print_template'], 10 );
		}

		/**
		 * Add slideshow background option
		 *
		 * @since 1.0.0
		 */
		function bg_settings( $element )
		{
			// extract background args
			// group control is not available, so only get main bg control
			$bg = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'background_background' );
			// add new condition, for REY slideshow background
			$bg['options']['rey_slideshow'] = [
				'title' => _x( 'Background Slideshow (Rey Theme)', 'Background Control', 'rey-core' ),
				'icon' => 'fa fa-arrows-h',
			];
			$bg['prefix_class'] = 'rey-section-bg--';
			$element->update_control( 'background_background', $bg );

			// Add Dynamic switcher
			$element->add_control(
				'rey_dynamic_bg',
				[
					'label' => esc_html__( 'Use Post/Category Image (Rey)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'background_background' => 'classic',
					],
				]
			);

			// adds desktop-only gradient
			// Add Dynamic switcher
			$element->add_control(
				'rey_desktop_gradient',
				[
					'label' => esc_html__( 'Desktop-Only Gradient', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'background_background' => 'gradient',
					],
					'prefix_class' => 'rey-gradientDesktop-',
				]
			);
		}

		/**
		 * Add custom slideshow settings into Elementor's Section
		 *
		 * @since 1.0.0
		 */
		function slideshow_settings( $element )
		{
			$element->start_controls_section(
				'rey_section_slideshow',
				[
					'label' => __( 'Background Slideshow (Rey Theme)', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
					'hide_in_inner' => true,
					'condition' => [
						'background_background' => 'rey_slideshow'
					]
				]
			);

			$element->add_control(
				'rey_slideshow_autoplay',
				[
					'label' => __( 'Autoplay', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$element->add_control(
				'rey_slideshow_autoplay_time',
				[
					'label' => __( 'Autoplay Timeout', 'rey-core' ) . ' (ms)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 5000,
					'min' => 100,
					'max' => 30000,
					'step' => 10,
					'placeholder' => 5000,
					'condition' => [
						'rey_slideshow_autoplay' => 'yes',
					],
				]
			);

			$element->add_control(
				'rey_slideshow_speed',
				[
					'label' => __( 'Transition speed', 'rey-core' ) . ' (ms)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 500,
					'min' => 100,
					'max' => 5000,
					'step' => 10,
					'placeholder' => 500,
				]
			);

			$element->add_control(
				'rey_slideshow_effect',
				[
					'label' => __( 'Slideshow Effect', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'slide',
					'options' => [
						'slide'  => __( 'Slide', 'rey-core' ),
						'fade'  => __( 'Fade In/Out', 'rey-core' ),
						'scaler'  => __( 'Scale & Fade', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'rey_slideshow_nav',
				[
					'label' => __( 'Connect the dots', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'label_block' => true,
					'placeholder' => __( 'eg: #toggle-boxes-gd4fg6', 'rey-core' ),
					'description' => __( 'Use the Toggle Boxes widget and paste its unique id here. If empty, the first Toggler widget found in this section will be used, if any.', 'rey-core' ),
				]
			);

			$element->add_control(
				'rey_slideshow_mobile',
				[
					'label' => __( 'Show on mobiles?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$slides = new \Elementor\Repeater();

			$slides->add_control(
				'img',
				[
				   'label' => __( 'Choose Image', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'default' => [
						'url' => \Elementor\Utils::get_placeholder_image_src(),
					],
					'selectors' => [
						'{{WRAPPER}} {{CURRENT_ITEM}} ' => 'background-image: url("{{URL}}");',
					],
				]
			);

			$slides->add_responsive_control(
				'img_position',
				[
				   'label' => __( 'Position (X & Y)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '50% 50%',
					'selectors' => [
						'{{WRAPPER}} {{CURRENT_ITEM}}' => 'background-position: {{VALUE}};',
					],
				]
			);

			$element->add_control(
				'rey_slideshow_slides',
				[
					'label' => __( 'Slides', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $slides->get_controls(),
				]
			);

			$element->end_controls_section();
		}

		/**
		 * Update the conditions of the background overlay section,
		 * to apply for rey_slideshow as well.
		 */
		function slideshow_bg_overlay_settings( $stack )
		{
		 	// Disabled in 2.7.0+ because Elementor has been updated without any background type condition
			if( version_compare(ELEMENTOR_VERSION, '2.7.0', '>=' ) ) {
				return;
			}

			// get section args
			$section_bg_overlay = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $stack->get_unique_name(), 'section_background_overlay' );
			// pass custom condition
			$section_bg_overlay['condition']['background_background'][] = 'rey_slideshow';
			// update section
			$stack->update_control( 'section_background_overlay', $section_bg_overlay, ['recursive'=> true] );
		}

		/**
		 * Filter Section Print Content
		 *
		 * @since 1.0.0
		 **/
		function print_template( $template )
		{
			$slideshow = "
			<# if ( settings.background_background && settings.background_background == 'rey_slideshow' && settings.rey_slideshow_slides ) { #>
				<# var slide_config = JSON.stringify({
					'autoplay': settings.rey_slideshow_autoplay,
					'autoplaySpeed': settings.rey_slideshow_autoplay_time,
					'speed': settings.rey_slideshow_speed,
					'mobile': settings.rey_slideshow_mobile !== ''
				}); #>
				<div class='rey-section-slideshow' data-rey-slideshow-effect='{{settings.rey_slideshow_effect}}' data-rey-slideshow-nav='{{settings.rey_slideshow_nav}}' data-rey-slideshow-settings='{{slide_config}}'>
				<# _.each( settings.rey_slideshow_slides, function( item, index ) { #>
					<div class='rey-section-slideshowItem rey-section-slideshowItem--{{index}} elementor-repeater-item-{{item._id}}'></div>
				<# } ); #>
				</div>
			<#	} #>";

			return $slideshow . $template;
		}

		/**
		 * Add custom settings into Elementor's Section
		 *
		 * @since 1.0.0
		 */
		function layout_settings( $element )
		{
			$element->remove_control( 'stretch_section' );

			$element->start_injection( [
				'of' => '_title',
			] );

			$element->add_control(
				'rey_stretch_section',
				[
					'label' => __( 'Stretch Section', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'return_value' => 'section-stretched',
					'prefix_class' => 'rey-',
					'hide_in_inner' => true,
					'description' => __( 'Stretch the section to the full width of the page using plain CSS.', 'rey-core' ) . sprintf( ' <a href="%1$s" target="_blank">%2$s</a>', 'https://go.elementor.com/stretch-section/', __( 'Learn more.', 'rey-core' ) ),
				]
			);

			$element->end_injection();


			$element->start_injection( [
				'of' => 'html_tag',
			] );

			$element->add_control(
				'rey_flex_wrap',
				[
					'label' => __( 'Multi-rows (Flex Wrap)', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'description' => __( 'Enabling this option will allow columns on separate rows. Note that manual resizing handles are disabled.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Yes', 'rey-core' ),
					'label_off' => __( 'No', 'rey-core' ),
					'return_value' => 'rey-flexWrap',
					'default' => '',
					'prefix_class' => '',
					'separator' => 'before'
				]
			);

			$element->end_injection();
		}


		/**
		 * Add custom settings into Elementor's Section
		 *
		 * @since 1.0.0
		 */
		function modal_settings( $element )
		{

			$element->start_controls_section(
				'section_modal',
				[
					'label' => __( 'Modal Settings', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'tab' => \Elementor\Controls_Manager::TAB_LAYOUT
				]
			);

			$element->add_control(
				'rey_modal',
				[
					'label' => __( 'Enable Modal', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'modal-section',
					'default' => '',
					'prefix_class' => 'rey-',
					'hide_in_inner' => true,
				]
			);

			$element->add_control(
				'rey_modal_width',
				[
				'label' => __( 'Width', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'vw' ],
					'range' => [
						'px' => [
							'min' => 320,
							'max' => 2560,
							'step' => 1,
						],
						'vw' => [
							'min' => 10,
							'max' => 100,
							'step' => 1,
						],
					],
					'default' => [
						'unit' => 'vw',
						'size' => 80,
					],
					'selectors' => [
						'{{WRAPPER}}' => 'max-width: {{SIZE}}{{UNIT}};',
					],
					'condition' => [
						'rey_modal!' => '',
					],
				]
			);

			$element->add_control(
				'rey_modal_close_pos',
				[
					'label' => __( 'Close Position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => __( 'Inside', 'rey-core' ),
						'--outside'  => __( 'Outside', 'rey-core' ),
					],
					'render_type' => 'none',
					'condition' => [
						'rey_modal!' => '',
					],
				]
			);

			$element->add_control(
				'rey_modal_splash',
				[
					'label' => __( 'Auto pop-up?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => __( 'Disabled', 'rey-core' ),
						'scroll'  => __( 'On Page Scroll', 'rey-core' ),
						'time'  => __( 'On Page Load', 'rey-core' ),
						'exit'  => __( 'On Exit Intent', 'rey-core' ),
					],
					'render_type' => 'none',
					'separator' => 'before',
					'condition' => [
						'rey_modal!' => '',
					],
				]
			);

			$element->add_control(
				'rey_modal_splash_scroll_distance',
				[
					'label' => esc_html__( 'Scroll Distance (%)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 50,
					'min' => 0,
					'max' => 100,
					'step' => 1,
					'condition' => [
						'rey_modal!' => '',
						'rey_modal_splash' => 'scroll',
					],
				]
			);

			$element->add_control(
				'rey_modal_splash_timeframe',
				[
					'label' => esc_html__( 'Timeframe (Seconds)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 4,
					'min' => 0,
					'max' => 500,
					'step' => 1,
					'condition' => [
						'rey_modal!' => '',
						'rey_modal_splash' => 'time',
					],
				]
			);

			$element->add_control(
				'rey_modal_splash_nag',
				[
					'label' => esc_html__( 'Disable Nagging?', 'rey-core' ),
					'description' => esc_html__( 'When the visitor closes the splash popup, he won\'t be nagged with this splash for one day.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'rey_modal!' => '',
						'rey_modal_splash!' => '',
					],
				]
			);

			$element->add_control(
				'rey_modal_id',
				[
					'label' => __( 'Modal ID', 'rey-core' ),
					'label_block' => true,
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => uniqid('#modal-'),
					'placeholder' => __( 'eg: #some-unique-id', 'rey-core' ),
					'description' => __( 'Copy the ID above and paste it into the link text-fields, where specified.', 'rey-core' ),
					'separator' => 'before',
					'condition' => [
						'rey_modal!' => '',
					],
					'render_type' => 'none',
				]
			);

			$element->end_controls_section();
		}

		/**
		 * Add custom settings into Elementor's Section
		 *
		 * @since 1.0.0
		 */
		function tabs_settings( $element )
		{

			$element->start_controls_section(
				'section_tabs',
				[
					'label' => __( 'Tabs Settings', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'tab' => \Elementor\Controls_Manager::TAB_LAYOUT
				]
			);

			$element->add_control(
				'rey_tabs',
				[
					'label' => __( 'Enable Tabs', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'tabs-section',
					'default' => '',
					'prefix_class' => 'rey-',
					// 'hide_in_inner' => true,
				]
			);


			$element->add_control(
				'rey_tabs_id',
				[
					'label' => __( 'Tabs ID', 'rey-core' ),
					'label_block' => true,
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => uniqid('tabs-'),
					'placeholder' => __( 'eg: some-unique-id', 'rey-core' ),
					'description' => __( 'Copy the ID above and paste it into the "Toggle Boxes" Widget where specified.', 'rey-core' ),
					'condition' => [
						'rey_tabs!' => '',
					],
					'render_type' => 'none',
				]
			);

			$element->add_control(
				'rey_tabs_effect',
				[
					'label' => esc_html__( 'Tabs Effect', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'default',
					'options' => [
						'default'  => esc_html__( 'Fade', 'rey-core' ),
						'slide'  => esc_html__( 'Fade & Slide', 'rey-core' ),
					],
				]
			);

			$element->end_controls_section();
		}


		/**
		 * Add custom settings into Elementor's Section
		 *
		 * @since 1.0.0
		 */
		function effects_settings( $element )
		{

			if( ReyCoreElementor::animations_enabled() ):

				$element->add_control(
					'rey_animation_type',
					[
						'label' => __( 'Entrace Effect', 'rey-core' ) . reyCoreElementor::getReyBadge(),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => '',
						'options' => [
							''  => __( '- Select -', 'rey-core' ),
							'reveal'  => __( 'Reveal', 'rey-core' ),
							'fade-in'  => __( 'Fade In', 'rey-core' ),
							'fade-slide'  => __( 'Fade In From Bottom', 'rey-core' ),
						],
						'render_type' => 'none',
						// 'condition' => [
						// 	'sticky!' => ['top', 'bottom'],
						// ],
					]
				);

				$element->add_control(
					'rey_entrance_title',
					[
					'label' => __( 'ENTRANCE SETTINGS', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
						'condition' => [
							'rey_animation_type!' => [''],
						],
					]
				);

				$element->add_control(
					'rey_animation_duration',
					[
						'label' => __( 'Animation Duration', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => '',
						'options' => [
							'slow' => __( 'Slow', 'rey-core' ),
							'' => __( 'Normal', 'rey-core' ),
							'fast' => __( 'Fast', 'rey-core' ),
						],
						'condition' => [
							'rey_animation_type!' => [''],
						],
						'render_type' => 'none',
					]
				);

				$element->add_control(
					'rey_animation_delay',
					[
						'label' => __( 'Animation Delay', 'rey-core' ) . ' (ms)',
						'type' => \Elementor\Controls_Manager::NUMBER,
						'default' => '',
						'min' => 0,
						'step' => 100,
						'condition' => [
							'rey_animation_type!' => [''],
						],
						'render_type' => 'none',
					]
				);

				$element->add_control(
					'rey_reveal_title',
					[
					'label' => __( 'REVEAL SETTINGS', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
						'condition' => [
							'rey_animation_type' => ['reveal'],
						],
					]
				);

				$element->add_control(
					'rey_animation_type_reveal_direction',
					[
						'label' => __( 'Reveal Direction', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => 'left',
						'options' => [
							'left'  => __( 'Left', 'rey-core' ),
							'top'  => __( 'Top', 'rey-core' ),
						],
						'condition' => [
							'rey_animation_type' => ['reveal'],
						],
						'render_type' => 'none',
					]
				);

				$element->add_control(
					'rey_animation_type__reveal_bg_color',
					[
						'label' => __( 'Reveal Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						// 'selectors' => [
						// 	'{{WRAPPER}}.rey-anim--reveal-bg .rey-anim--reveal-bgHolder' => 'background-color: {{VALUE}}',
						// ],
						'condition' => [
							'rey_animation_type' => ['reveal'],
						],
						'render_type' => 'none',
					]
				);
			endif;

			/**
			 * Scroll effects
			 */

			$element->add_control(
				'rey_scroll_effects',
				[
					'label' => __( 'Scroll Effects', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => __( 'None', 'rey-core' ),
						'clip-in'  => __( 'Clip In', 'rey-core' ),
						'clip-out'  => __( 'Clip Out', 'rey-core' ),
						'sticky'  => __( 'Sticky', 'rey-core' ),
					],
					// 'hide_in_inner' => true,
					'prefix_class' => 'rey-sectionScroll rey-sectionScroll--',
					'separator' => 'before',
					// 'condition' => [
					// 	'sticky!' => ['top', 'bottom'],
					// ],
				]
			);

			$element->add_control(
				'rey_clip_offset',
				[
					'label' => __( 'Clipping Offset', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 5,
					'max' => 300,
					'step' => 1,
					'condition' => [
						'rey_scroll_effects' => ['clip-in', 'clip-out'],
					],
					'selectors' => [
						'{{WRAPPER}}.rey-sectionScroll' => '--clip-offset: {{VALUE}}px; ',
					]
				]
			);

			$element->add_control(
				'rey_sticky_offset',
				[
					'label' => __( 'Sticky Offset', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 1,
					'max' => 300,
					'step' => 1,
					'condition' => [
						'rey_scroll_effects' => 'sticky',
					],
					'selectors' => [
						'{{WRAPPER}}.rey-sectionScroll' => '--sticky-offset: {{VALUE}}px; ',
					]
				]
			);

			$element->add_control(
				'rey_sticky_breakpoints',
				[
					'label' => __( 'Sticky Breakpoints', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT2,
					'multiple' => true,
					'label_block' => true,
					'default' => ['desktop'],
					'options' => [
						'desktop'  => __( 'Desktop', 'rey-core' ),
						'tablet'  => __( 'Tablet', 'rey-core' ),
						'mobile'  => __( 'Mobile', 'rey-core' ),
					],
					'condition' => [
						'rey_scroll_effects' => 'sticky',
					],
				]
			);

		}

		function custom_css_settings(  $element ){

			$element->start_controls_section(
				'section_rey_custom_CSS',
				[
					'label' => sprintf( '<span>%s</span><span class="rey-hasStylesNotice">%s</span>', __( 'Custom CSS', 'rey-core' ) , __( 'Has Styles!', 'rey-core' ) ) . reyCoreElementor::getReyBadge(),
					'tab' => \Elementor\Controls_Manager::TAB_ADVANCED,
					'hide_in_inner' => true,
				]
			);

			$css_desc = sprintf(__('<p class="rey-addMargin">Click to insert selector: <span class="rey-selectorCss js-insertToEditor" title="Click to insert">%s</span></p>', 'rey-core') , 'SECTION-ID {}' );

			$css_desc .= sprintf('<p class="rey-addMargin"><span></span><select class="js-insertSnippetToEditor">
				<option value="">%s</option>
				<option value="@media (max-width:767px) {}">< 767px (Mobile only)</option>
				<option value="@media (max-width:1024px) {}">< 1024px (Mobiles & Tablet)</option>
				<option value="@media (min-width:768px) and (max-width:1024px) {}">768px to 1024px (Tablet only)</option>
				<option value="@media (min-width:768px) {}">> 768px (Tablet & Desktop)</option>
				<option value="@media (min-width:1025px) {}">> 1025px (Desktop only)</option>
				<option value="@media (min-width:1025px) and (max-width:1440px) {}">1025px to 1440px (Desktop, until 1440px)</option>
				<option value="@media (min-width:1441px) {}">> 1441px (Desktop, from 1441px)</option>
			</select></p>', esc_html__('Insert media query snippet:', 'rey-core'));

			$css_desc .= __( '<p>For more advanced control over the section\'s CSS, or any other element, i suggest trying <a href="https://elementor.com/pro/" target="_blank">Elementor PRO</a>.</p>', 'rey-core' );

			$element->add_control(
				'rey_custom_css',
				[
					'type' => \Elementor\Controls_Manager::CODE,
					'label' => esc_html__('Custom CSS', 'rey-core'),
					'language' => 'css',
					'render_type' => 'ui',
					'show_label' => false,
					'separator' => 'none',
					'description' =>  $css_desc,
				]
			);

			$element->end_controls_section();

		}


		function after_add_attributes($element){

			if( 'section' !== $element->get_unique_name() ){
				return;
			}

			$settings = $element->get_settings_for_display();

			if( ReyCoreElementor::animations_enabled() && $settings['rey_animation_type'] != '' ):

				/**
				 * Hack to delay loading (lazy load) the video background if section is animated
				 */

				// Check if background enabled
				if( $settings['background_background'] === 'video' && $settings['background_video_link'] ){
					// add a temporary custom attribute
					$element->add_render_attribute( '_wrapper', 'data-rey-video-link', esc_attr($settings['background_video_link']) );
					// unset video link to remove it from data-settings attribute
					$frontend_settings = $element->get_render_attributes('_wrapper', 'data-settings');
					if( $frontend_settings && isset($frontend_settings[0]) && $frontend_settings_dec = json_decode($frontend_settings[0], true) ){
						unset($frontend_settings_dec['background_video_link']);
						$element->add_render_attribute( '_wrapper', 'data-settings', wp_json_encode( $frontend_settings_dec ), true );
					}
				}
			endif;

		}

		/**
		 * Render some attributes before rendering
		 *
		 * @since 1.0.0
		 **/
		function before_render( $element )
		{
			$settings = $element->get_settings_for_display();

			$classes = [];

			if( ReyCoreElementor::animations_enabled() && $settings['rey_animation_type'] != '' ):

				$classes[] = 'rey-animate-el';
				$classes[] = 'rey-anim--' . esc_attr( $settings['rey_animation_type'] );
				$classes[] = 'rey-anim--viewport';

				$config = [
					'id'               => $element->get_id(),
					'element_type'     => 'section',
					'animation_type'   => esc_attr( $settings['rey_animation_type'] ),
					'reveal_direction' => esc_attr( $settings['rey_animation_type_reveal_direction']),
					'reveal_bg'        => esc_attr( $settings['rey_animation_type__reveal_bg_color']),
				];

				if( $settings['rey_animation_delay'] ) {
					$config['delay']= esc_attr( $settings['rey_animation_delay'] );
				}

				if( $settings['rey_animation_duration'] ) {
					$config['duration']= esc_attr( $settings['rey_animation_duration'] );
				}

				$element->add_render_attribute( '_wrapper', 'data-rey-anim-config', wp_json_encode($config) );

			endif;


			// Modal
			if( $settings['rey_modal'] == 'modal-section' ){

				$modal_attributes[] = 'data-rey-modal-id="'. $settings['rey_modal_id'] .'"';

				if( isset($settings['rey_modal_splash']) && $splash = $settings['rey_modal_splash'] ){
					$modal_attributes[] = sprintf("data-rey-modal-splash='%s'", wp_json_encode([
						'type' => esc_attr($splash),
						'time' => esc_attr($settings['rey_modal_splash_timeframe']),
						'distance' => esc_attr($settings['rey_modal_splash_scroll_distance']),
						'nag' => $settings['rey_modal_splash_nag'] !== 'yes',
					]));
				}

				// Wrap section & add overlay
				echo '<div class="rey-modalSection" '. implode(' ', $modal_attributes) .'><div class="rey-modalSection-overlay"></div><div class="rey-modalSection-inner"><button class="rey-modalSection-close '. esc_attr($settings['rey_modal_close_pos']) .'">'. reycore__get_svg_icon(['id' => 'rey-icon-close']) .'</button>';
			}

			// Tabs
			if( $settings['rey_tabs'] == 'tabs-section' ){
				$element->add_render_attribute( '_wrapper', 'data-tabs-id', esc_attr($settings['rey_tabs_id']) );
				$classes[] = '--tabs-effect-' . esc_attr($settings['rey_tabs_effect']);
			}

			// Dynamic image background
			if( 'classic' === $settings['background_background'] && isset($settings['rey_dynamic_bg']) && $settings['rey_dynamic_bg'] === 'yes' ):
				$thumbnail_data = reycore__get_post_term_thumbnail();

				if( $thumbnail_url = $thumbnail_data['url'] ){
					$element->add_render_attribute( '_wrapper', 'style', sprintf('background-image:url(%s);', $thumbnail_url) );
				}
			endif;

			// Rey slidesjow
			if( 'rey_slideshow' === $settings['background_background'] && isset($settings['rey_slideshow_slides']) && !empty($settings['rey_slideshow_slides']) ):

				$element->add_script_depends('jquery-slick');

				$slideshow_config = [];
				if( $settings['rey_slideshow_autoplay'] ){
					$slideshow_config['autoplay'] = (bool) $settings['rey_slideshow_autoplay'];
					$slideshow_config['autoplaySpeed'] = absint( $settings['rey_slideshow_autoplay_time'] );
				}
				$slideshow_config['speed'] = absint( $settings['rey_slideshow_speed'] );
				$slideshow_config['mobile'] = $settings['rey_slideshow_mobile'] !== '';

				$element->add_render_attribute( 'slideshow_wrapper', 'data-rey-slideshow-settings', wp_json_encode($slideshow_config) );
				$element->add_render_attribute( 'slideshow_wrapper', 'data-rey-slideshow-effect', esc_attr( $settings['rey_slideshow_effect'] ) );
				$element->add_render_attribute( 'slideshow_wrapper', 'data-rey-slideshow-nav', esc_attr( $settings['rey_slideshow_nav'] ) );

				// Catch output
				ob_start();

			endif;

			// Sticky
			if( 'sticky' === $settings['rey_scroll_effects'] ){

				$sticky_config = [];

				if( $sticky_offset = $settings['rey_sticky_offset'] ){
					$sticky_config['offset'] = esc_attr($sticky_offset);
				}

				if( $sticky_breakpoints = $settings['rey_sticky_breakpoints'] ){
					$sticky_config['breakpoints'] = array_map('esc_attr', $sticky_breakpoints);
				}

				if( !empty($sticky_config) ){
					$element->add_render_attribute( '_wrapper', 'data-sticky-config', wp_json_encode($sticky_config) );
				}
			}

			if( !empty($classes) ){
				$element->add_render_attribute( '_wrapper', 'class', $classes );
			}

		}

		/**
		 * Tweak the CSS classes field.
		 */
		function section_advanced( $stack )
		{
			$controls_manager = \Elementor\Plugin::instance()->controls_manager;
			$unique_name = $stack->get_unique_name();

			// Margin
			$margin = $controls_manager->get_control_from_stack( $unique_name, 'margin' );
			$margin['condition'] = 'all';
			$margin['condition'] = [
				'rey_allow_horizontal_margin' => ''
			];
			$stack->update_control( 'margin', $margin );

			$stack->start_injection( [
				'at' => 'after',
				'of' => 'margin',
			] );

			$stack->add_control(
				'rey_allow_horizontal_margin',
				[
					'label' => __( 'Allow Horizontal Margins', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'return_value' => 'all',
				]
			);

			$stack->add_responsive_control(
				'rey_margin_all',
				[
					'label' => __( 'Margin', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}}' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'condition' => [
						'rey_allow_horizontal_margin' => 'all'
					],
				]
			);

			$stack->end_injection();


			// CSS CLASS - stretch field
			$css_classes = $controls_manager->get_control_from_stack( $unique_name, 'css_classes' );
			$css_classes['label_block'] = true;
			$stack->update_control( 'css_classes', $css_classes );

			// PADDING
			foreach (['', '_tablet', '_mobile'] as $key => $value) {
				$item = [];
				$item[$key] = $controls_manager->get_control_from_stack( $unique_name, 'padding' . $value );
				$item[$key]['selectors'] = [
					'{{WRAPPER}} > .elementor-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				];
				$stack->update_control( 'padding' . $value, $item[$key] );
			}
		}


		/**
		 * Add HTML after section rendering
		 *
		 * @since 1.0.0
		 **/
		function after_render( $element )
		{
			$settings = $element->get_settings_for_display();

			if( 'rey_slideshow' === $settings['background_background'] && isset($settings['rey_slideshow_slides']) && !empty($settings['rey_slideshow_slides']) ):

				$slideshow_html = sprintf(
					'<div class="rey-section-slideshow rey-section-slideshow--%s" %s>',
					esc_attr( $settings['rey_slideshow_effect'] ),
					$element->get_render_attribute_string( 'slideshow_wrapper' )
				);
					foreach ($settings['rey_slideshow_slides'] as $index => $item) {
						$slideshow_html .= '<div class="rey-section-slideshowItem rey-section-slideshowItem--'.$index.' elementor-repeater-item-'.$item['_id'].'"></div>';
					}
				$slideshow_html .= '</div>';

				// Collect output
				$content = ob_get_clean();

				$html_tag = $settings['html_tag'] ?: 'section';
				$query = sprintf('//%s[contains( @class, "elementor-element-%s")]', $html_tag, $element->get_id());

				if( $new_html = ReyCoreElementor::el_inject_html( $content, $slideshow_html, $query) ){
					$content = $new_html;
				}

				echo $content;

			elseif( $element->get_settings_for_display('rey_modal') == 'modal-section' ):
				echo '</div></div>';
			endif;
		}
	}

endif;
