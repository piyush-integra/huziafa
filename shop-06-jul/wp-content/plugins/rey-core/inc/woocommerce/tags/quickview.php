<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


if( !class_exists('ReyCore_WooCommerce_QuickView') ):

/**
 * ReyCore_WooCommerce_QuickView class.
 *
 * @since 1.0.0
 */
class ReyCore_WooCommerce_QuickView {

	private static $_instance = null;

	const ACTION = 'get_quickview_product';

	private function __construct()
	{
		if( ! reycore_wc_get_loop_components('quickview') ){
			return;
		}

		add_action( 'wp_ajax_' . self::ACTION, [ $this, 'get_product'] );
		add_action( 'wp_ajax_nopriv_' . self::ACTION, [ $this, 'get_product'] );
		add_action( 'wp_footer', [ $this, 'panel_markup'], 10 );
		add_action( 'wp_enqueue_scripts', [$this, 'add_scripts']);
		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 10 );

		// force default skin
		add_filter('theme_mod_single_skin', function( $opt ){
			if( $this->check_if_quickview_request() ){
				return 'default';
			}
			return $opt;
		});

		// force vertical gallery
		add_filter('theme_mod_product_gallery_layout', function( $opt ){
			if( $this->check_if_quickview_request() ){
				return 'vertical';
			}
			return $opt;
		});
	}

	function check_if_quickview_request(){

		if( wp_doing_ajax() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] === self::ACTION  ){
			return true;
		}

		return false;
	}

	/**
	 * Filter main script's params
	 *
	 * @since 1.0.0
	 **/
	public function script_params($params)
	{
		$params['quickview_only'] = get_theme_mod('loop_quickview__link_all', false);
		return $params;
	}

	public function add_scripts()
	{
		wp_enqueue_script( 'wc-add-to-cart-variation' );
	}

	/**
	 * Get product
	 *
	 * @since   1.0.0
	 */
	public function get_product()
	{
		if( ! (isset($_POST['id']) && ($pid = absint($_POST['id']))) ){
			wp_send_json_error(esc_html__('Missing product ID.', 'rey-core'));
		}

		global $post, $product;

		$this->fix_page();

		$post = get_post( $pid );
		setup_postdata( $post );

		ob_start();

		$classes = [
			'fit' => '--image-fit-' . get_theme_mod('loop_quickview_image_fit', 'cover')
		];

		?>
		<div class="rey-quickview-mask"></div>
		<div class="rey-quickview-mask"></div>

		<div id="product-<?php the_ID(); ?>" <?php wc_product_class( implode(' ', $classes), $pid ); ?> data-id="<?php the_ID(); ?>">

			<?php
				/**
				 * Hook: woocommerce_before_single_product_summary.
				 *
				 * @hooked woocommerce_show_product_sale_flash - 10
				 * @hooked woocommerce_show_product_images - 20
				 */
				do_action( 'woocommerce_before_single_product_summary' );
			?>

			<div class="summary entry-summary">
				<div class="summary-inner js-scrollbar">
					<?php
						/**
						 * Hook: woocommerce_single_product_summary.
						 *
						 * @hooked woocommerce_template_single_title - 5
						 * @hooked woocommerce_template_single_rating - 10
						 * @hooked woocommerce_template_single_price - 10
						 * @hooked woocommerce_template_single_excerpt - 20
						 * @hooked woocommerce_template_single_add_to_cart - 30
						 * @hooked woocommerce_template_single_meta - 40
						 * @hooked woocommerce_template_single_sharing - 50
						 * @hooked WC_Structured_Data::generate_product_data() - 60
						 */
						do_action( 'woocommerce_single_product_summary' );
					?>
				</div>
			</div>

		</div>
		<?php
		wp_reset_postdata();

		$data = ob_get_clean();

		wp_send_json_success($data);
	}

	public function panel_markup()
	{
		// loop_quickview__panel_style

		?>
		<div class="rey-quickviewPanel woocommerce" id="js-rey-quickviewPanel">
			<div class="rey-quickview-container --<?php echo get_theme_mod('loop_quickview__panel_style', 'curtain'); ?>"></div>
			<button class="btn rey-quickviewPanel-close js-rey-quickviewPanel-close"><?php echo reycore__get_svg_icon(['id' => 'rey-icon-close']) ?></button>
			<div class="rey-lineLoader"></div>
		</div>
		<?php
	}

	private function fix_page(){

		// Include WooCommerce frontend stuff
		wc()->frontend_includes();

		set_query_var('rey__is_quickview', true);

		if( get_theme_mod('shop_catalog', false) == true ){
			add_filter( 'woocommerce_is_purchasable', '__return_false');
			remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
		}

		add_filter('woocommerce_post_class', function($classes){

			if( array_key_exists('product_page_class', $classes) ){
				unset($classes['product_page_class']);
			}

			return $classes;
		});

		// re-load gallery skin
		ReyCore_WooCommerce_ProductGallery_Base::getInstance()->load_current_gallery_type();

		// disable mobile gallery (panel is lg+)
		add_filter('reycore/woocommerce/allow_mobile_gallery', '__return_false');

		// make short desc shorter
		add_filter('reycore_theme_mod_product_short_desc_toggle_v2', '__return_true');

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_breadcrumb', 1 );

		/**
		 * add custom title with link
		 */
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
		add_action( 'woocommerce_single_product_summary', function() {
			echo sprintf( '<h1 class="product_title entry-title"><a href="%s">%s</a></h1>',
				get_the_permalink(),
				get_the_title()
			);
		}, 5 );

		/**
		 * add specifications
		 */
		add_action('woocommerce_single_product_summary', function(){

			if( reycore_wc_get_loop_components('quickview') && get_theme_mod('loop_quickview_specifications', true) ){

				global $product;

				if( !$product ){
					return;
				}

				ob_start();
				do_action( 'woocommerce_product_additional_information', $product );
				$content = ob_get_clean();

				if( ! empty($content) ){
					echo '<div class="rey-qvSpecs">';
					$heading = apply_filters( 'woocommerce_product_additional_information_heading', __( 'Specifications', 'rey-core' ) );
					if ( $heading ) :
						printf('<h2>%s</h2>', esc_html( $heading ));
					endif;
					echo $content;
					echo '</div>';
				}
			}

		}, 100);
	}

	/**
	 * Print quickview button
	 */
	public static function get_button_html( $product_id = '', $button_class = 'button' )
	{

		if( get_theme_mod('loop_quickview__link_all', false) && get_theme_mod('loop_quickview__link_all_hide', false) ){
			return;
		}

		if( $product_id !== '' ){
			$id = $product_id;
		}
		else {

			$product = wc_get_product();

			if ( ! ($product && $id = $product->get_id()) ) {
				return;
			}

		}

		$text = esc_html_x('QUICKVIEW', 'Quickview button text in products listing.', 'rey-core');

		$button_content = $text;
		$type = get_theme_mod('loop_quickview', '1');
		$btn_style = get_theme_mod('loop_quickview_style', 'under');

		if( $type === 'icon' ){
			$button_content = reycore__get_svg_icon__core([ 'id'=>'reycore-icon-' . get_theme_mod('loop_quickview_icon_type', 'eye') ]);
			$button_class .= ' rey-btn--' . $btn_style;
		}

		if( $type === '1' ){
			$button_class .= ' rey-btn--' . $btn_style;
		}

		$btn_html = sprintf(
			'<a href="%5$s" class="%1$s rey-quickviewBtn js-rey-quickviewBtn" data-id="%2$s" title="%3$s">%4$s</a>',
			$button_class,
			esc_attr($id),
			$text,
			$button_content,
			esc_url( get_permalink($id) )
		);

		return apply_filters('reycore/woocommerce/quickview/btn_html', $btn_html, $product);
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_WooCommerce_QuickView
	 */
	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
}
endif;

ReyCore_WooCommerce_QuickView::getInstance();
