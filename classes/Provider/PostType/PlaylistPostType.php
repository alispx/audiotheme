<?php
/**
 * Playlist post type registration and integration.
 *
 * Extends the playlist post type in the Cue plugin.
 *
 * @package AudioTheme\Core
 * @since 2.0.0
 */

namespace AudioTheme\Core\Provider\PostType;

use AudioTheme\Core\HookProviderInterface;
use AudioTheme\Core\Plugin;
use AudioTheme\Core\Util;

/**
 * Class for registering the playlist post type and integration.
 *
 * @package AudioTheme\Core
 * @since   2.0.0
 */
class PlaylistPostType implements HookProviderInterface {
	/**
	 * Module.
	 *
	 * @since 2.0.0
	 * @var \AudioTheme\Core\Module
	 */
	protected $module;

	/**
	 * Plugin instance.
	 *
	 * @since 2.0.0
	 * @var \AudioTheme\Core\Plugin
	 */
	protected $plugin;

	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 *
	 * @param \AudioTheme\Core\Plugin Main plugin instance.
	 * @param \AudioTheme\Core\Module Module instance.
	 */
	public function __construct( Plugin $plugin, $module ) {
		$this->plugin = $plugin;
		$this->module = $module;
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 *
	 * @param \AudioTheme\Core\Plugin Main plugin instance.
	 */
	public function register( Plugin $plugin ) {
		add_filter( 'cue_playlist_args',     array( $this, 'playlist_post_type_args' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_playlist_assets' ) );
		add_action( 'print_media_templates', array( $this, 'print_playlist_templates' ) );
	}

	/**
	 * Move the playlist menu item under discography.
	 *
	 * @since 1.5.0
	 *
	 * @param array $args Post type registration args.
	 * @return array
	 */
	public function playlist_post_type_args( $args ) {
		$args['show_in_menu'] = 'edit.php?post_type=audiotheme_record';
		return $args;
	}

	/**
	 * Enqueue playlist scripts and styles.
	 *
	 * @since 1.5.0
	 */
	public function enqueue_playlist_assets() {
		if ( 'cue_playlist' !== get_post_type() ) {
			return;
		}

		wp_enqueue_style( 'audiotheme-playlist-admin', AUDIOTHEME_URI . 'admin/css/playlist.css' );

		wp_enqueue_script(
			'audiotheme-playlist-admin',
			AUDIOTHEME_URI . 'admin/js/playlist.js',
			array( 'cue-admin' ),
			'1.0.0',
			true
		);

		wp_localize_script( 'audiotheme-playlist-admin', '_audiothemePlaylistSettings', array(
			'l10n' => array(
				'frameTitle'        => __( 'AudioTheme Tracks', 'audiotheme' ),
				'frameMenuItemText' => __( 'Add from AudioTheme', 'audiotheme' ),
				'frameButtonText'   => __( 'Add Tracks', 'audiotheme' ),
			),
		) );
	}

	/**
	 * Print playlist JavaScript templates.
	 *
	 * @since 1.5.0
	 */
	public function print_playlist_templates() {
		include( AUDIOTHEME_DIR . 'admin/views/templates-playlist.php' );
	}
}
