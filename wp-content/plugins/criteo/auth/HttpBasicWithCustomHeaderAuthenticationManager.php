<?php

defined('ABSPATH') or die('Forbidden');

require_once(dirname(__FILE__) . '/../settings/CriteoPluginSettings.php');

class HttpBasicWithCustomHeaderAuthenticationManager
{
    const TOKEN_HEADER = 'HTTP_X_CRITEO_TOKEN';

    public static function checkModuleEnabledAndAuthentication()
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');

        if (!is_plugin_active(CriteoPluginSettings::PLUGIN_FILE)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized');
            header('Plugin not installed');
            return false;
        }

        if (!self::hasAuthenticationHeader() && !self::hasAuthenticationUrlParam()) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized');
            header('X-Criteo-Token: Criteo Token is missing');
            return false;
        }

        if (!self::checkAuthentication()) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized');
            return false;
        }
        return true;
    }

    private static function hasAuthenticationHeader()
    {
        return isset($_SERVER[self::TOKEN_HEADER]) && $_SERVER[self::TOKEN_HEADER] !== '';
    }

    private static function hasAuthenticationUrlParam()
    {
        return isset($_GET[CriteoPluginSettings::TOKEN_URL_PARAM]) && $_GET[CriteoPluginSettings::TOKEN_URL_PARAM] !== '';
    }

    private static function checkAuthentication()
    {
        $data = base64_decode(self::getHeaderOrUrlParam());
        $userAndPassword = explode(":", $data, 2);

        return $userAndPassword[0] === CriteoPluginSettings::getUser()
            && $userAndPassword[1] === CriteoPluginSettings::getPassword();
    }

    private static function getHeaderOrUrlParam() {
        if (self::hasAuthenticationHeader()) {
            return $_SERVER[self::TOKEN_HEADER];
        } else {
            return $_GET[CriteoPluginSettings::TOKEN_URL_PARAM];
        }
    }

}