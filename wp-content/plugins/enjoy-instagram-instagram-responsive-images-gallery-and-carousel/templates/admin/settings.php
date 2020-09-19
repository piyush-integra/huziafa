<?php
/**
 * Main plugin settings template
 */

if( ! defined( 'ABSPATH' ) ) {
    exit; // return if called directly
}

?>
<div class="wrap">
    <div class="ei_block">
        <div class="ei_left_block">
            <div class="ei_hard_block">
                <img src="<?php echo ENJOYINSTAGRAM_ASSETS_URL . '/images/enjoyinstagram.png'; ?>">
            </div>

            <div class="ei_twitter_block">
                <a href="https://twitter.com/share" class="twitter-share-button" data-url="http://www.mediabeta.com/enjoy-instagram/" data-text="I've just installed Enjoy Plugin for Instagram for wordpress. Awesome!" data-hashtags="wordpress">Tweet</a>
                <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');
                </script>
            </div>

            <div id="fb-root"></div>
            <script>(function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s); js.id = id;
                js.src = "//connect.facebook.net/it_IT/sdk.js#xfbml=1&appId=359330984151581&version=v2.0";
                fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk'));</script>
            <div class="ei_facebook_block">
                <div class="fb-like" data-href="http://www.mediabeta.com/enjoy-instagram/" data-layout="button_count" data-action="like" data-show-faces="true" data-share="true">
                </div>
            </div>
        </div>

        <div id="buy_me_a_coffee" style="background:url(<?php echo  ENJOYINSTAGRAM_ASSETS_URL . '/images/buymeacoffee.png'; ?>)#fff no-repeat; ">
            <div class="pad_coffee">
                <span class="coffee_title"><?php _e( 'Buy me a coffee!', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?></span>
                <p><span><?php _e( 'If you liked our work please consider to make a kind donation through Paypal.', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?></span></p>
                <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                    <input type="hidden" name="cmd" value="_s-xclick" />
                    <input type="hidden" name="hosted_button_id" value="PAZ58MQY9SVCJ" />
                    <input type="image" src="https://www.paypalobjects.com/en_US/IT/i/btn/btn_donateCC_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" />
                    <img alt="" border="0" src="https://www.paypal.com/en_IT/i/scr/pixel.gif" width="1" height="1" />
                </form>
            </div>
        </div>
    </div>

    <div class="ei_block">
        <div id="premium_release">
            <div class="pad_premium_release">
                <span class="coffee_title">
                    <?php printf( __( 'Discover interesting OFFERS and UPDATES for <a href="%s"> Premium Version</a> !', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ), 'http://www.mediabetaprojects.com/enjoy-instagram-premium/coupon-code-enjoy-instagram-premium/' ); ?>
                </span>
                <p>
                    <span style="color:#900; font-weight: bold;">
                        <?php printf( __( 'Visit Now <a href="%s">ENJOY INSTAGRAM PREMIUM</a> to verify if a Coupon-Code is ready for you!', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ), 'http://www.mediabetaprojects.com/enjoy-instagram-premium/coupon-code-enjoy-instagram-premium/' ); ?>
                    </span>
                </p>
            </div>
        </div>
    </div>

    <h2 class="nav-tab-wrapper">
    <?php foreach( $tabs as $tab_id => $tab ) : ?>
        <a class="nav-tab <?php echo $active_tab == $tab_id ? 'nav-tab-active' : ''; ?>" href="<?php echo EnjoyInstagram_Admin()->get_tab_url( $tab_id ); ?>">
            <?php echo $tab ?>
        </a>
    <?php endforeach; ?>
    </h2>
    <?php
    // include template
    $active_tab && $active_tab = str_replace( '_', '-', $active_tab );
    $active_tab && include( ENJOYINSTAGRAM_TEMPLATE_PATH . '/admin/' . $active_tab . '.php' );
    ?>
</div>
