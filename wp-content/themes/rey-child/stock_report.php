<?php if ( ! defined( 'ABSPATH' ) ) exit;
add_action('admin_menu', 'huzaifa_stock_report_admin_menu');
function huzaifa_stock_report_admin_menu() {  
  add_menu_page('Stock Report', 'Stock Report', 10, 'huzaifa_stock_report', 'endo_admin_stock_report_page');
}
function endo_admin_stock_report_page() {?>
	<div class="wrap">
<?php 
    // get all simple products where stock is managed
	$args = array(
	'post_type'			=> 'product',
	
    'posts_per_page' 	=> -1,
    'orderby'			=> 'title',
    'order'				=> 'ASC',
	'meta_query' 		=> array(
        array(
            'key' 	=> '_manage_stock',
            'value' => 'yes'
        )
    ),
		'tax_query' => array(
			array(
				'taxonomy' 	=> 'product_type',
				'field' 	=> 'slug',
				'terms' 	=> array('simple'),
				'operator' 	=> 'IN'
			)
		)
	);?>
<style>
    table tr td{border:1px solid #ccc;}
    #download_csv {padding: 10px 50px; font-size: 20px; background-color: #80642D;border:2px solid #80642D;line-height: 40px;font-weight: bold; float:right;margin-bottom:25px; width:100%;}
    #download_csv:hover{border:2px solid #80642D; background-color: #ffff;color:#80642D;}
</style>
<div style=" margin: 0 auto;margin-top:0px;padding:0px 0px; width:80%;">
    <div style="display:flex">
        <h2 style="font-size:40px;line-height: 40px;  font-weight: bold; text-align:left; width:70%;">HUZAIFA SHOP STOCK REPORT</h2>
        <form method="post" id="export-form" action="" style="width:30%;">
            <?php submit_button('Download Stock Report', 'primary', 'download_csv' ); ?>
        </form>
    </div>
</div>
    <table style="background: #ffffff; margin: 0 auto;margin-top:0px;padding:0px 0px;" border-radius="5px" width="80%" cellspacing="0" cellpadding="0" bgcolor="FFFFFF">
    <thead>
    <tr>
    <td style="color:#fff; border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" width="4%" >No</td>
    <td style="color:#fff; border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" width="7%" >Sku</td>
    <td style="color:#fff; border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" width="20%" >Product - Simple</td>
    <td style="color:#fff; border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" width="7%" >Sale Price</td>
    <td style="color:#fff; border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" width="7%" >Regular Price</td>
    <td style="color:#fff; border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" width="5%"> Stock</td>
    <td style="color:#fff; border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" width="10%"> Category1</td>
    <td style="color:#fff; border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" width="10%"> Category2</td>
    <td style="color:#fff; border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" width="10%"> Category3</td>
    <td style="color:#fff; border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" width="10%"> Status</td>
    <td style="color:#fff; border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" width="10%"> Badge</td>
    </tr>
    </thead>
    <tbody>
    <?php
	$loop = new WP_Query( $args );
    $sr = 1;
	while ( $loop->have_posts() ) : $loop->the_post();	
        global $product;
        $product_catogories = [$product->category_ids[0], $product->category_ids[1], $product->category_ids[2]];
        $product_catogories = array_filter($product_catogories);         
        sort($product_catogories); 
        $c1 =  $product_catogories[0];
        $c2 =  $product_catogories[1]; 
        $c3=  $product_catogories[2];
       
        $Category1 = get_term( $c1, 'product_cat' );
        $Category2 = get_term( $c2, 'product_cat' );
        $Category3 = get_term( $c3, 'product_cat' );
        $id=$product->id;
        $badge= get_field('badge_text',$id);
        //var_dump($product);
        ?>      


    <tr style="background: #eee !important;">
    <td style="padding: 5px 10px; background: #eee;" width="4%"><?=$sr?></td>
    <td style="padding: 5px 10px; background: #efefef;" width="7%"><?=$product->sku?></td>		
    <td style="padding: 5px 10px; background: #eee;" width="20%"><?=$product->get_title()?></td>	
    <td style="padding: 5px 10px; background: #efefef;" width="7%"><?=$product->sale_price?></td>	
    <td style="padding: 5px 10px; background: #eee;" width="7%"><?=$product->regular_price?></td>	
    <td style="padding: 5px 10px; background: #efefef;" width="5%"><?=round($product->stock)?> </td>    
    <td style="padding: 5px 10px; background: #efefef;" width="10%"><?=$Category1->name;?> </td>   
    <td style="padding: 5px 10px; background: #efefef;" width="10%"><?=$Category2->name;?> </td>
    <td style="padding: 5px 10px; background: #efefef;" width="10%"><?=$Category3->name;?> </td>
    <td style="padding: 5px 10px; background: #efefef;" width="10%"><?=$product->status;?> </td>
    <td style="padding: 5px 10px; background: #efefef;" width="10%"><?=$badge;?> </td>
	</tr>   
    <?php $sr++;	
    endwhile;?>
     </tbody>
    </table>
    <table style="background: #ffffff; margin: 0 auto;margin-top:0px;padding:0px 0px;margin-top:30px;" border-radius="5px" width="80%" cellspacing="0" cellpadding="0" bgcolor="FFFFFF">
    <thead>
    <tr>
    <td style="color:#fff; border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" width="4%" >No</td>
    <td style="color:#fff; border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" width="7%" >Sku</td>
    <td style="color:#fff; border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" width="20%" >Product - Variable</td>
    <td style="color:#fff; border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" width="7%" >Sale Price</td>
    <td style="color:#fff; border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" width="7%" >Regular Price</td>
    <td style="color:#fff; border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" width="5%"> Stock</td>
    <td style="color:#fff; border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" width="10%"> Category1</td>
    <td style="color:#fff; border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" width="10%"> Category2</td>
    <td style="color:#fff; border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" width="10%"> Category3</td>
    <td style="color:#fff; border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" width="10%"> Status</td>
    <td style="color:#fff; border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" width="10%"> Badge</td>
    </tr>
    </thead>
    <tbody>
    <?php 
	$argsvariable = array(
        'post_type'			=> 'product_variation',       
        'posts_per_page' 	=> -1,
        'orderby'			=> 'title',
        'order'				=> 'ASC',
		'meta_query' => array(
			array(
				'key' 		=> '_stock',
				'value' 	=> array('', false, null),
				'compare' 	=> 'NOT IN'
			)
		)
	);	
    $loopvariable = new WP_Query( $argsvariable );
    $sr = 1;
	while ( $loopvariable->have_posts() ) : $loopvariable->the_post();	
        global $product;    //echo $id;    
        $id=$product->id;
        $badge= get_field('badge_text',$id);          
        $terms = get_the_terms ( $id, 'product_cat' );
        $items = array();
        foreach ( $terms as $term ) {    
            $term->term_id;
            $items[] = $term->term_id;       
        }
        $product_catogoriesv = array_filter($items);         
        sort($product_catogoriesv); 
        $c1 =  $product_catogoriesv[0];
        $c2 =  $product_catogoriesv[1]; 
        $c3=  $product_catogoriesv[2];
       
        $Category1 = get_term( $c1, 'product_cat' );
        $Category2 = get_term( $c2, 'product_cat' );
        $Category3 = get_term( $c3, 'product_cat' );
        //var_dump($product);

        ?>
    <tr style="background: #eee !important;">
    <td style="padding: 5px 10px; background: #eee;" width="4%"><?=$sr?></td>
    <td style="padding: 5px 10px; background: #efefef;" width="7%"><?=$product->sku?></td>		
    <td style="padding: 5px 10px; background: #eee;" width="20%"><?=$product->get_title()?></td>	
    <td style="padding: 5px 10px; background: #efefef;" width="7%"><?=$product->sale_price?></td>	
    <td style="padding: 5px 10px; background: #eee;" width="7%"><?=$product->regular_price?></td>	
    <td style="padding: 5px 10px; background: #efefef;" width="5%"><?=round($product->stock)?> </td>    
    <td style="padding: 5px 10px; background: #efefef;" width="10%"><?=$Category1->name;?> </td>   
    <td style="padding: 5px 10px; background: #efefef;" width="10%"><?=$Category2->name;?> </td>
    <td style="padding: 5px 10px; background: #efefef;" width="10%"><?=$Category3->name;?> </td>
    <td style="padding: 5px 10px; background: #efefef;" width="10%"><?=get_post_status($id);?> </td>
    <td style="padding: 5px 10px; background: #efefef;" width="10%"><?=$badge;?> </td>      
	</tr>   
    <?php $sr++;	
    endwhile;?>
    </tbody>
    </table>  
    </div>
    <?php }
add_action('admin_init', 'huzaifa_stock_report_admin_init');
function huzaifa_stock_report_admin_init() {
	global $plugin_page;
	if ( isset($_POST['download_csv']) && $plugin_page == 'huzaifa_stock_report' ) {	   
	   	generate_stock_report_csv();	    
	    die();
    }
}
function generate_stock_report_csv() {

	// output headers so that the file is downloaded rather than displayed
	header('Content-Type: text/csv; charset=utf-8');

	// set file name with current date
	header('Content-Disposition: attachment; filename=Huzaifa-shop-stock-report-' . date('Y-m-d') . '.csv');

	// create a file pointer connected to the output stream
	$output = fopen('php://output', 'w');

	// set the column headers for the csv
	$headings = array( 'SKU', 'Product','Sale Price', 'Regular Price','Stock','Category1','Category2','Category3','Status','Badge' );

	// output the column headings
	fputcsv($output, $headings );

	// get all simple products where stock is managed
	$args = array(
	'post_type'			=> 'product',
	
    'posts_per_page' 	=> -1,
    'orderby'			=> 'title',
    'order'				=> 'ASC',
	'meta_query' 		=> array(
        array(
            'key' 	=> '_manage_stock',
            'value' => 'yes'
        )
    ),
		'tax_query' => array(
			array(
				'taxonomy' 	=> 'product_type',
				'field' 	=> 'slug',
				'terms' 	=> array('simple'),
				'operator' 	=> 'IN'
			)
		)
	);

	$loop = new WP_Query( $args );

	while ( $loop->have_posts() ) : $loop->the_post();
	
        global $product;

      

        $product_catogories = [$product->category_ids[0], $product->category_ids[1], $product->category_ids[2]];
        $product_catogories = array_filter($product_catogories);         
        sort($product_catogories); 
        $c1 =  $product_catogories[0];
        $c2 =  $product_catogories[1]; 
        $c3=  $product_catogories[2];
       
        $Category1 = get_term( $c1, 'product_cat' );
        $Category2 = get_term( $c2, 'product_cat' );
        $Category3 = get_term( $c3, 'product_cat' );
        $id=$product->id;
        $badge= get_field('badge_text',$id);
        //var_dump($product);

        $row = array( $product->sku,$product->get_title(),$product->sale_price,$product->regular_price,$product->stock,$Category1->name,$Category2->name,$Category3->name,$product->status,$badge);

        fputcsv($output, $row);
		
	endwhile; 

	// get all product variations where stock is managed
	$args = array(
		'post_type'			=> 'product_variation',		
        'posts_per_page' 	=> -1,
        'orderby'			=> 'title',
        'order'				=> 'ASC',
		'meta_query' => array(
			array(
				'key' 		=> '_stock',
				'value' 	=> array('', false, null),
				'compare' 	=> 'NOT IN'
			)
		)
	);
	
	$loop = new WP_Query( $args );

	while ( $loop->have_posts() ) : $loop->the_post();
	
        $product = new WC_Product_Variation( $loop->post->ID );
        $id=$product->id;
        $badge= get_field('badge_text',$id);          
        $terms = get_the_terms ( $id, 'product_cat' );
        $items = array();
        foreach ( $terms as $term ) {    
            $term->term_id;
            $items[] = $term->term_id;       
        }
        $product_catogoriesv = array_filter($items);         
        sort($product_catogoriesv); 
        $c1 =  $product_catogoriesv[0];
        $c2 =  $product_catogoriesv[1]; 
        $c3=  $product_catogoriesv[2];
       
        $Category1 = get_term( $c1, 'product_cat' );
        $Category2 = get_term( $c2, 'product_cat' );
        $Category3 = get_term( $c3, 'product_cat' );
        $status=get_post_status($id);
        
		
        
        $row = array( $product->sku,$product->get_title() . ', ' . get_the_title( $product->variation_id ),$product->sale_price,$product->regular_price,$product->stock,$Category1->name,$Category2->name,$Category3->name,$status,$badge );

        fputcsv($output, $row);

	endwhile;

}