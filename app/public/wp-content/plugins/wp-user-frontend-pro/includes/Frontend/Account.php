<?php

namespace WeDevs\Wpuf\Pro\Frontend;

use WP_Error;

/**
 * Pro functionality for frontend account page
 *
 * @since 2.8.2
 */
class Account {

    public function __construct() {
        add_filter( 'wp_authenticate_user', [ $this, 'validate_login' ] );
        add_filter( 'wpuf_account_sections', [ $this, 'manage_account_sections' ] );
        add_filter( 'wpuf_my_account_tab_links', [ $this, 'manage_account_tab_links' ] );
        add_filter( 'wpuf_account_edit_profile_content', [ $this, 'edit_profile_content' ] );
    }

    /**
     * Show/Hide frontend account section depending on Edit Profile option
     *
     * @return array $sections
     */
    public function manage_account_sections( $sections ) {
        $allow_profile_edit = wpuf_get_option( 'show_edit_profile_menu', 'wpuf_my_account', 'on' );
        if ( $allow_profile_edit != 'on' ) {
            foreach ( $sections as $section => $label ) {
                if ( $section == 'edit-profile' ) {
                    unset( $sections[ $section ] );
                }
            }

            return $sections;
        }

        return $sections;
    }

    /**
     * Show/Hide frontend account section depending on Edit Profile option
     *
     * @return array $sections
     */
    public function manage_account_tab_links( $links ) {
        $allow_profile_edit = wpuf_get_option( 'show_edit_profile_menu', 'wpuf_my_account', 'on' );
        if ( $allow_profile_edit != 'on' ) {
            unset( $links['edit-profile'] );
        }

        return $links;
    }

    /**
     * Display content on frontend account page
     *
     * @return string $content
     */
    public function edit_profile_content( $content ) {
        $edit_profile_form = wpuf_get_option( 'edit_profile_form', 'wpuf_my_account', '-1' );
        if ( $edit_profile_form != '-1' ) {
            $content = do_shortcode( '[wpuf_profile type="profile" id="' . $edit_profile_form . '"]' );
        }

        return $content;
    }

    /**
     * Generate error for login form based on user status
     *
     * @return string
     */
    public function validate_login( $user ) {
        $status = $this->get_user_status( $user->ID );

        switch ( $status ) {
            case 'pending':
                $pending_message = $this->get_authentication_message( 'pending' );
                $user = new WP_Error( 'wpuf_pending_user_error', $pending_message );
                break;
            case 'denied':
                $denied_message = $this->get_authentication_message( 'denied' );
                $user = new WP_Error( 'wpuf_denied_user_error', $denied_message );
                break;
            default:
        }

        return $user;
    }

    /**
     * Get the status of a user.
     *
     * @param int $user_id
     *
     * @return string the status of the user
     */
    public function get_user_status( $user_id ) {
        $user_status = get_user_meta( $user_id, 'wpuf_user_status', true );

        if ( empty( $user_status ) ) {
            $user_status = 'approved';
        }

        return $user_status;
    }

    /**
     * The default message that is shown to a user depending on their status when trying to sign in.
     *
     * @return string
     */
    public function get_authentication_message( $status ) {
        $message = '';
        $default_pending_msg = __(
            '<strong>ERROR:</strong> Your account has to be approved by an administrator before you can login.',
            'wpuf-pro'
        );
        $default_denied_msg = __(
            '<strong>ERROR:</strong> Your account has been denied by an administrator, please contact admin to approve your account.',
            'wpuf-pro'
        );

        $pending_user_message = wpuf_get_option( 'pending_user_message', 'wpuf_profile', $default_pending_msg );
        $denied_user_message  = wpuf_get_option( 'denied_user_message', 'wpuf_profile', $default_denied_msg );

        if ( 'pending' === $status ) {
            $message = $pending_user_message;
        } elseif ( 'denied' === $status ) {
            $message = $denied_user_message;
        }

        return $message;
    }
}
