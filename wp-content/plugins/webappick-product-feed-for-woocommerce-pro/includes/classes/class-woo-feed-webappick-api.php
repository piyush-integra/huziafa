<?php
/**
 * WooCommerce Product Feed Plugin Uses Tracker
 * Uses Webappick Insights for tracking
 * @since 3.1.41
 * @version 1.0.2
 */
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'WooFeedWebAppickAPI' ) ) {
	/**
	 * Class WooFeedWebAppickAPI
	 */
	final class WooFeedWebAppickAPI {

		/**
		 * Singleton instance
		 * @var WooFeedWebAppickAPI
		 */
		protected static $instance;

		/**
		 * @var WebAppick\AppServices\Client
		 */
		protected $client = null;

		/**
		 * @var WebAppick\AppServices\Insights
		 */
		protected $insights = null;

		/**
		 * Promotions Class Instance
		 * @var WebAppick\AppServices\Promotions
		 */
		public $promotion = null;

		/**
		 * Plugin License Manager
		 * @var WebAppick\AppServices\License
		 */
		protected $license = null;

		/**
		 * Plugin Updater
		 * @var WebAppick\AppServices\Updater
		 */
		protected $updater = null;

		/**
		 * Initialize
		 * @return WooFeedWebAppickAPI
		 */
		public static function getInstance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Class constructor
		 *
		 * @return void
		 * @since 1.0.0
		 *
		 */
		private function __construct() {
			if ( ! class_exists( 'WebAppick\AppServices\Client' ) ) {
				/** @noinspection PhpIncludeInspection */
				require_once WOO_FEED_LIBS_PATH . 'WebAppick/AppServices/Client.php';
			}
			// Load Client
			$this->client = new WebAppick\AppServices\Client( 'dec06622-980f-4674-8b08-72e23cc9e70f', 'WooCommerce Product Feed Pro', WOO_FEED_PRO_FILE );
			// Load
			$this->insights  = $this->client->insights(); // Plugin Insights
			$this->license   = $this->client->license(); // Plugin License Checker
			$this->updater   = $this->client->updater(); // Plugin Update Checker
			$this->promotion = $this->client->promotions(); // Promo offers

			$this->license->set_product_data( [
				'yearly'   => [
					'label'    => __( 'Yearly', 'woo-feed-pro' ),
					'products' => [
						45660 => __( 'Single Site Yearly', 'woo-feed-pro' ),
						45659 => __( 'Two Sites Yearly', 'woo-feed-pro' ),
						45658 => __( 'Five Sites Yearly', 'woo-feed-pro' ),
					],
				],
				'lifetime' => [
					'label'    => __( 'Lifetime', 'woo-feed-pro' ),
					'products' => [
						63687 => __( 'Single Site Lifetime', 'woo-feed-pro' ),
						63686 => __( 'Two Sites Lifetime', 'woo-feed-pro' ),
						63685 => __( 'Five Sites Lifetime', 'woo-feed-pro' ),
					],
				],
			] );
			
			// Setup
			$this->license->add_settings_page( [
				'menu_title'  => __( 'License', 'woo-feed' ),
				'type'        => 'submenu',
				'parent_slug' => 'webappick-manage-feeds',
			] );
			
			$this->promotion->set_source( 'https://api.bitbucket.org/2.0/snippets/woofeed/RLbyop/files/woo-feed-notice.json' );

			// Initialize
			$this->insightInit();
			$this->license->init();
			$this->updater->init();
			$this->promotion->init();

			// Housekeeping
			// migrate to new license from API Manager
			add_action( 'woo_feed_plugin_updated', [ $this, '__migrate_wc_am_license' ], 10 );
			add_filter( 'woo_feed_dropdown_exclude_option_name_pattern', [ $this, '__exclude_license_option' ], 10, 1 );
			// Filter updater api data
			add_filter( 'WebAppick_' . $this->client->getSlug() . '_plugin_api_info', [
				$this,
				'__plugin_api_info'
			], 10, 1 );
		}

		/**
		 * Exclude License data from option dropdown
		 *
		 * @param $exclude
		 *
		 * @return array
		 */
		public function __exclude_license_option( $exclude ) {
			$exclude[] = 'WebAppick_%_manage_license';

			return $exclude;
		}

		/**
		 * Cloning is forbidden.
		 * @since 1.0.2
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning is forbidden.', 'woo-feed' ), '1.0.2' );
		}

		/**
		 * Check license status
		 * @return bool
		 */
		public function isActive() {
			return $this->license->is_valid();
		}

		/**
		 * Initialize Insights
		 * @return void
		 */
		private function insightInit() {
			global $wpdb;
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name LIKE %s;", "wf_feed_%" ), 'ARRAY_A' ); // phpcs:ignore
			if ( ! is_array( $result ) ) {
				$result = [];
			}
			$catCount = wp_count_terms( 'product_cat', [
				'hide_empty' => false,
				'parent'     => 0,
			] );
			if ( is_wp_error( $catCount ) ) {
				$catCount = 0;
			}
			$settings = woo_feed_get_options( 'all' );
			/**
			 * @TODO count products by type
			 * @see wc_get_product_types();
			 */
			$this->insights->add_extra( [
				'products'                  => $this->insights->get_post_count( 'product' ),
				'variations'                => $this->insights->get_post_count( 'product_variation' ),
				'batch_limit'               => $settings['per_batch'],
				'settings'                  => $settings,
				'feed_configs'              => wp_json_encode( $result ),
				'using_currency_conversion' => ( ! empty( get_option( 'woo_feed_currency_api_code', '' ) ) ? 'Yes' : 'No' ),
				'product_cat_num'           => $catCount,
			] );
			$projectSlug = $this->client->getSlug();
			add_filter( $projectSlug . '_what_tracked', [ $this, 'data_we_collect' ], 10, 1 );
			add_filter( "WebAppick_{$projectSlug}_Support_Ticket_Recipient_Email", function () {
				return 'sales@webappick.com';
			}, 10 );
			add_filter( "WebAppick_{$projectSlug}_Support_Ticket_Email_Template", [
				$this,
				'supportTicketTemplate'
			], 10 );
			add_filter( "WebAppick_{$projectSlug}_Support_Request_Ajax_Success_Response", [
				$this,
				'supportResponse'
			], 10 );
			add_filter( "WebAppick_{$projectSlug}_Support_Request_Ajax_Error_Response", [
				$this,
				'supportErrorResponse'
			], 10 );
			add_filter( "WebAppick_{$projectSlug}_Support_Page_URL", function () {
				return 'https://webappick.com/support/';
			}, 10 );
			$this->insights->init();
		}

		/**
		 * Generate Support Ticket Email Template
		 * @return string
		 */
		public function supportTicketTemplate() {
			// dynamic variable format __INPUT_NAME__
			$licenseData = sprintf( '<br>API Key : %s', $this->license->get_key() );
			/** @noinspection HtmlUnknownTarget */
			$template = sprintf( '<div style="margin: 10px auto;"><p>Website : <a href="__WEBSITE__">__WEBSITE__</a><br>Plugin : %s (v%s)%s</p></div>', $this->client->getName(), $this->client->getProjectVersion(), $licenseData );
			$template .= '<div style="margin: 10px auto;"><hr></div>';
			$template .= '<div style="margin: 10px auto;"><h3>__SUBJECT__</h3></div>';
			$template .= '<div style="margin: 10px auto;">__MESSAGE__</div>';
			$template .= '<div style="margin: 10px auto;"><hr></div>';
			$template .= sprintf(
				'<div style="margin: 50px auto 10px auto;"><p style="font-size: 12px;color: #009688">%s</p></div>',
				'Message Processed With WebAppick Service Library (v.' . $this->client->getClientVersion() . ')'
			);

			return $template;
		}

		/**
		 * Generate Support Ticket Ajax Response
		 * @return string
		 */
		public function supportResponse() {
			$response        = '';
			$response        .= sprintf( '<h3>%s</h3>', esc_html__( 'Thank you -- Support Ticket Submitted.', 'woo-feed' ) );
			$ticketSubmitted = esc_html__( 'Your ticket has been successfully submitted.', 'woo-feed' );
			$twenty4Hours    = sprintf( '<strong>%s</strong>', esc_html__( '24 hours', 'woo-feed' ) );
			/* translators: %s: Approx. time to response after ticket submission. */
			$notification = sprintf( esc_html__( 'You will receive an email notification from "support@webappick.com" in your inbox within %s.', 'woo-feed' ), $twenty4Hours );
			$followUp     = esc_html__( 'Please Follow the email and WebAppick Support Team will get back with you shortly.', 'woo-feed' );
			$response     .= sprintf( '<p>%s %s %s</p>', $ticketSubmitted, $notification, $followUp );
			$docLink      = sprintf( '<a class="button button-primary" href="https://webappick.helpscoutdocs.com/" target="_blank"><span class="dashicons dashicons-media-document" aria-hidden="true"></span> %s</a>', esc_html__( 'Documentation', 'woo-feed' ) );
			$vidLink      = sprintf( '<a class="button button-primary" href="http://bit.ly/2u6giNz" target="_blank"><span class="dashicons dashicons-video-alt3" aria-hidden="true"></span> %s</a>', esc_html__( 'Video Tutorials', 'woo-feed' ) );
			$response     .= sprintf( '<p>%s %s</p>', $docLink, $vidLink );
			$response     .= '<br><br><br>';
			$toc          = sprintf( '<a href="https://webappick.com/terms-and-conditions/" target="_blank">%s</a>', esc_html__( 'Terms & Conditions', 'woo-feed' ) );
			$pp           = sprintf( '<a href="https://webappick.com/privacy-policy/" target="_blank">%s</a>', esc_html__( 'Privacy Policy', 'woo-feed' ) );
			/* translators: 1: Link to the Trams And Condition Page, 2: Link to the Privacy Policy Page */
			$policy   = sprintf( esc_html__( 'Please read our %1$s and %2$s', 'woo-feed' ), $toc, $pp );
			$response .= sprintf( '<p style="font-size: 12px;">%s</p>', $policy );

			return $response;
		}

		/**
		 * Set Error Response Message For Support Ticket Request
		 * @return string
		 */
		public function supportErrorResponse() {
			return sprintf(
				'<div class="mui-error"><p>%s</p><p>%s</p><br><br><p style="font-size: 12px;">%s</p></div>',
				esc_html__( 'Something Went Wrong. Please Try The Support Ticket Form On Our Website.', 'woo-feed' ),
				sprintf( '<a class="button button-primary" href="https://webappick.com/support/" target="_blank">%s</a>', esc_html__( 'Get Support', 'woo-feed' ) ),
				esc_html__( 'Support Ticket form will open in new tab in 5 seconds.', 'woo-feed' )
			);
		}

		/**
		 * Set Data Collection description for the tracker
		 *
		 * @param $data
		 *
		 * @return array
		 */
		public function data_we_collect( $data ) {
			$data = array_merge( $data, [
				esc_html__( 'Number of products in your site.', 'woo-feed' ),
				esc_html__( 'Number of product categories in your site.', 'woo-feed' ),
				esc_html__( 'Feed Configuration.', 'woo-feed' ),
				esc_html__( 'Site name, language and url.', 'woo-feed' ),
				esc_html__( 'Number of active and inactive plugins.', 'woo-feed' ),
				esc_html__( 'Your name and email address.', 'woo-feed' ),
			] );

			return $data;
		}

		/**
		 * Get Tracker Data Collection Description Array
		 * @return array
		 */
		public function get_data_collection_description() {
			return $this->insights->get_data_collection_description();
		}

		/**
		 * Add Missing Info for plugin details after fetching through api
		 *
		 * @param $data
		 *
		 * @return array
		 */
		public function __plugin_api_info( $data ) {
			// house keeping
			if ( isset( $data['homepage'], $data['author'] ) && false === strpos( $data['author'], '<a' ) ) {
				/** @noinspection HtmlUnknownTarget */
				$data['author'] = sprintf( '<a href="%s">%s</a>', $data['homepage'], $data['author'] );
			}
			if ( ! isset( $data['contributors'] ) ) {
				$data['contributors'] = [
					'wahid0003' => [
						'profile'      => 'https://webappick.com/',
						'avatar'       => 'https://secure.gravatar.com/avatar/3fd731922ac65c5a1ba045b28a7bfb21?s=96&d=monsterid&r=g',
						'display_name' => 'Md Ohidul Islam',
					],
				];
			}
			$sections = [ 'description', 'installation', 'faq', 'screenshots', 'changelog', 'reviews', 'other_notes' ];
			foreach ( $sections as $section ) {
				if ( isset( $data['sections'][ $section ] ) && empty( $data['sections'][ $section ] ) ) {
					unset( $data['sections'][ $section ] );
				}
			}

			return $data;
		}

		/**
		 * Update Tracker OptIn
		 *
		 * @param bool $override optional. ignore last send datetime settings if true.
		 *
		 * @see Insights::send_tracking_data()
		 * @return void
		 */
		public function trackerOptIn( $override = false ) {
			$this->insights->optIn( $override );
		}

		/**
		 * Update Tracker OptOut
		 * @return void
		 */
		public function trackerOptOut() {
			$this->insights->optOut();
		}

		/**
		 * Check if tracking is enable
		 * @return bool
		 */
		public function is_tracking_allowed() {
			return $this->insights->is_tracking_allowed();
		}

		/**
		 * Migrate to New License Client Format from WooCommerce API Manager Data
		 *
		 * @param bool $override
		 *
		 * @return bool
		 */
		public function __migrate_wc_am_license( $override = false ) {
			// if license isn't valid try to migrate license data from wc-am.
			if ( $this->license->is_valid() && ! $override ) {
				return false;
			}

			return $this->license->migrate_license_from_wc_am( $override );
		}
	}
}
// End of file class-woo-feed-webappick-api.php
