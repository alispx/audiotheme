<?php
/**
 * Venue model.
 *
 * @package AudioTheme\Core\Gigs
 * @since 2.0.0
 */

namespace AudioTheme\Core\Model;

use AudioTheme\Core\Model\AbstractPost;

/**
 * Venue model class.
 *
 * @package AudioTheme\Core\Gigs
 * @since 2.0.0
 */
class Venue extends AbstractPost {
	/**
	 * Venue name.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	public $name = '';

	/**
	 * Address.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	public $address = '';

	/**
	 * City.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	public $city = '';

	/**
	 * State/region.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	public $state = '';

	/**
	 * Postal code.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	public $postal_code = '';

	/**
	 * Country.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	public $country = '';

	/**
	 * Website URL.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	public $website = '';

	/**
	 * Phone number.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	public $phone = '';

	/**
	 * Timezone identifier.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	public $timezone_string = '';

	/**
	 * Contact name.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	public $contact_name = '';

	/**
	 * Contact phone number.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	public $contact_phone = '';

	/**
	 * Contact email address.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	public $contact_email = '';

	/**
	 * Notes
	 *
	 * @since 2.0.0
	 * @type string
	 */
	public $notes = '';

	/**
	 * Post type name.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	protected $post_type = 'audiotheme_venue';

	/**
	 * Constructor method.
	 *
	 * Intializes the model attributes from post meta.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $post CPT slug, post ID or post object.
	 */
	public function __construct( $post = 0 ) {
		parent::__construct( $post );

		if ( empty( $this->post ) ) {
			return;
		}

		$this->name = $this->post->post_title;
	}
}
