<?php
/**
 * Administration functionality for the Discography module.
 *
 * @package AudioTheme\Discography
 * @since 2.0.0
 */

namespace AudioTheme\Admin;

/**
 * Discography module administration class.
 *
 * @package AudioTheme\Discography
 * @since 2.0.0
 */
class Discography {
	/**
	 * Attach hooks for loading discography administration.
	 *
	 * @since 1.0.0
	 */
	public function load() {
		$this->register_hooks();
		$this->register_ajax_actions();
	}

	/**
	 * Register admin hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'admin_menu',            array( $this, 'add_menu_item' ) );
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );

		// Record archive.
		add_action( 'add_audiotheme_archive_settings_meta_box_audiotheme_record', '__return_true' );
		add_action( 'save_audiotheme_archive_settings',     array( $this, 'on_record_archive_save' ), 10, 3 );
		add_action( 'audiotheme_archive_settings_meta_box', array( $this, 'record_archive_settings' ) );

		// Playlists. Extends the Cue plugin.
		add_filter( 'cue_playlist_args',      array( $this, 'playlist_post_type_args' ) );
		add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue_playlist_assets' ) );
		add_action( 'print_media_templates',  array( $this, 'print_playlist_templates' ) );
	}

	/**
	 * Register AJAX callbacks.
	 *
	 * @since 2.0.0
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_audiotheme_ajax_get_default_track',    'audiotheme_ajax_get_default_track' );
		add_action( 'wp_ajax_audiotheme_ajax_get_playlist_track',   'audiotheme_ajax_get_playlist_track' );
		add_action( 'wp_ajax_audiotheme_ajax_get_playlist_tracks',  'audiotheme_ajax_get_playlist_tracks' );
		add_action( 'wp_ajax_audiotheme_ajax_get_playlist_records', 'audiotheme_ajax_get_playlist_records' );
	}

	/**
	 * Discography admin menu.
	 *
	 * @since 2.0.0
	 */
	public function add_menu_item() {
		add_menu_page(
			__( 'Discography', 'audiotheme' ),
			__( 'Discography', 'audiotheme' ),
			'edit_posts',
			'edit.php?post_type=audiotheme_record',
			null,
			audiotheme_encode_svg( 'admin/images/dashicons/discography.svg' ),
			513
		);
	}

	/**
	 * Save record archive sort order.
	 *
	 * The $post_id and $post parameters will refer to the archive CPT, while the
	 * $post_type parameter references the type of post the archive is for.
	 *
	 * @since 1.3.0
	 *
	 * @param int $post_id Post ID.
	 * @param WP_Post $post Post object.
	 * @param string $post_type The type of post the archive lists.
	 */
	public function on_record_archive_save( $post_id, $post, $post_type ) {
		if ( 'audiotheme_record' != $post_type ) {
			return;
		}

		$orderby = ( isset( $_POST['audiotheme_orderby'] ) ) ? $_POST['audiotheme_orderby'] : '';
		update_post_meta( $post_id, 'orderby', $orderby );
	}

	/**
	 * Add an orderby setting to the record archive.
	 *
	 * Allows for changing the sort order of records. Custom would require a plugin
	 * like Simple Page Ordering.
	 *
	 * @since 1.3.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function record_archive_settings( $post ) {
		$post_type = is_audiotheme_post_type_archive_id( $post->ID );
		if ( 'audiotheme_record' != $post_type ) {
			return;
		}

		$options = apply_filters( 'audiotheme_record_archive_orderby_choices', array(
			'release_year' => __( 'Release Year', 'audiotheme' ),
			'title'        => __( 'Title', 'audiotheme' ),
			'custom'       => __( 'Custom', 'audiotheme' ),
		) );

		$orderby = get_audiotheme_archive_meta( 'orderby', true, 'release_year', 'audiotheme_record' );
		?>
		<p>
			<label for="audiotheme-orderby"><?php _e( 'Order by:', 'audiotheme' ); ?></label>
			<select name="audiotheme_orderby" id="audiotheme-orderby">
				<?php
				foreach ( $options as $id => $value ) {
					printf( '<option value="%s"%s>%s</option>',
						esc_attr( $id ),
						selected( $id, $orderby, false ),
						esc_html( $value )
					);
				}
				?>
			</select>
		</p>
		<?php
	}

	/**
	 * Move the playlist menu item under discography.
	 *
	 * @since 1.5.0
	 *
	 * @param array $args Post type registration args.
	 * @return array
	 */
	public function playlist_post_type_args( $args ) {
		$args['show_in_menu'] = 'edit.php?post_type=audiotheme_record';
		return $args;
	}

	/**
	 * Enqueue playlist scripts and styles.
	 *
	 * @since 1.5.0
	 */
	public function enqueue_playlist_assets() {
		if ( 'cue_playlist' !== get_post_type() ) {
			return;
		}

		wp_enqueue_style( 'audiotheme-playlist-admin', AUDIOTHEME_URI . 'admin/css/playlist.css' );

		wp_enqueue_script(
			'audiotheme-playlist-admin',
			AUDIOTHEME_URI . 'admin/js/playlist.js',
			array( 'cue-admin' ),
			'1.0.0',
			true
		);

		wp_localize_script( 'audiotheme-playlist-admin', '_audiothemePlaylistSettings', array(
			'l10n' => array(
				'frameTitle'        => __( 'AudioTheme Tracks', 'audiotheme' ),
				'frameMenuItemText' => __( 'Add from AudioTheme', 'audiotheme' ),
				'frameButtonText'   => __( 'Add Tracks', 'audiotheme' ),
			),
		) );
	}

	/**
	 * Print playlist JavaScript templates.
	 *
	 * @since 1.5.0
	 */
	public function print_playlist_templates() {
		include( AUDIOTHEME_DIR . 'admin/views/templates-playlist.php' );
	}

	/**
	 * Discography update messages.
	 *
	 * @since 2.0.0
	 * @see /wp-admin/edit-form-advanced.php
	 *
	 * @param array $messages The array of existing post update messages.
	 * @return array
	 */
	public function post_updated_messages( $messages ) {
		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		if ( 'audiotheme_record' == $post_type ) {
			$messages['audiotheme_record'] = array(
				0  => '', // Unused. Messages start at index 1.
				1  => __( 'Record updated.', 'audiotheme' ),
				2  => __( 'Custom field updated.', 'audiotheme' ),
				3  => __( 'Custom field deleted.', 'audiotheme' ),
				4  => __( 'Record updated.', 'audiotheme' ),
				/* translators: %s: date and time of the revision */
				5  => isset( $_GET['revision'] ) ? sprintf( __( 'Record restored to revision from %s', 'audiotheme' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6  => __( 'Record published.', 'audiotheme' ),
				7  => __( 'Record saved.', 'audiotheme' ),
				8  => __( 'Record submitted.', 'audiotheme' ),
				9  => sprintf( __( 'Record scheduled for: <strong>%1$s</strong>.', 'audiotheme' ),
					  // translators: Publish box date format, see http://php.net/date
					  date_i18n( __( 'M j, Y @ G:i', 'audiotheme' ), strtotime( $post->post_date ) ) ),
				10 => __( 'Record draft updated.', 'audiotheme' ),
			);

			if ( $post_type_object->publicly_queryable ) {
				$permalink = get_permalink( $post->ID );
				$preview_permalink = add_query_arg( 'preview', 'true', $permalink );

				$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View record', 'audiotheme' ) );
				$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview record', 'audiotheme' ) );

				$messages[ $post_type ][1]  .= $view_link;
				$messages[ $post_type ][6]  .= $view_link;
				$messages[ $post_type ][9]  .= $view_link;
				$messages[ $post_type ][8]  .= $preview_link;
				$messages[ $post_type ][10] .= $preview_link;
			}
		}

		if ( 'audiotheme_track' == $post_type ) {
			$messages['audiotheme_track'] = array(
				0  => '', // Unused. Messages start at index 1.
				1  => __( 'Track updated.', 'audiotheme' ),
				2  => __( 'Custom field updated.', 'audiotheme' ),
				3  => __( 'Custom field deleted.', 'audiotheme' ),
				4  => __( 'Track updated.', 'audiotheme' ),
				/* translators: %s: date and time of the revision */
				5  => isset( $_GET['revision'] ) ? sprintf( __( 'Track restored to revision from %s', 'audiotheme' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6  => __( 'Track published.', 'audiotheme' ),
				7  => __( 'Track saved.', 'audiotheme' ),
				8  => __( 'Track submitted.', 'audiotheme' ),
				9  => sprintf( __( 'Track scheduled for: <strong>%1$s</strong>.', 'audiotheme' ),
					  // translators: Publish box date format, see http://php.net/date
					  date_i18n( __( 'M j, Y @ G:i', 'audiotheme' ), strtotime( $post->post_date ) ) ),
				10 => __( 'Track draft updated.', 'audiotheme' ),
			);

			if ( $post_type_object->publicly_queryable ) {
				$permalink = get_permalink( $post->ID );
				$preview_permalink = add_query_arg( 'preview', 'true', $permalink );

				$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View track', 'audiotheme' ) );
				$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview track', 'audiotheme' ) );

				$messages[ $post_type ][1]  .= $view_link;
				$messages[ $post_type ][6]  .= $view_link;
				$messages[ $post_type ][9]  .= $view_link;
				$messages[ $post_type ][8]  .= $preview_link;
				$messages[ $post_type ][10] .= $preview_link;
			}
		}

		return $messages;
	}
}
