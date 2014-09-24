<?php
/**
 * Define default filters for modifying WordPress behavior.
 *
 * @package AudioTheme
 * @since 1.0.0
 */

/**
 * Add helpful nav menu item classes.
 *
 * @since 1.0.0
 *
 * @param array $items List of menu items.
 * @param array $args Menu display args.
 * @return array
 */
function audiotheme_nav_menu_classes( $items, $args ) {
	global $wp;

	if ( is_404() || is_search() ) {
		return $items;
	}

	$current_url = trailingslashit( home_url( add_query_arg( array(), $wp->request ) ) );
	$blog_page_id = get_option( 'page_for_posts' );
	$is_blog_post = is_singular( 'post' );

	$is_audiotheme_post_type = is_singular( array( 'audiotheme_gig', 'audiotheme_record', 'audiotheme_track', 'audiotheme_video' ) );
	$post_type_archive_id = get_audiotheme_post_type_archive( get_post_type() );
	$post_type_archive_link = get_post_type_archive_link( get_post_type() );

	$current_menu_parents = array();

	foreach ( $items as $key => $item ) {
		if (
			'audiotheme_archive' == $item->object &&
			$post_type_archive_id == $item->object_id &&
			trailingslashit( $item->url ) == $current_url
		) {
			$items[ $key ]->classes[] = 'current-menu-item';
			$current_menu_parents[] = $item->menu_item_parent;
		}

		if ( $is_blog_post && $blog_page_id == $item->object_id ) {
			$items[ $key ]->classes[] = 'current-menu-parent';
			$current_menu_parents[] = $item->menu_item_parent;
		}

		// Add 'current-menu-parent' class to CPT archive links when viewing a singular template.
		if ( $is_audiotheme_post_type && $post_type_archive_link == $item->url ) {
			$items[ $key ]->classes[] = 'current-menu-parent';
		}
	}

	// Add 'current-menu-parent' classes.
	$current_menu_parents = array_filter( $current_menu_parents );

	if ( ! empty( $current_menu_parents ) ) {
		foreach ( $items as $key => $item ) {
			if ( in_array( $item->ID, $current_menu_parents ) ) {
				$items[ $key ]->classes[] = 'current-menu-parent';
			}
		}
	}

	return $items;
}

/**
 * Enqueue scripts and styles.
 *
 * @since 2.0.0
 */
function audiotheme_enqueue_assets() {
	if ( apply_filters( 'audiotheme_enqueue_assets', '__return_true' ) ) {
		wp_enqueue_script( 'audiotheme' );
		wp_enqueue_style( 'audiotheme' );
	}
}

/**
 * Add wrapper open tags in default templates for theme compatibility.
 *
 * @since 1.2.0
 */
function audiotheme_before_main_content() {
	echo '<div class="audiotheme">';
}

/**
 * Add wrapper close tags in default templates for theme compatibility.
 *
 * @since 1.2.0
 */
function audiotheme_after_main_content() {
	echo '</div>';
}
