<?php

defined('ABSPATH') or die('Forbidden');

require_once(dirname(__FILE__) . '/CriteoPluginSettings.php');
require_once(dirname(__FILE__) . '/CriteoPluginDefaultConfig.php');

class CriteoPluginSettingsManager
{

    public static function loadDefaultSettings()
    {
        criteoLogAuditData('Activate hook for Criteo plugin called.');
        criteoLogAuditData('Loading default settings into database.');
        self::setOptionIfNotSet(CriteoPluginSettings::PARTNER_ID_PROPERTY, CriteoPluginDefaultConfig::PARTNER_ID);
        self::setOptionIfNotSet(CriteoPluginSettings::MOAB_ID_PROPERTY, CriteoPluginDefaultConfig::MOAB_ID);
        self::setOptionIfNotSet(CriteoPluginSettings::USERNAME_PROPERTY, CriteoPluginDefaultConfig::USERNAME);
        self::setOptionIfNotSet(CriteoPluginSettings::PASSWORD_PROPERTY, CriteoPluginDefaultConfig::PASSWORD);
        self::setOptionIfNotSet(CriteoPluginSettings::USE_SKU_FOR_PRODUCT_ID_PROPERTY, CriteoPluginSettings::DEFAULT_USE_SKU_FOR_PRODUCT_ID);
        self::setOptionIfNotSet(CriteoPluginSettings::FEED_QUERY_LIMIT_PROPERTY, CriteoPluginSettings::DEFAULT_PRODUCTS_PER_FEED_PAGE);
        criteoLogAuditData('Finished loading default settings into database.');
    }

    private static function setOptionIfNotSet($option, $defaultValue)
    {
        //Set the option only if it's not set so if the plugin is activate and then deactivated won't reset the settings.
        if (!get_option($option)) {
            criteoLogData('Setting ' . $option . ' to default value: ' . $defaultValue);
            update_option($option, $defaultValue);
        } else {
            criteoLogData($option . ' not set because it has a default value. ');
        }
    }

    public static function cleanupSettings()
    {
        criteoLogAuditData('Uninstall hook for Criteo plugin called.');
        criteoLogAuditData('Deleting settings from database.');
        delete_option(CriteoPluginSettings::PARTNER_ID_PROPERTY);
        delete_option(CriteoPluginSettings::MOAB_ID_PROPERTY);
        delete_option(CriteoPluginSettings::USERNAME_PROPERTY);
        delete_option(CriteoPluginSettings::PASSWORD_PROPERTY);
        delete_option(CriteoPluginSettings::USE_SKU_FOR_PRODUCT_ID_PROPERTY);
        delete_option(CriteoPluginSettings::FEED_QUERY_LIMIT_PROPERTY);
        criteoLogAuditData('Finished deleting settings from database.');
    }

}