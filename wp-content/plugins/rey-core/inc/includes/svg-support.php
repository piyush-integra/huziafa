<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists('\\IconPressNS\\Helpers\\Svg_support') && !class_exists('ReyCore_SvgSupport') ):

	/**
	 * Class Svg_support
	 *
	 * Base class for Rest API requests
	 *
	 * @package IconPressNS\Helpers
	 */
	class ReyCore_SvgSupport
	{

		/**
		 * Holds the reference to the instance of this class
		 * @var Base
		 */
		private static $_instance = null;

		/**
		 * Base constructor.
		 */
		private function __construct()
		{
			add_action( 'upload_mimes', [ $this, 'addSvgFiletype' ]);
			add_filter( 'image_send_to_editor', [ $this, 'fixSvgImageSizeAttributes' ], 10 );
			add_action( 'admin_head', [ $this, 'fixSvgDisplayThumbs' ] );
			add_filter( 'wp_prepare_attachment_for_js',  [ $this, 'fixSvgDisplayThumbs__AddMedia' ], 10 );
		}

		/**
		 * Retrieve the reference to the instance of this class
		 * @return Base
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}


		/**
		 * Add SVG as supported filetype
		 */
		function addSvgFiletype($file_types){
			$file_types['svg'] = 'image/svg+xml';
			return $file_types;
		}

		// Fix image sizes attributes when adding an SVG image
		// into the editor
		function fixSvgImageSizeAttributes( $html, $alt='' ) {
			// get class
			preg_match( '/class="([\s\S]*?)"/', $html, $matches );
			$class = isset($matches[1]) ? $matches[1] : '';

			// check if the src file has .svg extension
			if ( strpos( $html, '.svg' ) !== FALSE ) {
				// strip html for svg files
				$html = preg_replace( '/(width|height|title|alt|class)=".*"\s/', 'class="' . $class . '"', $html );;
			} else {
				// leave html intact for non-svg
				$html = $html;
			}
			return $html;
		}

		// Fix svg thumbnails in media library
		function fixSvgDisplayThumbs(){
			echo '<style type="text/css" media="screen">table.media .column-title .media-icon img[src$=".svg"] {width:auto !important; height:auto !important;}</style>';
		}

		// Fix svg thumbnails in Add Media panel
		function fixSvgDisplayThumbs__AddMedia( $response ) {
			if ( $response['mime'] == 'image/svg+xml' && empty( $response['sizes'] ) ) {
				$response['sizes'] = [
					'full' => [
						'url' => $response['url']
					]
				];
			}
			return $response;
		}


	}

	ReyCore_SvgSupport::getInstance();

endif;
