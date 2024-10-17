<?php

namespace WeDevs\Wpuf\Pro\Admin\FormBuilder;

/**
 * Pro Render class
 */
class Render_Form {

    public function __construct() {
        // render_form
        add_action( 'wpuf_add_post_form_top', [ $this, 'wpuf_add_post_form_top_runner' ], 10, 2 );
        add_action( 'wpuf_edit_post_form_top', [ $this, 'wpuf_edit_post_form_top_runner' ], 10, 3 );
    }

    public function wpuf_form_buttons_custom_runner() {
        //add formbuilder widget pro buttons
        Form_Element::add_form_custom_buttons();
    }

    public function wpuf_form_buttons_other_runner() {
        Form_Element::add_form_other_buttons();
    }

    public function wpuf_edit_form_area_profile_runner() {
        Form_Element::render_registration_form();
    }

    public function registration_setting_runner() {
        Form_Element::render_registration_settings();
    }

    public function wpuf_check_post_type_runner( $post, $update ) {
        Form_Element::check_post_type( $post, $update );
    }

    public function wpuf_form_custom_taxonomies_runner() {
        Form_Element::render_custom_taxonomies_element();
    }

    public function wpuf_conditional_field_render_hook_runner( $field_id, $con_fields, $obj ) {
        Form_Element::render_conditional_field( $field_id, $con_fields, $obj );
    }

    //render_form
    public function wpuf_add_post_form_top_runner( $form_id, $form_settings ) {
        if ( ! isset( $form_settings['enable_multistep'] ) || $form_settings['enable_multistep'] != 'yes' ) {
            return;
        }
        if ( $form_settings['multistep_progressbar_type'] == 'progressive' ) {
            wp_enqueue_script( 'jquery-ui-progressbar' );
        }
    }

    public function wpuf_edit_post_form_top_runner( $form_id, $post_id, $form_settings ) {
        if ( ! isset( $form_settings['enable_multistep'] ) || $form_settings['enable_multistep'] != 'yes' ) {
            return;
        }
        if ( isset( $form_settings['multistep_progressbar_type'] ) && $form_settings['multistep_progressbar_type'] == 'progressive' ) {
            wp_enqueue_script( 'jquery-ui-progressbar' );
        }
    }

    /**
     *
     * Conditional logic on submit button
     *
     * @since v3.1.5
     *
     * @param $form_id , $form_settings
     *
     */
    public function conditional_logic_on_submit_button( $form_id, $form_settings ) {
        if ( ! isset( $form_settings['submit_button_cond']['condition_status'] ) || $form_settings['submit_button_cond']['condition_status'] != 'yes' ) {
            return;
        }
        $cond_inputs                     = $form_settings['submit_button_cond'];
        $cond_inputs['condition_status'] = isset( $cond_inputs['condition_status'] ) ? $cond_inputs['condition_status'] : '';
        if ( $cond_inputs['condition_status'] == 'yes' ) {
            $cond_field    = [];
            $cond_operator = [];
            $cond_option   = [];
            if ( isset( $cond_inputs['conditions'] ) && ! empty( $cond_inputs['conditions'] ) ) {
                foreach ( $cond_inputs['conditions'] as $condition ) {
                    if ( isset( $condition['name'] ) && ! empty( $condition['name'] ) ) {
                        array_push( $cond_field, $condition['name'] );
                        array_push( $cond_operator, $condition['operator'] );
                        array_push( $cond_option, $condition['option'] );
                    }
                }
                unset( $cond_inputs['conditions'] );
            }
            $cond_inputs['cond_field']    = $cond_field;
            $cond_inputs['cond_operator'] = $cond_operator;
            $cond_inputs['cond_option']   = $cond_option;
            $cond_inputs['type']          = 'submit';
            $cond_inputs['name']          = 'submit';
            $cond_inputs['form_id']       = $form_id;
            $condition                    = json_encode( $cond_inputs );
        } else {
            $condition = '';
        }
        ?>
        <script type="text/javascript">
            wpuf_conditional_items.push(<?php echo $condition; ?>);
        </script>
        <?php
    }

}
