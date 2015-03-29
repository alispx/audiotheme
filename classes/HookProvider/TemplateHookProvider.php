<?php
/**
 * Template hooks.
 *
 * @package AudioTheme\Core
 * @since 2.0.0
 */

namespace AudioTheme\Core\HookProvider;

use AudioTheme\Core\HookProviderInterface;
use AudioTheme\Core\Plugin;

/**
 * Template hooks class.
 *
 * @package AudioTheme\Core
 * @since   2.0.0
 */
class TemplateHookProvider implements HookProviderInterface {
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

		// Default hooks.
		add_action( 'wp_head',                      array( $this, 'document_javascript_support' ) );
		add_filter( 'body_class',                   array( $this, 'body_classes' ) );
		add_filter( 'wp_nav_menu_objects',          array( $this, 'nav_menu_classes' ), 10, 3 );
		add_filter( 'wp_prepare_attachment_for_js', array( $this, 'prepare_audio_attachment_for_js' ), 10, 3 );

		// Prevent the audiotheme_archive rewrite rules from being registered.
		add_filter( 'audiotheme_archive_rewrite_rules',   '__return_empty_array' );
		add_filter( 'audiotheme_archive_settings_fields', array( $this, 'default_archive_settings_fields' ), 9, 2 );

		// Deprecated.
		add_action( 'init', 'audiotheme_less_setup' );
	}

	/**
	 * Add a 'js' class to the html element if JavaScript is enabled.
	 *
	 * @since 2.0.0
	 */
	public function document_javascript_support() {
		?>
		<script>
		var classes = document.documentElement.className.replace( 'no-js', 'js' );
		document.documentElement.className += /^js$|^js | js$| js /.test( classes ) ? '' : ' js';
		</script>
		<?php
	}

	/**
	 * Add HTML classes to the body element.
	 *
	 * @since 2.0.0
	 *
	 * @param array $classes Array of classes.
	 * @return array
	 */
	public function body_classes( $classes ) {
		if ( is_audiotheme_theme_compat_active() ) {
			$classes[] = 'audiotheme-theme-compat';
		}

		return $classes;
	}

	/**
	 * Add helpful nav menu item classes.
	 *
	 * @since 2.0.0
	 *
	 * @param array $items List of menu items.
	 * @param array $args Menu display args.
	 * @return array
	 */
	public function nav_menu_classes( $items, $args ) {
		global $wp;

		if ( is_404() || is_search() ) {
			return $items;
		}

		$current_url  = trailingslashit( home_url( add_query_arg( array(), $wp->request ) ) );
		$blog_page_id = get_option( 'page_for_posts' );
		$is_blog_post = is_singular( 'post' );

		$is_audiotheme_post_type = is_singular( array( 'audiotheme_gig', 'audiotheme_record', 'audiotheme_track', 'audiotheme_video' ) );
		$post_type_archive_id    = get_audiotheme_post_type_archive( get_post_type() );
		$post_type_archive_link  = get_post_type_archive_link( get_post_type() );

		$current_menu_parents = array();

		foreach ( $items as $key => $item ) {
			if (
				'audiotheme_archive' == $item->object &&
				$post_type_archive_id == $item->object_id &&
				trailingslashit( $item->url ) == $current_url
			) {
				$items[ $key ]->classes[] = 'current-menu-item';
				$current_menu_parents[] = $item->menu_item_parent;
			}

			if ( $is_blog_post && $blog_page_id == $item->object_id ) {
				$items[ $key ]->classes[] = 'current-menu-parent';
				$current_menu_parents[] = $item->menu_item_parent;
			}

			// Add 'current-menu-parent' class to CPT archive links when viewing a singular template.
			if ( $is_audiotheme_post_type && $post_type_archive_link == $item->url ) {
				$items[ $key ]->classes[] = 'current-menu-parent';
			}
		}

		// Add 'current-menu-parent' classes.
		$current_menu_parents = array_filter( $current_menu_parents );

		if ( ! empty( $current_menu_parents ) ) {
			foreach ( $items as $key => $item ) {
				if ( in_array( $item->ID, $current_menu_parents ) ) {
					$items[ $key ]->classes[] = 'current-menu-parent';
				}
			}
		}

		return $items;
	}

	/**
	 * Add audio metadata to attachment response objects.
	 *
	 * @since 2.0.0
	 *
	 * @param array $response Attachment data to send as JSON.
	 * @param WP_Post $attachment Attachment object.
	 * @param array $meta Attachment meta.
	 * @return array
	 */
	public function prepare_audio_attachment_for_js( $response, $attachment, $meta ) {
		if ( 'audio' !== $response['type'] ) {
			return $response;
		}

		$response['audiotheme'] = $meta;

		return $response;
	}

	/**
	 * Activate default archive setting fields.
	 *
	 * Themes will need to disable or override these settings.
	 *
	 * @since 2.0.0
	 *
	 * @param array $fields List of default fields to activate.
	 * @param string $post_type Post type archive.
	 * @return array
	 */
	public function default_archive_settings_fields( $fields, $post_type ) {
		if ( ! in_array( $post_type, array( 'audiotheme_record', 'audiotheme_video' ) ) ) {
			return $fields;
		}

		$fields['columns'] = array(
			'choices' => range( 3, 5 ),
			'default' => 4,
		);

		$fields['posts_per_archive_page'] = true;

		return $fields;
	}
}
