<?php

namespace WeDevs\Wpuf\Pro\Fields;

use WeDevs\Wpuf\Fields\Field_Contract;
/**
 * Password Field Class
 *
 * @since 3.1.0
 **/
class Field_Password extends Field_Contract {

	public function __construct() {
        $this->name       = __( 'Password', 'wpuf-pro' );
        $this->input_type = 'password';
        $this->icon       = 'lock';
    }

    /**
     * Render the Password field
     *
     * @param  array  $field_settings
     * @param  integer  $form_id
     * @param  string  $type
     * @param  integer  $post_id
     *
     * @return void
     */
    public function render( $field_settings, $form_id, $type = 'post', $post_id = null ) {
        $value             = $field_settings['default'];
        $repeat_pass       = ! empty( $field_settings['repeat_pass'] ) ? $field_settings['repeat_pass'] : 'no';
        $pass_strength     = $field_settings['pass_strength'] === 'yes';
        $minimum_strength  = ! empty( $field_settings['minimum_strength'] ) ? $field_settings['minimum_strength'] : '';
        $minimum_length    = ! empty( $field_settings['min_length'] ) ? $field_settings['min_length'] : 0;

        if ( $pass_strength || ! empty( $minimum_strength ) ) {
            wp_enqueue_script( 'zxcvbn' );
            wp_enqueue_script( 'password-strength-meter' );
        }

        $field_settings['required'] = is_user_logged_in() ? '' : $field_settings['required'];
        $eye_icon_src               = file_exists( WPUF_ROOT . '/assets/images/eye.svg' ) ? WPUF_ASSET_URI . '/images/eye.svg' : '';
        ?>

        <li <?php $this->print_list_attributes( $field_settings ); ?>>
            <?php $this->print_label( $field_settings, $form_id ); ?>

            <div class="wpuf-fields">
                <div class="wpuf-fields-inline" style="position: relative;">
                    <input
                        class="password <?php echo ' wpuf_' . $field_settings['name'] . '_' . $form_id; ?>"
                        id="<?php echo $field_settings['name'] . '_' . $form_id . '_1'; ?>"
                        type="password"
                        data-required="<?php echo $field_settings['required']; ?>"
                        data-type="password"
                        data-repeat="<?php echo $repeat_pass; ?>"
                        data-minimum-length="<?php echo $minimum_length; ?>"
                        data-strength="<?php echo $minimum_strength; ?>"
                        placeholder="<?php echo esc_attr( $field_settings['placeholder'] ); ?>"
                        value=""
                        size="<?php echo esc_attr( $field_settings['size'] ); ?>"
                        name="pass1"
                    />
                    <?php
                    if ( $eye_icon_src ) {
                        ?>
                        <img class="wpuf-eye" src="<?php echo esc_url( $eye_icon_src ); ?>" alt="">
                        <?php
                    }
                    ?>
                </div>

                <span class="wpuf-wordlimit-message wpuf-help"></span>
                <?php $this->help_text( $field_settings ); ?>
            </div>
        </li>

        <?php if ( 'yes' === $repeat_pass ) { ?>

            <?php
            $confirm_pass_field_settings          = $field_settings;
            $confirm_pass_field_settings['label'] = __( 'Confirm Password', 'wpuf-pro' );
            $confirm_pass_field_settings['name']  = 'confirm_password';
            $re_pass_placeholder                  = ! empty( $field_settings['re_pass_placeholder'] ) ? esc_attr( $field_settings['re_pass_placeholder'] ) : '';
            $re_pass_help                         = ! empty( $field_settings['re_pass_help'] ) ? esc_attr( $field_settings['re_pass_help'] ) : '';
            ?>

            <li <?php $this->print_list_attributes( $confirm_pass_field_settings ); ?>>
                <div class="wpuf-label">
                    <label for="<?php echo isset( $field_settings['re_pass_label'] ) ? $field_settings['re_pass_label'] . '_' . $form_id : 'cls'; ?>"><?php echo $field_settings['re_pass_label'] . $this->required_mark( $field_settings ); ?></label>
                </div>

                <div class="wpuf-fields">
                    <input
                        id="<?php echo $field_settings['name'] . '_' . $form_id . '_2'; ?>"
                        class="password <?php echo ' wpuf_' . $field_settings['name'] . '_' . $form_id; ?>"
                        type="password"
                        data-strength="<?php echo $minimum_strength; ?>"
                        placeholder="<?php echo $re_pass_placeholder; ?>"
                        data-required="<?php echo $field_settings['required']; ?>"
                        data-type="confirm_password" name="pass2"
                        value=""
                        size="<?php echo esc_attr( $field_settings['size'] ); ?>"
                    />

                    <span class="wpuf-wordlimit-message wpuf-help"></span>
                    <span class="wpuf-help"><?php echo esc_html( $re_pass_help ); ?></span>
                </div>
            </li>

            <?php
        }

        if ( $pass_strength ) {
            ?>

            <li>
                <div class="wpuf-label">&nbsp;</div>
                <div class="wpuf-fields">
                    <div class="pass-strength-result" id="pass-strength-result_<?php echo $form_id; ?>" style="display: block"><?php esc_html_e( 'Strength indicator', 'wpuf-pro' ); ?></div>
                </div>
            </li>

            <script type="text/javascript">
                jQuery(function($) {
                    function check_pass_strength() {
                        var pass1 = $("#<?php echo $field_settings['name'] . '_' . $form_id . '_1'; ?>").val(),
                            pass2 = $("#<?php echo $field_settings['name'] . '_' . $form_id . '_2'; ?>").val(),
                            strength;
                        let minimumStrength = "<?php echo $minimum_strength; ?>";
                        let strengthField = "#pass-strength-result_<?php echo $form_id; ?>";

                        if ( typeof pass2 === undefined ) {
                            pass2 = pass1;
                        }

                        $( strengthField ).removeClass('short bad good strong');
                        if (!pass1) {
                            $( strengthField ).html(pwsL10n.empty);
                            return;
                        }

                        if ( ! minimumStrength ) {
                            return;
                        }

                        strength = wp.passwordStrength.meter(pass1, wp.passwordStrength.userInputDisallowedList(), pass2);

                        switch (strength) {
                            case 2:
                                $( strengthField ).addClass('bad').html(pwsL10n.bad);
                                break;
                            case 3:
                                $( strengthField ).addClass('good').html(pwsL10n.good);
                                break;
                            case 4:
                                $( strengthField ).addClass('strong').html(pwsL10n.strong);
                                break;
                            case 5:
                                $( strengthField ).addClass('short').html(pwsL10n.mismatch);
                                break;
                            default:
                                $( strengthField ).addClass('short').html(pwsL10n['short']);
                        }

                        let passwordField = "#<?php echo $field_settings['name'] . '_' . $form_id . '_1'; ?>"

                        WP_User_Frontend.removeErrors($(passwordField).closest('li'));

                        switch ( minimumStrength ) {
                            case 'weak':
                                if ( strength < 2 ) {
                                    WP_User_Frontend.markError(passwordField, 'custom', wpuf_frontend.password_warning_weak);
                                }
                                break;
                            case 'medium':
                                if ( strength < 3 ) {
                                    WP_User_Frontend.markError(passwordField, 'custom', wpuf_frontend.password_warning_medium);
                                }
                                break;
                            case 'strong':
                                if ( strength < 4 ) {
                                    WP_User_Frontend.markError(passwordField, 'custom', wpuf_frontend.password_warning_strong);
                                }
                                break;
                        }
                    }

                    $("#<?php echo $field_settings['name'] . '_' . $form_id . '_1'; ?>").val('').keyup(check_pass_strength);
                    $("#<?php echo $field_settings['name'] . '_' . $form_id . '_2'; ?>").val('').keyup(check_pass_strength);
                    $( "#pass-strength-result_<?php echo $form_id; ?>" ).show();
                });
            </script>
            <?php
        }
    }

    /**
     * It's a full width block
     *
     * @return void
     **/
    public function is_full_width() {
        return true;
    }

	/**
	 * Get field options setting
	 *
	 * @return array
	 **/
    public function get_options_settings() {
        $default_options      = $this->get_default_option_settings( false, array( 'dynamic' ) );
        $default_text_options = $this->get_default_text_option_settings();

        $settings = array(

            array(
                'name'          => 'min_length',
                'title'         => __( 'Minimum password length', 'wpuf-pro' ),
                'type'          => 'text',
                'section'       => 'advanced',
                'priority'      => 23,
            ),

            array(
                'name'          => 'repeat_pass',
                'title'         => __( 'Password Re-type', 'wpuf-pro' ),
                'type'          => 'checkbox',
                'options'       => array( 'yes' => __( 'Require Password repeat', 'wpuf-pro' ) ),
                'is_single_opt' => true,
                'section'       => 'advanced',
                'priority'      => 24,
            ),

            array(
                'name'          => 're_pass_label',
                'title'         => __( 'Re-type password label', 'wpuf-pro' ),
                'type'          => 'text',
                'section'       => 'advanced',
                'priority'      => 25,
            ),

            [
                'name'      => 're_pass_placeholder',
                'title'     => __( 'Re-type password placeholder', 'wpuf-pro' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 25.1,
                'help_text' => __( 'Text for HTML5 placeholder attribute', 'wpuf-pro' ),
            ],

            [
                'name'      => 're_pass_help',
                'title'     => __( 'Re-type password help', 'wpuf-pro' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 20.1,
                'help_text' => __( 'Give the user some information about this field', 'wpuf-pro' ),
            ],

            array(
                'name'          => 'pass_strength',
                'title'         => __( 'Password Strength Meter', 'wpuf-pro' ),
                'type'          => 'checkbox',
                'options'       => array( 'yes' => __( 'Show password strength meter', 'wpuf-pro' ) ),
                'is_single_opt' => true,
                'section'       => 'advanced',
                'priority'      => 26,
            ),

            [
                'name'          => 'minimum_strength',
                'title'         => __( 'Minimum Strength', 'wpuf-pro' ),
                'type'          => 'select',
                'options'       => [
                    'weak'   => __( 'Weak', 'wpuf-pro' ),
                    'medium' => __( 'Medium', 'wpuf-pro' ),
                    'strong' => __( 'Strong', 'wpuf-pro' ),
                ],
                'is_single_opt' => true,
                'section'       => 'advanced',
                'priority'      => 26.1,
            ],
        );

        return array_merge( $default_options, $default_text_options, $settings );
    }

    /**
     * Get the field props
     *
     * @return array
     **/
    public function get_field_props() {
        $defaults = $this->default_attributes();

        $props = [
            'input_type'          => 'password',
            'name'                => 'password',
            'required'            => 'yes',
            'is_meta'             => 'no',
            'size'                => 40,
            'id'                  => 0,
            'is_new'              => true,
            'min_length'          => 5,
            'repeat_pass'         => 'yes',
            're_pass_label'       => 'Confirm Password',
            'pass_strength'       => 'yes',
            're_pass_placeholder' => '',
            'minimum_strength'    => 'weak',
            're_pass_help'        => '',
        ];

        return array_merge( $defaults, $props );
    }

    /**
     * Prepare entry
     *
     * @param $field
     *
     * @return mixed
     */
    public function prepare_entry( $field ) {
        $field_name = isset( $_REQUEST[ $field['name'] ] ) ? trim( sanitize_text_field( wp_unslash( $_REQUEST[ $field['name'] ] ) ) ) : '';
		return $field_name;
    }

}
