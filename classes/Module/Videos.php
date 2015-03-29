<?php
/**
 * Video module.
 *
 * @package AudioTheme\Core\Videos
 * @since 2.0.0
 */

namespace AudioTheme\Core\Module;

use AudioTheme\Core\Module;
use AudioTheme\Core\Util;

/**
 * Video module class.
 *
 * @package AudioTheme\Core\Videos
 * @since 2.0.0
 */
class Videos extends Module {
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
	 * Load the video module.
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
		add_action( 'init',              array( $this, 'register_taxonomies' ) );
		add_action( 'init',              array( $this, 'register_post_types' ) );
		add_action( 'pre_get_posts',     array( $this, 'video_category_query' ), 9 );
		add_action( 'template_include',  array( $this, 'template_include' ) );
		add_action( 'delete_attachment', array( $this, 'delete_oembed_thumbnail_data' ) );
		add_filter( 'post_class',        array( $this, 'archive_post_class' ) );

		if ( ! is_admin() ) {
			add_action( 'pre_get_posts', array( $this, 'sort_query' ) );
		}
	}

	/**
	 * Register the 'audiotheme_video' post type.
	 *
	 * @since 2.0.0
	 */
	public function register_post_types() {
		$labels = array(
			'name'               => _x( 'Videos', 'post type general name', 'audiotheme' ),
			'singular_name'      => _x( 'Video', 'post type singular name', 'audiotheme' ),
			'add_new'            => _x( 'Add New', 'video', 'audiotheme' ),
			'add_new_item'       => __( 'Add New Video', 'audiotheme' ),
			'edit_item'          => __( 'Edit Video', 'audiotheme' ),
			'new_item'           => __( 'New Video', 'audiotheme' ),
			'view_item'          => __( 'View Video', 'audiotheme' ),
			'search_items'       => __( 'Search Videos', 'audiotheme' ),
			'not_found'          => __( 'No videos found', 'audiotheme' ),
			'not_found_in_trash' => __( 'No videos found in Trash', 'audiotheme' ),
			'all_items'          => __( 'All Videos', 'audiotheme' ),
			'menu_name'          => __( 'Videos', 'audiotheme' ),
			'name_admin_bar'     => _x( 'Video', 'add new on admin bar', 'audiotheme' ),
		);

		$args = array(
			'has_archive'            => $this->get_rewrite_base(),
			'hierarchical'           => true,
			'labels'                 => $labels,
			'menu_icon'              => Util::encode_svg( 'admin/images/dashicons/videos.svg' ),
			'menu_position'          => 514,
			'public'                 => true,
			'publicly_queryable'     => true,
			'rewrite'                => array(
				'slug'       => $this->get_rewrite_base(),
				'with_front' => false
			),
			'show_ui'                => true,
			'show_in_menu'           => true,
			'show_in_nav_menus'      => false,
			'supports'               => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions', 'author' ),
		);

		register_post_type( 'audiotheme_video', $args );

		// Register the archive.
		$this->archives->add_post_type_archive( 'audiotheme_video' );
	}

	/**
	 * Register taxonomies.
	 *
	 * @since 2.0.0
	 */
	public function register_taxonomies() {
		$labels = array(
			'name'                       => _x( 'Categories', 'taxonomy general name', 'audiotheme' ),
			'singular_name'              => _x( 'Category', 'taxonomy singular name', 'audiotheme' ),
			'search_items'               => __( 'Search Categories', 'audiotheme' ),
			'popular_items'              => __( 'Popular Categories', 'audiotheme' ),
			'all_items'                  => __( 'All Categories', 'audiotheme' ),
			'parent_item'                => __( 'Parent Category', 'audiotheme' ),
			'parent_item_colon'          => __( 'Parent Category:', 'audiotheme' ),
			'edit_item'                  => __( 'Edit Category', 'audiotheme' ),
			'view_item'                  => __( 'View Category', 'audiotheme' ),
			'update_item'                => __( 'Update Category', 'audiotheme' ),
			'add_new_item'               => __( 'Add New Category', 'audiotheme' ),
			'new_item_name'              => __( 'New Category Name', 'audiotheme' ),
			'separate_items_with_commas' => __( 'Separate categories with commas', 'audiotheme' ),
			'add_or_remove_items'        => __( 'Add or remove categories', 'audiotheme' ),
			'choose_from_most_used'      => __( 'Choose from most used categories', 'audiotheme' ),
			'menu_name'                  => __( 'Categories', 'audiotheme' ),
		);

		$args = array(
			'args'                           => array( 'orderby' => 'term_order' ),
			'hierarchical'                   => true,
			'labels'                         => $labels,
			'public'                         => true,
			'query_var'                      => true,
			'rewrite'                        => array(
				'slug'                       => $this->get_rewrite_base() . '/category',
				'with_front'                 => false,
			),
			'show_ui'                        => true,
			'show_admin_column'              => true,
			'show_in_nav_menus'              => true,
			'show_tagcloud'                  => false,
		);

		register_taxonomy( 'audiotheme_video_category', 'audiotheme_video', $args );
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
	 * Sort video archive requests.
	 *
	 * Defaults to sorting by publish date in descending order. A plugin can hook
	 * into pre_get_posts at an earlier priority and manually set the order.
	 *
	 * @since 1.4.4
	 *
	 * @param object $query The main WP_Query object. Passed by reference.
	 */
	public function sort_query( $query ) {
		if ( ! $query->is_main_query() || ! is_post_type_archive( 'audiotheme_video' ) ) {
			return;
		}

		if ( ! $orderby = $query->get( 'orderby' ) ) {
			$orderby = get_audiotheme_archive_meta( 'orderby', true, 'post_date', 'audiotheme_video' );
			switch ( $orderby ) {
				// Use a plugin like Simple Page Ordering to change the menu order.
				case 'custom' :
					$query->set( 'orderby', 'menu_order' );
					$query->set( 'order', 'asc' );
					break;

				case 'title' :
					$query->set( 'orderby', 'title' );
					$query->set( 'order', 'asc' );
					break;

				// Sort videos by publish date.
				default :
					$query->set( 'orderby', 'post_date' );
					$query->set( 'order', 'desc' );
			}
		}
	}

	/**
	 * Set video category requests to use the same archive settings as videos.
	 *
	 * @since 2.0.0
	 */
	public function video_category_query( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! is_tax( 'audiotheme_video_category' ) ) {
			return;
		}

		$this->archives->set_current_archive_post_type( 'audiotheme_video' );
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

	/**
	 * Delete oEmbed thumbnail post meta if the associated attachment is deleted.
	 *
	 * @since 1.0.0
	 *
	 * @param int $attachment_id The ID of the attachment being deleted.
	 */
	public function delete_oembed_thumbnail_data( $attachment_id ) {
		global $wpdb;

		$sql     = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_audiotheme_oembed_thumbnail_id' AND meta_value=%d", $attachment_id );
		$post_id = $wpdb->get_var( $sql );

		if ( $post_id ) {
			delete_post_meta( $post_id, '_audiotheme_oembed_thumbnail_id' );
			delete_post_meta( $post_id, '_audiotheme_oembed_thumbnail_url' );
		}
	}

	/**
	 * Add classes to video posts on the archive page.
	 *
	 * @since 1.2.0
	 *
	 * @param array $classes Default post classes.
	 * @return array
	 */
	public function archive_post_class( $classes ) {
		global $wp_query;

		if ( $wp_query->is_main_query() && is_post_type_archive( 'audiotheme_video' ) ) {
			$classes[] = 'item';
		}

		return $classes;
	}
}
