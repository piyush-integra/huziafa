<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = reycore_wc__get_header_search_args();

$from_sticky = get_query_var('rey__is_sticky', false);
?>

<div class="rey-headerSearch rey-headerIcon js-rey-headerSearch">

	<button class="btn rey-headerSearch-toggle js-rey-headerSearch-toggle <?php echo esc_attr( $args['search_text_order'] === 'right' ? '--reverse' : '' ) ?> <?php echo esc_attr( $from_sticky === true ? 'js-from-sticky' : '' ) ?>">
		<?php if(get_theme_mod('header_search_text_enable', false) === true): ?>
			<span class="rey-headerSearch-text"><?php esc_html_e('Search', 'rey-core') ?></span>
		<?php endif; ?>
		<?php echo reycore__get_svg_icon(['id' => 'rey-icon-search', 'class' => 'icon-search']) ?>
		<?php echo reycore__get_svg_icon(['id' => 'rey-icon-close', 'class' => 'icon-close']) ?>
	</button>
	<!-- .rey-headerSearch-toggle -->

</div>
