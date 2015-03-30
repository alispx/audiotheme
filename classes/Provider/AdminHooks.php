<?php
/**
 * Admin hooks.
 *
 * @package AudioTheme\Core
 * @since 2.0.0
 */

namespace AudioTheme\Core\Provider;

use AudioTheme\Core\HookProviderInterface;
use AudioTheme\Core\Plugin;
use AudioTheme\Core\Util;

/**
 * Admin hooks class.
 *
 * @package AudioTheme\Core
 * @since   2.0.0
 */
class AdminHooks implements HookProviderInterface {
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

		add_action( 'admin_init',                 array( $this, 'sort_admin_menu' ) );
		add_action( 'admin_body_class',           array( $this, 'admin_body_classes' ) );
		add_filter( 'user_contactmethods',        array( $this, 'register_user_contact_methods' ) );
		add_action( 'manage_pages_custom_column', array( $this, 'display_custom_columns' ), 10, 2 );
		add_action( 'manage_posts_custom_column', array( $this, 'display_custom_columns' ), 10, 2 );
		add_action( 'save_post',                  array( $this, 'update_post_terms' ), 10, 2 );
		add_action( 'admin_init',                 array( $this, 'upgrade' ) );
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
