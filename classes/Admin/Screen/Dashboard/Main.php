<?php
/**
 * Main Dashboard screen.
 *
 * @package AudioTheme\Core\Administration
 * @since 2.0.0
 */

namespace AudioTheme\Core\Admin\Screen\Dashboard;

use AudioTheme\Core\Admin\Screen\Dashboard;
use AudioTheme\Core\Util;

/**
 * Class to render the main Dashboard screen.
 *
 * @package AudioTheme
 * @since 2.0.0
 */
class Main extends Dashboard {
	/**
	 * Plugin modules.
	 *
	 * @since 2.0.0
	 * @type AudioTheme\Core\ModuleCollection
	 */
	public $modules;

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'admin_menu',                      array( $this, 'add_menu_item' ) );
		add_action( 'audiotheme_module_card_overview', array( $this, 'render_core_module_overview' ) );
	}

	/**
	 * Add menu items.
	 *
	 * @since 2.0.0
	 */
	public function add_menu_item() {
		add_menu_page(
			__( 'AudioTheme', 'audiotheme' ),
			__( 'AudioTheme', 'audiotheme' ),
			'edit_posts',
			'audiotheme',
			array( $this, 'render_screen' ),
			Util::encode_svg( 'admin/images/dashicons/audiotheme.svg' ),
			3.901
		);

		$page_hook = add_submenu_page(
			'audiotheme',
			__( 'Getting Started', 'audiotheme' ),
			__( 'Getting Started', 'audiotheme' ),
			'edit_posts',
			'audiotheme',
			array( $this, 'render_screen' )
		);

		add_action( 'load-' . $page_hook, array( $this, 'load_screen' ) );
	}

	/**
	 * Set up the main Dashboard screen.
	 *
	 * @since 2.0.0
	 */
	public function load_screen() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue assets for the Dashboard screen.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_assets() {
		wp_enqueue_script( 'audiotheme-dashboard' );
		wp_enqueue_style( 'audiotheme-dashboard' );

		// Hide menu items for inactive modules on initial load.
		$styles = '';
		foreach ( $this->modules->get_inactive_keys() as $module_id ) {
			$styles .= sprintf( '#%s { display: none;}', $this->modules[ $module_id ]->admin_menu_id );
		}

		wp_add_inline_style( 'audiotheme-dashboard', $styles );
	}

	/**
	 * Display the Dashboard screen.
	 *
	 * @since 2.0.0
	 */
	public function render_screen() {
		$modules = $this->modules;

		$this->render_screen_header();
		include( AUDIOTHEME_DIR . 'admin/views/screen-dashboard.php' );
		$this->render_screen_footer();
		include( AUDIOTHEME_DIR . 'admin/views/templates-dashboard.php' );
	}

	/**
	 * Output overview text for the core modules.
	 *
	 * @since 2.0.0
	 *
	 * @param string $module_id Module identifier.
	 */
	public function render_core_module_overview( $module_id ) {
		if ( 'discography' == $module_id ) {
			?>
			<p>
				Everything you need to build your Discography is at your fingertips.
			</p>
			<p>
				Your discography is the window through which listeners are introduced to and discover your music on the web. Encourage that discovery on your website through a detailed and organized history of your recorded output using the AudioTheme discography feature. Upload album artwork, assign titles and tracks, add audio files, and enter links to purchase your music.
			</p>

			<h3>Documentation</h3>
			<ul>
				<li><a href="">All Records</a></li>
				<li><a href="">Add New Record</a></li>
				<li><a href="">All Tracks</a></li>
				<li><a href="">All Playlists</a></li>
				<li><a href="">Archive</a></li>
			</ul>
			<?php
		} elseif ( 'gigs' == $module_id ) {
			?>
			<p>
				Gigs overview
			</p>
			<?php
		} elseif ( 'videos' == $module_id ) {
			?>
			<p>
				Videos overview
			</p>
			<?php
		}
	}
}
