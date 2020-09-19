<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'shop_product_section_components';

reycore_customizer__title([
	'title'       => esc_html__('Related products', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_product_page_related',
	'label'       => esc_html__( 'Display section', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'single_product_page_related_title',
	'label'    => esc_html__('Title', 'rey-core'),
	'section'  => $section,
	'default'  => '',
	'active_callback' => [
		[
			'setting'  => 'single_product_page_related',
			'operator' => '==',
			'value'    => true,
		],
	],
	'input_attrs' => [
		'placeholder' => esc_html__('eg: Related products', 'rey-core')
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'single_product_page_related_columns',
    'label'       => esc_html__('Products per row', 'rey-core'),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'min'  => 1,
		'max'  => 6,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'single_product_page_related',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'single_product_page_related_per_page',
    'label'       => esc_html__('Limit', 'rey-core'),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'min'  => 1,
		'max'  => 20,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'single_product_page_related',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

// ReyCoreKirki::add_field( 'rey_core_kirki', [
// 	'type'        => 'toggle',
// 	'settings'    => 'single_product_page_related_carousel',
// 	'label'       => esc_html__( 'Carousel', 'rey-core' ),
// 	'description'       => esc_html__( 'Make related products as a carousel', 'rey-core' ),
// 	'section'     => $section,
// 	'default'     => false,
// 	'active_callback' => [
// 		[
// 			'setting'  => 'single_product_page_related',
// 			'operator' => '==',
// 			'value'    => true,
// 		],
// 	],
// ] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_product_page_related_custom',
	'label'       => esc_html__( 'Custom products', 'rey-core' ),
	'description'       => esc_html__( 'Enabling this option will add a custom input into the products pages in admin, in the Linked products tab, to select custom products.', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'single_product_page_related',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_product_page_related_custom_replace',
	'label'       => esc_html__( 'Replace products', 'rey-core' ),
	'description' => esc_html__( 'Enable to replace default related products with the ones you select in the Linked products tab.', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'single_product_page_related',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'single_product_page_related_custom',
			'operator' => '==',
			'value'    => true,
		],
	],
] );
