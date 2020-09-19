<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_RelatedProducts') ):

class ReyCore_WooCommerce_RelatedProducts
{

	const META_KEY = '_rey_related_ids';

	protected $product_ids = [];

	public function __construct() {
		add_action('init', [$this, 'init']);
	}

	function init(){
		add_action( 'wp', [$this, 'disable_section']);
		add_action( 'woocommerce_product_related_products_heading', [$this, 'change_title']);
		add_action( 'woocommerce_product_options_related', [$this, 'add_extra_product_edit_options'] );
		add_action( 'woocommerce_update_product', [$this, 'process_extra_product_edit_options'] );
		add_filter( 'woocommerce_related_products', [$this, 'filter_related_products'], 10, 3 );
		add_filter( 'woocommerce_output_related_products_args', [$this, 'filter_related_products_args'] );
	}

	public function disable_section(){
		if( ! $this->is_enabled() ){
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
		}
	}

	public function change_title( $title ){
		if( $this->is_enabled() && ($related_title = get_theme_mod('single_product_page_related_title', '')) ){
			return $related_title;
		}
		return $title;
	}

	function add_extra_product_edit_options(){

		if( ! $this->custom_enabled() ){
			return;
		}

		?>
		<div class="options_group">

			<p class="form-field hide_if_grouped hide_if_external">
				<label for="rey_related_products"><?php esc_html_e( 'Related products', 'rey-core' ); ?></label>
				<select class="wc-product-search" multiple="multiple" style="width: 50%;" id="rey_related_products" name="<?php echo self::META_KEY ?>[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'rey-core' ); ?>" data-action="woocommerce_json_search_products_and_variations" data-exclude="<?php echo intval( get_the_ID() ); ?>">
					<?php
					$product_ids = $this->get_products_ids();

					if( is_array($product_ids) ):
						foreach ( $product_ids as $product_id ) {
							$product = wc_get_product( $product_id );
							if ( is_object( $product ) ) {
								echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
							}
						}
					endif;
					?>
				</select> <?php echo wc_help_tip( __( 'Select custom related products.', 'rey-core' ) ); // WPCS: XSS ok. ?>
			</p>

		</div>

		<?php
	}

	public function process_extra_product_edit_options( $product_id ) {
		if ( $this->custom_enabled() && isset($_POST[ self::META_KEY ]) && $related = wc_clean( $_POST[ self::META_KEY ] ) ) {
			update_post_meta( $product_id, self::META_KEY, $related );
		}
	}

	public function filter_related_products($related_posts, $product_id, $args) {

        if ( $this->custom_enabled() && $custom_related = $this->get_products_ids($product_id)) {
			if( get_theme_mod('single_product_page_related_custom_replace', true) ){
				$related_posts = $custom_related;
			}
			else {
				$related_posts = array_unique( $custom_related + $related_posts );
				add_filter('woocommerce_product_related_posts_shuffle', '__return_false');
			}
        }

        return $related_posts;
    }

	public function filter_related_products_args($args) {

		if( $cols = get_theme_mod('single_product_page_related_columns', '') ){
			$args['columns'] = $cols;
		}

		if( $per_page = get_theme_mod('single_product_page_related_per_page', '') ){
			$args['posts_per_page'] = $per_page;
		}

		// options:
		// - carousel y/n - forces 8 items
		// - columns to show

        if ( $this->custom_enabled() && get_theme_mod('single_product_page_related_custom_replace', true) ) {
			$args['orderby'] = 'ID';
        }

        return $args;
	}

	public function get_products_ids( $product_id = '' ){

		if( !empty( $this->product_ids ) ){
			return $this->product_ids;
		}

		if( empty($product_id) ){
			$product_id = get_the_ID();
		}

		return get_post_meta($product_id, self::META_KEY, true);
	}

	public function custom_enabled(){
		return $this->is_enabled() && get_theme_mod('single_product_page_related_custom', false);
	}

	public function is_enabled(){
		return get_theme_mod('single_product_page_related', true);
	}

}

new ReyCore_WooCommerce_RelatedProducts;

endif;
