<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( defined( 'ELEMENTOR_PRO_VERSION' ) && !class_exists('ReyCore_Compatibility__ElementorPro') ):
	/**
	 * Elementor PRO
	 *
	 * @since 1.3.0
	 */
	class ReyCore_Compatibility__ElementorPro
	{

		private $headers = [];
		private $footers = [];

		public function __construct()
		{
			add_action( 'elementor_pro/init', [ $this, 'on_elementor_pro_init' ] );
		}

		public function on_elementor_pro_init() {

			$this->misc();

			add_action( 'get_header', [ $this, 'handle_theme_support' ], 8 );
			add_filter( 'rey/header/header_classes', [$this, 'header_classes']);

			// WooCommerce
			add_filter( 'reycore/woocommerce_cart_item_remove_link', [$this, 'remove_mini_cart_remove_btn']);
			add_action( 'elementor/widget/before_render_content', [$this, 'loop_props_in_elements']);
			add_action( 'elementor/widget/before_render_content', [$this, 'single_elements']);

			add_action( 'elementor/element/single/document_settings/before_section_end', [$this, 'add_rey_pb_template_option']);
			add_action( 'elementor/element/archive/document_settings/before_section_end', [$this, 'add_rey_pb_template_option']);

			add_action('elementor/theme/before_do_single', [$this, 'location_before']);
			add_action('elementor/theme/after_do_single', [$this, 'location_after']);
			add_action('elementor/theme/before_do_archive', [$this, 'location_before']);
			add_action('elementor/theme/after_do_archive', [$this, 'location_after']);

			// add_action( 'elementor/frontend/widget/before_render', [$this, 'before_render'], 10);
			// add_action( 'elementor/frontend/widget/after_render', [$this, 'after_render'], 10);

		}

		private function get_theme_builder_module() {
			return \ElementorPro\Modules\ThemeBuilder\Module::instance();
		}

		private function get_theme_support_instance() {
			$module = $this->get_theme_builder_module();
			return $module->get_component( 'theme_support' );
		}

		public function handle_theme_support() {
			$module = $this->get_theme_builder_module();
			$conditions_manager = $module->get_conditions_manager();
			$this->headers = $conditions_manager->get_documents_for_location( 'header' );
			$this->footers = $conditions_manager->get_documents_for_location( 'footer' );

			$this->remove_action( 'header' );
			$this->remove_action( 'footer' );

			$this->add_support();
		}

		public function remove_action( $action ) {
			$handler = 'get_' . $action;
			$instance = $this->get_theme_support_instance();
			remove_action( $handler, [ $instance, $handler ] );
		}

		public function do_header(){
			$module = $this->get_theme_builder_module();
			$location_manager = $module->get_locations_manager();
			$location_manager->do_location( 'header' );
		}

		public function do_footer(){
			$module = $this->get_theme_builder_module();
			$location_manager = $module->get_locations_manager();
			$location_manager->do_location( 'footer' );
		}

		public function add_support(){

			if ( !empty( $this->headers ) && function_exists('rey__header__content') ) {
				remove_action('rey/header/content', 'rey__header__content');
				add_action('rey/header/content', [$this, 'do_header']);

				add_filter('reycore/header/display', '__return_false');
			}

			if ( !empty( $this->footers ) && function_exists('rey_action__footer__content') ) {
				remove_action('rey/footer/content', 'rey_action__footer__content');
				add_action('rey/footer/content', [$this, 'do_footer']);

				add_filter('reycore/footer/display', '__return_false');
			}
		}

		public function header_classes($classes){

			if( !empty( $this->headers ) && isset($classes['layout']) ){
				$classes['layout'] = 'rey-siteHeader--custom';
			}

			return $classes;
		}

		function misc(){

			// make search form element use the product catalog template
			add_action('elementor_pro/search_form/after_input', function(){
				echo '<input type="hidden" name="post_type" value="product">';
			});

		}

		function add_rey_pb_template_option($element){
			$element->add_control(
				'rey_page_builder',
				[
					'label' => esc_html__( 'Enable "Rey - Page Builder" template', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);
		}

		function is_rey_pb() {
			$meta = get_post_meta( get_the_ID(), '_elementor_page_settings', true );

			if( isset($meta['rey_page_builder']) && $meta['rey_page_builder'] === 'yes' ){
				return true;
			}

			return false;
		}

		function location_before( $instance ){
			if( $this->is_rey_pb() && function_exists('rey_action__before_site_container') ){
				rey_action__before_site_container();
			}
		}

		function location_after( $instance ){
			if( $this->is_rey_pb() && function_exists('rey_action__after_site_container') ){
				rey_action__after_site_container();
			}
		}

		/**
		 * WooCommerce
		 */


		/**
		 * Run loop props for Upsell & Related
		 *
		 * @since 1.3.2
		 */
		function loop_props_in_elements( $element ){

			$widgets = [
				'woocommerce-products',
				'woocommerce-product-related',
				'woocommerce-product-upsell',
				'wc-archive-products',
			];

			$widget_name = $element->get_unique_name();

			if( ! in_array($widget_name, $widgets) ){
				return;
			}

			if( !class_exists('ReyCore_WooCommerce_Loop') ){
				return;
			}

			$loop_instance = ReyCore_WooCommerce_Loop::getInstance();

			$loop_instance->set_loop_props();
			$loop_instance->components_add_remove();

			$settings = $element->get_settings_for_display();


			$widget_name_clean = str_replace('-', '_', $widget_name);
			$widget_function = "el_widget__{$widget_name_clean}";

			if( method_exists($this, $widget_function ) ){
				$this->$widget_function($settings);
			}

			// Make sure to change cols
			if( isset($settings['columns']) ){
				wc_set_loop_prop('columns', $settings['columns']);
			}

			add_filter('reycore/woocommerce/columns', function($breakpoints) use ($settings){

				$breakpoints['tablet'] = 3;
				$breakpoints['mobile'] = 2;

				if( isset($settings['columns']) && $desktop = $settings['columns']){
					$breakpoints['desktop'] = $desktop;
				}

				if( isset($settings['columns_tablet']) && $tablet = $settings['columns_tablet']){
					$breakpoints['tablet'] = $tablet;
				}

				if( isset($settings['columns_mobile']) && $mobile = $settings['columns_mobile']){
					$breakpoints['mobile'] = $mobile;
				}

				return $breakpoints;
			});
		}

		/**
		 * Fix for EPRO custom mini-cart skin
		 *
		 * @since 1.3.1
		 */
		public function remove_mini_cart_remove_btn( $html ){

			$use_mini_cart_template = get_option( 'elementor_use_mini_cart_template', 'no' );

			if ( 'yes' === $use_mini_cart_template ) {
				return false;
			}

			return $html;
		}

		function single_elements( $element )
		{

			if( $element->get_unique_name() === 'woocommerce-product-images' ){
				add_action('woocommerce_before_template_part', [$this, 'fix_image_gallery'], 10, 4);
			}
		}

		/**
		 * Fix theme builder's product gallery
		 *
		 * @since 1.6.4
		 **/
		function fix_image_gallery( $template_name ){

			if( $template_name !== 'single-product/product-image.php' ){
				return;
			}

			// load mobile gallery
			if( class_exists('ReyCore_WooCommerce_ProductGallery_Base') ){
				ReyCore_WooCommerce_ProductGallery_Base::getInstance()->mobile_gallery();
			}

		}

	}

	new ReyCore_Compatibility__ElementorPro;
endif;
