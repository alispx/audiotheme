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
	 * Modules API.
	 *
	 * @since 2.0.0
	 * @type Audiotheme_Modules
	 */
	protected $modules;

	/**
	 * Path to the main plugin file.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	protected $plugin_file;

	/**
	 * Theme compatibility class.
	 *
	 * @since 2.0.0
	 * @type Audiotheme_Theme_Compat
	 */
	protected $theme_compat;

	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 */
	public function __construct( $args = array() ) {
		$keys = array_keys( get_object_vars( $this ) );
		foreach ( $keys as $key ) {
			if ( isset( $args[ $key ] ) ) {
				$this->$key = $args[ $key ];
			}
		}
	}

	/**
	 * Magic getter.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name Property name.
	 * @return mixed Property value.
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'modules' :
			case 'plugin_file' :
			case 'theme_compat' :
				return $this->{$name};
		}
	}

	/**
	 * Magic setter.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name Property name.
	 * @param mixed $value Property value.
	 */
	public function __set( $name, $value ) {
		switch ( $name ) {
			default :
				$this->{$name} = $value;
		}
	}

	/**
	 * Load the plugin.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin() {
		$this->load_textdomain();
		$this->load_active_modules();
		$this->register_hooks();

		register_activation_hook( $this->plugin_file, 'activate' );
		register_deactivation_hook( $this->plugin_file, 'deactivate' );
	}

	/**
	 * Localize the plugin's strings.
	 *
	 * @since 1.0.0
	 */
	protected function load_textdomain() {
		$plugin_rel_path = dirname( plugin_basename( $this->plugin_file ) ) . '/languages';
		load_plugin_textdomain( 'audiotheme', false, $plugin_rel_path );
	}

	/**
	 * Load the active modules.
	 *
	 * Modules are always loaded when viewing the AudioTheme Settings screen so
	 * they can be toggled with instant feedback.
	 *
	 * @since 2.0.0
	 */
	protected function load_active_modules() {
		$is_settings_screen = is_admin() && isset( $_GET['page'] ) && 'audiotheme-settings' == $_GET['page'];

		foreach ( $this->modules->get_all() as $module_id => $module ) {
			if ( ! $module->is_active() && ! $is_settings_screen ) {
				continue;
			}

			$module->load();
		}
	}

	/**
	 * Attach hooks to interact with WordPress at various points.
	 *
	 * @since 1.0.0
	 */
	protected function register_hooks() {
		// Default hooks.
		add_action( 'widgets_init',                 'audiotheme_widgets_init' );
		add_action( 'wp_loaded',                    array( $this, 'maybe_flush_rewrite_rules' ) );
		add_action( 'wp_enqueue_scripts',           array( $this, 'register_assets' ), 1 );
		add_action( 'admin_enqueue_scripts',        array( $this, 'register_assets' ), 1 );
		add_action( 'wp_enqueue_scripts',           'audiotheme_enqueue_assets' );
		add_filter( 'wp_nav_menu_objects',          'audiotheme_nav_menu_classes', 10, 3 );
		add_filter( 'wp_prepare_attachment_for_js', 'audiotheme_wp_prepare_audio_attachment_for_js', 10, 3 );

		// Prevent the audiotheme_archive post type rules from being registered.
		add_filter( 'audiotheme_archive_rewrite_rules', '__return_empty_array' );

		// Template hooks.
		add_action( 'audiotheme_before_main_content',   'audiotheme_before_main_content', 15 );
		add_action( 'audiotheme_after_main_content',    'audiotheme_after_main_content', 5 );
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

		wp_register_script( 'audiotheme',               $base_url . 'includes/js/audiotheme.js',               array( 'jquery', 'audiotheme-media-classes' ), AUDIOTHEME_VERSION, true );
		wp_register_script( 'audiotheme-media-classes', $base_url . 'includes/js/audiotheme-media-classes.js', array( 'jquery' ), AUDIOTHEME_VERSION, true );
		wp_register_script( 'jquery-timepicker',        $base_url . 'includes/js/jquery.timepicker.min.js',    array( 'jquery' ), '1.1', true );

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
