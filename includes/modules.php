<?php
/**
 * AudioTheme Modules
 *
 * @package AudioTheme
 * @subpackage Modules
 * @since 2.0.0
 */

/**
 * Retrieve a list of modules.
 *
 * @since 2.0.0
 *
 * @return array
 */
function audiotheme_get_modules() {
	$modules = array(
		'discography' => array(
			'name'          => __( 'Discography', 'audiotheme' ),
			'is_active'     => audiotheme_is_module_active( 'discography' ),
			'admin_menu_id' => 'toplevel_page_edit-post_type-audiotheme_record',
		),
		'gigs' => array(
			'name'          => __( 'Gigs', 'audiotheme' ),
			'is_active'     => audiotheme_is_module_active( 'gigs' ),
			'admin_menu_id' => 'toplevel_page_audiotheme-gigs',
		),
		'videos' => array(
			'name'          => __( 'Videos', 'audiotheme' ),
			'is_active'     => audiotheme_is_module_active( 'videos' ),
			'admin_menu_id' => 'menu-posts-audiotheme_video',
		),
	);

	return $modules;
}

/**
 * Retrieve data for a single module.
 *
 * @since 2.0.0
 *
 * @param string $module A module identifier.
 * @return array
 */
function audiotheme_get_module( $module ) {
	$modules = audiotheme_get_modules();
	return $modules[ $module ];
}

/**
 * Whether a module is active.
 *
 * @since 2.0.0
 *
 * @param string $module A module identifier.
 * @return bool
 */
function audiotheme_is_module_active( $module ) {
	$active_modules = get_option( 'audiotheme_active_modules', array(
		'discography',
		'gigs',
		'videos',
	) );

	return in_array( $module, $active_modules );
}

/**
 * Retrieve a list of active modules.
 *
 * @since 2.0.0
 *
 * @return array
 */
function audiotheme_get_active_modules() {
	$modules = audiotheme_get_modules();
	return wp_list_filter( $modules, array( 'is_active' => true ) );
}

/**
 * Retrieve a list of inactive modules.
 *
 * @since 2.0.0
 *
 * @return array
 */
function audiotheme_get_inactive_modules() {
	$modules = audiotheme_get_modules();
	return wp_list_filter( $modules, array( 'is_active' => false ) );
}

/**
 * Activate a module.
 *
 * @since 2.0.0
 *
 * @param string $module A module identifier.
 */
function audiotheme_activate_module( $module ) {
	$modules = array_keys( audiotheme_get_active_modules() );
	$modules = array_unique( array_merge( $modules, array( $module ) ) );
	sort( $modules );
	update_option( 'audiotheme_active_modules', $modules );
}

/**
 * Deactivate a module.
 *
 * @since 2.0.0
 *
 * @param string $module A module identifier.
 */
function audiotheme_deactivate_module( $module ) {
	$modules = array_keys( audiotheme_get_active_modules() );
	unset( $modules[ array_search( $module, $modules ) ] );
	update_option( 'audiotheme_active_modules', array_values( $modules ) );
}
