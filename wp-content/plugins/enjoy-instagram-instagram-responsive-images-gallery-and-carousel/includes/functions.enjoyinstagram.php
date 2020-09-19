<?php
/**
 * Common functions for plugin Enjoy Instagram
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // exit if call directly
}

if ( ! function_exists( 'enjoyinstagram_shuffle_assoc' ) ) {
	function enjoyinstagram_shuffle_assoc( &$array ) {
		if ( empty( $array ) ) {
			return false;
		}

		$keys = array_keys( $array );
		shuffle( $keys );
		foreach ( $keys as $key ) {
			$new[ $key ] = $array[ $key ];
		}
		$array = $new;

		return true;
	}
}

if ( ! function_exists( 'enjoyinstagram_replace4byte' ) ) {
	/**
	 * @param $string
	 *
	 * @return null|string|string[]
	 */
	function enjoyinstagram_replace4byte( $string ) {
		return preg_replace( '%(?:
          \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
        | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
        | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
    )%xs', '', $string );
	}
}

if ( ! function_exists( 'enjoyinstagram_isHttps' ) ) {
	/**
	 * Check HTTPS
	 *
	 * @return bool
	 */
	function enjoyinstagram_isHttps() {
		return ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off';
	}
}

if ( ! function_exists( 'enjoyinstagram_format_entry_before_print' ) ) {
	/**
	 * Format an entry before print
	 *
	 * @param array $entry
	 *
	 * @return array
	 * @since 4.0.0
	 */
	function enjoyinstagram_format_entry_before_print( $entry ) {
		$entry['caption'] = array(
			'text' => ! empty( $entry['caption'] ) ? str_replace( "\"", "&quot;",
				$entry['caption'] ) : ''
		);

		$entry['type'] = strpos( $entry['image_url'], 'video' ) !== false ? 'video' : 'image';

		if ( $entry['type'] === 'video' && $entry['thumbnail_url'] === null ) {
			// In public hashtag video has no thumb
			$entry['thumbnail_url'] = ENJOYINSTAGRAM_ASSETS_URL . '/images/video_placeholder.jpeg';
		}

		$entry['images'] = array(
			'thumbnail'           => array( 'url' => $entry['thumbnail_url'] ),
			'standard_resolution' => array( 'url' => $entry['image_url'] )
		);

		if ( enjoyinstagram_isHttps() ) {
			$entry['images']['thumbnail']['url']           = str_replace( 'http://', 'https://',
				$entry['images']['thumbnail']['url'] );
			$entry['images']['standard_resolution']['url'] = str_replace( 'http://', 'https://',
				$entry['images']['standard_resolution']['url'] );
		}

		return $entry;
	}
}

if ( ! function_exists( 'enjoyinstagram_add_notice' ) ) {
	/**
	 * Add a flash message. It will be displayed in the admin_notice section
	 *
	 * @param string $message
	 * @param string $type
	 *
	 * @return void
	 * @author Giulio Ganci
	 * @since 11.0.0
	 */
	function enjoyinstagram_add_notice( $message, $type ) {
		$notices   = enjoyinstagram_get_notices();
		$notices[] = array( 'message' => $message, 'type' => $type );
		set_transient( 'enjoyinstagram_notices', $notices );
	}
}

if ( ! function_exists( 'enjoyinstagram_get_notices' ) ) {
	/**
	 * Return the flash messages that will be displayed in admin_notice section
	 *
	 * @param bool $delete
	 *
	 * @return array
	 * @author Giulio Ganci
	 * @since 11.0.0
	 */
	function enjoyinstagram_get_notices( $delete = true ) {
		$notices = get_transient( 'enjoyinstagram_notices' );
		if ( ! $notices ) {
			return [];
		}

		if ( $delete ) {
			delete_transient( 'enjoyinstagram_notices' );
		}

		return $notices;
	}
}

if ( ! function_exists( 'enjoyinstagram_extract_hashtags' ) ) {
	/**
	 * Return the hashtags from a given text
	 *
	 * @param string $string
	 *
	 * @return array
	 * @author Giulio Ganci
	 * @since 11.0.0
	 */
	function enjoyinstagram_extract_hashtags( $string ) {
		$regex = '/(^|[^0-9A-Z&\/\?]+)([#＃]+)([0-9A-Z_]*[A-Z_]+[a-z0-9_üÀ-ÖØ-öø-ÿ]*)/iu';
		preg_match_all( $regex, $string, $matches );

		return $matches[3];
	}
}