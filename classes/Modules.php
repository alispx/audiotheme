<?php
/**
 * Modules API.
 *
 * @package AudioTheme\Modules
 * @since 2.0.0
 */

namespace AudioTheme;

use \Pimple\Container;

/**
 * Modules API class.
 *
 * @package AudioTheme\Modules
 * @since 2.0.0
 */
class Modules extends Container {
	/**
	 * Whether a module is active.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id Module identifier.
	 * @return bool
	 */
	public function is_active( $id ) {
		$active_modules = get_option( 'audiotheme_inactive_modules', array() );
		return ! in_array( $id, $active_modules );
	}

	/**
	 * Retrieve all active modules.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_active() {
		$module_ids = array();
		foreach ( $this->keys() as $id ) {
			if ( ! $this->is_active( $id ) ) {
				continue;
			}
			$module_ids[] = $id;
		}
		return $module_ids;
	}

	/**
	 * Retrieve inactive modules.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_inactive() {
		$module_ids = array();
		foreach ( $this->keys() as $id ) {
			if ( $this->is_active( $id ) ) {
				continue;
			}
			$module_ids[] = $id;
		}
		return $module_ids;
	}

	/**
	 * Activate a module.
	 *
	 * @since 2.0.0
	 *
	 * @param string $module Module identifier.
	 */
	public function activate( $module ) {
		$modules = $this->get_inactive();
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
		$modules = $this->get_inactive();
		$modules = array_unique( array_merge( $modules, array( $module ) ) );
		sort( $modules );
		update_option( 'audiotheme_inactive_modules', $modules );
	}
}
