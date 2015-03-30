<?php
/**
 * Venue post type registration and integration.
 *
 * @package AudioTheme\Core
 * @since 2.0.0
 */

namespace AudioTheme\Core\Provider\PostType;

use AudioTheme\Core\HookProviderInterface;
use AudioTheme\Core\Plugin;
use AudioTheme\Core\Util;

/**
 * Class for registering the venue post type and integration.
 *
 * @package AudioTheme\Core
 * @since   2.0.0
 */
class VenuePostType implements HookProviderInterface {
	/**
	 * Module.
	 *
	 * @since 2.0.0
	 * @var \AudioTheme\Core\Module
	 */
	protected $module;

	/**
	 * Plugin instance.
	 *
	 * @since 2.0.0
	 * @var \AudioTheme\Core\Plugin
	 */
	protected $plugin;

	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 *
	 * @param \AudioTheme\Core\Plugin Main plugin instance.
	 * @param \AudioTheme\Core\Module Module instance.
	 */
	public function __construct( Plugin $plugin, $module ) {
		$this->plugin = $plugin;
		$this->module = $module;
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 *
	 * @param \AudioTheme\Core\Plugin Main plugin instance.
	 */
	public function register_hooks( Plugin $plugin ) {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'get_edit_post_link', 'get_audiotheme_venue_edit_link', 10, 2 );
	}

	/**
	 * Register the venue post type.
	 *
	 * @since 2.0.0
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Venues', 'post type general name', 'audiotheme' ),
			'singular_name'      => _x( 'Venue', 'post type singular name', 'audiotheme' ),
			'add_new'            => _x( 'Add New', 'venue', 'audiotheme' ),
			'add_new_item'       => __( 'Add New Venue', 'audiotheme' ),
			'edit_item'          => __( 'Edit Venue', 'audiotheme' ),
			'new_item'           => __( 'New Venue', 'audiotheme' ),
			'view_item'          => __( 'View Venue', 'audiotheme' ),
			'search_items'       => __( 'Search Venues', 'audiotheme' ),
			'not_found'          => __( 'No venues found', 'audiotheme' ),
			'not_found_in_trash' => __( 'No venues found in Trash', 'audiotheme' ),
			'all_items'          => __( 'All Venues', 'audiotheme' ),
			'menu_name'          => __( 'Venues', 'audiotheme' ),
			'name_admin_bar'     => _x( 'Venues', 'add new on admin bar', 'audiotheme' ),
		);

		$args = array(
			'has_archive'            => false,
			'hierarchical'           => false,
			'labels'                 => $labels,
			'public'                 => false,
			'publicly_queryable'     => false,
			'query_var'              => 'audiotheme_venue',
			'rewrite'                => false,
			'supports'               => array( '' ),
		);

		register_post_type( 'audiotheme_venue', $args );
	}
}
