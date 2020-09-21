<?php

/**
 * Theme functions and definitions.
 * This child theme is purposed to Rey Theme
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 */
/*
	* If your child theme has more than one .css file (eg. ie.css, style.css, main.css) then
	* you will have to make sure to maintain all of the parent theme dependencies.
	*
	* Make sure you're using the correct handle for loading the parent theme's styles.
	* Failure to use the proper tag will result in a CSS file needlessly being loaded twice.
	* This will usually not affect the site appearance, but it's inefficient and extends your page's loading time.
	*
	* @link https://codex.wordpress.org/Child_Themes
	*/
/**
 * Load styles
 */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('rey-wp-style', get_template_directory_uri() . '/style.css', false, wp_get_theme()->parent()->get('Version'));
    wp_enqueue_style('rey-wp-style-child', get_stylesheet_uri());
});
// developer function
function ss_wc_change_uae_currency_symbol($currency_symbol, $currency)
{
    switch ($currency) {
        case 'AED':
            $currency_symbol = 'AED';
            break;
    }
    return $currency_symbol;
}
add_filter('woocommerce_currency_symbol', 'ss_wc_change_uae_currency_symbol', 10, 2);
//Remove WooCommerce Tabs - this code removes all 3 tabs - to be more specific just remove actual unset lines 
add_filter('woocommerce_product_tabs', 'woo_remove_product_tabs', 98);
function woo_remove_product_tabs($tabs)
{
    unset($tabs['description']);          // Remove the description tab
    unset($tabs['reviews']);             // Remove the reviews tab
    unset($tabs['additional_information']);      // Remove the additional information tab
    return $tabs;
}
// Removing sale badge
add_filter('woocommerce_sale_flash', '__return_false');
// Removing archives prices
remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
// Add the saved discounted percentage to variable products
add_filter('woocommerce_format_sale_price', 'add_sale_price_percentage', 20, 3);
function add_sale_price_percentage($price, $regular_price, $sale_price)
{
    // Strip html tags and currency (we keep only the float number)
    $regular_price = strip_tags($regular_price);
    $regular_price = (float) preg_replace('/[^0-9.]+/', '', $regular_price);
    $sale_price = strip_tags($sale_price);
    $sale_price = (float) preg_replace('/[^0-9.]+/', '', $sale_price);
    // Percentage text and calculation
    $percentage  = __(' ', 'woocommerce') . ' ';
    $percentage .= round(($regular_price - $sale_price) / $regular_price * 100);
    // return on sale price range with "Save " and the discounted percentage
    return $price . ' <span class="save-percent">' . $percentage . '% OFF</span>';
}
/**
 * Display a custom text field for dimention
 * @since 1.0.0
 */

function cfwc_create_custom_field()
{
    woocommerce_wp_textarea_input(
        array(
            'id' => 'custom_text_field_title',
            'label' => __('Dimension', 'cfwc'),
            'class' => 'cfwc-custom-field',
            'desc_tip' => true,
            'description' => __('Enter the Dimension of your custom text field.', 'ctwc'),
        )
    );
}
add_action('woocommerce_product_options_general_product_data', 'cfwc_create_custom_field');

function cfwc_save_custom_field($pid)
{
    $product = wc_get_product($pid);
    $title = isset($_POST['custom_text_field_title']) ? $_POST['custom_text_field_title'] : '';
    $product->update_meta_data('custom_text_field_title', sanitize_text_field($title));
    $product->save();
}
add_action('woocommerce_process_product_meta', 'cfwc_save_custom_field');
/**
 * Display custom field on the front end
 * @since 1.0.0
 */
//add_action( 'woocommerce_single_product_summary', 'cfwc_display_custom_field' ); 
add_action('woocommerce_single_product_summary', 'huzaifa_single_custom_action', 25);
function huzaifa_single_custom_action()
{
    $product_id         = get_the_ID();
    $term_list          = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));
    $product_s          = wc_get_product(get_the_ID());
    if (in_array(89111, $term_list) && !empty($product_s)) {
        return;
    }
    global $post;
    $product = wc_get_product($post->ID);
    $product_id = get_the_ID();
    //$title = $product->get_meta( 'custom_text_field_title' );
    $product_dimension = get_field('product_dimension', $product_id);
?>
    <div class="woocommerce-product-details__short-description dimension_container">
        <?php if ($product->is_type('variable')) { ?>
            <strong>DIMENSIONS</strong>
            <!-- <div class="woocommerce-variation-dimention"></div>   -->
            <p><?php echo $product_dimension; ?></p>
        <?php } else { ?>
            <strong>DIMENSIONS</strong>
            <p><?php echo $product_dimension; ?></p>
        <?php } ?>
    </div>
    <?php $product_id = get_the_ID();
    $post = get_post($product_id);
    $getPost =  $post->post_content; ?>
    <div class="product_extra_popup">
        <?php if ($product->is_type('variable')) { ?>
            <div class="accordion-container">
                <?Php $postwithbreaks = wpautop($getPost, true); ?>
                <?php if ($postwithbreaks) { ?>
                    <div class="set">
                        <a data-href="#">Materials and finish <i class="fas fa-caret-down"></i></a>
                        <!-- <div class="content_variable content">  </div>                              -->
                        <div class="content">
                            <?php echo $postwithbreaks; ?>
                        </div>
                    </div>
                <?php } ?>
            </div>

        <?Php } else { ?>
            <?Php $postwithbreaks = wpautop($getPost, true); ?>
            <?php if ($postwithbreaks) { ?>
                <div class="accordion-container">
                    <div class="set">
                        <a data-href="#">Materials and finish <i class="fas fa-caret-down"></i></a>
                        <div class="content">
                            <?php echo $postwithbreaks; ?>
                        </div>
                    </div>
                </div>
        <?php }
        } ?>
    </div>
    <?php }
add_action('woocommerce_single_product_summary', 'custom_single_product_styles', 2);
function custom_single_product_styles()
{
    global $product;
    // Only for variable products
    if (!$product->is_type('variable')) {
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
        add_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 15);
    } else {
        // Change the short description location after the add-to-cart button
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
        add_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 15);
        // Removing the price range
        //remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
        // Change the price location above variation attribute select fields
        remove_action('woocommerce_single_variation', 'woocommerce_single_variation', 10);
        add_action('woocommerce_before_variations_form', 'woocommerce_single_variation', 10);
        // Styles to display the variation attribute select fields at the left of Quantity/Add to cart
        // (Can be removed and inserted in styles.css file)
    ?>
        <?php
    }
}
add_filter('woocommerce_get_stock_html', '__return_empty_string');
add_action('woocommerce_single_product_summary', 'hooks_open_div', 7);
function hooks_open_div()
{
    echo '<div class="pr_summery">';
}
add_action('woocommerce_single_product_summary', 'hooks_close_div', 33);
function hooks_close_div()
{
    echo '</div>';
}
// Show Product Additional information tab content after Product summary on Single Page 
add_action('woocommerce_single_product_summary', 'show_attributes', 20);
function show_attributes()
{
    global $product;
    if ($product->is_type('simple')) {
        $product->list_attributes();
    } else {
        $product_id         = get_the_ID();
        $term_list          = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));
        $product_s          = wc_get_product(get_the_ID());
        if (in_array(89, $term_list) && !empty($product_s)) {
            $product->list_attributes();
        }
    }
}
add_action('woocommerce_variable_price_html', 'iconic_template_loop_add_to_cart', 10);
function iconic_template_loop_add_to_cart()
{
    global $post, $woocommerce, $product;
    if (!$product->is_type('variable')) {
        return;
    }
    $wcv_max_price = $product->get_variation_price('max', true);
    $wcv_min_price = $product->get_variation_price('min', true);
    if ($product->is_type('variable')) {
        $prices        = $product->get_variation_prices(true);
        $min_price     = current($prices['regular_price']);
        $min_price = round($min_price, 0);
        $max_price     = end($prices['regular_price']);
        $max_price = round($max_price, 0);
        $min_price_s   = current($prices['sale_price']);
        $min_price_s = round($min_price_s, 0);
        $max_price_s   = end($prices['sale_price']);
        $max_price_s = round($max_price_s, 0);
        if ($wcv_max_price != $wcv_min_price) { ?>
            <span class="price variabledrop">
                <?php if (!empty($max_price) && !empty($max_price_s)) { ?>
                    <?php if ($max_price_s == $max_price) { ?>
                        <ins><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">AED</span><?php echo number_format($max_price_s, 0); ?></span></ins>
                    <?php   } else { ?>
                        <del><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">AED</span><?php echo number_format($max_price, 0); ?></span></del>
                        <ins><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">AED</span><?php echo number_format($max_price_s, 0); ?></span></ins>
                    <?php   }
                } else {
                    if (!empty($max_price)) { ?>
                        <del><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">AED</span><?php echo number_format($max_price, 0); ?></span></del>
                    <?php   }
                    if (!empty($max_price_s)) { ?>
                        <ins><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">AED</span><?php echo number_format($max_price_s, 0); ?></span></ins>
                <?php  }
                } ?>
            </span>
        <?php  } else { ?>
            <?php if (!empty($max_price) && !empty($max_price_s)) { ?>
                <?php if ($max_price_s == $max_price) { ?>
                    <ins><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">AED</span><?php echo number_format($max_price_s, 0); ?></span></ins>
                <?php   } else { ?>
                    <del><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">AED</span><?php echo number_format($max_price, 0); ?></span></del>
                    <ins><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">AED</span><?php echo number_format($max_price_s, 0); ?></span></ins>
                    <?php $percentage  = __(' ', 'woocommerce') . ' ';
                    $percentage .= round(($max_price - $max_price_s) / $max_price * 100); ?>
                    <span class="save-percent"><?php echo  $percentage; ?> % OFF</span>
                <?php   }
            } else {
                if (!empty($max_price)) { ?><del><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">AED</span><?php echo number_format($max_price, 2); ?></span></del>
                <?php   }
                if (!empty($max_price_s)) { ?>
                    <ins><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">AED</span><?php echo number_format($max_price_s, 2); ?></span></ins>
                <?php  }
                $percentage  = __(' ', 'woocommerce') . ' ';
                $percentage .= round(($max_price - $max_price_s) / $max_price * 100); ?>
                <span class="save-percent"><?php echo  $percentage; ?> % OFF</span>
            <?php
            } ?>
    <?php }
    }
}
// user created
add_action('woocommerce_created_customer', 'woocommerce_created_customer_admin_notification');
function woocommerce_created_customer_admin_notification($customer_id)
{
    wp_send_new_user_notifications($customer_id, 'admin');
}
// -----------------------------------------
// 1. Add custom field input @ Product Data > Variations > Single Variation

add_action('woocommerce_variation_options_pricing', 'bbloomer_add_custom_field_to_variations', 10, 3);
function bbloomer_add_custom_field_to_variations($loop, $variation_data, $variation)
{
    woocommerce_wp_text_input(array(
        'id' => 'custom_field_dimention[' . $loop . ']',
        'class' => 'short',
        'label' => __('Dimension', 'woocommerce'),
        'value' => get_post_meta($variation->ID, 'custom_field_dimention', true)
    ));
}
// -----------------------------------------
// 2. Save custom field on product variation save 
add_action('woocommerce_save_product_variation', 'bbloomer_save_custom_field_variations', 10, 2);
function bbloomer_save_custom_field_variations($variation_id, $i)
{
    $custom_field_dimention = $_POST['custom_field_dimention'][$i];
    if (isset($custom_field_dimention)) update_post_meta($variation_id, 'custom_field_dimention', esc_attr($custom_field_dimention));
}
// -----------------------------------------
// 3. Store custom field value into variation data 
add_filter('woocommerce_available_variation', 'bbloomer_add_custom_field_variation_data');
function bbloomer_add_custom_field_variation_data($variations)
{
    $variations['custom_field_dimention'] = '<div class="woocommerce_custom_field_dimention"><span>' . get_post_meta($variations['variation_id'], 'custom_field_dimention', true) . '</span></div>';
    return $variations;
}
add_action('woocommerce_checkout_fields', 'wps_add_select_checkout_field');
function wps_add_select_checkout_field($fields)
{
    $fields['billing']['shipping_location'] = array(
        'type'          => 'select',
        'class'         => array('wps-drop'),
        'label'         => __('Delivery Location'),
        'required'      => TRUE,
        'options'       => array('' => __('Select Delivery Location', 'woocommerce'), 'Dubai' => __('Dubai', 'woocommerce'), 'Sharjah' => __('Sharjah', 'woocommerce'), 'Ajman' => __('Ajman', 'woocommerce'), 'Abu Dhabi' => __('Abu Dhabi', 'woocommerce'), 'Al Sila' => __('Al Sila ( Abu Dhabi )', 'woocommerce'), 'Madinat Zayed' => __('Madinat Zayed ( Abu Dhabi )', 'woocommerce'), 'Bu Hasa' => __('Bu Hasa ( Abu Dhabi )', 'woocommerce'), 'Al Ruwais' => __('Al Ruwais ( Abu Dhabi )', 'woocommerce'), 'Asab' => __('Asab ( Abu Dhabi )', 'woocommerce'), 'Al Khis' => __('Al Khis ( Abu Dhabi )', 'woocommerce'), 'Ras Al Khaimah' => __('Ras Al Khaimah', 'woocommerce'), 'Al Hamra' => __('Al Hamra ( Ras Al Khaimah )', 'woocommerce'), 'Fujairah' => __('Fujairah', 'woocommerce'), 'Al Ain' => __('Al Ain', 'woocommerce'), 'Al Wagan' => __('Al Wagan ( Al Ain )', 'woocommerce'), 'Umm Al Quwain' => __('Umm Al Quwain', 'woocommerce'), 'Khorfakan' => __('Khorfakan', 'woocommerce')),
        'priority' => 1,
    );
    return $fields;
}
add_action('woocommerce_checkout_process', 'huzaifa_checkout_field_process');
function huzaifa_checkout_field_process()
{
    if ($_POST['shipping_location'] == "blank")
        wc_add_notice(__('Select Delivery Location.'), 'error');
}
add_action('woocommerce_checkout_update_order_meta', 'saving_checkout_cf_data');
function saving_checkout_cf_data($order_id)
{
    $shipping_location = $_POST['shipping_location'];
    if (!empty($shipping_location))
        update_post_meta($order_id, 'huzaifa_order_shipping_location', sanitize_text_field($shipping_location));
}
add_action('woocommerce_admin_order_data_after_billing_address', 'huzaifa_checkout_field_display_admin_order_billing', 10, 1);
function huzaifa_checkout_field_display_admin_order_billing($order)
{
    $delivery_option = get_post_meta($order->id, 'huzaifa_order_shipping_location', true);
    echo '<p><strong>' . __('Delivery Location') . ':</strong> ' . $delivery_option . '</p>';
}
add_action('wp_footer', 'custom_checkout_script');
function custom_checkout_script()
{
    $carttotalamont = WC()->cart->subtotal;
    // Only on checkout page
    if (!is_checkout() && is_wc_endpoint_url('order-received'))
        return;
    ?>
    <script type="text/javascript">
        jQuery(function($) {
            var a = 'select#shipping_location',
                b = 'input[name^="shipping_location[0]"]',
                c = '#shipping_method_0_',
                dubai100 = 'flat_rate7',
                dubaifree = 'free_shipping8',
                ummalquwain100 = 'flat_rate9';
            ummalquwainfree = 'free_shipping10';
            ajman100 = 'flat_rate11',
                ajmanfree = 'free_shipping12',
                sharjah100 = 'flat_rate13';
            sharjahfree = 'free_shipping14';
            abudhabhi100 = 'flat_rate15',
                abudhabhifree = 'free_shipping16',
                alain100 = 'flat_rate17';
            alainfree = 'free_shipping18';
            fujairah100 = 'flat_rate19',
                fujairahfree = 'free_shipping20',
                khorfakan100 = 'flat_rate21';
            khorfakanfree = 'free_shipping22';
            rasalkhaimah100 = 'flat_rate23',
                rasalkhaimahfree = 'free_shipping24',
                alsila100 = 'flat_rate25';
            alsilafree = 'free_shipping26';
            madinatzayed100 = 'flat_rate27',
                madinatzayedfree = 'free_shipping28',
                buhasa100 = 'flat_rate29';
            buhasafree = 'free_shipping30';
            alruwais100 = 'flat_rate31',
                alruwaisfree = 'free_shipping32',
                asab100 = 'flat_rate33';
            asabfree = 'free_shipping34';
            alkhis100 = 'flat_rate35',
                alkhisfree = 'free_shipping36',
                alhamra100 = 'flat_rate37';
            alhamrafree = 'free_shipping38';
            alwagan100 = 'flat_rate39';
            alwaganfree = 'free_shipping40';
            freeshipping = 'free_shipping41';
            jQuery('#shipping_location').prop('selectedIndex', 0);
            jQuery(c + freeshipping).prop("checked", true);
            // Live action event: On Select "delivery_method" change
            jQuery(a).change(function() {
                var carttotal = '<?php echo $carttotalamont; ?>';
                if (jQuery(this).val() == 'Dubai' && carttotal >= 2000) {
                    jQuery(c + dubaifree).prop("checked", true);
                } else if (jQuery(this).val() == 'Dubai' && carttotal < 2000) {
                    jQuery(c + dubai100).prop("checked", true);
                } else if (jQuery(this).val() == 'Umm Al Quwain' && carttotal >= 2000) {
                    jQuery(c + ummalquwainfree).prop("checked", true);
                } else if (jQuery(this).val() == 'Umm Al Quwain' && carttotal < 2000) {
                    jQuery(c + ummalquwain100).prop("checked", true);
                } else if (jQuery(this).val() == 'Ajman' && carttotal >= 2000) {
                    jQuery(c + ajmanfree).prop("checked", true);
                } else if (jQuery(this).val() == 'Ajman' && carttotal < 2000) {
                    jQuery(c + ajman100).prop("checked", true);
                } else if (jQuery(this).val() == 'Sharjah' && carttotal >= 2000) {
                    jQuery(c + sharjahfree).prop("checked", true);
                } else if (jQuery(this).val() == 'Sharjah' && carttotal < 2000) {
                    jQuery(c + sharjah100).prop("checked", true);
                } else if (jQuery(this).val() == 'Abu Dhabi' && carttotal >= 2000) {
                    jQuery(c + abudhabhifree).prop("checked", true);
                } else if (jQuery(this).val() == 'Abu Dhabi' && carttotal < 2000) {
                    jQuery(c + abudhabhi100).prop("checked", true);
                } else if (jQuery(this).val() == 'Al Ain' && carttotal >= 2000) {
                    jQuery(c + alainfree).prop("checked", true);
                } else if (jQuery(this).val() == 'Al Ain' && carttotal < 2000) {
                    jQuery(c + alain100).prop("checked", true);
                } else if (jQuery(this).val() == 'Fujairah' && carttotal >= 2000) {
                    jQuery(c + fujairahfree).prop("checked", true);
                } else if (jQuery(this).val() == 'Fujairah' && carttotal < 2000) {
                    jQuery(c + fujairah100).prop("checked", true);
                } else if (jQuery(this).val() == 'Khorfakan' && carttotal >= 2000) {
                    jQuery(c + khorfakanfree).prop("checked", true);
                } else if (jQuery(this).val() == 'Khorfakan' && carttotal < 2000) {
                    jQuery(c + khorfakan100).prop("checked", true);
                } else if (jQuery(this).val() == 'Ras Al Khaimah' && carttotal >= 2000) {
                    jQuery(c + rasalkhaimahfree).prop("checked", true);
                } else if (jQuery(this).val() == 'Ras Al Khaimah' && carttotal < 2000) {
                    jQuery(c + rasalkhaimah100).prop("checked", true);
                } else if (jQuery(this).val() == 'Al Sila' && carttotal >= 20000) {
                    jQuery(c + alsilafree).prop("checked", true);
                } else if (jQuery(this).val() == 'Al Sila' && carttotal < 20000) {
                    jQuery(c + alsila100).prop("checked", true);
                } else if (jQuery(this).val() == 'Madinat Zayed' && carttotal >= 20000) {
                    jQuery(c + madinatzayedfree).prop("checked", true);
                } else if (jQuery(this).val() == 'Madinat Zayed' && carttotal < 20000) {
                    jQuery(c + madinatzayed100).prop("checked", true);
                } else if (jQuery(this).val() == 'Bu Hasa' && carttotal >= 20000) {
                    jQuery(c + buhasafree).prop("checked", true);
                } else if (jQuery(this).val() == 'Bu Hasa' && carttotal < 20000) {
                    jQuery(c + buhasa100).prop("checked", true);
                } else if (jQuery(this).val() == 'Al Ruwais' && carttotal >= 20000) {
                    jQuery(c + alruwaisfree).prop("checked", true);
                } else if (jQuery(this).val() == 'Al Ruwais' && carttotal < 20000) {
                    jQuery(c + alruwais100).prop("checked", true);
                } else if (jQuery(this).val() == 'Asab' && carttotal >= 20000) {
                    jQuery(c + asabfree).prop("checked", true);
                } else if (jQuery(this).val() == 'Asab' && carttotal < 20000) {
                    jQuery(c + asab100).prop("checked", true);
                } else if (jQuery(this).val() == 'Al Khis' && carttotal >= 20000) {
                    jQuery(c + alkhisfree).prop("checked", true);
                } else if (jQuery(this).val() == 'Al Khis' && carttotal < 20000) {
                    jQuery(c + alkhis100).prop("checked", true);
                } else if (jQuery(this).val() == 'Al Hamra' && carttotal >= 20000) {
                    jQuery(c + alhamrafree).prop("checked", true);
                } else if (jQuery(this).val() == 'Al Hamra' && carttotal < 20000) {
                    jQuery(c + alhamra100).prop("checked", true);
                } else if (jQuery(this).val() == 'Al Wagan' && carttotal >= 20000) {
                    jQuery(c + alwaganfree).prop("checked", true);
                } else if (jQuery(this).val() == 'Al Wagan' && carttotal < 20000) {
                    jQuery(c + alwagan100).prop("checked", true);
                } else {
                    jQuery(c + freeshipping).prop("checked", true);
                }
                jQuery(document.body).trigger('update_checkout');
            });
            //Live action event: On Shipping method change
            jQuery('form.checkout').on('change', b, function() {
                // If Free shipping is not selected, we change "delivery_method" slect field to "post"
                if (jQuery(this).val() != f)
                    jQuery(a).val('ajman');
                else
                    jQuery(a).val('pickupatshop');
            });
        });
    </script>
<?php
}
function disable_shipping_calc_on_cart($show_shipping)
{
    if (is_cart()) {
        return false;
    }
    return $show_shipping;
}
add_filter('woocommerce_cart_ready_to_calc_shipping', 'disable_shipping_calc_on_cart', 99); ?>
<?php /* // Move Variation Description to Product Description Tab
add_action( 'wp_footer', 'ec_child_modify_wc_variation_desc_position' );
function ec_child_modify_wc_variation_desc_position() { ?>
    <script>
    (function($) {
        $(document).on( 'found_variation', function() {
            var desc = $( '.woocommerce-variation.single_variation' ).find( '.woocommerce-variation-description' ).html();
            var dimention = $( '.woocommerce-variation.single_variation' ).find( '.woocommerce-variation-custom_field_dimention' ).html();
            var $material_finish = $( '.accordion-container' );
            var $product_dimention = $( '.dimension_container' );
            $material_finish.find( '.content_variable' ).html( desc );
            $product_dimention.find( '.woocommerce-variation-dimention' ).html( dimention );
        });
    })( jQuery );
    </script>
    <style>
        form.variations_form .woocommerce-variation-description {
            display: none;
        }
        form.variations_form .woocommerce-variation-custom_field_dimention {
            display: none;
        }
    </style>
<?php } */ ?>
<?php add_image_size('custom-size', 400, 280);
require get_stylesheet_directory() . '/stock_report.php';
/**
 * Change number of upsells output
 */
add_filter('woocommerce_upsell_display_args', 'wc_change_number_related_products', 20);

function wc_change_number_related_products($args)
{

    $args['posts_per_page'] = 3;
    $args['columns'] = 3; //change number of upsells here
    return $args;
}
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
add_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products_custom', 20);
function woocommerce_output_related_products_custom()
{
    $relative_product = '';
    $relative_product        = get_field('select_relative_products'); ?>
    <?php if ($relative_product) { ?><h2 class="relativehead">Related products</h2> <?php } ?>

    <?php if ($relative_product) { ?>
        <h2 class="relativehead">Related products</h2> 
        <ul data-cols="3" class=" relativeproduct products columns-tablet-2 columns-mobile-2 rey-wcGap-narrow rey-wcGrid-default columns-3">
            <?php foreach ($relative_product as $key => $product_id_product) {
                global $woocommerce;
                $product            = new WC_Product($product_id_product);
                $product_image          = get_the_post_thumbnail_url($product_id_product);
                $product_permalink      = get_permalink($product_id_product);
                $product_title          = get_the_title($product_id_product);
                $product_price          = $product->get_price_html(); ?>
                <?php $post_object = get_post($product_id_product);
                setup_postdata($GLOBALS['post'] = &$post_object); ?>
                <?php wc_get_template_part('content', 'product'); ?>
            <?php } ?>
        </ul>
    <?php } ?>
    
<?php }
add_action('woocommerce_before_shop_loop_item_title', 'bbloomer_show_sale_percentage_loop', 25);

function bbloomer_show_sale_percentage_loop()
{
    global $product;
    if (!$product->is_on_sale()) return;
    if ($product->is_type('simple')) {
        $max_percentage = (($product->get_regular_price() - $product->get_sale_price()) / $product->get_regular_price()) * 100;
    } elseif ($product->is_type('variable')) {
        $max_percentage = 0;
        foreach ($product->get_children() as $child_id) {
            $variation = wc_get_product($child_id);
            $price = $variation->get_regular_price();
            $sale = $variation->get_sale_price();
            if ($price != 0 && !empty($sale)) $percentage = ($price - $sale) / $price * 100;
            if ($percentage > $max_percentage) {
                $max_percentage = $percentage;
            }
        }
    }
    if ($max_percentage > 0) echo "<div class='sale-perc'>-" . round($max_percentage) . "%</div>";
}

// add_action('init', 'add_to_yoast_seo');
// function add_to_yoast_seo()
// {
//     $args = array(
//         'numberposts' => -1,
//         'post_type'   => 'product',
//         'fields' => 'ids'
//     );
//     $products = get_posts($args);

//     $metatitle = "";
//     $metadesc = "";
//     $metakeywords = "";


//     // Include plugin library to check if Yoast Seo is presently active
//     foreach ($products as $pid) {


//         $this_post = get_post($pid);
//         $this_post_title = $this_post->post_title;


//         $terms = get_the_terms($pid, 'product_cat');

//         foreach ($terms as $term) {
//             if ($term->slug == 'xlux-luxury-sofa-sets-sofas') {
//                 $metatitle = "Buy the " . $this_post_title . " | Al Huzaifa Furniture UAE";
//                 $metadesc = "Add a touch of luxury to your home with Al Huzaifa Furniture's " . $this_post_title . ". Explore our wide selection of sofa sets, corner sofas, and other unique sofa designs.";
//                 $metakeywords = "sofa set online , buy luxury sofa, 3 Seater Sofa, Leather Sofa, Luxury Sofa, Sofa Set, sofa set online, online sofa, online sofa dubai, sofa set in sharjah, sofa set dubai";
//                 update_post_meta($pid, '_yoast_wpseo_title', $metatitle);
//                 update_post_meta($pid, '_yoast_wpseo_metadesc', $metadesc);
//                 update_post_meta($pid, '_yoast_wpseo_focuskw', $metakeywords);
//             }

//             if ($term->slug == 'contemporary-sofa-sets-sofas') {
//                 $metatitle = "Buy the " . $this_post_title . " | Al Huzaifa Furniture UAE";
//                 $metadesc = "Find the highest comfort levels and shop Al Huzaifa's " . $this_post_title . ". Explore an array of contemporary leather sofas, corner sofas, and more in the UAE.";
//                 $metakeywords = "Contemporary Sofa, Contemporary Leather Sofa, sofa set online, online sofa, online sofa dubai, sofa set in sharjah, sofa set dubai, buy sofa dubai, buy sofa set, Sectional Sofas";
//                 update_post_meta($pid, '_yoast_wpseo_title', $metatitle);
//                 update_post_meta($pid, '_yoast_wpseo_metadesc', $metadesc);
//                 update_post_meta($pid, '_yoast_wpseo_focuskw', $metakeywords);
//             }

//             if ($term->slug == 'bargains-sofa-sets-sofas') {
//                 $metatitle = "Buy the " . $this_post_title . " | Al Huzaifa Furniture UAE";
//                 $metadesc = "If you're looking for bargains, then you'll love this collection of high quality sofa sets with affordable prices. Shop the " . $this_post_title . " from Al Huzaifa in UAE.";
//                 $metakeywords = "bargains sofa sets, sofa set online, online sofa, online sofa dubai, sofa set in sharjah, sofa set dubai, buy sofa dubai, buy sofa set, Corner Sofa, Sectional Sofas";
//                 update_post_meta($pid, '_yoast_wpseo_title', $metatitle);
//                 update_post_meta($pid, '_yoast_wpseo_metadesc', $metadesc);
//                 update_post_meta($pid, '_yoast_wpseo_focuskw', $metakeywords);
//             }

//             if ($term->slug == 'coffee-table') {
//                 $metatitle = "Buy the " . $this_post_title . " | Al Huzaifa Furniture UAE";
//                 $metadesc = "Get the " . $this_post_title . " onine from Al Huzaifa in the UAE, and browse a range of elegant coffee tables in modern and classic designs.";
//                 $metakeywords = "online coffee table, buy coffee table dubai, coffee table dubai, coffee table sets, shop coffee table, glass coffee table, marble coffee table, round coffee table";
//                 update_post_meta($pid, '_yoast_wpseo_title', $metatitle);
//                 update_post_meta($pid, '_yoast_wpseo_metadesc', $metadesc);
//                 update_post_meta($pid, '_yoast_wpseo_focuskw', $metakeywords);
//             }

//             if ($term->slug == 'occasional-chairs') {
//                 $metatitle = "Shop the Modern " . $this_post_title . " | Al Huzaifa Furniture UAE";
//                 $metadesc = "Buy the grand " . $this_post_title . " for your living room from Al Huzaifa in the UAE, & check out our popular collection of chairs in various modern and classic designs.";
//                 $metakeywords = "occasional chairs, shop occasional chairs, living room chairs, armchair, leather armchair, modern armchair, buy chair";
//                 update_post_meta($pid, '_yoast_wpseo_title', $metatitle);
//                 update_post_meta($pid, '_yoast_wpseo_metadesc', $metadesc);
//                 update_post_meta($pid, '_yoast_wpseo_focuskw', $metakeywords);
//             }

//             if ($term->slug == 'dining-chair') {
//                 $metatitle = "Buy The " . $this_post_title . " | Al Huzaifa Furniture UAE";
//                 $metadesc = "Shop Al Huzaifa Furniture's " . $this_post_title . " to complete your dining room and browse other modern dining chairs online in Dubai, Abu Dhabi, and Sharjah.";
//                 $metakeywords = "buy dining chairs, online dining chairs, dining chairs dubai, Dining Room Chairs, Modern Dining Chairs, shop dining chairs, armchair, Dining Chairs";
//                 update_post_meta($pid, '_yoast_wpseo_title', $metatitle);
//                 update_post_meta($pid, '_yoast_wpseo_metadesc', $metadesc);
//                 update_post_meta($pid, '_yoast_wpseo_focuskw', $metakeywords);
//             }

//             if ($term->slug == 'xlux-luxury-dining-table') {
//                 $metatitle = "Buy The " . $this_post_title . " | Al Huzaifa Furniture UAE";
//                 $metadesc = "Own the " . $this_post_title . " from the dining collection at Al Huzaifa Furniture in the UAE and shop other luxury dining tables in various designs.";
//                 $metakeywords = "luxury dining table, Luxury Round Dining Tables, Furniture Dining Table, Dining Room Tables, buy dining table, buy dining table dubai, custom dining tables, Glass Dining Table";
//                 update_post_meta($pid, '_yoast_wpseo_title', $metatitle);
//                 update_post_meta($pid, '_yoast_wpseo_metadesc', $metadesc);
//                 update_post_meta($pid, '_yoast_wpseo_focuskw', $metakeywords);
//             }

//             if ($term->slug == 'contemporary-dining-table') {
//                 $metatitle = "Buy The " . $this_post_title . " | Al Huzaifa Furniture UAE";
//                 $metadesc = "Own the " . $this_post_title . " from the dining collection at Al Huzaifa Furniture in the UAE, and shop other contemporary dining room tables in various designs and materials.";
//                 $metakeywords = "contemporary dining table, Contemporary Dining Room Furniture, Contemporary Wood Dining Table, Modern Contemporary Dining Table, Contemporary Glass Dining Table, Large Contemporary Dining Table";
//                 update_post_meta($pid, '_yoast_wpseo_title', $metatitle);
//                 update_post_meta($pid, '_yoast_wpseo_metadesc', $metadesc);
//                 update_post_meta($pid, '_yoast_wpseo_focuskw', $metakeywords);
//             }

//             if ($term->slug == 'side-board') {
//                 $metatitle = "Buy The " . $this_post_title . " | Al Huzaifa Furniture UAE";
//                 $metadesc = "Save space and decorate your dining room with the " . $this_post_title . " from Al Huzaifa. Explore a range of sideboard cabinets and buffets online.";
//                 $metakeywords = "buy sideboard, shop sideboard, sideboard online, sideboard, sideboard cabinet, sideboards and buffets, sideboard table, buffets & sideboards, dining room buffets";
//                 update_post_meta($pid, '_yoast_wpseo_title', $metatitle);
//                 update_post_meta($pid, '_yoast_wpseo_metadesc', $metadesc);
//                 update_post_meta($pid, '_yoast_wpseo_focuskw', $metakeywords);
//             }

//             if ($term->slug == 'xlux-luxury-bedroom-set-beds') {
//                 $metatitle = "Buy the " . $this_post_title . " For Your Comfort | Al Huzaifa Furniture UAE";
//                 $metadesc = "Shop the " . $this_post_title . " from Al Huzaifa and create a haven of serenity in your own home. Explore a range of luxurious bedroom sets in the UAE.";
//                 $metakeywords = "bedroom set luxury, modern luxury bedroom, luxury bedroom furniture, bedroom set dubai, full bedroom sets, buy bedroom set, luxury master bedroom";
//                 update_post_meta($pid, '_yoast_wpseo_title', $metatitle);
//                 update_post_meta($pid, '_yoast_wpseo_metadesc', $metadesc);
//                 update_post_meta($pid, '_yoast_wpseo_focuskw', $metakeywords);
//             }

//             if ($term->slug == 'contemporary-bedroom-set-beds') {
//                 $metatitle = "Buy the " . $this_post_title . " For Your Comfort | Al Huzaifa Furniture UAE";
//                 $metadesc = "Get the " . $this_post_title . " from Al Huzaifa for a comfortable, contemporary bedroom. Explore a wide collection of bedroom furniture in various designs.";
//                 $metakeywords = "contemporary bedroom furniture, buy bedroom furniture, buy bedroom, best place to buy bedroom furniture";
//                 update_post_meta($pid, '_yoast_wpseo_title', $metatitle);
//                 update_post_meta($pid, '_yoast_wpseo_metadesc', $metadesc);
//                 update_post_meta($pid, '_yoast_wpseo_focuskw', $metakeywords);
//             }

//             if ($term->slug == 'accents') {
//                 $metatitle = "Buy the " . $this_post_title . " | Al Huzaifa Furniture UAE";
//                 $metadesc = "Add a touch of elegance and decorate your home with the " . $this_post_title . " from Al Huzaifa. Explore a variety of home decor items in various sizes and designs.";
//                 $metakeywords = "buy home décor online, home accents, Accent Furniture, accents dubai, home decor items, home decor stores";
//                 update_post_meta($pid, '_yoast_wpseo_title', $metatitle);
//                 update_post_meta($pid, '_yoast_wpseo_metadesc', $metadesc);
//                 update_post_meta($pid, '_yoast_wpseo_focuskw', $metakeywords);
//             }

//             if ($term->slug == 'paintings') {
//                 $metatitle = "Buy the " . $this_post_title . " | Al Huzaifa Furniture UAE";
//                 $metadesc = "Let your walls talk with the " . $this_post_title . " from Al Huzaifa in the UAE. Explore a range of modern & classic paintings in different sizes and canvases.";
//                 $metakeywords = "shop painting, abu dhabi painting, modern paintings, online painting, buy painting, buy paintings online";
//                 update_post_meta($pid, '_yoast_wpseo_title', $metatitle);
//                 update_post_meta($pid, '_yoast_wpseo_metadesc', $metadesc);
//                 update_post_meta($pid, '_yoast_wpseo_focuskw', $metakeywords);
//             }

//             if ($term->slug == 'mirrors') {
//                 $metatitle = "Buy the " . $this_post_title . " | Al Huzaifa Furniture UAE";
//                 $metadesc = "Complete your room with the " . $this_post_title . " from Al Huzaifa's online collection of elegant mirrors in a range of shapes and sizes.";
//                 $metakeywords = "buy mirror online, buy mirror, shop mirror, online wall mirror, mirror uae, mirror dubai, mirror abu dhabi, mirror sharjah";
//                 update_post_meta($pid, '_yoast_wpseo_title', $metatitle);
//                 update_post_meta($pid, '_yoast_wpseo_metadesc', $metadesc);
//                 update_post_meta($pid, '_yoast_wpseo_focuskw', $metakeywords);
//             }

//             if ($term->slug == 'lightings') {
//                 $metatitle = "Buy the " . $this_post_title . " | Al Huzaifa Furniture UAE";
//                 $metadesc = "Light up any room in your home with the " . $this_post_title . " from Al Huzaifa's inclusive collection of pendant lamps, chandeliers, wall & table lamps, and ceiling lights.";
//                 $metakeywords = "shop lighting, wall lamp, chandelier, hanging lights, track lighting, lighting stores, online lighting stores, light fixtures in uae, lighting uae, lighting abu dhabi, table lamps dubai, ceiling lights dubai";
//                 update_post_meta($pid, '_yoast_wpseo_title', $metatitle);
//                 update_post_meta($pid, '_yoast_wpseo_metadesc', $metadesc);
//                 update_post_meta($pid, '_yoast_wpseo_focuskw', $metakeywords);
//             }

//             if ($term->slug == 'kids-collection') {
//                 $metatitle = "Buy the " . $this_post_title . " | Al Huzaifa Furniture UAE";
//                 $metadesc = "Add a touch of fun and coziness to your child's bedroom with the " . $this_post_title . " from Al Huzaifa's adorable collection of stuffed toys.";
//                 $metakeywords = "shop toys, stuffed toy, stuffed animals, online toys, buy toys, buy toys online, online stuffed toys, stuffed toy animals, buy stuffed toys online, buy stuffed toys";
//                 update_post_meta($pid, '_yoast_wpseo_title', $metatitle);
//                 update_post_meta($pid, '_yoast_wpseo_metadesc', $metadesc);
//                 update_post_meta($pid, '_yoast_wpseo_focuskw', $metakeywords);
//             }
//         }
//     }
// }

?>