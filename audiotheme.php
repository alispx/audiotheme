<?php
/**
 * Plugin Name: AudioTheme
 * Plugin URI: https://audiotheme.com/view/audiotheme/
 * Description: A platform for music-oriented websites, allowing for easy management of gigs, discography, videos and more.
 * Version: 2.0.0-alpha
 * Author: AudioTheme
 * Author URI: https://audiotheme.com/
 * Requires at least: 3.8
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
 * @version 1.6.2
 * @author AudioTheme
 * @link https://audiotheme.com/
 * @copyright Copyright 2012 AudioTheme
 * @license GPL-2.0+
 */

/**
 * The AudioTheme version.
 */
define( 'AUDIOTHEME_VERSION', '1.6.2' );

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
require( AUDIOTHEME_DIR . 'includes/archive-template.php' );
require( AUDIOTHEME_DIR . 'includes/default-hooks.php' );
require( AUDIOTHEME_DIR . 'includes/deprecated.php' );
require( AUDIOTHEME_DIR . 'includes/discography-template.php' );
require( AUDIOTHEME_DIR . 'includes/functions.php' );
require( AUDIOTHEME_DIR . 'includes/general-template.php' );
require( AUDIOTHEME_DIR . 'includes/gigs-template.php' );
require( AUDIOTHEME_DIR . 'includes/videos-template.php' );
require( AUDIOTHEME_DIR . 'includes/widgets.php' );
require( AUDIOTHEME_DIR . 'vendor/scribu/lib-posts-to-posts/autoload.php' );
require( AUDIOTHEME_DIR . 'vendor/scribu/scb-framework/load.php' );
require( AUDIOTHEME_DIR . 'includes/load-p2p.php' );

if ( is_admin() ) {
	require( AUDIOTHEME_DIR . 'admin/ajax-actions.php' );
	require( AUDIOTHEME_DIR . 'admin/functions.php' );
}

/**
 * Autoloader callback.
 *
 * Converts a class name to a file path and requires it if it exists.
 *
 * @since 2.0.0
 *
 * @param string $class Class name.
 */
function audiotheme_autoloader( $class ) {
	if ( 0 !== stripos( $class, 'AudioTheme' ) ) {
		return;
	}

	$file  = dirname( __FILE__ );
	$file .= ( false === strpos( $class, 'Admin' ) ) ? '/includes/' : '/admin/';
	$file .= 'class-' . strtolower( str_replace( '_', '-', $class ) ) . '.php';

	if ( file_exists( $file ) ) {
		require_once( $file );
	}
}
spl_autoload_register( 'audiotheme_autoloader' );

/**
 * Retrieve the AudioTheme plugin instance.
 *
 * @since 2.0.0
 *
 * @return AudioTheme
 */
function audiotheme() {
	static $instance;

	if ( null === $instance ) {
		$instance = new AudioTheme;
	}

	return $instance;
}

/**
 * Initialize the plugin.
 */
$audiotheme = audiotheme();
add_action( 'plugins_loaded', array( $audiotheme, 'load_plugin' ) );

/**
 * Define dependencies.
 */
$audiotheme->plugin_file  = __FILE__;
$audiotheme->archives     = new AudioTheme_Archives;
$audiotheme->templates    = new AudioTheme_Template_Loader;
$audiotheme->theme_compat = new AudioTheme_Theme_Compat;

$audiotheme->modules                = new AudioTheme_Modules;
$audiotheme->modules['discography'] = 'AudioTheme_Module_Discography';
$audiotheme->modules['gigs']        = 'AudioTheme_Module_Gigs';
$audiotheme->modules['videos']      = 'AudioTheme_Module_Videos';

if ( is_admin() ) {
	$audiotheme->admin          = new AudioTheme_Admin;
	$audiotheme->admin->modules = new AudioTheme_Container;
	$audiotheme->admin->screens = new AudioTheme_Container;

	$audiotheme->admin->screens['settings'] = 'AudioTheme_Admin_Screen_Settings';

	if ( $audiotheme->modules->is_active( 'discography' ) || $audiotheme->is_settings_screen() ) {
		$audiotheme->admin->modules['discography']    = 'AudioTheme_Admin_Discography';
		$audiotheme->admin->screens['manage_records'] = 'AudioTheme_Admin_Screen_ManageRecords';
		$audiotheme->admin->screens['edit_record']    = 'AudioTheme_Admin_Screen_EditRecord';
		$audiotheme->admin->screens['manage_tracks']  = 'AudioTheme_Admin_Screen_ManageTracks';
		$audiotheme->admin->screens['edit_track']     = 'AudioTheme_Admin_Screen_EditTrack';
	}

	if ( $audiotheme->modules->is_active( 'gigs' ) || $audiotheme->is_settings_screen() ) {
		$audiotheme->admin->modules['gigs']          = 'AudioTheme_Admin_Gigs';
		$audiotheme->admin->screens['manage_gigs']   = 'AudioTheme_Admin_Screen_ManageGigs';
		$audiotheme->admin->screens['edit_gig']      = 'AudioTheme_Admin_Screen_EditGig';
		$audiotheme->admin->screens['manage_venues'] = 'AudioTheme_Admin_Screen_ManageVenues';
		$audiotheme->admin->screens['edit_venue']    = 'AudioTheme_Admin_Screen_EditVenue';
	}

	if ( $audiotheme->modules->is_active( 'videos' ) || $audiotheme->is_settings_screen() ) {
		$audiotheme->admin->modules['videos'] = 'AudioTheme_Admin_Videos';
	}
}
