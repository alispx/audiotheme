<?php
add_action( 'init', 'audiotheme_load_galleries_admin' );

function audiotheme_load_galleries_admin() {
	add_filter( 'post_updated_messages', 'audiotheme_gallery_post_updated_messages' );
	add_filter( 'audiotheme_nav_menu_archive_items', 'audiotheme_gallery_archive_menu_item' );
}

function audiotheme_gallery_post_updated_messages( $messages ) {
	global $post, $post_ID;
	
	$messages['audiotheme_gallery'] = array(
		0 => '',
		1 => sprintf( __( 'Gallery updated. <a href="%s">View Gallery</a>', 'audiotheme-i18n' ), esc_url( get_permalink( $post_ID ) ) ),
		2 => __( 'Custom field updated.', 'audiotheme-i18n' ),
		3 => __( 'Custom field deleted.', 'audiotheme-i18n' ),
		4 => __( 'Gallery updated.', 'audiotheme-i18n' ),
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Gallery restored to revision from %s', 'audiotheme-i18n' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Gallery published. <a href="%s">View Gallery</a>', 'audiotheme-i18n' ), esc_url( get_permalink( $post_ID ) ) ),
		7 => __( 'Gallery saved.', 'audiotheme-i18n' ),
		8 => sprintf( __( 'Gallery submitted. <a target="_blank" href="%s">Preview Gallery</a>', 'audiotheme-i18n' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		9 => sprintf( __( 'Gallery scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Gallery</a>', 'audiotheme-i18n' ), date_i18n( __( 'M j, Y @ G:i', 'audiotheme-i18n' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( 'Gallery draft updated. <a target="_blank" href="%s">Preview Gallery</a>', 'audiotheme-i18n' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	);

	return $messages;
}

/**
 * Gallery Archive Nav Menu Item
 *
 * @since 1.0.0
 */
function audiotheme_gallery_archive_menu_item( $items ) {
	$items[] = array(
		'title' => _x( 'Gallery', 'nav menu archive label' ),
		'post_type' => 'audiotheme_gallery',
		'url'   => get_post_type_archive_link( 'audiotheme_gallery' )
	);
	
	return $items;
}
?>