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
 * Load the Composer autoloader.
 */
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require( __DIR__ . '/vendor/autoload.php' );
}

use AudioTheme\Admin;
use AudioTheme\Admin\Screen;
use AudioTheme\Module;
use AudioTheme\Modules;
use Pimple\Container;

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
require( AUDIOTHEME_DIR . 'includes/default-hooks.php' );
require( AUDIOTHEME_DIR . 'includes/deprecated.php' );
require( AUDIOTHEME_DIR . 'includes/functions.php' );
require( AUDIOTHEME_DIR . 'includes/widgets.php' );
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
 * Retrieve the AudioTheme plugin instance.
 *
 * @since 2.0.0
 *
 * @return AudioTheme
 */
function audiotheme() {
	static $instance;

	if ( null === $instance ) {
		$instance = new AudioTheme\Plugin;
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
$audiotheme->archives     = new AudioTheme\Archives;
$audiotheme->templates    = new AudioTheme\Template\Loader;
$audiotheme->theme_compat = new AudioTheme\Theme\Compat;
$audiotheme->modules      = new Modules;

$audiotheme->modules['discography'] = function( $c ) {
	return new Module\Discography;
};

$audiotheme->modules['gigs'] = function( $c ) {
	return new Module\Gigs;
};

$audiotheme->modules['videos'] = function( $c ) {
	return new Module\Videos;
};

if ( is_admin() ) {
	$audiotheme->admin          = new Admin;
	$audiotheme->admin->modules = new Modules;
	$audiotheme->admin->screens = new Container;

	$audiotheme->admin->modules['discography'] = function( $c ) use ( $audiotheme ) {
		$audiotheme->admin->screens['manage_records'] = function( $c ) {
			return new Screen\ManageRecords;
		};

		$audiotheme->admin->screens['edit_record'] = function( $c ) {
			return new Screen\EditRecord;
		};

		$audiotheme->admin->screens['manage_tracks'] = function( $c ) {
			return new Screen\ManageTracks;
		};

		$audiotheme->admin->screens['edit_track'] = function( $c ) {
			return new Screen\EditTrack;
		};

		return new Admin\Discography;
	};

	$audiotheme->admin->modules['gigs'] = function( $c ) use ( $audiotheme ) {
		$audiotheme->admin->screens['manage_gigs'] = function( $c ) {
			return new Screen\ManageGigs;
		};

		$audiotheme->admin->screens['edit_gig'] = function( $c ) {
			return new Screen\EditGig;
		};

		$audiotheme->admin->screens['manage_venues'] = function( $c ) {
			return new Screen\ManageVenues;
		};

		$audiotheme->admin->screens['edit_venue'] = function( $c ) {
			return new Screen\EditVenue;
		};

		return new Admin\Gigs;
	};

	$audiotheme->admin->modules['videos'] = function( $c ) {
		return new Admin\Videos;
	};

	$audiotheme->admin->screens['settings'] = function( $c ) {
		return new Screen\Settings;
	};
}
