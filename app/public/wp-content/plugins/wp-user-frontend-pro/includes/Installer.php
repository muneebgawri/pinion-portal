<?php

namespace WeDevs\Wpuf\Pro;

use Plugin_Upgrader;
use WP_Ajax_Upgrader_Skin;

/**
 * The installer class
 *
 * @since 4.0.0
 */
class Installer {

    /**
     * Install the WP User Frontend plugin via ajax
     *
     * @since 2.4.2
     *
     * @return void
     */
    public function install_wpuf_free() {
        check_ajax_referer( 'wpuf-pro-installer-nonce' );

        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $plugin = 'wp-user-frontend';
        $api    = plugins_api(
            'plugin_information', [
                'slug'   => $plugin,
                'fields' => [ 'sections' => false ],
            ]
        );

        $upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
        $result   = $upgrader->install( $api->download_link );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result );
        }

        $result = activate_plugin( 'wp-user-frontend/wpuf.php' );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result );
        }

        // check whether the version of wpuf free is prior to the code restructure
        if ( defined( 'WPUF_VERSION' ) && version_compare( WPUF_VERSION, '4.0.0', '<' ) ) {
            add_action( 'admin_notices', [ $this, 'upgrade_notice' ] );

            deactivate_plugins( WPUF_FILE );
        }

        wp_send_json_success();
    }

    /**
     * Show WordPress error notice if WP User Frontend not found
     *
     * @since WPUF_SINCE
     */
    public function upgrade_notice() {
        ?>
        <div class="notice error" id="wpuf-pro-installer-notice" style="padding: 1em; position: relative;">
            <h2><?php esc_html_e( 'Your WP User Frontend Pro is almost ready!', 'wpuf-pro' ); ?></h2>
            <p>
                <?php
                /* translators: 1: opening anchor tag, 2: closing anchor tag. */
                printf( __( 'We\'ve pushed a major update on both <b>WP User Frontend Free</b> and <b>WP User Frontend Pro</b> that requires you to use latest version of both. Please update the WPUF pro to the latest version. <br><strong>Please make sure to take a complete backup of your site before updating.</strong>', 'wpuf-pro' ), '<a target="_blank" href="https://wordpress.org/plugins/wp-user-frontend/">', '</a>' );
                ?>
            </p>
        </div>
        <?php
    }
}
