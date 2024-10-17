<?php

namespace WeDevs\Wpuf\Pro;

use WeDevs\Wpuf\Admin\Forms\Form;
use WeDevs\Wpuf\Pro\Fields\Field_Address;
use WeDevs\Wpuf\Pro\Fields\Field_Avatar;
use WeDevs\Wpuf\Pro\Fields\Field_Country;
use WeDevs\Wpuf\Pro\Fields\Field_Date;
use WeDevs\Wpuf\Pro\Fields\Field_Display_Name;
use WeDevs\Wpuf\Pro\Fields\Field_Embed;
use WeDevs\Wpuf\Pro\Fields\Field_File;
use WeDevs\Wpuf\Pro\Fields\Field_First_Name;
use WeDevs\Wpuf\Pro\Fields\Field_GMap;
use WeDevs\Wpuf\Pro\Fields\Field_Hook;
use WeDevs\Wpuf\Pro\Fields\Field_Last_Name;
use WeDevs\Wpuf\Pro\Fields\Field_Math_Captcha;
use WeDevs\Wpuf\Pro\Fields\Field_Nickname;
use WeDevs\Wpuf\Pro\Fields\Field_Numeric;
use WeDevs\Wpuf\Pro\Fields\Field_Password;
use WeDevs\Wpuf\Pro\Fields\Field_Phone;
use WeDevs\Wpuf\Pro\Fields\Field_Rating;
use WeDevs\Wpuf\Pro\Fields\Field_Really_Simple_Captcha;
use WeDevs\Wpuf\Pro\Fields\Field_Repeat;
use WeDevs\Wpuf\Pro\Fields\Field_Shortcode;
use WeDevs\Wpuf\Pro\Fields\Field_Step;
use WeDevs\Wpuf\Pro\Fields\Field_Time;
use WeDevs\Wpuf\Pro\Fields\Field_Toc;
use WeDevs\Wpuf\Pro\Fields\Field_User_Bio;
use WeDevs\Wpuf\Pro\Fields\Field_User_Email;
use WeDevs\Wpuf\Pro\Fields\Field_User_Url;
use WeDevs\Wpuf\Pro\Fields\Field_Username;

/**
 *  Pro Fields Manager Class
 *
 * @since 3.1.0
 **/
class Fields_Manager {
    public function __construct() {
        add_filter( 'wpuf_form_fields', [ $this, 'register_fields' ] );
        add_filter( 'wpuf_form_fields_custom_fields', [ $this, 'add_to_custom_fields' ] );
        add_filter( 'wpuf_form_fields_others_fields', [ $this, 'add_to_others_fields' ] );
        add_filter( 'wpuf_field_get_js_settings', [ $this, 'add_conditional_field' ] );
        add_action( 'wpuf_form_fields_top', [ $this, 'step_start_form_top' ], 10, 2 );
    }

    /**
     * Register pro fields
     *
     * @param array $fields
     *
     * @return array
     */
    public function register_fields( $fields ) {
        if ( ! class_exists( 'WeDevs\Wpuf\Fields\Field_Contract' ) ) {
            return $fields;
        }

        $fields['user_login']            = new Field_Username();
        $fields['first_name']            = new Field_First_Name();
        $fields['last_name']             = new Field_Last_Name();
        $fields['display_name']          = new Field_Display_Name();
        $fields['nickname']              = new Field_Nickname();
        $fields['user_email']            = new Field_User_Email();
        $fields['user_url']              = new Field_User_Url();
        $fields['user_bio']              = new Field_User_Bio();
        $fields['password']              = new Field_Password();
        $fields['avatar']                = new Field_Avatar();
        $fields['repeat_field']          = new Field_Repeat();
        $fields['date_field']            = new Field_Date();
        $fields['file_upload']           = new Field_File();
        $fields['country_list_field']    = new Field_Country();
        $fields['numeric_text_field']    = new Field_Numeric();
        $fields['address_field']         = new Field_Address();
        $fields['google_map']            = new Field_GMap();
        $fields['shortcode']             = new Field_Shortcode();
        $fields['action_hook']           = new Field_Hook();
        $fields['toc']                   = new Field_Toc();
        $fields['ratings']               = new Field_Rating();
        $fields['step_start']            = new Field_Step();
        $fields['embed']                 = new Field_Embed();
        $fields['really_simple_captcha'] = new Field_really_simple_captcha();
        $fields['math_captcha']          = new Field_Math_Captcha();
        $fields['phone_field']           = new Field_Phone();
        $fields['time_field']            = new Field_Time();

        return $fields;
    }

    /**
     * Register fields to custom field section
     *
     * @param array $fields
     */
    public function add_to_custom_fields( $fields ) {
        $pro_fields = [
            'repeat_field',
            'date_field',
            'time_field',
            'file_upload',
            'country_list_field',
            'numeric_text_field',
            'phone_field',
            'address_field',
            'google_map',
            'step_start',
            'embed',
        ];

        return array_merge( $fields, $pro_fields );
    }

    /**
     * Register fields to others field section
     *
     * @param array $fields
     */
    public function add_to_others_fields( $fields ) {
        $pro_fields = [
            'shortcode',
            'action_hook',
            'toc',
            'ratings',
            'really_simple_captcha',
            'math_captcha',
        ];

        return array_merge( $fields, $pro_fields );
    }

    /**
     * Add conditional field settings
     *
     * @param array $settings
     */
    public function add_conditional_field( $settings ) {
        $settings['settings'][] = [
            'name'      => 'wpuf_cond',
            'title'     => __( 'Conditional Logic', 'wpuf-pro' ),
            'type'      => 'conditional-logic',
            'section'   => 'advanced',
            'priority'  => 30,
            'help_text' => '',
        ];

        return $settings;
    }

    /**
     * [step_start_form_top description]
     *
     * @param Form          $form
     * @param array         $form_fields
     *
     * @return void
     */
    public function step_start_form_top( $form, $form_fields ) {
        $settings     = $form->get_settings();
        $is_multistep = isset( $settings['enable_multistep'] ) && $settings['enable_multistep'];
        if ( ! $is_multistep ) {
            return;
        }
        if ( isset( $settings['multistep_progressbar_type'] ) && $settings['multistep_progressbar_type'] == 'progressive' ) {
            wp_enqueue_script( 'jquery-ui-progressbar' );
        }
        if ( isset( $settings['enable_multistep'] ) && $settings['enable_multistep'] == 'yes' ) {
            $ms_ac_txt_color   = isset( $settings['ms_ac_txt_color'] ) ? $settings['ms_ac_txt_color'] : '#ffffff';
            $ms_active_bgcolor = isset( $settings['ms_active_bgcolor'] ) ? $settings['ms_active_bgcolor'] : '#00a0d2';
            $ms_bgcolor        = isset( $settings['ms_bgcolor'] ) ? $settings['ms_bgcolor'] : '#E4E4E4';
            ?>
            <style type="text/css">
                .wpuf-form-add .wpuf-form .wpuf-multistep-progressbar ul.wpuf-step-wizard li,
                .wpuf-form-add .wpuf-form .wpuf-multistep-progressbar.ui-progressbar {
                    background-color: <?php echo $ms_bgcolor; ?>;
                    background: <?php echo $ms_bgcolor; ?>;
                }

                .wpuf-form-add .wpuf-form .wpuf-multistep-progressbar ul.wpuf-step-wizard li::after {
                    border-left-color: <?php echo $ms_bgcolor; ?>;
                }

                .wpuf-form-add .wpuf-form .wpuf-multistep-progressbar ul.wpuf-step-wizard li.active-step,
                .wpuf-form-add .wpuf-form .wpuf-multistep-progressbar .ui-widget-header {
                    color: <?php echo $ms_ac_txt_color; ?>;
                    background-color: <?php echo $ms_active_bgcolor; ?>;
                }

                .wpuf-form-add .wpuf-form .wpuf-multistep-progressbar ul.wpuf-step-wizard li.active-step::after {
                    border-left-color: <?php echo $ms_active_bgcolor; ?>;
                }

                .wpuf-form-add .wpuf-form .wpuf-multistep-progressbar.ui-progressbar .wpuf-progress-percentage {
                    color: <?php echo $ms_ac_txt_color; ?>;
                }
            </style>
            <input type="hidden" name="wpuf_multistep_type"
                   value="<?php echo $settings['multistep_progressbar_type'] ?>"/>

            <?php
            if ( $settings['multistep_progressbar_type'] == 'step_by_step' ) {
                ?>
                <!-- wpuf-multistep-progressbar -->
                <div class="wpuf-multistep-progressbar"></div>
                <?php
            } else {
                ?>
                <div class="wpuf-multistep-progressbar"></div>
                <?php
            }
        }
    }
}
