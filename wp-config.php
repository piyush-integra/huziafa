<?php

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'shop_huzaifa_staging');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'Huza!fa!234');

/** MySQL hostname */
define('DB_HOST', 'huziafa-stg-rds-instance-1.clpfcweu1hm5.ap-southeast-1.rds.amazonaws.com');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'c~:;,]|=}H9k`c):rJFpI:g5U%G+TR=}0_;%uaN3-ooRp2a*8kNI,J%5{>QISI*M');
define('SECURE_AUTH_KEY',  'OH$&zDBzTcI$!{0UC8NA4tXVK<81`@;NB6DJ3G0+4|D[xNcU&LV.g)Vi]HhA?.1-');
define('LOGGED_IN_KEY',    '>XLhgrG4ymsp5P1:fx3jm~@;>ob9lLl8vADVj }GxbJciAkNWUkYH)uhKBHf,z3w');
define('NONCE_KEY',        '2kDk7XnQfU3`yiSyUI>5d4*+|UT|dkPbLPewJ84b^s;Aj{j&YwOkN)f)n4w]z%I8');
define('AUTH_SALT',        ' k8@dkeB2S?~T)SY=74jH*.ZufSyU;b4 g={?vuy~^:-s1:|xS1`&eombem}JO$k');
define('SECURE_AUTH_SALT', 'v0hL|giR?2?{~F,|<P&,}1SOmu(|a,:JBC!f`(Lz6yyI4+96y_;%[#%dHAmaT+k^');
define('LOGGED_IN_SALT',   '+Vj<yJn{V0g3!+*.7{*z.9[=|&4Xe|spO=V?$a^pfkfy/cygX>yir@Kt@alk2,va');
define('NONCE_SALT',       's.@*=D)o*,#SnNuOkM]/I=WI7Lkm3cxWMj;|I}%jm1-Er $p:>t/r+i#DrC,>la2');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'hzf_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
