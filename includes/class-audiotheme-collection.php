<?php
/**
 *
 *
 * @package AudioTheme\
 * @since 2.0.0
 */

/**
 *
 *
 * @package AudioTheme\
 * @since 2.0.0
 */
class AudioTheme_Collection {
	/**
	 * List of items.
	 *
	 * @since 2.0.0
	 * @type array
	 */
	protected $items = array();

	/**
	 * Register an item.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id Item identifier.
	 * @param array $item Item data.
	 */
	public function add( $id, $item = null ) {
		if ( null === $item ) {
			$item = $id;
			$id   = $item->id;
		}

		$this->items[ $id ] = $item;
	}

	/**
	 * Retrieve a single item.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id Item identifier.
	 * @return array
	 */
	public function get( $id ) {
		$modules = $this->get_all();
		return isset( $modules[ $id ] ) ? $modules[ $id ]: null;
	}

	/**
	 * Retrieve all items.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_all() {
		return $this->items;
	}
}
