<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.intolap.com
 * @since      1.2
 *
 * @package    Country_Code_Selector
 * @subpackage Country_Code_Selector/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Country_Code_Selector
 * @subpackage Country_Code_Selector/admin
 * @author     INTOLAP <developer@intolap.com>
 */
class Country_Code_Selector_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.2
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.2
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.2
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.2
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Country_Code_Selector_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Country_Code_Selector_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name.'-jqms', plugin_dir_url( __FILE__ ) . 'css/jquery.multiselect.css', array(), $this->version, 'all' );

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/country-code-selector-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.2
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Country_Code_Selector_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Country_Code_Selector_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name.'-jq', 'https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js', array(), $this->version, false );
		wp_enqueue_script( $this->plugin_name.'-jqms', plugin_dir_url( __FILE__ ) . 'js/jquery.multiselect.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/country-code-selector-admin.js', array( 'jquery' ), $this->version, false );
		/*wp_localize_script(
	      'ccs-ajax-script',
	      'ccs_object',
	      array( 
	        'ajaxurl' => admin_url( 'admin-ajax.php' ),
	        'show_selected' => get_option('show_selected')
	      )
	    );*/
	}

	public function country_code_selector_settings(){
		add_option( 'enable_on_woocommerce','');
		
		add_option( 'enable_on_shopp','');
		
		add_option( 'enable_on_gform','');
		add_option( 'selected_gform','');
		add_option( 'gform_phone_field_id','');

		add_option( 'enable_on_cform7','');
		add_option( 'selected_cform7','');
		add_option( 'cform7_phone_field_id','');

		add_option( 'show_selected','');
		add_option( 'selected_countries','');

		add_option( 'js_validation','');

   		register_setting( 'country_code_selector_group','enable_on_woocommerce','myplugin_callback' );
   		
   		register_setting( 'country_code_selector_group','enable_on_shopp','myplugin_callback' );
   		
   		register_setting( 'country_code_selector_group','enable_on_gform','myplugin_callback' );
   		register_setting( 'country_code_selector_group','selected_gform','myplugin_callback' );
   		register_setting( 'country_code_selector_group','gform_phone_field_id','myplugin_callback' );
   		
   		register_setting( 'country_code_selector_group','enable_on_cform7','myplugin_callback' );
   		register_setting( 'country_code_selector_group','selected_cform7','myplugin_callback' );
   		register_setting( 'country_code_selector_group','cform7_phone_field_id','myplugin_callback' );

   		register_setting( 'country_code_selector_group','show_selected','myplugin_callback' );
   		
   		register_setting( 'country_code_selector_group','selected_countries','myplugin_callback' );

   		register_setting( 'country_code_selector_group','js_validation','myplugin_callback' );
	}

	// creating the menu entries
	public function country_code_selector_page() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/country-code-selector-admin-display.php';
	}


	// creating the options menu entriess
	public function country_code_selector_menu() {
		add_options_page('Country Code Selector', 'Country Code Selector', 'manage_options', 'country-code-selector', array($this,'country_code_selector_page'));
	}

}
