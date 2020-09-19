<?php

defined('ABSPATH') or die('Forbidden');

require_once(dirname(__FILE__) . '/../settings/CriteoPluginSettings.php');

/**
 * Manager for HTTP Digest Authentication.
 * Implementation taken and adapted from: http://php.net/manual/en/features.http-auth.php
 *
 * As found out in production for Magento plugin, if the PHP process is executed external
 * to the Apache server (and not as an Apache module) then there is high chance that the
 * Authentication header won't be passed to the Wordpress application hence making this implementation unusable.
 *
 */
class HttpDigestAuthenticationManager
{

    public static function checkModuleEnabledAndAuthentication()
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');

        if (!is_plugin_active(CriteoPluginSettings::PLUGIN_FILE)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized');
            return false;
        }

        $realm = 'Criteo Realm';

        if (!self::hasAuthenticationHeader()) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized');
            header('WWW-Authenticate: Digest realm="' . $realm .
                '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($realm) . '"');
            return false;
        }

        if(!self::checkAuthentication($realm)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized');
            return false;
        }

        return true;
    }

    private static function hasAuthenticationHeader()
    {
        return isset($_SERVER['PHP_AUTH_DIGEST']);
    }

    private static function checkAuthentication($realm)
    {
        $data = self::httpDigestParse($_SERVER['PHP_AUTH_DIGEST']);
        // analyze the PHP_AUTH_DIGEST variable
        if (!$data || $data['username'] != CriteoPluginSettings::getUser()) {
            return false;
        }

        // generate the valid response
        $password = CriteoPluginSettings::getPassword();
        $A1 = md5($data['username'] . ':' . $realm . ':' . $password);
        $A2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $data['uri']);
        $valid_response = md5($A1 . ':' . $data['nonce'] . ':' . $data['nc'] . ':' . $data['cnonce'] . ':' . $data['qop'] . ':' . $A2);

        if ($data['response'] != $valid_response) {
            return false;
        }

        return true;
    }

    private static function httpDigestParse($txt)
    {
        // protect against missing data
        $needed_parts = array('nonce' => 1, 'nc' => 1, 'cnonce' => 1,
            'qop' => 1, 'username' => 1, 'uri' => 1, 'response' => 1);
        $data = array();
        $keys = implode('|', array_keys($needed_parts));

        preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $key = $m[1];
            $value = $m[3] ? $m[3] : $m[4];
            $value = str_replace('\\"', '', $value);

            $data[$key] = $value;
            unset($needed_parts[$m[1]]);
        }
        return $needed_parts ? false : $data;
    }

}