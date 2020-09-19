<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/********************************************************************
 * Version 3.0
 * Export a 11Main CSV data feed
 * Copyright 2015 Export Feed. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Calv 2015-20-08
 ********************************************************************/
class EtsyDlg extends PBaseFeedDialog
{


    function __construct()
    {
        parent::__construct();
        $this->service_name = 'Etsy';
        $this->service_name_long = 'Etsy Shop';
        $this->plugin_link ="https://wordpress.org/plugins/exportfeed-for-woocommerce-product-to-etsy/";
        $this->plugin_message = "<p style='font-size:16px;''>Want to sell products on Etsy?
            </p><p>Please use our Etsy Plugin to benefit from:</p>
            <ol><li>Upload bulk products to Etsy Merchant</li>
            <li>List products with their product variations</li>
            <li>Send multiple product images as per Etsy's limit</li>
            <li>We'll create your 1st Etsy product feed for Free</li>
            <li> For more information <a target='_blank' href='https://www.exportfeed.com/woocommerce-product-feed/woocommerce-product-feeds-to-etsy/'><b>Click here</b></a><br></li></ol>
            <p>";
        $this->landinpageurl = "https://www.exportfeed.com/woocommerce-product-feed/woocommerce-product-feeds-to-etsy/";
    }

    function categoryList($initial_remote_category)
    {
        $etsy = new Etsy();

        return 'Etsy categories : 
			<input type="text" name="etsy_category_display" class="text_big" id="etsy_category_display"  onclick="showEtsyCategories(\'' . $this->service_name . '\')" value="' . $initial_remote_category . '" autocomplete="off" readonly="true" placeholder="Click here to select your categories"/>
			<input type="hidden" name="remote_category" id="remote_category" value="' . $initial_remote_category . '" />';

        /*		return $etsy->fetch_category($initial_remote_category).'<input type="hidden" id="service_status" value="'.get_current_user_id().'"/><div id="categoryList" class="categoryList"></div><input type="hidden" id="remote_category" name="remote_category" value="'.$initial_remote_category.'" />';*/
    }

    function convert_option($option)
    {
        return strtolower(str_replace(" ", "_", $option));
    }
}
