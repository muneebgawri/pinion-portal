<?php

namespace WeDevs\Wpuf\Pro\Admin;

class Settings {
    public function __construct() {
        add_filter( 'wpuf_options_others', [ $this, 'general_settings' ] );
        add_filter( 'wpuf_options_wpuf_my_account', [ $this, 'my_account_settings' ] );
        add_action( 'wpuf_options_payment', [$this, 'payment_options'] );
    }

    /**
     * Adds paypal specific options to the admin panel
     *
     * @param type $options
     *
     * @return string
     */
    public function payment_options( $options ) {
        $pages = wpuf_get_pages();

        $options[] = [
            'name'    => 'gate_instruct_bank',
            'label'   => __( 'Bank Instruction', 'wpuf-pro' ),
            'type'    => 'wysiwyg',
            'default' => 'Make your payment directly into our bank account.',
        ];

        $options[] = [
            'name'    => 'bank_success',
            'label'   => __( 'Bank Payment Success Page', 'wpuf-pro' ),
            'desc'    => __( 'After payment users will be redirected here', 'wpuf-pro' ),
            'type'    => 'select',
            'options' => $pages,
        ];

        $options[] = [
            'name'  => 'paypal_email',
            'label' => __( 'PayPal Email', 'wpuf-pro' ),
        ];

        $options[] = [
            'name'    => 'gate_instruct_paypal',
            'label'   => __( 'PayPal Instruction', 'wpuf-pro' ),
            'type'    => 'wysiwyg',
            'default' => "Pay via PayPal; you can pay with your credit card if you don't have a PayPal account",
        ];

        $options[] = [
            'name'  => 'paypal_api_username',
            'label' => __( 'PayPal API username', 'wpuf-pro' ),
        ];
        $options[] = [
            'name'  => 'paypal_api_password',
            'label' => __( 'PayPal API password', 'wpuf-pro' ),
        ];
        $options[] = [
            'name'  => 'paypal_api_signature',
            'label' => __( 'PayPal API signature', 'wpuf-pro' ),
        ];

        return $options;
    }

    /**
     * Add general settings options
     *
     * @param $wpuf_general_fields
     *
     * @return array
     */
    public function general_settings( $wpuf_general_fields ) {
        $pro_settings_fields = [
            [
                'name'  => 'gmap_api_key',
                'label' => __( 'Google Map API', 'wpuf-pro' ),
                'desc'  => __( '<a target="_blank" href="https://developers.google.com/maps/documentation/javascript">API</a> key is needed to render Google Maps', 'wpuf-pro' ),
            ],
        ];

        $wpuf_general_settings = array_merge( $wpuf_general_fields, $pro_settings_fields );

        return $wpuf_general_settings;
    }

    /**
     * Add general settings options
     *
     * @return array $options
     */
    public function my_account_settings( $options ) {
        $options[] = array(
            'name'    => 'show_edit_profile_menu',
            'label'   => __( 'Edit Profile', 'wpuf-pro' ),
            'desc'    => __( 'Allow user to update their profile information from the account page', 'wpuf-pro' ),
            'type'    => 'checkbox',
            'default' => 'on'
        );

        $options[] = array(
            'name'    => 'edit_profile_form',
            'label'   => __( 'Profile Form', 'wpuf-pro' ),
            'desc'    => __( 'User will use this form to update their information from the account page,', 'wpuf-pro' ),
            'type'    => 'select',
            'options' => $this->get_profile_forms()
        );

        return $options;
    }

    /**
     * Get registration forms created by WPUF
     *
     * @return array $forms
     */
    public function get_profile_forms() {
        $args = [
            'post_type'   => 'wpuf_profile',
            'post_status' => 'any',
            'orderby'     => 'DESC',
            'order'       => 'ID',
        ];

        $query = new \WP_Query( $args );

        $forms = [
            '-1' => __( 'Default Form', 'wpuf-pro' ),
        ];

        if ( $query->have_posts() ) {
            $i = 0;

            while ( $query->have_posts() ) {
                $query->the_post();

                $form = $query->posts[ $i ];

                $settings = get_post_meta( get_the_ID(), 'wpuf_form_settings', true );

                $forms[ $form->ID ] = $form->post_title;

                $i++;
            }
        }

        return $forms;
    }
}
