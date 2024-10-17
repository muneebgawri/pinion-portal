<?php

namespace WeDevs\Wpuf\Pro\Admin;

use WeDevs\Wpuf\Pro\Admin\Blocks\PartialContentRestriction\Partial_Content_Restriction;
use WP_Post;

/**
 * Content Restriction
 *
 * @since 2.4
 */
class Content_Restriction {

    /**
     * @var Partial_Content_Restriction
     */
    public $block_content;

    public function __construct() {
        // for gutenberg block
        $this->block_content = new Partial_Content_Restriction();

        // for classic editor
        add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
        add_action( 'save_post', [ $this, 'save_post_meta' ], 10, 2 );

        // show the content restriction shortcode on classic editor wpuf shortcodes list
        add_filter( 'wpuf_page_shortcodes', [ $this, 'add_content_restriction_shortcode' ] );
    }

    /**
     * Add content restriction shortcode
     *
     * @param array $shortcode_list
     *
     * @return array
     */
    public function add_content_restriction_shortcode( $shortcode_list ) {
        $shortcode_list['content-restriction'] = [
            'title'   => __( 'Partial Content Restriction', 'wpuf-pro' ),
            'content' => '[wpuf_partial_restriction]',
        ];

        return $shortcode_list;
    }

    /**
     * Meta box for all Post types
     *
     * Registers a meta box in public post types for
     * content restriction settings
     *
     * @return void
     */
    public function add_meta_boxes() {
        $post_types = get_post_types( [ 'public' => true ] );
        foreach ( $post_types as $post_type ) {
            add_meta_box(
                'wpuf-content-restriction', __( 'WPUF Content Restriction', 'wpuf-pro' ), [ $this, 'restriction_form' ],
                $post_type, 'normal', 'high'
            );
        }
    }

    public function restriction_form( $post ) {
        global $post;

        $display_to             = get_post_meta( $post->ID, '_wpuf_res_display', true );
        $selected_plans         = get_post_meta( $post->ID, '_wpuf_res_subscription', true );
        $display_to             = ! empty( $display_to ) ? $display_to : 'all';
        $subscriptions          = wpuf()->subscription->get_subscriptions();
        $selected_subscriptions = is_array( $selected_plans ) ? $selected_plans : [];
        $user_roles             = get_post_meta( $post->ID, '_wpuf_res_loggedin', true );
        ?>

        <table class="form-table" id="wpuf-content-restriction-table">
            <tbody>
            <tr>
                <th><?php esc_html_e( 'Display to', 'wpuf-pro' ); ?></th>
                <td>
                    <label>
                        <input
                            type="radio"
                            name="_wpuf_res_display"
                            value="all"
                            <?php checked( $display_to, 'all' ); ?>>
                            <?php esc_html_e( 'Everyone', 'wpuf-pro' ); ?>
                    </label>&nbsp;
                    <label>
                        <input
                            type="radio"
                            name="_wpuf_res_display"
                            value="loggedin"
                            <?php checked( $display_to, 'loggedin' ); ?>>
                            <?php esc_html_e( 'Logged in users only', 'wpuf-pro' ); ?>
                    </label>&nbsp;
                    <label>
                        <input
                            type="radio"
                            name="_wpuf_res_display"
                            value="subscription"
                            <?php checked( $display_to, 'subscription' ); ?>>
                        <?php esc_html_e( 'Subscription users only', 'wpuf-pro' ); ?>
                    </label>
                </td>
            </tr>

            <tr class="show-if-wpuf-res-subscription">
                <th><?php esc_html_e( 'Subscription Plans', 'wpuf-pro' ); ?></th>
                <td>
                    <?php
                    if ( $subscriptions ) {
                        foreach ( $subscriptions as $pack ) {
                            ?>
                            <label>
                                <input
                                    type="checkbox"
                                    name="_wpuf_res_subscription[]"
                                    <?php checked( in_array( $pack->ID, $selected_subscriptions, true ) ); ?>
                                    value="<?php echo $pack->ID; ?>"><?php echo $pack->post_title; ?>
                            </label>&nbsp;
                            <?php
                        }
                        printf(
                            '<p class="description">%s</p>', __(
                                'Members subscribed to these subscription plans will be able to view this page.', 'wpuf-pro'
                            )
                        );
                    } else {
                        esc_html_e( 'No subscription plan found.', 'wpuf-pro' );
                    }
                    ?>
                </td>
            </tr>

            <tr class="show-if-wpuf-res-loggedin">
                <th><?php esc_html_e( 'User Roles', 'wpuf-pro' ); ?></th>
                <td>
                    <?php
                    $all_roles = get_editable_roles();
                    // phpcs:ignore WordPress.Security.NonceVerification
                    if ( ! isset( $_POST['_wpuf_res_loggedin'] ) && $all_roles ) {
                        foreach ( $all_roles as $role_name => $role_info ) {
                            ?>
                            <label>
                                <input
                                    type="checkbox"
                                    name="_wpuf_res_loggedin[]"
                                    <?php checked( in_array( $role_name, (array) $user_roles, true ) ); ?>
                                    value="<?php echo $role_name; ?>"><?php echo $role_info['name']; ?>
                            </label>&nbsp;
                            <?php
                        }
                    }
                    ?>
                </td>
            </tr>
            </tbody>
        </table>

        <input
            type="hidden"
            name="_wpuf_res_nonce"
            id="_wpuf_res_nonce"
            value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>"/>

        <script type="text/javascript">
            jQuery( function ( $ ) {
                const displayField = 'input[name="_wpuf_res_display"][type=radio]';

                $( displayField ).change( function () {
                    const radio = $( this ).val();

                    // console.log(radio);

                    if (radio === 'subscription') {
                        $( '.show-if-wpuf-res-subscription' ).show();
                    } else {
                        $( '.show-if-wpuf-res-subscription' ).hide();
                    }
                } ).filter( ':checked' ).trigger( 'change' );

                $( displayField ).change( function () {
                    const radio = $( this ).val();

                    // console.log(radio);

                    if (radio === 'loggedin') {
                        $( '.show-if-wpuf-res-loggedin' ).show();
                    } else {
                        $( '.show-if-wpuf-res-loggedin' ).hide();
                    }
                } ).filter( ':checked' ).trigger( 'change' );

            } );
        </script>
        <?php
    }

    /**
     * Save the restriction settings
     *
     * @param int     $post_id
     * @param WP_Post $post
     *
     * @return void
     */
    public function save_post_meta( $post_id, $post ) {
        $nonce = ! empty( $_POST['_wpuf_res_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpuf_res_nonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, plugin_basename( __FILE__ ) ) ) {
            return;
        }

        // post type capability checking
        $post_type = get_post_type_object( $post->post_type );

        if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
            return;
        }
        $display_to    = 'all';
        $subscriptions = [];
        $all_roles     = get_editable_roles();
        $selected_roles = [];

        if ( isset( $_POST['_wpuf_res_display'] ) && in_array( $_POST['_wpuf_res_display'], [ 'all', 'loggedin', 'subscription' ], true ) ) {
            $display_to = wp_unslash( $_POST['_wpuf_res_display'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        }

        update_post_meta( $post_id, '_wpuf_res_display', $display_to );

        if ( ! empty( $_POST['_wpuf_res_subscription'] ) && is_array( $_POST['_wpuf_res_subscription'] ) ) {
            $subscriptions = array_map( 'intval', $_POST['_wpuf_res_subscription'] );
        }

        if ( ! empty( $_POST['_wpuf_res_loggedin'] ) && is_array( $_POST['_wpuf_res_loggedin'] ) ) {
            // filter out the roles that are not editable
            $selected_roles = array_intersect( $_POST['_wpuf_res_loggedin'], array_keys( $all_roles ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
        }

        if ( 'subscription' === $display_to ) {
            update_post_meta( $post_id, '_wpuf_res_subscription', $subscriptions );
        } else {
            delete_post_meta( $post_id, '_wpuf_res_subscription' );
        }

        if ( 'loggedin' === $display_to ) {
            update_post_meta( $post_id, '_wpuf_res_loggedin', $selected_roles );
        } else {
            delete_post_meta( $post_id, '_wpuf_res_loggedin' );
        }
    }

}
