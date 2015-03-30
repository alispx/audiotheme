<?php
/**
 * Record post type registration and integration.
 *
 * @package AudioTheme\Core
 * @since 2.0.0
 */

namespace AudioTheme\Core\Provider\PostType;

use AudioTheme\Core\HookProviderInterface;
use AudioTheme\Core\Plugin;

/**
 * Class for registering the record post type and integration.
 *
 * @package AudioTheme\Core
 * @since   2.0.0
 */
class RecordPostType implements HookProviderInterface {
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
	public function register( Plugin $plugin ) {
		add_action( 'init',                   array( $this, 'register_post_types' ) );
		add_action( 'pre_get_posts',          array( $this, 'sort_query' ) );
		add_filter( 'post_type_archive_link', array( $this, 'archive_permalink' ), 10, 2 );
		add_filter( 'post_type_link',         array( $this, 'post_permalinks' ), 10, 4 );
		add_filter( 'post_class',             array( $this, 'archive_post_class' ) );
		add_filter( 'post_updated_messages',  array( $this, 'post_updated_messages' ) );
	}

	/**
	 * Register the record post type.
	 *
	 * @since 2.0.0
	 */
	public function register_post_types() {
		$labels = array(
			'name'               => _x( 'Records', 'post format general name', 'audiotheme' ),
			'singular_name'      => _x( 'Record', 'post format singular name', 'audiotheme' ),
			'add_new'            => _x( 'Add New', 'audiotheme_record', 'audiotheme' ),
			'add_new_item'       => __( 'Add New Record', 'audiotheme' ),
			'edit_item'          => __( 'Edit Record', 'audiotheme' ),
			'new_item'           => __( 'New Record', 'audiotheme' ),
			'view_item'          => __( 'View Record', 'audiotheme' ),
			'search_items'       => __( 'Search Records', 'audiotheme' ),
			'not_found'          => __( 'No records found.', 'audiotheme' ),
			'not_found_in_trash' => __( 'No records found in Trash.', 'audiotheme' ),
			'parent_item_colon'  => __( 'Parent Records:', 'audiotheme' ),
			'all_items'          => __( 'All Records', 'audiotheme' ),
			'menu_name'          => __( 'Records', 'audiotheme' ),
			'name_admin_bar'     => _x( 'Record', 'add new on admin bar', 'audiotheme' ),
		);

		$args = array(
			'capability_type'        => 'post',
			'has_archive'            => $this->module->get_rewrite_base(),
			'hierarchical'           => true,
			'labels'                 => $labels,
			'menu_position'          => 513,
			'public'                 => true,
			'publicly_queryable'     => true,
			'rewrite'                => false,
			'show_ui'                => true,
			'show_in_admin_bar'      => true,
			'show_in_menu'           => true,
			'show_in_nav_menus'      => true,
			'supports'               => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		);

		register_post_type( 'audiotheme_record', $args );

		// Register the archive.
		$this->plugin['archives']->add_post_type_archive( 'audiotheme_record' );
	}

	/**
	 * Sort record archive requests.
	 *
	 * Defaults to sorting by release year in descending order. An option is
	 * available on the archive page to sort by title or a custom order. The
	 * custom order using the 'menu_order' value, which can be set using a
	 * plugin like Simple Page Ordering.
	 *
	 * Alternatively, a plugin can hook into pre_get_posts at an earlier
	 * priority and manually set the order.
	 *
	 * @since 1.3.0
	 *
	 * @param object $query The main WP_Query object. Passed by reference.
	 */
	public function sort_query( $query ) {
		if (
			is_admin() ||
			! $query->is_main_query() ||
			! ( is_post_type_archive( 'audiotheme_record' ) || is_tax( 'audiotheme_record_type' ) )
		) {
			return;
		}

		if ( ! $orderby = $query->get( 'orderby' ) ) {
			$orderby = get_audiotheme_archive_meta( 'orderby', true, 'release_year', 'audiotheme_record' );
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

				// Sort records by release year, then by title.
				default :
					$query->set( 'meta_key', '_audiotheme_release_year' );
					$query->set( 'orderby', 'meta_value_num' );
					$query->set( 'order', 'desc' );
					add_filter( 'posts_orderby_request', array( $this, 'query_sort_sql' ) );
			}

			do_action_ref_array( 'audiotheme_record_query_sort', array( &$query ) );
		}
	}

	/**
	 * Sort records by title after sorting by release year.
	 *
	 * @since 1.3.0
	 *
	 * @param string $orderby SQL order clause.
	 * @return string
	 */
	public function query_sort_sql( $orderby ) {
		global $wpdb;

		return $orderby . ", {$wpdb->posts}.post_title ASC";
	}

	/**
	 * Filter the permalink for the discography archive.
	 *
	 * @since 1.0.0
	 * @uses audiotheme_discography_rewrite_base()
	 *
	 * @param string $link The default archive URL.
	 * @param string $post_type Post type.
	 * @return string The discography archive URL.
	 */
	public function archive_permalink( $link, $post_type ) {
		$permalink = get_option( 'permalink_structure' );
		if ( ! empty( $permalink ) && 'audiotheme_record' == $post_type ) {
			$link = home_url( '/' . $this->module->get_rewrite_base() . '/' );
		}

		return $link;
	}

	/**
	 * Filter discography permalinks to match the custom rewrite rules.
	 *
	 * Allows the standard WordPress API function get_permalink() to return the
	 * correct URL when used with a discography post type.
	 *
	 * @since 1.0.0
	 * @see get_post_permalink()
	 * @see audiotheme_discography_rewrite_base()
	 *
	 * @param string $post_link The default permalink.
	 * @param object $post_link The record or track to get the permalink for.
	 * @param bool $leavename Whether to keep the post name.
	 * @param bool $sample Is it a sample permalink.
	 * @return string The record or track permalink.
	 */
	public function post_permalinks( $post_link, $post, $leavename, $sample ) {
		global $wpdb;

		$base                = $this->module->get_rewrite_base();
		$is_draft_or_pending = isset( $post->post_status ) && in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft' ) );

		if ( ! $is_draft_or_pending ) {
			$permalink = get_option( 'permalink_structure' );

			if ( ! empty( $permalink ) && 'audiotheme_record' == get_post_type( $post ) ) {
				$slug = ( $leavename ) ? '%postname%' : $post->post_name;
				$post_link = home_url( sprintf( '/%s/%s/', $base, $slug ) );
			}
		}

		return $post_link;
	}

	/**
	 * Add classes to record posts on the archive page.
	 *
	 * @since 1.2.0
	 *
	 * @param array $classes Default post classes.
	 * @return array
	 */
	public function archive_post_class( $classes ) {
		global $wp_query;

		if ( $wp_query->is_main_query() && ( is_post_type_archive( 'audiotheme_record' ) || is_tax( 'audiotheme_record_type' ) ) ) {
			$classes[] = 'item';
		}

		return $classes;
	}

	/**
	 * Record update messages.
	 *
	 * @since 2.0.0
	 * @see /wp-admin/edit-form-advanced.php
	 *
	 * @param array $messages The array of existing post update messages.
	 * @return array
	 */
	public function post_updated_messages( $messages ) {
		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		if ( 'audiotheme_record' != $post_type ) {
			return $messages;
		}

		$messages['audiotheme_record'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Record updated.', 'audiotheme' ),
			2  => __( 'Custom field updated.', 'audiotheme' ),
			3  => __( 'Custom field deleted.', 'audiotheme' ),
			4  => __( 'Record updated.', 'audiotheme' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Record restored to revision from %s', 'audiotheme' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Record published.', 'audiotheme' ),
			7  => __( 'Record saved.', 'audiotheme' ),
			8  => __( 'Record submitted.', 'audiotheme' ),
			9  => sprintf( __( 'Record scheduled for: <strong>%1$s</strong>.', 'audiotheme' ),
				  // translators: Publish box date format, see http://php.net/date
				  date_i18n( __( 'M j, Y @ G:i', 'audiotheme' ), strtotime( $post->post_date ) ) ),
			10 => __( 'Record draft updated.', 'audiotheme' ),
		);

		if ( $post_type_object->publicly_queryable ) {
			$permalink = get_permalink( $post->ID );
			$preview_permalink = add_query_arg( 'preview', 'true', $permalink );

			$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View record', 'audiotheme' ) );
			$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview record', 'audiotheme' ) );

			$messages[ $post_type ][1]  .= $view_link;
			$messages[ $post_type ][6]  .= $view_link;
			$messages[ $post_type ][9]  .= $view_link;
			$messages[ $post_type ][8]  .= $preview_link;
			$messages[ $post_type ][10] .= $preview_link;
		}

		return $messages;
	}
}
