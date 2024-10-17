<?php
$profile_header_template = wpuf_get_option( 'profile_header_template', 'user_directory', 'layout' );

$profile_fields = $this->get_options();
// get the saved profile tabs from userlisting builder setting
$profile_tabs = isset( $profile_fields['profile_tabs'] ) ? $profile_fields['profile_tabs'] : [];

$saved_tabs = [];

// if profile tabs are set from userlisting builder
if ( count( $profile_tabs ) ) {
    foreach ( $profile_tabs as $saved ) {
        foreach ( $saved as $key => $value ) {
            if ( $value['show_tab'] ) {
                $saved_tabs[ $key ] = [
                    'label' => $value['label'],
                    'id'    => $value['id'],
                ];
            }
        }
    }
}

$file_name = 'layout-one.php';
switch ( $profile_header_template ) {
    case 'layout1':
        $file_name = 'layout-two.php';
        break;
    case 'layout2':
        $file_name = 'layout-three.php';
        break;
}

wpuf_load_pro_template( $file_name, [], WPUF_UD_TEMPLATES . '/profile/layouts/' );
