<?php

defined('ABSPATH') or die('Forbidden');

require_once(dirname(__FILE__) . '/../settings/CriteoPluginSettings.php');


/**
 * Manager for HTTP Basic Authentication.
 * Implementation taken and adapted from: http://php.net/manual/en/features.http-auth.php
 *
 * As found out in production for Magento plugin, if the PHP process is executed external
 * to the Apache server (and not as an Apache module) then there is high chance that the
 * Authentication header won't be passed to the Wordpress application hence making this implementation unusable.
 *
 */
class HttpBasicAuthenticationManager
{
    public static function checkModuleEnabledAndAuthentication()
    {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        if(!is_plugin_active(CriteoPluginSettings::PLUGIN_FILE)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized');
            return false;
        }

        if (!self::hasAuthenticationHeader()) {
            header('WWW-Authenticate: Basic realm="Criteo Realm"');
            header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized');
            return false;
        }

        if (!self::checkAuthentication()) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized');
            return false;
        }

        return true;
    }

    private static function checkAuthentication()
    {
        $username = CriteoPluginSettings::getUser();
        if (strcasecmp($_SERVER['PHP_AUTH_USER'], $username) != 0) {
            return false;
        }

        $password = CriteoPluginSettings::getPassword();
        if (strcmp($_SERVER['PHP_AUTH_PW'], $password) != 0) {
            return false;
        }

        return true;
    }

    private static function hasAuthenticationHeader()
    {
        return isset($_SERVER['PHP_AUTH_USER']);
    }

}