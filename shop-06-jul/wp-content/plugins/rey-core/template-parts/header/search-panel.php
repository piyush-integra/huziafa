<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = reycore_wc__get_header_search_args(); ?>

<div class="rey-searchPanel rey-searchForm rey-searchAjax js-rey-ajaxSearch" data-style="<?php echo esc_attr( $args['search_style'] ) ?>">

	<button class="btn rey-searchPanel-close js-rey-searchPanel-close">
		<?php echo reycore__get_svg_icon(['id' => 'rey-icon-close']) ?>
	</button>
	<!-- .rey-searchPanel-close -->

	<div class="rey-searchPanel-inner">

		<form role="search" action="<?php echo esc_url(home_url('/')) ?>" method="get">
			<?php
			$form_title = sprintf('<h4>%s %s</h4>', esc_html__('Search', 'rey-core'), str_replace(['http://', 'https://'], '', get_site_url()) );
			echo apply_filters('rey/search_form/title', $form_title); ?>
			<input type="search" name="s" placeholder="<?php esc_attr_e( 'type to search..', 'rey-core' ); ?>" />
			<?php do_action('rey/search_form'); ?>
		</form>

		<?php do_action('reycore/search_panel/after_search_form', $args); ?>
		<!-- .row -->
	</div>
</div>
<!-- .rey-searchPanel -->
