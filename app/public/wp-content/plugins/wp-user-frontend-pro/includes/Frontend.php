<?php

namespace WeDevs\Wpuf\Pro;

use WeDevs\WpUtils\ContainerTrait;

/**
 * The class which will hold all the starting point of operations outside WordPress dashboard for WPUF
 * We will initialize all the admin classes from here.
 *
 * @since WPUF_SINCE
 */

class Frontend {
    use ContainerTrait;

    public function __construct() {
        $this->profile_form                = new Frontend\Profile_Form();
        $this->content_restriction         = new Frontend\Content_Restriction();
        $this->shortcode                   = new Frontend\Shortcode();
        $this->account                     = new Frontend\Account();
        $this->invoice                     = new Frontend\Invoice();

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_filter( 'wpuf_frontend_object', [ $this, 'wpuf_frontend_object' ] );
    }

    /**
     * Add pro asset URL to the frontend object
     *
     * @since 4.0.8
     *
     * @param array $object
     *
     * @return array
     */
    public function wpuf_frontend_object( $object ) {
        $object['pro_asset_url'] = WPUF_PRO_ASSET_URI;

        return $object;
    }

    /**
     * Enqueue JS and CSS files upon checking WPUF pages
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function enqueue_assets() {
        global $post;

        if ( ! is_a( $post, 'WP_Post' ) ) {
            return;
        }

        if ( has_shortcode( $post->post_content, 'wpuf_form' ) || has_shortcode(
                $post->post_content, 'wpuf_edit'
            ) || has_shortcode( $post->post_content, 'wpuf_profile' ) || has_shortcode(
                 $post->post_content, 'weforms'
             ) || has_shortcode( $post->post_content, 'wpuf_account' ) || is_single() || is_page_template() || strstr(
                 $_SERVER['REQUEST_URI'], 'wp-admin/post.php'
             ) || strstr(
                 $_SERVER['REQUEST_URI'], 'wp-admin/admin.php'
             ) || ( isset( $_GET['section'] ) && $_GET['section'] == 'submit-post' ) || ( isset( $_GET['wpuf_preview'] ) && isset( $_GET['form_id'] ) ) ) {

            wp_enqueue_style( 'wpuf-css-stars' );
            wp_enqueue_style( 'wpuf-math-captcha' );
            // Load International Telephone Input CSS - https://github.com/jackocnr/intl-tel-input.
            wp_enqueue_style( 'wpuf-intlTelInput' );

            wp_enqueue_script( 'wpuf-rating-js' );
            wp_enqueue_script( 'wpuf-conditional-logic' );
            // Load International Telephone Input JS - https://github.com/jackocnr/intl-tel-input.s
            wp_enqueue_script( 'wpuf-intlTelInput' );
        }
    }
}
