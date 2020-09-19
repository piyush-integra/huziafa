<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


ReyCoreKirki::add_section('woocommerce_product_catalog_misc', array(
    'title'          => esc_html__('Product Catalog - Misc.', 'rey-core'),
	'priority'       => 11,
	'panel'			=> 'woocommerce'
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'shop_catalog',
	'label'       => esc_html__( 'Enable Catalog Mode', 'rey-core' ),
	'section'     => 'woocommerce_product_catalog_misc',
	'default'     => false,
	'priority'    => 5,
	'description' => __( 'Enabling catalog mode will disable all cart functionalities.', 'rey-core' ),
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'shop_catalog_variable',
	'label'       => esc_html__( 'Disable variable product form?', 'rey-core' ),
	'section'     => 'woocommerce_product_catalog_misc',
	'default'     => true,
	'priority'    => 5,
	'active_callback' => [
		[
			'setting'  => 'shop_catalog',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'shop_catalog_page_exclude',
	'label'       => esc_html__( 'Exclude categories from Shop Page', 'rey-core' ),
	'section'     => 'woocommerce_product_catalog_misc',
	'default'     => '',
	'priority'    => 5,
	'multiple'    => 100,
	'choices'     => reycore_wc__product_categories([
		'parent' => 0,
		'hide_empty' => false,
	]),
] );

reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-woocommerce/#product-catalog-miscellaneous',
	'section' => 'woocommerce_product_catalog_misc'
]);
