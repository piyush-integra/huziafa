<?php

defined('ABSPATH') or die('Forbidden');

require_once(dirname(__FILE__) . '/WordPressSettingsProvider.php');
require_once(dirname(__FILE__) . '/../auth/HttpBasicWithCustomHeaderAuthenticationManager.php');

class DebugFeedGenerator
{

    public static function getDebugFeed()
    {
        if (!HttpBasicWithCustomHeaderAuthenticationManager::checkModuleEnabledAndAuthentication()) {
            return;
        }

        criteoLogData('Retrieving debug feed data');

        header('Content-type: application/json; charset=utf-8');
        echo(json_encode(WordPressSettingsProvider::getPlatformSettings(), JSON_PRETTY_PRINT));
    }

}