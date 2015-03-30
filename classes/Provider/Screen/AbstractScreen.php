<?php
/**
 * Base admin screen functionality.
 *
 * @package AudioTheme\Core\Administration
 * @since 2.0.0
 */

namespace AudioTheme\Core\Provider\Screen;

use AudioTheme\Core\HookProviderInterface;
use AudioTheme\Core\Plugin;

/**
 * Base screen class.
 *
 * @package AudioTheme
 * @since 2.0.0
 */
abstract class AbstractScreen implements HookProviderInterface {
	/**
	 * Plugin instance.
	 *
	 * @since 2.0.0
	 * @var \AudioTheme\Core\Plugin
	 */
	protected $plugin;

	/**
	 * Register the screen.
	 *
	 * @since 2.0.0
	 *
	 * @param \AudioTheme\Core\Plugin Main plugin instance.
	 */
	public function register_screen( Plugin $plugin ) {
		$this->set_plugin( $plugin );
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 *
	 * @param \AudioTheme\Core\Plugin Main plugin instance.
	 */
	abstract function register_hooks( Plugin $plugin );

	/**
	 * Set the main plugin instance.
	 *
	 * @since 2.0.0
	 *
	 * @param \AudioTheme\Core\Plugin Main plugin instance.
	 */
	protected function set_plugin( Plugin $plugin ) {
		$this->plugin = $plugin;
	}
}
