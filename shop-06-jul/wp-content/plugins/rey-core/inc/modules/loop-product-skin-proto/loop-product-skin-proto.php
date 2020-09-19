<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( class_exists('WooCommerce') && class_exists('ReyCore_WooCommerce_Loop') && !class_exists('ReyCore_WooCommerce_Loop_Skin_Proto') ):
/**
 * Proto Products Loop Skin
 */
class ReyCore_WooCommerce_Loop_Skin_Proto extends ReyCore_WooCommerce_Loop
{
	const TYPE = 'proto';

	public function __construct()
	{

		add_filter('reycore/kirki_fields/field=loop_skin', [$this, 'add_skin'], 20);
		add_action( 'reycore/kirki_fields/after_field=loop_skin', [ $this, 'add_customizer_options' ] );

		add_action( 'init', [$this, 'init'] );
	}

	public function init()
	{
		if ( is_customize_preview() ) {
			add_action( 'customize_preview_init', [$this, 'load_skin_hooks'] );
			return;
		}
		$this->load_skin_hooks();
	}

	public function load_skin_hooks()
	{
		if( $this->get_loop_active_skin() !== self::TYPE ){
			return;
		}

		add_action( 'woocommerce_before_shop_loop_item', [$this, 'apply_extra_thumbs_filter'], 10);
		add_action( 'woocommerce_after_shop_loop_item', [$this, 'remove_extra_thumbs_filter'], 10);

		add_action( 'woocommerce_after_shop_loop_item', [$this, 'wrap_product_buttons'], 9);
		add_action( 'woocommerce_after_shop_loop_item', 'reycore_wc__generic_wrapper_end', 199);

		add_action( 'woocommerce_before_shop_loop_item_title', [$this, 'wrap_product_details'], 19);
		add_action( 'woocommerce_after_shop_loop_item', 'reycore_wc__generic_wrapper_end', 200 );
		add_action( 'woocommerce_before_subcategory_title', [$this, 'wrap_product_details'], 19);
		add_action( 'woocommerce_after_subcategory_title', 'reycore_wc__generic_wrapper_end', 200 );

		add_filter( 'post_class', [$this,'custom_css_classes'], 20 );
		add_filter( 'product_cat_class', [$this,'custom_css_classes'], 20 );
		add_filter( 'reycore/loop/component_hooks', [$this, 'get_component_hooks'] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

	}

	function add_skin( $field ){
		$field['choices'][self::TYPE] = esc_attr__('Proto Skin', 'rey-core');
		return $field;
	}

	function add_customizer_options(){
		require_once REY_CORE_MODULE_DIR . 'loop-product-skin-proto/customizer-options.php';
	}

	/**
	 * Override default components.
	 *
	 * @since 1.3.0
	 */
	public function get_component_hooks( $components ){

		$component_hooks  =  [
			'brands'         => [
				'type'          => 'action',
				'tag'           => 'woocommerce_before_shop_loop_item_title',
				'callback'      => [ $this, 'component_brands' ],
				'priority'      => 60,
			],
			'category'       => [
				'type'          => 'action',
				'tag'           => 'woocommerce_before_shop_loop_item_title',
				'callback'      => [ $this, 'component_product_category'],
				'priority'      => 70,
			],
			'quickview'      => [
				'bottom' => [
					'callback'      => [ $this, 'component_quickview_button' ],
				],
				'topright' => [
					'callback'      => [ $this, 'component_quickview_button' ],
				],
				'bottomright' => [
					'callback'      => [ $this, 'component_quickview_button' ],
				],
			],
			'wishlist'       => [
				'bottom' => [
					'callback'      => [ $this, 'component_wishlist' ],
				],
				'topright' => [
					'callback'      => [ $this, 'component_wishlist' ],
				],
				'bottomright' => [
					'callback'      => [ $this, 'component_wishlist' ],
				],
			],
		];

		$component_hooks = reycore__wp_parse_args( $component_hooks, $components );

		return $component_hooks;
	}

	function is_padded(){
		return get_theme_mod('proto_loop_padded', false);
	}

	/**
	 * Wraps the product details so it can be absolutely positioned
	 *
	 * @since 1.0.0
	 */
	public function wrap_product_details(){
		?>
		<div class="rey-loopDetails <?php echo $this->is_padded() ? '--padded' : '' ?>">
		<?php
	}

	/**
	 * Wrap product buttons
	 *
	 * @since 1.0.0
	 **/
	function wrap_product_buttons()
	{ ?>
		<div class="rey-loopButtons">
		<?php
	}


	/**
	 * Item Component - Quickview button
	 *
	 * @since 1.0.0
	 */
	public function component_quickview_button(){
		$start = $end = '';

		if( get_theme_mod('loop_quickview_position', 'bottom') !== 'bottom' ){
			$start = '<div class="rey-thPos-item --no-margins">';
			$end = '</div>';
		}

		echo $start;
		echo ReyCore_WooCommerce_QuickView::get_button_html();
		echo $end;
	}

	/**
	 * Item Component - Wishlist
	 *
	 * @since 1.3.0
	 */
	public function component_wishlist(){
		$start = $end = '';

		if( get_theme_mod('loop_wishlist_position', 'bottom') !== 'bottom' ){
			$start = '<div class="rey-thPos-item --no-margins">';
			$end = '</div>';
		}

		echo $start;
		echo ReyCore_WooCommerce_Wishlist::get_button_html();
		echo $end;
	}


	/**
	 * Adds custom CSS Classes
	 *
	 * @since 1.1.2
	 */
	function custom_css_classes( $classes )
	{
		if( is_admin() ){
			return $classes;
		}

		if ( $this->is_product() ) {
			if( get_theme_mod('proto_loop_hover_animation', true) ) {
				$classes['hover-animated'] = 'is-animated';
			}
		}

		if( $this->is_padded() ){
			$classes['shadow_active'] = '--shadow-' . get_theme_mod('proto_loop_shadow', '1');
			$classes['shadow_hover'] = '--shadow-h-' . get_theme_mod('proto_loop_shadow_hover', '3');
		}

		if( ($general_css_classes = $this->general_css_classes()) && is_array($general_css_classes) ){
			$classes = array_merge($classes, $general_css_classes);
		}

		return $classes;
	}

	public function enqueue_scripts(){
		wp_enqueue_style( 'reycore-loop-product-skin-proto', REY_CORE_MODULE_URI . basename(__DIR__) . '/style.css', [], REY_CORE_VERSION );
	}

}

new ReyCore_WooCommerce_Loop_Skin_Proto;

endif;
