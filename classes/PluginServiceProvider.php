<?php
/**
 * Plugin service definitions.
 *
 * @package AudioTheme
 * @since 2.0.0
 */

namespace AudioTheme\Core;

use AudioTheme\Core\Admin;
use AudioTheme\Core\Admin\Screen;
use AudioTheme\Core\Container;
use AudioTheme\Core\Module;
use AudioTheme\Core\ModuleCollection;
use AudioTheme\Core\ServiceProviderInterface;
use AudioTheme\Core\Theme;

/**
 * Plugin service provider class.
 *
 * @package AudioTheme
 * @since 2.0.0
 */
class PluginServiceProvider implements ServiceProviderInterface {
	/**
	 * Register plugin services.
	 *
	 * @since 2.0.0
	 *
	 * @param \Pimple\Container $plugin Container instance.
	 */
	public function register( Container $plugin ) {
		$plugin['archives'] = function() {
			return new Archives;
		};

		$plugin['template_loader'] = function() {
			return new Template\Loader;
		};

		$plugin['theme_compatibility'] = function() {
			return new Theme\Compatibility;
		};

		$plugin['modules'] = function() use ( $plugin ) {
			$modules = new ModuleCollection();

			$modules['discography'] = function() use ( $plugin ) {
				$module = new Module\Discography;
				$module->archives            = $plugin['archives'];
				$module->template_loader     = $plugin['template_loader'];
				$module->theme_compatibility = $plugin['theme_compatibility'];
				return $module;
			};

			$modules['gigs'] = function() use ( $plugin ) {
				$module = new Module\Gigs;
				$module->archives            = $plugin['archives'];
				$module->template_loader     = $plugin['template_loader'];
				$module->theme_compatibility = $plugin['theme_compatibility'];
				return $module;
			};

			$modules['videos'] = function() use ( $plugin ) {
				$module = new Module\Videos;
				$module->archives            = $plugin['archives'];
				$module->template_loader     = $plugin['template_loader'];
				$module->theme_compatibility = $plugin['theme_compatibility'];
				return $module;
			};

			return $modules;
		};

		$plugin['admin'] = function( $plugin ) {
			$admin = new Admin;

			$admin->modules = new ModuleCollection;
			$admin->screens = new Container;

			$admin->screens['dashboard'] = function() use ( $plugin ) {
				$screen = new Screen\Dashboard\Main;
				$screen->modules = $plugin['modules'];
				return $screen;
			};

			$admin->screens['themes'] = function() use ( $plugin ) {
				return new Screen\Dashboard\Themes;
			};

			$admin->screens['settings'] = function() use ( $plugin ) {
				return new Screen\Settings;
			};

			$admin->modules['discography'] = function() use ( $admin ) {
				$admin->screens['manage_records'] = function() {
					return new Screen\ManageRecords;
				};

				$admin->screens['edit_record'] = function() {
					return new Screen\EditRecord;
				};

				$admin->screens['manage_tracks'] = function() {
					return new Screen\ManageTracks;
				};

				$admin->screens['edit_track'] = function() {
					return new Screen\EditTrack;
				};

				return new Admin\Discography;
			};

			$admin->modules['gigs'] = function() use ( $admin ) {
				$admin->screens['manage_gigs'] = function() {
					return new Screen\ManageGigs;
				};

				$admin->screens['edit_gig'] = function() {
					return new Screen\EditGig;
				};

				$admin->screens['manage_venues'] = function() {
					return new Screen\ManageVenues;
				};

				$admin->screens['edit_venue'] = function() {
					return new Screen\EditVenue;
				};

				return new Admin\Gigs;
			};

			$admin->modules['videos'] = function() {
				return new Admin\Videos;
			};

			return $admin;
		};
	}
}
