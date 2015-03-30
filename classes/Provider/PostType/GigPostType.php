<?php
/**
 * Gig post type registration and integration.
 *
 * @package AudioTheme\Core
 * @since 2.0.0
 */

namespace AudioTheme\Core\Provider\PostType;

use AudioTheme\Core\HookProviderInterface;
use AudioTheme\Core\Plugin;
use AudioTheme\Core\Util;

/**
 * Class for registering the gig post type and integration.
 *
 * @package AudioTheme\Core
 * @since   2.0.0
 */
class GigPostType implements HookProviderInterface {
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
		add_action( 'init',                     array( $this, 'register_post_type' ) );
		add_filter( 'query_vars',               array( $this, 'register_query_vars' ) );
		add_action( 'pre_get_posts',            array( $this, 'query' ) );
		add_filter( 'post_type_archive_link',   array( $this, 'archive_permalink' ), 10, 2 );
		add_filter( 'post_type_link',           array( $this, 'post_permalinks' ), 10, 4 );
		add_filter( 'wp_unique_post_slug',      array( $this, 'get_unique_slug' ), 10, 6 );
		add_action( 'save_post_audiotheme_gig', array( $this, 'update_bad_slug' ), 20, 2 );
		add_filter( 'post_class',               array( $this, 'post_class' ), 10, 3 );
		add_action( 'before_delete_post',       array( $this, 'on_before_delete' ) );
		add_filter( 'post_updated_messages',    array( $this, 'post_updated_messages' ) );
	}

	/**
	 * Register the gig post type.
	 *
	 * @since 2.0.0
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Gigs', 'post type general name', 'audiotheme' ),
			'singular_name'      => _x( 'Gig', 'post type singular name', 'audiotheme' ),
			'add_new'            => _x( 'Add New', 'gig', 'audiotheme' ),
			'add_new_item'       => __( 'Add New Gig', 'audiotheme' ),
			'edit_item'          => __( 'Edit Gig', 'audiotheme' ),
			'new_item'           => __( 'New Gig', 'audiotheme' ),
			'view_item'          => __( 'View Gig', 'audiotheme' ),
			'search_items'       => __( 'Search Gigs', 'audiotheme' ),
			'not_found'          => __( 'No gigs found', 'audiotheme' ),
			'not_found_in_trash' => __( 'No gigs found in Trash', 'audiotheme' ),
			'all_items'          => __( 'All Gigs', 'audiotheme' ),
			'menu_name'          => __( 'Gigs', 'audiotheme' ),
			'name_admin_bar'     => _x( 'Gigs', 'add new on admin bar', 'audiotheme' ),
		);

		$args = array(
			'has_archive'            => $this->module->get_rewrite_base(),
			'hierarchical'           => false,
			'labels'                 => $labels,
			'menu_position'          => 512,
			'public'                 => true,
			'rewrite'                => false,
			'show_in_admin_bar'      => true,
			'show_in_menu'           => 'audiotheme-gigs',
			'show_in_nav_menus'      => false,
			'supports'               => array( 'title', 'editor', 'thumbnail' ),
		);

		register_post_type( 'audiotheme_gig', $args );

		// Register the archive.
		$this->plugin['archives']->add_post_type_archive( 'audiotheme_gig', array(
			'admin_menu_parent' => 'audiotheme-gigs',
		) );
	}

	/**
	 * Register query variables.
	 *
	 * @since 2.0.0
	 *
	 * @param array $vars Array of valid query variables.
	 * @return array
	 */
	public function register_query_vars( $vars ) {
		$vars[] = 'audiotheme_gig_range';
		return $vars;
	}

	/**
	 * Filter gigs requests.
	 *
	 * Automatically sorts gigs in ascending order by the gig date, but limits
	 * to showing upcoming gigs unless a specific date range is requested (year,
	 * month, day).
	 *
	 * @since 1.0.0
	 *
	 * @param object $query The main WP_Query object. Passed by reference.
	 */
	public function query( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! is_post_type_archive( 'audiotheme_gig' ) ) {
			return;
		}

		$orderby = $query->get( 'orderby' );
		if ( ! empty( $orderby ) ) {
			return;
		}

		$query->set( 'posts_per_archive_page', -1 );
		$query->set( 'meta_key', '_audiotheme_gig_datetime' );
		$query->set( 'orderby', 'meta_value' );
		$query->set( 'order', 'asc' );

		if ( is_date() ) {
			if ( is_day() ) {
				$d = absint( $query->get( 'day' ) );
				$m = absint( $query->get( 'monthnum' ) );
				$y = absint( $query->get( 'year' ) );

				$start = sprintf( '%s-%s-%s 00:00:00', $y, zeroise( $m, 2 ), zeroise( $d, 2 ) );
				$end = sprintf( '%s-%s-%s 23:59:59', $y, zeroise( $m, 2 ), zeroise( $d, 2 ) );
			} elseif ( is_month() ) {
				$m = absint( $query->get( 'monthnum' ) );
				$y = absint( $query->get( 'year' ) );

				$start = sprintf( '%s-%s-01 00:00:00', $y, zeroise( $m, 2 ) );
				$end = sprintf( '%s 23:59:59', date( 'Y-m-t', mktime( 0, 0, 0, $m, 1, $y ) ) );
			} elseif ( is_year() ) {
				$y = absint( $query->get( 'year' ) );

				$start = sprintf( '%s-01-01 00:00:00', $y );
				$end = sprintf( '%s-12-31 23:59:59', $y );
			}

			if ( isset( $start ) && isset( $end ) ) {
				$meta_query[] = array(
					'key'     => '_audiotheme_gig_datetime',
					'value'   => array( $start, $end ),
					'compare' => 'BETWEEN',
					'type'    => 'DATETIME',
				);

				$query->set( 'day', null );
				$query->set( 'monthnum', null );
				$query->set( 'year', null );
			}
		} elseif ( 'past' == $query->get( 'audiotheme_gig_range' ) ){
			$meta_query[] = array(
				'key'     => '_audiotheme_gig_datetime',
				'value'   => date( 'Y-m-d', current_time( 'timestamp' ) ),
				'compare' => '<',
				'type'    => 'DATETIME',
			);

			$query->set( 'posts_per_archive_page', null );
			$query->set( 'order', 'desc' );
		} else {
			// Only show upcoming gigs.
			$meta_query[] = array(
				'key'     => '_audiotheme_gig_datetime',
				'value'   => date( 'Y-m-d', current_time( 'timestamp' ) ),
				'compare' => '>=',
				'type'    => 'DATETIME',
			);
		}

		if ( isset( $meta_query ) ) {
			$query->set( 'meta_query', $meta_query );
		}
	}

	/**
	 * Filter the permalink for the gigs archive.
	 *
	 * @since 1.0.0
	 * @uses audiotheme_gigs_rewrite_base()
	 *
	 * @param string $link The default archive URL.
	 * @param string $post_type Post type.
	 * @return string The gig archive URL.
	 */
	public function archive_permalink( $link, $post_type ) {
		if ( 'audiotheme_gig' == $post_type && get_option( 'permalink_structure' ) ) {
			$base = $this->module->get_rewrite_base();
			$link = home_url( '/' . $base . '/' );
		} elseif ( 'audiotheme_gig' == $post_type ) {
			$link = add_query_arg( 'post_type', 'audiotheme_gig', home_url( '/' ) );
		}

		return $link;
	}

	/**
	 * Filter gig permalinks to match the custom rewrite rules.
	 *
	 * Allows the standard WordPress API function get_permalink() to return the
	 * correct URL when used with a gig post type.
	 *
	 * @since 1.0.0
	 * @see get_post_permalink()
	 * @see audiotheme_gigs_rewrite_base()
	 *
	 * @param string $post_link The default gig URL.
	 * @param object $post_link The gig to get the permalink for.
	 * @param bool $leavename Whether to keep the post name.
	 * @param bool $sample Is it a sample permalink.
	 * @return string The gig permalink.
	 */
	public function post_permalinks( $post_link, $post, $leavename, $sample ) {
		$is_draft_or_pending = isset( $post->post_status ) && in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft' ) );

		if ( ! $is_draft_or_pending || $sample ) {
			$permalink = get_option( 'permalink_structure' );

			if ( ! empty( $permalink ) && 'audiotheme_gig' == get_post_type( $post ) ) {
				$base = $this->module->get_rewrite_base();
				$slug = ( $leavename ) ? '%postname%' : $post->post_name;

				$post_link = home_url( sprintf( '/%s/%s/', $base, $slug ) );
			}
		}

		return $post_link;
	}

	/**
	 * Prevent conflicts in gig permalinks.
	 *
	 * Gigs without titles will fall back to using the ID for the slug, however,
	 * when the ID is a 4 digit number, it will conflict with date-based
	 * permalinks.
	 *
	 * Any slugs that match the ID will default to the gig date if one has been
	 * saved, otherwise the ID will be prepended with 'gig-'.
	 *
	 * @since 1.6.1
	 * @see wp_unique_post_slug()
	 *
	 * @param string $slug The desired slug (post_name).
	 * @param integer $post_id
	 * @param string $post_status No uniqueness checks are made if the post is still draft or pending.
	 * @param string $post_type
	 * @param integer $post_parent
	 * @param string $original_slug Slug passed to the uniqueness method.
	 * @return string
	 */
	public function get_unique_slug( $slug, $post_id, $post_status, $post_type, $post_parent, $original_slug = null ) {
		global $wpdb, $wp_rewrite;

		if ( 'audiotheme_gig' == $post_type ) {
			$slug = $original_slug;

			$feeds = $wp_rewrite->feeds;
			if ( ! is_array( $feeds ) ) {
				$feeds = array();
			}

			// Four-digit numeric slugs interfere with date-based archives.
			if ( $slug == $post_id ) {
				$slug = 'gig-' . $slug;

				// If a date is set, default to the date rather than the post id.
				$datetime = get_post_meta( $post_id, '_audiotheme_gig_datetime', true );
				if ( ! empty( $datetime ) ) {
					$dt = date_parse( $datetime );
					$slug = sprintf( '%s-%s-%s', $dt['year'], zeroise( $dt['month'], 2 ), zeroise( $dt['day'], 2 ) );
				}
			}

			// Make sure the gig slug is unique.
			$check_sql = "SELECT post_name FROM $wpdb->posts WHERE post_name=%s AND post_type=%s AND ID!=%d LIMIT 1";
			$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $slug, $post_type, $post_id ) );

			if ( $post_name_check || apply_filters( 'wp_unique_post_slug_is_bad_flat_slug', false, $slug, $post_type ) ) {
				$suffix = 2;
				do {
					$alt_post_name = substr( $slug, 0, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
					$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $alt_post_name, $post_type, $post_id ) );
					$suffix++;
				} while ( $post_name_check );
				$slug = $alt_post_name;
			}
		}

		return $slug;
	}

	/**
	 * Prevent conflicts with numeric gig slugs.
	 *
	 * If a slug is empty when a post is published, wp_insert_post() will base
	 * the slug off the title/ID without a way to filter it until after the post
	 * is saved.
	 *
	 * @since 1.6.1
	 *
	 * @param int $post_id Post ID.
	 * @param WP_Post $post Post object.
	 */
	public function update_bad_slug( $post_id, $post ) {
		global $wpdb;

		if ( 'audiotheme_gig' !== $post->post_type ) {
			return;
		}

		if ( $post->post_name == $post_id && ! in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft' ) ) ) {
			$slug = audiotheme_gig_unique_slug(
				'gig-' . $post_id,
				$post_id,
				$post->post_status,
				$post->post_type,
				$post->post_parent,
				$post_id
			);

			$wpdb->update( $wpdb->posts, array( 'post_name' => $slug ), array( 'ID' => $post_id ) );
		}
	}

	/**
	 * Update a venue's cached gig count when gig is deleted.
	 *
	 * Determines if a venue's gig_count meta field needs to be updated when a
	 * gig is deleted.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id ID of the gig being deleted.
	 */
	public function on_before_delete( $post_id ) {
		if ( 'audiotheme_gig' == get_post_type( $post_id ) ) {
			$gig = get_audiotheme_gig( $post_id );
			if ( isset( $gig->venue->ID ) ) {
				$count = get_audiotheme_venue_gig_count( $gig->venue->ID );
				update_audiotheme_venue_gig_count( $gig->venue->ID, --$count );
			}
		}
	}

	/**
	 * Add useful classes to gig posts.
	 *
	 * @since 1.1.0
	 *
	 * @param array $classes List of classes.
	 * @param string|array $class One or more classes to add to the class list.
	 * @param int $post_id An optional post ID.
	 * @return array Array of classes.
	 */
	public function post_class( $classes, $class, $post_id ) {
		if ( 'audiotheme_gig' == get_post_type( $post_id ) && audiotheme_gig_has_ticket_meta() ) {
			$classes[] = 'has-audiotheme-ticket-meta';
		}

		return $classes;
	}

	/**
	 * Gig update messages.
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

		if ( 'audiotheme_gig' != $post_type ) {
			return;
		}

		$messages['audiotheme_gig'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => sprintf( __( 'Gig updated. <a href="%s">View Gig</a>', 'audiotheme' ), esc_url( get_permalink( $post->ID ) ) ),
			2  => __( 'Custom field updated.', 'audiotheme' ),
			3  => __( 'Custom field deleted.', 'audiotheme' ),
			4  => __( 'Gig updated.', 'audiotheme' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Gig restored to revision from %s', 'audiotheme' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => sprintf( __( 'Gig published. <a href="%s">View Gig</a>', 'audiotheme' ), esc_url( get_permalink( $post->ID ) ) ),
			7  => __( 'Gig saved.', 'audiotheme' ),
			8  => sprintf( __( 'Gig submitted. <a target="_blank" href="%s">Preview Gig</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
			9  => sprintf( __( 'Gig scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Gig</a>', 'audiotheme' ),
				  /* translators: Publish box date format, see http://php.net/date */
				  date_i18n( __( 'M j, Y @ G:i', 'audiotheme' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post->ID ) ) ),
			10 => sprintf( __( 'Gig draft updated. <a target="_blank" href="%s">Preview Gig</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
		);

		return $messages;
	}
}
