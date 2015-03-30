<?php
/**
 * Gigs AJAX actions.
 *
 * @package AudioTheme\Core
 * @since 2.0.0
 */

namespace AudioTheme\Core\Ajax;

use AudioTheme\Core\HookProviderInterface;
use AudioTheme\Core\Model\Venue;
use AudioTheme\Core\Plugin;

/**
 * Gigs AJAX actions class.
 *
 * @package AudioTheme\Core
 * @since   2.0.0
 */
class GigsAjax implements HookProviderInterface {
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
	public function register( Plugin $plugin ) {
		$this->plugin = $plugin;

		add_action( 'wp_ajax_audiotheme_ajax_get_venue',  array( $this, 'get_venue' ) );
		add_action( 'wp_ajax_audiotheme_ajax_get_venues', array( $this, 'get_venues' ) );
		add_action( 'wp_ajax_audiotheme_ajax_save_venue', array( $this, 'save_venue' ) );
	}

	/**
	 * Retrieve a venue.
	 *
	 * @since 2.0.0
	 */
	public function get_venue() {
		$venue = new Venue( absint( $_POST['ID'] ) );
		wp_send_json_success( $venue->prepare_for_js() );
	}

	/**
	 * Retrieve venues.
	 *
	 * @since 2.0.0
	 */
	public function get_venues() {
		$response = array();

		$query_args = isset( $_REQUEST['query_args'] ) ? (array) $_REQUEST['query_args'] : array();
		$query_args = array_intersect_key( $query_args, array_flip( array( 'paged', 'posts_per_page', 's' ) ) );
		$query_args = wp_parse_args( $query_args, array(
			'post_type'      => 'audiotheme_venue',
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );

		$query = new \WP_Query( $query_args );

		if ( $query->have_posts() ) {
			foreach ( $query->posts as $post ) {
				$venue      = new Venue( $post );
				$response[] = $venue->prepare_for_js();
			}
		}

		wp_send_json_success( $response );
	}

	/**
	 * Create or update a venue.
	 *
	 * @since 2.0.0
	 */
	public function save_venue() {
		$data = $_POST['model'];

		if ( empty( $data['ID'] ) ) {
			check_ajax_referer( 'insert-venue', 'nonce' );
		} else {
			check_ajax_referer( 'update-post_' . $data['ID'], 'nonce' );
		}

		$venue_id = save_audiotheme_venue( $data );
		$venue    = new Venue( $venue_id );
		wp_send_json_success( $venue->prepare_for_js() );
	}
}
