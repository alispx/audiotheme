<?php
/**
 * Module defaults.
 *
 * @package AudioTheme\Modules
 * @since 2.0.0
 */

/**
 * Abstract class for a new module.
 *
 * @package AudioTheme\Modules
 * @since 2.0.0
 */
abstract class AudioTheme_Module {
	/**
	 * Module identifier.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	protected $module_id;

	/**
	 * Module name.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	protected $module_name;

	/**
	 * Module description.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	protected $module_description;

	/**
	 * Whether the module is a core module.
	 *
	 * @since 2.0.0
	 * @type bool
	 */
	protected $is_core_module = false;

	/**
	 * Whether the module's status can be toggled.
	 *
	 * @since 2.0.0
	 * @type bool
	 */
	protected $is_togglable = false;

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
			case 'module_id' :
			case 'module_name' :
			case 'module_description' :
			case 'admin_menu_id' :
				return $this->{$name};
		}
	}

	/**
	 * Method for loading the module.
	 *
	 * Typically occurs at plugins_loaded:10 after the text domain has been loaded.
	 *
	 * @since 2.0.0
	 */
	abstract function load();

	/**
	 * Whether the module is active.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_active() {
		$active_modules = get_option( 'audiotheme_inactive_modules', array() );
		return ! in_array( $this->module_id, $active_modules );
	}

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
	 * Whether the module's status can be toggled.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_togglable() {
		return (bool) $this->is_togglable;
	}
}
