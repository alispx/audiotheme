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
	}

	/**
	 * Register a hook provider.
	 *
	 * @since 2.0.0
	 *
	 * @param \AudioTheme\Core\HookProviderInterface $provider Hook provider.
	 * @return $this
	 */
	public function register_hooks( $provider ) {
		$provider->register_hooks( $this );
		return $this;
	}

	/**
	 * Register an admin screen.
	 *
	 * @since 2.0.0
	 *
	 * @param object $provider Screen
	 * @return $this
	 */
	public function register_screen( $screen ) {
		$screen->register_screen( $this );
		$this->register_hooks( $screen );
		return $this;
	}

	/**
	 * Retrieve the path to a file in the plugin.
	 *
	 * @since 2.0.0
	 *
	 * @param string $path Optional. Path relative to the plugin root.
	 * @return string
	 */
	public function get_path( $path = '' ) {
		return AUDIOTHEME_DIR . ltrim( $path, '/' );
	}

	/**
	 * Retrieve the URL for a file in the plugin.
	 *
	 * @since 2.0.0
	 *
	 * @param string $path Optional. Path relative to the plugin root.
	 * @return string
	 */
	public function get_url( $path = '' ) {
		return AUDIOTHEME_URI . ltrim( $path, '/' );
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
