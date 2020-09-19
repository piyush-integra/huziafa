<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if(!class_exists('ReyCore_Widget_Header_Logo')):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Header_Logo extends \Elementor\Widget_Base {

	public function get_name() {
		return 'reycore-header-logo';
	}

	public function get_title() {
		return __( 'Logo - Header', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-logo';
	}

	public function get_categories() {
		return [ 'rey-header' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements-header/#logo';
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

		$cst_link = add_query_arg([
			'autofocus[section]' => 'header_logo_options'
			], admin_url( 'customize.php' )
		);

		$this->add_control(
			'edit_link',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => sprintf( __( 'Logo options can be changed into <a href="%s" target="_blank">Customizer panel > Header > Logo</a>. If you\'re planning on using a custom logo image, consider using an Image Widget.', 'rey-core' ), $cst_link ),
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

		$this->add_control(
			'blog_name',
			[
				'label' => __( 'Site Name', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => get_bloginfo( 'name' ),
				'description' => __( 'Disabled if image is used.', 'rey-core' ),
				'condition' => [
					'custom!' => [''],
				],
			]
		);

		$this->add_control(
			'blog_description',
			[
				'label' => __( 'Site Description', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => get_bloginfo( 'description', 'display' ),
				'description' => __( 'Disabled if image is used.', 'rey-core' ),
				'condition' => [
					'custom!' => [''],
				],
			]
		);

		$this->add_control(
			'logo',
			[
			   'label' => __( 'Choose Logo', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [],
				'condition' => [
					'custom!' => [''],
				],
			]
		);

		$this->add_control(
			'logo_mobile',
			[
			   'label' => __( 'Choose Logo for Mobile view', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [],
				'condition' => [
					'custom!' => [''],
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Logo styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

		$this->add_responsive_control(
			'width',
			[
				'label' => __( 'Width', 'rey-core' ),
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
					'{{WRAPPER}} .rey-siteLogo img' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'space',
			[
				'label' => __( 'Max Width', 'rey-core' ) . ' (%)',
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
					'{{WRAPPER}} .rey-siteLogo img' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'max_height',
			[
				'label' => __( 'Max Height', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 300,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-siteLogo img' => 'max-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();


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

		// if it's sticky, inherit the initial logo settings
		if( get_query_var('rey__is_sticky') === true ){
			set_query_var('rey__header_logo', [
				'logo' => get_theme_mod('custom_logo', ''),
				'logo_mobile' => get_theme_mod('logo_mobile', ''),
			]);
		}

		if( $settings['custom'] ){

			$vars = [
				'blog_name' => $settings['blog_name'],
				'blog_description' => $settings['blog_description'],
			];

			if( isset($settings['logo']['id']) ){
				$vars['logo'] = $settings['logo']['id'];
			}

			if( isset($settings['logo_mobile']['id']) ){
				$vars['logo_mobile'] = $settings['logo_mobile']['id'];
			}

			set_query_var('rey__header_logo', $vars);
		}

		reycore__get_template_part('template-parts/header/logo');

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
