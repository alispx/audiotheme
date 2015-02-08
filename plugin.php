<?php
/**
 * Plugin initialization.
 *
 * @package AudioTheme
 * @since 2.0.0
 */

/**
 * Load the Composer autoloader.
 */
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require( __DIR__ . '/vendor/autoload.php' );
}

use AudioTheme\Core\Plugin;
use AudioTheme\Core\PluginServiceProvider;

/**
 * Load functions and template tags.
 */
require( AUDIOTHEME_DIR . 'includes/deprecated.php' );
require( AUDIOTHEME_DIR . 'includes/template-tags/archive.php' );
require( AUDIOTHEME_DIR . 'includes/template-tags/discography.php' );
require( AUDIOTHEME_DIR . 'includes/template-tags/feed.php' );
require( AUDIOTHEME_DIR . 'includes/template-tags/general.php' );
require( AUDIOTHEME_DIR . 'includes/template-tags/gigs.php' );
require( AUDIOTHEME_DIR . 'includes/template-tags/videos.php' );

if ( is_admin() ) {
	require( AUDIOTHEME_DIR . 'admin/ajax-actions.php' );
	require( AUDIOTHEME_DIR . 'admin/functions.php' );
}

/**
 * Autoloader callback.
 *
 * @param string $class Class name.
 */
function audiotheme_autoloader( $class ) {
	$classes = array(
		'wp_less'       => AUDIOTHEME_DIR . '/vendor/icit/wp-less/wp-less.php',
		'wp_list_table' => ABSPATH . 'wp-admin/includes/class-wp-list-table.php',
	);

	$class = strtolower( $class );
	if ( isset( $classes[ $class ] ) ) {
		require_once( $classes[ $class ] );
	}
}
spl_autoload_register( 'audiotheme_autoloader' );

/**
 * Retrieve the AudioTheme plugin instance.
 *
 * @since 2.0.0
 *
 * @param string $service Optional. Service identifier.
 * @return AudioTheme\Core\Plugin|object The main AudioTheme plugin instance or a service.
 */
function audiotheme( $service = null ) {
	static $instance;

	if ( null === $instance ) {
		$instance = new Plugin;
	}

	return empty( $service ) ? $instance : $instance[ $service ];
}

/**
 * Initialize the plugin and register services.
 */
$audiotheme = audiotheme();
$audiotheme['plugin_file'] = AUDIOTHEME_DIR . '/audiotheme.php';
$audiotheme->register( new PluginServiceProvider() );
add_action( 'plugins_loaded', array( $audiotheme, 'load' ) );
