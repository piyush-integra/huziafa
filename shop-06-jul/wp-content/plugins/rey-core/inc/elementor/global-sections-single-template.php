<?php
get_header(); ?>

<div class="rey-pbTemplate rey-pbTemplate--gs rey-pbTemplate--gs-<?php echo reycore__acf_get_field('gs_type') ?>">
    <div class="rey-pbTemplate-inner">
        <?php
        while ( have_posts() ) : the_post();
		reycore__get_template_part( 'template-parts/page/content' );
        endwhile;
        ?>
    </div>
</div>
<!-- .rey-pbTemplate -->


<?php

	do_action('reycore/global_section_template/after_content');


get_footer();
