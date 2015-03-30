<?php
/**
 * Edit track administration screen functionality.
 *
 * @package AudioTheme\Core\Discography
 * @since 2.0.0
 */

namespace AudioTheme\Core\Provider\Screen;

use AudioTheme\Core\Plugin;

/**
 * Edit track administration screen class.
 *
 * @package AudioTheme\Core\Discography
 * @since 2.0.0
 */
class EditTrack extends AbstractScreen {
	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 *
	 * @param \AudioTheme\Core\Plugin Main plugin instance.
	 */
	public function register_hooks( Plugin $plugin ) {
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

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_assets() {
		wp_enqueue_script( 'audiotheme-media' );
	}

	/**
	 * Register track meta boxes.
	 *
	 * @since 2.0.0
	 *
	 * @param int $post_id Track ID.
	 */
	public function register_meta_boxes( $post ) {
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

		add_meta_box(
			'audiotheme-track-record-info',
			__( 'Record Details', 'audiotheme' ),
			array( $this, 'display_record_info_meta_box' ),
			'audiotheme_track',
			'side',
			'low'
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
		include( $this->plugin->get_path( 'admin/views/meta-box-track-details.php' ) );
	}

	/**
	 * Display record information meta box.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post The track post object being edited.
	 */
	public function display_record_info_meta_box( $post ) {
		$record                  = get_post( $post->post_parent );
		$record_post_type_object = get_post_type_object( 'audiotheme_record' );

		if ( ! $record ) {
			$records = get_posts( 'post_type=audiotheme_record&orderby=title&order=asc&posts_per_page=-1' );
		} else {
			$artist  = get_audiotheme_record_artist( $record->ID );
			$genre   = get_audiotheme_record_genre( $record->ID );
			$release = get_audiotheme_record_release_year( $record->ID );
		}

		include( $this->plugin->get_path( 'admin/views/meta-box-track-record-info.php' ) );
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

		audiotheme_record_update_track_count( $track->post_parent );
	}
}
