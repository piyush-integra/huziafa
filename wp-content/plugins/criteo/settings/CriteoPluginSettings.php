<?php

defined('ABSPATH') or die('Forbidden');


class CriteoPluginSettings
{
    const PLUGIN_FILE = 'criteo/criteo.php';
    const PRODUCT_FEED_NAME = 'criteo_feed';
    const INFO_FEED_NAME = 'criteo_info';
    const DEBUG_FEED_NAME = 'criteo_debug';
    const VERSION_FEED_NAME = 'criteo_version';

    const CRITEO_SETTINGS_PREFIX = 'criteo_integrate_settings';
    const USE_SKU_FOR_PRODUCT_ID_PROPERTY = 'criteo_integrate_settings_use_sku';
    const PARTNER_ID_PROPERTY = 'criteo_integrate_settings_partner';
    const FEED_QUERY_LIMIT_PROPERTY = 'criteo_integrate_settings_limit';
    const USERNAME_PROPERTY = 'criteo_integrate_settings_username';
    const PASSWORD_PROPERTY = 'criteo_integrate_settings_password';
    const MOAB_ID_PROPERTY = 'criteo_integrate_settings_moab_id';
    const DEBUG_MODE_PROPERTY = 'criteo_integrate_settings_debug_mode';

    const TOKEN_URL_PARAM = 'x-criteo-token';
    const DEFAULT_PRODUCTS_PER_FEED_PAGE = 100;
    const DEFAULT_USE_SKU_FOR_PRODUCT_ID = 'no';
    const FEED_PAGE_QUERY_VARIABLE = "page";
    const FEED_PAGE_PRODUCTS_QUERY_VARIABLE = "limit";

    const MIN_WORDPRESS_VERSION = '4.1';
    const TESTED_UP_TO_WORDPRESS_MAJOR_VERSION = '4.9';
    const MIN_WOOCOMERCE_VERSION = '2.1';
    const TESTED_UP_TO_WOOCOMERCE_MAJOR_VERSION = '3.3';

    public static $SETTINGS_TO_BE_EXPOSED = ['permalink_structure', 'rewrite_rules', 'template', 'stylesheet',
        'woocommerce_db_version', 'woocommerce_default_country', 'woocommerce_currency', 'woocommerce_price_num_decimals',
        'woocommerce_price_thousand_sep', 'woocommerce_price_decimal_sep', 'woocommerce_weight_unit',
        'woocommerce_dimension_unit', 'woocommerce_version', 'woocommerce_force_ssl_checkout'];

    public static function useSkuForProductID() {
        $value = get_option(self::USE_SKU_FOR_PRODUCT_ID_PROPERTY);
        if (strcasecmp($value, 'yes') == 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function getPartnerID() {
        return get_option(self::PARTNER_ID_PROPERTY);
    }

    public static function getMoabId() {
        return get_option(self::MOAB_ID_PROPERTY);
    }

    public static function getFeedQueryLimit() {
        $limit = (int) get_option(self::FEED_QUERY_LIMIT_PROPERTY);
        if (!$limit) {
            $limit = self::DEFAULT_PRODUCTS_PER_FEED_PAGE;
        }
        return $limit;
    }

    public static function getUser() {
        return get_option(self::USERNAME_PROPERTY);
    }

    public static function getPassword() {
        return get_option(self::PASSWORD_PROPERTY);
    }

    public static function getPluginNameAndVersion()
    {
        return self::getPluginName() . ' v' . self::getPluginVersion();
    }

    public static function getPluginName()
    {
        // Check if get_plugins() function exists. This is required on the front end of the
        // site, since it is in a file that is normally only loaded in the admin.
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $data = get_plugin_data(WP_PLUGIN_DIR . '/' . self::PLUGIN_FILE);
        return $data['Name'];
    }

    public static function getPluginVersion()
    {
        // Check if get_plugins() function exists. This is required on the front end of the
        // site, since it is in a file that is normally only loaded in the admin.
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $data = get_plugin_data(WP_PLUGIN_DIR . '/' . self::PLUGIN_FILE);
        return $data['Version'];
    }

    /**
     * On WooCommerce 3.0.0 product rating & number of reviews are calculated into product and refreshed when a review is added.
     * On WooCommerce >= 2.4.0 & < 3.0.0 you need to trigger WC_Product::get_average_rating()/WC_Product::get_review_count() methods
     * (they are automatically triggered when navigating to product page).
     * On WooCommerce < 2.4.0 the product rating & number of reviews are calculated on the fly when navigating to product page.
     */
    public static function isRatingPreCalculated() {
        return self::isWooCommerceGreatherOrEqualThan('3.0.0');
    }

    /**
     * On WooCommerce 2.6.0+ when you're updating the basket an AJAX call is performed and we must trigger
     * a new viewBasket event.
     * On older version the page is reloaded and the tag is loaded along with the page.
     */
    public static function isWooCommerceVersionWithAjaxBasketUpdate() {
        return self::isWooCommerceGreatherOrEqualThan('2.6.0');
    }

    public static function isVisibilityImplementedAsWpTerm() {
        return self::isWooCommerceGreatherOrEqualThan('3.0.0');
    }

    public static function isWooCommerceVersionWithVariationLinks() {
        return self::isWooCommerceGreatherOrEqualThan('2.6.0');
    }

    public static function isWooCommerceVersionTestedWithPlugin()
    {
        return self::isWooCommerceGreatherOrEqualThan(self::MIN_WOOCOMERCE_VERSION) &&
            self::isWooCommerceLessOrEqualThanMajorVersion(self::TESTED_UP_TO_WOOCOMERCE_MAJOR_VERSION);
    }

    public static function isWordPressVersionTestedWithPlugin()
    {
        return self::isWordpressGreatherOrEqualThan(self::MIN_WORDPRESS_VERSION) &&
            self::isWordpressLessOrEqualThanMajorVersion(self::TESTED_UP_TO_WORDPRESS_MAJOR_VERSION);
    }

    private static function isWooCommerceGreatherOrEqualThan($version)
    {
        return version_compare(self::getWooCommerceVersion(), $version, '>=');
    }

    private static function isWooCommerceLessOrEqualThanMajorVersion($version)
    {
        return version_compare(self::getWooCommerceMajorVersion(), $version, '<=');
    }

    private static function isWordpressGreatherOrEqualThan($version)
    {
        return version_compare(self::getWordPressVersion(), $version, '>=');
    }

    private static function isWordpressLessOrEqualThanMajorVersion($version)
    {
        return version_compare(self::getWordPressMajorVersion(), $version, '<=');
    }

    public static function getWordPressMajorVersion()
    {
        return self::getMajorVersion(self::getWordPressVersion());
    }

    public static function getWooCommerceMajorVersion()
    {
        return self::getMajorVersion(self::getWooCommerceVersion());
    }


    public static function getMajorVersion($version)
    {
        $versionArray = explode(".", $version);
        $majorVersion = $versionArray[0] . '.' . $versionArray[1];
        return $majorVersion;
    }

    public static function getWordPressVersion()
    {
        global $wp_version;
        return $wp_version;
    }

    public static function getWooCommerceVersion()
    {
        global $woocommerce;
        return $woocommerce->version;
    }

}