<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$section = 'section_cover__shop';

ReyCoreKirki::add_section($section, array(
	'title'          => esc_html__('WooCommerce (Shop)', 'rey-core'),
	'panel'			 => 'cover_panel',
	'priority'       => 10,
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'custom',
	'settings'    => 'cover_title__shop',
	'section'     => $section,
	'priority'       => 10,
	'default'     => $main_desc,
] );

// Shop Categories
reycore_customizer__title([
	'title'       => esc_html__('Categories', 'rey-core'),
	'description' => esc_html__('Select a page cover to display in product categories. You can always disable or change the Page Cover of a specific category, in its options.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'       => 'none',
	'upper'        => true,
	'priority'    => 10,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'cover__shop_cat',
	'label'       => esc_html__( 'Select a Page Cover', 'rey-core' ),
	'section'     => $section,
	'default'     => 'no',
	'choices'     => $covers,
	'priority'    => 10
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'cover__shop_cat_inherit',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Subcategories inherit parent', 'rey-core' ),
		esc_html__( 'If enabled, subcategories will inherit parent category cover.')
	),
	'section'     => $section,
	'default'     => false,
	'priority'    => 10,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'repeater',
	'settings'    => 'cover__shop_cat_custom',
	'label'       => esc_html__('Page Cover per Category', 'rey-core'),
	'description' => __('Assign Page Cover global sections per product categories.', 'rey-core'),
	'section'     => $section,
	'row_label' => [
		'value' => esc_html__('Page Cover', 'rey-core'),
		'type'  => 'field',
		'field' => 'categories',
	],
	'button_label' => esc_html__('New cover per category', 'rey-core'),
	'default'      => [],
	'fields' => [
		'gs' => [
			'type'        => 'select',
			'label'       => esc_html__('Select Global Section', 'rey-core'),
			'choices'     => ReyCore_GlobalSections::get_global_sections('cover', ['' => '- Select -']),
		],
		'categories' => [
			'type'        => 'select',
			'label'       => esc_html__('Categories', 'rey-core'),
			'choices'     => reycore_wc__product_categories([
				'hide_empty' => false,
				'labels' => true,
			]),
			'multiple' => 100
		],
	],
] );


// Product Page
reycore_customizer__title([
	'title'       => esc_html__('Product Page', 'rey-core'),
	'description' => esc_html__('Select a page cover to display in product pages.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
	'priority'    => 10,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'cover__product_page',
	'label'       => esc_html__( 'Select a Page Cover', 'rey-core' ),
	'section'     => $section,
	'default'     => 'no',
	'choices'     => $covers,
	'priority'    => 10
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'repeater',
	'settings'    => 'cover__product_page_custom',
	'label'       => esc_html__('Product Page Cover based on Condition', 'rey-core'),
	'description' => __('Assign Page Cover global sections on product pages, based on their categories or tags.', 'rey-core'),
	'section'     => $section,
	'row_label' => [
		'value' => esc_html__('Page Cover', 'rey-core'),
		'type'  => 'text',
	],
	'button_label' => esc_html__('New cover per condition', 'rey-core'),
	'default'      => [],
	'fields' => [
		'gs' => [
			'type'        => 'select',
			'label'       => esc_html__('Select Global Section', 'rey-core'),
			'choices'     => ReyCore_GlobalSections::get_global_sections('cover', ['' => '- Select -']),
		],
		'categories' => [
			'type'        => 'select',
			'label'       => esc_html__('Categories', 'rey-core'),
			'choices'     => reycore_wc__product_categories([
				'hide_empty' => false,
				'labels' => true,
			]),
			'multiple' => 100
		],
		'tags' => [
			'type'        => 'select',
			'label'       => esc_html__('Tags', 'rey-core'),
			'choices'     => reycore_wc__product_tags([
				'hide_empty' => false,
			]),
			'multiple' => 100
		],
	],
] );


// Shop Page
reycore_customizer__title([
	'title'       => esc_html__('Shop Page', 'rey-core'),
	'description' => esc_html__('These settings will apply on the default Shop page (Assigned in WooCommerce > Settings > Products > General).', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
	'priority'    => 10,
]);
ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'cover__shop_page',
	'label'       => esc_html__( 'Select a Page Cover', 'rey-core' ),
	'section'     => $section,
	'default'     => 'no',
	'choices'     => $covers,
	'priority'    => 10
] );

reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-page-cover/#shop',
	'section' => $section
]);
