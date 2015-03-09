<div class="wrap">
	<h2><?php _e( 'Settings', 'audiotheme' ); ?></h2>

	<form action="options.php" method="post">
		<?php settings_fields( 'audiotheme-settings' ); ?>
		<?php do_settings_sections( 'audiotheme-settings' ); ?>
		<?php submit_button(); ?>
	</form>
</div>
