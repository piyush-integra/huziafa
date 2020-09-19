<?php

defined('ABSPATH') or die('Forbidden');

require_once(dirname(__FILE__) . '/WordPressPlugin.php');
require_once(dirname(__FILE__) . '/WordPressPlatformSettings.php');
require_once(dirname(__FILE__) . '/../settings/CriteoPluginSettings.php');

class WordPressSettingsProvider
{

    public static function getInfoSettings()
    {
        $infoSettings = [];
        $infoSettings['plugin_version'] = CriteoPluginSettings::getPluginVersion();
        $infoSettings['woocommerce_version'] = WC()->version;
        $infoSettings['wordpress_version'] = get_bloginfo('version');
        $infoSettings['partner_id'] = CriteoPluginSettings::getPartnerID();
        $infoSettings['moab_id'] = CriteoPluginSettings::getMoabId();
        return $infoSettings;
    }

    public static function getPlatformSettings()
    {
        $wordPressPlugins = self::getWordPressPlugins();
        $settings = self::getWordPressSettings();
        $environmentSettings = self::getEnvironmentSettings();
        $settings = array_merge($settings, $environmentSettings);

        $wordPressPlatformSettings = new WordPressPlatformSettings($wordPressPlugins, $settings);
        return $wordPressPlatformSettings;
    }

    private static function getWordPressPlugins()
    {
        // Check if get_plugins() function exists. This is required on the front end of the
        // site, since it is in a file that is normally only loaded in the admin.
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $wordPressPlugins = [];

        $all_plugins = get_plugins();
        foreach ($all_plugins as $plugin_file => $plugin) {
            $active = is_plugin_active($plugin_file);
            $wordPressPlugin = new WordPressPlugin($plugin['Name'], $plugin['PluginURI'], $plugin['Version'],
                $plugin['Description'], $plugin['Author'], $plugin['AuthorURI'], $plugin['DomainPath'],
                $plugin['Network'], $plugin['Title'], $plugin['AuthorName'], $active);
            array_push($wordPressPlugins, $wordPressPlugin);
        }
        return $wordPressPlugins;
    }

    private static function getWordPressSettings()
    {
        $wordPressSettings = [];
        $allOptions = wp_load_alloptions();
        foreach ($allOptions as $key => $value) {
            //we expose criteo plugin settings & a few predefined values
            if (self::isCriteoPluginOption($key) ||
                self::isOptionThatCanBeExposed($key)
            ) {
                $wordPressSettings[$key] = $value;
            }
        }
        return $wordPressSettings;
    }

    private static function isCriteoPluginOption($key)
    {
        return 0 === strpos($key, CriteoPluginSettings::CRITEO_SETTINGS_PREFIX) &&
            $key !== CriteoPluginSettings::USERNAME_PROPERTY &&
            $key != CriteoPluginSettings::PASSWORD_PROPERTY;
    }

    private static function isOptionThatCanBeExposed($key)
    {
        return in_array($key, CriteoPluginSettings::$SETTINGS_TO_BE_EXPOSED);
    }


    private static function getEnvironmentSettings()
    {
        $environmentSettings = [];
        $environmentSettings[CriteoPluginSettings::DEBUG_MODE_PROPERTY] = CriteoPluginDefaultConfig::DEV_MODE;
        $environmentSettings['wordpress_home_url'] = home_url();
        $environmentSettings['wordpress_site_url'] = site_url();
        $environmentSettings['woocommerce_version'] = WC()->version;
        $environmentSettings['wordpress_version'] = get_bloginfo('version');
        $environmentSettings['wordpress_multisite'] = is_multisite();
        $environmentSettings['wordpress_memory_limit'] = WP_MEMORY_LIMIT;
        $environmentSettings['wordpress_debug_mode'] = defined('WP_DEBUG') && WP_DEBUG;
        if (defined('WPLANG') && WPLANG) {
            $environmentSettings['wordpress_language'] = WPLANG;
        }
        $environmentSettings['server_info'] = $_SERVER['SERVER_SOFTWARE'];
        if (function_exists('phpversion')) {
            $environmentSettings['php_version'] = phpversion();
        }

        global $wpdb;
        $environmentSettings['mysql_version'] = $wpdb->db_version();

        $environmentSettings['default_timezone'] = date_default_timezone_get();
        $active_theme = wp_get_theme();
        $environmentSettings['wordpress_theme_name'] = $active_theme->Name;
        $environmentSettings['wordpress_theme_version'] = $active_theme->Version;
        $environmentSettings['wordpress_theme_author'] = $active_theme->Author;
        $environmentSettings['wordpress_theme_author_uri'] = $active_theme->{'Author URI'};
        $environmentSettings['wordpress_theme_template'] = $active_theme->Template;
        $environmentSettings['wordpress_theme_status'] = $active_theme->Status;

        $locale = localeconv();
        $environmentSettings['decimal_point'] = $locale['decimal_point'];
        $environmentSettings['thousands_sep'] = $locale['thousands_sep'];
        $environmentSettings['https_enabled'] = 'https' === substr(get_permalink(wc_get_page_id('shop')), 0, 5);

        $products = wp_count_posts('product');
        $productVariants = wp_count_posts('product_variation');

        $totalProducts = $products->publish + $productVariants->publish;
        $environmentSettings['woocommerce_total_number_of_published_products'] = $totalProducts;

        if (defined('WC_LOG_DIR')) {
            $environmentSettings['woocommerce_log_path'] = WC_LOG_DIR;
        }else{
            $environmentSettings['woocommerce_log_path'] = 'NOT FOUND';
        }

        $phpErrorLogPath = ini_get('error_log');
        $phpErrorLogPath = isset($phpErrorLogPath) && strlen(trim($phpErrorLogPath)) > 0 ? $phpErrorLogPath : "DEFAULT SYSTEM PATH";
        $environmentSettings['php_error_log_path'] = $phpErrorLogPath;

        return $environmentSettings;
    }
}