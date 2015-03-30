<?php
/**
 * Admin-functionality for the video module.
 *
 * @package AudioTheme\Core\Videos
 * @since 2.0.0
 */

namespace AudioTheme\Core\Admin;

use AudioTheme\Core\Util;

/**
 * Video module admin class.
 *
 * @package AudioTheme\Core\Videos
 * @since 2.0.0
 */
class Videos {
	/**
	 * Attach hooks for loading and managing videos in the admin dashboard.
	 *
	 * @since 1.0.0
	 */
	public function load() {
		$this->register_hooks();
	}

	/**
	 * Register admin hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'admin_enqueue_scripts',                array( $this, 'register_assets' ), 1 );
		add_action( 'admin_enqueue_scripts',                array( $this, 'enqueue_assets' ) );
		add_filter( 'manage_edit-audiotheme_video_columns', array( $this, 'register_columns' ) );
		add_action( 'save_post',                            array( $this, 'on_save_video' ), 10, 2 );
		add_action( 'add_meta_boxes_audiotheme_video',      array( $this, 'register_meta_boxes' ) );
		add_filter( 'admin_post_thumbnail_html',            array( $this, 'post_thumbnail_html' ), 10, 2 );
		add_filter( 'post_updated_messages',                array( $this, 'post_updated_messages' ) );

		// Videos archive.
		add_action( 'add_audiotheme_archive_settings_meta_box_audiotheme_video', '__return_true' );
		add_action( 'save_audiotheme_archive_settings',     array( $this, 'on_save_archive_settings' ), 10, 3 );
		add_action( 'audiotheme_archive_settings_meta_box', array( $this, 'archive_settings' ) );
	}

	/**
	 * Register scripts and styles.
	 *
	 * @since 2.0.0
	 */
	public function register_assets() {
		wp_register_script(
			'audiotheme-video-edit',
			AUDIOTHEME_URI . 'admin/js/video-edit.js',
			array( 'jquery', 'post', 'wp-backbone', 'wp-util' ),
			'2.0.0',
			true
		);
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_assets() {
		$screen = get_current_screen();
		if ( 'post' != $screen->base || 'audiotheme_video' != $screen->post_type ) {
			return;
		}

		wp_enqueue_script( 'audiotheme-video-edit' );
	}

	/**
	 * Register video columns.
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns An array of the column names to display.
	 * @return array The filtered array of column names.
	 */
	public function register_columns( $columns ) {
		// Register an image column and insert it after the checkbox column.
		$image_column = array( 'audiotheme_image' => _x( 'Image', 'column name', 'audiotheme' ) );
		$columns      = Util::array_insert_after_key( $columns, 'cb', $image_column );
		return $columns;
	}

	/**
	 * Register video meta boxes.
	 *
	 * @since 1.0.0
	 */
	public function register_meta_boxes() {
		add_action( 'edit_form_after_title', array( $this, 'display_video_url_field' ) );
	}

	/**
	 * Display a field to enter a video URL after the post title.
	 *
	 * @since 1.0.0
	 */
	public function display_video_url_field( $post ) {
		$video = get_audiotheme_video_url( $post->ID );
		wp_nonce_field( 'save-video-meta_' . $post->ID, 'audiotheme_save_video_meta_nonce', false );
		?>
		<div class="audiotheme-edit-after-title" style="position: relative">
			<p>
				<label for="audiotheme-video-url" class="screen-reader-text"><?php _e( 'Video URL:', 'audiotheme' ); ?></label>
				<input type="text" name="_video_url" id="audiotheme-video-url" value="<?php echo esc_url( $video ); ?>" placeholder="<?php esc_attr_e( 'Video URL', 'audiotheme' ); ?>" class="widefat"><br>

				<span class="description">
					<?php
					printf( __( 'Enter a video URL from one of the %s.', 'audiotheme' ),
						'<a href="http://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F" target="_blank">' . __( 'supported video services', 'audiotheme' ) . '</a>'
					);
					?>
				</span>
			</p>
		</div>
		<?php
	}

	/**
	 * Add a link to get the video thumbnail from an oEmbed endpoint.
	 *
	 * Adds data about the current thumbnail and a previously fetched thumbnail
	 * from an oEmbed endpoint so the link can be hidden or shown as necessary. A
	 * function is also fired each time the HTML is output in order to determine
	 * whether the link should be displayed.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content Default post thumbnail HTML.
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public function post_thumbnail_html( $content, $post_id ) {
		if ( 'audiotheme_video' != get_post_type( $post_id ) ) {
			return $content;
		}

		$data = array(
			'thumbnailId'       => get_post_thumbnail_id( $post_id ),
			'oembedThumbnailId' => get_post_meta( $post_id, '_audiotheme_oembed_thumbnail_id', true ),
		);

		ob_start();
		?>
		<p id="audiotheme-select-oembed-thumb" class="hide-if-no-js">
			<a href="#" id="audiotheme-select-oembed-thumb-button"><?php _e( 'Get video thumbnail', 'audiotheme' ); ?></a>
			<?php audiotheme_admin_spinner(); ?>
		</p>
		<script id="audiotheme-video-thumbnail-data" type="application/json"><?php echo json_encode( $data ); ?></script>
		<script>if ( '_audiothemeVideoThumbnailPing' in window ) { _audiothemeVideoThumbnailPing(); }</script>
		<?php
		$content .= ob_get_clean();

		return $content;
	}

	/**
	 * Save custom video data.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id The ID of the post.
	 * @param object $post The post object.
	 */
	public function on_save_video( $post_id, $post ) {
		$is_autosave    = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
		$is_revision    = wp_is_post_revision( $post_id );
		$is_valid_nonce = isset( $_POST['audiotheme_save_video_meta_nonce'] ) && wp_verify_nonce( $_POST['audiotheme_save_video_meta_nonce'], 'save-video-meta_' . $post_id );

		// Bail if the data shouldn't be saved or intention can't be verified.
		if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
			return;
		}

		if ( isset( $_POST['_video_url'] ) ) {
			update_post_meta( $post_id, '_audiotheme_video_url', esc_url_raw( $_POST['_video_url'] ) );
		}
	}

	/**
	 * Save video archive sort order.
	 *
	 * The $post_id and $post parameters will refer to the archive CPT, while the
	 * $post_type parameter references the type of post the archive is for.
	 *
	 * @since 1.4.4
	 *
	 * @param int $post_id Post ID.
	 * @param WP_Post $post Post object.
	 * @param string $post_type The type of post the archive lists.
	 */
	public function on_save_archive_settings( $post_id, $post, $post_type ) {
		if ( 'audiotheme_video' != $post_type ) {
			return;
		}

		$orderby = isset( $_POST['audiotheme_orderby'] ) ? $_POST['audiotheme_orderby'] : '';
		update_post_meta( $post_id, 'orderby', $orderby );
	}

	/**
	 * Add an orderby setting to the video archive.
	 *
	 * Allows for changing the sort order of videos. Custom would require a plugin
	 * like Simple Page Ordering.
	 *
	 * @since 1.4.4
	 *
	 * @param WP_Post $post Post object.
	 */
	public function archive_settings( $post ) {
		$post_type = is_audiotheme_post_type_archive_id( $post->ID );
		if ( 'audiotheme_video' != $post_type ) {
			return;
		}

		$options = array(
			'post_date' => __( 'Publish Date', 'audiotheme' ),
			'title'     => __( 'Title', 'audiotheme' ),
			'custom'    => __( 'Custom', 'audiotheme' ),
		);

		$orderby = get_audiotheme_archive_meta( 'orderby', true, 'post_date', 'audiotheme_video' );
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
	 * Video update messages.
	 *
	 * @since 1.0.0
	 *
	 * @see /wp-admin/edit-form-advanced.php
	 *
	 * @param array $messages The array of existing post update messages.
	 * @return array
	 */
	public function post_updated_messages( $messages ) {
		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		if ( 'audiotheme_video' !== $post_type ) {
			return $messages;
		}

		$messages[ $post_type ] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Video updated.', 'audiotheme' ),
			2  => __( 'Custom field updated.', 'audiotheme' ),
			3  => __( 'Custom field deleted.', 'audiotheme' ),
			4  => __( 'Video updated.', 'audiotheme' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Video restored to revision from %s', 'audiotheme' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Video published.', 'audiotheme' ),
			7  => __( 'Video saved.', 'audiotheme' ),
			8  => __( 'Video submitted.', 'audiotheme' ),
			9  => sprintf( __( 'Video scheduled for: <strong>%1$s</strong>.', 'audiotheme' ),
				  // translators: Publish box date format, see http://php.net/date
				  date_i18n( __( 'M j, Y @ G:i', 'audiotheme' ), strtotime( $post->post_date ) ) ),
			10 => __( 'Video draft updated.', 'audiotheme' ),
		);

		if ( $post_type_object->publicly_queryable ) {
			$permalink = get_permalink( $post->ID );
			$preview_permalink = add_query_arg( 'preview', 'true', $permalink );

			$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View video', 'audiotheme' ) );
			$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview video', 'audiotheme' ) );

			$messages[ $post_type ][1]  .= $view_link;
			$messages[ $post_type ][6]  .= $view_link;
			$messages[ $post_type ][9]  .= $view_link;
			$messages[ $post_type ][8]  .= $preview_link;
			$messages[ $post_type ][10] .= $preview_link;
		}

		return $messages;
	}
}
