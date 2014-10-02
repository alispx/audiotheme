<?php
// Determine where the WordPress Unit Tests are located.
$wp_tests_dir = getenv( 'WP_TESTS_DIR' );

require_once( $wp_tests_dir . '/includes/functions.php' );

function _manually_load_plugin() {
	require( dirname( dirname( __FILE__ ) ) . '/audiotheme.php' );
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require( $wp_tests_dir . '/includes/bootstrap.php' );
