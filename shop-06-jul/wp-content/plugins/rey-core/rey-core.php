<?php
/**
 * Plugin Name: Rey Core
 * Description: Core plugin for Rey.
 * Plugin URI: http://www.reytheme.com/
 * Version: 1.6.9
 * Author: ReyTheme
 * Author URI:  https://twitter.com/mariushoria
 * Text Domain: rey-core
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore') ):

class ReyCore
{
	private static $_instance = null;

	private function __construct()
	{
		$this->define_constants();
		$this->init_hooks();
		$this->includes();
	}

	/**
	 * Initialize Hooks
	 *
	 * @since 1.0.0
	 */
	public function init_hooks()
	{
		add_action( 'admin_notices', [$this, 'show_errors_and_deactivate'] );
		add_action( 'admin_notices', [$this, 'show_notices'] );
		add_action( 'admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
		add_action( 'wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
		add_action( 'plugins_loaded', [$this, 'includes_after_plugins_loaded'] );
	}

	/**
	 * Define Constants.
	 * @since 1.0.0
	 */
	private function define_constants()
	{
		$this->define( 'REY_CORE_DIR', plugin_dir_path( __FILE__ ) );
		$this->define( 'REY_CORE_URI', plugin_dir_url( __FILE__ ) );
		$this->define( 'REY_CORE_THEME_NAME', 'rey' );
		$this->define( 'REY_CORE_THEME_MIN_VERSION', '1.6.0' );

		if( !defined('REY_DEV_MODE') ){
			$this->define( 'REY_CORE_VERSION', '1.6.9' );
		}
		else {
			// cache buster
			$this->define( 'REY_CORE_VERSION', rand(100, 99999) );
		}

		$this->define( 'REY_CORE_PLACEHOLDER', REY_CORE_URI . 'assets/images/placeholder.png' );
		$this->define( 'REY_CORE_REQUIRED_WP_VERSION', '4.7' );
		$this->define( 'REY_CORE_REQUIRED_PHP_VERSION', '5.4.0' );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Check plugin requirements
	 * @since 1.0.0
	 */
	private function get_errors()
	{
		$errors = array();

		// Check PHP version
		if ( version_compare( phpversion(), REY_CORE_REQUIRED_PHP_VERSION, '<' ) ) {
			$errors[] = sprintf( __( 'The PHP version <strong>%s</strong> is needed in order to be able to run the <strong>%s</strong> plugin. Please contact your hosting support and ask them to upgrade the PHP version to at least v<strong>%s</strong> for you.', 'rey-core' ),
				REY_CORE_REQUIRED_PHP_VERSION, 'Rey Core', REY_CORE_REQUIRED_PHP_VERSION );
		}
		// Check WP version
		elseif ( version_compare( get_bloginfo( 'version' ), REY_CORE_REQUIRED_WP_VERSION, '<' ) ) {
			$errors[] = __( '<strong>WordPress Rest API</strong> is needed in order to be able to run the <strong>Rey Core</strong> plugin. Please update WordPress to the latest version, or at least version 4.7 .', 'rey-core' );
		}

		return $errors;
	}

	/**
	 * Display errors and deactivate
	 *
	 * @since 1.0.0
	 */
	function show_errors_and_deactivate()
	{
		$errors = $this->get_errors();

		if ( ! empty( $errors ) ) {
			/**
			 * Render the notices about the plugin's requirements
			 */
			echo '<div class="notice notice-error">';
			foreach ( $errors as $error ) {
				echo "<p>{$error}</p>";
			}
			echo '<p>' . sprintf( __( '<strong>%s</strong> has been deactivated.', 'rey-core' ), 'Rey Core' ) . '</p>';
			echo '</div>';

			$this->deactivate();
		}
	}

	/**
	 * Display notices
	 *
	 * @since 1.0.0
	 */
	function show_notices()
	{
		$errors = [];

		if ( defined('REY_THEME_VERSION') && version_compare( REY_THEME_VERSION, REY_CORE_THEME_MIN_VERSION, '<' ) ) {
			$errors[] = sprintf(__( 'Rey theme is outdated and not in sync with Rey Core. This can cause potential issues with if they\'re not both at their latest versions. Please check the <a href="%s">Updates</a> page and update it to its latest version.', 'rey-core' ) , esc_url( admin_url( 'update-core.php?force-check=1' ) ));
		}

		if ( ! empty( $errors ) ) {
			/**
			 * Render the notices about the plugin's requirements
			 */
			echo '<div class="notice notice-error">';
			foreach ( $errors as $error ) {
				echo "<p>{$error}</p>";
			}
			echo '</div>';
		}
	}

	private function deactivate(){
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		deactivate_plugins( 'rey-core/rey-core.php' );
		unset( $_GET['activate'], $_GET['plugin_status'], $_GET['activate-multi'] );
		return;
	}

	// Load localization file
	function load_plugin_textdomain(){
		load_plugin_textdomain( 'rey-core', false, plugin_basename(dirname(__FILE__)) . '/languages');
	}

	function enqueue_admin_scripts(){

		// Scripts
		wp_enqueue_script( 'rey-core-admin-script', REY_CORE_URI . 'assets/js/admin.js', ['jquery', 'underscore'], REY_CORE_VERSION, true );
		wp_localize_script( 'rey-core-admin-script', 'reyCoreAdmin', apply_filters('reycore/admin_script_params', [
			'rey_core_version' => REY_CORE_VERSION,
			'ajax_url'      => admin_url( 'admin-ajax.php' ),
			'ajax_nonce'    => wp_create_nonce('reycore-ajax-verification'),
			'back_btn_text' => esc_html__('Back to List', 'rey-core'),
			'back_btn_url'  => admin_url('edit.php?post_type=rey-global-sections'),
			'is_customizer' => false,
			'strings' => [
				'refresh_demos_error' => esc_html__('Error. Please retry!', 'rey-core'),
				'reloading' => esc_html__('Reloading page!', 'rey-core'),
				'refresh_btn_text' => esc_html__('Refresh Demos List', 'rey-core'),
				'help' => esc_html__('Need help?', 'rey-core'),
			],
			'sound_effect' => REY_CORE_URI . 'assets/audio/ding.mp3',
		]) );
		// Styles
		wp_enqueue_style('rey-core-admin-style', REY_CORE_URI . 'assets/css/admin.css', false, REY_CORE_VERSION, null);
	}

	function enqueue_frontend_scripts()
	{
		// Register Scripts
		wp_register_script( 'animejs', REY_CORE_URI . 'assets/js/lib/anime.min.js', ['jquery'], '3.1.0', true );
		wp_register_script( 'simple-scrollbar', REY_CORE_URI . 'assets/js/lib/simple-scrollbar.js', ['jquery'], '0.4.0', true );
		// Enqueue scripts

		wp_enqueue_script( 'reycore-scripts', REY_CORE_URI . 'assets/js/reycore.js', ['jquery', 'rey-script'], REY_CORE_VERSION, true );
		wp_localize_script( 'reycore-scripts', 'reyCoreParams', apply_filters('reycore/script_params', [
			'icons_path' => reycore__icons_sprite_path(),
			'social_icons_path' => reycore__social_icons_sprite_path(),
			'js_params'     => [
				'sticky_debounce' => 200,
				'svg_icons_version' => apply_filters('rey/svg_icon/append_version', true) ? REY_CORE_VERSION : ''
			]
		]) );
		// Enqueue Styles
		wp_enqueue_style('reycore-styles', REY_CORE_URI . 'assets/css/styles.css', ['rey-wp-style'], REY_CORE_VERSION, null);
	}

	function includes(){
		//#! Load core files
		require_once REY_CORE_DIR . 'inc/plugin-functions.php';
		// Misc.
		require_once REY_CORE_DIR . 'inc/includes/helper.php';
		require_once REY_CORE_DIR . 'inc/includes/breadcrumbs.php';
		require_once REY_CORE_DIR . 'inc/includes/svg-support.php';
		require_once REY_CORE_DIR . 'inc/includes/colors.php';
		require_once REY_CORE_DIR . 'inc/includes/webfonts.php';
		require_once REY_CORE_DIR . 'inc/includes/admin.php';
		require_once REY_CORE_DIR . 'inc/styles.php';
		require_once REY_CORE_DIR . 'inc/tags.php';
		require_once REY_CORE_DIR . 'inc/tags-header.php';
		require_once REY_CORE_DIR . 'inc/cover.php';
		require_once REY_CORE_DIR . 'inc/shortcodes.php';
		require_once REY_CORE_DIR . 'inc/demos.php';
		// vendor
		require_once REY_CORE_DIR . 'inc/vendor/kirki/kirki.php';
		require_once REY_CORE_DIR . 'inc/vendor/advanced-custom-fields-pro/acf.php';
	}

	function includes_after_plugins_loaded()
	{
		$this->load_plugin_textdomain();

		if ( reycore__theme_active() && class_exists('WooCommerce') ) {
			require_once REY_CORE_DIR . 'inc/woocommerce/woocommerce.php';
		}

		// Advanced Custom Fields
		if( class_exists('ACF') )  {
			// load predefined fields
			if( apply_filters('reycore/acf/use_predefined_fields', true) ){
				require_once REY_CORE_DIR . 'inc/acf/acf-fields.php';
			}
			require_once REY_CORE_DIR . 'inc/acf/acf-functions.php';
		}

		// Elementor
		require_once REY_CORE_DIR . 'inc/elementor/elementor.php';
		require_once REY_CORE_DIR . 'inc/elementor/elementor-widget-assets.php';
		require_once REY_CORE_DIR . 'inc/elementor/global-sections.php';

		if( reycore__theme_active() ){

			// Load Kirki fallback & Customizer options
			require_once REY_CORE_DIR . 'inc/customizer/kirki-fallback.php';

			if( class_exists('Kirki') )  {
				require_once REY_CORE_DIR . 'inc/customizer/customizer-options.php';
				require_once REY_CORE_DIR . 'inc/customizer/customizer-functions.php';
				require_once REY_CORE_DIR . 'inc/customizer/customizer-styles.php';
				require_once REY_CORE_DIR . 'inc/customizer/customizer-fields.php';
			}
		}

		// Modules
		require_once REY_CORE_DIR . 'inc/modules/loader.php';

		// Compatiblity
		require_once REY_CORE_DIR . 'inc/compatibility/loader.php';


		do_action('reycore/loaded');
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore
	 */
	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

}
endif;

ReyCore::getInstance();
