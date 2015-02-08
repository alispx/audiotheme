<?php
/**
 * WP_Query for gigs.
 *
 * @package AudioTheme\Query
 * @since 2.0.0
 */

namespace AudioTheme\Query;

/**
 * Class to extend WP_Query and set default arguments when querying for gigs.
 *
 * @link http://bradt.ca/blog/extending-wp_query/
 *
 * @package AudioTheme\Query
 * @since 1.0.0
 */
class Gigs extends \WP_Query {
	/**
	 * Run the query and cache connected venues.
	 *
	 * @since 1.0.0
	 *
	 * @uses p2p_type()
	 *
	 * @param array $args WP_Query args.
	 */
	public function __construct( $args = array() ) {
		$args = $this->parse_args( $args );
		$args['post_type'] = 'audiotheme_gig';
		parent::__construct( $args );
		p2p_type( 'audiotheme_venue_to_gig' )->each_connected( $this );
	}

	/**
	 * Parse the query args and set defaults.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args WP_Query args.
	 * @return array
	 */
	protected function parse_args( $args = array() ) {
		$defaults = array(
			'post_status'         => 'publish',
			'meta_key'            => '_audiotheme_gig_datetime',
			'orderby'             => 'meta_value',
			'order'               => 'asc',
			'ignore_sticky_posts' => true,
		);

		if ( isset( $args['audiotheme_preset'] ) && 'recent' == $args['audiotheme_preset'] ) {
			$defaults['meta_query'] = array(
				array(
					'key'     => '_audiotheme_gig_datetime',
					'value'   => current_time( 'mysql' ),
					'compare' => '<=',
					'type'    => 'DATETIME',
				),
			);

			$defaults['order'] = 'desc';
		} else {
			$defaults['meta_query'] = array(
				array(
					'key'     => '_audiotheme_gig_datetime',
					'value'   => date( 'Y-m-d', current_time( 'timestamp' ) ),
					'compare' => '>=',
					'type'    => 'DATETIME',
				),
			);
		}

		$args = wp_parse_args( $args, $defaults );

		return apply_filters( 'audiotheme_gig_query_args', $args );
	}
}
