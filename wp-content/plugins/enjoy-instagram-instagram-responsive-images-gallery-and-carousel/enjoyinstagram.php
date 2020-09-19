<?php
/*
Plugin Name: Enjoy Plugin for Instagram
Description: Instagram Responsive Images Gallery and Carousel, works with Shortcodes and Widgets.
Version: 5.0.7
Author: Mediabeta Srl
Text Domain: enjoy-instagram-instagram-responsive-images-gallery-and-carousel
Author URI: http://www.mediabeta.com/team/
*/

! defined( 'ENJOYINSTAGRAM_VERSION' ) && define( 'ENJOYINSTAGRAM_VERSION', '5.0.7' );
! defined( 'ENJOYINSTAGRAM_FILE' ) && define( 'ENJOYINSTAGRAM_FILE', __FILE__ );
! defined( 'ENJOYINSTAGRAM_URL' ) && define( 'ENJOYINSTAGRAM_URL', plugin_dir_url( __FILE__ ) );
! defined( 'ENJOYINSTAGRAM_DIR' ) && define( 'ENJOYINSTAGRAM_DIR', plugin_dir_path( __FILE__ ) );
! defined( 'ENJOYINSTAGRAM_ASSETS_URL' ) && define( 'ENJOYINSTAGRAM_ASSETS_URL', ENJOYINSTAGRAM_URL . 'assets' );
! defined( 'ENJOYINSTAGRAM_TEMPLATE_PATH' ) && define( 'ENJOYINSTAGRAM_TEMPLATE_PATH',
	ENJOYINSTAGRAM_DIR . 'templates' );
! defined( 'ENJOYINSTAGRAM_FB_APP_ID' ) && define( 'ENJOYINSTAGRAM_FB_APP_ID', '773612959700549' );
! defined( 'ENJOYINSTAGRAM_APP_ID' ) && define( 'ENJOYINSTAGRAM_APP_ID', '1367115243477960' );
! defined( 'ENJOYINSTAGRAM_GRAPH_API_REDIRECT' ) && define( 'ENJOYINSTAGRAM_GRAPH_API_REDIRECT',
	'https://www.mediabetaprojects.com/enjoy-instagram-api/graph-api-redirect.php' );
! defined( 'ENJOYINSTAGRAM_BASIC_DISPLAY_API_REDIRECT' ) && define( 'ENJOYINSTAGRAM_BASIC_DISPLAY_API_REDIRECT',
	'https://www.mediabetaprojects.com/enjoy-instagram-api/basic-display-redirect.php' );


function enjoyinstagram_require_activation_class() {
	require_once( 'includes/class.enjoyinstagram-activation.php' );
}

register_activation_hook( __FILE__, 'enjoyinstagram_require_activation_class' );

add_action( 'plugins_loaded', 'enjoyinstagram_init_plugin' );
function enjoyinstagram_init_plugin() {

	load_plugin_textdomain( 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel', false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	require_once( 'includes/functions.enjoyinstagram.php' );
	require_once( 'includes/class.enjoyinstagram.php' );
	EnjoyInstagram();
}
