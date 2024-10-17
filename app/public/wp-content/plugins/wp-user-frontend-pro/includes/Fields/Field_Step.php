<?php

namespace WeDevs\Wpuf\Pro\Fields;

use WeDevs\Wpuf\Fields\Form_Field_Text;
use WeDevs\Wpuf\Admin\Forms\Form;

/**
 * Step Field Class
 *
 * @since 3.1.0
 **/
class Field_Step extends Form_Field_Text {

    public function __construct() {
        $this->name       = __( 'Step Start', 'wpuf-pro' );
        $this->input_type = 'step_start';
        $this->icon       = 'step-forward';
    }

    /**
     * Render the Step field
     *
     * @param  array  $field_settings
     *
     * @param  integer  $form_id
     *
     * @param  string  $type
     *
     * @param  integer  $post_id
     *
     * @return void
     */
    public function render( $field_settings, $form_id, $type = 'post', $post_id = null ) {
        $form          = new Form( $form_id );
        $form_settings = $form->get_settings();

        static $step_started = false;
        if ( $step_started ) { ?>
            </fieldset>
        <?php } ?>

        <fieldset class="wpuf-multistep-fieldset">
            <legend>
                <?php echo $field_settings['label']; ?>
            </legend>
            <div class="multistep-button-area">
                <button class="wpuf-multistep-prev-btn btn btn-primary"><?php echo $field_settings['step_start']['prev_button_text']; ?></button>
                <?php
                if ( ! empty( $form_settings['draft_post'] ) && $form_settings['draft_post'] === 'true' ) {
                    ?>
                    <a href="#" class="btn wpuf-post-draft" id="wpuf-post-draft">Save Draft</a>
                    <?php
                }
                ?>
                <button class="wpuf-multistep-next-btn btn btn-primary"><?php echo $field_settings['step_start']['next_button_text']; ?></button>
            </div>
                <?php
                if ( ! $step_started ) {
                    $step_started = true;
                }
    }

    /**
     * It's a full width block
     *
     * @return boolean
     */
    public function is_full_width() {
        return true;
    }

    /**
     * Get field options setting
     *
     * @return array
     */
    public function get_options_settings() {
        return array(
            array(
                'name'          => 'step_start',
                'title'         => '',
                'type'          => 'step-start',
                'section'       => 'basic',
                'priority'      => 10,
                'help_text'     => '',
            ),
        );
    }

    /**
     * Get the field props
     *
     * @return array
     */
    public function get_field_props() {
        $props = array(
            'input_type' => 'step_start',
            'is_meta'    => 'no',
            'template'   => $this->get_type(),
            'label'      => $this->get_name(),
            'id'         => 0,
            'is_new'     => true,
            'step_start' => array(
                'prev_button_text' => __( 'Previous', 'wpuf-pro' ),
                'next_button_text' => __( 'Next', 'wpuf-pro' ),
            ),
        );

        return $props;
    }

    /**
     * Render field data
     *
     * @since 3.3.1
     *
     * @param mixed $data
     * @param array $field
     *
     * @return string
     */
    public function render_field_data( $data, $field ) {
        return '';
    }
}
