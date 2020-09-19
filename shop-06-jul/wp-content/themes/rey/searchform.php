<?php
/**
 * Template for displaying search forms in Rey Theme
 */

?>

<?php $unique_id = rey__unique_id( 'search-form-' ) ; ?>

<form role="search" method="get" class="rey-searchBox" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr($unique_id); ?>"  class="screen-reader-text">
		<span><?php echo esc_html_x( 'Search for:', 'label', 'rey' ); ?></span>
	</label>
	<input type="search" id="<?php echo esc_attr($unique_id); ?>" class="form-control" placeholder="<?php echo esc_attr_x( 'Search &hellip;', 'placeholder', 'rey' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
	<button type="submit" class="btn">
		<?php echo rey__get_svg_icon( [ 'id' => 'rey-icon-search' ] ); ?>
		<span class="screen-reader-text"><?php echo esc_html_x( 'Search', 'submit button', 'rey' ); ?></span>
	</button>
	<?php do_action('rey/search_form'); ?>
</form>
