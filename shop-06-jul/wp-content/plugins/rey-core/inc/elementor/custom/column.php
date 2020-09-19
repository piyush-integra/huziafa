<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Element_Column') ):
    /**
	 * Column Overrides and customizations
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Element_Column {

		function __construct(){
			add_action( 'elementor/element/column/layout/before_section_end', [$this,'layout_settings'], 10);
			add_action( 'elementor/element/column/section_style/before_section_end', [$this,'video_bg_settings'], 10);
			add_action( 'elementor/element/column/section_background_overlay/before_section_end', [$this,'video_bg_overlay_settings'], 10);
			add_action( 'elementor/element/column/section_effects/before_section_end', [$this,'effects_settings'], 10);
			add_action( 'elementor/element/column/section_advanced/before_section_end', [$this,'section_advanced'], 10);
			add_action( 'elementor/frontend/column/before_render', [$this,'before_render'], 10);
			add_action( 'elementor/frontend/column/after_render', [$this,'after_render'], 10);
			add_filter( 'elementor/column/print_template', [$this,'print_template'], 10 );
		}

		/**
		 * Add custom settings into Elementor's Columns
		 *
		 * @since 1.0.0
		 */
		function layout_settings( $element )
		{
			$control_manager = \Elementor\Plugin::instance()->controls_manager;

			foreach (['', '_tablet', '_mobile'] as $key => $value) {
				$item = [];
				$item[$key] = $control_manager->get_control_from_stack( $element->get_unique_name(), '_inline_size' . $value );
				$item[$key]['condition']['section_rey_flex_wrap'] = '';
				$element->update_control( '_inline_size' . $value, $item[$key] );
			}

			$element->start_injection( [
				'of' => '_inline_size_mobile',
			] );

			$element->add_control(
				'section_rey_flex_wrap',
				[
					'label' => __( 'Parent Section Flex Wrap', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HIDDEN,
					'default' => '',
				]
			);

			$element->add_responsive_control(
				'rey_flex_wrap_inline_size',
				[
					'label' => __( 'Column Width', 'rey-core' ) . ' (%)' ,
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 5,
					'max' => 100,
					'step' => 1,
					// 'required' => true,
					'device_args' => [
						\Elementor\Controls_Stack::RESPONSIVE_TABLET => [
							'max' => 100,
							'required' => false,
						],
						\Elementor\Controls_Stack::RESPONSIVE_MOBILE => [
							'max' => 100,
							'required' => false,
						],
					],
					'min_affected_device' => [
						\Elementor\Controls_Stack::RESPONSIVE_DESKTOP => \Elementor\Controls_Stack::RESPONSIVE_TABLET,
						\Elementor\Controls_Stack::RESPONSIVE_TABLET => \Elementor\Controls_Stack::RESPONSIVE_TABLET,
					],
					'selectors' => [
						'{{WRAPPER}}' => 'width: {{VALUE}}%',
					],
					'description' => __( 'This option will force a custom column size. Unlike the native option, this doesn\'t get recalculated, allowing columns to display like rows.', 'rey-core' ),
					'condition' => [
						'section_rey_flex_wrap!' => [''],
					],
				]
			);

			$element->end_injection();

			$element->add_responsive_control(
				'rey_custom_height',
				[
					'label' => __( 'Minimum Height', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'default' => [
						'size' => '',
					],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 1440,
						],
						'vh' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'size_units' => [ 'px', 'vh' ],
					'selectors' => [
						'{{WRAPPER}} > .elementor-column-wrap' => 'min-height: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$element->add_responsive_control(
				'rey_custom_width',
				[
					'label' => __( 'Custom Width', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'description' => __( 'Customize the width as you want to, it\'ll overwrite the default percent value. Use pixel value, calc(), auto or whatever.', 'rey-core' ),
					'placeholder' => __( 'eg: calc(100% - 300px)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'selectors' => [
						'(desktop+){{WRAPPER}}' => 'width: {{VALUE}};',
					],
					'device_args' => [
						\Elementor\Controls_Stack::RESPONSIVE_TABLET => [
							'selectors' => [
								'{{WRAPPER}}' => 'width: {{VALUE}};',
							],
						],
						\Elementor\Controls_Stack::RESPONSIVE_MOBILE => [
							'selectors' => [
								'{{WRAPPER}}' => 'width: {{VALUE}};',
							],
						],
					],
				]
			);

			$element->add_responsive_control(
				'rey_col_order',
				[
					'label' => __( 'Column Order', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Default -', 'rey-core' ),
						'-1'  => esc_html__( 'First', 'rey-core' ),
						'1'  => esc_html__( '1', 'rey-core' ),
						'2'  => esc_html__( '2', 'rey-core' ),
						'3'  => esc_html__( '3', 'rey-core' ),
						'4'  => esc_html__( '4', 'rey-core' ),
						'5'  => esc_html__( '5', 'rey-core' ),
						'6'  => esc_html__( '6', 'rey-core' ),
						'7'  => esc_html__( '7', 'rey-core' ),
						'8'  => esc_html__( '8', 'rey-core' ),
						'9'  => esc_html__( '9', 'rey-core' ),
						'10'  => esc_html__( '10', 'rey-core' ),
						'999'  => esc_html__( 'Last', 'rey-core' ),
					],
					'selectors' => [
						'{{WRAPPER}}' => 'order: {{VALUE}};',
					],
				]
			);
		}


		/**
		 * Add video BG
		 *
		 * @since 1.0.0
		 */
		function video_bg_settings( $element )
		{
			if( ReyCoreElementor::get_compatibilities('column_video') )
			{
				// extract background args
				// group control is not available, so only get main bg control
				$bg = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'background_background' );
				// add new condition, for REY video background
				$bg['options']['video'] = [
					'title' => _x( 'Background Video', 'Background Control', 'rey-core' ),
					'icon' => 'fa fa-video-camera',
				];
				$bg['prefix_class'] = 'rey-colbg--';
				$element->update_control( 'background_background', $bg );

				// remove options
				if( ! ReyCoreElementor::get_compatibilities('video_bg_play_on_mobile') ){
					$element->remove_control('background_play_on_mobile');
				}
			}
		}


		/**
		 * Update the conditions of the background overlay section,
		 * to apply for rey_video as well.
		 */
		function video_bg_overlay_settings( $stack )
		{
			if( ReyCoreElementor::get_compatibilities('column_video') )
			{
				// get section args
				$section_bg_overlay = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $stack->get_unique_name(), 'section_background_overlay' );
				// pass custom condition
				$section_bg_overlay['condition']['background_background'][] = 'video';
				// update section
				$stack->update_control( 'section_background_overlay', $section_bg_overlay, ['recursive'=> true] );
			}
		}


		/**
		 * Add custom settings into Elementor's Column
		 *
		 * @since 1.0.0
		 */
		function effects_settings( $element )
		{

			if( ReyCoreElementor::animations_enabled() ):

				$element->add_control(
					'rey_animation_type',
					[
						'label' => __( 'Entrance Effect', 'rey-core' ) . reyCoreElementor::getReyBadge(),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => '',
						'options' => [
							''  => __( '- Select -', 'rey-core' ),
							'reveal'  => __( 'Reveal', 'rey-core' ),
							'fade-in'  => __( 'Fade In', 'rey-core' ),
							'fade-slide'  => __( 'Fade In From Bottom', 'rey-core' ),
							'slide-hidden'  => __( 'Slide Hidden From Bottom', 'rey-core' ),
						],
						'prefix_class' => 'rey-animate-el rey-anim--',
						// 'render_type' => 'none',
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
					'rey_animation_activation_trigger',
					[
						'label' => __( 'Activation Trigger', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => 'viewport',
						'options' => [
							'viewport'  => __( 'In viewport', 'rey-core' ),
							'parent'  => __( 'Parent section has animated', 'rey-core' ),
						],
						'condition' => [
							'rey_animation_type!' => [''],
						],
						'render_type' => 'none',
						'prefix_class' => 'rey-anim--',
					]
				);

				$element->add_control(
					'rey_animation_subject',
					[
						'label' => __( 'Animation Subject', 'rey-core' ),
						'description' => esc_html__('Select the animation subject, either this column itself, or the widgets inside, sequentially.' , 'rey-core'),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => 'column',
						'options' => [
							'column'  => __( 'Column itself', 'rey-core' ),
							'widgets'  => __( 'Widgets in this column', 'rey-core' ),
						],
						'condition' => [
							'rey_animation_type!' => [''],
						],
						'prefix_class' => 'rey-anim--subject-',
						'render_type' => 'none',
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
					'rey_animation_type__reveal_zoom',
					[
						'label' => __( 'Reveal Zoom Animation', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'label_on' => __( 'Yes', 'rey-core' ),
						'label_off' => __( 'No', 'rey-core' ),
						'return_value' => 'yes',
						'default' => 'yes',
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
						'condition' => [
							'rey_animation_type' => ['reveal'],
						],
						'render_type' => 'none',
					]
				);

			endif;

			$element->add_control(
				'rey_sticky',
				[
					'label' => __( 'Sticky', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default' => '',
					'render_type' => 'none',
					'separator' => 'before',
				]
			);

			$element->add_control(
				'rey_sticky_offset',
				[
					'label' => __( 'Sticky Offset', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'condition' => [
						'rey_sticky' => ['yes'],
					],
				]
			);
		}

		/**
		 * Tweak the CSS classes field.
		 */
		function section_advanced( $stack )
		{
			// get args
			$css_classes = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $stack->get_unique_name(), 'css_classes' );
			$css_classes['label_block'] = true;
			$stack->update_control( 'css_classes', $css_classes );

			$stack->add_control(
				'rey_utility_classes',
				[
					'label' => esc_html__( 'Utility Classes', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- None -', 'rey-core' ),
						'column-flex-dir--vertical'  => esc_html__( 'Flex direction - Column', 'rey-core' ),
						'column-stretch-right'  => esc_html__( 'Stretch column to right window edge', 'rey-core' ),
						'column-stretch-left'  => esc_html__( 'Stretch column to left window edge', 'rey-core' ),
						'u-topDeco-splitLine'  => esc_html__( 'Border decoration', 'rey-core' ),

					],
					'prefix_class' => ''
				]
			);
		}


		/**
		 * Render some attributes before rendering
		 *
		 * @since 1.0.0
		 **/
		function before_render( $element )
		{
			$settings = $element->get_settings_for_display();
			$el_id = $element->get_id();

			$element->add_render_attribute( '_inner_wrapper', 'class', ['elementor-column-wrap--' . $el_id ] );

			if( ReyCoreElementor::animations_enabled() && $settings['rey_animation_type'] != '' ):

				$config = [
					'id'                 => $el_id,
					'element_type'       => 'column',
					'animation_type'     => esc_attr( $settings['rey_animation_type'] ),
					'reveal_direction'   => esc_attr( $settings['rey_animation_type_reveal_direction']),
					'reveal_zoom'        => esc_attr( $settings['rey_animation_type__reveal_zoom'] ),
					'reveal_bg'          => esc_attr( $settings['rey_animation_type__reveal_bg_color']),
					'activation_trigger' => esc_attr( $settings['rey_animation_activation_trigger']),
					'subject'            => esc_attr( $settings['rey_animation_subject'] ),
				];

				if( $settings['rey_animation_delay'] ) {
					$config['delay']= esc_attr( $settings['rey_animation_delay'] );
				}

				if( $settings['rey_animation_duration'] ) {
					$config['duration']= esc_attr( $settings['rey_animation_duration'] );
				}

				$element->add_render_attribute( '_wrapper', 'data-rey-anim-config', wp_json_encode($config) );

				if( $settings['rey_animation_subject'] == 'column' ) {
					$element->add_render_attribute( '_inner_wrapper', 'class', ['rey-animator-inner', 'rey-animator-inner--' . $config['id'] ] );
				}

			endif;

			if( 'yes' === $settings['rey_sticky'] ):
				$element->add_render_attribute( '_wrapper', 'class', '--sticky-col' );
				if( $settings['rey_sticky_offset'] ){
					$element->add_render_attribute( '_wrapper', 'data-top-offset', $settings['rey_sticky_offset'] );
				}
			endif;

			// Video
			if( ReyCoreElementor::get_compatibilities('column_video') && 'video' === $settings['background_background'] && $video_link = $settings['background_video_link'] ):

				// Catch output
				ob_start();

			endif;

		}


		/**
		 * Inject Video HTML Markup
		 *
		 * @since 1.0.0
		 **/
		function after_render( $element )
		{
			$settings = $element->get_settings_for_display();

			if ( ReyCoreElementor::get_compatibilities('column_video') &&
				'video' === $settings['background_background'] && $video_link = $settings['background_video_link'] ) :

				$video_properties = \Elementor\Embed::get_video_properties( $video_link );

				$video_html = '';

				if( isset($video_properties['provider']) && 'youtube' === $video_properties['provider'] ){
					$video_html = reycore__get_youtube_iframe_html([
						'class' => 'rey-background-video-container',
						'video_id' => $video_properties['video_id'],
						'html_id' => 'yt' . $element->get_id(),
						'add_preview_image' => false,
						'mobile' => isset($settings['background_play_on_mobile']) && $settings['background_play_on_mobile'] === 'yes',
						'params' => [
							'start' => $settings['background_video_start'],
							'end' => $settings['background_video_end'],
							'loop' => $settings['background_play_once'] === '' ? 1 : 0,
						],
					]);
				}
				else {
					$video_html = reycore__get_video_html([
						'class' => 'rey-background-video-container',
						'video_url' => $video_link,
						'start' => $settings['background_video_start'],
						'end' => $settings['background_video_end'],
						'mobile' => isset($settings['background_play_on_mobile']) && $settings['background_play_on_mobile'] === 'yes',
						'params' => [
							'loop' => $settings['background_play_once'] === '' ? 'loop' : '',
						],
					]);
				}

				// Collect output
				$content = ob_get_clean();

				$query = '//div[contains(@class,"elementor-column-wrap--'. $element->get_id() .'")]';

				if( $new_html = ReyCoreElementor::el_inject_html( $content, $video_html,  $query) ){
					$content = $new_html;
				}

				echo $content;

			endif;

		}


		/**
		 * Filter Columns Print Content
		 *
		 * @since 1.0.0
		 **/
		function print_template( $template )
		{
			if( ReyCoreElementor::get_compatibilities('column_video') ){

				$old_template = '<div class="elementor-background-overlay"></div>';

				$new_template = '
				<# if ( settings.background_video_link ) {
					var model = view.getEditModel();
					var play_once_yt = settings.background_play_once === "" ? 1 : 0;
					var play_once_hosted = settings.background_play_once === "" ? "loop" : ""; #>';

					$new_template .= reycore__get_youtube_iframe_html([
						'video_id' => '{{{ settings.background_video_link }}}',
						'class' => 'rey-background-video-container',
						'html_id' => 'yt{{{model.id}}}',
						'params' => [
							'start' => '{{{settings.background_video_start}}}',
							'end' => '{{{settings.background_video_end}}}',
							'loop' => '{{{play_once_yt}}}',
						],
					]);

					$new_template .= reycore__get_video_html([
						'video_url' => '{{{ settings.background_video_link }}}',
						'class' => 'rey-background-video-container',
						'params' => [
							'loop' => '{{{play_once_hosted}}}',
						],
						'start' => '{{{settings.background_video_start}}}',
						'end' => '{{{settings.background_video_end}}}',
					]);

				$new_template .= '
				<# } #>
				<div class="elementor-background-overlay"></div>';

				return str_replace( $old_template, $new_template, $template );
			}
			return $template;
		}

	}
endif;
