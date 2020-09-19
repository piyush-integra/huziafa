<?php
/**
 * Rey Ajax Product Filter by Price
 */
if (!class_exists('REYAJAXFILTERS_Price_Filter_Widget')) {
	class REYAJAXFILTERS_Price_Filter_Widget extends WP_Widget {

		/**
		 * Register widget with WordPress.
		 */
		function __construct() {
			parent::__construct(
				'reyajfilter-price-filter', // Base ID
				__('Rey Filter - by Price', 'rey-core'), // Name
				array('description' => __('Filter woocommerce products by price.', 'rey-core')) // Args
			);

			$this->defaults = [
				'title'            => '',
				'display_type'     => 'slider',
				'custom_height'    => '',
				'show_currency'    => false,
				'show_as_dropdown' => false,
				'price_list'       => [],
				'price_list_start' => [
					'enable' => '',
					'text' => __('Under', 'rey-core'),
					'max' => '',
				],
				'price_list_end'   => [
					'enable' => '',
					'text' => __('Over', 'rey-core'),
					'min' => '',
				],
				'show_checkboxes'  => false,
				'placeholder'      => '',
				'dd_width'         => '',
				'show_only_on_categories' => [],
				'custom__separator'         => '',
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

			// display type, slider or list
			$display_type = $instance['display_type'];
			$is_slider = $display_type === 'slider';
			$html = '';

			$prices = reyAjaxFilters()::get_prices_range();
			$step = max( apply_filters( 'woocommerce_price_filter_widget_step', 10 ), 1 );

			// to be sure that these values are number
			$min_price = $max_price = 0;

			if (sizeof($prices) === 2) {
				$min_price = $prices['min_price'];
				$max_price = $prices['max_price'];
			}

			// Check to see if we should add taxes to the prices if store are excl tax but display incl.
			$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );

			if ( wc_tax_enabled() && ! wc_prices_include_tax() && 'incl' === $tax_display_mode ) {
				$tax_class = apply_filters( 'woocommerce_price_filter_widget_tax_class', '' ); // Uses standard tax class.
				$tax_rates = WC_Tax::get_rates( $tax_class );

				if ( $tax_rates ) {
					$min_price += WC_Tax::get_tax_total( WC_Tax::calc_exclusive_tax( $min_price, $tax_rates ) );
					$max_price += WC_Tax::get_tax_total( WC_Tax::calc_exclusive_tax( $max_price, $tax_rates ) );
				}
			}

			$min_price = apply_filters( 'woocommerce_price_filter_widget_min_amount', floor( $min_price / $step ) * $step );
			$max_price = apply_filters( 'woocommerce_price_filter_widget_max_amount', ceil( $max_price / $step ) * $step );

			// If both min and max are equal, we don't need a slider.
			if ( $min_price === $max_price ) {
				$is_slider = false;
			}

			// required scripts
			// enqueue necessary scripts
			wp_enqueue_style('reyajfilter-style');
			wp_enqueue_script('reyajfilter-script');

			// HTML markup for price slider
			// Slider markup
			if ($is_slider) {

				wp_enqueue_script('nouislider');
				wp_enqueue_style('reyajfilter-nouislider-style');

				$current_min_price = isset( $_GET['min-price'] ) ? floor( floatval( wp_unslash( $_GET['min-price'] ) ) / $step ) * $step : $min_price; // WPCS: input var ok, CSRF ok.
				$current_max_price = isset( $_GET['max-price'] ) ? ceil( floatval( wp_unslash( $_GET['max-price'] ) ) / $step ) * $step : $max_price; // WPCS: input var ok, CSRF ok.

				$html .= '<div class="reyajfilter-price-filter-wrapper">';
					$html .= '<div id="reyajfilter-noui-slider" class="noUi-extended" data-min="' . $min_price . '" data-max="' . $max_price . '" data-set-min="' . $current_min_price . '" data-set-max="' . $current_max_price . '"></div>';
					$html .= '<br />';
					$html .= '<div class="slider-values">';
						$html .= '<p>' . __('Min Price', 'rey-core') . ': <span class="reyajfilter-slider-value" id="reyajfilter-noui-slider-value-min"></span></p>';
						$html .= '<p>' . __('Max Price', 'rey-core') . ': <span class="reyajfilter-slider-value" id="reyajfilter-noui-slider-value-max"></span></p>';
					$html .= '</div>';
				$html .= '</div>';
			}

			// List markup
			elseif( $display_type === 'list' ) {

				$list_html = $dd_html = $prefix = $before = $after = '';

				// show checkboxes
				if( $instance['show_checkboxes'] ){
					$prefix = '<span class="__checkbox"></span>';
				}

				if ($instance['show_currency'])  {

					$currency_symbol = get_woocommerce_currency_symbol();
					$currency_position = get_option('woocommerce_currency_pos');

					if ($currency_position === 'left') {
						$before = $currency_symbol;
					} elseif ($currency_position === 'left_space') {
						$before = $currency_symbol . ' ';
					} elseif ($currency_position === 'right') {
						$after = $currency_symbol;
					} elseif ($currency_position === 'right_space') {
						$after = ' ' . $currency_symbol;
					}
				}

				// price start
				if( $instance['price_list_start']['enable'] == 1 && $instance['price_list_start']['max'] ){
					$price_start = [
						'min' => '',
						'max' => $instance['price_list_start']['max'],
						'text' => $instance['price_list_start']['text'],
					];
					array_unshift($instance['price_list'], $price_start);
				}

				// price end
				if( $instance['price_list_end']['enable'] == 1 && $instance['price_list_end']['min'] ){
					$instance['price_list'][] = [
						'max' => '',
						'min' => $instance['price_list_end']['min'],
						'text' => $instance['price_list_end']['text'],
					];
				}

				foreach ($instance['price_list'] as $price_list) {
					$is_selected = false;

					if (isset($_GET['min-price']) && $_GET['min-price'] == $price_list['min']) {
						$is_selected = true;
						$list_html .= '<li class="chosen">';
					} elseif (isset($_GET['max-price']) && $_GET['max-price'] == $price_list['max']) {
						$is_selected = true;
						$list_html .= '<li class="chosen">';
					} else {
						$list_html .= '<li>';
					}

					$list_html .= sprintf(
						'<a class="reyajfilter-price-filter-listItem" href="javascript:void(0)" data-key-min="min-price" data-value-min="%s" data-key-max="max-price" data-value-max="%s">',
						$price_list['min'],
						$price_list['max']
					);

					$list_html .= $prefix;

					$dd_html .= sprintf('<option value="%1$s" data-key-min="min-price" data-value-min="%2$s" data-key-max="max-price" data-value-max="%3$s" %4$s>',
						$price_list['min'] . $price_list['max'],
						$price_list['min'],
						$price_list['max'],
						selected(true, $is_selected, false)
					);

					if (isset($price_list['text']) && $price_list['text']) {
						$list_html .= '<span class="__text">' . $price_list['text'] . '</span>';
						$dd_html .= $price_list['text'];
					}

					if (isset($price_list['min']) && $price_list['min']) {
						$list_html .= '<span class="__min">' . $before . $price_list['min'] . $after . '</span>';
						$dd_html .= $before . $price_list['min'] . $after;
					}

					if (isset($price_list['to']) && $price_list['to']) {
						$list_html .= '<span class="__to">' . $price_list['to'] . '</span>';
						$dd_html .= ' ' . $price_list['to'] . ' ';
					}

					if (isset($price_list['max']) && $price_list['max']) {
						$list_html .= '<span class="__max">' . $before . $price_list['max'] . $after . '</span>';
						$dd_html .= $before . $price_list['max'] . $after;
					}

					$list_html .= '</a></li>';
					$dd_html .= '</option>';
				}

				if( $instance['show_as_dropdown'] ){

					// required scripts
					wp_enqueue_style('reyajfilter-select2');
					wp_enqueue_script('reyajfilter-select2');

					$placeholder = $instance['placeholder'] ? $instance['placeholder'] : esc_html__('Select Price', 'rey-core');

					$attributes = sprintf('data-placeholder="%s"', $placeholder);

					if( $instance['show_checkboxes'] ):
						wp_enqueue_script('reyajfilter-select2-multi-checkboxes');
						$attributes .= ' data-checkboxes="true"';
					endif;

					if( isset($instance['dd_width']) && $dropdown_width = $instance['dd_width'] ){
						$attributes .= sprintf(' data-ddcss=\'%s\'', wp_json_encode([
							'min-width' => $dropdown_width . 'px'
						]));
					}

					$html .= '<div class="reyajfilter-dropdown-nav">';
					$html .= '<select class="reyajfilter-select2 reyajfilter-select2-single reyajfilter-select2--prices" style="width: 100%;" '. $attributes .'>';
						$html .= '<option></option>';
						$html .= $dd_html;
					$html .= '</select>';
					$html .= '</div>';
				}
				else {

					$list_classes[] = '--style-' . ($instance['show_checkboxes'] ? 'checkboxes' : 'default');

					$html .= sprintf('<div class="reyajfilter-layered-nav %s">', implode(' ', $list_classes));

						$html .= '<ul>';
							$html .= $list_html;
						$html .= '</ul>';
					$html .= '</div>';
				}

			}

			elseif( $display_type === 'custom' ){

				global $wp;

				if ( '' === get_option( 'permalink_structure' ) ) {
					$form_action = remove_query_arg( array( 'page', 'paged', 'product-page' ), add_query_arg( $wp->query_string, '', home_url( $wp->request ) ) );
				} else {
					$form_action = preg_replace( '%\/page/[0-9]+%', '', home_url( trailingslashit( $wp->request ) ) );
				}

				$html .= '<form method="get" action="'. esc_url( $form_action ) .'" class="reyajfilter-price-filter--custom">';

					$current_min_price = isset( $_GET['min-price'] ) ? floor( floatval( wp_unslash( $_GET['min-price'] ) ) / $step ) * $step : $min_price; // WPCS: input var ok, CSRF ok.
					$current_max_price = isset( $_GET['max-price'] ) ? ceil( floatval( wp_unslash( $_GET['max-price'] ) ) / $step ) * $step : $max_price; // WPCS: input var ok, CSRF ok.

					if( ! (bool) $current_max_price && isset($prices['max_price']) ){
						$current_max_price = ceil( floatval( wp_unslash( $prices['max_price'] ) ) / $step ) * $step;
					}

					if ($instance['show_currency'])  {
						$currency_symbol = get_woocommerce_currency_symbol();
						$html .= sprintf('<span class="__currency">%s</span>', $currency_symbol);
					}

					$html .= sprintf(
						'<input type="number" id="%3$s" name="min-price" value="%1$s" class="__min" />',
						esc_attr( $current_min_price ),
						esc_attr( $min_price ),
						esc_attr($args['widget_id']) . '-min-price'
					);

					if ( $separator_text = $instance['custom__separator'] )  {
						$html .= sprintf('<span class="__separator">%s</span>', $separator_text);
					}

					$html .= sprintf(
						'<input type="number" id="%3$s" name="max-price" value="%1$s" class="__max" />',
						esc_attr( $current_max_price ),
						esc_attr( $max_price ),
						esc_attr($args['widget_id']) . '-max-price'
					);

					$html .= sprintf(
						'<button type="submit" class="button">%s</button>',
						reycore__arrowSvg()
					);

				$html .= '</form>';
			}

			extract($args);

			// Add class to before_widget from within a custom widget
			// http://wordpress.stackexchange.com/questions/18942/add-class-to-before-widget-from-within-a-custom-widget

			if ($display_type === 'slider') {
				$widget_class = 'woocommerce reyajfilter-price-filter-widget';
			} else {
				$widget_class = 'woocommerce reyajfilter-price-filter-widget reyajfilter-ajax-term-filter';
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
							'slider' => esc_html__('Slider', 'rey-core'),
							'list' => esc_html__('List', 'rey-core'),
							'custom' => esc_html__('Custom (From/To)', 'rey-core'),
						]
					]);


					$list_condition = wp_json_encode([
						[
							'name' => $display_name,
							'value' => 'list',
							'compare' => '==='
						]
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'show_currency',
						'type' => 'checkbox',
						'label' => __( 'Show currency', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => ['list', 'custom'],
								'compare' => 'in'
							],
						],
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'show_checkboxes',
						'type' => 'checkbox',
						'label' => __( 'Show checkboxes', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'list',
								'compare' => '==='
							],
						],
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'type' => 'title',
						'label' => __( 'PRICING POINTS', 'rey-core' ),
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'list',
								'compare' => '=='
							],
						],
						'field_class' => 'rey-widget-innerTitle'
					]);

					?>

					<div class="rey-widgetPrice-wrapper" data-condition='<?php echo $list_condition; ?>'>

						<?php
							$start_enabled = $instance['price_list_start']['enable'] == 1;
							$end_enabled = $instance['price_list_end']['enable'] == 1;
						?>

						<p class="rey-widgetPrice-list --start <?php echo ! $start_enabled ? '--hidden' : ''; ?>">
							<input type="hidden" name="<?php echo $this->get_field_name('price_list_start'); ?>[enable]" value="<?php echo $instance['price_list_start']['enable']; ?>" />
							<input type="text" class="widefat __text" name="<?php echo $this->get_field_name('price_list_start'); ?>[text]" value="<?php echo $instance['price_list_start']['text']; ?>" placeholder="<?php _e('eg: Under', 'rey-core'); ?>" />
							<input type="text" class="widefat __max" name="<?php echo $this->get_field_name('price_list_start'); ?>[max]" value="<?php echo $instance['price_list_start']['max']; ?>" placeholder="<?php _e('eg: 100', 'rey-core'); ?>" />
							<a href="#" class="rey-widgetPrice-remove">&times;</a>
						</p>

						<div id="<?php echo $this->get_field_id('price_list'); ?>-wrapper" class="rey-widgetPrice-listWrapper">
							<?php if (isset($instance['price_list']) && !empty($instance['price_list'])): ?>
								<?php foreach ($instance['price_list'] as $price_list): ?>
									<p class="rey-widgetPrice-list --default">
										<input type="text" class="widefat __min" name="<?php echo $this->get_field_name('price_list'); ?>[min][]" value="<?php echo $price_list['min']; ?>" placeholder="<?php _e('Min price', 'rey-core'); ?>" />
										<input type="text" class="widefat __to" name="<?php echo $this->get_field_name('price_list'); ?>[to][]" value="<?php echo $price_list['to']; ?>" placeholder="<?php _e('to', 'rey-core'); ?>" />
										<input type="text" class="widefat __max" name="<?php echo $this->get_field_name('price_list'); ?>[max][]" value="<?php echo $price_list['max']; ?>" placeholder="<?php _e('Max price', 'rey-core'); ?>" />
										<a href="javascript:void(0)" class="rey-widgetPrice-remove">&times;</a>
									</p>
								<?php endforeach ?>
							<?php else: ?>
								<p class="rey-widgetPrice-list --default">
									<input type="text" class="widefat __min" name="<?php echo $this->get_field_name('price_list'); ?>[min][]" value="" placeholder="<?php _e('Min price', 'rey-core'); ?>" />
									<input type="text" class="widefat __to" name="<?php echo $this->get_field_name('price_list'); ?>[to][]" value="" placeholder="<?php _e('to', 'rey-core'); ?>" />
									<input type="text" class="widefat __max" name="<?php echo $this->get_field_name('price_list'); ?>[max][]" value="" placeholder="<?php _e('Max price', 'rey-core'); ?>" />
									<a href="javascript:void(0)" class="rey-widgetPrice-remove">&times;</a>
								</p>
							<?php endif ?>
						</div>

						<p class="rey-widgetPrice-list --end <?php echo ! $end_enabled ? '--hidden' : ''; ?>">
							<input type="hidden" name="<?php echo $this->get_field_name('price_list_end'); ?>[enable]" value="<?php echo $instance['price_list_end']['enable']; ?>" />
							<input type="text" class="widefat __text" name="<?php echo $this->get_field_name('price_list_end'); ?>[text]" value="<?php echo $instance['price_list_end']['text']; ?>" placeholder="<?php _e('eg: Over', 'rey-core'); ?>" />
							<input type="text" class="widefat __min" name="<?php echo $this->get_field_name('price_list_end'); ?>[min]" value="<?php echo $instance['price_list_end']['min']; ?>" placeholder="<?php _e('eg: 1000', 'rey-core'); ?>" />
							<a href="#" class="rey-widgetPrice-remove">&times;</a>
						</p>

						<p class="rey-widgetPrice-addWrapper">
							<a href="javascript:void(0)" class="button rey-widgetPrice-add"><?php _e('Add', 'rey-core'); ?></a>
							&nbsp;&nbsp; <a href="javascript:void(0)" class="rey-widgetPrice-add-start <?php echo $start_enabled ? '--inactive' : ''; ?>"><?php _e('Add start', 'rey-core'); ?></a>
							&nbsp;&nbsp; <a href="javascript:void(0)" class="rey-widgetPrice-add-end <?php echo $end_enabled ? '--inactive' : ''; ?>"><?php _e('Add end', 'rey-core'); ?></a>
						</p>

						<div class="rey-widgetPrice-listEmpty hidden">
							<p class="rey-widgetPrice-list --default">
								<input type="text" class="widefat __min" name="<?php echo $this->get_field_name('price_list'); ?>[min][]" value="" placeholder="<?php _e('Min price', 'rey-core'); ?>" />
								<input type="text" class="widefat __to" name="<?php echo $this->get_field_name('price_list'); ?>[to][]" value="" placeholder="<?php _e('to', 'rey-core'); ?>" />
								<input type="text" class="widefat __max" name="<?php echo $this->get_field_name('price_list'); ?>[max][]" value="" placeholder="<?php _e('Max price', 'rey-core'); ?>" />
								<a href="javascript:void(0)" class="rey-widgetPrice-remove">&times;</a>
							</p>
						</div>
					</div>


					<?php

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'show_as_dropdown',
						'type' => 'checkbox',
						'label' => __( 'Show as dropdown list', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'list',
								'compare' => '=='
							],
						],
						'separator' => 'before'
					]);

					$dd_condition = [
						[
							'name' => 'display_type',
							'value' => 'list',
							'compare' => '==='
						],
						[
							'name' => 'show_as_dropdown',
							'value' => true,
							'compare' => '=='
						]
					];

					reyajaxfilter_widget__option( $this, $instance, [
						'type' => 'title',
						'label' => __( 'DROPDOWN OPTIONS', 'rey-core' ),
						'conditions' => $dd_condition,
						'field_class' => 'rey-widget-innerTitle'
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'placeholder',
						'type' => 'text',
						'label' => __( 'Placeholder', 'rey-core' ),
						'value' => '',
						'conditions' => $dd_condition,
						'placeholder' => esc_html__('eg: Choose', 'rey-core')
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'dd_width',
						'type' => 'number',
						'label' => __( 'Custom dropdown width', 'rey-core' ),
						'value' => '',
						'field_class' => 'small-text',
						'conditions' => $dd_condition,
						'options' => [
							'step' => 1,
							'min' => 50,
							'max' => 1000,
						],
						'suffix' => 'px'
					]);

					// Custom From To
					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'custom__separator',
						'type' => 'text',
						'label' => __( 'Separator', 'rey-core' ),
						'value' => __( 'To', 'rey-core' ),
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'custom',
								'compare' => '==='
							],
						],
						'placeholder' => esc_html__('eg: To', 'rey-core')
					]);

					?>
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

			$instance                     = [];
			$instance['title']            = sanitize_text_field($new_instance['title']);
			$instance['display_type']     = sanitize_text_field($new_instance['display_type']);
			$instance['show_as_dropdown'] = !empty($new_instance['show_as_dropdown']);
			$instance['show_currency']    = !empty($new_instance['show_currency']);
			$instance['show_checkboxes']  = !empty($new_instance['show_checkboxes']);
			$instance['placeholder']      = sanitize_text_field($new_instance['placeholder']);
			$instance['dd_width']         = sanitize_text_field($new_instance['dd_width']);
			$instance['custom__separator']= sanitize_text_field($new_instance['custom__separator']);
			$instance['show_only_on_categories'] = !empty($new_instance['show_only_on_categories']) && is_array($new_instance['show_only_on_categories']) ? array_map('sanitize_text_field', $new_instance['show_only_on_categories']) : [];

			// price list
			if (isset($new_instance['price_list'])) {

				$min = isset($new_instance['price_list']['min']) ? $new_instance['price_list']['min'] : [];
				$to = isset($new_instance['price_list']['to']) ? $new_instance['price_list']['to'] : [];
				$max = isset($new_instance['price_list']['max']) ? $new_instance['price_list']['max'] : [];
				$price_list = array();

				foreach ($min as $key => $mmin) {
					$mmin = $mmin;
					$mmax = $max[$key];
					$mto = !empty($to[$key]) ? $to[$key] : '-';

					if (!empty($mmin) || !empty($mmax)) {
						$price_list[] = array(
							'min' => $mmin,
							'to'  => $mto,
							'max' => $mmax,
						);
					}
				}

				$instance['price_list'] = $price_list;

			}

			if (isset($new_instance['price_list_start'])) {
				$instance['price_list_start'] = [
					'enable' => isset($new_instance['price_list_start']['enable']) ? absint($new_instance['price_list_start']['enable']) : '',
					'text' => isset($new_instance['price_list_start']['text']) ? sanitize_text_field($new_instance['price_list_start']['text']) : '',
					'max' => isset($new_instance['price_list_start']['max']) ? sanitize_text_field($new_instance['price_list_start']['max']) : '',
				];
			}

			if (isset($new_instance['price_list_end'])) {
				$instance['price_list_end'] = [
					'enable' => isset($new_instance['price_list_end']['enable']) ? absint($new_instance['price_list_end']['enable']) : '',
					'text' => isset($new_instance['price_list_end']['text']) ? sanitize_text_field($new_instance['price_list_end']['text']) : '',
					'min' => isset($new_instance['price_list_end']['min']) ? sanitize_text_field($new_instance['price_list_end']['min']) : '',
				];
			}

			return $instance;
		}
	}
}

// register widget
if (!function_exists('reyajaxfilter_register_price_filter_widget')) {
	function reyajaxfilter_register_price_filter_widget() {
		register_widget('REYAJAXFILTERS_Price_Filter_Widget');
	}
	add_action('widgets_init', 'reyajaxfilter_register_price_filter_widget');
}
