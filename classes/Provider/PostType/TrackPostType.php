<?php
/**
 * Track post type registration and integration.
 *
 * @package AudioTheme\Core
 * @since 2.0.0
 */

namespace AudioTheme\Core\Provider\PostType;

use AudioTheme\Core\HookProviderInterface;
use AudioTheme\Core\Plugin;

/**
 * Class for registering the track post type and integration.
 *
 * @package AudioTheme\Core
 * @since   2.0.0
 */
class TrackPostType implements HookProviderInterface {
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
		add_action( 'init',                    array( $this, 'register_post_type' ) );
		add_action( 'pre_get_posts',           array( $this, 'track_query' ) );
		add_filter( 'post_type_archive_link',  array( $this, 'archive_permalink' ), 10, 2 );
		add_filter( 'post_type_link',          array( $this, 'post_permalinks' ), 10, 4 );
		add_filter( 'wp_unique_post_slug',     array( $this, 'unique_slug' ), 10, 6 );
		add_action( 'wp_print_footer_scripts', array( $this, 'print_tracks_js' ) );
		add_filter( 'post_updated_messages',   array( $this, 'post_updated_messages' ) );
	}

	/**
	 * Register the track post type.
	 *
	 * @since 2.0.0
	 */
	public function register_post_type() {
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
		if ( ! empty( $permalink ) && 'audiotheme_track' == $post_type ) {
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
	public function unique_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug = null ) {
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
	 * Transform a track id or array of data into the expected format for use as
	 * an object in JavaScript.
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
	 * Convert enqueued track lists into an array of tracks prepared for
	 * JavaScript and output the JSON-encoded object in the footer.
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
	 * Track update messages.
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

		if ( 'audiotheme_track' != $post_type ) {
			return $messages;
		}

		$messages['audiotheme_track'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Track updated.', 'audiotheme' ),
			2  => __( 'Custom field updated.', 'audiotheme' ),
			3  => __( 'Custom field deleted.', 'audiotheme' ),
			4  => __( 'Track updated.', 'audiotheme' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Track restored to revision from %s', 'audiotheme' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Track published.', 'audiotheme' ),
			7  => __( 'Track saved.', 'audiotheme' ),
			8  => __( 'Track submitted.', 'audiotheme' ),
			9  => sprintf( __( 'Track scheduled for: <strong>%1$s</strong>.', 'audiotheme' ),
				  // translators: Publish box date format, see http://php.net/date
				  date_i18n( __( 'M j, Y @ G:i', 'audiotheme' ), strtotime( $post->post_date ) ) ),
			10 => __( 'Track draft updated.', 'audiotheme' ),
		);

		if ( $post_type_object->publicly_queryable ) {
			$permalink = get_permalink( $post->ID );
			$preview_permalink = add_query_arg( 'preview', 'true', $permalink );

			$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View track', 'audiotheme' ) );
			$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview track', 'audiotheme' ) );

			$messages[ $post_type ][1]  .= $view_link;
			$messages[ $post_type ][6]  .= $view_link;
			$messages[ $post_type ][9]  .= $view_link;
			$messages[ $post_type ][8]  .= $preview_link;
			$messages[ $post_type ][10] .= $preview_link;
		}

		return $messages;
	}
}
