<?php
/**
 * Edit video archive administration screen functionality.
 *
 * @package AudioTheme\Core\Videos
 * @since 2.0.0
 */

namespace AudioTheme\Core\Admin\Screen;

/**
 * Edit video archive administration screen class.
 *
 * @package AudioTheme\Core\Videos
 * @since 2.0.0
 */
class EditVideoArchive {
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
		add_action( 'add_audiotheme_archive_settings_meta_box_audiotheme_video', '__return_true' );
		add_action( 'save_audiotheme_archive_settings',     array( $this, 'on_save' ), 10, 3 );
		add_action( 'audiotheme_archive_settings_meta_box', array( $this, 'display_orderby_field' ) );
	}

	/**
	 * Save video archive sort order.
	 *
	 * The $post_id and $post parameters will refer to the archive CPT, while
	 * the $post_type parameter references the type of post the archive is for.
	 *
	 * @since 1.4.4
	 *
	 * @param int $post_id Post ID.
	 * @param WP_Post $post Post object.
	 * @param string $post_type The type of post the archive lists.
	 */
	public function on_save( $post_id, $post, $post_type ) {
		if ( 'audiotheme_video' != $post_type ) {
			return;
		}

		$orderby = isset( $_POST['audiotheme_orderby'] ) ? $_POST['audiotheme_orderby'] : '';
		update_post_meta( $post_id, 'orderby', $orderby );
	}

	/**
	 * Add an orderby setting to the video archive.
	 *
	 * Allows for changing the sort order of videos. Custom would require a
	 * plugin like Simple Page Ordering.
	 *
	 * @since 1.4.4
	 *
	 * @param WP_Post $post Post object.
	 */
	public function display_orderby_field( $post ) {
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
}
