<?php

defined('ABSPATH') or die('Forbidden');

/*
 * Initial Plugin Settings must be in a .php file (and not in a .properties/.ini file) for security reasons
 * as php is interpreted and the output is empty when calling directly the file.
 * A file with other extension could be called directly (depending on security setup) and would be displayed as is.
 */
class CriteoPluginDefaultConfig
{
    const PARTNER_ID = '75824';
    const MOAB_ID = '73419';
    const USERNAME = 'criteo';
    const PASSWORD = 'zYmCrg6ngt';
    const DEV_MODE = false;

}