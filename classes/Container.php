<?php
/**
 * Basic container for managing and retrieving objects.
 *
 * @package AudioTheme
 * @since 2.0.0
 */

/**
 * Basic container class.
 *
 * @package AudioTheme
 * @since 2.0.0
 */
class AudioTheme_Container implements ArrayAccess {
	/**
	 * List of items added to the container.
	 *
	 * @since 2.0.0
	 * @type array
	 */
	protected $container = array();

	/**
	 * List of instantiated objects.
	 *
	 * @since 2.0.0
	 * @type array
	 */
	protected $objects = array();

	/**
	 * Instantiate and retrieve all objects.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_all() {
		$objects = array();
		foreach ( $this->container as $id => $item ) {
			$objects[ $id ] = $this[ $id ];
		}
		return $objects;
	}

	/**
	 * Whether an item as been registered.
	 *
	 * @since 2.0.0
	 *
	 * @param string $offset Item identifier.
	 * @return bool
	 */
	public function offsetExists( $offset ) {
		return isset( $this->container[ $offset ] );
	}

	/**
	 * Retrieve an object.
	 *
	 * @since 2.0.0
	 *
	 * @param string $offset Item identifier.
	 * @return array
	 */
	public function offsetGet( $offset ) {
		$object = null;

		if ( isset( $this->objects[ $offset ] ) ) {
			$object = $this->objects[ $offset ];
		} elseif ( isset( $this->container[ $offset ] ) && class_exists( $this->container[ $offset ] ) ) {
			$object = new $this->container[ $offset ];
			$this->objects[ $offset ] = $object;
		}
		return $object;
	}

	/**
	 * Register an item.
	 *
	 * @since 2.0.0
	 *
	 * @param string $offset Item identifier.
	 * @param array $value Item data.
	 */
	public function offsetSet( $offset, $value ) {
		$this->container[ $offset ] = $value;
	}

	/**
	 * Unset an item.
	 *
	 * @since 2.0.0
	 *
	 * @param string $offset Item identifier.
	 */
	public function offsetUnset( $offset ) {
		unset( $this->container[ $offset ] );
		unset( $this->objects[ $offset ] );
	}
}
