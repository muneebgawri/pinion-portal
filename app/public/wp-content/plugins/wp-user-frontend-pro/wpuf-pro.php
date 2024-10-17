<?php
/*
Plugin Name: WP User Frontend Pro - professional
Plugin URI: https://wedevs.com/wp-user-frontend-pro/
Description: The paid module to add extra features on WP User Frontend.
Author: weDevs
Version: 4.0.11
Author URI: https://wedevs.com
License: GPL2
TextDomain: wpuf-pro
Domain Path: /languages/
*/

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$autoload = __DIR__ . '/vendor/autoload.php';

if ( file_exists( $autoload ) ) {
    require_once $autoload;
}

use WeDevs\WpUtils\SingletonTrait;
use WeDevs\WpUtils\ContainerTrait;

class WP_User_Frontend_Pro {

    use SingletonTrait;
    use ContainerTrait;

    /**
     * Package plan
     *
     * @since 2.6.1
     */
    private $plan = 'wpuf-professional';

    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
        add_action( 'init', [ $this, 'localization_setup' ] );

        register_activation_hook( __FILE__, [ $this, 'show_notice' ] );
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'wpuf-pro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Show notice if the WPUF free version is prior to the code restructure
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function show_notice() {
        // check whether the version of wpuf free is prior to the code restructure
        if ( defined( 'WPUF_VERSION' ) && version_compare( WPUF_VERSION, '4.0.0', '<' ) ) {
            add_action( 'admin_notices', [ $this, 'wpuf_activation_notice' ] );

            deactivate_plugins( WPUF_FILE );
        }

        if ( ! class_exists( 'WP_User_Frontend' ) ) {
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            add_action( 'admin_notices', [ $this, 'wpuf_activation_notice' ] );

            // needs to install the WPUF free plugin using ajax
            $this->ajax = new WeDevs\Wpuf\Pro\Ajax();
        }
    }

    /**
     * Check whether to show the version notice
     *
     * @since 4.0.0
     *
     * @return bool
     */
    private function need_to_show_version_notice() {
        return ( defined( 'WPUF_VERSION' ) && version_compare( WPUF_VERSION, '4.0.0', '<' ) ) || ! class_exists( 'WP_User_Frontend' );
    }

    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function init_plugin() {
        if ( $this->need_to_show_version_notice() ) {
            $this->show_notice();

            return;
        }

        // Define constants
        $this->define_constants();

        // Include files
        $this->includes();

        // Instantiate classes
        $this->instantiate();
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate() {
        $this->maybe_activate_modules();
    }

    /**
     * Maybe Activate modules
     *
     * @since 1.0.0
     *
     * @return void
     **/
    public function maybe_activate_modules() {
        global $wpdb;

        $has_installed = $wpdb->get_row( "SELECT option_id FROM {$wpdb->options} WHERE option_name = 'wpuf_pro_active_modules'" );

        if ( $has_installed ) {
            return;
        }

        if ( ! function_exists( 'wpuf_pro_get_modules' ) ) {
            require_once WPUF_PRO_INCLUDES . '/modules.php';
        }

        $modules = wpuf_pro_get_modules();

        if ( $modules ) {
            foreach ( $modules as $module_file => $data ) {
                wpuf_pro_activate_module( $module_file );
            }
        }
    }

    /**
     * Show WordPress error notice if WP User Frontend not found
     *
     * @since 2.4.2
     */
    public function wpuf_activation_notice() {
        ?>
        <style>
            .notice.is-dismissible#wpuf-update-offer-notice {
                display: none;
            }
        </style>
        <div class="updated" id="wpuf-pro-installer-notice" style="padding: 1em; position: relative;">
            <h2><?php esc_html_e( 'Your WP User Frontend Pro is almost ready!', 'wpuf-pro' ); ?></h2>

            <?php
            $plugin_file      = basename( __DIR__ ) . '/wpuf-pro.php';
            $core_plugin_file = 'wp-user-frontend/wpuf.php';
            $free_version     = get_option( 'wpuf_version' );
            ?>
            <a href="<?php echo wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . $plugin_file . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'deactivate-plugin_' . $plugin_file ); ?>" class="notice-dismiss" style="text-decoration: none;" title="<?php esc_attr_e( 'Dismiss this notice', 'wpuf-pro' ); ?>"></a>

            <?php if ( file_exists( WP_PLUGIN_DIR . '/' . $core_plugin_file ) && is_plugin_inactive( 'wpuf-user-frontend' ) && version_compare( $free_version, '4.0.0', '>=' )) { ?>
                <p><?php esc_html_e( 'You just need to activate the latest version of the latest Core Plugin to make it functional.', 'wpuf-pro' ); ?></p>
                <p>
                    <a class="button button-primary" href="<?php echo wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $core_plugin_file . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'activate-plugin_' . $core_plugin_file ); ?>"  title="<?php esc_attr_e( 'Activate this plugin', 'wpuf-pro' ); ?>"><?php esc_html_e( 'Activate', 'wpuf-pro' ); ?></a>
                </p>
            <?php } else { ?>
                <p>
                    <?php
                    /* translators: 1: opening anchor tag, 2: closing anchor tag. */
                    echo sprintf( __( 'We\'ve pushed a major update on both <b>WP User Frontend Free</b> and <b>WP User Frontend Pro</b> that requires you to use latest version of both. Please update the %1$sCore Plugin%2$s to the latest version. <br><strong>Please make sure to take a complete backup of your site before updating.</strong>', 'wpuf-pro' ), '<a target="_blank" href="https://wordpress.org/plugins/wp-user-frontend/">', '</a>' );
                    ?>
                </p>

                <p>
                    <button id="wpuf-pro-installer" class="button"><?php esc_html_e( 'Install Now', 'wpuf-pro' ); ?></button>
                </p>
            <?php } ?>

        </div>

        <script type="text/javascript">
            (function ($) {
                var wrapper = $('#wpuf-pro-installer-notice');

                wrapper.on('click', '#wpuf-pro-installer', function (e) {
                    var self = $(this);

                    e.preventDefault();
                    self.addClass('install-now updating-message');
                    self.text('<?php echo esc_js( 'Installing...', 'wpuf-pro' ); ?>');

                    var data = {
                        action: 'wpuf_pro_install_wp_user_frontend',
                        _wpnonce: '<?php echo wp_create_nonce( 'wpuf-pro-installer-nonce' ); ?>'
                    };

                    $.post(ajaxurl, data, function (response) {
                        if (response.success) {
                            self.attr('disabled', 'disabled');
                            self.removeClass('install-now updating-message');
                            self.text('<?php echo esc_js( 'Installed', 'wpuf-pro' ); ?>');

                            window.location.reload();
                        }
                    });
                });
            })(jQuery);
        </script>
        <?php
    }

    /**
     * Define the constants
     *
     * @return void
     */
    private function define_constants() {
        define( 'WPUF_PRO_VERSION', '4.0.11' );
        define( 'WPUF_PRO_FILE', __FILE__ );
        define( 'WPUF_PRO_ROOT', __DIR__ );
        define( 'WPUF_PRO_INCLUDES', WPUF_PRO_ROOT . '/includes' );
        define( 'WPUF_PRO_MODULES', WPUF_PRO_ROOT . '/modules' );
        define( 'WPUF_PRO_ROOT_URI', plugins_url( '', __FILE__ ) );
        define( 'WPUF_PRO_ASSET_URI', WPUF_PRO_ROOT_URI . '/assets' );
    }

    /**
     * Get the DB version key
     *
     * @since 4.0.11
     *
     * @return string
     */
    public function get_db_version_key() {
        return 'wpuf_pro_version';
    }

    /**
     * Include the files
     *
     * @return void
     */
    public function includes() {
        require_once WPUF_PRO_ROOT . '/functions/functions.php';

        if ( ! function_exists( 'wpuf_pro_get_active_modules' ) ) {
            require_once WPUF_PRO_ROOT . '/functions/modules.php';
        }

        // load all the active modules
        $modules = wpuf_pro_get_active_modules();

        if ( $modules ) {
            foreach ( $modules as $module_file ) {
                $module_path = WPUF_PRO_MODULES . '/' . $module_file;

                if ( file_exists( $module_path ) ) {
                    include_once $module_path;
                }
            }
        }
    }

    /**
     * Instantiate the classes
     *
     * @return void
     */
    public function instantiate() {
        new WeDevs\Wpuf\Pro\Upgrade\Upgrades();

        $this->assets               = new WeDevs\Wpuf\Pro\Assets();
        $this->subscription         = new WeDevs\Wpuf\Pro\Admin\Subscription();
        $this->taxonomy_restriction = new WeDevs\Wpuf\Pro\Admin\Taxonomy_Restriction();
        $this->customizer           = new WeDevs\Wpuf\Pro\Pro_Customizer_Options();
        $this->coupons              = new WeDevs\Wpuf\Pro\Coupons();
        $this->menu_restriction     = new WeDevs\Wpuf\Pro\Menu_Restriction();
        $this->pro_fields           = new WeDevs\Wpuf\Pro\Fields_Manager();
        $this->tax                  = new WeDevs\Wpuf\Pro\Tax();
        $this->coupon               = new WeDevs\Wpuf\Pro\Coupons();
        $this->post_form            = new WeDevs\Wpuf\Pro\Post_Form();
        $this->integrations         = new WeDevs\Wpuf\Pro\Integrations();

        if ( is_admin() ) {
            $this->admin = new WeDevs\Wpuf\Pro\Admin();
        } else {
            $this->frontend = new WeDevs\Wpuf\Pro\Frontend();
        }

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            $this->ajax = new WeDevs\Wpuf\Pro\Ajax();
        }
    }
}

/**
 * Load WPUF Pro Plugin when all plugins loaded
 *
 * @return void|WP_User_Frontend_Pro
 */
function wpuf_pro() { // phpcs:ignore
    // Get the instance of the singleton class
    return WP_User_Frontend_Pro::instance();
}

wpuf_pro();
