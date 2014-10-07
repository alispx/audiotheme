<?php
/**
 * Settings administration screen.
 *
 * @package AudioTheme\Administration
 * @since 1.0.0
 */

/**
 * Settings administration screen class.
 *
 * @package AudioTheme
 * @since 1.0.0
 */
class AudioTheme_Admin_Screen_Settings {
	/**
	 * Screen identifier.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	public $id = 'settings';

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
		$modules          = audiotheme()->modules->get_all();
		$inactive_modules = audiotheme()->modules->get_inactive();

		// Hide menu items for inactive modules on initial load.
		$styles = '';
		foreach ( $inactive_modules as $id => $module ) {
			$styles .= sprintf( '#%s { display: none;}', $module->admin_menu_id );
		}

		include( AUDIOTHEME_DIR . 'admin/views/screen-settings.php' );
	}
}