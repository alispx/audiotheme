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
class AudioTheme_Modules extends AudioTheme_Collection {
	/**
	 * Whether a module is active.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id Module identifier.
	 * @return bool
	 */
	public function is_active( $id ) {
		return $this->items[ $id ]->is_active();
	}

	/**
	 * Retrieve all active modules.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_active() {
		$active = array();
		foreach ( $this->items as $id => $module ) {
			if ( ! $module->is_active() ) {
				continue;
			}

			$active[ $id ] = $module;
		}
		return $active;
	}

	/**
	 * Retrieve inactive modules.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_inactive() {
		$inactive = array();
		foreach ( $this->items as $id => $module ) {
			if ( $module->is_active() ) {
				continue;
			}

			$inactive[ $id ] = $module;
		}
		return $inactive;
	}

	/**
	 * Activate a module.
	 *
	 * @since 2.0.0
	 *
	 * @param string $module Module identifier.
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
	 * @param string $module Module identifier.
	 */
	public function deactivate( $module ) {
		$modules = array_keys( $this->get_inactive() );
		$modules = array_unique( array_merge( $modules, array( $module ) ) );
		sort( $modules );
		update_option( 'audiotheme_inactive_modules', $modules );
	}
}
