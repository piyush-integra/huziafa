<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$section = 'blog_post_options';


ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('Blog Posts', 'rey-core'),
	'priority'       => 4,
	'panel'			=> 'blog_options'
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'blog_post_sidebar',
    'label'       => esc_html__('Sidebar', 'rey-core'),
    'description' => __('Select the placement of sidebar or disable it. Default is right.', 'rey-core'),
	'section'     => $section,
	'default'     => 'disabled',
    'choices'     => [
        'inherit' => esc_attr__('Inherit', 'rey-core'),
        'left' => esc_attr__('Left', 'rey-core'),
        'right' => esc_attr__('Right', 'rey-core'),
        'disabled' => esc_attr__('Disabled', 'rey-core'),
	],
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'post_width',
    'label'       => esc_html__('Post width', 'rey-core'),
    'description' => __('Select the post width style.', 'rey-core'),
	'section'     => $section,
	'default'     => 'c',
    'choices'     => array(
        'c' => esc_attr__('Compact', 'rey-core'),
        'e' => esc_attr__('Expanded', 'rey-core')
    ),
));


// Meta
reycore_customizer__title([
	'title'   => esc_html__('Meta', 'rey-core'),
	'section' => $section,
	'size'    => 'md',
	'border'  => 'top',
	'upper'   => true,
]);

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'post_date_visibility',
    'label'       => esc_html__('Date', 'rey-core'),
    'description' => __('Enable date?', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'post_comment_visibility',
    'label'       => esc_html__('Comments', 'rey-core'),
    'description' => __('Enable comments number?', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
));
ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'post_categories_visibility',
    'label'       => esc_html__('Categories', 'rey-core'),
    'description' => __('Enable categories?', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
));
ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'post_author_visibility',
    'label'       => esc_html__('Author', 'rey-core'),
    'description' => __('Enable author?', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
));
ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'post_author_box',
    'label'       => esc_html__('Author Box', 'rey-core'),
    'description' => __('Enable author box?', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'post_share',
    'label'       => esc_html__('Sharing links', 'rey-core'),
    'description' => __('Enable Sharing links?', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'post_tags',
    'label'       => esc_html__('Tags', 'rey-core'),
    'description' => __('Enable post tags?', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'post_navigation',
    'label'       => esc_html__('Navigation', 'rey-core'),
    'description' => __('Enable post navigation?', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'post_thumbnail_visibility',
    'label'       => esc_html__('Display Featured Image', 'rey-core'),
    'description' => __('Enable thumbnail or other media?', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'post_cat_text_visibility',
    'label'       => esc_html__('Category Text', 'rey-core'),
    'description' => __('Enable the big category text?', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
));

reycore_customizer__title([
	'title'       => esc_html__('Typography', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'typography',
	'settings'    => 'typography_blog_post_title',
	'label'       => esc_attr__('Post Title', 'rey-core'),
	'section'     => $section,
	'show_variants' => true,
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
			'element' => '.single-post .rey-postTitle',
		),
	),
	'load_choices' => true,
));


ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'typography',
	'settings'    => 'typography_blog_post_content',
	'label'       => esc_attr__('Post Content', 'rey-core'),
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
			'element' => '.single-post .rey-postContent, .single-post .rey-postContent a',
		),
	),
	'load_choices' => true,
));


reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-blog-settings/#blog-post',
	'section' => $section
]);
