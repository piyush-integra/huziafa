<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if(!class_exists('ReyCore_Widget_Toggle_Boxes')):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Toggle_Boxes extends \Elementor\Widget_Base {

	public function get_name() {
		return 'reycore-toggle-boxes';
	}

	public function get_title() {
		return __( 'Toggle Boxes', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-navigation-horizontal';
	}

	public function get_categories() {
		return [ 'rey-theme' ];
	}

	public function get_script_depends() {
		return [ 'reycore-widget-toggle-boxes-scripts' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements/#toggle-boxes';
	}

	protected function _register_skins() {
		$this->add_skin( new ReyCore_Widget_Toggle_Boxes__Stacks( $this ) );
	}

	/**
	 * Register widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _register_controls() {

		$this->start_controls_section(
			'section_layout',
			[
				'label' => __( 'Layout', 'rey-core' ),
			]
		);

		$this->add_control(
			'target_type',
			[
				'label' => __( 'Select target type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => __( 'None', 'rey-core' ),
					'tabs'  => __( 'Tabs', 'rey-core' ),
					'carousel'  => __( 'Image Carousel (widget)', 'rey-core' ),
					'parent'  => __( 'Parent Section Slideshow', 'rey-core' ),
				],
				'label_block' => true
			]
		);

		$this->add_control(
			'target_tabs',
			[
				'label' => __( 'Tabs ID', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'default' => '',
				'placeholder' => __( 'eg: unique-id', 'rey-core' ),
				'condition' => [
					'target_type' => 'tabs',
				],
				'description' => esc_html__('Copy the unique ID, found in Section > Tabs Settings > Tabs ID, and paste it here.', 'rey-core')
			]
		);

		$this->add_control(
			'target_carousel',
			[
				'label' => __( 'Carousel ID', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'default' => '',
				'placeholder' => __( 'eg: unique-id', 'rey-core' ),
				'condition' => [
					'target_type' => 'carousel',
				],
				'description' => esc_html__('Copy the unique ID, found in Image Carousel widget ID, and paste it here.', 'rey-core')
			]
		);


		$this->add_control(
			'trigger',
			[
				'label' => __( 'Trigger', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'click',
				'options' => [
					'click'  => __( 'On Click', 'rey-core' ),
					'hover'  => __( 'On Hover', 'rey-core' ),
				],
				'condition' => [
					'target_type' => ['parent', 'carousel', 'tabs'],
				],
			]
		);

		$items = new \Elementor\Repeater();

		$items->add_control(
			'text',
			[
				'label'       => __( 'Text', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
			]
		);

		$items->add_control(
			'icon',
			[
				'label' => __( 'Icon', 'elementor' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'default' => [],
			]
		);

		$this->add_control(
			'items',
			[
				'label' => __( 'Items', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $items->get_controls(),
				'condition' => [
					'_skin' => '',
				],
				'default' => [
					[
						'text' => __( 'Item Text  #1', 'rey-core' ),
					],
					[
						'text' => __( 'Item Text  #2', 'rey-core' ),
					],
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'direction',
			[
				'label' => __( 'Direction', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'h',
				'options' => [
					'h'  => __( 'Horizontal', 'rey-core' ),
					'v'  => __( 'Vertical', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'distance',
			[
				'label' => __( 'Items Distance', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-toggleBoxes--h .rey-toggleBox' => 'margin-left: {{VALUE}}px; margin-right: {{VALUE}}px;',
					'{{WRAPPER}} .rey-toggleBoxes--v .rey-toggleBox' => 'margin-top: {{VALUE}}px; margin-bottom: {{VALUE}}px;',
				]
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label' => __( 'Alignment', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'rey-core' ),
						'icon' => 'fa fa-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'rey-core' ),
						'icon' => 'fa fa-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'rey-core' ),
						'icon' => 'fa fa-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_items_style',
			[
				'label' => __( 'Items Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'tabs_items_styles' );

			$this->start_controls_tab(
				'tabs_items_styles_normal',
				array(
					'label' => esc_html__( 'Normal', 'rey-core' ),
				)
			);

				$this->add_control(
					'text_color',
					[
						'label' => __( 'Text Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-toggleBox' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'bg_color',
					[
						'label' => __( 'Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-toggleBox' => 'background-color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'icon_color',
					[
						'label' => __( 'Icon Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-toggleBox > i' => 'color: {{VALUE}}',
						],
						'condition' => [
							'_skin' => '',
						],
					]
				);

				$this->add_responsive_control(
					'border_width',
					[
						'label' => __( 'Border Width', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', 'em', '%' ],
						'selectors' => [
							'{{WRAPPER}} .rey-toggleBox' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'border_color',
					[
						'label' => __( 'Border Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-toggleBox' => 'border-color: {{VALUE}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					[
						'name' => 'box_shadow',
						'selector' => '{{WRAPPER}} .rey-toggleBox',
					]
				);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tabs_items_styles_hover',
				array(
					'label' => esc_html__( 'Active', 'rey-core' ),
				)
			);

				$this->add_control(
					'text_color_active',
					[
						'label' => __( 'Text Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-toggleBox.--active, {{WRAPPER}} .rey-toggleBox:hover' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'bg_color_active',
					[
						'label' => __( 'Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-toggleBox.--active, {{WRAPPER}} .rey-toggleBox:hover' => 'background-color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'icon_color_active',
					[
						'label' => __( 'Icon Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-toggleBox.--active > i, {{WRAPPER}} .rey-toggleBox:hover > i' => 'color: {{VALUE}}',
						],
						'condition' => [
							'_skin' => '',
						],
					]
				);

				$this->add_responsive_control(
					'border_width_active',
					[
						'label' => __( 'Border Width', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', 'em', '%' ],
						'selectors' => [
							'{{WRAPPER}} .rey-toggleBox.--active, {{WRAPPER}} .rey-toggleBox:hover' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'border_color_active',
					[
						'label' => __( 'Border Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-toggleBox.--active, {{WRAPPER}} .rey-toggleBox:hover' => 'border-color: {{VALUE}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					[
						'name' => 'box_shadow_active',
						'selector' => '{{WRAPPER}} .rey-toggleBox.--active, {{WRAPPER}} .rey-toggleBox:hover',
					]
				);

			$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'box_padding',
			[
				'label' => __( 'Padding', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .rey-toggleBox' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .rey-toggleBox-text-active' => 'top: {{TOP}}{{UNIT}}; left: {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'border_radius',
			[
				'label' => __( 'Border Radius', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .rey-toggleBox' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'main_text',
			[
			   'label' => __( 'Main Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typo',
				'selector' => '{{WRAPPER}} .rey-toggleBox-text-main'
			]
		);

		$this->add_control(
			'icon_style_title',
			[
			   'label' => __( 'Icon styles', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'_skin' => '',
				],
			]
		);

		$this->add_control(
			'icons_distance',
			[
				'label' => __( 'Icon Distance', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-toggleBoxes' => '--toggle-boxes-icon-distance: {{VALUE}}px;',
				],
				'condition' => [
					'_skin' => '',
				],
			]
		);

		$this->add_control(
			'icons_size',
			[
				'label' => __( 'Icon Size', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-toggleBoxes' => '--toggle-boxes-icon-size: {{VALUE}}px;',
				],
				'condition' => [
					'_skin' => '',
				],
			]
		);



		$this->end_controls_section();
	}

	public function render_start( $settings ){

		$this->add_render_attribute( 'wrapper', 'class', 'rey-toggleBoxes' );
		$this->add_render_attribute( 'wrapper', 'class', 'rey-toggleBoxes--' . $settings['direction'] );

		if( ! $skin = $settings['_skin'] ){
			$skin = 'default';
		}

		$this->add_render_attribute( 'wrapper', 'class', 'rey-toggleBoxes--' . $skin );

		if( $settings['target_type'] !== '' ){
			$config = [
				'target_type' => $settings['target_type'],
				'tabs_target' => esc_attr($settings['target_tabs']),
				'carousel_target' => esc_attr($settings['target_carousel']),
				'parent_trigger' => esc_attr($settings['trigger']),
			];
			$this->add_render_attribute( 'wrapper', 'data-config', wp_json_encode($config) );
		}

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
		<?php
	}

	public function render_end(){
		?>
		</div>
		<?php
	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		$this->render_start($settings);

		if( !empty($settings['items']) ){
			foreach ($settings['items'] as $key => $item) {

				printf('<div class="rey-toggleBox %s">', ($key == 0 ? '--active': ''));

					if( $icon = $item['icon'] ){
						\Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
					}

					printf('<span class="rey-toggleBox-text-main">%s</span>', $item['text']);

				echo '</div>';
			}
		}

		$this->render_end();
	}

	/**
	 * Render widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _content_template() {}
}
endif;
