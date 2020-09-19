<?php
/**
 * Created by PhpStorm.
 * User: subash
 * Date: 5/9/16
 * Time: 4:55 PM
 */

define('XMLRPC_REQUEST', true);
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
require_once dirname(__FILE__) . '/../../data/feedcore.php';
require_once dirname(__FILE__) . '/../../model/eBayAccount.php';

$default_account = CPF_eBayAccount::getDefaultAccount();
global $wpdb;
global $EC;

$table_accounts = $wpdb->prefix . 'ebay_accounts';
$table_currency = $wpdb->prefix . 'ebay_currency';
$site_info = $wpdb->get_row("SELECT acc.site_id,acc.site_code,cur.site_abbr ,cur.currency_code  FROM $table_accounts AS acc 
LEFT JOIN $table_currency as cur on acc.site_id = cur.site_id WHERE default_account = 1", ARRAY_A);
$table = $wpdb->prefix . 'ebay_shipping';
if (!sanitize_text_field($_POST['hiddenId'])) {
    $wpdb->insert($table, array(
        'paypal_email' => sanitize_text_field($_POST['paypal_email']),
        'paypal_accept' => sanitize_text_field($_POST['ebayPaypalAccepted']),
        'shippingfee' => sanitize_text_field($_POST['flatShipping']),
        'ebayShippingType' => sanitize_text_field($_POST['ebayShippingType']),
        'dispatchTime' => sanitize_text_field($_POST['dispatchTime']),
        'default_account' => $default_account,
        'shipping_service' => sanitize_text_field($_POST['shippingService']),
        'listingDuration' => sanitize_text_field($_POST['listingDuration']),
        'listingType' => sanitize_text_field($_POST['listingType']),
        'refundOption' => sanitize_text_field($_POST['refundOption']),
        'refundDesc' => sanitize_text_field($_POST['refundDesc']),
        'returnwithin' => sanitize_text_field($_POST['returnwithin']),
        'postalcode' => sanitize_text_field($_POST['postalcode']),
        'additionalshippingservice' => sanitize_text_field($_POST['additionalshippingservice']),
        'conditionType' => sanitize_text_field($_POST['conditionType']),
        'quantity' => sanitize_text_field($_POST['quantity']),
        'site_id' => sanitize_text_field($site_info['site_id']),
        'site_code' => sanitize_text_field($site_info['site_code']),
        'currency_code' => sanitize_text_field($site_info['currency_code']),
        'site_abbr' => sanitize_text_field($site_info['site_abbr'])
    ));
} else {
    $wpdb->update($table, array(
        'paypal_email' => sanitize_text_field($_POST['paypal_email']),
        'paypal_accept' => sanitize_text_field($_POST['ebayPaypalAccepted']),
        'shippingfee' => sanitize_text_field($_POST['flatShipping']),
        'ebayShippingType' => sanitize_text_field($_POST['ebayShippingType']),
        'dispatchTime' => sanitize_text_field($_POST['dispatchTime']),
        'default_account' => $default_account,
        'shipping_service' => sanitize_text_field($_POST['shippingService']),
        'listingDuration' => sanitize_text_field($_POST['listingDuration']),
        'listingType' => sanitize_text_field($_POST['listingType']),
        'refundOption' => sanitize_text_field($_POST['refundOption']),
        'refundDesc' => sanitize_text_field($_POST['refundDesc']),
        'returnwithin' => sanitize_text_field($_POST['returnwithin']),
        'postalcode' => sanitize_text_field($_POST['postalcode']),
        'additionalshippingservice' => sanitize_text_field($_POST['additionalshippingservice']),
        'conditionType' => sanitize_text_field($_POST['conditionType']),
        'quantity' => sanitize_text_field($_POST['quantity']),
        'site_id' => sanitize_text_field($site_info['site_id']),
        'site_code' => sanitize_text_field($site_info['site_code']),
        'currency_code' => sanitize_text_field($site_info['currency_code']),
        'site_abbr' => sanitize_text_field($site_info['site_abbr'])
    ),
        array('ID' => sanitize_text_field($_POST['hiddenId']))
    );
}



