<?php
/**
 * General template tags and functions.
 *
 * @package AudioTheme\Template
 * @since 1.0.0
 */

/**
 * Load a template part into a template.
 *
 * @since 2.0.0
 *
 * @param string $slug The slug name for the generic template.
 * @param string $name The name of the specialised template.
 */
function get_audiotheme_template_part( $slug, $name = null ) {
	audiotheme()->templates->get_template_part( $slug, $name );
}

/**
 * Whether theme compatibility mode is active.
 *
 * @since 2.0.0
 *
 * @return bool
 */
function is_audiotheme_theme_compat_active() {
	return audiotheme()->theme_compat->is_active();
}

/**
 * Strip the protocol and trailing slash from a URL for display.
 *
 * @since 1.2.0
 *
 * @param string $url URL to simplify.
 * @return string
 */
function audiotheme_simplify_url( $url ) {
	return untrailingslashit( preg_replace( '|^https?://(www\.)?|i', '', esc_url( $url ) ) );
}

/**
 * Displays navigation to next/previous pages when applicable in archive
 * templates.
 *
 * @since 1.2.0
 */
function audiotheme_archive_nav() {
	global $wp_query;

	if ( $wp_query->max_num_pages > 1 ) :
		?>
		<div class="audiotheme-paged-nav" role="navigation">
			<?php if ( get_previous_posts_link() ) : ?>
				<span class="audiotheme-paged-nav-prev"><?php previous_posts_link( __( '&larr; Previous', 'audiotheme' ) ); ?></span>
			<?php endif; ?>

			<?php if ( get_next_posts_link() ) : ?>
				<span class="audiotheme-paged-nav-next"><?php next_posts_link( __( 'Next &rarr;', 'audiotheme' ) ) ?></span>
			<?php endif; ?>
		</div>
		<?php
	endif;
}

/**
 * Template tag to allow for CSS classes to be easily filtered
 * across templates.
 *
 * @since 1.2.1
 * @link http://www.blazersix.com/blog/wordpress-class-template-tag/
 *
 * @param string $id Element identifier.
 * @param array|string $classes Optional. List of default classes as an array or space-separated string.
 * @param array|string $args Optional. Override defaults.
 * @return array
 */
function audiotheme_class( $id, $classes = array(), $args = array() ) {
	$id = sanitize_key( $id );

	$args = wp_parse_args( (array) $args, array(
		'echo'    => true,
		'post_id' => null
	) );

	if ( ! empty( $classes ) && ! is_array( $classes ) ) {
		// Split a string.
		$classes = preg_split( '#\s+#', $classes );
	} elseif ( empty( $classes ) ) {
		// If the function call didn't pass any classes, use the id as a default class.
		// Otherwise, the calling function can pass the id as a class along with any others.
		$classes = array( $id );
	}

	// Add support for the body element.
	if ( 'body' == $id ) {
		$classes = array_merge( get_body_class(), $classes );
	}

	// Add support for post classes.
	if ( 'post' == $id ) {
		$classes = array_merge( get_post_class( '', $args['post_id'] ), $classes );
	}

	// A page template should set modifier classes all at once in the form of an array.
	$class_mods = apply_filters( 'audiotheme_class', array(), $id, $args );

	if ( ! empty( $class_mods ) && isset( $class_mods[ $id ] ) ) {
		$mods = $class_mods[ $id ];

		// Split a string.
		if ( ! is_array( $mods ) ) {
			$mods = preg_split( '#\s+#', $mods );
		}

		foreach( $mods as $key => $mod ) {
			// If the class starts with a double minus, remove it from both arrays.
			if ( 0 === strpos( $mod, '--' ) ) {
				$unset_class = substr( $mod, 2 );
				unset( $mods[ $key ] );
				unset( $classes[ array_search( $unset_class, $classes ) ] );
			}
		}

		$classes = array_merge( $classes, $mods );
	}

	// Last chance to modify.
	$classes = apply_filters( 'audiotheme_classes', $classes, $id, $args );
	$classes = apply_filters( 'audiotheme_classes-' . $id, $classes, $args );

	if ( $args['echo'] ) {
		echo 'class="' . join( ' ', array_map( 'sanitize_html_class', $classes ) ) . '"';
	}

	return $classes;
}
