<?php

defined('ABSPATH') or die('Forbidden');

class PageUtil
{
    public static function isHomePage()
    {
        return is_front_page() || is_home();
    }

    public static function isListingPage()
    {
        return is_product_category() || is_shop() || is_search();
    }

    public static function isProductPage()
    {
        return is_product();
    }

    public static function isBasketPage()
    {
        return is_cart() && !is_checkout();
    }

    public static function isSalePage()
    {
        return is_order_received_page();
    }

    public static function isBasketPageUrl() {
        $requestPageLink = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        //get_refreshed_fragments is the method called for cart updates
        return (strpos($requestPageLink, 'get_refreshed_fragments') !== false);
    }

    public static function printPageType()
    {
        error_log('Detecting page type');

        if (is_front_page()) {
            error_log("Page type = front page");
        }

        if (is_home()) {
            error_log("Page type = home page");
        }

        if (is_shop()) {
            error_log("Page type = shop page");
        }

        if (is_product_category()) {
            error_log("Page type = product category page");
        }

        if (is_search()) {
            error_log("Page type = search page");
        }

        if (is_product()) {
            error_log("Page type = single product page");
        }

        if (is_cart()) {
            error_log("Page type = cart view page");
        }

        if (is_checkout()) {
            error_log("Page type = cart checkout page");
        }

        if (is_order_received_page()) {
            error_log("Page type = order received page");
        }
    }
}