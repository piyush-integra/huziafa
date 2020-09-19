<?php
/**
 * The file that defines the merchants attributes dropdown
 *
 * A class definition that includes attributes dropdown and functions used across the admin area.
 *
 * @link       https://webappick.com/
 * @since      1.0.0
 *
 * @package    Woo_Feed
 * @subpackage Woo_Feed/includes
 * @author     Ohidul Islam <wahid@webappick.com>
 */

class Woo_Feed_Dropdown_Pro extends Woo_Feed_Dropdown {

	public $cats = array();
	public $output_types = array(
		'1'  => 'Default',
		'2'  => 'Strip Tags',
		'3'  => 'UTF-8 Encode',
		'4'  => 'htmlentities',
		'5'  => 'Integer',
		'6'  => 'Price',
		'7'  => 'Remove Space',
		'10' => 'Remove ShortCodes',
		'9'  => 'Remove Special Character',
		'8'  => 'CDATA',
		'11' => 'ucwords',
		'12' => 'ucfirst',
		'13' => 'strtoupper',
		'14' => 'strtolower',
		'15' => 'urlToSecure',
		'16' => 'urlToUnsecure',
		'17' => 'only_parent',
		'18' => 'parent',
		'19' => 'parent_if_empty',
	);

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Dropdown of Merchant List
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function merchantsDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'merchantsDropdown', $selected );
		if ( empty( $options ) ) {
			$attributes = new Woo_Feed_Merchant_Pro();
			$options    = $this->cache_dropdown( 'merchantsDropdown', $attributes->merchants(), $selected );
		}

		return $options;
	}

	/**
	 * Get Active Languages for current site.
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function getActiveLanguages( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'getActiveLanguages', $selected );
		if ( false === $options ) {
			if ( class_exists( 'SitePress' ) ) {
				$get_languages = apply_filters( 'wpml_active_languages', null, 'orderby=id&order=desc' );
				if ( ! empty( $get_languages ) ) {
					$languages = [];
					foreach ( $get_languages as $key => $language ) {
						$languages[ $key ] = $language['translated_name'];
					}
					$options = $this->cache_dropdown( 'getActiveLanguages', $languages, $selected, __( 'Select Language', 'woo-feed' ) );
				}
			}
		}

		return $options;
	}

	public function getActiveCurrencies( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'getActiveCurrencies', $selected );
		if ( false === $options ) {
			if ( class_exists( 'SitePress' ) && class_exists( 'woocommerce_wpml' ) ) {
				global $woocommerce_wpml;
				$get_currencies = isset( $woocommerce_wpml->multi_currency->currencies ) ? $woocommerce_wpml->multi_currency->currencies : [];
				if ( ! empty( $get_currencies ) ) {
					$currencies = [];
					foreach ( $get_currencies as $key => $currency ) {
						$currencies[ $key ] = $key;
					}
					$options = $this->cache_dropdown( 'getActiveCurrencies', $currencies, $selected, __( 'Select Currency', 'woo-feed' ) );
				}
			}
		}

		return $options;
	}

	/**
	 * Get WP Option Table Item List
	 *
	 * @param string $selected
	 */
	public function woo_feed_get_wp_options( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'woo_feed_dropdown_wp_options', $selected );
		if ( false === $options ) {
			global $wpdb;
			$default_exclude_keys = array(
				'db_version',
				'cron',
				'wpfp_option',
				'recovery_keys',
				'wf_schedule',
				'woo_feed_output_type_options',
				'ftp_credentials',
			);

			/**
			 * Exclude Option Names from dropdown
			 *
			 * @param array $exclude Option Names to exclude.
			 * @param array $default_exclude_keys Option Names by default.
			 *
			 */
			$user_exclude = apply_filters( 'woo_feed_dropdown_exclude_option_names', null, $default_exclude_keys );

			if ( is_array( $user_exclude ) && ! empty( $user_exclude ) ) {
				$user_exclude         = esc_sql( $user_exclude );
				$default_exclude_keys = array_merge( $default_exclude_keys, $user_exclude );
			}

			$default_exclude_keys = array_map( 'esc_sql', $default_exclude_keys );
			$exclude_keys         = '\'' . implode( '\', \'', $default_exclude_keys ) . '\'';

			$default_exclude_key_patterns = array(
				'mailserver_%',
				'_transient%',
				'_site_transient%',
				'wf_config%',
				'wf_feed_%',
				'wpfw_%',
				'wf_dattribute_%',
				'wf_cmapping_%',
				'webappick-woo%',
				'widget_%',
			);

			/**
			 * Exclude Option Name patterns from dropdown
			 *
			 * @param array $exclude Option Name Patter to exclude.
			 * @param array $default_exclude_key_patterns Option Name Patters by default.
			 *
			 */
			$user_exclude_patterns = apply_filters( 'woo_feed_dropdown_exclude_option_name_pattern',
				null,
				$default_exclude_key_patterns );
			if ( is_array( $user_exclude_patterns ) && ! empty( $user_exclude_patterns ) ) {
				$default_exclude_key_patterns = array_merge( $default_exclude_key_patterns, $user_exclude_patterns );
			}
			$exclude_key_patterns = '';
			foreach ( $default_exclude_key_patterns as $pattern ) {
				$exclude_key_patterns .= $wpdb->prepare( ' AND option_name NOT LIKE %s', $pattern );
			}

			/** @noinspection SqlConstantCondition */
			$query = "SELECT * FROM $wpdb->options
			WHERE 1=1 AND
			( option_name NOT IN ( $exclude_keys ) $exclude_key_patterns )";
			// sql escaped, cached
			$options = $wpdb->get_results( $query ); // phpcs:ignore
			$item    = [];
			if ( is_array( $options ) && ! empty( $options ) ) {
				foreach ( $options as $key => $value ) {
					$item[ esc_attr( $value->option_id ) . '-' . esc_attr( $value->option_name ) ] = esc_html( $value->option_name );
				}
			}
			$options = $this->cache_dropdown( 'woo_feed_dropdown_wp_options', $item, $selected );
		}
		// HTML option element with escaped label and value
		echo $options; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get All Product Categories
	 *
	 * @param int $parent Category Parent Id.
	 *
	 * @return array
	 */
	public function get_categories( $parent = 0 ) {

		$args = array(
			'taxonomy'     => 'product_cat',
			'parent'       => $parent,
			'orderby'      => 'term_group',
			'show_count'   => 1,
			'pad_counts'   => 1,
			'hierarchical' => 1,
			'title_li'     => '',
			'hide_empty'   => 0,
		);

		$categories = get_categories( $args );
		if ( ! empty( $categories ) ) {
			foreach ( $categories as $cat ) {
				$this->cats[ $cat->slug ] = $cat->name;
				$this->get_categories( $cat->term_id );
			}
		}

		return $this->cats;
	}

	/**
	 * Get All Product Category
	 * @return array
	 */
	public function categories() {
//		$categories = woo_feed_get_cached_data( 'woo_feed_dropdown_product_categories' );
//		if ( false === $categories ) {
			$categories = $this->get_categories( 0 );
//			woo_feed_set_cache_data( 'woo_feed_dropdown_product_categories', $categories );
//		}

		return (array) $categories;
	}

	/**
	 * Get WooCommerce Vendor List for multi-vendor shop
	 *
	 * @return false|WP_User[]|array
	 */
	public function getAllVendors() {
		$users = woo_feed_get_cached_data( 'woo_feed_dropdown_product_vendors' );
		if ( false === $users ) {
			$users       = array();
			$vendor_role = woo_feed_get_multi_vendor_user_role();
			if ( ! empty( $vendor_role ) ) {
				/**
				 * Filter Get Vendor (User) Query Args
				 *
				 * @param array $args
				 */
				$args = apply_filters( 'woo_feed_get_vendors_args', [ 'role' => $vendor_role ] );
				if ( is_array( $args ) && ! empty( $args ) ) {
					$users = get_users( $args );
				}
			}
			woo_feed_set_cache_data( 'woo_feed_dropdown_product_vendors', $users );
		}

		return apply_filters( 'woo_feed_product_vendors', $users );
	}

	// Product DropDowns.

	/**
	 * Get product related post meta keys (filtered)
	 * @return array
	 */
	protected function getCustomAttributes() {
		$attributes = woo_feed_get_cached_data( 'woo_feed_dropdown_product_custom_attributes' );
		if ( false === $attributes ) {
			// Get Variation Attributes
			global $wpdb;
			$attributes = array();
			$sql        = "SELECT DISTINCT( meta_key ) FROM $wpdb->postmeta
			WHERE post_id IN (
			    SELECT ID FROM $wpdb->posts WHERE post_type = 'product_variation' -- local attributes will be found on variation product meta only with attribute_ suffix
			) AND (
			    meta_key LIKE 'attribute_%' -- include only product attributes from meta list
			    AND meta_key NOT LIKE 'attribute_pa_%'
			)";
			// sanitization ok
			$localAttributes = $wpdb->get_col( $sql ); // phpcs:ignore
			foreach ( $localAttributes as $localAttribute ) {
				$localAttribute                                                                     = str_replace( 'attribute_', '', $localAttribute );
				$attributes[ Woo_Feed_Products_v3_Pro::PRODUCT_ATTRIBUTE_PREFIX . $localAttribute ] = ucwords( str_replace( '-', ' ', $localAttribute ) );
			}

			// Get Product Custom Attributes
			$sql              = 'SELECT meta.meta_id, meta.meta_key as name, meta.meta_value as type FROM ' . $wpdb->postmeta . ' AS meta, ' . $wpdb->posts . " AS posts WHERE meta.post_id = posts.id AND posts.post_type LIKE '%product%' AND meta.meta_key='_product_attributes';";
			$customAttributes = $wpdb->get_results( $sql ); // phpcs:ignore

			if ( ! empty( $customAttributes ) ) {
				foreach ( $customAttributes as $key => $value ) {
					$product_attr = maybe_unserialize( $value->type );
					if ( ! empty( $product_attr ) && is_array($product_attr)) {
						foreach ( $product_attr as $key => $arr_value ) {
							if ( strpos( $key, 'pa_' ) === false ) {
								$attributes[ Woo_Feed_Products_v3_Pro::PRODUCT_ATTRIBUTE_PREFIX . $key ] = ucwords( str_replace( '-', ' ', $arr_value['name'] ) );
							}
						}
					}
				}
			}
			woo_feed_set_cache_data( 'woo_feed_dropdown_product_custom_attributes', $attributes );
		}

		// @TODO implement filter hook
		return (array) $attributes;
	}

	/**
	 * Get All Custom Attributes
	 * @return array
	 */
	protected function getProductMetaKeys() {
		$info = woo_feed_get_cached_data( 'woo_feed_dropdown_meta_keys' );
		if ( false === $info ) {
			global $wpdb;
			$info = array();
			// Load the main attributes.

			$default_exclude_keys = array(
				// WP internals.
				'_edit_lock',
				'_wp_old_slug',
				'_edit_last',
				'_wp_old_date',
				// WC internals.
				'_downloadable_files',
				'_sku',
				'_weight',
				'_width',
				'_height',
				'_length',
				'_file_path',
				'_file_paths',
				'_default_attributes',
				'_product_attributes',
				'_children',
				'_variation_description',
				// ignore variation description, engine will get child product description from WC CRUD WC_Product::get_description().
				// Plugin Data.
				'_wpcom_is_markdown',
				// JetPack Meta.
				'_yith_wcpb_bundle_data',
				// Yith product bundle data.
				'_et_builder_version',
				// Divi builder data.
				'_vc_post_settings',
				// Visual Composer (WP Bakery) data.
				'_enable_sidebar',
				'frs_woo_product_tabs',
				// WooCommerce Custom Product Tabs http://www.skyverge.com/.
			);

			/**
			 * Exclude meta keys from dropdown
			 *
			 * @param array $exclude meta keys to exclude.
			 * @param array $default_exclude_keys Exclude keys by default.
			 *
			 */
			$user_exclude = apply_filters( 'woo_feed_dropdown_exclude_meta_keys', null, $default_exclude_keys );

			if ( is_array( $user_exclude ) && ! empty( $user_exclude ) ) {
				$user_exclude         = esc_sql( $user_exclude );
				$default_exclude_keys = array_merge( $default_exclude_keys, $user_exclude );
			}

			$default_exclude_keys = array_map( 'esc_sql', $default_exclude_keys );
			$exclude_keys         = '\'' . implode( '\', \'', $default_exclude_keys ) . '\'';

			$default_exclude_key_patterns = [
				'%_et_pb_%', // Divi builder data
				'attribute_%', // Exclude product attributes from meta list
				'_yoast_wpseo_%', // Yoast SEO Data
				'_acf-%', // ACF duplicate fields
				'_aioseop_%', // All In One SEO Pack Data
				'_oembed%', // exclude oEmbed cache meta
				'_wpml_%', // wpml metas
				'_oh_add_script_%', // SOGO Add Script to Individual Pages Header Footer.
			];

			/**
			 * Exclude meta key patterns from dropdown
			 *
			 * @param array $exclude meta keys to exclude.
			 * @param array $default_exclude_key_patterns Exclude keys by default.
			 *
			 */
			$user_exclude_patterns = apply_filters( 'woo_feed_dropdown_exclude_meta_keys_pattern', null, $default_exclude_key_patterns );
			if ( is_array( $user_exclude_patterns ) && ! empty( $user_exclude_patterns ) ) {
				$default_exclude_key_patterns = array_merge( $default_exclude_key_patterns, $user_exclude_patterns );
			}
			$exclude_key_patterns = '';
			foreach ( $default_exclude_key_patterns as $pattern ) {
				$exclude_key_patterns .= $wpdb->prepare( ' AND meta_key NOT LIKE %s', $pattern );
			}

			$sql = "SELECT DISTINCT( meta_key ) FROM $wpdb->postmeta
			WHERE 1=1 AND
	        post_id IN ( SELECT ID FROM $wpdb->posts WHERE post_type = 'product' OR post_type = 'product_variation' ) AND
	        ( meta_key NOT IN ( $exclude_keys ) $exclude_key_patterns )";

			// sql escaped, cached
			$data = $wpdb->get_results( $sql ); // phpcs:ignore

			if ( count( $data ) ) {
				foreach ( $data as $key => $value ) {
					$info[ Woo_Feed_Products_v3_Pro::POST_META_PREFIX . $value->meta_key ] = $value->meta_key;
				}
			}
			woo_feed_set_cache_data( 'woo_feed_dropdown_meta_keys', $info );
		}

		return (array) $info;
	}

	/**
	 * Get All Taxonomy
	 * @return array
	 */
	protected function getAllTaxonomy() {
//		$info = woo_feed_get_cached_data( 'woo_feed_dropdown_product_taxonomy' );
//
//		if ( false === $info ) {

			$data = get_object_taxonomies( 'product' );
			$info = array();
			global $wp_taxonomies;
			$default_excludes = [
				'product_type',
				'product_visibility',
				'product_cat',
				'product_tag',
				'product_shipping_class',
				'translation_priority',
			];

			/**
			 * Exclude Taxonomy from dropdown
			 *
			 * @param array $user_excludes
			 * @param array $default_excludes
			 */
			$user_excludes = apply_filters( 'woo_feed_dropdown_exclude_taxonomy', null, $default_excludes );
			if ( is_array( $user_excludes ) && ! empty( $user_excludes ) ) {
				$default_excludes = array_merge( $default_excludes, $user_excludes );
			}
			if ( count( $data ) ) {
				foreach ( $data as $key => $value ) {
					if ( in_array( $value, $default_excludes ) || strpos( $value, 'pa_' ) !== false ) {
						continue;
					}
					$label                                                              = isset( $wp_taxonomies[ $value ] ) ? $wp_taxonomies[ $value ]->label . " [{$value}]" : $value;
					$info[ Woo_Feed_Products_v3_Pro::PRODUCT_TAXONOMY_PREFIX . $value ] = $label;
				}
			}
//			woo_feed_set_cache_data( 'woo_feed_dropdown_product_taxonomy', $info );
//		}

		return (array) $info;
	}

	/**
	 * Get Category Mappings
	 * @return array
	 */
	protected function getCustomCategoryMappedAttributes() {
			global $wpdb;
			// Load Custom Category Mapped Attributes
			$info = array();
			// query cached and escaped
			$data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name LIKE %s;", Woo_Feed_Products_v3_Pro::PRODUCT_CATEGORY_MAPPING_PREFIX . '%' ) );  // phpcs:ignore
			if ( count( $data ) ) {
				foreach ( $data as $key => $value ) {
					$opts                        = maybe_unserialize( $value->option_value );
					$opts                        = maybe_unserialize( $opts );
					$info[ $value->option_name ] = is_array( $opts ) && isset( $opts['mappingname'] ) ? $opts['mappingname'] : str_replace( 'wf_cmapping_',
						'',
						$value->option_name );
				}
			}
		return (array) $info;
	}

	/**
	 * Get Attribute Mappings
	 * @return array
	 */
	protected function getCustomMappedAttributes() {
//		$info = woo_feed_get_cached_data( 'woo_feed_dropdown_product_attribute_mappings' );
//		if ( false === $info ) {
			global $wpdb;
			// Load Custom Category Mapped Attributes
			$info = array();
			// query cached and escaped
			$data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name LIKE %s;", Woo_Feed_Products_v3_Pro::PRODUCT_ATTRIBUTE_MAPPING_PREFIX . '%' ) );  // phpcs:ignore
			if ( count( $data ) ) {
				foreach ( $data as $key => $value ) {
					$opts                        = maybe_unserialize( $value->option_value );
					$info[ $value->option_name ] = is_array( $opts ) && isset( $opts['name'] ) ? $opts['name'] : str_replace( Woo_Feed_Products_v3_Pro::PRODUCT_ATTRIBUTE_MAPPING_PREFIX, '', $value->option_name );
				}
			}
//			woo_feed_set_cache_data( 'woo_feed_dropdown_product_attribute_mappings', $info );
//		}

		return (array) $info;
	}

	/**
	 * Get Dynamic Attribute List
	 * @return array
	 */
	protected function dynamicAttributes() {
//		$info = woo_feed_get_cached_data( 'woo_feed_dropdown_product_dynamic_attributes' );
//		if ( false === $info ) {
			global $wpdb;

			// Load Custom Category Mapped Attributes
			$info = array();
			// query escaped and cached
			$data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name LIKE %s;", Woo_Feed_Products_v3_Pro::PRODUCT_DYNAMIC_ATTRIBUTE_PREFIX . '%' ) ); // phpcs:ignore
			if ( count( $data ) ) {
				foreach ( $data as $key => $value ) {
					$opts                        = maybe_unserialize( $value->option_value );
					$opts                        = maybe_unserialize( $opts );
					$info[ $value->option_name ] = is_array( $opts ) && isset( $opts['wfDAttributeName'] ) ? $opts['wfDAttributeName'] : str_replace( 'wf_dattribute_',
						'',
						$value->option_name );
				}
			}
//			woo_feed_set_cache_data( 'woo_feed_dropdown_product_dynamic_attributes', $info );
//		}

		return (array) $info;
	}

	/**
	 * Product Attributes
	 *
	 * @return array
	 */
	protected function get_product_attributes() {

		$attributes = parent::get_product_attributes();

		$_custom_attributes = $this->getCustomAttributes();
		if ( ! empty( $_custom_attributes ) && is_array( $_custom_attributes ) ) {
			$attributes['--4']  = esc_html__( 'Product Custom Attributes', 'woo-feed' );
			$attributes         = $attributes + $_custom_attributes;
			$attributes['---4'] = '';
		}

		$_meta_keys = $this->getProductMetaKeys();
		if ( ! empty( $_meta_keys ) && is_array( $_meta_keys ) ) {
			$attributes['--5']  = esc_html__( 'Custom Fields & Post Metas', 'woo-feed' );
			$attributes         = $attributes + $_meta_keys;
			$attributes['---5'] = '';
		}

		$_taxonomies = $this->getAllTaxonomy();
		if ( ! empty( $_taxonomies ) && is_array( $_taxonomies ) ) {
			$attributes['--6']  = esc_html__( 'Custom Taxonomies', 'woo-feed' );
			$attributes         = $attributes + $_taxonomies;
			$attributes['---6'] = '';
		}

		$_wp_options = get_option( 'wpfp_option', array() );
		if ( ! empty( $_wp_options ) && is_array( $_wp_options ) ) {
			$attributes['--7']  = esc_html__( 'WP Options', 'woo-feed' );
			$attributes         = $attributes + $_wp_options;
			$attributes['---7'] = '';
		}

		$_dynamic_attributes = $this->dynamicAttributes();
		if ( ! empty( $_dynamic_attributes ) && is_array( $_dynamic_attributes ) ) {
			$attributes['--8']  = esc_html__( 'Dynamic Attributes', 'woo-feed' );
			$attributes         = $attributes + $_dynamic_attributes;
			$attributes['---8'] = '';
		}

		$_category_mappings = $this->getCustomCategoryMappedAttributes();
		if ( ! empty( $_category_mappings ) && is_array( $_category_mappings ) ) {
			$attributes['--9']  = esc_html__( 'Category Mappings', 'woo-feed' );
			$attributes         = $attributes + $_category_mappings;
			$attributes['---9'] = '';
		}

		$_attribute_mappings = $this->getCustomMappedAttributes();
		if ( ! empty( $_attribute_mappings ) && is_array( $_attribute_mappings ) ) {
			$attributes['--10']  = esc_html__( 'Attribute Mappings', 'woo-feed' );
			$attributes          = $attributes + $_attribute_mappings;
			$attributes['---10'] = '';
		}

		// Extra Attributes.
		$extra_attributes = apply_filters( 'woo_feed_dropdown_product_extra_attributes', null );
		if ( is_array( $extra_attributes ) && ! empty( $extra_attributes ) ) {
			$attributes['--11']  = esc_html__( 'Extra Attributes', 'woo-feed' );
			$attributes          = $attributes + [];
			$attributes['---11'] = '';
		}

		return $attributes;
	}
}
