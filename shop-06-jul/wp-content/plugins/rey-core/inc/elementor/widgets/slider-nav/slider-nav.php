<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if(!class_exists('ReyCore_Widget_Slider_Nav')):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Slider_Nav extends \Elementor\Widget_Base {

	public $_settings = [];

	public function get_name() {
		return 'reycore-slider-nav';
	}

	public function get_title() {
		return __( 'Slider Navigation', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-post-navigation';
	}

	public function get_categories() {
		return [ 'rey-theme' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements/#slider-navigation';
	}

	protected function _register_skins() {
		$this->add_skin( new ReyCore_Widget_Slider_Nav__Bullets( $this ) );
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
			'slider_id',
			[
				'label' => __( 'Slider Unique ID', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( '', 'rey-core' ),
				'description' => __( 'Supported widgets: Product Grid - Carousel .', 'rey-core' ),
				'placeholder' => __( 'eg: .rey-gridCarousel-a6596db', 'rey-core' ),
				'label_block' => true

			]
		);

		$this->add_control(
			'show_counter',
			[
				'label' => __( 'Show Counter', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'_skin' => '',
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
			'color',
			[
				'label' => __( 'Primary Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-sliderNav' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'align',
			[
				'label' => __( 'Horizontal Align', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => __( 'Default', 'rey-core' ),
					'flex-start' => __( 'Start', 'rey-core' ),
					'center' => __( 'Center', 'rey-core' ),
					'flex-end' => __( 'End', 'rey-core' ),
					'space-between' => __( 'Space Between', 'rey-core' ),
					'space-around' => __( 'Space Around', 'rey-core' ),
					'space-evenly' => __( 'Space Evenly', 'rey-core' ),
				],
				'selectors' => [
					'{{WRAPPER}} .rey-sliderNav' => 'justify-content: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'bullets_style',
			[
				'label' => __( 'Bullets Style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'lines',
				'options' => [
					'lines'  => __( 'Navigation Lines', 'rey-core' ),
					'dots'  => __( 'Navigation Dots', 'rey-core' ),
				],
				'condition' => [
					'_skin' => 'bullets',
				],
			]
		);

		$this->add_control(
			'bullets_width',
			[
				'label' => __( 'Bullets Width', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 2,
				'max' => 200,
				'step' => 1,
				'condition' => [
					'_skin' => 'bullets',
				],
				'selectors' => [
					'{{WRAPPER}} .rey-sliderNav--bullets-lines .slick-dots button' => 'width: calc({{VALUE}}px + 16px)',
					'{{WRAPPER}} .rey-sliderNav--bullets-dots .slick-dots button' => 'width: calc({{VALUE}}px + 16px); height: calc({{VALUE}}px + 16px);',
				],

			]
		);



		$this->end_controls_section();


	}

	public function render_start(){

		$this->add_render_attribute( 'wrapper', 'class', [
			'rey-sliderNav',
			'rey-sliderNav' . ( $this->_settings['_skin'] == 'bullets' ? '--bullets' : '--arrows' )
		] );

		if( $this->_settings['_skin'] == 'bullets' ) {
			$this->add_render_attribute( 'wrapper', 'class', 'rey-sliderNav--bullets-' . $this->_settings['bullets_style'] );
		}

		if( $slider_id = $this->_settings['slider_id'] ){
			$this->add_render_attribute( 'wrapper', 'data-slider-id', esc_attr($slider_id) );
		}

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
		<?php
	}

	public function render_end(){
		?></div><?php
	}

	public function render_counter(){
		if( $this->_settings['show_counter'] === 'yes' ): ?>
			<div class="rey-sliderNav-counter">
				<span class="rey-sliderNav-counterCurrent"></span>
				<span class="rey-sliderNav-counterSeparator">&mdash;</span>
				<span class="rey-sliderNav-counterTotal"></span>
			</div>
		<?php endif;
	}

	protected function render() {

		$this->_settings = $this->get_settings_for_display();

		$this->render_start();

		echo reycore__arrowSvg(false);

		$this->render_counter();

		echo reycore__arrowSvg();

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
