<?php
/**
 * Module base.
 *
 * @package AudioTheme\Core\Modules
 * @since 2.0.0
 */

namespace AudioTheme\Core\Module;

use AudioTheme\Core\Plugin;

/**
 * Base module class.
 *
 * @package AudioTheme\Core\Modules
 * @since 2.0.0
 */
abstract class AbstractModule {
	/**
	 * Archives class.
	 *
	 * @since 2.0.0
	 * @type object
	 */
	protected $archives;

	/**
	 * Admin menu item HTML id.
	 *
	 * Used for hiding menu items when toggling modules.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	protected $admin_menu_id;

	/**
	 * Module name.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	protected $name;

	/**
	 * Module description.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	protected $description;

	/**
	 * Whether the module is a core module.
	 *
	 * @since 2.0.0
	 * @type bool
	 */
	protected $is_core_module = false;

	/**
	 * Plugin instance.
	 *
	 * @since 2.0.0
	 * @var \AudioTheme\Core\Plugin
	 */
	protected $plugin;

	/**
	 * Template loader.
	 *
	 * @since 2.0.0
	 * @type AudioTheme\Core\Template\Loader
	 */
	protected $template_loader;

	/**
	 * Theme compatability class.
	 *
	 * @since 2.0.0
	 * @type AudioTheme\Core\Theme\Compatibility
	 */
	protected $theme_compatibility;

	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Array of properties to set on initialization.
	 */
	public function __construct( $args = array() ) {
		$keys = array_keys( get_object_vars( $this ) );
		foreach ( $keys as $key ) {
			if ( isset( $args[ $key ] ) ) {
				$this->$key = $args[ $key ];
			}
		}
	}

	/**
	 * Magic getter.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name Property name.
	 * @return mixed Property value.
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'admin_menu_id' :
			case 'description' :
			case 'name' :
				return $this->{$name};
		}
	}

	/**
	 * Magic setter.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name Property name.
	 * @param mixed $value Property value.
	 */
	public function __set( $name, $value ) {
		switch ( $name ) {
			case 'plugin' :
				$this->set_plugin( $value );
				break;
			case 'archives' :
			case 'template_loader' :
			case 'theme_compatibility' :
				$this->{$name} = $value;
		}
	}

	/**
	 * Method for loading the module.
	 *
	 * Typically occurs at plugins_loaded:10 after the text domain has been loaded.
	 *
	 * @since 2.0.0
	 */
	public function load() {
		$this->register_hooks();
	}

	/**
	 * Register module hooks.
	 *
	 * @since 2.0.0
	 */
	abstract function register_hooks();

	/**
	 * Whether the module is a core module.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_core() {
		return (bool) $this->is_core_module;
	}

	/**
	 * Retrieve a template that's compatible with the theme.
	 *
	 * Ensures the given template is compatible with theme, otherwise theme
	 * compatibility mode is enabled and a generic template is located from the
	 * theme to use in instead.
	 *
	 * @since 2.0.0
	 *
	 * @param string $template
	 * @return string
	 */
	public function get_compatible_template( $template ) {
		$compat = $this->theme_compatibility;

		// Enable theme compatibility.
		if ( ! $compat->is_template_compatible( $template ) ) {
			$compat->enable();
			$template = $compat->get_theme_template();
		}

		return $template;
	}

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
