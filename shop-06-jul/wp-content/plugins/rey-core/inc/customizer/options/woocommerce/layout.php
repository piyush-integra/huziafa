<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'woocommerce_product_catalog';

reycore_customizer__title([
	'title'       => esc_html__('Columns', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'bottom',
	'upper'       => true,
	'priority'    => 0,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'woocommerce_catalog_columns_tablet',
    'label'       => esc_html__('Products per row (tablet)', 'rey-core'),
	'section'     => $section,
	'default'     => 2,
	'priority'    => 1,
	'choices'     => [
		'min'  => 1,
		'max'  => 4,
		'step' => 1,
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'woocommerce_catalog_columns_mobile',
    'label'       => esc_html__('Products per row (mobile)', 'rey-core'),
	'section'     => $section,
	'default'     => 2,
	'priority'    => 1,
	'choices'     => [
		'min'  => 1,
		'max'  => 2,
		'step' => 1,
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'woocommerce_catalog_mobile_listview',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Enable list view on mobiles', 'rey-core' ),
		__('This will make the mobile view more compact, where the thumnails is on the side and content on the right.', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
	'priority'    => 1,
	'active_callback' => [
		[
			'setting'  => 'woocommerce_catalog_columns_mobile',
			'operator' => '==',
			'value'    => '1',
		],
	],
] );


// reycore_customizer__separator([
// 	'section' => $section,
// 	'id'      => 'a1',
// ]);


/* ------------------------------------ Grid options ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Grid options', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'loop_grid_layout',
	'label'       => esc_html__( 'Grid Layout', 'rey-core' ),
	'section'     => $section,
	'default'     => 'default',
	'choices'     => [
		'default' => esc_html__( 'Default', 'rey-core' ),
		'masonry' => esc_html__( 'Masonry', 'rey-core' ),
		'masonry2' => esc_html__( 'Masonry V2', 'rey-core' ),
		'metro' => esc_html__( 'Metro (squares)', 'rey-core' ),
		'scattered' => esc_html__( 'Scattered', 'rey-core' ),
		'scattered2' => esc_html__( 'Scattered Mixed & Random', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'loop_gap_size',
	'label'       => esc_html__( 'Gap size', 'rey-core' ),
	'section'     => $section,
	'default'     => 'default',
	'choices'     => [
		'no'  => __( 'No gaps', 'rey-core' ),
		'line'  => __( 'Line', 'rey-core' ),
		'narrow'  => __( 'Narrow', 'rey-core' ),
		'default'  => __( 'Default', 'rey-core' ),
		'extended'  => __( 'Extended', 'rey-core' ),
		'wide'  => __( 'Wide', 'rey-core' ),
		'wider'  => __( 'Wider', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'loop_pagination',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Pagination type', 'rey-core' ),
		__('Select the type of pagination you want to be displayed after the products.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 'paged',
	'choices'     => [
		'paged' => esc_html__( 'Paged', 'rey-core' ),
		'load-more' => esc_html__( 'Load More Button (via Ajax)', 'rey-core' ),
		'infinite' => esc_html__( 'Infinite loading (via Ajax)', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'loop_pagination_ajax_counter',
	'label'       => esc_html__( 'Add counter to button.', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'loop_pagination',
			'operator' => 'in',
			'value'    => ['load-more', 'infinite'],
		],
	],
	'rey_group_start' => [
		'label'       => esc_html__( 'Ajax Pagination Options', 'rey-core' ),
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'loop_pagination_ajax_text',
	'label'    => esc_html__( 'Button Text', 'rey-core' ),
	'section'  => $section,
	'input_attrs' => [
		'placeholder'  => esc_html__( 'eg: SHOW MORE', 'rey-core' ),
	],
	'default' => '',
	'active_callback' => [
		[
			'setting'  => 'loop_pagination',
			'operator' => 'in',
			'value'    => ['load-more', 'infinite'],
		],
	],
] );
ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'loop_pagination_ajax_end_text',
	'label'    => esc_html__( 'Button End Text', 'rey-core' ),
	'section'  => $section,
	'input_attrs' => [
		'placeholder'  => esc_html__( 'eg: END', 'rey-core' ),
	],
	'default' => '',
	'active_callback' => [
		[
			'setting'  => 'loop_pagination',
			'operator' => 'in',
			'value'    => ['load-more', 'infinite'],
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'loop_pagination_ajax_history',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Add history marker for URLs.', 'rey-core' ),
		__('If enabled, each time a new batch of products is loaded, the URL will change like in pagination mode.', 'rey-core')
	),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'loop_pagination',
			'operator' => 'in',
			'value'    => ['load-more', 'infinite'],
		],
	],
	'rey_group_end' => true
] );


/* ------------------------------------ Product options ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Product item options', 'rey-core'),
	'description' => esc_html__('Customize the product item layout.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'loop_skin',
	'label'       => esc_html__('Product Skin', 'rey-core'),
	'description' => __('Select a skin for the product item.', 'rey-core'),
	'section'     => $section,
	'default'     => 'basic',
	'choices'     => [],
]);


/* ------------------------------------ Basic Skin options ------------------------------------ */

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'loop_hover_animation',
	'label'       => esc_html__('Hover animation', 'rey-core'),
	'description' => __('Select if products should have an animation effect on hover.', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
	'choices'     => array(
		'1' => esc_html__('Enable', 'rey-core'),
		'2' => esc_html__('Disable', 'rey-core')
	),
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '==',
			'value'    => 'basic',
		],
	],
	'rey_group_start' => [
		'label'       => esc_html__( 'Basic Skin Options', 'rey-core' ),
	]
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'slider',
	'settings'    => 'loop_basic_inner_padding',
	'label'       => esc_html__( 'Content Inner padding', 'rey-core' ),
	'section'     => $section,
	'default'     => 0,
	'transport'       => 'auto',
	'choices'     => [
		'min'  => 0,
		'max'  => 100,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '==',
			'value'    => 'basic',
		],
	],
	'output'          => [
		[
			'element'  		   => ':root',
			'property' 		   => '--woocommerce-loop-basic-padding',
			'units' 		   => 'px',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'            => 'color',
	'settings'        => 'loop_basic_bg_color',
	'label'           => __( 'Background Color', 'rey-core' ),
	'section'         => $section,
	'default'         => '',
	'choices'         => [
		'alpha'          => true,
	],
	'transport'       => 'auto',
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '==',
			'value'    => 'basic',
		],
	],
	'output'          => [
		[
			'element'  		   => ':root',
			'property' 		   => '--woocommerce-loop-basic-bgcolor',
		],
	],
	'rey_group_end' => true
] );


/* ------------------------------------ Wrapped Skin options ------------------------------------ */

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'wrapped_loop_hover_animation',
	'label'       => esc_html__('Hover animation', 'rey-core'),
	'description' => __('Select if products should have an animation effect on hover.', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
	'choices'     => [
		'1' => esc_html__('Enable', 'rey-core'),
		'2' => esc_html__('Disable', 'rey-core')
	],
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '==',
			'value'    => 'wrapped',
		],
	],
	'rey_group_start' => [
		'label'       => esc_html__( 'Wrapped Skin Options', 'rey-core' ),
	]
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'slider',
	'settings'    => 'wrapped_loop_basic_inner_padding',
	'label'       => esc_html__( 'Content Inner padding', 'rey-core' ),
	'section'     => $section,
	'default'     => 40,
	'transport'       => 'auto',
	'choices'     => [
		'min'  => 0,
		'max'  => 100,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '==',
			'value'    => 'wrapped',
		],
	],
	'output'          => [
		[
			'element'  		   => '.woocommerce ul.products li.product.rey-wc-skin--wrapped',
			'property' 		   => '--woocommerce-loop-wrapped-padding',
			'units' 		   => 'px',
		],
	],
	'responsive' => true
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'color',
	'settings'    => 'wrapped_loop_overlay_color',
	'label'       => esc_html__( 'Overlay Color', 'rey-core' ),
	'section'     => $section,
	'default'     => 'rgba(0, 0, 0, 0.3)',
	'choices'     => [
		'alpha' => true,
	],
	'output'      		=> [
		[
			'element'  		=> '.woocommerce ul.products li.product.rey-wc-skin--wrapped',
			'property' 		=> '--woocommerce-loop-wrapped-ov-color',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '==',
			'value'    => 'wrapped',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'color',
	'settings'    => 'wrapped_loop_overlay_color_hover',
	'label'       => esc_html__( 'Overlay Hover Color', 'rey-core' ),
	'section'     => $section,
	'default'     => 'rgba(0, 0, 0, 0.45)',
	'choices'     => [
		'alpha' => true,
	],
	'output'      		=> [
		[
			'element'  		=> '.woocommerce ul.products li.product.rey-wc-skin--wrapped',
			'property' 		=> '--woocommerce-loop-wrapped-ov-color-hover',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '==',
			'value'    => 'wrapped',
		],
	],
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'wrapped_loop_item_height',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Item Height', 'rey-core' ),
		__('Select a custom height for the product images. Don\'t forget unit.', 'rey-core')
	),
	'input_attrs' => [
		'placeholder'  => esc_html__( 'eg: 300px', 'rey-core' ),
	],
	'section'     => $section,
	'default'     => '',
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '==',
			'value'    => 'wrapped',
		],
	],
	'output'      		=> [
		[
			'element'  		=> '.woocommerce ul.products li.product.rey-wc-skin--wrapped .rey-productThumbnail-extra, .woocommerce ul.products li.product.rey-wc-skin--wrapped .rey-productThumbnail__second, .woocommerce ul.products li.product.rey-wc-skin--wrapped .wp-post-img',
			'property' 		=> 'height',
		],
	],
	'responsive' => true,
	'rey_group_end' => true
] );

/* ------------------------------------ Misc options ------------------------------------ */

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'loop_alignment',
	'label'    => reycore_customizer__title_tooltip(
		__('Text Alignment', 'rey-core'),
		__('Select an alignment for the content in product items.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 'left',
	'choices'     => [
		'left' => esc_html__('Left', 'rey-core'),
		'center' => esc_html__('Center', 'rey-core'),
		'right' => esc_html__('Right', 'rey-core')
	],
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'loop_extra_media',
	'label'       => esc_html__( 'Extra Images Display', 'rey-core' ),
	'section'     => $section,
	'default'     => 'second',
	'choices'     => [
		'no' => esc_html__( 'Disabled', 'rey-core' ),
		'second' => esc_html__( '2nd image on hover', 'rey-core' ),
		'slideshow' => esc_html__( 'Slideshow', 'rey-core' ),
	],
] );

/* ------------------------------------ Slideshow settings ------------------------------------ */


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'loop_slideshow_nav',
	'label'       => esc_html__( 'Slideshow navigation', 'rey-core' ),
	'section'     => $section,
	'default'     => 'dots',
	'choices'     => [
		'dots' => esc_html__( 'Show Dots only', 'rey-core' ),
		'arrows' => esc_html__( 'Show Arrows only', 'rey-core' ),
		'both' => esc_html__( 'Show Both', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'loop_extra_media',
			'operator' => '==',
			'value'    => 'slideshow',
		],
	],
	'rey_group_start' => [
		'label'       => esc_html__( 'Slideshow Options', 'rey-core' ),
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'color',
	'settings'    => 'loop_slideshow_nav_color',
	'label'       => esc_html__( 'Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'active_callback' => [
		[
			'setting'  => 'loop_extra_media',
			'operator' => '==',
			'value'    => 'slideshow',
		],
	],
	'output'          => [
		[
			'element'  		   => ':root',
			'property' 		   => '--woocommerce-loop-nav-color',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'loop_slideshow_nav_color_invert',
	'label'       => esc_html__( 'Adapt (invert) colors on slide', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'loop_extra_media',
			'operator' => '==',
			'value'    => 'slideshow',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'loop_slideshow_nav_hover_dots',
	'label'       => esc_html__( 'Change slide on dot hover', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'loop_extra_media',
			'operator' => '==',
			'value'    => 'slideshow',
		],
		[
			'setting'  => 'loop_slideshow_nav',
			'operator' => 'in',
			'value'    => ['dots', 'both'],
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'loop_slideshow_nav_max',
	'label'       => esc_html__( 'Maximum images', 'rey-core' ),
	'section'     => $section,
	'default'     => 4,
	'choices'     => [
		'min'  => 1,
		'max'  => 20,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'loop_extra_media',
			'operator' => '==',
			'value'    => 'slideshow',
		],
	],
	'rey_group_end' => true
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_items_eq',
	'label'    => reycore_customizer__title_tooltip(
		__('Equalize titles height', 'rey-core'),
		__('If enabled, the product titles height will be equalized on all sibilings on each row.', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '!=',
			'value'    => 'wrapped',
		],
	],
] );


/* ------------------------------------ Sidebar options ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Sidebar', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'catalog_sidebar_position',
	'label'       => esc_html__( 'Sidebar Position', 'rey-core' ),
	'description'       => esc_html__( 'Select the placement of sidebar or disable it. Default is right.', 'rey-core' ),
	'section'     => $section,
	'default'     => 'right',
	'choices'     => [
		'right' => esc_html__( 'Right', 'rey-core' ),
		'left' => esc_html__( 'Left', 'rey-core' ),
		'disabled' => esc_html__( 'Disabled', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'slider',
	'settings'    => 'shop_sidebar_size',
	'label'       => esc_html__( 'Sidebar Size', 'rey-core' ),
	'section'     => $section,
	'default'     => 16,
	'choices'     => [
		'min'  => 10,
		'max'  => 60,
		'step' => 1,
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--woocommerce-sidebar-size',
			'units'    		=> '%',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'sidebar_title_styles',
	'label'       => esc_html__( 'Custom sidebar titles styles', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'typography',
	'settings'    => 'sidebar_title_typo',
	'label'       => esc_attr__('Sidebar title', 'rey-core'),
	'section'     => $section,
	'default'     => [
		'font-family'      => '',
		'font-size'      => '',
		'line-height'    => '',
		'letter-spacing' => '',
		'font-weight' => '',
		'text-transform' => '',
		'variant' => '',
		'color' => '',
	],
	'output' => [
		[
			'element' => '.rey-ecommSidebar .widget-title',
		],
	],
	'load_choices' => true,
	'transport' => 'auto',
	'responsive' => true,
	'active_callback' => [
		[
			'setting'  => 'sidebar_title_styles',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Sidebar title options', 'rey-core')
	],
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'sidebar_title_layouts',
	'label'       => esc_html__( 'Sidebar Title styles', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'' => esc_html__( 'Default', 'rey-core' ),
		'bline' => esc_html__( 'Bottom Lined', 'rey-core' ),
		'sline' => esc_html__( 'Side Line', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'sidebar_title_styles',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_end' => true
] );


/* ------------------------------------ Title typography ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Typography', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'typography',
	'settings'    => 'typography_catalog_product_title',
	'label'       => esc_attr__('Product title', 'rey-core'),
	'section'     => $section,
	'default'     => array(
		'font-family'      => '',
		'font-size'      => '',
		'line-height'    => '',
		'letter-spacing' => '',
		'font-weight' => '',
		'variant' => '',
		'color' => '',
	),
	'output' => array(
		array(
			'element' => '.woocommerce ul.products li.product .woocommerce-loop-product__title',
		),
	),
	'load_choices' => true,
	'transport' => 'auto',
	'responsive' => true,
));


reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-woocommerce/#product-catalog-layout',
	'section' => $section
]);
