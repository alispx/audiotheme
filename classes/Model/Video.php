<?php
/**
 * Video model.
 *
 * @package AudioTheme\Core\Videos
 * @since 2.0.0
 */

namespace AudioTheme\Core\Model;

use AudioTheme\Core\Model\AbstractPost;

/**
 * Video model class.
 *
 * @package AudioTheme\Core\Videos
 * @since 2.0.0
 */
class Video extends AbstractPost {
	/**
	 * Post type name.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	protected $post_type = 'audiotheme_video';

	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $post CPT slug, post ID or post object.
	 */
	public function __construct( $post = null ) {
		parent::__construct( $post );
	}

	/**
	 * Convert to the embed HTML.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->get_html();
	}

	/**
	 * Retrieve the video embed HTMl.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Optional. (width, height)
	 * @param array $query_args Optional. Provider specific parameters.
	 * @return string Video HTML
	 */
	public function get_html( $args = array(), $query_args = array() ) {
		global $wp_embed;

		$html      = '';
		$video_url = $this->get_url();

		if ( ! empty( $video_url ) ) {
			// Save current embed settings and restore them after running the shortcode.
			$restore_post_id       = $wp_embed->post_ID;
			$restore_false_on_fail = $wp_embed->return_false_on_fail;
			$restore_linkifunknown = $wp_embed->linkifunknown;
			$restore_usecache      = $wp_embed->usecache;

			// Can't be sure what the embed settings are, so explicitly set them.
			$wp_embed->post_ID              = $this->ID;
			$wp_embed->return_false_on_fail = true;
			$wp_embed->linkifunknown        = false;
			$wp_embed->usecache             = true;

			$html = $wp_embed->shortcode( $args, add_query_arg( $query_args, $video_url ) );

			// Restore original embed settings.
			$wp_embed->post_ID              = $restore_post_id;
			$wp_embed->return_false_on_fail = $restore_false_on_fail;
			$wp_embed->linkifunknown        = $restore_linkifunknown;
			$wp_embed->usecache             = $restore_usecache;
		}

		if ( false !== strpos( $html, '[video' ) ) {
			$html = do_shortcode( $html );
		}

		return apply_filters( 'audiotheme_video_html', $html, $this->ID, $video_url, $args, $query_args );
	}

	/**
	 * Retrieve the video URL.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_url() {
		return $this->_audiotheme_video_url;
	}
}
