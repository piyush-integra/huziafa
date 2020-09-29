<?php
/*
Plugin Name: Custom New User Email
Author : Muneer panangadan @nexa
Description: Changes the copy in the email sent out to new users
*/
 
// Redefine user notification function
 
if ( !function_exists( 'wp_new_user_notification' ) ) {
    function wp_new_user_notification( $user_id, $plaintext_pass = '' ) {            
        // set content type to html
        add_filter( 'wp_mail_content_type', 'wpmail_content_type' );            
        // user
        $user = new WP_User( $user_id );
        $userEmail = stripslashes( $user->user_email );
        $userName = stripslashes( $user->user_login );
        $siteUrl = get_site_url();      

        $subject = 'New user registration at Al Huzaifa Furniture website';
        $headers = 'From: Al Huzaifa Furniture <noreply@mysite.com>';
        
        
        $message .= '<div style="    background-color: #f7f7f7;width: 100%;float: left;position: relative;display: block;padding: 50px 0px;">';
        $message .= '<table style="background: #ffffff; margin: 0 auto;margin-top:0px;padding:0px 0px;" border-radius="5px" width="60%" cellspacing="0" cellpadding="0" bgcolor="FFFFFF">';
        $message .= '<thead>';
        $message .= '<tr>';
        $message .= '<td style="border-radius:5px 5px 0px 0px;background-color: #80642D; padding: 5px; text-align: center;" colspan="2"><a href="'.$siteUrl.'">';
        $message .= '<img class="aligncenter size-full wp-image-18" src="'.$siteUrl.'/wp-content/uploads/2020/05/Group-14.png" alt="Al Huzaifa Furniture" />';
        $message .= '</a></td>';
        $message .= '</tr>';
        $message .= '</thead>';
        $message .= '<tbody>';
        $message .= '<tr style="background: #dddddd !important;">';
        $message .= '<td style="padding: 15px 20px; background: #dddddd;" width="15%"><strong>Username: </strong></td>';
        $message .= '<td style="padding: 15px 20px; text-align: left; background: #dddddd;">'.$userName.'</td>';
        $message .= '</tr>';
        $message .= '<tr style="background: #f2f2f2;">';
        $message .= '<td style="padding: 15px 20px; background: #f2f2f2;" width="15%"><strong>Email: </strong></td>';
        $message .= '<td style="padding: 15px 20px; text-align: left; background: #f2f2f2;"> '.$userEmail.'</td>';
        $message .= '</tr>';        
        $message .= '</tbody>';
        $message .= '<tfoot>';
        $message .= '<tr>';
        $message .= '<td style="padding: 20px; background: #80642D;border-radius:0px 0px 5px 5px;" colspan="2">';
        $message .= '<p style="text-align: center; color: #fff; font-size: 14px; margin: 10px 0;">© Al Huzaifa Furniture 2020. All Rights Reserved.</p>';
        $message .= '</td>';
        $message .= '</tr>';
        $message .= '</tfoot>';
        $message .= '</table>';
        $message .= '</div>';
        @wp_mail( get_option( 'admin_email' ),  $subject, $message, $headers );       
        remove_filter ( 'wp_mail_content_type', 'wpmail_content_type' );
    }
}                    
 function wpmail_content_type() {
     return 'text/html';
 }?>