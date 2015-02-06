<?php

namespace AudioTheme\Template;

/**
 * Template loader.
 *
 * Based off version 1.1.0 of the Gamajo Template Loader by Gary Jones. Changes
 * allow for overriding class properties during instantiation and adding a
 * load_template() method that accepts arbitrary data to extract into the local
 * template scope.
 *
 * @package AudioTheme\Template
 * @since 2.0.0
 * @author Gary Jones
 * @author Brady Vercher
 * @link http://github.com/GaryJones/Gamajo-Template-Loader
 * @license GPL-2.0+
 */

/**
 * Template loader class.
 *
 * @package AudioTheme\Template
 * @since 2.0.0
 * @author Gary Jones
 * @author Brady Vercher
 */
class Loader extends \Gamajo_Template_Loader {
	/**
	 * Prefix for filter names.
	 *
	 * @since 2.0.0
	 *
	 * @type string
	 */
	protected $filter_prefix = 'audiotheme';

	/**
	 * Directory name where custom templates for this plugin should be found in
	 * the theme.
	 *
	 * @since 2.0.0
	 *
	 * @type string
	 */
	protected $theme_template_directory = 'audiotheme';

	/**
	 * Reference to the root directory path of this plugin.
	 *
	 * @since 2.0.0
	 *
	 * @type string
	 */
	protected $plugin_directory = AUDIOTHEME_DIR;

	/**
	 * Load a template file.
	 *
	 * @since 2.0.0
	 *
	 * @param string $template_file Absolute path to a file or list of template parts.
	 * @param array  $data          Optional. List of variables to extract into the template scope.
	 */
	public function load_template( $template_file, $data = array() ) {
		global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

		if ( is_array( $wp_query->query_vars ) ) {
			extract( $wp_query->query_vars, EXTR_SKIP );
		}

		if ( is_array( $data ) && ! empty( $data ) ) {
			extract( $data, EXTR_OVERWRITE );
			unset( $data );
		}

		if ( file_exists( $template_file ) ) {
			require( $template_file );
		}
	}
}
