<?php
/**
 * Gig-related admin functionality.
 *
 * @package AudioTheme_Framework
 * @subpackage Gigs
 */

/**
 * Include gig admin dependencies.
 */
require( AUDIOTHEME_DIR . 'modules/gigs/admin/ajax.php' );

/**
 * Attach hooks for loading and managing gigs in the admin dashboard.
 *
 * @since 1.0.0
 */
function audiotheme_gigs_admin_setup() {
	global $pagenow;

	add_action( 'save_post', 'audiotheme_gig_save_post', 10, 2 );

	add_action( 'admin_menu', 'audiotheme_gigs_admin_menu' );
	add_filter( 'post_updated_messages', 'audiotheme_gig_post_updated_messages' );

	// Register ajax admin actions.
	add_action( 'wp_ajax_audiotheme_ajax_get_venue_matches', 'audiotheme_ajax_get_venue_matches' );
	add_action( 'wp_ajax_audiotheme_ajax_is_new_venue', 'audiotheme_ajax_is_new_venue' );

	// Register scripts.
	wp_register_script( 'audiotheme-gig-edit', AUDIOTHEME_URI . 'modules/gigs/admin/js/gig-edit.js', array( 'audiotheme-admin', 'audiotheme-pointer', 'jquery-timepicker', 'jquery-ui-autocomplete', 'jquery-ui-datepicker' ) );
	wp_localize_script( 'audiotheme-gig-edit', 'audiothemeGigsL10n', array(
		'datepickerIcon' => AUDIOTHEME_URI . 'admin/images/calendar.png',
		'timeFormat'     => get_option( 'time_format' ),
	) );

	wp_register_script( 'audiotheme-venue-edit', AUDIOTHEME_URI . 'modules/gigs/admin/js/venue-edit.js', array( 'audiotheme-admin', 'jquery-ui-autocomplete', 'post' ) );

	// Only run on the gig and venue Manage Screens.
	if ( 'admin.php' == $pagenow && isset( $_GET['page'] ) && ( 'audiotheme-gigs' == $_GET['page'] || 'audiotheme-venues' == $_GET['page'] ) ) {
		add_filter( 'set-screen-option', 'audiotheme_gigs_screen_options', 999, 3 );
	}
}

/**
 * Add the admin menu items for gigs.
 *
 * @since 1.0.0
 */
function audiotheme_gigs_admin_menu() {
	global $pagenow, $plugin_page, $typenow;

	// Redirect the default Manage Gigs screen.
	if ( 'audiotheme_gig' == $typenow && 'edit.php' == $pagenow ) {
		wp_redirect( get_audiotheme_gig_admin_url() );
		exit;
	}

	$gig_object = get_post_type_object( 'audiotheme_gig' );
	$venue_object = get_post_type_object( 'audiotheme_venue' );

	// Remove the default gigs menu item and replace it with the screen using the custom post list table.
	remove_submenu_page( 'audiotheme-gigs', 'edit.php?post_type=audiotheme_gig' );

	$manage_gigs_hook = add_menu_page(
		$gig_object->labels->name,
		$gig_object->labels->menu_name,
		'edit_posts',
		'audiotheme-gigs',
		'audiotheme_gigs_manage_screen',
		audiotheme_encode_svg( 'admin/images/dashicons/gigs.svg' ),
		512
	);
		add_submenu_page( 'audiotheme-gigs', $gig_object->labels->name, $gig_object->labels->all_items, 'edit_posts', 'audiotheme-gigs', 'audiotheme_gigs_manage_screen' );
		$edit_gig_hook = add_submenu_page( 'audiotheme-gigs', $gig_object->labels->add_new_item, $gig_object->labels->add_new, 'edit_posts', 'post-new.php?post_type=audiotheme_gig' );
		$manage_venues_hook = add_submenu_page( 'audiotheme-gigs', $venue_object->labels->name, $venue_object->labels->menu_name, 'edit_posts', 'audiotheme-venues', 'audiotheme_venues_manage_screen' );
		$edit_venue_hook = add_submenu_page( 'audiotheme-gigs', $venue_object->labels->add_new_item, $venue_object->labels->add_new_item, 'edit_posts', 'audiotheme-venue', 'audiotheme_venue_edit_screen' );

	add_action( 'parent_file', 'audiotheme_gigs_admin_menu_highlight' );
	add_action( 'load-' . $manage_gigs_hook, 'audiotheme_gigs_manage_screen_setup' );
	add_action( 'load-' . $edit_gig_hook, 'audiotheme_gig_edit_screen_setup' );
	add_action( 'load-' . $manage_venues_hook, 'audiotheme_venues_manage_screen_setup' );
	add_action( 'load-' . $edit_venue_hook, 'audiotheme_venue_edit_screen_setup' );
}

/**
 * Gig update messages.
 *
 * @see /wp-admin/edit-form-advanced.php
 *
 * @param array $messages The array of post update messages.
 * @return array
 */
function audiotheme_gig_post_updated_messages( $messages ) {
	global $post;

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

	return $messages;
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
function audiotheme_gigs_screen_options( $return, $option, $value ) {
	if ( 'toplevel_page_audiotheme_gigs_per_page' == $option || 'gigs_page_audiotheme_venues_per_page' == $option ) {
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
function audiotheme_gigs_admin_menu_highlight( $parent_file ) {
	global $pagenow, $post_type, $submenu, $submenu_file;

	if ( 'audiotheme_gig' == $post_type ) {
		$parent_file = 'audiotheme-gigs';
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
 * Set up the gig Manage Screen.
 *
 * Adds a help tab, initializes the custom post list table, and processes any
 * actions that need to be handled.
 *
 * @since 1.0.0
 */
function audiotheme_gigs_manage_screen_setup() {
	audiotheme_gig_list_help();

	$post_type_object = get_post_type_object( 'audiotheme_gig' );
	$title = $post_type_object->labels->name;
	add_screen_option( 'per_page', array( 'label' => $title, 'default' => 20 ) );

	require_once( AUDIOTHEME_DIR . 'modules/gigs/admin/class-audiotheme-gigs-list-table.php' );

	$gigs_list_table = new Audiotheme_Gigs_List_Table();
	$gigs_list_table->process_actions();
}

/**
 * Display the gig Manage Screen.
 *
 * @since 1.0.0
 */
function audiotheme_gigs_manage_screen() {
	$post_type_object = get_post_type_object( 'audiotheme_gig' );

	$gigs_list_table = new Audiotheme_Gigs_List_Table();
	$gigs_list_table->prepare_items();

	require( AUDIOTHEME_DIR . 'modules/gigs/admin/views/list-gigs.php' );
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
function audiotheme_gig_edit_screen_setup( $post ) {
	audiotheme_gig_help();

	wp_enqueue_script( 'audiotheme-gig-edit' );
	wp_enqueue_style( 'jquery-ui-theme-audiotheme' );

	if ( ! is_audiotheme_pointer_dismissed( 'at100_gigvenue_tz' ) ) {
		wp_enqueue_style( 'wp-pointer' );

		$pointer  = __( 'Be sure to set a timezone when you add new venues so you don\'t have to worry about converting dates and times.', 'audiotheme' ) . "\n\n";
		$pointer .= __( 'It also gives your visitors the ability to subscribe to your events in their own timezones.', 'audiotheme' ) . "\n\n";
		// $pointer_content .= '<a href="">Find out more.</a>'; // Maybe link this to a help section?
		audiotheme_enqueue_pointer( 'at100_gigvenue_tz', __( 'Venue Timezones', 'audiotheme' ), $pointer, array( 'position' => 'top' ) );
	}

	// Add a customized submit meta box.
	remove_meta_box( 'submitdiv', 'audiotheme_gig', 'side' );
	add_meta_box( 'submitdiv', __( 'Publish', 'audiotheme' ), 'audiotheme_post_submit_meta_box', 'audiotheme_gig', 'side', 'high', array(
		'force_delete'      => false,
		'show_publish_date' => false,
		'show_statuses'     => array(),
		'show_visibility'   => false,
	) );

	// Add a meta box for entering ticket information.
	add_meta_box( 'audiothemegigticketsdiv', __( 'Tickets', 'audiotheme' ), 'audiotheme_gig_tickets_meta_box', 'audiotheme_gig', 'side', 'default' );

	// Display the main gig fields after the title.
	add_action( 'edit_form_after_title', 'audiotheme_edit_gig_fields' );
}

/**
 * Setup and display the main gig fields for editing.
 *
 * @since 1.0.0
 */
function audiotheme_edit_gig_fields() {
	global $post, $wpdb;

	$gig = get_audiotheme_gig( $post->ID );

	$gig_date = '';
	$gig_time = '';
	$gig_venue = '';

	if ( $gig->gig_datetime ) {
		$timestamp = strtotime( $gig->gig_datetime );
		// jQuery date format is kinda limited?
		$gig_date = date( 'Y/m/d', $timestamp );

		$t = date_parse( $gig->gig_time );
		if ( empty( $t['errors'] ) ) {
			$gig_time = date( get_option( 'time_format' ), $timestamp );
		} else {
			// No values allowed other than valid times.
			$gig_time = '';
		}
	}

	$gig_venue = ( isset( $gig->venue->name ) ) ? $gig->venue->name : '';
	$timezone_string = ( isset( $gig->venue->timezone_string ) ) ? $gig->venue->timezone_string : '';

	require( AUDIOTHEME_DIR . 'modules/gigs/admin/views/edit-gig.php' );
}

/**
 * Gig tickets meta box.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post The gig post object being edited.
 */
function audiotheme_gig_tickets_meta_box( $post ) {
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
 * Process and save gig info when the CPT is saved.
 *
 * @since 1.0.0
 *
 * @param int $gig_id Gig post ID.
 * @param WP_Post $post Gig post object.
 */
function audiotheme_gig_save_post( $post_id, $post ) {
	$is_autosave    = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
	$is_revision    = wp_is_post_revision( $post_id );
	$is_valid_nonce = isset( $_POST['audiotheme_save_gig_nonce'] ) && wp_verify_nonce( $_POST['audiotheme_save_gig_nonce'], 'save-gig_' . $post_id );
	$data_exists    = isset( $_POST['gig_date'] ) && isset( $_POST['gig_time'] );

	// Bail if the data shouldn't be saved or intention can't be verified.
	if( $is_autosave || $is_revision || ! $is_valid_nonce || ! $data_exists ) {
		return;
	}

	$venue    = set_audiotheme_gig_venue( $post_id, $_POST['gig_venue'] );
	$datetime = audiotheme_datetime_string( $_POST['gig_date'], $_POST['gig_time'] );
	$time     = audiotheme_time_string( $_POST['gig_time'] );

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
 * Add a help tab to the gig list screen.
 *
 * @since 1.0.0
 */
function audiotheme_gig_list_help() {
	get_current_screen()->add_help_tab( array(
		'id'      => 'overview',
		'title'   => __( 'Overview', 'audiotheme' ),
		'content' => '<p>' . __( 'This screen provides access to all of your gigs. You can customize the display of this screen to suit your workflow.', 'audiotheme' ) . '</p>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'      => 'screen-content',
		'title'   => __( 'Screen Content', 'audiotheme' ),
		'content' =>
			'<p>' . __( "You can customize the appearance of this screen's content in a number of ways:", 'audiotheme' ) . '</p>' .
			'<ul>' .
			'<li>' . __( "You can hide/display columns based on your needs and decide how many gigs to list per screen using the Screen Options tab.", 'audiotheme' ) . '</li>' .
			'<li>' . __( "You can filter the list of gigs by status using the text links in the upper left to show Upcoming, Past, All, Published, Draft, or Trashed gigs. The default view is to show all upcoming gigs.", 'audiotheme' ) . '</li>' .
			'<li>' . __( "You can refine the list to show only gigs for a specific venue or from a specific month by using the dropdown menus above the gigs list. Click the Filter button after making your selection.", 'audiotheme' ) . '</li>' .
			'<li>' . __( "You can also sort your gigs in any view by clicking the column headers.", 'audiotheme' ) . '</li>' .
			'</ul>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'      => 'available-actions',
		'title'   => __( 'Available Actions', 'audiotheme' ),
		'content' =>
			'<p>' . __( "Hovering over a row in the gigs list will display action links that allow you to manage your gig. You can perform the following actions:", 'audiotheme' ) . '</p>' .
			'<ul>' .
			'<li>' . __( "<strong>Edit</strong> takes you to the editing screen for that gig. You can also reach that screen by clicking on the gig date.", 'audiotheme' ) . '</li>' .
			'<li>' . __( "<strong>Trash</strong> removes your gig from this list and places it in the trash, from which you can permanently delete it.", 'audiotheme' ) . '</li>' .
			'<li>' . __( "<strong>Preview</strong> will show you what your draft gig will look like if you publish it.", 'audiotheme' ) . '</li>' .
			'<li>' . __( "<strong>View</strong> will take you to your live site to view the gig. Which link is available depends on your gig's status.", 'audiotheme' ) . '</li>' .
			'</ul>',
	) );
}

/**
 * Add a help tab to the add/edit gig screen.
 *
 * @since 1.0.0
 */
function audiotheme_gig_help() {
	get_current_screen()->add_help_tab( array(
		'id'      => 'standard-fields',
		'title'   => __( 'Standard Fields', 'audiotheme' ),
		'content' =>
			'<p>' . __( "<strong>Title</strong> - Enter a title for your gig. After you enter a title, you'll see the permalink below, which you can edit.", 'audiotheme' ) . '</p>' .
			'<p>' . __( "<strong>Date</strong> - Choose the date of your gig or enter it in the <code>YYYY/MM/DD</code> format.", 'audiotheme' ) . '</p>' .
			'<p>' . __( "<strong>Time</strong> - Choose the time of your gig. Leave it blank if you don't know it.", 'audiotheme' ) . '</p>' .
			'<p>' . __( "<strong>Venue</strong> - Enter the name of a new venue, or select a saved venue. <em>It is important to select the time zone for new venues.</em> New venues will be saved to your venue database and you can update additional details on the Edit Venue screen.", 'audiotheme' ) . '</p>' .
			'<p>' . __( "<strong>Note</strong> - Enter a short note about the gig.", 'audiotheme' ) . '</p>' .
			'<p>' . __( "<strong>Editor</strong> - Enter a longer description for your gig. There are two modes of editing: Visual and Text. Choose the mode by clicking on the appropriate tab. Visual mode gives you a WYSIWYG editor. Click the last icon in the row to get a second row of controls. The Text mode allows you to enter HTML along with your description text. Line breaks will be converted to paragraphs automatically. You can insert media files by clicking the icons above the editor and following the directions. You can go to the distraction-free writing screen via the Fullscreen icon in Visual mode (second to last in the top row) or the Fullscreen button in Text mode (last in the row). Once there, you can make buttons visible by hovering over the top area. Exit Fullscreen back to the regular editor.", 'audiotheme' ) . '</p>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'		=> 'inserting-media',
		'title'		=> __( 'Inserting Media', 'audiotheme' ),
		'content' 	=>
			'<p>' . __( 'You can upload and insert media (images, audio, documents, etc.) by clicking the Add Media button. You can select from the images and files already uploaded to the Media Library, or upload new media to add to your gig description. To create an image gallery, select the images to add and click the "Create a new gallery" button.', 'audiotheme' ) . '</p>' .
			'<p>' . __( 'You can also embed media from many popular websites including Twitter, YouTube, Flickr and others by pasting the media URL on its own line into the gig description editor. Please refer to the Codex to <a href="http://codex.wordpress.org/Embeds">learn more about embeds</a>.', 'audiotheme' ) . '</p>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'		=> 'tickets',
		'title'		=> __( 'Tickets', 'audiotheme' ),
		'content' 	=>
			'<p>' . __( 'The ticket box allows you to share information about ticket purchases and availability.', 'audiotheme' ) . '</p>' .
			'<ul>' .
			'<li>' . __( "<strong>Price</strong> - Does it cost money to attend your gig? Share that here so there aren't any surprises.", 'audiotheme' ) . '</li>' .
			'<li>' . __( "<strong>URL</strong> - If tickets can be purchased online, provide a link.", 'audiotheme' ) . '</li>' .
			'</ul>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'		=> 'publish-settings',
		'title'		=> __( 'Publish Settings', 'audiotheme' ),
		'content' 	=>
			'<p>' . __( 'Several boxes on this screen contain settings for how your content will be published, including:', 'audiotheme' ) . '</p>' .
			'<ul>' .
			'<li>' . __( "<strong>Publish</strong> - When you're done adding a gig, click the Publish button to make it available on your site. If you're not ready to publish, or want to finish updating your gig later, click the Save Draft button to privately save your progress. You can access your drafts at a later time through the <strong>Gigs > All Gigs</strong> menu.", 'audiotheme' ) . '</li>' .
			'<li>' . __( '<strong>Featured Image</strong> - If the author of your theme built in support for featured images, you can set those here. Find out more about <a href="http://codex.wordpress.org/Post_Thumbnails" target="_blank">setting featured images</a> in the WordPress Codex.', 'audiotheme' ) . '</li>' .
			'</ul>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'      => 'customize-display',
		'title'   => __( 'Customize This Screen', 'audiotheme' ),
		'content' => '<p>' . __( 'The title, date, time, venue, note and big post editing area are fixed in place, but you can reposition all the other boxes using drag and drop. You can also minimize or expand them by clicking the title bar of each box. Use the Screen Options tab to hide or unhide boxes or to choose a 1 or 2-column layout for this screen.', 'audiotheme' ) . '</p>',
	) );
}
