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
class VideoCategoryTaxonomy implements HookProviderInterface {
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
	public function register( Plugin $plugin ) {
		add_action( 'init',          array( $this, 'register_taxonomy' ) );
		add_action( 'pre_get_posts', array( $this, 'video_category_query' ), 9 );
	}

	/**
	 * Register taxonomies.
	 *
	 * @since 2.0.0
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Categories', 'taxonomy general name', 'audiotheme' ),
			'singular_name'              => _x( 'Category', 'taxonomy singular name', 'audiotheme' ),
			'search_items'               => __( 'Search Categories', 'audiotheme' ),
			'popular_items'              => __( 'Popular Categories', 'audiotheme' ),
			'all_items'                  => __( 'All Categories', 'audiotheme' ),
			'parent_item'                => __( 'Parent Category', 'audiotheme' ),
			'parent_item_colon'          => __( 'Parent Category:', 'audiotheme' ),
			'edit_item'                  => __( 'Edit Category', 'audiotheme' ),
			'view_item'                  => __( 'View Category', 'audiotheme' ),
			'update_item'                => __( 'Update Category', 'audiotheme' ),
			'add_new_item'               => __( 'Add New Category', 'audiotheme' ),
			'new_item_name'              => __( 'New Category Name', 'audiotheme' ),
			'separate_items_with_commas' => __( 'Separate categories with commas', 'audiotheme' ),
			'add_or_remove_items'        => __( 'Add or remove categories', 'audiotheme' ),
			'choose_from_most_used'      => __( 'Choose from most used categories', 'audiotheme' ),
			'menu_name'                  => __( 'Categories', 'audiotheme' ),
		);

		$args = array(
			'args'                           => array( 'orderby' => 'term_order' ),
			'hierarchical'                   => true,
			'labels'                         => $labels,
			'public'                         => true,
			'query_var'                      => true,
			'rewrite'                        => array(
				'slug'                       => $this->module->get_rewrite_base() . '/category',
				'with_front'                 => false,
			),
			'show_ui'                        => true,
			'show_admin_column'              => true,
			'show_in_nav_menus'              => true,
			'show_tagcloud'                  => false,
		);

		register_taxonomy( 'audiotheme_video_category', 'audiotheme_video', $args );
	}

	/**
	 * Set video category requests to use the same archive settings as videos.
	 *
	 * @since 2.0.0
	 */
	public function video_category_query( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! is_tax( 'audiotheme_video_category' ) ) {
			return;
		}

		$this->plugin['archives']->set_current_archive_post_type( 'audiotheme_video' );
	}
}