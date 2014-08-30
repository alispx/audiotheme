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
	 * Load the plugin.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin() {
		$this->load_textdomain();
		add_action( 'after_setup_theme', array( $this, 'load_modules' ), 5 );
		add_action( 'after_setup_theme', array( $this, 'load_admin' ), 5 );
		add_action( 'after_setup_theme', array( $this, 'attach_hooks' ), 5 );
	}

	/**
	 * Localize the plugin's strings.
	 *
	 * @since 1.0.0
	 */
	protected function load_textdomain() {
		load_plugin_textdomain( 'audiotheme', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages' );
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
		add_action( 'init', 'audiotheme_less_setup' );
		add_action( 'wp_loaded', 'audiotheme_loaded' );
		add_action( 'audiotheme_template_include', 'audiotheme_template_setup' );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ), 0 );

		add_filter( 'wp_nav_menu_objects', 'audiotheme_nav_menu_classes', 10, 3 );

		// Media hooks.
		add_action( 'init', 'audiotheme_add_default_oembed_providers' );
		add_filter( 'embed_oembed_html', 'audiotheme_oembed_html', 10, 4 );
		add_filter( 'embed_handler_html', 'audiotheme_oembed_html', 10, 4 );
		add_filter( 'video_embed_html', 'audiotheme_oembed_html', 10 ); // Jetpack compat.

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
		add_action( 'audiotheme_before_main_content', 'audiotheme_before_main_content' );
		add_action( 'audiotheme_after_main_content', 'audiotheme_after_main_content' );

		// Deprecated.
		add_filter( 'dynamic_sidebar_params', 'audiotheme_widget_count_class' );
		add_filter( 'get_pages', 'audiotheme_page_list' );
		add_filter( 'page_css_class', 'audiotheme_page_list_classes', 10, 2 );
		add_filter( 'nav_menu_css_class', 'audiotheme_nav_menu_name_class', 10, 2 );
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
	 * Additional setup during init.
	 *
	 * @since 1.2.0
	 */
	public function init() {
		if ( current_theme_supports( 'audiotheme-post-gallery' ) ) {
			// High priority so plugins filtering ouput don't get stomped. Jetpack, etc.
			add_filter( 'post_gallery', 'audiotheme_post_gallery', 5000, 2 );
		}
	}

	/**
	 * Register frontend scripts and styles for enqueuing on-demand.
	 *
	 * @since 1.0.0
	 * @link http://core.trac.wordpress.org/ticket/18909
	 */
	public function register_scripts() {
		$suffix   = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$base_url = set_url_scheme( AUDIOTHEME_URI );

		wp_register_script( 'jquery-timepicker', $base_url . 'includes/js/jquery.timepicker.min.js', array( 'jquery' ), '1.1', true );

		wp_register_style( 'audiotheme', $base_url . 'includes/css/audiotheme.min.css' );
	}
}
