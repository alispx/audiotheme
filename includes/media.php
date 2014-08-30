<?php
/**
 * AudioTheme API for working with media and defines filters for modifying
 * WordPress behavior related to media.
 *
 * @package AudioTheme
 * @since 1.0.0
 */

/**
 * Add audio metadata to attachment response objects.
 *
 * @since 1.4.4
 *
 * @param array $response Attachment data to send as JSON.
 * @param WP_Post $attachment Attachment object.
 * @param array $meta Attachment meta.
 * @return array
 */
function audiotheme_wp_prepare_audio_attachment_for_js( $response, $attachment, $meta ) {
	if ( 'audio' !== $response['type'] ) {
		return $response;
	}

	$response['audiotheme'] = $meta;

	return $response;
}
add_filter( 'wp_prepare_attachment_for_js', 'audiotheme_wp_prepare_audio_attachment_for_js', 10, 3 );
