<?php
/**
 * Administration functionality for the Gigs module.
 *
 * @package AudioTheme\Core\Gigs
 * @since 2.0.0
 */

namespace AudioTheme\Core\Admin;

/**
 * Gigs module administration class.
 *
 * @package AudioTheme\Core\Gigs
 * @since 2.0.0
 */
class Gigs {
	/**
	 * Attach hooks for loading gigs administration.
	 *
	 * @since 1.0.0
	 */
	public function load() {
		$this->register_hooks();
		$this->register_ajax_actions();
	}

	/**
	 * Register admin hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'parent_file',           array( $this, 'admin_menu_highlight' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ), 1 );
		add_filter( 'set-screen-option',     array( $this, 'screen_options' ), 999, 3 );
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
	}

	/**
	 * Register AJAX callbacks.
	 *
	 * @since 2.0.0
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_audiotheme_ajax_get_venue',  'audiotheme_ajax_get_venue' );
		add_action( 'wp_ajax_audiotheme_ajax_get_venues', 'audiotheme_ajax_get_venues' );
		add_action( 'wp_ajax_audiotheme_ajax_save_venue', 'audiotheme_ajax_save_venue' );
	}

	/**
	 * Register scripts and styles.
	 *
	 * @since 2.0.0
	 */
	public function register_assets() {
		wp_register_script( 'audiotheme-gig-edit',    AUDIOTHEME_URI . 'admin/js/gig-edit.js',    array( 'audiotheme-admin', 'audiotheme-venue-modal', 'jquery-timepicker', 'jquery-ui-autocomplete', 'jquery-ui-datepicker', 'pikaday', 'underscore' ), AUDIOTHEME_VERSION, true );
		wp_register_script( 'audiotheme-venue-edit',  AUDIOTHEME_URI . 'admin/js/venue-edit.js',  array( 'audiotheme-admin', 'jquery-ui-autocomplete', 'post', 'underscore' ), AUDIOTHEME_VERSION, true );
		wp_register_script( 'audiotheme-venue-modal', AUDIOTHEME_URI . 'admin/js/venue-modal.js', array( 'audiotheme-admin', 'jquery', 'media-models', 'media-views', 'underscore', 'wp-backbone', 'wp-util' ), AUDIOTHEME_VERSION, true );

		$post_type_object = get_post_type_object( 'audiotheme_venue' );

		$settings = array(
			'canPublishVenues' => false,
			'canEditVenues'    => current_user_can( $post_type_object->cap->edit_posts ),
			'insertVenueNonce' => false,
			'l10n'             => array(

			),
		);

		if ( current_user_can( $post_type_object->cap->publish_posts ) ) {
			$settings['canPublishVenues'] = true;
			$settings['insertVenueNonce'] = wp_create_nonce( 'insert-venue' );
		}

		wp_localize_script( 'audiotheme-venue-modal', '_audiothemeVenueModalSettings', $settings );
	}

	/**
	 * Sanitize the 'per_page' screen option on the Manage Gigs and Manage Venues
	 * screens.
	 *
	 * Apparently any other hook attached to the same filter that runs after this
	 * will stomp all over it. To prevent this filter from doing the same, it's
	 * only attached on the screens that require it. The priority should be set
	 * extremely low to help ensure the correct value gets returned.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $return Default is 'false'.
	 * @param string $option The option name.
	 * @param mixed $value The value to sanitize.
	 * @return mixed The sanitized value.
	 */
	public function screen_options( $return, $option, $value ) {
		global $pagenow;

		// Only run on the gig and venue Manage Screens.
		$is_manage_screen = 'admin.php' == $pagenow && isset( $_GET['page'] ) && ( 'audiotheme-gigs' == $_GET['page'] || 'audiotheme-venues' == $_GET['page'] );
		$is_valid_option  = ( 'toplevel_page_audiotheme_gigs_per_page' == $option || 'gigs_page_audiotheme_venues_per_page' == $option );

		if ( $is_manage_screen && $is_valid_option ) {
			$return = absint( $value );
		}

		return $return;
	}

	/**
	 * Higlight the correct top level and sub menu items for the gig screen being
	 * displayed.
	 *
	 * @since 1.0.0
	 *
	 * @param string $parent_file The screen being displayed.
	 * @return string The menu item to highlight.
	 */
	public function admin_menu_highlight( $parent_file ) {
		global $pagenow, $post_type, $submenu, $submenu_file;

		if ( 'audiotheme_gig' == $post_type ) {
			$parent_file  = 'audiotheme-gigs';
			$submenu_file = ( 'post.php' == $pagenow ) ? 'audiotheme-gigs' : $submenu_file;
		}

		if ( 'audiotheme-gigs' == $parent_file && isset( $_GET['page'] ) && 'audiotheme-venue' == $_GET['page'] ) {
			$submenu_file = 'audiotheme-venues';
		}

		// Remove the Add New Venue submenu item.
		if ( isset( $submenu['audiotheme-gigs'] ) ) {
			foreach ( $submenu['audiotheme-gigs'] as $key => $sm ) {
				if ( isset( $sm[0] ) && 'audiotheme-venue' == $sm[2] ) {
					unset( $submenu['audiotheme-gigs'][ $key ] );
				}
			}
		}

		return $parent_file;
	}

	/**
	 * Discography update messages.
	 *
	 * @since 2.0.0
	 * @see /wp-admin/edit-form-advanced.php
	 *
	 * @param array $messages The array of existing post update messages.
	 * @return array
	 */
	public function post_updated_messages( $messages ) {
		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		if ( 'audiotheme_gig' == $post_type ) {
			$messages['audiotheme_gig'] = array(
				0  => '', // Unused. Messages start at index 1.
				1  => sprintf( __( 'Gig updated. <a href="%s">View Gig</a>', 'audiotheme' ), esc_url( get_permalink( $post->ID ) ) ),
				2  => __( 'Custom field updated.', 'audiotheme' ),
				3  => __( 'Custom field deleted.', 'audiotheme' ),
				4  => __( 'Gig updated.', 'audiotheme' ),
				/* translators: %s: date and time of the revision */
				5  => isset( $_GET['revision'] ) ? sprintf( __( 'Gig restored to revision from %s', 'audiotheme' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6  => sprintf( __( 'Gig published. <a href="%s">View Gig</a>', 'audiotheme' ), esc_url( get_permalink( $post->ID ) ) ),
				7  => __( 'Gig saved.', 'audiotheme' ),
				8  => sprintf( __( 'Gig submitted. <a target="_blank" href="%s">Preview Gig</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
				9  => sprintf( __( 'Gig scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Gig</a>', 'audiotheme' ),
					  /* translators: Publish box date format, see http://php.net/date */
					  date_i18n( __( 'M j, Y @ G:i', 'audiotheme' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post->ID ) ) ),
				10 => sprintf( __( 'Gig draft updated. <a target="_blank" href="%s">Preview Gig</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
			);
		}

		return $messages;
	}
}
