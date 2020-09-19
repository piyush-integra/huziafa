<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'woocommerce_product_images';

/* ------------------------------------ CUSTOM MAIN IMAGE HEIGHT ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Main Image (Product Page)', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'bottom',
	'upper'       => true,
	'priority'    => 5,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'custom_main_image_height',
	'label'       => __( 'Main Image <em>Container</em> height', 'rey-core' ),
	'description' => __( 'This will force the main image\'s container height, therefore make the image constrain inside.', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'priority'    => 10,
	'active_callback' => [
		[
			'setting'  => 'product_gallery_layout',
			'operator' => 'in',
			'value'    => ['horizontal'],
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'custom_main_image_height_size',
	'label'       => esc_html__( 'Height (px)', 'rey-core' ),
	'section'     => $section,
	'default'     => 540,
	'priority'    => 10,
	'choices'     => [
		'min'  => 100,
		'max'  => 1000,
		'step' => 1,
	],
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--woocommerce-custom-main-image-height',
			'units'    		=> 'px',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'custom_main_image_height',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'product_gallery_layout',
			'operator' => 'in',
			'value'    => ['horizontal'],
		],
	],
	'rey_group_start' => [
		'label'       => esc_html__( 'Main Image-Container Height', 'rey-core' ),
	],
	'rey_group_end' => true
] );


/* ------------------------------------ CUSTOM THUMBNAIL HEIGHT ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Thumbnail image (Catalog)', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'bottom',
	'upper'       => true,
	'priority'    => 19,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'custom_image_height',
	'label'       => esc_html__( 'Thumbnail Container Height', 'rey-core' ),
	'description' => __( 'Adding a custom image container height forces the image to fit in its parent container.<br><strong>Only works for Uncropped images!</strong>', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'priority'    => 120,
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'custom_image_height_size',
	'label'       => esc_html__( 'Height (px)', 'rey-core' ),
	'section'     => $section,
	'default'     => 350,
	'priority'    => 120,
	'choices'     => [
		'min'  => 0,
		'max'  => 1000,
		'step' => 1,
	],
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--woocommerce-custom-image-height',
			'units'    		=> 'px',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'custom_image_height',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label'       => esc_html__( 'Image-Container Height', 'rey-core' ),
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'custom_image_height_size_mobile',
	'label'       => esc_html__( 'Height (Mobile)', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'priority'    => 120,
	'choices'     => [
		'min'  => 0,
		'max'  => 1000,
		'step' => 1,
	],
	'output'      		=> [
		[
			'media_query'	=> '@media (max-width: 767px)',
			'element'  		=> ':root',
			'property' 		=> '--woocommerce-custom-image-height',
			'units'    		=> 'px',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'custom_image_height',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_end' => true
] );


/* ------------------------------------ THUMBNAIL PADDING ------------------------------------ */

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'dimensions',
	'settings'    => 'shop_thumbnails_padding',
	'label'       => esc_html__( 'Thumbnails Inner Padding', 'rey-core' ),
	'description' => __( 'Will add padding around the <strong>thumbnails</strong>. Dont forget to include unit (eg: px, em, rem).', 'rey-core' ),
	'section'     => $section,
	'priority'    => 130,
	'default'     => [
		'top'    => '',
		'right'  => '',
		'bottom' => '',
		'left'   => '',
	],
	'choices'     => [
		'labels' => [
			'top'  => esc_html__( 'Top', 'rey-core' ),
			'right' => esc_html__( 'Right', 'rey-core' ),
			'bottom'  => esc_html__( 'Bottom', 'rey-core' ),
			'left' => esc_html__( 'Left', 'rey-core' ),
		],
	],
	'transport'   		=> 'auto',
	'output'      		=> array(
		array(
			'element'  		=> ':root',
			'property' 		=> '--woocommerce-thumbnails-padding',
		),
	),
	'input_attrs' => array(
		'data-control-class' => 'dimensions-4-cols',
	),
	'responsive' => true
] );

reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-woocommerce/#product-images',
	'section' => $section
]);
