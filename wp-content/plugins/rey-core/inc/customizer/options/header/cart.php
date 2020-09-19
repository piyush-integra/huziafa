<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'header_cart_options';

ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('Shopping Cart', 'rey-core'),
	'priority'       => 60,
	'panel'			 => 'header_options'
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_enable_cart',
	'label'       => esc_html__( 'Enable Shopping Cart?', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'header_cart_layout',
	'label'       => esc_html__( 'Cart Layout', 'rey-core' ),
	'section'     => $section,
	'default'     => 'bag',
	'choices'     => [
		'bag' => esc_html__( 'Icon - Shopping Bag', 'rey-core' ),
		'bag2' => esc_html__( 'Icon - Shopping Bag 2', 'rey-core' ),
		'bag3' => esc_html__( 'Icon - Shopping Bag 3', 'rey-core' ),
		'basket' => esc_html__( 'Icon - Shopping Basket', 'rey-core' ),
		'basket2' => esc_html__( 'Icon - Shopping Basket 2', 'rey-core' ),
		'cart' => esc_html__( 'Icon - Shopping Cart', 'rey-core' ),
		'cart2' => esc_html__( 'Icon - Shopping Cart 2', 'rey-core' ),
		'cart3' => esc_html__( 'Icon - Shopping Cart 3', 'rey-core' ),
		'text' => esc_html__( 'Text (deprecated)', 'rey-core' ),
		'disabled' => esc_html__( 'No Icon', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'header_enable_cart',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

// TODO: Remove in 2.0
ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'header_cart_text',
	'label'    => esc_html__( 'Cart Text', 'rey-core' ),
	'description'    => esc_html__( 'Use {{total}} string to add the cart totals.', 'rey-core' ),
	'section'  => $section,
	'default'  => '',
	'active_callback' => [
		[
			'setting'  => 'header_enable_cart',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'header_cart_layout',
			'operator' => '==',
			'value'    => 'text',
		],
	],
	'input_attrs' => [
		'placeholder' => esc_html__( 'eg: CART', 'rey-core' )
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'header_cart_text_v2',
	'label'    => esc_html__( 'Cart Text', 'rey-core' ),
	'description'    => esc_html__( 'Use {{total}} string to add the cart totals.', 'rey-core' ),
	'section'  => $section,
	'default'  => '',
	'active_callback' => [
		[
			'setting'  => 'header_enable_cart',
			'operator' => '==',
			'value'    => true,
		],
		// TODO: remove in v2.0
		// added this to make sure there aren't 2 text fields
		[
			'setting'  => 'header_cart_layout',
			'operator' => '!=',
			'value'    => 'text',
		],
	],
	'input_attrs' => [
		'placeholder' => esc_html__( 'eg: CART', 'rey-core' )
	]
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'header_cart_hide_empty',
	'label'       => esc_html__( 'Hide Cart if empty?', 'rey-core' ),
	'description' => esc_html__( 'Will hide the cart icon if no products in cart.', 'rey-core' ),
	'section'     => $section,
	'default'     => 'no',
	'choices'     => [
		'yes' => esc_html__( 'Yes', 'rey-core' ),
		'no' => esc_html__( 'No', 'rey-core' ),
	],
] );

reycore_customizer__title([
	'title'       => esc_html__('Cart Panel', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'none',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'header_cart_gs',
	'label'       => esc_html__( 'Empty Cart Content', 'rey-core' ),
	'description' => esc_html__( 'Add custom Elementor content into the Cart Panel if no products are added into it.', 'rey-core' ),
	'section'     => $section,
	'default'     => 'none',
	'choices'     => ReyCore_GlobalSections::get_global_sections('generic', ['none' => '- None -']),
	'active_callback' => [
		[
			'setting'  => 'header_cart_hide_empty',
			'operator' => '==',
			'value'    => 'no',
			],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_cart_show_shipping',
	'label'       => esc_html__( 'Show Shipping under subtotal', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_cart_show_qty',
	'label'       => esc_html__( 'Show quantity controls', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
] );

reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-header-settings/#shopping-cart',
	'section' => $section
]);
