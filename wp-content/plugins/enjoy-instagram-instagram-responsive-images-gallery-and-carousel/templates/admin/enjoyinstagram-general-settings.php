<?php
/**
 * Users settings template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // return if called directly
}

$user = EnjoyInstagram()->get_selected_user();

if ( $user ) : ?>
    <div>
        <h2><?php _e( 'Your Instagram Profile',
				'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?></h2>
        <hr/>

        <div id="enjoy_user_profile">
			<?php if ( $user['business'] ): ?>
                <img class="enjoy_user_profile" src="<?php echo $user['profile_picture']; ?>">
			<?php endif; ?>
            <form method="post">
                <input type="hidden" name="action" value="enjoyinstagram_remove_user">
                <input type="submit" id="button_logout"
                       value="<?php _e( 'Unlink Profile',
					       'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>"
                       class="button-primary ei_top"/>
            </form>
        </div>

        <div id="enjoy_user_block">
            <h3><?php echo $user['username'] ?></h3>
			<?php if ( $user['business'] ): ?>
                <p><i><?php echo $user['bio']; ?></i></p>
			<?php endif ?>
            <hr/>
			<?php printf( __( 'Customize the plugin with our <a href="%s">settings</a> tab.',
				'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ),
				EnjoyInstagram_Admin()->get_tab_url( 'enjoyinstagram_advanced_settings' ) ); ?>
            <hr/>
        </div>
    </div>
    <div class="wrap" style="
    float: left;
    width: 95%;
    background: rgba(79, 173, 26, 0.45);
    padding: 20px;
    margin-top: 20px;
    border: 2px solid green;">
        <h3><?php _e( 'Shortocodes to use', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>
            :</h3>
        <b>[enjoyinstagram_mb]</b> -> <?php _e( 'Carousel View',
			'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?><br/>
        <b>[enjoyinstagram_mb_grid]</b> -> <?php _e( 'Grid View',
			'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>
    </div>

<?php else : ?>

    <p>
		<?php _e( 'Thank you for you choise! <strong>Enjoy Instagram - Responsive gallery</strong> is a plugin lovingly developed for you by ',
			'enjoyinstagram' ); ?>
        <a href="http://www.mediabeta.com" target="_blank">Mediabeta</a>.
    </p>

    <p>
        By using this plugin, you are agreeing to the <a href="http://instagram.com/about/legal/terms/api/"
                                                         target="_blank">Instagram API Terms of Use</a>.
    </p>

    <p>
		<?php _e( 'If you are a Business Instagram account connect to Facebook Graph API using the button below to get latest API features and hashtag search',
			'enjoyinstagram' ); ?>
    </p>

    <a href="<?php echo EnjoyInstagram_Admin()->get_facebook_connect_url() ?>" class="button-primary">
		<?php _e( 'Add new user - Business Instagram Account', 'enjoyinstagram' ) ?>
    </a>

    <a href="#" class="enjoy-instagram-help"
       title="<?php _e( 'Business accounts have access to everything from basic profile with full name, bio, website, following count, followers count, profile picture, feed of posts and hashtag feed',
		   'enjoyinstagram' ) ?>">[?]</a>
    <p>
		<?php _e( 'Click the button below to connect a standard Instagram account',
			'enjoyinstagram' ) ?>
    </p>
    <a href="<?php echo EnjoyInstagram_Admin()->get_instagram_login_url() ?>" class="button-secondary">
		<?php _e( 'Add new user - Personal Instagram Account', 'enjoyinstagram' ); ?>
    </a>
    <a href="#" class="enjoy-instagram-help"
       title="<?php _e( 'Personal accounts have access to profile info such as username, media count, user id and feed of posts',
		   'enjoyinstagram' ) ?>">[?]</a>

<?php endif; ?>
