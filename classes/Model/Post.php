<?php
/**
 * Basic CPT model.
 *
 * @package AudioTheme\Core
 * @since 2.0.0
 */

namespace AudioTheme\Core\Model;

/**
 * CPT model class.
 *
 * @package AudioTheme\Core
 * @since 2.0.0
 */
abstract class Post {
	/**
	 * WordPress post ID.
	 *
	 * @since 2.0.0
	 * @type int
	 */
	public $ID = 0;

	/**
	 * WordPress post object.
	 *
	 * @since 2.0.0
	 * @type WP_Post
	 */
	protected $post;

	/**
	 * Post type name.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	protected $post_type = 'post';

	/**
	 * Constructor method.
	 *
	 * Retrieves the WP Post object.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $post Slug, post ID, post object, or an array of default attributes.
	 */
	public function __construct( $post = 0 ) {
		// Array of default attributes.
		if ( is_array( $post ) ) {
			// Set default attributes.
			$this->set( $post );
		}

		// Post ID or object.
		elseif ( is_numeric( $post ) || ! is_string( $post ) ) {
			$this->post = get_post( $post );
		}

		// A post slug.
		else {
			$this->post = get_page_by_path( $post, OBJECT, $this->post_type );
		}

		// Don't fetch attributes if a post hasn't been initialized.
		if ( empty( $this->post ) ) {
			return;
		}

		$attributes = array();
		$meta       = (array) get_post_custom( $this->post->ID );

		// Grab metadata and strip the '_audiotheme_' prefix.
		foreach( $meta as $key => $value ) {
			// Serialized and non-single metadata should be
			// set in child model constructors.
			if ( ! isset( $value[0] ) ) {
				continue;
			}

			$unprefixed = str_replace( '_audiotheme_', '', $key );
			$attributes[ $unprefixed ] = $value[0];
		}

		$defaults   = $this->to_array();
		$attributes = array_intersect_key( $attributes, $defaults );
		$attributes = wp_parse_args( $attributes, $defaults );

		// Initialize model attributes from post meta.
		foreach( $attributes as $key => $value ) {
			$this->{$key} = $value;
		}

		$this->ID = $this->post->ID;
	}

	/**
	 *
	 */
	public function __isset( $key ) {
		return isset( $this->post->{$key} );
	}

	/**
	 * Getter method.
	 *
	 * Retrieves a WP_Post property or metadata.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key Property name or meta key.
	 * @return mixed
	 */
	public function __get( $key ) {
		switch ( $key ) {
			default :
				// Check post properties and meta.
				$value = $this->post->{$key};
		}

		return $value;
	}

	/**
	 * Whether a post has been set.
	 *
	 * @since 2.0.0
	 *
	 * @return WP_Post
	 */
	public function has_post() {
		return ! empty( $this->post );
	}

	/**
	 * Retrieve the WP Post object.
	 *
	 * @since 2.0.0
	 *
	 * @return WP_Post
	 */
	public function get_post() {
		return $this->post;
	}

	/**
	 * Prepare the post for JavaScript.
	 *
	 * @since 2.0.0
	 *
	 * @return object
	 */
	public function prepare_for_js() {
		$data = array();

		foreach ( $this->to_array() as $key => $value ) {
			// Underscore to camelCase.
			$js_key = lcfirst( str_replace( ' ', '', ucwords( str_replace( '_', ' ', strtolower( $key ) ) ) ) );
			$data[ $js_key ] = $value;
		}

		return (object) $data;
	}

	/**
	 * Set model attributes.
	 *
	 * @todo Allow key/value arguments, too?
	 *
	 * @since 2.0.0
	 *
	 * @param array $attributes Array of model attributes.
	 */
	public function set( $attributes ) {
		foreach ( array_keys( $this->to_array() ) as $key ) {
			if ( isset( $attributes[ $key ] ) ) {
				$this->{$key} = $attributes[ $key ];
			}
		}
	}

	/**
	 * Convert the model to an array.
	 *
	 * Creates an array from the model's public attributes.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function to_array() {
		return call_user_func( 'get_object_vars', $this );
	}
}
