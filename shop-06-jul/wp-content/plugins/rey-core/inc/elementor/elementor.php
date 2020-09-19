<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

if(!class_exists('ReyCoreElementor')):
	/**
	 * Elementor integration
	 */
	final class ReyCoreElementor
	{
		/**
		 * Holds the reference to the instance of this class
		 * @var ReyCoreElementor
		 */
		private static $_instance = null;

		/**
		 * Holds the widgets that are loaded
		 */
		public $widgets = [];

		/**
		 * Holds the absolute path to widgets folder
		 */
		public $widgets_dir = '';

		/**
		 * Holds the local elementor widgets path
		 */
		public $widgets_folder = 'inc/elementor/widgets/';

		const REY_TEMPLATE = 'template-builder.php';
		/**
		 * ReyCoreElementor constructor.
		 */
		private function __construct()
		{
			// Exit if Elementor is not active
			if ( ! reycore__theme_active() || ! did_action( 'elementor/loaded' ) ) {
				return;
			}

			$this->includes();
			$this->get_widgets();
			$this->init_hooks();
		}

		public function includes(){
			require_once REY_CORE_DIR . 'inc/elementor/widgets-manager.php';
			require_once REY_CORE_DIR . 'inc/elementor/template-library/library.php';
		}

		public function init_hooks(){
			add_action( 'elementor/controls/controls_registered', [ $this, 'register_controls' ] );
			add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );
			add_action( 'elementor/elements/categories_registered', [ $this, 'add_elementor_widget_categories'] );
			add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'editor_scripts'] );
			add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'enqueue_frontend_scripts'] );
			add_action( 'elementor/frontend/after_register_styles', [ $this, 'register_frontend_styles'] );
			add_action( 'elementor/frontend/after_register_styles', [ $this, 'register_frontend_scripts'] );
			add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'enqueue_frontend_styles'] );
			add_action( 'elementor/editor/after_save', [ $this, 'elementor_to_acf'], 10);
			if ( ! empty( $_REQUEST['action'] ) && 'elementor' === $_REQUEST['action'] && is_admin() ) {
				add_action( 'init', [ $this, 'register_wc_hooks' ], 5 /* Priority = 5, in order to allow plugins remove/add their wc hooks on init. */);
			}
			add_action( 'init', [ $this, 'load_elements_overrides' ], 5 );
			add_filter( 'post_class', [ $this, 'add_product_post_class' ] , 20);
			add_filter( 'elementor/editor/localize_settings', [ $this, 'localized_settings' ], 10 );
			add_action( 'wp', [ $this, 'get_page_settings'], 10);
			add_filter( 'acf/validate_post_id', [ $this, 'acf_validate_post_id'], 10, 2);
			add_action( 'rey/flush_cache_after_updates', [ $this, 'flush_cache' ] );
			add_action( 'acf/init', [ $this, 'add_animations_enable_setting' ] );
			add_action( 'elementor/editor/init', [ $this, 'animations_hide_native_anim_options' ] );
			add_action( 'customize_save_after', [ $this, 'update_elementor_schemes' ]);
			add_action( 'admin_init', [$this, 'filter_html_tag'] );
			add_filter( 'template_include', [ $this, 'template_include' ], 11 /* After Plugins/WooCommerce */ );
			add_filter( "theme_elementor_library_templates", [$this, 'library__page_templates_support'] );
			add_filter( 'admin_body_class', [$this, 'shop_page_disable_button']);
		}

		/**
		 * Disable page builder button
		 *
		 * @since 1.6.x
		 */
		function shop_page_disable_button($classes){

			if( class_exists('WooCommerce') && (get_the_ID() === wc_get_page_id('shop')) && apply_filters('reycore/elementor/hide_shop_page_btn', true) ){
				$classes .= ' --prevent-elementor-btn ';
			}

			return $classes;
		}
		/**
		 * Get widgets
		 *
		 * @since 1.0.0
		 */
		public function get_widgets(){
			$this->widgets_dir = trailingslashit( REY_CORE_DIR . $this->widgets_folder );
			array_map( [$this, 'get_widget_basename'], glob( $this->widgets_dir . '*' ));
		}

		public function get_widgets_list(){
			return $this->widgets;
		}

		/**
		 * Get widgets basename
		 *
		 * @since 1.0.0
		 */
		private function get_widget_basename($item){
			if( is_dir($item) ){
				$this->widgets[] = basename( $item );
			}
		}

		/**
		 * On Widgets Registered
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 */
		public function register_widgets( $widgets_manager ) {

			$disabled_elements = class_exists('ReyCore_WidgetsManager') ? ReyCore_WidgetsManager::get_disabled_elements() : [];

			foreach ( $this->widgets as $widget ) {
				$this->register_widget( $widget, $widgets_manager );
			}
		}

		/**
		 * Register widget by folder name
		 *
		 * @param  string $widget            Widget folder name.
		 * @param  object $widgets_manager Widgets manager instance.
		 * @return void
		 */
		public function register_widget( $widget, $widgets_manager ) {

			$class = ucwords( str_replace( '-', ' ', $widget ) );
			$class = str_replace( ' ', '_', $class );
			$class = sprintf( 'ReyCore_Widget_%s', $class );

			// Load Skins
			foreach ( glob( trailingslashit( $this->widgets_dir . $widget ) . "skin-*.php") as $skin) {
				require_once $skin;
			}
			// Load widget
			if( ( $file = trailingslashit( $this->widgets_dir . $widget ) . $widget . '.php' ) && is_readable($file) ){
				require_once $file;
			}

			if ( class_exists( $class ) ) {
				// Register widget
				$widgets_manager->register_widget_type( new $class );
			}

		}


		/**
		 * Load custom Elementor elements overrides
		 *
		 * @since 1.0.0
		 */
		public function load_elements_overrides()
		{
			$elements_dir = REY_CORE_DIR . 'inc/elementor/custom/';
			$elements = glob( $elements_dir . '*.php' );

			foreach ($elements as $element) {

				$base  = basename( $element, '.php' );
				$class = ucwords( str_replace( '-', ' ', $base ) );
				$class = str_replace( ' ', '_', $class );
				$class = sprintf( 'ReyCore_Element_%s', $class );

				if( ( $file = $elements_dir . $base . '.php' ) && is_readable($file) ){
					require $file;
				}
				if ( class_exists( $class ) ) {
					new $class;
				}
			}
		}

		public function register_controls() {

			require_once REY_CORE_DIR . 'inc/elementor/controls/rey-query.php';

			// Register Controls
			\Elementor\Plugin::instance()->controls_manager->register_control( 'rey-query', new ReyCore__Control_Query() );
		}

		/**
		 * Add Rey Widget Categories
		 *
		 * @since 1.0.0
		 */
		public function add_elementor_widget_categories( $elements_manager ) {

			$elements_manager->add_category(
				'rey-header',
				[
					'title' => __( 'REY Theme - Header', 'rey-core' ),
					'icon' => 'fa fa-plug',
				]
			);
			$elements_manager->add_category(
				'rey-theme',
				[
					'title' => __( 'REY Theme', 'rey-core' ),
					'icon' => 'fa fa-plug',
				]
			);
			$elements_manager->add_category(
				'rey-theme-covers',
				[
					'title' => __( 'REY Theme - Covers (sliders)', 'rey-core' ),
					'icon' => 'fa fa-plug',
				]
			);

		}

		/**
		 * Register Frontend CSS
		 *
		 * @since 1.0.0
		 */
		public function register_frontend_styles() {
			wp_register_style( 'rey-core-elementor-frontend-css', REY_CORE_URI . 'assets/css/elementor-frontend.css', ['elementor-frontend'], REY_CORE_VERSION );
			wp_register_style( 'elementor-entrance-animations-css', REY_CORE_URI . 'assets/css/elementor-entrance-animations.css', ['elementor-frontend'], REY_CORE_VERSION );
		}

		/**
		 * Enqueue ReyCore's Elementor Frontend CSS
		 */
		public function enqueue_frontend_styles() {
			wp_enqueue_style( 'rey-core-elementor-frontend-css' );
			// load entrance animations CSS
			if( self::animations_enabled() ){
				wp_enqueue_style( 'elementor-entrance-animations-css');
			}
		}

		/**
		 * Register Frontend JS
		 *
		 * @since 1.0.0
		 */
		public function register_frontend_scripts() {

			wp_register_script( 'rey-core-elementor-frontend', REY_CORE_URI . 'assets/js/elementor-frontend.js', [], REY_CORE_VERSION, true );
			wp_localize_script('rey-core-elementor-frontend', 'reyElementorFrontendParams', [
				'compatibilities' => self::get_compatibilities(),
				'ajax_url'      => admin_url( 'admin-ajax.php' ),
				'ajax_nonce'    => wp_create_nonce('reycore-ajax-verification'),
			]);
			wp_register_script( 'jquery-mousewheel', REY_CORE_URI . 'assets/js/lib/jquery-mousewheel.js', [], REY_CORE_VERSION, true );
			wp_register_script( 'elementor-entrance-animations', REY_CORE_URI . 'assets/js/lib/elementor-entrance-animations.js', ['rey-core-elementor-frontend'], REY_CORE_VERSION, true );

			wp_register_script('threejs', 'https://cdnjs.cloudflare.com/ajax/libs/three.js/109/three.min.js', [], true);
			wp_localize_script('threejs', 'reyThreeConfig', [
				'displacements' => [
					'https://i.imgur.com/t4AA2A8.jpg',
					'https://i.imgur.com/10UwPUy.jpg',
					'https://i.imgur.com/tO1ukJf.jpg',
					'https://i.imgur.com/iddaUQ7.png',
					'https://i.imgur.com/YbFcFOJ.png',
					'https://i.imgur.com/JzGo2Ng.jpg',
					'https://i.imgur.com/0toUHNF.jpg',
					'https://i.imgur.com/NPnfoR8.jpg',
					'https://i.imgur.com/xpqg1ot.jpg',
					'https://i.imgur.com/Ttm5Vj4.jpg',
					'https://i.imgur.com/wrz3VyW.jpg',
					'https://i.imgur.com/rfbuWmS.jpg',
					'https://i.imgur.com/NRHQLRF.jpg',
					'https://i.imgur.com/G29N5nR.jpg',
					'https://i.imgur.com/tohZyaA.jpg',
					'https://i.imgur.com/YvRcylt.jpg',
				],
			]);
			wp_register_script('distortion-app', REY_CORE_URI . 'assets/js/lib/distortion-app.js', ['threejs', 'jquery'], true);
		}
		/**
		 * Load Frontend JS
		 *
		 * @since 1.0.0
		 */
		public function enqueue_frontend_scripts()
		{
			wp_enqueue_script( 'animejs' );
			wp_enqueue_script( 'rey-core-elementor-frontend');

			// load entrance animations script
			if( self::animations_enabled() ){
				wp_enqueue_script( 'elementor-entrance-animations');
			}
		}

		/**
		 * Load Editor JS
		 *
		 * @since 1.0.0
		 */
		public function editor_scripts() {
			wp_enqueue_style( 'rey-core-elementor-editor-css', REY_CORE_URI . 'assets/css/elementor-editor.css', [], REY_CORE_VERSION );
			wp_enqueue_script( 'rey-core-elementor-editor', REY_CORE_URI . 'assets/js/elementor-editor.js', [], REY_CORE_VERSION, true );
			wp_localize_script('rey-core-elementor-editor', 'reyElementorEditorParams', [
				'reload_text' => esc_html__('Please save & reload page to apply this setting.', 'rey-core'),
				'rey_typography' => $this->get_typography_names(),
				'rey_icon' => REY_CORE_URI  . 'assets/images/logo-simple-white.svg',
				'rey_title' => esc_html__('Rey - Quick Menu', 'rey-core')
			]);
		}

		public function get_typography_names(){

			$pff = $sff = '';

			if( ($primary_typo = get_theme_mod('typography_primary', [])) && isset($primary_typo['font-family']) ){
				$pff = "( {$primary_typo['font-family']} )";
			}
			$primary = sprintf(esc_html__('Primary Font %s', 'rey-core'), $pff);

			if( ($secondary_typo = get_theme_mod('typography_secondary', [])) && isset($secondary_typo['font-family']) ){
				$sff = "( {$secondary_typo['font-family']} )";
			}
			$secondary = sprintf(esc_html__('Secondary Font %s', 'rey-core'), $sff);

			return [
				'primary' => $primary,
				'secondary' => $secondary,
			];
		}

		/**
		 * Push (Sync) elementor meta to ACF fields
		 *
		 * @since 1.0.0
		 */
		function elementor_to_acf( $post_id ) {

			$post_type = get_post_type($post_id);

			// settings to update
			$settings = [];

			// get Elementor' meta
			$em = get_post_meta( $post_id, \Elementor\Core\Settings\Page\Manager::META_KEY, true );

			if( empty($em) ){
				return;
			}

			// Title Display
			$settings['title_display'] = isset($em['hide_title']) && $em['hide_title'] == 'yes' ? 'hide' : '';

			if ( class_exists('ReyCore_GlobalSections') && ( $post_type === ReyCore_GlobalSections::POST_TYPE || $post_type === 'revision' ) ) {
				if( isset($em['gs_type']) && ! is_null( $em['gs_type'] ) ){
					$settings['gs_type'] = $em['gs_type'];
				}
			}

			// Transparent gradient
			$settings['rey_body_class'] = isset($em['rey_body_class']) ? $em['rey_body_class'] : '';

			if( !empty($settings) && class_exists('ACF') ){
				foreach ($settings as $key => $value) {
					update_field($key, $value, $post_id);
				}
			}
		}

		/**
		 * Load WooCommerce's
		 * On Editor - Register WooCommerce frontend hooks before the Editor init.
		 * Priority = 5, in order to allow plugins remove/add their wc hooks on init.
		 *
		 * @since 1.0.0
		 */
		public function register_wc_hooks(){
			if( class_exists('WooCommerce') ){
				wc()->frontend_includes();
			}
		}

		/**
		 * Add Product post classes (elementor edit mode)
		 * @since 1.0.0
		 */
		public function add_product_post_class( $classes ) {

			if ( in_array( get_post_type(), [ 'product', 'product_variation' ], true ) ) {

				$classes[] = 'product';

				if( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
					$classes = array_diff($classes, ['is-animated-entry']);
				}
			}

			return $classes;
		}

		/**
		 * Add an attribute to html tag
		 *
		 * @since 1.0.0
		 **/
		function filter_html_tag()
		{
			add_filter('language_attributes', function($output){
				$attributes[] = sprintf("data-post-type='%s'", get_post_type());
				return $output . implode(' ', $attributes);
			} );
		}

		/**
		 * Add Rey Config into Elementor's
		 *
		 * @since 1.0.0
		 **/
		public function localized_settings( $settings )
		{

			if( apply_filters('reycore/elementor/quickmenu', true) ){

				$settings['rey'] = [
					'global_links' => [

						'dashboard' => [
							'title' => esc_html__('WordPress Dashboard', 'rey-core'),
							'link' => esc_url( admin_url() ),
							'icon' => 'eicon-wordpress'
						],

						'exit_backend' => [
							'title' => esc_html__('Exit to page backend', 'rey-core'),
							'link' => add_query_arg([
								'post' => get_the_ID(),
								'action' => 'edit',
								], admin_url( 'post.php' )
							),
							'icon' => 'fa fa-code',
							'show_in_el_menu' => false,
						],

						'customizer' => [
							'title' => esc_html__('Customizer Settings', 'rey-core'),
							'link' => add_query_arg([
								'url' => get_permalink(  )
								], admin_url( 'customize.php' )
							),
							'icon' => 'fa fa-paint-brush',
							'class' => '--top-separator'
						],
						'settings' => [
							'title' => esc_html__('Rey Settings', 'rey-core'),
							'link' => add_query_arg([
								'page' => 'rey-settings'
								], admin_url( 'admin.php' )
							),
							'icon' => 'fa fa-cogs'
						],
						'custom_css' => [
							'title' => esc_html__('Additional CSS', 'rey-core'),
							'link' => add_query_arg([
								'autofocus[section]' => 'custom_css',
								'url' => get_permalink(  )
								], admin_url( 'customize.php' )
							),
							'icon' => 'fa fa-code'
						],
						'global_sections' => [
							'title' => esc_html__('Global Sections', 'rey-core'),
							'link' => add_query_arg([
								'post_type' => ReyCore_GlobalSections::POST_TYPE
								], admin_url( 'edit.php' )
							),
							'icon' => 'fa fa-columns'
						],

						'new_page' => [
							'title' => esc_html__('New Page', 'rey-core'),
							'link' => add_query_arg([
								'post_type' => 'page'
								], admin_url( 'post-new.php' )
							),
							'icon' => 'fa fa-edit',
							'class' => '--top-separator'
						],
						'new_global_section' => [
							'title' => esc_html__('New Global Section', 'rey-core'),
							'link' => add_query_arg([
								'post_type' => ReyCore_GlobalSections::POST_TYPE
								], admin_url( 'post-new.php' )
							),
							'icon' => 'fa fa-edit'
						],

					]
				];
			}

			// remove disabled widgets
			if( isset($settings['widgets']) ){
				$disabled_elements = class_exists('ReyCore_WidgetsManager') ? ReyCore_WidgetsManager::get_disabled_elements() : [];
				foreach( $disabled_elements as $element ){
					if(isset($settings['widgets'][$element])){
						unset($settings['widgets'][$element]);
					}
				}
			}

			return $settings;
		}

		/**
		 * Include Rey builder template
		 */
		public function template_include( $template ) {

			if ( is_singular() ) {
				$document = \Elementor\Plugin::$instance->documents->get_doc_for_frontend( get_the_ID() );
				if ( $document && $document->get_meta( '_wp_page_template' ) == self::REY_TEMPLATE ) {
					$template_path = trailingslashit( get_template_directory() ) . self::REY_TEMPLATE;
					if ( is_readable($template_path) ) {
						$template = $template_path;
					}
				}
			}

			return $template;
		}

		function library__page_templates_support( $templates ){
			$rey_templates = function_exists('rey__page_templates') ? rey__page_templates() : [];
			return $templates + $rey_templates;
		}

		function get_page_settings(){

			// if no meta, not an Elementor page
			if( ! ($elementor_meta = get_post_meta( get_the_ID(), \Elementor\Core\Base\Document::PAGE_META_KEY, true )) ){
				return;
			}

			add_filter( 'rey/site_container/classes', function( $classes ) use ($elementor_meta){
				if( isset($elementor_meta['rey_stretch_page']) && ($stretch = $elementor_meta['rey_stretch_page']) && $stretch === 'rey-stretchPage' ){
					$classes[] = $stretch;
				}
				return $classes;
			}, 10);

			do_action('reycore/elementor/get_page_meta', $elementor_meta);
		}

		/**
		 * Get Document settings
		 *
		 * @since 1.0.0
		 */
		public function get_document_settings( $setting = '' ){

			// Get the current post id
			$post_id = get_the_ID();

			// Get the page settings manager
			$page_settings_manager = \Elementor\Core\Settings\Manager::get_settings_managers( 'page' );

			// Get the settings model for current post
			$page_settings_model = $page_settings_manager->get_model( $post_id );

			return $page_settings_model->get_settings( $setting );
		}

		/**
		 * Inject HTML into an element/widget output
		 *
		 * @since 1.0.0
		 */
		public static function el_inject_html( $content, $injection, $query )
		{
			// checks
			if ( ( class_exists( 'DOMDocument' ) && class_exists( 'DOMXPath' ) && function_exists( 'libxml_use_internal_errors' ) ) ) {

				// We have to go through DOM, since it can load non-well-formed XML (i.e. HTML).
				$dom = new DOMDocument();

				// The @ is not enough to suppress errors when dealing with libxml,
				// we have to tell it directly how we want to handle errors.
				libxml_use_internal_errors( true );
				@$dom->loadHTML( mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR ); // suppress parser warnings
				libxml_clear_errors();
				libxml_use_internal_errors( false );

				// Get parsed document
				$xpath = new DOMXPath($dom);
				$container = $xpath->query($query);

				if( $container ) {
					$container_node = $container->item(0);

					// Create new node
					$newNode = $dom->createDocumentFragment();
					// add the slideshow html into the newly node
					if ( $newNode->appendXML( $injection ) && $container_node ) {
						// insert before the first child
						$container_node->insertBefore($newNode, $container_node->firstChild);
						// fixed extra html & body tags
						// on some hostings these tags are added even though LIBXML_HTML_NOIMPLIED is added
						$clean_tags = ['<html>', '<body>', '</html>', '</body>'];
						// save the content
						return str_replace($clean_tags, '', $dom->saveHTML());
					}
				}
			}
			else {
				return __( "PHP's DomDocument is not available. Please contact your hosting provider to enable PHP's DomDocument extension." );
			}

			return $content;
		}

		public static function button_styles(){
			$style['simple'] = __( 'REY - Link', 'rey-core' );
			$style['primary'] = __( 'REY - Primary', 'rey-core' );
			$style['secondary'] = __( 'REY - Secondary', 'rey-core' );
			$style['primary-outline'] = __( 'REY - Primary Outline', 'rey-core' );
			$style['secondary-outline'] = __( 'REY - Secondary Outline', 'rey-core' );
			$style['underline'] = __( 'REY - Underlined', 'rey-core' );
			$style['underline-hover'] = __( 'REY - Hover Underlined', 'rey-core' );
			$style['dashed --large'] = __( 'REY - Large Dash', 'rey-core' );
			$style['dashed'] = __( 'REY - Normal Dash', 'rey-core' );
			$style['underline-1'] = __( 'REY - Underline 1', 'rey-core' );
			$style['underline-2'] = __( 'REY - Underline 2', 'rey-core' );

			return $style;
		}

		/**
		 * Wierd bug in Elementor Preview, where ACF
		 * is not getting the proper POST ID
		 *
		 * @since 1.0.0
		 */
		function acf_validate_post_id( $pid, $_pid ){
			if( isset($_GET['preview_id']) && isset($_GET['preview_nonce']) ){
				return $_pid;
			}
			return $pid;
		}

		/**
		 * Flush Elementor cache
		 * - after updates
		 */
		public function flush_cache(){
			if( is_multisite() ){
				$blogs = get_sites();
				foreach ( $blogs as $keys => $blog ) {
					$blog_id = $blog->blog_id;
					switch_to_blog( $blog_id );
						\Elementor\Plugin::$instance->files_manager->clear_cache();
					restore_current_blog();
				}
			}
			else {
				\Elementor\Plugin::$instance->files_manager->clear_cache();
			}
		}

		/**
		 * Display a notice in widgets, in edit mode.
		 * For example requirements of a widget or a simple warning
		 *
		 * @since 1.0.0
		 */
		public static function edit_mode_widget_notice( $presets = [], $args = [] )
		{
			if( \Elementor\Plugin::instance()->editor->is_edit_mode() || \Elementor\Plugin::instance()->preview->is_preview_mode() ) {

				$default_presets = [
					'full_viewport' => [
						'type' => 'warning',
						'title' => __('Requirement!', 'rey-core'),
						'text' => __('This widget is full viewport only. Please access this widget\'s parent section, and <strong>enable Stretch Section</strong> and also select <strong>Content Width to "Full width"</strong>.', 'rey-core'),
						'class' => 'coverEl-notice--needStretch',
					],
					'tabs_modal' => [
						'type' => 'warning',
						'title' => __('Not using properly!', 'rey-core'),
						'text' => __('Please don\'t use this widget into a section with <strong>Tabs</strong> or <strong>Modal</strong> enabled. Please disable those settings.', 'rey-core'),
						'class' => 'coverEl-notice--noTabs',
					]
				];

				$markup = '<div class="rey-elementorNotice rey-elementorNotice--%s %s"><h4>%s</h4><p>%s</p></div>';

				if( !empty($args) ){

					$defaults = [
						'type' => 'warning',
						'text' => __('Warning!', 'rey-core'),
						'text' => __('Text', 'rey-core'),
						'class' => '',
					];

					// Parse args.
					$args = wp_parse_args( $args, $defaults );

					printf( $markup, $args['type'], $args['class'], $args['title'], $args['text'] );
				}

				if( !empty($presets) ){
					foreach ($presets as $preset) {
						if( isset($default_presets[$preset]) ){
							printf(
								$markup,
								$default_presets[$preset]['type'],
								$default_presets[$preset]['class'],
								$default_presets[$preset]['title'],
								$default_presets[$preset]['text']
							);
						}
					}
				}

			}
		}

		/**
		 * Allow disabling entrance animation engine.
		 *
		 * @since 1.0.0
		 */
		public function add_animations_enable_setting(){
			acf_add_local_field(array(
				'key'          => 'field_elementor_animations_enable',
				'name'         => 'elementor_animations_enable',
				'label'        => esc_html__('Enable Elementor Animations', 'rey-core'),
				'type'         => 'true_false',
				'instructions' => __('Enable or disable Elementors entrance animation engine. Learn more <a href="https://support.reytheme.com/kb/how-to-use-animated-entrance-effects/" target="_blank">how to use animated entrance effects</a>', 'rey-core'),
				'default_value' => 1,
				'ui' => 1,
				'parent'       => 'group_5c990a758cfda',
				'menu_order'   => 300,
			));
		}

		/**
		 * Checks if entrance animation engine is enabled
		 * @since 1.0.0
		 */
		public static function animations_enabled()
		{
			if( !class_exists('ACF') ){
				return true;
			}

			$option = get_field('elementor_animations_enable', REY_CORE_THEME_NAME);

			return is_null($option) || $option === true;
		}

		/**
		 * Hides Elementor's native entrance animation options
		 * if Rey's animations are enabled.
		 * @since 1.0.0
		 */
		public function animations_hide_native_anim_options(){
			if(self::animations_enabled()):
				add_action('wp_head', function(){?>
					<style>
						.elementor-control-type-animation { display: none; }
					</style>
				<?php });
			endif;
		}

		/**
		 * Sync Customizer's colors with Elementor's
		 * Only update if the scheme's haven't been modified in Elementor
		 *
		 * @since 1.0.0
		 */
		public function update_elementor_schemes()
		{

			if( get_option(\Elementor\Scheme_Base::LAST_UPDATED_META) ){
				return;
			}

			// Color Scheme
			$el_scheme_color = get_option( 'elementor_scheme_color' );

			if( $el_scheme_color && is_array($el_scheme_color) ){
				// Theme Text Color
				$text_color = get_theme_mod('style_text_color');
				// Primary
				$el_scheme_color[1] = $text_color ? $text_color : '#373737';
				// Text
				$el_scheme_color[3] = $text_color ? $text_color : '#373737';
				// Accent
				$el_scheme_color[4] = get_theme_mod('style_accent_color', '#212529');

				update_option( 'elementor_scheme_color', $el_scheme_color );
			}

			// Typography
			$el_scheme_typography = get_option( 'elementor_scheme_typography' );

			if( $el_scheme_typography && is_array($el_scheme_typography) )
			{
				foreach($el_scheme_typography as $key => $typography_scheme){
					// Just reset to defaults
					$el_scheme_typography[$key]['font_family'] = '';
				}
				update_option( 'elementor_scheme_typography', $el_scheme_typography );
			}
		}

		/**
		 * Method to get Elementor compatibilities.
		 *
		 * @since 1.0.0
		 */
		public static function get_compatibilities( $support = '' )
		{
			$supports = [
				/**
				 * If Elementor adds video support on columns,
				 * i'll need to add ELEMENTOR_VERSION < x.x.x .
				 */
				'column_video' => true,
				/**
				 * Currently disabled by default. Needs implementation. However still,
				 * i'll need to add support on ELEMENTOR_VERSION > 2.7.0
				 */
				'video_bg_play_on_mobile' => true,
			];

			if( $support && isset($supports[$support]) ){
				return $supports[$support];
			}

			return $supports;
		}

		public static function getReyBadge( $text = '' ){

			if( empty($text) && defined('REY_CORE_THEME_NAME') ){
				$text = REY_THEME_NAME;
			}

			return sprintf('<span class="rey-elementorBadge">%s</span>', $text);
		}


		/**
		 * Retrieve the reference to the instance of this class
		 * @return ReyCoreElementor
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

	}

	function reyCoreElementor() {
		return ReyCoreElementor::getInstance();
	}

	reyCoreElementor();

endif;
