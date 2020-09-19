<?php /** @noinspection PhpUnusedPrivateMethodInspection, PhpUnused, PhpUnusedLocalVariableInspection, DuplicatedCode */
/**
 * Product V3 Pro
 *
 * @author    Kudratullah <mhamudul.hk@gmail.com>
 * @copyright 2020 WebAppick
 * @package   WooFeed/Pro
 * @version   1.0.1
 * @since     WooFeed 3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Woo_Feed_Products_v3_Pro
 */
class Woo_Feed_Products_v3_Pro extends Woo_Feed_Products_v3 {
	/**
	 * Product types Supported for query
	 * @var array
	 */
    protected $product_types = array(
        'simple',
        'variable',
        'variation',
        'grouped',
        'external',
        'composite', // WooCommerce Composite Products.
        'bundle', // WooCommerce Bundle Products.
        'yith_bundle', // WooCommerce YITH Bundle Products.
        'yith-composite', // WooCommerce YITH Composite Products.
        'subscription',
        'variable-subscription',
    );
	/**
	 * Categories to include or exclude [Filter]
	 * @var array
	 */
	protected $categories = array();
	/**
	 * Vendors to include [Filter]
	 * @var array
	 */
	protected $vendors = array();
	/**
	 * Product Ids to include [Filter]
	 * @var array
	 */
	protected $ids_in = array();
	/**
	 * Product Ids to exclude [Filter]
	 * @var array
	 */
	protected $ids_not_in = array();
	
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
	
	/**
	 * Extra Attribute Prefix
	 * @since 3.2.20
	 */
	const PRODUCT_EXTRA_ATTRIBUTE_PREFIX = 'wf_extra_';
	
	/**
	 * Product Attribute Mappings Prefix
	 * @since 3.3.2*
	 */
	const PRODUCT_ATTRIBUTE_MAPPING_PREFIX = 'wp_attr_mapping_';
	
	/**
	 * Woo_Feed_Products_v3_Pro constructor.
	 *
	 * @param $config
	 * @return void
	 */
	public function __construct( $config ) {
		$this->config    = woo_feed_parse_feed_rules( $config );
		$this->queryType = woo_feed_get_options( 'product_query_type' );
		$this->process_xml_wrapper();
		
		$this->inExProducts();
		// TODO move this sanitization[s] to config save function.
		if ( ! empty( $this->config['categories'] ) ) {
			$this->categories = array_map( 'sanitize_text_field', (array) $this->config['categories'] );
		}
		if ( ! empty( $this->config['vendors'] ) ) {
			$this->vendors = array_map( 'sanitize_text_field', (array) $this->config['vendors'] );
		}
		
		if ( ! empty( $this->config['post_status'] ) ) {
			$this->config['post_status'] = array_map( 'sanitize_text_field', (array) $this->config['post_status'] );
			$statuses                    = array_keys( woo_feed_get_post_statuses() );
			// check if status is allowed.
			if ( count( array_diff( $this->config['post_status'], $statuses ) ) == 0 ) {
				if ( 'include' == $this->config['filter_mode']['post_status'] ) {
					$this->post_status = $this->config['post_status'];
				} else {
					$this->post_status = array_diff( $statuses, $this->config['post_status'] );
				}
			}
		}
		
		woo_feed_log_feed_process( $this->config['filename'], sprintf( 'Current Query Type is %s', $this->queryType ) );
	}
	
	/**
	 *  Get Ids to Exclude or Include from product list
	 *
	 * @since 2.2.0
	 * @for WC 3.1+
	 */
	public function inExProducts() {
		// Argument for Product search by ID
		if ( isset( $this->config['fattribute'] ) && is_array( $this->config['fattribute'] ) ) {
			if ( count( $this->config['fattribute'] ) ) {
				$condition = $this->config['condition'];
				$compare   = $this->config['filterCompare'];
				foreach ( $this->config['fattribute'] as $key => $rule ) {
					if ( 'id' == $rule && in_array( $condition[ $key ], array( '==', 'contain' ) ) ) {
						unset( $this->config['fattribute'][ $key ] );
						unset( $this->config['condition'][ $key ] );
						unset( $this->config['filterCompare'][ $key ] );
						if ( false !== strpos( $compare[ $key ], ',' ) ) {
							foreach ( explode( ',', $compare[ $key ] ) as $k => $id ) {
								array_push( $this->ids_in, $id );
							}
						} else {
							array_push( $this->ids_in, $compare[ $key ] );
						}
					} elseif ( 'id' == $rule && in_array( $condition[ $key ], array( '!=', 'nContains' ) ) ) {
						unset( $this->config['fattribute'][ $key ] );
						unset( $this->config['condition'][ $key ] );
						unset( $this->config['filterCompare'][ $key ] );
						if ( false !== strpos( $compare[ $key ], ',' ) ) {
							foreach ( explode( ',', $compare[ $key ] ) as $k => $id ) {
								array_push( $this->ids_not_in, $id );
							}
						} else {
							array_push( $this->ids_not_in, $compare[ $key ] );
						}
					}
				}
			}
		}
		if ( ! empty( trim( $this->config['product_ids'] ) ) ) {
			$this->config['product_ids'] = explode( ',', $this->config['product_ids'] );
			$this->config['product_ids'] = array_map( 'absint', $this->config['product_ids'] );
			$this->config['product_ids'] = array_filter( $this->config['product_ids'] );
			if ( ! empty( $this->config['product_ids'] ) ) {
				if ( 'include' == $this->config['filter_mode']['product_ids'] ) {
					$this->ids_in = array_merge( $this->ids_in, $this->config['product_ids'] );
				} else {
					$this->ids_not_in = array_merge( $this->ids_not_in, $this->config['product_ids'] );
				}
			}
		}
		if ( ! empty( $this->ids_in ) ) {
			$this->ids_in = array_unique( $this->ids_in );
		}
		if ( ! empty( $this->ids_not_in ) ) {
			$this->ids_not_in = array_unique( $this->ids_not_in );
		}
	}
	
	/**
	 * Generate Query Args For WP/WC query class
	 * @param string $type
	 * @return array
	 */
	protected function get_query_args( $type = 'wc' ) {
		$args = [];

		if ( 'wc' === $type ) {
			$args = array(
				'limit'            => -1,
				'offset'           => 0,
				'status'           => $this->post_status,
				'type'             => [ 'simple', 'variable', 'grouped', 'external', 'composite', 'bundle', 'yith_bundle', 'yith-composite','subscription', 'variable-subscription' ],
				'exclude'          => $this->ids_not_in,
				'include'          => $this->ids_in,
				'author'           => implode( ',', $this->vendors ),
				'orderby'          => 'date',
				'order'            => 'DESC',
				'return'           => 'ids',
				'suppress_filters' => false,
			);
			
			// Remove Out Of Stock Products

			if ( isset( $this->config['is_outOfStock'] ) && 'y' == $this->config['is_outOfStock'] ) {
				$args['stock_status'] = 'instock';
			}
			
			// Get Products for Specific Categories
			if ( is_array( $this->categories ) && ! empty( $this->categories ) ) {
				if ( 'include' == $this->config['filter_mode']['categories'] ) {
					$args['category'] = $this->categories;
				} else {
					$args['tax_query'][] = [
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						'terms'    => $this->categories,
						'operator' => 'NOT IN',
					];
				}
			}
		}
		if ( 'wp' === $type ) {
			$args = array(
				'posts_per_page'         => - 1,
				'post_type'              => 'product',
				'post_status'            => $this->post_status,
				'post__in'               => $this->ids_in,
				'post__not_in'           => $this->ids_not_in,
				'author__in'             => implode( ',', $this->vendors ),
				'order'                  => 'DESC',
				'fields'                 => 'ids',
				'cache_results'          => false,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'suppress_filters'       => false,
			);
			
			// Remove Out Of Stock Products
			if ( isset( $this->config['is_outOfStock'] ) && 'y' == $this->config['is_outOfStock'] ) {
				$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => '_stock_status',
						'value'   => 'outofstock',
						'compare' => '!=',
					),
				);
			}
			
			// Get Products for Specific Categories
			if ( is_array( $this->categories ) && ! empty( $this->categories ) ) {
				$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						// This is optional, as it defaults to 'term_id'
						'terms'    => $this->categories,
						'operator' => 'include' == $this->config['filter_mode']['categories'] ? 'IN' : 'NOT IN',
						// Possible values are 'IN', 'NOT IN', 'AND'.
					),
				);
			}
		}
		return $args;
	}
	
	/**
	 * Process product variations
	 * @param WC_Abstract_Legacy_Product $product
	 *
	 * @return bool
	 * @since 3.3.9
	 */
	protected function process_variation( $product ) {
		// Apply variable and variation settings
		if ( $product->is_type( 'variable' ) && $product->has_child() ) {
			// Include Product Variations if configured
			if ( 'y' == $this->config['is_variations'] || 'both' == $this->config['is_variations'] ) {
				$this->pi ++;
				$variations = $product->get_visible_children();
				if ( is_array( $variations ) && ( sizeof( $variations ) > 0 ) ) {
					if ( woo_feed_is_debugging_enabled() ) {
						woo_feed_log_feed_process( $this->config['filename'], sprintf( 'Getting Variation Product(s) :: %s', implode( ', ', $variations ) ) );
					}
					$this->get_products( $variations );
				}
			}
			
			// Skip variable products if only variations are configured
			if ( 'y' == $this->config['is_variations'] ) {
				woo_feed_log_feed_process( $this->config['filename'], 'Skipping Product :: Include only variations' );
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Process The Attributes and assign value to merchant attribute
	 *
	 * @param WC_Abstract_Legacy_Product $product
	 *
	 * @return void
	 * @since 3.3.9
	 */
	protected function process_attributes( $product ) {
		// Get Product Attribute values by type and assign to product array
		foreach ( $this->config['attributes'] as $attr_key => $attribute ) {
			
			$merchant_attribute = $this->config['mattributes'][ $attr_key ];
			$feedType = $this->config['feedType'];
			
			if ( $this->exclude_current_attribute( $product, $merchant_attribute, $attribute, $feedType ) ) {
				continue;
			}
			
			// Add Prefix and Suffix into Output
			$prefix   = $this->config['prefix'][ $attr_key ];
			$suffix   = $this->config['suffix'][ $attr_key ];
			$merchant = $this->config['provider'];
			
			if ( 'pattern' == $this->config['type'][ $attr_key ] ) {
				$attributeValue = $this->config['default'][ $attr_key ];
			} else { // Get Pattern value
				$attributeValue = $this->getAttributeValueByType( $product, $attribute );
			}
			
			// Format Output according to Output Type config.
			$outputType = $this->config['output_type'][ $attr_key ];
			if ( 'default' != $outputType ) {
				$attributeValue = $this->format_output( $attributeValue, $outputType, $product, $attribute );
			}
			
			// Format Output according to command
			$commands = $this->config['limit'][ $attr_key ];
			if ( false !== strpos( $commands, '[' ) ) {
				$attributeValue = $this->process_commands( $attributeValue, $commands, $product, $attribute );
			}
			
			// Add Prefix and Suffix into Output
			$prefix = $this->config['prefix'][ $attr_key ];
			$suffix = $this->config['suffix'][ $attr_key ];
			
			$attributeValue = $this->process_prefix_suffix( $attributeValue, $prefix, $suffix, $attribute );
			$attributeValue = $this->str_replace( $attributeValue, $attribute );
			// Replace XML Nodes according to merchant requirement
			$getReplacedAttribute = woo_feed_replace_to_merchant_attribute( $merchant_attribute, $merchant, $feedType );
			
			if ( 'xml' == $feedType ) {
				
				// XML does not support space in element. So replace Space with Underscore
				$getReplacedAttribute = str_replace( ' ', '_', $getReplacedAttribute );
				
				// Trim XML Element text
				if ( ! empty( $attributeValue ) ) {
					$attributeValue = trim( $attributeValue );
				}
				
				// Add closing XML node if value is empty
				if ( '' != $attributeValue ) {
					// Add CDATA wrapper for XML feed to prevent XML error.
					$attributeValue = woo_feed_add_cdata( $merchant_attribute, $attributeValue, $merchant );
					
					// Replace Google Color attribute value according to requirements
					if ( 'g:color' == $getReplacedAttribute ) {
						$attributeValue = str_replace( ', ', '/', $attributeValue );
					}
					
					// Strip slash from output
					$attributeValue = stripslashes( $attributeValue );
					
					$this->feedBody .= '<' . $getReplacedAttribute . '>' . "$attributeValue" . '</' . $getReplacedAttribute . '>';
					$this->feedBody .= "\n";
					
				} else {
					$this->feedBody .= '<' . $getReplacedAttribute . '/>';
					$this->feedBody .= "\n";
				}
			} elseif ( 'csv' == $feedType ) {
				$merchant_attribute = $this->processStringForCSV( $getReplacedAttribute );
				$attributeValue  = $this->processStringForCSV( $attributeValue );
			} elseif ( 'txt' == $feedType ) {
				$merchant_attribute = $this->processStringForTXT( $getReplacedAttribute );
				$attributeValue  = $this->processStringForTXT( $attributeValue );
			}
			
			$mAttributes[ $attr_key ]                        = $this->config['mattributes'][ $attr_key ];
			$this->products[ $this->pi ][ $merchant_attribute ] = $attributeValue;
		}
	}
	
	/**
	 * Check if current product should be processed for feed
	 * This should be using by Woo_Feed_Products_v3::get_products()
	 *
	 * @param WC_Product $product
	 *
	 * @return bool
	 * @since 3.3.9
	 *
	 */
	protected function exclude_from_loop( $product ) {
		// For WP_Query check available product types
		if ( 'wp' == $this->queryType ) {
			if ( ! in_array( $product->get_type(), $this->product_types ) ) {
				woo_feed_log_feed_process( $this->config['filename'], sprintf( 'Skipping Product :: Invalid Post/Product Type : %s.', $product->get_type() ) );
				return true;
			}
		}
		
		// Skip for invalid products
		if ( ! is_object( $product ) ) {
			woo_feed_log_feed_process( $this->config['filename'], 'Skipping Product :: Product data is not a valid WC_Product object.' );
			return true;
		}
		
		// Skip for invisible products if $this->config['product_visibility'] set to 0
		if ( 0 == $this->config['product_visibility'] && ! $product->is_visible() ) {
			woo_feed_log_feed_process( $this->config['filename'], 'Skipping Product :: Product is not visible.' );
			return true;
		}
		
		// Execute Product id and stock filter for product variations
		if ( $product->is_type( 'variation' ) ) {
			if ( ! empty( $this->ids_not_in ) && in_array( $product->get_id(), $this->ids_not_in ) ) {
				woo_feed_log_feed_process( $this->config['filename'], 'Skipping Product :: Product found in ids_not_in array' );
				return true;
			}
			
			if ( ! empty( $this->ids_in ) && ! in_array( $product->get_id(), $this->ids_in ) ) {
				woo_feed_log_feed_process( $this->config['filename'], 'Skipping Product :: Product found in ids_in array' );
				return true;
			}
			
			if ( isset( $this->config['is_outOfStock'] ) && 'y' == $this->config['is_outOfStock'] ) {
				if ( $product->get_stock_status() == 'outofstock' ) {
					woo_feed_log_feed_process( $this->config['filename'], 'Skipping Product :: Product is out of stock' );
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Get Product Attribute Value by Type
	 *
	 * @param WC_Product|WC_Product_Variable|WC_Product_Variation|WC_Product_Grouped|WC_Product_External|WC_Product_Composite $product Product Object.
	 * @param string                                                                                                          $attribute
	 *
	 * @return mixed|string
	 *
	 * @since 3.2.0
	 *
	 */
	public function getAttributeValueByType( $product, $attribute ) {
		
		if ( method_exists( $this, $attribute ) ) {
			$output = call_user_func_array( array( $this, $attribute ), array( $product ) );
		} elseif ( false !== strpos( $attribute, self::PRODUCT_EXTRA_ATTRIBUTE_PREFIX ) ) {
			$attribute = str_replace( self::PRODUCT_EXTRA_ATTRIBUTE_PREFIX, '', $attribute );
			/**
			 * Filter output for extra attribute, which can be added via 3rd party plugins.
			 *
			 * @param string $output the output
			 * @param WC_Product|WC_Product_Variable|WC_Product_Variation|WC_Product_Grouped|WC_Product_External|WC_Product_Composite $product Product Object.
			 * @param array feed config/rule
			 *
			 * @since 3.3.5
			 *
			 */
			return apply_filters( "woo_feed_get_extra_{$attribute}_attribute", '', $product, $this->config );
		} elseif ( false !== strpos( $attribute, self::PRODUCT_ATTRIBUTE_MAPPING_PREFIX ) ) {
			return $this->get_mapped_attribute_value( $product, $attribute );
		} elseif ( false !== strpos( $attribute, self::PRODUCT_ATTRIBUTE_PREFIX ) ) {
			$attribute = str_replace( self::PRODUCT_ATTRIBUTE_PREFIX, '', $attribute );
			$output    = $this->getProductAttribute( $product, $attribute );
		} elseif ( false !== strpos( $attribute, self::POST_META_PREFIX ) ) {
			$attribute = str_replace( self::POST_META_PREFIX, '', $attribute );
			$output    = $this->getProductMeta( $product, $attribute );
		} elseif ( false !== strpos( $attribute, self::PRODUCT_TAXONOMY_PREFIX ) ) {
			$attribute = str_replace( self::PRODUCT_TAXONOMY_PREFIX, '', $attribute );
			$output    = $this->getProductTaxonomy( $product, $attribute );
		} elseif ( false !== strpos( $attribute, self::PRODUCT_DYNAMIC_ATTRIBUTE_PREFIX ) ) {
			// self::get_dynamic_attribute_value() calls this method, no need to filter the output.
			return $this->get_dynamic_attribute_value( $product, $attribute );
		} elseif ( false !== strpos( $attribute, self::PRODUCT_CATEGORY_MAPPING_PREFIX ) ) {
			$id     = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
			$output = woo_feed_get_category_mapping_value( $attribute, $id );
		} elseif ( false !== strpos( $attribute, self::WP_OPTION_PREFIX ) ) {
			$optionName = str_replace( self::WP_OPTION_PREFIX, '', $attribute );
			$output     = get_option( $optionName );
		} elseif ( 'image_' == substr( $attribute, 0, 6 ) ) {
			// For additional image method images() will be used with extra parameter - image number
			$imageKey = explode( '_', $attribute );
			if ( ! isset( $imageKey[1] ) || ( isset( $imageKey[1] ) && ( empty( $imageKey[1] ) || ! is_numeric( $imageKey[1] ) ) ) ) {
				$imageKey[1] = '';
			}
			$output = call_user_func_array( array( $this, 'images' ), array( $product, $imageKey[1] ) );
		} else {
			// return the attribute so multiple attribute can be join with separator to make custom attribute.
			$output = $attribute;
		}
		
		// Json encode if value is an array
		if ( is_array( $output ) ) {
			$output = wp_json_encode( $output );
		}
		
		/**
		 * Filter attribute value before return based on merchant and attribute name
		 *
		 * @param string $output the output
		 * @param WC_Product|WC_Product_Variable|WC_Product_Variation|WC_Product_Grouped|WC_Product_External|WC_Product_Composite $product Product Object.
		 * @param array feed config/rule
		 *
		 * @since 3.3.7
		 *
		 */
		$output = apply_filters( "woo_feed_get_{$this->config['provider']}_{$attribute}_attribute", $output, $product, $this->config );
		
		/**
		 * Filter attribute value before return based on attribute name
		 *
		 * @param string $output the output
		 * @param WC_Product|WC_Product_Variable|WC_Product_Variation|WC_Product_Grouped|WC_Product_External|WC_Product_Composite $product Product Object.
		 * @param array feed config/rule
		 *
		 * @since 3.3.5
		 *
		 */
		return apply_filters( "woo_feed_get_{$attribute}_attribute", $output, $product, $this->config );
	}
	
	/**
	 * Get the value of a dynamic attribute
	 *
	 * @param WC_Product    $product
	 * @param $attributeName
	 *
	 * @return mixed|string
	 * @since 3.2.0
	 *
	 */
	private function get_dynamic_attribute_value( $product, $attributeName ) {
		$getValue         = maybe_unserialize( get_option( $attributeName ) );
		$wfDAttributeCode = isset( $getValue['wfDAttributeCode'] ) ? $getValue['wfDAttributeCode'] : '';
		$attribute        = isset( $getValue['attribute'] ) ? (array) $getValue['attribute'] : array();
		$condition        = isset( $getValue['condition'] ) ? (array) $getValue['condition'] : array();
		$compare          = isset( $getValue['compare'] ) ? (array) $getValue['compare'] : array();
		$type             = isset( $getValue['type'] ) ? (array) $getValue['type'] : array();
		
		$prefix = isset( $getValue['prefix'] ) ? (array) $getValue['prefix'] : array();
		$suffix = isset( $getValue['suffix'] ) ? (array) $getValue['suffix'] : array();
		
		$value_attribute = isset( $getValue['value_attribute'] ) ? (array) $getValue['value_attribute'] : array();
		$value_pattern   = isset( $getValue['value_pattern'] ) ? (array) $getValue['value_pattern'] : array();
		
		$default_type            = isset( $getValue['default_type'] ) ? $getValue['default_type'] : 'attribute';
		$default_value_attribute = isset( $getValue['default_value_attribute'] ) ? $getValue['default_value_attribute'] : '';
		$default_value_pattern   = isset( $getValue['default_value_pattern'] ) ? $getValue['default_value_pattern'] : '';
		
		$result = '';
		
		// Check If Attribute Code exist
		if ( $wfDAttributeCode ) {
			if ( count( $attribute ) ) {
				foreach ( $attribute as $key => $name ) {
					if ( ! empty( $name ) ) {
						
						$conditionName = $this->getAttributeValueByType( $product, $name );
						
						$conditionCompare  = $compare[ $key ];
						$conditionOperator = $condition[ $key ];
						
						if ( ! empty( $conditionCompare ) ) {
							$conditionCompare = trim( $conditionCompare );
						}
						$conditionValue = '';
						if ( 'pattern' == $type[ $key ] ) {
							$conditionValue = $value_pattern[ $key ];
						} elseif ( 'attribute' == $type[ $key ] ) {
							$conditionValue = $this->getAttributeValueByType( $product, $value_attribute[ $key ] );
						} elseif ( 'remove' == $type[ $key ] ) {
							$conditionValue = '';
						}
						
						switch ( $conditionOperator ) {
							case '==':
								if ( $conditionName == $conditionCompare ) {
									$result = $this->price_format( $name, $conditionName, $conditionValue );
									if ( '' != $result ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								break;
							case '!=':
								if ( $conditionName != $conditionCompare ) {
									$result = $this->price_format( $name, $conditionName, $conditionValue );
									if ( '' != $result ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								break;
							case '>=':
								if ( $conditionName >= $conditionCompare ) {
									$result = $this->price_format( $name, $conditionName, $conditionValue );
									if ( '' != $result ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								
								break;
							case '<=':
								if ( $conditionName <= $conditionCompare ) {
									$result = $this->price_format( $name, $conditionName, $conditionValue );
									if ( '' != $result ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								break;
							case '>':
								if ( $conditionName > $conditionCompare ) {
									$result = $this->price_format( $name, $conditionName, $conditionValue );
									if ( '' != $result ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								break;
							case '<':
								if ( $conditionName < $conditionCompare ) {
									$result = $this->price_format( $name, $conditionName, $conditionValue );
									if ( '' != $result ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								break;
							case 'contains':
								if ( false !== strpos( strtolower( $conditionName ),
										strtolower( $conditionCompare ) ) ) {
									$result = $this->price_format( $name, $conditionName, $conditionValue );
									if ( '' != $result ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								break;
							case 'nContains':
								if ( strpos( strtolower( $conditionName ),
										strtolower( $conditionCompare ) ) === false ) {
									$result = $this->price_format( $name, $conditionName, $conditionValue );
									if ( '' != $result ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								break;
							case 'between':
								$compare_items = explode( '-', $conditionCompare );
								
								if ( isset( $compare_items[1] ) && is_numeric( $compare_items[0] ) && is_numeric( $compare_items[1] ) ) {
									if ( $conditionName >= $compare_items[0] && $conditionName <= $compare_items[1] ) {
										$result = $this->price_format( $name, $conditionName, $conditionValue );
										if ( '' != $result ) {
											$result = $prefix[ $key ] . $result . $suffix[ $key ];
										}
									}
								} else {
									$result = '';
								}
								break;
							default:
								break;
						}
					}
				}
			}
		}
		
		
		if ( '' == $result ) {
			if ( 'pattern' == $default_type ) {
				$result = $default_value_pattern;
			} elseif ( 'attribute' == $default_type ) {
				if ( ! empty( $default_value_attribute ) ) {
					$result = $this->getAttributeValueByType( $product, $default_value_attribute );
				}
			} elseif ( 'remove' == $default_type ) {
				$result = '';
			}
		}
		
		return $result;
	}
	
	/**
	 * Format price value
	 *
	 * @param string $name Attribute Name
	 * @param int    $conditionName condition
	 * @param int    $result price
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function price_format( $name, $conditionName, $result ) {
		// calc and return the output.
		if ( false !== strpos( $name, 'price' ) ) {
			if ( false !== strpos( $result, '+' ) && false !== strpos( $result, '%' ) ) {
				$result = str_replace_trim( '+', '', $result );
				$result = str_replace_trim( '%', '', $result );
				if ( is_numeric( $result ) ) {
					$result = $conditionName + ( ( $conditionName * $result ) / 100 );
				}
			} elseif ( false !== strpos( $result, '-' ) && false !== strpos( $result, '%' ) ) {
				$result = str_replace_trim( '-', '', $result );
				$result = str_replace_trim( '%', '', $result );
				if ( is_numeric( $result ) ) {
					$result = $conditionName - ( ( $conditionName * $result ) / 100 );
				}
			} elseif ( false !== strpos( $result, '*' ) && false !== strpos( $result, '%' ) ) {
				$result = str_replace_trim( '*', '', $result );
				$result = str_replace_trim( '%', '', $result );
				if ( is_numeric( $result ) ) {
					$result = ( ( $conditionName * $result ) / 100 );
				}
			} elseif ( false !== strpos( $result, '+' ) ) {
				$result = str_replace_trim( '+', '', $result );
				if ( is_numeric( $result ) ) {
					$result = ( $conditionName + $result );
				}
			} elseif ( false !== strpos( $result, '-' ) ) {
				$result = str_replace_trim( '-', '', $result );
				if ( is_numeric( $result ) ) {
					$result = $conditionName - $result;
				}
			} elseif ( false !== strpos( $result, '*' ) ) {
				$result = str_replace_trim( '*', '', $result );
				if ( is_numeric( $result ) ) {
					$result = ( $conditionName * $result );
				}
			} elseif ( false !== strpos( $result, '/' ) ) {
				$result = str_replace_trim( '/', '', $result );
				if ( is_numeric( $result ) ) {
					$result = ( $conditionName / $result );
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * Get Product Title
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function title( $product ) {
		if ( isset( $this->config['ptitle_show'] ) && ! empty( $this->config['ptitle_show'] ) ) {
			return $this->get_extended_title( $product );
		}
		
		return $product->get_name();
	}
	
	/**
	 * Get formatted product title
	 *
	 * @param WC_Product $product
	 *
	 * @return bool|string
	 * @since 3.2.0
	 *
	 */
	protected function get_extended_title( $product ) {
		$attributes = explode( '|', $this->config['ptitle_show'] );
		$attributes = array_filter( $attributes );
		$title = $product->get_name();
		if ( ! empty( $attributes ) ) {
			$output = '';
			if ( $product->is_type( 'variation' ) ) {
				$parent = wc_get_product( $product->get_parent_id() );
				$title  = $parent->get_name();
			}
			foreach ( $attributes as $attribute ) {
				$attribute = trim( $attribute );
				if ( empty( $attribute ) ) continue;
				if ( 'title' == $attribute ) {
					$output .= ' ' . $title;
				} else {
				    $get_attr_value= $this->getAttributeValueByType( $product, $attribute );
				    if(''===$get_attr_value && $product->is_type( 'variation' )){
                        $get_attr_value= $this->getAttributeValueByType( $parent, $attribute );
                    }
					$output .= ' ' . $get_attr_value;
				}
			}
			$output = ! empty( $output ) ? trim( $output ) : '';
			
			return preg_replace( '!\s+!', ' ', $output );
		} else {
			return $title;
		}
	}
	
	/**
	 * @param WC_Product $product
	 * @param string     $attribute_map_option_name
	 *
	 * @return string
	 */
	protected function get_mapped_attribute_value( $product, $attribute_map_option_name ) {
		$attributes = get_option( $attribute_map_option_name );
		$output = [];
		if ( isset( $attributes['mapping'] ) ) {
			foreach ( $attributes['mapping'] as $map ) {
				// TODO add settings on Add/Edit Mapping page for exclude attribute if value is empty
				// TODO add settings with previous todo if user want to evaluate 0 as empty.
				$output[] = $this->getAttributeValueByType( $product, $map );
			}
		}
		// glue together
		$output = implode( $attributes['glue'], $output );
		// remove extra whitespace
		$output = preg_replace( '!\s\s+!', ' ', $output );
		return $output;
	}
	
	/**
	 * Get Product Regular Price
	 *
	 * @param WC_Product|WC_Product_Variable|WC_Product_Grouped|WC_Product_Composite $product Product Object.
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function price( $product ) {
		if ( $product->is_type( 'variable' ) ) {
			return $this->getVariableProductPrice( $product, 'regular_price' );
		} elseif ( $product->is_type( 'grouped' ) ) {
			return $this->getGroupProductPrice( $product,
				'regular' ); // this calls self::price() so no need to use self::getWPMLPrice()
		} elseif ( $product->is_type( 'composite' ) || $product->is_type( 'yith-composite' ) ) {
			return $this->getCompositeProductPrice( $product,
				'regular_price' ); // this calls self::price() so no need to use self::getWPMLPrice()
		} else {
			$price = $product->get_regular_price();
			
			// Get WooCommerce Multi language Price by Currency.
			return $this->getWPMLPrice( $product, $price, '_regular_price' );
		}
	}
	
	/**
	 * Get Product Price
	 *
	 * @param WC_Product|WC_Product_Variable|WC_Product_Grouped|WC_Product_Composite $product
	 *
	 * @return int|float|double|mixed
	 * @since 3.2.0
	 *
	 */
	protected function current_price( $product ) {
		if ( $product->is_type( 'variable' ) ) {
			return $this->getVariableProductPrice( $product, 'price' );
		} elseif ( $product->is_type( 'grouped' ) ) {
			return $this->getGroupProductPrice( $product, 'current' );
		} elseif ( $product->is_type( 'composite' ) || $product->is_type( 'yith-composite' ) ) {
			return $this->getCompositeProductPrice( $product, 'current_price' );
		} else {
			$price = $product->get_price();
			
			// Get WooCommerce Multi language Price by Currency
			return $this->getWPMLPrice( $product, $price, '_price' );
		}
	}
	
	/**
	 * Get Product Sale Price
	 *
	 * @param WC_Product|WC_Product_Variable|WC_Product_Grouped|WC_Product_Composite $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function sale_price( $product ) {
		if ( $product->is_type( 'variable' ) ) {
			return $this->getVariableProductPrice( $product, 'sale_price' );
		} elseif ( $product->is_type( 'grouped' ) ) {
			return $this->getGroupProductPrice( $product, 'sale' );
		} elseif ( $product->is_type( 'composite' ) || $product->is_type( 'yith-composite' ) ) {
			return $this->getCompositeProductPrice( $product, 'sale_price' );
		} else {
			$price = $product->get_sale_price();
			// Get WooCommerce Multi language Price by Currency
			$price = $this->getWPMLPrice( $product, $price, '_sale_price' );
			
			return $price > 0 ? $price : '';
		}
	}
	
	/**
	 * Get Product Regular Price with Tax
	 *
	 * @param WC_Product|WC_Product_Variable|WC_Product_Grouped|WC_Product_Composite $product Product Object.
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function price_with_tax( $product ) {
		if ( $product->is_type( 'variable' ) ) {
			return $this->getVariableProductPrice( $product, 'regular_price', true );
		} elseif ( $product->is_type( 'grouped' ) ) {
			return $this->getGroupProductPrice( $product, 'regular', true );
		} elseif ( $product->is_type( 'composite' ) || $product->is_type( 'yith-composite' ) ) {
			return $this->getCompositeProductPrice( $product, 'regular_price', true );
		} else {
			$price = $this->price( $product );
			
			// Get price with tax.
			return ( $product->is_taxable() && ! empty( $price ) ) ? $this->get_price_with_tax( $product,
				$price ) : $price;
		}
	}
	
	/**
	 * Get Product Regular Price with Tax
	 *
	 * @param WC_Product|WC_Product_Variable|WC_Product_Grouped|WC_Product_Composite $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function current_price_with_tax( $product ) {
		if ( $product->is_type( 'variable' ) ) {
			return $this->getVariableProductPrice( $product, 'current_price', true );
		} elseif ( $product->is_type( 'grouped' ) ) {
			return $this->getGroupProductPrice( $product, 'current', true );
		} elseif ( $product->is_type( 'composite' ) || $product->is_type( 'yith-composite' ) ) {
			return $this->getCompositeProductPrice( $product, 'current_price', true );
		} else {
			$price = $this->current_price( $product );
			
			// Get price with tax
			return ( $product->is_taxable() && ! empty( $price ) ) ? $this->get_price_with_tax( $product,
				$price ) : $price;
		}
	}
	
	/**
	 * Get Product Regular Price with Tax
	 *
	 * @param WC_Product|WC_Product_Variable|WC_Product_Grouped|WC_Product_Composite $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function sale_price_with_tax( $product ) {
		if ( $product->is_type( 'variable' ) ) {
			return $this->getVariableProductPrice( $product, 'sale_price', true );
		} elseif ( $product->is_type( 'grouped' ) ) {
			return $this->getGroupProductPrice( $product, 'sale', true );
		} elseif ( $product->is_type( 'composite' ) || $product->is_type( 'yith-composite' ) ) {
			return $this->getCompositeProductPrice( $product, 'sale_price', true );
		} else {
			$price = $this->sale_price( $product );
			if ( $product->is_taxable() && ! empty( $price ) ) {
				$price = $this->get_price_with_tax( $product, $price );
			}
			
			return $price > 0 ? $price : '';
		}
	}
	
	/**
	 * Get total price of variable product
	 *
	 * @param WC_Product_Variable $variable
	 * @param string              $type regular_price, sale_price & current_price
	 * @param bool                $tax calculate tax
	 *
	 * @return int|string
	 * @since 3.2.0
	 *
	 */
	protected function getVariableProductPrice( $variable, $type, $tax = false ) {
		$price = 0;
		if ( 'first' == $this->config['variable_price'] ) {
			$children = $variable->get_visible_children();
			if ( isset( $children[0] ) && ! empty( $children[0] ) ) {
				$variation = wc_get_product( $children[0] );
				if ( 'regular_price' == $type ) {
					$price = $variation->get_regular_price();
				} elseif ( 'sale_price' == $type ) {
					$price = $variation->get_sale_price();
				} else {
					$price = $variation->get_price();
				}
				// Get WooCommerce Multi language Price by Currency.
				$price = $this->getWPMLPrice( $variation, $price, '_' . $type );
				// tax
				if ( true === $tax && $variable->is_taxable() ) {
					$price = $this->get_price_with_tax( $variable, $price );
				}
			}
		} else {
			if ( 'regular_price' == $type ) {
				$price = $variable->get_variation_regular_price( $this->config['variable_price'] );
			} elseif ( 'sale_price' == $type ) {
				$price = $variable->get_variation_sale_price( $this->config['variable_price'] );
			} else {
				$price = $variable->get_variation_price( $this->config['variable_price'] );
			}
			// Get WooCommerce Multi language Price by Currency.
			$price = $this->getWPMLPrice( $variable, $price, '_' . $type );
			// tax
			if ( true === $tax && $variable->is_taxable() ) {
				$price = $this->get_price_with_tax( $variable, $price );
			}
		}
		
		return $price;
	}
	
	/**
	 * Get Composite Product Price
	 *
	 * @param WC_Product_Composite $product product object.
	 * @param string               $type regular_price, sale_price & current_price.
	 * @param bool                 $tax
	 *
	 * @return int|float|double
	 * @since 3.2.6
	 *
	 */
	protected function getCompositeProductPrice( $product, $type, $tax = false ) {
		if ( isset( $this->config['composite_price'] ) && 'all_product_price' == $this->config['composite_price'] ) {
			$_total_regular_price = 0;
			$_total_price         = 0;
			$_total_sale_price    = 0;
			
			$ids = $product->get_component_ids();
			foreach ( $ids as $id ) {
				foreach ( $product->get_component_data( $id )['assigned_ids'] as $assigned_id ) {
					$_compo_prod = wc_get_product( $assigned_id );
					if ( $_compo_prod ) {
						$_regular_price = $this->getWPMLPrice( $_compo_prod,
							$_compo_prod->get_regular_price(),
							'_regular_price' );
						$_price         = $this->getWPMLPrice( $_compo_prod, $_compo_prod->get_price(), '_price' );
						$_sale_price    = $this->getWPMLPrice( $_compo_prod,
							$_compo_prod->get_sale_price(),
							'_sale_price' );
						
						if ( true === $tax && $_compo_prod->is_taxable() ) {
							if ( ! empty( $_regular_price ) ) {
								$_regular_price = $this->get_price_with_tax( $_compo_prod, $_regular_price );
							}
							if ( ! empty( $_price ) ) {
								$_price = $this->get_price_with_tax( $_compo_prod, $_price );
							}
							if ( ! empty( $_sale_price ) ) {
								$_sale_price = $this->get_price_with_tax( $_compo_prod, $_sale_price );
							}
						}
						
						$_total_regular_price += $_regular_price;
						$_total_price         += $_price;
						$_total_sale_price    += $_sale_price;
					}
				}
			}
			
			$_regular_price = $this->getWPMLPrice( $product, $product->get_regular_price(), '_regular_price' );
			$_price         = $this->getWPMLPrice( $product, $product->get_price(), '_price' );
			$_sale_price    = $this->getWPMLPrice( $product, $product->get_sale_price(), '_sale_price' );
			
			if ( true === $tax && $product->is_taxable() ) {
				if ( ! empty( $_regular_price ) ) {
					$_regular_price = $this->get_price_with_tax( $product, $_regular_price );
				}
				if ( ! empty( $_price ) ) {
					$_price = $this->get_price_with_tax( $product, $_price );
				}
				if ( ! empty( $_sale_price ) ) {
					$_sale_price = $this->get_price_with_tax( $product, $_sale_price );
				}
			}
			
			$regular_price = $_total_regular_price + $_regular_price;
			$price         = $_total_price + $_price;
			$sale_price    = $_total_sale_price + $_sale_price;
		} else {
			$min_max_first = ( isset( $this->config['component_price_type'] ) ) ? $this->config['component_price_type'] : 'first';
			if ( 'first' == $min_max_first ) {
				$regular_price = $this->getWPMLPrice( $product,
					$product->get_composite_regular_price(),
					'_regular_price' );
				$price         = $this->getWPMLPrice( $product, $product->get_composite_price(), '_price' );
				if ( true === $tax && $product->is_taxable() ) {
					if ( ! empty( $regular_price ) ) {
						$regular_price = $this->get_price_with_tax( $product, $regular_price );
					}
					if ( ! empty( $_price ) ) {
						$price = $this->get_price_with_tax( $product, $price );
					}
				}
				$sale_price = ( $regular_price != $price ) ? $price : '';
			} else {
				$regular_price = $this->getWPMLPrice( $product,
					$product->get_composite_regular_price( $min_max_first ),
					'_regular_price' );
				$price         = $this->getWPMLPrice( $product,
					$product->get_composite_price( $min_max_first ),
					'_price' );
				if ( true === $tax && $product->is_taxable() ) {
					if ( ! empty( $regular_price ) ) {
						$regular_price = $this->get_price_with_tax( $product, $regular_price );
					}
					if ( ! empty( $_price ) ) {
						$price = $this->get_price_with_tax( $product, $price );
					}
				}
				$sale_price = ( $regular_price != $price ) ? $price : '';
			}
		}
		
		if ( 'regular_price' == $type ) {
			return $regular_price;
		} elseif ( 'sale_price' == $type ) {
			return $sale_price;
		} else {
			return $price;
		}
	}
	
	/**
	 * Get WooCommerce Multi language Price by Currency
	 *
	 * @param WC_Product $product
	 * @param float      $price
	 * @param string     $type | Price Type (_regular_price or _price or _sale_price)
	 *
	 * @return float
	 * @since 3.2.0
	 *
	 */
	protected function getWPMLPrice( $product, $price, $type ) {
		if ( isset( $this->config['feedCurrency'] ) && get_woocommerce_currency() != $this->config['feedCurrency'] ) {
			$price = woo_feed_get_wcml_price( $product->get_id(), $this->config['feedCurrency'], $price, $type );
		}
		
		return $price;
	}
	
	/**
	 * Get Meta
	 *
	 * @param WC_Product $product
	 * @param string     $meta post meta key
	 *
	 * @return mixed|string
	 * @since 2.2.3
	 *
	 */
	protected function getProductMeta( $product, $meta ) {
		// user can decide to get parent meta value with output type dropdown.
		return get_post_meta( $product->get_id(), $meta, true );
	}
	
	/**
	 * Filter Products by Conditions
	 *
	 * @param WC_Product $product
	 *
	 * @return bool|array
	 * @since 3.2.0
	 *
	 */
	public function filter_product( $product ) {
		
		if ( isset( $this->config['fattribute'] ) && count( $this->config['fattribute'] ) ) {
			// Filtering Variable
			$fAttributes   = $this->config['fattribute'];
			$conditions    = $this->config['condition'];
			$filterCompare = $this->config['filterCompare'];
			$filterType    = $this->config['filterType'];
			
			$matched = 0;
			foreach ( $fAttributes as $key => $check ) {
				
				$conditionName    = $this->getAttributeValueByType( $product, $check );
				$condition        = $conditions[ $key ];
				$conditionCompare = stripslashes( $filterCompare[ $key ] );
// DEBUG HERE
// echo "Product Name: ".$product->get_name() .''.$product->get_id();   echo "<br>";
// echo "Name: ".$conditionName;   echo "<br>";
// echo "Condition: ".$condition;   echo "<br>";
// echo "Compare: ".$conditionCompare;  echo "<br>";   echo "<br>";
				
				switch ( $condition ) {
					case '==':
						if ( strtolower( $conditionName ) == strtolower( $conditionCompare ) ) {
							$matched ++;
						}
						break;
					case '!=':
						if ( strtolower( $conditionName ) != strtolower( $conditionCompare ) ) {
							$matched ++;
						}
						break;
					case '>=':
						if ( strtolower( $conditionName ) >= strtolower( $conditionCompare ) ) {
							$matched ++;
						}
						break;
					case '<=':
						if ( strtolower( $conditionName ) <= strtolower( $conditionCompare ) ) {
							$matched ++;
						}
						break;
					case '>':
						if ( strtolower( $conditionName ) > strtolower( $conditionCompare ) ) {
							$matched ++;
						}
						break;
					case '<':
						if ( strtolower( $conditionName ) < strtolower( $conditionCompare ) ) {
							$matched ++;
						}
						break;
					case 'contains':
						if ( false !== strpos( strtolower( $conditionName ), strtolower( $conditionCompare ) ) ) {
							$matched ++;
						}
						break;
					case 'nContains':
						if ( false === strpos( strtolower( $conditionName ), strtolower( $conditionCompare ) ) ) {
							$matched ++;
						}
						break;
					case 'between':
						$compare_items = explode( '-', $conditionCompare );
						if ( $conditionName >= $compare_items[0] && $conditionName <= $compare_items[1] ) {
							$matched ++;
						}
						break;
					default:
						break;
				}
			}
			
			if ( 1 == $filterType && 0 == $matched ) {
				return false;
			}
			
			if ( 2 == $filterType && count( $fAttributes ) != $matched ) {
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Format output According to Output Type config
	 *
	 * @param string     $output
	 * @param array      $outputTypes
	 * @param WC_Product $product
	 * @param string     $productAttribute
	 *
	 * @return float|int|string
	 * @since 3.2.0
	 *
	 */
	protected function format_output( $output, $outputTypes, $product, $productAttribute ) {
		if ( ! empty( $outputTypes ) && is_array( $outputTypes ) ) {

			// Format Output According to output type
			if ( in_array( 2, $outputTypes ) ) { // Strip Tags
				$output = wp_strip_all_tags( html_entity_decode( $output ) );
			}

			if ( in_array( 3, $outputTypes ) ) { // UTF-8 Encode
				$output = utf8_encode( $output );
			}

			if ( in_array( 4, $outputTypes ) ) { // htmlentities
				$output = htmlentities( $output, ENT_QUOTES, 'UTF-8' );
			}

			if ( in_array( 5, $outputTypes ) ) { // Integer
				$output = intval( $output );
			}

			if ( in_array( 6, $outputTypes ) ) { // Format Price
				if ( ! empty( $output ) && $output > 0 ) {
					$output = (float) $output;
					$output = woo_feed_format_price( $output, $this->config );
				}
			}

			if ( in_array( 7, $outputTypes ) ) { // Delete Space
				$output = trim( $output );
				$output = preg_replace( '!\s+!', ' ', $output );
			}

			if ( in_array( 9, $outputTypes ) ) { // Remove Invalid Character
				$output = woo_feed_stripInvalidXml( $output );
			}

			if ( in_array( 10, $outputTypes ) ) {  // Remove ShortCodes
				$output = $this->remove_short_codes( $output );
			}

			if ( in_array( 11, $outputTypes ) ) {
				$output = ucwords( strtolower( $output ) );
			}

			if ( in_array( 12, $outputTypes ) ) {
				$output = ucfirst( strtolower( $output ) );
			}

			if ( in_array( 13, $outputTypes ) ) {
				$output = strtoupper( strtolower( $output ) );
			}

			if ( in_array( 14, $outputTypes ) ) {
				$output = strtolower( $output );
			}

			if ( in_array( 15, $outputTypes ) ) {
				if ( 'http' == substr( $output, 0, 4 ) ) {
					$output = str_replace( 'http://', 'https://', $output );
				}
			}

			if ( in_array( 16, $outputTypes ) ) {
				if ( 'http' == substr( $output, 0, 4 ) ) {
					$output = str_replace( 'https://', 'http://', $output );
				}
			}

			if ( in_array( 17, $outputTypes ) ) { // only parent
				if ( $product->is_type( 'variation' ) ) {
					$id            = $product->get_parent_id();
					$parentProduct = wc_get_product( $id );
					$output        = $this->getAttributeValueByType( $parentProduct, $productAttribute );
				}
			}

			if ( in_array( 18, $outputTypes ) ) { // child if parent empty
				if ( $product->is_type( 'variation' ) ) {
					$id            = $product->get_parent_id();
					$parentProduct = wc_get_product( $id );
					$output        = $this->getAttributeValueByType( $parentProduct, $productAttribute );
					if ( empty( $output ) ) {
						$output = $this->getAttributeValueByType( $product, $productAttribute );
					}
				}
			}

			if ( in_array( 19, $outputTypes ) ) { // parent if child empty
				if ( $product->is_type( 'variation' ) ) {
					$output = $this->getAttributeValueByType( $product, $productAttribute );
					if ( empty( $output ) ) {
						$id            = $product->get_parent_id();
						$parentProduct = wc_get_product( $id );
						$output        = $this->getAttributeValueByType( $parentProduct, $productAttribute );
					}
				}
			}

			if ( in_array( 8, $outputTypes ) && ! empty( $output ) ) { // Add CDATA
				$output = '<![CDATA[' . $output . ']]>';
			}
		}

//		$output = $this->str_replace( $output, $productAttribute );

		return $output;
	}
	
	/**
	 * @param string $output
	 * @param string $productAttribute
	 *
	 * @return string
	 */
	protected function str_replace( $output, $productAttribute ) {
		// str_replace array can contain duplicate subjects, so better loop through...
		foreach ( $this->config['str_replace'] as $str_replace ) {
			if ( empty( $str_replace['subject'] ) || $productAttribute !== $str_replace['subject'] ) {
				continue;
			}
			$output = str_replace( $str_replace['search'], $str_replace['replace'], $output );
		}
		
		return $output;
	}
	
	/**
	 * Process command to format output
	 *
	 * @param $output
	 * @param $commands
	 * @param WC_Product  $product
	 * @param NULL|string $productAttribute
	 *
	 * @return bool|string
	 * @since 3.2.0
	 *
	 */
	public function process_commands( $output, $commands, $product, $productAttribute ) {
		// Custom Template 2 return commands as array
		if ( ! is_array( $commands ) ) {
			$commands = woo_feed_get_functions_from_command( $commands );
		}
		
		if ( ! empty( $commands['formatter'] ) ) {
			$output = trim( $output );
			foreach ( $commands['formatter'] as $key => $command ) {
				if ( ! empty( $command ) ) {
					if ( false !== strpos( $command, 'only_parent' ) ) {
						if ( $product->is_type( 'variation' ) ) {
							$id            = $product->get_parent_id();
							$parentProduct = wc_get_product( $id );
							$output        = $this->getAttributeValueByType( $parentProduct, $productAttribute );
						}
					} elseif ( false !== strpos( $command, 'parent_if_empty' ) ) {
						if ( $product->is_type( 'variation' ) ) {
							$output = $this->getAttributeValueByType( $product, $productAttribute );
							if ( empty( $output ) ) {
								$id            = $product->get_parent_id();
								$parentProduct = wc_get_product( $id );
								$output        = $this->getAttributeValueByType( $parentProduct, $productAttribute );
							}
						}
					} elseif ( false !== strpos( $command, 'parent' ) ) {
						if ( $product->is_type( 'variation' ) ) {
							$id            = $product->get_parent_id();
							$parentProduct = wc_get_product( $id );
							$output        = $this->getAttributeValueByType( $parentProduct, $productAttribute );
							if ( empty( $output ) ) {
								$output = $this->getAttributeValueByType( $product, $productAttribute );
							}
						}
					} elseif ( false !== strpos( $command, 'substr' ) ) {
						$args   = preg_split( '/\s+/', $command );
						$output = substr( $output, $args[1], $args[2] );
					} elseif ( false !== strpos( $command, 'strip_tags' ) ) {
						$output = wp_strip_all_tags( $output );
					} elseif ( false !== strpos( $command, 'htmlentities' ) ) {
						$output = htmlentities( $output );
					} elseif ( false !== strpos( $command, 'clear' ) ) {
						$output = woo_feed_stripInvalidXml( $output );
					} elseif ( false !== strpos( $command, 'ucwords' ) ) {
						$output = ucwords( strtolower( $output ) );
					} elseif ( false !== strpos( $command, 'ucfirst' ) ) {
						$output = ucfirst( strtolower( $output ) );
					} elseif ( false !== strpos( $command, 'strtoupper' ) ) {
						$output = strtoupper( strtolower( $output ) );
					} elseif ( false !== strpos( $command, 'strtolower' ) ) {
						$output = strtolower( $output );
					} elseif ( false !== strpos( $command, 'convert' ) ) {
						// Skip convert command if API Key not found
						if ( ! empty( get_option( 'woo_feed_currency_api_code' ) ) ) {
							$args              = preg_split( '/\s+/', $command );
							$number            = explode( ' ', $output );
							$convertedCurrency = woo_feed_convert_currency( $number[0], $args[1], $args[2] );
							
							if ( $convertedCurrency ) {
								$currencyCode = ( isset( $number[1] ) && ! empty( $number[1] ) ) ? ' ' . trim( $number[1] ) : '';
								$output       = $convertedCurrency . $currencyCode;
							}
						}
					} elseif ( false !== strpos( $command, 'number_format' ) ) {
						if ( ! empty( $output ) ) {
							$args      = explode( ' ', $command, 3 );
							$arguments = array( 0 => '' );
							
							if ( isset( $args[1] ) ) {
								$arguments[1] = $args[1];
							}
							
							if ( isset( $args[2] ) && 'point' == $args[2] ) {
								$arguments[2] = '.';
							} elseif ( isset( $args[2] ) && 'comma' == $args[2] ) {
								$arguments[2] = ',';
							} elseif ( isset( $args[2] ) && 'space' == $args[2] ) {
								$arguments[2] = ' ';
							}
							
							if ( isset( $args[3] ) && 'point' == $args[3] ) {
								$arguments[3] = '.';
							} elseif ( isset( $args[3] ) && 'comma' == $args[3] ) {
								$arguments[3] = ',';
							} elseif ( isset( $args[3] ) && 'space' == $args[3] ) {
								$arguments[3] = ' ';
							} else {
								$arguments[3] = '';
							}
							
							if ( isset( $arguments[1] ) && isset( $arguments[2] ) && isset( $arguments[3] ) ) {
								$output = number_format( $output, $arguments[1], $arguments[2], $arguments[3] );
							} elseif ( isset( $arguments[1] ) && isset( $arguments[2] ) ) {
								$output = number_format( $output, $arguments[1], $arguments[2], $arguments[3] );
							} elseif ( isset( $arguments[1] ) ) {
								$output = number_format( $output, $arguments[1] );
							} else {
								$output = number_format( $output );
							}
						}
					} elseif ( false !== strpos( strtolower( $command ), 'urltounsecure' ) ) {
						if ( 'http' == substr( $output, 0, 4 ) ) {
							$output = str_replace( 'https://', 'http://', $output );
						}
					} elseif ( false !== strpos( strtolower( $command ), 'urltosecure' ) ) {
						if ( 'http' == substr( $output, 0, 4 ) ) {
							$output = str_replace( 'http://', 'https://', $output );
						}
					} elseif ( false !== strpos( $command, 'str_replace' ) ) {
						$args = explode( '=>', $command, 3 );
						if ( array_key_exists( 1, $args ) && array_key_exists( 2, $args ) ) {
							
							$argument1 = $args[1];
							$argument2 = $args[2];
							
							if ( false !== strpos( $args[1], 'comma' ) ) {
								$argument1 = str_replace( 'comma', ',', $args[1] );
							}
							
							if ( false !== strpos( $args[2], 'comma' ) ) {
								$argument2 = str_replace( 'comma', ',', $args[2] );
							}
							
							$output = str_replace( "$argument1", "$argument2", $output );
						}
					} elseif ( false !== strpos( $command, 'strip_shortcodes' ) ) {
						$output = $this->remove_short_codes( $output );
					} elseif ( false !== strpos( $command, 'preg_replace' ) ) {
						$args = explode( ' ', $command, 3 );
						
						$argument1 = $args[1];
						$argument2 = $args[2];
						
						if ( false !== strpos( $args[1], 'comma' ) ) {
							$argument1 = str_replace( 'comma', ',', $args[1] );
						}
						
						if ( false !== strpos( $args[2], 'comma' ) ) {
							$argument2 = str_replace( 'comma', ',', $args[2] );
						}
						
						$output = preg_replace( stripslashes( $argument1 ), $argument2, $output );
					}
				}
			}
		}
		
		if ( in_array( $this->config['provider'], woo_feed_get_custom2_merchant() ) ) {
			$output = $this->str_replace( $output, $productAttribute );
		}
		
		return "$output";
	}
	
	public function get_not_ids_in() {
		return $this->ids_not_in;
	}
	public function get_ids_in() {
		return $this->ids_in;
	}
}
