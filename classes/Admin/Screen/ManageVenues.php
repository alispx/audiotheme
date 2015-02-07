<?php
/**
 * Manage venues administration screen functionality.
 *
 * @package AudioTheme\Gigs
 * @since 2.0.0
 */

namespace AudioTheme\Admin\Screen;

/**
 * Manage venues administration screen class.
 *
 * @package AudioTheme\Gigs
 * @since 2.0.0
 */
class ManageVenues {
	/**
	 * List table class.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	public $list_table_class = 'AudioTheme\Admin\ListTable\Venues';

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
	 * Add the menu item to access the venue list.
	 *
	 * @since 2.0.0
	 */
	public function add_menu_item() {
		$post_type_object = get_post_type_object( 'audiotheme_venue' );

		$screen_hook = add_submenu_page(
			'audiotheme-gigs',
			$post_type_object->labels->name,
			$post_type_object->labels->menu_name,
			'edit_posts',
			'audiotheme-venues',
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
		$post_type_object = get_post_type_object( 'audiotheme_venue' );
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
		$post_type_object = get_post_type_object( 'audiotheme_venue' );

		$action      = 'add';
		$title       = $post_type_object->labels->name;
		$nonce_field = wp_nonce_field( 'add-venue', 'audiotheme_venue_nonce', true, false );
		$values      = get_default_audiotheme_venue_properties();

		if ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] && isset( $_GET['venue_id'] ) && is_numeric( $_GET['venue_id'] ) ) {
			$venue_to_edit = get_audiotheme_venue( $_GET['venue_id'] );

			$action      = 'edit';
			$nonce_field = wp_nonce_field( 'update-venue_' . $venue_to_edit->ID, 'audiotheme_venue_nonce', true, false );
			$values      = wp_parse_args( get_object_vars( $venue_to_edit ), $values );
		}

		extract( $values, EXTR_SKIP );

		require( AUDIOTHEME_DIR . 'admin/views/screen-manage-venues.php' );
	}
}
