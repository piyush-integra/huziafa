<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$show_account_forms_and_menu = true;

if( get_theme_mod('shop_catalog', false) === true && apply_filters('reycore/catalog_mode/hide_account', false) ){
	$show_account_forms_and_menu = false;
}

$args = reycore_wc__get_account_panel_args();

if( ! $args['forms'] ){
	$show_account_forms_and_menu = false;
}

?>

<div class="rey-accountPanel-wrapper js-rey-accountPanel">
	<div class="rey-accountPanel">

		<?php reycore__get_template_part('template-parts/woocommerce/header-account-wishlist'); ?>

		<?php if( $show_account_forms_and_menu ): ?>

			<?php if(! is_user_logged_in() ): ?>

				<div class="rey-accountForms" <?php reycore_wc__account_redirect_attrs() ?>>
					<?php
						reycore__get_template_part('template-parts/woocommerce/form-login');
						reycore__get_template_part('template-parts/woocommerce/form-register');
						reycore__get_template_part('template-parts/woocommerce/form-lost-password');
						do_action( 'woocommerce_after_customer_login_form' ); ?>
				</div>

			<?php else:

					reycore__get_template_part('template-parts/woocommerce/header-account-menu');

			endif; ?>

		<?php endif; ?>

	</div>
	<!-- .rey-accountPanel -->
</div>
<!-- .rey-accountPanel-wrapper -->
