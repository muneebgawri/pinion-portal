<?php
if ( $user_bio ) {
	?>
<div class="user-biography">
    <div class="biogrophy-title">
        <h3><?php esc_html_e( 'Biography', 'wpuf-pro' ); ?></h3>
    </div>
    <div class="biography-description">
        <p><?php echo links_add_target( make_clickable( $user_bio ) ); ?></p>
    </div>
</div>
<?php } ?>
