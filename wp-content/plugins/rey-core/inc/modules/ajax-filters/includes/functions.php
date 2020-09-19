<?php
/**
 * Necessary functions in Rey Ajax Product Filter plugin.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

if(!function_exists('reyajaxfilter_get_filtered_term_product_counts')):
	/**
	 * Count products within certain terms, taking the main WP query into consideration.
	 *
	 * This query allows counts to be generated based on the viewed products, not all products.
	 *
	 * @param  array  $term_ids Term IDs.
	 * @param  string $taxonomy Taxonomy.
	 * @param  string $query_type Query Type.
	 * @return array
	 */
	function reyajaxfilter_get_filtered_term_product_counts( $term_ids, $taxonomy, $query_type ) {

		global $wpdb;

		$tax_query  = apply_filters('reycore/ajaxfilters/tax_query', []);
		$meta_query  = apply_filters('reycore/ajaxfilters/meta_query', []);

		$is_product_type = is_main_query() && ( is_post_type_archive('product') || is_tax(get_object_taxonomies('product')) );

		if( $is_product_type ){
			$tax_query  = WC_Query::get_main_tax_query();
			$meta_query  = WC_Query::get_main_meta_query();
		}

		if ( 'or' === $query_type ) {
			foreach ( $tax_query as $key => $query ) {
				if ( is_array( $query ) && $taxonomy === $query['taxonomy'] ) {
					unset( $tax_query[ $key ] );
				}
			}
		}

		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$meta_query[] = array(
				'key'     => '_stock_status',
				'value'   => 'outofstock',
				'compare' => 'NOT LIKE',
			);
		}

		$meta_query     = new WP_Meta_Query( $meta_query );
		$tax_query      = new WP_Tax_Query( $tax_query );
		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );

		// Generate query.
		$query           = array();
		$query['select'] = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) as term_count, terms.term_id as term_count_id";
		$query['from']   = "FROM {$wpdb->posts}";
		$query['join']   = "
			INNER JOIN {$wpdb->term_relationships} AS term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id
			INNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy USING( term_taxonomy_id )
			INNER JOIN {$wpdb->terms} AS terms USING( term_id )
			" . $tax_query_sql['join'] . $meta_query_sql['join'];

		$query['where'] = "
			WHERE {$wpdb->posts}.post_type IN ( 'product' )
			AND {$wpdb->posts}.post_status = 'publish'"
			. $tax_query_sql['where'] . $meta_query_sql['where'] .
			'AND terms.term_id IN (' . implode( ',', array_map( 'absint', $term_ids ) ) . ')';


		$search  = $is_product_type ? WC_Query::get_main_search_query_sql() : apply_filters('reycore/ajaxfilters/search_query', []);

		if ( $search ) {
			$query['where'] .= ' AND ' . $search;
		}

		$query['group_by'] = 'GROUP BY terms.term_id';
		$query             = apply_filters( 'woocommerce_get_filtered_term_product_counts_query', $query );
		$query             = implode( ' ', $query );

		// We have a query - let's see if cached results of this query already exist.
		$query_hash = md5( $query );

		// Maybe store a transient of the count values.
		$cache = apply_filters( 'woocommerce_layered_nav_count_maybe_cache', true );
		if ( true === $cache ) {
			$cached_counts = (array) get_transient( 'reyajaxfilter_counts_' . sanitize_title( $taxonomy ) );
		} else {
			$cached_counts = array();
		}

		if ( ! isset( $cached_counts[ $query_hash ] ) ) {
			$results                      = $wpdb->get_results( $query, ARRAY_A ); // @codingStandardsIgnoreLine
			$counts                       = array_map( 'absint', wp_list_pluck( $results, 'term_count', 'term_count_id' ) );
			$cached_counts[ $query_hash ] = $counts;
			if ( true === $cache ) {
				set_transient( 'reyajaxfilter_counts_' . sanitize_title( $taxonomy ), $cached_counts, DAY_IN_SECONDS );
			}
		}

		return array_map( 'absint', (array) $cached_counts[ $query_hash ] );
	}
endif;

/**
 * Get child term ids for given term.
 *
 * @param  int $term_id
 * @param  string $taxonomy
 * @return array
 */
if (!function_exists('reyajaxfilter_get_term_childs')) {
	function reyajaxfilter_get_term_childs($term_id, $taxonomy, $hide_empty) {

		if( reyajaxfilter_transient_lifespan() === false ){
			$term_childs = get_terms( $taxonomy, [ 'child_of' => $term_id, 'fields' => 'ids', 'hide_empty' => $hide_empty ] );
			return (array)$term_childs;
		}

		$transient_name = 'reyajaxfilter_term_childs_' . md5(sanitize_key($taxonomy) . sanitize_key($term_id));

		if (false === ($term_childs = get_transient($transient_name))) {
			$term_childs = get_terms( $taxonomy, [ 'child_of' => $term_id, 'fields' => 'ids', 'hide_empty' => $hide_empty ] );
			set_transient($transient_name, $term_childs, reyajaxfilter_transient_lifespan());
		}

		return (array)$term_childs;
	}
}

/**
 * Get details for given term.
 *
 * @param  int $term_id
 * @param  string $taxonomy
 * @return mixed
 */
if (!function_exists('reyajaxfilter_get_term_data')) {
	function reyajaxfilter_get_term_data($term_id, $taxonomy) {

		if( reyajaxfilter_transient_lifespan() === false ){
			return get_term($term_id, $taxonomy);
		}

		$transient_name = 'reyajaxfilter_term_data_' . md5(sanitize_key($taxonomy) . sanitize_key($term_id));

		if (false === ($term_data = get_transient($transient_name))) {
			$term_data = get_term($term_id, $taxonomy);
			set_transient($transient_name, $term_data, reyajaxfilter_transient_lifespan());
		}

		return $term_data;
	}
}


/**
 * Function for clearing old transients stored.
 */
if (!function_exists('reyajaxfilter_clear_transients')) {
	function reyajaxfilter_clear_transients() {

		global $wpdb;
		$sql = "DELETE FROM $wpdb->options WHERE `option_name` LIKE ('%\_transient_reyajaxfilter\_%') OR `option_name` LIKE ('%\_transient_timeout_reyajaxfilter\_%')";
		return $wpdb->query($sql);
	}
}
// clear old transients
add_action('create_term', 'reyajaxfilter_clear_transients');
add_action('edit_term', 'reyajaxfilter_clear_transients');
add_action('delete_term', 'reyajaxfilter_clear_transients');
add_action('save_post', 'reyajaxfilter_clear_transients');
add_action('delete_post', 'reyajaxfilter_clear_transients');
add_action('rey/flush_cache_after_updates', 'reyajaxfilter_clear_transients');


if(!function_exists('reyajaxfilter_ajax_clear_transients')):
	/**
	 * Clear transients ajax
	 *
	 * @since 1.5.0
	 **/
	function reyajaxfilter_ajax_clear_transients()
	{
		if( !current_user_can('manage_options') ){
			wp_send_json_error();
		}

		if( reyajaxfilter_clear_transients() ) {
			wp_send_json_success();
		}
	}
	add_action('wp_ajax_ajaxfilter_clear_transient', 'reyajaxfilter_ajax_clear_transients');
endif;


if (!function_exists('reyajaxfilter_terms_output')):
	/**
	 * Lists terms based on taxonomy
	 *
	 * @since 1.6.2
	 */
	function reyajaxfilter_terms_output($args) {

		$reyAjaxFilters = reyAjaxFilters();
		$chosen_filters = $reyAjaxFilters->chosen_filters;

		$args = wp_parse_args($args, [
			'taxonomy'           => '',
			'data_key'           => '',
			'url_array'          => '',
			'query_type'         => '',
			'placeholder'        => '',
			'enable_multiple'    => false,
			'show_count'         => false,
			'enable_hierarchy'   => false,
			'show_children_only' => false,
			'hide_empty'         => true,
			'custom_height'      => '',
			'alphabetic_menu'    => false,
			'search_box'         => false,
			'dropdown'           => false,
			'show_tooltips'      => false,
			'accordion_list'     => false,
			'show_checkboxes'    => false,

			'terms'              => [],
			'has_filters'        => (isset($chosen_filters['chosen']) && !empty($chosen_filters['chosen'])),
			'selected_term_ids'  => isset($chosen_filters['chosen'][$args['taxonomy']]) ? $chosen_filters['chosen'][$args['taxonomy']]['terms'] : [],
		]);

		if( !empty($args['selected_term_ids']) ){
			$args['selected_term_ids'] = array_map('absint', $args['selected_term_ids']);
		}

		$parent_args = [
			'fields'       => 'ids',
			'taxonomy'     => $args['taxonomy'],
			'hide_empty'   => $args['hide_empty'],
			'order'        => 'ASC',
			'hierarchical' => $args['enable_hierarchy'],
		];

		if ($args['enable_hierarchy'] === true) {
			$parent_args['parent'] = 0;
		}

		if ($args['taxonomy'] === 'product_cat' && is_tax($args['taxonomy'])) {

			$current_cat   = get_queried_object();
			$cat_ancestors = get_ancestors( $current_cat->term_id, $args['taxonomy'] );

			if( ! apply_filters('reycore/ajaxfilters/terms/force_first_ancestor', false, $args, $current_cat) ) {
				$parent_args['parent'] = $current_cat->term_id;
			}
			else {
				if( ! empty($cat_ancestors) ){
					$parent_args['parent'] = end($cat_ancestors);
				}
			}
		}

		$parent_args = apply_filters('reycore/ajaxfilters/terms_args', $parent_args);

		if( isset($args['url_array']['on-sale']) && $args['url_array']['on-sale'] ){

			$get_sale_terms = wp_get_object_terms( reyajaxfilters_get_sale_products(), $args['taxonomy'], $parent_args );

			if( $args['taxonomy'] === 'product_cat' ){

				if( $args['enable_hierarchy'] ){
					$args['terms'] = $get_sale_terms;
				}
				// show only child terms
				else {
					$sale_terms = [];

					foreach ($get_sale_terms as $term_id) {
						if( $has_ancestors = get_ancestors( $term_id, $args['taxonomy'] ) ){
							$sale_terms[] = $term_id;
						}
					}

					$args['terms'] = $sale_terms;
				}

			}
			// others than categories
			else {
				$args['terms'] = $get_sale_terms;
			}

		}
		else {
			$args['terms'] = get_terms($parent_args);
		}

		if ( ! (is_array($args['terms']) && !empty($args['terms'])) ) {
			return [
				'html'  => '',
				'found' => false
			];
		}

		// safe measure in case there are multiple
		// selected terms ids, so it makes sense to force enable this
		if( sizeof($args['selected_term_ids']) > 1){
			$args['enable_multiple'] = true;
		}

		if( $args['dropdown'] ){
			return reyajaxfilter_dropdown_terms($args);
		}

		return reyajaxfilter_list_terms($args);
	}
endif;


if (!function_exists('reyajaxfilter_sub_terms_output')) {
	/**
	 * Render Sub-terms
	 *
	 * @param  array $sub_term_args
	 * @param  bool $found used for widgets to determine if they should hide entirely
	 * @return mixed
	 */
	function reyajaxfilter_sub_terms_output($args, $found) {

		$html = '';

		if( ! $args['dropdown'] ){
			$html = '<ul class="children">';
		}

		$term_counts = reyajaxfilter_get_filtered_term_product_counts( $args['sub_term_ids'], $args['taxonomy'], $args['query_type'] );

		foreach ($args['sub_term_ids'] as $sub_term_id) {

			$sub_term_data = reyajaxfilter_get_term_data($sub_term_id, $args['taxonomy']);

			if ($sub_term_data && ($sub_term_data->parent == $args['parent_term_id'])) {

				$_parent_term_id = $sub_term_data->term_id;
				$_parent_term_name = $sub_term_data->name;

				$count = isset( $term_counts[ $sub_term_id ] ) ? $term_counts[ $sub_term_id ] : 0;

				$force_show = in_array($_parent_term_id, $args['selected_term_ids']);
				$ancestors = get_ancestors( $_parent_term_id, $args['taxonomy'] );

				if (sizeof($ancestors) > 0 && in_array($_parent_term_id, $ancestors)) {
					$force_show = true;
				}

				if ($count > 0 || $force_show) {

					$found = true;
					$_sub_term_ids = [];

					if (($args['enable_hierarchy'] && !$args['show_children_only']) || ($args['show_children_only'] && $force_show)) {

						// get sub term ids for this term
						$_sub_term_ids = reyajaxfilter_get_term_childs($_parent_term_id, $args['taxonomy'], $args['hide_empty'] );

						if (!empty($_sub_term_ids)) {
							$sub_args = [
								'taxonomy'           => $args['taxonomy'],
								'data_key'           => $args['data_key'],
								'query_type'         => $args['query_type'],
								'enable_multiple'    => $args['enable_multiple'],
								'show_count'         => $args['show_count'],
								'enable_hierarchy'   => $args['enable_hierarchy'],
								'show_children_only' => $args['show_children_only'],
								'selected_term_ids'  => $args['selected_term_ids'],
								'hide_empty'         => $args['hide_empty'],
								'parent_term_id'     => $_parent_term_id,
								'sub_term_ids'       => $_sub_term_ids,
								'dropdown'           => $args['dropdown'],
							];
						}
					}

					// List
					if( ! $args['dropdown'] ){

						$li_attribute = $before_text = $after_text = '';
						$li_classes = $force_show ? 'chosen' : '';

						if( $args['alphabetic_menu'] && strlen($_parent_term_name) > 0 ){
							$li_attribute = sprintf('data-letter="%s"', mb_substr($_parent_term_name, 0, 1, 'UTF-8') );
						}

						if ($args['show_tooltips']) {
							$li_attribute .= sprintf('data-rey-tooltip="%s"', $_parent_term_name);
						}

						$html .= sprintf('<li class="%s" %s>', $li_classes, $li_attribute);

						// show accordion list icon
						if( !empty($_sub_term_ids) && $args['accordion_list'] ){
							$html .= '<button class="__toggle">'. reycore__get_svg_icon__core(['id'=>'reycore-icon-arrow']) .'</button>';
						}

						// show checkboxes
						if( $args['show_checkboxes'] ){
							$before_text .= '<span class="__checkbox"></span>';
						}

						// show counter
						$after_text .= ($args['show_count'] ? sprintf('<span class="__count">%s</span>', $count) : '');

						$html .= sprintf(
							'<a href="%1$s" data-key="%2$s" data-value="%3$s" data-multiple-filter="%4$s" data-slug="%8$s">%7$s %5$s %6$s</a>',
							get_term_link( $_parent_term_id, $args['taxonomy'] ),
							esc_attr($args['data_key']),
							esc_attr($_parent_term_id),
							esc_attr($args['enable_multiple']),
							$_parent_term_name,
							$after_text,
							$before_text,
							$sub_term_data->slug
						);

						if (!empty($_sub_term_ids)) {

							$sub_args['alphabetic_menu'] = $args['alphabetic_menu'];
							$sub_args['show_tooltips'] = $args['show_tooltips'];
							$sub_args['accordion_list'] = $args['accordion_list'];
							$sub_args['show_checkboxes'] = $args['show_checkboxes'];

							$results = reyajaxfilter_sub_terms_output($sub_args, $found);

							$html .= $results['html'];
							$found = $results['found'];
						}

						$html .= '</li>';
					}

					// dropdown
					else {

						$html .= sprintf(
							'<option value="%1$s" %2$s data-depth="%5$s" data-count="%4$s">%3$s</option>',
							$_parent_term_id,
							(in_array($_parent_term_id, $args['selected_term_ids'], true)) ? 'selected="selected"' : '',
							$_parent_term_name,
							$args['show_count'] ? $count : '',
							$args['depth']
						);

						if (!empty($_sub_term_ids)) {

							$sub_args['depth'] = $args['depth'] + 1;
							$results = reyajaxfilter_sub_terms_output($sub_args, $found);

							$html .= $results['html'];
							$found = $results['found'];
						}
					}
				}
			}
		}

		if( ! $args['dropdown'] ){
			$html .= '</ul>';
		}

		return array(
			'html'  => $html,
			'found' => $found
		);
	}
}


if (!function_exists('reyajaxfilter_list_terms')):
	/**
	 * Lists terms based on taxonomy
	 *
	 * @since 1.5.0
	 */
	function reyajaxfilter_list_terms($args) {


		$html = $list_html = '';
		$found = false;
		$term_counts = reyajaxfilter_get_filtered_term_product_counts( $args['terms'], $args['taxonomy'], $args['query_type'] );

		foreach ($args['terms'] as $parent_term_id) {

			$count = isset( $term_counts[ $parent_term_id ] ) ? $term_counts[ $parent_term_id ] : 0;

			// TODO: see if needed
			$ancestors = get_ancestors( $parent_term_id, $args['taxonomy'] );

			// if this term id is present in $args['selected_term_ids'] array we will force
			$force_show = in_array($parent_term_id, $args['selected_term_ids'], true);

			// if child term is selected we will force
			if (sizeof($ancestors) > 0 && in_array($parent_term_id, $ancestors)) {
				$force_show = true;
			}

			if ($count > 0 || $force_show) {

				// flag widgets to output HTML
				$found = true;
				$li_attribute = $before_text = $after_text = '';
				$li_classes = $force_show ? 'chosen' : '';
				$sub_term_ids = [];
				$parent_term = get_term_by( 'id', $parent_term_id, $args['taxonomy'] );

				// get child terms
				// get sub term ids for this term
				if (($args['enable_hierarchy'] && !$args['show_children_only']) || ($args['show_children_only'] && $force_show)) {
					$sub_term_ids = reyajaxfilter_get_term_childs($parent_term_id, $args['taxonomy'], $args['hide_empty']);
				}

				if( $args['alphabetic_menu'] && strlen($parent_term->name) > 0 ){
					$li_attribute = sprintf('data-letter="%s"', mb_substr($parent_term->name, 0, 1, 'UTF-8') );
				}

				if ($args['show_tooltips']) {
					$li_attribute .= sprintf('data-rey-tooltip="%s"', $parent_term->name);
				}

				$html .= sprintf('<li class="%s" %s>', $li_classes, $li_attribute);

				// show accordion list icon
				if( !empty($sub_term_ids) && $args['accordion_list'] ){
					$html .= '<button class="__toggle">'. reycore__get_svg_icon__core(['id'=>'reycore-icon-arrow']) .'</button>';
				}

				// show checkboxes
				if( $args['show_checkboxes'] ){
					$before_text .= '<span class="__checkbox"></span>';
				}

				// show counter
				$after_text .= ($args['show_count'] ? sprintf('<span class="__count">%s</span>', $count) : '');

				$link = get_term_link( $parent_term, $args['taxonomy'] );

				$term_html = sprintf(
					'<a href="%1$s" data-key="%2$s" data-value="%3$s" data-multiple-filter="%4$s" data-slug="%8$s">%7$s %5$s %6$s</a>',
					$link,
					esc_attr($args['data_key']),
					esc_attr($parent_term_id),
					esc_attr($args['enable_multiple']),
					$parent_term->name,
					$after_text,
					$before_text,
					$parent_term->slug
				);

				$html .= apply_filters( 'woocommerce_layered_nav_term_html', $term_html, $parent_term, $link, $count );

				if (!empty($sub_term_ids)) {
					$sub_term_args = [
						'taxonomy'           => $args['taxonomy'],
						'data_key'           => $args['data_key'],
						'query_type'         => $args['query_type'],
						'enable_multiple'    => $args['enable_multiple'],
						'show_count'         => $args['show_count'],
						'enable_hierarchy'   => $args['enable_hierarchy'],
						'show_children_only' => $args['show_children_only'],
						'parent_term_id'     => $parent_term_id,
						'sub_term_ids'       => $sub_term_ids,
						'selected_term_ids'  => $args['selected_term_ids'],
						'hide_empty'         => $args['hide_empty'],
						'alphabetic_menu'    => $args['alphabetic_menu'],
						'show_tooltips'      => $args['show_tooltips'],
						'accordion_list'     => $args['accordion_list'],
						'show_checkboxes'    => $args['show_checkboxes'],
						'dropdown'           => false,
					];

					$results = reyajaxfilter_sub_terms_output($sub_term_args, $found);

					$html .= $results['html'];
					$found = $results['found'];
				}

				$html .= '</li>';
			}
		}

		$list_classes = $list_wrapper_styles = $list_attributes = [];

		if( ! $args['accordion_list'] && $custom_height = absint($args['custom_height'] ) ){
			$list_wrapper_styles[] = sprintf('height:%spx', $custom_height);
			$list_attributes[] = sprintf('data-height="%s"', $custom_height);
		}

		if( $args['enable_hierarchy'] ){
			$list_classes[] = '--hierarchy';

			if( $args['accordion_list'] ){
				$list_classes[] = '--accordion';
			}
		}

		$list_classes[] = '--style-' . ($args['show_checkboxes'] ? 'checkboxes' : 'default');

		if( $args['alphabetic_menu'] ){
			$list_html .= sprintf('<div class="reyajfilter-alphabetic"><span class="reyajfilter-alphabetic-all %3$s" data-key="%2$s">%1$s</span></div>',
				esc_html__('All', 'rey-core'),
				esc_attr($args['data_key']),
				$args['has_filters'] ? '--reset-filter' : ''
			);
		}

		if( $args['search_box'] ){
			$list_html .= '<div class="reyajfilter-searchbox js-reyajfilter-searchbox">';
			$list_html .= reycore__get_svg_icon__core(['id'=>'reycore-icon-search']);
			$taxonomy_object = get_taxonomy( $args['taxonomy'] );
			$searchbox_label = sprintf(esc_html__('Search %s', 'rey-core'), $taxonomy_object->label);
			$list_html .= sprintf('<input type="text" placeholder="%s">', $searchbox_label);
			$list_html .= '</div>';
		}

		$list_attributes[] = sprintf('data-taxonomy="%s"', esc_attr($args['taxonomy']));

		$list_html .= sprintf('<div class="reyajfilter-layered-nav %s" %s>', implode(' ', $list_classes), implode(' ', $list_attributes));

			$list_html .= sprintf('<div class="reyajfilter-layered-navInner" style="%s">', implode(' ', $list_wrapper_styles));
			$list_html .= '<ul class="reyajfilter-layered-list">';
			$list_html .= $html;
			$list_html .= '</ul>';
			$list_html .= '</div>';

			if( ! $args['accordion_list'] && $custom_height ){
				$list_html .= '<span class="reyajfilter-customHeight-all">'. esc_html__('Show All +', 'rey-core') .'</span>';
			}

		$list_html .= '</div>';

		return [
			'html'  => $list_html,
			'found' => $found
		];
	}
endif;


/**
 * reyajaxfilter_dropdown_terms function
 *
 * @param  array $args
 * @return mixed
 */
if (!function_exists('reyajaxfilter_dropdown_terms')):
	function reyajaxfilter_dropdown_terms($args) {

		$html = '';
		$found = false;

		$placeholder = $args['placeholder'];

		if( empty($placeholder) ):
			if (preg_match('/^attr/', $args['data_key'])) {
				$attr = str_replace(['attra-', 'attro-'], '', $args['data_key']);
				$placeholder = sprintf(__('Choose %s', 'rey-core'), reyajaxfilter_get_attribute_name( $attr ));
			} elseif (preg_match('/^product-cat/', $args['data_key'])) {
				$placeholder = sprintf(__('Choose category', 'rey-core'));
			}
			elseif (preg_match('/^product-tag/', $args['data_key'])) {
				$placeholder = sprintf(__('Choose tag', 'rey-core'));
			}
			elseif ( isset($args['taxonomy_name']) && !empty($args['taxonomy_name']) ) {
				$placeholder = sprintf(__('Choose %s', 'rey-core'), $args['taxonomy_name']);
			}
		endif;

		if (!empty($args['terms'])) {

			// required scripts
			wp_enqueue_style('reyajfilter-select2');
			wp_enqueue_script('reyajfilter-select2');

			$html .= '<div class="reyajfilter-dropdown-nav">';

				$attributes = ($args['enable_multiple'] ? 'multiple="multiple"' : '');

				if( $args['search_box'] ):
					$attributes .= ' data-search="true"';
				endif;

				if( $args['show_checkboxes'] ):

					if( $args['enable_multiple'] ) {
						wp_enqueue_script('reyajfilter-select2-multi-checkboxes');
					}

					$attributes .= ' data-checkboxes="true"';
				endif;

				if( isset($args['dd_width']) && $dropdown_width = $args['dd_width'] ){
					$attributes .= sprintf(' data-ddcss=\'%s\'', wp_json_encode([
						'min-width' => $dropdown_width . 'px'
					]));
				}

				$html .= sprintf( '<select class="%1$s" name="%2$s" style="width: 100%;" %3$s data-placeholder="%4$s">',
					'reyajfilter-select2 ' . (($args['enable_multiple'] ? 'reyajfilter-select2-multiple' : 'reyajfilter-select2-single')),
					$args['data_key'],
					$attributes,
					$placeholder
				);

				if (!$args['enable_multiple']) {
					$html .= '<option value=""></option>';
				}

				$term_counts = reyajaxfilter_get_filtered_term_product_counts( $args['terms'], $args['taxonomy'], $args['query_type'] );

				foreach ($args['terms'] as $parent_term_id) {

					$count = isset( $term_counts[ $parent_term_id ] ) ? $term_counts[ $parent_term_id ] : 0;
					$ancestors = get_ancestors( $parent_term_id, $args['taxonomy'] );
					$force_show = in_array($parent_term_id, $args['selected_term_ids'], true);

					// if child term is selected we will force
					if (sizeof($ancestors) > 0 && in_array($parent_term_id, $ancestors)) {
						$force_show = true;
					}

					if ($count > 0 || $force_show === true) {

						$found = true;
						$parent_term = get_term_by( 'id', $parent_term_id, $args['taxonomy'] );

						$html .= sprintf( '<option value="%1$s" %2$s data-count="%4$s">%3$s</option>',
							$parent_term_id,
							(in_array($parent_term_id, $args['selected_term_ids'], true) ? 'selected="selected"' : ''),
							$parent_term->name,
							($args['show_count'] ? $count : '')
						);

						if (($args['enable_hierarchy'] && !$args['show_children_only']) || ($args['show_children_only'] && $force_show)) {

							// get sub term ids for this term
							$sub_term_ids = reyajaxfilter_get_term_childs($parent_term_id, $args['taxonomy'], $args['hide_empty']);

							if (sizeof($sub_term_ids) > 0) {
								$sub_term_args = [
									'taxonomy'           => $args['taxonomy'],
									'data_key'           => $args['data_key'],
									'query_type'         => $args['query_type'],
									'enable_multiple'    => $args['enable_multiple'],
									'show_count'         => $args['show_count'],
									'enable_hierarchy'   => $args['enable_hierarchy'],
									'show_children_only' => $args['show_children_only'],
									'parent_term_id'     => $parent_term_id,
									'sub_term_ids'       => $sub_term_ids,
									'selected_term_ids'  => $args['selected_term_ids'],
									'hide_empty'         => $args['hide_empty'],
									'dropdown'           => true,
									'depth'              => 1,
								];

								$results = reyajaxfilter_sub_terms_output($sub_term_args, $found);

								$html .= $results['html'];
								$found = $results['found'];
							}
						}
					}
				}

				$html .= '</select>';
			$html .= '</div>';
		}

		return [
			'html'  => $html,
			'found' => $found
		];
	}
endif;


if( !function_exists('reyajaxfilter_get_attribute_name') ):
	/**
	 * Pulls label from attributes
	 *
	 * @since 3.0.1
	 */
	function reyajaxfilter_get_attribute_name( $attr ){

		$attribute_taxonomies = wc_get_attribute_taxonomies();

		if( is_array($attribute_taxonomies) && !empty($attribute_taxonomies) ){

			$attribute = array_filter($attribute_taxonomies, function($v, $k) use ($attr){
				return $v->attribute_name === $attr;
			}, ARRAY_FILTER_USE_BOTH);

			$attribute = array_values($attribute);

			if( isset($attribute[0]) && isset($attribute[0]->attribute_label) ){
				return $attribute[0]->attribute_label;
			}
		}

		return $attr;
	}
endif;


/**
 * Transient lifespan
 *
 * @return int
 */
if (!function_exists('reyajaxfilter_transient_lifespan')) {
	function reyajaxfilter_transient_lifespan() {

		if( ! get_theme_mod('ajaxfilter_transients', true) ){
			return false;
		}

		return apply_filters('reycore/ajaxfilters/transient_lifespan', REYAJAXFILTERS_CACHE_TIME);
	}
}

if(!function_exists('reyajaxfilter_filter_color_attr_html')):
	/**
	 * Override Color Attribute html in filter nav
	 *
	 * @since 1.5.0
	 **/
	function reyajaxfilter_filter_color_attr_html($term_html, $term, $link, $count)
	{
		if( $taxonomy = $term->taxonomy ) {

			if( $taxonomy == 'product_cat' ) {
				return $term_html;
			}

			$colors = $color = '';

			if( class_exists('ReyCore_WooCommerce_Variations') ){

				// Customize color attributes
				$colors = ReyCore_WooCommerce_Variations::getInstance()->get_color_attributes();

				$term_id = $term->term_id;

				if( is_array($colors) && !empty($colors) && isset($colors[$term_id]) ) {

					$swatch_tag = ReyCore_WooCommerce_Variations::parse_attribute_color([
						'value' => $term->slug,
						'name' => $term->name
					], $colors[$term_id]['color'] );

					$term_html = str_replace( $term->name, $swatch_tag, $term_html );
				}
			}
		}

		return $term_html;
	}
endif;

if(!function_exists('reyajaxfilter_filter_image_attr_html')):
	/**
	 * Override Image Attribute html in filter nav
	 *
	 * @since 1.5.4
	 **/
	function reyajaxfilter_filter_image_attr_html($term_html, $term, $link, $count)
	{
		if( $taxonomy = $term->taxonomy ) {

			if( $taxonomy == 'product_cat' ) {
				return $term_html;
			}

			if( class_exists('ReyCore_WooCommerce_Variations') ){

				// get all image attributes
				$images = ReyCore_WooCommerce_Variations::getInstance()->get_image_attributes();

				if( is_array($images) && !empty($images) && isset($images[$term->term_id]) ) {

					$image_size = function_exists('woo_variation_swatches') ? woo_variation_swatches()->get_option( 'attribute_image_size' ) : 'thumbnail';
					$image = '';

					array_walk($images, function($key) use(&$image, $term, $image_size){
						if( $key['slug'] === $term->slug ){
							$image = wp_get_attachment_image( absint($key['image'] ), $image_size);
						}
					});

					$term_html = str_replace( $term->name, $image, $term_html );
				}
			}
		}

		return $term_html;
	}
endif;


if(!function_exists('reyajaxfilter_load_simple_template')):
	function reyajaxfilter_load_simple_template($template) {
		if( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtoupper( $_SERVER['HTTP_X_REQUESTED_WITH'] ) === 'XMLHTTPREQUEST' &&
			isset($_REQUEST['reynotemplate']) && absint( $_REQUEST['reynotemplate'] ) === 1 ){
			return REYAJAXFILTERS_DIR . '/includes/notemplate.php';
		}
		return $template;
	}
	add_filter( 'template_include', 'reyajaxfilter_load_simple_template', 20 );
endif;


if(!function_exists('reyajaxfilters_get_sale_products')):
	/**
	 * Get filtered sale products ids
	 *
	 * @since 1.6.3
	 **/
	function reyajaxfilters_get_sale_products() {
		$products = apply_filters('reycore/ajaxfilters/product_ids_on_sale', wc_get_product_ids_on_sale());
		return array_unique($products);
	}
endif;


if(!function_exists('reyajaxfilters__filter_sidebar_classes')):
	/**
	 * Filter "Top Filters Sidebar" classes
	 *
	 * @since 1.6.6
	 **/
	function reyajaxfilters__filter_sidebar_classes( $classes, $position )
	{
		if( $position === 'filters-top-sidebar' && get_theme_mod('ajaxfilter_topbar_sticky', false) ){
			$classes['topbar_sticky'] = '--sticky';
		}

		return $classes;
	}
	add_filter('rey/content/sidebar_class', 'reyajaxfilters__filter_sidebar_classes', 10, 2);
endif;


if(!function_exists('reyajaxfilters__filter_admin_titles')):
	/**
	 * add custom titles in widget title
	 *
	 * @since 1.6.6
	 **/
	function reyajaxfilters__filter_admin_titles( $categs )
	{
		$published_categories = [];
		foreach ( $categs as $key => $slug) {
			$term = get_term_by('slug', $slug, 'product_cat');
			$published_categories[] = $term->name;
		}
		if( !empty($published_categories) ): ?>
			<span class="rey-widgetToTitle --hidden" data-class="__published-on-categ">
				<span><?php esc_html_e('Only for: ', 'rey-core') ?></span>
				<span><?php echo implode(', ', $published_categories); ?></span>
			</span>
		<?php endif;
	}
endif;


if(!function_exists('reyajaxfilter_widget__option')):
/**
 * Generate widget option
 *
 * @since 1.6.7
 **/
function reyajaxfilter_widget__option( $widget, $instance = [], $args = [] )
{
	if( empty($args) ){
		return;
	}

	$args = wp_parse_args($args, [
		'name' => '',
		'type' => '',
		'label' => '',
		'value' => '',
		'conditions' => [],
		'wrapper_class' => '',
		'field_class' => 'widefat',
		'separator' => '',
		'placeholder' => '',
		'suffix' => '',
	]);

	if( !empty($args['type']) ):

		if( empty($args['name']) ){
			$args['name'] = 'widget-title-' . sanitize_title($args['label']);
		}

		if( !empty($args['conditions'])  ){
			$conditions = [];
			foreach ($args['conditions'] as $key => $value) {
				$condition = $value;
				$condition['name'] = $widget->get_field_name($value['name']);
				$conditions[] = $condition;
			}
			$args['conditions'] = $conditions;
		}

		$func = "reyajaxfilter_widget__option_{$args['type']}";

		// calc. separator
		if( !empty($args['separator']) ){
			$args['wrapper_class'] .= '--separator-' . $args['separator'];
		}

		if( function_exists($func) ){

			printf('<p id="%1$s-wrapper" class="rey-widget-field %3$s" %2$s>',
				$widget->get_field_id($args['name']),
				!empty($args['conditions']) ? sprintf("data-condition='%s'", wp_json_encode($args['conditions'])) : '',
				$args['wrapper_class']
			);

				$func($widget, $instance, $args);

				if( $suffix = $args['suffix'] ){
					printf('<span class="__suffix">%s</span>', $suffix);
				}

			echo '</p>';

		}
	endif;
}
endif;

if(!function_exists('reyajaxfilter_widget__option_checkbox')):
	/**
	 * Generate widget checkbox
	 *
	 * @since 1.6.7
	 **/
	function reyajaxfilter_widget__option_checkbox( $widget, $instance = [], $args = [] )
	{
		printf( '<input class="checkbox %5$s" type="checkbox" id="%1$s" name="%2$s" %3$s value="%4$s" >',
			$widget->get_field_id($args['name']),
			$widget->get_field_name($args['name']),
			isset($instance[$args['name']]) ? checked( $instance[$args['name']], true, false ) : '',
			$args['value'],
			$args['field_class']
		);
		printf(
			'<label for="%1$s">%2$s</label>',
			$widget->get_field_id($args['name']),
			$args['label']
		);
	}
endif;

if(!function_exists('reyajaxfilter_widget__option_text')):
	/**
	 * Generate widget text
	 *
	 * @since 1.6.7
	 **/
	function reyajaxfilter_widget__option_text( $widget, $instance = [], $args = [] )
	{
		printf(
			'<label for="%1$s">%2$s</label>',
			$widget->get_field_id($args['name']),
			$args['label']
		);

		$attributes = [];

		if( $placeholder = $args['placeholder'] ){
			$attributes[] = sprintf('placeholder="%s"', $placeholder);
		}

		$value = $args['value'];

		if( isset( $instance[$args['name']] ) ){
			$value = $instance[$args['name']];
		}

		printf( '<input class="%4$s" type="text" id="%1$s" name="%2$s" value="%3$s" %5$s>',
			$widget->get_field_id($args['name']),
			$widget->get_field_name($args['name']),
			esc_attr($value),
			$args['field_class'],
			implode(' ', $attributes)
		);
	}
endif;

if(!function_exists('reyajaxfilter_widget__option_number')):
	/**
	 * Generate widget number
	 *
	 * @since 1.6.7
	 **/
	function reyajaxfilter_widget__option_number( $widget, $instance = [], $args = [] )
	{
		printf(
			'<label for="%1$s">%2$s</label>',
			$widget->get_field_id($args['name']),
			$args['label']
		);

		$attributes = [];

		if( isset($args['options']) && !empty($args['options']) ){

			if( isset($args['step']) ){
				$attributes[] = sprintf('step="%s"', $args['step']);
			}

			if( isset($args['min']) ){
				$attributes[] = sprintf('min="%s"', $args['min']);
			}

			if( isset($args['max']) ){
				$attributes[] = sprintf('max="%s"', $args['max']);
			}
		}

		printf( '<input class="%4$s" type="number" id="%1$s" name="%2$s" value="%3$s" %5%s>',
			$widget->get_field_id($args['name']),
			$widget->get_field_name($args['name']),
			esc_attr($instance[$args['name']]),
			$args['field_class'],
			implode(' ', $attributes)
		);
	}
endif;

if(!function_exists('reyajaxfilter_widget__option_select')):
	/**
	 * Generate widget select list
	 *
	 * @since 1.6.7
	 **/
	function reyajaxfilter_widget__option_select( $widget, $instance = [], $args = [] )
	{
		printf(
			'<label for="%1$s">%2$s</label>',
			$widget->get_field_id($args['name']),
			$args['label']
		);

		$is_multiple = isset( $args['multiple'] ) && $args['multiple'];

		$options = '';

		if( isset($args['options']) && !empty($args['options']) ){
			foreach ($args['options'] as $key => $value) {

				if( $is_multiple ){
					$is_selected = in_array( $key, $instance[$args['name']], true ) ? 'selected' : '';
				}
				else {
					$is_selected = selected( $instance[$args['name']], $key, false);
				}

				$options .= sprintf('<option value="%1$s" %3$s>%2$s</option>', $key, $value, $is_selected );
			}
		}

		printf( '<select class="%4$s" id="%1$s" name="%2$s" %5$s>%3$s</select>',
			$widget->get_field_id($args['name']),
			$widget->get_field_name($args['name']) . ( $is_multiple ? '[]' : '' ),
			$options,
			$args['field_class'],
			$is_multiple ? 'multiple' : ''
		);
	}
endif;

if(!function_exists('reyajaxfilter_widget__option_title')):
	/**
	 * Generate widget title
	 *
	 * @since 1.6.7
	 **/
	function reyajaxfilter_widget__option_title( $widget, $instance = [], $args = [] )
	{
		printf('<span class="%s">%s</span>', $args['field_class'], $args['label'] );
	}
endif;
