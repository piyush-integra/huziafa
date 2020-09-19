<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://woofx.kaizenflow.xyz/
 * @since             1.0.0
 * @package           Stock_Count_Report_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Stock Count Report for WooCommerce
 * Plugin URI:        https://github.com/woofx/stock-count-report-woocommerce
 * Description:       View stock count report for your WooCoomerce store.
 * Version:           1.0.0
 * Author:            woofx
 * Author URI:        http://woofx.kaizenflow.xyz/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       stock-count-report-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'STOCK_COUNT_REPORT_WOOCOMMERCE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-stock-count-report-woocommerce-activator.php
 */
function activate_stock_count_report_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-stock-count-report-woocommerce-activator.php';
	Stock_Count_Report_Woocommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-stock-count-report-woocommerce-deactivator.php
 */
function deactivate_stock_count_report_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-stock-count-report-woocommerce-deactivator.php';
	Stock_Count_Report_Woocommerce_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_stock_count_report_woocommerce' );
register_deactivation_hook( __FILE__, 'deactivate_stock_count_report_woocommerce' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-stock-count-report-woocommerce.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_stock_count_report_woocommerce() {

	$plugin = new Stock_Count_Report_Woocommerce();
	$plugin->run();

}
run_stock_count_report_woocommerce();
