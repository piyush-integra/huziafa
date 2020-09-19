<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( class_exists('WooCommerce') && !class_exists('ReyCore_Wc_StickyAddToCart') ):

class ReyCore_Wc_StickyAddToCart
{
	private $settings = [];

	private $product;

	public function __construct()
	{
		add_action( 'reycore/kirki_fields/after_field=product_page_ajax_add_to_cart', [ $this, 'add_customizer_options' ] );
		add_action( 'wp', [$this, 'init'] );
	}

	public function init()
	{
		if( ! is_single() ){
			return;
		}

		$this->product = wc_get_product();

		if( ! $this->product ){
			return;
		}

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_filter( 'rey/main_script_params', [ $this, 'script_params'] );
		add_action( 'rey/after_site_wrapper', [$this, 'markup']);
		add_action( 'reycore/module/satc/before_markup', [$this, 'rearrange']);
		add_action( 'reycore/module/satc/after_components', [ ReyCore_WooCommerce_ProductNavigation::getInstance(), 'get_navigation' ], 1);

		$this->settings = apply_filters('reycore/module/sticky_add_to_cart_settings', [
			'price_args' => []
		]);
	}

	function rearrange(){
		remove_all_actions('woocommerce_before_add_to_cart_button');
		remove_all_actions('woocommerce_after_add_to_cart_button');
		add_action( 'woocommerce_before_add_to_cart_button', [ ReyCore_WooCommerce_Single::getInstance(), 'wrap_cart_qty' ], 10);
		add_action( 'woocommerce_after_add_to_cart_button', 'reycore_wc__generic_wrapper_end', 5);
	}

	function markup(){

		if( !is_single() ){
			return;
		}

		do_action('reycore/module/satc/before_markup');

		?>
		<div class="rey-stickyAtc-wrapper">
			<div class="rey-stickyAtc">
				<div class="product">
					<h4 class="rey-stickyAtc-title"><?php echo $this->product->get_title() ?></h4>
					<div class="rey-stickyAtc-price"><?php echo $this->product->get_price_html() ?></div>
					<div class="rey-stickyAtc-cart --<?php echo $this->product->get_type() ?>">
						<?php $this->get_add_to_cart() ?>
					</div>
					<?php do_action('reycore/module/satc/after_components'); ?>
				</div>
			</div>
		</div>
		<?php
	}

	function get_add_to_cart(){

		switch($this->product->get_type()):
			case "simple":
				woocommerce_simple_add_to_cart();
				break;
			case "variable":
				$this->get_product_variable();
				break;
			case "external":
				woocommerce_external_add_to_cart();
				break;
			case "grouped":
				$this->get_product_grouped();
				break;
		endswitch;
	}

	function get_price(){
		return wc_price( wc_get_price_to_display( $this->product ), $this->settings['price_args'] );
	}

	function get_open_button( $label ){
		return sprintf('<button class="rey-satc-openBtn btn btn-primary"><span>%1$s</span><span class="satc-price"><span>%4$s</span>%3$s</span> %2$s</button>',
			$label,
			reycore__get_svg_icon__core(['id'=>'reycore-icon-arrow']),
			$this->get_price(),
			_x('From', 'From text in the Sticky add to cart bar, for price in button.', 'rey-core')
		);
	}

	function get_product_variable(){
		echo $this->get_open_button( _x('SELECT OPTIONS', 'Select Options text in the Sticky add to cart bar, for variable products.', 'rey-core') );
		echo '<div class="rey-satc-productForm">';

			// Enqueue variation scripts.
			wp_enqueue_script( 'wc-add-to-cart-variation' );

			// Get Available variations?
			$get_variations = count( $this->product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $this->product );

			// Load the template.
			wc_get_template(
				'single-product/add-to-cart/variable.php',
				array(
					'available_variations' => $get_variations ? $this->product->get_available_variations() : false,
					'attributes'           => $this->product->get_variation_attributes(),
					'selected_attributes'  => $this->product->get_default_attributes(),
				)
			);

		echo '</div>';
	}

	function get_product_grouped(){
		echo $this->get_open_button( _x('SELECT PRODUCTS', 'Select Products text in the Sticky add to cart bar, for grouped products.', 'rey-core') );
		echo '<div class="rey-satc-productForm">';
			woocommerce_grouped_add_to_cart();
		echo '</div>';
	}

	public function enqueue_scripts(){

		wp_enqueue_style( 'reycore-satc-popup', REY_CORE_MODULE_URI . basename(__DIR__) . '/style.css', [], REY_CORE_VERSION );
		wp_enqueue_script( 'reycore-satc-popup', REY_CORE_MODULE_URI . basename(__DIR__) . '/script.js', ['rey-script', 'reycore-scripts', 'rey-woocommerce-script'], REY_CORE_VERSION , true);
	}

	public function script_params($params)
	{
		if( $this->product->is_type('simple') ){
			$params['sticky_add_to_cart__btn_text'] = sprintf('<span class="satc-price">%s</span>', $this->get_price() );
		}

		return $params;
	}

	function add_customizer_options( $field_args ){

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'toggle',
			'settings'    => 'product_page_sticky_add_to_cart',
			'label'       => reycore_customizer__title_tooltip(
				esc_html__( 'Sticky Add to Cart Bar', 'rey-core' ),
				esc_html__( 'This will enable a sticky bar at the bottom of the viewport, which is shown when scrolling past the summary Add to cart button.', 'rey-core' )
			),
			'section'     => $field_args['section'],
			'default'     => false,
			'input_attrs' => [
				'data-control-class' => '--separator-bottom'
			]
		] );

	}

	public function is_enabled() {
		return get_theme_mod('product_page_sticky_add_to_cart', false) === true && $this->product->is_purchasable();
	}

}

new ReyCore_Wc_StickyAddToCart;

endif;
