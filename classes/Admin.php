<?php

namespace AudioTheme\Core;

/**
 * Administration class.
 *
 * @package AudioTheme\Core\Administration
 * @since 2.0.0
 */
class Admin {
	/**
	 * Admin modules container.
	 *
	 * @since 2.0.0
	 * @type AudioTheme_Container
	 */
	public $modules;

	/**
	 * Screen container.
	 *
	 * @since 2.0.0
	 * @type AudioTheme_Container
	 */
	public $screens;

	/**
	 * Load administration functionality.
	 *
	 * @since 2.0.0
	 */
	public function load() {
		$this->load_modules();
		$this->load_screens();
	}

	/**
	 * Load module admin classes.
	 *
	 * @since 2.0.0
	 */
	public function load_modules() {
		// Load all modules on the settings screen.
		if ( $this->is_dashboard_screen() ) {
			$modules = $this->modules->keys();
		} else {
			$modules = $this->modules->get_active_keys();
		}

		foreach ( $modules as $module_id ) {
			$this->modules[ $module_id ]->load();
		}
	}

	/**
	 * Load admin screens.
	 *
	 * @since 2.0.0
	 */
	public function load_screens() {
		foreach ( $this->screens->keys() as $screen_id ) {
			$this->screens[ $screen_id ]->load();
		}
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
