<?php
/**
 * Single Product tabs
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/tabs.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter tabs and allow third parties to add their own.
 *
 * Each tab is an array containing title, callback and priority.
 * @see woocommerce_default_product_tabs()
 */
$tabs = apply_filters( 'woocommerce_product_tabs', array() );

if ( ! empty( $tabs ) ) :

	$blocks = [];
	$panels = ['description', 'additional_information', 'information'];
	?>

	<div class="rey-wcPanels <?php echo implode(' ', array_map('esc_attr', apply_filters('rey/woocommerce/product_panels_classes', []))) ?>">
		<?php foreach ( $tabs as $key => $tab ) :

			if( !in_array( $key, $panels ) ) {
				$blocks[$key] = $tab;
				continue;
			}
			?>
			<div class="rey-wcPanel rey-wcPanel--<?php echo esc_attr( $key ); ?>">
				<?php
					if ( isset( $tab['callback'] ) ) {
						call_user_func( $tab['callback'], $key, $tab );
					}
				?>
			</div>
		<?php endforeach; ?>
	</div>

	<?php do_action('reycore/woocommerce/before_blocks_review'); ?>

	<?php if ( ! empty( $blocks ) ) : ?>
		<div class="rey-wcBlocks">
			<?php foreach ( $blocks as $key => $tab ) : ?>
				<div class="rey-wcBlock rey-wcBlock--<?php echo esc_attr( $key ); ?>">
					<?php

						if( $key == 'reviews' && isset( $tab['title'] ) ) {
							printf( '<div class="rey-reviewsBtn btn btn-secondary-outline btn--block js-toggle-target"  data-target=".rey-wcBlock--reviews .woocommerce-Reviews"><span>%s</span></div>', $tab['title'] );
						}

						if ( isset( $tab['callback'] ) ) {
							call_user_func( $tab['callback'], $key, $tab );
						}
					?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>


<?php endif; ?>
