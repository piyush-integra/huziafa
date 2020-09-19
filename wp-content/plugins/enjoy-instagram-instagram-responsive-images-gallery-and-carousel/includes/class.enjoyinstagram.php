<?php
/**
 * This main plugin class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // exit if call directly
}

final class EnjoyInstagram {

	/**
	 * Single plugin instance
	 * @since 9.0.0
	 * @var \EnjoyInstagram_Admin
	 */
	protected static $instance;

	/**
	 * Plugin users array
	 * @var array
	 */
	private $_users = [];

	/**
	 * Returns single instance of the class
	 *
	 * @return EnjoyInstagram
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
		$this->_load_required();
		$this->_users = (array) get_option( 'enjoy_instagram_options', array() );
		$this->_users = array_filter( $this->_users );

		// sync actions
		add_action( 'init', array( $this, 'schedule_sync' ), 1 );
	}

	/**
	 * Load required files
	 *
	 * @return void
	 * @since 11.0.0
	 */
	private function _load_required() {
		// common file
		require_once( 'class.enjoyinstagram-api-connection.php' );
		require_once( 'class.enjoyinstagram-db.php' );
		require_once( 'class.enjoyinstagram-shortcodes.php' );
		// widgets
		require_once( 'widgets/widgets.php' );
		require_once( 'widgets/widgets_grid.php' );

		// require admin class
		if ( is_admin() ) {
			include_once( 'tinymce/tinymce.php' );
			require_once( 'class.enjoyinstagram-admin.php' );
		}
	}

	/**
	 * Get the first logged user. Helper for free plugin
	 *
	 * @return array|false
	 */
	public function get_selected_user() {
		return count( $this->_users ) > 0 ? array_values( $this->_users )[0] : false;
	}

	/**
	 * Get the users array
	 *
	 * @return array
	 * @since 11.0.0
	 */
	public function get_users() {
		return $this->_users;
	}

	/**
	 * Get a single user by id
	 *
	 * @param string $id
	 *
	 * @return array|boolean
	 * @since 11.0.0
	 */
	public function get_user( $id ) {
		return isset( $this->_users[ $id ] ) ? $this->_users[ $id ] : false;
	}

	/**
	 * There is a business user?
	 *
	 * @return string|boolean
	 * @since 11.0.0
	 */
	public function has_business_user() {
		if ( empty( $this->_users ) ) {
			return false;
		}

		foreach ( $this->_users as $id => $data ) {
			if ( ! empty( $data['business'] ) ) {
				return $id;
			}
		}

		return false;
	}

	/**
	 * Add an user to main array
	 *
	 * @param string $id
	 * @param array $data
	 *
	 * @since 11.0.0
	 */
	public function add_user( $id, $data ) {
		$this->_users[ $id ] = $data;

		update_option( 'enjoy_instagram_options', $this->_users );
		// force sync
		delete_option( 'enjoyinstagram_sync_times' );
	}

	/**
	 * Remove an user
	 *
	 * @param string $id
	 *
	 * @return boolean
	 * @since 11.0.0
	 */
	public function remove_user( $id ) {
		unset( $this->_users[ $id ] );
		update_option( 'enjoy_instagram_options', $this->_users );

		EnjoyInstagram_DB()->delete_media_by_user( $id ); // delete also media and hashtags

		return true;
	}

	/**
	 * Schedule event sync. Use cron if active, otherwise use custom
	 *
	 * @param boolean $force If force the update. Valid only for latest
	 *
	 * @return void
	 * @since 9.1.0
	 */
	public function schedule_sync( $force = false ) {

		if ( empty( $this->_users ) || defined( 'DOING_AJAX' ) ) {
			return;
		};

		$times   = get_option( 'enjoyinstagram_sync_times', array() );
		$current = time();

		foreach ( $this->_users as $user => $user_data ) {
			$latest = isset( $times[ $user ] ) ? intval( $times[ $user ] ) : 0;
			if ( $current > ( $latest + DAY_IN_SECONDS ) ) {
				$this->sync_users_data( $user ); // sync also data
				$this->sync_media_event( $user, 'all' );
				// update time
				$times[ $user ] = $current;
			} elseif ( $force || ( $current > ( $latest + ( 5 * MINUTE_IN_SECONDS ) ) ) ) {
				$this->sync_media_event( $user, 'latest' );
				// update time
				$times[ $user ] = $current;
			}
		}

		update_option( 'enjoyinstagram_sync_times', $times );
	}

	/**
	 * Sync user data scheduled
	 *
	 * @param string $user
	 *
	 * @return void
	 * @since 10.0.1
	 */
	protected function sync_users_data( $user ) {

		if ( ! isset( $this->_users[ $user ] ) ) {
			return;
		}

		$user_data = $this->_users[ $user ];

		$api  = EnjoyInstagram_Api_Connection();
		$data = $api->get_user_profile( $user_data['id'], $user_data['access_token'], $user_data['business'] );


		if ( $data === false && ! empty( $api->last_error ) ) {
			enjoyinstagram_add_notice( $api->last_error, 'error' );
		} else {
			$this->_users[ $user ] = array_merge( $this->_users[ $user ], $data );
		}

		update_option( 'enjoy_instagram_options', $this->_users );
	}

	/**
	 * Sync user media event scheduled
	 *
	 * @param string $to_sync The media to sync (latest|all)
	 * @param string $user
	 *
	 * @return void
	 * @since 9.1.0
	 */
	public function sync_media_event( $user, $to_sync = 'latest' ) {
		if ( ! isset( $this->_users[ $user ] ) ) {
			return;
		}

		$this->_sync( $user, $to_sync );
	}

	/**
	 * Sync media
	 *
	 * @param string $user
	 * @param string $to_sync
	 * @param string $offset
	 *
	 * @return void
	 * @since 9.1.0
	 */
	private function _sync( $user, $to_sync = 'latest', $offset = '' ) {

		$user_data = ! empty( $this->_users[ $user ] ) ? $this->_users[ $user ] : false;
		if ( ! $user_data ) {
			return;
		}

		$api    = EnjoyInstagram_Api_Connection();
		$medias = $api->get_user_media( $user_data, 33, $offset );

		if ( empty( $medias['data'] ) && ! empty( $api->last_error ) ) {
			$error_msg = sprintf( __( 'An error occurred while syncing the media: %s', 'enjoyinstagram' ),
				$api->last_error );
			enjoyinstagram_add_notice( $error_msg, 'error' );

			return;
		}

		foreach ( $medias['data'] as $media ) {
			if ( empty( $media['image_id'] ) ) {
				continue;
			}

			$media_id = EnjoyInstagram_DB()->add_in_main_table( $media );
			if ( $media_id && ! empty( $media['tags'] ) ) {
				foreach ( $media['tags'] as $tag ) {
					EnjoyInstagram_DB()->add_in_hashtag_table( array(
						'image_id' => $media_id,
						'hashtag'  => esc_sql( $tag )
					) );
				}
			}
		}

		// continue sync
		if ( $to_sync == 'all' && ! empty( $medias['next'] ) ) {
			$this->_sync( $user, $to_sync, $medias['next'] );
		}
	}
}

/**
 * Unique access to instance of EnjoyInstagram class
 *
 * @return EnjoyInstagram
 * @since 9.0.0
 */
function EnjoyInstagram() {
	return EnjoyInstagram::get_instance();
}