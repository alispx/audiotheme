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

	$module_id = $_POST['module'];

	check_ajax_referer( 'toggle-module_' . $module_id, 'nonce' );

	$modules = audiotheme()->modules;
	$module  = $modules->get( $module_id );

	if ( $module->is_active() ) {
		$modules->deactivate( $module_id );
	} else {
		$modules->activate( $module_id );
	}

	wp_send_json_success( array(
		'isActive'    => $module->is_active(),
		'adminMenuId' => $module->admin_menu_id,
	) );
}
