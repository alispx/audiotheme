<?php
/**
 * Edit record archive administration screen functionality.
 *
 * @package AudioTheme\Core\Discography
 * @since 2.0.0
 */

namespace AudioTheme\Core\Provider\Screen;

use AudioTheme\Core\Plugin;

/**
 * Edit record archive administration screen class.
 *
 * @package AudioTheme\Core\Discography
 * @since 2.0.0
 */
class EditRecordArchive extends AbstractScreen {
	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 *
	 * @param \AudioTheme\Core\Plugin Main plugin instance.
	 */
	public function register_hooks( Plugin $plugin ) {
		add_action( 'add_audiotheme_archive_settings_meta_box_audiotheme_record', '__return_true' );
		add_action( 'save_audiotheme_archive_settings',     array( $this, 'on_save' ), 10, 3 );
		add_action( 'audiotheme_archive_settings_meta_box', array( $this, 'display_orderby_field' ) );
	}

	/**
	 * Save record archive sort order.
	 *
	 * The $post_id and $post parameters will refer to the archive CPT, while
	 * the $post_type parameter references the type of post the archive is for.
	 *
	 * @since 1.3.0
	 *
	 * @param int $post_id Post ID.
	 * @param WP_Post $post Post object.
	 * @param string $post_type The type of post the archive lists.
	 */
	public function on_save( $post_id, $post, $post_type ) {
		if ( 'audiotheme_record' != $post_type ) {
			return;
		}

		$orderby = ( isset( $_POST['audiotheme_orderby'] ) ) ? $_POST['audiotheme_orderby'] : '';
		update_post_meta( $post_id, 'orderby', $orderby );
	}

	/**
	 * Add an orderby setting to the record archive.
	 *
	 * Allows for changing the sort order of records. Custom would require a
	 * plugin like Simple Page Ordering.
	 *
	 * @since 1.3.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function display_orderby_field( $post ) {
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
}
