<div class="wrap">
	<h2><?php _e( 'Settings', 'audiotheme' ); ?></h2>

	<form action="options.php" method="post">
		<div class="audiotheme-modules">
			<h3><?php _e( 'Modules', 'audiotheme' ); ?></h3>

			<?php
			foreach ( $modules as $module_id => $module ) :
				$classes = array( 'audiotheme-module' );
				if ( $module['is_active'] ) {
					$classes[] = 'is-active';
				}
				$nonce   = wp_create_nonce( 'toggle-module_' . $module_id );
				?>
				<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
					data-module="<?php echo esc_attr( $module_id ); ?>"
					data-toggle-nonce="<?php echo esc_attr( $nonce ); ?>">
					<h3 class="audiotheme-module-title"><?php echo esc_html( $module['name'] ); ?></h3>
					<p>
						A description.
					</p>
				</div>
			<?php endforeach; ?>
		</div>

		<?php settings_fields( 'audiotheme-settings' ); ?>
		<?php do_settings_sections( 'audiotheme-settings' ); ?>
		<?php submit_button(); ?>
	</form>

	<style type="text/css"><?php echo $styles; ?></style>
</div>
