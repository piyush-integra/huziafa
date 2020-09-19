<?php
/**
 * Default Hooks
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

// Admin Page Form Actions.
add_action( 'admin_post__wf_save_attribute_mapping', 'woo_feed_save_attribute_mapping' );

// The Editor.
add_filter( 'woo_feed_parsed_rules', 'woo_feed_pro_parse_feed_rules', 10, 1 );
add_filter( 'woo_feed_insert_feed_data', 'woo_feed_pro_insert_feed_data_filter', 10, 1 );

// Editor Tabs.
add_filter( 'woo_feed_editor_tabs', 'woo_feed_pro_filter_tabs' );


// Switch Language Before Getting Product Data.
add_action( 'before_woo_feed_get_product_information', 'woo_feed_switch_language', 10, 1 );
add_action( 'before_woo_feed_generate_batch_data', 'woo_feed_switch_language', 10, 1 );
add_action( 'before_woo_feed_generate_feed', 'woo_feed_switch_language', 10, 1 );
// Restore Language
add_action( 'after_woo_feed_get_product_information', 'woo_feed_restore_language', 10 );
add_action( 'after_woo_feed_generate_batch_data', 'woo_feed_restore_language', 10 );
add_action( 'after_woo_feed_generate_feed', 'woo_feed_restore_language', 10 );

// Product Loop Start.
add_action( 'woo_feed_before_product_loop', 'woo_feed_pro_apply_hooks_before_product_loop', 10, 2 );

// In The Loop

// Product Loop End.
add_action( 'woo_feed_after_product_loop', 'woo_feed_remove_hooks_before_product_loop', 10, 2 );

// End of file hooks.php.
