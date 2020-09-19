<?php

$users = EnjoyInstagram()->get_users();

?>

<h3><?php _e( 'System info', 'enjoyinstagram' ) ?></h3>

<textarea readonly="readonly" onclick="this.focus();this.select()"
          title="To copy, click the field then press Ctrl + C (PC) or Cmd + C (Mac)."
          style="width: 100%; max-width: 960px; height: 500px; white-space: pre; font-family: Menlo,Monaco,monospace;">
## ENV INFO ##
Site URL:           <?php echo site_url() . "\n" ?>
Home URL:           <?php echo home_url() . "\n" ?>
Wordpress Version:  <?php echo get_bloginfo( 'version' ) . "\n" ?>
PHP Version:        <?php echo phpversion() . "\n" ?>
SAPI:               <?php echo php_sapi_name() . "\n" ?>
WEB Server:         <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n" ?>

## USERS: ##
<?php

foreach ( $users as $user ) {
	var_export( array(
		'id'       => $user['id'],
		'token'    => $user['access_token'],
		'username' => $user['username'],
		'business' => $user['business']
	) );
}
?>

## API RESPONSE ##
<?php
foreach ( $users as $user ) {
	$data  = EnjoyInstagram_Api_Connection()->get_user_profile( $user['id'], $user['access_token'], $user['business'] );
	$error = EnjoyInstagram_Api_Connection()->last_error;

	echo $user['username'] . ": ";
	if ( $data === false && ! empty( $error ) ) {
		echo $error . "\n";
	} else {
		echo "Success!\n";
	}

}

?>

## DATABASE ##
<?php $stats = EnjoyInstagram_DB()->stats(); ?>
Version:        <?php echo get_option( 'enjoy_instagram_installed_db_version' ) . "\n" ?>

*Media*
Count:          <?php echo $stats['media']['count'] . "\n" ?>
	<?php echo $stats['media']['structure'] . "\n" ?>

*Hashtag*
Count:     	<?php echo $stats['hashtag']['count'] . "\n" ?>
	<?php echo $stats['hashtag']['structure'] . "\n" ?>


## ACTIVE PLUGINS: ##
<?php
$plugins        = get_plugins();
$active_plugins = get_option( 'active_plugins', array() );

foreach ( $plugins as $plugin_path => $plugin ) {
	if ( ! in_array( $plugin_path, $active_plugins ) ) {
		continue;
	}

	echo $plugin['Name'] . ': ' . $plugin['Version'] . "\n";
}
?>
</textarea>
<p>
	<a id="ei_reset_db" class="button-secondary"
	   href="<?php echo wp_nonce_url( EnjoyInstagram_Admin()->build_admin_url( 'diagnostic',
		   array( 'action' => 'reset-db' ) ), 'reset-db' ) ?>"><?php _e( 'Re-install Database' ) ?></a>
</p>