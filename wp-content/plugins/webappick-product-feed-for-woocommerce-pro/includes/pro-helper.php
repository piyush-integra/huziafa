<?php /** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection PhpUnusedParameterInspection */
/**
 * Pro Helper Functions
 * @package WooFeed
 * @subpackage WooFeed_Helper_Functions
 * @version 1.0.0
 * @since WooFeed 3.3.0
 * @author KD <mhamudul.hk@gmail.com>
 * @copyright WebAppick
 */

if ( ! defined( 'ABSPATH' ) ) {
	die(); // Silence...
}
/** @define "WOO_FEED_PRO_ADMIN_PATH" "./../admin/" */ // phpcs:ignore

// Pages.
if ( ! function_exists( 'woo_feed_manage_attribute' ) ) {
	/**
	 * Dynamic Attribute
	 */
	function woo_feed_manage_attribute() {
		// Manage action for category mapping.
		if ( isset( $_GET['action'], $_GET['dattribute'] ) && 'edit-attribute' == $_GET['action'] ) {
			if ( count( $_POST ) && isset( $_POST['wfDAttributeCode'], $_POST['edit-attribute'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				check_admin_referer( 'woo-feed-dynamic-attribute' );
				$condition        = sanitize_text_field( $_POST['wfDAttributeCode'] );
				$dAttributeOption = 'wf_dattribute_' . $condition;
				if ( $dAttributeOption != $_GET['dattribute'] ) {
					delete_option( sanitize_text_field( $_GET['dattribute'] ) );
				}
				$_data = woo_feed_sanitize_form_fields( $_POST );
				$oldData = maybe_unserialize( get_option( $dAttributeOption, array() ) );

                # Delete product attribute drop-down cache
                delete_transient( '__woo_feed_cache_woo_feed_dropdown_product_attributes');

				if ( $oldData === $_data ) {
					update_option( 'wpf_message', esc_html__( 'Attribute Not Changed', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-manage-feed-attribute&wpf_message=warning' ) );
					die();
				}
				$update = update_option( $dAttributeOption, serialize( $_data ), false ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
				if ( $update ) {
					update_option( 'wpf_message', esc_html__( 'Attribute Updated Successfully', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-manage-feed-attribute&wpf_message=success' ) );
					die();
				} else {
					update_option( 'wpf_message', esc_html__( 'Failed To Updated Attribute', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-manage-feed-attribute&wpf_message=error' ) );
					die();
				}
			} else {
				require WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-dynamic-attribute.php';
			}
		} elseif ( isset( $_GET['action'] ) && 'add-attribute' == $_GET['action'] ) {
			check_admin_referer( 'woo-feed-dynamic-attribute' );
			if ( count( $_POST ) && isset( $_POST['wfDAttributeCode'], $_POST['add-attribute'] ) ) {
				$condition = sanitize_text_field( $_POST['wfDAttributeCode'] );
				$_data     = woo_feed_sanitize_form_fields( $_POST );

                # Delete product attribute drop-down cache
                delete_transient( '__woo_feed_cache_woo_feed_dropdown_product_attributes');

				if ( false !== get_option( 'wf_dattribute_' . $condition, false ) ) {
					update_option( 'wpf_message', esc_html__( 'Another attribute with the same name already exists.', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-manage-feed-attribute&wpf_message=warning' ) );
					die();
				}
				$update    = update_option( 'wf_dattribute_' . $condition, serialize( $_data ), false ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
				if ( $update ) {
					update_option( 'wpf_message', esc_html__( 'Attribute Added Successfully', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-manage-feed-attribute&wpf_message=success' ) );
					die();
				} else {
					update_option( 'wpf_message', esc_html__( 'Failed To Add Attribute', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-manage-feed-attribute&wpf_message=error' ) );
					die();
				}
			}
			require WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-dynamic-attribute.php';
		} else {
			require WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-dynamic-attribute-list.php';
		}
		
	}
}
if ( ! function_exists( 'woo_feed_manage_attribute_mapping' ) ) {
	/**
	 * Attribute Mapping
	 */
	function woo_feed_manage_attribute_mapping() {
		// page actions
		if ( isset( $_GET['action'] ) && ( 'edit-mapping' == $_GET['action'] || 'add-mapping' == $_GET['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			require WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-attribute-mapping.php';
		} else {
			require WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-attribute-mapping-list.php';
		}
    }
}
if ( ! function_exists( 'woo_feed_category_mapping' ) ) {
	/**
	 * Category Mapping
	 */
	function woo_feed_category_mapping() {
		// Manage action for category mapping.
		if ( isset( $_GET['action'], $_GET['cmapping'] ) && 'edit-mapping' == $_GET['action'] ) {
			if ( count( $_POST ) && isset( $_POST['mappingname'] ) && isset( $_POST['edit-mapping'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				check_admin_referer( 'category-mapping' );

				$mappingOption = sanitize_text_field( $_POST['mappingname'] );
				$mappingOption = 'wf_cmapping_' . sanitize_title( $mappingOption );
				$mappingData = woo_feed_array_sanitize( $_POST );
				$oldMapping = maybe_unserialize( get_option( $mappingOption, array() ) );

                # Delete product attribute drop-down cache
                delete_transient( '__woo_feed_cache_woo_feed_dropdown_product_attributes');

				if ( $oldMapping === $mappingData ) {
					update_option( 'wpf_message', esc_html__( 'Mapping Not Changed', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=warning' ) );
					die();
				}
				
				if ( update_option( $mappingOption, serialize( $mappingData ), false ) ) { // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
					update_option( 'wpf_message', esc_html__( 'Mapping Updated Successfully', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=success' ) );
					die();
				} else {
					update_option( 'wpf_message', esc_html__( 'Failed To Updated Mapping', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=error' ) );
					die();
				}
			}
			require WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-category-mapping.php';
		} elseif ( isset( $_GET['action'] ) && 'add-mapping' == $_GET['action'] ) {
			if ( count( $_POST ) && isset( $_POST['mappingname'] ) && isset( $_POST['add-mapping'] ) ) {
				check_admin_referer( 'category-mapping' );

				$mappingOption = 'wf_cmapping_' . sanitize_text_field( $_POST['mappingname'] );

                # Delete product attribute drop-down cache
                delete_transient( '__woo_feed_cache_woo_feed_dropdown_product_attributes');

				if ( false !== get_option( $mappingOption, false ) ) {
					update_option( 'wpf_message', esc_html__( 'Another category mapping exists with the same name.', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=warning' ) );
					die();
				}
				if ( update_option( $mappingOption, serialize( woo_feed_array_sanitize( $_POST ) ), false ) ) { // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
					update_option( 'wpf_message', esc_html__( 'Mapping Added Successfully', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=success' ) );
					die();
				} else {
					update_option( 'wpf_message', esc_html__( 'Failed To Add Mapping', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=error' ) );
					die();
				}
			}
			require WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-category-mapping.php';
		} else {
			require WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-category-mapping-list.php';
		}
	}
}
if ( ! function_exists( 'woo_feed_wp_options' ) ) {
	function woo_feed_wp_options() {
		if ( isset( $_GET['action'] ) && 'add-option' == $_GET['action'] ) {
			if ( count( $_POST ) && isset( $_POST['wpfp_option'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				check_admin_referer( 'woo-feed-add-option' );

				$options = get_option( 'wpfp_option', array() );
				$newOption         = sanitize_text_field( $_POST['wpfp_option'] );
				$id                = explode( '-', $newOption );
				if ( false !== array_search( $id[0], array_column( $options, 'option_id' ) ) ) { // found
					update_option( 'wpf_message', esc_html__( 'Option Already Added.', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-wp-options&wpf_message=error' ) );
					die();
				} else {
					$options[ $id[0] ] = array(
						'option_id'   => $id[0],
						'option_name' => 'wf_option_' . str_replace( $id[0] . '-', '', $newOption ),
					);
					update_option( 'wpfp_option', $options, false );
					update_option( 'wpf_message', esc_html__( 'Option Successfully Added.', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-wp-options&wpf_message=success' ) );
					die();
				}
			}
			require WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-add-option.php';
		} else {
			require WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-option-list.php';
		}
	}
}

// Admin Page Form Actions.
if ( ! function_exists( 'woo_feed_save_attribute_mapping' ) ) {
	
	/**
     * Handle Attribute Mapping Form Actions
	 * @return void
	 */
	function woo_feed_save_attribute_mapping() {
	    check_admin_referer( 'wf-attribute-mapping' );

		$slug     = false;
		$data     = [];
		$old_data = [];
		$is_update = false;
		if ( ! isset( $_POST['mapping_name'] ) || ( isset( $_POST['mapping_name'] ) && empty( $_POST['mapping_name'] ) ) ) {
	        wp_die( esc_html__( 'Missing Required Fields.', 'woo-feed' ), esc_html__( 'Invalid Request.', 'woo-feed' ), [ 'back_link' => true ] );
        } else {
			$data['name'] = sanitize_text_field( $_POST['mapping_name'] );
        }
		
		if ( ! isset( $_POST['value'] ) || ( isset( $_POST['value'] ) && ( empty( $_POST['value'] ) || ! is_array( $_POST['value'] )) ) ) {
			wp_die( esc_html__( 'Missing Required Fields.', 'woo-feed' ), esc_html__( 'Invalid Request.', 'woo-feed' ), [ 'back_link' => true ] );
		} else {
			foreach ( $_POST['value'] as $item ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				if ( ' ' === $item ) {
					$data['mapping'][] = $item;
				} elseif ( '' !== $item ) {
					$data['mapping'][] = sanitize_text_field( $item );
				}
			}
			$data['mapping'] = array_filter( $data['mapping'] );
		}
		
		if ( isset( $_POST['mapping_glue'] ) ) {
			$data['glue'] = sanitize_text_field( $_POST['mapping_glue'] );
		} else {
			$data['glue'] = '';
        }
		
		if (
            isset( $_POST['option_name'] ) &&
            ! empty( $_POST['option_name'] ) &&
            false !== strpos( $_POST['option_name'], Woo_Feed_Products_v3_Pro::PRODUCT_ATTRIBUTE_MAPPING_PREFIX ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        ) {
			$slug = sanitize_text_field( $_POST['option_name'] );
		} else {
			// generate unique one.
			$slug = woo_feed_unique_option_name( Woo_Feed_Products_v3_Pro::PRODUCT_ATTRIBUTE_MAPPING_PREFIX . sanitize_title( $data['name'] ) );
        }
		
		if ( $slug ) {
			$old_data = get_option( $slug );
			$is_update = true;
			if ( ! isset( $old_data['name'], $old_data['mapping'] ) ) {
				$old_data = [];
			}
        }
		
		if ( empty( $data ) ) {
			wp_die( esc_html__( 'Invalid Data Submitted.', 'woo-feed' ), esc_html__( 'Invalid Request.', 'woo-feed' ), [ 'back_link' => true ] );
        }
	    
	    // check for update
	    if ( $data !== $old_data ) {
		    $update = update_option( $slug, $data, false );
		    if ( $update ) {
			    $update = 'success';
			    $message = $is_update ? esc_html__( 'Attribute Mapping Data Updated.', 'woo-feed' ) : esc_html__( 'Attribute Mapping Data Saved.', 'woo-feed' );
			    update_option( 'wpf_message', $message, false );
		    } else {
			    $update = 'error';
			    update_option( 'wpf_message', esc_html__( 'Unable To Save Attribute Mapping Data.', 'woo-feed' ), false );
		    }
	    } else {
		    $update = 'warning';
		    update_option( 'wpf_message', esc_html__( 'Nothing Updated.', 'woo-feed' ), false );
	    }

        # Delete product attribute drop-down cache
        delete_transient( '__woo_feed_cache_woo_feed_dropdown_product_attributes');
	    
		wp_safe_redirect( admin_url( 'admin.php?page=webappick-manage-attribute-mapping&wpf_message=' . $update ) );
		die();
    }
}

// The Editor.
if ( ! function_exists( 'woo_feed_pro_parse_feed_rules' ) ) {
	/**
	 * Parse Feed Config/Rules to make sure that necessary array keys are exists
	 * this will reduce the uses of isset() checking
	 *
	 * @param array $rules
	 *
	 * @return array
	 */
	function woo_feed_pro_parse_feed_rules( $rules = [] ) {
		if ( isset( $rules['provider'] ) && in_array( $rules['provider'], woo_feed_get_custom2_merchant() ) ) {
			if ( ! isset( $rules['feed_config_custom2'] ) ) {
				$rules['feed_config_custom2'] = '';
			}
		}
		if ( isset( $rules['feed_config_custom2'] ) ) {
			$rules['feed_config_custom2'] = trim( preg_replace( '/\\\\/', '', $rules['feed_config_custom2'] ) );
		}
		$str_replace = [
			'subject' => '',
			'search'  => '',
			'replace' => '',
		];
		if ( ! isset( $rules['str_replace'] ) || empty( $rules['str_replace'] ) ) {
			$rules['str_replace'] = [ $str_replace ];
		} else {
			for ( $i = 0; $i < count( $rules['str_replace'] ); $i++ ) {
				$rules['str_replace'][ $i ] = wp_parse_args( $rules['str_replace'][ $i ], $str_replace );
			}
		}
		
		return $rules;
	}
}
if ( ! function_exists( 'woo_feed_pro_insert_feed_data_filter' ) ) {
	/**
	 * Filter Feed config before insert
	 *
	 * @since 3.3.3
	 *
	 * @param array $config
	 *
	 * @return array
	 */
    function woo_feed_pro_insert_feed_data_filter( $config ) {
	    if ( isset( $config['str_replace'] ) ) {
		    $config['str_replace'] = array_filter( $config['str_replace'], function( $item ) {
		    	return isset( $item['subject'], $item['search'], $item['replace'] ) && ! empty( $item['subject'] ) && ! empty( $item['search'] ) ? $item : null;
		    } );
		    $config['str_replace'] = array_filter( $config['str_replace'] );
		    $config['str_replace'] = array_values( $config['str_replace'] );
		    $config['str_replace'] = array_map( function ( $item ) {
			    $item['subject'] = wp_unslash( $item['subject'] );
			    $item['search']  = wp_unslash( $item['search'] );
			    $item['replace'] = wp_unslash( $item['replace'] );
			    return $item;
		    }, $config['str_replace'] );
	    }
        return $config;
    }
}
if ( ! function_exists( 'woo_feed_get_variable_visibility_options' ) ) {
	/**
	 * Get Variable visibility options for feed editor
	 * @return array
	 */
	function woo_feed_get_variable_visibility_options() {
		return apply_filters( 'woo_feed_variable_visibility_options',
			[
				'n'    => esc_html__( 'Only Variable Products', 'woo-feed' ),
				'y'    => esc_html__( 'Only Product Variations', 'woo-feed' ),
				'both' => esc_html__( 'Both Variable Products and Product Variations', 'woo-feed' ),
			] );
	}
}
if ( ! function_exists( 'woo_feed_get_variable_price_options' ) ) {
	/**
	 * Get Variable price options for feed editor
	 * @return array
	 */
	function woo_feed_get_variable_price_options() {
		return apply_filters( 'woo_feed_variable_price_options',
			[
				'first' => esc_html__( 'First Variation Price', 'woo-feed' ),
				'max'   => esc_html__( 'Max Variation Price', 'woo-feed' ),
				'min'   => esc_html__( 'Min Variation Price', 'woo-feed' ),
			] );
	}
}
if ( ! function_exists( 'woo_feed_get_variable_quantity_options' ) ) {
	/**
	 * Get Variable quantity options for feed editor
	 * @return array
	 */
	function woo_feed_get_variable_quantity_options() {
		return apply_filters( 'woo_feed_variable_quantity_options',
			[
				'first' => esc_html__( 'First Variation Quantity', 'woo-feed' ),
				'max'   => esc_html__( 'Max Variation Quantity', 'woo-feed' ),
				'min'   => esc_html__( 'Min Variation Quantity', 'woo-feed' ),
				'sum'   => esc_html__( 'Sum of Variation Quantity', 'woo-feed' ),
			] );
	}
}
if ( ! function_exists( 'woo_feed_get_post_statuses' ) ) {
	/**
	 * Get Product Statuses
	 * @return array
	 */
	function woo_feed_get_post_statuses() {
		return (array) apply_filters( 'woo_feed_product_statuses', get_post_statuses() );
	}
}
if ( ! function_exists( 'woo_feed_format_price' ) ) {
	/**
	 * Format Price with specified number format
	 *
	 * @param $price
	 * @param $options
	 *
	 * @return string
	 * @see wc_price()
	 */
	function woo_feed_format_price( $price, $options ) {
		// @TODO include currency and price_format.
		$options           = apply_filters( 'woo_feed_price_options',
			wp_parse_args( $options,
				[
					'decimal_separator'  => wc_get_price_decimal_separator(),
					'thousand_separator' => wc_get_price_thousand_separator(),
					'decimals'           => wc_get_price_decimals(),
				] ) );
		$unformatted_price = $price;
		$negative          = $price < 0;
		$price             = apply_filters( 'raw_woo_feed_price', floatval( $negative ? $price * - 1 : $price ) );
		$price             = apply_filters(
			'formatted_woo_feed_price',
			number_format(
				$price,
				$options['decimals'],
				$options['decimal_separator'],
				$options['thousand_separator']
			),
			$price,
			$options['decimals'],
			$options['decimal_separator'],
			$options['thousand_separator']
		);
		if ( apply_filters( 'woo_feed_price_trim_zeros', false ) && $options['decimals'] > 0 ) {
			$price = preg_replace( '/' . preg_quote( $options['decimal_separator'], '/' ) . '0++$/', '', $price );
		}
		
		$price = ( $negative ? '-' : '' ) . $price;
		
		/**
		 * Filters the string of price markup.
		 *
		 * @param string $price Formatted price.
		 * @param array $options Pass on the args.
		 * @param float $unformatted_price Price as float to allow plugins custom formatting. Since 3.2.0.
		 */
		return apply_filters( 'woo_feed_price', $price, $options, $unformatted_price );
	}
}
if ( ! function_exists( 'woo_feed_get_conditions' ) ) {
	function woo_feed_get_conditions() {
		$conditions = array(
			'=='        => __( 'is / equal', 'woo-feed' ),
			'!='        => __( 'is not / not equal', 'woo-feed' ),
			'>='        => __( 'equals or greater than', 'woo-feed' ),
			'>'         => __( 'greater than', 'woo-feed' ),
			'<='        => __( 'equals or less than', 'woo-feed' ),
			'<'         => __( 'less than', 'woo-feed' ),
			'contains'  => __( 'contains', 'woo-feed' ),
			'nContains' => __( 'does not contain', 'woo-feed' ),
			'between'   => __( 'between', 'woo-feed' ),
		);
		
		return $conditions;
	}
}

// Editor Tabs.
if ( ! function_exists( 'woo_feed_pro_filter_tabs' ) ) {
    function woo_feed_pro_filter_tabs( $tabs ) {
	    return array_splice_assoc( $tabs, 'filter', 'filter', array(
		    'advanced-filter' => array(
			    'label'    => __( 'Advanced Filter', 'woo-feed' ),
			    'callback' => 'render_advanced_filter_config',
		    ),
	    ) );
    }
}
if ( ! function_exists( 'render_advanced_filter_config' ) ) {
	/** @noinspection PhpUnusedParameterInspection */
	/**
	 * @param string $tabId
	 * @param array  $feedRules
	 * @param bool   $idEdit
	 */
	function render_advanced_filter_config( $tabId, $feedRules, $idEdit ) {
		global $provider, $wooFeedDropDown, $merchant;
		include WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-edit-advanced-filter.php';
	}
}

// File process.
if ( ! function_exists( 'woo_feed_duplicate_feed' ) ) {
	/**
	 * @param string $feed_from     Required. Feed name to duplicate from
	 * @param string $new_name      Optional. New name for duplicate feed.
	 *                              Default to auto generated slug from the old name prefixed with number.
	 * @param bool   $copy_file     Optional. Copy the file. Default is true.
	 *
	 * @return bool|WP_Error        WP_Error object on error, true on success.
	 */
    function woo_feed_duplicate_feed( $feed_from, $new_name = '', $copy_file = true ) {
        
        if ( empty( $feed_from ) ) {
	        return new WP_Error( 'invalid_feed_name_top_copy_from', esc_html__( 'Invalid Request.', 'woo-feed' ) );
        }
	    // normalize the option name.
	    $feed_from = woo_feed_extract_feed_option_name( $feed_from );
	    // get the feed data for duplicating.
	    $base_feed = maybe_unserialize( get_option( 'wf_feed_' . $feed_from, array() ) );
	    // validate the feed data.
	    if ( empty( $base_feed ) || ! is_array( $base_feed ) || ! isset( $base_feed['feedrules'] ) || ( isset( $base_feed['feedrules'] ) && empty( $base_feed['feedrules'] ) ) ) {
		    return new WP_Error( 'empty_base_feed', esc_html__( 'Feed data is empty. Can\'t duplicate feed.', 'woo-feed' ) );
	    }
	    $part = '';
	    if ( empty( $new_name ) ) {
		    // generate a unique slug for duplicate the feed.
		    $new_name = generate_unique_feed_file_name( $feed_from, $base_feed['feedrules']['feedType'], $base_feed['feedrules']['provider'] );
		    // example-2 or example-2-2-3
		    $part = ' ' . str_replace_trim( $feed_from . '-', '', $new_name ); // -2-2-3
	    } else {
		    $new_name = generate_unique_feed_file_name( $new_name, $base_feed['feedrules']['feedType'], $base_feed['feedrules']['provider'] );
	    }
	    // new name for the feed with numeric parts from the unique slug.
	    $base_feed['feedrules']['filename'] = $base_feed['feedrules']['filename'] . $part;
	    // copy feed config data.
	    $saved_feed = woo_feed_save_feed_config_data( $base_feed['feedrules'], $new_name, false );
	    if ( false === $saved_feed ) {
		    return new WP_Error( 'unable_to_save_the_duplicate', esc_html__( 'Unable to save the duplicate feed data.', 'woo-feed' ) );
	    }
	    
	    if ( true === $copy_file ) {
		    // copy the data file.
		    $original_file = woo_feed_get_file( $feed_from, $base_feed['feedrules']['provider'], $base_feed['feedrules']['feedType'] );
		    $new_file      = woo_feed_get_file( $new_name, $base_feed['feedrules']['provider'], $base_feed['feedrules']['feedType'] );
		    if ( copy( $original_file, $new_file ) ) {
			    return true;
		    } else {
			    return new WP_Error( 'unable_to_copy_file', esc_html__( 'Feed Successfully Duplicated, but unable to generate the data file. Please click the "Regenerate Button"', 'woo-feed' ) );
		    }
	    }
        return true;
    }
}

// Third Party
if ( ! function_exists( 'woo_feed_is_multi_vendor' ) ) {
	/**
	 * Check any multi vendor plugin installed or not
	 * Check if any of following multi vendor plugin class exists
	 * @link https://wedevs.com/dokan/
	 * @link https://www.wcvendors.com/
	 * @link https://yithemes.com/themes/plugins/yith-woocommerce-multi-vendor/
	 * @link https://wc-marketplace.com/
	 * @link https://wordpress.org/plugins/wc-multivendor-marketplace/
	 * @return bool
	 */
	function woo_feed_is_multi_vendor() {
		return apply_filters(
			'woo_feed_is_multi_vendor',
			(
				class_exists( 'WeDevs_Dokan' ) ||
				class_exists( 'WC_Vendors' ) ||
				class_exists( 'YITH_Vendor' ) ||
				class_exists( 'WCMp' ) ||
				class_exists( 'WCFMmp' )
			)
		);
	}
}
if ( ! function_exists( 'woo_feed_get_multi_vendor_user_role' ) ) {
	/**
	 * Get Sellers User Role Based On Multi Vendor Plugin
	 * @return string
	 */
	function woo_feed_get_multi_vendor_user_role() {
		$map         = [
			'WeDevs_Dokan' => 'seller',
			'WC_Vendors'   => 'vendor',
			'YITH_Vendor'  => 'yith_vendor',
			'WCMp'         => 'dc_vendor',
			'WCFMmp'       => 'wcfm_vendor',
		];
		$vendor_role = '';
		foreach ( $map as $class => $role ) {
			if ( class_exists( $class, false ) ) {
				$vendor_role = $role;
				break;
			}
		}
		
		/**
		 * Filter Vendor User Role
		 *
		 * @param string $vendor_role
		 *
		 * @since 3.4.0
		 */
		return apply_filters( 'woo_feed_multi_vendor_user_role', $vendor_role );
	}
}
if ( ! function_exists( 'woo_feed_has_composite_product_plugin' ) ) {
	function woo_feed_has_composite_product_plugin() {
		return (
			class_exists( 'WC_Product_Composite', false ) ||
			class_exists( 'WC_Product_Yith_Composite', false )
		);
	}
}

if ( ! function_exists( 'woo_feed_sanitize_custom_template2_config_field' ) ) {
	/**
	 * filter callback to allow un-sanitized data for custom template 2 config textarea
	 *
	 * @param bool   $status
	 * @param string $key
	 *
	 * @return bool
	 */
	function woo_feed_sanitize_custom_template2_config_field( $status, $key ) {
		if ( 'feed_config_custom2' === $key ) {
			return false;
		}
		
		return $status;
	}
	
	add_filter( 'woo_feed_sanitize_form_fields', 'woo_feed_sanitize_custom_template2_config_field', 10, 2 );
}

// Category mapping.
if ( ! function_exists( 'woo_feed_render_categories' ) ) {
	/**
	 * Get Product Categories
	 *
	 * @param int    $parent Parent ID.
	 * @param string $par separator.
	 * @param string $value mapped values.
	 */
	function woo_feed_render_categories( $parent = 0, $par = '', $value = '' ) {
		$categoryArgs = [
			'taxonomy'     => 'product_cat',
			'parent'       => $parent,
			'orderby'      => 'term_group',
			'show_count'   => 1,
			'pad_counts'   => 1,
			'hierarchical' => 1,
			'title_li'     => '',
			'hide_empty'   => 0,
		];
		$categories   = get_categories( $categoryArgs );
		if ( ! empty( $categories ) ) {
			if ( ! empty( $par ) ) {
				$par = $par . ' > ';
			}
			foreach ( $categories as $cat ) {
				$class = $parent ? "treegrid-parent-{$parent} category-mapping" : 'treegrid-parent category-mapping';
				?>
				<tr class="treegrid-1 ">
					<th>
						<label for="cat_mapping_<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $par . $cat->name ); ?></label>
					</th>
					<td><!--suppress HtmlUnknownAttribute -->
						<input id="cat_mapping_<?php echo esc_attr( $cat->term_id ); ?>"
						class="<?php echo esc_attr( $class ); ?> woo-feed-mapping-input"
						autocomplete="off"
						type="text"
						name="cmapping[<?php echo esc_attr( $cat->term_id ); ?>]"
						placeholder="<?php echo esc_attr( $par . $cat->name ); ?>"
						data-cat_id="<?php echo esc_attr( $cat->term_id ); ?>"
						value="<?php echo is_array( $value ) && isset( $value['cmapping'][ $cat->term_id ] ) ? esc_attr( $value['cmapping'][ $cat->term_id ] ) : ''; ?>"
						>
					</td>
				</tr>
				<?php
				// call and render the child category if any.
				woo_feed_render_categories( $cat->term_id, $par . $cat->name, $value );
			}
		}
	}
}

if ( ! function_exists( 'woo_feed_get_category_mapping_value' ) ) {
	/**
	 * Return Category Mapping Values by Parent Product Id
	 *
	 * @param string $cmappingName Category Mapping Name
	 * @param int    $parent Parent id of the product
	 *
	 * @return mixed
	 */
	function woo_feed_get_category_mapping_value( $cmappingName, $parent ) {
		$getValue = maybe_unserialize( get_option( $cmappingName ) );
		if ( ! isset( $getValue['cmapping'] ) ) {
			return '';
		}
		$cmapping   = is_array( $getValue['cmapping'] ) ? array_reverse( $getValue['cmapping'], true ) : $getValue['cmapping'];
		$categories = '';
		if ( get_the_terms( $parent, 'product_cat' ) ) {
			$categories = array_reverse( get_the_terms( $parent, 'product_cat' ) );
		}
		if ( ! empty( $categories ) && is_array( $categories ) && count( $categories ) ) {
			foreach ( $categories as $key => $category ) {
				if ( isset( $cmapping[ $category->term_id ] ) && ! empty( $cmapping[ $category->term_id ] ) ) {
					return $cmapping[ $category->term_id ];
				} else {
					return '';
				}
			}
		}
		
		return '';
	}
}

if ( ! function_exists( 'woo_feed_get_functions_from_command' ) ) {
	/**
	 * Get command function names from string
	 *
	 * @param string $string
	 *
	 * @return array
	 */
	function woo_feed_get_functions_from_command( $string ) {
		$functions = explode( ',', $string );
		$funArray  = array();
		if ( $functions ) {
			foreach ( $functions as $key => $value ) {
				if ( ! empty( $value ) ) {
					$funArray['formatter'][] = woo_feed_get_string_between( $value, '[', ']' );
				}
			}
		}
		
		return $funArray;
	}
}
if ( ! function_exists( 'woo_feed_get_string_between' ) ) {
	/**  Separate XML header footer and body from feed content
	 *
	 * @param string $string
	 * @param string $start
	 * @param string $end
	 *
	 * @return string
	 */
	function woo_feed_get_string_between( $string, $start, $end ) {
		$string = ' ' . $string;
		$ini    = strpos( $string, $start );
		if ( 0 == $ini ) {
			return '';
		}
		$ini += strlen( $start );
		$len = strpos( $string, $end, $ini ) - $ini;
		
		return substr( $string, $ini, $len );
	}
}
if ( ! function_exists( 'woo_feed_get_conversion_rate' ) ) {
	/**
	 * Base Currency Convert
	 *
	 * @param string $from
	 * @param string $to
	 * @param int    $retry
	 *
	 * @return bool|float
	 */
	function woo_feed_get_conversion_rate( $from, $to, $retry = 0 ) {
		$apiKey = get_option( 'woo_feed_currency_api_code', '' );
		if ( ! empty( $apiKey ) ) {
			// phpcs:disable
			// not in used, will upgrade to wp_safe_remote_get()
			$ch = curl_init( "http://free.currencyconverterapi.com/api/v5/convert?q=" . $from . "_" . $to . "&compact=y&apiKey=" . $apiKey );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_HEADER, false );
			curl_setopt( $ch, CURLOPT_NOBODY, false );
			$rate = curl_exec( $ch );
			curl_close( $ch );
			// phpcs:enable
			$code     = $from . '_' . $to;
			$rateInfo = json_decode( $rate, true );
			
			$value = $rateInfo[ $code ]['val'];
			
			if ( $rate ) {
				return (float) $value;
			} elseif ( $retry > 0 ) {
				return woo_feed_get_conversion_rate( $from, $to, -- $retry );
			}
		}
		
		return false;
	}
}
if ( ! function_exists( 'woo_feed_convert_currency' ) ) {
	/**
	 * Currency Convert
	 *
	 * @param int|float|double $amount
	 * @param string           $from
	 * @param string           $to
	 *
	 * @return float
	 */
	function woo_feed_convert_currency( $amount, $from, $to ) {
		$optionName = strtolower( $from ) . strtolower( $to );
		if ( ! get_option( $optionName ) || get_option( 'wf_convert_' . gmdate( 'Y-m-d' ) != gmdate( 'Y-m-d' ) ) ) {
			$converted = woo_feed_get_conversion_rate( $from, $to );
			update_option( 'wf_convert_' . gmdate( 'Y-m-d' ), gmdate( 'Y-m-d' ), false );
			update_option( $optionName, $converted, false );
			$newAmount = $amount * $converted;
			
			return round( $newAmount, 2 );
		} else {
			$rate = get_option( $optionName );
			
			return round( $amount * $rate, 2 );
		}
	}
}

// WPML Helper Functions.
if ( ! function_exists( 'wooFeed_is_multilingual' ) ) {
	/**
	 * Check if if site is multilingual
	 * @TODO add common language handler for all multilingual plugins
	 * @return bool
	 */
	function wooFeed_is_multilingual() {
		/**
		 * filter is multilingual enabled.
		 *
		 * @param bool $status multilingual status
		 */
		return apply_filters( 'woo_feed_is_multilingual', ( class_exists( 'SitePress', false ) ) );
	}
}
if ( ! function_exists( 'woo_feed_switch_language' ) ) {
	/**
	 * Switch Current language.
	 * Switches WPML's query language
	 *
	 * @param array|string $language_code The language code to switch to Or the feed config to get the language code
	 *                                    If set to null it restores the original language
	 *                                    If set to 'all' it will query content from all active languages
	 *                                    Defaults to null
	 * @param bool|string  $cookie_lang  Optionally also switch the cookie language
	 *                                   to the value given. default is true.
	 *
	 * @return void
	 * @global SitePress $sitepress
	 * @see SitePress::switch_lang()
	 * @see SitePress::get_current_language()
	 *
	 */
	function woo_feed_switch_language( $language_code, $cookie_lang = true ) {
		if ( is_array( $language_code ) && isset( $language_code['feedLanguage'] ) ) {
			$language_code = $language_code['feedLanguage'];
		}
		if ( ! empty( $language_code ) ) {
			if ( class_exists( 'SitePress', false ) ) {
				// WPML Switch Language.
				global $sitepress;
				if ( $sitepress->get_current_language() !== $language_code ) {
					$sitepress->switch_lang( $language_code, $cookie_lang );
				}
			}
		}
	}
}
if ( ! function_exists( 'woo_feed_restore_language' ) ) {
	/**
	 * Restore Default Language.
	 * Switches WPML's query language to site's default language
	 * @return void
	 * @see SitePress::get_default_language()
	 * @see woo_feed_switch_language()
	 * @global SitePress $sitepress
	 */
	function woo_feed_restore_language() {
		$language_code = '';
		if ( class_exists( 'SitePress', false ) ) {
			// WPML restore Language.
			global $sitepress;
			$language_code = $sitepress->get_default_language();
		}
		/**
		 * Filter to hijack Default Language code before restore
		 * @param string $language_code
		 */
		$language_code = apply_filters( 'woo_feed_restore_language', $language_code );
		if ( ! empty( $language_code ) ) {
			woo_feed_switch_language( $language_code );
		}
	}
}
if ( ! function_exists( 'woo_feed_wpml_get_original_post' ) ) {
	/**
	 * @see wpml_get_default_language()
	 * @see wpml_object_id_filter()
	 * @param int $element_id
	 *
	 * @return int:null
	 */
	function woo_feed_wpml_get_original_post_id( $element_id ) {
		$lang = apply_filters( 'wpml_default_language', '' );
		/**
		 * Get translation of specific language for element id.
		 *
		 * @param int         $elementId                    translated object id
		 * @param string      $element_type                 object type (post type). If set to 'any' wpml will try to detect the object type
		 * @param bool|false  $return_original_if_missing   return the original if missing.
		 * @param string|null $language_code                Language code to get the translation. If set to 'null', wpml will use current language.
		 */
		return apply_filters( 'wpml_object_id', $element_id, 'any', true, $lang );
	}
}
if ( ! function_exists( 'woo_feed_get_wcml_price' ) ) {
	/**
	 * Get price by product id and currency
	 *
	 * @param int    $productId     Product ID for price convert
	 * @param string $currency      currency to convert the price
	 * @param string $price         current/raw price
	 * @param string $type          Price type (_price , _regular_price or _sale_price)
	 *
	 * @return float               return current price if type is null
	 */
	function woo_feed_get_wcml_price( $productId, $currency, $price, $type = null ) {
		if ( class_exists( 'woocommerce_wpml' ) ) {
			$originalId = woo_feed_wpml_get_original_post_id( $productId );
			global $woocommerce_wpml;
			if ( get_post_meta( $originalId, '_wcml_custom_prices_status', true ) ) {
				$prices = $woocommerce_wpml->multi_currency->custom_prices->get_product_custom_prices( $originalId, $currency );
				if ( ! empty( $prices ) ) {
					if ( is_null( $type ) ) {
						return $prices;
					}
					if ( array_key_exists( $type, $prices ) ) {
						return $prices[ $type ];
					} else {
						return $prices['_price'];
					}
				}
			} else {
				$currencies = $woocommerce_wpml->multi_currency->currencies;
				if ( array_key_exists( $currency, $currencies ) ) {
					$price = ( (float) $price * (float) $currencies[ $currency ]['rate'] );
					$price = $woocommerce_wpml->multi_currency->prices->apply_rounding_rules( $price, $currency );
				}
			}
		}
		
		return (float) $price;
	}
}

// Hooks on feed generating process...
if ( ! function_exists( 'woo_feed_pro_apply_hooks_before_product_loop' ) ) {
	/**
	 * Apply Hooks Before Looping through ProductIds
	 *
	 * @param int[] $productIds
	 * @param array $feedConfig
	 */
	function woo_feed_pro_apply_hooks_before_product_loop( $productIds, $feedConfig ) {
		// RightPress dynamic pricing support.
		add_filter( 'rightpress_product_price_shop_change_prices_in_backend', '__return_true', 999 );
		add_filter( 'rightpress_product_price_shop_change_prices_before_cart_is_loaded', '__return_true', 999 );
		if ( isset( $feedConfig['outofstock_visibility'] ) && '1' == $feedConfig['outofstock_visibility'] ) {
			// just return false as wc expect the value should be 'yes' with eqeqeq (===) operator.
			add_filter( 'pre_option_woocommerce_hide_out_of_stock_items', '__return_false', 999 );
		}
	}
}
if ( ! function_exists( 'woo_feed_pro_remove_hooks_before_product_loop' ) ) {
	/**
	 * Remove Applied Hooks Looping through ProductIds
	 *
	 * @param int[] $productIds
	 * @param array $feedConfig the feed array.
	 *
	 * @see woo_feed_apply_hooks_before_product_loop
	 */
	function woo_feed_pro_remove_hooks_before_product_loop( $productIds, $feedConfig ) {
		remove_filter( 'rightpress_product_price_shop_change_prices_in_backend', '__return_true', 999 );
		remove_filter( 'rightpress_product_price_shop_change_prices_before_cart_is_loaded', '__return_true', 999 );
		if ( isset( $feedConfig['outofstock_visibility'] ) && '1' == $feedConfig['outofstock_visibility'] ) {
			remove_filter( 'pre_option_woocommerce_hide_out_of_stock_items', '__return_false', 999 );
		}
	}
}

//
// add_filter( 'woo_feed_get_custom_exchange_rate', 'get_custom_plugin_exchange_rate' );
// function get_custom_plugin_exchange_rate($feedname){
// According to documentation: https://currency-switcher.com/function/woocs-get_currencies/
// global $WOOCS;
// $currencies=$WOOCS->get_currencies();
//
// if ($feedname=='google_feed_USD') {
//
// if (isset($currencies['USD'])){
// return $currencies['USD']['rate']; // return exchange rate
// }
//
// }else if ($feedname=='google_feed_BRL') {
//
// if (isset($currencies['BRL'])){
// return $currencies['BRL']['rate'];
// }
//
// }else if ($feedname=='google_feed_AED') {
//
// if (isset($currencies['AED'])){
// return $currencies['AED']['rate'];
// }
//
// }else if ($feedname=='google_feed_JPY') {
//
// if (isset($currencies['JPY'])){
// return $currencies['JPY']['rate'];
// }
//
// }else if ($feedname=='google_feed_EUR') {
//
// if (isset($currencies['EUR'])){
// return $currencies['EUR']['rate'];
// }
//
// }
//
// return false;
//
// }

// Multi Vendor...
// @TODO Move hooks to hooks.php. 
if ( woo_feed_is_multi_vendor() ) {
	if ( ! function_exists( 'woo_feed_add_view_feeds_tab_menu' ) ) {
		/**
		 * Add View Feed Tabs to My Account
		 *
		 * @param $menu_links
		 *
		 * @return array
		 */
		function woo_feed_add_view_feeds_tab_menu( $menu_links ) {
			if ( woo_feed_is_multi_vendor() ) {
				$menu_links = array_slice( $menu_links, 0, 5, true ) + [ 'view-feeds' => 'Product Feed' ] + array_slice( $menu_links, 5, 1, true );
			}
			
			return $menu_links;
		}
		
		add_filter( 'woocommerce_account_menu_items', 'woo_feed_add_view_feeds_tab_menu' );
	}
	
	if ( ! function_exists( 'woo_feed_add_endpoint_for_view_feeds_menu' ) ) {
		function woo_feed_add_endpoint_for_view_feeds_menu() {
			add_rewrite_endpoint( 'view-feeds', EP_PAGES );
		}
		
		add_action( 'init', 'woo_feed_add_endpoint_for_view_feeds_menu' );
	}
	if ( ! function_exists( 'woo_feed_view_vendor_feeds_endpoint_add_content' ) ) {
		function woo_feed_view_vendor_feeds_endpoint_add_content() {
			$result = woo_feed_get_cached_data( 'woo_feed_get_feeds' );
			if ( false === $result ) {
				global $wpdb;
				$query  = $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name LIKE %s", 'wf_feed_%' );
				$result = $wpdb->get_results( $query, 'ARRAY_A' ); // phpcs:ignore
				woo_feed_set_cache_data( 'woo_feed_get_feeds', $result );
			}
			$user_id = get_current_user_id();
			?>
			<div>
				<table class="table table-responsive">
					<thead>
					<tr>
						<th style="width: 20%;"><?php esc_html_e( 'Feed Name', 'woo-feed' ); ?></th>
						<th><?php esc_html_e( 'Feed Link', 'woo-feed' ); ?></th>
						<th style="width: 30%;"><?php esc_html_e( 'Actions', 'woo-feed' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php if ( empty( $result ) || ! is_array( $result ) ) { ?>
						<tr><td colspan="3" style="text-align: center;"><?php esc_html_e( 'No Feed Available', 'woo-feed' ); ?></td></tr>
						<?php
					} else {
						foreach ( $result as $feed ) {
							$info = maybe_unserialize( get_option( $feed['option_name'] ) );
							if ( isset( $info['feedrules']['vendors'] ) ) {
								if ( in_array( $user_id, $info['feedrules']['vendors'] ) ) {
									$fileName   = $info['feedrules']['filename'];
									$fileURL    = $info['url'];
									?>
									<tr>
										<td><?php echo esc_html( $fileName ); ?></td>
										<td style="color: rgb(0, 135, 121); font-weight: bold;"><?php echo esc_html( $fileURL ); ?></td>
										<td><a href="<?php echo esc_url( $fileURL ); ?>" class="button button-primary" target="_blank"><?php esc_html_e( 'View/Download', 'woo-feed' ); ?></a></td>
									</tr>
									<?php
								}
							}
						}
					}
					?>
					</tbody>
				</table>
			</div>
			<?php
		}
		
		add_action( 'woocommerce_account_view-feeds_endpoint', 'woo_feed_view_vendor_feeds_endpoint_add_content' );
	}
	if ( ! function_exists( 'woo_feed_add_rewrite_rules_for_view_feeds_tab' ) ) {
		function woo_feed_add_rewrite_rules_for_view_feeds_tab( $wp_rewrite ) {
			$feed_rules = array(
				'my-account/?$' => 'index.php?account-page=true',
			);
			
			$wp_rewrite->rules = $wp_rewrite->rules + $feed_rules;
			
			return $wp_rewrite->rules;
		}
		
		add_filter( 'generate_rewrite_rules', 'woo_feed_add_rewrite_rules_for_view_feeds_tab' );
	}
}

// Deprecated...
if ( ! function_exists( 'woo_feed_convert_price_with_exRate' ) ) {
	/**
	 * Filter to convert currency
	 *
	 * @param $variables
	 *
	 * @return int
	 */
	function woo_feed_convert_price_with_exRate( $variables ) {
		
		$price    = $variables['price'];
		$filename = $variables['filename'];
// $exchange_rate = apply_filters("woo_feed_get_custom_exchange_rate",$filename);
// if ($exchange_rate && $exchange_rate > 0){
// return $exchange_rate*$price;
// }
		// @TODO remove this filter or use correctly...
		return ! empty( $price ) ? $price : '';
	}
	
	add_filter( 'woo_feed_convert_price', 'woo_feed_convert_price_with_exRate' );
}
// End of file pro-helper.php.
