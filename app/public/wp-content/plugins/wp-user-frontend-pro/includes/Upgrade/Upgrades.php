<?php

namespace WeDevs\Wpuf\Pro\Upgrade;

class Upgrades {

    /**
     * The class constructor
     *
     * @since 4.0.11
     *
     * @return void
     */
    public function __construct() {
        add_action( 'admin_notices', [ $this, 'show_upgrade_notice' ] );
        add_action( 'admin_init', [ $this, 'perform_updates' ] );
    }

    /**
     * Show upgrade notice.
     *
     * @since 4.0.11
     *
     * @return void
     */
    public function show_upgrade_notice() {
        if ( ! current_user_can( 'update_plugins' ) || ! $this->is_upgrade_required() ) {
            return;
        }

        if ( $this->is_upgrade_required() ) {
            $url  = ! empty( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
            $link = add_query_arg(
                [
                    'wpuf_do_update' => true,
                    'nonce'          => wp_create_nonce( 'wpuf_do_update' ),
                ],
                $url
            );
            ?>
            <div id="wpuf-pro-message" class="updated">
                <p><?php printf( '<strong>%s</strong>', esc_attr__( 'WPUF Pro Data Update Required', 'wpuf-pro' ) ); ?></p>
                <p class="submit"><a href="<?php echo esc_url( $link ); ?>" class="wpuf-pro-update-btn button-primary"><?php esc_attr_e( 'Run the updater', 'wpuf-pro' ); ?></a></p>
            </div>

            <script type="text/javascript">
                jQuery('.wpuf-pro-update-btn').click('click', function() {
                    return confirm( '<?php esc_attr_e( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'wpuf-pro' ); ?>' );
                });
            </script>
            <?php
        }
    }

    /**
     * Perform all the necessary upgrade routines
     *
     * @since 4.0.11
     *
     * @return void
     */
    public function perform_updates() {
        if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'wpuf_do_update' ) ) {
            return;
        }

        if ( empty( $_GET['wpuf_do_update'] ) || ! sanitize_text_field( wp_unslash( $_GET['wpuf_do_update'] ) ) ) {
            return;
        }

        $installed_version = $this->get_db_installed_version();

        foreach ( self::$upgrades as $version => $file ) {
            if ( version_compare( $installed_version, $version, '<' ) ) {
                $path = WPUF_PRO_ROOT . '/includes/Upgrade/upgraders/' . $file;
                if ( file_exists( $path ) ) {
                    include_once $path;
                }
            }
        }

        update_option( wpuf_pro()->get_db_version_key(), WPUF_PRO_VERSION );
    }

    /**
     * List of upgrades
     *
     * Add array element like
     * `4.0.11 => [ 'upgrader' => Upgrades\V_4_0_11::class, 'require' => '2.8.0' ]`
     * where `require` is the last version found in \WeDevs\Wpuf\Pro\Upgrade\Upgrades
     * class.
     *
     * @since 4.0.11
     *
     * @var array
     */
    private static $upgrades = [
        '4.0.11' => 'upgrade-4.0.11.php',
    ];

    /**
     * Get DB installed version number
     *
     * @since 4.0.11
     *
     * @return string
     */
    public static function get_db_installed_version() {
        return get_option( 'wpuf_pro_version', null );
    }

    /**
     * Detects if upgrade is required
     *
     * @since 4.0.11
     *
     * @param bool $is_required
     *
     * @return bool
     */
    public static function is_upgrade_required( $is_required = false ) {
        $installed_version = self::get_db_installed_version();
        $upgrade_versions  = array_keys( self::$upgrades );

        if ( version_compare( $installed_version, end( $upgrade_versions ), '<' ) ) {
            return true;
        }

        return $is_required;
    }

    /**
     * Get upgrades
     *
     * @since 4.0.11
     *
     * @param array $upgrades
     *
     * @return array
     */
    public static function get_upgrades( $upgrades = [] ) {
        if ( ! self::is_upgrade_required() ) {
            return $upgrades;
        }

        $installed_version = self::get_db_installed_version();

        foreach ( self::$upgrades as $version => $upgrade ) {
            if ( version_compare( $installed_version, $version, '<' ) ) {
                $upgrades[ $upgrade['require'] ][] = $upgrade;
            }
        }

        return $upgrades;
    }
}
