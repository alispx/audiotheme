<?php
/**
 * Settings administration screen.
 *
 * @package AudioTheme\Core\Administration
 * @since 1.0.0
 */

namespace AudioTheme\Core\Admin\Screen;

/**
 * Settings administration screen class.
 *
 * @package AudioTheme
 * @since 1.0.0
 */
class Settings {
	/**
	 * Load the screen.
	 *
	 * @since 1.0.0
	 */
	public function load() {
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
		$modules = $this->modules;

		// Hide menu items for inactive modules on initial load.
		$styles = '';
		foreach ( $modules->get_inactive_keys() as $module_id ) {
			$styles .= sprintf( '#%s { display: none;}', $modules[ $module_id ]->admin_menu_id );
		}

		include( AUDIOTHEME_DIR . 'admin/views/screen-settings.php' );
	}
}
