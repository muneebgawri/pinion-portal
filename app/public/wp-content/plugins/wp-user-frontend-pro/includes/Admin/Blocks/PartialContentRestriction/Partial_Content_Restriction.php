<?php

namespace WeDevs\Wpuf\Pro\Admin\Blocks\PartialContentRestriction;

/**
 * Add WPUF block
 */
class Partial_Content_Restriction {
    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        //Enqueue the Dashicons script as they are not loading
        //when wpuf_form shortcode exists in admin pages.
        add_action( 'admin_enqueue_scripts', [ $this, 'load_dashicons' ] );
        // wait for Gutenberg to enqueue it's block assets
        add_action( 'enqueue_block_editor_assets', [ $this, 'wpuf_partial_content_block' ], 10 );
    }

    /**
     * Loading Dashicon
     *
     * @return void
     */
    public function load_dashicons() {
        global $pagenow;

        if ( ! in_array( $pagenow, [ 'post.php', 'post-new.php' ], true ) ) {
            return;
        }

        // load dashicons & editor style as they are not loading when partial restrict shortcode exists in admin pages.
        wp_register_style( 'wpuf_dashicons', includes_url() . 'css/dashicons.css', false, '1.0.0' );
        wp_enqueue_style( 'wpuf_dashicons' );
    }

    /**
     * Enqueue block scripts and styles
     *
     * @return void
     */
    public function wpuf_partial_content_block() {
        // Once we have Gutenberg block javascript, we can enqueue our assets
        wp_register_script( // phpcs:ignore
            'wpuf-partial-content-block',
            WPUF_PRO_ROOT_URI . '/includes/Admin/Blocks/PartialContentRestriction/block.js',
            [ 'wp-blocks', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-element', 'underscore' ],
            WPUF_PRO_VERSION
        );

        wp_register_style(
            'wpuf-partial-content-block-style',
            WPUF_PRO_ROOT_URI . '/includes/Admin/Blocks/PartialContentRestriction/block.css',
            [ 'wp-edit-blocks' ],
            WPUF_PRO_VERSION
        );

        /*
         * we need to get our forms so that the block can build a dropdown
         * with the forms
         * */
        wp_enqueue_script( 'wpuf-partial-content-block' );

        global $wp_roles;
        $subscriptions = get_posts(
            [
				'post_type'   => 'wpuf_subscription',
	            'post_status' => 'publish',
			]
        );

        wp_localize_script(
            'wpuf-partial-content-block', 'wpufProBlock', [
				'roles'          => $wp_roles->get_names(),
				'subscriptions'  => $subscriptions,
			]
        );
        wp_enqueue_style( 'wpuf-partial-content-block-style' );
    }
}
