<?php

namespace WeDevs\Wpuf\Pro;

use WeDevs\Wpuf\Pro\Admin\FormBuilder;
use WeDevs\Wpuf\Pro\Admin\FormBuilder\Form_Element;
use WeDevs\WpUtils\ContainerTrait;

/**
 * Pro features for wpuf_forms builder
 *
 * @since 2.5
 */
class Post_Form {

    use ContainerTrait;

    /**
     * Class constructor
     *
     * @since 2.5
     *
     * @return void
     */
    public function __construct() {
        $this->render_form_element = new FormBuilder\Render_Form_Element();
        $this->event               = new FormBuilder\Events_Plugins_Integration();

        $this->gmap_api_key = wpuf_get_option( 'gmap_api_key', 'wpuf_general' );

        $this->init_post_form_items();

        add_action( 'wpuf_load_post_forms', [ $this, 'enqueue_scripts' ] );
        // add_action( 'wpuf-form-builder-enqueue-after-components', array( $this, 'admin_enqueue_scripts_components' ) );
    }

    public function init_post_form_items() {
        // $this->enqueue_scripts();
        add_filter( 'wpuf_form_builder_localize_script', [ $this, 'localize_script' ] );
        add_filter( 'wpuf_form_builder_wpuf_forms_js_deps', [ $this, 'wpuf_forms_pro_scripts' ] );
        add_filter( 'wpuf_form_builder_js_builder_stage_mixins', [ $this, 'add_builder_stage_mixins' ] );
        add_filter( 'wpuf_form_builder_js_form_fields_mixins', [ $this, 'add_form_field_mixins' ] );
        add_filter( 'wpuf_form_builder_i18n', [ $this, 'i18n' ] );
        add_filter( 'wpuf-get-form-fields', [ $this, 'get_form_fields' ] );

        add_action( 'wpuf_form_builder_enqueue_after_mixins', [ $this, 'admin_enqueue_mixins' ] );
        add_action( 'wpuf_form_builder_enqueue_after_components', [ $this, 'admin_enqueue_components' ] );
        add_action( 'wpuf_form_builder_add_js_templates', [ $this, 'add_js_templates' ] );
        // render element form in backend form builder
        add_action( 'wpuf_admin_field_custom_repeater', [ $this, 'custom_repeater' ], 10, 4 );
        add_action( 'wpuf_admin_template_post_repeat_field', [ $this, 'repeat_field' ], 10, 5 );
        add_action( 'wpuf_admin_field_custom_date', [ $this, 'custom_date' ], 10, 4 );
        add_action( 'wpuf_admin_template_post_date_field', [ $this, 'date_field' ], 10, 5 );
        add_action( 'wpuf_admin_field_custom_file', [ $this, 'custom_file' ], 10, 4 );
        add_action( 'wpuf_admin_template_post_file_upload', [ $this, 'file_upload' ], 10, 5 );
        add_action( 'wpuf_admin_field_custom_map', [ $this, 'custom_map' ], 10, 4 );
        add_action( 'wpuf_admin_template_post_google_map', [ $this, 'google_map' ], 10, 5 );
        add_action( 'wpuf_admin_field_country_select', [ $this, 'country_select' ], 10, 4 );
        add_action( 'wpuf_admin_template_post_country_list_field', [ $this, 'country_list' ], 10, 5 );
        add_action( 'wpuf_admin_field_numeric_field', [ $this, 'wpuf_admin_field_numeric_field_runner' ], 10, 4 );
        add_action( 'wpuf_admin_template_post_numeric_text_field', [ $this, 'wpuf_admin_template_post_numeric_text_field_runner' ], 10, 5 );
        add_action( 'wpuf_admin_field_address_field', [ $this, 'wpuf_admin_field_address_field_runner' ], 10, 4 );
        add_action( 'wpuf_admin_template_post_address_field', [ $this, 'wpuf_admin_template_post_address_field_runner' ], 10, 5
        );
        add_action( 'wpuf_admin_field_step_start', [ $this, 'wpuf_admin_field_step_start_runner' ], 10, 4 );
        add_action( 'wpuf_admin_template_post_step_start', [ $this, 'wpuf_admin_template_post_step_start_runner' ], 10, 5 );
        add_action( 'wpuf_admin_field_really_simple_captcha', [ $this, 'wpuf_admin_field_really_simple_captcha_runner' ], 10, 4 );
        add_action( 'wpuf_admin_template_post_really_simple_captcha', [ $this, 'wpuf_admin_template_post_really_simple_captcha_runner' ], 10, 5 );
        add_action( 'wpuf_admin_field_action_hook', [ $this, 'wpuf_admin_field_action_hook_runner' ], 10, 4 );
        add_action( 'wpuf_admin_template_post_action_hook', [ $this, 'wpuf_admin_template_post_action_hook_runner' ], 10, 5 );
        add_action( 'wpuf_admin_field_toc', [ $this, 'wpuf_admin_field_toc_runner' ], 10, 4 );
        add_action( 'wpuf_admin_field_ratings', [ $this, 'wpuf_admin_field_ratings_runner' ], 10, 4 );
        add_action( 'wpuf_admin_template_post_toc', [ $this, 'wpuf_admin_template_post_toc_runner' ], 10, 5 );
        add_action( 'wpuf_admin_template_post_ratings', [ $this, 'wpuf_admin_template_post_ratings' ], 10, 5 );
        // others
        add_action( 'wpuf_form_buttons_custom', [ $this, 'wpuf_form_buttons_custom_runner' ] );
        add_action( 'wpuf_form_buttons_other', [ $this, 'wpuf_form_buttons_other_runner' ] );
        add_action( 'wpuf_form_post_expiration', [ $this, 'wpuf_form_post_expiration_runner' ] );
        add_action( 'wpuf_form_setting', [ $this, 'form_setting_runner' ], 10, 2 );
        add_action( 'wpuf_form_settings_post_notification', [ $this, 'post_notification_hook_runner' ] );
        add_action( 'wpuf_edit_form_area_profile', [ $this, 'wpuf_edit_form_area_profile_runner' ] );
        add_action( 'wpuf_add_profile_form_top', [ $this, 'wpuf_add_profile_form_top_runner' ], 10, 2 );
        add_action( 'registration_setting', [ $this, 'registration_setting_runner' ] );
        add_action( 'wpuf_check_post_type', [ $this, 'wpuf_check_post_type_runner' ], 10, 2 );
        add_action( 'wpuf_form_custom_taxonomies', [ $this, 'wpuf_form_custom_taxonomies_runner' ] );
        add_action( 'wpuf_conditional_field_render_hook', [ $this, 'wpuf_conditional_field_render_hook_runner' ], 10, 3 );
        add_action( 'wpuf_submit_btn', [ $this->render_form_element, 'conditional_logic_on_submit_button' ], 10, 2 );
        add_filter( 'wpuf_get_post_types', [ $this, 'add_custom_post_types' ] );
    }

    public function custom_map( $type, $field_id, $classname, $obj ) {
        Form_Element::google_map( $field_id, 'Custom Field: Google Map', $classname );
    }

    public function google_map( $name, $count, $input_field, $classname, $obj ) {
        Form_Element::google_map( $count, $name, $classname, $input_field );
    }

    public function country_select( $type, $field_id, $classname, $obj ) {
        Form_Element::country_list_field( $field_id, 'Custom field: Select', $classname );
    }

    public function country_list( $name, $count, $input_field, $classname, $obj ) {
        Form_Element::country_list_field( $count, $name, $classname, $input_field );
    }

    public function wpuf_admin_field_numeric_field_runner( $type, $field_id, $classname, $obj ) {
        Form_Element::numeric_text_field( $field_id, 'Custom field: Numeric Text', $classname );
    }

    public function wpuf_admin_template_post_numeric_text_field_runner( $name, $count, $input_field, $classname, $obj ) {
        Form_Element::numeric_text_field( $count, $name, $classname, $input_field );
    }

    public function wpuf_admin_field_address_field_runner( $type, $field_id, $classname, $obj ) {
        Form_Element::address_field( $field_id, 'Custom field: Address', $classname );
    }

    public function wpuf_admin_template_post_address_field_runner( $name, $count, $input_field, $classname, $obj ) {
        Form_Element::address_field( $count, $name, $classname, $input_field );
    }

    public function wpuf_admin_field_step_start_runner( $type, $field_id, $classname, $obj ) {
        Form_Element::step_start( $field_id, 'Step Starts', $classname );
    }

    public function wpuf_admin_template_post_step_start_runner( $name, $count, $input_field, $classname, $obj ) {
        Form_Element::step_start( $count, $name, $classname, $input_field );
    }

    public function wpuf_admin_field_really_simple_captcha_runner( $type, $field_id, $classname, $obj ) {
        Form_Element::really_simple_captcha( $field_id, 'Really Simple Captcha', $classname );
    }

    public function wpuf_admin_template_post_really_simple_captcha_runner( $name, $count, $input_field, $classname, $obj ) {
        Form_Element::really_simple_captcha( $count, $name, $classname, $input_field );
    }

    public function wpuf_admin_field_action_hook_runner( $type, $field_id, $classname, $obj ) {
        Form_Element::action_hook( $field_id, 'Action Hook', $classname );
    }

    public function wpuf_admin_template_post_action_hook_runner( $name, $count, $input_field, $classname, $obj ) {
        Form_Element::action_hook( $count, $name, $classname, $input_field );
    }

    public function wpuf_admin_field_toc_runner( $type, $field_id, $classname, $obj ) {
        Form_Element::toc( $field_id, 'TOC', $classname );
    }

    public function wpuf_admin_field_ratings_runner( $type, $field_id, $classname, $obj ) {
        Form_Element::ratings( $field_id, 'Ratings', $classname );
    }

    public function wpuf_admin_template_post_toc_runner( $name, $count, $input_field, $classname, $obj ) {
        Form_Element::toc( $count, $name, $classname, $input_field );
    }

    public function wpuf_admin_template_post_ratings( $name, $count, $input_field, $classname, $obj ) {
        Form_Element::ratings( $count, $name, $classname, $input_field );
    }

    public function wpuf_add_profile_form_top_runner( $form_id, $form_settings ) {
        if ( isset( $form_settings['multistep_progressbar_type'] ) && $form_settings['multistep_progressbar_type'] == 'progressive' ) {
            wp_enqueue_script( 'jquery-ui-progressbar' );
        }
    }

    public function custom_date( $type, $field_id, $classname, $obj ) {
        Form_Element::date_field( $field_id, 'Custom Field: Date', $classname );
    }

    public function file_upload( $name, $count, $input_field, $classname, $obj ) {
        Form_Element::file_upload( $count, $name, $classname, $input_field );
    }

    public function custom_file( $type, $field_id, $classname, $obj ) {
        Form_Element::file_upload( $field_id, 'Custom field: File Upload', $classname );
    }

    public function repeat_field( $name, $count, $input_field, $classname, $obj ) {
        Form_Element::repeat_field( $count, $name, $classname, $input_field );
    }

    //form element's rendering form in backend form builder
    public function custom_repeater( $type, $field_id, $classname, $obj ) {
        Form_Element::repeat_field( $field_id, 'Custom field: Repeat Field', $classname );
    }

    public function date_field( $name, $count, $input_field, $classname, $obj ) {
        Form_Element::date_field( $count, $name, $classname, $input_field );
    }

    /**
     * Enqueue pro scripts in post forms editor page
     *
     * @since 2.5.3
     *
     * @param array $deps
     *
     * @return array
     */
    public function wpuf_forms_pro_scripts( $deps ) {
        $deps[] = 'wpuf-form-builder-wpuf-forms-pro';

        return $deps;
    }

    /**
     * Enqueue Vue components
     *
     * @since 2.5
     *
     * @return void
     */
    public function admin_enqueue_components() {
        wp_enqueue_script( 'wpuf-form-builder-components-pro' );
        wp_enqueue_script( 'wpuf-google-maps' );
    }

    /**
     * i18n translatable strings
     *
     * @since 2.5
     *
     * @param array $i18n
     *
     * @return array
     */
    public function i18n( $i18n ) {
        return array_merge( $i18n, array(
            'street_address'    => __( 'Address Line 1', 'wpuf-pro' ),
            'street_address2'   => __( 'Address Line 2', 'wpuf-pro' ),
            'city_name'         => __( 'City', 'wpuf-pro' ),
            'state'             => __( 'State', 'wpuf-pro' ),
            'zip'               => __( 'Zip Code', 'wpuf-pro' ),
            'country_select'    => __( 'Country', 'wpuf-pro' ),
            'show_all'          => __( 'Show all', 'wpuf-pro' ),
            'hide_these'        => __( 'Hide these', 'wpuf-pro' ),
            'only_show_these'   => __( 'Only show these', 'wpuf-pro' ),
            'select_countries'  => __( 'Select Countries', 'wpuf-pro' ),
        ) );
    }

    /**
     * Add mixin_form_field_pro mixin
     *
     * @since 2.5
     *
     * @param array $mixins
     *
     * @return array
     */
    public function add_builder_stage_mixins( $mixins ) {
        return array_merge( $mixins, array( 'mixin_form_field_pro', 'mixin_builder_stage_pro' ) );
    }

    public function form_setting_runner( $form_settings, $post ) {
        Form_Element::add_form_settings_content( $form_settings, $post );
    }

    public function wpuf_form_post_expiration_runner() {
        Form_Element::render_form_expiration_tab();
    }

    public function post_notification_hook_runner() {
        Form_Element::add_post_notification_content();
    }


    /**
     * Add Vue templates
     *
     * @since 2.5
     *
     * @return void
     */
    public function add_js_templates() {
        wpuf_include_once( WPUF_PRO_ROOT . '/assets/js-templates/form-components.php' );
    }

    /**
     * Add mixins to form_fields
     *
     * @since 2.5
     *
     * @param array $mixins
     *
     * @return array
     */
    public function add_form_field_mixins( $mixins ) {
        return array_merge( $mixins, array( 'mixin_form_field_pro' ) );
    }

    /**
     * Filter form fields
     *
     * @since 2.5
     *
     * @param array $field
     *
     * @return array
     */
    public function get_form_fields( $field ) {
        // make sure that country_list has all its properties
        if ( 'country_list' === $field['input_type'] ) {
            if ( ! isset( $field['country_list']['country_select_hide_list'] ) ) {
                $field['country_list']['country_select_hide_list'] = [];
            }
            if ( ! isset( $field['country_list']['country_select_show_list'] ) ) {
                $field['country_list']['country_select_show_list'] = [];
            }
        }
        if ( 'address' === $field['input_type'] ) {
            if ( ! isset( $field['address']['country_select']['country_select_hide_list'] ) ) {
                $field['address']['country_select']['country_select_hide_list'] = [];
            }
            if ( ! isset( $field['address']['country_select']['country_select_show_list'] ) ) {
                $field['address']['country_select']['country_select_show_list'] = [];
            }
        }
        if ( 'google_map' === $field['input_type'] && ! isset( $field['directions'] ) ) {
            $field['show_checkbox'] = false;
        }
        if ( 'toc' === $field['input_type'] && ! isset( $field['show_checkbox'] ) ) {
            $field['show_checkbox'] = false;
        }
        if ( 'ratings' === $field['input_type'] && ! isset( $field['selected'] ) ) {
            $field['selected'] = [];
        }

        return $field;
    }

    /**
     * Add data to localize script data array
     *
     * @since 2.5
     *
     * @param array $data
     *
     * @return array
     */
    public function localize_script( $data ) {
        if ( ! array_key_exists( 'form_settings', $data ) && ! array_key_exists( 'submit_button_cond', $data['form_settings'] ) ) {
            $data['form_settings']['submit_button_cond'] = array(
                'condition_status' => 'no',
                'cond_logic'       => 'any',
                'conditions'       => array(
                    array(
                        'name'             => '',
                        'operator'         => '=',
                        'option'           => '',
                    ),
                ),
            );
        }

        return array_merge(
            $data, [
                'gmap_api_key'               => $this->gmap_api_key,
                'is_rs_captcha_active'       => class_exists( 'ReallySimpleCaptcha' ) ? true : false,
                'countries'                  => wpuf_get_countries(),
                'wpuf_cond_supported_fields' => [
                    'radio_field',
                    'checkbox_field',
                    'dropdown_field',
                    'text_field',
                    'numeric_text_field',
                    'textarea_field',
                    'email_address',
                    'website_url',
                    'password',
                    'google_map',
                    'user_email',
                ],
            ]
        );
    }

    /**
     * Enqueue form builder related CSS and JS
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function enqueue_scripts() {
        wp_enqueue_style( 'wpuf-css-stars' );
        wp_enqueue_style( 'wpuf-math-captcha' );
        wp_enqueue_style( 'wpuf-tax' );
        // Load International Telephone Input CSS - https://github.com/jackocnr/intl-tel-input.
        wp_enqueue_style( 'wpuf-intlTelInput' );
        wp_enqueue_style( 'wpuf-form-builder-pro' );
        wp_enqueue_style( 'wpuf-css-stars' );

        wp_enqueue_script( 'wpuf-barrating' );
        wp_enqueue_script( 'wpuf-conditional-logic' );
        // Load International Telephone Input JS - https://github.com/jackocnr/intl-tel-input.s
        wp_enqueue_script( 'wpuf-intlTelInput' );
        wp_enqueue_script( 'wpuf-tax' );
        // wp_enqueue_script( 'wpuf-form-builder-wpuf-forms-pro' );
    }

    /**
     * Enqueue Vue mixins
     *
     * @since 2.5
     *
     * @return void
     */
    public function admin_enqueue_mixins() {
        wp_enqueue_script( 'wpuf-form-builder-mixins-pro' );
    }

    /**
     * Filter to add custom post types to wpuf_get_post_types
     *
     * @since 2.5
     *
     * @param array $post_types
     *
     * @return array
     */
    public function add_custom_post_types( $post_types ) {
        $args = array( '_builtin' => false );

        $custom_post_types = get_post_types( $args );

        $ignore_post_types = array(
            'wpuf_forms', 'wpuf_profile', 'wpuf_input'
        );

        foreach ( $custom_post_types as $key => $val ) {
            if ( in_array( $val, $ignore_post_types ) ) {
                unset( $custom_post_types[$key] );
            }
        }

        return array_merge( $post_types, $custom_post_types );
    }
}
