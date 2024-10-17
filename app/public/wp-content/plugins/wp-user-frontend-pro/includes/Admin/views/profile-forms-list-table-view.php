<div class="wrap">
    <?php
    if ( method_exists( 'WeDevs\Wpuf\Admin\Menu', 'load_headway_badge' ) ) {
    ?>
    <h2 class="with-headway-icon">
        <span class="title-area">
        <?php
        _e( 'Profile Forms', 'wpuf-pro' );

        if ( current_user_can( wpuf_admin_role() ) ):
            ?>
            <a href="<?php echo $add_new_page_url; ?>" id="new-wpuf-profile-form" class="page-title-action add-form add-new-h2"><?php _e( 'Add Form', 'wpuf-pro' ); ?></a>
        <?php
        endif;
        ?>
        </span>
        <span class="flex-end">
            <span class="headway-icon"></span>
            <a class="canny-link" target="_blank" href="<?php echo esc_url( 'https://wpuf.canny.io/ideas' ); ?>">💡 <?php esc_html_e( 'Submit Ideas', 'wpuf-pro' ); ?></a>
        </span>
    </h2>

    <?php } else { ?>
    <div class="wrap">
        <h2>
            <?php

            _e( 'Profile Forms', 'wpuf-pro' );

            if ( current_user_can( wpuf_admin_role() ) ):
                ?>
                <a href="<?php echo $add_new_page_url; ?>" id="new-wpuf-profile-form" class="page-title-action add-form add-new-h2"><?php _e( 'Add Form', 'wpuf-pro' ); ?></a>
            <?php
            endif;
            ?>
        </h2>
    <?php } ?>

    <div class="list-table-wrap wpuf-profile-form-wrap">
        <div class="list-table-inner wpuf-profile-form-wrap-inner">

            <form method="get">
                <input type="hidden" name="page" value="wpuf-profile-forms">
                <?php
                    $wpuf_profile_form = new WeDevs\Wpuf\Pro\Admin\List_Table_Profile_Forms();
                    $wpuf_profile_form->prepare_items();
                    $wpuf_profile_form->search_box( __( 'Search Forms', 'wpuf-pro' ), 'wpuf-profile-form-search' );

                    if ( current_user_can( wpuf_admin_role() ) ) {
                        $wpuf_profile_form->views();
                    }

                    $wpuf_profile_form->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->

</div>
