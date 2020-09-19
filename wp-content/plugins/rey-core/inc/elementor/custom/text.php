<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Element_Text') ):
    /**
	 * Text Overrides and customizations
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Element_Text {

		function __construct(){

			add_action( 'elementor/element/text-editor/section_style/before_section_end', [$this, 'layout_settings'], 10);

		}

		/**
		 * Add custom settings into Elementor's Section
		 *
		 * @since 1.0.0
		 */
		function layout_settings( $element )
		{

			$element->add_control(
				'remove_last_p',
				[
					'label' => __( 'Remove last bottom-margin', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'description' => __( 'Remove the last paragraph bottom margin.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'u-last-p-margin',
					'default' => '',
					'prefix_class' => '',
					'separator' => 'before'
				]
			);

		}

	}
endif;
