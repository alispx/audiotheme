<?php

class AudioTheme_Module_Discography extends Audiotheme_Module {
	/**
	 *
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args( array(
			'module_id'          => 'discography',
			'module_name'        => __( 'Discography', 'audiotheme' ),
			'module_description' => __( '', 'audiotheme' ),
			'is_core_module'     => true,
			'is_togglable'       => true,
			'admin_menu_id'      => 'toplevel_page_edit-post_type-audiotheme_record',
		), $args );

		parent::__construct( $args );
	}

	public function load() {
		add_action( 'init', 'audiotheme_discography_init' );

		if ( is_admin() ) {
			$this->load_admin();
		} else {

		}
	}

	public function load_admin() {
		add_action( 'init', 'audiotheme_load_discography_admin' );
	}

	public function register_post_types() {

	}

	/**
	 * Get the videos rewrite base. Defaults to 'videos'.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_rewrite_base() {
		return get_option( 'audiotheme_record_rewrite_base', 'music' );
	}
}
