<?php
/**
 * Asset hooks.
 *
 * @package AudioTheme\Core
 * @since 2.0.0
 */

namespace AudioTheme\Core\Provider;

use AudioTheme\Core\HookProviderInterface;
use AudioTheme\Core\Plugin;

/**
 * Asset hooks class.
 *
 * @package AudioTheme\Core
 * @since   2.0.0
 */
class Assets implements HookProviderInterface {
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
	public function register_hooks( Plugin $plugin ) {
		$this->plugin = $plugin;

		add_action( 'wp_enqueue_scripts',    array( $this, 'register_assets' ), 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ), 1 );

		// Enqueue after theme styles.
		add_action( 'wp_enqueue_scripts',    array( $this, 'enqueue_assets' ), 11 );
	}

	/**
	 * Register frontend scripts and styles for enqueuing on-demand.
	 *
	 * @since 1.0.0
	 *
	 * @link http://core.trac.wordpress.org/ticket/18909
	 */
	public function register_assets() {
		global $wp_locale;

		$base_url = set_url_scheme( $this->plugin->get_url() );
		$suffix   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'audiotheme',               $base_url . 'includes/js/audiotheme.js',                   array( 'jquery', 'jquery-cue', 'audiotheme-media-classes' ), AUDIOTHEME_VERSION, true );
		wp_register_script( 'audiotheme-media-classes', $base_url . 'includes/js/audiotheme-media-classes.js',     array( 'jquery' ), AUDIOTHEME_VERSION, true );
		wp_register_script( 'jquery-cue',               $base_url . 'includes/js/vendor/jquery.cue.min.js',        array( 'jquery', 'mediaelement' ), '1.1.3', true );
		wp_register_script( 'jquery-timepicker',        $base_url . 'includes/js/vendor/jquery.timepicker.min.js', array( 'jquery' ), '1.1', true );
		wp_register_script( 'moment',                   $base_url . 'includes/js/vendor/moment.js',                array(), '2.9.0', true );
		wp_register_script( 'pikaday',                  $base_url . 'includes/js/vendor/pikaday.js',               array( 'moment'), '1.3.2', true );

		wp_localize_script( 'pikaday', '_pikadayL10n', array(
			'previousMonth' => __( 'Previous Month', 'audiotheme' ),
			'nextMonth'     => __( 'Next Month', 'audiotheme' ),
			'months'        => array_values( $wp_locale->month ),
			'weekdays'      => $wp_locale->weekday,
			'weekdaysShort' => array_values( $wp_locale->weekday_abbrev ),
		) );

		wp_register_style( 'audiotheme', $base_url . 'includes/css/audiotheme.min.css' );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_assets() {
		if ( ! apply_filters( 'audiotheme_enqueue_theme_assets', true ) ) {
			return;
		}

		wp_enqueue_script( 'audiotheme' );
		wp_enqueue_style( 'audiotheme' );
	}
}
