<?php
/**
 * Discography-related admin functionality.
 *
 * @package AudioTheme
 * @subpackage Discography
 * @since 1.0.0
 */

/**
 * Include discography admin dependencies.
 */
require( AUDIOTHEME_DIR . 'modules/discography/admin/ajax.php' );
require( AUDIOTHEME_DIR . 'modules/discography/admin/playlist.php' );
require( AUDIOTHEME_DIR . 'modules/discography/admin/record.php' );
require( AUDIOTHEME_DIR . 'modules/discography/admin/track.php' );

/**
 * Attach hooks for loading and managing discography in the admin dashboard.
 *
 * @since 1.0.0
 */
function audiotheme_load_discography_admin() {
	// Register AJAX admin actions.
	add_action( 'wp_ajax_audiotheme_ajax_get_default_track', 'audiotheme_ajax_get_default_track' );
	add_action( 'wp_ajax_audiotheme_ajax_get_playlist_track', 'audiotheme_ajax_get_playlist_track' );
	add_action( 'wp_ajax_audiotheme_ajax_get_playlist_tracks', 'audiotheme_ajax_get_playlist_tracks' );
	add_action( 'wp_ajax_audiotheme_ajax_get_playlist_records', 'audiotheme_ajax_get_playlist_records' );

	// Set up the record type taxonomy.
	add_action( 'admin_init', 'audiotheme_discography_admin_setup' );

	add_action( 'admin_menu', 'audiotheme_discography_admin_menu' );
	add_filter( 'post_updated_messages', 'audiotheme_discography_post_updated_messages' );

	// Playlists
	add_filter( 'cue_playlist_args', 'audiotheme_playlist_args' );
	add_action( 'admin_enqueue_scripts', 'audiotheme_playlist_admin_enqueue_scripts' );
	add_action( 'print_media_templates', 'audiotheme_playlist_print_templates' );

	// Records
	add_action( 'save_post', 'audiotheme_record_save_post' );

	// Manage Records screen.
	add_filter( 'parse_query', 'audiotheme_records_admin_query' );
	add_action( 'load-edit.php', 'audiotheme_record_list_help' );
	add_action( 'load-post.php', 'audiotheme_record_help' );
	add_action( 'load-post-new.php', 'audiotheme_record_help' );
	add_filter( 'manage_edit-audiotheme_record_columns', 'audiotheme_record_register_columns' );
	add_action( 'manage_edit-audiotheme_record_sortable_columns', 'audiotheme_record_register_sortable_columns' );
	add_action( 'manage_pages_custom_column', 'audiotheme_record_display_columns', 10, 2 );
	add_filter( 'bulk_actions-edit-audiotheme_record', 'audiotheme_record_list_table_bulk_actions' );
	add_action( 'page_row_actions', 'audiotheme_record_list_table_actions', 10, 2 );

	// Edit Record screen.
	add_action( 'add_meta_boxes_audiotheme_record', 'audiotheme_edit_record_meta_boxes' );
	add_action( 'audiotheme_record_details_meta_box', 'audiotheme_record_details_field_released' );
	add_action( 'audiotheme_record_details_meta_box', 'audiotheme_record_details_field_artist', 20 );
	add_action( 'audiotheme_record_details_meta_box', 'audiotheme_record_details_field_genre', 30 );
	add_action( 'audiotheme_record_details_meta_box', 'audiotheme_record_details_field_types', 40 );
	add_action( 'audiotheme_record_details_meta_box', 'audiotheme_record_details_field_links', 50 );

	// Tracks
	add_action( 'save_post', 'audiotheme_track_save_post' );

	// Manage Tracks screen.
	add_filter( 'parse_query', 'audiotheme_tracks_admin_query' );
	add_action( 'load-edit.php', 'audiotheme_track_list_help' );
	add_action( 'load-post.php', 'audiotheme_track_help' );
	add_action( 'load-post-new.php', 'audiotheme_track_help' );
	add_action( 'restrict_manage_posts', 'audiotheme_tracks_filters' );
	add_filter( 'manage_edit-audiotheme_track_columns', 'audiotheme_track_register_columns' );
	add_action( 'manage_edit-audiotheme_track_sortable_columns', 'audiotheme_track_register_sortable_columns' );
	add_action( 'manage_posts_custom_column', 'audiotheme_track_display_columns', 10, 2 );
	add_filter( 'bulk_actions-edit-audiotheme_track', 'audiotheme_track_list_table_bulk_actions' );
	add_action( 'post_row_actions', 'audiotheme_track_list_table_actions', 10, 2 );

	// Edit Track screen.
	add_action( 'add_meta_boxes_audiotheme_track', 'audiotheme_edit_track_meta_boxes' );

	// Record Archive
	add_action( 'add_audiotheme_archive_settings_meta_box_audiotheme_record', '__return_true' );
	add_action( 'save_audiotheme_archive_settings', 'audiotheme_record_archive_save_settings_hook', 10, 3 );
	add_action( 'audiotheme_archive_settings_meta_box', 'audiotheme_record_archive_settings' );
}

/**
 * Add initial discography data.
 *
 * Ensures the record type taxonomies exist. Runs anytime themes.php is
 * visited to ensure record types exist.
 *
 * @since 1.0.0
 */
function audiotheme_discography_admin_setup() {
	if ( taxonomy_exists( 'audiotheme_record_type' ) ) {
		$record_types = get_audiotheme_record_type_slugs();
		if ( $record_types ) {
			foreach( $record_types as $type_slug ) {
				if ( ! term_exists( $type_slug, 'audiotheme_record_type' ) ) {
					wp_insert_term( $type_slug, 'audiotheme_record_type', array( 'slug' => $type_slug ) );
				}
			}
		}
	}
}

/**
 * Discography admin menu.
 *
 * @since 1.0.0
 */
function audiotheme_discography_admin_menu() {
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
 * Discography update messages.
 *
 * @since 1.0.0
 * @see /wp-admin/edit-form-advanced.php
 *
 * @param array $messages The array of existing post update messages.
 * @return array
 */
function audiotheme_discography_post_updated_messages( $messages ) {
	global $post;

	$messages['audiotheme_record'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => sprintf( __( 'Record updated. <a href="%s">View Record</a>', 'audiotheme' ), esc_url( get_permalink( $post->ID ) ) ),
		2  => __( 'Custom field updated.', 'audiotheme' ),
		3  => __( 'Custom field deleted.', 'audiotheme' ),
		4  => __( 'Record updated.', 'audiotheme' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Record restored to revision from %s', 'audiotheme' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => sprintf( __( 'Record published. <a href="%s">View Record</a>', 'audiotheme' ), esc_url( get_permalink( $post->ID ) ) ),
		7  => __( 'Record saved.', 'audiotheme' ),
		8  => sprintf( __( 'Record submitted. <a target="_blank" href="%s">Preview Record</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
		9  => sprintf( __( 'Record scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Record</a>', 'audiotheme' ),
		      /* translators: Publish box date format, see http://php.net/date */
		      date_i18n( __( 'M j, Y @ G:i', 'audiotheme' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post->ID ) ) ),
		10 => sprintf( __( 'Record draft updated. <a target="_blank" href="%s">Preview Record</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
	);

	$messages['audiotheme_track'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => sprintf( __( 'Track updated. <a href="%s">View Track</a>', 'audiotheme' ), esc_url( get_permalink( $post->ID ) ) ),
		2  => __( 'Custom field updated.', 'audiotheme' ),
		3  => __( 'Custom field deleted.', 'audiotheme' ),
		4  => __( 'Track updated.', 'audiotheme' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Track restored to revision from %s', 'audiotheme' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => sprintf( __( 'Track published. <a href="%s">View Track</a>', 'audiotheme' ), esc_url( get_permalink( $post->ID ) ) ),
		7  => __( 'Track saved.', 'audiotheme' ),
		8  => sprintf( __( 'Track submitted. <a target="_blank" href="%s">Preview Track</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
		9  => sprintf( __( 'Track scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Track</a>', 'audiotheme' ),
		      /* translators: Publish box date format, see http://php.net/date */
		      date_i18n( __( 'M j, Y @ G:i', 'audiotheme' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post->ID ) ) ),
		10 => sprintf( __( 'Track draft updated. <a target="_blank" href="%s">Preview Track</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
	);

	return $messages;
}
