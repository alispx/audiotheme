<?php
/**
 * Object sorting functionality.
 *
 * @package AudioTheme
 * @since 2.0.0
 */

namespace AudioTheme\Core;

/**
 * Object list sorting class.
 *
 * @since 2.0.0
 */
class ObjectSorter {
	protected $fallback;
	protected $order;
	protected $orderby;

	// Fallback is limited to working with properties of the parent object.
	public function __construct( $orderby, $order, $fallback = null ) {
		$this->order    = ( 'desc' == strtolower( $order ) ) ? 'DESC' : 'ASC';
		$this->orderby  = $orderby;
		$this->fallback = $fallback;
	}

	public function sort( $a, $b ) {
		if ( is_string( $this->orderby ) ) {
			$a_value = $a->{$this->orderby};
			$b_value = $b->{$this->orderby};
		} elseif ( is_array( $this->orderby ) ) {
			$a_value = $a;
			$b_value = $b;

			foreach( $this->orderby as $prop ) {
				$a_value = isset( $a_value->$prop ) ? $a_value->$prop : '';
				$b_value = isset( $b_value->$prop ) ? $b_value->$prop : '';
			}
		}

		if ( $a_value == $b_value ) {
			if ( ! empty( $this->fallback ) ) {
				$properties = explode( ',', $this->fallback );
				foreach( $properties as $prop ) {
					if ( $a->$prop != $b->$prop ) {
						#printf( '(%s - %s) - (%s - %s)<br>', $a_value, $a->$prop, $b_value, $b->$prop );
						return $this->compare( $a->$prop, $b->$prop );
					}
				}

			}

			return 0;
		}

		return $this->compare( $a_value, $b_value );
	}

	public function compare( $a, $b ) {
		if ( $a < $b ) {
			return 'ASC' == $this->order ? -1 : 1;
		} else {
			return 'ASC' == $this->order ? 1 : -1;
		}
	}
}
