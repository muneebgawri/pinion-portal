<?php

namespace WeDevs\Wpuf\Pro\Admin;

use WP_Post;

/**
 * Coupon Class
 *
 * @package WPUF
 */
class Coupon {

    public function __construct() {
        add_action( 'init', [ $this, 'register_post_type' ] );
        // dynamic hook. sample: "add_meta_boxes_{$post_type}"
        add_action( 'add_meta_boxes_wpuf_coupon', [ $this, 'add_meta_box_coupon_post' ] );
        add_action( 'add_meta_boxes_wpuf_coupon', [ $this, 'load_scripts' ] );
        add_action( 'save_post', [ $this, 'save_form_meta' ], 1, 3 );
        add_filter( 'enter_title_here', [ $this, 'change_default_title' ] );
        add_filter( 'post_updated_messages', [ $this, 'updated_messages' ] );
        // dynamic hook. sample: "manage_{$screen->id}_columns"
        add_filter( 'manage_wpuf_coupon_posts_columns', [ $this, 'coupon_columns_head' ] );
        // dynamic hook. sample: "manage_{$this->screen->id}_custom_column"
        add_action( 'manage_wpuf_coupon_posts_custom_column', [ $this, 'coupon_columns_content' ], 10, 2 );
        add_action( 'wpuf_coupon_settings_form', [ $this, 'wpuf_coupon_settings_form_runner' ] );
        add_action( 'wpuf_check_save_permission', [ $this, 'wpuf_check_save_permission_runner' ], 10, 2 );
    }

    /**
     * Load all the scripts
     *
     * @return void
     */
    public function load_scripts() {
        wp_enqueue_script( 'wpuf-jquery-ui' );
        wp_enqueue_script( 'wpuf-chosen' );
        wp_enqueue_style( 'wpuf-jquery-ui' );
        wp_enqueue_style( 'wpuf-chosen' );
    }

    /**
     * Coupon list table column values
     *
     * @param string $column_name
     * @param int    $post_ID
     *
     * @return void
     */
    public function coupon_columns_content( $column_name, $post_ID ) {
        switch ( $column_name ) {
            case 'coupon_type':
                $price = get_post_meta( $post_ID, '_type', true );
                if ( $price == 'amount' ) {
                    _e( 'Fixed Price', 'wpuf-pro' );
                } else {
                    _e( 'Percentage', 'wpuf-pro' );
                }
                break;
            case 'amount':
                $type   = get_post_meta( $post_ID, '_type', true );
                $amount = get_post_meta( $post_ID, '_amount', true );
                echo ( $type == 'percent' ) ? $amount . '%' : wpuf_format_price( $amount ? $amount : 0 );
                break;
            case 'usage_limit':
                $usage_limit = get_post_meta( $post_ID, '_usage_limit', true );
                if ( intval( $usage_limit ) == 0 ) {
                    $usage_limit = __( '&infin;', 'wpuf-pro' );
                }
                $use = intval( get_post_meta( $post_ID, '_coupon_used', true ) );
                echo $use . '/' . $usage_limit;
                break;
            case 'expire_date':
                $start_date = get_post_meta( $post_ID, '_start_date', true );
                $end_date   = get_post_meta( $post_ID, '_end_date', true );
                $start_date = ! empty( $start_date ) ? date_i18n( 'M j, Y', strtotime( $start_date ) ) : '';
                $end_date   = ! empty( $end_date ) ? date_i18n( 'M j, Y', strtotime( $end_date ) ) : '';
                echo ! empty( $start_date ) & ! empty( $start_date ) ? $start_date . ' to ' . $end_date : '-';
                break;
        }
    }

    /**
     * Coupon list table columns
     *
     * @param array $head
     *
     * @return array
     */
    public function coupon_columns_head( $head ) {
        unset( $head['date'] );
        $head['title']       = __( 'Coupon Code', 'wpuf-pro' );
        $head['coupon_type'] = __( 'Coupon Type', 'wpuf-pro' );
        $head['amount']      = __( 'Amount', 'wpuf-pro' );
        $head['usage_limit'] = __( 'Usage / Limit', 'wpuf-pro' );
        $head['expire_date'] = __( 'Expire date', 'wpuf-pro' );

        return $head;
    }

    /**
     * Custom post update message
     *
     * @param array $messages
     *
     * @return array
     */
    public function updated_messages( $messages ) {
        $message = [
            0  => '',
            1  => __( 'Coupon updated.', 'wpuf-pro' ),
            2  => __( 'Custom field updated.', 'wpuf-pro' ),
            3  => __( 'Custom field deleted.', 'wpuf-pro' ),
            4  => __( 'Coupon updated.', 'wpuf-pro' ),
            5  => isset( $_GET['revision'] ) ? sprintf(
                __( 'Coupon restored to revision from %s', 'wpuf-pro' ),
                wp_post_revision_title( ( int ) $_GET['revision'], false )
            ) : false,
            6  => __( 'Coupon published.', 'wpuf-pro' ),
            7  => __( 'Coupon saved.', 'wpuf-pro' ),
            8  => __( 'Coupon submitted.', 'wpuf-pro' ),
            9  => '',
            10 => __( 'Coupon draft updated.', 'wpuf-pro' ),
        ];
        $messages['wpuf_coupon'] = $message;

        return $messages;
    }

    /**
     * Placeholder text for coupon post title field
     *
     * @param string $title
     *
     * @return string
     */
    public function change_default_title( $title ) {
        $screen = get_current_screen();
        if ( 'wpuf_coupon' == $screen->post_type ) {
            $title = __( 'Enter coupon code', 'wpuf-pro' );
        }

        return $title;
    }

    /**
     * Register coupon post type
     *
     * @return void
     */
    public function register_post_type() {
        $capability = wpuf_admin_role();
        register_post_type(
            'wpuf_coupon', [
                'label'           => __( 'Coupon', 'wpuf-pro' ),
                'public'          => false,
                'show_ui'         => true,
                'show_in_menu'    => false,
                'capability_type' => 'post',
                'hierarchical'    => false,
                'query_var'       => false,
                'supports'        => [ 'title' ],
                'capabilities'    => [
                    'publish_posts'       => $capability,
                    'edit_posts'          => $capability,
                    'edit_others_posts'   => $capability,
                    'delete_posts'        => $capability,
                    'delete_others_posts' => $capability,
                    'read_private_posts'  => $capability,
                    'edit_post'           => $capability,
                    'delete_post'         => $capability,
                    'read_post'           => $capability,
                ],
                'labels'          => [
                    'name'               => __( 'Coupon', 'wpuf-pro' ),
                    'singular_name'      => __( 'Coupon', 'wpuf-pro' ),
                    'menu_name'          => __( 'Coupon', 'wpuf-pro' ),
                    'add_new'            => __( 'Add Coupon', 'wpuf-pro' ),
                    'add_new_item'       => __( 'Add New Coupon', 'wpuf-pro' ),
                    'edit'               => __( 'Edit', 'wpuf-pro' ),
                    'edit_item'          => __( 'Edit Coupon', 'wpuf-pro' ),
                    'new_item'           => __( 'New Coupon', 'wpuf-pro' ),
                    'view'               => __( 'View Coupon', 'wpuf-pro' ),
                    'view_item'          => __( 'View Coupon', 'wpuf-pro' ),
                    'search_items'       => __( 'Search Coupon', 'wpuf-pro' ),
                    'not_found'          => __( 'No Coupon Found', 'wpuf-pro' ),
                    'not_found_in_trash' => __( 'No Coupon Found in Trash', 'wpuf-pro' ),
                    'parent'             => __( 'Parent Coupon', 'wpuf-pro' ),
                ],
            ]
        );
    }

    /**
     * Adds coupon details meta boxe
     *
     * @return void
     */
    public function add_meta_box_coupon_post() {
        add_meta_box(
            'wpuf-metabox-coupon', __( 'Coupon Details', 'wpuf-pro' ), [ $this, 'settings_form' ], 'wpuf_coupon',
            'normal', 'high'
        );
    }

    /**
     * Save coupon details
     *
     * @param int     $post_ID
     * @param WP_Post $post
     *
     * @return void
     */
    public function save_form_meta( $post_ID, $post, $update ) {
        do_action( 'wpuf_check_save_permission', $post, $update );
        $post = $_POST;
        if ( ! isset( $post['wpuf_coupon'] ) ) {
            return;
        }
        if ( ! wp_verify_nonce( $post['wpuf_coupon'], 'wpuf_coupon_editor' ) ) {
            return;
        }
        // Is the user allowed to edit the post or page?
        if ( ! current_user_can( 'edit_post', $post_ID ) ) {
            return;
        }
        $this->update_coupon_meta( $post_ID, $post );
    }

    /**
     * Update coupon meta
     *
     * @param int   $post_id
     * @param array $post
     *
     * @return void
     */
    public function update_coupon_meta( $post_id, $post ) {
        $acccess = ! empty( $post['access'] ) ? explode( "\n", $post['access'] ) : [];
        if ( empty( $post['code'] ) ) {
            $coupon_post = get_post( $post_id );
            if ( ! empty( $coupon_post->post_title ) ) {
                $post['code'] = $coupon_post->post_title;
            }
        }
        update_post_meta( $post_id, '_code', $post['code'] );
        update_post_meta( $post_id, '_package', array_key_exists( 'package', $post ) ? $post['package'] : 'all' );
        update_post_meta(
            $post_id, '_start_date', wpuf_date2mysql( ! empty( $post['start_date'] ) ? $post['start_date'] : '' )
        );
        update_post_meta(
            $post_id, '_end_date', wpuf_date2mysql( ! empty( $post['end_date'] ) ? $post['end_date'] : '' )
        );
        update_post_meta( $post_id, '_type', $post['type'] );
        update_post_meta( $post_id, '_amount', $post['amount'] );
        update_post_meta( $post_id, '_usage_limit', ! empty( $post['usage_limit'] ) ? $post['usage_limit'] : 0 );
        update_post_meta( $post_id, '_access', $acccess );
        do_action( 'wpuf_update_coupon', $post_id, $post );
    }

    /**
     * Print the main settings form
     *
     * @return void
     */
    public function settings_form() {
        do_action( 'wpuf_coupon_settings_form', $this );
    }

    /**
     * Get all packs
     *
     * @return array
     */
    public function get_packs() {
        $args = apply_filters(
            'wpuf_get_packs', [
                'post_type'   => 'wpuf_subscription',
                'post_status' => 'publish',
                'numberposts' => '-1',
            ]
        );
        $packs = get_posts( $args );

        return $packs;
    }

    /**
     * Get a single pack
     *
     * @param int $pack_id
     *
     * @return \WP_Post
     */
    public function get_pack( $pack_id ) {
        $pack = get_post( $pack_id );
        $pack = apply_filters( 'wpuf_get_pack', $pack, $pack_id );

        return $pack;
    }

    /**
     * Get pack dropdown
     *
     * @param array $selected
     *
     * @return void
     */
    public function get_pack_dropdown( $selected = [] ) {
        $selected = is_array( $selected ) ? $selected : [];
        $packs    = $this->get_packs();
        ?>

        <option value="all" <?php echo in_array( 'all', $selected ) ? 'selected' : ''; ?>><?php _e(
                'All package', 'wpuf-pro'
            ); ?></option>
        <?php
        foreach ( $packs as $key => $pack_obj ) {
            $selecte = ( ! in_array( 'all', $selected ) && in_array( $pack_obj->ID, $selected ) ) ? 'selected' : '';
            ?>
            <option value="<?php echo esc_attr(
                $pack_obj->ID
            ); ?>" <?php echo $selecte; ?>><?php echo $pack_obj->post_title; ?></option>
            <?php
        }
    }

    //coupon
    public function wpuf_coupon_settings_form_runner( $obj ) {
        Coupon_Elements::add_coupon_elements( $obj );
    }

    public function wpuf_check_save_permission_runner( $post, $update ) {
        Coupon_Elements::check_saving_capability( $post, $update );
    }
}
