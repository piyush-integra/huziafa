<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists('ReyCore_PageCover') ):
	/**
	 * Page cover global sections manager
	 *
	 * @since 1.0.0
	 */
	class ReyCore_PageCover
	{
		private static $_instance = null;

		/**
		 * Holds the cover ID
		 */
		private $cover = false;

		private function __construct()
		{
			add_action( 'wp', [$this, 'get_cover'], 0);
			add_action( 'rey/after_header_outside', [$this, 'cover_markup']);
			add_action( 'reycore/cover/content', [$this, 'add_cover_into_page']);
			add_filter( 'rey/add_titles', [$this, 'remove_page_title']);
			add_action( 'wp_enqueue_scripts', [$this, 'load_css'], 500 );
			add_action( 'wp', [$this, 'disable_woocommerce_page_titles']);
			add_filter( 'reycore/cover/get_cover', [$this, 'product_categories_custom_covers'], 10);
			add_filter( 'reycore/cover/get_cover', [$this, 'product_page_custom_covers'], 10);
			add_filter( 'reycore/cover/get_cover', [$this, 'inherit_category_cover'], 20);
			add_filter( 'reycore/cover/get_cover', [$this, 'cover_per_page'], 30);
			add_filter('reycore/cover/inherit_category_cover', [$this, 'update_inherit_category_cover']);

		}

		/**
		 * Gets the cover ID
		 *
		 * @since 1.0.0
		 */
		public function get_cover_id(){
			return $this->cover;
		}

		/**
		 * Check option after checking first the ACF options
		 *
		 * @since 1.0.0
		 */
		public function get_option( $opt = 'cover__pages' ){
			return get_theme_mod($opt, 'no');
		}

		/**
		 * Determine if cover is enabled
		 *
		 * @since 1.0.0
		 */
		public function get_cover(){

			if( $this->cover ) {
				return;
			}

			$cover = false;

			// Front page
			if( is_front_page() && ( $cover_frontpage = $this->get_option('cover__frontpage') ) && $cover_frontpage != 'no'  ) {
				$cover = $cover_frontpage;
			}

			// Blog page
			elseif( is_home() && !is_front_page() && 'post' == get_post_type() && ( $cover_blog_home = $this->get_option('cover__blog_home') ) && $cover_blog_home != 'no'  ) {
				$cover = $cover_blog_home;
			}

			// Blog categories
			elseif( is_category() && !is_home() && !is_front_page() && 'post' == get_post_type() && ( $cover_blog_category = $this->get_option('cover__blog_cat') ) && $cover_blog_category != 'no'  ) {
				$cover = $cover_blog_category;
			}

			// Blog post
			elseif( is_single() && 'post' == get_post_type() && ( $cover_blog_post = $this->get_option('cover__blog_post') ) && $cover_blog_post != 'no'  ) {
				$cover = $cover_blog_post;
			}

			// Product Page
			elseif( class_exists('WooCommerce') && is_product() && ( $cover_product_page = $this->get_option('cover__product_page') ) && $cover_product_page != 'no'  ) {
				$cover = $cover_product_page;
			}

			// Shop Page
			elseif( class_exists('WooCommerce') && (is_shop() || (is_product_taxonomy() && !is_product_category())) && !is_front_page() && ( $cover_shop_page = $this->get_option('cover__shop_page') ) && $cover_shop_page != 'no'  ) {
				$cover = $cover_shop_page;
			}

			// Shop Category Page
			elseif( class_exists('WooCommerce') && is_product_category() && ( $cover_shop_category = $this->get_option('cover__shop_cat') ) && $cover_shop_category != 'no'  ) {
				$cover = $cover_shop_category;
			}

			// Pages
			elseif( is_page() && !is_front_page() && !is_home() && ( $cover_pages = $this->get_option('cover__pages') ) && $cover_pages != 'no' ){
				$cover = $cover_pages;
			}

			$this->cover = apply_filters('reycore/cover/get_cover', $cover, $this);
		}

		/**
		 * Load Global Sections into the page
		 *
		 * @since 1.0.0
		 */
		public function add_cover_into_page(){
			if( $this->has_cover() && class_exists('ReyCore_GlobalSections' ) ) {
				echo ReyCore_GlobalSections::do_section( $this->cover );
			}
		}

		/**
		 * Disable page titles
		 *
		 * @since 1.0.0
		 */
		public function disable_woocommerce_page_titles()
		{
			if( $this->has_cover() && class_exists('WooCommerce') && ( is_product_category() || is_product_tag() || is_product_taxonomy() || is_shop() ) ) {
				// remove title
				add_filter( 'woocommerce_show_page_title', '__return_false', 10 );
				// remove breadcrumb too
				remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
				// remove description
				remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
				remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
			}
		}

		/**
		 * Adds the cover markup
		 *
		 * @since 1.0.0
		 */
		public function cover_markup(){

			if( ! $this->has_cover() ){
				return;
			}

			$classes[] = 'rey-pageCover--h-' . reycore__get_option('header_position', 'rel');

			?>
			<section class="rey-pageCover <?php echo esc_attr( implode(' ', apply_filters('reycore/cover/css_classes', $classes ))) ?>">
				<?php
				/**
				 * Prints content inside the cover
				 * @hook reycore/cover/content
				 */
				do_action('reycore/cover/content'); ?>
			</section>
			<!-- .rey-pageCover -->
			<?php
		}

		function has_cover(){
			return $this->cover && is_numeric($this->cover);
		}

		/**
		 * Load Sections custom CSS to prevent FOUC
		 *
		 * @since 1.0.0
		 */
		public function load_css() {

			if( $this->has_cover() ) {

				wp_enqueue_style( 'rey-core-elementor-frontend-css' );

				$css_file = '';

				if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
					$css_file = new \Elementor\Core\Files\CSS\Post( $this->cover );
				} elseif ( class_exists( '\Elementor\Post_CSS_File' ) ) {
					$css_file = new \Elementor\Post_CSS_File( $this->cover );
				}

				if( !empty($css_file) ){
					$css_file->enqueue();
				}
			}
		}

		/**
		 * Remove titles when cover is enabled
		 *
		 * @since 1.0.0
		 */
		public function remove_page_title( $status ){
			if( $this->has_cover() ) {
				$status = false;
			}
			return $status;
		}


		/**
		 * Override cover per product category
		 *
		 * @since 1.4.0
		 */
		public function product_categories_custom_covers( $cover ){

			$custom_cats = get_theme_mod('cover__shop_cat_custom', []);

			if( !class_exists('WooCommerce') || empty($custom_cats) ){
				return $cover;
			}

			// checks if it's a category
			if( ! is_product_category() ){
				return $cover;
			}

			foreach($custom_cats as $gs_cat){
				if( !empty($gs_cat['categories']) && is_product_category( $gs_cat['categories'] ) ){
					return absint($gs_cat['gs']);
				}
			}

			return $cover;
		}

		/**
		 * Override cover per product page
		 *
		 * @since 1.6.6
		 */
		public function product_page_custom_covers( $cover ){

			$conditions = get_theme_mod('cover__product_page_custom', []);

			if( !class_exists('WooCommerce') || empty($conditions) ){
				return $cover;
			}

			// checks if it's a product page
			if( ! is_product() ){
				return $cover;
			}

			foreach($conditions as $condition){

				if( empty($condition['gs']) ){
					continue;
				}

				$is_cat = !empty($condition['categories']) && has_term( $condition['categories'], 'product_cat', get_the_ID() );
				$is_tag = !empty($condition['tags']) && has_term( $condition['tags'], 'product_tag', get_the_ID() );

				if( $is_cat || $is_tag ){
					return absint($condition['gs']);
				}
			}

			return $cover;
		}

		/**
		 * Override cover at individual level
		 *
		 * @since 1.4.0
		 */
		public function cover_per_page( $cover ){

			if( ($acf_page_cover = reycore__acf_get_field('page_cover')) ){
				return $acf_page_cover;
			}

			return $cover;
		}

		function update_inherit_category_cover( $status ){
			return get_theme_mod('cover__shop_cat_inherit', false);
		}


		public function inherit_category_cover($cover){

			if( ! apply_filters('reycore/cover/inherit_category_cover', false) ){
				return $cover;
			}

			if( ! is_tax( 'product_cat' ) ){
				return $cover;
			}

			$current_cat = get_queried_object();
			$ancestors = get_ancestors($current_cat->term_id, 'product_cat');
			$is_parent = count( $ancestors ) === 0;

			if( $is_parent ){
				return $cover;
			}

			foreach ($ancestors as $parent_id) {
				if( $parent_cover = reycore__acf_get_field('page_cover', get_term_by('term_taxonomy_id', $parent_id) ) ){
					return absint($parent_cover);
				}
			}

			return $cover;
		}


		/**
		 * Retrieve the reference to the instance of this class
		 * @return ReyCore_PageCover
		 */
		public static function instance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
	}

	ReyCore_PageCover::instance();

endif;
