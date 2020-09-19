<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_Brands') ):

class ReyCore_WooCommerce_Brands
{

	private static $_instance = null;

	public $has_archive = false;

	private $brand_terms = [];

	/**
	 * ReyCore_WooCommerce_Brands constructor.
	 */
	private function __construct()
	{
		// bail if already using WooCommerce Brands
		if( class_exists( 'WC_Brands' ) ){
			return;
		}

		add_action( 'admin_init', [$this, 'admin_filters'] );
		add_action( 'woocommerce_single_product_summary', [$this, 'product_page_show_brands'], 6 );
		add_filter( 'woocommerce_structured_data_product', [$this, 'structured_data_brands'], 10, 2 );
		add_filter( 'saswp_modify_product_schema_output', [$this, 'saswp_structured_data_brands'], 10 );
		add_filter( 'wp', [$this, 'brand_tax_has_archive'], 10 );
		add_filter( 'facebook_for_woocommerce_integration_prepare_product', [$this, 'facebook_catalog_brand'], 10, 2 );
		// add_filter('reycore/kirki_fields/field=search__include', [$this, 'add_to_search'], 20);
	}

	function add_to_search($field){
		$key = $this->brand_attribute(true);
		$field['choices'][$key] = esc_html__( 'Brand names', 'rey-core' );
		return $field;
	}

	function structured_data_brands( $markup, $product ){

		if( ! is_array($markup) ){
			return $markup;
		}

		$markup['brand'] = [
			"@type" => "Thing",
			'name' => $this->get_brand_name()
		];

		return $markup;
	}

	/**
	 * Compatibility with `Schema & Structured Data for WP` plugin
	 * @since 1.3.0
	 */
	function saswp_structured_data_brands( $data ){

		if( isset($data['brand']) ){
			$data['brand']['name'] = $this->get_brand_name();
		}

		return $data;
	}

	/**
	 * Get Brand Attribute Name
	 *
	 * @since 1.0.0
	 **/
	function brand_attribute( $wc_tax = false )
	{
		$brand = apply_filters('reycore/woocommerce/brand_attribute', 'brand');

		if( $wc_tax ){
			return wc_attribute_taxonomy_name( $brand );
		}

		return wc_clean( $brand );
	}

	function get_brands( $field = '' ){

		$product = wc_get_product();

		if( ! $product ){
			return;
		}

		$taxonomy = wc_attribute_taxonomy_name( $this->brand_attribute() );
		$terms = get_the_terms($product->get_id(), $taxonomy);

		if($terms && !empty($field) ){
			return wp_list_pluck($terms, $field);
		}

		return $terms;
	}

	/**
	 * Get Product Brand
	 *
	 * @since 1.0.0
	 */
	function get_brand_name( $id = false ){

		if( $custom_brand = apply_filters('reycore/structured_data/brand', false) ){
			return $custom_brand;
		}

		$product = wc_get_product( $id );

		if ( $product && $brand = $product->get_attribute( $this->brand_attribute() ) ) {
			return $brand;
		}

		return false;
	}

	function brand_tax_has_archive(){
		$attribute_taxonomies = wc_get_attribute_taxonomies();

		foreach ( $attribute_taxonomies as $attribute ) {
			if( $attribute->attribute_name === $this->brand_attribute() ){
				$this->has_archive = (bool) $attribute->attribute_public;
			}
		}
	}

	public function brands_tax_exists(){
		return taxonomy_exists( wc_attribute_taxonomy_name( $this->brand_attribute() ) );
	}

	/**
	 * Show brand attribute in loop & product
	 *
	 * @since 1.0.0
	 */
	function get_brand_link($brand = []){

		if ( empty($brand) ) {
			return '';
		}

		$brand_attribute_name = $this->brand_attribute();

		if( $this->has_archive && ($term_link = get_term_link( $brand, $brand_attribute_name )) && is_string($term_link) ){
			return esc_url( $term_link );
		}

		$shop_url = get_permalink( wc_get_page_id( 'shop' ) );

		// Default attribute filtering
		$brand_url = sprintf( '%1$s?filter_%2$s=%3$s', $shop_url, $brand_attribute_name, $brand->slug );

		return esc_url( apply_filters('reycore/woocommerce/brands/url', $brand_url, $brand_attribute_name, $shop_url ) );
	}

	/**
	 * Get Brands HTML
	 *
	 * @since 1.0.0
	 */
	function get_brands_html(){
		if ( $this->brands_tax_exists() && $brands = $this->get_brands() ) {

			$links = '';

			foreach ($brands as $brand) {
				$links .= sprintf('<a href="%s">%s</a>', $this->get_brand_link( $brand ), $brand->name);
			}

			echo apply_filters('reycore/woocommerce/brands/html', sprintf( '<div class="rey-brandLink">%s</div>', $links));
		}
	}

	/**
	 * Show brand attribute in loop
	 *
	 * @since 1.0.0
	 */
	function loop_show_brands(){
		return $this->get_brands_html();
	}

	/**
	 * Show brand attribute in product
	 *
	 * @since 1.0.0
	 */
	function product_page_show_brands(){
		return $this->get_brands_html();
	}

	/**
	 * Append brand to facebook catalog
	 * if using Facebook official plugin
	 *
	 * @since 1.6.8
	 */
	function facebook_catalog_brand( $product_data, $id ) {

		if( $brand_name = $this->get_brand_name($id) ) {
			$product_data['brand'] = $brand_name;
		}

		return $product_data;
	}


	function admin_filters(){

		if( ! $this->brands_tax_exists() ){
			return;
		}

		$this->brand_tax_has_archive();

		add_filter('manage_product_posts_columns', [$this, 'add_column']);
		add_action('manage_posts_custom_column', [$this, 'populate_column'], 10, 2);

		add_action( 'restrict_manage_posts', [$this, 'admin__add_filter_list'], 20 );
		add_action( 'pre_get_posts', [$this, 'admin__filter_products_list'] );

		// bulk edit
		add_action('woocommerce_product_bulk_edit_end',  [$this, 'admin__bulk_edit_add_brands_field']);
		add_action('woocommerce_product_bulk_edit_save',  [$this, 'admin__bulk_save'], 99);
	}

	function add_column( $column_array ) {
		$column_array['brand'] = esc_html__('Brand', 'rey-core');
		return $column_array;
	}

	function populate_column( $column_name, $post_id ) {
		if( $column_name === 'brand' ) {
			echo $this->get_brand_name($post_id);
		}
	}


	function admin__add_filter_list( $post_type, $args = [] ){

		if( $post_type !== 'product' ) {
			return;
		}

		$args = wp_parse_args($args, [
			'name'             => 'rey_brand_term',
			'by'               => 'slug',
			'check_active'     => true,
			'hide_empty'       => true,
			'unbranded_option' => true
		]);

		$brands = get_terms([
			'taxonomy'   => wc_attribute_taxonomy_name( $this->brand_attribute() ),
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => $args['hide_empty'],
			'parent'     => 0,
		]);

		$brands_options = [];

		$active = '';

		if( $args['check_active'] && isset($_GET[$args['name']]) && $active_brand = wc_clean($_GET[$args['name']]) ){
			$active = $active_brand;
		}

		foreach ($brands as $key => $brand) {
			if( isset($brand->{$args['by']}) && isset($brand->name) ){
				$brands_options[] = sprintf('<option value="%1$s" %3$s>%2$s</option>', $brand->{$args['by']}, $brand->name, selected($active, $brand->{$args['by']}, false));
			}
		}

		if( !empty($brands_options) ){
			echo sprintf('<select name="%s">', $args['name']);
			echo sprintf('<option value="">%s</option>', esc_html__('Select a brand', 'rey-core'));

			if( $args['unbranded_option'] ){
				echo sprintf('<option value="-1" %2$s>%1$s</option>', esc_html__('Unbranded', 'rey-core'), selected($active, '-1', false));
			}

			echo implode( '', $brands_options );
			echo '</select>';
		}
	}

	function admin__filter_products_list( $query ){
		global $pagenow;

		if ( ! ($query->is_admin && $pagenow === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'product') ) {
			return $query;
		}

		if( ! (isset($_GET['rey_brand_term']) && $active_brand = wc_clean($_GET['rey_brand_term'])) ){
			return $query;
		}

		$tax_query = [
			'relation' => 'AND',
		];

		$query_data = [
			'taxonomy'         => wc_attribute_taxonomy_name( $this->brand_attribute() ),
			'field'            => 'slug',
			'terms'            => $active_brand,
			'operator'         => 'IN',
			'include_children' => false,
		];

		if( $active_brand === '-1' ){
			$query_data = [
				'taxonomy'         => wc_attribute_taxonomy_name( $this->brand_attribute() ),
				'operator'         => 'NOT EXISTS',
			];
		}

		$query->tax_query->queries[] = $query_data;

		foreach ( $query->tax_query->queries as $q ) {
			$tax_query[] = $q;
		}

		$query->set('tax_query', $tax_query);

	}

	function admin__bulk_edit_add_brands_field() {
		?>
		<div class="inline-edit-group">
		  <label class="alignleft">
			 <span class="title"><?php _e( 'Brand', 'rey-core' ); ?></span>
			 <span class="input-text-wrap">
				<?php
					$this->admin__add_filter_list('product', [
						'name'             => 'rey_brand_term_bulk',
						'by'               => 'term_id',
						'check_active'     => false,
						'hide_empty'       => false,
						'unbranded_option' => false,
					]);
				?>
			 </span>
			</label>
		</div>
		<?php
	}

	function admin__bulk_save( $product ){

		if( ! (isset($_REQUEST['rey_brand_term_bulk']) && $brand = absint($_REQUEST['rey_brand_term_bulk'])) ){
			return;
		}

		$brand_tax_name = wc_attribute_taxonomy_name( $this->brand_attribute() );
		$product_id = $product->get_id();

		$meta_attributes = get_post_meta( $product->get_id(), '_product_attributes', true );

		/**
		 * WC_Product_Variable_Data_Store_CPT
		 * read_attributes
		 */
		if ( ! empty( $meta_attributes ) && is_array( $meta_attributes ) ) {

			$attributes   = array();
			$force_update = false;
			$has_brand = false;

			foreach ( $meta_attributes as $meta_attribute_key => $meta_attribute_value ) {
				$meta_value = array_merge(
					array(
						'name'         => '',
						'value'        => '',
						'position'     => 0,
						'is_visible'   => 0,
						'is_variation' => 0,
						'is_taxonomy'  => 0,
					),
					(array) $meta_attribute_value
				);

				// Check if is a taxonomy attribute.
				if ( ! empty( $meta_value['is_taxonomy'] ) ) {
					if ( ! taxonomy_exists( $meta_value['name'] ) ) {
						continue;
					}
					$id      = wc_attribute_taxonomy_id_by_name( $meta_value['name'] );
					$options = wc_get_object_terms( $product->get_id(), $meta_value['name'], 'term_id' );
				} else {
					$id      = 0;
					$options = wc_get_text_attributes( $meta_value['value'] );
				}

				// tell only to modify it
				if( $meta_value['name'] === $brand_tax_name ){
					$options = array_map('absint', wc_get_text_attributes( $brand ));
					$has_brand = true;
					$force_update = true;
				}

				$attribute = new WC_Product_Attribute();
				$attribute->set_id( $id );
				$attribute->set_name( $meta_value['name'] );
				$attribute->set_options( $options );
				$attribute->set_position( $meta_value['position'] );
				$attribute->set_visible( $meta_value['is_visible'] );
				$attribute->set_variation( $meta_value['is_variation'] );
				$attributes[] = $attribute;
			}

			// doesn't have brand, add it
			if( ! $has_brand ){
				$b_attribute = new WC_Product_Attribute();
				$b_attribute->set_id( wc_attribute_taxonomy_id_by_name( $brand_tax_name ) );
				$b_attribute->set_name( $brand_tax_name );
				$b_attribute->set_options( array_map('absint', wc_get_text_attributes( $brand )) );
				$b_attribute->set_position( count($attributes) + 1 );
				$b_attribute->set_visible( true );
				$b_attribute->set_variation( false );
				$attributes[] = $b_attribute;
				$force_update = true;
			}

			$product->set_attributes( $attributes );

			if ( $force_update ) {
				$data_store   = WC_Data_Store::load( 'product' );
				$data_store->update( $product );
			}
		}

	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_WooCommerce_Brands
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

ReyCore_WooCommerce_Brands::getInstance();
