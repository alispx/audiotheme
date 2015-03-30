<?php
/**
 * Discography module.
 *
 * @package AudioTheme\Core\Discography
 * @since 2.0.0
 */

namespace AudioTheme\Core\Module;

use AudioTheme\Core\Util;

/**
 * Discography module class.
 *
 * @package AudioTheme\Core\Discography
 * @since 2.0.0
 */
class Discography extends AbstractModule {
	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args( array(
			'id'             => 'discography',
			'name'           => __( 'Discography', 'audiotheme' ),
			'description'    => __( 'Upload album artwork, assign titles and tracks, add audio files, and enter links to purchase your music.', 'audiotheme' ),
			'is_core_module' => true,
			'admin_menu_id'  => 'toplevel_page_edit-post_type-audiotheme_record',
		), $args );

		parent::__construct( $args );
	}

	/**
	 * Load the Discography module.
	 *
	 * @since 2.0.0
	 */
	public function load() {
		$this->register_hooks();
	}

	/**
	 * Register module hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_filter( 'generate_rewrite_rules', array( $this, 'generate_rewrite_rules' ) );
		add_action( 'template_include',       array( $this, 'template_include' ) );

		// Admin hooks.
		add_action( 'admin_menu',             array( $this, 'add_menu_item' ) );
	}

	/**
	 * Get the Discography rewrite base. Defaults to 'music'.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_rewrite_base() {
		global $wp_rewrite;

		$front = '';
		$base  = get_option( 'audiotheme_record_rewrite_base', 'music' );

		if ( $wp_rewrite->using_index_permalinks() ) {
			$front = $wp_rewrite->index . '/';
		}

		return $front . $base;
	}

	/**
	 * Load discography templates.
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

		if (
			is_post_type_archive( array( 'audiotheme_record', 'audiotheme_track' ) ) ||
			is_tax( 'audiotheme_record_type' ) ||
			is_tax( 'audiotheme_genre' )
		) {
			if ( is_post_type_archive( 'audiotheme_track' ) ) {
				$templates[] = 'archive-track.php';
			}

			if ( is_tax( 'audiotheme_record_type' ) ) {
				$term = get_queried_object();
				$slug = str_replace( 'record-type-', '', $term->slug );
				$taxonomy = str_replace( 'audiotheme_', '', $term->taxonomy );
				$templates[] = "taxonomy-$taxonomy-{$slug}.php";
				$templates[] = "taxonomy-$taxonomy.php";
			}

			if ( is_tax( 'audiotheme_genre' ) ) {
				$term = get_queried_object();
				$templates[] = "taxonomy-genre-{$term->slug}.php";
				$templates[] = "taxonomy-genre.php";
			}

			$templates[] = 'archive-record.php';

			$template = $template_loader->locate_template( $templates );
			$compat->set_title( get_audiotheme_post_type_archive_title() );
			$compat->set_loop_template_part( 'record/loop', 'archive' );
		} elseif ( is_singular( 'audiotheme_record' ) ) {
			$templates = array( 'single-record.php' );

			$template = $template_loader->locate_template( $templates );
			$compat->set_title( get_queried_object()->post_title );
			$compat->set_loop_template_part( 'record/loop', 'single' );
		} elseif ( is_singular( 'audiotheme_track' ) ) {
			$template = $template_loader->locate_template( 'single-track.php' );
			$compat->set_title( get_queried_object()->post_title );
			$compat->set_loop_template_part( 'track/loop', 'single' );
		}

		if ( $template !== $original_template ) {
			$template = $this->get_compatible_template( $template );
			do_action( 'audiotheme_template_include', $template );
		}

		return $template;
	}

	/**
	 * Add custom discography rewrite rules.
	 *
	 * @since 2.0.0
	 * @see get_audiotheme_discography_rewrite_base()
	 *
	 * @param object $wp_rewrite The main rewrite object. Passed by reference.
	 */
	public function generate_rewrite_rules( $wp_rewrite ) {
		$base = $this->get_rewrite_base();

		$new_rules[ $base . '/genre/([^/]+)/?$' ] = 'index.php?post_type=audiotheme_record&audiotheme_genre=$matches[1]';
		$new_rules[ $base . '/genre/([^/]+)/page/([0-9]{1,})/?$'] = 'index.php?post_type=audiotheme_record&audiotheme_genre=$matches[1]&paged=$matches[2]';
		$new_rules[ $base . '/tracks/?$' ] = 'index.php?post_type=audiotheme_track';
		$new_rules[ $base . '/page/([0-9]{1,})/?$'] = 'index.php?post_type=audiotheme_record&paged=$matches[1]';
		$new_rules[ $base .'/([^/]+)/track/([^/]+)?$'] = 'index.php?audiotheme_record=$matches[1]&audiotheme_track=$matches[2]';
		$new_rules[ $base . '/([^/]+)/?$'] = 'index.php?audiotheme_record=$matches[1]';
		$new_rules[ $base . '/?$' ] = 'index.php?post_type=audiotheme_record';

		$wp_rewrite->rules = array_merge( $new_rules, $wp_rewrite->rules );
	}

	/**
	 * Discography admin menu.
	 *
	 * @since 2.0.0
	 */
	public function add_menu_item() {
		add_menu_page(
			__( 'Discography', 'audiotheme' ),
			__( 'Discography', 'audiotheme' ),
			'edit_posts',
			'edit.php?post_type=audiotheme_record',
			null,
			Util::encode_svg( 'admin/images/dashicons/discography.svg' ),
			513
		);
	}
}
