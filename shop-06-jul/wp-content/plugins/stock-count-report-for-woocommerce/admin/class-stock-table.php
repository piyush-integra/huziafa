<?php

/**
 * Table class for displaying stock count report.
 *
 * @link       http://woofx.kaizenflow.xyz/
 * @since      1.0.0
 *
 * @package    Stock_Count_Report_Woocommerce
 * @subpackage Stock_Count_Report_Woocommerce/admin
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Stock_Table extends WP_List_Table {

    /**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
	 * Total stock quantity count.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      integer    $_total_stock    Total stock quantity count.
	 */
    private $_total_stock = 0;

    /**
	 * Total value of the stock.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $_total_value    Total value of the stock.
	 */
    private $_total_value = 0;

    /**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        parent::__construct( [
            'singular' => __( 'Stock', $this->plugin_name ), //singular name of the listed records
            'plural'   => __( 'Stock', $this->plugin_name ), //plural name of the listed records
            'ajax'     => false //does this table support ajax?
        ] );
    }

    /**
     * Handles data query and filter, sorting, and pagination.
     * 
     * @since    1.0.0
     */
    public function prepare_items() {

        // load columns
        $this->_column_headers = $this->get_column_info();

        // prepare items
        $this->items = $this->_get_stock_items();
        
        // set pagination information
        // this is a single page report so setting total_items = per_page
        $this->set_pagination_args( [
            'total_items' => count($this->items), //WE have to calculate the total number of items
            'per_page'    => count($this->items)
        ] );
    }

    /**
     * Associative array of columns
     *
     * @since   1.0.0
     * @return  array
     */
    public function get_columns() {
        $columns = [
            'sku'       => __(  'SKU',          $this->plugin_name ),
            'variation' => __(  'Variation',    $this->plugin_name ),
            'stock'     => __(  'Stock Count',  $this->plugin_name ),
            'price'     => __(  'Price',        $this->plugin_name),
            'value'     => __(  'Value',        $this->plugin_name),            
        ];
        return $columns;
    }

    /**
     * Columns to make sortable.
     *
     * @since   1.0.0
     * @return  array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'sku' => array( 'sku', false )
        );
        return $sortable_columns;
    }

    /**
     * Query and prepare item data
     * 
     * @since   1.0.0
     * @return  array   list of associative column-value arrays
     */
    private function _get_stock_items(){
        
        // get products
        $args = array(
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        );
        if(isset($_GET['cat-filter'])) $args['category'] = esc_sql($_GET['cat-filter']);
        if(isset($_GET['orderby'])){
            $args['orderby']    = esc_sql($_GET['orderby']);
            $args['order']      = esc_sql($_GET['order']);
        }
        $query = new WC_Product_Query( $args );
        $products = $query->get_products();
        
        // populate stock data
        // reference - https://stackoverflow.com/a/41015745
        $data = [];
        foreach($products as $product){
            $sku = $product->get_sku();
            
            // get count for variations if applicable
            if ($product->is_type( 'variable' )){
                
                $available_variations = $product->get_available_variations();
                foreach ($available_variations as $key => $variation){ 
                    
                    if( $variation['variation_is_active'] ){
                        
                        $attr_text = implode(', ',$variation['attributes']);
                        $price = $product->get_price();
                        
                        if($variation['is_in_stock']){
                            
                            $qty = $variation['max_qty'];
                            $amount = $price*$qty;
                            
                            $data[] = [
                                'sku'       => $sku,
                                'variation' => $attr_text,
                                'stock'     => $qty,
                                'price'     => $price,
                                'value'     => $amount
                            ];
                            
                            $this->_total_stock += $qty;
                            $this->_total_value += $amount;
                        }
                        else{
                            
                            $data[] = [
                                'sku'       => $sku,
                                'variation' => '-',
                                'stock'     => 0,
                                'price'     => $price,
                                'value'     => 0
                            ];
                        }
                    }
                }
            }
            else{
                
                $qty = $product->get_stock_quantity();
                $price = $product->get_price();
                $amount = $qty*$price;
                
                $data[] = [
                    'sku'       => $sku,
                    'variation' => '-',
                    'stock'     => $qty,
                    'price'     => $price,
                    'value'     => $amount
                ];
                
                $this->_total_stock += $qty;
                $this->_total_value += $amount;
            }
            
            
        }
        
        return $data;
    }

    /**
     * Render a column when no column specific method exist.
     *
     * @since   1.0.0
     * @param   array   $item
     * @param   string  $column_name     *
     * @return  mixed
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'sku':
            case 'variation':
            case 'stock':
            case 'price':
            case 'value':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ); //Show the whole array for troubleshooting purposes
        }
    }
    
    /** Text displayed when no customer data is available */
    public function no_items() {
        _e( 'No stock units avaliable.', $this->plugin_name );
    }    
    
    /**
     * Add filter controls for Stock_Table
     *
     * @since   1.0.0
     * @param   string  $which  position value, can be 'top' or 'bottom'
     * @return  array
     */
    public function extra_tablenav( $which ){
        if($which=='top'){
            $cat = '';
            if(isset($_GET['cat-filter'])) $cat = esc_sql($_GET['cat-filter']);
            wp_dropdown_categories([
                'show_option_all'   =>   'All Categories',
                'taxonomy'          =>   'product_cat',
                'id'                =>   'stock-count-report-cat-filter',
                'selected'          =>   $cat,
                'value_field'       =>   'slug'
            ]);
            echo '<a id="stock-count-report-cat-download" class="button" value="">Download</a>';
            echo '&nbsp;&nbsp;'.$this->_total_stock . ' units in stock';
            echo "&nbsp;&nbsp;(value: ".get_woocommerce_currency()." ". number_format($this->_total_value) .")";
        }
        
    }
    
    
}