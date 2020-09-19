<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Widget_Menu__Product_Categories') ):

	class ReyCore_Widget_Menu__Product_Categories extends \Elementor\Skin_Base
	{

		public function get_id() {
			return 'product-categories';
		}

		public function get_title() {
			return __( 'Product Categories', 'rey-core' );
		}

		protected function _register_controls_actions() {
			parent::_register_controls_actions();

			if( ! class_exists('WooCommerce') ){
				return;
			}

			add_action( 'elementor/element/reycore-menu/section_settings/before_section_end', [ $this, 'register_prod_cat_controls' ] );
		}

		public function register_prod_cat_controls( $element ){

			$element->add_control(
				'pcat_type',
				[
					'label' => __( 'Selection', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'all',
					'options' => [
						'all'    => __( 'All Categories', 'rey-core' ),
						'parent' => __( 'Parent Categories', 'rey-core' ),
						'sub'    => __( 'Sub-Categories', 'rey-core' ),
						'manual' => __( 'Manually Pick Categories', 'rey-core' ),
						'manual_order' => __( 'Manually Pick Categories (Exact order)', 'rey-core' ),
					],
					'condition' => [
						'_skin' => 'product-categories',
					],
				]
			);

			$element->add_control(
				'pcat_categories',
				[
					'label' => esc_html__('Manually Select Categories', 'rey-core'),
					'type' => \Elementor\Controls_Manager::SELECT2,
					'label_block' => true,
					'multiple' => true,
					'default'     => [],
					'options' => reycore_wc__product_categories([
						'hide_empty' => false,
						'labels' => true,
						'orderby' => 'menu_order'
					]),
					'condition' => [
						'_skin' => 'product-categories',
						'pcat_type' => 'manual',
					],
				]
			);

			// Custom order
			$custom_order_cats = new \Elementor\Repeater();

				$custom_order_cats->add_control(
					'item',
					[
						'label' => esc_html__( 'Select attribute', 'rey-core' ),
						'placeholder' => esc_html__('- Select-', 'rey-core'),
						'type' => 'rey-query',
						'query_args' => [
							'type' => 'terms', // terms, posts
							'taxonomy' => 'product_cat',
							// 'field' => 'slug'
						],
						'label_block' => true,
						'default' => [],
					]
				);

			$element->add_control(
				'pcat_categories_order',
				[
					'label' => __( 'Manually Select Categories (in Order)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $custom_order_cats->get_controls(),
					'default' => [],
					'condition' => [
						'_skin' => 'product-categories',
						'pcat_type' => 'manual_order',
					],
				]
			);

			$element->add_control(
				'pcat_parent_category',
				[
					'label' => esc_html__('Categories', 'rey-core'),
					'type' => \Elementor\Controls_Manager::SELECT,
					'label_block' => true,
					// 'multiple' => true,
					'default'     => [],
					'options' => reycore_wc__product_categories([
						'hide_empty' => false,
						'orderby' => 'menu_order',
						'extra_item' => [
							'' => esc_html__('- Automatic -', 'rey-core')
						]
					]),
					'condition' => [
						'_skin' => 'product-categories',
						'pcat_type' => 'sub',
					],
				]
			);

			$element->add_control(
				'hide_empty',
				[
					'label' => esc_html__( 'Hide Empty Categories', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'_skin' => 'product-categories',
						'pcat_type!' => ['manual', 'manual_order'],
					],
				]
			);

			$element->add_control(
				'orderby',
				[
					'label' => esc_html__( 'Order By', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'name',
					'options' => [
						'name'  => esc_html__( 'Name', 'rey-core' ),
						'menu_order'  => esc_html__( 'Menu Order', 'rey-core' ),
						'term_id'  => esc_html__( 'Term ID', 'rey-core' ),
						'term_group'  => esc_html__( 'Term Group', 'rey-core' ),
						'parent'  => esc_html__( 'Parent', 'rey-core' ),
						'count'  => esc_html__( 'Count', 'rey-core' ),
					],
					'condition' => [
						'pcat_type!' => ['manual', 'manual_order'],
					],
				]
			);

			$element->add_control(
				'all_button',
				[
					'label' => esc_html__( 'Append "All" button', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'_skin' => 'product-categories',
						'pcat_type' => 'all',
					],
				]
			);

			$element->add_control(
				'all_button_text',
				[
					'label' => esc_html__( '"All" button text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => esc_html__( 'All', 'rey-core' ),
					'placeholder' => esc_html__( 'eg: All', 'rey-core' ),
					'condition' => [
						'_skin' => 'product-categories',
						'pcat_type' => 'all',
						'all_button!' => '',
					],
				]
			);

		}

		public function get_categories($settings)
		{
			$orderby = 'name';
			if( isset($settings['orderby']) ){
				$orderby = $settings['orderby'];
			}

			if( $settings['pcat_type'] === 'all' ){
				$cats = reycore_wc__product_categories([
					'hide_empty' => $settings['hide_empty'] === 'yes',
					'orderby' => $orderby
				]);
			}

			elseif( $settings['pcat_type'] === 'manual' ){

				$cats = reycore_wc__product_categories([
					'hide_empty' => $settings['hide_empty'] === 'yes'
				]);

				$selected_cats = $settings['pcat_categories'];
				$new_cats = [];
				foreach ($selected_cats as $selected_cat) {
					if( isset($cats[$selected_cat]) ){
						$new_cats[$selected_cat] = $cats[$selected_cat];
					}
				}

				$cats = $new_cats;
			}

			elseif( $settings['pcat_type'] === 'manual_order' ){

				$cats = [];

				if( isset($settings['pcat_categories_order']) && ($handpicked_ordered_terms = $settings['pcat_categories_order']) ){

					$handpicked_ordered_terms__clean           = array_filter( wp_list_pluck($handpicked_ordered_terms, 'item') );
					foreach ($handpicked_ordered_terms__clean as $term_id) {
						$cats[$term_id] = get_term( $term_id )->name;
					}
				}

			}

			elseif( $settings['pcat_type'] === 'parent' ){
				$cats = reycore_wc__product_categories( [
					'hide_empty' => $settings['hide_empty'] === 'yes',
					'parent' => 0,
					'orderby' => $orderby
				] );
			}

			elseif( $settings['pcat_type'] === 'sub' ){
				$cats = reycore_wc__product_categories( [
					'hide_empty' => $settings['hide_empty'] === 'yes',
					'parent' => $settings['pcat_parent_category'],
					'orderby' => $orderby
				] );
			}

			return $cats;
		}

		public function render_items( $cats, $settings )
		{

			printf('<ul class="reyEl-menu-nav rey-navEl --menuHover-%s">', $settings['hover_style']);

			if( $settings['all_button'] !== '' && $settings['pcat_type'] === 'all' ){
				printf(
					'<li class="menu-item %3$s"><a href="%2$s"><span>%1$s</span></a></li>',
					$settings['all_button_text'],
					get_permalink( wc_get_page_id('shop') ),
					is_shop() ? 'current-menu-item' : ''
				);
			}

			foreach ($cats as $i => $cat) {
				printf(
					'<li class="menu-item %3$s"><a href="%2$s"><span>%1$s</span></a></li>',
					$cat,
					get_term_link( $i, 'product_cat' ),
					is_tax( 'product_cat', $i ) ? 'current-menu-item' : ''
				);
			}
			echo '</ul>';
		}

		public function render_menu($settings)
		{
			$cats = $this->get_categories($settings);

			if( !empty($cats) ){
				echo '<div class="reyEl-menu-navWrapper">';

					ob_start();
					$this->render_items( $cats, $settings );
					$html = ob_get_clean();

					echo apply_filters('reycore/elementor/menu/product_categories_skin/render_menu', $html, $cats, $settings, $this->parent);

				echo '</div>';
			}
		}

		public function render() {

			if( ! class_exists('WooCommerce') ){
				return esc_html__('WooCommerce not installed', 'rey-core');
			}


			$settings = $this->parent->get_settings_for_display();

			$this->parent->render_start($settings);
			$this->parent->render_title($settings);
			$this->render_menu($settings);
			$this->parent->render_end();
		}
	}
endif;
