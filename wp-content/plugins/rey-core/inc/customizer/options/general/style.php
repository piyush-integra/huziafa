<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'style_options';

ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('Style Settings', 'rey-core'),
	'priority'       => 5,
	'panel'			=> 'general_options'
));

reycore__kirki_custom_bg_group([
	'settings'    => 'style_bg_image',
	'label'       => __('Background image', 'rey-core'),
	'description' => __('Change the site background.', 'rey-core'),
	'section'     => $section,
	// 'priority'   => 30,
	'supports' => [
		'color', 'image', 'repeat', 'attachment', 'size', 'position'
	],
	'output_element' => ':root',
	'color' => [
		'default' => '#ffffff',
		'output_property' => '--body-bg-color',
	],
	'image' => [
		'output_property' => '--body-bg-image',
	],
	'repeat' => [
		'output_property' => '--body-bg-repeat',
	],
	'attachment' => [
		'output_property' => '--body-bg-attachment',
	],
	'size' => [
		'output_property' => '--body-bg-size',
	],
	'positionx' => [
		'output_property' => '--body-bg-posx',
	],
	'positiony' => [
		'output_property' => '--body-bg-posy',
	],
]);


ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'color',
	'settings'    => 'style_text_color',
	'label'       => reycore_customizer__title_tooltip(
		__('Text Color', 'rey-core'),
		__('Change the site text color.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '',
	// 'priority'    => 40,
	'output'      => [
		[
			'element'  		=> ':root',
			'property' 		=> '--body-color',
		],
		[
			'element'  => '.edit-post-visual-editor.editor-styles-wrapper',
			'property' => '--body-color',
			'context'  => [ 'editor' ],
		],
	],
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'color',
	'settings'    => 'style_link_color',
	'label'       => reycore_customizer__title_tooltip(
		__('Link Color', 'rey-core'),
		__('Change the links color.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '',
	// 'priority'    => 50,
	'output'      => [
		[
			'element'  		=> ':root',
			'property' 		=> '--link-color',
		],
		[
			'element'  => '.edit-post-visual-editor.editor-styles-wrapper',
			'property' => '--link-color',
			'context'  => [ 'editor' ],
		],
	],
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'color',
	'settings'    => 'style_link_color_hover',
	'label'       => reycore_customizer__title_tooltip(
		__('Link Color Hover', 'rey-core'),
		__('Change the links hover color.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '',
	'output'      => [
		[
			'element'  		=> ':root',
			'property' 		=> '--link-color-hover',
		],
		[
			'element'  => '.edit-post-visual-editor.editor-styles-wrapper',
			'property' => '--link-color-hover',
			'context'  => [ 'editor' ],
		],
	],
));

/* ------------------------------------ ACCENT COLOR ------------------------------------ */

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'color',
	'settings'    => 'style_accent_color',
	'label'       => reycore_customizer__title_tooltip(
		__('Accent Color', 'rey-core'),
		__('Change the accent color. Some elements are using this color, such as primary buttons.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '#212529',
]);

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'color',
	'settings'    => 'style_accent_color_hover',
	'label'       => reycore_customizer__title_tooltip(
		__('Accent Hover Color', 'rey-core'),
		__('Change the hover accent color.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '',
]);

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'color',
	'settings'    => 'style_accent_color_text',
	'label'       => reycore_customizer__title_tooltip(
		__('Accent Text Color', 'rey-core'),
		__('Change the text accent color.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '',
]);


reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-general-settings/#style-settings',
	'section' => $section
]);
