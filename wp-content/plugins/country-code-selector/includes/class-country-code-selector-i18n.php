<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://www.intolap.com
 * @since      1.2
 *
 * @package    Country_Code_Selector
 * @subpackage Country_Code_Selector/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.2
 * @package    Country_Code_Selector
 * @subpackage Country_Code_Selector/includes
 * @author     INTOLAP <developer@intolap.com>
 */
class Country_Code_Selector_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.2
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'country-code-selector',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
