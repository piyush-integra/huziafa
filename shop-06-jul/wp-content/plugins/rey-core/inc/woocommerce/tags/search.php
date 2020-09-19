<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if(!class_exists('ReyCore_WooCommerce_Search')):
	/**
	 * Rey Search.
	 *
	 * @since   1.0.0
	 */

	class ReyCore_WooCommerce_Search {

		private static $_instance = null;

		const REST_SEARCH = '/product_search';

		private function __construct()
		{
			add_action('init', [$this, 'init']);
			add_action( 'rest_api_init', [ $this, 'register_route'] );
		}

		function init(){

			// return if search is disabled
			if( ! get_theme_mod('header_enable_search', true) ) {
				return;
			}

			add_filter( 'rey/main_script_params', [ $this, 'script_params'], 10 );
			add_action( 'rey/search_form', [ $this, 'search_form' ], 10);
			add_action( 'reycore/search_panel/after_search_form', [ $this, 'results_html' ], 10);
			add_action( 'wp_footer', [ $this, 'search_template' ], 10);
			add_filter( 'reycore/cover/get_cover', [$this, 'search_page_cover'], 40);
			add_filter( 'theme_mod_header_position', [$this, 'search_page_header_position'], 40);
			add_filter( 'rey_acf_option_header_position', [$this, 'search_page_header_position'], 40);
			add_filter( 'acf/load_value', [$this, 'search_page_reset_header_text_color'], 10, 3);
			add_filter( 'posts_search', [$this, 'search_include_sku'], 9, 2);
			add_filter('posts_where', [$this, '__search_where']);
			add_filter('posts_join', [$this, '__search_join']);
			add_filter('posts_groupby', [$this, '__search_groupby']);
		}

		/**
		 * Filter main script's params
		 *
		 * @since 1.0.0
		 **/
		public function script_params($params)
		{
			$params['rest_search_url'] = self::REST_SEARCH;
			$params['search_texts'] = [
				'NO_RESULTS' => esc_html__('Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'rey-core'),
			];
			$params['ajax_search_only_title'] = false;
			$params['ajax_search'] = get_theme_mod('header_enable_ajax_search', true);


			return $params;
		}

		/**
		 * Register custom endpoints
		 *
		 * @since 1.0.0
		 **/
		public function register_route()
		{

			register_rest_route( ReyCore_WooCommerce_Base::REY_ENDPOINT, self::REST_SEARCH, [
				'methods' => WP_REST_Server::READABLE,
				'callback' => [ $this , 'search_products' ],
				'args' => [
					's' => [
						'required' => true,
						'type' => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => 'rest_validate_request_arg',
					],
					'post_type' => [
						'required' => true,
						'type' => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => 'rest_validate_request_arg',
					],
				],
			] );
		}

		/**
		 * Query
		 *
		 * @since   1.0.0
		 */
		public function search_products_query( $args = [] )
		{
			if( function_exists('rey__maybe_disable_obj_cache') ){
				rey__maybe_disable_obj_cache();
			}

			$args = apply_filters('reycore/woocommerce/search/ajax_args', wp_parse_args( $args, [
				'post_type'           => 'product',
				'post_status'         => 'publish',
				's'                   => '',
				'paged'               => 0,
				'orderby'             => 'relevance',
				'order'               => 'asc',
				'posts_per_page'      => 5,
				'cache_results'       => false,
				'cache_timeout'       => 4,
			] ) );

			if ( 'product' === $args['post_type'] && 'yes' == get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
				$args['meta_query'][] = [
					'key'     => '_stock_status',
					'value'   => 'outofstock',
					'compare' => 'NOT LIKE',
				];
			}

			set_query_var('rey_search', true );
			set_query_var('search_terms', explode(' ', $args['s']) );

			if ( true === $args['cache_results'] ) {
				$dynamic_key    = md5( sanitize_text_field( $args['s'] ) );
				$transient_name = "rey-wc-search-results_{$dynamic_key}";
				$timeout        = absint( sanitize_text_field( $args['cache_timeout'] ) );
				if ( false === ( $the_query = get_transient( $transient_name ) ) ) {
					$the_query = new WP_Query( $args );
					set_transient( $transient_name, $the_query, $timeout * HOUR_IN_SECONDS );
				}
			} else {

				$the_query = new WP_Query( $args );
			}

			do_action('reycore/woocommerce/search/search_products_query', $the_query);

			return $the_query;
		}


		/**
		 * Gets the default search value linking to a search page with an "s" query string
		 * @since   1.0.0
		 */
		protected function get_default_search_value( \WP_Query $query, $result ) {
			$search_permalink = add_query_arg( array(
				's'         => sanitize_text_field( $query->query_vars['s'] ),
				'post_type' => 'product',
			), get_home_url() );

			$result['items'][] = array(
				'default'   => true,
				'id'        => get_the_ID(),
				'text'      => sprintf( esc_html__('View all results (%d)', 'rey-core'), $query->found_posts ),
				'permalink' => $search_permalink,
			);

			return $result;
		}

		/**
		 * Converts a wp_query result in a select 2 format result
		 * @since   1.0.0
		 */
		public function json_results( \WP_Query $query, $args = [] )
		{
			$result = array(
				'items' => array(),
				'total_count' => 0
			);

			if ( $query->have_posts() ) {

				while ( $query->have_posts() ) {

					set_query_var('loop_prevent_product_items_slideshow', true);

					$query->the_post();

					$result['items'][] = [
						'id'        => get_the_ID(),
						'text'      => get_the_title(),
						'permalink' => get_permalink( get_the_ID() ),
						'img'       => get_the_post_thumbnail(),
						'price'     => $this->get_product_price(),
					];

				}

				$result['total_count']    = $query->found_posts;
				$result['posts_per_page'] = $query->query_vars['posts_per_page'];

				wp_reset_postdata();

				if ( $result['total_count'] > $result['posts_per_page']) {
					$result = $this->get_default_search_value( $query, $result );
				}
			}

			return $result;
		}


		/**
		 * Searches products
		 *
		 * @since   1.0.0
		 */
		public function search_products( $wpr ) {

			if ( $wpr instanceof \WP_REST_Request ) {

				$search_string = $wpr->get_param('s');

				if( empty($search_string) ) {
					return;
				}

				$cache_results = true;

				if( defined('WP_DEBUG') && WP_DEBUG ){
					$cache_results = false;
				}

				$search_result = $this->search_products_query( array(
					's'             => $search_string,
					'cache_results' => $cache_results,
					'post_type'     => $wpr->get_param('post_type')
				) );

				$results = $this->json_results( $search_result );

				wp_send_json_success( $results );
			}
			return false;
		}

		public function get_product_price() {

			$product = wc_get_product();

			if( $product ) {
				return $product->get_price_html();
			}

			return '';
		}

		/**
		 * Make search form product type
		 * @since 1.0.0
		 **/
		public function search_form(){

			$post_types = get_theme_mod('search_supported_post_types_list', []);

			if( !empty($post_types) && count($post_types) > 1 ){

				$plist = reycore__get_post_types_list();
				$options = '';
				$first = '';

				foreach ($post_types as $key => $value) {
					$title = !empty($value['title']) ? $value['title'] : $plist[$value['post_type']];
					$options .= sprintf('<option value="%1$s">%2$s</option>', esc_attr($value['post_type']), $title );
					if( $key === 0 ){
						$first = $title;
					}
				}

				printf('<label class="rey-searchForm-postType"><span>%2$s</span><select name="post_type" >%1$s</select></label>', $options, $first);

				return;
			}

			echo '<input type="hidden" name="post_type" value="product" />';
		}

		/**
		 * Adds markup for Ajax Search's results
		 *
		 * @since 1.0.0
		 */
		function results_html()
		{
			$classes = [];

			if( class_exists('ReyCore_WooCommerce_Loop') && ReyCore_WooCommerce_Loop::getInstance()->is_custom_image_height() ) {
				$classes[] = '--customImageContainerHeight';
			} ?>
			<div class="rey-searchResults js-rey-searchResults <?php echo esc_attr( implode(' ', $classes) ) ?>"></div>
			<div class="rey-lineLoader"></div>
			<?php
		}

		/**
		 * Search template used for search drop panel.
		 * @since 1.0.0
		 */
		function search_template(){
			reycore__get_template_part('template-parts/woocommerce/search-panel-results');
		}

		/**
		 * Search to include SKU's
		 * Credits to "Search By Product SKU - for Woocommerce" plugin by Rohit Kumar
		 * @since 1.0.0
		 */
		function search_include_sku( $where, $wp_query ){

			if( ! apply_filters('reycore/search/enable_sku', true) ){
				return $where;
			}

			global $pagenow, $wpdb;

			$type = ['product', 'jam'];

			if (
				(is_admin() && 'edit.php' != $pagenow) ||
				! $wp_query->is_search ||
				! isset($wp_query->query_vars['s'])
			) {
				return $where;
			}

			if( isset($wp_query->query_vars['post_type']) && ($post_type = $wp_query->query_vars['post_type']) ){

				if( is_array($post_type) ){
					$is_product = in_array('product', $post_type, true);
				}
				else {
					$is_product = 'product' == $post_type;
				}

				if( ! $is_product ){
					return $where;
				}
			}

			$search_ids = [];
			$terms = explode(',', $wp_query->query_vars['s']);

			foreach ($terms as $term) {

				//Include search by id if admin area.
				if (is_admin() && is_numeric($term)) {
					$search_ids[] = $term;
				}

				// search variations with a matching sku and return the parent.
				$sku_to_parent_id = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT p.post_parent as post_id FROM {$wpdb->posts} as p join {$wpdb->postmeta} pm on p.ID = pm.post_id and pm.meta_key='_sku' and pm.meta_value LIKE '%%%s%%' where p.post_parent <> 0 group by p.post_parent",
						wc_clean($term)
					)
				);

				//Search a regular product that matches the sku.
				$sku_to_id = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_sku' AND meta_value LIKE '%%%s%%';",
						wc_clean($term)
					)
				);

				$search_ids = array_merge($search_ids, $sku_to_id, $sku_to_parent_id);

			}

			$search_ids = array_filter(array_map('absint', $search_ids));

			if (count($search_ids) > 0) {
				$where = str_replace(')))', ") OR ({$wpdb->posts}.ID IN (" . implode(',', $search_ids) . "))))", $where);
			}

			return $where;
		}

		function search_includes(){
			return apply_filters('reycore/woocommerce/search_taxonomies', get_theme_mod('search__include', []));
		}

		function __search_where($where){
			global $wpdb, $wp_query;

			if( empty($this->search_includes()) ){
				return $where;
			}

			if ( ! (is_search() || get_query_var('rey_search')) ) {
				return $where;
			}

			$search_terms = get_query_var( 'search_terms' );

			if( empty($search_terms) ){
				return $where;
			}

			$where .= " OR (";
			$i = 0;
			foreach ($search_terms as $search_term) {
				$i++;
				if ($i>1) $where .= " OR";
				// if ($i>1) $where .= " AND"; // OR if you prefer requiring all search terms to match taxonomies (not really recommended)
				$where .= " (t.name LIKE '%".$search_term."%')";
			}

			$where .= " )";

			return $where;
		}

		function __search_join($join){

			global $wpdb;

			if( empty($this->search_includes()) ){
				return $join;
			}

			if ( ! (is_search() || get_query_var('rey_search')) ) {
				return $join;
			}

			$includes = $this->search_includes();

			foreach ($includes as $key => $inc) {
				if( ! taxonomy_exists($inc) ){
					continue;
				}
				$on[] = sprintf("tt.taxonomy = '%s'", esc_sql($inc));
			}

			// build our final string
			$on = ' ( ' . implode( ' OR ', $on ) . ' ) ';
			$join .= " LEFT JOIN {$wpdb->term_relationships} AS tr ON ({$wpdb->posts}.ID = tr.object_id) LEFT JOIN {$wpdb->term_taxonomy} AS tt ON ( " . $on . " AND tr.term_taxonomy_id = tt.term_taxonomy_id) LEFT JOIN {$wpdb->terms} AS t ON (tt.term_id = t.term_id) ";

			return $join;
		}

		function __search_groupby($groupby){

			global $wpdb;

			if( empty($this->search_includes()) ){
				return $groupby;
			}

			// we need to group on post ID
			$groupby_id = "{$wpdb->posts}.ID";

			if( ! (is_search() || get_query_var('rey_search')) || strpos($groupby, $groupby_id) !== false) {
				return $groupby;
			}

			// groupby was empty, use ours
			if(!strlen(trim($groupby))) {
				return $groupby_id;
			}

			// wasn't empty, append ours
			return $groupby.", ".$groupby_id;
		}


		public function search_page_cover( $cover ){

			if( ! is_search() ){
				return $cover;
			}

			$search_cover = get_theme_mod('cover__search_page', 'no');

			if( $search_cover === 'no' ){
				return false;
			}

			return $search_cover;
		}

		public function search_page_header_position( $pos ){

			if( ! is_search() ){
				return $pos;
			}

			if( $search_header_pos = get_theme_mod('search__header_position', 'rel') ){
				return $search_header_pos;
			}

			return $pos;
		}

		public function search_page_reset_header_text_color( $value, $post_id, $field ){

			if( $field['name'] === 'header_text_color' && is_search() ){
				return '';
			}

			return $value;
		}

		/**
		 * Retrieve the reference to the instance of this class
		 * @return ReyCore_WooCommerce_Search
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

	}

	ReyCore_WooCommerce_Search::getInstance();
endif;
