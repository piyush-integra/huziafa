<?php

defined('ABSPATH') or die('Forbidden');

require_once(dirname(__FILE__) . '/CriteoPluginSettings.php');
require_once(dirname(__FILE__) . '/WooCommerceExtension.php');

class CriteoPluginSettingsUI {

    const TAB_NAME = "woocommerce-settings-tab-criteo";

    /**
     * Bootstraps the class and hooks required actions & filters.
     *
     */
    public static function init() {
        add_filter('woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50);
        add_action('woocommerce_settings_tabs_settings_criteo', __CLASS__ . '::settings_tab');
        add_action('woocommerce_update_options_settings_criteo', __CLASS__ . '::update_settings');
        add_action('woocommerce_update_option_password_with_generate', 'WooCommerceExtension::updatePasswordWithGenerate');
    }

    /**
     * Add a new settings tab to the WooCommerce settings tabs array.
     *
     * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
     * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
     */
    public static function add_settings_tab($settings_tabs) {
        $settings_tabs['settings_criteo'] = __('Criteo', 'criteo');
        return $settings_tabs;
    }

    /**
     * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
     *
     * @uses woocommerce_admin_fields()
     * @uses self::get_settings()
     */
    public static function settings_tab() {
        woocommerce_admin_fields(self::get_settings());
    }

    /**
     * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
     *
     * @uses woocommerce_update_options()
     * @uses self::get_settings()
     */
    public static function update_settings() {
        woocommerce_update_options(self::get_settings());
    }

    /**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
     *
     * @return array Array of settings for @see woocommerce_admin_fields() function.
     */
    public static function get_settings() {
        $catalogLink = get_feed_link(CriteoPluginSettings::PRODUCT_FEED_NAME);
        $catalogLinkWithCredentials = self::addUserAndPasswordToUrl($catalogLink);
        $testedWooCommerce = CriteoPluginSettings::isWooCommerceVersionTestedWithPlugin();
        $testedWordPress = CriteoPluginSettings::isWordPressVersionTestedWithPlugin();

        $settings = array(
            'wc_criteo_section_title' => array(
                'name' => __('Settings', 'criteo'),
                'type' => 'title',
                'desc' => ''
            ),
            'wc_criteo_partner_id' => array(
                'name' => __('Enter your Partner ID', 'criteo'),
                'type' => 'text',
                'desc' => __('Please contact Criteo commercial contact if Partner ID is not provided.', 'criteo'),
                'desc_tip' => true,
                'id' => CriteoPluginSettings::PARTNER_ID_PROPERTY
            ),
            'wc_criteo_use_sku_for_product_id' => array(
                'name' => __('Use SKU for Product ID', 'criteo'),
                'type' => 'checkbox',
                'desc' => __('If you are enabling this option please make sure your are sending SKUs in Criteo OneTag for your products.', 'criteo'),
                'desc_tip' => false,
                'id' => CriteoPluginSettings::USE_SKU_FOR_PRODUCT_ID_PROPERTY
            ),
            'wc_criteo_subsection_1_end' => array(
                'type' => 'horizontal_line'
            ),
            'wc_criteo_subsection_2_title' => array(
                'type' => 'subsection_title',
                'title' => __('Product feed credentials', 'criteo')
            ),
            'wc_criteo_username' => array(
                'name' => __('Login', 'criteo'),
                'type' => 'text',
                'desc' => __('This is used by Criteo for retrieving product feed.', 'criteo'),
                'desc_tip' => false,
                'custom_attributes' => array('readonly' => 'true'),
                'id' => CriteoPluginSettings::USERNAME_PROPERTY
            ),
            'wc_criteo_password' => array(
                'name' => __('Password', 'criteo'),
                'type' => 'text',
                'desc' => __('This is used by Criteo for retrieving product feed.', 'criteo'),
                'desc_tip' => false,
                'custom_attributes' => array('readonly' => 'true'),
                'id' => CriteoPluginSettings::PASSWORD_PROPERTY
            ),
            'wc_criteo_subsection_2_end' => array(
                'type' => 'horizontal_line'
            ),
            'wc_criteo_catalog_link' => array(
                'name' => __('Sample catalog feed', 'criteo'),
                'type' => 'link',
                'desc' => __('',
                    self::TAB_NAME),
                'label' => $catalogLink,
                'link' => $catalogLinkWithCredentials
            ),
            'wc_criteo_wordpress' => array(
                'name' => __('Wordpress tested versions (major releases):', 'criteo'),
                'type' => 'version_display',
                'tool_version' => CriteoPluginSettings::getWordPressMajorVersion(),
                'compatible' => $testedWordPress,
                'label_value' => CriteoPluginSettings::MIN_WORDPRESS_VERSION . ' - ' . CriteoPluginSettings::TESTED_UP_TO_WORDPRESS_MAJOR_VERSION,
                'desc' => __('', 'criteo')
            ),
            'wc_criteo_woocommerce' => array(
                'name' => __('WooCommerce tested versions (major releases):', 'criteo'),
                'type' => 'version_display',
                'tool_version' => CriteoPluginSettings::getWooCommerceMajorVersion(),
                'compatible' => $testedWooCommerce,
                'label_value' => CriteoPluginSettings::MIN_WOOCOMERCE_VERSION . ' - ' . CriteoPluginSettings::TESTED_UP_TO_WOOCOMERCE_MAJOR_VERSION,
                'desc' => __('', 'criteo')
            ),
            'wc_criteo_section_end' => array(
                'type' => 'sectionend'
            )
        );

        return apply_filters(CriteoPluginSettings::CRITEO_SETTINGS_PREFIX.'_settings', $settings);
    }

    public static function addUserAndPasswordToUrl($url) {
        $userAndPassword = base64_encode(CriteoPluginSettings::getUser().':'.CriteoPluginSettings::getPassword());
        $userAndPassword = urlencode($userAndPassword);
        return $url.'?'.CriteoPluginSettings::TOKEN_URL_PARAM.'='.$userAndPassword;
    }
}

CriteoPluginSettingsUI::init();
