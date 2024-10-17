<?php

namespace WeDevs\Wpuf\Pro\Admin;

use WeDevs\Wpuf\Admin\Admin_Installer;

class Page_Installer extends Admin_Installer {

    public function __construct() {
        add_filter( 'wpuf_pro_page_install', [ $this, 'install_pro_pages' ] );
    }

    public function install_pro_pages( $profile_options ) {
        $reg_page = false;
        $reg_form = $this->create_reg_form();
        if ( $reg_form ) {
            $reg_page = $this->create_page(
                __( 'Registration', 'wpuf-pro' ), '[wpuf_profile type="registration" id="' . $reg_form . '"]'
            );
            if ( $reg_page ) {
                $profile_options['reg_override_page'] = $reg_page;
            }
        }
        $data = [
            'profile_options' => $profile_options,
            'reg_page'        => $reg_page,
        ];

        return $data;
    }
}
