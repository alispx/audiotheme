/*global _:false, _audiothemeGigEditSettings:false, _pikadayL10n:false, ajaxurl:false, isRtl:false, Pikaday:false, wp:false */

(function( window, $, _, wp, undefined ) {
	'use strict';

	var $date = $( '#gig-date' ),
		$time = $( '#gig-time' ),
		$venue = $( '#gig-venue' ),
		$venueTzGroup = $( '#gig-venue-timezone-group' ),
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

	// Autocomplete venue names.
	// If the venue is new, show the time zone selection ui.
	$venue.autocomplete({
		change: function() {
			$venueTzGroup.hide();

			if ( '' !== $venue.val() ) {
				wp.ajax.send( 'audiotheme_ajax_is_new_venue', {
					type: 'GET',
					data: {
						name: $venue.val()
					}
				}).done(function( response ) {
					$venueTzGroup.toggle( response );
				});
			}
		},
		select: function() { $venueTzGroup.hide(); },
		source: ajaxurl + '?action=audiotheme_ajax_get_venue_matches',
		minLength: 0,
		position:  ( 'undefined' !== typeof isRtl && isRtl ) ? { my: 'right top', at: 'right bottom', offset: '0, -1' } : { offset: '0, -1' },
		open: function() { $( this ).addClass( 'open' ); },
		close: function() { $( this ).removeClass( 'open' ); }
	});

	$( '#gig-venue-select' ).on( 'click', function() {
		$venue.focus().autocomplete( 'search', '' );
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
		format: 'YYYY-MM-DD',
		i18n: _pikadayL10n || {},
		isRTL: isRtl,
		theme: 'audiotheme-pikaday'
	});

})( window, jQuery, _, wp );
