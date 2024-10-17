<?php

namespace WeDevs\Wpuf\Pro;

/**
 * The assets handler for WPUF Pro. All the styles and scripts should register from here first.
 * Then we will enqueue them from the related pages.
 *
 * @since 4.0.0
 */
if ( class_exists( '\WeDevs\Wpuf\Assets' ) ) {
    class Assets extends \WeDevs\Wpuf\Assets {

        /**
         * The css dependencies list for form builder
         *
         * @since 4.0.0
         *
         * @var array|mixed|null
         */
        public $form_builder_css_deps = [];

        public function __construct() {
            add_filter( 'wpuf_styles_to_register', [ $this, 'get_pro_styles' ] );
            add_filter( 'wpuf_scripts_to_register', [ $this, 'get_pro_scripts' ] );
        }

        /**
         * Get the CSS of WPUF Pro
         *
         * @param $styles
         *
         * @since 4.0.0
         *
         * @return mixed
         */
        public function get_pro_styles( $styles ) {
            $pro_styles = [
                'form-builder-pro' => [
                    'src'  => WPUF_PRO_ASSET_URI . '/css/wpuf-form-builder-pro.css',
                    'deps' => [ 'wpuf-form-builder' ],
                ],
                'css-stars'      => [
                    'src' => WPUF_PRO_ASSET_URI . '/css/css-stars.css',
                ],
                'math-captcha'     => [
                    'src' => WPUF_PRO_ASSET_URI . '/css/frontend/fields/math-captcha.css',
                ],
                'intlTelInput'     => [
                    'src'     => WPUF_PRO_ASSET_URI . '/vendor/intl-tel-input/css/intlTelInput.min.css',
                    'version' => '17.0.5',
                ],
                'chosen'           => [
                    'src'     => WPUF_ASSET_URI . '/css/chosen/chosen.css',
                    'version' => '1.1.0',
                ],
                'module'           => [
                    'src'  => WPUF_PRO_ASSET_URI . '/css/wpuf-module.css',
                ],
                'tax'           => [
                    'src'  => WPUF_PRO_ASSET_URI . '/css/wpuf-tax.css',
                ],
            ];

            return array_merge( $styles, $pro_styles );
        }

        /**
         * Get the JS of WPUF Pro
         *
         * @param $styles
         *
         * @since 4.0.0
         *
         * @return mixed
         */
        public function get_pro_scripts( $scripts ) {
            $pro_scripts = [
                'form-builder-mixins-pro'     => [
                    'src'  => WPUF_PRO_ASSET_URI . '/js/wpuf-form-builder-mixins-pro.js',
                    'deps' => [ 'wpuf-form-builder-mixins' ],
                ],
                'form-builder-components-pro' => [
                    'src'  => WPUF_PRO_ASSET_URI . '/js/wpuf-form-builder-components-pro.js',
                    'deps' => [ 'wpuf-form-builder-components' ],
                ],
                'form-builder-wpuf-forms-pro' => [
                    'src'  => WPUF_PRO_ASSET_URI . '/js/wpuf-form-builder-wpuf-forms-pro.js',
                    'deps' => [ 'wpuf-form-builder-wpuf-forms' ],
                ],
                'barrating'                      => [
                    'src'  => WPUF_PRO_ASSET_URI . '/js/jquery.barrating.min.js',
                    'deps' => [ 'jquery' ],
                ],
                'conditional-logic'           => [
                    'src'  => WPUF_PRO_ASSET_URI . '/js/conditional-logic.js',
                    'deps' => [ 'jquery' ],
                ],
                'intlTelInput'                => [
                    'src'     => WPUF_PRO_ASSET_URI . '/vendor/intl-tel-input/js/intlTelInput.min.js',
                    'deps'    => [ 'jquery' ],
                    'version' => '17.0.5',
                ],
                'tax'                         => [
                    'src'  => WPUF_PRO_ASSET_URI . '/js/wpuf-tax.js',
                    'deps' => [ 'jquery' ],
                ],
                'form-builder-wpuf-profile'   => [
                    'src'  => WPUF_PRO_ASSET_URI . '/js/wpuf-form-builder-wpuf-profile.js',
                    'deps' => [ 'jquery', 'underscore', 'wpuf-vue', 'wpuf-vuex' ],
                ],
                'chosen'                      => [
                    'src'     => WPUF_ASSET_URI . '/js/chosen.jquery.js',
                    'deps'    => [ 'jquery' ],
                    'version' => '1.1.0',
                ],
                'module'                      => [
                    'src'  => WPUF_PRO_ASSET_URI . '/js/wpuf-module.js',
                    'deps' => [ 'jquery', 'wpuf-jquery-blockui' ],
                ],
            ];

            return array_merge( $scripts, $pro_scripts );
        }

        /**
         * Register the CSS from here. Need to define the CSS first from get_styles()
         *
         * @since WPUF_SINCE
         *
         * @return void
         */
        public function register_styles( $styles ) {
            foreach ( $styles as $handle => $style ) {
                $deps    = ! empty( $style['deps'] ) ? $style['deps'] : [];
                $version = ! empty( $style['version'] ) ? $style['version'] : WPUF_PRO_VERSION;
                $media   = ! empty( $style['media'] ) ? $style['media'] : 'all';

                wp_register_style( 'wpuf-' . $handle, $style['src'], $deps, $version, $media );
            }
        }

        /**
         * Register the JS from here. Need to define the JS first from get_scripts()
         *
         * @since 4.0.0
         *
         * @return void
         */
        public function register_scripts( $scripts ) {
            foreach ( $scripts as $handle => $script ) {
                $deps      = ! empty( $script['deps'] ) ? $script['deps'] : [];
                $in_footer = ! empty( $script['in_footer'] ) ? $script['in_footer'] : true;
                $version   = ! empty( $script['version'] ) ? $script['version'] : WPUF_PRO_VERSION;

                wp_register_script( 'wpuf-' . $handle, $script['src'], $deps, $version, $in_footer );
            }
        }
    }
}
