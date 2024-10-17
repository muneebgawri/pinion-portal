<?php
$profile_header_template = wpuf_get_option( 'profile_header_template', 'user_directory', 'layout' );
$avatar_size  = wpuf_get_option( 'avatar_size', 'user_directory', 120 );

$profile_fields = WPUF_User_Listing()->shortcode->get_options();
// get the saved profile tabs from userlisting builder setting
$profile_tabs = ! empty( $profile_fields['profile_tabs'] ) ? $profile_fields['profile_tabs'] : [];

$saved_tabs = [];

$account_page_id = wpuf_get_option( 'account_page', 'wpuf_my_account', false );
$account_page_link = get_page_link( $account_page_id );
$private_message_link = $account_page_link . '?section=message#/user/' . $user->ID;

// if profile tabs are set from userlisting builder
if ( count( $profile_tabs ) ) {
    foreach ( $profile_tabs as $key => $value ) {
        if ( ! empty( $value['show_tab'] ) ) {
            $saved_tabs[ $key ] = [
                'label' => $value['label'],
                'id'    => $value['id'],
            ];
        }
    }
}

$current_tab = ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'posts'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$all_data['profile_header_template'] = $profile_header_template;
$all_data['avatar_size']    = $avatar_size;
$all_data['profile_fields'] = $profile_fields;
$all_data['profile_tabs']   = $profile_tabs;
$all_data['saved_tabs']     = $saved_tabs;
$all_data['user']           = $user;
$all_data['current_tab']    = $current_tab;
$all_data['profile_permalink_base'] = $profile_permalink_base;
$all_data['profile_base_value'] = $profile_base_value;

$user_bio = $user->description;
$phone_no = get_user_meta( $user->ID, 'phone', true );

$all_data['user_email'] = apply_filters( 'wpuf_profile_header_user_email', $user->user_email );
$all_data['user_url'] = apply_filters( 'wpuf_profile_header_user_url', ! empty( $user->user_url ) ? esc_url( $user->user_url ) : '' );
$all_data['user_bio'] = apply_filters( 'wpuf_profile_header_user_bio', ! empty( $user_bio ) ? wp_kses( $user_bio, wp_kses_allowed_html( 'user_description' ) ) : '' );
$all_data['user_phone'] = apply_filters( 'wpuf_profile_header_user_phone', ! empty( $phone_no ) ? esc_html( $phone_no ) : '' );

switch ( $profile_header_template ) {
    case 'layout':
        wp_enqueue_style( 'wpuf-ud-layout-one' );
        wpuf_load_pro_template( 'layout-one.php', $all_data, WPUF_UD_TEMPLATES . '/profile/layouts/' );
        break;
    case 'layout1':
        wp_enqueue_style( 'wpuf-ud-layout-two' );
        wpuf_load_pro_template( 'layout-two.php', $all_data, WPUF_UD_TEMPLATES . '/profile/layouts/' );
        break;
    case 'layout2':
        wp_enqueue_style( 'wpuf-ud-layout-three' );
        wpuf_load_pro_template( 'layout-three.php', $all_data, WPUF_UD_TEMPLATES . '/profile/layouts/' );
        break;
}

if ( ! defined( 'WPUF_PM_DIR' ) ) {
    return;
}
?>
<div class="wpuf-floating-message-button">
    <a href="<?php echo $private_message_link; ?>" target="_blank">
        <?php echo apply_filters( 'wpuf_message_floating_button_text', esc_html__( 'Message', 'wpuf-pro' ) ); ?>
    </a>
</div>
