<?php
/**
 * Modules API.
 *
 * @package AudioTheme\Modules
 * @since 2.0.0
 */

/**
 * Modules API class.
 *
 * @package AudioTheme\Modules
 * @since 2.0.0
 */
class Audiotheme_Modules {
	/**
	 * List of instantiated modules.
	 *
	 * @since 2.0.0
	 * @type array
	 */
	protected $modules = array();

	/**
	 * Magic getter.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name Property name.
	 * @return mixed Property value.
	 */
	public function __get( $name ) {
		if ( isset( $this->modules[ $name ] ) ) {
			return $this->modules[ $name ];
		}
	}

	/**
	 * Register a module.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id Module identifier.
	 * @param array $data Module data.
	 */
	public function register( $module ) {
		$this->modules[ $module->module_id ] = $module;
	}

	/**
	 * Retrieve all modules.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_all() {
		return $this->modules;
	}

	/**
	 * Retrieve data for a single module.
	 *
	 * @since 2.0.0
	 *
	 * @param string $module A module identifier.
	 * @return array
	 */
	public function get( $module_id ) {
		$modules = $this->get_all();
		return $modules[ $module_id ];
	}

	/**
	 * Whether a module is active.
	 *
	 * @since 2.0.0
	 *
	 * @param string $module A module identifier.
	 * @return bool
	 */
	public function is_active( $module_id ) {
		return $this->modules[ $module_id ]->is_active();
	}

	/**
	 * Retrieve a list of active modules.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_active() {
		$active = array();
		foreach ( $this->modules as $module_id => $module ) {
			if ( ! $module->is_active() ) {
				continue;
			}

			$active[ $module_id ] = $module;
		}
		return $active;
	}

	/**
	 * Retrieve a list of inactive modules.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_inactive() {
		$inactive = array();
		foreach ( $this->modules as $module_id => $module ) {
			if ( $module->is_active() ) {
				continue;
			}

			$inactive[ $module_id ] = $module;
		}
		return $inactive;
	}

	/**
	 * Activate a module.
	 *
	 * @since 2.0.0
	 *
	 * @param string $module A module identifier.
	 */
	public function activate( $module ) {
		$modules = array_keys( $this->get_inactive() );
		unset( $modules[ array_search( $module, $modules ) ] );
		update_option( 'audiotheme_inactive_modules', array_values( $modules ) );
	}

	/**
	 * Deactivate a module.
	 *
	 * @since 2.0.0
	 *
	 * @param string $module A module identifier.
	 */
	public function deactivate( $module ) {
		$modules = array_keys( $this->get_inactive() );
		$modules = array_unique( array_merge( $modules, array( $module ) ) );
		sort( $modules );
		update_option( 'audiotheme_inactive_modules', $modules );
	}
}
