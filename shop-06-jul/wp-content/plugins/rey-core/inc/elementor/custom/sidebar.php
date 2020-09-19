<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Element_Sidebar') ):
    /**
	 * Global Overrides and customizations
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Element_Sidebar {

		function __construct(){
			add_action( 'elementor/frontend/widget/before_render', [$this, 'before_render'], 10);
		}

		/**
		 * Add custom settings into Elementor's Global Settings
		 *
		 * @since 1.0.0
		 */
		function before_render( $element )
		{
			if( $element->get_unique_name() !== 'sidebar' ){
				return;
			}

			$settings = $element->get_settings_for_display();

			if( isset($settings['sidebar']) && $sidebar = $settings['sidebar'] ){

				$sidebar_class = $sidebar;

				if( $sidebar === 'filters-top-sidebar' ){
					$sidebar_class =  implode( ' ', array_map( 'sanitize_html_class', apply_filters('rey/content/sidebar_class', [$sidebar], $sidebar) ) );
				}

				$element->add_render_attribute( '_wrapper', 'class', $sidebar_class );
			}

		}
	}
endif;
