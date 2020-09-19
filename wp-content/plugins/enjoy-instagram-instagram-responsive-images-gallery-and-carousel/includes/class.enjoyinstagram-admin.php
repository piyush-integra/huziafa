<?php
/**
 * This class handles all admin actions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // exit if call directly
}

class EnjoyInstagram_Admin {

	/**
	 * Single plugin instance
	 * @since 9.0.0
	 * @var \EnjoyInstagram_Admin
	 */
	protected static $instance;

	/**
	 * Plugin options page name
	 * @var string
	 */
	protected $_options_page = 'enjoyinstagram_plugin_options';

	/**
	 * Plugin options page name
	 * @var array
	 */
	protected $_tabs = [];

	/**
	 * Plugin options page name
	 * @var array
	 */
	protected $_plugin_options = [];

	/**
	 * Returns single instance of the class
	 *
	 * @return \EnjoyInstagram_Admin
	 * @since 1.0.0
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Construct
	 *
	 * @return void
	 */
	private function __construct() {
		$this->init();
		add_action( 'admin_menu', array( $this, 'add_admin_menus' ) );
		add_action( 'admin_notices', array( $this, 'print_notices' ) );
		// register default options group
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles_scripts' ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'admin_footer_scripts' ), PHP_INT_MAX );
		add_filter( 'plugin_action_links_' . plugin_basename( ENJOYINSTAGRAM_DIR . '/' . basename( ENJOYINSTAGRAM_FILE ) ),
			array( $this, 'settings_link' ) );
		// add/remove user
		add_action( 'admin_init', array( $this, 'add_user' ), 1 );
		add_action( 'admin_init', array( $this, 'remove_user' ), 1 );
		add_action( 'admin_init', array( $this, 'reset_database' ), - 1 );
	}

	/**
	 * Check if current page is a plugin admin page
	 *
	 * @return boolean
	 * @since 11.0.0
	 */
	public function is_admin_page() {
		return isset( $_GET['page'] ) && $_GET['page'] == $this->_options_page;
	}

	/**
	 * Print notices in plugin admin pages
	 *
	 * @return void
	 * @since 11.0.0
	 */
	public function print_notices() {
		if ( ! $this->is_admin_page() ) {
			return;
		}

		$notices = enjoyinstagram_get_notices();

		if ( ! $notices ) {
			return;
		}

		foreach ( $notices as $notice ) {
			?>
            <div class="updated settings-error <?php echo $notice['type'] ?> is-dismissible">
                <p><?php echo $notice['message'] ?></p>
            </div>
			<?php
		}
	}

	/**
	 * Init admin class variables
	 *
	 * @return void
	 * @since 9.0.0
	 */
	protected function init() {
		// init tabs
		$this->_tabs = apply_filters( 'enjoyinstagram_plugin_admin_tabs', array(
			'enjoyinstagram_general_settings'  => __( 'Profile',
				'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ),
			'enjoyinstagram_advanced_settings' => __( 'Settings',
				'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ),
			'diagnostic'                       => __( 'Diagnostic', 'enjoyinstagram' ),
		) );

		if ( file_exists( ENJOYINSTAGRAM_DIR . 'plugin-options/options.php' ) ) {
			$this->_plugin_options = include( ENJOYINSTAGRAM_DIR . 'plugin-options/options.php' );
		}
	}

	/**
	 * Build an admin url
	 *
	 * @param string $tab
	 * @param array $params
	 *
	 * @return string
	 * @since 4.0.0
	 */
	public function build_admin_url( $tab, $params = array() ) {
		$params_string = '';
		foreach ( $params as $key => $value ) {
			$params_string .= '&' . $key . '=' . $value;
		}

		return admin_url( "options-general.php?page={$this->_options_page}&tab={$tab}{$params_string}" );
	}

	/**
	 * Enqueue plugin styles and scripts
	 *
	 * @return void
	 * @since 9.0.0
	 */
	public function admin_styles_scripts() {
		global $wp_scripts;

		wp_register_style( 'enjoyinstagram_settings', ENJOYINSTAGRAM_ASSETS_URL . '/css/enjoyinstagram_settings.css',
			array(), ENJOYINSTAGRAM_VERSION );

		if ( isset( $_GET['page'] ) && $_GET['page'] == $this->_options_page ) {
			wp_enqueue_style( 'enjoyinstagram_settings' );
			wp_enqueue_script( "jquery-ui-core" );
			wp_enqueue_script( 'jquery-ui-tooltip' );
			wp_enqueue_style( 'plugin_name-admin-ui-css',
				'//ajax.googleapis.com/ajax/libs/jqueryui/' . $wp_scripts->registered['jquery-ui-core']->ver . '/themes/smoothness/jquery-ui.css',
				false,
				ENJOYINSTAGRAM_VERSION,
				false );
		}
	}

	/**
	 * Enqueue admin footer scripts
	 *
	 * @since 5.0.0
	 */
	public function admin_footer_scripts() {
		if ( isset( $_GET['page'] ) && $_GET['page'] == $this->_options_page ) {
			?>
            <script type="text/javascript">
              jQuery(function () {
                jQuery(".enjoy-instagram-help").tooltip({
                  content: function () {
                    return jQuery(this).prop('title');
                  }
                });
              })
            </script>
			<?php
		}
	}

	/**
	 * Add admin menu under Settings
	 *
	 * @return void
	 * @since 9.0.0
	 */
	public function add_admin_menus() {
		add_options_page(
			__( 'Enjoy Plugin for Instagram', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ),
			__( 'Enjoy Plugin for Instagram', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ),
			'manage_options',
			$this->_options_page,
			array( $this, 'output_options_page' ) );
	}

	/**
	 * Add plugin settings link
	 *
	 * @param string $links
	 *
	 * @return string
	 * @since 9.0.0
	 */
	public function settings_link( $links ) {
		$links[] = '<a href="options-general.php?page=' . $this->_options_page . '">' . __( 'Settings',
				'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ) . '</a>';
		$links[] = '<a href="widgets.php">' . __( 'Widgets',
				'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ) . '</a>';
		$links[] = '<a href="http://www.mediabeta.com/enjoy-instagram/">' . __( 'Premium Version',
				'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ) . '</a>';

		return $links;
	}

	/**
	 * Register settings
	 *
	 * @return void
	 * @since 9.0.0
	 */
	public function register_settings() {
		if ( empty( $this->_plugin_options ) ) {
			return;
		}

		foreach ( $this->_plugin_options as $group => $options ) {
			foreach ( $options as $option_id => $default ) {
				register_setting( $group, $option_id );
			}
		}
	}

	/**
	 * Get plugin admin tabs
	 *
	 * @sine 9.0.0
	 * @return array
	 */
	public function get_tabs() {
		return $this->_tabs;
	}

	/**
	 * Get current active tab or return the first one
	 *
	 * @return string
	 * @since 9.0.0
	 */
	public function get_active_tab() {
		if ( ! is_array( $this->_tabs ) ) {
			return '';
		}

		$c = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
		reset( $this->_tabs );

		return isset( $this->_tabs[ $c ] ) ? $c : key( $this->_tabs );
	}

	/**
	 * Get admin tab url
	 *
	 * @param string $tab
	 *
	 * @return string
	 * @since 9.0.0
	 */
	public function get_tab_url( $tab ) {
		return add_query_arg( array( 'page' => $this->_options_page, 'tab' => $tab ),
			admin_url( 'options-general.php' ) );
	}

	/**
	 * Output plugin options page
	 *
	 * @return void
	 * @since 9.0.0
	 */
	public function output_options_page() {
		$tabs       = $this->get_tabs();
		$active_tab = $this->get_active_tab();

		include( ENJOYINSTAGRAM_TEMPLATE_PATH . '/admin/settings.php' );
	}

	/**
	 * Manage Instagram response and add a new user
	 *
	 * @return void
	 * @since 4.0.0
	 */
	public function add_user() {
		if ( ( isset( $_GET['page'] ) && $_GET['page'] != $this->_options_page ) || ! isset( $_GET['access_token'] ) || ! isset( $_GET['api'] ) ) {
			return;
		}

		$is_business  = $_GET['api'] === 'graph';
		$access_token = sanitize_text_field( $_GET['access_token'] );
		$data         = EnjoyInstagram_Api_Connection()->get_user_accounts( $access_token, $is_business );

		if ( $data === false ) {
			$message = EnjoyInstagram_Api_Connection()->last_error;
			enjoyinstagram_add_notice( $message, 'error' );
		} else if ( empty( $data ) ) {
			$message = __( 'No account found for this user', 'enjoyinstagram' );
			enjoyinstagram_add_notice( $message, 'error' );
		} else {
			$profile                 = $data[0]; // one account allowed for the free version
			$profile['access_token'] = $access_token;
			EnjoyInstagram()->add_user( $profile['username'], $profile );
			enjoyinstagram_add_notice(
				sprintf( __( 'User %s successfully added', 'enjoyinstagram' ), $profile['username'] ),
				'notice'
			);
		}

		// redirect to main settings page
		wp_redirect( $this->build_admin_url( 'enjoyinstagram_general_settings' ) );
		exit;
	}

	/**
	 * Remove and user from plugin
	 *
	 * @return void
	 * @since 4.0.0
	 */
	public function remove_user() {

		if ( ! isset( $_REQUEST['action'] ) || $_REQUEST['action'] != 'enjoyinstagram_remove_user' ) {
			return;
		}

		update_option( 'enjoy_instagram_options', array() );
		delete_option( 'enjoyinstagram_sync_times' );
		EnjoyInstagram_DB()->clear_all();

		// redirect to main settings page
		wp_redirect( $this->build_admin_url( 'enjoyinstagram_general_settings' ) );
		exit;
	}

	/**
	 * Re-install plugin database
	 *
	 * @return void;
	 * @author Giulio Ganci
	 * @since 11.1.0
	 */
	public function reset_database() {

		$action = 'reset-db';

		if ( ! $this->is_admin_page() || ! isset( $_GET['action'] ) || $_GET['action'] !== $action || ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'],
				$action ) ) {
			return;
		}

		try {
			EnjoyInstagram_DB()->reset();
			enjoyinstagram_add_notice( __( 'Database initialized', 'enjoyinstagram' ), 'success' );
		} catch ( Exception $e ) {
			enjoyinstagram_add_notice( sprintf( __( 'Database inizialization error: %s', 'enjoyinstagram' ),
				$e->getMessage() ), 'error' );
		}

		wp_redirect( $this->build_admin_url( 'diagnostic' ) );
		exit;
	}

	/**
	 * Get Instagram login url
	 *
	 * @return string
	 * @author Francesco Licandro
	 * @since 9.0.0
	 */
	public function get_instagram_login_url() {
		$return_url = admin_url( "options-general.php?page={$this->_options_page}" );

		return add_query_arg( array(
			'app_id'        => ENJOYINSTAGRAM_APP_ID,
			'redirect_uri'  => ENJOYINSTAGRAM_BASIC_DISPLAY_API_REDIRECT,
			'response_type' => 'code',
			'scope'         => 'user_profile,user_media',
			'state'         => base64_encode( $return_url )
		), 'https://api.instagram.com/oauth/authorize' );
	}

	/**
	 * Create url to connect with FB
	 *
	 * @return string Connection url
	 */
	public function get_facebook_connect_url() {
		$admin_url = admin_url( 'options-general.php?page=' . $this->_options_page );

		$auth_url = add_query_arg( array(
			'response_type' => 'token',
			'client_id'     => ENJOYINSTAGRAM_FB_APP_ID,
			'redirect_uri'  => ENJOYINSTAGRAM_GRAPH_API_REDIRECT,
			'scope'         => 'manage_pages,instagram_basic',
		), 'https://www.facebook.com/dialog/oauth' );

		$auth_url .= '&state=' . base64_encode( $admin_url );

		return $auth_url;
	}
}

/**
 * Unique access to instance of EnjoyInstagram_Admin class
 *
 * @return \EnjoyInstagram_Admin
 * @since 9.0.0
 */
function EnjoyInstagram_Admin() {
	return EnjoyInstagram_Admin::get_instance();
}

EnjoyInstagram_Admin();