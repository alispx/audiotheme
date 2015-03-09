<?php
/**
 * Common dashboard functionality.
 *
 * @package AudioTheme\Core\Administration
 * @since 2.0.0
 */

namespace AudioTheme\Core\Admin\Screen;

use AudioTheme\Core\Util;

/**
 * Class to extend for common functionality on dashboard screens.
 *
 * @package AudioTheme
 * @since 2.0.0
 */
class Dashboard {
	/**
	 * Load the screen.
	 *
	 * @since 2.0.0
	 */
	public function load() {
		$this->register_hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {}

	/**
	 * Display the screen header.
	 *
	 * @since 2.0.0
	 */
	public function render_screen_header() {
		include( AUDIOTHEME_DIR . 'admin/views/screen-dashboard-header.php' );
	}

	/**
	 * Display the screen footer.
	 *
	 * @since 2.0.0
	 */
	public function render_screen_footer() {
		include( AUDIOTHEME_DIR . 'admin/views/screen-dashboard-footer.php' );
	}

	/**
	 * Retrieve dashboard tabs.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public static function get_tabs() {
		return array(
			array(
				'label'     => __( 'Getting Started', 'audiotheme' ),
				'url'       => admin_url( 'admin.php?page=audiotheme' ),
				'is_active' => isset( $_GET['page'] ) && 'audiotheme' == $_GET['page'],
			),
			array(
				'label'     => __( 'Themes', 'audiotheme' ),
				'url'       => admin_url( 'admin.php?page=audiotheme-themes' ),
				'is_active' => isset( $_GET['page'] ) && 'audiotheme-themes' == $_GET['page'],
			),
			array(
				'label'     => __( 'Credits', 'audiotheme' ),
				'url'       => admin_url( 'admin.php?page=audiotheme' ),
				'is_active' => false,
			)
		);
	}
}
