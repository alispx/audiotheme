<?php
/**
 * Manage gigs administration screen functionality.
 *
 * @package AudioTheme\Gigs
 * @since 2.0.0
 */

/**
 * Manage gigs administration screen class.
 *
 * @package AudioTheme\Gigs
 * @since 2.0.0
 */
class AudioTheme_Admin_Screen_ManageGigs {
	/**
	 * Screen identifier.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	public $id = 'manage_gigs';

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
			audiotheme_encode_svg( 'admin/images/dashicons/gigs.svg' ),
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

		// Include the WP_List_Table dependency if it doesn't exist.
		if( ! class_exists( 'WP_List_Table' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}

		require_once( AUDIOTHEME_DIR . 'admin/class-audiotheme-gigs-list-table.php' );

		$list_table = new AudioTheme_Gigs_List_Table();
		$list_table->process_actions();
	}

	/**
	 * Display the screen.
	 *
	 * @since 1.0.0
	 */
	public function display_screen() {
		$post_type_object = get_post_type_object( 'audiotheme_gig' );

		$list_table = new AudioTheme_Gigs_List_Table();
		$list_table->prepare_items();

		require( AUDIOTHEME_DIR . 'admin/views/screen-manage-gigs.php' );
	}
}
