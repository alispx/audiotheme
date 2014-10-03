<?php
/**
 * Edit track administration screen functionality.
 *
 * @package AudioTheme\Discography
 * @since 2.0.0
 */

/**
 * Edit track administration screen class.
 *
 * @package AudioTheme\Discography
 * @since 2.0.0
 */
class AudioTheme_Admin_Screen_EditTrack {
	/**
	 * Screen identifier.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	public $id = 'edit_track';

	/**
	 * Load the screen.
	 *
	 * @since 2.0.0
	 */
	public function load() {
		$this->register_hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'load-post.php',                   array( $this, 'load_screen' ) );
		add_action( 'load-post-new.php',               array( $this, 'load_screen' ) );
		add_action( 'add_meta_boxes_audiotheme_track', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post',                       array( $this, 'on_track_save' ) );
	}

	/**
	 * Register hooks specific to the Edit Track screen.
	 *
	 * @since 2.0.0
	 */
	public function load_screen() {
		if ( 'audiotheme_track' != get_current_screen()->post_type ) {
			return;
		}

		wp_enqueue_script( 'audiotheme-media' );
	}

	/**
	 * Register track meta boxes.
	 *
	 * @since 2.0.0
	 *
	 * @param int $post_id Track ID.
	 */
	function register_meta_boxes( $post ) {
		remove_meta_box( 'submitdiv', 'audiotheme_track', 'side' );

		add_meta_box(
			'submitdiv',
			__( 'Publish', 'audiotheme' ),
			'audiotheme_post_submit_meta_box',
			'audiotheme_track',
			'side',
			'high',
			array(
				'force_delete'      => false,
				'show_publish_date' => false,
				'show_statuses'     => array(),
				'show_visibility'   => false,
			)
		);

		add_meta_box(
			'audiotheme-track-details',
			__( 'Track Details', 'audiotheme' ),
			array( $this, 'display_details_meta_box' ),
			'audiotheme_track',
			'side',
			'high'
		);
	}

	/**
	 * Display track details meta box.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post The track post object being edited.
	 */
	public function display_details_meta_box( $post ) {
		wp_nonce_field( 'update-track_' . $post->ID, 'audiotheme_track_nonce' );
		?>
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

			<a href="#" class="button audiotheme-media-control-choose" style="float: right"><?php _e( 'Upload MP3', 'audiotheme' ); ?></a>
		</p>

		<p class="audiotheme-field">
			<label for="track-length"><?php _e( 'Length:', 'audiotheme' ) ?></label>
			<input type="text" name="length" id="track-length" value="<?php echo esc_attr( get_post_meta( $post->ID, '_audiotheme_length', true ) ) ; ?>" placeholder="00:00" class="widefat">
		</p>

		<p class="audiotheme-field">
			<label for="track-purchase-url"><?php _e( 'Purchase URL:', 'audiotheme' ) ?></label>
			<input type="url" name="purchase_url" id="track-purchase-url" value="<?php echo esc_url( get_post_meta( $post->ID, '_audiotheme_purchase_url', true ) ) ; ?>" class="widefat">
		</p>

		<?php
		if ( ! get_post( $post->post_parent ) ) {
			$records = get_posts( 'post_type=audiotheme_record&orderby=title&order=asc&posts_per_page=-1' );
			if ( $records ) {
				echo '<p class="audiotheme-field">';
					echo '<label for="post-parent">' . __( 'Record:', 'audiotheme' ) . '</label>';
					echo '<select name="post_parent" id="post-parent" class="widefat">';
						echo '<option value=""></option>';

						foreach ( $records as $record ) {
							printf( '<option value="%s">%s</option>',
								$record->ID,
								esc_html( $record->post_title )
							);
						}
					echo '</select>';
					echo '<span class="description">' . __( 'Associate this track with a record.', 'audiotheme' ) . '</span>';
				echo '</p>';
			}
		}
	}

	/**
	 * Rules for saving a track.
	 *
	 * @since 2.0.0
	 * @todo Get ID3 info for remote files.
	 *
	 * @param int $post_id Post ID.
	 */
	public function on_track_save( $post_id ) {
		$is_autosave    = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
		$is_revision    = wp_is_post_revision( $post_id );
		$is_valid_nonce = isset( $_POST['audiotheme_track_nonce'] ) && wp_verify_nonce( $_POST['audiotheme_track_nonce'], 'update-track_' . $post_id );

		// Bail if the data shouldn't be saved or intention can't be verified.
		if( $is_autosave || $is_revision || ! $is_valid_nonce ) {
			return;
		}

		$track = get_post( $post_id );

		$fields = array( 'artist', 'file_url', 'length', 'purchase_url' );
		foreach( $fields as $field ) {
			$value = empty( $_POST[ $field ] ) ? '' : $_POST[ $field ];

			if ( 'artist' == $field ) {
				$value = sanitize_text_field( $value );
			} elseif ( 'length' == $field ) {
				$value = preg_replace( '/[^0-9:]/', '', $value );
			} elseif ( ( 'file_url' == $field || 'purchase_url' == $field ) && ! empty( $value ) ) {
				$value = esc_url_raw( $value );
			}

			update_post_meta( $post_id, '_audiotheme_' . $field, $value );
		}

		$is_downloadable = empty( $_POST['is_downloadable'] ) ? null : 1;
		update_post_meta( $post_id, '_audiotheme_is_downloadable', $is_downloadable );

		// @todo This needs to be updated.
		audiotheme_record_update_track_count( $track->post_parent );
	}
}
