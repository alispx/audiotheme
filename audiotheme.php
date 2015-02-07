<?php
/**
 * Plugin Name: AudioTheme
 * Plugin URI: https://audiotheme.com/view/audiotheme/
 * Description: A platform for music-oriented websites, allowing for easy management of gigs, discography, videos and more.
 * Version: 2.0.0-alpha
 * Author: AudioTheme
 * Author URI: https://audiotheme.com/
 * Requires at least: 4.0
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: audiotheme
 * Domain Path: /languages
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc., 59
 * Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package AudioTheme
 * @version 2.0.0-alpha
 * @author AudioTheme
 * @link https://audiotheme.com/
 * @copyright Copyright 2012 AudioTheme
 * @license GPL-2.0+
 */

/**
 * Load the Composer autoloader.
 */
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require( __DIR__ . '/vendor/autoload.php' );
}

use AudioTheme\Plugin;
use AudioTheme\PluginServiceProvider;

/**
 * The AudioTheme version.
 */
define( 'AUDIOTHEME_VERSION', '2.0.0-alpha' );

/**
 * Framework path and URL.
 */
if ( ! defined( 'AUDIOTHEME_DIR' ) ) {
	define( 'AUDIOTHEME_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'AUDIOTHEME_URI' ) ) {
	define( 'AUDIOTHEME_URI', plugin_dir_url( __FILE__ ) );
}

/**
 * Load functions, libraries and template tags.
 */
require( AUDIOTHEME_DIR . 'includes/deprecated.php' );
require( AUDIOTHEME_DIR . 'includes/load-p2p.php' );
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
 * @return AudioTheme\Plugin|object The main AudioTheme plugin instance or a service.
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
$audiotheme['plugin_file'] = __FILE__;
$audiotheme->register( new PluginServiceProvider() );
add_action( 'plugins_loaded', array( $audiotheme, 'load' ) );
