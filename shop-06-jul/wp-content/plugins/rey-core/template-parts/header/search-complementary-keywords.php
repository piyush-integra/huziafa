<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = reycore_wc__get_header_search_args(); ?>

<nav class="rey-searchPanel__suggestions" aria-label="<?php esc_attr_e( 'Search Suggestions', 'rey-core' ); ?>">
	<h4><?php echo esc_html_x('TRENDING', 'Search keywords title', 'rey-core');?></h4>
	<?php
	if( !empty($args['keywords']) ):
		$keywords_arr = array_map( 'trim', explode( ',', $args['keywords'] ) );
		foreach ($keywords_arr as $kwd):
			echo "<button>" . esc_html( $kwd ) . "</button>";
		endforeach;
	endif;
	?>
</nav>
