<?php

namespace WeDevs\Wpuf\Pro\Frontend;

class Shortcode {
    public function __construct() {
        add_action( 'init', [ $this, 'init_shortcodes' ] );
    }

    /**
     * Initialize the WPUF pro shortcodes
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function init_shortcodes() {
        add_shortcode( 'wpuf_profile', [ wpuf_pro()->frontend->profile_form, 'shortcode_handler' ] );
        add_shortcode( 'wpuf_partial_restriction', [ wpuf_pro()->frontend->content_restriction, 'shortcode_handler' ] );
        add_shortcode( 'wpuf_restrict', [ wpuf_pro()->frontend->content_restriction, 'shortcode_filter' ] );
    }
}
