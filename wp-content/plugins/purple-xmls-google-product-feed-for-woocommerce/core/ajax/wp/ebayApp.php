<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if(!is_admin()){
    die('Permission Denied!');
}
class EbayApp {

    var $app_id;
    var $ebay_Cats;
    var $site_id ;

    function __construct($appID) {
        // get options
        $this->app_id=$appID;
        $this->setupEbayApp();
        //fetch site code for default account first
        //$this->site_id = '16';
        global $wpdb;
        $table = $wpdb->prefix.'ebay_accounts';
        $this->site_id = $wpdb->get_var("
            SELECT site_id
            FROM $table
            WHERE default_account = 1");

    }


    function getCats($parent_cat=-1) {
        if(!$parent_cat) $parent_cat = -1; // doesn't work as a prop

        // -1: top, 11450: Clothing, Shoes & Accessories, 1059: Men's Clothing,
        $endpoint = 'http://open.api.ebay.com/Shopping?callname=GetCategoryInfo&appid='.$this->app_id.'&version=675&siteid='.$this->site_id.'&CategoryID='.$parent_cat.'&IncludeSelector=ChildCategories';
        $responsexml = '';
        if( ini_get('allow_url_fopen') ) {
            $responsexml = @file_get_contents($endpoint);
            if($responsexml) {
                $xml = simplexml_load_string($responsexml);
                // remove top from list
                unset($xml->CategoryArray->Category[0]);
                return $xml->CategoryArray;
            }
            return;
        } else if(function_exists('curl_version')) {
            $endpoint = 'http://open.api.ebay.com/shopping?';
            $headers = array(
                'X-EBAY-API-CALL-NAME: GetCategoryInfo',
                'X-EBAY-API-VERSION: 521',
                'X-EBAY-API-REQUEST-ENCODING: XML',
                'X-EBAY-API-SITE-ID: 0',
                'X-EBAY-API-APP-ID: '.$this->app_ID,
                'Content-Type: text/xml;charset=utf-8'
            );
            $xmlrequest = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
				<GetCategoryInfoRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">
				  	<CategoryID>".$parent_cat."</CategoryID>
					<IncludeSelector>ChildCategories</IncludeSelector>
				</GetCategoryInfoRequest>";
            // check this and process accordingly
            $response = wp_remote_post($endpoint,
                array(
                    'timeout' => 60,
                    'redirection' => 5,
                    'blocking' => true,
                    'headers' => array(
                        'Expect' => '',
                        'X-EBAY-API-CALL-NAME'=> 'GetCategoryInfo',
                        'X-EBAY-API-VERSION' => 521,
                        'X-EBAY-API-REQUEST-EN' => 0,
                        'X-EBAY-API-APP-ID' => $this->app_ID,
                        'Content-Type' => 'text/xml;charset=utf-8'
                    ),
                    'body' => $xmlrequest
                )
            );
            $responsexml = wp_remote_retrieve_response_code($response);

            // var_dump($responsexml);
            $xml = simplexml_load_string($responsexml);
            // remove top from list
            unset($xml->CategoryArray->Category[0]);
            return $xml->CategoryArray;
        } else {
            return;
        }
    }

    function setCats($parent='') {
        $this->ebay_Cats = $this->getCats($parent);
    }

    function setupEbayApp() {
        if($this->app_id==null)
            die("App ID is required.");

        $this->setCats($parent_cat);
    }

}
