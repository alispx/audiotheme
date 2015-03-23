<?php

namespace AudioTheme\Core;

use AudioTheme\Core\Util;

/**
 * Administration class.
 *
 * @package AudioTheme\Core\Administration
 * @since 2.0.0
 */
class Admin {
	/**
	 * Admin modules container.
	 *
	 * @since 2.0.0
	 * @type AudioTheme_Container
	 */
	public $modules;

	/**
	 * Screen container.
	 *
	 * @since 2.0.0
	 * @type AudioTheme_Container
	 */
	public $screens;

	/**
	 * Load administration functionality.
	 *
	 * @since 2.0.0
	 */
	public function load() {
		$this->register_hooks();
		$this->register_ajax_actions();
		$this->load_modules();
		$this->load_screens();
	}

	/**
	 * Load module admin classes.
	 *
	 * @since 2.0.0
	 */
	public function load_modules() {
		// Load all modules on the settings screen.
		if ( $this->is_dashboard_screen() ) {
			$modules = $this->modules->keys();
		} else {
			$modules = $this->modules->get_active_keys();
		}

		foreach ( $modules as $module_id ) {
			$this->modules[ $module_id ]->load();
		}
	}

	/**
	 * Load admin screens.
	 *
	 * @since 2.0.0
	 */
	public function load_screens() {
		foreach ( $this->screens->keys() as $screen_id ) {
			$this->screens[ $screen_id ]->load();
		}
	}

	/**
	 * Register module hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'admin_init',                 array( $this, 'sort_admin_menu' ) );
		add_action( 'admin_enqueue_scripts',      array( $this, 'register_assets' ), 1 );
		add_action( 'admin_enqueue_scripts',      array( $this, 'enqueue_assets' ) );
		add_action( 'admin_body_class',           array( $this, 'admin_body_classes' ) );
		add_filter( 'user_contactmethods',        array( $this, 'register_user_contact_methods' ) );
		add_action( 'manage_pages_custom_column', array( $this, 'display_custom_columns' ), 10, 2 );
		add_action( 'manage_posts_custom_column', array( $this, 'display_custom_columns' ), 10, 2 );
		add_action( 'save_post',                  array( $this, 'update_post_terms' ), 10, 2 );
		add_action( 'admin_init',                 array( $this, 'upgrade' ) );
	}

	/**
	 * Register AJAX callbacks.
	 *
	 * @since 2.0.0
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_audiotheme_ajax_toggle_module', 'audiotheme_ajax_toggle_module' );
		add_action( 'wp_ajax_audiotheme_ajax_insert_term',   'audiotheme_ajax_insert_term' );
	}

	/**
	 * Sort the admin menu.
	 *
	 * @since 1.0.0
	 */
	public function sort_admin_menu() {
		global $menu;

		if ( ! is_network_admin() && $menu ) {
			$menu = array_values( $menu ); // Re-key the array.

			$separator = array( '', 'read', 'separator-before-audiotheme', '', 'wp-menu-separator' );
			audiotheme_menu_insert_item( $separator, 'audiotheme', 'before' );

			// Reverse the order and always insert after the main AudioTheme menu item.
			audiotheme_menu_move_item( 'edit.php?post_type=audiotheme_video', 'audiotheme' );
			audiotheme_menu_move_item( 'edit.php?post_type=audiotheme_record', 'audiotheme' );
			audiotheme_menu_move_item( 'audiotheme-gigs', 'audiotheme' );

			audiotheme_submenu_move_after( 'audiotheme-settings', 'audiotheme', 'audiotheme' );
			audiotheme_submenu_move_after( 'audiotheme-themes', 'audiotheme', 'audiotheme' );
		}
	}

	/**
	 * Register admin assets.
	 *
	 * @since 2.0.0
	 */
	public function register_assets() {
		$base_url = set_url_scheme( AUDIOTHEME_URI . 'admin/' );
		$suffix   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'audiotheme-admin',     $base_url . 'js/admin' . $suffix . '.js',       array( 'jquery-ui-sortable', 'wp-util' ), '2.0.0', true );
		wp_register_script( 'audiotheme-dashboard', $base_url . 'js/dashboard.js',                  array( 'jquery', 'wp-backbone', 'wp-util' ), '2.0.0', true );
		wp_register_script( 'audiotheme-media',     $base_url . 'js/media' . $suffix . '.js',       array( 'jquery' ), '2.0.0', true );

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

	/**
	 * Add current screen ID as CSS class to the body element.
	 *
	 * @since 2.0.0
	 *
	 * @param string $class Body class.
	 * @return string
	 */
	public function admin_body_classes( $classes ) {
		global $post;

		$classes .= ' screen-' . sanitize_html_class( get_current_screen()->id );

		if ( 'audiotheme_archive' == get_current_screen()->id && $post_type = is_audiotheme_post_type_archive_id( $post->ID )) {
			$classes .= ' ' . $post_type . '-archive';
		}

		return implode( ' ', array_unique( explode( ' ', $classes ) ) );
	}

	/**
	 * General custom post type columns.
	 *
	 * @since 1.0.0
	 *
	 * @param string $column_name Column identifier.
	 * @param int $post_id Post ID.
	 */
	public function display_custom_columns( $column_name, $post_id ) {
		switch ( $column_name ) {
			case 'audiotheme_image' :
				printf( '<a href="%1$s">%2$s</a>',
					esc_url( get_edit_post_link( $post_id ) ),
					get_the_post_thumbnail( $post_id, array( 60, 60 ) )
				);
				break;
		}
	}

	/**
	 * Save custom taxonomy terms when a post is saved.
	 *
	 * @since 2.0.0
	 *
	 * @param int $post_id Post ID.
	 * @param WP_Post $post Post object.
	 */
	public function update_post_terms( $post_id, $post ) {
		$is_autosave  = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
		$is_revision  = wp_is_post_revision( $post_id );

		// Bail if the data shouldn't be saved.
		if ( $is_autosave || $is_revision || empty( $_POST['audiotheme_post_terms'] ) ) {
			return;
		}

		foreach ( $_POST['audiotheme_post_terms'] as $taxonomy => $term_ids ) {
			// Don't save if intention can't be verified.
			if ( ! isset( $_POST[ $taxonomy . '_nonce' ] ) || ! wp_verify_nonce( $_POST[ $taxonomy . '_nonce' ], 'save-post-terms_' . $post_id ) ) {
				continue;
			}

			$term_ids = array_map( 'absint', $term_ids );
			wp_set_object_terms( $post_id, $term_ids, $taxonomy );
		}
	}

	/**
	 * Custom user contact fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $contactmethods List of contact methods.
	 * @return array
	 */
	public function register_user_contact_methods( $contact_methods ) {
		$contact_methods['twitter']  = __( 'Twitter Username', 'audiotheme' );
		$contact_methods['facebook'] = __( 'Facebook URL', 'audiotheme' );
		return $contact_methods;
	}

	/**
	 * Whether the current request is the dashboard screen.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_dashboard_screen() {
		return is_admin() && isset( $_GET['page'] ) && 'audiotheme' == $_GET['page'];
	}

	/**
	 * Upgrade routine.
	 *
	 * @since 2.0.0
	 */
	public function upgrade() {
		$saved_version = get_option( 'audiotheme_version', '0' );
		$current_version = AUDIOTHEME_VERSION;

		if ( version_compare( $saved_version, '2.0.0-alpha', '<' ) ) {
			$this->upgrade200();
		}

		if ( '0' == $saved_version || version_compare( $saved_version, $current_version, '<' ) ) {
			update_option( 'audiotheme_version', AUDIOTHEME_VERSION );
		}
	}

	protected function upgrade200() {
		delete_option( 'audiotheme_disable_directory_browsing' );

		// Add the archive post type to its metadata and delete the inactive option.
		if ( $archives = get_option( 'audiotheme_archives_inactive' ) ) {
			foreach ( $archives as $post_type => $post_id ) {
				update_post_meta( $post_id, 'post_type', $post_type );
			}
			delete_option( 'audiotheme_archives_inactive' );
		}

		// Add the archive post type to its metadata.
		if ( $archives = get_option( 'audiotheme_archives' ) ) {
			foreach ( $archives as $post_type => $post_id ) {
				update_post_meta( $post_id, 'post_type', $post_type );
			}
		}

		// @todo Convert genres.

		// Update record types.
		$terms = get_terms( 'audiotheme_record_type', array( 'get' => 'all' ) );
		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$name = get_audiotheme_record_type_string( $term->slug );
				$name = empty( $name ) ? ucwords( str_replace( array( 'record-type-', '-' ), array( '', ' ' ), $term->name ) ) : $name;
				$slug = str_replace( 'record-type-', '', $term->slug );

				$result = wp_update_term( $term->term_id, 'audiotheme_record_type', array(
					'name' => $name,
					'slug' => $slug,
				) );

				if ( is_wp_error( $result ) ) {
					// Update the name only. We'll account for the 'record-type-' prefix.
					wp_update_term( $term->term_id, 'audiotheme_record_type', array(
						'name' => $name,
					) );
				}
			}
		}

		flush_rewrite_rules();
	}
}
