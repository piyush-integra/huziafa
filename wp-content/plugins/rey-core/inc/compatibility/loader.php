<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'REY_CORE_COMPATIBILITY_DIR', plugin_dir_path( __FILE__ ) );
define( 'REY_CORE_COMPATIBILITY_URI', plugin_dir_url( __FILE__ ) );

$dirs = array_filter( glob( REY_CORE_COMPATIBILITY_DIR . '/*'), 'is_dir' );

foreach($dirs as $dir){
	$name = basename($dir, '.php');
	if( ( $file = trailingslashit($dir) . $name . '.php' ) && is_readable($file) ){
		require $file;
	}
}
