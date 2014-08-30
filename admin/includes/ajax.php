<?php
/**
 *
 */

/**
 * AJAX callback to toggle a module's status.
 *
 * @since 2.0.0
 */
function audiotheme_ajax_toggle_module() {
	if ( empty( $_POST['module'] ) ) {
		wp_send_json_error();
	}

	$module = $_POST['module'];

	check_ajax_referer( 'toggle-module_' . $module, 'nonce' );

	$is_module_active = audiotheme_is_module_active( $module );

	if ( $is_module_active ) {
		audiotheme_deactivate_module( $module );
	} else {
		audiotheme_activate_module( $module );
	}

	wp_send_json_success( array(
		'isActive' => ! $is_module_active,
		'module'   => audiotheme_get_module( $module ),
	) );
}
