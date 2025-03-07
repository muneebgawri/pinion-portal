<?php

/** Walker_Nav_Menu_Edit class */
if ( ! class_exists( 'Walker_Nav_Menu_Edit' ) ) {
    global $wp_version;
    if ( version_compare( $wp_version, '4.4', '>=' ) ) {
        require_once ABSPATH . 'wp-admin/includes/class-walker-nav-menu-edit.php';
    } else {
        require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
    }
}

/**
 * Custom Walker for Nav Menu Editor
 *
 * Add wp_nav_menu_item_custom_fields hook to the nav menu editor.
 *
 * Credits:
 * @helgatheviking - Initial concept which has made adding settings in the menu editor in a compatible way.
 * @kucrut - preg_replace() method so that we no longer have to translate core strings
 * @danieliser - refactor for less complexity between WP versions & updating versioned classes for proper backward compatibility with the new methods.
 *
 * @since WordPress 3.6.0
 * @uses Walker_Nav_Menu_Edit
 */
class Walker_Nav_Menu_Edit_Custom_Fields extends Walker_Nav_Menu_Edit {

    /**
     * Start the element output.
     *
     * @see Walker_Nav_Menu_Edit::start_el()
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param object $item Menu item data object.
     * @param int $depth Depth of menu item.
     * @param array $args
     * @param int $id
     */
    public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
        $item_output = '';

        // call parent function
        parent::start_el( $item_output, $item, $depth, $args, $id );

        // inject custom field HTML
        $output .= preg_replace(
            '/(?=<(fieldset|p)[^>]+class="[^"]*field-move)/',
            $this->get_custom_fields( $item, $depth, $args, $id ),
            $item_output
        );
    }


    /**
     * Get custom fields
     *
     * @uses do_action() Calls 'menu_item_custom_fields' hook
     *
     * @param object $item Menu item data object.
     * @param int $depth Depth of menu item. Used for padding.
     * @param array $args Menu item args.
     *
     * @return string Additional fields or html for the nav menu editor.
     */
    protected function get_custom_fields( $item, $depth, $args = array() ) {
        /**
         * From WP v5.4 this action is added to core
         *
         * @see wp-admin/includes/class-walker-nav-menu-edit.php
         * @see https://github.com/WordPress/wordpress-develop/commit/5a5c1924fd17be38c1cc7e1255e5e866c0d0fece
         */
        if ( did_action( 'wp_nav_menu_item_custom_fields' ) ) {
            return;
        }

        ob_start();
        $item_id = intval( $item->ID );
        /**
         * Get menu item custom fields from plugins/themes
         *
         * @param int $item_id post ID of menu
         * @param object $item Menu item data object.
         * @param int $depth Depth of menu item. Used for padding.
         * @param array $args Menu item args.
         *
         * @return string Custom fields
         */
        do_action( 'wp_nav_menu_item_custom_fields', $item_id, $item, $depth, $args );

        return ob_get_clean();
    }
}
