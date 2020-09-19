<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Adds the Webfont Loader to load fonts asyncronously.
 *
 * @package     Rey. Heavily inspired by Kirki
 * @author      Ari Stathopoulos (@aristath) | Marius Hogas
 * @copyright   Copyright (c) 2019, Ari Stathopoulos (@aristath)
 * @license    https://opensource.org/licenses/MIT
 * @since       1.0.0
 */

if(!class_exists('ReyCore_Webfonts_Embed')):
	/**
	 * Manages the way Google Fonts are enqueued.
	 */
	final class ReyCore_Webfonts_Embed {

		/**

		* 5. solution to clear cache (probabil la save options)
		*/

		/**
		 * Holds the reference to the instance of this class
		 * @var Base
		 */
		private static $_instance = null;

		/**
		 * Fonts to load.
		 *
		 * @access protected
		 * @since 1.0.0
		 * @var array
		 */
		protected $fonts_to_load = [];
		protected $google_fonts = [];
		protected $adobe_fonts = [];

		const GOOGLE_FONTS_TRANSIENT = 'rey_google_fonts';
		const ADOBE_FONTS_LIST_TRANSIENT = 'rey_adobe_fonts';
		const ADOBE_FONTS_CSS_TRANSIENT = 'rey_adobe_fonts_css';

		/**
		 * Extra symbol to differentiate google custom font is lists.
		 *
		 * @access public
		 * @since 1.0.0
		 * @var string
		 */
		const SYMBOL = '__';

		private function __construct()
		{
			add_action( 'init', [ $this, 'init' ], 9 );
			add_filter( 'rey/css_styles', [ $this, 'enqueue_css' ] );
			// add_action( 'wp_enqueue_scripts', [ $this, 'adobe_fonts_embed_css' ] );
			add_filter( 'wp_resource_hints', [ $this, 'resource_hints' ], 10, 2 );
			add_action( 'acf/save_post', [ $this, 'clear_fonts_transient_on_save' ], 20);
			add_filter( 'elementor/fonts/groups', [ $this, 'elementor_group' ] );
			add_filter( 'elementor/fonts/additional_fonts', [ $this, 'add_elementor_fonts' ] );
			add_filter( 'revslider_operations_getArrFontFamilys', [ $this, 'add_revolution_fonts' ] );
			add_filter( 'reycore/kirki_fields/field_type=typography', [ $this, 'add_font_choices' ] );
			add_action( 'admin_head', [ $this, 'add_revolution_fonts_css' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'add_revolution_enqueue_scripts' ] );
			add_action( 'elementor_pro/fonts_manager_loaded', [ $this, 'add_elementor_pro_custom_fonts' ] );
			add_action( 'admin_enqueue_scripts', [$this, 'add_gutenberg_fonts_css']);
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

		public function init(){


		}

		public function get_fonts(){

			if( ! is_admin() ){
				return [];
			}

			return array_merge( $this->get_google_fonts_list(), $this->get_adobe_fonts_list() );
		}

		function get_google_fonts_list(){

			if( ! empty($this->google_fonts) ){
				return $this->google_fonts;
			}

			$this->google_fonts = reycore__acf_get_field('preload_google_fonts', REY_CORE_THEME_NAME, []);

			// adds a special argument
			if( is_array($this->google_fonts) ){
				array_walk($this->google_fonts, function (&$value, $key){
					$value['type'] = 'google';
				});
			}

			return $this->google_fonts;
		}


		/**
		 * Add preconnect for Google Fonts.
		 *
		 * @access public
		 * @param array  $urls           URLs to print for resource hints.
		 * @param string $relation_type  The relation type the URLs are printed.
		 * @return array $urls           URLs to print for resource hints.
		 */
		public function resource_hints( $urls, $relation_type ) {

			if ( 'preconnect' === $relation_type ) {

				if( $this->get_google_fonts_list() ) {
					$urls[] = array(
						'href' => '//fonts.gstatic.com',
						'crossorigin',
					);
				}

				if( $this->get_adobe_fonts_list() ) {
					$urls[] = array(
						'href' => '//use.typekit.net',
						'crossorigin',
					);
				}
			}

			return $urls;
		}

		/**
		 * Prepare Google fonts URL
		 *
		 * @since 1.5.0
		 */
		private function get_google_fonts_url( $encode = false ) {

			$font_families = [];
			$font_subsets = [];

			if( $fonts = $this->get_google_fonts_list() ) {
				foreach( $fonts as $font ){

					$font_name = str_replace(' ', '+', $font['font_name']);
					$font_variants = implode(',', $font['font_variants']);
					if( $font['font_subsets'] ) {
						foreach($font['font_subsets'] as $subset) {
							$font_subsets[] = $subset;
						}
					}
					$font_families[] = $font_name . ':' . $font_variants;
				}
			}

			if( empty($font_families) ){
				return false;
			}

			$join_families = implode( '|', $font_families );

			$query_args = [
				'family' => $encode ? urlencode( $join_families ) : $join_families
			];
			if( !empty( $font_subsets ) ){
				$join_weights = implode(',', array_unique($font_subsets) );
				$query_args['subset'] = $encode ? urlencode( $join_weights ) : $join_weights;
			}

			$fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );

			return esc_url_raw($fonts_url);
		}

		private function get_google_fonts_css() {

			if( function_exists('rey__maybe_disable_obj_cache') ){
				rey__maybe_disable_obj_cache();
			}

			$contents = get_transient( self::GOOGLE_FONTS_TRANSIENT );

			if ( ! $contents ) {

				$url = $this->get_google_fonts_url();

				if( !$url ){
					return '';
				}

				// Get the contents of the remote URL.
				$contents = self::get_remote_url_contents(
					$url,
					array(
						'headers' => array(
							/**
							 * Set user-agent to firefox so that we get woff files.
							 * If we want woff2, use this instead: 'Mozilla/5.0 (X11; Linux i686; rv:64.0) Gecko/20100101 Firefox/64.0'
							 */
							'user-agent' => 'Mozilla/5.0 (X11; Linux i686; rv:21.0) Gecko/20100101 Firefox/21.0',
						),
					)
				);

				/**
				 * Allow filtering the font-display property.
				 */
				$font_display = 'swap';

				if ( $contents ) {

					// Add font-display:swap to improve rendering speed.
					$contents = str_replace( '@font-face {', '@font-face{', $contents );
					$contents = str_replace( '@font-face{', '@font-face{font-display:' . $font_display . ';', $contents );

					// Remove blank lines and extra spaces.
					$contents = str_replace(
						array( ': ', ';  ', '; ', '  ' ),
						array( ':', ';', ';', ' ' ),
						preg_replace( "/\r|\n/", '', $contents )
					);

					foreach( $this->get_google_fonts_list() as $font ){
						$contents = str_replace( "font-family:'". $font['font_name'] ."';", "font-family:'". $font['font_name'] . self::SYMBOL ."';", $contents );
					}

					// Set the transient for a week.
					set_transient( self::GOOGLE_FONTS_TRANSIENT, $contents, WEEK_IN_SECONDS );
				}
			}

			if ( $contents ) {
				/**
				 * Note to code reviewers:
				 *
				 * Though all output should be run through an escaping function, this is pure CSS
				 * and it is added on a call that has a PHP `header( 'Content-type: text/css' );`.
				 * No code, script or anything else can be executed from inside a stylesheet.
				 * For extra security we're using the wp_strip_all_tags() function here
				 * just to make sure there's no <script> tags in there or anything else.
				 */
				return wp_strip_all_tags( $contents ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
		}

		public function get_adobe_fonts_list(){

			if( ! empty($this->adobe_fonts) ){
				return $this->adobe_fonts;
			}

			$adobe_project_id = reycore__acf_get_field('adobe_fonts_project_id', REY_CORE_THEME_NAME);

			if( ! $adobe_project_id ) {
				return [];
			}

			if( function_exists('rey__maybe_disable_obj_cache') ){
				rey__maybe_disable_obj_cache();
			}

			$this->adobe_fonts = get_transient( self::ADOBE_FONTS_LIST_TRANSIENT );

			if ( ! $this->adobe_fonts ) {

				// Get the contents of the remote URL.
				$contents = self::get_remote_url_contents(
					sprintf('https://typekit.com/api/v1/json/kits/%s/published', $adobe_project_id),
					array(
						'timeout' => '30',
					)
				);

				if ( $contents ) {

					$data = json_decode( $contents, true );

					if( isset($data['kit']['families']) && $families = $data['kit']['families'] ){
						foreach ( $families as $i => $family ) {

							$this->adobe_fonts[$i] = [
								'font_name'     => $family['slug'],
								'font_variants' => [],
								'font_subsets'  => '',
								'family'        => str_replace( ' ', '-', $family['name'] ),
								'type'          => 'adobe'
							];

							foreach ( $family['variations'] as $variation ) {
								$variations = str_split( $variation );
								$weight = $variations[1] . '00';
								if ( ! in_array( $weight, $this->adobe_fonts[$i]['font_variants'] ) ) {
									$this->adobe_fonts[$i]['font_variants'][] = $weight;
								}
							}
						}

						// Set the transient for a week.
						set_transient( self::ADOBE_FONTS_LIST_TRANSIENT, $this->adobe_fonts, WEEK_IN_SECONDS );
					}
				}
			}

			return $this->adobe_fonts;
		}

		private function get_adobe_fonts_css() {

			if( function_exists('rey__maybe_disable_obj_cache') ){
				rey__maybe_disable_obj_cache();
			}

			$contents = get_transient( self::ADOBE_FONTS_CSS_TRANSIENT );

			if ( ! $contents ) {

				$url = $this->get_adobe_fonts_url();

				if( !$url ){
					return '';
				}

				// Get the contents of the remote URL.
				$contents = self::get_remote_url_contents( $url, [
						'headers' => [
							'user-agent' => 'Mozilla/5.0 (X11; Linux i686; rv:21.0) Gecko/20100101 Firefox/21.0',
						],
					]
				);

				// remove everything before
				if( ! is_array($contents) ){
					$contents = strstr($contents, '@font-face');
				}

				if ( $contents ) {
					// Set the transient for a week.
					set_transient( self::ADOBE_FONTS_CSS_TRANSIENT, $contents, WEEK_IN_SECONDS );
				}
			}

			if ( $contents ) {
				/**
				 * Note to code reviewers:
				 *
				 * Though all output should be run through an escaping function, this is pure CSS
				 * and it is added on a call that has a PHP `header( 'Content-type: text/css' );`.
				 * No code, script or anything else can be executed from inside a stylesheet.
				 * For extra security we're using the wp_strip_all_tags() function here
				 * just to make sure there's no <script> tags in there or anything else.
				 */
				return wp_strip_all_tags( $contents ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
		}

		/**
		 * Enqueue Typekit CSS.
		 *
		 * @return void
		 */
		public function adobe_fonts_embed_css() {
			if ( $embed_url = $this->get_adobe_fonts_url() ) {
				wp_enqueue_style( 'rey-adobe-fonts', $embed_url, [] );
			}
		}

		/**
		 * Get the Adobe Fonts embed URL
		 *
		 * @since 1.0.0
		 */
		private function get_adobe_fonts_url() {

			if( $adobe_project_id = reycore__acf_get_field('adobe_fonts_project_id', REY_CORE_THEME_NAME, '') ) {
				return sprintf( 'https://use.typekit.net/%s.css', $adobe_project_id );
			}

			return false;
		}


		/**
		 * Get the css and push into Rey styles
		 *
		 * @since 1.0.0
		 */
		public function enqueue_css( $css = [] ) {

			if( is_array($css) ){
				$css[] = $this->get_google_fonts_css();
				$css[] = $this->get_adobe_fonts_css();
			}

			return $css;
		}

		/**
		 * Gets the remote URL contents.
		 *
		 * @since 1.0.0
		 * @param string $url  The URL we want to get.
		 * @param array  $args An array of arguments for the wp_remote_retrieve_body() function.
		 * @return string      The contents of the remote URL.
		 */
		public static function get_remote_url_contents( $url, $args = array() ) {
			$response = wp_remote_get( $url, $args );
			if ( is_wp_error( $response ) ) {
				return array();
			}
			$html = wp_remote_retrieve_body( $response );
			if ( is_wp_error( $html ) ) {
				return;
			}
			return $html;
		}

		public static function kirki_font_choices( $option = false ) {}

		function add_font_choices( $args ){

			if( isset($args['load_choices']) ){
				$args['choices'] = $this->customizer_font_choices( $args['load_choices'] === 'exclude' );
			}

			return $args;
		}

		/**
		 * Populate Kirki typography controls with custom fonts
		 *
		 * @since 1.0.0
		 */
		public function customizer_font_choices( $exclude_presets = false ) {

			$choices = [];

			if( $exclude_presets !== true )
			{
				$pff = $sff = '';

				if( ($primary_typo = get_theme_mod('typography_primary', [])) && isset($primary_typo['font-family']) ){
					$pff = "( {$primary_typo['font-family']} )";
				}
				$children[] = [
					'id' => 'var(--primary-ff)',
					'text' => sprintf(esc_html__('Primary Font %s', 'rey-core'), $pff)
				];

				if( ($secondary_typo = get_theme_mod('typography_secondary', [])) && isset($secondary_typo['font-family']) ){
					$sff = sprintf( "( %s )", $secondary_typo['font-family'] ? $secondary_typo['font-family'] : esc_html__('not selected', 'rey-core') );
				}
				$children[] = [
					'id' => 'var(--secondary-ff)',
					'text' => sprintf(esc_html__('Secondary Font %s', 'rey-core'), $sff)
				];

				$variants['var(--primary-ff)'] = [ '100', '200', '300', '400', '500', '600', '700', '800', '900'];
				$variants['var(--secondary-ff)'] = [ '100', '200', '300', '400', '500', '600', '700', '800', '900'];
			}

			if( $fonts = $this->get_fonts() ) {

				foreach( $fonts as $font ){

					$font_name = $font['font_name'];

					// add custom symbol
					if( isset($font['type']) && $font['type'] == 'google' ){
						$font_name = $font['font_name'] . self::SYMBOL;
					}

					$children[] = [
						'id' => $font_name,
						'text' => $font['font_name']
					];

					$variants[$font_name] = $font['font_variants'];
				}

				$choices = [
					'fonts' => [
						'families' => [
							'custom' => [
								'text'     => esc_html__('Rey Fonts (Preloaded)', 'rey-core'),
								'children' => $children,
							],
						],
						'variants' => $variants,
					],
				];
			}

			return apply_filters('reycore/webfonts/kirki_choices', $choices);
		}

		/**
		 * Refresh fonts cache on save options
		 *
		 * @since 1.0.0
		 */
		public function clear_fonts_transient_on_save( $post_id ) {
			if ($post_id === REY_CORE_THEME_NAME) {

				if( function_exists('rey__maybe_disable_obj_cache') ){
					rey__maybe_disable_obj_cache();
				}

				delete_transient(self::GOOGLE_FONTS_TRANSIENT);
				delete_transient(self::ADOBE_FONTS_LIST_TRANSIENT);
				delete_transient(self::ADOBE_FONTS_CSS_TRANSIENT);
			}
		}

		/**
		 * Add Custom Font group to elementor font list.
		 *
		 * Group name "Custom" is added as the first element in the array.
		 *
		 * @since  1.0.0
		 * @param  Array $font_groups default font groups in elementor.
		 * @return Array              Modified font groups with newly added font group.
		 */
		public function elementor_group( $font_groups ) {
			$new_group[ 'rey_font_group' ] = esc_html__('Rey Fonts (Preloaded)', 'rey-core');
			$font_groups                   = $new_group + $font_groups;

			return $font_groups;
		}



		/**
		 * Add Custom Fonts to the Elementor Page builder's font param.
		 *
		 * @since  1.0.0
		 */
		public function add_elementor_fonts( $fonts ) {

			// !TODO:  wait for https://github.com/elementor/elementor/issues/9415
			// int ref: https://github.com/hogash/rey/issues/161
			// $fonts[ 'var(--primary-ff)' ] = 'rey_font_group';
			// $fonts[ 'var(--secondary-ff)' ] = 'rey_font_group';

			$all_fonts = $this->get_fonts();

			if ( ! empty( $all_fonts ) ) {
				foreach ( $all_fonts as $font ) {

					$font_name = $font['font_name'];

					if( isset($font['type']) && $font['type'] == 'google' ){
						// add custom symbol
						$font_name = $font['font_name'] . self::SYMBOL;
					}

					$fonts[ $font_name ] = 'rey_font_group';
				}
			}

			return $fonts;
		}

		/**
		 * Add Rey's Fonts to Revolution Slider lists.
		 *
		 * @since  1.0.0
		 */
		public function add_revolution_fonts( $fonts ) {

			$all_fonts = $this->get_fonts();

			if ( ! empty( $all_fonts ) ) {
				foreach ( $all_fonts as $font ) {

					$font_to_add = [
						'type' => $font['type'],
						'version' => '',
						'variants' => $font['font_variants'],
						'category' => REY_CORE_THEME_NAME,
						'subsets' => $font['font_subsets'],
					];

					if( isset($font['type']) ){
						if( $font['type'] == 'google' ){
							// add custom symbol
							$font_to_add['label'] = $font['font_name'] . self::SYMBOL;
						}
						elseif( $font['type'] == 'adobe' ){
							$font_to_add['label'] = $font['font_name'];
						}
					}

					array_unshift($fonts, $font_to_add );

				}
			}

			return $fonts;
		}

		/**
		 * Add fonts CSS into Revolution Slider Editor
		 *
		 * @since 1.0.0
		 */
		public function add_revolution_fonts_css() {
			$screen = get_current_screen();

			if ( $screen && 'toplevel_page_revslider' === $screen->id ) {

				$custom_fonts_css = $this->enqueue_css();
				// Output cleanup
				$custom_fonts_css = implode(' ', $custom_fonts_css);
				$custom_fonts_css = str_replace(array("\r\n", "\r", "\n"), '', $custom_fonts_css); // remove newlines

				echo '<style type="text/css">' . $custom_fonts_css . '</style>';
			}
		}

		/**
		 * Enqueue styles in Revolution Slider Editor
		 *
		 * @since 1.0.0
		 */
		public function add_revolution_enqueue_scripts() {
			$screen = get_current_screen();

			if ( 'toplevel_page_revslider' === $screen->id ) {
				$this->adobe_fonts_embed_css();
			}
		}

		/**
		 * Add fonts CSS into Gutengberg Editor
		 *
		 * @since 1.0.0
		 */
		public function add_gutenberg_fonts_css() {

			wp_register_style( 'reycore-gutenberg-fonts-styles', false );
			wp_enqueue_style( 'reycore-gutenberg-fonts-styles' );

			$custom_fonts_css = $this->enqueue_css();

			// Output cleanup
			$custom_fonts_css = implode(' ', $custom_fonts_css);
			$custom_fonts_css = str_replace(array("\r\n", "\r", "\n"), '', $custom_fonts_css); // remove newlines

			// Add the style
			wp_add_inline_style( 'reycore-gutenberg-fonts-styles', $custom_fonts_css);
		}

		/**
		 * Adds compatibility with Elementor PRO's custom fonts
		 *
		 * @since 1.0.3
		 */
		function add_elementor_pro_custom_fonts( $font_manager ){

			if( $elpro_fonts = $font_manager->get_fonts() ){

				add_filter('reycore/webfonts/kirki_choices', function($choices) use ($elpro_fonts){

					if( !empty($choices) && isset( $choices['fonts']['families']['custom']['children'] ) ) {
						foreach ($elpro_fonts as $font => $font_data) {
							$choices['fonts']['families']['custom']['children'][] = [
								'id' => $font,
								'text' => $font
							];
							$choices['fonts']['variants'][$font] = [ '100', '200', '300', '400', '500', '600', '700', '800', '900'];
						}
					}

					return $choices;
				});

				add_action('wp_head', function() use ($elpro_fonts) {
					$print_style = '';
					foreach ($elpro_fonts as $key => $font) {
						$print_style .= $font['font_face'];
					}
					printf('<style>%s</style>', $print_style);
				});
			}

		}
	}

	function reyCoreWebfonts(){
		return ReyCore_Webfonts_Embed::getInstance();
	}

	reyCoreWebfonts();

endif;
