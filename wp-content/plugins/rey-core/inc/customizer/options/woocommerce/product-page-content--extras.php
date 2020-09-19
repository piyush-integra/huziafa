<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'shop_product_section_content';

reycore_customizer__title([
    'title'       => esc_html__('EXTRAS', 'rey-core'),
	'description' => esc_html__('Misc. content displayed before product meta.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

/* ------------------------------------ ESTIMATED DELIVERY ------------------------------------ */

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_extras__estimated_delivery',
	'label'       => esc_html__( 'Show Estimated Delivery', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'estimated_delivery__prefix',
	'label'       => esc_html__( 'Prefix title', 'rey-core' ),
	'section'     => $section,
	'default'     => esc_html__('Estimated delivery:', 'rey-core'),
	'input_attrs'     => [
		'placeholder' => esc_html__('eg: Estimated delivery:', 'rey-core'),
	],
	'active_callback' => [
		[
			'setting'  => 'single_extras__estimated_delivery',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Settings', 'rey-core')
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'estimated_delivery__days',
	'label'       => esc_html__( 'Days', 'rey-core' ),
	'section'     => $section,
	'default'     => 3,
	'choices'     => [
		'min'  => 1,
		'max'  => 200,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'single_extras__estimated_delivery',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'estimated_delivery__display_type',
	'label'       => esc_html__( 'Display type', 'rey-core' ),
	'section'     => $section,
	'default'     => 'number',
	'choices'     => [
		'number' => esc_html__( 'Number of days', 'rey-core' ),
		'date' => esc_html__( 'Exact date', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'single_extras__estimated_delivery',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'estimated_delivery__exclude',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Non-working days', 'rey-core'),
		__('Exclude certain non-working days from date estimation count.', 'rey-core')
	),
	'section'     => $section,
	'default'     => ['Saturday', 'Sunday'],
	'multiple'    => 6,
	'choices'     => [
		'Monday' => esc_html__( 'Monday', 'rey-core' ),
		'Tuesday' => esc_html__( 'Tuesday', 'rey-core' ),
		'Wednesday' => esc_html__( 'Wednesday', 'rey-core' ),
		'Thursday' => esc_html__( 'Thursday', 'rey-core' ),
		'Friday' => esc_html__( 'Friday', 'rey-core' ),
		'Saturday' => esc_html__( 'Saturday', 'rey-core' ),
		'Sunday' => esc_html__( 'Sunday', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'single_extras__estimated_delivery',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'estimated_delivery__display_type',
			'operator' => '==',
			'value'    => 'date',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'estimated_delivery__inventory',
	'label'       => __('Show for', 'rey-core'),
	'section'     => $section,
	'default'     => ['instock'],
	'multiple'    => 3,
	'choices'     => [
		'instock' => esc_html__( 'In Stock', 'rey-core' ),
		'outofstock' => esc_html__( 'Out of stock', 'rey-core' ),
		'onbackorder' => esc_html__( 'Available on backorder', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'single_extras__estimated_delivery',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'estimated_delivery__days_margin',
	'label'       => esc_html__( 'Days margin', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'min'  => 1,
		'max'  => 100,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'single_extras__estimated_delivery',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'color',
	'settings'    => 'estimated_delivery__color',
	'label'       => esc_html__( 'Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output'          => [
		[
			'element'  		   => '.woocommerce div.product .rey-estimatedDelivery',
			'property' 		   => 'color',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'single_extras__estimated_delivery',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_end' => true
] );


/* ------------------------------------ CHECKBOXES ------------------------------------ */

/*

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_extras__features_list',
	'label'       => esc_html__( 'Show Features List', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'repeater',
	'settings'    => 'features_list__items',
	'label'       => esc_html__('Add Features', 'rey-core'),
	'section'     => $section,
	'row_label' => [
		'value' => esc_html__('Feature', 'rey-core'),
		'type'  => 'field',
		'field' => 'text',
	],
	'button_label' => esc_html__('New global section per category', 'rey-core'),
	'default'      => [
		[
			'text' => esc_html__('Top Customer Service', 'rey-core')
		],
		[
			'text' => esc_html__('Satisfaction Guaranteed', 'rey-core')
		],
		[
			'text' => esc_html__('SSL Enabled Secure Checkout', 'rey-core')
		],
	],
	'fields' => [
		'text' => [
			'type'        => 'text',
			'label'       => esc_html__('Feature title', 'rey-core'),
			'default'       => '',
			'input_attrs'     => [
				'placeholder' => esc_html__('eg: Some cool feature', 'rey-core'),
			],
		],
	],
	'active_callback' => [
		[
			'setting'  => 'single_extras__features_list',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Settings', 'rey-core')
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => '',
	'label'       => esc_html__( 'Select icon', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'' => esc_html__( 'Checkbox', 'rey-core' ),
		'' => esc_html__( 'Check sign', 'rey-core' ),
		'' => esc_html__( 'Badge', 'rey-core' ),
	],
] );

// show features with icon
// select icon style cu color option

*/
