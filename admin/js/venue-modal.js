/*global _:false, _audiothemeVenueModalSettings:false, Backbone:false, wp:false */

window.audiotheme = window.audiotheme || {};

(function( window, $, _, Backbone, wp, undefined ) {
	'use strict';

	var l10n,
		app = window.audiotheme,
		settings = _audiothemeVenueModalSettings;

	l10n = settings.l10n;
	delete settings.l10n;

	_.extend( app, { model: {}, view: {}, controller: {} } );


	/**
	 * ========================================================================
	 * MODELS
	 * ========================================================================
	 */

	app.model.Venue = Backbone.Model.extend({
		idAttribute: 'ID',

		defaults: {
			ID: null,
			name: '',
			address: '',
			city: '',
			state: '',
			postal_code: '',
			country: '',
			website: '',
			phone: '',
			timezone_string: ''
		},

		sync: function( method, model, options ) {
			options = options || {};
			options.context = this;

			if ( 'create' === method ) {
				if ( ! settings.canPublishVenues || ! settings.insertVenueNonce ) {
					return $.Deferred().rejectWith( this ).promise();
				}

				options.data = _.extend( options.data || {}, {
					action: 'audiotheme_ajax_save_venue',
					model: model.toJSON(),
					nonce: settings.insertVenueNonce
				});

				return wp.ajax.send( options );
			}

			// If the attachment does not yet have an `ID`, return an instantly
			// rejected promise. Otherwise, all of our requests will fail.
			if ( _.isUndefined( this.id ) ) {
				return $.Deferred().rejectWith( this ).promise();
			}

			// Overload the `read` request so Venue.fetch() functions correctly.
			if ( 'read' === method ) {
				options.data = _.extend( options.data || {}, {
					action: 'audiotheme_ajax_get_venue',
					ID: this.id
				});
				return wp.ajax.send( options );
			}

			else if ( 'update' === method ) {
				// If we do not have the necessary nonce, fail immeditately.
				if ( ! this.get( 'nonces' ) || ! this.get( 'nonces' ).update ) {
					return $.Deferred().rejectWith( this ).promise();
				}

				// Set the action and ID.
				options.data = _.extend( options.data || {}, {
					action: 'audiotheme_ajax_save_venue',
					nonce: this.get( 'nonces' ).update
				});

				// Record the values of the changed attributes.
				if ( model.hasChanged() ) {
					options.data.model = model.changed;
					options.data.model.ID = this.id;
				}

				return wp.ajax.send( options );
			}
		}
	});

	app.model.Venues = Backbone.Collection.extend({
		model: app.model.Venue,

		comparator: function( model ) {
			return model.get( 'name' );
		}
	});

	app.model.VenuesQuery = app.model.Venues.extend({
		initialize: function( models, options ) {
			options = options || {};
			app.model.Venues.prototype.initialize.apply( this, arguments );

			this.props = new Backbone.Model();
			this.props.set( _.defaults( options.props || {} ) );
			this.props.on( 'change', this.requery, this );

			this.args = _.extend( {}, {
				posts_per_page: 20
			}, options.args || {} );

			this._hasMore = true;
		},

		hasMore: function() {
			return this._hasMore;
		},

		/**
		 * Fetch more venues from the server for the collection.
		 *
		 * @param   {object}  [options={}]
		 * @returns {Promise}
		 */
		more: function( options ) {
			var query = this;

			// If there is already a request pending, return early with the Deferred object.
			if ( this._more && 'pending' === this._more.state() ) {
				return this._more;
			}

			if ( ! this.hasMore() ) {
				return $.Deferred().resolveWith( this ).promise();
			}

			options = options || {};
			options.remove = false;

			return this._more = this.fetch( options ).done(function( response ) {
				if ( _.isEmpty( response ) || -1 === this.args.posts_per_page || response.length < this.args.posts_per_page ) {
					query._hasMore = false;
				}
			});
		},

		observe: function( collection ) {
			var self = this;

			collection.on( 'change', function( model ) {
				self.set( model, { add: false, remove: false });
			});
		},

		requery: function() {
			this._hasMore = true;
			this.args.paged = 1;
			this.fetch({ reset: true });
		},

		/**
		 * Overrides Backbone.Collection.sync
		 *
		 * @param {String} method
		 * @param {Backbone.Model} model
		 * @param {Object} [options={}]
		 * @returns {Promise}
		 */
		sync: function( method, model, options ) {
			var args, fallback;

			// Overload the read method so VenuesQuery.fetch() functions correctly.
			if ( 'read' === method ) {
				options = options || {};
				options.context = this;

				options.data = _.extend( options.data || {}, {
					action: 'audiotheme_ajax_get_venues'
				});

				args = _.clone( this.args );

				if ( this.props.get( 's' ) ) {
					args.s = this.props.get( 's' );
				}

				// Determine which page to query.
				if ( -1 !== args.posts_per_page ) {
					args.paged = Math.floor( this.length / args.posts_per_page ) + 1;
				}

				options.data.query_args = args;
				return wp.ajax.send( options );
			}

			// Otherwise, fall back to Backbone.sync()
			else {
				fallback = app.model.Venues.prototype.sync ? app.model.Venues.prototype : Backbone;
				return fallback.sync.apply( this, arguments );
			}
		}
	});


	/**
	 * ========================================================================
	 * CONTROLLERS
	 * ========================================================================
	 */

	/**
	 *
	 */
	app.controller.Venues = wp.media.controller.State.extend({
		defaults: {
			id:      'audiotheme-venues',
			menu:    'audiotheme-venues',
			content: 'audiotheme-venues',
			toolbar: 'main-audiotheme-venues',
			title:   'Venues',
			button:  {
				text: 'Select'
			},
			menuItem: {
				text: 'Manage Venues',
				priority: 10
			},
			mode: 'view',
			provider: 'venues'
		},

		initialize: function() {
			var search = new app.model.VenuesQuery({}, { props: { s: '' } }),
				venues = new app.model.VenuesQuery;

			this.set( 'search', search );
			this.set( 'venues', venues );
			this.set( 'selection', new app.model.Venues );

			// Synchronize changes to models in each collection.
			search.observe( venues );
			venues.observe( search );
		},

		search: function( query ) {
			// Restore the original state if the text in the search field
			// is less than 3 characters.
			if ( query.length < 3 ) {
				this.get( 'search' ).reset();
				this.set( 'provider', 'venues' );
				return;
			}

			this.set( 'provider', 'search' );
			this.get( 'search' ).props.set( 's', query );
		}
	});

	/**
	 *
	 */
	app.controller.VenueAdd = wp.media.controller.State.extend({
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
			this.set( 'model', new app.model.Venue() );
		}
	});


	/**
	 * ========================================================================
	 * VIEWS
	 * ========================================================================
	 */

	/**
	 *
	 */
	app.view.Frame = wp.media.view.Frame.extend({
		className: 'media-frame',
		template: wp.media.template( 'media-frame' ),
		regions: ['menu', 'title', 'content', 'toolbar'],

		initialize: function() {
			wp.media.view.Frame.prototype.initialize.apply( this, arguments );

			_.defaults( this.options, {
				title: '',
				modal: true
			});

			// Ensure core UI is enabled.
			this.$el.addClass( 'wp-core-ui' );

			// Initialize modal container view.
			if ( this.options.modal ) {
				this.modal = new wp.media.view.Modal({
					controller: this,
					title:      this.options.title
				});

				this.modal.content( this );
			}

			this.on( 'attach', _.bind( this.views.ready, this.views ), this );

			// Bind default title creation.
			this.on( 'title:create:default', this.createTitle, this );
			this.title.mode( 'default' );

			this.on( 'menu:create:audiotheme-venues', this.createMenu, this );
		},

		render: function() {
			// Activate the default state if no active state exists.
			if ( ! this.state() && this.options.state ) {
				this.setState( this.options.state );
			}
			// Call 'render' directly on the parent class.
			return wp.media.view.Frame.prototype.render.apply( this, arguments );
		},

		createTitle: function( title ) {
			title.view = new wp.media.View({
				controller: this,
				tagName: 'h1'
			});
		},

		createMenu: function( menu ) {
			menu.view = new wp.media.view.Menu({
				controller: this
			});
		},

		createToolbar: function( toolbar ) {
			toolbar.view = new wp.media.view.Toolbar({
				controller: this
			});
		}
	});

	// Map some of the modal's methods to the frame.
	_.each(['open','close','attach','detach','escape'], function( method ) {
		/**
		 * @returns {wp.media.view.VenueFrame} Returns itself to allow chaining.
		 */
		app.view.Frame.prototype[ method ] = function() {
			if ( this.modal ) {
				this.modal[ method ].apply( this.modal, arguments );
			}
			return this;
		};
	});

	/**
	 *
	 */
	app.view.VenueFrame = app.view.Frame.extend({
		className: 'media-frame audiotheme-venue-frame',

		initialize: function() {
			app.view.Frame.prototype.initialize.apply( this, arguments );

			_.defaults( this.options, {
				title: '',
				modal: true,
				state: 'audiotheme-venues'
			});

			this.createStates();
			this.bindHandlers();
		},

		createStates: function() {
			this.states.add( new app.controller.Venues );

			if ( settings.canPublishVenues ) {
				this.states.add( new app.controller.VenueAdd );
			}
		},

		bindHandlers: function() {
			this.on( 'content:create:audiotheme-venues', this.createContent, this );
			this.on( 'toolbar:create:main-audiotheme-venues', this.createSelectToolbar, this );
			this.on( 'toolbar:create:audiotheme-venue-add', this.createAddToolbar, this );
			this.on( 'content:render:audiotheme-venue-add', this.renderAddContent, this );
		},

		createContent: function( contentRegion ) {
			contentRegion.view = new app.view.VenuesContent({
				controller: this,
				collection: this.state().get( 'venues' ),
				searchQuery: this.state().get( 'search' )
			});
		},

		createSelectToolbar: function( toolbar ) {
			toolbar.view = new app.view.VenueSelectToolbar({
				controller: this
			});
		},

		createAddToolbar: function( toolbar ) {
			toolbar.view = new app.view.VenueAddToolbar({
				controller: this,
				model: this.state( 'audiotheme-venue-add' ).get( 'model' )
			});
		},

		renderAddContent: function() {
			this.content.set( new app.view.VenueAddContent({
				controller: this
			}) );
		}
	});

	/**
	 *
	 */
	app.view.VenuesContent = wp.media.View.extend({
		className: 'audiotheme-venue-frame-content',

		initialize: function( options ) {
			var selection = this.controller.state( 'audiotheme-venues' ).get( 'selection' );

			if ( ! this.collection.length ) {
				this.collection.fetch().done(function() {
					if ( ! selection.length ) {
						selection.reset( view.collection.first() );
					}
				});
			}
		},

		render: function() {
			this.views.add([
				new app.view.VenuesSearch({
					controller: this.controller
				}),
				new app.view.VenuesList({
					controller: this.controller,
					collection: this.collection
				}),
				new app.view.VenuePanel({
					controller: this.controller
				})
			]);

			return this;
		}
	});

	/**
	 *
	 */
	app.view.VenueAddContent = wp.media.View.extend({
		className: 'audiotheme-venue-frame-content audiotheme-venue-frame-content--add',

		render: function() {
			this.views.add([
				new app.view.VenueAddForm({
					controller: this.controller,
					model: this.controller.state( 'audiotheme-venue-add' ).get( 'model' )
				})
			]);
			return this;
		}
	});

	/**
	 *
	 */
	app.view.VenuesSearch = wp.media.View.extend({
		tagName: 'div',
		className: 'audiotheme-venues-search',
		template: wp.template( 'audiotheme-venues-search-field' ),

		events: {
			'keyup input': 'search',
			'search input': 'search'
		},

		render: function() {
			this.$field = this.$el.html( this.template() ).find( 'input' );
			return this;
		},

		search: function() {
			var view = this;

			clearTimeout( this.timeout );
			this.timeout = setTimeout(function() {
				view.controller.state().search( view.$field.val() );
			}, 300 );
		}
	});

	/**
	 *
	 *
	 * @todo Show feedback (spinner) when searching.
	 */
	app.view.VenuesList = wp.media.View.extend({
		tagName: 'div',
		className: 'audiotheme-venues',

		initialize: function( options ) {
			var state = this.controller.state();

			this.listenTo( state, 'change:provider', this.switchCollection );
			this.listenTo( this.collection, 'add', this.addVenue );
			this.listenTo( this.collection, 'reset', this.render );
			this.listenTo( state.get( 'search' ), 'reset', this.render );
		},

		render: function() {
			this.$el
				.off( 'scroll' )
				.on( 'scroll', _.bind( this.scroll, this ) )
				.html( '<ul />' );

			if ( this.collection.length ) {
				this.collection.each( this.addVenue, this );
			} else {
				// @todo Show feedback about there not being any matches.
			}
			return this;
		},

		addVenue: function( venue ) {
			var view = new app.view.VenuesListItem({
				controller: this.controller,
				model: venue
			}).render();

			this.$el.children( 'ul' ).append( view.el );
		},

		scroll: function() {
			if ( this.el.scrollHeight < this.el.scrollTop + this.el.clientHeight * 3 && this.collection.hasMore() ) {
				this.collection.more();
			}
		},

		switchCollection: function() {
			var state = this.controller.state(),
				provider = state.get( 'provider' );

			this.collection = state.get( provider );
			this.render();
		}
	});

	/**
	 *
	 */
	app.view.VenuesListItem = wp.media.View.extend({
		tagName: 'li',
		className: 'audiotheme-venues-list-item',

		events: {
			'click': 'setSelection'
		},

		initialize: function() {
			var selection = this.controller.state( 'audiotheme-venues' ).get( 'selection' );
			selection.on( 'reset', this.updateSelected, this );
			this.listenTo( this.model, 'change:name', this.render );
		},

		render: function() {
			this.$el.html( this.model.get( 'name' ) );
			this.updateSelected();
			return this;
		},

		setSelection: function() {
			this.controller.state().get( 'selection' ).reset( this.model );
		},

		updateSelected: function() {
			var isSelected = this.controller.state( 'audiotheme-venues' ).get( 'selection' ).first() === this.model;
			this.$el.toggleClass( 'is-selected', isSelected );
		}
	});

	/**
	 *
	 */
	app.view.VenuePanel = wp.media.View.extend({
		tagName: 'div',
		className: 'audiotheme-venue-panel',

		initialize: function() {
			this.listenTo( this.controller.state().get( 'selection' ), 'reset', this.render );
			this.listenTo( this.controller.state(), 'change:mode', this.render );
		},

		render: function() {
			var panelContent,
				model = this.controller.state().get( 'selection' ).first();

			if ( ! this.controller.state( 'audiotheme-venues' ).get( 'selection' ).length ) {
				return this;
			}

			if ( 'edit' === this.controller.state().get( 'mode' ) ) {
				panelContent = new app.view.VenueEditForm({
					controller: this.controller,
					model: model
				});
			} else {
				panelContent = new app.view.VenueDetails({
					controller: this.controller,
					model: model
				});
			}

			this.views.set([
				new app.view.VenuePanelTitle({
					controller: this.controller,
					model: model
				}),
				panelContent
			]);

			return this;
		}
	});

	/**
	 *
	 *
	 * @todo Don't show the button if the user can't edit venues.
	 */
	app.view.VenuePanelTitle = wp.media.View.extend({
		tagName: 'div',
		className: 'audiotheme-venue-panel-title',
		template: wp.template( 'audiotheme-venue-panel-title' ),

		events: {
			'click button': 'toggleMode'
		},

		initialize: function( options ) {
			this.model = options.model;
			this.listenTo( this.model, 'change:name', this.updateTitle );
		},

		render: function() {
			var state = this.controller.state( 'audiotheme-venues' ),
				mode = state.get( 'mode' );

			this.$el.html( this.template( this.model.toJSON() ) );
			this.$el.find( 'button' ).text( 'edit' === mode ? 'View' : 'Edit' );
			return this;
		},

		toggleMode: function( e ) {
			var mode = this.controller.state().get( 'mode' );
			e.preventDefault();
			this.controller.state().set( 'mode', 'edit' === mode ? 'view' : 'edit' );
		},

		updateTitle: function() {
			this.$el.find( 'h2' ).text( this.model.get( 'name' ) );
		}
	});

	/**
	 *
	 */
	app.view.VenueDetails = wp.media.View.extend({
		tagName: 'div',
		className: 'audiotheme-venue-details',
		template: wp.template( 'audiotheme-venue-details' ),

		render: function() {
			var model = this.controller.state( 'audiotheme-venues' ).get( 'selection' ).first(),
				data = _.extend( model.toJSON(), app.templateHelpers );

			this.$el.html( this.template( data ) );
			return this;
		}
	});

	/**
	 *
	 *
	 * @todo Search for timezone based on the city.
	 * @todo Display an error if the timezone isn't set.
	 */
	app.view.VenueAddForm = wp.media.View.extend({
		tagName: 'div',
		className: 'audiotheme-venue-edit-form',
		template: wp.template( 'audiotheme-venue-edit-form' ),

		events: {
			'change [data-setting]': 'updateAttribute'
		},

		initialize: function( options ) {
			this.model = options.model;
		},

		render: function() {
			this.$el.html( this.template( this.model.toJSON() ) );
			//this.$button = this.controller.toolbar.view.views.first( '.media-frame-toolbar' ).primary.get( 'save' ).$el;
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
			var attribute = $( e.target ).data( 'setting' ),
				value = e.target.value;

			if ( this.model.get( attribute ) !== value ) {
				this.model.set( attribute, value );
			}
		}
	});

	/**
	 *
	 */
	app.view.VenueEditForm = wp.media.View.extend({
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

	/**
	 *
	 */
	app.view.VenueAddToolbar = wp.media.view.Toolbar.extend({
		initialize: function( options ) {
			_.bindAll( this, 'saveVenue' );

			// This is a button.
			this.options.items = _.defaults( this.options.items || {}, {
				save: {
					text: this.controller.state().get( 'button' ).text,
					style: 'primary',
					priority: 80,
					requires: false,
					click: this.saveVenue
				},
				spinner: new wp.media.view.Spinner({
					priority: 60
				})
			});

			this.options.items.spinner.delay = 0;
			this.listenTo( this.model, 'change:name', this.toggleButtonState );

			wp.media.view.Toolbar.prototype.initialize.apply( this, arguments );
		},

		render: function() {
			this.$button = this.get( 'save' ).$el;
			this.toggleButtonState();
			return this;
		},

		saveVenue: function() {
			var controller = this.controller,
				model = controller.state().get( 'model' ),
				spinner = this.get( 'spinner' ).show();

			model.save().done(function( response ) {
				var selectController = controller.state( 'audiotheme-venues' );

				// Insert into the venues collection and update the selection.
				selectController.get( 'venues' ).add( model );
				selectController.get( 'selection' ).reset( model );
				selectController.set( 'mode', 'view' );
				controller.state().set( 'model', new app.model.Venue() );

				// Switch to the select view.
				controller.setState( 'audiotheme-venues' );

				spinner.hide();
			});
		},

		toggleButtonState: function() {
			this.$button.attr( 'disabled', '' === this.model.get( 'name' ) );
		}
	});

	/**
	 *
	 */
	app.view.VenueSelectToolbar = wp.media.view.Toolbar.extend({
		initialize: function( options ) {
			// This is a button.
			this.options.items = _.defaults( this.options.items || {}, {
				select: {
					text: this.controller.state().get( 'button' ).text,
					style: 'primary',
					priority: 80,
					requires: {
						selection: true
					},
					click: function() {
						var state = this.controller.state(),
							selection = state.get( 'selection' );

						state.trigger( 'insert', selection );
						this.controller.close();
					}
				}
			});

			wp.media.view.Toolbar.prototype.initialize.apply( this, arguments );
		}
	});

	/**
	 *
	 */
	app.templateHelpers = {
		isAddressEmpty: function() {
			return ! ( this.address || this.city || this.state || this.postal_code || this.country );
		}
	};

})( window, jQuery, _, Backbone, wp );
