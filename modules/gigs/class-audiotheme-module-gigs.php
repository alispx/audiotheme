<?php

class AudioTheme_Module_Gigs extends Audiotheme_Module {
	/**
	 *
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args( array(
			'module_id'          => 'gigs',
			'module_name'        => __( 'Gigs', 'audiotheme' ),
			'module_description' => __( '', 'audiotheme' ),
			'is_core_module'     => true,
			'is_togglable'       => true,
			'admin_menu_id'      => 'toplevel_page_audiotheme-gigs',
		), $args );

		parent::__construct( $args );
	}

	public function load() {
		add_action( 'init', 'audiotheme_gigs_init' );

		if ( is_admin() ) {
			$this->load_admin();
		} else {

		}
	}

	public function load_admin() {
		add_action( 'init', 'audiotheme_gigs_admin_setup' );
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
		return get_option( 'audiotheme_gig_rewrite_base', 'shows' );
	}
}
