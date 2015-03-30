<?php
/**
 * Plugin service definitions.
 *
 * @package AudioTheme
 * @since 2.0.0
 */

namespace AudioTheme\Core;

use AudioTheme\Core\Admin\Screen;
use AudioTheme\Core\Ajax\DiscographyAjax;
use AudioTheme\Core\Ajax\GigsAjax;
use AudioTheme\Core\Ajax\VideosAjax;
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
			$modules = new ModuleCollection;

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

		$plugin['admin.screens'] = function( $plugin ) {
			$screens = new Container;

			$screens['dashboard'] = function() use ( $plugin ) {
				$screen = new Screen\Dashboard\Main;
				$screen->modules = $plugin['modules'];
				return $screen;
			};

			$screens['themes'] = function() {
				return new Screen\Dashboard\Themes;
			};

			$screens['settings'] = function() {
				return new Screen\Settings;
			};

			return $screens;
		};

		$plugin['admin.modules'] = function( $plugin ) {
			$modules = new ModuleCollection;
			$screens = $plugin['admin.screens'];

			$modules['discography'] = function() use ( $plugin, $screens ) {
				$plugin->register_hooks( new DiscographyAjax );

				$screens['manage_records'] = function() {
					return new Screen\ManageRecords;
				};

				$screens['edit_record'] = function() {
					return new Screen\EditRecord;
				};

				$screens['manage_tracks'] = function() {
					return new Screen\ManageTracks;
				};

				$screens['edit_track'] = function() {
					return new Screen\EditTrack;
				};

				return new Admin\Discography;
			};

			$modules['gigs'] = function() use ( $plugin, $screens ) {
				$plugin->register_hooks( new GigsAjax );

				$screens['manage_gigs'] = function() {
					return new Screen\ManageGigs;
				};

				$screens['edit_gig'] = function() {
					return new Screen\EditGig;
				};

				$screens['manage_venues'] = function() {
					return new Screen\ManageVenues;
				};

				$screens['edit_venue'] = function() {
					return new Screen\EditVenue;
				};

				return new Admin\Gigs;
			};

			$modules['videos'] = function() use( $plugin ) {
				$plugin->register_hooks( new VideosAjax );
				return new Admin\Videos;
			};

			return $modules;
		};
	}
}
