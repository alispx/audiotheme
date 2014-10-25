<?php
/**
 * Manage records administration screen functionality.
 *
 * @package AudioTheme\Discography
 * @since 2.0.0
 */

/**
 * Manage records administration screen class.
 *
 * @package AudioTheme\Discography
 * @since 2.0.0
 */
class AudioTheme_Admin_Screen_ManageRecords {
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
		add_filter( 'parse_query',                                    array( $this, 'admin_query' ) );
		add_filter( 'manage_edit-audiotheme_record_columns',          array( $this, 'register_columns' ) );
		add_action( 'manage_edit-audiotheme_record_sortable_columns', array( $this, 'register_sortable_columns' ) );
		add_action( 'manage_pages_custom_column',                     array( $this, 'display_columns' ), 10, 2 );
		add_filter( 'bulk_actions-edit-audiotheme_record',            array( $this, 'list_table_bulk_actions' ) );
		add_action( 'page_row_actions',                               array( $this, 'list_table_actions' ), 10, 2 );
	}

	/**
	 * Custom sort records on the Manage Records screen.
	 *
	 * @since 1.0.0
	 *
	 * @param object $wp_query The main WP_Query object. Passed by reference.
	 */
	public function admin_query( $wp_query ) {
		if ( is_admin() && isset( $_GET['post_type'] ) && 'audiotheme_record' == $_GET['post_type'] ) {
			$sortable_keys = array( 'artist', 'release_year', 'tracks' );
			if ( ! empty( $_GET['orderby'] ) && in_array( $_GET['orderby'], $sortable_keys ) ) {
				switch ( $_GET['orderby'] ) {
					case 'release_year' :
						$meta_key = '_audiotheme_release_year';
						$orderby = 'meta_value_num';
						break;
					case 'tracks' :
						$meta_key = '_audiotheme_track_count';
						$orderby = 'meta_value_num';
						break;
				}

				$order = ( isset( $_GET['order'] ) && 'desc' == $_GET['order'] ) ? 'desc' : 'asc';
				$orderby = ( empty( $orderby ) ) ? 'meta_value' : $orderby;

				$wp_query->set( 'meta_key', $meta_key );
				$wp_query->set( 'orderby', $orderby );
				$wp_query->set( 'order', $order );
			}
		}
	}

	/**
	 * Register record columns.
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns An array of the column names to display.
	 * @return array Filtered array of column names.
	 */
	public function register_columns( $columns ) {
		$columns['title'] = _x( 'Record', 'column_name', 'audiotheme' );

		// Create columns and insert them in the appropriate position in the columns array.
		$image_column = array( 'audiotheme_image' => _x( 'Image', 'column name', 'audiotheme' ) );
		$release_column = array( 'release_year' => _x( 'Released', 'column_name', 'audiotheme' ) );
		$columns = audiotheme_array_insert_after_key( $columns, 'cb', $image_column );
		$columns = audiotheme_array_insert_after_key( $columns, 'title', $release_column );

		$columns['taxonomy-audiotheme_record_type'] = _x( 'Type', 'column name', 'audiotheme' );
		$columns['track_count'] = _x( 'Tracks', 'column name', 'audiotheme' );

		unset( $columns['date'] );

		return $columns;
	}

	/**
	 * Register sortable record columns.
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns Column query vars with their corresponding column id as the key.
	 * @return array
	 */
	public function register_sortable_columns( $columns ) {
		$columns['release_year'] = 'release_year';
		$columns['track_count']  = 'tracks';
		return $columns;
	}

	/**
	 * Display custom record columns.
	 *
	 * @since 1.0.0
	 *
	 * @param string $column_id The id of the column to display.
	 * @param int $post_id Post ID.
	 */
	public function display_columns( $column_name, $post_id ) {
		global $post;

		switch ( $column_name ) {
			case 'release_year' :
				echo get_audiotheme_record_release_year( $post_id );
				break;

			case 'track_count' :
				$args = array(
					'post_type' => 'audiotheme_track',
					'post_parent' => $post_id,
				);

				printf( '<a href="%s">%s</a>',
					add_query_arg( $args, esc_url( admin_url( 'edit.php' ) ) ),
					get_post_meta( $post_id, '_audiotheme_track_count', true )
				);
				break;
		}
	}

	/**
	 * Remove quick edit from the record list table.
	 *
	 * @since 1.0.0
	 *
	 * @param array $actions List of actions.
	 * @param WP_Post $post A post.
	 * @return array
	 */
	public function list_table_actions( $actions, $post ) {
		if ( 'audiotheme_record' == get_post_type( $post ) ) {
			unset( $actions['inline hide-if-no-js'] );
		}
		return $actions;
	}

	/**
	 * Remove bulk edit from the record list table.
	 *
	 * @since 1.0.0
	 *
	 * @param array $actions List of actions.
	 * @return array
	 */
	public function list_table_bulk_actions( $actions ) {
		unset( $actions['edit'] );
		return $actions;
	}
}
