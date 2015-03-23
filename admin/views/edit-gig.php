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

		<div id="audiotheme-gig-venue-meta-box" class="audiotheme-meta-box">
			<div class="audiotheme-meta-box-header">
				<h4 class="audiotheme-meta-box-title"><?php _e( 'Venue', 'audiotheme' ); ?></h4>
			</div>
			<div class="audiotheme-meta-box-body">
				<input type="hidden" name="gig_venue_id" id="gig-venue-id" value="<?php echo absint( $venue_id ); ?>">
			</div>
		</div>

	</div>

	<?php wp_nonce_field( 'save-gig_' . $post->ID, 'audiotheme_save_gig_nonce' ); ?>
</div>
