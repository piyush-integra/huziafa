<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Element_Document') ):
    /**
	 * Document Overrides and customizations
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Element_Document {

		function __construct(){
			if( version_compare(ELEMENTOR_VERSION, '2.7.0', '>=') ){
				add_action( 'elementor/element/wp-post/document_settings/before_section_end', [$this, 'page_settings'], 10);
				add_action( 'elementor/element/wp-post/document_settings/after_section_end', [$this, 'extra_page_settings'], 10);
				add_action( 'elementor/element/wp-post/section_page_style/before_section_end', [$this, 'page_styles'], 10);
				add_action( 'elementor/element/wp-page/document_settings/before_section_end', [$this, 'page_settings'], 10);
				add_action( 'elementor/element/wp-page/document_settings/after_section_end', [$this, 'extra_page_settings'], 10);
				add_action( 'elementor/element/wp-page/section_page_style/before_section_end', [$this, 'page_styles'], 10);
			}
			else {
				add_action( 'elementor/element/post/document_settings/before_section_end', [$this, 'page_settings'], 10);
				add_action( 'elementor/element/post/document_settings/after_section_end', [$this, 'extra_page_settings'], 10);
				add_action( 'elementor/element/post/section_page_style/before_section_end', [$this, 'page_styles'], 10);
			}

			if( version_compare(ELEMENTOR_VERSION, '2.7.0', '>=') ){
				add_action( 'elementor/element/wp-post/document_settings/before_section_end', [$this, 'gs_settings'], 10);
			}
			else {
				add_action( 'elementor/element/post/document_settings/before_section_end', [$this, 'gs_settings'], 10);
			}
		}

		/**
		 * Add page settings into Elementor
		 *
		 * @since 1.0.0
		 */
		function page_settings( $page )
		{
			if( ($page_id = $page->get_id()) && $page_id != "" && $post_type = get_post_type( $page_id )) {

				if ( $post_type === 'page'  || $post_type === 'revision') {

					// Inject options
					$page->start_injection( [
						'of' => 'template',
					] );

					$page->add_control(
						'rey_stretch_page',
						[
							'label' => __( 'Stretch Page', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::SWITCHER,
							'return_value' => 'rey-stretchPage',
							'default' => '',
							'condition' => [
								'template' => 'template-builder.php',
							],
						]
					);

					$page->end_injection();

					$page->add_control(
						'rey_need_help',
						[
							'type' => \Elementor\Controls_Manager::RAW_HTML,
							'raw' => sprintf(
								__('To learn more about these options, please visit <a href="%s" target="_blank">Rey\'s Documentation</a>.', 'rey-core'),
								'https://support.reytheme.com/kb/elementor-page-settings/'
							),
							'content_classes' => 'elementor-descriptor',
						]
					);

				}

				if( $post_type === 'revision' ){
					$page->add_control(
						'rey_notice',
						[
							'type' => \Elementor\Controls_Manager::RAW_HTML,
							'raw' => __( 'This is a <strong>revision</strong>. Please update and refresh to enable the custom options.', 'rey-core' ),
							'content_classes' => 'rey-raw-html',
						]
					);
				}
			}

		}

		/**
		 * Add page settings into Elementor
		 *
		 * @since 1.0.0
		 */
		function gs_settings( $page )
		{
			if(
				class_exists('ReyCore_GlobalSections') &&
				($page_id = $page->get_id()) && $page_id != "" && ($post_type = get_post_type( $page_id )) &&
				($post_type === ReyCore_GlobalSections::POST_TYPE || $post_type === 'revision')
			) {

				$gs_type = $page->get_settings_for_display('gs_type');

				if( !$gs_type ) {
					$gs_type = reycore__acf_get_field('gs_type', $page_id, 'generic');
				}

				$page->add_control(
					'gs_type',
					[
						'label' => __( 'Global Section Type', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'options' => ReyCore_GlobalSections::get_global_section_types(),
						'default' => $gs_type,
					]
				);
			}
		}


		/**
		 * Add page settings into Elementor
		 *
		 * @since 1.0.0
		 */
		function extra_page_settings( $page )
		{
			if( ($page_id = $page->get_id()) && isset($page) && $page_id != "" && $post_type = get_post_type( $page_id )) {

				if ( $post_type === 'page' || $post_type === 'revision') {

					$page->start_controls_section(
						'rey_utilities',
						[
							'label' => __( 'Utilities (REY)', 'rey-core' ),
							'tab' => \Elementor\Controls_Manager::TAB_SETTINGS,
						]
					);

					$page->add_control(
						'rey_body_class',
						[
							'label' => esc_html__( 'Body CSS Classes', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::TEXT,
							'default' => '',
							'label_block' => true
						]
					);

					$page->add_control(
						'rey_theme_edit_mode',
						[
							'label' => __( 'Edit Mode Settings', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'description' => __( 'These settings won\'t be saved or affect the layout. They\'re just heplers to improve Edit Mode display' , 'rey-core' ),
							'separator' => 'before',
						]
					);

					$page->add_control(
						'rey_hide_gs',
						[
							'label' => __( 'Hide Global Sections Overlay', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::BUTTON,
							'button_type' => 'default',
							'text' => __( 'Hide', 'rey-core' ),
							'event' => 'rey:editor:hide_gs',
						]
					);

					if( in_array(reycore__get_option('header_position', 'rel'), ['absolute', 'fixed'], true) ){

						$page->add_control(
							'rey_hide_header',
							[
								'label' => __( 'Hide Header', 'rey-core' ),
								'description' => __( 'Hide the header to reach sections behind it.', 'rey-core' ),
								'type' => \Elementor\Controls_Manager::BUTTON,
								'button_type' => 'default',
								'text' => __( 'Hide', 'rey-core' ),
								'event' => 'rey:editor:hide_header',
							]
						);
					}

					$page->end_controls_section();

				}
			}
		}

		function page_styles( $stack )
		{
			if(isset($stack) && $stack->get_id() != "") {

				$post_type = get_post_type( $stack->get_id() );

				if ($post_type == 'page' || $post_type === 'revision' ) {

					// Update padding
					$p_padding = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $stack->get_unique_name(), 'padding' );
					$p_padding['selectors'] = [
						':root' => '--page-padding-top: {{TOP}}{{UNIT}}; --page-padding-right: {{RIGHT}}{{UNIT}}; --page-padding-bottom: {{BOTTOM}}{{UNIT}}; --page-padding-left: {{LEFT}}{{UNIT}}',
					];
					$stack->update_control( 'padding', $p_padding );

					// adds desktop-only gradient
					// Add Dynamic switcher
					$stack->add_control(
						'rey_desktop_gradient',
						[
							'label' => esc_html__( 'Desktop-Only Gradient', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::SWITCHER,
							'default' => '',
							'condition' => [
								'background_background' => 'gradient',
							],
							'prefix_class' => 'rey-gradientDesktop-',
						]
					);

				}
			}
		}

	}
endif;
