<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


if(!function_exists('reycore_wc__add_cart_into_header')):
	/**
	 * Add Mini Cart to Header
	 */
	function reycore_wc__add_cart_into_header(){
		if( get_theme_mod('shop_catalog', false) != true && get_theme_mod('header_enable_cart', true) ){
			reycore__get_template_part('template-parts/woocommerce/header-shopping-cart');
		}
	}
endif;
add_action('rey/header/row', 'reycore_wc__add_cart_into_header', 40);


if(!function_exists('reycore_wc__add_cart_panel')):
	/**
	 * Add Cart Panel (triggered by header)
	 * @since 1.0.8
	 */
	function reycore_wc__add_cart_panel(){
		if( get_theme_mod('shop_catalog', false) != true && get_theme_mod('header_enable_cart', true) ){
			reycore__get_template_part('template-parts/woocommerce/header-shopping-cart-panel');
		}
	}
endif;
add_action('rey/after_site_wrapper', 'reycore_wc__add_cart_panel');


if ( ! function_exists( 'reycore_wc__cart_link' ) ) {
	/**
	 * Cart Counter
	 * Displayed a link to the cart including the number of items present
	 *
	 * @return void
	 * @since  1.0.0
	 */
	function reycore_wc__cart_link() {
		$cart_contents = is_object( WC()->cart ) ? WC()->cart->get_cart_contents_count() : '';
		?>
			<span class="rey-headerCart-nb">
				<?php echo sprintf( '%d', $cart_contents ); ?>
			</span>
		<?php
	}
}

if ( ! function_exists( 'reycore_wc__cart_total' ) ) {
	/**
	 * Cart total
	 *
	 * @since  1.4.5
	 */
	function reycore_wc__cart_total() {
		return sprintf('<span class="rey-headerCart-textTotal">%s</span>', is_object( WC()->cart ) ? WC()->cart->get_cart_total() : '');
	}
}


/**
 * Cart fragment
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'reycore_wc__cart_icon_fragment' ) ) {
	/**
	 * Cart Fragments
	 * Ensure cart contents update when products are added to the cart via AJAX
	 *
	 * @param  array $fragments Fragments to refresh via AJAX.
	 * @return array            Fragments to refresh via AJAX
	 */
	function reycore_wc__cart_icon_fragment( $fragments ) {

		ob_start();
		reycore_wc__cart_link();
		$fragments['.rey-headerCart-nb'] = ob_get_clean();

		ob_start();
		echo reycore_wc__cart_total();
		$fragments['.rey-headerCart-textTotal'] = ob_get_clean();

		ob_start();
		reycore_wc__cart_link();
		$fragments['.rey-cartPanel-title span'] = ob_get_clean();

		$fragments['div.widget_shopping_cart_content'] = str_replace(
			'button wc-forward',
			'button wc-forward button--cart',
			$fragments['div.widget_shopping_cart_content']
		);

		return $fragments;
	}
}

if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.3', '>=' ) ) {
	add_filter( 'woocommerce_add_to_cart_fragments', 'reycore_wc__cart_icon_fragment' );
} else {
	add_filter( 'add_to_cart_fragments', 'reycore_wc__cart_icon_fragment' );
}


if(!function_exists('reycore_wc__minicart_before_cart_items')):
	/**
	 * Wrap mini cart items
	 *
	 * @since 1.3.7
	 **/
	function reycore_wc__minicart_before_cart_items()
	{
		echo '<div class="woocommerce-mini-cart-inner">';
	}
endif;
add_action('woocommerce_before_mini_cart_contents', 'reycore_wc__minicart_before_cart_items', 0);


if(!function_exists('reycore_wc__minicart_after_cart_items')):
	/**
	 * Wrap mini cart items
	 *
	 * @since 1.3.7
	 **/
	function reycore_wc__minicart_after_cart_items()
	{
		echo '</div>';
	}
	endif;
add_action('woocommerce_mini_cart_contents', 'reycore_wc__minicart_after_cart_items', 999);


if(!function_exists('reycore_wc__minicart_custom_content_when_empty')):
	/**
	 * Adds content into Cart Panel when empty
	 *
	 * @since 1.4.3
	 **/
	function reycore_wc__minicart_custom_content_when_empty()
	{
		if( WC()->cart->is_empty() && ($header_cart_gs = get_theme_mod('header_cart_gs', 'none')) && $header_cart_gs !== 'none' ){
			echo ReyCore_GlobalSections::do_section( $header_cart_gs );
		}
	}
	endif;
add_action('woocommerce_after_mini_cart', 'reycore_wc__minicart_custom_content_when_empty');


if(!function_exists('reycore_wc__show_shipping_minicart')):
	/**
	 * Show Shipping in minicart
	 *
	 * @since 1.6.3
	 **/
	function reycore_wc__show_shipping_minicart()
	{
		if( ! get_theme_mod('header_cart_show_shipping', false) ){
			return;
		}

		$total = WC()->cart->get_shipping_total();
		$currency = get_woocommerce_currency_symbol();

		$currency_position = get_option('woocommerce_currency_pos');

		if ($currency_position === 'left') {
			$left = $currency;
			$right = $total;
		}
		elseif ($currency_position === 'left_space') {
			$left = $currency . ' ';
			$right = $total;
		}
		elseif ($currency_position === 'right') {
			$left = $total;
			$right = $currency;
		}
		elseif ($currency_position === 'right_space') {
			$left = $total . ' ';
			$right = $currency;
		}
		else {
			$left = $total;
			$right = $currency;
		}

		printf(
			'<span class="minicart-shipping"><strong>%1$s</strong><span>%2$s%3$s</span></span>',
			esc_html__( 'Shipping', 'rey-core' ),
			$left,
			$right
		);
	}
	add_action('woocommerce_widget_shopping_cart_total', 'reycore_wc__show_shipping_minicart', 15);
endif;


if(!function_exists('reycore_wc__cart_wrap_name')):
	/**
	 * Change cart remove text
	 *
	 * @since 1.0.0
	 */
	function reycore_wc__cart_wrap_name($html) {
		return sprintf('<div class="woocommerce-mini-cart-item-title">%s</div>', $html);
	}
	add_filter('woocommerce_cart_item_name', 'reycore_wc__cart_wrap_name');
endif;

if(!function_exists('reycore_wc__mini_cart_elementorpro_template_active')):
	/**
	 * Check if Elementor PRO mini cart template is loaded
	 *
	 * @since 1.6.7
	 **/
	function reycore_wc__mini_cart_elementorpro_template_active()
	{
		return 'yes' === get_option( 'elementor_use_mini_cart_template', 'no' );
	}
endif;


if(!function_exists('reycore_wc__mini_cart_quantity')):
	/**
	 * Add quantity in Mini-Cart
	 *
	 * @since 1.6.6
	 **/
	function reycore_wc__mini_cart_quantity($html, $cart_item, $cart_item_key)
	{
		if ( ! get_theme_mod('header_cart_show_qty', true) ) {
			return $html;
		}

		if ( reycore_wc__mini_cart_elementorpro_template_active() ) {
			return $html;
		}

		$product_id   = absint( $cart_item['product_id'] );
		$variation_id = absint( $cart_item['variation_id'] );

		// Ensure we don't add a variation to the cart directly by variation ID.
		if ( 'product_variation' === get_post_type( $product_id ) ) {
			$variation_id = $product_id;
			$product_id   = wp_get_post_parent_id( $variation_id );
		}

		$product = wc_get_product( $variation_id ? $variation_id : $product_id );

		// prevent showing quantity controls
		if ( $product->is_sold_individually() ) {
			return $html;
		}

		$product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $cart_item['data'] ), $cart_item, $cart_item_key );
		// $product_price = sprintf( '%s &times; %s', $cart_item['quantity'], $product_price );

		$defaults = [
			'input_value'  	=> $cart_item['quantity'],
			'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
			'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
			'step' 		=> apply_filters( 'woocommerce_quantity_input_step', '1', $product ),
		];

		$quantity = woocommerce_quantity_input( $defaults, $cart_item['data'], false );

		$loader = '<div class="__loader"></div>';

		return '<div class="quantity-wrapper">'. $quantity  . $product_price .'</div>' . $loader;

	}
	add_filter( 'woocommerce_widget_cart_item_quantity', 'reycore_wc__mini_cart_quantity', 10, 3 );
endif;


if(!function_exists('reycore_wc__update_minicart_qty')):
	/**
	 * update cart
	 *
	 * @since 1.6.6
	 **/
	function reycore_wc__update_minicart_qty()
	{
		if( reycore_wc__mini_cart_elementorpro_template_active() ){
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$cart_item_key = wc_clean( isset( $_POST['cart_item_key'] ) ? wp_unslash( $_POST['cart_item_key'] ) : '' );
		$cart_item_qty = wc_clean( isset( $_POST['cart_item_qty'] ) ? absint( $_POST['cart_item_qty'] ) : '' );

		if ( $cart_item_key && $cart_item_qty ) {

			WC()->cart->set_quantity($cart_item_key, $cart_item_qty, $refresh_totals = true);

			ob_start();

			woocommerce_mini_cart();

			$mini_cart = ob_get_clean();

			$data = array(
				'fragments' => apply_filters(
					'woocommerce_add_to_cart_fragments',
					array(
						'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
					)
				),
				'cart_hash' => WC()->cart->get_cart_hash(),
			);

			wp_send_json( $data );

		} else {
			wp_send_json_error();
		}
	}
	add_action('wp_ajax_rey_update_minicart', 'reycore_wc__update_minicart_qty');
	add_action('wp_ajax_nopriv_rey_update_minicart', 'reycore_wc__update_minicart_qty');
endif;

/**
 * Enable Qty controls on mini-cart & disable select (if enabled)
 * @since 1.6.6
 */
add_action('woocommerce_before_mini_cart_contents', function(){
	add_filter('theme_mod_single_atc_qty_controls', '__return_true');
	add_filter('reycore/woocommerce/quantity_field/can_add_select', '__return_false');
});
add_action('woocommerce_mini_cart_contents', function(){
	remove_filter('theme_mod_single_atc_qty_controls', '__return_true');
	remove_filter('reycore/woocommerce/quantity_field/can_add_select', '__return_false');
});
