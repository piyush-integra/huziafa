<?php
// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

if( !class_exists('ReyCore_WooCommerce_Variations') ):

class ReyCore_WooCommerce_Variations
{
	private static $_instance = null;

	const TRANSIENT_ACTIVE_ATTR_DATA_WVS = 'wvs_get_wc_attribute_taxonomy_%s';
	const TRANSIENT_PRODUCT_VARIATIONS = 'rey_product_variations_';
	const TRANSIENT_TERMS_COLOR = 'rey_terms_color';
	const TRANSIENT_TERMS_IMAGE = 'rey_terms_image';

	private $store_transients = true;

	private $color_attributes = [];
	private $image_attributes = [];

	private function __construct()
	{

		if(defined('REY_DEV_MODE') && REY_DEV_MODE){
			$this->store_transients = false;
		}

		add_action( 'init', [$this, 'wvs_pro_support'], 11 );
		add_action( 'woocommerce_init', [$this, 'register_hook'] );
		add_action( 'customize_save_woocommerce_loop_variation', [$this, 'customizer_clear_transients']);

		// Clean transients
		add_action( 'woocommerce_delete_product_transients', [$this, 'clear_transients']);
		add_action( 'save_post', [$this, 'clear_product_transient'] );
		add_action( 'woocommerce_attribute_updated', [$this, 'remove_attribute_transients'], 20, 2 );
		add_action( 'woocommerce_attribute_deleted', [$this, 'remove_attribute_transients'], 20, 2 );
		add_action( 'woocommerce_attribute_added', [$this, 'remove_attribute_transients'], 20, 2 );
		add_action( 'reycore/demo_import/attributes', [$this, 'demo_import_clear_caches'] );
		add_action( 'create_term', [$this, 'clear_term_transient'], 10, 3 );
		add_action( 'edit_term', [$this, 'clear_term_transient'], 10, 3 );
		add_action( 'delete_term', [$this, 'clear_term_transient'], 10, 3 );

		add_action( 'wvs_wc_attribute_taxonomy_meta_added', [$this, 'color_attr_add_multiple_fields'], 10, 2);
		add_filter( 'wvs_variable_item', [$this, 'color_variable_item'], 10, 5);
		add_filter( 'wvs_advanced_setting_fields', [$this, 'add_custom_size_fields']);
		add_filter( 'rey/css_styles', [$this, 'add_sizes_styles'] );
		add_filter( 'wvs_available_attributes_types', [$this, 'available_attributes_types'] );
	}

	public function register_hook(){

		$position = get_theme_mod('woocommerce_loop_variation_position', 'after');

		if( $position === 'after' ) {
			add_action( 'woocommerce_after_shop_loop_item', function(){
				do_action('reycore/variations_html');
			}, 198 );
		}
		elseif( $position === 'before' ) {
			add_action( 'woocommerce_after_shop_loop_item', function(){
				do_action('reycore/variations_html');
			}, 5 );
		}
		elseif( $position === 'first' ) {
			add_action( 'woocommerce_before_shop_loop_item_title', function(){
				do_action('reycore/variations_html');
			}, 20 );
		}
	}

	function do_variation()
	{
		global $product;

		$attr_data = $this->get_variation_attribute_data();

		if( !empty($attr_data) && $product )
		{
			if( get_theme_mod('woocommerce_loop_variation_force_regular', false) && ($regular_attribute = $this->get_regular_attribute_data( $product, $attr_data )) ){
				echo $regular_attribute;
			}

			elseif( $variation_data = $this->get_variation_data( $product, $attr_data ) ){
				$this->get_variations_html( $variation_data );
			}

		}
	}

	function get_active_variation_type(){
		return get_theme_mod('woocommerce_loop_variation', 'disabled');
	}

	/**
	 * Get the selected visible attribute data
	 *
	 * @since 1.0.0
	 */
	function get_variation_attribute_data()
	{
		$selected_variation = $this->get_active_variation_type();

		if( $selected_variation === 'disabled' || $this->wvs_pro_archive_is_enabled() ){
			return [];
		}

		if( function_exists('rey__maybe_disable_obj_cache') ){
			rey__maybe_disable_obj_cache();
		}

		$data['name'] = $selected_variation;
		$data['taxonomy_name'] = wc_attribute_taxonomy_name( $data['name'] );
		$data['type'] = 'select';

		if( function_exists('wvs_get_wc_attribute_taxonomy') ){
			$attr_tax = wvs_get_wc_attribute_taxonomy( $data['taxonomy_name'] );
			if( isset($attr_tax->attribute_type) ){
				$data['type'] =  $attr_tax->attribute_type;
			}
		}

		return $data;
	}

	/**
	 * Based on WooCommerce's get_available_variation()
	 *
	 * @since 1.0.0
	 */
	function get_variation_data( $product, $attr_data )
	{
		if( function_exists('rey__maybe_disable_obj_cache') ){
			rey__maybe_disable_obj_cache();
		}

		if( ! $product->is_type( 'variable' ) ){
			return;
		}

		$prod_id = $product->get_id();

		$data = get_transient( self::TRANSIENT_PRODUCT_VARIATIONS . $prod_id );

		if( ! $data ){

			$data = [];

			$attributes = $product->get_variation_attributes();
			$options    = isset($attributes[ $attr_data['taxonomy_name'] ]) ? $attributes[ $attr_data['taxonomy_name'] ]: [];
			$selected   = $product->get_variation_default_attribute( $attr_data['taxonomy_name'] );

			if ( ! empty( $options ) ) {
				if ( taxonomy_exists( $attr_data['taxonomy_name'] ) ) {

					// Get terms if this is a taxonomy - ordered. We need the names too.
					$terms = wc_get_product_terms(
						$prod_id,
						$attr_data['taxonomy_name'],
						[
							'fields' => 'all',
						]
					);

					foreach ( $terms as $term ) {
						if ( in_array( $term->slug, $options, true ) ) {
							$data['attributes'][] = [
								'value' => $term->slug,
								'selected' => sanitize_title( $selected ) === $term->slug,
								'name' => apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attr_data['taxonomy_name'], $product )
							];
						}
					}
				} else {
					foreach ( $options as $option ) {
						$data['attributes'][] = [
							'value' => $option,
							'selected' => sanitize_title( $option ) === $selected,
							'name' => apply_filters( 'woocommerce_variation_option_name', $option, null, $attr_data['taxonomy_name'], $product )
						];
					}
				}

				foreach ( $product->get_children() as $key => $child_id ) {

					$variation = wc_get_product( $child_id );

					// Hide out of stock variations if 'Hide out of stock items from the catalog' is checked.
					if ( ! $variation || ! $variation->exists() || ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && ! $variation->is_in_stock() ) ) {
						continue;
					}

					$data['product_variations'][] = $this->get_available_variation( $variation, $product, $attr_data );
				}
			}

			if( $this->store_transients ){
				set_transient( self::TRANSIENT_PRODUCT_VARIATIONS . $prod_id, $data, WEEK_IN_SECONDS );
			}
		}

		$limit = get_theme_mod('woocommerce_loop_variation_limit', 0);

		if( is_array($data) && $limit > 0 && isset( $data['attributes'] ) ){
			$data['attributes'] = array_slice($data['attributes'], 0, $limit);
		}

		return $data;
	}

	/**
	 * Trimmed version of WooCommerce' get_available_variation()
	 * @since 1.0.0
	 */
	function get_available_variation( $variation, $product, $attr_data )
	{
		if ( is_numeric( $variation ) ) {
			$variation = wc_get_product( $variation );
		}

		if ( ! $variation instanceof WC_Product_Variation ) {
			return false;
		}

		// See if prices should be shown for each variation after selection.
		$show_variation_price = apply_filters( 'woocommerce_show_variation_price', $variation->get_price() === '' || $product->get_variation_sale_price( 'min' ) !== $product->get_variation_sale_price( 'max' ) || $product->get_variation_regular_price( 'min' ) !== $product->get_variation_regular_price( 'max' ), $product, $variation );

		// Filter 'woocommerce_hide_invisible_variations' to optionally hide invisible variations (disabled variations and variations with empty price).
		$hide_invisible_option = function_exists('woo_variation_swatches') && woo_variation_swatches()->get_option( 'attribute-behavior' ) === 'hide';
		$hide_invisible = apply_filters( 'woocommerce_hide_invisible_variations', $hide_invisible_option, $product->get_id() , $variation ) && ! $variation->variation_is_visible();

		$available_variation = [
			'image_src'            => wp_get_attachment_image_src( absint($variation->get_image_id()), apply_filters( 'woocommerce_thumbnail_size', 'woocommerce_thumbnail' ) ),
			'price_html'           => $show_variation_price ? '<span class = "price">' . $variation->get_price_html() . '</span>': '',
			'variation_id'         => $variation->get_id(),
			'attributes'           => $variation->get_variation_attributes(),
			'variation_is_visible' => $variation->variation_is_visible(),
			'hidden'               => $hide_invisible,

			// 'variation_is_active' => $variation->variation_is_active(),
			// 'image_id'       => $variation->get_image_id(),
			// 'availability_html'     => wc_get_stock_html( $variation ),
			// 'backorders_allowed'    => $variation->backorders_allowed(),
			// 'dimensions'            => $variation->get_dimensions( false ),
			// 'dimensions_html'       => wc_format_dimensions( $variation->get_dimensions( false ) ),
			// 'display_price'         => wc_get_price_to_display( $variation ),
			// 'display_regular_price' => wc_get_price_to_display( $variation, array( 'price' => $variation->get_regular_price() ) ),
			// 'image'                 => wc_get_product_attachment_props( $variation->get_image_id() ),
			// 'is_downloadable'       => $variation->is_downloadable(),
			// 'is_in_stock'           => $variation->is_in_stock(),
			// 'is_purchasable'        => $variation->is_purchasable(),
			// 'is_sold_individually'  => $variation->is_sold_individually() ? 'yes' : 'no',
			// 'is_virtual'            => $variation->is_virtual(),
			// 'max_qty'               => 0 < $variation->get_max_purchase_quantity() ? $variation->get_max_purchase_quantity() : '',
			// 'min_qty'               => $variation->get_min_purchase_quantity(),
			// 'sku'                   => $variation->get_sku(),
			// 'variation_description' => wc_format_content( $variation->get_description() ),
			// 'weight'                => $variation->get_weight(),
			// 'weight_html'           => wc_format_weight( $variation->get_weight() ),
		];

		return apply_filters( 'reycore/woocommerce_available_variation', $available_variation, $product, $variation );
	}

	function get_terms_transients( $type = '' ){

		$transients = apply_filters('reycore/woocommerce/variations/terms_transients', [
			'color' => self::TRANSIENT_TERMS_COLOR,
			'image' => self::TRANSIENT_TERMS_IMAGE,
		]);

		if( !empty($type) && isset($transients[$type]) ){
			return $transients[$type];
		}

		return $transients;
	}

	function get_terms_by_attribute_type( $attribute_type )
	{
		$attribute_taxonomies = wc_get_attribute_taxonomies();
		$taxonomy_terms = [];

		if ( $attribute_taxonomies ) :
			foreach ($attribute_taxonomies as $tax) :

				$taxonomy_name = wc_attribute_taxonomy_name( $tax->attribute_name );

				if ( $tax->attribute_type === $attribute_type && taxonomy_exists( $taxonomy_name ) ) :
					$taxonomy_terms = array_merge( get_terms( $taxonomy_name, 'hide_empty=0' ), $taxonomy_terms );
				endif;
			endforeach;
		endif;

		return $taxonomy_terms;
	}

	/**
	 * Gets a regular attribute data, regardless if
	 * product is variable
	 *
	 * @since 1.3.9
	 */
	function get_regular_attribute_data( $product, $attr_data ){

		if( empty($attr_data) ){
			return;
		}

		$all_attributes = $product->get_attributes();

		if( isset( $all_attributes[$attr_data['taxonomy_name']] ) && $attribute = $all_attributes[$attr_data['taxonomy_name']] ){

			$attribute_values   = wc_get_product_terms( $product->get_id(), $attribute->get_name(), [ 'fields' => 'all' ] );

			$values = [];

			foreach ( $attribute_values as $attribute_value ) {
				$values[] = esc_html( $attribute_value->name );
			}

			if( !empty($values) && $attribute->get_visible() ){
				return sprintf(
					'<div class="rey-productAttribute">' . apply_filters('reycore/woocommerce/loop_attribute_display', '%s: %s') . '</div>',
					wc_attribute_label( $attribute->get_name() ),
					implode( ', ', $values )
				);
			}
		}
	}

	/**
	 * Get & Cache Color attributes
	 * @return array
	 */
	function get_color_attributes()
	{
		if( function_exists('rey__maybe_disable_obj_cache') ){
			rey__maybe_disable_obj_cache();
		}

		if( !empty( $this->color_attributes ) ){
			return $this->color_attributes;
		}

		if( $this->store_transients ){
			$this->color_attributes = get_transient( $this->get_terms_transients('color') );
		}

		if( ! $this->color_attributes ){

			$terms = $this->get_terms_by_attribute_type('color');

			$colors = [];

			if( !empty($terms) ) {
				foreach( $terms as $key => $attr ) {

					$term_meta = get_term_meta( $attr->term_id );

					$color = array_filter($term_meta, function($i, $v){
						return $v == 'product_attribute_color' || $v == 'product_attribute_color_2' || $v == 'product_attribute_color_2_img' ;
					}, ARRAY_FILTER_USE_BOTH);

					$color_values = [];

					if( isset($color['product_attribute_color'][0]) && !empty($color['product_attribute_color'][0]) &&
						isset($color['product_attribute_color_2'][0]) && !empty($color['product_attribute_color_2'][0]) ){
						$color_values['color_1'] = $color['product_attribute_color'][0];
						$color_values['color_2'] = $color['product_attribute_color_2'][0];
					}

					if( isset($color['product_attribute_color_2_img'][0]) && !empty($color['product_attribute_color_2_img'][0]) ){
						$color_values['color_img'] = $color['product_attribute_color_2_img'][0];
					}

					if( isset($color['product_attribute_color'][0]) && empty($color_values) ) {
						$color_values = $color['product_attribute_color'][0];
					}

					if( !empty($color_values) ) {
						$colors[$attr->term_id] = [
							'color' => $color_values,
							'slug' => $attr->slug
						];
					}

					// set transient
					if( $this->store_transients && !empty($colors) ){
						set_transient( $this->get_terms_transients('color'), $colors, MONTH_IN_SECONDS );
					}
				}
			}

			$this->color_attributes = $colors;
		}

		return $this->color_attributes;
	}

	/**
	 * Get single color based on slug
	 *
	 * @since 1.0.0
	 */
	function get_attribute_color( $slug )
	{
		$color = '';
		$colors = $this->get_color_attributes();
		array_walk($colors, function($key) use(&$color, $slug){
			if( $key['slug'] === $slug ){
				$color = $key['color'];
			}
		});

		return $color;
	}

	public static function parse_attribute_color($attribute, $attribute_color = ''){

		if( empty($attribute_color) ){
			return '';
		}

		$swatch_data = '';

		if( is_array($attribute_color) ){

			// as image
			if( isset($attribute_color['color_img']) && !empty($attribute_color['color_img']) ) {

				$image = wp_get_attachment_image_src( absint($attribute_color['color_img']), 'thumbnail' );

				$swatch_data = sprintf(
					'<img data-slug="%2$s" alt="%1$s" src="%3$s" width="%4$d" height="%5$d" />',
					esc_html( $attribute['name'] ),
					esc_attr( $attribute['value'] ),
					esc_url( $image[ 0 ] ),
					$image[ 1 ],
					$image[ 2 ]
				);
			}
			// dual color
			elseif(
				isset($attribute_color['color_1']) && !empty($attribute_color['color_1']) &&
				isset($attribute_color['color_2']) && !empty($attribute_color['color_2'])
			){
				$swatch_data = sprintf(
					'<span title="%1$s" data-slug="%4$s" style="background: linear-gradient(135deg, %2$s 50%%, %3$s 50%%, %3$s 50%% );"></span>',
					esc_html( $attribute['name'] ),
					esc_attr( $attribute_color['color_1'] ),
					esc_attr( $attribute_color['color_2'] ),
					esc_attr( $attribute['value'] )
				);
			}
		}
		// normal color
		else {
			$swatch_data = sprintf(
				'<span style="background-color:%1$s" data-slug="%2$s" title="%3$s"></span>',
				esc_attr( $attribute_color ),
				esc_attr( $attribute['value'] ),
				esc_html( $attribute['name'] )
			);
		}

		return $swatch_data;
	}

	/**
	 * Get & Cache Image attributes
	 * @return array
	 */
	function get_image_attributes()
	{
		if( function_exists('rey__maybe_disable_obj_cache') ){
			rey__maybe_disable_obj_cache();
		}

		if( !empty( $this->image_attributes ) ){
			return $this->image_attributes;
		}

		if( $this->store_transients ){
			$this->image_attributes = get_transient( $this->get_terms_transients('image') );
		}

		if( ! $this->image_attributes ){

			$terms = $this->get_terms_by_attribute_type('image');
			$images = [];

			if( !empty($terms) ) {

				foreach( $terms as $key => $attr ) {
					$term_meta = get_term_meta( $attr->term_id );

					$image = array_filter($term_meta, function($i, $v){
						return $v == 'product_attribute_image';
					}, ARRAY_FILTER_USE_BOTH);

					if( isset($image['product_attribute_image'][0]) ) {
						$images[$attr->term_id] = [
							'image' => $image['product_attribute_image'][0],
							'slug' => $attr->slug
						];
					}

					// set transient
					if( $this->store_transients && !empty($images) ){
						set_transient( $this->get_terms_transients('image'), $images, MONTH_IN_SECONDS );
					}
				}
			}

			$this->image_attributes = $images;
		}

		return $this->image_attributes;
	}

	function get_attribute_image( $slug )
	{
		$image = '';
		$image_size = 'thumbnail';

		$images = $this->get_image_attributes();

		if( function_exists('woo_variation_swatches') ){
			$image_size = woo_variation_swatches()->get_option( 'attribute_image_size' );
		}

		array_walk($images, function($key) use(&$image, $slug, $image_size){
			if( $key['slug'] === $slug ){
				$image = wp_get_attachment_image_src( absint($key['image'] ), $image_size);
				$image = $image[0];
			}
		});

		return $image;
	}


	function get_variations_html( $variation_data )
	{

		if( ! (isset($variation_data['product_variations']) && isset($variation_data['attributes'])) ){
			return;
		}

		$variations_json = wp_json_encode( $variation_data['product_variations'] );
		$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );

		$attr_data = $this->get_variation_attribute_data();

		echo '<div
			class="rey-productVariations rey-productVariations--' . esc_attr($attr_data['type']) . ' rey-productVariations--pos-'. get_theme_mod('woocommerce_loop_variation_position', 'after') .'"
			data-attribute-type="' . esc_attr($attr_data['type']) . '"
			data-product_variations="' . $variations_attr . '"
			data-attribute-name="attribute_' . esc_attr($attr_data['taxonomy_name']) . '"
		>';

		switch( $attr_data['type'] ):
			case "color":
				echo '<ul>';
				foreach ($variation_data['attributes'] as $attribute) {

					$attribute_color = $this->get_attribute_color( $attribute['value'] );

					$swatch_tag = self::parse_attribute_color($attribute, $attribute_color);

					printf( '<li class="%s">%s</li>',
						$attribute['selected'] ? '--active' : '',
						$swatch_tag
					);
				}
				echo '</ul>';
				break;

			case "image":
				echo '<ul>';
				foreach ($variation_data['attributes'] as $attribute) {
					printf( '<li class="%3$s"><span style="background-image:url(%1$s)" data-slug="%2$s" title="%4$s"></span></li>',
						esc_attr( $this->get_attribute_image( $attribute['value'] ) ),
						esc_attr( $attribute['value'] ),
						$attribute['selected'] ? '--active' : '',
						esc_html( $attribute['name'] )
					);
				}
				echo '</ul>';
				break;

			case "button":
				echo '<ul>';
				foreach ($variation_data['attributes'] as $attribute) {
					printf( '<li class="%3$s"><span data-slug="%2$s">%1$s</span></li>',
						esc_html( $attribute['name'] ),
						esc_attr( $attribute['value'] ),
						$attribute['selected'] ? '--active' : ''
					);
				}
				echo '</ul>';
				break;

			case "select":
				echo '<select>';
				foreach ($variation_data['attributes'] as $attribute) {
					printf( '<option value="%2$s" %3$s>%1$s</option>',
						esc_html( $attribute['name'] ),
						esc_attr( $attribute['value'] ),
						$attribute['selected'] ? 'selected' : ''
					);
				}
				echo '</select>';
				break;
		endswitch;

		echo '</div>';
	}

	public static function get_attributes_list(){

		$attrs = [];

		foreach( wc_get_attribute_taxonomies() as $attribute ) {
			$attribute_name = $attribute->attribute_name;
			$attrs[$attribute_name] = $attribute->attribute_label;
		}

		return $attrs;
	}

	/**
	 * Adds extra color field and image field,
	 * for Color attribute.
	 *
	 * @since 1.3.7
	 */
	function color_attr_add_multiple_fields($product_attr, $product_attr_type){

		if( $product_attr_type !== 'color' ){
			return;
		}

		$fields = wvs_taxonomy_meta_fields();

		if( ! isset($fields['color']) || ! isset($fields['image']) ){
			return;
		}

		if( isset($fields['color'][0]) ){
			$new_color_field = $fields['color'][0];
			$new_color_field['label'] = esc_html__('Second Color', 'rey-core');
			$new_color_field['id'] = $fields['color'][0]['id'] . '_2';
			$fields['color'][0] = $new_color_field;

			// add image
			if( isset($fields['image'][0]) ){
				$new_image_field = $fields['image'][0];
				$new_image_field['label'] = esc_html__('Image instead of color', 'rey-core');
				$new_image_field['id'] = $fields['color'][0]['id'] . '_img';
				$fields['color'][] = $new_image_field;
			}
		}

		woo_variation_swatches()->add_term_meta( $product_attr, 'product', $fields[ $product_attr_type ] );
	}

	/**
	 * Adds dual color and image for color swatches.
	 *
	 * @since 1.3.7
	 */
	function color_variable_item($data, $type, $options, $args, $saved_attribute){

		if( $type === 'color' ){

			$product   = $args[ 'product' ];
			$attribute = $args[ 'attribute' ];
			$new_data      = '';

			if ( ! empty( $options ) ) {
				if ( $product && taxonomy_exists( $attribute ) ) {
					$terms = wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'all' ) );
					$name  = uniqid( wc_variation_attribute_name( $attribute ) );
					foreach ( $terms as $term ) {
						if ( in_array( $term->slug, $options ) ) {
							$selected_class = ( sanitize_title( $args[ 'selected' ] ) == $term->slug ) ? 'selected' : '';
							$tooltip        = trim( apply_filters( 'wvs_variable_item_tooltip', $term->name, $term, $args ) );

							$tooltip_html_attr = ! empty( $tooltip ) ? sprintf( 'data-wvstooltip="%s"', esc_attr( $tooltip ) ) : '';

							if ( wp_is_mobile() ) {
								$tooltip_html_attr .= ! empty( $tooltip ) ? ' tabindex="2"' : '';
							}

							$swatch_data = false;

							$color = sanitize_hex_color( get_term_meta( $term->term_id, 'product_attribute_color', true ) );
							$color_2 = sanitize_hex_color( get_term_meta( $term->term_id, 'product_attribute_color_2', true ) );
							$color_img = absint(get_term_meta( $term->term_id, 'product_attribute_color_2_img', true ));

							if( $color_img ){

								$image_size    = woo_variation_swatches()->get_option( 'attribute_image_size' );
								$image         = wp_get_attachment_image_src( $color_img, apply_filters( 'wvs_product_attribute_image_size', $image_size ) );

								$swatch_data = sprintf(
									'<img class="variable-item-color-img" alt="%s" src="%s" width="%d" height="%d" />',
									esc_attr( $term->name ),
									esc_url( $image[ 0 ] ),
									$image[ 1 ],
									$image[ 2 ]
								);
							}

							elseif( $color && $color_2 ){

								$swatch_data = sprintf(
									'<span class="variable-item-span variable-item-span-%1$s" style="background: linear-gradient(135deg, %2$s 50%%, %3$s 50%%, %3$s 50%% );"></span>',
									esc_attr( $type ),
									esc_attr( $color ),
									esc_attr( $color_2 )
								);
							}

							elseif( $color ){

								$swatch_data = sprintf(
									'<span class="variable-item-span variable-item-span-%1$s" style="background-color:%2$s;"></span>',
									esc_attr( $type ),
									esc_attr( $color )
								);

							}

							if( $swatch_data ){
								$new_data .= sprintf(
									'<li %1$s class="variable-item %2$s-variable-item %2$s-variable-item-%3$s %4$s" title="%5$s" data-value="%3$s">%6$s</li>',
									$tooltip_html_attr,
									esc_attr( $type ),
									esc_attr( $term->slug ),
									esc_attr( $selected_class ),
									esc_html( $term->name ),
									$swatch_data
								);
							}
						}
					}
				}
			}

			if( $new_data ){
				$data = $new_data;
			}
		}

		return $data;
	}

	/**
	 * Adds custom sizing for Rey swatches
	 *
	 * @since 1.3.7
	 */
	function add_custom_size_fields( $fields ){

		$fields[] = [
			'id'      => 'rey_width',
			'type'    => 'number',
			'title'   => esc_html__( 'Width (Rey)', 'rey-core' ),
			'desc'    => esc_html__( 'Variation item width for custom Rey styles, in single product page. Enable plugin default stylesheet to disable this option.', 'rey-core' ),
			'default' => '',
			'min'     => 10,
			'max'     => 200,
			'suffix'  => 'px'
		];

		$fields[] = [
			'id'      => 'rey_height',
			'type'    => 'number',
			'title'   => esc_html__( 'Height (Rey)', 'rey-core' ),
			'desc'    => esc_html__( 'Variation item height for custom Rey styles, in single product page. Enable plugin default stylesheet to disable this option.', 'rey-core' ),
			'default' => '',
			'min'     => 10,
			'max'     => 200,
			'suffix'  => 'px'
		];

		return $fields;
	}

	/**
	 * Adds custom sizes for product page swatches
	 *
	 * @since 1.3.7
	 */
	function add_sizes_styles( $styles ){

		if( ! class_exists('Woo_Variation_Swatches') ){
			return $styles;
		}

		$width     = woo_variation_swatches()->get_option( 'rey_width' );
		$height    = woo_variation_swatches()->get_option( 'rey_height' );

		if( $width || $height ){

			$css_style = '.woo-variation-swatches-stylesheet-disabled .variable-items-wrapper .variable-item:not(.radio-variable-item) {';

				if( $width ) {
					$css_style .= sprintf('width:%spx;', $width);
					$css_style .= sprintf('min-width:%spx;', $width);
				}

				if( $height ) {
					$css_style .= sprintf('height:%spx;', $height);
				}

			$css_style .= '}';

			if( $width ) {
				$css_style .= sprintf('.woo-variation-swatches-stylesheet-disabled .woo-variation-swatches-style-squared .button-variable-item { min-width : %spx; }', $width);
			}

			$styles['rey_wcvs'] = $css_style;
		}

		return $styles;
	}

	function wvs_pro_archive_is_enabled(){
		if (
			class_exists( 'Woo_Variation_Swatches_Pro' ) && function_exists('woo_variation_swatches') &&
			woo_variation_swatches()->get_option( 'show_on_archive' ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Adds compatibility with WooCommerce Variation Swatches PRO
	 *
	 * @since 1.0.0
	 */
	function wvs_pro_support()
	{
		if ( $this->wvs_pro_archive_is_enabled() ) {

			remove_action( 'woocommerce_after_shop_loop_item', 'wvs_pro_archive_variation_template', 30 );
			remove_action( 'woocommerce_after_shop_loop_item', 'wvs_pro_archive_variation_template', 7 );

			$position = trim( woo_variation_swatches()->get_option( 'archive_swatches_position' ) );

			if( $position === 'after' ) {
				add_action( 'woocommerce_after_shop_loop_item', 'wvs_pro_archive_variation_template', 998 );
			}
			elseif( $position === 'before' ) {
				add_action( 'woocommerce_after_shop_loop_item', 'wvs_pro_archive_variation_template', 5 );
			}

			add_filter( 'wp_get_attachment_image_attributes', function ( $attr ) {
				$attr[ 'class' ] .= ' wp-post-image';
				return $attr;
			} );
		}
	}

	function available_attributes_types($types){

		if( isset($types['color']['preview']) ){
			$types[ 'color' ]['preview'] = [$this, 'color_variation_attribute_preview'];
		}

		return $types;
	}

	function color_variation_attribute_preview( $term_id, $attribute, $fields ) {

		$key   = $fields[ 0 ][ 'id' ];

		$color_1 = sanitize_hex_color( get_term_meta( $term_id, $key, true ) );
		$color_2 = sanitize_hex_color( get_term_meta( $term_id, $key . '_2', true ) );
		$color_img = absint( get_term_meta( $term_id, $key . '_2_img', true ) );

		$swatch_data = false;

		if( $color_img ){

			$image  = wp_get_attachment_image_src( $color_img, 'thumbnail' );

			$swatch_data = sprintf(
				'<img class="variable-item-color-img" src="%s" width="%d" height="%d" />',
				esc_url( $image[ 0 ] ),
				$image[ 1 ],
				$image[ 2 ]
			);
		}

		elseif( $color_1 && $color_2 ){

			$swatch_data = sprintf(
				'<span style="background: linear-gradient(135deg, %1$s 50%%, %2$s 50%%, %2$s 50%% );"></span>',
				esc_attr( $color_1 ),
				esc_attr( $color_2 )
			);
		}

		elseif( $color_1 ){

			$swatch_data = sprintf(
				'<span style="background-color:%s;"></span>',
				esc_attr( $color_1 )
			);
		}

		printf( '<div class="wvs-preview wvs-color-preview">%s</div>', $swatch_data );
	}


	/**
	 * Clear some transients on Customizer Publish
	 *
	 * @since 1.0.0
	 */
	function customizer_clear_transients( $setting )
	{
		if( ! method_exists($setting, 'value') ){
			return;
		}

		$value = $setting->value();
		$post_value = $setting->post_value();

		if( $value !== $post_value && $post_value !== 'disabled'){
			// clear transients
			$this->clear_transients();
		}
	}

	function clear_transients( $post_id = 0 ){

		if( function_exists('rey__maybe_disable_obj_cache') ){
			rey__maybe_disable_obj_cache();
		}

		// Transient data to clear with a fixed name which may be stale after product updates.
		$transients_to_clear = [
			sprintf( self::TRANSIENT_ACTIVE_ATTR_DATA_WVS, wc_attribute_taxonomy_name($this->get_active_variation_type()) )
		];

		$transients_to_clear = array_merge($transients_to_clear, $this->get_terms_transients());

		foreach ( $transients_to_clear as $transient ) {
			delete_transient( $transient );
		}

		if ( $post_id > 0 ) {
			// Transient names that include an ID - since they are dynamic they cannot be cleaned in bulk without the ID.
			$post_transient_names = array(
				self::TRANSIENT_PRODUCT_VARIATIONS
			);
			foreach ( $post_transient_names as $transient ) {
				delete_transient( $transient . $post_id );
			}
		}
	}

	function clear_product_transient( $post_id ){

		if( function_exists('rey__maybe_disable_obj_cache') ){
			rey__maybe_disable_obj_cache();
		}

		delete_transient( self::TRANSIENT_PRODUCT_VARIATIONS . $post_id );
	}

	function remove_attribute_transients( $attribute_id, $attribute ){

		$attribute_name = $attribute;

		if( isset($attribute[ 'attribute_name' ]) ){
			$attribute_name = $attribute[ 'attribute_name' ];
		}

		$transients = $this->get_terms_transients();

		if( array_key_exists($attribute_name, $transients) ){
			delete_transient( $transients[ $attribute_name ] );
		}
	}


	function clear_term_transient($term_id, $tt_id, $taxonomy) {

		if( function_exists('rey__maybe_disable_obj_cache') ){
			rey__maybe_disable_obj_cache();
		}

		$transients = $this->get_terms_transients();
		$attribute_taxonomies = wc_get_attribute_taxonomies();
		$taxonomy_terms = [];

		if ( $attribute_taxonomies ) :
			foreach ($attribute_taxonomies as $tax) :
				$taxonomy_name = wc_attribute_taxonomy_name( $tax->attribute_name );
				if( array_key_exists($tax->attribute_type, $transients) ){
					delete_transient( $transients[ $tax->attribute_type ] );
				}
			endforeach;
		endif;
	}

	function demo_import_clear_caches(){
		delete_transient( 'wc_attribute_taxonomies' );
		delete_transient( sprintf( self::TRANSIENT_ACTIVE_ATTR_DATA_WVS, wc_attribute_taxonomy_name($this->get_active_variation_type()) ) );
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_WooCommerce_Variations
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

ReyCore_WooCommerce_Variations::getInstance();
