<?php
/**
 * Appearance settings template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // return if called directly
}

$user = EnjoyInstagram()->get_selected_user();

?>

<form method="post" action="options.php" novalidate>
	<?php settings_fields( 'enjoyinstagram_options_carousel_group' ); ?>

    <script type="text/javascript">
      jQuery(document).ready(function ($) {
        $("input[name$='enjoyinstagram_user_or_hashtag']").click(function () {
          var test = $(this).val();

          if (test === 'public_hashtag') {
            test = 'hashtag';
          }

          if (test === 'user') {
            $('#enjoyinstagram_hashtag').attr('disabled', true);
          } else if (test === 'hashtag') {
            $('#enjoyinstagram_hashtag').attr('disabled', false);
          }
          $("div.desc").hide();
          $("#enjoyinstagram_user_or_hashtag_" + test).show();
        });
      });
    </script>

    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" style="align:left;">
                    <span class="highlighted">
                        <?php _e( 'Inclusion mode',
	                        'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>:
                    </span>
            </th>
            <td>
                <div class="ei_block">
                    <div class="ei_settings_float_block">
						<?php _e( 'Show pics', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>:
                    </div>
                    <div class="ei_settings_float_block">
                        <input type="radio"
                               name="enjoyinstagram_user_or_hashtag" <?php checked( 'user',
							get_option( 'enjoyinstagram_user_or_hashtag', 'user' ) ) ?>
                               value="user">
						<?php _ex( 'of Your Profile', 'option label',
							'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?><br/><br/>
                        <input type="radio"
                               name="enjoyinstagram_user_or_hashtag" <?php checked( 'hashtag',
							get_option( 'enjoyinstagram_user_or_hashtag', 'user' ) ) ?>
                               value="hashtag">
						<?php _ex( 'by Hashtag', 'option label',
							'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?><br/>

						<?php if ( $user !== false && $user['business'] ): ?>
                            <br/>
                            <input type="radio"
                                   name="enjoyinstagram_user_or_hashtag" <?php checked( 'public_hashtag',
								get_option( 'enjoyinstagram_user_or_hashtag', 'user' ) ) ?>
                                   value="public_hashtag">
							<?php _ex( 'by Public Hashtag', 'option label',
								'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?><br/>
						<?php endif ?>
                    </div>
                    <div class="ei_settings_float_block">
                        <div id="enjoyinstagram_user_or_hashtag_user"
                             class="desc" <?php if ( get_option( 'enjoyinstagram_user_or_hashtag',
								'user' ) != 'user' ) {
							echo 'style="display:none;"';
						} ?>>
                            <input type="text" class="ei_disabled" id="enjoyinstagram_user" disabled
                                   value="<?php echo get_option( 'enjoyinstagram_user_username' ); ?>"
                                   name="enjoyinstagram_user"/>
                        </div>
                        <div id="enjoyinstagram_user_or_hashtag_hashtag"
                             class="desc" <?php if ( strpos( get_option( 'enjoyinstagram_user_or_hashtag', 'user' ),
								'hashtag' ) === false ) {
							echo 'style="display:none;"';
						} ?>>
                            #<input type="text" id="enjoyinstagram_hashtag" required
                                    value="<?php echo get_option( 'enjoyinstagram_hashtag' ); ?>"
                                    name="enjoyinstagram_hashtag"/>
                            <span class="description"><?php _ex( "insert a hashtag without '#'", 'option description',
									'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?></span>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        </tbody>
    </table>

    <hr/>

    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" style="align:left;">
                <span class="highlighted">
                    <?php _e( 'Carousel settings',
	                    'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>:
                </span>
            </th>
            <td>
                <div class="ei_block">
                    <div class="ei_settings_float_block ei_fixed">
                        <label for="enjoyinstagram_carousel_items_number">
							<?php _ex( 'Images displayed at a time', 'option label',
								'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>:
                        </label>
                    </div>
                    <div class="ei_settings_float_block">
                        <select name="enjoyinstagram_carousel_items_number" class="ei_sel"
                                id="enjoyinstagram_carousel_items_number">
							<?php for ( $i = 1; $i <= 10; $i ++ ): ?>
                                <option value="<?php echo $i ?>" <?php selected( $i,
									get_option( 'enjoyinstagram_carousel_items_number', '4' ) ); ?>>
									<?php echo "&nbsp;" . $i; ?>
                                </option>
							<?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="ei_block">
                    <div class="ei_settings_float_block ei_fixed">
                        <label for="enjoyinstagram_carousel_navigation">
							<?php _ex( 'Navigation buttons', 'option label',
								'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>:
                        </label>
                    </div>
                    <div class="ei_settings_float_block">
                        <select name="enjoyinstagram_carousel_navigation" class="ei_sel"
                                id="enjoyinstagram_carousel_navigation">
                            <option value="true" <?php selected( 'true',
								get_option( 'enjoyinstagram_carousel_navigation', 'false' ) ); ?>>
								<?php _e( 'Yes',
									'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>
                            </option>
                            <option value="false" <?php selected( 'false',
								get_option( 'enjoyinstagram_carousel_navigation', 'false' ) ); ?>>
								<?php _e( 'No', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>
                            </option>
                        </select>
                    </div>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
    <hr/>
    <!-- SHORTCODE WALL GRID -->
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" style="align:left;">
                <span class="highlighted">
                    <?php _e( 'Grid view settings',
	                    'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>:
                </span>
            </th>
            <td>
                <div class="ei_block">
                    <div class="ei_settings_float_block ei_fixed">
                        <label for="enjoyinstagram_grid_cols">
							<?php _ex( 'Number of Columns', 'option label',
								'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>:
                        </label>
                    </div>
                    <div class="ei_settings_float_block">
                        <select name="enjoyinstagram_grid_cols" id="enjoyinstagram_grid_cols" class="ei_sel">
							<?php for ( $i = 1; $i <= 10; $i ++ ) : ?>
                                <option value="<?php echo $i ?>" <?php selected( $i,
									get_option( 'enjoyinstagram_grid_cols', '5' ) ); ?>>
									<?php echo "&nbsp;" . $i; ?>
                                </option>
							<?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="ei_block">
                    <div class="ei_settings_float_block ei_fixed">
                        <label for="enjoyinstagram_grid_rows">
							<?php _ex( 'Number of Rows', 'option label',
								'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>:
                        </label>
                    </div>
                    <div class="ei_settings_float_block">
                        <select name="enjoyinstagram_grid_rows" id="enjoyinstagram_grid_rows" class="ei_sel">
							<?php for ( $i = 1; $i <= 10; $i ++ ) : ?>
                                <option value="<?php echo $i ?>" <?php selected( $i,
									get_option( 'enjoyinstagram_grid_rows', '2' ) ); ?>>
									<?php echo "&nbsp;" . $i; ?>
                                </option>
							<?php endfor; ?>
                        </select>
                    </div>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
    <hr/>
    <p>
		<?php _e( '<strong>Free version</strong>: Only 20 images allowed.',
			'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>
    </p>
    <input type="submit" class="button-primary" id="button_enjoyinstagram_advanced"
           name="button_enjoyinstagram_advanced"
           value="<?php _e( 'Save Settings', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>">
</form>
<div class="wrap" style="
    float: left;
    width: 95%;
    background: rgba(79, 173, 26, 0.45);
    padding: 20px;
    margin-top: 20px;
    border: 2px solid green;">
    <h3><?php _e( 'Shortocodes to use', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>:</h3>
    [enjoyinstagram_mb] &raquo; <?php _e( 'Carousel View',
		'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?><br/>
    [enjoyinstagram_mb_grid] &raquo; <?php _e( 'Grid View',
		'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>
</div>

