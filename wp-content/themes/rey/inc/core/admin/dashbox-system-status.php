<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="rey-dashBox">
	<div class="rey-dashBox-inner">

		<h2 class="rey-dashBox-title"><?php esc_html_e('System Status', 'rey') ?></h2>

		<div class="rey-dashBox-content">

			<table class="rey-systemStatus">

				<tr>
					<td width="180"><?php esc_html_e( 'Install Location:', 'rey' ); ?></td>
					<td>
						<?php
						if ( get_template() === REY_THEME_NAME ) {
							echo rey__wp_kses( sprintf( '<code class="ssFlag ssFlag--success">%s</code>', esc_html__( 'Standard', 'rey' ) ) );
						} else {
							echo rey__wp_kses( sprintf( '<code class="ssFlag ssFlag--danger">%s</code>', __( 'Non-standard', 'rey' ) ) );
							echo rey__wp_kses( sprintf( __( 'Using %s Theme from non-standard install location or having a different directory name could lead to issues in receiving and installing updates. Please make sure that theme folder name is <strong>%s</strong>, without spaces.', 'rey' ), ucfirst(REY_THEME_NAME), REY_THEME_NAME ) );
						}
						?>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'File System Accessible:', 'rey' ); ?></td>
					<td>
						<?php
						global $wp_filesystem;

						if ( $wp_filesystem || WP_Filesystem() ) {
							echo rey__wp_kses( sprintf( '<code class="ssFlag ssFlag--success">%s</code>', esc_html__( 'Yes', 'rey' ) ) );
						} else {
							echo rey__wp_kses( sprintf( '<code class="ssFlag ssFlag--danger">%s</code><p>%s</p>',
								__( 'No', 'rey' ),
								__( 'Theme has no direct access to the file system. Therefore plugins and pre-made websites installation is not possible to work properly.<br>Please try to insert the following code: <code>define( "FS_METHOD", "direct" );</code><br>before <code>/* That\'s all, stop editing! Happy blogging. */</code> in <code>wp-config.php</code>.', 'rey' ) )
							);
						}
						?>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Uploads Folder Writable:', 'rey' ); ?></td>
					<td>
					<?php
						$wp_uploads = wp_get_upload_dir();
						if ( wp_is_writable( trailingslashit( $wp_uploads['basedir'] ) ) ) {
							echo rey__wp_kses( sprintf( '<code class="ssFlag ssFlag--success">%s</code>', esc_html__( 'Yes', 'rey' ) ) );
						} else {
							echo rey__wp_kses( sprintf( '<code class="ssFlag ssFlag--danger">%s</code><p>%s</p>',
								__( 'No', 'rey' ),
								__( 'Uploads folder must be writable to allow WordPress function properly.<br>See <a href="https://codex.wordpress.org/Changing_File_Permissions" target="_blank">changing file permissions</a> or contact your hosting provider.', 'rey' ) )
							);
						}
					?>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'ZipArchive Support:', 'rey' ); ?></td>
					<td>
					<?php
					if ( class_exists( 'ZipArchive' ) ) {
						echo rey__wp_kses( sprintf( '<code class="ssFlag ssFlag--success">%s</code>', esc_html__( 'Yes', 'rey' ) ) );
					} else {
						echo rey__wp_kses( sprintf( '<code class="ssFlag ssFlag--danger">%s</code><p>%s</p>',
							__( 'No', 'rey' ),
							__( 'ZipArchive is required for plugins installation and pre-made websites import.<br>Please contact your hosting provider.', 'rey' ) )
						);
					}
					?>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'PHP Version:', 'rey' ); ?></td>
					<td>
					<?php
					$php_version = PHP_VERSION;
					if ( version_compare( '7.2.0', $php_version, '>' ) ) {
						echo rey__wp_kses( sprintf( '<code class="ssFlag ssFlag--warning">%s</code> %s',
							$php_version,
							__( 'Current version is sufficient. However <strong>v.7.2.0</strong> or greater is recommended to improve the performance.', 'rey' ) )
						);
					} else {
						echo rey__wp_kses( sprintf( '<code class="ssFlag ssFlag--success">%s</code> %s',
							$php_version,
							__( 'Current version is sufficient.', 'rey' ) )
						);
					}
					?>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'PHP Max Input Vars:', 'rey' ); ?></td>
					<td>
					<?php
					$max_input_vars = ini_get( 'max_input_vars' );
					if ( $max_input_vars < 1000 ) {
						echo rey__wp_kses( sprintf( '<code class="ssFlag ssFlag--danger">%s</code> %s',
							$max_input_vars,
							__( 'Minimum value is <strong>1000</strong>. <strong>2000</strong> is recommended. <strong>3000</strong> or more may be required if lots of plugins are in use and/or you have a large amount of menu items.', 'rey' ) )
						);

					} elseif ( $max_input_vars < 2000 ) {
						echo rey__wp_kses( sprintf( '<code class="ssFlag ssFlag--warning">%s</code> %s',
							$max_input_vars,
							__( 'Current limit is sufficient for most tasks. <strong>2000</strong> is recommended. <strong>3000</strong> or more may be required if lots of plugins are in use and/or you have a large amount of menu items.', 'rey' ) )
						);
					} elseif ( $max_input_vars < 3000 ) {
						echo rey__wp_kses( sprintf( '<code class="ssFlag ssFlag--success">%s</code> %s',
							$max_input_vars,
							__( 'Current limit is sufficient. However, up to <strong>3000</strong> or more may be required if lots of plugins are in use and/or you have a large amount of menu items.', 'rey' ) )
						);
					} else {
						echo rey__wp_kses( sprintf( '<code class="ssFlag ssFlag--success">%s</code> %s',
							$max_input_vars,
							__( 'Current limit is sufficient.', 'rey' ) )
						);
					}
					?>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'WP Memory Limit:', 'rey' ); ?></td>
					<td>
					<?php

					$memory = wp_convert_hr_to_bytes( @ini_get( 'memory_limit' ) );

					// translators: %1$s - wp codex article url.
					$tip = rey__wp_kses( sprintf(  __( '<br><small>See <a href="%1$s" target="_blank">increasing memory allocated to PHP</a> or contact your hosting provider.</small>', 'rey' ), 'http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP' ) );

					if ( $memory < 67108864 ) {
						echo rey__wp_kses(
							sprintf( '<code class="ssFlag ssFlag--danger">%s</code> %s %s',
								size_format( $memory ),
								__( 'Minimum value is <strong>64 MB</strong>. <strong>128 MB</strong> is recommended. <strong>256 MB</strong> or more may be required if lots of plugins are in use and/or you want to install the London Demo.', 'rey' ),
								$tip
							)
						);
					} elseif ( $memory < 134217728 ) {
						echo rey__wp_kses(
							sprintf( '<code class="ssFlag ssFlag--warning">%s</code> %s %s',
								size_format( $memory ),
								__( 'Current memory limit is sufficient for most tasks. However, recommended value is <strong>128 MB</strong>. <strong>256 MB</strong> or more may be required if lots of plugins are in use and/or you want to install the London Demo.', 'rey' ),
								$tip
							)
						);
					} elseif ( $memory < 268435456 ) {
						echo rey__wp_kses(
							sprintf( '<code class="ssFlag ssFlag--success">%s</code> %s %s',
								size_format( $memory ),
								__( 'Current memory limit is sufficient. However, <strong>256 MB</strong> or more may be required if lots of plugins are in use and/or you want to install the London Demo.', 'rey' ),
								$tip
							)
						);
					} else {
						echo rey__wp_kses(
							sprintf( '<code class="ssFlag ssFlag--success">%s</code> %s',
								size_format( $memory ),
								__( 'Current memory limit is sufficient.', 'rey' )
							)
						);
					}
					?>
					</td>
				</tr>
				<?php if ( function_exists( 'ini_get' ) ) : ?>
					<tr>
						<td><?php esc_html_e( 'PHP Time Limit:', 'rey' ); ?></td>
						<td>
							<?php
							$time_limit = ini_get( 'max_execution_time' );

							// translators: %1$s - wp codex article url.
							$tip = rey__wp_kses( sprintf( __( '<br><small>See <a href="%1$s" target="_blank">increasing max PHP execution time</a> or contact your hosting provider.</small>', 'rey' ), 'http://codex.wordpress.org/Common_WordPress_Errors#Maximum_execution_time_exceeded' ) );

							if ( 30 > $time_limit && 0 != $time_limit ) {
								echo rey__wp_kses(
									sprintf( '<code class="ssFlag ssFlag--danger">%s</code> %s %s',
										$time_limit,
										__( 'Minimum value is <strong>30</strong>. <strong>60</strong> is recommended.', 'rey' ),
										$tip
									)
								);
							} elseif ( (60 > $time_limit && 30 <= $time_limit) && 0 != $time_limit ) {
								echo rey__wp_kses(
									sprintf( '<code class="ssFlag ssFlag--warning">%s</code> %s %s',
										$time_limit,
										__( 'Current time limit is sufficient, however <strong>60</strong> is recommended.', 'rey' ),
										$tip
									)
								);
							} elseif ( 60 <= $time_limit && 0 != $time_limit ) {
								echo rey__wp_kses(
									sprintf( '<code class="ssFlag ssFlag--success">%s</code> %s %s',
										$time_limit,
										__( 'Current time limit should be sufficient.', 'rey' ),
										$tip
									)
								);
							} else {
								echo rey__wp_kses(
									sprintf( '<code class="ssFlag ssFlag--success">%s</code> %s',
										_x( 'unlimited', 'Time limit status.', 'rey' ),
										__( 'Current time limit is sufficient.', 'rey' )
									)
								);
							}
							?>
						</td>
					</tr>
				<?php endif; ?>
			</table>

		</div>
	</div>
</div>
