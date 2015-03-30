<?php
/**
 *
 *
 * @package AudioTheme\Core
 * @since 2.0.0
 */

namespace AudioTheme\Core\Provider\Taxonomy;

use AudioTheme\Core\HookProviderInterface;
use AudioTheme\Core\Plugin;

/**
 *
 *
 * @package AudioTheme\Core
 * @since   2.0.0
 */
class RecordTypeTaxonomy implements HookProviderInterface {
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
		add_action( 'init', array( $this, 'register_taxonomy' ) );
	}

	/**
	 * Register taxonomies.
	 *
	 * @since 2.0.0
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Record Types', 'taxonomy general name', 'audiotheme' ),
			'singular_name'              => _x( 'Record Type', 'taxonomy singular name', 'audiotheme' ),
			'search_items'               => __( 'Search Record Types', 'audiotheme' ),
			'popular_items'              => __( 'Popular Record Types', 'audiotheme' ),
			'all_items'                  => __( 'All Record Types', 'audiotheme' ),
			'parent_item'                => __( 'Parent Record Type', 'audiotheme' ),
			'parent_item_colon'          => __( 'Parent Record Type:', 'audiotheme' ),
			'edit_item'                  => __( 'Edit Record Type', 'audiotheme' ),
			'view_item'                  => __( 'View Record Type', 'audiotheme' ),
			'update_item'                => __( 'Update Record Type', 'audiotheme' ),
			'add_new_item'               => __( 'Add New Record Type', 'audiotheme' ),
			'new_item_name'              => __( 'New Record Type Name', 'audiotheme' ),
			'separate_items_with_commas' => __( 'Separate record types with commas', 'audiotheme' ),
			'add_or_remove_items'        => __( 'Add or remove record types', 'audiotheme' ),
			'choose_from_most_used'      => __( 'Choose from most used record types', 'audiotheme' ),
		);

		$args = array(
			'args'                           => array( 'orderby' => 'term_order' ),
			'hierarchical'                   => false,
			'labels'                         => $labels,
			'meta_box_cb'                    => 'audiotheme_taxonomy_checkbox_list_meta_box',
			'public'                         => true,
			'query_var'                      => true,
			'rewrite'                        => array(
				'slug'                       => $this->module->get_rewrite_base() . '/type',
				'with_front'                 => false,
			),
			'show_ui'                        => true,
			'show_admin_column'              => true,
			'show_in_nav_menus'              => true,
			'show_tagcloud'                  => false,
		);

		register_taxonomy( 'audiotheme_record_type', 'audiotheme_record', $args );
	}
}