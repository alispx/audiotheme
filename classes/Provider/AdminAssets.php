<?php
/**
 * Admin asset hooks.
 *
 * @package AudioTheme\Core
 * @since 2.0.0
 */

namespace AudioTheme\Core\Provider;

use AudioTheme\Core\HookProviderInterface;
use AudioTheme\Core\Plugin;

/**
 * Admin asset hooks class.
 *
 * @package AudioTheme\Core
 * @since   2.0.0
 */
class AdminAssets implements HookProviderInterface {
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

		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ), 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register admin assets.
	 *
	 * @since 2.0.0
	 */
	public function register_assets() {
		$base_url = set_url_scheme( AUDIOTHEME_URI . 'admin/' );
		$suffix   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'audiotheme-admin',     $base_url . 'js/admin' . $suffix . '.js', array( 'jquery-ui-sortable', 'wp-util' ), '2.0.0', true );
		wp_register_script( 'audiotheme-dashboard', $base_url . 'js/dashboard.js',            array( 'jquery', 'wp-backbone', 'wp-util' ), '2.0.0', true );
		wp_register_script( 'audiotheme-media',     $base_url . 'js/media' . $suffix . '.js', array( 'jquery' ), '2.0.0', true );

		wp_localize_script( 'audiotheme-dashboard', '_audiothemeDashboardSettings', array(
			'canActivateModules' => current_user_can( 'activate_plugins' ),
			'l10n'               => array(
				'activate'   => __( 'Activate', 'audiotheme' ),
				'deactivate' => __( 'Deactivate', 'audiotheme' ),
			),
		) );

		wp_localize_script( 'audiotheme-media', 'AudiothemeMediaControl', array(
			'audioFiles'      => __( 'Audio files', 'audiotheme' ),
			'frameTitle'      => __( 'Choose an Attachment', 'audiotheme' ),
			'frameUpdateText' => __( 'Update Attachment', 'audiotheme' ),
		) );

		wp_register_style( 'audiotheme-admin',           $base_url . 'css/admin.min.css' );
		wp_register_style( 'audiotheme-dashboard',       $base_url . 'css/dashboard.min.css' );
		wp_register_style( 'audiotheme-venue-manager',   $base_url . 'css/venue-manager.min.css', array(), '2.0.0' );
		wp_register_style( 'jquery-ui-theme-smoothness', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css' );
		wp_register_style( 'jquery-ui-theme-audiotheme', $base_url . 'css/jquery-ui-audiotheme.min.css', array( 'jquery-ui-theme-smoothness' ) );
	}

	/**
	 * Enqueue global admin assets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_assets() {
		wp_enqueue_script( 'audiotheme-admin' );
		wp_enqueue_style( 'audiotheme-admin' );
	}
}
