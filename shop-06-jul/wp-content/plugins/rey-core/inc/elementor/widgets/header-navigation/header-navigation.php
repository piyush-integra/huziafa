<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if(!class_exists('ReyCore_Widget_Header_Navigation')):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Header_Navigation extends \Elementor\Widget_Base {

	public $old_logo_vars = [];

	public function __construct( $data = [], $args = null ) {

		if ( $data && isset($data['settings']) && $settings = $data['settings'] ) {
			if( isset($settings['mobile_panel_disable_footer']) && 'yes' === $settings['mobile_panel_disable_footer'] ){
				remove_action('rey/mobile_nav/footer', 'reycore_wc__add_mobile_nav_link', 5);
			}
		}

		parent::__construct( $data, $args );
	}

	// add_action( 'elementor/element/before_parse_css', [$this, 'add_widget_css_to_post_file'], 10, 2 );
	// public function add_widget_css_to_post_file( $post_css, $element ){
	// 	if ( $post_css instanceof Elementor\Core\Files\CSS\Post ) {
	// 		if( ($el_id = $this->get_id()) && $element->get_id() === $el_id ){
	// 			$breakpoint = $this->get_settings_for_display('breakpoint');
	// 			$css = $this->get_breakpoint_css( $breakpoint, $el_id );
	// 			$post_css->get_stylesheet()->add_raw_css( $css );

	// 		}
	// 	}
	// }

	public function get_name() {
		return 'reycore-header-navigation';
	}

	public function get_title() {
		return __( 'Navigation - Header', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-menu-toggle';
	}

	public function get_categories() {
		return [ 'rey-header' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements-header/#navigation';
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
			'section_settings',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

		$this->add_control(
			'notice',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => esc_html__( 'If you don\'t want to show this element, simply remove it from its section.', 'rey-core' ),
				'content_classes' => 'rey-raw-html',
			]
		);

		$cst_link_query['autofocus[section]'] = 'header_nav_options';
		$cst_link = add_query_arg( $cst_link_query, admin_url( 'customize.php' ) );

		$this->add_control(
			'edit_link',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => sprintf( __( 'Navigation options can be edited into the <a href="%s" target="_blank">Customizer Panel > Header > Navigation</a>, but you can also override those settings below.', 'rey-core' ), $cst_link ),
				'content_classes' => 'rey-raw-html',
				'condition' => [
					'custom' => [''],
				],
			]
		);

		$this->add_control(
			'custom',
			[
				'label' => __( 'Override settings', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$get_all_menus = reycore__get_all_menus();

		$this->add_control(
			'menu_source',
			[
				'label' => __( 'Menu Source', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => ['' => esc_html__('- Disabled -', 'rey-core')] + $get_all_menus,
				'default' => 'main-menu',
				'condition' => [
					'custom!' => [''],
				],
			]
		);

		$this->add_control(
			'mobile_menu_source',
			[
				'label' => __( 'Mobile Menu Source', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => ['' => esc_html__('- Disabled -', 'rey-core')] + $get_all_menus,
				'default' => 'main-menu',
				'condition' => [
					'custom!' => [''],
				],
			]
		);

		$this->add_control(
			'breakpoint',
			[
				'label'       => __( 'Breakpoint for mobile navigation', 'rey-core' ),
				'description' => __( 'This will control at which window size to switch the menu into mobile navigation.', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'       => 768,
				// 'max'       => 2560,
				'step'      => 1,
				'default'     => 1024,
				'condition' => [
					'custom!' => [''],
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( '1st Level Menu Items', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

		$this->add_control(
			'style',
			[
				'label' => __( 'Menu style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => __( 'Default', 'rey-core' ),
					'simple'  => __( 'Simple', 'rey-core' ),
					'ulr'  => __( 'Left to Right Underline', 'rey-core' ),
					'ulr --thinner'  => __( 'Left to Right Underline (thinner)', 'rey-core' ),
					'ut'  => __( 'Left to Right Thick Underline', 'rey-core' ),
					'ut2'  => __( 'Left to Right Background', 'rey-core' ),
					'ub'  => __( 'Bottom Underline', 'rey-core' ),
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'      => 'typography',
				'selector'  => '{{WRAPPER}} .rey-mainMenu--desktop > .menu-item > a',
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
					'text_color',
					[
						'label' => __( 'Text Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-mainMenu--desktop > .menu-item > a' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'bg_color',
					[
						'label' => __( 'Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-mainMenu--desktop > .menu-item > a' => 'background-color: {{VALUE}}',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' => 'border',
						'selector' => '{{WRAPPER}} .rey-mainMenu--desktop > .menu-item > a',
						// 'separator' => 'before',
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
					'hover_text_color',
					[
						'label' => __( 'Hover Text Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-mainMenu--desktop > .menu-item:hover > a, {{WRAPPER}} .rey-mainMenu--desktop > .menu-item > a:hover, {{WRAPPER}} .rey-mainMenu--desktop > .menu-item.current-menu-item > a' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'hover_bg_color',
					[
						'label' => __( 'Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-mainMenu--desktop > .menu-item:hover > a, {{WRAPPER}} .rey-mainMenu--desktop > .menu-item > a:hover, {{WRAPPER}} .rey-mainMenu--desktop > .menu-item.current-menu-item > a' => 'background-color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'hover_border_color',
					[
						'label' => __( 'Border Color', 'elementor' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'condition' => [
							'border_border!' => '',
						],
						'selectors' => [
							'{{WRAPPER}} .rey-mainMenu--desktop > .menu-item:hover > a, {{WRAPPER}} .rey-mainMenu--desktop > .menu-item > a:hover, {{WRAPPER}} .rey-mainMenu--desktop > .menu-item.current-menu-item > a' => 'border-color: {{VALUE}};',
							'{{WRAPPER}} .rey-mainMenu--desktop > .menu-item:focus > a, {{WRAPPER}} .rey-mainMenu--desktop > .menu-item > a:focus' => 'border-color: {{VALUE}};',
						],
					]
				);

			$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'deco_color',
			[
				'label' => esc_html__( 'Menu deco. color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-mainMenu--desktop > .menu-item > a:after' => 'color: {{VALUE}}',
				],
				'condition' => [
					'style!' => 'none',
				],
			]
		);

		$this->add_control(
			'horizontal_spacing',
			[
			   'label' => esc_html__( 'Horizontal Item Spacing', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'rem' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 180,
						'step' => 1,
					],
					'rem' => [
						'min' => 0,
						'max' => 5.0,
					],
				],
				'default' => [
					'unit' => 'rem',
					'size' => 1,
				],
				'selectors' => [
					'{{WRAPPER}}' => '--header-nav-x-spacing: {{SIZE}}{{UNIT}};',
				],
				'separator' => 'before'
			]
		);

		$this->add_responsive_control(
			'horizontal_alignment',
			[
				'label' => esc_html__( 'Horizontal Alignment', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => __( 'Default', 'rey-core' ),
					'flex-start' => __( 'Start', 'rey-core' ),
					'center' => __( 'Center', 'rey-core' ),
					'flex-end' => __( 'End', 'rey-core' ),
					'space-between' => __( 'Space Between', 'rey-core' ),
					'space-around' => __( 'Space Around', 'rey-core' ),
					'space-evenly' => __( 'Space Evenly', 'rey-core' ),
				],
				'selectors' => [
					'{{WRAPPER}} .rey-mainMenu--desktop' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'items_padding',
			[
				'label' => __( 'Padding', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .rey-mainMenu--desktop > .menu-item > a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'border_radius',
			[
				'label' => __( 'Border Radius', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .rey-mainMenu--desktop > .menu-item > a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_styles_submenus',
			[
				'label' => __( 'Sub-Menu Items', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'      => 'typography_sub',
				'selector'  => '{{WRAPPER}} .rey-mainMenu--desktop .--is-regular .sub-menu .menu-item > a',
			]
		);

		$this->add_control(
			'submenu_width',
			[
			   'label' => esc_html__( 'Sub-menu Width', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 120,
						'max' => 400,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-mainMenu--desktop .--is-regular .sub-menu > .menu-item > a' => 'min-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'submenu_bg_color',
			[
				'label' => esc_html__( 'Sub-menu Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-mainMenu--desktop .--is-regular' => '--body-bg-color: {{VALUE}}',
					// '{{WRAPPER}} .rey-mainMenu--desktop .--is-mega .rey-mega-gs:before' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_styles_submenus');

			$this->start_controls_tab(
				'tab_default_submenus',
				[
					'label' => __( 'Default', 'rey-core' ),
				]
			);

				$this->add_control(
					'text_color_submenus',
					[
						'label' => __( 'Text Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-mainMenu--desktop .--is-regular .sub-menu .menu-item > a' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'bg_color_submenus',
					[
						'label' => __( 'Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-mainMenu--desktop .--is-regular .sub-menu .menu-item > a' => 'background-color: {{VALUE}}',
						],
					]
				);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tab_hover_submenus',
				[
					'label' => __( 'Hover', 'rey-core' ),
				]
			);

				$this->add_control(
					'hover_text_color_submenus',
					[
						'label' => __( 'Hover Text Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-mainMenu--desktop .--is-regular .sub-menu .menu-item:hover > a, {{WRAPPER}} .rey-mainMenu--desktop .--is-regular .sub-menu .menu-item > a:hover, {{WRAPPER}} .rey-mainMenu--desktop .--is-regular .sub-menu .menu-item.current-menu-item > a' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'hover_bg_color_submenus',
					[
						'label' => __( 'Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-mainMenu--desktop .--is-regular .sub-menu .menu-item:hover > a, {{WRAPPER}} .rey-mainMenu--desktop .--is-regular .sub-menu .menu-item > a:hover, {{WRAPPER}} .rey-mainMenu--desktop .--is-regular .sub-menu .menu-item.current-menu-item > a' => 'background-color: {{VALUE}}',
						],
					]
				);

			$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'nav_indicator',
			[
				'label' => esc_html__( 'Submenus Indicators', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'circle',
				'options' => [
					'none'  => esc_html__( 'None', 'rey-core' ),
					'circle'  => esc_html__( 'Circle', 'rey-core' ),
					'arrow'  => esc_html__( 'Arrow', 'rey-core' ),
					'arrow2'  => esc_html__( 'Arrow v2', 'rey-core' ),
					'dash'  => esc_html__( 'Dash', 'rey-core' ),
				],
				'separator' => 'before'
			]
		);

		$this->add_control(
			'nav_indicator_color',
			[
				'label' => esc_html__( 'Indicator Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-mainMenu .menu-item-has-children .--submenu-indicator' => 'color: {{VALUE}}',
				],
				'condition' => [
					'nav_indicator!' => 'none',
				],
			]
		);

		$this->add_control(
			'nav_indicator_size',
			[
				'label' => esc_html__( 'Indicator Size', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 40,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-mainMenu .menu-item-has-children .--submenu-indicator' => 'font-size: {{VALUE}}',
				],
				'condition' => [
					'nav_indicator!' => 'none',
				],
			]
		);

		$this->add_control(
			'submenu_top_indicator',
			[
				'label' => esc_html__( 'Submenu panels - Top Pointer', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'separator' => 'before'
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_styles_mobile',
			[
				'label' => __( 'Mobile Panel', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

		$this->add_control(
			'mobile_panel_width',
			[
			   'label' => esc_html__( 'Mobile panel width', 'rey-core' ) . ' (vw)',
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'vw' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-mainNavigation.rey-mainNavigation--mobile' => 'max-width: {{SIZE}}vw;',
				]
			]
		);

		$this->add_control(
			'mobile_panel_logo_max_width',
			[
			   'label' => esc_html__( 'Logo Max-Width', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 480,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-mobileNav-header .rey-siteLogo img' => 'max-width: {{SIZE}}px;',
				],
				'separator' => 'before'
			]
		);

		$this->add_control(
			'mobile_panel_logo_max_height',
			[
			   'label' => esc_html__( 'Logo Max-Height', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 480,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-mobileNav-header .rey-siteLogo img' => 'max-height: {{SIZE}}px;',
				],

			]
		);

		$this->add_control(
			'mobile_panel_logo_img',
			[
			   'label' => esc_html__( 'Custom Logo', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [],
				'separator' => 'after'
			]
		);

		$this->add_control(
			'text_color_mobile',
			[
				'label' => __( 'Text & Links Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-mainNavigation--mobile, {{WRAPPER}} .rey-mainNavigation--mobile a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'hover_text_color_mobile',
			[
				'label' => __( 'Links hover color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-mainNavigation--mobile .menu-item:hover > a, {{WRAPPER}} .rey-mainNavigation--mobile a:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'bg_color_mobile',
			[
				'label' => __( 'Panel Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-mainNavigation--mobile' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typography_mobile',
				'selector' => '{{WRAPPER}} .rey-mainNavigation--mobile .menu-item > a',
			]
		);

		$this->add_control(
			'mobile_panel_disable_footer',
			[
				'label' => esc_html__( 'Disable Footer Menu', 'rey-core' ),
				'description' => esc_html__( 'Disable the Login & Logout link items.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'separator' => 'before'
			]
		);

		$this->add_control(
			'mobile_panel_disable_mega_gs',
			[
				'label' => esc_html__( 'Disable Mega Menus', 'rey-core' ),
				'description' => esc_html__( 'Disable mega menu global sections in mobile panel.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'separator' => 'before',
				'prefix_class' => '',
				'return_value' => '--disable-mega-gs',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_styles_mobile_hamburger',
			[
				'label' => __( 'Hamburger Icon', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

		$this->add_control(
			'hamburger_style',
			[
				'label' => esc_html__( 'Icon Bars', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( 'Default - 3 bars', 'rey-core' ),
					'--2bars'  => esc_html__( '2 bars', 'rey-core' ),
					'--hover2bars'  => esc_html__( '2 bars + hover', 'rey-core' ),
				],
				'prefix_class' => '--hbg-style'
			]
		);

		$this->add_responsive_control(
			'hamburger_style_width',
			[
				'label' => esc_html__( 'Bars Width', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 100,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-mainNavigation-mobileBtn' => '--hbg-bars-width: {{VALUE}}px',
				],
			]
		);

		$this->add_responsive_control(
			'hamburger_style_bars_thick',
			[
				'label' => esc_html__( 'Bars Thickness', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 15,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-mainNavigation-mobileBtn' => '--hbg-bars-thick: {{VALUE}}px',
				],
			]
		);

		$this->add_responsive_control(
			'hamburger_style_bars_distance',
			[
				'label' => esc_html__( 'Bars Distance', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 15,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-mainNavigation-mobileBtn' => '--hbg-bars-distance: {{VALUE}}px',
				],
			]
		);

		$this->add_responsive_control(
			'hamburger_style_bars_round',
			[
				'label' => esc_html__( 'Bars Roundness', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 2,
				'min' => 0,
				'max' => 15,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-mainNavigation-mobileBtn' => '--hbg-bars-roundness: {{VALUE}}px',
				],
			]
		);

		$this->add_responsive_control(
			'hamburger_color',
			[
				'label' => esc_html__( 'Icon Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-mainNavigation-mobileBtn' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'hamburger_trigger_hover',
			[
				'label' => esc_html__( 'Trigger on hover?', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'prefix_class' => '--hbg-hover-'
			]
		);

		$this->add_control(
			'hamburger_trigger_hover_close',
			[
				'label' => esc_html__( 'Close on mouseleave?', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'prefix_class' => '--hbg-hover-close-',
				'condition' => [
					'hamburger_trigger_hover!' => '',
				],
			]
		);

		$this->add_control(
			'hamburger_text',
			[
				'label' => esc_html__( 'Custom Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'selectors' => [
					'{{WRAPPER}} .rey-mainNavigation-mobileBtn:after' => 'content: "{{VALUE}}"',
				],
				'separator' => 'before'
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'hamburger_text_styles',
				'selector' => '{{WRAPPER}} .rey-mainNavigation-mobileBtn:after',
			]
		);

		$this->add_control(
			'hamburger_text_mobile',
			[
				'label' => esc_html__( 'Hide text on mobiles/tablet', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'prefix_class' => '--hbg-text-mobile',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Generate breakpoint CSS
	 */
	public function get_breakpoint_css()
	{
		$css = '<style>';
		$css .= '.elementor-element-%1$s, .rey-mobileNav--%1$s{ --nav-breakpoint-desktop: none; --nav-breakpoint-mobile: block; }';
		$css .= '@media (min-width: %2$spx) { .elementor-element-%1$s, .rey-mobileNav--%1$s { --nav-breakpoint-desktop: block; --nav-breakpoint-mobile: none; } }';
		$css .= '</style>';
		printf( $css, $this->get_id(), $this->get_settings_for_display('breakpoint') );
	}

	public function set_custom_mobile_panel_logo(){

		if( ($logo = $this->get_settings_for_display('mobile_panel_logo_img')) && isset($logo['id']) && !empty($logo['id']) ) {

			$this->old_logo_vars = get_query_var('rey__header_logo');

			set_query_var('rey__header_logo', [
				'logo' => $logo['id'],
				'logo_mobile' => false,
			]);
		}
	}

	public function unset_custom_mobile_panel_logo(){

		if( !empty($this->old_logo_vars) ){
			set_query_var('rey__header_logo', $this->old_logo_vars);
		}

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

		$settings = $this->get_settings_for_display();

		$this->set_custom_mobile_panel_logo();

		$vars = [];

		$nav_styles[] = $settings['submenu_top_indicator'] !== '' ? '--submenu-top' : '';

		if( $settings['style'] !== '' ){
			$nav_styles[] = 'rey-navEl';
			$nav_styles[] = '--menuHover-' . $settings['style'];
		}

		$vars['nav_ul_style'] = implode(' ', $nav_styles);

		if( $settings['custom'] ){

			if( $settings['mobile_menu_source'] && $settings['breakpoint'] ){
				$this->get_breakpoint_css();
			}

			$vars['menu'] = $settings['menu_source'];
			$vars['mobile_menu'] = $settings['mobile_menu_source'];
			$vars['nav_id'] = '-' . $this->get_id();
		}

		$vars['nav_indicator'] = $settings['nav_indicator'];

		set_query_var('rey__header_nav', $vars);

		// change mobile panel logo
		add_action('rey/mobile_nav/header', [$this, 'set_custom_mobile_panel_logo']);

		reycore__get_template_part('template-parts/header/navigation');

		// reset mobile panel logo
		add_action('rey/mobile_nav/footer', [$this, 'unset_custom_mobile_panel_logo']);
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
