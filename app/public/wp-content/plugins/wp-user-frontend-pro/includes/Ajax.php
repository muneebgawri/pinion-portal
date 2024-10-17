<?php

namespace WeDevs\Wpuf\Pro;

use WeDevs\Wpuf\Pro\Traits\AjaxableTrait;
use WeDevs\Wpuf\Traits\TaxableTrait;

if ( trait_exists( 'WeDevs\Wpuf\Traits\TaxableTrait' ) ) {
    class Ajax {

        use TaxableTrait, AjaxableTrait;

        public function __construct() {
            $profile_form = new Frontend\Profile_Form();
            $coupon       = new Coupons();
            $modules      = new Admin\Modules();
            $this->register_ajax(
                'wpuf_pro_install_wp_user_frontend', [ new Installer(), 'install_wpuf_free' ], $this->logged_in_only
            );
            $this->register_ajax( 'wpuf_submit_register', [ $profile_form, 'user_register' ] );
            $this->register_ajax( 'wpuf_update_profile', [ $profile_form, 'update_profile' ] );
            $this->register_ajax( 'wpuf_coupon_apply', [ $coupon, 'apply_coupon' ] );
            $this->register_ajax( 'wpuf_coupon_cancel', [ $coupon, 'cancel_coupon' ] );
            $this->register_ajax( 'wpuf_delete_avatar', [ new Admin\Posting_Profile(), 'delete_avatar_ajax' ] );
            $this->register_ajax( 'wpuf_tax_states', [ $this, 'wpuf_tax_get_states_field' ], $this->logged_in_only );
            $this->register_ajax(
                'wpuf_get_base_states', [ $this, 'wpuf_tax_get_states_field' ], $this->logged_in_only
            );
            $this->register_ajax( 'wpuf_toggle_module', [ $modules, 'toggle_module' ], $this->logged_in_only );
            $this->register_ajax(
                'wpuf_toggle_all_modules', [ $modules, 'toggle_all_modules' ], $this->logged_in_only
            );
        }
    }
} else {
    class Ajax {
        use AjaxableTrait;

        public function __construct() {
            $this->register_ajax(
                'wpuf_pro_install_wp_user_frontend', [ new Installer(), 'install_wpuf_free' ], $this->logged_in_only
            );
        }
    }
}

