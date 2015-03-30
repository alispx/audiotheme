<?php
/**
 * Video post type registration and integration.
 *
 * @package AudioTheme\Core
 * @since 2.0.0
 */

namespace AudioTheme\Core\Provider\PostType;

use AudioTheme\Core\HookProviderInterface;
use AudioTheme\Core\Plugin;
use AudioTheme\Core\Util;

/**
 * Class for registering the video post type and integration.
 *
 * @package AudioTheme\Core
 * @since   2.0.0
 */
class VideoPostType implements HookProviderInterface {
	/**
	 * Module.
	 *
	 * @since 2.0.0
	 * @var \AudioTheme\Core\Module
	 */
	protected $module;

	/**
	 * Plugin instance.
	 *
	 * @since 2.0.0
	 * @var \AudioTheme\Core\Plugin
	 */
	protected $plugin;

	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 *
	 * @param \AudioTheme\Core\Plugin Main plugin instance.
	 * @param \AudioTheme\Core\Module Module instance.
	 */
	public function __construct( Plugin $plugin, $module ) {
		$this->plugin = $plugin;
		$this->module = $module;
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 *
	 * @param \AudioTheme\Core\Plugin Main plugin instance.
	 */
	public function register_hooks( Plugin $plugin ) {
		add_action( 'init',              array( $this, 'register_post_type' ) );
		add_action( 'delete_attachment', array( $this, 'delete_oembed_thumbnail_data' ) );
		add_filter( 'post_class',        array( $this, 'archive_post_class' ) );

		if ( ! is_admin() ) {
			add_action( 'pre_get_posts', array( $this, 'sort_query' ) );
		}

		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
	}

	/**
	 * Register the 'audiotheme_video' post type.
	 *
	 * @since 2.0.0
	 */
	public function register_post_type() {
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
			'has_archive'            => $this->module->get_rewrite_base(),
			'hierarchical'           => true,
			'labels'                 => $labels,
			'menu_icon'              => Util::encode_svg( 'admin/images/dashicons/videos.svg' ),
			'menu_position'          => 514,
			'public'                 => true,
			'publicly_queryable'     => true,
			'rewrite'                => array(
				'slug'       => $this->module->get_rewrite_base(),
				'with_front' => false
			),
			'show_ui'                => true,
			'show_in_menu'           => true,
			'show_in_nav_menus'      => false,
			'supports'               => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions', 'author' ),
		);

		register_post_type( 'audiotheme_video', $args );

		// Register the archive.
		$this->plugin['archives']->add_post_type_archive( 'audiotheme_video' );
	}

	/**
	 * Sort video archive requests.
	 *
	 * Defaults to sorting by publish date in descending order. A plugin can
	 * hook into pre_get_posts at an earlier priority and manually set the order.
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

	/**
	 * Video update messages.
	 *
	 * @since 1.0.0
	 *
	 * @see /wp-admin/edit-form-advanced.php
	 *
	 * @param array $messages The array of existing post update messages.
	 * @return array
	 */
	public function post_updated_messages( $messages ) {
		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		if ( 'audiotheme_video' !== $post_type ) {
			return $messages;
		}

		$messages[ $post_type ] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Video updated.', 'audiotheme' ),
			2  => __( 'Custom field updated.', 'audiotheme' ),
			3  => __( 'Custom field deleted.', 'audiotheme' ),
			4  => __( 'Video updated.', 'audiotheme' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Video restored to revision from %s', 'audiotheme' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Video published.', 'audiotheme' ),
			7  => __( 'Video saved.', 'audiotheme' ),
			8  => __( 'Video submitted.', 'audiotheme' ),
			9  => sprintf( __( 'Video scheduled for: <strong>%1$s</strong>.', 'audiotheme' ),
				  // translators: Publish box date format, see http://php.net/date
				  date_i18n( __( 'M j, Y @ G:i', 'audiotheme' ), strtotime( $post->post_date ) ) ),
			10 => __( 'Video draft updated.', 'audiotheme' ),
		);

		if ( $post_type_object->publicly_queryable ) {
			$permalink = get_permalink( $post->ID );
			$preview_permalink = add_query_arg( 'preview', 'true', $permalink );

			$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View video', 'audiotheme' ) );
			$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview video', 'audiotheme' ) );

			$messages[ $post_type ][1]  .= $view_link;
			$messages[ $post_type ][6]  .= $view_link;
			$messages[ $post_type ][9]  .= $view_link;
			$messages[ $post_type ][8]  .= $preview_link;
			$messages[ $post_type ][10] .= $preview_link;
		}

		return $messages;
	}
}
