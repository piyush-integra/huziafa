<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$section = 'shop_product_section_layout';

/**
 * PRODUCT PAGE - layout settings
 */

ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('Product Page - Layout', 'rey-core'),
	'priority'       => 15,
	'panel'			=> 'woocommerce'
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'wc_product_layout_presets',
	'label'       => esc_html__( 'Layout Presets', 'rey-core' ),
	'description' => esc_html__( 'These are product page layout presets from each demo.', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'' => esc_html__( 'Default', 'rey-core' ),
		'london' => esc_html__( 'London Demo', 'rey-core' ),
		'valencia' => esc_html__( 'Valencia Demo', 'rey-core' ),
		'amsterdam' => esc_html__( 'Amsterdam Demo', 'rey-core' ),
		'newyork' => esc_html__( 'New York Demo', 'rey-core' ),
		'tokyo' => esc_html__( 'Tokyo Demo', 'rey-core' ),
		'beijing' => esc_html__( 'Beijing Demo', 'rey-core' ),
		'milano' => esc_html__( 'Milano Demo', 'rey-core' ),
		'melbourne' => esc_html__( 'Melbourne Demo', 'rey-core' ),
		'paris' => esc_html__( 'Paris Demo', 'rey-core' ),
		'stockholm' => esc_html__( 'Stockholm Demo', 'rey-core' ),
		'frankfurt' => esc_html__( 'Frankfurt Demo', 'rey-core' ),
	],
	'preset' => [
		'london' => [
			'settings' => [
				'single_skin' => 'fullscreen',
				'single_skin_default_flip' => false,
				'summary_size' => 45,
				'product_page_summary_fixed' => true,
				'product_gallery_layout' => 'cascade',
				'product_page_gallery_zoom' => true,
				'single_skin_fullscreen_stretch_gallery' => true,
				'single_skin_cascade_bullets' => true,
			]
		],
		'valencia' => [
			'settings' => [
				'single_skin' => 'compact',
				'single_skin_default_flip' => false,
				'summary_size' => 36,
				'product_page_summary_fixed' => false,
				'product_gallery_layout' => 'grid',
				'product_page_gallery_zoom' => true,
			],
		],
		'amsterdam' => [
			'settings' => [
				'single_skin' => 'fullscreen',
				'single_skin_default_flip' => false,
				'summary_size' => 40,
				'product_page_summary_fixed' => true,
				'product_gallery_layout' => 'vertical',
				'product_page_gallery_zoom' => true,
			],
		],
		'newyork' => [
			'settings' => [
				'single_skin' => 'default',
				'single_skin_default_flip' => false,
				'summary_size' => 40,
				'product_page_summary_fixed' => false,
				'product_gallery_layout' => 'grid',
				'product_page_gallery_zoom' => true,
			],
		],
		'tokyo' => [
			'settings' => [
				'single_skin' => 'default',
				'single_skin_default_flip' => false,
				'summary_size' => 44,
				'product_page_summary_fixed' => false,
				'product_gallery_layout' => 'vertical',
				'product_page_gallery_zoom' => true,
			],
		],
		'beijing' => [
			'settings' => [
				'single_skin' => 'default',
				'single_skin_default_flip' => false,
				'summary_size' => 44,
				'product_page_summary_fixed' => false,
				'product_gallery_layout' => 'horizontal',
				'product_page_gallery_zoom' => true,
			],
		],
		'milano' => [
			'settings' => [
				'single_skin' => 'default',
				'single_skin_default_flip' => false,
				'summary_size' => 38,
				'product_page_summary_fixed' => true,
				'product_gallery_layout' => 'cascade-scattered',
				'product_page_gallery_zoom' => true,
			],
		],
		'melbourne' => [
			'settings' => [
				'single_skin' => 'default',
				'single_skin_default_flip' => false,
				'summary_size' => 45,
				'product_page_summary_fixed' => false,
				'product_gallery_layout' => 'vertical',
				'product_page_gallery_zoom' => true,
			],
		],
		'paris' => [
			'settings' => [
				'single_skin' => 'fullscreen',
				'single_skin_default_flip' => false,
				'summary_size' => 40,
				'product_page_summary_fixed' => false,
				'product_gallery_layout' => 'vertical',
				'product_page_gallery_zoom' => true,
			],
		],
		'stockholm' => [
			'settings' => [
				'single_skin' => 'compact',
				'single_skin_default_flip' => false,
				'summary_size' => 36,
				'product_page_summary_fixed' => false,
				'product_gallery_layout' => 'cascade',
				'product_page_gallery_zoom' => true,
				'product_content_layout' => 'blocks',
			],
		],
		'frankfurt' => [
			'settings' => [
				'single_skin' => 'default',
				'single_skin_default_flip' => false,
				'summary_size' => 45,
				'summary_padding' => 50,
				'summary_bg_color' => '#ebf5fb',
				'product_page_summary_fixed' => false,
				'product_gallery_layout' => 'vertical',
				'product_page_gallery_zoom' => true,
				'product_content_layout' => 'blocks',
			],
		],
	],
] );

reycore_customizer__title([
	'title'       => esc_html__('Product Page Layout', 'rey-core'),
	'description' => esc_html__('Customize the product page layout', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'single_skin',
    'label'       => esc_html__('Skin', 'rey-core'),
    'description' => __('Select the product page\'s skin (layout).', 'rey-core'),
	'section'     => $section,
	'default'     => 'default',
	// 'priority'    => 10,
    'choices'     => class_exists('ReyCore_WooCommerce_Single') ? ReyCore_WooCommerce_Single::getInstance()->get_single_skins() : []
));


/* ------------------------------------ Fullscreen Options ------------------------------------ */

// Stretch Gallery (for fullscreen & Cascade gallery)
ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_skin_fullscreen_stretch_gallery',
	'label'       => esc_html__( 'Stretch Gallery [Full-screen]', 'rey-core' ),
    'description' => __('This option will stretch the gallery.', 'rey-core'),
	'section'     => $section,
	'default'     => false,
	// 'priority'    => 110,
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => '==',
			'value'    => 'fullscreen',
		],
		[
			'setting'  => 'product_gallery_layout',
			'operator' => '==',
			'value'    => 'cascade',
		],
	],
	'rey_group_start' => [
		'label'       => esc_html__( 'Fullscreen Options', 'rey-core' ),
		'active_callback' => [
			[
				'setting'  => 'single_skin',
				'operator' => '==',
				'value'    => 'fullscreen',
			],
		],
	]
] );

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_skin_cascade_bullets',
	'label'       => esc_html__( 'Bullets Navigation (Cascade gallery)', 'rey-core' ),
    'description' => __('This option will add bullets (dots) navigation for the Cascade gallery.', 'rey-core'),
	'section'     => $section,
	'default'     => true,
	// 'priority'    => 110,
	'active_callback' => [
		[
			'setting'  => 'product_gallery_layout',
			'operator' => '==',
			'value'    => 'cascade',
		],
		[
			'setting'  => 'single_skin',
			'operator' => '!=',
			'value'    => 'compact',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'            => 'color',
	'settings'        => 'single_skin_fullscreen_gallery_color',
	'label'           => __( 'Gallery Background Color', 'rey-core' ),
	'section'         => $section,
	'default'         => '',
	'choices'         => [
		'alpha'          => true,
	],
	// 'priority'        => 120,
	'transport'       => 'auto',
	'active_callback' => [
		[
			'setting'       => 'single_skin',
			'operator'      => '==',
			'value'         => 'fullscreen',
		],
	],
	'output'          => [
		[
			'element'  		   => ':root',
			'property' 		   => '--woocommerce-single-fs-gallery-color',
		],
	],
] );


ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_skin_fullscreen_custom_height',
	'label'       => esc_html__( 'Custom Summary Height', 'rey-core' ),
    'description' => __('This option will allow setting a custom summary height.', 'rey-core'),
	'section'     => $section,
	'default'     => false,
	// 'priority'    => 125,
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => '==',
			'value'    => 'fullscreen',
		],
		[
			'setting'  => 'product_gallery_layout',
			'operator' => 'in',
			'value'    => ['vertical', 'horizontal'],
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'slider',
	'settings'    => 'single_skin_fullscreen_summary_height',
	'label'       => esc_html__( 'Summary Min. Height (vh)', 'rey-core' ),
	'section'     => $section,
	'default'     => 100,
	'choices'     => [
		'min'  => 35,
		'max'  => 100,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => '==',
			'value'    => 'fullscreen',
		],
		[
			'setting'  => 'product_gallery_layout',
			'operator' => 'in',
			'value'    => ['vertical', 'horizontal'],
		],
		[
			'setting'  => 'single_skin_fullscreen_custom_height',
			'operator' => '==',
			'value'    => true,
		],
	],
	'output'      		=> [
		[
			'media_query'	=> '@media (min-width: 1025px)',
			'element'  		=> ':root',
			'property' 		=> '--woocommerce-fullscreen-gallery-height',
			'units'    		=> 'vh',
		],
	],
] );

/*
ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_skin_fullscreen__inherit_header_pos',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Inherit Header position', 'rey-core' ),
		esc_html__( 'By default header position is absolute, to be over the summary.', 'rey-core' )
	),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => '==',
			'value'    => 'fullscreen',
		],
	],
] );
*/

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'single_skin_fullscreen__top_padding',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Top padding', 'rey-core' ) . ' (px)',
		esc_html__( 'Customize the top padding.', 'rey-core' )
	),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'max'  => 400,
		'step' => 1,
	],
	'transport' => 'auto',
	'output'    => [
		[
			'element'  		=> ':root',
			'property' 		=> '--woocommerce-fullscreen-top-padding',
			'units'    		=> 'px',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => '==',
			'value'    => 'fullscreen',
		],
	],
	'rey_group_end' => true
] );



/* ------------------------------------ Product summary ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Product Summary', 'rey-core'),
	'description' => esc_html__('Customize the product summary block\'s layout.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => '!=',
			'value'    => 'compact',
			],
	],
]);

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_skin_default_flip',
	'label'       => esc_html__( 'Flip Gallery & Summary', 'rey-core' ),
    'description' => __('This option will flip the positions of product summary (title, add to cart button) with the images gallery.', 'rey-core'),
	'section'     => $section,
	'default'     => false,
	// 'priority'    => 30,
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => 'in',
			'value'    => ['default', 'fullscreen'],
		],
	],
] );

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'slider',
	'settings'    => 'summary_size',
	'label'       => esc_html__( 'Summary Size', 'rey-core' ),
	'description' => __('Control the product summary content size.', 'rey-core'),
	'section'     => $section,
	// 'priority'     => 40,
	'default'     => 36,
	'choices'     => [
		'min'  => 20,
		'max'  => 60,
		'step' => 1,
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--woocommerce-summary-size',
			'units'    		=> '%',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => '!=',
			'value'    => 'compact',
		],
	],
] );


ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'slider',
	'settings'    => 'summary_padding',
	'label'       => esc_html__( 'Summary Padding', 'rey-core' ),
	'description' => __('Control the product summary content padding.', 'rey-core'),
	'section'     => $section,
	'default'     => 0,
	'choices'     => [
		'min'  => 0,
		'max'  => 150,
		'step' => 1,
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			// 'media_query'	=> '@media (min-width: 1025px)',
			'element'  		=> ':root',
			'property' 		=> '--woocommerce-summary-padding',
			'units'    		=> 'px',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => 'in',
			'value'    => ['default'],
		],
	],
	'responsive' => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'            => 'color',
	'settings'        => 'summary_bg_color',
	'label'           => __( 'Background Color', 'rey-core' ),
	'section'         => $section,
	// 'priority'     => 44,
	'default'         => '',
	'choices'         => [
		'alpha'          => true,
	],
	'transport'       => 'auto',
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => 'in',
			'value'    => ['default', 'fullscreen'],
		],
	],
	'output'          => [
		[
			'element'  		   => ':root',
			'property' 		   => '--woocommerce-summary-bgcolor',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'            => 'color',
	'settings'        => 'summary_text_color',
	'label'           => __( 'Text Color', 'rey-core' ),
	'section'         => $section,
	'default'         => '',
	'choices'         => [
		'alpha'          => true,
	],
	'transport'       => 'auto',
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => 'in',
			'value'    => ['default', 'fullscreen'],
		],
	],
	'output'          => [
		[
			'element'  		   => '.woocommerce div.product div.summary, .woocommerce div.product div.summary a, .woocommerce div.product .rey-postNav .nav-links a,  .woocommerce div.product .rey-productShare h5, .woocommerce div.product form.cart .variations label, .woocommerce div.product .product_meta, .woocommerce div.product .product_meta a',
			'property' 		   => 'color',
		],
	],
] );



ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_page_summary_fixed',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Fixed Summary', 'rey-core' ),
		esc_html__( 'This option will make the product summary fixed upon page scrolling, until the product gallery images are outside viewport.', 'rey-core' )
	),
	'section'     => $section,
	'default'     => false,
	// 'priority'    => 50,
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => '!=',
			'value'    => 'compact',
		],
	],
] );

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'product_page_summary_fixed__offset',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Top Offset', 'rey-core' ) . ' (px)',
		esc_html__( 'Customize the top sticky offset. Useful ONLY with non-fixed header.', 'rey-core' )
	),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'max'  => 400,
		'step' => 1,
	],
	'output'          => [
		[
			'element'  => '.--fixed-summary',
			'property' => '--woocommerce-fixedsummary-offset',
			'units'    => 'px'
		],
	],
	'active_callback' => [
		[
			'setting'  => 'product_page_summary_fixed',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Fixed summary options', 'rey-core')
	],
	'rey_group_end' => true
] );

/* ------------------------------------ Product gallery ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Product Gallery', 'rey-core'),
	'description' => esc_html__('Customize the product page gallery style.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'product_gallery_layout',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Gallery layout', 'rey-core' ),
		__('Select the gallery layout.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 'vertical',
	// 'priority'    => 70,
	'choices'     => [
		'vertical' => esc_html__('Vertical Layout', 'rey-core'),
		'horizontal' => esc_html__('Horizontal Layout', 'rey-core'),
		'grid' => esc_html__('Grid Layout', 'rey-core'),
		'cascade' => esc_html__('Cascade Layout', 'rey-core'),
		'cascade-grid' => esc_html__('Cascade - Grid Layout', 'rey-core'),
		'cascade-scattered' => esc_html__('Cascade - Scattered Layout', 'rey-core'),
		// TODO:
		// 'default' => esc_html__('WooCommerce Default', 'rey-core'),
		// - future: centered slider
		// - future: full-screen with side content
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_page_gallery_zoom',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Enable Hover Zoom', 'rey-core' ),
		__('This option will enable zooming the main image by hovering it.', 'rey-core')
	),
	'section'     => $section,
	'default'     => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_page_gallery_lightbox',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Enable Lightbox', 'rey-core' ),
		__('This option enables the lightbox for images when clicking on the icon or images.', 'rey-core')
	),
	'section'     => $section,
	'default'     => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_page_gallery_arrow_nav',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Arrows Navigation', 'rey-core' ),
		__('This option will enable arrows navigation.', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'product_gallery_layout',
			'operator' => 'in',
			'value'    => ['vertical', 'horizontal'],
		],
	],
] );

reycore_customizer__title([
	'title'   => esc_html__('THUMBNAILS', 'rey-core'),
	'section' => $section,
	'size'    => 'xs',
	'border'  => 'none',
	'active_callback' => [
		[
			'setting'  => 'product_gallery_layout',
			'operator' => 'in',
			'value'    => ['vertical', 'horizontal'],
		],
	],
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'product_gallery_thumbs_max',
	'label'       => esc_html__( 'Max. visible thumbs', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'min'  => 0,
		'max'  => 10,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'product_gallery_layout',
			'operator' => 'in',
			'value'    => ['vertical', 'horizontal'],
		],
	],
	'output'          => [
		[
			'element'  		   => ':root',
			'property' 		   => '--woocommerce-gallery-max-thumbs',
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Thumbs options', 'rey-core')
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'product_gallery_thumbs_nav_style',
	'label'       => esc_html__( 'Thumbs Navigation Style', 'rey-core' ),
	'section'     => $section,
	'default'     => 'boxed',
	'choices'     => [
		'boxed' => esc_html__( 'Boxed', 'rey-core' ),
		'minimal' => esc_html__( 'Minimal', 'rey-core' ),
		'edges' => esc_html__( 'Edges', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'product_gallery_layout',
			'operator' => 'in',
			'value'    => ['vertical', 'horizontal'],
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_gallery_thumbs_flip',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Flip thumbs position', 'rey-core' ),
		__('This option will flip the thumbnail list on the other side of the main image.', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'product_gallery_layout',
			'operator' => '==',
			'value'    => 'vertical',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_gallery_thumbs_disable_cropping',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Disable cropping', 'rey-core' ),
		__('By default WooCommerce is cropping the gallery thumbnails. You can disable this with this option and contain the image in its natural sizes.', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'product_gallery_layout',
			'operator' => 'in',
			'value'    => ['vertical', 'horizontal'],
		],
	],
	'rey_group_end' => true
] );

reycore_customizer__title([
	'title'   => esc_html__('MOBILE GALLERY', 'rey-core'),
	'section' => $section,
	'size'    => 'xs',
	'border'  => 'none',
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'product_gallery_mobile_nav_style',
	'label'       => esc_html__( 'Navigation Style', 'rey-core' ),
	'section'     => $section,
	'default'     => 'bars',
	'choices'     => [
		'bars' => esc_html__( 'Norizontal Bars', 'rey-core' ),
		'circle' => esc_html__( 'Circle Bullets', 'rey-core' ),
		'thumbs' => esc_html__( 'Thumbnails', 'rey-core' ),
	],
	'rey_group_start' => [
		'label' => esc_html__('Mobile gallery options', 'rey-core')
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_gallery_mobile_arrows',
	'label'       => esc_html__( 'Show Arrows', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_page_scroll_top_after_variation_change',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Scroll top on variation change', 'rey-core'),
		esc_html__( 'On mobiles, after a variation is changed, the page will animate and scroll back to the gallery, so that any image swap is visible.', 'rey-core' )
	),
	'section'     => $section,
	'default'     => false,
	'rey_group_end' => true
] );



reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-woocommerce/#product-page-layout',
	'section' => $section
]);
