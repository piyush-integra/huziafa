<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Widget_Toggle_Boxes__Stacks') ):

	class ReyCore_Widget_Toggle_Boxes__Stacks extends \Elementor\Skin_Base
	{

		public function get_id() {
			return 'stacks';
		}

		public function get_title() {
			return __( 'Stacks', 'rey-core' );
		}

		protected function _register_controls_actions() {
			parent::_register_controls_actions();

			add_action( 'elementor/element/reycore-toggle-boxes/section_layout/before_section_end', [ $this, 'register_additional_content_controls' ] );
			add_action( 'elementor/element/reycore-toggle-boxes/section_items_style/before_section_end', [ $this, 'register_additional_style_controls' ] );
		}

		public function register_additional_content_controls( $element ){

			$stacks = new \Elementor\Repeater();

			$stacks->add_control(
				'main_text',
				[
					'label'       => __( 'Title', 'rey-core' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'label_block' => true,
				]
			);

			$stacks->add_control(
				'sec_text',
				[
					'label'       => __( 'Sub-Title', 'rey-core' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'label_block' => true,
				]
			);

			$stacks->add_control(
				'active_text',
				[
					'label'       => __( 'Active Text', 'rey-core' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'label_block' => true,
				]
			);

			$stacks->add_control(
				'active_url',
				[
					'label' => __( 'Link', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::URL,
					'placeholder' => __( 'https://your-link.com', 'rey-core' ),
					'show_external' => true,
					'default' => [
						'url' => '',
						'is_external' => false,
					],
				]
			);

			$element->add_control(
				'stacks_items',
				[
					'label' => __( 'Items', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $stacks->get_controls(),
					'condition' => [
						'_skin' => 'stacks',
					],
					'default' => [
						[
							'main_text' => __( 'Main Text #1', 'rey-core' ),
							'sec_text' => __( 'Secondary Text #1', 'rey-core' ),
							'active_text' => __( 'Active Text #1', 'rey-core' ),
							'active_url' => __( '#', 'rey-core' ),
						],
						[
							'main_text' => __( 'Main Text #2', 'rey-core' ),
							'sec_text' => __( 'Secondary Text #2', 'rey-core' ),
							'active_text' => __( 'Active Text #2', 'rey-core' ),
							'active_url' => __( '#', 'rey-core' ),
						],
					],
				]
			);
		}

		public function register_additional_style_controls( $element ){

			$element->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'typo_secondary',
					'label' => esc_html__('Subtitle', 'rey-core'),
					'selector' => '{{WRAPPER}} .rey-toggleBox-text-secondary',
					'condition' => [
						'_skin' => 'stacks',
					],
				]
			);

			$element->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'typo_active',
					'label' => esc_html__('Active Button', 'rey-core'),
					'selector' => '{{WRAPPER}} .rey-toggleBox-text-active',
					'condition' => [
						'_skin' => 'stacks',
					],
				]
			);
		}

		// public function get_script_depends() {
		// 	return parent::get_script_depends();
		// }

		public function render() {

			$settings = $this->parent->get_settings_for_display();

			$this->parent->render_start($settings);

			if( !empty($settings['stacks_items']) ){
				foreach ($settings['stacks_items'] as $key => $item) { ?>

					<div class="rey-toggleBox <?php echo $key == 0 ? '--active': '' ?>">

					<?php
 					if( $active_text = $item['active_text'] ):
						$url = $target = $nofollow = '';
						if( isset( $item['active_url']['url'] ) ) {
							$url = $item['active_url']['url'];
							$target = isset($settings['active_url']['is_external']) && $settings['active_url']['is_external'] ? ' target="_blank"' : '';
							$nofollow = isset($settings['active_url']['nofollow']) && $settings['active_url']['nofollow'] ? ' rel="nofollow"' : '';
						}
						?>
						<a href="<?php echo $url ?>" <?php echo $target ?> <?php echo $nofollow ?> class="rey-toggleBox-text-active"><?php echo $active_text ?></a>
					<?php endif; ?>

					<?php if( $main_text = $item['main_text'] ): ?>
						<span class="rey-toggleBox-text-main"><?php echo $main_text ?></span>
					<?php endif; ?>

					<?php if( $sec_text = $item['sec_text'] ): ?>
						<span class="rey-toggleBox-text-secondary"><?php echo $sec_text ?></span>
					<?php endif; ?>

					</div>
					<?php
				}
			}

			$this->parent->render_end();
		}
	}
endif;
