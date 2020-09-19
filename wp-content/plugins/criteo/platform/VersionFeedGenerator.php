<?php

defined('ABSPATH') or die('Forbidden');

require_once(dirname(__FILE__) . '/../settings/CriteoPluginSettings.php');

class VersionFeedGenerator
{
    public static function getVersion() {
        criteoLogData('Retrieving version feed data');

        header('Content-type: text/html; charset=utf-8');
        echo(CriteoPluginSettings::getPluginVersion());
    }

}