<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Widget_Basic_Post_Grid__Inner') ):

	class ReyCore_Widget_Basic_Post_Grid__Inner extends \Elementor\Skin_Base
	{

		public function get_id() {
			return 'inner';
		}

		public function get_title() {
			return __( 'Inner Content', 'rey-core' );
		}

		public function render() {

			$this->parent->query_posts();

			if ( ! $this->parent->_query->found_posts ) {
				return;
			}

			$settings = $this->parent->get_settings_for_display();

			$this->parent->render_start();

			while ( $this->parent->_query->have_posts() ) : $this->parent->_query->the_post(); ?>
			<div class="reyEl-bPostGrid-item rey-gapItem <?php echo $this->parent->animatedItemClass(); ?>">
				<div class="reyEl-bPostGrid-innerWrapper">
					<?php
						$this->parent->render_thumbnail();
					?>
					<div class="reyEl-bPostGrid-inner">
					<?php
						$this->parent->render_meta($settings);
						$this->parent->render_title();
						$this->parent->render_excerpt();
						$this->parent->render_footer();
					?>
					</div>
				</div>
			</div>
			<?php endwhile;
			wp_reset_postdata();

			$this->parent->render_end();
		}

	}
endif;
