(function( window, $, undefined ) {
	'use strict';

	$( document ).ready(function() {
		$( '.audiotheme-gigs, .audiotheme-gig-single, .audiotheme-records, .audiotheme-record-single, .audiotheme-videos' ).audiothemeMediaClasses({
			breakpoints: [600, 400]
		});
	});

})( this, jQuery );
