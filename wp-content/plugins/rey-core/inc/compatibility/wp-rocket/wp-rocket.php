<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( defined('WP_ROCKET_VERSION') && !class_exists('ReyCore_Compatibility__WPRocket') ):

	class ReyCore_Compatibility__WPRocket
	{
		public function __construct()
		{
			add_filter('rocket_cdn_reject_files', [$this, 'exclude_files_cdn'], 20, 2);
		}

		public function exclude_files_cdn($files) {

			// (.*).svg

			if( function_exists('rey__svg_sprite_path') ){
				$files[] = rey__svg_sprite_path();
			}

			if( function_exists('reycore__icons_sprite_path') ){
				$files[] = reycore__icons_sprite_path();
			}

			if( function_exists('reycore__social_icons_sprite_path') ){
				$files[] = reycore__social_icons_sprite_path();
			}

			return $files;
		}

	}

	new ReyCore_Compatibility__WPRocket;
endif;
