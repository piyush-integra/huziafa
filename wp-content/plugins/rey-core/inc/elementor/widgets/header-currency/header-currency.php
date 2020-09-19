<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if( (class_exists( 'WOOCS_STARTER' ) || class_exists( 'WOOMULTI_CURRENCY_F' ) || class_exists( 'WC_Aelia_CurrencySwitcher' )) && !class_exists('ReyCore_Widget_Header_Currency') ):
/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Header_Currency extends \Elementor\Widget_Base {

	public function get_name() {
		return 'reycore-header-currency';
	}

	public function get_title() {
		return __( 'Currency switcher - Header', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-select';
	}

	public function get_categories() {
		return [ 'rey-header' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements-header/#currency-switcher';
	}

	public function get_keywords() {
		return [ 'currency', 'woocs', 'switcher', 'aelia' ];
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

		if( class_exists('Aelia_WC_TaxDisplayByCountry_RequirementsChecks') ):

			$this->add_control(
				'aelia_show_countries',
				[
					'label' => esc_html__( 'Show Countries (Aelia)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

		endif;

		$this->add_control(
			'symbol',
			[
				'label' => esc_html__( 'Symbol', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'yes',
				'options' => [
					'yes'  => esc_html__( 'Show symbol', 'rey-core' ),
					'first'  => esc_html__( 'Show symbol first', 'rey-core' ),
					'no'  => esc_html__( 'No symbol', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'always_show_caret',
			[
				'label' => esc_html__( 'Always show caret', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);


		$this->add_control(
			'show_mobile',
			[
				'label' => esc_html__( 'Show on mobiles', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
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
			'text_color',
			[
				'label' => __( 'Button Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-woocurrency .rey-headerIcon-btn' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'hover_text_color',
			[
				'label' => __( 'Button Hover Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-woocurrency .rey-headerIcon-btn:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'label' => __( 'Button Typography', 'rey-core' ),
				'selector' => '{{WRAPPER}} .rey-woocurrency .rey-headerIcon-btn',
			]
		);

		$this->add_control(
			'panel_text_color',
			[
				'label' => __( 'Panel Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-woocurrency .rey-woocurrency-item' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'panel_hover_text_color',
			[
				'label' => __( 'Panel Hover Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-woocurrency .rey-woocurrency-item:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'panel_typography',
				'label' => __( 'Panel Typography', 'rey-core' ),
				'selector' => '{{WRAPPER}} .rey-woocurrency .rey-woocurrency-item',
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

		if(
			(class_exists( 'WOOCS_STARTER' ) || class_exists( 'WOOMULTI_CURRENCY_F' ) || class_exists( 'WC_Aelia_CurrencySwitcher' )) &&
			class_exists('ReyCore_Compatibility__WoocommerceCurrencySwitcher')
			) {

			$settings = $this->get_settings_for_display();

			if( isset($settings['aelia_show_countries']) && $settings['aelia_show_countries'] !== '' ){
				ReyCore_Compatibility__WoocommerceCurrencySwitcher::getInstance()->aelia_country();
				return;
			}

			ReyCore_Compatibility__WoocommerceCurrencySwitcher::getInstance()->header([
				'symbol' => $settings['symbol'],
				'always_show_caret' => $settings['always_show_caret'] === 'yes',
				'show_mobile' => $settings['show_mobile'] !== '',
			]);

		}

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
