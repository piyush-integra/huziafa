<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'shop_product_section_components';


/* ------------------------------------ REQUEST A QUOTE ------------------------------------ */

reycore_customizer__title([
    'title'       => esc_html__('Request a Quote (Send enquiry)', 'rey-core'),
	'description' => esc_html__('Add a button in product pages that opens a modal containing a contact form.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'request_quote__type',
	'label'       => esc_html__( 'Select which products', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'' => esc_html__( 'None', 'rey-core' ),
		'all' => esc_html__( 'All products', 'rey-core' ),
		'products' => esc_html__( 'Specific products', 'rey-core' ),
		'categories' => esc_html__( 'Specific category products', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'request_quote__products',
	'label'       => esc_html__( 'Select products (comma separated)', 'rey-core' ),
	'placeholder' => esc_html__( 'eg: 100, 101, 102', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'active_callback' => [
		[
			'setting'  => 'request_quote__type',
			'operator' => '==',
			'value'    => 'products',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'request_quote__categories',
	'label'       => esc_html__( 'Select categories', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'multiple'    => 100,
	'choices'     => reycore_wc__product_categories([
		'parent' => 0,
		'hide_empty' => false,
	]),
	'active_callback' => [
		[
			'setting'  => 'request_quote__type',
			'operator' => '==',
			'value'    => 'categories',
		],
	],
] );

$forms = reycore__get_cf7_forms();
$nocf7_notice = '';

if( ! function_exists('wpcf7_contact_form') ){
	$nocf7_notice = __('<p>It seems <a href="https://wordpress.org/plugins/contact-form-7/" target="_blank">Contact Form 7</a> is not installed or active. Please activate it to be able to create a contact form to be used with this option.</p>', 'rey-core');
}
else {
	if( empty($forms) ){
		$nocf7_notice = __('<p>Please create a contact form to be used in this option.</p>', 'rey-core');
	}
}

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'request_quote__cf7',
	'label'       => esc_html__( 'Select Contact Form', 'rey-core' ),
	'description' => $nocf7_notice . esc_html__( 'Select the contact form you created in Contact Form 7 plugin.', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'' => esc_html__( 'None', 'rey-core' ),
	] + $forms,
	'active_callback' => [
		[
			'setting'  => 'request_quote__type',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'request_quote__btn_text',
	'label'       => esc_html__( 'Button Text', 'rey-core' ),
	'placeholder' => esc_html__( 'eg: Request a quote', 'rey-core' ),
	'section'     => $section,
	'default'     => esc_html__( 'Request a Quote', 'rey-core' ),
	'active_callback' => [
		[
			'setting'  => 'request_quote__type',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'request_quote__btn_style',
	'label'       => esc_html__( 'Button Style', 'rey-core' ),
	'section'     => $section,
	'default'     => 'btn-line-active',
	'choices'     => [
		'btn-line-active' => esc_html__( 'Underlined', 'rey-core' ),
		'btn-primary' => esc_html__( 'Regular', 'rey-core' ),
		'btn-primary btn--block' => esc_html__( 'Regular & Full width', 'rey-core' ),
		'btn-primary-outlined' => esc_html__( 'Regular outline', 'rey-core' ),
		'btn-primary-outlined btn--block' => esc_html__( 'Regular outline & Full width', 'rey-core' ),
		'btn-secondary' => esc_html__( 'Regular', 'rey-core' ),
		'btn-secondary btn--block' => esc_html__( 'Regular & Full width', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'request_quote__type',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'textarea',
	'settings'    => 'request_quote__btn_text_after',
	'label'       => esc_html__( 'Text after button', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'active_callback' => [
		[
			'setting'  => 'request_quote__type',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );
