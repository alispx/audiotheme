<?php
/**
 * AudioTheme
 *
 * @package AudioTheme
 * @since 2.0.0
 */

namespace AudioTheme\Core;

use AudioTheme\Core\Container;
use AudioTheme\Core\DefaultHooks;

/**
 * Main plugin class.
 *
 * @package AudioTheme
 * @since 2.0.0
 */
class Plugin extends Container {
	/**
	 * Load the plugin.
	 *
	 * @since 1.0.0
	 */
	public function load() {
		$this->load_textdomain();
		$this['archives']->load();
		$this->register_hooks();
		$this->load_modules();
		scb_init( array( $this, 'load_p2p_core' ) );

		if ( is_admin() ) {
			$this['admin']->load();
		}

		register_activation_hook( $this['plugin_file'], 'activate' );
		register_deactivation_hook( $this['plugin_file'], 'deactivate' );
	}

	/**
	 * Localize the plugin's strings.
	 *
	 * @since 1.0.0
	 */
	protected function load_textdomain() {
		$plugin_rel_path = dirname( plugin_basename( $this['plugin_file'] ) ) . '/languages';
		load_plugin_textdomain( 'audiotheme', false, $plugin_rel_path );
	}

	/**
	 * Load the active modules.
	 *
	 * Modules are always loaded when viewing the AudioTheme Settings screen so
	 * they can be toggled with instant access.
	 *
	 * @since 2.0.0
	 */
	protected function load_modules() {
		// Load all modules on the settings screen.
		if ( $this->is_dashboard_screen() ) {
			$modules = $this['modules']->keys();
		} else {
			$modules = $this['modules']->get_active_keys();
		}

		foreach ( $modules as $module_id ) {
			$this['modules'][ $module_id ]->load();
		}
	}

	/**
	 * Attach hooks to interact with WordPress at various points.
	 *
	 * @since 1.0.0
	 */
	protected function register_hooks() {
		add_action( 'widgets_init',          array( $this, 'register_widgets' ) );
		add_action( 'wp_loaded',             array( $this, 'maybe_flush_rewrite_rules' ) );
		add_action( 'wp_enqueue_scripts',    array( $this, 'register_assets' ), 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ), 1 );
		add_action( 'wp_enqueue_scripts',    array( $this, 'enqueue_assets' ), 11 ); // Enqueue after theme styles.

		// Default hooks.
		add_action( 'wp_head',                      array( $this['default_hooks_class'], 'document_javascript_support' ) );
		add_filter( 'body_class',                   array( $this['default_hooks_class'], 'body_classes' ) );
		add_filter( 'wp_nav_menu_objects',          array( $this['default_hooks_class'], 'nav_menu_classes' ), 10, 3 );
		add_filter( 'wp_prepare_attachment_for_js', array( $this['default_hooks_class'], 'prepare_audio_attachment_for_js' ), 10, 3 );

		// Prevent the audiotheme_archive rewrite rules from being registered.
		add_filter( 'audiotheme_archive_rewrite_rules', '__return_empty_array' );
		add_filter( 'audiotheme_archive_settings_fields', array( $this['default_hooks_class'], 'default_archive_settings_fields' ), 9, 2 );

		// Deprecated.
		add_action( 'init', 'audiotheme_less_setup' );
	}

	/**
	 * Load Posts 2 Posts core.
	 *
	 * Posts 2 Posts requires two custom database tables to store post
	 * relationships and relationship metadata. If an alternative version of the
	 * library doesn't exist, the tables are created on admin_init.
	 *
	 * @since 2.0.0
	 */
	public function load_p2p_core() {
		if ( function_exists( 'p2p_register_connection_type' ) ) {
			return;
		}

		if ( ! defined( 'P2P_TEXTDOMAIN' ) ) {
			define( 'P2P_TEXTDOMAIN', 'audiotheme' );
		}

		require( AUDIOTHEME_DIR . '/vendor/scribu/lib-posts-to-posts/autoload.php' );

		\P2P_Storage::init();
		\P2P_Query_Post::init();
		\P2P_Query_User::init();
		\P2P_URL_Query::init();
		\P2P_Widget::init();
		\P2P_Shortcodes::init();

		add_action( 'admin_init', array( '\P2P_Storage', 'install' ) );
	}

	/**
	 * Register supported widgets.
	 *
	 * Themes can load all widgets by calling
	 * add_theme_support( 'audiotheme-widgets' ).
	 *
	 * If support for all widgets isn't desired, a second parameter consisting
	 * of an array of widget keys can be passed to load the specified widgets:
	 * add_theme_support( 'audiotheme-widgets', array( 'upcoming-gigs' ) )
	 *
	 * @since 2.0.0
	 */
	public function register_widgets() {
		$widgets = array();
		$widgets['recent-posts'] = '\AudioTheme\Core\Widget\RecentPosts';

		if ( $this['modules']->is_active( 'discography' ) ) {
			$widgets['record'] = '\AudioTheme\Core\Widget\Record';
			$widgets['track']  = '\AudioTheme\Core\Widget\Track';
		}

		if ( $this['modules']->is_active( 'gigs' ) ) {
			$widgets['upcoming-gigs'] = '\AudioTheme\Core\Widget\UpcomingGigs';
		}

		if ( $this['modules']->is_active( 'videos' ) ) {
			$widgets['video']  = '\AudioTheme\Core\Widget\Video';
		}

		if ( $support = get_theme_support( 'audiotheme-widgets' ) ) {
			if ( is_array( $support ) ) {
				$widgets = array_intersect_key( $widgets, array_flip( $support[0] ) );
			}

			if ( ! empty( $widgets ) ) {
				foreach ( $widgets as $widget_id => $widget_class ) {
					register_widget( $widget_class );
				}
			}
		}
	}

	/**
	 * Register frontend scripts and styles for enqueuing on-demand.
	 *
	 * @since 1.0.0
	 *
	 * @link http://core.trac.wordpress.org/ticket/18909
	 */
	public function register_assets() {
		$base_url = set_url_scheme( AUDIOTHEME_URI );
		$suffix   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'audiotheme',               $base_url . 'includes/js/audiotheme.js',               array( 'jquery', 'jquery-cue', 'audiotheme-media-classes' ), AUDIOTHEME_VERSION, true );
		wp_register_script( 'audiotheme-media-classes', $base_url . 'includes/js/audiotheme-media-classes.js', array( 'jquery' ), AUDIOTHEME_VERSION, true );
		wp_register_script( 'jquery-cue',               $base_url . 'includes/js/vendor/jquery.cue.min.js',    array( 'jquery', 'mediaelement' ), '1.1.3', true );
		wp_register_script( 'jquery-timepicker',        $base_url . 'includes/js/vendor/jquery.timepicker.min.js',    array( 'jquery' ), '1.1', true );

		wp_register_style( 'audiotheme', $base_url . 'includes/css/audiotheme.min.css' );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_assets() {
		if ( ! apply_filters( 'audiotheme_enqueue_theme_assets', true ) ) {
			return;
		}

		wp_enqueue_script( 'audiotheme' );
		wp_enqueue_style( 'audiotheme' );
	}

	/**
	 * Flush the rewrite rules if needed.
	 *
	 * @since 1.0.0
	 */
	public function maybe_flush_rewrite_rules() {
		if ( ! is_network_admin() && 'no' != get_option( 'audiotheme_flush_rewrite_rules' ) ) {
			update_option( 'audiotheme_flush_rewrite_rules', 'no' );
			flush_rewrite_rules();
		}
	}

	/**
	 * Activation routine.
	 *
	 * Occurs too late to flush rewrite rules, so set an option to flush the
	 * rewrite rules on the next request.
	 *
	 * @since 1.0.0
	 */
	public function activate() {
		update_option( 'audiotheme_flush_rewrite_rules', 'yes' );
	}

	/**
	 * Deactivation routine.
	 *
	 * Deleting the rewrite rules option should force them to be regenerated the
	 * next time they're needed.
	 *
	 * @since 1.0.0
	 */
	public function deactivate() {
		delete_option( 'rewrite_rules' );
	}

	/**
	 * Whether the current request is the dashboard screen.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	protected function is_dashboard_screen() {
		return is_admin() && isset( $_GET['page'] ) && 'audiotheme' == $_GET['page'];
	}
}
