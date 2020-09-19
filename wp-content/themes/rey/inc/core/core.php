<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme main hooks.
 */
require_once REY_THEME_DIR . '/inc/core/hooks.php';

/**
 * Theme Functions.
 */
require_once REY_THEME_DIR . '/inc/core/functions.php';

/**
 * Theme Admin.
 */
require_once REY_THEME_DIR . '/inc/core/api.php';
require_once REY_THEME_DIR . '/inc/core/admin.php';
require_once REY_THEME_DIR . '/inc/core/dashboard.php';
require_once REY_THEME_DIR . '/inc/core/wizard.php';
require_once REY_THEME_DIR . '/inc/core/plugins.php';
require_once REY_THEME_DIR . '/inc/core/updates.php';

/**
 * Theme tags functions
 * Used to create, alter or remove markup.
 */
require_once REY_THEME_DIR . '/inc/core/tags.php';
