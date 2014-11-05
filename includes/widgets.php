<?php
/**
 * Load registered widgets.
 *
 * @package AudioTheme\Widgets
 * @since 1.0.0
 */

/**
 * Register Supported Widgets
 *
 * Themes can load all widgets by calling add_theme_support( 'audiotheme-widgets' ).
 *
 * If support for all widgets isn't desired, a second parameter consisting of an array
 * of widget keys can be passed to load the specified widgets:
 * add_theme_support( 'audiotheme-widgets', array( 'upcoming-gigs' ) )
 *
 * @since 1.0.0
 */
function audiotheme_widgets_init() {
	$widgets = array(
		'recent-posts'  => 'AudioTheme_Widget_Recent_Posts',
		'record'        => 'AudioTheme_Widget_Record',
		'track'         => 'AudioTheme_Widget_Track',
		'upcoming-gigs' => 'AudioTheme_Widget_Upcoming_Gigs',
		'video'         => 'AudioTheme_Widget_Video',
	);

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
