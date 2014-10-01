<?php
/**
 * Video module.
 *
 * @package AudioTheme\Videos
 * @since 2.0.0
 */

/**
 * Video module class.
 *
 * @package AudioTheme\Videos
 * @since 2.0.0
 */
class AudioTheme_Module_Videos extends AudioTheme_Module {
	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args( array(
			'module_id'          => 'videos',
			'module_name'        => __( 'Videos', 'audiotheme' ),
			'module_description' => __( '', 'audiotheme' ),
			'is_core_module'     => true,
			'is_togglable'       => true,
			'admin_menu_id'      => 'menu-posts-audiotheme_video',
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

		if ( is_admin() ) {
			$this->load_admin();
		}
	}

	/**
	 * Load the video module admin.
	 *
	 * @since 2.0.0
	 */
	public function load_admin() {
		$admin = new AudioTheme_Module_Videos_Admin();
		$admin->load();
	}

	/**
	 * Register module hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'init',              array( $this, 'register_post_types' ) );
		add_action( 'template_include',  array( $this, 'template_include' ) );
		add_action( 'delete_attachment', array( $this, 'delete_oembed_thumbnail_data' ) );
		add_filter( 'post_class',        array( $this, 'archive_post_class' ) );

		if ( ! is_admin() ) {
			add_action( 'pre_get_posts', array( $this, 'sort_query' ) );
			add_action( 'pre_get_posts', array( $this, 'default_template_query' ) );
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
			'menu_icon'              => audiotheme_encode_svg( 'modules/admin/images/dashicons/videos.svg' ),
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
			'taxonomies'             => array( 'post_tag' ),
		);

		register_post_type( 'audiotheme_video', $args );
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
	 * Set posts per page for video archives.
	 *
	 * The default video archive template uses a 4-column grid. If it's loaded from
	 * the plugin, set the posts per page arg to a multiple of 4.
	 *
	 * @since 1.3.0
	 *
	 * @param object $query The main WP_Query object. Passed by reference.
	 */
	public function default_template_query( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! is_post_type_archive( 'audiotheme_video' ) ) {
			return;
		}

		if ( '' == $query->get( 'posts_per_archive_page' ) ) {
			$query->set( 'posts_per_archive_page', 12 );
		}
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
		$compat            = audiotheme()->theme_compat;

		if ( is_post_type_archive( 'audiotheme_video' ) ) {
			$template = audiotheme_locate_template( 'archive-video.php' );

			$compat->set_title( get_audiotheme_post_type_archive_title() );
			$compat->set_loop_template_part( 'parts/loop-archive', 'video' );
		} elseif ( is_singular( 'audiotheme_video' ) ) {
			$template = audiotheme_locate_template( 'single-video.php' );

			$compat->set_title( '' );
			$compat->set_loop_template_part( 'parts/loop-single', 'video' );
		}

		if ( $template !== $original_template ) {
			// Enable theme compatibility.
			if ( ! $compat->is_template_compatible( $template ) ) {
				$compat->enable();
				$template = $compat->get_template();
			}

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
	 * Classes serve as helpful hooks to aid in styling across various browsers.
	 *
	 * - Adds nth-child classes to video posts.
	 *
	 * @since 1.2.0
	 *
	 * @param array $classes Default post classes.
	 * @return array
	 */
	public function archive_post_class( $classes ) {
		global $wp_query;

		if ( $wp_query->is_main_query() && is_post_type_archive( 'audiotheme_video' ) ) {
			$nth_child_classes = audiotheme_nth_child_classes( array(
				'current' => $wp_query->current_post + 1,
				'max'     => get_audiotheme_archive_meta( 'columns', true, 4 ),
			) );

			$classes = array_merge( $classes, $nth_child_classes );
		}

		return $classes;
	}

	/**
	 * Get the videos rewrite base. Defaults to 'videos'.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_rewrite_base() {
		return get_option( 'audiotheme_video_rewrite_base', 'videos' );
	}
}
