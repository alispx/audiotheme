<div class="audiotheme-gig-editor">

	<div class="audiotheme-gig-editor-primary">
		<div class="audiotheme-gig-date-picker audiotheme-gig-date-picker-start">
			<div id="audiotheme-gig-start-date-picker"></div>
			<div class="audiotheme-gig-date-picker-footer">
				<input type="text" name="gig_date" value="<?php echo esc_attr( $gig_date ); ?>" placeholder="YYYY-MM-DD" autocomplete="off">
			</div>
		</div>
	</div>

	<div class="audiotheme-gig-editor-secondary">

		<div class="audiotheme-meta-box">
			<div class="audiotheme-meta-box-header">
				<h4 class="audiotheme-meta-box-title"><?php _e( 'Time', 'audiotheme' ); ?></h4>
			</div>
			<div class="audiotheme-meta-box-body">
				<div class="audiotheme-gig-time-picker audiotheme-input-group">
					<input type="text" name="gig_time" id="gig-time" value="<?php echo esc_attr( $gig_time ); ?>" placeholder="HH:MM" class="audiotheme-input-group-field ui-autocomplete-input">
					<label for="gig-time" id="gig-time-select" class="audiotheme-input-group-trigger dashicons dashicons-clock"></label>
				</div>
			</div>
		</div>

		<div class="audiotheme-meta-box">
			<div class="audiotheme-meta-box-header">
				<h4 class="audiotheme-meta-box-title"><?php _e( 'Venue', 'audiotheme' ); ?></h4>
			</div>
			<div class="audiotheme-meta-box-body">
				<div class="audiotheme-input-group">
					<input type="text" name="gig_venue" id="gig-venue" value="<?php echo esc_html( $gig_venue ); ?>" class="audiotheme-input-group-field">
					<label for="gig-venue" id="gig-venue-select" class="audiotheme-input-group-trigger dashicons dashicons-arrow-down-alt2"></label>
				</div>

				<div id="gig-venue-timezone-group" class="hide-if-js">
					<input type="text" id="gig-venue-timezone-search" placeholder="<?php esc_attr_e( 'Search time zone by city', 'audiotheme' ); ?>" class="hide-if-no-js">
					<select name="audiotheme_venue[timezone_string]" id="gig-venue-timezone" class="hide-if-js">
						<?php echo \AudioTheme\Core\Util::timezone_choice( $timezone_string ); ?>
					</select><br>
					<em><?php _e( "Be sure to set a timezone when adding a new venue.", 'audiotheme' ); ?></em>
				</div>
			</div>
		</div>

	</div>

	<?php wp_nonce_field( 'save-gig_' . $post->ID, 'audiotheme_save_gig_nonce' ); ?>
</div>
