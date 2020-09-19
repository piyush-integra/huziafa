<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = wp_parse_args( get_query_var('rey__header_logo'), [
	'blog_name' => get_bloginfo( 'name', 'display' ),
	'blog_description' => get_bloginfo( 'description', 'display' ),
	'logo' => rey__get_option('custom_logo', ''),
	'logo_mobile' => rey__get_option('logo_mobile', ''),
]); ?>

<div class="rey-logoWrapper">

	<?php if ( $args['logo'] ) : ?>

        <div class="rey-siteLogo">
			<?php echo rey__custom_logo( $args ); ?>
		</div>

    <?php elseif( ! empty( $args['blog_name'] ) ) : ?>

        <?php if ( is_front_page() && is_home() ) : ?>
            <h1 class="rey-logoTitle">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html($args['blog_name']); ?></a>
            </h1>
        <?php else : ?>
            <p class="rey-logoTitle"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html($args['blog_name']); ?></a></p>
        <?php endif; ?>

        <?php
        if ( $args['blog_description'] || is_customize_preview() ) :
            ?>
            <p class="rey-logoDescription">
                <?php echo esc_html($args['blog_description']); ?>
            </p>
        <?php endif; ?>

    <?php endif; ?>
</div>
<!-- .rey-logoWrapper -->
