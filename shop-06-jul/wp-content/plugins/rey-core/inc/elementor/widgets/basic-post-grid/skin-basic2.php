<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Widget_Basic_Post_Grid__Basic2') ):

	class ReyCore_Widget_Basic_Post_Grid__Basic2 extends \Elementor\Skin_Base
	{

		public function get_id() {
			return 'basic2';
		}

		public function get_title() {
			return __( 'Default 2', 'rey-core' );
		}

		/**
		 * Render meta
		 *
		 * @since 1.0.0
		 **/
		public function render_meta($settings)
		{

			if( 'yes' === $settings['meta_author'] || 'yes' === $settings['meta_comments'] ): ?>
				<div class="rey-postInfo">
				<?php
					if( 'yes' === $settings['meta_author'] ){
						if( function_exists('rey__posted_by') ){
							rey__posted_by();
						}
					}
					if( 'yes' === $settings['meta_comments'] ){
						if( function_exists('rey__comment_count') ){
							rey__comment_count();
						}
					}
					if( function_exists('rey__edit_link') ){
						rey__edit_link();
					}
				?>
				</div>
			<?php endif;
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
				<?php
					$this->parent->render_thumbnail();

					$this->render_meta($settings);

					echo '<div class="basic2-postMeta">';

						if( 'yes' === $settings['meta_date'] ){
							echo sprintf(
								'<span class="rey-entryDate"><time datetime="%1$s">%2$s</time></span>',
								esc_attr(get_the_date(DATE_W3C)),
								esc_html(get_the_date('m / y'))
							);
						}

						$this->parent->render_title();

					echo '</div>';

					$this->parent->render_excerpt();
					$this->parent->render_footer();
				?>
			</div>
			<?php endwhile;
			wp_reset_postdata();

			$this->parent->render_end();
		}

	}
endif;
