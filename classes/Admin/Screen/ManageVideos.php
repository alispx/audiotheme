<?php
/**
 * Manage videos administration screen functionality.
 *
 * @package AudioTheme\Core\Videos
 * @since 2.0.0
 */

namespace AudioTheme\Core\Admin\Screen;

use AudioTheme\Core\Util;

/**
 * Manage videos administration screen class.
 *
 * @package AudioTheme\Core\Videos
 * @since 2.0.0
 */
class ManageVideos {
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
		add_filter( 'manage_edit-audiotheme_video_columns', array( $this, 'register_columns' ) );
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
}
