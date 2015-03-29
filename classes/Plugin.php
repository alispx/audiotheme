<?php
/**
 * AudioTheme
 *
 * @package AudioTheme
 * @since 2.0.0
 */

namespace AudioTheme\Core;

use AudioTheme\Core\Container;

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
		$this['archives']->load();
		$this->load_modules();
		scb_init( array( $this, 'load_p2p_core' ) );

		if ( is_admin() ) {
			$this->load_admin();
		}
	}

	/**
	 * Load administration functionality.
	 *
	 * @since 2.0.0
	 */
	protected function load_admin() {
		$this->load_admin_modules();
		$this->load_admin_screens();
	}

	/**
	 * Register a hook provider.
	 *
	 * @since 2.0.0
	 *
	 * @param object $provider Hook provider.
	 * @return $this
	 */
	public function register_hooks( $provider ) {
		$provider->register( $this );
		return $this;
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
	 * Load module admin classes.
	 *
	 * @since 2.0.0
	 */
	protected function load_admin_modules() {
		// Load all modules on the settings screen.
		if ( $this->is_dashboard_screen() ) {
			$modules = $this['admin.modules']->keys();
		} else {
			$modules = $this['admin.modules']->get_active_keys();
		}

		foreach ( $modules as $module_id ) {
			$this['admin.modules'][ $module_id ]->load();
		}
	}

	/**
	 * Load admin screens.
	 *
	 * @since 2.0.0
	 */
	protected function load_admin_screens() {
		foreach ( $this['admin.screens']->keys() as $screen_id ) {
			$this['admin.screens'][ $screen_id ]->load();
		}
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
