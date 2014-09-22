<?php
/**
 * AudioTheme
 *
 * @package AudioTheme
 * @since 2.0.0
 */

/**
 * Main plugin class.
 *
 * @package AudioTheme
 * @since 2.0.0
 */
class Audiotheme {
	/**
	 * Theme compatibility class.
	 *
	 * @since 2.0.0
	 * @type Audiotheme_Theme_Compat
	 */
	public $theme_compat;

	/**
	 * Path to the main plugin file.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	protected $plugin_file;

	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->plugin_file = dirname( dirname( __FILE__ ) ) . '/audiotheme.php';
		$this->theme_compat = new AudioTheme_Theme_Compat();
	}

	/**
	 * Load the plugin.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin() {
		$this->load_textdomain();

		add_action( 'after_setup_theme', array( $this, 'load_modules' ), 5 );
		add_action( 'after_setup_theme', array( $this, 'load_admin' ), 5 );
		add_action( 'after_setup_theme', array( $this, 'attach_hooks' ), 5 );

		register_activation_hook( $this->plugin_file, 'activate' );
		register_deactivation_hook( $this->plugin_file, 'deactivate' );
	}

	/**
	 * Localize the plugin's strings.
	 *
	 * @since 1.0.0
	 */
	protected function load_textdomain() {
		load_plugin_textdomain( 'audiotheme', false, dirname( plugin_basename( $this->plugin_file ) ) . '/languages' );
	}

	/**
	 * Load administration functions and libraries.
	 *
	 * Has to be loaded after the Theme Customizer in order to determine if the
	 * Settings API should be included while customizing a theme.
	 *
	 * @since 1.0.0
	 */
	public function load_admin() {
		if ( ! is_admin() ) {
			return;
		}

		$admin = new AudioTheme_Admin();
		$admin->load();
	}

	/**
	 * Attach hooks to interact with WordPress at various points.
	 *
	 * @since 1.0.0
	 */
	public function attach_hooks() {
		// Default hooks.
		add_action( 'widgets_init', 'audiotheme_widgets_init' );
		add_action( 'wp_loaded', array( $this, 'maybe_flush_rewrite_rules' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ), 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ), 1 );
		add_action( 'wp_enqueue_scripts', 'audiotheme_enqueue_assets' );

		add_filter( 'wp_nav_menu_objects', 'audiotheme_nav_menu_classes', 10, 3 );

		// Archive hooks.
		add_action( 'init', 'register_audiotheme_archives' );
		add_filter( 'post_type_link', 'audiotheme_archives_post_type_link', 10, 3 );
		add_filter( 'post_type_archive_link', 'audiotheme_archives_post_type_archive_link', 10, 2 );
		add_filter( 'post_type_archive_title', 'audiotheme_archives_post_type_archive_title' );

		add_action( 'admin_bar_menu', 'audiotheme_archives_admin_bar_edit_menu', 80 );
		add_action( 'post_updated', 'audiotheme_archives_post_updated', 10, 3 );
		add_action( 'delete_post', 'audiotheme_archives_deleted_post' );

		// Prevent the audiotheme_archive post type rules from being registered.
		add_filter( 'audiotheme_archive_rewrite_rules', '__return_empty_array' );

		// Template hooks.
		add_action( 'audiotheme_before_main_content', 'audiotheme_before_main_content', 15 );
		add_action( 'audiotheme_after_main_content', 'audiotheme_after_main_content', 5 );
	}

	/**
	 * Load the active modules.
	 *
	 * Modules are always loaded when viewing the AudioTheme Settings screen so they can be toggled with instant feedback.
	 *
	 * @since 2.0.0
	 */
	public function load_modules() {
		$modules = array(
			'discography' => array(
				'audiotheme_discography_init',
				'audiotheme_load_discography_admin',
			),
			'gigs' => array(
				'audiotheme_gigs_init',
				'audiotheme_gigs_admin_setup',
			),
			'videos' => array(
				'audiotheme_videos_init',
				'audiotheme_load_videos_admin',
			),
		);

		$is_settings_screen = is_admin() && isset( $_GET['page'] ) && 'audiotheme-settings' == $_GET['page'];

		foreach ( $modules as $module_id => $hooks ) {
			if ( audiotheme_is_module_active( $module_id ) || $is_settings_screen ) {
				add_action( 'init', $hooks[0] );

				if ( is_admin() ) {
					add_action( 'init', $hooks[1] );
				}
			}
		}
	}

	/**
	 * Register frontend scripts and styles for enqueuing on-demand.
	 *
	 * @since 1.0.0
	 * @link http://core.trac.wordpress.org/ticket/18909
	 */
	public function register_assets() {
		$suffix   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$base_url = set_url_scheme( AUDIOTHEME_URI );

		wp_register_script( 'audiotheme', $base_url . 'includes/js/audiotheme.js', array( 'jquery', 'audiotheme-media-classes' ), AUDIOTHEME_VERSION, true );
		wp_register_script( 'audiotheme-media-classes', $base_url . 'includes/js/audiotheme-media-classes.js', array( 'jquery' ), AUDIOTHEME_VERSION, true );
		wp_register_script( 'jquery-timepicker', $base_url . 'includes/js/jquery.timepicker.min.js', array( 'jquery' ), '1.1', true );

		wp_register_style( 'audiotheme', $base_url . 'includes/css/audiotheme.min.css' );
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
}
