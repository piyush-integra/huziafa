<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://woofx.kaizenflow.xyz/
 * @since      1.0.0
 *
 * @package    Stock_Count_Report_Woocommerce
 * @subpackage Stock_Count_Report_Woocommerce/includes
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
 * @since      1.0.0
 * @package    Stock_Count_Report_Woocommerce
 * @subpackage Stock_Count_Report_Woocommerce/includes
 * @author     woofx <rafaat.ahmed@kaizenflow.xyz>
 */
class Stock_Count_Report_Woocommerce {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Stock_Count_Report_Woocommerce_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Reference to the activated plugin instance.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Cod_Default_Status_For_Woocommerce    $_instance    Reference to the activated plugin instance.
	 */
	protected static $_instance = null;

	/**
	 * Main Plugin Instance
	 *
	 * Ensures only one instance of plugin is loaded or can be loaded.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'STOCK_COUNT_REPORT_WOOCOMMERCE_VERSION' ) ) {
			$this->version = STOCK_COUNT_REPORT_WOOCOMMERCE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'stock-count-report-woocommerce';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Stock_Count_Report_Woocommerce_Loader. Orchestrates the hooks of the plugin.
	 * - Stock_Count_Report_Woocommerce_i18n. Defines internationalization functionality.
	 * - Stock_Count_Report_Woocommerce_Admin. Defines all hooks for the admin area.
	 * - Stock_Count_Report_Woocommerce_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-stock-count-report-woocommerce-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-stock-count-report-woocommerce-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-stock-count-report-woocommerce-admin.php';

		$this->loader = new Stock_Count_Report_Woocommerce_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Stock_Count_Report_Woocommerce_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Stock_Count_Report_Woocommerce_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Stock_Count_Report_Woocommerce_Admin( $this->get_plugin_name(), $this->get_version() );

		// Add stock report to WooCommerce Reports
		$this->loader->add_filter( 'woocommerce_admin_reports', $plugin_admin, 'register_report_stock_count' );

		// Register report screen options
		$this->loader->add_action( 'load-woocommerce_page_wc-reports', $plugin_admin, 'register_report_screen_options');
		
		// Hook to save screen options
		$this->loader->add_action( 'set-screen-option', $plugin_admin, 'set_report_screen_options');
		
		// Screen option default values for hidden columns
		$this->loader->add_filter( 'default_hidden_columns', $plugin_admin, 'default_report_hidden_columns');

		// Enque scripts for report filter controls
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Stock_Count_Report_Woocommerce_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * The name of the option key prefixed with plugin name, used to uniquely 
	 * identify it within the context of WordPress.
	 *
	 * @since	1.0.0
	 * @param	string	$key	Option key name.
	 * @return  string			Prefixed name of option.
	 */
	public function get_option_name($key) {
		return $this->plugin_name . '-' . $key;
	}

	/**
	 * The value of the option by using plugin local option key (not prefixed).
	 *
	 * @since	1.0.0
	 * @param	string	$key		Option key name
	 * @param	mixed	$default	Default option value
	 * @return  mixed				Option value.
	 */
	public function get_option($key,$default) {
		$_name = $this->plugin_name . '-' . $key;
		return get_option($_name,$default);
	}

}
