<?php
/**
 * Settings administration screen.
 *
 * @package AudioTheme\Core\Administration
 * @since 1.0.0
 */

namespace AudioTheme\Core\Provider\Screen;

use AudioTheme\Core\Plugin;

/**
 * Settings administration screen class.
 *
 * @package AudioTheme
 * @since 1.0.0
 */
class Settings extends AbstractScreen {
	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 *
	 * @param \AudioTheme\Core\Plugin Main plugin instance.
	 */
	public function register_hooks( Plugin $plugin ) {
		if ( is_multisite() && ! is_network_admin() ) {
			return;
		}

		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
		add_action( 'admin_init', array( $this, 'add_sections' ) );
	}

	/**
	 * Add the settings menu item.
	 *
	 * @since 1.0.0
	 */
	public function add_menu_item() {
		add_submenu_page(
			is_network_admin() ? 'settings.php' : 'audiotheme',
			__( 'Settings', 'audiotheme' ),
			is_network_admin() ? __( 'AudioTheme', 'audiotheme' ) : __( 'Settings', 'audiotheme' ),
			is_network_admin() ? 'manage_network_options' : 'manage_options',
			'audiotheme-settings',
			array( $this, 'render_screen' )
		);
	}

	/**
	 * Add settings sections.
	 *
	 * @since 1.0.0
	 */
	public function add_sections() {
		add_settings_section(
			'default',
			'',
			'__return_null',
			'audiotheme-settings'
		);
	}

	/**
	 * Display the screen.
	 *
	 * @since 1.0.0
	 */
	public function render_screen() {
		include( $this->plugin->get_path( 'admin/views/screen-settings.php' ) );
	}
}
