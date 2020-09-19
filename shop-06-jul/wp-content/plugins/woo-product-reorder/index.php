<?php
/*
Plugin Name: Product Reorder for Woocommerce
Description: Product Reorder Plugin that allow you to reorder the products in woocommerce shop page.
Author: Gangesh Matta
text domain:wpr-reorder;
version:1.0;

 */

add_action('admin_init', 'wpr_reorder_scripts');
add_action('admin_enqueue_scripts', 'wpr_reorder_enqueue');

function wpr_reorder_scripts()
{

    wp_enqueue_style('wpr-style2', plugin_dir_url(__FILE__) . 'css/style.css');
}

function wpr_reorder_enqueue()
{

    // Use `get_stylesheet_directory_uri() if your script is inside your theme or child theme.
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('reorder-pro0', plugin_dir_url(__FILE__) . 'js/modernizr.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('reorder-pro1', plugin_dir_url(__FILE__) . 'js/isotope.pkgd.min.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('reorder-pro3', plugin_dir_url(__FILE__) . 'js/jGravity.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('reorder-pro5', plugin_dir_url(__FILE__) . 'js/isotope.pkgd.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('reorder-pro4', plugin_dir_url(__FILE__) . 'js/index.js', array('jquery-ui-core'), '1.0.0', true);
}

//product categories

// Draw the menu page itself
function wpr_reorder_do_page()
{
    ?>


<div class="wrap">
    <div class="fixed-holder">
        <h1 class="reorder-title"><?php esc_html_e('Woocommerce Product Reorder', 'wpr-reorder');?></h1><button
            id="save_order" class="btn btn-primary"><?php esc_html_e('Save order', 'wpr-reorder')?></button>
    </div>



    <div id="inner">
        <?php
$args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',

        'meta_query' => array(
            array(
                'key' => '_stock_status',
                'value' => 'instock',
                'compare' => '=',
            ),
        ),

        'tax_query' => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'product_visibility',
                'field' => 'name',
                'terms' => 'exclude-from-catalog',
                'operator' => 'NOT IN',

            ),

        ),

    );

    $loop = new WP_Query($args);
    if ($loop->have_posts()) {
        while ($loop->have_posts()):
            $loop->the_post();
            $menu_order = $loop->post->menu_order;

            global $post;
            $terms = get_the_terms($loop->post->ID, 'product_cat');

            $product_cat_slug = "";
            foreach ($terms as $term) {
                $product_cat_id = $term->term_id;
                $product_cat_slug .= $term->slug . " ";

            }
            ?>

        <div class="gravity all <?php //echo $product_cat_slug; ?>"
            data-category="<?php //echo $product_cat_slug; ?> 'all' " data-order="<?=$menu_order?>"
            data-pid="<?=$loop->post->ID?>">
            <?php
    the_post_thumbnail('thumbnail');
            echo '<h4>' . get_the_title() . '</h4>';
            echo $term->slug;
            $product = new WC_Product(get_the_ID());
            echo '<h4>' . wc_price($product->get_price_including_tax(1, $product->get_price())) . '</h4>';
            echo '<h4>' . edit_post_link() . '</h4>';

            ?>

        </div>
        <?php
endwhile;
    } else {
        echo esc_html_e('No products found');
    }
    wp_reset_postdata();
    ?>
    </div>
</div>
<?php
}

// add wc reorder (submenu page)

if (is_admin()) {
    add_action('admin_menu', 'wpr_add_products_menu_entry', 100);
}

function wpr_add_products_menu_entry()
{
    add_submenu_page(
        'edit.php?post_type=product',
        'Product Grabber',
        'WC Reorder',
        'manage_woocommerce', // Required user capability
        'ddg-product',
        'wpr_reorder_do_page'
    );
}

function wpr_filter(array &$array)
{
    array_walk_recursive($array, function (&$value) {
        $value = filter_var(trim($value), FILTER_SANITIZE_STRING);
    });

    return $array;
}

function wpr_update_order()
{
    global $wpdb;

    // Make sure things are set before using them
    $items = isset($_POST['obj']) ? (array) $_POST['obj'] : array();

    $items = wpr_filter($items);
    //   update_post_meta($post->ID, 'wpr_items', $items);
    //var_dump($items);

    foreach ($items as $item) {

        $data = array(
            'menu_order' => $item["menu_order"],
        );
        $data = apply_filters('post-types-order_save-ajax-order', $data, $item["menu_order"], $item["ID"]);
        $wpdb->update($wpdb->posts, $data, array('ID' => $item["ID"]));
    }

    die();
}

add_action('wp_ajax_nopriv_wc_order', 'wpr_update_order');
add_action('wp_ajax_wc_order', 'wpr_update_order');

function wpr_textdomain()
{
    load_theme_textdomain('we-reorder', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'wpr_textdomain');

function wpr_strings($translated_text, $text, $domain)
{
    switch ($translated_text) {
        case 'Reorder Title':
            //   $translated_text = esc_html_e('Woocommerce Product Reorder', 'wpr-reorder');
            break;
    }
    return $translated_text;
}
add_filter('gettext', 'wpr_strings', 20, 3);