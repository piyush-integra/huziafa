<?php

/********************************************************************
Version 2.0
	An Amazon Feed (Product Ads)
	Copyright 2014 Purple Turtle Productions. All rights reserved.
	license	GNU General Public License version 3 or later; see GPLv3.txt
By: Suzan 2019-03

********************************************************************/

/*** Changes to categories.txt should be relfected in initializeTemplateData() ***/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PWalmartFeed extends PCSVFeedEx
{
	function __construct ()
	{
		parent::__construct();
		$this->providerName = 'Walmart';
		$this->providerNameL = 'Walmart';
		$this->fileformat = 'xlsx';
		$this->fields = array();
		$this->fieldDelimiter = ",";

		$this->stripHTML = true;
        //Required
        //$this->addAttributeMapping('id', 'ProductID', true, true);
        $this->addAttributeMapping('title', 'productName', true, true); //product name (15-70 chars)
        $this->addAttributeMapping('sku', 'sku', true, true);

        $this->addAttributeMapping('description_short', 'shortDescription', true, true);
        $this->addAttributeMapping('product_type', 'shelfDescription', true, true);
        $this->addAttributeMapping('description_long', 'longDescription', true, true);
        $this->addAttributeMapping('brand', 'brand', true, true);

        $this->addAttributeMapping('upc', 'productIdentifiersUPC', true, true);
        $this->addAttributeMapping('gtin', 'productIdentifiersGTIN', true, true);
        $this->addAttributeMapping('ean', 'productIdentifiersEAN', true, true);

        $this->addAttributeMapping('tax_code', 'productTaxCode', true, true);
        $this->addAttributeMapping('stock_quantity', 'quantityAmount', true , true);
        $this->addAttributeMapping('lag_time', 'fulfillmentLagTime', true , true);

        $this->addAttributeMapping('regular_price', 'priceAmount', true, true);
        $this->addAttributeMapping('sale_price', 'reducedPriceAmount', true, true);
        $this->addAttributeMapping('category', '_category', true, true);
        $this->addAttributeMapping('sub_category', '_subcategory', true);

        $this->addAttributeMapping('feature_imgurl', 'MainImageURL', true, true); //begin with http://

        $this->addAttributeMapping('item_group_id' , 'variantGroupId' , true, false);
        $this->addAttributeMapping('attribute_name' , 'variantAttributeName' , true, false);
        $this->addAttributeMapping('attribute_value' , 'variantAttributeValue' , true, false);

       for ($i = 1;$i <= 10;$i++){
            $this->addAttributeMapping('additional_image_'.$i, 'additionalAssets_'.$i .'_assetUrl', true); //begin with http://
       }

        //Description and title: escape any quotes
       $this->addRule('csv_standard', 'CSVStandard', array('title' , 100));
        $this->addRule('csv_standard', 'CSVStandard', array('description'));

		$this->addAttributeDefault('price', 'none', 'PSalePriceIfDefined');
		$this->addAttributeDefault('local_category', 'none','PCategoryTree'); //store's local category tree
		$this->addRule('price_rounding','pricerounding');	
	}

	function formatProduct($product) {

	    $product->attributes['error_code'] = [];
        $walmart_category = explode('>' , $product->attributes['current_category']);
	    $product->attributes['category'] = $walmart_category[0];
	    if(isset($walmart_category[1])){
            $product->attributes['sub_category'] = $walmart_category[1];
        }

        if($product->attributes['stock_quantity'] == 0){
            $product->attributes['stock_quantity'] = NULL;
        }

        if($product->attributes['regular_price'] == 0 || $product->attributes['regular_price'] == '0.00' ){
            $product->attributes['regular_price'] = NULL;
        }
        if($product->attributes['sale_price'] == 0 || $product->attributes['sale_price'] == '0.00' ){
            $product->attributes['sale_price'] = NULL;
        }

            //max 10 additional images
            if ((count($product->imgurls) > 1)) {
                $i = 1;
                //$imgUrls = array_splice($product->imgUrls,0,10);
                foreach ($product->imgurls as $key  => $img){
                    $product->attributes['additional_image_'.$i] = $img;
                    $i++;
                }

            }
            if($product->attributes['isVariation']){
                if(isset($product->attributes['color'])){
                    $product->attributes['attribute_name'] = 'color';
                    $product->attributes['attribute_value'] = $product->attributes['color'];
                }
                if(isset($product->attributes['size'])){
                    $product->attributes['attribute_name'] = 'size';
                    $product->attributes['attribute_value'] = $product->attributes['size'];
                }

                if(isset($product->attributes['material'])){
                    $product->attributes['attribute_name'] = 'material';
                    $product->attributes['attribute_value'] = $product->attributes['material'];
                }
            }
        return parent::formatProduct($product);
	} //format Product
}