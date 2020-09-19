<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$theme_latest_text = $core_latest_text = __('<em>(latest)</em>', 'rey');
?>

<div class="rey-dashBox">
	<div class="rey-dashBox-inner">
		<h2 class="rey-dashBox-title">
			<span><?php esc_html_e('Versions Status', 'rey') ?></span>
		</h2>
		<div class="rey-dashBox-content">

			<table class="rey-systemStatus rey-versionsStatus">

			<tr>
				<td width="180"><?php esc_html_e( 'Rey Theme version:', 'rey' ); ?></td>
				<td>
					<?php
					$theme_update = ReyTheme_Updates::get_theme_latest_update_info();
					printf( '<strong>v%s</strong> %s', REY_THEME_VERSION, $theme_update ? '' : $theme_latest_text );
					echo $theme_update;
					?>
				</td>
			</tr>

			<?php if( defined('REY_CORE_VERSION') ): ?>
				<tr>
					<td width="180"><?php esc_html_e( 'Rey Core version:', 'rey' ); ?></td>
					<td>
						<?php
						$core_update = ReyTheme_Updates::get_core_latest_update_info();
						printf( '<strong>v%s</strong> %s', REY_CORE_VERSION, $core_update ? '' : $core_latest_text );
						echo $core_update;
						?>
					</td>
				</tr>
			<?php endif; ?>

		</table>

		</div>
	</div>
</div>
