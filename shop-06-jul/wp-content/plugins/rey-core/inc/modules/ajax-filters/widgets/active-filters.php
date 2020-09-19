<?php
/**
 * WC Ajax Active Filters
 */
if (!class_exists('REYAJAXFILTERS_Active_Filters_Widget')) {
	class REYAJAXFILTERS_Active_Filters_Widget extends WP_Widget {
		/**
		 * Register widget with WordPress.
		 */
		function __construct() {
			parent::__construct(
				'reyajfilter-active-filters', // Base ID
				__('Rey - Active Filters', 'rey-core'), // Name
				array('description' => __('Shows active filters so users can see and deactivate them.', 'rey-core')) // Args
			);
		}

		/**
		 * Front-end display of widget.
		 *
		 * @see WP_Widget::widget()
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		public function widget($args, $instance) {

			if ( ! apply_filters('reycore/ajaxfilters/widgets_support', false) ) {
				reyAjaxFilters()->show_widgets_notice_for_pages();
				return;
			}

			$reyAjaxFilters = reyAjaxFilters();

			// enqueue necessary scripts
			wp_enqueue_style('reyajfilter-style');
			wp_enqueue_script('reyajfilter-script');

			$chosen_filters = $reyAjaxFilters->chosen_filters;

			if( empty($chosen_filters) ){
				return;
			}

			$active_filters = $chosen_filters['active_filters'];
			$found = false;
			$html = $filters_html = '';

			$close = reycore__get_svg_icon(['id' => 'rey-icon-close']);

			if (!empty($active_filters)) {

				$found = true;

				if( ! get_theme_mod('ajaxfilters__hide_active_tax_filter', false) && is_tax() && $current_tax = get_queried_object() ){
					if( isset($current_tax->term_id) && isset($active_filters['term']) ){
						foreach ($active_filters['term'] as $key => $term_group) {
							if( array_key_exists($current_tax->term_id, $term_group) ){
								if( count($term_group) === 1 ){
									unset($active_filters['term'][$key]);
								}
								else {
									unset($active_filters['term'][$key][$current_tax->term_id]);
								}

								if( empty($active_filters['term']) ){
									unset($active_filters['term']);
								}
							}
						}
					}
				}

				foreach ($active_filters as $key => $active_filter) {

					if ($key === 'term') {
						foreach ($active_filter as $data_key => $terms) {
							foreach ($terms as $term_id => $term_tax) {
								$term_data = reyajaxfilter_get_term_data($term_id, $term_tax);
								if( isset($term_data->name) ){
									$filters_html .= '<a href="javascript:void(0)" data-key="' . $data_key . '" data-value="' . $term_id . '">' . $close. '<span>' . $term_data->name . '</span></a>';
								}
							}
						}
					}

					if ($key === 'keyword') {
						$filters_html .= '<a href="javascript:void(0)" data-key="keyword">' . $close . '<span>' . __('Search For: ', 'rey-core') . $active_filter . '</span></a>';
					}

					if (apply_filters('reycore/ajaxfilters/active_filters/order_display', false) && $key === 'orderby') {
						$filters_html .= '<a href="javascript:void(0)" data-key="orderby">' . $close . '<span>' . __('Orderby: ', 'rey-core') . $active_filter . '</span></a>';
					}

					if( $active_filter !== '' ) {
						if ($key === 'min_price' ) {
							$filters_html .= '<a href="javascript:void(0)" data-key="min-price">'. $close . '<span>' . __('Min Price: ', 'rey-core') . $active_filter . '</span></a>';
						}

						if ($key === 'max_price') {
							$filters_html .= '<a href="javascript:void(0)" data-key="max-price">' . $close . '<span>' . __('Max Price: ', 'rey-core') . $active_filter . '</span></a>';
						}
					}

					if ($key === 'in-stock') {
						$filters_html .= '<a href="javascript:void(0)" data-key="in-stock">' . $close . '<span>' . __('In Stock', 'rey-core') . '</span></a>';
					}

					if ($key === 'on-sale') {
						$filters_html .= '<a href="javascript:void(0)" data-key="on-sale">' . $close . '<span>' . __('On Sale', 'rey-core') . '</span></a>';
					}

					if ($key === 'is-featured') {
						$filters_html .= '<a href="javascript:void(0)" data-key="is-featured">' . $close . '<span>' . __('Featured', 'rey-core') . '</span></a>';
					}

				}

				if ( !empty($filters_html) && !empty($instance['button_text'])) {

					if ( defined( 'SHOP_IS_ON_FRONT' ) ) {
						$link = home_url();
					} elseif ( is_post_type_archive( 'product' ) || is_page( wc_get_page_id('shop') ) ) {
						$link = get_post_type_archive_link( 'product' );
					} elseif ( is_tax( get_object_taxonomies('product') ) ) {
						$link = get_term_link( get_queried_object_id() );
					} elseif( get_query_var('term') && get_query_var('taxonomy') ) {
						$link = get_term_link( get_query_var('term'), get_query_var('taxonomy') );
					} else {
						$link = get_page_link();
					}

					/**
					 * Search Arg.
					 * To support quote characters, first they are decoded from &quot; entities, then URL encoded.
					 */
					if ( get_search_query() ) {
						$link = add_query_arg( 's', rawurlencode( htmlspecialchars_decode( get_search_query() ) ), $link );
					}

					// Post Type Arg
					if ( isset( $_GET['post_type'] ) && $_GET['post_type'] ) {
						$link = add_query_arg( 'post_type', wc_clean( $_GET['post_type'] ), $link );
					}

					$filters_html .= '<a href="javascript:void(0)" class="reset" data-location="' . $link . '">' . $close . '<span>' . $instance['button_text'] . '</span></a>';
				}

				if( !empty($filters_html) ){
					$html .= '<div class="reyajfilter-active-filters">' . $filters_html . '</div>';
				}
			}

			extract($args);

			// Add class to before_widget from within a custom widget
			// http://wordpress.stackexchange.com/questions/18942/add-class-to-before-widget-from-within-a-custom-widget

			if ($found === false) {
				$widget_class = 'reyajfilter-widget-hidden woocommerce reyajfilter-ajax-term-filter';
			} else {
				$widget_class = 'woocommerce reyajfilter-ajax-term-filter';
			}

			// no class found, so add it
			if (strpos($before_widget, 'class') === false) {
				$before_widget = str_replace('>', 'class="' . $widget_class . '"', $before_widget);
			}
			// class found but not the one that we need, so add it
			else {
				$before_widget = str_replace('class="', 'class="' . $widget_class . ' ', $before_widget);
			}

			echo $before_widget;

			if (!empty($instance['title'])) {
				echo $args['before_title'] . apply_filters('widget_title', $instance['title']). $args['after_title'];
			}

			echo $html;

			echo $args['after_widget'];
		}

		/**
		 * Back-end widget form.
		 *
		 * @see WP_Widget::form()
		 *
		 * @param array $instance Previously saved values from database.
		 */
		public function form($instance) {
			?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php printf(__('Title:', 'rey-core')); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo (!empty($instance['title']) ? esc_attr($instance['title']) : ''); ?>">
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('button_text'); ?>"><?php printf(__('Button Text:', 'rey-core')); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('button_text'); ?>" name="<?php echo $this->get_field_name( 'button_text' ); ?>" type="text" value="<?php echo (!empty($instance['button_text']) ? esc_attr($instance['button_text']) : ''); ?>">
			</p>
			<?php
		}

		/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @see WP_Widget::update()
		 *
		 * @param array $new_instance Values just sent to be saved.
		 * @param array $old_instance Previously saved values from database.
		 *
		 * @return array Updated safe values to be saved.
		 */
		public function update($new_instance, $old_instance) {
			$instance = array();
			$instance['title'] = (isset($new_instance['title']) && !empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
			$instance['button_text'] = (isset($new_instance['button_text']) && !empty($new_instance['button_text'])) ? strip_tags($new_instance['button_text']) : '';
			return $instance;
		}
	}
}

// register widget
if (!function_exists('reyajaxfilter_register_active_filters_widget')) {
	function reyajaxfilter_register_active_filters_widget() {
		register_widget('REYAJAXFILTERS_Active_Filters_Widget');
	}
	add_action('widgets_init', 'reyajaxfilter_register_active_filters_widget');
}
