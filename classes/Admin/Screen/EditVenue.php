<?php
/**
 * Edit venue administration screen functionality.
 *
 * @package AudioTheme\Core\Gigs
 * @since 2.0.0
 */

namespace AudioTheme\Core\Admin\Screen;

/**
 * Edit venue administration screen class.
 *
 * @package AudioTheme\Core\Gigs
 * @since 2.0.0
 */
class EditVenue {
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
	 * Add the menu item to add a venue.
	 *
	 * @since 2.0.0
	 */
	public function add_menu_item() {
		$post_type_object = get_post_type_object( 'audiotheme_venue' );

		$screen_hook = add_submenu_page(
			'audiotheme-gigs',
			$post_type_object->labels->add_new_item,
			$post_type_object->labels->add_new_item,
			'edit_posts',
			'audiotheme-venue',
			array( $this, 'display_screen' )
		);

		add_action( 'load-' . $screen_hook, array( $this, 'load_screen' ) );
	}

	/**
	 * Set up the gig Add/Edit screen.
	 *
	 * Add custom meta boxes, enqueues scripts and styles, and hook up the action
	 * to display the edit fields after the title.
	 *
	 * @since 1.0.0
	 */
	public function load_screen() {
		$this->process_actions();
		$this->register_meta_boxes();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register venue meta boxes.
	 *
	 * @since 2.0.0
	 */
	public function register_meta_boxes() {
		$screen = get_current_screen();
		$values = get_default_audiotheme_venue_properties();

		if ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] && isset( $_GET['venue_id'] ) && is_numeric( $_GET['venue_id'] ) ) {
			$venue_to_edit = get_audiotheme_venue( $_GET['venue_id'] );
			$values        = wp_parse_args( get_object_vars( $venue_to_edit ), $values );
		}

		add_meta_box(
			'venuecontactdiv',
			__( 'Contact <i>(Private)</i>', 'audiotheme' ),
			array( $this, 'display_contact_meta_box' ),
			$screen->id,
			'normal',
			'core',
			$values
		);

		add_meta_box(
			'venuenotesdiv',
			__( 'Notes <i>(Private)</i>', 'audiotheme' ),
			array( $this, 'display_notes_meta_box' ),
			$screen->id,
			'normal',
			'core',
			$values
		);

		// The 'submitdiv' id prevents the meta box from being hidden.
		add_meta_box(
			'submitdiv',
			__( 'Save', 'audiotheme' ),
			array( $this, 'display_submit_meta_box' ),
			$screen->id,
			'side',
			'high'
		);
	}

	/**
	 * Enqueue assets for the Edit Gig screen.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_assets() {
		wp_enqueue_script( 'audiotheme-venue-edit' );
		wp_enqueue_style( 'jquery-ui-theme-audiotheme' );
	}

	/**
	 * Display the venue add/edit screen.
	 *
	 * @since 1.0.0
	 */
	public function display_screen() {
		$screen           = get_current_screen();
		$post_type_object = get_post_type_object( 'audiotheme_venue' );

		$action      = 'add';
		$nonce_field = wp_nonce_field( 'add-venue', 'audiotheme_venue_nonce', true, false );
		$values      = get_default_audiotheme_venue_properties();

		if ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] && isset( $_GET['venue_id'] ) && is_numeric( $_GET['venue_id'] ) ) {
			$venue_to_edit = get_audiotheme_venue( $_GET['venue_id'] );
			$action        = 'edit';
			$nonce_field   = wp_nonce_field( 'update-venue_' . $venue_to_edit->ID, 'audiotheme_venue_nonce', true, false );
			$values        = wp_parse_args( get_object_vars( $venue_to_edit ), $values );
		}

		extract( $values, EXTR_SKIP );
		require( AUDIOTHEME_DIR . 'admin/views/screen-edit-venue.php' );
	}

	/**
	 * Display venue contact information meta box.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post Venue post object.
	 * @param array $args Additional args passed during meta box registration.
	 */
	public function display_contact_meta_box( $post, $args ) {
		extract( $args['args'], EXTR_SKIP );
		require( AUDIOTHEME_DIR . 'admin/views/edit-venue-contact.php' );
	}

	/**
	 * Display venue notes meta box.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post Venue post object.
	 * @param array $args Additional args passed during meta box registration.
	 */
	public function display_notes_meta_box( $post, $args ) {
		extract( $args['args'], EXTR_SKIP );

		$notes = format_to_edit( $notes, user_can_richedit() );

		wp_editor( $notes, 'venuenotes', array(
			'editor_css'    => '<style type="text/css" scoped="true">.mceIframeContainer { background-color: #fff;}</style>',
			'media_buttons' => false,
			'textarea_name' => 'audiotheme_venue[notes]',
			'textarea_rows' => 6,
			'teeny'         => true,
		) );
	}

	/**
	 * Display custom venue submit meta box.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post Venue post object.
	 */
	public function display_submit_meta_box( $post ) {
		$post             = empty( $post ) ? get_default_post_to_edit( 'audiotheme_venue' ) : $post;
		$post_type        = $post->post_type;
		$post_type_object = get_post_type_object( $post_type );
		$can_publish      = current_user_can( $post_type_object->cap->publish_posts );
		?>
		<div class="submitbox" id="submitpost">

			<div id="major-publishing-actions">

				<?php if ( 'auto-draft' != $post->post_status && 'draft' != $post->post_status ) : ?>
					<div id="delete-action">
						<?php
						if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) ) {
							$delete_args['action']   = 'delete';
							$delete_args['venue_id'] = $post->ID;
							$delete_url              = get_audiotheme_venues_admin_url( $delete_args );
							$delete_url_onclick      = " onclick=\"return confirm('" . esc_js( sprintf( __( 'Are you sure you want to delete this %s?', 'audiotheme' ), strtolower( $post_type_object->labels->singular_name ) ) ) . "');\"";
							echo sprintf( '<a href="%s" class="submitdelete deletion"%s>%s</a>', wp_nonce_url( $delete_url, 'delete-venue_' . $post->ID ), $delete_url_onclick, esc_html( __( 'Delete Permanently', 'audiotheme' ) ) );
						}
						?>
					</div>
				<?php endif; ?>

				<div id="publishing-action">
					<?php
					audiotheme_admin_spinner( array( 'id' => 'ajax-loading' ) );

					if ( ! in_array( $post->post_status, array( 'publish', 'future', 'private' ) ) || 0 == $post->ID ) {
						?>
						<input type="hidden" name="original_publish" id="original_publish" value="<?php esc_attr_e( 'Publish', 'audiotheme' ) ?>">
						<?php
						submit_button( $post_type_object->labels->add_new_item, 'primary', 'publish', false, array( 'accesskey' => 'p' ) );
					} else {
						?>
						<input type="hidden" name="original_publish" id="original_publish" value="<?php esc_attr_e( 'Update', 'audiotheme' ) ?>">
						<input type="submit" name="save" id="publish" class="button-primary" accesskey="p" value="<?php esc_attr_e( 'Update', 'audiotheme' ) ?>">
					<?php } ?>
				</div><!--end div#publishing-action-->

				<div class="clear"></div>
			</div><!--end div#major-publishing-actions-->
		</div><!--end div#submitpost-->

		<script type="text/javascript">
		jQuery(function( $ ) {
			$( 'input[type="submit"], a.submitdelete' ).click(function(){
				window.onbeforeunload = null;
				$( ':button, :submit', '#submitpost' ).each(function(){
					var t = $( this );
					if ( t.hasClass( 'button-primary' ) ) {
						t.addClass( 'button-primary-disabled' );
					} else {
						t.addClass( 'button-disabled' );
					}
				});

				if ( 'publish' === $( this ).attr( 'id' ) ) {
					$( '#major-publishing-actions .spinner' ).show();
				}
			});
		});
		</script>
		<?php
	}

	/**
	 * Process venue add/edit actions.
	 *
	 * @since 1.0.0
	 */
	protected function process_actions() {
		$action = '';
		if ( isset( $_POST['audiotheme_venue'] ) && isset( $_POST['audiotheme_venue_nonce'] ) ) {
			$data         = $_POST['audiotheme_venue'];
			$nonce_action = ( empty( $data['ID'] ) ) ? 'add-venue' : 'update-venue_' . $data['ID'];

			// Should die on error.
			if ( check_admin_referer( $nonce_action, 'audiotheme_venue_nonce' ) ) {
				$action = ( ! empty( $data['ID'] ) ) ? 'edit' : 'add';
			}
		}

		if ( ! empty( $action ) ) {
			$venue_id = save_audiotheme_venue( $data );
			$sendback = get_edit_post_link( $venue_id );

			if ( $venue_id && 'add' == $action ) {
				$sendback = add_query_arg( 'message', 1, $sendback );
			} elseif ( $venue_id && 'edit' == $action ) {
				$sendback = add_query_arg( 'message', 2, $sendback );
			}

			wp_redirect( $sendback );
			exit;
		}
	}
}
