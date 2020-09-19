<?php

defined('ABSPATH') or die('Forbidden');

class ProductFeedLoader
{

    const GOOGLE_SPEC_IMAGES_LIMIT = 10;
    const VARIANTS_FIELD_PREFIX = 'attribute_';

    public static function getProductInfo($page, $productsPerPage)
    {
        global $wpdb;
        $sqlQuery = self::createPaginatedSqlQuery($page, $productsPerPage, $wpdb);
        $result = $wpdb->get_results($sqlQuery, OBJECT);

        if (count($result) == 0) {
            return [];
        }

        foreach ($result as $product) {
            self::loadProductMeta($product);

            if (self::isProductVariation($product)) {
                $parent = new stdClass;
                $parent->id = $product->parent_id;
                self::loadProductMeta($parent);
                self::mergeValuesFromParent($product, $parent);
            } else if (self::isBaseProductForProductVariations($product, $wpdb)) {
                self::setItemGroupId($product, $product);
            }

            self::setReviewsAndRating($product);
            self::setLinks($product);
            self::setProductType($product);
            self::setCurrency($product);
            self::setProductIdOrSku($product);
            //This must come last because some of the fields are used above for computations of other fields.
            self::removeAdditionalFields($product);
        }
        return $result;
    }

    private static function createPaginatedSqlQuery($page, $productsPerPage, $wpdb)
    {
        $limit_query = self::createLimitClause($page, $productsPerPage);
        $hiddenProductsFiltering = self::getHiddenProductsFiltering();

        //Query database directly for products info - it's optimal because it queries the database
        //and gets only necessary data so the memory footprint of the app is minimal.
        //Also some of the filtering can't be done with the get_posts() method (e.g check for parent password or parent status)
        //directly into the query to respect pagination settings.
        //Note: for variations the description contains the concatenation of both the parent and the variation descriptions
        $query = <<<EOT
            SELECT DISTINCT(sub.id), sub.title, sub.description, sub.woo_product_type, sub.parent_id FROM (
            SELECT p.ID id,
                p.post_title title,
                IF(p.post_parent = 0, p.post_content, CONCAT(parent.post_content, ' ', p.post_content)) description,
                p.post_type woo_product_type,
                p.post_parent parent_id,
                IF(p.post_parent = 0, 1, 0) isParent,
                (SELECT COUNT(*) FROM %wp_posts% c WHERE c.post_parent = p.id AND c.post_parent <> c.id and c.post_status = 'publish' AND c.post_type IN ('product', 'product_variation')) childrenCount
            FROM %wp_posts% p
            LEFT JOIN %wp_posts% parent
               ON p.post_parent = parent.id
            WHERE p.post_status = 'publish'
            AND (parent.post_status IS NULL OR parent.post_status = 'publish')
            AND p.post_type IN ('product', 'product_variation')
            AND p.post_password = ''
            AND (parent.post_password IS NULL OR parent.post_password = '')
            $hiddenProductsFiltering) sub
            WHERE sub.isParent = 0 OR (sub.isParent = 1 AND sub.childrenCount = 0)
            GROUP BY sub.id
            ORDER BY sub.id ASC
            $limit_query
EOT;

        $sql = self::replacePrefixes($wpdb, $query);
        return $sql;
    }

    private static function getHiddenProductsFiltering()
    {
        if (CriteoPluginSettings::isVisibilityImplementedAsWpTerm()) {
            //Starting with WooCommerce 3.0.0 the catalog visibility is implemented as term.
            //If both  'exclude-from-catalog' and 'exclude-from-search' terms are associated to the product
            //then it means that it's completely hidden.
            return <<<EOT
            AND (SELECT COUNT(*) FROM %wp_term_relationships% tr
                 INNER JOIN %wp_term_taxonomy% AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                 INNER JOIN %wp_terms% AS t ON t.term_id = tt.term_id
                 WHERE tr.object_id = p.id
                 AND t.name IN ('exclude-from-catalog', 'exclude-from-search')) < 2

            AND (SELECT COUNT(*) FROM %wp_term_relationships% tr
                 INNER JOIN %wp_term_taxonomy% AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                 INNER JOIN %wp_terms% AS t ON t.term_id = tt.term_id
                 WHERE tr.object_id = parent.id
                 AND t.name IN ('exclude-from-catalog', 'exclude-from-search')) < 2
EOT;
        } else {
            //For versions older than WooCommerce 3.0.0. the catalog visibility is implemented as a meta value.
            return <<<EOT
            AND (SELECT COUNT(*) FROM %wp_postmeta% m
                WHERE m.meta_key = '_visibility'
                AND m.meta_value = 'hidden'
                AND m.post_id = p.id) = 0

            AND (SELECT COUNT(*) FROM %wp_postmeta% m
                WHERE m.meta_key = '_visibility'
                AND m.meta_value = 'hidden'
                AND m.post_id = parent.id) = 0
EOT;
        }
    }

    private static function createLimitClause($page, $productsPerPage)
    {
        $start = ($page - 1) * $productsPerPage;
        return 'LIMIT ' . $start . ' , ' . $productsPerPage;
    }

    private static function isProductVariation($product)
    {
        return $product->woo_product_type == 'product_variation';
    }

    private static function isBaseProductForProductVariations($product, $wpdb)
    {
        $query = <<<EOT
            SELECT COUNT(DISTINCT(p.ID)) childs
            FROM %wp_posts% p
            WHERE p.post_parent = $product->id
            AND p.post_type = 'product_variation'
EOT;
        $sql = self::replacePrefixes($wpdb, $query);
        $result = $wpdb->get_row($sql, OBJECT);
        return $result->childs > 0;
    }

    private static function loadProductMeta($product)
    {
        $metadata = get_post_meta( $product->id);
        $product->price = $metadata['_regular_price'][0];
        $product->sale_price =  $metadata['_sale_price'][0];
        if (isset($metadata['_stock_status'])) {
            $product->availability = $metadata['_stock_status'][0];
            $product->availability = ($product->availability == 'instock') ? 'in stock' : 'out of stock';
        } else {
            $product->availability = '';
        }
        $product->sku = $metadata['_sku'][0];

        $product->shipping_weight = $metadata['_weight'][0];
        $product->shipping_length = $metadata['_length'][0];
        $product->shipping_width = $metadata['_width'][0];
        $product->shipping_height = $metadata['_height'][0];
        $product->image_id = $metadata['_thumbnail_id'][0];

        if (isset($metadata['_product_image_gallery'])) {
            $product->additional_images_ids = array_unique($metadata['_product_image_gallery']);
        } else {
            $product->additional_images_ids = [];
        }

        foreach ($metadata as $field => $value) {
            if (self::isVariantField($field)) {
                //required for determining the product link, later will be unset
                $product->$field = $value[0];
                $field = str_replace(self::VARIANTS_FIELD_PREFIX, '', $field);
                //this is the field that actually gets serialized into catalog
                $product->$field = $value[0];
            }
        }
    }

    private static function replacePrefixes($wpdb, $query)
    {
        //replace prefix if any
        $change = array(
            '%wp_posts%' => $wpdb->prefix . 'posts',
            '%wp_postmeta%' => $wpdb->prefix . 'postmeta',
            '%wp_term_relationships%' => $wpdb->prefix . 'term_relationships',
            '%wp_term_taxonomy%' => $wpdb->prefix . 'term_taxonomy',
            '%wp_terms%' => $wpdb->prefix . 'terms'
        );
        $sql = str_replace(array_keys($change), array_values($change), $query);
        return $sql;
    }

    private static function mergeValuesFromParent($product, $parent)
    {
        foreach ($product as $field => $value) {
            if (empty($value)) {
                if (isset($parent->$field)) {
                    $product->$field = $parent->$field;
                }
            }
        }
        self::setItemGroupId($product, $parent);
    }

    private static function setItemGroupId($product, $parent)
    {
        if (CriteoPluginSettings::useSkuForProductID()) {
            $product->item_group_id = $parent->sku;
        } else {
            $product->item_group_id = $parent->id;
        }
    }

    private static function setReviewsAndRating($product)
    {
        if (CriteoPluginSettings::isRatingPreCalculated()) {
            if(self::isProductVariation($product)) {
                $product->product_rating = get_post_meta($product->parent_id, '_wc_average_rating', true);
                $product->number_of_reviews = get_post_meta($product->parent_id, '_wc_review_count', true);
            }
            else {
                $product->product_rating = get_post_meta($product->id, '_wc_average_rating', true);
                $product->number_of_reviews = get_post_meta($product->id, '_wc_review_count', true);
            }
        } else {
            if(self::isProductVariation($product))
                $comments = get_approved_comments($product->parent_id);
            else
                $comments = get_approved_comments($product->id);

            $numberOfComments = sizeof($comments);
            $commentsSum = 0;
            foreach ($comments as $comment) {
                $commentsSum += get_comment_meta($comment->comment_ID, 'rating', true);
            }

            $product->number_of_reviews = $numberOfComments;
            if ($numberOfComments > 0) {
                $product->product_rating = number_format($commentsSum / $numberOfComments, 2);
            }
        }
    }

    private static function setProductIdOrSku($product)
    {
        if (CriteoPluginSettings::useSkuForProductID()) {
            $product->id = $product->sku;
        }
    }

    private static function setLinks($product)
    {
        if (self::isProductVariation($product) && !CriteoPluginSettings::isWooCommerceVersionWithVariationLinks()) {
            $product->link = self::getVariationLink($product);
        } else {
            $product->link = get_permalink($product->id);
        }

        $product->image_link = wp_get_attachment_url($product->image_id);
        $product->additional_image_link = [];
        foreach ($product->additional_images_ids as $imageId) {
            if (sizeof($product->additional_image_link) < self::GOOGLE_SPEC_IMAGES_LIMIT) {
                array_push($product->additional_image_link, wp_get_attachment_url($imageId));
            } else {
                break;
            }
        }
        unset($product->image_id);
        unset($product->additional_images_ids);
    }

    private static function getVariationLink($product)
    {
        $parentLink = get_permalink($product->parent_id);
        $variationAttributes = '';
        foreach ($product as $field => $value) {
            if (self::isVariantField($field)) {
                $variationAttributes .= $field . '=' . $value;
            }
        }
        if (!empty($variationAttributes)) {
            return $parentLink . '?' . $variationAttributes;
        } else {
            return $parentLink;
        }
    }

    private static function setProductType($product)
    {
        if (!empty($product->parent_id) && $product->parent_id > 0) {
            $terms = get_the_terms($product->parent_id, 'product_cat');
        } else {
            $terms = get_the_terms($product->id, 'product_cat');
        }

        $categories = [];
        if (!empty($terms)) {
            foreach ($terms as $term) {
                array_push($categories, $term->name);
            }
        }
        $product->product_type = implode(' > ', $categories);
    }

    private static function removeAdditionalFields($product)
    {
        unset($product->woo_product_type);
        unset($product->sku);
        unset($product->visibility);
        unset($product->parent_id);
        foreach ($product as $field => $value) {
            if (self::isVariantField($field)) {
                unset($product->$field);
            }
        }
    }

    private static function isVariantField($fieldName) {
        return 0 === strpos($fieldName, self::VARIANTS_FIELD_PREFIX);
    }

    private static function setCurrency($product) {
        $currency = get_woocommerce_currency();
        if (!empty($product->price)) {
            $product->price = $product->price . ' ' . $currency;
        }
        if (!empty($product->sale_price)) {
            $product->sale_price = $product->sale_price . ' ' . $currency;
        }
    }
}