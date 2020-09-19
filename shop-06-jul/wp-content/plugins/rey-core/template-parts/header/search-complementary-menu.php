<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = reycore_wc__get_header_search_args(); ?>

<?php if ( is_nav_menu( $args['search_menu_source'] ) ) : ?>
	<nav class="rey-searchPanel__qlinks" aria-label="<?php esc_attr_e( 'Search Menu', 'rey-core' ); ?>">
		<h4><?php
			$search_menu_object = wp_get_nav_menu_object( $args['search_menu_source'] );
			echo esc_html( $search_menu_object->name );
		?></h4>
		<?php
		wp_nav_menu([
			'menu'       => $args['search_menu_source'],
			'menu_class' => 'rey-searchMenu list-unstyled',
			'container'  => '',
			'depth'      => 1,
			'items_wrap' => '<ul id = "%1$s" class = "%2$s">%3$s</ul>',
			'fallback_cb' 	=> '__return_empty_string',
		]);
		?>
	</nav><!-- .rey-searchPanel__qlinks -->
<?php endif; ?>
