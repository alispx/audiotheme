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
 * Load functions and libraries.
 */
require( AUDIOTHEME_DIR . 'includes/class-audiotheme.php' );
require( AUDIOTHEME_DIR . 'includes/class-audiotheme-module.php' );
require( AUDIOTHEME_DIR . 'includes/class-audiotheme-modules.php' );
require( AUDIOTHEME_DIR . 'includes/class-audiotheme-theme-compat.php' );
require( AUDIOTHEME_DIR . 'includes/default-hooks.php' );
require( AUDIOTHEME_DIR . 'includes/functions.php' );
require( AUDIOTHEME_DIR . 'includes/general-template.php' );
require( AUDIOTHEME_DIR . 'includes/load-p2p.php' );
require( AUDIOTHEME_DIR . 'includes/widgets.php' );

/**
 * Load AudioTheme CPTs and corresponding functionality.
 */
require( AUDIOTHEME_DIR . 'modules/admin/class-audiotheme-module-admin.php' );
require( AUDIOTHEME_DIR . 'modules/archives/class-audiotheme-module-archives.php' );
require( AUDIOTHEME_DIR . 'modules/archives/post-template.php' );
require( AUDIOTHEME_DIR . 'modules/discography/class-audiotheme-module-discography.php' );
require( AUDIOTHEME_DIR . 'modules/discography/post-template.php' );
require( AUDIOTHEME_DIR . 'modules/gigs/class-audiotheme-module-gigs.php' );
require( AUDIOTHEME_DIR . 'modules/gigs/post-template.php' );
require( AUDIOTHEME_DIR . 'modules/videos/class-audiotheme-module-videos.php' );
require( AUDIOTHEME_DIR . 'modules/videos/post-template.php' );

if ( is_admin() ) {
	/**
	 * Load admin functions and libraries.
	 */
	require( AUDIOTHEME_DIR . 'modules/admin/ajax-actions.php' );
	require( AUDIOTHEME_DIR . 'modules/admin/functions.php' );
	require( AUDIOTHEME_DIR . 'modules/admin/class-audiotheme-module-admin-screen-settings.php' );

	/**
	 * Load AudioTheme modules admin functionality.
	 */
	require( AUDIOTHEME_DIR . 'modules/discography/admin/class-audiotheme-module-discography-admin.php' );
	require( AUDIOTHEME_DIR . 'modules/discography/admin/class-audiotheme-module-discography-admin-screen-editrecord.php' );
	require( AUDIOTHEME_DIR . 'modules/discography/admin/class-audiotheme-module-discography-admin-screen-edittrack.php' );
	require( AUDIOTHEME_DIR . 'modules/discography/admin/class-audiotheme-module-discography-admin-screen-managerecords.php' );
	require( AUDIOTHEME_DIR . 'modules/discography/admin/class-audiotheme-module-discography-admin-screen-managetracks.php' );
	require( AUDIOTHEME_DIR . 'modules/discography/admin/ajax-actions.php' );

	require( AUDIOTHEME_DIR . 'modules/gigs/admin/class-audiotheme-module-gigs-admin.php' );
	require( AUDIOTHEME_DIR . 'modules/gigs/admin/class-audiotheme-module-gigs-admin-screen-editgig.php' );
	require( AUDIOTHEME_DIR . 'modules/gigs/admin/class-audiotheme-module-gigs-admin-screen-editvenue.php' );
	require( AUDIOTHEME_DIR . 'modules/gigs/admin/class-audiotheme-module-gigs-admin-screen-managegigs.php' );
	require( AUDIOTHEME_DIR . 'modules/gigs/admin/class-audiotheme-module-gigs-admin-screen-managevenues.php' );
	require( AUDIOTHEME_DIR . 'modules/gigs/admin/ajax-actions.php' );

	require( AUDIOTHEME_DIR . 'modules/videos/admin/class-audiotheme-module-videos-admin.php' );
}

/**
 * Retrieve the AudioTheme plugin instance.
 *
 * @since 2.0.0
 *
 * @return AudioTheme
 */
function audiotheme() {
	static $instance;

	if ( null == $instance ) {
		$instance = new AudioTheme( array(
			'modules'      => new AudioTheme_Modules(),
			'plugin_file'  => __FILE__,
			'theme_compat' => new AudioTheme_Theme_Compat(),
		) );
	}

	return $instance;
}

/**
 * Initialize the plugin.
 */
$audiotheme = audiotheme();
add_action( 'plugins_loaded', array( $audiotheme, 'load_plugin' ) );

/**
 * Register core modules.
 *
 * @since 2.0.0
 */
$audiotheme->modules->register( new AudioTheme_Module_Admin );
$audiotheme->modules->register( new AudioTheme_Module_Archives );
$audiotheme->modules->register( new AudioTheme_Module_Discography );
$audiotheme->modules->register( new AudioTheme_Module_Gigs );
$audiotheme->modules->register( new AudioTheme_Module_Videos );
