<?php

/**
 * A class definition responsible for processing and mapping product according to feed rules and make the feed
 *
 * @link       https://webappick.com/
 * @since      1.0.0
 *
 * @package    Woo_Feed
 * @subpackage Woo_Feed/includes
 * @author     Ohidul Islam <wahid@webappick.com>
 */
class WF_Engine {

	/**
	 * This variable is responsible for mapping store attributes to merchant attribute
	 *
	 * @since   1.0.0
	 * @var     array $mapping Map store attributes to merchant attribute
	 * @access  private
	 */
	private $mapping;

	/**
	 * Store product information
	 *
	 * @since   1.0.0
	 * @var     array $storeProducts
	 * @access  public
	 */
	private $storeProducts;

	/**
	 * New product information
	 *
	 * @since   1.0.0
	 * @var     array $products
	 * @access  private
	 */
	private $products;

	/**
	 * Contain Feed Rules
	 *
	 * @since   1.0.0
	 * @var     array $rules
	 * @access  private
	 */
	private $rules;

	/**
	 * Contain TXT Feed First Row
	 *
	 * @since   1.0.0
	 * @var     array $rules
	 * @access  private
	 */
	public $txtFeedHeader;

	/**
	 * Contain CSV Feed First Row
	 *
	 * @since   1.0.0
	 * @var     array $rules
	 * @access  private
	 */
	public $csvFeedHeader;
	public $productClass;
	public $oldpinfo;

	/**
	 * Fallback key for if any attribute value is empty
	 *
	 * @since 3.1.18
	 * @see WF_Engine::productArrayByMapping()
	 * @var array
	 */
	public $fallbackValue = array(
		'aioseop_title'        => 'title',
		'aioseop_description'  => 'description',
		'yoast_wpseo_title'    => 'title',
		'yoast_wpseo_metadesc' => 'description',
	);

	public function __construct( $Products, $rules ) {
		$this->rules         = $rules;
		$this->storeProducts = $Products;
		$this->oldpinfo      = $Products;
		$this->productClass  = new Woo_Feed_Products();
	}

	/**
	 * Return Category Mapping Values by Parent Product Id
	 *
	 * @param   string $mappingName Category Mapping Name
	 * @param   int    $parent Parent id of the product
	 *
	 * @return mixed
	 */
	public function get_category_mapping_value( $mappingName, $parent ) {
		return woo_feed_get_category_mapping_value( $mappingName, $parent );
	}

	/**
	 * Format price value
	 *
	 * @param string $name Attribute Name
	 * @param int    $conditionName condition
	 * @param int    $result price
	 *
	 * @return mixed
	 */
	public function price_format( $name, $conditionName, $result ) {
		$plus    = '+';
		$minus   = '-';
		$percent = '%';

		if ( strpos( $name, 'price' ) !== false ) {
			if ( strpos( $result, $plus ) !== false && strpos( $result, $percent ) !== false ) {
				$result = str_replace( '+', '', $result );
				$result = str_replace( '%', '', $result );
				if ( is_numeric( $result ) ) {
					$result = $conditionName + ( ( $conditionName * $result ) / 100 );
				}
			} elseif ( strpos( $result, $minus ) !== false && strpos( $result, $percent ) !== false ) {
				$result = str_replace( '-', '', $result );
				$result = str_replace( '%', '', $result );
				if ( is_numeric( $result ) ) {
					$result = $conditionName - ( ( $conditionName * $result ) / 100 );
				}
			} elseif ( strpos( $result, $plus ) !== false ) {
				$result = str_replace( '+', '', $result );
				if ( is_numeric( $result ) ) {
					$result = ( $conditionName + $result );
				}
			} elseif ( strpos( $result, $minus ) !== false ) {
				$result = str_replace( '-', '', $result );
				if ( is_numeric( $result ) ) {
					$result = $conditionName - $result;
				}
			}
		}

		return $result;
	}

	/**
	 * Get the value of a dynamic attribute
	 *
	 * @param $attributeName
	 * @param $attributes
	 *
	 * @return mixed|string
	 */
	public function get_dynamic_attribute_value( $attributeName, $attributes ) {
		$getValue = maybe_unserialize( get_option( $attributeName ) );
		// $wfDAttributeName = isset( $getValue['wfDAttributeName'] ) ? $getValue['wfDAttributeName'] : '';
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

		$id       = $attributes['id'];
		$parentId = $attributes['item_group_id'];

		// Check If Attribute Code exist
		if ( $wfDAttributeCode ) {
			if ( count( $attribute ) ) {
				foreach ( $attribute as $key => $name ) {
					if ( ! empty( $name ) ) {

						if ( array_key_exists( $name, $attributes ) ) {
							$conditionName = $attributes[ $name ];
						} elseif ( strpos( $name, Woo_Feed_Products::PRODUCT_ATTRIBUTE_PREFIX ) !== false ) {
							$conditionName = $this->productClass->getProductAttribute( $id, $name );
						} elseif ( strpos( $name, Woo_Feed_Products::POST_META_PREFIX ) !== false ) {
							$conditionName = $this->productClass->getProductMeta( $id, $name );
						} elseif ( strpos( $name, Woo_Feed_Products::PRODUCT_TAXONOMY_PREFIX ) !== false ) {
							$conditionName = $this->productClass->getProductTaxonomy( $parentId, $name );
						} elseif ( strpos( $name, Woo_Feed_Products::PRODUCT_CATEGORY_MAPPING_PREFIX ) !== false ) {
							$conditionName = $this->get_category_mapping_value( $name, $parentId );
						} elseif ( strpos( $name, Woo_Feed_Products::PRODUCT_DYNAMIC_ATTRIBUTE_PREFIX ) !== false ) {
							$conditionName = $this->get_dynamic_attribute_value( $name, $attributes );
						} elseif ( strpos( $name, Woo_Feed_Products::WP_OPTION_PREFIX ) !== false ) {
							$optionName    = str_replace( Woo_Feed_Products::WP_OPTION_PREFIX, '', $name );
							$optionValue   = get_option( $optionName );
							$conditionName = $optionValue;
						}
						$conditionCompare = $compare[ $key ];
						$conditionValue   = '';
						if ( ! empty( $conditionCompare ) ) {
							$conditionCompare = trim( $conditionCompare );
						}

						if ( $type[ $key ] == 'pattern' ) {
							$conditionValue = $value_pattern[ $key ];
						} elseif ( $type[ $key ] == 'attribute' ) {
							if ( array_key_exists( $value_attribute[ $key ], $attributes ) ) {
								$conditionValue = $attributes[ $value_attribute[ $key ] ];
							} elseif ( strpos( $value_attribute[ $key ], Woo_Feed_Products::PRODUCT_ATTRIBUTE_PREFIX ) !== false ) {
								$conditionValue = $this->productClass->getProductAttribute( $id, $value_attribute[ $key ] );
							} elseif ( strpos( $value_attribute[ $key ], Woo_Feed_Products::POST_META_PREFIX ) !== false ) {
								$conditionValue = $this->productClass->getProductMeta( $id, $value_attribute[ $key ] );
							} elseif ( strpos( $value_attribute[ $key ], Woo_Feed_Products::PRODUCT_TAXONOMY_PREFIX ) !== false ) {
								$conditionValue = $this->productClass->getProductTaxonomy( $parentId, $value_attribute[ $key ] );
							} elseif ( strpos( $value_attribute[ $key ], Woo_Feed_Products::PRODUCT_CATEGORY_MAPPING_PREFIX ) !== false ) {
								$conditionValue = $this->get_category_mapping_value( $value_attribute[ $key ], $parentId );
							} elseif ( strpos( $value_attribute[ $key ], Woo_Feed_Products::PRODUCT_DYNAMIC_ATTRIBUTE_PREFIX ) !== false ) {
								$conditionValue = $this->get_dynamic_attribute_value( $value_attribute[ $key ], $attributes );
							} elseif ( strpos( $value_attribute[ $key ], Woo_Feed_Products::WP_OPTION_PREFIX ) !== false ) {
								$optionName     = str_replace( Woo_Feed_Products::WP_OPTION_PREFIX, '', $value_attribute[ $key ] );
								$optionValue    = get_option( $optionName );
								$conditionValue = $optionValue;
							}
						} elseif ( $type[ $key ] == 'remove' ) {
							$conditionValue = '';
						}

						switch ( $condition[ $key ] ) {
							case '==':
								if ( $conditionName == $conditionCompare ) {
									$result = $conditionValue;
									$result = $this->price_format( $name, $conditionName, $result );

									if ( $result != '' ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								break;
							case '!=':
								if ( $conditionName != $conditionCompare ) {
									$result = $conditionValue;
									$result = $this->price_format( $name, $conditionName, $result );
									if ( $result != '' ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								break;
							case '>=':
								if ( $conditionName >= $conditionCompare ) {
									$result = $conditionValue;
									$result = $this->price_format( $name, $conditionName, $result );
									if ( $result != '' ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}

								break;
							case '<=':
								if ( $conditionName <= $conditionCompare ) {
									$result = $conditionValue;
									$result = $this->price_format( $name, $conditionName, $result );
									if ( $result != '' ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								break;
							case '>':
								if ( $conditionName > $conditionCompare ) {
									$result = $conditionValue;
									$result = $this->price_format( $name, $conditionName, $result );
									if ( $result != '' ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								break;
							case '<':
								if ( $conditionName < $conditionCompare ) {
									$result = $conditionValue;
									$result = $this->price_format( $name, $conditionName, $result );
									if ( $result != '' ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								break;
							case 'contains':
								if ( strpos( strtolower( $conditionName ), strtolower( $conditionCompare ) ) !== false ) {
									$result = $conditionValue;
									$result = $this->price_format( $name, $conditionName, $result );
									if ( $result != '' ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								break;
							case 'nContains':
								if ( strpos( strtolower( $conditionName ), strtolower( $conditionCompare ) ) === false ) {
									$result = $conditionValue;
									$result = $this->price_format( $name, $conditionName, $result );
									if ( $result != '' ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								break;
							case 'between':
								$compare_items = explode( '-', $conditionCompare );

								if ( isset( $compare_items[1] ) && is_numeric( $compare_items[0] ) && is_numeric( $compare_items[1] ) ) {
									if ( $conditionName >= $compare_items[0] && $conditionName <= $compare_items[1] ) {
										$result = $conditionValue;
										$result = $this->price_format( $name, $conditionName, $result );
										if ( $result != '' ) {
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

		if ( $result == '' ) {
			if ( $default_type == 'pattern' ) {
				$result = $default_value_pattern;
			} elseif ( $default_type == 'attribute' ) {
				if ( ! empty( $default_value_attribute ) ) {
					if ( array_key_exists( $default_value_attribute, $attributes ) ) {
						$result = $attributes[ $default_value_attribute ];
					} elseif ( strpos( $default_value_attribute, Woo_Feed_Products::PRODUCT_ATTRIBUTE_PREFIX ) !== false ) {
						$result = $this->productClass->getProductAttribute( $id, $default_value_attribute );
					} elseif ( strpos( $default_value_attribute, Woo_Feed_Products::POST_META_PREFIX ) !== false ) {
						$result = $this->productClass->getProductMeta( $id, $default_value_attribute );
					} elseif ( strpos( $default_value_attribute, Woo_Feed_Products::PRODUCT_TAXONOMY_PREFIX ) !== false ) {
						$result = $this->productClass->getProductTaxonomy( $parentId, $default_value_attribute );
					} elseif ( strpos( $default_value_attribute, Woo_Feed_Products::PRODUCT_CATEGORY_MAPPING_PREFIX ) !== false ) {
						$result = $this->get_category_mapping_value( $default_value_attribute, $parentId );
					} elseif ( strpos( $default_value_attribute, Woo_Feed_Products::PRODUCT_DYNAMIC_ATTRIBUTE_PREFIX ) !== false ) {
						$result = $this->get_dynamic_attribute_value( $default_value_attribute, $attributes );
					} elseif ( strpos( $default_value_attribute, Woo_Feed_Products::WP_OPTION_PREFIX ) !== false ) {
						$optionName  = str_replace( Woo_Feed_Products::WP_OPTION_PREFIX, '', $default_value_attribute );
						$optionValue = get_option( $optionName );
						$result      = $optionValue;
					}
				}
			} elseif ( $default_type == 'remove' ) {
				$result = '';
			}
		}

		return $result;
	}

	/**
	 * Filter Products by Conditions
	 *
	 * @param $attributes
	 *
	 * @return bool|array
	 */
	public function filter_product( $attributes ) {

		// Filtering Variable
		$fAttributes   = $this->rules['fattribute'];
		$condition     = $this->rules['condition'];
		$filterCompare = $this->rules['filterCompare'];
		$filterType    = $this->rules['filterType'];

		$id       = $attributes['id'];
		$parentId = $attributes['item_group_id'];

		$matched = 0;
		foreach ( $fAttributes as $key => $check ) {

			if ( array_key_exists( $check, $attributes ) ) {
				$conditionName = $attributes[ $check ];
			} elseif ( strpos( $check, Woo_Feed_Products::PRODUCT_ATTRIBUTE_PREFIX ) !== false ) {
				$conditionName = $this->productClass->getProductAttribute( $id, str_replace( Woo_Feed_Products::PRODUCT_ATTRIBUTE_PREFIX, '', $check ) );
			} elseif ( strpos( $check, Woo_Feed_Products::POST_META_PREFIX ) !== false ) {
				$conditionName = $this->productClass->getProductMeta( $id, str_replace( Woo_Feed_Products::POST_META_PREFIX, '', $check ) );
			} elseif ( strpos( $check, Woo_Feed_Products::PRODUCT_TAXONOMY_PREFIX ) !== false ) {
				$conditionName = $this->productClass->getProductTaxonomy( $parentId, str_replace( Woo_Feed_Products::PRODUCT_TAXONOMY_PREFIX, '', $check ) );
			} elseif ( strpos( $check, Woo_Feed_Products::PRODUCT_CATEGORY_MAPPING_PREFIX ) !== false ) {
				$conditionName = $this->get_category_mapping_value( $check, $parentId );
			} elseif ( strpos( $check, Woo_Feed_Products::PRODUCT_DYNAMIC_ATTRIBUTE_PREFIX ) !== false ) {
				$conditionName = $this->get_dynamic_attribute_value( $check, $attributes );
			} elseif ( strpos( $check, Woo_Feed_Products::WP_OPTION_PREFIX ) !== false ) {
				$optionName    = str_replace( Woo_Feed_Products::WP_OPTION_PREFIX, '', $check );
				$optionValue   = get_option( $optionName );
				$conditionName = $optionValue;
			}

			$con              = $condition[ $key ];
			$conditionCompare = stripslashes( $filterCompare[ $key ] );

			switch ( $con ) {
				case '==':
					if ( strtolower( $conditionName ) == strtolower( $conditionCompare ) ) {
						$matched++;
					}
					break;
				case '!=':
					if ( strtolower( $conditionName ) != strtolower( $conditionCompare ) ) {
						$matched++;
					}
					break;
				case '>=':
					if ( strtolower( $conditionName ) >= strtolower( $conditionCompare ) ) {
						$matched++;
					}
					break;
				case '<=':
					if ( strtolower( $conditionName ) <= strtolower( $conditionCompare ) ) {
						$matched++;
					}
					break;
				case '>':
					if ( strtolower( $conditionName ) > strtolower( $conditionCompare ) ) {
						$matched++;
					}
					break;
				case '<':
					if ( strtolower( $conditionName ) < strtolower( $conditionCompare ) ) {
						$matched++;
					}
					break;
				case 'contains':
					if ( strpos( strtolower( $conditionName ), strtolower( $conditionCompare ) ) !== false ) {
						$matched++;
					}
					break;
				case 'nContains':
					if ( strpos( strtolower( $conditionName ), strtolower( $conditionCompare ) ) === false ) {
						$matched++;
					}
					break;
				case 'between':
					$compare_items = explode( '-', $conditionCompare );
					if ( $conditionName >= $compare_items[0] && $conditionName <= $compare_items[1] ) {
						$matched++;
					}
					break;
				default:
					break;
			}
		}

		if ( $filterType == 1 && $matched == 0 ) {
			return false;
		}

		if ( $filterType == 2 && count( $fAttributes ) != $matched ) {
			return false;
		}

		return true;
	}

	/**
	 * Configure the feed according to the rules
	 *
	 * @return array
	 */
	public function mapProductsByRules() {
		$attributes         = $this->rules['attributes'];
		$prefix             = $this->rules['prefix'];
		$suffix             = $this->rules['suffix'];
		$outputType         = $this->rules['output_type'];
		$limit              = $this->rules['limit'];
		$merchantAttributes = $this->rules['mattributes'];
		$type               = $this->rules['type'];
		$default            = $this->rules['default'];
		$feedType           = $this->rules['feedType'];

		$wf_attr       = array();
		$wf_cattr      = array();
		$wf_taxo       = array();
		$wf_dattribute = array();
		$wf_cmapping   = array();
		$wf_option     = array();

		// Map Merchant Attributes and Woo Attributes
		$countAttr = 0;
		update_option( 'wpf_progress', 'Mapping Attributes', false );

		if ( count( $merchantAttributes ) ) {
			foreach ( $merchantAttributes as $key => $attr ) {
				if ( $type[ $key ] == 'attribute' ) {
					$this->mapping[ $attr ]['value']  = $attributes[ $key ];
					$this->mapping[ $attr ]['suffix'] = $suffix[ $key ];
					$this->mapping[ $attr ]['prefix'] = $prefix[ $key ];
					$this->mapping[ $attr ]['type']   = $outputType[ $key ];
					$this->mapping[ $attr ]['limit']  = $limit[ $key ];
				} elseif ( $type[ $key ] == 'pattern' ) {
					$this->mapping[ $attr ]['value']  = "wf_pattern_$default[$key]";
					$this->mapping[ $attr ]['suffix'] = $suffix[ $key ];
					$this->mapping[ $attr ]['prefix'] = $prefix[ $key ];
					$this->mapping[ $attr ]['type']   = $outputType[ $key ];
					$this->mapping[ $attr ]['limit']  = $limit[ $key ];
				}
				$countAttr++;
			}
		}

		// Process Dynamic Attributes and Category Mapping
		if ( count( $this->mapping ) ) {
			foreach ( $this->mapping as $mkey => $attr ) {
				if ( strpos( $attr['value'], Woo_Feed_Products::PRODUCT_CATEGORY_MAPPING_PREFIX ) !== false ) {
					$wf_cmapping[] = $attr['value'];
				}

				if ( strpos( $attr['value'], Woo_Feed_Products::WP_OPTION_PREFIX ) !== false ) {
					$wf_option[] = $attr['value'];
				}

				if ( strpos( $attr['value'], Woo_Feed_Products::PRODUCT_DYNAMIC_ATTRIBUTE_PREFIX ) !== false ) {
					$wf_dattribute[] = $attr['value'];
				}

				if ( strpos( $attr['value'], Woo_Feed_Products::PRODUCT_ATTRIBUTE_PREFIX ) !== false ) {
					$wf_attr[] = $attr['value'];
				}

				if ( strpos( $attr['value'], Woo_Feed_Products::POST_META_PREFIX ) !== false ) {
					$wf_cattr[] = $attr['value'];
				}

				if ( strpos( $attr['value'], Woo_Feed_Products::PRODUCT_TAXONOMY_PREFIX ) !== false ) {
					$wf_taxo[] = $attr['value'];
				}
			}
			// Init Woo Attributes, Custom Attributes and Taxonomies
			if ( ! empty( $this->storeProducts ) ) {
				$i = 0;
				foreach ( $this->storeProducts as $key => $value ) {
					$id       = $value['id'];
					$parentId = $value['item_group_id'];

					// if ($value['type'] == 'variation') {
					// $this->storeProducts[$key]['title']=$this->get_formatted_variation_title($id,$parentId,$value['parent_title'],$key);
					// }else{
					// $this->storeProducts[$key]['title']=$this->get_formatted_variation_title($id,$parentId,$value['title'],$key);
					// }

					$this->storeProducts[ $key ]['title'] = $this->get_formatted_variation_title( $id, $parentId, $value['title'], $key );

					// Get Dynamic Attribute Values
					if ( count( $wf_dattribute ) ) {
						foreach ( $wf_dattribute as $wf_dattribute_key => $wf_dattribute_value ) {
							$dAttribute = $this->get_dynamic_attribute_value( $wf_dattribute_value, $value );
							$this->storeProducts[ $key ][ $wf_dattribute_value ] = $dAttribute;
						}
					}

					// Get Category Mapping Values
					if ( count( $wf_cmapping ) ) {
						foreach ( $wf_cmapping as $wf_cmapping_key => $wf_cmapping_value ) {
							$category = $this->get_category_mapping_value( $wf_cmapping_value, $parentId );
							$this->storeProducts[ $key ][ $wf_cmapping_value ] = $category;
						}
					}

					// Get WP Option Value
					if ( count( $wf_option ) ) {
						foreach ( $wf_option as $wf_option_key => $wf_option_value ) {
							$optionName                                      = str_replace( 'wf_option_', '', $wf_option_value );
							$optionValue                                     = get_option( $optionName );
							$this->storeProducts[ $key ][ $wf_option_value ] = $optionValue;
						}
					}

					// Get Woo Attributes
					if ( count( $wf_attr ) ) {
						foreach ( $wf_attr as $attr_key => $attr_value ) {
							$this->storeProducts[ $key ][ $attr_value ] = $this->productClass->getProductAttribute( $id, $attr_value );
						}
					}

					// Get Custom Attributes
					if ( count( $wf_cattr ) ) {
						foreach ( $wf_cattr as $cattr_key => $cattr_value ) {
							$this->storeProducts[ $key ][ $cattr_value ] = $this->productClass->getProductMeta( $id, $cattr_value );
						}
					}

					// Get Taxonomies
					if ( count( $wf_taxo ) ) {
						foreach ( $wf_taxo as $taxo_key => $taxo_value ) {
							$this->storeProducts[ $key ][ $taxo_value ] = $this->productClass->getProductTaxonomy( $parentId, $taxo_value );
						}
					}
					// Make the product array according to mapping rules
					$this->productArrayByMapping( $key );
					$i++;
				}
			}
		}

		return $this->products;
	}

	/**
	 * Base Currency Convert
	 *
	 * @param $from
	 * @param $to
	 * @param int  $retry
	 *
	 * @return bool|float
	 */
	function baseConvert( $from, $to, $retry = 0 ) {
		return woo_feed_get_conversion_rate( $from, $to, $retry );
	}

	/**
	 * Currency Convert
	 *
	 * @param $amount
	 * @param $from
	 * @param $to
	 *
	 * @return float
	 */
	public function convertCurrency( $amount, $from, $to ) {
		$optionName = strtolower( $from ) . strtolower( $to );
		if ( ! get_option( $optionName ) || get_option( 'wf_convert_' . date( 'Y-m-d' ) != date( 'Y-m-d' ) ) ) {
			$converted = $this->baseConvert( $from, $to );

			update_option( 'wf_convert_' . gmdate( 'Y-m-d' ), gmdate( 'Y-m-d' ), false );
			update_option( $optionName, $converted, false );
			$newAmount = $amount * $converted;
			return round( $newAmount, 2 );
		} else {
			$rate = get_option( $optionName );
			return round( $amount * $rate, 2 );
		}
	}

	/**
	 * Remove Invalid Character from XML
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function stripInvalidXml( $value ) {
		return woo_feed_stripInvalidXml( $value );
	}

	/**  Separate XML header footer and body from feed content
	 *
	 * @param $string
	 * @param $start
	 * @param $end
	 * @return string
	 */
	public function get_string_between( $string, $start, $end ) {
		$string = ' ' . $string;
		$ini    = strpos( $string, $start );
		if ( $ini == 0 ) {
			return '';
		}
		$ini += strlen( $start );
		$len  = strpos( $string, $end, $ini ) - $ini;
		return substr( $string, $ini, $len );
	}

	/**
	 * Process command to format output
	 *
	 * @param $output
	 * @param $attribute
	 *
	 * @return bool|string
	 */
	public function outputFormatter( $output, $attribute ) {
		if ( ! empty( $attribute['formatter'] ) ) {
			$output = trim( $output );
			foreach ( $attribute['formatter'] as $key => $value ) {

				if ( ! empty( $value ) && ! empty( $output ) ) {

					if ( strpos( $value, 'substr' ) !== false ) {
						$args   = preg_split( '/\s+/', $value );
						$output = substr( $output, $args[1], $args[2] );
					} elseif ( strpos( $value, 'strip_tags' ) !== false ) {
						// $output= wp_strip_all_tags(html_entity_decode($output));
						$output = wp_strip_all_tags( $output );
					} elseif ( strpos( $value, 'htmlentities' ) !== false ) {
						$output = htmlentities( $output );
					} elseif ( strpos( $value, 'clear' ) !== false ) {
						$output = $this->stripInvalidXml( $output );
					} elseif ( strpos( $value, 'ucwords' ) !== false ) {
						$output = ucwords( strtolower( $output ) );
					} elseif ( strpos( $value, 'ucfirst' ) !== false ) {
						$output = ucfirst( strtolower( $output ) );
					} elseif ( strpos( $value, 'strtoupper' ) !== false ) {
						$output = strtoupper( strtolower( $output ) );
					} elseif ( strpos( $value, 'strtolower' ) !== false ) {
						$output = strtolower( $output );
					} elseif ( strpos( $value, 'convert' ) !== false ) {
						$args              = preg_split( '/\s+/', $value );
						$number            = explode( ' ', $output );
						$convertedCurrency = $this->convertCurrency( $number[0], $args[1], $args[2] );

						if ( $convertedCurrency ) {
							$currencyCode = ( isset( $number[1] ) && ! empty( $number[1] ) ) ? ' ' . trim( $number[1] ) : '';
							$output       = $convertedCurrency . $currencyCode;
						}
					} elseif ( strpos( $value, 'number_format' ) !== false ) {
						// $args=preg_split('/\s+/', $value);
						$args = explode( ' ', $value, 3 );

						$arguments = array( 0 => '' );

						if ( isset( $args[1] ) ) {
							$arguments[1] = $args[1];
						}

						if ( isset( $args[2] ) && $args[2] == 'point' ) {
							$arguments[2] = '.';
						} elseif ( isset( $args[2] ) && $args[2] == 'comma' ) {
							$arguments[2] = ',';
						} elseif ( isset( $args[2] ) && $args[2] == 'space' ) {
							$arguments[2] = ' ';
						}

						if ( isset( $args[3] ) && $args[3] == 'point' ) {
							$arguments[3] = '.';
						} elseif ( isset( $args[3] ) && $args[3] == 'comma' ) {
							$arguments[3] = ',';
						} elseif ( isset( $args[3] ) && $args[3] == 'space' ) {
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
					} elseif ( strpos( strtolower( $value ), 'urltounsecure' ) !== false ) {
						if ( substr( $output, 0, 4 ) == 'http' ) {
							$output = str_replace( 'https://', 'http://', $output );
						}
					} elseif ( strpos( strtolower( $value ), 'urltosecure' ) !== false ) {
						if ( substr( $output, 0, 4 ) == 'http' ) {
							$output = str_replace( 'http://', 'https://', $output );
						}
					} elseif ( strpos( $value, 'str_replace' ) !== false ) {
						$args = explode( '=>', $value, 3 );
						if ( array_key_exists( 1, $args ) && array_key_exists( 2, $args ) ) {

							$argument1 = ( trim( $args[1] ) == 'comma' ) ? ',' : $args[1];
							$argument2 = ( trim( $args[2] ) == 'comma' ) ? ',' : $args[2];

							$param1 = ! empty( $argument1 ) ? trim( $argument1 ) : ' ';
							$param2 = ! empty( $argument2 ) ? trim( $argument2 ) : ' ';

							$output = str_replace( $param1, $param2, $output );
						}
					} elseif ( strpos( $value, 'strip_shortcodes' ) !== false ) {
						// $output=preg_replace("/\[[^]]+\]/",'',$output);
						// $output=do_shortcode($output);
						// $output=$this->productClass->remove_short_codes($output);
					} elseif ( strpos( $value, 'preg_replace' ) !== false ) {
						$args = explode( ' ', $value, 3 );

						if ( ! isset( $args[2] ) ) {
							$args[2] = '';
						}

						if ( $args[2] == 'comma' ) {
							$args[2] = ',';
						}
						$output = preg_replace( $args[1], $args[2], $output );
					}
				}
			}
			return $output;
		}
		return false;
	}



	public function productArrayByMapping( $key ) {
		$value = $this->storeProducts[ $key ];
		// Make Product feed array according to mapping
		$totalProduct = count( $this->storeProducts );
		$count        = 1;

		// Filter products by condition
		if ( isset( $this->rules['fattribute'] ) && count( $this->rules['fattribute'] ) ) {
			if ( ! $this->filter_product( $value ) ) {
				return false;
			}
		}
		$i = 0;
		foreach ( $this->mapping as $attr => $rules ) {
			if ( is_array( $value ) && array_key_exists( $rules['value'], $value ) ) {
				$output = $value[ $rules['value'] ];
				if ( empty( $output ) && in_array( $rules['value'], array_keys( $this->fallbackValue ) ) ) {
					$output = $value[ $this->fallbackValue[ $rules['value'] ] ];
				}

				if ( ! empty( $output ) ) {
					// @TODO remove duplicate codes by adding new method for eval output rule type << $output
					if ( ! empty( $rules['type'] ) ) {
						foreach ( $rules['type'] as $key22 => $value22 ) {
							// Format Output According to output type
							// @TODO Tokenize output rule type
							if ( $value22 == 2 ) { // Strip Tags
								$output = wp_strip_all_tags( html_entity_decode( $output ) );
							} elseif ( $value22 == 3 ) { // UTF-8 Encode
								$output = utf8_encode( $output );
							} elseif ( $value22 == 4 ) { // htmlentities
								$output = htmlentities( $output, ENT_QUOTES, 'UTF-8' );
							} elseif ( $value22 == 5 ) { // Integer
								$output = absint( $output );
							} elseif ( $value22 == 6 ) { // Format Price
								$output = (float) $output;
								$output = number_format( $output, 2, '.', '' );
							} elseif ( $value22 == 7 ) { // Delete Space
								$output = trim( $output );
							} elseif ( $value22 == 8 ) { // Add CDATA
								$output = '<![CDATA[' . $output . ']]>';
							} elseif ( $value22 == 9 ) { // Remove Invalid Character
								$output = $this->stripInvalidXml( $output );
							} elseif ( $value22 == 10 ) {  // Remove ShortCodes
								// $output=$this->productClass->remove_short_codes($output);
							}
						}
					}
					// Format Output According to output limit
					// @TODO remove redundant code by replacing cdata before an don't add again until finish
					if ( ! empty( $rules['limit'] ) && strpos( $output, '<![CDATA[' ) !== false ) {
						$output = str_replace( array( '<![CDATA[', ']]>' ), array( '', '' ), $output );

						// Limit is PHP Command from version 2
						$functions = $this->getPHPFunctions( $rules['limit'] );
						$output    = $this->outputFormatter( $output, $functions );

						$output = '<![CDATA[' . $output . ']]>';
					} elseif ( ! empty( $rules['limit'] ) ) {

						// Limit is PHP Command from version 2
						$functions = $this->getPHPFunctions( $rules['limit'] );
						$output    = $this->outputFormatter( $output, $functions );
					}

					// Prefix and Suffix Assign
					if ( strpos( $output, '<![CDATA[' ) !== false ) {
						$output = str_replace( array( '<![CDATA[', ']]>' ), array( '', '' ), $output );
						if ( substr( $output, 0, 4 ) === 'http' ) {
							$output = $this->make_url_with_parameter( $rules['prefix'], $output, $rules['suffix'] );
						} else {
							if ( ( $rules['value'] == 'price' || $rules['value'] == 'sale_price' || $rules['value'] == 'current_price' || $rules['value'] == 'price_with_tax' ) && $output != '' ) {
								$suffix = ! empty( $rules['suffix'] ) ? trim( $rules['suffix'] ) : $rules['suffix'];

								// If WPML installed & base currency code not match then convert currency
								$currencyCode = strtoupper( strtolower( get_woocommerce_currency() ) );
								if ( class_exists( 'SitePress' ) && ! empty( $suffix ) && $currencyCode != $suffix ) {
									$output = woo_feed_get_wcml_price( $value['id'], $suffix, $output, '_' . $rules['value'] );
								}

								$output = $rules['prefix'] . $output . ' ' . $suffix;
							} else {
								if ( $output != '' ) {
									$output = $rules['prefix'] . $output . $rules['suffix'];
								}
							}
						}
						$output = '<![CDATA[' . $output . ']]>';

					} else {
						if ( substr( $output, 0, 4 ) === 'http' ) {
							$output = $this->make_url_with_parameter( $rules['prefix'], $output, $rules['suffix'] );
						} else {
							if ( ( $rules['value'] == 'price' || $rules['value'] == 'sale_price' || $rules['value'] == 'current_price' || $rules['value'] == 'price_with_tax' ) && $output != '' ) {
								$suffix = ! empty( $rules['suffix'] ) ? trim( strtoupper( strtolower( $rules['suffix'] ) ) ) : $rules['suffix'];

								// If WPML installed & base currency code not match then convert currency
								$currencyCode = strtoupper( strtolower( get_woocommerce_currency() ) );
								if ( class_exists( 'SitePress' ) && ! empty( $suffix ) && $currencyCode != $suffix ) {
									$output = woo_feed_get_wcml_price( $value['id'], $suffix, $output, '_' . $rules['value'] );
								}

								$output = $rules['prefix'] . $output . ' ' . $suffix;
							} else {
								if ( $output != '' ) {
									$output = $rules['prefix'] . $output . $rules['suffix'];
								}
							}
						}
					}

					if ( ! empty( $output ) ) {
						$output = stripslashes( $output );
					}
				} elseif ( $output == '0' ) {
					$output = '0';
				} else {
					$output = '';
				}

				$attr                            = trim( $attr );
				$this->products[ $key ][ $attr ] = stripslashes( $output );
			} else {

				if ( ! empty( $this->rules['default'][ $i ] ) ) {
					$output = str_replace( 'wf_pattern_', '', $rules['value'] );
					if ( ! empty( $output ) ) {
						// Format Output According to output type
						if ( ! empty( $rules['type'] ) ) {
							foreach ( $rules['type'] as $key22 => $value22 ) {
								if ( $value22 == 2 ) { // Strip Tags
									$output = wp_strip_all_tags( html_entity_decode( $output ) );
								} elseif ( $value22 == 3 ) { // UTF-8 Encode
									$output = utf8_encode( $output );
								} elseif ( $value22 == 4 ) { // htmlentities
									$output = htmlentities( $output, ENT_QUOTES, 'UTF-8' );
								} elseif ( $value22 == 5 ) { // Integer
									$output = absint( $output );
								} elseif ( $value22 == 6 ) { // Format Price
									$output = (float) $output;
									$output = number_format( $output, 2, '.', '' );
								} elseif ( $value22 == 7 ) { // Delete Space
									$output = trim( $output );
								} elseif ( $value22 == 8 ) { // Add CDATA
									$output = '<![CDATA[' . $output . ']]>';
								} elseif ( $value22 == 9 ) {
									$output = $this->stripInvalidXml( $output ); // Remove Invalid Character
								} elseif ( $value22 == 10 ) {
									// $output=$this->productClass->remove_short_codes($output); # Remove ShortCodes
								}
							}
						}

						// Format Output According to output limit
						if ( ! empty( $rules['limit'] ) && strpos( $output, '<![CDATA[' ) !== false ) {
							$output = str_replace( array( '<![CDATA[', ']]>' ), array( '', '' ), $output );
							// Limit is PHP Command from version 2
							$functions = $this->getPHPFunctions( $rules['limit'] );
							$output    = $this->outputFormatter( $output, $functions );
							$output    = '<![CDATA[' . $output . ']]>';
						} elseif ( ! empty( $rules['limit'] ) ) {
							// Limit is PHP Command from version 2
							$functions = $this->getPHPFunctions( $rules['limit'] );
							$output    = $this->outputFormatter( $output, $functions );
						}

						// Prefix and Suffix Assign
						if ( strpos( $output, '<![CDATA[' ) !== false ) {
							$output = str_replace( array( '<![CDATA[', ']]>' ), array( '', '' ), $output );
							if ( substr( $output, 0, 4 ) === 'http' ) {
								$output = $this->make_url_with_parameter( $rules['prefix'], $output, $rules['suffix'] );
							} else {
								if ( ( $rules['value'] == 'price' || $rules['value'] == 'sale_price' || $rules['value'] == 'current_price' || $rules['value'] == 'price_with_tax' ) && $output != '' ) {
									$suffix = ! empty( $rules['suffix'] ) ? trim( $rules['suffix'] ) : $rules['suffix'];

									// If WPML installed & base currency code not match then convert currency
									$currencyCode = strtoupper( strtolower( get_woocommerce_currency() ) );
									if ( class_exists( 'SitePress' ) && ! empty( $suffix ) && $currencyCode != $suffix ) {
										$output = woo_feed_get_wcml_price( $value['id'], $suffix, $output, '_' . $rules['value'] );
									}

									$output = $rules['prefix'] . $output . ' ' . $suffix;
								} else {
									if ( $output != '' ) {
										$output = $rules['prefix'] . $output . $rules['suffix'];
									}
								}
							}
							$output = '<![CDATA[' . $output . ']]>';
						} else {
							if ( substr( $output, 0, 4 ) === 'http' ) {
								$output = $this->make_url_with_parameter( $rules['prefix'], $output, $rules['suffix'] );
							} else {
								if ( ( $rules['value'] == 'price' || $rules['value'] == 'sale_price' || $rules['value'] == 'current_price' || $rules['value'] == 'price_with_tax' ) && $output != '' ) {
									$suffix = ! empty( $rules['suffix'] ) ? trim( $rules['suffix'] ) : $rules['suffix'];

									// If WPML installed & base currency code not match then convert currency
									$currencyCode = strtoupper( strtolower( get_woocommerce_currency() ) );
									if ( class_exists( 'SitePress' ) && ! empty( $suffix ) && $currencyCode != $suffix ) {
										$output = woo_feed_get_wcml_price( $value['id'], $suffix, $output, '_' . $rules['value'] );
									}
									$output = $rules['prefix'] . $output . ' ' . $suffix;
								} else {
									if ( $output != '' ) {
										$output = $rules['prefix'] . $output . $rules['suffix'];
									}
								}
							}
						}

						if ( ! empty( $output ) ) {
							$output = stripslashes( $output );
						}
					} elseif ( $output == '0' ) {
						$output = '0';
					} else {
						$output = '';
					}

					$attr                            = trim( $attr );
					$this->products[ $key ][ $attr ] = $output;
				} else {
					if ( str_replace( 'wf_pattern_', '', $rules['value'] ) == '0' ) {
						$output = 0;
					} else {
						$output = '';
					}
					$attr                            = trim( $attr );
					$this->products[ $key ][ $attr ] = stripslashes( $output );
				}
			}
			$i++;
		}
		$count++;
		return true;
	}

	/**
	 * Make proper URL using parameters
	 *
	 * @param $prefix
	 * @param $output
	 * @param $suffix
	 * @return string
	 */

	public function make_url_with_parameter( $prefix = '', $output = '', $suffix = '' ) {
		$getParam = explode( '?', $output );
		$URLParam = array();
		if ( isset( $getParam[1] ) ) {
			$URLParam = $this->proper_parse_str( $getParam[1] );
		}

		$EXTRAParam = array();
		if ( ! empty( $suffix ) ) {
			$suffix     = str_replace( '?', '', $suffix );
			$EXTRAParam = $this->proper_parse_str( $suffix );
		}

		$params = array_merge( $URLParam, $EXTRAParam );
		if ( ! empty( $params ) && $output != '' ) {
			$params  = http_build_query( $params );
			$baseURL = isset( $getParam ) ? $getParam[0] : $output;
			$output  = $prefix . $baseURL . '?' . $params;
		} else {
			if ( $output != '' ) {
				$output = $prefix . $output . $suffix;
			}
		}

		return $output;
	}

	/**
	 * Parse URL parameter
	 *
	 * @param $str
	 * @return array
	 */
	public function proper_parse_str( $str = '' ) {
		return woo_feed_parse_string( $str );
	}


	public function getPHPFunctions( $string ) {
		$functions = explode( ',', $string );
		$funArray  = array();
		if ( $functions ) {
			foreach ( $functions as $key => $value ) {
				if ( ! empty( $value ) ) {
					$funArray['formatter'][] = $this->get_string_between( $value, '[', ']' );
				}
			}
		}
		return $funArray;
	}


	/**
	 * Get Custom Attribute value
	 *
	 * @param $cattr_value
	 * @param $id
	 * @return mixed
	 */
	public function get_custom_attribute_value( $cattr_value, $id ) {
		return $this->productClass->getProductMeta( $id, $cattr_value );
	}

	/**
	 * Responsible to make XML feed header
	 *
	 * @return string
	 */
	public function get_xml_feed_header() {
		 $wrapper    = $this->rules['itemsWrapper'];
		$extraHeader = $this->rules['extraHeader'];

		$output  = '<?xml version="1.0" encoding="UTF-8" ?>
<' . $wrapper . '>';
		$output .= "\n";
		if ( ! empty( $extraHeader ) ) {
			$output .= $extraHeader;
			$output .= "\n";
		}

		return $output;
	}

	/**
	 * Responsible to make XML feed body
	 *
	 * @var array $items Product array
	 * @return string
	 */
	public function get_xml_feed_body() {
		$feed = '';

		$itemWrapper = $this->rules['itemWrapper'];

		if ( ! empty( $this->storeProducts ) ) {
			foreach ( $this->storeProducts as $each => $products ) {
				$feed .= '      <' . $itemWrapper . '>';
				foreach ( $products as $key => $value ) {
					if ( ! empty( $value ) ) {
						$feed .= $value;
					}
				}
				$feed .= "\n      </" . $itemWrapper . ">\n";
			}

			return $feed;
		}

		return false;
	}

	/**
	 * Responsible to make XML feed footer
	 *
	 * @return string
	 */
	public function get_xml_feed_footer() {
		 $wrapper = $this->rules['itemsWrapper'];
		$footer   = "  </$wrapper>";

		return $footer;
	}



	/**
	 * Responsible to make TXT feed
	 *
	 * @return string
	 */

	public function get_txt_feed() {
		if ( ! empty( $this->storeProducts ) ) {

			if ( $this->rules['delimiter'] == 'tab' ) {
				$delimiter = "\t";
			} else {
				$delimiter = $this->rules['delimiter'];
			}

			if ( ! empty( $this->rules['enclosure'] ) ) {
				$enclosure = $this->rules['enclosure'];
				if ( $enclosure == 'double' ) {
					$enclosure = '"';
				} elseif ( $enclosure == 'single' ) {
					$enclosure = "'";
				} else {
					$enclosure = '';
				}
			} else {
				$enclosure = '';
			}

			if ( ! empty( $this->storeProducts ) ) {
				if ( ! empty( $this->rules['extraHeader'] ) ) {
					$headers = explode( "\n", $this->rules['extraHeader'] );
					foreach ( $headers as $header ) {
						$header = trim( preg_replace( '/\s+/', ' ', $header ) );
						if ( ! empty( $header ) ) {
							$feed[] = explode( ',', $header );
						}
					}
				}
				$headers = array_keys( $this->storeProducts[ key( $this->storeProducts ) ] );
				$feed[]  = $headers;
				foreach ( $this->storeProducts as $no => $product ) {
					$row = array();
					foreach ( $headers as $key => $header ) {
						$row[] = isset( $product[ $header ] ) ? $this->processStringForTXT( $product[ $header ] ) : '';
					}
					$feed[] = $row;
				}
				$str = '';
				$i   = 1;
				foreach ( $feed as $fields ) {
					if ( $i == 1 ) {
						$this->txtFeedHeader = $enclosure . implode( "$enclosure$delimiter$enclosure", $fields ) . $enclosure . "\n";
					} else {
						$str .= $enclosure . implode( "$enclosure$delimiter$enclosure", $fields ) . $enclosure . "\n";
					}

					$i++;}
				return $str;
			}
		}

		return false;
	}

	/**
	 * Responsible to make CSV feed
	 *
	 * @return string
	 */
	public function get_csv_feed() {
		if ( ! empty( $this->storeProducts ) ) {
			$feed = array();
			if ( ! empty( $this->rules['extraHeader'] ) ) {
				$headers = explode( "\n", $this->rules['extraHeader'] );
				foreach ( $headers as $header ) {
					$header = trim( preg_replace( '/\s+/', ' ', $header ) );
					if ( ! empty( $header ) ) {
						$feed[] = explode( ',', $header );
					}
				}
			}
			$headers             = array_keys( $this->storeProducts[ key( $this->storeProducts ) ] );
			$this->csvFeedHeader = $headers;
			foreach ( $this->storeProducts as $no => $product ) {
				$row = array();
				foreach ( $headers as $key => $header ) {
					if ( $this->rules['provider'] == 'spartoo.fi' && $header == 'Parent / Child' ) {
						$_value = isset( $product[ $header ] ) ? $this->processStringForCSV( $product[ $header ] ) : '';
						if ( $_value == 'variation' ) {
							$row[] = 'child';
						} else {
							$row[] = 'parent';
						}
					} else {
						$row[] = isset( $product[ $header ] ) ? $this->processStringForCSV( $product[ $header ] ) : '';
					}
				}
				$feed[] = $row;
			}
			return $feed;
		}
		return false;
	}

	/**
	 * Process string for CSV
	 *
	 * @param $string
	 * @return mixed|string
	 */
	public function processStringForCSV( $string ) {
		if ( ! empty( $string ) ) {
			$string = str_replace( "\n", ' ', $string );
			$string = str_replace( "\r", ' ', $string );
			$string = trim( $string );
			$string = stripslashes( $string );
			return $string;
		} elseif ( $string == '0' ) {
			return '0';
		} else {
			return '';
		}
	}

	/**
	 * Process string for TXT
	 *
	 * @param $string
	 * @return mixed|string
	 */
	public function processStringForTXT( $string ) {
		if ( ! empty( $string ) ) {
			$string = html_entity_decode( $string, ENT_HTML401 | ENT_QUOTES ); // Convert any HTML entities

			if ( stristr( $string, '"' ) ) {
				$string = str_replace( '"', '""', $string );
			}
			$string = str_replace( "\n", ' ', $string );
			$string = str_replace( "\r", ' ', $string );
			$string = str_replace( "\t", ' ', $string );
			$string = trim( $string );
			$string = stripslashes( $string );

			return $string;
		} elseif ( $string == '0' ) {
			return '0';
		} else {
			return '';
		}
	}


	/**
	 * Get formatted variation product title
	 *
	 * @param $id
	 * @param $title
	 * @param $pkey
	 * @return bool|string
	 */
	public function get_formatted_variation_title( $id, $parentId, $title = '', $pkey ) {
		$getTitle = isset( $this->rules['ptitle_show'] ) && ! empty( $this->rules['ptitle_show'] ) ? explode( '|', $this->rules['ptitle_show'] ) : '';

		if ( ! empty( $getTitle ) && count( $getTitle ) ) {
			$str = '';

			// if(in_array('title',$getTitle)){
			// $key = array_search ('title', $getTitle);
			// unset($getTitle[$key]);
			// }

			// $str.= $title." ";

			foreach ( $getTitle as $akey => $attr ) {
				if ( ! empty( $attr ) ) {

					if ( $attr == 'title' ) {
						$str .= ' ' . $title;
					} else {
						$str .= ' ' . $this->get_attribute_value_by_type( trim( $attr ), $id, $parentId, $pkey );
					}
				}
			}

			$str = ! empty( $str ) ? trim( $str ) : '';

			return preg_replace( '!\s+!', ' ', $str );
		} else {
			return $title;
		}
	}


	/**
	 * Get any attribute value by type
	 *
	 * @param $attribute
	 * @param $id
	 * @return mixed|string
	 */
	public function get_attribute_value_by_type( $attribute, $id, $parentId, $key ) {

		if ( strpos( $attribute, Woo_Feed_Products::PRODUCT_ATTRIBUTE_PREFIX ) !== false ) {

			return $this->productClass->getProductAttribute( $id, $attribute );

		} elseif ( strpos( $attribute, Woo_Feed_Products::POST_META_PREFIX ) !== false ) {

			return $this->productClass->getProductMeta( $id, $attribute );

		} elseif ( strpos( $attribute, Woo_Feed_Products::PRODUCT_TAXONOMY_PREFIX ) !== false ) {

			return $this->productClass->getProductTaxonomy( $parentId, $attribute );

		} elseif ( strpos( $attribute, Woo_Feed_Products::PRODUCT_CATEGORY_MAPPING_PREFIX ) !== false ) {

			return $this->get_category_mapping_value( $attribute, $parentId );

		} elseif ( strpos( $attribute, Woo_Feed_Products::PRODUCT_DYNAMIC_ATTRIBUTE_PREFIX ) !== false ) {

			return $this->get_dynamic_attribute_value( $attribute, $this->storeProducts[ $key ] );

		} elseif ( strpos( $attribute, Woo_Feed_Products::WP_OPTION_PREFIX ) !== false ) {

			$optionName  = str_replace( Woo_Feed_Products::WP_OPTION_PREFIX, '', $attribute );
			$optionValue = get_option( $optionName );
			return $optionValue;

		} elseif ( array_key_exists( $attribute, $this->storeProducts[ $key ] ) ) {

			return $this->storeProducts[ $key ][ $attribute ];

		} else {

			return $attribute;

		}
	}

}
