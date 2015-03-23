var VenueEditForm,
	$ = require( 'jquery' ),
	wp = require( 'wp' );

VenueEditForm = wp.media.View.extend({
	tagName: 'div',
	className: 'audiotheme-venue-edit-form',
	template: wp.template( 'audiotheme-venue-edit-form' ),

	events: {
		'change [data-setting]': 'updateAttribute'
	},

	initialize: function( options ) {
		this.model = options.model;
		this.$spinner = $( '<span class="spinner"></span>' );
	},

	render: function() {
		var tzString = this.model.get( 'timezone_string' );

		this.$el.html( this.template( this.model.toJSON() ) );

		if ( tzString ) {
			this.$el.find( '#venue-timezone-string' ).find( 'option[value="' + tzString + '"]' ).prop( 'selected', true );
		}
		return this;
	},

	/**
	 * Update a model attribute when a field is changed.
	 *
	 * Fields with a 'data-setting="{{key}}"' attribute whose value
	 * corresponds to a model attribute will be automatically synced.
	 *
	 * @param {Object} e Event object.
	 */
	updateAttribute: function( e ) {
		var $target = $( e.target ),
			attribute = $target.data( 'setting' ),
			value = e.target.value,
			$spinner = this.$spinner;

		if ( this.model.get( attribute ) !== value ) {
			$spinner.insertAfter( $target ).show();

			this.model.set( attribute, value ).save().always(function() {
				$spinner.hide();
			});
		}
	}
});

module.exports = VenueEditForm;
