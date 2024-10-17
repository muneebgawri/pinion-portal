<?php

namespace WeDevs\Wpuf\Pro;

/**
 * The Integrations class to handle all 3rd party theme/plugins integrations and compatibility
 */
class Integrations {
    public function __construct() {
        add_filter( 'loco_plugins_data', [ $this, 'overwrite_text_domain_for_loco' ] );
    }

    /**
     * Overwrite text domain for loco translate
     *
     * @since 4.0.8
     *
     * @param array $cached
     *
     * @return array
     */
    public function overwrite_text_domain_for_loco( $cached ) {
        if ( ! isset( $cached['wp-user-frontend-pro/wpuf-pro.php'] ) ) {
            return $cached;
        }

        $cached['wp-user-frontend-pro/wpuf-pro.php']['TextDomain'] = 'wpuf-pro';

        return $cached;
    }
}
