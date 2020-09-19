<?php
/**
 * @link              http://www.intolap.com
 * @since             1.2
 * @package           Country_Code_Selector
 *
 * @wordpress-plugin
 * Plugin Name:       Country Code Selector
 * Plugin URI:        http://www.intolap.com/products/country-code-selector/
 * Description:       Country Code Selector uses a JavaScript base to allow customers checking out in WooCommerce, Shopp, Contact form 7, Gravity form plugins select the country code using a dropdown field.
 * Version:           1.2
 * WC requires at least: 3.9
 * WC tested up to: 4.2
 * Author:            INTOLAP
 * Author URI:        http://www.intolap.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       country-code-selector
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.2 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'COUNTRY_CODE_SELECTOR_VERSION', '1.2' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-country-code-selector-activator.php
 */
function activate_country_code_selector() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-country-code-selector-activator.php';
	Country_Code_Selector_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-country-code-selector-deactivator.php
 */
function deactivate_country_code_selector() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-country-code-selector-deactivator.php';
	Country_Code_Selector_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_country_code_selector' );
register_deactivation_hook( __FILE__, 'deactivate_country_code_selector' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-country-code-selector.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.2
 */
function run_country_code_selector() {

	$plugin = new Country_Code_Selector();
	$plugin->run();

}
run_country_code_selector();
