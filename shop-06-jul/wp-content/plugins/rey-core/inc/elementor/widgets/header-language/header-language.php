<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if( (class_exists('QTX_Translator') || class_exists('Polylang') || class_exists('SitePress')) && !class_exists('ReyCore_Widget_Header_Language') ):
/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Header_Language extends \Elementor\Widget_Base {

	public function get_name() {
		return 'reycore-header-language';
	}

	public function get_title() {
		return __( 'Language switcher - Header', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-select';
	}

	public function get_categories() {
		return [ 'rey-header' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements-header/#language-switcher';
	}

	public function get_keywords() {
		return [ 'wpml', 'polylang', 'language' ];
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
			'show_flags',
			[
				'label' => esc_html__( 'Show Flags', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_active_flag',
			[
				'label' => esc_html__( 'Show Active Flag', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
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

		if( class_exists('QTX_Translator') && class_exists('ReyCore_Compatibility__QtranslateX' ) ) {
			ReyCore_Compatibility__QtranslateX::getInstance()->header($settings);
		}
		elseif( class_exists('Polylang') && class_exists('ReyCore_Compatibility__Polylang' ) ) {
			ReyCore_Compatibility__Polylang::getInstance()->header($settings);
		}
		elseif( class_exists('SitePress') && class_exists('ReyCore_Compatibility__Wpml' ) ){
			ReyCore_Compatibility__Wpml::getInstance()->header($settings);
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
