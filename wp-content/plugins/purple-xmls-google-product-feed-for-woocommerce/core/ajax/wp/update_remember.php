<?php
/********************************************************************
 * Version 1.0
 * Update user's saved credential information for a particular provider.
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Tyler 2014-12-27
 ********************************************************************/

//To do: This should one day just use the existing update_setting script -KH
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}

if (isset($_POST['remember'])) {
    $remember = sanitize_text_field($_POST['remember']);
    $user_id = sanitize_text_field($_POST['userid']);
    $provider = sanitize_text_field($_POST['provider']);
    update_user_meta($user_id, "cpf_remember_$provider", $remember);
    if ($remember == 'true') {
        $posts = wp_unslash($_POST);
        foreach ($posts as $key => $val) {
            if (!in_array($key, array('remember', 'provider', 'userid')))
                update_user_meta($user_id, "cpf_$key" . "_$provider", $val);
        }
    } else {
        $posts = wp_unslash($_POST);
        foreach ($posts as $key => $val) {
            if (!in_array($key, array('remember', 'provider', 'userid')))
                update_user_meta($user_id, "cpf_$key" . "_$provider", '');
        }
    }
}
