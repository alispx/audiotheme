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
	 * Admininistration class.
	 *
	 * @since 2.0.0
	 * @type object
	 */
	protected $admin;

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
	 * Archives class.
	 *
	 * @since 2.0.0
	 * @type object
	 */
	protected $archives;

	/**
	 * Module identifier.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	protected $id;

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
	 * Theme compatability class.
	 *
	 * @since 2.0.0
	 * @type AudioTheme_Theme_Compat
	 */
	protected $theme_compat;

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
			case 'admin' :
			case 'admin_menu_id' :
			case 'archives' :
			case 'description' :
			case 'id' :
			case 'name' :
			case 'theme_compat' :
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
			case 'admin' :
			case 'archives' :
			case 'theme_compat' :
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
		return ! in_array( $this->id, $active_modules );
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
}
