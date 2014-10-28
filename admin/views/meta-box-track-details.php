<p class="audiotheme-field">
	<label for="track-artist"><?php _e( 'Artist:', 'audiotheme' ) ?></label>
	<input type="text" name="artist" id="track-artist" value="<?php echo esc_attr( get_post_meta( $post->ID, '_audiotheme_artist', true ) ) ; ?>" class="widefat">
</p>

<p class="audiotheme-field audiotheme-media-control audiotheme-field-upload"
	data-title="<?php esc_attr_e( 'Choose an MP3', 'audiotheme' ); ?>"
	data-update-text="<?php esc_attr_e( 'Use MP3', 'audiotheme' ); ?>"
	data-target="#track-file-url"
	data-return-property="url"
	data-file-type="audio">
	<label for="track-file-url"><?php _e( 'Audio File URL:', 'audiotheme' ) ?></label>
	<input type="url" name="file_url" id="track-file-url" value="<?php echo esc_attr( get_post_meta( $post->ID, '_audiotheme_file_url', true ) ) ; ?>" class="widefat">

	<input type="checkbox" name="is_downloadable" id="track-is-downloadable" value="1"<?php checked( get_post_meta( $post->ID, '_audiotheme_is_downloadable', true ) ); ?>>
	<label for="track-is-downloadable"><?php _e( 'Allow downloads?', 'audiotheme' ) ?></label>

	<button href="#" class="button audiotheme-media-control-choose" style="float: right"><?php _e( 'Upload MP3', 'audiotheme' ); ?></button>
</p>

<p class="audiotheme-field">
	<label for="track-length"><?php _e( 'Length:', 'audiotheme' ) ?></label>
	<input type="text" name="length" id="track-length" value="<?php echo esc_attr( get_post_meta( $post->ID, '_audiotheme_length', true ) ) ; ?>" placeholder="00:00" class="widefat">
</p>

<p class="audiotheme-field">
	<label for="track-purchase-url"><?php _e( 'Purchase URL:', 'audiotheme' ) ?></label>
	<input type="url" name="purchase_url" id="track-purchase-url" value="<?php echo esc_url( get_post_meta( $post->ID, '_audiotheme_purchase_url', true ) ) ; ?>" class="widefat">
</p>
