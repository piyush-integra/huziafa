<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'woocommerce_template_loop_product_title' ) ):
	/**
	 * Override native function, by adding link into H2 tag.
	 *
	 * Show the product title in the product loop. By default this is an H2.
	 *
	 * @since 1.0.0
	 */
	function woocommerce_template_loop_product_title() {
		echo sprintf(
			'<h2 class="%s"><a href="%s">%s</a></h2>',
			esc_attr( apply_filters( 'woocommerce_product_loop_title_classes', 'woocommerce-loop-product__title' ) ),
			esc_url(get_the_permalink()),
			get_the_title()
		);
	}
endif;


if(!function_exists('reycore_wc__add_account_btn')):
	/**
	 * Add account button and panel markup
	 * @since 1.0.0
	 **/
	function reycore_wc__add_account_btn(){
		if( get_theme_mod('header_enable_account', false) ) {
			reycore__get_template_part('template-parts/woocommerce/header-account');
		}
	}
endif;
add_action('rey/header/row', 'reycore_wc__add_account_btn', 50);


if(!function_exists('reycore_wc__get_account_panel_args')):
	/**
	 * Get account panel options
	 * @since 1.0.0
	 **/
	function reycore_wc__get_account_panel_args( $option = '' ){

		$options = wp_parse_args( get_query_var('rey__header_account'), [
			'enabled' => get_theme_mod('header_enable_account', false),
			'button_type' => get_theme_mod('header_account_type', 'text'),
			'button_text' => get_theme_mod('header_account_text', 'ACCOUNT'),
			'button_text_logged_in' => get_theme_mod('header_account_text_logged_in', ''),
			'icon_type' => get_theme_mod('header_account_icon_type', 'rey-icon-user'),
			'wishlist' =>  get_theme_mod('header_account_wishlist', true) && class_exists('TInvWL_Public_AddToWishlist'),
			'counter' => get_theme_mod('header_account_wishlist_counter', true) && class_exists('TInvWL_Public_AddToWishlist'),
			'show_separately' => true,
			'login_register_redirect' => get_theme_mod('header_account_redirect_type', 'load_menu'),
			'login_register_redirect_url' => get_theme_mod('header_account_redirect_url', ''),
			'ajax_forms' => apply_filters('reycore/header/account/ajax_forms', true),
			'forms' => get_theme_mod('header_account__enable_forms', true)
		]);

		if( !empty($option) && isset($options[$option]) ){
			return $options[$option];
		}

		return $options;
	}
endif;


if(!function_exists('reycore_wc__account_nav_wrap_start')):
	function reycore_wc__account_nav_wrap_start() {
		echo '<div class="woocommerce-MyAccount-navigation-wrapper">';
	}
	add_action('woocommerce_before_account_navigation', 'reycore_wc__account_nav_wrap_start');
	add_action('woocommerce_after_account_navigation', 'reycore_wc__generic_wrapper_end', 20);
endif;

if(!function_exists('reycore_wc__account_custom_nav')):
	/**
	 * Header Account custom menu items
	 *
	 * @since 1.6.3
	 **/
	function reycore_wc__account_custom_nav() {

		if( ! (($menu_items = get_theme_mod('header_account_menu_items', [])) && is_array($menu_items) ) ){
			return;
		} ?>
		<nav class="woocommerce-MyAccount-navigation --custom">
			<ul>
				<?php foreach ( $menu_items as $menu_item ) : ?>
					<li class="myaccount-nav-<?php echo sanitize_title_with_dashes($menu_item['text']) ?>">
						<a href="<?php echo esc_url( $menu_item['url'] ); ?>" target="<?php esc_attr_e($menu_item['target']) ?>"><?php echo esc_html($menu_item['text']) ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav> <?php
	}
	add_action('woocommerce_after_account_navigation', 'reycore_wc__account_custom_nav');
endif;


if(!function_exists('reycore_wc__account_redirect_attrs')):
/**
 * Redirect attributes for account panel containing login register forms
 *
 * @since 1.4.5
 **/
function reycore_wc__account_redirect_attrs()
{
	$args = reycore_wc__get_account_panel_args();

	$redirect_type = $args['login_register_redirect'];
	$redirect_url = $args['login_register_redirect_url'];

	if( $redirect_type === 'myaccount' ){
		$redirect_url = wc_get_page_permalink( 'myaccount' );
	}

	printf( 'data-redirect-type="%s" data-redirect-url="%s" %s',
		esc_attr($redirect_type),
		esc_attr($redirect_url),
		! $args['ajax_forms'] ? 'data-no-ajax' : ''
	);

}
endif;


if(!function_exists('reycore_wc__add_account_panel')):
	/**
	 * Add account button and panel markup
	 * @since 1.0.0
	 **/
	function reycore_wc__add_account_panel(){
		if( reycore_wc__get_account_panel_args('enabled') ) {
			reycore__get_template_part('template-parts/woocommerce/header-account-panel');
		}
	}
endif;
add_action('rey/after_site_wrapper', 'reycore_wc__add_account_panel');


if(!function_exists('reycore_wc__generic_wrapper_end')):
	/**
	 * Ending wrapper
	 *
	 * @since 1.0.0
	 **/
	function reycore_wc__generic_wrapper_end()
	{ ?>
		</div>
	<?php }
endif;


if( !function_exists('reycore_wc__checkout_required_span') ):

	/**
	 * Add the required mark to the terms & comditions text
	 * to maintain it on the same line visually.
	 *
	 * @since 1.0.0
	 **/
	function reycore_wc__checkout_required_span($text)
	{
		return $text . '<span class="required">*</span>';
	}
endif;
add_filter('woocommerce_get_terms_and_conditions_checkbox_text', 'reycore_wc__checkout_required_span');


if(!function_exists('reycore_wc__placeholder_img_src')):
	/**
	 * Placeholder
	 */
	function reycore_wc__placeholder_img_src( $placeholder ) {
		return defined('REY_CORE_PLACEHOLDER') ? REY_CORE_PLACEHOLDER : $placeholder;
	}
endif;
add_filter('woocommerce_placeholder_img_src', 'reycore_wc__placeholder_img_src');
add_filter( 'option_woocommerce_placeholder_image', 'reycore_wc__placeholder_img_src' );


if(!function_exists('reycore_wc__get_product_images_ids')):
	/**
	 * Get product's image ids
	 *
	 * @since 1.0.0
	 **/
	function reycore_wc__get_product_images_ids( $add_main = true )
	{
		$product = wc_get_product();
		$ids = [];

		if( $product && $main_image_id = $product->get_image_id() ){

			if( $add_main ){
				// get main image' id
				$ids[] = $main_image_id;
			}

			// get gallery
			if( $gallery_image_ids = $product->get_gallery_image_ids() ){
				foreach ($gallery_image_ids as $key => $gallery_img_id) {
					$ids[] = $gallery_img_id;
				}
			}
		}

		return $ids;
	}
endif;


if(!function_exists('reycore_wc__add_mobile_nav_link')):
	/**
	 * Adds dashboard (my account) link into Mobile navigation's footer
	 * @since 1.0.0
	 */
	function reycore_wc__add_mobile_nav_link(){

		$show_account_links = true;

		if( get_theme_mod('shop_catalog', false) === true && apply_filters('reycore/catalog_mode/hide_account', false) ){
			$show_account_links = false;
		}

		if( $show_account_links ) {
			reycore__get_template_part('template-parts/woocommerce/header-mobile-navigation-footer-link');
		}
	}
endif;
add_action('rey/mobile_nav/footer', 'reycore_wc__add_mobile_nav_link', 5);


if(!function_exists('reycore_wc__exclude_cats_in_shop_page')):
	/**
	 * Exclude categories from shop page query
	 *
	 * @since 1.2.0
	 **/
	function reycore_wc__exclude_cats_in_shop_page( $q )
	{
		if( ! ($exclude_cats = reycore__get_theme_mod('shop_catalog_page_exclude', [], [
				'translate' => true,
				'translate_post_type' => 'product_cat',
			])) ){
			return;
		}

		if(!is_shop()){
			return;
		}

		$tax_query = (array) $q->get( 'tax_query' );

		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field' => 'slug',
			'terms' => $exclude_cats,
			'operator' => 'NOT IN'
		);

		$q->set( 'tax_query', $tax_query );
	}
endif;
add_action('woocommerce_product_query', 'reycore_wc__exclude_cats_in_shop_page');


if(!function_exists('reycore_wc__format_price_range')):
	/**
	 * Remove dash from grouped products
	 *
	 * @since 1.0.0
	 */
	function reycore_wc__format_price_range( $price, $from, $to ) {

		$min = is_numeric( $from ) ? wc_price( $from ) : $from;
		$max = is_numeric( $to ) ? wc_price( $to ) : $to;

		/* translators: 1: price from 2: price to */
		$price = sprintf(
			esc_html_x( '%1$s %2$s', 'Price range: from-to', 'rey-core' ),
			$min,
			$max
		);

		if( $custom_price_range = get_theme_mod('custom_price_range', '') ){

			$custom_price_range = explode(' ', $custom_price_range);
			$custom_price_range = array_map(function($arr) use ($min, $max){
				$arr = str_replace('{{min}}', $min, $arr);
				$arr = str_replace('{{max}}', $max, $arr);
				return "<span class='__custom-price-range'>{$arr}</span>";
			}, $custom_price_range);

			return implode('', $custom_price_range);
		}


		return $price;
	}
endif;
add_filter('woocommerce_format_price_range', 'reycore_wc__format_price_range', 10, 3);


if(!function_exists('reycore_wc__move_banner')):
	/**
	 * Move WooCommerce banner store
	 *
	 * @since 1.0.0
	 **/
	function reycore_wc__move_banner()
	{
		remove_action( 'wp_footer', 'woocommerce_demo_store' );

		$hook = 'rey/before_site_wrapper';

		if( get_theme_mod('header_layout_type', 'default') !== 'none' ){
			$hook = 'rey/header/content';
		}

		add_action( $hook, 'woocommerce_demo_store', 5 );
	}
endif;
add_action( 'wp', 'reycore_wc__move_banner' );


if(!function_exists('reycore_wc__qty_input_select')):
	/**
	 * Adds the ability to select number on focus.
	 *
	 * @since 1.3.5
	 */
	function reycore_wc__qty_input_select( $classes ) {
		$classes['select'] = '--select-text';
		return $classes;

	}
endif;
add_filter('woocommerce_quantity_input_classes', 'reycore_wc__qty_input_select');


if( ! function_exists( 'reycore_wc__ajax_add_to_cart' ) ):

	function reycore_wc__ajax_add_to_cart() {

		$data = [];

		// Notices
		ob_start();
		wc_print_notices();
		$data['notices'] = ob_get_clean();

		// Mini cart
		ob_start();
		woocommerce_mini_cart();
		$data['fragments']['div.widget_shopping_cart_content'] = sprintf('<div class="widget_shopping_cart_content">%s</div>', ob_get_clean() );
		$data['fragments'] = apply_filters( 'woocommerce_add_to_cart_fragments', $data['fragments']);

		// Cart Hash
		$data['cart_hash'] = apply_filters( 'woocommerce_add_to_cart_hash',
			WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '',
			WC()->cart->get_cart_for_session()
		);

		wp_send_json( $data );
		die();
	}
endif;
add_action( 'wp_ajax_reycore_ajax_add_to_cart', 'reycore_wc__ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_reycore_ajax_add_to_cart', 'reycore_wc__ajax_add_to_cart' );

if(!function_exists('reycore_wc__add_variation_cart_scripts')):
	/**
	 * Add variations add to cart script
	 *
	 * @since 1.4.0
	 **/
	function reycore_wc__add_variation_cart_scripts()
	{
		if( ! get_theme_mod('loop_ajax_variable_products', false) ){
			return;
		}
		// Enqueue variation scripts.
		wp_enqueue_script( 'wc-add-to-cart-variation' );
	}
endif;
add_action( 'wp_enqueue_scripts', 'reycore_wc__add_variation_cart_scripts' );


if( ! function_exists( 'reycore_loop_variable_product_add_to_cart' ) ):
	function reycore_loop_variable_product_add_to_cart() {

		if( ! ( isset($_REQUEST['product_id']) && $product_id = absint($_REQUEST['product_id']) ) ){
			wp_send_json_error( esc_html__('Product ID not found.', 'rey-core') );
		}

		$product = wc_get_product($product_id);

		remove_all_actions('woocommerce_before_add_to_cart_button');
		remove_all_actions('woocommerce_after_add_to_cart_button');

		if( class_exists('ReyCore_WooCommerce_Single') ){
			add_action( 'woocommerce_before_add_to_cart_button', [ ReyCore_WooCommerce_Single::getInstance(), 'wrap_cart_qty' ], 10);
			add_action( 'woocommerce_after_add_to_cart_button', 'reycore_wc__generic_wrapper_end', 5);
		}

		if( $product && $product->is_purchasable() && $product->is_type('variable') ){
			ob_start();

			echo sprintf('<div class="rey-productLoop-variationsForm woocommerce" data-id="%s">', $product_id);
			echo '<div class="product">';
				echo sprintf('<span class="rey-productLoop-variationsForm-close">%s</span>', reycore__get_svg_icon(['id' => 'rey-icon-close']));
				$GLOBALS['product'] = $product;
				woocommerce_variable_add_to_cart();
			echo '</div>';
			echo '</div>';

			$data = ob_get_clean();

			wp_send_json_success( $data );
		}

		wp_send_json_error( esc_html__('Product not purchasable.', 'rey-core') );
	}
endif;
add_action( 'wp_ajax_reycore_loop_variable_product_add_to_cart', 'reycore_loop_variable_product_add_to_cart' );
add_action( 'wp_ajax_nopriv_reycore_loop_variable_product_add_to_cart', 'reycore_loop_variable_product_add_to_cart' );


add_action('init', function(){
	if( wp_doing_ajax() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'reycore_account_forms' ){
		remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'process_login' ), 20 );
		remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'process_registration' ), 20 );
		remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'process_lost_password' ), 20 );
	}
});

if( ! function_exists( 'reycore_account_forms_ajax_process' ) ):
	function reycore_account_forms_ajax_process() {

		if( isset($_POST['action_type']) && ($action_type = $_POST['action_type']) && class_exists('WC_Form_Handler') ):

			$data = [];

			wc_clear_notices();

			ob_start();

			if( 'register' === $action_type ){
				WC_Form_Handler::process_registration();
			}
			elseif( 'login' === $action_type ){
				WC_Form_Handler::process_login();
			}
			elseif( 'forgot' === $action_type ){
				WC_Form_Handler::process_lost_password();
			}

			wc_print_notices();
			$data['notices'] = ob_get_clean();

			wp_send_json_success( $data );

		endif;

		wp_send_json_error( esc_html__('Something went wrong while submitting this form. Please try again!', 'rey-core') );
	}
endif;
add_action( 'wp_ajax_nopriv_reycore_account_forms', 'reycore_account_forms_ajax_process' );


if(!function_exists('reycore_wc__modal_template')):

	function reycore_wc__modal_template(){
		?>
		<script type="text/html" id="tmpl-reycore-modal-tpl">
			<div class="rey-modal {{{data.wrapperClass}}}">
				<div class="rey-modalOverlay"></div>
				<div class="rey-modalInner">
					<button class="rey-modalClose"><?php echo reycore__get_svg_icon(['id' => 'rey-icon-close']) ?></button>
					<div class="rey-modalLoader">
						<div class="rey-lineLoader"></div>
					</div>
					<div class="rey-modalContent {{{data.contentClass}}}"></div>
				</div>
			</div>
		</script>
		<?php
	}
endif;
add_action('wp_footer', 'reycore_wc__modal_template');


if(!function_exists('reycore__woocommerce_filter_js')):
	/**
	 * Filter WC JS
	 *
	 * @since 1.0.0
	 **/
	function reycore__woocommerce_filter_js($js)
	{
		$search_for = '.selectWoo( {';
		$replace_with = '.selectWoo( {';
		$replace_with .= 'containerCssClass: "select2-reyStyles",';
		$replace_with .= 'dropdownCssClass: "select2-reyStyles",';
		$replace_with .= 'dropdownAutoWidth: true,';
		$replace_with .= 'width: "auto",';

		return str_replace($search_for, $replace_with, $js);
	}
endif;
add_filter('woocommerce_queued_js', 'reycore__woocommerce_filter_js');


if(!function_exists('reycore_wc__related_change_cols')):
	/**
	 * Filter related products columns no.
	 *
	 * @since 1.5.0
	 **/
	function reycore_wc__related_change_cols( $args )
	{
		$args['posts_per_page'] = reycore_wc_get_columns('desktop');
		$args['columns'] = reycore_wc_get_columns('desktop');

		return $args;
	}
add_filter('woocommerce_output_related_products_args', 'reycore_wc__related_change_cols', 10);
endif;


if(!function_exists('reycore_wc__track_product_view')):
	/**
	 * Track product views.
	 */
	function reycore_wc__track_product_view() {

		if ( ! is_singular( 'product' ) ) {
			return;
		}

		if( ! apply_filters('reycore/woocommerce/track_product_view', true) ){
			return;
		}

		global $post;

		if ( empty( $_COOKIE['woocommerce_recently_viewed'] ) ) { // @codingStandardsIgnoreLine.
			$viewed_products = array();
		} else {
			$viewed_products = wp_parse_id_list( (array) explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) ); // @codingStandardsIgnoreLine.
		}

		// Unset if already in viewed products list.
		$keys = array_flip( $viewed_products );

		if ( isset( $keys[ $post->ID ] ) ) {
			unset( $viewed_products[ $keys[ $post->ID ] ] );
		}

		$viewed_products[] = $post->ID;

		if ( count( $viewed_products ) > 15 ) {
			array_shift( $viewed_products );
		}

		// Store for session only.
		wc_setcookie( 'woocommerce_recently_viewed', implode( '|', $viewed_products ) );
	}
	add_action( 'template_redirect', 'reycore_wc__track_product_view', 20 );
endif;


if(!function_exists('reycore_wc__fix_variable_sale_product_prices')):
	/**
	 * Fix variable products on sale price display
	 *
	 * @since 1.6.6
	 **/
	function reycore_wc__fix_variable_sale_product_prices($price, $product)
	{
		if( ! get_theme_mod('fix_variable_product_prices', false) ){
			return $price;
		}

		if( ! $product->is_on_sale() ){
			return $price;
		}

		$show_from_in_price = false;

		// Regular Price
		$regular_prices = [
			$product->get_variation_regular_price( 'min', true ),
			$product->get_variation_regular_price( 'max', true )
		];

		$flexible_regular_prices = $regular_prices[0] !== $regular_prices[1];

		if( $show_from_in_price ){
			$regular_price = $flexible_variables_prices ? sprintf( '<span class="woocommerce-Price-from">%1$s</span> %2$s', esc_html__('From:', 'woocommerce'), wc_price( $regular_prices[0] ) ) : wc_price( $regular_prices[0] );
		}
		else {
			$regular_price = wc_price( $regular_prices[0] );
		}

		// Sale Price
		$prod_prices = [
			$product->get_variation_price( 'min', true ),
			$product->get_variation_price( 'max', true )
		];

		if( $show_from_in_price ){
			$prod_price = $prod_prices[0] !== $prod_prices[1] ? sprintf( '<span class="woocommerce-Price-from">%1$s</span> %2$s', esc_html__('From:', 'woocommerce'), wc_price( $prod_prices[0] ) ) : wc_price( $prod_prices[0] );
		}
		else {
			$prod_price = wc_price( $prod_prices[0] );
		}

		if ( $prod_price !== $regular_price ) {
			$prod_price = sprintf('<del>%s</del> <ins>%s</ins>', $regular_price . $product->get_price_suffix(), $prod_price . $product->get_price_suffix() );
		}

		return $prod_price;
	}
	add_filter( 'woocommerce_variable_price_html', 'reycore_wc__fix_variable_sale_product_prices', 10, 2 );
endif;

add_action('woocommerce_before_single_product_summary', function(){
	set_query_var('rey_is_single_product', true);
}, 0);

add_action('woocommerce_after_single_product_summary', function(){
	set_query_var('rey_is_single_product', false);
}, 0);
