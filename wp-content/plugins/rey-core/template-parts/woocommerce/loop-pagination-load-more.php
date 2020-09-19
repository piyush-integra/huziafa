<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if( function_exists('reycore__ajax_load_more_pagination') ):
	reycore__ajax_load_more_pagination([
		'class' => 'btn btn-line-active js-rey-ajaxLoadMore',
		'target' => '.rey-siteMain ul.products',
		'post_type' => 'product',
	]);
endif;
