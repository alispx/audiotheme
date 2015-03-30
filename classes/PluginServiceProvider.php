<?php
/**
 * Plugin service definitions.
 *
 * @package AudioTheme
 * @since 2.0.0
 */

namespace AudioTheme\Core;

use AudioTheme\Core\Admin\Screen;
use AudioTheme\Core\Container;
use AudioTheme\Core\Module;
use AudioTheme\Core\ModuleCollection;
use AudioTheme\Core\Provider\Ajax\DiscographyAjax;
use AudioTheme\Core\Provider\Ajax\GigsAjax;
use AudioTheme\Core\Provider\Ajax\VideosAjax;
use AudioTheme\Core\Provider\PostType\GigPostType;
use AudioTheme\Core\Provider\PostType\PlaylistPostType;
use AudioTheme\Core\Provider\PostType\RecordPostType;
use AudioTheme\Core\Provider\PostType\TrackPostType;
use AudioTheme\Core\Provider\PostType\VenuePostType;
use AudioTheme\Core\Provider\PostType\VideoPostType;
use AudioTheme\Core\Provider\Taxonomy\GenreTaxonomy;
use AudioTheme\Core\Provider\Taxonomy\RecordTypeTaxonomy;
use AudioTheme\Core\Provider\Taxonomy\VideoCategoryTaxonomy;
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

				$plugin->register_hooks( new RecordPostType( $plugin, $module ) );
				$plugin->register_hooks( new TrackPostType( $plugin, $module ) );
				$plugin->register_hooks( new RecordTypeTaxonomy( $plugin, $module ) );
				$plugin->register_hooks( new GenreTaxonomy( $plugin, $module ) );
				$plugin->register_hooks( new PlaylistPostType( $plugin, $module ) );

				return $module;
			};

			$modules['gigs'] = function() use ( $plugin ) {
				$module = new Module\Gigs;
				$module->archives            = $plugin['archives'];
				$module->template_loader     = $plugin['template_loader'];
				$module->theme_compatibility = $plugin['theme_compatibility'];

				$plugin->register_hooks( new GigPostType( $plugin, $module ) );
				$plugin->register_hooks( new VenuePostType( $plugin, $module ) );

				return $module;
			};

			$modules['videos'] = function() use ( $plugin ) {
				$module = new Module\Videos;
				$module->archives            = $plugin['archives'];
				$module->template_loader     = $plugin['template_loader'];
				$module->theme_compatibility = $plugin['theme_compatibility'];

				$plugin->register_hooks( new VideoCategoryTaxonomy( $plugin, $module ) );
				$plugin->register_hooks( new VideoPostType( $plugin, $module ) );

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

		$plugin->extend( 'modules', function( $modules, $plugin ) {
			if ( ! is_admin() ) {
				return $modules;
			}

			$screens = $plugin['admin.screens'];

			$modules->extend( 'discography', function( $module ) use( $plugin, $screens ) {
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

				$screens['edit_record_archive'] = function() {
					return new Screen\EditRecordArchive;
				};

				return $module;
			} );

			$modules->extend( 'gigs', function( $module ) use( $plugin, $screens ) {
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

				return $module;
			} );

			$modules->extend( 'videos', function( $module ) use( $plugin, $screens ) {
				$plugin->register_hooks( new VideosAjax );

				$screens['manage_videos'] = function() {
					return new Screen\ManageVideos;
				};

				$screens['edit_video'] = function() {
					return new Screen\EditVideo;
				};

				$screens['edit_video_archive'] = function() {
					return new Screen\EditVideoArchive;
				};

				return $module;
			} );

			return $modules;
		} );
	}
}
