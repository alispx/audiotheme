<?php
/**
 * Video module.
 *
 * @package AudioTheme\Core\Videos
 * @since 2.0.0
 */

namespace AudioTheme\Core\Module;

use AudioTheme\Core\Util;

/**
 * Video module class.
 *
 * @package AudioTheme\Core\Videos
 * @since 2.0.0
 */
class Videos extends AbstractModule {
	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args( array(
			'id'             => 'videos',
			'name'           => __( 'Videos', 'audiotheme' ),
			'description'    => __( 'Embed videos from services like YouTube and Vimeo to create your own video library.', 'audiotheme' ),
			'is_core_module' => true,
			'admin_menu_id'  => 'menu-posts-audiotheme_video',
		), $args );

		parent::__construct( $args );
	}

	/**
	 * Register module hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'template_include', array( $this, 'template_include' ) );
	}

	/**
	 * Get the videos rewrite base. Defaults to 'videos'.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_rewrite_base() {
		global $wp_rewrite;

		$front = '';
		$base  = get_option( 'audiotheme_video_rewrite_base', 'videos' );

		if ( $wp_rewrite->using_index_permalinks() ) {
			$front = $wp_rewrite->index . '/';
		}

		return $front . $base;
	}

	/**
	 * Load video templates.
	 *
	 * Templates should be included in an /audiotheme/ directory within the theme.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template Template path.
	 * @return string
	 */
	public function template_include( $template ) {
		$original_template = $template;
		$template_loader   = $this->template_loader;
		$compat            = $this->theme_compatibility;

		if ( is_post_type_archive( 'audiotheme_video' ) || is_tax( 'audiotheme_video_category' ) ) {
			if ( is_tax() ) {
				$term = get_queried_object();
				$taxonomy = str_replace( 'audiotheme_', '', $term->taxonomy );
				$templates[] = "taxonomy-$taxonomy-{$term->slug}.php";
				$templates[] = "taxonomy-$taxonomy.php";
			}

			$template = $template_loader->locate_template( 'archive-video.php' );
			$compat->set_title( get_audiotheme_post_type_archive_title() );
			$compat->set_loop_template_part( 'video/loop', 'archive' );
		} elseif ( is_singular( 'audiotheme_video' ) ) {
			$template = $template_loader->locate_template( 'single-video.php' );
			$compat->set_title( get_queried_object()->post_title );
			$compat->set_loop_template_part( 'video/loop', 'single' );
		}

		if ( $template !== $original_template ) {
			$template = $this->get_compatible_template( $template );
			do_action( 'audiotheme_template_include', $template );
		}

		return $template;
	}
}
