<div class="wpuf-user-lists layout-two square-layout <?php echo $list_class; ?>">
    <?php foreach ( $users as $user ) { ?>
    <div class="wpuf-ud-user-object">
        <?php
        $user_link = ( 'username' === $profile_permalink_base ) ? WPUF_User_Listing()->shortcode->get_user_link_by_username( $user->user_login ) : WPUF_User_Listing()->shortcode->get_user_link( $user->ID );
        if ( isset( WPUF_User_Listing()->shortcode->settings['avatar'] ) && true === WPUF_User_Listing()->shortcode->settings['avatar'] ) {
            ?>
            <div class="image">
            <?php echo get_avatar( $user->user_email, $avatar_size ); ?>
            </div>
            <?php
        }
        ?>
        <div class="wpuf-ud-user-details">
            <p class="wpuf-ud-user-name">
                <?php esc_html_e( $user->display_name, 'wpuf-pro' ); ?>
            </p>
            <div class="wpuf-ud-contact-details">
                <p class="wpuf-ud-user-email">
                    <?php esc_html_e( $user->user_email, 'wpuf-pro' ); ?>
                </p>
                <p class="wpuf-ud-user-website">
                    <a href="<?php echo esc_url( $user->user_url ); ?>" target="_blank">
                        <?php echo esc_url( $user->user_url ); ?>
                    </a>
                </p>
            </div>
            <p class="wpuf-ud-user-view-details">
                <a href="<?php echo $user_link; ?>"><?php esc_html_e( 'View Profile', 'wpuf-pro' ); ?></a>
                </a>
            </p>
        </div>
    </div>
    <?php } ?>
</div>
