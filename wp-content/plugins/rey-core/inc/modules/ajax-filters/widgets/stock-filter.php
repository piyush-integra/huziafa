<?php
/**
 * Rey Ajax Product Filter in stock
 */
if (!class_exists('REYAJAXFILTERS_Stock_Filter_Widget')) {
	class REYAJAXFILTERS_Stock_Filter_Widget extends WP_Widget {
		/**
		 * Register widget with WordPress.
		 */
		function __construct() {
			parent::__construct(
				'reyajfilter-stock-filter', // Base ID
				__('Rey Filter - In Stock', 'rey-core'), // Name
				array('description' => __('Filter woocommerce products in stock.', 'rey-core')) // Args
			);
			$this->defaults = [
				'title'              => '',
				'label_title'      => '',
				'show_only_on_categories' => []
			];
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

			$instance = wp_parse_args( (array) $instance, $this->defaults );

			// bail if set to exclude on certain category
			if( !empty($instance['show_only_on_categories']) && is_tax( 'product_cat', $instance['show_only_on_categories'] ) ){
				return;
			}

			$html = '';

			// required scripts
			// enqueue necessary scripts
			wp_enqueue_style('reyajfilter-style');
			wp_enqueue_script('reyajfilter-script');

			// get values from url
			$in_stock = null;
			if (isset($_GET['in-stock']) && !empty($_GET['in-stock'])) {
				$in_stock = absint( $_GET['in-stock'] );
			}

			extract($args);

			$id = $widget_id . '-stock-check';

			$html .= '<div class="reyajfilter-stock-filter js-reyajfilter-check-filter rey-filterCheckbox">';
				$html .= sprintf('<input type="checkbox" id="%1$s" name="%1$s" data-key="in-stock" value="1" %2$s />', $id, checked(1, $in_stock, false) );
				$html .= sprintf('<label for="%s"><span class="__checkbox"></span><span class="__text">%s</span></label>', $id, $instance['label_title']);
				$html .= '</div>';

			$widget_class = 'woocommerce reyajfilter-stock-filter-widget reyajfilter-ajax-term-filter';

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

			$instance = wp_parse_args( (array) $instance, $this->defaults );

			?>
			<div class="rey-widgetTabs-wrapper">

				<div class="rey-widgetTabs-buttons">
					<span data-tab="basic" class="--active"><?php esc_html_e('Basic options', 'rey-core') ?></span>
					<span data-tab="advanced"><?php esc_html_e('Advanced', 'rey-core') ?></span>
				</div>

				<div class="rey-widgetTabs-tabContent --active" data-tab="basic">
					<p>
						<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'rey-core'); ?></label>
						<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo (!empty($instance['title']) ? esc_attr($instance['title']) : ''); ?>">
					</p>
					<p>
						<label for="<?php echo $this->get_field_id('label_title'); ?>"><?php _e('Label Title:', 'rey-core'); ?></label>
						<input class="widefat" id="<?php echo $this->get_field_id('label_title'); ?>" name="<?php echo $this->get_field_name( 'label_title' ); ?>" type="text" value="<?php echo (!empty($instance['label_title']) ? esc_attr($instance['label_title']) : esc_html__('In Stock Only', 'rey-core')); ?>" placeholder="ex: <?php esc_html_e('In Stock Only', 'rey-core') ?>">
					</p>
				</div>
				<!-- end tab -->

				<div class="rey-widgetTabs-tabContent" data-tab="advanced">

					<p>
						<label for="<?php echo $this->get_field_id('show_only_on_categories'); ?>"><?php esc_html_e('Display widget on certain categories:', 'rey-core'); ?></label>
						<select class="widefat" id="<?php echo $this->get_field_id('show_only_on_categories'); ?>" multiple name="<?php echo $this->get_field_name('show_only_on_categories'); ?>[]">
							<?php
								$categories = reycore_wc__product_categories();
								foreach ($categories as $slug => $category) {
									printf(
										'<option value="%1$s" %2$s>%3$s</option>',
										$slug,
										in_array( $slug, $instance['show_only_on_categories'], true ) ? 'selected' : '',
										$category
									);
								}
							?>
						</select>
					</p>

				</div>
				<!-- end tab -->

			</div>
			<?php
			reyajaxfilters__filter_admin_titles( $instance['show_only_on_categories'] );
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
			$instance['label_title'] = (isset($new_instance['label_title']) && !empty($new_instance['label_title'])) ? strip_tags($new_instance['label_title']) : '';
			$instance['show_only_on_categories'] = !empty($new_instance['show_only_on_categories']) && is_array($new_instance['show_only_on_categories']) ? array_map('sanitize_text_field', $new_instance['show_only_on_categories']) : [];

			return $instance;
		}
	}
}

// register widget
if (!function_exists('reyajaxfilter_register_stock_filter_widget')) {
	function reyajaxfilter_register_stock_filter_widget() {
		register_widget('REYAJAXFILTERS_Stock_Filter_Widget');
	}
	add_action('widgets_init', 'reyajaxfilter_register_stock_filter_widget');
}
