<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Element_Heading') ):
    /**
	 * Heading Overrides and customizations
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Element_Heading {

		function __construct(){
			add_action( 'elementor/widget/heading/skins_init', [$this,'heading_skins'] );
			add_action( 'elementor/element/heading/section_title/before_section_end', [$this,'heading_controls'], 10);
			add_action( 'elementor/element/heading/section_title_style/after_section_end', [$this,'heading_controls_styles'], 10);
		}

		/**
		 * Add custom skins into Elementor's Heading widget
		 *
		 * @since 1.0.0
		 */
		function heading_skins( $element )
		{
			if( class_exists('ReyCore_Heading_Dynamic_Skin') ){
				$element->add_skin( new ReyCore_Heading_Dynamic_Skin( $element ) );
			}
		}


		/**
		 * Add custom settings into Elementor's title section
		 *
		 * @since 1.0.0
		 */
		function heading_controls( $element )
		{
			$heading_title = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'title' );
			$heading_title['condition']['_skin'] = [''];
			$element->update_control( 'title', $heading_title );

			$element->start_injection( [
				'of' => 'title',
			] );

			$element->add_control(
				'source',
				[
					'label' => __( 'Title Source', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'title',
					'options' => [
						'title'  => __( 'Post Title', 'rey-core' ),
						'excerpt'  => __( 'Post excerpt', 'rey-core' ),
						'archive_title'  => __( 'Archive Title', 'rey-core' ),
						'desc'  => __( 'Archive Description', 'rey-core' ),
					],
					'condition' => [
						'_skin' => ['dynamic_title'],
					],
				]
			);

			$element->end_injection();
		}


		/**
		 * Add custom settings into Elementor's title style section
		 *
		 * @since 1.0.0
		 */
		function heading_controls_styles( $element )
		{

			$element->start_controls_section(
				'section_special_styles',
				[
					'label' => __( 'Special Styles', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$element->add_control(
				'rey_text_stroke',
				[
					'label' => __( 'Text Stroke', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'return_value' => 'stroke',
					'prefix_class' => 'elementor-heading--'
				]
			);

			$element->add_responsive_control(
				'rey_vertical_text',
				[
					'label' => __( 'Vertical Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'return_value' => 'vertical',
					'prefix_class' => 'elementor-heading-%s-'
				]
			);

			$element->add_control(
				'rey_parent_hover',
				[
					'label' => __( 'Animate on Parent Hover', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => __( 'None', 'rey-core' ),
						'underline'  => __( 'Underline animation', 'rey-core' ),
						'show'  => __( 'Visible In', 'rey-core' ),
						'hide'  => __( 'Visible Out', 'rey-core' ),
						'slide_in'  => __( 'Slide In', 'rey-core' ),
						'slide_out'  => __( 'Slide Out', 'rey-core' ),
					],
					'prefix_class' => 'el-parent-animation--'
				]
			);

			$element->add_control(
				'rey_parent_hover_slide_direction',
				[
					'label' => __( 'Slide direction', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'bottom',
					'options' => [
						'top'  => __( 'Top', 'rey-core' ),
						'right'  => __( 'Right', 'rey-core' ),
						'bottom'  => __( 'Bottom', 'rey-core' ),
						'left'  => __( 'Left', 'rey-core' ),
					],
					'prefix_class' => '--slide-',
					'condition' => [
						'rey_parent_hover' => ['slide_in', 'slide_out'],
					],
				]
			);

			$element->add_control(
				'rey_parent_hover_slide_blur',
				[
					'label' => esc_html__( 'Slide with blur', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'return_value' => 'ry',
					'prefix_class' => '--blur',
					'condition' => [
						'rey_parent_hover' => ['show', 'hide', 'slide_in', 'slide_out'],
					],
				]
			);

			$element->add_control(
				'rey_parent_hover_delay',
				[
					'label' => esc_html__( 'Transition delay', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 5000,
					'step' => 50,
					'selectors' => [
						'{{WRAPPER}} .elementor-heading-title' => 'transition-delay: {{VALUE}}ms',
					],
					'condition' => [
						'rey_parent_hover' => ['show', 'hide', 'slide_in', 'slide_out'],
					],
				]
			);

			$element->add_control(
				'rey_parent_hover_trigger',
				[
					'label' => __( 'Parent Hover Trigger', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'column',
					'options' => [
						'column'  => __( 'Parent Column', 'rey-core' ),
						'section'  => __( 'Parent Section', 'rey-core' ),
					],
					'condition' => [
						'rey_parent_hover!' => '',
					],
					'prefix_class' => 'el-parent-trigger--'
				]
			);

			// parent hover
			// hover trigger - parent section / parent column
			// hover effect - underline / animate in

			$element->end_controls_section();
		}

	}
endif;
