<?php

namespace WeDevs\Wpuf\Pro\Admin\Forms;

use WeDevs\Wpuf\Admin\Forms\Form_Template;
use WeDevs\Wpuf\Frontend_Render_Form;
use WeDevs\Wpuf\Pro\Admin\FormBuilder\Render_Form;
use WeDevs\Wpuf\Pro\Admin\Forms\ProfileTemplates\Dokan_Vendor_Reg_Template;
use WeDevs\Wpuf\Pro\Admin\Forms\ProfileTemplates\WC_Marketplace_Reg_Template;
use WeDevs\Wpuf\Pro\Admin\Forms\ProfileTemplates\WC_Vendor_Reg_Template;

/**
 * Admin profile form template handler
 *
 * Create profile forms based on form templates
 *
 * @since 2.7
 */
class Profile_Form_Template {

    public function __construct() {
        // profile form templates
        add_action( 'admin_footer', [ $this, 'render_profile_form_templates' ] );
        // dynamic hook. format: "admin_action_{$action}". more details: wp-admin/admin.php
        add_action( 'admin_action_wpuf_profile_form_template', [ $this, 'create_profile_form_from_template' ] );
        // form settings
        add_action( 'wpuf_profile_setting', [ $this, 'profile_form_settings' ], 8, 2 );
        // process registration errors
        add_filter( 'registration_errors', [ $this, 'register_errors' ], 10, 3 );
        add_filter( 'wpuf_duplicate_username_error', [ $this, 'duplicate_username_error' ], 10, 2 );
        // frontend insert/update
        add_action( 'wpuf_update_profile', [ $this, 'profile_form_submission' ], 10, 3 );
        add_action( 'wpuf_after_register', [ $this, 'profile_form_submission' ], 10, 3 );
    }

    /**
     * Should a form displayed or script enqueued?
     *
     * @return boolean
     */
    public function should_display() {
        $current_screen = get_current_screen();
        $submenu_hooks  = wpuf_pro()->admin->pro_menu->get_all_submenu_hooks();

        if ( ! empty( $submenu_hooks['profile_forms'] ) && $current_screen->id === $submenu_hooks['profile_forms'] ) {
            return true;
        }

        return false;
    }

    /**
     * Get profile form templates
     *
     * @return array
     */
    public function wpuf_get_profile_form_templates() {
        $integrations                                = [];
        $integrations['dokan_vendor_reg_template']   = new Dokan_Vendor_Reg_Template();
        $integrations['wc_vendor_reg_template']      = new WC_Vendor_Reg_Template();
        $integrations['wc_marketplace_reg_template'] = new WC_Marketplace_Reg_Template();

        return apply_filters( 'wpuf_get_profile_form_templates', $integrations );
    }

    /**
     * Render the forms in the modal
     *
     * @return void
     */
    public function render_profile_form_templates() {
        if ( ! $this->should_display() ) {
            return;
        }

        $registry       = $this->wpuf_get_profile_form_templates();
        $pro_templates  = wpuf_get_pro_form_previews();
        $blank_form_url = admin_url( 'admin.php?page=wpuf-profile-forms&action=add-new' );
        $action_name    = 'wpuf_profile_form_template';
        $footer_help    = sprintf(
            __( 'Want a new integration? <a href="%s" target="_blank">Let us know</a>.', 'wpuf-pro' ),
            'mailto:support@wedevs.com?subject=WPUF Custom Profile Template Integration Request'
        );

        if ( ! $registry ) {
            return;
        }

        include WPUF_ROOT . '/includes/Admin/template-parts/modal.php';
    }

    /**
     * Get a template object by name from the registry
     *
     * @param string $template
     *
     * @return boolean|Form_Template
     */
    public function get_template_object( $template ) {
        $registry = $this->wpuf_get_profile_form_templates();
        if ( ! array_key_exists( $template, $registry ) ) {
            return false;
        }

        $template_object = $registry[ $template ];

        if ( ! is_a( $template_object, 'WeDevs\Wpuf\Admin\Forms\Form_Template' ) ) {
            return false;
        }

        return $template_object;
    }

    /**
     * Create a profile form from a profile template
     *
     * @since 2.7
     *
     * @return void
     */
    public function create_profile_form_from_template() {
        check_admin_referer( 'wpuf_create_from_template' );
        $template_name = isset( $_GET['template'] ) ? sanitize_text_field( $_GET['template'] ) : '';
        if ( ! $template_name ) {
            return;
        }
        $template_object = $this->get_template_object( $template_name );
        if ( false === $template_object ) {
            return;
        }
        $current_user = get_current_user_id();
        $form_post_data = [
            'post_title'  => $template_object->get_title(),
            'post_type'   => 'wpuf_profile',
            'post_status' => 'publish',
            'post_author' => $current_user,
        ];
        $form_id = wp_insert_post( $form_post_data );
        if ( is_wp_error( $form_id ) ) {
            return;
        }
        // form has been created, lets setup
        update_post_meta( $form_id, 'wpuf_form_settings', $template_object->get_form_settings() );
        $form_fields = $template_object->get_form_fields();
        if ( ! $form_fields ) {
            return;
        }
        foreach ( $form_fields as $menu_order => $field ) {
            wp_insert_post(
                [
                    'post_type'    => 'wpuf_input',
                    'post_status'  => 'publish',
                    'post_content' => maybe_serialize( $field ),
                    'post_parent'  => $form_id,
                    'menu_order'   => $menu_order,
                ]
            );
        }
        wp_redirect( admin_url( 'admin.php?page=wpuf-profile-forms&action=edit&id=' . $form_id ) );
        exit;
    }

    /**
     * Add settings field to override a form template
     *
     * @param array  $form_settings
     * @param object $post
     *
     * @return void
     */
    public function profile_form_settings( $form_settings, $post ) {
        $registry = $this->wpuf_get_profile_form_templates();
        $selected = isset( $form_settings['form_template'] ) ? $form_settings['form_template'] : '';
        ?>
        <tr>
            <th><?php _e( 'Form Template', 'wpuf-pro' ); ?></th>
            <td>
                <select name="wpuf_settings[form_template]">
                    <option value=""><?php echo __( '&mdash; No Template &mdash;', 'wpuf-pro' ); ?></option>
                    <?php
                    if ( $registry ) {
                        foreach ( $registry as $key => $template ) {
                            printf(
                                '<option value="%s"%s>%s</option>' . "\n", $key, selected( $selected, $key, false ),
                                $template->get_title()
                            );
                        }
                    }
                    ?>
                </select>
                <p class="description"><?php _e(
                        'If selected a form template, it will try to execute that integration options when new post created and updated.',
                        'wpuf-pro'
                    ); ?></p>
            </td>
        </tr>
        <?php
    }

    /**
     * Call the integration functions on form submission/update
     *
     * @param int   $post_id
     * @param int   $form_id
     * @param array $form_settings
     *
     * @return void
     */
    public function profile_form_submission( $user_id, $form_id, $form_settings ) {
        $template = isset( $form_settings['form_template'] ) ? $form_settings['form_template'] : '';
        if ( ! $template ) {
            return;
        }
        $template_object = $this->get_template_object( $template );
        if ( false === $template_object ) {
            return;
        }
        $current_action = current_action();
        if ( $current_action == 'wpuf_after_register' ) {
            $template_object->after_insert( $user_id, $form_id, $form_settings );
        } else if ( $current_action == 'wpuf_update_profile' ) {
            $template_object->after_update( $user_id, $form_id, $form_settings );
        }
    }

    /**
     * Process registration errors
     *
     * @param str $errors
     *
     * @return str $errors
     */
    public function register_errors( $errors, $username, $user_email ) {
        // WC Vendor registration form: check the unique value of shop name
        $pv_shop_name = isset( $_POST['pv_shop_name'] ) ? sanitize_title( $_POST['pv_shop_name'] ) : '';
        if ( ! empty( $pv_shop_name ) ) {
            $users = get_users( [ 'meta_key' => 'pv_shop_slug', 'meta_value' => $pv_shop_name ] );
            if ( ! empty( $users ) && $users[0]->ID != $user_id ) {
                ( new \WeDevs\Wpuf\Frontend_Render_Form )->send_error(
                    __( 'That shop name is already taken. Your shop name must be unique.', 'wpuf-pro' )
                );
            }
        }

        return $errors;
    }

    /**
     * Check unique username
     *
     * @param str   $username_error
     * @param array $form_settings
     *
     * @return str $username_error
     */
    public function duplicate_username_error( $username_error, $form_settings ) {
        $template = isset( $form_settings['form_template'] ) ? $form_settings['form_template'] : '';
        if ( $template == 'wc_marketplace_reg_template' ) {
            $username_error = __( 'That store name is already taken. Your store name must be unique.', 'wpuf-pro' );
        }

        return $username_error;
    }

}
