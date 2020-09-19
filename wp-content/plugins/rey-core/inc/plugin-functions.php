<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


if(!function_exists('reycore__theme_active')):
	/**
	 * Check if Rey is active
	 *
	 * @since 1.0.0
	 **/
	function reycore__theme_active()
	{
		$theme = wp_get_theme();
		$rey_theme_name = apply_filters('reycore/theme_name', ucfirst(REY_CORE_THEME_NAME));
		return ( $rey_theme_name == $theme->name || $rey_theme_name == $theme->parent_theme );
	}
endif;


if(!function_exists('reycore__get_purchase_code')):
	/**
	 * Get purchase code
	 *
	 * @since 1.0.0
	 **/
	function reycore__get_purchase_code()
	{
		return trim( get_site_option( 'rey_purchase_code' ) );
	}
endif;


if(!function_exists('reycore__get_dashboard_page_id')):
	/**
	 * Get Rey's dashboard page id
	 *
	 * @since 1.0.0
	 **/
	function reycore__get_dashboard_page_id()
	{
		if( class_exists('ReyTheme_Base') ){
			return ReyTheme_Base::DASHBOARD_PAGE_ID;
		}
		return false;
	}
endif;


if(!function_exists('reycore__get_post_id')):
	/**
	 * Wrapper for queried object
	 *
	 * @since 1.0.0
	 */
	function reycore__get_post_id() {

		if( class_exists('WooCommerce') && is_shop() ){
			return wc_get_page_id('shop');
		}
		elseif( is_home() ){
			return absint( get_option('page_for_posts') );
		}
		elseif( is_tax() || is_archive() || is_author() || is_category() || is_tag() ){
			if( apply_filters('rey/get_queried_object_id', false, 'reycore' ) ){
				return get_queried_object_id();
			}
			else {
				return get_queried_object();
			}
		}
		elseif( isset($_GET['preview_id']) && isset($_GET['preview_nonce']) && ($pid = $_GET['preview_id']) ){
			return absint( $pid );
		}

		return false;
	}
endif;

if(!function_exists('reycore__get_theme_mod')):
	/**
	 * Get theme mod wrapper
	 *
	 * @since 1.6.6
	 **/
	function reycore__get_theme_mod( $mod, $default, $args = [] )
	{
		$args = wp_parse_args($args, [
			'translate' => false,
			'translate_post_type' => false,
		]);

		if( $args['translate'] ){
			return apply_filters('reycore/theme_mod/translate_ids', get_theme_mod($mod, $default), $args['translate_post_type']);
		}

		return get_theme_mod($mod, $default);
	}
endif;


if(!function_exists('reycore__config')):
	/**
	 * Wrapper for ReyConfig
	 *
	 * @since 1.0.0
	 */
	function reycore__config( $setting = '' ) {
		if(function_exists('rey__config')) {
			return rey__config($setting);
		}
		return false;
	}
endif;


if(!function_exists('reycore__exclude_modules')):
	/**
	 * Exclude modules from running since early load
	 *
	 * @since 1.0.0
	 */
	function reycore__exclude_modules() {
		return apply_filters('reycore/exclude_modules', []);
	}
endif;


if(!function_exists('reycore__acf_get_field')):
	/**
	 * Get ACF Field - wrapper for get_field
	 *
	 * @since 1.0.0
	 **/
	function reycore__acf_get_field( $name, $pid = false, $return = false )
	{
		if( ! $pid ) {
			$pid = reycore__get_post_id();
		}

		// check for ACF and get the field
		if( class_exists('ACF') )  {

			$field = get_field( $name, $pid );

			if( ! $field && $return !== false ) {
				return $return;
			}

			return $field;
		}

		return $return;
	}
endif;


if(!function_exists('reycore__get_option')):
	/**
	 * Get Option - wrapper for get_theme_mod and get_field
	 * overrides reycore__get_option from theme
	 *
	 * @since 1.0.0
	 **/
	function reycore__get_option( $name, $default = false, $skip_acf = false )
	{
		// check for ACF and get the field
		if( !$skip_acf && ($opt = reycore__acf_get_field( $name )) )  {
			return apply_filters("rey_acf_option_{$name}", $opt);
		}

		return get_theme_mod( $name, $default);
	}
endif;


if(!function_exists('reycore__get_header_styles')):
	/**
	 * Wrapper for `rey__get_header_styles`
	 *
	 * @since 1.0.0
	 **/
	function reycore__get_header_styles( $add_default = true )
	{
		$defaults = [
			'none'  => esc_html__( 'Disabled', 'rey-core' ),
		];

		if( $add_default ){
			$defaults['default'] = esc_html__( 'Default', 'rey-core' );
		}

		return apply_filters('reycore/options/header_layout_options', $defaults, true);
	}
endif;


if(!function_exists('reycore__get_footer_styles')):
	/**
	 * Get footer styles
	 *
	 * @since 1.0.0
	 **/
	function reycore__get_footer_styles()
	{
		return apply_filters('reycore/options/footer_layout_options', [
			'none'  => esc_attr__( 'None', 'rey-core' ),
			'default'  => esc_attr__( 'Default Footer', 'rey-core' ),
		], true);
	}
endif;


if(!function_exists('reycore__get_all_menus')):
	/**
	 * Get a list of all WordPress menus
	 *
	 * @since 1.0.0
	 */
	function reycore__get_all_menus( $clean = true ){

			$terms = get_terms( 'nav_menu', ['update_term_meta_cache'	=> false] );

		if( $clean ){

			$menus = [];
			foreach ($terms as $term) {
				$menus[$term->slug] = $term->name;
			}
			return $menus;
		}

		if( !is_array($terms) ){
			return [];
		}
		else {
			return $terms;
		}
	}
endif;


if(!function_exists('reycore__header_footer_layout_desc')):
	/**
	 * Retrieve header / footer list description
	 *
	 * @since 1.0.0
	 **/
	function reycore__header_footer_layout_desc( $type = 'header', $inherit_text = false )
	{

		$suffix = '';

		$layout_type = esc_html(ucfirst($type));

		if( $inherit_text ){
			$suffix = sprintf(
				__('By default inherits the option from Customizer > %s > General.' , 'rey-core'),
				$layout_type
			);
		}

		return sprintf(
			__('A Global Section is a block of content built with Elementor, that\'s embedded into the website. To use or create more %1$s global sections, head over to <a href="%2$s" target="blank">%3$s</a>. %4$s' , 'rey-core'),
			$layout_type,
			admin_url('edit.php?post_type=rey-global-sections'),
			esc_html__('Global Sections', 'rey-core'),
			$suffix
		);
	}
endif;


if(!function_exists('reycore__var_dump')):
	/**
	 * Shows debug info wrapped in var_dump.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  mixed $c Content to display.
	 * @param  bool $hidden Wether or not to hide.
	 * @return void
	 */
	function reycore__var_dump($c, $hidden = true) {
		echo '<div class="' .( $hidden ? 'd-none' : '' ). '">';
		var_dump($c);
		echo '</div>';
	}
endif;


if(!function_exists('reycore__get_hooks')):
	/**
	 * Shows debug info wrapped in var_dump.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  mixed $c Content to display.
	 * @param  bool $hidden Wether or not to hide.
	 * @return void
	 */
	function reycore__get_hooks($hook) {
		global $wp_filter;
		var_dump( $wp_filter[$hook] );
	}
endif;


if(!function_exists('reycore__breadcrumbs_args')):
	/**
	 * Filter WooCommerce Breadcrumbs defaults
	 *
	 * @since 1.0.0
	 */
	function reycore__breadcrumbs_args($args){

		$args['delimiter']   = '<span class="rey-breadcrumbs-del">&#8250;</span>';
		$args['wrap_before'] = '<nav class="rey-breadcrumbs">';
		$args['wrap_after']  = '</nav>';
		$args['before']  = '<div class="rey-breadcrumbs-item">';
		$args['after']  = '</div>';

		return $args;
	}
endif;
add_filter('woocommerce_breadcrumb_defaults', 'reycore__breadcrumbs_args', 5);


/**
 * Remove Instagram Widget Assets
 *
 * @since 1.0.0
 **/
function reycore__instagram_widget_js_assets()
{
	if( class_exists('Wpzoom_Instagram_Widget_API') ):
		wp_dequeue_script( 'zoom-instagram-widget-lazy-load' );
		wp_dequeue_script( 'zoom-instagram-widget' );
	endif;
}
add_action('wp_print_scripts','reycore__instagram_widget_js_assets');


/**
 * Remove Instagram Widget Assets
 *
 * @since 1.0.0
 **/
function reycore__instagram_widget_css_assets()
{
	if( class_exists('Wpzoom_Instagram_Widget_API') ):
		wp_dequeue_style( 'zoom-instagram-widget' );
	endif;
}
add_action('wp_print_styles','reycore__instagram_widget_css_assets');


function reycore_add_products_global(){
	$GLOBALS["rey_exclude_products"] = [];
}
add_action('wp', 'reycore_add_products_global', 5);


if(!function_exists('rey__log_error')):
	/**
	 * Log Errors.
	 * Dummy function for Rey theme's `rey__log_error` to avoid errors
	 * if Rey theme is not active.
	 *
	 * @since 1.0.0
	 **/
	function rey__log_error( $source = '', $error )
	{
		return false;
	}
endif;


if(!function_exists('reycore__wp_parse_args')):
	/**
	 * Recursive wp_parse_args WordPress function which handles multidimensional arrays
	 * @url http://mekshq.com/recursive-wp-parse-args-wordpress-function/
	 * @param  array &$a Args
	 * @param  array $b Defaults
	 * @since: 1.0.0
	 */
	function reycore__wp_parse_args( &$a, $b )
	{
		$a = (array)$a;
		$b = (array)$b;
		$result = $b;
		foreach ( $a as $k => &$v )
		{
			if ( is_array( $v ) && isset( $result[ $k ] ) )
			{
				$result[ $k ] = reycore__wp_parse_args( $v, $result[ $k ] );
			}
			else
			{
				$result[ $k ] = $v;
			}
		}
		return $result;
	}
endif;


if(!function_exists('reycore__format_period')):
	/**
	 * Format microtime
	 *
	 * @since 1.0.0
	 **/
	function reycore__format_period( $duration )
	{
		$hours = (int) ($duration / 60 / 60);
		$minutes = (int) ($duration / 60) - $hours * 60;
		$seconds = (int) $duration - $hours * 60 * 60 - $minutes * 60;

		return ($hours == 0 ? "00":$hours) . ":" . ($minutes == 0 ? "00":($minutes < 10? "0".$minutes:$minutes)) . ":" . ($seconds == 0 ? "00":($seconds < 10? "0".$seconds:$seconds));
	}
endif;


if(!function_exists('reycore__clean')):
	/**
	 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
	 * Non-scalar values are ignored.
	 *
	 * @param string|array $var Data to sanitize.
	 * @return string|array
	 */
	function reycore__clean( $var ) {
		if ( is_array( $var ) ) {
			return array_map( 'reycore__clean', $var );
		} else {
			if( is_bool($var) ){
				return filter_var($var, FILTER_VALIDATE_BOOLEAN);
			}
			else {
				return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
			}
		}
	}
endif;

if(!function_exists('reycore__array_values_recursive')):
	/**
	 * Get arrau values recursively
	 * https://davidwalsh.name/get-array-values-with-php-recursively
	 * @since 1.6.6
	 */
	function reycore__array_values_recursive($array) {
		$flat = [];

		foreach($array as $value) {
			if (is_array($value)) {
				$flat = array_merge($flat, reycore__array_values_recursive($value));
			}
			else {
				$flat[] = $value;
			}
		}

		return $flat;
	}
endif;


if(!function_exists('reycore__preloader_is_active')):
	/**
	 * Checks if preloader is active.
	 *
	 * @since 1.0.0
	 */
	function reycore__preloader_is_active() {
		return get_theme_mod('site_preloader', true);
	}
endif;

if(!function_exists('reycore__get_attachment_image')):
	function reycore__get_attachment_image( $args = [] ){

		$_image = '';

		$defaults = [
			'image' => [
				'id' => '',
				'url' => '',
			],
			'size' => 'large',
			'attributes' => [],
			'key' => 'image',
			'settings' => [],
			'return_url' => false
		];

		// Parse args.
		$args = reycore__wp_parse_args( $args, $defaults );

		$image_id = $args['image']['id'];

		if( $args['size'] === 'custom' && !empty($args['settings']) ){

			$image_src = \Elementor\Group_Control_Image_Size::get_attachment_image_src( $image_id, $args['key'], $args['settings'] );

			if( $args['return_url'] ){

				$width = 800;
				$height = 800;

				if( isset( $args['settings'][ $args['key'] . '_custom_dimension' ] ) ){
					$width = $args['settings'][ $args['key'] . '_custom_dimension' ]['width'];
					$height = $args['settings'][ $args['key'] . '_custom_dimension' ]['height'];
				}

				return [
					$image_src,
					absint($width),
					absint($height)
				];
			}
		}

		if( $args['return_url'] ){
			return wp_get_attachment_image_src( $image_id, $args['size']);
		}

		if( $image_id ) {
			$_image = wp_get_attachment_image( $image_id, $args['size'], false, $args['attributes']);
		}
		elseif( $image_url = $args['image']['url'] ) {
			$_image = sprintf( '<img src="%s" %s>', $image_url, implode(' ', array_map(
				function ($k, $v) { return $k .'="'. esc_attr($v) .'"'; },
				array_keys($args['attributes']), $args['attributes']
			)) );
		}

		return $_image;
	}
endif;


if(!function_exists('reycore__array_has_string_keys')):
	/**
	 * Chec if array has string keys
	 *
	 * @since 1.0.0
	 **/
	function reycore__array_has_string_keys(array $array) {
		return count( array_filter( array_keys( $array ), 'is_string' ) ) > 0;
	}
endif;


if(!function_exists('reycore__elementor_edit_mode')):
	/**
	 * Check if Elementor is in edit mode
	 *
	 * @since 1.0.0
	 **/
	function reycore__elementor_edit_mode()
	{
		return class_exists('\Elementor\Plugin') && ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() );
	}
endif;


if(!function_exists('reycore__arrowSvg')):
	/**
	 * wrapper for arrow SVG
	 *
	 * @since 1.0.0
	 **/
	function reycore__arrowSvg( $right = true, $class = '' )
	{
		if( function_exists('rey__arrowSvg') ){
			return rey__arrowSvg( $right, $class );
		}
		return false;
	}
endif;


if(!function_exists('reycore__get_template_part')):
	/**
	 * Get template part alternative for ReyCore plugin
	 *
	 * @since 1.0.0
	 **/
	function reycore__get_template_part( $slug, $path = false, $skip_theme = false )
	{
		$cache_key = sanitize_key( implode( '-', ['template-part', $slug, REY_CORE_VERSION] ) );
		$template  = (string) wp_cache_get( $cache_key, 'rey-core' );

		if ( ! $template ) {

			if( !$path ){
				$path = REY_CORE_DIR;
			}

			if ( file_exists( STYLESHEETPATH . '/' . $slug . ".php" ) ) {
				$template = STYLESHEETPATH . '/' . $slug . ".php";
			}
			elseif ( ! $skip_theme && file_exists( TEMPLATEPATH . '/' . $slug . ".php" ) ) {
				$template = TEMPLATEPATH . '/' . $slug . ".php";
			}
			elseif ( file_exists( $path . $slug . ".php" ) ) {
				$template = $path . $slug . ".php";
			}

			if( ! (defined('REY_DEV_MODE') && REY_DEV_MODE) ) {
				wp_cache_set( $cache_key, $template, 'rey-core' );
			}
		}

		// Allow 3rd party plugins to filter template file from their plugin.
		$template = apply_filters( 'reycore/get_template_part', $template, $slug );

		if ( $template ) {
			load_template( $template, false );
		}
	}
endif;


if(!function_exists('reycore__wp_filesystem')):
	/**
	 * Retrieve the reference to the instance of the WP file system
	 * Wrapper for `rey__wp_filesystem` in the theme.
	 * @return $wp_filesystem
	 * @since 1.0.0
	 */
	function reycore__wp_filesystem()
	{
		if( function_exists('rey__wp_filesystem') ){
			return rey__wp_filesystem();
		}
	}
endif;

if(!function_exists('reycore__download_sideload_file')):
/**
 * Download file without adding it into Media Library
 *
 * @since 1.0.0
 **/
function reycore__download_sideload_file( $url = '' )
{
	if( empty($url) ){
		return;
	}

	// Gives us access to the download_url() and wp_handle_sideload() functions
	require_once( ABSPATH . 'wp-admin/includes/file.php' );

	$timeout_seconds = 5;

	// Download file to temp dir
	$temp_file = download_url( $url, $timeout_seconds );

	if ( !is_wp_error( $temp_file ) ) {

		$file = array(
			'name'     => basename($url),
			'type'     => 'application/json',
			'tmp_name' => $temp_file,
			'error'    => 0,
			'size'     => filesize($temp_file),
		);

		$overrides = array(
			// Tells WordPress to not look for the POST form
			// fields that would normally be present as
			// we downloaded the file from a remote server, so there
			// will be no form fields
			// Default is true
			'test_form' => false,
			'test_size' => true, // Setting this to false lets WordPress allow empty files, not recommended
			'test_type' => false,
		);

		// Move the temporary file into the uploads directory
		$result = wp_handle_sideload( $file, $overrides );

		// 	$filename  = $result['file']; // Full path to the file
		// 	$local_url = $result['url'];  // URL to the file in the uploads dir
		// 	$type      = $result['type']; // MIME type of the file
		return $result;
	}
}
endif;



if(!function_exists('reycore__kirki_typography_process')):
	/**
	 * Process Kirki's typography CSS
	 *
	 * @return empty string
	 * @since 1.0.0
	 **/
	function reycore__kirki_typography_process($args = [])
	{
		$defaults = [
			'name' => '',
			'prefix' => '',
			'supports' => [
				// options:
				// 'font-family', 'font-size', 'line-height', 'variant', 'font-weight', 'letter-spacing', 'text-transform'
			],
			'wrap' => false,
			'default_values' => []
		];
		$args = reycore__wp_parse_args($args, $defaults);

		if( $args['name'] ) {

			$mod = get_theme_mod($args['name'], $args['default_values']);

			$css = '';

			if( !empty($mod) ){
				foreach ($mod as $key => $value) {
					if( in_array($key, $args['supports']) && !empty($value) ){
						$css .= $args['prefix'] . "{$key}: {$value};";
					}
				}
			}

			if( !empty($css) && $args['wrap'] ){
				$css = $args['wrap'] . "{{$css}}";
			}

			return $css;
		}
		return '';
	}
endif;

if(!function_exists('reycore__parse_text_editor')):
	/**
	 * Parse text coming from rich editor
	 *
	 * @since 1.1.0
	 **/
	function reycore__parse_text_editor( $content ) {

		$content = shortcode_unautop( $content );
		$content = do_shortcode( $content );
		$content = wptexturize( $content );

		if ( $GLOBALS['wp_embed'] instanceof \WP_Embed ) {
			$content = $GLOBALS['wp_embed']->autoembed( $content );
		}

		return $content;
	}
endif;


if(!function_exists('reycore__get_cf7_forms')):
	/**
	 * Get forms.
	 *
	 * Retrieve an array of forms from the CF7 plugin.
	 */
	function reycore__get_cf7_forms() {

		$cf7 = get_posts( 'post_type="wpcf7_contact_form"&numberposts=-1' );

		$contact_forms = [];
		if ( $cf7 ) {
			$contact_forms[''] = '- Select -';
			foreach ( $cf7 as $cform ) {
				$contact_forms[ $cform->ID ] = $cform->post_title;
			}
		}
		return $contact_forms;
	}
endif;

if(!function_exists('reycore__remove_filters_with_method_name')):
	/**
	 * Allow to remove method for an hook when, it's a class method used and class don't have global for instanciation !
	 * Solution from https://github.com/herewithme/wp-filters-extras/
	 *
	 * @since 1.3.0
	 */
	function reycore__remove_filters_with_method_name( $hook_name = '', $method_name = '', $priority = 0 ) {
		global $wp_filter;
		// Take only filters on right hook name and priority
		if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
			return false;
		}
		// Loop on filters registered
		foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
			// Test if filter is an array ! (always for class/method)
			if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
				// Test if object is a class and method is equal to param !
				if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && $filter_array['function'][1] == $method_name ) {
					// Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/)
					if ( is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) ) {
						unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
					} else {
						unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
					}
				}
			}
		}
		return false;
	}
endif;

if(!function_exists('reycore__remove_filters_for_anonymous_class')):
	/**
	 * Allow to remove method for an hook when, it's a class method used and class don't have variable, but you know the class name :)
	 * Solution from https://github.com/herewithme/wp-filters-extras/
	 *
	 * @since 1.3.0
	 */
	function reycore__remove_filters_for_anonymous_class( $hook_name = '', $class_name = '', $method_name = '', $priority = 0 ) {
		global $wp_filter;
		// Take only filters on right hook name and priority
		if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
			return false;
		}
		// Loop on filters registered
		foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
			// Test if filter is an array ! (always for class/method)
			if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
				// Test if object is a class, class and method is equal to param !
				if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) == $class_name && $filter_array['function'][1] == $method_name ) {
					// Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/)
					if ( is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) ) {
						unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
					} else {
						unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
					}
				}
			}
		}
		return false;
	}
endif;

if(!function_exists('reycore__get_tinv_url')):
	function reycore__get_tinv_url(){
		if( function_exists('tinv_url_wishlist_default') ){
			return tinv_url_wishlist_default();
		}
		return false;
	}
endif;


if(!function_exists('reycore__get_rey_logo')):
	/**
	 * Get logo image
	 *
	 * @since 1.3.0
	 **/
	function reycore__get_rey_logo($theme = 'black')
	{
		return REY_CORE_URI . sprintf( 'assets/images/logo-simple-%s.svg', esc_attr($theme) );
	}
endif;


if(!function_exists('reycore__preg_grep_keys')):
	/**
	 * Preg Grep for array keys
	 *
	 * @since 1.5.3
	 **/
	function reycore__preg_grep_keys($pattern, $input, $flags = 0) {
		return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
	}
endif;
