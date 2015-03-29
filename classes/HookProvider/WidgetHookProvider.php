<?php
/**
 * Widget hooks.
 *
 * @package AudioTheme\Core
 * @since 2.0.0
 */

namespace AudioTheme\Core\HookProvider;

use AudioTheme\Core\HookProviderInterface;
use AudioTheme\Core\Plugin;

/**
 * Widget hooks class.
 *
 * @package AudioTheme\Core
 * @since   2.0.0
 */
class WidgetHookProvider implements HookProviderInterface {
	/**
	 * Plugin instance.
	 *
	 * @since 2.0.0
	 * @var \AudioTheme\Core\Plugin
	 */
	protected $plugin;

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 *
	 * @param \AudioTheme\Core\Plugin Main plugin instance.
	 */
	public function register( Plugin $plugin ) {
		$this->plugin = $plugin;

		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
	}

	/**
	 * Register supported widgets.
	 *
	 * Themes can load all widgets by calling
	 * add_theme_support( 'audiotheme-widgets' ).
	 *
	 * If support for all widgets isn't desired, a second parameter consisting
	 * of an array of widget keys can be passed to load the specified widgets:
	 * add_theme_support( 'audiotheme-widgets', array( 'upcoming-gigs' ) )
	 *
	 * @since 2.0.0
	 */
	public function register_widgets() {
		$widgets = array();
		$widgets['recent-posts'] = '\AudioTheme\Core\Widget\RecentPosts';

		if ( $this->plugin['modules']->is_active( 'discography' ) ) {
			$widgets['record'] = '\AudioTheme\Core\Widget\Record';
			$widgets['track']  = '\AudioTheme\Core\Widget\Track';
		}

		if ( $this->plugin['modules']->is_active( 'gigs' ) ) {
			$widgets['upcoming-gigs'] = '\AudioTheme\Core\Widget\UpcomingGigs';
		}

		if ( $this->plugin['modules']->is_active( 'videos' ) ) {
			$widgets['video']  = '\AudioTheme\Core\Widget\Video';
		}

		if ( $support = get_theme_support( 'audiotheme-widgets' ) ) {
			if ( is_array( $support ) ) {
				$widgets = array_intersect_key( $widgets, array_flip( $support[0] ) );
			}

			if ( ! empty( $widgets ) ) {
				foreach ( $widgets as $widget_id => $widget_class ) {
					register_widget( $widget_class );
				}
			}
		}
	}
}
