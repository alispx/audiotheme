/*global _:false, wp:false */

(function( window, $, _, wp, undefined ) {
	'use strict';

	var $city = $( '#venue-city' ),
		$state = $( '#venue-state' ),
		$country = $( '#venue-country' ),
		$timezone = $( '#venue-timezone-string' );

	function searchPlace( q ) {
		return $.ajax({
			url: 'http://api.geonames.org/search',
			data: {
				name_startsWith: q,
				featureClass: 'P',
				maxRows: 12,
				style: 'FULL',
				type: 'json'
			},
			dataType: 'jsonp'
		});
	}

	$city.autocomplete({
		source: function( request, callback ) {
			searchPlace( request.term )
				.done(function( response ) {
					var data;

					if ( 'geonames' in response ) {
						data = $.map( response.geonames, function( item ) {
							return {
								label: item.name + ( item.adminName1 ? ', ' + item.adminName1 : '' ) + ', ' + item.countryName,
								value: item.name,
								adminCode: item.adminCode1,
								countryName: item.countryName,
								timezone: item.timezone.timeZoneId
							};
						});
					}

					callback( data );
				})
				.fail(function() {
					callback();
				});
		},
		minLength: 2,
		select: function( e, ui ) {
			if ( '' === $state.val() ) {
				$state.val( ui.item.adminCode );
			}

			if ( '' === $country.val() ) {
				$country.val( ui.item.countryName );
			}

			$timezone.find( 'option[value="' + ui.item.timezone + '"]' ).attr( 'selected','selected' );
		}
	});

})( window, jQuery, _, wp );
