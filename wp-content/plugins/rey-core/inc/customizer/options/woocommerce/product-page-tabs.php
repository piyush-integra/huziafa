<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'shop_product_section_tabs';

/**
 * PRODUCT PAGE - tabs settings
 */

ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('Product Page - Tabs/Blocks', 'rey-core'),
	'priority'       => 15,
	'panel'			=> 'woocommerce'
));


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'product_content_layout',
	'label'       => esc_html__( 'Tabs Layout', 'rey-core' ),
	'section'     => $section,
	'default'     => 'blocks',
	'choices'     => [
		'blocks' => esc_html__( 'As Blocks', 'rey-core' ),
		'tabs' => esc_html__( 'Tabs', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_content_tabs_disable_titles',
	'label'       => esc_html__( 'Disable titles in Tabs', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'product_content_layout',
			'operator' => '==',
			'value'    => 'tabs',
		],
	],
] );


reycore_customizer__title([
	'title'       => esc_html__('Built-in Tabs (Blocks)', 'rey-core'),
	'description' => esc_html__('Customize the built-in tabs/blocks.', 'rey-core'),
	'section'     => $section,
	'size'        => 'lg',
	'border'      => 'bottom',
	'upper'       => true,
]);

/* ------------------------------------ DESCRPTION ------------------------------------ */


reycore_customizer__title([
	'title'       => esc_html__('Description', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'none',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_content_blocks_desc_toggle',
	'label'       => esc_html__( 'Toggle long description', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_content_blocks_desc_stretch',
	'label'       => esc_html__( 'Stretch Description Block', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'product_content_layout',
			'operator' => '==',
			'value'    => 'blocks',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'single_description_priority',
    'label'       => reycore_customizer__title_tooltip(
		esc_html__('Priority', 'rey-core'),
		__('Choose the order of the blocks/tabs.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 10,
    'choices'     => [
		'min'  => 10,
		'max'  => 200,
		'step' => 5,
	],
]);

/* ------------------------------------ INFORMATION ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Information', 'rey-core'),
	// 'description' => esc_html__('You can add a block of custom content right after the product summary.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'none',
	'upper'       => true,
]);

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'product_info',
    'label'       => esc_html__('Custom Information', 'rey-core'),
    'description' => __('Select if you want to add a tab with text content. You can override or disable per product.', 'rey-core'),
	'section'     => $section,
	'default'     => '2',
    'choices'     => [
		'1' => esc_attr__('Show', 'rey-core'),
        '2' => esc_attr__('Hide', 'rey-core')
	],
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'single__product_info_title',
	'label'       => esc_html__( 'Title', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'input_attrs'     => [
		'placeholder' => esc_html__('eg: Information', 'rey-core'),
	],
	'active_callback' => [
		[
			'setting'  => 'product_info',
			'operator' => '==',
			'value'    => '1',
		],
		[
			'setting'  => 'product_content_layout',
			'operator' => '==',
			'value'    => 'tabs',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'single_custom_info_priority',
    'label'       => reycore_customizer__title_tooltip(
		esc_html__('Priority', 'rey-core'),
		__('Choose the order of the blocks/tabs.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 15,
    'choices'     => [
		'min'  => 10,
		'max'  => 200,
		'step' => 5,
	],
]);

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'editor',
	'settings'    => 'product_info_content',
	'label'       => esc_html__( 'Add Content', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'active_callback' => [
		[
			'setting'  => 'product_info',
			'operator' => '==',
			'value'    => '1',
		],
	],
	'partial_refresh'    => [
		'product_info_content' => [
			'selector'        => '.rey-wcPanel--information',
			'render_callback' => function() {
				return get_theme_mod('product_info_content', '');
			},
		],
	],
] );


/* ------------------------------------ Specs ------------------------------------ */

reycore_customizer__title([
    'title'       => esc_html__('Additional Info. / SPECIFICATIONS', 'rey-core'),
	'description' => __('Content inside Additional Information / Specifications block/tab.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_specifications_block',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Enable tab/block', 'rey-core'),
		__('Select the visibility of Specifications (Additional Information) block/tab', 'rey-core')
	),
	'section'     => $section,
	'default'     => true
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'single_specifications_title',
	'label'       => esc_html__( 'Title', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'input_attrs'     => [
		'placeholder' => esc_html__('eg: Specifications', 'rey-core'),
	],
	'active_callback' => [
		[
			'setting'  => 'single_specifications_block',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_specifications_block_dimensions',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Enable Dimensions Info', 'rey-core'),
		__('Select the visibility of Weight/Dimensions rows.', 'rey-core')
	),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'single_specifications_block',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'single_specifications_position',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Spec. Position', 'rey-core'),
		__('Select if you want to move the Specifications block in product summary.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'' => esc_html__( 'Default (as block/tab)', 'rey-core' ),
		'29' => esc_html__( 'After short description', 'rey-core' ),
		'39' => esc_html__( 'After Add to cart button', 'rey-core' ),
		'49' => esc_html__( 'After Meta', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'single_specifications_block',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'single_specs_priority',
    'label'       => reycore_customizer__title_tooltip(
		esc_html__('Priority', 'rey-core'),
		__('Choose the order of the blocks/tabs.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 20,
    'choices'     => [
		'min'  => 10,
		'max'  => 200,
		'step' => 5,
	],
]);


/* ------------------------------------ Specs ------------------------------------ */

reycore_customizer__title([
    'title'       => esc_html__('REVIEWS', 'rey-core'),
	'description' => esc_html__('Content inside reviews block/tab.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_reviews_start_opened',
	'label'       => esc_html__( 'Start Opened', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'single_reviews_priority',
    'label'       => reycore_customizer__title_tooltip(
		esc_html__('Priority', 'rey-core'),
		__('Choose the order of the blocks/tabs.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 30,
    'choices'     => [
		'min'  => 10,
		'max'  => 200,
		'step' => 5,
	],
]);

/* ------------------------------------ CUSTOM TABS ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Custom Tabs', 'rey-core'),
	'description' => esc_html__('Add extra tabs. Content will be edited in product page settings.', 'rey-core'),
	'section'     => $section,
	'size'        => 'lg',
	'border'      => 'bottom',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'repeater',
	'settings'    => 'single__custom_tabs',
	'label'       => esc_html__('Add Custom tabs', 'rey-core'),
	'section'     => $section,
	'row_label' => [
		'value' => esc_html__('Tab', 'rey-core'),
		'type'  => 'field',
		'field' => 'text',
	],
	'button_label' => esc_html__('New Tab', 'rey-core'),
	'default'      => [],
	'fields' => [
		'text' => [
			'type'        => 'text',
			'label'       => esc_html__('Title', 'rey-core'),
			'default'       => '',
			'input_attrs'     => [
				'placeholder' => esc_html__('eg: Tab title', 'rey-core'),
			],
		],
		'priority' => [
			'type'        => 'text',
			'label'       => esc_html__('Priority', 'rey-core'),
			'default'       => 40,
		],
	],
] );
