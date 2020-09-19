<?php

/**
 * This is used to store all the information about wooCommerce store products
 *
 * @since      1.0.0
 * @package    Woo_Feed
 * @subpackage Woo_Feed/includes
 * @author     Ohidul Islam <wahid@webappick.com>
 */
class Woo_Feed_Products {
	
	/**
	 * Contain all parent product information for the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $parent Contain all parent product information for the plugin.
	 */
	public $parent;
	
	/**
	 * Contain all child product information for the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $parent Contain all child product information for the plugin.
	 */
	public $child;
	
	/**
	 * The parent id of current product.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $parentID The current product's Parent ID.
	 */
	public $parentID;
	/**
	 * The child id of current product.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $parentID The current product's child ID.
	 */
	public $childID;
	
	/**
	 * The Variable that contain all products.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array $productsList Products list array.
	 */
	public $productsList;
	
	/**
	 * Hold Modified Filter Condition
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array filter condition array.
	 */
	public $feedRule;
	public $ids_in = array();
	public $ids_not_in = array();
	public $categories;
	public $vendors;
	public $WPFP_WPML = false;
	public $currentLang = false;
	public $pi;
	public $idExist = array();
	
	/**
	 * Post meta prefix for dropdown item
	 * @since 3.1.18
	 * @var string
	 */
	const POST_META_PREFIX = 'wf_cattr_';
	/**
	 * Product Attribute (taxonomy & local) Prefix
	 * @since 3.1.18
	 * @var string
	 */
	const PRODUCT_ATTRIBUTE_PREFIX = 'wf_attr_';
	/**
	 * Product Taxonomy Prefix
	 * @since 3.1.18
	 * @var string
	 */
	const PRODUCT_TAXONOMY_PREFIX = 'wf_taxo_';
	/**
	 * Product Category Mapping Prefix
	 * @since 3.1.18
	 * @var string
	 */
	const PRODUCT_CATEGORY_MAPPING_PREFIX = 'wf_cmapping_';
	/**
	 * Product Dynamic Attribute Prefix
	 * @since 3.1.18
	 * @var string
	 */
	const PRODUCT_DYNAMIC_ATTRIBUTE_PREFIX = 'wf_dattribute_';
	/**
	 * WordPress Option Prefix
	 * @since 3.1.18
	 * @var string
	 */
	const WP_OPTION_PREFIX = 'wf_option_';
	
	const PRODUCT_EXTRA_ATTRIBUTE_PREFIX = 'wf_extra_';
	
	const PRODUCT_ATTRIBUTE_MAPPING_PREFIX = 'wp_attr_mapping_';
	
	public function wooProductQuery( $arg ) {
		global $wpdb;
		
		$categories = '';
		if ( isset( $arg['category'] ) && '' != $arg['category'] ) {
			$catIds = array();
			
			foreach ( $arg['category'] as $key => $value ) {
				$catid = get_term_by( 'slug', sanitize_text_field( $value ), 'product_cat' ); // sanitization ok
				array_push( $catIds, $catid->term_id );
			}
			
			$categories = implode( ',', $catIds );
			$categories = "AND $wpdb->term_taxonomy.term_id IN ($categories)"; // sanitization ok
		}
		
		$variations = '';
		if ( isset( $this->feedRule['is_variations'] ) && 'n' != $this->feedRule['is_variations'] ) {
			$variations = "OR $wpdb->posts.post_type = 'product_variation'"; // sanitization ok
		}
		
		$limit  = '';
		$offset = '';
		if ( '' != $arg['limit'] && '-1' != $arg['limit'] && $arg['limit'] > 0 ) {
			$limit = absint( $arg['limit'] ); // sanitization ok
			$limit = "LIMIT $limit";
			
			
			if ( '' != $arg['offset'] && '-1' != $arg['offset'] ) {
				$offset = absint( $arg['offset'] ); // sanitization ok
				$offset = "OFFSET $offset";
			}
		}
		
		
		$stock = '';
		if ( isset( $arg['stock_status'] ) && 'instock' == $arg['stock_status'] ) {
			$stock = "AND $wpdb->postmeta.meta_value='instock'"; // sanitization ok
		}
		
		$idsNotIn = '';
		if ( isset( $arg['exclude'] ) && '' != $arg['exclude'] ) {
			$idsNotIn = array_map( 'absint', (array) $arg['exclude'] ); // sanitization ok
			$idsNotIn = implode( ',', $idsNotIn );
			$idsNotIn = "AND $wpdb->posts.ID NOT IN ($idsNotIn)";
		}
		
		$idsIn = '';
		if ( isset( $arg['include'] ) && '' != $arg['include'] ) {
			$idsIn = array_map( 'absint', (array) $arg['include'] ); // sanitization ok
			$idsIn = implode( ',', $idsIn );
			$idsIn = "AND $wpdb->posts.ID IN ($idsIn)";
		}
		
		$query = "SELECT DISTINCT $wpdb->posts.ID
		FROM $wpdb->posts
		LEFT JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
		LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
		LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
		WHERE $wpdb->posts.post_status = 'publish' $stock $categories $idsNotIn $idsIn AND ( $wpdb->posts.post_type = 'product' $variations )
		ORDER BY ID DESC $limit $offset";
		// sanitization ok
		$products = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore
		return $products;
	}
	
	public function getWC3Products( $limit, $offset ) {
		wp_cache_delete( 'wf_check_duplicate', 'options' );
		$limit  = ! empty( $limit ) && is_numeric( $limit ) ? absint( $limit ) : - 1;
		$offset = ! empty( $offset ) && is_numeric( $offset ) ? absint( $offset ) : 0;
		
		// Process Duplicate Products
		if ( '0' == $offset ) {
			delete_option( 'wf_check_duplicate' );
		}
		$getIDs = get_option( 'wf_check_duplicate' ); // get
		
		// Query Arguments
		$arg = array(
			'limit'   => $limit,
			'offset'  => $offset,
			'status'  => 'publish',
			'orderby' => 'date',
			'order'   => 'DESC',
			'return'  => 'ids',
		);
		
		// Remove products by id
		$this->inExProducts();
		if ( sizeof( $this->ids_not_in ) > 0 ) {
			$arg['exclude'] = $this->ids_not_in;
		}
		
		if ( sizeof( $this->ids_in ) > 0 ) {
			$arg['include'] = $this->ids_in;
		}
		
		// Remove Out Of Stock Products
		if ( isset( $this->feedRule['is_outOfStock'] ) && 'y' == $this->feedRule['is_outOfStock'] ) {
			$arg['stock_status'] = 'instock';
		}
		
		// Get Products for Specific Categories
		if ( is_array( $this->categories ) && ! empty( $this->categories[0] ) ) {
			$arg['category'] = $this->categories;
		}
		
		// Get Products for Specific author
		if ( is_array( $this->vendors ) && ! empty( $this->vendors[0] ) ) {
			$arg['author'] = implode( ',', $this->vendors );
		}
		
// # Don't Include Variations for Facebook
// if ( $this->feedRule['provider'] == 'facebook' ) {
// $this->feedRule['is_variations'] = 'n';
// }
		
		$types = array(
			'simple',
			'variable',
			'grouped',
			'external',
			'composite',
			'bundle',
			'yith_bundle',
			'yith-composite',
		);


// if ($this->feedRule['is_variations'] == 'y' ||  isset( $this->feedRule['is_variations'] ) &&  ) {
// array_push($types, 'variable');
// }
//
// if ( isset( $this->feedRule['is_variations'] ) && $this->feedRule['is_variations'] == 'n' ) {
// array_push($types, 'variable');
// }
//
// if ( isset( $this->feedRule['is_variations'] ) && $this->feedRule['is_variations'] == 'both' ) {
// array_push($types, 'variable', 'variation');
// }
		
		// Product Type
		$arg['type'] = $types;
		$query       = new WC_Product_Query( $arg );
		$products    = $query->get_products();
		
		$this->pi = 1; // Product Index
		foreach ( $products as $key => $productId ) {
			
			$prod = wc_get_product( $productId );
			
			$id = $prod->get_id();
			
			if ( $getIDs ) {
				if ( in_array( $id, $getIDs ) ) {
					continue;
				} else {
					array_push( $this->idExist, $id );
				}
			} else {
				array_push( $this->idExist, $id );
			}
			
			if ( $prod->is_type( 'simple' ) ) {
				
				$simple = new WC_Product_Simple( $id );
				
				$this->productsList[ $this->pi ]['id']             = $simple->get_id();
				$this->productsList[ $this->pi ]['title']          = $simple->get_name();
				$this->productsList[ $this->pi ]['description']    = $this->remove_short_codes( $simple->get_description() );
				$this->productsList[ $this->pi ]['variation_type'] = $simple->get_type();
				
				$this->productsList[ $this->pi ]['short_description'] = $this->remove_short_codes( $simple->get_short_description() );
				$this->productsList[ $this->pi ]['product_type']      = wp_strip_all_tags( wc_get_product_category_list( $id, '>', '' ) );
				$this->productsList[ $this->pi ]['link']              = $simple->get_permalink();
				$this->productsList[ $this->pi ]['ex_link']           = $simple->get_permalink();
				
				
				// Featured Image
				if ( has_post_thumbnail( $id ) ) :
					$image                                            = wp_get_attachment_image_src( get_post_thumbnail_id( $id ),
						'single-post-thumbnail' );
					$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( $image[0] );
					$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( $image[0] );
				else :
					$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( wp_get_attachment_url( $id ) );
					$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( wp_get_attachment_url( $id ) );
				endif;
				
				// Additional Images
				$attachmentIds = $simple->get_gallery_image_ids();
				$imgUrls       = array();
				if ( $attachmentIds && is_array( $attachmentIds ) ) {
					$mKey = 1;
					foreach ( $attachmentIds as $attachmentId ) {
						$imgUrls[ $mKey ]                               = $this->get_formatted_url( wp_get_attachment_url( $attachmentId ) );
						$this->productsList[ $this->pi ][ "image_$mKey" ] = $imgUrls[ $mKey ];
						$mKey ++;
					}               
}
				
				$this->productsList[ $this->pi ]['images']           = implode( ',', $imgUrls );
				$this->productsList[ $this->pi ]['condition']        = 'new';
				$this->productsList[ $this->pi ]['type']             = $simple->get_type();
				$this->productsList[ $this->pi ]['is_bundle']        = 'no';
				$this->productsList[ $this->pi ]['multipack']        = '';
				$this->productsList[ $this->pi ]['visibility']       = $simple->get_catalog_visibility();
				$this->productsList[ $this->pi ]['rating_total']     = $simple->get_rating_count();
				$this->productsList[ $this->pi ]['rating_average']   = $simple->get_average_rating();
				$this->productsList[ $this->pi ]['tags']             = $this->getProductTaxonomy( $id, 'product_tag' );
				$this->productsList[ $this->pi ]['item_group_id']    = $simple->get_id();
				$this->productsList[ $this->pi ]['sku']              = $simple->get_sku();
				$this->productsList[ $this->pi ]['parent_sku']       = $simple->get_sku();
				$this->productsList[ $this->pi ]['availability']     = $this->availability( $id );
				$this->productsList[ $this->pi ]['quantity']         = $simple->get_stock_quantity();
				$this->productsList[ $this->pi ]['sale_price_sdate'] = $simple->get_date_on_sale_from();
				$this->productsList[ $this->pi ]['sale_price_edate'] = $simple->get_date_on_sale_to();
				// $this->productsList[ $this->pi ]['price']            = $simple->get_regular_price();
				$this->productsList[ $this->pi ]['price'] = apply_filters( 'woo_feed_convert_price',
					array(
						'filename' => $this->feedRule['filename'],
						'price'    => $simple->get_regular_price(),
					) );
				// $this->productsList[ $this->pi ]['current_price']    = $simple->get_price();
				$this->productsList[ $this->pi ]['current_price'] = apply_filters( 'woo_feed_convert_price',
					array(
						'filename' => $this->feedRule['filename'],
						'price'    => $simple->get_price(),
					) );
				// $this->productsList[ $this->pi ]['price_with_tax']   = ( $simple->is_taxable() ) ? $this->get_price_with_tax( $simple ) : $simple->get_price();
				$this->productsList[ $this->pi ]['price_with_tax'] = apply_filters( 'woo_feed_convert_price',
					array(
						'filename' => $this->feedRule['filename'],
						'price'    => ( $simple->is_taxable() ) ? $this->get_price_with_tax( $simple ) : $simple->get_price(),
					) );
				$this->productsList[ $this->pi ]['sale_price']     = apply_filters( 'woo_feed_convert_price',
					array(
						'filename' => $this->feedRule['filename'],
						'price'    => $simple->get_sale_price(),
					) );// $simple->get_sale_price();
				$this->productsList[ $this->pi ]['weight']         = $simple->get_weight();
				$this->productsList[ $this->pi ]['width']          = $simple->get_width();
				$this->productsList[ $this->pi ]['height']         = $simple->get_height();
				$this->productsList[ $this->pi ]['length']         = $simple->get_length();
				$this->productsList[ $this->pi ]['shipping_class'] = $simple->get_shipping_class();
				
				$this->productsList[ $this->pi ]['author_name']  = $this->get_author_info( $simple->get_id(), 'name' );
				$this->productsList[ $this->pi ]['author_email'] = $this->get_author_info( $simple->get_id(), 'email' );
				
				$this->productsList[ $this->pi ]['date_created'] = $this->format_product_date( $simple->get_date_created() );
				$this->productsList[ $this->pi ]['date_updated'] = $this->format_product_date( $simple->get_date_modified() );
				
				// Sale price effective date
				$from = $this->sale_price_effective_date( $id, '_sale_price_dates_from' );
				$to   = $this->sale_price_effective_date( $id, '_sale_price_dates_to' );
				if ( ! empty( $from ) && ! empty( $to ) ) {
					$from                                                         = gmdate( 'c', strtotime( $from ) );
					$to                                                           = gmdate( 'c', strtotime( $to ) );
					$this->productsList[ $this->pi ]['sale_price_effective_date'] = "$from" . '/' . "$to";
				} else {
					$this->productsList[ $this->pi ]['sale_price_effective_date'] = '';
				}           
} elseif ( $prod->is_type( 'external' ) ) {
				
				$external = new WC_Product_External( $id );
				
				$this->productsList[ $this->pi ]['id']             = $external->get_id();
				$this->productsList[ $this->pi ]['title']          = $external->get_name();
				$this->productsList[ $this->pi ]['description']    = $this->remove_short_codes( $external->get_description() );
				$this->productsList[ $this->pi ]['variation_type'] = $external->get_type();
				
				$this->productsList[ $this->pi ]['short_description'] = $this->remove_short_codes( $external->get_short_description() );
				$this->productsList[ $this->pi ]['product_type']      = wp_strip_all_tags( wc_get_product_category_list( $id, '>', '' ) );
				$this->productsList[ $this->pi ]['link']              = $external->get_permalink();
				$this->productsList[ $this->pi ]['ex_link']           = $external->get_product_url();
				
				
				// Featured Image
				if ( has_post_thumbnail( $id ) ) :
					$image                                            = wp_get_attachment_image_src( get_post_thumbnail_id( $id ),
						'single-post-thumbnail' );
					$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( $image[0] );
					$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( $image[0] );
							else :
								$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( wp_get_attachment_url( $id ) );
								$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( wp_get_attachment_url( $id ) );
										endif;
				
							// Additional Images
							$attachmentIds = $external->get_gallery_image_ids();
							$imgUrls       = array();
							if ( $attachmentIds && is_array( $attachmentIds ) ) {
								$mKey = 1;
								foreach ( $attachmentIds as $attachmentId ) {
									$imgUrls[ $mKey ]                               = $this->get_formatted_url( wp_get_attachment_url( $attachmentId ) );
									$this->productsList[ $this->pi ][ "image_$mKey" ] = $imgUrls[ $mKey ];
									$mKey ++;
								}               
									}
				
							$this->productsList[ $this->pi ]['images']           = implode( ',', $imgUrls );
							$this->productsList[ $this->pi ]['condition']        = 'new';
							$this->productsList[ $this->pi ]['type']             = $external->get_type();
							$this->productsList[ $this->pi ]['is_bundle']        = 'no';
							$this->productsList[ $this->pi ]['multipack']        = '';
							$this->productsList[ $this->pi ]['visibility']       = $external->get_catalog_visibility();
							$this->productsList[ $this->pi ]['rating_total']     = $external->get_rating_count();
							$this->productsList[ $this->pi ]['rating_average']   = $external->get_average_rating();
							$this->productsList[ $this->pi ]['tags']             = $this->getProductTaxonomy( $id, 'product_tag' );
							$this->productsList[ $this->pi ]['item_group_id']    = $external->get_id();
							$this->productsList[ $this->pi ]['sku']              = $external->get_sku();
							$this->productsList[ $this->pi ]['parent_sku']       = $external->get_sku();
							$this->productsList[ $this->pi ]['availability']     = $this->availability( $id );
							$this->productsList[ $this->pi ]['quantity']         = $external->get_stock_quantity();
							$this->productsList[ $this->pi ]['sale_price_sdate'] = $external->get_date_on_sale_from();
							$this->productsList[ $this->pi ]['sale_price_edate'] = $external->get_date_on_sale_to();
							$this->productsList[ $this->pi ]['price']            = apply_filters( 'woo_feed_convert_price',
								array(
									'filename' => $this->feedRule['filename'],
									'price'    => $external->get_regular_price(),
								) );// $external->get_regular_price();
				$this->productsList[ $this->pi ]['current_price']    = apply_filters( 'woo_feed_convert_price',
					array(
						'filename' => $this->feedRule['filename'],
						'price'    => $external->get_price(),
					) );// $external->get_price();
				$this->productsList[ $this->pi ]['price_with_tax']   = apply_filters( 'woo_feed_convert_price',
					array(
						'filename' => $this->feedRule['filename'],
						'price'    => ( $external->is_taxable() ) ? $this->get_price_with_tax( $external ) : $external->get_price(),
					) );// ( $external->is_taxable() ) ? $this->get_price_with_tax( $external ) : $external->get_price();
				$this->productsList[ $this->pi ]['sale_price']       = apply_filters( 'woo_feed_convert_price',
					array(
						'filename' => $this->feedRule['filename'],
						'price'    => $external->get_sale_price(),
					) );// $external->get_sale_price();
				$this->productsList[ $this->pi ]['weight']           = $external->get_weight();
				$this->productsList[ $this->pi ]['width']            = $external->get_width();
				$this->productsList[ $this->pi ]['height']           = $external->get_height();
				$this->productsList[ $this->pi ]['length']           = $external->get_length();
				$this->productsList[ $this->pi ]['shipping_class']   = $external->get_shipping_class();
				
				$this->productsList[ $this->pi ]['author_name']  = $this->get_author_info( $external->get_id(),
					'name' );
				$this->productsList[ $this->pi ]['author_email'] = $this->get_author_info( $external->get_id(),
					'email' );
				
				$this->productsList[ $this->pi ]['date_created'] = $this->format_product_date( $external->get_date_created() );
				$this->productsList[ $this->pi ]['date_updated'] = $this->format_product_date( $external->get_date_modified() );
				
				// Sale price effective date
				$from = $this->sale_price_effective_date( $id, '_sale_price_dates_from' );
				$to   = $this->sale_price_effective_date( $id, '_sale_price_dates_to' );
				if ( ! empty( $from ) && ! empty( $to ) ) {
					$from                                                         = gmdate( 'c', strtotime( $from ) );
					$to                                                           = gmdate( 'c', strtotime( $to ) );
					$this->productsList[ $this->pi ]['sale_price_effective_date'] = "$from" . '/' . "$to";
							} else {
					$this->productsList[ $this->pi ]['sale_price_effective_date'] = '';
							}
			} elseif ( $prod->is_type( 'grouped' ) ) {
				
				$grouped = new WC_Product_Grouped( $id );
				
				$this->productsList[ $this->pi ]['id']             = $grouped->get_id();
				$this->productsList[ $this->pi ]['title']          = $grouped->get_name();
				$this->productsList[ $this->pi ]['description']    = $this->remove_short_codes( $grouped->get_description() );
				$this->productsList[ $this->pi ]['variation_type'] = $grouped->get_type();
				
				$this->productsList[ $this->pi ]['short_description'] = $this->remove_short_codes( $grouped->get_short_description() );
				$this->productsList[ $this->pi ]['product_type']      = wp_strip_all_tags( wc_get_product_category_list( $id, '>', '' ) );
				$this->productsList[ $this->pi ]['link']              = $grouped->get_permalink();
				$this->productsList[ $this->pi ]['ex_link']           = $grouped->get_permalink();
				
				
				// Featured Image
				if ( has_post_thumbnail( $id ) ) :
					$image                                            = wp_get_attachment_image_src( get_post_thumbnail_id( $id ),
						'single-post-thumbnail' );
					$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( $image[0] );
					$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( $image[0] );
				else :
					$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( wp_get_attachment_url( $id ) );
					$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( wp_get_attachment_url( $id ) );
				endif;
				
				// Additional Images
				$attachmentIds = $grouped->get_gallery_image_ids();
				$imgUrls       = array();
				if ( $attachmentIds && is_array( $attachmentIds ) ) {
					$mKey = 1;
					foreach ( $attachmentIds as $attachmentId ) {
						$imgUrls[ $mKey ]                               = $this->get_formatted_url( wp_get_attachment_url( $attachmentId ) );
						$this->productsList[ $this->pi ][ "image_$mKey" ] = $imgUrls[ $mKey ];
						$mKey ++;
					}               
}
				
				$this->productsList[ $this->pi ]['images']           = implode( ',', $imgUrls );
				$this->productsList[ $this->pi ]['condition']        = 'new';
				$this->productsList[ $this->pi ]['type']             = $grouped->get_type();
				$this->productsList[ $this->pi ]['is_bundle']        = 'no';
				$this->productsList[ $this->pi ]['multipack']        = ( count( $grouped->get_children() ) > 0 ) ? count( $grouped->get_children() ) : '';
				$this->productsList[ $this->pi ]['visibility']       = $grouped->get_catalog_visibility();
				$this->productsList[ $this->pi ]['rating_total']     = $grouped->get_rating_count();
				$this->productsList[ $this->pi ]['rating_average']   = $grouped->get_average_rating();
				$this->productsList[ $this->pi ]['tags']             = $this->getProductTaxonomy( $id, 'product_tag' );
				$this->productsList[ $this->pi ]['item_group_id']    = $grouped->get_id();
				$this->productsList[ $this->pi ]['sku']              = $grouped->get_sku();
				$this->productsList[ $this->pi ]['parent_sku']       = $grouped->get_sku();
				$this->productsList[ $this->pi ]['availability']     = $this->availability( $id );
				$this->productsList[ $this->pi ]['quantity']         = $grouped->get_stock_quantity();
				$this->productsList[ $this->pi ]['sale_price_sdate'] = $grouped->get_date_on_sale_from();
				$this->productsList[ $this->pi ]['sale_price_edate'] = $grouped->get_date_on_sale_to();
				$this->productsList[ $this->pi ]['price']            = apply_filters( 'woo_feed_convert_price',
					array(
						'filename' => $this->feedRule['filename'],
						'price'    => $this->getGroupProductPrice( $grouped, 'regular' ),
					) );// $this->getGroupProductPrice($grouped,'regular');
				$this->productsList[ $this->pi ]['current_price']    = apply_filters( 'woo_feed_convert_price',
					array(
						'filename' => $this->feedRule['filename'],
						'price'    => $this->getGroupProductPrice( $grouped, 'sale' ),
					) );// $this->getGroupProductPrice($grouped,'sale');
				$this->productsList[ $this->pi ]['price_with_tax']   = apply_filters( 'woo_feed_convert_price',
					array(
						'filename' => $this->feedRule['filename'],
						'price'    => ( $grouped->is_taxable() ) ? $this->getGroupProductPrice( $grouped,
							'sale',
							true ) : $grouped->get_price(),
					) );// ( $grouped->is_taxable() ) ? $this->getGroupProductPrice($grouped,'sale',true) : $grouped->get_price();
				$this->productsList[ $this->pi ]['sale_price']       = apply_filters( 'woo_feed_convert_price',
					array(
						'filename' => $this->feedRule['filename'],
						'price'    => $this->getGroupProductPrice( $grouped, 'sale' ),
					) );// $this->getGroupProductPrice($grouped,'sale');
				$this->productsList[ $this->pi ]['weight']           = $grouped->get_weight();
				$this->productsList[ $this->pi ]['width']            = $grouped->get_width();
				$this->productsList[ $this->pi ]['height']           = $grouped->get_height();
				$this->productsList[ $this->pi ]['length']           = $grouped->get_length();
				$this->productsList[ $this->pi ]['shipping_class']   = $grouped->get_shipping_class();
				
				$this->productsList[ $this->pi ]['author_name']  = $this->get_author_info( $grouped->get_id(), 'name' );
				$this->productsList[ $this->pi ]['author_email'] = $this->get_author_info( $grouped->get_id(),
					'email' );
				
				$this->productsList[ $this->pi ]['date_created'] = $this->format_product_date( $grouped->get_date_created() );
				$this->productsList[ $this->pi ]['date_updated'] = $this->format_product_date( $grouped->get_date_modified() );
				
				// Sale price effective date
				$from = $this->sale_price_effective_date( $id, '_sale_price_dates_from' );
				$to   = $this->sale_price_effective_date( $id, '_sale_price_dates_to' );
				if ( ! empty( $from ) && ! empty( $to ) ) {
					$from                                                         = gmdate( 'c', strtotime( $from ) );
					$to                                                           = gmdate( 'c', strtotime( $to ) );
					$this->productsList[ $this->pi ]['sale_price_effective_date'] = "$from" . '/' . "$to";
				} else {
					$this->productsList[ $this->pi ]['sale_price_effective_date'] = '';
				}
			} elseif ( $prod->is_type( 'composite' ) ) {
				if ( class_exists( 'WC_Product_Composite' ) ) {
					
					$composite = new WC_Product_Composite( $id );
					
					$this->productsList[ $this->pi ]['id']             = $composite->get_id();
					$this->productsList[ $this->pi ]['title']          = $composite->get_name();
					$this->productsList[ $this->pi ]['description']    = $this->remove_short_codes( $composite->get_description() );
					$this->productsList[ $this->pi ]['variation_type'] = $composite->get_type();
					
					$this->productsList[ $this->pi ]['short_description'] = $this->remove_short_codes( $composite->get_short_description() );
					$this->productsList[ $this->pi ]['product_type']      = wp_strip_all_tags( wc_get_product_category_list( $id, '>', '' ) );
					$this->productsList[ $this->pi ]['link']              = $composite->get_permalink();
					$this->productsList[ $this->pi ]['ex_link']           = $composite->get_permalink();
					
					
					// Featured Image
					if ( has_post_thumbnail( $id ) ) :
						$image                                            = wp_get_attachment_image_src( get_post_thumbnail_id( $id ),
							'single-post-thumbnail' );
						$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( $image[0] );
						$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( $image[0] );
					else :
						$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( wp_get_attachment_url( $id ) );
						$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( wp_get_attachment_url( $id ) );
					endif;
					
					// Additional Images
					$attachmentIds = $composite->get_gallery_image_ids();
					$imgUrls       = array();
					if ( $attachmentIds && is_array( $attachmentIds ) ) {
						$mKey = 1;
						foreach ( $attachmentIds as $attachmentId ) {
							$imgUrls[ $mKey ]                               = $this->get_formatted_url( wp_get_attachment_url( $attachmentId ) );
							$this->productsList[ $this->pi ][ "image_$mKey" ] = $imgUrls[ $mKey ];
							$mKey ++;
						}
					}
					
					$this->productsList[ $this->pi ]['images']         = implode( ',', $imgUrls );
					$this->productsList[ $this->pi ]['condition']      = 'new';
					$this->productsList[ $this->pi ]['type']           = $composite->get_type();
					$this->productsList[ $this->pi ]['is_bundle']      = 'no';
					$this->productsList[ $this->pi ]['multipack']      = '';
					$this->productsList[ $this->pi ]['visibility']     = $composite->get_catalog_visibility();
					$this->productsList[ $this->pi ]['rating_total']   = $composite->get_rating_count();
					$this->productsList[ $this->pi ]['rating_average'] = $composite->get_average_rating();
					$this->productsList[ $this->pi ]['tags'] = $this->getProductTaxonomy( $id,
						'product_tag' );
					$this->productsList[ $this->pi ]['item_group_id'] = $composite->get_id();
					$this->productsList[ $this->pi ]['sku'] = $composite->get_sku();
					$this->productsList[ $this->pi ]['parent_sku'] = $composite->get_sku();
					$this->productsList[ $this->pi ]['availability'] = $this->availability( $id );
					
					if ( isset( $this->feedRule['composite_price'] ) && 'all_product_price' == $this->feedRule['composite_price'] ) {
						$_total_price  = 0;
						$_total_sPrice = 0;
						
						$_ids = $prod->get_component_ids();
						
						foreach ( $_ids as $_id ) {
							foreach ( $prod->get_component_data( $_id )['assigned_ids'] as $assigned_id ) {
								$_compo_prod   = wc_get_product( $assigned_id );
								$_total_price  += $_compo_prod->get_regular_price();
								$_total_sPrice += $_compo_prod->get_price();
							}
						}
						$price  = $_total_price + (int) $composite->get_regular_price();
						$sPrice = $_total_sPrice + (int) $composite->get_price();
					} else {
						if ( 'first' == $this->feedRule['variable_price'] ) {
							$price  = $composite->get_composite_regular_price();
							$sPrice = $composite->get_composite_price();
						} else {
							$price  = $composite->get_composite_regular_price( $this->feedRule['variable_price'] );
							$sPrice = $composite->get_composite_price( $this->feedRule['variable_price'] );
						}
					}
					
					$this->productsList[ $this->pi ]['quantity']         = $composite->get_stock_quantity();
					$this->productsList[ $this->pi ]['sale_price_sdate'] = $composite->get_date_on_sale_from();
					$this->productsList[ $this->pi ]['sale_price_edate'] = $composite->get_date_on_sale_to();
					$this->productsList[ $this->pi ]['price']            = apply_filters( 'woo_feed_convert_price',
						array(
							'filename' => $this->feedRule['filename'],
							'price'    => $price,
						) );// $price;
					$this->productsList[ $this->pi ]['current_price']    = apply_filters( 'woo_feed_convert_price',
						array(
							'filename' => $this->feedRule['filename'],
							'price'    => $composite->get_price(),
						) );// $composite->get_price();
					$this->productsList[ $this->pi ]['price_with_tax']   = apply_filters( 'woo_feed_convert_price',
						array(
							'filename' => $this->feedRule['filename'],
							'price'    => $composite->get_composite_price_including_tax(),
						) );// $composite->get_composite_price_including_tax();
					$this->productsList[ $this->pi ]['sale_price']       = apply_filters( 'woo_feed_convert_price',
						array(
							'filename' => $this->feedRule['filename'],
							'price'    => $sPrice,
						) );// $sPrice;
					$this->productsList[ $this->pi ]['weight']           = $composite->get_weight();
					$this->productsList[ $this->pi ]['width']            = $composite->get_width();
					$this->productsList[ $this->pi ]['height']           = $composite->get_height();
					$this->productsList[ $this->pi ]['length']           = $composite->get_length();
					$this->productsList[ $this->pi ]['shipping_class']   = $composite->get_shipping_class();
					
					$this->productsList[ $this->pi ]['author_name']  = $this->get_author_info( $composite->get_id(),
						'name' );
					$this->productsList[ $this->pi ]['author_email'] = $this->get_author_info( $composite->get_id(),
						'email' );
					
					$this->productsList[ $this->pi ]['date_created'] = $this->format_product_date( $composite->get_date_created() );
					$this->productsList[ $this->pi ]['date_updated'] = $this->format_product_date( $composite->get_date_modified() );
					
					// Sale price effective date
					$from = $this->sale_price_effective_date( $id, '_sale_price_dates_from' );
					$to   = $this->sale_price_effective_date( $id, '_sale_price_dates_to' );
					if ( ! empty( $from ) && ! empty( $to ) ) {
						$from                                                         = gmdate( 'c',
							strtotime( $from ) );
						$to                                                           = gmdate( 'c', strtotime( $to ) );
						$this->productsList[ $this->pi ]['sale_price_effective_date'] = "$from" . '/' . "$to";
					} else {
						$this->productsList[ $this->pi ]['sale_price_effective_date'] = '';
					}
				}
			} elseif ( $prod->is_type( 'yith-composite' ) ) {
				if ( class_exists( 'WC_Product_Yith_Composite' ) ) {
					$yith_composite = new WC_Product_Yith_Composite( $id );
					
					$this->productsList[ $this->pi ]['id']             = $yith_composite->get_id();
					$this->productsList[ $this->pi ]['title']          = $yith_composite->get_name();
					$this->productsList[ $this->pi ]['description']    = $this->remove_short_codes( $yith_composite->get_description() );
					$this->productsList[ $this->pi ]['variation_type'] = $yith_composite->get_type();
					
					$this->productsList[ $this->pi ]['short_description'] = $this->remove_short_codes( $yith_composite->get_short_description() );
					$this->productsList[ $this->pi ]['product_type']      = wp_strip_all_tags( wc_get_product_category_list( $id,
						'>',
						'' ) );
					$this->productsList[ $this->pi ]['link']              = $yith_composite->get_permalink();
					$this->productsList[ $this->pi ]['ex_link']           = $yith_composite->get_permalink();
					
					if ( has_post_thumbnail( $id ) ) :
						$image                                            = wp_get_attachment_image_src( get_post_thumbnail_id( $id ),
							'single-post-thumbnail' );
						$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( $image[0] );
						$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( $image[0] );
					else :
						$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( wp_get_attachment_url( $id ) );
						$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( wp_get_attachment_url( $id ) );
					endif;
					
					$attachmentIds = $yith_composite->get_gallery_image_ids();
					$imgUrls       = array();
					if ( $attachmentIds && is_array( $attachmentIds ) ) {
						$mKey = 1;
						foreach ( $attachmentIds as $attachmentId ) {
							$imgUrls[ $mKey ]                               = $this->get_formatted_url( wp_get_attachment_url( $attachmentId ) );
							$this->productsList[ $this->pi ][ "image_$mKey" ] = $imgUrls[ $mKey ];
							$mKey ++;
						}
					}
					
					$this->productsList[ $this->pi ]['images']         = implode( ',', $imgUrls );
					$this->productsList[ $this->pi ]['condition']      = 'new';
					$this->productsList[ $this->pi ]['type']           = $yith_composite->get_type();
					$this->productsList[ $this->pi ]['is_bundle']      = 'no';
					$this->productsList[ $this->pi ]['multipack']      = '';
					$this->productsList[ $this->pi ]['visibility']     = $yith_composite->get_catalog_visibility();
					$this->productsList[ $this->pi ]['rating_total']   = $yith_composite->get_rating_count();
					$this->productsList[ $this->pi ]['rating_average'] = $yith_composite->get_average_rating();
					$this->productsList[ $this->pi ]['tags']           = wp_strip_all_tags( wc_get_product_tag_list( $id,
						',' ) );
					$this->productsList[ $this->pi ]['item_group_id']  = $yith_composite->get_id();
					$this->productsList[ $this->pi ]['sku']            = $yith_composite->get_sku();
					$this->productsList[ $this->pi ]['parent_sku']     = $yith_composite->get_sku();
					$this->productsList[ $this->pi ]['availability']   = $this->availability( $id );
					
					if ( isset( $this->feedRule['composite_price'] ) && 'all_product_price' == $this->feedRule['composite_price'] ) {
						$_total_price  = 0;
						$_total_sPrice = 0;
						
						foreach ( $yith_composite->getComponentsData() as $_component ) {
							foreach ( $_component['option_type_product_id_values'] as $product_id_value ) {
								$_id = $product_id_value;
								
								$prod          = wc_get_product( $_id );
								$_total_price  += $prod->get_regular_price();
								$_total_sPrice += $prod->get_price();
							}
						}
						$price  = $_total_price + (int) $yith_composite->get_regular_price();
						$sPrice = $_total_sPrice + (int) $yith_composite->get_price();
					} else {
						if ( 'first' == $this->feedRule['variable_price'] ) {
							$price  = $yith_composite->get_regular_price();
							$sPrice = $yith_composite->get_price();
						} else {
							$price  = $yith_composite->get_regular_price( $this->feedRule['variable_price'] );
							$sPrice = $yith_composite->get_price( $this->feedRule['variable_price'] );
						}
					}
					
					$this->productsList[ $this->pi ]['quantity']         = $yith_composite->get_stock_quantity();
					$this->productsList[ $this->pi ]['sale_price_sdate'] = $yith_composite->get_date_on_sale_from();
					$this->productsList[ $this->pi ]['sale_price_edate'] = $yith_composite->get_date_on_sale_to();
					$this->productsList[ $this->pi ]['price']            = apply_filters( 'woo_feed_convert_price',
						array(
							'filename' => $this->feedRule['filename'],
							'price'    => $price,
						) );// $price;
					$this->productsList[ $this->pi ]['current_price']    = apply_filters( 'woo_feed_convert_price',
						array(
							'filename' => $this->feedRule['filename'],
							'price'    => $yith_composite->get_price(),
						) );
					$this->productsList[ $this->pi ]['price_with_tax']   = apply_filters( 'woo_feed_convert_price',
						array(
							'filename' => $this->feedRule['filename'],
							'price'    => $yith_composite->get_price(),
						) );
					$this->productsList[ $this->pi ]['sale_price']       = apply_filters( 'woo_feed_convert_price',
						array(
							'filename' => $this->feedRule['filename'],
							'price'    => $sPrice,
						) );// $sPrice;
					$this->productsList[ $this->pi ]['weight']           = $yith_composite->get_weight();
					$this->productsList[ $this->pi ]['width']            = $yith_composite->get_width();
					$this->productsList[ $this->pi ]['height']           = $yith_composite->get_height();
					$this->productsList[ $this->pi ]['length']           = $yith_composite->get_length();
					$this->productsList[ $this->pi ]['shipping_class']   = $yith_composite->get_shipping_class();
					
					$this->productsList[ $this->pi ]['date_created'] = $this->format_product_date( $yith_composite->get_date_created() );
					$this->productsList[ $this->pi ]['date_updated'] = $this->format_product_date( $yith_composite->get_date_modified() );
					
					$from = $this->sale_price_effective_date( $id, '_sale_price_dates_from' );
					$to   = $this->sale_price_effective_date( $id, '_sale_price_dates_to' );
					if ( ! empty( $from ) && ! empty( $to ) ) {
						$from                                                         = gmdate( 'c',
							strtotime( $from ) );
						$to                                                           = gmdate( 'c', strtotime( $to ) );
						$this->productsList[ $this->pi ]['sale_price_effective_date'] = "$from" . '/' . "$to";
					} else {
						$this->productsList[ $this->pi ]['sale_price_effective_date'] = '';
					}
				}
			} elseif ( $prod->is_type( 'bundle' ) ) {
				if ( class_exists( 'WC_Product_Bundle' ) ) {
					
					$bundle = new WC_Product_Bundle( $id );
					
					$this->productsList[ $this->pi ]['id']             = $bundle->get_id();
					$this->productsList[ $this->pi ]['title']          = $bundle->get_name();
					$this->productsList[ $this->pi ]['description']    = $this->remove_short_codes( $bundle->get_description() );
					$this->productsList[ $this->pi ]['variation_type'] = $bundle->get_type();
					
					$this->productsList[ $this->pi ]['short_description'] = $this->remove_short_codes( $bundle->get_short_description() );
					$this->productsList[ $this->pi ]['product_type']      = wp_strip_all_tags( wc_get_product_category_list( $id,
						'>',
						'' ) );
					$this->productsList[ $this->pi ]['link']              = $bundle->get_permalink();
					$this->productsList[ $this->pi ]['ex_link']           = $bundle->get_permalink();
					
					
					// Featured Image
					if ( has_post_thumbnail( $id ) ) :
						$image                                            = wp_get_attachment_image_src( get_post_thumbnail_id( $id ),
							'single-post-thumbnail' );
						$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( $image[0] );
						$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( $image[0] );
					else :
						$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( wp_get_attachment_url( $id ) );
						$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( wp_get_attachment_url( $id ) );
					endif;
					
					// Additional Images
					$attachmentIds = $bundle->get_gallery_image_ids();
					$imgUrls       = array();
					if ( $attachmentIds && is_array( $attachmentIds ) ) {
						$mKey = 1;
						foreach ( $attachmentIds as $attachmentId ) {
							$imgUrls[ $mKey ]                               = $this->get_formatted_url( wp_get_attachment_url( $attachmentId ) );
							$this->productsList[ $this->pi ][ "image_$mKey" ] = $imgUrls[ $mKey ];
							$mKey ++;
						}
					}
					
					$this->productsList[ $this->pi ]['images']         = implode( ',', $imgUrls );
					$this->productsList[ $this->pi ]['condition']      = 'new';
					$this->productsList[ $this->pi ]['type']           = $bundle->get_type();
					$this->productsList[ $this->pi ]['is_bundle']      = 'yes';
					$this->productsList[ $this->pi ]['multipack']      = '';
					$this->productsList[ $this->pi ]['visibility']     = $bundle->get_catalog_visibility();
					$this->productsList[ $this->pi ]['rating_total']   = $bundle->get_rating_count();
					$this->productsList[ $this->pi ]['rating_average'] = $bundle->get_average_rating();
					$this->productsList[ $this->pi ]['tags']           = $this->getProductTaxonomy( $id,
						'product_tag' );
					$this->productsList[ $this->pi ]['item_group_id']  = $bundle->get_id();
					$this->productsList[ $this->pi ]['sku']            = $bundle->get_sku();
					$this->productsList[ $this->pi ]['parent_sku']     = $bundle->get_sku();
					$this->productsList[ $this->pi ]['availability']   = $this->availability( $id );
					
					if ( 'first' == $this->feedRule['variable_price'] ) {
						$price  = $bundle->get_bundle_regular_price();
						$sPrice = $bundle->get_bundle_price();
					} else {
						$price  = $bundle->get_bundle_regular_price( $this->feedRule['variable_price'] );
						$sPrice = $bundle->get_bundle_price( $this->feedRule['variable_price'] );
					}
					
					$this->productsList[ $this->pi ]['quantity']         = $bundle->get_stock_quantity();
					$this->productsList[ $this->pi ]['sale_price_sdate'] = $bundle->get_date_on_sale_from();
					$this->productsList[ $this->pi ]['sale_price_edate'] = $bundle->get_date_on_sale_to();
					$this->productsList[ $this->pi ]['price']            = apply_filters( 'woo_feed_convert_price',
						array(
							'filename' => $this->feedRule['filename'],
							'price'    => $price,
						) );// $price;
					$this->productsList[ $this->pi ]['current_price']    = apply_filters( 'woo_feed_convert_price',
						array(
							'filename' => $this->feedRule['filename'],
							'price'    => $bundle->get_price(),
						) );
					$this->productsList[ $this->pi ]['price_with_tax']   = apply_filters( 'woo_feed_convert_price',
						array(
							'filename' => $this->feedRule['filename'],
							'price'    => $bundle->get_bundle_price_including_tax(),
						) );
					$this->productsList[ $this->pi ]['sale_price']       = apply_filters( 'woo_feed_convert_price',
						array(
							'filename' => $this->feedRule['filename'],
							'price'    => $sPrice,
						) );
					$this->productsList[ $this->pi ]['weight']           = $bundle->get_weight();
					$this->productsList[ $this->pi ]['width']            = $bundle->get_width();
					$this->productsList[ $this->pi ]['height']           = $bundle->get_height();
					$this->productsList[ $this->pi ]['length']           = $bundle->get_length();
					$this->productsList[ $this->pi ]['shipping_class']   = $bundle->get_shipping_class();
					
					$this->productsList[ $this->pi ]['author_name']  = $this->get_author_info( $bundle->get_id(),
						'name' );
					$this->productsList[ $this->pi ]['author_email'] = $this->get_author_info( $bundle->get_id(),
						'email' );
					
					$this->productsList[ $this->pi ]['date_created'] = $this->format_product_date( $bundle->get_date_created() );
					$this->productsList[ $this->pi ]['date_updated'] = $this->format_product_date( $bundle->get_date_modified() );
					
					// Sale price effective date
					$from = $this->sale_price_effective_date( $id, '_sale_price_dates_from' );
					$to   = $this->sale_price_effective_date( $id, '_sale_price_dates_to' );
					if ( ! empty( $from ) && ! empty( $to ) ) {
						$from                                                         = gmdate( 'c',
							strtotime( $from ) );
						$to                                                           = gmdate( 'c', strtotime( $to ) );
						$this->productsList[ $this->pi ]['sale_price_effective_date'] = "$from" . '/' . "$to";
					} else {
						$this->productsList[ $this->pi ]['sale_price_effective_date'] = '';
					}
				}
			} elseif ( $prod->is_type( 'yith_bundle' ) ) {
				if ( class_exists( 'WC_Product_Yith_Bundle' ) ) {
					$yith_bundle = new WC_Product_Yith_Bundle( $id );
					
					$this->productsList[ $this->pi ]['id']                = $yith_bundle->get_id();
					$this->productsList[ $this->pi ]['title']             = $yith_bundle->get_name();
					$this->productsList[ $this->pi ]['description']       = $this->remove_short_codes( $yith_bundle->get_description() );
					$this->productsList[ $this->pi ]['variation_type']    = $yith_bundle->get_type();
					$this->productsList[ $this->pi ]['short_description'] = $this->remove_short_codes( $yith_bundle->get_short_description() );
					$this->productsList[ $this->pi ]['product_type']      = wp_strip_all_tags( wc_get_product_category_list( $id,
						'>',
						'' ) );
					$this->productsList[ $this->pi ]['link']              = $yith_bundle->get_permalink();
					$this->productsList[ $this->pi ]['ex_link']           = $yith_bundle->get_permalink();
					// Featured Image
					if ( has_post_thumbnail( $id ) ) :
						$image                                            = wp_get_attachment_image_src( get_post_thumbnail_id( $id ),
							'single-post-thumbnail' );
						$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( $image[0] );
						$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( $image[0] );
					else :
						$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( wp_get_attachment_url( $id ) );
						$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( wp_get_attachment_url( $id ) );
					endif;
					// Additional Images
					$attachmentIds = $yith_bundle->get_gallery_image_ids();
					$imgUrls       = array();
					if ( $attachmentIds && is_array( $attachmentIds ) ) {
						$mKey = 1;
						foreach ( $attachmentIds as $attachmentId ) {
							$imgUrls[ $mKey ]                               = $this->get_formatted_url( wp_get_attachment_url( $attachmentId ) );
							$this->productsList[ $this->pi ][ "image_$mKey" ] = $imgUrls[ $mKey ];
							$mKey ++;
						}
					}
					$this->productsList[ $this->pi ]['images']         = implode( ',', $imgUrls );
					$this->productsList[ $this->pi ]['condition']      = 'New';
					$this->productsList[ $this->pi ]['type']           = $yith_bundle->get_type();
					$this->productsList[ $this->pi ]['is_bundle']      = 'yes';
					$this->productsList[ $this->pi ]['multipack']      = '';
					$this->productsList[ $this->pi ]['visibility']     = $yith_bundle->get_catalog_visibility();
					$this->productsList[ $this->pi ]['rating_total']   = $yith_bundle->get_rating_count();
					$this->productsList[ $this->pi ]['rating_average'] = $yith_bundle->get_average_rating();
					$this->productsList[ $this->pi ]['tags']           = wp_strip_all_tags( wc_get_product_tag_list( $id,
						',' ) );
					$this->productsList[ $this->pi ]['item_group_id']  = $yith_bundle->get_id();
					$this->productsList[ $this->pi ]['sku']            = $yith_bundle->get_sku();
					$this->productsList[ $this->pi ]['parent_sku']     = $yith_bundle->get_sku();
					$this->productsList[ $this->pi ]['availability']   = $this->availability( $id );
					
					$this->productsList[ $this->pi ]['quantity']         = $yith_bundle->get_stock_quantity();
					$this->productsList[ $this->pi ]['sale_price_sdate'] = $yith_bundle->get_date_on_sale_from();
					$this->productsList[ $this->pi ]['sale_price_edate'] = $yith_bundle->get_date_on_sale_to();
					$this->productsList[ $this->pi ]['price']            = apply_filters( 'woo_feed_convert_price',
						array(
							'filename' => $this->feedRule['filename'],
							'price'    => $yith_bundle->get_regular_price(),
						) );
					$this->productsList[ $this->pi ]['current_price'] = apply_filters( 'woo_feed_convert_price',
						array(
							'filename' => $this->feedRule['filename'],
							'price'    => $yith_bundle->get_price(),
						) );
					$this->productsList[ $this->pi ]['price_with_tax']   = apply_filters( 'woo_feed_convert_price',
						array(
							'filename' => $this->feedRule['filename'],
							'price'    => ( $yith_bundle->is_taxable() ) ? $this->get_price_with_tax( $yith_bundle ) : $yith_bundle->get_price(),
						) );
					$this->productsList[ $this->pi ]['sale_price']       = apply_filters( 'woo_feed_convert_price',
						array(
							'filename' => $this->feedRule['filename'],
							'price'    => $yith_bundle->get_sale_price(),
						) );
					$this->productsList[ $this->pi ]['weight']           = $yith_bundle->get_weight();
					$this->productsList[ $this->pi ]['width']            = $yith_bundle->get_width();
					$this->productsList[ $this->pi ]['height']           = $yith_bundle->get_height();
					$this->productsList[ $this->pi ]['length']           = $yith_bundle->get_length();
					$this->productsList[ $this->pi ]['shipping_class']   = $yith_bundle->get_shipping_class();
					// Sale price effective date
					$from = $this->sale_price_effective_date( $id, '_sale_price_dates_from' );
					$to   = $this->sale_price_effective_date( $id, '_sale_price_dates_to' );
					if ( ! empty( $from ) && ! empty( $to ) ) {
						$from                                                         = gmdate( 'c', strtotime( $from ) );
						$to                                                           = gmdate( 'c', strtotime( $to ) );
						$this->productsList[ $this->pi ]['sale_price_effective_date'] = "$from" . '/' . "$to";
					} else {
						$this->productsList[ $this->pi ]['sale_price_effective_date'] = '';
					}
				}
			} elseif ( $prod->is_type( 'variable' ) ) {
				// If product variations are set to exclude then only include variable (parent) products
				
				$variable = new WC_Product_Variable( $id );
				
				// Include Variations
				if ( 'y' == $this->feedRule['is_variations'] || 'both' == $this->feedRule['is_variations'] ) {
					if ( $variable->has_child() ) {
						$this->getWC3Variations( $variable->get_children() );
					}
				}
				
				if ( 'y' == $this->feedRule['is_variations'] ) {
					continue;
				}
				$this->productsList[ $this->pi ]['id']                = $variable->get_id();
				$this->productsList[ $this->pi ]['title']             = $variable->get_name();
				$this->productsList[ $this->pi ]['description']       = $this->remove_short_codes( $variable->get_description() );
				$this->productsList[ $this->pi ]['variation_type']    = $variable->get_type();
				$this->productsList[ $this->pi ]['short_description'] = $this->remove_short_codes( $variable->get_short_description() );
				$this->productsList[ $this->pi ]['product_type']      = wp_strip_all_tags( wc_get_product_category_list( $id, '>', '' ) );
				$this->productsList[ $this->pi ]['link']              = $variable->get_permalink();
				$this->productsList[ $this->pi ]['ex_link']           = $variable->get_permalink();
				$this->productsList[ $this->pi ]['sku']               = $variable->get_sku();
				$this->productsList[ $this->pi ]['parent_sku']        = $variable->get_sku();
				
				
				// Featured Image
				if ( has_post_thumbnail( $id ) ) :
					$image                                            = wp_get_attachment_image_src( get_post_thumbnail_id( $id ),
						'single-post-thumbnail' );
					$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( $image[0] );
					$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( $image[0] );
				else :
					$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( wp_get_attachment_url( $id ) );
					$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( wp_get_attachment_url( $id ) );
				endif;
				
				// Additional Images
				$attachmentIds = $variable->get_gallery_image_ids();
				$imgUrls       = array();
				if ( $attachmentIds && is_array( $attachmentIds ) ) {
					$mKey = 1;
					foreach ( $attachmentIds as $attachmentId ) {
						$imgUrls[ $mKey ]                               = $this->get_formatted_url( wp_get_attachment_url( $attachmentId ) );
						$this->productsList[ $this->pi ][ "image_$mKey" ] = $imgUrls[ $mKey ];
						$mKey ++;
					}               
}
				
				$this->productsList[ $this->pi ]['images']         = implode( ',', $imgUrls );
				$this->productsList[ $this->pi ]['condition']      = 'new';
				$this->productsList[ $this->pi ]['type']           = $variable->get_type();
				$this->productsList[ $this->pi ]['is_bundle']      = 'no';
				$this->productsList[ $this->pi ]['multipack']      = '';
				$this->productsList[ $this->pi ]['visibility']     = $variable->get_catalog_visibility();
				$this->productsList[ $this->pi ]['rating_total']   = $variable->get_rating_count();
				$this->productsList[ $this->pi ]['rating_average'] = $variable->get_average_rating();
				$this->productsList[ $this->pi ]['tags']           = $this->getProductTaxonomy( $id, 'product_tag' );
				$this->productsList[ $this->pi ]['item_group_id']  = $variable->get_id();
				$this->productsList[ $this->pi ]['sku']            = $variable->get_sku();
				$this->productsList[ $this->pi ]['availability']   = $this->availability( $id );
				
				if ( 'first' == $this->feedRule['variable_price'] ) {
					$children = $variable->get_visible_children();
					$first    = false;
					if ( ! empty( $children ) ) {
						$firstChild = wc_get_product( $children[0] );
						if ( $firstChild && $firstChild instanceof WC_Product_Variable ) {
							$first  = true;
							$price  = $firstChild->get_regular_price();
							$sPrice = $firstChild->get_sale_price();
							$rPrice = $firstChild->get_sale_price();
						}
					}
					if ( ! $first ) {
						$price  = $variable->get_variation_regular_price();
						$sPrice = $variable->get_variation_sale_price();
						$rPrice = $variable->get_variation_sale_price();
					}
				} else {
					$price  = $variable->get_variation_price( $this->feedRule['variable_price'] );
					$sPrice = $variable->get_variation_sale_price( $this->feedRule['variable_price'] );
					$rPrice = $variable->get_variation_regular_price( $this->feedRule['variable_price'] );
				}
				
				
				$stock = $this->get_quantity( $id, '_stock', $variable );
				
				$this->productsList[ $this->pi ]['quantity']         = $stock;// $variable->get_stock_quantity();
				$this->productsList[ $this->pi ]['sale_price_sdate'] = $variable->get_date_on_sale_from();
				$this->productsList[ $this->pi ]['sale_price_edate'] = $variable->get_date_on_sale_to();
				$this->productsList[ $this->pi ]['price']            = apply_filters( 'woo_feed_convert_price',
					array(
						'filename' => $this->feedRule['filename'],
						'price'    => $rPrice,
					) );// $price;
				$this->productsList[ $this->pi ]['current_price']    = apply_filters( 'woo_feed_convert_price',
					array(
						'filename' => $this->feedRule['filename'],
						'price'    => $price,
					) );// $variable->get_price();
				$this->productsList[ $this->pi ]['price_with_tax']   = apply_filters( 'woo_feed_convert_price',
					array(
						'filename' => $this->feedRule['filename'],
						'price'    => ( $variable->is_taxable() ) ? $this->get_price_with_tax( $variable ) : $variable->get_price(),
					) );// ( $variable->is_taxable() ) ? $this->get_price_with_tax( $variable ) : $variable->get_price();
				$this->productsList[ $this->pi ]['sale_price']       = apply_filters( 'woo_feed_convert_price',
					array(
						'filename' => $this->feedRule['filename'],
						'price'    => $sPrice,
					) );// $sPrice;
				$this->productsList[ $this->pi ]['weight']           = $variable->get_weight();
				$this->productsList[ $this->pi ]['width']            = $variable->get_width();
				$this->productsList[ $this->pi ]['height']           = $variable->get_height();
				$this->productsList[ $this->pi ]['length']           = $variable->get_length();
				$this->productsList[ $this->pi ]['shipping_class']   = $variable->get_shipping_class();
				
				$this->productsList[ $this->pi ]['author_name']  = $this->get_author_info( $variable->get_id(),
					'name' );
				$this->productsList[ $this->pi ]['author_email'] = $this->get_author_info( $variable->get_id(),
					'email' );
				
				$this->productsList[ $this->pi ]['date_created'] = $this->format_product_date( $variable->get_date_created() );
				$this->productsList[ $this->pi ]['date_updated'] = $this->format_product_date( $variable->get_date_modified() );
				
				// Sale price effective date
				$from = $this->sale_price_effective_date( $id, '_sale_price_dates_from' );
				$to   = $this->sale_price_effective_date( $id, '_sale_price_dates_to' );
				if ( ! empty( $from ) && ! empty( $to ) ) {
					$from                                                         = gmdate( 'c', strtotime( $from ) );
					$to                                                           = gmdate( 'c', strtotime( $to ) );
					$this->productsList[ $this->pi ]['sale_price_effective_date'] = "$from" . '/' . "$to";
				} else {
					$this->productsList[ $this->pi ]['sale_price_effective_date'] = '';
				}           
}
			
			// SEO Plugin Data
			if (
				// not variation or
				( ! $prod->is_type( 'variation' ) ) ||
				// variable and config allowed both or only variable
				( $prod->is_type( 'variable' ) && ( 'both' == $this->feedRule['is_variations'] || 'n' == $this->feedRule['is_variations'] ) )
			) {
				$this->get_product_seo_data( $productId );
			}
			$this->pi ++;
		}
		// Remove duplicate products
		if ( $getIDs ) {
			$mergedIds = array_merge( $getIDs, $this->idExist );
			update_option( 'wf_check_duplicate', $mergedIds, false );
		} else {
			update_option( 'wf_check_duplicate', $this->idExist, false );
		}
		
		return $this->productsList;
	}
	
	/**
	 * Get SEO Title & Description of product
	 *
	 * @param int $productId
	 *
	 * @since 3.1.18
	 */
	public function get_product_seo_data( $productId ) {
		
		if ( class_exists( 'WPSEO_Frontend' ) ) {
			$this->productsList[ $this->pi ]['yoast_wpseo_title']    = WPSEO_Frontend::get_instance()->get_seo_title( get_post( $productId ) );
			$this->productsList[ $this->pi ]['yoast_wpseo_metadesc'] = wpseo_replace_vars( WPSEO_Meta::get_value( 'metadesc',
				$productId ),
				get_post( $productId ) );
		}
		if ( class_exists( 'All_in_One_SEO_Pack' ) ) {
			global $aioseop_options, $aiosp;
			if ( ! ( $aiosp instanceof All_in_One_SEO_Pack ) ) {
				$aiosp = new All_in_One_SEO_Pack();
			}
// if( ! is_array( $aioseop_options) ) $aioseop_options = get_option( 'aioseop_options' );
			if ( in_array( 'product', $aioseop_options['aiosp_cpostactive'], true ) ) {
				$title = '';
				if ( ! empty( $aioseop_options['aiosp_rewrite_titles'] ) ) {
					$title = $aiosp->get_aioseop_title( get_post( $productId ) );
					$title = $aiosp->apply_cf_fields( $title );
				}
				$this->productsList[ $this->pi ]['aioseop_title']       = apply_filters( 'aioseop_title', $title );
				$description                                            = $aiosp->get_main_description( get_post( $productId ) );    // Get the description.
				$description                                            = $aiosp->trim_description( $description );
				$description                                            = apply_filters( 'aioseop_description_full',
					$aiosp->apply_description_format( $description, get_post( $productId ) ) );
				$this->productsList[ $this->pi ]['aioseop_description'] = $description;
			}
		}
	}
	
	/**
	 * Get total price of grouped product
	 *
	 * @param WC_Product_Grouped $grouped
	 * @param string             $type
	 * @param bool               $taxable
	 *
	 * @return int|string
	 * @since 2.2.14
	 */
	public function getGroupProductPrice( $grouped, $type, $taxable = false ) {
		$groupProductIds = $grouped->get_children();
		$sum             = 0;
		if ( ! empty( $groupProductIds ) ) {
			foreach ( $groupProductIds as $id ) {
				$product = wc_get_product( $id );
				if ( ! $product ) {
					continue; // make sure that the product exists..
				}
				$regularPrice = $product->get_regular_price();
				$currentPrice = $product->get_price();
				if ( 'regular' == $type ) {
					if ( $taxable ) {
						$regularPrice = $this->get_price_with_tax( $product );
					}
					// @XXX warning if variable product added to the grouped product (Warning:  A non-numeric value encountered)
					// variable product doesn't have price (variation has the prices)...
					$sum += $regularPrice;
				} else {
					if ( $taxable ) {
						$currentPrice = $this->get_price_with_tax( $product );
					}
					$sum += $currentPrice;
				}
			}
		}
		
		return $sum;
	}
	
	/**
	 * Get Product Variations of a variable product
	 *
	 * @param array $variations
	 *
	 * @since  2.2.0
	 * @for    WC 3.1+
	 */
	public function getWC3Variations( $variations ) {
		if ( is_array( $variations ) && ( sizeof( $variations ) > 0 ) ) {
			foreach ( $variations as $vKey => $id ) {
				if ( ! empty( $this->ids_not_in ) && in_array( $id, $this->ids_not_in ) ) {
					continue;
				}
				if ( ! empty( $this->ids_in ) && ! in_array( $id, $this->ids_in ) ) {
					continue;
				}
				$variation = wc_get_product( $id );
				$parentId  = $variation->get_parent_id();
				$parent    = wc_get_product( $parentId );
				if ( ! $variation || ! ( $variation instanceof WC_Product_Variation ) || ! $parent || ! ( $parent instanceof WC_Product_Variable ) ) {
					continue;
				}
				// Skip if not a valid product
				if ( ! $variation->variation_is_visible() || 'publish' != $parent->get_status() ) {
					continue;
				}
				// Get Variation Description
				$description = $variation->get_description();
				if ( empty( $description ) ) {
					$description = $parent->get_description();
				}
				$description = $this->remove_short_codes( $description );
				// Get Variation Short Description
				$short_description = $variation->get_short_description();
				if ( empty( $short_description ) ) {
					$short_description = $parent->get_short_description();
				}
				
				$short_description = $this->remove_short_codes( $short_description );
				if ( 'facebook' == $this->feedRule['provider'] ) {
					$variationInfo = explode( '-', $variation->get_name() );
					if ( isset( $variationInfo[1] ) ) {
						$extension = $variationInfo[1];
					} else {
						$extension = $variation->get_id();
					}
					$description .= ' ' . $extension;
				}
				$this->productsList[ $this->pi ]['id']                = $variation->get_id();
				$this->productsList[ $this->pi ]['title']             = $variation->get_name();
				$this->productsList[ $this->pi ]['parent_title']      = $parent->get_name();
				$this->productsList[ $this->pi ]['description']       = $description;
				$this->productsList[ $this->pi ]['variation_type']    = $variation->get_type();
				$this->productsList[ $this->pi ]['short_description'] = $short_description;
				$this->productsList[ $this->pi ]['product_type']      = wp_strip_all_tags( wc_get_product_category_list( $variation->get_parent_id(), '>', '' ) );
				$this->productsList[ $this->pi ]['link']              = $variation->get_permalink();
				$this->productsList[ $this->pi ]['ex_link']           = $variation->get_permalink();
				// Featured Image
				$image = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'single-post-thumbnail' );
				if ( has_post_thumbnail( $id ) && ! empty( $image[0] ) ) :
					
					$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( $image[0] );
					$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( $image[0] );
				else :
					$image                                            = wp_get_attachment_image_src( get_post_thumbnail_id( $variation->get_parent_id() ),
						'single-post-thumbnail' );
					$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( $image[0] );
					$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( $image[0] );
				endif;
				// Additional Images
				$attachmentIds = $parent->get_gallery_image_ids();
				$imgUrls       = array();
				if ( $attachmentIds && is_array( $attachmentIds ) ) {
					$mKey = 1;
					foreach ( $attachmentIds as $attachmentId ) {
						$imgUrls[ $mKey ]                               = $this->get_formatted_url( wp_get_attachment_url( $attachmentId ) );
						$this->productsList[ $this->pi ][ "image_$mKey" ] = $imgUrls[ $mKey ];
						$mKey ++;
					}               
}
				
				$this->productsList[ $this->pi ]['images']           = implode( ',', $imgUrls );
				$this->productsList[ $this->pi ]['condition']        = 'new';
				$this->productsList[ $this->pi ]['type']             = $variation->get_type();
				$this->productsList[ $this->pi ]['is_bundle']        = 'no';
				$this->productsList[ $this->pi ]['multipack']        = '';
				$this->productsList[ $this->pi ]['visibility']       = $variation->get_catalog_visibility();
				$this->productsList[ $this->pi ]['rating_total']     = $variation->get_rating_count();
				$this->productsList[ $this->pi ]['rating_average']   = $variation->get_average_rating();
				$this->productsList[ $this->pi ]['tags']             = $this->getProductTaxonomy( $parentId,
					'product_tag' );
				$this->productsList[ $this->pi ]['item_group_id']    = $variation->get_parent_id();
				$this->productsList[ $this->pi ]['sku']              = $variation->get_sku();
				$this->productsList[ $this->pi ]['parent_sku']       = $parent->get_sku();
				$this->productsList[ $this->pi ]['availability']     = $this->availability( $id );
				$this->productsList[ $this->pi ]['quantity']         = $variation->get_stock_quantity();
				$this->productsList[ $this->pi ]['sale_price_sdate'] = $variation->get_date_on_sale_from();
				$this->productsList[ $this->pi ]['sale_price_edate'] = $variation->get_date_on_sale_to();
				$this->productsList[ $this->pi ]['price']            = apply_filters( 'woo_feed_convert_price', array(
					'filename' => $this->feedRule['filename'],
					'price'    => $variation->get_regular_price(),
				) );// $variation->get_regular_price();
				$this->productsList[ $this->pi ]['current_price']    = apply_filters( 'woo_feed_convert_price', array(
					'filename' => $this->feedRule['filename'],
					'price'    => $variation->get_price(),
				) );// $variation->get_price();
				$this->productsList[ $this->pi ]['price_with_tax']   = apply_filters( 'woo_feed_convert_price', array(
					'filename' => $this->feedRule['filename'],
					'price'    => ( $variation->is_taxable() ) ? $this->get_price_with_tax( $variation ) : $variation->get_price(),
				) );// ( $variation->is_taxable() ) ? $this->get_price_with_tax( $variation ) : $variation->get_price();
				$this->productsList[ $this->pi ]['sale_price']       = apply_filters( 'woo_feed_convert_price', array(
					'filename' => $this->feedRule['filename'],
					'price'    => $variation->get_sale_price(),
				) );// $variation->get_sale_price();
				$this->productsList[ $this->pi ]['weight']           = $variation->get_weight();
				$this->productsList[ $this->pi ]['width']            = $variation->get_width();
				$this->productsList[ $this->pi ]['height']           = $variation->get_height();
				$this->productsList[ $this->pi ]['length']           = $variation->get_length();
				$this->productsList[ $this->pi ]['shipping_class']   = $variation->get_shipping_class();
				
				$this->productsList[ $this->pi ]['author_name']  = $this->get_author_info( $variation->get_id(),
					'name' );
				$this->productsList[ $this->pi ]['author_email'] = $this->get_author_info( $variation->get_id(),
					'email' );
				
				$this->productsList[ $this->pi ]['date_created'] = $this->format_product_date( $variation->get_date_created() );
				$this->productsList[ $this->pi ]['date_updated'] = $this->format_product_date( $variation->get_date_modified() );
				
				// Sale price effective date
				$from = $this->sale_price_effective_date( $id, '_sale_price_dates_from' );
				$to   = $this->sale_price_effective_date( $id, '_sale_price_dates_to' );
				if ( ! empty( $from ) && ! empty( $to ) ) {
					$from                                                         = gmdate( 'c', strtotime( $from ) );
					$to                                                           = gmdate( 'c', strtotime( $to ) );
					$this->productsList[ $this->pi ]['sale_price_effective_date'] = "$from" . '/' . "$to";
				} else {
					$this->productsList[ $this->pi ]['sale_price_effective_date'] = '';
				}
				
				/**
				 * The feed engine will not check the fallback data if seo keys are not in the $value array...
				 * @see WF_Engine::productArrayByMapping()
				 */
				if ( class_exists( 'WPSEO_Frontend' ) ) {
					$this->productsList[ $this->pi ]['yoast_wpseo_title']    = '';
					$this->productsList[ $this->pi ]['yoast_wpseo_metadesc'] = '';
				}
				if ( class_exists( 'All_in_One_SEO_Pack' ) ) {
					$this->productsList[ $this->pi ]['aioseop_title']       = '';
					$this->productsList[ $this->pi ]['aioseop_description'] = '';
				}
				
				$this->pi ++;
			}
		}
	}
	
	/**
	 * At First convert Short Codes and then Remove failed Short Codes from String
	 *
	 * @param $content
	 *
	 * @return mixed|string
	 */
	public function remove_short_codes( $content ) {
		if ( empty( $content ) ) {
			return '';
		}

// # Remove Visual Composer Short Codes
// if(class_exists('WPBMap')){
// if(method_exists('addAllMappedShortcodes')){
// WPBMap::addAllMappedShortcodes(); // This does all the work
// $content = apply_filters( 'the_content',$content);
// }
// }
//
// $content = apply_filters( 'the_content',$content);
//
		// Remove DIVI Builder Short Codes
		if ( class_exists( 'ET_Builder_Module' ) || defined( 'ET_BUILDER_PLUGIN_VERSION' ) ) {
			/** @noinspection RegExpRedundantEscape */
			$content = preg_replace( '/\[\/?et_pb.*?\]/', '', $content );
		}
		
		$content = do_shortcode( $content );
		
		$content = $this->stripInvalidXml( $content );
		
		return strip_shortcodes( $content );
	}
	
	/**
	 * Remove Invalid Character from XML
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function stripInvalidXml( $value ) {
		return woo_feed_stripInvalidXml( $value );
	}
	
	/**
	 *  Get Ids to Exclude or Include from product list
	 *
	 * @since 2.2.0
	 * @for WC 3.1+
	 */
	public function inExProducts() {
		// Argument for Product search by ID
		if ( isset( $this->feedRule['fattribute'] ) && is_array( $this->feedRule['fattribute'] ) ) {
			if ( count( $this->feedRule['fattribute'] ) ) {
				$condition        = $this->feedRule['condition'];
				$compare          = $this->feedRule['filterCompare'];
				$this->ids_in     = array();
				$this->ids_not_in = array();
				foreach ( $this->feedRule['fattribute'] as $key => $rule ) {
					if ( 'id' == $rule && in_array( $condition[ $key ], array( '==', 'contain' ) ) ) {
						unset( $this->feedRule['fattribute'][ $key ] );
						unset( $this->feedRule['condition'][ $key ] );
						unset( $this->feedRule['filterCompare'][ $key ] );
						if ( strpos( $compare[ $key ], ',' ) !== false ) {
							foreach ( explode( ',', $compare[ $key ] ) as $key => $id ) {
								array_push( $this->ids_in, $id );
							}
						} else {
							array_push( $this->ids_in, $compare[ $key ] );
						}
					} elseif ( 'id' == $rule && in_array( $condition[ $key ], array( '!=', 'nContains' ) ) ) {
						unset( $this->feedRule['fattribute'][ $key ] );
						unset( $this->feedRule['condition'][ $key ] );
						unset( $this->feedRule['filterCompare'][ $key ] );
						if ( strpos( $compare[ $key ], ',' ) !== false ) {
							foreach ( explode( ',', $compare[ $key ] ) as $key => $id ) {
								array_push( $this->ids_not_in, $id );
							}
						} else {
							array_push( $this->ids_not_in, $compare[ $key ] );
						}
					}
				}
			}
		}
	}
	
	/**
	 * Get WooCommerce Products
	 *
	 * @param $limit
	 * @param $offset
	 * @param $categories
	 * @param $feedRule
	 *
	 * @return array
	 */
	public function woo_feed_get_visible_product( $limit, $offset, $categories, $feedRule ) {
		
		$this->feedRule   = $feedRule;
		$this->categories = $categories;
		$this->vendors    = isset( $feedRule['vendors'] ) ? $feedRule['vendors'] : '';
		
		if ( get_option( 'WPFP_WPML' ) && get_option( 'WPFP_WPML' ) == 'yes' ) {
			$this->WPFP_WPML = true;
		}
		
		// WC 3.2+ Compatibility
		
		if ( woo_feed_wc_version_check( 3.0 ) ) {
			
			$products = $this->getWC3Products( $limit, $offset );
			
			return $products;
		} else {
			
			try {
				
				if ( $this->WPFP_WPML ) {
					$this->currentLang = apply_filters( 'wpml_current_language', null );
					if ( isset( $feedRule['feedLanguage'] ) && $feedRule['feedLanguage'] != $this->currentLang ) {
						do_action( 'wpml_switch_language', $feedRule['feedLanguage'] );
					}
				}
				
				$limit  = ! empty( $limit ) && is_numeric( $limit ) ? absint( $limit ) : '-1';
				$offset = ! empty( $offset ) && is_numeric( $offset ) ? absint( $offset ) : '0';
				
				$arg = array(
					'post_type'              => array( 'product' ),
					'post_status'            => 'publish',
					'ignore_sticky_posts'    => 1,
					'posts_per_page'         => $limit,
					'orderby'                => 'date',
					'order'                  => 'desc',
					'fields'                 => 'ids',
					'offset'                 => $offset,
					'cache_results'          => false,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
				);
				
				// Argument for Product search by ID
				if ( isset( $this->feedRule['fattribute'] ) && is_array( $this->feedRule['fattribute'] ) ) {
					if ( count( $this->feedRule['fattribute'] ) ) {
						$condition        = $this->feedRule['condition'];
						$compare          = $this->feedRule['filterCompare'];
						$this->ids_in     = array();
						$this->ids_not_in = array();
						foreach ( $this->feedRule['fattribute'] as $key => $rule ) {
							if ( 'id' == $rule && in_array( $condition[ $key ], array( '==', 'contain' ) ) ) {
								unset( $this->feedRule['fattribute'][ $key ] );
								unset( $this->feedRule['condition'][ $key ] );
								unset( $this->feedRule['filterCompare'][ $key ] );
								if ( strpos( $compare[ $key ], ',' ) !== false ) {
									foreach ( explode( ',', $compare[ $key ] ) as $key => $id ) {
										array_push( $this->ids_in, $id );
									}
								} else {
									array_push( $this->ids_in, $compare[ $key ] );
								}
							} elseif ( 'id' == $rule && in_array( $condition[ $key ],
									array( '!=', 'nContains' ) ) ) {
								unset( $this->feedRule['fattribute'][ $key ] );
								unset( $this->feedRule['condition'][ $key ] );
								unset( $this->feedRule['filterCompare'][ $key ] );
								if ( strpos( $compare[ $key ], ',' ) !== false ) {
									foreach ( explode( ',', $compare[ $key ] ) as $key => $id ) {
										array_push( $this->ids_not_in, $id );
									}
								} else {
									array_push( $this->ids_not_in, $compare[ $key ] );
								}
							}
						}
						
						if ( count( $this->ids_in ) ) {
							$arg['post__in'] = $this->ids_in;
						}
						
						if ( count( $this->ids_not_in ) ) {
							$arg['post__not_in'] = $this->ids_not_in;
						}
					}
				}
				
				if ( is_array( $categories ) && ! empty( $categories[0] ) ) {
					$i                            = 0;
					$arg['tax_query']['relation'] = 'OR';
					foreach ( $categories as $key => $value ) {
						if ( ! empty( $value ) ) {
							$arg['tax_query'][ $i ]['taxonomy'] = 'product_cat';
							$arg['tax_query'][ $i ]['field']    = 'slug';
							$arg['tax_query'][ $i ]['terms']    = $value;
							$i ++;
						}
					}
				}
				
				// Query Database for products
				$loop = new WP_Query( $arg );
				$i    = 0;
				
				while ( $loop->have_posts() ) :
$loop->the_post();
					
					$this->childID  = get_the_ID();
					$this->parentID = wp_get_post_parent_id( $this->childID );
					
					global $product;
					$type1 = '';
					
					if ( ! is_object( $product ) ) {
						$product = wc_get_product( get_the_ID() );
						if ( ! is_object( $product ) ) {
							continue;
						}
					}
					
					if ( ! $product->is_visible() ) {
						continue;
					}
					
					if ( is_object( $product ) && $product->is_type( 'simple' ) ) {
						// No variations to product
						$type1 = 'simple';
					} elseif ( is_object( $product ) && $product->is_type( 'variable' ) ) {
						// Product has variations
						$type1 = 'variable';
					} elseif ( is_object( $product ) && $product->is_type( 'grouped' ) ) {
						$type1 = 'grouped';
					} elseif ( is_object( $product ) && $product->is_type( 'external' ) ) {
						$type1 = 'external';
					} elseif ( is_object( $product ) && $product->is_downloadable() ) {
						$type1 = 'downloadable';
					} elseif ( is_object( $product ) && $product->is_virtual() ) {
						$type1 = 'virtual';
					}
					
					$post = get_post( $this->parentID );
					
					
					if ( 'trash' == $post->post_status ) {
						continue;
					}

// if ( $this->feedRule['provider'] == 'facebook' ) {
// $this->feedRule['is_variations'] = 'n';
// }
					
					if ( ! isset( $this->feedRule['is_variations'] ) ) {
						$this->feedRule['is_variations'] = 'y';
					}
					
					if ( ! isset( $this->feedRule['is_outOfStock'] ) ) {
						$this->feedRule['is_outOfStock'] = 'n';
					}
					
					if ( ! isset( $this->feedRule['variable_price'] ) ) {
						$this->feedRule['variable_price'] = 'first';
					}
					
					if ( 'product' == get_post_type() ) {
						if ( 'simple' == $type1 ) {
							
							if ( 'y' == $this->feedRule['is_outOfStock'] ) {
								if ( 'out of stock' == $this->availability( $post->ID ) ) {
									continue;
								}
							}
							
							$mainImage = wp_get_attachment_url( $product->get_image_id() );
							$link      = get_permalink( $post->ID );
							
							
							if ( 'custom' != $this->feedRule['provider'] ) {
								if ( 'http' !== substr( trim( $link ), 0, 4 ) && 'http' !== substr( trim( $mainImage ), 0, 4 ) ) {
									continue;
								}
							}
							
							$this->productsList[ $i ]['id']             = $post->ID;
							$this->productsList[ $i ]['variation_type'] = 'simple';
							$this->productsList[ $i ]['title']          = $post->post_title;
							$this->productsList[ $i ]['description']    = $post->post_content;
							
							$this->productsList[ $i ]['short_description'] = $post->post_excerpt;
							$this->productsList[ $i ]['product_type']      = $this->get_product_term_list( $post->ID, 'product_cat', '', '>' );// $this->categories($this->parentID);//TODO
							$this->productsList[ $i ]['link']              = $link;
							$this->productsList[ $i ]['ex_link']           = '';
							$this->productsList[ $i ]['image']             = $this->get_formatted_url( $mainImage );
							
							// Featured Image
							if ( has_post_thumbnail( $post->ID ) ) :
								$image                                     = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ),
									'single-post-thumbnail' );
								$this->productsList[ $i ]['feature_image'] = $this->get_formatted_url( $image[0] );
							else :
								$this->productsList[ $i ]['feature_image'] = $this->get_formatted_url( $mainImage );
							endif;
							
							// Additional Images
							$imageLinks = array();
							$images     = $this->additionalImages( $post->ID );
							if ( $images && is_array( $images ) ) {
								$mKey = 1;
								foreach ( $images as $key => $value ) {
									if ( $value != $this->productsList[ $i ]['image'] ) {
										$imgLink                                 = $this->get_formatted_url( $value );
										$this->productsList[ $i ][ "image_$mKey" ] = $imgLink;
										if ( ! empty( $imgLink ) ) {
											array_push( $imageLinks, $imgLink );
										}
									}
									$mKey ++;
								}
							}
							$this->productsList[ $i ]['images']         = implode( ',', $imageLinks );
							$this->productsList[ $i ]['condition']      = 'new';
							$this->productsList[ $i ]['type']           = $product->get_type();
							$this->productsList[ $i ]['visibility']     = $this->getAttributeValue( $post->ID, '_visibility' );
							$this->productsList[ $i ]['rating_total']   = $product->get_rating_count();
							$this->productsList[ $i ]['rating_average'] = $product->get_average_rating();
							$this->productsList[ $i ]['tags']           = $this->get_product_term_list( $post->ID, 'product_tag' );
							
							$this->productsList[ $i ]['item_group_id'] = $post->ID;
							$this->productsList[ $i ]['sku']           = $product->get_sku();
							
							$this->productsList[ $i ]['availability'] = $this->availability( $post->ID );
							
							$this->productsList[ $i ]['quantity']         = $this->get_quantity( $post->ID, '_stock', $product );
							$this->productsList[ $i ]['sale_price_sdate'] = $this->get_date( $post->ID, '_sale_price_dates_from' );
							$this->productsList[ $i ]['sale_price_edate'] = $this->get_date( $post->ID, '_sale_price_dates_to' );
							$this->productsList[ $i ]['price']            = ( $product->get_regular_price() ) ? $product->get_regular_price() : $product->get_price();
							$this->productsList[ $i ]['current_price']    = $product->get_price();
							$this->productsList[ $i ]['price_with_tax']   = ( $product->is_taxable() ) ? $this->get_price_with_tax( $product ) : $product->get_price();
							$this->productsList[ $i ]['sale_price']       = ( $product->get_sale_price() ) && ( $product->get_sale_price() > 0 ) ? $product->get_sale_price() : '';
							$this->productsList[ $i ]['weight']           = ( $product->get_weight() ) ? $product->get_weight() : '';
							$this->productsList[ $i ]['width']            = ( $product->get_width() ) ? $product->get_width() : '';
							$this->productsList[ $i ]['height']           = ( $product->get_height() ) ? $product->get_height() : '';
							$this->productsList[ $i ]['length']           = ( $product->get_length() ) ? $product->get_length() : '';
							
							// Sale price effective date
							$from = $this->sale_price_effective_date( $post->ID, '_sale_price_dates_from' );
							$to   = $this->sale_price_effective_date( $post->ID, '_sale_price_dates_to' );
							if ( ! empty( $from ) && ! empty( $to ) ) {
								$from                                                  = gmdate( 'c',
									strtotime( $from ) );
								$to                                                    = gmdate( 'c', strtotime( $to ) );
								$this->productsList[ $i ]['sale_price_effective_date'] = "$from" . '/' . "$to";
							} else {
								$this->productsList[ $i ]['sale_price_effective_date'] = '';
							}                       
						} elseif ( 'external' == $type1 ) {
							
							if ( 'y' == $this->feedRule['is_outOfStock'] ) {
								if ( 'out of stock' == $this->availability( $post->ID ) ) {
									continue;
								}
							}
							
							$mainImage = wp_get_attachment_url( $product->get_image_id() );
							
							$getLink = new WC_Product_External( $post->ID );
							$EX_link = $getLink->get_product_url();
							$link    = get_permalink( $post->ID );
							if ( 'custom' != $this->feedRule['provider'] ) {
								if ( 'http' !== substr( trim( $link ), 0, 4 ) && 'http' !== substr( trim( $mainImage ), 0, 4 ) ) {
									continue;
								}
							}
							
							$this->productsList[ $i ]['id']             = $post->ID;
							$this->productsList[ $i ]['variation_type'] = 'external';
							$this->productsList[ $i ]['title']          = $post->post_title;
							$this->productsList[ $i ]['description']    = $post->post_content;
							
							$this->productsList[ $i ]['short_description'] = $post->post_excerpt;
							$this->productsList[ $i ]['product_type']      = $this->get_product_term_list( $post->ID, 'product_cat', '', '>' );// $this->categories($this->parentID);//TODO
							$this->productsList[ $i ]['link']              = $link;
							$this->productsList[ $i ]['ex_link']           = $EX_link;
							$this->productsList[ $i ]['image']             = $this->get_formatted_url( $mainImage );
							
							// Featured Image
							if ( has_post_thumbnail( $post->ID ) ) :
								$image                                     = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
								$this->productsList[ $i ]['feature_image'] = $this->get_formatted_url( $image[0] );
							else :
								$this->productsList[ $i ]['feature_image'] = $this->get_formatted_url( $mainImage );
							endif;
							
							// Additional Images
							$imageLinks = array();
							$images     = $this->additionalImages( $post->ID );
							if ( $images && is_array( $images ) ) {
								$mKey = 1;
								foreach ( $images as $key => $value ) {
									if ( $value != $this->productsList[ $i ]['image'] ) {
										$imgLink                                 = $this->get_formatted_url( $value );
										$this->productsList[ $i ][ "image_$mKey" ] = $imgLink;
										if ( ! empty( $imgLink ) ) {
											array_push( $imageLinks, $imgLink );
										}
									}
									$mKey ++;
								}
							}
							$this->productsList[ $i ]['images']         = implode( ',', $imageLinks );
							$this->productsList[ $i ]['condition']      = 'new';
							$this->productsList[ $i ]['type']           = $product->get_type();
							$this->productsList[ $i ]['visibility']     = $this->getAttributeValue( $post->ID, '_visibility' );
							$this->productsList[ $i ]['rating_total']   = $product->get_rating_count();
							$this->productsList[ $i ]['rating_average'] = $product->get_average_rating();
							$this->productsList[ $i ]['tags']           = $this->get_product_term_list( $post->ID, 'product_tag' );
							
							$this->productsList[ $i ]['item_group_id'] = $post->ID;
							$this->productsList[ $i ]['sku']           = $product->get_sku();
							
							$this->productsList[ $i ]['availability'] = $this->availability( $post->ID );
							
							$this->productsList[ $i ]['quantity']         = $this->get_quantity( $post->ID, '_stock', $product );
							$this->productsList[ $i ]['sale_price_sdate'] = $this->get_date( $post->ID, '_sale_price_dates_from' );
							$this->productsList[ $i ]['sale_price_edate'] = $this->get_date( $post->ID, '_sale_price_dates_to' );
							$this->productsList[ $i ]['price']            = ( $product->get_regular_price() ) ? $product->get_regular_price() : $product->get_price();
							$this->productsList[ $i ]['current_price']    = $product->get_price();
							$this->productsList[ $i ]['price_with_tax']   = ( $product->is_taxable() ) ? $this->get_price_with_tax( $product ) : $product->get_price();
							$this->productsList[ $i ]['sale_price']       = ( $product->get_sale_price() ) && ( $product->get_sale_price() > 0 ) ? $product->get_sale_price() : '';
							$this->productsList[ $i ]['weight']           = ( $product->get_weight() ) ? $product->get_weight() : '';
							$this->productsList[ $i ]['width']            = ( $product->get_width() ) ? $product->get_width() : '';
							$this->productsList[ $i ]['height']           = ( $product->get_height() ) ? $product->get_height() : '';
							$this->productsList[ $i ]['length']           = ( $product->get_length() ) ? $product->get_length() : '';
							
							// Sale price effective date
							$from = $this->sale_price_effective_date( $post->ID, '_sale_price_dates_from' );
							$to   = $this->sale_price_effective_date( $post->ID, '_sale_price_dates_to' );
							if ( ! empty( $from ) && ! empty( $to ) ) {
								$from                                                  = gmdate( 'c', strtotime( $from ) );
								$to                                                    = gmdate( 'c', strtotime( $to ) );
								$this->productsList[ $i ]['sale_price_effective_date'] = "$from" . '/' . "$to";
							} else {
								$this->productsList[ $i ]['sale_price_effective_date'] = '';
							}                       
						} elseif ( 'grouped' == $type1 ) {
							
							if ( 'y' == $this->feedRule['is_outOfStock'] ) {
								if ( 'out of stock' == $this->availability( $post->ID ) ) {
									continue;
								}
							}
							
							$grouped        = new WC_Product_Grouped( $post->ID );
							$children       = $grouped->get_children();
							$this->parentID = $post->ID;
							if ( $children ) {
								foreach ( $children as $cKey => $child ) {
									
									$product       = wc_get_product( $child );
									$this->childID = $child;
									$post          = get_post( $this->childID );
									
									if ( 'y' == $this->feedRule['is_outOfStock'] ) {
										if ( 'out of stock' == $this->availability( $post->ID ) ) {
											continue;
										}
									}
									
									if ( ! empty( $this->ids_in ) && ! in_array( $post->ID, $this->ids_in ) ) {
										continue;
									}
									
									if ( ! empty( $this->ids_not_in ) && in_array( $post->ID, $this->ids_in ) ) {
										continue;
									}
									
									if ( ! $product->is_visible() ) {
										continue;
									}
									
									if ( 'trash' == $post->post_status ) {
										continue;
									}
									
									$i ++;
									
									$mainImage = wp_get_attachment_url( $product->get_image_id() );
									$link      = get_permalink( $post->ID );
									if ( 'custom' != $this->feedRule['provider'] ) {
										if ( 'http' !== substr( trim( $link ), 0, 4 ) && 'http' !== substr( trim( $mainImage ), 0, 4 ) ) {
											continue;
										}
									}
									
									$this->productsList[ $i ]['id']             = $post->ID;
									$this->productsList[ $i ]['variation_type'] = 'grouped';
									$this->productsList[ $i ]['title']          = $post->post_title;
									$this->productsList[ $i ]['description']    = $post->post_content;
									
									$this->productsList[ $i ]['short_description'] = $post->post_excerpt;
									$this->productsList[ $i ]['product_type']      = $this->get_product_term_list( $post->ID, 'product_cat', '', '>' );// $this->categories($this->parentID);//TODO
									$this->productsList[ $i ]['link']              = $link;
									$this->productsList[ $i ]['ex_link']           = '';
									$this->productsList[ $i ]['image']             = $this->get_formatted_url( $mainImage );
									
									// Featured Image
									if ( has_post_thumbnail( $post->ID ) ) :
										$image                                     = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
										$this->productsList[ $i ]['feature_image'] = $this->get_formatted_url( $image[0] );
									else :
										$this->productsList[ $i ]['feature_image'] = $this->get_formatted_url( $mainImage );
									endif;
									
									// Additional Images
									$imageLinks = array();
									$images     = $this->additionalImages( $this->childID );
									if ( $images && is_array( $images ) ) {
										$mKey = 1;
										foreach ( $images as $key => $value ) {
											if ( $value != $this->productsList[ $i ]['image'] ) {
												$imgLink                                 = $this->get_formatted_url( $value );
												$this->productsList[ $i ][ "image_$mKey" ] = $imgLink;
												if ( ! empty( $imgLink ) ) {
													array_push( $imageLinks, $imgLink );
												}
											}
											$mKey ++;
										}
									}
									$this->productsList[ $i ]['images']         = implode( ',', $imageLinks );
									$this->productsList[ $i ]['condition']      = 'new';
									$this->productsList[ $i ]['type']           = $product->get_type();
									$this->productsList[ $i ]['visibility']     = $this->getAttributeValue( $post->ID, '_visibility' );
									$this->productsList[ $i ]['rating_total']   = $product->get_rating_count();
									$this->productsList[ $i ]['rating_average'] = $product->get_average_rating();
									$this->productsList[ $i ]['tags']           = $this->get_product_term_list( $post->ID, 'product_tag' );
									
									$this->productsList[ $i ]['item_group_id'] = $this->parentID;
									$this->productsList[ $i ]['sku']           = $product->get_sku();
									
									$this->productsList[ $i ]['availability'] = $this->availability( $post->ID );
									
									$this->productsList[ $i ]['quantity']         = $this->get_quantity( $post->ID, '_stock', $product );
									$this->productsList[ $i ]['sale_price_sdate'] = $this->get_date( $post->ID, '_sale_price_dates_from' );
									$this->productsList[ $i ]['sale_price_edate'] = $this->get_date( $post->ID, '_sale_price_dates_to' );
									$this->productsList[ $i ]['price']            = ( $product->get_regular_price() ) ? $product->get_regular_price() : $product->get_price();
									$this->productsList[ $i ]['current_price']    = $product->get_price();
									$this->productsList[ $i ]['price_with_tax']   = ( $product->is_taxable() ) ? $this->get_price_with_tax( $product ) : $product->get_price();
									$this->productsList[ $i ]['sale_price']       = ( $product->get_sale_price() ) && ( $product->get_sale_price() > 0 ) ? $product->get_sale_price() : '';
									$this->productsList[ $i ]['weight']           = ( $product->get_weight() ) ? $product->get_weight() : '';
									$this->productsList[ $i ]['width']            = ( $product->get_width() ) ? $product->get_width() : '';
									$this->productsList[ $i ]['height']           = ( $product->get_height() ) ? $product->get_height() : '';
									$this->productsList[ $i ]['length']           = ( $product->get_length() ) ? $product->get_length() : '';
									
									// Sale price effective date
									$from = $this->sale_price_effective_date( $post->ID, '_sale_price_dates_from' );
									$to   = $this->sale_price_effective_date( $post->ID, '_sale_price_dates_to' );
									if ( ! empty( $from ) && ! empty( $to ) ) {
										$from                                                  = gmdate( 'c', strtotime( $from ) );
										$to                                                    = gmdate( 'c', strtotime( $to ) );
										$this->productsList[ $i ]['sale_price_effective_date'] = "$from" . '/' . "$to";
									} else {
										$this->productsList[ $i ]['sale_price_effective_date'] = '';
									}
								}
							}
						} elseif ( 'variable' == $type1 && $product->has_child() ) {
							
							
							if ( 'n' == $this->feedRule['is_variations'] ) {
								
								if ( 'y' == $this->feedRule['is_outOfStock'] ) {
									if ( 'out of stock' == $this->availability( $post->ID ) ) {
										continue;
									}
								}
								
								$mainImage = wp_get_attachment_url( $product->get_image_id() );
								$link      = get_permalink( $post->ID );
								
								if ( 'custom' != $this->feedRule['provider'] ) {
									if ( 'http' !== substr( trim( $link ), 0, 4 ) && 'http' !== substr( trim( $mainImage ), 0, 4 ) ) {
										continue;
									}
								}
								
								$this->productsList[ $i ]['id']             = $post->ID;
								$this->productsList[ $i ]['variation_type'] = 'parent';
								$this->productsList[ $i ]['title']          = $post->post_title;
								$this->productsList[ $i ]['description']    = $post->post_content;
								
								$this->productsList[ $i ]['short_description'] = $post->post_excerpt;
								$this->productsList[ $i ]['product_type']      = $this->get_product_term_list( $post->ID, 'product_cat', '', '>' );// $this->categories($this->parentID);//TODO
								$this->productsList[ $i ]['link']              = $link;
								$this->productsList[ $i ]['ex_link']           = '';
								$this->productsList[ $i ]['image']             = $this->get_formatted_url( $mainImage );
								
								// Featured Image
								if ( has_post_thumbnail( $post->ID ) ) :
									$image                                     = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
									$this->productsList[ $i ]['feature_image'] = $this->get_formatted_url( $image[0] );
								else :
									$this->productsList[ $i ]['feature_image'] = $this->get_formatted_url( $mainImage );
								endif;
								
								// Additional Images
								$imageLinks = array();
								$images     = $this->additionalImages( $this->childID );
								if ( $images && is_array( $images ) ) {
									$mKey = 1;
									foreach ( $images as $key => $value ) {
										if ( $value != $this->productsList[ $i ]['image'] ) {
											$imgLink                                 = $this->get_formatted_url( $value );
											$this->productsList[ $i ][ "image_$mKey" ] = $imgLink;
											if ( ! empty( $imgLink ) ) {
												array_push( $imageLinks, $imgLink );
											}
										}
										$mKey ++;
									}
								}
								$this->productsList[ $i ]['images'] = implode( ',', $imageLinks );
								
								$this->productsList[ $i ]['condition']      = 'new';
								$this->productsList[ $i ]['type']           = $product->get_type();
								$this->productsList[ $i ]['visibility']     = $this->getAttributeValue( $post->ID, '_visibility' );
								$this->productsList[ $i ]['rating_total']   = $product->get_rating_count();
								$this->productsList[ $i ]['rating_average'] = $product->get_average_rating();
								$this->productsList[ $i ]['tags']           = $this->get_product_term_list( $post->ID, 'product_tag' );
								
								$this->productsList[ $i ]['item_group_id'] = $post->ID;
								$this->productsList[ $i ]['sku']           = $product->get_sku();
								
								$this->productsList[ $i ]['availability'] = $this->availability( $post->ID );
								
								$this->productsList[ $i ]['quantity']         = $this->get_quantity( $post->ID, '_stock', $product );
								$this->productsList[ $i ]['sale_price_sdate'] = $this->get_date( $post->ID, '_sale_price_dates_from' );
								$this->productsList[ $i ]['sale_price_edate'] = $this->get_date( $post->ID, '_sale_price_dates_to' );
								
								
								if ( 'first' == $this->feedRule['variable_price'] ) {
									$price  = ( $product->get_price() ) ? $product->get_price() : '';
									$sPrice = ( $product->get_sale_price() ) ? $product->get_sale_price() : '';
								} else {
									$getVariable = new WC_Product_Variable( $product );
									$price       = $getVariable->get_variation_regular_price( $this->feedRule['variable_price'],
										$product->is_taxable() );
									$sPrice      = $getVariable->get_variation_sale_price( $this->feedRule['variable_price'],
										$product->is_taxable() );
								}
								
								
								$this->productsList[ $i ]['price']          = ( $price ) ? $price : $product->get_price();
								$this->productsList[ $i ]['current_price']  = $product->get_price();
								$this->productsList[ $i ]['price_with_tax'] = ( $product->is_taxable() ) ? $this->get_price_with_tax( $product ) : $price;
								$this->productsList[ $i ]['sale_price']     = ( $sPrice ) && ( $sPrice > 0 ) ? $sPrice : '';
								$this->productsList[ $i ]['weight']         = ( $product->get_weight() ) ? $product->get_weight() : '';
								$this->productsList[ $i ]['width']          = ( $product->get_width() ) ? $product->get_width() : '';
								$this->productsList[ $i ]['height']         = ( $product->get_height() ) ? $product->get_height() : '';
								$this->productsList[ $i ]['length']         = ( $product->get_length() ) ? $product->get_length() : '';
								
								// Sale price effective date
								$from = $this->sale_price_effective_date( $post->ID, '_sale_price_dates_from' );
								$to   = $this->sale_price_effective_date( $post->ID, '_sale_price_dates_to' );
								if ( ! empty( $from ) && ! empty( $to ) ) {
									$from                                                  = gmdate( 'c', strtotime( $from ) );
									$to                                                    = gmdate( 'c', strtotime( $to ) );
									$this->productsList[ $i ]['sale_price_effective_date'] = "$from" . '/' . "$to";
								} else {
									$this->productsList[ $i ]['sale_price_effective_date'] = '';
								}                           
}
							
							if ( 'y' == $this->feedRule['is_variations'] ) {
								// Get Child Products
								if ( $product->has_child() ) {
									$variations = $product->get_available_variations();
									
									if ( ! empty( $variations ) ) {
										foreach ( $variations as $key => $variation ) {
											
											$status = get_post( $this->childID );
											
											if ( ! $status || ! is_object( $status ) ) {
												continue;
											}
											
											if ( 'trash' == $status->post_status ) {
												continue;
											}
											
											$this->childID  = $variation['variation_id'];
											$this->parentID = wp_get_post_parent_id( $this->childID );
											
											if ( 'y' == $this->feedRule['is_outOfStock'] ) {
												if ( 'out of stock' == $this->availability( $this->childID ) ) {
													continue;
												}
											}
											
											if ( ! empty( $this->ids_in ) && ! in_array( $variation['variation_id'], $this->ids_in ) ) {
												continue;
											}
											
											if ( ! empty( $this->ids_not_in ) && in_array( $variation['variation_id'], $this->ids_in ) ) {
												continue;
											}
											
											if ( ! $variation['variation_is_visible'] ) {
												continue;
											}
											
											
											$i ++;
											
											$product   = wc_get_product( $this->childID );
											$mainImage = wp_get_attachment_url( $product->get_image_id() );
											
											if ( ! $mainImage ) {
												$image     = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
												$mainImage = $this->get_formatted_url( $image[0] );
											}
											$link = $product->get_permalink();
											if ( 'http' == substr( trim( $link ), 0, 4 ) ) {
												$link = get_permalink( $this->parentID );
											}
											
											
											$this->productsList[ $i ]['id']             = $this->childID;
											$this->productsList[ $i ]['variation_type'] = 'child';
											$this->productsList[ $i ]['item_group_id']  = $this->parentID;
											$this->productsList[ $i ]['sku']            = $product->get_sku();
											$this->productsList[ $i ]['parent_sku']     = $this->getAttributeValue( $this->parentID, '_sku' );
											$this->productsList[ $i ]['title']          = $product->get_title();
											$this->productsList[ $i ]['description']    = $post->post_content;
											
											// Short Description to variable description
											$vDesc = $this->getAttributeValue( $this->childID, '_variation_description' );
											
											if ( ! empty( $vDesc ) ) {
												$this->productsList[ $i ]['short_description'] = $vDesc;
											} else {
												$this->productsList[ $i ]['short_description'] = $post->post_excerpt;
											}
											
											$this->productsList[ $i ]['product_type'] = $this->get_product_term_list( $post->ID, 'product_cat', '', '>' );// $this->categories($this->parentID);//TODO
											$this->productsList[ $i ]['link']         = $link;
											$this->productsList[ $i ]['ex_link']      = '';
											$this->productsList[ $i ]['image']        = $this->get_formatted_url( $mainImage );
											
											// Featured Image
											if ( has_post_thumbnail( $post->ID ) ) :
												$image                                     = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
												$this->productsList[ $i ]['feature_image'] = $this->get_formatted_url( $image[0] );
											else :
												$this->productsList[ $i ]['feature_image'] = $this->get_formatted_url( $mainImage );
											endif;

// # Additional Images
// $imageLinks=array();
// $images = $this->additionalImages($this->parentID);
// if ($images and is_array($images)) {
// foreach ($images as $key => $value) {
// if ($value != $this->productsList[$i]['image']) {
// $imgLink=$this->get_formatted_url($value);
// $this->productsList[$i]["image_$key"] = $imgLink;
// if(!empty($imgLink)){
// array_push($imageLinks,$imgLink);
// }
// }
// }
// }
											
											// Additional Images
											$imageLinks = array();
											$images     = $this->additionalImages( $this->parentID );
											if ( $images && is_array( $images ) ) {
												$mKey = 1;
												foreach ( $images as $key => $value ) {
													if ( $value != $this->productsList[ $i ]['image'] ) {
														$imgLink                                 = $this->get_formatted_url( $value );
														$this->productsList[ $i ][ "image_$mKey" ] = $imgLink;
														if ( ! empty( $imgLink ) ) {
															array_push( $imageLinks, $imgLink );
														}
													}
													$mKey ++;
												}
											}
											
											$this->productsList[ $i ]['images']         = implode( ',', $imageLinks );
											$this->productsList[ $i ]['condition']      = 'new';
											$this->productsList[ $i ]['type']           = $product->get_type();
											$this->productsList[ $i ]['visibility']     = $this->getAttributeValue( $this->childID, '_visibility' );
											$this->productsList[ $i ]['rating_total']   = $product->get_rating_count();
											$this->productsList[ $i ]['rating_average'] = $product->get_average_rating();
											$this->productsList[ $i ]['tags']           = $this->get_product_term_list( $post->ID, 'product_tag' );
											$this->productsList[ $i ]['shipping']       = $product->get_shipping_class();
											
											$this->productsList[ $i ]['availability']     = $this->availability( $this->childID );
											$this->productsList[ $i ]['quantity']         = $this->get_quantity( $this->childID, '_stock', $product );
											$this->productsList[ $i ]['sale_price_sdate'] = $this->get_date( $this->childID, '_sale_price_dates_from' );
											$this->productsList[ $i ]['sale_price_edate'] = $this->get_date( $this->childID, '_sale_price_dates_to' );
											$this->productsList[ $i ]['price']            = ( $product->get_regular_price() ) ? $product->get_regular_price() : $product->get_price();
											$this->productsList[ $i ]['current_price']    = $product->get_price();
											$this->productsList[ $i ]['price_with_tax']   = ( $product->is_taxable() ) ? $this->get_price_with_tax( $product ) : $product->get_price();
											$this->productsList[ $i ]['sale_price']       = ( $product->get_sale_price() ) && ( $product->get_sale_price() > 0 ) ? $product->get_sale_price() : '';
											$this->productsList[ $i ]['weight']           = ( $product->get_weight() ) ? $product->get_weight() : '';
											$this->productsList[ $i ]['width']            = ( $product->get_width() ) ? $product->get_width() : '';
											$this->productsList[ $i ]['height']           = ( $product->get_height() ) ? $product->get_height() : '';
											$this->productsList[ $i ]['length']           = ( $product->get_length() ) ? $product->get_length() : '';
											
											// Sale price effective date
											$from = $this->sale_price_effective_date( $this->childID, '_sale_price_dates_from' );
											$to   = $this->sale_price_effective_date( $this->childID, '_sale_price_dates_to' );
											if ( ! empty( $from ) && ! empty( $to ) ) {
												$from                                                  = gmdate( 'c', strtotime( $from ) );
												$to                                                    = gmdate( 'c', strtotime( $to ) );
												$this->productsList[ $i ]['sale_price_effective_date'] = "$from" . '/' . "$to";
											} else {
												$this->productsList[ $i ]['sale_price_effective_date'] = '';
											}
										}
									}
								}
							}
						}
					}
					$i ++;
				
				endwhile;
				wp_reset_postdata();
				if ( $this->WPFP_WPML ) {
					if ( isset( $feedRule['feedLanguage'] ) && $feedRule['feedLanguage'] != $this->currentLang ) {
						do_action( 'wpml_switch_language', $this->currentLang );
					}
				}
				return $this->productsList;
			} catch ( Exception $e ) {
				if ( $this->WPFP_WPML ) {
					if ( isset( $feedRule['feedLanguage'] ) && $feedRule['feedLanguage'] != $this->currentLang ) {
						do_action( 'wpml_switch_language', $this->currentLang );
					}
				}
			}       
		}
		return [];
	}
	
	/** Return product price with tax
	 *
	 * @param $product
	 *
	 * @return float|string
	 */
	public function get_price_with_tax( $product ) {
		if ( woo_feed_wc_version_check( 3.0 ) ) {
			return wc_get_price_including_tax( $product, array( 'price' => $product->get_price() ) );
		} else {
			return $product->get_price_including_tax();
		}
	}
	
	/**
	 * @param object $product
	 *
	 * @return string
	 */
	public function get_formatted_title( $product ) {
		$name       = $product->get_name();
		$attributes = $product->get_attributes();
		$val        = array();
		if ( ! empty( $attributes ) ) {
			foreach ( $attributes as $key => $attribute ) {
				$attr = $product->get_attribute( $key );
				if ( ! empty( $attr ) ) {
					$val[ $key ] = $product->get_attribute( $key );
				}
			}
		}
		
		if ( ! empty( $val ) ) {
			return $name . ' - ' . implode( ', ', $val );
		} else {
			return $name;
		}
	}
	
	/**
	 * Get formatted image url
	 *
	 * @param $url
	 *
	 * @return bool|string
	 */
	public function get_formatted_url( $url = '' ) {
		if ( ! empty( $url ) ) {
			if ( substr( trim( $url ), 0, 4 ) === 'http' || substr( trim( $url ),
					0,
					3 ) === 'ftp' || substr( trim( $url ), 0, 4 ) === 'sftp' ) {
				return rtrim( $url, '/' );
			} else {
				$base = get_site_url();
				$url  = $base . $url;
				
				return rtrim( $url, '/' );
			}
		}
		
		return '';
	}
	
	/**
	 * Get formatted product date
	 *
	 * @param $id
	 * @param $name
	 *
	 * @return bool|string
	 */
	public function get_date( $id, $name ) {
		$date = $this->getAttributeValue( $id, $name );
		if ( $date ) {
			return gmdate( 'Y-m-d', $date );
		}
		
		return false;
	}
	
	/**
	 * Get formatted product quantity
	 *
	 * @param $id
	 * @param $name
	 * @param $product
	 *
	 * @return bool|mixed
	 */
	public function get_quantity( $id, $name, $product ) {
		if ( $product->has_child() ) {
			$childs = $product->get_children();
			$qty    = array();
			foreach ( $childs as $key => $child ) {
				$childQty = $this->getProductMeta( $child, $name );
				$qty[]    = (int) $childQty + 0;
			}
			
			if ( isset( $this->feedRule['variable_quantity'] ) ) {
				$vaQty = $this->feedRule['variable_quantity'];
				if ( 'max' == $vaQty ) {
					return max( $qty );
				} elseif ( 'min' == $vaQty ) {
					return min( $qty );
				} elseif ( 'sum' == $vaQty ) {
					return array_sum( $qty );
				} elseif ( 'first' == $vaQty ) {
					return ( (int) $qty[0] );
				} else {
					return array_sum( $qty );
				}
			} else {
				return array_sum( $qty );
			}
		}
		
		return '';
	}
	
	/**
	 * Retrieve a post's terms as a list with specified format.
	 *
	 * @param int    $id Post ID.
	 * @param string $taxonomy Taxonomy name.
	 * @param string $before Optional. Before list.
	 * @param string $sep Optional. Separate items using this.
	 * @param string $after Optional. After list.
	 *
	 * @return string|false|WP_Error A list of terms on success, false if there are no terms, WP_Error on failure.
	 * @since 2.5.0
	 *
	 */
	function get_product_term_list( $id, $taxonomy, $before = '', $sep = ',', $after = '' ) {
		$terms = get_the_terms( $id, $taxonomy );
		
		if ( is_wp_error( $terms ) ) {
			return $terms;
		}
		
		if ( empty( $terms ) ) {
			return false;
		}
		
		$links = array();
		
		foreach ( $terms as $term ) {
			$links[ $term->term_id ] = $term->name;
		}
		ksort( $links );
		
		return $before . join( $sep, $links ) . $after;
	}
	
	/** Return additional image URLs
	 *
	 * @param integer $productId
	 *
	 * @return array
	 */
	public function additionalImages( $productId ) {
		$ids    = $this->getAttributeValue( $productId, '_product_image_gallery' );
		$imgIds = ! empty( $ids ) ? explode( ',', $ids ) : '';
		
		$images = array();
		if ( ! empty( $imgIds ) ) {
			foreach ( $imgIds as $key => $value ) {
				if ( $key < 10 ) {
					$images[ $key ] = wp_get_attachment_url( $value );
				}
			}
			
			return $images;
		}
		
		return [];
	}
	
	/**
	 * Give space to availability text
	 *
	 * @param integer $id
	 *
	 * @return string
	 */
	public function availability( $id ) {
		$status = $this->getAttributeValue( $id, '_stock_status' );
		if ( $status ) {
			if ( 'instock' == $status ) {
				return 'in stock';
			} elseif ( 'outofstock' == $status ) {
				return 'out of stock';
			}
		}
		
		return 'out of stock';
	}
	
	/**
	 * Ger Product Attribute
	 *
	 * @param $id
	 * @param $attr
	 *
	 * @return string
	 * @since 2.2.3
	 */
	public function getProductAttribute( $id, $attr ) {
		
		$attr = str_replace( self::PRODUCT_ATTRIBUTE_PREFIX, '', $attr );
		
		if ( woo_feed_wc_version_check( 3.2 ) ) {
			// Get Product
			$product = wc_get_product( $id );
			
			if ( ! is_object( $product ) ) {
				return '';
			}
			
			if ( woo_feed_wc_version_check( 3.6 ) ) {
				$attr = str_replace( 'pa_', '', $attr );
			}
			
			$value = $product->get_attribute( $attr );
			
			return $value;
		} else {
			return implode( ',', wc_get_product_terms( $id, $attr, array( 'fields' => 'names' ) ) );
		}
	}
	
	/**
	 * Get Meta
	 *
	 * @param int    $id Product Id
	 * @param string $meta post meta key
	 *
	 * @return mixed|string
	 * @since 2.2.3
	 */
	public function getProductMeta( $id, $meta ) {
		$product = false;
		$meta    = str_replace( self::POST_META_PREFIX, '', $meta );
		
		if ( false !== strpos( $meta, 'attribute_pa' ) ) {
			$productMeta = $this->getProductAttribute( $id, str_replace( 'attribute_', '', $meta ) );
		} else {
			$productMeta = get_post_meta( $id, $meta, true );
		}
		
		if ( empty( $productMeta ) && class_exists( 'SitePress' ) && false !== strpos( $meta, '_price_' ) ) {
			
			global $sitepress;
			$lang = $sitepress->get_default_language();
			
			if ( woo_feed_wc_version_check( 3.2 ) ) {
				$parentId = apply_filters( 'wpml_object_id', $id, 'product', false, $lang );
			} else {
				$parentId = icl_object_id( $id );
			}
			$productMeta = get_post_meta( $parentId, $meta, true );
		} elseif ( '' == $productMeta ) {
			$postParent = wp_get_post_parent_id( $id );
			if ( $postParent > 0 ) {
				$productMeta = get_post_meta( $postParent, $meta, true );
			}
		}
// if( $meta == '_aioseop_title' && empty( $productMeta ) ) {
// if( ! $product ) $product = wc_get_product( $id );
// $productMeta = $product->get_title();
// }
// if( $meta == '_aioseop_description' && empty( $productMeta ) ) {
// if( ! $product ) $product = wc_get_product( $id );
// $productMeta = $product->get_description();
// }
		return $productMeta;
	}
	
	/**
	 * Get Taxonomy
	 *
	 * @param $id
	 * @param $taxonomy
	 *
	 * @return string
	 */
	public function getProductTaxonomy( $id, $taxonomy ) {
		$taxonomy = str_replace( self::PRODUCT_TAXONOMY_PREFIX, '', $taxonomy );
		$Taxo     = get_the_term_list( $id, $taxonomy, '', ',', '' );
		
		if ( ! empty( $Taxo ) ) {
			return wp_strip_all_tags( $Taxo );
		}
		
		return '';
	}
	
	/**
	 * Get Product Attribute Value or Post Meta value
	 *
	 * @param $id
	 * @param $name
	 *
	 * @return mixed
	 * @deprecated 2.2.3
	 */
	public function getAttributeValue( $id, $name ) {
		return $this->getProductMeta( $id, $name );
	}
	
	/**
	 * Get Sale price effective date for google
	 *
	 * @param $id
	 * @param $name
	 *
	 * @return string
	 */
	public function sale_price_effective_date( $id, $name ) {
		$date = $this->getAttributeValue( $id, $name );
		return ( $date ) ? date_i18n( 'Y-m-d', $date ) : '';
	}
	
	/**
	 * Format product created & updated date
	 *
	 * @param $date
	 *
	 * @return false|string
	 */
	public function format_product_date( $date ) {
		return gmdate( 'Y-m-d', strtotime( $date ) );
	}
	
	/**
	 * Get Author info
	 *
	 * @param int    $id
	 * @param string $info only name or email can be retrieve
	 *
	 * @return bool|string
	 */
	public function get_author_info( $id, $info ) {
		$post     = get_post( $id );
		$authorId = $post->post_author;
		
		if ( 'name' == $info ) {
			return get_the_author_meta( 'user_login', $authorId );
		} elseif ( 'email' == $info ) {
			return get_the_author_meta( 'user_email', $authorId );
		}
		
		return false;
	}
}
