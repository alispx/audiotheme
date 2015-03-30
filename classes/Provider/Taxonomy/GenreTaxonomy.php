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
class GenreTaxonomy implements HookProviderInterface {
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
		add_action( 'init',                           array( $this, 'register_taxonomy' ) );
		add_filter( 'term_link',                      array( $this, 'term_permalinks' ), 10, 3 );
		add_filter( 'post_type_archive_title',        array( $this, 'archive_title' ) );
		add_filter( 'audiotheme_archive_description', array( $this, 'archive_description' ) );
	}

	/**
	 * Register taxonomies.
	 *
	 * @since 2.0.0
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Genres', 'taxonomy general name', 'audiotheme' ),
			'singular_name'              => _x( 'Genre', 'taxonomy singular name', 'audiotheme' ),
			'search_items'               => __( 'Search Genres', 'audiotheme' ),
			'popular_items'              => __( 'Popular Genres', 'audiotheme' ),
			'all_items'                  => __( 'All Genres', 'audiotheme' ),
			'parent_item'                => __( 'Parent Genre', 'audiotheme' ),
			'parent_item_colon'          => __( 'Parent Genre:', 'audiotheme' ),
			'edit_item'                  => __( 'Edit Genre', 'audiotheme' ),
			'view_item'                  => __( 'View Genre', 'audiotheme' ),
			'update_item'                => __( 'Update Genre', 'audiotheme' ),
			'add_new_item'               => __( 'Add New Genre', 'audiotheme' ),
			'new_item_name'              => __( 'New Genre Name', 'audiotheme' ),
			'separate_items_with_commas' => __( 'Separate genres with commas', 'audiotheme' ),
			'add_or_remove_items'        => __( 'Add or remove genres', 'audiotheme' ),
			'choose_from_most_used'      => __( 'Choose from most used genres', 'audiotheme' ),
			'menu_name'                  => __( 'Genres', 'audiotheme' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'meta_box_cb'       => 'audiotheme_taxonomy_checkbox_list_meta_box',
			'public'            => true,
			'query_var'         => true,
			'rewrite'           => false,
			'show_admin_column' => true,
			'show_ui'           => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
		);

		register_taxonomy( 'audiotheme_genre', array( 'audiotheme_record' ), $args );
	}

	/**
	 * Filter genre term permalinks.
	 *
	 * @since 2.0.0
	 *
	 * @param string $link Term permalink.
	 * @param object $term Term object.
	 * @param string $taxonomy Taxonomy name.
	 * @return string
	 */
	public function term_permalinks( $link, $term, $taxonomy ) {
		if ( 'audiotheme_genre' == $taxonomy && get_option( 'permalink_structure' ) ) {
			$link = home_url( sprintf( '/%s/genre/%s/', $this->module->get_rewrite_base(), $term->slug ) );
		}
		return $link;
	}

	/**
	 * Filter archive titles to display the genre name.
	 *
	 * @since 2.0.0
	 *
	 * @param string $title Archive title.
	 * @return string
	 */
	public function archive_title( $title ) {
		if ( is_tax( 'audiotheme_genre' ) ) {
			$title = single_term_title( '', false );
		}
		return $title;
	}

	/**
	 * Filter archive descriptions to display the genre term description.
	 *
	 * @since 2.0.0
	 *
	 * @param string $description Archive description.
	 * @return string
	 */
	public function archive_description( $description ) {
		if ( is_tax( 'audiotheme_genre' ) ) {
			$description = term_description();
		}
		return $description;
	}
}
