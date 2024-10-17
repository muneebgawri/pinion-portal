<?php

namespace WeDevs\Wpuf\Pro\Frontend;

use WeDevs\Wpuf\Admin\Subscription;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class partial content restriction
 *
 * * @since 3.4.4
 */
class Content_Restriction {

    public function __construct() {
        add_filter( 'the_content', [ $this, 'the_content' ], 11 );
    }

    /**
     * Post content restriction
     *
     * @param string $content
     *
     * @return string
     */
    public function the_content( $content ) {
        global $post;
        $display_to = get_post_meta( $post->ID, '_wpuf_res_display', true );
        // no restriction found
        if ( ! in_array( $display_to, [ 'loggedin', 'subscription' ], true ) ) {
            return $content;
        }
        $allowed_packs = get_post_meta( $post->ID, '_wpuf_res_subscription', true );
        $allowed_roles = get_post_meta( $post->ID, '_wpuf_res_loggedin', true );

        return $this->content_filter( $display_to, $content, $allowed_packs, $allowed_roles );
    }

    /**
     * Add shortcode for partial content restriction
     *
     * @param array  $atts
     * @param string $content
     *
     * @return false|string
     */
    public function shortcode_handler( $atts, $content ) {
        $type = 'everyone';
        if ( isset( $atts['roles'] ) ) {
            $type = 'loggedin';
        }
        if ( isset( $atts['subscriptions'] ) ) {
            $type = 'subscription';
        }

        $defaults = [
            'roles'         => '',
            'subscriptions' => '',
        ];

        $atts          = shortcode_atts( $defaults, $atts, 'wpuf_content_restrict' );
        $roles         = ! empty( $atts['roles'] ) ? explode( ',', $atts['roles'] ) : [];
        $subscriptions = ! empty( $atts['subscriptions'] ) ? explode( ',', $atts['subscriptions'] ) : [];
        $subscriptions = array_map( 'intval', $subscriptions );

        ob_start();
        $this->partial_content_restrict( do_shortcode( $content ), $roles, $subscriptions, $type );

        return ob_get_clean();
    }

    /**
     * Restrict partial content
     *
     *
     * @param string $content
     * @param array  $roles
     * @param array  $subscriptions
     *
     * @return void
     */
    public function partial_content_restrict( $content, $roles, $subscriptions, $type ) {
        // for admin and everyone
        if ( current_user_can( 'manage_options' ) || 'everyone' === $type ) {
            echo $content;

            return;
        }

        $errors       = [];
        $current_pack = get_user_meta( get_current_user_id(), '_wpuf_subscription_pack', true );
        $pack_id      = ! empty( $current_pack['pack_id'] ) ? $current_pack['pack_id'] : 0;

        if ( 'loggedin' === $type && ! is_user_logged_in() ) {
            /* translators: 1: Login Url */
            $errors[] = sprintf(
                // translators: %s: login link
                __( 'You must be %s to view this content.', 'wpuf-pro' ), sprintf(
                    '<a href="%s">%s</a>', wp_login_url( get_permalink( get_the_ID() ) ), __( 'logged in', 'wpuf-pro' )
                )
            );
        }

        if ( 'loggedin' === $type && is_user_logged_in() && ! empty( $roles ) && ! wpuf_user_has_roles( $roles ) ) {
            $errors[] = __( 'This content is restricted for your user role', 'wpuf-pro' );
        }

        if ( 'subscription' === $type && ! is_user_logged_in() ) {
            /* translators: 1: Login Url */
            $errors[] = sprintf(
                // translators: %s: login link
                __( 'You must be %s to view this content.', 'wpuf-pro' ), sprintf(
                    '<a href="%s">%s</a>', wp_login_url( get_permalink( get_the_ID() ) ), __( 'logged in', 'wpuf-pro' )
                )
            );
        }
        if ( 'subscription' === $type && empty( $current_pack ) ) {
            /* translators: 1: Login Url */
            $errors[] = sprintf(
                __( 'You don\'t have a valid subscription package.', 'wpuf-pro' ), sprintf(
                    '<a href="%s">%s</a>', wp_login_url( get_permalink( get_the_ID() ) ), __( 'logged in', 'wpuf-pro' )
                )
            );
        }

        if ( 'subscription' === $type && ! empty( $subscriptions ) && ! in_array( intval( $pack_id ), $subscriptions, true ) ) {
            $errors[] = __( 'Your subscription pack is not allowed to view this content', 'wpuf-pro' );
        }

        if ( $errors ) {
            /* translators: 1: Error Message */
            printf( '<div class="wpuf-info wpuf-restrict-message">%s</div>', $errors[0] );
        } else {
            echo $content;
        }
    }

    /**
     * Shortcode support for content restriction
     *
     * @param array  $atts
     * @param string $content
     *
     * @return string
     */
    public function shortcode_filter( $atts, $content = '' ) {
        $atts = shortcode_atts(
            [
                'type'     => 'loggedin',
                'pack_ids' => '',
                'role'     => '',
            ], $atts, 'wpuf_restrict'
        );
        if ( in_array( $atts['type'], [ 'loggedin', 'subscription' ], true ) ) {
            $sub_packs = ( 'subscription' === $atts['type'] ) ? array_map(
                'intval', explode( ',', $atts['pack_ids'] )
            ) : [];

            return $this->content_filter( $atts['type'], $content, $sub_packs );
        }
        if ( 'role' === $atts['type'] ) {
            $errors = $this->get_restriction_errors();
            if ( ! is_user_logged_in() ) {
                return $this->wrap_error( $errors['login'] );
            }
            if ( ! current_user_can( $atts['role'] ) ) {
                return $this->wrap_error( $errors['role'] );
            }
        }

        return $content;
    }

    /**
     * Return the page content or error message
     * based on page restrictions and user roles
     *
     * @param string $type
     * @param string $content
     * @param array  $allowed_packs
     * @param array  $allowed_roles
     *
     * @return string
     */
    public function content_filter( $type, $content, $allowed_packs = [], $allowed_roles = [] ) {
        $errors = $this->get_restriction_errors();
        // restriction selected but user is not logged in
        if ( ( 'loggedin' === $type || 'subscription' === $type ) && ! is_user_logged_in() ) {
            return $this->wrap_error( $errors['login'] );
        }
        // no specific packs or roles restriction selected
        // or the visitor is admin
        // so display the content
        if ( ( empty( $allowed_packs ) && empty( $allowed_roles ) ) || current_user_can( 'manage_options' ) ) {
            return $content;
        }

        $current_user_id = get_current_user_id();
        if ( 'subscription' === $type ) {
            $sub_pack = Subscription::get_user_pack( $current_user_id );
            if ( ! $sub_pack ) {
                return $this->wrap_error( $errors['sub_limit'] );
            }
            if ( ! $this->is_valid_subscription( $sub_pack ) ) {
                return $this->wrap_error( $errors['sub_limit'] );
            }
            $pack_id = is_array( $sub_pack ) ? intval( $sub_pack['pack_id'] ) : 0;
            if ( ! in_array( $pack_id, $allowed_packs, true ) ) {
                return $this->wrap_error( $errors['sub_limit'] );
            }
        }
        if ( 'loggedin' === $type ) {
            $user_exist = false;
            foreach ( $allowed_roles as $role ) {
                if ( current_user_can( $role ) ) {
                    $user_exist = true;
                    break;
                }
            }
            if ( ! $user_exist ) {
                return $this->wrap_error( $errors['role'] );
            }
        }

        return $content;
    }

    /**
     * Get content restriction error messages
     *
     * @return array
     */
    public function get_restriction_errors() {
        return [
            'login'        => sprintf(
            // translators: %s: login link
                __( 'You must be %s to view the content.', 'wpuf-pro' ), sprintf(
                    '<a href="%s">%s</a>', wp_login_url( get_permalink( get_the_ID() ) ), __( 'logged in', 'wpuf-pro' )
                )
            ),
            'sub_limit'    => __( 'This content is restricted for your subscription package.', 'wpuf-pro' ),
            'invalid_pack' => __( 'You don\'t have a valid subscription package', 'wpuf-pro' ),
            'expired'      => __( 'Your subscription pack is invalid or expired.', 'wpuf-pro' ),
            'not_allowed'  => __( 'Your subscription pack is not allowed to view this content', 'wpuf-pro' ),
            'role'         => __( 'This content is restricted for your user role', 'wpuf-pro' ),
        ];
    }

    /**
     * Check if the subscription is valid
     *
     * @param array $package
     *
     * @return boolean
     */
    public function is_valid_subscription( $package ) {
        $pack_id = is_array( $package ) ? intval( $package['pack_id'] ) : 0;
        if ( ! $pack_id ) {
            return false;
        }
        // check expiration
        $expire = isset( $package['expire'] ) ? $package['expire'] : 0;
        if ( 'unlimited' === strtolower( $expire ) || empty( $expire ) ) {
            $has_expired = false;
        } elseif ( ( strtotime( date( 'Y-m-d', strtotime( $expire ) ) ) >= strtotime( date( 'Y-m-d', time() ) ) ) ) { // phpcs:ignore
            $has_expired = false;
        } else {
            $has_expired = true;
        }
        if ( $has_expired ) {
            return false;
        }

        return true;
    }

    /**
     * Print restriction message
     *
     * @param string $text
     *
     * @return string
     */
    public function wrap_error( $text ) {
        return sprintf( '<div class="wpuf-info wpuf-restrict-message">%s</div>', $text );
    }
}
