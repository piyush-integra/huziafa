<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists('ReyCore_Module__HeaderFixed') ):

	class ReyCore_Module__HeaderFixed
	{

		public function __construct()
		{
			add_action( 'init', [ $this, 'init' ]);
			add_filter( 'reycore/kirki_fields/field=header_fixed_disable_mobile', [ $this, 'customizer_move_group_end' ] );
			add_action( 'reycore/kirki_fields/after_field=header_fixed_disable_mobile', [ $this, 'customizer_add_options' ] );
		}

		function is_enabled(){
			return get_theme_mod('header_position', 'rel') === 'fixed';
		}

		function shrink_is_enabled(){
			return $this->is_enabled() && get_theme_mod('header_fixed_shrink', false) === true;
		}

		public function init(){

			if( !$this->is_enabled() ){
				return;
			}

			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
			add_filter( 'rey/header/header_classes', [$this, 'header_classes']);
			add_filter( 'reycore/cover/css_classes', [$this, 'cover_classes']);
			add_filter( 'rey/main_script_params', [ $this, 'script_params'] );
			add_action( 'elementor/element/section/section_advanced/after_section_end', [$this, 'elementor_section_fixed_header_settings'], 10);
			add_action( 'elementor/element/column/section_advanced/after_section_end', [$this, 'elementor_column_fixed_header_settings'], 10);
			add_action( 'elementor/element/reycore-header-logo/section_settings/before_section_end', [$this, 'elementor_header_logo_fixed_header_settings'], 10);
			add_action( 'elementor/element/reycore-header-logo/section_styles/after_section_end', [$this, 'elementor_header_logo_fixed_header_styles'], 10);
			add_action( 'elementor/frontend/section/before_render', [$this, 'elementor_element_before_render'], 10);
			add_action( 'elementor/frontend/column/before_render', [$this, 'elementor_element_before_render'], 10);
			add_action( 'elementor/frontend/widget/before_render', [$this, 'elementor_header_logo_before_render'], 10);
			add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'editor_styles'], 10 );


		}

		public function header_classes($classes){

			if( $this->shrink_is_enabled() ){
				$classes['fixed-shrink'] = '--fixed-shrinking';
			}

			return $classes;
		}


		public function cover_classes($classes){

			if( $this->shrink_is_enabled() ){
				$classes['fixed-shrink'] = '--fixed-shrinking';
			}

			return $classes;
		}

		public function script_params($params)
		{
			$params['fixed_header_activation_point'] = 0;
			return $params;
		}

		function elementor_section_fixed_header_settings( $element ){

			if( ! $this->is_enabled() ){
				return;
			}

			$element->start_controls_section(
				'section_rey_fixed_header',
				[
					'label' => __( 'Fixed/Sticky Section Settings', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'tab' => \Elementor\Controls_Manager::TAB_ADVANCED,
					'hide_in_inner' => true,
				]
			);

			$element->add_control(
				'rey_hide_on_scroll',
				[
					'label' => esc_html__( 'Hide on scroll', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$element->add_control(
				'rey_disable_sticky_transitions',
				[
					'label' => esc_html__( 'Disable transitions', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'prefix_class' => '--disable-transitions-',
				]
			);

			if( $this->shrink_is_enabled() ){

				$element->add_control(
					'rey_scrolled_title',
					[
					   'label' => esc_html__( 'ANIMATED HEADER ON SCROLL OPTIONS', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
						'condition' => [
							'rey_hide_on_scroll' => '',
						],
					]
				);

				$element->add_control(
					'rey_scrolled_force_height',
					[
						'label' => __( 'Force <strong>Exact Height</strong>', 'rey-core' ),
						'description' => __( 'Instead of min-height, this option will force a height.', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'default' => '',
						'condition' => [
							'rey_hide_on_scroll' => '',
						],
						'prefix_class' => '--forced-height-',
					]
				);

				$element->add_responsive_control(
					'rey_scrolled_height__true',
					[
						'label' => __( 'Height (px)', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SLIDER,
						'range' => [
							'px' => [
								'max' => 400,
								'min' => 0,
								'step' => 1,
							],
						],
						'selectors' => [
							'.--shrank {{WRAPPER}} > .elementor-container' => 'height: {{SIZE}}px; min-height: auto; padding-top:0px; padding-bottom:0px;',
							'.--shrank {{WRAPPER}} .rey-siteLogo .custom-logo' => 'max-height: calc({{SIZE}}px - var(--shrank-forced-height, 1rem)); max-width: none; width: auto;',
						],
						'condition' => [
							'rey_hide_on_scroll' => '',
							'rey_scrolled_force_height' => 'yes',
						],
					]
				);

				$element->add_responsive_control(
					'rey_scrolled_height',
					[
						'label' => __( 'Min-Height (px)', 'rey-core' ),
						'description' => __( 'Adjust if this section height is set to Minimum height, otherwise it won\'t transition.', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SLIDER,
						'range' => [
							'px' => [
								'max' => 400,
								'min' => 0,
								'step' => 1,
							],
						],
						'selectors' => [
							'.--shrank {{WRAPPER}} > .elementor-container' => 'min-height: {{SIZE}}px;',
						],
						'condition' => [
							'rey_hide_on_scroll' => '',
							'rey_scrolled_force_height' => '',
						],
					]
				);

				$element->add_responsive_control(
					'rey_scrolled_pt',
					[
						'label' => __( 'Top Padding (px)', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SLIDER,
						'range' => [
							'px' => [
								'max' => 100,
								'min' => 0,
								'step' => 1,
							],
						],
						'selectors' => [
							'.--shrank {{WRAPPER}} > .elementor-container' => 'padding-top: {{SIZE}}px;',
						],
						'condition' => [
							'rey_hide_on_scroll' => '',
							'rey_scrolled_force_height' => '',
						],
					]
				);

				$element->add_responsive_control(
					'rey_scrolled_pb',
					[
						'label' => __( 'Bottom Padding (px)', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SLIDER,
						'range' => [
							'px' => [
								'max' => 100,
								'min' => 0,
								'step' => 1,
							],
						],
						'selectors' => [
							'.--shrank {{WRAPPER}} > .elementor-container' => 'padding-bottom: {{SIZE}}px;',
						],
						'condition' => [
							'rey_hide_on_scroll' => '',
							'rey_scrolled_force_height' => '',
						],
					]
				);


				$element->add_control(
					'rey_scrolled_bg',
					[
						'label' => esc_html__( 'Background-color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'.--shrank {{WRAPPER}}' => 'background-color: {{VALUE}}',
						],
						'condition' => [
							'rey_hide_on_scroll' => '',
						],
						'separator' => 'before'
					]
				);

				$element->add_control(
					'rey_scrolled_text_color',
					[
						'label' => esc_html__( 'Text Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'.--shrank {{WRAPPER}}' => 'color: {{VALUE}}',
						],
						'condition' => [
							'rey_hide_on_scroll' => '',
						],
					]
				);

				$element->add_control(
					'rey_scrolled_link_color',
					[
						'label' => esc_html__( 'Link Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'.--shrank {{WRAPPER}} a' => 'color: {{VALUE}}',
						],
						'condition' => [
							'rey_hide_on_scroll' => '',
						],
					]
				);

				$element->add_control(
					'rey_scrolled_link_hover_color',
					[
						'label' => esc_html__( 'Link Hover Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'.--shrank {{WRAPPER}} a:hover' => 'color: {{VALUE}}',
						],
						'condition' => [
							'rey_hide_on_scroll' => '',
						],
					]
				);

				$element->add_control(
					'rey_scrolled_disable_transitions',
					[
						'label' => esc_html__( 'Disable transitions', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'default' => '',
						'condition' => [
							'rey_hide_on_scroll' => '',
						],
					]
				);


			}

			$element->end_controls_section();

		}


		function elementor_column_fixed_header_settings( $element ){

			if( ! $this->is_enabled() ){
				return;
			}

			$element->start_controls_section(
				'section_rey_fixed_header',
				[
					'label' => __( 'Rey - Fixed Header Settings', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_ADVANCED,
					'hide_in_inner' => true,
				]
			);

			$element->add_control(
				'rey_hide_on_scroll',
				[
					'label' => esc_html__( 'Hide on scroll', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$element->add_control(
				'rey_hide_on_scroll_mobile',
				[
					'label' => esc_html__( 'Hide on scroll (Mobile)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( 'Inherit', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

			$element->end_controls_section();

		}

		function elementor_header_logo_fixed_header_settings( $element ){

			if( ! $this->is_enabled() ){
				return;
			}

			$element->add_control(
				'logo_fixed_heading',
				[
				   'label' => __( 'LOGO SETTINGS ON FIXED VIEW', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before'
				]
			);

			$element->add_control(
				'logo_fixed',
				[
				   'label' => __( 'Logo (FIXED/STICKY)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'default' => [],
				]
			);

			$element->add_control(
				'logo_mobile_fixed',
				[
				   'label' => __( 'Mobile Logo (FIXED/STICKY)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'default' => [],
				]
			);

		}

		/**
		 * Logo styles
		 */
		function elementor_header_logo_fixed_header_styles( $element ){

			if( ! $this->is_enabled() ){
				return;
			}

			$element->start_injection( [
				'of' => 'width_mobile',
			] );

			$element->add_responsive_control(
				'logo_fixed_width',
				[
					'label' => __( 'Width (FIXED/STICKY)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'default' => [
						'unit' => '%',
					],
					'tablet_default' => [
						'unit' => '%',
					],
					'mobile_default' => [
						'unit' => '%',
					],
					'size_units' => [ '%', 'px', 'vw' ],
					'range' => [
						'%' => [
							'min' => 1,
							'max' => 100,
						],
						'px' => [
							'min' => 1,
							'max' => 1000,
						],
						'vw' => [
							'min' => 1,
							'max' => 100,
						],
					],
					'selectors' => [
						'.rey-siteHeader.--scrolled {{WRAPPER}} .rey-siteLogo img' => 'width: {{SIZE}}{{UNIT}};',
					],
					'separator' => 'after'
				]
			);

			$element->end_injection();

			$element->start_injection( [
				'of' => 'space_mobile',
			] );

			$element->add_responsive_control(
				'logo_fixed_space',
				[
					'label' => __( 'Max Width (FIXED/STICKY)', 'rey-core' ) . ' (%)',
					'type' => \Elementor\Controls_Manager::SLIDER,
					'default' => [
						'unit' => '%',
					],
					'tablet_default' => [
						'unit' => '%',
					],
					'mobile_default' => [
						'unit' => '%',
					],
					'size_units' => [ '%' ],
					'range' => [
						'%' => [
							'min' => 1,
							'max' => 100,
						],
					],
					'selectors' => [
						'.rey-siteHeader.--scrolled {{WRAPPER}} .rey-siteLogo img' => 'max-width: {{SIZE}}{{UNIT}};',
					],
					'separator' => 'after'
				]
			);

			$element->end_injection();

			$element->start_injection( [
				'of' => 'max_height_mobile',
			] );

			$element->add_responsive_control(
				'logo_fixed_max_height',
				[
					'label' => __( 'Max Height (FIXED/STICKY)', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 1,
							'max' => 300,
						],
					],
					'selectors' => [
						'.rey-siteHeader.--scrolled {{WRAPPER}} .rey-siteLogo img' => 'max-height: {{SIZE}}{{UNIT}};',
					],
					'separator' => 'after'
				]
			);

			$element->end_injection();

		}


		function elementor_element_before_render( $element )
		{
			if( ! $this->is_enabled() ){
				return;
			}

			$settings = $element->get_settings_for_display();

			if( isset($settings['rey_hide_on_scroll']) && $settings['rey_hide_on_scroll'] === 'yes' ){
				$element->add_render_attribute( '_wrapper', 'class', 'hide-on-scroll' );
			}

			if( isset($settings['rey_hide_on_scroll_mobile']) && $settings['rey_hide_on_scroll_mobile'] !== '' ){
				$element->add_render_attribute( '_wrapper', 'data-hide-on-scroll-mobile', esc_attr($settings['rey_hide_on_scroll_mobile']) );
			}

			if( isset($settings['rey_scrolled_disable_transitions']) && $settings['rey_scrolled_disable_transitions'] === 'yes' ){
				$element->add_render_attribute( '_wrapper', 'class', '--notransitions' );
			}
		}

		function elementor_header_logo_before_render( $element )
		{
			if( ! $this->is_enabled() ){
				return;
			}

			if( $element->get_unique_name() !== 'reycore-header-logo' ){
				return;
			}

			$settings = $element->get_settings_for_display();

			$attrs = [];

			if( isset($settings['logo_fixed']) && isset($settings['logo_fixed']['url']) && $logo_url = $settings['logo_fixed']['url'] ){
				$attrs['data-sticky-logo'] = esc_attr($logo_url);
			}

			if( isset($settings['logo_mobile_fixed']) && isset($settings['logo_mobile_fixed']['url']) && $mobile_logo_url = $settings['logo_mobile_fixed']['url'] ){
				$attrs['data-sticky-mobile-logo'] = esc_attr($mobile_logo_url);
			}

			if( !empty($attrs) ){
				$element->add_render_attribute( '_wrapper', $attrs );
			}

		}

		public function enqueue_scripts(){

			if( !$this->is_enabled() ){
				return;
			}

            wp_enqueue_style( 'reycore-fixedheader', REY_CORE_MODULE_URI . basename(__DIR__) . '/style.css', [], REY_CORE_VERSION );
            wp_enqueue_script( 'reycore-fixedheader', REY_CORE_MODULE_URI . basename(__DIR__) . '/script.js', ['reycore-scripts'], REY_CORE_VERSION , true);
		}

		public function editor_styles(){

			$custom_css = '.elementor-control-section_rey_fixed_header{ display: none; }';
			$custom_css .= 'html[data-post-type="rey-global-sections"][data-gs="header"] .elementor-control-section_rey_fixed_header{ display: block; }';

			wp_add_inline_style( 'rey-core-elementor-editor-css', $custom_css);

		}

		public function customizer_move_group_end( $args ){

			if( isset($args['rey_group_end']) ){
				unset($args['rey_group_end']);
			}

			return $args;
		}

		public function customizer_add_options(){

			ReyCoreKirki::add_field( 'rey_core_kirki', [
				'type'        => 'toggle',
				'settings'    => 'header_fixed_shrink',
				'label'       => esc_html__( 'Animate Header on Scroll', 'rey-core' ),
				'description' => __( 'If enabled, the header will animate (shrink) on page scroll. Read more here on <a href="https://support.reytheme.com/kb/create-customize-a-shrinking-sticky-header/" target="_blank">how you can customize</a>. If you want more flexibility, such as a totally different header when sticky, consider using <a href="https://support.reytheme.com/kb/create-a-custom-sticky-header/" target="_blank">Sticky Top Content - Global sections</a>.', 'rey-core' ),
				'section'     => 'header_general_options',
				'default'     => false,
				'active_callback' => [
					[
						'setting'  => 'header_layout_type',
						'operator' => '!=',
						'value'    => 'none',
					],
					[
						'setting'  => 'header_position',
						'operator' => '==',
						'value'    => 'fixed',
					],
				],
				'rey_group_end' => true
			] );

		}

	}

	new ReyCore_Module__HeaderFixed();
endif;
