<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://www.intolap.com
 * @since      1.2
 *
 * @package    Country_Code_Selector
 * @subpackage Country_Code_Selector/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.2
 * @package    Country_Code_Selector
 * @subpackage Country_Code_Selector/includes
 * @author     INTOLAP <developer@intolap.com>
 */
class Country_Code_Selector {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.2
	 * @access   protected
	 * @var      Country_Code_Selector_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.2
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.2
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.2
	 */
	public function __construct() {
		if ( defined( 'COUNTRY_CODE_SELECTOR_VERSION' ) ) {
			$this->version = COUNTRY_CODE_SELECTOR_VERSION;
		} else {
			$this->version = '1.2';
		}
		$this->plugin_name = 'country-code-selector';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Country_Code_Selector_Loader. Orchestrates the hooks of the plugin.
	 * - Country_Code_Selector_i18n. Defines internationalization functionality.
	 * - Country_Code_Selector_Admin. Defines all hooks for the admin area.
	 * - Country_Code_Selector_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.2
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-country-code-selector-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-country-code-selector-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-country-code-selector-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-country-code-selector-public.php';

		$this->loader = new Country_Code_Selector_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Country_Code_Selector_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.2
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Country_Code_Selector_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.2
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Country_Code_Selector_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'country_code_selector_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'country_code_selector_settings' );

	}

	

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.2
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Country_Code_Selector_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		if(get_option('enable_on_woocommerce') == 'on'){
			$this->loader->add_action( 'woocommerce_after_checkout_form', $plugin_public, 'ccs_enable_on_woocomerce');
		}

		if(get_option('enable_on_shopp') == 'on'){
			$this->loader->add_action( 'get_footer', $plugin_public, 'ccs_enable_on_shopp');
		}

		if(
			get_option('enable_on_gform') == 'on' && 
			get_option('selected_gform') !== '' &&
			get_option('gform_phone_field_id') !== ''
		){
			$gform_id = get_option('selected_gform');
			$this->loader->add_action( 'gform_register_init_scripts_'.$gform_id, $plugin_public, 'ccs_enable_on_gravity_form', 10, 2 );
			add_filter( 'gform_phone_formats', 'ccs_override_phone_format', 10, 2 );
		}

		if(
			get_option('enable_on_cform7') == 'on' &&
			get_option('selected_cform7') !== '' &&
			get_option('cform7_phone_field_id') !== ''
		){
			$this->loader->add_action( 'get_footer', $plugin_public, 'ccs_enable_on_contact_form7', 10, 0 ); 
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.2
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.2
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.2
	 * @return    Country_Code_Selector_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.2
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
