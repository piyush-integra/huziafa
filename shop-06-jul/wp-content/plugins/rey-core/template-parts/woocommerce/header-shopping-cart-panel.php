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

$title = sprintf( '<h3 class="rey-cartPanel-title">%s (<span>%d</span>)</h3>',
	esc_html_x('SHOPPING BAG', 'Shopping bag title in cart panel', 'rey-core'),
	is_object( WC()->cart ) ? WC()->cart->get_cart_contents_count() : ''
);

$close = sprintf( '<button class="btn rey-cartPanel-close rey-sidePanel-close js-rey-sidePanel-close">%s</button>',
	reycore__get_svg_icon(['id' => 'rey-icon-close'])
);

$header = '<div class="rey-cartPanel-header">' . $title . $close . '</div>';
?>

<div class="rey-cartPanel-wrapper rey-sidePanel js-rey-cartPanel">
	<?php the_widget( 'WC_Widget_Cart', 'title=', [
		'before_widget' => '<div class="widget rey-cartPanel %s">' . $header,
	]); ?>
</div>
