<?php
/**
 * Themes list screen.
 *
 * @package AudioTheme\Core\Administration
 * @since 2.0.0
 */

namespace AudioTheme\Core\Admin\Screen\Dashboard;

use AudioTheme\Core\Admin\Screen\Dashboard;
use AudioTheme\Core\Util;

/**
 * Themes list screen class.
 *
 * @package AudioTheme
 * @since 2.0.0
 */
class Themes extends Dashboard {
	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
	}

	/**
	 * Add the menu item for viewing themes.
	 *
	 * @since 2.0.0
	 */
	public function add_menu_item() {
		$page_hook = add_submenu_page(
			'audiotheme',
			__( 'Themes', 'audiotheme' ),
			__( 'Themes', 'audiotheme' ),
			'edit_posts',
			'audiotheme-themes',
			array( $this, 'render_screen' )
		);

		add_action( 'load-' . $page_hook, array( $this, 'load_screen' ) );
	}

	/**
	 * Set up the Themes screen.
	 *
	 * @since 2.0.0
	 */
	public function load_screen() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue assets for the Themes screen.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_assets() {
		wp_enqueue_style( 'audiotheme-dashboard' );
	}

	/**
	 * Display the Themes screen.
	 *
	 * @since 2.0.0
	 */
	public function render_screen() {
		$this->render_screen_header();
		include( AUDIOTHEME_DIR . 'admin/views/screen-themes.php' );
		$this->render_screen_footer();
	}
}
