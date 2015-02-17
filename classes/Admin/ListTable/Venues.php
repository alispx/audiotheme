<?php
/**
 * Class to list venues on the Manage Venues screen.
 *
 * @package AudioTheme\Core\Gigs
 * @since 1.0.0
 */

namespace AudioTheme\Core\Admin\ListTable;

/**
 * Venues list table class.
 *
 * @since 1.0.0
 */
class Venues extends \WP_List_Table {
	/**
	 * Constructor.
	 *
	 * Sets up default properties.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'venue',
			'plural'   => 'venues',
			'ajax'     => false,
		) );
	}

	/**
	 * Prepares the list of venues for displaying.
	 *
	 * Modifies the query based on the current view and screen options and
	 * begins setting up columns.
	 *
	 * @since 1.0.0
	 */
	public function prepare_items() {
		global $wp_query, $wpdb;

		$screen   = get_current_screen();
		$per_page = get_user_option( 'gigs_page_audiotheme_venues_per_page' );
		$per_page = empty( $per_page ) ? 20 : $per_page;

		// Set up column headers.
		$columns               = $this->get_columns();
		$hidden                = get_hidden_columns( $screen->id );
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Compile the WP_Query args based on the current view and user options.
		$args = array(
			'post_type'      => 'audiotheme_venue',
			'order'          => isset( $_REQUEST['order'] ) && 'desc' == strtolower( $_REQUEST['order'] ) ? 'desc' : 'asc',
			'orderby'        => ! isset( $_REQUEST['orderby'] ) ? 'title' : $_REQUEST['orderby'],
			'posts_per_page' => $per_page,
		);

		if ( isset( $_REQUEST['orderby'] ) ) {
			switch( $_REQUEST['orderby'] ) {
				case 'gigs':
					$args['meta_key'] = '_audiotheme_gig_count';
					$args['orderby']  = 'meta_value_num';
					break;
				case 'city':
				case 'contact_name':
				case 'contact_phone':
				case 'contact_email':
				case 'country':
				case 'phone':
				case 'state':
				case 'website':
					$args['meta_key'] = '_audiotheme_' . $_REQUEST['orderby'];
					$args['orderby']  = 'meta_value';
					break;
			}
		}

		if ( isset( $_REQUEST['s'] ) ) {
			$args['s'] = stripslashes( $_REQUEST['s'] );
		}

		$args['paged'] = $this->get_pagenum();

		// Run the query.
		$items = array();
		$wp_query = new \WP_Query( $args );
		if ( isset( $wp_query->posts ) && count( $wp_query->posts ) ) {
			foreach ( $wp_query->posts as $post ) {
				$items[ $post->ID ] = get_audiotheme_venue( $post->ID );
			}
		}

		$this->items = $items;

		$this->set_pagination_args( array(
			'total_items' => $wp_query->found_posts,
			'per_page'    => $per_page,
			'total_pages' => $wp_query->max_num_pages,
		) );
	}

	/**
	 * Get the list of columns to display.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'            => '<input type="checkbox">',
			'name'          => __( 'Name', 'audiotheme' ),
			'city'          => __( 'City', 'audiotheme' ),
			'state'         => __( 'State', 'audiotheme' ),
			'country'       => __( 'Country', 'audiotheme' ),
			'phone'         => __( 'Phone', 'audiotheme' ),
			'contact_name'  => __( 'Contact', 'audiotheme' ),
			'contact_phone' => __( 'Contact Phone', 'audiotheme' ),
			'contact_email' => __( 'Contact Email', 'audiotheme' ),
			'gigs'          => __( 'Gigs', 'audiotheme' ),
			'website'       => sprintf(
				'<span class="audiotheme-column-icon dashicons dashicons-admin-links">%s</span>',
				__( 'Website', 'audiotheme' )
			),
		);

		// The screen id is used when managing column visibility.
		$columns = apply_filters( 'manage_' . $this->screen->id . '_posts_columns', $columns );

		return $columns;
	}

	/**
	 * Get the list of sortable columns.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'name'          => array( 'title', true ), // True means it's already sorted.
			'city'          => array( 'city', false ),
			'state'         => array( 'state', false ),
			'country'       => array( 'country', false ),
			'phone'         => array( 'phone', false ),
			'contact_name'  => array( 'contact_name', false ),
			'contact_phone' => array( 'contact_phone', false ),
			'contact_email' => array( 'contact_email', false ),
			'gigs'          => array( 'gigs', false ),
			'website'       => array( 'website', false ),
		);

		return $sortable_columns;
	}

	/**
	 * Get the actions that can be performed in bulk.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete Permanently', 'audiotheme' ),
		);

		return $actions;
	}

	/**
	 * Process actions.
	 *
	 * @since 1.0.0
	 * @see wp-admin/edit.php
	 */
	public function process_actions() {
		global $wpdb;

		$action           = '';
		$current_user     = wp_get_current_user();
		$post_type_object = get_post_type_object( 'audiotheme_venue' );

		$sendback = remove_query_arg( array( 'deleted', 'ids', 'message', 'venue_id' ), wp_get_referer() );
		if ( ! $sendback ) {
			$sendback = get_audiotheme_venues_admin_url();
		}
		$sendback = add_query_arg( 'paged', $this->get_pagenum(), $sendback );

		if ( isset( $_POST['audiotheme_venue'] ) && isset( $_POST['audiotheme_venue_nonce'] ) ) {
			$data         = $_POST['audiotheme_venue'];
			$nonce_action = empty( $data['ID'] ) ? 'add-venue' : 'update-venue_' . $data['ID'];

			// Should die on error.
			if ( check_admin_referer( $nonce_action, 'audiotheme_venue_nonce' ) ) {
				$action = ! empty( $data['ID'] ) ? 'edit' : 'add';
			}
		} elseif ( isset( $_REQUEST['action'] ) && 'delete' == $_REQUEST['action'] && ! empty( $_REQUEST['venue_id'] ) ) {
			$post_ids = array( absint( $_REQUEST['venue_id'] ) );

			if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'delete-venue_' . $post_ids[0] ) ) {
				$action   = 'delete';
				$sendback = get_audiotheme_venues_admin_url();
			}
		} elseif ( ! empty( $_REQUEST['ids'] ) ) {
			$post_ids = ( is_array( $_REQUEST['ids'] ) ) ? $_REQUEST['ids'] : explode( ',', $_REQUEST['ids'] );
			$post_ids = array_map( 'absint', $post_ids );

			if ( check_admin_referer( 'bulk-' . $this->_args['plural'] ) ) {
				$action = $this->current_action();
			}
		}

		if ( ! empty( $action ) ) {
			switch( $action ) {
				case 'add':
				case 'edit':
					$venue_id = save_audiotheme_venue( $data );

					if ( $venue_id && 'add' == $action ) {
						$sendback = add_query_arg( 'message', 1, $sendback );
					} elseif ( $venue_id && 'edit' == $action ) {
						$sendback = add_query_arg( 'updated', 1, $sendback );
					}
					break;
				case 'delete':
					$deleted = 0;
					foreach ( $post_ids as $post_id ) {
						if ( ! current_user_can( $post_type_object->cap->delete_post, $post_id ) )
							wp_die( __( 'You are not allowed to delete this item.', 'audiotheme' ) );

						if ( ! wp_delete_post( $post_id ) )
							wp_die( __( 'Error in deleting&hellip;', 'audiotheme' ) );
						$deleted++;
					}
					$sendback = add_query_arg( 'deleted', $deleted, $sendback );
					break;
				default:
					break;
			}

			$sendback = remove_query_arg( array( 'action', 'action2' ), $sendback );
			wp_redirect( $sendback );
			exit;
		}

		if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
			 wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), stripslashes( $_SERVER['REQUEST_URI'] ) ) );
			 exit;
		}
	}

	/**
	 * Display the checkbox column.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $item Venue post object.
	 * @return string Column value.
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="ids[]" value="%s">', $item->ID );
	}

	/**
	 * Display the venue name column along with any row actions.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $item Venue post object.
	 * @return string Column value.
	 */
	public function column_name( $item ) {
		$post_type_object = get_post_type_object( 'audiotheme_venue' );

		$output = sprintf(
			'<strong><a href="%s" class="row-title">%s</a></strong><br>',
			esc_url( get_edit_post_link( $item->ID ) ),
			$item->name
		);

		$actions['edit'] = sprintf( '<a href="%s">Edit</a>', get_edit_post_link( $item->ID ) );

		$delete_args['action']   = 'delete';
		$delete_args['venue_id'] = $item->ID;
		$delete_url              = get_audiotheme_venues_admin_url( $delete_args );
		$delete_url_onclick      = " onclick=\"return confirm('" . esc_js( sprintf( __( 'Are you sure you want to delete this %s?', 'audiotheme' ), strtolower( $post_type_object->labels->singular_name ) ) ) . "');\"";

		$actions['delete'] = sprintf(
			'<a href="%s"%s>%s</a>',
			wp_nonce_url( $delete_url, 'delete-venue_' . $item->ID ),
			$delete_url_onclick,
			__( 'Delete', 'audiotheme' )
		);

		$output .= $this->row_actions( $actions );

		return $output;
	}

	/**
	 * Display other columns.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $item Venue post object.
	 * @param string $column_name The column id to display.
	 * @return string Column value for display.
	 */
	public function column_default( $item, $column_name ) {
		switch($column_name){
			case 'gigs':
				$count     = get_post_meta( $item->ID, '_audiotheme_gig_count', true );
				$admin_url = get_audiotheme_gig_admin_url( array( 'post_type' => 'audiotheme_gig', 'post_status' => 'any', 'venue' => $item->ID ) );
				return empty( $count ) ? $count : sprintf( '<a href="%s">%d</a>', $admin_url, $count );
			case 'website':
				$website = '';
				if ( ! empty( $website ) ) {
					$website = sprintf(
						' <a href="%s" class="venue-website-link dashicons dashicons-admin-links" target="_blank"></a>',
						esc_url( $item->website ),
						esc_attr( __( 'Visit venue website', 'audiotheme' ) )
					);
				}
				return $website;
			default:
				return isset( $item->{$column_name} ) ? $item->{$column_name} : '';
		}
	}
}
