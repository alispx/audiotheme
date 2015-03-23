var GigVenueSelectButton,
	wp = require( 'wp' );

GigVenueSelectButton = wp.media.View.extend({
	className: 'button',
	tagName: 'button',

	events: {
		'click': 'openModal'
	},

	render: function() {
		this.$el.text( 'Select Venue' );
		return this;
	},

	openModal: function( e ) {
		e.preventDefault();
		this.controller.get( 'frame' ).open();
	}
});

module.exports = GigVenueSelectButton;
