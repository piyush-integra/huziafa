<?php
defined('ABSPATH') or die('Forbidden');

/**
 * The model class for WordPress platform settings.
 */
class WordPressPlatformSettings {

    public $wordPressPlugins;
    public $wordPressSettings;

    function __construct($wordPressPlugins, $wordPressSettings)
    {
        $this->wordPressPlugins = $wordPressPlugins;
        $this->wordPressSettings = $wordPressSettings;

    }

}