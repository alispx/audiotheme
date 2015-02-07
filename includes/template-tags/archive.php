<?php
/**
 * Post type archives admin functionality.
 *
 * This method allows for archive titles, descriptions, and even post type
 * slugs to be easily changed via a familiar interface. It also allows
 * archives to be easily added to nav menus without using a custom link.
 *
 * @package AudioTheme\Template
 * @since 1.0.0
 */

/**
 * Get archive post IDs.
 *
 * @since 1.0.0
 *
 * @return array Associative array with post types as keys and post IDs as the values.
 */
function get_audiotheme_archive_ids() {
	return audiotheme( 'archives' )->get_archive_ids();
}

/**
 * Get the archive post ID for a particular post type.
 *
 * @since 1.0.0
 *
 * @param string $post_type_name Optional. Post type name
 * @return int
 */
function get_audiotheme_post_type_archive( $post_type = null ) {
	return audiotheme( 'archives' )->get_archive_id( $post_type );
}

/**
 * Determine if the current template is a post type archive.
 *
 * @since 1.0.0
 *
 * @return bool
 */
function is_audiotheme_post_type_archive() {
	return ( is_post_type_archive( array( 'audiotheme_gig', 'audiotheme_record', 'audiotheme_video' ) ) );
}

/**
 * Determine if a post ID is for a post type archive post.
 *
 * @since 1.0.0
 *
 * @param int $archive_id Post ID.
 * @return string|bool Post type name if true, otherwise false.
 */
function is_audiotheme_post_type_archive_id( $archive_id ) {
	return audiotheme( 'archives' )->is_archive_id( $archive_id );
}

/**
 * Retrieve the title for a post type archive.
 *
 * @since x.x.x
 *
 * @param string $post_type Optional. Post type name. Defaults to the current post type.
 * @param string $title Optional. Fallback title.
 * @return string
 */
function get_audiotheme_post_type_archive_title( $post_type = '', $title = '' ) {
	return audiotheme( 'archives' )->get_archive_title( $post_type, $title );
}

/**
 * Display a post type archive title.
 *
 * Just a wrapper to the default post_type_archive_title for the sake of
 * consistency. This should only be used in AudioTheme-specific template files.
 *
 * @since 1.0.0
 *
 * @see post_type_archive_title()
 *
 * @param string $before Optional. Content to prepend to the title. Default empty.
 * @param string $after  Optional. Content to append to the title. Default empty.
 */
function the_audiotheme_archive_title( $before = '', $after = '' ) {
	$title = post_type_archive_title( '', false );

	if ( ! empty( $title ) ) {
		echo $before . $title . $after;
	}
}

/**
 * Display a post type archive description.
 *
 * @since 1.0.0
 *
 * @param string $before Content to display before the description.
 * @param string $after Content to display after the description.
 */
function the_audiotheme_archive_description( $before = '', $after = '' ) {
	$description = '';

	if ( is_post_type_archive() ) {
		$post_type_object = get_queried_object();

		if ( $archive_id = get_audiotheme_post_type_archive( $post_type_object->name ) ) {
			$archive = get_post( $archive_id );
			$description = $archive->post_content;
		}
	} elseif ( is_tax() ) {
		$description = term_description();
	}

	$description = apply_filters( 'audiotheme_archive_description', $description );
	if ( ! empty( $description ) ) {
		echo $before . apply_filters( 'the_content', $description ) . $after;
	}
}

/**
 * Retrieve archive meta.
 *
 * @since 1.0.0
 *
 * @param string $key Optional. The meta key to retrieve. By default, returns data for all keys.
 * @param bool $single Optional. Whether to return a single value.
 * @param mixed $default Optional. A default value to return if the requested meta doesn't exist.
 * @param string $post_type Optional. The post type archive to retrieve meta data for. Defaults to the current post type.
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
 */
function get_audiotheme_archive_meta( $key = '', $single = false, $default = null, $post_type = null ) {
	return audiotheme( 'archives' )->get_archive_meta( $key, $single, $default, $post_type );
}

/**
 * Display classes for the wrapper div on AudioTheme archive pages.
 *
 * @since 1.2.1
 * @uses audiotheme_class()
 *
 * @param array|string $classes Optional. List of default classes as an array or space-separated string.
 * @param array|string $args Optional. Override defaults.
 * @return array
 */
function audiotheme_archive_class( $classes = array(), $args = array() ) {
	if ( ! empty( $classes ) && ! is_array( $classes ) ) {
		// Split a string.
		$classes = preg_split( '#\s+#', $classes );
	}

	if ( is_audiotheme_post_type_archive() ) {
		$post_type = get_post_type() ? get_post_type() : get_query_var( 'post_type' );
		$post_type_class = 'audiotheme-archive-' . str_replace( 'audiotheme_', '', $post_type );
		$classes = array_merge( $classes, array( 'audiotheme-archive', $post_type_class ) );
	}

	return audiotheme_class( 'archive', $classes, $args );
}
