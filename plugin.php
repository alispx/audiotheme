<?php
/**
 * Plugin initialization.
 *
 * @package AudioTheme
 * @since 2.0.0
 */

use AudioTheme\Core\Autoloader;
use AudioTheme\Core\Plugin;
use AudioTheme\Core\PluginServiceProvider;

/**
 * Load and configure the autoloader.
 */
require( AUDIOTHEME_DIR . 'classes/Autoloader.php' );

// @todo Make sure a root Composer autoloader takes precedence.
$audiotheme_autoloader = new Autoloader;
$audiotheme_autoloader->register();
$audiotheme_autoloader->add_namespace( 'AudioTheme\\Core\\', AUDIOTHEME_DIR . 'classes/', true );
$audiotheme_autoloader->add_classes( array(
	'Gamajo_Template_Loader' => AUDIOTHEME_DIR . 'vendor/gamajo/template-loader/class-gamajo-template-loader.php',
	'lessc'                  => AUDIOTHEME_DIR . 'vendor/leafo/lessphp/lessc.inc.php',
	'wp_less'                => AUDIOTHEME_DIR . 'vendor/icit/wp-less/wp-less.php',
	'WP_List_Table'          => ABSPATH . 'wp-admin/includes/class-wp-list-table.php',
) );

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
require( AUDIOTHEME_DIR . 'vendor/scribu/scb-framework/load.php' );

if ( is_admin() ) {
	require( AUDIOTHEME_DIR . 'admin/ajax-actions.php' );
	require( AUDIOTHEME_DIR . 'admin/functions.php' );
}

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
$audiotheme['autoloader']          = $audiotheme_autoloader;
$audiotheme['plugin_file']         = AUDIOTHEME_DIR . '/audiotheme.php';
$audiotheme['default_hooks_class'] = '\AudioTheme\Core\DefaultHooks';
$audiotheme->register( new PluginServiceProvider() );
add_action( 'plugins_loaded', array( $audiotheme, 'load' ) );
