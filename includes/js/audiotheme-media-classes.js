(function( factory ) {
    'use strict';

	if ( 'function' === typeof define && define.amd ) {
        define( [ 'jquery' ], factory );
    } else if ( 'object' === typeof exports ) {
        module.exports = factory( require( 'jquery' ) );
    } else {
        factory( jQuery );
    }
}(function ( $ ) {
	'use strict';

	var $items = $([]);

	/**
	 * Convert element breakpoints to the expected format when initialized to
	 * keep the udpate method lightweight.
	 */
	function setupElement( el ) {
		var bp, data, i, type,
			$el = $( el ),
			settings = $el.data( 'audiothemeMediaClasses' ),
			breakpoints = [];

		if ( ! settings.breakpoints.length ) {
			return;
		}

		for ( i = 0; i < settings.breakpoints.length; i++ ) {
			bp = settings.breakpoints[ i ];

			// Convert stringified settings into objects.
			if ( 'string' === typeof bp ) {
				data = bp.trim().split( ':' );

				bp = {
					size: data[0],
					className: data[1] || null
				}
			}

			type = bp.type || 'min-width';

			breakpoints[ i ] = {
				type: type,
				size: parseInt( bp.size, 10 ),
				className: bp.className || type + '-' + bp.size
			};
		}

		settings.breakpoints = breakpoints;
		$el.data( 'audiothemeMediaSettings', settings );
	}

	function throttle( handler, threshold ) {
		var callback,
			doCallback = true;

		return function() {
			if ( doCallback ) {
				doCallback = false;
				setTimeout( function() {
					handler();
					doCallback = true;
				}, threshold );
			}
		};
	}

	/**
	 * Update media classes.
	 */
	function update( $items ) {
		$items.each(function() {
			var $el = $( this ),
				w = $el.outerWidth(),
				settings = $el.data( 'audiothemeMediaClasses' ),
				bp, i;

			if ( ! settings.breakpoints.length ) {
				return;
			}

			if ( 'number' !== typeof w ) {
				w = $el.width();
			}

			for ( i = 0; i < settings.breakpoints.length; i++ ) {
				bp = settings.breakpoints[ i ];
				$el.toggleClass( bp.className, 'min-width' === bp.type ? w >= bp.size : w <= bp.size );
			}
		});

		$items.removeClass( 'no-fouc' );
	}

	function updateAll() {
		update( $items );
	}

	function updateInAnimationFrame() {
		if ( window.requestAnimationFrame ) {
			requestAnimationFrame( updateAll );
		} else {
			updateAll();
		}
	}

	/**
	 * jQuery plugin interface to add element queries.
	 */
	$.fn.audiothemeMediaClasses = function( options ) {
		var settings = $.extend({
			breakpoints: []
		}, options );

		return this.each(function() {
			var $this = $( this );
			$this.data( 'audiothemeMediaClasses', settings );
			$items = $items.add( $this );
			setupElement( $this );
		});
	};

	$.fn.audiothemeMediaClasses.defaults = {
		resizeThreshold: 250
	};

	// Initialize elements with the data attribute.
	$( '[data-audiotheme-media-classes]' ).each(function() {
		var $this = $( this ),
			breakpoints = $this.data( 'audiotheme-media-classes' ).split( ',' );
		$this.audiothemeMediaClasses({ breakpoints: breakpoints });
	});

	// Initialize event listeners.
	$( window )
		.on( 'load.audiothemeMediaClasses onorientationchange.audiothemeMediaClasses', updateInAnimationFrame )
		.on( 'resize.audiothemeMediaClasses', throttle( updateInAnimationFrame, $.fn.audiothemeMediaClasses.defaults.resizeThreshold ) );

}));
