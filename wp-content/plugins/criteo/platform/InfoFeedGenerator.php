<?php

defined('ABSPATH') or die('Forbidden');

require_once(dirname(__FILE__) . '/../settings/CriteoPluginSettings.php');
require_once(dirname(__FILE__) . '/WordPressSettingsProvider.php');
require_once(dirname(__FILE__) . '/../auth/HttpBasicWithCustomHeaderAuthenticationManager.php');

class InfoFeedGenerator {

    public static function getInfoFeed() {
        if (!HttpBasicWithCustomHeaderAuthenticationManager::checkModuleEnabledAndAuthentication()) {
            return;
        }

        criteoLogData('Retrieving info feed data');

        header('Content-type: application/json; charset=utf-8');
        echo(json_encode(WordPressSettingsProvider::getInfoSettings(), JSON_PRETTY_PRINT));
    }

}