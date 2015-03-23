/*global _:false, _audiothemeGigEditSettings:false, _pikadayL10n:false, Backbone:false, isRtl:false, Pikaday:false, wp:false */

window.audiotheme = window.audiotheme || {};

(function( window, $, _, Backbone, wp, undefined ) {
	'use strict';

	var $date = $( '#gig-date' ),
		$time = $( '#gig-time' ),
		$venueTz = $( '#gig-venue-timezone' ),
		$venueTzSearch = $( '#gig-venue-timezone-search' ),
		ss = sessionStorage || {},
		lastGigDate = 'lastGigDate' in ss ? new Date( ss.lastGigDate ) : null,
		lastGigTime = 'lastGigTime' in ss ? new Date( ss.lastGigTime ) : null,
		settings = _audiothemeGigEditSettings;

	// Add a day to the last saved gig date.
	if ( lastGigDate ) {
		lastGigDate.setDate( lastGigDate.getDate() + 1 );
	}

	// Initialize the time picker.
	$time.timepicker({
		'scrollDefaultTime': lastGigTime || '',
		'timeFormat': settings.timeFormat,
		'className': 'ui-autocomplete'
	}).on( 'showTimepicker', function() {
		$( this ).addClass( 'open' );
		$( '.ui-timepicker-list' ).width( $( this ).outerWidth() );
	}) .on( 'hideTimepicker', function() {
		$( this ).removeClass( 'open' );
	}) .next().on( 'click', function() {
		$time.focus();
	});

	// Add the last saved date and time to session storage
	// when the gig is saved.
	$( '#publish' ).on( 'click', function() {
		var date = $date.datepicker( 'getDate' ),
			time = $time.timepicker( 'getTime' );

		if ( ss && '' !== date ) {
			ss.lastGigDate = date;
		}

		if ( ss && '' !== time ) {
			ss.lastGigTime = time;
		}
	});

	// Automcomplete the search for a city.
	$venueTzSearch.autocomplete({
		source: function( request, callback ) {
			$.ajax({
				url: 'http://api.wordpress.org/core/name-to-zoneinfo/1.0/',
				type: 'GET',
				data: {
					s: $venueTzSearch.val()
				},
				dataType: 'jsonp',
				jsonpCallback: 'dummyCallback'
			}).done(function( response ) {
				var data = $.map( response, function( item ) {
					return {
						label: item.name + ', ' + item.location + ' - ' + item.timezone,
						value: item.timezone,
						location: item.location,
						timezone: item.timezone
					};
				});

				callback( data );
			}).fail(function() {
				callback();
			});
		},
		minLength: 2,
		select: function( e, ui ) {
			$venueTz.find( 'option[value="' + ui.item.timezone + '"]' ).attr( 'selected','selected' );
		},
		position:  ( 'undefined' !== typeof isRtl && isRtl ) ? { my: 'right top', at: 'right bottom', offset: '0, -1' } : { offset: '0, -1' },
		open: function() { $( this ).addClass( 'open' ); },
		close: function() { $( this ).removeClass( 'open' ); }
	});

	// Initialize the date picker.
	new Pikaday({
		bound: false,
		container: document.getElementById( 'audiotheme-gig-start-date-picker' ),
		field: $( '.audiotheme-gig-date-picker-start' ).find( 'input' ).get( 0 ),
		format: 'YYYY/MM/DD',
		i18n: _pikadayL10n || {},
		isRTL: isRtl,
		theme: 'audiotheme-pikaday'
	});


	var frame, screen,
		app = window.audiotheme,
		$venueIdField = $( '#gig-venue-id' );

	app.view.GigVenueMetaBox = wp.media.View.extend({
		el: '#audiotheme-gig-venue-meta-box',

		initialize: function( options ) {
			this.controller = options.controller;
			this.controller.get( 'frame' ).on( 'open', this.updateSelection, this );
		},

		render: function() {
			this.views.add( '.audiotheme-meta-box-body', [
				new app.view.GigVenueDetails({
					controller: this.controller
				}),
				new app.view.GigVenueSelectButton({
					controller: this.controller
				})
			]);

			return this;
		},

		updateSelection: function() {
			var frame = this.controller.get( 'frame' ),
				venue = this.controller.get( 'venue' ),
				venues = frame.states.get( 'audiotheme-venues' ).get( 'venues' ),
				selection = frame.states.get( 'audiotheme-venues' ).get( 'selection' );

			if ( venue.get( 'ID' ) ) {
				venues.add( venue, { at: 0 });
				selection.reset( venue );
			}
		}
	});

	/**
	 *
	 *
	 * @todo Need to refresh these details if a venue is edited in the modal.
	 */
	app.view.GigVenueDetails = wp.media.View.extend({
		className: 'audiotheme-gig-venue-details',
		template: wp.template( 'audiotheme-gig-venue-details' ),

		initialize: function( options ) {
			this.listenTo( this.controller, 'change:venue', this.render );
			this.listenTo( this.controller.get( 'venue' ), 'change', this.render );
		},

		render: function() {
			var data, model = this.controller.get( 'venue' );

			if ( model.get( 'ID' ) ) {
				data = _.extend( model.toJSON(), app.templateHelpers );
				this.$el.html( this.template( data ) );
			} else {
				this.$el.empty();
			}

			return this;
		}
	});

	app.view.GigVenueSelectButton = wp.media.View.extend({
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

	// Initialize the venue frame.
	frame = new app.view.VenueFrame({
		title: 'Venues',
		button: {
			text: 'Select Venue'
		}
	});

	// Refresh venue in case data was edited in the modal.
	frame.on( 'close', function() {
		screen.get( 'venue' ).fetch();
	});

	frame.on( 'insert', function( selection ) {
		screen.set( 'venue', selection.first() );
		$venueIdField.val( selection.first().get( 'ID' ) );
	});

	screen = new Backbone.Model({
		frame: frame,
		venue: new app.model.Venue( _audiothemeGigEditSettings.venue || {} )
	});

	new app.view.GigVenueMetaBox({
		controller: screen
	}).render();

})( window, jQuery, _, Backbone, wp );
