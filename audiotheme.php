<?php
/**
 * Plugin Name: AudioTheme Framework
 * Plugin URI: https://audiotheme.com/view/audiotheme/
 * Description: A platform for music-oriented websites, allowing for easy management of gigs, discography, videos and more.
 * Version: 1.6.2
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
require( AUDIOTHEME_DIR . 'includes/discography-template.php' );
require( AUDIOTHEME_DIR . 'includes/functions.php' );
require( AUDIOTHEME_DIR . 'includes/general-template.php' );
require( AUDIOTHEME_DIR . 'includes/gigs-template.php' );
require( AUDIOTHEME_DIR . 'includes/load-p2p.php' );
require( AUDIOTHEME_DIR . 'includes/videos-template.php' );
require( AUDIOTHEME_DIR . 'includes/widgets.php' );

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
	if ( 0 !== strpos( $class, 'AudioTheme' ) ) {
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
		$instance = new AudioTheme( array(
			'plugin_file'  => __FILE__,
		) );

		$instance->archives     = new AudioTheme_Archives;
		$instance->theme_compat = new AudioTheme_Theme_Compat;

		/**
		 * Register core modules.
		 *
		 * @since 2.0.0
		 */
		$instance->modules = new AudioTheme_Modules;
		$instance->modules->add( new AudioTheme_Module_Discography );
		$instance->modules->add( new AudioTheme_Module_Gigs );
		$instance->modules->add( new AudioTheme_Module_Videos );

		if ( is_admin() ) {
			$instance->admin          = new AudioTheme_Admin;
			$instance->admin->modules = new AudioTheme_Collection;
			$instance->admin->screens = new AudioTheme_Collection;

			$instance->admin->screens->add( new AudioTheme_Admin_Screen_Settings );

			if ( $instance->modules->get( 'discography' )->is_active() || $instance->is_settings_screen() ) {
				$instance->admin->modules->add( 'discography', new AudioTheme_Admin_Discography );
				$instance->admin->screens->add( new AudioTheme_Admin_Screen_EditRecord );
				$instance->admin->screens->add( new AudioTheme_Admin_Screen_EditTrack );
				$instance->admin->screens->add( new AudioTheme_Admin_Screen_ManageRecords );
				$instance->admin->screens->add( new AudioTheme_Admin_Screen_ManageTracks );
			}

			if ( $instance->modules->get( 'gigs' )->is_active() || $instance->is_settings_screen() ) {
				$instance->admin->modules->add( 'gigs', new AudioTheme_Admin_Gigs );
				$instance->admin->screens->add( new AudioTheme_Admin_Screen_EditGig );
				$instance->admin->screens->add( new AudioTheme_Admin_Screen_EditVenue );
				$instance->admin->screens->add( new AudioTheme_Admin_Screen_ManageGigs );
				$instance->admin->screens->add( new AudioTheme_Admin_Screen_ManageVenues );
			}

			if ( $instance->modules->get( 'videos' )->is_active() || $instance->is_settings_screen() ) {
				$instance->admin->modules->add( 'videos', new AudioTheme_Admin_Videos );
			}
		}
	}

	return $instance;
}

/**
 * Initialize the plugin.
 */
$audiotheme = audiotheme();
add_action( 'plugins_loaded', array( $audiotheme, 'load_plugin' ) );
