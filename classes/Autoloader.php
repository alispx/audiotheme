<?php
/**
 * Autoloader.
 *
 * @package AudioTheme\Core
 * @since 2.0.0
 */

namespace AudioTheme\Core;

/**
 * Autoloader class.
 *
 * @package AudioTheme\Core
 * @since 2.0.0
 * @link http://www.php-fig.org/psr/psr-4/examples/
 */
class Autoloader {
	/**
	 * An associative array where the key is a namespace prefix and the value is
	 * an array of base directories for classes in that namespace.
	 *
	 * @since 2.0.0
	 * @type array
	 */
	protected $prefixes = array();

	/**
	 * An associative array where the key is a class name and the value is the
	 * absolute path to the file with the class definition.
	 *
	 * @since 2.0.0
	 * @type array
	 */
	protected $classmap = array();

	/**
	 * Register loader with SPL autoloader stack.
	 */
	public function register() {
		spl_autoload_register( array( $this, 'load_class' ) );
	}

	/**
	 * Add a class to the class map.
	 *
	 * @since 2.0.0
	 *
	 * @param string $class Class name.
	 * @param string $file Absolute path to file with the class definition.
	 */
	public function add_class( $class, $file ) {
		$this->class_map[ $class ] = $file;
	}

	/**
	 * Add an array of classes to the class map.
	 *
	 * @since 2.0.0
	 *
	 * @param array $classes An associative array where the key is a class name and the value is the absolute path to the file with the class definition.
	 */
	public function add_classes( $classes ) {
		foreach ( $classes as $class => $file ) {
			$this->add_class( $class, $file );
		}
	}

	/**
	 * Adds a base directory for a namespace prefix.
	 *
	 * @param string $prefix The namespace prefix.
	 * @param string $base_directory A base directory for class files in the namespace.
	 * @param bool $prepend If true, prepend the base directory to the stack instead of appending it; this causes it to be searched first rather than last.
	 */
	public function add_namespace( $prefix, $base_directory, $prepend = false ) {
		$prefix         = trim( $prefix, '\\' ) . '\\';
		$base_directory = rtrim( $base_directory, DIRECTORY_SEPARATOR ) . '/';

		// Initialize the namespace prefix array.
		if ( isset( $this->prefixes[ $prefix] ) === false ) {
			$this->prefixes[$prefix] = array();
		}

		// Retain the base directory for the namespace prefix.
		if ( $prepend ) {
			array_unshift( $this->prefixes[ $prefix ], $base_directory );
		} else {
			array_push( $this->prefixes[ $prefix ], $base_directory );
		}
	}

	/**
	 * Loads the class file for a given class name.
	 *
	 * @param string $class The fully-qualified class name.
	 * @return mixed The mapped file name on success, or boolean false on failure.
	 */
	public function load_class( $class ) {
		$prefix = $class;

		// Check the class map first.
		if ( isset( $this->classmap[ $class ] ) ) {
			return $this->require_file( $this->classmap[ $class ] );
		}

		// Work backwards through the namespace names of the fully-qualified
		// class name to find a mapped file name.
		while ( false !== $position = strrpos( $prefix, '\\' ) ) {

			// Retain the trailing namespace separator in the prefix.
			$prefix = substr( $class, 0, $position + 1 );

			// The rest is the relative class name.
			$relative_class = substr( $class, $position + 1 );

			// Try to load a mapped file for the prefix and relative class.
			$mapped_file = $this->load_mapped_file( $prefix, $relative_class );
			if ( $mapped_file ) {
				return $mapped_file;
			}

			// Remove the trailing namespace separator for the next iteration
			// of strrpos().
			$prefix = rtrim( $prefix, '\\' );
		}

		return false;
	}

	/**
	 * Load the mapped file for a namespace prefix and relative class.
	 *
	 * @param string $prefix The namespace prefix.
	 * @param string $relative_class The relative class name.
	 * @return mixed Boolean false if no mapped file can be loaded, or the name of the mapped file that was loaded.
	 */
	protected function load_mapped_file( $prefix, $relative_class ) {
		// Are there any base directories for this namespace prefix?
		if ( false === isset( $this->prefixes[ $prefix ] ) ) {
			return false;
		}

		// Look through base directories for this namespace prefix.
		foreach ( $this->prefixes[ $prefix ] as $base_directory ) {

			// Replace the namespace prefix with the base directory, replace
			// namespace separators with directory separators in the relative
			// class name, append with .php
			$file = $base_directory . str_replace( '\\', '/', $relative_class ) . '.php';

			// If the mapped file exists, require it.
			if ( $this->require_file( $file ) ) {
				return $file;
			}
		}

		return false;
	}

	/**
	 * If a file exists, require it from the file system.
	 *
	 * @param string $file The file to require.
	 * @return bool True if the file exists, false if not.
	 */
	protected function require_file( $file ) {
		if ( file_exists( $file ) ) {
			require( $file );
			return true;
		}
		return false;
	}
}
