<?php
/**
 * Video template functions.
 *
 * @package AudioTheme\Core\Template
 * @since 1.0.0
 */

use AudioTheme\Core\Model\Video;

/**
 * Retrieve a video URL.
 *
 * @since 1.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @return string
 */
function get_audiotheme_video_url( $post_id = null ) {
	$video = get_audiotheme_video( $post_id );
	return $video->get_url();
}

/**
 * Retrieve a video.
 *
 * @since 1.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @return \AudioTheme\Core\Model\Video
 */
function get_audiotheme_video( $post_id = null, $args = array(), $query_args = array() ) {
	$video = new Video( $post_id );

	if ( ! empty( $args ) || ! empty( $query_args ) ) {
		return $video->get_html( $args, $query_args );
	}

	return $video;
}

/**
 * Display a video.
 *
 * @since 1.0.0
 *
 * @param array $args Optional. (width, height)
 * @param array $query_args Optional. Provider specific parameters.
 */
function the_audiotheme_video_html( $args = array(), $query_args = array() ) {
	echo get_audiotheme_video_html( get_the_ID(), $args, $query_args );
}

/**
 * Retrieve a video.
 *
 * @since 2.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @param array $args Optional. (width, height)
 * @param array $query_args Optional. Provider specific parameters.
 * @return string Video HTML
 */
function get_audiotheme_video_html( $post_id = null, $args = array(), $query_args = array() ) {
	$video = get_audiotheme_video( $post_id );
	return $video->get_html( $args, $query_args );
}
