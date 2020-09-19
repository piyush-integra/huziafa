<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class ReyCoreElementor_Widget_Assets
{
	private static $_instance = null;
	private $reycore_elementor = null;
	private $scripts = [];
	private $styles = [];
	private $added_elements = [];

	private function __construct()
	{
		$this->reycore_elementor = reyCoreElementor();
		$this->get_scripts_and_styles();

		add_action( 'elementor/frontend/after_register_scripts', [$this, 'register_widgets_scripts'] );
		add_action( 'elementor/frontend/after_enqueue_styles', [$this, 'enqueue_widgets_styles'] );

		if( ! defined('REY_SCRIPTS_DEBUG') ){
			add_action( 'elementor/element/before_parse_css', [$this, 'add_widget_css_to_post_file'], 0, 2 );
		}

		add_action( 'elementor/element/parse_css', [$this, 'add_section_custom_css_to_post_file'], 10, 2 );

	}

	/**
	 * Get all script.js and style.css files
	 *
	 * @since 1.0.0
	 */
	private function get_scripts_and_styles(){

		$disabled_elements = class_exists('ReyCore_WidgetsManager') ? ReyCore_WidgetsManager::get_disabled_elements() : [];

		foreach ( $this->reycore_elementor->widgets as $widget ) {

			// don't load disabled elements
			if( in_array('reycore-' . $widget, $disabled_elements, true) ){
				continue;
			}

			$assets_folder = trailingslashit('assets');

			$dir_path = trailingslashit($this->reycore_elementor->widgets_dir . $widget) . $assets_folder;
			$uri = trailingslashit(REY_CORE_URI . $this->reycore_elementor->widgets_folder . $widget) . $assets_folder;

			// script
			if( is_readable($dir_path . 'script.js') ){
				$this->scripts[$widget] = $uri . 'script.js';
			}

			if( is_readable($dir_path . 'style.css') ){
				$this->styles[ 'reycore-' . $widget ]['uri'] = $uri . 'style.css';
				$this->styles[ 'reycore-' . $widget ]['dir'] = $dir_path . 'style.css';
			}
		}
	}

	/**
	 * Register widgets script.js files, for enqueuing as dependecies
	 *
	 * @since 1.0.0
	 */
	public function register_widgets_scripts(){
		foreach ( $this->scripts as $widget => $script ) {
			wp_register_script(
				"reycore-widget-{$widget}-scripts",
				$script,
				['elementor-frontend', 'rey-core-elementor-frontend'],
				REY_CORE_VERSION,
				true
			);
		}
	}

	/**
	 * Enqueue widgets style.css stylesheets, but only for Edit Mode, or when in Rey-development mode
	 *
	 * @since 1.0.0
	 */
	public function enqueue_widgets_styles(){
		$handles = [];

		foreach ( $this->styles as $widget => $style ) {
			if( isset($style['uri']) ){
				$handle = "reycore-widget-{$widget}-styles";
				$handles[] = $handle;
				wp_register_style(
					$handle,
					$style['uri'],
					false,
					REY_CORE_VERSION
				);
			}
		}

		if( (defined('REY_SCRIPTS_DEBUG') && REY_SCRIPTS_DEBUG) || \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode()){
			foreach ( $handles as $handle ) {
				wp_enqueue_style( $handles );
			}
		}
	}

	/**
	 * Adds widgets style.css into post's CSS file.
	 *
	 * @since 1.0.0
	 */
	public function add_widget_css_to_post_file( $post_css, $element ){

		if ( $post_css instanceof Elementor\Core\DynamicTags\Dynamic_CSS ) {
			return;
		}

		$el_name = $element->get_unique_name();
		// check if widget is used in this particular post.
		if( array_key_exists( $el_name, $this->styles) ){
			// check if Elementor CSS metadata exists,
			// and only then, add the widgets CSS
			if( ! metadata_exists('post', $post_css->get_post_id(), $post_css::META_KEY ) && !in_array($el_name, $this->added_elements) ){

				$stylesheet = '';

				if(
					($wp_filesystem = reycore__wp_filesystem()) &&
					($stylesheet_file = $this->styles[$el_name]['dir']) && $wp_filesystem->is_file( $stylesheet_file )
				) {
					$stylesheet = $wp_filesystem->get_contents( $stylesheet_file );
				}

				$post_css->get_stylesheet()->add_raw_css( $stylesheet );
				$this->added_elements[] = $el_name;
			}
		}
	}

	public function add_section_custom_css_to_post_file( $post_css, $element ){

		if ( $post_css instanceof Elementor\Core\DynamicTags\Dynamic_CSS ) {
			return;
		}

		if( 'section' !== $element->get_type() ){
			return;
		}

		$rey_custom_css = $element->get_settings('rey_custom_css');

		if( ! ($css = trim( $rey_custom_css )) ) {
			return;
		}

		$css = str_replace( 'SECTION-ID', $post_css->get_element_unique_selector( $element ), $css );

		$post_css->get_stylesheet()->add_raw_css( $css );
	}


	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCoreElementor_Widget_Assets
	 */
	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
}

ReyCoreElementor_Widget_Assets::getInstance();
