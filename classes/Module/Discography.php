<?php
/**
 * Discography module.
 *
 * @package AudioTheme\Core\Discography
 * @since 2.0.0
 */

namespace AudioTheme\Core\Module;

use AudioTheme\Core\Module;
use AudioTheme\Core\Util;

/**
 * Discography module class.
 *
 * @package AudioTheme\Core\Discography
 * @since 2.0.0
 */
class Discography extends Module {
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
		add_action( 'init',                           array( $this, 'register_post_types' ) );
		add_action( 'init',                           array( $this, 'register_taxonomies' ) );
		add_filter( 'generate_rewrite_rules',         array( $this, 'generate_rewrite_rules' ) );
		add_action( 'pre_get_posts',                  array( $this, 'record_query_sort' ) );
		add_action( 'pre_get_posts',                  array( $this, 'track_query' ) );
		add_action( 'pre_get_posts',                  array( $this, 'record_type_query' ), 9 );
		add_action( 'template_include',               array( $this, 'template_include' ) );
		add_filter( 'post_type_archive_link',         array( $this, 'archive_permalink' ), 10, 2 );
		add_filter( 'post_type_link',                 array( $this, 'post_permalinks' ), 10, 4 );
		add_filter( 'term_link',                      array( $this, 'genre_permalinks' ), 10, 3 );
		add_filter( 'post_type_archive_title',        array( $this, 'genre_archive_title' ) );
		add_filter( 'audiotheme_archive_description', array( $this, 'genre_archive_description' ) );
		add_filter( 'wp_unique_post_slug',            array( $this, 'track_unique_slug' ), 10, 6 );
		add_action( 'wp_print_footer_scripts',        array( $this, 'print_tracks_js' ) );
		add_filter( 'post_class',                     array( $this, 'record_archive_post_class' ) );
	}

	/**
	 * Register post types.
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
			'has_archive'            => $this->get_rewrite_base(),
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

		$labels = array(
			'name'               => _x( 'Tracks', 'post format general name', 'audiotheme' ),
			'singular_name'      => _x( 'Track', 'post format singular name', 'audiotheme' ),
			'add_new'            => _x( 'Add New', 'audiotheme_track', 'audiotheme' ),
			'add_new_item'       => __( 'Add New Track', 'audiotheme' ),
			'edit_item'          => __( 'Edit Track', 'audiotheme' ),
			'new_item'           => __( 'New Track', 'audiotheme' ),
			'view_item'          => __( 'View Track', 'audiotheme' ),
			'search_items'       => __( 'Search Tracks', 'audiotheme' ),
			'not_found'          => __( 'No tracks found.', 'audiotheme' ),
			'not_found_in_trash' => __( 'No tracks found in Trash.', 'audiotheme' ),
			'all_items'          => __( 'All Tracks', 'audiotheme' ),
			'menu_name'          => __( 'Tracks', 'audiotheme' ),
			'name_admin_bar'     => _x( 'Track', 'add new on admin bar', 'audiotheme' ),
		);

		$args = array(
			'capability_type'        => 'post',
			'has_archive'            => false,
			'hierarchical'           => false,
			'labels'                 => $labels,
			'public'                 => true,
			'publicly_queryable'     => true,
			'rewrite'                => false,
			'show_ui'                => true,
			'show_in_admin_bar'      => true,
			'show_in_menu'           => 'edit.php?post_type=audiotheme_record',
			'show_in_nav_menus'      => true,
			'supports'               => array( 'title', 'editor', 'thumbnail' ),
		);

		register_post_type( 'audiotheme_track', $args );

		// Register the archive.
		$this->archives->add_post_type_archive( 'audiotheme_record' );
	}

	/**
	 * Register taxonomies.
	 *
	 * @since 2.0.0
	 */
	public function register_taxonomies() {
		$labels = array(
			'name'                       => _x( 'Record Types', 'taxonomy general name', 'audiotheme' ),
			'singular_name'              => _x( 'Record Type', 'taxonomy singular name', 'audiotheme' ),
			'search_items'               => __( 'Search Record Types', 'audiotheme' ),
			'popular_items'              => __( 'Popular Record Types', 'audiotheme' ),
			'all_items'                  => __( 'All Record Types', 'audiotheme' ),
			'parent_item'                => __( 'Parent Record Type', 'audiotheme' ),
			'parent_item_colon'          => __( 'Parent Record Type:', 'audiotheme' ),
			'edit_item'                  => __( 'Edit Record Type', 'audiotheme' ),
			'view_item'                  => __( 'View Record Type', 'audiotheme' ),
			'update_item'                => __( 'Update Record Type', 'audiotheme' ),
			'add_new_item'               => __( 'Add New Record Type', 'audiotheme' ),
			'new_item_name'              => __( 'New Record Type Name', 'audiotheme' ),
			'separate_items_with_commas' => __( 'Separate record types with commas', 'audiotheme' ),
			'add_or_remove_items'        => __( 'Add or remove record types', 'audiotheme' ),
			'choose_from_most_used'      => __( 'Choose from most used record types', 'audiotheme' ),
		);

		$args = array(
			'args'                           => array( 'orderby' => 'term_order' ),
			'hierarchical'                   => false,
			'labels'                         => $labels,
			'meta_box_cb'                    => 'audiotheme_taxonomy_checkbox_list_meta_box',
			'public'                         => true,
			'query_var'                      => true,
			'rewrite'                        => array(
				'slug'                       => $this->get_rewrite_base() . '/type',
				'with_front'                 => false,
			),
			'show_ui'                        => true,
			'show_admin_column'              => true,
			'show_in_nav_menus'              => true,
			'show_tagcloud'                  => false,
		);

		register_taxonomy( 'audiotheme_record_type', 'audiotheme_record', $args );

		$labels = array(
			'name'                       => _x( 'Genres', 'taxonomy general name', 'atgenres' ),
			'singular_name'              => _x( 'Genre', 'taxonomy singular name', 'atgenres' ),
			'search_items'               => __( 'Search Genres', 'atgenres' ),
			'popular_items'              => __( 'Popular Genres', 'atgenres' ),
			'all_items'                  => __( 'All Genres', 'atgenres' ),
			'parent_item'                => __( 'Parent Genre', 'atgenres' ),
			'parent_item_colon'          => __( 'Parent Genre:', 'atgenres' ),
			'edit_item'                  => __( 'Edit Genre', 'atgenres' ),
			'view_item'                  => __( 'View Genre', 'atgenres' ),
			'update_item'                => __( 'Update Genre', 'atgenres' ),
			'add_new_item'               => __( 'Add New Genre', 'atgenres' ),
			'new_item_name'              => __( 'New Genre Name', 'atgenres' ),
			'separate_items_with_commas' => __( 'Separate genres with commas', 'atgenres' ),
			'add_or_remove_items'        => __( 'Add or remove genres', 'atgenres' ),
			'choose_from_most_used'      => __( 'Choose from most used genres', 'atgenres' ),
			'menu_name'                  => __( 'Genres', 'atgenres' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'meta_box_cb'       => 'audiotheme_taxonomy_checkbox_list_meta_box',
			'public'            => true,
			'query_var'         => true,
			'rewrite'           => false,
			'show_admin_column' => true,
			'show_ui'           => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
		);

		register_taxonomy( 'audiotheme_genre', array( 'audiotheme_record' ), $args );
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
	 * Sort record archive requests.
	 *
	 * Defaults to sorting by release year in descending order. An option is
	 * available on the archive page to sort by title or a custom order. The custom
	 * order using the 'menu_order' value, which can be set using a plugin like
	 * Simple Page Ordering.
	 *
	 * Alternatively, a plugin can hook into pre_get_posts at an earlier priority
	 * and manually set the order.
	 *
	 * @since 1.3.0
	 *
	 * @param object $query The main WP_Query object. Passed by reference.
	 */
	public function record_query_sort( $query ) {
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
					add_filter( 'posts_orderby_request', array( $this, 'record_query_sort_sql' ) );
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
	public function record_query_sort_sql( $orderby ) {
		global $wpdb;

		return $orderby . ", {$wpdb->posts}.post_title ASC";
	}

	/**
	 * Set record type requests to use the same archive settings as records.
	 *
	 * @since 2.0.0
	 */
	public function record_type_query( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! is_tax( 'audiotheme_record_type' ) ) {
			return;
		}

		$this->archives->set_current_archive_post_type( 'audiotheme_record' );
	}

	/**
	 * Filter track requests.
	 *
	 * Tracks must belong to a record, so the parent record is set for track
	 * requests.
	 *
	 * @since 1.3.0
	 *
	 * @param object $query The main WP_Query object. Passed by reference.
	 */
	public function track_query( $query ) {
		global $wpdb;

		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		// Limit requests for single tracks to the context of the parent record.
		if ( is_single() && 'audiotheme_track' == $query->get( 'post_type' ) ) {
			if ( get_option( 'permalink_structure' ) ) {
				$record_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='audiotheme_record' AND post_name=%s LIMIT 1", $query->get( 'audiotheme_record' ) ) );
				if ( $record_id ) {
					$query->set( 'post_parent', $record_id );
				}
			} elseif ( ! empty( $_GET['post_parent'] ) ) {
				$query->set( 'post_parent', absint( $_GET['post_parent'] ) );
			}
		}
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
			$compat->set_loop_template_part( 'parts/loop-archive', 'record' );
		} elseif ( is_singular( 'audiotheme_record' ) ) {
			$templates = array( 'single-record.php' );

			$template = $template_loader->locate_template( $templates );
			$compat->set_title( get_queried_object()->post_title );
			$compat->set_loop_template_part( 'parts/loop-single', 'record' );
		} elseif ( is_singular( 'audiotheme_track' ) ) {
			$template = $template_loader->locate_template( 'single-track.php' );
			$compat->set_title( get_queried_object()->post_title );
			$compat->set_loop_template_part( 'parts/loop-single', 'track' );
		}

		if ( $template !== $original_template ) {
			$template = $this->get_compatible_template( $template );
			do_action( 'audiotheme_template_include', $template );
		}

		return $template;
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
		if ( ! empty( $permalink ) && ( 'audiotheme_record' == $post_type || 'audiotheme_track' == $post_type ) ) {
			$link = home_url( '/' . $this->get_rewrite_base() . '/' );
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

		$base                = $this->get_rewrite_base();
		$is_draft_or_pending = isset( $post->post_status ) && in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft' ) );

		if ( ! $is_draft_or_pending ) {
			$permalink = get_option( 'permalink_structure' );

			if ( ! empty( $permalink ) && 'audiotheme_record' == get_post_type( $post ) ) {
				$slug = ( $leavename ) ? '%postname%' : $post->post_name;
				$post_link = home_url( sprintf( '/%s/%s/', $base, $slug ) );
			}

			if ( ! empty( $permalink ) && 'audiotheme_track' == get_post_type( $post ) && ! empty( $post->post_parent ) ) {
				$slug = ( $leavename ) ? '%postname%' : $post->post_name;
				$record = get_post( $post->post_parent );
				if ( $record ) {
					$post_link = home_url( sprintf( '/%s/%s/track/%s/', $base, $record->post_name, $slug ) );
				}
			} elseif ( empty( $permalink ) && 'audiotheme_track' == get_post_type( $post ) && ! empty( $post->post_parent ) ) {
				$post_link = add_query_arg( 'post_parent', $post->post_parent, $post_link );
			}
		}

		return $post_link;
	}

	/**
	 * Filter genre term permalinks.
	 *
	 * @since 2.0.0
	 *
	 * @param string $link Term permalink.
	 * @param object $term Term object.
	 * @param string $taxonomy Taxonomy name.
	 * @return string
	 */
	public function genre_permalinks( $link, $term, $taxonomy ) {
		if ( 'audiotheme_genre' == $taxonomy && get_option( 'permalink_structure' ) ) {
			$link = home_url( sprintf( '/%s/genre/%s/', $this->get_rewrite_base(), $term->slug ) );
		}
		return $link;
	}

	/**
	 * Filter archive titles to display the genre name.
	 *
	 * @since 2.0.0
	 *
	 * @param string $title Archive title.
	 * @return string
	 */
	public function genre_archive_title( $title ) {
		if ( is_tax( 'audiotheme_genre' ) ) {
			$title = single_term_title( '', false );
		}
		return $title;
	}

	/**
	 * Filter archive descriptions to display the genre term description.
	 *
	 * @since 2.0.0
	 *
	 * @param string $description Archive description.
	 * @return string
	 */
	public function genre_archive_description( $description ) {
		if ( is_tax( 'audiotheme_genre' ) ) {
			$description = term_description();
		}
		return $description;
	}

	/**
	 * Ensure track slugs are unique.
	 *
	 * Tracks should always be associated with a record so their slugs only need
	 * to be unique within the context of a record.
	 *
	 * @since 1.0.0
	 * @see wp_unique_post_slug()
	 *
	 * @param string $slug The desired slug (post_name).
	 * @param integer $post_ID
	 * @param string $post_status No uniqueness checks are made if the post is still draft or pending.
	 * @param string $post_type
	 * @param integer $post_parent
	 * @param string $original_slug Slug passed to the uniqueness method.
	 * @return string
	 */
	public function track_unique_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug = null ) {
		global $wpdb, $wp_rewrite;

		if ( 'audiotheme_track' == $post_type ) {
			$slug = $original_slug;

			$feeds = $wp_rewrite->feeds;
			if ( ! is_array( $feeds ) ) {
				$feeds = array();
			}

			// Make sure the track slug is unique within the context of the record only.
			$check_sql = "SELECT post_name FROM $wpdb->posts WHERE post_name=%s AND post_type=%s AND post_parent=%d AND ID!=%d LIMIT 1";
			$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $slug, $post_type, $post_parent, $post_ID ) );

			if ( $post_name_check || apply_filters( 'wp_unique_post_slug_is_bad_flat_slug', false, $slug, $post_type ) ) {
				$suffix = 2;
				do {
					$alt_post_name = substr( $slug, 0, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
					$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $alt_post_name, $post_type, $post_parent, $post_ID ) );
					$suffix++;
				} while ( $post_name_check );
				$slug = $alt_post_name;
			}
		}

		return $slug;
	}

	/**
	 * Transform a track id or array of data into the expected format for use as a
	 * JavaScript object.
	 *
	 * @since 1.1.0
	 *
	 * @param int|array $track Track ID or array of expected track properties.
	 * @return array
	 */
	public function prepare_track_for_js( $track ) {
		$data = array(
			'artist'  => '',
			'artwork' => '',
			'mp3'     => '',
			'record'  => '',
			'title'   => '',
		);

		// Enqueue a track post type.
		if ( 'audiotheme_track' == get_post_type( $track ) ) {
			$track = get_post( $track );
			$record = get_post( $track->post_parent );

			$data['artist'] = get_audiotheme_track_artist( $track->ID );
			$data['mp3'] = get_audiotheme_track_file_url( $track->ID );
			$data['record'] = $record->post_title;
			$data['title'] = $track->post_title;

			// WP playlist format.
			$data['format'] = 'mp3';
			$data['meta']['artist'] = $data['artist'];
			$data['meta']['length_formatted'] = '0:00';
			$data['src'] = $data['mp3'];

			if ( $thumbnail_id = get_audiotheme_track_thumbnail_id( $track ) ) {
				$image = wp_get_attachment_image_src( $thumbnail_id, apply_filters( 'audiotheme_track_js_artwork_size', 'thumbnail' ) );
				$data['artwork'] = $image[0];
			}
		}

		// Add the track data directly.
		elseif ( is_array( $track ) ) {
			if ( isset( $track['artwork'] ) ) {
				$data['artwork'] = esc_url( $track['artwork'] );
			}

			if ( isset( $track['file'] ) ) {
				$data['mp3'] = esc_url_raw( Util::encode_url_path( $track['file'] ) );
			}

			if ( isset( $track['mp3'] ) ) {
				$data['mp3'] = esc_url_raw( Util::encode_url_path( $track['mp3'] ) );
			}

			if ( isset( $track['title'] ) ) {
				$data['title'] = wp_strip_all_tags( $track['title'] );
			}

			$data = array_merge( $track, $data );
		}

		$data = apply_filters( 'audiotheme_track_js_data', $data, $track );

		return $data;
	}

	/**
	 * Convert enqueued track lists into an array of tracks prepared for JavaScript
	 * and output the JSON-encoded object in the footer.
	 *
	 * @since 1.1.0
	 */
	public function print_tracks_js() {
		global $audiotheme_enqueued_tracks;

		if ( empty( $audiotheme_enqueued_tracks ) || ! is_array( $audiotheme_enqueued_tracks ) ) {
			return;
		}

		$lists = array();

		// @todo The track & record ids should be collected at some point so they can all be fetched in a single query.

		foreach ( $audiotheme_enqueued_tracks as $list => $tracks ) {
			if ( empty( $tracks ) || ! is_array( $tracks ) ) {
				continue;
			}

			do_action( 'audiotheme_prepare_tracks', $list );

			foreach ( $tracks as $track ) {
				if ( 'audiotheme_record' == get_post_type( $track ) ) {
					$record_tracks = get_audiotheme_record_tracks( $track, array( 'has_file' => true ) );

					if ( $record_tracks ) {
						foreach ( $record_tracks as $record_track ) {
							if ( $track_data = $this->prepare_track_for_js( $record_track ) ) {
								$lists[ $list ][] = $track_data;
							}
						}
					}
				} elseif ( $track_data = $this->prepare_track_for_js( $track ) ) {
					$lists[ $list ][] = $track_data;
				}
			}
		}

		// Print a JavaScript object.
		if ( ! empty( $lists ) ) {
			?>
			<script type="text/javascript">
			/* <![CDATA[ */
			window.AudiothemeTracks = window.AudiothemeTracks || {};

			(function( window ) {
				var tracks = <?php echo json_encode( $lists ); ?>,
					i;

				for ( i in tracks ) {
					window.AudiothemeTracks[ i ] = tracks[ i ];
				}
			})( this );
			/* ]]> */
			</script>
			<?php
		}
	}

	/**
	 * Add classes to record posts on the archive page.
	 *
	 * @since 1.2.0
	 *
	 * @param array $classes Default post classes.
	 * @return array
	 */
	public function record_archive_post_class( $classes ) {
		global $wp_query;

		if ( $wp_query->is_main_query() && ( is_post_type_archive( 'audiotheme_record' ) || is_tax( 'audiotheme_record_type' ) ) ) {
			$classes[] = 'item';
		}

		return $classes;
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
}
