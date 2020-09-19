<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$section = 'shop_product_section_content';

ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('Product Page - Content', 'rey-core'),
	'priority'       => 15,
	'panel'			=> 'woocommerce'
));

require REY_CORE_DIR . 'inc/customizer/options/woocommerce/product-page-content--extras.php';


if( class_exists('ReyCore_GlobalSections') ):

	reycore_customizer__title([
		'title'       => esc_html__('Global Sections', 'rey-core'),
		'description' => __('Add global sections into product page. Read more about <a href="https://support.reytheme.com/kb/what-exactly-are-global-sections/" target="_blank">Global Sections</a>.', 'rey-core'),
		'section'     => $section,
		'size'        => 'md',
		'border'      => 'top',
		'upper'       => true,
	]);

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'select',
		'settings'    => 'product_content_after_summary',
		'label'       => esc_html__( 'After product summary section', 'rey-core' ),
		'description' => __( 'Select a global section to append <strong>after product summary</strong> section which will be shown in all product pages.', 'rey-core' ),
		'section'     => $section,
		'default'     => 'none',
		'choices'     => ReyCore_GlobalSections::get_global_sections('generic', ['none' => '- None -']),
	] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'select',
		'settings'    => 'product_content_after_content',
		'label'       => esc_html__( 'After content', 'rey-core' ),
		'description' => __( 'Select a global section to append <strong>after content end</strong> (after reviews) which will be shown in all product pages.', 'rey-core' ),
		'section'     => $section,
		'default'     => 'none',
		'choices'     => ReyCore_GlobalSections::get_global_sections('generic', ['none' => '- None -']),
	] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'repeater',
		'settings'    => 'product_content_after_content_per_category',
		'label'       => esc_html__('"After" Content per Category', 'rey-core'),
		'description' => __('Assign generic global sections to be assigned in products that belong to a certain product category.', 'rey-core'),
		'section'     => $section,
		'row_label' => [
			'value' => esc_html__('Global Section', 'rey-core'),
			'type'  => 'field',
			'field' => 'categories',
		],
		'button_label' => esc_html__('New global section per category', 'rey-core'),
		'default'      => [],
		'fields' => [
			'gs' => [
				'type'        => 'select',
				'label'       => esc_html__('Select Global Section', 'rey-core'),
				'choices'     => ReyCore_GlobalSections::get_global_sections('generic', ['' => esc_html__('- Select -', 'rey-core')]),
			],
			'position' => [
				'type'        => 'select',
				'label'       => esc_html__('Select Position', 'rey-core'),
				'choices'     => [
					'summary' => esc_html__('After Product Summary', 'rey-core'),
					'content' => esc_html__('After Product Content (reviews block)', 'rey-core')
				],
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

endif;


reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-woocommerce/#product-page-content',
	'section' => $section
]);
