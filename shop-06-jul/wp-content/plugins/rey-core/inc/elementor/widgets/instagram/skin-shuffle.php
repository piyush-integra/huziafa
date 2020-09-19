<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Widget_Instagram__Shuffle') ):

	class ReyCore_Widget_Instagram__Shuffle extends \Elementor\Skin_Base
	{

		public function get_id() {
			return 'shuffle';
		}

		public function get_title() {
			return __( 'Shuffle', 'rey-core' );
		}

		public function get_script_depends() {
			return [ 'reycore-widget-instagram-scripts' ];
		}

		public function render_items(){
			$settings = $this->parent->get_settings_for_display();

			if( isset($this->parent->_items['items']) && !empty($this->parent->_items['items']) ){

				$anim_class =  !\Elementor\Plugin::$instance->editor->is_edit_mode() ? 'rey-elInsta-item--animated': '';

				if( 'yes' === $settings['enable_box'] ){

					// box url
					if( !empty($settings['box_url']) ){
						$box_url = $settings['box_url'];
					}
					else {
						$box_url = 'https://www.instagram.com/' . $this->parent->_items['username'];
					}
					// box text
					if( !empty($settings['box_text']) ){
						$box_text = $settings['box_text'];
					}
					else {
						$box_text = $this->parent->_items['username'];
					}

					$hide_mobile = $settings['hide_box_mobile'] === 'yes' ? '--hide-mobile' : '';

					$shuffle_item = '<div class="rey-elInsta-item rey-gapItem rey-elInsta-shuffleItem '. $anim_class .' '. $hide_mobile .'">';
						$shuffle_item .= '<div>';
						$shuffle_item .= '<a href="'. $box_url .'" class="rey-instaItem-link" target="_blank"><span>'.$box_text.'</span></a>';
						$shuffle_item .= '</div>';
					$shuffle_item .= '</div>';
				}

				foreach ($this->parent->_items['items'] as $key => $item) {

					$link = $this->parent->get_url($item);

					if( ($key + 1) == $settings['box_position'] ) {
						echo $shuffle_item;
					}

					echo '<div class="rey-elInsta-item rey-gapItem '. $anim_class .'">';
						echo '<a href="'. $link['url'] .'" class="rey-instaItem-link" title="'. $item['image-caption'] .'" '. $link['attr'] .'>';
							echo '<img src="'. $item['image-url'] .'" alt="'. $item['image-caption'] .'" class="rey-instaItem-img">';
						echo '</a>';
					echo '</div>';
				}

			}
		}

		public function render() {
			$this->parent->query_items();
			$this->parent->render_start();
			$this->render_items();
			$this->parent->render_end();
		}
	}
endif;
