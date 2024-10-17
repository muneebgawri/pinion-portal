<?php

namespace WeDevs\Wpuf\Pro\Admin;

class Menu extends \WeDevs\Wpuf\Admin\Menu {
    public function __construct() {
        add_action( 'wpuf_admin_menu_top', [ $this, 'admin_menu_top' ] );
        add_action( 'wpuf_admin_menu_bottom', [ $this, 'admin_menu_bottom' ] );
        add_action( 'wpuf_admin_menu', [ $this, 'add_coupon_menu' ] );
    }

    /**
     * Callback method for WP User Frontend submenu
     *
     * @since 2.5
     *
     * @return void
     */
    public function add_coupon_menu() {
        if ( 'on' === wpuf_get_option( 'enable_payment', 'wpuf_payment', 'on' ) ) {
            $capability = wpuf_admin_role();
            add_submenu_page( wpuf()->admin->menu->parent_slug, __( 'Coupons', 'wpuf-pro' ), __( 'Coupons', 'wpuf-pro' ), $capability, 'edit.php?post_type=wpuf_coupon' );
        }
    }

    /**
     * Callback method for WP User Frontend submenu
     *
     * @since 2.6
     *
     * @return void
     */
    public function admin_menu_bottom() {
        $capability = wpuf_admin_role();

        $modules = add_submenu_page( $this->parent_slug, __( 'Modules', 'wpuf-pro' ), __( 'Modules', 'wpuf-pro' ), $capability, 'wpuf-modules', [ $this, 'modules_page' ] );
        add_action( 'load-' . $modules, [ $this, 'modules_scripts' ] );
    }

    /**
     * Modules Page
     *
     * @since 2.7
     *
     * @return void
     **/
    public function modules_page() {
        include WPUF_PRO_INCLUDES . '/Admin/views/modules.php';
    }

    /**
     * Modules Scripts
     *
     * @since 2.7
     *
     * @return void
     **/
    public function modules_scripts() {
        wp_enqueue_style( 'wpuf-module' );
        wp_enqueue_script( 'wpuf-jquery-blockui' );
        wp_enqueue_script( 'wpuf-module' );

        $wpuf_module = apply_filters(
            'wpuf_module_localize_param', [
                'ajaxurl'      => admin_url( 'admin-ajax.php' ),
                'nonce'        => wp_create_nonce( 'wpuf-admin-nonce' ),
                'activating'   => __( 'Activating', 'wpuf-pro' ),
                'deactivating' => __( 'Deactivating', 'wpuf-pro' ),
            ]
        );

        wp_localize_script( 'wpuf-module', 'wpuf_module', $wpuf_module );
    }

    /**
     * Callback method for WP User Frontend submenu
     *
     * @since 2.5
     *
     * @return void
     */
    public function admin_menu_top() {
        $capability = wpuf_admin_role();
        $profile_forms_page = add_submenu_page(
            $this->parent_slug,
            __( 'Registration Forms', 'wpuf-pro' ),
            __( 'Registration Forms', 'wpuf-pro' ),
            $capability, 'wpuf-profile-forms', [ $this, 'wpuf_profile_forms_page' ]
        );
        $this->all_submenu_hooks['profile_forms'] = $profile_forms_page;

        add_action( 'load-' . $profile_forms_page, [ $this, 'profile_form_menu_action' ] );
    }

    /**
     * Callback method for Profile Forms submenu
     *
     * @since 2.5
     *
     * @return void
     */
    public function wpuf_profile_forms_page() {
        // phpcs:ignore WordPress.Security.NonceVerification
        $action           = ! empty( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
        $add_new_page_url = admin_url( 'admin.php?page=wpuf-profile-forms&action=add-new' );

        switch ( $action ) {
            case 'edit':
            case 'add-new':
                require_once WPUF_PRO_INCLUDES . '/Admin/views/profile-form.php';
                break;

            default:
                require_once WPUF_PRO_INCLUDES . '/Admin/views/profile-forms-list-table-view.php';
                break;
        }
    }

    /**
     * The action to run just after the menu is created
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function profile_form_menu_action() {
        wp_enqueue_style( 'wpuf-admin' );
        wp_enqueue_style( 'wpuf-registration-forms' );
        wp_enqueue_script( 'wpuf-registration-forms' );
        /**
         * Backdoor for calling the menu hook.
         * This hook won't get translated even the site language is changed
         */
        do_action( 'wpuf_load_profile_forms' );
    }

}
