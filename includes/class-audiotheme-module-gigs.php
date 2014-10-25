<?php
/**
 * Gigs module.
 *
 * @package AudioTheme\Gigs
 * @since 2.0.0
 */

/**
 * Gigs module class.
 *
 * @package AudioTheme\Gigs
 * @since 2.0.0
 */
class AudioTheme_Module_Gigs extends AudioTheme_Module {
	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args( array(
			'id'             => 'gigs',
			'name'           => __( 'Gigs', 'audiotheme' ),
			'description'    => __( '', 'audiotheme' ),
			'is_core_module' => true,
			'admin_menu_id'  => 'toplevel_page_audiotheme-gigs',
		), $args );

		parent::__construct( $args );
	}

	/**
	 * Load the Gigs module.
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
		add_action( 'init',                     array( $this, 'register_post_types' ) );
		add_filter( 'generate_rewrite_rules',   array( $this, 'generate_rewrite_rules' ) );
		add_filter( 'query_vars',               array( $this, 'register_query_vars' ) );
		add_action( 'pre_get_posts',            array( $this, 'gig_query' ) );
		add_action( 'template_redirect',        array( $this, 'template_redirect' ) );
		add_action( 'template_include',         array( $this, 'template_include' ) );
		add_filter( 'post_type_link',           array( $this, 'post_permalinks' ), 10, 4 );
		add_filter( 'post_type_archive_link',   array( $this, 'archive_permalink' ), 10, 2 );
		add_filter( 'wp_unique_post_slug',      array( $this, 'get_unique_gig_slug' ), 10, 6 );
		add_action( 'save_post_audiotheme_gig', array( $this, 'update_bad_gig_slug' ), 20, 2 );
		add_filter( 'get_edit_post_link',       'get_audiotheme_venue_edit_link', 10, 2 );
		add_filter( 'post_class',               array( $this, 'gig_post_class' ), 10, 3 );
		add_action( 'before_delete_post',       array( $this, 'on_before_delete_gig' ) );
	}

	/**
	 * Register post types.
	 *
	 * @since 2.0.0
	 */
	public function register_post_types() {
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
			'has_archive'            => $this->get_rewrite_base(),
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

		$labels = array(
			'name'               => _x( 'Venues', 'post type general name', 'audiotheme' ),
			'singular_name'      => _x( 'Venue', 'post type singular name', 'audiotheme' ),
			'add_new'            => _x( 'Add New', 'venue', 'audiotheme' ),
			'add_new_item'       => __( 'Add New Venue', 'audiotheme' ),
			'edit_item'          => __( 'Edit Venue', 'audiotheme' ),
			'new_item'           => __( 'New Venue', 'audiotheme' ),
			'view_item'          => __( 'View Venue', 'audiotheme' ),
			'search_items'       => __( 'Search Venues', 'audiotheme' ),
			'not_found'          => __( 'No venues found', 'audiotheme' ),
			'not_found_in_trash' => __( 'No venues found in Trash', 'audiotheme' ),
			'all_items'          => __( 'All Venues', 'audiotheme' ),
			'menu_name'          => __( 'Venues', 'audiotheme' ),
			'name_admin_bar'     => _x( 'Venues', 'add new on admin bar', 'audiotheme' ),
		);

		$args = array(
			'has_archive'            => false,
			'hierarchical'           => false,
			'labels'                 => $labels,
			'public'                 => false,
			'publicly_queryable'     => false,
			'query_var'              => 'audiotheme_venue',
			'rewrite'                => false,
			'supports'               => array( '' ),
		);

		register_post_type( 'audiotheme_venue', $args );

		// Register the archive.
		$this->archives->add_post_type_archive( 'audiotheme_gig', array(
			'admin_menu_parent' => 'audiotheme-gigs',
		) );

		// Register the relationship between gigs and venues.
		p2p_register_connection_type( array(
			'name'        => 'audiotheme_venue_to_gig',
			'from'        => 'audiotheme_venue',
			'to'          => 'audiotheme_gig',
			'cardinality' => 'one-to-many',
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
	 * Get the videos rewrite base. Defaults to 'videos'.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_rewrite_base() {
		global $wp_rewrite;

		$front = '';
		$base  = get_option( 'audiotheme_gig_rewrite_base', 'shows' );

		if ( $wp_rewrite->using_index_permalinks() ) {
			$front = $wp_rewrite->index . '/';
		}

		return $front . $base;
	}

	/**
	 * Filter gigs requests.
	 *
	 * Automatically sorts gigs in ascending order by the gig date, but limits to
	 * showing upcoming gigs unless a specific date range is requested (year,
	 * month, day).
	 *
	 * @since 1.0.0
	 *
	 * @param object $query The main WP_Query object. Passed by reference.
	 */
	public function gig_query( $query ) {
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
	 * Gig feeds and venue connections.
	 *
	 * Caches gig->venue connections and reroutes feed requests to
	 * the appropriate template for processing.
	 *
	 * @since 1.0.0
	 * @uses $wp_query
	 * @uses p2p_type()->each_connected()
	 */
	public function template_redirect() {
		global $wp_query;

		if ( is_post_type_archive( 'audiotheme_gig' ) ) {
			p2p_type( 'audiotheme_venue_to_gig' )->each_connected( $wp_query );
		}

		$type = $wp_query->get( 'feed' );
		if ( is_feed() && 'audiotheme_gig' == $wp_query->get( 'post_type' ) ) {
			p2p_type( 'audiotheme_venue_to_gig' )->each_connected( $wp_query );

			require_once( AUDIOTHEME_DIR . 'includes/feed.php' );

			switch( $type ) {
				case 'feed':
					load_template( AUDIOTHEME_DIR . 'includes/views/gigs-feed-rss2.php' );
					break;
				case 'ical':
					load_template( AUDIOTHEME_DIR . 'includes/views/gigs-feed-ical.php' );
					break;
				case 'json':
					load_template( AUDIOTHEME_DIR . 'includes/views/gigs-feed-json.php' );
					break;
				default:
					$message = sprintf( __( 'ERROR: %s is not a valid feed template.', 'audiotheme' ), esc_html( $type ) );
					wp_die( $message, '', array( 'response' => 404 ) );
			}
			exit;
		}
	}

	/**
	 * Load gig templates.
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
		$compat            = $this->theme_compat;

		if ( is_post_type_archive( 'audiotheme_gig' ) ) {
			$template = audiotheme_locate_template( 'archive-gig.php' );

			$compat->set_title( get_audiotheme_post_type_archive_title() );
			$compat->set_loop_template_part( 'parts/loop-archive', 'gig' );
		} elseif ( is_singular( 'audiotheme_gig' ) ) {
			$template = audiotheme_locate_template( 'single-gig.php' );

			$compat->set_title( '' );
			$compat->set_loop_template_part( 'parts/loop-single', 'gig' );
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
				$base = $this->get_rewrite_base();
				$slug = ( $leavename ) ? '%postname%' : $post->post_name;

				$post_link = home_url( sprintf( '/%s/%s/', $base, $slug ) );
			}
		}

		return $post_link;
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
			$base = $this->get_rewrite_base();
			$link = home_url( '/' . $base . '/' );
		} elseif ( 'audiotheme_gig' == $post_type ) {
			$link = add_query_arg( 'post_type', 'audiotheme_gig', home_url( '/' ) );
		}

		return $link;
	}

	/**
	 * Prevent conflicts in gig permalinks.
	 *
	 * Gigs without titles will fall back to using the ID for the slug, however,
	 * when the ID is a 4 digit number, it will conflict with date-based permalinks.
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
	public function get_unique_gig_slug( $slug, $post_id, $post_status, $post_type, $post_parent, $original_slug = null ) {
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
	 * If a slug is empty when a post is published, wp_insert_post() will base the
	 * slug off the title/ID without a way to filter it until after the post is
	 * saved.
	 *
	 * @since 1.6.1
	 *
	 * @param int $post_id Post ID.
	 * @param WP_Post $post Post object.
	 */
	public function update_bad_gig_slug( $post_id, $post ) {
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
	 * Determines if a venue's gig_count meta field needs to be updated
	 * when a gig is deleted.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id ID of the gig being deleted.
	 */
	public function on_before_delete_gig( $post_id ) {
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
	public function gig_post_class( $classes, $class, $post_id ) {
		if ( 'audiotheme_gig' == get_post_type( $post_id ) && audiotheme_gig_has_ticket_meta() ) {
			$classes[] = 'has-audiotheme-ticket-meta';
		}

		return $classes;
	}

	/**
	 * Add custom gig rewrite rules.
	 *
	 * /base/YYYY/MM/DD/(feed|ical|json)/
	 * /base/YYYY/MM/DD/
	 * /base/YYYY/MM/(feed|ical|json)/
	 * /base/YYYY/MM/
	 * /base/YYYY/(feed|ical|json)/
	 * /base/YYYY/
	 * /base/past/page/2/
	 * /base/past/
	 * /base/(feed|ical|json)/
	 * /base/%postname%/
	 * /base/
	 *
	 * @todo /base/tour/%tourname%/
	 *       /base/YYYY/page/2/
	 *       etc.
	 *
	 * @since 1.0.0
	 * @see audiotheme_gigs_rewrite_base()
	 *
	 * @param object $wp_rewrite The main rewrite object. Passed by reference.
	 */
	public function generate_rewrite_rules( $wp_rewrite ) {
		$base = $this->get_rewrite_base();

		$new_rules[ $base . '/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(feed|ical|json)/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]';
		$new_rules[ $base . '/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]';
		$new_rules[ $base . '/([0-9]{4})/([0-9]{1,2})/(feed|ical|json)/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]';
		$new_rules[ $base . '/([0-9]{4})/([0-9]{1,2})/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]&monthnum=$matches[2]';
		$new_rules[ $base . '/([0-9]{4})/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]';
		$new_rules[ $base . '/(feed|ical|json)/?$' ] = 'index.php?post_type=audiotheme_gig&feed=$matches[1]';
		$new_rules[ $base . '/past/page/([0-9]{1,})/?$' ] = 'index.php?post_type=audiotheme_gig&paged=$matches[1]&audiotheme_gig_range=past';
		$new_rules[ $base . '/past/?$' ] = 'index.php?post_type=audiotheme_gig&audiotheme_gig_range=past';
		$new_rules[ $base . '/([^/]+)/(ical|json)/?$' ] = 'index.php?audiotheme_gig=$matches[1]&feed=$matches[2]';
		$new_rules[ $base . '/([^/]+)/?$' ] = 'index.php?audiotheme_gig=$matches[1]';
		$new_rules[ $base . '/?$' ] = 'index.php?post_type=audiotheme_gig';

		$wp_rewrite->rules = array_merge( $new_rules, $wp_rewrite->rules );
	}
}
