<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://woofx.kaizenflow.xyz/
 * @since      1.0.0
 *
 * @package    Stock_Count_Report_Woocommerce
 * @subpackage Stock_Count_Report_Woocommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Stock_Count_Report_Woocommerce
 * @subpackage Stock_Count_Report_Woocommerce/admin
 * @author     woofx <rafaat.ahmed@kaizenflow.xyz>
 */
class Stock_Count_Report_Woocommerce_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Refernce to Stock_Table class.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Stock_Table
	 */
	private $stock_table;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Add stock report to WooCommerce Reports
	 *
	 * @since	1.0.0
	 * @param	array	$reports	list of all reports under the wc-report page
	 */
	public function register_report_stock_count( $reports ){

		$reports['stock']['reports']['stock_count'] = array(
			'title'       => __( 'Stock Count', $this->plugin_name ),
			'description' => '',
			'hide_title'  => true,
			'callback'    => array( $this, 'display_report_stock_count' ),
		);
	
		return $reports;
	
	}

	/**
	 * Display callback for report section
	 * 
	 * @since 1.0.0
	 */
	public function display_report_stock_count(){

		//Note: $this->stock_table is initiated in set_report_screen_options()
		
		$this->stock_table->prepare_items();
		$this->stock_table->display();

	}

	/**
	 * Register screen options for this report view
	 * 
	 * @since 1.0.0
	 */
	public function register_report_screen_options(){
		
		// get screen object
		$screen = get_current_screen();

		// check whether in report screen
		if( !is_object($screen) || $this->_is_report_screen($screen->id)){

			// load Stock_Table class
			if( !class_exists('Stock_Table') ){

				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-stock-table.php';

			}

			// initiate class			
			$this->stock_table = new Stock_Table($this->plugin_name, $this->version);

		}
		
	}

	/**
	 * Hook to save screen options
	 * 
	 * @since 1.0.0
	 */
	public function set_report_screen_options( $status, $option, $value){
		return $value;
	}

	/**
	 * Screen option default values for hidden columns
	 *
	 * @since	1.0.0
	 * @param	string	$screen	screen id of current page
	 */
	public function default_report_hidden_columns($screen){
		$hidden = array();
		if( $this->_is_report_screen($screen) ){
			$hidden = array('price','amount');
		}
		return $hidden;
	}

	/**
	 * Register js scripts for report page
	 *
	 * @since	1.0.0
	 * @param	string	$screen	screen id of current page
	 */
	public function enqueue_scripts($screen) {

		if( $this->_is_report_screen($screen) ){
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/stock-count-report-woocommerce-admin.js', array( 'jquery' ), $this->version, false );
		}

	}

	/**
	 * Checks whether current page is report page
	 *
	 * @since	1.0.0
	 * @param	string	$screen	screen id of current page
	 */
	private function _is_report_screen($screen){
		if( 'woocommerce_page_wc-reports' == $screen ) {

			if(isset($_GET['tab']) && isset($_GET['report'])){

				if( 'stock' == $_GET['tab'] && 'stock_count' == $_GET['report']){

					return true;
	
				}

			}			

		}

		return false;
	}

}
