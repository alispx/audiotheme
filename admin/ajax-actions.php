<?php
/**
 * Toggle a module's status.
 *
 * @since 2.0.0
 */
function audiotheme_ajax_toggle_module() {
	if ( empty( $_POST['module'] ) ) {
		wp_send_json_error();
	}

	$module_id = $_POST['module'];

	check_ajax_referer( 'toggle-module_' . $module_id, 'nonce' );

	$modules = audiotheme()->modules;
	$module  = $modules->get( $module_id );

	if ( $module->is_core() && $module->is_active() ) {
		$modules->deactivate( $module_id );
	} else {
		$modules->activate( $module_id );
	}

	wp_send_json_success( array(
		'isActive'    => $module->is_active(),
		'adminMenuId' => $module->admin_menu_id,
	) );
}

/**
 * Create a default track for use in the tracklist repeater.
 *
 * @since 1.0.0
 */
function audiotheme_ajax_get_default_track() {
	$is_valid_nonce = ! empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'get-default-track_' . $_POST['record'] );

	if ( empty( $_POST['record'] ) || ! $is_valid_nonce ) {
		wp_send_json_error();
	}

	$data['track'] = get_default_post_to_edit( 'audiotheme_track', true );
	$data['nonce'] = wp_create_nonce( 'get-default-track_' . $_POST['record'] );

	wp_send_json( $data );
}

/**
 * Retrieve a track for use in Cue.
 *
 * @since 1.5.0
 */
function audiotheme_ajax_get_playlist_track() {
	wp_send_json_success( get_cue_playlist_track( $_POST['post_id'] ) );
}

/**
 * Retrieve a collection of tracks for use in Cue.
 *
 * @since 1.5.0
 */
function audiotheme_ajax_get_playlist_tracks() {
	$posts = get_posts( array(
		'post_type'      => 'audiotheme_track',
		'post__in'       => array_filter( (array) $_POST['post__in'] ),
		'posts_per_page' => -1,
	) );

	$tracks = array();
	foreach ( $posts as $post ) {
		$tracks[] = get_audiotheme_playlist_track( $post );
	}

	wp_send_json_success( $tracks );
}

/**
 * Retrieve a list of records and their corresponding tracks for use in Cue.
 *
 * @since 1.5.0
 */
function audiotheme_ajax_get_playlist_records() {
	global $wpdb;

	$data           = array();
	$page           = isset( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;
	$posts_per_page = isset( $_POST['posts_per_page'] ) ? absint( $_POST['posts_per_page'] ) : 2;

	$records = new WP_Query( array(
		'post_type'      => 'audiotheme_record',
		'post_status'    => 'publish',
		'posts_per_page' => $posts_per_page,
		'paged'          => $page,
		'orderby'        => 'title',
		'order'          => 'ASC',
	) );

	if ( $records->have_posts() ) {
		foreach ( $records->posts as $record ) {
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $record->ID ), array( 120, 120 ) );

			$data[ $record->ID ] = array(
				'id'        => $record->ID,
				'title'     => $record->post_title,
				'artist'    => get_audiotheme_record_artist( $record->ID ),
				'release'   => get_audiotheme_record_release_year( $record->ID ),
				'thumbnail' => $image[0],
				'tracks'    => array(),
			);
		}

		$tracks = $wpdb->get_results( "SELECT p.ID, p.post_title, p2.ID AS record_id
			FROM $wpdb->posts p
			INNER JOIN $wpdb->posts p2 ON p.post_parent=p2.ID
			WHERE p.post_type='audiotheme_track' AND p.post_status='publish'
			ORDER BY p.menu_order ASC" );

		if ( $tracks ) {
			foreach ( $tracks as $track ) {
				if ( ! isset( $data[ $track->record_id ] ) ) {
					continue;
				}

				$data[ $track->record_id ]['tracks'][] = array(
					'id'    => $track->ID,
					'title' => $track->post_title,
				);
			}
		}

		// Remove records that don't have any tracks.
		foreach ( $data as $key => $item ) {
			if ( empty( $item['tracks'] ) ) {
				unset( $data[ $key ] );
			}
		}
	}

	$send['maxNumPages'] = $records->max_num_pages;
	$send['records']     = array_values( $data );

	wp_send_json_success( $send );
}

/**
 * Search for venues that begin with a string.
 *
 * @since 1.0.0
 */
function audiotheme_ajax_get_venue_matches() {
	global $wpdb;

	$var    = $wpdb->esc_like( stripslashes( $_GET['term'] ) ) . '%';
	$sql    = $wpdb->prepare( "SELECT post_title FROM $wpdb->posts WHERE post_type='audiotheme_venue' AND post_title LIKE %s ORDER BY post_title ASC", $var );
	$venues = $wpdb->get_col( $sql );

	wp_send_json( $venues );
}

/**
 * Check for an existing venue with the same name.
 *
 * @since 1.0.0
 */
function audiotheme_ajax_is_new_venue() {
	global $wpdb;

	$sql   = $wpdb->prepare( "SELECT post_title FROM $wpdb->posts WHERE post_type='audiotheme_venue' AND post_title=%s ORDER BY post_title ASC LIMIT 1", stripslashes( $_GET['name'] ) );
	$venue = $wpdb->get_col( $sql );

	wp_send_json_success( empty( $venue ) );
}