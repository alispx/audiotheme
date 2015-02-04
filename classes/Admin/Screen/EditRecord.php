<?php
/**
 * Edit record administration screen functionality.
 *
 * @package AudioTheme\Discography
 * @since 2.0.0
 */

/**
 * Edit record administration screen functionality.
 *
 * @package AudioTheme\Discography
 * @since 2.0.0
 */
class AudioTheme_Admin_Screen_EditRecord {
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
		add_action( 'load-post.php',                      array( $this, 'load_screen' ) );
		add_action( 'load-post-new.php',                  array( $this, 'load_screen' ) );
		add_action( 'add_meta_boxes_audiotheme_record',   array( $this, 'register_meta_boxes' ) );
		add_action( 'audiotheme_record_details_meta_box', array( $this, 'display_field_released' ) );
		add_action( 'audiotheme_record_details_meta_box', array( $this, 'display_field_artist' ), 20 );
		add_action( 'audiotheme_record_details_meta_box', array( $this, 'display_field_genre' ), 30 );
		add_action( 'audiotheme_record_details_meta_box', array( $this, 'display_field_links' ), 40 );
		add_action( 'save_post',                          array( $this, 'on_record_save' ) );
	}

	/**
	 * Register hooks specific to the Edit Record screen.
	 *
	 * @since 2.0.0
	 */
	public function load_screen() {
		if ( 'audiotheme_record' != get_current_screen()->post_type ) {
			return;
		}

		add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue_assets' ) );
		add_action( 'edit_form_after_editor', array( $this, 'render_tracklist_editor' ) );
	}

	/**
	 * Register record meta boxes.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post The record post object being edited.
	 */
	public function register_meta_boxes( $post ) {
		remove_meta_box( 'submitdiv', 'audiotheme_record', 'side' );

		add_meta_box(
			'submitdiv',
			__( 'Publish', 'audiotheme' ),
			'audiotheme_post_submit_meta_box',
			'audiotheme_record',
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
			'audiotheme-record-details',
			__( 'Record Details', 'audiotheme' ),
			array( $this, 'display_details_meta_box' ),
			'audiotheme_record',
			'side',
			'high'
		);
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_assets() {
		wp_enqueue_script(
			'audiotheme-record-edit',
			AUDIOTHEME_URI . 'admin/js/record-edit.js',
			array( 'audiotheme-admin', 'audiotheme-media' ),
			'1.0.0',
			true
		);
	}

	/**
	 * Tracklist editor.
	 *
	 * @since 2.0.0
	 */
	public function render_tracklist_editor( $post ) {
		$tracks = get_audiotheme_record_tracks( $post->ID );

		if ( $tracks ) {
			foreach ( $tracks as $key => $track ) {
				$tracks[ $key ] = array(
					'key'          => $key,
					'id'           => $track->ID,
					'title'        => esc_attr( $track->post_title ),
					'artist'       => esc_attr( get_post_meta( $track->ID, '_audiotheme_artist', true ) ),
					'fileUrl'      => esc_attr( get_post_meta( $track->ID, '_audiotheme_file_url', true ) ),
					'downloadable' => is_audiotheme_track_downloadable( $track->ID ),
					'purchaseUrl'  => esc_url( get_post_meta( $track->ID, '_audiotheme_purchase_url', true ) ),
				);
			}
		}

		include( AUDIOTHEME_DIR . 'admin/views/edit-record-tracklist.php' );
		include( AUDIOTHEME_DIR . 'admin/views/templates-record.php' );

		wp_localize_script( 'audiotheme-record-edit', '_audiothemeTracklistSettings', array(
			'postId' => $post->ID,
			'tracks' => empty( $tracks ) ? null : json_encode( $tracks ),
			'nonce'  => wp_create_nonce( 'get-default-track_' . $post->ID ),
		) );
	}

	/**
	 * Record details meta box.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post The record post object being edited.
	 */
	public function display_details_meta_box( $post ) {
		wp_nonce_field( 'update-record_' . $post->ID, 'audiotheme_record_nonce' );
		do_action( 'audiotheme_record_details_meta_box', $post );
	}

	/**
	 * Display a field to edit the record release year.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post Record post object.
	 */
	public function display_field_released( $post ) {
		$released = get_post_meta( $post->ID, '_audiotheme_release_year', true );
		?>
		<p class="audiotheme-field">
			<label for="record-year"><?php _e( 'Release Year', 'audiotheme' ); ?></label>
			<input type="text" name="release_year" id="record-year" value="<?php echo esc_attr( $released ) ; ?>" class="widefat">
		</p>
		<?php
	}

	/**
	 * Display a field to edit the record artist.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post Record post object.
	 */
	public function display_field_artist( $post ) {
		$artist = get_audiotheme_record_artist( $post->ID );
		?>
		<p class="audiotheme-field">
			<label for="record-artist"><?php _e( 'Artist', 'audiotheme' ); ?></label>
			<input type="text" name="artist" id="record-artist" value="<?php echo esc_attr( $artist ) ; ?>" class="widefat">
		</p>
		<?php
	}

	/**
	 * Display a field to edit the record genre.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post Record post object.
	 */
	public function display_field_genre( $post ) {
		$genre = get_audiotheme_record_genre( $post->ID );
		?>
		<p class="audiotheme-field">
			<label for="record-genre"><?php _e( 'Genre', 'audiotheme' ); ?></label>
			<input type="text" name="genre" id="record-genre" value="<?php echo esc_attr( $genre ) ; ?>" class="widefat">
		</p>
		<?php
	}

	/**
	 * Display a field to edit record links.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post Record post object.
	 */
	public function display_field_links( $post ) {
		$record_links = (array) get_audiotheme_record_links( $post->ID );
		$record_links = empty( $record_links ) ? array( '' ) : $record_links;

		$record_link_sources      = get_audiotheme_record_link_sources();
		$record_link_source_names = array_keys( $record_link_sources );
		sort( $record_link_source_names );

		include( AUDIOTHEME_DIR . 'admin/views/edit-record-links.php' );
	}

	/**
	 * Rules for saving a record.
	 *
	 * Creates and updates child tracks and saves additional record meta.
	 *
	 * @since 2.0.0
	 *
	 * @param int $post_id Post ID.
	 */
	public function on_record_save( $post_id ) {
		$is_autosave    = defined( 'DOING_AUTOSAVE' );
		$is_revision    = wp_is_post_revision( $post_id );
		$is_valid_nonce = isset( $_POST['audiotheme_record_nonce'] ) && wp_verify_nonce( $_POST['audiotheme_record_nonce'], 'update-record_' . $post_id );

		// Bail if the data shouldn't be saved or intention can't be verified.
		if( $is_autosave || $is_revision || ! $is_valid_nonce ) {
			return;
		}

		$current_user = wp_get_current_user();

		// Whitelisted fields.
		$fields = array( 'artist', 'genre', 'release_year' );
		foreach( $fields as $field ) {
			$value = empty( $_POST[ $field ] ) ? '' : $_POST[ $field ];
			update_post_meta( $post_id, '_audiotheme_' . $field, $value );
		}

		// Update purchase urls.
		$record_links = array();
		if ( isset( $_POST['record_links'] ) && is_array( $_POST['record_links'] ) ) {
			foreach( $_POST['record_links'] as $link ) {
				if ( ! empty( $link['name'] ) && ! empty( $link['url'] ) ) {
					$link['url']    = esc_url_raw( $link['url'] );
					$record_links[] = $link;
				}
			}
		}
		update_post_meta( $post_id, '_audiotheme_record_links', $record_links );

		// Update tracklist.
		if ( ! empty( $_POST['audiotheme_tracks'] ) ) {
			$i = 1;
			foreach ( $_POST['audiotheme_tracks'] as $track_data ) {
				$data         = array();
				$default_data = array( 'artist' => '', 'post_id' => '', 'title' => '' );
				$track_data   = wp_parse_args( $track_data, $default_data );
				$track_id     = empty( $track_data['post_id'] ) ? '' : absint( $track_data['post_id'] );

				if ( ! empty( $track_data['title'] ) ) {
					$data['post_title'] = $track_data['title'];
					$data['post_status'] = 'publish';
					$data['post_parent'] = $post_id;
					$data['menu_order'] = $i;
					$data['post_type'] = 'audiotheme_track';

					// Insert or update track.
					if ( empty( $track_id ) ) {
						$track_id = wp_insert_post( $data );
					} else {
						$data['ID']          = $track_id;
						$data['post_author'] = $current_user->ID;
						wp_update_post( $data );
					}

					$i++;
				}

				// Update track artist and file url.
				if ( ! empty( $track_id ) && ! is_wp_error( $track_id ) ) {
					update_post_meta( $track_id, '_audiotheme_artist', $track_data['artist'] );
					update_post_meta( $track_id, '_audiotheme_file_url', $track_data['file_url'] );
				}
			}

			// Update track count.
			audiotheme_record_update_track_count( $post_id );
		}
	}
}
