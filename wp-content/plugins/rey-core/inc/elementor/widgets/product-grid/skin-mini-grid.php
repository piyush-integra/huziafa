<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Widget_Product_Grid__Mini') ):

	class ReyCore_Widget_Product_Grid__Mini extends \Elementor\Skin_Base
	{

		public function get_id() {
			return 'mini';
		}

		public function get_title() {
			return __( 'Mini Grid', 'rey-core' );
		}

		public function get_script_depends() {
			return [];
		}

		protected function _register_controls_actions() {
			parent::_register_controls_actions();

			add_action( 'elementor/element/reycore-product-grid/section_layout/after_section_end', [ $this, 'register_carousel_controls' ] );
		}

		public function register_carousel_controls( $element ){


		}

		public function loop_start()
		{
			wc_set_loop_prop( 'loop', 0 );

			$parent_classes = $this->parent->get_css_classes();
			unset( $parent_classes['grid_layout'] );

			$settings = $this->parent->get_settings_for_display();

			$classes = [
				'--prevent-metro', // make sure it does not have thumbnail slideshow
				'--prevent-thumbnail-sliders', // make sure it does not have thumbnail slideshow
				'--prevent-scattered', // make sure scattered is not applied
				'--prevent-masonry', // make sure masonry is not applied
			];

			$classes['columns'] = 'columns-' . wc_get_loop_prop('columns');

			$cols_per_tablet = isset($settings['per_row_tablet'] ) && $settings['per_row_tablet'] ? $settings['per_row_tablet'] : reycore_wc_get_columns('tablet');
			$classes['columns-tablet'] = 'columns-tablet-' . absint( $cols_per_tablet );

			$classes['columns-mobile'] = 'columns-mobile-1';

			printf('<ul class="products %s">', implode(' ', array_merge( $classes, $parent_classes ) ) );
		}

		public function loop_end(){
			echo '</ul>';
		}

		public function render_products( $products )
		{
			if( isset($GLOBALS['post']) ) {
				$original_post = $GLOBALS['post'];
			}

			if ( wc_get_loop_prop( 'total' ) ) {

				if( $this->parent->get_settings_for_display('entry_animation') !== 'yes' ){
					wc_set_loop_prop( 'entry_animation', false );
				}

				add_filter( 'post_class', function($classes){
					unset($classes['rey_skin']);
					return $classes;
				}, 20 );

				foreach ( $products->ids as $product_id ) {
					$GLOBALS['post'] = get_post( $product_id ); // WPCS: override ok.
					setup_postdata( $GLOBALS['post'] );
					// Render product template.
					// wc_get_template_part( 'content', 'product' );
					$this->render_product( $GLOBALS['post'] );
				}
			}

			if( isset($original_post) ) {
				$GLOBALS['post'] = $original_post; // WPCS: override ok.
			}
		}

		public function render_product( $product ){

			$settings = $this->parent->get_settings_for_display();

			?>
			<li <?php wc_product_class( '', $product ); ?>>

				<?php if( $settings['hide_thumbnails'] !== 'yes' ): ?>
				<div class="rey-mini-img rey-productThumbnail">
					<?php
						woocommerce_template_loop_product_link_open();
						woocommerce_template_loop_product_thumbnail();
						woocommerce_template_loop_product_link_close();
					?>
				</div>
				<?php endif; ?>

				<div class="rey-mini-content">
					<?php

						if( $settings['hide_brands'] !== 'yes' && class_exists('ReyCore_WooCommerce_Loop') ):
							ReyCore_WooCommerce_Loop::getInstance()->component_brands();
						endif;

						if( $settings['hide_category'] !== 'yes' && class_exists('ReyCore_WooCommerce_Loop') ):
							ReyCore_WooCommerce_Loop::getInstance()->component_product_category();
						endif;

						echo sprintf(
							'<h2 class="%s"><a href="%s">%s</a></h2>',
							esc_attr( apply_filters( 'woocommerce_product_loop_title_classes', 'woocommerce-loop-product__title' ) ),
							esc_url(get_the_permalink()),
							get_the_title()
						);

						if( $settings['hide_ratings'] !== 'yes' ):
							woocommerce_template_loop_rating();
						endif;

						if( $settings['hide_prices'] !== 'yes' ):
							woocommerce_template_loop_price();
						endif;

						if( $settings['hide_add_to_cart'] !== 'yes' ):
							woocommerce_template_loop_add_to_cart();
						endif;
					?>
				</div>
			</li>
		<?php
		}

		/**
		 * Render widget output on the frontend.
		 *
		 * Written in PHP and used to generate the final HTML.
		 *
		 * @since 1.0.0
		 * @access public
		 */
		public function render() {

			$this->parent->get_query_args();

			$products = $this->parent->get_query_results();

			if ( $products && $products->ids ) {
				$settings = $this->parent->get_settings_for_display();
				$this->parent->render_start( $settings, $products );
					$this->loop_start();
					$this->render_products( $products );
					$this->loop_end();
				$this->parent->render_end();
			}
			else {
				wc_get_template( 'loop/no-products-found.php' );
			}
		}

	}
endif;
