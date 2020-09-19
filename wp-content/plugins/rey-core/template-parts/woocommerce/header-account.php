<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = reycore_wc__get_account_panel_args(); ?>

<div class="rey-headerAccount rey-headerIcon <?php echo get_theme_mod('header_account_mobile', false) ? 'd-md-block d-none' : '' ?>">
    <button class="btn rey-headerIcon-btn rey-headerAccount-btn--<?php echo esc_attr($args['button_type']); ?> js-rey-headerAccount">
		<?php
			$btn_html = '';
			$btn_icon = '';

			// icons are shown always
			if( $args['icon_type'] === 'reycore-icon-heart' ){
				$btn_icon = reycore__get_svg_icon__core(['id' => 'reycore-icon-heart', 'class' => 'rey-headerAccount-btnIcon']);
			}
			else {
				$btn_icon = reycore__get_svg_icon(['id' => 'rey-icon-user', 'class' => 'rey-headerAccount-btnIcon']);
			}

			// add counter
			ob_start();
			reycore__get_template_part('template-parts/woocommerce/header-account-wishlist-count');
			$btn_icon .= ob_get_clean();

			if( in_array($args['button_type'], ['text', 'both_before', 'both_after'], true) ){
				$btn_text = $args['button_text'];

				if( is_user_logged_in() && $args['button_text_logged_in']  ){
					$btn_text = $args['button_text_logged_in'];
				}

				$btn_html = sprintf('<span class="rey-headerAccount-btnText">%s</span>', $btn_text );
			}

			// text included, because the icon is shown on mobiles
			if( $args['button_type'] === 'both_before' ){
				echo $btn_icon;
			}

			echo $btn_html;

			if( in_array($args['button_type'], ['text', 'icon', 'both_after'], true) ){
				echo $btn_icon;
			}

		?>
    </button>

</div>
<!-- .rey-headerAccount-wrapper -->
