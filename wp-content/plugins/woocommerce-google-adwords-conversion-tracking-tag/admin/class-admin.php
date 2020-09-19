<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WgactAdmin {

    public static $ip;

	public static function init() {

		// add the admin options page
		add_action( 'admin_menu', 'WgactAdmin::wgact_plugin_admin_add_page', 99 );

		// install a settings page in the admin console
		add_action( 'admin_init', 'WgactAdmin::wgact_plugin_admin_init' );

		// add a settings link on the plugins page
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'WgactAdmin::wgact_settings_link' );

		// Load textdomain
		add_action( 'init', 'WgactAdmin::load_plugin_textdomain' );
	}

	// add the admin options page
	public static function wgact_plugin_admin_add_page() {
		//add_options_page('WGACT Plugin Page', 'WGACT Plugin Menu', 'manage_options', 'wgact', array($this, 'wgact_plugin_options_page'));
		add_submenu_page(
			'woocommerce',
			esc_html__(
				'Google Ads Conversion Tracking',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			esc_html__(
				'Google Ads Conversion Tracking',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			'manage_options',
			'wgact',
            'WgactAdmin::wgact_plugin_options_page'
		);
	}

	// add the admin settings and such
	public static function wgact_plugin_admin_init() {

		//register_setting( 'wgact_plugin_options_settings_fields', 'wgact_plugin_options', 'wgact_plugin_options_validate');
		register_setting(
		        'wgact_plugin_options_settings_fields',
                'wgact_plugin_options',
                'WgactAdmin::wgact_plugin_options_validate'
        );
		//error_log('after register setting');

		add_settings_section(
		        'wgact_plugin_main',
                esc_html__(
                        'Main Settings',
                        'woocommerce-google-adwords-conversion-tracking-tag'
                ),
			    'WgactAdmin::wgact_plugin_section_main',
                'wgact'
        );

		// add the field for the conversion id
		add_settings_field(
		        'wgact_plugin_conversion_id',
                esc_html__(
                        'Conversion ID',
                        'woocommerce-google-adwords-conversion-tracking-tag'
                ),
			    'WgactAdmin::wgact_plugin_setting_conversion_id',
                'wgact',
                'wgact_plugin_main'
        );

		// ad the field for the conversion label
		add_settings_field(
			'wgact_plugin_conversion_label',
			esc_html__(
				'Conversion Label',
				'woocommerce-google-adwords-conversion-tracking-tag'
            ),
			'WgactAdmin::wgact_plugin_setting_conversion_label',
			'wgact',
			'wgact_plugin_main'
		);

		// add fields for the order total logic
		add_settings_field(
			'wgact_plugin_order_total_logic',
			esc_html__(
				'Order Total Logic',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			'WgactAdmin::wgact_plugin_setting_order_total_logic',
			'wgact',
			'wgact_plugin_main'
		);

		// add fields for the gtag insertion
		add_settings_field(
			'wgact_plugin_gtag',
			esc_html__(
				'gtag Deactivation',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			'WgactAdmin::wgact_plugin_setting_gtag_deactivation',
			'wgact',
			'wgact_plugin_main'
		);

		// add new section for cart data
		add_settings_section(
			'wgact_plugin_add_cart_data',
			esc_html__(
				'Add Cart Data',
				'woocommerce-google-adwords-conversion-tracking-tag'
            ) . ' (<span style="color:#ff0000">beta</span>)',
			'WgactAdmin::wgact_plugin_section_add_cart_data',
			'wgact'
		);

		// add fields for cart data
		add_settings_field(
			'wgact_plugin_add_cart_data',
			esc_html__(
				'Activation',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			'WgactAdmin::wgact_plugin_setting_add_cart_data',
			'wgact',
			'wgact_plugin_add_cart_data'
		);

		// add the field for the aw_merchant_id
		add_settings_field(
            'wgact_plugin_aw_merchant_id',
            esc_html__(
                    'aw_merchant_id',
                    'woocommerce-google-adwords-conversion-tracking-tag'
            ),
            'WgactAdmin::wgact_plugin_setting_aw_merchant_id',
            'wgact',
            'wgact_plugin_add_cart_data'
        );

		// add the field for the aw_feed_country
		add_settings_field(
            'wgact_plugin_aw_feed_country',
            esc_html__(
                    'aw_feed_country',
                    'woocommerce-google-adwords-conversion-tracking-tag'
            ),
			'WgactAdmin::wgact_plugin_setting_aw_feed_country',
            'wgact',
            'wgact_plugin_add_cart_data'
        );

		// add the field for the aw_feed_language
		add_settings_field(
            'wgact_plugin_aw_feed_language',
            esc_html__(
                    'aw_feed_language',
                    'woocommerce-google-adwords-conversion-tracking-tag'
            ),
			'WgactAdmin::wgact_plugin_setting_aw_feed_language',
            'wgact',
            'wgact_plugin_add_cart_data'
        );

		// add fields for the product identifier
		add_settings_field(
			'wgact_plugin_option_product_identifier',
			esc_html__(
				'Product Identifier',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			'WgactAdmin::wgact_plugin_option_product_identifier',
			'wgact',
			'wgact_plugin_add_cart_data'
		);
	}

	// adds a link on the plugins page for the wgdr settings
	public static function wgact_settings_link( $links ) {

		$mylinks = array(
			'<a href="' . admin_url( 'admin.php?page=wgact' ) . '">Settings</a>',
		);
		return array_merge( $links, $mylinks );
	}

	// Load text domain function
	public static function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-google-adwords-conversion-tracking-tag', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	// display the admin options page
	public static function wgact_plugin_options_page() {

		?>

		<br>
		<div style="width:980px; float: left; margin: 5px">
			<div style="float:left; margin: 5px; margin-right:20px; width:750px">
				<div style="background: #0073aa; padding: 10px; font-weight: bold; color: white; border-radius: 2px">
					<?php esc_html_e( 'Google Ads Conversion Tracking Settings', 'woocommerce-google-adwords-conversion-tracking-tag' ) ?>
				</div>
				<form action="options.php" method="post">
					<?php settings_fields( 'wgact_plugin_options_settings_fields' ); ?>
					<?php do_settings_sections( 'wgact' ); ?>
					<br>
					<table class="form-table" style="margin: 10px">
						<tr>
							<th scope="row" style="white-space: nowrap">
								<input name="Submit" type="submit" value="<?php esc_attr_e( 'Save Changes' ); ?>"
								       class="button button-primary"/>
							</th>
						</tr>
					</table>
				</form>

				<br>
				<div
					style="background: #0073aa; padding: 10px; font-weight: bold; color: white; margin-bottom: 20px; border-radius: 2px">
					<span>
						<?php esc_html_e( 'Profit Driven Marketing by Wolf+Bär', 'woocommerce-google-adwords-conversion-tracking-tag' ) ?>
					</span>
					<span style="float: right;">
						<a href="https://wolfundbaer.ch/"
						   target="_blank" style="color: white">
							<?php esc_html_e( 'Visit us here: https://wolfundbaer.ch', 'woocommerce-google-adwords-conversion-tracking-tag' ) ?>
						</a>
					</span>
				</div>
			</div>
			<div style="float: left; margin: 5px">
				<a href="https://wordpress.org/plugins/woocommerce-google-dynamic-retargeting-tag/" target="_blank">
					<img src="<?php echo( plugins_url( '../images/wgdr-icon-256x256.png', __FILE__ ) ) ?>" width="150px"
					     height="150px">
				</a>
			</div>
			<div style="float: left; margin: 5px">
				<a href="https://wordpress.org/plugins/woocommerce-google-adwords-conversion-tracking-tag/"
				   target="_blank">
					<img src="<?php echo( plugins_url( '../images/wgact-icon-256x256.png', __FILE__ ) ) ?>" width="150px"
					     height="150px">
				</a>
			</div>
		</div>
		<?php
	}

	public static function wgact_plugin_section_main() {
		// do nothing
	}

	public static function wgact_plugin_section_add_cart_data() {

		_e( 'Find out more about this wonderful new feature: ', 'woocommerce-google-adwords-conversion-tracking-tag');
		echo '<a href="https://support.google.com/google-ads/answer/9028254" target="_blank">https://support.google.com/google-ads/answer/9028254</a><br>';
		_e( 'At the moment we are testing this feature. It might go into a PRO version of this plugin in the future.', 'woocommerce-google-adwords-conversion-tracking-tag');
	}

	public static function wgact_plugin_setting_conversion_id() {
		$options = get_option( 'wgact_plugin_options' );
		echo "<input id='wgact_plugin_conversion_id' name='wgact_plugin_options[conversion_id]' size='40' type='text' value='{$options['conversion_id']}' />";
		echo '<br><br>';
		_e('The conversion ID looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag');
		echo '&nbsp;<i>123456789</i>';
		echo '<p>';
		_e('Watch a video that explains how to find the conversion ID: ', 'woocommerce-google-adwords-conversion-tracking-tag');
		echo '<a href="https://www.youtube.com/watch?v=p9gY3JSrNHU" target="_blank">https://www.youtube.com/watch?v=p9gY3JSrNHU</a>';
	}

	public static function wgact_plugin_setting_conversion_label() {
		$options = get_option( 'wgact_plugin_options' );
		echo "<input id='wgact_plugin_conversion_label' name='wgact_plugin_options[conversion_label]' size='40' type='text' value='{$options['conversion_label']}' />";
		echo '<br><br>';
		_e('The conversion Label looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag');
		echo '&nbsp;<i>Xt19CO3axGAX0vg6X3gM</i>';
		echo '<p>';
		_e('Watch a video that explains how to find the conversion ID: ', 'woocommerce-google-adwords-conversion-tracking-tag');
		echo '<a href="https://www.youtube.com/watch?v=p9gY3JSrNHU" target="_blank">https://www.youtube.com/watch?v=p9gY3JSrNHU</a>';
	}

	public static function wgact_plugin_setting_order_total_logic() {
		$options = get_option( 'wgact_plugin_options' );
		?>
		<input type='radio' id='wgact_plugin_option_product_identifier_0' name='wgact_plugin_options[order_total_logic]'
		       value='0'  <?php echo( checked( 0, $options['order_total_logic'], false ) ) ?> ><?php _e( 'Use order_subtotal: Doesn\'t include tax and shipping (default)', 'woocommerce-google-adwords-conversion-tracking-tag' ) ?>
		<br>
		<input type='radio' id='wgact_plugin_option_product_identifier_1' name='wgact_plugin_options[order_total_logic]'
		       value='1'  <?php echo( checked( 1, $options['order_total_logic'], false ) ) ?> ><?php _e( 'Use order_total: Includes tax and shipping', 'woocommerce-google-adwords-conversion-tracking-tag' ) ?>
		<br><br>
		<?php _e( 'This is the order total amount reported back to Google Ads', 'woocommerce-google-adwords-conversion-tracking-tag' ) ?>
		<?php
	}

	public static function wgact_plugin_setting_gtag_deactivation() {
		$options = get_option( 'wgact_plugin_options' );
		?>
		<input type='checkbox' id='wgact_plugin_option_gtag_deactivation' name='wgact_plugin_options[gtag_deactivation]'
		       value='1' <?php checked( $options['gtag_deactivation'] ); ?> />
		<?php
		echo( esc_html__( 'Disable gtag.js insertion if another plugin is inserting it already.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
	}

	public static function wgact_plugin_setting_add_cart_data() {
		$options = get_option( 'wgact_plugin_options' );
		?>
		<input type='checkbox' id='wgact_plugin_add_cart_data' name='wgact_plugin_options[add_cart_data]' size='40' value='1' <?php echo( checked( 1, $options['add_cart_data'], true ) ) ?> >
		<?php
		_e( 'Add the cart data to the conversion event', 'woocommerce-google-adwords-conversion-tracking-tag' );
	}

	public static function wgact_plugin_setting_aw_merchant_id() {
		$options = get_option( 'wgact_plugin_options' );
		echo "<input type='text' id='wgact_plugin_aw_merchant_id' name='wgact_plugin_options[aw_merchant_id]' size='40' value='{$options['aw_merchant_id']}' />";
		echo '<br><br>Enter the ID of your Google Merchant Center account.';
	}

	public static function wgact_plugin_setting_aw_feed_country() {

		echo '<b>' . self::get_visitor_country() . '</b>&nbsp;';
//		echo '<br>' . 'get_external_ip_address: ' . WC_Geolocation::get_external_ip_address();
//		echo '<br>' . 'get_ip_address: ' . WC_Geolocation::get_ip_address();
//		echo '<p>' . 'geolocate_ip: ' . '<br>';
//		echo print_r(WC_Geolocation::geolocate_ip());
//		echo '<p>' . 'WC_Geolocation::geolocate_ip(WC_Geolocation::get_external_ip_address()): ' . '<br>';
//		echo print_r(WC_Geolocation::geolocate_ip(WC_Geolocation::get_external_ip_address()));
		echo '<br><br>Currently the plugin automatically detects the location of the visitor for this setting. In most, if not all, cases this will work fine. Please let us know if you have a use case where you need another output: <a href="mailto:support@wolfundbaer.ch">support@wolfundbaer.ch</a>';
	}

	// dupe in pixel
	public static function get_visitor_country(){

		if ( self::isLocalhost() ){
//	        error_log('check external ip');
			self::$ip = WC_Geolocation::get_external_ip_address();
		} else {
//		    error_log('check regular ip');
			self::$ip = WC_Geolocation::get_ip_address();
		}

		$location = WC_Geolocation::geolocate_ip(self::$ip);

//	    error_log ('ip: ' . self::$ip);
//	    error_log ('country: ' . $location['country']);
		return $location['country'];
	}

    // dupe in pixel
	public static function isLocalhost() {
		return in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);
	}

	public static function wgact_plugin_setting_aw_feed_language() {
		echo '<b>' . self::get_gmc_language() . '</b>';
		echo "<br><br>The plugin will use the WordPress default language for this setting. If the shop uses translations, in theory we could also use the visitors locale. But, if that language is  not set up in the Google Merchant Center we might run into issues. If you need more options here let us know:  <a href=\"mailto:support@wolfundbaer.ch\">support@wolfundbaer.ch</a>";
	}

	// dupe in pixel
	public static function get_gmc_language(){
		return strtoupper(substr( get_locale(), 0, 2 ));
	}

	public static function wgact_plugin_option_product_identifier() {
		$options = get_option( 'wgact_plugin_options' );
		?>
		<input type='radio' id='wgact_plugin_option_product_identifier_0' name='wgact_plugin_options[product_identifier]'
		       value='0' <?php echo( checked( 0, $options['product_identifier'], false ) ) ?>/><?php _e( 'post id (default)', 'woocommerce-google-adwords-conversion-tracking-tag' ) ?><br>

		<input type='radio' id='wgact_plugin_option_product_identifier_1' name='wgact_plugin_options[product_identifier]'
		       value='1' <?php echo( checked( 1, $options['product_identifier'], false ) ) ?>/><?php _e( 'post id with woocommerce_gpf_ prefix *', 'woocommerce-google-adwords-conversion-tracking-tag' ) ?><br>

		<input type='radio' id='wgact_plugin_option_product_identifier_1' name='wgact_plugin_options[product_identifier]'
		       value='2' <?php echo( checked( 2, $options['product_identifier'], false ) ) ?>/><?php _e( 'SKU', 'woocommerce-google-adwords-conversion-tracking-tag' ) ?><br><br>

		<?php echo( esc_html__( 'Choose a product identifier.', 'woocommerce-google-adwords-conversion-tracking-tag' ) ); ?><br><br>
		<?php _e( '* This is for users of the <a href="https://woocommerce.com/products/google-product-feed/" target="_blank">WooCommerce Google Product Feed Plugin</a>', 'woocommerce-google-adwords-conversion-tracking-tag' ); ?>


		<?php
	}

	// validate our options
	public static function wgact_plugin_options_validate( $input ) {

		// Create our array for storing the validated options
		$output = $input;

		// validate and sanitize conversion_id

		$needles_cid = array( 'AW-', '"',);
		$replacements_cid = array( '', '');

		// clean
		$output['conversion_id'] = wp_strip_all_tags( str_ireplace( $needles_cid, $replacements_cid, $input['conversion_id'] ) );

		// validate and sanitize conversion_label

		$needles_cl = array( '"' );
		$replacements_cl = array( '' );

		$output['conversion_label'] = wp_strip_all_tags( str_ireplace( $needles_cl, $replacements_cl, $input['conversion_label'] ) );

		// Return the array processing any additional functions filtered by this action
		// return apply_filters( 'sandbox_theme_validate_input_examples', $output, $input );
		return $output;
	}
}