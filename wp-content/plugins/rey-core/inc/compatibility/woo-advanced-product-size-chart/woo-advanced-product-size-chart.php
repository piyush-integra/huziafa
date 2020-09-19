<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if( !class_exists('ReyCore_Compatibility__WooAdvancedProductSizeChart') ):

	class ReyCore_Compatibility__WooAdvancedProductSizeChart
	{
		private $data = [];

		public function __construct()
		{
			if( ! class_exists('Size_Chart_For_Woocommerce') ){
				return;
			}

			add_action( 'acf/init', [ $this, 'add_plugin_settings' ] );
			add_action( 'wp', [ $this, 'init' ] );
		}

		public function init(){

			add_action( 'wp_enqueue_scripts', [ $this, 'load_styles' ] );

			$this->data = [
				'btn_text' => '',
				'modal_content' => ''
			];

			$this->advanced_size_chart_for_woocommerce();

			add_action('rey/after_site_wrapper', [$this, 'modal_html'], 50);
			add_filter( 'reycore/modal_template/show', '__return_true' );

			$btn_position = 'before_atc';
			$product_type = 'simple';

			if( class_exists('ACF') && ($selected_position = get_field('wapsc_button_position', REY_CORE_THEME_NAME)) ){
				$btn_position = $selected_position;
			}

			$button_positions = [
				'before_atc' => [
					'simple' => [
						'hook' => 'woocommerce_single_product_summary',
						'priority' => 29
					],
					'variable' => [
						'hook' => 'woocommerce_before_single_variation',
						'priority' => 10
					]
				],
				'after_atc' => [
					'simple' => [
						'hook' => 'woocommerce_single_product_summary',
						'priority' => 31
					],
					'variable' => [
						'hook' => 'woocommerce_after_single_variation',
						'priority' => 10
					]
				]
			];

			// patch when catalog mode
			if( get_theme_mod('shop_catalog', false) ){
				$button_positions['before_atc']['variable']['hook'] = 'woocommerce_single_product_summary';
				$button_positions['before_atc']['variable']['priority'] = 30;
				$button_positions['after_atc']['variable']['hook'] = 'woocommerce_single_product_summary';
				$button_positions['after_atc']['variable']['priority'] = 30;
			}

			if( ($product = wc_get_product()) && $product->is_type( 'variable' ) ){
				$product_type = 'variable';
			}

			add_action($button_positions[$btn_position][$product_type]['hook'], [$this, 'button_html'], $button_positions[$btn_position][$product_type]['priority']);
		}

		public function button_html(){

			if( !($btn_text = $this->data['btn_text']) ){
				return;
			}

			$btn_style = 'line-active';

			if( class_exists('ACF') && ($selected_style = get_field('wapsc_button_style', REY_CORE_THEME_NAME)) ){
				$btn_style = $selected_style;
			}

			printf('<div class="rey-sizeChart-btnWrapper"><a href="#" class="btn btn-%3$s rey-sizeChart-btn" data-reymodal=\'%2$s\'>%1$s</a></div>',
				$btn_text,
				wp_json_encode([
					'content' => '.rey-sizeChart-modal',
					'width' => 700,
					'id' => 'wapsc-' . get_the_ID(),
					// 'closeInside' => false
				]),
				esc_attr($btn_style)
			);
		}

		public function modal_html(){

			if( !($modal_content = $this->data['modal_content']) ){
				return;
			}

			printf('<div class="rey-sizeChart-modal --hidden">%s</div>', $modal_content);
		}

		function advanced_size_chart_for_woocommerce() {

			if( apply_filters('reycore/woo-advanced-product-size-chart/override', true) === false ){
				return;
			}

			$handle = 'advanced-product-size-charts-for-woocommerce';

			add_action( 'wp_print_scripts', function() use ($handle){
				wp_dequeue_script( $handle );
			});

			add_action( 'wp_enqueue_scripts', function() use ($handle){
				wp_dequeue_style( $handle );
				wp_dequeue_style( $handle . '-jquery-modal-default-theme' );
			}, 999);

			reycore__remove_filters_for_anonymous_class( 'woocommerce_before_single_product', 'Size_Chart_For_Woocommerce_Public', 'size_chart_popup_button_position_callback', 10 );

			$product = wc_get_product();

			if( ! ($product && ($product_id = $product->get_id())) ){
				return;
			}

        	$prod_id = size_chart_get_product_chart_id( $product_id );

			if ( isset( $prod_id ) && !empty($prod_id) && '' !== get_post_status( $prod_id ) && 'publish' === get_post_status( $prod_id ) ) {
				$chart_id = $prod_id;
			} else {
				$Size_Chart_For_Woocommerce_Public = new Size_Chart_For_Woocommerce_Public('', '', '');
				$chart_id = $Size_Chart_For_Woocommerce_Public->size_chart_id_by_category( $product_id );
			}

			$chart_label = size_chart_get_label_by_chart_id( $chart_id );
			$chart_position = size_chart_get_position_by_chart_id( $chart_id );

			if ( 0 !== $chart_id && 'popup' === $chart_position ) {

				$size_chart_popup_label = size_chart_get_popup_label();

				if ( isset( $size_chart_popup_label ) && !empty($size_chart_popup_label) ) {
					$popup_label = $size_chart_popup_label;
				} else {
					$popup_label = $chart_label;
				}

				$this->data['btn_text'] = esc_html( $popup_label );

				$file_dir_path = 'includes/common-files/size-chart-contents.php';

				if ( file_exists( WP_PLUGIN_DIR . '/woo-advanced-product-size-chart/' . $file_dir_path ) ) {

					ob_start();
					include_once WP_PLUGIN_DIR . '/woo-advanced-product-size-chart/' . $file_dir_path;
					$this->data['modal_content'] = ob_get_clean();
				}
			}

		}

		public function add_plugin_settings(){

			acf_add_local_field(array(
				'key'        => 'field_wapsc_title',
				'type'       => 'message',
				'parent'     => 'group_5c990a758cfda',
				'message'    => __('<h1>Advanced Product Size Charts for WooCommerce</h1>', 'rey-core'),
				'menu_order' => 300,
			));

			acf_add_local_field(array(
				'key' => 'field_5e43b4251a2f7',
				'label' => 'Position',
				'name' => 'wapsc_button_position',
				'type' => 'select',
				'instructions' => esc_html__('Select the position of the button', 'rey-core'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => 'rey-decrease-list-size',
					'id' => '',
				),
				'choices' => array(
					'before_atc' => esc_html__('Before Add to cart button', 'rey-core'),
					'after_atc' => esc_html__('After Add to cart button', 'rey-core'),
				),
				'default_value' => array(
					0 => 'before_atc',
				),
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'value',
				'ajax' => 0,
				'placeholder' => '',
				'parent'       => 'group_5c990a758cfda',
				'menu_order'   => 300,
			));

			acf_add_local_field(array(
				'key' => 'field_5e43b5331a2f8',
				'label' => 'Button Style',
				'name' => 'wapsc_button_style',
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => 'rey-decrease-list-size',
					'id' => '',
				),
				'choices' => array(
					'primary' => 'Primary',
					'secondary' => 'Secondary',
					'line-active' => 'Underlined',
					'line' => 'Underlined on hover',
				),
				'default_value' => array(
					0 => 'line-active',
				),
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'value',
				'ajax' => 0,
				'placeholder' => '',
				'parent'       => 'group_5c990a758cfda',
				'menu_order'   => 300,
			));

		}

		public function load_styles(){
            wp_enqueue_style( 'reycore-wapsc-styles', REY_CORE_COMPATIBILITY_URI . basename(__DIR__) . '/style.css', [], REY_CORE_VERSION );
		}

	}

	new ReyCore_Compatibility__WooAdvancedProductSizeChart();
endif;
