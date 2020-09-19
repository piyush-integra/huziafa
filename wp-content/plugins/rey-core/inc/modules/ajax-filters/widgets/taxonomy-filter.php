<?php
/**
 * Rey Ajax Product Filter by Tag
 */
if (!class_exists('REYAJAXFILTERS_Taxonomy_Filter_Widget')) {
	class REYAJAXFILTERS_Taxonomy_Filter_Widget extends WP_Widget {
		/**
		 * Register widget with WordPress.
		 */
		function __construct() {
			parent::__construct(
				'reyajfilter-taxonomy-filter', // Base ID
				__('Rey Filter - by Taxonomy', 'rey-core'), // Name
				array('description' => __('Filter woocommerce products by taxonomy.', 'rey-core')) // Args
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

			// enqueue necessary scripts
			wp_enqueue_style('reyajfilter-style');
			wp_enqueue_script('simple-scrollbar');
			wp_enqueue_script('reyajfilter-script');

			$instance = wp_parse_args( (array) $instance, $this->defaults );

			// bail if set to exclude on certain category
			if( !empty($instance['show_only_on_categories']) && is_tax( 'product_cat', $instance['show_only_on_categories'] ) ){
				return;
			}

			if ( ! ($query_type = $instance['query_type']) ) {
				return;
			}

			$display_type = $instance['display_type'];
			$is_list = $display_type === 'list';

			$taxonomy   = (!empty($instance['taxonomy'])) ? wc_clean($instance['taxonomy']) : '';
			$taxonomy_name = '';
			$taxonomies = $this->get_taxonomies();

			foreach ($taxonomies as $value) {
				if( $value['id'] === $taxonomy ){
					$taxonomy_name = $value['name'];
					continue;
				}
			}

			if( empty($taxonomy) || empty($taxonomy_name) ){
				return;
			}

			$key_slug = str_replace('-', '', sanitize_title( $taxonomy_name ));
			$data_key   = ($query_type === 'and') ? "product-{$key_slug}a" : "product-{$key_slug}o";

			// parse url
			$url = $_SERVER['QUERY_STRING'];
			parse_str($url, $url_array);

			$attr_args = [
				'taxonomy'           => $taxonomy,
				'taxonomy_name'      => $taxonomy_name,
				'data_key'           => $data_key,
				'query_type'         => $query_type,
				'url_array'          => apply_filters('reycore/ajaxfilters/query_url', $url_array),
				'enable_multiple'    => (bool) $instance['enable_multiple'],
				'show_count'         => (bool) $instance['show_count'],
				'enable_hierarchy'   => (bool) $instance['hierarchical'],
				'show_children_only' => (bool) $instance['show_children_only'],
				'hide_empty'         => (bool) $instance['hide_empty'],
				'custom_height'      => (!empty($instance['custom_height']) && $is_list) ? $instance['custom_height']: '',
				'alphabetic_menu'    => ((bool) $instance['alphabetic_menu'] && $is_list),
				'search_box'         => ((bool) $instance['search_box']),
				'accordion_list'     => ((bool) $instance['accordion_list'] && $is_list && (bool) $instance['hierarchical'] ),
				'show_checkboxes'    => ((bool) $instance['show_checkboxes']),
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

		public function get_taxonomies(){

			if( isset($GLOBALS['reyAjaxFilters']) && ($aj = $GLOBALS['reyAjaxFilters']) ){
				return $aj->get_registered_taxonomies();
			}

			return [];
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
					<p>
						<label for="<?php echo $this->get_field_id('title'); ?>"><?php esc_html_e('Title:', 'rey-core'); ?></label>
						<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>">
					</p>
					<p>
						<label for="<?php echo $this->get_field_id('taxonomy'); ?>"><?php _e('Taxonomy') ?></label>
						<select class="widefat " id="<?php echo $this->get_field_id('taxonomy'); ?>" name="<?php echo $this->get_field_name('taxonomy'); ?>">
							<option><?php _e('- Select -', 'rey-core'); ?></option>
							<?php
							$taxonomies = $this->get_taxonomies();
							foreach ($taxonomies as $value) {
								printf('<option value="%2$s" %3$s>%1$s</option>',
									$value['name'],
									$value['id'],
									isset($instance['taxonomy']) ? selected($instance['taxonomy'], $value['id'], false) : ''
								);
							}
							?>
						</select>
					</p>
					<p>
						<label for="<?php echo $this->get_field_id('display_type'); ?>"><?php esc_html_e('Display Type', 'rey-core'); ?></label>
						<select class="widefat" id="<?php echo $this->get_field_id('display_type'); ?>" name="<?php echo $display_name; ?>">
							<option value="list" <?php selected( $instance['display_type'], 'list'); ?>><?php esc_html_e('List', 'rey-core'); ?></option>
							<option value="dropdown" <?php selected( $instance['display_type'], 'dropdown'); ?>><?php esc_html_e('Dropdown', 'rey-core'); ?></option>
						</select>
					</p>
					<p>
						<label for="<?php echo $this->get_field_id('query_type'); ?>"><?php esc_html_e('Query Type', 'rey-core'); ?></label>
						<select class="widefat" id="<?php echo $this->get_field_id('query_type'); ?>" name="<?php echo $this->get_field_name('query_type'); ?>">
							<option value="or" <?php selected( $instance['query_type'], 'or'); ?>><?php esc_html_e('OR', 'rey-core'); ?></option>
							<option value="and" <?php selected( $instance['query_type'], 'and'); ?>><?php esc_html_e('AND', 'rey-core'); ?></option>
						</select>
					</p>
					<p>
						<input id="<?php echo $this->get_field_id('enable_multiple'); ?>" name="<?php echo $this->get_field_name('enable_multiple'); ?>" type="checkbox" value="1" <?php checked($instance['enable_multiple']); ?>>
						<label for="<?php echo $this->get_field_id('enable_multiple'); ?>"><?php esc_html_e('Enable multiple filter', 'rey-core'); ?></label>
					</p>
					<p>
						<input id="<?php echo $this->get_field_id('show_count'); ?>" name="<?php echo $this->get_field_name('show_count'); ?>" type="checkbox" value="1" <?php checked($instance['show_count']); ?>>
						<label for="<?php echo $this->get_field_id('show_count'); ?>"><?php esc_html_e('Show count', 'rey-core'); ?></label>
					</p>
					<p>
						<input id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php echo $this->get_field_name('hierarchical'); ?>" type="checkbox" value="1" <?php checked($instance['hierarchical']); ?>>
						<label for="<?php echo $this->get_field_id('hierarchical'); ?>"><?php esc_html_e('Show hierarchy', 'rey-core'); ?></label>
					</p>
					<p>
						<input id="<?php echo $this->get_field_id('show_children_only'); ?>" name="<?php echo $this->get_field_name('show_children_only'); ?>" type="checkbox" value="1" <?php checked($instance['show_children_only']); ?>>
						<label for="<?php echo $this->get_field_id('show_children_only'); ?>"><?php esc_html_e('Only show children of the current term', 'rey-core'); ?></label>
					</p>

					<p>
						<input id="<?php echo $this->get_field_id('hide_empty'); ?>" name="<?php echo $this->get_field_name('hide_empty'); ?>" type="checkbox" value="1" <?php checked( $instance['hide_empty'] ); ?>>
						<label for="<?php echo $this->get_field_id('hide_empty'); ?>"><?php esc_html_e('Hide empty', 'rey-core'); ?></label>
					</p>

					<p id="<?php echo $this->get_field_id('show_checkboxes'); ?>-wrapper">
						<input id="<?php echo $this->get_field_id('show_checkboxes'); ?>" name="<?php echo $this->get_field_name('show_checkboxes'); ?>" type="checkbox" value="1" <?php checked( $instance['show_checkboxes'] ); ?>>
						<label for="<?php echo $this->get_field_id('show_checkboxes'); ?>"><?php esc_html_e('Show checkboxes', 'rey-core'); ?></label>
					</p>

					<p id="<?php echo $this->get_field_id('search_box'); ?>-wrapper">
						<input id="<?php echo $this->get_field_id('search_box'); ?>" name="<?php echo $this->get_field_name('search_box'); ?>" type="checkbox" value="1" <?php checked( $instance['search_box'] ); ?>>
						<label for="<?php echo $this->get_field_id('search_box'); ?>"><?php esc_html_e('Show search (filter) field', 'rey-core'); ?></label>
					</p>

					<?php
						$list_condition = wp_json_encode([
							[
								'name' => $display_name,
								'value' => 'list',
								'compare' => '==='
							]
						]);
					?>

					<p data-condition='<?php echo $list_condition; ?>'><strong><?php esc_html_e('LIST OPTIONS', 'rey-core') ?></strong></p>

					<p id="<?php echo $this->get_field_id('rey_multi_col'); ?>-wrapper" data-condition='<?php echo wp_json_encode([
							[
								'name' => $display_name,
								'value' => 'list',
								'compare' => '==='
							],
							[
								'name' => $this->get_field_name('hierarchical'),
								'value' => true,
								'compare' => '!='
							],
						]); ?>'>
						<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('rey_multi_col'); ?>" name="<?php echo $this->get_field_name('rey_multi_col'); ?>" <?php checked( $instance['rey_multi_col'] ); ?> value="1" />
						<label for="<?php echo $this->get_field_id('rey_multi_col'); ?>">
							<?php _e( 'Display list on 2 columns', 'rey-core' ); ?>
						</label>
					</p>

					<p id="<?php echo $this->get_field_id('accordion_list'); ?>-wrapper" data-condition='<?php echo wp_json_encode([
							[
								'name' => $display_name,
								'value' => 'list',
								'compare' => '==='
							],
							[
								'name' => $this->get_field_name('hierarchical'),
								'value' => true,
								'compare' => '=='
							],
							[
								'name' => $this->get_field_name('show_children_only'),
								'value' => true,
								'compare' => '!='
							],
						]); ?>'>
						<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('accordion_list'); ?>" name="<?php echo $this->get_field_name('accordion_list'); ?>" <?php checked( $instance['accordion_list'] ); ?> value="1" />
						<label for="<?php echo $this->get_field_id('accordion_list'); ?>">
							<?php _e( 'Display list as accordion', 'rey-core' ); ?>
						</label>
					</p>

					<p id="<?php echo $this->get_field_id('alphabetic_menu'); ?>-wrapper" data-condition='<?php echo $list_condition; ?>'>
						<input id="<?php echo $this->get_field_id('alphabetic_menu'); ?>" name="<?php echo $this->get_field_name('alphabetic_menu'); ?>" type="checkbox" value="1" <?php checked( $instance['alphabetic_menu'] ); ?>>
						<label for="<?php echo $this->get_field_id('alphabetic_menu'); ?>"><?php esc_html_e('Show alphabetic menu', 'rey-core'); ?></label>
					</p>

					<p id="<?php echo $this->get_field_id('custom_height'); ?>-wrapper" data-condition='<?php echo $list_condition; ?>'>
						<label for="<?php echo $this->get_field_id('custom_height'); ?>">
							<?php _e( 'Custom Height (px)', 'rey-core' ); ?>
						</label>
						<input class="tiny-text" type="number" step="1" min="50" max="1000" value="<?php esc_attr_e($instance['custom_height']) ?>" id="<?php echo $this->get_field_id('custom_height'); ?>" name="<?php echo $this->get_field_name('custom_height'); ?>" style="width: 100px" />
					</p>

					<?php
					$dd_condition = wp_json_encode([
						[
							'name' => $display_name,
							'value' => 'dropdown',
							'compare' => '==='
						]
					]); ?>

					<p data-condition='<?php echo $dd_condition; ?>'><strong><?php esc_html_e('DROPDOWN OPTIONS', 'rey-core') ?></strong></p>

					<p data-condition='<?php echo $dd_condition; ?>'>
						<label for="<?php echo $this->get_field_id('placeholder'); ?>"><?php esc_html_e('Placeholder:', 'rey-core'); ?></label>
						<input class="widefat" id="<?php echo $this->get_field_id('placeholder'); ?>" name="<?php echo $this->get_field_name( 'placeholder' ); ?>" type="text" value="<?php echo esc_attr($instance['placeholder']); ?>" placeholder="<?php esc_html_e('eg: Choose', 'rey-core') ?>">
					</p>

					<p data-condition='<?php echo $dd_condition; ?>'>
						<label for="<?php echo $this->get_field_id('dd_width'); ?>">
							<?php _e( 'Custom dropdown width', 'rey-core' ); ?>
						</label>
						<input class="tiny-text" type="number" step="1" min="50" max="1000" value="<?php esc_attr_e($instance['dd_width']) ?>" id="<?php echo $this->get_field_id('dd_width'); ?>" name="<?php echo $this->get_field_name('dd_width'); ?>" style="width: 100px" />
						<span><small><?php _e( 'px', 'rey-core' ); ?></small></span>
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
			$instance = [];
			$instance['title']              = sanitize_text_field($new_instance['title']);
			$instance['taxonomy']           = wc_clean($new_instance['taxonomy']);
			$instance['display_type']       = sanitize_text_field($new_instance['display_type']);
			$instance['placeholder']        = sanitize_text_field($new_instance['placeholder']);
			$instance['query_type']         = sanitize_text_field($new_instance['query_type']);
			$instance['enable_multiple']    = !empty($new_instance['enable_multiple']);
			$instance['show_count']         = !empty($new_instance['show_count']);
			$instance['hierarchical']       = !empty($new_instance['hierarchical']);
			$instance['show_children_only'] = !empty($new_instance['show_children_only']);
			$instance['rey_multi_col']      = !empty($new_instance['rey_multi_col']);
			$instance['alphabetic_menu']    = !empty($new_instance['alphabetic_menu']);
			$instance['accordion_list']     = !empty($new_instance['accordion_list']);
			$instance['show_checkboxes']    = !empty($new_instance['show_checkboxes']);
			$instance['custom_height']      = sanitize_text_field($new_instance['custom_height']);
			$instance['search_box']         = !empty($new_instance['search_box']);
			$instance['hide_empty']         = !empty($new_instance['hide_empty']);
			$instance['dd_width']           = sanitize_text_field($new_instance['dd_width']);
			$instance['show_only_on_categories'] = !empty($new_instance['show_only_on_categories']) && is_array($new_instance['show_only_on_categories']) ? array_map('sanitize_text_field', $new_instance['show_only_on_categories']) : [];
			return $instance;
		}
	}
}

// register widget
if (!function_exists('reyajaxfilter_register_taxonomy_filter_widget')) {
	function reyajaxfilter_register_taxonomy_filter_widget() {
		register_widget('REYAJAXFILTERS_Taxonomy_Filter_Widget');
	}
	add_action('widgets_init', 'reyajaxfilter_register_taxonomy_filter_widget');
}
