<?php
/**
 * Deprecated functions from past AudioTheme versions.
 *
 * These should no longer be used.
 *
 * @package AudioTheme\Deprecated
 * @since 2.0.0
 */

/**
 * Old setup method. Used to determine if AudioTheme was active.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_load() {}

/**
 * Get record type strings.
 *
 * List of default record types to better define the record, much like a post
 * format.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @return array List of record types.
 */
function get_audiotheme_record_type_strings() {
	$strings = array(
		'record-type-album'  => _x( 'Album',  'Record type', 'audiotheme' ),
		'record-type-single' => _x( 'Single', 'Record type', 'audiotheme' ),
	);

	/**
	 * Filter the list of available of record types.
	 *
	 * Terms will be registered automatically for new record types. Keys must
	 * be prefixed with 'record-type'.
	 *
	 * @since 1.5.0
	 *
	 * @param array strings List of record types. Keys must be prefixed with 'record-type-'.
	 */
	return apply_filters( 'audiotheme_record_type_strings', $strings );
}

/**
 * Get record type slugs.
 *
 * Gets an array of available record type slugs from record type strings.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @return array List of record type slugs.
 */
function get_audiotheme_record_type_slugs() {
	$slugs = array_keys( get_audiotheme_record_type_strings() );
	return $slugs;
}

/**
 * Get record type string.
 *
 * Sets default value of record type if option is not set.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string Record type slug.
 * @return string Record type label.
 */
function get_audiotheme_record_type_string( $slug ) {
	$strings = get_audiotheme_record_type_strings();

	if ( ! $slug ) {
		return $strings['record-type-album'];
	} else {
		return ( isset( $strings[ $slug ] ) ) ? $strings[ $slug ] : '';
	}
}

if ( ! function_exists( 'get_audiotheme_option' ) ) :
/**
 * Returns an option value.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $option_name Option name as stored in database.
 * @param string $key Optional. Index of value in the option array.
 * @param mixed $default Optional. A default value to return if the requested option doesn't exist.
 * @return mixed The option value or $default.
 */
function get_audiotheme_option( $option_name, $key = null, $default = null ) {
	$option = get_option( $option_name );

	if ( $key == $option_name || empty( $key ) ) {
		return ( $option ) ? $option : $default;
	}

	return ( isset( $option[ $key ] ) ) ? $option[ $key ] : $default;
}
endif;

if ( ! function_exists( 'get_audiotheme_theme_option' ) ) :
/**
 * Returns a theme option value.
 *
 * Function called to get a theme option. The returned value defaults to false
 * unless a default is passed.
 *
 * Note that this function footprint is slightly different than
 * get_audiotheme_option(). While working in themes, the $option_name shouldn't
 * necessarily need to be known or required, so it should be slightly easier to
 * use while in a theme.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @uses get_audiotheme_option()
 *
 * @param string The option key
 * @param mixed Optional. Default value to return if option key doesn't exist.
 * @param string Optional. Retrieve a non-standard option.
 * @return mixed The option value or $default or false.
 */
function get_audiotheme_theme_option( $key, $default = false, $option_name = '' ) {
	$option_name = ( empty( $option_name ) ) ? get_audiotheme_theme_options_name() : $option_name;

	return get_audiotheme_option( $option_name, $key, $default );
}
endif;

if ( ! function_exists( 'get_audiotheme_theme_options_name' ) ) :
/**
 * Retrieve the registered option name for theme options.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @uses get_audiotheme_theme_options_support()
 */
function get_audiotheme_theme_options_name() {
	static $option_name;

	if ( ! isset( $option_name ) && ( $name = get_audiotheme_theme_options_support( 'option_name' ) ) ) {
		// The default option name is the first one registered in add_theme_support().
		$option_name = ( is_array( $name ) ) ? $name[0] : $name;
	}

	return ( isset( $option_name ) ) ? $option_name : false;
}
endif;

if ( ! function_exists( 'get_audiotheme_theme_options_support' ) ) :
/**
 * Check if the theme supports theme options and return registered arguments
 * with supplied defaults.
 *
 * Adding support for theme options is as simple as:
 * add_theme_support( 'audiotheme-theme-options' );
 *
 * Additional arguments can be supplied for more control. If the second
 * parameter is a string, it will be the callback for registering theme
 * options. Otherwise, it should be an array of arguments.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @uses get_theme_support()
 *
 * @param string $var Optional. Specific argument to return.
 * @return mixed Value of requested argument or theme option support arguments.
 */
function get_audiotheme_theme_options_support( $var = null ) {
	if ( $support = get_theme_support( 'audiotheme-theme-options' ) ) {
		$option_name = 'audiotheme_mods-' . get_option( 'stylesheet' );

		$args = array(
			'callback'    => 'audiotheme_register_theme_options',
			'option_name' => $option_name,
			'menu_title'  => __( 'Theme Options', 'audiotheme' ),
		);

		if ( isset( $support[0] ) ) {
			if ( is_array( $support[0] ) ) {
				$args = wp_parse_args( $support[0], $args );
			} elseif ( is_string( $support[0] ) ) {
				$args['callback'] = $support[0];
			}
		}

		// Reset the option name if it was blanked out.
		if ( empty( $args['option_name'] ) ) {
			$args['option_name'] = $option_name;
		}

		// Option names can be arrays, so make sure it's always an array and sanitize each name.
		$args['option_name'] = array_map( 'sanitize_key', (array) $args['option_name'] );

		// If a specific arg is requested and it exists, return it, otherwise return false.
		if ( ! empty( $var ) ) {
			return ( isset( $args[ $var ] ) ) ? $args[ $var ] : false;
		}

		// Return the args.
		return $args;
	}

	return false;
}
endif;

/**
 * Load the LESS compiler and set up Theme Customizer support.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_less_setup() {
	if ( $support = get_theme_support( 'audiotheme-less' ) ) {
		wp_less::instance();

		add_action( 'wp_loaded', 'audiotheme_less_register_vars', 20 );
		add_filter( 'wp_less_cache_url', 'audiotheme_less_force_ssl' );

		// Register a style sheet specifically for the Theme Customizer.
		$stylesheet = ( empty( $support[0]['customize_stylesheet'] ) ) ? '' : $support[0]['customize_stylesheet'];
		if ( ! empty( $stylesheet ) ) {
			wp_register_style( 'audiotheme-less-customize', $stylesheet );
			add_action( 'wp_footer', 'audiotheme_less_customize_enqueue_stylesheet' );
		}
	}
}

/**
 * Force SSL on LESS cache URLs.
 *
 * @since 1.3.1
 * @deprecated 2.0.0
 *
 * @param string $url URL to compiled CSS.
 * @return string
 */
function audiotheme_less_force_ssl( $dir ) {
	if ( is_ssl() ) {
		$dir = set_url_scheme( $dir, 'https' );
	}

	return $dir;
}

/**
 * Execute the callback function to register LESS vars and fire an action so
 * additional vars can be registered.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_less_register_vars() {
	$support = get_theme_support( 'audiotheme-less' );
	$callback = ( empty( $support[0]['less_vars_callback'] ) ) ? '' : $support[0]['less_vars_callback'];

	// Always points to the parent theme.
	add_less_var( 'templateurl', '~"' . get_template_directory_uri() . '/"' );

	if ( ! empty( $callback ) && function_exists( $callback ) ) {
		call_user_func( $callback );
	}

	do_action( 'audiotheme_less_register_vars' );
}

/**
 * Enqueue the Theme Customizer style sheet.
 *
 * This should only be run after the main style sheets have been output in
 * order to prevent changes from being made live prematurely.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_less_customize_enqueue_stylesheet() {
	global $wp_customize;

	// Load a separate customizer stylesheet when the customizer is being used.
	// Should prevent temporary changes from displaying on the front-end.
	if ( ! $wp_customize || ! $wp_customize->is_preview() ) {
		return;
	}

	// Enqueue the Theme Customizer style sheet if it has been registered.
	if ( wp_style_is( 'audiotheme-less-customize', 'registered' ) ) {
		add_filter( 'less_force_compile', '__return_true' );
		wp_enqueue_style( 'audiotheme-less-customize' );
	}
}
