<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$args = wp_parse_args( get_query_var('rey__header_nav'),  [
	'menu' => 'main-menu',
	'mobile_menu' => get_theme_mod('mobile_menu', 'main-menu'),
	'nav_id' => 'mm1',
	'nav_style' => '--style-default',
	'nav_ul_style' => '',
	'nav_indicator' => 'circle',
]);

if ( ($desktop_menu = rey__get_nav_menu_by_location( $args['menu'] )) && is_nav_menu( $desktop_menu ) ) :

	get_template_part('template-parts/header/hamburger-icon'); ?>

    <nav id="site-navigation<?php echo esc_attr($args['nav_id']) ?>" class="rey-mainNavigation rey-mainNavigation--desktop <?php esc_attr_e($args['nav_style']) ?> <?php esc_attr_e( implode(' ', apply_filters('rey/header/nav_classes', [], $args, 'desktop') )) ?>" <?php rey__render_attributes('site-navigation', [
		'data-id' => $args['nav_id'],
		'aria-label' => __( 'Main Menu', 'rey' ),
		'data-sm-indicator' => $args['nav_indicator']
	]); ?>>

        <?php
        $desktop_nav_menu = wp_nav_menu(
            [
                'menu'        => $desktop_menu,
                'menu_id'     => 'main-menu-desktop' . esc_attr($args['nav_id']),
                'menu_class'  => esc_attr( implode(' ', apply_filters('rey/header/nav_ul_classes', [
						'rey-mainMenu', 'rey-mainMenu--desktop', 'id--mainMenu--desktop', $args['nav_ul_style']
					], $args, 'desktop') )),
                'container'   => '',
                'items_wrap'  => '<ul id = "%1$s" class = "%2$s">%3$s</ul>',
                'link_before' => '<span>',
				'link_after'  => '</span>',
				'echo'        => false,
            ]
		);
		echo $desktop_nav_menu;
		?>
	</nav><!-- .rey-mainNavigation -->

<?php else: ?>
	<?php if( current_user_can('administrator') ): ?>
    <div class="rey-mainNavigation--missing"><?php echo sprintf( wp_kses( __('You have to create a menu then select Primary Menu location using&nbsp;<a href="%s">Menu Builder</a>', 'rey'), ['a' => ['href' => []]] ), esc_url(admin_url('nav-menus.php')) ) ?></div>
    <?php endif; ?>
<?php endif; ?>

<?php
if ( ($mobile_menu = rey__get_nav_menu_by_location ($args['mobile_menu'])) && is_nav_menu( $mobile_menu ) ) : ?>

	<nav id="site-navigation-mobile<?php echo esc_attr($args['nav_id']) ?>" class="rey-mainNavigation rey-mainNavigation--mobile rey-mobileNav <?php esc_attr_e( implode(' ', apply_filters('rey/header/nav_classes', [], $args, 'mobile') )) ?>" <?php rey__render_attributes('site-navigation', [
		'data-id' => $args['nav_id'],
		'aria-label' => __( 'Main Menu', 'rey' )
	]); ?>>
		<div class="rey-mobileNav-container">
			<div class="rey-mobileNav-header">

				<?php do_action('rey/mobile_nav/header'); ?>

				<div class="rey-siteLogo">
					<?php
						$logo_data = wp_parse_args( get_query_var('rey__header_logo'), [
							'blog_name' => get_bloginfo( 'name', 'display' ),
							'logo' => get_theme_mod('custom_logo', ''),
							'logo_mobile' => get_theme_mod('logo_mobile', ''),
						]); ?>
					<?php if ( $logo_data['logo'] ) : ?>
						<?php echo rey__custom_logo( $logo_data ); ?>
					<?php else: ?>
						<a class="rey-logoTitle" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html($logo_data['blog_name']); ?></a>
					<?php endif; ?>
				</div>

				<button class="btn rey-mobileMenu-close js-rey-mobileMenu-close">
					<?php echo rey__get_svg_icon(['id' => 'rey-icon-close']) ?>
				</button>
			</div>

			<div class="rey-mobileNav-main">
				<?php

				// if desktop menu is the same as mobile,
				// let's not make another db. query, and instead replace stuff inside
				if( $mobile_menu === $desktop_menu ){
					$mobile_nav_menu = str_replace( 'main-menu-desktop' . esc_attr($args['nav_id']), 'main-menu-mobile' . esc_attr($args['nav_id']), $desktop_nav_menu); // menu id
					$mobile_nav_menu = str_replace( ['id--mainMenu--desktop', $args['nav_ul_style']], '', $mobile_nav_menu); // menu class
					$mobile_nav_menu = str_replace( 'rey-mainMenu--desktop', 'rey-mainMenu-mobile', $mobile_nav_menu); // menu class
				}
				else {
					$mobile_nav_menu = wp_nav_menu([
						'menu'            => $mobile_menu,
						'menu_id'         => 'main-menu-mobile' . esc_attr($args['nav_id']),
						'menu_class'      => 'rey-mainMenu rey-mainMenu-mobile',
						'items_wrap'      => '<ul id = "%1$s" class = "%2$s">%3$s</ul>',
						'link_before'     => '<span>',
						'link_after'      => '</span>',
						'echo'            => false,
					]);
				}

				echo $mobile_nav_menu; ?>
			</div>

			<div class="rey-mobileNav-footer">
				<?php do_action('rey/mobile_nav/footer'); ?>
			</div>
		</div>

	</nav>

<?php endif; ?>
