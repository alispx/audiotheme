<?php
/**
 * General utility methods.
 *
 * @package AudioTheme
 * @since 2.0.0
 */

namespace AudioTheme\Core;

use AudioTheme\Core\ObjectSorter;

/**
 * General utility methods class.
 *
 * @package AudioTheme
 * @since 2.0.0
 */
class Util {
	/**
	 * Remove a portion of an associative array, optionally replace it with
	 * something else and maintain the keys.
	 *
	 * Can produce unexpected behavior with numeric indexes. Use array_splice() if
	 * keys don't need to be preserved, although exact behavior of offset and
	 * length is not duplicated.
	 *
	 * @since 2.0.0
	 *
	 * @version 1.0.0
	 * @see array_splice()
	 *
	 * @param array $input The input array.
	 * @param int $offset The position to start from.
	 * @param int $length Optional. The number of elements to remove. Defaults to 0.
	 * @param mixed $replacement Optional. Item(s) to replace removed elements.
	 * @param string $primary Optiona. input|replacement Defaults to input. Which array should take precedence if there is a key collision.
	 * @return array The modified array.
	 */
	public static function array_asplice( $input, $offset, $length = 0, $replacement = null, $primary = 'input' ) {
		$input = (array) $input;
		$replacement = (array) $replacement;

		$start = array_slice( $input, 0, $offset, true );
		// $remove = array_slice( $input, $offset, $length, true );
		$end = array_slice( $input, $offset + $length, null, true );

		// Discard elements in $replacement whose keys match keys in $input.
		if ( 'input' == $primary ) {
			$replacement = array_diff_key( $replacement, $input );
		}

		// Discard elements in $start and $end whose keys match keys in $replacement.
		// Could change the size of $input, so this is done after slicing the start and end.
		elseif ( 'replacement' == $primary ) {
			$start = array_diff_key( $start, $replacement );
			$end = array_diff_key( $end, $replacement );
		}

		// Which is faster?
		// return $start + $replacement + $end;
		return array_merge( $start, $replacement, $end );
	}

	/**
	 * Insert an element(s) after a particular value if it exists in an array.
	 *
	 * @since 1.0.0
	 *
	 * @version  2.0.0
	 * @uses AudioTheme\Core\Util::array_find()
	 * @uses AudioTheme\Core\Util::array_asplice()
	 *
	 * @param array $input The input array.
	 * @param mixed $needle Value to insert new elements after.
	 * @param mixed $insert The element(s) to insert.
	 * @return array|bool Modified array or false if $needle couldn't be found.
	 */
	public static function array_insert_after( $input, $needle, $insert ) {
		$input = (array) $input;
		$insert = (array) $insert;

		$position = self::array_find( $needle, $input );
		if ( false === $position ) {
			return false;
		}

		return self::array_asplice( $input, $position + 1, 0, $insert );
	}

	/**
	 * Insert an element(s) after a certain key if it exists in an array.
	 *
	 * Use array_splice() if keys don't need to be maintained.
	 *
	 * @since 2.0.0
	 *
	 * @version 1.0.0
	 * @uses AudioTheme\Core\Util::array_key_find()
	 * @uses AudioTheme\Core\Util::array_asplice()
	 *
	 * @param array $input The input array.
	 * @param mixed $needle Value to insert new elements after.
	 * @param mixed $insert The element(s) to insert.
	 * @return array|bool Modified array or false if $needle couldn't be found.
	 */
	public static function array_insert_after_key( $input, $needle, $insert ) {
		$input = (array) $input;
		$insert = (array) $insert;

		$position = self::array_key_find( $needle, $input );
		if ( false === $position ) {
			return false;
		}

		return self::array_asplice( $input, $position + 1, 0, $insert );
	}

	/**
	 * Find the position (not index) of a value in an array.
	 *
	 * @since 2.0.0
	 *
	 * @version 1.0.0
	 * @see array_search()
	 * @uses AudioTheme\Core\Util::array_key_find()
	 *
	 * @param mixed $needle The value to search for.
	 * @param array $haystack The array to search.
	 * @param bool $strict Whether to search for identical (types) values.
	 * @return int|bool Position of the first matching element or false if not found.
	 */
	public static function array_find( $needle, $haystack, $strict = false ) {
		if ( ! is_array( $haystack ) ) {
			return false;
		}

		$key = array_search( $needle, $haystack, $strict );

		return ( $key ) ? self::array_key_find( $key, $haystack ) : false;
	}

	/**
	 * Find the position (not index) of a key in an array.
	 *
	 * @since 2.0.0
	 *
	 * @version 1.0.0
	 * @see array_key_exists()
	 *
	 * @param $key string|int The key to search for.
	 * @param $search The array to search.
	 * @return int|bool Position of the key or false if not found.
	 */
	public static function array_key_find( $key, $search ) {
		$key = ( is_int( $key ) ) ? $key : (string) $key;

		if ( ! is_array( $search ) ) {
			return false;
		}

		$keys = array_keys( $search );

		return array_search( $key, $keys );
	}

	/**
	 * Return key value pairs with argument and operation separators.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data Array of properties.
	 * @param string $arg_separator Separator between arguments.
	 * @param string $value_separator Separator between keys and values.
	 * @return array string
	 */
	public static function build_query( $data, $arg_separator = '|', $value_separator = ':' ) {
		$output = http_build_query( $data, null, $arg_separator );
		return str_replace( '=', $value_separator, $output );
	}

	/**
	 * Return a base64 encoded SVG icon for use as a data URI.
	 *
	 * @since 2.0.0
	 *
	 * @param string $path Path to SVG icon.
	 * @return string
	 */
	public static function encode_svg( $path ) {
		$path = path_is_absolute( $path ) ? $path : AUDIOTHEME_DIR . $path;

		if ( ! file_exists( $path ) || 'svg' != pathinfo( $path, PATHINFO_EXTENSION ) ) {
			return '';
		}

		return 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( $path ) );
	}

	/**
	 * Encode the path portion of a URL.
	 *
	 * Spaces in directory or filenames are stripped by esc_url() and can cause
	 * issues when requesting a URL programmatically. This method encodes spaces and
	 * other characters.
	 *
	 * @since 2.0.0
	 *
	 * @param string $url A URL.
	 * @return string
	 */
	public static function encode_url_path( $url ) {
		$parts = parse_url( $url );

		$return  = isset( $parts['scheme'] ) ? $parts['scheme'] . '://' : '';
		$return .= isset( $parts['host'] ) ? $parts['host'] : '';
		$return .= isset( $parts['port'] ) ? ':' . $parts['port'] : '';
		$user = isset( $parts['user'] ) ? $parts['user'] : '';
		$pass = isset( $parts['pass'] ) ? ':' . $parts['pass']  : '';
		$return .= ( $user || $pass ) ? "$pass@" : '';

		if ( isset( $parts['path'] ) ) {
			$path = implode( '/', array_map( 'rawurlencode', explode( '/', $parts['path'] ) ) );
			$return .= $path;
		}

		$return .= isset( $parts['query'] ) ? '?' . $parts['query'] : '';
		$return .= isset( $parts['fragment'] ) ? '#' . $parts['fragment'] : '';

		return $return;
	}

	/**
	 * Convert a date string to MySQL DateTime format.
	 *
	 * @since 2.0.0
	 *
	 * @param string $datetime Date or date and time string.
	 * @param string $time Optional. Time string.
	 * @return string Blank string if input couldn't be converted.
	 */
	public static function format_datetime_string( $datetime, $time = '' ) {
		$dt       = date_parse( $datetime . ' ' . $time );
		$datetime = '';

		if ( checkdate( $dt['month'], $dt['day'], $dt['year'] ) ) {
			$datetime = sprintf(
				'%d-%s-%s %s:%s:%s',
				$dt['year'],
				zeroise( $dt['month'], 2 ),
				zeroise( $dt['day'], 2 ),
				zeroise( $dt['hour'], 2 ),
				zeroise( $dt['minute'], 2 ),
				zeroise( $dt['second'], 2 )
			);
		}

		return $datetime;
	}

	/**
	 * Convert a time string to the format used in the time portion of MySQL DateTime.
	 *
	 * @since 2.0.0
	 *
	 * @param string $time Time string.
	 * @return string
	 */
	public static function format_time_string( $time ) {
		$t = date_parse( $time );

		if ( empty( $t['errors'] ) ) {
			$time = sprintf( '%s:%s:%s',
				zeroise( $t['hour'], 2 ),
				zeroise( $t['minute'], 2 ),
				zeroise( $t['second'], 2 )
			);
		}

		return $time;
	}

	/**
	 * Helper function to determine if a shortcode attribute is true or false.
	 *
	 * @since 2.0.0
	 *
	 * @param string|int|bool $var Attribute value.
	 * @return bool
	 */
	public static function shortcode_bool( $var ) {
		$falsey = array( 'false', '0', 'no', 'n' );
		return ( ! $var || in_array( strtolower( $var ), $falsey ) ) ? false : true;
	}

	/**
	 * Sort an array of objects by an objects properties.
	 *
	 * Ex: sort_objects( $gigs, array( 'venue', 'name' ), 'asc', true, 'gig_datetime' );
	 *
	 * @since 2.0.0
	 * @uses AudioTheme_Sort_Objects
	 *
	 * @param array $objects An array of objects to sort.
	 * @param string $orderby The object property to sort on.
	 * @param string $order The sort order; ASC or DESC.
	 * @param bool $unique Optional. If the objects have an ID property, it will be used for the array keys, thus they'll unique. Defaults to true.
	 * @param string $fallback Optional. Comma-delimited string of properties to sort on if $orderby property is equal.
	 * @return array The array of sorted objects.
	 */
	public static function sort_objects( $objects, $orderby, $order = 'ASC', $unique = true, $fallback = null ) {
		if ( ! is_array( $objects ) ) {
			return false;
		}

		usort( $objects, array( new ObjectSorter( $orderby, $order, $fallback ), 'sort' ) );

		// Use object ids as the array keys.
		if ( $unique && count( $objects ) && isset( $objects[0]->ID ) ) {
			$objects = array_combine( wp_list_pluck( $objects, 'ID' ), $objects );
		}

		return $objects;
	}

	/**
	 * Gives a nicely formatted list of timezone strings.
	 *
	 * Strips the manual offsets from the default WordPress list.
	 *
	 * @since 2.0.0
	 * @uses wp_timezone_choice()
	 *
	 * @param string $selected_zone Selected Zone.
	 * @return string
	 */
	public static function timezone_choice( $selected_zone = null ) {
		$selected = ( empty( $selected_zone ) ) ? get_option( 'timezone_string' ) : $selected_zone;
		$choices = wp_timezone_choice( $selected );

		// Remove the manual offsets optgroup.
		$pos = strrpos( $choices, '<optgroup' );
		if ( false !== $pos ) {
			$choices = substr( $choices, 0, $pos );
		}

		return apply_filters( 'audiotheme_timezone_dropdown', $choices, $selected );
	}
}
