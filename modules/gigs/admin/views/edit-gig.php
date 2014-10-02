<div id="gig-ui" class="audiotheme-edit-after-title">
	<?php wp_nonce_field( 'save-gig_' . $post->ID, 'audiotheme_save_gig_nonce' ); ?>
	<table id="gig-fields">
		<tr>
			<th><label for="gig-date"><?php _e( 'Date', 'audiotheme' ) ?></label></th>
			<td>
				<div class="audiotheme-input-group">
					<input type="text" name="gig_date" id="gig-date" value="<?php echo esc_attr( $gig_date ); ?>" placeholder="YYYY/MM/DD" autocomplete="off" class="audiotheme-input-group-field">
				</div>
			</td>
		</tr>
		<tr>
			<th><label for="gig-time"><?php _e( 'Time', 'audiotheme' ) ?></label></th>
			<td>
				<div class="audiotheme-input-group">
					<input type="text" name="gig_time" id="gig-time" value="<?php echo esc_attr( $gig_time ); ?>" placeholder="HH:MM" class="audiotheme-input-group-field ui-autocomplete-input">
					<label for="gig-time" id="gig-time-select" class="audiotheme-input-group-trigger"><img src="<?php echo AUDIOTHEME_URI; ?>modules/admin/images/clock.png" width="12" height="12"></label>
				</div>
			</td>
		</tr>
		<tr>
			<th><label for="gig-venue"><?php _e( 'Venue', 'audiotheme' ) ?></label></th>
			<td>
				<div class="audiotheme-input-group">
					<input type="text" name="gig_venue" id="gig-venue" value="<?php echo esc_html( $gig_venue ); ?>" class="audiotheme-input-group-field">
					<label for="gig-venue" id="gig-venue-select" class="audiotheme-input-group-trigger"><img src="<?php echo AUDIOTHEME_URI; ?>modules/admin/images/arrow-down.png" width="12" height="12" title="<?php esc_attr_e( 'Select Venue', 'audiotheme' ); ?>" alt="<?php esc_attr_e( 'Select Venue', 'audiotheme' ); ?>"></label>
				</div>

				<div id="gig-venue-timezone-group" class="hide-if-js">
					<input type="text" id="gig-venue-timezone-search" placeholder="<?php esc_attr_e( 'Search time zone by city', 'audiotheme' ); ?>" class="hide-if-no-js">
					<select name="audiotheme_venue[timezone_string]" id="gig-venue-timezone" class="hide-if-js">
						<?php echo audiotheme_timezone_choice( $timezone_string ); ?>
					</select><br>
					<em><?php _e( "Be sure to set a timezone when adding a new venue.", 'audiotheme' ); ?></em>
				</div>
			</td>
		</tr>
		<tr>
			<th><?php _e( 'Note', 'audiotheme' ) ?></th>
			<td>
				<textarea name="excerpt" id="excerpt" cols="76" rows="3"><?php echo wp_specialchars_decode( esc_textarea( $post->post_excerpt ) ); ?></textarea><br>
				<span class="description"><?php _e( 'A description of the gig to display within the list of gigs. Who is the opening act, special guests, etc? Keep it short.', 'audiotheme' ); ?></span>
			</td>
		</tr>
	</table>
</div>
