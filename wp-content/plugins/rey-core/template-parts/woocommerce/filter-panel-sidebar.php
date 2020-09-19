<?php
/**
 * Filter Sidebar Panel
 */
 ?>

<div class="rey-filterPanel-wrapper rey-sidePanel" id="js-filterPanel">
	<?php do_action('reycore/filters_sidebar/before_panel'); ?>
	<div class="rey-filterPanel">

		<header class="rey-filterPanel__header">
			<button class="rey-sidePanel-close js-rey-sidePanel-close">
				<?php
				echo reycore__get_svg_icon(['id' => 'rey-icon-close']); ?>
			</button>
			<button class="rey-filterPanel__reset btn btn-line-active js-rey-filter-reset" data-location="<?php echo reycore_wc__reset_filters_link(); ?>"><?php esc_html_e('RESET FILTERS', 'rey-core'); ?></button>
		</header>

		<div class="rey-filterPanel-content-wrapper">
			<div class="rey-filterPanel-content" data-ss-container>
				<?php do_action('reycore/filters_sidebar/before_widgets'); ?>
				<?php dynamic_sidebar( 'filters-sidebar' ); ?>
				<?php do_action('reycore/filters_sidebar/after_widgets'); ?>
			</div>
		</div>

	</div>
	<?php do_action('reycore/filters_sidebar/after_panel'); ?>
</div>
