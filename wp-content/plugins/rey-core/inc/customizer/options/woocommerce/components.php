<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'woocommerce_product_catalog_components';

ReyCoreKirki::add_section($section, array(
    'title'          => esc_html__('Product Catalog - Components', 'rey-core'),
	'priority'       => 11,
	'panel'			=> 'woocommerce'
));

/* ------------------------------------ GENERAL COMPONENTS ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('General components', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'none',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'loop_add_to_cart',
	'label'       => esc_html__( 'Enable Add to Cart button', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'loop_ajax_variable_products',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Ajax show variations', 'rey-core' ),
		__('If enabled, variable products with "Select Options" button, will show the variations form on click.', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'loop_add_to_cart',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Add to cart button options', 'rey-core')
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'loop_add_to_cart_style',
	'label'       => esc_html__( 'Button style', 'rey-core' ),
	'section'     => $section,
	'default'     => 'under',
	'priority'    => 10,
	'choices'     => [
		'under' => esc_html__( 'Default (underlined)', 'rey-core' ),
		'hover' => esc_html__( 'Hover Underlined', 'rey-core' ),
		'primary' => esc_html__( 'Primary', 'rey-core' ),
		'primary-out' => esc_html__( 'Primary Outlined', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'loop_add_to_cart',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'color',
	'settings'    => 'loop_add_to_cart_accent_color',
	'label'       => esc_html__( 'Accent Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> '.woocommerce ul.products li.product .rey-productInner .button, .tinvwl-loop-button-wrapper',
			'property' 		=> '--accent-color',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'loop_add_to_cart',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'color',
	'settings'    => 'loop_add_to_cart_accent_hover_color',
	'label'       => esc_html__( 'Accent Hover Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> '.woocommerce ul.products li.product .rey-productInner .button, .tinvwl-loop-button-wrapper',
			'property' 		=> '--accent-hover-color',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'loop_add_to_cart',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'color',
	'settings'    => 'loop_add_to_cart_accent_text_color',
	'label'       => esc_html__( 'Accent Text Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> '.woocommerce ul.products li.product .rey-productInner .button, .tinvwl-loop-button-wrapper',
			'property' 		=> '--accent-text-color',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'loop_add_to_cart',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'loop_add_to_cart_mobile',
	'label'       => esc_html__( 'Show button on mobiles', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'loop_add_to_cart',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_end' => true
] );


require REY_CORE_DIR . 'inc/customizer/options/woocommerce/components--quickview.php';


ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'loop_show_brads',
    'label'       => esc_html__('Brands', 'rey-core'),
    'description' => __('Choose if you want to display product brads. ', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
    'choices'     => array(
        '1' => esc_attr__('Show', 'rey-core'),
        '2' => esc_attr__('Hide', 'rey-core')
    )
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'loop_show_categories',
    'label'       => esc_html__('Category', 'rey-core'),
    'description' => __('Choose if you want to display product categories.', 'rey-core'),
	'section'     => $section,
	'default'     => '2',
    'choices'     => array(
        '1' => esc_attr__('Show', 'rey-core'),
        '2' => esc_attr__('Hide', 'rey-core')
    )
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'loop_categories__exclude_parents',
	'label'       => esc_html__( 'Exclude parent categories', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'loop_show_categories',
			'operator' => '==',
			'value'    => '1',
		],
	],
	'rey_group_start' => [
		'label'  => esc_html__('Options', 'rey-core'),
	],
	'rey_group_end' => true
] );


ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'loop_ratings',
    'label'       => esc_html__('Ratings', 'rey-core'),
    'description' => __('Choose if you want ratings to be displayed.', 'rey-core'),
	'section'     => $section,
	'default'     => '2',
    'choices'     => array(
        '1' => esc_attr__('Show', 'rey-core'),
        '2' => esc_attr__('Hide', 'rey-core')
    )
));


ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'loop_new_badge',
    'label'       => esc_html__('New Badge', 'rey-core'),
    'description' => __('Choose if you want to show a "New" badge on products newer than 30 days.', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
    'choices'     => array(
        '1' => esc_attr__('Show', 'rey-core'),
        '2' => esc_attr__('Hide', 'rey-core')
    )
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'loop_short_desc',
    'label'       => esc_html__('Short description', 'rey-core'),
    'description' => __('Choose if you want to show the product excerpt.', 'rey-core'),
	'section'     => $section,
	'default'     => '2',
    'choices'     => array(
        '1' => esc_attr__('Show', 'rey-core'),
        '2' => esc_attr__('Hide', 'rey-core')
    )
));

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'loop_short_desc_limit',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Limit words', 'rey-core' ),
		esc_html__( 'Limit the number of words. 0 means full, not truncated.', 'rey-core' )
	),
	'section'  => $section,
	'default'  => 8,
	'active_callback' => [
		[
			'setting'  => 'loop_short_desc',
			'operator' => '==',
			'value'    => '1',
		],
	],
	'input_attrs' => [
		'data-control-class' => 'eg: 8'
	],
	'rey_group_start' => [
		'label' => esc_html__('Excerpt options', 'rey-core')
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'loop_short_desc_mobile',
	'label'       => esc_html__( 'Show on mobiles', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'loop_short_desc',
			'operator' => '==',
			'value'    => '1',
		],
	],
	'rey_group_end' => true
] );


ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'loop_sold_out_badge',
    'label'       => esc_html__('Sold Out Badge', 'rey-core'),
    'description' => __('Choose if you want to show a "SOLD OUT" badge on products out of stock.', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
    'choices'     => array(
        '1' => esc_attr__('Show', 'rey-core'),
        'in-stock' => esc_attr__('Show - In Stock', 'rey-core'),
        '2' => esc_attr__('Hide', 'rey-core')
    )
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'loop_featured_badge',
	'label'       => esc_html__( 'Featured Badge', 'rey-core' ),
	'section'     => $section,
	'default'     => 'hide',
	'choices'     => [
		'show' => esc_html__( 'Show', 'rey-core' ),
		'hide' => esc_html__( 'Hide', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'loop_featured_badge__text',
	'label'       => esc_html__( 'Text', 'rey-core' ),
	'section'     => $section,
	'default'     => esc_html__('FEATURED', 'rey-core'),
	'input_attrs'     => [
		'placeholder' => esc_html__('eg: FEATURED', 'rey-core'),
	],
	'active_callback' => [
		[
			'setting'  => 'loop_featured_badge',
			'operator' => '==',
			'value'    => 'show',
			],
	],
	'rey_group_start' => [
		'label' => esc_html__('Badge settings', 'rey-core')
	],
	'rey_group_end' => true
] );


if( class_exists('TInvWL_Public_AddToWishlist') && class_exists('ReyCore_WooCommerce_Wishlist') ):

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'toggle',
		'settings'    => 'loop_wishlist_enable',
		'label'       => esc_html__( 'Enable Wishlist button', 'rey-core' ),
		'section'     => $section,
		'default'     => true,
	] );

	$default_wishlist_pos = ReyCore_WooCommerce_Wishlist::tinvl_get_wishlist_default_position();

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'select',
		'settings'    => 'loop_wishlist_position',
		'label'       => esc_html__( 'Wishlist Position', 'rey-core' ),
		'section'     => $section,
		'default'     => $default_wishlist_pos,
		'choices'     => [
			'bottom' => esc_html__( 'Bottom', 'rey-core' ),
			'topright' => esc_html__( 'Thumb. top right', 'rey-core' ),
			'bottomright' => esc_html__( 'Thumb. bottom right', 'rey-core' ),
		],
		'active_callback' => [
			[
				'setting'  => 'loop_wishlist_enable',
				'operator' => '==',
				'value'    => true,
			],
		],
		'rey_group_start' => [
			'label' => esc_html__('Wishlist options', 'rey-core')
		],
		'rey_group_end' => true
	]);

endif;

/* ------------------------------------ PRICE & LABELS ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Price & Labels', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'loop_show_prices',
    'label'       => esc_html__('Price', 'rey-core'),
    'description' => __('Choose if you want to hide prices.', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
    'choices'     => array(
        '1' => esc_attr__('Show', 'rey-core'),
        '2' => esc_attr__('Hide', 'rey-core')
    )
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'loop_show_sale_label',
    'label'       => esc_html__('Sale/Discount Label', 'rey-core'),
    'description' => __('Choose if you want to display a sale/discount label products.', 'rey-core'),
	'section'     => $section,
	'default'     => 'percentage',
    'choices'     => array(
        '' => esc_attr__('Disabled', 'rey-core'),
        'sale' => esc_attr__('Sale Label (top right)', 'rey-core'),
        'percentage' => esc_attr__('Discount percentage', 'rey-core')
	),
	'active_callback' => [
		[
			'setting'  => 'loop_show_prices',
			'operator' => '==',
			'value'    => '1',
		],
	],
));


ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'loop_discount_label',
    'label'       => esc_html__('Discount Label Position', 'rey-core'),
    'description' => __('Choose the discount label position.', 'rey-core'),
	'section'     => $section,
	'default'     => 'price',
    'choices'     => array(
        'price' => esc_attr__('In Price', 'rey-core'),
        'top' => esc_attr__('Top Right', 'rey-core'),
	),
	'active_callback' => [
		[
			'setting'  => 'loop_show_sale_label',
			'operator' => '==',
			'value'    => 'percentage',
		],
		[
			'setting'  => 'loop_show_prices',
			'operator' => '==',
			'value'    => '1',
		],
	],
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'color',
	'settings'    => 'loop_discount_label_color',
	'label'       => __( 'Sale Price & Discount Color', 'rey-core' ),
	'section'     => $section,
    'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'active_callback' => [
		[
			'setting'  => 'loop_show_prices',
			'operator' => '==',
			'value'    => '1',
		],
	],
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--woocommerce-discount-color',
		],
	],

));

/* ------------------------------------ VARIATION ATTRIBUTES ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Variation Attributes', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'woocommerce_loop_variation',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Attributes Type', 'rey-core'),
		__('Choose if you want to display product variation swatches into product listing, by selecting which variation should be displayed.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 'disabled',
    'choices'     => array_merge([
		'disabled' => __('Disabled','rey-core')
	], class_exists('ReyCore_WooCommerce_Variations') ? ReyCore_WooCommerce_Variations::get_attributes_list() : [] )
]);


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'woocommerce_loop_variation_force_regular',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Force display as attribute', 'rey-core' ),
		esc_html__( 'This option will force display attributes that are not used for product variations (for example attributes that are shown in Specifications block).', 'rey-core' )
	),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'woocommerce_loop_variation',
			'operator' => '!=',
			'value'    => 'disabled',
		],
	],
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'woocommerce_loop_variation_position',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Position', 'rey-core'),
		__('Choose the position of the swatches.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 'after',
    'choices'     => [
		'first' => __('Before title','rey-core'),
		'before' => __('Before "Add to cart" button','rey-core'),
		'after' => __('After "Add to cart" button','rey-core'),
	],
	'active_callback' => [
		[
			'setting'  => 'woocommerce_loop_variation',
			'operator' => '!=',
			'value'    => 'disabled',
		],
	],
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'dimensions',
	'settings'    => 'woocommerce_loop_variation_size',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Attribute swatches sizes', 'rey-core'),
		__('Customize the sizes of the swatches.', 'rey-core')
	),
	'section'     => $section,
    'default'     => [
		'width'  => '',
		'height' => '',
	],
	'transport'   		=> 'auto',
	'output'      		=> [
		[
			'choice'      => 'width',
			'element'  		=> ':root',
			'property' 		=> '--woocommerce-swatches-width',
		],
		[
			'choice'      => 'height',
			'element'  		=> ':root',
			'property' 		=> '--woocommerce-swatches-height',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'woocommerce_loop_variation',
			'operator' => '!=',
			'value'    => 'disabled',
		],
	],
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'woocommerce_loop_variation_limit',
    'label'       => reycore_customizer__title_tooltip(
		esc_html__('Attributes display limit', 'rey-core'),
		__('Limit how many attributes to display. 0 is unlimited.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 0,
    'choices'     => [
		'min'  => 0,
		'max'  => 80,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'woocommerce_loop_variation',
			'operator' => '!=',
			'value'    => 'disabled',
		],
	],
]);

/* ------------------------------------ VIEW SWITCHER ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('View Switcher', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'loop_view_switcher',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('View Switcher', 'rey-core'),
		__('Choose if you want to display the products per row switcher.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '1',
    'choices'     => array(
        '1' => esc_attr__('Show', 'rey-core'),
        '2' => esc_attr__('Hide', 'rey-core')
    )
));

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'loop_view_switcher_options',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'View Switcher - Columns', 'rey-core' ),
		esc_html__( 'Add columns variations, separated by comma, up to 6 columns.', 'rey-core' )
	),
	'section'  => $section,
	'default'  => esc_html__( '2, 3, 4', 'rey-core' ),
	'placeholder'  => esc_html__( 'eg: 2, 3, 4', 'rey-core' ),
	'active_callback' => [
		[
			'setting'  => 'loop_view_switcher',
			'operator' => '==',
			'value'    => '1',
		],
	],
	'input_attrs' => [
		'data-control-class' => '--text-md'
	]
] );

reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-woocommerce/#product-catalog-components',
	'section' => $section
]);
