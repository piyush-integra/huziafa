<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_Tabs') ):

	class ReyCore_WooCommerce_Tabs
	{

		public function __construct() {
			add_action( 'init', [$this, 'initialize']);
			add_action( 'acf/init', [ $this, 'acf_fields' ] );
		}

		function initialize(){

			add_filter( 'woocommerce_product_tabs', [$this,'add_information_panel'], 10);
			add_filter( 'woocommerce_product_additional_information_heading', [$this,'rename_additional_info_panel'], 10);
			add_filter( 'woocommerce_product_tabs', [$this, 'manage_tabs'], 20);
			add_filter( 'wc_product_enable_dimensions_display', [$this, 'disable_specifications_dimensions'], 10);
			add_action( 'wp_footer', [$this, 'reviews__start_open'], 999);
			add_filter( 'rey/woocommerce/product_panels_classes', [$this, 'add_blocks_class']);
			add_filter( 'the_content', [$this, 'add_description_toggle']);
			add_filter( 'wp', [$this, 'move_specifications_block']);
			add_filter('acf/load_value/key=field_5ecae99f56e6d', [$this, 'load_custom_tabs'], 10, 3);

			$this->remove_tabs_titles();

		}

		/**
		 * Add Information Panel
		 *
		 * @since 1.0.0
		 **/
		function information_panel_content()
		{
			echo reycore__parse_text_editor( reycore__get_option( 'product_info_content' ) );
		}

		/**
		 * Add Information Panel
		 *
		 * @since 1.0.0
		 **/
		function add_information_panel($tabs)
		{
			if( ($ip = reycore__get_option('product_info', '2')) && ($ip === '1' || $ip === 'custom') ) {

				$title = __( 'Information', 'rey-core' );

				if( $custom_title = get_theme_mod('single__product_info_title', '') ){
					$title = $custom_title;
				}

				$tabs['information'] = array(
					'title'    => $title,
					'priority' => 15,
					'callback' => [$this, 'information_panel_content'],
				);
			}

			return $tabs;
		}


		/**
		 * Rename Additional Information Panel
		 *
		 * @since 1.0.0
		 **/
		function rename_additional_info_panel($heading)
		{
			if( $title = get_theme_mod('single_specifications_title', '') ){
				return $title;
			}

			return esc_html__( 'Specifications', 'rey-core' );
		}

		function manage_tabs( $tabs ){

			// change priorities
			foreach ([

				'description' => get_theme_mod('single_description_priority', 10),
				'information' => get_theme_mod('single_custom_info_priority', 15),
				'additional_information' => get_theme_mod('single_specs_priority', 20),
				'reviews' => get_theme_mod('single_reviews_priority', 30),

			] as $key => $value) {
				if( isset($tabs[$key]) && isset($tabs[$key]['priority']) ){
					$tabs[$key]['priority'] = absint($value);
				}
			}

			// Specs title
			if( $specs_title = get_theme_mod('single_specifications_title', '') ){
				$tabs['additional_information']['title'] = $specs_title;
			}

			// disable specs tab
			if( ! get_theme_mod('single_specifications_block', true) ){
				unset( $tabs['additional_information'] );
			}

			/**
			 * Custom Tabs
			 */
			$custom_tabs = get_theme_mod('single__custom_tabs', '');

			if( is_array($custom_tabs) && !empty($custom_tabs) && class_exists('ACF') && $custom_tabs_content = get_field('product_custom_tabs') ){

				foreach ($custom_tabs_content as $key => $c_tab) {
					$tab_content = isset($c_tab['tab_content']) ? reycore__parse_text_editor( $c_tab['tab_content'] ) : '';
					if( empty($tab_content) ){
						continue;
					}
					$slug = sanitize_title($c_tab['tab_title']);
					$tabs[$slug] = [
						'title' => $c_tab['tab_title'],
						'priority' => absint($custom_tabs[$key]['priority']),
						'callback' => function() use ($tab_content) {
							echo $tab_content;
						},
					];
				}
			}

			return $tabs;
		}

		/**
		 * Move Specifications / Additional Information block/tab into Summary
		 *
		 * @since 1.6.7
		 */
		function move_specifications_block(){

			if( ! ($pos = get_theme_mod('single_specifications_position', '')) ){
				return;
			}

			// move specifications / additional in summary
			add_action( 'woocommerce_single_product_summary', 'woocommerce_product_additional_information_tab', $pos );

			add_filter( 'woocommerce_product_tabs', function( $tabs ) {
				unset( $tabs['additional_information'] );
				return $tabs;
			}, 99 );
		}



		function disable_specifications_dimensions(){
			return get_theme_mod('single_specifications_block_dimensions', true);
		}

		function remove_tabs_titles(){

			if( ! ((get_theme_mod('product_content_layout', 'blocks') === 'tabs') && get_theme_mod('product_content_tabs_disable_titles', false)) ){
				return;
			}

			add_filter('woocommerce_product_description_heading', '__return_false');
			add_filter('woocommerce_product_additional_information_heading', '__return_false');
			add_filter('woocommerce_post_class', function($classes){
				$classes['remove-titles'] = '--tabs-noTitles';
				return $classes;
			});
		}


		function reviews__start_open()
		{
			if( get_theme_mod('single_reviews_start_opened', false) ){ ?>
			<script>
				jQuery(document).on('ready', function(){
					jQuery('.single-product .rey-reviewsBtn.js-toggle-target').trigger('click');
				});
			</script>
			<?php }
		}


		/**
		 * Customize product page's blocks
		 *
		 * @since 1.0.12
		 **/
		function add_blocks_class( $classes )
		{
			if( get_theme_mod('product_content_layout', 'blocks') === 'blocks' ){
				$classes[] = get_theme_mod('product_content_blocks_desc_stretch', false) ? '--stretch-desc' : '';
			}

			return $classes;
		}

		function add_description_toggle($content){

			if( is_product() && get_theme_mod('product_content_blocks_desc_toggle', false) ){
				return sprintf(
					'<div class="rey-prodDescToggle u-toggle-text-next-btn %s">%s</div><button class="btn btn-line-active"><span data-read-more="%s" data-read-less="%s"></span></button>',
					apply_filters('reycore/productdesc/mobile_only', false) ? '--mobile' : '',
					$content,
					esc_html_x('Read more', 'Toggling the product excerpt in Compact layout.', 'rey-core'),
					esc_html_x('Less', 'Toggling the product excerpt in Compact layout.', 'rey-core')
				);
			}

			return $content;
		}

		function acf_fields(){

			acf_add_local_field([
				'key' => 'field_5ecaea2256e70',
				'label' => 'Custom Tabs',
				'name' => '',
				'type' => 'tab',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'placement' => 'top',
				'endpoint' => 0,
				'parent'     => 'group_5d4ff536a2684',
			]);

			acf_add_local_field([
				'key' => 'field_5ecae99f56e6d',
				'label' => 'Tabs',
				'name' => 'product_custom_tabs',
				'type' => 'repeater',
				'instructions' => 'These tabs are created in Customizer > WooCommerce > Product page - Tabs panel.',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => 'rey-hideNewBtn',
					'id' => '',
				),
				'collapsed' => 'field_5ecae9c356e6e',
				'min' => 0,
				'max' => 0,
				'layout' => 'row',
				'button_label' => '',
				'sub_fields' => array(
					array(
						'key' => 'field_5ecae9c356e6e',
						'label' => 'Tab Title',
						'name' => 'tab_title',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array(
						'key' => 'field_5ecae9ef56e6f',
						'label' => 'Tab Content',
						'name' => 'tab_content',
						'type' => 'wysiwyg',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'tabs' => 'all',
						'toolbar' => 'full',
						'media_upload' => 1,
						'delay' => 1,
					),
				),
				'parent'     => 'group_5d4ff536a2684',
			]);
		}

		function load_custom_tabs($value, $post_id, $field) {

			if ($value !== false) {
				return $value;
			}

			$tabs = get_theme_mod('single__custom_tabs', '');

			if( is_array($tabs) && !empty($tabs) ){
				$value = [];
				foreach ($tabs as $key => $tab) {
					$value[]['field_5ecae9c356e6e'] = $tab['text'];
				}
			}

			return $value;
		}
	}

	new ReyCore_WooCommerce_Tabs;

endif;
