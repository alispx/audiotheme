<?php
/**
 * Loads the Posts to Posts library and dependencies.
 *
 * @package AudioTheme
 * @since 1.0.0
 *
 * @link https://github.com/scribu/wp-lib-posts-to-posts
 */

/**
 * Attach hook to load the Posts to Posts core.
 *
 * This doesn't actually occur during the init hook despite the name.
 *
 * @since 1.0.0
 */
function audiotheme_p2p_init() {
	add_action( 'plugins_loaded', 'audiotheme_p2p_load_core', 20 );
	register_uninstall_hook( AUDIOTHEME_DIR . 'audiotheme.php', array( 'P2P_Storage', 'uninstall' ) );
}
scb_init( 'audiotheme_p2p_init' );

/**
 * Load Posts 2 Posts core.
 *
 * Posts 2 Posts requires two custom database tables to store post
 * relationships and relationship metadata. If an alternative version of the
 * library doesn't exist, the tables are created on admin_init.
 *
 * @since 1.0.0
 */
function audiotheme_p2p_load_core() {
	if ( ! defined( 'P2P_TEXTDOMAIN' ) ) {
		define( 'P2P_TEXTDOMAIN', 'audiotheme' );
	}

	P2P_Storage::init();
	P2P_Query_Post::init();
	P2P_Query_User::init();
	P2P_URL_Query::init();
	P2P_Widget::init();
	P2P_Shortcodes::init();
}
