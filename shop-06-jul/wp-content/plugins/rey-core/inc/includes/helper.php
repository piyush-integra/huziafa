<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists('ReyCoreHelper') ):

	class ReyCoreHelper
	{
		private static $_instance = null;

		public $parent_categories = [];

		private function __construct()
		{


		}

		public function get_product_categories( $args = [] ){

			$args = wp_parse_args($args, [
				'hide_empty' => true,
				'parent' => false,
				'labels' => false,
				'orderby' => 'menu_order',
			]);

			$terms_args = [
				'taxonomy'   => 'product_cat',
				'hide_empty' => $args['hide_empty'],
				'orderby' => $args['orderby'] // 'name', 'term_id', 'term_group', 'parent', 'menu_order', 'count'
			];

			$hide_empty_key = $args['hide_empty'] ? 'non_empty' : 'with_empty';

			// if parent only
			if( $args['parent'] === 0 ){

				if( isset( $this->parent_categories[ $hide_empty_key ] ) ){
					return $this->parent_categories[ $hide_empty_key ];
				}

				$terms_args['parent'] = 0;
			}
			// if subcategories
			elseif( $args['parent'] !== 0 && $args['parent'] !== false ){

				// if automatic
				if( $args['parent'] === '' ) {
					$parent_term = get_queried_object();
				}
				// if pre-defined parent category
				else {
					$parent_term = get_term_by('slug', $args['parent'], 'product_cat');
				}

				if(is_object($parent_term) && isset($parent_term->term_id) ) {
					$terms_args['parent'] = $parent_term->term_id;
				}
			}

			$terms = get_terms( $terms_args );

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){

				if( $args['parent'] === 0 ){
					$this->parent_categories[ $hide_empty_key ] = $terms;
				}

				return $terms;
			}
		}

		public function get_product_categories_list( $args = [] ){

			$options = [];

			foreach ( $this->get_product_categories( $args ) as $term ) {
				$term_name = wp_specialchars_decode($term->name);
				$options[ $term->slug ] = $term_name;
			}

			return $options;
		}


		/**
		 * Retrieve the reference to the instance of this class
		 * @return ReyCoreHelper
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

	}

	function reyCoreHelper(){
		return ReyCoreHelper::getInstance();
	}

	reyCoreHelper();

endif;
