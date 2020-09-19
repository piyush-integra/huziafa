<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Add Progress Bar to the Cart and Checkout pages.
 */
add_action( 'woocommerce_before_cart', 'reycore_wc__cart_progress' );
add_action( 'woocommerce_before_checkout_form', 'reycore_wc__cart_progress', 5 );

if ( ! function_exists( 'reycore_wc__cart_progress' ) ) {

	/**
	 * More product info
	 * Link to product
	 *
	 * @return void
	 * @since  1.0.0
	 */
	function reycore_wc__cart_progress() {

		if( ! get_theme_mod('cart_checkout_bar_process', true) ){
			return;
		}

		$pid = get_the_ID();
		$active_cart = wc_get_page_id( 'cart' ) == $pid || wc_get_page_id( 'checkout' ) == $pid;
		$active_checkout = wc_get_page_id( 'checkout' ) == $pid;
		?>

		<div class="rey-checkoutBar-wrapper <?php echo get_theme_mod('cart_checkout_bar_icons', 'icon') === 'icon' ? '--icon' : '--numbers'; ?>">
			<ul class="rey-checkoutBar">
				<li class="<?php echo ($active_cart ? '--is-active' : '') ?>">
					<a href="<?php echo get_permalink( wc_get_page_id( 'cart' ) ); ?>">
						<h4>
							<?php echo ($active_cart ? reycore__get_svg_icon(['id' => 'rey-icon-check']) : ''); ?>
							<span><?php
								if( $title_1 = get_theme_mod('cart_checkout_bar_text1_t', '' ) ) {
									echo $title_1;
								}
								else {
									echo esc_html_x('Shopping Bag', 'Checkout bar shopping cart title', 'rey-core');
								}
							?></span>
						</h4>
						<p><?php
							if( $subtitle_1 = get_theme_mod('cart_checkout_bar_text1_s', '' ) ) {
								echo $subtitle_1;
							}
							else {
								echo esc_html_x('View your items', 'Checkout bar shopping cart subtitle', 'rey-core');
							}
						?></p>
					</a>
				</li>
				<li class="<?php echo ($active_checkout ? '--is-active' : '') ?>">
					<a href="<?php echo get_permalink( wc_get_page_id( 'checkout' ) ); ?>">
						<h4>
							<?php echo ($active_checkout ? reycore__get_svg_icon(['id' => 'rey-icon-check']) : ''); ?>
							<span><?php
								if( $title_2 = get_theme_mod('cart_checkout_bar_text2_t', '' ) ) {
									echo $title_2;
								}
								else {
									echo esc_html_x('Shipping and Checkout', 'Checkout bar checkout title', 'rey-core');
								}
							?></span>
						</h4>
						<p><?php
							if( $subtitle_2 = get_theme_mod('cart_checkout_bar_text2_s', '' ) ) {
								echo $subtitle_2;
							}
							else {
								echo esc_html_x('Enter your details', 'Checkout bar checkout subtitle', 'rey-core');
							}
						?></p>
					</a>
				</li>
				<li>
					<div>
						<h4><?php
							if( $title_3 = get_theme_mod('cart_checkout_bar_text3_t', '' ) ) {
								echo $title_3;
							}
							else {
								echo esc_html_x('Confirmation', 'Checkout bar confirmation title', 'rey-core');
							}
						?></h4>
						<p><?php
							if( $subtitle_3 = get_theme_mod('cart_checkout_bar_text3_s', '' ) ) {
								echo $subtitle_3;
							}
							else {
								echo esc_html_x('Review your order!', 'Checkout bar confirmation subtitle', 'rey-core');
							}
						?></p>
					</div>
				</li>
			</ul>
		</div>
		<?php

	}
}


if(!function_exists('reycore_wc__checkout_add_title')):
/**
 * Add title inside order table
 *
 * @since 1.0.0
 **/
function reycore_wc__checkout_add_title()
{
	?>
		<h3 class="order_review_heading"><?php esc_html_e( 'Your order', 'rey-core' ); ?></h3>
	<?php
}
endif;
add_action('woocommerce_checkout_order_review', 'reycore_wc__checkout_add_title', 0);


if(!function_exists('reycore_wc__checkout_distraction_free')):
	/**
	 * Distraction Free Checkout
	 *
	 * @since 1.5.0
	 **/
	function reycore_wc__checkout_distraction_free()
	{
		if (is_checkout() && !is_wc_endpoint_url('order-received') && get_theme_mod('checkout_distraction_free', false) ){
			// disable header
			remove_all_actions('rey/header');
			// adds a logo only
			add_action('rey/content/title', 'reycore__tags_logo_block', 0);
			// adds class
			add_filter('rey/site_content_classes', function($classes){
				return $classes + ['--checkout-distraction-free'];
			});
		}
	}
endif;
add_action('wp', 'reycore_wc__checkout_distraction_free');
