<?php
/**
 * Admin AJAX actions.
 *
 * @package AudioTheme\Core
 * @since 2.0.0
 */

namespace AudioTheme\Core\Provider\Ajax;

use AudioTheme\Core\HookProviderInterface;
use AudioTheme\Core\Plugin;

/**
 * Admin AJAX actions class.
 *
 * @package AudioTheme\Core
 * @since   2.0.0
 */
class AdminAjax implements HookProviderInterface {
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
	public function register_hooks( Plugin $plugin ) {
		$this->plugin = $plugin;

		add_action( 'wp_ajax_audiotheme_ajax_toggle_module', array( $this, 'toggle_module' ) );
		add_action( 'wp_ajax_audiotheme_ajax_insert_term',   array( $this, 'insert_term' ) );
	}

	/**
	 * Toggle a module's status.
	 *
	 * @since 2.0.0
	 */
	public function toggle_module() {
		if ( empty( $_POST['module'] ) ) {
			wp_send_json_error();
		}

		$module_id = $_POST['module'];

		check_ajax_referer( 'toggle-module_' . $module_id, 'nonce' );

		$modules = audiotheme( 'modules' );
		$module  = $modules[ $module_id ];

		if ( $module->is_core() && $modules->is_active( $module_id ) ) {
			$modules->deactivate( $module_id );
		} else {
			$modules->activate( $module_id );
		}

		wp_send_json_success( array(
			'isActive'    => $modules->is_active( $module_id ),
			'adminMenuId' => $module->admin_menu_id,
		) );
	}

	/**
	 * AJAX callback to insert a new term.
	 *
	 * @since 1.0.0
	 */
	public function insert_term() {
		$response       = array();
		$taxonomy       = $_POST['taxonomy'];
		$is_valid_nonce = isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'add-term_' . $taxonomy );

		if ( ! $is_valid_nonce ) {
			$response['message'] = __( 'Unauthorized request.', 'audiotheme' );
			wp_send_json_error( $response );
		}

		$term      = empty( $_POST['term'] ) ? '' : $_POST['term'];
		$term_data = wp_insert_term( $term, $taxonomy );

		if ( is_wp_error( $term_data ) ) {
			$response['message'] = $term_data->get_error_message();
			wp_send_json_error( $response );
		}

		$response['html'] = sprintf(
			'<li><label><input type="checkbox" name="audiotheme_record_types[]" value="%d" checked="checked"> %s</label></li>',
			absint( $term_data['term_id'] ),
			$term
		);

		wp_send_json_success( $response );
	}
}
