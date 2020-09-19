<?php
if (!defined('ABSPATH')) {
    exit;
}
/*Exit if accessed directly*/
if (!is_admin()) {
    die('Permission Denied!');
}
define('XMLRPC_REQUEST', true);
ob_start();

class CustomfeedProductSearch
{
    public $count = 0;

    public function __construct()
    {
        global $wpdb;
        $this->db   = $wpdb;
        $this->date = date("Y-m-d H:i:s");
    }

    public function ProductSearch($params)
    {
        $args = array();
        if (is_array($params)) {
            foreach ($params as $key => $param) {
                $args[$key] = $param;
            }
        }

        wp_send_json_success($args);
        die();
    }

}
