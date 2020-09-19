<?php

// Post Thumbnail
if ( rey__can_show_post_thumbnail() ): ?>
	<figure class="rey-postMedia rey-postThumbnail">
		<?php if ( is_singular() ) : ?>
			<?php the_post_thumbnail( 'rey-standard-large' ); ?>
		<?php else : ?>
		<a class="rey-postThumbnail-inner" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php the_post_thumbnail( 'rey-standard-large' ); ?>
		</a>
		<?php endif; ?>
		<?php rey__categoriesList(); ?>
	</figure>
<?php endif; ?>
