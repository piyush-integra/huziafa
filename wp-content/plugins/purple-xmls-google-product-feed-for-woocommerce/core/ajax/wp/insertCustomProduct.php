<?php
if (!defined('ABSPATH')) {
    exit;
}
/*Exit if accessed directly*/
if (defined('ENV') && ENV === 'development') {
    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
if (!is_admin()) {
    die('Permission Denied!');
}
define('XMLRPC_REQUEST', true);
ob_start();

Class CustomProduct
{
    private $Method;
    public $count = 0;
    const FEEDTABLE = 'cp_feeds';
    const CUSTOMFEEDTABLE = 'cpf_customfeeds';

    function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->CurrentFeedId = null;
        $this->feedID = null;
        $this->date = date("Y-m-d H:i:s");
    }

    public function getWooProductData($id)
    {
        global $woocommerce;
        if ($woocommerce != null) {
            $wc_version = explode('.', $woocommerce->version);
            if (($wc_version[0] <= 2)) {
                $this->lowerWcVersion = true;
                $productDataByID = get_product($id);
                if (is_object($productDataByID) && !empty($productDataByID)) {
                    return $productDataByID;
                }
                return null;
            } else {
                $this->lowerWcVersion = false;
                $productDataByID = wc_get_product($id);
                if (is_object($productDataByID) && !empty($productDataByID)) {
                    return $productDataByID;
                }
                return null;
            }
        }
        return null;
    }

    public function getSearchedProduct()
    {

    }

    public function saveSelectedProduct()
    {
        $table = $this->db->prefix . self::CUSTOMFEEDTABLE;
        $insertion = false;
        $productIDS = array_key_exists('product_IDS', $_POST) ? wp_unslash($_POST['product_IDS']) : null;
        if (is_array($productIDS) && count($productIDS) > 0) {
            $this->count = count($productIDS);
            $count = 0;
            foreach ($productIDS as $key => $pid) {
                $data = array(
                    'product_id' => $pid,
                    'feed_identifier' => $this->feedID,
                    'created_at' => $this->date,
                    'updated_at' => $this->date,
                );
                add_filter('query', array($this, 'modifyInsertQuery'), 10);
                if ($this->db->insert($table, $data)) {
                    $count++;
                    $insertion = true;
                } else {
                    $insertion = false;
                }
            }
            if ($insertion == true && $count == $this->count) {
                echo json_encode(array('success' => true, 'feed_id' => $this->feedID, 'All products saved successfully'));
                exit;
            } else {
                echo json_encode(array('success' => true, 'feed_id' => $this->feedID, 'msg' => 'Some products may has not been saved,Please click move selected to reselect the products. Thanks.'));
                exit;
            }
        }
    }

    public function getSavedProductForCustomFeed($params)
    {
        remove_filter('query', array($this, 'modifyInsertQuery'), 10);
        if (sanitize_text_field($params)) {
            $table = $this->db->prefix . self::CUSTOMFEEDTABLE;
            $qry = $this->db->prepare("SELECT * FROM $table WHERE feed_identifier=%s", [sanitize_text_field($params)]);
            $result = $this->db->get_results($qry);

            if (is_array($result) && count($result) > 0) {
                $html = null;
                foreach ($result as $key => $p) {
                    $productData = $this->getWooProductData($p->product_id);
                    $productData->categories = get_the_term_list($p->product_id, 'product_cat', '', ',', '');
                    if ($productData) {
                        $html .= '<tr>';
                        $html .= '<td style="width: 5%"><input type="checkbox"/></td>';
                        $html .= '<td class="index">' . $productData->get_name() . '<span class="cpf_product_id_hidden" style="display:none;">' . $p->product_id . '</span>';
                        $html .= '<span class="cpf_feed_id_hidden" style="display:none;">' . $p->product_id . '</span></td>';
                        $html .= '<td class="index">' . $productData->categories . '</td>';
                        $html .= '<td class="cpf-selected-parent" style="width: 7%"><span class="dashicons dashicons-trash" onclick="deleteSelectedProduct(this);"title="Delete this row."></span><span class="spinner"></span></td>';
                        $html .= '</tr>';
                    }
                }
                echo $html;
                exit();
            } else {
                $html = '<tr id="cpf-no-products"><td colspan="5">No product selected.</td></tr>';
                echo $html;
                exit();
            }
        } else {
            $html = '<tr id="cpf-no-products"><td colspan="5">No Feed ID Provided.</td></tr>';
            echo $html;
            exit();
        }
    }

    public function deleteSelectedProducts($param)
    {
        if ($param) {
            $table = $this->db->prefix . self::CUSTOMFEEDTABLE;
            if ($this->db->delete($table, ['product_id' => $param, 'feed_identifier' => $this->feedID])) {
                echo json_encode(array('success' => true, 'feed_id' => $this->feedID));
                exit();
            } else {
                echo json_encode(array('success' => false, 'feed_id' => $this->feedID));
                exit();
            }
        } else {
            echo json_encode(array('success' => false, 'feed_id' => $this->feedID));
            exit();
        }
    }

    public function getLatestFeedId()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'cp_feeds';
        $query = "SELECT id FROM $table ORDER BY id DESC LIMIT 1";
        $result = $wpdb->get_row($query);
        if ($result) {
            return $result->id;
        }

    }

    public function getFeedID()
    {
        $table = $this->db->prefix . self::FEEDTABLE;
        $sql = $this->db->prepare("SELECT id FROM  {$table} WHERE id=%d", [$this->CurrentFeedId + 1]);
        $result = $this->db->get_row($sql);
        if ($result) {
            return $result->id + 1;
        }
        return $this->CurrentFeedId + 1;

    }

    public function modifyInsertQuery($query)
    {
        $count = 0;
        $query = preg_replace('/^(INSERT INTO)/i', 'INSERT IGNORE INTO', $query, 1, $count);
        return $query;
    }

    public function _Initiate()
    {
        if (array_key_exists('identifier', $_POST)) {
            $this->feedID = sanitize_text_field($_POST['identifier']);
        }
        $method = array_key_exists('perform', $_POST) ? sanitize_text_field($_POST['perform']) : null;
        $arguments = array_key_exists('params', $_POST) ? wp_unslash($_POST['params']) : '';
        if (!is_array($arguments)) {
            $arguments = array($arguments);
        }
        if (is_null($method)) {
            echo json_encode(array('success' => false, 'msg' => "Methods was null"));
        } elseif (!method_exists($this, $method)) {
            echo json_encode(array('success' => false, 'msg' => "Methods {$method} does not exists."));
        } else {
            call_user_func_array(array($this, $method), $arguments);
        }
    }
}

$OBJECT = New CustomProduct();
$OBJECT->_Initiate();
