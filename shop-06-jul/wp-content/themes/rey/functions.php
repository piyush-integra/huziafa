<?php
/**
 * REY functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package rey
 */

/**
 * Global Variables
 *
 * Defining global variables to make
 * usage easier.
 */
define('REY_THEME_DIR', get_template_directory());
define('REY_THEME_PARENT_DIR', get_stylesheet_directory());
define('REY_THEME_URI', get_template_directory_uri());
define('REY_THEME_PLACEHOLDER', REY_THEME_URI . '/assets/images/placeholder.png');
define('REY_THEME_NAME', 'rey');
define('REY_THEME_CORE_SLUG', 'rey-core');
if( !defined('REY_DEV_MODE') ){
	define( 'REY_THEME_VERSION', '1.6.9' );
}
else {
	// cache buster
	define( 'REY_THEME_VERSION', rand(100, 99999) );
}

// Minimum required versions
define( 'REY_THEME_REQUIRED_WP_VERSION', '4.7' );
define( 'REY_THEME_REQUIRED_PHP_VERSION', '5.4.0' );

/**
 * Disable theme if PHP 5.4 not supported & WP Version is 4.7+
 */
function rey__check_theme(){

	/**
	 * WP Version Check.
	 */
	if ( version_compare( get_bloginfo( 'version' ), REY_THEME_REQUIRED_WP_VERSION, '<' ) ) {
		// Theme not activated info message
        add_action( 'admin_notices', 'rey__wp_version_admin_notice' );
        function rey__wp_version_admin_notice() {
            ?>
            <div class="notice notice-error">
                <?php printf( esc_html__( 'This theme requires a minimum WordPress version of %s. Your version is s%. Your previous theme has been restored.', 'rey' ), REY_THEME_REQUIRED_WP_VERSION, get_bloginfo( 'version' ) ); ?>
            </div>
            <?php
        }
		// Switch back to previous theme
        switch_theme( get_option( 'theme_switched' ) );
		return false;
	}

    /**
	 * PHP Version Check.
	 */
    if ( version_compare( PHP_VERSION, REY_THEME_REQUIRED_PHP_VERSION, '<' ) ) :
        // Theme not activated info message
        add_action( 'admin_notices', 'rey__php_version_admin_notice' );
        function rey__php_version_admin_notice() {
            ?>
            <div class="notice notice-error">
                <?php printf( esc_html__( 'This theme requires a minimum PHP version of %s. Your version is s%. Your previous theme has been restored.', 'rey' ), REY_THEME_REQUIRED_PHP_VERSION, PHP_VERSION ); ?>
            </div>
            <?php
        }
        // Switch back to previous theme
        switch_theme( get_option( 'theme_switched' ) );
		return false;
    endif;
}
add_action( 'after_switch_theme', 'rey__check_theme' );


/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
add_action( 'after_setup_theme', function() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on components, use a find and replace
	 * to change 'rey' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'rey', REY_THEME_DIR . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 1440, 9999 , true );
	add_image_size( 'rey-standard-large', 1024, 9999 );
	add_image_size( 'rey-ratio-16-9', 1440, 810, true ); // height = 1440 x 0.5625


	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'main-menu' => esc_html__( 'Main Menu', 'rey' ),
		'footer-menu' => esc_html__( 'Footer Menu', 'rey' ),
	));

	/**
	 * Add support for core custom logo.
	 */
	add_theme_support( 'custom-logo', array(
		'height'      => 200,
		'width'       => 200,
		'flex-width'  => true,
		'flex-height' => true,
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	// add support for post formats
	add_theme_support('post-formats', ['gallery', 'image', 'video', 'audio', 'link', 'quote', 'status']);

	// Gutenberg Editor
	add_theme_support( 'align-wide' );

	/*
	 * This theme styles the visual editor to resemble the theme style,
	 * specifically font, colors, and column width.
	  */
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor' . (defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min') . '.css' );

	/**
	 * Load admin theme assets
	 */
	add_action('admin_enqueue_scripts', function() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Scripts
		wp_enqueue_script( 'masonry' );
		wp_enqueue_script( 'rey-admin-scripts', REY_THEME_URI . '/assets/js/rey-admin' . $suffix . '.js', ['jquery', 'masonry' ], REY_THEME_VERSION, true );
		wp_localize_script('rey-admin-scripts', 'reyAdminParams', apply_filters('rey/admin_script_params', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		]));

		// Styles
		wp_enqueue_style('rey-admin-styles', REY_THEME_URI . '/assets/css/rey-admin' . $suffix . '.css', false, REY_THEME_VERSION);
	});

	/**
	 * Load main theme assets
	 */
	add_action('wp_enqueue_scripts', function() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Enqueue Scripts
		wp_enqueue_script( 'imagesloaded' );
		wp_enqueue_script( 'masonry' );
		wp_enqueue_script( 'scroll-out', REY_THEME_URI . '/assets/js/lib/scroll-out' . $suffix . '.js', ['jquery'], '2.2.3', true );

		if( !wp_script_is( 'slick', 'enqueued' ) && !wp_script_is( 'jquery-slick', 'enqueued' ) ) {
			wp_enqueue_script( 'jquery-slick', REY_THEME_URI . '/assets/js/lib/slick' . $suffix . '.js', ['jquery'], '1.9.0', true );
		}

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

		// Main script
		wp_enqueue_script( 'rey-script', REY_THEME_URI . '/assets/js/rey' . $suffix . '.js', ['jquery', 'imagesloaded', 'masonry', 'wp-util' ], REY_THEME_VERSION, true );
		wp_localize_script('rey-script', 'reyParams', apply_filters('rey/main_script_params', [
			'icons_path' => esc_url( rey__svg_sprite_path() ),
			'theme_js_params' => [
				'menu_prevent_delays' => false,
				'menu_hover_overlay' => true,
				'menu_hover_timer' => 400,
				'menu_items_hover_timer' => 120,
			]
		]));

		// Main Styles
		wp_enqueue_style('rey-wp-style', get_stylesheet_directory_uri() . '/style' . $suffix . '.css', false, REY_THEME_VERSION);

		// Rey Styles
		$rey_styles = apply_filters(
			'rey_enqueue_styles',
			[
				'rey-blog'      => [
					'src'     => REY_THEME_URI . '/assets/css/rey-blog' . $suffix . '.css',
					'deps'    => ['rey-wp-style'],
					'version' => REY_THEME_VERSION,
				],
			]
		);

		foreach($rey_styles as $handle => $style ){
			wp_enqueue_style($handle, $style['src'], $style['deps'], $style['version']);
		}
	});

});

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
add_action( 'after_setup_theme', function() {
	$GLOBALS['content_width'] = $content_width = apply_filters( 'rey/content/width', 1440 );
}, 0 );


/**
 * Register widget area.
 */
add_action( 'widgets_init', function() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'rey' ),
		'id'            => 'main-sidebar',
		'description'   => esc_html__('This sidebar will be visible on the pages with default template option.' , 'rey'),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
} );

/**
 * Load Core
 */
require_once REY_THEME_DIR . '/inc/core/core.php';
