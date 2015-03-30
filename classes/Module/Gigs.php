<?php
/**
 * Gigs module.
 *
 * @package AudioTheme\Core\Gigs
 * @since 2.0.0
 */

namespace AudioTheme\Core\Module;

/**
 * Gigs module class.
 *
 * @package AudioTheme\Core\Gigs
 * @since 2.0.0
 */
class Gigs extends AbstractModule {
	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args( array(
			'id'             => 'gigs',
			'name'           => __( 'Gigs & Venues', 'audiotheme' ),
			'description'    => __( 'Share gig details with your fans, include: location, venue, date, time, and ticket prices.', 'audiothmee' ),
			'is_core_module' => true,
			'admin_menu_id'  => 'toplevel_page_audiotheme-gigs',
		), $args );

		parent::__construct( $args );
	}

	/**
	 * Register module hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'wp_loaded',              array( $this, 'register_post_connections' ) );
		add_filter( 'generate_rewrite_rules', array( $this, 'generate_rewrite_rules' ) );
		add_action( 'template_redirect',      array( $this, 'template_redirect' ) );
		add_action( 'template_include',       array( $this, 'template_include' ) );

		// Admin hooks.
		add_action( 'parent_file',            array( $this, 'admin_menu_highlight' ) );
		add_action( 'admin_enqueue_scripts',  array( $this, 'register_admin_assets' ), 1 );
		add_filter( 'set-screen-option',      array( $this, 'screen_options' ), 999, 3 );
	}

	/**
	 * Register post connections.
	 *
	 * @since 2.0.0
	 */
	public function register_post_connections() {
		// Register the relationship between gigs and venues.
		p2p_register_connection_type( array(
			'name'        => 'audiotheme_venue_to_gig',
			'from'        => 'audiotheme_venue',
			'to'          => 'audiotheme_gig',
			'cardinality' => 'one-to-many',
			'admin_box'   => false,
		) );
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

			switch( $type ) {
				case 'feed':
					load_template( $this->plugin->get_path( 'includes/views/gigs-feed-rss2.php' ) );
					break;
				case 'ical':
					load_template( $this->plugin->get_path( 'includes/views/gigs-feed-ical.php' ) );
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
		$template_loader   = $this->template_loader;
		$compat            = $this->theme_compatibility;

		if ( is_post_type_archive( 'audiotheme_gig' ) ) {
			$template = $template_loader->locate_template( 'archive-gig.php' );
			$compat->set_title( get_audiotheme_post_type_archive_title() );
			$compat->set_loop_template_part( 'gig/loop', 'archive' );
		} elseif ( is_singular( 'audiotheme_gig' ) ) {
			$template = $template_loader->locate_template( 'single-gig.php' );
			$compat->set_title( get_queried_object()->post_title );
			$compat->set_loop_template_part( 'gig/loop', 'single' );
		}

		if ( $template !== $original_template ) {
			$template = $this->get_compatible_template( $template );
			do_action( 'audiotheme_template_include', $template );
		}

		return $template;
	}

	/**
	 * Add custom gig rewrite rules.
	 *
	 * /base/YYYY/MM/DD/(feed|ical)/
	 * /base/YYYY/MM/DD/
	 * /base/YYYY/MM/(feed|ical)/
	 * /base/YYYY/MM/
	 * /base/YYYY/(feed|ical)/
	 * /base/YYYY/
	 * /base/past/page/2/
	 * /base/past/
	 * /base/(feed|ical)/
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
		$past = preg_replace( '/[^a-z0-9-_]/', '', apply_filters( 'audiotheme_past_gigs_rewrite_slug', 'past' ) );

		$new_rules[ $base . '/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(feed|ical)/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]';
		$new_rules[ $base . '/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]';
		$new_rules[ $base . '/([0-9]{4})/([0-9]{1,2})/(feed|ical)/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]';
		$new_rules[ $base . '/([0-9]{4})/([0-9]{1,2})/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]&monthnum=$matches[2]';
		$new_rules[ $base . '/([0-9]{4})/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]';
		$new_rules[ $base . '/(feed|ical)/?$' ] = 'index.php?post_type=audiotheme_gig&feed=$matches[1]';
		$new_rules[ $base . '/' . $past . '/page/([0-9]{1,})/?$' ] = 'index.php?post_type=audiotheme_gig&paged=$matches[1]&audiotheme_gig_range=past';
		$new_rules[ $base . '/' . $past . '/?$' ] = 'index.php?post_type=audiotheme_gig&audiotheme_gig_range=past';
		$new_rules[ $base . '/([^/]+)/(ical)/?$' ] = 'index.php?audiotheme_gig=$matches[1]&feed=$matches[2]';
		$new_rules[ $base . '/([^/]+)/?$' ] = 'index.php?audiotheme_gig=$matches[1]';
		$new_rules[ $base . '/?$' ] = 'index.php?post_type=audiotheme_gig';

		$wp_rewrite->rules = array_merge( $new_rules, $wp_rewrite->rules );
	}

	/**
	 * Register scripts and styles.
	 *
	 * @since 2.0.0
	 */
	public function register_admin_assets() {
		$base_url = set_url_scheme( $this->plugin->get_url( 'admin/js/' ) );

		wp_register_script( 'audiotheme-gig-edit',      $base_url . 'gig-edit.js',      array( 'audiotheme-admin', 'audiotheme-venue-manager', 'jquery-timepicker', 'jquery-ui-autocomplete', 'pikaday', 'underscore', 'wp-backbone', 'wp-util' ), AUDIOTHEME_VERSION, true );
		wp_register_script( 'audiotheme-venue-edit',    $base_url . 'venue-edit.js',    array( 'audiotheme-admin', 'jquery-ui-autocomplete', 'post', 'underscore' ), AUDIOTHEME_VERSION, true );
		wp_register_script( 'audiotheme-venue-manager', $base_url . 'venue-manager.js', array( 'audiotheme-admin', 'jquery', 'media-models', 'media-views', 'underscore', 'wp-backbone', 'wp-util' ), AUDIOTHEME_VERSION, true );

		$post_type_object = get_post_type_object( 'audiotheme_venue' );

		$settings = array(
			'canPublishVenues' => false,
			'canEditVenues'    => current_user_can( $post_type_object->cap->edit_posts ),
			'insertVenueNonce' => false,
			'l10n'             => array(

			),
		);

		if ( current_user_can( $post_type_object->cap->publish_posts ) ) {
			$settings['canPublishVenues'] = true;
			$settings['insertVenueNonce'] = wp_create_nonce( 'insert-venue' );
		}

		wp_localize_script( 'audiotheme-venue-manager', '_audiothemeVenueManagerSettings', $settings );
	}

	/**
	 * Sanitize the 'per_page' screen option on the Manage Gigs and Manage Venues
	 * screens.
	 *
	 * Apparently any other hook attached to the same filter that runs after
	 * this will stomp all over it. To prevent this filter from doing the same,
	 * it's only attached on the screens that require it. The priority should be
	 * set extremely low to help ensure the correct value gets returned.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $return Default is 'false'.
	 * @param string $option The option name.
	 * @param mixed $value The value to sanitize.
	 * @return mixed The sanitized value.
	 */
	public function screen_options( $return, $option, $value ) {
		global $pagenow;

		// Only run on the gig and venue Manage Screens.
		$is_manage_screen = 'admin.php' == $pagenow && isset( $_GET['page'] ) && ( 'audiotheme-gigs' == $_GET['page'] || 'audiotheme-venues' == $_GET['page'] );
		$is_valid_option  = ( 'toplevel_page_audiotheme_gigs_per_page' == $option || 'gigs_page_audiotheme_venues_per_page' == $option );

		if ( $is_manage_screen && $is_valid_option ) {
			$return = absint( $value );
		}

		return $return;
	}

	/**
	 * Highlight the correct top level and sub menu items for the current screen.
	 *
	 * @since 1.0.0
	 *
	 * @param string $parent_file The screen being displayed.
	 * @return string The menu item to highlight.
	 */
	public function admin_menu_highlight( $parent_file ) {
		global $pagenow, $post_type, $submenu, $submenu_file;

		if ( 'audiotheme_gig' == $post_type ) {
			$parent_file  = 'audiotheme-gigs';
			$submenu_file = ( 'post.php' == $pagenow ) ? 'audiotheme-gigs' : $submenu_file;
		}

		if ( 'audiotheme-gigs' == $parent_file && isset( $_GET['page'] ) && 'audiotheme-venue' == $_GET['page'] ) {
			$submenu_file = 'audiotheme-venues';
		}

		// Remove the Add New Venue submenu item.
		if ( isset( $submenu['audiotheme-gigs'] ) ) {
			foreach ( $submenu['audiotheme-gigs'] as $key => $sm ) {
				if ( isset( $sm[0] ) && 'audiotheme-venue' == $sm[2] ) {
					unset( $submenu['audiotheme-gigs'][ $key ] );
				}
			}
		}

		return $parent_file;
	}
}
