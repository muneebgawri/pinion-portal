<?php

namespace WeDevs\Wpuf\Pro;

use WeDevs\Wpuf\Pro\Admin\Forms\PostTemplates\Post_Form_Template_EDD;
use WeDevs\Wpuf\Pro\Admin\Forms\PostTemplates\Post_Form_Template_Events_Calendar;
use WeDevs\Wpuf\Pro\Admin\Forms\PostTemplates\Post_Form_Template_WooCommerce;
use WeDevs\Wpuf\Pro\Admin\Post_Status_Notification;
use WeDevs\WpUtils\ContainerTrait;

/**
 * The Admin class which will hold all the starting point of WordPress dashboard admin operations for WPUF Pro
 * We will initialize all the admin classes from here.
 *
 * @since 4.0.0
 */

class Admin {

    use ContainerTrait;

    public function __construct() {
        $this->pro_menu             = new Admin\Menu();
        $this->user_approve         = new Admin\New_User_Approve();
        $this->coupon               = new Admin\Coupon();
        $this->module               = new Admin\Modules();
        $this->settings             = new Admin\Settings();
        $this->invoice              = new Admin\Invoice();
        $this->post_notification    = new Post_Status_Notification();
        $this->update               = new Admin\Update();
        $this->content_filter       = new Admin\Content_Filter();
        $this->admin_post_profile   = new Admin\Posting_Profile();
        $this->page_installer       = new Admin\Page_Installer();
        $this->profile_form         = new Admin\Profile_Form();
        $this->content_restriction  = new Admin\Content_Restriction();

        // post form templates
        add_filter( 'wpuf_get_post_form_templates', [ $this, 'post_form_templates' ] );
    }

    /**
     * Post form templates
     *
     * @since 2.4
     *
     * @param array $integrations
     *
     * @return array
     */
    public function post_form_templates( $integrations ) {
        $integrations['post_form_template_woocommerce']     = new Post_Form_Template_WooCommerce();
        $integrations['post_form_template_edd']             = new Post_Form_Template_EDD();
        $integrations['post_form_template_events_calendar'] = new Post_Form_Template_Events_Calendar();

        return $integrations;
    }
}
