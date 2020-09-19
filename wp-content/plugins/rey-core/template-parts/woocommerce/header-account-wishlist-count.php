<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = reycore_wc__get_account_panel_args();
?>

<span class="rey-headerAccount-count">

	<?php
		if( $args['wishlist'] && $args['counter'] != '' ):
			wp_enqueue_script( 'tinvwl' );
			?>
			<span class="wishlist_products_counter">
				<span class="wishlist_products_counter_number"></span>
			</span><?php
		endif;
	?>

	<span class="rey-headerAccount-closeIcon">
		<?php
		echo reycore__get_svg_icon(['id' => 'rey-icon-close']) ?>
	</span>
</span>
