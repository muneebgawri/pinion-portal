<?php

namespace WeDevs\Wpuf\Pro;

use WeDevs\Wpuf\Data\Country_State;
use WeDevs\Wpuf\Traits\TaxableTrait;

/**
 * Tax Class
 *
 * @since 2.8.1
 *
 */
class Tax {

    use TaxableTrait;

    public function __construct() {
        add_action( 'init', [ $this, 'wpuf_save_tax_options' ] );

        if ( ! class_exists( 'WeDevs\Wpuf\Data\Country_State' ) ) {
            return;
        }
        add_filter( 'wpuf_settings_sections', [ $this, 'wpuf_tax_settings_tab' ] );
        add_filter( 'wpuf_settings_fields', [ $this, 'wpuf_tax_settings_content' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_filter( 'wpuf_payment_amount', [ $this, 'wpuf_amount_with_tax' ] );
        add_action( 'wpuf_calculate_tax', [ $this, 'wpuf_calculate_taxes' ] );
        add_action( 'wpuf_before_pack_payment_total', [ $this, 'wpuf_render_tax_field' ] );
    }

    /**
     *
     * Adds tax settings tab
     *
     */
    public function wpuf_tax_settings_tab( $settings ) {
        $tax_settings = [
            [
                'id'    => 'wpuf_payment_tax',
                'title' => __( 'Tax', 'wpuf-pro' ),
                'icon'  => 'dashicons-media-text',
            ],
        ];

        return array_merge( $settings, $tax_settings );
    }

    /**
     *
     * Adds tax settings tab contents
     *
     */
    public function wpuf_tax_settings_content( $settings_fields ) {
        $cs = new Country_State();
        $countries = $cs->countries();
        $tax_settings_fields = [
            'wpuf_payment_tax' => [
                [
                    'name'     => 'tax_help',
                    'label'    => __( 'Need help?', 'wpuf-pro' ),
                    'desc'     => sprintf(
                        __(
                            'Visit the <a href="%s" target="_blank">Tax setup documentation</a> for guidance on how to setup tax.',
                            'wpuf-pro'
                        ), 'https://wedevs.com/docs/wp-user-frontend-pro/settings/tax/'
                    ),
                    'callback' => 'wpuf_descriptive_text',
                ],
                [
                    'name'    => 'enable_tax',
                    'label'   => __( 'Enable Tax', 'wpuf-pro' ),
                    'desc'    => __( 'Enable tax on payments', 'wpuf-pro' ),
                    'type'    => 'checkbox',
                    'default' => 'on',
                ],
                [
                    'name'     => 'wpuf_base_country_state',
                    'label'    => '<strong>' . __( 'Base Country and State', 'wpuf-pro' ) . '</strong>',
                    'desc'     => __( 'Select your base country and state', 'wpuf-pro' ),
                    'callback' => [ $this, 'wpuf_base_country_state' ],
                ],
                [
                    'name'     => 'wpuf_tax_rates',
                    'label'    => '<strong>' . __( 'Tax Rates', 'wpuf-pro' ) . '</strong>',
                    'desc'     => __(
                        'Add tax rates for specific regions. Enter a percentage, such as 5 for 5%', 'wpuf-pro'
                    ),
                    'callback' => [ $this, 'wpuf_tax_rates' ],
                ],
                [
                    'name'    => 'fallback_tax_rate',
                    'label'   => '<strong>' . __( 'Fallback Tax Rate', 'wpuf-pro' ) . '</strong>',
                    'desc'    => __(
                        'Customers not in a specific rate will be charged this tax rate. Enter a percentage, such as 5 for 5%',
                        'wpuf-pro'
                    ),
                    'type'    => 'number',
                    'default' => 0,
                ],
                [
                    'name'    => 'prices_include_tax',
                    'label'   => __( 'Show prices with tax', 'wpuf-pro' ),
                    'desc'    => __( 'If frontend prices will include tax or not', 'wpuf-pro' ),
                    'type'    => 'radio',
                    'default' => 'yes',
                    'options' => [
                        'yes' => __( 'Show prices with tax', 'wpuf-pro' ),
                        'no'  => __( 'Show prices without tax', 'wpuf-pro' ),
                    ],
                ],
            ],
        ];

        return array_merge( $settings_fields, $tax_settings_fields );
    }

    /**
     *
     * Enqueue scripts
     *
     */
    public function enqueue_scripts() {
        wp_enqueue_script( 'wpuf-tax' );
        wp_enqueue_style( 'wpuf-tax' );
    }
}
