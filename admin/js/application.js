/*jshint node:true */

if ( global.audiotheme ) {
	return module.exports = global.audiotheme;
}

var _ = require( 'underscore' );

function Application() {
	var settings = {};

	_.extend( this, {
		controller: {},
		l10n: {},
		model: {},
		view: {}
	});

	this.settings = function( options ) {
		options && _.extend( settings, options );

		if ( settings.l10n ) {
			this.l10n = _.extend( this.l10n, settings.l10n );
			delete settings.l10n;
		}

		return settings || {};
	};
}

global.audiotheme = module.exports = new Application();
