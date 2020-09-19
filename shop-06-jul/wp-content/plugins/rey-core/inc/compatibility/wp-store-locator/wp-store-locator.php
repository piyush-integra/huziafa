<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists( 'WP_Store_locator' ) && !class_exists('ReyCore_Compatibility__WpStoreLocator') ):
	/**
	 * Wp Store Locator Plugin Compatibility
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Compatibility__WpStoreLocator
	{
		private static $_instance = null;

		private function __construct()
		{
			add_action( 'wp_enqueue_scripts', [ $this, 'load_styles' ] );
			add_action( 'wp', [ $this, 'determine_dealer_button_location' ] );
			add_action( 'acf/init', [ $this, 'add_wpsl_settings' ] );
		}

		public function load_styles(){
            wp_enqueue_style( 'reycore-wpsl-styles', REY_CORE_COMPATIBILITY_URI . basename(__DIR__) . '/style.css', ['wpsl-styles'], WPSL_VERSION_NUM );
		}

		public function add_wpsl_settings(){

			acf_add_local_field(array(
				'key'        => 'field_wpsl_title',
				'type'       => 'message',
				'parent'     => 'group_5c990a758cfda',
				'message'    => __('<h1>WP Store Locator</h1>', 'rey-core'),
				'menu_order' => 300,
			));

			acf_add_local_field(array(
				'key'          => 'field_wpsl_enable_button',
				'name'         => 'wpsl_enable_button',
				'label'        => esc_html__('Enable Button in Product Page', 'rey-core'),
				'type'         => 'true_false',
				'instructions' => esc_html__('Enable or disable a "Find Dealer" button in the Product page.', 'rey-core'),
				'default_value' => 1,
				'ui' => 1,
				'parent'       => 'group_5c990a758cfda',
				'menu_order'   => 300,
			));

			acf_add_local_field(array(
				'key'             => 'field_wpsl_button_text',
				'name'            => 'wpsl_button_text',
				'label'           => esc_html__('Button Text', 'rey-core'),
				'type'            => 'text',
				'default_value'   => esc_html__('FIND DEALER NEAR YOU', 'rey-core'),
				'placeholder'     => 'eg: FIND A DEALER',
				'maxlength'       => 100,
				'wrapper'         => array(
					'width'          => '',
					'class'          => 'rey-decrease-list-size',
					'id'             => '',
				),
				'parent'          => 'group_5c990a758cfda',
				'menu_order'      => 300,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_wpsl_enable_button',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
			));

			acf_add_local_field(array(
				'key'             => 'field_wpsl_button_url',
				'name'            => 'wpsl_button_url',
				'label'           => esc_html__('Button URL', 'rey-core'),
				'type'            => 'link',
				'allow_null' => 1,
				'wrapper'         => array(
					'width'          => '',
					'class'          => 'rey-decrease-list-size',
					'id'             => '',
				),
				'parent'          => 'group_5c990a758cfda',
				'menu_order'      => 300,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_wpsl_enable_button',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
			));


		}

		function determine_dealer_button_location(){
			$priority = 25;

			if(get_theme_mod('single_skin', 'default') === 'compact' ){
				$priority = 26;
			}

			add_action( 'woocommerce_single_product_summary', [ $this, 'add_dealer_button' ], $priority );
		}

		public function add_dealer_button(){
			if( class_exists('ACF') && get_field('wpsl_enable_button', REY_CORE_THEME_NAME) ):

				$link_attributes = '';
				$url = get_field('wpsl_button_url', REY_CORE_THEME_NAME);

				if( $url ){
					$link_attributes .= "href='{$url['url']}'";
					$link_attributes .= "title='{$url['title']}' ";
					$link_attributes .= "target='{$url['target']}' ";
				}

				ob_start();
				?>

				<div class="rey-wpStoreLocator">
					<a <?php echo $link_attributes; ?> class="rey-wpsl-btn btn btn-primary">
						<i class="fa fa-map-marker" aria-hidden="true"></i>
						<span><?php echo get_field('wpsl_button_text', REY_CORE_THEME_NAME) ?></span>
					</a>
				</div>

				<?php
				return apply_filters('reycore/wp_store_locator/button', ob_get_contents());

			endif;
		}

		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
	}

	ReyCore_Compatibility__WpStoreLocator::getInstance();
endif;
