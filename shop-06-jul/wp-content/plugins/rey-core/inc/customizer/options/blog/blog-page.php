<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$section = 'blog_style_options';

ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('Blog Page', 'rey-core'),
	'priority'       => 2,
	'panel'			=> 'blog_options'
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'blog_columns',
    'label'       => esc_html__('Posts per row', 'rey-core'),
    'description' => __('Select the number of posts per row.', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
    'choices'     => [
		'1' => esc_attr__('1 per row', 'rey-core'),
        '2' => esc_attr__('2 per row', 'rey-core'),
        '3' => esc_attr__('3 per row', 'rey-core'),
        '4' => esc_attr__('4 per row', 'rey-core')
	],
	'responsive' => true,
	'output'     => [
		[
			'element'  		=> ':root',
			'property' 		=> '--blog-columns',
		],
	],
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'blog_pagination',
	'label'       => esc_html__( 'Pagination type', 'rey-core' ),
	'description' => __('Select the type of pagination you want to be displayed after the products.', 'rey-core'),
	'section'     => $section,
	'default'     => 'paged',
		'choices'     => [
		'paged' => esc_html__( 'Paged', 'rey-core' ),
		'load-more' => esc_html__( 'Load More Button (via Ajax)', 'rey-core' ),
		'infinite' => esc_html__( 'Infinite loading (via Ajax)', 'rey-core' ),
	],
] );

reycore_customizer__title([
	'title'   => esc_html__('Sidebar', 'rey-core'),
	'section' => $section,
	'size'    => 'md',
	'upper'   => true,
]);

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'blog_sidebar',
    'label'       => esc_html__('Sidebar Placement', 'rey-core'),
    'description' => __('Select the placement of sidebar or disable it. Default is right.', 'rey-core'),
	'section'     => $section,
	'default'     => 'right',
    'choices'     => array(
        'left' => esc_attr__('Left', 'rey-core'),
        'right' => esc_attr__('Right', 'rey-core'),
        'disabled' => esc_attr__('Disabled', 'rey-core'),
    )
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'slider',
	'settings'    => 'blog_sidebar_size',
	'label'       => esc_html__( 'Sidebar Size', 'rey-core' ),
	'section'     => $section,
	'default'     => 27,
	'choices'     => [
		'min'  => 15,
		'max'  => 60,
		'step' => 1,
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--sidebar-size',
			'units'    		=> '%',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'blog_sidebar_boxed',
	'label'       => esc_html__( 'Enable boxed sidebar', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
] );


// Meta
reycore_customizer__title([
	'title'   => esc_html__('Meta', 'rey-core'),
	'section' => $section,
	'size'    => 'md',
	'upper'   => true,
]);


ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'blog_date_visibility',
    'label'       => esc_html__('Date', 'rey-core'),
    'description' => __('Enable date?', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
));
ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'blog_date_type',
    'label'       => esc_html__('Human Date', 'rey-core'),
    'description' => __('Enable human readable date?', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
	'active_callback' => [
		[
			'setting'  => 'blog_date_visibility',
			'operator' => '==',
			'value'    => true,
		],
	],
));
ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'blog_comment_visibility',
    'label'       => esc_html__('Comments', 'rey-core'),
    'description' => __('Enable comments number?', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
));
ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'blog_categories_visibility',
    'label'       => esc_html__('Categories', 'rey-core'),
    'description' => __('Enable categories?', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
));
ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'blog_author_visibility',
    'label'       => esc_html__('Author', 'rey-core'),
    'description' => __('Enable author?', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
));
ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'blog_read_visibility',
    'label'       => esc_html__('Read duration', 'rey-core'),
    'description' => __('Enable read duration?', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'blog_thumbnail_visibility',
    'label'       => esc_html__('Thumbnail (Media)', 'rey-core'),
    'description' => __('Enable thumbnail or other media?', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'blog_thumbnail_animation',
    'label'       => esc_html__('Expand Thumbnail With Animation', 'rey-core'),
    'description' => __('Enable expanded thumbnail with hover animation?', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
	'active_callback' => [
		[
			'setting'  => 'blog_thumbnail_visibility',
			'operator' => '==',
			'value'    => true,
		],
	],
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'blog_title_animation',
    'label'       => esc_html__('Title Animation', 'rey-core'),
    'description' => __('Select the title\'s animation on post hover.', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'blog_content_type',
    'label'       => esc_html__('Content', 'rey-core'),
    'description' => __('Select the post\' content type.', 'rey-core'),
	'section'     => $section,
	'default'     => 'e',
	'choices'     => array(
        'e' => esc_attr__('Excerpt', 'rey-core'),
        'c' => esc_attr__('Default Content', 'rey-core'),
        'none' => esc_attr__('None', 'rey-core'),
    ),
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'blog_excerpt_length',
    'label'       => esc_html__('Excerpt length', 'rey-core'),
	'section'     => $section,
	'default'     => 55,
	'choices'     => [
		'min'  => 5,
		'max'  => 200,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'blog_content_type',
			'operator' => '==',
			'value'    => 'e',
			],
	],
] );

/**
 * Blog
 */
reycore_customizer__title([
	'title'       => esc_html__('Typography', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'typography',
	'settings'    => 'typography_blog_title',
	'label'       => esc_attr__('Blog Post Title', 'rey-core'),
	'section'     => $section,
	'default'     => array(
		'font-family'      => '',
		'font-size'      => '',
		'line-height'    => '',
		'letter-spacing' => '',
		'text-transform' => '',
		'font-weight' => '',
		'variant' => '',
		'color' => '',
	),
	'output' => array(
		array(
			'element' => '.rey-postList .rey-postTitle, .rey-postList .rey-postTitle a',
		),
	),
	'load_choices' => true,
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'typography',
	'settings'    => 'typography_blog_content',
	'label'       => esc_attr__('Blog Post Content', 'rey-core'),
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
			'element' => '.rey-postList .rey-postContent, .rey-postList .rey-postContent a',
		),
	),
	'load_choices' => true,
));


reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-blog-settings/#blog-page',
	'section' => $section
]);
