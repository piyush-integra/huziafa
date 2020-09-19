<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_Loop') ):
/**
 * Loop fucntionality
 *
 */
class ReyCore_WooCommerce_Loop
{
	private static $_instance = null;

	private $_skins = [];

	private function __construct()
	{

		add_filter('reycore/kirki_fields/field=loop_skin', [$this, 'load_skins']);
		add_action( 'init', [$this, 'init'] );

		$this->set_loop_skins();
		$this->load_includes();
	}

	function init(){

		if ( is_customize_preview() ) {
			add_action( 'customize_preview_init', [$this, 'load_hooks'] );
			return;
		}

		$this->load_hooks();
	}

	public function load_hooks(){

		add_filter( 'rey/content/sidebar_class', [$this, 'mobile_sidebar_add_filter_class']);
		add_filter( 'rey/content/site_main_class', [$this, 'mobile_sidebar_add_filter_class']);
		add_action( 'woocommerce_after_template_part', [$this,'after_no_products'], 1);
		add_action( 'rey/after_footer', [$this,'add_filter_sidebar_panel'], 10 );
		add_filter( 'woocommerce_product_loop_start', [$this, 'filter_product_list_start']);

		add_action( 'woocommerce_before_shop_loop', [$this, 'set_loop_props'], 0);
		add_action( 'woocommerce_before_shop_loop', [$this, 'components_add_remove']);

		add_action( 'woocommerce_before_shop_loop', [$this, 'header_wrapper_start'], 19 );
		add_action( 'woocommerce_before_shop_loop', [$this, 'header_wrapper_end'], 31 );

		// Sale shortcode
		add_action( 'woocommerce_shortcode_before_sale_products_loop', [$this, 'set_loop_props'], 0);
		add_action( 'woocommerce_shortcode_before_sale_products_loop', [$this, 'components_add_remove']);
		add_action( 'woocommerce_shortcode_before_sale_products_loop', [$this, 'header_wrapper_start'], 19 );
		add_action( 'woocommerce_shortcode_before_sale_products_loop', [$this, 'header_wrapper_end'], 31 );

		add_action( 'woocommerce_before_shop_loop_item', [$this, 'start_wrapper_div'], 0);
		add_action( 'woocommerce_after_shop_loop_item', 'reycore_wc__generic_wrapper_end', 999);
		add_action( 'woocommerce_before_subcategory', [$this, 'start_wrapper_cat_div'], 0);
		add_action( 'woocommerce_after_subcategory', 'reycore_wc__generic_wrapper_end', 999);

		// remove default hooks
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
		remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
		remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );

		add_action( 'woocommerce_before_shop_loop_item_title', [$this, 'thumbnail_wrapper_start'], 5);
		$this->add_wrapping_links();
		add_action( 'woocommerce_before_shop_loop_item_title', 'reycore_wc__generic_wrapper_end', 12); // thumbnail wrapper end

		// fix for related & upsells
		add_action( 'woocommerce_after_single_product_summary', [$this, 'set_loop_props'], 0);
		add_action( 'woocommerce_after_single_product_summary', [$this, 'components_add_remove'], 5);
		add_action( 'woocommerce_cart_collaterals', [$this, 'set_loop_props'], 0);
		add_action( 'woocommerce_cart_collaterals', [$this, 'components_add_remove'], 5);

		// TODO:
		// add_filter( 'woocommerce_short_description', [$this, 'archive_description_more_tag']);

		add_filter('woocommerce_loop_add_to_cart_args', [$this, 'add_to_cart_args'], 10, 2);
	}

	public function set_loop_props(){

		// all components
		$components = reycore_wc_get_loop_components();

		// available components
		$available_components = $this->get_default_component_hooks();

		foreach ($components as $component => $flag) {
			if( is_array($flag) ){
				foreach ($flag as $subcomponent => $subflag) {
					wc_set_loop_prop($component . '_' . $subcomponent, false );
					if( array_key_exists( $subcomponent, $available_components[$component] ) ){
						wc_set_loop_prop( $component . '_' . $subcomponent, $subflag );
					}
				}
			}
			else {
				wc_set_loop_prop($component, false );
				if( array_key_exists( $component, $available_components ) ){
					wc_set_loop_prop($component, $flag );
				}
			}
		}
	}

	public function components_add_remove(){

		$component_hooks = $this->get_default_component_hooks();

		foreach ($component_hooks as $key => $component) {

			if( !is_array($component) ){
				continue;
			}

			if( isset( $component['type'] ) ){ // it means it's a regular, not sub-component
				$this->hooks_add_remove($component, wc_get_loop_prop($key));
			}
			else {
				foreach($component as $skey => $subcomponent){
					$this->hooks_add_remove($subcomponent, wc_get_loop_prop( $key . '_' . $skey ));
				}
			}
		}
	}

	/**
	 * Add Remove Hook
	 *
	 * @since 1.0.0
	 **/
	private function hooks_add_remove($item, $to_add)
	{
		if( isset($item['type']) ){
			call_user_func(
				sprintf('%s_%s', ($to_add ? 'add' : 'remove'), $item['type'] ),
				$item['tag'],
				$item['callback'],
				isset($item['priority']) ? $item['priority'] : 10,
				isset($item['accepted_args']) ? $item['accepted_args'] : 1
			);
		}
	}

	/**
	 * Predefined list of components.
	 *
	 * @since 1.0.0
	 */
	public function get_default_component_hooks(){

		return apply_filters('reycore/loop/component_hooks', [

			'result_count'         => [
				'type'          => 'action',
				'tag'           => 'woocommerce_before_shop_loop',
				'callback'      => 'woocommerce_result_count',
				'priority'      => 20,
			],

			'catalog_ordering'         => [
				'type'          => 'action',
				'tag'           => 'woocommerce_before_shop_loop',
				'callback'      => 'woocommerce_catalog_ordering',
				'priority'      => 30,
			],

			/**
			 * Loop components
			 */

			'view_selector'         => [
				'type'          => 'action',
				'tag'           => 'woocommerce_before_shop_loop',
				'callback'      => [ $this, 'loop_component_view_selector' ],
				'priority'      => 29,
			],
			'filter_button'         => [
				'type'          => 'action',
				'tag'           => 'woocommerce_before_shop_loop',
				'callback'      => [ $this, 'loop_component_filter_button' ],
				'priority'      => 20,
			],
			'mobile_filter_button' => [
				'type'          => 'action',
				'tag'           => 'woocommerce_before_shop_loop',
				'callback'      => [ $this, 'loop_component_mobile_sidebar_filter_button' ],
				'priority'      => 20,
			],
			'filter_top_sidebar' => [
				'type'          => 'action',
				'tag'           => 'rey/get_sidebar',
				'callback'      => [ $this, 'loop_component_filter_top_sidebar' ],
				'priority'      => 10,
			],

			/**
			 * Item components
			 */

			'brands'         => [
				'type'          => 'action',
				'tag'           => 'woocommerce_shop_loop_item_title',
				'callback'      => [ $this, 'component_brands' ],
				'priority'      => 4,
			],
			'category'       => [
				'type'          => 'action',
				'tag'           => 'woocommerce_shop_loop_item_title',
				'callback'      => [ $this, 'component_product_category'],
				'priority'      => 5,
			],
			'add_to_cart'    => [
				'type'          => 'action',
				'tag'           => 'woocommerce_after_shop_loop_item',
				'callback'      => 'woocommerce_template_loop_add_to_cart',
				'priority'      => 10,
			],
			'quickview'      => [
				'bottom' => [
					'type'          => 'action',
					'tag'           => 'woocommerce_after_shop_loop_item',
					'callback'      => [ $this, 'component_quickview_button' ],
					'priority'      => 10,
				],
				'topright' => [
					'type'          => 'action',
					'tag'           => 'reycore/loop_inside_thumbnail/top-right',
					'callback'      => [ $this, 'component_quickview_button' ],
					'priority'      => 10,
				],
				'bottomright' => [
					'type'          => 'action',
					'tag'           => 'reycore/loop_inside_thumbnail/bottom-right',
					'callback'      => [ $this, 'component_quickview_button' ],
					'priority'      => 10,
				],
			],
			'wishlist'       => [
				'bottom' => [
					'type'          => 'action',
					'tag'           => 'woocommerce_after_shop_loop_item',
					'callback'      => [ $this, 'component_wishlist' ],
					'priority'      => 40,
				],
				'topright' => [
					'type'          => 'action',
					'tag'           => 'reycore/loop_inside_thumbnail/top-right',
					'callback'      => [ $this, 'component_wishlist' ],
					'priority'      => 10,
				],
				'bottomright' => [
					'type'          => 'action',
					'tag'           => 'reycore/loop_inside_thumbnail/bottom-right',
					'callback'      => [ $this, 'component_wishlist' ],
					'priority'      => 10,
				],
			],
			'ratings'        => [
				'type'          => 'action',
				'tag'           => 'woocommerce_shop_loop_item_title',
				'callback'      => 'woocommerce_template_loop_rating',
				'priority'      => 3,
			],
			'prices'         => [
				'type'          => 'action',
				'tag'           => 'woocommerce_after_shop_loop_item_title',
				'callback'      => 'woocommerce_template_loop_price',
				'priority'      => 10,
			],
			'discount' => [
				'top'       => [
					'type'          => 'action',
					'tag'           => 'reycore/loop_inside_thumbnail/top-right',
					'callback'      => [$this, 'component_discount_percentage_or_sale_to_top'],
					'priority'      => 10
				],
				'price'       => [
					'type'          => 'filter',
					'tag'           => 'woocommerce_get_price_html',
					'callback'      => [$this, 'component_discount_percentage_to_price'],
					'priority'      => 10,
					'accepted_args' => 2
				],
			],
			'thumbnails'     => [
				'type'         => 'action',
				'tag'          => 'woocommerce_before_shop_loop_item_title',
				'callback'     => 'woocommerce_template_loop_product_thumbnail',
				'priority'     => 10,
			],
			'variations'         => [
				'type'          => 'action',
				'tag'           => 'reycore/variations_html',
				'callback'      => [ $this, 'component_variations' ],
				'priority'      => 10,
			],
			'new_badge'         => [
				'type'          => 'action',
				'tag'           => 'reycore/loop_inside_thumbnail/bottom-left',
				'callback'      => [ $this, 'component_new_badge' ],
				'priority'      => 10,
			],
			'sold_out_badge' => [
				'type'          => 'action',
				'tag'           => 'reycore/loop_inside_thumbnail/top-right',
				'callback'      => [ $this, 'component_sold_out_badge' ],
				'priority'      => 10,
			],
			'featured_badge' => [
				'type'          => 'action',
				'tag'           => 'reycore/loop_inside_thumbnail/top-left',
				'callback'      => [ $this, 'component_featured_badge' ],
				'priority'      => 10,
			],
			'title'          => [
				'type'          => 'action',
				'tag'           => 'woocommerce_shop_loop_item_title',
				'callback'      => 'woocommerce_template_loop_product_title',
				'priority'      => 10,
			],
			'excerpt'          => [
				'type'          => 'action',
				'tag'           => 'woocommerce_shop_loop_item_title',
				'callback'      => [$this, 'component_excerpt'],
				'priority'      => 10,
			],
		] );
	}

	/**
	 * Work in progress
	 */
	protected function get_default_settings( $setting = '' ){

		$settings = [
			'loop_alignment' => 'left',
		];

		if( isset( $settings[$setting] ) ){
			return $settings[$setting];
		}

		return $settings;
	}

	/**
	 * Item Component - Brands link
	 *
	 * @since 1.0.0
	 */
	public function component_brands(){
		echo ReyCore_WooCommerce_Brands::getInstance()->loop_show_brands();
	}

	/**
	 * Item Component - Category link
	 *
	 * @since 1.0.0
	 */
	public function component_product_category()
	{
		$product = wc_get_product();
		if ( ! ($product && $id = $product->get_id()) ) {
			return;
		}

		$__categories = get_the_terms( $id, 'product_cat' );

		if( is_wp_error( $__categories ) ){
			return;
		}

		if( empty($__categories) ){
			return;
		}

		$categories = [];

		// loop through each cat
		foreach($__categories as $category) :

			if( get_theme_mod('loop_categories__exclude_parents', false) ){
				// get the children (if any) of the current cat
				$children = get_categories(['taxonomy' => 'product_cat', 'parent' => $category->term_id ]);
				if ( count($children) == 0 ) {
					// if no children, then get the category name.
					$categories[] = sprintf('<a href="%s">%s</a>', get_term_link($category, 'product_cat'), $category->name);
				}
			}
			else {
				$categories[] = sprintf('<a href="%s">%s</a>', get_term_link($category, 'product_cat'), $category->name);
			}

		endforeach;

		if( empty($categories) ){
			return;
		}

		printf('<div class="rey-productCategories">%s</div>', implode(', ', $categories));

	}

	/**
	 * Item Component - Quickview button
	 *
	 * @since 1.0.0
	 */
	public function component_quickview_button(){
		echo ReyCore_WooCommerce_QuickView::get_button_html();
	}

	/**
	 * Item Component - Wishlist
	 *
	 * @since 1.3.0
	 */
	public function component_wishlist(){
		echo ReyCore_WooCommerce_Wishlist::get_button_html();
	}

	/**
	 * Item Component - iscount percentage to product price
	 *
	 * @since 1.0.0
	 */
	public function component_discount_percentage_to_price( $html, $product )
	{
		if( ! is_product() ){
			if ( get_theme_mod('loop_show_sale_label', 'percentage') == 'percentage' && $discount_perc = reycore_wc__get_discount( $product ) ){
				$label_pos = get_theme_mod('loop_discount_label', 'price');
				if ( $label_pos == 'price'  ) {
					return $html . $this->get_discount_html($label_pos, $discount_perc);
				}
			}
		}
		return $html;
	}

	/**
	 * Item Component - discount percentage or SALE label to product, top-right
	 *
	 * @since 1.0.0
	 */
	public function component_discount_percentage_or_sale_to_top()
	{
		if( ! is_product() && reycore_wc_get_loop_components('prices') ){

			if ( get_theme_mod('loop_show_sale_label', 'percentage') === 'percentage' && $discount_perc = reycore_wc__get_discount() ){
				if( ($label_pos = get_theme_mod('loop_discount_label', 'price')) && $label_pos === 'top' ){
					echo $this->get_discount_html($label_pos, $discount_perc);
				}
			}

			elseif( get_theme_mod('loop_show_sale_label', 'percentage') === 'sale' ){
				return woocommerce_show_product_loop_sale_flash();
			}
		}
	}

	/**
	 * Item Component - Variations buttons
	 *
	 * @since 1.3.0
	 */
	public function component_variations(){
		echo ReyCore_WooCommerce_Variations::getInstance()->do_variation();
	}

	/**
	 * Item Component - NEW badge to product entry for any product added in the last 30 days.
	*
	* @since 1.0.0
	*/
	public function component_new_badge() {
		$postdate      = get_the_time( 'Y-m-d' ); // Post date
		$postdatestamp = strtotime( $postdate );  // Timestamped post date
		$newness       = 30;                      // Newness in days
		if ( ( time() - ( 60 * 60 * 24 * $newness ) ) < $postdatestamp ) {
			printf('<div class="rey-itemBadge rey-new-badge">%s</div>', apply_filters('reycore/woocommerce/loop/new_text', esc_html__( 'NEW', 'rey-core' ) ) );
		}
	}

	/**
	 * Item Component - Sold out badge for out of stock items.
	*
	* @since 1.4.4
	*/
	public function component_sold_out_badge() {

		$product = wc_get_product();

		if( $product ){

			$badge = '';

			if( $product->is_in_stock() ){
				if( get_theme_mod('loop_sold_out_badge', '1') === 'in-stock' ){
					$badge = apply_filters('reycore/woocommerce/loop/in_stock_text', esc_html__( 'IN STOCK', 'rey-core' ) );
				}
			}
			else {
				if( get_theme_mod('loop_sold_out_badge', '1') === '1' ) {
					$badge = apply_filters('reycore/woocommerce/loop/sold_out_text', esc_html__( 'SOLD OUT', 'rey-core' ) );
				}
			}

			if( empty($badge) ){
				return;
			}

			printf('<div class="rey-itemBadge rey-soldout-badge">%s</div>', $badge );
		}

	}

	/**
	 * Item Component - Sold out badge for out of stock items.
	*
	* @since 1.6.9
	*/
	public function component_featured_badge() {

		$product = wc_get_product();

		if( $product && get_theme_mod('loop_featured_badge', 'hide') === 'show' && $product->is_featured() ){
			printf('<div class="rey-itemBadge rey-featured-badge">%s</div>', get_theme_mod('loop_featured_badge__text', esc_html__('FEATURED', 'rey-core')) );
		}
	}

	function component_excerpt() {

		global $post;

		if ( ! ($excerpt = apply_filters( 'woocommerce_short_description', $post->post_excerpt )) ) {
			return;
		}

		if( $limit = absint( get_theme_mod('loop_short_desc_limit', '8') ) ){
			$excerpt = wp_trim_words($excerpt, $limit);
		}

		$class = '';

		if( get_theme_mod('loop_short_desc_mobile', false) ){
			$class .= ' --show-mobile';
		}

		?>
		<div class="woocommerce-product-details__short-description <?php esc_attr_e($class)  ?>">
			<?php echo $excerpt; // WPCS: XSS ok. ?>
		</div>
		<?php
	}


	/**
	 * Utility
	 *
	 * Utility to check exact product type
	 *
	 * @since 1.1.2
	 */
	function is_product(){
		return in_array( get_post_type(), [ 'product', 'product_variation' ], true );
	}

	function is_custom_image_height(){
		return ! in_array( get_theme_mod('loop_grid_layout', 'default'), [ 'metro' ]) &&
		get_option('woocommerce_thumbnail_cropping', '1:1') === 'uncropped' &&
		get_theme_mod('custom_image_height', false) === true ;
	}

	/**
	 * Utility
	 *
	 * Add animation hover class in loop
	 *
	 * @since 1.0.0
	 */
	function general_css_classes($exclude = [])
	{
		$classes = [];

		if ( in_array( get_post_type(), [ 'product', 'product_variation' ], true ) ) {
			$classes['rey_skin'] = 'rey-wc-skin--' . get_theme_mod('loop_skin', 'basic');
		}

		if ( $this->is_product() ) {

			if( !in_array('animation-entry', $exclude) && ! reycore__elementor_edit_mode() && wc_get_loop_prop( 'entry_animation' ) !== false ){
				$classes['animated-entry'] = 'is-animated-entry';
			}

			// Check if product has custom height
			// @note: using get_option on WC's cropping option intentionally
			if(
				! in_array('image-container-height', $exclude) &&
				$this->is_custom_image_height()
			){
				$classes['image-container-height'] = ' --customImageContainerHeight';
			}

			if( !in_array('text-alignment', $exclude) ){
				$classes['text-align'] = 'rey-wc-loopAlign-' . get_theme_mod('loop_alignment', 'left');
			}

			// Check if product has more than one image
			if( !in_array('extra-media', $exclude) && ($images = reycore_wc__get_product_images_ids()) && count($images) > 1 ){
				$classes['extra-media'] = '--extraImg-' . get_theme_mod('loop_extra_media', 'second');
			}

			if( get_option('woocommerce_thumbnail_cropping', '1:1') === 'uncropped' ){
				$classes[] = '--uncropped';
			}

		}

		return $classes;
	}

	/**
	 * Utility
	 *
	 * Filter product list start to add custom CSS classes
	 *
	 * @since 1.1.2
	 */
	public function filter_product_list_start( $html )
	{
		$search_for = 'class="products';

		$classes['columns_tablet'] = 'columns-tablet-' . reycore_wc_get_columns('tablet');
		$classes['columns_mobile'] = 'columns-mobile-' . reycore_wc_get_columns('mobile');

		if( reycore_wc_get_columns('mobile') === 1 && get_theme_mod('woocommerce_catalog_mobile_listview', false) ){
			$classes['mobile_listview'] = 'rey-wcGrid-mobile-listView';
		}

		/**
		 * Grid Gap CSS class
		 */
		$classes['grid_gap'] = 'rey-wcGap-' . esc_attr( get_theme_mod('loop_gap_size', 'default') );

		/**
		 * Product Grid CSS class
		 */

		$classes['grid_layout'] = 'rey-wcGrid-' . esc_attr( get_theme_mod('loop_grid_layout', 'default') );

		$html = str_replace( $search_for, $search_for . ' ' . implode(' ', apply_filters('reycore/woocommerce/product_loop_classes', $classes)), $html );
		$html = str_replace( '<ul', sprintf('<ul data-cols="%s"', esc_attr( wc_get_loop_prop( 'columns' ) )), $html );

		return $html;
	}

	/**
	 * Loop Component
	 *
	 * Add viewselector
	 *
	 * @since 1.0.0
	 **/
	function loop_component_view_selector()
	{
		reycore__get_template_part('template-parts/woocommerce/view-selector');
	}

	/**
	 * Loop Component
	 *
	 * Add Filter button in loop's header
	 *
	 * @since 1.0.0
	 **/
	function loop_component_filter_button()
	{
		reycore__get_template_part('template-parts/woocommerce/filter-panel-button');
	}

	function add_filter_btn_class( $classes ){
		$classes['mobile_class'] = 'filter-btnMobile';
		return $classes;
	}
	/**
	 * Loop Component
	 *
	 * Add Filter button in loop's header, for sidebar on mobiles
	 *
	 * @since 1.0.0
	 **/
	function loop_component_mobile_sidebar_filter_button()
	{
		add_filter('reycore/filters/btn_class', [$this, 'add_filter_btn_class']);
		reycore__get_template_part('template-parts/woocommerce/filter-panel-button');
		remove_filter('reycore/filters/btn_class', [$this, 'add_filter_btn_class']);
	}

	/**
	 * Loop utility
	 *
	 * Filter Sidebar - Add CSS class to shop sidebar
	 *
	 * @since 1.0.0
	 **/
	function mobile_sidebar_add_filter_class($classes)
	{
		if( (reycore_wc__check_shop_sidebar() || reycore_wc__check_filter_sidebar_top()) && !is_singular('product') ) {
			$classes[] = 'rey-filterSidebar';
		}

		if( reycore_wc__check_filter_panel() ){
			$classes[] = '--filter-panel';
		}
		return $classes;
	}

	/**
	 * Loop Component
	 *
	 * HTML wrapper to insert after the not found product loops.
	 *
	 */
	function after_no_products($template_name = '', $template_path = '', $located = '') {
		if ($template_name == 'loop/no-products-found.php') {
			if( reycore_wc__check_filter_panel() ) {
				reycore__get_template_part('template-parts/woocommerce/filter-panel-button-not-found');
			}
		}
	}

	/**
	 * Loop Component
	 *
	 * Add Filter panel after footer
	 *
	 * @since 1.0.0
	 **/
	function add_filter_sidebar_panel()
	{
		if( reycore_wc__check_filter_panel() ) {
			reycore__get_template_part('template-parts/woocommerce/filter-panel-sidebar');
		}
	}

	/**
	 * Item Component
	 *
	 * Add Filter sidebar before loop
	 *
	 * @since 1.0.0
	 **/
	function loop_component_filter_top_sidebar($position)
	{
		if(
			( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) && reycore_wc__check_filter_sidebar_top()
		) {
			reycore__get_template_part('template-parts/woocommerce/filter-top-sidebar');
		}
	}

	/**
	 * Wrap layout - start
	 *
	 * @since 1.0.0
	 **/
	function start_wrapper_div()
	{
		$product = wc_get_product();

		if ( ! ($product && $id = $product->get_id()) ) {
			return;
		}

		printf('<div class="rey-productInner" data-id="%d" >', absint($id));
	}

	function start_wrapper_cat_div()
	{
		echo '<div class="rey-productInner">';
	}

	/**
	 * Wrap loop header
	 *
	 * @since 1.0.0
	 **/
	function header_wrapper_start()
	{
		if( ! (wc_get_loop_prop( 'result_count', true ) || wc_get_loop_prop( 'catalog_ordering', true )) ){
			return;
		}

		echo sprintf('<div class="rey-loopHeader %s">', esc_attr( implode(' ', apply_filters('rey/woocommerce/loop/header_classes', [] ) ) ) );
	}

	/**
	 * Wrap loop header
	 *
	 * @since 1.0.0
	 **/
	function header_wrapper_end()
	{
		if( ! (wc_get_loop_prop( 'result_count', true ) || wc_get_loop_prop( 'catalog_ordering', true )) ){
			return;
		}

		do_action('reycore/loop_products/before_header_end');

		echo '</div>';
	}

	/**
	 * Utility
	 *
	 * Get the Discount percentage HTML markup
	 *
	 * @since 1.0.0
	 */
	function get_discount_html($label_pos, $discount_perc ){
		return sprintf(
				__('<span class="rey-discount">-%s%%</span>', 'rey-core'),
				$discount_perc
			);
	}

	/**
	 * Utility
	 *
	 * Wrap thumbnails with links
	 *
	 * @since 1.2.0
	 */
	public function add_wrapping_links(){

		if( ! $this->should_wrap_thumbnails_with_link() ){
			return;
		}

		add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_link_open', 9 );
		add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_link_close', 12 );
	}

	/**
	 * Utility
	 *
	 * Checks if the thumbnail should be wrapped with link
	 *
	 * @since 1.2.0
	 */
	public function should_wrap_thumbnails_with_link() {

		$status = true;


		if( get_theme_mod('loop_extra_media', 'second') === 'slideshow' ){
			$status = false;
		}

		return apply_filters('reycore/loop_product/should_wrap_thumbnails_with_link', $status);
	}

	/**
	 * Utility
	 *
	 * Wrap product thumbnail - start
	 *
	 * @since 1.0.0
	 **/
	function thumbnail_wrapper_start()
	{
		?>
		<div class="rey-productThumbnail">
		<?php
		foreach([ 'top-left', 'top-right', 'bottom-left', 'bottom-right' ] as $position){

			$hook = 'reycore/loop_inside_thumbnail/' . $position;

			ob_start();
			do_action($hook);
			$th_content = ob_get_clean();

			if( !empty($th_content) ){
				printf('<div class="rey-thPos rey-thPos--%2$s">%1$s</div>', $th_content, $position);
			}
		}
	}

	/**
	 * Utility
	 *
	 * Add second image
	 *
	 * @since 1.0.0
	 **/
	function add_second_thumbnail( $html, $product, $size )
	{
		if( is_admin() ){
			return $html;
		}

		if( get_query_var('reycore_wc__prevent_2nd_image') === true ){
			return $html;
		}

		if( get_theme_mod('loop_extra_media', 'second') !== 'second' ){
			return $html;
		}

		$second_img = '';

		if ( ($images = reycore_wc__get_product_images_ids()) && count($images) > 1 ) {
			$second_img = wp_get_attachment_image( $images[1], $size, false, ['class'=>'rey-productThumbnail__second'] );
		}

		return $html . $second_img;
	}

	/**
	 * Utility
	 *
	 * Add thumbnails slideshow
	 *
	 * @since 1.0.0
	 **/
	function add_thumbnails_slideshow( $html, $product, $size )
	{
		if( is_admin() ){
			return $html;
		}

		if( get_query_var('loop_prevent_product_items_slideshow') === true ){
			return $html;
		}

		if( get_theme_mod('loop_extra_media', 'second') === 'slideshow' ){

			$html_images = [];

			if( $images = reycore_wc__get_product_images_ids() ){
				if ( count($images) > 1  && wc_get_loop_prop( 'product_thumbnails_slideshow', true ) ) {

					$max = get_theme_mod('loop_slideshow_nav_max', 4);
					foreach ($images as $key => $img) {
						if( $max < ($key + 1) ) {
							continue;
						}
						$html_images[] = sprintf('<a href="%s">%s</a>',
							esc_url( apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product ) ),
							wp_get_attachment_image( $img, $size, false, ['class'=>'rey-productThumbnail-extra'] )
						);
					}

					$classes = '';

					if( get_theme_mod('loop_slideshow_nav_color_invert', false) ){
						$classes .= ' --color-invert';
					}

					$html = sprintf('<div class="rey-productSlideshow %2$s">%1$s</div>', implode('', $html_images), $classes ) ;
				}
				else {
					$html = sprintf('<a href="%s" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">%s</a>',
						esc_url( apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product ) ),
						$html
					);
				}
			}
		}

		return $html;
	}

	function apply_extra_thumbs_filter(){
		add_filter( 'woocommerce_product_get_image', [$this, 'add_second_thumbnail'], 10, 3);
		add_filter( 'woocommerce_product_get_image', [$this, 'add_thumbnails_slideshow'], 10, 3);
	}

	function remove_extra_thumbs_filter(){
		remove_filter( 'woocommerce_product_get_image', [$this, 'add_second_thumbnail'], 10, 3);
		remove_filter( 'woocommerce_product_get_image', [$this, 'add_thumbnails_slideshow'], 10, 3);
	}

	function add_to_cart_args( $args, $product ){

		if( $btn_style = get_theme_mod('loop_add_to_cart_style', 'under') ){
			$args['class'] .= ' rey-btn--' . $btn_style;
		}

		if( get_theme_mod('loop_add_to_cart_mobile', false) ){
			$args['class'] .= ' --mobile-on';
		}

		return $args;
	}

	/**
	 * Work in progress
	 */
	function archive_description_more_tag( $content ){

		if ( !(is_product_taxonomy() || is_product_category() || is_shop()) ) {
			return $content;
		}

		$parts = str_replace( '&lt;!--more--&gt;', '<!--more-->', $content );

		if( strpos($parts, '<!--more-->') !== false ){
			$parts = str_replace(['<p>', '</p>'], '', $parts);
			$parts = explode( '<!--more-->', $parts );

			return $parts[0] . '<button class="btn u-toggle-btn" data-read-more="'. esc_html_x('Read more', 'Toggling the product excerpt in Compact layout.', 'rey-core') .'" data-read-less="'. esc_html_x('Less', 'Toggling the product excerpt in Compact layout.', 'rey-core') .'"></button>';
		}

		return $content;
	}


	private function set_loop_skins(){
		$this->_skins = [
			'default'  => esc_attr__('Default', 'rey-core'),
			'basic'    => esc_attr__('Basic Skin', 'rey-core'),
			'wrapped'  => esc_attr__('Wrapped Skin', 'rey-core'),
		];
	}

	function load_skins( $field ){
		$field['choices'] = $this->_skins;
		return $field;
	}

	/**
	 * Load files
	 *
	 * @since 1.0.0
	 **/
	function load_includes()
	{
		foreach( $this->_skins as $skin => $name ){
			if( ($skin_path = REY_CORE_DIR . sprintf("inc/woocommerce/tags/loop-skin-{$skin}.php", $skin)) && is_readable($skin_path) ){
				require $skin_path;
			}
		}
	}

	public function get_loop_active_skin(){
		return get_theme_mod('loop_skin', 'basic');
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_WooCommerce_Loop
	 */
	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
}
endif;

ReyCore_WooCommerce_Loop::getInstance();
