<?php /** @noinspection PhpUnusedPrivateMethodInspection, PhpUndefinedMethodInspection, PhpUnused, PhpUnusedPrivateFieldInspection, PhpUnusedLocalVariableInspection, DuplicatedCode, PhpUnusedParameterInspection, PhpForeachNestedOuterKeyValueVariablesConflictInspection */

/**
 * Class Custom
 *
 * Responsible for processing and generating custom feed
 *
 * @since 1.0.0
 * @package Shopping
 *
 */
class Woo_Feed_Custom2 {
	/**
	 * This variable is responsible for holding all product attributes and their values
	 *
	 * @since   1.0.0
	 * @var     array $products Contains all the product attributes to generate feed
	 * @access  public
	 */
	public $products;
	
	/**
	 * This variable is responsible for holding feed configuration form values
	 *
	 * @since   1.0.0
	 * @var     array $rules Contains feed configuration form values
	 * @access  public
	 */
	public $rules;
	
	/**
	 * This variable is responsible for holding feed string
	 *
	 * @since   1.0.0
	 * @var     string $feedString Contains feed information
	 * @access  public
	 */
	public $feedHeader;
	public $feedString;
	public $feedFooter;
	private $engine;
	
	public $s = - 1;
	
	/**
	 * Define the core functionality to generate feed.
	 *
	 * Set the feed rules. Map products according to the rules and Check required attributes
	 * and their values according to merchant specification.
	 * @var array $feedRule Contain Feed Configuration
	 * @since    1.0.0
	 */
	public function __construct( $feedRule ) {
		$products = new Woo_Feed_Products();
		
		$this->feedHeader = '';
		$this->feedString = '';
		$this->feedFooter = '';
		
		$limit          = isset( $feedRule['Limit'] ) ? esc_html( $feedRule['Limit'] ) : '';
		$offset         = isset( $feedRule['Offset'] ) ? esc_html( $feedRule['Offset'] ) : '';
		$categories     = isset( $feedRule['categories'] ) ? $feedRule['categories'] : '';
		$this->products = $products->woo_feed_get_visible_product( $limit, $offset, $categories, $feedRule );
		$feedRule       = $products->feedRule;
		$this->engine   = new WF_Engine( $this->products, $feedRule );
		$this->rules    = $feedRule;
		$this->process_xml( $feedRule['feed_config_custom2'] );
	}
	
	
	public function make_xml_header( $xml ) {
		$getHeader = explode( '{each product start}', $xml );
		$header    = $getHeader[0];
		$getNodes  = explode( "\n", $header );
// $testNodes=$this->getXMLNodes($header);
		
		if ( ! empty( $getNodes ) ) {
			foreach ( $getNodes as $key => $value ) {
				// Add header info to feed file
				$value = preg_replace( '/\\\\/', '', $value );
				// $this->feedString.=str_pad($value,strlen($value)+$this->s," ",STR_PAD_LEFT);
				
				if ( strpos( $value, 'return' ) !== false ) {
					$return       = $this->engine->get_string_between( $value, '{(', ')}' );
					$return_value = $this->process_eval( $return );
					/** @noinspection RegExpRedundantEscape */
					$value = preg_replace( '/\{\(.*?\)\}/', $return_value, $value );
				}
				$this->feedHeader .= $value;
				$this->s          = $this->s + 2;
			}
		} else {
			$this->feedHeader .= '<?xml version="1.0" encoding="utf-8" ?>' . "\n";
		}
	}
	
	public function process_eval( $attribute ) {
		$return = preg_replace( '/\\\\/', '', $attribute );
		
		return eval( $return );
	}
	
	public function save_xml() {
		$upload_dir = wp_get_upload_dir();
		$base       = $upload_dir['basedir'];
		$fileName   = 'feed.xml';
		// Check If any products founds
		if ( ! empty( $this->feedString ) ) {
			// Save File
			$path = $base . '/woo-feed';
			$file = $path . '/' . $fileName;
			
			$save = new Woo_Feed_Savefile();
			
			return $save->saveFile( $path, $file, $this->feedString );
		} else {
			return false;
		}
	}
	
	public function make_xml_footer( $xml ) {
		$getFooter = explode( '{each product end}', $xml );
		$getNodes  = explode( "\n", $getFooter[1] );
		if ( ! empty( $getNodes ) ) {
			foreach ( $getNodes as $key => $value ) {
				$this->s = $this->s - 2;
				// Add header info to feed file
				$this->feedFooter .= $value;
			}
		}
	}
	
	
	public function process_xml( $xml ) {
		$this->make_xml_header( $xml );
		$XMLNodes = $this->getXMLNodes( $xml );
		
		$p = false;
		if ( ! empty( $XMLNodes ) && ! empty( $this->products ) ) {
			foreach ( $this->products as $key => $product ) {
				$feedString = '';
				
				// Start making XML node
				foreach ( $XMLNodes as $each => $attribute ) {
					
					$cdata  = $this->getCDATA( isset( $attribute['cdata'] ) ? $attribute['cdata'] : '' );
					$start  = '';
					$end    = '';
					$output = '';
					// Get the parent node
					if ( empty( $attribute['attr'] ) && empty( $attribute['end'] ) && count( $attribute ) == 4 ) {
						// Start XML Node
						$nodeStart = stripslashes( $attribute['start'] );
						if ( ! empty( $attribute['start_code'] ) ) {
							$start_attr_codes = array();
							foreach ( $attribute['start_code'] as $nodeAttrKey => $nodeAttrValue ) {
								if ( strpos( $nodeAttrValue, 'return' ) !== false ) {
									$start_attr_code                                    = $this->engine->get_string_between( $nodeAttrValue, '{(', ')}' );
									$tempAttribute                                      = array(
										'to_return' => $start_attr_code,
										'attr'      => $nodeAttrValue,
									);
									$start_attr_code                                    = $this->getReturnTypeValue( $tempAttribute, $product, $product['id'] );
									$start_attr_codes[ stripslashes( $nodeAttrValue ) ] = $start_attr_code;
								} else {
									$start_attr_code                    = $this->engine->get_string_between( $nodeAttrValue, '{', '}' );
									$start_attr_code                    = $this->getAttributeTypeAndValue( $start_attr_code, $this->products[ $key ] );
									$start_attr_codes[ $nodeAttrValue ] = $start_attr_code;
								}
							}
							$nodeStart = str_replace( array_keys( $start_attr_codes ),
								array_values( $start_attr_codes ),
								$nodeStart );
						}
						
						$end        .= '<' . $nodeStart . '>';
						$feedString .= $end . "\n";
						$this->s    = $this->s + 2;
						$p          = true;
						// echo "<pre>";print_r($feedString);die();
					} elseif ( ! empty( $attribute['start'] ) ) {
						$nodeStart = preg_replace( '/\\\\/', '', $attribute['start'] );
						
						if ( ! empty( $attribute['start_code'] ) ) {
							$start_attr_code = $this->getAttributeTypeAndValue( $attribute['start_code'],
								$this->products[ $key ] );
							$toReplace       = '{' . $attribute['start_code'] . '}';
							$nodeStart       = str_replace( $toReplace,
								htmlspecialchars( $start_attr_code ),
								$nodeStart );
						}
						
						$start .= '<' . $nodeStart . '>';
					}
					
					// Make XML Node Value
					if ( ! empty( $attribute['attr'] ) ) {
						if ( 'attribute' == $attribute['attr_type'] ) {
							if ( array_key_exists( $attribute['attr_code'], $this->products[ $key ] ) ) {
								if ( 'title' == $attribute['attr_code'] ) {
									
									if ( 'variation' == $this->products[ $key ]['type'] ) {
										$title = $this->products[ $key ]['parent_title'];
									} else {
										$title = $this->products[ $key ]['title'];
									}
									
									$output = $this->engine->get_formatted_variation_title( $this->products[ $key ]['id'],
										$this->products[ $key ]['item_group_id'],
										$title,
										$key );
									
								} else {
									$output = $this->products[ $key ][ $attribute['attr_code'] ];
								}
							} else {
								$output                                            = $this->getAttributeTypeAndValue( $attribute['attr_code'],
									$this->products[ $key ] );
								$this->products[ $key ][ $attribute['attr_code'] ] = $output;
							}
						} elseif ( 'return' == $attribute['attr_type'] ) {
							$output = $this->getReturnTypeValue( $attribute, $this->products[ $key ], $key );
						} elseif ( 'php' == $attribute['attr_type'] ) {
							if ( isset( $attribute['to_return'] ) && ! empty( $attribute['to_return'] ) ) {
								$output = $this->returnPHPFunction( $attribute['to_return'] );
							}
						} elseif ( 'text' == $attribute['attr_type'] ) {
							$output = ( isset( $attribute['attr_value'] ) && ! empty( $attribute['attr_value'] ) ) ? $attribute['attr_value'] : '';
						}
					}
					
					if ( $product['variation_type'] = 'child' && array_key_exists( 'id_type',
							$attribute ) && ! empty( $attribute['id_type'] ) ) {
						$type   = $attribute['id_type'];
						$parent = $this->searchProductInArray( $this->products,
							'id',
							$this->products[ $key ]['item_group_id'] );
						if ( ! empty( $parent ) ) {
							$parent = array_reduce( $parent, 'array_merge', array() );
						}
						
						if ( 'parent' == $type ) { // Will set parent attribute value if parent is not empty
							if ( ! empty( $parent ) && isset( $parent[ $attribute['attr_code'] ] ) && ! empty( $parent[ $attribute['attr_code'] ] ) ) {
								$output = $parent[ $attribute['attr_code'] ];
							}
						} elseif ( 'only_parent' == $type ) { // Will only set parent attribute value whatever the value is empty or not
							if ( ! empty( $parent ) && isset( $parent[ $attribute['attr_code'] ] ) ) {
								$output = $parent[ $attribute['attr_code'] ];
							}
						} elseif ( 'parent_if_empty' == $type ) { // Will set parent attribute value if child is empty
							if ( empty( $this->products[ $key ][ $attribute['attr_code'] ] ) ) {
								if ( ! empty( $parent ) && ! empty( $parent[ $attribute['attr_code'] ] ) ) {
									$output = $parent[ $attribute['attr_code'] ];
								}
							}
						}
					}
					
					if ( array_key_exists( 'formatter', $attribute ) ) {
						$output = $this->outputFormatter( $output, $attribute, $this->products[ $key ] );
					}
					
					if ( '/' . $attribute['end'] == $attribute['start'] && empty( $attribute['attr'] ) && 4 == count( $attribute ) ) {
						// End XML Node
						if ( ! empty( $attribute['end'] ) ) {
							$end .= '<' . $attribute['start'] . '>';
						}
						$p          = true;
						$feedString .= $end . "\n";
					} else {
						if ( ! empty( $attribute['end'] ) ) {
							$end .= '</' . $attribute['end'] . '>';
						}
					}
					if ( ! $p ) {
						$prefix = '';
						$suffix = '';
						if ( ! empty( $attribute['prefix'] ) ) {
							$prefix = $attribute['prefix'] . ' ';
							$prefix = preg_replace( '!\s+!', ' ', $prefix );
						}
						if ( ! empty( $attribute['suffix'] ) ) {
							// No space if url
							if ( substr( $output, 0, 4 ) == 'http' ) {
								$suffix = $attribute['suffix'];
							} else {
								$suffix = $attribute['suffix'];
								$suffix = preg_replace( '!\s+!', ' ', $suffix );
							}
						}
						if ( ! empty( $output ) ) {
							$node = $start . $cdata[0] . $prefix . $output . $suffix . $cdata[1] . $end;
						} else {
							$node = $start . $output . $end;
						}
						$feedString .= $node . "\n";
						if ( isset( $attribute['attr_code'] ) ) {
							$this->products[ $key ][ $attribute['attr_code'] ] = htmlspecialchars( $output );
						}
					}
					$p = false;
				}
				$engine = new WF_Engine( $this->products, $this->rules );
				// Filter products by condition
				if ( isset( $this->rules['fattribute'] ) && count( $this->rules['fattribute'] ) ) {
					if ( ! $engine->filter_product( $product ) ) {
						$feedString = '';
						continue;
					} else {
						$this->feedString .= $feedString;
					}
				} else {
					$this->feedString .= $feedString;
				}
			}
		}
		
		$this->make_xml_footer( $xml );
		
		return $this->feedString;
	}
	
	
	public function searchProductInArray( $products, $key, $value ) {
		$results = array();
		if ( is_array( $products ) ) {
			if ( isset( $products[ $key ] ) && $products[ $key ] == $value ) {
				$results[] = $products;
			}
			
			foreach ( $products as $subarray ) {
				$results = array_merge( $results, $this->searchProductInArray( $subarray, $key, $value ) );
			}
		}
		
		return $results;
	}
	
	
	public function getReturnTypeValue( $attribute, $product, $pid ) {
		// echo $attribute;
		if ( ! empty( $attribute ) && strpos( $attribute['to_return'], '$' ) !== false ) {
			$pattern = '/\$\S+/';
			preg_match_all( $pattern, $attribute['attr'], $matches, PREG_SET_ORDER );
			$matches = array_column( $matches, 0 );
			foreach ( $matches as $key => $value ) {
				$value = str_replace( '$', '', $value );
				if ( ! array_key_exists( trim( $value ), $product ) ) {
					$attribute['attr_code']                            = $value;
					$product[ $value ]                                 = $this->getAttributeTypeAndValue( $attribute['attr_code'],
						$product );
					$this->products[ $pid ][ $attribute['attr_code'] ] = $product[ $value ];
				}
			}
		}
		
		extract( $product );
		$return = $attribute['to_return'];
		$return = preg_replace( '/\\\\/', '', $return );
		
		return eval( $return );
	}
	
	public function outputFormatter( $output, $attribute, $product ) {
		if ( ! empty( $attribute['formatter'] ) ) {
			$output = trim( $output );
			$engine = new WF_Engine( $this->products, $this->rules );
			foreach ( $attribute['formatter'] as $key => $value ) {
				if ( ! empty( $value ) && ! empty( $output ) ) {
					if ( strpos( $value, 'substr' ) !== false ) {
						$args   = preg_split( '/\s+/', $value );
						$output = substr( $output, $args[1], $args[2] );
					} elseif ( strpos( $value, 'strip_tags' ) !== false ) {
						$output = wp_strip_all_tags( html_entity_decode( $output ) );
					} elseif ( strpos( $value, 'htmlentities' ) !== false ) {
						$output = htmlentities( $output );
					} elseif ( strpos( $value, 'htmlspecialchars' ) !== false ) {
						$output = htmlspecialchars( $output );
					} elseif ( strpos( $value, 'clear' ) !== false ) {
						$output = $engine->stripInvalidXml( $output );
					} elseif ( strpos( $value, 'ucwords' ) !== false ) {
						$output = ucwords( strtolower( $output ) );
					} elseif ( strpos( $value, 'ucfirst' ) !== false ) {
						$output = ucfirst( strtolower( $output ) );
					} elseif ( strpos( $value, 'strtoupper' ) !== false ) {
						$output = strtoupper( strtolower( $output ) );
					} elseif ( strpos( $value, 'strtolower' ) !== false ) {
						$output = strtolower( $output );
					} elseif ( strpos( $value, 'wpml_price' ) !== false ) {
						$output = woo_feed_get_wcml_price( $product['id'],
							trim( $attribute['suffix'] ),
							$output,
							'_' . $attribute['attr_code'] );
					} elseif ( strpos( $value, 'convert' ) !== false ) {
						$args   = preg_split( '/\s+/', $value );
						$output = $engine->convertCurrency( $output, $args[1], $args[2] );
					} elseif ( strpos( $value, 'number_format' ) !== false ) {
						// $args=preg_split('/\s+/', $value);
						$args = explode( ' ', $value, 3 );
						if ( isset( $args[1] ) && isset( $args[2] ) ) {
							$output = number_format( $output, $args[1], $args[2] );
						} elseif ( isset( $args[1] ) ) {
							$output = number_format( $output, $args[1] );
						} else {
							$output = number_format( $output );
						}
					} elseif ( strpos( strtolower( $value ), 'urltounsecure' ) !== false ) {
						if ( 'http' == substr( $output, 0, 4 ) ) {
							$output = str_replace( 'https://', 'http://', $output );
						}
					} elseif ( strpos( strtolower( $value ), 'urltosecure' ) !== false ) {
						if ( 'http' == substr( $output, 0, 4 ) ) {
							$output = str_replace( 'http://', 'https://', $output );
						}
					} elseif ( strpos( $value, 'str_replace' ) !== false ) {
						// $args=preg_split('/\s+/', $value);
						$args = explode( '>', $value, 3 );
						if ( array_key_exists( 1, $args ) && array_key_exists( 2, $args ) ) {
							$param1 = ! empty( $args[1] ) ? trim( $args[1] ) : ' ';
							$param2 = ! empty( $args[2] ) ? trim( $args[2] ) : ' ';
							$output = str_replace( $param1, $param2, $output );
						}
					} elseif ( false !== strpos( $value, 'strip_shortcodes' ) ) {
						// $output=preg_replace("/\[[^]]+\]/",'',$output);
						$output = $engine->remove_short_codes( $output );
					} elseif ( false !== strpos( $value, 'preg_replace' ) ) {
						$args = explode( ' ', $value, 3 );
						if ( ! isset( $args[2] ) ) {
							$args[2] = '';
						}
						$output = preg_replace( $args[1], $args[2], $output );
					}
				}
			}
			
			return $output;
		}
		
		return false;
	}
	
	public function getAttributeTypeAndValue( $attrCode, $product ) {
		$engine = new WF_Engine( $this->products, $this->rules );
		
		if ( array_key_exists( $attrCode, $product ) ) {
			return $product[ $attrCode ];
		} elseif ( strpos( $attrCode, 'wf_cmapping_' ) !== false ) {
			return $engine->get_category_mapping_value( $attrCode, $product['item_group_id'] );
		} elseif ( strpos( $attrCode, 'wf_option_' ) !== false ) {
			return get_option( str_replace( 'wf_option_', '', $attrCode ) );
		} elseif ( strpos( $attrCode, 'wf_dattribute_' ) !== false ) {
			return $engine->get_dynamic_attribute_value( $attrCode, $product );
		} elseif ( strpos( $attrCode, 'wf_attr_' ) !== false ) {
			return implode( ',',
				wc_get_product_terms( $product['id'],
					str_replace( 'wf_attr_', '', $attrCode ),
					array( 'fields' => 'names' ) ) );
		} elseif ( strpos( $attrCode, 'wf_cattr_' ) !== false ) {
			return $engine->get_custom_attribute_value( $attrCode, $product['id'] );
		} elseif ( strpos( $attrCode, 'wf_taxo_' ) !== false ) {
			$productClass = new Woo_Feed_Products();
			
			return $productClass->get_product_term_list( $product['id'], str_replace( 'wf_taxo_', '', $attrCode ) );
		} else {
			return '';
		}
	}
	
	/** Return the php function of the attribute
	 *
	 * @param $function
	 *
	 * @return mixed
	 */
	public function returnPHPFunction( $function ) {
		// $function = eval("return $function");
		return $function;
	}
	
	/** Get XML node information for each product
	 *
	 * @param $xml
	 *
	 * @return array
	 */
	public function getXMLNodes( $xml ) {
		$xml = trim( preg_replace( '/\+/', '', $xml ) );
		// Get XML nodes for each products
		$getFeedBody = $this->engine->get_string_between( $xml, '{each product start}', '{each product end}' );
		// Explode each node by new line
		$getNodes = explode( "\n", $getFeedBody );
		$XMLNodes = array();
		$i        = 1;
		if ( ! empty( $getNodes ) ) {
			foreach ( $getNodes as $key => $value ) {
				if ( ! empty( $value ) ) {
					// Check Node
					$node = $this->engine->get_string_between( $value, '<', '>' );
					if ( empty( $node ) ) {
						continue;
					}
					
					// Get node starting
					$XMLNodes[ $i ]['start'] = $node;
					
					
					// Get node ending
					$XMLNodes[ $i ]['end'] = $this->engine->get_string_between( $value, '</', '>' );
					// Set CDATA status and remove CDATA
					$attr = $this->engine->get_string_between( $value, '>', '</' );
					if ( strpos( $attr, 'CDATA' ) !== false ) {
						$XMLNodes[ $i ]['cdata'] = 'yes';
						$attr                    = str_replace( array( '<![CDATA[', ']]>' ), array( '', '' ), $attr );
					}
					// Get Pattern of the xml node
					$XMLNodes[ $i ]['attr'] = $attr;
					
					if ( ! empty( $XMLNodes[ $i ]['attr'] ) ) {
						// Get type of the attribute pattern
						if ( strpos( $attr, '{' ) === false and strpos( $attr, '}' ) === false ) {
							$XMLNodes[ $i ]['attr_type']  = 'text';
							$XMLNodes[ $i ]['attr_value'] = $attr;
						} elseif ( strpos( $attr, 'return' ) !== false ) {
							$XMLNodes[ $i ]['attr_type'] = 'return';
							$return                      = $this->engine->get_string_between( $attr, '{(', ')}' );
							$XMLNodes[ $i ]['to_return'] = $return;
						} elseif ( false !== strpos( $attr, 'php ' ) ) {
							$XMLNodes[ $i ]['attr_type'] = 'php';
							$php                         = $this->engine->get_string_between( $attr, '{(', ')}' );
							$XMLNodes[ $i ]['to_return'] = str_replace( 'php', '', $php );
						} else {
							$XMLNodes[ $i ]['attr_type'] = 'attribute';
							$attribute                   = $this->engine->get_string_between( $attr, '{', '}' );
							$getAttrBaseFormat           = explode( ',', $attribute );
							$attrInfo                    = $getAttrBaseFormat[0];
							if ( count( $getAttrBaseFormat ) > 1 ) {
								$j = 0;
								foreach ( $getAttrBaseFormat as $key => $value ) {
									if ( ! empty( $value ) ) {
										$XMLNodes[ $i ]['formatter'][ $j ] = $this->engine->get_string_between( $value, '[', ']' );
										$j ++;
									}
								}
							}
							
							$getAttrCodes                = explode( '|', $attrInfo );
							$XMLNodes[ $i ]['attr_code'] = $getAttrCodes[0];
							$XMLNodes[ $i ]['id_type']   = isset( $getAttrCodes[1] ) ? $getAttrCodes[1] : '';
						}
						
						// Get prefix of the attribute node value
						$XMLNodes[ $i ]['prefix'] = '';
						if ( '{' != substr( trim( $attr ), 0, 1 ) && 'text' != $XMLNodes[ $i ]['attr_type'] ) {
							$getPrefix                = explode( '{', $attr );
							$XMLNodes[ $i ]['prefix'] = ( count( $getPrefix ) > 1 ) ? $getPrefix[0] : '';
						}
						// Get suffix of the attribute node value
						$XMLNodes[ $i ]['suffix'] = '';
						if ( '}' != substr( trim( $attr ), 0, 1 ) && 'text' != $XMLNodes[ $i ]['attr_type'] ) {
							$getSuffix                = explode( '}', $attr );
							$XMLNodes[ $i ]['suffix'] = ( count( $getSuffix ) > 1 ) ? $getSuffix[1] : '';
						}
					}
					
					
					// $XMLNodes[$i]['start_code'] = $this->engine->get_string_between($node, '{', '}');
					preg_match_all( '/{(.*?)}/', $node, $matches );
					$XMLNodes[ $i ]['start_code'] = ( isset( $matches[0] ) ? $matches[0] : '' );
					
					$i ++;
				}
			}
		}
		
		return $XMLNodes;
	}
	
	/** Get CDATA status of a node
	 *
	 * @param string $status
	 *
	 * @return array
	 */
	public function getCDATA( $status = '' ) {
		if ( 'yes' == $status ) {
			return array( '<![CDATA[', ']]>' );
		} else {
			return array( '', '' );
		}
	}
	
	/** Get formatted variation product title
	 *
	 * @param $attribute
	 * @param $product
	 * @param string    $title
	 *
	 * @return mixed|string
	 */
	public function get_formatted_variation_title( $attribute, $product, $title = '' ) {
		$getTitle = isset( $this->rules['ptitle_show'] ) && ! empty( $this->rules['ptitle_show'] ) ? explode( '|',
			$this->rules['ptitle_show'] ) : '';
		
		if ( ! empty( $getTitle ) && count( $getTitle ) ) {
			$str = '';
			if ( ! in_array( 'title', $getTitle ) ) {
				$str .= $title . ' ';
			}
			foreach ( $getTitle as $key => $attr ) {
				if ( ! empty( $attr ) ) {
					$value = $this->getAttributeTypeAndValue( $attr, $product );
					$str   .= ' ' . $value;
				}
			}
			
			return preg_replace( '!\s+!', ' ', $str );
		} else {
			return $title;
		}
	}
	
	/**
	 * Return Feed
	 *
	 * @return array|bool|string
	 */
	public function returnFinalProduct() {
		$feed = array(
			'header' => $this->feedHeader,
			'body'   => $this->feedString,
			'footer' => $this->feedFooter,
		);
		
		return $feed;
	}
}
