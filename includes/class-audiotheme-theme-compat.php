<?php
/**
 * AudioTheme Theme Compatibility
 *
 * @package AudioTheme
 * @since 2.0.0
 */

/**
 * Theme compatibility class.
 *
 * Plugins aren't aware of HTML wrappers are the main content defined in a
 * theme's templates, making it difficult to render custom templates.
 *
 * Themes can add support by printing their content wrappers in the
 * 'audiotheme_before_main_content' and 'audiotheme_after_main_content'
 * actions and declaring support with add_theme_support( 'audiotheme' ).
 *
 * If a theme hasn't declared support and the located template is in the
 * plugin's /templates directory, then a compatible template in the theme
 * (like page.php) is loaded instead and the main $wp_query object is
 * manipulated in order to facilitate rendering.
 *
 * @link https://bbpress.trac.wordpress.org/browser/trunk/src/includes/core/theme-compat.php
 *
 * @package AudioTheme
 * @since 2.0.0
 */
class Audiotheme_Theme_Compat {
	/**
	 * Whether theme compat mode is enabled.
	 *
	 * @since 2.0.0
	 * @type bool
	 */
	protected $is_active = false;

	/**
	 * The temporary post title if a compat template is loaded.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	protected $title = '';

	/**
	 * Slug for the template part that renders the main loop.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	protected $template_part_slug = '';

	/**
	 * Name for the template part that renders the main loop.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	protected $template_part_name = '';

	/**
	 * Deep copy of the main query.
	 *
	 * @since 2.0.0
	 * @type WP_Query
	 */
	protected $the_query;

	/**
	 * Copy of any removed filters.
	 *
	 * @since 2.0.0
	 * @type array
	 */
	protected $filters = array();

	/**
	 * Copy of any removed filters.
	 *
	 * @since 2.0.0
	 * @type array
	 */
	protected $merged_filters = array();

	/**
	 * Whether the original main loop has started.
	 *
	 * This flag is set to true when the original main loop starts so the
	 * 'loop_start' filter isn't applied to any other loops. Removing a filter
	 * from within itself can cause issues.
	 *
	 * @link https://core.trac.wordpress.org/ticket/21169
	 *
	 * @since 2.0.0
	 * @type bool
	 */
	protected $has_main_loop_started = false;

	/**
	 * Enable theme compatibility.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $is_active
	 */
	public function enable() {
		$this->is_active = true;

		// Time to start drinking. Hijack the main loop.
		add_action( 'loop_start', array( $this, 'loop_start' ) );
	}

	/**
	 * Retrieve a theme compatible template.
	 *
	 * @since 2.0.0
	 *
	 * @link https://core.trac.wordpress.org/ticket/20509
	 * @link https://core.trac.wordpress.org/ticket/22355
	 *
	 * @return string The template path if one is located.
	 */
	public function get_template() {
		// If the template is being loaded from the plugin and the theme hasn't
		// declared support, search for a compatible template in the theme.
		$template = locate_template( array(
			'plugin-audiotheme.php',
			'audiotheme.php',
			'generic.php',
			'page.php',
			'single.php',
			'index.php',
		) );

		return $template;
	}

	/**
	 * Retrieve the main content for the request.
	 *
	 * Loads the template part declared in set_loop_template_part().
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_content() {
		if ( empty( $this->template_part_slug ) ) {
			return '';
		}

		ob_start();
		get_audiotheme_template_part( $this->template_part_slug, $this->template_part_name );
		return ob_get_clean();
	}

	/**
	 * Whether theme compatibility mode is enabled.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_active() {
		return $this->is_active;
	}

	/**
	 * Whether a template file is compatible with the theme.
	 *
	 * @param string $template Template file path.
	 * @return bool
	 */
	public function is_template_compatible( $template ) {
		return current_theme_supports( 'audiotheme' ) || ! $this->is_plugin_template( $template );
	}

	/**
	 * Set the template part for rendering the main loop.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug The slug name for the generic template.
	 * @param string $name The name of the specialised template.
	 */
	public function set_loop_template_part( $slug, $name = null ) {
		$this->template_part_slug = $slug;
		$this->template_part_name = $name;
	}

	/**
	 * Set the title for the temporary post when a compat template is loaded.
	 *
	 * @since 2.0.0
	 *
	 * @param string $title Page title.
	 */
	public function set_title( $title ) {
		$this->title = $title;
	}

	/*
	 * Protected methods.
	 */

	/**
	 * Filter the main loop as it gets started.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Query $wp_query Main query object.
	 */
	public function loop_start( $wp_query ) {
		// Only run this during the main loop.
		if ( ! $wp_query->is_main_query() || $this->has_main_loop_started ) {
			return;
		}

		// Set a flag so this method won't run again.
		$this->has_main_loop_started = true;

		// Hack to create a deep copy of $wp_query.
		$this->the_query = unserialize( serialize( $wp_query ) );

		// Get a temporary post for the default main loop.
		$post = $this->get_temp_post(
			array(
				'post_content' => $this->get_content(),
				'post_title'   => $this->title,
			),
			is_singular() ? $wp_query->post : array()
		);

		$post = new WP_Post( (object) $post );
		$this->replace_the_query( $post );
		$this->disable_filters();

		// Restore the query and filters when this loop ends.
		add_action( 'loop_end', array( $this, 'loop_end' ) );
	}

	/**
	 * Clean up after the main loop has finished.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Query $wp_query Main query object.
	 */
	public function loop_end( $wp_query ) {
		if ( ! $wp_query->is_main_query() ) {
			return;
		}

		$this->reset_the_query();
		$this->restore_filters();
	}

	/**
	 * Retrieve a temporary post object.
	 *
	 * @since 2.0.0
	 *
	 * @see get_default_post_to_edit()
	 * @see bbp_theme_compat_reset_post()
	 *
	 * @param array $args Optional. Properties to assign to the temporary post.
	 * @param array $defaults Optional. Properties that should override the default temporary properties.
	 * @return array
	 */
	protected function get_temp_post( $args = array(), $defaults = array() ) {
		if ( ! empty( $defaults ) ) {
			$args = wp_parse_args( $args, (array) $defaults );
		}

		$post = wp_parse_args( $args, array(
			'ID'                    => -9999,
			'post_author'           => 0,
			'post_date'             => 0,
			'post_date_gmt'         => 0,
			'post_content'          => '',
			'post_title'            => '',
			'post_excerpt'          => '',
			'post_status'           => 'publish',
			'comment_status'        => 'closed',
			'ping_status'           => '',
			'post_password'         => '',
			'post_name'             => '',
			'to_ping'               => '',
			'pinged'                => '',
			'post_modified'         => 0,
			'post_modified_gmt'     => 0,
			'post_content_filtered' => '',
			'post_parent'           => 0,
			'guid'                  => '',
			'menu_order'            => 0,
			'post_type'             => 'page',
			'post_mime_type'        => '',
			'comment_count'         => 0,
			'page_template'         => 'default',
			'filter'                => 'raw',
		) );

		return $post;
	}

	/**
	 * Whether a template file is located in the plugin.
	 *
	 * @since 2.0.0
	 *
	 * @param string $template Template file path.
	 * @return bool
	 */
	protected function is_plugin_template( $template ) {
		$base     = str_replace( array( '\\', '/' ), DIRECTORY_SEPARATOR, AUDIOTHEME_DIR );
		$template = str_replace( array( '\\', '/' ), DIRECTORY_SEPARATOR, $template );
		return ( 0 === strpos( $template, $base ) );
	}

	/**
	 * Replace properties in $wp_query.
	 *
	 * @since 2.0.0
	 *
	 * @global WP_Query $wp_query
	 *
	 * @param WP_Post $post Post to replace in the query.
	 */
	protected function replace_the_query( $post ) {
		global $wp_query;

		$wp_query->post        = $post;
		$wp_query->posts       = array( $post );
		$wp_query->post_count  = 1;
		$wp_query->found_posts = 1;
		$wp_query->is_archive  = false;
		$wp_query->is_singular = true;

		// Attempt to disable the comment form if the current post doesn't support them.
		if ( 'open' != $post->comment_status || ! post_type_supports( $post->post_type, 'comments' ) ) {
			$wp_query->is_single  = false;
		}
	}

	/**
	 * Restore the query.
	 *
	 * @since 2.0.0
	 *
	 * @global WP_Query $wp_query
	 */
	protected function reset_the_query() {
		global $wp_query;

		if ( ! isset( $this->the_query ) ) {
			return;
		}

		$wp_query->post        = $this->the_query->post;
		$wp_query->posts       = $this->the_query->posts;
		$wp_query->post_count  = $this->the_query->post_count;
		$wp_query->found_posts = $this->the_query->found_posts;
		$wp_query->is_404      = $this->the_query->is_404;
		$wp_query->is_archive  = $this->the_query->is_archive;
		$wp_query->is_page     = $this->the_query->is_page;
		$wp_query->is_single   = $this->the_query->is_single;
		$wp_query->is_singular = $this->the_query->is_singular;
		$wp_query->is_tax      = $this->the_query->is_tax;

		// Break the reference.
		unset( $this->the_query );
	}

	/**
	 * Disable standard filters that shouldn't be run on the temporary post in a compat template.
	 *
	 * @since 2.0.0
	 */
	protected function disable_filters() {
		// Disable post thumbnails.
		add_filter( 'get_post_metadata', array( $this, 'disable_post_thumbnails' ), 20, 3 );

		// Remove any filters from the_content.
		$this->remove_all_filters( 'the_content' );
	}

	/**
	 * Prevent post thumbnails from being displayed for the temporary post.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $value Filtered post meta value.
	 * @param int $post_id Post ID.
	 * @param string $key Post meta key.
	 * @return mixed Post meta value.
	 */
	public function disable_post_thumbnails( $value, $post_id, $key ) {
		if ( '_thumbnail_id' == $key ) {
			$value = '';
		}
		return $value;
	}

	/**
	 * Restore filters.
	 *
	 * @since 2.0.0
	 */
	protected function restore_filters() {
		remove_filter( 'get_post_metadata', array( $this, 'disable_post_thumbnails' ), 20, 3 );

		// Restore the_content filters.
		$this->restore_all_filters( 'the_content' );
	}

	/**
	 * Remove all the hooks from a filter and save a reference.
	 *
	 * @since 2.0.0
	 *
	 * @see bbp_remove_all_filters()
	 * @global WP_filter $wp_filter
	 * @global array $merged_filters
	 *
	 * @param string $tag The filter to remove hooks from.
	 * @param int|bool $priority Optional. The priority number to remove. Default false.
	 * @return bool True when finished.
	 */
	protected function remove_all_filters( $tag, $priority = false ) {
		global $wp_filter, $merged_filters;

		if ( isset( $wp_filter[ $tag ] ) ) {
			if ( false !== $priority && isset( $wp_filter[ $tag ][ $priority ] ) ) {
				$this->filters[ $tag ][ $priority ] = $wp_filter[ $tag ][ $priority ];
				$wp_filter[ $tag ][ $priority ] = array();
			} else {
				$this->filters[ $tag ] = $wp_filter[ $tag ];
				$wp_filter[ $tag ] = array();
			}
		}

		if ( isset( $merged_filters[ $tag ] ) ) {
			$this->merged_filters[ $tag ] = $merged_filters[ $tag ];
			unset( $merged_filters[ $tag ] );
		}

		return true;
	}

	/**
	 * Restore all hooks that were removed from a filter.
	 *
	 * @since 2.0.0
	 *
	 * @see bbp_restore_all_filters()
	 * @global WP_filter $wp_filter
	 * @global array $merged_filters
	 *
	 * @param string $tag The filter to remove hooks from.
	 * @param int|bool $priority Optional. The priority number to remove. Default false.
	 * @return bool True when finished.
	 */
	protected function restore_all_filters( $tag, $priority = false ) {
		global $wp_filter, $merged_filters;

		if ( isset( $this->filters[ $tag ] ) ) {
			if ( false !== $priority && isset( $this->filters[ $tag ][ $priority ] ) ) {
				$wp_filter[ $tag ][ $priority ] = $this->filters[ $tag ][ $priority ];
				unset( $this->filters[ $tag ][ $priority ] );
			} else {
				$wp_filter[ $tag ] = $this->filters[ $tag ];
				unset( $this->filters[ $tag ] );
			}
		}

		if ( isset( $this->merged_filters[ $tag ] ) ) {
			$merged_filters[ $tag ] = $this->merged_filters[ $tag ];
			unset( $this->merged_filters[ $tag ] );
		}

		return true;
	}
}
