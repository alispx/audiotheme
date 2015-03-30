<?php
/**
 * Plugin service definitions.
 *
 * @package AudioTheme
 * @since 2.0.0
 */

namespace AudioTheme\Core;

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
use AudioTheme\Core\Provider\Screen;
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

		// Register module admin hooks and screens.
		$plugin->extend( 'modules', function( $modules, $plugin ) {
			if ( ! is_admin() ) {
				return $modules;
			}

			$modules->extend( 'discography', function( $module ) use( $plugin ) {
				$plugin->register_hooks( new DiscographyAjax );

				$plugin->register_screen( new Screen\ManageRecords );
				$plugin->register_screen( new Screen\EditRecord );
				$plugin->register_screen( new Screen\ManageTracks );
				$plugin->register_screen( new Screen\EditTrack );
				$plugin->register_screen( new Screen\EditRecordArchive );

				return $module;
			} );

			$modules->extend( 'gigs', function( $module ) use( $plugin ) {
				$plugin->register_hooks( new GigsAjax );

				$plugin->register_screen( new Screen\ManageGigs );
				$plugin->register_screen( new Screen\EditGig );
				$plugin->register_screen( new Screen\ManageVenues );
				$plugin->register_screen( new Screen\EditVenue );

				return $module;
			} );

			$modules->extend( 'videos', function( $module ) use( $plugin ) {
				$plugin->register_hooks( new VideosAjax );

				$plugin->register_screen( new Screen\ManageVideos );
				$plugin->register_screen( new Screen\EditVideo );
				$plugin->register_screen( new Screen\EditVideoArchive );

				return $module;
			} );

			return $modules;
		} );
	}
}
