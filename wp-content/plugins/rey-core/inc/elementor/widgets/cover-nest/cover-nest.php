<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if(!class_exists('ReyCore_Widget_Cover_Nest')):

	/**
	 *
	 * Elementor widget.
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Widget_Cover_Nest extends \Elementor\Widget_Base {

		private $_items = [];

		private $_settings = [];

		public function get_name() {
			return 'reycore-cover-nest';
		}

		public function get_title() {
			return __( 'Cover - Nest Slider', 'rey-core' );
		}

		public function get_icon() {
			return 'rey-font-icon-general-r';
		}

		public function get_categories() {
			return [ 'rey-theme-covers' ];
		}

		public function get_script_depends() {
			return [ 'animejs', 'jquery-slick', 'reycore-widget-cover-nest-scripts' ];
		}

		public function get_custom_help_url() {
			return 'https://support.reytheme.com/kb/rey-elements-covers/#nest-slider';
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
				'section_content',
				[
					'label' => __( 'Content', 'rey-core' ),
				]
			);

			$items = new \Elementor\Repeater();

			$items->add_control(
				'image',
				[
				'label' => __( 'Image', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'default' => [
						'url' => \Elementor\Utils::get_placeholder_image_src(),
					],
				]
			);

			$items->add_control(
				'overlay_color',
				[
					'label' => __( 'Overlay Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} {{CURRENT_ITEM}}.cNest-slide:after' => 'background-color: {{VALUE}}',
					],
				]
			);

			$items->add_control(
				'captions',
				[
					'label' => __( 'Enable Captions', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$items->add_control(
				'label',
				[
					'label'       => __( 'Label Text', 'rey-core' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'label_block' => true,
					'conditions' => [
						'terms' => [
							[
								'name' => 'captions',
								'operator' => '!=',
								'value' => '',
							],
						],
					],
				]
			);

			$items->add_control(
				'title',
				[
					'label'       => __( 'Title', 'rey-core' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'label_block' => true,
					'conditions' => [
						'terms' => [
							[
								'name' => 'captions',
								'operator' => '!=',
								'value' => '',
							],
						],
					],
				]
			);

			$items->add_control(
				'subtitle',
				[
					'label'       => __( 'Subtitle Text', 'rey-core' ),
					'type'        => \Elementor\Controls_Manager::TEXTAREA,
					'label_block' => true,
					'conditions' => [
						'terms' => [
							[
								'name' => 'captions',
								'operator' => '!=',
								'value' => '',
							],
						],
					],
				]
			);

			$items->add_control(
				'button_text',
				[
					'label' => __( 'Button Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'dynamic' => [
						'active' => true,
					],
					'default' => __( 'Click here', 'rey-core' ),
					'placeholder' => __( 'eg: SHOP NOW', 'rey-core' ),
					'conditions' => [
						'terms' => [
							[
								'name' => 'captions',
								'operator' => '!=',
								'value' => '',
							],
						],
					],
				]
			);

			$items->add_control(
				'button_url',
				[
					'label' => __( 'Link', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::URL,
					'dynamic' => [
						'active' => true,
					],
					'placeholder' => __( 'https://your-link.com', 'rey-core' ),
					'default' => [
						'url' => '#',
					],
					'conditions' => [
						'terms' => [
							[
								'name' => 'captions',
								'operator' => '!=',
								'value' => '',
							],
						],
					],
				]
			);

			$items->add_control(
				'text_color',
				[
					'label' => __( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} {{CURRENT_ITEM}}.cNest-caption' => 'color: {{VALUE}}',
					],
					'conditions' => [
						'terms' => [
							[
								'name' => 'captions',
								'operator' => '!=',
								'value' => '',
							],
						],
					],
				]
			);

			$this->add_control(
				'items',
				[
					'label' => __( 'Items', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $items->get_controls(),
					'default' => [
						[
							'image' => [
								'url' => \Elementor\Utils::get_placeholder_image_src(),
							],
							'captions' => 'yes',
							'label' => __( 'Label Text #1', 'rey-core' ),
							'title' => __( 'Title Text #1', 'rey-core' ),
							'subtitle' => __( 'Subtitle Text #1', 'rey-core' ),
							'button_text' => __( 'Button Text #1', 'rey-core' ),
							'button_url' => [
								'url' => '#',
							],
						],
						[
							'image' => [
								'url' => \Elementor\Utils::get_placeholder_image_src(),
							],
							'captions' => 'yes',
							'label' => __( 'Label Text #2', 'rey-core' ),
							'title' => __( 'Title Text #2', 'rey-core' ),
							'subtitle' => __( 'Subtitle Text #2', 'rey-core' ),
							'button_text' => __( 'Button Text #2', 'rey-core' ),
							'button_url' => [
								'url' => '#',
							],
						],
					]
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Image_Size::get_type(),
				[
					'name' => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
					'default' => 'large',
					// 'separator' => 'before',
					'exclude' => ['custom'],
					// TODO: add support for custom size thumbnails #40
				]
			);

			$this->end_controls_section();

			/**
			 * Social Icons
			 */

			$this->start_controls_section(
				'section_social',
				[
					'label' => __( 'Social Icons', 'rey-core' ),
				]
			);

			$social_icons = new \Elementor\Repeater();

			$social_icons->add_control(
				'social',
				[
					'label' => __( 'Show Elements', 'plugin-domain' ),
					'type' => \Elementor\Controls_Manager::SELECT2,
					'label_block' => true,
					'options' => reycore__social_icons_list_select2(),
					'default' => 'wordpress',
				]
			);

			$social_icons->add_control(
				'link',
				[
					'label' => __( 'Link', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::URL,
					'label_block' => true,
					'default' => [
						'is_external' => 'true',
					],
					'placeholder' => __( 'https://your-link.com', 'rey-core' ),
				]
			);

			$this->add_control(
				'social_icon_list',
				[
					'label' => __( 'Social Icons', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $social_icons->get_controls(),
					'default' => [
						[
							'social' => 'facebook',
						],
						[
							'social' => 'twitter',
						],
						[
							'social' => 'google-plus',
						],
					],
					'title_field' => '{{{ social.replace( \'-\', \' \' ).replace( /\b\w/g, function( letter ){ return letter.toUpperCase() } ) }}}',
				]
			);

			$this->add_control(
				'social_text',
				[
					'label' => __( '"Follow" Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => __( 'FOLLOW US', 'rey-core' ),
					'placeholder' => __( 'eg: FOLLOW US', 'rey-core' ),
				]
			);

			$this->end_controls_section();

		/**
		 * Bottom Content
		 */

			$this->start_controls_section(
				'section_bottom_text',
				[
					'label' => __( 'Bottom Text', 'rey-core' ),
				]
			);

			$this->add_control(
				'bottom_text',
				[
					'label' => __( 'Content', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::WYSIWYG,
					'default' => '',
					'placeholder' => __( 'Type your content here', 'rey-core' ),
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'bottom_text_typo',
					'selector' => '{{WRAPPER}} .cNest-contact',
				]
			);

			$this->end_controls_section();


			$this->start_controls_section(
				'section_slider',
				[
					'label' => __( 'Slider Settings', 'rey-core' ),
				]
			);

			$this->add_control(
				'autoplay',
				[
					'label' => __( 'Autoplay', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'autoplay_duration',
				[
					'label' => __( 'Autoplay Duration (ms)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 5000,
					'min' => 2000,
					'max' => 20000,
					'step' => 50,
					'condition' => [
						'autoplay!' => '',
					],
				]
			);

			$this->add_control(
				'bars_nav',
				[
					'label' => __( 'Bars Navigation', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'effect',
				[
					'label' => __( 'Transition Effect', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'scaler',
					'options' => [
						'slide'  => __( 'Slide', 'rey-core' ),
						'fade'  => __( 'Fade In/Out', 'rey-core' ),
						'scaler'  => __( 'Scale & Fade', 'rey-core' ),
					],
				]
			);

			$this->end_controls_section();


			$this->start_controls_section(
				'section_general_styles',
				[
					'label' => __( 'General Styles', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_control(
				'bg_color',
				[
					'label' => __( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .cNest-nestLines svg' => 'stroke: {{VALUE}}',
						'{{WRAPPER}} .cNest-borders span' => 'background-color: {{VALUE}}',
						'{{WRAPPER}} .cNest-loadingBg--1' => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'text_color',
				[
					'label' => __( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,

					'selectors' => [
						'{{WRAPPER}} .cNest-footer' => 'color: {{VALUE}}',
						':root' => '--header-text-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'lines',
				[
					'label' => __( 'Enable Decoration Lines', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'lines_width',
				[
					'label' => __( 'Lines Width', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 40,
					'min' => 5,
					'max' => 50,
					'step' => 1,
					'condition' => [
						'lines' => 'yes',
					],
				]
			);


			$this->end_controls_section();


			$this->start_controls_section(
				'section_style',
				[
					'label' => __( 'Slider Styles', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);


			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'label_typo',
					'label' => esc_html__('Label Typography', 'rey-core'),
					'selector' => '{{WRAPPER}} .cNest-captionLabel',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'title_typo',
					'label' => esc_html__('Title Typography', 'rey-core'),
					'scheme' => \Elementor\Scheme_Typography::TYPOGRAPHY_1,
					'selector' => '{{WRAPPER}} .cNest-captionTitle',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'subtitle_typo',
					'label' => esc_html__('Sub-Title Typography', 'rey-core'),
					'selector' => '{{WRAPPER}} .cNest-captionSubtitle',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'button_typo',
					'label' => esc_html__('Button Typography', 'rey-core'),
					'selector' => '{{WRAPPER}} .cNest-captionBtn a',
				]
			);

			$this->add_control(
				'button_style',
				[
					'label' => __( 'Button Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'btn-line-active',
					'options' => [
						'btn-simple'  => __( 'Link', 'rey-core' ),
						'btn-primary'  => __( 'Primary', 'rey-core' ),
						'btn-secondary'  => __( 'Secondary', 'rey-core' ),
						'btn-primary-outline'  => __( 'Primary Outlined', 'rey-core' ),
						'btn-secondary-outline'  => __( 'Secondary Outlined', 'rey-core' ),
						'btn-line-active'  => __( 'Underlined', 'rey-core' ),
						'btn-line'  => __( 'Hover Underlined', 'rey-core' ),
						'btn-primary-outline btn-dash'  => __( 'Primary Outlined & Dash', 'rey-core' ),
					],
				]
			);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_misc_styles',
				[
					'label' => __( 'Misc. Styles', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
					]
				);

			$this->add_control(
				'loading_bg_color_1',
				[
					'label' => __( 'Loader Curtain Color (1st)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .cNest-loadingBg--1' => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'loading_bg_color_2',
				[
					'label' => __( 'Loader Curtain Color (2nd)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .cNest-loadingBg--2' => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->end_controls_section();
		}


		public function render_start(){

			$this->add_render_attribute( 'wrapper', 'class', 'rey-coverNest' );

			if( ! reycore__preloader_is_active() ){
				$this->add_render_attribute( 'wrapper', 'class', '--loading' );
			}

			if( $this->_settings['effect'] == 'scaler' ){
				$this->add_render_attribute( 'wrapper', 'class', '--scaler' );
			}

			if( count($this->_items) > 1 ) {
				$this->add_render_attribute( 'wrapper', 'data-slider-settings', wp_json_encode([
					'autoplay' => $this->_settings['autoplay'] !== '',
					'autoplaySpeed' => $this->_settings['autoplay_duration'],
					'effect' => $this->_settings['effect'],
				]) );
			}
			?>
			<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
				<div class="cNest-loadingBg cNest-loadingBg--1 cNest--abs"></div>
				<div class="cNest-loadingBg cNest-loadingBg--2 cNest--abs"></div>
					<?php
		}

		public function render_end(){
			?>
			</div>
			<?php
			echo ReyCoreElementor::edit_mode_widget_notice(['full_viewport', 'tabs_modal']);
		}

		public function render_slides()
		{
			?>
			<div class="cNest-slider" >
				<?php

				if( !empty($this->_items) ):
					foreach($this->_items as $key => $item): ?>
					<div class="cNest-slide elementor-repeater-item-<?php echo $item['_id'] ?>">
						<?php
						echo reycore__get_attachment_image( [
							'image' => $item['image'],
							'size' => $this->_settings['image_size'],
							'attributes' => ['class'=>'cNest-slideContent cNest--abs']
						] ); ?>
					</div>
					<?php
					endforeach;
				endif; ?>
			</div>
			<?php
		}

		public function render_captions()
		{
			if( !empty($this->_items) ): ?>
				<div class="cNest-captions" >
				<?php
					foreach($this->_items as $key => $item):
						if( $item['captions'] !== '' ): ?>
						<div class="cNest-caption elementor-repeater-item-<?php echo $item['_id'] ?>">
							<?php if( $label = $item['label'] ): ?>
							<div class="cNest-captionEl cNest-captionLabel"><?php echo $label ?></div>
							<?php endif; ?>

							<?php if( $title = $item['title'] ): ?>
							<h2 class="cNest-captionEl cNest-captionTitle"><?php echo $title ?></h2>
							<?php endif; ?>

							<?php if( $subtitle = $item['subtitle'] ): ?>
							<div class="cNest-captionEl cNest-captionSubtitle"><?php echo $subtitle ?></div>
							<?php endif; ?>

							<?php if( $button_text = $item['button_text'] ): ?>
								<div class="cNest-captionEl cNest-captionBtn">

									<?php
									$url_key = 'url'.$key;

									$this->add_render_attribute( $url_key , 'class', 'btn ' . $this->_settings['button_style'] );

									if( isset($item['button_url']['url']) && $url = $item['button_url']['url'] ){
										$this->add_render_attribute( $url_key , 'href', $url );

										if( $item['button_url']['is_external'] ){
											$this->add_render_attribute( $url_key , 'target', '_blank' );
										}

										if( $item['button_url']['nofollow'] ){
											$this->add_render_attribute( $url_key , 'rel', 'nofollow' );
										}
									} ?>
									<a <?php echo  $this->get_render_attribute_string($url_key); ?>>
										<?php echo $button_text; ?>
									</a>
								</div>
								<!-- .cNest-btn -->
							<?php endif; ?>

						</div><?php
						endif;
					endforeach; ?>
				</div>
				<?php
			endif;
		}

		public function render_decorations()
		{
			?>
			<div class="cNest-decorations cNest--abs">
				<?php
					$this->render_borders();
					$this->render_lines();
				?>
			</div>
			<?php
		}

		public function render_lines(){

			if( $this->_settings['lines'] === '' ){
				return;
			} ?>

			<div class="cNest-nestLines" data-stroke="<?php echo esc_attr( $this->_settings['lines_width'] ) ?>">
				<svg viewBox="0 0 1920 1080" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid slice">
					<g fill="none" fill-rule="evenodd">
						<path d="M0,856.513433 L1920,201.5"></path>
						<path d="M519.5,679.48665 L56.4884224,0"></path>
						<path d="M519.4875,679.48665 L792,1079.51335"></path>
						<path d="M1190.51242,449.514039 L759.487585,0.485961123"></path>
						<path d="M1631.5125,300.486284 L1281.4875,1079.51372"></path>
						<path d="M1565.48764,447.486807 L1920,815.513193"></path>
					</g>
				</svg>
			</div>
			<?php
		}

		public function render_borders(){
			?>
			<div class="cNest-borders">
				<span></span>
				<span></span>
				<span></span>
				<span></span>
			</div>
			<?php
		}

		public function render_contact(){
			if( $text = $this->_settings['bottom_text'] ): ?>
				<div class="cNest-contact elementor-text-editor">
					<?php echo $text ?>
				</div>
			<?php
			endif;
		}

		public function render_nav(){
			?>
				<div class="cNest-nav"></div>
			<?php
		}

		public function render_social(){

			if( $social_icon_list = $this->_settings['social_icon_list'] ): ?>

				<div class="cNest-social">

					<?php if($social_text = $this->_settings['social_text']): ?>
						<div class="cNest-socialText"><?php echo $social_text ?></div>
					<?php endif; ?>

					<div class="cNest-socialIcons">
						<?php
						foreach ( $social_icon_list as $index => $item ):

							$link_key = 'link_' . $index;

							$this->add_render_attribute( $link_key, 'href', $item['link']['url'] );

							if ( $item['link']['is_external'] ) {
								$this->add_render_attribute( $link_key, 'target', '_blank' );
							}

							if ( $item['link']['nofollow'] ) {
								$this->add_render_attribute( $link_key, 'rel', 'nofollow' );
							}
							?>
							<a class="cNest-socialIcons-link" <?php echo $this->get_render_attribute_string( $link_key ); ?>>
								<?php echo reycore__get_svg_social_icon([ 'id'=>$item['social'] ]); ?>
							</a>
						<?php endforeach; ?>
					</div>

				</div>
				<!-- .cNest-social -->
			<?php endif;
		}

		public function render_footer(){
			?>
			<div class="cNest-footer">
				<div class="cNest-footerInner">
				<?php

					$this->render_contact();

					if( $this->_settings['bars_nav'] !== '' && $this->_items > 1 ){
						$this->render_nav();
					}

					$this->render_social();
				?>
				</div>
			</div>
			<?php
		}

		protected function render() {

			$this->_settings = $this->get_settings_for_display();
			$this->_items = $this->_settings['items'];

			$this->render_start();
			$this->render_slides();
			$this->render_captions();
			$this->render_decorations();
			$this->render_footer();
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
