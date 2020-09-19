<?php
/**
 * Merchant Info Pro
 * @author Kudratullah <mhamudul.hk@gmail.com>
 * @copyright 2020 WebAppick
 * @package WooFeed
 * @since WooFeed 3.3.10
 * @version 1.0.0
 */

class Woo_Feed_Merchant_Pro extends Woo_Feed_Merchant
{

	/**
	 * Woo_Feed_Merchant constructor.
	 *
	 * @param string $merchant      merchant slug
	 *                              if merchant not found default template params will be loaded.
	 * @param string $currency      currency code
	 * @param string $brand_pattern brand name (pattern)
	 *
	 * @return void
	 *
	 * @see Woo_Feed_Merchant::load_merchant_templates
	 */


	public function __construct( $merchant = null, $currency = null, $brand_pattern = null ) {
		parent::__construct( $merchant, $currency, $brand_pattern );
		$this->load_merchant_infos();
		$this->load_merchant_templates();
		$this->get_template_raw();
		$this->merchantInfo();

	}


	protected function load_merchant_infos() {
		parent::load_merchant_infos();
		$this->merchant_infos = array_merge(
			$this->merchant_infos,
			array(
				'custom2'    => array(
					'feed_file_type' => array( 'XML' ),
				),
				'admarkt'    => array(
					'feed_file_type' => array( 'XML' ),
				),
				'glami'      => array(
					'link'           => 'https://www.glami.eco/info/feed/',
					'feed_file_type' => array( 'XML' ),
				),
				'yandex_xml' => array(
					'link'           => 'http://bit.ly/2P3pFbH',
					'feed_file_type' => array( 'XML' ),
				), 
            // Yandex (XML).
			)
		);

	}


	/**
	 * Sets Merchant Templates
	 *
	 * @return void
	 */
	protected function load_merchant_templates() {
		parent::load_merchant_templates();
		$this->templates = array_merge(
			$this->templates,
			array(
				'custom2'    => array(
					'feed_config_custom2' => file_get_contents( WOO_FEED_PRO_ADMIN_PATH . '/partials/templates/custom2/custom2.txt' ),
				),
				'admarkt'    => array(
					'feed_config_custom2' => file_get_contents( WOO_FEED_PRO_ADMIN_PATH . '/partials/templates/custom2/admarkt.txt' ),
				),
				'glami'      => array(
					'feed_config_custom2' => file_get_contents( WOO_FEED_PRO_ADMIN_PATH . '/partials/templates/custom2/glami.txt' ),
				),
				'yandex_xml' => array(
					'feed_config_custom2' => file_get_contents( WOO_FEED_PRO_ADMIN_PATH . '/partials/templates/custom2/yandex_xml.txt' ),
				),
			)
		);

	}


	/**
	 * Custom Template List
	 * @return array
	 */
	protected function custom_merchants() {
		return array(
			'--1'     => esc_html__( 'Custom Template', 'woo-feed' ),
			'custom'  => esc_html__( 'Custom Template 1', 'woo-feed' ),
			'custom2' => esc_html__( 'Custom Template 2 (XML)', 'woo-feed' ),
			'---1'    => '',
		);

	}


	/**
	 * Popular Template List
	 * @return array
	 */
	protected function popular_merchants() {
		$templates = parent::popular_merchants();
		unset( $templates['---2'] );
		$templates = ($templates + array(
			'yandex_xml' => esc_html__( 'Yandex (XML)', 'woo-feed' ),
			'---2'       => '',
		));
		ksort( $templates );
		
		return $templates;

	}


	/**
	 * Other Template LIst
	 * @return array
	 */
	protected function others_merchants() {
		$templates = parent::others_merchants();
		unset( $templates['--3'] );
		unset( $templates['---3'] );
		$templates = ($templates + array(
			'admarkt' => esc_html__( 'Admarkt(marktplaats)', 'woo-feed' ),
			'glami'   => esc_html__( 'GLAMI', 'woo-feed' ),
		));
		ksort( $templates );
		return (array(
			'--3' => esc_html__( 'Templates', 'woo-feed' ),
		) + $templates + array( '---3' => '' ));

	}


}
