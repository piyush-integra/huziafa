<?php /** @noinspection PhpUnusedPrivateMethodInspection, PhpUndefinedMethodInspection, PhpUnused, PhpUnusedPrivateFieldInspection, PhpUnusedLocalVariableInspection, DuplicatedCode, PhpUnusedParameterInspection, PhpForeachNestedOuterKeyValueVariablesConflictInspection, RegExpRedundantEscape */

/**
 * Created by PhpStorm.
 * User: wahid
 * Date: 12/9/19
 * Time: 12:42 PM
 */

class Woo_Feed_Custom_XML {
	/**
	 * This variable is responsible for holding all product attributes and their values
	 *
	 * @since   1.0.0
	 * @var     array $products Product IDs
	 * @access  public
	 */
	private $products;
	/**
	 * This variable is responsible for holding all product attributes and their values
	 *
	 * @since   1.0.0
	 * @var     Woo_Feed_Products_v3_Pro $products Contains all the product attributes to generate feed
	 * @access  public
	 */
	private $productEngine;

	/**
	 * This variable is responsible for holding feed configuration form values
	 *
	 * @since   1.0.0
	 * @var     array $rules Contains feed configuration form values
	 * @access  public
	 */
	private $config;

	private $feedHeader;
	/**
	 * This variable is responsible for holding feed string
	 *
	 * @since   1.0.0
	 * @var     string $feedString Contains feed information
	 * @access  public
	 */
	private $feedString;
	private $feedFooter;
	private $Elements;
	private $s = - 1;

	public function __construct( $feedRule ) {
		$this->feedHeader    = '';
		$this->feedString    = '';
		$this->feedFooter    = '';
		$this->config        = $feedRule;
		$this->productEngine = new Woo_Feed_Products_v3_Pro( $feedRule );

		// When update via cron job then set productIds
		if ( ! isset( $feedRule['productIds'] ) ) {
			$feedRule['productIds'] = $this->productEngine->query_products();
		}
		$this->products = $feedRule['productIds'];


		$xml            = $this->config['feed_config_custom2'];
		$this->Elements = $this->getXMLElements( $xml );
		// Make Header
		$this->make_xml_header( $xml );
		// Make Body
		$this->process_xml( $feedRule['productIds'] );
		// Make Footer
		$this->make_xml_footer( $xml );
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


	private function process_xml( $ids ) {


		// Get XML Elements from feed config
		$Elements = $this->Elements;

		if ( ! empty( $Elements ) && ! empty( $ids ) ) {
			foreach ( $ids as $key => $pid ) {
				$product = wc_get_product( $pid );

				// For WP_Query check available product types
				if ( 'wp' == $this->productEngine->get_query_type() ) {
					if ( ! in_array( $product->get_type(), $this->productEngine->get_product_types() ) ) {
						continue;
					}
				}

				// Skip for invalid products
				if ( ! is_object( $product ) ) {
					continue;
				}

				// Skip for invisible products
				if ( ! $product->is_visible() ) {
					continue;
				}

				if ( $product->is_type( 'variation' ) ) {
					if ( ! empty( $this->productEngine->get_not_ids_in() ) && in_array( $pid, $this->productEngine->get_not_ids_in() ) ) {
						continue;
					}

					if ( ! empty( $this->productEngine->get_ids_in() ) && ! in_array( $pid, $this->productEngine->get_ids_in() ) ) {
						continue;
					}

					if ( isset( $this->config['is_outOfStock'] ) && 'y' == $this->config['is_outOfStock'] ) {
						if ( $product->get_stock_status() == 'outofstock' ) {
							continue;
						}
					}
				}

				// Apply variable and variation settings
				if ( $product->is_type( 'variable' ) && $product->has_child() ) {
					// Include Product Variations if configured
					if ( 'y' == $this->config['is_variations'] || 'both' == $this->config['is_variations'] ) {
						$variations = $product->get_visible_children();

						if ( is_array( $variations ) && ( sizeof( $variations ) > 0 ) ) {
							$this->process_xml( $variations );
						}
					}

					// Skip variable products if only variations are configured
					if ( 'y' == $this->config['is_variations'] ) {
						continue;
					}
				}

				// Filter products by Condition
				if ( isset( $this->config['fattribute'] ) && count( $this->config['fattribute'] ) ) {
					if ( ! $this->productEngine->filter_product( $product ) ) {
						continue;
					}
				}

				$feedString = '';$p = false;
				// Start making XML Elements
				foreach ( $Elements as $each => $element ) {

					$start  = '';
					$end    = '';
					$output = '';

					// Start XML Element
					if (
						empty( $element['elementTextInfo'] ) && // Get the root element.
					    empty( $element['end'] ) &&
					    5 == count( $element )
					) {

						// Start XML Element
						$elementStart = stripslashes( $element['start'] );
						if ( ! empty( $element['start_code'] ) ) {
							$start_attr_codes = array();
							foreach ( $element['start_code'] as $attrKey => $attrValue ) {
								if ( strpos( $attrValue, 'return' ) !== false ) {
									$start_attr_code                                = woo_feed_get_string_between( $attrValue, '{(', ')}' );
									$tempAttribute                                  = array(
										'to_return' => $start_attr_code,
										'attr'      => $attrValue,
									);
									$start_attr_code                                = $this->getReturnTypeValue( $tempAttribute, $product );
									$start_attr_codes[ stripslashes( $attrValue ) ] = $start_attr_code;
								} else {
									$start_attr_code                = woo_feed_get_string_between( $attrValue, '{', '}' );
									$start_attr_code                = $this->getAttributeTypeAndValue( $start_attr_code, $product );
									$start_attr_codes[ $attrValue ] = $start_attr_code;
								}
							}
							$elementStart = str_replace( array_keys( $start_attr_codes ), array_values( $start_attr_codes ), $elementStart );
						}

						$end        .= '<' . $elementStart . '>';
						$feedString .= $end . "\n";
						$p = true;
					} elseif ( ! empty( $element['start'] ) ) {
						$elementStart = preg_replace( '/\\\\/', '', $element['start'] );
						if ( ! empty( $element['start_code'] ) ) {
							$start_attr_code = $this->getAttributeTypeAndValue( $element['start_code'], $product );
							$toReplace       = '{' . $element['start_code'] . '}';
							$elementStart    = str_replace( $toReplace, htmlspecialchars( $start_attr_code ), $elementStart );
						}

						$start .= '<' . $elementStart . '>';
					}

					// Make XML Element Text
					if ( ! empty( $element['elementTextInfo'] ) ) {
						if ( 'attribute' == $element['attr_type'] ) {
							$output = $this->getAttributeTypeAndValue( $element['attr_code'], $product );
						} elseif ( 'return' == $element['attr_type'] ) {
							$output = $this->getReturnTypeValue( $element, $product );
						} elseif ( 'php' == $element['attr_type'] ) {
							if ( isset( $element['to_return'] ) && ! empty( $element['to_return'] ) ) {
								$output = $this->returnPHPFunction( $element['to_return'] );
							}
						} elseif ( 'text' == $element['attr_type'] ) {
							$output = ( isset( $element['attr_value'] ) && ! empty( $element['attr_value'] ) ) ? $element['attr_value'] : '';
						}

						$pluginAttribute = null;
						if ( 'attribute' == $element['attr_type'] ) {
							$pluginAttribute = $element['attr_code'];
						}

						// Format output according to commands
						if ( array_key_exists( 'formatter', $element ) ) {
							$output = $this->productEngine->process_commands( $output, $element, $product, $pluginAttribute );
						}
						$p = false;
					}

					// End XML Element
					if ( '/' . $element['end'] == $element['start'] && empty( $element['elementTextInfo'] ) && 5 == count( $element ) ) {
						if ( ! empty( $element['end'] ) ) {
							$end .= '<' . $element['start'] . '>';
						}
						$feedString .= $end . "\n";
						$p = true;
					} else {
						if ( ! empty( $element['end'] ) ) {
							$end .= '</' . $element['end'] . ">\n";
						}                   
}
					
					if ( ! $p ) {
						// Add Prefix and Suffix
						$prefix = isset( $element['prefix'] ) ? preg_replace( '!\s+!', ' ', $element['prefix'] ) : '';
						$suffix = isset( $element['suffix'] ) ? preg_replace( '!\s+!', ' ', $element['suffix'] ) : '';
						$output = $this->productEngine->process_prefix_suffix( $output, $prefix, $suffix, isset( $element['attr_code'] ) ? $element['attr_code'] : '' );

						// Add CDATA if needed
						$cdata = $this->getCDATA( $element['include_cdata'], $output );

						if ( ! empty( $output ) ) {
							$elementOutput = $start . $cdata[0] . $output . $cdata[1] . $end;
						} else {
							$elementOutput = $start . $output . $end;
						}

						$feedString .= $elementOutput;
						$p = false;
					}
				}
				$this->feedString .= $feedString;
			}
		}
	}



	/** Get XML node information for each product
	 *
	 * @param $xml
	 *
	 * @return array
	 */
	public function getXMLElements( $xml ) {
		$xml = trim( preg_replace( '/\+/', '', $xml ) );
		// Get XML nodes for each products
		$getFeedBody = woo_feed_get_string_between( $xml, '{each product start}', '{each product end}' );
		// Explode each element by new line
		$getElements = explode( "\n", $getFeedBody );
		$Elements    = array();
		$i           = 1;
		if ( ! empty( $getElements ) ) {
			foreach ( $getElements as $key => $value ) {
				if ( ! empty( $value ) ) {
					// Get Element info
					$element = woo_feed_get_string_between( $value, '<', '>' );
					if ( empty( $element ) ) {
						continue;
					}

					// Get starting element
					$Elements[ $i ]['start'] = $element;
					// Get ending element
					$Elements[ $i ]['end'] = woo_feed_get_string_between( $value, '</', '>' );

					// Set CDATA status and remove CDATA
					$elementTextInfo                 = woo_feed_get_string_between( $value, '>', '</' );
					$Elements[ $i ]['include_cdata'] = 'no';
					if ( strpos( strtolower($elementTextInfo), 'cdata' ) !== false ) {
						$Elements[ $i ]['include_cdata'] = 'yes';
						$elementTextInfo                 = str_replace( array( '<![CDATA[', ']]>' ), array(
							'',
							'',
						), $elementTextInfo );
					}
					// Get Pattern of the xml node
					$Elements[ $i ]['elementTextInfo'] = $elementTextInfo;

					if ( ! empty( $Elements[ $i ]['elementTextInfo'] ) ) {
						// Get type of the attribute pattern
						if ( strpos( $elementTextInfo, '{' ) === false and strpos( $elementTextInfo, '}' ) === false ) {
							$Elements[ $i ]['attr_type']  = 'text';
							$Elements[ $i ]['attr_value'] = $elementTextInfo;
						} elseif ( strpos( $elementTextInfo, 'return' ) !== false ) {
							$Elements[ $i ]['attr_type'] = 'return';
							$return                      = woo_feed_get_string_between( $elementTextInfo, '{(', ')}' );
							$Elements[ $i ]['to_return'] = $return;
						} elseif ( strpos( $elementTextInfo, 'php ' ) !== false ) {
							$Elements[ $i ]['attr_type'] = 'php';
							$php                         = woo_feed_get_string_between( $elementTextInfo, '{(', ')}' );
							$Elements[ $i ]['to_return'] = str_replace( 'php', '', $php );
						} else {
							$Elements[ $i ]['attr_type'] = 'attribute';
							$attribute                   = woo_feed_get_string_between( $elementTextInfo, '{', '}' );
							$getAttrBaseFormat           = explode( ',', $attribute );
							$attrInfo                    = $getAttrBaseFormat[0];
							if ( count( $getAttrBaseFormat ) > 1 ) {
								$j = 0;
								foreach ( $getAttrBaseFormat as $_key => $_value ) {
									if ( ! empty( $value ) ) {
										$Elements[ $i ]['formatter'][ $j ] = woo_feed_get_string_between( $_value, '[', ']' );
										$j ++;
									}
								}
							}

							$getAttrCodes                = explode( '|', $attrInfo );
							$Elements[ $i ]['attr_code'] = $getAttrCodes[0];
							$Elements[ $i ]['id_type']   = isset( $getAttrCodes[1] ) ? $getAttrCodes[1] : '';
						}

						// Get prefix of the attribute node value
						$Elements[ $i ]['prefix'] = '';
						if ( '{' != substr( trim( $elementTextInfo ), 0, 1 ) && 'text' != $Elements[ $i ]['attr_type'] ) {
							$getPrefix                = explode( '{', $elementTextInfo );
							$Elements[ $i ]['prefix'] = ( count( $getPrefix ) > 1 ) ? $getPrefix[0] : '';
						}
						// Get suffix of the attribute node value
						$Elements[ $i ]['suffix'] = '';
						if ( '}' != substr( trim( $elementTextInfo ), 0, 1 ) && 'text' != $Elements[ $i ]['attr_type'] ) {
							$getSuffix                = explode( '}', $elementTextInfo );
							$Elements[ $i ]['suffix'] = ( count( $getSuffix ) > 1 ) ? $getSuffix[1] : '';
						}
					}


					// $Elements[$i]['start_code'] = woo_feed_get_string_between($node, '{', '}');
					preg_match_all( '/{(.*?)}/', $element, $matches );
					$Elements[ $i ]['start_code'] = ( isset( $matches[0] ) ? $matches[0] : '' );

					$i ++;
				}
			}
		}

		return $Elements;
	}

	/**
	 * Make feed Header
	 *
	 * @param $xml
	 */
	public function make_xml_header( $xml ) {
		$getHeader = explode( '{each product start}', $xml );
		$header    = $getHeader[0];
		$getNodes  = explode( "\n", $header );

		if ( ! empty( $getNodes ) ) {
			foreach ( $getNodes as $key => $value ) {
				// Add header info to feed file
				$value = preg_replace( '/\\\\/', '', $value );
				if ( strpos( $value, 'return' ) !== false ) {
					$return       = woo_feed_get_string_between( $value, '{(', ')}' );
					$return_value = $this->process_eval( $return );
					$value = preg_replace( '/\{\(.*?\)\}/', $return_value, $value );
				}
				$this->feedHeader .= $value;
				$this->s          = $this->s + 2;
			}
		} else {
			$this->feedHeader .= '<?xml version="1.0" encoding="utf-8" ?>' . "\n";
		}
	}

	/**
	 * Make Feed Footer
	 *
	 * @param $xml
	 */
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

	public function process_eval( $attribute ) {
		$return = preg_replace( '/\\\\/', '', $attribute );

		return eval( $return );
	}

	public function getReturnTypeValue( $attribute, $product ) {
		// echo "<pre>";
		$variables = array();
		if ( ! empty( $attribute ) && strpos( $attribute['to_return'], '$' ) !== false ) {
// $matches = str_word_count( $attribute['to_return'], 1, '$' );
			$pattern = '/\$\S+/';
			preg_match_all($pattern, $attribute['to_return'], $matches, PREG_SET_ORDER);
			$matches = array_column($matches, 0);
// print_r( $matches );
			foreach ( $matches as $key => $variable ) {
				if ( strpos( $variable, '$' ) !== false ) {
					$variable                             = str_replace( array( '$', ';' ), '', $variable );
					$attribute['attr_code']               = $variable;
					$variables[ $attribute['attr_code'] ] = $this->getAttributeTypeAndValue( $attribute['attr_code'], $product );
				}
			}
		}
		
		extract( $variables );
		$return = $attribute['to_return'];
		$return = preg_replace( '/\\\\/', '', $return );

		return eval( $return );
	}

	public function getAttributeTypeAndValue( $attribute, $product ) {

		return $this->productEngine->getAttributeValueByType( $product, $attribute );

	}

	/** Return the php function of the attribute
	 *
	 * @param $function
	 *
	 * @return mixed
	 */
	public function returnPHPFunction( $function ) {
		return $function;
	}

	/** Get CDATA status of a node
	 *
	 * @param string $status
	 * @param string $output
	 * TODO Dynamic Option
	 * @return array
	 */
	public function getCDATA( $status, $output ) {
		if ( 'yes' == $status ) {
			return array( '<![CDATA[', ']]>' );
		}
// elseif ( strpos( $output, "&" ) !== false ||
// substr( trim( $output ), 0, 4 ) == "http" ) {
// return array( "<![CDATA[", "]]>" );
// }
		else {
			return array( '', '' );
		}
	}
}
