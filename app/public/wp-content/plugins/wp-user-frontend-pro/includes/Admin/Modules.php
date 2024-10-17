<?php

namespace WeDevs\Wpuf\Pro\Admin;

/**
 * The modules class
 */
class Modules {

    /**
     * Toggle module
     *
     * @since 2.7
     *
     * @return void
     **/
    public function toggle_module() {
        if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( $_POST['nonce'], 'wpuf-admin-nonce' ) ) {
            wp_send_json_error( __( 'Invalid nonce', 'wpuf-pro' ) );
        }
        $module = isset( $_POST['module'] ) ? sanitize_text_field( $_POST['module'] ) : '';
        $type   = isset( $_POST['type'] ) ? $_POST['type'] : '';
        if ( ! $module ) {
            wp_send_json_error( __( 'Invalid module provided', 'wpuf-pro' ) );
        }
        if ( ! in_array( $type, [ 'activate', 'deactivate' ] ) ) {
            wp_send_json_error( __( 'Invalid request type', 'wpuf-pro' ) );
        }
        $module_data = wpuf_pro_get_module( $module );
        if ( 'activate' == $type ) {
            $status = wpuf_pro_activate_module( $module );
            if ( is_wp_error( $status ) ) {
                wp_send_json_error(
                    [
                        'error'   => $status->get_error_code(),
                        'message' => $status->get_error_message(),
                    ]
                );
            }
            $message = __( 'Activated', 'wpuf-pro' );
        } else {
            wpuf_pro_deactivate_module( $module );
            $message = __( 'Deactivated', 'wpuf-pro' );
        }
        wp_send_json_success( $message );
    }

    /**
     * Toggle all modules
     *
     * @since 2.7
     *
     * @return void
     **/
    public function toggle_all_modules() {
        if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( $_POST['nonce'], 'wpuf-admin-nonce' ) ) {
            wp_send_json_error( __( 'Invalid nonce', 'wpuf-pro' ) );
        }
        $type = isset( $_POST['type'] ) ? $_POST['type'] : '';
        if ( ! in_array( $type, [ 'activate', 'deactivate' ] ) ) {
            wp_send_json_error( __( 'Invalid request type', 'wpuf-pro' ) );
        }
        $modules = wpuf_pro_get_modules();
        if ( 'activate' == $type ) {
            foreach ( $modules as $module => $data ) {
                wpuf_pro_activate_module( $module );
            }
            $message = __( 'Activated', 'wpuf-pro' );
        } else {
            foreach ( $modules as $module => $data ) {
                wpuf_pro_deactivate_module( $module );
            }
            $message = __( 'Deactivated', 'wpuf-pro' );
        }
        wp_send_json_success( $message );
    }

}
