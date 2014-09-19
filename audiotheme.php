<?php
/**
 * Plugin Name: AudioTheme Framework
 * Plugin URI: http://audiotheme.com/view/audiotheme/
 * Description: A platform for music-oriented websites, allowing for easy management of gigs, discography, videos and more.
 * Version: 1.6.2
 * Author: AudioTheme
 * Author URI: http://audiotheme.com/
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
 * @link http://audiotheme.com/
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
if ( ! defined( 'AUDIOTHEME_DIR' ) )
	define( 'AUDIOTHEME_DIR', plugin_dir_path( __FILE__ ) );

if ( ! defined( 'AUDIOTHEME_URI' ) )
	define( 'AUDIOTHEME_URI', plugin_dir_url( __FILE__ ) );

/**
 * Load functions and libraries.
 */
require( AUDIOTHEME_DIR . 'includes/class-audiotheme.php' );
require( AUDIOTHEME_DIR . 'includes/archives.php' );
require( AUDIOTHEME_DIR . 'includes/default-filters.php' );
require( AUDIOTHEME_DIR . 'includes/functions.php' );
require( AUDIOTHEME_DIR . 'includes/general-template.php' );
require( AUDIOTHEME_DIR . 'includes/load-p2p.php' );
require( AUDIOTHEME_DIR . 'includes/media.php' );
require( AUDIOTHEME_DIR . 'includes/modules.php' );
require( AUDIOTHEME_DIR . 'includes/widgets.php' );

/**
 * Load AudioTheme CPTs and corresponding functionality.
 */
require( AUDIOTHEME_DIR . 'modules/discography/discography.php' );
require( AUDIOTHEME_DIR . 'modules/gigs/gigs.php' );
require( AUDIOTHEME_DIR . 'modules/videos/videos.php' );

if ( is_admin() ) {
	/**
	 * Load admin functions and libraries.
	 */
	require( AUDIOTHEME_DIR . 'admin/includes/ajax.php' );
	require( AUDIOTHEME_DIR . 'admin/includes/archives.php' );
	require( AUDIOTHEME_DIR . 'admin/includes/functions.php' );
	require( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-admin.php' );
	require( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-admin-screen-settings.php' );
}

/**
 * Initialize the plugin.
 */
$audiotheme = new Audiotheme();
add_action( 'plugins_loaded', array( $audiotheme, 'load_plugin' ) );
