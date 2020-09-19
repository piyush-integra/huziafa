<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if(!class_exists('ReyCore_Widget_Header_Search')):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Header_Search extends \Elementor\Widget_Base {

	public function get_name() {
		return 'reycore-header-search';
	}

	public function get_title() {
		return __( 'Search Box - Header', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-search';
	}

	public function get_categories() {
		return [ 'rey-header' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements-header/#search-box';
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

		$cst_link_query['autofocus[section]'] = 'header_search_options';
		$cst_link = add_query_arg( $cst_link_query, admin_url( 'customize.php' ) );

		$this->add_control(
			'edit_link',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => sprintf( __( 'Search options can be edited into the <a href="%s" target="_blank">Customizer Panel > Header > Search</a>, but you can also override those settings below.', 'rey-core' ), $cst_link ),
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
			'search_style',
			[
				'label' => __( 'Search Panel Style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => $this->get_default_search_style(),
				'options' => [
					'wide' => esc_html__( 'Wide Panel', 'rey-core' ),
					'side' => esc_html__( 'Side Panel', 'rey-core' ),
				],
				'condition' => [
					'custom!' => [''],
				],
			]
		);

		$this->add_control(
			'search_complementary',
			[
				'label' => __( 'Suggestions content type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => esc_html__( '- Select -', 'rey-core' ),
					'menu' => esc_html__( 'Menu', 'rey-core' ),
					'keywords' => esc_html__( 'Keyword suggestions', 'rey-core' ),
				],
				'condition' => [
					'custom!' => [''],
				],
			]
		);

		$get_all_menus = reycore__get_all_menus();

		$this->add_control(
			'search_menu_source',
			[
				'label' => __( 'Menu Source', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => ['' => esc_html__('- Select -', 'rey-core')] + $get_all_menus,
				'default' => '',
				'condition' => [
					'custom!' => '',
					'search_complementary' => 'menu',
				],
			]
		);

		$this->add_control(
			'keywords',
			[
				'label' => __( 'Keywords', 'rey-core' ),
				'description' => __( 'Add keyword suggestions, separated by comma ",".', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => '',
				'placeholder' => __( 'eg: t-shirt, pants, trousers', 'rey-core' ),
				'condition' => [
					'custom!' => '',
					'search_complementary' => 'keywords',
				],
			]
		);


		$this->end_controls_section();


		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label' => esc_html__( 'Search Icon Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-headerSearch-toggle' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => __( 'Panel Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					':root' => '--search-text-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'bg_color',
			[
				'label' => __( 'Panel Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					':root' => '--search-bg-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();
	}

	public function get_default_search_style(){

		if( function_exists('reycore_wc__get_header_search_args') ){
			return reycore_wc__get_header_search_args('search_style');
		}

		return get_theme_mod('header_search_style', 'wide');
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

		$search_style = !is_null($settings['search_style']) ? $settings['search_style'] : $this->get_default_search_style();

		if( $settings['custom'] ){

			$vars = [
				'search_complementary' => $settings['search_complementary'],
				'search_menu_source' => $settings['search_menu_source'],
				'keywords' => $settings['keywords'],
				'search_style' => $search_style,
			];

			set_query_var('rey__header_search', apply_filters('reycore/elementor/header_search/vars', $vars ) );
		}

		// Wide & Side panels
		if( in_array($search_style, ['wide', 'side']) ){
			reycore__get_template_part('template-parts/header/search-toggle');
		}
		// Default simple form
		elseif($search_style === 'button') {
			get_template_part('template-parts/header/search-button');
		}

		do_action('reycore/elementor/header-search/template', $settings, $search_style);
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
