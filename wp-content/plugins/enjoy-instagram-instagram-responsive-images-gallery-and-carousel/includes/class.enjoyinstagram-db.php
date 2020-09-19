<?php
/**
 * This class handles DB connection and other DB stuff
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // exit if call directly
}

class EnjoyInstagram_DB {

	/**
	 * Main table name
	 * @var string
	 */
	protected $main_table = 'enjoy_instagram_media';

	/**
	 * Main table name
	 * @var string
	 */
	protected $hashtags_table = 'enjoy_instagram_hashtags';

	/**
	 * Main sync ajax action name
	 * @var string
	 */
	protected $import_ajax_action = 'enjoyinstagram_import_ajax';

	/**
	 * DB version
	 * @var string
	 */
	protected $db_version = '1.0.3';

	/**
	 * Single plugin instance
	 * @since 9.0.0
	 * @var \EnjoyInstagram_DB
	 */
	protected static $instance;

	/**
	 * Returns single instance of the class
	 *
	 * @return \EnjoyInstagram_DB
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
	public function __construct() {
		$this->init();
	}

	/**
	 * Init class variables
	 *
	 * @return void
	 * @since 9.1.0
	 */
	public function init() {
		global $wpdb;

		$this->main_table     = $wpdb->prefix . $this->main_table;
		$this->hashtags_table = $wpdb->prefix . $this->hashtags_table;

		// add tables
		$this->add_tables();
		// import data from old tables
		add_action( 'wp_ajax_' . $this->import_ajax_action, array( $this, 'import_media_ajax' ) );
		add_action( 'wp_ajax_nopriv_' . $this->import_ajax_action, array( $this, 'import_media_ajax' ) );
	}

	/**
	 * Add plugin tables
	 *
	 * @return void
	 * @since 9.0.0
	 */
	public function add_tables() {

		$installedDB = get_option( 'enjoy_instagram_installed_db_version', '0' );
		if ( version_compare( $installedDB, $this->db_version, '>=' ) ) {
			return;
		}

		global $wpdb;

		$sqls            = array();
		$charset_collate = $wpdb->get_charset_collate();

		$sqls['enjoy_instagram_media'] = "CREATE TABLE IF NOT EXISTS $this->main_table (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    image_id  varchar(255) DEFAULT '' NOT NULL,
                    image_ids  varchar(255) DEFAULT '' NOT NULL,
                    image_link varchar(255) DEFAULT '' NOT NULL,
                    image_url text DEFAULT '' NOT NULL,
                    thumbnail_url text DEFAULT '' NOT NULL,
                    user varchar(225) DEFAULT '' NOT NULL,
                    caption longtext DEFAULT '' NOT NULL,
                    likes mediumint(9) DEFAULT 0 NOT NULL,
                    moderate varchar(20) DEFAULT '' NOT NULL,
                    date bigint(20) DEFAULT 0 NOT NULL,
                    UNIQUE KEY id (id)
                    ) $charset_collate;";

		$sqls['enjoy_instagram_hashtags'] = "CREATE TABLE IF NOT EXISTS $this->hashtags_table (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    image_id bigint(20) DEFAULT 0 NOT NULL,
                    hashtag varchar(255) DEFAULT '' NOT NULL,
                    moderate varchar(20) DEFAULT '' NOT NULL,
                    UNIQUE KEY id (id)
                    ) $charset_collate;";

		if ( ! function_exists( 'dbDelta' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		}

		foreach ( $sqls as $sql ) {
			dbDelta( $sql );
		}

		switch ( $installedDB ) {
			case '1.0.0':
				$wpdb->query( "ALTER TABLE {$this->main_table} ADD date bigint(20) DEFAULT 0 NOT NULL" );
				delete_option( 'enjoyinstagram_sync_times' );
				break;
			case '1.0.2':
				$wpdb->query( "ALTER TABLE {$this->main_table} ADD thumbnail_url text DEFAULT '' NOT NULL AFTER image_url" );
				delete_option( 'enjoyinstagram_sync_times' );
				break;
		}

		update_option( 'enjoy_instagram_installed_db_version', $this->db_version );
	}

	public function reset() {
		global $wpdb;

		update_option('enjoy_instagram_installed_db_version', '0');
		$wpdb->query( "DROP TABLE {$this->main_table}" );
		$wpdb->query( "DROP TABLE {$this->hashtags_table}" );
		update_option( 'enjoyinstagram_sync_times', array() );
	}


	/**
	 * Build where clause for queries
	 *
	 * @param array $where
	 *
	 * @return string
	 * @since 9.0.0
	 * @author Francesco Licandro
	 */
	public static function build_where( $where ) {
		$return = '1=1';

		if ( empty( $where ) ) {
			return $return;
		}

		foreach ( $where as $condition ) {
			! isset( $condition['compare'] ) && $condition['compare'] = '=';
			! isset( $condition['relation'] ) && $condition['relation'] = 'AND';

			$value = $condition['value'];
			if ( is_array( $value ) ) {
				$value = implode( "','", $value );
				$value = "('{$value}')";
			} elseif ( is_string( $value ) ) {
				$value = "'{$value}'";
			}

			$return .= " {$condition['relation']} {$condition['key']} {$condition['compare']} $value";
		}

		return $return;
	}

	/**
	 * Get media approved
	 *
	 * @param array $where
	 *
	 * @return mixed
	 * @since 9.0.0
	 */
	public function get_media( $where = array() ) {
		global $wpdb;
		$where = self::build_where( $where );

		return $wpdb->get_results( "SELECT * FROM {$this->main_table} WHERE {$where} ORDER BY date DESC", ARRAY_A );
	}

	/**
	 * Get media approved
	 *
	 * @param array $where
	 *
	 * @return mixed
	 * @since 9.0.0
	 */
	public function get_media_by_hashtag( $where = array() ) {
		global $wpdb;
		$where = self::build_where( $where );

		$ids = $wpdb->get_col( "SELECT DISTINCT image_id FROM {$this->hashtags_table} WHERE {$where}" );

		if ( empty( $ids ) ) {
			return array();
		}

		$ids = implode( ',', $ids );

		return $wpdb->get_results( "SELECT * FROM {$this->main_table} WHERE id IN ({$ids}) ORDER BY date DESC",
			ARRAY_A );
	}

	/**
	 * Moderate an image
	 *
	 * @param string $type
	 * @param array $args
	 *
	 * @return boolean
	 * @author Francesco Licandro
	 * @since 9.0.0
	 */
	public function moderate_image( $type, $args ) {
		global $wpdb;

		/**
		 * @var string $moderate
		 * @var string $value
		 */
		extract( $args );

		if ( empty( $media_id ) ) {
			return false;
		}

		if ( $type == 'hashtag' ) {
			$wpdb->query( "UPDATE {$this->hashtags_table} SET moderate = '{$moderate}' WHERE hashtag = '{$value}' AND image_id = {$media_id}" );
		} else {
			$wpdb->query( "UPDATE {$this->main_table} SET moderate = '{$moderate}' WHERE id = {$media_id}" );
		}

		return true;
	}

	/**
	 * Get media for shortcode by user
	 *
	 * @param string $user
	 * @param array $hashtags
	 * @param boolean|string $moderate
	 * @param boolean|integer $limit
	 *
	 * @return array
	 * @author Francesco Licandro
	 * @since 9.0.0
	 */
	public function get_shortcode_media_user( $user, $hashtags = array(), $moderate = false, $limit = false ) {
		global $wpdb;

		( $limit === false ) && $limit = get_option( 'enjoyinstagram_images_captured', 20 );

		$where = "user = '{$user}'";
		$moderate !== false && $where .= " AND moderate = '{$moderate}'";

		if ( ! empty( $hashtags ) ) {
			$hashtags = implode( "','", $hashtags );
			$hashtags = str_replace( '#', '', $hashtags ); // remove char #
			$ids      = $wpdb->get_col( "SELECT DISTINCT image_id FROM {$this->hashtags_table} WHERE hashtag IN ('{$hashtags}')" );
			if ( empty( $ids ) ) {
				return array();
			}

			$ids   = implode( "','", $ids );
			$where .= " AND id IN ('{$ids}')";
		}

		$result = $wpdb->get_results( "SELECT * FROM {$this->main_table} WHERE {$where} ORDER BY date DESC LIMIT {$limit}",
			ARRAY_A );

		return $result;
	}

	/**
	 * Get images id by hashtags
	 *
	 * @param array $hashtags
	 * @param boolean $moderate
	 * @param int|bool $limit
	 *
	 * @return array
	 * @author Francesco Licandro
	 * @since 9.0.0
	 */
	public function get_shortcode_media_hashtag( $hashtags, $moderate = false, $limit = false ) {
		global $wpdb;

		( $limit === false ) && $limit = get_option( 'enjoyinstagram_images_captured', 20 );

		if ( empty( $hashtags ) ) {
			return array();
		}

		$hashtags       = implode( "','", $hashtags );
		$hashtags       = str_replace( '#', '', $hashtags ); // remove char #
		$where_moderate = ( $moderate !== false ) ? " AND moderate = '{$moderate}'" : '';
		$ids            = $wpdb->get_col( "SELECT DISTINCT image_id FROM {$this->hashtags_table} WHERE hashtag IN ('{$hashtags}') {$where_moderate}" );
		if ( empty( $ids ) ) {
			return array();
		}

		$ids    = implode( "','", $ids );
		$result = $wpdb->get_results( "SELECT * FROM {$this->main_table} WHERE id IN ('{$ids}') ORDER BY date DESC LIMIT {$limit}",
			ARRAY_A );

		// double check for url expired
		/*        foreach( $result as $key => $media ) {
					if( ! $media->user ) {
						$response = wp_remote_get( $media->image_url );
						if( wp_remote_retrieve_response_code( $response ) == '403' ){
							unset( $result[$key] );
							$this->delete_media( $media->id );
						}
					}
				}*/

		return $result;
	}

	/**
	 * Add in main table
	 *
	 * @param array $data
	 *
	 * @return boolean|integer
	 * @since 9.0.0
	 * @author Francesco Licandro
	 */
	public function add_in_main_table( $data ) {
		global $wpdb;

		if ( empty( $data['image_id'] ) ) {
			return false;
		}

		$id         = $wpdb->get_var( "SELECT id FROM {$this->main_table} WHERE image_id = '{$data['image_id']}'" );
		$table_cols = array(
			'image_id'      => '%s',
			'image_link'    => '%s',
			'image_url'     => '%s',
			'thumbnail_url' => '%s',
			'user'          => '%s',
			'caption'       => '%s',
			'likes'         => '%d',
			'moderate'      => '%s',
			'date'          => '%d'
		);

		$insert = array_intersect_key( $data, $table_cols );
		$format = array_merge( $insert, array_intersect_key( $table_cols, $data ) );

		if ( empty( $id ) ) {
			$wpdb->insert( $this->main_table, $insert, $format );
			$id = $wpdb->insert_id;
		} else { // update
			$wpdb->update( $this->main_table, $insert, array( 'id' => $id ), $format, array( 'id' => '%d' ) );
		}

		return $id;
	}

	/**
	 * Delete a media
	 *
	 * @param string|integer $id
	 *
	 * @return void
	 * @since 10.0.1
	 */
	public function delete_media( $id ) {
		global $wpdb;

		$id = intval( $id );

		$wpdb->query( "DELETE FROM {$this->main_table} WHERE id = {$id}" );
		$wpdb->query( "DELETE FROM {$this->hashtags_table} WHERE image_id = {$id}" );
	}

	/**
	 * Delete media by user
	 *
	 * @param string $user_id
	 *
	 * @return void
	 * @since 11.0.0
	 */
	public function delete_media_by_user( $user_id ) {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$this->main_table} WHERE user = %s", $user_id ) );
		$wpdb->query( "DELETE FROM {$this->hashtags_table} WHERE image_id NOT IN ( SELECT id FROM {$this->main_table} )" );
	}

	/**
	 * Clean all media related data
	 *
	 * @return void
	 * @since 5.0.0
	 */
	public function clear_all() {
		global $wpdb;

		$wpdb->query( "DELETE FROM {$this->main_table}" );
		$wpdb->query( "DELETE FROM {$this->hashtags_table}" );
	}

	/**
	 * Add in hashtag table
	 *
	 * @param array $data
	 *
	 * @return boolean|integer
	 * @since 9.0.0
	 * @author Francesco Licandro
	 */
	public function add_in_hashtag_table( $data ) {
		global $wpdb;

		if ( empty( $data['image_id'] ) || empty( $data['hashtag'] ) ) {
			return false;
		}

		$id = $wpdb->get_var( "SELECT id FROM {$this->hashtags_table} WHERE image_id = '{$data['image_id']}' AND hashtag = '{$data['hashtag']}'" );
		if ( $id ) {
			return false;
		}

		$table_cols = array(
			'image_id' => '%s',
			'hashtag'  => '%s',
			'moderate' => '%s'
		);

		$format = array_intersect_key( $table_cols, $data );
		$insert = array_intersect_key( $data, $table_cols );

		$wpdb->insert( $this->hashtags_table, $insert, $format );
		$id = $wpdb->insert_id;

		return $id;
	}

	/**
	 * Check if given hashtag exists in table
	 *
	 * @param string $hashtag
	 *
	 * @return boolean
	 * @since 9.1.0
	 */
	public function hashtag_exists( $hashtag ) {
		global $wpdb;
		$hashtag = esc_sql( $hashtag );
		$hashtag = $wpdb->get_var( "SELECT hashtag FROM {$this->hashtags_table} WHERE hashtag = '{$hashtag}' LIMIT 1" );

		return ! ! $hashtag;
	}

	/**
	 * Check if given user has media
	 *
	 * @param string $user
	 *
	 * @return boolean
	 * @since 9.1.0
	 */
	public function user_has_media( $user ) {
		global $wpdb;
		$user = $wpdb->get_var( "SELECT user FROM {$this->main_table} WHERE user = '{$user}' LIMIT 1" );

		return ! ! $user;
	}

	/**
	 * Do action if is plugin update
	 *
	 * @return void
	 * @since 9.1.0
	 */
	protected function do_action_for_update() {
		global $wpdb;

		if ( $wpdb->query( "SHOW TABLES LIKE '{$wpdb->prefix}enjoy_instagram_table'" ) ) {
			// init option
			update_option( 'enjoyinstagram_sync_times', array(
				'all'    => time() + DAY_IN_SECONDS,
				'latest' => time() + DAY_IN_SECONDS
			) );

			$this->_import();
		}
	}

	/**
	 * Sync media Ajax
	 *
	 * @return void
	 * @since 9.1.0
	 */
	public function import_media_ajax() {
		// Don't lock up other requests while processing
		session_write_close();

		if ( ! isset( $_REQUEST['offset'] ) || ! EnjoyInstagram()->verify_communication( $_REQUEST['nonce'],
				$_REQUEST['action'] ) ) {
			wp_die();
		}

		$this->_import( $_REQUEST['offset'] );

		wp_die();
	}

	/**
	 * Update media
	 *
	 * @param int $offset
	 *
	 * @return void
	 * @since 9.1.0
	 */
	private function _import( $offset = 0 ) {

		global $wpdb;

		$offset      = intval( $offset );
		$offset_cond = $offset ? " OFFSET {$offset}" : '';
		$medias      = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}enjoy_instagram_table WHERE image_id NOT LIKE '%http%' AND timestamp > '1527811200' ORDER BY timestamp DESC LIMIT 10 {$offset_cond}",
			ARRAY_A );

		if ( empty( $medias ) ) {
			delete_option( 'enjoyinstagram_sync_times' );

			return;
		}

		foreach ( $medias as $media ) {

			$moderate = $wpdb->get_var( "SELECT id FROM {$wpdb->prefix}enjoy_instagram_moderate_accepted WHERE image_id = '{$media['image_id']}' LIMIT 1" );
			$moderate && $moderate = 'approved';
			if ( ! $moderate ) {
				$moderate = $wpdb->get_var( "SELECT id FROM {$wpdb->prefix}enjoy_instagram_moderate_rejected WHERE image_id = '{$media['image_id']}' LIMIT 1" );
				$moderate && $moderate = 'rejected';
			}

			$media_id = $this->add_in_main_table( array(
				'image_id'   => trim( $media['image_id'] ),
				'image_link' => $media['image_link'],
				'image_url'  => $media['image_url'],
				'user'       => $media['user'],
				'caption'    => str_replace( "\"", "&quot;", $media['caption'] ),
				'likes'      => intval( $media['likes'] ),
				'moderate'   => $moderate,
				'date'       => isset( $media['timestamp'] ) ? intval( $media['timestamp'] ) : 0
			) );

			if ( ! $media['user'] && $media['hashtag'] ) {
				$this->add_in_hashtag_table( array(
					'image_id' => $media_id,
					'hashtag'  => $media['hashtag'],
					'moderate' => $moderate
				) );
			}
		}

		// build remote url
		$url = admin_url( 'admin-ajax.php' );
		$url = add_query_arg( array(
			'action' => $this->import_ajax_action,
			'nonce'  => substr( wp_hash( $this->import_ajax_action . '|' . $this->ajax_key, 'nonce' ), - 12, 10 ),
			'offset' => $offset + 10,
		), $url );

		wp_remote_post( $url );
	}

	public function stats() {
		global $wpdb;

		return array(
			'media'   => array(
				'count'     => $wpdb->get_var( "SELECT COUNT(*) FROM {$this->main_table}" ),
				'structure' => $wpdb->get_var( "SHOW CREATE TABLE {$this->main_table}", 1 )
			),
			'hashtag' => array(
				'count'     => $wpdb->get_var( "SELECT COUNT(*) FROM {$this->hashtags_table}" ),
				'structure' => $wpdb->get_var( "SHOW CREATE TABLE {$this->hashtags_table}", 1 )
			)
		);
	}
}

/**
 * Unique access to instance of EnjoyInstagram_DB class
 *
 * @return \EnjoyInstagram_DB
 * @since 9.0.0
 */
function EnjoyInstagram_DB() {
	return EnjoyInstagram_DB::get_instance();
}

EnjoyInstagram_DB();
