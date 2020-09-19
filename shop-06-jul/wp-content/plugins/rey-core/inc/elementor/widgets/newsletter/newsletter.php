<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if(!class_exists('ReyCore_Widget_Newsletter')):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Newsletter extends \Elementor\Widget_Base {

	public function get_name() {
		return 'reycore-newsletter';
	}

	public function get_title() {
		return __( 'Newsletter Form', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-mailchimp';
	}

	public function get_categories() {
		return [ 'rey-theme' ];
	}

	public function get_keywords() {
		return [ 'newsletter', 'mailing list', 'mailchimp' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements/#newsletter-form';
	}

	public function on_export($element)
    {
        unset(
            $element['settings']['form_id']
        );

        return $element;
    }

	/**
	 * Get forms.
	 *
	 * Retrieve an array of forms from the MailChimp for WordPress plugin.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return array An array containing button sizes.
	 */
	public static function get_forms() {

		if( function_exists('mc4wp_get_forms') ) :

			$forms = mc4wp_get_forms();
			$data = ['' => '- Select -'];

			foreach ($forms as $form) {
				$data[$form->ID] = $form->name;
			}

			return $data;
		endif;

		return false;
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
			'section_settings',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);
		$forms = self::get_forms();

		$this->add_control(
			'important_note',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => $forms === false ? __( 'To use this element you need to install <a href="https://wordpress.org/plugins/mailchimp-for-wp/" target="_blank">Mailchimp for WordPress</a>.', 'rey-core' ) : '',
				'content_classes' => 'elementor-descriptor',
			]
		);

		// form id
		$this->add_control(
			'form_id',
			[
				'label' => __( 'Form ID', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $forms,
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
			'form_style',
			[
				'label' => __( 'Form Style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => '- Select -',
					'inline-basic' => esc_html__('Inline Basic', 'rey-core'),
				],
			]
		);

		$this->add_control(
			'primary_color',
			[
				'label' => __( 'Primary Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-nlForm--inline-basic .mc4wp-form-fields' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'secondary_color',
			[
				'label' => __( 'Secondary Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-nlForm--inline-basic input' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'input_typo',
			   'label' => __( 'Input typography', 'rey-core' ),
				'selector' => '{{WRAPPER}} .rey-newsletterForm input[type="email"]',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'btn_typo',
			   'label' => __( 'Button typography', 'rey-core' ),
				'selector' => '{{WRAPPER}} .rey-newsletterForm input[type="submit"]',
			]
		);


		$this->end_controls_section();


		$this->start_controls_section(
			'section_other_styles',
			[
				'label' => __( 'Borders & Other Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'el_tabs_border',
			[
				'condition' => [
					'form_style' => ['inline-basic'],
				],
			]
		);

		$this->start_controls_tab(
			'el_tab_border_normal',
			[
				'label' => __( 'Normal', 'rey-core' ),
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'el_border',
				'selector' => '{{WRAPPER}} .rey-nlForm--inline-basic .mc4wp-form-fields',
			]
		);

		$this->add_responsive_control(
			'el_border_radius',
			[
				'label' => __( 'Border Radius', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .rey-nlForm--inline-basic .mc4wp-form-fields' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'el_box_shadow',
				'selector' => '{{WRAPPER}} .rey-nlForm--inline-basic .mc4wp-form-fields',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'el_tab_border_hover',
			[
				'label' => __( 'Hover', 'rey-core' ),
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'el_border_hover',
				'selector' => '{{WRAPPER}}:hover .rey-nlForm--inline-basic .mc4wp-form-fields',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'el_box_shadow_hover',
				'selector' => '{{WRAPPER}}:hover .rey-nlForm--inline-basic .mc4wp-form-fields',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'el_padding',
			[
				'label' => __( 'Padding', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .rey-nlForm--inline-basic .mc4wp-form-fields' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'form_style' => ['inline-basic'],
				],
			]
		);


		$this->end_controls_section();
	}

	public function render_form($form_id){

		try {
			$form = mc4wp_get_form($form_id);
		} catch (Exception $e) {
			return false;
		}

		echo $form;
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'wrapper', 'class', 'rey-element' );
		$this->add_render_attribute( 'wrapper', 'class', 'rey-newsletterForm' );
		$this->add_render_attribute( 'wrapper', 'class', 'rey-nlForm--' . $settings['form_style'] );

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<?php if( function_exists('mc4wp_get_form') && $form_id = $settings['form_id'] ){
				$this->render_form($form_id);
			} ?>
		</div>
		<?php
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
