<?php
/**
 * Main plugin options array
 * The array is divided by Settings Group
 *
 * array(
 *   group => array(
 *      option_id => default
 *   )
 * )
 *
 */

return apply_filters( 'enjoyinstagram_main_array_options', array(
    'enjoyinstagram_options_group_auth'          => array(
        'enjoyinstagram_client_id'                          => '',
        'enjoyinstagram_client_secret'                      => '',
        'enjoyinstagram_client_code'                        => '',
        'enjoyinstagram_user_instagram'                     => '',
        'enjoyinstagram_user_id'                            => '',
        'enjoyinstagram_user_username'                      => '',
        'enjoyinstagram_user_profile_picture'               => '',
        'enjoyinstagram_user_fullname'                      => '',
        'enjoyinstagram_user_website'                       => '',
        'enjoyinstagram_user_bio'                           => '',
        'enjoyinstagram_access_token'                       => '',
    ),
    'enjoyinstagram_options_carousel_group' => array(
        'enjoyinstagram_carousel_items_number'              => '4',
        'enjoyinstagram_carousel_navigation'                => 'false',
        'enjoyinstagram_grid_rows'                          => '2',
        'enjoyinstagram_grid_cols'                          => '5',
        'enjoyinstagram_hashtag'                            => '',
        'enjoyinstagram_user_or_hashtag'                    => 'user',
    )
));