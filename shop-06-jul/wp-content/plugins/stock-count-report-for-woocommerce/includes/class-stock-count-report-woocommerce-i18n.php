<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://woofx.kaizenflow.xyz/
 * @since      1.0.0
 *
 * @package    Stock_Count_Report_Woocommerce
 * @subpackage Stock_Count_Report_Woocommerce/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Stock_Count_Report_Woocommerce
 * @subpackage Stock_Count_Report_Woocommerce/includes
 * @author     woofx <rafaat.ahmed@kaizenflow.xyz>
 */
class Stock_Count_Report_Woocommerce_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'stock-count-report-woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
