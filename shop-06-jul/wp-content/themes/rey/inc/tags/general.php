<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if(!function_exists('rey__main_content_wrapper_start')):
	/**
	 * Main wrapper start
	 *
	 * @since 1.0.0
	 */
	function rey__main_content_wrapper_start(){

		// Container Classes
		$container_class = [];

		if( is_page_template('template-builder.php') ){
			$container_class[] = 'rey-pbTemplate';
		}

		if( is_page_template('template-builder-stretch.php') ){
			$container_class[] = 'rey-stretchPage';
		}

		?>

		<div class="rey-siteContainer <?php echo implode(' ', array_unique( apply_filters('rey/site_container/classes', $container_class ) ) ); ?>">
			<div class="rey-siteRow">

				<?php
				// Sidebar Left Position
				do_action('rey/get_sidebar', 'left'); ?>

				<main id="main" class="rey-siteMain <?php echo rey__site_main__class(); ?>">
		<?php
	}
endif;
add_action('rey/before_site_container', 'rey__main_content_wrapper_start', 500);


if(!function_exists('rey__main_content_wrapper_end')):
	/**
	 * Main wrapper markup end
	 *
	 * @since 1.0.0
	 */
	function rey__main_content_wrapper_end(){ ?>

				</main>
				<!-- .rey-siteMain -->

				<?php
				// Sidebar Right Position
				do_action('rey/get_sidebar', 'right'); ?>
			</div>
		</div>
		<!-- .rey-siteContainer -->
		<?php
	}
endif;
add_action('rey/after_site_container', 'rey__main_content_wrapper_end', 0);


if(!function_exists('rey__add_site_overlay')):
	/**
	 * Add site overlay
	 *
	 * @since 1.0.0
	 */
	function rey__add_site_overlay(){
		get_template_part('template-parts/misc/site-overlay');
	}
endif;
add_action('rey/after_site_wrapper_start', 'rey__add_site_overlay');


if(!function_exists('rey__add_titles')):
	/**
	 * Adds Titles into respective pages
	 *
	 * @since 1.0.0
	 **/
	function rey__add_titles()
	{
		if( ! apply_filters('rey/add_titles', true) ) {
			return;
		}
		if ( is_archive() ) {
			get_template_part( 'template-parts/titles/archive' );
		}
		elseif ( is_page() ) {
			get_template_part( 'template-parts/titles/page' );
		}
		elseif ( is_search() ) {
			get_template_part( 'template-parts/titles/search' );
		}
		elseif ( is_singular() && !is_page() ) {
			get_template_part( 'template-parts/titles/single' );
		}
	}
endif;
add_action('rey/content/title', 'rey__add_titles');


if(!function_exists('rey__post_list_markup')):
	/**
	 * Wrapper for wp pagination
	 *
	 * @param array $args
	 * @return string String of page links or array of page links.
	 * @since 1.0.0
	 */
	function rey__post_list_markup() {
		get_template_part( 'template-parts/content/post-list' );
	}
endif;
add_action('rey/post_list', 'rey__post_list_markup');


if(!function_exists('rey__pagination')):
	/**
	 * Wrapper for wp pagination
	 *
	 * @since 1.0.0
	 */
	function rey__pagination() {
		get_template_part( 'template-parts/misc/pagination-paged' );
	}
endif;
add_action('rey/post_list', 'rey__pagination', 50);


/**
 * Navigation markup css class addition
 * @since 1.0.0
 */
add_filter('navigation_markup_template' , function($template){
	return str_replace('<nav class="navigation', '<nav class="navigation rey-postNav', $template);
}, 10);


/**
 * Password form css class addition
 * @since 1.0.0
 */
add_filter('the_password_form' , function($html){
	return str_replace('type="submit"', 'type="submit" class="btn btn-primary"', $html);
}, 10);


if(!function_exists('rey__ctp_supports_blog')):
	/**
	 * Custom Post Type supports Rey Blog
	 *
	 * @since 1.5.0
	 **/
	function rey__ctp_supports_blog()
	{
		return post_type_supports( get_post_type(), 'rey-blog' );
	}
endif;


if(!function_exists('rey__site_main__class')):
	/**
	 * Site's main CSS class
	 * @since 1.0.0
	 */
	function rey__site_main__class($classes = []){

		if ( rey__is_blog_list() ) {

			$classes[] = '--is-bloglist';

			if( is_active_sidebar('main-sidebar') && rey__blog_should_display_sidebar() )
			{
				$classes[] = '--has-sidebar';
			}

			if( ! is_singular() )
			{
				foreach (['', '_tablet', '_mobile'] as $bp) {
					// Blog columns
					$classes['blog-columns' . $bp] = sprintf('blog--columns%s-%s', $bp, get_theme_mod('blog_columns' . $bp, '1'));
				}
			}
			else
			{
				if( is_singular('post') || ( is_singular() && rey__ctp_supports_blog() ) )
				{
					// Post width
					$classes['post-width'] = 'post-width--' . get_theme_mod('post_width', 'c');
				}
			}
		}

		return esc_attr( implode( ' ', array_map( 'sanitize_html_class', apply_filters('rey/content/site_main_class', $classes) ) ) );
	}
endif;


if(!function_exists('rey__general_check_main_sidebar')):
	/**
	 * Detect if sidebar is active
	 *
	 * @since 1.0.0
	 */
	function rey__general_check_main_sidebar($is_active_sidebar, $index){
		if( $index === 'main-sidebar' && is_page() ){
			return false;
		}
		return $is_active_sidebar;
	}
endif;
add_filter('is_active_sidebar', 'rey__general_check_main_sidebar',10, 2);


if(!function_exists('rey__get_sidebar_name')):
	/**
	 * Get sidebar name
	 *
	 * @since 1.0.0
	 **/
	function rey__get_sidebar_name()
	{
		return apply_filters('rey/sidebar_name', 'main-sidebar');
	}
endif;


if(!function_exists('rey__get_main_sidebar')):
	/**
	 * Detect if main sidebar is active
	 *
	 * @since 1.0.0
	 */
	function rey__get_main_sidebar($position)
	{
		if(
			rey__is_blog_list() &&
			is_active_sidebar('main-sidebar') &&
			rey__blog_should_display_sidebar($position)
		) {
			get_sidebar();
		}
	}
endif;
add_action('rey/get_sidebar', 'rey__get_main_sidebar');


if(!function_exists('rey__blog_should_display_sidebar')):
	/**
	 * Checks if the sidebar should display
	 *
	 * @since 1.0.0
	 */
	function rey__blog_should_display_sidebar( $position = '' ){

		$blog_post_option = get_theme_mod('blog_post_sidebar', 'disabled');
		$blog_option = get_theme_mod('blog_sidebar', 'right');

		// blog post
		if( is_singular() ){

			// make sure the sidebar is not disabled
			if( $blog_post_option !== 'disabled' ){
				// check if inherits from blog page
				if( $blog_post_option === 'inherit' && $blog_option !== 'disabled' ){
					if( $position ) {
						return $blog_option === $position;
					}
					else {
						return $blog_post_option !== 'disabled';
					}
				}
				// rely on what's selected making sure its not disabled
				else {
					if( $position ) {
						return $blog_post_option === $position;
					}
					else {
						return $blog_post_option !== 'disabled' && $blog_post_option !== 'inherit';
					}
				}
			}
		}
		// blog page
		else {
			// checks if sidebar is disabled
			if( !$position ){
				return $blog_option !== 'disabled';
			}
			// checks if the sidebar is enabled and positioned
			else {
				return $blog_option === $position;
			}
		}
	}
endif;


if(!function_exists('rey__general_post_classes')):
	/**
	 * Filter post classes
	 *
	 * @since 1.0.0
	 */
	function rey__general_post_classes($classes){

		if( rey__is_blog_list() ):
			// Animate post media
			if(
				!is_singular() &&
				get_post_format() != 'video' &&
				get_post_format() != 'audio' &&
				get_post_format() != 'gallery' &&
				get_theme_mod('blog_thumbnail_animation', true) ){
				$classes[] = 'rey-postMedia--animated';
			}

			// Enable hover line
			if( !is_singular() ){

				if( rey__config('animate_blog_items') ) {
					$classes[] = 'is-animated-entry';
				}
			}
			$classes[] = '--content-' . rey__postContent_type();

		endif;

		return $classes;
	}
endif;
add_filter('post_class', 'rey__general_post_classes');


/**
 * Excerpt Length
 *
 * @since 1.0.0
 */
add_filter( 'excerpt_length', function( $length ) {
	return get_theme_mod( 'blog_excerpt_length' , 55);
}, 999 );




/**
 * Load Polyfills
 *
 * @since 1.0.0
 */
add_action('wp_enqueue_scripts', function() {

	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	$load_polyfills = get_option( 'rey_load_polyfills', 'request' );

	if( $load_polyfills != 'never' ) {
		wp_enqueue_script( 'rey-polyfills', REY_THEME_URI . '/assets/js/lib/polyfills' . $suffix . '.js', [], REY_THEME_VERSION, false );
		wp_localize_script('rey-polyfills', 'reyPolyfills', [
			'url' => REY_THEME_URI,
			'css_vars' => [
				'iecp' => "/assets/js/lib/polyfill/ie11CustomProperties.js",
				'cvpn' => "/assets/js/lib/polyfill/css-vars-ponyfill.js",
			],
			'css_vars_use' => 'cvpn',
			'method' => esc_html($load_polyfills)
		]);
	}

});

/**
 * NO JS handling.
 *
 * @since 1.0.0
 */
function rey__no_js() {
	?>
	<script type="text/javascript">
		var c = document.body.className;
		c = c.replace(/rey-no-js/, 'rey-js');
		document.body.className = c;
	</script>
	<?php
}
add_action( 'rey/before_site_wrapper', 'rey__no_js' );


if(!function_exists('rey__body_class')):
	/**
	 * Filter Body Class
	 *
	 * @since 1.0.0
	 **/
	function rey__body_class($classes)
	{
		if (is_customize_preview()) {
			$classes[] = 'customizer-preview-mode';
		}

		if( get_theme_mod('site_preloader', true) && !(is_customize_preview() || (function_exists('reycore__elementor_edit_mode') && reycore__elementor_edit_mode())) ) {
			$classes[] = 'site-preloader--active';
		}

		$classes['site_navigation'] = 'rey-navDefault';
		$classes[] = 'rey-no-js';
		$classes['search_style'] = 'search-panel--' . get_theme_mod('header_search_style', 'wide');

		return $classes;
	}
endif;
add_filter('body_class', 'rey__body_class', 10);


if(!function_exists('rey__site_preloader_html')):
	/**
	 * Add site preloader
	 *
	 * @since 1.0.0
	 **/
	function rey__site_preloader_html()
	{
		if( get_theme_mod('site_preloader', true) && !(is_customize_preview() || (function_exists('reycore__elementor_edit_mode') && reycore__elementor_edit_mode())) ) {
			printf(
				'<div id="rey-site-preloader" class="rey-sitePreloader">%s</div>',
				apply_filters('rey/site_preloader_html', '<div class="rey-lineLoader"></div>')
			);
		}
	}
endif;
add_action( 'rey/before_site_wrapper', 'rey__site_preloader_html' );


if(!function_exists('rey__404page')):
	/**
	 * Add 404 page content
	 *
	 * @since 1.5.0
	 */
	function rey__404page() {
		get_template_part( 'template-parts/misc/page404' );
	}
endif;
add_action('rey/404page', 'rey__404page');


if(!function_exists('rey__implode_attributes')):
	/**
	 * Join attributes
	 *
	 * @since 1.3.0
	 **/
	function rey__implode_attributes( $attributes )
	{
		return implode(' ', array_map(
			function ($k, $v) {
				return sprintf('%s="%s"', esc_attr($k), esc_attr($v));
			},
			array_keys($attributes), $attributes
		));
	}
endif;


if(!function_exists('rey__render_attributes')):
/**
 * Render tag attributes
 *
 * @since 1.3.0
 **/
function rey__render_attributes($type = '', $attributes = [])
{
	if( empty($type) ){
		return;
	}

	switch($type):
		case "body":
			$attributes += apply_filters('rey/body/tag_attributes', []);
			break;
		case "header":
			$attributes += apply_filters('rey/header/tag_attributes', []);
			break;
		case "footer":
			$attributes += apply_filters('rey/footer/tag_attributes', []);
			break;
		case "sidebar":
			$attributes += apply_filters('rey/sidebar/tag_attributes', []);
			break;
		case "site-navigation":
			$attributes += apply_filters('rey/site-navigation/tag_attributes', []);
			break;

	endswitch;

	echo rey__implode_attributes( $attributes );
}
endif;

if(!function_exists('rey__page_templates')):
	function rey__page_templates() {
		return [
			'template-builder.php' => _x( 'Rey - Page Builder', 'Page Template', 'rey' ),
			'template-builder-stretch.php' => _x( 'Rey - Full Width', 'Page Template', 'rey' ),
			'template-compact.php' => _x( 'Rey - Compact Page', 'Page Template', 'rey' ),
			'template-multi-cols.php' => _x( 'Rey - Multi-columns Page', 'Page Template', 'rey' ),
		];
	}
endif;

if(!function_exists('rey__add_page_templates')):
	function rey__add_page_templates( $page_templates, $wp_theme, $post ) {
		$the_page_templates = rey__page_templates();
		return $the_page_templates + $page_templates;
	}
	add_filter( "theme_post_templates", 'rey__add_page_templates', 10, 4 );
	add_filter( "theme_page_templates", 'rey__add_page_templates', 10, 4 );
endif;

if(!function_exists('reycore__template_type_classes')):
	/**
	 * Filter classes
	 *
	 * @since 1.6.x
	 **/
	function reycore__template_type_classes($classes) {

		if( ! is_singular() ) {
			return $classes;
		}

		if ( is_page_template() ) {
			$classes['template_type'] = "--tpl";

			$template_slug  = get_page_template_slug();
			$template_parts = explode( '/', $template_slug );

			foreach ( $template_parts as $part ) {
				$classes['template_type'] = "--tpl-" . sanitize_html_class( str_replace( array( '.', '/' ), '-', basename( $part, '.php' ) ) );
			}
			$classes['template_type'] = "--tpl-" . sanitize_html_class( str_replace( '.', '-', $template_slug ) );
		} else {
			$classes['template_type'] = "--tpl-default";
		}

		return $classes;
	}
	add_filter('rey/site_content_classes', 'reycore__template_type_classes', 10);
endif;
