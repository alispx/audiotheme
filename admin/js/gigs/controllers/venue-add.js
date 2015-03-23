var VenueAddController,
	Venue = require( '../models/venue' ),
	wp = require( 'wp' );

VenueAddController = wp.media.controller.State.extend({
	defaults: {
		id:      'audiotheme-venue-add',
		menu:    'audiotheme-venues',
		content: 'audiotheme-venue-add',
		toolbar: 'audiotheme-venue-add',
		title:   'Add New Venue',
		button:  {
			text: 'Save'
		},
		menuItem: {
			text: 'Add a Venue',
			priority: 20
		}
	},

	initialize: function() {
		this.set( 'model', new Venue() );
	}
});

module.exports = VenueAddController;
