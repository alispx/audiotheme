<?php
/**
 * Edit gig administration screen functionality.
 *
 * @package AudioTheme\Core\Gigs
 * @since 2.0.0
 */

namespace AudioTheme\Core\Admin\Screen;

use AudioTheme\Core\Model\Venue;
use AudioTheme\Core\Util;

/**
 * Edit gig administration screen class.
 *
 * @package AudioTheme\Core\Gigs
 * @since 2.0.0
 */
class EditGig {
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
		add_action( 'admin_menu',                    array( $this, 'add_menu_item' ) );
		add_action( 'load-post.php',                 array( $this, 'load_screen' ) );
		add_action( 'load-post-new.php',             array( $this, 'load_screen' ) );
		add_action( 'add_meta_boxes_audiotheme_gig', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post',                     array( $this, 'on_gig_save' ), 10, 2 );
	}

	/**
	 * Add the menu item to add a gig.
	 *
	 * @since 2.0.0
	 */
	public function add_menu_item() {
		$post_type_object = get_post_type_object( 'audiotheme_gig' );

		add_submenu_page(
			'audiotheme-gigs',
			$post_type_object->labels->add_new_item,
			$post_type_object->labels->add_new,
			'edit_posts',
			'post-new.php?post_type=audiotheme_gig'
		);
	}

	/**
	 * Set up the gig Add/Edit screen.
	 *
	 * Add custom meta boxes, enqueues scripts and styles, and hook up the action
	 * to display the edit fields after the title.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post The gig post object being edited.
	 */
	public function load_screen( $post ) {
		if ( 'audiotheme_gig' != get_current_screen()->post_type ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'edit_form_after_title', array( $this, 'display_edit_fields' ) );
		add_action( 'admin_footer',          array( $this, 'print_templates' ) );
	}

	/**
	 * Register gig meta boxes.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post The gig post object being edited.
	 */
	public function register_meta_boxes( $post ) {
		remove_meta_box( 'submitdiv', 'audiotheme_gig', 'side' );

		add_meta_box(
			'submitdiv',
			__( 'Publish', 'audiotheme' ),
			'audiotheme_post_submit_meta_box',
			'audiotheme_gig',
			'side',
			'high',
			array(
				'force_delete'      => false,
				'show_publish_date' => false,
				'show_statuses'     => array(),
				'show_visibility'   => false,
			)
		);

		// Add a meta box for entering ticket information.
		add_meta_box(
			'audiothemegigticketsdiv',
			__( 'Tickets', 'audiotheme' ),
			array( $this, 'display_tickets_meta_box' ),
			'audiotheme_gig',
			'side',
			'default'
		);
	}

	/**
	 * Enqueue assets for the Edit Gig screen.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_assets() {
		wp_enqueue_script( 'audiotheme-gig-edit' );
		wp_enqueue_script( 'audiotheme-venue-modal' );
		wp_enqueue_script( 'pikaday' );
		wp_enqueue_style( 'audiotheme-venue-modal' );
		wp_enqueue_style( 'jquery-ui-theme-audiotheme' );
	}

	/**
	 * Set up and display the main gig fields for editing.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function display_edit_fields( $post ) {
		$gig = get_audiotheme_gig( $post->ID );

		$gig_date  = '';
		$gig_time  = '';
		$gig_venue = '';

		if ( $gig->gig_datetime ) {
			$timestamp = strtotime( $gig->gig_datetime );
			$gig_date  = date( 'Y/m/d', $timestamp );

			$t = date_parse( $gig->gig_time );
			if ( empty( $t['errors'] ) ) {
				$gig_time = date( $this->compatible_time_format(), $timestamp );
			}
		}

		$venue    = isset( $gig->venue->ID ) ? new Venue( $gig->venue->ID ) : null;
		$venue_id = $venue ? $venue->ID : 0;

		wp_localize_script( 'audiotheme-gig-edit', '_audiothemeGigEditSettings', array(
			'venue'      => $venue ? $venue->prepare_for_js() : array(),
			'timeFormat' => $this->compatible_time_format(),
		) );

		require( AUDIOTHEME_DIR . 'admin/views/edit-gig.php' );
	}

	/**
	 * Gig tickets meta box.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post The gig post object being edited.
	 */
	public function display_tickets_meta_box( $post ) {
		?>
		<p class="audiotheme-field">
			<label for="gig-tickets-price">Price:</label><br>
			<input type="text" name="gig_tickets_price" id="gig-tickets-price" value="<?php echo esc_attr( get_post_meta( $post->ID, '_audiotheme_tickets_price', true ) ) ; ?>" class="large-text">
		</p>
		<p class="audiotheme-field">
			<label for="gig-tickets-url">Tickets URL:</label><br>
			<input type="text" name="gig_tickets_url" id="gig-tickets-url" value="<?php echo esc_attr( get_post_meta( $post->ID, '_audiotheme_tickets_url', true ) ) ; ?>" class="large-text">
		</p>
		<?php
	}

	/**
	 * Print Underscore.js templates.
	 *
	 * @since 2.0.0
	 */
	public function print_templates() {
		include( AUDIOTHEME_DIR . 'admin/views/templates-gig.php' );
		include( AUDIOTHEME_DIR . 'admin/views/templates-venue.php' );
	}

	/**
	 * Process and save gig info when the CPT is saved.
	 *
	 * @since 1.0.0
	 *
	 * @param int $gig_id Gig post ID.
	 * @param WP_Post $post Gig post object.
	 */
	public function on_gig_save( $post_id, $post ) {
		$is_autosave    = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
		$is_revision    = wp_is_post_revision( $post_id );
		$is_valid_nonce = isset( $_POST['audiotheme_save_gig_nonce'] ) && wp_verify_nonce( $_POST['audiotheme_save_gig_nonce'], 'save-gig_' . $post_id );
		$data_exists    = isset( $_POST['gig_date'] ) && isset( $_POST['gig_time'] );

		// Bail if the data shouldn't be saved or intention can't be verified.
		if( $is_autosave || $is_revision || ! $is_valid_nonce || ! $data_exists ) {
			return;
		}

		$venue    = set_audiotheme_gig_venue( $post_id, absint( $_POST['gig_venue_id'] ) );
		$datetime = Util::format_datetime_string( $_POST['gig_date'], $_POST['gig_time'] );
		$time     = Util::format_time_string( $_POST['gig_time'] );

		// Date and time are always stored local to the venue.
		// If GMT, or time in another locale is needed, use the venue time zone to calculate.
		// Other functions should be aware that time is optional; check for the presence of gig_time.
		update_post_meta( $post_id, '_audiotheme_gig_datetime', $datetime );

		// Time is saved separately to check for empty values, TBA, etc.
		update_post_meta( $post_id, '_audiotheme_gig_time', $time );
		update_post_meta( $post_id, '_audiotheme_tickets_price', $_POST['gig_tickets_price'] );
		update_post_meta( $post_id, '_audiotheme_tickets_url', $_POST['gig_tickets_url'] );
	}

	/**
	 * Attempt to make custom time formats more compatible between JavaScript and PHP.
	 *
	 * If the time format option has an escape sequences, use a default format
	 * determined by whether or not the option uses 24 hour format or not.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function compatible_time_format() {
		$time_format = get_option( 'time_format' );

		if ( false !== strpos( $time_format, '\\' ) ) {
			$time_format = false !== strpbrk( $time_format, 'GH' ) ? 'G:i' : 'g:i a';
		}

		return $time_format;
	}
}
