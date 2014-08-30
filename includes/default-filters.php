<?php
/**
 * Define default filters for modifying WordPress behavior.
 *
 * @package AudioTheme
 * @since 1.0.0
 */

/**
 * Filter audiotheme_archive permalinks to match the corresponding post type's
 * archive.
 *
 * @since 1.0.0
 *
 * @param string $permalink Default permalink.
 * @param WP_Post $post Post object.
 * @param bool $leavename Optional, defaults to false. Whether to keep post name.
 * @return string Permalink.
 */
function audiotheme_archives_post_type_link( $permalink, $post, $leavename ) {
	global $wp_rewrite;

	if ( 'audiotheme_archive' == $post->post_type ) {
		$post_type = is_audiotheme_post_type_archive_id( $post->ID );
		$post_type_object = get_post_type_object( $post_type );

		if ( get_option( 'permalink_structure' ) ) {
			$front = '/';
			if ( $wp_rewrite->using_index_permalinks() ) {
				$front .= $wp_rewrite->index . '/';
			}

			if ( isset( $post_type_object->rewrite ) && $post_type_object->rewrite['with_front'] ) {
				$front = $wp_rewrite->front;
			}

			if ( $leavename ) {
				$permalink = home_url( $front . '%postname%/' );
			} else {
				$permalink = home_url( $front . $post->post_name . '/' );
			}
		} else {
			$permalink = add_query_arg( 'post_type', $post_type, home_url( '/' ) );
		}
	}

	return $permalink;
}

/**
 * Filter post type archive permalinks.
 *
 * @since 1.0.0
 *
 * @param string $link Post type archive link.
 * @param string $post_type Post type name.
 * @return string
 */
function audiotheme_archives_post_type_archive_link( $link, $post_type ) {
	if ( $archive_id = get_audiotheme_post_type_archive( $post_type ) ) {
		$link = get_permalink( $archive_id );
	}

	return $link;
}

/**
 * Filter the default post_type_archive_title() template tag and replace with
 * custom archive title.
 *
 * @since 1.0.0
 *
 * @param string $label Post type archive title.
 * @return string
 */
function audiotheme_archives_post_type_archive_title( $title ) {
	$post_type_object = get_queried_object();
	return get_audiotheme_post_type_archive_title( $post_type_object->name, $title );
}

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
 * Set up AudioTheme templates when they're loaded.
 *
 * Limits default scripts and styles to load only for AudioTheme templates.
 *
 * @since 1.2.0
 */
function audiotheme_template_setup( $template ) {
	if ( is_audiotheme_default_template( $template ) ) {
		add_action( 'wp_enqueue_scripts', 'audiotheme_enqueue_scripts' );
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
