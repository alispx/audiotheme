<?php
/**
 * Environment compatibility checks and notices.
 *
 * @package AudioTheme
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Display an admin notice when the current verison of PHP is unsupported.
 *
 * @since 2.0.0
 */
function audiotheme_php_compatibility_notice() {
	$plugin_rel_path = dirname( plugin_basename( __FILE__ ) ) . '/languages';
	load_plugin_textdomain( 'audiotheme', false, $plugin_rel_path );
	?>
	<style type="text/css">#audiotheme-required-notice { display: none;}</style>
	<div id="audiotheme-php-compatibility-notice" class="error">
		<p>
			<?php
			printf(
				__( 'AudioTheme requires PHP 5.3 or later to run. Your current version is %s.', 'audiotheme' ),
				phpversion()
			);
			?>
			<a href=""><?php _e( 'Learn more.', 'audiotheme' ); ?></a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'audiotheme_php_compatibility_notice' );
