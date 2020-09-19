<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( class_exists('Wpzoom_Instagram_Widget_API') && !class_exists('ReyCore_Widget_Instagram') ):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Instagram extends \Elementor\Widget_Base {

	public $_items = [];
	public $_errors = [];

	public function get_name() {
		return 'reycore-instagram';
	}

	public function get_title() {
		return __( 'Instagram', 'rey-core' );
	}

	public function get_icon() {
		return 'fa fa-instagram';
	}

	public function get_categories() {
		return [ 'rey-theme' ];
	}

	public function get_script_depends() {
		return [ 'reycore-widget-instagram-scripts' ];
	}

	protected function _register_skins() {
		$this->add_skin( new ReyCore_Widget_Instagram__Shuffle( $this ) );
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements/#instagram';
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

		if ( empty( $this->_items ) ) {
			/* translators: 1: Instagram Settings Page link */
			$html = sprintf( __( 'No Instagram posts found. If you have just installed or updated this plugin, please go to the <a href="%s" target="_blank">Settings page</a> and <strong>connect</strong> it with your Instagram account.', 'rey-core' ), admin_url('options-general.php?page=wpzoom-instagram-widget') );
			$content_classes = 'elementor-panel-alert elementor-panel-alert-danger';
		} else {
			$html = __( 'Your account is connected.', 'rey-core' );
			$content_classes = 'elementor-descriptor';
		}

		$this->add_control(
			'connect_msg',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => $html,
				'content_classes' => $content_classes,
			]
		);

		$this->add_control(
			'limit',
			[
				'label' => __( 'Limit', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 6,
				'min' => 1,
				'max' => 20,
				'step' => 1,
			]
		);

		$this->add_control(
			'per_row',
			[
				'label' => __( 'Items per row', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 6,
				'min' => 1,
				'max' => 7,
				'step' => 1,
			]
		);

		$this->add_control(
			'img_size',
			[
				'label' => __( 'Image Size', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'low_resolution',
				'options' => [
					'thumbnail'  => __( 'Thumbnail ( 150x150px )', 'rey-core' ),
					'low_resolution'  => __( 'Low Resolution ( 320x320px )', 'rey-core' ),
					'standard_resolution'  => __( 'Standard Resolution ( 640x640px )', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'spacing',
			[
				'label' => __( 'Spacing Gap', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => __( 'Default', 'rey-core' ),
					'no' => __( 'No Spacing', 'rey-core' ),
					'narrow' => __( 'Narrow', 'rey-core' ),
					'extended' => __( 'Extended', 'rey-core' ),
					'wide' => __( 'Wide', 'rey-core' ),
					'wider' => __( 'Wider', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'link',
			[
				'label' => __( 'Link To', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'insta',
				'options' => [
					'insta'  => __( 'Instagram Page', 'rey-core' ),
					'image'  => __( 'Image Lightbox', 'rey-core' ),
					'url'  => __( 'Caption URL', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'caption_url_target',
			[
				'label' => __( 'Caption URL Target', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '_self',
				'options' => [
					'_self'  => __( 'Same window', 'rey-core' ),
					'_blank'  => __( 'New window', 'rey-core' ),
				],
				'condition' => [
					'link' => ['url'],
				],
			]
		);

		if ( empty( $this->_items ) ) {

			$this->add_control(
				'demo_items',
				[
					'label' => esc_html__( 'Items JSON', 'rey-core' ),
					'description' => esc_html__( 'Mostly used for demo purposes. This control is used when you don\'t have an Instagram account connected', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXTAREA,
					'default' => '',
					'placeholder' => '{ .. }',
				]
			);
		}

		$this->end_controls_section();

		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

		$this->add_control(
			'top_spacing',
			[
				'label'       => esc_html__( 'Top-Spacing Items', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'eg: 2, 3, 4',
				'description' => __( 'Adds a top-spacing margin for specific items. Add item index number separated by comma.', 'rey-core' ),
				'condition' => [
					'_skin' => [''],
				],
			]
		);

		// Shuffled
		$this->add_control(
			'enable_box',
			[
				'label' => __( 'Display Username Box', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => 'yes',
				'condition' => [
					'_skin' => ['shuffle'],
				],
			]
		);

		$this->add_control(
			'box_text',
			[
				'label'       => __( 'Text', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( '', 'rey-core' ),
				'description' => __( 'Leave empty for username.', 'rey-core' ),
				'condition' => [
					'_skin' => ['shuffle'],
				],
			]
		);

		$this->add_control(
			'box_url',
			[
				'label'       => __( 'URL', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( '', 'rey-core' ),
				'description' => __( 'Leave empty for your Instagram profile.', 'rey-core' ),
				'condition' => [
					'_skin' => ['shuffle'],
				],
			]
		);

		$this->add_control(
			'box_position',
			[
				'label' => __( 'Position', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 20,
				'condition' => [
					'_skin' => ['shuffle'],
				],
			]
		);

		$this->add_control(
			'box_bg_color',
			[
				'label' => __( 'Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_4,
				],
				'selectors' => [
					'{{WRAPPER}} .rey-elInsta-shuffleItem a' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'_skin' => ['shuffle'],
				],
			]
		);

		$this->add_control(
			'box_text_color',
			[
				'label' => __( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-elInsta-shuffleItem a' => 'color: {{VALUE}}',
				],
				'condition' => [
					'_skin' => ['shuffle'],
				],
			]
		);

		$this->add_control(
			'hide_box_mobile',
			[
				'label' => __( 'Hide Username Box on Mobiles', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'_skin' => ['shuffle'],
				],
			]
		);

		$this->end_controls_section();
	}

	public function image_sizes(){
		return [
			'thumbnail' => 150,
			'low_resolution' => 320,
			'standard_resolution' => 640,
		];
	}

	public function query_items() {
		$settings = $this->get_settings_for_display();
		$img_sizes = $this->image_sizes();
		$api = Wpzoom_Instagram_Widget_API::getInstance();

		$def_items = [];

		if( isset($settings['demo_items']) && !empty($settings['demo_items']) && ( $settings_demo_items = json_decode($settings['demo_items'], true) ) ){
			$def_items = $settings_demo_items;
		}

		// used for demos
		$this->_items = apply_filters('reycore/elementor/instagram/data', $def_items, $this->get_id() );

		if( isset($this->_items['items']) ){
			$this->_items['items'] = array_slice($this->_items['items'], 0, $settings['limit']);
		}

		if( $api->is_configured() ) {

			$args = [
				'image-limit' => $settings['limit'],
				'image-width' => $img_sizes[$settings['img_size']],
				'image-resolution' => $settings['img_size'],
			];

			$insta_items = $api->get_items($args);

			if( $insta_items ){
				$this->_items = $insta_items;
			}
			else {
				$this->_errors = $api->errors->get_error_messages();
			}
		}

	}

	/**
	 * Output errors if widget is misconfigured and current user can manage options (plugin settings).
	 *
	 * @return void
	 */
	protected function display_errors($errors = []) {

		if ( current_user_can( 'edit_theme_options' ) ) {
			?>
			<p class="text-center">
				<?php _e( 'Instagram Widget misconfigured, check plugin &amp; widget settings.', 'rey-core' ); ?>
			</p>

            <?php if ( ! empty( $errors ) ): ?>
                <ul>
					<?php foreach ( $errors as $error ): ?>
                        <li class="text-center"><?php echo $error; ?></li>
					<?php endforeach; ?>
                </ul>
			<?php endif; ?>
		<?php
		} else {
			echo "&#8230;";
		}
	}

	public function get_insta_items() {
		return $this->_items;
	}

	public function get_url($item = []) {
		if( empty($item) ) {
			return;
		}

		$settings = $this->get_settings_for_display();

		// Default Instagram URL
		$url = [
			'url' => $item['link'],
			'attr' => 'target="_blank"'
		];

		// Instagram IMAGE
		if( 'image' == $settings['link'] ){
			$url = [
				'url' => $item['image-url'],
				'attr' => 'data-elementor-open-lightbox="yes"'
			];
		}

		// Instagram Caption URL
		// gets first link, if not, get default
		elseif( 'url' == $settings['link'] ){
			$matches = [];

			$regex = '/https?\:\/\/[^\" ]+/i';

			if( isset($item['image-caption']) && ($caption = $item['image-caption']) ){
				preg_match($regex, $caption, $matches);
			}

			if( !empty($matches) ){
				$url = [
					'url' => $matches[0],
					'attr' => 'data-caption-url target="'. $settings['caption_url_target'] .'"'
				];
			}
		}

		return $url;
	}

	public function render_items(){

		if( isset($this->_items['items']) && !empty($this->_items['items']) ){

			$top_spacing = array_map( 'trim', explode( ',', $this->get_settings_for_display('top_spacing') ) );
			// $anim_class =  !\Elementor\Plugin::$instance->editor->is_edit_mode() ? 'rey-elInsta-item--animated': '';
			$anim_class =  '';

			foreach ($this->_items['items'] as $key => $item) {

				$link = $this->get_url($item);

				echo '<div class="rey-elInsta-item rey-gapItem '. ( in_array( ($key + 1), $top_spacing) ? '--spaced' : '' ) . $anim_class .'">';
					echo '<a href="'. $link['url'] .'" class="rey-instaItem-link" title="'. $item['image-caption'] .'" '. $link['attr'] .'>';
						echo '<img src="'. $item['image-url'] .'" alt="'. $item['image-caption'] .'" class="rey-instaItem-img">';
					echo '</a>';
				echo '</div>';
			}
		}
	}

	public function render_start(){

		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'wrapper', 'class', [
			'rey-elInsta clearfix',
			'rey-elInsta--skin-' . ($settings['_skin'] ? $settings['_skin'] : 'default'),
			'rey-gap--' . $settings['spacing']
		] );

		$this->add_render_attribute( 'wrapper', 'data-per-row', $settings['per_row'] );

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>

		<?php
		if ( empty( $this->_items ) ) {
			/* translators: 1: Instagram Settings Page link */
			printf(
				__( '<p class="text-center">No Instagram posts found. If you have just installed or updated this plugin, please go to the <a href="%s" target="_blank">Settings page</a> and <strong>connect</strong> it with your Instagram account.</p>', 'rey-core' ),
				admin_url('options-general.php?page=wpzoom-instagram-widget')
			);

			$this->display_errors($this->_errors);
		}

	}

	public function render_end(){
		?>
		</div>
		<?php
	}

	protected function render() {
		$this->query_items();
		$this->render_start();
		$this->render_items();
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
