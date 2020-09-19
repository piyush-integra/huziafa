<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( defined('WCCF_VERSION') && !class_exists('ReyCore_Compatibility__WCCustomFields') ):

	class ReyCore_Compatibility__WCCustomFields
	{
		public function __construct()
		{
			add_action( 'admin_init', [$this, 'disable_nags'] );
		}

		function disable_nags(){
			if( get_site_option('rightpress_up_dis_woocommerce_custom_fields') != 1 ){
				update_site_option('rightpress_up_dis_woocommerce_custom_fields', 1);
			}
		}
	}

	new ReyCore_Compatibility__WCCustomFields;
endif;
