<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if( class_exists('WooCommerce') && !class_exists('ReyCore_Widget_Header_Cart')  ):
/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Header_Cart extends \Elementor\Widget_Base {

	public function get_name() {
		return 'reycore-header-cart';
	}

	public function get_title() {
		return __( 'Cart - Header', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-cart';
	}

	public function get_categories() {
		return [ 'rey-header' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements-header/#cart';
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

		$this->add_control(
			'edit_link',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => sprintf(
					__( 'Cart options can be edited into the <a href="%s" target="_blank">Customizer Panel > Header > Cart</a>, but you can also override those settings below.', 'rey-core' ),
					add_query_arg( ['autofocus[section]' => 'header_cart_options'], admin_url( 'customize.php' ) ) ),
				'content_classes' => 'rey-raw-html',
			]
		);

		$this->add_control(
			'hide_empty',
			[
				'label' => __( 'Hide Cart if empty', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''    => __( 'Inherit option', 'rey-core' ),
					'yes' => __( 'Yes', 'rey-core' ),
					'no'  => __( 'No', 'rey-core' ),
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label' => esc_html__( 'Cart Icon Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-headerCart' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'hide_counter',
			[
				'label' => __( 'Hide Counter', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'none',
				'default' => 'inline-block',
				'selectors' => [
					'{{WRAPPER}} .rey-headerCart .rey-headerCart-nb' => 'display: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'counter_bg_color',
			[
				'label' => __( 'Counter Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-headerCart .rey-headerCart-nb' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'hide_counter!' => 'none',
				],
			]
		);

		$this->add_control(
			'counter_text_color',
			[
				'label' => __( 'Counter Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-headerCart .rey-headerCart-nb' => 'color: {{VALUE}}',
				],
				'condition' => [
					'hide_counter!' => 'none',
				],
			]
		);

		$this->add_responsive_control(
			'panel_width',
			[
			   'label' => esc_html__( 'Panel Width (Mobile)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vw' ],
				'range' => [
					'px' => [
						'min' => 200,
						'max' => 1000,
						'step' => 1,
					],
				],
				'selectors' => [
					'.rey-cartPanel-wrapper.rey-sidePanel' => 'width: {{SIZE}}{{UNIT}};',
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

		// $settings = $this->get_settings_for_display();

		$hide_empty = $this->get_settings_for_display('hide_empty');

		$vars['hide_empty'] = $hide_empty === '' ? get_theme_mod('header_cart_hide_empty', 'no') : $hide_empty;

		set_query_var('rey__header_cart', $vars);

		if( $vars['hide_empty'] === 'yes' ){
			$this->add_render_attribute( '_wrapper', 'class', '--hide-empty' );

			$cart_count = is_object( WC()->cart ) ? WC()->cart->get_cart_contents_count() : '';
			$this->add_render_attribute( '_wrapper', 'data-rey-cart-count', absint($cart_count) );
		}

		reycore__get_template_part('template-parts/woocommerce/header-shopping-cart');
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
