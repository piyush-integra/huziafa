<?php

/*
  Plugin Name: Criteo
  Plugin URI:  http://www.criteo.com/
  Description: Official Criteo plugin to setup your product feed and Criteo OneTag.
  Version:     1.3
  Author:      Criteo
  Author URI:  http://www.criteo.com/
  Text Domain: criteo
  Domain Path: /languages
  License:     Criteo Proprietary Rights
  License URI: http://www.criteo.com/terms-and-conditions/
 */

defined('ABSPATH') or die('Forbidden');

require_once(dirname(__FILE__) . '/feed/ProductFeedGenerator.php');
require_once(dirname(__FILE__) . '/platform/InfoFeedGenerator.php');
require_once(dirname(__FILE__) . '/platform/DebugFeedGenerator.php');
require_once(dirname(__FILE__) . '/platform/VersionFeedGenerator.php');
require_once(dirname(__FILE__) . '/platform/WordPressSettingsProvider.php');
require_once(dirname(__FILE__) . '/tagging/CriteoTagsProvider.php');
require_once(dirname(__FILE__) . '/settings/CriteoPluginSettings.php');
require_once(dirname(__FILE__) . '/settings/CriteoPluginSettingsManager.php');
require_once(dirname(__FILE__) . '/settings/WooCommerceExtension.php');

/**
 * Check if WooCommerce is active
 * */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    /*
     * CRITEO PLUGIN SETTINGS SECTION
     */
    if (is_admin()) {
        require_once(dirname(__FILE__) . '/settings/CriteoPluginSettingsUI.php');
    }

    register_activation_hook(__FILE__, array('CriteoPluginSettingsManager', 'loadDefaultSettings'));
    register_uninstall_hook(__FILE__, array('CriteoPluginSettingsManager', 'cleanupSettings'));

    //custom WooCommerce field used in Criteo Plugin Settings tab.
    add_action('woocommerce_admin_field_link', 'output_link_field');
    function output_link_field($value)
    {
        WooCommerceExtension::outputLinkField($value);
    }

    //custom WooCommerce field used in Criteo Plugin Settings tab.
    add_action('woocommerce_admin_field_version_display', 'output_version_display');
    function output_version_display($value)
    {
        WooCommerceExtension::outputVersionDisplayField($value);
    }

    add_action('woocommerce_admin_field_horizontal_line', 'output_horizontal_line');
    function output_horizontal_line()
    {
        WooCommerceExtension::outputHorizontalLine();
    }

    add_action('woocommerce_admin_field_subsection_title', 'output_subsection_title');
    function output_subsection_title($value)
    {
        WooCommerceExtension::outputSubsectionTitle($value);
    }

    //translations
    function loadTranslations() {
        load_plugin_textdomain( 'criteo', false, basename( dirname( __FILE__ ) ) . '/languages' ); ;
    }
    add_action('admin_init', 'loadTranslations');

    /*
     * FEED & INFO SECTION
     */
    register_activation_hook(__FILE__, 'criteoActivateFeeds');
    add_action('init', 'criteoFeedsInit');
    add_filter('query_vars', 'addQueryVarForCatalogPage');

    function criteoActivateFeeds() {
        global $wp_rewrite;
        criteoFeedsInit();
        //flush rule for one time during plugin activation as recommended in WordPress documentation.
        $wp_rewrite->flush_rules();
    }

    //create custom query variables
    function addQueryVarForCatalogPage($vars) {
        array_push($vars, CriteoPluginSettings::FEED_PAGE_QUERY_VARIABLE);
        array_push($vars, CriteoPluginSettings::FEED_PAGE_PRODUCTS_QUERY_VARIABLE);
        return $vars;
    }

    function criteoFeedsInit() {
        //Feed endpoint
        add_feed(CriteoPluginSettings::PRODUCT_FEED_NAME, array('ProductFeedGenerator', 'getCriteoFeed'));
        //Info & debug endpoints are implemented as feeds because REST Web Services are available only
        //in recent version of WordPress (>= 4.4.0).
        add_feed(CriteoPluginSettings::INFO_FEED_NAME, array('InfoFeedGenerator', 'getInfoFeed'));
        add_feed(CriteoPluginSettings::DEBUG_FEED_NAME, array('DebugFeedGenerator', 'getDebugFeed'));
        add_feed(CriteoPluginSettings::VERSION_FEED_NAME, array('VersionFeedGenerator', 'getVersion'));
    }

    /*
     * TAGS SECTION
     */
    add_action('wp_head', array('CriteoTagsProvider', 'addCriteoTag'));
    add_action('woocommerce_thankyou', array('CriteoTagsProvider', 'addCriteoSaleTag'));
    //basket is updated using an AJAX call on some WooCommerce versions.
    //We're intercepting that and returning a header with the basket info.
    add_action('send_headers', array('CriteoTagsProvider', 'addCriteoBasketHeader'));


    /**
     * DEV MODE SETTINGS
     */
    if (CriteoPluginDefaultConfig::DEV_MODE) {
        /**ENABLED FOR PROFILING*/
        add_shortcode( 'criteo_feed_shortcode', array('ProductFeedGenerator', 'testXmlFeed'));

        function createPerformancePage(){
            criteoLogData('Creating performance page.');
            $new_page_title = 'Performance';
            $new_page_content = '[criteo_feed_shortcode]';
            $new_page_template = '';
            $page_check = get_page_by_title($new_page_title);
            $new_page = array(
                'post_type' => 'page',
                'post_title' => $new_page_title,
                'post_content' => $new_page_content,
                'post_status' => 'publish',
                'post_author' => 1,
            );
            if(!isset($page_check->ID)){
                $new_page_id = wp_insert_post($new_page);
                if(!empty($new_page_template)){
                    update_post_meta($new_page_id, '_wp_page_template', $new_page_template);
                }
            }
        }
        //end install_events_pg function to add page to wp on plugin activation
        register_activation_hook(__FILE__, 'createPerformancePage');
    }

    if (!function_exists('criteoLogData')) {
        function criteoLogData($log)
        {
            if (!CriteoPluginDefaultConfig::DEV_MODE) {
                return;
            }
            $log = CriteoPluginSettings::getPluginNameAndVersion().' : '.$log;
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }


    if (!function_exists('criteoLogAuditData')) {
        function criteoLogAuditData($log)
        {
            $d = date("j-M-Y H:i:s e");
            $formattedMessage = "[$d] " . $log . PHP_EOL;
            if (defined('WC_LOG_DIR')) {
                if (is_writable(dirname(WC_LOG_DIR))) {
                    error_log($formattedMessage, 3, WC_LOG_DIR . "/criteo-ecp.log");
                } else {
                    error_log("Criteo-Unable to write into criteo-ecp.log file! Please check the write access of the folder " . WC_LOG_DIR);
                    error_log("Criteo-" . $formattedMessage);
                }
            } else {
                //in some older versions of woocommerce (21) the WC_LOG_DIR constant is not defined; in this case write the audit info into the standard debug log
                error_log("Criteo-" . $formattedMessage);
            }
        }
    }
}