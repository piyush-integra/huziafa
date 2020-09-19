<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_Elementor_QueryControl') ):

class ReyCore_Elementor_QueryControl
{
	public function __construct(){
		add_action('elementor/init', [$this, 'init']);
	}

	public function init(){
		add_action( 'elementor/ajax/register_actions', [ $this, 'register_ajax_actions' ] );
	}

	/**
	 * Get saved value titles
	 */
	public function control_value_titles( $data ){

		if( !(isset($data['query_args']['type']) && $query_type = $data['query_args']['type']) ) {
			throw new \Exception( 'Missing query type.' );
		}

		return call_user_func( [ $this, 'get_value_titles_for_' . $query_type ], $data );
	}

	/**
	 * Get titles of the saved values
	 */
	public function get_value_titles_for_terms($data){

		$results = [];

		if( ! (isset( $data['values'] ) && $values = $data['values']) ){
			return $results;
		}

		$key = 'term_id';
		$query_args = [
			'term_taxonomy_id' => $values,
			'hide_empty' 	   => false,
		];

		if( isset($data['query_args']['field']) && $field = $data['query_args']['field'] ){
			// Use Slug if specified
			if( 'slug' === $field ){
				unset($query_args['term_taxonomy_id']);
				$query_args['slug'] = $values;
				$key = 'slug';
			}
		}

		$terms = get_terms($query_args);

		foreach ( $terms as $term ) {
			$taxonomy = get_taxonomy( $term->taxonomy );
			$results[ $term->$key ] = sprintf($term->name . ' (%s)', ucfirst($taxonomy->labels->singular_name));
		}

		return $results;
	}

	/**
	 * Get titles of the saved values
	 */
	public function get_value_titles_for_posts($data){

		$results = [];

		if( ! (isset( $data['values'] ) && $values = $data['values']) ){
			return $results;
		}

		foreach ($data['values'] as $id) {
			$results[ $id ] = get_the_title($id);
		}

		return $results;
	}

	/**
	 * Get search results
	 *
	 * @since 1.5.0
	 */
	public function filter_autocomplete( $data ){

		if ( empty( $data['query_args'] ) || empty( $data['q'] ) ) {
			throw new \Exception( 'Bad Request' );
		}

		if( !(isset($data['query_args']['type']) && $query_type = $data['query_args']['type']) ) {
			throw new \Exception( 'Missing query type.' );
		}

		$results = call_user_func( [ $this, 'get_autocomplete_for_' . $query_type ], $data );

		return [
			'results' => $results,
		];
	}

	/**
	 * Terms search results
	 */
	function get_autocomplete_for_terms( $data ){

		$results = [];

		$key = 'term_id';
		$query_args = [
			'search' 		=> $data['q'],
			'hide_empty' 	=> false,
		];

		if( isset($data['query_args']['taxonomy']) ){
			$query_args['taxonomy'] = $data['query_args']['taxonomy'];
		}

		if( isset($data['query_args']['field']) && $field = $data['query_args']['field'] ){
			// Use Slug if specified
			if( 'slug' === $field ){
				$key = 'slug';
			}
		}

		$terms = get_terms($query_args);

		foreach ( $terms as $term ) {
			$taxonomy = get_taxonomy( $term->taxonomy );
			$results[] = [
				'id' 	=> $term->$key,
				'text' 	=> sprintf($term->name . ' (%s)', ucfirst($taxonomy->labels->singular_name)),
			];
		}

		return $results;
	}

	/**
	 * Posts search results
	 */
	function get_autocomplete_for_posts( $data ){

		$results = [];

		$query_args = [
			's' 		       => $data['q'],
			'posts_per_page'   => 200,
			'orderby'          => 'date',
			'post_status'      => 'publish',
		];

		if( isset($data['query_args']['post_type']) ){
			$query_args['post_type'] = $data['query_args']['post_type'];
		}

		$posts = get_posts($query_args);

		foreach ( $posts as $post ) {
			$results[] = [
				'id' 	=> $post->ID,
				'text' 	=> $post->post_title,
			];
		}

		return $results;
	}

	/**
	 * Register Elementor Ajax Actions
	 *
	 * @since  2.0.0
	 * @return array
	 */
	public function register_ajax_actions( $ajax_manager ) {
		$ajax_manager->register_ajax_action( 'rey_query_control_value_titles', [ $this, 'control_value_titles' ] );
		$ajax_manager->register_ajax_action( 'rey_query_control_filter_autocomplete', [ $this, 'filter_autocomplete' ] );
	}

}
new ReyCore_Elementor_QueryControl;

endif;
