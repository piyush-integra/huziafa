<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mini Cart
 */
if ( !class_exists('WooCommerce') ) {
    return;
}

if( get_theme_mod('shop_catalog', false) === true ){
	return;
}

$args = get_query_var('rey__header_cart',  [
	'hide_empty' => get_theme_mod('header_cart_hide_empty', 'no'),
]);

$cart_count = is_object( WC()->cart ) ? WC()->cart->get_cart_contents_count() : '';
$cart_layout = get_theme_mod('header_cart_layout', 'bag');
$cart_icon = $cart_holder = '';

if( $cart_layout !== 'disabled' ){
	$cart_icon = reycore__get_svg_icon__core([ 'id'=> 'reycore-icon-' . $cart_layout ]);
}

// Text is deprecated, but keep until v2.0
if( $cart_layout === 'text' ) {
	$cart_text = str_replace( '{{total}}', reycore_wc__cart_total(), get_theme_mod('header_cart_text', 'CART') );
	$cart_holder = sprintf('<span class="rey-headerCart-text">%1$s</span> %2$s', $cart_text, reycore__get_svg_icon__core([ 'id'=> 'reycore-icon-bag' ]));
}
else {

	$cart_holder = $cart_icon;

	if( $cart_text = get_theme_mod('header_cart_text_v2', '') ){
		$cart_text = str_replace( '{{total}}', reycore_wc__cart_total(), $cart_text );
		$cart_holder = sprintf('<span class="rey-headerCart-text-v2">%1$s %2$s</span> %2$s', $cart_text, $cart_icon);
	}
}
?>

<div class="rey-headerCart-wrapper rey-headerIcon <?php echo esc_attr($args['hide_empty']) === 'yes' ? '--hide-empty' : ''; ?>" data-rey-cart-count="<?php echo absint($cart_count); ?>">
	<button data-href="<?php echo esc_url( wc_get_cart_url() ) ?>" class="btn rey-headerCart js-rey-headerCart <?php echo is_cart() || is_checkout() ? '--cart-checkout' : ''; ?>">
        <?php echo $cart_holder; ?>
        <?php reycore_wc__cart_link(); ?>
	</button>
</div>
<!-- .rey-headerCart-wrapper -->
