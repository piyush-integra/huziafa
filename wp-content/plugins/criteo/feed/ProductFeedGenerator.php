<?php

defined('ABSPATH') or die('Forbidden');

require_once(dirname(__FILE__) . '/ProductFeedLoader.php');
require_once(dirname(__FILE__) . '/../auth/HttpBasicWithCustomHeaderAuthenticationManager.php');
require_once(dirname(__FILE__) . '/../settings/CriteoPluginSettings.php');

/*
 * Class to query and generate Criteo feed
 */
class ProductFeedGenerator
{

    public static function getCriteoFeed()
    {
        if (!HttpBasicWithCustomHeaderAuthenticationManager::checkModuleEnabledAndAuthentication()) {
            return;
        }

        header('Content-type: text/xml; charset=utf-8');
        try {
            $page = self::getPageNumber();
            $productsPerPage = self::getProductsPerPage();

            criteoLogData('Retrieving product catalog for page = ' . $page . ' and limit = ' . $productsPerPage);

            //get product info from database
            $feed_data = ProductFeedLoader::getProductInfo($page, $productsPerPage);

            //generate feed and we are done
            $xml_feed = self::generateXmlFeed($feed_data);
        } catch (Exception $e) {
            criteoLogData('Error while retrieving product catalog:' . $e->getMessage());
            //when the sky falls
            $xml_feed = '<error>' . $e->getMessage() . '</error>';
        }
        echo($xml_feed);
    }

    /**ENABLED FOR PROFILING*/
    public static function testXmlFeed()
    {
        //enable memory profilter
        if (function_exists('memprof_enable')) {
            memprof_enable();
        }

        $page = self::getPageNumber();
        $productsPerPage = self::getProductsPerPage();
        $memoryProfilerSnapshotLocation = '/var/www/html/callgrind.out';

        //get product info from database
        $feed_data = ProductFeedLoader::getProductInfo($page, $productsPerPage);

        //generate feed
        self::generateXmlFeed($feed_data);

        echo 'This is a performance testing page.<br/>';
        echo "You've called this page with the following settings:<br/>";
        echo 'Page number = ' . $page . '<br/>';
        echo 'Products per page = ' . $productsPerPage . '<br/>';
        echo 'Check the settings with Memory Viewer on top of this page<br/>';
        echo 'If memory profiler is installed also a memory dump will be dropped on ' . $memoryProfilerSnapshotLocation;


        if (function_exists('memprof_dump_callgrind')) {
            //snapshot location, this file can be loaded with KCachegrind
            memprof_dump_callgrind(fopen($memoryProfilerSnapshotLocation, "w"));
        }
    }

    public static function generateXmlFeed($feedData)
    {
        $xml_string = '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">' . PHP_EOL;
        foreach ($feedData as $row) {
            $xml_string .= self::generateXmlRow($row) . PHP_EOL;
        }
        $xml_string .= '</rss>';
        return $xml_string;
    }

    private static function generateXmlRow($row)
    {
        $xml_string = '<item>' . PHP_EOL;
        foreach ($row as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $single_value) {
                    $xml_string .= self::generateXmlAttribute($key, $single_value);
                }
            } else {
                $xml_string .= self::generateXmlAttribute($key, $value);
            }
        }
        $xml_string .= '</item>' . PHP_EOL;
        return $xml_string;
    }

    private static function generateXmlAttribute($key, $single_value)
    {
        $xml_string = '';
        if (!empty($single_value)) {
            $xml_string .= '<g:' . $key . '>';
            $xml_string .= htmlspecialchars($single_value);
            $xml_string .= '</g:' . $key . '>';
        }
        return $xml_string;
    }

    private static function getPageNumber()
    {
        //filter page parameter given in URL
        $page = get_query_var(CriteoPluginSettings::FEED_PAGE_QUERY_VARIABLE, 1);
        $filter_options = array(
            'options' => array(
                'default' => 1,
                'min_range' => 1
            )
        );
        $page = filter_var($page, FILTER_VALIDATE_INT, $filter_options);
        if (!is_int($page)) {
            $page = 1;
        }
        return $page;
    }

    private static function getProductsPerPage()
    {
        $default = CriteoPluginSettings::getFeedQueryLimit();
        $productsPerPage = get_query_var(CriteoPluginSettings::FEED_PAGE_PRODUCTS_QUERY_VARIABLE, $default);
        $filter_options = array(
            'options' => array(
                'default' => $default,
                'min_range' => 1
            )
        );
        $productsPerPage = filter_var($productsPerPage, FILTER_VALIDATE_INT, $filter_options);
        if (!is_int($productsPerPage)) {
            $productsPerPage = $default;
        }
        return $productsPerPage;
    }
}
