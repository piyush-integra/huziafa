<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if(!class_exists('ReyCore_Widget_Header_Caller')):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Header_Caller extends \Elementor\Widget_Base {

	public function get_name() {
		return 'reycore-header-caller';
	}

	public function get_title() {
		return __( 'Caller - Header', 'rey-core' );
	}

	public function get_icon() {
		return 'fa fa-volume-control-phone';
	}

	public function get_categories() {
		return [ 'rey-header' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements-header/#caller-block';
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
				'label' => __( 'Text', 'rey-core' ),
			]
		);

		$this->add_control(
			'text',
			[
				'label' => __( 'Text / Phone number', 'rey-core' ),
				'label_block' => true,
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( '+44 589 58 58 00', 'rey-core' ),
				'placeholder' => __( 'eg: +44 589 58 58 00', 'rey-core' ),
			]
		);

		$this->add_control(
			'text_link',
			[
				'label' => __( 'Link', 'rey-core' ),
				'label_block' => true,
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'eg: tel:+44589585800', 'rey-core' ),
			]
		);

		$this->add_control(
			'swap_icon',
			[
				'label' => __( 'Show icon on Mobiles', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
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

		$this->start_controls_section(
			'section_button_settings',
			[
				'label' => __( 'Button', 'rey-core' ),
			]
		);


		$this->add_control(
			'button_text',
			[
				'label' => __( 'Button Text', 'rey-core' ),
				'label_block' => true,
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'REQUEST A CALL', 'rey-core' ),
				'placeholder' => __( 'eg: REQUEST A CALL', 'rey-core' ),
			]
		);

		$this->add_control(
			'button_link',
			[
				'label' => __( 'Link', 'rey-core' ),
				'label_block' => true,
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				// 'placeholder' => __( '', 'rey-core' ),
			]
		);

		$this->add_control(
			'button_target',
			[
				'label' => __( 'Link Target', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '_self',
				'options' => [
					'_self'  => __( 'Same Window', 'rey-core' ),
					'_blank'  => __( 'New Window', 'rey-core' ),
					'modal'  => __( 'Content Modal / Popup', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'el_hide_mobile',
			[
				'label' => __( 'Hide on Mobiles', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Text Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'text_typo',
				'selector' => '{{WRAPPER}} .rey-caller-text span',
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => __( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-caller-text' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'text_hover_color',
			[
				'label' => __( 'Text Link Hover Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} a.rey-caller-text:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_button_styles',
			[
				'label' => __( 'Button Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'button_typo',
				'selector' => '{{WRAPPER}} .rey-caller-button',
			]
		);

		$this->add_control(
			'button_style',
			[
				'label' => __( 'Button Style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => __( 'Default', 'rey-core' ),
					// 'btn-primary'  => __( 'Primary Button', 'rey-core' ),
					// 'btn-secondary'  => __( 'Secondary Button', 'rey-core' ),
					'btn-line-active'  => __( 'Underlined', 'rey-core' ),
					'btn-line'  => __( 'Hover underlined', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => __( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-caller-button' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'button_text_hover_color',
			[
				'label' => __( 'Button Hover Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-caller-button:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();
	}

	public function render_text(){

		$settings = $this->get_settings_for_display();

		if( empty($settings['text']) ) {
			return;
		}

		$tag_start = $tag_end = 'span';

		if( $settings['text_link'] ) {
			$tag_start = 'a href="'. $settings['text_link'] .'"';
			$tag_end = 'a';
		}

		$icon = $settings['swap_icon'] == 'yes' ? reycore__get_svg_icon([
			'id' => 'rey-icon-telephone'
		]) : '';

		printf( '<%1$s class="rey-caller-text"><span>%3$s</span>%4$s</%2$s>', $tag_start, $tag_end, $settings['text'], $icon );
	}

	public function render_button(){

		$settings = $this->get_settings_for_display();

		if( empty($settings['button_text']) ) {
			return;
		}

		if( $settings['button_target'] == '_self' || $settings['button_target'] == '_blank' ) {
			$target = "target='{$settings['button_target']}'";
		}
		else {
			$target = "data-rey-inline-modal";
		}

		$style = $settings['button_style'];

		if( $settings['el_hide_mobile'] == 'yes' ){
			$style .= ' rey-caller-button--mobile-hidden';
		}

		printf( '<a href="%2$s" %3$s class="rey-caller-button btn %4$s">%1$s</a>',  $settings['button_text'], $settings['button_link'], $target, $style );
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
		$this->render_text();
		$this->render_button();
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
