<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
define('XMLRPC_REQUEST', true);
ob_start();
//********************************************************************
//Load the products
//********************************************************************
global $wpdb;

$pattern = "~'~";

if ($_REQUEST['q'] == 'ajax') {
    $keywords = sanitize_text_field($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
    $filterTerm = sanitize_text_field($_POST['searchfilters']) ? sanitize_text_field($_POST['searchfilters']) : '';
    if ($filterTerm == 'sku') {
        $where = "meta_key = '_sku' AND meta_value LIKE '%{$keywords}%' AND p.post_parent='0'";
        $sql = "SELECT pm.meta_id as meta_id ,pm.post_id as id,  pm.meta_value as title
                         FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->prefix}posts p on p.id=pm.post_id
                         where {$where}
                 ";
        $result = $wpdb->get_results($sql, ARRAY_A);
        $skuorkeyword = "sku";
        if (!count($result) > 0) {
            $skuorkeyword = "keyword";
            $where = "post_title like '%{$keywords}%' AND post_type='product' AND post_parent='0'";
            $sql = "SELECT ID as id ,post_title as title
                         FROM {$wpdb->prefix}posts
                         where {$where}
                    ";
            $result = $wpdb->get_results($sql, ARRAY_A);
        }
    }

    if ($filterTerm == 'all') {
        $where = "(post_title like '%{$keywords}%' OR postmeta_table_1.meta_value LIKE '%{$keywords}%') AND (meta_key='_sku' AND post_type='product' AND post_parent='0')";
        $sql = "SELECT ID as id ,post_title as title
                         FROM {$wpdb->prefix}posts
                         LEFT JOIN {$wpdb->prefix}postmeta as postmeta_table_1 on postmeta_table_1.post_id = {$wpdb->prefix}posts.ID
                         where {$where}
                    ";
    }

    ?>
    <ul id="filters_results">
        <?php
        if (count($result) > 0) {
            foreach ($result as $data => $product) {?>
                <li onclick="selectFilters('<?php echo $product['title']; ?>');"><?php echo $product['title']; ?></li>
                <input type="hidden" value="<?php echo $product['id']; ?>" name="cpf-hidden-id"/>
            <?php }?>
            <input id="skuorkeyword" type="hidden" name="skuorkeywordsearch" value="<?php echo $skuorkeyword; ?>">
        <?php } else {?>
            <li><span class="no-search-results">No Record found</span></li>
        <?php }?>
    </ul>
<?php }?>

<?php
if (sanitize_text_field($_REQUEST['q']) == 'search') {
    $merchat_type = isset($_POST['merchat_type']) ? sanitize_text_field($_POST['merchat_type']) : sanitize_text_field($_POST['service_name']);
    $keywords = isset($_POST['keywords']) ? sanitize_text_field($_POST['keywords']) : "";
    $category_id = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $price_range = isset($_POST['price_range']) ? sanitize_text_field($_POST['price_range']) : '';
    $product_sku = isset($_POST['sku']) ? sanitize_text_field($_POST['sku']) : '';
    $showOutofStock = isset($_POST['showOutofStock']) ? sanitize_text_field($_POST['showOutofStock']) : '0';
    $limit = isset($_POST['limit']) ? sanitize_text_field($_POST['limit']) : '';
    $keywordsorsku = isset($_POST['keywordsorsku']) ? sanitize_text_field($_POST['keywordsorsku']) : '';
    $page = array_key_exists('page', $_POST) ? sanitize_text_field($_POST['page']) : 0;
    $cats = "";
    $priceLimit = "";
    $skuQuery = "";
    $title = '';
    $sku = '';
    if ($showOutofStock == '1') {
        $hideoutofstock = false;
    } else {
        $hideoutofstock = true;
    }

    if ($keywordsorsku == 'sku') {
        $sku = $product_sku;
    } else {
        $title = $product_sku;
    }

    $table = $wpdb->prefix . 'posts';
    $relationTable = $wpdb->prefix . 'term_relationships';
    $taxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $termTable = $wpdb->prefix . 'terms';
    $postMetatable = $wpdb->prefix . 'postmeta';
    $postmetaSelect = '';
    $postmetaJoin = '';

    $where = '';
    $or = false;
    if ($sku || $category_id || $title || $hideoutofstock || $price_range) {
        $where .= 'WHERE';
    }
    if ($title) {
        $or = true;
        $where .= " P.post_title like '%{$product_sku}%'";
    }
    if ($sku || $price_range || $hideoutofstock) {
        $metawhere = '';
        $postmetaSelect = ', GROUP_CONCAT(PM.meta_value) as value';
        $postmetaJoin = " LEFT JOIN {$postMetatable} PM ON PM.post_id = P.id";
        if ($or == false) {
            $or = true;
            $where .= " (";} else {
            $or = true;
            $where .= "  (";
        }
        $and = false;
        if ($sku) {
            $and = true;
            $metawhere .= " PM.meta_key='_sku' AND PM.meta_value like '%{$sku}%' ";
        }
        if ($price_range) {
            if ($and == true) {
                $and = true;
                $metawhere = " AND PM.meta_value {$price_range} ";
                if (strpos($price_range, '-')) {
                    $price = explode('-', $price_range);
                    $metawhere = " AND (PM.meta_value >= {$price[0]} AND PM.meta_value <= {$price[1]}) ";
                }
            } else {
                $and = true;
                $metawhere = "PM.meta_key = '_regular_price' AND PM.meta_value = {$price_range}";
                if (strpos($price_range, '-')) {
                    $price = explode('-', $price_range);
                    $metawhere = " (PM.meta_key = '_regular_price' AND PM.meta_value >= {$price[0]} AND PM.meta_value <= {$price[1]}) ";
                }
            }

        }

        if ($hideoutofstock) {
            if ($and == true) {
                $metawhere .= " AND (PM.meta_key = '_stock' AND PM.meta_value >=1) ";
            } else {
                $metawhere .= "(PM.meta_key = '_stock' AND PM.meta_value >=1) ";
            }

        }

        $where .= $metawhere . ' )';

    }
    if ($category_id) {
        if (strlen($where) < 5) {
            $where .= "WHERE ";
        }
        $taxonomies = array(
            'taxonomy' => 'product_cat');

        $args = array('child_of' => $category_id);

        $childCategories = get_terms($taxonomies, $args);

        if (is_array($childCategories) && count($childCategories) > 0) {
            $catarray = array($category_id);
            foreach ($childCategories as $key => $value) {
                array_push($catarray, $value->term_id);
            }

            $category_ids = implode(',', $catarray);
            if ($or == false) {
                $where .= " T.term_id IN ({$category_ids})";
            } else {
                $where .= " AND T.term_id IN ({$category_ids}) ";
            }

        } else {

            if ($or == false) {
                $where .= " T.term_id = $category_id";
            } else {
                $where .= " AND T.term_id = $category_id ";
            }

        }

    }

    if (strlen($where) > 10) {
        $where .= " AND P.post_type='product' AND P.post_status ='publish' AND (tax.taxonomy='category' OR tax.taxonomy='product_cat')";
    } else {
        $where = " WHERE P.post_type='product' AND P.post_status ='publish' AND (tax.taxonomy='category' OR tax.taxonomy='product_cat')";
    }

    $count = "SELECT COUNT(DISTINCT P.ID) as count FROM {$table} P
            LEFT JOIN {$relationTable} rel ON rel.object_id = P.ID
            LEFT JOIN {$taxonomyTable} tax ON tax.term_taxonomy_id = rel.term_taxonomy_id
            LEFT JOIN {$termTable} T ON T.term_id = tax.term_id
            {$postmetaJoin}
            {$where}
             ";
    $cresult = $wpdb->get_row($count);

    $totalrow = $cresult->count;
    $perpage = 20;
    $pagecount = 1 + $page;
    $offset = $page * $perpage;
    $limit = "LIMIT {$perpage} OFFSET {$offset}";

    $SQL = "SELECT P.*,GROUP_CONCAT(T.name) as category, GROUP_CONCAT(T.slug) as category_slug, GROUP_CONCAT(tax.taxonomy) as taxtype {$postmetaSelect}  FROM {$table} P
            LEFT JOIN {$relationTable} rel ON rel.object_id = P.id
            LEFT JOIN {$taxonomyTable} tax ON tax.term_taxonomy_id = rel.term_taxonomy_id
            LEFT JOIN {$termTable} T ON T.term_id = tax.term_id
            {$postmetaJoin}
            {$where} GROUP BY P.ID {$limit}
             ";

    $results = $wpdb->get_results($SQL);

    if ($pagecount * $perpage >= $totalrow) {
        $hideshowmore = true;
    } else {
        $hideshowmore = false;
    }

    $html = '';

    if (is_array($results) && count($results) > 0) {
        foreach ($results as $key => $value) {
            $productData = getWooProductData($value->ID);

            if (strlen($productData->get_name()) > 30) {
                $dot = '...';
            } else {
                $dot = '';
            }
            $catnames = explode(',', $value->category);
            $displayCat = array_unique($catnames);
            $html .= '<tr>
                      <td style="text-align:center;"><input type="checkbox"></td>
                      <td class="index">' . $productData->get_sku() . '</td>
                      <td class="index">' . str_replace('woo', '', substr($productData->get_name(), 0, 20)) . $dot . '</td>
                      <td class="index">' . implode(',', $displayCat) . '
                      <div class="cpf_selected_product_hidden_attr" style="display: none ;">';
            $html .= '<span class="cpf_selected_product_id">' . $value->ID . '</span>';
            $html .= '<span class="cpf_selected_product_title">' . $productData->get_name() . '</span>';
            $html .= '<span class="cpf_selected_product_cat_names">' . implode(',', $displayCat) . '</span>
                      <span class="cpf_selected_local_cat_ids">' . implode(',', $productData->get_category_ids()) . '</span>
                      <span class="cpf_selected_product_type">' . $productData->get_type() . '</span>
                      <span
                      class="cpf_selected_product_attributes_details"></span>
                      <span class="cpf_selected_product_variation_ids"></span>
                      </div>
                      </td>

                      <td style="text-align:center;" >' . $productData->get_regular_price() . '</td>
                      <td style="text-align:center;" >' . $productData->get_stock_quantity() . '</td>
                      <td>
                      <div><span><input style="border: none; background-color: #fff;" disabled type="search" name="categoryDisplayText" class="text_big" id="categoryDisplayText"
                      onkeyup="doFetchCategory_timed_custom(' . "'{$merchat_type}'" . ',this)" value=""
                      onclick = "doFetchCategory_timed_custom(' . "'{$merchat_type}'" . ',this)"
                      autocomplete="off"
                      placeholder="Select Merchant category from bulk action" style="width: 100%;"></span>
                      <div class="categoryList"></div>
                      <div class="no_remote_category"></div>
                      </div>
                      </td>
                      <td style="text-align:center;" class="cpf-selected-parent" style="width: 7%"><span class="dashicons dashicons-trash "
                      onclick="cpf_remove_feed_parent(this);" title="Delete this row."></span><span class="spinner"></span></td>
                    </tr>';

        }
    }

    /*if ($count > 0) {
                foreach ($results as $data => $product): ?>
                    <?php

                if (strlen($product['post_title']) > 30) {
                    $dot = '...';
                } else {
                    $dot = '';
                }
                if ($product['price'] > 0) {
                    $price = get_woocommerce_currency_symbol() . ' ' . $product['price'];
                } else {
                    $price = '--';
                }

                $quantity = isset($product['quantity']) ? $product['quantity'] : '--';
                $html .= '<tr>
                              <td style="text-align:center;"><input type="checkbox"></td>
                              <td class="index">' . $product['sku'] . '</td>
                              <td class="index">' . str_replace('woo', '', substr($product['post_title'], 0, 20)) . $dot . '</td>
                              <td class="index">' . $product['category_names'] . '
                              <div class="cpf_selected_product_hidden_attr" style="display: none ;">

                              <span class="cpf_selected_product_id">' . $product['ID'] . '</span>
                              <span class="cpf_selected_product_title">' . $product['post_title'] . '</span>
                              <span class="cpf_selected_product_cat_names">' . $product['category_names'] . '</span>
                              <span class="cpf_selected_local_cat_ids">' . $product['category_ids'] . '</span>
                              <span class="cpf_selected_product_type">' . $product['product_type'] . '</span>
                              <span
                              class="cpf_selected_product_attributes_details">' . $product['attribute_details'] . '</span>
                              <span class="cpf_selected_product_variation_ids">' . $product['variation_ids'] . '</span>
                              </div>
                              </td>

                              <td style="text-align:center;" >' . $price . '</td>
                              <td style="text-align:center;" >' . $quantity . '</td>
                              <td>
                              <div><span><input style="border: none; background-color: #fff;" disabled type="search" name="categoryDisplayText" class="text_big" id="categoryDisplayText"
                              onkeyup="doFetchCategory_timed_custom(' . "'{$merchat_type}'" . ',this)" value=""
                              onclick = "doFetchCategory_timed_custom(' . "'{$merchat_type}'" . ',this)"
                              autocomplete="off"
                              placeholder="Select Merchant category from bulk action" style="width: 100%;"></span>
                              <div class="categoryList"></div>
                              <div class="no_remote_category"></div>
                              </div>
                              </td>
                              <td style="text-align:center;" class="cpf-selected-parent" style="width: 7%"><span class="dashicons dashicons-trash "
                              onclick="cpf_remove_feed_parent(this);" title="Delete this row."></span><span class="spinner"></span></td>
                            </tr>';
                endforeach;

    */

    $data = [
        'html' => $html,
        'hideshowmore' => $hideshowmore,
        'count' => $totalrow,
    ];

    echo json_encode($data);exit;

}?>
<?php
if (sanitize_text_field($_REQUEST['q']) == 'savep') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cpf_custom_products';
    // $wpdb->query('TRUNCATE TABLE '.$table_name);
    if (sanitize_text_field($_POST['remote_category']) == '') {
        $remote_category = null;
        /* echo '<div id="no_remote_category_selected">Please select merchant category.</div>';
    die;*/
    } else {
        $remote_category = sanitize_text_field($_POST['remote_category']);
    }
    if (sanitize_text_field($_POST['local_cat_ids'])) {
        $check = $wpdb->get_row("SELECT COUNT(product_id) as count FROM $table_name WHERE product_id = " . sanitize_text_field($_POST['product_id']));
        if ($check->count <= 0) {
            $wpdb->insert(
                $table_name,
                array(
                    'category' => sanitize_text_field($_POST['local_cat_ids']),
                    'product_title' => sanitize_text_field($_POST['product_title']),
                    'category_name' => sanitize_text_field($_POST['category_name']),
                    'product_type' => sanitize_text_field($_POST['product_type']),
                    'product_attributes' => sanitize_text_field($_POST['product_attributes']),
                    'product_variation_ids' => sanitize_text_field($_POST['product_variation_ids']),
                    'remote_category' => $remote_category,
                    'product_id' => sanitize_text_field($_POST['product_id']),
                )

            );
        }
    }
    print_r($wpdb->last_query);
    die;
}

if (sanitize_text_field($_REQUEST['q']) == 'truncateTable') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cpf_custom_products';
    $wpdb->query('TRUNCATE TABLE ' . $table_name);
    die;
}

if (sanitize_text_field($_REQUEST['q']) == 'assignCategory') {
    if (sanitize_text_field($_POST['remote_category'])) {
        $remote_category = sanitize_text_field($_POST['remote_category']);
        global $wpdb;
        $table_name = $wpdb->prefix . 'cpf_custom_products';
        $sql = "UPDATE $table_name SET `remote_category` = '$remote_category' ";
        $wpdb->query($sql);
        die;
    } else {
        die();
    }
}

if (sanitize_text_field($_REQUEST['q']) == 'showT') {
    global $wpdb;

    $feed_id = sanitize_text_field($_POST['feed_id']) ? sanitize_text_field($_POST['feed_id']) : '';

    if ($feed_id) {
        $sql = "SELECT `product_details` from {$wpdb->prefix}cp_feeds where `feed_type`=1 AND `id` = {$feed_id} ";
        $res = $wpdb->get_var(($sql));
        $result = maybe_unserialize($res);
        if (empty($result)) {
            $result = preg_replace_callback($pattern, 'callback', $res);
            $result = unserialize($result);
        }
    } else {
        $table_name = $wpdb->prefix . 'cpf_custom_products';
        $sql = "
            SELECT id,product_title , category_name , remote_category , product_id
             FROM {$table_name}
             ORDER BY id
             ";
        $result = $wpdb->get_results($sql, ARRAY_A);
    }
    if (count($result)) {

        foreach ($result as $data => $product) {?>
            <tr>
                <td style="width: 5%"><input type="checkbox"/></td>
                <td class="index"><?php echo $product['product_title']; ?><span class="cpf_product_id_hidden"
                                                                                style="display:none;"><?php echo $product['product_id']; ?></span>
                    <span class="cpf_feed_id_hidden"
                          style="display:none;"><?php echo $product['id']; ?></span>
                </td>
                <td class="index"><?php echo $product['category_name']; ?></td>
                <td class="cpf-selected-parent" style="width: 7%"><span class="dashicons dashicons-trash "
                                                                        onclick="cpf_remove_feed(this);"
                                                                        title="Delete this row."></span><span
                            class="spinner"></span></td>
            </tr>
        <?php }
    } else {?>

        <tr id="cpf-no-products">
            <td colspan="5">No product selected.</td>
        </tr>
    <?php }

}

if (sanitize_text_field($_REQUEST['q']) == 'checkDB') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cpf_custom_products';
    $sql = "
            SELECT id,product_title , category_name , remote_category , product_id
             FROM {$table_name}
             ORDER BY id
             ";
    $results = $wpdb->get_results($sql, ARRAY_A);
    if (is_array($results) && count($results) > 0) {
        $data = array('status' => true, 'data' => count($results));
    } else {
        $data = array('status' => false, 'data' => 0);
    }
    echo json_encode($data);exit;
}

if (sanitize_text_field($_REQUEST['q']) == 'delR') {
    $id = sanitize_text_field($_POST['id']);
    $identifier = array_key_exists('identifier', $_POST) ? sanitize_text_field($_POST['identifier']) : null;
    if (is_array($id)) {
        $id = implode(',', $id);
    }
    if (is_null($identifier)) {
        echo "Feed Identifier is Empty, Can't Delete the products now, Please try again later";
        exit();
    }
    global $pfcore;
    $tableName = $wpdb->prefix . 'cpf_customfeeds';
    $sql = "DELETE FROM {$tableName} WHERE product_id IN ($id) AND feed_identifier='{$identifier}'";
    $wpdb->query($sql);
    $wpdb->last_error;
    // $wpdb->delete($tableName, array('id' => $id));
    die;

}

if (sanitize_text_field($_REQUEST['q']) == 'saveEdit') {
    global $wpdb;
    $pattern = "~'~";
    $table_name = $wpdb->prefix . 'cpf_custom_products';
    $feed_id = sanitize_text_field($_POST['feed_id']) ? sanitize_text_field($_POST['feed_id']) : '';
    $sql = "SELECT `product_details` from {$wpdb->prefix}cp_feeds where `feed_type`=1 AND `id` = {$feed_id} ";
    $res = $wpdb->get_var(($sql));
    $result = maybe_unserialize($res);
    if (empty($result)) {
        $result = preg_replace_callback($pattern, 'callback', $res);
        $result = unserialize($result);
    }

    $rm = null;

    if (is_array($result) && !empty($result)) {
        $rm = $result[0]['remote_category'];
    }
    $table_name = $wpdb->prefix . 'cpf_custom_products';

    if (is_array($result) && count($result) > 0) {
        foreach ($result as $data => $products) {
            $check = $wpdb->get_row("SELECT COUNT(product_id) as count FROM $table_name WHERE product_id = " . sanitize_text_field($products['product_id']));
            if ($check->count <= 0) {
                $wpdb->insert(
                    $table_name,
                    array(
                        'category' => sanitize_text_field($products['category']),
                        'product_title' => sanitize_text_field($products['product_title']),
                        'category_name' => sanitize_text_field($products['category_name']),
                        'product_type' => sanitize_text_field($products['product_type']),
                        'product_attributes' => sanitize_text_field($products['product_attributes']),
                        'product_variation_ids' => sanitize_text_field($products['product_variation_ids']),
                        'remote_category' => sanitize_text_field($products['remote_category']),
                        'product_id' => sanitize_text_field($products['product_id']),
                    )
                );
            } else {
                $sql_custom = "TRUNCATE {$wpdb->prefix}cpf_custom_products";
                $wpdb->query($sql_custom);
                $wpdb->insert(
                    $table_name,
                    array(
                        'category' => sanitize_text_field($products['category']),
                        'product_title' => sanitize_text_field($products['product_title']),
                        'category_name' => sanitize_text_field($products['category_name']),
                        'product_type' => sanitize_text_field($products['product_type']),
                        'product_attributes' => sanitize_text_field($products['product_attributes']),
                        'product_variation_ids' => sanitize_text_field($products['product_variation_ids']),
                        'remote_category' => sanitize_text_field($products['remote_category']),
                        'product_id' => sanitize_text_field($products['product_id']),
                    )
                );
            }
        }
    }

    $data = array(
        'status' => 'success',
        'remote_category' => $rm,
    );
    echo json_encode($data);
    /* print_r($wpdb->last_query);
die;*/
}

function callback($matches) {

    return "\'";
}

function getWooProductData($id) {
    global $woocommerce;
    if ($woocommerce != null) {
        $wc_version = explode('.', $woocommerce->version);
        if (($wc_version[0] <= 2)) {
            $owerWcVersion = true;
            $productDataByID = get_product($id);
            if (is_object($productDataByID) && !empty($productDataByID)) {
                return $productDataByID;
            }
            return null;
        } else {
            $lowerWcVersion = false;
            $productDataByID = wc_get_product($id);
            if (is_object($productDataByID) && !empty($productDataByID)) {
                return $productDataByID;
            }
            return null;
        }
    }
    return null;
}
