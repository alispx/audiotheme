<?php
/**
 * Manage gigs administration screen functionality.
 *
 * @package AudioTheme\Core\Gigs
 * @since 2.0.0
 */

namespace AudioTheme\Core\Admin\Screen;

use AudioTheme\Core\Util;

/**
 * Manage gigs administration screen class.
 *
 * @package AudioTheme\Core\Gigs
 * @since 2.0.0
 */
class ManageGigs {
	/**
	 * List table class.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	public $list_table_class = 'AudioTheme\Core\Admin\ListTable\Gigs';

	/**
	 * List table instance.
	 *
	 * @since 2.0.0
	 * @type WP_List_Table
	 */
	protected $list_table;

	/**
	 * Load the screen.
	 *
	 * @since 2.0.0
	 */
	public function load() {
		$this->register_hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
	}

	/**
	 * Add the menu item to access the gig list.
	 *
	 * @since 2.0.0
	 */
	public function add_menu_item() {
		global $pagenow, $typenow;

		// Redirect the default Manage Gigs screen.
		if ( 'audiotheme_gig' == $typenow && 'edit.php' == $pagenow ) {
			wp_redirect( get_audiotheme_gig_admin_url() );
			exit;
		}

		$post_type_object = get_post_type_object( 'audiotheme_gig' );

		// Remove the default gigs menu item and replace it with the screen using the custom post list table.
		remove_submenu_page( 'audiotheme-gigs', 'edit.php?post_type=audiotheme_gig' );

		$screen_hook = add_menu_page(
			$post_type_object->labels->name,
			$post_type_object->labels->menu_name,
			'edit_posts',
			'audiotheme-gigs',
			array( $this, 'display_screen' ),
			Util::encode_svg( 'admin/images/dashicons/gigs.svg' ),
			512
		);

		add_submenu_page(
			'audiotheme-gigs',
			$post_type_object->labels->name,
			$post_type_object->labels->all_items,
			'edit_posts',
			'audiotheme-gigs',
			array( $this, 'display_screen' )
		);

		add_action( 'load-' . $screen_hook, array( $this, 'load_screen' ) );
	}

	/**
	 * Set up the screen.
	 *
	 * @since 1.0.0
	 */
	public function load_screen() {
		$post_type_object = get_post_type_object( 'audiotheme_gig' );
		$title = $post_type_object->labels->name;
		add_screen_option( 'per_page', array( 'label' => $title, 'default' => 20 ) );

		$this->list_table = new $this->list_table_class;
		$this->list_table->process_actions();
	}

	/**
	 * Display the screen.
	 *
	 * @since 1.0.0
	 */
	public function display_screen() {
		$this->list_table->prepare_items();

		$list_table       = $this->list_table;
		$post_type_object = get_post_type_object( 'audiotheme_gig' );
		require( AUDIOTHEME_DIR . 'admin/views/screen-manage-gigs.php' );
	}
}
