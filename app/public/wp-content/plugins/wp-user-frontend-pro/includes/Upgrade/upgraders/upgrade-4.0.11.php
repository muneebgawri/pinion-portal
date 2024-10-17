<?php

function wpuf_upgrade_4_0_11_email_body_update() {
    global $wpdb;

    // get 'wpuf_mails' option from wp_options table
    $mails = get_option( 'wpuf_mails', [] );

    if ( empty( $mails ) ) {
        return;
    }

    $search  = [
        '%activation_link%',
        '%username%',
        '%sub_pack_name%',
        '%sub_expiration_date%',
        '%sub_start_date%',
        '%sub_end_date%',
        '%sub_pack_price%',
        '%user_email%',
        '%display_name%',
        '%user_status%',
        '%pending_users%',
        '%approved_users%',
        '%denied_users%',
        '%login_url%',
        '%blog_name%',
        '%post_title%',
        '%post_link%',
        '%blogname%',
        '%password_reset_link%',
    ];
    $replace = [
        '{activation_link}',
        '{username}',
        '{sub_pack_name}',
        '{sub_expiration_date}',
        '{sub_start_date}',
        '{sub_end_date}',
        '{sub_pack_price}',
        '{user_email}',
        '{display_name}',
        '{user_status}',
        '{pending_users}',
        '{approved_users}',
        '{denied_users}',
        '{login_url}',
        '{blog_name}',
        '{post_title}',
        '{post_link}',
        '{blogname}',
        '{password_reset_link}',
    ];

    $mail_keys = [
        'guest_email_body',
        'pre_sub_exp_body',
        'post_sub_exp_body',
        'pending_user_email_body',
        'denied_user_email_body',
        'approved_user_email_body',
        'account_activated_user_email_body',
        'approved_post_email_body',
    ];

    foreach ( $mail_keys as $mail_key ) {
        if ( isset( $mails[ $mail_key ] ) ) {
            $mails[ $mail_key ] = str_replace( $search, $replace, $mails[ $mail_key ] );
        }
    }

    update_option( 'wpuf_mails', $mails );

    // get all the post meta from postmeta table where meta_key is 'wpuf_form_settings'
    $form_settings = $wpdb->get_results( "SELECT * FROM {$wpdb->postmeta} WHERE meta_key = 'wpuf_form_settings'" );

    if ( empty( $form_settings ) ) {
        return;
    }

    foreach ( $form_settings as $form_setting ) {
        $unserilized = maybe_unserialize( $form_setting->meta_value );

        $search_key_arr = [
            'verification_subject',
            'verification_body',
            'welcome_email_subject',
            'welcome_email_body',
            'admin_email_subject',
        ];
        $admin_mail_keys = [
            'user_status_pending',
            'user_status_approved',
        ];

        foreach ( $search_key_arr as $search_key ) {
            if ( isset( $unserilized['notification'][ $search_key ] ) ) {
                $mail_body         = $unserilized['notification'][ $search_key ];
                $updated_mail_body = str_replace( $search, $replace, $mail_body );

                $unserilized['notification'][ $search_key ] = $updated_mail_body;
            }
        }

        foreach ( $admin_mail_keys as $admin_mail_key ) {
            if ( isset( $unserilized['notification']['admin_email_body'][ $admin_mail_key ] ) ) {
                $admin_mail_body         = $unserilized['notification']['admin_email_body'][ $admin_mail_key ];
                $updated_admin_mail_body = str_replace( $search, $replace, $admin_mail_body );

                $unserilized['notification']['admin_email_body'][ $admin_mail_key ] = $updated_admin_mail_body;
            }
        }

        update_post_meta( $form_setting->post_id, 'wpuf_form_settings', $unserilized );
    }
}

wpuf_upgrade_4_0_11_email_body_update();
