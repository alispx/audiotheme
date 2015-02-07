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
 * @package AudioTheme\ModuleCollection
 * @since 2.0.0
 */
class ModuleCollection extends Container {
	/**
	 * Whether a module is active.
	 *
	 * @since 2.0.0
	 *
	 * @param string $module_id Module identifier.
	 * @return bool
	 */
	public function is_active( $module_id ) {
		$active_modules = get_option( 'audiotheme_inactive_modules', array() );
		return ! in_array( $module_id, $active_modules );
	}

	/**
	 * Retrieve all active modules.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_active_keys() {
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
	public function get_inactive_keys() {
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
	 * @param string $module_id Module identifier.
	 */
	public function activate( $module_id ) {
		$modules = $this->get_inactive_keys();
		unset( $modules[ array_search( $module_id, $modules ) ] );
		update_option( 'audiotheme_inactive_modules', array_values( $modules ) );
	}

	/**
	 * Deactivate a module.
	 *
	 * @since 2.0.0
	 *
	 * @param string $module_id Module identifier.
	 */
	public function deactivate( $module_id ) {
		$modules = $this->get_inactive_keys();
		$modules = array_unique( array_merge( $modules, array( $module_id ) ) );
		sort( $modules );
		update_option( 'audiotheme_inactive_modules', $modules );
	}
}
