<?php
/**
 * Rey Ajax Product Filter by Category
 */
if (!class_exists('REYAJAXFILTERS_Category_Filter_Widget')) {
	class REYAJAXFILTERS_Category_Filter_Widget extends WP_Widget {
		/**
		 * Register widget with WordPress.
		 */
		function __construct() {

			parent::__construct(
				'reyajfilter-category-filter', // Base ID
				__('Rey Filter - by Category', 'rey-core'), // Name
				array('description' => __('Filter woocommerce products by category.', 'rey-core')) // Args
			);

			$this->defaults = [
				'title'              => '',
				'custom_height'      => '',
				'query_type'         => 'or',
				'hide_empty'         => true,
				'search_box'         => false,
				'enable_multiple'    => false,
				'show_count'         => false,
				'hierarchical'       => false,
				'accordion_list'     => false,
				'show_checkboxes'    => false,
				'show_children_only' => false,
				'display_type'       => 'list',
				'rey_multi_col'      => false,
				'alphabetic_menu'    => false,
				// dropdown
				'placeholder'        => '',
				'dd_width'        => '',
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

			// enqueue necessary scripts
			wp_enqueue_style('reyajfilter-style');
			wp_enqueue_script('reyajfilter-script');

			if ( ! ($query_type = $instance['query_type']) ) {
				return;
			}

			$taxonomy   = 'product_cat';
			$display_type = $instance['display_type'];
			$is_list = $display_type === 'list';
			$data_key   = ($query_type === 'and') ? 'product-cata' : 'product-cato';

			// parse url
			$url = $_SERVER['QUERY_STRING'];
			parse_str($url, $url_array);

			$attr_args = [
				'taxonomy'           => $taxonomy,
				'data_key'           => $data_key,
				'url_array'          => apply_filters('reycore/ajaxfilters/query_url', $url_array),
				'query_type'         => $query_type,
				'enable_multiple'    => (bool) $instance['enable_multiple'],
				'show_count'         => (bool) $instance['show_count'],
				'enable_hierarchy'   => (bool) $instance['hierarchical'],
				'show_children_only' => (bool) $instance['show_children_only'],
				'hide_empty'         => (bool) $instance['hide_empty'],
				'custom_height'      => (!empty($instance['custom_height']) && $is_list) ? $instance['custom_height']: '',
				'alphabetic_menu'    => ((bool) $instance['alphabetic_menu'] && $is_list),
				'search_box'         => ((bool) $instance['search_box']),
				'accordion_list'     => ((bool) $instance['accordion_list'] && $is_list && (bool) $instance['hierarchical'] ),
				'show_checkboxes'    => (bool) $instance['show_checkboxes'],

				'dropdown'           => ($display_type === 'dropdown'),
				'placeholder'        => $instance['placeholder'],
				'dd_width'           => $instance['dd_width'],
			];

			$output = reyajaxfilter_terms_output($attr_args);

			if( !isset($output['html']) ){
				return;
			}

			$html = $output['html'];
			$found = $output['found'];

			extract($args);

			// Add class to before_widget from within a custom widget
			// http://wordpress.stackexchange.com/questions/18942/add-class-to-before-widget-from-within-a-custom-widget

			// if $selected_terms array is empty we will hide this widget totally
			if ($found === false) {
				$widget_class = 'reyajfilter-widget-hidden woocommerce reyajfilter-ajax-term-filter';
			} else {
				$widget_class = 'woocommerce reyajfilter-ajax-term-filter';

				if( $display_type !== 'dropdown' && $instance['rey_multi_col'] ){
					$widget_class .= ' rey-filterList-cols';
				}
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

			$instance = wp_parse_args( (array) $instance, $this->defaults );
			$display_name = $this->get_field_name('display_type');

			?>

			<div class="rey-widgetTabs-wrapper">

				<div class="rey-widgetTabs-buttons">
					<span data-tab="basic" class="--active"><?php esc_html_e('Basic options', 'rey-core') ?></span>
					<span data-tab="advanced"><?php esc_html_e('Advanced', 'rey-core') ?></span>
				</div>

				<div class="rey-widgetTabs-tabContent --active" data-tab="basic">

					<?php

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'title',
						'type' => 'text',
						'label' => __( 'Title', 'rey-core' ),
						'value' => '',
						'field_class' => 'widefat'
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'display_type',
						'type' => 'select',
						'label' => __( 'Display Type', 'rey-core' ),
						'field_class' => 'widefat',
						'options' => [
							'list' => esc_html__('List', 'rey-core'),
							'dropdown' => esc_html__('Dropdown', 'rey-core'),
						]
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'query_type',
						'type' => 'select',
						'label' => __( 'Query Type', 'rey-core' ),
						'field_class' => 'widefat',
						'options' => [
							'or' => esc_html__('OR (IN)', 'rey-core'),
							'and' => esc_html__('AND', 'rey-core'),
						]
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'type' => 'title',
						'label' => __( 'Using "AND" query type is very strict and might return empty results. Use with caution.', 'rey-core' ),
						'conditions' => [
							[
								'name' => 'query_type',
								'value' => 'and',
								'compare' => '==='
							],
						],
						'wrapper_class' => 'description',
						'field_class' => '',
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'enable_multiple',
						'type' => 'checkbox',
						'label' => __( 'Enable multiple filter', 'rey-core' ),
						'value' => '1',
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'show_count',
						'type' => 'checkbox',
						'label' => __( 'Show count', 'rey-core' ),
						'value' => '1',
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'hierarchical',
						'type' => 'checkbox',
						'label' => __( 'Show hierarchy', 'rey-core' ),
						'value' => '1',
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'show_children_only',
						'type' => 'checkbox',
						'label' => __( 'Only show children of the current category', 'rey-core' ),
						'value' => '1',
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'hide_empty',
						'type' => 'checkbox',
						'label' => __( 'Hide empty', 'rey-core' ),
						'value' => '1',
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'show_checkboxes',
						'type' => 'checkbox',
						'label' => __( 'Show checkboxes', 'rey-core' ),
						'value' => '1',
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'search_box',
						'type' => 'checkbox',
						'label' => __( 'Show search (filter) field', 'rey-core' ),
						'value' => '1',
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'type' => 'title',
						'label' => __( 'LIST OPTIONS', 'rey-core' ),
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'list',
								'compare' => '=='
							],
						],
						'field_class' => 'rey-widget-innerTitle'
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'rey_multi_col',
						'type' => 'checkbox',
						'label' => __( 'Display list on 2 columns', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'list',
								'compare' => '=='
							],
							[
								'name' => 'hierarchical',
								'value' => true,
								'compare' => '!='
							],
						]
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'accordion_list',
						'type' => 'checkbox',
						'label' => __( 'Display list as accordion', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'list',
								'compare' => '=='
							],
							[
								'name' => 'hierarchical',
								'value' => true,
								'compare' => '=='
							],
							[
								'name' => 'show_children_only',
								'value' => true,
								'compare' => '!='
							],
						]
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'alphabetic_menu',
						'type' => 'checkbox',
						'label' => __( 'Show alphabetic menu', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'list',
								'compare' => '=='
							],
						]
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'custom_height',
						'type' => 'number',
						'label' => __( 'Custom Height', 'rey-core' ),
						'value' => '',
						'field_class' => 'small-text',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'list',
								'compare' => '=='
							],
						],
						'options' => [
							'step' => 1,
							'min' => 50,
							'max' => 1000,
						],
						'suffix' => 'px'
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'type' => 'title',
						'label' => __( 'DROPDOWN OPTIONS', 'rey-core' ),
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'dropdown',
								'compare' => '=='
							],
						],
						'field_class' => 'rey-widget-innerTitle'
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'placeholder',
						'type' => 'text',
						'label' => __( 'Placeholder', 'rey-core' ),
						'value' => '',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'dropdown',
								'compare' => '=='
							],
						],
						'placeholder' => esc_html__('eg: Choose', 'rey-core')
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'dd_width',
						'type' => 'number',
						'label' => __( 'Custom dropdown width', 'rey-core' ),
						'value' => '',
						'field_class' => 'small-text',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'dropdown',
								'compare' => '=='
							],
						],
						'options' => [
							'step' => 1,
							'min' => 50,
							'max' => 1000,
						],
						'suffix' => 'px'
					]);

					?>
				</div>
				<!-- end tab -->

				<div class="rey-widgetTabs-tabContent" data-tab="advanced">

					<?php
						reyajaxfilter_widget__option( $this, $instance, [
							'name' => 'show_only_on_categories',
							'type' => 'select',
							'multiple' => true,
							'label' => __( 'Display widget on certain categories:', 'rey-core' ),
							'wrapper_class' => '--stretch',
							'options' => reycore_wc__product_categories()
						]);
					?>

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
			$instance = [];
			$instance['title']                   = sanitize_text_field($new_instance['title']);
			$instance['display_type']            = sanitize_text_field($new_instance['display_type']);
			$instance['query_type']              = sanitize_text_field($new_instance['query_type']);
			$instance['placeholder']             = sanitize_text_field($new_instance['placeholder']);
			$instance['enable_multiple']         = !empty($new_instance['enable_multiple']);
			$instance['show_count']              = !empty($new_instance['show_count']);
			$instance['hierarchical']            = !empty($new_instance['hierarchical']);
			$instance['show_children_only']      = !empty($new_instance['show_children_only']);
			$instance['rey_multi_col']           = !empty($new_instance['rey_multi_col']);
			$instance['alphabetic_menu']         = !empty($new_instance['alphabetic_menu']);
			$instance['accordion_list']          = !empty($new_instance['accordion_list']);
			$instance['show_checkboxes']         = !empty($new_instance['show_checkboxes']);
			$instance['custom_height']           = sanitize_text_field($new_instance['custom_height']);
			$instance['dd_width']                = sanitize_text_field($new_instance['dd_width']);
			$instance['search_box']              = !empty($new_instance['search_box']);
			$instance['hide_empty']              = !empty($new_instance['hide_empty']);
			$instance['show_only_on_categories'] = !empty($new_instance['show_only_on_categories']) && is_array($new_instance['show_only_on_categories']) ? array_map('sanitize_text_field', $new_instance['show_only_on_categories']) : [];
			return $instance;
		}
	}
}

// register widget
if (!function_exists('reyajaxfilter_register_category_filter_widget')) {
	function reyajaxfilter_register_category_filter_widget() {
		register_widget('REYAJAXFILTERS_Category_Filter_Widget');
	}
	add_action('widgets_init', 'reyajaxfilter_register_category_filter_widget');
}
