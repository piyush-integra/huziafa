<?php
/**
 * This class handles activation actions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // exit if call directly
}

final class EnjoyInstagram_Activation {

	/**
	 * Construct
	 *
	 * @return void
	 */
	public function __construct() {
		$this->add_default_settings();
	}

	/**
	 * Add default plugin settings
	 *
	 * @return void
	 * @since 9.0.0
	 */
	public function add_default_settings() {

		delete_option( 'enjoyinstagram_sync_times' );

		$plugin_options = array();
		if ( file_exists( ENJOYINSTAGRAM_DIR . 'plugin-options/options.php' ) ) {
			$plugin_options = include( ENJOYINSTAGRAM_DIR . 'plugin-options/options.php' );
		}

		if ( empty( $plugin_options ) ) {
			return;
		}

		foreach ( $plugin_options as $options ) {
			foreach ( $options as $option_id => $default_value ) {
				add_option( $option_id, $default_value );
			}
		}
	}
}

new EnjoyInstagram_Activation();