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
 * Plugin version constant.
 */
define( 'AUDIOTHEME_VERSION', '2.0.0-alpha' );

/**
 * Plugin path and URL constants.
 */
if ( ! defined( 'AUDIOTHEME_DIR' ) ) {
	define( 'AUDIOTHEME_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'AUDIOTHEME_URI' ) ) {
	define( 'AUDIOTHEME_URI', plugin_dir_url( __FILE__ ) );
}

/**
 * Load the plugin or display a notice about requirements.
 */
if ( version_compare( phpversion(), '5.3', '>=' ) ) {
	require( AUDIOTHEME_DIR . '/plugin.php' );
} else {
	require( AUDIOTHEME_DIR . '/compatibility.php' );
}
