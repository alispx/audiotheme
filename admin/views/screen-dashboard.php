<div class="audiotheme-dashboard-lead">
	<p>
		<?php _e( 'Gigs, Discography, and Videos are the backbone of AudioTheme. Explore each feature below or use the menu options to the left to get started.', 'audiotheme' ); ?>
	</p>
</div>

<div class="audiotheme-module-cards">

	<?php foreach ( array( 'gigs', 'discography', 'videos' ) as $module_id ) :
		$module    = $modules[ $module_id ];
		$classes   = array( 'audiotheme-module-card', 'audiotheme-module-card--' . $module_id );
		$classes[] = $modules->is_active( $module_id ) ? 'is-active' : 'is-inactive';
		$nonce     = wp_create_nonce( 'toggle-module_' . $module_id );
		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
			data-module-id="<?php echo esc_attr( $module_id ); ?>"
			data-toggle-nonce="<?php echo esc_attr( $nonce ); ?>">

			<div class="audiotheme-module-card-details">
				<h2 class="audiotheme-module-card-name"><?php echo esc_html( $module->name ); ?></h2>
				<div class="audiotheme-module-card-description">
					<?php echo wpautop( esc_html( $module->description ) ); ?>
				</div>
				<div class="audiotheme-module-card-overview">

					<?php do_action( 'audiotheme_module_card_overview', $module_id ); ?>

				</div>
			</div>

			<div class="audiotheme-module-card-actions">
				<div class="audiotheme-module-card-actions-primary">
					<?php if ( current_user_can( 'activate_plugins' ) ) : ?>
						<span class="spinner"></span>
						<button class="button button-primary button-activate js-toggle-module"><?php _e( 'Activate', 'audiotheme' ); ?></button>
					<?php endif; ?>

					<?php if ( 'discography' == $module_id ) : ?>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=audiotheme_record' ) ); ?>" class="button"><?php _e( 'Add Record', 'audiotheme' ); ?></a>
					<?php elseif ( 'gigs' == $module_id ) : ?>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=audiotheme_gig' ) ); ?>" class="button"><?php _e( 'Add Gig', 'audiotheme' ); ?></a>
					<?php elseif ( 'videos' == $module_id ) : ?>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=audiotheme_video' ) ); ?>" class="button"><?php _e( 'Add Video', 'audiotheme' ); ?></a>
					<?php else : ?>
						<?php do_action( 'audiotheme_module_card_primary_button', $module_id ); ?>
					<?php endif; ?>
				</div>

				<div class="audiotheme-module-card-actions-secondary">
					<a href=""><?php _e( 'Details', 'audiotheme' ); ?></a>
				</div>
			</div>

		</div>
	<?php endforeach; ?>

</div>
