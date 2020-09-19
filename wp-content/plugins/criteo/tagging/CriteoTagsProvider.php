<?php

defined('ABSPATH') or die('Forbidden');

require_once(dirname(__FILE__) . '/CriteoTagsGenerator.php');
require_once(dirname(__FILE__) . '/../settings/CriteoPluginSettings.php');
require_once(dirname(__FILE__) . '/PageUtil.php');

class CriteoTagsProvider
{

    const NUMBER_OF_PRODUCTS_IDS_TO_USE = 3;
    private static $PRODUCT_TYPES = ['product', 'product_variation'];

    public static function addCriteoTag()
    {
        try {
            $partnerId = CriteoPluginSettings::getPartnerID();
            $criteoTagsGenerator = new CriteoTagsGenerator($partnerId);
            $criteoTagsGenerator->setEmail(self::getUserHashedEmail());

            //check which page are we in and fire tag accordingly
            if (PageUtil::isHomePage()) {
                self::generateHomePageTag($criteoTagsGenerator);
            } elseif (PageUtil::isListingPage()) {
                self::generateListingPageTag($criteoTagsGenerator);
            } elseif (PageUtil::isProductPage()) {
                self::generateProductPageTag($criteoTagsGenerator);
            } elseif (PageUtil::isBasketPage()) {
                self::generateBasketPageTag($criteoTagsGenerator);
                if (CriteoPluginSettings::isWooCommerceVersionWithAjaxBasketUpdate()) {
                    self::generateAjaxPageTag($criteoTagsGenerator);
                }
            } else {
                return;
            }
        } catch (Exception $e) {
            criteoLogData('Exception caught while trying to generate tag:' . $e->getMessage());
        }
    }

    public static function addCriteoBasketHeader()
    {
        try {
            if (CriteoPluginSettings::isWooCommerceVersionWithAjaxBasketUpdate()
                && is_ajax()
                && PageUtil::isBasketPageUrl()
            ) {
                $cartProducts = self::getCartProducts();
                if (sizeof($cartProducts['item']) > 0) {
                    $items = $cartProducts['item'];
                    criteoLogData(' Ajax call detected - generating viewCart event for products:' . self::debugItems($items));

                    $partnerId = CriteoPluginSettings::getPartnerID();
                    $criteoTagsGenerator = new CriteoTagsGenerator($partnerId);
                    $criteoTagsGenerator->setEmail(self::getUserHashedEmail());
                    $criteoTagsGenerator->viewBasket($cartProducts);

                    header('Criteo-Tag:' . $criteoTagsGenerator->getJsonTagValues());
                } else {
                    criteoLogData('Ajax call detected but no products were found on cart page.');
                }
            }
        } catch (Exception $e) {
            criteoLogData('Exception caught while trying to generate header for AJAX tags:' . $e->getMessage());
        }
    }

    /**
     * @param CriteoTagsGenerator $criteoTagsGenerator
     */
    private static function generateHomePageTag(CriteoTagsGenerator $criteoTagsGenerator)
    {
        criteoLogData('Generating viewHome event.');
        $criteoTagsGenerator->viewHome();
        echo $criteoTagsGenerator->getTagCode();
    }

    /**
     * @param CriteoTagsGenerator $criteoTagsGenerator
     */
    private static function generateProductPageTag(CriteoTagsGenerator $criteoTagsGenerator)
    {
        $productID = self::getProductIdOrSku();
        criteoLogData('Generating viewItem event for product: ' . $productID);
        $criteoTagsGenerator->viewItem($productID);
        echo $criteoTagsGenerator->getTagCode();
    }

    private static function generateListingPageTag(CriteoTagsGenerator $criteoTagGenerator)
    {
        global $wp_query;

        $index = 0;
        $productIds = array();
        if ($wp_query->have_posts()) {
            while ($wp_query->have_posts()) :
                $post = $wp_query->the_post();
                //Check if post_type is product in order to be safe that we're not mixing something else.
                if (in_array(get_post_type($post), self::$PRODUCT_TYPES)) {
                    $index++;
                    array_push($productIds, self::getProductIdOrSku());
                    if ($index >= self::NUMBER_OF_PRODUCTS_IDS_TO_USE) {
                        break;
                    }
                }
            endwhile;
        }
        $wp_query->rewind_posts();
        if (sizeof($productIds) > 0) {
            criteoLogData('Generating viewList event for products: ' . implode(', ', $productIds));
            $criteoTagGenerator->viewList($productIds);
            echo $criteoTagGenerator->getTagCode();
        } else {
            criteoLogData('No products found on listing page.');
        }
    }

    /**
     * @param CriteoTagsGenerator $criteoTagsGenerator
     */
    private static function generateBasketPageTag(CriteoTagsGenerator $criteoTagsGenerator)
    {
        $products = self::getCartProducts();
        if (sizeof($products['item']) > 0) {
            $items = $products['item'];
            criteoLogData('Generating viewCart event for products:' . self::debugItems($items));
            $criteoTagsGenerator->viewBasket($products);
            echo $criteoTagsGenerator->getTagCode();
        } else {
            criteoLogData('No products found on cart page.');
        }
    }

    private static function generateAjaxPageTag(CriteoTagsGenerator $criteoTagsGenerator) {
        criteoLogData('Generating ajax page trigger.');
        echo $criteoTagsGenerator->getAjaxTagCode();
    }

    private static function getCartProducts()
    {
        global $woocommerce;
        $items = $woocommerce->cart->get_cart();

        $products = array('item' => array());
        foreach ($items as $item => $values) {
            $productId = $values['product_id'];
            $item_info = [
                'id' => self::getProductIdOrSkuForItem($productId),
                'price' => get_post_meta($productId, '_price', true),
                'quantity' => $values['quantity']
            ];
            array_push($products['item'], $item_info);
        }
        return $products;
    }

    /**
     * Method called by WooCommerce with proper $orderId when checkout event was generated.
     * Also see: https://docs.woocommerce.com/document/custom-tracking-code-for-the-thanks-page/
     * @param $orderId - the checkout order id.
     */
    public static function addCriteoSaleTag($orderId)
    {
        try {
            $partnerId = CriteoPluginSettings::getPartnerID();
            $criteoTagsGenerator = new CriteoTagsGenerator($partnerId);

            $email = self::getUserHashedEmail();
            if (empty($email)) {
                $email = self::getBillingHashedEmail($orderId);
            }
            $criteoTagsGenerator->setEmail($email);

            $order = self::getOrder($orderId);
            $transactionId = $order->get_order_number();

            $items = array();
            foreach ($order->get_items() as $item) {
                $productId = $item['product_id'];
                $item_info = [
                    'id' => self::getProductIdOrSkuForItem($productId),
                    'price' => $order->get_item_total($item),
                    'quantity' => self::getQuantity($item)
                ];
                array_push($items, $item_info);
            }

            criteoLogData('Generating trackTransaction event for products:' . self::debugItems($items));
            $criteoTagsGenerator->trackTransaction($transactionId, $items);

            echo $criteoTagsGenerator->getTagCode();
        } catch (Exception $e) {
            criteoLogData('Exception caught while trying to generate transaction tag:' . $e->getMessage());
        }
    }

    private static function getUserHashedEmail()
    {
        $email = wp_get_current_user()->user_email;
        if (!empty($email)) {
            return md5(strtolower($email));
        } else {
            return '';
        }
    }

    private static function getBillingHashedEmail($order_id)
    {
        $email = get_post_meta($order_id, '_billing_email', true);
        if (!empty($email)) {
            return md5(strtolower($email));
        } else {
            return '';
        }
    }

    private static function getProductIdOrSku()
    {
        if (CriteoPluginSettings::useSkuForProductID()) {
            return get_post_meta(get_the_ID(), '_sku', true);
        } else {
            return get_the_ID();
        }
    }

    private static function getProductIdOrSkuForItem($productId)
    {
        if (CriteoPluginSettings::useSkuForProductID()) {
            return get_post_meta($productId, '_sku', true);
        } else {
            return $productId;
        }
    }

    private static function getOrder($orderId)
    {
        global $woocommerce;
        if (version_compare($woocommerce->version, '2.2', '>=')) {
            return wc_get_order($orderId);
        } else {
            return new WC_Order($orderId);
        }
    }

    private static function getQuantity($item)
    {
        global $woocommerce;
        if (version_compare($woocommerce->version, '3.0', '>=')) {
            return $item['quantity'];
        } else {
            return $item['qty'];
        }
    }

    private static function debugItems($items)
    {
        $message = '[';
        foreach ($items as $item) {
            $message .= '{';
            foreach ($item as $key => $value) {
                $message .= $key . ':' . $value . '; ';
            }
            $message .= '} ';
        }
        $message .= ']';
        return $message;
    }
}