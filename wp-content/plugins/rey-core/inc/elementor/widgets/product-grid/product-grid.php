<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( class_exists('WooCommerce') && !class_exists('ReyCore_Widget_Product_Grid') ):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Product_Grid extends \Elementor\Widget_Base {

	private $query_args = [];

	// private $was = [];

	public function get_name() {
		return 'reycore-product-grid';
	}

	public function get_title() {
		return __( 'Products Grid', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-woocommerce';
	}

	public function get_categories() {
		return [ 'rey-theme' ];
	}

	public function get_keywords() {
		return [ 'woocommerce', 'products', 'carousel', 'grid', 'shop' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements/#products-grid';
	}

	protected function _register_skins() {
		$this->add_skin( new ReyCore_Widget_Product_Grid__Carousel( $this ) );
		$this->add_skin( new ReyCore_Widget_Product_Grid__Carousel_Section( $this ) );
		$this->add_skin( new ReyCore_Widget_Product_Grid__Mini( $this ) );
	}

	/**
	 * Register widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _register_controls() {

		$this->start_controls_section(
			'section_layout',
			[
				'label' => __( 'Layout settings', 'rey-core' ),
			]
		);

		$this->add_responsive_control(
			'per_row',
			[
				'label' => __( 'Products per row', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 6,
				'default' => reycore_wc_get_columns('desktop'),
				'condition' => [
					'_skin' => ['', 'mini'],
				],
				'selectors' => [
					'{{WRAPPER}} ul.products' => '--woocommerce-grid-columns: {{VALUE}}',
				],
				'render_type' => 'template',
			]
		);

		$this->add_control(
			'limit',
			[
				'label' => __( 'Limit products', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 4,
				// 'render_type' => 'template',
				'min' => 1,
				'max' => 200,
			]
		);

		$this->add_control(
			'paginate',
			[
				'label' => __( 'Pagination', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'_skin' => '',
				],
			]
		);

		$this->add_control(
			'show_header',
			[
				'label' => __( 'Show Header', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'paginate' => 'yes',
					'_skin' => '',
				],
			]
		);

		$this->add_control(
			'show_view_selector',
			[
				'label' => __( 'Show View Selector', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'description' => esc_html__('If enabled, the grid will use the stored user selected column number.', 'rey-core'),
				'condition' => [
					'paginate!' => '',
					'show_header!' => '',
					'_skin' => '',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Image_Size::get_type(),
			[
				'name' => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
				'default' => 'shop_catalog',
				'separator' => 'before',
				'exclude' => ['custom'],
				'condition' => [
					'_skin!' => 'carousel-section',
				],
				// TODO: add support for custom size thumbnails #40
			]
		);

		$this->end_controls_section();

		/**
		 * Query Settings
		 */

		$this->start_controls_section(
			'section_query',
			[
				'label' => __( 'Products Query', 'rey-core' ),
			]
		);

		$this->add_control(
            'query_type',
            [
                'label' => esc_html__('Query Type', 'rey-core'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'recent',
                'options' => [
                    'recent'           => esc_html__('Latest', 'rey-core'),
                    'featured'         => esc_html__('Featured', 'rey-core'),
                    'best-selling'     => esc_html__('Best Selling', 'rey-core'),
                    'sale'             => esc_html__('Sale', 'rey-core'),
                    'top'              => esc_html__('Top Rated', 'rey-core'),
                    'manual-selection' => esc_html__('Manual Selection', 'rey-core'),
					'recently-viewed'  => esc_html__('Recently viewed', 'rey-core'),
					'current_query'  => esc_html__('Current Query', 'rey-core'),
					'custom'  => esc_html__('Custom', 'rey-core'),
                ],
            ]
		);

		// Advanced settings

		$this->add_control(
			'advanced',
			[
				'label' => __( 'Advanced settings', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'condition' => [
					'query_type!' => ['recently-viewed', 'current_query'],
				],
			]
		);

		$this->add_control(
			'include',
			[
				'label'       => esc_html__( 'Product(s)', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'eg: 21, 22',
				'label_block' => true,
				'description' => __( 'Add product IDs separated by comma.', '' ),
				'condition' => [
					'query_type' => ['manual-selection', 'current_query'],
				],
			]
		);

        $this->add_control(
            'categories',
            [
                'label' => esc_html__('Product Category', 'rey-core'),
                'placeholder' => esc_html__('- Select category -', 'rey-core'),
                'type' => 'rey-query',
				'query_args' => [
					'type' => 'terms',
					'taxonomy' => 'product_cat',
					'field' => 'slug'
				],
                'label_block' => true,
                'multiple' => true,
				'default'     => [],
				'condition' => [
					'query_type!' => ['manual-selection', 'recently-viewed', 'current_query'],
				],
            ]
		);

		$this->add_control(
            'tags',
            [
                'label' => esc_html__('Product Tags', 'rey-core'),
                'placeholder' => esc_html__('- Select tag -', 'rey-core'),
                'type' => 'rey-query',
				'query_args' => [
					'type' => 'terms',
					'taxonomy' => 'product_tag',
					'field' => 'slug'
				],
                'label_block' => true,
                'multiple' => true,
				'default'     => [],
				'condition' => [
					'query_type!' => ['manual-selection', 'recently-viewed', 'current_query'],
				],
            ]
		);

		$attributes = wc_get_attribute_taxonomy_labels();

		$this->add_control(
			'attribute',
			[
				'label' => __( 'Product Attribute', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => ['' => esc_html__('- Select -', 'rey-core')] + $attributes,
				'condition' => [
					'query_type!' => ['manual-selection', 'recently-viewed', 'current_query'],
				],
			]
		);

		foreach($attributes as $term => $term_label):
			$this->add_control(
				'attribute__' . $term,
				[
					'label' => sprintf( esc_html__( 'Select one or more %s attributes', 'rey-core' ), $term_label ),
					'placeholder' => esc_html__('- Select -', 'rey-core'),
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'terms', // terms, posts
						'taxonomy' => wc_attribute_taxonomy_name( $term ),
						'field' => 'slug'
					],
					'multiple' => true,
					'default' => [],
					'label_block' => true,
					'condition' => [
						'attribute' => $term,
						'query_type!' => ['manual-selection', 'recently-viewed', 'current_query'],
					],
				]
			);
		endforeach;


		$this->add_control(
			'orderby',
			[
				'label' => __( 'Order by', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'date',
				'options' => [
					'date' => __( 'Date', 'rey-core' ),
					'title' => __( 'Title', 'rey-core' ),
					'price' => __( 'Price', 'rey-core' ),
					'popularity' => __( 'Popularity', 'rey-core' ),
					'rating' => __( 'Rating', 'rey-core' ),
					'rand' => __( 'Random', 'rey-core' ),
					'menu_order' => __( 'Menu Order', 'rey-core' ),
					// 'menu_order title' => __( 'Menu Order + Title', 'rey-core' ),
					// 'menu_order date' => __( 'Menu Order + Date', 'rey-core' ),
				],
				'condition' => [
					'query_type!' => ['manual-selection', 'recent', 'top', 'best-selling', 'recently-viewed', 'current_query'],
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label' => __( 'Order direction', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'desc',
				'options' => [
					'asc' => __( 'ASC', 'rey-core' ),
					'desc' => __( 'DESC', 'rey-core' ),
				],
				'condition' => [
					'query_type!' => ['manual-selection', 'recent', 'top', 'best-selling', 'recently-viewed', 'current_query'],
				],
			]
		);


		$this->add_control(
			'exclude',
			[
				'label'       => esc_html__( 'Exclude Product(s)', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'eg: 21, 22',
				'label_block' => true,
				'description' => __( 'Add product IDs separated by comma.', '' ),
				'condition' => [
					'query_type!' => ['manual-selection', 'recently-viewed'],
				],
			]
		);

		$this->add_control(
			'exclude_duplicates',
			[
				'label' => __( 'Exclude Duplicate Products', 'rey-core' ),
				'description' => __( 'Exclude duplicate products that were already loaded in this page', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'debug__show_query',
			[
				'label' => esc_html__( 'Debug: Show query', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_layout_components',
			[
				'label' => __( 'Components', 'rey-core' ),
			]
		);

		$yesno_opts = [
			''  => esc_html__( '- Select -', 'rey-core' ),
			'yes'  => esc_html__( 'Yes', 'rey-core' ),
			'no'  => esc_html__( 'No', 'rey-core' ),
		];

		$this->add_control(
			'hide_brands',
			[
				'label' => __( 'Hide Brand', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
			]
		);

		$this->add_control(
			'hide_category',
			[
				'label' => __( 'Hide Category', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
			]
		);

		$this->add_control(
			'hide_quickview',
			[
				'label' => __( 'Hide Quickview', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
			]
		);

		$this->add_control(
			'hide_wishlist',
			[
				'label' => __( 'Hide Wishlist Icon', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
			]
		);

		$this->add_control(
			'hide_prices',
			[
				'label' => __( 'Hide Price', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
			]
		);

		$this->add_control(
			'hide_discount',
			[
				'label' => __( 'Hide Discount Label', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
				'condition' => [
					'hide_prices!' => 'yes',
				],
			]
		);

		$this->add_control(
			'hide_ratings',
			[
				'label' => __( 'Hide Ratings', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
			]
		);

		$this->add_control(
			'hide_add_to_cart',
			[
				'label' => __( 'Hide Add To Cart', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
			]
		);

		$this->add_control(
			'hide_thumbnails',
			[
				'label' => __( 'Hide Thumbnails', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
				'condition' => [
					'_skin!' => 'carousel-section',
				],
			]
		);

		$this->add_control(
			'hide_new_badge',
			[
				'label' => __( 'Hide "New" Badge', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
			]
		);

		$this->add_control(
			'hide_variations',
			[
				'label' => __( 'Hide Product Variations', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
			]
		);

		$this->add_control(
			'entry_animation',
			[
				'label' => __( 'Animate on scroll', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'_skin!' => ['carousel', 'carousel-section'],
				],
			]
		);

		if( get_theme_mod('loop_extra_media', 'second') !== 'no' ):
			$this->add_control(
				'prevent_2nd_image',
				[
					'label' => __( 'Prevent extra images', 'rey-core' ),
					'description' => __( 'This option will disable showing extra images inside the product item. The option overrides the one located in Customizer > WooCommerce > Product catalog - Layout.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);
		endif;

		$this->end_controls_section();


		$this->start_controls_section(
			'section_styles_general',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'color',
			[
				'label' => __( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ul.products li.product' => '--body-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'link_color',
			[
				'label' => __( 'Links Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ul.products li.product' => '--link-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'link_hover_color',
			[
				'label' => __( 'Links Hover Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ul.products li.product' => '--link-color-hover: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'text_align',
			[
				'label' => __( 'Alignment', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'rey-core' ),
						'icon' => 'fa fa-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'rey-core' ),
						'icon' => 'fa fa-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'rey-core' ),
						'icon' => 'fa fa-align-right',
					],
				],
				'condition' => [
					'_skin!' => 'mini',
				],
			]
		);

		if( get_option('woocommerce_thumbnail_cropping', '1:1') === 'uncropped' ){

			$this->add_control(
				'uncropped_vertical_align',
				[
					'label' => esc_html__( 'Middle Vertical Align', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'_skin!' => 'mini',
					],
				]
			);

			$this->add_control(
				'custom_image_container_height',
				[
				   'label' => esc_html__( 'Custom Image Container Height', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'em' ],
					'range' => [
						'px' => [
							'min' => 100,
							'max' => 1000,
							'step' => 1,
						],
						'em' => [
							'min' => 3,
							'max' => 15.0,
						],
					],
					'selectors' => [
						'{{WRAPPER}}' => '--woocommerce-custom-image-height: {{SIZE}}{{UNIT}};',
					],
					'render_type' => 'template',
					'condition' => [
						'_skin!' => 'mini',
					],
				]
			);
		}

		$this->add_control(
			'grid_styles_settings_title', [
				'label' => __( 'Grid styles', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'grid_layout',
			[
				'label' => esc_html__( 'Grid Layout', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''           => esc_html__( 'Inherit', 'rey-core' ),
					'default'    => esc_html__( 'Default', 'rey-core' ),
					'masonry'    => esc_html__( 'Masonry', 'rey-core' ),
					'masonry2'   => esc_html__( 'Masonry V2', 'rey-core' ),
					'metro'      => esc_html__( 'Metro', 'rey-core' ),
					'scattered'  => esc_html__( 'Scattered', 'rey-core' ),
					'scattered2' => esc_html__( 'Scattered Mixed & Random', 'rey-core' ),
				],
				'condition' => [
					'_skin!' => 'mini',
				],
			]
		);

		$this->add_control(
			'gaps',
			[
				'label' => __( 'Grid Gaps', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					''         => __( 'Inherit', 'rey-core' ),
					'no'       => __( 'No gaps', 'rey-core' ),
					'line'     => __( 'Line', 'rey-core' ),
					'narrow'   => __( 'Narrow', 'rey-core' ),
					'default'  => __( 'Default', 'rey-core' ),
					'extended' => __( 'Extended', 'rey-core' ),
					'wide'     => __( 'Wide', 'rey-core' ),
					'wider'    => __( 'Wider', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'grid_mb',
			[
				'label' => __( 'Grid Vertical Margin', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 180,
						'step' => 1,
					],
					'em' => [
						'min' => 0,
						'max' => 5.0,
					],
					'rem' => [
						'min' => 0,
						'max' => 5.0,
					],
				],
				'selectors' => [
					'{{WRAPPER}} ul.products.columns-1 li.product:nth-child(1) ~ li.product' => 'margin-top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} ul.products.columns-2 li.product:nth-child(2) ~ li.product' => 'margin-top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} ul.products.columns-3 li.product:nth-child(3) ~ li.product' => 'margin-top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} ul.products.columns-4 li.product:nth-child(4) ~ li.product' => 'margin-top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} ul.products.columns-5 li.product:nth-child(5) ~ li.product' => 'margin-top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} ul.products.columns-6 li.product:nth-child(6) ~ li.product' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'_skin' => '',
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_layout_title',
			[
				'label' => __( 'Title', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title',
			[
				'label' => __( 'Show Title', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typo',
				'scheme' => \Elementor\Scheme_Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .woocommerce-loop-product__title a',
				'condition' => [
					'title' => ['yes'],
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => __( 'Title Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce-loop-product__title a' => 'color: {{VALUE}}',
				],
				'condition' => [
					'title' => ['yes'],
				],
			]
		);

		$this->add_control(
			'title_hover_color',
			[
				'label' => __( 'Title Hover Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce-loop-product__title a:hover' => 'color: {{VALUE}}',
				],
				'condition' => [
					'title' => ['yes'],
				],
			]
		);

		$this->end_controls_section();

		switch( $this->get_loop_skin() ):
			case"wrapped":
				$this->register_wrapped_skin_controls();
				break;
		endswitch;
	}

	/**
	 * Adds controls, specially for "wrapped" skin.
	 *
	 * @since 1.0.0
	 */
	public function register_wrapped_skin_controls(){

		$this->start_controls_section(
			'section_wrapper_styles',
			[
				'label' => __( 'Wrapper Skin Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'wrapped_inner_padding',
			[
				'label' => __( 'Inner Padding', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} li.product.rey-wc-skin--wrapped .rey-loopWrapper-details' => 'bottom: {{BOTTOM}}{{UNIT}}; left: {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} li.product.rey-wc-skin--wrapped .rey-new-badge' => 'top: {{TOP}}{{UNIT}}; left: {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs('wrapped_colors_tabs');

			$this->start_controls_tab(
				'wrapped_colors_active',
				[
					'label' => __( 'Active', 'rey-core' ),
			]);
			// Active
			$this->add_control(
				'wrapped_text_color',
				[
					'label' => __( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-wc-skin--wrapped, {{WRAPPER}} .rey-wc-skin--wrapped a {{WRAPPER}} .rey-wc-skin--wrapped a:hover, {{WRAPPER}} .rey-wc-skin--wrapped .button, {{WRAPPER}} .rey-wc-skin--wrapped .reyEl-productGrid-cs-dots' => 'color: {{VALUE}}',
					],
				]
			);
			$this->add_control(
				'wrapped_overlay_color',
				[
					'label' => __( 'Overlay Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product.rey-wc-skin--wrapped .woocommerce-loop-product__link:after' => 'background-color: {{VALUE}}',
					],
				]
			);
			$this->end_controls_tab();

			$this->start_controls_tab(
				'wrapped_colors_hover',
				[
					'label' => __( 'Hover', 'rey-core' ),
			]);
			// Hover
			$this->add_control(
				'wrapped_text_color_hover',
				[
					'label' => __( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-wc-skin--wrapped a:hover, {{WRAPPER}} .rey-wc-skin--wrapped .button' => 'color: {{VALUE}}',
					],
				]
			);
			$this->add_control(
				'wrapped_overlay_color_hover',
				[
					'label' => __( 'Overlay Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product.rey-wc-skin--wrapped:hover .woocommerce-loop-product__link:after' => 'background-color: {{VALUE}}',
					],
				]
			);
			$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Get query arguments based on settings.
	 *
	 * @since 1.0.0
	 */
	public function get_query_args( $settings = [] ){

		if( empty( $settings ) ){
			$settings = $this->get_settings_for_display();
		}

		if ( (is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy()) && $settings['query_type'] === 'current_query' ) {
			$query_args = $GLOBALS['wp_query']->query_vars;
			$query_args['posts_per_page'] = !empty($settings['limit'])  ? absint($settings['limit']) : $this->get_default_limit();
			$query_args['orderby'] = $settings['orderby'];
			$query_args['order'] = $settings['order'];
		}
		else {

			/**
			 * Create attributes for WC Shortcode Products
			 */
			$type = 'products';

			$atts = [
				// Number of columns.
				'columns'        => $settings['per_row'],
				// menu_order, title, date, rand, price, popularity, rating, or id.
				'orderby'        => $settings['orderby'],
				// ASC or DESC.
				'order'          => $settings['order'],
				// Should shortcode output be cached.
				'cache'          => false,
				// Paginate
				'paginate'       => $settings['paginate'],
			];

			$atts['limit'] = !empty($settings['limit'])  ? absint($settings['limit']) : $this->get_default_limit();

			// Manual selection settings
			if ( $settings['query_type'] == 'manual-selection' ) {
				if( is_array($settings['include']) ){
					$atts['ids'] = implode(',',$settings['include']);
				}
				else {
					$atts['ids'] = trim($settings['include']);
				}
			}
			else {

				// categories
				if( !empty($settings['categories']) ) {
					$atts['category'] = implode(',',$settings['categories']);
				}
				// tags
				if( !empty($settings['tags']) ) {
					$atts['tag'] = implode(',',$settings['tags']);
				}
				// attributes
				if( ($attribute = $settings['attribute']) && ($terms = $settings[ 'attribute__' . $attribute ]) ) {
					$atts['attribute'] = $attribute;
					$atts['terms'] = implode(',',$terms);
				}
			}

			// Recent
			if ( $settings['query_type'] == 'recent' ) {
				$atts['orderby'] = 'date';
				$atts['order'] = 'DESC';
				$type = 'recent_products';
			}
			elseif ( $settings['query_type'] == 'sale' ) {
				$type = 'sale_products';
			}
			elseif ( $settings['query_type'] == 'best-selling' ) {
				$type = 'best_selling_products';
			}
			elseif ( $settings['query_type'] == 'featured' ) {
				$atts['visibility'] = 'featured';
				$type = 'featured_products';
			}

			if( is_user_logged_in() && $this->get_settings_for_display('debug__show_query') === 'yes' ){
				echo '<pre><h4>Atts:</h4>';
				var_dump( $atts );
				echo '</pre>';
			}

			$shortcode = new WC_Shortcode_Products( $atts, $type );
			$query_args = $shortcode->get_query_args();
		}

		// Exclude duplicates
		$to_exclude = [];
		if( $settings['exclude_duplicates'] !== '' &&
			isset($GLOBALS["rey_exclude_products"]) &&
			!empty($GLOBALS["rey_exclude_products"]) ){
			$to_exclude = $GLOBALS["rey_exclude_products"];
		}

		/**
		 * If we have products on sale, and we want to exclude,
		 * override the query args.
		 */
		if( $settings['query_type'] == 'sale' && !empty($settings['exclude']) && isset($query_args['post__in']) && !empty($query_args['post__in']) ) {
			$excludes = array_map( 'trim', explode( ',', $settings['exclude'] ) );
			$excludes = array_merge($excludes, $to_exclude);
			$query_args['post__in'] = array_diff( $query_args['post__in'], array_map( 'absint', $excludes ) );
		}
		/**
		 * Get Top Rated
		 */
		elseif ( $settings['query_type'] == 'top' ) {
			$query_args['meta_key'] = '_wc_average_rating';
			$query_args['orderby'] = 'meta_value_num';
			$query_args['order'] = 'DESC';
		}
		/**
		 * Recently Viewed
		 */
		elseif ( $settings['query_type'] == 'recently-viewed' ) {
			$viewed_products = ! empty( $_COOKIE['woocommerce_recently_viewed'] ) ? (array) explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) : [];
			$query_args['post__in'] = array_reverse( array_filter( array_map( 'absint', $viewed_products ) ) );
			$query_args['orderby']  = 'post__in';
			// $query_args['order'] = 'DESC';
		}
		/**
		 * Add excludes for the rest
		 */
		else {

			if ( $settings['query_type'] != 'manual-selection' && (!empty($settings['exclude']) || !empty($to_exclude))) {
				$query_args['post__not_in'] = array_map( 'trim', explode( ',', $settings['exclude'] ) );
				$query_args['post__not_in'] = array_merge($query_args['post__not_in'], $to_exclude);
			}
		}

		if ( 'yes' == get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$query_args['meta_query'][] = array(
				'key'     => '_stock_status',
				'value'   => 'outofstock',
				'compare' => 'NOT LIKE',
			);
		}

		$this->query_args = $query_args;

		return $this->query_args;
	}

	/**
	 * Get the query results,
	 * based on $query_args.
	 *
	 * @since 1.0.0
	 */
	public function get_query_results()
	{
		$query_args = apply_filters( 'reycore/elementor/product_grid/query_args', $this->query_args, $this->get_id() );

		if( is_user_logged_in() && $this->get_settings_for_display('debug__show_query') === 'yes' ){
			echo '<pre><h4>Query args:</h4>';
			var_dump( $query_args );
			echo '</pre>';
		}

		/**
		 * Cancel default query and override all results
		 * @since 1.6.3
		 */
		if( $pre_results = apply_filters( 'reycore/elementor/product_grid/pre_results', [], $this->get_id() ) ){
			$results = (object) $pre_results;
		}
		else {

			$query = new WP_Query( array_merge( $query_args, [
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			] ) );

			$paginated = ! $query->get( 'no_found_rows' );

			$results = (object) apply_filters( 'reycore/elementor/product_grid/results', [
				'ids'          => wp_parse_id_list( $query->posts ),
				'total'        => $paginated ? (int) $query->found_posts : count( $query->posts ),
				'total_pages'  => $paginated ? (int) $query->max_num_pages : 1,
				'per_page'     => (int) $query->get( 'posts_per_page' ),
				'current_page' => $paginated ? (int) max( 1, $query->get( 'paged', 1 ) ) : 1,
			], $this->get_id(), $query );
		}

		$GLOBALS["rey_exclude_products"] = $results->ids;

		// Remove ordering query arguments which may have been added by get_catalog_ordering_args.
		WC()->query->remove_ordering_args();
		return $results;
	}

	/**
	 * Retrieves current loop skin
	 */
	public function get_loop_skin(){
		return get_theme_mod('loop_skin', 'basic');
	}

	public function get_default_limit(){
		return apply_filters( 'loop_shop_per_page', wc_get_default_products_per_row() * wc_get_default_product_rows_per_page() );
	}

	/**
	 * Sets individual loop prop based on settings
	 *
	 * @since 1.3.0
	 */
	function set_loop_prop( $flag, $component, $status ){

		switch ( $flag ) {
			case '':
				$prop_status = $status;
				break;
			case 'yes':
				$prop_status = false;
				break;
			case 'no':
				$prop_status = true;
				break;
			default:
		}

		wc_set_loop_prop( $component, $prop_status );
	}

	/**
	 * Sets loop * item components * props based on settings
	 *
	 * @since 1.3.0
	 */
	public function set_loop_props(){

		$all_components = reycore_wc_get_loop_components();
		$settings = $this->get_settings_for_display();

		foreach ($all_components as $component => $status) {

			if( ! isset( $settings['hide_' . $component] ) ){
				continue;
			}

			if( !is_array($status) ){ // regular
				$this->set_loop_prop($settings['hide_' . $component], $component, $status);
			}
			// has subcomponents
			else {
				foreach ($status as $subcomponent => $substatus) {
					$this->set_loop_prop($settings['hide_' . $component], $component . '_' . $subcomponent, $substatus);
				}
			}
		}

		/**
		 * Loop components
		 */

		$loop_components = [
			'columns'      => $settings['per_row'],
			'is_paginated' => $settings['paginate'] === 'yes',
			'result_count' => $settings['show_header'] === 'yes',
			'catalog_ordering' => $settings['show_header'] === 'yes',
			'view_selector' => $settings['show_view_selector'] === 'yes',
			'filter_button' => false,
			'mobile_filter_button' => (isset($settings['page_uses_filters']) && $settings['page_uses_filters'] === 'yes'),
			'filter_top_sidebar' => false,
			'product_thumbnails_slideshow' => get_theme_mod('loop_extra_media', 'second') === 'slideshow',
		];

		// Tweak
		if( in_array($settings['_skin'], ['carousel-section', 'carousel'] ) ){
			// disable thumbnails slideshow
			$loop_components['product_thumbnails_slideshow'] = false;
			// Remove entry animation class
			$loop_components['entry_animation'] = false;
		}

		// Tweak
		if( in_array($settings['_skin'], ['carousel-section'] ) ){
			// disable thumbnails
			$loop_components['thumbnails'] = false;
		}

		$loop_components = apply_filters('reycore/elementor/product_grid/loop_components', $loop_components, $settings );

		foreach ($loop_components as $component => $status) {
			// $this->was[$component] = wc_get_loop_prop($component);
			wc_set_loop_prop( $component, $status );
		}

	}

	public function loop_header_tweaks(){
		if( $this->get_settings_for_display('show_header') === 'yes' ){
			do_action('reycore/elementor/product_grid/show_header', \Elementor\Plugin::$instance->editor->is_edit_mode());
		}
	}

	public function resize_images( $size ){

		$settings = $this->get_settings_for_display();

		if( ($image_size = $settings['image_size']) && $image_size != 'custom' && $settings['hide_thumbnails'] != 'yes'){
			$size = $image_size;
		}
		// else if ( isset($settings['image_custom_dimension']) && $image_custom_dimension = $settings['image_custom_dimension'] ) {
		// 	// work in progress
		// }

		return $size;
	}

	public function prevent_extra_images( $settings, $flag = '' ){
		if( get_theme_mod('loop_extra_media', 'second') !== 'no' && isset($settings['prevent_2nd_image']) && $settings['prevent_2nd_image'] === 'yes' ){
			set_query_var('reycore_wc__prevent_2nd_image', $flag);
			set_query_var('loop_prevent_product_items_slideshow', $flag);
		}
	}

	/**
	 * Render Start
	 *
	 * @since 1.0.0
	 */
	public function render_start( $settings, $products )
	{

		if ( WC()->session ) {
			wc_print_notices();
		}

		// Include WooCommerce frontend stuff
		wc()->frontend_includes();

		// Disable extra images
		$this->prevent_extra_images( $settings, true);

		/**
		 * Resize thumbnails
		 * only if different size than shop_catalog
		 */
		add_filter( 'single_product_archive_thumbnail_size', [$this, 'resize_images'], 10 );

		// Prime caches to reduce future queries.
		if ( is_callable( '_prime_post_caches' ) ) {
			_prime_post_caches( $products->ids );
		}

		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_output_all_notices', 10 );

		add_action( 'woocommerce_before_shop_loop', [$this, 'set_loop_props'], 5); // after initial `set_loop_props`
		add_action( 'woocommerce_before_shop_loop', [$this, 'loop_header_tweaks'], 6);

		// Setup the loop.
		wc_setup_loop(
			[
				'is_shortcode' => true,
				'is_product_grid' => true,
				'is_search'    => false,
				'total'        => $products->total,
				'total_pages'  => $products->total_pages,
				'per_page'     => $products->per_page,
				'current_page' => $products->current_page,
			]
		);

		$wrapper_classes = [
			'woocommerce',
			'rey-element',
			'reyEl-productGrid',
			'reyEl-productGrid--' . ( $settings['hide_thumbnails'] == 'yes' ? 'no-thumbs' : 'has-thumbs' ),
			'reyEl-productGrid--skin-' . $settings['_skin'],
			$settings['show_header'] === 'yes' ? '--show-header' : ''
		];

		// Vertical align in middle for
		// uncropped images.
		if(
			get_option('woocommerce_thumbnail_cropping', '1:1') === 'uncropped' &&
			isset( $settings['uncropped_vertical_align'] ) && $settings['uncropped_vertical_align'] !== '' ){
				$wrapper_classes[] = '--vertical-middle-thumbs';
		}

		if( $settings['paginate'] === 'yes' ){

			add_filter( 'reycore/load_more_pagination_args', function($args){

				$pagenum =  empty( $_GET['product-page'] ) ? 2 : absint( $_GET['product-page'] ) + 1;

				if( wc_get_loop_prop( 'total_pages' ) >= $pagenum ) {

					$path = add_query_arg( 'product-page', $pagenum, false );

					if( is_multisite() ){
						$args['url'] = esc_url_raw ( network_site_url( $path ) );
					}
					else {
						$args['url'] = esc_url_raw ( site_url( $path ) );
					}
				}

				$args['post_type'] = 'product';
				return $args;
			});

		}

		$this->add_render_attribute( 'wrapper', 'class', $wrapper_classes );
		?>

		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>

		<?php
			do_action( 'woocommerce_before_shop_loop' );
	}

	/**
	 * End rendering the widget
	 * Reset components at the end
	 *
	 * @since 1.0.0
	 */
	public function render_end( $settings = []){

		do_action( 'woocommerce_after_shop_loop' );

		// Disable extra images, revert
		$this->prevent_extra_images( $settings, '');

		remove_action( 'woocommerce_before_shop_loop', [$this, 'set_loop_props'], 5); // after initial `set_loop_props`
		remove_action( 'woocommerce_before_shop_loop', [$this, 'loop_header_tweaks'], 6);

		remove_filter( 'single_product_archive_thumbnail_size', [$this, 'resize_images'], 10 );

		?>
		</div>
		<?php
	}

	public function product_css_classes($classes){

		$settings = $this->get_settings_for_display();

		// If both Add to cart & Quickview buttons are disabled
		// remove the is-animated class as it breaks the layout.
		if( array_key_exists('hover-animated', $classes) ){
			if( isset($settings['hide_add_to_cart']) && $settings['hide_add_to_cart'] === 'yes' &&
				isset($settings['hide_quickview']) && $settings['hide_quickview'] === 'yes' ) {
				unset( $classes['hover-animated'] );
			}
		}

		// if text align is selected
		// remove global class and replace with selected text align
		if( isset($settings['text_align']) && !empty($settings['text_align']) ){
			if( array_key_exists( 'rey-wc-loopAlign-' . get_theme_mod('loop_alignment', 'left') , $classes) ){
				unset( $classes['text-align'] );
			}
			$classes['text-align'] = 'rey-wc-loopAlign-' . $settings['text_align'];
		}

		// custom height for cropped image layout
		$unsupported_grids_custom_container_height = ['metro'];
		if( get_option('woocommerce_thumbnail_cropping', '1:1') === 'uncropped' &&
			! in_array( $this->get_grid_type(), $unsupported_grids_custom_container_height) &&
			isset($settings['custom_image_container_height']) && $settings['custom_image_container_height']['size'] !== '' ) {
			$classes[] = '--customImageContainerHeight';
		}

		return $classes;
	}

	/**
	 * Product Loop
	 *
	 * @since 1.0.0
	 */
	public function render_products( $products )
	{
		if( isset($GLOBALS['post']) ) {
			$original_post = $GLOBALS['post'];
		}

		if ( wc_get_loop_prop( 'total' ) ) {

			if( $this->get_settings_for_display('entry_animation') !== 'yes' ){
				wc_set_loop_prop( 'entry_animation', false );
			}

			add_filter( 'post_class', [$this, 'product_css_classes'], 20 );

			foreach ( $products->ids as $product_id ) {
				$GLOBALS['post'] = get_post( $product_id ); // WPCS: override ok.
				setup_postdata( $GLOBALS['post'] );
				// Render product template.
				wc_get_template_part( 'content', 'product' );
			}

			remove_filter( 'post_class', [$this, 'product_css_classes'], 20 );

		}

		if( isset($original_post) ) {
			$GLOBALS['post'] = $original_post; // WPCS: override ok.
		}
	}

	/**
	 * Get Grid type
	 */
	public function get_grid_type(){

		$grid = get_theme_mod('loop_grid_layout', 'default');

		$grid_layout = $this->get_settings_for_display('grid_layout');

		if( $grid_layout !== '' ){
			$grid = $grid_layout;
		}

		return esc_attr( $grid );
	}

	/**
	 * Grid CSS Classes
	 */
	public function get_css_classes(){

		$settings = $this->get_settings_for_display();

		// Grid Gap CSS class
		$gap_size = get_theme_mod('loop_gap_size', 'default');

		if( $settings['gaps'] !== '' ){
			$gap_size = $settings['gaps'];
		}

		$classes['grid_gap'] = 'rey-wcGap-' . esc_attr( $gap_size );

		// Product Grid CSS class
		$classes['grid_layout'] = 'rey-wcGrid-' . $this->get_grid_type();

		return $classes;
	}

	public function loop_start()
	{
		wc_set_loop_prop( 'loop', 0 );

		$settings = $this->get_settings_for_display();
		$classes = $this->get_css_classes();

		$classes['columns'] = 'columns-' . wc_get_loop_prop('columns');

		$cols_per_tablet = isset($settings['per_row_tablet'] ) && $settings['per_row_tablet'] ? $settings['per_row_tablet'] : reycore_wc_get_columns('tablet');
		$classes['columns-tablet'] = 'columns-tablet-' . absint( $cols_per_tablet );

		$cols_per_mobile = isset($settings['per_row_mobile'] ) && $settings['per_row_mobile'] ? $settings['per_row_mobile'] : reycore_wc_get_columns('mobile');
		$classes['columns-mobile'] = 'columns-mobile-' . absint( $cols_per_mobile );

		printf('<ul class="products %s">', implode(' ', $classes) );
	}

	public function loop_end(){
		echo '</ul>';
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		$this->get_query_args();

		$products = $this->get_query_results();

		if ( $products && $products->ids ) {

			$settings = $this->get_settings_for_display();

			$this->render_start( $settings, $products );

				$this->loop_start();

					$this->render_products( $products );

				$this->loop_end();

			$this->render_end( $settings );

			wp_reset_postdata();
			wc_reset_loop();
		}
		else {
			wc_get_template( 'loop/no-products-found.php' );
		}
	}

	/**
	 * Render widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _content_template() {}
}

endif;
