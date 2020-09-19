<?php
/**
 * Created by PhpStorm.
 * User: wahid
 * Date: 5/23/20
 * Time: 10:07 AM
 */
################ Spartoo #################
/**
 * Modify Spartoo feed Parent/Child attribute value.
 *
 * @param $attribute_value
 * @param $product
 * @param $feed_config
 * @return string
 */
function woo_feed_spartoo_attribute_value_modify($attribute_value, $product, $feed_config){
    if('spartoo.fi'===$feed_config['provider'] ){
        if('variation'===$attribute_value){
            return "child";
        }else{
            return "parent";
        }
    }
    return $attribute_value;
}
add_filter('woo_feed_get_type_attribute','woo_feed_spartoo_attribute_value_modify',10,3);
################ Availability #################
/**
 * Modify  Availability value
 *
 * @param $attribute_value
 * @param $product
 * @param $feed_config
 * @return string
 */
function woo_feed_availability_attribute_value_modify($attribute_value, $product, $feed_config){
    if('skroutz'===$feed_config['provider'] || 'bestprice'===$feed_config['provider']){
        if('in stock'===$attribute_value){
            return "Y";
        }else{
            return "N";
        }
    }elseif ('pricerunner'===$feed_config['provider']){
        if('in stock'===$attribute_value){
            return "Yes";
        }else{
            return "No";
        }
    }
    return $attribute_value;
}
add_filter('woo_feed_get_availability_attribute','woo_feed_availability_attribute_value_modify',10,3);
################ Best Price #################
/**
 * Replace BestPrice categoryPath value from > to ,
 *
 * @param $attribute_value
 * @param $product
 * @param $feed_config
 * @return string
 */
function woo_feed_get_bestprice_categoryPath_attribute_value_modify($attribute_value, $product, $feed_config){
    $attribute_value=str_replace('>',', ',$attribute_value);
    return $attribute_value;
}
add_filter('woo_feed_get_bestprice_product_type_attribute','woo_feed_get_bestprice_categoryPath_attribute_value_modify',10,3);