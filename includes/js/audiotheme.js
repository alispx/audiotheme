/*global AudiothemeTracks:false */

(function( window, $, undefined ) {
	'use strict';

	$( document ).ready(function() {
		var $tracklist = $( '.audiotheme-tracklist-section' );

		if ( $tracklist.length && $.isFunction( $.fn.cuePlaylist ) ) {
			$tracklist.cuePlaylist({
				cuePlaylistTracks: AudiothemeTracks.record,
				cueSelectors: {
					playlist: '.audiotheme-tracklist-section',
					track: '.audiotheme-track',
					trackCurrentTime: '.jp-current-time',
					tracklist: '.audiotheme-tracklist'
				},
				features: ['cueplaylist']
			});
		}

		$( '.js-media-classes' ).audiothemeMediaClasses({
			breakpoints: [ 600, 400 ]
		});
	});

})( this, jQuery );
